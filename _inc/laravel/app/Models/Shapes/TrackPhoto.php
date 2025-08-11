<?php

namespace App\Models;

use App\Traits\UsesUuids;
use Illuminate\Database\Eloquent\{Model, Relations\BelongsTo};

class TrackPhoto extends Model
{
    use UsesUuids;

    private const COL_TRACK_ID = 'track_id';
    private const COL_USER_ID = 'user_id';
    private const COL_IMG_PATH = 'img_path';
    private const COL_TIME    = 'time';
    private const COL_STATUS  = 'status';

    private const FILLABLE_FIELDS = [
        self::COL_TRACK_ID,
        self::COL_USER_ID,
        self::COL_IMG_PATH,
        self::COL_TIME,
        self::COL_STATUS,
    ];
    protected $fillable = self::FILLABLE_FIELDS;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, self::COL_USER_ID, 'id');
    }
}
