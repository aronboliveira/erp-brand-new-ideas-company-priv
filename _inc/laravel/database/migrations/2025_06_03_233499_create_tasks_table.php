<?php

use App\Config\Constants\{
	ActivitiesConstants as AC,
	DatabaseConstants as DC,
	ProjectsConstants as PJC
};
use App\Enums\{AppModuleType, PriorityLevel};
use App\Traits\{HasNullableAuditColumns};
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateTasksTable extends Migration
{
	use HasNullableAuditColumns;
	private const TABLE = DC::TABLE_TASKS;
	public function up(): void
	{
		Schema::create(self::TABLE, function (Blueprint $table) {
			$table->uuid('id')->primary();
			$table->string('title', 254)->index();
			$table->string(AC::COL_A_O_M)->index(); // ? agent_or_manager // * this is probably the name of the agent or manager... keeping for compatibility
			$table->uuid(PJC::COL_AOM_ID)->nullable()->index(); // * agent_or_manager_id, reference to DC::TABLE_EMPLOYEES where the 'manager' == true
			$table->date('date')->index();
			$table->time('time');
			$table->text('description')->nullable();
			$table->enum('priority', PriorityLevel::values())->default(PriorityLevel::Medium->value)->nullable()->index(); // ? priority_class
			$table->enum(AC::COL_MT, AppModuleType::values())->default(AppModuleType::Other->value)->nullable()->index(); // ? module_type, should be clamped at model level to the existing module types defined in the system
			$table->string(AC::COL_MI)->nullable()->index(); // ? the module_id, reference to the id of the module defined by module_type, just the case index stringified for now
			$table->uuid(PJC::COL_ASGN)->nullable();
			$table->uuid(PJC::COL_PJ_ID)->nullable();
			$table->uuid(PJC::COL_ML_ID)->nullable(); // ? if found to be not null in model, through querying in the Milestone model, then merge all the involded users/employees from there into this task's involved list, ensuring uniqueness
			$table->json('stages')->nullable(); // ? list of ids for TaskStage instances, filtered as such, and where the priority, status, etc. is defined
			$table->json('attachments')->nullable();
			$table->json('involved')->nullable(); // ? list of user id/names or employee id/names involved in this task
			$table->json('tags')->nullable();
			$table->json('metadata')->nullable();
			foreach (
				[
					PJC::COL_ML_ID    => DC::TABLE_MSS,
					PJC::COL_PJ_ID    => DC::TABLE_PROJECTS,
					PJC::COL_ASGN			=> DC::TABLE_USERS,
					PJC::COL_AOM_ID   => DC::TABLE_EMPLOYEES,
				] as $col => $tbl
			)
				$table->foreign($col)
					->references('id')
					->on($tbl)
					->nullOnDelete(); // * ADDED
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
					PJC::COL_ASGN,
					PJC::COL_AOM_ID
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
