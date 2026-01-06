<?php

use App\Config\Constants\{ActivitiesConstants as AC, DatabaseConstants as DC};
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateActivitiesTable extends Migration
{
	private const TABLE = DC::TABLE_ACTIVITIES;
	private const COL_USER = AC::COL_U;
	private const COL_PROJ = AC::COL_PJ;
	private const COL_TASK = AC::COL_TSK;
	private const COL_DEAL = AC::COL_DL;
	public function up(): void
	{
		Schema::create(self::TABLE, function (Blueprint $table): void {
			$table->uuid('id')->primary();
			$table->uuid(self::COL_USER);
			$table->uuid(self::COL_PROJ);
			$table->uuid(self::COL_TASK);
			$table->uuid(self::COL_DEAL);
			$table->uuid(AC::COL_MI)->nullable();
			$table->string(AC::COL_MT);
			$table->string(AC::COL_LT);
			$table->uuid(AC::COL_NT)->nullable();
			$table->timestamps();
			$table->uuid(DC::COL_TABLE_CREATOR)->nullable();
			foreach (
				[
					self::COL_USER                         => DC::TABLE_USERS,
					self::COL_PROJ                      => DC::TABLE_PROJECTS,
					self::COL_TASK                         => DC::TABLE_TASKS,
					self::COL_DEAL                         => DC::TABLE_DEALS,
					AC::COL_NT				=> DC::TABLE_NOTES,
					DC::COL_TABLE_CREATOR  => DC::TABLE_USERS,
				] as $column => $referencedTable
			)
				$table->foreign($column)
					->references('id')
					->on($referencedTable)
					->cascadeOnDelete();
		});
	}

	public function down(): void
	{
		Schema::table(self::TABLE, function (Blueprint $table): void {
			foreach (
				[
					self::COL_USER,
					self::COL_PROJ,
					self::COL_TASK,
					self::COL_DEAL,
					AC::COL_NT,
					DC::COL_TABLE_CREATOR,
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
