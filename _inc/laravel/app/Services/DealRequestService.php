<?php

namespace App\Services;

use App\Traits\ChecksLogin;
use App\Config\Constants\{
	ActivitiesConstants as AC,
	DatabaseConstants as DC,
	ProjectsConstants as PJC,
	UsersConstants as UC
};
use App\Enums\UserType;
use App\Models\{Deal, Stage};
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;

class DealRequestService
{
	use ChecksLogin;

	/**
	 * Get deal summary - formatted price or numeric value
	 * Handles authentication internally via ChecksLogin trait
	 * 
	 * @param array|Collection $deals
	 * @param bool $numeric Whether to return numeric value instead of formatted string
	 * @return string|array|RedirectResponse Formatted price, numeric array, or redirect if not authenticated
	 */
	public function getDealSummary(
		array|Collection $deals,
		bool $numeric = false
	): string|array|RedirectResponse {
		if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse)
			return $userOrRedirect;

		$user = $userOrRedirect;

		$dealsArray = is_array($deals)
			? $deals
			: ($deals instanceof Collection ? $deals->toArray() : []);

		$total = collect($dealsArray)->sum(fn($d) => $d->price ?? 0);

		return $user->priceFormat($total, $numeric);
	}

	/**
	 * Get total deal value for authenticated user
	 * 
	 * @param array|Collection $deals
	 * @return float|RedirectResponse Total deal value or redirect if not authenticated
	 */
	public function getTotalDealValue(
		array|Collection $deals
	): float|RedirectResponse {
		if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse)
			return $userOrRedirect;

		$user = $userOrRedirect;

		$dealsArray = is_array($deals)
			? $deals
			: ($deals instanceof Collection ? $deals->toArray() : []);

		return (float) collect($dealsArray)->sum(fn($d) => $d->price ?? 0);
	}

	/**
	 * Get formatted deal summary with currency
	 * 
	 * @param array|Collection $deals
	 * @return string|RedirectResponse Formatted price with currency or redirect if not authenticated
	 */
	public function getFormattedDealSummary(
		array|Collection $deals
	): string|RedirectResponse {
		return $this->getDealSummary($deals, false);
	}

	/**
	 * Get numeric deal summary
	 * 
	 * @param array|Collection $deals
	 * @return array|RedirectResponse Numeric price data or redirect if not authenticated
	 */
	public function getNumericDealSummary(
		array|Collection $deals
	): array|RedirectResponse {
		return $this->getDealSummary($deals, true);
	}

	/**
	 * Get deals for a stage filtered by authenticated user
	 * Returns deals based on user type (client or regular user)
	 * 
	 * @param Stage $stage
	 * @return Collection|RedirectResponse Collection of deals or redirect if not authenticated
	 */
	public function getDealsForStage(Stage $stage): Collection|RedirectResponse
	{
		if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse)
			return $userOrRedirect;

		$user = $userOrRedirect;

		$pivot = $user[UC::COL_TP] === UserType::Client->value
			? DC::TABLE_CLT_DLS
			: DC::TABLE_USR_DLS;

		$userKey = $user[UC::COL_TP] === UserType::Client->value
			? PJC::COL_CLIENT_ID
			: UC::COL_USER_ID;

		return Deal::select(DC::TABLE_DEALS . '.*')
			->join(
				$pivot,
				"$pivot." . PJC::COL_DL_ID,
				'=',
				DC::TABLE_DEALS . '.id'
			)
			->where("$pivot.$userKey", $user->id)
			->where(
				DC::TABLE_DEALS . '.' . PJC::COL_STG_ID,
				$stage->id
			)
			->orderBy(DC::TABLE_DEALS . '.' . AC::COL_OD)
			->get();
	}
}
