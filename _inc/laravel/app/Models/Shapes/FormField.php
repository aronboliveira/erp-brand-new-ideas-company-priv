<?php

namespace App\Models;

use App\Config\Constants\{DatabaseConstants as DC, FormsConstants as FC};
use App\Traits\{
	DescribesClientField,
	DescribesHtmlLinkedEntity,
	HasAuditFields,
	NormalizesAddresses,
	UsesUuids
};
use Illuminate\Database\Eloquent\{Model};
use Illuminate\Database\Eloquent\Relations\{BelongsTo};
use Illuminate\Support\Facades\{Log, Schema};

class FormField extends Model
{
    use HasAuditFields, UsesUuids, NormalizesAddresses, DescribesClientField, DescribesHtmlLinkedEntity;

    protected $table = DC::TABLE_FM_FD;

    protected $with = [
        // 'form' removed: circular eager-load with FormBuilder->formFields->form
        'createdBy',
        'customQuestion',
    ];

    protected $fillable = [
        FC::COL_FM_ID,
        'email',
        FC::COL_CT_QT_ID,
        ...self::CLIENT_FIELD_COLS,
        'aria',
        'dataset',
        'selectors',
        'size',
        'tags',
    ];

    protected $guarded = [
        'id',
        DC::COL_TABLE_CREATOR,
    ];

    protected $casts = [
        'required'       => 'boolean',
        'readonly'       => 'boolean',
        'multiline'      => 'boolean',
        'multiple'       => 'boolean',
        'autocapitalize' => 'boolean',
        'autocomplete'   => 'boolean',
        'autocorrect'    => 'boolean',
        'disabled'       => 'boolean',
        'tags'           => 'array',
        'options'        => 'array',
        'optgroups'      => 'array',
        'accepts'        => 'array',
        'aria'           => 'array',
        'dataset'        => 'array',
        'selectors'      => 'array',
        'size'           => 'array',
        DC::COL_C_AT     => 'datetime',
        DC::COL_U_AT     => 'datetime',
    ];

    protected $appends = [
        'resolved_client_payload',
        'resolved_constraints',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $model): void {
            try {
                if (Schema::hasTable($model->getTable()) && Schema::hasColumn($model->getTable(), 'email')) {
                    $model->setAttribute(
                        'email',
                        static::normalizeEmail(
                            $model->getAttribute('email'),
                            'form_field.email',
                            $model->getAttribute('id')
                        )
                    );
                }
            } catch (\Throwable $e) {
                Log::warning(static::class . ' failed to normalize email before saving', [
                    'table' => $model->getTable(),
                    'id'    => $model->getAttribute('id'),
                    'email' => $model->getAttribute('email'),
                    'error' => $e->getMessage(),
                    'file'  => $e->getFile(),
                    'line'  => $e->getLine(),
                ]);
            }
        });
    }

    public function form(): BelongsTo
    {
        return $this->belongsTo(FormBuilder::class, FC::COL_FM_ID, 'id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, DC::COL_TABLE_CREATOR, 'id');
    }

    public function customQuestion(): BelongsTo
    {
        return $this->belongsTo(CustomQuestion::class, FC::COL_CT_QT_ID, 'id');
    }

        public function getResolvedClientPayloadAttribute(): array
    {
            try {
            $base = [];
            $cq = $this->getCachedCustomQuestion();

            if ($cq) $base = $cq->getAttribute('resolved_client_payload') ?? [];

            $local = array_merge($this->getClientFieldAttributes(), $this->getHtmlLinkedAttributes(true));
            $merged = static::overlayIfMeaningful($base, $local);

            if (Schema::hasColumn($this->getTable(), 'email'))
                $merged['email'] = $this->getAttribute('email');

            return static::normalizeClientFieldPayload($merged);
            } catch (\Throwable $e) {
                Log::error(static::class . '::getResolvedClientPayloadAttribute — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                return [];
            }
    }

    public function getResolvedConstraintsAttribute(): array
    {
        try {
            $payload = $this->getResolvedClientPayloadAttribute();

            $constraints = [];
            foreach (static::clientFieldConstraintColumns() as $k) {
                if (!array_key_exists($k, $payload)) continue;
                $constraints[$k] = $payload[$k];
            }

                    foreach (['aria', 'dataset'] as $k) {
                if (!array_key_exists($k, $payload)) continue;
                $constraints[$k] = $payload[$k];
            }

            return $constraints;
        } catch (\Throwable $e) {
            Log::error(static::class . '::getResolvedConstraintsAttribute — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
            return [];
        }
    }

    protected function getCachedCustomQuestion(): ?\App\Models\CustomQuestion
    {
        try {
            if ($this->relationLoaded('customQuestion')) return $this->getRelation('customQuestion');
            try {
                return $this->customQuestion()->first();
            } catch (\Throwable $e) {
                Log::warning(static::class . ' failed to fetch linked customQuestion', [
                    'id'   => $this->getAttribute('id'),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'err'  => $e->getMessage(),
                ]);
                return null;
            }
        } catch (\Throwable $e) {
            Log::error(static::class . '::getCachedCustomQuestion — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
            return null;
        }
    }

        public function isEffectivelyWritable(): bool
    {
            try {
            $p = $this->getResolvedClientPayloadAttribute();
            $disabled = (bool)($p['disabled'] ?? false);
            $readonly = (bool)($p['readonly'] ?? false);
            return !$disabled && !$readonly;
            } catch (\Throwable $e) {
                Log::error(static::class . '::isEffectivelyWritable — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                return false;
            }
    }
}
