<?php

namespace App\Models;

use App\Traits\{ChecksLogin, UsesUuids};
use Illuminate\Database\Eloquent\{
    Collection,
    Model,
    Relations\HasMany,
    Relations\HasOne
};
use Illuminate\Support\Facades\DB;

class ProductServiceCategory extends Model
{
    use ChecksLogin, UsesUuids;

    private const COL_CHART_ACCOUNT_ID = 'chart_account_id';
    private const COL_COLOR           = 'color';
    private const COL_CREATED_BY      = 'created_by';
    private const COL_NAME            = 'name';
    private const COL_TYPE            = 'type';

    protected $fillable = [
        self::COL_NAME,
        self::COL_TYPE,
        self::COL_CHART_ACCOUNT_ID,
        self::COL_COLOR,
        self::COL_CREATED_BY,
    ];

    public static $categoryType = [
        'Product & Service',
        'Income',
        'Expense',
    ];

    public static $catTypes = [
        'product & service'   => 'Product & Service',
        'income'              => 'Income',
        'expense'             => 'Expense',
        'asset'               => 'Asset',
        'liability'           => 'Liability',
        'equity'              => 'Equity',
        'costs of good sold'  => 'Costs of Goods Sold',
    ];

    public function categories(): HasMany
    {
        return $this
            ->hasMany(Revenue::class, 'category_id', 'id');
        // * consider naming relation revenue() or using belongsToMany
    }

    public function incomeCategoryRevenueAmount(): float
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof \Illuminate\Http\RedirectResponse
        )
            return $userOrRedirect;
        $user = $userOrRedirect;
        $userId = $user?->creatorId();
        $year   = date('Y');
        $revenue = $this->categories()
            ->where('created_by', $userId)
            ->whereRaw('YEAR(date)=?', ['?'], $year)
            ->sum('amount');
        $invoices = Invoice::where('category_id', $this->id)
            ->where('created_by', $userId)
            ->whereRaw('YEAR(send_date)=?', ['?'], $year)
            ->get();
        $totals = $invoices->map(fn ($inv) => $inv->getTotal())->all();
        return ($revenue ?: 0) + array_sum($totals);
    }

    public function expenseCategoryAmount(): float
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof \Illuminate\Http\RedirectResponse
        )
            return $userOrRedirect;
        $user = $userOrRedirect;
        $userId = $user?->creatorId();
        $year  = date('Y');
        $payment = Payment::where('category_id', $this->id)
            ->where('created_by', $userId)
            ->whereRaw('YEAR(date)=?', ['?'], $year)
            ->sum('amount');
        $bills = Bill::where('category_id', $this->id)
            ->where('created_by', $userId)
            ->whereRaw('YEAR(send_date)=?', ['?'], $year)
            ->get();
        $totals = $bills->map(fn ($bill) => $bill->getTotal())->all();
        return ($payment ?: 0) + array_sum($totals);
    }

    public static function getAllCategories(): Collection
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof \Illuminate\Http\RedirectResponse
        )
            return $userOrRedirect;
        $user = $userOrRedirect;
        $creator = $user?->creatorId();
        return DB::table((new self)->getTable() . ' as c')
            ->select('c.*', DB::raw('COUNT(p.category_id) as product_services'))
            ->leftJoin('product_services as p', 'c.id', '=', 'p.category_id')
            ->where('c.created_by', $creator)
            ->where('c.type', 0)
            ->groupBy('c.id')
            ->orderBy('c.id', 'desc')
            ->get();
    }

    public function chartAccount(): HasOne
    {
        return $this
            ->hasOne(ChartOfAccount::class, 'id', self::COL_CHART_ACCOUNT_ID);
        // * consider using belongsTo
    }
}
