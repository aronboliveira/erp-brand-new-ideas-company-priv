<?php

namespace App\Models;

use App\Config\Constants\{
    DatabaseConstants as DC,
    EmailsConstants as EC,
    MessagesConstants as MC
};
use App\Enums\AvailableLang;
use App\Traits\{
    HasAuditFields,
    NormalizesArrays,
    UsesUuids
};
use Illuminate\Database\Eloquent\{
    Model,
    Relations\BelongsTo
};
use Illuminate\Support\Facades\Log;

/**
 * @property string|null $from
 */
class EmailTemplateLang extends Model
{
    use UsesUuids;
    use HasAuditFields;
    use NormalizesArrays;

    /**
     * Traduções cacheadas por template+língua.
     *
     * @var array<string,self|null>
     */
    protected static array $cacheByParentAndLang = [];

    /**
     * Cache de contagem de traduções por template.
     *
     * @var array<string,int>
     */
    protected static array $countCacheByParent = [];

    protected $fillable = [
        EC::COL_PRT_ID,
        'lang',
        'subject',
        'content',
        'translator',
        MC::COL_TRL_ID,
        'variables',
        'metadata',
    ];

    protected $casts = [
        'id'            => 'string',
        EC::COL_PRT_ID  => 'string',
        'lang'          => AvailableLang::class,
        'subject'       => 'string',
        'content'       => 'string',
        'translator'    => 'string',
        MC::COL_TRL_ID  => 'string',
        'variables'     => 'array',
        'metadata'      => 'array',
        DC::COL_C_AT    => 'datetime',
        DC::COL_U_AT    => 'datetime',
    ];

    protected $with = [
        'template',
    ];

    protected $appends = [
        'lang_label',
        'has_translator_account',
        'has_variables',
        'has_metadata',
        'translations_count',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $model): void {
            try {
                $model->ensureJsonAttributesAreEncoded(['variables', 'metadata']);
            } catch (\Throwable $e) {
                Log::warning(static::class . ' failed to encode JSON attributes', [
                    'id'    => $model->getAttribute('id'),
                    'error' => $e->getMessage(),
                ]);
            }

            $langEnum = self::normalizeLangValue($model->getAttribute('lang'));
            $model->setAttribute('lang', $langEnum);
        });

        static::saved(function (self $model): void {
            $parentId = (string) ($model->getAttribute(EC::COL_PRT_ID) ?? '');
            if ($parentId === '') return;

            self::invalidateCacheForTemplate($parentId);
        });

        static::deleted(function (self $model): void {
            $parentId = (string) ($model->getAttribute(EC::COL_PRT_ID) ?? '');
            if ($parentId === '') return;

            self::invalidateCacheForTemplate($parentId);
        });
    }

    private static function normalizeLangValue(mixed $value): AvailableLang
    {
        if ($value instanceof AvailableLang) return $value;

        $raw = is_string($value) ? trim($value) : '';

        if ($raw !== '') {
            $enum = AvailableLang::tryFrom($raw);
            if ($enum instanceof AvailableLang) return $enum;
        }

        $default = AvailableLang::tryFrom(DC::DEFAULT_LANG);
        if ($default instanceof AvailableLang) return $default;

        $cases = AvailableLang::cases();

        return $cases[0];
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(EmailTemplate::class, EC::COL_PRT_ID, 'id');
    }

    public function getLangLabelAttribute(): ?string
    {
        $lang = $this->getAttribute('lang');

        if ($lang instanceof AvailableLang)
            $value = $lang->value;
        elseif (is_string($lang))
            $value = $lang;
        else
            return null;

        $normalized = str_replace('_', '-', strtolower(trim($value)));

        return strtoupper($normalized);
    }

    public function getHasTranslatorAccountAttribute(): bool
    {
        $translatorId = $this->getAttribute(MC::COL_TRL_ID);

        if (!is_string($translatorId)) return false;

        $trimmed = trim($translatorId);

        if ($trimmed === '' || $trimmed === DC::DEFAULT_UUID) return false;

        return true;
    }

    public function getHasVariablesAttribute(): bool
    {
        $variables = $this->getAttribute('variables');

        return is_array($variables) && $variables !== [];
    }

    public function getHasMetadataAttribute(): bool
    {
        $metadata = $this->getAttribute('metadata');

        return is_array($metadata) && $metadata !== [];
    }

    public function getTranslationsCountAttribute(): int
    {
        $parentId = (string) ($this->getAttribute(EC::COL_PRT_ID) ?? '');

        if ($parentId === '') return 0;

        return self::countTranslationsForTemplate($parentId);
    }

    public static function forTemplateAndLang(
        string $parentId,
        AvailableLang|string|null $lang = null
    ): ?self {
        $parentId = trim($parentId);

        if ($parentId === '') return null;

        $langEnum = $lang instanceof AvailableLang
            ? $lang
            : self::normalizeLangValue($lang);

        $key = $parentId . ':' . $langEnum->value;

        if (array_key_exists($key, self::$cacheByParentAndLang))
            return self::$cacheByParentAndLang[$key];

        try {
            $row = self::query()
                ->where(EC::COL_PRT_ID, $parentId)
                ->where('lang', $langEnum->value)
                ->first();
        } catch (\Throwable $e) {
            Log::error(static::class . ' failed to load translation', [
                EC::COL_PRT_ID => $parentId,
                'lang'      => $langEnum->value,
                'error'     => $e->getMessage(),
            ]);
            return null;
        }

        self::$cacheByParentAndLang[$key] = $row;

        return $row;
    }

    public static function countTranslationsForTemplate(string $parentId): int
    {
        $parentId = trim($parentId);

        if ($parentId === '') return 0;

        if (array_key_exists($parentId, self::$countCacheByParent))
            return self::$countCacheByParent[$parentId];

        try {
            $count = (int) self::query()
                ->where(EC::COL_PRT_ID, $parentId)
                ->count();
        } catch (\Throwable $e) {
            Log::error(static::class . ' failed to count translations', [
                EC::COL_PRT_ID => $parentId,
                'error'     => $e->getMessage(),
            ]);
            return 0;
        }

        self::$countCacheByParent[$parentId] = $count;

        return $count;
    }

    public static function invalidateCacheForTemplate(string $parentId): void
    {
        $parentId = trim($parentId);

        if ($parentId === '') return;

        foreach (array_keys(self::$cacheByParentAndLang) as $key)
            if (str_starts_with($key, $parentId . ':'))
                unset(self::$cacheByParentAndLang[$key]);

        unset(self::$countCacheByParent[$parentId]);
    }
}
