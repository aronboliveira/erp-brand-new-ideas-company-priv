<?php

namespace App\Models;

use App\Config\Constants\{
    DatabaseConstants,
    EmailsConstants,
    UsersConstants
};
use App\Traits\UsesUuids;
use Illuminate\Database\Eloquent\{Model, Relations\HasOne};
use Illuminate\Support\Facades\Auth;

class EmailTemplate extends Model
{
    use UsesUuids;

    private const COL_FROM      = EmailsConstants::COL_FROM;
    private const COL_TITLE      = EmailsConstants::COL_TT;
    private const SLUG = EmailsConstants::COL_SLG;
    private static ?self $templateData = null;
    private const COL_CREATED_BY = DatabaseConstants::TABLE_CREATOR;
    private const FILLABLE      = [
        self::COL_TITLE,
        self::COL_FROM,
        self::COL_CREATED_BY,
        self::SLUG
    ];
    protected $fillable = self::FILLABLE;

    public function template(): HasOne
    {
        return $this
            ->hasOne(UserEmailTemplate::class, EmailsConstants::COL_TMP, 'id')
            ->where(UsersConstants::COL_USER_ID, '=', Auth::id());
        // * consider belongsTo(UserEmailTemplate::class,'id',EmailsConstants::COL_TMP)
    }

    public static function emailTemplateData(): ?self
    {
        return self::$templateData ??= self::first();
    }
}
