<?php

namespace App\Models;

use App\Config\Constants\{DatabaseConstants as DC, UsersConstants as UC};
use App\Models\Employee;
use App\Traits\{HasAuditFields, UsesUuids};
use Illuminate\Database\Eloquent\{Factories\HasFactory, Model, Relations\HasOne};

class Commission extends Model
{
    use HasFactory, UsesUuids, HasAuditFields;

    public const TABLE = DC::TABLE_CMS;

    protected $table = self::TABLE;

    /** @var array<string,string> */
    public static array $commissionType = [
        'fixed'      => 'Fixed',
        'percentage' => 'Percentage',
    ];

    protected $fillable = [
        UC::COL_EMP_ID,
        'title',
        'amount',
        'type',
    ];

    protected $guarded = [
        'id',
        DC::COL_TABLE_CREATOR,
        DC::COL_TABLE_UPDATER,
    ];

    protected $casts = [
        'amount'       => 'decimal:2',
        UC::COL_EMP_ID => 'string',
    ];

    protected $with = [
        'employee',
    ];

    protected $appends = [
        'is_percentage',
    ];

    public function getIsPercentageAttribute(): bool
    {
        return $this->type === 'percentage';
    }

    public function employee(): HasOne
    {
        return $this->hasOne(
            Employee::class,
            'id',
            UC::COL_EMP_ID
        );
    }
}
