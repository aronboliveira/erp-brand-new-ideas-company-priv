<?php

namespace App\Models;

use App\Config\Constants\{
    ChartsConstants as CHTC,
    DatabaseConstants as DC
};
use App\Traits\{HasAuditFields, UsesUuids};
use Illuminate\Database\Eloquent\{
    Factories\HasFactory,
    Model
};

class ChartOfAccountType extends Model
{
    use HasFactory, UsesUuids, HasAuditFields;

    public const TABLE = DC::TABLE_COA_TYPES;

    protected $table = self::TABLE;

    protected $fillable = [
        CHTC::COL_NM,
        CHTC::COL_CD,
        'category',
        'description',
        'attributes',
        'units',
    ];

    protected $guarded = [
        'id',
        DC::TABLE_CREATOR,
        DC::TABLE_UPDATER,
    ];

    protected $casts = [
        'attributes' => 'array',
        'units'      => 'array',
    ];

    protected $appends = [
        'x_units',
        'y_units',
    ];

    protected static function booted(): void
    {
        parent::booted();

        static::saving(function (self $m): void {
            foreach ([CHTC::COL_NM, CHTC::COL_CD, 'category'] as $field)
                if (isset($m->{$field}) && is_string($m->{$field}))
                    $m->{$field} = trim($m->{$field});

            if ($m->attributes === null)
                $m->attributes = [];
            elseif (!is_array($m->attributes))
                $m->attributes = (array) $m->attributes;

            if ($m->units !== null && !is_array($m->units))
                $m->units = (array) $m->units;

            $m->units = static::normalizeUnitsArray($m->units ?? []);
        });
    }

    protected static function normalizeUnitsArray(array $units): array
    {
        $units['X'] ??= [];
        $units['Y'] ??= [];

        $units['X']['type']   ??= 'timestamp';
        $units['X']['unit']   ??= 'MM';
        $units['X']['values'] = isset($units['X']['values'])
            ? (array) $units['X']['values']
            : [];

        $units['Y']['type']   ??= 'value';
        $units['Y']['unit']   ??= null;
        $units['Y']['values'] = isset($units['Y']['values'])
            ? (array) $units['Y']['values']
            : [];

        return $units;
    }

    public function getXUnitsAttribute(): ?array
    {
        return $this->units['X'] ?? null;
    }

    public function getYUnitsAttribute(): ?array
    {
        return $this->units['Y'] ?? null;
    }
}
