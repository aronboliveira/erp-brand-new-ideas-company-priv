<?php

namespace App\Models;

use App\Traits\UsesUuids;
use Illuminate\Database\Eloquent\{Model, Relations\HasOne};

class Indicator extends Model
{
    use UsesUuids;

    private const FILLABLE_FIELDS = [
        'attendance', 'administration', 'branch', 'created_by', 'created_user',
        'customer_experience', 'designation', 'integrity', 'marketing',
        'professionalism', 'rating'
    ];
    protected $fillable = self::FILLABLE_FIELDS;

    private const ORGANIZATIONAL_LEVELS = [
        'None', 'Beginner', 'Intermediate', 'Advanced'
    ];
    public static array $organizational = self::ORGANIZATIONAL_LEVELS;

    private const TECHNICAL_LEVELS = [
        'None', 'Beginner', 'Intermediate', 'Advanced', 'Expert / Leader'
    ];
    public static array $technical = self::TECHNICAL_LEVELS;

    public function branches(): HasOne
    {
        return $this->hasOne(Branch::class, 'id', 'branch');
        // * consider belongsTo(Branch::class,'branch','id')
    }

    public function departments(): HasOne
    {
        return $this->hasOne(Department::class, 'id', 'department');
        // * consider belongsTo(Department::class,'department','id')
    }

    public function designations(): HasOne
    {
        return $this->hasOne(Designation::class, 'id', 'designation');
        // * consider belongsTo(Designation::class,'designation','id')
    }

    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'created_user');
        // * consider belongsTo(User::class,'created_user','id')
    }
}
