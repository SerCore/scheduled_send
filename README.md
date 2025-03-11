Шаги для создания плагина

1. Структура плагина
Создайте следующую структуру папок и файлов для плагина:

scheduled_send/
├── plugin.php
├── config.inc.php
├── localization/
│   └── en_US.inc
├── js/
│   └── scheduled_send.js
└── lib/
    └── scheduled_send_cron.php

    2. Файл plugin.php
Это основной файл плагина, который регистрирует его в Roundcube.
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
3. Файл config.inc.php
Этот файл содержит настройки плагина.

<?php

$config['scheduled_send_table'] = 'scheduled_emails'; // Название таблицы в базе данных
4. Файл localization/en_US.inc
Этот файл содержит локализацию для плагина.

<?php

$labels = array();
$labels['scheduled_send'] = 'Scheduled Send';
$labels['scheduled_send_time'] = 'Schedule Time';
5. Файл js/scheduled_send.js
Этот файл добавляет JavaScript для обработки интерфейса.

javascript
Copy
$(document).ready(function() {
    $('#scheduled_send_time').on('change', function() {
        // Обработка выбора времени
        console.log('Время отправки выбрано: ' + $(this).val());
    });
});
6. Файл lib/scheduled_send_cron.php
Этот файл будет использоваться для отправки запланированных писем через Cron.

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
7. Создание таблицы в базе данных
Создайте таблицу для хранения запланированных писей:

CREATE TABLE scheduled_emails (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    subject TEXT NOT NULL,
    body TEXT NOT NULL,
    scheduled_time DATETIME NOT NULL,
    status ENUM('pending', 'sent') DEFAULT 'pending'
);
8. Настройка Cron
Добавьте задачу Cron для запуска скрипта отправки писей. Например:

* * * * * php /path/to/roundcube/plugins/scheduled_send/lib/scheduled_send_cron.php





(Пошаговый гайд по cron)
1.1 Проверьте путь к PHP
Убедитесь, что вы знаете путь к интерпретатору PHP на вашем сервере. Вы можете узнать его с помощью команды:

which php
Пример вывода:
/usr/bin/php
2.2 Проверьте путь к скрипту
Убедитесь, что вы знаете полный путь к файлу scheduled_send_cron.php. Например:

/var/www/roundcube/plugins/scheduled_send/lib/scheduled_send_cron.php
3.3 Откройте Cron для редактирования
Откройте терминал на вашем сервере.

Введите команду для редактирования Cron-задач:

crontab -e
Если вы редактируете Cron впервые, вам может быть предложено выбрать текстовый редактор (например, nano или vim). Выберите удобный для вас.

4.4 Добавьте задачу Cron
В открывшемся редакторе добавьте следующую строку:

* * * * * /usr/bin/php /var/www/roundcube/plugins/scheduled_send/lib/scheduled_send_cron.php
Здесь:

* * * * * — расписание (каждую минуту).

/usr/bin/php — путь к PHP (замените на ваш, если он отличается).

/var/www/roundcube/plugins/scheduled_send/lib/scheduled_send_cron.php — путь к вашему скрипту.

Сохраните файл и закройте редактор:

В nano: Нажмите Ctrl + O, затем Enter, чтобы сохранить, и Ctrl + X, чтобы выйти.

В vim: Нажмите Esc, затем введите :wq и нажмите Enter.

5.5 Проверьте, что задача добавлена
Вы можете проверить список активных Cron-задач с помощью команды:

crontab -l
Убедитесь, что ваша задача отображается в списке.
6.6 Проверьте работу скрипта
Подождите несколько минут, чтобы Cron запустил скрипт.
Проверьте логи Cron, чтобы убедиться, что скрипт выполняется:

grep CRON /var/log/syslog
Или, если вы используете systemd, выполните:

journalctl -u cron
Если скрипт не работает, проверьте:

Правильность пути к PHP и скрипту.

Права доступа к файлу scheduled_send_cron.php (должны быть доступны для пользователя, от которого запускается Cron).

Логи ошибок PHP (если они есть).

7.7 (Опционально) Настройка расписания
Если вы хотите, чтобы скрипт запускался не каждую минуту, а, например, каждые 5 минут, измените Cron-задачу:
*/5 * * * * /usr/bin/php /var/www/roundcube/plugins/scheduled_send/lib/scheduled_send_cron.php
8.8 (Опционально) Логирование вывода
Если вы хотите сохранять вывод скрипта в файл для отладки, добавьте перенаправление вывода:
* * * * * /usr/bin/php /var/www/roundcube/plugins/scheduled_send/lib/scheduled_send_cron.php >> /var/log/scheduled_send.log 2>&1
Здесь:
>> /var/log/scheduled_send.log — добавляет вывод в файл лога.

2>&1 — перенаправляет ошибки в тот же файл.
9.9 (Опционально) Права доступа
Убедитесь, что файл scheduled_send_cron.php имеет права на выполнение:
chmod +x /var/www/roundcube/plugins/scheduled_send/lib/scheduled_send_cron.php









9. Установка плагина
Поместите папку scheduled_send в директорию plugins Roundcube.

Активируйте плагин в конфигурации Roundcube:

$config['plugins'] = array('scheduled_send');
10. Тестирование
Откройте Roundcube и создайте новое письмо.

Выберите время отправки и нажмите "Отправить".

Проверьте, что письмо сохраняется в базе данных и отправляется в указанное время.


