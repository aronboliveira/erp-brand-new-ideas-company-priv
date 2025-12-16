<?php

namespace App\Traits;

use App\Config\Constants\{BillsConstants as BC, DatabaseConstants as DC};
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\{Log, Schema};

trait IsCardNote
{
	use CustomerConnected, HasCreditCardInfo, HasPaymentColumns, HasPaymentConclusionColumns;
	protected function addCardNoteColumns(Blueprint $table, bool $nullableCustomer = false, $onDeleteCustomer = 'restrict', bool $nullableReconcile = true, bool $nullableInvoice = true, bool $nullableAcc = true, bool $nullableCat = true, $onDeleteAcc = 'set null', $onDeleteCat = 'set null', $billIdentifier = BC::COL_BL_ID): void
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
		$table->uuid($billIdentifier)->nullable()->index(); // * booted/saving should ensure that this Debit note belongs to either a bill or an invoice, else rollback and an error is thrown
		$table->foreign($billIdentifier)
			->references('id')
			->on(DC::TABLE_BILLS)
			->nullOnDelete();
		$this->addCreditCardInfoColumns($table); // * this is nullable only for tests
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
