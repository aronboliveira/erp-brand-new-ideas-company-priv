<?php

namespace App\Models;

use App\Models\PerformanceType;
use App\Traits\UsesUuids;
use Illuminate\Database\Eloquent\Model;

class Competencies extends Model
{
    use UsesUuids;

    private const FILLABLE_FIELDS = [     // ! CHANGED
        'name',
        'type',
        'created_by'
    ];

    protected $fillable = self::FILLABLE_FIELDS; // ! CHANGED

    public function performance()
    {
        return $this->hasOne(
            PerformanceType::class,
            'id',
            'type'
        );
        // * consider belongsTo(PerformanceType::class,'type')
    }
}
