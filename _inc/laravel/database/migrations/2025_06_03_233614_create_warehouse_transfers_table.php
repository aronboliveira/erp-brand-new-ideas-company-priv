<?php

use App\Config\Constants\DatabaseConstants;
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateWarehouseTransfersTable extends Migration
{
	private const WH = 'warehouse';
	private const F_WH = 'from_' . self::WH;
	private const T_WH = 'to_' . self::WH;
	private const TABLE = 'warehouse_transfers';
	private const COL_PROD = 'product_id';
	public function up(): void
	{
		Schema::create(self::TABLE, function (Blueprint $table) {
			$table->uuid('id')->primary();              // ! CHANGED
			$table->uuid(self::F_WH);             // ! CHANGED
			$table->uuid(self::T_WH);               // ! CHANGED
			$table->uuid(self::COL_PROD);                 // ! CHANGED
			$table->integer('quantity');
			$table->date('date');
			$table->uuid(DatabaseConstants::TABLE_CREATOR);                 // ! CHANGED
			$table->timestamps();
			foreach ([
				self::F_WH                     => DatabaseConstants::TABLE_WHS,
				self::T_WH                     => DatabaseConstants::TABLE_WHS,
				self::COL_PROD                 => DatabaseConstants::TABLE_PROD_SERVS,
				DatabaseConstants::TABLE_CREATOR => DatabaseConstants::TABLE_USERS,
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
				self::F_WH,
				self::T_WH,
				self::COL_PROD,
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
