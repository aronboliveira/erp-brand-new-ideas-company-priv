<?php

namespace App\Models;

use App\Config\Constants\{DatabaseConstants as DC, UsersConstants as UC};
use App\Traits\HasAuditFields;
use App\Traits\UsesUuids;
use Illuminate\Database\Eloquent\{Model, Relations\BelongsTo};

class Award extends Model
{
    use HasAuditFields, UsesUuids;
    protected $table = DC::TABLE_AWD;
    protected $fillable = [
        UC::COL_EMP_ID,
        UC::COL_AWD_TP,
        'date',
        'gift',
        'description',
    ];
    protected $casts = [
        'date' => 'date',
    ];
    protected $guarded = ['id', DC::TABLE_CREATOR];
    protected $with = [
        'awardType',
        'employee',
    ];

    public function awardType(): BelongsTo
    {
        return $this->belongsTo(AwardType::class, UC::COL_AWD_TP, 'id');
    }

    /** O prêmio PERTENCE a um empregado */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, UC::COL_EMP_ID, 'id');
    }

    public function getTypeNameAttribute(): string
    {
        return (string) ($this->awardType?->name ?? '');
    }

    public function scopeForEmployee($query, string $employeeId)
    {
        return $query->where(UC::COL_EMP_ID, $employeeId);
    }

    public function scopeBetweenDates($query, ?string $start, ?string $end)
    {
        if ($start) $query->whereDate('date', '>=', $start);
        if ($end)   $query->whereDate('date', '<=', $end);
        return $query;
    }
}
