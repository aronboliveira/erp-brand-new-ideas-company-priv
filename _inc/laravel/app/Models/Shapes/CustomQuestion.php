<?php

namespace App\Models;

use App\Traits\UsesUuids;
use Illuminate\Database\Eloquent\Model;

class CustomQuestion extends Model
{
    use UsesUuids;

    protected $fillable = [
        'question',
        'is_required',
        'created_by',
    ];

    public static $isRequired = [ // ! CHANGED
        'yes' => 'Yes',
        'no'  => 'No',
    ];
    // * Consider adding a relation: createdBy(): BelongsTo(User::class, 'created_by');
}
