<?php

return [

    /**
     *
     * תרגומים משותפים.
     *
     */
    'title'   => 'מתקין',
    'next'    => 'השלב הבא',
    'back'    => 'הקודם',
    'finish'  => 'התקן',
    'forms'   => [
        'errorTitle' => 'אירעו השגיאות הבאות:',
    ],

    /**
     *
     * תרגומים של דף הבית.
     *
     */
    'welcome' => [
        'templateTitle' => 'ברוכים הבאים',
        'title'         => 'מתקין',
        'message'       => 'אשף התקנה והגדרה קל.',
        'next'          => 'בדוק דרישות',
    ],

    /**
     *
     * תרגומים של דף הדרישות.
     *
     */
    'requirements' => [
        'templateTitle' => 'שלב 1 | דרישות השרת',
        'title'         => 'דרישות השרת',
        'next'          => 'בדוק הרשאות',
    ],

    /**
     *
     * תרגומים של דף ההרשאות.
     *
     */
    'permissions' => [
        'templateTitle' => 'שלב 2 | הרשאות',
        'title'         => 'הרשאות',
        'next'          => 'הגדר סביבה',
    ],

    /**
     *
     * תרגומים של דף הסביבה.
     *
     */
    'environment' => [
        'menu' => [
            'templateTitle'  => 'שלב 3 | הגדרות סביבה',
            'title'          => 'הגדרות סביבה',
            'desc'           => 'אנא בחר כיצד ברצונך להגדיר את קובץ <code>.env</code> של האפליקציה.',
            'wizard-button'  => 'הגדרה עם אשף',
            'classic-button' => 'עורך טקסט קלאסי',
        ],
        'wizard' => [
            'templateTitle' => 'שלב 3 | הגדרות סביבה | אשף מודרך',
            'title'         => 'אשף <code>.env</code> מודרך',
            'tabs'          => [
                'environment' => 'סביבה',
                'database'    => 'בסיס נתונים',
                'application' => 'אפליקציה',
            ],
            'form'          => [
                'name_required'                     => 'נדרש שם סביבה.',
                'app_name_label'                    => 'שם האפליקציה',
                'app_name_placeholder'              => 'שם האפליקציה',
                'app_environment_label'             => 'סביבת האפליקציה',
                'app_environment_label_local'       => 'מקומי',
                'app_environment_label_developement' => 'פיתוח',
                'app_environment_label_qa'          => 'QA',
                'app_environment_label_production'  => 'ייצור',
                'app_environment_label_other'       => 'אחר',
                'app_environment_placeholder_other' => 'הזן את הסביבה שלך...',
                'app_debug_label'                   => 'מצב ניפוי שגיאות',
                'app_debug_label_true'              => 'נכון',
                'app_debug_label_false'             => 'לא נכון',
                'app_log_level_label'               => 'רמת יומן אפליקציה',
                'app_log_level_label_debug'         => 'דיבוג',
                'app_log_level_label_info'          => 'מידע',
                'app_log_level_label_notice'        => 'הערה',
                'app_log_level_label_warning'       => 'אזהרה',
                'app_log_level_label_error'         => 'שגיאה',
                'app_log_level_label_critical'      => 'קריטי',
                'app_log_level_label_alert'         => 'התרעה',
                'app_log_level_label_emergency'     => 'חירום',
                'app_url_label'                     => 'URL האפליקציה',
                'app_url_placeholder'               => 'URL האפליקציה',
                'db_connection_label'               => 'חיבור למסד נתונים',
                'db_connection_label_mysql'         => 'mysql',
                'db_connection_label_sqlite'        => 'sqlite',
                'db_connection_label_pgsql'         => 'pgsql',
                'db_connection_label_sqlsrv'        => 'sqlsrv',
                'db_host_label'                     => 'שרת מסד הנתונים',
                'db_host_placeholder'               => 'שרת מסד הנתונים',
                'db_port_label'                     => 'פורט מסד הנתונים',
                'db_port_placeholder'               => 'פורט מסד הנתונים',
                'db_name_label'                     => 'שם מסד הנתונים',
                'db_name_placeholder'               => 'שם מסד הנתונים',
                'db_username_label'                 => 'שם משתמש למסד הנתונים',
                'db_username_placeholder'           => 'שם משתמש למסד הנתונים',
                'db_password_label'                 => 'סיסמת מסד הנתונים',
                'db_password_placeholder'           => 'סיסמת מסד הנתונים',

                'app_tabs' => [
                    'more_info'              => 'מידע נוסף',
                    'broadcasting_title'     => 'שידור, מטמון, מפגש ותור',
                    'broadcasting_label'     => 'נהג שידור',
                    'broadcasting_placeholder' => 'נהג שידור',
                    'cache_label'            => 'נהג מטמון',
                    'cache_placeholder'      => 'נהג מטמון',
                    'session_label'          => 'נהג מפגש',
                    'session_placeholder'    => 'נהג מפגש',
                    'queue_label'            => 'נהג תור',
                    'queue_placeholder'      => 'נהג תור',
                    'redis_label'            => 'נהג Redis',
                    'redis_host'             => 'שרת Redis',
                    'redis_password'         => 'סיסמת Redis',
                    'redis_port'             => 'פורט Redis',

                    'mail_label'               => 'דואר',
                    'mail_driver_label'        => 'נהג דואר',
                    'mail_driver_placeholder'  => 'נהג דואר',
                    'mail_host_label'          => 'שרת דואר',
                    'mail_host_placeholder'    => 'שרת דואר',
                    'mail_port_label'          => 'פורט דואר',
                    'mail_port_placeholder'    => 'פורט דואר',
                    'mail_username_label'      => 'שם משתמש דואר',
                    'mail_username_placeholder' => 'שם משתמש דואר',
                    'mail_password_label'      => 'סיסמת דואר',
                    'mail_password_placeholder' => 'סיסמת דואר',
                    'mail_encryption_label'    => 'הצפנת דואר',
                    'mail_encryption_placeholder' => 'הצפנת דואר',

                    'pusher_label'                => 'Pusher',
                    'pusher_app_id_label'         => 'מזהה אפליקציית Pusher',
                    'pusher_app_id_palceholder'   => 'מזהה אפליקציית Pusher',
                    'pusher_app_key_label'        => 'מפתח אפליקציית Pusher',
                    'pusher_app_key_palceholder'  => 'מפתח אפליקציית Pusher',
                    'pusher_app_secret_label'     => 'סוד אפליקציית Pusher',
                    'pusher_app_secret_palceholder' => 'סוד אפליקציית Pusher',
                ],
                'buttons' => [
                    'setup_database'    => 'הגדר מסד נתונים',
                    'setup_application' => 'הגדר אפליקציה',
                    'install'           => 'התקן',
                ],
            ],
        ],
        'classic' => [
            'templateTitle' => 'שלב 3 | הגדרות סביבה | עורך קלאסי',
            'title'         => 'עורך קלאסי לסביבה',
            'save'          => 'שמור .env',
            'back'          => 'השתמש באשף',
            'install'       => 'שמור והתקן',
        ],
        'success' => 'ההגדרות של קובץ .env נשמרו בהצלחה.',
        'errors'  => 'לא ניתן לשמור את קובץ .env. אנא צור אותו ידנית.',
    ],

    'install' => 'התקן',

    /**
     *
     * תרגומים של יומן ההתקנות.
     *
     */
    'installed' => [
        'success_log_message' => 'המתקין הותקן בהצלחה ב־ ',
    ],

    /**
     *
     * תרגומים של הדף הסופי.
     *
     */
    'final' => [
        'title'         => 'ההתקנה הושלמה',
        'templateTitle' => 'ההתקנה הושלמה',
        'finished'      => 'האפליקציה הותקנה בהצלחה.',
        'migration'     => 'פלט קונסול של הגירה ו-seed:',
        'console'       => 'פלט קונסול של האפליקציה:',
        'log'           => 'יומן התקנה:',
        'env'           => 'קובץ .env סופי:',
        'exit'          => 'לחץ כאן ליציאה',
    ],

    /**
     *
     * תרגומים של העדכון.
     *
     */
    'updater' => [
        /**
         *
         * תרגומים משותפים.
         *
         */
        'title' => 'עדכון',

        /**
         *
         * תרגומים של דף הברוכים הבאים של העדכון.
         *
         */
        'welcome' => [
            'title'   => 'ברוכים הבאים לעדכון',
            'message' => 'ברוכים הבאים לאשף העדכון.',
        ],

        /**
         *
         * תרגומים של דף הסקירה.
         *
         */
        'overview' => [
            'title'           => 'סקירה כללית',
            'message'         => 'יש 1 עדכון.|יש :number עדכונים.',
            'install_updates' => 'התקן עדכונים',
        ],

        /**
         *
         * תרגומים של הדף הסופי של העדכון.
         *
         */
        'final' => [
            'title'    => 'הושלם',
            'finished' => 'מסד הנתונים של האפליקציה עודכן בהצלחה.',
            'exit'     => 'לחץ כאן ליציאה',
        ],

        'log' => [
            'success_message' => 'המתקין עודכן בהצלחה ב־ ',
        ],
    ],
];
