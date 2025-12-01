<?php

namespace App\Models;

use App\Config\Constants\{CompaniesConstants as CPC, DatabaseConstants as DC};
use App\Models\Branch;
use App\Traits\{HasAuditFields, UsesUuids};
use Illuminate\Database\Eloquent\{Model, Relations\HasOne};
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Department extends Model
{
    use UsesUuids, HasAuditFields;

    protected $fillable = [
        CPC::COL_BRC_ID,
        CPC::COL_DEP_NM
    ];
    protected $table    = DC::TABLE_DEPARTMENTS;
    protected $guarded  = ['id', DC::COL_TABLE_CREATOR];
    protected $with = ['branch'];
    protected $casts     = [
        'budget'   => 'decimal:2',
        'expenses' => 'decimal:2',
        'profit'   => 'decimal:2',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, CPC::COL_BRC_ID, 'id');
    }
}
