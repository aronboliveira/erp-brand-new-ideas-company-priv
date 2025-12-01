<?php

use App\Config\Constants\{
	ActivitiesConstants,
	DatabaseConstants,
	ProjectsConstants
};
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateSchedulesTable extends Migration
{
	public function up(): void
	{
		Schema::create(DatabaseConstants::TABLE_SCHEDULES, function (Blueprint $table) {
			$table->uuid('id')->primary();
			$table->string(ActivitiesConstants::COL_NT)->default('No notes were written');
			$table->string(ActivitiesConstants::COL_SCHD_TP);
			$table->date(ProjectsConstants::COL_S_DT);
			$table->time(ActivitiesConstants::COL_ST_TIME);
			$table->uuid(ActivitiesConstants::COL_MI)->nullable();
			$table->integer(ActivitiesConstants::COL_MT)->nullable();
			$table->uuid(DatabaseConstants::COL_TABLE_CREATOR);
			$table->timestamps();
			$table->index(ProjectsConstants::COL_S_DT);
			$table->index([ActivitiesConstants::COL_SCHD_TP, ProjectsConstants::COL_S_DT]);
			$table
				->foreign(DatabaseConstants::COL_TABLE_CREATOR)
				->references('id')
				->on(DatabaseConstants::TABLE_USERS ?? 'users')
				->onDelete('cascade');
		});
	}

	public function down(): void
	{
		Schema::table(DatabaseConstants::TABLE_SCHEDULES, function (Blueprint $table): void {
			foreach (
				[
					DatabaseConstants::COL_TABLE_CREATOR,
				] as $column
			) {
				try {
					Schema::hasColumn(DatabaseConstants::TABLE_SCHEDULES, $column)
						&& $table->dropForeign([$column]);
				} catch (\Exception $e) {
					Log::warning(
						'Failed to drop foreign key for '
							. $column
							. ' on table '
							. DatabaseConstants::TABLE_SCHEDULES
							. ': '
							. $e->getMessage()
					);
				}
			}
		});
		Schema::dropIfExists(DatabaseConstants::TABLE_SCHEDULES);
	}
};
