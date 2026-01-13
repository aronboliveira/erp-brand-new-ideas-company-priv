<?php

use App\Config\Constants\{
	DatabaseConstants as DC,
};
use App\Traits\{HasNullableAuditColumns, PlansWithSchedule};
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Schema};

// * this model is kind of redundant considering we have planning_schedules, but this one is more generic and can be used across modules
class CreateSchedulesTable extends Migration
{
	use HasNullableAuditColumns, PlansWithSchedule;
	public function up(): void
	{
		Schema::create(DC::TABLE_SCHEDULES, function (Blueprint $table) {
			$table->uuid('id')->primary();
			$this->addScheduleColumns($table); // * generated at model level as SCHD-{UUID}, checking uniqueness with do/while;
			$this->addAuditColumns($table);
		});
	}

	public function down(): void
	{
		Schema::table(DC::TABLE_SCHEDULES, function (Blueprint $table): void {
			$this->dropAuditColumnForeigns($table, DC::TABLE_SCHEDULES);
		});
		Schema::dropIfExists(DC::TABLE_SCHEDULES);
	}
};
