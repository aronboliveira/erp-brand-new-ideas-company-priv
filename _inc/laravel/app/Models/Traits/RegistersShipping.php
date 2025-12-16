<?php

namespace App\Traits;

use App\Config\Constants\BillsConstants as BC;
use Illuminate\Database\Schema\Blueprint;

trait RegistersShipping
{
	protected function addShippingColumns(Blueprint $table): void
	{
		$table->string(BC::COL_SHIP_NAME)->nullable();
		$table->string(BC::COL_SHIP_CTR)->nullable();
		$table->string(BC::COL_SHIP_ZIP)->nullable()->index(); // ? nullable for testing
		$table->text(BC::COL_SHIP_ADR)->nullable(); // ? nullable for testing
		$table->string(BC::COL_SHIP_ST)->nullable();
		$table->string(BC::COL_SHIP_CTY)->nullable();
		$table->string(BC::COL_SHIP_TEL)->nullable()->index();
		$table->string(BC::COL_SHIP_EMAIL)->nullable();
		$table->text(BC::COL_SHIP_DTL)->nullable(); // ? apartment, block, etc.
	}
	protected function addBillingColumns(Blueprint $table): void
	{
		$table->string(BC::COL_BL_NAME)->nullable();
		$table->string(BC::COL_BL_EMAIL)->nullable();
		$table->string(BC::COL_BL_TEL)->nullable()->index();
		$table->string(BC::COL_BL_ZIP)->nullable()->index();
		$table->text(BC::COL_BL_ADR)->nullable();
		$table->string(BC::COL_BL_ST)->nullable();
		$table->string(BC::COL_BL_CTY)->nullable();
		$table->string(BC::COL_BL_CTR)->nullable();
		$table->text(BC::COL_BL_DTL)->nullable(); // ? apartment, block, etc.
	}
}
