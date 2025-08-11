<?php

namespace App\Models;

use App\Config\Constants\{EmailsConstants, UsersConstants};
use App\Models\EmailTemplate;
use App\Models\User;
use App\Traits\UsesUuids;
use Illuminate\Database\Eloquent\{Model, Relations\HasOne};

class UserEmailTemplate extends Model
{
    use UsesUuids;

    private const COL_IS_ACTIVE  = EmailsConstants::COL_IA;
    private const COL_TEMPLATE_ID = EmailsConstants::COL_TMP;
    private const COL_USER_ID    = UsersConstants::COL_USER_ID;
    private const FILLABLE       = [
        self::COL_TEMPLATE_ID,
        self::COL_USER_ID,
        self::COL_IS_ACTIVE,
    ];

    protected $fillable = self::FILLABLE;

    public function template(): HasOne
    {
        return $this->hasOne(EmailTemplate::class, 'id', self::COL_TEMPLATE_ID);
        // * consider belongsTo(EmailTemplate::class,self::COL_TEMPLATE_ID,'id')
    }

    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'id', self::COL_USER_ID);
        // * consider belongsTo(User::class,self::COL_USER_ID,'id')
    }
}
