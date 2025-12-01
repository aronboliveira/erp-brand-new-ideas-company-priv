<?php

namespace App\Models;

use App\Config\Constants\DatabaseConstants as DC;

class CreditNote extends CardNote
{
    protected $table = DC::TABLE_CR_NOTES;

    protected function monetarySign(): int
    {
        return -1;
    }
}
