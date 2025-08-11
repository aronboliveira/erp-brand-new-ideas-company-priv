<?php

namespace App\Models;

use App\Traits\UsesUuids;
use Illuminate\Database\Eloquent\{
    Model,
    Relations\BelongsToMany
};

class Asset extends Model
{
    use UsesUuids;

    private const COL_CREATED_BY = 'created_by';
    private const COL_DESCRIPTION = 'description';
    private const COL_NAME       = 'name';
    private const COL_PURCHASE_DATE  = 'purchase_date';
    private const COL_SUPPORTED_DATE = 'supported_date';
    private const COL_AMOUNT     = 'amount';
    private const COL_EMPLOYEE_ID = 'employee_id'; // * ADDED

    private const FILLABLE = [
        self::COL_NAME,
        self::COL_PURCHASE_DATE,
        self::COL_SUPPORTED_DATE,
        self::COL_AMOUNT,
        self::COL_DESCRIPTION,
        self::COL_CREATED_BY,
        self::COL_EMPLOYEE_ID, // ! field type remains text (comma-separated UUIDs)
    ];

    protected $fillable = self::FILLABLE;

    public function employees(): BelongsToMany
    {
        // * maybe refining mapping table?
        return $this->belongsToMany(
            Employee::class,
            'employees',
            '',
            'user_id'
        );
    }

    private static $usersData = null;

    public function users(string $users): array
    {
        if (self::$usersData === null) {
            $ids = explode(',', $users);
            self::$usersData = array_values(array_filter(array_map(
                fn ($uid) => optional(
                    Employee::where('user_id', $uid)->first()
                )->user,
                $ids
            )));
        }
        return self::$usersData;
    }
}
