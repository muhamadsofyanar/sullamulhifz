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
    'academy_portal_url' => env('ACADEMY_PORTAL_URL', 'https://'.env('ACADEMY_HOST', 'academy.sullamulhifz.or.id')),
    'academy_host' => env('ACADEMY_HOST', 'academy.sullamulhifz.or.id'),
    'api_host' => env('API_HOST', 'api.sullamulhifz.or.id'),
    'staging_host' => env('STAGING_HOST', 'staging.sullamulhifz.or.id'),
    'staging_enabled' => filter_var(env('STAGING_ENABLED', false), FILTER_VALIDATE_BOOL),
    'public_hosts' => $csv(env('PUBLIC_HOSTS', 'sullamulhifz.or.id,www.sullamulhifz.or.id')),
    'legacy_hosts' => $csv(env('LEGACY_HOSTS', 'taysriulqurani.id,www.taysriulqurani.id')),
    'domain_separation_enabled' => filter_var(env('DOMAIN_SEPARATION_ENABLED', false), FILTER_VALIDATE_BOOL),
    'public_contact_email' => env('PUBLIC_CONTACT_EMAIL', 'info@sullamulhifz.or.id'),
    'initial_institution_code' => env('INITIAL_INSTITUTION_CODE', 'ALINSYIRAH'),
    'upload_max_kb' => (int) env('UPLOAD_MAX_KB', 25600),
    'media_retention_days' => (int) env('MEDIA_RETENTION_DAYS', 180),
    // Mushaf Line Engine: KFGQPC V2 (1421H), 604-page Madani layout.
    // Data is synchronized at runtime so the application does not redistribute third-party layout files.
    'mushaf_line_layout' => env('MUSHAF_LINE_LAYOUT', 'kfgqpc-v2-1421h'),
    'mushaf_line_source_name' => env('MUSHAF_LINE_SOURCE_NAME', 'Madani Mushaf line-layout mirror'),
    'mushaf_line_source_ref' => env('MUSHAF_LINE_SOURCE_REF', 'main'),
    'mushaf_line_archive_url' => env('MUSHAF_LINE_ARCHIVE_URL', 'https://codeload.github.com/zonetecde/mushaf-layout/zip/refs/heads/main'),
    'mushaf_line_page_url' => env('MUSHAF_LINE_PAGE_URL', 'https://raw.githubusercontent.com/zonetecde/mushaf-layout/refs/heads/main/mushaf/page-%03d.json'),
    'trusted_proxies' => $csv(env('TRUSTED_PROXIES', '127.0.0.1,10.0.0.0/8,172.16.0.0/12,192.168.0.0/16,100.64.0.0/10')),
    'allowed_upload_mimes' => $csv(env('ALLOWED_UPLOAD_MIMES', 'application/pdf,image/jpeg,image/png,image/webp,audio/mpeg,audio/mp4,audio/x-m4a,audio/wav,video/mp4,video/quicktime,application/vnd.openxmlformats-officedocument.wordprocessingml.document')),
    'allowed_document_mimes' => $csv(env('ALLOWED_DOCUMENT_MIMES', 'application/pdf,image/jpeg,image/png,image/webp,application/vnd.openxmlformats-officedocument.wordprocessingml.document')),
    'allowed_evidence_mimes' => $csv(env('ALLOWED_EVIDENCE_MIMES', 'image/jpeg,image/png,image/webp,audio/mpeg,audio/mp4,audio/x-m4a,audio/wav,video/mp4,video/quicktime,application/pdf')),
    'statuses' => [
        'attendance' => ['present', 'late', 'permission', 'sick', 'absent'],
        'learning' => ['good', 'practice_needed', 'guidance_needed', 'special_correction'],
        'memorization' => ['fluent', 'fair', 'repeat_needed', 'postponed'],
        'murajaah' => ['maintained', 'strengthening_needed', 'reactivation_needed'],
    ],
];
