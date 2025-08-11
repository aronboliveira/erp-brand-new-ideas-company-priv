<?php

namespace App\Models;

use App\Traits\UsesUuids;
use Illuminate\Database\Eloquent\{Factories\HasFactory, Model};

class Event extends Model
{
    use HasFactory, UsesUuids;
    protected $fillable = [
        'branch_id',
        'department_id',
        'employee_id',
        'title',
        'start_date',
        'end_date',
        'color',
        'description',
        'created_by',
    ];
}
