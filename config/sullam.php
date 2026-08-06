<?php

return [
    'public_url' => env('PUBLIC_SITE_URL'),
    'portal_url' => env('PORTAL_URL'),
    'portal_host' => env('PORTAL_HOST', 'app.sullamulhifz.or.id'),
    'public_contact_email' => env('PUBLIC_CONTACT_EMAIL', 'info@sullamulhifz.or.id'),
    'upload_max_kb' => (int) env('UPLOAD_MAX_KB', 25600),
    'media_retention_days' => (int) env('MEDIA_RETENTION_DAYS', 180),
    'statuses' => [
        'attendance' => ['present', 'late', 'permission', 'sick', 'absent'],
        'learning' => ['good', 'practice_needed', 'guidance_needed', 'special_correction'],
        'memorization' => ['fluent', 'fair', 'repeat_needed', 'postponed'],
        'murajaah' => ['maintained', 'strengthening_needed', 'reactivation_needed'],
    ],
];
