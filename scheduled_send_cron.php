<?php

require_once '/path/to/roundcube/program/include/iniset.php';

$rcmail = rcmail::get_instance();
$db = $rcmail->get_dbh();

// Получаем письма, которые нужно отправить
$sql = "SELECT * FROM scheduled_emails WHERE scheduled_time <= NOW() AND status = 'pending'";
$result = $db->query($sql);

while ($row = $db->fetch_assoc($result)) {
    // Отправляем письмо
    $message = new rcube_message();
    $message->subject = $row['subject'];
    $message->body = $row['body'];

    $rcmail->smtp->send_message($message);

    // Обновляем статус письма
    $db->query("UPDATE scheduled_emails SET status = 'sent' WHERE id = ?", $row['id']);
}