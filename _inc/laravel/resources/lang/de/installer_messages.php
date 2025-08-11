<?php

return [

    /**
     *
     * Shared translations.
     *
     */
    'title' => 'Installationsassistent',
    'next' => 'Nächster Schritt',
    'back' => 'Zurück',
    'finish' => 'Installieren',
    'forms' => [
        'errorTitle' => 'Folgende Fehler sind aufgetreten:',
    ],

    /**
     *
     * Home page translations.
     *
     */
    'welcome' => [
        'templateTitle' => 'Willkommen',
        'title'   => 'Installationsassistent',
        'message' => 'Einfacher Installations- und Einrichtungsassistent.',
        'next'    => 'Systemvoraussetzungen prüfen',
    ],

    /**
     *
     * Requirements page translations.
     *
     */
    'requirements' => [
        'templateTitle' => 'Schritt 1 | Servervoraussetzungen',
        'title' => 'Servervoraussetzungen',
        'next'    => 'Berechtigungen prüfen',
    ],

    /**
     *
     * Permissions page translations.
     *
     */
    'permissions' => [
        'templateTitle' => 'Schritt 2 | Berechtigungen',
        'title' => 'Berechtigungen',
        'next' => 'Umgebung konfigurieren',
    ],

    /**
     *
     * Environment page translations.
     *
     */
    'environment' => [
        'menu' => [
            'templateTitle' => 'Schritt 3 | Umgebungseinstellungen',
            'title' => 'Umgebungseinstellungen',
            'desc' => 'Bitte wählen Sie, wie Sie die <code>.env</code>-Datei konfigurieren möchten.',
            'wizard-button' => 'Assistenten verwenden',
            'classic-button' => 'Klassischer Texteditor',
        ],
        'wizard' => [
            'templateTitle' => 'Schritt 3 | Umgebungseinstellungen | Geführter Assistent',
            'title' => 'Geführter <code>.env</code>-Assistent',
            'tabs' => [
                'environment' => 'Umgebung',
                'database' => 'Datenbank',
                'application' => 'Anwendung'
            ],
            'form' => [
                'name_required' => 'Ein Umgebungsname ist erforderlich.',
                'app_name_label' => 'Anwendungsname',
                'app_name_placeholder' => 'Anwendungsname',
                'app_environment_label' => 'Anwendungsumgebung',
                'app_environment_label_local' => 'Lokal',
                'app_environment_label_developement' => 'Entwicklung',
                'app_environment_label_qa' => 'Qualitätssicherung',
                'app_environment_label_production' => 'Produktion',
                'app_environment_label_other' => 'Andere',
                'app_environment_placeholder_other' => 'Geben Sie Ihre Umgebung ein...',
                'app_debug_label' => 'Anwendungs-Debug',
                'app_debug_label_true' => 'Aktiviert',
                'app_debug_label_false' => 'Deaktiviert',
                'app_log_level_label' => 'Log-Level',
                'app_log_level_label_debug' => 'debug',
                'app_log_level_label_info' => 'info',
                'app_log_level_label_notice' => 'notice',
                'app_log_level_label_warning' => 'warning',
                'app_log_level_label_error' => 'error',
                'app_log_level_label_critical' => 'critical',
                'app_log_level_label_alert' => 'alert',
                'app_log_level_label_emergency' => 'emergency',
                'app_url_label' => 'Anwendungs-URL',
                'app_url_placeholder' => 'Anwendungs-URL',
                'db_connection_label' => 'Datenbankverbindung',
                'db_connection_label_mysql' => 'mysql',
                'db_connection_label_sqlite' => 'sqlite',
                'db_connection_label_pgsql' => 'pgsql',
                'db_connection_label_sqlsrv' => 'sqlsrv',
                'db_host_label' => 'Datenbank-Host',
                'db_host_placeholder' => 'Datenbank-Host',
                'db_port_label' => 'Datenbank-Port',
                'db_port_placeholder' => 'Datenbank-Port',
                'db_name_label' => 'Datenbankname',
                'db_name_placeholder' => 'Datenbankname',
                'db_username_label' => 'Datenbank-Benutzername',
                'db_username_placeholder' => 'Datenbank-Benutzername',
                'db_password_label' => 'Datenbank-Passwort',
                'db_password_placeholder' => 'Datenbank-Passwort',

                'app_tabs' => [
                    'more_info' => 'Weitere Informationen',
                    'broadcasting_title' => 'Broadcasting, Caching, Session &amp; Queue',
                    'broadcasting_label' => 'Broadcast-Treiber',
                    'broadcasting_placeholder' => 'Broadcast-Treiber',
                    'cache_label' => 'Cache-Treiber',
                    'cache_placeholder' => 'Cache-Treiber',
                    'session_label' => 'Session-Treiber',
                    'session_placeholder' => 'Session-Treiber',
                    'queue_label' => 'Queue-Treiber',
                    'queue_placeholder' => 'Queue-Treiber',
                    'redis_label' => 'Redis-Treiber',
                    'redis_host' => 'Redis-Host',
                    'redis_password' => 'Redis-Passwort',
                    'redis_port' => 'Redis-Port',

                    'mail_label' => 'E-Mail',
                    'mail_driver_label' => 'Mail-Treiber',
                    'mail_driver_placeholder' => 'Mail-Treiber',
                    'mail_host_label' => 'Mail-Host',
                    'mail_host_placeholder' => 'Mail-Host',
                    'mail_port_label' => 'Mail-Port',
                    'mail_port_placeholder' => 'Mail-Port',
                    'mail_username_label' => 'Mail-Benutzername',
                    'mail_username_placeholder' => 'Mail-Benutzername',
                    'mail_password_label' => 'Mail-Passwort',
                    'mail_password_placeholder' => 'Mail-Passwort',
                    'mail_encryption_label' => 'Mail-Verschlüsselung',
                    'mail_encryption_placeholder' => 'Mail-Verschlüsselung',

                    'pusher_label' => 'Pusher',
                    'pusher_app_id_label' => 'Pusher App ID',
                    'pusher_app_id_palceholder' => 'Pusher App ID',
                    'pusher_app_key_label' => 'Pusher App Key',
                    'pusher_app_key_palceholder' => 'Pusher App Key',
                    'pusher_app_secret_label' => 'Pusher App Secret',
                    'pusher_app_secret_palceholder' => 'Pusher App Secret',
                ],
                'buttons' => [
                    'setup_database' => 'Datenbank einrichten',
                    'setup_application' => 'Anwendung einrichten',
                    'install' => 'Installieren',
                ],
            ],
        ],
        'classic' => [
            'templateTitle' => 'Schritt 3 | Umgebungseinstellungen | Klassischer Editor',
            'title' => 'Klassischer Umgebungseditor',
            'save' => '.env speichern',
            'back' => 'Assistent verwenden',
            'install' => 'Speichern und installieren',
        ],
        'success' => 'Ihre .env-Einstellungen wurden gespeichert.',
        'errors' => 'Die .env-Datei konnte nicht gespeichert werden. Bitte erstellen Sie sie manuell.',
    ],

    'install' => 'Installieren',

    /**
     *
     * Installed Log translations.
     *
     */
    'installed' => [
        'success_log_message' => 'Installationsassistent erfolgreich installiert am ',
    ],

    /**
     *
     * Final page translations.
     *
     */
    'final' => [
        'title' => 'Installation abgeschlossen',
        'templateTitle' => 'Installation abgeschlossen',
        'finished' => 'Die Anwendung wurde erfolgreich installiert.',
        'migration' => 'Migration &amp; Seed Konsolenausgabe:',
        'console' => 'Anwendungskonsolenausgabe:',
        'log' => 'Installationsprotokolleintrag:',
        'env' => 'Finale .env-Datei:',
        'exit' => 'Hier klicken zum Beenden',
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
        'title' => 'Aktualisierungsassistent',

        /**
         *
         * Welcome page translations for update feature.
         *
         */
        'welcome' => [
            'title'   => 'Willkommen zum Aktualisierungsassistenten',
            'message' => 'Willkommen zum Aktualisierungsassistenten.',
        ],

        /**
         *
         * Welcome page translations for update feature.
         *
         */
        'overview' => [
            'title'   => 'Übersicht',
            'message' => 'Es ist 1 Update verfügbar.|Es sind :number Updates verfügbar.',
            'install_updates' => "Updates installieren"
        ],

        /**
         *
         * Final page translations.
         *
         */
        'final' => [
            'title' => 'Abgeschlossen',
            'finished' => 'Die Datenbank der Anwendung wurde erfolgreich aktualisiert.',
            'exit' => 'Hier klicken zum Beenden',
        ],

        'log' => [
            'success_message' => 'Aktualisierungsassistent erfolgreich aktualisiert am ',
        ],
    ],
];
