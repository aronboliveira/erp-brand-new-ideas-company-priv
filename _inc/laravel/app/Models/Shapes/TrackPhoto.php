<?php

namespace App\Models;

use App\Config\Constants\{DatabaseConstants as DC, ProjectsConstants as PJC, UsersConstants as UC};
use App\Enums\Visibility;
use App\Traits\{FiltersSecureAttachments, HasAuditFields, UsesUuids};
use Illuminate\Database\Eloquent\{Factories\HasFactory, Model, Relations\BelongsTo};
use Illuminate\Support\Facades\Log;

class TrackPhoto extends Model
{
    use UsesUuids, HasAuditFields, HasFactory, FiltersSecureAttachments;

    protected $table = DC::TABLE_TRK_PHT;

    protected $guarded = [
        'id',
        DC::COL_TABLE_CREATOR,
    ];

    protected $fillable = [
        PJC::COL_TRK_ID,
        UC::COL_USER_ID,
        PJC::COL_IMG_PATH,
        'url',
        'time',
        'visibility',
        'status',
    ];

    protected $casts = [
        'time' => 'datetime',
    ];

    protected $appends = [
        'visibility_label',
        'is_public',
        'is_restricted',
    ];

    protected $with = [
        'user',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $m): void {
            try {
                $trk = trim((string) $m->getAttribute(PJC::COL_TRK_ID));
                $m->setAttribute(PJC::COL_TRK_ID, $trk === '' ? null : $trk);

                $userId = trim((string) $m->getAttribute(UC::COL_USER_ID));
                $m->setAttribute(UC::COL_USER_ID, $userId === '' ? null : $userId);

                $img = trim((string) $m->getAttribute(PJC::COL_IMG_PATH));
                $m->setAttribute(PJC::COL_IMG_PATH, $img === '' ? null : $img);

                $url = trim((string) $m->getAttribute('url'));
                $m->setAttribute('url', $url === '' ? null : $url);

                $status = trim((string) $m->getAttribute('status'));
                $m->setAttribute('status', $status === '' ? null : $status);

                $m->setAttribute('visibility', $m->visibilityEnum()->value);
            } catch (\Throwable $e) {
                Log::error(self::class . ' saving normalization failed', [
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'error' => $e->getMessage(),
                    'table' => $m->getTable(),
                    'model_id' => $m->getAttribute('id') ?? null,
                ]);
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, UC::COL_USER_ID, 'id');
    }

    public function visibilityEnum(): Visibility
    {
        try {
            $raw = $this->getAttribute('visibility');
            $norm = Visibility::normalize($raw instanceof Visibility ? $raw : $raw);
            return $norm ?? Visibility::Private;
        } catch (\Throwable $e) {
            Log::warning(self::class . ' failed normalizing visibility enum', [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'error' => $e->getMessage(),
                'model_id' => $this->getAttribute('id') ?? null,
                'raw' => $this->getAttribute('visibility'),
            ]);
            return Visibility::Private;
        }
    }

    public function getVisibilityLabelAttribute(): string
    {
        try {
            return $this->visibilityEnum()->label();
        } catch (\Throwable $e) {
            Log::debug(self::class . ' visibility label fallback', [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'error' => $e->getMessage(),
                'model_id' => $this->getAttribute('id') ?? null,
            ]);
            return (string) ($this->getAttribute('visibility') ?? Visibility::Private->value);
        }
    }

    public function getIsPublicAttribute(): bool
    {
        return $this->visibilityEnum()->isPublic();
    }

    public function getIsRestrictedAttribute(): bool
    {
        return $this->visibilityEnum()->isRestricted();
    }

    public function markTimeNow(): void
    {
        $this->setAttribute('time', now());
    }

    public function markVisibility(Visibility|string|null $visibility): void
    {
        $enum = $visibility instanceof Visibility ? $visibility : (Visibility::normalize($visibility) ?? Visibility::Private);
        $this->setAttribute('visibility', $enum->value);
    }

    public function hasImage(): bool
    {
        return trim((string) $this->getAttribute(PJC::COL_IMG_PATH)) !== '' || trim((string) $this->getAttribute('url')) !== '';
    }
}
