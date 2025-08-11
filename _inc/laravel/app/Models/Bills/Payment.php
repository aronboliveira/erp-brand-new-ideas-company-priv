<?php

namespace App\Models;

use App\Traits\UsesUuids;
use Illuminate\Database\Eloquent\{Model, Relations\HasOne};

class Payment extends Model
{
    use UsesUuids;

    private const FILLABLE_FIELDS = [
        'date', 'amount', 'account_id', 'chart_account_id', 'vendor_id',
        'description', 'category_id', 'payment_method', 'reference', 'created_by'
    ]; // ! CHANGED

    protected $fillable = self::FILLABLE_FIELDS; // ! CHANGED

    public function category(): HasOne
    {
        return $this->hasOne(ProductServiceCategory::class, 'id', 'category_id');
    }

    public function vendor(): HasOne
    {
        return $this->hasOne(Vendor::class, 'id', 'vendor_id');
    }

    public function vender(): HasOne // * KEPT FOR COMPATIBILITY, DO NOT USE IN ENDPOINTS
    {
        return $this->vendor();
    }

    public function bankAccount(): HasOne
    {
        return $this->hasOne(BankAccount::class, 'id', 'account_id');
    }

    public function chartAccount(): HasOne
    {
        return $this->hasOne(ChartOfAccount::class, 'id', 'chart_account_id');
    }
}
