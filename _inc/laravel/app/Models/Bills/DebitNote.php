<?php

namespace App\Models;

use App\Traits\UsesUuids;
use Illuminate\Database\Eloquent\{Model, Relations\HasOne};

class DebitNote extends Model
{
    use UsesUuids;

    private const COL_BILL  = 'bill';
    private const COL_VENDOR = 'vendor';

    protected $fillable = [
        self::COL_BILL,
        self::COL_VENDOR,
        'amount',
        'date',
        'description', // * added to match migration
    ];

    public function vendor(): HasOne
    {
        return $this
            ->hasOne(Vendor::class, 'vendor_id', self::COL_VENDOR);
        // * consider using belongsTo(Vendor::class, self::COL_VENDOR)
    }
}
