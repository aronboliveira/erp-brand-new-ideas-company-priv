<?php

use App\Config\Constants\{
    ActivitiesConstants,
    DatabaseConstants,
    ProjectsConstants
};
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateStagesTable extends Migration
{
    private const TABLE = DatabaseConstants::TABLE_STAGES;
    private const COL_PL = ProjectsConstants::COL_PPL_ID;
    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table) {
            $table->uuid('id')->primary();       // ! CHANGED
            $table->string(ProjectsConstants::COL_STG_NM);
            $table->uuid(self::COL_PL);         // ! CHANGED
            $table->integer(ActivitiesConstants::COL_OD)->default(0);
            $table->timestamps();
            $table->uuid(DatabaseConstants::TABLE_CREATOR);          // ! CHANGED
            foreach ([
                self::COL_PL                        => DatabaseConstants::TABLE_PIPELINES,
                DatabaseConstants::TABLE_CREATOR     => DatabaseConstants::TABLE_USERS,
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
                self::COL_PL,
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
