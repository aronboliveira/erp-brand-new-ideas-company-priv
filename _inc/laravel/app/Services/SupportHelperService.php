<?php

namespace App\Services;

use App\Config\Constants\{
	MessagesConstants as MC,
	SupportsConstants as SPC,
};
use App\Enums\CaseStatus;
use App\Models\{Support, SupportReply, User, Utility};
use Illuminate\Database\Eloquent\{Builder, Collection};
use Illuminate\Support\Facades\Schema;

class SupportHelperService
{
	/**
	 * Get count of unread replies for a support ticket
	 * Uses authenticated user if no user provided
	 */
	public function getUnreadRepliesCount(
		string $supportId,
		?User $user = null
	): int {
		$user = $user ?? auth()->user();
		if (!$user)
			return 0;
		$isEmployee = Utility::isEmployee($user);
		$query = SupportReply::where(SPC::COL_SPT_ID, $supportId)
			->where(MC::COL_IS_RD, 0);
		return $isEmployee
			? $query->where('user', '!=', $user->id)->count('id')
			: $query->count('id');
	}

	/**
	 * Apply closed-by policy to a support ticket
	 * Uses authenticated user if no user provided
	 */
	public function touchClosedByPolicy(
		Support $support,
		?string $userId = null
	): bool {
		try {
			$table = $support->getTable();

			if (
				!Schema::hasColumn($table, SPC::COL_CLSD_BY) ||
				!Schema::hasColumn($table, SPC::COL_CLSD_AT)
			)
				return false;

			$status = $support->getAttribute(SPC::COL_STT_LB);

			if (!($status instanceof CaseStatus) || !$status->isTerminal())
				return false;

			$uid = trim((string) ($userId ?? auth()->id() ?? ''));

			if ($uid === '')
				return false;

			$support->setAttribute(SPC::COL_CLSD_BY, $uid);

			if (empty($support->getAttribute(SPC::COL_CLSD_AT)))
				$support->setAttribute(SPC::COL_CLSD_AT, now());

			return true;
		} catch (\Throwable $e) {
			\Illuminate\Support\Facades\Log::error(
				static::class . ' touchClosedByPolicy failed',
				[
					'support_id' => $support->getKey(),
					'error' => $e->getMessage(),
					'file' => $e->getFile(),
					'line' => $e->getLine(),
				]
			);

			return false;
		}
	}

	/**
	 * Scope query to only open/active cases
	 */
	public function applyOpenCasesScope(Builder $query): Builder
	{
		$activeCaseValues = array_map(
			fn($case) => $case->value,
			array_filter(
				CaseStatus::cases(),
				fn($case) => $case->isActive()
			)
		);

		return $query->whereIn(SPC::COL_STT_LB, array_values($activeCaseValues));
	}

	/**
	 * Get all open support cases for the authenticated user
	 * Filters by user permissions
	 */
	public function getOpenCases(?User $user = null): Collection
	{
		$user = $user ?? auth()->user();

		if (!$user)
			return new Collection();

		$query = Support::query();
		$this->applyOpenCasesScope($query);

		$isEmployee = Utility::isEmployee($user);

		if (!$isEmployee) {
			$query->where(function ($q) use ($user) {
				$q->where(SPC::COL_USR, $user->id)
					->orWhere(SPC::COL_TKT_CR, $user->id)
					->orWhere(SPC::COL_ASG_TO, $user->id);
			});
		}

		return $query->orderBy('created_at', 'desc')->get();
	}

	/**
	 * Get support cases assigned to a specific user
	 */
	public function getAssignedCases(?User $user = null): Collection
	{
		$user = $user ?? auth()->user();

		if (!$user)
			return new Collection();

		return Support::where(SPC::COL_ASG_TO, $user->id)
			->orderBy('created_at', 'desc')
			->get();
	}

	/**
	 * Get support cases created by a specific user
	 */
	public function getCreatedCases(?User $user = null): Collection
	{
		$user = $user ?? auth()->user();

		if (!$user)
			return new Collection();

		return Support::where(SPC::COL_TKT_CR, $user->id)
			->orderBy('created_at', 'desc')
			->get();
	}

	/**
	 * Check if user can view a support ticket
	 */
	public function userCanView(Support $support, ?User $user = null): bool
	{
		$user = $user ?? auth()->user();

		if (!$user)
			return false;

		$isEmployee = Utility::isEmployee($user);

		if ($isEmployee)
			return true;

		return $support->getAttribute(SPC::COL_TKT_CR) === $user->id
			|| $support->getAttribute(SPC::COL_USR) === $user->id
			|| $support->getAttribute(SPC::COL_ASG_TO) === $user->id;
	}

	/**
	 * Check if user can update a support ticket
	 */
	public function userCanUpdate(Support $support, ?User $user = null): bool
	{
		$user = $user ?? auth()->user();

		if (!$user)
			return false;

		$isEmployee = Utility::isEmployee($user);

		if ($isEmployee)
			return true;

		$status = $support->getAttribute(SPC::COL_STT_LB);
		$isTerminal = $status instanceof CaseStatus && $status->isTerminal();

		return !$isTerminal &&
			$support->getAttribute(SPC::COL_TKT_CR) === $user->id;
	}

	/**
	 * Check if user can close a support ticket
	 */
	public function userCanClose(Support $support, ?User $user = null): bool
	{
		$user = $user ?? auth()->user();

		if (!$user)
			return false;

		$isEmployee = Utility::isEmployee($user);

		return $isEmployee ||
			$support->getAttribute(SPC::COL_TKT_CR) === $user->id;
	}
}
