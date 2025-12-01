<?php

namespace App\Models;

use App\Config\Constants\{
    ActivitiesConstants,
    DatabaseConstants,
    ProjectsConstants,
    MessagesConstants,
    SupportsConstants,
    UsersConstants,
};
use App\Traits\{ChecksLogin, UsesUuids};
use Illuminate\Database\Eloquent\{Factories\HasFactory, Model, Relations\HasOne};
use Illuminate\Support\Facades\Auth;

class Support extends Model
{
    use ChecksLogin, HasFactory, UsesUuids;

    protected $fillable = [
        SupportsConstants::COL_SBJ,
        SupportsConstants::COL_USR,
        ProjectsConstants::COL_PRT,
        ProjectsConstants::COL_E_DT,
        SupportsConstants::COL_TKT_CD,
        SupportsConstants::COL_TKT_CR,
        ActivitiesConstants::COL_TSK_STT,
        DatabaseConstants::COL_TABLE_CREATOR,
        SupportsConstants::COL_ATC,
        ActivitiesConstants::COL_DESC
    ];

    public static array $priority = ['Low', 'Medium', 'High', 'Critical'];

    public static array $status = [
        'Open' => 'Open',
        'Close' => 'Close',
        'On Hold' => 'On Hold'
    ];

    public static function status(): array
    {
        return [
            'Open'    => __('Open'),
            'Close'   => __('Close'),
            'On Hold' => __('On Hold')
        ];
    }

    public function createdBy(): HasOne
    {
        return $this->hasOne(User::class, 'id', SupportsConstants::COL_TKT_CR);
    }

    public function assignUser(): HasOne
    {
        return $this->hasOne(User::class, 'id', SupportsConstants::COL_USR);
    }

    public function replyUnread(): int
    {
        $user = Auth::user();
        return strtolower($user[UsersConstants::COL_TP]) === 'employee'
            ? SupportReply::where(SupportsConstants::COL_SPT_ID, $this->id)
            ->where(MessagesConstants::COL_IS_RD, 0)
            ->where('user', '!=', $user?->id)
            ->count('id')
            : SupportReply::where(SupportsConstants::COL_SPT_ID, $this->id)
            ->where(MessagesConstants::COL_IS_RD, 0)
            ->count('id');
    }

    public function priorityList(): array
    {
        return self::$priority;
    }

    public function statusList(): array
    {
        return self::$status;
    }
}
