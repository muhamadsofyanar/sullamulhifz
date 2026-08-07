<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        $this->extendAcademyCatalog();

        if (! Schema::hasTable('academy_learning_paths')) {
            Schema::create('academy_learning_paths', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
                $table->string('title');
                $table->string('slug');
                $table->string('audience', 30)->default('all');
                $table->string('category', 50)->nullable();
                $table->text('summary')->nullable();
                $table->string('status', 30)->default('draft');
                $table->boolean('is_featured')->default(false);
                $table->unsignedSmallInteger('sort_order')->default(0);
                $table->json('metadata')->nullable();
                $table->timestamps();
                $table->unique(['institution_id', 'slug']);
                $table->index(['institution_id', 'status', 'audience'], 'academy_paths_listing_idx');
            });
        }

        if (! Schema::hasTable('academy_learning_path_items')) {
            Schema::create('academy_learning_path_items', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('academy_learning_path_id')->constrained('academy_learning_paths')->cascadeOnDelete();
                $table->string('item_type', 30); // lesson, quran_preset, reflection, activity
                $table->unsignedBigInteger('item_id')->nullable();
                $table->string('title_override')->nullable();
                $table->text('instruction')->nullable();
                $table->boolean('is_required')->default(true);
                $table->unsignedSmallInteger('sort_order')->default(0);
                $table->json('metadata')->nullable();
                $table->timestamps();
                $table->index(['academy_learning_path_id', 'sort_order'], 'academy_path_items_order_idx');
                $table->index(['item_type', 'item_id'], 'academy_path_items_target_idx');
            });
        }

        if (! Schema::hasTable('academy_bookmarks')) {
            Schema::create('academy_bookmarks', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->string('bookmark_type', 30)->default('lesson');
                $table->unsignedBigInteger('bookmark_id');
                $table->string('label')->nullable();
                $table->json('context')->nullable();
                $table->timestamps();
                $table->unique(['user_id', 'bookmark_type', 'bookmark_id'], 'academy_bookmark_user_target_uq');
                $table->index(['institution_id', 'user_id']);
            });
        }

        if (! Schema::hasTable('academy_reflections')) {
            Schema::create('academy_reflections', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->foreignId('academy_lesson_id')->nullable()->constrained('academy_lessons')->nullOnDelete();
                $table->foreignId('student_id')->nullable()->constrained()->nullOnDelete();
                $table->text('reflection');
                $table->string('follow_up', 255)->nullable();
                $table->string('visibility', 30)->default('private');
                $table->timestamps();
                $table->index(['user_id', 'academy_lesson_id']);
                $table->index(['student_id', 'created_at']);
            });
        }

        if (! Schema::hasTable('student_portfolios')) {
            Schema::create('student_portfolios', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
                $table->foreignId('student_id')->constrained()->cascadeOnDelete();
                $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('media_asset_id')->nullable()->constrained('media_assets')->nullOnDelete();
                $table->string('category', 50)->default('learning');
                $table->string('title');
                $table->text('description')->nullable();
                $table->date('occurred_on')->nullable();
                $table->string('visibility', 30)->default('guardian_teacher');
                $table->string('status', 30)->default('published');
                $table->json('metadata')->nullable();
                $table->timestamps();
                $table->softDeletes();
                $table->index(['institution_id', 'student_id', 'status'], 'student_portfolio_listing_idx');
            });
        }

        if (! Schema::hasTable('community_spaces')) {
            Schema::create('community_spaces', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
                $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
                $table->string('space_type', 30)->default('institution');
                $table->unsignedBigInteger('reference_id')->nullable();
                $table->string('name');
                $table->text('description')->nullable();
                $table->string('moderation_mode', 30)->default('approval');
                $table->string('status', 30)->default('draft');
                $table->json('settings')->nullable();
                $table->timestamps();
                $table->softDeletes();
                $table->index(['institution_id', 'status', 'space_type'], 'community_spaces_listing_idx');
            });
        }

        if (! Schema::hasTable('community_posts')) {
            Schema::create('community_posts', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('community_space_id')->constrained('community_spaces')->cascadeOnDelete();
                $table->foreignId('created_by_user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('media_asset_id')->nullable()->constrained('media_assets')->nullOnDelete();
                $table->text('body');
                $table->string('post_type', 30)->default('update');
                $table->string('status', 30)->default('pending');
                $table->timestamp('published_at')->nullable();
                $table->timestamps();
                $table->softDeletes();
                $table->index(['community_space_id', 'status', 'published_at'], 'community_posts_listing_idx');
            });
        }

        if (! Schema::hasTable('learning_insights')) {
            Schema::create('learning_insights', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
                $table->foreignId('student_id')->constrained()->cascadeOnDelete();
                $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('insight_type', 50)->default('observation');
                $table->string('title');
                $table->text('summary');
                $table->json('evidence')->nullable();
                $table->string('source', 30)->default('human');
                $table->string('status', 30)->default('active');
                $table->timestamp('generated_at')->nullable();
                $table->timestamps();
                $table->index(['institution_id', 'student_id', 'status'], 'learning_insights_listing_idx');
            });
        }

        if (! Schema::hasTable('integration_connections')) {
            Schema::create('integration_connections', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
                $table->string('provider', 60);
                $table->string('display_name');
                $table->string('status', 30)->default('disabled');
                $table->json('configuration')->nullable(); // non-secret operational config only
                $table->timestamp('last_checked_at')->nullable();
                $table->timestamps();
                $table->unique(['institution_id', 'provider']);
            });
        }
    }

    private function extendAcademyCatalog(): void
    {
        if (Schema::hasTable('academy_programs')) {
            Schema::table('academy_programs', function (Blueprint $table): void {
                if (! Schema::hasColumn('academy_programs', 'category')) {
                    $table->string('category', 50)->nullable()->after('audience');
                }
                if (! Schema::hasColumn('academy_programs', 'learning_track')) {
                    $table->string('learning_track', 50)->nullable()->after('category');
                }
                if (! Schema::hasColumn('academy_programs', 'metadata')) {
                    $table->json('metadata')->nullable()->after('sort_order');
                }
            });
        }

        if (Schema::hasTable('academy_modules') && ! Schema::hasColumn('academy_modules', 'metadata')) {
            Schema::table('academy_modules', function (Blueprint $table): void {
                $table->json('metadata')->nullable()->after('status');
            });
        }

        if (Schema::hasTable('academy_lessons') && ! Schema::hasColumn('academy_lessons', 'metadata')) {
            Schema::table('academy_lessons', function (Blueprint $table): void {
                $table->json('metadata')->nullable()->after('status');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('integration_connections');
        Schema::dropIfExists('learning_insights');
        Schema::dropIfExists('community_posts');
        Schema::dropIfExists('community_spaces');
        Schema::dropIfExists('student_portfolios');
        Schema::dropIfExists('academy_reflections');
        Schema::dropIfExists('academy_bookmarks');
        Schema::dropIfExists('academy_learning_path_items');
        Schema::dropIfExists('academy_learning_paths');

        if (Schema::hasTable('academy_lessons') && Schema::hasColumn('academy_lessons', 'metadata')) {
            Schema::table('academy_lessons', fn (Blueprint $table) => $table->dropColumn('metadata'));
        }
        if (Schema::hasTable('academy_modules') && Schema::hasColumn('academy_modules', 'metadata')) {
            Schema::table('academy_modules', fn (Blueprint $table) => $table->dropColumn('metadata'));
        }
        if (Schema::hasTable('academy_programs')) {
            $columns = array_values(array_filter(['metadata', 'learning_track', 'category'], fn ($column) => Schema::hasColumn('academy_programs', $column)));
            if ($columns) {
                Schema::table('academy_programs', fn (Blueprint $table) => $table->dropColumn($columns));
            }
        }
    }
};
