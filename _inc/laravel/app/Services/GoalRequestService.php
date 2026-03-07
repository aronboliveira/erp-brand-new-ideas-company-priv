<?php

namespace App\Services;

use App\Config\Constants\{BillsConstants as BC, DatabaseConstants as DC};
use App\Enums\GoalType as GoalTypeEnum;
use App\Models\{Bill, Goal, Invoice, Payment, Revenue};
use App\Traits\ChecksLogin;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class GoalRequestService
{
	use ChecksLogin;

	/**
	 * Calculate goal target percentage and total
	 * Computes actual vs target amount for specified date range and goal type
	 * 
	 * @param string $type Goal type (invoice, bill, revenue, payment)
	 * @param string $from Start date
	 * @param string $to End date
	 * @param float $amount Target amount
	 * @return array|RedirectResponse Array with percentage and total, or redirect if not authenticated
	 */
	public function calculateTarget(
		string $type,
		string $from,
		string $to,
		float $amount
	): array|RedirectResponse {
		if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse)
			return $userOrRedirect;

		$user = $userOrRedirect;
		$userId = $user->creatorId();

		try {
			$start = Goal::parseDateTimeOrNull($from)?->startOfDay() ?? Carbon::now()->startOfMonth();
			$end = Goal::parseDateTimeOrNull($to)?->endOfDay() ?? Carbon::now()->endOfMonth();
		} catch (\Throwable $e) {
			Log::debug(static::class . ' invalid date range for target, using current month', [
				'error' => $e->getMessage(),
			]);
			$start = Carbon::now()->startOfMonth();
			$end = Carbon::now()->endOfMonth();
		}

		$enum = GoalTypeEnum::normalize($type);
		$total = 0.0;

		try {
			switch ($enum) {
				case GoalTypeEnum::Invoice:
					if (class_exists(Invoice::class)) {
						$total = Invoice::query()
							->where(DC::COL_TABLE_CREATOR, $userId)
							->whereBetween(BC::COL_ISS_DT, [$start->toDateString(), $end->toDateString()])
							->get()
							->sum(static fn($inv) => (float) $inv->getTotal());
					}
					break;

				case GoalTypeEnum::Bill:
					if (class_exists(Bill::class)) {
						$total = Bill::query()
							->where(DC::COL_TABLE_CREATOR, $userId)
							->whereBetween(BC::COL_BL_DT, [$start->toDateString(), $end->toDateString()])
							->get()
							->sum(static fn($b) => (float) $b->getTotal());
					}
					break;

				case GoalTypeEnum::Revenue:
					if (class_exists(Revenue::class)) {
						$total = (float) Revenue::query()
							->where(DC::COL_TABLE_CREATOR, $userId)
							->whereBetween('date', [$start->toDateString(), $end->toDateString()])
							->sum('amount');
					}
					break;

				case GoalTypeEnum::Payment:
					if (class_exists(Payment::class)) {
						$total = (float) Payment::query()
							->where(DC::COL_TABLE_CREATOR, $userId)
							->whereBetween('date', [$start->toDateString(), $end->toDateString()])
							->sum('amount');
					}
					break;

				default:
					$total = 0.0;
			}
		} catch (\Throwable $e) {
			Log::warning(static::class . ' failed to compute target for goal', [
				'error' => $e->getMessage(),
				'type' => $enum->value,
				'user_id' => $userId,
			]);
		}

		$percentage = $amount > 0.0 ? ($total * 100.0) / $amount : 0.0;

		return [
			'percentage' => $percentage,
			'total' => $total,
		];
	}
}
