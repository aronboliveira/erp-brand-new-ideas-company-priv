<?php

namespace App\Models;

use App\Config\Constants\DatabaseConstants as DC;
use App\Models\User;
use App\Traits\{HasAuditFields, UsesUuids};
use Illuminate\Database\Eloquent\{Model, Relations\BelongsTo};

class TerminationType extends Model
{
    use HasAuditFields, UsesUuids;
    protected $fillable = ['name', 'description'];
    protected $guarded = ['id', DC::COL_TABLE_CREATOR];

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, DC::COL_TABLE_CREATOR, 'id');
    }
}
