<?php

namespace App\Models;

use App\Config\Constants\{
    CompaniesConstants as CC,
    DatabaseConstants as DC,
    ProjectsConstants as PJC,
    UsersConstants as UC
};
use App\Traits\{
    DefinesDates,
    DescribesCompanyBranch,
    HasAuditFields,
    NormalizesArrays,
    UsesUuids
};
use Carbon\Carbon;
use Illuminate\Database\Eloquent\{
    Model,
    Relations\BelongsTo
};
use Illuminate\Support\Facades\Log;

class Announcement extends Model
{
    use UsesUuids, DescribesCompanyBranch, HasAuditFields, NormalizesArrays, DefinesDates;

    protected $table = DC::TABLE_ANC;

    protected $fillable = [
        'title',
        PJC::COL_S_DT,
        PJC::COL_E_DT,
        CC::COL_BRC_ID,
        CC::COL_DEP_ID,
        UC::COL_EMP_ID,
        'recruiter',
        'description',
        UC::COL_IA,
        UC::COL_IS_RD,
        PJC::COL_PLN_ST,
        'requirements',
        'tags',
        'steps',
    ];

    protected $guarded = [
        'id',
        DC::COL_TABLE_CREATOR,
        DC::COL_TABLE_UPDATER,
    ];

    protected $casts = [
        PJC::COL_S_DT   => 'date',
        PJC::COL_E_DT   => 'date',
        PJC::COL_PLN_ST => 'date',
        UC::COL_IA      => 'boolean',
        UC::COL_IS_RD   => 'boolean',
        'requirements'  => 'array',
        'tags'          => 'array',
        'steps'         => 'array',
    ];

    protected static function booted(): void
    {
        parent::booted();

        static::saving(function (self $m): void {
            foreach (['title', 'description'] as $field)
                if (!empty($m->getAttribute($field)) && is_string($m->getAttribute($field)))
                    $m->setAttribute($field, trim($m->getAttribute($field)));
            foreach (['requirements', 'tags', 'steps'] as $field) {
                try {
                    $m->setAttribute($field, self::normalizeArrayField($m->getAttribute($field) ?? []));
                } catch (\Throwable $e) {
                    Log::warning(self::class . " failed to normalize {$field}", [
                        'announcement_id' => $m->id ?? null,
                        'error'           => $e->getMessage(),
                    ]);
                    $m->setAttribute($field, []);
                }
            }
            if ($m->getAttribute(UC::COL_IA) === null)
                $m->setAttribute(UC::COL_IA, true);
            if ($m->getAttribute(UC::COL_IS_RD) === null)
                $m->setAttribute(UC::COL_IS_RD, true);
            $today = today();
            if (!$m->getAttribute(PJC::COL_S_DT))
                $m->setAttribute(PJC::COL_S_DT, $today);
            else {
                $start = $m->getAttribute(PJC::COL_S_DT) instanceof \DateTimeInterface ? Carbon::instance($m->getAttribute(PJC::COL_S_DT)) : Carbon::parse($m->getAttribute(PJC::COL_S_DT));
                if ($start->lt($today)) $start = $today;
                $m->setAttribute(PJC::COL_S_DT, $start);
            }
            if ($m->getAttribute(PJC::COL_PLN_ST) === null)
                $m->setAttribute(PJC::COL_PLN_ST, (clone $m->getAttribute(PJC::COL_S_DT))->addDays(14));
            else {
                $planned = $m->getAttribute(PJC::COL_PLN_ST) instanceof \DateTimeInterface ? Carbon::instance($m->getAttribute(PJC::COL_PLN_ST)) : Carbon::parse($m->getAttribute(PJC::COL_PLN_ST));
                if ($planned->lt($m->getAttribute(PJC::COL_S_DT))) $planned = (clone $m->getAttribute(PJC::COL_S_DT))->addDays(14);
                $m->setAttribute(PJC::COL_PLN_ST, $planned);
            }
            if ($m->getAttribute(PJC::COL_E_DT)) {
                $end = $m->getAttribute(PJC::COL_E_DT) instanceof \DateTimeInterface ? Carbon::instance($m->getAttribute(PJC::COL_E_DT)) : Carbon::parse($m->getAttribute(PJC::COL_E_DT));
                if ($end->lt($m->getAttribute(PJC::COL_S_DT))) $end = $m->getAttribute(PJC::COL_S_DT);
                $m->setAttribute(PJC::COL_E_DT, $end);
            }
            if ($m->getAttribute(PJC::COL_PLN_ST) === null)
                $m->setAttribute(PJC::COL_PLN_ST, (clone $today)->addDays(14));
        });
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, CC::COL_BRC_ID, 'id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, CC::COL_DEP_ID, 'id');
    }

    public function employee(): BelongsTo
    {
        // quem criou o anúncio
        return $this->belongsTo(Employee::class, UC::COL_EMP_ID, 'id');
    }

    public function recruiter(): BelongsTo
    {
        // responsável pelo recrutamento
        return $this->belongsTo(Employee::class, 'recruiter', 'id');
    }
}
