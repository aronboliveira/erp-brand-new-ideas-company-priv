<?php

namespace App\Models;

use App\Config\Constants\{BillsConstants as BC, DatabaseConstants as DC, ProjectsConstants as PJC};
use App\Enums\{EvaluationStatus, Frequency};
use App\Traits\{ChecksLogin, HasAuditFields, UsesUuids};
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany, HasOne};
use Illuminate\Support\Facades\{Log, Validator};
use Illuminate\Validation\ValidationException;

class Contract extends Model
{
    use ChecksLogin, UsesUuids, HasAuditFields;

    public const TABLE = DC::TABLE_CONTRACTS;

    protected $table = self::TABLE;

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
        PJC::COL_APV_BY,
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
    ];

    protected $guarded = [
        'id',
        DC::COL_TABLE_CREATOR,
        DC::COL_TABLE_UPDATER,
    ];

    protected $casts = [
        PJC::COL_S_DT         => 'date',
        PJC::COL_E_DT           => 'date',
        PJC::COL_CL_SIGN_AT  => 'date',
        PJC::COL_CO_SIGN_AT  => 'date',
        PJC::COL_WT_SIGN_AT  => 'date',
        PJC::COL_WT2_SIGN_AT => 'date',
        'renewable'          => 'boolean',
        PJC::COL_ARNW        => 'boolean',
        'status'             => EvaluationStatus::class,
        'frequency'          => Frequency::class,
        PJC::COL_ATC_PATHS   => 'array',
    ];

    protected $with = [
        'client',
        'contractType',
        'project',
    ];

    protected $appends = [
        'is_fully_signed',
        'is_active',
    ];

    protected static function booted(): void
    {
        parent::booted();
        static::saving(function (self $c): void {
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
            $v = Validator::make($c->getAttributes(), [
                'currency' => ['nullable', 'string', 'max:8'],
                'value'    => ['nullable', 'regex:/^\d+(\.\d{1,2})?$/'], // compatível com coluna string
            ]);
            if ($v->fails())
                throw new ValidationException($v);
        });
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
                    Log::warning([
                        'value' => "O valor do contrato não pode ser menor que " . number_format($minValue, 2, ',', '.'),
                    ]);
                    $contract->setAttribute('value', number_format((float)$minValue, 2, '.', ''));
                }
                if ($maxValue !== null && $numericValue > (float)$maxValue) {
                    Log::warning([
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
                Log::warning([
                    PJC::COL_E_DT => "A duração do contrato deve ser de pelo menos {$minMonths} " . ($minMonths === 1 ? 'mês' : 'meses'),
                ]);
                $contract->setAttribute(PJC::COL_E_DT, $startDate->addMonths((int)$minMonths)->toDateString()); // todo for now set, but later throw error
            }
            if ($maxMonths !== null && $durationMonths > (int)$maxMonths) {
                Log::warning([
                    PJC::COL_E_DT => "A duração do contrato não pode exceder {$maxMonths} " . ($maxMonths === 1 ? 'mês' : 'meses'),
                ]);
                $contract->setAttribute(PJC::COL_E_DT, $startDate->addMonths((int)$maxMonths)->toDateString());
            }
        }
        $renewable = $contract->getAttribute('renewable');
        $allowsRenegotiation = $type->getAttribute(BC::COL_RNGT);
        if ($allowsRenegotiation === false && $renewable === true) {
            Log::warning([
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

    public function getDurationMonthsAttribute(): ?int
    {
        if (!$this->{PJC::COL_S_DT} || !$this->{PJC::COL_E_DT}) return null;
        return $this->{PJC::COL_S_DT}->diffInMonths($this->{PJC::COL_E_DT}) ?: 0;
    }

    /*
    |--------------------------------------------------------------------------
    | Mutators (saneamento defensivo)
    |--------------------------------------------------------------------------
    */
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

    public function client(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'client_id');
    }

    public function clients(): HasOne // * kept for compatibility, don't use in endpoints
    {
        return $this->client();
    }

    public function contractType(): HasOne
    {
        return $this->hasOne(ContractType::class, 'id', 'type');
    }

    public function types(): HasOne // * kept for compatibility, don't use in endpoints
    {
        return $this->contractType();
    }

    public static function getContractSummary($contracts): string // ! CHANGED
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof \Illuminate\Http\RedirectResponse
        )
            return $userOrRedirect;

        $user = $userOrRedirect;
        $total = $contracts->sum(fn($c) => $c->value);
        return $user?->priceFormat($total);
    }

    public function project(): HasOne
    {
        return $this->hasOne(Project::class, 'id', 'project_id');
    }

    public function projects(): HasOne // * kept for compatibility, don't use in endpoints
    {
        return $this->project();
    }

    public function files(): HasMany
    {
        return $this->hasMany(ContractAttachment::class, 'contract_id', 'id');
    }

    public function notes(): HasMany
    {
        return $this->hasMany(ContractNotes::class, 'contract_id', 'id');
    }

    public function comment(): HasMany
    {
        return $this->hasMany(ContractComment::class, 'contract_id', 'id');
    }

    public function note(): HasMany
    {
        return $this->hasMany(ContractNotes::class, 'contract_id', 'id');
    }

    public function contractAttachment(): BelongsTo // ! CHANGED
    {
        return $this->belongsTo(ContractAttachment::class, 'id', 'contract_id');
    }

    public function ContractAttechment(): BelongsTo // * KEPT FOR COMPATIBILITY, DON'T USE IN ENDPOINTS
    {
        return $this->contractAttachment();
    }

    public function contractComment(): BelongsTo // ! CHANGED
    {
        return $this->belongsTo(ContractComment::class, 'id', 'contract_id');
    }

    public function contractNote(): BelongsTo // ! CHANGED
    {
        return $this->belongsTo(ContractNotes::class, 'id', 'contract_id');
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
}
