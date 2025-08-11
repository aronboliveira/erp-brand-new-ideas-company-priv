<?php

namespace App\Models;

use App\Traits\UsesUuids;
use Illuminate\Database\Eloquent\Model;

class UserContact extends Model
{
    use UsesUuids;

    private const FILLABLE_FIELDS = ['parent_id', 'role', 'user_id'];
    protected $fillable = self::FILLABLE_FIELDS;

    // * consider adding: belongsTo(User::class,'user_id','id')
    // * consider adding: belongsTo(ParentModel::class,'parent_id','id')
}
