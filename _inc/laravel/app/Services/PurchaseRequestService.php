<?php

namespace App\Services;

use App\Config\Constants\DatabaseConstants as DC;
use App\Models\Purchase;
use App\Traits\ChecksLogin;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\{DB, Log};

class PurchaseRequestService
{
	use ChecksLogin;

	/**
	 * Get total purchase amount for authenticated user
	 * Optionally filter by current month
	 * 
	 * @param bool $month Whether to filter by current month
	 * @return string|RedirectResponse Formatted price or redirect if not authenticated
	 */
	public function getTotalPurchaseAmount(bool $month = false): string|RedirectResponse
	{
		if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse)
			return $userOrRedirect;

		$user = $userOrRedirect;

		try {
			$sql = "select id from " . DC::TABLE_PURCHASES . " where " . DC::COL_TABLE_CREATOR . " = ?";
			$bindings = [$user->creatorId()];

			if ($month) {
				$sql .= " and month(created_at) = ?";
				$bindings[] = (int) date('m');
			}

			$rows = DB::select($sql, $bindings);
			$ids = array_map(fn($r) => (string) ($r->id ?? ''), $rows);

			$total = 0.0;
			foreach (array_chunk(array_values(array_filter($ids, fn($v) => is_string($v) && $v !== '')), 200) as $chunk) {
				$purchases = Purchase::query()->whereIn('id', $chunk)->get();
				$total += (float) $purchases->sum(fn(Purchase $p) => $p->getTotal());
			}

			return method_exists($user, 'priceFormat') ? $user->priceFormat($total) : (string) $total;
		} catch (\Throwable $e) {
			Log::warning(static::class . ' getTotalPurchaseAmount failed: ' . $e->getMessage(), [
				'file' => $e->getFile(),
				'line' => $e->getLine(),
				'user_id' => $user->id,
				'month_filter' => $month,
			]);
			return '0';
		}
	}

	/**
	 * Get purchase report chart data for last 10 days
	 * Returns array with labels (dates) and values (daily totals)
	 * 
	 * @return array|RedirectResponse Array with 'label' and 'value' keys or redirect
	 */
	public function getPurchaseReportChart(): array|RedirectResponse
	{
		if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse)
			return $userOrRedirect;

		$user = $userOrRedirect;

		$out = ['label' => [], 'value' => []];

		try {
			$rows = DB::select(
				"select id, created_at from " . DC::TABLE_PURCHASES . " where created_at > ? and " . DC::COL_TABLE_CREATOR . " = ? order by created_at asc",
				[Carbon::now()->subDays(10)->toDateTimeString(), $user->creatorId()]
			);

			$byDay = [];
			foreach ($rows as $r) {
				$k = Carbon::parse((string) ($r->created_at ?? ''))->format('dm');
				$byDay[$k] ??= [];
				$byDay[$k][] = (string) ($r->id ?? '');
			}

			$now = Carbon::now();
			for ($i = 0; $i <= 9; $i++) {
				$date = $now->copy()->subDays($i)->format('Y-m-d');
				$key = Carbon::parse($date)->format('dm');

				$out['label'][] = $date;

				$sum = 0.0;
				$ids = $byDay[$key] ?? [];
				foreach (array_chunk($ids, 200) as $chunk) {
					$sum += (float) Purchase::query()->whereIn('id', $chunk)->get()->sum(fn(Purchase $p) => $p->getTotal());
				}

				$out['value'][] = $sum;
			}

			return $out;
		} catch (\Throwable $e) {
			Log::warning(static::class . ' getPurchaseReportChart failed: ' . $e->getMessage(), [
				'file' => $e->getFile(),
				'line' => $e->getLine(),
				'user_id' => $user->id,
			]);
		}

		return $out;
	}

	/**
	 * Get total purchase amount as numeric value
	 * 
	 * @param bool $month Whether to filter by current month
	 * @return float|RedirectResponse Total amount or redirect if not authenticated
	 */
	public function getTotalPurchaseAmountNumeric(bool $month = false): float|RedirectResponse
	{
		if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse)
			return $userOrRedirect;

		$user = $userOrRedirect;

		try {
			$sql = "select id from " . DC::TABLE_PURCHASES . " where " . DC::COL_TABLE_CREATOR . " = ?";
			$bindings = [$user->creatorId()];

			if ($month) {
				$sql .= " and month(created_at) = ?";
				$bindings[] = (int) date('m');
			}

			$rows = DB::select($sql, $bindings);
			$ids = array_map(fn($r) => (string) ($r->id ?? ''), $rows);

			$total = 0.0;
			foreach (array_chunk(array_values(array_filter($ids, fn($v) => is_string($v) && $v !== '')), 200) as $chunk) {
				$purchases = Purchase::query()->whereIn('id', $chunk)->get();
				$total += (float) $purchases->sum(fn(Purchase $p) => $p->getTotal());
			}

			return $total;
		} catch (\Throwable $e) {
			Log::warning(static::class . ' getTotalPurchaseAmountNumeric failed: ' . $e->getMessage(), [
				'file' => $e->getFile(),
				'line' => $e->getLine(),
				'user_id' => $user->id,
				'month_filter' => $month,
			]);
			return 0.0;
		}
	}

	/**
	 * Get purchase statistics for user
	 * 
	 * @return array|RedirectResponse Statistics array or redirect
	 */
	public function getPurchaseStatistics(): array|RedirectResponse
	{
		if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse)
			return $userOrRedirect;

		$user = $userOrRedirect;

		try {
			$allTimeTotal = $this->getTotalPurchaseAmountNumeric(false);
			$monthTotal = $this->getTotalPurchaseAmountNumeric(true);

			$todayCount = DB::table(DC::TABLE_PURCHASES)
				->where(DC::COL_TABLE_CREATOR, $user->creatorId())
				->whereDate('created_at', Carbon::today())
				->count();

			$monthCount = DB::table(DC::TABLE_PURCHASES)
				->where(DC::COL_TABLE_CREATOR, $user->creatorId())
				->whereRaw('MONTH(created_at) = ?', [date('m')])
				->count();

			return [
				'total_all_time' => $allTimeTotal,
				'total_this_month' => $monthTotal,
				'count_this_month' => $monthCount,
				'count_today' => $todayCount,
			];
		} catch (\Throwable $e) {
			Log::warning(static::class . ' getPurchaseStatistics failed: ' . $e->getMessage(), [
				'file' => $e->getFile(),
				'line' => $e->getLine(),
				'user_id' => $user->id,
			]);
			return [
				'total_all_time' => 0.0,
				'total_this_month' => 0.0,
				'count_this_month' => 0,
				'count_today' => 0,
			];
		}
	}
}
