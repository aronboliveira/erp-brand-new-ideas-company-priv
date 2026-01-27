<?php

namespace App\Traits;

use App\Config\Constants\BillsConstants as BC;
use Illuminate\Database\{Eloquent\Model, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

/**
 * HasProductSecurityCoverage trait
 * 
 * Manages warranty, insurance, and extra security coverage for products.
 * Automatically enforces business logic for coverage-related columns.
 */
trait HasProductSecurityCoverage
{
	public const PRODUCT_SECURITY_COLUMNS = [
		BC::COL_HAS_WRT,
		BC::COL_WRT_CST,
		BC::COL_WRT_DYS,
		BC::COL_WRT_PLC,
		BC::COL_HAS_INS,
		BC::COL_INS_CST,
		BC::COL_INS_PLC,
		BC::COL_HAS_EXT_SEC,
		BC::COL_EXT_SEC_CST,
	];
	/**
	 * Boot the HasProductSecurityCoverage trait
	 */
	protected static function bootHasProductSecurityCoverage(): void
	{
		static::saving(function (Model $model) {
			static::enforceProductSecurityCoverageLogic($model);
		});
	}

	/**
	 * Add product security coverage columns to migration
	 * 
	 * @param Blueprint $table
	 */
	protected function addProductSecurityCoverageColumns(Blueprint $table): void
	{
		$table->boolean(BC::COL_HAS_WRT)->default(false)->nullable()->index();
		$table->decimal(BC::COL_WRT_CST, 10, 2)->default(0.00)->nullable();
		$table->unsignedSmallInteger(BC::COL_WRT_DYS)->default(60)->nullable();
		$table->longText(BC::COL_WRT_PLC)->nullable();
		$table->boolean(BC::COL_HAS_INS)->default(false)->nullable()->index();
		$table->decimal(BC::COL_INS_CST, 10, 2)->nullable()->default(0.00);
		$table->longText(BC::COL_INS_PLC)->nullable();
		$table->boolean(BC::COL_HAS_EXT_SEC)->default(false)->nullable()->index();
		$table->decimal(BC::COL_EXT_SEC_CST, 10, 2)->nullable()->default(0.00);
	}

	/**
	 * Drop product security coverage columns from migration
	 * 
	 * @param Blueprint $table
	 */
	protected function dropProductSecurityCoverageColumns(Blueprint $table): void
	{
		$tableName = $table->getTable() ?? 'unknown';
		$columns = [
			BC::COL_HAS_WRT,
			BC::COL_WRT_CST,
			BC::COL_WRT_DYS,
			BC::COL_WRT_PLC,
			BC::COL_HAS_INS,
			BC::COL_INS_CST,
			BC::COL_INS_PLC,
			BC::COL_HAS_EXT_SEC,
			BC::COL_EXT_SEC_CST,
		];

		foreach ($columns as $column) {
			try {
				if (Schema::hasColumn($tableName, $column))
					$table->dropColumn($column);
			} catch (\Exception $e) {
				Log::warning('HasProductSecurityCoverage::dropProductSecurityCoverageColumns - Failed to drop column', [
					'trait' => 'HasProductSecurityCoverage',
					'method' => __METHOD__,
					'line' => __LINE__,
					'table' => $tableName,
					'column' => $column,
					'error' => $e->getMessage()
				]);
			}
		}

		Log::debug('HasProductSecurityCoverage::dropProductSecurityCoverageColumns - Dropped coverage columns', [
			'trait' => 'HasProductSecurityCoverage',
			'method' => __METHOD__,
			'line' => __LINE__,
			'table' => $tableName
		]);
	}

	/**
	 * Enforce product security coverage business logic
	 * 
	 * @param Model $model
	 */
	protected static function enforceProductSecurityCoverageLogic(Model $model): void
	{
		try {
			static::enforceWarrantyLogic($model);
			static::enforceInsuranceLogic($model);
			static::enforceExtraSecurityLogic($model);

			Log::debug('HasProductSecurityCoverage::enforceProductSecurityCoverageLogic - Logic enforced', [
				'trait' => 'HasProductSecurityCoverage',
				'class' => \get_class($model),
				'id' => $model->id ?? null,
				'has_warranty' => $model->getAttribute(BC::COL_HAS_WRT),
				'has_insurance' => $model->getAttribute(BC::COL_HAS_INS),
				'has_extra_security' => $model->getAttribute(BC::COL_HAS_EXT_SEC)
			]);
		} catch (\Throwable $e) {
			Log::error('HasProductSecurityCoverage::enforceProductSecurityCoverageLogic - Failed to enforce logic', [
				'trait' => 'HasProductSecurityCoverage',
				'class' => \get_class($model),
				'method' => __METHOD__,
				'line' => __LINE__,
				'id' => $model->id ?? null,
				'error' => $e->getMessage()
			]);
		}
	}

	/**
	 * Enforce warranty-related logic
	 * 
	 * @param Model $model
	 */
	protected static function enforceWarrantyLogic(Model $model): void
	{
		$hasWarranty = $model->getAttribute(BC::COL_HAS_WRT);

		if (!$hasWarranty || $hasWarranty === false || $hasWarranty === 0) {
			$model->setAttribute(BC::COL_WRT_CST, null);
			$model->setAttribute(BC::COL_WRT_DYS, null);
			$model->setAttribute(BC::COL_WRT_PLC, null);

			Log::debug('HasProductSecurityCoverage::enforceWarrantyLogic - Warranty disabled, nullified related fields', [
				'trait' => 'HasProductSecurityCoverage',
				'class' => \get_class($model),
				'id' => $model->id ?? null
			]);
		} else {
			if ($model->getAttribute(BC::COL_WRT_CST) === null)
				$model->setAttribute(BC::COL_WRT_CST, 0.00);

			if ($model->getAttribute(BC::COL_WRT_DYS) === null)
				$model->setAttribute(BC::COL_WRT_DYS, 60);

			Log::debug('HasProductSecurityCoverage::enforceWarrantyLogic - Warranty enabled, set defaults', [
				'trait' => 'HasProductSecurityCoverage',
				'class' => \get_class($model),
				'id' => $model->id ?? null,
				'warranty_cost' => $model->getAttribute(BC::COL_WRT_CST),
				'warranty_days' => $model->getAttribute(BC::COL_WRT_DYS)
			]);
		}
	}

	/**
	 * Enforce insurance-related logic
	 * 
	 * @param Model $model
	 */
	protected static function enforceInsuranceLogic(Model $model): void
	{
		$hasInsurance = $model->getAttribute(BC::COL_HAS_INS);

		if (!$hasInsurance || $hasInsurance === false || $hasInsurance === 0) {
			$model->setAttribute(BC::COL_INS_CST, null);
			$model->setAttribute(BC::COL_INS_PLC, null);

			Log::debug('HasProductSecurityCoverage::enforceInsuranceLogic - Insurance disabled, nullified related fields', [
				'trait' => 'HasProductSecurityCoverage',
				'class' => \get_class($model),
				'id' => $model->id ?? null
			]);
		} else {
			if ($model->getAttribute(BC::COL_INS_CST) === null)
				$model->setAttribute(BC::COL_INS_CST, 0.00);

			Log::debug('HasProductSecurityCoverage::enforceInsuranceLogic - Insurance enabled, set defaults', [
				'trait' => 'HasProductSecurityCoverage',
				'class' => \get_class($model),
				'id' => $model->id ?? null,
				'insurance_cost' => $model->getAttribute(BC::COL_INS_CST)
			]);
		}
	}

	/**
	 * Enforce extra security-related logic
	 * 
	 * @param Model $model
	 */
	protected static function enforceExtraSecurityLogic(Model $model): void
	{
		$hasExtraSecurity = $model->getAttribute(BC::COL_HAS_EXT_SEC);

		if (!$hasExtraSecurity || $hasExtraSecurity === false || $hasExtraSecurity === 0) {
			$model->setAttribute(BC::COL_EXT_SEC_CST, null);

			Log::debug('HasProductSecurityCoverage::enforceExtraSecurityLogic - Extra security disabled, nullified cost', [
				'trait' => 'HasProductSecurityCoverage',
				'class' => \get_class($model),
				'id' => $model->id ?? null
			]);
		} else {
			if ($model->getAttribute(BC::COL_EXT_SEC_CST) === null)
				$model->setAttribute(BC::COL_EXT_SEC_CST, 0.00);

			Log::debug('HasProductSecurityCoverage::enforceExtraSecurityLogic - Extra security enabled, set default cost', [
				'trait' => 'HasProductSecurityCoverage',
				'class' => \get_class($model),
				'id' => $model->id ?? null,
				'extra_security_cost' => $model->getAttribute(BC::COL_EXT_SEC_CST)
			]);
		}
	}

	/**
	 * Calculate total security coverage cost
	 * 
	 * @return float
	 */
	public function getTotalSecurityCoverageCost(): float
	{
		try {
			$total = 0.00;

			if ($this->getAttribute(BC::COL_HAS_WRT))
				$total += (float) ($this->getAttribute(BC::COL_WRT_CST) ?? 0.00);

			if ($this->getAttribute(BC::COL_HAS_INS))
				$total += (float) ($this->getAttribute(BC::COL_INS_CST) ?? 0.00);

			if ($this->getAttribute(BC::COL_HAS_EXT_SEC))
				$total += (float) ($this->getAttribute(BC::COL_EXT_SEC_CST) ?? 0.00);

			Log::debug('HasProductSecurityCoverage::getTotalSecurityCoverageCost - Calculated total', [
				'trait' => 'HasProductSecurityCoverage',
				'class' => static::class,
				'id' => $this->id ?? null,
				'total' => $total
			]);

			return $total;
		} catch (\Throwable $e) {
			Log::error('HasProductSecurityCoverage::getTotalSecurityCoverageCost - Failed to calculate total', [
				'trait' => 'HasProductSecurityCoverage',
				'class' => static::class,
				'method' => __METHOD__,
				'line' => __LINE__,
				'id' => $this->id ?? null,
				'error' => $e->getMessage()
			]);

			return 0.00;
		}
	}

	/**
	 * Check if product has any security coverage
	 * 
	 * @return bool
	 */
	public function hasAnySecurityCoverage(): bool
	{
		return $this->getAttribute(BC::COL_HAS_WRT)
			|| $this->getAttribute(BC::COL_HAS_INS)
			|| $this->getAttribute(BC::COL_HAS_EXT_SEC);
	}

	/**
	 * Get active coverage types
	 * 
	 * @return array
	 */
	public function getActiveCoverageTypes(): array
	{
		$types = [];

		if ($this->getAttribute(BC::COL_HAS_WRT)) $types[] = 'warranty';
		if ($this->getAttribute(BC::COL_HAS_INS)) $types[] = 'insurance';
		if ($this->getAttribute(BC::COL_HAS_EXT_SEC)) $types[] = 'extra_security';

		return $types;
	}

	/**
	 * Enable warranty with optional parameters
	 * 
	 * @param float|null $cost
	 * @param int|null $days
	 * @param string|null $policy
	 * @return static
	 */
	public function enableWarranty(?float $cost = null, ?int $days = null, ?string $policy = null): static
	{
		$this->setAttribute(BC::COL_HAS_WRT, true);

		if ($cost !== null) $this->setAttribute(BC::COL_WRT_CST, $cost);
		if ($days !== null) $this->setAttribute(BC::COL_WRT_DYS, $days);
		if ($policy !== null) $this->setAttribute(BC::COL_WRT_PLC, $policy);

		return $this;
	}

	/**
	 * Disable warranty
	 * 
	 * @return static
	 */
	public function disableWarranty(): static
	{
		$this->setAttribute(BC::COL_HAS_WRT, false);
		return $this;
	}

	/**
	 * Enable insurance with optional parameters
	 * 
	 * @param float|null $cost
	 * @param string|null $policy
	 * @return static
	 */
	public function enableInsurance(?float $cost = null, ?string $policy = null): static
	{
		$this->setAttribute(BC::COL_HAS_INS, true);

		if ($cost !== null) $this->setAttribute(BC::COL_INS_CST, $cost);
		if ($policy !== null) $this->setAttribute(BC::COL_INS_PLC, $policy);

		return $this;
	}

	/**
	 * Disable insurance
	 * 
	 * @return static
	 */
	public function disableInsurance(): static
	{
		$this->setAttribute(BC::COL_HAS_INS, false);
		return $this;
	}

	/**
	 * Enable extra security with optional cost
	 * 
	 * @param float|null $cost
	 * @return static
	 */
	public function enableExtraSecurity(?float $cost = null): static
	{
		$this->setAttribute(BC::COL_HAS_EXT_SEC, true);

		if ($cost !== null) $this->setAttribute(BC::COL_EXT_SEC_CST, $cost);

		return $this;
	}

	/**
	 * Disable extra security
	 * 
	 * @return static
	 */
	public function disableExtraSecurity(): static
	{
		$this->setAttribute(BC::COL_HAS_EXT_SEC, false);
		return $this;
	}
}
