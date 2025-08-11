<?php

namespace App\Models;

use App\Traits\UsesUuids;
use Illuminate\Database\Eloquent\{Model, Relations\HasOne};

class Award extends Model
{
    use UsesUuids;

    protected $fillable = [
        'employee_id',
        'award_type',
        'date',
        'gift',
        'description',
        'created_by',
    ];

    public function awardType(): HasOne
    {
        $cls = get_class($this);
        return $this->hasOne(substr($cls, 0, strrpos($cls, '\\')) . '\\' . ucfirst(__FUNCTION__), 'id', 'award_type');
    }

    public function employee(): HasOne
    {
        $f = 'id';
        $cls = get_class($this);
        return $this->hasOne(substr($cls, 0, strrpos($cls, '\\')) . '\\' . ucfirst(__FUNCTION__), $f, __FUNCTION__ . '_' . $f);
    }
}
