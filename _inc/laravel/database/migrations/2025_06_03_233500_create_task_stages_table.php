<?php

use App\Config\Constants\{
    ActivitiesConstants as AC,
    DatabaseConstants as DC,
    ProjectsConstants as PJC
};
use App\Enums\{EvaluationStatus, PriorityLevel};
use App\Traits\{HasNullableAuditColumns};
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateTaskStagesTable extends Migration
{
    use HasNullableAuditColumns;
    private const TABLE = DC::TABLE_TSK_STGS;
    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid(AC::COL_PJ)->nullable()->index(); // ? project linked to the task stage, may be null
            $table->uuid('task')->nullable()->index(); // ? task linked to the task stage, may be null only for testing due to it, for some reason, not existing in legacy code
            $table->string('name', 254)->nullable()->index();
            $table->text('description')->nullable();
            $table->dateTime(PJC::COL_D_DATE)->nullable()->index(); // due_date
            $table->enum('priority', array_column(PriorityLevel::cases(), 'value'))->default(PriorityLevel::Medium->value)->nullable()->index(); // ? nullable for tests, constrained at model level
            $table->unsignedTinyInteger('progress')->default(0)->index(); // * from 0 to 100, enforced at model level
            $table->enum('status', array_column(EvaluationStatus::cases(), 'value'))->default(EvaluationStatus::Pending->value)->nullable()->index(); // ? nullable for tests, constrained at model level
            $table->boolean('complete')->default(false);
            $table->string('color', 15)->default('#558855')->nullable(); // * constrained at model level to valid CSS color strings
            $table->unsignedSmallInteger('order')->default(0);
            $table->uuid('responsible')->nullable()->index(); // ? user id of the responsible of the task stage, may be null, needs to be a user
            $table->json('involved')->nullable(); // ? an array<string> of user ids (or names) or employee ids (or names), filtered at model level according to this
            $table->json('metadata')->nullable();
            $table->json('attachments')->nullable();
            $table->json('tags')->nullable();
            foreach (
                [
                    AC::COL_PJ => DC::TABLE_PROJECTS,
                    'task'     => DC::TABLE_TASKS,
                    'responsible'   => DC::TABLE_USERS,
                ] as $column => $referencedTable
            )
                $table->foreign($column)
                    ->references('id')
                    ->on($referencedTable)
                    ->nullOnDelete();
            $this->addAuditColumns($table);
        });
    }

    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table): void {
            $this->dropAuditColumnForeigns($table, self::TABLE);
            foreach (
                [
                    'task',
                    'responsible',
                    AC::COL_PJ,
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
