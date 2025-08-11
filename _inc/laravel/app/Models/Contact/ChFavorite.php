<?php

namespace App\Models;

use App\Traits\UsesUuids;
use Illuminate\Database\Eloquent\Model;

class ChFavorite extends Model
{
    use UsesUuids;

    private const FILLABLE_FIELDS = ['user_id', 'favorite_id']; // ! CHANGED

    protected $fillable = self::FILLABLE_FIELDS;              // ! CHANGED

    // * consider adding belongsTo(User::class,'favorite_id') relationship
}
