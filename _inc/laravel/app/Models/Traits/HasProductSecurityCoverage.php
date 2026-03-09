<?php

namespace App\Traits;

use App\Config\Constants\{BillsConstants as BC};
use Illuminate\Database\Eloquent\{Model};
use Illuminate\Database\Schema\{Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

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
	protected static function bootHasProductSecurityCoverage(): void
	{
		try {
			static::saving(function (Model $model) {
				static::enforceProductSecurityCoverageLogic($model);
			});
		} catch (\Throwable $e) {
			Log::error(static::class . '::bootHasProductSecurityCoverage — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
		}
	}

	protected function addProductSecurityCoverageColumns(Blueprint $table): void
	{
		try {
			$table->boolean(BC::COL_HAS_WRT)->default(false)->nullable()->index();
			$table->decimal(BC::COL_WRT_CST, 10, 2)->default(0.00)->nullable();
			$table->unsignedSmallInteger(BC::COL_WRT_DYS)->default(60)->nullable();
			$table->longText(BC::COL_WRT_PLC)->nullable();
			$table->boolean(BC::COL_HAS_INS)->default(false)->nullable()->index();
			$table->decimal(BC::COL_INS_CST, 10, 2)->nullable()->default(0.00);
			$table->longText(BC::COL_INS_PLC)->nullable();
			$table->boolean(BC::COL_HAS_EXT_SEC)->default(false)->nullable()->index();
			$table->decimal(BC::COL_EXT_SEC_CST, 10, 2)->nullable()->default(0.00);
		} catch (\Throwable $e) {
			Log::error(static::class . '::addProductSecurityCoverageColumns — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
		}
	}

	protected function dropProductSecurityCoverageColumns(Blueprint $table): void
	{
		try {
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
		} catch (\Throwable $e) {
			Log::error(static::class . '::dropProductSecurityCoverageColumns — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
		}
	}

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

	protected static function enforceWarrantyLogic(Model $model): void
	{
		try {
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
		} catch (\Throwable $e) {
			Log::error(static::class . '::enforceWarrantyLogic — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
		}
	}

	protected static function enforceInsuranceLogic(Model $model): void
	{
		try {
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
		} catch (\Throwable $e) {
			Log::error(static::class . '::enforceInsuranceLogic — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
		}
	}

	protected static function enforceExtraSecurityLogic(Model $model): void
	{
		try {
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
		} catch (\Throwable $e) {
			Log::error(static::class . '::enforceExtraSecurityLogic — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
		}
	}

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

	public function hasAnySecurityCoverage(): bool
	{
		try {
			return $this->getAttribute(BC::COL_HAS_WRT)
				|| $this->getAttribute(BC::COL_HAS_INS)
				|| $this->getAttribute(BC::COL_HAS_EXT_SEC);
		} catch (\Throwable $e) {
			Log::error(static::class . '::hasAnySecurityCoverage — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
			return false;
		}
	}

	public function getActiveCoverageTypes(): array
	{
		try {
			$types = [];

			if ($this->getAttribute(BC::COL_HAS_WRT)) $types[] = 'warranty';
			if ($this->getAttribute(BC::COL_HAS_INS)) $types[] = 'insurance';
			if ($this->getAttribute(BC::COL_HAS_EXT_SEC)) $types[] = 'extra_security';

			return $types;
		} catch (\Throwable $e) {
			Log::error(static::class . '::getActiveCoverageTypes — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
			return [];
		}
	}

	public function enableWarranty(?float $cost = null, ?int $days = null, ?string $policy = null): static
	{
		try {
			$this->setAttribute(BC::COL_HAS_WRT, true);

			if ($cost !== null) $this->setAttribute(BC::COL_WRT_CST, $cost);
			if ($days !== null) $this->setAttribute(BC::COL_WRT_DYS, $days);
			if ($policy !== null) $this->setAttribute(BC::COL_WRT_PLC, $policy);

			return $this;
		} catch (\Throwable $e) {
			Log::error(static::class . '::enableWarranty — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
			return $this;
		}
	}

	public function disableWarranty(): static
	{
		$this->setAttribute(BC::COL_HAS_WRT, false);
		return $this;
	}

	public function enableInsurance(?float $cost = null, ?string $policy = null): static
	{
		try {
			$this->setAttribute(BC::COL_HAS_INS, true);

			if ($cost !== null) $this->setAttribute(BC::COL_INS_CST, $cost);
			if ($policy !== null) $this->setAttribute(BC::COL_INS_PLC, $policy);

			return $this;
		} catch (\Throwable $e) {
			Log::error(static::class . '::enableInsurance — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
			return $this;
		}
	}

	public function disableInsurance(): static
	{
		$this->setAttribute(BC::COL_HAS_INS, false);
		return $this;
	}

	public function enableExtraSecurity(?float $cost = null): static
	{
		try {
			$this->setAttribute(BC::COL_HAS_EXT_SEC, true);

			if ($cost !== null) $this->setAttribute(BC::COL_EXT_SEC_CST, $cost);

			return $this;
		} catch (\Throwable $e) {
			Log::error(static::class . '::enableExtraSecurity — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
			return $this;
		}
	}

	public function disableExtraSecurity(): static
	{
		$this->setAttribute(BC::COL_HAS_EXT_SEC, false);
		return $this;
	}
}
