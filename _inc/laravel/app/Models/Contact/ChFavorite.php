<?php

namespace App\Models;

use App\Traits\UsesUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
/**
 * @property int|null $favorite_id
 */

class ChFavorite extends Model
{
    use HasFactory;
    use UsesUuids;

    protected $table = 'chatify_favorites';

    private const FILLABLE_FIELDS = ['user_id', 'favorite_id']; // ! CHANGED

    protected $fillable = self::FILLABLE_FIELDS;              // ! CHANGED

    // * consider adding belongsTo(User::class,'favorite_id') relationship
}
