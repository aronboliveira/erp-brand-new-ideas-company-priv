<?php

use App\Config\Constants\{
    ActivitiesConstants,
    DatabaseConstants,
    ProjectsConstants
};
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateLogActivitiesTable extends Migration
{
    private const TABLE = DatabaseConstants::TABLE_LOG_ACTS;
    public function up(): void
    {
        if (!Schema::hasTable(self::TABLE)) {
            Schema::create(self::TABLE, function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string(ActivitiesConstants::COL_TP);
                $table->date(ProjectsConstants::COL_S_DT);
                $table->time(ActivitiesConstants::COL_TSK_TIME);
                $table->text(ActivitiesConstants::COL_NT);
                $table->string(ActivitiesConstants::COL_MT);
                $table->uuid(ActivitiesConstants::COL_MI); // ! CHANGED
                $table->timestamps();
                $table->uuid(DatabaseConstants::COL_TABLE_CREATOR); // ! CHANGED
                foreach (
                    [
                        DatabaseConstants::COL_TABLE_CREATOR => DatabaseConstants::TABLE_USERS,
                    ] as $column => $referencedTable
                )
                    $table->foreign($column)
                        ->references('id')
                        ->on($referencedTable)
                        ->cascadeOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table): void {
            foreach (
                [
                    DatabaseConstants::COL_TABLE_CREATOR,
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
