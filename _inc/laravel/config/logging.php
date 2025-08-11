<?php

use App\Config\Constants\SettingsConstants;
use App\Logging\{RemoveTraceProcessor, TabLogs};
use Monolog\Handler\{
    NullHandler,
    StreamHandler,
    SyslogUdpHandler
};

return [
    'default' => env('LOG_CHANNEL', 'stack'),
    'channels' => [
        'stack' => [
            'driver'            => 'stack',
            'channels'          => [
                'volatile', 'short_lived', 'notice', 'warning', 'error', 'critical',
                SettingsConstants::WARN_TRACE, SettingsConstants::ERR_TRACE, SettingsConstants::CRT_TRACE
            ],
            'ignore_exceptions' => false,
        ],

        'volatile' => [
            'driver' => 'daily',
            'path'   => storage_path('logs/volatile.log'),
            'level'  => 'debug',
            'days'   => 2,
            'tap'    => [TabLogs::class]
        ],

        SettingsConstants::WARN_TRACE => [
            'driver' => 'daily',
            'path'   => storage_path('logs/' . SettingsConstants::WARN_TRACE . '.log'),
            'level'  => 'debug',
            'days'   => 2,
            'tap'    => [TabLogs::class]
        ],

        SettingsConstants::ERR_TRACE => [
            'driver' => 'daily',
            'path'   => storage_path('logs/' . SettingsConstants::ERR_TRACE . '.log'),
            'level'  => 'error',
            'days'   => 2,
            'tap'    => [TabLogs::class]
        ],

        'short_lived' => [
            'driver' => 'daily',
            'path'   => storage_path('logs/short_lived.log'),
            'level'  => 'info',
            'days'   => 7,
            'tap'    => [TabLogs::class]
        ],

        'notice' => [
            'driver' => 'daily',
            'path'   => storage_path('logs/notice.log'),
            'level'  => 'notice',
            'days'   => 14,
            'tap' => [
                RemoveTraceProcessor::class,
            ],
        ],

        'warning' => [
            'driver' => 'daily',
            'path'   => storage_path('logs/warning.log'),
            'level'  => 'warning',
            'days'   => 30,
            'tap' => [
                RemoveTraceProcessor::class,
            ],
        ],

        'error' => [
            'driver' => 'daily',
            'path'   => storage_path('logs/error.log'),
            'level'  => 'error',
            'days'   => 60,
            'tap' => [
                RemoveTraceProcessor::class,
            ],
        ],

        'critical' => [
            'driver' => 'single',
            'path'   => storage_path('logs/critical.log'),
            'level'  => 'critical',
            'tap' => [
                RemoveTraceProcessor::class,
            ],
        ],

        SettingsConstants::CRT_TRACE => [
            'driver' => 'daily',
            'path'   => storage_path('logs/' . SettingsConstants::CRT_TRACE . '.log'),
            'level'  => 'critical',
            'tap'    => [TabLogs::class]
        ],

        'slack' => [
            'driver' => 'slack',
            'url' => env('LOG_SLACK_WEBHOOK_URL'),
            'username' => 'Laravel Log',
            'emoji' => ':boom:',
            'level' => env('LOG_LEVEL', 'critical'),
        ],

        'papertrail' => [
            'driver' => 'monolog',
            'level' => env('LOG_LEVEL', 'debug'),
            'handler' => env('LOG_PAPERTRAIL_HANDLER', SyslogUdpHandler::class),
            'handler_with' => [
                'host' => env('PAPERTRAIL_URL'),
                'port' => env('PAPERTRAIL_PORT'),
                'connectionString' => 'tls://' . env('PAPERTRAIL_URL') . ':' . env('PAPERTRAIL_PORT'),
            ],
        ],

        'stderr' => [
            'driver' => 'monolog',
            'level' => env('LOG_LEVEL_STDERR', 'debug'),
            'handler' => StreamHandler::class,
            'formatter' => env('LOG_STDERR_FORMATTER'),
            'with' => [
                'stream' => 'php://stderr',
            ],
        ],

        'syslog' => [
            'driver' => 'syslog',
            'level' => env('LOG_LEVEL_SYS', 'debug'),
        ],

        'errorlog' => [
            'driver' => 'errorlog',
            'level' => env('LOG_LEVEL_ERR', 'debug'),
        ],

        'null' => [
            'driver' => 'monolog',
            'handler' => NullHandler::class,
        ],

        'emergency' => [
            'path' => storage_path('logs/laravel.log'),
        ],

        'PayTabs' => [
            'driver' => 'single',
            'path' => storage_path('logs/paytabs.log'),
            'level' => 'info',
        ],
    ],
    'deprecations' => [
        'channel' => env('LOG_DEPRECATIONS_CHANNEL', 'null'),
        'trace' => false,
    ],
];
