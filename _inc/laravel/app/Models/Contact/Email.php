<?php

namespace App\Models;

use App\Config\Constants\{
    ActivitiesConstants as AC,
    DatabaseConstants as DC,
    EmailsConstants as EC,
    MessagesConstants as MC
};
use App\Enums\AppModuleType;
use App\Traits\{DefinesDates, FiltersSecureAttachments, HasAuditFields, NormalizesAddresses, UsesUuids};
use Carbon\Carbon;
use Illuminate\Database\Eloquent\{Builder, Model, SoftDeletes};
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\{DB, Log};
use Illuminate\Support\Str;

class Email extends Model
{
    use UsesUuids;
    use HasAuditFields;
    use NormalizesAddresses;
    use FiltersSecureAttachments;
    use SoftDeletes;
    use DefinesDates;

    protected $table = DC::TABLE_EMAILS;

    protected $guarded = [
        'id',
        DC::COL_TABLE_CREATOR,
    ];

    protected $fillable = [
        EC::COL_TT,              // title
        'provider',
        'description',
        'body',
        'html',
        'notes',

        EC::COL_FROM,
        EC::COL_FROM_ID,
        'to',
        EC::COL_TO_ID,

        AC::COL_IS_RPL,           // is_reply
        'thread',
        'cc',
        'bcc',

        AC::COL_IS_FV,            // is_favorite
        MC::COL_IS_DFT,           // is_draft
        MC::COL_IS_TRS,           // is_trashed
        MC::COL_IS_ARC,           // is_archived
        MC::COL_IS_SPAM,          // is_spam

        DC::COL_MW_FREE,
        MC::COL_SNT_AT,
        MC::COL_IS_RD,
        MC::COL_RD_AT,

        EC::COL_D_URL,
        DC::COL_DOC_ID,
        EC::COL_EM_KEY,               // unique identifier (legacy name "email")

        AC::COL_MT,               // module_type
        AC::COL_MI,               // module_id (legacy)

        'counter',
        'headers',
        'attachments',
        'templates',
        'variables',
        'settings',
        DC::COL_MW_SCAN,
        'metadata',

        DC::COL_TABLE_UPDATER,
    ];

    protected $casts = [
        EC::COL_TT        => 'string',
        'provider'        => 'string',
        'description'     => 'string',
        'body'            => 'string',
        'html'            => 'string',
        'notes'           => 'string',

        EC::COL_FROM      => 'string',
        'to'              => 'string',

        AC::COL_IS_RPL    => 'boolean',
        AC::COL_IS_FV     => 'boolean',
        MC::COL_IS_DFT    => 'boolean',
        MC::COL_IS_TRS    => 'boolean',
        MC::COL_IS_ARC    => 'boolean',
        MC::COL_IS_SPAM   => 'boolean',
        DC::COL_MW_FREE   => 'boolean',
        MC::COL_IS_RD     => 'boolean',

        MC::COL_SNT_AT    => 'datetime',
        MC::COL_RD_AT     => 'datetime',
        'deleted_at'      => 'datetime',

        'thread'          => 'array',
        'cc'              => 'array',
        'bcc'             => 'array',
        'headers'         => 'array',
        'attachments'     => 'array',
        'templates'       => 'array',
        'variables'       => 'array',
        'settings'        => 'array',
        DC::COL_MW_SCAN   => 'array',
        'metadata'        => 'array',

        'counter'         => 'integer',
        AC::COL_MT        => 'string',
    ];

    protected $with = [
        'document',
    ];

    protected $appends = [
        'module_type_enum',
        'from_resolved',
        'to_resolved',
        'is_deliverable',
        'is_threaded',
        'thread_count',
        'recipients_all',
    ];

    private static array $cache = [
        'user_email' => [],
        'fallback'   => [],
    ];

    protected static function booted(): void
    {
        // todo too heavy for mocking, use only in production
        // static::saving(function (self $m): void {
        //     try {
        //         $m->normalizeCoreStrings();
        //         $m->normalizeUuids();
        //         $m->ensureUniqueIdentifier();
        //         $m->normalizeModuleType();
        //         $m->normalizeJsonFields();
        //         $m->normalizeFromToWithFallbacks();
        //         $m->normalizeFlagsAndTimestamps();
        //     } catch (\Throwable $e) {
        //         Log::error(self::class . ' saving failed', [
        //             'id'        => (string) ($m->getAttribute('id') ?? ''),
        //             'from_id'   => (string) ($m->getAttribute(EC::COL_FROM_ID) ?? ''),
        //             'to_id'     => (string) ($m->getAttribute(EC::COL_TO_ID) ?? ''),
        //             'error'     => $e->getMessage(),
        //             'method' => 'static::saving'
        //         ]);
        //         throw $e;
        //     }
        // });
        static::addGlobalScope('order_created_desc', function (Builder $b): void {
            $b->reorder()->orderBy(DC::COL_C_AT, 'desc');
        });
    }

    public function fromUser(): BelongsTo
    {
        return $this->belongsTo(User::class, EC::COL_FROM_ID, 'id');
    }

    public function toUser(): BelongsTo
    {
        return $this->belongsTo(User::class, EC::COL_TO_ID, 'id');
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class, DC::COL_DOC_ID, 'id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, DC::COL_TABLE_CREATOR, 'id');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, DC::COL_TABLE_UPDATER, 'id');
    }

    public function getModuleTypeEnumAttribute(): AppModuleType
    {
        $raw = $this->getAttribute(AC::COL_MT);
        return $raw instanceof AppModuleType ? $raw : AppModuleType::normalize(is_string($raw) ? $raw : null);
    }

    public function getFromResolvedAttribute(): ?string
    {
        $from = $this->getAttribute(EC::COL_FROM);
        if (is_string($from) && trim($from) !== '') return $from;

        $resolved = $this->resolveUserEmail((string) ($this->getAttribute(EC::COL_FROM_ID) ?? ''));
        return $resolved ?: $this->fallbackFromEmail();
    }

    public function getToResolvedAttribute(): ?string
    {
        $to = $this->getAttribute('to');
        if (is_string($to) && trim($to) !== '') return $to;

        $resolved = $this->resolveUserEmail((string) ($this->getAttribute(EC::COL_TO_ID) ?? ''));
        return $resolved ?: $this->fallbackToEmail();
    }

    public function getIsDeliverableAttribute(): bool
    {
        $f = $this->getAttribute('from_resolved');
        $t = $this->getAttribute('to_resolved');
        return is_string($f) && $f !== '' && is_string($t) && $t !== '';
    }

    public function getIsThreadedAttribute(): bool
    {
        $thread = $this->getAttribute('thread');
        return is_array($thread) && count($thread) > 0;
    }

    public function getThreadCountAttribute(): int
    {
        $thread = $this->getAttribute('thread');
        return is_array($thread) ? count($thread) : 0;
    }

    public function getRecipientsAllAttribute(): array
    {
        $out = [];

        $to = $this->getAttribute('to_resolved');
        if (is_string($to) && $to !== '') $out[] = $to;

        foreach (['cc', 'bcc'] as $k) {
            $v = $this->getAttribute($k);
            if (!is_array($v)) continue;
            foreach ($v as $item) {
                if (!is_string($item)) continue;
                $item = trim($item);
                if ($item !== '') $out[] = $item;
            }
        }

        return array_values(array_unique($out));
    }

    public function scopeInboxFor(Builder $q, string $userId): Builder
    {
        return $q->where(EC::COL_TO_ID, $userId);
    }

    public function scopeSentBy(Builder $q, string $userId): Builder
    {
        return $q->where(EC::COL_FROM_ID, $userId);
    }

    public function scopeVisible(Builder $q): Builder
    {
        return $q
            ->where(MC::COL_IS_TRS, false)
            ->where(MC::COL_IS_SPAM, false)
            ->where(MC::COL_IS_ARC, false);
    }

    private function normalizeCoreStrings(): void
    {
        foreach ([EC::COL_TT, 'provider', 'description', 'body', 'html', 'notes', EC::COL_D_URL] as $k) {
            $raw = $this->getAttribute($k);
            if ($raw === null) continue;
            if (!is_string($raw)) {
                $this->setAttribute($k, null);
                continue;
            }
            $v = trim($raw);
            $this->setAttribute($k, $v === '' ? null : $v);
        }

        $cnt = (int) ($this->getAttribute('counter') ?? 0);
        $this->setAttribute('counter', max(0, $cnt));
    }

    private function normalizeUuids(): void
    {
        foreach ([EC::COL_FROM_ID, EC::COL_TO_ID, DC::COL_DOC_ID, AC::COL_MI] as $k) {
            $raw = $this->getAttribute($k);
            if ($raw === null) continue;

            if (!is_string($raw)) {
                $this->setAttribute($k, null);
                continue;
            }

            $v = trim($raw);
            if ($v === '') $this->setAttribute($k, null);
            elseif (!Utility::looksLikeUuid($v)) $this->setAttribute($k, null);
            else $this->setAttribute($k, $v);
        }
    }

    private function ensureUniqueIdentifier(): void
    {
        $raw = $this->getAttribute(EC::COL_EM_KEY);
        $candidateKey = null;
        if (!is_string($raw) || trim($raw) === '') {
            $retriesLimit = 1024;
            do {
                $retriesLimit--;
                $candidateKey = (string) Str::uuid();
            } while (self::where(EC::COL_EM_KEY, $candidateKey)->exists() && $retriesLimit > 0);
            if (!$retriesLimit) {
                Log::error(self::class . ' ensureUniqueIdentifier failed to generate unique key', [
                    'method' => __METHOD__,
                    'id'     => (string) ($this->getAttribute('id') ?? ''),
                ]);
                throw new \RuntimeException('Failed to generate unique email identifier');
            }
            !empty($candidateKey) && $this->setAttribute(EC::COL_EM_KEY, $candidateKey);
        } else {
            $v = trim($raw);
            $retriesLimit = 1024;
            do {
                $retriesLimit--;
                $candidateKey = (string) Str::uuid();
            } while (self::where(EC::COL_EM_KEY, $candidateKey)->exists() && $retriesLimit > 0);
            if (!$retriesLimit) {
                Log::error(self::class . ' ensureUniqueIdentifier failed to generate unique key', [
                    'method' => __METHOD__,
                    'id'     => (string) ($this->getAttribute('id') ?? ''),
                ]);
                throw new \RuntimeException('Failed to generate unique email identifier');
            }
            $this->setAttribute(EC::COL_EM_KEY, $v === '' ? (string) Str::uuid() : $v);
        }
    }

    private function normalizeModuleType(): void
    {
        $raw  = $this->getAttribute(AC::COL_MT);
        $enum = $raw instanceof AppModuleType ? $raw : AppModuleType::normalize(is_string($raw) ? $raw : null);
        $this->setAttribute(AC::COL_MT, $enum->value);
    }

    private function normalizeJsonFields(): void
    {
        $thread = $this->getAttribute('thread');
        if (is_string($thread))
            $thread = self::normalizeArrayField($thread);

        if (is_array($thread)) {
            $filtered = [];
            foreach ($thread as $id) {
                if (!is_string($id)) continue;
                $id = trim($id);
                if ($id !== '' && Utility::looksLikeUuid($id)) $filtered[] = $id;
            }
            $this->setAttribute('thread', array_values(array_unique($filtered)));
        } elseif ($thread !== null) {
            $this->setAttribute('thread', null);
        }

        foreach (['cc', 'bcc'] as $k) {
            $v = $this->getAttribute($k);
            if (is_string($v))
                $v = self::normalizeArrayField($v);

            if (!is_array($v)) {
                $this->setAttribute($k, null);
                continue;
            }

            $out = [];
            foreach ($v as $item) {
                if (!is_string($item)) continue;
                $norm = self::normalizeEmail($item, "email.{$k}", (string) ($this->getAttribute('id') ?? ''));
                if (is_string($norm) && trim($norm) !== '') $out[] = $norm;
            }
            $this->setAttribute($k, $out ? array_values(array_unique($out)) : null);
        }

        $this->ensureJsonAttributesAreEncoded([
            'thread',
            'cc',
            'bcc',
            'headers',
            'attachments',
            'templates',
            'variables',
            'settings',
            DC::COL_MW_SCAN,
            'metadata',
        ]);
    }

    private function normalizeFromToWithFallbacks(): void
    {
        $from = $this->getAttribute(EC::COL_FROM);
        $to   = $this->getAttribute('to');

        if (is_string($from)) $from = self::normalizeEmail($from, 'email.from', (string) ($this->getAttribute('id') ?? ''));
        if (is_string($to))   $to   = self::normalizeEmail($to, 'email.to', (string) ($this->getAttribute('id') ?? ''));

        $this->setAttribute(EC::COL_FROM, (is_string($from) && trim($from) !== '') ? $from : null);
        $this->setAttribute('to', (is_string($to) && trim($to) !== '') ? $to : null);

        if (!$this->getAttribute(EC::COL_FROM)) {
            $resolved = $this->resolveUserEmail((string) ($this->getAttribute(EC::COL_FROM_ID) ?? ''));
            $this->setAttribute(EC::COL_FROM, $resolved ?: $this->fallbackFromEmail());
        }

        if (!$this->getAttribute('to')) {
            $resolved = $this->resolveUserEmail((string) ($this->getAttribute(EC::COL_TO_ID) ?? ''));
            $this->setAttribute('to', $resolved ?: $this->fallbackToEmail());
        }
    }

    private function normalizeFlagsAndTimestamps(): void
    {
        foreach ([AC::COL_IS_RPL, AC::COL_IS_FV, MC::COL_IS_DFT, MC::COL_IS_TRS, MC::COL_IS_ARC, MC::COL_IS_SPAM, DC::COL_MW_FREE, MC::COL_IS_RD] as $k) {
            $this->setAttribute($k, (bool) ($this->getAttribute($k) ?? false));
        }

        $now = Carbon::now('America/Sao_Paulo');

        $sent = $this->getAttribute(MC::COL_SNT_AT);
        $read = $this->getAttribute(MC::COL_RD_AT);

        try {
            if ($sent) {
                $sentAt = $sent instanceof Carbon ? $sent : Carbon::parse((string) $sent, 'America/Sao_Paulo');
                if ($sentAt->isFuture()) $sentAt = $now;
                $this->setAttribute(MC::COL_SNT_AT, $sentAt);
            }
        } catch (\Throwable $e) {
            Log::warning(self::class . ' invalid sent_at', [
                'error' => $e->getMessage(),
                'method' => __METHOD__,
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'original' => is_string($sent) ? $sent : '#NULL',
            ]);
            $this->setAttribute(MC::COL_SNT_AT, null);
        }

        $isRead = (bool) $this->getAttribute(MC::COL_IS_RD);

        if (!$isRead)
            $this->setAttribute(MC::COL_RD_AT, null);
        else {
            try {
                $readAt = $read ? ($read instanceof Carbon ? $read : Carbon::parse((string) $read, 'America/Sao_Paulo')) : $now;
                if ($readAt->isFuture()) $readAt = $now;

                $sentAt = $this->getAttribute(MC::COL_SNT_AT);
                if ($sentAt instanceof Carbon && $readAt->lt($sentAt)) $readAt = $sentAt;

                $this->setAttribute(MC::COL_RD_AT, $readAt);
            } catch (\Throwable $e) {
                Log::warning(self::class . ' invalid read_at', [
                    'error' => $e->getMessage(),
                    'method' => __METHOD__,
                    'line' => $e->getLine(),
                    'file' => $e->getFile(),
                    'original' => is_string($read) ? $read : '#NULL',
                ]);
                $this->setAttribute(MC::COL_RD_AT, null);
                $this->setAttribute(MC::COL_IS_RD, false);
            }
        }
    }

    private function resolveUserEmail(string $userId): ?string
    {
        $userId = trim($userId);
        if ($userId === '' || !Utility::looksLikeUuid($userId)) return null;

        if (array_key_exists($userId, self::$cache['user_email']))
            return self::$cache['user_email'][$userId];

        try {
            $email = (string) (DB::table(DC::TABLE_USERS)->where('id', $userId)->value('email') ?? '');
            $email = self::normalizeEmail($email, 'user.email', $userId);
            self::$cache['user_email'][$userId] = (is_string($email) && trim($email) !== '') ? $email : null;
        } catch (\Throwable $e) {
            Log::debug(self::class . ' failed resolving user email', [
                'user_id' => $userId,
                'error'   => $e->getMessage(),
            ]);
            self::$cache['user_email'][$userId] = null;
        }

        return self::$cache['user_email'][$userId];
    }

    private function fallbackFromEmail(): ?string
    {
        if (array_key_exists('from', self::$cache['fallback']))
            return self::$cache['fallback']['from'];

        self::$cache['fallback']['from'] =
            $this->emailFromSystemUser()
            ?: $this->emailFromAnyAdmin(['super admin', 'admin'])
            ?: $this->emailFromAnyUser();

        return self::$cache['fallback']['from'];
    }

    private function fallbackToEmail(): ?string
    {
        if (array_key_exists('to', self::$cache['fallback']))
            return self::$cache['fallback']['to'];

        self::$cache['fallback']['to'] =
            $this->emailFromAnyAdmin(['super admin', 'admin'])
            ?: $this->emailFromAnyUser();

        return self::$cache['fallback']['to'];
    }

    private function emailFromSystemUser(): ?string
    {
        try {
            $uid = defined(DC::class . '::DEFAULT_UUID') ? (string) constant(DC::class . '::DEFAULT_UUID') : '';
            if ($uid === '' || !Utility::looksLikeUuid($uid)) return null;

            $email = (string) (DB::table(DC::TABLE_USERS)->where('id', $uid)->value('email') ?? '');
            $email = self::normalizeEmail($email, 'system.email', $uid);

            return (is_string($email) && trim($email) !== '') ? $email : null;
        } catch (\Throwable $e) {
            Log::debug(self::class . ' failed fetching system email', ['error' => $e->getMessage()]);
            return null;
        }
    }

    private function emailFromAnyAdmin(array $types): ?string
    {
        try {
            $email = (string) (DB::table(DC::TABLE_USERS)
                ->whereIn('type', $types)
                ->whereNotNull('email')
                ->where('email', '!=', '')
                ->inRandomOrder()
                ->value('email') ?? '');

            $email = self::normalizeEmail($email, 'admin.email', implode('|', $types));
            return (is_string($email) && trim($email) !== '') ? $email : null;
        } catch (\Throwable $e) {
            Log::debug(self::class . ' failed fetching admin email', ['error' => $e->getMessage()]);
            return null;
        }
    }

    private function emailFromAnyUser(): ?string
    {
        try {
            $email = (string) (DB::table(DC::TABLE_USERS)
                ->whereNotNull('email')
                ->where('email', '!=', '')
                ->inRandomOrder()
                ->value('email') ?? '');

            $email = self::normalizeEmail($email, 'any.email', '#any');
            return (is_string($email) && trim($email) !== '') ? $email : null;
        } catch (\Throwable $e) {
            Log::debug(self::class . ' failed fetching any user email', ['error' => $e->getMessage()]);
            return null;
        }
    }
}
