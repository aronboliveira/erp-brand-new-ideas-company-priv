<?php

use App\Config\Constants\DatabaseConstants as DC;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Q1 fix: original quarantine table migration declared domain enum as ('finance','warehouse','crm','general') —
 * but production code writes 'hrm', 'planning', 'heavy_io' too. With Laravel's default strict=false MySQL config,
 * those values were silently truncated to '' on INSERT, corrupting the overlay's domain attribution.
 * Also adds 'dismissed' to the action enum on the audits table (no constant existed before; Q4 introduces it).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable(DC::TABLE_OPERATION_QUARANTINES)) {
            // MySQL ENUM with extended values. Order is preserved; existing rows keep their values.
            DB::statement("ALTER TABLE " . DC::TABLE_OPERATION_QUARANTINES . " MODIFY COLUMN `domain` ENUM('finance','warehouse','crm','hrm','planning','heavy_io','general') NOT NULL");
        }

        if (Schema::hasTable(DC::TABLE_OPERATION_QUARANTINE_AUDITS)) {
            DB::statement("ALTER TABLE " . DC::TABLE_OPERATION_QUARANTINE_AUDITS . " MODIFY COLUMN `domain` ENUM('finance','warehouse','crm','hrm','planning','heavy_io','general') NOT NULL");
            DB::statement("ALTER TABLE " . DC::TABLE_OPERATION_QUARANTINE_AUDITS . " MODIFY COLUMN `action` ENUM('quarantined','judge_decision','recovered','rolled_back','manual_review_requested','dismissed') NOT NULL");
        }
    }

    public function down(): void
    {
        if (Schema::hasTable(DC::TABLE_OPERATION_QUARANTINES)) {
            // Reverting may fail if any rows already use the added values — that's intentional, force ops to acknowledge.
            DB::statement("ALTER TABLE " . DC::TABLE_OPERATION_QUARANTINES . " MODIFY COLUMN `domain` ENUM('finance','warehouse','crm','general') NOT NULL");
        }

        if (Schema::hasTable(DC::TABLE_OPERATION_QUARANTINE_AUDITS)) {
            DB::statement("ALTER TABLE " . DC::TABLE_OPERATION_QUARANTINE_AUDITS . " MODIFY COLUMN `action` ENUM('quarantined','judge_decision','recovered','rolled_back','manual_review_requested') NOT NULL");
            DB::statement("ALTER TABLE " . DC::TABLE_OPERATION_QUARANTINE_AUDITS . " MODIFY COLUMN `domain` ENUM('finance','warehouse','crm','general') NOT NULL");
        }
    }
};
