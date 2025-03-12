<?php

require_once '/path/to/roundcube/program/include/iniset.php';

$rcmail = rcmail::get_instance();
$db = $rcmail->get_dbh();

// Получаем письма, которые нужно отправить
$sql = "SELECT * FROM scheduled_emails WHERE scheduled_time <= NOW() AND status = 'pending'";
$result = $db->query($sql);

while ($row = $db->fetch_assoc($result)) {
    // Создаем объект письма
    $message = new rcube_message();
    $message->set_header('Subject', $row['subject']);
    $message->set_body($row['body']);
    $message->set_header('From', $row['from_address']);
    $message->set_header('To', $row['to_address']);
    $message->set_header('Cc', $row['cc']);
    $message->set_header('Bcc', $row['bcc']);

    // Отправляем письмо
    if ($rcmail->smtp->send_message($message)) {
        // Обновляем статус письма
        $db->query("UPDATE scheduled_emails SET status = 'sent' WHERE id = ?", $row['id']);
    } else {
        // Логируем ошибку
        rcube::write_log('errors', 'Ошибка отправки письма: ' . $rcmail->smtp->get_error());
    }
}