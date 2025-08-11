<?php

use App\Config\Constants\{
    ActivitiesConstants,
    DatabaseConstants,
    ProjectsConstants
};
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateProjectStagesTable extends Migration
{
    private const TABLE         = DatabaseConstants::TABLE_PROJ_STAGES;
    private const COL_NAME      = ProjectsConstants::COL_NM;
    private const COL_COLOR     = ProjectsConstants::COL_CL;
    private const COL_ORDER     = ActivitiesConstants::COL_OD;
    private const COL_CREATED_BY = DatabaseConstants::TABLE_CREATOR;

    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table) {
            $table->uuid('id')->primary();                    // ! CHANGED
            $table->string(self::COL_NAME);
            $table->string(self::COL_COLOR, 15)->nullable();
            $table->uuid(self::COL_CREATED_BY);               // ! CHANGED
            $table->integer(self::COL_ORDER)->default(0);
            $table->timestamps();
            $table->foreign(self::COL_CREATED_BY)
                ->references('id')->on(DatabaseConstants::TABLE_USERS)
                ->cascadeOnDelete(); // * ADDED
        });
    }

    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table): void {
            try {
                Schema::hasColumn(self::TABLE, self::COL_CREATED_BY) &&
                    $table->dropForeign([self::COL_CREATED_BY]);
            } catch (\Exception $e) {
                Log::warning('Failed to drop foreign key for ' . self::COL_CREATED_BY . ': ' . $e->getMessage());
            }
        });
        Schema::dropIfExists(self::TABLE);
    }
}
