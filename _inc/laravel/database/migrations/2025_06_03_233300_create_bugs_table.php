<?php

use App\Config\Constants\{
    ActivitiesConstants as AC,
    DatabaseConstants as DC,
    ProjectsConstants as PJC
};
use App\Traits\HasNullableAuditColumns;
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateBugsTable extends Migration
{
    use HasNullableAuditColumns;
    private const TABLE          = DC::TABLE_BUGS;
    private const COL_PROJECT_ID = PJC::COL_PJ_ID;

    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid(AC::COL_BUG)->default(DC::DEFAULT_UUID);
            $table->uuid(self::COL_PROJECT_ID);
            $table->string(AC::COL_TT)->nullable();
            $table->string(PJC::COL_PRT)->nullable();
            $table->date(PJC::COL_S_DT)->nullable();
            $table->date(PJC::COL_D_DATE)->nullable();
            $table->text(AC::COL_DESC);
            $table->string(AC::COL_TSK_STT)->nullable();
            $table->string(AC::COL_OD)->default(0);
            $table->uuid(PJC::COL_ASGN)->nullable();
            $table->foreign(self::COL_PROJECT_ID)
                ->references('id')->on(DC::TABLE_PROJECTS)
                ->cascadeOnDelete();
            foreach (
                [
                    PJC::COL_ASGN => DC::TABLE_USERS,
                ] as $col => $tbl
            )
                $table->foreign($col)
                    ->references('id')->on($tbl)
                    ->nullOnDelete();
            $this->addAuditColumns($table);
        });
    }

    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table): void {
            $this->dropAuditColumnForeigns($table, self::TABLE);
            foreach (
                [
                    self::COL_PROJECT_ID,
                    PJC::COL_ASGN,
                ] as $col
            )
                try {
                    Schema::hasColumn(self::TABLE, $col) &&
                        $table->dropForeign([$col]);
                } catch (\Exception $e) {
                    Log::warning("Failed to drop foreign key for {$col}: " . $e->getMessage());
                }
        });
        Schema::dropIfExists(self::TABLE);
    }
}
