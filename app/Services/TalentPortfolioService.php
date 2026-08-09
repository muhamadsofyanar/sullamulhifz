<?php

namespace App\Services;

use App\Models\Program;
use App\Models\Student;
use App\Models\StudentPortfolio;
use App\Models\StudentPortfolioEvidence;
use App\Models\TalentProgressRecord;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class TalentPortfolioService
{
    public function recordProgress(Student $student, User $actor, array $data): TalentProgressRecord
    {
        abort_unless((int) $student->institution_id === (int) $actor->institution_id, 403);

        $programId = $data['program_id'] ?? null;
        if ($programId) {
            abort_unless(Program::query()->whereKey($programId)->where('institution_id', $student->institution_id)->exists(), 422);
        }

        return TalentProgressRecord::create([
            'institution_id' => $student->institution_id,
            'student_id' => $student->id,
            'program_id' => $programId,
            'learning_group_id' => $data['learning_group_id'] ?? null,
            'recorded_by_user_id' => $actor->id,
            'domain' => $data['domain'],
            'rubric_key' => $data['rubric_key'],
            'progress_level' => $data['progress_level'],
            'observation' => $data['observation'],
            'next_step' => $data['next_step'] ?? null,
            'recorded_on' => $data['recorded_on'] ?? today(),
            'status' => 'active',
        ]);
    }

    public function addPortfolioEvidence(StudentPortfolio $portfolio, User $actor, array $data): StudentPortfolioEvidence
    {
        abort_unless((int) $portfolio->institution_id === (int) $actor->institution_id, 403);

        return DB::transaction(function () use ($portfolio, $actor, $data): StudentPortfolioEvidence {
            return StudentPortfolioEvidence::create([
                'institution_id' => $portfolio->institution_id,
                'student_portfolio_id' => $portfolio->id,
                'created_by_user_id' => $actor->id,
                'media_asset_id' => $data['media_asset_id'] ?? null,
                'evidence_type' => $data['evidence_type'] ?? 'note',
                'label' => $data['label'],
                'reference_url' => $data['reference_url'] ?? null,
                'note' => $data['note'] ?? null,
                'occurred_on' => $data['occurred_on'] ?? $portfolio->occurred_on,
                'metadata' => $data['metadata'] ?? null,
            ]);
        });
    }
}
