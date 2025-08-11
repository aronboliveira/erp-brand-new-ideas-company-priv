<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\UsesUuids;

class Product extends Model
{
    use UsesUuids;

    protected $fillable = [
        'name',
        'price',
        'description',
        'image',
        'type',
        'created_by',
    ];

    // * for storing dynamic custom fields
    public $customField;
}
