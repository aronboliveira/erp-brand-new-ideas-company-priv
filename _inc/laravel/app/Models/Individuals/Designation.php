<?php

namespace App\Models;

use App\Config\Constants\{
    CompaniesConstants as CPC,
    DatabaseConstants as DC,
    UsersConstants as UC
};
use App\Traits\{HasAuditFields, UsesUuids};
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Designation extends Model
{
    use UsesUuids, HasAuditFields;
    protected $fillable = [
        UC::COL_DSG_NM,
        CPC::COL_DEP_ID,
        CPC::COL_EBDG,
        CPC::COL_VFROM,
        CPC::COL_VTO,
        'description',
        'notes',
    ];
    protected $guarded = ['id', DC::TABLE_CREATOR];
    protected $with = ['department'];
    protected $casts = [
        CPC::COL_EBDG => 'decimal:2',
        CPC::COL_VFROM => 'date',
        CPC::COL_VTO => 'date',
    ];
    protected static function boot(): void
    {
        parent::boot();
        static::creating(function ($model) {
            if (!$model->valid_from)
                $model->valid_from = now()->setTimezone(new \DateTimeZone('America/Sao_Paulo'));
            if (!$model->valid_to)
                $model->valid_to = now()->setTimezone(new \DateTimeZone('America/Sao_Paulo'))->addYears(10);
        });
    }
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, CPC::COL_DEP_ID, 'id');
    }
}
