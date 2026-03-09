<?php

namespace App\Models;

use App\Config\Constants\{DatabaseConstants as DC, ProjectsConstants as PJC, UsersConstants as UC};
use App\Traits\{FiltersSecureAttachments, HasAuditFields, UsesUuids};
use Illuminate\Database\Eloquent\{Model, Relations\BelongsTo};
use Illuminate\Support\Facades\{Cache, DB, Log};
use Illuminate\Support\Str;

class ContractNotes extends Model
{
    use UsesUuids, HasAuditFields, FiltersSecureAttachments;

    protected $table = DC::TABLE_CTC_NTS;

    protected $guarded = ['id', DC::COL_TABLE_CREATOR, DC::COL_TABLE_UPDATER];

    protected $fillable = [
        'code',
        PJC::COL_CTC_ID,
        UC::COL_USER_ID,
        'notes',
    ];

    protected $with = ['user', 'contract'];

    protected $casts = [
        PJC::COL_CTC_ID  => 'string',
        UC::COL_USER_ID  => 'string',
        'code'           => 'string',
        'notes'          => 'string',
        'created_at'     => 'datetime',
        'updated_at'     => 'datetime',
    ];

    protected $appends = [
        'notes_parts',
        'notes_has_attachments',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $m): void {
            try {
                self::ensureCode($m);
                self::normalizeNotes($m);
            } catch (\Throwable $e) {
                Log::error(static::class . ' saving normalization failed', [
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'error' => $e->getMessage(),
                    'model_id' => $m->getKey(),
                    'table' => $m->getTable(),
                ]);
            }
        });
    }

    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class, PJC::COL_CTC_ID, 'id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, UC::COL_USER_ID, 'id');
    }

    public function getNotesPartsAttribute(): array
    {
        $raw = $this->getAttribute('notes');
        $raw = is_string($raw) ? trim($raw) : trim((string) $raw);

        if ($raw === '')
            return [];

        if (!self::notesLooksLikeList($raw))
            return [$raw];

        $parts = array_map(static fn($v) => trim((string) $v), explode(',', $raw));
        $out = [];
        foreach ($parts as $p)
            if ($p !== '')
                $out[] = $p;

        return $out;
    }

    public function getNotesHasAttachmentsAttribute(): bool
    {
        $parts = $this->getAttribute('notes_parts');
        if (!is_array($parts))
            $parts = $this->getNotesPartsAttribute();

        foreach ($parts as $p) {
            $s = trim((string) $p);
            if ($s === '')
                continue;
            if (Str::isUuid($s) || str_starts_with($s, 'https://') || preg_match('/^(att:\/\/|file:\/\/|blob:|data:)/i', $s))
                return true;
        }

        return false;
    }

    public static function countForContractCached(string $contractId, int $ttlSeconds = 300): int
    {
        $contractId = trim($contractId);
        if ($contractId === '')
            return 0;

        $key = 'ctc_notes_count:' . $contractId;

        return (int) Cache::remember($key, $ttlSeconds, function () use ($contractId): int {
            try {
                $table = DC::TABLE_CTC_NTS;
                $col = PJC::COL_CTC_ID;
                $row = DB::selectOne("select count(*) as c from {$table} where {$col} = ? limit 1", [$contractId]);
                return (int) ($row->c ?? 0);
            } catch (\Throwable $e) {
                Log::warning(static::class . ' countForContractCached failed', [
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'error' => $e->getMessage(),
                    'contract_id' => $contractId,
                ]);
                return 0;
            }
        });
    }

    private static function ensureCode(self $m): void
    {
        $raw = $m->getAttribute('code');
        $code = is_string($raw) ? trim($raw) : trim((string) $raw);

        if ($code !== '' && preg_match('/^CTC\-NTS\-[0-9a-f]{8}\-[0-9a-f]{4}\-[0-9a-f]{4}\-[0-9a-f]{4}\-[0-9a-f]{12}$/i', $code) === 1)
            return;

        $attempt = 0;
        $maxAttempts = 25;
        $candidate = null;
        $exists = true;

        do {
            $candidate = 'CTC-NTS-' . Str::uuid();
            $exists = false;

            try {
                $exists = DB::table($m->getTable())
                    ->where('code', $candidate)
                    ->where('id', '!=', (string) ($m->getAttribute('id') ?? ''))
                    ->exists();
            } catch (\Throwable $e) {
                Log::warning(static::class . ' code uniqueness check failed', [
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'error' => $e->getMessage(),
                    'candidate' => $candidate,
                    'table' => $m->getTable(),
                    'model_id' => $m->getKey(),
                ]);
                $exists = false;
            }

            $attempt++;
        } while ($exists && $attempt < $maxAttempts);

        if ($exists)
            Log::error(static::class . ' failed generating unique code (attempt limit)', [
                'table' => $m->getTable(),
                'model_id' => $m->getKey(),
                'last_candidate' => $candidate,
                'attempts' => $attempt,
            ]);

        $m->setAttribute('code', $candidate);
    }

    private static function normalizeNotes(self $m): void
    {
        $raw = $m->getAttribute('notes');
        if ($raw === null)
            return;

        $rawStr = is_string($raw) ? trim($raw) : trim((string) $raw);
        if ($rawStr === '') {
            $m->setAttribute('notes', null);
            return;
        }

        $isList = self::notesLooksLikeList($rawStr);
        $parts = $isList ? explode(',', $rawStr) : [$rawStr];

        $out = [];
        foreach ($parts as $p) {
            $p = trim((string) $p);
            if ($p === '')
                continue;

            $normalized = self::normalizeNotePart($p, $m);
            if ($normalized === null)
                continue;

            $out[] = $normalized;
        }

        if (!$out) {
            $m->setAttribute('notes', null);
            return;
        }

        $m->setAttribute('notes', $isList ? implode(', ', $out) : (string) $out[0]);
    }

    private static function normalizeNotePart(string $value, self $m): ?string
    {
        $v = trim($value);
        if ($v === '')
            return null;

        if (Str::isUuid($v)) {
            $resolved = self::resolveUuidToNoteText($v);
            return $resolved !== null ? $resolved : $v;
        }

        if (str_starts_with($v, 'https://'))
            return self::validateSafeUrl($v);

        if (preg_match('/^(att:\/\/|file:\/\/|blob:|data:)/i', $v) === 1 || str_contains($v, '/') || str_contains($v, '\\'))
            return self::validatePath($v);

        return $v;
    }

    private static function resolveUuidToNoteText(string $uuid): ?string
    {
        $uuid = trim($uuid);
        if ($uuid === '' || !Str::isUuid($uuid))
            return null;

        $tables = [
            DC::TABLE_NOTES,
            DC::TABLE_CTC_ATC,
            DC::TABLE_DOCS,
        ];

        foreach ($tables as $table) {
            try {
                $row = DB::selectOne("select * from {$table} where id = ? limit 1", [$uuid]);
                if (!$row)
                    continue;

                $text = self::extractNoteTextFromRow($row);
                if ($text !== null)
                    return $text;
            } catch (\Throwable $e) {
                Log::notice(static::class . ' resolveUuidToNoteText lookup failed', [
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'error' => $e->getMessage(),
                    'uuid' => $uuid,
                    'table' => $table,
                ]);
                continue;
            }
        }

        return null;
    }

    private static function extractNoteTextFromRow(object $row): ?string
    {
        $candidates = [
            'notes',
            'note',
            'content',
            'text',
            'description',
            'title',
            'name',
            'url',
            DC::COL_FL_PT,
            'file_path',
            'path',
        ];

        foreach ($candidates as $k) {
            if (!property_exists($row, $k))
                continue;

            $v = trim((string) ($row->{$k} ?? ''));
            if ($v !== '')
                return $v;
        }

        return null;
    }

    private static function notesLooksLikeList(string $value): bool
    {
        if (!str_contains($value, ','))
            return false;

        return preg_match('/https:\/\/|att:\/\/|file:\/\/|blob:|data:|[0-9a-f]{8}\-[0-9a-f]{4}\-[0-9a-f]{4}\-[0-9a-f]{4}\-[0-9a-f]{12}/i', $value) === 1;
    }
}
