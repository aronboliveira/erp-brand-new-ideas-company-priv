<?php

namespace App\Models;

use App\Config\Constants\{
    ActivitiesConstants as AC,
    DatabaseConstants as DC,
    EmailsConstants as EC,
    MessagesConstants as MC,
};
use App\Enums\EmailTemplateType;
use App\Services\EmailRequestService;
use App\Traits\{HasAuditFields, NormalizesAddresses, UsesUuids};
use Illuminate\Database\Eloquent\{
    Model,
    Relations\BelongsTo,
    Relations\HasOne,
    Relations\HasMany
};
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

use Illuminate\Database\Eloquent\Factories\HasFactory;
/**
 * @property string|null $from

 * @property mixed $template
 */
class EmailTemplate extends Model
{
    use HasFactory;
    use HasAuditFields, NormalizesAddresses, UsesUuids;

    protected $table = DC::TABLE_EMAIL_TEMPLATES;

    /**
     * Cache simples de um template padrão.
     */
    protected static ?self $templateData = null;

    protected $fillable = [
        EC::COL_TT,          // title
        EC::COL_FROM,        // from
        EC::COL_SLG,         // slug
        'description',
        'notification',
        'type',              // EmailTemplateType
        AC::COL_AV_FROM,     // available_from
        AC::COL_DSB,         // is_disabled
        'categories',
        MC::COL_EX_PLN,      // excluded_plans
        'rules',
        MC::COL_AV_LG,       // available_languages
        'variables',
        'settings',
        'tags',
        DC::COL_PLT_AV,      // platforms_available
        DC::COL_TABLE_CREATOR,
        DC::COL_TABLE_UPDATER,
    ];

    protected $casts = [
        'id'                 => 'string',

        EC::COL_TT           => 'string',
        EC::COL_FROM         => 'string',
        EC::COL_SLG          => 'string',
        'description'        => 'string',
        'notification'       => 'string',

        'type'               => EmailTemplateType::class,

        AC::COL_AV_FROM      => 'datetime',
        AC::COL_DSB          => 'bool',

        'categories'         => 'array',
        MC::COL_EX_PLN       => 'array',
        'rules'              => 'array',
        MC::COL_AV_LG        => 'array',
        'variables'          => 'array',
        'settings'           => 'array',
        'tags'               => 'array',
        DC::COL_PLT_AV       => 'array',

        DC::COL_C_AT         => 'datetime',
        DC::COL_U_AT         => 'datetime',
    ];

    /**
     * Atributos derivados expostos no JSON.
     */
    protected $appends = [
        'is_active',
        'is_global',
        'type_label',
        'type_category',
        'type_icon',
        'type_is_transactional',
        'type_is_marketing',
        'type_is_automated',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $model): void {
            $rawSlug  = $model->getAttribute(EC::COL_SLG);
            $baseSlug = empty($rawSlug) && $model->getAttribute(EC::COL_TT)
                ? Str::slug((string) $model->getAttribute(EC::COL_TT))
                : Str::slug((string) $rawSlug);

            $acc = 0;
            do {
                $candidateSlug = $acc === 0
                    ? Str::limit($baseSlug, 254)
                    : Str::limit($baseSlug . '-' . Str::random(8), 254);
                $acc++;
            } while (
                DB::table($model->getTable())
                ->where(EC::COL_SLG, $candidateSlug)
                ->where('id', '!=', $model->getAttribute('id') ?? '')
                ->exists()
                && $acc < 256
            );
            if ($acc >= 256)
                throw new \RuntimeException(
                    'Failed to generate unique slug for EmailTemplate after 256 attempts'
                );
            $model->setAttribute(EC::COL_SLG, $candidateSlug);
            if (!$model->getAttribute(AC::COL_AV_FROM))
                $model->setAttribute(AC::COL_AV_FROM, now());
            if ($model->getAttribute(AC::COL_DSB) === null)
                $model->setAttribute(AC::COL_DSB, false);
            if (!$model->getAttribute(MC::COL_AV_LG))
                $model->setAttribute(MC::COL_AV_LG, [DC::DEFAULT_LANG]);
            $from = $model->getAttribute(EC::COL_FROM);
            if ($from && preg_match('/.+@.+\..+/', $from))
                $model->setAttribute(EC::COL_FROM, self::normalizeEmail($from));
            foreach (['categories', MC::COL_EX_PLN, 'rules', 'tags', MC::COL_AV_LG, 'variables', 'settings', DC::COL_PLT_AV] as $attr) {
                $value = $model->getAttribute($attr);
                if (!is_array($value)) {
                    $model->setAttribute($attr, []);
                    continue;
                }
                $normalized = [];
                foreach ($value as $item) {
                    if (!is_scalar($item)) continue;
                    $trimmed = trim((string) $item);
                    if ($trimmed !== '') $normalized[] = $trimmed;
                }
                $model->setAttribute($attr, array_values(array_unique($normalized)));
            }
        });

        static::updating(function (self $model): void {
            if (
                $model->isDirty(EC::COL_TT)
                && !$model->getAttribute(EC::COL_SLG)
                && $model->getAttribute(EC::COL_TT)
            )
                $model->setAttribute(
                    EC::COL_SLG,
                    Str::slug((string) $model->getAttribute(EC::COL_TT))
                );
        });

        static::saving(function (self $model): void {
            $rawType = $model->getAttribute('type');
            if ($rawType === null) {
                $model->setAttribute('type', EmailTemplateType::normalize(null));
                return;
            }
            if (!$rawType instanceof EmailTemplateType)
                $model->setAttribute('type', EmailTemplateType::normalize((string) $rawType));
        });
    }

    public static function emailTemplateData(): ?self
    {
        if (self::$templateData instanceof self)
            return self::$templateData;
        /** @var self|null $template */
        $template = app(EmailRequestService::class)
            ->getAvailableTemplates()
            ->sortBy(DC::COL_C_AT)
            ->first();
        self::$templateData = $template;
        return self::$templateData;
    }

    /**
     * Recupera um template padrão para um tipo específico.
     */
    public static function defaultForType(EmailTemplateType|string|null $type): ?self
    {
        /** @var self|null */
        return app(EmailRequestService::class)->getTemplatesByType($type)
            ->sortBy(DC::COL_C_AT)
            ->first();
    }

    public function notificationTemplate(): BelongsTo
    {
        return $this->belongsTo(
            NotificationTemplate::class,
            'notification',
            'id'
        );
    }

    /**
     * Template específico do usuário (mantém assinatura original).
     */
    public function template(): HasOne
    {
        return app(EmailRequestService::class)->getUserTemplate($this);
    }

    public function userTemplate(): HasOne
    {
        return $this->template();
    }

    public function templates(): HasMany
    {
        return $this->hasMany(UserEmailTemplate::class, EC::COL_TMP, 'id');
    }

    public function userTemplates(): HasMany
    {
        return $this->templates();
    }

    public function getIsActiveAttribute(): bool
    {
        $availableFrom = $this->getAttribute(AC::COL_AV_FROM);
        $disabled = (bool) $this->getAttribute(AC::COL_DSB);

        if (! $availableFrom) {
            return ! $disabled;
        }

        return ! $disabled && $availableFrom <= now();
    }

    public function getIsGlobalAttribute(): bool
    {
        $categories = $this->getAttribute('categories') ?? [];
        return empty($categories);
    }

    /**
     * Rótulo legível do tipo (localizado quando possível).
     */
    public function getTypeLabelAttribute(): ?string
    {
        $type = $this->getAttribute('type');

        if (! $type instanceof EmailTemplateType) {
            return null;
        }

        $lang = config('app.locale', DC::DEFAULT_LANG);
        $labels = EmailTemplateType::labels($lang);

        return $labels[$type->value] ?? $type->value;
    }

    public function getTypeCategoryAttribute(): ?string
    {
        $type = $this->getAttribute('type');

        return $type instanceof EmailTemplateType
            ? $type->getCategory()
            : null;
    }

    public function getTypeIconAttribute(): ?string
    {
        $type = $this->getAttribute('type');

        return $type instanceof EmailTemplateType
            ? $type->getIcon()
            : null;
    }

    public function getTypeIsTransactionalAttribute(): ?bool
    {
        $type = $this->getAttribute('type');

        return $type instanceof EmailTemplateType
            ? $type->isTransactional()
            : null;
    }

    public function getTypeIsMarketingAttribute(): ?bool
    {
        $type = $this->getAttribute('type');

        return $type instanceof EmailTemplateType
            ? $type->isMarketing()
            : null;
    }

    public function getTypeIsAutomatedAttribute(): ?bool
    {
        $type = $this->getAttribute('type');

        return $type instanceof EmailTemplateType
            ? $type->isAutomated()
            : null;
    }

    public function scopeAvailable($query)
    {
        return $query
            ->where(AC::COL_DSB, false)
            ->where(function ($q) {
                $q->whereNull(AC::COL_AV_FROM)
                    ->orWhere(AC::COL_AV_FROM, '<=', now());
            });
    }

    public function scopeForNotification($query, string $notificationId)
    {
        return $query->where('notification', $notificationId);
    }

    public function scopeForPlatform($query, string $platform)
    {
        return $query->whereJsonContains(DC::COL_PLT_AV, $platform);
    }

    public function scopeOfType($query, EmailTemplateType|string|null $type)
    {
        $enum = $type instanceof EmailTemplateType
            ? $type
            : EmailTemplateType::normalize($type);

        return $query->where('type', $enum->value);
    }

    public function scopeTransactional($query)
    {
        $values = [];
        foreach (EmailTemplateType::cases() as $case) {
            if ($case->isTransactional()) {
                $values[] = $case->value;
            }
        }

        return $query->whereIn('type', $values);
    }

    public function scopeMarketing($query)
    {
        $values = [];
        foreach (EmailTemplateType::cases() as $case) {
            if ($case->isMarketing()) {
                $values[] = $case->value;
            }
        }

        return $query->whereIn('type', $values);
    }

    public function scopeAutomated($query)
    {
        $values = [];
        foreach (EmailTemplateType::cases() as $case) {
            if ($case->isAutomated()) {
                $values[] = $case->value;
            }
        }

        return $query->whereIn('type', $values);
    }

    /**
     * Remetente efetivo (considerando fallback de config).
     */
    public function getEffectiveFrom(): ?string
    {
        $from = $this->getAttribute(EC::COL_FROM);

        if ($from) {
            return $from;
        }

        return config('mail.from.address') ?? null;
    }

    /**
     * Expondo algumas capacidades do enum de tipo direto no modelo
     * (útil em services/Controllers sem precisar conhecer o enum inteiro).
     */
    public function expectedMetrics(): ?array
    {
        $type = $this->getAttribute('type');

        return $type instanceof EmailTemplateType
            ? $type->getExpectedMetrics()
            : null;
    }

    public function gdprSensitive(): ?bool
    {
        $type = $this->getAttribute('type');

        return $type instanceof EmailTemplateType
            ? $type->isGdprSensitive()
            : null;
    }

    public function abTestSuggestions(): ?array
    {
        $type = $this->getAttribute('type');

        return $type instanceof EmailTemplateType
            ? $type->getAbTestSuggestions()
            : null;
    }

    public function recommendedFrequency(): ?string
    {
        $type = $this->getAttribute('type');

        return $type instanceof EmailTemplateType
            ? $type->getFrequency()
            : null;
    }
}
