<?php

namespace App\Models;

use App\Config\Constants\{
    ActivitiesConstants,
    DatabaseConstants,
    PermissionsConstants,
    ProjectsConstants,
    UsersConstants
};
use App\Traits\{ChecksLogin, UsesUuids};
use Illuminate\Database\Eloquent\{Factories\HasFactory, Model};
use Illuminate\{Http\RedirectResponse, Support\Collection};

class Stage extends Model
{
    use HasFactory;
    use UsesUuids;
    use ChecksLogin;

    protected $fillable = [
        ProjectsConstants::COL_STG_NM, ProjectsConstants::COL_PPL_ID,
        DatabaseConstants::TABLE_CREATOR, ActivitiesConstants::COL_OD
    ];

    private const CLIENT_TYPE       = PermissionsConstants::CL;
    private const PIVOT_CLIENT_DEALS = 'client_deals';
    private const PIVOT_USER_DEALS  = 'user_deals';
    private const FK_DEAL_ID        = ActivitiesConstants::COL_DL;
    private const FK_CLIENT_ID      = 'client_id';
    private const FK_USER_ID        = 'user_id';
    private const FK_STAGE_ID       = 'stage_id';
    private const DEALS_TABLE       = DatabaseConstants::TABLE_DEALS;
    private const ORDER_COL         = ActivitiesConstants::COL_OD;

    public function deals(): Collection|RedirectResponse
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        ) return $userOrRedirect;
        $user  = $userOrRedirect;
        $pivot = $user[UsersConstants::COL_TP] === self::CLIENT_TYPE
            ? self::PIVOT_CLIENT_DEALS
            : self::PIVOT_USER_DEALS;
        $userKey = $user[UsersConstants::COL_TP] === self::CLIENT_TYPE
            ? self::FK_CLIENT_ID
            : self::FK_USER_ID;

        return Deal::select(self::DEALS_TABLE . '.*')
            ->join(
                $pivot,
                "$pivot." . self::FK_DEAL_ID,
                '=',
                self::DEALS_TABLE . '.id'
            )
            ->where("$pivot.$userKey", $user?->id)
            ->where(
                self::DEALS_TABLE . '.' . self::FK_STAGE_ID,
                $this->id
            )
            ->orderBy(self::DEALS_TABLE . '.' . self::ORDER_COL)
            ->get();
    }
}
