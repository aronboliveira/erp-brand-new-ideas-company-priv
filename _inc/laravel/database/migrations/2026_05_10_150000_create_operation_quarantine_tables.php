<?php

use App\Config\Constants\DatabaseConstants as DC;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable(DC::TABLE_OPERATION_QUARANTINES)) {
            Schema::create(DC::TABLE_OPERATION_QUARANTINES, function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('operation_ledger_id')->nullable()->index();
                $table->string('source_table', 120)->index();
                $table->string('source_type', 191)->nullable()->index();
                $table->string('source_record_id', 80)->nullable()->index();
                $table->enum('domain', ['finance', 'warehouse', 'crm', 'general'])->index();
                $table->enum('severity', ['critical', 'high', 'medium', 'low'])->default('critical')->index();
                $table->enum('status', ['pending_review', 'manual_review', 'recovered', 'rolled_back', 'dismissed'])->default('pending_review')->index();
                $table->enum('remediation_decision', ['auto_recover', 'manual_review', 'rollback'])->nullable()->index();
                $table->json('failed_criteria')->nullable();
                $table->json('validation_errors')->nullable();
                $table->json('snapshot_payload')->nullable();
                $table->json('origin_event')->nullable();
                $table->text('resolution_notes')->nullable();
                $table->uuid('actor_id')->nullable()->index();
                $table->timestamp('quarantined_at')->nullable()->index();
                $table->timestamp('resolved_at')->nullable()->index();
                $table->timestamp('expires_at')->nullable()->index();
                $table->uuid(DC::COL_TABLE_CREATOR)->nullable()->index();
                $table->uuid(DC::COL_TABLE_UPDATER)->nullable()->index();
                $table->timestamps();

                $table->index(['domain', 'severity', 'status']);
                $table->index(['source_table', 'source_record_id']);
                $table->foreign('operation_ledger_id')->references('id')->on(DC::TABLE_OPERATION_LEDGERS)->nullOnDelete();
            });
        }

        if (!Schema::hasTable(DC::TABLE_OPERATION_QUARANTINE_AUDITS)) {
            Schema::create(DC::TABLE_OPERATION_QUARANTINE_AUDITS, function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('operation_quarantine_id')->nullable()->index();
                $table->uuid('operation_ledger_id')->nullable()->index();
                $table->string('source_record_id', 80)->nullable()->index();
                $table->enum('domain', ['finance', 'warehouse', 'crm', 'general'])->index();
                $table->enum('action', ['quarantined', 'judge_decision', 'recovered', 'rolled_back', 'manual_review_requested'])->index();
                $table->enum('actor_type', ['system', 'human'])->default('system')->index();
                $table->uuid('actor_id')->nullable()->index();
                $table->text('details');
                $table->json('metadata')->nullable();
                $table->timestamp('occurred_at')->nullable()->index();
                $table->timestamps();

                $table->foreign('operation_quarantine_id')->references('id')->on(DC::TABLE_OPERATION_QUARANTINES)->nullOnDelete();
                $table->foreign('operation_ledger_id')->references('id')->on(DC::TABLE_OPERATION_LEDGERS)->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists(DC::TABLE_OPERATION_QUARANTINE_AUDITS);
        Schema::dropIfExists(DC::TABLE_OPERATION_QUARANTINES);
    }
};
