<?php

namespace App\Models;

use App\Config\Constants\{ActivitiesConstants as AC, DatabaseConstants as DC};
use App\Traits\{DefinesDates, HasAuditFields, UsesUuids};
use Illuminate\Database\Eloquent\{
    Factories\HasFactory,Model};
use Illuminate\Database\Eloquent\Relations\{BelongsTo};
use Illuminate\Support\{Carbon};
use Illuminate\Support\Facades\{Log};

class JobApplicationNote extends Model
{
    use HasFactory;
    use UsesUuids, HasAuditFields, DefinesDates;

    protected $table = DC::TABLE_JB_AP_NTS;

    protected $fillable = [
        'author',
        AC::COL_AUTHOR_ID,
        AC::COL_WRT_AT,
        AC::COL_APLN_ID,
        AC::COL_NOTE_CREATED,
        'note',
        'reviewer',
        AC::COL_RVW_AT,
    ];

    protected $with = [
        'application',
        'authorUser',
        'reviewerUser',
        'noteRow',
    ];

    protected $appends = [
        'note_resolved',
        'author_resolved',
        'reviewer_resolved',
    ];

    protected $casts = [
        AC::COL_WRT_AT => 'datetime',
        AC::COL_RVW_AT => 'datetime',
    ];

    protected array $runtimeCache = [];

    protected static function booted(): void
    {
        static::saving(function (self $m): void {
            $author = is_scalar($m->getAttribute('author') ?? null) ? trim((string) $m->getAttribute('author')) : '';
            $authorId = is_scalar($m->getAttribute(AC::COL_AUTHOR_ID) ?? null) ? trim((string) $m->getAttribute(AC::COL_AUTHOR_ID)) : '';

            if ($author === '' && $authorId === '') {
                $m->setAttribute(AC::COL_WRT_AT, null);
            } elseif ($m->getAttribute(AC::COL_WRT_AT) === null) {
                $m->setAttribute(AC::COL_WRT_AT, Carbon::now());
            }

            $reviewerId = is_scalar($m->getAttribute('reviewer') ?? null) ? trim((string) $m->getAttribute('reviewer')) : '';
            if ($reviewerId === '') {
                $m->setAttribute(AC::COL_RVW_AT, null);
            } elseif ($m->getAttribute(AC::COL_RVW_AT) === null) {
                $m->setAttribute(AC::COL_RVW_AT, Carbon::now());
            }
        });
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(JobApplication::class, AC::COL_APLN_ID, 'id');
    }

    public function authorUser(): BelongsTo
    {
        return $this->belongsTo(User::class, AC::COL_AUTHOR_ID, 'id');
    }

    public function reviewerUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer', 'id');
    }

    public function noteRow(): BelongsTo
    {
        return $this->belongsTo(Note::class, AC::COL_NOTE_CREATED, 'id');
    }

    public function noteCreated(): BelongsTo
    {
        return $this->noteRow();
    }

    public function getNoteResolvedAttribute(): ?string
    {
        try {
            return $this->cacheOnce('note_resolved', function (): ?string {
                $note = is_scalar($this->getAttribute('note') ?? null) ? trim((string) $this->getAttribute('note')) : '';
                if ($note !== '') return $note;

                $row = $this->getRelationValue('noteRow');
                if (!$row) return null;

                foreach (['note', 'content', 'body', 'text', 'description'] as $col) {
                    $v = $row->getAttribute($col);
                    if (is_scalar($v) && trim((string) $v) !== '') return trim((string) $v);
                }

                return null;
            });
        } catch (\Throwable $e) {
            Log::error(static::class . '::getNoteResolvedAttribute — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
            return '';
        }
    }

    public function getAuthorResolvedAttribute(): ?string
    {
        try {
            return $this->cacheOnce('author_resolved', function (): ?string {
                $author = is_scalar($this->getAttribute('author') ?? null) ? trim((string) $this->getAttribute('author')) : '';
                if ($author !== '') return $author;

                $u = $this->getRelationValue('authorUser');
                $name = $u?->getAttribute('name');
                return is_scalar($name) && trim((string) $name) !== '' ? trim((string) $name) : null;
            });
        } catch (\Throwable $e) {
            Log::error(static::class . '::getAuthorResolvedAttribute — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
            return '';
        }
    }

    public function getReviewerResolvedAttribute(): ?string
    {
        try {
            return $this->cacheOnce('reviewer_resolved', function (): ?string {
                $u = $this->getRelationValue('reviewerUser');
                $name = $u?->getAttribute('name');
                return is_scalar($name) && trim((string) $name) !== '' ? trim((string) $name) : null;
            });
        } catch (\Throwable $e) {
            Log::error(static::class . '::getReviewerResolvedAttribute — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
            return '';
        }
    }

    protected function cacheOnce(string $key, callable $fn): mixed
    {
        if (array_key_exists($key, $this->runtimeCache)) return $this->runtimeCache[$key];
        return $this->runtimeCache[$key] = $fn();
    }
}
