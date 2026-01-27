<?php

namespace App\Services;

use App\Traits\ChecksLogin;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;

class ContractRequestService
{
	use ChecksLogin;

	/**
	 * Get formatted summary of total contract values
	 * 
	 * @param Collection $contracts
	 * @return string|RedirectResponse Formatted price string or redirect if not authenticated
	 */
	public function getContractSummary(Collection $contracts): string|RedirectResponse
	{
		if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse)
			return $userOrRedirect;

		$user = $userOrRedirect;
		$total = $contracts->sum(fn($c) => $c->value);

		return $user->priceFormat($total);
	}
}
