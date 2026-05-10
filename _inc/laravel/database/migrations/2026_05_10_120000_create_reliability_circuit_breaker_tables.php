<?php

use App\Config\Constants\DatabaseConstants as DC;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable(DC::TABLE_CIRCUIT_BREAKER_STATES)) {
            Schema::create(DC::TABLE_CIRCUIT_BREAKER_STATES, function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->string('breaker_key', 191)->unique();
                $table->string('name', 191)->index();
                $table->string('domain', 80)->index();
                $table->enum('criticality', ['trivial', 'low', 'medium', 'high', 'critical'])->default('medium')->index();
                $table->enum('state', ['closed', 'open', 'half_open', 'disabled'])->default('closed')->index();
                $table->unsignedSmallInteger('sliding_window_size')->default(20);
                $table->unsignedInteger('sliding_window_seconds')->default(300);
                $table->decimal('failure_rate_threshold', 5, 2)->default(50.00);
                $table->unsignedSmallInteger('minimum_calls')->default(10);
                $table->unsignedInteger('open_state_seconds')->default(60);
                $table->unsignedSmallInteger('half_open_allowed_calls')->default(3);
                $table->decimal('half_open_success_threshold', 5, 2)->default(100.00);
                $table->boolean('half_open_conservative')->default(true)->index();
                $table->json('config')->nullable();
                $table->timestamp('opened_at')->nullable()->index();
                $table->timestamp('half_opened_at')->nullable()->index();
                $table->timestamp('closed_at')->nullable()->index();
                $table->timestamp('next_attempt_at')->nullable()->index();
                $table->text('last_failure_message')->nullable();
                $table->timestamp('expires_at')->nullable()->index();
                $table->uuid(DC::COL_TABLE_CREATOR)->nullable()->index();
                $table->uuid(DC::COL_TABLE_UPDATER)->nullable()->index();
                $table->timestamps();

                $table->index(['domain', 'criticality', 'state']);
            });
        }

        if (!Schema::hasTable(DC::TABLE_CIRCUIT_BREAKER_CALLS)) {
            Schema::create(DC::TABLE_CIRCUIT_BREAKER_CALLS, function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('circuit_breaker_state_id')->nullable()->index();
                $table->string('breaker_key', 191)->index();
                $table->enum('state_before', ['closed', 'open', 'half_open', 'disabled'])->index();
                $table->enum('state_after', ['closed', 'open', 'half_open', 'disabled'])->nullable()->index();
                $table->enum('status', ['permitted', 'rejected', 'succeeded', 'failed'])->index();
                $table->unsignedInteger('duration_ms')->nullable();
                $table->string('error_class', 191)->nullable()->index();
                $table->text('error_message')->nullable();
                $table->json('context')->nullable();
                $table->timestamp('occurred_at')->nullable()->index();
                $table->timestamp('expires_at')->nullable()->index();
                $table->uuid(DC::COL_TABLE_CREATOR)->nullable()->index();
                $table->uuid(DC::COL_TABLE_UPDATER)->nullable()->index();
                $table->timestamps();

                $table->index(['breaker_key', 'status', 'occurred_at']);
                $table->foreign('circuit_breaker_state_id')->references('id')->on(DC::TABLE_CIRCUIT_BREAKER_STATES)->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists(DC::TABLE_CIRCUIT_BREAKER_CALLS);
        Schema::dropIfExists(DC::TABLE_CIRCUIT_BREAKER_STATES);
    }
};
