<?php

return [

    /**
     *
     * Wspólne tłumaczenia.
     *
     */
    'title'   => 'Instalator',
    'next'    => 'Następny krok',
    'back'    => 'Poprzedni',
    'finish'  => 'Zainstaluj',
    'forms'   => [
        'errorTitle' => 'Wystąpiły następujące błędy:',
    ],

    /**
     *
     * Tłumaczenia strony powitalnej.
     *
     */
    'welcome' => [
        'templateTitle' => 'Witamy',
        'title'         => 'Instalator',
        'message'       => 'Łatwy kreator instalacji i konfiguracji.',
        'next'          => 'Sprawdź wymagania',
    ],

    /**
     *
     * Tłumaczenia strony wymagań.
     *
     */
    'requirements' => [
        'templateTitle' => 'Krok 1 | Wymagania serwera',
        'title'         => 'Wymagania serwera',
        'next'          => 'Sprawdź uprawnienia',
    ],

    /**
     *
     * Tłumaczenia strony uprawnień.
     *
     */
    'permissions' => [
        'templateTitle' => 'Krok 2 | Uprawnienia',
        'title'         => 'Uprawnienia',
        'next'          => 'Skonfiguruj środowisko',
    ],

    /**
     *
     * Tłumaczenia strony środowiska.
     *
     */
    'environment' => [
        'menu' => [
            'templateTitle'  => 'Krok 3 | Ustawienia środowiska',
            'title'          => 'Ustawienia środowiska',
            'desc'           => 'Wybierz sposób konfiguracji pliku <code>.env</code> aplikacji.',
            'wizard-button'  => 'Konfiguracja w kreatorze',
            'classic-button' => 'Klasyczny edytor tekstu',
        ],
        'wizard' => [
            'templateTitle' => 'Krok 3 | Ustawienia środowiska | Kreator',
            'title'         => 'Kreator <code>.env</code>',
            'tabs'          => [
                'environment' => 'Środowisko',
                'database'    => 'Baza danych',
                'application' => 'Aplikacja',
            ],
            'form'          => [
                'name_required'                     => 'Nazwa środowiska jest wymagana.',
                'app_name_label'                    => 'Nazwa aplikacji',
                'app_name_placeholder'              => 'Nazwa aplikacji',
                'app_environment_label'             => 'Środowisko aplikacji',
                'app_environment_label_local'       => 'Lokalne',
                'app_environment_label_developement' => 'Deweloperskie',
                'app_environment_label_qa'          => 'QA',
                'app_environment_label_production'  => 'Produkcyjne',
                'app_environment_label_other'       => 'Inne',
                'app_environment_placeholder_other' => 'Wprowadź inne środowisko...',
                'app_debug_label'                   => 'Debugowanie aplikacji',
                'app_debug_label_true'              => 'Tak',
                'app_debug_label_false'             => 'Nie',
                'app_log_level_label'               => 'Poziom logów aplikacji',
                'app_log_level_label_debug'         => 'Debugowanie',
                'app_log_level_label_info'          => 'Informacja',
                'app_log_level_label_notice'        => 'Powiadomienie',
                'app_log_level_label_warning'       => 'Ostrzeżenie',
                'app_log_level_label_error'         => 'Błąd',
                'app_log_level_label_critical'      => 'Krytyczny',
                'app_log_level_label_alert'         => 'Alert',
                'app_log_level_label_emergency'     => 'Awaryjny',
                'app_url_label'                     => 'Adres URL aplikacji',
                'app_url_placeholder'               => 'Adres URL aplikacji',
                'db_connection_label'               => 'Połączenie z bazą danych',
                'db_connection_label_mysql'         => 'mysql',
                'db_connection_label_sqlite'        => 'sqlite',
                'db_connection_label_pgsql'         => 'pgsql',
                'db_connection_label_sqlsrv'        => 'sqlsrv',
                'db_host_label'                     => 'Host bazy danych',
                'db_host_placeholder'               => 'Host bazy danych',
                'db_port_label'                     => 'Port bazy danych',
                'db_port_placeholder'               => 'Port bazy danych',
                'db_name_label'                     => 'Nazwa bazy danych',
                'db_name_placeholder'               => 'Nazwa bazy danych',
                'db_username_label'                 => 'Użytkownik bazy danych',
                'db_username_placeholder'           => 'Użytkownik bazy danych',
                'db_password_label'                 => 'Hasło bazy danych',
                'db_password_placeholder'           => 'Hasło bazy danych',

                'app_tabs' => [
                    'more_info'              => 'Więcej informacji',
                    'broadcasting_title'     => 'Transmisja, cache, sesja i kolejka',
                    'broadcasting_label'     => 'Sterownik transmisji',
                    'broadcasting_placeholder' => 'Sterownik transmisji',
                    'cache_label'            => 'Sterownik cache',
                    'cache_placeholder'      => 'Sterownik cache',
                    'session_label'          => 'Sterownik sesji',
                    'session_placeholder'    => 'Sterownik sesji',
                    'queue_label'            => 'Sterownik kolejki',
                    'queue_placeholder'      => 'Sterownik kolejki',
                    'redis_label'            => 'Sterownik Redis',
                    'redis_host'             => 'Host Redis',
                    'redis_password'         => 'Hasło Redis',
                    'redis_port'             => 'Port Redis',

                    'mail_label'               => 'Poczta',
                    'mail_driver_label'        => 'Sterownik poczty',
                    'mail_driver_placeholder'  => 'Sterownik poczty',
                    'mail_host_label'          => 'Host poczty',
                    'mail_host_placeholder'    => 'Host poczty',
                    'mail_port_label'          => 'Port poczty',
                    'mail_port_placeholder'    => 'Port poczty',
                    'mail_username_label'      => 'Użytkownik poczty',
                    'mail_username_placeholder' => 'Użytkownik poczty',
                    'mail_password_label'      => 'Hasło poczty',
                    'mail_password_placeholder' => 'Hasło poczty',
                    'mail_encryption_label'    => 'Szyfrowanie poczty',
                    'mail_encryption_placeholder' => 'Szyfrowanie poczty',

                    'pusher_label'                => 'Pusher',
                    'pusher_app_id_label'         => 'ID aplikacji Pusher',
                    'pusher_app_id_palceholder'   => 'ID aplikacji Pusher',
                    'pusher_app_key_label'        => 'Klucz aplikacji Pusher',
                    'pusher_app_key_palceholder'  => 'Klucz aplikacji Pusher',
                    'pusher_app_secret_label'     => 'Sekret aplikacji Pusher',
                    'pusher_app_secret_palceholder' => 'Sekret aplikacji Pusher',
                ],
                'buttons' => [
                    'setup_database'    => 'Konfiguruj bazę danych',
                    'setup_application' => 'Konfiguruj aplikację',
                    'install'           => 'Zainstaluj',
                ],
            ],
        ],
        'classic' => [
            'templateTitle' => 'Krok 3 | Ustawienia środowiska | Klasyczny edytor',
            'title'         => 'Klasyczny edytor środowiska',
            'save'          => 'Zapisz .env',
            'back'          => 'Użyj kreatora',
            'install'       => 'Zapisz i zainstaluj',
        ],
        'success' => 'Ustawienia pliku .env zostały zapisane.',
        'errors'  => 'Nie można zapisać pliku .env. Utwórz go ręcznie.',
    ],

    'install' => 'Zainstaluj',

    /**
     *
     * Tłumaczenia logu instalacji.
     *
     */
    'installed' => [
        'success_log_message' => 'Instalator został pomyślnie zainstalowany ',
    ],

    /**
     *
     * Tłumaczenia strony końcowej.
     *
     */
    'final' => [
        'title'         => 'Instalacja zakończona',
        'templateTitle' => 'Instalacja zakończona',
        'finished'      => 'Aplikacja została pomyślnie zainstalowana.',
        'migration'     => 'Wyjście konsoli migracji i seed:',
        'console'       => 'Wyjście konsoli aplikacji:',
        'log'           => 'Rejestr instalacji:',
        'env'           => 'Końcowy plik .env:',
        'exit'          => 'Kliknij tutaj, aby wyjść',
    ],

    /**
     *
     * Tłumaczenia aktualizatora.
     *
     */
    'updater' => [
        /**
         *
         * Wspólne tłumaczenia.
         *
         */
        'title' => 'Aktualizator',

        /**
         *
         * Strona powitalna aktualizatora.
         *
         */
        'welcome' => [
            'title'   => 'Witamy w aktualizatorze',
            'message' => 'Witamy w kreatorze aktualizacji.',
        ],

        /**
         *
         * Strona przeglądu aktualizacji.
         *
         */
        'overview' => [
            'title'           => 'Przegląd',
            'message'         => 'Jest 1 aktualizacja.|Są :number aktualizacje.',
            'install_updates' => 'Zainstaluj aktualizacje',
        ],

        /**
         *
         * Strona końcowa aktualizatora.
         *
         */
        'final' => [
            'title'    => 'Zakończono',
            'finished' => 'Baza danych aplikacji została pomyślnie zaktualizowana.',
            'exit'     => 'Kliknij tutaj, aby wyjść',
        ],

        'log' => [
            'success_message' => 'Aktualizator został pomyślnie zaktualizowany ',
        ],
    ],

];
