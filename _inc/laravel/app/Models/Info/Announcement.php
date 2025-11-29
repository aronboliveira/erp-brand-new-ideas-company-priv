<?php

namespace App\Models;

use App\Config\Constants\{
    CompaniesConstants as CC,
    DatabaseConstants as DC,
    ProjectsConstants as PJC,
    UsersConstants as UC
};
use App\Traits\{
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
    use UsesUuids, HasAuditFields, NormalizesArrays;

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
        DC::TABLE_CREATOR,
        DC::TABLE_UPDATER,
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
                if (isset($m->{$field}) && is_string($m->{$field}))
                    $m->{$field} = trim($m->{$field});

            foreach (['requirements', 'tags', 'steps'] as $field) {
                try {
                    $m->{$field} = self::normalizeArrayField($m->{$field} ?? []);
                } catch (\Throwable $e) {
                    Log::warning(self::class . " failed to normalize {$field}", [
                        'announcement_id' => $m->id ?? null,
                        'error'           => $e->getMessage(),
                    ]);
                    $m->{$field} = [];
                }
            }

            if ($m->{UC::COL_IA} === null)
                $m->{UC::COL_IA} = true;

            if ($m->{UC::COL_IS_RD} === null)
                $m->{UC::COL_IS_RD} = true;

            $today = today();

            if (!$m->{PJC::COL_S_DT})
                $m->{PJC::COL_S_DT} = $today;
            else {
                $start = $m->{PJC::COL_S_DT} instanceof \DateTimeInterface ? Carbon::instance($m->{PJC::COL_S_DT}) : Carbon::parse($m->{PJC::COL_S_DT});
                if ($start->lt($today)) $start = $today;
                $m->{PJC::COL_S_DT} = $start;
            }

            if ($m->{PJC::COL_PLN_ST} === null)
                $m->{PJC::COL_PLN_ST} = (clone $m->{PJC::COL_S_DT})->addDays(14);
            else {
                $planned = $m->{PJC::COL_PLN_ST} instanceof \DateTimeInterface ? Carbon::instance($m->{PJC::COL_PLN_ST}) : Carbon::parse($m->{PJC::COL_PLN_ST});
                if ($planned->lt($m->{PJC::COL_S_DT})) $planned = (clone $m->{PJC::COL_S_DT})->addDays(14);
                $m->{PJC::COL_PLN_ST} = $planned;
            }

            if ($m->{PJC::COL_E_DT}) {
                $end = $m->{PJC::COL_E_DT} instanceof \DateTimeInterface ? Carbon::instance($m->{PJC::COL_E_DT}) : Carbon::parse($m->{PJC::COL_E_DT});
                if ($end->lt($m->{PJC::COL_S_DT})) $end = $m->{PJC::COL_S_DT};
                $m->{PJC::COL_E_DT} = $end;
            }

            if ($m->{PJC::COL_PLN_ST} === null)
                $m->{PJC::COL_PLN_ST} = (clone $today)->addDays(14);
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
