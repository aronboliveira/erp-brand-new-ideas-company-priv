<?php

namespace App\Services;

use App\Config\Constants\{
	BillsConstants as BC,
	DatabaseConstants as DC
};
use App\Models\{
	Bill,
	Invoice,
	Payment,
	Pos,
	PosProduct,
	ProductService,
	ProductServiceCategory,
	Purchase,
	PurchaseProduct
};
use App\Traits\ChecksLogin;
use Illuminate\Database\Eloquent\{Builder, Collection};
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class ProductOrServiceRequestService
{
	use ChecksLogin;

	/**
	 * Get all products for authenticated user
	 * 
	 * @return \Illuminate\Database\Eloquent\Builder|RedirectResponse Query builder or redirect if not authenticated
	 */
	public function getAllProducts(): Builder|RedirectResponse
	{
		if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse)
			return $userOrRedirect;

		$user = $userOrRedirect;
		$table = ProductService::TABLE;

		return ProductService::select($table . '.*', 'c.name as category_name')
			->where($table . '.type', 'product')
			->leftJoin(DC::TABLE_PROD_SERV_CATS . ' as c', 'c.id', '=', $table . '.' . BC::COL_CAT_ID)
			->where($table . '.' . DC::COL_TABLE_CREATOR, $user->creatorId())
			->orderByDesc($table . '.id');
	}

	/**
	 * Get total quantity for a product
	 * Calculates purchased quantity minus sold quantity
	 * 
	 * @param ProductService $product
	 * @return float|RedirectResponse Total quantity or redirect if not authenticated
	 */
	public function getTotalProductQuantity(ProductService $product): float|RedirectResponse
	{
		if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse)
			return $userOrRedirect;

		$user = $userOrRedirect;
		$userId = $user->creatorId();
		$pid = $product->id;

		$purchases = Purchase::where(DC::COL_TABLE_CREATOR, $userId);
		if ($user->isUser())
			$purchases->where(BC::COL_WRH_ID, $user->{BC::COL_WRH_ID});

		$purchasedQty = $purchases->get()->sum(
			fn($p) => optional(
				PurchaseProduct::where(BC::COL_PRC_ID, $p->id)
					->where(BC::COL_PRD_ID, $pid)
					->first()
			)->quantity ?: 0
		);

		$poses = Pos::where(DC::COL_TABLE_CREATOR, $userId);
		if ($user->isUser())
			$poses->where(BC::COL_WRH_ID, $user->{BC::COL_WRH_ID});

		$posQty = $poses->get()->sum(
			fn($p) => optional(
				PosProduct::where(BC::COL_POS_ID, $p->id)
					->where(BC::COL_PRD_ID, $pid)
					->first()
			)->quantity ?: 0
		);

		return $purchasedQty - $posQty;
	}

	/**
	 * Get tax ID for a product
	 * 
	 * @param string|int $productId
	 * @return int|RedirectResponse Tax ID or 0 if not found, or redirect if not authenticated
	 */
	public function getProductTaxId(string|int $productId): int|RedirectResponse
	{
		if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse)
			return $userOrRedirect;

		$user = $userOrRedirect;

		return DB::table(ProductService::TABLE)
			->where('id', $productId)
			->where(DC::COL_TABLE_CREATOR, $user->creatorId())
			->value(BC::COL_TAX_ID) ?: 0;
	}

	/**
	 * Get total revenue amount for income category
	 * Includes revenue from categories and invoices for current year
	 * 
	 * @param ProductServiceCategory $category
	 * @return float|RedirectResponse Total revenue amount or redirect if not authenticated
	 */
	public function getIncomeCategoryRevenueAmount(ProductServiceCategory $category): float|RedirectResponse
	{
		if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse)
			return $userOrRedirect;

		$user = $userOrRedirect;
		$userId = $user->creatorId();
		$year = date('Y');

		$revenue = $category->categories()
			->where(DC::COL_TABLE_CREATOR, $userId)
			->whereYear('date', $year)
			->sum('amount');

		$invoices = Invoice::where(BC::COL_CAT_ID, $category->id)
			->where(DC::COL_TABLE_CREATOR, $userId)
			->whereYear(BC::COL_SD_DT, $year)
			->get();

		$totals = $invoices
			->map(fn(Invoice $inv) => $inv->getTotal())
			->all();

		return ($revenue ?: 0) + array_sum($totals);
	}

	/**
	 * Get total expense amount for category
	 * Includes payments and bills for current year
	 * 
	 * @param ProductServiceCategory $category
	 * @return float|RedirectResponse Total expense amount or redirect if not authenticated
	 */
	public function getExpenseCategoryAmount(ProductServiceCategory $category): float|RedirectResponse
	{
		if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse)
			return $userOrRedirect;

		$user = $userOrRedirect;
		$userId = $user->creatorId();
		$year = date('Y');

		$payment = Payment::where(BC::COL_CAT_ID, $category->id)
			->where(DC::COL_TABLE_CREATOR, $userId)
			->whereYear('date', $year)
			->sum('amount');

		$bills = Bill::where(BC::COL_CAT_ID, $category->id)
			->where(DC::COL_TABLE_CREATOR, $userId)
			->whereYear(BC::COL_SD_DT, $year)
			->get();

		$totals = $bills
			->map(fn(Bill $bill) => $bill->getTotal())
			->all();

		return ($payment ?: 0) + array_sum($totals);
	}

	/**
	 * Get all categories with product/service counts
	 * 
	 * @return Collection|RedirectResponse Collection of categories or redirect if not authenticated
	 */
	public function getAllCategories(): Collection|RedirectResponse
	{
		if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse)
			return $userOrRedirect;

		$user = $userOrRedirect;
		$creator = $user->creatorId();

		return DB::table(ProductServiceCategory::TABLE . ' as c')
			->select('c.*', DB::raw('COUNT(p.' . BC::COL_CAT_ID . ') as product_services'))
			->leftJoin(DC::TABLE_PROD_SERVS . ' as p', 'c.id', '=', 'p.' . BC::COL_CAT_ID)
			->where('c.' . DC::COL_TABLE_CREATOR, $creator)
			->where('c.type', 0)
			->groupBy('c.id')
			->orderBy('c.id', 'desc')
			->get();
	}
}
