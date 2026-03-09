<?php

namespace App\Models;

use App\Config\Constants\{DatabaseConstants as DC};
use App\Models\{CustomField};
use App\Traits\{HasAuditFields, UsesUuids};
use Illuminate\Database\Eloquent\{
    Builder,
    Model,
    Relations\BelongsTo
};
use Illuminate\Support\Facades\{DB, Log};

class CustomFieldValue extends Model
{
    use HasAuditFields, UsesUuids;

    protected $table = DC::TABLE_CFV;

    protected $fillable = [
        DC::COL_RCD_ID,
        DC::COL_FLD_ID,
        'value',
        'checked'
    ];

    protected $guarded = [
        'id',
        DC::COL_TABLE_CREATOR,
    ];

    protected $casts = [
        'id'                 => 'string',
        DC::COL_RCD_ID       => 'string',
        DC::COL_FLD_ID       => 'string',
        'value'              => 'string',
        DC::COL_C_AT         => 'datetime',
        DC::COL_U_AT         => 'datetime',
        DC::COL_TABLE_CREATOR => 'string',
        DC::COL_TABLE_UPDATER => 'string',
    ];

    protected static function booted()
    {
        static::saving(function (CustomFieldValue $model) {
            try {
                $field = DB::table($model->getAttribute(DC::COL_FLD_ID))->where('id', $model->getAttribute(DC::COL_FLD_ID))->first();
                if (!$field)
                    throw new \Exception('Related custom field not found.');
                if (in_array($field->type, ['checkbox', 'radio'])) {
                    $isChecked = filter_var($model->getAttribute('checked'), FILTER_VALIDATE_BOOLEAN);
                    $isDisabled = filter_var($field->disabled ?? false, FILTER_VALIDATE_BOOLEAN);
                    $isReadonly = filter_var($field->readonly ?? false, FILTER_VALIDATE_BOOLEAN);
                    if ($isDisabled || $isReadonly) {
                        $model->setAttribute('checked', false);
                        $model->setAttribute('value', 'off');
                    } else {
                        $model->setAttribute('checked', $isChecked);
                        $model->setAttribute('value', $isChecked ? 'on' : 'off');
                    }
                } else $model->setAttribute('checked', null);
            } catch (\Exception $e) {
                Log::error('Error enforcing checked field type: ' . $e->getMessage());
            }
        });
    }

    public function field(): BelongsTo
    {
        return $this->belongsTo(CustomField::class, DC::COL_FLD_ID, 'id');
    }

    public function scopeForRecord(Builder $query, string $recordId): Builder
    {
        return $query->where(DC::COL_RCD_ID, $recordId);
    }
}
