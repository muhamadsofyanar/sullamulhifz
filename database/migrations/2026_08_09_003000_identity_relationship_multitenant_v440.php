<?php

/**
 * @phase 4.3 Identity & Relationship Core
 * @phase 4.4 Multi-tenant Institution Foundation
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        $this->extendInstitutions();
        $this->createWorkspaceMemberships();
        $this->createUserRelationships();
        $this->createWorkspaceInvitations();
        $this->backfillWorkspaceData();
    }

    private function extendInstitutions(): void
    {
        Schema::table('institutions', function (Blueprint $table): void {
            if (! Schema::hasColumn('institutions', 'institution_type')) {
                $table->string('institution_type', 40)->default('tpa')->after('workspace_type')->index();
            }
            if (! Schema::hasColumn('institutions', 'onboarding_status')) {
                $table->string('onboarding_status', 40)->default('completed')->after('privacy_mode')->index();
            }
            if (! Schema::hasColumn('institutions', 'registration_source')) {
                $table->string('registration_source', 60)->nullable()->after('onboarding_status');
            }
            if (! Schema::hasColumn('institutions', 'custom_domain')) {
                $table->string('custom_domain', 190)->nullable()->after('registration_source')->unique();
            }
            if (! Schema::hasColumn('institutions', 'brand_primary_color')) {
                $table->string('brand_primary_color', 12)->default('#004b3f')->after('custom_domain');
            }
            if (! Schema::hasColumn('institutions', 'brand_secondary_color')) {
                $table->string('brand_secondary_color', 12)->default('#d3a13a')->after('brand_primary_color');
            }
            if (! Schema::hasColumn('institutions', 'terminology')) {
                $table->json('terminology')->nullable()->after('brand_secondary_color');
            }
            if (! Schema::hasColumn('institutions', 'approved_at')) {
                $table->timestamp('approved_at')->nullable()->after('terminology');
            }
            if (! Schema::hasColumn('institutions', 'approved_by_user_id')) {
                $table->foreignId('approved_by_user_id')->nullable()->after('approved_at')->constrained('users')->nullOnDelete();
            }
        });
    }

    private function createWorkspaceMemberships(): void
    {
        if (Schema::hasTable('workspace_memberships')) {
            return;
        }

        Schema::create('workspace_memberships', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('role_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->string('membership_type', 40)->default('member');
            $table->string('display_label', 120)->nullable();
            $table->string('status', 30)->default('active');
            $table->boolean('is_default')->default(false);
            $table->timestamp('joined_at')->nullable();
            $table->timestamp('left_at')->nullable();
            $table->json('settings')->nullable();
            $table->timestamps();
            $table->unique(['institution_id', 'user_id', 'membership_type'], 'workspace_member_type_uq');
            $table->index(['user_id', 'status', 'is_default'], 'workspace_member_context_idx');
            $table->index(['institution_id', 'status', 'membership_type'], 'workspace_member_scope_idx');
        });
    }

    private function createUserRelationships(): void
    {
        if (Schema::hasTable('user_relationships')) {
            return;
        }

        Schema::create('user_relationships', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('institution_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('context_key', 80)->default('global');
            $table->foreignId('from_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('to_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('relationship_type', 40);
            $table->string('status', 30)->default('pending');
            $table->json('visibility_scope')->nullable();
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(
                ['context_key', 'from_user_id', 'to_user_id', 'relationship_type'],
                'user_relationship_direction_uq'
            );
            $table->index(['from_user_id', 'status', 'relationship_type'], 'user_relationship_from_idx');
            $table->index(['to_user_id', 'status', 'relationship_type'], 'user_relationship_to_idx');
        });
    }

    private function createWorkspaceInvitations(): void
    {
        if (Schema::hasTable('workspace_invitations')) {
            return;
        }

        Schema::create('workspace_invitations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
            $table->foreignId('invited_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('accepted_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('role_id')->nullable()->constrained()->nullOnDelete();
            $table->string('membership_type', 40)->default('member');
            $table->string('email', 190)->nullable();
            $table->string('phone', 40)->nullable();
            $table->string('token_hash', 64)->unique();
            $table->string('status', 30)->default('pending');
            $table->timestamp('expires_at');
            $table->timestamp('accepted_at')->nullable();
            $table->timestamps();
            $table->index(['institution_id', 'status', 'expires_at'], 'workspace_invitation_scope_idx');
        });
    }

    private function backfillWorkspaceData(): void
    {
        $now = now();

        DB::table('institutions')
            ->where('workspace_type', 'personal')
            ->update([
                'institution_type' => 'personal',
                'onboarding_status' => 'completed',
                'registration_source' => DB::raw("COALESCE(registration_source, 'legacy_personal')"),
            ]);

        DB::table('institutions')
            ->where('workspace_type', 'institution')
            ->whereNull('approved_at')
            ->update(['approved_at' => $now]);

        $roles = DB::table('roles')->pluck('name', 'id');
        $roleMembershipMap = [
            'superadmin' => 'platform_admin',
            'institution_admin' => 'owner',
            'head' => 'manager',
            'teacher' => 'teacher',
            'guardian' => 'guardian',
            'personal' => 'learner',
            'student' => 'learner',
        ];

        foreach (DB::table('user_roles')->where('status', 'active')->whereNotNull('institution_id')->get() as $assignment) {
            $roleName = $roles[$assignment->role_id] ?? null;
            $membershipType = $roleMembershipMap[$roleName] ?? 'member';
            $isDefault = (int) DB::table('users')->where('id', $assignment->user_id)->value('institution_id') === (int) $assignment->institution_id;

            DB::table('workspace_memberships')->updateOrInsert(
                [
                    'institution_id' => $assignment->institution_id,
                    'user_id' => $assignment->user_id,
                    'membership_type' => $membershipType,
                ],
                [
                    'role_id' => $assignment->role_id,
                    'branch_id' => $assignment->branch_id ?? null,
                    'status' => 'active',
                    'is_default' => $isDefault,
                    'joined_at' => $assignment->created_at ?? $now,
                    'updated_at' => $now,
                    'created_at' => $assignment->created_at ?? $now,
                ],
            );
        }

        foreach (DB::table('users')->whereNotNull('institution_id')->get() as $user) {
            $exists = DB::table('workspace_memberships')
                ->where('institution_id', $user->institution_id)
                ->where('user_id', $user->id)
                ->exists();

            if (! $exists) {
                DB::table('workspace_memberships')->insert([
                    'institution_id' => $user->institution_id,
                    'user_id' => $user->id,
                    'membership_type' => 'member',
                    'status' => 'active',
                    'is_default' => true,
                    'joined_at' => $user->created_at ?? $now,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('workspace_invitations');
        Schema::dropIfExists('user_relationships');
        Schema::dropIfExists('workspace_memberships');

        Schema::table('institutions', function (Blueprint $table): void {
            if (Schema::hasColumn('institutions', 'approved_by_user_id')) {
                $table->dropConstrainedForeignId('approved_by_user_id');
            }
            foreach ([
                'institution_type', 'onboarding_status', 'registration_source', 'custom_domain',
                'brand_primary_color', 'brand_secondary_color', 'terminology', 'approved_at',
            ] as $column) {
                if (Schema::hasColumn('institutions', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
