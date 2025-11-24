<?php

namespace App\Models;

use App\Config\Constants\{BillsConstants as BC, DatabaseConstants as DC};
use App\Traits\{HasAuditFields, UsesUuids};
use Illuminate\Database\Eloquent\{Factories\HasFactory, Model};

class ContractType extends Model
{
    use HasFactory, UsesUuids, HasAuditFields;

    protected $table = DC::TABLE_CONTRACT_TYPES;

    protected $fillable = [
        'name',
        'description',
        'category',
        BC::COL_MIN_V,
        BC::COL_MAX_V,
        BC::COL_MIN_M,
        BC::COL_MAX_M,
        BC::COL_TC,
        BC::COL_DEF_TRMC,
        BC::COL_SVR_GRT,
        BC::COL_RNGT,
    ];

    protected $guarded = [
        'id',
        DC::TABLE_CREATOR,
        DC::TABLE_UPDATER,
    ];

    protected $casts = [
        BC::COL_MIN_V    => 'decimal:2',
        BC::COL_MAX_V    => 'decimal:2',
        BC::COL_MIN_M    => 'integer',
        BC::COL_MAX_M    => 'integer',
        BC::COL_DEF_TRMC => 'boolean',
        BC::COL_SVR_GRT  => 'boolean',
        BC::COL_RNGT     => 'boolean',
    ];

    protected static function booted(): void
    {
        parent::booted();
        static::saving(function (self $m): void {
            $m->{BC::COL_MIN_V} ??= 0.00;
            $m->{BC::COL_MAX_V} ??= 9999999999.00;
            if ($m->{BC::COL_MIN_V} !== null && $m->{BC::COL_MIN_V} < 0)
                $m->{BC::COL_MIN_V} = 0.00;
            if ($m->{BC::COL_MAX_V} !== null && $m->{BC::COL_MAX_V} < 0)
                $m->{BC::COL_MAX_V} = 0.00;
            if ($m->{BC::COL_MIN_M} !== null && $m->{BC::COL_MIN_M} < 0)
                $m->{BC::COL_MIN_M} = 0;
            if ($m->{BC::COL_MAX_M} !== null && $m->{BC::COL_MAX_M} < 0)
                $m->{BC::COL_MAX_M} = 0;
            $minV = $m->{BC::COL_MIN_V};
            $maxV = $m->{BC::COL_MAX_V};
            if ($minV !== null && $maxV !== null && $minV > $maxV)
                [$m->{BC::COL_MIN_V}, $m->{BC::COL_MAX_V}] = [$maxV, $minV];
            $minM = $m->{BC::COL_MIN_M};
            $maxM = $m->{BC::COL_MAX_M};
            if ($minM !== null && $maxM !== null && $minM > $maxM)
                [$m->{BC::COL_MIN_M}, $m->{BC::COL_MAX_M}] = [$maxM, $minM];
        });
    }
}
