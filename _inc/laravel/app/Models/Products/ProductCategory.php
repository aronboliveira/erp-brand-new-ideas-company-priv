<?php

namespace App\Models;

use App\Traits\UsesUuids;
use Illuminate\Database\Eloquent\Model;

class ProductCategory extends Model
{
    use UsesUuids;

    private const FILLABLE_FIELDS = [
        'name', 'description', 'created_by'
    ];
    protected $fillable = self::FILLABLE_FIELDS;

    protected $hidden = [];
    // * consider adding: hasMany(Product::class,'category_id','id')
}
