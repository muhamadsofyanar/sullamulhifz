<?php

namespace Tests\Feature;

/** @phase 6.0 Free, Infaq & Distraction-Free Tahfizh regression */

use App\Models\Institution;
use App\Models\AcademyProgram;
use App\Models\QuranSurah;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use App\Services\BusinessBillingService;
use App\Services\DistractionFreeSubmissionService;
use App\Services\InfaqService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class FreeInfaqDistractionFreeV600Test extends TestCase
{
    use RefreshDatabase;

    public function test_v600_schema_and_new_routes_are_additive(): void
    {
        foreach (['student_memorization_focuses', 'student_memorization_assessments', 'infaq_transactions'] as $table) {
            $this->assertTrue(Schema::hasTable($table), "Tabel {$table} belum tersedia.");
        }
        foreach (['daily_decision', 'short_note', 'submission_key'] as $column) {
            $this->assertTrue(Schema::hasColumn('memorization_records', $column));
            $this->assertTrue(Schema::hasColumn('murajaah_records', $column));
        }

        foreach ([
            'teacher.tahfizh.quick-memorization.store',
            'teacher.tahfizh.quick-murajaah.store',
            'teacher.tahfizh.focus.update',
            'teacher.tahfizh.assessments.store',
            'infaq.index',
            'infaq.store',
            'admin.infaq.index',
            'admin.infaq.update',
        ] as $route) {
            $this->assertTrue(Route::has($route), "Rute {$route} belum tersedia.");
        }

        // Jalur rinci versi lama sengaja dipertahankan untuk asesmen/kasus khusus.
        $this->assertTrue(Route::has('teacher.tahfizh.memorization.store'));
        $this->assertTrue(Route::has('teacher.tahfizh.murajaah.store'));
        $this->assertTrue(Route::has('teacher.meetings.memorization.store'));
        $this->assertTrue(Route::has('teacher.meetings.murajaah.store'));
    }

    public function test_quick_submission_is_idempotent_and_schedules_review(): void
    {
        [$user, $student] = $this->teacherAndStudent('quick');
        QuranSurah::create([
            'id' => 1,
            'name_arabic' => 'الفاتحة',
            'name_latin' => 'Al-Fatihah',
            'verse_count' => 7,
            'sequence' => 1,
        ]);
        $payload = [
            'submission_key' => (string) Str::uuid(),
            'surah_id' => 1,
            'start_verse' => 1,
            'end_verse' => 7,
            'daily_decision' => 'kuatkan',
            'short_note' => 'Ulang sambungan ayat 5–7.',
        ];

        $service = app(DistractionFreeSubmissionService::class);
        $first = $service->recordMemorization($user, $student, $payload);
        $retry = $service->recordMemorization($user, $student, $payload);

        $this->assertSame($first->id, $retry->id);
        $this->assertSame('fair', $first->result);
        $this->assertSame('kuatkan', $first->daily_decision);
        $this->assertSame(today()->addDays(2)->toDateString(), $first->next_review_date->toDateString());
        $this->assertDatabaseCount('memorization_records', 1);
        $this->assertDatabaseCount('memorization_review_plans', 1);
    }

    public function test_infaq_retry_is_safe_per_user_and_never_changes_entitlements(): void
    {
        [$firstUser] = $this->teacherAndStudent('infaq-one');
        [$secondUser] = $this->teacherAndStudent('infaq-two');
        $key = (string) Str::uuid();
        $data = ['purpose' => 'technology', 'amount' => 50000, 'is_anonymous' => true];
        $service = app(InfaqService::class);
        $before = app(BusinessBillingService::class)->entitlements($firstUser);

        $first = $service->createPending($firstUser, $data, $key);
        $retry = $service->createPending($firstUser, $data, $key);
        $second = $service->createPending($secondUser, $data, $key);

        $this->assertSame($first->id, $retry->id);
        $this->assertNotSame($first->id, $second->id);
        $this->assertDatabaseCount('infaq_transactions', 2);
        $this->assertSame($before, app(BusinessBillingService::class)->entitlements($firstUser));
        $this->assertContains('learning_hub', $before);
        $this->assertSame('pending', $firstUser->infaqTransactions()->firstOrFail()->status);
    }

    public function test_infaq_retry_key_cannot_be_reused_for_a_different_payload(): void
    {
        [$user] = $this->teacherAndStudent('infaq-mismatch');
        $service = app(InfaqService::class);
        $key = (string) Str::uuid();

        $service->createPending($user, [
            'purpose' => 'technology',
            'amount' => 50000,
            'is_anonymous' => false,
        ], $key);

        $this->expectException(ValidationException::class);
        $service->createPending($user, [
            'purpose' => 'teacher_development',
            'amount' => 75000,
            'is_anonymous' => false,
        ], $key);
    }

    public function test_submission_retry_key_cannot_change_the_teacher_decision(): void
    {
        [$user, $student] = $this->teacherAndStudent('submission-mismatch');
        QuranSurah::create([
            'id' => 1,
            'name_arabic' => 'الفاتحة',
            'name_latin' => 'Al-Fatihah',
            'verse_count' => 7,
            'sequence' => 1,
        ]);
        $key = (string) Str::uuid();
        $payload = [
            'submission_key' => $key,
            'surah_id' => 1,
            'start_verse' => 1,
            'end_verse' => 7,
            'daily_decision' => 'lanjut',
            'short_note' => null,
        ];
        $service = app(DistractionFreeSubmissionService::class);
        $service->recordMemorization($user, $student, $payload);

        $this->expectException(ValidationException::class);
        $service->recordMemorization($user, $student, [
            ...$payload,
            'daily_decision' => 'ulang',
        ]);
    }

    public function test_new_subscription_invoice_is_closed_when_free_mode_is_enabled(): void
    {
        config()->set('sullam.subscriptions_enabled', false);
        [$user] = $this->teacherAndStudent('free-mode');

        $this->expectException(ValidationException::class);
        app(BusinessBillingService::class)->createSubscriptionInvoice(
            $user,
            \App\Models\BillingPlan::create([
                'code' => 'legacy-paid-test',
                'name' => 'Legacy Paid Test',
                'audience' => 'teacher',
                'billing_cycle' => 'monthly',
                'price' => 100000,
                'currency' => 'IDR',
                'status' => 'active',
            ]),
        );
    }

    public function test_academy_preview_only_uses_an_explicitly_public_institution(): void
    {
        $private = Institution::create([
            'name' => 'Katalog Privat', 'code' => 'ACADEMY-PRIVATE', 'slug' => 'academy-private',
            'status' => 'active', 'settings' => ['public_academy' => false],
        ]);
        $public = Institution::create([
            'name' => 'Katalog Publik', 'code' => 'ACADEMY-PUBLIC', 'slug' => 'academy-public',
            'status' => 'active', 'settings' => ['public_academy' => true],
        ]);
        AcademyProgram::create([
            'institution_id' => $private->id, 'title' => 'Program Rahasia',
            'slug' => 'program-rahasia', 'audience' => 'all', 'status' => 'published',
        ]);
        AcademyProgram::create([
            'institution_id' => $public->id, 'title' => 'Program Terbuka',
            'slug' => 'program-terbuka', 'audience' => 'all', 'status' => 'published',
        ]);

        $this->getJson('/api/v1/academy-preview')
            ->assertOk()
            ->assertJsonPath('institution', 'Katalog Publik')
            ->assertJsonPath('data.0.title', 'Program Terbuka')
            ->assertJsonMissing(['title' => 'Program Rahasia']);
    }

    /** @return array{0: User, 1: Student} */
    private function teacherAndStudent(string $suffix): array
    {
        $institution = Institution::create([
            'name' => 'TPA '.Str::headline($suffix),
            'code' => 'V6-'.Str::upper(substr(sha1($suffix), 0, 8)),
            'slug' => 'v6-'.$suffix,
            'status' => 'active',
        ]);
        $user = User::create([
            'institution_id' => $institution->id,
            'name' => 'Ustadz '.Str::headline($suffix),
            'email' => $suffix.'@example.test',
            'password' => 'RahasiaAman123',
            'status' => 'active',
            'must_change_password' => false,
        ]);
        Teacher::create([
            'institution_id' => $institution->id,
            'user_id' => $user->id,
            'employee_code' => 'T-'.Str::upper(substr(sha1($suffix), 0, 8)),
            'full_name' => $user->name,
            'status' => 'active',
        ]);
        $student = Student::create([
            'institution_id' => $institution->id,
            'student_code' => 'S-'.Str::upper(substr(sha1($suffix), 0, 8)),
            'full_name' => 'Santri '.Str::headline($suffix),
            'status' => 'active',
        ]);

        return [$user->fresh(), $student];
    }
}
