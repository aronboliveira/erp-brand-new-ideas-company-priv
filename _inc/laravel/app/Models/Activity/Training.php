<?php

namespace App\Models;

use App\Traits\UsesUuids;
use Illuminate\Database\Eloquent\{Factories\HasFactory, Model, Relations\HasOne};

class Training extends Model
{
    use HasFactory;
    use UsesUuids;

    protected $fillable = [
        'branch', 'trainer_option', 'training_type', 'trainer',
        'training_cost', 'employee', 'start_date', 'end_date',
        'description', 'remarks', 'performance', 'status', 'created_by'
    ];

    public static array $options = ['Internal', 'External'];
    public static array $performance = [
        'Not Concluded', 'Satisfactory', 'Average', 'Poor', 'Excellent'
    ];
    public static array $status = [ // ! CHANGED renamed from $status
        'Pending', 'Started', 'Completed', 'Terminated'
    ];

    public function branches(): HasOne
    {
        return $this->hasOne(Branch::class, 'id', 'branch');
    }

    public function types(): HasOne
    {
        return $this->hasOne(TrainingType::class, 'id', 'training_type');
    }

    public function employees(): HasOne
    {
        return $this->hasOne(Employee::class, 'id', 'employee');
    }

    public function trainers(): HasOne
    {
        return $this->hasOne(Trainer::class, 'id', 'trainer');
    }
}
