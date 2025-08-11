<?php

use App\Config\Constants\{DatabaseConstants, ProjectsConstants};
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\{Facades\Log, Facades\Schema, Str};

class CreateProjectsTable extends Migration
{
    private const TABLE = DatabaseConstants::TABLE_PROJECTS;
    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table) {
            $table->uuid('id')->primary();                         // ! CHANGED
            $table->string(ProjectsConstants::COL_NM);
            $table->date(ProjectsConstants::COL_S_DT)->nullable();
            $table->date(ProjectsConstants::COL_E_DT)->nullable();
            $table->uuid(ProjectsConstants::COL_CLIENT_ID)->index();                    // ! CHANGED
            $table->string(ProjectsConstants::COL_IMG)->nullable();
            $table->integer(ProjectsConstants::COL_BUDGET)->nullable();
            $table->uuid(ProjectsConstants::COL_STAGE_ID)->index();             // ! CHANGED
            $table->text(ProjectsConstants::COL_DESCRIPTION)->nullable();
            $table->string(ProjectsConstants::COL_STATUS);
            $table->string(ProjectsConstants::COL_E_HRS)->nullable();
            $table->string(ProjectsConstants::COL_PASSWORD)->nullable();                // * consider adding to $fillable
            $table->text(ProjectsConstants::COL_COPYLINK)->nullable();           // * consider adding to $fillable
            $table->text(ProjectsConstants::COL_TAGS)->nullable();
            $table->uuid(DatabaseConstants::TABLE_CREATOR)->index();                   // ! CHANGED
            $table->timestamps();
            foreach ([
                ProjectsConstants::COL_CLIENT_ID                                => DatabaseConstants::TABLE_CLIENTS,
                ProjectsConstants::COL_STAGE_ID              => DatabaseConstants::TABLE_PROJ_STAGES,
                DatabaseConstants::TABLE_CREATOR           => DatabaseConstants::TABLE_USERS,
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
                ProjectsConstants::COL_CLIENT_ID,
                ProjectsConstants::COL_STAGE_ID,
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
        Schema::dropIfExists(self::TABLE);                        // ! CHANGED
    }
}
