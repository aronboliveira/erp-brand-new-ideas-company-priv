<?php

namespace App\Models;

use App\Config\Constants\DatabaseConstants;
use App\Traits\UsesUuids;
use Illuminate\Database\Eloquent\{Model, Relations\HasOne};

class JobApplication extends Model
{
    use UsesUuids;

    private const FILLABLE_FIELDS = [
        'job',
        'name',
        'email',
        'phone',
        'profile',
        'resume',
        'cover_letter',
        'dob',
        'gender',
        'country',
        'state',
        'city',
        'stage',
        'order',
        'skill',
        'rating',
        'is_archive',
        'custom_question',
        DatabaseConstants::COL_TABLE_CREATOR
    ];                                         // ! CHANGED

    protected $fillable = self::FILLABLE_FIELDS; // ! CHANGED

    public function jobs(): HasOne
    {
        return $this->hasOne(Job::class, 'id', 'job'); // * existing relation
    }
}
