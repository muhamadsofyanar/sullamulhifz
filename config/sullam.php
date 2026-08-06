<?php

return [
    'upload_max_kb' => (int) env('UPLOAD_MAX_KB', 25600),
    'media_retention_days' => (int) env('MEDIA_RETENTION_DAYS', 180),
    'statuses' => [
        'attendance' => ['present', 'late', 'permission', 'sick', 'absent'],
        'learning' => ['good', 'practice_needed', 'guidance_needed', 'special_correction'],
        'memorization' => ['fluent', 'fair', 'repeat_needed', 'postponed'],
        'murajaah' => ['maintained', 'strengthening_needed', 'reactivation_needed'],
    ],
];
