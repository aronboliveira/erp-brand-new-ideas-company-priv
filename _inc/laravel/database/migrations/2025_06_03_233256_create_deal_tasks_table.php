<?php

use App\Config\Constants\{
    ActivitiesConstants as AC,
    DatabaseConstants as DC,
    ProjectsConstants as PJC
};
use App\Traits\HasNullableAuditColumns;
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateDealTasksTable extends Migration
{

    use HasNullableAuditColumns;
    private const TABLE       = 'deal_tasks';
    private const COL_DEAL_ID = AC::COL_DL;

    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid(self::COL_DEAL_ID)->index();
            $table->string(PJC::COL_NM);
            $table->date(AC::COL_TSK_DATE);
            $table->time(AC::COL_TSK_TIME);
            $table->unsignedTinyInteger(PJC::COL_PRT)->default(1);
            $table->unsignedTinyInteger(AC::COL_TSK_STT)->default(0);
            $table->foreign(self::COL_DEAL_ID)
                ->references('id')
                ->on(DC::TABLE_DEALS)
                ->cascadeOnDelete();
            $this->addAuditColumns($table);
        });
    }

    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table): void {
            $this->dropAuditColumnForeigns($table, self::TABLE);
            foreach (
                [
                    self::COL_DEAL_ID,
                ] as $column
            ) {
                try {
                    Schema::hasColumn(self::TABLE, $column)
                        && $table->dropForeign([$column]);
                } catch (\Exception $e) {
                    Log::warning(
                        'Failed to drop foreign key for '
                            . $column
                            . ' on table '
                            . self::TABLE
                            . ': '
                            . $e->getMessage()
                    );
                }
            }
        });
        Schema::dropIfExists(self::TABLE);
    }
}
