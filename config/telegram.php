<?php

return [
    // The Telegram BOT API token
    'token' => env('TELEGRAM_TOKEN'),

    // Validate that incoming webhook requests come from Telegram's IP range
    'safe_mode' => env('APP_ENV', 'local') === 'production',

    // Log channel for Telegram errors
    'log_channel' => env('TELEGRAM_LOG_CHANNEL', 'null'),
];
