<?php

namespace App\Models;

use App\Models\Employee;
use App\Traits\UsesUuids;
use Illuminate\Database\Eloquent\{Model, Relations\HasOne};

class Complaint extends Model
{
    use UsesUuids;

    private const FILLABLE_FIELDS = [
        'complaint_against', 'complaint_date', 'complaint_from',
        'created_by', 'description', 'employee_id', 'title'
    ];
    protected $fillable = self::FILLABLE_FIELDS;

    public function complaintAgainst(): HasOne
    {
        return $this->hasOne(Employee::class, 'id', 'complaint_against');
    }

    public function complaintFrom(): HasOne
    {
        return $this->hasOne(Employee::class, 'id', 'complaint_from');
    }

    public function employee(): HasOne
    {
        return $this->hasOne(Employee::class, 'id', 'employee_id');
    }
}
