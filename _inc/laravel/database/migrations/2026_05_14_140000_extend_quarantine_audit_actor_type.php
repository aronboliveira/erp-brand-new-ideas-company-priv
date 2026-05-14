<?php

use App\Config\Constants\DatabaseConstants as DC;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Q5: extend operation_quarantine_audits.actor_type so audit rows can distinguish:
 *   - 'system'    — generic automated action (default)
 *   - 'human'     — manual ops intervention
 *   - 'judge'     — automated remediation judge decision (was previously conflated with 'system')
 *   - 'scheduler' — automated retention/expiration sweep (Q6)
 *
 * Filtering audit logs by actor_type previously couldn't separate "the judge decided X" from
 * "an unattributed system action did X", which made forensics ambiguous.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable(DC::TABLE_OPERATION_QUARANTINE_AUDITS)) {
            DB::statement("ALTER TABLE " . DC::TABLE_OPERATION_QUARANTINE_AUDITS . " MODIFY COLUMN `actor_type` ENUM('system','human','judge','scheduler') NOT NULL DEFAULT 'system'");
        }
    }

    public function down(): void
    {
        if (Schema::hasTable(DC::TABLE_OPERATION_QUARANTINE_AUDITS)) {
            DB::statement("ALTER TABLE " . DC::TABLE_OPERATION_QUARANTINE_AUDITS . " MODIFY COLUMN `actor_type` ENUM('system','human') NOT NULL DEFAULT 'system'");
        }
    }
};
