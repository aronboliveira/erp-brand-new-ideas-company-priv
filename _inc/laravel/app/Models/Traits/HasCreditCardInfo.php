<?php

namespace App\Traits;

use App\Config\Constants\{BillsConstants as BC};
use App\Enums\MonthName;

trait HasCreditCardInfo
{
	protected function addCreditCardInfoColumns($table, bool $nullableCard = true): void
	{
		if ($nullableCard) {
			$table->string(BC::COL_CD_FLG, 20)->nullable(); // * when the system is more mature, then this can be enumerated
			$table->string(BC::COL_CD_NB, 19)->nullable(); // ? this should be stored encrypted in the model and strip out any non-numeric characters
			$table->string(BC::COL_CD_DG, 4)->nullable(); // ? this should be stored encrypted in the model and strip out any non-numeric characters
			$table->string(BC::COL_CD_HNM, 124)->nullable(); // ? this should be stored encrypted in the model
			$table->enum(BC::COL_CD_EX_M, MonthName::values())->default(MonthName::January->value)->nullable(); // * model should ensure this is never less than the current month if the year is the current year
			$table->string(BC::COL_CD_EX_Y, 4)->nullable(); // * model should ensure this is never less than the current year
		} else {
			$table->string(BC::COL_CD_FLG, 20);
			$table->string(BC::COL_CD_NB, 19);
			$table->string(BC::COL_CD_DG, 4);
			$table->string(BC::COL_CD_HNM, 124);
			$table->enum(BC::COL_CD_EX_M, MonthName::values())->default(MonthName::January->value);
			$table->string(BC::COL_CD_EX_Y, 4);
		}
	}
	protected function dropCreditCardInfoColumns($table): void
	{
		$table->dropColumn([
			BC::COL_CD_FLG,
			BC::COL_CD_NB,
			BC::COL_CD_DG,
			BC::COL_CD_HNM,
			BC::COL_CD_EX_M,
			BC::COL_CD_EX_Y,
		]);
	}
}
