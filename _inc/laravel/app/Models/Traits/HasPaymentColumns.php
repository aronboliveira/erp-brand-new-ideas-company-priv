<?php

namespace App\Traits;

use App\Config\Constants\{BillsConstants as BC, DatabaseConstants as DC, SettingsConstants as SC};
use App\Enums\PaymentMethod;
use App\Enums\TransferType;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\{Log, Schema};

trait HasPaymentColumns
{
	protected function addPaymentColumns(Blueprint $table, $nullableReconcile = true): void
	{
		$table->string(BC::COL_CUR_ID, 3)->default(SC::DEF_SITE_CURRENCY_ID)->nullable(); // ? nullable for testing purposes
		$table->unsignedDecimal('amount', 16, 2)->default(0.00); // * it is not clear yet if this is the gross or net amount, so keeping it as is from the old implementation
		$table->unsignedDecimal(BC::COL_SVC_FEE, 16, 2)->default(0.00)->nullable(); // ? nullable for testing purposes
		$table->unsignedDecimal(BC::COL_TXS_FEE, 16, 2)->default(0.00)->nullable(); // ? nullable for testing purposes

		// ? a transfer can be scheduled for a future date
		$table->boolean(BC::COL_IS_SCD)->default(false)->nullable(); // ? nullable for testing purposes
		$table->boolean(BC::COL_CAN_CHG_BK)->default(false)->nullable(); // ? nullable for testing purposes

		// * Requisitos do BACEN para empresas de larga escala
		$table->string(BC::COL_PPS_CD)->index()->default('300')->nullable(); // ? nullable for testing purposes
		$table->enum(BC::COL_TRF_TP, TransferType::values())->default(TransferType::Other)->nullable(); // ? nullable for testing purposes
		$table->text(BC::COL_PPS_DS)->nullable();

		$table->json(BC::COL_TXS_LST)->nullable(); // ? nullable for testing purposes
		$table->unsignedTinyInteger(BC::COL_PAY_MTD)->default(0); // * this is not clear in the old implementation, so it will be kept for now for compatibility, so just randomize it on seeders between 0 and 1
		$table->enum(BC::COL_PAY_MTD_LB, PaymentMethod::values())->default(PaymentMethod::Other)->nullable(); // ? nullable for testing purposes
		$table->unsignedSmallInteger(BC::COL_N_INTR)->default(1)->nullable(); // ? nullable for testing purposes
		$table->unsignedSmallInteger(BC::COL_CURR_N_INTR)->default(1)->nullable(); // ? nullable for testing purposes

		$table->string('reference')->nullable();
		$table->text('description');
		$table->text('notes')->nullable();
		$table->json('attachments')->nullable();
		$table->json(BC::COL_TC)->nullable();
		$nullableReconcile ? $table->boolean(BC::COL_AUTORCC)->default(false)->nullable() : $table->boolean(BC::COL_AUTORCC)->default(false);
		$nullableReconcile ? $table->json(BC::COL_RCC_RL)->nullable() : $table->json(BC::COL_RCC_RL);
		$nullableReconcile ? $table->timestamp(BC::COL_RCC_AT)->nullable() : $table->timestamp(BC::COL_RCC_AT);
		$table->uuid(BC::COL_RCC_BY)->nullable();

		// * Possíveis ponteiros de relação
		$table->uuid('contract')->index()->nullable();
		$table->uuid('loan')->index()->nullable();
		$table->uuid('invoice')->index()->nullable();
		$table->uuid('payslip')->index()->nullable();
		$table->uuid(BC::COL_PRD_SV_UNT)->index()->nullable();
		foreach (
			[
				BC::COL_RCC_BY         => DC::TABLE_USERS,
				'contract'    => DC::TABLE_CONTRACTS,
				'loan'           => DC::TABLE_LN,
				'invoice'         => DC::TABLE_INVS,
				'payslip'      => DC::TABLE_PAY_SLP,
				BC::COL_PRD_SV_UNT => DC::TABLE_PROD_SERV_UNITS,
			] as $column => $referencedTable
		)
			$table->foreign($column)
				->references('id')
				->on($referencedTable)
				->nullOnDelete();
	}

	protected function dropPaymentColumnForeigns(Blueprint $table): void
	{
		foreach (
			[
				BC::COL_RCC_BY,
				'contract',
				'loan',
				'invoice',
				'payslip',
				BC::COL_PRD_SV_UNT,
			] as $col
		) {
			try {
				Schema::hasColumn($table->getTable(), $col) &&
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
