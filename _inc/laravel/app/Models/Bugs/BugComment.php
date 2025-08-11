<?php

namespace App\Models;

use App\Traits\UsesUuids;
use Illuminate\Database\Eloquent\{Model, Relations\HasOne};

class BugComment extends Model
{
    use UsesUuids;

    private const COL_BUG_ID    = 'bug_id';
    private const COL_COMMENT   = 'comment';
    private const COL_CREATED_BY = 'created_by';
    private const COL_USER_TYPE = 'user_type';
    private const FILLABLE      = [
        self::COL_COMMENT,
        self::COL_BUG_ID,
        self::COL_CREATED_BY,
        self::COL_USER_TYPE,
    ];

    protected $fillable = self::FILLABLE;

    public function commentUser(): ?User
    {
        return User::where('id', $this->created_by)->first();
    }

    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'id', self::COL_CREATED_BY);
        // * consider belongsTo(User::class,self::COL_CREATED_BY,'id')
    }
}
