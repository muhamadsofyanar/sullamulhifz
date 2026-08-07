<?php

namespace App\Support;

use App\Models\FeatureFlag;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class Feature
{
    public static function enabled(string $key, ?int $institutionId = null, bool $default = false): bool
    {
        if (! Schema::hasTable('feature_flags')) {
            return $default;
        }

        return Cache::remember(
            'sullam:feature:'.($institutionId ?? 'global').':'.$key,
            now()->addMinutes(5),
            function () use ($key, $institutionId, $default): bool {
                $scoped = FeatureFlag::where('feature_key', $key)
                    ->where('institution_id', $institutionId)
                    ->value('enabled');

                if ($scoped !== null) {
                    return (bool) $scoped;
                }

                $global = FeatureFlag::where('feature_key', $key)
                    ->whereNull('institution_id')
                    ->value('enabled');

                return $global !== null ? (bool) $global : $default;
            },
        );
    }
}
