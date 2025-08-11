<?php

namespace App\Models;

use App\Traits\UsesUuids;
use Illuminate\Database\Eloquent\{Factories\HasFactory, Model, Relations\HasOne};

/**
 * @property string|int $id
 */
class Resignation extends Model
{
    use HasFactory, UsesUuids;

    protected $fillable = [
        'employee_id',
        'notice_date',
        'resignation_date',
        'description',
        'created_by',
    ];

    public function employee(): HasOne
    {
        return $this->hasOne(Employee::class, 'id', 'employee_id');
        // * consider using belongsTo(Employee::class, 'employee_id');
    }
}
