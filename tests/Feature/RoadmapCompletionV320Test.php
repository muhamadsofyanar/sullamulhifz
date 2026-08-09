<?php

namespace Tests\Feature;

use App\Models\Guardian;
use App\Models\Institution;
use App\Models\MemorizationReviewPlan;
use App\Models\QuranSurah;
use App\Models\Student;
use App\Models\StudentPortfolio;
use App\Models\User;
use App\Services\AiAssistWorkflowService;
use App\Services\MurajaahReminderService;
use App\Services\RoadmapStatusService;
use App\Services\TalentPortfolioService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class RoadmapCompletionV320Test extends TestCase
{
    use RefreshDatabase;

    public function test_phase_eight_and_nine_implementation_foundations_are_complete(): void
    {
        foreach ([
            'talent_progress_records', 'student_portfolio_evidence',
            'ai_assist_drafts', 'ai_assist_reviews',
            'community_moderation_actions', 'payment_transactions',
        ] as $table) {
            $this->assertTrue(Schema::hasTable($table), "Tabel {$table} belum tersedia.");
        }
        $this->assertTrue(Schema::hasColumn('memorization_review_plans', 'reminder_sent_at'));

        $institution = Institution::create(['name' => 'Roadmap v320', 'code' => 'R320', 'slug' => 'r320', 'status' => 'active']);
        $phases = app(RoadmapStatusService::class)->phases($institution);
        $this->assertSame(100, $phases[8]['implementation_pct']);
        $this->assertSame(100, $phases[9]['implementation_pct']);
        $this->assertLessThan(100, $phases[10]['implementation_pct'], 'Fase 10 tidak boleh dipalsukan 100% tanpa aktivasi tenant/integrasi nyata.');
    }

    public function test_talent_progress_and_portfolio_evidence_use_non_ranking_workflow(): void
    {
        $institution = Institution::create(['name' => 'Talent v320', 'code' => 'T320', 'slug' => 't320', 'status' => 'active']);
        $actor = User::create(['institution_id' => $institution->id, 'name' => 'Pembina', 'email' => 'pembina-v320@example.test', 'password' => 'RahasiaAman123', 'status' => 'active']);
        $student = Student::create(['institution_id' => $institution->id, 'student_code' => 'S320', 'full_name' => 'Santri v320', 'status' => 'active']);
        $portfolio = StudentPortfolio::create([
            'institution_id' => $institution->id, 'student_id' => $student->id, 'created_by_user_id' => $actor->id,
            'category' => 'talent', 'title' => 'Proyek Khitobah', 'occurred_on' => today(), 'status' => 'published',
        ]);

        $service = app(TalentPortfolioService::class);
        $progress = $service->recordProgress($student, $actor, [
            'domain' => 'public_speaking', 'rubric_key' => 'clarity', 'progress_level' => 'developing',
            'observation' => 'Mampu menyampaikan pembukaan dengan runtut.', 'next_step' => 'Latih penutupan dan kontak mata.',
        ]);
        $evidence = $service->addPortfolioEvidence($portfolio, $actor, [
            'evidence_type' => 'note', 'label' => 'Catatan pembina', 'note' => 'Perkembangan terdokumentasi tanpa skor/ranking.',
        ]);

        $this->assertSame('developing', $progress->progress_level);
        $this->assertSame($portfolio->id, $evidence->student_portfolio_id);
        $this->assertDatabaseCount('talent_progress_records', 1);
        $this->assertDatabaseCount('student_portfolio_evidence', 1);
    }

    public function test_ai_assist_draft_cannot_bypass_human_review_audit(): void
    {
        $institution = Institution::create(['name' => 'AI v320', 'code' => 'AI320', 'slug' => 'ai320', 'status' => 'active']);
        $author = User::create(['institution_id' => $institution->id, 'name' => 'Guru', 'email' => 'guru-ai-v320@example.test', 'password' => 'RahasiaAman123', 'status' => 'active']);
        $reviewer = User::create(['institution_id' => $institution->id, 'name' => 'Reviewer', 'email' => 'reviewer-ai-v320@example.test', 'password' => 'RahasiaAman123', 'status' => 'active']);
        $student = Student::create(['institution_id' => $institution->id, 'student_code' => 'AI-S320', 'full_name' => 'Santri AI', 'status' => 'active']);

        $service = app(AiAssistWorkflowService::class);
        $draft = $service->storeDraft($author, $student, [
            'purpose' => 'report_note',
            'evidence_snapshot' => ['source' => 'teacher_observation', 'ids' => [1]],
            'draft_text' => 'Draft belum boleh menjadi keputusan final.',
            'provider' => 'disabled-test-provider',
        ]);

        $this->assertSame('pending_review', $draft->status);
        $review = $service->review($draft, $reviewer, 'modified', 'Catatan final setelah diperiksa guru.', 'Disesuaikan dengan observasi kelas.');
        $this->assertSame('modified', $review->decision);
        $this->assertSame('approved', $draft->refresh()->status);
        $this->assertDatabaseHas('activity_logs', ['action' => 'ai_assist.reviewed', 'subject_id' => $draft->id]);
    }

    public function test_murajaah_reminder_is_database_only_and_idempotent(): void
    {
        $institution = Institution::create(['name' => 'Reminder v320', 'code' => 'M320', 'slug' => 'm320', 'status' => 'active']);
        $guardianUser = User::create(['institution_id' => $institution->id, 'name' => 'Wali', 'email' => 'wali-v320@example.test', 'password' => 'RahasiaAman123', 'status' => 'active']);
        $guardian = Guardian::create(['institution_id' => $institution->id, 'user_id' => $guardianUser->id, 'full_name' => 'Wali Santri', 'status' => 'active']);
        $student = Student::create(['institution_id' => $institution->id, 'student_code' => 'M-S320', 'full_name' => 'Santri Reminder', 'status' => 'active']);
        $student->guardians()->attach($guardian->id, ['relationship' => 'guardian', 'can_receive_notifications' => true]);
        QuranSurah::create(['id' => 114, 'name_arabic' => 'الناس', 'name_latin' => 'An-Nas', 'verse_count' => 6, 'sequence' => 114]);

        $plan = MemorizationReviewPlan::create([
            'institution_id' => $institution->id, 'student_id' => $student->id, 'surah_id' => 114,
            'start_verse' => 1, 'end_verse' => 6, 'review_date' => today(), 'review_type' => 'scheduled',
            'priority' => 'normal', 'status' => 'scheduled',
        ]);

        $service = app(MurajaahReminderService::class);
        $first = $service->sendDue(today());
        $second = $service->sendDue(today());

        $this->assertSame(['plans' => 1, 'recipients' => 1], $first);
        $this->assertSame(['plans' => 0, 'recipients' => 0], $second);
        $this->assertNotNull($plan->refresh()->reminder_sent_at);
        $this->assertDatabaseCount('notifications', 1);
    }
}
