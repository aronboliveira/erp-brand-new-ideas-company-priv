<?php

namespace App\Models;

use App\Traits\UsesUuids;
use Illuminate\Database\Eloquent\Model;

class JobOnBoard extends Model
{
    use UsesUuids;

    private const FILLABLE_FIELDS = [
        'application', 'joining_date', 'status', 'convert_to_employee',
        'job_type', 'days_of_week', 'salary', 'salary_type',
        'salary_duration', 'created_by'
    ];                                                          // ! CHANGED

    protected $fillable = self::FILLABLE_FIELDS;                 // ! CHANGED

    public function applications()
    {
        return $this->hasOne('App\Models\JobApplication', 'id', 'application'); // * consider belongsTo(JobApplication::class,'application')
    }

    public static array $status = [
        '' => 'Select Status', 'pending' => 'Pending',
        'cancel' => 'Cancel', 'confirm' => 'Confirm'
    ];

    public static array $job_type = [
        '' => 'Select Job Type', 'full time' => 'Full Time',
        'part time' => 'Part Time'
    ];

    public static array $salary_duration = [
        '' => 'Select Salary Duration', 'monthly' => 'Monthly',
        'weekly' => 'Weekly'
    ];
}
