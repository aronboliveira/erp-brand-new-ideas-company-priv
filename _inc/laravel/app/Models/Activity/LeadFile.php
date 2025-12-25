<?php

namespace App\Models;

use App\Config\Constants\{DatabaseConstants as DC, ProjectsConstants as PJC};
use App\Enums\{DocumentKind, FileCategory, MimeType};
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Log;

final class LeadFile extends AbstractFile
{
    use HasFactory;

    protected $table = DC::TABLE_LD_FILES;

    protected $with = ['lead'];

    protected $casts = [
        DC::COL_MM_TP  => MimeType::class,
        'type'         => DocumentKind::class,
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
            PJC::COL_LD_ID,     // lead_id
            DC::COL_FL_NM,      // file_name (legado)
        ]);
    }

    protected static function booted(): void
    {
        parent::booted();

        static::saving(function (self $m): void {
            try {
                $m->syncLegacyFileNameWithName();
                $m->applyLeadMimeFallback();
                $m->ensureDocumentKindWhenDocument();
            } catch (\Throwable $e) {
                Log::warning(static::class . ' saving hook failed', [
                    'id' => $m->getAttribute('id'),
                    'error' => $e->getMessage(),
                ]);
            }
        });
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class, PJC::COL_LD_ID, 'id');
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

    public function isDownloadable(): bool
    {
        return !$this->is_expired;
    }

    public function incrementDownloadCount(): void
    {
        $current = (int) ($this->getAttribute(DC::COL_DL_CT) ?? 0);
        $this->setAttribute(DC::COL_DL_CT, $current + 1);
    }

    public function touchLastAccessed(): void
    {
        $this->setAttribute(DC::COL_LA, now());
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

    private function applyLeadMimeFallback(): void
    {
        $mime = $this->getAttribute(DC::COL_MM_TP);
        $mimeEnum = $mime instanceof MimeType
            ? $mime
            : (is_string($mime) ? MimeType::normalize($mime) : null);
        if (!$mimeEnum || $mimeEnum === MimeType::OTHER)
            $this->setAttribute(DC::COL_MM_TP, MimeType::APPLICATION_OCTET_STREAM->value);
    }

    private function ensureDocumentKindWhenDocument(): void
    {
        $mime = $this->getAttribute(DC::COL_MM_TP);

        $mimeEnum = $mime instanceof MimeType
            ? $mime
            : (is_string($mime) ? MimeType::normalize($mime) : null);

        if (!$mimeEnum || !$mimeEnum->isDocument()) {
            $this->setAttribute('type', null);
            return;
        }

        $current = $this->getAttribute('type');
        if ($current instanceof DocumentKind) return;

        if (is_string($current) && DocumentKind::normalize($current)) return;

        $ext = (string) ($this->getAttribute('extension') ?? '');
        $kind = $ext !== '' ? DocumentKind::fromExtension($ext) : null;

        $this->setAttribute('type', ($kind ?? DocumentKind::OTHER)->value);
    }
}
