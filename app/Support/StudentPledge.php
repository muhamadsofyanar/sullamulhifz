<?php

namespace App\Support;

use App\Models\SystemSetting;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Schema;

class StudentPledge
{
    public const SETTING_KEY = 'student_pledge';

    public static function forInstitution(?int $institutionId): array
    {
        $defaults = config('student_pledge', []);

        if (! $institutionId || ! Schema::hasTable('system_settings')) {
            return $defaults;
        }

        $setting = SystemSetting::query()
            ->where('institution_id', $institutionId)
            ->where('key', self::SETTING_KEY)
            ->first();

        if (! $setting || blank($setting->value)) {
            return $defaults;
        }

        $custom = json_decode($setting->value, true);

        if (! is_array($custom)) {
            return $defaults;
        }

        return self::normalize($custom, $defaults);
    }

    public static function save(int $institutionId, array $data): void
    {
        abort_unless(Schema::hasTable('system_settings'), 503, 'Penyimpanan pengaturan belum tersedia. Jalankan migration v1.4.0 terlebih dahulu.');

        SystemSetting::updateOrCreate(
            ['institution_id' => $institutionId, 'key' => self::SETTING_KEY],
            [
                'group' => 'content',
                'type' => 'json',
                'value' => json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            ]
        );
    }

    public static function reset(int $institutionId): void
    {
        abort_unless(Schema::hasTable('system_settings'), 503, 'Penyimpanan pengaturan belum tersedia. Jalankan migration v1.4.0 terlebih dahulu.');

        SystemSetting::query()
            ->where('institution_id', $institutionId)
            ->where('key', self::SETTING_KEY)
            ->delete();
    }

    private static function normalize(array $custom, array $defaults): array
    {
        $result = $defaults;

        foreach (['eyebrow', 'title', 'institution_descriptor', 'institution_motto', 'intro', 'closing', 'aspiration'] as $key) {
            $value = Arr::get($custom, $key);
            if (is_string($value) && filled($value)) {
                $result[$key] = $value;
            }
        }

        if (isset($custom['items']) && is_array($custom['items']) && count($custom['items']) === 7 && collect($custom['items'])->every(fn ($item): bool => is_array($item))) {
            $result['items'] = array_map(
                fn (array $item, int $index): array => array_replace($defaults['items'][$index] ?? [], $item),
                array_values($custom['items']),
                array_keys(array_values($custom['items']))
            );
        }

        if (isset($custom['values']) && is_array($custom['values']) && count($custom['values']) === 5 && collect($custom['values'])->every(fn ($item): bool => is_array($item))) {
            $result['values'] = array_map(
                fn (array $item, int $index): array => array_replace($defaults['values'][$index] ?? [], $item),
                array_values($custom['values']),
                array_keys(array_values($custom['values']))
            );
        }

        if (isset($custom['practice']) && is_array($custom['practice']) && count($custom['practice']) === 3 && collect($custom['practice'])->every(fn ($item): bool => is_array($item))) {
            $result['practice'] = array_map(
                fn (array $item, int $index): array => array_replace($defaults['practice'][$index] ?? [], $item),
                array_values($custom['practice']),
                array_keys(array_values($custom['practice']))
            );
        }

        return $result;
    }
}
