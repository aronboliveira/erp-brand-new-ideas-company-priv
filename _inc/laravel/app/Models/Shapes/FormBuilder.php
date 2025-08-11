<?php

namespace App\Models;

use App\Traits\UsesUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{HasMany, HasOne};

class FormBuilder extends Model
{
    use UsesUuids;

    private const FILLABLE_FIELDS = [
        'name', 'code', 'is_active', 'is_lead_active', 'created_by'  // ! CHANGED
    ];
    protected $fillable = self::FILLABLE_FIELDS;

    public static array $fieldTypes = [
        'text'     => 'Text',
        'email'    => 'Email',
        'number'   => 'Number',
        'date'     => 'Date',
        'textarea' => 'Textarea'
    ];

    public function formField(): HasMany
    {
        return $this->hasMany(FormField::class, 'form_id', 'id');
    }

    public function fieldResponse(): HasOne
    {
        return $this->hasOne(FormFieldResponse::class, 'form_id', 'id');
    }

    public function response(): HasMany
    {
        return $this->hasMany(FormResponse::class, 'form_id', 'id');
    }
}
