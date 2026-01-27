<?php

namespace App\Services;

use App\Models\Estimation;
use App\Traits\ChecksLogin;
use Illuminate\Http\RedirectResponse;

class BusinessRequestService
{
	use ChecksLogin;

	/**
	 * Get formatted summary of total estimation values
	 * 
	 * @param iterable $estimates
	 * @return string|RedirectResponse Formatted price string or redirect if not authenticated
	 */
	public function getEstimationSummary(iterable $estimates): string|RedirectResponse
	{
		if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse)
			return $userOrRedirect;

		$user = $userOrRedirect;
		$total = collect($estimates)->sum(fn(Estimation $e): float => $e->getTotal());

		return $user->priceFormat($total);
	}
}
