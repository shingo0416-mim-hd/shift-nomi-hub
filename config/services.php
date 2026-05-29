<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    's3' => [
        'bucket-prefix' => env('AWS_BUCKET_PREFIX', 'dev'),
        'temporary-url-expires-days' => env('AWS_TEMPORARY_URL_EXPIRES_DAYS', 7),
        'image-base-url' => env('S3_IMAGE_BASE_URL'),
    ],

    'cloudfront' => [
        'distribution_id' => env('CLOUDFRONT_DISTRIBUTION_ID'),
        'region' => env('AWS_DEFAULT_REGION', 'ap-northeast-1'),
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
    ],

    // CLINE
    'cline' => [
        'host_url' => env('CLINE_HOST_URL', 'https://cline-app.com'),
    ],

    // LINE Messaging API
    'messaging-api' => [
        'base_url' => env('LINE_MESSAGING_API_BASE_URL', 'https://api.line.me'),
    ],

];
