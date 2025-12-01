<?php

namespace App\Traits;

use App\Config\Constants\{BillsConstants as BC, DatabaseConstants as DC, SettingsConstants as SC};
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\{Log, Schema};

trait HasFinancialIssuingColumns
{
	protected function addFinancingIssuingColumns(Blueprint $table, bool $nullableReconcile = true): void
	{
		$table->string(BC::COL_CUR_ID, 3)->default(SC::DEF_SITE_CURRENCY_ID)->nullable(); // ? nullable for testing purposes
		$table->unsignedDecimal('amount', 16, 2)->default(0.00); // * it is not clear yet if this is the gross or net amount, so keeping it as is from the old implementation
		$table->float('discount')->default(0.00);
		$table->unsignedDecimal(BC::COL_SVC_FEE, 16, 2)->default(0.00)->nullable(); // ? nullable for testing purposes
		$table->unsignedDecimal(BC::COL_TXS_FEE, 16, 2)->default(0.00)->nullable(); // ? nullable for testing purposes
		$table->string('reference')->nullable();
		$table->text('description');
		$table->text('notes')->nullable();
		$table->json('attachments')->nullable();
		$table->json(BC::COL_TC)->nullable();
		$nullableReconcile ? $table->boolean(BC::COL_AUTORCC)->default(false)->nullable() : $table->boolean(BC::COL_AUTORCC)->default(false);
		$nullableReconcile ? $table->json(BC::COL_RCC_RL)->nullable() : $table->json(BC::COL_RCC_RL);
		// * Possíveis ponteiros de relação
		$table->uuid('contract')->index()->nullable();
		$table->uuid('loan')->index()->nullable();
		$table->uuid(BC::COL_PRD_SV_UNT)->index()->nullable();
		foreach (
			[
				'contract'    => DC::TABLE_CONTRACTS,
				'loan'           => DC::TABLE_LN,
				BC::COL_PRD_SV_UNT => DC::TABLE_PROD_SERV_UNITS,
			] as $column => $referencedTable
		)
			$table->foreign($column)
				->references('id')
				->on($referencedTable)
				->nullOnDelete();
	}

	protected function dropFinancialIssuingColumnForeigns(Blueprint $table): void
	{
		foreach (
			[
				'contract',
				'loan',
				BC::COL_PRD_SV_UNT,
			] as $column
		) {
			try {
				Schema::hasColumn($table->getTable(), $column)
					&&
					$table->dropForeign([$column]);
			} catch (\Exception $e) {
				Log::warning(
					'Failed to drop foreign key for '
						. $column
						. ': '
						. $e->getMessage()
				);
			}
		}
	}
}
