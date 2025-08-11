<?php

namespace App\Models;

use App\Traits\UsesUuids;
use Illuminate\Database\Eloquent\{Factories\HasFactory, Model, Relations\HasOne};

class ContractComment extends Model
{
    use HasFactory, UsesUuids;

    protected $table = 'contract_comment';

    protected $fillable = [
        'contract_id',
        'user_id',
        'comment',
        'created_by',
    ];

    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'created_by');
        // * consider belongsTo(User::class,'created_by') instead
    }
}
