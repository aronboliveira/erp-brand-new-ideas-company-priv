<?php

namespace App\Models;

use App\Traits\UsesUuids;
use Illuminate\Database\Eloquent\{Model, Relations\HasOne};

class Trainer extends Model
{
    use UsesUuids;

    private const COL_ADDRESS   = 'address';
    private const COL_BRANCH    = 'branch';
    private const COL_CONTACT   = 'contact';
    private const COL_CREATED_BY = 'created_by';
    private const COL_EMAIL     = 'email';
    private const COL_EXPERTISE = 'expertise';
    private const COL_FIRSTNAME = 'firstname';
    private const COL_LASTNAME  = 'lastname';
    private const FILLABLE      = [
        self::COL_BRANCH,
        self::COL_FIRSTNAME,
        self::COL_LASTNAME,
        self::COL_CONTACT,
        self::COL_EMAIL,
        self::COL_ADDRESS,
        self::COL_EXPERTISE,
        self::COL_CREATED_BY,
    ];

    protected $fillable = self::FILLABLE;

    public function branches(): HasOne
    {
        return $this->hasOne(Branch::class, 'id', self::COL_BRANCH);
        // * consider belongsTo(Branch::class,self::COL_BRANCH,'id')
    }
}
