<?php

namespace App\Services;

use App\Config\Constants\{DatabaseConstants as DC, ProjectsConstants as PJC};
use App\Models\{LeadStage, Pipeline, Stage};
use App\Traits\ChecksLogin;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;

class PipelineRequestService
{
	use ChecksLogin;

	/**
	 * Get stages for a pipeline
	 * Filters by authenticated user's ownership
	 * 
	 * @param Pipeline $pipeline
	 * @return Collection|RedirectResponse Collection of stages or redirect if not authenticated
	 */
	public function getStagesForPipeline(Pipeline $pipeline): Collection|RedirectResponse
	{
		if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse)
			return $userOrRedirect;

		$user = $userOrRedirect;

		return Stage::where(PJC::COL_PPL_ID, $pipeline->id)
			->where(DC::COL_TABLE_CREATOR, $user->ownerId())
			->orderBy('order')
			->get();
	}

	/**
	 * Get lead stages for a pipeline
	 * Filters by authenticated user's ownership
	 * 
	 * @param Pipeline $pipeline
	 * @return Collection|RedirectResponse Collection of lead stages or redirect if not authenticated
	 */
	public function getLeadStagesForPipeline(Pipeline $pipeline): Collection|RedirectResponse
	{
		if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse)
			return $userOrRedirect;

		$user = $userOrRedirect;

		return LeadStage::where(PJC::COL_PPL_ID, $pipeline->id)
			->where(DC::COL_TABLE_CREATOR, $user->ownerId())
			->orderBy('order')
			->get();
	}

	/**
	 * Get all pipelines accessible by user
	 * 
	 * @return Collection|RedirectResponse
	 */
	public function getUserPipelines(): Collection|RedirectResponse
	{
		if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse)
			return $userOrRedirect;

		$user = $userOrRedirect;

		return Pipeline::where(DC::COL_TABLE_CREATOR, $user->ownerId())
			->orderBy('order')
			->get();
	}

	/**
	 * Check if user can access a pipeline
	 * 
	 * @param Pipeline $pipeline
	 * @return bool|RedirectResponse
	 */
	public function userCanAccessPipeline(Pipeline $pipeline): bool|RedirectResponse
	{
		if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse)
			return $userOrRedirect;

		$user = $userOrRedirect;

		return $pipeline->getAttribute(DC::COL_TABLE_CREATOR) === $user->ownerId();
	}

	/**
	 * Get stage count for a pipeline
	 * 
	 * @param Pipeline $pipeline
	 * @return int|RedirectResponse
	 */
	public function getStageCount(Pipeline $pipeline): int|RedirectResponse
	{
		if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse)
			return $userOrRedirect;

		$user = $userOrRedirect;

		return Stage::where(PJC::COL_PPL_ID, $pipeline->id)
			->where(DC::COL_TABLE_CREATOR, $user->ownerId())
			->count();
	}

	/**
	 * Get lead stage count for a pipeline
	 * 
	 * @param Pipeline $pipeline
	 * @return int|RedirectResponse
	 */
	public function getLeadStageCount(Pipeline $pipeline): int|RedirectResponse
	{
		if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse)
			return $userOrRedirect;

		$user = $userOrRedirect;

		return LeadStage::where(PJC::COL_PPL_ID, $pipeline->id)
			->where(DC::COL_TABLE_CREATOR, $user->ownerId())
			->count();
	}
}
