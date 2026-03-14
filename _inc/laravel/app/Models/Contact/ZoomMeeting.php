<?php

namespace App\Models;

use App\Config\Constants\{ActivitiesConstants as AC, CompaniesConstants as CC, DatabaseConstants as DC, ProjectsConstants as PJC, UsersConstants as UC};
use App\Enums\{ApprovalType, Frequency};
use App\Traits\{HasAuditFields, NormalizesArrays, UsesUuids};
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\{Factories\HasFactory, Model, Relations\BelongsTo};
use Illuminate\Support\Facades\{DB, Hash, Log};
use Illuminate\Support\Str;
/**
 * @property int|null $meeting_id
 */

class ZoomMeeting extends Model
{
    use UsesUuids, HasAuditFields, HasFactory, NormalizesArrays;

    protected $table = DC::TABLE_ZM_MT;

    protected $guarded = [
        'id',
        DC::COL_TABLE_CREATOR,
        DC::COL_TABLE_UPDATER,
    ];

    protected $fillable = [
        CC::COL_MT_ID,
        'code',
        'title',
        'password',
        AC::COL_APV_TP,
        AC::COL_ENC_TP,
        'duration',
        AC::COL_STRT_URL,
        AC::COL_JOIN_URL,
        AC::COL_RGT_URL,
        'type',
        'frequency',
        'timezone',
        PJC::COL_PJ_ID,
        UC::COL_USER_ID,
        PJC::COL_CLIENT_ID,
        PJC::COL_S_DT,
        'audio',
        AC::COL_AUTO_RCD,
        AC::COL_MAX_PRT,
        'agenda',
        'status',
        AC::COL_MT_CHAT,
        AC::COL_PV_CHAT,
        AC::COL_SCR_SHR,
        AC::COL_WHO_CAN_SHR_SCR,
        AC::COL_WT_ROOM,
        AC::COL_BRK_ROOM,
        AC::COL_FC_MD,
        AC::COL_USE_PMI,
        AC::COL_ALT_HST_ENB,
        AC::COL_ALT_HST,
        AC::COL_CLS_RGT_AFT_HRS,
        AC::COL_MUTE_UPON_ENTRY,
        AC::COL_CTC_NM_RQ,
        AC::COL_CTC_EML_RQ,
        AC::COL_ALW_SHR_BT,
        AC::COL_ALW_MT_DV,
        'settings',
        'participants',
        'webhooks',
        'metadata',
    ];

    protected $with = [
        'meeting',
        'project',
        'user',
    ];

    protected $appends = [
        'client_name',
    ];

    protected $casts = [
        'duration'         => 'integer',
        PJC::COL_S_DT      => 'datetime',

        AC::COL_MAX_PRT    => 'integer',

        AC::COL_MT_CHAT    => 'boolean',
        AC::COL_PV_CHAT    => 'boolean',
        AC::COL_SCR_SHR    => 'boolean',
        AC::COL_WT_ROOM    => 'boolean',
        AC::COL_BRK_ROOM   => 'boolean',
        AC::COL_FC_MD      => 'boolean',
        AC::COL_USE_PMI    => 'boolean',
        AC::COL_ALT_HST_ENB => 'boolean',
        AC::COL_CLS_RGT_AFT_HRS => 'boolean',
        AC::COL_MUTE_UPON_ENTRY => 'boolean',
        AC::COL_CTC_NM_RQ  => 'boolean',
        AC::COL_CTC_EML_RQ => 'boolean',
        AC::COL_ALW_SHR_BT => 'boolean',
        AC::COL_ALW_MT_DV  => 'boolean',

        'frequency'        => Frequency::class,
        AC::COL_APV_TP     => ApprovalType::class,

        'settings'         => 'array',
        'participants'     => 'array',
        'webhooks'         => 'array',
        'metadata'         => 'array',
    ];

    protected static function booted(): void
    {
        parent::booted();

        static::saving(function (self $m): void {
            try {
                self::hydrateFromMeeting($m);
                self::ensureUniqueCode($m);
                self::normalizeUrls($m);
                self::normalizeRecurrence($m);
                self::normalizeAltHosts($m);
                $m->ensureJsonAttributesAreEncoded(['settings', 'participants', 'webhooks', 'metadata']);
            } catch (\Throwable $e) {
                Log::error(static::class . ' saving hook failed: ' . $e->getMessage(), [
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'id'   => (string) ($m->getAttribute('id') ?? ''),
                ]);
            }
        });
    }

    protected static function hydrateFromMeeting(self $m): void
    {
        $meetingId = (string) ($m->getAttribute(CC::COL_MT_ID) ?? '');
        if ($meetingId === '') return;

        try {
            $row = DB::selectOne(
                'select code, title, date, time, ' . PJC::COL_MIN_DR . ' as min_dr, ' . PJC::COL_EXP_DR . ' as exp_dr, ' . PJC::COL_MAX_DR . ' as max_dr, url from ' . DC::TABLE_MEETINGS . ' where id = ? limit 1',
                [$meetingId]
            );
        } catch (\Throwable $e) {
            Log::warning(static::class . ' hydrateFromMeeting failed: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'meeting_id' => $meetingId,
            ]);
            return;
        }

        if (!$row) return;

        $code = trim((string) ($m->getAttribute('code') ?? ''));
        if ($code === '' && !empty($row->code)) $m->setAttribute('code', (string) $row->code);

        $title = trim((string) ($m->getAttribute('title') ?? ''));
        if ($title === '' && !empty($row->title)) $m->setAttribute('title', (string) $row->title);

        $join = trim((string) ($m->getAttribute(AC::COL_JOIN_URL) ?? ''));
        if ($join === '' && !empty($row->url)) $m->setAttribute(AC::COL_JOIN_URL, (string) $row->url);

        $start = $m->getAttribute(PJC::COL_S_DT);
        if ($start === null && !empty($row->date)) {
            try {
                $dt = CarbonImmutable::parse((string) $row->date);
                if (!empty($row->time)) $dt = CarbonImmutable::parse((string) $row->date . ' ' . (string) $row->time);
                $m->setAttribute(PJC::COL_S_DT, $dt);
            } catch (\Throwable) {
            }
        }

        $min = isset($row->min_dr) ? (int) $row->min_dr : 0;
        $exp = isset($row->exp_dr) ? (int) $row->exp_dr : 0;
        $max = isset($row->max_dr) ? (int) $row->max_dr : 0;

        $duration = (int) ($m->getAttribute('duration') ?? 0);
        if ($duration <= 0 && $exp > 0) $duration = $exp;
        if ($min > 0) $duration = max($min, $duration);
        if ($max > 0) $duration = min($max, $duration);

        $m->setAttribute('duration', $duration);
    }

    protected static function ensureUniqueCode(self $m): void
    {
        $code = trim((string) ($m->getAttribute('code') ?? ''));
        if ($code !== '') return;

        $attempts = 0;
        do {
            $candidate = 'ZM-' . strtoupper(Str::uuid()->toString());
            $exists = DB::table($m->getTable())
                ->where('code', $candidate)
                ->where('id', '!=', (string) ($m->getAttribute('id') ?? ''))
                ->exists();

            if (!$exists) {
                $m->setAttribute('code', $candidate);
                return;
            }
        } while (++$attempts < 16);

        Log::warning(static::class . ' code generation attempt limit reached', [
            'id' => (string) ($m->getAttribute('id') ?? ''),
        ]);
        $m->setAttribute('code', 'ZM-' . strtoupper(Str::uuid()->toString()));
    }

    protected static function normalizeUrls(self $m): void
    {
        foreach ([AC::COL_STRT_URL, AC::COL_JOIN_URL, AC::COL_RGT_URL] as $k) {
            $raw = trim((string) ($m->getAttribute($k) ?? ''));
            if ($raw === '') continue;

            if (!self::isZoomUrl($raw)) $m->setAttribute($k, null);
        }
    }

    protected static function normalizeRecurrence(self $m): void
    {
        $type = trim((string) ($m->getAttribute('type') ?? ''));
        $isRecurring = in_array($type, ['recurring_fixed', 'recurring_no_fixed'], true);

        if (!$isRecurring) $m->setAttribute('frequency', null);
        if ($isRecurring && $m->getAttribute('frequency') === null) $m->setAttribute('frequency', Frequency::Once->value);

        $tz = trim((string) ($m->getAttribute('timezone') ?? ''));
        if ($tz === '') $m->setAttribute('timezone', 'UTC');
    }

    protected static function normalizeAltHosts(self $m): void
    {
        $enabled = (bool) ($m->getAttribute(AC::COL_ALT_HST_ENB) ?? false);
        if (!$enabled) {
            $m->setAttribute(AC::COL_ALT_HST, null);
            return;
        }

        $raw = trim((string) ($m->getAttribute(AC::COL_ALT_HST) ?? ''));
        if ($raw === '') {
            $m->setAttribute(AC::COL_ALT_HST, null);
            return;
        }

        $parts = array_values(array_filter(array_map(fn($v) => trim((string) $v), explode(',', $raw)), fn($v) => $v !== ''));
        if (empty($parts)) {
            $m->setAttribute(AC::COL_ALT_HST, null);
            return;
        }

        $out = [];
        foreach ($parts as $p) {
            if (Str::isUuid($p)) {
                $out[] = $p;
                continue;
            }
            if (preg_match('/^[^@\s]+@[^@\s]+\.[^@\s]+$/', $p)) $out[] = strtolower($p);
        }

        $out = array_values(array_unique($out));
        $m->setAttribute(AC::COL_ALT_HST, $out ? implode(',', $out) : null);
    }

    protected static function isZoomUrl(string $url): bool
    {
        $u = parse_url($url);
        $host = strtolower((string) ($u['host'] ?? ''));
        if ($host === '') return false;

        return str_contains($host, 'zoom.us') || str_contains($host, 'zoom.com');
    }

    public function setPasswordAttribute(mixed $value): void
    {
        $v = trim((string) $value);
        if ($v === '') {
            $this->attributes['password'] = null;
            return;
        }

        if (str_starts_with($v, '$2y$') || str_starts_with($v, '$argon2')) {
            $this->attributes['password'] = $v;
            return;
        }

        $this->attributes['password'] = Hash::make($v);
    }

    public function meeting(): BelongsTo
    {
        return $this->belongsTo(Meeting::class, CC::COL_MT_ID, 'id');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, PJC::COL_PJ_ID, 'id');
    }

    public function projectName(): ?string
    {
        return $this->project()?->value(PJC::COL_NM);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, UC::COL_USER_ID, 'id');
    }

    public function userName(): ?string
    {
        return $this->user()?->value(UC::COL_NM);
    }

    public function getClientNameAttribute(): string
    {
        $id = trim((string) ($this->getAttribute(PJC::COL_CLIENT_ID) ?? ''));
        if ($id === '' || !Str::isUuid($id)) return '';

        try {
            $row = DB::selectOne(
                'select ' . UC::COL_NM . ' as nm, first_name, last_name from ' . DC::TABLE_USERS . ' where id = ? limit 1',
                [$id]
            );
            if (!$row) return '';

            $nm = trim((string) ($row->nm ?? ''));
            if ($nm !== '') return $nm;

            $fn = trim((string) ($row->first_name ?? ''));
            $ln = trim((string) ($row->last_name ?? ''));
            return trim($fn . ' ' . $ln);
        } catch (\Throwable $e) {
            Log::warning(static::class . ' getClientNameAttribute failed: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'client_id' => $id,
            ]);
            return '';
        }
    }

    public function checkDateTime(): int
    {
        $start = $this->getAttribute(PJC::COL_S_DT);
        $duration = (int) ($this->getAttribute('duration') ?? 0);
        if ($start === null || $duration <= 0) return 0;

        try {
            return CarbonImmutable::parse($start)->addMinutes($duration)->gt(CarbonImmutable::now()) ? 1 : 0;
        } catch (\Throwable) {
            return 0;
        }
    }

    /**
     * Parse comma-separated user IDs and return User model array.
     */
    public function users(string $ids): array
    {
        $parsed = array_filter(array_map('trim', explode(',', $ids)));
        if (empty($parsed)) return [];

        return User::whereIn('id', $parsed)->get()->all();
    }
}
