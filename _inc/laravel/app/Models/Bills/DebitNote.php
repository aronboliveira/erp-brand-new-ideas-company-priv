<?php

namespace App\Models;

use App\Config\Constants\{DatabaseConstants as DC, UsersConstants as UC};
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DebitNote extends CardNote
{
    protected $table = DC::TABLE_DB_NOTES;

    private const EXTRA_FILLABLE = [
        UC::COL_VD_ID,
    ];

    protected $fillable = [
        ...parent::BASE_FILLABLE,
        ...self::EXTRA_FILLABLE,
    ];

    protected $with = [
        ...parent::BASE_WITH,
        'vendor',
    ];

    protected function monetarySign(): int
    {
        return 1;
    }

    public function getFillable(): array
    {
        return array_merge(parent::getFillable(), self::EXTRA_FILLABLE);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class, UC::COL_VD_ID);
    }
}
