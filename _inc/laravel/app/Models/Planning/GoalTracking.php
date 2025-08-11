<?php

namespace App\Models;

use App\Traits\UsesUuids;
use Illuminate\Database\Eloquent\{Model, Relations\HasOne};

class GoalTracking extends Model
{
    use UsesUuids;

    private const FILLABLE_FIELDS = [
        'branch', 'goal_type', 'start_date', 'end_date', 'subject',
        'target_achievement', 'description', 'created_by', 'rating'
    ];
    protected $fillable = self::FILLABLE_FIELDS;

    private const STATUS_LIST = [
        'Not Started', 'In Progress', 'Completed'
    ];
    public static array $status = self::STATUS_LIST;

    public function goalType(): HasOne
    {
        return $this->hasOne(GoalType::class, 'id', 'goal_type');
        // * consider belongsTo(GoalType::class,'goal_type','id')
    }

    public function branches(): HasOne
    {
        return $this->hasOne(Branch::class, 'id', 'branch');
        // * consider belongsTo(Branch::class,'branch','id')
    }
}
