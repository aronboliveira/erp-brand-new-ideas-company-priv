<?php

use App\Config\Constants\{ActivitiesConstants as AC, DatabaseConstants as DC, ProjectsConstants as PJC};
use App\Enums\AppModuleType;
use App\Traits\{HasNullableAuditColumns, PlansWithSchedule};
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{DB, Log, Schema};

class CreatePlanningSchedulesTable extends Migration
{
	use HasNullableAuditColumns, PlansWithSchedule;
	private const TABLE = DC::TABLE_PLN_SCHD;
	public function up(): void
	{
		Schema::create(self::TABLE, function (Blueprint $table): void {
			$table->uuid('id')->primary();
			$this->addScheduleColumns($table); // * 'code' generated at model level as PLN-SCHD-{UUID}, checking uniqueness with do/while;
			$table->date(PJC::COL_E_DT)->nullable();
			$table->time(AC::COL_E_TIME)->nullable();
			$table->enum('start', ['single', 'recurring'])->default('single')->index();
			$table->json('events')->nullable(); // * list of id for rows in DC::TABLE_EVENTS
			$table->json('meetings')->nullable(); // * list of id for rows in DC::TABLE_MEETINGS or DC::TABLE_ZM_MT
			$table->json('tasks')->nullable(); // * list of id for rows in DC::TABLE_TASKS or in DC::TABLE_PROJ_TSKS
			$table->json('todos')->nullable(); // * list of id for rows in DC::TABLE_USR_TD
			$table->json('timesheets')->nullable(); // * list of id for rows in DC::TABLE_TMS
			$table->json('notifications')->nullable(); // * list of id for rows in DC::TABLE_NTF
			$table->json('interviews')->nullable(); // * list of id for rows in DC::TABLE_ITV_SCD
			$table->json('payments')->nullable(); // * list of id for rows in DC::TABLE_PAY, DC::TABLE_POS_PAY, DC::TABLE_INV_PAY, DC::TABLE_BL_PAY, DC::TABLE_TRS, DC::TABLE_BNK_TRF, DC::TABLE_PRJ_INV, DC::TABLE_PRC_PAY
			$table->json('stages')->nullable(); // * list of id for rows in DC::TABLE_GL_TRK, DC::TABLE_TSK_STGS and DC::TABLE_TM_TRK
			$table->json('milestones')->nullable(); // * list of id for rows in DC::TABLE_MSS
			$table->json('reports')->nullable(); // * list of id for rows in DC::TABLE_STK_RPT, DC::TABLE_DOCS
			$this->addAuditColumns($table);
		});
	}

	public function down(): void
	{
		Schema::table(self::TABLE, function (Blueprint $table): void {
			$this->dropAuditColumnForeigns($table, self::TABLE);
		});
		Schema::dropIfExists(self::TABLE);
	}
}
