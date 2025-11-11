<?php

use App\Config\Constants\{
    ActivitiesConstants as AC,
    DatabaseConstants as DC,
    ProjectsConstants as PJC
};
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateDealTasksTable extends Migration
{
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
            $table->timestamps();
            $table->uuid(DC::TABLE_CREATOR)->nullable();
            $table->uuid(DC::TABLE_UPDATER)->nullable();
            $table->foreign(self::COL_DEAL_ID)
                ->references('id')
                ->on(DC::TABLE_DEALS)
                ->cascadeOnDelete();
            foreach (
                [
                    DC::TABLE_CREATOR   => DC::TABLE_USERS,
                    DC::TABLE_UPDATER   => DC::TABLE_USERS,
                ] as $column => $referencedTable
            )
                $table->foreign($column)
                    ->references('id')
                    ->on($referencedTable)
                    ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table): void {
            foreach (
                [
                    self::COL_DEAL_ID,
                    DC::TABLE_CREATOR,
                    DC::TABLE_UPDATER,
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
