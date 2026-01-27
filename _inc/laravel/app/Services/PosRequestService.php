<?php

namespace App\Services;

use App\Config\Constants\DatabaseConstants as DC;
use App\Models\Pos;
use App\Traits\ChecksLogin;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;

class PosRequestService
{
	use ChecksLogin;

	/**
	 * Get total POS amount for authenticated user
	 * Optionally filter by current month
	 * 
	 * @param bool $month Whether to filter by current month
	 * @return string|RedirectResponse Formatted price or redirect if not authenticated
	 */
	public function getTotalPosAmount(bool $month = false): string|RedirectResponse
	{
		if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse)
			return $userOrRedirect;

		$user = $userOrRedirect;
		$query = Pos::where(DC::COL_TABLE_CREATOR, $user->creatorId());

		if ($month)
			$query->whereRaw('MONTH(created_at) = ?', [date('m')]);

		$total = (float) $query->get()->sum(fn(Pos $p) => $p->getTotal());

		return $user->priceFormat($total);
	}

	/**
	 * Get POS report chart data for last 10 days
	 * Returns array with labels (dates) and values (daily totals)
	 * 
	 * @return array|RedirectResponse Array with 'label' and 'value' keys or redirect
	 */
	public function getPosReportChart(): array|RedirectResponse
	{
		if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse)
			return $userOrRedirect;

		$user = $userOrRedirect;

		$grouped = Pos::whereDate('created_at', '>', Carbon::now()->subDays(10))
			->where(DC::COL_TABLE_CREATOR, $user->creatorId())
			->orderBy('created_at')
			->get()
			->groupBy(fn($v) => Carbon::parse($v->created_at)->format('dm'));

		$posesArray = [
			'label' => [],
			'value' => [],
		];

		$now = Carbon::now();

		for ($i = 0; $i <= 9; $i++) {
			$date = $now->copy()->subDays($i)->format('Y-m-d');
			$key = Carbon::parse($date)->format('dm');

			$posesArray['label'][] = $date;
			$posesArray['value'][] = isset($grouped[$key])
				? (float) $grouped[$key]->sum(fn(Pos $p) => $p->getTotal())
				: 0.0;
		}

		return $posesArray;
	}

	/**
	 * Get total POS amount as numeric value
	 * 
	 * @param bool $month Whether to filter by current month
	 * @return float|RedirectResponse Total amount or redirect if not authenticated
	 */
	public function getTotalPosAmountNumeric(bool $month = false): float|RedirectResponse
	{
		if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse)
			return $userOrRedirect;

		$user = $userOrRedirect;
		$query = Pos::where(DC::COL_TABLE_CREATOR, $user->creatorId());

		if ($month)
			$query->whereRaw('MONTH(created_at) = ?', [date('m')]);

		return (float) $query->get()->sum(fn(Pos $p) => $p->getTotal());
	}

	/**
	 * Get POS transactions for user within date range
	 * 
	 * @param string|null $startDate
	 * @param string|null $endDate
	 * @return \Illuminate\Database\Eloquent\Collection|RedirectResponse
	 */
	public function getPosTransactions(
		?string $startDate = null,
		?string $endDate = null
	): \Illuminate\Database\Eloquent\Collection|RedirectResponse {
		if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse)
			return $userOrRedirect;

		$user = $userOrRedirect;
		$query = Pos::where(DC::COL_TABLE_CREATOR, $user->creatorId());

		if ($startDate)
			$query->whereDate('created_at', '>=', $startDate);

		if ($endDate)
			$query->whereDate('created_at', '<=', $endDate);

		return $query->orderBy('created_at', 'desc')->get();
	}

	/**
	 * Get POS statistics for user
	 * 
	 * @return array|RedirectResponse Statistics array or redirect
	 */
	public function getPosStatistics(): array|RedirectResponse
	{
		if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse)
			return $userOrRedirect;

		$user = $userOrRedirect;

		$allTime = Pos::where(DC::COL_TABLE_CREATOR, $user->creatorId());
		$thisMonth = clone $allTime;
		$thisMonth->whereRaw('MONTH(created_at) = ?', [date('m')]);

		$todayStart = Carbon::today();
		$today = clone $allTime;
		$today->whereDate('created_at', $todayStart);

		return [
			'total_all_time' => (float) $allTime->get()->sum(fn(Pos $p) => $p->getTotal()),
			'total_this_month' => (float) $thisMonth->get()->sum(fn(Pos $p) => $p->getTotal()),
			'total_today' => (float) $today->get()->sum(fn(Pos $p) => $p->getTotal()),
			'count_all_time' => $allTime->count(),
			'count_this_month' => $thisMonth->count(),
			'count_today' => $today->count(),
		];
	}
}
