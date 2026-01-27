<?php

namespace App\Services;

use App\Config\Constants\{
	ActivitiesConstants as AC,
	DatabaseConstants as DC,
	PermissionsConstants as PMC,
	ProjectsConstants as PJC,
	UsersConstants as UC
};
use App\Traits\ChecksLogin;
use App\Models\{Project, ProjectStage, ProjectTask, ProjectUser, Task, TaskStage, User, Utility};
use Illuminate\Database\Eloquent\{Builder, Collection};
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\{Auth, DB, Log};

class ProjectRequestService
{
	use ChecksLogin;

	public function authId(): ?string
	{
		$id = Auth::id();
		return $id !== null ? (string) $id : null;
	}


	/**
	 * Get assigned project tasks for a user
	 * Uses authenticated user if no user provided
	 */
	public function getAssignedProjectTasks(
		?string $projectId = null,
		?string $stageId = null,
		array $filterData = [],
		?User $user = null
	): ?Builder {
		try {
			$project = $projectId ? Project::find($projectId) : null;
			$user = $user ?? auth()->user();
			if (!$user && $project)
				$user = User::where('id', $project[DC::COL_TABLE_CREATOR])->first();
			$ids = $user?->tasks()->pluck('id')->toArray() ?? [];
			$query = ProjectTask::whereIn('id', $ids);
			if ($project)
				$query->where(PJC::COL_PJ_ID, $projectId);
			if ($stageId)
				$query->where(PJC::COL_STAGE_ID, $stageId);
			foreach ($filterData as $col => $val)
				if ($val !== null && $val !== '')
					$query->where($col, $val);
			return $query;
		} catch (\Exception $e) {
			Log::notice('ProjectRequestService: getAssignedProjectTasks failed for project ' . ($projectId ?? 'null'), [
				'method' => __METHOD__,
				'line' => __LINE__,
				'class' => get_class($this),
				'error' => $e->getMessage(),
			]);
			return null;
		}
	}

	/**
	 * Get project status statistics for authenticated user
	 * Returns percentage distribution of projects by status
	 */
	public function getProjectStatus(?User $user = null): array
	{
		try {
			$user = $user ?? auth()->user();
			if (!$user)
				return [];
			$type = $user->type;
			$keys = array_keys(PJC::$projectStatus);
			$counts = [];
			foreach ($keys as $status) {
				$counts[$status] = match ($type) {
					PMC::CPN => Project::where(AC::COL_TSK_STT, $status)
						->where(DC::COL_TABLE_CREATOR, $user->id)
						->count(),
					PMC::CL => Project::where(AC::COL_TSK_STT, $status)
						->where('client_id', $user->id)
						->count(),
					default => ProjectUser::join(
						DC::TABLE_PROJECTS,
						'project_users.' . PJC::COL_PJ_ID,
						'=',
						DC::TABLE_PROJECTS . '.id'
					)
						->where(DC::TABLE_PROJECTS . '.' . AC::COL_TSK_STT, $status)
						->where(UC::COL_USER_ID, $user->id)
						->count()
				};
			}

			$total = array_sum($counts);

			return array_map(
				fn($c) => $total ? round(($c / $total) * 100, 2) : 0,
				$counts
			);
		} catch (\Throwable $e) {
			Log::notice('ProjectRequestService: getProjectStatus failed for user ' . ($user?->id ?? 'null'), [
				'method' => __METHOD__,
				'line' => __LINE__,
				'class' => get_class($this),
				'error' => $e->getMessage(),
			]);
			return [];
		}
	}

	/**
	 * Get the last task stage for a project's creator
	 * Uses authenticated user if no user provided
	 */
	public function getProjectLastStage(?User $user = null): TaskStage|RedirectResponse|null
	{
		try {
			if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse)
				return $userOrRedirect;
			$user = $user ?? auth()->user();
			$isCreatorCallable = $user instanceof User && is_callable([$user, 'creatorId']);
			if (!$isCreatorCallable)
				return null;
			// ? duplication of check only due to compiler issues
			return TaskStage::where(DC::COL_TABLE_CREATOR, $user instanceof User && is_callable([$user, 'creatorId']) ? $user->creatorId() : null)
				->orderBy('order', 'desc')
				->first();
		} catch (\Throwable $e) {
			Log::notice('ProjectRequestService: getProjectLastStage failed for user ' . ($user?->id ?? 'null'), [
				'method' => __METHOD__,
				'line' => __LINE__,
				'class' => get_class($this),
				'error' => $e->getMessage(),
			]);
			return null;
		}
	}

	/**
	 * Count tasks for a project, filtered by user permissions
	 * Returns completed/total format (e.g., "5/10")
	 * Uses authenticated user if no user ID provided
	 */
	public function countTask(Project $project, ?string $userId = null): string|RedirectResponse|null
	{
		try {
			if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse)
				return $userOrRedirect;
			$user = auth()->user();
			if (!$user)
				return null;
			$userId = $userId ?? $user->id;
			$userRole = $user instanceof User && is_callable([$user, 'checkProject']) ? $user->checkProject($project->id) : null;
			$isOwner = is_string($userRole) && strtolower($userRole) === 'owner';
			if ($isOwner) {
				$complete = $project->tasks->where(PJC::COL_IS_CP, 1)->count();
				$total = $project->tasks->count();
			} else {
				$complete = $project->tasks()
					->where(PJC::COL_IS_CP, 1)
					->whereRaw("find_in_set('{$userId}'," . PJC::COL_ASGN . ")")
					->count();

				$total = $project->tasks()
					->whereRaw("find_in_set('{$userId}'," . PJC::COL_ASGN . ")")
					->count();
			}

			return "{$complete}/{$total}";
		} catch (\Throwable $e) {
			Log::notice('ProjectRequestService: countTask failed for project ' . $project->id, [
				'method' => __METHOD__,
				'line' => __LINE__,
				'class' => get_class($this),
				'error' => $e->getMessage(),
			]);
			return null;
		}
	}

	/**
	 * Check if user has access to a project
	 */
	public function userCanAccessProject(Project $project, ?User $user = null): bool
	{
		try {
			$user = $user ?? auth()->user();
			if (!$user)
				return false;
			$isEmployee = Utility::isEmployee($user);
			if ($isEmployee)
				return true;
			if ($project->getAttribute(DC::COL_TABLE_CREATOR) === $user->id)
				return true;
			if ($project->getAttribute(PJC::COL_CLIENT_ID) === $user->id)
				return true;
			return ProjectUser::where(PJC::COL_PJ_ID, $project->id)
				->where(UC::COL_USER_ID, $user->id)
				->exists();
		} catch (\Throwable $e) {
			Log::notice('ProjectRequestService: userCanAccessProject failed for project ' . $project->id, [
				'method' => __METHOD__,
				'line' => __LINE__,
				'class' => get_class($this),
				'error' => $e->getMessage(),
			]);
			return false;
		}
	}

	/**
	 * Check if user can edit a project
	 */
	public function userCanEditProject(Project $project, ?User $user = null): bool
	{
		try {
			$user = $user ?? auth()->user();
			if (!$user)
				return false;
			$isEmployee = Utility::isEmployee($user);
			if ($isEmployee)
				return true;
			return $project->getAttribute(DC::COL_TABLE_CREATOR) === $user->id;
		} catch (\Throwable $e) {
			Log::notice('ProjectRequestService: userCanEditProject failed for project ' . $project->id, [
				'method' => __METHOD__,
				'line' => __LINE__,
				'class' => get_class($this),
				'error' => $e->getMessage(),
			]);
			return false;
		}
	}

	/**
	 * Check if user can delete a project
	 */
	public function userCanDeleteProject(Project $project, ?User $user = null): bool
	{
		try {
			$user = $user ?? auth()->user();
			if (!$user)
				return false;
			$isEmployee = Utility::isEmployee($user);
			if ($isEmployee)
				return true;
			return $project->getAttribute(DC::COL_TABLE_CREATOR) === $user->id;
		} catch (\Throwable $e) {
			Log::notice('ProjectRequestService: userCanDeleteProject failed for project ' . $project->id, [
				'method' => __METHOD__,
				'line' => __LINE__,
				'class' => get_class($this),
				'error' => $e->getMessage(),
			]);
			return false;
		}
	}

	/**
	 * Get all projects accessible by user
	 */
	public function getUserProjects(?User $user = null): Collection
	{
		try {
			$user = $user ?? auth()->user();
			if (!$user)
				return new Collection();
			$type = $user->type;
			return match ($type) {
				PMC::CPN => Project::where(DC::COL_TABLE_CREATOR, $user->id)->get(),
				PMC::CL => Project::where('client_id', $user->id)->get(),
				default => Project::join(
					'project_users',
					DC::TABLE_PROJECTS . '.id',
					'=',
					'project_users.' . PJC::COL_PJ_ID
				)
					->where('project_users.' . UC::COL_USER_ID, $user->id)
					->select(DC::TABLE_PROJECTS . '.*')
					->get()
			};
		} catch (\Throwable $e) {
			Log::notice('ProjectRequestService: getUserProjects failed for user ' . ($user?->id ?? 'null'), [
				'method' => __METHOD__,
				'line' => __LINE__,
				'class' => get_class($this),
				'error' => $e->getMessage(),
			]);
			return new Collection();
		}
	}

	/**
	 * Get projects created by user
	 */
	public function getCreatedProjects(?User $user = null): Collection
	{
		try {
			$user = $user ?? auth()->user();
			if (!$user)
				return new Collection();
			return Project::where(DC::COL_TABLE_CREATOR, $user->id)->get();
		} catch (\Throwable $e) {
			Log::notice('ProjectRequestService: getCreatedProjects failed for user ' . ($user?->id ?? 'null'), [
				'method' => __METHOD__,
				'line' => __LINE__,
				'class' => get_class($this),
				'error' => $e->getMessage(),
			]);
			return new Collection();
		}
	}

	/**
	 * Get projects where user is assigned
	 */
	public function getAssignedProjects(?User $user = null): Collection
	{
		try {
			$user = $user ?? auth()->user();
			if (!$user)
				return new Collection();
			return Project::join(
				'project_users',
				DC::TABLE_PROJECTS . '.id',
				'=',
				'project_users.' . PJC::COL_PJ_ID
			)
				->where('project_users.' . UC::COL_USER_ID, $user->id)
				->select(DC::TABLE_PROJECTS . '.*')
				->get();
		} catch (\Throwable $e) {
			Log::notice('ProjectRequestService: getAssignedProjects failed for user ' . ($user?->id ?? 'null'), [
				'method' => __METHOD__,
				'line' => __LINE__,
				'class' => get_class($this),
				'error' => $e->getMessage(),
			]);
			return new Collection();
		}
	}

	public function shouldRestrictTasksToAssignee(?User $user): bool
	{
		$ut = is_object($user) ? (string) ($user->type ?? '') : '';
		if ($ut === '') return false;
		return $ut !== PMC::CL
			&& $ut !== PMC::CPN;
	}

	public function stageTasksCountForProject(string $stageId, string|int $projectId): int
	{
		if (trim($stageId) === '') return 0;

		try {
			$sql = 'select count(*) as c from ' . DC::TABLE_TASKS .
				' where ' . PJC::COL_STG . ' = ?' .
				' and ' . PJC::COL_PJ_ID . ' = ?';

			$bindings = [$stageId, (string) $projectId];

			$user = Auth::user();
			if ($this->shouldRestrictTasksToAssignee($user)) {
				$sql .= ' and ' . PJC::COL_ASGN . ' = ?';
				$bindings[] = (string) ($user?->id ?? '');
			}

			$row = DB::selectOne($sql, $bindings);
			$c = (int) (is_object($row) ? ($row->c ?? 0) : 0);

			return max(0, $c);
		} catch (\Throwable $e) {
			Log::warning(static::class . ' stageTasksCountForProject failed', [
				'file' => $e->getFile(),
				'line' => $e->getLine(),
				'error' => $e->getMessage(),
				'stage_id' => $stageId,
				'project_id' => (string) $projectId,
			]);
			return 0;
		}
	}


	public function stageTasksForProject(ProjectStage $stage, string|int $projectId): Collection
	{
		$user = Auth::user();
		$stageId = (string) ($stage->getAttribute('id') ?? '');
		$q = Task::query()
			->where(PJC::COL_STG, $stageId)
			->where(PJC::COL_PJ_ID, (string) $projectId);
		if ($this->shouldRestrictTasksToAssignee($user))
			$q->where(PJC::COL_ASGN, (string) ($user?->id ?? ''));
		return $q->orderBy('order')->get();
	}


	public function getStageTasksKey(string $stageId, string|int $projectId): string
	{
		$uid = (string) ($this->authId() ?? '');
		return $stageId . '|' . (string) $projectId . '|' . $uid;
	}

	public function projectStageChartData(): array|RedirectResponse
	{
		$userOrRedirect = self::_checkLogin();
		if ($userOrRedirect instanceof RedirectResponse) {
			return $userOrRedirect;
		}

		if (!($userOrRedirect instanceof User)) {
			return ['label' => [], 'dataset' => []];
		}

		$user = $userOrRedirect;

		try {
			$format = 'Y-m-d';

			$arrDate = [];
			$arrDay = ['label' => []];

			$m  = (int) date('m');
			$de = (int) date('d');
			$y  = (int) date('Y');

			for ($i = 0; $i <= 6; $i++) {
				$date = date($format, mktime(0, 0, 0, $m, $de - $i, $y));
				$arrDate[] = $date;
				$arrDay['label'][] = __(date('D', mktime(0, 0, 0, $m, $de - $i, $y)));
			}

			$creatorId = is_callable([$user, 'creatorId'])
				? (string) $user->creatorId()
				: (string) ($user->id ?? '');

			$stages = ProjectStage::query()
				->where(DC::COL_TABLE_CREATOR, $creatorId)
				->orderBy('order')
				->get();

			$arrTask = [];
			$ut = (string) ($user->type ?? '');

			foreach ($stages as $stage) {
				$stageId = (string) ($stage->getAttribute('id') ?? '');
				$data = [];

				foreach ($arrDate as $d) {
					if ($ut === PMC::CPN) {
						$sql = 'select count(*) as c from ' . DC::TABLE_TASKS .
							' where ' . PJC::COL_STG . ' = ?' .
							' and date(' . DC::COL_U_AT . ') = ?';
						$row = DB::selectOne($sql, [$stageId, $d]);
						$data[] = (int) (is_object($row) ? ($row->c ?? 0) : 0);
						continue;
					}

					if ($ut === PMC::CL) {
						$sql = 'select count(*) as c
                            from ' . DC::TABLE_TASKS . ' t
                            join ' . DC::TABLE_PROJECTS . ' p on t.' . PJC::COL_PJ_ID . ' = p.id
                            where p.' . PMC::CL . ' = ?
                              and t.' . PJC::COL_STG . ' = ?
                              and date(t.' . DC::COL_U_AT . ') = ?';
						$row = DB::selectOne($sql, [(string) ($user->id ?? ''), $stageId, $d]);
						$data[] = (int) (is_object($row) ? ($row->c ?? 0) : 0);
						continue;
					}

					$sql = 'select count(*) as c from ' . DC::TABLE_TASKS .
						' where ' . PJC::COL_ASGN . ' = ?' .
						' and ' . PJC::COL_STG . ' = ?' .
						' and date(' . DC::COL_U_AT . ') = ?';
					$row = DB::selectOne($sql, [(string) ($user->id ?? ''), $stageId, $d]);
					$data[] = (int) (is_object($row) ? ($row->c ?? 0) : 0);
				}

				$arrTask[] = [
					'label'           => (string) ($stage->getAttribute('name') ?? ''),
					'fill'            => '!0',
					'backgroundColor' => 'transparent',
					'borderColor'     => (string) ($stage->getAttribute('color') ?? ProjectStage::DEFAULT_COLOR),
					'data'            => $data,
				];
			}

			$arrTaskData = array_merge($arrDay, ['dataset' => $arrTask]);

			$last = count($arrTaskData['dataset']) - 1;
			if ($last >= 0) {
				unset($arrTaskData['dataset'][$last]['fill']);
				$arrTaskData['dataset'][$last]['backgroundColor'] = '#ccc';
			}

			return $arrTaskData;
		} catch (\Throwable $e) {
			Log::error(static::class . ' projectStageChartData failed', [
				'file' => $e->getFile(),
				'line' => $e->getLine(),
				'error' => $e->getMessage(),
			]);
			return ['label' => [], 'dataset' => []];
		}
	}
}
