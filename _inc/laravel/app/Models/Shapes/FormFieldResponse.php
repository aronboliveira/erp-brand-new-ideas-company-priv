<?php

namespace App\Models;

use App\Traits\UsesUuids;
use Illuminate\Database\Eloquent\{Model, Relations\BelongsTo};

class FormFieldResponse extends Model
{
    use UsesUuids;

    protected $fillable = [
        'form_id',
        'subject_id',
        'name_id',
        'email_id',
        'user_id',
        'pipeline_id',
    ];

    public function form(): BelongsTo
    {
        return $this->belongsTo(FormBuilder::class, 'form_id');
    }

    public function subjectField(): BelongsTo
    {
        return $this->belongsTo(FormField::class, 'subject_id');
    }

    public function nameField(): BelongsTo
    {
        return $this->belongsTo(FormField::class, 'name_id');
    }

    public function emailField(): BelongsTo
    {
        return $this->belongsTo(FormField::class, 'email_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function pipeline(): BelongsTo
    {
        return $this->belongsTo(Pipeline::class, 'pipeline_id');
    }
}
