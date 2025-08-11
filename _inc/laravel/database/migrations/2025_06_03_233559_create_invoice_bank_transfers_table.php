<?php

use App\Config\Constants\DatabaseConstants;
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateInvoiceBankTransfersTable extends Migration
{
	private const TABLE = DatabaseConstants::TABLE_INV_BANK_TRANSFERS;
	private const COL_INV = DatabaseConstants::INV_BANK_TRANSFER_INV;
	private const COL_ORDER = DatabaseConstants::INV_BANK_TRANSFER_ORDER;
	public function up(): void
	{
		Schema::create(self::TABLE, function (Blueprint $table) {
			$table->id();
			$table->uuid(self::COL_INV);
			$table->uuid(self::COL_ORDER);
			$table->decimal('amount', 15, 2);
			$table->string('status');
			$table->date('date');
			$table->string('receipt')->nullable();
			$table->uuid(DatabaseConstants::TABLE_CREATOR);
			$table->timestamps();
			foreach ([
				self::COL_INV                     => DatabaseConstants::TABLE_INVS,
				self::COL_ORDER                   => DatabaseConstants::TABLE_ORDERS,
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
				self::COL_INV,
				self::COL_ORDER,
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
