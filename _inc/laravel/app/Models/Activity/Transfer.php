<?php

namespace App\Models;

use App\Config\Constants\{DatabaseConstants as DC, UsersConstants as UC};
use App\Traits\{HasAuditFields, UsesUuids};
use Illuminate\Database\Eloquent\{Factories\HasFactory, Model, Relations\HasOne};
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transfer extends Model
{
    use HasAuditFields, HasFactory, UsesUuids;

    protected const TABLE = DC::TABLE_TRFS;
    protected $fillable = [
        UC::COL_EMP_ID,
        UC::COL_BRC_ID,
        UC::COL_DEP_ID,
        UC::COL_TRF_DT,
        'description',
        'notes',
    ];
    protected $guarded = ['id', DC::TABLE_CREATOR];
    protected $casts = [
        UC::COL_TRF_DT => 'date',
    ];
    protected $with = [
        'employee',
        'branch',
        'department',
    ];

    public function department(): HasOne
    {
        return $this->hasOne(
            Department::class,
            'id',
            UC::COL_DEP_ID
        );
    }

    public function branch(): HasOne
    {
        return $this->hasOne(
            Branch::class,
            'id',
            UC::COL_BRC_ID
        );
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(
            Employee::class,
            UC::COL_EMP_ID,
            'id'
        );
    }
}
