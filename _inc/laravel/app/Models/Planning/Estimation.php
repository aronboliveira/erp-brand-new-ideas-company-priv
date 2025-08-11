<?php

namespace App\Models;

use App\Traits\{ChecksLogin, UsesUuids};
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsToMany, HasOne};

class Estimation extends Model
{
    use ChecksLogin, UsesUuids;

    private const COL_CLIENT_ID    = 'client_id';
    private const COL_CREATED_BY   = 'created_by';
    private const COL_DISCOUNT     = 'discount';
    private const COL_ESTIMATION_ID = 'estimation_id';
    private const COL_ISSUE_DATE   = 'issue_date';
    private const COL_STATUS       = 'status';
    private const COL_TAX_ID       = 'tax_id';
    private const COL_TERMS        = 'terms';

    private const FILLABLE = [
        self::COL_ESTIMATION_ID,
        self::COL_CLIENT_ID,
        self::COL_STATUS,
        self::COL_ISSUE_DATE,
        self::COL_DISCOUNT,
        self::COL_TAX_ID,
        self::COL_TERMS,
        self::COL_CREATED_BY,
    ];

    private const STATUS_OPTIONS = [
        'Open',
        'Not Paid',
        'Partially Paid',
        'Paid',
        'Cancelled',
    ];

    protected $fillable = self::FILLABLE;

    public static function status(): array
    {
        return self::STATUS_OPTIONS;
    }

    public function client(): HasOne
    {
        return $this->hasOne(User::class, 'id', self::COL_CLIENT_ID);
        // * consider belongsTo(User::class,self::COL_CLIENT_ID,'id')
    }

    public function tax(): HasOne
    {
        return $this->hasOne(Tax::class, 'id', self::COL_TAX_ID);
        // * consider belongsTo(Tax::class,self::COL_TAX_ID,'id')
    }

    public function getProducts(): BelongsToMany
    {
        return $this->belongsToMany(
            ProductService::class,
            'estimation_products',
            self::COL_ESTIMATION_ID,
            'product_id'
        )->withPivot('id', 'price', 'quantity', 'description');
    }

    public function getSubTotal(): float
    {
        return $this->getProducts
            ->sum(fn ($product): float => $product->pivot->price * $product->pivot->quantity);
    }

    public function getTax(): float
    {
        $sub = $this->getSubTotal();
        return $sub > 0
            ? (($sub - $this->discount) * ($this->tax->rate ?? 0)) / 100.0
            : 0.0;
    }

    public function getTotal(): float
    {
        return $this->getSubTotal() - $this->discount + $this->getTax();
    }

    public function getDue(): float
    {
        // ! ALERT payments relation not defined
        return $this->getTotal() - ($this->payments->sum('amount') ?? 0);
    }

    public static function getEstimationSummary(iterable $estimates): string
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof \Illuminate\Http\RedirectResponse
        )
            return $userOrRedirect;
        $user = $userOrRedirect;
        $total = collect($estimates)
            ->sum(fn ($e): float => $e->getTotal());
        return $user?->priceFormat($total);
    }
}
