<?php

return [

    /**
     *
     * Shared translations.
     *
     */
    'title' => 'Installazione',
    'next' => 'Passo Successivo',
    'back' => 'Indietro',
    'finish' => 'Installa',
    'forms' => [
        'errorTitle' => 'Si sono verificati i seguenti errori:',
    ],

    /**
     *
     * Home page translations.
     *
     */
    'welcome' => [
        'templateTitle' => 'Benvenuto',
        'title'   => 'Procedura di Installazione',
        'message' => 'Procedura guidata per installazione e configurazione.',
        'next'    => 'Verifica Requisiti',
    ],

    /**
     *
     * Requirements page translations.
     *
     */
    'requirements' => [
        'templateTitle' => 'Passo 1 | Requisiti di Sistema',
        'title' => 'Requisiti di Sistema',
        'next'    => 'Verifica Permessi',
    ],

    /**
     *
     * Permissions page translations.
     *
     */
    'permissions' => [
        'templateTitle' => 'Passo 2 | Permessi',
        'title' => 'Permessi',
        'next' => 'Configura Ambiente',
    ],

    /**
     *
     * Environment page translations.
     *
     */
    'environment' => [
        'menu' => [
            'templateTitle' => 'Passo 3 | Configurazione Ambiente',
            'title' => 'Configurazione Ambiente',
            'desc' => 'Seleziona come vuoi configurare il file <code>.env</code> dell\'applicazione.',
            'wizard-button' => 'Configurazione Guidata',
            'classic-button' => 'Editor Testuale Classico',
        ],
        'wizard' => [
            'templateTitle' => 'Passo 3 | Configurazione Ambiente | Procedura Guidata',
            'title' => 'Procedura Guidata <code>.env</code>',
            'tabs' => [
                'environment' => 'Ambiente',
                'database' => 'Database',
                'application' => 'Applicazione'
            ],
            'form' => [
                'name_required' => 'È richiesto un nome per l\'ambiente.',
                'app_name_label' => 'Nome Applicazione',
                'app_name_placeholder' => 'Nome Applicazione',
                'app_environment_label' => 'Ambiente Applicazione',
                'app_environment_label_local' => 'Locale',
                'app_environment_label_developement' => 'Sviluppo',
                'app_environment_label_qa' => 'Quality Assurance',
                'app_environment_label_production' => 'Produzione',
                'app_environment_label_other' => 'Altro',
                'app_environment_placeholder_other' => 'Inserisci il tuo ambiente...',
                'app_debug_label' => 'Debug Applicazione',
                'app_debug_label_true' => 'Attivo',
                'app_debug_label_false' => 'Disattivo',
                'app_log_level_label' => 'Livello Log',
                'app_log_level_label_debug' => 'debug',
                'app_log_level_label_info' => 'info',
                'app_log_level_label_notice' => 'notice',
                'app_log_level_label_warning' => 'warning',
                'app_log_level_label_error' => 'error',
                'app_log_level_label_critical' => 'critical',
                'app_log_level_label_alert' => 'alert',
                'app_log_level_label_emergency' => 'emergency',
                'app_url_label' => 'URL Applicazione',
                'app_url_placeholder' => 'URL Applicazione',
                'db_connection_label' => 'Connessione Database',
                'db_connection_label_mysql' => 'mysql',
                'db_connection_label_sqlite' => 'sqlite',
                'db_connection_label_pgsql' => 'pgsql',
                'db_connection_label_sqlsrv' => 'sqlsrv',
                'db_host_label' => 'Host Database',
                'db_host_placeholder' => 'Host Database',
                'db_port_label' => 'Porta Database',
                'db_port_placeholder' => 'Porta Database',
                'db_name_label' => 'Nome Database',
                'db_name_placeholder' => 'Nome Database',
                'db_username_label' => 'Username Database',
                'db_username_placeholder' => 'Username Database',
                'db_password_label' => 'Password Database',
                'db_password_placeholder' => 'Password Database',

                'app_tabs' => [
                    'more_info' => 'Maggiori Informazioni',
                    'broadcasting_title' => 'Broadcasting, Caching, Session &amp; Queue',
                    'broadcasting_label' => 'Driver Broadcasting',
                    'broadcasting_placeholder' => 'Driver Broadcasting',
                    'cache_label' => 'Driver Cache',
                    'cache_placeholder' => 'Driver Cache',
                    'session_label' => 'Driver Sessioni',
                    'session_placeholder' => 'Driver Sessioni',
                    'queue_label' => 'Driver Code',
                    'queue_placeholder' => 'Driver Code',
                    'redis_label' => 'Driver Redis',
                    'redis_host' => 'Host Redis',
                    'redis_password' => 'Password Redis',
                    'redis_port' => 'Porta Redis',

                    'mail_label' => 'Email',
                    'mail_driver_label' => 'Driver Email',
                    'mail_driver_placeholder' => 'Driver Email',
                    'mail_host_label' => 'Host Email',
                    'mail_host_placeholder' => 'Host Email',
                    'mail_port_label' => 'Porta Email',
                    'mail_port_placeholder' => 'Porta Email',
                    'mail_username_label' => 'Username Email',
                    'mail_username_placeholder' => 'Username Email',
                    'mail_password_label' => 'Password Email',
                    'mail_password_placeholder' => 'Password Email',
                    'mail_encryption_label' => 'Crittografia Email',
                    'mail_encryption_placeholder' => 'Crittografia Email',

                    'pusher_label' => 'Pusher',
                    'pusher_app_id_label' => 'Pusher App ID',
                    'pusher_app_id_palceholder' => 'Pusher App ID',
                    'pusher_app_key_label' => 'Pusher App Key',
                    'pusher_app_key_palceholder' => 'Pusher App Key',
                    'pusher_app_secret_label' => 'Pusher App Secret',
                    'pusher_app_secret_palceholder' => 'Pusher App Secret',
                ],
                'buttons' => [
                    'setup_database' => 'Configura Database',
                    'setup_application' => 'Configura Applicazione',
                    'install' => 'Installa',
                ],
            ],
        ],
        'classic' => [
            'templateTitle' => 'Passo 3 | Configurazione Ambiente | Editor Classico',
            'title' => 'Editor Ambiente Classico',
            'save' => 'Salva .env',
            'back' => 'Usa Procedura Guidata',
            'install' => 'Salva e Installa',
        ],
        'success' => 'Le impostazioni del file .env sono state salvate.',
        'errors' => 'Impossibile salvare il file .env, crealo manualmente.',
    ],

    'install' => 'Installa',

    /**
     *
     * Installed Log translations.
     *
     */
    'installed' => [
        'success_log_message' => 'Installazione completata con successo il ',
    ],

    /**
     *
     * Final page translations.
     *
     */
    'final' => [
        'title' => 'Installazione Completata',
        'templateTitle' => 'Installazione Completata',
        'finished' => 'L\'applicazione è stata installata con successo.',
        'migration' => 'Output Migrazione &amp; Seeding:',
        'console' => 'Output Console Applicazione:',
        'log' => 'Log Installazione:',
        'env' => 'File .env Finale:',
        'exit' => 'Clicca qui per uscire',
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
        'title' => 'Aggiornamento',

        /**
         *
         * Welcome page translations for update feature.
         *
         */
        'welcome' => [
            'title'   => 'Benvenuto nell\'Aggiornamento',
            'message' => 'Benvenuto nella procedura di aggiornamento.',
        ],

        /**
         *
         * Welcome page translations for update feature.
         *
         */
        'overview' => [
            'title'   => 'Panoramica',
            'message' => 'C\'è 1 aggiornamento disponibile.|Ci sono :number aggiornamenti disponibili.',
            'install_updates' => "Installa Aggiornamenti"
        ],

        /**
         *
         * Final page translations.
         *
         */
        'final' => [
            'title' => 'Completato',
            'finished' => 'Il database dell\'applicazione è stato aggiornato con successo.',
            'exit' => 'Clicca qui per uscire',
        ],

        'log' => [
            'success_message' => 'Aggiornamento completato con successo il ',
        ],
    ],
];
