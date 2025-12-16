<?php

namespace App\Models;

use App\Config\Constants\{DatabaseConstants as DC, UsersConstants as UC};
use App\Enums\{AppModuleType, EvaluationStatus, MimeType};
use App\Traits\{HasAuditFields, NormalizesArrays, UsesUuids};
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentUpload extends Model
{
    use HasAuditFields, UsesUuids, NormalizesArrays;

    protected $table = DC::TABLE_DOC_UP;

    protected $fillable = [
        'name',
        'role',
        'description',
        'document',
        'module',
        'type',
        'status',
        'progress',
        DC::COL_RQ_SPC,
        DC::COL_OBJ_URL,
        'sha256',
        'md5',
        DC::COL_IS_ENC,
        DC::COL_ENC_ALG,
        DC::COL_MW_FREE,
        DC::COL_DOC_ID,
        UC::COL_USER_ID,
        'policy',
        'metadata',
        'tags',
        DC::COL_MW_SCAN,
    ];

    protected $guarded = [
        'id',
        DC::COL_TABLE_CREATOR,
    ];

    protected $casts = [
        'id'            => 'string',
        'name'          => 'string',
        'role'          => 'string',
        'description'   => 'string',
        'document'      => 'string',

        'module'        => AppModuleType::class,
        'type'          => MimeType::class,
        'status'        => EvaluationStatus::class,

        'progress'      => 'float',
        DC::COL_RQ_SPC  => 'float',

        DC::COL_OBJ_URL => 'string',
        'sha256'        => 'string',
        'md5'           => 'string',

        DC::COL_IS_ENC  => 'bool',
        DC::COL_ENC_ALG => 'string',
        DC::COL_MW_FREE => 'bool',

        DC::COL_DOC_ID  => 'string',
        UC::COL_USER_ID => 'string',

        'policy'        => 'array',
        'metadata'      => 'array',
        'tags'          => 'array',
        DC::COL_MW_SCAN => 'array',

        DC::COL_C_AT    => 'datetime',
        DC::COL_U_AT    => 'datetime',
    ];

    protected $with = [
        'document',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $model): void {
            $rawModule = $model->getAttribute('module');
            $moduleValue = $rawModule instanceof AppModuleType
                ? $rawModule->value
                : ($rawModule !== null ? (string) $rawModule : null);
            $moduleEnum = AppModuleType::normalize($moduleValue);
            $model->setAttribute('module', $moduleEnum->value);
            $rawType = $model->getAttribute('type');
            if ($rawType instanceof MimeType)
                $mimeEnum = $rawType;
            elseif ($rawType !== null && $rawType !== '')
                $mimeEnum = MimeType::normalize((string) $rawType) ?? MimeType::OTHER;
            else
                $mimeEnum = MimeType::OTHER;
            if (! $mimeEnum->isDocument())
                $mimeEnum = MimeType::OTHER;
            $model->setAttribute('type', $mimeEnum->value);
            $rawStatus = $model->getAttribute('status');
            $statusEnum = EvaluationStatus::normalize($rawStatus);
            $model->setAttribute('status', $statusEnum->value);
            $progress = (float) ($model->getAttribute('progress') ?? 0.0);
            if ($progress < 0.0)
                $progress = 0.0;
            elseif ($progress > 100.0)
                $progress = 100.0;
            $model->setAttribute('progress', $progress);
            $requiredStorageAttr = DC::COL_RQ_SPC;
            $requiredStorage = $model->getAttribute($requiredStorageAttr);
            if ($requiredStorage === null || $requiredStorage < 0)
                $model->setAttribute($requiredStorageAttr, 0.0);
            $isEncryptedAttr = DC::COL_IS_ENC;
            $encAlgAttr      = DC::COL_ENC_ALG;
            $isEncrypted = $model->getAttribute($isEncryptedAttr);
            if ($isEncrypted === null)
                $isEncrypted = false;
            $isEncrypted = (bool) $isEncrypted;
            $model->setAttribute($isEncryptedAttr, $isEncrypted);
            if (! $isEncrypted)
                $model->setAttribute($encAlgAttr, null);
            elseif ($model->getAttribute($encAlgAttr) !== null)
                $model->setAttribute($encAlgAttr, trim((string) $model->getAttribute($encAlgAttr)));
            $mwFreeAttr = DC::COL_MW_FREE;
            $mwFree = $model->getAttribute($mwFreeAttr);
            if ($mwFree === null)
                $model->setAttribute($mwFreeAttr, true);
            else
                $model->setAttribute($mwFreeAttr, (bool) $mwFree);
            foreach (['sha256', 'md5'] as $hashField) {
                $hash = $model->getAttribute($hashField);
                if ($hash !== null)
                    $model->setAttribute($hashField, strtolower(trim($hash)));
            }
            foreach (['policy', 'metadata', 'tags', DC::COL_MW_SCAN, DC::COL_ER_LG] as $jsonField)
                if (!is_array($model->getAttribute($jsonField)))
                    $model->setAttribute($jsonField, []);
        });
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class, DC::COL_DOC_ID, 'id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, UC::COL_USER_ID, 'id');
    }

    public function isEncrypted(): bool
    {
        return (bool) $this->getAttribute(DC::COL_IS_ENC);
    }

    public function isMalwareFree(): bool
    {
        return (bool) $this->getAttribute(DC::COL_MW_FREE);
    }
}
