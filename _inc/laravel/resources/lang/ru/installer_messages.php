<?php

return [

    /**
     *
     * Shared translations.
     *
     */
    'title' => 'Мастер установки',
    'next' => 'Следующий шаг',
    'back' => 'Назад',
    'finish' => 'Установить',
    'forms' => [
        'errorTitle' => 'Произошли следующие ошибки:',
    ],

    /**
     *
     * Home page translations.
     *
     */
    'welcome' => [
        'templateTitle' => 'Добро пожаловать',
        'title'   => 'Мастер установки',
        'message' => 'Простой мастер установки и настройки.',
        'next'    => 'Проверить требования',
    ],

    /**
     *
     * Requirements page translations.
     *
     */
    'requirements' => [
        'templateTitle' => 'Шаг 1 | Требования к серверу',
        'title' => 'Требования к серверу',
        'next'    => 'Проверить права доступа',
    ],

    /**
     *
     * Permissions page translations.
     *
     */
    'permissions' => [
        'templateTitle' => 'Шаг 2 | Права доступа',
        'title' => 'Права доступа',
        'next' => 'Настроить окружение',
    ],

    /**
     *
     * Environment page translations.
     *
     */
    'environment' => [
        'menu' => [
            'templateTitle' => 'Шаг 3 | Настройки окружения',
            'title' => 'Настройки окружения',
            'desc' => 'Выберите способ настройки файла <code>.env</code>.',
            'wizard-button' => 'Мастер настройки',
            'classic-button' => 'Классический текстовый редактор',
        ],
        'wizard' => [
            'templateTitle' => 'Шаг 3 | Настройки окружения | Пошаговый мастер',
            'title' => 'Пошаговый мастер <code>.env</code>',
            'tabs' => [
                'environment' => 'Окружение',
                'database' => 'База данных',
                'application' => 'Приложение'
            ],
            'form' => [
                'name_required' => 'Требуется указать название окружения.',
                'app_name_label' => 'Название приложения',
                'app_name_placeholder' => 'Название приложения',
                'app_environment_label' => 'Окружение приложения',
                'app_environment_label_local' => 'Локальное',
                'app_environment_label_developement' => 'Разработка',
                'app_environment_label_qa' => 'Тестирование',
                'app_environment_label_production' => 'Продакшен',
                'app_environment_label_other' => 'Другое',
                'app_environment_placeholder_other' => 'Введите ваше окружение...',
                'app_debug_label' => 'Режим отладки',
                'app_debug_label_true' => 'Включен',
                'app_debug_label_false' => 'Выключен',
                'app_log_level_label' => 'Уровень логирования',
                'app_log_level_label_debug' => 'debug',
                'app_log_level_label_info' => 'info',
                'app_log_level_label_notice' => 'notice',
                'app_log_level_label_warning' => 'warning',
                'app_log_level_label_error' => 'error',
                'app_log_level_label_critical' => 'critical',
                'app_log_level_label_alert' => 'alert',
                'app_log_level_label_emergency' => 'emergency',
                'app_url_label' => 'URL приложения',
                'app_url_placeholder' => 'URL приложения',
                'db_connection_label' => 'Тип подключения к БД',
                'db_connection_label_mysql' => 'mysql',
                'db_connection_label_sqlite' => 'sqlite',
                'db_connection_label_pgsql' => 'pgsql',
                'db_connection_label_sqlsrv' => 'sqlsrv',
                'db_host_label' => 'Хост БД',
                'db_host_placeholder' => 'Хост БД',
                'db_port_label' => 'Порт БД',
                'db_port_placeholder' => 'Порт БД',
                'db_name_label' => 'Имя БД',
                'db_name_placeholder' => 'Имя БД',
                'db_username_label' => 'Пользователь БД',
                'db_username_placeholder' => 'Пользователь БД',
                'db_password_label' => 'Пароль БД',
                'db_password_placeholder' => 'Пароль БД',

                'app_tabs' => [
                    'more_info' => 'Дополнительно',
                    'broadcasting_title' => 'Вещание, Кэширование, Сессии &amp; Очереди',
                    'broadcasting_label' => 'Драйвер вещания',
                    'broadcasting_placeholder' => 'Драйвер вещания',
                    'cache_label' => 'Драйвер кэширования',
                    'cache_placeholder' => 'Драйвер кэширования',
                    'session_label' => 'Драйвер сессий',
                    'session_placeholder' => 'Драйвер сессий',
                    'queue_label' => 'Драйвер очередей',
                    'queue_placeholder' => 'Драйвер очередей',
                    'redis_label' => 'Драйвер Redis',
                    'redis_host' => 'Хост Redis',
                    'redis_password' => 'Пароль Redis',
                    'redis_port' => 'Порт Redis',

                    'mail_label' => 'Почта',
                    'mail_driver_label' => 'Почтовый драйвер',
                    'mail_driver_placeholder' => 'Почтовый драйвер',
                    'mail_host_label' => 'Почтовый сервер',
                    'mail_host_placeholder' => 'Почтовый сервер',
                    'mail_port_label' => 'Почтовый порт',
                    'mail_port_placeholder' => 'Почтовый порт',
                    'mail_username_label' => 'Имя пользователя почты',
                    'mail_username_placeholder' => 'Имя пользователя почты',
                    'mail_password_label' => 'Пароль почты',
                    'mail_password_placeholder' => 'Пароль почты',
                    'mail_encryption_label' => 'Шифрование почты',
                    'mail_encryption_placeholder' => 'Шифрование почты',

                    'pusher_label' => 'Pusher',
                    'pusher_app_id_label' => 'Pusher App ID',
                    'pusher_app_id_palceholder' => 'Pusher App ID',
                    'pusher_app_key_label' => 'Pusher App Key',
                    'pusher_app_key_palceholder' => 'Pusher App Key',
                    'pusher_app_secret_label' => 'Pusher App Secret',
                    'pusher_app_secret_palceholder' => 'Pusher App Secret',
                ],
                'buttons' => [
                    'setup_database' => 'Настроить БД',
                    'setup_application' => 'Настроить приложение',
                    'install' => 'Установить',
                ],
            ],
        ],
        'classic' => [
            'templateTitle' => 'Шаг 3 | Настройки окружения | Классический редактор',
            'title' => 'Классический редактор окружения',
            'save' => 'Сохранить .env',
            'back' => 'Использовать мастер',
            'install' => 'Сохранить и установить',
        ],
        'success' => 'Настройки вашего .env файла сохранены.',
        'errors' => 'Не удалось сохранить .env файл, создайте его вручную.',
    ],

    'install' => 'Установить',

    /**
     *
     * Installed Log translations.
     *
     */
    'installed' => [
        'success_log_message' => 'Мастер установки успешно завершен ',
    ],

    /**
     *
     * Final page translations.
     *
     */
    'final' => [
        'title' => 'Установка завершена',
        'templateTitle' => 'Установка завершена',
        'finished' => 'Приложение успешно установлено.',
        'migration' => 'Вывод миграций &amp; сидинга:',
        'console' => 'Вывод консоли приложения:',
        'log' => 'Журнал установки:',
        'env' => 'Финальный .env файл:',
        'exit' => 'Нажмите для выхода',
    ],

    /**
     *
     * Update specific translations
     *
     */
    'updater' => [
        /**
         *
         * Shared translations.
         *
         */
        'title' => 'Мастер обновления',

        /**
         *
         * Welcome page translations for update feature.
         *
         */
        'welcome' => [
            'title'   => 'Добро пожаловать в мастер обновления',
            'message' => 'Добро пожаловать в мастер обновления.',
        ],

        /**
         *
         * Welcome page translations for update feature.
         *
         */
        'overview' => [
            'title'   => 'Обзор',
            'message' => 'Доступно 1 обновление.|Доступно :number обновлений.',
            'install_updates' => "Установить обновления"
        ],

        /**
         *
         * Final page translations.
         *
         */
        'final' => [
            'title' => 'Завершено',
            'finished' => 'База данных приложения успешно обновлена.',
            'exit' => 'Нажмите для выхода',
        ],

        'log' => [
            'success_message' => 'Мастер обновления успешно завершен ',
        ],
    ],
];
