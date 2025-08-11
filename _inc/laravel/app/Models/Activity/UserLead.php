<?php

namespace App\Models;

use App\Traits\UsesUuids;
use Illuminate\Database\Eloquent\{Factories\HasFactory, Model};
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasOne};

class UserLead extends Model
{
    use HasFactory, UsesUuids;

    protected $fillable = ['user_id', 'lead_id'];

    private const FK_LEAD = 'lead_id';
    private const FK_USER = 'user_id';

    public function getLeadUser(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    public function lead(): BelongsTo // * ADDED
    {
        return $this->belongsTo(Lead::class, self::FK_LEAD, 'id');
    }

    public function user(): BelongsTo // * ADDED
    {
        return $this->belongsTo(User::class, self::FK_USER, 'id');
    }
}
