<?php

namespace App\Traits;

use App\Config\Constants\{BillsConstants as BC};
use Illuminate\Database\Schema\Blueprint;

trait AcceptsSchedule
{
	protected function addScheduleColumns(Blueprint $table): void
	{
		$table->timestamp(BC::COL_SCHD_TRF_TS)->default(now())->nullable(); // ? nullable for testing purposes
		$table->timestamp(BC::COL_EXC_AT)->nullable();
		$table->timestamp(BC::COL_CNC_AT)->nullable();
		$table->timestamp(BC::COL_CMP_AT)->nullable();
		$table->text(BC::COL_CNC_RS)->nullable();
	}
}
