<?php

use App\Config\Constants\{DatabaseConstants as DC, ProjectsConstants as PJC};
use App\Enums\{EvaluationStatus, PriorityLevel};
use App\Traits\HasNullableAuditColumns;
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateGoalTrackingsTable extends Migration
{
    use HasNullableAuditColumns;
    private const TABLE = DC::TABLE_GL_TRK;
    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('company')->nullable()->index(); // * id of the company (user of type === 'company') that owns this goal tracking, nullable for now, filtered by users of type === 'company'
            $table->uuid('branch')->nullable()->index();
            $table->uuid('department')->nullable()->index();
            $table->uuid(PJC::COL_GL_TP)->index(); // ? goal_type
            $table->uuid('goal')->nullable()->index(); // ? not necessarily connected to a specific goal, but one can be a reference
            $table->date(PJC::COL_S_DT);
            $table->date(PJC::COL_E_DT);
            $table->string('subject')->nullable();
            $table->string('rating')->nullable();
            $table->string(PJC::COL_TRG_ACHV)->nullable();
            $table->text('description')->nullable();
            $table->enum('status', array_merge(array_column(EvaluationStatus::cases(), 'value'), ['Not Started', 'In Progress', 'Completed']))->default(EvaluationStatus::Pending->value)->nullable()->index(); // ? nullable for testing
            $table->unsignedDecimal('progress', 5, 2)->default(0.00)->nullable(); // * percentage of completion of the goal, from 0.00 to 100.00, nullable for now
            $table->enum('priority', array_column(PriorityLevel::cases(), 'value'))->default(PriorityLevel::Medium->value)->nullable()->index(); // ? nullable for testing
            $table->json('metrics')->nullable(); // * array of key-value pairs defining the metrics tracked during this goal tracking, nullable for now
            $table->json('attachments')->nullable(); // * array of attachment metadata, such as file name, path, size, mime type, etc, nullable for now, subject to constraints of the goal_type rules as many other fields
            $table->json('tags')->nullable();
            $table->json('steps')->nullable(); // * array of steps or milestones achieved during this goal tracking, nullable for now
            $table->json('metadata')->nullable();
            $table->foreign(PJC::COL_GL_TP)
                ->references('id')
                ->on(DC::TABLE_GOAL_TYPES)
                ->restrictOnDelete();
            foreach (
                [
                    'company' => DC::TABLE_USERS,
                    'branch' => DC::TABLE_BRANCHES,
                    'department' => DC::TABLE_DEPARTMENTS,
                    'goal' => DC::TABLE_GL,
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
                    'company',
                    'branch',
                    'department',
                    'goal',
                    PJC::COL_GL_TP,
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
