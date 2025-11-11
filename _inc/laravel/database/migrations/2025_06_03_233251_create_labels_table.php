<?php

use App\Config\Constants\{DatabaseConstants as DC, ProjectsConstants as PJC};
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateLabelsTable extends Migration
{
    private const TABLE = DC::TABLE_LBL;
    private const COL_PIPELINE = PJC::COL_PPL_ID;
    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string(PJC::COL_LB_NM);
            $table->string(PJC::COL_CL);
            $table->uuid(self::COL_PIPELINE);
            $table->uuid(DC::TABLE_CREATOR)->nullable();
            $table->uuid(DC::TABLE_UPDATER)->nullable();
            $table->timestamps();
            $table->foreign(self::COL_PIPELINE)
                ->references('id')
                ->on(DC::TABLE_PIPELINES)
                ->cascadeOnDelete();
            foreach (
                [
                    DC::TABLE_UPDATER    => DC::TABLE_USERS,
                    DC::TABLE_CREATOR     => DC::TABLE_USERS,
                ] as $column => $referencedTable
            )
                $table->foreign($column)
                    ->references('id')
                    ->on($referencedTable)
                    ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table): void {
            foreach (
                [
                    self::COL_PIPELINE,
                    DC::TABLE_CREATOR,
                    DC::TABLE_UPDATER
                ] as $col
            ) {
                try {
                    Schema::hasColumn(self::TABLE, $col) &&
                        $table->dropForeign([$col]);
                } catch (\Exception $e) {
                    Log::warning(
                        'Failed to execute down for '
                            . $col
                            . ' foreign key column: '
                            . $e->getMessage()
                    );
                }
            }
        });
        Schema::dropIfExists(self::TABLE);
    }
}
