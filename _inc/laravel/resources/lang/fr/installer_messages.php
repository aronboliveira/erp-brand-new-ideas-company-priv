<?php

return [

    /**
     *
     * Traductions partagées.
     *
     */
    'title'   => 'Programme d’installation',
    'next'    => 'Étape suivante',
    'back'    => 'Précédent',
    'finish'  => 'Installer',
    'forms'   => [
        'errorTitle' => 'Les erreurs suivantes se sont produites :',
    ],

    /**
     *
     * Traductions de la page d’accueil.
     *
     */
    'welcome' => [
        'templateTitle' => 'Bienvenue',
        'title'         => 'Programme d’installation',
        'message'       => 'Assistant d’installation et de configuration simple.',
        'next'          => 'Vérifier les prérequis',
    ],

    /**
     *
     * Traductions de la page des prérequis.
     *
     */
    'requirements' => [
        'templateTitle' => 'Étape 1 | Prérequis du serveur',
        'title'         => 'Prérequis du serveur',
        'next'          => 'Vérifier les autorisations',
    ],

    /**
     *
     * Traductions de la page des autorisations.
     *
     */
    'permissions' => [
        'templateTitle' => 'Étape 2 | Autorisations',
        'title'         => 'Autorisations',
        'next'          => 'Configurer l’environnement',
    ],

    /**
     *
     * Traductions de la page de configuration d’environnement.
     *
     */
    'environment' => [
        'menu' => [
            'templateTitle'  => 'Étape 3 | Paramètres de l’environnement',
            'title'          => 'Paramètres de l’environnement',
            'desc'           => 'Veuillez sélectionner comment vous souhaitez configurer le fichier <code>.env</code> de l’application.',
            'wizard-button'  => 'Configuration assistée',
            'classic-button' => 'Éditeur classique',
        ],
        'wizard' => [
            'templateTitle' => 'Étape 3 | Paramètres de l’environnement | Assistant guidé',
            'title'         => 'Assistant guidé de <code>.env</code>',
            'tabs'          => [
                'environment' => 'Environnement',
                'database'    => 'Base de données',
                'application' => 'Application',
            ],
            'form'          => [
                'name_required'                     => 'Un nom d’environnement est requis.',
                'app_name_label'                    => 'Nom de l’application',
                'app_name_placeholder'              => 'Nom de l’application',
                'app_environment_label'             => 'Environnement de l’application',
                'app_environment_label_local'       => 'Local',
                'app_environment_label_developement' => 'Développement',
                'app_environment_label_qa'          => 'QA',
                'app_environment_label_production'  => 'Production',
                'app_environment_label_other'       => 'Autre',
                'app_environment_placeholder_other' => 'Entrez votre environnement…',
                'app_debug_label'                   => 'Mode débogage',
                'app_debug_label_true'              => 'Vrai',
                'app_debug_label_false'             => 'Faux',
                'app_log_level_label'               => 'Niveau de journalisation de l’application',
                'app_log_level_label_debug'         => 'débogage',
                'app_log_level_label_info'          => 'info',
                'app_log_level_label_notice'        => 'notice',
                'app_log_level_label_warning'       => 'avertissement',
                'app_log_level_label_error'         => 'erreur',
                'app_log_level_label_critical'      => 'critique',
                'app_log_level_label_alert'         => 'alerte',
                'app_log_level_label_emergency'     => 'urgence',
                'app_url_label'                     => 'URL de l’application',
                'app_url_placeholder'               => 'URL de l’application',
                'db_connection_label'               => 'Connexion de la base de données',
                'db_connection_label_mysql'         => 'mysql',
                'db_connection_label_sqlite'        => 'sqlite',
                'db_connection_label_pgsql'         => 'pgsql',
                'db_connection_label_sqlsrv'        => 'sqlsrv',
                'db_host_label'                     => 'Hôte de la base de données',
                'db_host_placeholder'               => 'Hôte de la base de données',
                'db_port_label'                     => 'Port de la base de données',
                'db_port_placeholder'               => 'Port de la base de données',
                'db_name_label'                     => 'Nom de la base de données',
                'db_name_placeholder'               => 'Nom de la base de données',
                'db_username_label'                 => 'Nom d’utilisateur de la base de données',
                'db_username_placeholder'           => 'Nom d’utilisateur de la base de données',
                'db_password_label'                 => 'Mot de passe de la base de données',
                'db_password_placeholder'           => 'Mot de passe de la base de données',

                'app_tabs' => [
                    'more_info'              => 'Plus d’informations',
                    'broadcasting_title'     => 'Diffusion, mise en cache, session et file d’attente',
                    'broadcasting_label'     => 'Pilote de diffusion',
                    'broadcasting_placeholder' => 'Pilote de diffusion',
                    'cache_label'            => 'Pilote de cache',
                    'cache_placeholder'      => 'Pilote de cache',
                    'session_label'          => 'Pilote de session',
                    'session_placeholder'    => 'Pilote de session',
                    'queue_label'            => 'Pilote de file d’attente',
                    'queue_placeholder'      => 'Pilote de file d’attente',
                    'redis_label'            => 'Pilote Redis',
                    'redis_host'             => 'Hôte Redis',
                    'redis_password'         => 'Mot de passe Redis',
                    'redis_port'             => 'Port Redis',

                    'mail_label'               => 'Courriel',
                    'mail_driver_label'        => 'Pilote de messagerie',
                    'mail_driver_placeholder'  => 'Pilote de messagerie',
                    'mail_host_label'          => 'Hôte de messagerie',
                    'mail_host_placeholder'    => 'Hôte de messagerie',
                    'mail_port_label'          => 'Port de messagerie',
                    'mail_port_placeholder'    => 'Port de messagerie',
                    'mail_username_label'      => 'Nom d’utilisateur du courriel',
                    'mail_username_placeholder' => 'Nom d’utilisateur du courriel',
                    'mail_password_label'      => 'Mot de passe du courriel',
                    'mail_password_placeholder' => 'Mot de passe du courriel',
                    'mail_encryption_label'    => 'Chiffrement du courriel',
                    'mail_encryption_placeholder' => 'Chiffrement du courriel',

                    'pusher_label'                => 'Pusher',
                    'pusher_app_id_label'         => 'ID de l’application Pusher',
                    'pusher_app_id_palceholder'   => 'ID de l’application Pusher',
                    'pusher_app_key_label'        => 'Clé de l’application Pusher',
                    'pusher_app_key_palceholder'  => 'Clé de l’application Pusher',
                    'pusher_app_secret_label'     => 'Secret de l’application Pusher',
                    'pusher_app_secret_palceholder' => 'Secret de l’application Pusher',
                ],
                'buttons' => [
                    'setup_database'    => 'Configurer la base de données',
                    'setup_application' => 'Configurer l’application',
                    'install'           => 'Installer',
                ],
            ],
        ],
        'classic' => [
            'templateTitle' => 'Étape 3 | Paramètres de l’environnement | Éditeur classique',
            'title'         => 'Éditeur classique de l’environnement',
            'save'          => 'Enregistrer .env',
            'back'          => 'Utiliser l’assistant',
            'install'       => 'Enregistrer et installer',
        ],
        'success' => 'Les paramètres de votre fichier .env ont été enregistrés.',
        'errors'  => 'Impossible d’enregistrer le fichier .env. Veuillez le créer manuellement.',
    ],

    'install' => 'Installer',

    /**
     *
     * Traductions de l’historique des installations.
     *
     */
    'installed' => [
        'success_log_message' => 'Installation terminée avec succès le ',
    ],

    /**
     *
     * Traductions de la page finale.
     *
     */
    'final' => [
        'title'         => 'Installation terminée',
        'templateTitle' => 'Installation terminée',
        'finished'      => 'L’application a été installée avec succès.',
        'migration'     => 'Sortie de la console de migration et seed :',
        'console'       => 'Sortie de la console de l’application :',
        'log'           => 'Journal d’installation :',
        'env'           => 'Fichier .env final :',
        'exit'          => 'Cliquez ici pour quitter',
    ],

    /**
     *
     * Traductions spécifiques à la mise à jour.
     *
     */
    'updater' => [
        /**
         *
         * Traductions partagées.
         *
         */
        'title' => 'Mise à jour',

        /**
         *
         * Traductions de la page d’accueil du module de mise à jour.
         *
         */
        'welcome' => [
            'title'   => 'Bienvenue dans le module de mise à jour',
            'message' => 'Bienvenue dans l’assistant de mise à jour.',
        ],

        /**
         *
         * Traductions de la page d’aperçu.
         *
         */
        'overview' => [
            'title'           => 'Aperçu',
            'message'         => 'Il y a 1 mise à jour.|Il y a :number mises à jour.',
            'install_updates' => 'Installer les mises à jour',
        ],

        /**
         *
         * Traductions de la page finale du module de mise à jour.
         *
         */
        'final' => [
            'title'    => 'Terminé',
            'finished' => 'La base de données de l’application a été mise à jour avec succès.',
            'exit'     => 'Cliquez ici pour quitter',
        ],

        'log' => [
            'success_message' => 'Mise à jour terminée avec succès le ',
        ],
    ],
];
