<?php

namespace App\Models;

use App\Config\Constants\DatabaseConstants as DC;
use App\Traits\{HasAuditFields, UsesUuids};
use Illuminate\Database\Eloquent\{Factories\HasFactory, Model, Relations\BelongsTo};

class Source extends Model
{
    use HasFactory, UsesUuids, HasAuditFields;

    protected $fillable = ['name', DC::TABLE_UPDATER];
    protected $guarded  = ['id', DC::TABLE_CREATOR];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, DC::TABLE_CREATOR, 'id');
    }
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, DC::TABLE_CREATOR, 'id');
    }
}
