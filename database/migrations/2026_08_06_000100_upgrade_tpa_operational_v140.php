<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table): void {
            if (! Schema::hasColumn('students', 'birth_place')) {
                $table->string('birth_place')->nullable()->after('gender');
            }
            if (! Schema::hasColumn('students', 'photo_path')) {
                $table->string('photo_path')->nullable()->after('birth_date');
            }
        });

        Schema::table('users', function (Blueprint $table): void {
            if (! Schema::hasColumn('users', 'login_count')) {
                $table->unsignedInteger('login_count')->default(0)->after('last_login_ip');
            }
        });

        Schema::table('announcements', function (Blueprint $table): void {
            if (! Schema::hasColumn('announcements', 'learning_group_id')) {
                $table->foreignId('learning_group_id')->nullable()->after('class_id')->constrained()->nullOnDelete();
            }
            if (! Schema::hasColumn('announcements', 'audience_type')) {
                $table->string('audience_type')->default('all')->after('learning_group_id');
            }
            if (! Schema::hasColumn('announcements', 'is_pinned')) {
                $table->boolean('is_pinned')->default(false)->after('status');
            }
            if (! Schema::hasColumn('announcements', 'require_acknowledgement')) {
                $table->boolean('require_acknowledgement')->default(false)->after('is_pinned');
            }
            if (! Schema::hasColumn('announcements', 'attachment_path')) {
                $table->string('attachment_path')->nullable()->after('require_acknowledgement');
                $table->string('attachment_original_name')->nullable()->after('attachment_path');
            }
        });

        Schema::table('friday_development_sessions', function (Blueprint $table): void {
            if (! Schema::hasColumn('friday_development_sessions', 'media_url')) {
                $table->string('media_url')->nullable()->after('home_follow_up');
            }
            if (! Schema::hasColumn('friday_development_sessions', 'worksheet_path')) {
                $table->string('worksheet_path')->nullable()->after('media_url');
                $table->string('worksheet_original_name')->nullable()->after('worksheet_path');
            }
            if (! Schema::hasColumn('friday_development_sessions', 'family_response_enabled')) {
                $table->boolean('family_response_enabled')->default(false)->after('worksheet_original_name');
            }
        });

        Schema::table('liaison_messages', function (Blueprint $table): void {
            if (! Schema::hasColumn('liaison_messages', 'file_path')) {
                $table->string('file_path')->nullable()->after('message_type');
                $table->string('original_name')->nullable()->after('file_path');
                $table->string('mime_type')->nullable()->after('original_name');
                $table->unsignedBigInteger('file_size')->nullable()->after('mime_type');
            }
        });

        Schema::create('login_histories', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('institution_id')->nullable()->constrained()->nullOnDelete();
            $table->string('login_identifier')->nullable();
            $table->boolean('was_successful')->default(false);
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('logged_in_at')->useCurrent();
            $table->index(['institution_id', 'logged_in_at']);
        });

        Schema::create('announcement_reads', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('announcement_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamp('read_at')->nullable();
            $table->timestamp('acknowledged_at')->nullable();
            $table->timestamps();
            $table->unique(['announcement_id', 'user_id']);
        });

        Schema::create('public_pages', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('institution_id')->nullable()->constrained()->nullOnDelete();
            $table->string('slug')->unique();
            $table->string('title');
            $table->text('summary')->nullable();
            $table->longText('content')->nullable();
            $table->string('status')->default('published');
            $table->string('seo_title')->nullable();
            $table->text('seo_description')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('public_articles', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('institution_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('author_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('excerpt')->nullable();
            $table->longText('content');
            $table->string('cover_image_path')->nullable();
            $table->string('status')->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->string('seo_title')->nullable();
            $table->text('seo_description')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['status', 'published_at']);
        });

        Schema::create('admission_registrations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('institution_id')->nullable()->constrained()->nullOnDelete();
            $table->string('student_name');
            $table->string('student_age')->nullable();
            $table->string('guardian_name');
            $table->string('guardian_phone');
            $table->string('guardian_email')->nullable();
            $table->string('desired_program')->nullable();
            $table->text('notes')->nullable();
            $table->string('status')->default('new');
            $table->foreignId('handled_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('handled_at')->nullable();
            $table->timestamps();
            $table->index(['status', 'created_at']);
        });

        Schema::create('report_cards', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->string('semester');
            $table->date('period_start')->nullable();
            $table->date('period_end')->nullable();
            $table->text('teacher_summary')->nullable();
            $table->text('guardian_note')->nullable();
            $table->string('status')->default('draft');
            $table->foreignId('prepared_by_user_id')->constrained('users')->restrictOnDelete();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->unique(['academic_year_id', 'student_id', 'semester'], 'report_card_year_student_semester_uq');
        });

        Schema::create('report_card_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('report_card_id')->constrained()->cascadeOnDelete();
            $table->string('category');
            $table->string('label');
            $table->string('score')->nullable();
            $table->text('description')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->index(['report_card_id', 'category']);
        });

        Schema::create('import_batches', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
            $table->foreignId('uploaded_by_user_id')->constrained('users')->restrictOnDelete();
            $table->string('type');
            $table->string('original_name');
            $table->string('status')->default('preview');
            $table->unsignedInteger('total_rows')->default(0);
            $table->unsignedInteger('success_rows')->default(0);
            $table->unsignedInteger('failed_rows')->default(0);
            $table->json('summary')->nullable();
            $table->timestamps();
        });

        Schema::create('import_rows', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('import_batch_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('row_number');
            $table->json('payload');
            $table->string('status')->default('pending');
            $table->text('error_message')->nullable();
            $table->timestamps();
            $table->unique(['import_batch_id', 'row_number']);
        });

        Schema::create('system_settings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('institution_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('group')->default('general');
            $table->string('key');
            $table->longText('value')->nullable();
            $table->string('type')->default('string');
            $table->timestamps();
            $table->unique(['institution_id', 'key']);
        });
    }

    public function down(): void
    {
        foreach (['system_settings','import_rows','import_batches','report_card_items','report_cards','admission_registrations','public_articles','public_pages','announcement_reads','login_histories'] as $table) {
            Schema::dropIfExists($table);
        }

        Schema::table('liaison_messages', function (Blueprint $table): void {
            foreach (['file_path','original_name','mime_type','file_size'] as $column) {
                if (Schema::hasColumn('liaison_messages', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('friday_development_sessions', function (Blueprint $table): void {
            foreach (['media_url','worksheet_path','worksheet_original_name','family_response_enabled'] as $column) {
                if (Schema::hasColumn('friday_development_sessions', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('announcements', function (Blueprint $table): void {
            if (Schema::hasColumn('announcements', 'learning_group_id')) {
                $table->dropConstrainedForeignId('learning_group_id');
            }
            foreach (['audience_type','is_pinned','require_acknowledgement','attachment_path','attachment_original_name'] as $column) {
                if (Schema::hasColumn('announcements', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('users', function (Blueprint $table): void {
            if (Schema::hasColumn('users', 'login_count')) {
                $table->dropColumn('login_count');
            }
        });

        Schema::table('students', function (Blueprint $table): void {
            foreach (['birth_place','photo_path'] as $column) {
                if (Schema::hasColumn('students', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
