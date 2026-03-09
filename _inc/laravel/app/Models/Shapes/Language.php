<?php

namespace App\Models;

use App\Config\Constants\{DatabaseConstants};
use App\Traits\{UsesUuids};
use Illuminate\Database\Eloquent\{Model};
use Illuminate\Support\Facades\{Log};

class Language extends Model
{
    use UsesUuids;

    protected $fillable = [
        'code',
        'full_name',
        DatabaseConstants::COL_TABLE_CREATOR,
    ];

    public static function languageData(string $code): ?self
    {
        try {
            return cache()->remember(
                'language_data_' . $code,
                now()->addHours(24),
                fn() => self::where('code', $code)->first()
            );
        } catch (\Throwable $e) {
            Log::error(static::class . '::languageData — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
            return null;
        }
    }
}
