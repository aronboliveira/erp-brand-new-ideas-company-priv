<?php

return [

    'title'       => 'Kurulum',
    'next'        => 'Sonraki Adım',
    'back'        => 'Önceki',
    'finish'      => 'Yükle',
    'forms'       => [
        'errorTitle' => 'Aşağıdaki hatalar oluştu:',
    ],

    'welcome'     => [
        'templateTitle' => 'Hoş Geldiniz',
        'title'         => 'Kurulum',
        'message'       => 'Kolay Kurulum ve Ayar Sihirbazı.',
        'next'          => 'Gereksinimleri Kontrol Et',
    ],

    'requirements' => [
        'templateTitle' => 'Adım 1 | Sunucu Gereksinimleri',
        'title'         => 'Sunucu Gereksinimleri',
        'next'          => 'İzinleri Kontrol Et',
    ],

    'permissions' => [
        'templateTitle' => 'Adım 2 | İzinler',
        'title'         => 'İzinler',
        'next'          => 'Ortamı Yapılandır',
    ],

    'environment' => [
        'menu' => [
            'templateTitle'  => 'Adım 3 | Ortam Ayarları',
            'title'          => 'Ortam Ayarları',
            'desc'           => 'Lütfen uygulamanın <code>.env</code> dosyasını nasıl yapılandırmak istediğinizi seçin.',
            'wizard-button'  => 'Form Sihirbazı',
            'classic-button' => 'Klasik Editör',
        ],
        'wizard' => [
            'templateTitle' => 'Adım 3 | Ortam Ayarları | Rehberli Sihirbaz',
            'title'         => 'Rehberli <code>.env</code> Sihirbazı',
            'tabs'          => [
                'environment' => 'Ortam',
                'database'    => 'Veritabanı',
                'application' => 'Uygulama',
            ],
            'form' => [
                'name_required'                     => 'Bir ortam adı gereklidir.',
                'app_name_label'                    => 'Uygulama Adı',
                'app_name_placeholder'              => 'Uygulama Adı',
                'app_environment_label'             => 'Uygulama Ortamı',
                'app_environment_label_local'       => 'Yerel',
                'app_environment_label_developement' => 'Geliştirme',
                'app_environment_label_qa'          => 'QA',
                'app_environment_label_production'  => 'Üretim',
                'app_environment_label_other'       => 'Diğer',
                'app_environment_placeholder_other' => 'Ortamınızı girin...',
                'app_debug_label'                   => 'Hata Ayıklama',
                'app_debug_label_true'              => 'Evet',
                'app_debug_label_false'             => 'Hayır',
                'app_log_level_label'               => 'Günlük Seviyesi',
                'app_log_level_label_debug'         => 'debug',
                'app_log_level_label_info'          => 'info',
                'app_log_level_label_notice'        => 'notice',
                'app_log_level_label_warning'       => 'warning',
                'app_log_level_label_error'         => 'error',
                'app_log_level_label_critical'      => 'critical',
                'app_log_level_label_alert'         => 'alert',
                'app_log_level_label_emergency'     => 'emergency',
                'app_url_label'                     => 'Uygulama URL',
                'app_url_placeholder'               => 'Uygulama URL',
                'db_connection_label'               => 'Veritabanı Bağlantısı',
                'db_connection_label_mysql'         => 'mysql',
                'db_connection_label_sqlite'        => 'sqlite',
                'db_connection_label_pgsql'         => 'pgsql',
                'db_connection_label_sqlsrv'        => 'sqlsrv',
                'db_host_label'                     => 'Veritabanı Sunucusu',
                'db_host_placeholder'               => 'Veritabanı Sunucusu',
                'db_port_label'                     => 'Veritabanı Portu',
                'db_port_placeholder'               => 'Veritabanı Portu',
                'db_name_label'                     => 'Veritabanı Adı',
                'db_name_placeholder'               => 'Veritabanı Adı',
                'db_username_label'                 => 'Veritabanı Kullanıcı Adı',
                'db_username_placeholder'           => 'Veritabanı Kullanıcı Adı',
                'db_password_label'                 => 'Veritabanı Şifresi',
                'db_password_placeholder'           => 'Veritabanı Şifresi',

                'app_tabs' => [
                    'more_info'              => 'Daha Fazla Bilgi',
                    'broadcasting_title'     => 'Yayın, Önbellekleme, Oturum & Kuyruk',
                    'broadcasting_label'     => 'Yayın Sürücüsü',
                    'broadcasting_placeholder' => 'Yayın Sürücüsü',
                    'cache_label'            => 'Önbellek Sürücüsü',
                    'cache_placeholder'      => 'Önbellek Sürücüsü',
                    'session_label'          => 'Oturum Sürücüsü',
                    'session_placeholder'    => 'Oturum Sürücüsü',
                    'queue_label'            => 'Kuyruk Sürücüsü',
                    'queue_placeholder'      => 'Kuyruk Sürücüsü',
                    'redis_label'            => 'Redis Sürücüsü',
                    'redis_host'             => 'Redis Sunucusu',
                    'redis_password'         => 'Redis Şifresi',
                    'redis_port'             => 'Redis Portu',

                    'mail_label'               => 'E-posta',
                    'mail_driver_label'        => 'Posta Sürücüsü',
                    'mail_driver_placeholder'  => 'Posta Sürücüsü',
                    'mail_host_label'          => 'Posta Sunucusu',
                    'mail_host_placeholder'    => 'Posta Sunucusu',
                    'mail_port_label'          => 'Posta Portu',
                    'mail_port_placeholder'    => 'Posta Portu',
                    'mail_username_label'      => 'Posta Kullanıcı Adı',
                    'mail_username_placeholder' => 'Posta Kullanıcı Adı',
                    'mail_password_label'      => 'Posta Şifresi',
                    'mail_password_placeholder' => 'Posta Şifresi',
                    'mail_encryption_label'    => 'Posta Şifreleme',
                    'mail_encryption_placeholder' => 'Posta Şifreleme',

                    'pusher_label'                => 'Pusher',
                    'pusher_app_id_label'         => 'Pusher App ID',
                    'pusher_app_id_palceholder'   => 'Pusher App ID',
                    'pusher_app_key_label'        => 'Pusher App Key',
                    'pusher_app_key_palceholder'  => 'Pusher App Key',
                    'pusher_app_secret_label'     => 'Pusher App Secret',
                    'pusher_app_secret_palceholder' => 'Pusher App Secret',
                ],

                'buttons' => [
                    'setup_database'    => 'Veritabanını Kur',
                    'setup_application' => 'Uygulamayı Kur',
                    'install'           => 'Kur',
                ],
            ],
        ],

        'classic' => [
            'templateTitle' => 'Adım 3 | Ortam Ayarları | Klasik Editör',
            'title'         => 'Klasik Editör',
            'save'          => '.env Kaydet',
            'back'          => 'Sihirbazı Kullan',
            'install'       => 'Kaydet ve Kur',
        ],

        'success' => '.env ayarları kaydedildi.',
        'errors'  => '.env kaydedilemedi, lütfen manuel oluşturun.',
    ],

    'install'   => 'Kur',

    'installed' => [
        'success_log_message' => 'Kurulum başarıyla TAMAMLANDI:',
    ],

    'final'     => [
        'title'         => 'Kurulum Tamamlandı',
        'templateTitle' => 'Kurulum Tamamlandı',
        'finished'      => 'Uygulama başarıyla yüklendi.',
        'migration'     => 'Migration & Seed Konsol Çıktısı:',
        'console'       => 'Uygulama Konsol Çıktısı:',
        'log'           => 'Kurulum Günlüğü:',
        'env'           => 'Son .env Dosyası:',
        'exit'          => 'Çıkmak için tıklayın',
    ],

    'updater'   => [
        'title' => 'Güncelleme',

        'welcome' => [
            'title'   => 'Güncellemeye Hoş Geldiniz',
            'message' => 'Güncelleme sihirbazına hoş geldiniz.',
        ],

        'overview' => [
            'title'           => 'Genel Bakış',
            'message'         => '1 güncelleme var.|:number güncelleme var.',
            'install_updates' => 'Güncellemeleri Kur',
        ],

        'final' => [
            'title'    => 'Tamamlandı',
            'finished' => 'Veritabanı başarıyla güncellendi.',
            'exit'     => 'Çıkmak için tıklayın',
        ],

        'log' => [
            'success_message' => 'Güncelleme başarıyla TAMAMLANDI:',
        ],
    ],

];
