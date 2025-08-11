<?php

use App\Config\Constants\DatabaseConstants;
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateTaskFilesTable extends Migration
{
    private const TABLE = 'task_files';
    private const COL_TASK = 'task_id';
    private const COL_CREATOR = 'created_by';
    private const F = 'file';
    public function up(): void
    {
        if (!Schema::hasTable(self::TABLE))
            Schema::create(self::TABLE, function (Blueprint $table): void {
                $table->uuid('id')->primary();               // ! CHANGED
                $table->string('name');
                $table->string('extension');
                $table->string(self::F);
                $table->string(self::F . '_size');
                $table->uuid(self::COL_TASK);                     // ! CHANGED
                $table->string('user_type');
                $table->uuid(self::COL_CREATOR);                  // ! CHANGED
                $table->timestamps();
                foreach ([
                    self::COL_TASK    => DatabaseConstants::TABLE_TASKS,
                    self::COL_CREATOR => DatabaseConstants::TABLE_USERS,
                ] as $col => $tbl)
                    $table->foreign($col)
                        ->references('id')
                        ->on($tbl)
                        ->cascadeOnDelete(); // * ADDED
            });
    }

    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table): void {
            foreach ([
                self::COL_TASK,
                self::COL_CREATOR,
            ] as $col) {
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
