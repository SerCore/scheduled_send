<h3><strong>1. Подготовка сервера</strong></h3>
<p><span style="font-weight: 400;">Убедитесь, что на сервере установлены:</span></p>
<ul>
<li style="font-weight: 400;"><strong>PHP</strong><span style="font-weight: 400;"> (версия 7.0 или выше).</span></li>
<li style="font-weight: 400;"><strong>MySQL/MariaDB</strong><span style="font-weight: 400;"> (или другая поддерживаемая Roundcube СУБД).</span></li>
<li style="font-weight: 400;"><strong>Roundcube</strong><span style="font-weight: 400;"> (версия 1.5 или выше).</span></li>
<li style="font-weight: 400;"><strong>SMTP-сервер</strong><span style="font-weight: 400;"> (для отправки писем).</span><span style="font-weight: 400;"><br /></span><span style="font-weight: 400;"><br /></span><span style="font-weight: 400;"><br /></span><span style="font-weight: 400;"><br /></span><strong>2. Установка плагина</strong><strong><br /></strong><strong>a. Клонирование репозитория</strong></li>
<ol>
<li style="font-weight: 400;"><span style="font-weight: 400;">Перейдите в папку плагинов Roundcube:</span><span style="font-weight: 400;"><br /></span><span style="font-weight: 400;"><br /></span><span style="font-weight: 400;"><br /></span><span style="font-weight: 400;">cd /var/www/html/webmail/plugins</span><span style="font-weight: 400;"><br /></span><span style="font-weight: 400;"><br /><br /></span></li>
<ol>
<li style="font-weight: 400;"><span style="font-weight: 400;">Перейдите в папку плагинов Roundcube:</span><span style="font-weight: 400;"><br /></span><span style="font-weight: 400;">cd /var/www/html/webmail/plugins</span></li>
<li style="font-weight: 400;"><span style="font-weight: 400;">Склонируйте ваш репозиторий:</span><span style="font-weight: 400;"><br /></span> <span style="font-weight: 400;"><br /></span><span style="font-weight: 400;">git clone https://github.com/SerCore/scheduled_send.git</span></li>
</ol>
</ol>
</ul>
<ol>
<li><strong> Проверка структуры плагина</strong><strong><br /></strong><span style="font-weight: 400;">Убедитесь, что структура плагина выглядит так:</span><span style="font-weight: 400;"><br /></span> <span style="font-weight: 400;"><br /></span><span style="font-weight: 400;">scheduled_send/</span></li>
</ol>
<p><span style="font-weight: 400;">├── scheduled_send.php</span></p>
<p><span style="font-weight: 400;">├── config.inc.php</span></p>
<p><span style="font-weight: 400;">├── localization/</span></p>
<p><span style="font-weight: 400;">│ &nbsp; ├── en_US.inc</span></p>
<p><span style="font-weight: 400;">│ &nbsp; └── ru_RU.inc</span></p>
<p><span style="font-weight: 400;">├── js/</span></p>
<p><span style="font-weight: 400;">│ &nbsp; └── scheduled_send.js</span></p>
<p><span style="font-weight: 400;">├── lib/</span></p>
<p><span style="font-weight: 400;">│ &nbsp; └── scheduled_send_cron.php</span></p>
<p><span style="font-weight: 400;">└── styles/</span></p>
<ol>
<li style="font-weight: 400;"><span style="font-weight: 400;">&nbsp;&nbsp;&nbsp;&nbsp;└── scheduled_send.css</span><span style="font-weight: 400;"><br /></span><span style="font-weight: 400;"><br /></span><strong>3. Настройка базы данных</strong><strong><br /></strong><strong>a. Создание таблицы</strong></li>
<ol>
<li style="font-weight: 400;"><span style="font-weight: 400;">Подключитесь к MySQL/MariaDB:</span><span style="font-weight: 400;"><br /></span> <span style="font-weight: 400;"><br /></span><span style="font-weight: 400;">mysql -u root -p</span></li>
<li style="font-weight: 400;"><span style="font-weight: 400;">Выберите базу данных Roundcube:</span><span style="font-weight: 400;"><br /></span> <span style="font-weight: 400;"><br /></span><span style="font-weight: 400;">USE roundcubemail;</span></li>
</ol>
</ol>
<p><span style="font-weight: 400;">Создайте таблицу для запланированных писем:</span><span style="font-weight: 400;"><br /></span> <span style="font-weight: 400;"><br /></span><span style="font-weight: 400;">CREATE TABLE scheduled_emails (</span></p>
<p><span style="font-weight: 400;">&nbsp;&nbsp;&nbsp;&nbsp;id INT AUTO_INCREMENT PRIMARY KEY,</span></p>
<p><span style="font-weight: 400;">&nbsp;&nbsp;&nbsp;&nbsp;user_id INT NOT NULL,</span></p>
<p><span style="font-weight: 400;">&nbsp;&nbsp;&nbsp;&nbsp;subject TEXT NOT NULL,</span></p>
<p><span style="font-weight: 400;">&nbsp;&nbsp;&nbsp;&nbsp;body TEXT NOT NULL,</span></p>
<p><span style="font-weight: 400;">&nbsp;&nbsp;&nbsp;&nbsp;from_address TEXT NOT NULL,</span></p>
<p><span style="font-weight: 400;">&nbsp;&nbsp;&nbsp;&nbsp;to_address TEXT NOT NULL,</span></p>
<p><span style="font-weight: 400;">&nbsp;&nbsp;&nbsp;&nbsp;cc TEXT,</span></p>
<p><span style="font-weight: 400;">&nbsp;&nbsp;&nbsp;&nbsp;bcc TEXT,</span></p>
<p><span style="font-weight: 400;">&nbsp;&nbsp;&nbsp;&nbsp;scheduled_time DATETIME NOT NULL,</span></p>
<p><span style="font-weight: 400;">&nbsp;&nbsp;&nbsp;&nbsp;status ENUM('pending', 'sent') DEFAULT 'pending'</span></p>
<ol>
<li style="font-weight: 400;"><span style="font-weight: 400;">);</span></li>
</ol>
<ol>
<li style="font-weight: 400;"><span style="font-weight: 400;"><br /></span><strong>4. Настройка Roundcube</strong><strong><br /></strong><strong>a. Активация плагина</strong></li>
<ol>
<li style="font-weight: 400;"><span style="font-weight: 400;">Откройте файл конфигурации Roundcube:</span><span style="font-weight: 400;"><br /></span> <span style="font-weight: 400;"><br /></span><span style="font-weight: 400;">nano /var/www/html/webmail/config/config.inc.php</span></li>
<li style="font-weight: 400;"><span style="font-weight: 400;">Добавьте плагин в массив </span><span style="font-weight: 400;">$config['plugins']</span><span style="font-weight: 400;">:</span><span style="font-weight: 400;"><br /></span> <span style="font-weight: 400;"><br /></span><span style="font-weight: 400;">$config['plugins'] = ['scheduled_send'];</span></li>
</ol>
</ol>
<ol>
<li><strong> Настройка SMTP</strong><strong><br /></strong><span style="font-weight: 400;">Убедитесь, что Roundcube настроен для отправки писем через SMTP. Проверьте настройки в </span><span style="font-weight: 400;">config.inc.php</span><span style="font-weight: 400;">:</span><span style="font-weight: 400;"><br /></span> <span style="font-weight: 400;"><br /></span><span style="font-weight: 400;">$config['smtp_server'] = 'tls://smtp.yandex.ru';</span></li>
</ol>
<p><span style="font-weight: 400;">$config['smtp_port'] = 587;</span></p>
<p><span style="font-weight: 400;">$config['smtp_user'] = 'ваш_логин';</span></p>
<ol>
<li style="font-weight: 400;"><span style="font-weight: 400;">$config['smtp_pass'] = 'ваш_пароль';</span><span style="font-weight: 400;"><br /></span><span style="font-weight: 400;"><br /></span><strong>5. Настройка прав доступа</strong><strong><br /></strong><strong>a. Права на файлы и папки</strong></li>
<ol>
<li style="font-weight: 400;"><span style="font-weight: 400;">Установите владельца папки плагина:</span><span style="font-weight: 400;"><br /></span> <span style="font-weight: 400;"><br /></span><span style="font-weight: 400;">sudo chown -R www-data:www-data /var/www/html/webmail/plugins/scheduled_send</span><span style="font-weight: 400;"><br /></span><span style="font-weight: 400;">(Замените </span><span style="font-weight: 400;">www-data</span><span style="font-weight: 400;"> на пользователя и группу, под которыми работает ваш веб-сервер).</span></li>
<li style="font-weight: 400;"><span style="font-weight: 400;">Установите права доступа:</span><span style="font-weight: 400;"><br /></span> <span style="font-weight: 400;"><br /></span><span style="font-weight: 400;">sudo chmod -R 755 /var/www/html/webmail/plugins/scheduled_send</span></li>
</ol>
<li style="font-weight: 400;"><strong>b. Права на логи</strong></li>
<ol>
<li style="font-weight: 400;"><span style="font-weight: 400;">Создайте файл логов:</span><span style="font-weight: 400;"><br /></span> <span style="font-weight: 400;"><br /></span><span style="font-weight: 400;">sudo touch /var/www/html/webmail/logs/scheduled_send.log</span></li>
</ol>
</ol>
<p><span style="font-weight: 400;">Установите права:</span><span style="font-weight: 400;"><br /></span> <span style="font-weight: 400;"><br /></span><span style="font-weight: 400;">sudo chown www-data:www-data /var/www/html/webmail/logs/scheduled_send.log</span></p>
<ol>
<li style="font-weight: 400;"><span style="font-weight: 400;">sudo chmod 644 /var/www/html/webmail/logs/scheduled_send.log</span></li>
<li style="font-weight: 400;"><span style="font-weight: 400;"><br /></span><strong>6. Настройка Cron</strong><strong><br /></strong><strong>a. Создание задачи Cron</strong></li>
<ol>
<li style="font-weight: 400;"><span style="font-weight: 400;">Откройте планировщик задач:</span><span style="font-weight: 400;"><br /></span> <span style="font-weight: 400;"><br /></span><span style="font-weight: 400;">crontab -e</span></li>
<li style="font-weight: 400;"><span style="font-weight: 400;">Добавьте задачу для запуска скрипта каждую минуту:</span><span style="font-weight: 400;"><br /></span> <span style="font-weight: 400;"><br /></span><span style="font-weight: 400;">* * * * * /usr/bin/php /var/www/html/webmail/plugins/scheduled_send/lib/scheduled_send_cron.php</span></li>
</ol>
<li style="font-weight: 400;"><strong>b. Проверка работы Cron</strong></li>
<ol>
<li style="font-weight: 400;"><span style="font-weight: 400;">Проверьте, что задача добавлена:</span><span style="font-weight: 400;"><br /></span> <span style="font-weight: 400;"><br /></span><span style="font-weight: 400;">crontab -l</span></li>
<li style="font-weight: 400;"><span style="font-weight: 400;">Проверьте логи Cron:</span><span style="font-weight: 400;"><br /></span> <span style="font-weight: 400;"><br /></span><span style="font-weight: 400;">grep CRON /var/log/syslog</span></li>
</ol>
<li style="font-weight: 400;"><span style="font-weight: 400;"><br /></span><strong>7. Проверка работы плагина</strong><strong><br /></strong><strong>a. Вход в Roundcube</strong></li>
<ol>
<li style="font-weight: 400;"><span style="font-weight: 400;">Откройте Roundcube в браузере:</span><span style="font-weight: 400;"><br /></span> <span style="font-weight: 400;"><br /></span><span style="font-weight: 400;">http://ваш_домен/webmail/</span></li>
<li style="font-weight: 400;"><span style="font-weight: 400;">Войдите в свою учётную запись.</span></li>
</ol>
<li style="font-weight: 400;"><strong>b. Создание запланированного письма</strong></li>
<ol>
<li style="font-weight: 400;"><span style="font-weight: 400;">Нажмите "Написать письмо".</span></li>
<li style="font-weight: 400;"><span style="font-weight: 400;">Заполните поля:</span></li>
<ul>
<li style="font-weight: 400;"><span style="font-weight: 400;">Кому: Укажите адрес получателя.</span></li>
<li style="font-weight: 400;"><span style="font-weight: 400;">Тема: Напишите тему письма.</span></li>
<li style="font-weight: 400;"><span style="font-weight: 400;">Текст: Напишите текст письма.</span></li>
</ul>
<li style="font-weight: 400;"><span style="font-weight: 400;">Выберите время отправки в поле "Запланировать отправку".</span></li>
<li style="font-weight: 400;"><span style="font-weight: 400;">Нажмите "Отправить".</span></li>
</ol>
<li style="font-weight: 400;"><strong>c. Проверка логов</strong></li>
<ol>
<li style="font-weight: 400;"><span style="font-weight: 400;">Проверьте логи плагина:</span><span style="font-weight: 400;"><br /></span> <span style="font-weight: 400;"><br /></span><span style="font-weight: 400;">cat /var/www/html/webmail/logs/scheduled_send.log</span></li>
<li style="font-weight: 400;"><span style="font-weight: 400;">Проверьте логи Roundcube:</span><span style="font-weight: 400;"><br /></span> <span style="font-weight: 400;"><br /></span><span style="font-weight: 400;">cat /var/www/html/webmail/logs/errors.log</span></li>
</ol>
<li style="font-weight: 400;"><span style="font-weight: 400;"><br /></span><strong>8. Дополнительные настройки</strong><strong><br /></strong><strong>a. Настройка PHP</strong></li>
</ol>
<p><span style="font-weight: 400;">Убедитесь, что в </span><span style="font-weight: 400;">php.ini</span><span style="font-weight: 400;"> включены необходимые модули:</span><span style="font-weight: 400;"><br /></span> <span style="font-weight: 400;"><br /></span><span style="font-weight: 400;">extension=mysqli.so</span></p>
<ol>
<li style="font-weight: 400;"><span style="font-weight: 400;">extension=imap.so</span></li>
<li style="font-weight: 400;"><span style="font-weight: 400;">Увеличьте лимит времени выполнения скриптов (если нужно):</span><span style="font-weight: 400;"><br /></span> <span style="font-weight: 400;"><br /></span><span style="font-weight: 400;">max_execution_time = 300</span></li>
</ol>
<ol>
<li style="font-weight: 400;"><strong>b. Настройка веб-сервера</strong></li>
</ol>
<p><span style="font-weight: 400;">Перезагрузите веб-сервер:</span><span style="font-weight: 400;"><br /></span> <span style="font-weight: 400;"><br /></span><span style="font-weight: 400;">sudo systemctl restart apache2</span><span style="font-weight: 400;"><br /></span><span style="font-weight: 400;">или</span><span style="font-weight: 400;"><br /></span> <span style="font-weight: 400;"><br /></span><span style="font-weight: 400;">sudo systemctl restart nginx</span><span style="font-weight: 400;"><br /></span><strong>9. Итог</strong><strong><br /></strong><span style="font-weight: 400;">Теперь ваш плагин </span><span style="font-weight: 400;">scheduled_send</span><span style="font-weight: 400;"> должен работать в Roundcube.</span></p>