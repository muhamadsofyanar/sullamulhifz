<?php

return [
    // "sync" tidak membutuhkan worker terpisah di Coolify. Ganti menjadi "queue"
    // hanya setelah worker queue dijalankan sebagai service terpisah.
    'dispatch_mode' => env('COMMUNICATION_DISPATCH_MODE', 'sync'),
    'timeout' => (int) env('COMMUNICATION_HTTP_TIMEOUT', 15),
    'default_country_code' => env('COMMUNICATION_DEFAULT_COUNTRY_CODE', '62'),
    'webhook_secret' => env('COMMUNICATION_WEBHOOK_SECRET'),
    'retention_days' => (int) env('COMMUNICATION_RETENTION_DAYS', 365),

    'whatsapp' => [
        'starsender' => [
            'base_url' => env('STARSENDER_BASE_URL', 'https://api.starsender.online'),
            'api_key' => env('STARSENDER_API_KEY'),
            'delay_seconds' => (int) env('STARSENDER_DELAY_SECONDS', 1),
        ],
        'generic' => [
            'endpoint' => env('WHATSAPP_WEBHOOK_ENDPOINT'),
            'token' => env('WHATSAPP_WEBHOOK_TOKEN'),
            'authorization_header' => env('WHATSAPP_WEBHOOK_AUTH_HEADER', 'Authorization'),
            'format' => env('WHATSAPP_WEBHOOK_FORMAT', 'json'),
            'recipient_field' => env('WHATSAPP_WEBHOOK_RECIPIENT_FIELD', 'to'),
            'message_field' => env('WHATSAPP_WEBHOOK_MESSAGE_FIELD', 'message'),
            'reference_field' => env('WHATSAPP_WEBHOOK_REFERENCE_FIELD', 'reference_id'),
            'token_field' => env('WHATSAPP_WEBHOOK_TOKEN_FIELD'),
        ],
    ],

    'email' => [
        'mailketing' => [
            'endpoint' => env('MAILKETING_ENDPOINT', 'https://api.mailketing.co.id/api/v1/send'),
            'api_token' => env('MAILKETING_API_TOKEN'),
        ],
        'reply_to' => env('MAIL_REPLY_TO_ADDRESS'),
    ],
];
