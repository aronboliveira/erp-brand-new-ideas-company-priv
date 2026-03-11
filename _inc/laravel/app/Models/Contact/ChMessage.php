<?php

namespace App\Models;

use App\Models\{User};
use App\Traits\{UsesUuids};
use Illuminate\Database\Eloquent\{Model};
use Illuminate\Database\Eloquent\Relations\{HasOne, BelongsTo};

use Illuminate\Database\Eloquent\Factories\HasFactory;
class ChMessage extends Model
{
    use HasFactory;
    use UsesUuids;

    private const COL_FROM_ID = 'from_id';
    private const COL_TO_ID  = 'to_id';
    private const COL_MESSAGE = 'body';
    private const COL_SEEN   = 'seen';
    private const FILLABLE   = [
        self::COL_FROM_ID,
        self::COL_TO_ID,
        self::COL_MESSAGE,
        self::COL_SEEN,
    ];

    protected $fillable = self::FILLABLE;

    public function from(): BelongsTo
    {
        return $this->belongsTo(User::class, self::COL_FROM_ID, 'id');
    }

    public function to(): BelongsTo
    {
        return $this->belongsTo(User::class, self::COL_TO_ID, 'id');
    }
}
