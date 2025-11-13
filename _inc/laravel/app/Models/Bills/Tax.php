<?php

namespace App\Models;

use App\Config\Constants\DatabaseConstants as DC;
use App\Traits\HasAuditFields;
use App\Traits\UsesUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Tax extends Model
{
    use UsesUuids, HasAuditFields;

    protected $fillable = ['name', 'rate'];
    protected $guarded = [
        'id',
        DC::TABLE_CREATOR,
    ];
    protected $casts = [
        'rate' => 'decimal:2',
    ];
    protected static function booted(): void
    {
        static::creating(function (self $m) {
            self::normalizeAndValidate($m, true);
        });
        static::updating(function (self $m) {
            self::normalizeAndValidate($m, false);
        });
    }

    private static function normalizeAndValidate(self $m, bool $isCreating): void
    {
        if ($isCreating || $m->isDirty('name')) {
            $name = trim(preg_replace('/\s+/u', ' ', (string) $m->name));
            $name = Str::of($name)->squish()->toString();
            if ($name === '')
                throw new \InvalidArgumentException('Nome do imposto não pode ser vazio.');
            $m->name = $name;
        }
        if ($isCreating || $m->isDirty('rate')) {
            $raw = (string) $m->rate;
            $normalized = str_replace(['.', ' '], ['', ''], $raw);
            $normalized = str_replace(',', '.', $normalized);
            if (!is_numeric($normalized))
                throw new \InvalidArgumentException('A alíquota (rate) deve ser numérica.');
            $value = (float) $normalized;
            if ($value < 0 || $value > 100)
                throw new \OutOfRangeException('A alíquota deve estar entre 0 e 100.');
            $m->rate = number_format($value, 2, '.', '');
        }
    }
}
