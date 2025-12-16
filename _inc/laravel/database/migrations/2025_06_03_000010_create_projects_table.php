<?php

use App\Config\Constants\{DatabaseConstants as DC, ProjectsConstants as PJC};
use App\Traits\HasNullableAuditColumns;
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\{Facades\Log, Facades\Schema, Str};

class CreateProjectsTable extends Migration
{
    use HasNullableAuditColumns;
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
            $table->decimal(PJC::COL_BUDGET, 15, 2)->nullable();
            $table->uuid(PJC::COL_STAGE_ID)->nullable()->index();
            $table->text(PJC::COL_DESCRIPTION)->nullable();
            $table->string(PJC::COL_STATUS);
            $table->string(PJC::COL_E_HRS)->nullable();
            $table->string(PJC::COL_PASSWORD)->nullable();                // * consider adding to $fillable
            $table->text(PJC::COL_COPYLINK)->nullable();           // * consider adding to $fillable
            $table->text(PJC::COL_TAGS)->nullable();
            foreach (
                [
                    PJC::COL_STAGE_ID          => DC::TABLE_PROJ_STAGES,
                ] as $column => $referencedTable
            )
                $table->foreign($column)
                    ->references('id')
                    ->on($referencedTable)
                    ->nullOnDelete();
            foreach (
                [
                    PJC::COL_CLIENT_ID          => DC::TABLE_CLIENTS,
                ] as $column => $referencedTable
            )
                $table->foreign($column)
                    ->references('id')
                    ->on($referencedTable)
                    ->cascadeOnDelete();
            $this->addAuditColumns($table);
        });
    }

    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table): void {
            $this->dropAuditColumnForeigns($table, self::TABLE);
            foreach (
                [
                    PJC::COL_CLIENT_ID,
                    PJC::COL_STAGE_ID,
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
