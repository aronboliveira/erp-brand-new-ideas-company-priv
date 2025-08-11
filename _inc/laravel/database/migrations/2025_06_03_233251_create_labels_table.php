<?php

use App\Config\Constants\{DatabaseConstants, ProjectsConstants};
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateLabelsTable extends Migration
{
    private const TABLE = DatabaseConstants::TABLE_LBL;
    private const COL_PIPELINE = ProjectsConstants::COL_PPL_ID;
    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table) {
            $table->uuid('id')->primary(); // ! CHANGED
            $table->string(ProjectsConstants::COL_LB_NM);
            $table->string(ProjectsConstants::COL_CL);
            $table->uuid(self::COL_PIPELINE); // ! CHANGED
            $table->uuid(DatabaseConstants::TABLE_CREATOR); // ! CHANGED
            $table->timestamps();
            foreach ([
                self::COL_PIPELINE                      => DatabaseConstants::TABLE_PIPELINES,
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
                self::COL_PIPELINE,
                DatabaseConstants::TABLE_CREATOR,
            ] as $col) {
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
