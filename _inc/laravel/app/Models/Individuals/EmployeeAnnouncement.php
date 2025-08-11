<?php

namespace App\Models;

use App\Traits\UsesUuids;
use Illuminate\Database\Eloquent\Model;

class EmployeeAnnouncement extends Model
{
    use UsesUuids;

    private const FILLABLE_FIELDS = [           // ! CHANGED
        'announcement_id',
        'employee_id',
        'created_by'
    ];

    protected $fillable = self::FILLABLE_FIELDS; // ! CHANGED
}
