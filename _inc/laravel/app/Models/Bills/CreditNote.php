<?php

namespace App\Models;

use App\Traits\UsesUuids;
use Illuminate\Database\Eloquent\{Model, Relations\HasOne};

class CreditNote extends Model
{
    use UsesUuids;

    private const COL_CUSTOMER = 'customer';
    private const COL_INVOICE = 'invoice';

    protected $fillable = [
        self::COL_INVOICE,
        self::COL_CUSTOMER,
        'amount',
        'date',
        'description', // * added to match migration
    ];

    public function customer(): HasOne
    {
        return $this
            ->hasOne(Customer::class, 'customer_id', self::COL_CUSTOMER);
        // * consider using belongsTo(Customer::class, self::COL_CUSTOMER)
    }
}
