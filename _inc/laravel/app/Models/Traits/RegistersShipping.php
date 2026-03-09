<?php

namespace App\Traits;

use App\Config\Constants\{BillsConstants as BC};
use Illuminate\Database\Schema\{Blueprint};
use Illuminate\Support\Facades\{Log};

trait RegistersShipping
{
	protected function addShippingColumns(Blueprint $table): void
	{
	    try {
    		$table->string(BC::COL_SHIP_NAME)->nullable();
    		$table->string(BC::COL_SHIP_CTR)->nullable();
    		$table->string(BC::COL_SHIP_ZIP)->nullable()->index();
    		$table->text(BC::COL_SHIP_ADR)->nullable();
    		$table->string(BC::COL_SHIP_ST)->nullable();
    		$table->string(BC::COL_SHIP_CTY)->nullable();
    		$table->string(BC::COL_SHIP_TEL)->nullable()->index();
    		$table->string(BC::COL_SHIP_EMAIL)->nullable();
    		$table->text(BC::COL_SHIP_DTL)->nullable();
	    } catch (\Throwable $e) {
	        Log::error(static::class . '::addShippingColumns — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
	    }
	}
	protected function addBillingColumns(Blueprint $table): void
	{
	    try {
    		$table->string(BC::COL_BL_NAME)->nullable();
    		$table->string(BC::COL_BL_EMAIL)->nullable();
    		$table->string(BC::COL_BL_TEL)->nullable()->index();
    		$table->string(BC::COL_BL_ZIP)->nullable()->index();
    		$table->text(BC::COL_BL_ADR)->nullable();
    		$table->string(BC::COL_BL_ST)->nullable();
    		$table->string(BC::COL_BL_CTY)->nullable();
    		$table->string(BC::COL_BL_CTR)->nullable();
    		$table->text(BC::COL_BL_DTL)->nullable();
	    } catch (\Throwable $e) {
	        Log::error(static::class . '::addBillingColumns — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
	    }
	}
}
