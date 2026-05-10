<?php

use App\Config\Constants\DatabaseConstants as DC;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable(DC::TABLE_OPERATION_LEDGERS)) {
            Schema::create(DC::TABLE_OPERATION_LEDGERS, function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->string('operation_key', 191)->unique();
                $table->string('operation_type', 191)->index();
                $table->string('domain', 80)->index();
                $table->enum('criticality', ['trivial', 'low', 'medium', 'high', 'critical'])->default('medium')->index();
                $table->string('isolation_level', 40)->nullable()->index();
                $table->enum('status', ['started', 'validating', 'committed', 'posted_to_ledger', 'failed', 'cancelled', 'compensating', 'compensated', 'closed'])->default('started')->index();
                $table->string('subject_type', 191)->nullable()->index();
                $table->string('subject_id', 80)->nullable()->index();
                $table->uuid('actor_id')->nullable()->index();
                $table->string('correlation_id', 191)->nullable()->index();
                $table->string('request_id', 191)->nullable()->index();
                $table->text('summary')->nullable();
                $table->json('context')->nullable();
                $table->json('result')->nullable();
                $table->text('error_message')->nullable();
                $table->timestamp('started_at')->nullable()->index();
                $table->timestamp('committed_at')->nullable()->index();
                $table->timestamp('posted_at')->nullable()->index();
                $table->timestamp('failed_at')->nullable()->index();
                $table->timestamp('closed_at')->nullable()->index();
                $table->timestamp('expires_at')->nullable()->index();
                $table->timestamp('compressed_at')->nullable()->index();
                $table->uuid(DC::COL_TABLE_CREATOR)->nullable()->index();
                $table->uuid(DC::COL_TABLE_UPDATER)->nullable()->index();
                $table->timestamps();

                $table->index(['domain', 'criticality', 'status']);
                $table->index(['subject_type', 'subject_id']);
            });
        }

        if (!Schema::hasTable(DC::TABLE_OPERATION_STEPS)) {
            Schema::create(DC::TABLE_OPERATION_STEPS, function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('operation_ledger_id')->index();
                $table->string('step_key', 191);
                $table->string('step_name', 191)->index();
                $table->enum('step_type', ['validation', 'db_write', 'outbox', 'ledger_post', 'external_io', 'decision', 'cleanup', 'other'])->default('other')->index();
                $table->unsignedInteger('sequence')->default(0)->index();
                $table->enum('status', ['pending', 'running', 'succeeded', 'failed', 'skipped', 'compensated'])->default('pending')->index();
                $table->enum('criticality', ['trivial', 'low', 'medium', 'high', 'critical'])->default('medium')->index();
                $table->enum('storage_mode', ['memory', 'database'])->default('database')->index();
                $table->json('payload')->nullable();
                $table->json('result')->nullable();
                $table->text('error_message')->nullable();
                $table->unsignedSmallInteger(DC::COL_RTR_CT)->default(0);
                $table->timestamp('started_at')->nullable()->index();
                $table->timestamp('finished_at')->nullable()->index();
                $table->uuid(DC::COL_TABLE_CREATOR)->nullable()->index();
                $table->uuid(DC::COL_TABLE_UPDATER)->nullable()->index();
                $table->timestamps();

                $table->unique(['operation_ledger_id', 'step_key']);
                $table->foreign('operation_ledger_id')->references('id')->on(DC::TABLE_OPERATION_LEDGERS)->cascadeOnDelete();
            });
        }

        if (!Schema::hasTable(DC::TABLE_OUTBOX_MESSAGES)) {
            Schema::create(DC::TABLE_OUTBOX_MESSAGES, function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->string('message_key', 191)->unique();
                $table->string('stream', 120)->index();
                $table->string('event_type', 191)->index();
                $table->enum('criticality', ['trivial', 'low', 'medium', 'high', 'critical'])->default('medium')->index();
                $table->enum('storage_mode', ['memory', 'database'])->default('database')->index();
                $table->enum('status', ['pending', 'ready', 'dispatched', 'failed', 'dead_letter', 'cancelled'])->default('pending')->index();
                $table->string('aggregate_type', 191)->nullable()->index();
                $table->string('aggregate_id', 80)->nullable()->index();
                $table->uuid('operation_ledger_id')->nullable()->index();
                $table->json('payload')->nullable();
                $table->json('headers')->nullable();
                $table->json('metadata')->nullable();
                $table->unsignedSmallInteger(DC::COL_RTR_CT)->default(0);
                $table->unsignedSmallInteger('max_attempts')->default(3);
                $table->text('last_error')->nullable();
                $table->timestamp('available_at')->nullable()->index();
                $table->timestamp('next_retry_at')->nullable()->index();
                $table->timestamp('dispatched_at')->nullable()->index();
                $table->timestamp(DC::COL_FL_AT)->nullable()->index();
                $table->timestamp('expires_at')->nullable()->index();
                $table->timestamp('compressed_at')->nullable()->index();
                $table->uuid(DC::COL_TABLE_CREATOR)->nullable()->index();
                $table->uuid(DC::COL_TABLE_UPDATER)->nullable()->index();
                $table->timestamps();

                $table->index(['status', 'criticality', 'available_at']);
                $table->index(['aggregate_type', 'aggregate_id']);
                $table->foreign('operation_ledger_id')->references('id')->on(DC::TABLE_OPERATION_LEDGERS)->nullOnDelete();
            });
        }

        if (!Schema::hasTable(DC::TABLE_INBOX_MESSAGES)) {
            Schema::create(DC::TABLE_INBOX_MESSAGES, function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->string('message_key', 191)->unique();
                $table->string('source', 120)->nullable()->index();
                $table->string('event_type', 191)->index();
                $table->enum('criticality', ['trivial', 'low', 'medium', 'high', 'critical'])->default('medium')->index();
                $table->enum('status', ['received', 'processing', 'processed', 'failed', 'ignored'])->default('received')->index();
                $table->string('payload_hash', 128)->nullable()->index();
                $table->uuid('operation_ledger_id')->nullable()->index();
                $table->json('payload')->nullable();
                $table->json('metadata')->nullable();
                $table->unsignedSmallInteger(DC::COL_RTR_CT)->default(0);
                $table->text('last_error')->nullable();
                $table->timestamp('received_at')->nullable()->index();
                $table->timestamp('processed_at')->nullable()->index();
                $table->timestamp(DC::COL_FL_AT)->nullable()->index();
                $table->timestamp('expires_at')->nullable()->index();
                $table->uuid(DC::COL_TABLE_CREATOR)->nullable()->index();
                $table->uuid(DC::COL_TABLE_UPDATER)->nullable()->index();
                $table->timestamps();

                $table->foreign('operation_ledger_id')->references('id')->on(DC::TABLE_OPERATION_LEDGERS)->nullOnDelete();
            });
        }

        if (!Schema::hasTable(DC::TABLE_OPERATIONAL_EVENTS)) {
            Schema::create(DC::TABLE_OPERATIONAL_EVENTS, function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->string('event_key', 191)->nullable()->unique();
                $table->string('event_type', 191)->index();
                $table->enum('severity', ['debug', 'info', 'notice', 'warning', 'error', 'critical'])->default('info')->index();
                $table->enum('criticality', ['trivial', 'low', 'medium', 'high', 'critical'])->default('low')->index();
                $table->enum('storage_mode', ['memory', 'database'])->default('database')->index();
                $table->string('channel', 120)->nullable()->index();
                $table->string('source', 191)->nullable()->index();
                $table->uuid('operation_ledger_id')->nullable()->index();
                $table->uuid('outbox_message_id')->nullable()->index();
                $table->string('subject_type', 191)->nullable()->index();
                $table->string('subject_id', 80)->nullable()->index();
                $table->uuid('actor_id')->nullable()->index();
                $table->enum('status', ['recorded', 'compressed', 'ignored'])->default('recorded')->index();
                $table->text('summary')->nullable();
                $table->json('context')->nullable();
                $table->timestamp('occurred_at')->nullable()->index();
                $table->timestamp('expires_at')->nullable()->index();
                $table->timestamp('compressed_at')->nullable()->index();
                $table->uuid(DC::COL_TABLE_CREATOR)->nullable()->index();
                $table->uuid(DC::COL_TABLE_UPDATER)->nullable()->index();
                $table->timestamps();

                $table->index(['channel', 'severity', 'criticality']);
                $table->index(['subject_type', 'subject_id']);
                $table->foreign('operation_ledger_id')->references('id')->on(DC::TABLE_OPERATION_LEDGERS)->nullOnDelete();
                $table->foreign('outbox_message_id')->references('id')->on(DC::TABLE_OUTBOX_MESSAGES)->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists(DC::TABLE_OPERATIONAL_EVENTS);
        Schema::dropIfExists(DC::TABLE_INBOX_MESSAGES);
        Schema::dropIfExists(DC::TABLE_OUTBOX_MESSAGES);
        Schema::dropIfExists(DC::TABLE_OPERATION_STEPS);
        Schema::dropIfExists(DC::TABLE_OPERATION_LEDGERS);
    }
};
