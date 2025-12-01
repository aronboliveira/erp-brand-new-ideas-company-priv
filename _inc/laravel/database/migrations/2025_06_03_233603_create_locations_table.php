<?php

use App\Config\Constants\DatabaseConstants;
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateLocationsTable extends Migration
{
	private const TABLE = 'locations';
	private const COL_COMPANY = 'company_id';
	public function up(): void
	{
		Schema::create(self::TABLE, function (Blueprint $table): void {
			$table->uuid('id')->primary();
			$table->uuid(self::COL_COMPANY)->index();
			$table->boolean('is_active')->default(true);
			$table->timestamps();
			$table->uuid(DatabaseConstants::COL_TABLE_CREATOR)->nullable();
			foreach (
				[
					self::COL_COMPANY               => DatabaseConstants::TABLE_USERS,
					DatabaseConstants::COL_TABLE_CREATOR => DatabaseConstants::TABLE_USERS,
				] as $column => $referencedTable
			) {
				$table->foreign($column)
					->references('id')
					->on($referencedTable)
					->cascadeOnDelete();
			}
		});
	}

	public function down(): void
	{
		Schema::table(self::TABLE, function (Blueprint $table): void {
			foreach (
				[
					self::COL_COMPANY,
					DatabaseConstants::COL_TABLE_CREATOR,
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
