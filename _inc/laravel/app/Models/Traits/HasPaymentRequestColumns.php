<?php

namespace App\Traits;

use App\Config\Constants\{BillsConstants as BC, DatabaseConstants as DC, ProjectsConstants as PJC};
use App\Enums\{BillStatus, PaymentStatus};
use Illuminate\Database\Schema\{Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

trait HasPaymentRequestColumns
{
	use HasFinancialIssuingColumns, RegistersShipping;
	protected function addPaymentRequestColumns(Blueprint $table, ?bool $isProjection = false, ?bool $issues = false, ?int $addIssueDays = 0, ?bool $prioritizesBillStatus = true): void
	{
	    try {
    		$this->addFinancialIssuingColumns($table);
    				$issues && $table->date(BC::COL_ISS_DT)->default(now()->addDays($addIssueDays)->format('Y-m-d'))->index();
    		$table->date(BC::COL_SD_DT)->nullable()->index();
    		$table->date(BC::COL_DUE_DT)->index();
    		$table->uuid(BC::COL_CAT_ID)->nullable()->index();
    		!$isProjection && $table->unsignedTinyInteger(PJC::COL_STATUS)->default(0)->index();
    		$table->enum($prioritizesBillStatus ? BC::COL_STT_LB : BC::COL_BILL_STATUS, BillStatus::values())->default(BillStatus::Draft)->nullable()->index();
    				!$isProjection && $table->enum(BC::COL_PAY_STT, PaymentStatus::values())->default(PaymentStatus::Processing)->nullable()->index();
    		!$isProjection && $table->unsignedTinyInteger(BC::COL_SHIP_DSP)->default(1);
    		$table->unsignedTinyInteger(BC::COL_DSC_APL)->default(0);
    						$table->json('taxes')->nullable();
    		$this->addBillingColumns($table);
    		$table->foreign(BC::COL_CAT_ID)
    			->references('id')
    			->on(DC::TABLE_PROD_SERV_CATS)
    			->nullOnDelete();
	    } catch (\Throwable $e) {
	        Log::error(static::class . '::addPaymentRequestColumns — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
	    }
	}

	protected function dropPaymentRequestColumnForeigns(Blueprint $table, string $tableName): void
	{
	    try {
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
	    } catch (\Throwable $e) {
	        Log::error(static::class . '::dropPaymentRequestColumnForeigns — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
	    }
	}
}
