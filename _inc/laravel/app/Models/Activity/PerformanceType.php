<?php

namespace App\Models;

use App\Traits\UsesUuids;
use Illuminate\Database\Eloquent\{Factories\HasFactory, Model, Relations\HasMany};

class PerformanceType extends Model
{
    use HasFactory;
    use UsesUuids;
    protected $fillable = ['name', 'created_by'];
    private const FK_COMPETENCY_TYPE = 'type';
    private const LOCAL_KEY          = 'id';
    private const MODEL_COMPETENCIES = Competencies::class;
    public function types(): HasMany
    {
        return $this->hasMany(
            self::MODEL_COMPETENCIES,
            self::FK_COMPETENCY_TYPE,
            self::LOCAL_KEY
        );
    }
}
