<?php

class scheduled_send extends rcube_plugin
{
    public $task = 'mail';

    public function init()
    {
        $this->add_hook('message_before_send', array($this, 'schedule_email'));
        $this->add_hook('template_object_messagecompose', array($this, 'add_schedule_ui'));
        $this->include_script('js/scheduled_send.js');
    }

    public function add_schedule_ui($args)
    {
        // Добавляем поле для выбора времени отправки
        $args['content'] .= '<div id="scheduled_send_field">
            <label for="scheduled_send_time">Запланировать отправку:</label>
            <input type="datetime-local" id="scheduled_send_time" name="scheduled_send_time">
        </div>';
        return $args;
    }

    public function schedule_email($args)
    {
        $rcmail = rcmail::get_instance();

        // Получаем выбранное время отправки
        $scheduled_time = rcube_utils::get_input_value('scheduled_send_time', rcube_utils::INPUT_POST);

        if (!empty($scheduled_time)) {
            // Сохраняем письмо в базу данных
            $this->save_to_database($args['message'], $scheduled_time);

            // Отменяем отправку письма сейчас
            $args['abort'] = true;
            $rcmail->output->show_message('Письмо запланировано на ' . $scheduled_time, 'confirmation');
        }

        return $args;
    }

    private function save_to_database($message, $scheduled_time)
    {
        $rcmail = rcmail::get_instance();
        $db = $rcmail->get_dbh();

        // Сохраняем письмо в базу данных
        $sql = "INSERT INTO scheduled_emails (user_id, subject, body, scheduled_time, status)
                VALUES (?, ?, ?, ?, 'pending')";
        $db->query($sql, $rcmail->user->ID, $message->subject, $message->body, $scheduled_time);
    }
}