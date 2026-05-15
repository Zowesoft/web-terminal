<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Route Prefix
    |--------------------------------------------------------------------------
    | The prefix used for the terminal routes.
    | Terminal will be accessible at: yoursite.com/{prefix}/terminal
    |
    */
    'prefix' => env('WEB_TERMINAL_PREFIX', 'admin'),

    /*
    |--------------------------------------------------------------------------
    | Middleware
    |--------------------------------------------------------------------------
    | Middleware applied to all terminal routes.
    | Add your own auth guards or middleware here.
    |
    */
    'middleware' => ['web', 'auth'],

    /*
    |--------------------------------------------------------------------------
    | Blacklisted Commands
    |--------------------------------------------------------------------------
    | These commands are always blocked regardless of who runs them.
    | Add any commands you want to permanently prevent here.
    |
    */
    'blacklist' => [
        'rm -rf /',
        'rm -rf *',
        'rm -rf ~',
        'mkfs',
        'dd if=',
        ':(){:|:&};:',
        'chmod -R 777 /',
        'wget http',
        'curl http',
        'nc ',
        'netcat',
        'python -c',
        'perl -e',
        'bash -i',
    ],

    /*
    |--------------------------------------------------------------------------
    | Shell PATH
    |--------------------------------------------------------------------------
    | Directories to look in when finding executables like git, composer, php.
    | Extend this if your server installs tools in non-standard locations.
    |
    */
    'path' => env('WEB_TERMINAL_PATH', '/usr/local/bin:/usr/bin:/bin:/usr/local/sbin:/usr/sbin'),

    /*
    |--------------------------------------------------------------------------
    | Command Timeout
    |--------------------------------------------------------------------------
    | Maximum seconds a command is allowed to run before being killed.
    |
    */
    'timeout' => env('WEB_TERMINAL_TIMEOUT', 120),

    /*
    |--------------------------------------------------------------------------
    | Token Column
    |--------------------------------------------------------------------------
    | The column name on the users table that stores the terminal token.
    |
    */
    'token_column' => 'terminal_token',

    /*
    |--------------------------------------------------------------------------
    | Admin Column
    |--------------------------------------------------------------------------
    | The column name on the users table that identifies admin users.
    |
    */
    'admin_column' => 'is_admin',

];
