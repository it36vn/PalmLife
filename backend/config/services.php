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

    'store' => [
        'verification_mode' => env('STORE_VERIFICATION_MODE', 'strict'),
        'apple_issuer_id' => env('APPLE_IAP_ISSUER_ID'),
        'apple_key_id' => env('APPLE_IAP_KEY_ID'),
        'apple_private_key_path' => env('APPLE_IAP_PRIVATE_KEY_PATH'),
        'google_package_name' => env('GOOGLE_PLAY_PACKAGE_NAME'),
        'google_service_account_json' => env('GOOGLE_PLAY_SERVICE_ACCOUNT_JSON'),
    ],

    'ai' => [
        'provider' => env('AI_READING_PROVIDER', 'ollama'),
    ],

    'ollama' => [
        'base_url' => env('OLLAMA_BASE_URL', 'http://127.0.0.1:11434'),
        'model' => env('OLLAMA_VISION_MODEL', 'llava'),
    ],

    'openai' => [
        'api_key' => env('OPENAI_API_KEY'),
        'model' => env('OPENAI_MODEL', 'gpt-4.1-mini'),
        'base_url' => env('OPENAI_BASE_URL', 'https://api.openai.com/v1'),
    ],

];
