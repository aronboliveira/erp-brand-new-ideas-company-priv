<?php

namespace App\Models;

use App\Config\Constants\{DatabaseConstants as DC, FormsConstants as FC, ProjectsConstants as PJC, UsersConstants as UC};
use App\Traits\{HasAuditFields, NormalizesAddresses, PlansByHierarchy, UsesUuids};
use Illuminate\Database\Eloquent\{Model, Relations\BelongsTo, Relations\HasMany};
use Illuminate\Support\Facades\{DB, Log, Schema};
/**
 * @property int|null $form_id
 * @property mixed $response
 */

class FormResponse extends Model
{
    use UsesUuids, HasAuditFields, PlansByHierarchy, NormalizesAddresses;

    protected $table = DC::TABLE_FORM_RSP;

    protected $with = [
        'form',
    ];

    protected $guarded = [
        'id',
        DC::COL_TABLE_CREATOR,
        DC::COL_TABLE_UPDATER,
    ];

    protected $fillable = [
        FC::COL_FM_ID,
        PJC::COL_SBM_AT,
        PJC::COL_SBM_BY,
        FC::COL_SBM_EML,
        FC::COL_SBM_IP,
        FC::COL_SBM_UA,
        FC::COL_SBM_URL,
        'response',
        'status',
        FC::COL_CAPTCHA_APV,
        FC::COL_CST_CHK,
        FC::COl_CSRF_TKN_APV,
        DC::COL_MW_FREE,
        FC::COL_EXP_AT,
        'lakes',
        'edits',
        'metadata',
    ];

    protected $casts = [
        PJC::COL_SBM_AT => 'datetime',
        FC::COL_EXP_AT => 'datetime',

        FC::COL_CAPTCHA_APV => 'boolean',
        FC::COL_CST_CHK => 'boolean',
        FC::COl_CSRF_TKN_APV => 'boolean',
        DC::COL_MW_FREE => 'boolean',

        'lakes' => 'array',
        'edits' => 'array',
        'metadata' => 'array',

        DC::COL_C_AT => 'datetime',
        DC::COL_U_AT => 'datetime',
    ];

    protected $appends = [
        'is_expired',
        'effective_status',
        'response_array',
    ];

    private const STATUS_VALUES = ['pending', 'approved', 'rejected', 'archived'];

    protected static function booted(): void
    {
        static::saving(function (self $m): void {
            try {
                $m->normalizeCore();
                $m->encodeJsonSafety();
            } catch (\Throwable $e) {
                Log::warning(static::class . ' failed to normalize before saving', [
                    'table' => $m->getTable(),
                    'id' => $m->getAttribute('id'),
                    'error' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ]);
            }
        });
    }

    public function form(): BelongsTo
    {
        return $this->belongsTo(FormBuilder::class, FC::COL_FM_ID, 'id');
    }

    public function submittedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, PJC::COL_SBM_BY, 'id');
    }

    public function isExpired(): bool
    {
        $exp = $this->getAttribute(FC::COL_EXP_AT);
        if ($exp === null) return false;

        try {
            return now()->greaterThanOrEqualTo($exp);
        } catch (\Throwable $e) {
            Log::debug(static::class . ' failed to evaluate expires_at', [
                'id' => $this->getAttribute('id'),
                'exp' => $exp,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            return false;
        }
    }

    public function isPending(): bool
    {
        return (string) ($this->getAttribute('status') ?? 'pending') === 'pending';
    }

    public function isApproved(): bool
    {
        return (string) ($this->getAttribute('status') ?? 'pending') === 'approved';
    }

    public function isRejected(): bool
    {
        return (string) ($this->getAttribute('status') ?? 'pending') === 'rejected';
    }

    public function isArchived(): bool
    {
        return (string) ($this->getAttribute('status') ?? 'pending') === 'archived';
    }

    public function fieldResponses(): HasMany
    {
        return $this->hasMany(FormFieldResponse::class, FC::COL_FM_DT_RSP_ID, 'id');
    }

    public function getIsExpiredAttribute(): bool
    {
        $exp = $this->getAttribute(FC::COL_EXP_AT);
        if ($exp === null) return false;

        try {
            return now()->greaterThanOrEqualTo($exp);
        } catch (\Throwable $e) {
            Log::debug(static::class . ' failed to compare expires_at', [
                'id' => $this->getAttribute('id'),
                'exp' => $exp,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            return false;
        }
    }

    public function getEffectiveStatusAttribute(): string
    {
        $raw = strtolower(trim((string) ($this->getAttribute('status') ?? 'pending')));
        return in_array($raw, self::STATUS_VALUES, true) ? $raw : 'pending';
    }

    public function getResponseArrayAttribute(): array
    {
        $raw = $this->getAttribute('response');
        if ($raw === null) return [];

        if (is_array($raw)) return $raw;

        $s = trim((string) $raw);
        if ($s === '') return [];

        try {
            $decoded = json_decode($s, true, 512, JSON_THROW_ON_ERROR);
            return is_array($decoded) ? $decoded : [];
        } catch (\Throwable) {
            return [];
        }
    }

    private function normalizeCore(): void
    {
        $status = strtolower(trim((string) ($this->getAttribute('status') ?? 'pending')));
        $this->setAttribute('status', in_array($status, self::STATUS_VALUES, true) ? $status : 'pending');

        foreach ([FC::COL_CAPTCHA_APV, FC::COL_CST_CHK, FC::COl_CSRF_TKN_APV, DC::COL_MW_FREE] as $b) {
            if (!Schema::hasColumn($this->getTable(), $b)) continue;
            $v = $this->getAttribute($b);
            if ($v === null) continue;
            $this->setAttribute($b, (bool) $v);
        }

        $email = $this->getAttribute(FC::COL_SBM_EML);
        if ($email !== null)
            $this->setAttribute(
                FC::COL_SBM_EML,
                static::normalizeEmail((string) $email, 'form_response.submitted_email', (string) ($this->getAttribute('id') ?? ''))
            );

        $ip = $this->getAttribute(FC::COL_SBM_IP);
        if ($ip !== null) {
            $ip = trim((string) $ip);
            $this->setAttribute(FC::COL_SBM_IP, $ip === '' ? null : $ip);
        }

        foreach ([FC::COL_SBM_UA, FC::COL_SBM_URL] as $k) {
            $v = $this->getAttribute($k);
            if ($v === null) continue;
            $s = trim((string) $v);
            $this->setAttribute($k, $s === '' ? null : $s);
        }

        $resp = $this->getAttribute('response');
        if ($resp === null) return;

        if (is_array($resp) || is_object($resp)) {
            try {
                $this->setAttribute('response', json_encode($resp, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE));
            } catch (\Throwable $e) {
                Log::debug(static::class . ' failed to json_encode response; keeping raw', [
                    'id' => $this->getAttribute('id'),
                    'error' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ]);
            }
            return;
        }

        $s = trim((string) $resp);
        $this->setAttribute('response', $s === '' ? null : $s);
    }

    private function encodeJsonSafety(): void
    {
        $this->ensureJsonAttributesAreEncoded(['lakes', 'edits', 'metadata']);
    }
}
