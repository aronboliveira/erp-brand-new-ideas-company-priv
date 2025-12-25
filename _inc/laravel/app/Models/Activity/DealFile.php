<?php

namespace App\Models;

use App\Config\Constants\{ActivitiesConstants as AC, DatabaseConstants as DC};
use App\Enums\{FileCategory, MimeType};
use Illuminate\Database\Eloquent\{Relations\BelongsTo, SoftDeletes};
use Illuminate\Support\Facades\Log;

final class DealFile extends AbstractFile
{
    use SoftDeletes;

    protected $table = DC::TABLE_DL_FL;

    protected $with = ['deal'];

    protected $casts = [
        DC::COL_MM_TP  => MimeType::class,
        'size'         => 'integer',
        DC::COL_DL_CT  => 'integer',
        DC::COL_FL_SZ  => 'float',
        DC::COL_LA     => 'datetime',
        DC::COL_EXP_DT => 'datetime',
    ];

    protected $appends = [
        'category_label',
        'mime_type_value',
        'is_media',
    ];

    protected static function fillableFields(): array
    {
        return array_merge(parent::fillableFields(), [
            AC::COL_DL,     // deal_id
            DC::COL_FL_NM,  // file_name (legado)
        ]);
    }

    protected static function booted(): void
    {
        parent::booted();

        static::saving(function (self $m): void {
            try {
                $m->syncLegacyFileNameWithName();
            } catch (\Throwable $e) {
                Log::warning(static::class . ' saving hook failed', [
                    'id' => $m->getAttribute('id'),
                    'error' => $e->getMessage(),
                ]);
            }
        });
    }

    public function deal(): BelongsTo
    {
        return $this->belongsTo(Deal::class, AC::COL_DL, 'id');
    }

    public function getMimeTypeValueAttribute(): ?string
    {
        $mime = $this->getAttribute(DC::COL_MM_TP);
        return $mime instanceof MimeType ? $mime->value : (is_string($mime) ? $mime : null);
    }

    public function getCategoryLabelAttribute(): string
    {
        $cat = $this->getDerivedCategory();
        return $cat?->label() ?? '';
    }

    public function getIsMediaAttribute(): bool
    {
        $cat = $this->getDerivedCategory();
        return (bool) ($cat?->isMedia());
    }

    private function getDerivedCategory(): ?FileCategory
    {
        $mime = $this->getAttribute(DC::COL_MM_TP);

        $mimeEnum = $mime instanceof MimeType
            ? $mime
            : (is_string($mime) ? MimeType::normalize($mime) : null);

        if (!$mimeEnum) return FileCategory::Other;

        return FileCategory::fromMimeType($mimeEnum) ?? FileCategory::Other;
    }

    private function syncLegacyFileNameWithName(): void
    {
        $legacy = $this->getAttribute(DC::COL_FL_NM);
        $name   = $this->getAttribute('name');

        if (is_string($legacy)) $legacy = trim($legacy);
        if (is_string($name)) $name = trim($name);

        if ($legacy && !$name) {
            $this->setAttribute('name', $legacy);
            return;
        }

        if ($name && !$legacy) {
            $this->setAttribute(DC::COL_FL_NM, $name);
            return;
        }

        if ($legacy && $name && $legacy !== $name) {
            $this->setAttribute(DC::COL_FL_NM, $name);
        }
    }
}
