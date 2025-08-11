<?php

return [

    /**
     *
     * 共享翻译
     *
     */
    'title'   => '安装程序',
    'next'    => '下一步',
    'back'    => '上一步',
    'finish'  => '安装',
    'forms'   => [
        'errorTitle' => '发生以下错误：',
    ],

    /**
     *
     * 主页翻译
     *
     */
    'welcome' => [
        'templateTitle' => '欢迎',
        'title'         => '安装程序',
        'message'       => '简易安装与设置向导。',
        'next'          => '检查需求',
    ],

    /**
     *
     * 服务器需求页面翻译
     *
     */
    'requirements' => [
        'templateTitle' => '第 1 步 | 服务器需求',
        'title'         => '服务器需求',
        'next'          => '检查权限',
    ],

    /**
     *
     * 权限页面翻译
     *
     */
    'permissions' => [
        'templateTitle' => '第 2 步 | 权限',
        'title'         => '权限',
        'next'          => '配置环境',
    ],

    /**
     *
     * 环境设置页面翻译
     *
     */
    'environment' => [
        'menu' => [
            'templateTitle'  => '第 3 步 | 环境设置',
            'title'          => '环境设置',
            'desc'           => '请选择如何配置应用的 <code>.env</code> 文件。',
            'wizard-button'  => '向导模式',
            'classic-button' => '经典编辑模式',
        ],
        'wizard' => [
            'templateTitle' => '第 3 步 | 环境设置 | 引导向导',
            'title'         => '引导式 <code>.env</code> 向导',
            'tabs'          => [
                'environment' => '环境',
                'database'    => '数据库',
                'application' => '应用',
            ],
            'form'          => [
                'name_required'                     => '必须填写环境名称。',
                'app_name_label'                    => '应用名称',
                'app_name_placeholder'              => '应用名称',
                'app_environment_label'             => '应用环境',
                'app_environment_label_local'       => '本地',
                'app_environment_label_developement' => '开发',
                'app_environment_label_qa'          => '测试',
                'app_environment_label_production'  => '生产',
                'app_environment_label_other'       => '其他',
                'app_environment_placeholder_other' => '请输入环境...',
                'app_debug_label'                   => '调试模式',
                'app_debug_label_true'              => '开启',
                'app_debug_label_false'             => '关闭',
                'app_log_level_label'               => '日志级别',
                'app_log_level_label_debug'         => '调试',
                'app_log_level_label_info'          => '信息',
                'app_log_level_label_notice'        => '通知',
                'app_log_level_label_warning'       => '警告',
                'app_log_level_label_error'         => '错误',
                'app_log_level_label_critical'      => '严重',
                'app_log_level_label_alert'         => '警报',
                'app_log_level_label_emergency'     => '紧急',
                'app_url_label'                     => '应用 URL',
                'app_url_placeholder'               => '应用 URL',
                'db_connection_label'               => '数据库连接',
                'db_connection_label_mysql'         => 'mysql',
                'db_connection_label_sqlite'        => 'sqlite',
                'db_connection_label_pgsql'         => 'pgsql',
                'db_connection_label_sqlsrv'        => 'sqlsrv',
                'db_host_label'                     => '数据库主机',
                'db_host_placeholder'               => '数据库主机',
                'db_port_label'                     => '数据库端口',
                'db_port_placeholder'               => '数据库端口',
                'db_name_label'                     => '数据库名称',
                'db_name_placeholder'               => '数据库名称',
                'db_username_label'                 => '数据库用户名',
                'db_username_placeholder'           => '数据库用户名',
                'db_password_label'                 => '数据库密码',
                'db_password_placeholder'           => '数据库密码',

                'app_tabs' => [
                    'more_info'              => '更多信息',
                    'broadcasting_title'     => '广播、缓存、会话与队列',
                    'broadcasting_label'     => '广播驱动',
                    'broadcasting_placeholder' => '广播驱动',
                    'cache_label'            => '缓存驱动',
                    'cache_placeholder'      => '缓存驱动',
                    'session_label'          => '会话驱动',
                    'session_placeholder'    => '会话驱动',
                    'queue_label'            => '队列驱动',
                    'queue_placeholder'      => '队列驱动',
                    'redis_label'            => 'Redis 驱动',
                    'redis_host'             => 'Redis 主机',
                    'redis_password'         => 'Redis 密码',
                    'redis_port'             => 'Redis 端口',

                    'mail_label'               => '邮件',
                    'mail_driver_label'        => '邮件驱动',
                    'mail_driver_placeholder'  => '邮件驱动',
                    'mail_host_label'          => '邮件主机',
                    'mail_host_placeholder'    => '邮件主机',
                    'mail_port_label'          => '邮件端口',
                    'mail_port_placeholder'    => '邮件端口',
                    'mail_username_label'      => '邮件用户名',
                    'mail_username_placeholder' => '邮件用户名',
                    'mail_password_label'      => '邮件密码',
                    'mail_password_placeholder' => '邮件密码',
                    'mail_encryption_label'    => '邮件加密',
                    'mail_encryption_placeholder' => '邮件加密',

                    'pusher_label'                => 'Pusher',
                    'pusher_app_id_label'         => 'Pusher 应用 ID',
                    'pusher_app_id_palceholder'   => 'Pusher 应用 ID',
                    'pusher_app_key_label'        => 'Pusher 应用 Key',
                    'pusher_app_key_palceholder'  => 'Pusher 应用 Key',
                    'pusher_app_secret_label'     => 'Pusher 应用 Secret',
                    'pusher_app_secret_palceholder' => 'Pusher 应用 Secret',
                ],
                'buttons' => [
                    'setup_database'    => '配置数据库',
                    'setup_application' => '配置应用',
                    'install'           => '安装',
                ],
            ],
        ],
        'classic' => [
            'templateTitle' => '第 3 步 | 环境设置 | 经典编辑器',
            'title'         => '经典环境编辑器',
            'save'          => '保存 .env',
            'back'          => '使用向导',
            'install'       => '保存并安装',
        ],
        'success' => '您的 .env 文件已保存。',
        'errors'  => '无法保存 .env 文件，请手动创建。',
    ],

    'install' => '安装',

    /**
     *
     * 已安装日志翻译
     *
     */
    'installed' => [
        'success_log_message' => '安装程序已于 ',
    ],

    /**
     *
     * 最终页面翻译
     *
     */
    'final' => [
        'title'         => '安装完成',
        'templateTitle' => '安装完成',
        'finished'      => '应用已成功安装。',
        'migration'     => '迁移及种子控制台输出：',
        'console'       => '应用控制台输出：',
        'log'           => '安装日志：',
        'env'           => '最终 .env 文件：',
        'exit'          => '点击这里退出',
    ],

    /**
     *
     * 更新程序翻译
     *
     */
    'updater' => [
        /**
         *
         * 共享翻译
         *
         */
        'title' => '更新程序',

        /**
         *
         * 更新欢迎页面翻译
         *
         */
        'welcome' => [
            'title'   => '欢迎使用更新程序',
            'message' => '欢迎使用更新向导。',
        ],

        /**
         *
         * 概览页面翻译
         *
         */
        'overview' => [
            'title'           => '概览',
            'message'         => '有 1 个更新。|有 :number 个更新。',
            'install_updates' => '安装更新',
        ],

        /**
         *
         * 更新最终页面翻译
         *
         */
        'final' => [
            'title'    => '完成',
            'finished' => '应用数据库已成功更新。',
            'exit'     => '点击这里退出',
        ],

        'log' => [
            'success_message' => '更新程序已于 ',
        ],
    ],

];
