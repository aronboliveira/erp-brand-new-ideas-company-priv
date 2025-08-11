<?php

use App\Config\Constants\{
    ActivitiesConstants,
    DatabaseConstants,
    ProjectsConstants
};
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateBugsTable extends Migration
{
    private const TABLE          = DatabaseConstants::TABLE_BUGS;
    private const COL_PROJECT_ID = ProjectsConstants::COL_PJ_ID;
    private const COL_CREATED_BY = DatabaseConstants::TABLE_CREATOR;

    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table) {
            $table->uuid('id')->primary();                    // ! CHANGED
            $table->uuid(ActivitiesConstants::COL_BUG)->default(DatabaseConstants::DEFAULT_UUID);   // ! CHANGED
            $table->uuid(self::COL_PROJECT_ID);               // ! CHANGED
            $table->string(ActivitiesConstants::COL_TT)->nullable();
            $table->string(ProjectsConstants::COL_PRT)->nullable();
            $table->date(ProjectsConstants::COL_S_DT)->nullable();
            $table->date(ProjectsConstants::COL_D_DATE)->nullable();
            $table->text(ActivitiesConstants::COL_DESC);
            $table->string(ActivitiesConstants::COL_TSK_STT)->nullable();
            $table->string(ActivitiesConstants::COL_OD)->default(0);
            $table->uuid(ProjectsConstants::COL_ASGN)->nullable();
            $table->uuid(self::COL_CREATED_BY);               // ! CHANGED
            $table->timestamps();
            foreach ([
                self::COL_PROJECT_ID => DatabaseConstants::TABLE_PROJECTS,
                ProjectsConstants::COL_ASGN => DatabaseConstants::TABLE_USERS,
                self::COL_CREATED_BY => DatabaseConstants::TABLE_USERS
            ] as $col => $tbl)
                $table->foreign($col)
                    ->references('id')->on($tbl)
                    ->cascadeOnDelete(); // * ADDED
        });
    }

    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table): void {
            foreach ([
                self::COL_PROJECT_ID,
                ProjectsConstants::COL_ASGN,
                self::COL_CREATED_BY,
            ] as $col)
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
