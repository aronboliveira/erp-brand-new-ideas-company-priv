<?php

use App\Config\Constants\DatabaseConstants;
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateActivityLogsTable extends Migration
{
    private const TABLE = 'activity_logs';
    private const COL_USER   = 'user_id';
    private const COL_PROJECT = 'project_id';
    private const COL_TASK   = 'task_id';
    private const COL_DEAL   = 'deal_id';
    public function up(): void
    {
        if (!Schema::hasTable(self::TABLE))
            Schema::create(self::TABLE, function (Blueprint $table): void {
                $table->uuid('id')->primary(); // ! CHANGED
                $table->uuid('user_id'); // ! CHANGED
                $table->uuid('project_id'); // ! CHANGED
                $table->uuid('task_id'); // ! CHANGED
                $table->uuid('deal_id'); // ! CHANGED
                $table->string('log_type');
                $table->text('remark')->nullable();
                $table->timestamps();
                $table->uuid(DatabaseConstants::COL_TABLE_CREATOR)->nullable();
                foreach (
                    [
                        self::COL_USER                  => DatabaseConstants::TABLE_USERS,
                        self::COL_PROJECT               => DatabaseConstants::TABLE_PROJECTS,
                        self::COL_TASK                  => DatabaseConstants::TABLE_TASKS,
                        self::COL_DEAL                  => DatabaseConstants::TABLE_DEALS,
                        DatabaseConstants::COL_TABLE_CREATOR => DatabaseConstants::TABLE_USERS,
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
                    self::COL_USER,
                    self::COL_PROJECT,
                    self::COL_TASK,
                    self::COL_DEAL,
                    DatabaseConstants::COL_TABLE_CREATOR,
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
