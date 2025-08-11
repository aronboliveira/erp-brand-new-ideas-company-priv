<?php

namespace App\Models;

use App\Traits\UsesUuids;
use Illuminate\Database\Eloquent\{Model, Relations\BelongsTo};

class EmployeeDocument extends Model
{

    use UsesUuids;

    protected $fillable = [
        'employee_id',
        'document_id',
        'document_value',
        'created_by',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'id');
        // * Alternatively: return $this->hasOne(Employee::class, 'id', 'employee_id');
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class, 'document_id', 'id');
        // * If your “document” records live in some other table (e.g. `documents`), adjust the class name/path accordingly.
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
        // * Optionally fetch the User who uploaded/created this record
    }
}
