<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        foreach (DB::table('institutions')->pluck('id') as $institutionId) {
            DB::table('quran_audio_sources')->updateOrInsert(
                ['institution_id' => $institutionId, 'provider' => 'mp3quran', 'external_id' => '118'],
                [
                    'name' => 'Murattal Mahmoud Khalil Al-Husary',
                    'reciter_name' => 'Mahmoud Khalil Al-Husary',
                    'rewaya' => 'Hafs dari Asim — Murattal',
                    'base_url' => 'https://server13.mp3quran.net/husr/',
                    'metadata' => json_encode([
                        'timing_endpoint' => 'https://mp3quran.net/api/v3/ayat_timing',
                        'source_attribution' => 'MP3Quran.net',
                        'sync_scope' => 'Juz 30',
                        'learning_role' => 'Standar utama tahfizh',
                        'description' => 'Bacaan presisi, tempo terukur, dan tajwid kuat untuk talaqqi serta penguatan hafalan.',
                    ], JSON_UNESCAPED_UNICODE),
                    'is_default' => true,
                    'status' => 'active',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );

            DB::table('quran_audio_sources')->updateOrInsert(
                ['institution_id' => $institutionId, 'provider' => 'mp3quran', 'external_id' => '112'],
                [
                    'name' => 'Murattal Muhammad Siddiq Al-Minshawi',
                    'reciter_name' => 'Muhammad Siddiq Al-Minshawi',
                    'rewaya' => 'Hafs dari Asim — Murattal',
                    'base_url' => 'https://server10.mp3quran.net/minsh/',
                    'metadata' => json_encode([
                        'timing_endpoint' => 'https://mp3quran.net/api/v3/ayat_timing',
                        'source_attribution' => 'MP3Quran.net',
                        'sync_scope' => 'Juz 30',
                        'learning_role' => 'Pilihan murajaah dan tadabbur',
                        'description' => 'Tempo tenang dan bacaan menyentuh untuk murajaah, menyimak, dan tadabbur.',
                    ], JSON_UNESCAPED_UNICODE),
                    'is_default' => false,
                    'status' => 'active',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );

            $husaryId = DB::table('quran_audio_sources')
                ->where('institution_id', $institutionId)
                ->where('provider', 'mp3quran')
                ->where('external_id', '118')
                ->value('id');

            $ajmiId = DB::table('quran_audio_sources')
                ->where('institution_id', $institutionId)
                ->where('provider', 'mp3quran')
                ->where('external_id', '5')
                ->value('id');

            DB::table('quran_audio_sources')
                ->where('institution_id', $institutionId)
                ->where('provider', 'mp3quran')
                ->where('external_id', '!=', '118')
                ->update(['is_default' => false, 'updated_at' => now()]);

            if ($ajmiId) {
                DB::table('quran_audio_sources')->where('id', $ajmiId)->update([
                    'is_default' => false,
                    'status' => 'inactive',
                    'updated_at' => now(),
                ]);

                if ($husaryId) {
                    DB::table('quran_practice_presets')
                        ->where('institution_id', $institutionId)
                        ->where('quran_audio_source_id', $ajmiId)
                        ->update([
                            'quran_audio_source_id' => $husaryId,
                            'updated_at' => now(),
                        ]);
                }
            }
        }
    }

    public function down(): void
    {
        foreach (DB::table('institutions')->pluck('id') as $institutionId) {
            $ajmiId = DB::table('quran_audio_sources')
                ->where('institution_id', $institutionId)
                ->where('provider', 'mp3quran')
                ->where('external_id', '5')
                ->value('id');

            $husaryId = DB::table('quran_audio_sources')
                ->where('institution_id', $institutionId)
                ->where('provider', 'mp3quran')
                ->where('external_id', '118')
                ->value('id');

            if ($ajmiId) {
                DB::table('quran_audio_sources')->where('id', $ajmiId)->update([
                    'is_default' => true,
                    'status' => 'active',
                    'updated_at' => now(),
                ]);

                if ($husaryId) {
                    DB::table('quran_practice_presets')
                        ->where('institution_id', $institutionId)
                        ->where('quran_audio_source_id', $husaryId)
                        ->update([
                            'quran_audio_source_id' => $ajmiId,
                            'updated_at' => now(),
                        ]);
                }
            }

            DB::table('quran_audio_sources')
                ->where('institution_id', $institutionId)
                ->where('provider', 'mp3quran')
                ->whereIn('external_id', ['118', '112'])
                ->update([
                    'is_default' => false,
                    'status' => 'inactive',
                    'updated_at' => now(),
                ]);
        }
    }
};
