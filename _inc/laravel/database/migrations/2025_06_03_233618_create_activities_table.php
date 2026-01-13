<?php

use App\Config\Constants\{ActivitiesConstants as AC, DatabaseConstants as DC};
use App\Traits\HasNullableAuditColumns;
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};
// * it's not clear what is the purpose of this table yet
class CreateActivitiesTable extends Migration
{
	use HasNullableAuditColumns;
	private const TABLE = DC::TABLE_ACTIVITIES;
	public function up(): void
	{
		Schema::create(self::TABLE, function (Blueprint $table): void {
			$table->uuid('id')->primary();
			$table->uuid(AC::COL_U)->nullable()->index();
			$table->uuid(AC::COL_PJ)->nullable()->index();
			$table->uuid(AC::COL_TSK)->nullable();
			$table->uuid(AC::COL_DL)->nullable();
			$table->uuid(AC::COL_MI)->nullable();
			$table->string(AC::COL_MT);
			$table->string(AC::COL_LT);
			$table->uuid(AC::COL_NT)->nullable();
			foreach (
				[
					AC::COL_U => DC::TABLE_USERS,
					AC::COL_PJ => DC::TABLE_PROJECTS,
					AC::COL_TSK => DC::TABLE_TASKS,
					AC::COL_DL => DC::TABLE_DEALS,
					AC::COL_NT => DC::TABLE_NOTES,
				] as $column => $referencedTable
			)
				$table->foreign($column)
					->references('id')
					->on($referencedTable)
					->nullOnDelete();
			$this->addNullableAuditColumns($table);
		});
	}

	public function down(): void
	{
		Schema::table(self::TABLE, function (Blueprint $table): void {
			$this->dropAuditColumnForeigns($table);
			foreach (
				[
					AC::COL_U,
					AC::COL_PJ,
					AC::COL_TSK,
					AC::COL_DL,
					AC::COL_NT,
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
