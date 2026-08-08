<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('learning_recommendation_reviews')) {
            Schema::create('learning_recommendation_reviews', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
                $table->foreignId('learning_insight_id')->constrained('learning_insights')->cascadeOnDelete();
                $table->foreignId('student_id')->constrained()->cascadeOnDelete();
                $table->foreignId('teacher_id')->constrained()->cascadeOnDelete();
                $table->string('decision', 24);
                $table->longText('original_recommendation');
                $table->longText('final_recommendation')->nullable();
                $table->text('review_note')->nullable();
                $table->timestamp('reviewed_at');
                $table->timestamps();

                $table->unique('learning_insight_id', 'learning_recommendation_review_insight_uq');
                $table->index(['institution_id', 'student_id', 'reviewed_at'], 'learning_recommendation_reviews_student_idx');
                $table->index(['teacher_id', 'decision'], 'learning_recommendation_reviews_teacher_idx');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('learning_recommendation_reviews');
    }
};
