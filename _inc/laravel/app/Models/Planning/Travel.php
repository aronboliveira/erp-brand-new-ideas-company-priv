<?php

namespace App\Models;

use App\Traits\UsesUuids;
use Illuminate\Database\Eloquent\{Model, Relations\HasOne};

class Travel extends Model
{
    use UsesUuids;

    private const FILLABLE_FIELDS = [
        'employee_id', 'start_date', 'end_date', 'purpose_of_visit',
        'place_of_visit', 'description', 'created_by'
    ];
    protected $fillable = self::FILLABLE_FIELDS;

    public function employee(): HasOne
    {
        return $this->hasOne(Employee::class, 'id', 'employee_id');
        // * consider belongsTo(Employee::class,'employee_id','id')
    }
}
