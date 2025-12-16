<?php

namespace App\Models;

use App\Config\Constants\{
    DatabaseConstants as DC,
    ProjectsConstants as PJC
};
use App\Enums\{
    FileCategory,
    MimeType
};
use App\Traits\{
    HasAuditFields,
    NormalizesArrays,
    UsesUuids
};
use Illuminate\Database\Eloquent\{
    Factories\HasFactory,
    Model
};
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

final class LeadFile extends Model
{
    use HasFactory;
    use HasAuditFields;
    use NormalizesArrays;
    use UsesUuids;

    protected $table = DC::TABLE_LD_FILES;

    protected $fillable = [
        PJC::COL_LD_ID,       // lead_id
        DC::COL_FL_NM,        // file_name
        DC::COL_FL_PT,        // file_path
        'extension',
        DC::COL_MM_TP,        // mime_type
        DC::COL_LA,           // last_accessed_at
        'type',               // FileCategory
        'size',               // raw size if needed
        'description',
        'notes',
        DC::COL_DL_CT,        // download_count
        DC::COL_FL_SZ,        // file_size (bytes)
        DC::COL_EXP_DT,       // expiration_date
        DC::COL_PERM_RLS,     // permission_rules
        'executors',
        'editors',
        'viewers',
    ];

    protected $guarded = [
        'id',
        DC::COL_TABLE_CREATOR,
        DC::COL_TABLE_UPDATER,
    ];

    protected $casts = [
        DC::COL_MM_TP   => MimeType::class,
        'type'          => FileCategory::class,
        'size'          => 'integer',
        DC::COL_DL_CT   => 'integer',
        DC::COL_FL_SZ   => 'float',
        DC::COL_LA      => 'datetime',
        DC::COL_EXP_DT  => 'datetime',
        'executors'     => 'array',
        'editors'       => 'array',
        'viewers'       => 'array',
    ];

    protected $with = [
        'lead',
    ];

    protected $appends = [
        'category_label',
        'mime_type_value',
        'is_expired',
        'is_media',
    ];

    protected static function booted(): void
    {
        parent::booted();

        static::saving(function (LeadFile $model): void {
            $model->normalizeMimeAndCategory();
            $model->normalizeActorLists();
            $model->normalizePermissionRules();
            $model->ensureJsonAttributesAreEncoded([
                'executors',
                'editors',
                'viewers',
            ]);
        });
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class, PJC::COL_LD_ID, 'id');
    }

    public function getCategoryLabelAttribute(): string
    {
        $type = $this->getAttribute('type');
        if ($type instanceof FileCategory)
            return $type->label();
        if (is_string($type) && $type !== '') {
            $normalized = FileCategory::normalize($type);
            return $normalized?->label() ?? $type;
        }

        return '';
    }

    public function getMimeTypeValueAttribute(): ?string
    {
        $mime = $this->getAttribute(DC::COL_MM_TP);
        if ($mime instanceof MimeType)
            return $mime->value;
        return is_string($mime) ? $mime : null;
    }

    public function getIsExpiredAttribute(): bool
    {
        $expiresAt = $this->getAttribute(DC::COL_EXP_DT);
        if ($expiresAt === null)
            return false;
        return $expiresAt->isPast();
    }

    public function getIsMediaAttribute(): bool
    {
        $type = $this->getAttribute('type');
        if ($type instanceof FileCategory)
            return $type->isMedia();
        if (is_string($type) && $type !== '') {
            $normalized = FileCategory::normalize($type);
            if ($normalized !== null)
                return $normalized->isMedia();
        }
        $mime = $this->getAttribute(DC::COL_MM_TP);
        if ($mime instanceof MimeType)
            return FileCategory::fromMimeType($mime)->isMedia();
        return false;
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
        $this->{DC::COL_LA} = now();
    }

    private function normalizeMimeAndCategory(): void
    {
        $mimeAttr = $this->getAttribute(DC::COL_MM_TP);
        $mimeEnum = null;
        if ($mimeAttr instanceof MimeType)
            $mimeEnum = $mimeAttr;
        elseif (is_string($mimeAttr) && $mimeAttr !== '')
            $mimeEnum = MimeType::normalize($mimeAttr);
        if (!$mimeEnum) {
            $ext = $this->getAttribute('extension');
            if (is_string($ext) && $ext !== '')
                $mimeEnum = MimeType::fromExtension($ext);
        }

        if (!$mimeEnum)
            $mimeEnum = MimeType::APPLICATION_OCTET_STREAM;
        $this->setAttribute(DC::COL_MM_TP, $mimeEnum);
        $typeAttr = $this->getAttribute('type');
        $category = null;
        if ($typeAttr instanceof FileCategory)
            $category = $typeAttr;
        elseif (is_string($typeAttr) && $typeAttr !== '')
            $category = FileCategory::normalize($typeAttr);
        if (!$category && $mimeEnum instanceof MimeType)
            $category = FileCategory::fromMimeType($mimeEnum);
        if (!$category)
            $category = FileCategory::Other;
        $this->setAttribute('type', $category);
    }

    /**
     * Normaliza executors/editors/viewers:
     * - aceita array, CSV, JSON etc.
     * - extrai ID de 'id', '<singular>_id' (executor_id, editor_id, viewer_id) ou 'user_id'
     * - filtra apenas usuários existentes na tabela users
     */
    private function normalizeActorLists(): void
    {
        $columns = ['executors', 'editors', 'viewers'];
        $allIds = [];
        $parsedByColumn = [];
        foreach ($columns as $column) {
            $raw = $this->getAttribute($column);
            $items = $this->parseActorRawItems($raw, $column);
            $ids = [];
            foreach ($items as $item) {
                $id = $this->extractActorId($item, $column);
                if ($id !== null) {
                    $ids[] = $id;
                    $allIds[$id] = true;
                }
            }

            $parsedByColumn[$column] = $ids;
        }

        if ($allIds === []) {
            foreach ($columns as $column)
                $this->setAttribute($column, []);
            return;
        }
        $validIds = User::query()
            ->whereIn('id', array_keys($allIds))
            ->pluck('id')
            ->all();
        $validSet = array_flip($validIds);
        foreach ($columns as $column) {
            $final = [];
            foreach ($parsedByColumn[$column] as $id)
                if (isset($validSet[$id]))
                    $final[$id] = true;
            $this->setAttribute($column, array_keys($final));
        }
    }

    /**
     * Converte o valor cru vindo do atributo (array/string/null)
     * em uma lista de "itens" (strings ou arrays) a serem analisados.
     */
    private function parseActorRawItems(mixed $raw, string $column): array
    {
        if ($raw === null)
            return [];
        if (is_array($raw))
            return $raw;
        if (is_string($raw)) {
            $raw = trim($raw);
            if ($raw === '')
                return [];
            if (str_starts_with($raw, '[') || str_starts_with($raw, '{')) {
                $decoded = json_decode($raw, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    if (is_array($decoded))
                        return array_keys($decoded) !== range(0, count($decoded) - 1) ? [$decoded] : $decoded;
                    return [$decoded];
                }
            }
            $parts = array_filter(array_map('trim', explode(',', $raw)));
            return $parts;
        }

        return [];
    }

    private function extractActorId(mixed $item, string $column): ?string
    {
        if (is_string($item)) {
            $id = trim($item);
            return $id === '' ? null : $id;
        }
        if (is_array($item))
            $data = $item;
        elseif (is_object($item))
            $data = (array) $item;
        else
            return null;
        $candidates = ['id'];
        $singular = rtrim($column, 's');
        if ($singular !== '')
            $candidates[] = $singular . '_id';
        $candidates[] = 'user_id';
        foreach ($candidates as $key)
            if (!empty($data[$key]) && is_string($data[$key])) {
                $id = trim($data[$key]);
                if ($id !== '')
                    return $id;
            }
        return null;
    }

    private function normalizePermissionRules(): void
    {
        $raw = $this->getAttribute(DC::COL_PERM_RLS);
        if ($raw === null)
            return;
        $value = trim((string) $raw);
        if ($value === '') {
            $this->setAttribute(DC::COL_PERM_RLS, null);
            return;
        }
        if (!preg_match('/^[0-7]+$/', $value)) {
            $this->setAttribute(DC::COL_PERM_RLS, $value);
            return;
        }
        if (strlen($value) < 6)
            $value = str_pad($value, 6, '0', STR_PAD_LEFT);
        $this->setAttribute(DC::COL_PERM_RLS, $value);
    }
}
