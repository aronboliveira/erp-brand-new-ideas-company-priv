<?php

namespace App\Traits;

use App\Config\Constants\BillsConstants as BC;
use Illuminate\Database\{Eloquent\Model, Schema\Blueprint};
use Illuminate\Support\Facades\Log;

/**
 * @phpstan-require-extends Model
 */
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
		$expBdg = $model->getAttribute(BC::COL_EXP_BDG) ?? 0.00;
		$maxBdg = $model->getAttribute(BC::COL_MAX_BDG) ?? 9999999999.00;
		$model->setAttribute(BC::COL_EXP_BDG, $expBdg);
		$model->setAttribute(BC::COL_MAX_BDG, $maxBdg);
		if ($expBdg > $maxBdg) {
			Log::error('Expected budget cannot be greater than maximum budget.', [
				'expected' => $expBdg,
				'maximum' => $maxBdg,
				'model_id' => $model->getAttribute('id') ?? 'new',
			]);
			$model->setAttribute(BC::COL_EXP_BDG, $maxBdg);
		}
	}
}
