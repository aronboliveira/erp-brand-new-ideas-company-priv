<?php

use App\Config\Constants\DatabaseConstants;
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{DB, Log, Schema};

class CreatePlanningSchedulesTable extends Migration
{
	private const ENTITY = 'schedule';
	private const TABLE = 'planning_' . self::ENTITY . 's';
	private const MD    = 'module';
	private const ST    = 'start';

	public function up(): void
	{
		Schema::create(self::TABLE, function (Blueprint $table): void {
			$table->uuid('id')->primary();
			$table->string('note')->default('No notes were written');
			$table->string(self::ENTITY . '_type')->index();
			$table->date(self::ST . '_date')->index();
			$table->time(self::ST . '_time');
			$table->uuid(self::MD . '_id')->index(); // ! CHANGED
			$table->string(self::MD . '_type')->index();
			$table->uuid(DatabaseConstants::COL_TABLE_CREATOR)->index();
			$table->timestamps();
			$table->foreign(DatabaseConstants::COL_TABLE_CREATOR)
				->references('id')
				->on(DatabaseConstants::TABLE_USERS)
				->cascadeOnDelete();
		});
	}

	public function down(): void
	{
		try {
			Schema::table(self::TABLE, function (Blueprint $table): void {
				if (Schema::hasColumn(self::TABLE, DatabaseConstants::COL_TABLE_CREATOR))
					$table->dropForeign([DatabaseConstants::COL_TABLE_CREATOR]);
			});
		} catch (\Exception $e) {
			Log::warning(
				'Failed to drop foreign key for '
					. DatabaseConstants::COL_TABLE_CREATOR
					. ' on table '
					. self::TABLE
					. ': '
					. $e->getMessage()
			);
		}

		Schema::dropIfExists(self::TABLE);
	}
}
