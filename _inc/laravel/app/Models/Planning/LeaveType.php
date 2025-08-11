<?php

namespace App\Models;

use App\Traits\UsesUuids;
use Illuminate\Database\Eloquent\{Factories\HasFactory, Model};

class LeaveType extends Model
{
    use HasFactory, UsesUuids;

    protected $fillable = [
        'title',
        'days',
        'created_by', // * foreign key to users
    ];
}
