<?php

$csv = static fn (?string $value): array => array_values(array_filter(array_map(
    static fn (string $item): string => strtolower(trim($item)),
    explode(',', (string) $value),
)));

$publicUrl = rtrim((string) env('PUBLIC_SITE_URL', env('APP_URL', 'https://sullamulhifz.or.id')), '/');
$portalBaseUrl = rtrim((string) env('PORTAL_BASE_URL', 'https://app.sullamulhifz.or.id'), '/');
$academyPublicUrl = rtrim((string) env('ACADEMY_PUBLIC_URL', $publicUrl.'/academy'), '/');

return [
    'public_url' => $publicUrl,
    'portal_base_url' => $portalBaseUrl,
    'portal_url' => env('PORTAL_URL', $portalBaseUrl.'/login'),
    'portal_host' => env('PORTAL_HOST', parse_url($portalBaseUrl, PHP_URL_HOST) ?: 'app.sullamulhifz.or.id'),
    'academy_public_url' => $academyPublicUrl,
    'academy_portal_url' => env('ACADEMY_PORTAL_URL', $portalBaseUrl.'/academy/belajar'),
    'academy_host' => env('ACADEMY_HOST', 'academy.sullamulhifz.or.id'),
    'public_hosts' => $csv(env('PUBLIC_HOSTS', 'sullamulhifz.or.id,www.sullamulhifz.or.id')),
    'legacy_hosts' => $csv(env('LEGACY_HOSTS', 'taysriulqurani.id,www.taysriulqurani.id')),
    'domain_separation_enabled' => filter_var(env('DOMAIN_SEPARATION_ENABLED', false), FILTER_VALIDATE_BOOL),
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
