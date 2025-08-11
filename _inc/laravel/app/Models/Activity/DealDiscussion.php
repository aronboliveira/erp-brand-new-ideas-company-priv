<?php

namespace App\Models;

use App\Models\User;
use App\Traits\UsesUuids;
use Illuminate\Database\Eloquent\{Model, Relations\HasOne};

class DealDiscussion extends Model
{
    use UsesUuids;
    private const FILLABLE = ['deal_id', 'comment', 'created_by']; // * extracted fillable to const
    protected $fillable = self::FILLABLE;

    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'created_by');
    }
}
