<?php

namespace App\Traits;

use App\Config\Constants\{DatabaseConstants as DC};

trait TracksFailures
{
	protected function addFailureTrackingColumns($table): void
	{
		$table->timestamp(DC::COL_FL_AT)->nullable();
		$table->text(DC::COL_FLD_RS)->nullable();
		$table->integer(DC::COL_RTR_CT)->default(0);
		$table->timestamp(DC::COL_LST_RTR_AT)->nullable();
		$table->json(DC::COL_ER_LG)->nullable();
	}
}
