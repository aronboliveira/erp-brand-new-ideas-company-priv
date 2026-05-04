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
        BC::COL_NFE_KEY,
        BC::COL_NFE_NUMBER,
        BC::COL_NFE_SERIES,
        BC::COL_NFE_XML_PATH,
        BC::COL_NFE_PROTOCOL,
        BC::COL_NFE_AUTH_AT,
    ];

    protected function monetarySign(): int
    {
        return -1;
    }
}
