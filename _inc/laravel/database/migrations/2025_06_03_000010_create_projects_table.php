<?php

use App\Config\Constants\{DatabaseConstants as DC, ProjectsConstants as PJC};
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\{Facades\Log, Facades\Schema, Str};

class CreateProjectsTable extends Migration
{
    private const TABLE = DC::TABLE_PROJECTS;
    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string(PJC::COL_NM);
            $table->date(PJC::COL_S_DT)->nullable();
            $table->date(PJC::COL_E_DT)->nullable();
            $table->uuid(PJC::COL_CLIENT_ID)->index();
            $table->string(PJC::COL_IMG)->nullable();
            $table->decimal(PJC::COL_BUDGET)->nullable();
            $table->uuid(PJC::COL_STAGE_ID)->index()->nullable();
            $table->text(PJC::COL_DESCRIPTION)->nullable();
            $table->string(PJC::COL_STATUS);
            $table->string(PJC::COL_E_HRS)->nullable();
            $table->string(PJC::COL_PASSWORD)->nullable();                // * consider adding to $fillable
            $table->text(PJC::COL_COPYLINK)->nullable();           // * consider adding to $fillable
            $table->text(PJC::COL_TAGS)->nullable();
            $table->uuid(DC::TABLE_CREATOR)->index();
            $table->uuid(DC::TABLE_UPDATER)->nullable();
            $table->timestamps();
            foreach (
                [
                    PJC::COL_STAGE_ID          => DC::TABLE_PROJ_STAGES,
                    DC::TABLE_UPDATER           => DC::TABLE_USERS,
                ] as $column => $referencedTable
            )
                $table->foreign($column)
                    ->references('id')
                    ->on($referencedTable)
                    ->nullOnDelete();
            foreach (
                [
                    PJC::COL_CLIENT_ID          => DC::TABLE_CLIENTS,
                    DC::TABLE_CREATOR           => DC::TABLE_USERS,
                ] as $column => $referencedTable
            )
                $table->foreign($column)
                    ->references('id')
                    ->on($referencedTable)
                    ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table): void {
            foreach (
                [
                    PJC::COL_CLIENT_ID,
                    PJC::COL_STAGE_ID,
                    DC::TABLE_CREATOR,
                    DC::TABLE_UPDATER
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
