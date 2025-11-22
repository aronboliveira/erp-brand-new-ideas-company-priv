<?php

namespace App\Traits;

use App\Config\Constants\BillsConstants as BC;
use Illuminate\Database\{Eloquent\Model, Schema\Blueprint};
use Illuminate\Support\Facades\Log;

trait IsNumericBenefit
{
	protected function addNumericBenefitColumns(Blueprint $table, ?float $minBudget = 0.00, ?float $maxBudget = 9999999999.00, ?bool $nullableBudget = true): void
	{
		$table->string('name')->index();
		$table->string('description')->nullable();
		$nullableBudget ? $table->decimal(BC::COL_EXP_BDG, 15, 2)->default($minBudget)->nullable() : $table->decimal(BC::COL_EXP_BDG, 15, 2)->default($minBudget);
		$nullableBudget ? $table->decimal(BC::COL_MAX_BDG, 15, 2)->default($maxBudget)->nullable() : $table->decimal(BC::COL_MAX_BDG, 15, 2)->default($maxBudget);
	}
	protected static function verifyMaxBudget(Model $model): void
	{
		$model->{BC::COL_EXP_BDG} ??= 0.00;
		$model->{BC::COL_MAX_BDG} ??= 9999999999.00;
		if ($model->{BC::COL_EXP_BDG} > $model->{BC::COL_MAX_BDG}) {
			Log::error('Expected budget cannot be greater than maximum budget.', [
				'expected' => $model->{BC::COL_EXP_BDG},
				'maximum' => $model->{BC::COL_MAX_BDG},
				'model_id' => $model->id ?? 'new',
			]);
			$model->{BC::COL_EXP_BDG} = $model->{BC::COL_MAX_BDG};
		}
	}
}
