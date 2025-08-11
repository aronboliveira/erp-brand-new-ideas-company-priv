<?php

return [

    /**
     *
     * Shared translations.
     *
     */
    'title' => 'Installeringsværktøj',
    'next' => 'Næste trin',
    'back' => 'Tilbage',
    'finish' => 'Installér',
    'forms' => [
        'errorTitle' => 'Følgende fejl opstod:',
    ],

    /**
     *
     * Home page translations.
     *
     */
    'welcome' => [
        'templateTitle' => 'Velkommen',
        'title'   => 'Installeringsværktøj',
        'message' => 'Nem installations- og opsætningsguide.',
        'next'    => 'Tjek krav',
    ],

    /**
     *
     * Requirements page translations.
     *
     */
    'requirements' => [
        'templateTitle' => 'Trin 1 | Serverkrav',
        'title' => 'Serverkrav',
        'next'    => 'Tjek tilladelser',
    ],

    /**
     *
     * Permissions page translations.
     *
     */
    'permissions' => [
        'templateTitle' => 'Trin 2 | Tilladelser',
        'title' => 'Tilladelser',
        'next' => 'Konfigurer miljø',
    ],

    /**
     *
     * Environment page translations.
     *
     */
    'environment' => [
        'menu' => [
            'templateTitle' => 'Trin 3 | Miljøindstillinger',
            'title' => 'Miljøindstillinger',
            'desc' => 'Vælg hvordan du vil konfigurere applikationens <code>.env</code> fil.',
            'wizard-button' => 'Guidet opsætning',
            'classic-button' => 'Klassisk teksteditor',
        ],
        'wizard' => [
            'templateTitle' => 'Trin 3 | Miljøindstillinger | Guidet vejledning',
            'title' => 'Guidet <code>.env</code> vejledning',
            'tabs' => [
                'environment' => 'Miljø',
                'database' => 'Database',
                'application' => 'Applikation'
            ],
            'form' => [
                'name_required' => 'Et miljønavn er påkrævet.',
                'app_name_label' => 'Applikationsnavn',
                'app_name_placeholder' => 'Applikationsnavn',
                'app_environment_label' => 'Applikationsmiljø',
                'app_environment_label_local' => 'Lokal',
                'app_environment_label_developement' => 'Udvikling',
                'app_environment_label_qa' => 'Test',
                'app_environment_label_production' => 'Produktion',
                'app_environment_label_other' => 'Andet',
                'app_environment_placeholder_other' => 'Indtast dit miljø...',
                'app_debug_label' => 'Applikationsfejlfinding',
                'app_debug_label_true' => 'Aktiveret',
                'app_debug_label_false' => 'Deaktiveret',
                'app_log_level_label' => 'Logningniveau',
                'app_log_level_label_debug' => 'debug',
                'app_log_level_label_info' => 'info',
                'app_log_level_label_notice' => 'notice',
                'app_log_level_label_warning' => 'warning',
                'app_log_level_label_error' => 'error',
                'app_log_level_label_critical' => 'critical',
                'app_log_level_label_alert' => 'alert',
                'app_log_level_label_emergency' => 'emergency',
                'app_url_label' => 'Applikations-URL',
                'app_url_placeholder' => 'Applikations-URL',
                'db_connection_label' => 'Databaseforbindelse',
                'db_connection_label_mysql' => 'mysql',
                'db_connection_label_sqlite' => 'sqlite',
                'db_connection_label_pgsql' => 'pgsql',
                'db_connection_label_sqlsrv' => 'sqlsrv',
                'db_host_label' => 'Databasevært',
                'db_host_placeholder' => 'Databasevært',
                'db_port_label' => 'Databaseport',
                'db_port_placeholder' => 'Databaseport',
                'db_name_label' => 'Databasenavn',
                'db_name_placeholder' => 'Databasenavn',
                'db_username_label' => 'Databasebrugernavn',
                'db_username_placeholder' => 'Databasebrugernavn',
                'db_password_label' => 'Databaseadgangskode',
                'db_password_placeholder' => 'Databaseadgangskode',

                'app_tabs' => [
                    'more_info' => 'Mere information',
                    'broadcasting_title' => 'Broadcasting, Caching, Session &amp; Kø',
                    'broadcasting_label' => 'Broadcast-driver',
                    'broadcasting_placeholder' => 'Broadcast-driver',
                    'cache_label' => 'Cache-driver',
                    'cache_placeholder' => 'Cache-driver',
                    'session_label' => 'Session-driver',
                    'session_placeholder' => 'Session-driver',
                    'queue_label' => 'Kø-driver',
                    'queue_placeholder' => 'Kø-driver',
                    'redis_label' => 'Redis-driver',
                    'redis_host' => 'Redis-vært',
                    'redis_password' => 'Redis-adgangskode',
                    'redis_port' => 'Redis-port',

                    'mail_label' => 'Mail',
                    'mail_driver_label' => 'Mail-driver',
                    'mail_driver_placeholder' => 'Mail-driver',
                    'mail_host_label' => 'Mail-vært',
                    'mail_host_placeholder' => 'Mail-vært',
                    'mail_port_label' => 'Mail-port',
                    'mail_port_placeholder' => 'Mail-port',
                    'mail_username_label' => 'Mail-brugernavn',
                    'mail_username_placeholder' => 'Mail-brugernavn',
                    'mail_password_label' => 'Mail-adgangskode',
                    'mail_password_placeholder' => 'Mail-adgangskode',
                    'mail_encryption_label' => 'Mail-kryptering',
                    'mail_encryption_placeholder' => 'Mail-kryptering',

                    'pusher_label' => 'Pusher',
                    'pusher_app_id_label' => 'Pusher App ID',
                    'pusher_app_id_palceholder' => 'Pusher App ID',
                    'pusher_app_key_label' => 'Pusher App Nøgle',
                    'pusher_app_key_palceholder' => 'Pusher App Nøgle',
                    'pusher_app_secret_label' => 'Pusher App Hemmelighed',
                    'pusher_app_secret_palceholder' => 'Pusher App Hemmelighed',
                ],
                'buttons' => [
                    'setup_database' => 'Opsæt database',
                    'setup_application' => 'Opsæt applikation',
                    'install' => 'Installér',
                ],
            ],
        ],
        'classic' => [
            'templateTitle' => 'Trin 3 | Miljøindstillinger | Klassisk editor',
            'title' => 'Klassisk miljøeditor',
            'save' => 'Gem .env',
            'back' => 'Brug guidet vejledning',
            'install' => 'Gem og installér',
        ],
        'success' => 'Dine .env filindstillinger er blevet gemt.',
        'errors' => 'Kunne ikke gemme .env filen, opret den venligst manuelt.',
    ],

    'install' => 'Installér',

    /**
     *
     * Installed Log translations.
     *
     */
    'installed' => [
        'success_log_message' => 'Installeringsværktøj korrekt INSTALLERET den ',
    ],

    /**
     *
     * Final page translations.
     *
     */
    'final' => [
        'title' => 'Installation fuldført',
        'templateTitle' => 'Installation fuldført',
        'finished' => 'Applikationen er blevet installeret korrekt.',
        'migration' => 'Migrerings- &amp; Seed-konsoloutput:',
        'console' => 'Applikationskonsoloutput:',
        'log' => 'Installationslogpost:',
        'env' => 'Endelig .env fil:',
        'exit' => 'Klik her for at afslutte',
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
        'title' => 'Opdateringsværktøj',

        /**
         *
         * Welcome page translations for update feature.
         *
         */
        'welcome' => [
            'title'   => 'Velkommen til opdateringsværktøjet',
            'message' => 'Velkommen til opdateringsguiden.',
        ],

        /**
         *
         * Welcome page translations for update feature.
         *
         */
        'overview' => [
            'title'   => 'Oversigt',
            'message' => 'Der er 1 opdatering.|Der er :number opdateringer.',
            'install_updates' => "Installer opdateringer"
        ],

        /**
         *
         * Final page translations.
         *
         */
        'final' => [
            'title' => 'Fuldført',
            'finished' => 'Applikationens database er blevet opdateret korrekt.',
            'exit' => 'Klik her for at afslutte',
        ],

        'log' => [
            'success_message' => 'Opdateringsværktøj korrekt OPDATERET den ',
        ],
    ],
];
