<?php

namespace App\Traits;

use App\Config\Constants\{BillsConstants as BC, DatabaseConstants as DC};
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\{Log, Schema};

trait IsCardNote
{
	protected function addCardNoteColumns(Blueprint $table, bool $nullableCustomer = false, $onDeleteCustomer = 'restrict', bool $nullableReconcile = true, bool $nullableInvoice = true, bool $nullableAcc = true, bool $nullableCat = true, $onDeleteAcc = 'set null', $onDeleteCat = 'set null'): void
	{
		$onDeleteCustomer = strtolower((string) trim($onDeleteCustomer));
		switch (true) {
			case $nullableCustomer && in_array($onDeleteCustomer, ['cascade', 'restrict']):
				Log::warning(
					'Foreign key constraint conflict: onDeleteCustomer was set to "' . $onDeleteCustomer . '" ' .
						'but $nullableCustomer is true. Forcing onDeleteCustomer to "set null" to maintain data integrity.'
				);
				$onDeleteCustomer = 'set null';
				break;
			case in_array($onDeleteCustomer, ['cascade', 'restrict', 'set null', 'null on delete']):
				$onDeleteCustomer = $onDeleteCustomer === 'null on delete' ? 'set null' : $onDeleteCustomer;
				break;
			default:
				$onDeleteCustomer = 'set null';
		}
		$this->addCustomerColumns($table, nullable: $nullableCustomer, onDelete: $onDeleteCustomer);
		$this->addPaymentColumns($table, nullableReconcile: $nullableReconcile, nullableInvoice: $nullableInvoice);
		$this->addPaymentConclusionColumns($table, nullableAcc: $nullableAcc, nullableCat: $nullableCat, onDeleteAcc: $onDeleteAcc, onDeleteCat: $onDeleteCat); // ? acc nullable for tests
		$table->uuid(BC::COL_BL_ID)->index()->nullable(); // * booted/saving should ensure that this Debit note belongs to either a bill or an invoice, else rollback and an error is thrown
		$table->unsignedSmallInteger(BC::COL_CURR_N_INTR)->default(1)->nullable(); // ? nullable for tests, should be booted/created at model level if null, never lower than 1
		$table->foreign(BC::COL_BL_ID)
			->references('id')
			->on(DC::TABLE_BILLS)
			->nullOnDelete();
		$table->addCreditCardInfoColumns($table); // * this is nullable only for tests
	}
	protected function dropCardNoteColumnForeigns(Blueprint $table, string $tableName): void
	{
		$this->dropCustomerColumnForeigns($table, $tableName);
		$this->dropPaymentColumnForeigns($table, $tableName);
		$this->dropPaymentConclusionColumnForeigns($table, $tableName);
		foreach (
			[
				BC::COL_BL_ID,
			] as $col
		) {
			try {
				Schema::hasColumn($tableName, $col) &&
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
	}
}
