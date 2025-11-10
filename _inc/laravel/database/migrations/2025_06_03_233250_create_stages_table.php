<?php

use App\Config\Constants\{
    ActivitiesConstants as AC,
    DatabaseConstants as DC,
    ProjectsConstants as PJC
};
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateStagesTable extends Migration
{
    private const TABLE = DC::TABLE_STAGES;
    private const COL_PL = PJC::COL_PPL_ID;
    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid(self::COL_PL)->index();
            $table->string(PJC::COL_STG_NM);
            $table->integer(AC::COL_OD)->default(0);
            $table->timestamps();
            $table->uuid(DC::TABLE_CREATOR);
            $table->uuid(DC::TABLE_UPDATER)->nullable();
            foreach (
                [
                    self::COL_PL                        => DC::TABLE_PIPELINES,
                    DC::TABLE_CREATOR     => DC::TABLE_USERS,
                ] as $column => $referencedTable
            )
                $table->foreign($column)
                    ->references('id')
                    ->on($referencedTable)
                    ->cascadeOnDelete();
            $table->foreign(DC::TABLE_UPDATER)
                ->references('id')
                ->on(DC::TABLE_USERS)
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table): void {
            foreach (
                [
                    self::COL_PL,
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
