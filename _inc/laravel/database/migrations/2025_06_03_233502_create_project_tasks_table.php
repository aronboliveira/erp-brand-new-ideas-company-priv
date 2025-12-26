<?php

use App\Config\Constants\{
    ActivitiesConstants as AC,
    DatabaseConstants as DC,
    ProjectsConstants as PJC
};
use App\Enums\{AppModuleType, EvaluationStatus, PriorityLevel};
use App\Traits\HasNullableAuditColumns;
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateProjectTasksTable extends Migration
{
    use HasNullableAuditColumns;
    private const TABLE = DC::TABLE_PROJ_TSKS;
    public function up(): void
    {
        if (!Schema::hasTable(self::TABLE))
            Schema::create(self::TABLE, function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('code', 254)->unique(); // * generated as "PRJ-TSK-{UUID}", checking uniqueness at model level with do/while
                $table->string('name', 254)->index();
                $table->text('description')->nullable();
                $table->string('color', 254)->default('#ffeee0')->nullable();
                $table->uuid('responsible')->nullable()->index();
                $table->text(PJC::COL_ASGN)->nullable();
                $table->string(PJC::COL_ASG_BY)->nullable()->index();
                $table->timestamp(PJC::COL_ASG_AT)->nullable()->index();
                $table->unsignedInteger(PJC::COL_E_HRS)->default(0);
                $table->unsignedInteger(PJC::COL_ACT_HRS)->default(0);
                $table->timestamp(PJC::COL_LAST_ACT_AT)->nullable();
                $table->date(PJC::COL_S_DT)->nullable();
                $table->date(PJC::COL_E_DT)->nullable();
                $table->enum(AC::COL_MT, AppModuleType::values())->default(AppModuleType::Other->value)->nullable()->index(); // ? module_type, should be clamped at model level to the existing module types defined in the system
                $table->enum('priority', PriorityLevel::values())->default(PriorityLevel::Medium->value)->nullable()->index(); // ? priority_class
                $table->string('progress', 5)->default('0'); // * this ideally should be a unsignedTinyDecimal, but keeping as it for compatibility // ? constrained between 0.00 and 100 in the model through numeric conversion, clamping and restringification
                $table->enum('status', [EvaluationStatus::NotStarted->value, EvaluationStatus::InProgress->value, EvaluationStatus::Completed->value, EvaluationStatus::Undefined->value])->default(EvaluationStatus::NotStarted->value)->nullable()->index(); // ? automatically set to Completed when 'progress' is == 100; automatically set to In Progress when 'progress' > 0 and < 100; automatically set to Not Started when 'progress' == 0
                $table->string(PJC::COL_PR_CL, 50)->default(PriorityLevel::colorCodes()[PriorityLevel::Medium->value])->nullable(); // ? enforced at model level based on the PriorityLevel::colorCodes()
                $table->unsignedSmallInteger(AC::COL_OD)->default(0)->index();
                $table->unsignedSmallInteger('depth')->default(0);
                $table->uuid(PJC::COL_PJ_ID)->nullable()->index(); // * this should NEVER be null, but for compatibility/testings reasons we allow it
                $table->uuid('parent')->nullable()->index(); // ? reference to parent task if any
                $table->uuid(PJC::COL_ML_ID)->nullable()->index();
                $table->uuid(PJC::COL_STAGE_ID)->nullable()->index();
                $table->boolean(PJC::COL_IS_FV)->default(false);
                $table->boolean(PJC::COL_IS_CP)->default(false); // ? automatically set to true when 'progress' == 100
                $table->date(PJC::COL_M_AT)->nullable();
                $table->boolean('recurring')->default(false)->index();
                $table->json('attachments')->nullable();
                $table->json('involved')->nullable(); // ? list of user id/names or employee id/names involved in this task, MUST include the COL_ASG_BY, COL_ASGN and the 'responsible'
                $table->json('tags')->nullable();
                $table->json('metadata')->nullable();
                $table->json('positioning')->nullable(); // ? ui data
                $table->json('notes')->nullable();
                $table->json('rules')->nullable();
                $table->json('reactions')->nullable(); // ? emojis, comments, etc.
                $table->foreign(PJC::COL_PJ_ID)
                    ->references('id')
                    ->on(DC::TABLE_PROJECTS)
                    ->cascadeOnDelete();
                foreach (
                    [
                        PJC::COL_ML_ID => DC::TABLE_MSS,
                        PJC::COL_STAGE_ID => DC::TABLE_PROJ_STAGES,
                        PJC::COL_ASG_BY => DC::TABLE_USERS,
                        'responsible' => DC::TABLE_USERS,
                        'parent' => self::TABLE,
                    ] as $col => $tbl
                )
                    $table->foreign($col)
                        ->references('id')
                        ->on($tbl)
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
                    PJC::COL_PJ_ID,
                    PJC::COL_ML_ID,
                    PJC::COL_STAGE_ID,
                    PJC::COL_ASG_BY,
                    'responsible',
                    'parent',
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
