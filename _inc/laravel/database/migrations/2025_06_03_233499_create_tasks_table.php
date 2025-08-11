<?php

use App\Config\Constants\{
	ActivitiesConstants,
	DatabaseConstants,
	ProjectsConstants
};
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateTasksTable extends Migration
{
	private const TABLE = DatabaseConstants::TABLE_TASKS;
	private const COL_PROJ = ProjectsConstants::COL_PJ_ID;
	private const COL_MS = ProjectsConstants::COL_ML_ID;
	public function up(): void
	{
		Schema::create(self::TABLE, function (Blueprint $table) {
			$table->uuid('id')->primary();               // ! CHANGED
			$table->string(ActivitiesConstants::COL_TT);
			$table->string(ActivitiesConstants::COL_A_O_M);
			$table->date(ActivitiesConstants::COL_TSK_DATE);
			$table->time(ActivitiesConstants::COL_TSK_TIME);
			$table->text(ActivitiesConstants::COL_DESC)->nullable();
			$table->string(ActivitiesConstants::COL_MT);
			$table->uuid(ActivitiesConstants::COL_MI);                    // ! CHANGED
			$table->uuid(ProjectsConstants::COL_ASGN)->nullable();        // ! CHANGED
			$table->uuid(self::COL_PROJ)->nullable();       // ! CHANGED
			$table->uuid(self::COL_MS)->nullable();     // ! CHANGED
			$table->uuid(DatabaseConstants::TABLE_CREATOR);                   // ! CHANGED
			$table->timestamps();
			foreach ([
				self::COL_PROJ                  => DatabaseConstants::TABLE_PROJECTS,
				self::COL_MS                    => DatabaseConstants::TABLE_MSS,
				ProjectsConstants::COL_ASGN			=> DatabaseConstants::TABLE_USERS,
				DatabaseConstants::TABLE_CREATOR => DatabaseConstants::TABLE_USERS,
			] as $col => $tbl)
				$table->foreign($col)
					->references('id')
					->on($tbl)
					->cascadeOnDelete(); // * ADDED
		});
	}

	public function down(): void
	{
		Schema::table(self::TABLE, function (Blueprint $table): void {
			foreach ([
				self::COL_PROJ,
				self::COL_MS,
				ProjectsConstants::COL_ASGN,
				DatabaseConstants::TABLE_CREATOR,
			] as $col) {
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
