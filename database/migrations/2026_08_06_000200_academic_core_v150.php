<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('academic_years', function (Blueprint $table): void {
            if (! Schema::hasColumn('academic_years', 'code')) {
                $table->string('code', 30)->nullable()->after('name');
            }
            if (! Schema::hasColumn('academic_years', 'active_semester')) {
                $table->string('active_semester', 30)->default('semester_1')->after('end_date');
            }
            if (! Schema::hasColumn('academic_years', 'enrollment_status')) {
                $table->string('enrollment_status', 30)->default('closed')->after('active_semester');
            }
        });

        if (! Schema::hasTable('quran_rubus')) {
            Schema::create('quran_rubus', function (Blueprint $table): void {
                $table->id();
                $table->unsignedTinyInteger('juz_number')->default(30);
                $table->unsignedTinyInteger('rubu_number');
                $table->string('name');
                $table->unsignedSmallInteger('start_surah_id');
                $table->unsignedSmallInteger('end_surah_id');
                $table->text('description')->nullable();
                $table->string('status')->default('active');
                $table->timestamps();
                $table->unique(['juz_number', 'rubu_number']);
            });
        }

        if (! Schema::hasTable('memorization_targets')) {
            Schema::create('memorization_targets', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
                $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
                $table->foreignId('student_id')->constrained()->cascadeOnDelete();
                $table->foreignId('learning_group_id')->nullable()->constrained()->nullOnDelete();
                $table->foreignId('assigned_by_teacher_id')->nullable()->constrained('teachers')->nullOnDelete();
                $table->foreignId('quran_rubu_id')->nullable()->constrained('quran_rubus')->nullOnDelete();
                $table->unsignedSmallInteger('surah_id');
                $table->unsignedSmallInteger('start_verse');
                $table->unsignedSmallInteger('end_verse');
                $table->foreignId('marhalah_type_id')->nullable()->constrained()->nullOnDelete();
                $table->string('target_type')->default('new_memorization');
                $table->string('status')->default('active');
                $table->date('target_date')->nullable();
                $table->date('due_date')->nullable();
                $table->timestamp('completed_at')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
                $table->softDeletes();
                $table->foreign('surah_id')->references('id')->on('quran_surahs')->restrictOnDelete();
                $table->index(['institution_id', 'academic_year_id', 'status'], 'memorization_targets_scope_idx');
                $table->index(['student_id', 'status']);
            });
        }

        if (! Schema::hasTable('learning_observations')) {
            Schema::create('learning_observations', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
                $table->foreignId('academic_year_id')->nullable()->constrained()->nullOnDelete();
                $table->foreignId('student_id')->constrained()->cascadeOnDelete();
                $table->foreignId('teacher_id')->nullable()->constrained()->nullOnDelete();
                $table->string('category')->default('learning_method');
                $table->string('method_name');
                $table->string('context')->nullable();
                $table->text('response')->nullable();
                $table->string('effectiveness')->nullable();
                $table->timestamp('observed_at')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
                $table->index(['student_id', 'observed_at']);
            });
        }

        $rubus = [
            ['juz_number'=>30,'rubu_number'=>1,'name'=>"Rubu' 1 — An-Nas sampai Al-Qori'ah",'start_surah_id'=>114,'end_surah_id'=>101,'description'=>"Milestone An-Nas hingga Al-Qori'ah",'status'=>'active'],
            ['juz_number'=>30,'rubu_number'=>2,'name'=>"Rubu' 2 — Al-'Adiyat sampai Al-Insyirah",'start_surah_id'=>100,'end_surah_id'=>94,'description'=>"Milestone Al-'Adiyat hingga Al-Insyirah",'status'=>'active'],
            ['juz_number'=>30,'rubu_number'=>3,'name'=>"Rubu' 3 — Adh-Dhuha sampai Al-Balad",'start_surah_id'=>93,'end_surah_id'=>90,'description'=>"Milestone Adh-Dhuha hingga Al-Balad",'status'=>'active'],
            ['juz_number'=>30,'rubu_number'=>4,'name'=>"Rubu' 4 — Al-Fajr sampai Al-A'la",'start_surah_id'=>89,'end_surah_id'=>87,'description'=>"Milestone Al-Fajr hingga Al-A'la",'status'=>'active'],
            ['juz_number'=>30,'rubu_number'=>5,'name'=>"Rubu' 5 — Ath-Thariq sampai Al-Insyiqaq",'start_surah_id'=>86,'end_surah_id'=>84,'description'=>"Milestone Ath-Thariq hingga Al-Insyiqaq",'status'=>'active'],
            ['juz_number'=>30,'rubu_number'=>6,'name'=>"Rubu' 6 — Al-Muthaffifin sampai Al-Infithar",'start_surah_id'=>83,'end_surah_id'=>82,'description'=>"Milestone Al-Muthaffifin hingga Al-Infithar",'status'=>'active'],
            ['juz_number'=>30,'rubu_number'=>7,'name'=>"Rubu' 7 — At-Takwir sampai 'Abasa",'start_surah_id'=>81,'end_surah_id'=>80,'description'=>"Milestone At-Takwir hingga 'Abasa",'status'=>'active'],
            ['juz_number'=>30,'rubu_number'=>8,'name'=>"Rubu' 8 — An-Nazi'at sampai An-Naba'",'start_surah_id'=>79,'end_surah_id'=>78,'description'=>"Milestone An-Nazi'at hingga An-Naba'",'status'=>'active'],
        ];

        foreach ($rubus as $rubu) {
            DB::table('quran_rubus')->updateOrInsert(
                ['juz_number'=>$rubu['juz_number'], 'rubu_number'=>$rubu['rubu_number']],
                [...$rubu, 'updated_at'=>now(), 'created_at'=>now()]
            );
        }

        DB::table('academic_years')->orderBy('id')->get()->each(function ($year): void {
            $code = preg_replace('/[^0-9]+/', '-', (string) $year->name);
            $code = trim((string) $code, '-');
            DB::table('academic_years')->where('id', $year->id)->update([
                'code' => $year->code ?: ($code ?: 'TA-'.$year->id),
                'active_semester' => $year->active_semester ?: 'semester_1',
                'enrollment_status' => $year->enrollment_status ?: 'closed',
            ]);
        });

        DB::table('institutions')->orderBy('id')->get()->each(function ($institution): void {
            $settings = json_decode((string) ($institution->settings ?? '{}'), true);
            if (! is_array($settings)) {
                $settings = [];
            }
            $defaults = [
                'master_brand' => 'Sullamul Ḥifẓ',
                'tagline' => 'Bukan Sekadar Hafal, Tapi KUAT',
                'brand_relation' => 'TPA Al-Insyirah — Powered by Sullamul Ḥifẓ',
                'leader_name' => null,
                'vision' => null,
                'mission' => null,
                'report_footer' => 'Jejak perkembangan, bukan perbandingan dengan santri lain.',
                'profile_completed' => false,
            ];
            DB::table('institutions')->where('id', $institution->id)->update([
                'settings' => json_encode(array_replace($defaults, $settings), JSON_UNESCAPED_UNICODE),
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('learning_observations');
        Schema::dropIfExists('memorization_targets');
        Schema::dropIfExists('quran_rubus');

        Schema::table('academic_years', function (Blueprint $table): void {
            foreach (['code', 'active_semester', 'enrollment_status'] as $column) {
                if (Schema::hasColumn('academic_years', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
