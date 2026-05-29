<?php

return [
    'webhook_secret' => env('GITHUB_WEBHOOK_SECRET', ''),
    'directory' => env('DEPLOY_DIRECTORY', '/var/www/html/shift-nomi-hub'),
    'branch' => env('DEPLOY_BRANCH', 'main'),
    'is_active' => env('DEPLOY_ISACTIVE', true),
];
