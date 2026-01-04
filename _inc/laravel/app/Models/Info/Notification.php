<?php

namespace App\Models;

use App\Config\Constants\{
    DatabaseConstants as DC,
    MessagesConstants as MC,
    UsersConstants as UC
};
use App\Enums\{MessagingPlatform, NotificationTemplateType};
use App\Models\User;
use App\Traits\{FiltersSecureAttachments, HasAuditFields, NormalizesArrays, UsesUuids};
use Illuminate\Database\Eloquent\{Builder, Factories\HasFactory, Model, Relations\BelongsTo};
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\{Cache, Log};

class Notification extends Model
{
    use UsesUuids, HasAuditFields, HasFactory, NormalizesArrays, FiltersSecureAttachments;

    protected $table = DC::TABLE_NTF;

    /**
     * @var array<int,string>
     */
    protected $fillable = [
        UC::COL_USER_ID,
        'type',
        'data',
        'attachments',
        'metadata',
        'tags',
        'platforms',
        MC::COL_SNT_AT,
        MC::COL_SNT_BY,
        MC::COL_IS_RD,
        MC::COL_RD_AT,
    ];

    /**
     * @var array<int,string>
     */
    protected $guarded = [
        'id',
        DC::COL_TABLE_CREATOR,
    ];

    /**
     * @var array<string,string>
     */
    protected $casts = [
        'attachments'      => 'array',
        'metadata'         => 'array',
        'tags'             => 'array',
        'platforms'        => 'array',
        MC::COL_SNT_AT     => 'datetime',
        MC::COL_RD_AT      => 'datetime',
        MC::COL_IS_RD      => 'boolean',
        DC::COL_C_AT       => 'datetime',
        DC::COL_U_AT       => 'datetime',
    ];

    /**
     * @var array<int,string>
     */
    protected $with = [
        'user',
    ];

    /**
     * @var array<int,string>
     */
    protected $appends = [
        'sent_at_human',
        'type_enum',
        'is_read_flag',
        'platforms_enum',
        'payload',
    ];

    public static function booted(): void
    {
        static::saving(function (self $model): void {
            try {
                $userId = $model->getAttribute(UC::COL_USER_ID);
                if ($userId !== null) {
                    $userId = trim((string) $userId);
                    if ($userId === '')
                        $userId = null;
                }
                $model->setAttribute(UC::COL_USER_ID, $userId);
                $sentAt = $model->getAttribute(MC::COL_SNT_AT);
                if (!$sentAt instanceof Carbon) {
                    if ($sentAt) {
                        try {
                            $sentAt = Carbon::parse((string) $sentAt);
                        } catch (\Throwable $e) {
                            Log::debug(static::class . ' invalid sent_at, using now()', [
                                'error' => $e->getMessage(),
                                'value' => $sentAt,
                            ]);
                            $sentAt = Carbon::now();
                        }
                    } else {
                        $sentAt = Carbon::now();
                    }
                }
                $model->setAttribute(MC::COL_SNT_AT, $sentAt);
                $sentBy = $model->getAttribute(MC::COL_SNT_BY);
                $sentBy = $sentBy !== null ? trim((string) $sentBy) : '';
                if ($sentBy === '')
                    $sentBy = DC::DEFAULT_UUID;
                $model->setAttribute(MC::COL_SNT_BY, $sentBy);
                $readAt    = $model->getAttribute(MC::COL_RD_AT);
                $isReadRaw = $model->getAttribute(MC::COL_IS_RD);
                $isRead    = (int) ($isReadRaw ?? 0) > 0 ? 1 : 0;
                if ($readAt) {
                    try {
                        if (!$readAt instanceof Carbon)
                            $readAt = Carbon::parse((string) $readAt);
                        $isRead = 1;
                    } catch (\Throwable $e) {
                        Log::debug(static::class . ' invalid read_at, nulling value', [
                            'error'   => $e->getMessage(),
                            'read_at' => $readAt,
                        ]);
                        $readAt = null;
                    }
                }

                if (($isRead === 1 || $isRead === true) && $readAt === null)
                    $readAt = Carbon::now();
                $model->setAttribute(MC::COL_IS_RD, $isRead);
                $model->setAttribute(MC::COL_RD_AT, $readAt);
                $attachments = self::normalizeArrayField($model->getAttribute('attachments'));
                $metadata    = self::normalizeArrayField($model->getAttribute('metadata'));
                $tags        = self::normalizeArrayField($model->getAttribute('tags'));
                $platforms   = self::normalizeArrayField($model->getAttribute('platforms'));
                $platformsNorm = [];
                foreach ($platforms as $value) {
                    if (!is_string($value) || trim($value) === '')
                        continue;
                    $platformEnum    = MessagingPlatform::normalize($value);
                    $platformsNorm[] = $platformEnum->value;
                }
                $template = null;
                $rules    = null;
                $typeSlug = trim((string) ($model->getAttribute('type') ?? ''));
                if ($typeSlug !== '' && class_exists(\App\Models\NotificationTemplate::class)) {
                    try {
                        /** @var \App\Models\NotificationTemplate |null $template */
                        $template = \App\Models\NotificationTemplate::query()
                            ->where('name', $typeSlug)
                            ->orWhere('slug', $typeSlug)
                            ->first();

                        if ($template) {
                            $rawRules = $template->getAttribute('rules');

                            if (is_array($rawRules))
                                $rules = $rawRules;
                            elseif (is_string($rawRules)) {
                                $decoded = json_decode($rawRules, true);
                                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded))
                                    $rules = $decoded;
                            }
                        }
                    } catch (\Throwable $e) {
                        Log::warning(static::class . ' failed to resolve notification template rules', [
                            'type'  => $typeSlug,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
                if (is_array($rules) && $rules !== []) {
                    if (isset($rules['attachments']) && is_array($rules['attachments'])) {
                        $attachmentRules = $rules['attachments'];
                        $required = !empty($attachmentRules['required']);
                        if ($required && count($attachments) === 0)
                            Log::warning(static::class . ' attachments required by template rules but empty', [
                                'notification_id' => $model->getAttribute('id'),
                                'type'            => $typeSlug,
                            ]);
                        if (isset($attachmentRules['max_count']) && is_numeric($attachmentRules['max_count'])) {
                            $maxCount = (int) $attachmentRules['max_count'];
                            if ($maxCount > 0 && count($attachments) > $maxCount) {
                                $trimmed = [];
                                $count   = 0;
                                foreach ($attachments as $item) {
                                    $trimmed[] = $item;
                                    $count++;
                                    if ($count >= $maxCount)
                                        break;
                                }
                                Log::debug(static::class . ' attachments trimmed by max_count rule', [
                                    'notification_id' => $model->getAttribute('id'),
                                    'type'            => $typeSlug,
                                    'max_count'       => $maxCount,
                                ]);
                                $attachments = $trimmed;
                            }
                        }

                        if (isset($attachmentRules['accepts']) && is_array($attachmentRules['accepts'])) {
                            $acceptList = [];
                            foreach ($attachmentRules['accepts'] as $pattern) {
                                if (!is_string($pattern))
                                    continue;
                                $pattern = strtolower(trim($pattern));
                                if ($pattern === '')
                                    continue;
                                $acceptList[] = $pattern;
                            }

                            if ($acceptList) {
                                $filtered = [];
                                foreach ($attachments as $item) {
                                    $mime = null;
                                    if (is_array($item)) {
                                        foreach (['mime', 'mimetype', 'type', 'content_type'] as $k) {
                                            if (isset($item[$k]) && is_string($item[$k])) {
                                                $candidate = strtolower(trim($item[$k]));
                                                if ($candidate !== '') {
                                                    $mime = $candidate;
                                                    break;
                                                }
                                            }
                                        }
                                    } elseif (is_string($item)) {
                                        $candidate = strtolower(trim($item));
                                        if (strpos($candidate, '/') !== false)
                                            $mime = $candidate;
                                    }
                                    if ($mime === null) {
                                        $filtered[] = $item;
                                        continue;
                                    }
                                    $allowed = false;
                                    foreach ($acceptList as $pattern) {
                                        if ($pattern === '*' || $pattern === '*/*') {
                                            $allowed = true;
                                            break;
                                        }
                                        if ($mime === $pattern) {
                                            $allowed = true;
                                            break;
                                        }
                                        $slashPos = strpos($pattern, '/');
                                        if ($slashPos !== false && substr($pattern, -2) === '/*') {
                                            $mainType = substr($pattern, 0, $slashPos);
                                            if (strpos($mime, $mainType . '/') === 0) {
                                                $allowed = true;
                                                break;
                                            }
                                        }
                                    }

                                    if ($allowed) {
                                        $filtered[] = $item;
                                    } else {
                                        Log::debug(static::class . ' attachment rejected by accepts rule', [
                                            'notification_id' => $model->getAttribute('id'),
                                            'type'            => $typeSlug,
                                            'mime'            => $mime,
                                            'accepts'         => $acceptList,
                                        ]);
                                    }
                                }
                                $attachments = $filtered;
                            }
                        }

                        $maxTotal = null;
                        if (isset($attachmentRules['max_total_size']) && is_numeric($attachmentRules['max_total_size']))
                            $maxTotal = (float) $attachmentRules['max_total_size'];
                        $maxEach = null;
                        if (isset($attachmentRules['max_each_size']) && is_numeric($attachmentRules['max_each_size']))
                            $maxEach = (float) $attachmentRules['max_each_size'];
                        if ($maxTotal !== null || $maxEach !== null) {
                            $total    = 0.0;
                            $filtered = [];
                            foreach ($attachments as $item) {
                                $size = null;
                                if (is_array($item)) {
                                    foreach (['size', 'filesize', 'bytes', 'length'] as $k) {
                                        if (isset($item[$k]) && (is_int($item[$k]) || is_float($item[$k]) || ctype_digit((string) $item[$k]))) {
                                            $size = (float) $item[$k];
                                            break;
                                        }
                                    }
                                }

                                if ($size === null) {
                                    $filtered[] = $item;
                                    continue;
                                }

                                if ($maxEach !== null && $size > $maxEach) {
                                    Log::debug(static::class . ' attachment rejected by max_each_size rule', [
                                        'notification_id' => $model->getAttribute('id'),
                                        'type'            => $typeSlug,
                                        'size'            => $size,
                                        'max_each_size'   => $maxEach,
                                    ]);
                                    continue;
                                }

                                if ($maxTotal !== null && $total + $size > $maxTotal) {
                                    Log::debug(static::class . ' attachment skipped by max_total_size rule', [
                                        'notification_id' => $model->getAttribute('id'),
                                        'type'            => $typeSlug,
                                        'size'            => $size,
                                        'current_total'   => $total,
                                        'max_total_size'  => $maxTotal,
                                    ]);
                                    continue;
                                }

                                $total      += $size;
                                $filtered[] = $item;
                            }

                            $attachments = $filtered;
                        }
                    }

                    if (isset($rules['platforms']) && is_array($rules['platforms'])) {
                        $platformRules = $rules['platforms'];
                        $only = [];
                        if (isset($platformRules['only']) && is_array($platformRules['only'])) {
                            foreach ($platformRules['only'] as $p) {
                                if (!is_string($p) || trim($p) === '')
                                    continue;
                                $only[] = MessagingPlatform::normalize($p)->value;
                            }
                            $only = array_values(array_unique($only));
                        }

                        $except = [];
                        if (isset($platformRules['except']) && is_array($platformRules['except'])) {
                            foreach ($platformRules['except'] as $p) {
                                if (!is_string($p) || trim($p) === '')
                                    continue;
                                $except[] = MessagingPlatform::normalize($p)->value;
                            }
                            $except = array_values(array_unique($except));
                        }
                        $required = !empty($platformRules['required']);
                        $enforced = [];
                        foreach ($platformsNorm as $p) {
                            if ($only && !in_array($p, $only, true))
                                continue;
                            if ($except && in_array($p, $except, true))
                                continue;
                            $enforced[] = $p;
                        }
                        if (!$enforced && $required && $only)
                            $enforced = $only;
                        if (!$enforced && $required && !$only)
                            Log::warning(static::class . ' all platforms filtered out by template rules', [
                                'notification_id' => $model->getAttribute('id'),
                                'type'            => $typeSlug,
                            ]);
                        if ($enforced)
                            $platformsNorm = array_values(array_unique($enforced));
                    }
                    if (isset($rules[MC::COL_SNT_BY]) && is_array($rules[MC::COL_SNT_BY])) {
                        $senderRules = $rules[MC::COL_SNT_BY];
                        $senderId    = (string) ($model->getAttribute(MC::COL_SNT_BY) ?? '');
                        $recipientId = (string) ($model->getAttribute(UC::COL_USER_ID) ?? '');
                        $onlySenders = [];
                        if (isset($senderRules['only']) && is_array($senderRules['only'])) {
                            foreach ($senderRules['only'] as $id) {
                                if (!is_string($id) && !is_numeric($id))
                                    continue;
                                $val = trim((string) $id);
                                if ($val === '')
                                    continue;
                                $onlySenders[] = $val;
                            }
                            $onlySenders = array_values(array_unique($onlySenders));
                        }
                        $exceptSenders = [];
                        if (isset($senderRules['except']) && is_array($senderRules['except'])) {
                            foreach ($senderRules['except'] as $id) {
                                if (!is_string($id) && !is_numeric($id))
                                    continue;
                                $val = trim((string) $id);
                                if ($val === '')
                                    continue;
                                $exceptSenders[] = $val;
                            }
                            $exceptSenders = array_values(array_unique($exceptSenders));
                        }

                        $mustExist            = !empty($senderRules['must_exist']);
                        $mustNotBeRecipient   = !empty($senderRules['must_not_be_recipient']);
                        $violation            = false;
                        $currentSenderId      = $senderId;
                        if ($onlySenders && !in_array($currentSenderId, $onlySenders, true))
                            $violation = true;
                        if ($exceptSenders && in_array($currentSenderId, $exceptSenders, true))
                            $violation = true;
                        if ($mustNotBeRecipient && $recipientId !== '' && $recipientId === $currentSenderId)
                            $violation = true;
                        if ($mustExist && $currentSenderId !== '' && class_exists(User::class)) {
                            try {
                                $exists = User::query()->whereKey($currentSenderId)->exists();
                                if (!$exists)
                                    $violation = true;
                            } catch (\Throwable $e) {
                                Log::debug(static::class . ' failed to check sender existence', [
                                    'error'           => $e->getMessage(),
                                    'notification_id' => $model->getAttribute('id'),
                                ]);
                            }
                        }
                        if ($violation) {
                            $fallback = null;
                            if (isset($senderRules['fallback']) && is_string($senderRules['fallback'])) {
                                $value = trim($senderRules['fallback']);
                                if ($value !== '')
                                    $fallback = $value;
                            }
                            if ($fallback === null && $template && $template->getAttribute(DC::COL_TABLE_CREATOR))
                                $fallback = (string) $template->getAttribute(DC::COL_TABLE_CREATOR);
                            if ($fallback === null)
                                $fallback = DC::DEFAULT_UUID;
                            $model->setAttribute(MC::COL_SNT_BY, $fallback);
                            Log::warning(static::class . ' sender adjusted by template rules', [
                                'previous'         => $currentSenderId,
                                'new'              => $fallback,
                                'notification_id'  => $model->getAttribute('id'),
                                'type'             => $typeSlug,
                            ]);
                        }
                    }
                    if (isset($rules['tags']) && is_array($rules['tags'])) {
                        $tagRules = $rules['tags'];
                        $maxCount = null;
                        if (isset($tagRules['max_count']) && is_numeric($tagRules['max_count']))
                            $maxCount = (int) $tagRules['max_count'];
                        if ($maxCount !== null && $maxCount > 0 && count($tags) > $maxCount) {
                            $trimmed = [];
                            $count   = 0;
                            foreach ($tags as $tag) {
                                $trimmed[] = $tag;
                                $count++;
                                if ($count >= $maxCount)
                                    break;
                            }
                            Log::debug(static::class . ' tags trimmed by max_count rule', [
                                'notification_id' => $model->getAttribute('id'),
                                'type'            => $typeSlug,
                                'max_count'       => $maxCount,
                            ]);
                            $tags = $trimmed;
                        }
                    }
                    if (isset($rules['data']) && is_array($rules['data'])) {
                        $dataRules = $rules['data'];
                        $payload   = [];
                        try {
                            $payload = $model->getPayloadAttribute();
                        } catch (\Throwable $e) {
                            Log::debug(static::class . ' failed to decode payload for rules', [
                                'error'           => $e->getMessage(),
                                'notification_id' => $model->getAttribute('id'),
                            ]);
                        }
                        if (isset($dataRules['required_keys']) && is_array($dataRules['required_keys'])) {
                            foreach ($dataRules['required_keys'] as $requiredKey) {
                                if (!is_string($requiredKey) || $requiredKey === '')
                                    continue;
                                $missing = !array_key_exists($requiredKey, $payload)
                                    || $payload[$requiredKey] === null
                                    || $payload[$requiredKey] === '';
                                if ($missing)
                                    Log::warning(static::class . ' payload missing required key as per template rules', [
                                        'key'             => $requiredKey,
                                        'notification_id' => $model->getAttribute('id'),
                                        'type'            => $typeSlug,
                                    ]);
                            }
                        }
                        if (isset($dataRules['max_length']) && is_numeric($dataRules['max_length'])) {
                            $maxLength = (int) $dataRules['max_length'];
                            if ($maxLength > 0) {
                                $rawData = (string) ($model->getAttribute('data') ?? '');
                                $len     = strlen($rawData);
                                if ($len > $maxLength) {
                                    $truncated = substr($rawData, 0, $maxLength);
                                    $model->setAttribute('data', $truncated);
                                    Log::debug(static::class . ' data truncated by max_length rule', [
                                        'notification_id'  => $model->getAttribute('id'),
                                        'type'             => $typeSlug,
                                        'max_length'       => $maxLength,
                                        'original_length'  => $len,
                                    ]);
                                }
                            }
                        }
                    }
                    if (isset($rules[UC::COL_USER_ID]) && is_array($rules[UC::COL_USER_ID])) {
                        $userRules = $rules[UC::COL_USER_ID];
                        $recipientId = (string) ($model->getAttribute(UC::COL_USER_ID) ?? '');
                        $onlyRecipients = [];
                        if (isset($userRules['only']) && is_array($userRules['only'])) {
                            foreach ($userRules['only'] as $id) {
                                if (!is_string($id) && !is_numeric($id))
                                    continue;
                                $val = trim((string) $id);
                                if ($val === '')
                                    continue;
                                $onlyRecipients[] = $val;
                            }
                            $onlyRecipients = array_values(array_unique($onlyRecipients));
                        }
                        $exceptRecipients = [];
                        if (isset($userRules['except']) && is_array($userRules['except'])) {
                            foreach ($userRules['except'] as $id) {
                                if (!is_string($id) && !is_numeric($id))
                                    continue;
                                $val = trim((string) $id);
                                if ($val === '')
                                    continue;
                                $exceptRecipients[] = $val;
                            }
                            $exceptRecipients = array_values(array_unique($exceptRecipients));
                        }
                        $mustExistRecipient = !empty($userRules['must_exist']);
                        $recipientViolation = false;
                        if ($onlyRecipients && !in_array($recipientId, $onlyRecipients, true))
                            $recipientViolation = true;
                        if ($exceptRecipients && in_array($recipientId, $exceptRecipients, true))
                            $recipientViolation = true;
                        if ($mustExistRecipient && $recipientId !== '' && class_exists(User::class)) {
                            try {
                                $existsRecipient = User::query()->whereKey($recipientId)->exists();
                                if (!$existsRecipient)
                                    $recipientViolation = true;
                            } catch (\Throwable $e) {
                                Log::debug(static::class . ' failed to check recipient existence', [
                                    'error'           => $e->getMessage(),
                                    'notification_id' => $model->getAttribute('id'),
                                ]);
                            }
                        }
                        if ($recipientViolation) {
                            $fallbackRecipient = null;
                            if (isset($userRules['fallback']) && is_string($userRules['fallback'])) {
                                $value = trim($userRules['fallback']);
                                if ($value !== '')
                                    $fallbackRecipient = $value;
                            }
                            if ($fallbackRecipient === null)
                                $fallbackRecipient = $recipientId;
                            $model->setAttribute(UC::COL_USER_ID, $fallbackRecipient);
                            Log::warning(static::class . ' recipient adjusted by template rules', [
                                'previous'         => $recipientId,
                                'new'              => $fallbackRecipient,
                                'notification_id'  => $model->getAttribute('id'),
                                'type'             => $typeSlug,
                            ]);
                        }
                    }
                }
                $model->setAttribute('attachments', array_values($attachments));
                $model->setAttribute('metadata', array_values($metadata));
                $model->setAttribute('tags', array_values($tags));
                $model->setAttribute('platforms', array_values(array_unique($platformsNorm)));
                $model->ensureJsonAttributesAreEncoded([
                    'attachments',
                    'metadata',
                    'tags',
                    'platforms',
                ]);
            } catch (\Throwable $e) {
                Log::warning(static::class . ' failed during saving normalization', [
                    'error' => $e->getMessage(),
                    'id'    => $model->getAttribute('id'),
                ]);
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, UC::COL_USER_ID, 'id');
    }

    public function recipient(): BelongsTo
    {
        return $this->user();
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, MC::COL_SNT_BY, 'id');
    }

    public function getSentAtHumanAttribute(): ?string
    {
        $sentAt = $this->getAttribute(MC::COL_SNT_AT);
        if (!$sentAt instanceof Carbon) {
            return null;
        }

        return $sentAt->diffForHumans();
    }

    public function getTypeEnumAttribute(): string
    {
        $raw  = (string) ($this->getAttribute('type') ?? '');
        $enum = NotificationTemplateType::normalize($raw);

        return $enum->value;
    }

    public function getIsReadFlagAttribute(): bool
    {
        return (bool) $this->getAttribute(MC::COL_IS_RD);
    }

    /**
     * @return array<int,string>
     */
    public function getPlatformsEnumAttribute(): array
    {
        $raw = self::normalizeArrayField($this->getAttribute('platforms'));

        $normalized = [];
        foreach ($raw as $value) {
            if (!is_string($value) || trim($value) === '')
                continue;
            $normalized[] = MessagingPlatform::normalize($value)->value;
        }

        return array_values(array_unique($normalized));
    }

    /**
     * @return array<string,mixed>
     */
    public function getPayloadAttribute(): array
    {
        $raw = (string) ($this->getAttribute('data') ?? '');
        if ($raw === '') {
            return [];
        }

        try {
            $decoded = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);

            return is_array($decoded) ? $decoded : [];
        } catch (\Throwable $e) {
            Log::debug(static::class . ' invalid JSON payload in data', [
                'error' => $e->getMessage(),
                'data'  => mb_substr($raw, 0, 256),
            ]);

            return [];
        }
    }

    public function scopeForUser(Builder $query, string $userId): Builder
    {
        return $query->where(UC::COL_USER_ID, trim($userId));
    }

    public function scopeUnread(Builder $query): Builder
    {
        return $query->where(MC::COL_IS_RD, 0);
    }

    public function scopeRead(Builder $query): Builder
    {
        return $query->where(MC::COL_IS_RD, 1);
    }

    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    public function scopeOnPlatform(Builder $query, string|MessagingPlatform $platform): Builder
    {
        $enum = $platform instanceof MessagingPlatform
            ? $platform
            : MessagingPlatform::normalize($platform);

        return $query->whereJsonContains('platforms', $enum->value);
    }

    public function markAsRead(?\DateTimeInterface $at = null): void
    {
        try {
            $when = $at ? Carbon::instance($at) : Carbon::now();
            $this->setAttribute(MC::COL_IS_RD, 1);
            $this->setAttribute(MC::COL_RD_AT, $when);
            $this->save();
        } catch (\Throwable $e) {
            Log::warning(static::class . ' failed to mark notification as read', [
                'error' => $e->getMessage(),
                'id'    => $this->getAttribute('id'),
            ]);
        }
    }

    public static function markAllAsReadForUser(string $userId): void
    {
        $userId = trim($userId);
        if ($userId === '') {
            return;
        }

        try {
            static::query()
                ->forUser($userId)
                ->unread()
                ->update([
                    MC::COL_IS_RD => 1,
                    MC::COL_RD_AT => Carbon::now(),
                    DC::COL_U_AT  => Carbon::now(),
                ]);
        } catch (\Throwable $e) {
            Log::warning(static::class . ' failed to mark all notifications as read for user', [
                'error'   => $e->getMessage(),
                'user_id' => $userId,
            ]);
        }
    }

    public static function unreadCountForUserCached(string $userId): int
    {
        $userId = trim($userId);
        if ($userId === '') {
            return 0;
        }

        $cacheKey = 'notifications:unread_count:' . $userId;

        try {
            return Cache::remember(
                $cacheKey,
                Carbon::now()->addMinutes(5),
                fn(): int => (int) static::query()
                    ->forUser($userId)
                    ->unread()
                    ->count()
            );
        } catch (\Throwable $e) {
            Log::warning(static::class . ' failed to compute unreadCountForUserCached', [
                'error'   => $e->getMessage(),
                'user_id' => $userId,
            ]);

            try {
                return (int) static::query()
                    ->forUser($userId)
                    ->unread()
                    ->count();
            } catch (\Throwable $e2) {
                Log::error(static::class . ' failed to compute unreadCountForUser without cache', [
                    'error'   => $e2->getMessage(),
                    'user_id' => $userId,
                ]);

                return 0;
            }
        }
    }

    /**
     * @return array<string,array{count:int}>
     */
    public static function aggregateCountByTypeForUser(string $userId): array
    {
        $userId = trim($userId);
        if ($userId === '') {
            return [];
        }

        try {
            $rows = static::query()
                ->forUser($userId)
                ->selectRaw('type, COUNT(*) as aggregate_count')
                ->groupBy('type')
                ->get();

            $result = [];
            foreach ($rows as $row) {
                $key = (string) ($row->type ?? 'unknown');
                $result[$key] = [
                    'count' => (int) ($row->aggregate_count ?? 0),
                ];
            }

            return $result;
        } catch (\Throwable $e) {
            Log::warning(static::class . ' failed to aggregateCountByTypeForUser', [
                'error'   => $e->getMessage(),
                'user_id' => $userId,
            ]);

            return [];
        }
    }

    public function toHtml(): string
    {
        try {
            $rawData = (string) ($this->getAttribute('data') ?? '');
            if ($rawData === '') {
                return '';
            }

            $decoded = json_decode($rawData);
            if (!is_object($decoded)) {
                return '';
            }

            $data       = $decoded;
            $link       = '#';
            $icon       = 'fa fa-bell';
            $icon_color = 'bg-primary';
            $text       = '';

            $usr = null;
            if (isset($data->updated_by) && !empty($data->updated_by) && class_exists(User::class)) {
                try {
                    $usr = User::query()->find($data->updated_by);
                } catch (\Throwable $e) {
                    Log::debug(static::class . ' failed to resolve notification user', [
                        'error' => $e->getMessage(),
                        'user'  => $data->updated_by,
                    ]);
                }
            }

            if (!empty($usr)) {
                $type = (string) ($this->getAttribute('type') ?? '');

                if ($type === 'assign_deal') {
                    $link       = route('deals.show', [$data->deal_id ?? null]);
                    $text       = $usr->name . ' ' . __('Added you') . ' ' . __('in deal')
                        . " <b class='font-weight-bold'>" . ($data->name ?? '') . '</b> ';
                    $icon       = 'fa fa-plus';
                    $icon_color = 'bg-primary';
                } elseif ($type === 'create_deal_call') {
                    $link       = route('deals.show', [$data->deal_id ?? null]);
                    $text       = $usr->name . ' ' . __('Create new Deal Call') . ' '
                        . __('in deal') . " <b class='font-weight-bold'>" . ($data->name ?? '') . '</b> ';
                    $icon       = 'fa fa-phone';
                    $icon_color = 'bg-info';
                } elseif ($type === 'update_deal_source') {
                    $link       = route('deals.show', [$data->deal_id ?? null]);
                    $text       = $usr->name . ' ' . __('Update Sources') . ' '
                        . __('in deal') . " <b class='font-weight-bold'>" . ($data->name ?? '') . '</b> ';
                    $icon       = 'fa fa-file-alt';
                    $icon_color = 'bg-warning';
                } elseif ($type === 'create_task') {
                    $link       = route('deals.show', [$data->deal_id ?? null]);
                    $text       = $usr->name . ' ' . __('Create new Task') . ' '
                        . __('in deal') . " <b class='font-weight-bold'>" . ($data->name ?? '') . '</b> ';
                    $icon       = 'fa fa-tasks';
                    $icon_color = 'bg-primary';
                } elseif ($type === 'add_product') {
                    $link       = route('deals.show', [$data->deal_id ?? null]);
                    $text       = $usr->name . ' ' . __('Add new Products') . ' '
                        . __('in deal') . " <b class='font-weight-bold'>" . ($data->name ?? '') . '</b> ';
                    $icon       = 'fa fa-dolly';
                    $icon_color = 'bg-danger';
                } elseif ($type === 'add_discussion') {
                    $link       = route('deals.show', [$data->deal_id ?? null]);
                    $text       = $usr->name . ' ' . __('Add new Discussion') . ' '
                        . __('in deal') . " <b class='font-weight-bold'>" . ($data->name ?? '') . '</b> ';
                    $icon       = 'fa fa-comments';
                    $icon_color = 'bg-info';
                } elseif ($type === 'move_deal') {
                    $link       = route('deals.show', [$data->deal_id ?? null]);
                    $text       = $usr->name . ' ' . __('Moved the deal') . " <b class='font-weight-bold'>" . ($data->name ?? '') . '</b> '
                        . __('from') . ' ' . __(ucwords($data->old_status ?? '')) . ' '
                        . __('to') . ' ' . __(ucwords($data->new_status ?? ''));
                    $icon       = 'fa fa-arrows-alt';
                    $icon_color = 'bg-primary';
                } elseif ($type === 'assign_estimation') {
                    $link       = route('estimations.show', [$data->estimation_id ?? null]);
                    $text       = $usr->name . ' ' . __('Added you') . ' '
                        . __('in estimation') . " <b class='font-weight-bold'>" . ($data->estimation_name ?? '') . '</b> ';
                    $icon       = 'fa fa-plus';
                    $icon_color = 'bg-primary';
                } elseif ($type === 'assign_lead') {
                    $link       = route('leads.show', [$data->lead_id ?? null]);
                    $text       = $usr->name . ' ' . __('Added you') . ' '
                        . __('in lead') . " <b class='font-weight-bold'>" . ($data->name ?? '') . '</b> ';
                    $icon       = 'fa fa-plus';
                    $icon_color = 'bg-primary';
                } elseif ($type === 'create_lead_call') {
                    $link       = route('leads.show', [$data->lead_id ?? null]);
                    $text       = $usr->name . ' ' . __('Create new Lead Call') . ' '
                        . __('in lead') . " <b class='font-weight-bold'>" . ($data->name ?? '') . '</b> ';
                    $icon       = 'fa fa-phone';
                    $icon_color = 'bg-info';
                } elseif ($type === 'update_lead_source') {
                    $link       = route('leads.show', [$data->lead_id ?? null]);
                    $text       = $usr->name . ' ' . __('Update Sources') . ' '
                        . __('in lead') . " <b class='font-weight-bold'>" . ($data->name ?? '') . '</b> ';
                    $icon       = 'fa fa-file-alt';
                    $icon_color = 'bg-warning';
                } elseif ($type === 'add_lead_product') {
                    $link       = route('leads.show', [$data->lead_id ?? null]);
                    $text       = $usr->name . ' ' . __('Add new Products') . ' '
                        . __('in lead') . " <b class='font-weight-bold'>" . ($data->name ?? '') . '</b> ';
                    $icon       = 'fa fa-dolly';
                    $icon_color = 'bg-danger';
                } elseif ($type === 'add_lead_discussion') {
                    $link       = route('leads.show', [$data->lead_id ?? null]);
                    $text       = $usr->name . ' ' . __('Add new Discussion') . ' '
                        . __('in lead') . " <b class='font-weight-bold'>" . ($data->name ?? '') . '</b> ';
                    $icon       = 'fa fa-comments';
                    $icon_color = 'bg-info';
                } elseif ($type === 'move_lead') {
                    $link       = route('leads.show', [$data->lead_id ?? null]);
                    $text       = $usr->name . ' ' . __('Moved the lead') . " <b class='font-weight-bold'>" . ($data->name ?? '') . '</b> '
                        . __('from') . ' ' . __(ucwords($data->old_status ?? '')) . ' '
                        . __('to') . ' ' . __(ucwords($data->new_status ?? ''));
                    $icon       = 'fa fa-arrows-alt';
                    $icon_color = 'bg-primary';
                }
            }

            if ($text === '') {
                $payload = $this->getPayloadAttribute();
                $text    = (string) ($payload['message'] ?? $payload['title'] ?? '');
                if ($text === '') {
                    return '';
                }
            }

            $dateSource = $this->getAttribute(MC::COL_SNT_AT)
                ?? $this->getAttribute(DC::COL_C_AT);

            $date = $dateSource instanceof Carbon
                ? $dateSource->diffForHumans()
                : '';

            return '<a href="' . $link . '" class="list-group-item list-group-item-action">'
                . '<div class="d-flex align-items-center">'
                . '<div><span class="avatar ' . $icon_color . ' text-white rounded-circle">'
                . '<i class="' . $icon . '"></i></span></div>'
                . '<div class="flex-fill ml-3">'
                . '<div class="h6 text-sm mb-0">' . $text . '</div>'
                . '<small class="text-muted text-xs">' . $date . '</small>'
                . '</div></div></a>';
        } catch (\Throwable $e) {
            Log::warning(static::class . ' failed to render notification HTML', [
                'error' => $e->getMessage(),
                'id'    => $this->getAttribute('id'),
            ]);

            return '';
        }
    }
}
