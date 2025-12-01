<?php

namespace App\Models;

use App\Config\Constants\{DatabaseConstants as DC, ProjectsConstants as PJC, UsersConstants as UC};
use App\Traits\{HasAuditFields, UsesUuids};
use Illuminate\Database\Eloquent\{Model, Relations\BelongsTo};

class Travel extends Model
{
    use HasAuditFields, UsesUuids;

    protected $table = DC::TABLE_TRAVELS;
    protected $fillable = [
        UC::COL_EMP_ID,
        PJC::COL_S_DT,
        PJC::COL_E_DT,
        PJC::VST_PPS,
        PJC::VST_PLC,
        'description',
    ];
    protected $guarded = ['id', DC::COL_TABLE_CREATOR];
    protected $casts = [
        PJC::COL_S_DT => 'date',
        PJC::COL_E_DT => 'date',
    ];
    protected $with = ['employee'];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, UC::COL_EMP_ID, 'id');
    }
}
