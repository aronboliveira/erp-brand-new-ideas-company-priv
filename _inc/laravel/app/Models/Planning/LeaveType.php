<?php

namespace App\Models;

use App\Config\Constants\{
    DatabaseConstants as DC,
    ProjectsConstants as PJC
};
use App\Traits\{
    HasAuditFields,
    NormalizesArrays,
    UsesUuids
};
use Illuminate\Database\Eloquent\{
    Factories\HasFactory,
    Model,
    Relations\HasOne
};
use Illuminate\Support\Facades\Log;

class LeaveType extends Model
{
    use HasFactory;
    use UsesUuids;
    use HasAuditFields;
    use NormalizesArrays;

    public const TABLE = DC::TABLE_LEAVE_TYPES;

    protected $table = self::TABLE;

    protected $fillable = [
        'title',
        'days',
        PJC::COL_EXT_DY,
        'paid',
        PJC::COL_HLT_RL,
        PJC::COL_SL_MIN_DD_PCT,
        PJC::COL_SL_MAX_DD_PCT,
        'description',
        'categories',
        'conditions',
        'attachments',
    ];

    protected $guarded = [
        'id',
        DC::COL_TABLE_CREATOR,
        DC::COL_TABLE_UPDATER,
    ];

    protected $casts = [
        'days'                          => 'integer',
        PJC::COL_EXT_DY                 => 'integer',
        'paid'                          => 'boolean',
        PJC::COL_HLT_RL                 => 'boolean',
        PJC::COL_SL_MIN_DD_PCT          => 'integer',
        PJC::COL_SL_MAX_DD_PCT          => 'integer',
        'categories'                    => 'array',
        'conditions'                    => 'array',
        'attachments'                   => 'array',
    ];

    protected static function booted(): void
    {
        parent::booted();

        static::saving(function (self $m): void {
            if (isset($m->title) && is_string($m->title))
                $m->title = trim($m->title);

            if (isset($m->description) && is_string($m->description))
                $m->description = trim($m->description);

            $m->days = (int) ($m->days ?? 0);
            if ($m->days < 0)
                $m->days = 0;

            $ext = (int) ($m->{PJC::COL_EXT_DY} ?? 0);
            if ($ext < 0)
                $ext = 0;
            $m->{PJC::COL_EXT_DY} = $ext;

            $minPct = (int) ($m->{PJC::COL_SL_MIN_DD_PCT} ?? 0);
            $maxPct = (int) ($m->{PJC::COL_SL_MAX_DD_PCT} ?? 0);

            foreach (['minPct', 'maxPct'] as $var) {
                if (${$var} < 0)
                    ${$var} = 0;
                if (${$var} > 100)
                    ${$var} = 100;
            }

            if ($minPct > $maxPct)
                $minPct = $maxPct;

            $m->{PJC::COL_SL_MIN_DD_PCT} = $minPct;
            $m->{PJC::COL_SL_MAX_DD_PCT} = $maxPct;

            try {
                $m->categories  = self::normalizeArrayField($m->categories ?? null);
                $m->conditions  = self::normalizeArrayField($m->conditions ?? null);
                $m->attachments = self::normalizeArrayField($m->attachments ?? null);
            } catch (\Throwable $e) {
                Log::warning(self::class . ' failed to normalize JSON fields for LeaveType', [
                    'id'    => $m->id ?? null,
                    'error' => $e->getMessage(),
                ]);
                $m->categories  = [];
                $m->conditions  = [];
                $m->attachments = [];
            }

            if ($m->{PJC::COL_HLT_RL} === null)
                $m->{PJC::COL_HLT_RL} = true;

            if ($m->paid === null)
                $m->paid = false;
        });
    }

    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'id', DC::COL_TABLE_CREATOR);
        // * considerar belongsTo(User::class, DC::COL_TABLE_CREATOR, 'id')
    }
}
