<?php

use App\Config\Constants\{ActivitiesConstants, DatabaseConstants};
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateActivitiesTable extends Migration
{
	private const TABLE = DatabaseConstants::TABLE_ACTIVITIES;
	private const COL_USER = ActivitiesConstants::COL_U;
	private const COL_PROJ = ActivitiesConstants::COL_PJ;
	private const COL_TASK = ActivitiesConstants::COL_TSK;
	private const COL_DEAL = ActivitiesConstants::COL_DL;
	public function up(): void
	{
		Schema::create(self::TABLE, function (Blueprint $table): void {
			$table->uuid('id')->primary();
			$table->uuid(self::COL_USER);
			$table->uuid(self::COL_PROJ);
			$table->uuid(self::COL_TASK);
			$table->uuid(self::COL_DEAL);
			$table->uuid(ActivitiesConstants::COL_MI)->nullable();
			$table->string(ActivitiesConstants::COL_MT);
			$table->string(ActivitiesConstants::COL_LT);
			$table->uuid(ActivitiesConstants::COL_NT)->nullable();
			$table->timestamps();
			$table->uuid(DatabaseConstants::TABLE_CREATOR)->nullable();
			foreach ([
				self::COL_USER                         => DatabaseConstants::TABLE_USERS,
				self::COL_PROJ                      => DatabaseConstants::TABLE_PROJECTS,
				self::COL_TASK                         => DatabaseConstants::TABLE_TASKS,
				self::COL_DEAL                         => DatabaseConstants::TABLE_DEALS,
				ActivitiesConstants::COL_NT				=> DatabaseConstants::TABLE_NOTES,
				DatabaseConstants::TABLE_CREATOR  => DatabaseConstants::TABLE_USERS,
			] as $column => $referencedTable)
				$table->foreign($column)
					->references('id')
					->on($referencedTable)
					->cascadeOnDelete();
		});
	}

	public function down(): void
	{
		Schema::table(self::TABLE, function (Blueprint $table): void {
			foreach ([
				self::COL_USER,
				self::COL_PROJ,
				self::COL_TASK,
				self::COL_DEAL,
				ActivitiesConstants::COL_NT,
				DatabaseConstants::TABLE_CREATOR,
			] as $column) {
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
