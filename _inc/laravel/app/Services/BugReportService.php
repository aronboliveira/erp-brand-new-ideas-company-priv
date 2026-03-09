<?php

namespace App\Services;

use App\Config\Constants\{
	ActivitiesConstants as AC,
	PermissionsConstants as PMC,
	ProjectsConstants as PJC,
	UsersConstants as UC
};
use App\Models\{Bug, BugStatus, User};
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class BugReportService
{
	/**
	 * Get bugs for a given status and project, filtered by user permissions
	 * Uses authenticated user if no user provided
	 */
	public function getBugsForStatus(
		string $statusId,
		int|string $projectId,
		?User $user = null
	): Collection {
		$user = $user ?? auth()->user();

		if (!$user) {
			return new Collection();
		}

		$query = Bug::where(AC::COL_TSK_STT, '=', $statusId)
			->where(PJC::COL_PJ_ID, '=', $projectId);

		// Company and Client users can see all bugs
		if ($this->userCanSeeAllBugs($user)) {
			return $query->orderBy(AC::COL_OD)->get();
		}

		// Other users can only see bugs they're assigned to
		return $query->whereRaw(
			"FIND_IN_SET(?, " . PJC::COL_ASGN . ")",
			[$user->id]
		)
			->orderBy(AC::COL_OD)
			->get();
	}

	/**
	 * Get bugs assigned to a specific user for a status and project
	 * Uses authenticated user if no user provided
	 */
	public function getAssignedBugs(
		string $statusId,
		int|string $projectId,
		?User $user = null
	): Collection {
		$user = $user ?? auth()->user();

		if (!$user) {
			return new Collection();
		}

		return Bug::where(AC::COL_TSK_STT, '=', $statusId)
			->where(PJC::COL_PJ_ID, '=', $projectId)
			->whereRaw("FIND_IN_SET(?, " . PJC::COL_ASGN . ")", [$user->id])
			->orderBy(AC::COL_OD)
			->get();
	}

	/**
	 * Get all bugs for a project, respecting user permissions
	 * Uses authenticated user if no user provided
	 */
	public function getBugsForProject(
		int|string $projectId,
		?User $user = null
	): Collection {
		$user = $user ?? auth()->user();

		if (!$user) {
			return new Collection();
		}

		$query = Bug::where(PJC::COL_PJ_ID, '=', $projectId);

		if (!$this->userCanSeeAllBugs($user)) {
			$query->whereRaw(
				"FIND_IN_SET(?, " . PJC::COL_ASGN . ")",
				[$user->id]
			);
		}

		return $query->orderBy(AC::COL_OD)->get();
	}

	/**
	 * Get bugs grouped by status for a project
	 * Uses authenticated user if no user provided
	 */
	public function getBugsGroupedByStatus(
		int|string $projectId,
		?User $user = null
	): array {
		$user = $user ?? auth()->user();

		if (!$user) {
			return [];
		}

		$statuses = BugStatus::orderBy(AC::COL_OD)->get();
		$grouped = [];

		foreach ($statuses as $status) {
			$grouped[$status->id] = [
				'status' => $status,
				'bugs' => $this->getBugsForStatus($status->id, $projectId, $user)
			];
		}

		return $grouped;
	}

	/**
	 * Update bug status with permission check
	 */
	public function updateBugStatus(
		string $bugId,
		string $newStatusId,
		User $user
	): bool {
		$bug = Bug::findOrFail($bugId);

		if (!$this->userCanUpdateBug($bug, $user)) {
			throw new \Illuminate\Auth\Access\AuthorizationException(
				'User does not have permission to update this bug'
			);
		}

		return DB::transaction(function () use ($bug, $newStatusId) {
			$bug->{AC::COL_TSK_STT} = $newStatusId; // @phpstan-ignore-line
			return $bug->save();
		});
	}

	/**
	 * Assign users to a bug
	 */
	public function assignUsersToBug(
		string $bugId,
		array $userIds,
		User $actor
	): bool {
		$bug = Bug::findOrFail($bugId);

		if (!$this->userCanUpdateBug($bug, $actor)) {
			throw new \Illuminate\Auth\Access\AuthorizationException(
				'User does not have permission to assign users to this bug'
			);
		}

		// Validate all user IDs exist
		$validUserIds = User::whereIn('id', $userIds)->pluck('id')->all();

		if (count($validUserIds) !== count($userIds)) {
			throw new \InvalidArgumentException('Some user IDs are invalid');
		}

		return DB::transaction(function () use ($bug, $validUserIds) {
			// Assuming COL_ASGN is a comma-separated string
			$bug->{PJC::COL_ASGN} = implode(',', $validUserIds); // @phpstan-ignore-line
			return $bug->save();
		});
	}

	/**
	 * Check if user can see all bugs in a project
	 */
	protected function userCanSeeAllBugs(User $user): bool
	{
		return in_array($user->{UC::COL_TP}, [
			PMC::CPN,  // Company
			PMC::CL    // Client
		], true);
	}

	/**
	 * Check if user can update a specific bug
	 */
	protected function userCanUpdateBug(Bug $bug, User $user): bool
	{
		// Company and Client users can update all bugs
		if ($this->userCanSeeAllBugs($user)) {
			return true;
		}

		// Other users can only update bugs they're assigned to
		$assignedUsers = array_filter(
			explode(',', (string) $bug->{PJC::COL_ASGN}) // @phpstan-ignore-line
		);

		return in_array($user->id, $assignedUsers, true);
	}

	/**
	 * Get bug statistics for a project
	 * Uses authenticated user if no user provided
	 */
	public function getBugStatistics(
		int|string $projectId,
		?User $user = null
	): array {
		$user = $user ?? auth()->user();

		if (!$user) {
			return [
				'total' => 0,
				'by_status' => [],
			];
		}

		$query = Bug::where(PJC::COL_PJ_ID, '=', $projectId);

		if (!$this->userCanSeeAllBugs($user)) {
			$query->whereRaw(
				"FIND_IN_SET(?, " . PJC::COL_ASGN . ")",
				[$user->id]
			);
		}

		$bugs = $query->get();
		$byStatus = $bugs->groupBy(AC::COL_TSK_STT);

		$stats = [
			'total' => $bugs->count(),
			'by_status' => [],
		];

		$statuses = BugStatus::orderBy(AC::COL_OD)->get();
		foreach ($statuses as $status) {
			$count = $byStatus->get($status->id)?->count() ?? 0;
			$stats['by_status'][$status->id] = [
				'status_title' => $status->{AC::COL_TT}, // @phpstan-ignore-line
				'count' => $count,
				'percentage' => $bugs->count() > 0
					? round(($count / $bugs->count()) * 100, 2)
					: 0,
			];
		}

		return $stats;
	}
}
