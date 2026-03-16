<?php

use App\Config\Constants\MiddlewaresConstants;

return [

    /*
    |-------------------------------------
    | Messenger display name
    |-------------------------------------
    */
    'name' => env('CHATIFY_NAME', 'Messenger'),


    /*
   |--------------------------------------------------------------------------
   | Package path
   |--------------------------------------------------------------------------
   |
   | This value is the path of the package or in other meaning, it is the prefix
   | of all the registered routes in this package.
   |
   | e.g. : app.test/chatify
   */

    'path' => env('CHATIFY_PATH', 'chats'),

    /*
    |-------------------------------------
    | Routes configurations
    |-------------------------------------
    */
    'routes' => [
        'prefix' => env('CHATIFY_ROUTES_PREFIX', 'chats'),
        'middleware' => env(
            'CHATIFY_ROUTES_MIDDLEWARE',
            [
                MiddlewaresConstants::WEB,
                MiddlewaresConstants::AUTH,
                MiddlewaresConstants::XSS,
                MiddlewaresConstants::PSR,
            ]
        ),
        'namespace' => env('CHATIFY_ROUTES_NAMESPACE', 'Chatify\Http\Controllers'),
    ],

    'api_routes' => [
        'prefix' => env('CHATIFY_API_ROUTES_PREFIX', 'api/chats'),
        'middleware' => env('CHATIFY_API_ROUTES_MIDDLEWARE', ['api', 'auth:sanctum']),
        'namespace' => env('CHATIFY_API_ROUTES_NAMESPACE', 'Chatify\Http\Controllers\Api'),
    ],


    /*
    |-------------------------------------
    | Pusher API credentials
    |-------------------------------------
    */
    'pusher' => [
        'key' => env('PUSHER_APP_KEY'),
        'secret' => env('PUSHER_APP_SECRET'),
        'app_id' => env('PUSHER_APP_ID'),
        'options' => (array)[
            'cluster' => env('PUSHER_APP_CLUSTER'),
            'useTLS' => env('PUSHER_APP_USETLS'),
        ],
    ],

    /*
    |-------------------------------------
    | User Avatar
    |-------------------------------------
    */
    'user_avatar' => [
        'folder' => 'uploads/avatar',
        'default' => 'avatar.png',
    ],

    /*
    |-------------------------------------
    | Attachments
    |-------------------------------------
    */
    'attachments' => [
        'folder' => 'attachments',
        'download_route_name' => 'attachments.download',
        'allowed_images' => (array)[
            'png',
            'jpg',
            'jpeg',
            'gif',
        ],
        'allowed_files' => (array)[
            'zip',
            'rar',
            'txt',
        ],
    ],
];
