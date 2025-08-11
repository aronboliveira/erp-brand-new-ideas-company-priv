<?php

namespace App\Models;

use App\Traits\UsesUuids;
use Illuminate\Database\Eloquent\Model;

class EventEmployee extends Model
{
    use UsesUuids;

    private const FILLABLE_FIELDS = [
        'created_by', 'employee_id', 'event_id'
    ];
    protected $fillable = self::FILLABLE_FIELDS;

    // * consider adding: belongsTo(Event::class,'event_id','id')
    // * consider adding: belongsTo(Employee::class,'employee_id','id')
}
