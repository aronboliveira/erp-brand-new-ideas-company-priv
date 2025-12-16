<?php

namespace App\Models;

use App\Config\Constants\{BillsConstants as BC, DatabaseConstants as DC};

final class CreditNote extends CardNote
{
    protected $table = DC::TABLE_CR_NOTES;

    protected $fillable = [
        ...parent::BASE_FILLABLE,
        'invoice',
        BC::COL_BL_ID,
    ];

    protected function monetarySign(): int
    {
        return -1;
    }
}
