<?php

namespace App\Models;

use App\Config\Constants\{
    DatabaseConstants as DC,
    EmailsConstants as EC,
    ProjectsConstants as PC,
    UsersConstants as UC
};
use App\Models\{
    EmailTemplate,
    User
};
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
 * @property bool|null $is_active
 */

class UserEmailTemplate extends Model
{
    use UsesUuids;
    use HasAuditFields;
    use NormalizesArrays;

    protected $table = DC::TABLE_USER_EML_TMPS;

    /**
     * Cache de template padrão por usuário.
     *
     * @var array<string,self|null>
     */
    protected static array $defaultCacheByUser = [];

    /**
     * Cache de templates favoritos por usuário.
     *
     * @var array<string,array<int,self>>
     */
    protected static array $favoritesCacheByUser = [];

    /**
     * Cache de uso (counter) por usuário+template.
     *
     * @var array<string,int>
     */
    protected static array $usageCacheByUserAndTemplate = [];

    protected $fillable = [
        EC::COL_TMP,
        UC::COL_USER_ID,
        UC::COL_IA,
        'counter',
        PC::COL_IS_FV,
        DC::COL_IS_DEF,
        'clients',
    ];

    protected $casts = [
        'id'             => 'string',
        EC::COL_TMP      => 'string',
        UC::COL_USER_ID  => 'string',
        UC::COL_IA       => 'bool',
        'counter'        => 'int',
        PC::COL_IS_FV    => 'bool',
        DC::COL_IS_DEF   => 'bool',
        'clients'        => 'array',
        DC::COL_C_AT     => 'datetime',
        DC::COL_U_AT     => 'datetime',
    ];

    protected $appends = [
        'clients_list',
        'clients_count',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $model): void {
            try {
                $model->ensureJsonAttributesAreEncoded(['clients']);
            } catch (\Throwable $e) {
                Log::warning(static::class . ' failed to encode JSON attributes', [
                    'id'    => $model->getAttribute('id'),
                    'error' => $e->getMessage(),
                ]);
            }

            $isActive = (bool) $model->getAttribute(UC::COL_IA);
            $model->setAttribute(UC::COL_IA, $isActive);

            $counter = (int) ($model->getAttribute('counter') ?? 0);
            if ($counter < 0) $counter = 0;
            $model->setAttribute('counter', $counter);

            $isFavorite = (bool) $model->getAttribute(PC::COL_IS_FV);
            $model->setAttribute(PC::COL_IS_FV, $isFavorite);

            $isDefault = (bool) $model->getAttribute(DC::COL_IS_DEF);
            $model->setAttribute(DC::COL_IS_DEF, $isDefault);
        });

        static::saved(fn(self $model) => self::invalidateCachesForModel($model));
        static::deleted(fn(self $model) => self::invalidateCachesForModel($model));
    }

    protected static function invalidateCachesForModel(self $model): void
    {
        $userId     = (string) ($model->getAttribute(UC::COL_USER_ID) ?? '');
        $templateId = (string) ($model->getAttribute(EC::COL_TMP) ?? '');

        if ($userId === '') return;

        unset(self::$defaultCacheByUser[$userId], self::$favoritesCacheByUser[$userId]);

        if ($templateId === '') return;

        $key = $userId . ':' . $templateId;
        unset(self::$usageCacheByUserAndTemplate[$key]);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(EmailTemplate::class, EC::COL_TMP, 'id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, UC::COL_USER_ID, 'id');
    }

    public function setClientsAttribute(mixed $value): void
    {
        $encoded = self::encodeJsonValue($value, 'clients');
        $this->setAttribute('clients', $encoded);
    }

    public function getClientsListAttribute(): array
    {
        $raw = $this->getAttribute('clients');

        return self::normalizeArrayField($raw);
    }

    public function getClientsCountAttribute(): int
    {
        return count($this->getClientsListAttribute());
    }

    public function scopeActive($query)
    {
        return $query->where(UC::COL_IA, true);
    }

    public function scopeFavorites($query)
    {
        return $query->where(PC::COL_IS_FV, true);
    }

    public function scopeDefaults($query)
    {
        return $query->where(DC::COL_IS_DEF, true);
    }

    public static function getDefaultForUser(string|int $userId): ?self
    {
        $userKey = (string) $userId;
        if ($userKey === '') return null;

        if (array_key_exists($userKey, self::$defaultCacheByUser))
            return self::$defaultCacheByUser[$userKey];

        try {
            $record = self::query()
                ->where(UC::COL_USER_ID, $userKey)
                ->where(UC::COL_IA, true)
                ->where(DC::COL_IS_DEF, true)
                ->orderByDesc(DC::COL_C_AT)
                ->first();
        } catch (\Throwable $e) {
            Log::error(static::class . ' failed to load default user email template', [
                'user_id' => $userKey,
                'error'   => $e->getMessage(),
            ]);
            return null;
        }

        self::$defaultCacheByUser[$userKey] = $record;

        return $record;
    }

    /**
     * Retorna templates favoritos de um usuário (apenas ativos), com cache em memória.
     *
     * @return array<int,self>
     */
    public static function getFavoritesForUser(string|int $userId): array
    {
        $userKey = (string) $userId;
        if ($userKey === '') return [];

        if (array_key_exists($userKey, self::$favoritesCacheByUser))
            return self::$favoritesCacheByUser[$userKey];

        try {
            $rows = self::query()
                ->where(UC::COL_USER_ID, $userKey)
                ->where(UC::COL_IA, true)
                ->where(PC::COL_IS_FV, true)
                ->orderByDesc(DC::COL_C_AT)
                ->get()
                ->all();
        } catch (\Throwable $e) {
            Log::error(static::class . ' failed to load favorite user email templates', [
                'user_id' => $userKey,
                'error'   => $e->getMessage(),
            ]);
            return [];
        }

        self::$favoritesCacheByUser[$userKey] = $rows;

        return $rows;
    }

    /**
     * Retorna o contador de uso para um par user/template, com cache.
     */
    public static function usageCountForUserAndTemplate(
        string|int $userId,
        string $templateId
    ): int {
        $userKey     = (string) $userId;
        $templateKey = trim($templateId);

        if ($userKey === '' || $templateKey === '') return 0;

        $cacheKey = $userKey . ':' . $templateKey;

        if (array_key_exists($cacheKey, self::$usageCacheByUserAndTemplate))
            return self::$usageCacheByUserAndTemplate[$cacheKey];

        try {
            $sum = (int) self::query()
                ->where(UC::COL_USER_ID, $userKey)
                ->where(EC::COL_TMP, $templateKey)
                ->sum('counter');
        } catch (\Throwable $e) {
            Log::error(static::class . ' failed to compute usageCountForUserAndTemplate', [
                'user_id'     => $userKey,
                'template_id' => $templateKey,
                'error'       => $e->getMessage(),
            ]);
            return 0;
        }

        if ($sum < 0) $sum = 0;

        self::$usageCacheByUserAndTemplate[$cacheKey] = $sum;

        return $sum;
    }

    /**
     * Incrementa o contador de uso de forma segura.
     */
    public function incrementUsage(int $by = 1): void
    {
        $step = $by > 0 ? $by : 1;

        try {
            $this->increment('counter', $step);
            $this->refresh();

            $userId     = (string) ($this->getAttribute(UC::COL_USER_ID) ?? '');
            $templateId = (string) ($this->getAttribute(EC::COL_TMP) ?? '');
            if ($userId !== '' && $templateId !== '') {
                $key = $userId . ':' . $templateId;
                self::$usageCacheByUserAndTemplate[$key] =
                    (int) ($this->getAttribute('counter') ?? 0);
            }
        } catch (\Throwable $e) {
            Log::error(static::class . ' failed to increment usage', [
                'id'      => $this->getAttribute('id'),
                'step'    => $step,
                'error'   => $e->getMessage(),
            ]);
        }
    }
}
