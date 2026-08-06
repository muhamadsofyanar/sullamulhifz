<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('institutions', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->string('slug')->unique();
            $table->string('legal_name')->nullable();
            $table->string('phone')->nullable()->unique();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->string('timezone')->default('Asia/Jakarta');
            $table->string('status')->default('active');
            $table->json('settings')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('institution_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('email')->nullable()->unique();
            $table->string('phone')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamp('phone_verified_at')->nullable();
            $table->string('password');
            $table->string('status')->default('active');
            $table->boolean('must_change_password')->default(false);
            $table->timestamp('last_login_at')->nullable();
            $table->string('last_login_ip', 45)->nullable();
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table): void {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('roles', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->unique();
            $table->string('display_name');
            $table->text('description')->nullable();
            $table->string('scope')->default('institution');
        });

        Schema::create('permissions', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->unique();
            $table->string('display_name');
            $table->text('description')->nullable();
        });

        Schema::create('role_permissions', function (Blueprint $table): void {
            $table->foreignId('role_id')->constrained()->cascadeOnDelete();
            $table->foreignId('permission_id')->constrained()->cascadeOnDelete();
            $table->primary(['role_id', 'permission_id']);
        });

        Schema::create('user_roles', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('role_id')->constrained()->cascadeOnDelete();
            $table->foreignId('institution_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('status')->default('active');
            $table->date('valid_from')->nullable();
            $table->date('valid_until')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'role_id', 'institution_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_roles');
        Schema::dropIfExists('role_permissions');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('roles');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
        Schema::dropIfExists('institutions');
    }
};
