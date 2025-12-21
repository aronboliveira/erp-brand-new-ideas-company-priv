<?php

namespace App\Traits;

use App\Config\Constants\{BillsConstants as BC, DatabaseConstants as DC, ProjectsConstants as PJC};
use App\Enums\{BillStatus, PaymentStatus};
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\{Log, Schema};

trait HasPaymentRequestColumns
{
	use HasFinancialIssuingColumns, RegistersShipping;
	protected function addPaymentRequestColumns(Blueprint $table, ?bool $isProjection = false, ?bool $issues = false, ?int $addIssueDays = 0, ?bool $prioritizesBillStatus = true): void
	{
		$this->addFinancingIssuingColumns($table);
		// * it is not clear why the amount wasn't listed in the old implementation, so its added here as nullable for now
		$issues && $table->date(BC::COL_ISS_DT)->default(now()->addDays($addIssueDays)->format('Y-m-d'))->index(); // ? nullable para testes 
		$table->date(BC::COL_SD_DT)->nullable()->index(); // ? nullable para testes 
		$table->date(BC::COL_DUE_DT)->index();
		$table->uuid(BC::COL_CAT_ID)->nullable()->index();
		!$isProjection && $table->unsignedTinyInteger(PJC::COL_STATUS)->default(0)->index();
		$table->enum($prioritizesBillStatus ? BC::COL_STT_LB : BC::COL_BILL_STATUS, BillStatus::values())->default(BillStatus::Draft)->nullable()->index(); // ? nullable para testes
		// * booted and saving should enforce the default in nullish cases
		!$isProjection && $table->enum(BC::COL_PAY_STT, PaymentStatus::values())->default(PaymentStatus::Processing)->nullable()->index(); // ? nullable para testes
		!$isProjection && $table->unsignedTinyInteger(BC::COL_SHIP_DSP)->default(1);
		$table->unsignedTinyInteger(BC::COL_DSC_APL)->default(0); // ? whether or not it is eligible for discount
		// * it is not clear why 'discount' was not listed in the old implementation, so its added here as nullable for now
		// * booted ans saving should ensure that 'discount' is always <= 'amount'
		$table->json('taxes')->nullable();
		$this->addBillingColumns($table);
		$table->foreign(BC::COL_CAT_ID)
			->references('id')
			->on(DC::TABLE_PROD_SERV_CATS)
			->nullOnDelete();
	}

	protected function dropPaymentRequestColumnForeigns(Blueprint $table, string $tableName): void
	{
		$this->dropFinancialIssuingColumnForeigns($table);
		foreach (
			[
				BC::COL_CAT_ID,
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
