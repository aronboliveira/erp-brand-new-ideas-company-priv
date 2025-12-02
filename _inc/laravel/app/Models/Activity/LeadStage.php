<?php

namespace App\Models;

use App\Config\Constants\{
    ActivitiesConstants as AC,
    DatabaseConstants as DC,
    PermissionsConstants as PMC,
    ProjectsConstants as PJC,
    UsersConstants as UC
};
use App\Traits\{
    ChecksLogin,
    HasAuditFields,
    UsesUuids
};
use Illuminate\Database\Eloquent\{Collection, Factories\HasFactory, Model, Relations\BelongsTo};
use Illuminate\Http\RedirectResponse;

class LeadStage extends Model
{
    use ChecksLogin;
    use HasAuditFields;
    use HasFactory;
    use UsesUuids;

    protected $table = DC::TABLE_LEAD_STAGES;

    protected $fillable = [
        PJC::COL_STG_NM,
        PJC::COL_PPL_ID,
        AC::COL_OD,
        'notes',
        PJC::COL_EST_CC,
        PJC::COL_CRT,
    ];

    protected $guarded = [
        'id',
        DC::COL_TABLE_CREATOR,
    ];

    protected $casts = [
        AC::COL_OD     => 'integer',
        PJC::COL_EST_CC => 'integer',
        PJC::COL_CRT   => 'boolean',
    ];

    protected $with = [
        'pipeline',
    ];

    public function pipeline(): BelongsTo
    {
        return $this->belongsTo(Pipeline::class, PJC::COL_PPL_ID);
    }

    public function leads(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Lead::class, PJC::COL_STG_ID);
    }

    public function lead(): Collection|RedirectResponse
    {
        $userOrRedirect = self::_checkLogin();
        if ($userOrRedirect instanceof RedirectResponse) return $userOrRedirect;

        $user = $userOrRedirect;

        if ($user->type === PMC::CPN)
            return Lead::where(DC::COL_TABLE_CREATOR, $user?->creatorId())
                ->where(PJC::COL_STG_ID, $this->id)
                ->orderBy(AC::COL_OD)
                ->get();

        return Lead::join(
            DC::TABLE_USR_LD,
            DC::TABLE_USR_LD . '.' . PJC::COL_LD_ID,
            '=',
            DC::TABLE_LEADS . '.id'
        )
            ->where(DC::TABLE_USR_LD . '.' . UC::COL_USER_ID, $user?->id)
            ->where(PJC::COL_STG_ID, $this->id)
            ->orderBy(DC::TABLE_LEADS . '.' . AC::COL_OD)
            ->get();
    }

    public function isCritical(): bool
    {
        return (bool) $this->getAttribute(PJC::COL_CRT);
    }

    public function hasNotes(): bool
    {
        return (bool) $this->getAttribute('notes');
    }

    public function estimatedCloseChance(): int
    {
        return (int) $this->getAttribute(PJC::COL_EST_CC);
    }
}
