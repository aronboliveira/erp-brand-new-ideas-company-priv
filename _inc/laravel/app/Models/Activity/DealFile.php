<?php

namespace App\Models;

use App\Traits\UsesUuids;
use App\Config\Constants\DatabaseConstants as DC;
use App\Traits\HasAuditFields;
use Illuminate\Database\Eloquent\{Model, Relations\BelongsTo};

class DealFile extends Model
{
    use UsesUuids, HasAuditFields;

    protected $fillable = ['deal_id', 'file_name', 'file_path'];
    protected $guarded = ['id', DC::TABLE_CREATOR];
    protected $with = ['deal'];

    public function deal(): BelongsTo
    {
        return $this->belongsTo(Deal::class, 'deal_id', 'id');
    }
}
