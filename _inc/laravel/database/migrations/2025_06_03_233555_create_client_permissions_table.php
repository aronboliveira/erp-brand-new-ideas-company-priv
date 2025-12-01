<?php

use App\Config\Constants\DatabaseConstants;
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateClientPermissionsTable extends Migration
{
	private const TABLE          = 'client_permissions';
	private const COL_CLIENT_ID  = 'client_id';
	private const COL_DEAL_ID    = 'deal_id';
	private const COL_PERMISSIONS = 'permissions';

	public function up(): void
	{
		Schema::create(self::TABLE, function (Blueprint $table) {
			$table->uuid('id')->primary();                   // ! CHANGED
			$table->uuid(self::COL_CLIENT_ID);               // ! CHANGED
			$table->uuid(self::COL_DEAL_ID);                 // ! CHANGED
			$table->text(self::COL_PERMISSIONS);             // ! CHANGED
			$table->timestamps();
			$table->uuid(DatabaseConstants::COL_TABLE_CREATOR)->nullable();
			foreach (
				[
					self::COL_CLIENT_ID              => DatabaseConstants::TABLE_USERS,
					self::COL_DEAL_ID                => DatabaseConstants::TABLE_DEALS,
					DatabaseConstants::COL_TABLE_CREATOR  => DatabaseConstants::TABLE_USERS,
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
					self::COL_CLIENT_ID,
					self::COL_DEAL_ID,
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
