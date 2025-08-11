<?php

namespace App\Models;

use App\Traits\{ChecksLogin, UsesUuids};
use Illuminate\Database\Eloquent\{Factories\HasFactory, Model};
use Illuminate\Support\Facades\{DB, Log};

class Warehouse extends Model
{
    use ChecksLogin, HasFactory, UsesUuids;

    private const COL_NAME      = 'name';
    private const COL_ADDRESS   = 'address';
    private const COL_CITY      = 'city';
    private const COL_CITY_ZIP  = 'city_zip';
    private const COL_CREATED_BY = 'created_by';

    protected $fillable = [
        self::COL_NAME,
        self::COL_ADDRESS,
        self::COL_CITY,
        self::COL_CITY_ZIP,
        self::COL_CREATED_BY,
    ];

    public static function warehouseId(string $warehouseName): int
    {
        try {
            if (
                ($userOrRedirect = self::_checkLogin())
                instanceof \Illuminate\Http\RedirectResponse
            )
                return $userOrRedirect;
            $user = $userOrRedirect;
            $warehouse = DB::table((new self)->getTable())
                ->where('id', $warehouseName)
                ->where(self::COL_CREATED_BY, $user?->creatorId())
                ->select('id')
                ->first();
            return $warehouse?->id ?? 0;
        } catch (\Throwable $e) {
            Log::error(__CLASS__ . '::' . __FUNCTION__ . " failed: {$e->getMessage()}");
            return 0;
        }
        // * consider using self::where('id',$warehouseName)
        //     ->where(self::COL_CREATED_BY,Auth::user()->creatorId())
        //     ->value('id');
    }
}
