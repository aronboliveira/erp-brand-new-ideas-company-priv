<?php

namespace App\Models;

use App\Config\Constants\{
    DatabaseConstants as DC,
    ProjectsConstants as PJC
};
use App\Traits\{
    FiltersSecureAttachments,
    HasAuditFields,
    NormalizesArrays,
    UsesUuids
};
use Illuminate\Database\Eloquent\{
    Factories\HasFactory,
    Model,
    Relations\BelongsTo
};
use Illuminate\Support\Facades\Log;
/**
 * @property mixed $created_by
 */

class LeaveType extends Model
{
    use UsesUuids;
    use HasAuditFields;
    use HasFactory;
    use NormalizesArrays;
    use FiltersSecureAttachments;

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
            if (!empty($m->getAttribute('title')) && is_string($m->getAttribute('title')))
                $m->setAttribute('title', trim($m->getAttribute('title')));

            if (!empty($m->getAttribute('description')) && is_string($m->getAttribute('description')))
                $m->setAttribute('description', trim($m->getAttribute('description')));
            $m->setAttribute('days', (int) ($m->getAttribute('days') ?? 0));
            if ($m->getAttribute('days') < 0)
                $m->setAttribute('days', 0);
            $ext = (int) ($m->getAttribute(PJC::COL_EXT_DY) ?? 0);
            if ($ext < 0)
                $ext = 0;
            $m->setAttribute(PJC::COL_EXT_DY, $ext);
            $minPct = (int) ($m->getAttribute(PJC::COL_SL_MIN_DD_PCT) ?? 0);
            $maxPct = (int) ($m->getAttribute(PJC::COL_SL_MAX_DD_PCT) ?? 0);
            foreach (['minPct', 'maxPct'] as $var) {
                if (${$var} < 0)
                    ${$var} = 0;
                if (${$var} > 100)
                    ${$var} = 100;
            }
            if ($minPct > $maxPct)
                $minPct = $maxPct;
            $m->setAttribute(PJC::COL_SL_MIN_DD_PCT, $minPct);
            $m->setAttribute(PJC::COL_SL_MAX_DD_PCT, $maxPct);
            try {
                $m->setAttribute('categories', self::normalizeArrayField($m->getAttribute('categories') ?? null));
                $m->setAttribute('conditions', self::normalizeArrayField($m->getAttribute('conditions') ?? null));
                $m->setAttribute('attachments', self::normalizeArrayField($m->getAttribute('attachments') ?? null));
            } catch (\Throwable $e) {
                Log::warning(self::class . ' failed to normalize JSON fields for LeaveType', [
                    'id'    => $m->id ?? null,
                    'error' => $e->getMessage(),
                ]);
                $m->setAttribute('categories', []);
                $m->setAttribute('conditions', []);
                $m->setAttribute('attachments', []);
            }
            if ($m->getAttribute(PJC::COL_HLT_RL) === null)
                $m->setAttribute(PJC::COL_HLT_RL, true);
            if ($m->getAttribute('paid') === null)
                $m->setAttribute('paid', false);
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, DC::COL_TABLE_CREATOR, 'id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->user();
    }
}
