<?php

namespace App\Services;

use App\Config\Constants\{
	ActivitiesConstants as AC,
	DatabaseConstants as DC,
	PermissionsConstants as PMC,
	ProjectsConstants as PJC,
	UsersConstants as UC
};
use App\Models\{Lead, LeadStage, Utility};
use App\Traits\ChecksLogin;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class LeadRequestService
{
	use ChecksLogin;

	/**
	 * Get leads for a specific stage
	 * Handles authentication and filters by user permissions
	 * 
	 * @param LeadStage $leadStage
	 * @return Collection|RedirectResponse Collection of leads or redirect if not authenticated
	 */
	public function getLeadsForStage(LeadStage $leadStage): Collection|RedirectResponse
	{
		$userOrRedirect = self::_checkLogin();
		if ($userOrRedirect instanceof RedirectResponse)
			return $userOrRedirect;

		$user = $userOrRedirect;

		if (in_array($user->type, [PMC::CPN, PMC::SA], true))
			return Lead::where(DC::COL_TABLE_CREATOR, $user->creatorId())
				->where(PJC::COL_STG_ID, $leadStage->id)
				->orderBy(AC::COL_OD)
				->get();

		return Lead::select(DC::TABLE_LEADS . '.*')
			->join(
				DC::TABLE_USR_LD,
				DC::TABLE_USR_LD . '.' . PJC::COL_LD_ID,
				'=',
				DC::TABLE_LEADS . '.id'
			)
			->where(DC::TABLE_USR_LD . '.' . UC::COL_USER_ID, $user->id)
			->where(DC::TABLE_LEADS . '.' . PJC::COL_STG_ID, $leadStage->id)
			->orderBy(DC::TABLE_LEADS . '.' . AC::COL_OD)
			->get();
	}

	/**
	 * Get all leads accessible by user
	 * 
	 * @return Collection|RedirectResponse
	 */
	public function getUserLeads(): Collection|RedirectResponse
	{
		$userOrRedirect = self::_checkLogin();
		if ($userOrRedirect instanceof RedirectResponse)
			return $userOrRedirect;

		$user = $userOrRedirect;

		if (in_array($user->type, [PMC::CPN, PMC::SA], true))
			return Lead::where(DC::COL_TABLE_CREATOR, $user->creatorId())
				->orderBy(AC::COL_OD)
				->get();

		return Lead::join(
			DC::TABLE_USR_LD,
			DC::TABLE_USR_LD . '.' . PJC::COL_LD_ID,
			'=',
			DC::TABLE_LEADS . '.id'
		)
			->where(DC::TABLE_USR_LD . '.' . UC::COL_USER_ID, $user->id)
			->orderBy(DC::TABLE_LEADS . '.' . AC::COL_OD)
			->get();
	}

	/**
	 * Check if user can access a specific lead
	 * 
	 * @param Lead $lead
	 * @return bool|RedirectResponse
	 */
	public function userCanAccessLead(Lead $lead): bool|RedirectResponse
	{
		$userOrRedirect = self::_checkLogin();
		if ($userOrRedirect instanceof RedirectResponse)
			return $userOrRedirect;

		$user = $userOrRedirect;

		if (Utility::isEmployee($user))
			return true;

		if ($user->type === PMC::CPN)
			return $lead->getAttribute(DC::COL_TABLE_CREATOR) === $user->creatorId();

		return DB::table(DC::TABLE_USR_LD)
			->where(PJC::COL_LD_ID, $lead->id)
			->where(UC::COL_USER_ID, $user->id)
			->exists();
	}

	/**
	 * Check if user can edit a specific lead
	 * 
	 * @param Lead $lead
	 * @return bool|RedirectResponse
	 */
	public function userCanEditLead(Lead $lead): bool|RedirectResponse
	{
		$userOrRedirect = self::_checkLogin();
		if ($userOrRedirect instanceof RedirectResponse)
			return $userOrRedirect;

		$user = $userOrRedirect;

		if (Utility::isEmployee($user))
			return true;

		if ($user->type === PMC::CPN)
			return $lead->getAttribute(DC::COL_TABLE_CREATOR) === $user->creatorId();

		return false;
	}

	/**
	 * Get lead count by stage for user
	 * 
	 * @return array|RedirectResponse
	 */
	public function getLeadCountByStage(): array|RedirectResponse
	{
		$userOrRedirect = self::_checkLogin();
		if ($userOrRedirect instanceof RedirectResponse)
			return $userOrRedirect;

		$user = $userOrRedirect;

		$query = $user->type === PMC::CPN
			? Lead::where(DC::COL_TABLE_CREATOR, $user->creatorId())
			: Lead::join(
				DC::TABLE_USR_LD,
				DC::TABLE_USR_LD . '.' . PJC::COL_LD_ID,
				'=',
				DC::TABLE_LEADS . '.id'
			)
			->where(DC::TABLE_USR_LD . '.' . UC::COL_USER_ID, $user->id);

		return $query->select(PJC::COL_STG_ID, DB::raw('count(*) as total'))
			->groupBy(PJC::COL_STG_ID)
			->pluck('total', PJC::COL_STG_ID)
			->toArray();
	}
}
