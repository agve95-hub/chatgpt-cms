<?php

return [
    'repo_root' => env('PIXELKRAFT_REPO_ROOT', storage_path('repos')),
    'github' => [
        'webhook_secret' => env('GITHUB_WEBHOOK_SECRET'),
        'token' => env('GITHUB_FINE_GRAINED_TOKEN'),
    ],
];
