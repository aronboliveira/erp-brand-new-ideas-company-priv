<?php

namespace App\Traits;

use App\Config\Constants\DatabaseConstants as DC;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\{Log, Schema};

trait TracksFailures
{
	public const FAILURE_TRACKING_COLS = [
		DC::COL_FL_AT,
		DC::COL_FLD_RS,
		DC::COL_RTR_CT,
		DC::COL_LST_RTR_AT,
		DC::COL_ER_LG,
	];

	/** @var array<string, array<string, bool>> */
	protected static array $schemaHasFailureColumnCache = [];

	protected static function bootTracksFailures(): void
	{
		static::saving(function (Model $model): void {
			try {
				if (!method_exists($model, 'getTable')) return;

				$table = (string) $model->getTable();
				if ($table === '') return;

				static::alignFailureTrackingColumns($model, $table);
			} catch (\Throwable $e) {
				Log::warning(static::class . ' failed to align failure tracking columns', [
					'table' => method_exists($model, 'getTable') ? $model->getTable() : null,
					'id'    => method_exists($model, 'getAttribute') ? $model->getAttribute('id') : null,
					'err'   => $e->getMessage(),
					'file'  => $e->getFile(),
					'line'  => $e->getLine(),
				]);
			}
		});
	}

	/**
	 * Optional alias.
	 */
	protected static function bootedTracksFailures(): void
	{
		static::bootTracksFailures();
	}

	protected function addFailureTrackingColumns(Blueprint $table): void
	{
		$table->timestamp(DC::COL_FL_AT)->nullable();
		$table->text(DC::COL_FLD_RS)->nullable();
		$table->unsignedInteger(DC::COL_RTR_CT)->default(0);
		$table->timestamp(DC::COL_LST_RTR_AT)->nullable();
		$table->json(DC::COL_ER_LG)->nullable();
	}

	protected static function alignFailureTrackingColumns(Model $model, string $table): void
	{
		$hasFailedAt    = static::hasFailureColumnCached($table, DC::COL_FL_AT);
		$hasReason      = static::hasFailureColumnCached($table, DC::COL_FLD_RS);
		$hasRetryCount  = static::hasFailureColumnCached($table, DC::COL_RTR_CT);
		$hasLastRetryAt = static::hasFailureColumnCached($table, DC::COL_LST_RTR_AT);
		$hasErrorLog    = static::hasFailureColumnCached($table, DC::COL_ER_LG);
		if (!$hasFailedAt && !$hasReason && !$hasRetryCount && !$hasLastRetryAt && !$hasErrorLog) return;
		$now = now();
		$failedAtRaw = $hasFailedAt ? $model->getAttribute(DC::COL_FL_AT) : null;
		$reasonRaw   = $hasReason ? $model->getAttribute(DC::COL_FLD_RS) : null;
		$retryRaw    = $hasRetryCount ? $model->getAttribute(DC::COL_RTR_CT) : null;
		$lastRaw     = $hasLastRetryAt ? $model->getAttribute(DC::COL_LST_RTR_AT) : null;
		$logRaw      = $hasErrorLog ? $model->getAttribute(DC::COL_ER_LG) : null;
		$reason = null;
		if ($hasReason) {
			$r = trim((string) $reasonRaw);
			$reason = $r === '' ? null : $r;
			$model->setAttribute(DC::COL_FLD_RS, $reason);
		}
		$retryCount = 0;
		if ($hasRetryCount) {
			$retryCount = is_numeric($retryRaw) ? (int) $retryRaw : 0;
			if ($retryCount < 0) $retryCount = 0;
			$model->setAttribute(DC::COL_RTR_CT, $retryCount);
		}
		$errorLog = null;
		if ($hasErrorLog) {
			$errorLog = static::normalizeErrorLog($logRaw);
			$model->setAttribute(DC::COL_ER_LG, $errorLog);
		}
		$hasFailureSignal = (bool) ($reason !== null || $errorLog !== null);
		if ($hasFailedAt) {
			$failedAtEmpty = $failedAtRaw === null || $failedAtRaw === '';
			if ($hasFailureSignal && $failedAtEmpty) $model->setAttribute(DC::COL_FL_AT, $now);
		}
		if ($hasLastRetryAt) {
			$lastEmpty = $lastRaw === null || $lastRaw === '';
			if ($retryCount > 0 && $lastEmpty) $model->setAttribute(DC::COL_LST_RTR_AT, $now);
			if ($retryCount === 0 && !$hasFailureSignal) $model->setAttribute(DC::COL_LST_RTR_AT, null);
		}
		if ($hasFailedAt && !$hasFailureSignal && $retryCount === 0)
			$model->setAttribute(DC::COL_FL_AT, null);
		if ($hasFailedAt && $hasLastRetryAt) {
			$failedAtNow = $model->getAttribute(DC::COL_FL_AT);
			$lastNow     = $model->getAttribute(DC::COL_LST_RTR_AT);
			if (($failedAtNow === null || $failedAtNow === '') && $lastNow !== null && $lastNow !== '')
				$model->setAttribute(DC::COL_FL_AT, $lastNow);
		}
	}

	protected static function normalizeErrorLog(mixed $value): ?array
	{
		if ($value === null || $value === '') return null;
		if (is_array($value)) return $value ?: null;
		if (is_string($value)) {
			$v = trim($value);
			if ($v === '') return null;
			$decoded = json_decode($v, true);
			if (is_array($decoded)) return $decoded ?: null;
			return ['message' => $v];
		}
		if (is_object($value)) return (array) $value;
		if (is_scalar($value)) return ['message' => (string) $value];
		return null;
	}

	protected static function hasFailureColumnCached(string $table, string $column): bool
	{
		if (isset(self::$schemaHasFailureColumnCache[$table][$column]))
			return self::$schemaHasFailureColumnCache[$table][$column];

		try {
			return self::$schemaHasFailureColumnCache[$table][$column] = Schema::hasColumn($table, $column);
		} catch (\Throwable $e) {
			Log::warning(static::class . ' failed Schema::hasColumn', [
				'table'  => $table,
				'column' => $column,
				'err'    => $e->getMessage(),
				'file'   => $e->getFile(),
				'line'   => $e->getLine(),
			]);
			return self::$schemaHasFailureColumnCache[$table][$column] = false;
		}
	}
}
