<?php

namespace App\Models;

use App\Config\Constants\{BillsConstants as BC, DatabaseConstants as DC, UsersConstants as UC};
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DebitNote extends CardNote
{
    protected $table = DC::TABLE_DB_NOTES;

    private const EXTRA_FILLABLE = [
        UC::COL_VD_ID,
        'bill',
        'invoice',
        BC::COL_NFE_KEY,
        BC::COL_NFE_NUMBER,
        BC::COL_NFE_SERIES,
        BC::COL_NFE_XML_PATH,
        BC::COL_NFE_PROTOCOL,
        BC::COL_NFE_AUTH_AT,
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
