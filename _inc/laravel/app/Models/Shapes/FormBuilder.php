<?php

namespace App\Models;

use App\Config\Constants\{DatabaseConstants as DC, FormsConstants as FC, ProjectsConstants as PJC, EmailsConstants as EC, ActivitiesConstants as AC};
use App\Enums\{AppModuleType, Visibility};
use App\Traits\{DescribesClientForm, DescribesHtmlLinkedEntity, HasAuditFields, NormalizesArrays, PlansByHierarchy, StoresManyRefJson, TracksFailures, UsesUuids};
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany, HasOne};
use Illuminate\Support\Facades\{DB, Log, Schema};
use Illuminate\Support\Str;

class FormBuilder extends Model
{
    use UsesUuids, HasAuditFields, NormalizesArrays, DescribesClientForm, DescribesHtmlLinkedEntity, PlansByHierarchy, StoresManyRefJson, TracksFailures;

    protected $table = DC::TABLE_FORM_BUILD;

    protected $with = [
        'formFields',
    ];

    protected $guarded = [
        'id',
        DC::COL_TABLE_CREATOR,
        DC::COL_TABLE_UPDATER
    ];

    protected $fillable = [
        'code',
        'shortcode',
        'module',
        'name',
        'visibility',

        FC::COL_CAPTCHA_PRV,
        FC::COL_RT_LMT,
        FC::COL_RTT_DAYS,
        FC::COL_EXP_AT,

        AC::COL_IA,
        FC::COL_RQ_LOGIN,
        FC::COL_LMT_ONE_PRSN,
        FC::COL_ACPT_SBM,
        FC::COL_CSRF_CHK_REQ,
        FC::COL_ALW_EDT_AFT_SB,
        FC::COL_CST_RQ,
        FC::COL_CST_DOC,
        FC::COL_PRV_PL_DOC,

        AC::COL_IS_LD_ACT,
        AC::COL_IS_DL_ACT,
        AC::COL_IS_SUP_ACT,
        AC::COL_IS_PRJ_ACT,
        AC::COL_IS_CTC_ACT,

        PJC::COL_LD_ID,
        PJC::COL_DL_ID,
        PJC::COL_SUP_ID,
        PJC::COL_PJ_ID,
        PJC::COL_CTC_ID,

        'receiver',
        EC::COL_RCV_TMP,
        EC::COL_SBM_TMP,
        EC::COL_FLD_TMP,

        FC::COL_RDR_URL,
        'generator',
        FC::COL_SBM_CNT,

        FC::COL_DPL_URL,
        FC::COL_DPL_BY,
        FC::COL_AUTH_DPLS,

        FC::COL_PUB_BY,
        FC::COL_PUB_AT,
        FC::COL_AUTH_PUBS,

        FC::COL_DPL_AT,
        FC::COL_SBM_URL,

        ...DescribesClientForm::CLIENT_FORM_COLUMNS,

        // HTML-linked metadata (new in migration)
        'aria',
        'dataset',
        'selectors',
        'size',
        'tags',

        'variables',
        'fields',
        'scripts',
        'styles',
        'webhooks',
        'notifications',
        'cookies',
        'exports',
        FC::COL_OTHER_URLS,
        FC::COL_BLK_IP_RG,
        'settings',
    ];

    protected $casts = [
        AC::COL_IA => 'boolean',
        FC::COL_RQ_LOGIN => 'boolean',
        FC::COL_LMT_ONE_PRSN => 'boolean',
        FC::COL_ACPT_SBM => 'boolean',
        FC::COL_CSRF_CHK_REQ => 'boolean',
        FC::COL_ALW_EDT_AFT_SB => 'boolean',
        FC::COL_CST_RQ => 'boolean',

        AC::COL_IS_LD_ACT => 'boolean',
        AC::COL_IS_DL_ACT => 'boolean',
        AC::COL_IS_SUP_ACT => 'boolean',
        AC::COL_IS_PRJ_ACT => 'boolean',
        AC::COL_IS_CTC_ACT => 'boolean',

        FC::COL_RT_LMT => 'integer',
        FC::COL_RTT_DAYS => 'integer',
        FC::COL_SBM_CNT => 'integer',

        FC::COL_EXP_AT => 'datetime',
        FC::COL_DPL_AT => 'datetime',
        FC::COL_PUB_AT => 'datetime',

        FC::COL_AUTH_DPLS => 'array',
        FC::COL_AUTH_PUBS => 'array',

        'variables' => 'array',
        'fields' => 'array',
        'scripts' => 'array',
        'styles' => 'array',
        'webhooks' => 'array',
        'notifications' => 'array',
        'cookies' => 'array',
        'exports' => 'array',
        FC::COL_OTHER_URLS => 'array',
        FC::COL_BLK_IP_RG => 'array',
        'settings' => 'array',

        DC::COL_C_AT => 'datetime',
        DC::COL_U_AT => 'datetime',

        'aria'      => 'array',
        'dataset'   => 'array',
        'selectors' => 'array',
        'size'      => 'array',
        'tags'      => 'array',
    ];

    protected $appends = [
        'effective_module',
        'effective_visibility',
        'is_deployed',
        'is_published',
        'effective_accepting_submissions',
        'resolved_client_form_payload',
    ];

    private const CODE_ATTEMPT_LIMIT = 80;
    private const SHORTCODE_ATTEMPT_LIMIT = 80;

    protected static function booted(): void
    {
        static::creating(function (self $m): void {
            try {
                $m->ensureCode();
                $m->ensureShortcode();
            } catch (\Throwable $e) {
                Log::warning(static::class . ' failed to ensure code/shortcode on creating', [
                    'table' => $m->getTable(),
                    'id' => $m->getAttribute('id'),
                    'err' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ]);
            }
        });

        static::saving(function (self $m): void {
            try {
                $m->normalizeFormBuilderColumns();
                $m->encodeJsonSafety();
                $m->applyPublishingAlignment();
                $m->applyExpirationRules();
                $m->applyAcceptingSubmissionsRules();
                $m->applyVisibilityRules();
                $m->normalizeBlockedIpRanges();
                $m->normalizeAllowedRoles();
                $m->normalizeFieldsShape();
            } catch (\Throwable $e) {
                Log::warning(static::class . ' failed to normalize before saving', [
                    'table' => $m->getTable(),
                    'id' => $m->getAttribute('id'),
                    'err' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ]);
            }
        });
    }

    public function formFields(): HasMany
    {
        return $this->hasMany(FormField::class, FC::COL_FM_ID, 'id');
    }

    public function fieldResponse(): HasOne
    {
        return $this->hasOne(FormFieldResponse::class, FC::COL_FM_ID, 'id');
    }

    public function responses(): HasMany
    {
        return $this->hasMany(FormResponse::class, FC::COL_FM_ID, 'id');
    }

    public function deployedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, FC::COL_DPL_BY, 'id');
    }

    public function publishedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, FC::COL_PUB_BY, 'id');
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class, PJC::COL_LD_ID, 'id');
    }

    public function deal(): BelongsTo
    {
        return $this->belongsTo(Deal::class, PJC::COL_DL_ID, 'id');
    }

    public function support(): BelongsTo
    {
        return $this->belongsTo(Support::class, PJC::COL_SUP_ID, 'id');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, PJC::COL_PJ_ID, 'id');
    }

    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class, PJC::COL_CTC_ID, 'id');
    }

    public function receiverTemplate(): BelongsTo
    {
        return $this->belongsTo(EmailTemplate::class, EC::COL_RCV_TMP, 'id');
    }

    public function submissionTemplate(): BelongsTo
    {
        return $this->belongsTo(EmailTemplate::class, EC::COL_SBM_TMP, 'id');
    }

    public function fieldTemplate(): BelongsTo
    {
        return $this->belongsTo(EmailTemplate::class, EC::COL_FLD_TMP, 'id');
    }

    public function getEffectiveModuleAttribute(): string
    {
        $raw = $this->getAttribute('module');
        return (AppModuleType::normalize(is_string($raw) ? $raw : null) ?? AppModuleType::Other)->value;
    }

    public function getEffectiveVisibilityAttribute(): string
    {
        $raw = $this->getAttribute('visibility');
        $vis = Visibility::normalize($raw);
        if ($vis instanceof Visibility) return $vis->value;

        return Visibility::Private->value;
    }

    public function getIsDeployedAttribute(): bool
    {
        $url = trim((string) ($this->getAttribute(FC::COL_DPL_URL) ?? ''));
        return $url !== '';
    }

    public function getIsPublishedAttribute(): bool
    {
        $at = $this->getAttribute(FC::COL_PUB_AT);
        $by = trim((string) ($this->getAttribute(FC::COL_PUB_BY) ?? ''));
        return $at !== null || $by !== '';
    }

    public function getEffectiveAcceptingSubmissionsAttribute(): bool
    {
        return $this->canAcceptSubmissions();
    }

    /**
     * Aggregated client payload for <form>.
     * You can merge your other “settings/variables” here if you want, but keep precedence clear.
     */
    public function getResolvedClientFormPayloadAttribute(): array
    {
        $form = [];
        foreach (static::clientFormColumns() as $k)
            $form[$k] = $this->getAttribute($k);

        $form['method']         = $this->effectiveFormMethod();
        $form['enctype']        = $this->effectiveFormEnctype();
        $form['constraints_ok'] = $this->formConstraintsOk();

        $form['is_active']             = (bool) ($this->getAttribute(AC::COL_IA) ?? false);
        $form['accepting_submissions'] = $this->canAcceptSubmissions();
        $form['is_deployed']           = $this->getIsDeployedAttribute();

        $form['rate_limit']      = (int) ($this->getAttribute(FC::COL_RT_LMT) ?? 0);
        $form['requires_login']  = (bool) ($this->getAttribute(FC::COL_RQ_LOGIN) ?? true);

        // html-linked metadata (aria/dataset/selectors/size/tags)
        foreach (self::htmlLinkedColumns(true) as $k)
            $form[$k] = $this->getAttribute($k);

        return $form;
    }

    public function canAcceptSubmissions(): bool
    {
        $isActive = (bool) ($this->getAttribute(AC::COL_IA) ?? false);
        if (!$isActive) return false;

        if (!$this->getIsDeployedAttribute()) return false;

        $exp = $this->getAttribute(FC::COL_EXP_AT);
        if ($exp !== null) {
            try {
                if (now()->greaterThanOrEqualTo($exp)) return false;
            } catch (\Throwable $e) {
                Log::debug(static::class . ' failed to compare expires_at; conservatively deny submissions', [
                    'id' => $this->getAttribute('id'),
                    'exp' => $exp,
                    'err' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ]);
                return false;
            }
        }

        if (!$this->formConstraintsOk()) return false;

        return (bool) ($this->getAttribute(FC::COL_ACPT_SBM) ?? false);
    }

    public function requiresConsent(): bool
    {
        return (bool) ($this->getAttribute(FC::COL_CST_RQ) ?? false);
    }

    public function hasCaptcha(): bool
    {
        return trim((string) ($this->getAttribute(FC::COL_CAPTCHA_PRV) ?? '')) !== '';
    }

    public function retentionDays(): int
    {
        $v = $this->getAttribute(FC::COL_RTT_DAYS);
        return is_numeric($v) ? max(0, (int) $v) : 730;
    }

    private function normalizeFormBuilderColumns(): void
    {
        $this->setAttribute('name', trim((string) ($this->getAttribute('name') ?? '')));
        if ($this->getAttribute('name') === '') $this->setAttribute('name', 'Form ' . Str::upper(Str::random(6)));

        $receiver = $this->getAttribute('receiver');
        if ($receiver !== null) {
            $r = trim((string) $receiver);
            $this->setAttribute('receiver', $r === '' ? null : $r);
        }

        $gen = $this->getAttribute('generator');
        if ($gen !== null) {
            $g = trim((string) $gen);
            $this->setAttribute('generator', $g === '' ? null : $g);
        }

        $vis = Visibility::normalize($this->getAttribute('visibility'));
        $this->setAttribute('visibility', ($vis ?? Visibility::Private)->value);

        $mod = AppModuleType::normalize(is_string($this->getAttribute('module')) ? $this->getAttribute('module') : null) ?? AppModuleType::Other;
        $this->setAttribute('module', $mod->value);

        $this->normalizeNonNegativeInt(FC::COL_RT_LMT, 0);
        $this->normalizeNonNegativeInt(FC::COL_RTT_DAYS, 730);
        $this->normalizeNonNegativeInt(FC::COL_SBM_CNT, 0);

        $this->normalizeUrlish(FC::COL_RDR_URL);
        $this->normalizeUrlish(FC::COL_DPL_URL);
        $this->normalizeUrlish(FC::COL_SBM_URL);

        $this->normalizeDocRef(FC::COL_CST_DOC);
        $this->normalizeDocRef(FC::COL_PRV_PL_DOC);
    }

    private function encodeJsonSafety(): void
    {
        $jsonFields = [
            FC::COL_AUTH_DPLS,
            FC::COL_AUTH_PUBS,
            'variables',
            'fields',
            'scripts',
            'styles',
            'webhooks',
            'notifications',
            'cookies',
            'exports',
            FC::COL_OTHER_URLS,
            FC::COL_BLK_IP_RG,
            'settings',
            // html-linked json
            'aria',
            'dataset',
            'selectors',
            'size',
            'tags',
        ];

        $this->ensureJsonAttributesAreEncoded($jsonFields);
    }

    private function applyPublishingAlignment(): void
    {
        $pubBy = $this->getAttribute(FC::COL_PUB_BY);
        $pubAt = $this->getAttribute(FC::COL_PUB_AT);

        $dplBy = $this->getAttribute(FC::COL_DPL_BY);
        $dplAt = $this->getAttribute(FC::COL_DPL_AT);

        $dplUrl = trim((string) ($this->getAttribute(FC::COL_DPL_URL) ?? ''));

        if ($dplUrl === '') {
            $this->setAttribute(FC::COL_DPL_BY, null);
            $this->setAttribute(FC::COL_DPL_AT, null);
            $this->setAttribute(FC::COL_AUTH_DPLS, null);
        }

        if (($pubBy === null || trim((string) $pubBy) === '') && $dplBy !== null && $dplUrl !== '') {
            $this->setAttribute(FC::COL_PUB_BY, $dplBy);
            if ($pubAt === null && $dplAt !== null) $this->setAttribute(FC::COL_PUB_AT, $dplAt);
        }
    }

    private function applyExpirationRules(): void
    {
        $exp = $this->getAttribute(FC::COL_EXP_AT);
        if ($exp === null) return;

        try {
            if (now()->greaterThanOrEqualTo($exp)) {
                $this->setAttribute(AC::COL_IA, false);
                $this->setAttribute(FC::COL_ACPT_SBM, false);
            }
        } catch (\Throwable $e) {
            Log::warning(static::class . ' failed to apply expires_at rules', [
                'id' => $this->getAttribute('id'),
                'exp' => $exp,
                'err' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
        }
    }

    private function applyAcceptingSubmissionsRules(): void
    {
        $isActive = (bool) ($this->getAttribute(AC::COL_IA) ?? false);
        if (!$isActive) $this->setAttribute(FC::COL_ACPT_SBM, null);

        $dplUrl = trim((string) ($this->getAttribute(FC::COL_DPL_URL) ?? ''));
        if ($dplUrl === '') $this->setAttribute(FC::COL_ACPT_SBM, null);
    }

    private function applyVisibilityRules(): void
    {
        $vis = Visibility::normalize($this->getAttribute('visibility')) ?? Visibility::Private;

        if (!$this->getIsDeployedAttribute() && $vis->isPublic())
            $this->setAttribute('visibility', Visibility::Draft->value);

        if (!$this->canAcceptSubmissions() && $vis->isPublic())
            $this->setAttribute('visibility', Visibility::Unlisted->value);
    }

    private function normalizeBlockedIpRanges(): void
    {
        $v = $this->getAttribute(FC::COL_BLK_IP_RG);
        $list = $this->normalizeStringList($v);
        if ($list === null) {
            $this->setAttribute(FC::COL_BLK_IP_RG, null);
            return;
        }

        $out = [];
        foreach ($list as $cidr) {
            $s = trim((string) $cidr);
            if ($s === '') continue;
            if (!preg_match('/^([0-9]{1,3}\.){3}[0-9]{1,3}(\/([0-9]|[12][0-9]|3[0-2]))$/', $s)) continue;
            $out[] = $s;
        }

        $out = array_values(array_unique($out));
        $this->setAttribute(FC::COL_BLK_IP_RG, $out ?: null);
    }

    private function normalizeAllowedRoles(): void
    {
        foreach ([FC::COL_AUTH_DPLS, FC::COL_AUTH_PUBS] as $k) {
            $list = $this->normalizeStringList($this->getAttribute($k));
            if ($list === null) {
                $this->setAttribute($k, null);
                continue;
            }
            $out = [];
            foreach ($list as $role) {
                $r = strtolower(trim((string) $role));
                if ($r === '') continue;
                $out[] = $r;
            }
            $out = array_values(array_unique($out));
            $this->setAttribute($k, $out ?: null);
        }
    }

    private function normalizeFieldsShape(): void
    {
        $v = $this->getAttribute('fields');
        $list = $this->normalizeStringList($v);
        $this->setAttribute('fields', $list);
    }

    private function normalizeNonNegativeInt(string $col, int $default): void
    {
        $raw = $this->getAttribute($col);
        if ($raw === null || $raw === '') {
            $this->setAttribute($col, $default);
            return;
        }
        if (!is_numeric($raw)) {
            $this->setAttribute($col, $default);
            return;
        }
        $this->setAttribute($col, max(0, (int) $raw));
    }

    private function normalizeUrlish(string $col): void
    {
        if (!Schema::hasColumn($this->getTable(), $col)) return;

        $raw = $this->getAttribute($col);
        if ($raw === null) return;

        $s = trim((string) $raw);
        if ($s === '') {
            $this->setAttribute($col, null);
            return;
        }

        if (preg_match('/^https?:\/\/[^\s]+$/i', $s)) {
            $this->setAttribute($col, $s);
            return;
        }

        Log::debug(static::class . " invalid url-ish {$col}; keeping raw value", [
            'id' => $this->getAttribute('id'),
            'raw' => $raw,
        ]);
    }

    private function normalizeDocRef(string $col): void
    {
        if (!Schema::hasColumn($this->getTable(), $col)) return;

        $raw = $this->getAttribute($col);
        if ($raw === null) return;

        $s = trim((string) $raw);
        if ($s === '') {
            $this->setAttribute($col, null);
            return;
        }

        if (preg_match('/^https?:\/\/[^\s]+$/i', $s)) {
            $this->setAttribute($col, $s);
            return;
        }

        if (preg_match('/^(attr|file):\/\/.+$/i', $s) || preg_match('/^\//', $s) || Str::isUuid($s)) {
            $this->setAttribute($col, $s);
            return;
        }

        Log::debug(static::class . " invalid doc ref {$col}; keeping raw value", [
            'id' => $this->getAttribute('id'),
            'raw' => $raw,
        ]);
    }

    private function ensureCode(): void
    {
        $cur = trim((string) ($this->getAttribute('code') ?? ''));
        if ($cur !== '') return;

        $attempts = 0;
        do {
            $attempts++;
            $candidate = 'FRM-' . Str::upper(Str::random(10));
            $exists = DB::table($this->getTable())
                ->where('code', $candidate)
                ->where('id', '!=', $this->getAttribute('id') ?? '')
                ->exists();
        } while ($exists && $attempts < self::CODE_ATTEMPT_LIMIT);

        if ($exists) $candidate = 'FRM-' . (string) Str::uuid();

        $this->setAttribute('code', $candidate);
    }

    private function ensureShortcode(): void
    {
        $cur = trim((string) ($this->getAttribute('shortcode') ?? ''));
        if ($cur !== '') return;

        $attempts = 0;
        do {
            $attempts++;
            $token = Str::lower(Str::random(10));
            $candidate = '[form id="' . $token . '"]';
            $exists = DB::table($this->getTable())
                ->where('shortcode', $candidate)
                ->where('id', '!=', $this->getAttribute('id') ?? '')
                ->exists();
        } while ($exists && $attempts < self::SHORTCODE_ATTEMPT_LIMIT);

        if ($exists) $candidate = '[form id="' . (string) Str::uuid() . '"]';

        $this->setAttribute('shortcode', $candidate);
    }
}
