<?php

namespace App\Models;

use App\Config\Constants\{BillsConstants as BC, DatabaseConstants as DC, ProjectsConstants as PJC};
use App\Enums\{EvaluationStatus, Frequency};
use App\Traits\{ChecksLogin, HasAuditFields, UsesUuids};
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany, HasOne};
use Illuminate\Support\Facades\Validator;
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
        DC::TABLE_CREATOR,
        DC::TABLE_UPDATER,
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
            if ($c->renewable === false)
                $c->{PJC::COL_ARNW} = false;
            if ($c->{PJC::COL_S_DT} && $c->{PJC::COL_E_DT} && $c->{PJC::COL_E_DT} < $c->{PJC::COL_S_DT})
                throw ValidationException::withMessages([
                    PJC::COL_E_DT => 'A data de término não pode ser anterior ao início.',
                ]);
            foreach (['title', 'subject', PJC::COL_CLIENT_NAME] as $f)
                if (isset($c->{$f}) && is_string($c->{$f})) $c->{$f} = trim($c->{$f});
            if ($c->type) {
                /** @var ContractType|null $t */
                $t = $c->contractType()->first();
                if ($t) self::applyTypeConstraints($c, $t);
            }
            $v = Validator::make($c->getAttributes(), [
                'currency' => ['nullable', 'string', 'max:8'],
                'value'    => ['nullable', 'regex:/^\d+(\.\d{1,2})?$/'], // compatível com coluna string
            ]);
            if ($v->fails()) throw new ValidationException($v);
        });
    }

    private static function applyTypeConstraints(self $c, ContractType $t): void
    {
        $val = $c->value !== null ? (float)$c->value : null;
        $minV = $t->{BC::COL_MIN_V} ?? null;
        $maxV = $t->{BC::COL_MAX_V} ?? null;

        if ($val !== null) {
            if ($minV !== null && $val < (float)$minV)
                throw ValidationException::withMessages([
                    'value' => "Valor abaixo do mínimo para o tipo selecionado (mínimo: " . number_format((float)$minV, 2, ',', '.') . ").",
                ]);
            if ($maxV !== null && (float)$maxV > 0 && $val > (float)$maxV)
                throw ValidationException::withMessages([
                    'value' => "Valor acima do máximo para o tipo selecionado (máximo: " . number_format((float)$maxV, 2, ',', '.') . ").",
                ]);
        }

        $start = self::toImmutable($c->{PJC::COL_S_DT});
        $end   = self::toImmutable($c->{PJC::COL_E_DT});
        if ($start && $end) {
            $months = $start->diffInMonths($end) ?: 0;

            $minM = $t->{BC::COL_MIN_M} ?? null;
            $maxM = $t->{BC::COL_MAX_M} ?? null;

            if ($minM !== null && $months < (int)$minM) {
                throw ValidationException::withMessages([
                    PJC::COL_E_DT => "Duração inferior ao mínimo para o tipo selecionado ({$minM} meses).",
                ]);
            }
            if ($maxM !== null && (int)$maxM > 0 && $months > (int)$maxM) {
                throw ValidationException::withMessages([
                    PJC::COL_E_DT => "Duração superior ao máximo para o tipo selecionado ({$maxM} meses).",
                ]);
            }
        }

        $termsCol = BC::COL_TC;
        if (empty($c->{PJC::COL_CDESC}) && !empty($t->{$termsCol}))
            $c->{PJC::COL_CDESC} = (string)$t->{$termsCol};
        $rngtCol = BC::COL_RNGT;
        if (isset($t->{$rngtCol})) {
            $isRenegotiable = (bool)$t->{$rngtCol};
            if (!$isRenegotiable) {
                $c->renewable = false;
                $c->{PJC::COL_ARNW} = false;
            }
        }

        // ? garantias do tipo (se o tipo exigir, não ajustamos aqui por falta de campos específicos;
        // ? validação documental pode ser feita na camada de caso de uso / serviço).
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
