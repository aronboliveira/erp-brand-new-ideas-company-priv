<?php

namespace App\Services;

use App\Config\Constants\{
	ActivitiesConstants as AC,
	ProjectsConstants as PJC,
	UsersConstants as UC
};
use App\Models\{ActivityLog, ProjectTask};
use App\Traits\ChecksLogin;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;

class ActivitysAndLogsRequestService
{
	use ChecksLogin;

	/**
	 * Get activity logs for a project task
	 * Filters by authenticated user and task's project
	 * 
	 * @param ProjectTask $task
	 * @return Collection|RedirectResponse Collection of activity logs or redirect if not authenticated
	 */
	public function getActivityLogForTask(ProjectTask $task): Collection|RedirectResponse
	{
		if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse)
			return $userOrRedirect;
		$user = $userOrRedirect;
		return ActivityLog::where(UC::COL_USER_ID, $user->id)
			->where(PJC::COL_PJ_ID, $task[PJC::COL_PJ_ID])
			->where(AC::COL_TSK_ID, $task->id)
			->get();
	}
}
