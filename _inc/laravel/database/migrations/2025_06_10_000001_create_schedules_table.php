<?php

use App\Config\Constants\{
	ActivitiesConstants as AC,
	DatabaseConstants as DC,
	ProjectsConstants as PJC
};
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateSchedulesTable extends Migration
{
	public function up(): void
	{
		Schema::create(DC::TABLE_SCHEDULES, function (Blueprint $table) {
			$table->uuid('id')->primary();
			$table->string(AC::COL_NT)->nullable();
			$table->string(AC::COL_SCHD_TP);
			$table->date(PJC::COL_S_DT);
			$table->time(AC::COL_ST_TIME);
			$table->uuid(AC::COL_MI)->nullable();
			$table->integer(AC::COL_MT)->nullable();
			$table->uuid(DC::COL_TABLE_CREATOR);
			$table->timestamps();
			$table->index(PJC::COL_S_DT);
			$table->index([AC::COL_SCHD_TP, PJC::COL_S_DT]);
			$table
				->foreign(DC::COL_TABLE_CREATOR)
				->references('id')
				->on(DC::TABLE_USERS ?? 'users')
				->onDelete('cascade');
		});
	}

	public function down(): void
	{
		Schema::table(DC::TABLE_SCHEDULES, function (Blueprint $table): void {
			foreach (
				[
					DC::COL_TABLE_CREATOR,
				] as $column
			) {
				try {
					Schema::hasColumn(DC::TABLE_SCHEDULES, $column)
						&& $table->dropForeign([$column]);
				} catch (\Exception $e) {
					Log::warning(
						'Failed to drop foreign key for '
							. $column
							. ' on table '
							. DC::TABLE_SCHEDULES
							. ': '
							. $e->getMessage()
					);
				}
			}
		});
		Schema::dropIfExists(DC::TABLE_SCHEDULES);
	}
};
