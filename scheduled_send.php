<?php

class scheduled_send extends rcube_plugin
{
    public $task = 'mail';

    public function init()
    {
		// Загружаем локализованные строки
        $this->add_texts('localization/');
		
        // Логируем вызов метода init
        rcube::write_log('scheduled_send.log', 'Метод init вызван');

        // Регистрируем хуки
        $this->add_hook('message_before_send', array($this, 'schedule_email'));
        $this->add_hook('template_object_messagecompose', array($this, 'add_schedule_ui'));
        $this->add_hook('scheduled_emails_list', array($this, 'list_scheduled_emails'));

        // Подключаем JS и CSS
        $this->include_script('js/scheduled_send.js');
        $this->include_stylesheet('styles/scheduled_send.css');
    }

    public function add_schedule_ui($args)
    {
        // Логируем вызов метода add_schedule_ui
        rcube::write_log('scheduled_send.log', 'Метод add_schedule_ui вызван');

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

        // Проверяем CSRF-токен
        $rcmail->check_request();

        // Получаем выбранное время отправки
        $scheduled_time = rcube_utils::get_input_value('scheduled_send_time', rcube_utils::INPUT_POST);

        if (!empty($scheduled_time)) {
            // Проверяем корректность времени
            $scheduled_timestamp = strtotime($scheduled_time);
            if ($scheduled_timestamp === false || $scheduled_timestamp < time()) {
                $rcmail->output->show_message('Некорректное время отправки. Укажите время в будущем.', 'error');
                $args['abort'] = true;
                return $args;
            }

            // Сохраняем письмо в базу данных
            if ($this->save_to_database($args['message'], $scheduled_time)) {
                // Отменяем отправку письма сейчас
                $args['abort'] = true;
                $rcmail->output->show_message('Письмо запланировано на ' . $scheduled_time, 'confirmation');
            } else {
                $rcmail->output->show_message('Ошибка при сохранении письма. Попробуйте снова.', 'error');
                $args['abort'] = true;
            }
        }

        return $args;
    }

    private function save_to_database($message, $scheduled_time)
    {
        $rcmail = rcmail::get_instance();
        $db = $rcmail->get_dbh();

        // Экранирование данных
        $subject = $db->escape($message->get_header('subject'));
        $body = $db->escape($message->get_body());
        $from = $db->escape($message->get_header('from'));
        $to = $db->escape($message->get_header('to'));
        $cc = $db->escape($message->get_header('cc'));
        $bcc = $db->escape($message->get_header('bcc'));

        // Сохраняем письмо в базу данных
        $sql = "INSERT INTO scheduled_emails (user_id, subject, body, from_address, to_address, cc, bcc, scheduled_time, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending')";
        $result = $db->query($sql, $rcmail->user->ID, $subject, $body, $from, $to, $cc, $bcc, $scheduled_time);

        if ($db->is_error($result)) {
            rcube::write_log('errors', 'Failed to save scheduled email: ' . $db->is_error($result));
            return false;
        }

        return true;
    }

    public function list_scheduled_emails($args)
    {
        $rcmail = rcmail::get_instance();
        $db = $rcmail->get_dbh();

        // Получаем запланированные письма
        $sql = "SELECT * FROM scheduled_emails WHERE user_id = ? AND status = 'pending'";
        $result = $db->query($sql, $rcmail->user->ID);

        $args['emails'] = $db->fetch_all($result);
        return $args;
    }
}