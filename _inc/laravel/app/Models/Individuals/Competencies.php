<?php

namespace App\Models;

use App\Models\PerformanceType;
use App\Traits\UsesUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
/**
 * @property mixed  $created_by
 * @property string $code
 */

class Competencies extends Model
{
    use UsesUuids;

    private const FILLABLE_FIELDS = [
        'name',
        'type',
        'code',
        'created_by',
    ];

    protected $fillable = self::FILLABLE_FIELDS;

    // Auto-generate code as CMPT-{random} if not provided, matching the
    // migration comment: "automatically generated as CMPT-{UUID}".
    protected static function booted(): void
    {
        static::creating(function (self $model): void {
            if (empty($model->code)) {
                $attempts = 0;
                do {
                    $model->code = 'CMPT-' . strtoupper(Str::random(8));
                    $attempts++;
                } while (static::where('code', $model->code)->exists() && $attempts < 20);
            }
        });
    }

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
