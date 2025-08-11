<?php

namespace App\Models;

use App\Config\Constants\{
    ActivitiesConstants,
    DatabaseConstants,
    ProjectsConstants
};
use App\Traits\{ChecksLogin, UsesUuids};
use Illuminate\Database\Eloquent\{Collection, Model, Factories\HasFactory};

class LeadStage extends Model
{
    use ChecksLogin, HasFactory, UsesUuids;
    protected $fillable = [
        ProjectsConstants::COL_STG_NM,
        ProjectsConstants::COL_PPL_ID,
        DatabaseConstants::TABLE_CREATOR,
        ActivitiesConstants::COL_OD
    ];
    private const USER_TYPE_COMPANY = 'company';
    private const PIVOT_USER_LEADS = 'user_leads';
    private const LEADS_TABLE      = DatabaseConstants::TABLE_LEADS;
    private const ORDER_COLUMN     = ActivitiesConstants::COL_OD;
    public function lead(): Collection
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof \Illuminate\Http\RedirectResponse
        )
            return $userOrRedirect;
        $user = $userOrRedirect;
        return $user->type == self::USER_TYPE_COMPANY
            ? Lead::where(DatabaseConstants::TABLE_CREATOR, $user?->creatorId())
            ->where('stage_id', $this->id)
            ->orderBy(self::ORDER_COLUMN)
            ->get()
            : Lead::join(
                self::PIVOT_USER_LEADS,
                self::PIVOT_USER_LEADS . '.lead_id',
                '=',
                self::LEADS_TABLE . '.id'
            )
            ->where(self::PIVOT_USER_LEADS . '.user_id', $user?->id)
            ->where('stage_id', $this->id)
            ->orderBy(self::LEADS_TABLE . '.' . self::ORDER_COLUMN)
            ->get();
    }
}
