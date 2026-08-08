<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('student_marhalah_histories')) {
            return;
        }

        Schema::table('student_marhalah_histories', function (Blueprint $table): void {
            if (! Schema::hasColumn('student_marhalah_histories', 'journey_juz_number')) {
                $table->unsignedTinyInteger('journey_juz_number')->nullable()->after('marhalah_type_id');
            }
            if (! Schema::hasColumn('student_marhalah_histories', 'stage_code')) {
                $table->string('stage_code', 60)->nullable()->after('journey_juz_number');
            }
            if (! Schema::hasColumn('student_marhalah_histories', 'portion_label')) {
                $table->string('portion_label', 190)->nullable()->after('stage_code');
            }
            if (! Schema::hasColumn('student_marhalah_histories', 'cadence_mode')) {
                $table->string('cadence_mode', 30)->nullable()->after('reason');
            }
            if (! Schema::hasColumn('student_marhalah_histories', 'cadence_notes')) {
                $table->text('cadence_notes')->nullable()->after('cadence_mode');
            }
        });

        if (! Schema::hasTable('quran_journey_profiles')) {
            return;
        }

        $portionByCode = [
            'ayah' => '1 ayat atau lebih',
            'tsalatsiyyah' => '3 baris',
            'khamsiyyah' => '5 baris',
            'nisfiyyah' => '½ halaman',
            'safhah' => '1 halaman',
            'safhatayn' => '2 halaman',
        ];

        $profiles = DB::table('quran_journey_profiles')->orderBy('id')->get();
        foreach ($profiles as $profile) {
            $active = DB::table('student_marhalah_histories')
                ->where('student_id', $profile->student_id)
                ->where('status', 'active')
                ->orderByDesc('id')
                ->first();

            if (! $active) {
                continue;
            }

            $marhalahCode = DB::table('marhalah_types')->where('id', $active->marhalah_type_id)->value('code');
            $portionLabel = $portionByCode[$marhalahCode] ?? null;

            // v2.6.0 dapat membawa catatan Juz lama ke Juz baru. Karena pada saat itu
            // belum ada form untuk mengubah arahan tahap aktif, catatan non-kosong pada
            // history advance_by_juz aman dianggap sebagai catatan tahap sebelumnya.
            $legacyCarriedNote = $active->decision === 'advance_by_juz'
                && is_string($profile->cadence_notes)
                && trim($profile->cadence_notes) !== '';

            if ($legacyCarriedNote) {
                $previous = DB::table('student_marhalah_histories')
                    ->where('student_id', $profile->student_id)
                    ->where('status', 'completed')
                    ->orderByDesc('effective_until')
                    ->orderByDesc('id')
                    ->first();

                if ($previous) {
                    $previousCode = DB::table('marhalah_types')->where('id', $previous->marhalah_type_id)->value('code');
                    $previousJuz = match ($previousCode) {
                        'ayah' => 30,
                        'tsalatsiyyah' => 29,
                        'khamsiyyah' => 28,
                        'nisfiyyah' => 27,
                        'safhah' => 26,
                        default => null,
                    };
                    DB::table('student_marhalah_histories')->where('id', $previous->id)->update([
                        'journey_juz_number' => $previous->journey_juz_number ?? $previousJuz,
                        'stage_code' => $previous->stage_code ?? null,
                        'portion_label' => $previous->portion_label ?? ($portionByCode[$previousCode] ?? null),
                        'cadence_mode' => $previous->cadence_mode ?? $profile->cadence_mode,
                        'cadence_notes' => $previous->cadence_notes ?? $profile->cadence_notes,
                        'updated_at' => now(),
                    ]);
                }

                DB::table('quran_journey_profiles')->where('id', $profile->id)->update([
                    'cadence_mode' => 'flexible',
                    'cadence_notes' => null,
                    'updated_at' => now(),
                ]);

                DB::table('student_marhalah_histories')->where('id', $active->id)->update([
                    'journey_juz_number' => $profile->current_juz_number,
                    'stage_code' => $profile->stage_code,
                    'portion_label' => $portionLabel,
                    'cadence_mode' => 'flexible',
                    'cadence_notes' => null,
                    'updated_at' => now(),
                ]);
                continue;
            }

            DB::table('student_marhalah_histories')->where('id', $active->id)->update([
                'journey_juz_number' => $profile->current_juz_number,
                'stage_code' => $profile->stage_code,
                'portion_label' => $portionLabel,
                'cadence_mode' => $profile->cadence_mode ?: 'flexible',
                'cadence_notes' => $profile->cadence_notes,
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('student_marhalah_histories')) {
            return;
        }

        Schema::table('student_marhalah_histories', function (Blueprint $table): void {
            foreach (['cadence_notes', 'cadence_mode', 'portion_label', 'stage_code', 'journey_juz_number'] as $column) {
                if (Schema::hasColumn('student_marhalah_histories', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
