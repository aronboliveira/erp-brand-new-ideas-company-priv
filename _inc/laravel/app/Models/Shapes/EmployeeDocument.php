<?php

namespace App\Models;

use App\Config\Constants\{DatabaseConstants as DC, TemplatesConstants as TC, UsersConstants as UC};
use Illuminate\Database\Eloquent\{Relations\BelongsTo};

class EmployeeDocument extends AbstractDocument
{

    protected $table = DC::TABLE_EDOCS;
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->fillable = array_merge($this->fillable, [
            UC::COL_EMP_ID,
            TC::COL_DC_ID,
            TC::COL_DC_V,
        ]);
    }
    protected $with = [
        'employee',
        'document',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, UC::COL_EMP_ID, 'id');
        // * Alternatively: return $this->hasOne(Employee::class, 'id', UC::COL_EMP_ID);
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class, TC::COL_DC_ID, 'id');
        // * If your “document” records live in some other table (e.g. `documents`), adjust the class name/path accordingly.
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, DC::COL_TABLE_CREATOR, 'id');
        // * Optionally fetch the User who uploaded/created this record
    }
}
