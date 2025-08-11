<?php

use App\Config\Constants\{
    ActivitiesConstants,
    DatabaseConstants,
    ProjectsConstants
};
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateProjectTasksTable extends Migration
{
    private const TABLE = DatabaseConstants::TABLE_PROJ_TSKS;
    private const P = ProjectsConstants::COL_PRT;
    private const COL_PROJ = ProjectsConstants::COL_PJ_ID;
    private const COL_MSS = ProjectsConstants::COL_ML_ID;
    private const COL_STG = ProjectsConstants::COL_STAGE_ID;
    public function up(): void
    {
        if (!Schema::hasTable(self::TABLE))
            Schema::create(self::TABLE, function (Blueprint $table) {
                $table->uuid('id')->primary();                    // ! CHANGED
                $table->string(ProjectsConstants::COL_NM);
                $table->text(ActivitiesConstants::COL_DESC)->nullable();
                $table->integer(ProjectsConstants::COL_E_HRS)->default(0);
                $table->date(ProjectsConstants::COL_S_DT)->nullable();
                $table->date(ProjectsConstants::COL_E_DT)->nullable();
                $table->uuid(ProjectsConstants::COL_ASGN)->nullable();            // * comma-separated user IDs
                $table->string(self::P, 50)->default('medium');
                $table->string(ProjectsConstants::COL_PR_CL, 50)->nullable();
                $table->uuid(self::COL_PROJ)->default(DatabaseConstants::DEFAULT_UUID);         // ! CHANGED
                $table->uuid(self::COL_MSS)->default(DatabaseConstants::DEFAULT_UUID);       // ! CHANGED
                $table->uuid(self::COL_STG)->default(DatabaseConstants::DEFAULT_UUID);           // ! CHANGED
                $table->integer(ActivitiesConstants::COL_OD)->default(0);
                $table->uuid(DatabaseConstants::TABLE_CREATOR)->default(DatabaseConstants::DEFAULT_UUID);         // ! CHANGED
                $table->boolean(ProjectsConstants::COL_IS_FV)->default(false);  // ! CHANGED
                $table->boolean(ProjectsConstants::COL_IS_CP)->default(false);   // ! CHANGED
                $table->date(ProjectsConstants::COL_M_AT)->nullable();
                $table->string(ProjectsConstants::COL_PGR, 5)->default('0');
                $table->timestamps();
                foreach ([
                    self::COL_PROJ                 => DatabaseConstants::TABLE_PROJECTS,
                    self::COL_MSS                  => DatabaseConstants::TABLE_MSS,
                    self::COL_STG                  => DatabaseConstants::TABLE_TSK_STGS,
                    ProjectsConstants::COL_ASGN    => DatabaseConstants::TABLE_USERS,
                    DatabaseConstants::TABLE_CREATOR => DatabaseConstants::TABLE_USERS,
                ] as $col => $tbl)
                    $table->foreign($col)
                        ->references('id')
                        ->on($tbl)
                        ->cascadeOnDelete();
            });
    }

    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table): void {
            foreach ([
                self::COL_PROJ,
                self::COL_MSS,
                self::COL_STG,
                ProjectsConstants::COL_ASGN,
                DatabaseConstants::TABLE_CREATOR,
            ] as $col) {
                try {
                    Schema::hasColumn(self::TABLE, $col) &&
                        $table->dropForeign([$col]);
                } catch (\Exception $e) {
                    Log::warning(
                        'Failed to drop foreign key for '
                            . $col
                            . ': '
                            . $e->getMessage()
                    );
                }
            }
        });
        Schema::dropIfExists(self::TABLE);
    }
}
