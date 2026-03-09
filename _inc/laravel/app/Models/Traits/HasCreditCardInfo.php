<?php

namespace App\Traits;

use App\Config\Constants\{BillsConstants as BC};
use App\Enums\{MonthName};
use Illuminate\Support\Facades\{Log};

trait HasCreditCardInfo
{
	protected function addCreditCardInfoColumns($table, bool $nullableCard = true): void
	{
	    try {
    		if ($nullableCard) {
    			$table->string(BC::COL_CD_FLG, 20)->nullable();
    			$table->string(BC::COL_CD_NB, 19)->nullable();
    			$table->string(BC::COL_CD_DG, 4)->nullable();
    			$table->string(BC::COL_CD_HNM, 124)->nullable();
    			$table->enum(BC::COL_CD_EX_M, MonthName::values())->default(MonthName::January->value)->nullable();
    			$table->string(BC::COL_CD_EX_Y, 4)->nullable();
    		} else {
    			$table->string(BC::COL_CD_FLG, 20);
    			$table->string(BC::COL_CD_NB, 19);
    			$table->string(BC::COL_CD_DG, 4);
    			$table->string(BC::COL_CD_HNM, 124);
    			$table->enum(BC::COL_CD_EX_M, MonthName::values())->default(MonthName::January->value);
    			$table->string(BC::COL_CD_EX_Y, 4);
    		}
	    } catch (\Throwable $e) {
	        Log::error(static::class . '::addCreditCardInfoColumns — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
	    }
	}
	protected function dropCreditCardInfoColumns($table): void
	{
	    try {
    		$table->dropColumn([
    			BC::COL_CD_FLG,
    			BC::COL_CD_NB,
    			BC::COL_CD_DG,
    			BC::COL_CD_HNM,
    			BC::COL_CD_EX_M,
    			BC::COL_CD_EX_Y,
    		]);
	    } catch (\Throwable $e) {
	        Log::error(static::class . '::dropCreditCardInfoColumns — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
	    }
	}
}
