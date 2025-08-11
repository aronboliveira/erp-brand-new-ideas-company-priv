<?php

use App\Config\Constants\{
    ActivitiesConstants,
    DatabaseConstants,
    ProjectsConstants
};
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateTaskStagesTable extends Migration
{
    private const TABLE = DatabaseConstants::TABLE_TSK_STGS;
    private const COL_PROJECT = ActivitiesConstants::COL_PJ;
    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table) {
            $table->uuid('id')->primary();                    // ! CHANGED
            $table->uuid(self::COL_PROJECT)->index();               // ! CHANGED
            $table->string(ProjectsConstants::COL_STG_NM)->nullable();
            $table->boolean(ActivitiesConstants::COL_CPT)->default(false);
            $table->string(ProjectsConstants::COL_CL, 15)->nullable();
            $table->integer(ActivitiesConstants::COL_OD)->default(0);
            $table->timestamps();
            $table->uuid(DatabaseConstants::TABLE_CREATOR)->index();               // ! CHANGED
            foreach ([
                self::COL_PROJECT                 => DatabaseConstants::TABLE_PROJECTS,
                DatabaseConstants::TABLE_CREATOR  => DatabaseConstants::TABLE_USERS,
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
                self::COL_PROJECT,
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
