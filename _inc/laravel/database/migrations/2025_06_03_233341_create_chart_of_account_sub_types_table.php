<?php

use App\Config\Constants\{
    ChartsConstants as CHTC,
    DatabaseConstants as DC
};
use App\Traits\HasNullableAuditColumns;
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateChartOfAccountSubTypesTable extends Migration
{
    use HasNullableAuditColumns;

    private const TABLE = DC::TABLE_COA_SUBTYPES;

    public function up(): void
    {
        if (Schema::hasTable(self::TABLE)) return;

        Schema::create(self::TABLE, function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string(CHTC::COL_CD)->unique()->index()->nullable();
            $table->string(CHTC::COL_NM)->nullable();
            $table->uuid(CHTC::COL_TP)->index();
            $table->string(CHTC::COL_TP_NM);
            $table->text('description')->nullable();
            $table->json(CHTC::COL_DR_TP)->nullable();
            $table->json(CHTC::COL_CC_RL)->nullable();
            $table->json(CHTC::COL_VL_RL)->nullable();
            $table->boolean(CHTC::COL_RQ_APV)->default(false)->nullable()->index();
            $table->boolean(CHTC::COL_ALW_MNL_ENT)->default(true)->nullable()->index();
            $table->json('rules')->nullable();
            $this->addAuditColumns($table);
            $table->foreign(CHTC::COL_TP)
                ->references('id')
                ->on(DC::TABLE_COA_TYPES)
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table): void {
            try {
                if (Schema::hasColumn(self::TABLE, CHTC::COL_TP))
                    $table->dropForeign([CHTC::COL_TP]);
            } catch (\Exception $e) {
                Log::warning(
                    "Failed to drop foreign key on `" .
                        CHTC::COL_TP .
                        "`: {$e->getMessage()}"
                );
            }

            $this->dropAuditColumnForeigns($table, self::TABLE);
        });

        Schema::dropIfExists(self::TABLE);
    }
}
