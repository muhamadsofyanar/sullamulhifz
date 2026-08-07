<?php

namespace App\Services;

use App\Models\AcademyLearningPath;
use App\Models\AcademyProgram;
use App\Models\CommunitySpace;
use App\Models\FeatureFlag;
use App\Models\Institution;
use App\Models\LaunchCheck;
use App\Models\QuranAudioSource;
use App\Models\QuranAyah;
use App\Models\QuranAyahTiming;
use App\Models\QuranSurah;
use Illuminate\Support\Facades\Schema;

class RoadmapStatusService
{
    /**
     * Roadmap percentages are intentionally conservative. A phase can only reach
     * 100% when implementation criteria AND its explicit production validation
     * checks are complete. This prevents a menu/table from being called "done".
     *
     * @return array<int, array<string, mixed>>
     */
    public function phases(Institution $institution): array
    {
        $institutionId = (int) $institution->id;

        return [
            1 => $this->phase(
                1,
                'Platform Core',
                'Fondasi teknis: autentikasi, permission, tenant, audit, media privat, notifikasi, feature flag, backup, keamanan dan API dasar.',
                $this->tableCriteria([
                    'roles', 'permissions', 'role_permissions', 'user_roles', 'activity_logs',
                    'media_assets', 'media_links', 'notifications', 'feature_flags', 'branches',
                ]),
                $this->manualCriteria($institutionId, ['security_https', 'security_roles', 'security_password', 'data_backup', 'data_restore', 'privacy_children']),
                'Luluskan security/restore/tenant isolation dan seluruh gate produksi sebelum dinyatakan 100%.',
            ),
            2 => $this->phase(
                2,
                'Full Qur’an Engine',
                '30 juz, 114 surah, 6.236 ayat, 604 halaman, 240 Rubu‘ al-Hizb, mushaf Academy, dua qari penuh, bookmark/riwayat baca dan playlist.',
                $this->fullQuranCriteria($institutionId),
                $this->manualCriteria($institutionId, [
                    'phase2_mushaf_desktop_mobile', 'phase2_audio_husary_minshawi',
                    'phase2_navigation_repeat', 'phase2_progress_resume',
                ]),
                'Sinkronkan korpus dan dua qari sampai penuh, lalu validasi mushaf/player di desktop dan ponsel.',
            ),
            3 => $this->phase(
                3,
                'Tahfizh Learning Engine',
                'Tahsīn, hafalan baru, murāja‘ah, target, talaqqi/tasmi‘, bantuan guru, preset audio dan histori penjagaan.',
                $this->tableCriteria([
                    'tahsin_records', 'memorization_records', 'murajaah_records', 'memorization_targets',
                    'quran_practice_presets', 'quran_practice_sessions', 'meetings', 'attendance_records',
                ]),
                $this->manualCriteria($institutionId, ['learning_teacher', 'learning_guardian']),
                'Lengkapi workflow talaqqi/tasmi‘ dan uji alur guru–wali pada data nyata.',
            ),
            4 => $this->phase(
                4,
                'Marhalah & Milestone',
                'Āyah sampai Ṣafḥatayn, histori keputusan marhalah, milestone surah/rubu‘/juz dan pemeriksaan penjagaan.',
                array_merge($this->tableCriteria(['marhalah_types', 'student_marhalah_histories', 'quran_rubus', 'memorization_targets']), [
                    ['label' => 'Milestone surah/rubu‘/juz terstruktur', 'passed' => Schema::hasTable('memorization_milestones')],
                    ['label' => 'Pemeriksaan penjagaan / retention exam', 'passed' => Schema::hasTable('memorization_retention_checks')],
                ]),
                $this->manualCriteria($institutionId, ['phase4_marhalah_flow', 'phase4_milestone_retention']),
                'Bangun keputusan naik/tetap/turun berbasis bukti dan milestone penjagaan lintas juz.',
            ),
            5 => $this->phase(
                5,
                'Academy LMS 2.0',
                'Course → module → lesson → activity/reflection, multimedia, learning path, bookmark, progres, resume dan sertifikat.',
                array_merge(
                    $this->tableCriteria([
                        'academy_programs', 'academy_modules', 'academy_lessons', 'academy_lesson_progress',
                        'academy_learning_paths', 'academy_learning_path_items', 'academy_bookmarks', 'academy_reflections',
                    ]),
                    [[
                        'label' => 'Minimal 4 program contoh terbit',
                        'passed' => AcademyProgram::query()->where('institution_id', $institutionId)->where('status', 'published')->count() >= 4,
                    ], [
                        'label' => 'Minimal 3 learning path terbit',
                        'passed' => AcademyLearningPath::query()->where('institution_id', $institutionId)->where('status', 'published')->count() >= 3,
                    ], [
                        'label' => 'Prerequisite lesson/path',
                        'passed' => Schema::hasTable('academy_prerequisites'),
                    ], [
                        'label' => 'Quiz / worksheet terstruktur',
                        'passed' => Schema::hasTable('academy_quizzes'),
                    ], [
                        'label' => 'Sertifikat penyelesaian',
                        'passed' => Schema::hasTable('academy_certificates'),
                    ]],
                ),
                $this->manualCriteria($institutionId, ['phase5_lms_resume', 'phase5_multimedia_learning']),
                'Tambahkan prerequisite, quiz/worksheet terstruktur dan sertifikat sebelum 100%.',
            ),
            6 => $this->phase(
                6,
                'Family & Teacher Ecosystem',
                'Parent Academy, Teacher Academy, parenting Qur’ani, STIFIn proporsional, aktivitas keluarga dan kompetensi guru.',
                [[
                    'label' => 'Parent Academy tersedia',
                    'passed' => AcademyProgram::query()->where('institution_id', $institutionId)->where('audience', 'guardian')->where('status', 'published')->exists(),
                ], [
                    'label' => 'Teacher Academy tersedia',
                    'passed' => AcademyProgram::query()->where('institution_id', $institutionId)->where('audience', 'teacher')->where('status', 'published')->exists(),
                ], ...$this->tableCriteria(['academy_recommendations', 'academy_lesson_progress']), [
                    'label' => 'Aktivitas keluarga terstruktur',
                    'passed' => Schema::hasTable('family_learning_activities'),
                ], [
                    'label' => 'Kompetensi/pelatihan guru terstruktur',
                    'passed' => Schema::hasTable('teacher_competencies'),
                ]],
                $this->manualCriteria($institutionId, ['phase6_parent_teacher_flow', 'phase6_stifin_guardrail']),
                'Lengkapi aktivitas keluarga, kompetensi guru dan evaluasi pembelajaran tanpa budaya ranking.',
            ),
            7 => $this->phase(
                7,
                'Personal Learning System',
                'Observasi metode belajar, respons nyata, preferensi, beban adaptif dan rekomendasi yang tetap dapat diedit guru.',
                array_merge($this->tableCriteria(['learning_observations', 'learning_insights', 'student_marhalah_histories']), [
                    ['label' => 'Mesin rekomendasi personal berbasis evidence', 'passed' => class_exists(\App\Services\PersonalLearningRecommendationService::class)],
                    ['label' => 'Teacher override tercatat', 'passed' => Schema::hasTable('learning_recommendation_reviews')],
                ]),
                $this->manualCriteria($institutionId, ['phase7_personalization_evidence', 'phase7_teacher_override']),
                'Bangun mesin rekomendasi berbasis evidence dengan teacher override dan guardrail STIFIn.',
            ),
            8 => $this->phase(
                8,
                'Character, Talent & Portfolio',
                'Pembinaan Jumat, adab/sirah, bakat, proyek dan portofolio perkembangan lintas tahun.',
                array_merge($this->tableCriteria(['friday_development_sessions', 'friday_session_targets', 'student_portfolios', 'programs', 'learning_groups']), [
                    ['label' => 'Rubrik/progres program bakat non-ranking', 'passed' => Schema::hasTable('talent_progress_records')],
                    ['label' => 'Proyek/portfolio evidence terhubung', 'passed' => Schema::hasTable('student_portfolio_evidence')],
                ]),
                $this->manualCriteria($institutionId, ['phase8_portfolio_flow', 'phase8_character_talent_flow']),
                'Lengkapi workflow program bakat dan portofolio yang dapat diwariskan antar tahun ajaran.',
            ),
            9 => $this->phase(
                9,
                'Insight, Automation & AI Assist',
                'Reminder penjagaan, rangkuman progres, draft rapor/catatan, pencarian dan AI assist dengan keputusan akhir tetap pada manusia.',
                array_merge($this->tableCriteria(['learning_insights', 'notifications', 'activity_logs']), [
                    ['label' => 'Reminder murāja‘ah otomatis', 'passed' => class_exists(\App\Services\MurajaahReminderService::class)],
                    ['label' => 'AI Assist dengan human review', 'passed' => Schema::hasTable('ai_assist_drafts')],
                    ['label' => 'Audit keputusan AI Assist', 'passed' => Schema::hasTable('ai_assist_reviews')],
                ]),
                $this->manualCriteria($institutionId, ['phase9_insight_accuracy', 'phase9_ai_human_review']),
                'Bangun otomatisasi dan AI assist dengan audit, human review dan evaluasi akurasi sebelum diaktifkan.',
            ),
            10 => $this->phase(
                10,
                'Ecosystem / SaaS',
                'Multi-cabang, multi-lembaga, onboarding, integrasi, object storage, mobile API/app, pembayaran dan community terkontrol.',
                array_merge(
                    $this->tableCriteria(['branches', 'integration_connections', 'community_spaces', 'community_posts']),
                    [[
                        'label' => 'Multi-cabang benar-benar diaktifkan',
                        'passed' => FeatureFlag::query()->where('institution_id', $institutionId)->where('feature_key', 'multi_branch')->where('enabled', true)->exists(),
                    ], [
                        'label' => 'Multi-lembaga benar-benar diaktifkan',
                        'passed' => FeatureFlag::query()->where('institution_id', $institutionId)->where('feature_key', 'multi_institution')->where('enabled', true)->exists(),
                    ], [
                        'label' => 'Minimal dua tenant untuk uji isolasi',
                        'passed' => Institution::query()->where('status', 'active')->count() >= 2,
                    ], [
                        'label' => 'Community moderasi aktif dan memiliki policy',
                        'passed' => FeatureFlag::query()->where('institution_id', $institutionId)->where('feature_key', 'community')->where('enabled', true)->exists() && Schema::hasTable('community_moderation_actions'),
                    ], [
                        'label' => 'Integrasi eksternal terkonfigurasi',
                        'passed' => Schema::hasTable('integration_connections') && \App\Models\IntegrationConnection::query()->where('institution_id', $institutionId)->where('status', 'active')->exists(),
                    ], [
                        'label' => 'Pembayaran opsional siap bila diaktifkan',
                        'passed' => Schema::hasTable('payment_transactions'),
                    ]],
                ),
                $this->manualCriteria($institutionId, ['phase10_tenant_isolation', 'phase10_integrations', 'phase10_scale_restore']),
                'Uji tenant isolation, integrasi, backup/restore dan beban produksi lintas lembaga sebelum 100%.',
            ),
        ];
    }

    /** @return array<string, mixed> */
    private function phase(int $number, string $name, string $purpose, array $implementation, array $validation, string $next): array
    {
        $implementationPct = $this->percentage($implementation);
        $validationPct = $this->percentage($validation);
        $overall = (int) round(($implementationPct * 0.8) + ($validationPct * 0.2));
        if ($implementationPct < 100 || $validationPct < 100) {
            $overall = min(99, $overall);
        }

        return [
            'number' => $number,
            'name' => $name,
            'purpose' => $purpose,
            'implementation' => $implementation,
            'validation' => $validation,
            'implementation_pct' => $implementationPct,
            'validation_pct' => $validationPct,
            'percent' => $overall,
            'status' => $overall === 100 ? 'complete' : ($overall >= 70 ? 'active' : ($overall >= 30 ? 'foundation' : 'planned')),
            'next' => $next,
        ];
    }

    /** @return array<int, array{label:string, passed:bool}> */
    private function fullQuranCriteria(int $institutionId): array
    {
        $surahs = Schema::hasTable('quran_surahs') ? QuranSurah::query()->count() : 0;
        $ayahs = Schema::hasTable('quran_ayahs') ? QuranAyah::query()->count() : 0;
        $juz = Schema::hasTable('quran_ayahs') ? QuranAyah::query()->whereNotNull('juz_number')->distinct()->count('juz_number') : 0;
        $pages = Schema::hasTable('quran_ayahs') ? QuranAyah::query()->whereNotNull('page_number')->distinct()->count('page_number') : 0;
        $rubus = Schema::hasTable('quran_ayahs') ? QuranAyah::query()->whereNotNull('hizb_quarter')->distinct()->count('hizb_quarter') : 0;

        $sources = Schema::hasTable('quran_audio_sources')
            ? QuranAudioSource::query()->where('institution_id', $institutionId)->where('status', 'active')->whereIn('external_id', ['118', '112'])->get()
            : collect();
        $audioComplete = $sources->count() === 2 && $sources->every(function (QuranAudioSource $source): bool {
            return QuranAyahTiming::query()->where('quran_audio_source_id', $source->id)->count() >= QuranCorpusSyncService::AYAH_COUNT;
        });

        return [
            ['label' => 'Tabel korpus dan riwayat baca tersedia', 'passed' => Schema::hasTable('quran_ayahs') && Schema::hasTable('quran_reading_progress')],
            ['label' => "114 surah ({$surahs}/114)", 'passed' => $surahs >= QuranCorpusSyncService::SURAH_COUNT],
            ['label' => "6.236 ayat ({$ayahs}/6236)", 'passed' => $ayahs >= QuranCorpusSyncService::AYAH_COUNT],
            ['label' => "30 juz ({$juz}/30)", 'passed' => $juz >= QuranCorpusSyncService::JUZ_COUNT],
            ['label' => "604 halaman Mushaf Madinah ({$pages}/604)", 'passed' => $pages >= QuranCorpusSyncService::PAGE_COUNT],
            ['label' => "240 Rubu‘ al-Hizb ({$rubus}/240)", 'passed' => $rubus >= QuranCorpusSyncService::HIZB_QUARTER_COUNT],
            ['label' => 'Al-Husary + Al-Minshawi masing-masing 6.236 timing', 'passed' => $audioComplete],
            ['label' => 'Mushaf Academy dan player v2.4 tersedia', 'passed' => is_file(resource_path('views/academy/audio-player.blade.php')) && is_file(public_path('js/academy-quran.js'))],
        ];
    }

    /** @return array<int, array{label:string, passed:bool}> */
    private function tableCriteria(array $tables): array
    {
        return array_map(fn (string $table): array => [
            'label' => 'Struktur '.$table,
            'passed' => Schema::hasTable($table),
        ], $tables);
    }

    /** @return array<int, array{label:string, passed:bool}> */
    private function manualCriteria(int $institutionId, array $keys): array
    {
        if (! Schema::hasTable('launch_checks')) {
            return array_map(fn (string $key): array => ['label' => $key, 'passed' => false], $keys);
        }

        $checks = LaunchCheck::query()
            ->where('institution_id', $institutionId)
            ->whereIn('check_key', $keys)
            ->get()
            ->keyBy('check_key');

        return array_map(function (string $key) use ($checks): array {
            $check = $checks->get($key);
            return [
                'label' => $check?->label ?? str_replace('_', ' ', $key),
                'passed' => $check?->status === 'done',
            ];
        }, $keys);
    }

    private function percentage(array $criteria): int
    {
        if ($criteria === []) {
            return 0;
        }

        $passed = count(array_filter($criteria, fn (array $item): bool => (bool) ($item['passed'] ?? false)));
        return (int) round(($passed / count($criteria)) * 100);
    }
}
