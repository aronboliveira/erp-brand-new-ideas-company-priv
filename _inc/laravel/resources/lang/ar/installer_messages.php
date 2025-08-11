<?php

return [

    /**
     *
     * Shared translations.
     *
     */
    'title' => 'برنامج التثبيت',
    'next' => 'الخطوة التالية',
    'back' => 'السابق',
    'finish' => 'تثبيت',
    'forms' => [
        'errorTitle' => 'حدثت الأخطاء التالية:',
    ],

    /**
     *
     * Home page translations.
     *
     */
    'welcome' => [
        'templateTitle' => 'مرحباً',
        'title'   => 'برنامج التثبيت',
        'message' => 'معالج التثبيت والإعداد السهل.',
        'next'    => 'فحص المتطلبات',
    ],

    /**
     *
     * Requirements page translations.
     *
     */
    'requirements' => [
        'templateTitle' => 'الخطوة 1 | متطلبات السيرفر',
        'title' => 'متطلبات السيرفر',
        'next'    => 'فحص الأذونات',
    ],

    /**
     *
     * Permissions page translations.
     *
     */
    'permissions' => [
        'templateTitle' => 'الخطوة 2 | الأذونات',
        'title' => 'الأذونات',
        'next' => 'ضبط البيئة',
    ],

    /**
     *
     * Environment page translations.
     *
     */
    'environment' => [
        'menu' => [
            'templateTitle' => 'الخطوة 3 | إعدادات البيئة',
            'title' => 'إعدادات البيئة',
            'desc' => 'الرجاء اختيار طريقة تهيئة ملف <code>.env</code> للتطبيق.',
            'wizard-button' => 'الإعداد باستخدام المعالج',
            'classic-button' => 'المحرر النصي التقليدي',
        ],
        'wizard' => [
            'templateTitle' => 'الخطوة 3 | إعدادات البيئة | معالج موجه',
            'title' => 'معالج <code>.env</code> الموجه',
            'tabs' => [
                'environment' => 'البيئة',
                'database' => 'قاعدة البيانات',
                'application' => 'التطبيق'
            ],
            'form' => [
                'name_required' => 'اسم البيئة مطلوب.',
                'app_name_label' => 'اسم التطبيق',
                'app_name_placeholder' => 'اسم التطبيق',
                'app_environment_label' => 'بيئة التطبيق',
                'app_environment_label_local' => 'محلي',
                'app_environment_label_developement' => 'تطوير',
                'app_environment_label_qa' => 'ضبط الجودة',
                'app_environment_label_production' => 'إنتاج',
                'app_environment_label_other' => 'أخرى',
                'app_environment_placeholder_other' => 'أدخل بيئتك...',
                'app_debug_label' => 'تصحيح التطبيق',
                'app_debug_label_true' => 'تفعيل',
                'app_debug_label_false' => 'تعطيل',
                'app_log_level_label' => 'مستوى سجلات التطبيق',
                'app_log_level_label_debug' => 'تصحيح',
                'app_log_level_label_info' => 'معلومات',
                'app_log_level_label_notice' => 'ملاحظة',
                'app_log_level_label_warning' => 'تحذير',
                'app_log_level_label_error' => 'خطأ',
                'app_log_level_label_critical' => 'حرج',
                'app_log_level_label_alert' => 'تنبيه',
                'app_log_level_label_emergency' => 'طارئ',
                'app_url_label' => 'رابط التطبيق',
                'app_url_placeholder' => 'رابط التطبيق',
                'db_connection_label' => 'اتصال قاعدة البيانات',
                'db_connection_label_mysql' => 'mysql',
                'db_connection_label_sqlite' => 'sqlite',
                'db_connection_label_pgsql' => 'pgsql',
                'db_connection_label_sqlsrv' => 'sqlsrv',
                'db_host_label' => 'خادم قاعدة البيانات',
                'db_host_placeholder' => 'خادم قاعدة البيانات',
                'db_port_label' => 'منفذ قاعدة البيانات',
                'db_port_placeholder' => 'منفذ قاعدة البيانات',
                'db_name_label' => 'اسم قاعدة البيانات',
                'db_name_placeholder' => 'اسم قاعدة البيانات',
                'db_username_label' => 'اسم مستخدم قاعدة البيانات',
                'db_username_placeholder' => 'اسم مستخدم قاعدة البيانات',
                'db_password_label' => 'كلمة مرور قاعدة البيانات',
                'db_password_placeholder' => 'كلمة مرور قاعدة البيانات',

                'app_tabs' => [
                    'more_info' => 'مزيد من المعلومات',
                    'broadcasting_title' => 'البث، التخزين المؤقت، الجلسات، والطابور',
                    'broadcasting_label' => 'سائق البث',
                    'broadcasting_placeholder' => 'سائق البث',
                    'cache_label' => 'سائق التخزين المؤقت',
                    'cache_placeholder' => 'سائق التخزين المؤقت',
                    'session_label' => 'سائق الجلسات',
                    'session_placeholder' => 'سائق الجلسات',
                    'queue_label' => 'سائق الطابور',
                    'queue_placeholder' => 'سائق الطابور',
                    'redis_label' => 'سائق Redis',
                    'redis_host' => 'خادم Redis',
                    'redis_password' => 'كلمة مرور Redis',
                    'redis_port' => 'منفذ Redis',

                    'mail_label' => 'البريد',
                    'mail_driver_label' => 'سائق البريد',
                    'mail_driver_placeholder' => 'سائق البريد',
                    'mail_host_label' => 'خادم البريد',
                    'mail_host_placeholder' => 'خادم البريد',
                    'mail_port_label' => 'منفذ البريد',
                    'mail_port_placeholder' => 'منفذ البريد',
                    'mail_username_label' => 'اسم مستخدم البريد',
                    'mail_username_placeholder' => 'اسم مستخدم البريد',
                    'mail_password_label' => 'كلمة مرور البريد',
                    'mail_password_placeholder' => 'كلمة مرور البريد',
                    'mail_encryption_label' => 'تشفير البريد',
                    'mail_encryption_placeholder' => 'تشفير البريد',

                    'pusher_label' => 'Pusher',
                    'pusher_app_id_label' => 'معرف تطبيق Pusher',
                    'pusher_app_id_palceholder' => 'معرف تطبيق Pusher',
                    'pusher_app_key_label' => 'مفتاح تطبيق Pusher',
                    'pusher_app_key_palceholder' => 'مفتاح تطبيق Pusher',
                    'pusher_app_secret_label' => 'سر تطبيق Pusher',
                    'pusher_app_secret_palceholder' => 'سر تطبيق Pusher',
                ],
                'buttons' => [
                    'setup_database' => 'إعداد قاعدة البيانات',
                    'setup_application' => 'إعداد التطبيق',
                    'install' => 'تثبيت',
                ],
            ],
        ],
        'classic' => [
            'templateTitle' => 'الخطوة 3 | إعدادات البيئة | محرر تقليدي',
            'title' => 'محرر البيئة التقليدي',
            'save' => 'حفظ .env',
            'back' => 'استخدام معالج النماذج',
            'install' => 'حفظ وتثبيت',
        ],
        'success' => 'تم حفظ إعدادات ملف .env بنجاح.',
        'errors' => 'تعذر حفظ ملف .env، يرجى إنشائه يدوياً.',
    ],

    'install' => 'تثبيت',

    /**
     *
     * Installed Log translations.
     *
     */
    'installed' => [
        'success_log_message' => 'تم تثبيت البرنامج بنجاح في ',
    ],

    /**
     *
     * Final page translations.
     *
     */
    'final' => [
        'title' => 'اكتمل التثبيت',
        'templateTitle' => 'اكتمل التثبيت',
        'finished' => 'تم تثبيت التطبيق بنجاح.',
        'migration' => 'نتائج التهجير والبذار:',
        'console' => 'نتائج وحدة التحكم:',
        'log' => 'سجل التثبيت:',
        'env' => 'ملف .env النهائي:',
        'exit' => 'انقر هنا للخروج',
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
        'title' => 'أداة التحديث',

        /**
         *
         * Welcome page translations for update feature.
         *
         */
        'welcome' => [
            'title'   => 'مرحباً في أداة التحديث',
            'message' => 'مرحباً بك في معالج التحديث.',
        ],

        /**
         *
         * Welcome page translations for update feature.
         *
         */
        'overview' => [
            'title'   => 'نظرة عامة',
            'message' => 'يوجد تحديث واحد.|يوجد :number تحديثات.',
            'install_updates' => "تثبيت التحديثات"
        ],

        /**
         *
         * Final page translations.
         *
         */
        'final' => [
            'title' => 'مكتمل',
            'finished' => 'تم تحديث قاعدة بيانات التطبيق بنجاح.',
            'exit' => 'انقر هنا للخروج',
        ],

        'log' => [
            'success_message' => 'تم تحديث البرنامج بنجاح في ',
        ],
    ],
];
