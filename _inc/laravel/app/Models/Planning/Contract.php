<?php

namespace App\Models;

use App\Config\Constants\{BillsConstants as BC, DatabaseConstants as DC, ProjectsConstants as PJC};
use App\Enums\{EvaluationStatus, Frequency};
use App\Helpers\ErrorHandler;
use App\Services\ContractRequestService;
use App\Traits\{DefinesDates, FiltersSecureAttachments, HasAuditFields, NormalizesArrays, PlansByHierarchy, PlansWithSchedule, UsesUuids};
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany, HasOne};
use Illuminate\Support\Facades\{DB, Log, Schema, Validator};
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class Contract extends Model
{
    use UsesUuids, HasAuditFields, PlansByHierarchy, NormalizesArrays, FiltersSecureAttachments, DefinesDates, PlansWithSchedule;

    public const TABLE = DC::TABLE_CONTRACTS;

    protected $table = self::TABLE;

    // * legacy, prefer using EvaluationStatus enum
    private const STATUS_OPTIONS = [
        'accept'  => 'Accept',
        'decline' => 'Decline',
        'draft'  => 'Draft',
        'pending' => 'Pending',
        'active'  => 'Active',
        'suspended' => 'Suspended',
        'completed' => 'Completed',
        'cancelled' => 'Cancelled',
        'expired'   => 'Expired',
        'archived'  => 'Archived',
    ]; // * manter erro gramatical por enquanto, para compatibilidade em testes

    protected $fillable = [
        'type',
        'title',
        'subject',
        'value',
        'currency',
        'description',
        'notes',
        PJC::COL_S_DT,
        PJC::COL_E_DT,
        PJC::COL_CDESC,
        'status',
        'renewable',
        PJC::COL_ARNW,
        'frequency',
        'company',
        PJC::COL_CLIENT_NAME,
        PJC::COL_OBG_NAME,
        PJC::COL_OBL_NAME,
        PJC::COL_OBG_IDF,
        PJC::COL_OBL_IDF,
        PJC::COL_OBG_ADDR,
        PJC::COL_OBL_ADDR,
        PJC::COL_OBG_CTC,
        PJC::COL_OBL_CTC,
        PJC::COL_CO_SIG,
        PJC::COL_CL_SIG,
        PJC::COL_CL_SIGN_AT,
        PJC::COL_CO_SIGN_AT,
        PJC::COL_APV_AT,
        PJC::COL_APV_BY,
        PJC::COL_REJ_AT,
        PJC::COL_REJ_BY,
        PJC::COL_WT_NM,
        PJC::COL_WT2_NM,
        PJC::COL_WT_IDF,
        PJC::COL_WT2_IDF,
        PJC::COL_WT_SIG,
        PJC::COL_WT2_SIG,
        PJC::COL_WT_SIGN_AT,
        PJC::COL_WT2_SIGN_AT,
        PJC::COL_PJ_ID,
        PJC::COL_F_PATH,
        PJC::COL_ATC_PATHS,
        'metadata'
    ];

    protected $guarded = [
        'id',
        DC::COL_TABLE_CREATOR,
        DC::COL_TABLE_UPDATER,
    ];

    protected $casts = [
        PJC::COL_S_DT        => 'date',
        PJC::COL_E_DT        => 'date',
        PJC::COL_CL_SIGN_AT  => 'date',
        PJC::COL_APV_AT      => 'date',
        PJC::COL_REJ_AT      => 'date',
        PJC::COL_CO_SIGN_AT  => 'date',
        PJC::COL_WT_SIGN_AT  => 'date',
        PJC::COL_WT2_SIGN_AT => 'date',
        'renewable'          => 'boolean',
        PJC::COL_ARNW        => 'boolean',
        'status'             => EvaluationStatus::class,
        'frequency'          => Frequency::class,
        PJC::COL_ATC_PATHS   => 'array',
        'metadata'           => 'array',
    ];

    protected $with = [
        'contractType',
        'project',
    ];

    protected $appends = [
        'is_fully_signed',
        'is_active',
    ];

    public static array $contractErrors = [];

    protected static function booted(): void
    {
        parent::booted();
        static::saving(function (self $c): void {
            if (empty((string) trim($c->getAttribute('code'))) || !is_string($c->getAttribute('code'))) {
                do $newCode = 'CTR-' . strtoupper((string) Str::uuid());
                while (DB::table(self::TABLE)->where('code', $newCode)->exists());
                $c->setAttribute('code', $newCode);
            }
            if ($c->getAttribute('frequency') === null || !in_array($c->getAttribute('frequency'), array_column(Frequency::cases(), 'value'), true))
                $c->setAttribute('frequency', Frequency::Once->value);
            if ($c->getAttribute('status') === null || !in_array($c->getAttribute('status'), array_column(EvaluationStatus::cases(), 'value'), true))
                $c->setAttribute('status', EvaluationStatus::Draft->value);
            if ($c->getAttribute('renewable') === false)
                $c->setAttribute(PJC::COL_ARNW, false);
            $start = $c->getAttribute(PJC::COL_S_DT);
            $end   = $c->getAttribute(PJC::COL_E_DT);
            if ($start && $end && $end < $start)
                throw ValidationException::withMessages([
                    PJC::COL_E_DT => 'A data de término não pode ser anterior ao início.',
                ]);
            foreach (['title', 'subject', PJC::COL_CLIENT_NAME] as $f) {
                $value = $c->getAttribute($f);
                if (is_string($value))
                    $c->setAttribute($f, trim($value));
            }
            $typeKey = $c->getAttribute('type');
            if ($typeKey) {
                /** @var ContractType|null $t */
                $t = $c->contractType()->first();
                if ($t)
                    self::applyTypeConstraints($c, $t);
            }
            $c->resolveApprovalRejectionWinner();
            $c->applyDynamicStatusFromColumns();
            $v = Validator::make($c->getAttributes(), [
                'currency' => ['nullable', 'string', 'max:8'],
                'value'    => ['nullable', 'regex:/^\d+(\.\d{1,2})?$/'], // compatível com coluna string
            ]);
            if ($v->fails())
                throw new ValidationException($v);
        });
    }

    public function getDurationMonthsAttribute(): ?int
    {
        if (!$this->{PJC::COL_S_DT} || !$this->{PJC::COL_E_DT}) return null;
        return $this->{PJC::COL_S_DT}->diffInMonths($this->{PJC::COL_E_DT}) ?: 0;
    }

    public function setValueAttribute($raw): void
    {
        if ($raw === null || $raw === '') {
            $this->attributes['value'] = null;
            return;
        }

        // remove separadores de milhar e normaliza decimal
        $s = is_string($raw) ? trim($raw) : (string)$raw;
        $s = str_replace([' ', "\xC2\xA0"], '', $s);        // espaços comuns e NBSP
        $s = str_replace(['R$', '$'], '', $s);              // símbolos comuns
        // converte "1.234,56" -> "1234.56"
        $s = preg_replace('/\.(?=.*\.)/', '', $s);          // remove pontos "internos"
        $s = str_replace(',', '.', $s);
        if (!is_numeric($s)) {
            throw ValidationException::withMessages(['value' => 'Valor contratual inválido.']);
        }
        $this->attributes['value'] = number_format((float)$s, 2, '.', '');
    }

    public function setCurrencyAttribute($v): void
    {
        $this->attributes['currency'] = $v ? strtoupper(trim((string)$v)) : null;
    }

    public function setFrequencyAttribute($v): void
    {
        $norm = Frequency::normalize($v);
        $this->attributes['frequency'] = $norm?->value;
    }

    public function setStatusAttribute($v): void
    {
        $norm = EvaluationStatus::normalize($v) ?? EvaluationStatus::Pending;
        $this->attributes['status'] = $norm->value;
    }

    public static function status(): array
    {
        // Mantém API existente, mas passa a usar o enum de status
        return EvaluationStatus::labels();
    }

    public function client(): ?BelongsTo
    {
        return Utility::getClient($this);
    }

    public function clients(): BelongsTo // * kept for compatibility, don't use in endpoints
    {
        return $this->client();
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(ContractAttachment::class, PJC::COL_CTC_ID, 'id');
    }

    public function contractType(): HasOne
    {
        return $this->hasOne(ContractType::class, 'id', 'type');
    }

    public function types(): HasOne // * kept for compatibility, don't use in endpoints
    {
        return $this->contractType();
    }

    public static function getContractSummary($contracts): string
    {
        return app(ContractRequestService::class)->getContractSummary($contracts);
    }

    public function project(): HasOne
    {
        return $this->hasOne(Project::class, 'id', PJC::COL_PJ_ID);
    }

    public function projects(): HasOne // * kept for compatibility, don't use in endpoints
    {
        return $this->project();
    }

    public function files(): HasMany
    {
        return $this->hasMany(ContractAttachment::class, PJC::COL_CTC_ID, 'id');
    }

    public function notes(): HasMany
    {
        return $this->hasMany(ContractNote::class, PJC::COL_CTC_ID, 'id');
    }

    public function comment(): HasMany
    {
        return $this->hasMany(ContractComment::class, PJC::COL_CTC_ID, 'id');
    }

    public function note(): HasMany
    {
        return $this->hasMany(ContractNote::class, PJC::COL_CTC_ID, 'id');
    }

    public function contractAttachment(): HasOne
    {
        return $this->hasOne(ContractAttachment::class, PJC::COL_CTC_ID, 'id')->latestOfMany();
    }


    public function ContractAttechment(): HasOne
    {
        return $this->contractAttachment();
    }

    public function comments(): HasMany
    {
        return $this->hasMany(ContractComment::class, PJC::COL_CTC_ID, 'id');
    }

    public function notesRows(): HasMany
    {
        return $this->hasMany(ContractNote::class, PJC::COL_CTC_ID, 'id');
    }

    public function contractComment(): BelongsTo
    {
        return $this->belongsTo(ContractComment::class, 'id', PJC::COL_CTC_ID);
    }

    public function contractNote(): BelongsTo
    {
        return $this->belongsTo(ContractNote::class, 'id', PJC::COL_CTC_ID);
    }

    public function getIsFullySignedAttribute(): bool
    {
        return (bool) ($this->{PJC::COL_CL_SIGN_AT} && $this->{PJC::COL_CO_SIGN_AT});
    }

    public function getIsActiveAttribute(): bool
    {
        $today = today();
        $status = EvaluationStatus::normalize($this->status);
        return in_array($status, [EvaluationStatus::Active, EvaluationStatus::Pending], true)
            && $this->start_date
            && $this->end_date
            && $this->start_date->lessThanOrEqualTo($today)
            && $this->end_date->greaterThanOrEqualTo($today);
    }

    protected function pushAutomaticEditHistory(string $event, array $payload = []): void
    {
        try {
            $meta = self::normalizeArrayField($this->getAttribute('metadata'));
            $hist = $meta['automatic_edit_history'] ?? [];
            if (!is_array($hist)) $hist = [];

            $hist[] = array_merge([
                'at'    => now()->toIso8601String(),
                'event' => $event,
                'id'    => (string) ($this->getAttribute('id') ?? ''),
            ], $payload);

            $meta['automatic_edit_history'] = array_values($hist);
            $this->setAttribute('metadata', $meta);
        } catch (\Throwable $e) {
            Log::warning(static::class . ' failed to push automatic_edit_history', [
                'id'    => $this->getAttribute('id'),
                'error' => $e->getMessage(),
                'file'  => $e->getFile(),
                'line'  => $e->getLine(),
            ]);
        }
    }

    protected function setStatusAutomatically(EvaluationStatus|string|null $next, string $reason, array $ctx = []): void
    {
        try {
            $nextEnum = $next instanceof EvaluationStatus ? $next : EvaluationStatus::normalize($next);
            if (!$nextEnum) return;

            $curEnum = EvaluationStatus::normalize($this->getAttribute('status'));
            $curVal  = $curEnum?->value ?? (string) ($this->getAttribute('status') ?? '');

            if ($curVal === $nextEnum->value) return;

            $this->setAttribute('status', $nextEnum->value);
            $this->pushAutomaticEditHistory('status_auto_set', [
                'reason' => $reason,
                'from'   => $curVal,
                'to'     => $nextEnum->value,
                'ctx'    => $ctx,
            ]);
        } catch (\Throwable $e) {
            Log::warning(static::class . ' failed to set status automatically', [
                'id'    => $this->getAttribute('id'),
                'error' => $e->getMessage(),
                'file'  => $e->getFile(),
                'line'  => $e->getLine(),
            ]);
        }
    }

    protected function resolveApprovalRejectionWinner(): void
    {
        try {
            $apvBy = $this->getAttribute(PJC::COL_APV_BY);
            $apvAt = $this->toImmutableSafe($this->getAttribute(PJC::COL_APV_AT));

            $rejBy = $this->getAttribute(PJC::COL_REJ_BY);
            $rejAt = $this->toImmutableSafe($this->getAttribute(PJC::COL_REJ_AT));

            $apvByOk = is_string($apvBy) ? trim($apvBy) !== '' : !empty($apvBy);
            $rejByOk = is_string($rejBy) ? trim($rejBy) !== '' : !empty($rejBy);

            $hasApv = $apvByOk || (bool) $apvAt;
            $hasRej = $rejByOk || (bool) $rejAt;

            if (!$hasApv && !$hasRej) return;

            if ($hasApv && !$hasRej) {
                $this->setAttribute(PJC::COL_REJ_BY, null);
                $this->setAttribute(PJC::COL_REJ_AT, null);
                return;
            }

            if ($hasRej && !$hasApv) {
                $this->setAttribute(PJC::COL_APV_BY, null);
                $this->setAttribute(PJC::COL_APV_AT, null);
                return;
            }

            $winner = 'reject';

            switch (true) {
                case $apvAt && $rejAt:
                    $winner = $apvAt->greaterThan($rejAt) ? 'approve' : 'reject';
                    if ($apvAt->equalTo($rejAt)) $winner = 'reject';
                    break;

                case $apvAt && !$rejAt:
                    $winner = 'approve';
                    break;

                case !$apvAt && $rejAt:
                    $winner = 'reject';
                    break;

                default:
                    $winner = 'reject';
            }

            if ($winner === 'reject') {
                $this->setAttribute(PJC::COL_APV_BY, null);
                $this->setAttribute(PJC::COL_APV_AT, null);

                $this->setStatusAutomatically(EvaluationStatus::Suspended, 'approval_rejection_conflict_resolved', [
                    'winner' => 'reject',
                    'apv_at' => $apvAt?->toIso8601String(),
                    'rej_at' => $rejAt?->toIso8601String(),
                ]);

                return;
            }

            $this->setAttribute(PJC::COL_REJ_BY, null);
            $this->setAttribute(PJC::COL_REJ_AT, null);

            $this->setStatusAutomatically(EvaluationStatus::Accept, 'approval_rejection_conflict_resolved', [
                'winner' => 'approve',
                'apv_at' => $apvAt?->toIso8601String(),
                'rej_at' => $rejAt?->toIso8601String(),
            ]);
        } catch (\Throwable $e) {
            ErrorHandler::evaluateExistenceToLogChannel(
                'contract_errors',
                [
                    'message' => static::class . ' failed to resolve approval/rejection winner',
                    'context' => [
                        'id'    => $this->getAttribute('id'),
                        'error' => $e->getMessage(),
                        'file'  => $e->getFile(),
                        'line'  => $e->getLine(),
                    ],
                ]
            );
        }
    }

    /**
     * Call from booted()->saving() AFTER other trait normalizations have run.
     * This method ONLY mutates the "status" attribute.
     */
    protected function applyDynamicStatusFromColumns(): void
    {
        try {
            $cur = EvaluationStatus::normalize($this->getAttribute('status'));
            $next = $this->inferDynamicStatusFromColumns();
            if (!$next) return;

            $curVal = $cur?->value ?? (string) ($this->getAttribute('status') ?? '');
            if ($curVal === $next->value) return;

            $reason = ($curVal === EvaluationStatus::Expired->value && $next->value !== EvaluationStatus::Expired->value)
                ? 'expired_reverted_by_system'
                : 'dynamic_status_inference';

            $this->setStatusAutomatically($next, $reason, [
                'cur'  => $curVal,
                'next' => $next->value,
            ]);
        } catch (\Throwable $e) {
            Log::warning(static::class . ' failed to apply dynamic status', [
                'id'    => $this->getAttribute('id'),
                'error' => $e->getMessage(),
                'file'  => $e->getFile(),
                'line'  => $e->getLine(),
            ]);
        }
    }

    /**
     * Computes status purely from:
     * - dates: start/end (PJC::COL_S_DT / PJC::COL_E_DT)
     * - approval: (PJC::COL_APV_BY / PJC::COL_APV_AT)
     * - beneficiary validity: client_id OR (client_name OR (obl_name || obl_idf))
     * - obligor validity: (obg_name || obg_idf)
     *
     * Returns null when you should not override the existing status.
     */
    protected function inferDynamicStatusFromColumns(): ?EvaluationStatus
    {
        $cur = EvaluationStatus::normalize($this->getAttribute('status'));

        if (in_array($cur, [EvaluationStatus::Archived, EvaluationStatus::Cancelled, EvaluationStatus::Decline], true))
            return null;

        $hasBeneficiary = $this->contractHasValidBeneficiary();
        $hasObligor     = $this->contractHasValidObligor();

        if (!$hasBeneficiary || !$hasObligor)
            return EvaluationStatus::Draft;

        $start = $this->toImmutableSafe($this->getAttribute(PJC::COL_S_DT));
        $end   = $this->toImmutableSafe($this->getAttribute(PJC::COL_E_DT));
        $now   = now();

        $hasStart = (bool) $start;
        $hasEnd   = (bool) $end;

        if ($hasEnd && $end->lessThan($now))
            return EvaluationStatus::Expired;

        if ($this->contractIsRejected())
            return EvaluationStatus::Suspended;

        if ($this->contractIsApproved())
            return EvaluationStatus::Accept;

        if (!$hasStart && !$hasEnd) return EvaluationStatus::Draft;
        return EvaluationStatus::Pending;
    }


    protected function contractIsApproved(): bool
    {
        $by = $this->getAttribute(PJC::COL_APV_BY);
        $at = $this->getAttribute(PJC::COL_APV_AT);

        $byOk = is_string($by) ? trim($by) !== '' : !empty($by);
        $atOk = $this->toImmutableSafe($at) !== null;

        return $byOk && $atOk;
    }

    protected function contractIsRejected(): bool
    {
        $by = $this->getAttribute(PJC::COL_REJ_BY);
        $at = $this->getAttribute(PJC::COL_REJ_AT);

        $byOk = is_string($by) ? trim($by) !== '' : !empty($by);
        $atOk = $this->toImmutableSafe($at) !== null;

        return $byOk && $atOk;
    }

    /**
     * Beneficiary validity:
     * - valid client_id (exists) OR
     * - non-empty client_name OR
     * - non-empty obligor name/idf (fallback)
     */
    protected function contractHasValidBeneficiary(): bool
    {
        $clientId = $this->getAttribute('client_id') ?? null;

        if (is_string($clientId) && trim($clientId) !== '' && $this->looksLikeUuidSafe($clientId))
            return $this->fkExistsSafe(DC::TABLE_USERS, $clientId);

        $clientName = $this->getAttribute(PJC::COL_CLIENT_NAME) ?? null;
        if (is_string($clientName) && trim($clientName) !== '') return true;

        $oblName = $this->getAttribute(PJC::COL_OBL_NAME) ?? null;
        $oblIdf  = $this->getAttribute(PJC::COL_OBL_IDF) ?? null;

        return (is_string($oblName) && trim($oblName) !== '')
            || (is_string($oblIdf) && trim($oblIdf) !== '');
    }

    /**
     * Obligor validity:
     * - non-empty obg name OR non-empty obg idf
     */
    protected function contractHasValidObligor(): bool
    {
        $obgName = $this->getAttribute(PJC::COL_OBG_NAME) ?? null;
        $obgIdf  = $this->getAttribute(PJC::COL_OBG_IDF) ?? null;

        return (is_string($obgName) && trim($obgName) !== '')
            || (is_string($obgIdf) && trim($obgIdf) !== '');
    }

    protected function toImmutableSafe(mixed $date): ?\Carbon\CarbonImmutable
    {
        try {
            if ($date instanceof \Carbon\CarbonImmutable) return $date;
            if ($date instanceof \Carbon\Carbon) return \Carbon\CarbonImmutable::instance($date);
            if (is_string($date) && trim($date) !== '') return \Carbon\CarbonImmutable::parse($date);
            return null;
        } catch (\Throwable $e) {
            Log::debug(static::class . ' failed to parse date for status inference', [
                'id'    => $this->getAttribute('id'),
                'raw'   => $date,
                'error' => $e->getMessage(),
                'file'  => $e->getFile(),
                'line'  => $e->getLine(),
            ]);
            return null;
        }
    }

    protected function looksLikeUuidSafe(string $value): bool
    {
        try {
            return Str::isUuid($value);
        } catch (\Throwable) {
            return false;
        }
    }

    protected function fkExistsSafe(string $table, string $id): bool
    {
        try {
            return DB::table($table)->where('id', $id)->exists();
        } catch (\Throwable $e) {
            Log::debug(static::class . ' failed FK existence check', [
                'table' => $table,
                'id'    => $this->getAttribute('id'),
                'fk'    => $id,
                'error' => $e->getMessage(),
                'file'  => $e->getFile(),
                'line'  => $e->getLine(),
            ]);
            return false;
        }
    }

    /**
     * Apply ContractType constraints to the Contract model
     *
     * @param self $contract
     * @param ContractType $type
     * @return void
     * @throws \Illuminate\Validation\ValidationException
     */
    private static function applyTypeConstraints(self $contract, ContractType $type): void
    {
        $value = $contract->getAttribute('value');
        $startDate = self::toImmutable($contract->getAttribute(PJC::COL_S_DT));
        $endDate = self::toImmutable($contract->getAttribute(PJC::COL_E_DT));
        if ($value !== null && $value !== '') {
            $numericValue = is_numeric($value) ? (float)$value : null;
            if ($numericValue !== null) {
                $minValue = $type->getAttribute(BC::COL_MIN_V);
                $maxValue = $type->getAttribute(BC::COL_MAX_V);
                if ($minValue !== null && $numericValue < (float)$minValue) {
                    Log::notice([
                        'value' => "O valor do contrato não pode ser menor que " . number_format($minValue, 2, ',', '.'),
                    ]);
                    $contract->setAttribute('value', number_format((float)$minValue, 2, '.', ''));
                }
                if ($maxValue !== null && $numericValue > (float)$maxValue) {
                    Log::notice([
                        'value' => "O valor do contrato não pode ser maior que " . number_format($maxValue, 2, ',', '.'),
                    ]);
                    $contract->setAttribute('value', number_format((float)$maxValue, 2, '.', ''));
                }
            }
        }
        if ($startDate && $endDate) {
            $durationMonths = $startDate->diffInMonths($endDate);
            $minMonths = $type->getAttribute(BC::COL_MIN_M);
            $maxMonths = $type->getAttribute(BC::COL_MAX_M);
            if ($minMonths !== null && $durationMonths < (int)$minMonths) {
                Log::notice([
                    PJC::COL_E_DT => "A duração do contrato deve ser de pelo menos {$minMonths} " . ($minMonths === 1 ? 'mês' : 'meses'),
                ]);
                $contract->setAttribute(PJC::COL_E_DT, $startDate->addMonths((int)$minMonths)->toDateString()); // todo for now set, but later throw error
            }
            if ($maxMonths !== null && $durationMonths > (int)$maxMonths) {
                Log::notice([
                    PJC::COL_E_DT => "A duração do contrato não pode exceder {$maxMonths} " . ($maxMonths === 1 ? 'mês' : 'meses'),
                ]);
                $contract->setAttribute(PJC::COL_E_DT, $startDate->addMonths((int)$maxMonths)->toDateString());
            }
        }
        $renewable = $contract->getAttribute('renewable');
        $allowsRenegotiation = $type->getAttribute(BC::COL_RNGT);
        if ($allowsRenegotiation === false && $renewable === true) {
            Log::notice([
                'renewable' => 'Este tipo de contrato não permite renovação.',
            ]);
            $contract->setAttribute('renewable', false);
        }
        $allowsSeveranceGuarantee = $type->getAttribute(BC::COL_SVR_GRT);
        if ($allowsSeveranceGuarantee === false) {
            // todo work on this later
        }
        $definesTermination = $type->getAttribute(BC::COL_DEF_TRMC);
        if ($definesTermination === true) {
            // todo work on this later
        }
        $typeTerms = $type->getAttribute(BC::COL_TC);
        $contractDescription = $contract->getAttribute('description');
        if ($typeTerms && (!$contractDescription || trim($contractDescription) === ''))
            $contract->setAttribute('description', $typeTerms);
    }

    /** @var \Carbon\CarbonImmutable|null */
    private static function toImmutable($date)
    {
        if ($date instanceof \Carbon\CarbonImmutable) return $date;
        if ($date instanceof \Carbon\Carbon) return \Carbon\CarbonImmutable::instance($date);
        if (is_string($date) && $date !== '') return \Carbon\CarbonImmutable::parse($date);
        return null;
    }
}
