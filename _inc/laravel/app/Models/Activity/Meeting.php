<?php

namespace App\Models;

use App\Traits\UsesUuids;
use Illuminate\Database\Eloquent\{Factories\HasFactory, Model};

class Meeting extends Model
{
    use HasFactory, UsesUuids;
    protected $fillable = [
        'branch_id',
        'department_id',
        'employee_id',
        'title',
        'date',
        'time',
        'note',
        'created_by'
    ];
}
