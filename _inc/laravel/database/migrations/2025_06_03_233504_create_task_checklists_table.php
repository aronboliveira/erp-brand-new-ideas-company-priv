<?php

use App\Config\Constants\DatabaseConstants;
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateTaskChecklistsTable extends Migration
{
    private const TABLE = 'task_checklists';
    private const COL_TSK = 'task_id';
    public function up(): void
    {
        if (!Schema::hasTable(self::TABLE))
            Schema::create(self::TABLE, function (Blueprint $table) {
                $table->uuid('id')->primary();              // ! CHANGED
                $table->string('name');
                $table->uuid(self::COL_TSK);                     // ! CHANGED
                $table->string('user_type');
                $table->uuid(DatabaseConstants::COL_TABLE_CREATOR);                  // ! CHANGED
                $table->integer('status')->default(0);
                $table->timestamps();
                foreach (
                    [
                        self::COL_TSK                      => DatabaseConstants::TABLE_TASKS,
                        DatabaseConstants::COL_TABLE_CREATOR   => DatabaseConstants::TABLE_USERS,
                    ] as $col => $tbl
                )
                    $table->foreign($col)
                        ->references('id')
                        ->on($tbl)
                        ->cascadeOnDelete(); // * ADDED
            });
    }

    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table): void {
            foreach (
                [
                    self::COL_TSK,
                    DatabaseConstants::COL_TABLE_CREATOR,
                ] as $col
            ) {
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
