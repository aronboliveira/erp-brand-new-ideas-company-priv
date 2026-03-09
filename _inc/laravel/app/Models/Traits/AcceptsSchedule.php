<?php

namespace App\Traits;

use App\Config\Constants\{BillsConstants as BC};
use Illuminate\Database\Schema\{Blueprint};
use Illuminate\Support\Facades\{Log};

trait AcceptsSchedule
{
	protected function addScheduleColumns(Blueprint $table): void
	{
	    try {
    		$table->timestamp(BC::COL_SCHD_TRF_TS)->default(now())->nullable();
    		$table->timestamp(BC::COL_EXC_AT)->nullable();
    		$table->timestamp(BC::COL_CNC_AT)->nullable();
    		$table->timestamp(BC::COL_CMP_AT)->nullable();
    		$table->text(BC::COL_CNC_RS)->nullable();
	    } catch (\Throwable $e) {
	        Log::error(static::class . '::addScheduleColumns — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
	    }
	}
}
