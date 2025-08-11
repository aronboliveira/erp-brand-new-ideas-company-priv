<?php

return [
    'default' => env('FILESYSTEM_DRIVER', 'local'),
    /*
    | Supported Drivers: "local", "ftp", "sftp", "s3"
    */
    'disks' => [

        'local' => [
            'driver' => 'local',
            'root' => storage_path('/'),
            'url' => env('APP_URL') . '/storage',
            'visibility' => 'public',
        ],

        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => env('APP_URL') . '/storage',
            'visibility' => 'public',
        ],

        's3' => [
            'driver' => 's3',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => env('AWS_BUCKET'),
            'url' => env('AWS_URL'),
            'endpoint' => env('AWS_ENDPOINT'),
            'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', false),
        ],

        'wasabi' => [
            'driver' => 's3',
            'key' => env('WAS_ACCESS_KEY_ID'),
            'secret' => env('WAS_SECRET_ACCESS_KEY'),
            'region' => env('WAS_DEFAULT_REGION'),
            'bucket' => env('WAS_BUCKET'),
            'endpoint' => env('WAS_URL')
        ],

    ],


    /*
    |--------------------------------------------------------------------------
    | Symbolic Links
    |--------------------------------------------------------------------------
    |
    */

    'links' => [
        public_path('storage') => storage_path('app/public'),
    ],

];
