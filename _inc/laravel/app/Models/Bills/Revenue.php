<?php

namespace App\Models;

use App\Traits\UsesUuids;
use Illuminate\Database\Eloquent\{Model, Relations\HasOne};

class Revenue extends Model
{
    use UsesUuids;

    private const FILLABLE_FIELDS = [
        'date', 'amount', 'account_id', 'customer_id', 'category_id',
        'recurring', 'payment_method', 'reference', 'description',
        'created_by'
    ]; // ! CHANGED

    protected $fillable = self::FILLABLE_FIELDS; // ! CHANGED

    public function category(): HasOne
    {
        return $this->hasOne(ProductServiceCategory::class, 'id', 'category_id'); // * consider belongsTo(ProductServiceCategory::class,'category_id')
    }

    public function customer(): HasOne
    {
        return $this->hasOne(Customer::class, 'id', 'customer_id'); // * consider belongsTo(Customer::class,'customer_id')
    }

    public function bankAccount(): HasOne
    {
        return $this->hasOne(BankAccount::class, 'id', 'account_id'); // * consider belongsTo(BankAccount::class,'account_id')
    }
}
