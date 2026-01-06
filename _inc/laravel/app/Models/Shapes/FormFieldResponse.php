<?php

namespace App\Models;

use App\Config\Constants\{DatabaseConstants as DC, FormsConstants as FC, UsersConstants as UC};
use App\Enums\FieldType;
use App\Traits\{HasAuditFields, NormalizesArrays, UsesUuids};
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\{Log, Schema};

class FormFieldResponse extends Model
{
    use UsesUuids, HasAuditFields, NormalizesArrays;

    protected $table = DC::TABLE_FM_FLD_RSP;

    protected $with = [
        'form',
        'user',
    ];

    protected $guarded = [
        'id',
        DC::COL_TABLE_CREATOR,
        DC::COL_TABLE_UPDATER,
    ];

    protected $fillable = [
        'type',
        FC::COL_FM_ID,
        UC::COL_USER_ID,
        FC::COL_FM_DT_RSP_ID,
        'name',
        FC::COL_HTML_LB,
        FC::COL_HTML_ID,
        'value',
        'checked',
        FC::COL_NM_ID,
        FC::COL_SUBJ_ID,
        FC::COL_EML_ID,
        FC::COL_PPL_ID,
        'metadata',
    ];

    protected $casts = [
        'checked'           => 'boolean',
        'metadata'          => 'array',
        DC::COL_C_AT        => 'datetime',
        DC::COL_U_AT        => 'datetime',
    ];

    protected $appends = [
        'effective_type',
        'is_checkable',
        'effective_checked',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $m): void {
            try {
                $m->normalizeResponseColumns();
                $m->encodeJsonSafety();
                $m->alignCheckedWithType();
            } catch (\Throwable $e) {
                Log::warning(static::class . ' failed to normalize before saving', [
                    'table' => $m->getTable(),
                    'id'    => $m->getAttribute('id'),
                    'err'   => $e->getMessage(),
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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, UC::COL_USER_ID, 'id');
    }

    public function formDataResponse(): BelongsTo
    {
        return $this->belongsTo(FormResponse::class, FC::COL_FM_DT_RSP_ID, 'id');
    }

    public function nameField(): BelongsTo
    {
        return $this->belongsTo(FormField::class, FC::COL_NM_ID, 'id');
    }

    public function subjectField(): BelongsTo
    {
        return $this->belongsTo(FormField::class, FC::COL_SUBJ_ID, 'id');
    }

    public function emailField(): BelongsTo
    {
        return $this->belongsTo(FormField::class, FC::COL_EML_ID, 'id');
    }

    public function pipeline(): BelongsTo
    {
        return $this->belongsTo(Pipeline::class, FC::COL_PPL_ID, 'id');
    }

    public function getEffectiveTypeAttribute(): string
    {
        return FieldType::normalize($this->getAttribute('type'))->value;
    }

    public function getIsCheckableAttribute(): bool
    {
        return FieldType::normalize($this->getAttribute('type'))->isCheckable();
    }

    public function getEffectiveCheckedAttribute(): ?bool
    {
        $t = FieldType::normalize($this->getAttribute('type'));
        if (!$t->isCheckable()) return null;

        $raw = $this->getAttribute('checked');
        if ($raw === null || $raw === '') return null;

        return (bool) $raw;
    }

    private function normalizeResponseColumns(): void
    {
        $this->setAttribute('type', FieldType::normalize($this->getAttribute('type'))->value);

        foreach (['name', FC::COL_HTML_LB, FC::COL_HTML_ID] as $col) {
            if (!Schema::hasColumn($this->getTable(), $col)) continue;

            $raw = $this->getAttribute($col);
            if ($raw === null) continue;

            $v = trim((string) $raw);
            $this->setAttribute($col, $v === '' ? null : $v);
        }

        if (Schema::hasColumn($this->getTable(), 'value')) {
            $raw = $this->getAttribute('value');
            if ($raw !== null) {
                $v = trim((string) $raw);
                $this->setAttribute('value', $v === '' ? null : $v);
            }
        }
    }

    private function encodeJsonSafety(): void
    {
        $this->ensureJsonAttributesAreEncoded(['metadata']);
    }

    private function alignCheckedWithType(): void
    {
        if (!Schema::hasColumn($this->getTable(), 'checked')) return;

        $type = FieldType::normalize($this->getAttribute('type'));

        if (!$type->isCheckable()) {
            $this->setAttribute('checked', null);
            return;
        }

        $raw = $this->getAttribute('checked');
        if ($raw === null || $raw === '') return;

        $this->setAttribute('checked', (bool) $raw);
    }
}
