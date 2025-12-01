<?php

namespace App\Traits;

use App\Config\Constants\{BillsConstants as BC, DatabaseConstants as DC};
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\{Log, Schema};

trait HasPaymentConclusionColumns
{
	protected function addPaymentConclusionColumns(Blueprint $table, $nullableAcc = true, $nullableCat = true, $onDeleteAcc = 'set null', $onDeleteCat = 'set null'): void
	{
		$table->date('date')->default(now()->format('Y-m-d'));
		$table->json(BC::COL_RCP_MD)->nullable();
		$onDeleteAcc = strtolower((string) trim($onDeleteAcc));
		switch (true) {
			case $nullableAcc && in_array($onDeleteAcc, ['cascade', 'restrict']):
				Log::warning(
					'Foreign key constraint conflict: onDeleteAcc was set to "' . $onDeleteAcc . '" ' .
						'but $nullableAcc is true. Forcing onDeleteAcc to "set null" to maintain data integrity.'
				);
				$onDeleteAcc = 'set null';
				break;
			case in_array($onDeleteAcc, ['cascade', 'restrict', 'set null', 'null on delete']):
				$onDeleteAcc = $onDeleteAcc === 'null on delete' ? 'set null' : $onDeleteAcc;
				break;
			default:
				$onDeleteAcc = 'set null';
		}
		$onDeleteCat = strtolower((string) trim($onDeleteCat));
		switch (true) {
			case $nullableCat && in_array($onDeleteCat, ['cascade', 'restrict']):
				Log::warning(
					'Foreign key constraint conflict: onDeleteCat was set to "' . $onDeleteCat . '" ' .
						'but $nullableCat is true. Forcing onDeleteCat to "set null" to maintain data integrity.'
				);
				$onDeleteCat = 'set null';
				break;
			case in_array($onDeleteCat, ['cascade', 'restrict', 'set null', 'null on delete']):
				$onDeleteCat = $onDeleteCat === 'null on delete' ? 'set null' : $onDeleteCat;
				break;
			default:
				$onDeleteCat = 'set null';
		}
		$nullableAcc ? $table->uuid(BC::COL_BACC_ID)->index()->nullable() : $table->uuid(BC::COL_BACC_ID)->index();
		$nullableCat ? $table->uuid(BC::COL_CAT_ID)->nullable() : $table->uuid(BC::COL_CAT_ID);
		$table->string(BC::COL_ADD_RCP)->nullable();
		$table->foreign(BC::COL_BACC_ID)
			->references('id')
			->on(DC::TABLE_BANK_ACC)
			->onDelete($onDeleteAcc);
		$table->foreign(BC::COL_CAT_ID)
			->references('id')
			->on(DC::TABLE_PROD_SERV_CATS)
			->onDelete($onDeleteCat);
	}
	protected function dropPaymentConclusionColumnForeigns(Blueprint $table, string $tableName): void
	{
		foreach (
			[
				BC::COL_BACC_ID,
				BC::COL_CAT_ID
			] as $column
		) {
			try {
				Schema::hasColumn($tableName, $column)
					&& $table->dropForeign([$column]);
			} catch (\Exception $e) {
				Log::warning(
					'Failed to drop foreign key for '
						. $column
						. ' on table '
						. $tableName
						. ': '
						. $e->getMessage()
				);
			}
		}
	}
}
