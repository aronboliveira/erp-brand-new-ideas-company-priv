<?php

namespace App\Models;

use App\Traits\UsesUuids;
use Illuminate\Database\Eloquent\{Model, Relations\HasOne};

class InterviewSchedule extends Model
{
    use UsesUuids;

    protected $fillable = [
        'candidate',
        'employee',
        'date',
        'time',
        'comment',
        'employee_response',
        'created_by',
    ];

    protected $casts = [
        'date' => 'date',
        'time' => 'datetime:H:i:s',
    ];

    public function applications(): HasOne
    {
        return $this->hasOne(\App\Models\JobApplication::class, 'id', 'candidate');
    }

    public function users(): HasOne
    {
        return $this->hasOne(\App\Models\User::class, 'id', 'employee');
    }
}
