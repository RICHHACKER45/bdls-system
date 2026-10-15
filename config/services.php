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

    'emailjs' => [
        'service_id' => env('EMAILJS_SERVICE_ID'),
        'template_id' => env('EMAILJS_TEMPLATE_ID'),
        'public_key' => env('EMAILJS_PUBLIC_KEY'),
        'private_key' => env('EMAILJS_PRIVATE_KEY'),
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

    'google' => [
        'credentials_path' => env('GOOGLE_CREDENTIALS_PATH', 'app/private/google-credentials.json'),
        'credentials_base64' => env('GOOGLE_CREDENTIALS_BASE64'),
        'credentials_json' => env('GOOGLE_CREDENTIALS_JSON'),
        'vision_api_url' => env('GOOGLE_VISION_API_URL', 'https://vision.googleapis.com/v1/images:annotate'),
        'auth_scope' => env('GOOGLE_AUTH_SCOPE', 'https://www.googleapis.com/auth/cloud-platform'),
    ],

    'sms' => [
        'driver' => env('SMS_DRIVER', 'hybrid'), // 'hybrid', 'pinassms', 'fortmed', 'log'
        'max_length' => (int) env('SMS_MAX_LENGTH', 160),
        'curfew_start' => (int) env('SMS_CURFEW_START', 21), // 9:00 PM
        'curfew_end' => (int) env('SMS_CURFEW_END', 7),     // 7:00 AM
        'queue_delay_ms' => (int) env('SMS_QUEUE_DELAY_MS', 200000), // 200ms pause between queue blasts
        'smart_prefixes' => env('SMS_SMART_PREFIXES', '0907,0908,0909,0910,0911,0912,0913,0914,0918,0919,0920,0921,0928,0929,0930,0931,0938,0939,0940,0946,0947,0948,0949,0950,0951,0961,0963,0964,0968,0969,0970,0971,0981,0989,0998,0999'),
    ],

    'pinassms' => [
        'api_key' => env('PINASSMS_API_KEY'),
        'base_url' => env('PINASSMS_BASE_URL', 'https://pinassms.com/api'),
        'timeout' => (int) env('PINASSMS_TIMEOUT', 15),
    ],

    'fortmed' => [
        'api_key' => env('FORTMED_SMS_API_KEY'),
        'api_url' => env('FORTMED_SMS_API_URL', 'https://fortmed.org/web/FMCSMS/api/messages.php'),
        'from_number' => env('FORTMED_SMS_FROM_NUMBER', '+639189876543'),
        'sender_name' => env('FORTMED_SMS_SENDER_NAME', 'BDLS'),
        'timeout' => (int) env('FORTMED_SMS_TIMEOUT', 15),
    ],
];

