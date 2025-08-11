<?php

namespace App\Http\Controllers;

use App\Models\{
	ChartOfAccount,
	CustomField,
	ProductService,
	ProductServiceCategory,
	ProductServiceUnit,
	Tax,
	Utility
};
use App\Traits\ChecksPermissions;
use Illuminate\Contracts\View\View;
use Illuminate\Http\{
	JsonResponse,
	RedirectResponse,
	Request
};
use Illuminate\Support\Facades\{
	Auth,
	DB,
	Log,
	Validator
};
use Throwable;

final class ProductServiceController extends Controller
{
	use ChecksLogin, ChecksPermissions;

	private const REDIRECT_INDEX = 'productservice.index';

	private static function safe(Request $request, string $ref, \Closure $fn): View|RedirectResponse|JsonResponse
	{
		Log::info(__METHOD__ . " start: {$ref}", ['user_id' => Auth::id()]);
		try {
			return $fn();
		} catch (Throwable $e) {
			Log::error(__METHOD__ . " error in {$ref}", ['error' => $e->getMessage()]);
			return defaultUndefinedException(
				$request,
				$e,
				__CLASS__ . '::' . $ref,
				route(self::REDIRECT_INDEX)
			);
		}
	}

	private static function validateRequest(Request $request, array $rules): ?RedirectResponse
	{
		$v = Validator::make($request->all(), $rules);
		if ($v->fails()) {
			Log::warning(__METHOD__ . ' validation failed', ['errors' => $v->errors()->all()]);
			return redirect()->back()->with('error', $v->errors()->first());
		}
		return null;
	}

	private static function formCollections(string|int $creator): array
	{
		Log::info(__METHOD__, ['creator' => $creator]);
		$category = ProductServiceCategory::whereCreatedBy($creator)
			->whereType('product & service')
			->pluck('name', 'id')
			->prepend('Select Category', '');
		$unit    = ProductServiceUnit::whereCreatedBy($creator)
			->pluck('name', 'id');
		$tax     = Tax::whereCreatedBy($creator)
			->pluck('name', 'id');
		$income  = ChartOfAccount::selectRaw('CONCAT(code," - ",name) AS code_name,id')
			->join('chart_of_account_types', 'chart_of_account_types.id', '=', 'chart_of_accounts.type')
			->where('chart_of_account_types.name', 'income')
			->where('chart_of_accounts.created_by', $creator)
			->pluck('code_name', 'id')
			->prepend('Select Account', '');
		$expense = ChartOfAccount::selectRaw('CONCAT(code," - ",name) AS code_name,id')
			->join('chart_of_account_types', 'chart_of_account_types.id', '=', 'chart_of_accounts.type')
			->whereIn('chart_of_account_types.name', ['Expenses', 'Costs of Goods Sold'])
			->where('chart_of_accounts.created_by', $creator)
			->pluck('code_name', 'id')
			->prepend('Select Account', '');
		return compact('category', 'unit', 'tax', 'income', 'expense');
	}

	public function index(Request $request): View|RedirectResponse
	{
		if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
		if ($c = self::guard($request, 'manage product & service', self::REDIRECT_INDEX)) return $c;
		return self::safe($request, 'index', function () use ($request, $u) {
			Log::info(__METHOD__ . ' loading list', ['creator' => $u->creatorId()]);
			$category = self::formCollections($u->creatorId())['category'];
			$products = ProductService::whereCreatedBy($u->creatorId())
				->when($request->filled('category'), fn ($q) => $q->whereCategoryId($request->category))
				->with(['category', 'unit'])
				->get();
			return view('productservice.index', [
				'productServices' => $products,
				'category' => $category
			]);
		});
	}

	public function create(Request $request): View|RedirectResponse
	{
		if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
		if ($c = self::guard($request, 'create product & service', self::REDIRECT_INDEX)) return $c;
		return self::safe($request, 'create', function () use ($u) {
			Log::info(__METHOD__ . ' preparing form', ['creator' => $u->creatorId()]);
			$collections = self::formCollections($u->creatorId());
			$custom     = CustomField::whereCreatedBy($u->creatorId())
				->whereModule('product')->get();
			return view('productservice.create', array_merge(
				$collections,
				['customFields' => $custom]
			));
		});
	}

	public function store(Request $request): RedirectResponse
	{
		if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
		if ($c = self::guard($request, 'create product & service', self::REDIRECT_INDEX)) return $c;
		$rules = [
			'name' => 'required',
			'sku' => "required|unique:product_services,sku,NULL,id,created_by,{$u->id}",
			'sale_price' => 'required|numeric',
			'purchase_price' => 'required|numeric',
			'category_id' => 'required',
			'unit_id' => 'required',
			'type' => 'required'
		];
		if ($c = self::validateRequest($request, $rules)) return $c;
		return self::safe($request, 'store', function () use ($request, $u) {
			DB::beginTransaction();
			try {
				Log::info(__METHOD__ . ' creating', ['creator' => $u->creatorId()]);
				$imageName = '';
				if ($request->hasFile('pro_image')) {
					$size = $request->file('pro_image')->getSize();
					if (Utility::updateStorageLimit($u->creatorId(), $size) === 1) {
						$imageName = $request->pro_image->getClientOriginalName();
						Utility::uploadFile($request, 'pro_image', $imageName, 'uploads/pro_image', []);
					}
				}
				$data = array_merge(
					$request->only([
						'name', 'description', 'sku', 'sale_price',
						'purchase_price', 'unit_id', 'quantity', 'type',
						'sale_chartaccount_id', 'expense_chartaccount_id', 'category_id'
					]),
					[
						'tax_id' => $request->filled('tax_id') ? implode(',', $request->tax_id) : '',
						'quantity' => $request->filled('quantity') ? $request->quantity : 0,
						'pro_image' => $imageName,
						'created_by' => $u->creatorId()
					]
				);
				$product = ProductService::create($data);
				CustomField::saveData($product, $request->customField);
				DB::commit();
				Log::info(__METHOD__ . ' success', ['id' => $product->id]);
				return redirect()->route(self::REDIRECT_INDEX)
					->with('success', __('Product successfully created.'));
			} catch (Throwable $e) {
				DB::rollBack();
				Log::error(__METHOD__ . ' failed', ['error' => $e->getMessage()]);
				throw $e;
			}
		});
	}

	public function edit(Request $request, string|int $id): View|RedirectResponse
	{
		if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
		if ($c = self::guard($request, 'edit product & service', self::REDIRECT_INDEX)) return $c;
		return self::safe($request, 'edit', function () use ($id, $u) {
			Log::info(__METHOD__ . ' loading', ['id' => $id]);
			$product    = ProductService::whereCreatedBy($u->creatorId())->findOrFail($id);
			$collections = self::formCollections($u->creatorId());
			$product->tax_id    = explode(',', $product->tax_id);
			$product->customField = CustomField::getData($product, 'product');
			$custom     = CustomField::whereCreatedBy($u->creatorId())
				->whereModule('product')->get();
			return view('productservice.edit', array_merge(
				$collections,
				['productService' => $product, 'customFields' => $custom]
			));
		});
	}

	public function update(Request $request, string|int $id): RedirectResponse
	{
		if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
		if ($c = self::guard($request, 'edit product & service', self::REDIRECT_INDEX)) return $c;
		$rules = [
			'name' => 'required',
			'sku' => "required|unique:product_services,sku,{$id}",
			'sale_price' => 'required|numeric',
			'purchase_price' => 'required|numeric',
			'category_id' => 'required',
			'unit_id' => 'required',
			'type' => 'required'
		];
		if ($c = self::validateRequest($request, $rules)) return $c;
		return self::safe($request, 'update', function () use ($request, $id, $u) {
			DB::beginTransaction();
			try {
				Log::info(__METHOD__ . ' updating', ['id' => $id]);
				$product  = ProductService::whereCreatedBy($u->creatorId())->findOrFail($id);
				$imageName = $product->pro_image;
				if ($request->hasFile('pro_image')) {
					$size = $request->file('pro_image')->getSize();
					if (Utility::updateStorageLimit($u->creatorId(), $size) === 1) {
						$imageName && Utility::changeStorageLimit(
							$u->creatorId(),
							"/uploads/pro_image/{$imageName}"
						);
						$imageName = $request->pro_image->getClientOriginalName();
						Utility::uploadFile($request, 'pro_image', $imageName, 'uploads/pro_image', []);
					}
				}
				$data = array_merge($request->only([
					'name', 'description', 'sku', 'sale_price',
					'purchase_price', 'unit_id', 'quantity', 'type',
					'sale_chartaccount_id', 'expense_chartaccount_id', 'category_id'
				]), [
					'tax_id' => $request->filled('tax_id') ? implode(',', $request->tax_id) : '',
					'quantity' => $request->filled('quantity') ? $request->quantity : 0,
					'pro_image' => $imageName
				]);
				$product->update($data);
				CustomField::saveData($product, $request->customField);
				DB::commit();
				Log::info(__METHOD__ . ' success', ['id' => $id]);
				return redirect()->route(self::REDIRECT_INDEX)
					->with('success', __('Product successfully updated.'));
			} catch (Throwable $e) {
				DB::rollBack();
				Log::error(__METHOD__ . ' failed', ['error' => $e->getMessage()]);
				throw $e;
			}
		});
	}

	public function destroy(Request $request, string|int $id): RedirectResponse
	{
		if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
		if ($c = self::guard($request, 'delete product & service', self::REDIRECT_INDEX)) return $c;
		return self::safe($request, 'destroy', function () use ($id, $u) {
			DB::beginTransaction();
			try {
				Log::info(__METHOD__ . ' deleting', ['id' => $id]);
				$product = ProductService::whereCreatedBy($u->creatorId())->findOrFail($id);
				$product->pro_image && Utility::changeStorageLimit(
					$u->creatorId(),
					"/uploads/pro_image/{$product->pro_image}"
				);
				$product->delete();
				DB::commit();
				Log::info(__METHOD__ . ' success', ['id' => $id]);
				return redirect()->route(self::REDIRECT_INDEX)
					->with('success', __('Product successfully deleted.'));
			} catch (Throwable $e) {
				DB::rollBack();
				Log::error(__METHOD__ . ' failed', ['error' => $e->getMessage()]);
				throw $e;
			}
		});
	}
}
