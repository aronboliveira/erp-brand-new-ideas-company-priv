<?php

namespace App\Models;

use App\Config\Constants\{DatabaseConstants as DC, TemplatesConstants as TC, UsersConstants as UC};
use Illuminate\Database\Eloquent\{Relations\BelongsTo, SoftDeletes};

class EmployeeDocument extends AbstractDocument
{
    use SoftDeletes;

    protected $table = DC::TABLE_EDOCS;

    protected $with = [
        'employee',
        'document',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->fillable = array_merge($this->fillable, [
            UC::COL_EMP_ID,
            TC::COL_DC_ID,
            TC::COL_DC_V,
        ]);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, UC::COL_EMP_ID, 'id');
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class, TC::COL_DC_ID, 'id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, DC::COL_TABLE_CREATOR, 'id');
    }
}
