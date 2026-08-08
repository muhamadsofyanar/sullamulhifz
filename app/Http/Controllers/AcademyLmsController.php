<?php

namespace App\Http\Controllers;

use App\Models\AcademyCertificate;
use App\Models\AcademyQuiz;
use App\Models\AcademyQuizAnswer;
use App\Models\AcademyQuizAttempt;
use App\Models\AcademyWorksheet;
use App\Models\AcademyWorksheetSubmission;
use App\Services\AcademyLmsService;
use App\Support\Feature;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AcademyLmsController extends Controller
{
    public function submitQuiz(Request $request, AcademyQuiz $quiz, AcademyLmsService $lms): RedirectResponse
    {
        $quiz->load('lesson.module.program', 'questions.options');
        $this->authorizeLesson($request, $quiz->lesson, $lms);
        abort_unless($quiz->status === 'published', 404);

        if ($quiz->questions->isEmpty()) return back()->with('error', 'Kuis belum memiliki pertanyaan.');
        if (AcademyQuizAttempt::where('user_id', $request->user()->id)->where('academy_quiz_id', $quiz->id)->where('passed', true)->exists()) {
            return back()->with('success', 'Kuis ini sudah lulus.');
        }

        $attemptNumber = (int) AcademyQuizAttempt::where('user_id', $request->user()->id)->where('academy_quiz_id', $quiz->id)->max('attempt_number') + 1;
        if ($attemptNumber > $quiz->max_attempts) return back()->with('error', 'Batas percobaan kuis sudah tercapai.');

        $answers = $request->input('answers', []);
        if (! is_array($answers)) $answers = [];
        foreach ($quiz->questions as $question) {
            $selectedId = isset($answers[$question->id]) ? (int) $answers[$question->id] : 0;
            if (! $question->options->contains('id', $selectedId)) {
                return back()->with('error', 'Jawab seluruh pertanyaan dengan opsi yang tersedia.');
            }
        }

        $attempt = DB::transaction(function () use ($request, $quiz, $attemptNumber, $answers): AcademyQuizAttempt {
            $maxScore = (int) $quiz->questions->sum('points');
            $score = 0;
            $rows = [];
            foreach ($quiz->questions as $question) {
                $selectedId = isset($answers[$question->id]) ? (int) $answers[$question->id] : 0;
                $selected = $question->options->firstWhere('id', $selectedId);
                $correct = $selected && $selected->is_correct;
                $awarded = $correct ? (int) $question->points : 0;
                $score += $awarded;
                $rows[] = [$question, $selected, $correct, $awarded];
            }
            $percent = $maxScore > 0 ? (int) round(($score / $maxScore) * 100) : 0;
            $attempt = AcademyQuizAttempt::create([
                'institution_id' => $request->user()->institution_id,
                'user_id' => $request->user()->id,
                'academy_quiz_id' => $quiz->id,
                'attempt_number' => $attemptNumber,
                'score' => $score,
                'max_score' => $maxScore,
                'percent' => $percent,
                'passed' => $percent >= $quiz->passing_percent,
                'completed_at' => now(),
            ]);
            foreach ($rows as [$question, $selected, $correct, $awarded]) {
                AcademyQuizAnswer::create([
                    'academy_quiz_attempt_id' => $attempt->id,
                    'academy_quiz_question_id' => $question->id,
                    'academy_quiz_option_id' => $selected?->id,
                    'is_correct' => (bool) $correct,
                    'awarded_points' => $awarded,
                ]);
            }
            return $attempt;
        });

        return back()->with($attempt->passed ? 'success' : 'error', $attempt->passed ? 'Kuis lulus. Anda dapat menyelesaikan materi.' : 'Nilai belum mencapai batas lulus. Silakan pelajari materi dan coba kembali.');
    }

    public function submitWorksheet(Request $request, AcademyWorksheet $worksheet, AcademyLmsService $lms): RedirectResponse
    {
        $worksheet->load('lesson.module.program');
        $this->authorizeLesson($request, $worksheet->lesson, $lms);
        abort_unless($worksheet->status === 'published', 404);
        $data = $request->validate(['response' => [$worksheet->completion_mode === 'reflection' ? 'required' : 'nullable', 'string', 'max:10000']]);
        AcademyWorksheetSubmission::updateOrCreate(
            ['user_id' => $request->user()->id, 'academy_worksheet_id' => $worksheet->id],
            ['institution_id' => $request->user()->institution_id, 'response' => $data['response'] ?? null, 'status' => 'completed', 'completed_at' => now()]
        );
        return back()->with('success', 'Worksheet selesai dan tersimpan.');
    }

    public function certificate(Request $request, AcademyCertificate $certificate): View
    {
        abort_unless((int) $certificate->institution_id === (int) $request->user()->institution_id, 404);
        abort_unless((int) $certificate->user_id === (int) $request->user()->id || $request->user()->hasAnyRole(['superadmin','institution_admin','head']), 403);
        $certificate->load('user', 'program', 'institution');
        $academyLayout = strtolower($request->getHost()) === strtolower((string) config('sullam.academy_host')) ? 'layouts.academy' : 'layouts.app';
        return view('academy.certificate', compact('certificate', 'academyLayout'));
    }

    public function verify(string $verificationCode): View
    {
        $certificate = AcademyCertificate::with('user', 'program', 'institution')->where('verification_code', $verificationCode)->where('status', 'issued')->first();
        return view('public.certificate-verify', compact('certificate'));
    }

    private function authorizeLesson(Request $request, $lesson, AcademyLmsService $lms): void
    {
        $program = $lesson->module->program;
        abort_unless((int) $program->institution_id === (int) $request->user()->institution_id, 404);
        abort_unless($program->status === 'published' && $lesson->status === 'published', 404);
        $audiences = ['all'];
        if ($request->user()->hasAnyRole(['superadmin','institution_admin','head'])) $audiences = ['all','guardian','teacher','admin'];
        else {
            if ($request->user()->hasRole('guardian')) $audiences[] = 'guardian';
            if ($request->user()->hasRole('teacher')) $audiences[] = 'teacher';
        }
        abort_unless(in_array($program->audience, array_unique($audiences), true), 403);
        if ($program->audience === 'guardian') abort_unless(Feature::enabled('parent_academy', (int) $request->user()->institution_id, true), 404);
        if ($program->audience === 'teacher') abort_unless(Feature::enabled('teacher_academy', (int) $request->user()->institution_id, true), 404);
        abort_unless($lms->isUnlocked($request->user(), 'lesson', (int) $lesson->id), 403);
    }
}
