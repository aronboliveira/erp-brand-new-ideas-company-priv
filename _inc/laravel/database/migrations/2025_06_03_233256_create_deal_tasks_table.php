<?php

use App\Config\Constants\{
    ActivitiesConstants,
    DatabaseConstants,
    ProjectsConstants
};
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateDealTasksTable extends Migration
{
    private const TABLE       = 'deal_tasks';
    private const COL_DEAL_ID = ActivitiesConstants::COL_DL;

    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table) {
            $table->uuid('id')->primary();           // ! CHANGED
            $table->uuid(self::COL_DEAL_ID);         // ! CHANGED
            $table->string(ProjectsConstants::COL_NM);
            $table->date(ActivitiesConstants::COL_TSK_DATE);
            $table->time(ActivitiesConstants::COL_TSK_TIME);
            $table->string(ProjectsConstants::COL_PRT);
            $table->string(ActivitiesConstants::COL_TSK_STT);
            $table->timestamps();
            $table->uuid(DatabaseConstants::TABLE_CREATOR)->nullable();
            foreach ([
                self::COL_DEAL_ID                  => DatabaseConstants::TABLE_DEALS,
                DatabaseConstants::TABLE_CREATOR   => DatabaseConstants::TABLE_USERS,
            ] as $column => $referencedTable)
                $table->foreign($column)
                    ->references('id')
                    ->on($referencedTable)
                    ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table): void {
            foreach ([
                self::COL_DEAL_ID,
                DatabaseConstants::TABLE_CREATOR,
            ] as $column) {
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
