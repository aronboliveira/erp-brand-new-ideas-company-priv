<?php

namespace App\Models;

use App\Traits\UsesUuids;
use Illuminate\Database\Eloquent\{Factories\HasFactory, Model, Relations\HasOne};

class LoginDetail extends Model
{
    use HasFactory, UsesUuids;

    private const FILLABLE_FIELDS = [
        'user_id', 'ip', 'date', 'Details', 'created_by'
    ];                                     // ! CHANGED

    protected $fillable = self::FILLABLE_FIELDS; // ! CHANGED

    public function createdBy(): HasOne
    {
        return $this->hasOne('App\Models\user', 'id', 'ticket_created'); // ! CHANGED
        // * consider using created_by as foreign key or belongsTo(User::class,'created_by')
    }
}
