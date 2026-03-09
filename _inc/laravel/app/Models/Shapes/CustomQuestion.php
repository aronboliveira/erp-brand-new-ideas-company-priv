<?php

namespace App\Models;

use App\Config\Constants\{DatabaseConstants as DC, FormsConstants as FC};
use App\Traits\{DescribesClientField, DescribesHtmlLinkedEntity, HasAuditFields, UsesUuids};
use Illuminate\Database\Eloquent\{Model, Relations\BelongsTo};
use Illuminate\Support\Facades\Schema;
/**
 * @property bool|null $is_required
 * @property string|null $question
 */

class CustomQuestion extends Model
{
    use HasAuditFields, UsesUuids, DescribesClientField, DescribesHtmlLinkedEntity;

    protected $table = DC::TABLE_CUSTOM_QUESTIONS;

    protected $fillable = [
        'question',
        DC::COL_IR,
        ...self::CLIENT_FIELD_COLS,
        'tags',
        FC::COL_CT_FD_ID,
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

    protected $with = [
        'customField',
    ];

    protected $appends = [
        'effective_required',
        'resolved_client_payload',
    ];


    public static array $isRequired = [
        'yes' => 'Yes',
        'no'  => 'No',
    ];

    /**
     * Alias for {@see $isRequired} — used by controllers/views (snake_case).
     * @var array<string,string>
     */
    public static array $is_required = [
        'yes' => 'Yes',
        'no'  => 'No',
    ];

    public function customField(): ?BelongsTo
    {
        return $this->belongsTo(CustomField::class, FC::COL_CT_FD_ID, 'id');
    }

    /**
     * “Effective required”:
     * - if local DC::COL_IR is not null/empty => use it
     * - else fallback to linked custom field required (if exists)
     */
    public function getEffectiveRequiredAttribute(): bool
    {
        $irCol = DC::COL_IR;

        $raw = $this->getAttribute($irCol);
        if ($raw !== null && !(is_string($raw) && trim($raw) === ''))
            return filter_var($raw, FILTER_VALIDATE_BOOLEAN) ?? false;

        $cf = $this->getCachedCustomField();
        if (!$cf) return (bool) $this->getAttribute('required');

        return (bool) $cf->getAttribute('required');
    }

    /**
     * Resolve client payload merging:
     * CustomField (lowest precedence) -> this CustomQuestion (if meaningful).
     */
    public function getResolvedClientPayloadAttribute(): array
    {
        $base = [];

        $cf = $this->getCachedCustomField();
        if ($cf) $base = $cf->getAttribute('client_payload') ?? [];

        $local = array_merge($this->getClientFieldAttributes(), $this->getHtmlLinkedAttributes(true));
        $merged = static::overlayIfMeaningful($base, $local);

        if (Schema::hasColumn($this->getTable(), 'question'))
            $merged['label'] = $this->getAttribute('question');

        $merged['required'] = $this->getEffectiveRequiredAttribute();

        return static::normalizeClientFieldPayload($merged);
    }

    /**
     * Cache helper: avoids repeated DB hits when building responses.
     * Keep it local (no global cache) unless/until you need cross-request caching.
     */
    protected function getCachedCustomField(): ?CustomField
    {
        if ($this->relationLoaded('customField')) {
            $rel = $this->getRelation('customField');
            return $rel instanceof CustomField ? $rel : null;
        }
        try {
            /** @var CustomField|null */
            return $this->customField()->first();
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning(static::class . ' failed to fetch linked customField', [
                'id'   => $this->getAttribute('id'),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'err'  => $e->getMessage(),
            ]);
            return null;
        }
    }
}
