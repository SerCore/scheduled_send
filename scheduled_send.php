<?php

class scheduled_send extends rcube_plugin
{
    public $task = 'mail';

    public function init()
    {
        // Загружаем локализованные строки
        $this->add_texts('localization/');

        // Регистрируем хуки
        $this->add_hook('message_before_send', array($this, 'schedule_email'));
        $this->add_hook('template_object_messagecompose', array($this, 'add_schedule_ui'));
        $this->add_hook('scheduled_emails_list', array($this, 'list_scheduled_emails'));

        // Регистрируем действие для отмены писем
        $this->register_action('plugin.scheduled_send_cancel', array($this, 'cancel_scheduled_email'));

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
            <label for="scheduled_send_time">' . $this->gettext('scheduled_send_time') . '</label>
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
                $rcmail->output->show_message($this->gettext('invalid_time'), 'error');
                $args['abort'] = true;
                return $args;
            }

            // Сохраняем письмо в базу данных
            if ($this->save_to_database($args['message'], $scheduled_time)) {
                // Отменяем отправку письма сейчас
                $args['abort'] = true;
                $rcmail->output->show_message(sprintf($this->gettext('scheduled_email_saved'), $scheduled_time), 'confirmation');
            } else {
                $rcmail->output->show_message($this->gettext('scheduled_email_error'), 'error');
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

        rcube::write_log('scheduled_send.log', 'Письмо успешно сохранено: ' . $scheduled_time);
        return true;
    }

    public function cancel_scheduled_email()
    {
        $rcmail = rcmail::get_instance();

        // Проверяем CSRF-токен
        $rcmail->check_request();

        // Получаем ID письма
        $email_id = rcube_utils::get_input_value('email_id', rcube_utils::INPUT_POST);

        $db = $rcmail->get_dbh();
        $sql = "DELETE FROM scheduled_emails WHERE id = ? AND user_id = ?";
        $result = $db->query($sql, $email_id, $rcmail->user->ID);

        if ($db->affected_rows($result)) {
            $rcmail->output->show_message($this->gettext('scheduled_email_canceled'), 'confirmation');
            rcube::write_log('scheduled_send.log', 'Письмо отменено: ' . $email_id);
        } else {
            $rcmail->output->show_message($this->gettext('scheduled_email_cancel_error'), 'error');
            rcube::write_log('errors', 'Ошибка отмены письма: ' . $email_id);
        }
    }
}