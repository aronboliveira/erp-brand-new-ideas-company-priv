<?php

namespace App\Services;

use App\Config\Constants\DatabaseConstants as DC;
use App\Models\Warehouse;
use App\Traits\ChecksLogin;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\{DB, Log};

class WarehouseRequestService
{
	use ChecksLogin;

	/**
	 * Get warehouse ID if user has access
	 * Maintained for compatibility
	 * 
	 * @param string $warehouseId
	 * @return string|int|RedirectResponse Warehouse ID string, 0 if not found, or redirect if not authenticated
	 */
	public function warehouseId(string $warehouseId): string|int|RedirectResponse
	{
		try {
			if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse)
				return $userOrRedirect;

			$user = $userOrRedirect;

			$id = DB::table((new Warehouse())->getTable())
				->where('id', $warehouseId)
				->where(DC::COL_TABLE_CREATOR, $user->creatorId())
				->value('id');

			return $id ? (string) $id : '0';
		} catch (\Throwable $e) {
			Log::error(__CLASS__ . '::' . __FUNCTION__ . " failed: {$e->getMessage()}");
			return 0;
		}
	}
}
