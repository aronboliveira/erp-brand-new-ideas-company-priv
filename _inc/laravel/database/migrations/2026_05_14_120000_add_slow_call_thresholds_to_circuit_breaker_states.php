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
            return;
        }

        Schema::table(DC::TABLE_CIRCUIT_BREAKER_STATES, function (Blueprint $table): void {
            if (!Schema::hasColumn(DC::TABLE_CIRCUIT_BREAKER_STATES, 'slow_call_duration_ms')) {
                // Calls completing slower than this are counted as "slow" for slow-call-rate evaluation. Null = feature disabled.
                $table->unsignedInteger('slow_call_duration_ms')->nullable()->after('half_open_conservative');
            }
            if (!Schema::hasColumn(DC::TABLE_CIRCUIT_BREAKER_STATES, 'slow_call_rate_threshold')) {
                // Percentage of slow calls in the closed window that trips the breaker even when failure rate is below threshold. Null = disabled.
                $table->decimal('slow_call_rate_threshold', 5, 2)->nullable()->after('slow_call_duration_ms');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable(DC::TABLE_CIRCUIT_BREAKER_STATES)) {
            return;
        }

        Schema::table(DC::TABLE_CIRCUIT_BREAKER_STATES, function (Blueprint $table): void {
            if (Schema::hasColumn(DC::TABLE_CIRCUIT_BREAKER_STATES, 'slow_call_rate_threshold')) {
                $table->dropColumn('slow_call_rate_threshold');
            }
            if (Schema::hasColumn(DC::TABLE_CIRCUIT_BREAKER_STATES, 'slow_call_duration_ms')) {
                $table->dropColumn('slow_call_duration_ms');
            }
        });
    }
};
