<?php

namespace App\Models;

use App\Traits\UsesUuids;
use Illuminate\Database\Eloquent\{Factories\HasFactory, Model};
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasOne};

class UserDeal extends Model
{
    use HasFactory, UsesUuids;

    protected $fillable = ['user_id', 'deal_id'];

    private const FK_DEAL = 'deal_id';
    private const FK_USER = 'user_id';

    public function getDealUser(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    public function deal(): BelongsTo // * ADDED
    {
        return $this->belongsTo(Deal::class, self::FK_DEAL, 'id');
    }

    public function user(): BelongsTo // * ADDED
    {
        return $this->belongsTo(User::class, self::FK_USER, 'id');
    }
}
