<?php

namespace App\Models;

use App\Config\Constants\DatabaseConstants;
use App\Traits\UsesUuids;
use Illuminate\Database\Eloquent\Model;

class Language extends Model
{
    use UsesUuids;

    protected $fillable = [
        'code',
        'full_name',
        DatabaseConstants::TABLE_CREATOR,
    ];

    public static function languageData(string $code): ?self
    {
        return cache()->remember(
            'language_data_' . $code,
            now()->addHours(24),
            fn () => self::where('code', $code)->first()
        );
    }
}
