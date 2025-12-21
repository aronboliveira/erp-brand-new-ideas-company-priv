<?php

use App\Config\Constants\{
    DatabaseConstants as DC,
    ProjectsConstants as PJC,
    UsersConstants as UC
};
use App\Enums\{PriorityLevel};
use App\Traits\HasNullableAuditColumns;
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateUserToDosTable extends Migration
{
    use HasNullableAuditColumns;
    private const TABLE = DC::TABLE_USR_TD;
    public function up(): void
    {
        if (!Schema::hasTable(self::TABLE))
            Schema::create(self::TABLE, function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('title')->index();
                $table->text('description')->nullable();
                $table->uuid(UC::COL_USER_ID)->index();
                $table->uuid(PJC::COL_ASG_BY)->nullable()->index(); // ? nullable for tests, nullified if the user linked is not whereIn ('type', ['admin', 'super admin', 'company']) and does not link to a non-null COL_EMP_ID that opens a table where ('manager', true)
                $table->dateTime(PJC::COL_ASG_AT)->nullable()->index();
                $table->uuid('milestone')->nullable()->index();
                $table->uuid('project')->nullable()->index();
                $table->uuid('task')->nullable()->index();
                $table->uuid('notification')->nullable();
                $table->enum('priority', array_column(PriorityLevel::cases(), 'value'))->default(PriorityLevel::Medium->value)->nullable()->index(); // ? nullable for tests, enforced at model level
                $table->unsignedDecimal('progress', 5, 2)->default(0.00)->nullable(); // ? enforced at model level to be numeric from 0.00 to 100
                $table->unsignedSmallInteger('order')->default(0)->nullable()->index(); // ? order of to-dos in a list for future implementation, enforced at model level
                $table->time(PJC::COL_E_HRS)->nullable();
                $table->date(PJC::COL_D_DATE)->nullable();
                $table->boolean(PJC::COL_IS_CP)->default(false)->index();
                $table->dateTime(PJC::COL_CMP_AT)->nullable()->index(); // ? this is nullified at model level if COL_IS_CP is not true
                $table->boolean(PJC::COL_IS_FV)->default(false)->nullable()->index(); // ? nullable for backward compatibility, enforced as boolean at model level
                $table->index([UC::COL_USER_ID, PJC::COL_IS_CP], 'idx_user_completed');
                $table->json('tags')->nullable();
                $table->json('attachments')->nullable();
                foreach (
                    [
                        'notification' => DC::TABLE_NTF,
                        'milestone' => DC::TABLE_MSS,
                        'project'   => DC::TABLE_PROJECTS,
                        'task'      => DC::TABLE_TASKS,
                        PJC::COL_ASG_BY => DC::TABLE_USERS,
                    ] as $column => $referencedTable
                )
                    $table->foreign($column)
                        ->references('id')
                        ->on($referencedTable)
                        ->nullOnDelete();
                $table->foreign(UC::COL_USER_ID)
                    ->references('id')
                    ->on(DC::TABLE_USERS)
                    ->cascadeOnDelete();
                $this->addAuditColumns($table);
            });
    }

    public function down(): void
    {
        try {
            Schema::table(self::TABLE, function (Blueprint $table): void {
                $this->dropAuditColumnForeigns($table, self::TABLE);
                foreach (
                    [
                        'notification',
                        'milestone',
                        'project',
                        'task',
                        PJC::COL_ASG_BY,
                        UC::COL_USER_ID,
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
        } catch (\Exception $e) {
            Log::warning(
                'One or more foreign keys on `' . self::TABLE . '` did not exist: '
                    . $e->getMessage()
            );
        }
        Schema::dropIfExists(self::TABLE);
    }
}
