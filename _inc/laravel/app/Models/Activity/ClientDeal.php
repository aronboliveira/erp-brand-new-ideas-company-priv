<?php

namespace App\Models;

use App\Traits\UsesUuids;
use App\Config\Constants\DatabaseConstants as DC;
use App\Traits\HasAuditFields;
use Illuminate\Database\Eloquent\{Model, Relations\BelongsTo};

class ClientDeal extends Model
{
    use UsesUuids, HasAuditFields;

    protected $fillable = ['client_id', 'deal_id'];
    protected $guarded = ['id'];
    protected $with = ['deal', 'client'];

    public function deal(): BelongsTo
    {
        return $this->belongsTo(Deal::class, 'deal_id', 'id');
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'client_id', 'id');
    }
}
