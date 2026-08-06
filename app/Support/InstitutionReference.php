<?php

namespace App\Support;

use App\Models\Institution;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Schema;

class InstitutionReference
{
    public static function current(): array
    {
        $defaults = config('institution_reference', []);

        if (! Schema::hasTable('institutions')) {
            return $defaults;
        }

        $institution = Institution::query()->where('status', 'active')->first();

        if (! $institution) {
            return $defaults;
        }

        $custom = Arr::get($institution->settings ?? [], 'reference_profile', []);
        $profile = is_array($custom)
            ? array_replace_recursive($defaults, $custom)
            : $defaults;

        foreach (['name', 'code', 'slug', 'timezone', 'address', 'phone', 'email', 'legal_name'] as $field) {
            $value = $institution->{$field};

            if (filled($value)) {
                if (in_array($field, ['name'], true)) {
                    $profile[$field] = $value;
                } else {
                    $profile['identity'][$field] = $value;
                }
            }
        }

        return $profile;
    }
}
