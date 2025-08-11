<?php

return [

    /**
     *
     * 共有翻訳
     *
     */
    'title'   => 'インストーラー',
    'next'    => '次のステップ',
    'back'    => '前へ',
    'finish'  => 'インストール',
    'forms'   => [
        'errorTitle' => '以下のエラーが発生しました：',
    ],

    /**
     *
     * ホームページの翻訳
     *
     */
    'welcome' => [
        'templateTitle' => 'ようこそ',
        'title'         => 'インストーラー',
        'message'       => '簡単なインストールおよび設定ウィザード。',
        'next'          => '要件を確認',
    ],

    /**
     *
     * 要件ページの翻訳
     *
     */
    'requirements' => [
        'templateTitle' => 'ステップ1 | サーバー要件',
        'title'         => 'サーバー要件',
        'next'          => '権限を確認',
    ],

    /**
     *
     * 権限ページの翻訳
     *
     */
    'permissions' => [
        'templateTitle' => 'ステップ2 | 権限',
        'title'         => '権限',
        'next'          => '環境を設定',
    ],

    /**
     *
     * 環境ページの翻訳
     *
     */
    'environment' => [
        'menu' => [
            'templateTitle'  => 'ステップ3 | 環境設定',
            'title'          => '環境設定',
            'desc'           => 'アプリケーションの<code>.env</code>ファイルをどのように設定するか選択してください。',
            'wizard-button'  => 'ウィザード設定',
            'classic-button' => 'クラシックエディター',
        ],
        'wizard' => [
            'templateTitle' => 'ステップ3 | 環境設定 | ガイド付きウィザード',
            'title'         => 'ガイド付き<code>.env</code>ウィザード',
            'tabs'          => [
                'environment' => '環境',
                'database'    => 'データベース',
                'application' => 'アプリケーション',
            ],
            'form'          => [
                'name_required'                     => '環境名は必須です。',
                'app_name_label'                    => 'アプリ名',
                'app_name_placeholder'              => 'アプリ名',
                'app_environment_label'             => 'アプリ環境',
                'app_environment_label_local'       => 'ローカル',
                'app_environment_label_developement' => '開発',
                'app_environment_label_qa'          => 'QA',
                'app_environment_label_production'  => '本番',
                'app_environment_label_other'       => 'その他',
                'app_environment_placeholder_other' => '環境を入力してください...',
                'app_debug_label'                   => 'アプリデバッグ',
                'app_debug_label_true'              => '有効',
                'app_debug_label_false'             => '無効',
                'app_log_level_label'               => 'アプリログレベル',
                'app_log_level_label_debug'         => 'デバッグ',
                'app_log_level_label_info'          => '情報',
                'app_log_level_label_notice'        => '通知',
                'app_log_level_label_warning'       => '警告',
                'app_log_level_label_error'         => 'エラー',
                'app_log_level_label_critical'      => '重大',
                'app_log_level_label_alert'         => 'アラート',
                'app_log_level_label_emergency'     => '緊急',
                'app_url_label'                     => 'アプリURL',
                'app_url_placeholder'               => 'アプリURL',
                'db_connection_label'               => 'データベース接続',
                'db_connection_label_mysql'         => 'mysql',
                'db_connection_label_sqlite'        => 'sqlite',
                'db_connection_label_pgsql'         => 'pgsql',
                'db_connection_label_sqlsrv'        => 'sqlsrv',
                'db_host_label'                     => 'データベースホスト',
                'db_host_placeholder'               => 'データベースホスト',
                'db_port_label'                     => 'データベースポート',
                'db_port_placeholder'               => 'データベースポート',
                'db_name_label'                     => 'データベース名',
                'db_name_placeholder'               => 'データベース名',
                'db_username_label'                 => 'データベースユーザー名',
                'db_username_placeholder'           => 'データベースユーザー名',
                'db_password_label'                 => 'データベースパスワード',
                'db_password_placeholder'           => 'データベースパスワード',

                'app_tabs' => [
                    'more_info'              => '詳細情報',
                    'broadcasting_title'     => 'ブロードキャスト、キャッシュ、セッション、キュー',
                    'broadcasting_label'     => 'ブロードキャストドライバー',
                    'broadcasting_placeholder' => 'ブロードキャストドライバー',
                    'cache_label'            => 'キャッシュドライバー',
                    'cache_placeholder'      => 'キャッシュドライバー',
                    'session_label'          => 'セッションドライバー',
                    'session_placeholder'    => 'セッションドライバー',
                    'queue_label'            => 'キュードライバー',
                    'queue_placeholder'      => 'キュードライバー',
                    'redis_label'            => 'Redisドライバー',
                    'redis_host'             => 'Redisホスト',
                    'redis_password'         => 'Redisパスワード',
                    'redis_port'             => 'Redisポート',

                    'mail_label'               => 'メール',
                    'mail_driver_label'        => 'メールドライバー',
                    'mail_driver_placeholder'  => 'メールドライバー',
                    'mail_host_label'          => 'メールホスト',
                    'mail_host_placeholder'    => 'メールホスト',
                    'mail_port_label'          => 'メールポート',
                    'mail_port_placeholder'    => 'メールポート',
                    'mail_username_label'      => 'メールユーザー名',
                    'mail_username_placeholder' => 'メールユーザー名',
                    'mail_password_label'      => 'メールパスワード',
                    'mail_password_placeholder' => 'メールパスワード',
                    'mail_encryption_label'    => 'メール暗号化',
                    'mail_encryption_placeholder' => 'メール暗号化',

                    'pusher_label'                => 'Pusher',
                    'pusher_app_id_label'         => 'PusherアプリID',
                    'pusher_app_id_palceholder'   => 'PusherアプリID',
                    'pusher_app_key_label'        => 'Pusherアプリキー',
                    'pusher_app_key_palceholder'  => 'Pusherアプリキー',
                    'pusher_app_secret_label'     => 'Pusherアプリシークレット',
                    'pusher_app_secret_palceholder' => 'Pusherアプリシークレット',
                ],
                'buttons' => [
                    'setup_database'    => 'データベースを設定',
                    'setup_application' => 'アプリケーションを設定',
                    'install'           => 'インストール',
                ],
            ],
        ],
        'classic' => [
            'templateTitle' => 'ステップ3 | 環境設定 | クラシックエディター',
            'title'         => 'クラシック環境エディター',
            'save'          => '.envを保存',
            'back'          => 'ウィザードを使用',
            'install'       => '保存してインストール',
        ],
        'success' => '.envファイルの設定が保存されました。',
        'errors'  => '.envファイルを保存できませんでした。手動で作成してください。',
    ],

    'install' => 'インストール',

    /**
     *
     * インストール済みログの翻訳
     *
     */
    'installed' => [
        'success_log_message' => 'インストーラーは正常にインストールされました ',
    ],

    /**
     *
     * 最終ページの翻訳
     *
     */
    'final' => [
        'title'         => 'インストール完了',
        'templateTitle' => 'インストール完了',
        'finished'      => 'アプリケーションのインストールが正常に完了しました。',
        'migration'     => 'マイグレーション＆シード コンソール出力：',
        'console'       => 'アプリケーション コンソール出力：',
        'log'           => 'インストールログエントリ：',
        'env'           => '最終 .env ファイル：',
        'exit'          => 'ここをクリックして終了',
    ],

    /**
     *
     * 更新特有の翻訳
     *
     */
    'updater' => [
        /**
         *
         * 共有翻訳
         *
         */
        'title' => '更新',

        /**
         *
         * 更新機能用ウェルカムページの翻訳
         *
         */
        'welcome' => [
            'title'   => 'アップデートへようこそ',
            'message' => 'アップデートウィザードへようこそ。',
        ],

        /**
         *
         * 概要ページの翻訳
         *
         */
        'overview' => [
            'title'           => '概要',
            'message'         => '1件の更新があります。|:number件の更新があります。',
            'install_updates' => '更新をインストール',
        ],

        /**
         *
         * 最終ページの翻訳
         *
         */
        'final' => [
            'title'    => '完了',
            'finished' => 'アプリケーションのデータベースが正常に更新されました。',
            'exit'     => 'ここをクリックして終了',
        ],

        'log' => [
            'success_message' => '更新が正常に完了しました ',
        ],
    ],
];
