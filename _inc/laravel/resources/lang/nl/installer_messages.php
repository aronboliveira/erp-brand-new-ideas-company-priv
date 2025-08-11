<?php

return [

    /**
     *
     * Shared translations.
     *
     */
    'title' => 'Installatiewizard',
    'next' => 'Volgende Stap',
    'back' => 'Vorige',
    'finish' => 'Installeren',
    'forms' => [
        'errorTitle' => 'De volgende fouten zijn opgetreden:',
    ],

    /**
     *
     * Home page translations.
     *
     */
    'welcome' => [
        'templateTitle' => 'Welkom',
        'title'   => 'Installatiewizard',
        'message' => 'Eenvoudige installatie- en configuratiewizard.',
        'next'    => 'Controleer Vereisten',
    ],

    /**
     *
     * Requirements page translations.
     *
     */
    'requirements' => [
        'templateTitle' => 'Stap 1 | Serververeisten',
        'title' => 'Serververeisten',
        'next'    => 'Controleer Rechten',
    ],

    /**
     *
     * Permissions page translations.
     *
     */
    'permissions' => [
        'templateTitle' => 'Stap 2 | Bestandsrechten',
        'title' => 'Bestandsrechten',
        'next' => 'Omgeving Configureren',
    ],

    /**
     *
     * Environment page translations.
     *
     */
    'environment' => [
        'menu' => [
            'templateTitle' => 'Stap 3 | Omgevingsinstellingen',
            'title' => 'Omgevingsinstellingen',
            'desc' => 'Selecteer hoe u het <code>.env</code> bestand wilt configureren.',
            'wizard-button' => 'Wizard Configuratie',
            'classic-button' => 'Klassieke Tekst Editor',
        ],
        'wizard' => [
            'templateTitle' => 'Stap 3 | Omgevingsinstellingen | Begeleide Wizard',
            'title' => 'Begeleide <code>.env</code> Wizard',
            'tabs' => [
                'environment' => 'Omgeving',
                'database' => 'Database',
                'application' => 'Applicatie'
            ],
            'form' => [
                'name_required' => 'Een omgevingsnaam is vereist.',
                'app_name_label' => 'Applicatienaam',
                'app_name_placeholder' => 'Applicatienaam',
                'app_environment_label' => 'Applicatieomgeving',
                'app_environment_label_local' => 'Lokaal',
                'app_environment_label_developement' => 'Ontwikkeling',
                'app_environment_label_qa' => 'Testomgeving',
                'app_environment_label_production' => 'Productie',
                'app_environment_label_other' => 'Anders',
                'app_environment_placeholder_other' => 'Voer uw omgeving in...',
                'app_debug_label' => 'Applicatiefoutopsporing',
                'app_debug_label_true' => 'Aan',
                'app_debug_label_false' => 'Uit',
                'app_log_level_label' => 'Logboekniveau',
                'app_log_level_label_debug' => 'debug',
                'app_log_level_label_info' => 'info',
                'app_log_level_label_notice' => 'notice',
                'app_log_level_label_warning' => 'warning',
                'app_log_level_label_error' => 'error',
                'app_log_level_label_critical' => 'critical',
                'app_log_level_label_alert' => 'alert',
                'app_log_level_label_emergency' => 'emergency',
                'app_url_label' => 'Applicatie-URL',
                'app_url_placeholder' => 'Applicatie-URL',
                'db_connection_label' => 'Databaseverbinding',
                'db_connection_label_mysql' => 'mysql',
                'db_connection_label_sqlite' => 'sqlite',
                'db_connection_label_pgsql' => 'pgsql',
                'db_connection_label_sqlsrv' => 'sqlsrv',
                'db_host_label' => 'Databasehost',
                'db_host_placeholder' => 'Databasehost',
                'db_port_label' => 'Databasepoort',
                'db_port_placeholder' => 'Databasepoort',
                'db_name_label' => 'Databasenaam',
                'db_name_placeholder' => 'Databasenaam',
                'db_username_label' => 'Databasegebruikersnaam',
                'db_username_placeholder' => 'Databasegebruikersnaam',
                'db_password_label' => 'Databasewachtwoord',
                'db_password_placeholder' => 'Databasewachtwoord',

                'app_tabs' => [
                    'more_info' => 'Meer Informatie',
                    'broadcasting_title' => 'Broadcasting, Caching, Sessies &amp; Wachtrij',
                    'broadcasting_label' => 'Broadcast Driver',
                    'broadcasting_placeholder' => 'Broadcast Driver',
                    'cache_label' => 'Cache Driver',
                    'cache_placeholder' => 'Cache Driver',
                    'session_label' => 'Sessie Driver',
                    'session_placeholder' => 'Sessie Driver',
                    'queue_label' => 'Wachtrij Driver',
                    'queue_placeholder' => 'Wachtrij Driver',
                    'redis_label' => 'Redis Driver',
                    'redis_host' => 'Redis Host',
                    'redis_password' => 'Redis Wachtwoord',
                    'redis_port' => 'Redis Poort',

                    'mail_label' => 'E-mail',
                    'mail_driver_label' => 'Mail Driver',
                    'mail_driver_placeholder' => 'Mail Driver',
                    'mail_host_label' => 'Mail Host',
                    'mail_host_placeholder' => 'Mail Host',
                    'mail_port_label' => 'Mail Poort',
                    'mail_port_placeholder' => 'Mail Poort',
                    'mail_username_label' => 'Mail Gebruikersnaam',
                    'mail_username_placeholder' => 'Mail Gebruikersnaam',
                    'mail_password_label' => 'Mail Wachtwoord',
                    'mail_password_placeholder' => 'Mail Wachtwoord',
                    'mail_encryption_label' => 'Mail Versleuteling',
                    'mail_encryption_placeholder' => 'Mail Versleuteling',

                    'pusher_label' => 'Pusher',
                    'pusher_app_id_label' => 'Pusher App ID',
                    'pusher_app_id_palceholder' => 'Pusher App ID',
                    'pusher_app_key_label' => 'Pusher App Sleutel',
                    'pusher_app_key_palceholder' => 'Pusher App Sleutel',
                    'pusher_app_secret_label' => 'Pusher App Geheim',
                    'pusher_app_secret_palceholder' => 'Pusher App Geheim',
                ],
                'buttons' => [
                    'setup_database' => 'Database Instellen',
                    'setup_application' => 'Applicatie Instellen',
                    'install' => 'Installeren',
                ],
            ],
        ],
        'classic' => [
            'templateTitle' => 'Stap 3 | Omgevingsinstellingen | Klassieke Editor',
            'title' => 'Klassieke Omgevingseditor',
            'save' => '.env Opslaan',
            'back' => 'Wizard Gebruiken',
            'install' => 'Opslaan en Installeren',
        ],
        'success' => 'Uw .env bestandsinstellingen zijn opgeslagen.',
        'errors' => 'Kan .env bestand niet opslaan, maak het handmatig aan.',
    ],

    'install' => 'Installeren',

    /**
     *
     * Installed Log translations.
     *
     */
    'installed' => [
        'success_log_message' => 'Installatiewizard succesvol GEÏNSTALLEERD op ',
    ],

    /**
     *
     * Final page translations.
     *
     */
    'final' => [
        'title' => 'Installatie Voltooid',
        'templateTitle' => 'Installatie Voltooid',
        'finished' => 'Applicatie is succesvol geïnstalleerd.',
        'migration' => 'Migratie &amp; Seeding Console Uitvoer:',
        'console' => 'Applicatie Console Uitvoer:',
        'log' => 'Installatie Logboek:',
        'env' => 'Laatste .env Bestand:',
        'exit' => 'Klik hier om af te sluiten',
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
        'title' => 'Updater',

        /**
         *
         * Welcome page translations for update feature.
         *
         */
        'welcome' => [
            'title'   => 'Welkom bij de Updater',
            'message' => 'Welkom bij de updatewizard.',
        ],

        /**
         *
         * Welcome page translations for update feature.
         *
         */
        'overview' => [
            'title'   => 'Overzicht',
            'message' => 'Er is 1 update beschikbaar.|Er zijn :number updates beschikbaar.',
            'install_updates' => "Updates Installeren"
        ],

        /**
         *
         * Final page translations.
         *
         */
        'final' => [
            'title' => 'Voltooid',
            'finished' => 'Applicatiedatabase is succesvol bijgewerkt.',
            'exit' => 'Klik hier om af te sluiten',
        ],

        'log' => [
            'success_message' => 'Updater succesvol BIJGEWERKT op ',
        ],
    ],
];
