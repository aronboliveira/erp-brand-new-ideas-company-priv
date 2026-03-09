<?php

namespace App\Services;

use App\Config\Constants\{
	ActivitiesConstants as AC,
	DatabaseConstants as DC,
	PermissionsConstants as PC,
	ProjectsConstants as PJC
};
use App\Models\TaskStage;
use App\Traits\ChecksLogin;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Carbon;

class TaskRequestService
{
	use ChecksLogin;

	/**
	 * Get chart data for task stages
	 * Returns 7-day data grouped by stage names
	 * 
	 * @return array|RedirectResponse Chart data with labels and datasets, or redirect if not authenticated
	 */
	public function getChartData(): array|RedirectResponse
	{
		if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse)
			return $userOrRedirect;

		$user = $userOrRedirect;
		$today = Carbon::now();
		$period = collect(range(0, 6))->map(fn($i) => $today->copy()->subDays($i));
		$labels = $period->map(fn($d) => __($d->format('D')))->all();
		$dates = $period->map(fn($d) => $d->format('Y-m-d'))->all();

		$base = TaskStage::query();

		try {
			if (method_exists($user, 'creatorId') && $user->creatorId())
				$base->where(DC::COL_TABLE_CREATOR, $user->creatorId());
		} catch (\Throwable) {
		}

		$datasets = [];
		$stageNames = (clone $base)
			->whereNotNull('name')
			->pluck('name')
			->map(fn($v) => trim((string) $v))
			->filter()
			->unique()
			->values()
			->all();

		foreach ($stageNames as $stageName) {
			$data = array_map(function (string $d) use ($user, $stageName) {
				$q = TaskStage::query()
					->where('name', $stageName)
					->whereDate(DC::COL_U_AT, $d);

				try {
					$userType = (string) ($user->type ?? '');
					$userId = (string) ($user->id ?? '');

					if ($userType === PC::CL) {
						return $q->join(
							DC::TABLE_PROJECTS,
							DC::TABLE_TSK_STGS . '.' . AC::COL_PJ,
							'=',
							DC::TABLE_PROJECTS . '.id'
						)->where(DC::TABLE_PROJECTS . '.client_id', $userId)
							->count();
					}

					return $q->where(function ($qq) use ($userId) {
						if ($userId === '')
							return;
						$qq->where('responsible', $userId)
							->orWhereJsonContains('involved', $userId);
					})->count();
				} catch (\Throwable $e) {
					return $q->count();
				}
			}, $dates);

			$datasets[] = [
				PJC::COL_NM => $stageName,
				'backgroundColor' => 'transparent',
				'borderColor' => '#999',
				'data' => $data,
			];
		}

		$last = count($datasets) - 1;
		if ($last >= 0) {
			if (isset($datasets[$last]['fill'])) {
				unset($datasets[$last]['fill']); // @phpstan-ignore-line
			}
			$datasets[$last]['backgroundColor'] = '#ccc';
		}

		return ['label' => $labels, 'dataset' => $datasets];
	}
}
