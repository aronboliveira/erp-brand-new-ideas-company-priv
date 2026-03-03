<?php

namespace App\Http\Controllers;

use App\Config\Constants\{
    PermissionsConstants,
    ViewsConstants,
    ViewClassNamesConstants
};
use App\Exports\ProductServiceExport;
use App\Imports\ProductServiceImport;
use App\Models\{
    ChartOfAccount,
    ChartOfAccountType,
    CustomField,
    Product,
    ProductService,
    ProductServiceCategory,
    ProductServiceUnit,
    Tax,
    User,
    Utility,
    Vendor,
    WarehouseProduct
};
use App\Traits\{ChecksLogin, ChecksPermissions};
use Illuminate\Http\{
    JsonResponse,
    RedirectResponse,
    Request
};
use Illuminate\Support\Facades\{
    Auth,
    DB,
    Log,
    Storage,
    Validator
};
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;

final class ProductServiceController extends Controller
{
    use ChecksLogin, ChecksPermissions;

    private const REDIRECT_INDEX = ViewsConstants::PRD_SV . '.index';

    public function index(Request $req)
    {
        $cls = __CLASS__;
        $fn = __FUNCTION__;
        $action = "$cls::$fn";
        $view = ViewsConstants::PRD_SV . '.' . $fn;
        if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
        $user = $userOrRedirect;
        if (($c = self::guard($req, PermissionsConstants::MNG_PRD_SV, self::REDIRECT_INDEX)) !== true) return $c;
        return $this->measureProfile($action, function () use ($req, $user, $view) {
            $category = ProductServiceCategory::whereCreatedBy($user?->creatorId())
                ->whereType('product & service')
                ->pluck('name', 'id')
                ->prepend('Select Category', '');
            $products = ProductService::whereCreatedBy($user?->creatorId())
                ->when($req->filled('category'), fn($q) => $q->whereCategoryId($req->category))
                ->with(['category', 'unit'])
                ->get();
            return view($view, ['productServices' => $products, 'category' => $category]);
        });
    }

    public function create(Request $req)
    {
        $cls = __CLASS__;
        $fn = __FUNCTION__;
        $action = "$cls::$fn";
        $view = ViewsConstants::PRD_SV . '.' . $fn;

        if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
        $user = $userOrRedirect;
        if (($c = self::guard($req, 'create product & service', self::REDIRECT_INDEX)) !== true) return $c;

        return $this->measureProfile($action, function () use ($user, $view) {
            $collections = self::formCollections($user?->creatorId());
            $custom = CustomField::whereCreatedBy($user?->creatorId())->whereModule('product')->get();
            return view($view, array_merge($collections, ['customFields' => $custom]));
        });
    }

    public function store(Request $req)
    {
        $cls = __CLASS__;
        $fn = __FUNCTION__;
        $action = "$cls::$fn";

        if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
        $user = $userOrRedirect;
        if (($c = self::guard($req, 'create product & service', self::REDIRECT_INDEX)) !== true) return $c;

        $rules = [
            'name' => 'required',
            'sku' => 'required|unique:product_services,sku,NULL,id,created_by,' . $user?->id,
            'sale_price' => 'required|numeric',
            'purchase_price' => 'required|numeric',
            'category_id' => 'required',
            'unit_id' => 'required',
            'type' => 'required'
        ];
        if ($c = self::v($req, $rules)) return $c;

        return $this->measureProfile($action, function () use ($req, $user) {
            $imageName = '';
            if ($req->hasFile('pro_image')) {
                $size = $req->file('pro_image')->getSize();
                $result = Utility::updateStorageLimit($user?->creatorId(), $size);
                if ($result === 1) {
                    $imageName = preg_replace('/[^A-Za-z0-9_\-\.]/', '_', $req->pro_image->getClientOriginalName());
                    Utility::uploadFile($req, 'pro_image', $imageName, 'uploads/pro_image', []);
                }
            }

            $data = collect($req->only([
                'name',
                'description',
                'sku',
                'sale_price',
                'purchase_price',
                'unit_id',
                'quantity',
                'type',
                'sale_chart_account_id',
                'expense_chart_account_id',
                'category_id'
            ]))->merge([
                'tax_id' => $req->filled('tax_id') ? implode(',', $req->tax_id) : '',
                'quantity' => $req->filled('quantity') ? $req->quantity : 0,
                'pro_image' => $imageName,
                'created_by' => $user?->creatorId()
            ])->all();

            $product = ProductService::create($data);
            CustomField::saveData($product, $req->customField);

            return redirect()->route(self::REDIRECT_INDEX)->with('success', __('Product successfully created.'));
        });
    }

    public function edit(Request $req, string|int $id)
    {
        $cls = __CLASS__;
        $fn = __FUNCTION__;
        $action = "$cls::$fn";
        $view = ViewsConstants::PRD_SV . '.' . $fn;

        if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
        $user = $userOrRedirect;
        if (($c = self::guard($req, 'edit product & service', self::REDIRECT_INDEX)) !== true) return $c;

        return $this->measureProfile($action, function () use ($id, $user, $view) {
            $product = ProductService::whereCreatedBy($user?->creatorId())->findOrFail($id);
            $collections = self::formCollections($user?->creatorId());
            $product->customField = CustomField::getData($product, 'product');
            $product->tax_id = explode(',', $product->tax_id);
            $custom = CustomField::whereCreatedBy($user?->creatorId())->whereModule('product')->get();
            return view($view, array_merge($collections, ['productService' => $product, 'customFields' => $custom]));
        });
    }

    public function update(Request $req, string|int $id)
    {
        $cls = __CLASS__;
        $fn = __FUNCTION__;
        $action = "$cls::$fn";

        if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
        $user = $userOrRedirect;
        if (($c = self::guard($req, 'edit product & service', self::REDIRECT_INDEX)) !== true) return $c;

        $rules = [
            'name' => 'required',
            'sku' => 'required|unique:product_services,sku,' . $id,
            'sale_price' => 'required|numeric',
            'purchase_price' => 'required|numeric',
            'category_id' => 'required',
            'unit_id' => 'required',
            'type' => 'required'
        ];
        if ($c = self::v($req, $rules)) return $c;

        return $this->measureProfile($action, function () use ($req, $id, $user) {
            $product = ProductService::whereCreatedBy($user?->creatorId())->findOrFail($id);

            $imageName = $product->pro_image;
            if ($req->hasFile('pro_image')) {
                $size = $req->file('pro_image')->getSize();
                if (Utility::updateStorageLimit($user?->creatorId(), $size) === 1) {
                    if ($imageName) {
                        Utility::changeStorageLimit($user?->creatorId(), '/uploads/pro_image/' . $imageName);
                    }
                    $imageName = preg_replace('/[^A-Za-z0-9_\-\.]/', '_', $req->pro_image->getClientOriginalName());
                    Utility::uploadFile($req, 'pro_image', $imageName, 'uploads/pro_image', []);
                }
            }

            $data = collect($req->only([
                'name',
                'description',
                'sku',
                'sale_price',
                'purchase_price',
                'unit_id',
                'quantity',
                'type',
                'sale_chart_account_id',
                'expense_chart_account_id',
                'category_id'
            ]))->merge([
                'tax_id' => $req->filled('tax_id') ? implode(',', $req->tax_id) : '',
                'quantity' => $req->filled('quantity') ? $req->quantity : 0,
                'pro_image' => $imageName
            ])->all();

            $product->update($data);
            CustomField::saveData($product, $req->customField);

            return redirect()->route(self::REDIRECT_INDEX)->with('success', __('Product successfully updated.'));
        });
    }

    public function destroy(Request $req, string|int $id)
    {
        $cls = __CLASS__;
        $fn = __FUNCTION__;
        $action = "$cls::$fn";

        if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
        $user = $userOrRedirect;
        if (($c = self::guard($req, 'delete product & service', self::REDIRECT_INDEX)) !== true) return $c;

        return $this->measureProfile($action, function () use ($id, $user) {
            $product = ProductService::whereCreatedBy($user?->creatorId())->findOrFail($id);
            if ($product->pro_image) {
                Utility::changeStorageLimit($user?->creatorId(), '/uploads/pro_image/' . $product->pro_image);
            }
            $product->delete();
            return redirect()->route(self::REDIRECT_INDEX)->with('success', __('Product successfully deleted.'));
        });
    }

    public function export(): BinaryFileResponse
    {
        $cls = __CLASS__;
        $fn = __FUNCTION__;
        $action = "$cls::$fn";

        return $this->measureProfile($action, function () {
            return Excel::download(new ProductServiceExport(), 'product_service_' . now()->format('Y-m-d_His') . '.xlsx');
        });
    }

    public const IMP_FL = 'importFile';
    public function importFile(): View
    {
        $cls = __CLASS__;
        $fn = __FUNCTION__;
        $action = "$cls::$fn";
        $view = ViewsConstants::PRD_SV . '.import';

        return $this->measureProfile($action, fn() => view($view));
    }

    public function import(Request $req): RedirectResponse
    {
        $cls = __CLASS__;
        $fn = __FUNCTION__;
        $action = "$cls::$fn";

        if ($c = self::v($req, ['file' => 'required|file'])) return $c;
        if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
        $user = $u;

        return $this->measureProfile($action, function () use ($req, $user) {
            $sheet = (new ProductServiceImport)->toArray($req->file('file'))[0];
            $header = array_map('strtolower', array_shift($sheet));
            $needed = ['name', 'sku', 'sale_price', 'purchase_price', 'quantity', 'tax_id', 'category_id', 'unit_id', 'type', 'description'];
            $missing = array_diff($needed, $header);
            if ($missing) return back()->with('error', __('Missing columns: :cols', ['cols' => implode(', ', $missing)]));

            $rowsSkipped = 0;
            DB::transaction(function () use ($sheet, $header, $user, &$rowsSkipped) {
                foreach ($sheet as $idx => $row) {
                    if (count($row) < count($header)) {
                        $rowsSkipped++;
                        continue;
                    }
                    $data = array_combine($header, $row);
                    ProductService::updateOrCreate(
                        ['sku' => $data['sku'], 'created_by' => $user?->creatorId()],
                        array_merge(
                            collect($data)->only([
                                'name',
                                'sale_price',
                                'purchase_price',
                                'quantity',
                                'tax_id',
                                'category_id',
                                'unit_id',
                                'type',
                                'description'
                            ])->toArray(),
                            ['created_by' => $user?->creatorId()]
                        )
                    );
                }
            });

            return back()->with(
                $rowsSkipped ? 'error' : 'success',
                $rowsSkipped
                    ? __('Imported with :n skipped malformed row(s).', ['n' => $rowsSkipped])
                    : __('Record successfully imported.')
            );
        });
    }

    public const WRH_DTL = 'warehouseDetail';
    public function warehouseDetail(Request $req, int|string $id): View|RedirectResponse
    {
        $cls = __CLASS__;
        $fn = __FUNCTION__;
        $action = "$cls::$fn";
        $view = ViewsConstants::PRD_SV . '.detail';

        Log::info($action . ' start', ['user_id' => Auth::id(), 'id' => $id]);
        if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
        $user = $u;
        if (($c = self::guard($req, PermissionsConstants::MNG_PRD_SV, self::REDIRECT_INDEX)) !== true) return $c;

        return $this->measureProfile($action, function () use ($id, $user, $view, $action) {
            Log::info($action . ' loading warehouse products', ['id' => $id]);
            $products = WarehouseProduct::whereProductId($id)->whereCreatedBy($user?->creatorId())->get();
            return view($view, compact('products'));
        });
    }

    public const SRC_PRD = 'searchProducts';
    public function searchProducts(Request $req)
    {
        $cls = __CLASS__;
        $fn = __FUNCTION__;
        $action = "$cls::$fn";

        if (($ur = self::_checkLogin()) instanceof RedirectResponse) return $ur;
        if (($c = self::guard($req, PermissionsConstants::MNG_POS, self::REDIRECT_INDEX)) !== true) return $c;

        return $this->measureProfile($action, function () use ($req) {
            $key = $req->session_key;
            if (!$req->ajax() || !$key) return response()->json();

            $warehouseId = $req->war_id;
            $catId = $req->cat_id;
            $search = $req->search;
            $ids = WarehouseProduct::whereWarehouseId($warehouseId ?: 1)->pluck('product_id');

            $q = ProductService::getAllProducts()
                ->when($warehouseId, fn($q) => $q->whereIn('product_services.id', $ids))
                ->when($catId !== '0', fn($q) => $q->whereCategoryId($catId))
                ->when($search !== '', fn($q) => $q->where('product_services.name', 'LIKE', "%$search%"));

            $products = $q->with('unit')->get();
            if ($products->isEmpty())
                return response('<div class="' . ViewClassNamesConstants::CD . ' card-body col-12 text-center"><h5>' . __('No Product Available') . '</h5></div>');

            if (($userOrRedirect = self::_checkLogin()) instanceof \Illuminate\Http\RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;

            $html = $products->reduce(function ($carry, $p) use ($req, $key, $user) {
                $qty = $p->warehouseProduct($p->id, $req->war_id ?: 7);
                $unit = optional($p->unit)->name ?: '';
                $img = $p->pro_image ? "uploads/pro_image/$p->pro_image" : 'uploads/pro_image/default.png';
                $price = $key === 'purchases' ? $p->purchase_price : ($key === 'pos' ? $p->sale_price : ($p->sale_price ?: $p->purchase_price));
                return $carry . '
        <div class="col-lg-2 col-md-2 col-sm-3 col-xs-4 col-12">
          <div class="tab-pane fade show active toacart w-100" data-url="' . url("add-to-cart/$p->id/$key") . '">
            <div class="position-relative ' . ViewClassNamesConstants::CD . '">
              <img src="' . asset(Storage::url($img)) . '" class="card-image avatar shadow hover-shadow-lg" style="height:6rem;width:100%;" alt="img">
              <div class="p-0 custom-card-body card-body d-flex">
                <div class="card-body my-2 p-2 text-left card-bottom-content">
                  <h6 class="mb-2 text-dark product-title-name">' . $p->name . '</h6>
                  <small class="badge badge-primary mb-0">' . $user?->priceFormat($price) . '</small>
                  <small class="top-badge badge badge-danger mb-0">' . $qty . ' ' . $unit . '</small>
                </div>
              </div>
            </div>
          </div>
        </div>';
            }, '');

            return response($html);
        });
    }

    public const ADD_CRT = 'addToCart';
    public function addToCart(Request $req, string|int $id, string $key)
    {
        $cls = __CLASS__;
        $fn = __FUNCTION__;
        $action = "$cls::$fn";

        if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
        if (!$req->ajax()) return response()->json(['code' => 404], 404);
        if (($c = self::guard($req, PermissionsConstants::MNG_PRD_SV, self::REDIRECT_INDEX)) !== true) return $c;

        return $this->measureProfile($action, function () use ($id, $key) {
            $product = ProductService::find($id);
            if (!$product) return response()->json(['code' => 404], 404);
            $stock = $product->getTotalProductQuantity();
            if ($key === 'pos' && $stock === 0) return response()->json(['code' => 404, 'error' => __('Out of stock')], 404);
            $price = match ($key) {
                'purchases' => $product->purchase_price ?: 0,
                'pos' => $product->sale_price ?: 0,
                default => $product->sale_price ?: $product->purchase_price
            };
            $taxRate = Utility::totalTaxRate($product->tax_id);
            $session = session()->get($key, []);
            $item = $session[$id] ?? [
                'name' => $product->name,
                'price' => $price,
                'tax' => $taxRate,
                'quantity' => 0,
                'product_tax' => collect(Utility::tax($product->tax_id))->pluck('name')->implode(', '),
                'product_tax_id' => $product->tax_id,
                'originalquantity' => $stock,
                'id' => $id
            ];
            $item['quantity']++;
            if ($item['quantity'] > $stock && $key === 'pos') return response()->json(['code' => 404, 'error' => __('Out of stock')], 404);
            $item['subtotal'] = ($item['price'] * $item['quantity']) + (($item['price'] * $item['quantity'] * $item['tax']) / 100);
            $session[$id] = $item;
            session()->put($key, $session);
            return response()->json(['code' => 200, 'product' => $item, 'carttotal' => $session]);
        });
    }

    public const UPD_CRT = 'updateCart';
    public function updateCart(Request $req)
    {
        $cls = __CLASS__;
        $fn = __FUNCTION__;
        $action = "$cls::$fn";

        if (($ur = self::_checkLogin()) instanceof RedirectResponse) return $ur;
        if (!$req->ajax()) return response()->json(['code' => 404], 404);
        if (($c = self::guard($req, PermissionsConstants::MNG_PRD_SV, self::REDIRECT_INDEX)) !== true) return $c;

        return $this->measureProfile($action, function () use ($req) {
            ['id' => $id, 'quantity' => $qty, 'discount' => $disc, 'session_key' => $key] = $req->only(['id', 'quantity', 'discount', 'session_key']);
            $cart = session()->get($key, []);
            if (!isset($cart[$id])) return response()->json(['code' => 404], 404);
            if ($qty == 0) unset($cart[$id]);
            else {
                $cart[$id]['quantity'] = $qty;
                $sub = $cart[$id]['price'] * $qty;
                $cart[$id]['subtotal'] = $sub + ($sub * $cart[$id]['tax'] / 100);
                if ($cart[$id]['quantity'] > $cart[$id]['originalquantity'] && $key === 'pos') return response()->json(['code' => 404, 'error' => __('Out of stock')], 404);
            }
            session()->put($key, $cart);
            $total = array_sum(array_column($cart, 'subtotal')) - ($disc ?: 0);
            return response()->json(['code' => 200, 'discount' => User::priceFormats($total)]);
        });
    }

    public const EMP_CRT = 'emptyCart';
    public function emptyCart(Request $req)
    {
        $cls = __CLASS__;
        $fn = __FUNCTION__;
        $action = "$cls::$fn";

        if (($ur = self::_checkLogin()) instanceof RedirectResponse) return $ur;
        if (($c = self::guard($req, PermissionsConstants::MNG_PRD_SV, self::REDIRECT_INDEX)) !== true) return $c;

        return $this->measureProfile($action, function () use ($req) {
            $key = $req->session_key;
            session()->forget($key);
            return back()->with('error', __('Cart is empty!'));
        });
    }

    public const WRH_EMP_CRT = 'warehouseEmptyCart';
    public function warehouseEmptyCart(Request $req): JsonResponse
    {
        $cls = __CLASS__;
        $fn = __FUNCTION__;
        $action = "$cls::$fn";

        return $this->measureProfile($action, function () use ($req) {
            session()->forget($req->session_key);
            return response()->json();
        });
    }

    public const RM_CRT = 'removeFromCart';
    public function removeFromCart(Request $req)
    {
        $cls = __CLASS__;
        $fn = __FUNCTION__;
        $action = "$cls::$fn";

        if (($ur = self::_checkLogin()) instanceof RedirectResponse) return $ur;
        if (($c = self::guard($req, PermissionsConstants::MNG_PRD_SV, self::REDIRECT_INDEX)) !== true) return $c;

        return $this->measureProfile($action, function () use ($req) {
            $key = $req->session_key;
            $id = $req->id;
            $cart = session()->get($key, []);
            unset($cart[$id]);
            session()->put($key, $cart);
            return back()->with('error', __('Product removed from cart!'));
        });
    }

    public function show(Request $req, ProductService $productService)
    {
        $cls = __CLASS__;
        $fn = __FUNCTION__;
        $action = "$cls::$fn";
        $view = ViewsConstants::PRD_SV . '.show';
        Log::info($action, ['user_id' => Auth::id(), 'product_service_id' => $productService->id]);
        if (($ur = self::_checkLogin()) instanceof RedirectResponse) return $ur;
        $user = $ur;
        if (($c = self::guard($req, 'view product & service', self::REDIRECT_INDEX)) !== true) return $c;
        return $this->measureProfile($action, function () use ($productService, $user, $view) {
            $collections = self::formCollections($user?->creatorId());
            $productService->tax_id = explode(',', $productService->tax_id);
            $productService->customField = CustomField::getData($productService, 'product')->toArray();
            return view($view, array_merge($collections, ['productService' => $productService, 'customFields' => $productService->customField]));
        });
    }


    private static function safe(
        Request  $req,
        string   $ref,
        \Closure $fn
    ): RedirectResponse|JsonResponse|View {
        try {
            return $fn();
        } catch (Throwable $e) {
            return defaultUndefinedException(
                $req,
                $e,
                __CLASS__ . '::' . $ref,
                route(self::REDIRECT_INDEX)
            );
        }
    }

    private static function v(Request $req, array $rules): ?RedirectResponse
    {
        $v = Validator::make($req->all(), $rules);
        return $v->fails()
            ? redirect()->back()->with('error', $v->getMessageBag()->first())
            : null;
    }

    private static function formCollections(string|int $creator): array
    {
        $category = ProductServiceCategory::whereCreatedBy($creator)
            ->whereType('product & service')
            ->pluck('name', 'id')
            ->prepend('Select Category', '');
        $unit    = ProductServiceUnit::whereCreatedBy($creator)
            ->pluck('name', 'id');
        $tax     = Tax::whereCreatedBy($creator)->pluck('name', 'id');
        $income  = ChartOfAccount::selectRaw(
            'CONCAT(code," - ",name) AS code_name,id'
        )
            ->leftJoin('chart_of_account_types', 'chart_of_account_types.id', '=', 'chart_of_accounts.type')
            ->where('chart_of_account_types.name', 'income')
            ->where('chart_of_accounts.created_by', $creator)
            ->pluck('code_name', 'id')
            ->prepend('Select Account', '');
        $expense = ChartOfAccount::selectRaw(
            'CONCAT(code," - ",name) AS code_name,id'
        )
            ->leftJoin('chart_of_account_types', 'chart_of_account_types.id', '=', 'chart_of_accounts.type')
            ->whereIn('chart_of_account_types.name', ['Expenses', 'Costs of Goods Sold'])
            ->where('chart_of_accounts.created_by', $creator)
            ->pluck('code_name', 'id')
            ->prepend('Select Account', '');
        return compact('category', 'unit', 'tax', 'income', 'expense');
    }
}
