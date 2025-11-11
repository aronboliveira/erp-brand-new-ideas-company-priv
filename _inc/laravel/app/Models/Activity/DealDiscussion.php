<?php

namespace App\Models;

use App\Config\Constants\{ActivitiesConstants as AC, DatabaseConstants as DC};
use App\Models\User;
use App\Traits\HasAuditFields;
use App\Traits\UsesUuids;
use Illuminate\Database\Eloquent\{Model, Relations\HasOne};
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DealDiscussion extends Model
{
    use UsesUuids, HasAuditFields;
    protected $fillable = [AC::COL_DL, 'comment'];
    protected $guarded = ['id', DC::TABLE_CREATOR];
    protected $with = ['deal'];

    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'id', DC::TABLE_CREATOR);
    }
    public function deal(): BelongsTo
    {
        return $this->belongsTo(Deal::class, AC::COL_DL, 'id');
    }
}
