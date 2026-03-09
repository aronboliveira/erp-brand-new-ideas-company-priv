<?php

namespace App\Models;

use App\Traits\{UsesUuids};
use Illuminate\Database\Eloquent\{Model};
use Illuminate\Database\Eloquent\Relations\{HasOne, BelongsTo};

use Illuminate\Database\Eloquent\Factories\HasFactory;
class IpRestrict extends Model
{
    use HasFactory;

    use UsesUuids;

    private const FILLABLE = ['ip', 'created_by'];
    protected $fillable  = self::FILLABLE;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
            }
}
