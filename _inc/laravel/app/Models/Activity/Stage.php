<?php

namespace App\Models;

use App\Config\Constants\{
    ActivitiesConstants as AC,
    DatabaseConstants as DC,
    PermissionsConstants as PMC,
    ProjectsConstants as PJC,
    UsersConstants as UC
};
use App\Traits\{ChecksLogin, HasAuditFields, UsesUuids};
use Illuminate\Database\Eloquent\{Factories\HasFactory, Model};
use Illuminate\{Http\RedirectResponse, Support\Collection};

class Stage extends Model
{
    use HasFactory, UsesUuids, ChecksLogin, HasAuditFields;

    protected $fillable = [
        PJC::COL_STG_NM,
        PJC::COL_PPL_ID,
        DC::COL_TABLE_CREATOR, // ! REMOVER APÓS TESTES
        AC::COL_OD
    ];
    protected $guarded  = [
        'id',
        // DC::COL_TABLE_CREATOR // ! ATIVAR APÓS TESTES
    ];

    private const CLIENT_TYPE       = PMC::CL;
    private const PIVOT_CLIENT_DEALS = 'client_deals';
    private const PIVOT_USER_DEALS  = 'user_deals';
    private const FK_DEAL_ID        = AC::COL_DL;
    private const FK_CLIENT_ID      = 'client_id';
    private const FK_USER_ID        = 'user_id';
    private const FK_STAGE_ID       = 'stage_id';
    private const DEALS_TABLE       = DC::TABLE_DEALS;
    private const ORDER_COL         = AC::COL_OD;

    public function deals(): Collection|RedirectResponse
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        ) return $userOrRedirect;
        $user  = $userOrRedirect;
        $pivot = $user[UC::COL_TP] === self::CLIENT_TYPE
            ? self::PIVOT_CLIENT_DEALS
            : self::PIVOT_USER_DEALS;
        $userKey = $user[UC::COL_TP] === self::CLIENT_TYPE
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
