<?php

namespace App\Traits;

use App\Config\Constants\{BillsConstants as BC, DatabaseConstants as DC};
use App\Enums\{PaymentMethod, PaymentStatus};
use App\Enums\TransferType;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\{Log, Schema};

trait HasPaymentColumns
{
	use HasFinancialIssuingColumns;
	protected function addPaymentColumns(Blueprint $table, bool $nullableReconcile = true, bool $nullableInvoice = true): void
	{
		$this->addFinancingIssuingColumns($table, $nullableReconcile);
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
		$table->enum('status', PaymentStatus::values())->default(PaymentStatus::Pending->value)->nullable(); // ? nullable for testing purposes
		$table->unsignedSmallInteger(BC::COL_N_INTR)->default(1)->nullable(); // ? nullable for testing purposes
		$table->unsignedSmallInteger(BC::COL_CURR_N_INTR)->default(1)->nullable(); // ? nullable for testing purposes
		$nullableReconcile ? $table->timestamp(BC::COL_RCC_AT)->nullable() : $table->timestamp(BC::COL_RCC_AT);
		$table->uuid(BC::COL_RCC_BY)->nullable();
		// * Possíveis ponteiros de rela'ção
		$nullableInvoice ? $table->uuid('invoice')->nullable()->index() : $table->uuid(BC::COL_INV_ID)->index(); // todo this should be changed later, keeping for tests
		$table->uuid('payslip')->nullable()->index();
		$nullableInvoice ? $table->foreign('invoice')
			->references('id')
			->on(DC::TABLE_INVS)
			->nullOnDelete() :
			$table->foreign(BC::COL_INV_ID)
			->references('id')
			->on(DC::TABLE_INVS)
			->restrictOnDelete();
		foreach (
			[
				BC::COL_RCC_BY  => DC::TABLE_USERS,
				'payslip'      => DC::TABLE_PAY_SLP,
			] as $column => $referencedTable
		)
			$table->foreign($column)
				->references('id')
				->on($referencedTable)
				->nullOnDelete();
	}

	protected function dropPaymentColumnForeigns(Blueprint $table, ?string $tableName = null): void
	{
		$this->dropFinancialIssuingColumnForeigns($table);
		foreach (
			[
				BC::COL_RCC_BY,
				'invoice',
				'payslip',
			] as $col
		) {
			try {
				Schema::hasColumn($tableName ?? $table->getTable(), $col) &&
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
