<?php

use App\Config\Constants\DatabaseConstants;
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateBillAccountsTable extends Migration
{
	private const TABLE = 'bill_accounts';
	private const COL_COA = 'chart_account_id';
	private const COL_REF = 'ref_id';
	public function up(): void
	{
		Schema::create(self::TABLE, function (Blueprint $table) {
			$table->uuid('id')->primary();              // ! CHANGED
			$table->uuid(self::COL_COA);            // ! CHANGED
			$table->decimal('price', 15, 2)->default(0);
			$table->text('description')->nullable();
			$table->string('type');
			$table->uuid(self::COL_REF);                      // ! CHANGED
			$table->timestamps();
			$table->uuid(DatabaseConstants::TABLE_CREATOR)->nullable();
			foreach ([
				self::COL_COA                      => DatabaseConstants::TABLE_COAS,
				self::COL_REF                      => DatabaseConstants::TABLE_BILLS,
				DatabaseConstants::TABLE_CREATOR   => DatabaseConstants::TABLE_USERS,
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
				self::COL_COA,
				self::COL_REF,
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
