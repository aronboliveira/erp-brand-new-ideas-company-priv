<?php

namespace App\Models;

use App\Config\Constants\{
    DatabaseConstants as DC,
    MessagesConstants as MC,
    NotificationsConstants as NC
};
use App\Enums\AvailableLang;
use App\Traits\{
    HasAuditFields,
    NormalizesArrays,
    UsesUuids
};
use Illuminate\Database\Eloquent\{
    Builder,
    Factories\HasFactory,
    Model,
    Relations\BelongsTo
};
use Illuminate\Support\Facades\Log;

class NotificationTemplateLang extends Model
{
    use HasAuditFields;
    use HasFactory;
    use UsesUuids;
    use NormalizesArrays;

    protected $table = DC::TABLE_NOTIFICATION_TEMPLATE_LANGS;
    protected $fillable = [
        NC::COL_TEMPL_PR,
        NC::COL_TEMPL_LG,
        NC::COL_TEMPL_CT,
        NC::COL_TEMPL_VARS,
        'translator',
        MC::COL_TRL_ID,
        'metadata',
    ];
    protected $guarded = [
        'id',
        DC::COL_TABLE_CREATOR,
    ];
    public $timestamps = false;
    protected $casts = [
        NC::COL_TEMPL_VARS => 'array',
        'metadata'         => 'array',
    ];
    protected $with = [
        'template',
        'translatorUser',
    ];
    protected $appends = [
        'lang_enum',
        'lang_label',
        'variables_keys',
    ];
    protected static array $cacheByTemplateLang = [];

    public static function booted(): void
    {
        static::saving(function (self $model): void {
            try {
                $rawLang  = $model->getAttribute(NC::COL_TEMPL_LG);
                $langEnum = AvailableLang::normalize($rawLang);
                $model->setAttribute(NC::COL_TEMPL_LG, $langEnum->value);

                $vars = self::normalizeArrayField($model->getAttribute(NC::COL_TEMPL_VARS));
                $model->setAttribute(NC::COL_TEMPL_VARS, $vars);

                $meta = self::normalizeArrayField($model->getAttribute('metadata'));
                $model->setAttribute('metadata', $meta);

                $translator = trim((string) ($model->getAttribute('translator') ?? ''));
                if ($translator === '' || $translator === DC::DEFAULT_UUID) {
                    $model->setAttribute('translator', null);
                }

                $translatorId = $model->getAttribute(MC::COL_TRL_ID);
                if ($translatorId !== null && !is_string($translatorId)) {
                    $model->setAttribute(MC::COL_TRL_ID, (string) $translatorId);
                }
            } catch (\Throwable $e) {
                Log::warning(self::class . ' failed while normalizing on saving', [
                    'id'    => $model->getAttribute('id'),
                    'error' => $e->getMessage(),
                ]);
            }
        });
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(NotificationTemplate::class, NC::COL_TEMPL_PR, 'id');
    }

    public function translatorUser(): BelongsTo
    {
        return $this->belongsTo(User::class, MC::COL_TRL_ID, 'id');
    }

    public function scopeForTemplate(Builder $query, string $templateId): Builder
    {
        return $query->where(NC::COL_TEMPL_PR, $templateId);
    }

    public function scopeForLang(Builder $query, string|AvailableLang $lang): Builder
    {
        $enum = AvailableLang::normalize($lang);
        return $query->where(NC::COL_TEMPL_LG, $enum->value);
    }

    public function getLangEnumAttribute(): AvailableLang
    {
        return AvailableLang::normalize($this->getAttribute(NC::COL_TEMPL_LG));
    }

    public function getLangLabelAttribute(): string
    {
        $enum   = $this->getLangEnumAttribute();
        $labels = AvailableLang::labels();
        return $labels[$enum->value] ?? $enum->value;
    }

    public function getVariablesKeysAttribute(): array
    {
        $vars = $this->getAttribute(NC::COL_TEMPL_VARS);

        if (!is_array($vars)) {
            $vars = self::normalizeArrayField($vars);
        }

        if (!$vars) {
            return [];
        }

        return array_values(array_unique(array_keys((array) $vars)));
    }

    public static function cachedForTemplateAndLang(
        string $templateId,
        string|AvailableLang|null $lang
    ): ?self {
        $enum = AvailableLang::normalize($lang);
        $key  = $templateId . '|' . $enum->value;

        if (\array_key_exists($key, self::$cacheByTemplateLang)) {
            return self::$cacheByTemplateLang[$key];
        }

        try {
            $record = static::query()
                ->forTemplate($templateId)
                ->forLang($enum)
                ->first();

            self::$cacheByTemplateLang[$key] = $record;
            return $record;
        } catch (\Throwable $e) {
            Log::warning(self::class . ' failed to load cachedForTemplateAndLang', [
                'template_id' => $templateId,
                'lang'        => $enum->value,
                'error'       => $e->getMessage(),
            ]);

            self::$cacheByTemplateLang[$key] = null;
            return null;
        }
    }

    public static function aggregateByLang(?string $templateId = null): array
    {
        try {
            $query = static::query()
                ->selectRaw(NC::COL_TEMPL_LG . ' as lang, COUNT(*) as aggregate_count')
                ->groupBy(NC::COL_TEMPL_LG);

            if ($templateId) {
                $query->forTemplate($templateId);
            }

            $rows   = $query->get();
            $result = [];

            foreach ($rows as $row) {
                $langValue = (string) ($row->lang ?? '');
                $result[$langValue] = (int) ($row->aggregate_count ?? 0);
            }

            return $result;
        } catch (\Throwable $e) {
            Log::warning(self::class . ' failed to aggregateByLang', [
                'template_id' => $templateId,
                'error'       => $e->getMessage(),
            ]);

            return [];
        }
    }
}
