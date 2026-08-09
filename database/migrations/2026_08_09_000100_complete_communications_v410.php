<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('communication_templates')) {
            Schema::create('communication_templates', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
                $table->string('channel', 20);
                $table->string('event_key', 80);
                $table->string('name', 150);
                $table->string('subject')->nullable();
                $table->longText('content');
                $table->json('available_variables')->nullable();
                $table->boolean('is_active')->default(true);
                $table->foreignId('updated_by_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
                $table->unique(['institution_id', 'channel', 'event_key'], 'communication_template_scope_uq');
                $table->index(['institution_id', 'channel', 'is_active'], 'communication_template_listing_idx');
            });
        }

        if (! Schema::hasTable('communication_deliveries')) {
            Schema::create('communication_deliveries', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
                $table->string('direction', 12)->default('outbound');
                $table->string('channel', 20);
                $table->string('provider', 60);
                $table->string('event_key', 80)->default('manual');
                $table->foreignId('recipient_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('recipient_name')->nullable();
                $table->string('recipient_address', 190);
                $table->string('subject')->nullable();
                $table->longText('content');
                $table->string('status', 30)->default('queued');
                $table->string('idempotency_key', 120)->nullable()->unique();
                $table->string('provider_message_id', 190)->nullable();
                $table->unsignedSmallInteger('attempts')->default(0);
                $table->timestamp('scheduled_at')->nullable();
                $table->timestamp('queued_at')->nullable();
                $table->timestamp('sent_at')->nullable();
                $table->timestamp('delivered_at')->nullable();
                $table->timestamp('failed_at')->nullable();
                $table->text('last_error')->nullable();
                $table->json('metadata')->nullable();
                $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
                $table->index(['institution_id', 'channel', 'status', 'created_at'], 'communication_delivery_listing_idx');
                $table->index(['provider', 'provider_message_id'], 'communication_delivery_provider_idx');
                $table->index(['recipient_user_id', 'created_at'], 'communication_delivery_recipient_idx');
            });
        }

        if (Schema::hasTable('integration_connections')) {
            Schema::table('integration_connections', function (Blueprint $table): void {
                if (! Schema::hasColumn('integration_connections', 'last_error')) {
                    $table->text('last_error')->nullable()->after('last_checked_at');
                }
                if (! Schema::hasColumn('integration_connections', 'activated_at')) {
                    $table->timestamp('activated_at')->nullable()->after('last_error');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('integration_connections')) {
            $columns = array_values(array_filter(
                ['last_error', 'activated_at'],
                fn (string $column): bool => Schema::hasColumn('integration_connections', $column),
            ));
            Schema::table('integration_connections', function (Blueprint $table) use ($columns): void {
                if ($columns !== []) {
                    $table->dropColumn($columns);
                }
            });
        }

        Schema::dropIfExists('communication_deliveries');
        Schema::dropIfExists('communication_templates');
    }
};
