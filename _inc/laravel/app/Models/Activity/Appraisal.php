<?php

namespace App\Models;

use App\Traits\UsesUuids;
use Illuminate\Database\Eloquent\{Model, Relations\HasOne};
use Illuminate\Support\Str;

class Appraisal extends Model
{
    use UsesUuids;

    private const FILLABLE_FIELDS = [
        'branch', 'customer_experience', 'created_by', 'administration', 'attendance',
        'appraisal_date', 'employee', 'integrity', 'marketing', 'professionalism',
        'rating', 'remark'
    ];
    protected $fillable = self::FILLABLE_FIELDS;
    private const TECHNICAL_LEVELS = [
        'None', 'Beginner', 'Intermediate', 'Advanced', 'Expert / Leader'
    ];
    public static array $technical = self::TECHNICAL_LEVELS;
    private const ORGANIZATIONAL_LEVELS = [
        'None', 'Beginner', 'Intermediate', 'Advanced'
    ];
    public static array $organizational = self::ORGANIZATIONAL_LEVELS;

    public function branches(): HasOne
    {
        $cls = get_class($this);
        $model = Str::singular(__FUNCTION__);
        return $this->hasOne(substr($cls, 0, strrpos($cls, '\\')) . '\\' . ucfirst($model), 'id', $model);
    }

    public function employees(): HasOne
    {
        $cls = get_class($this);
        $model = Str::singular(__FUNCTION__);
        return $this->hasOne(substr($cls, 0, strrpos($cls, '\\')) . '\\' . ucfirst($model), 'id', $model);
    }
}
