<?php

namespace App\Http\Controllers;

use App\Config\Constants\{
  BillsConstants,
  DatabaseConstants,
  PermissionsConstants,
  SettingsConstants,
  UsersConstants,
  ViewsConstants
};
use App\Mail\SelledInvoice;
use App\Models\{
  Customer,
  Pos,
  PosPayment,
  PosProduct,
  ProductService,
  StockReport,
  User,
  Utility,
  Warehouse,
  WarehouseProduct
};
use App\Traits\ChecksLogin;
use Illuminate\Http\{
  JsonResponse,
  RedirectResponse,
  Request,
  Response
};
use Illuminate\Support\Facades\{
  Auth,
  Crypt,
  DB,
  Log,
  Mail,
  Storage,
  Validator
};
use Illuminate\View\View;
use Throwable;

final class PosController extends Controller
{

  use ChecksLogin;

  public function index(Request $req): Response|RedirectResponse|JsonResponse|null
  {
    Log::info('POS index requested', [UsersConstants::COL_USER_ID => $req->user()?->id]);
    if (($r = self::_checkLogin()) instanceof RedirectResponse) {
      Log::warning('POS index: unauthenticated');
      return $r;
    }
    if (($r = self::_authorize($req, PermissionsConstants::MNG_POS)) !== null)
      return $r;
    try {
      $user     = $req->user();
      $creatorId = $user?->creatorId();
      $customers = Customer::where(DatabaseConstants::TABLE_CREATOR, $creatorId)
        ->pluck(UsersConstants::COL_USER_ID, 'name')
        ->prepend('Walk-in-customer', '');
      $warehouses = Warehouse::where(DatabaseConstants::TABLE_CREATOR, $creatorId)
        ->pluck('name', 'id');
      $details = [
        'pos_id'    => $user?->posNumberFormat(self::invoiceNumber()),
        'customer' => $customers->toArray(),
        'user'     => $user?->toArray(),
        'date'     => now()->toDateString(),
        'pay'      => 'show',
      ];
      Log::info('POS index data prepared', ['method' => __METHOD__]);
      return response()->view(ViewsConstants::POS . '.' . __FUNCTION__, compact('customers', 'warehouses', 'details'));
    } catch (Throwable $e) {
      return self::handleException($req, $e);
    }
  }

  public function create(Request $req): Response|RedirectResponse|JsonResponse|null
  {
    Log::info('POS create requested', [UsersConstants::COL_USER_ID => $req->user()?->id]);
    if (($r = self::_authorize($req, PermissionsConstants::MNG_POS)) !== null)
      return $r;
    if (($u = self::_checkLogin()) instanceof RedirectResponse) {
      Log::warning('POS create: unauthenticated');
      return $u;
    }
    try {
      $cart = session('pos', []);
      if (empty($cart)) {
        Log::warning('POS create: empty cart', ['method' => __METHOD__]);
        return response()->json(['error' => 'Add some products to cart!'], 404);
      }
      $user       = $u;
      $creatorId  = $user?->creatorId();
      $customer   = Customer::where('name', $req->vc_name)->where(DatabaseConstants::TABLE_CREATOR, $creatorId)->firstOrFail();
      $warehouse  = Warehouse::where('id', $req->warehouse_name)->where(DatabaseConstants::TABLE_CREATOR, $creatorId)->firstOrFail();
      $details    = [
        'posId'    => $user?->posNumberFormat(self::invoiceNumber()),
        'customer' => $customer->toArray(),
        'warehouse' => $warehouse->toArray(),
        'user'     => $user?->toArray(),
        'date'     => now()->toDateString(),
        'pay'      => 'show',
      ];
      $settings = Utility::settings();
      $this->_composeDetails($details, $settings);
      [$sales, $subtotal] = $this->_summarizeCart($cart, $req);
      $discount           = $req->discount ?? 0;
      $sales['discount']  = $user?->priceFormat($discount);
      $sales['subTotal']  = $user?->priceFormat($subtotal);
      $sales['total']     = $user?->priceFormat($subtotal - $discount);
      Log::info('POS create data prepared', ['method' => __METHOD__]);
      return response()->view(ViewsConstants::POS . '.show', compact('sales', 'details'));
    } catch (Throwable $e) {
      return self::handleException($req, $e);
    }
  }

  public function store(Request $req): JsonResponse|RedirectResponse|null
  {
    Log::info('POS store requested', [UsersConstants::COL_USER_ID => $req->user()?->id]);
    if (($u = self::_checkLogin()) instanceof RedirectResponse) {
      Log::warning('POS store: unauthenticated');
      return $u;
    }
    if (($r = self::_authorize($req, PermissionsConstants::MNG_POS)) !== null)
      return $r;
    try {
      return DB::transaction(function () use ($req, $u) {
        $creatorId = $u->creatorId();
        $cart     = session('pos', []);
        if (empty($cart)) {
          Log::warning('POS store: empty cart during transaction');
          return response()->json(['code' => 404, 'success' => 'Items not found!']);
        }
        $pid = self::invoiceNumber();
        if (Pos::where('pos_id', $pid)->where(DatabaseConstants::TABLE_CREATOR, $creatorId)->exists()) {
          Log::info('POS store: duplicate payment', ['pos_id' => $pid]);
          return response()->json(['code' => 200, 'success' => 'Payment is already completed!']);
        }
        $customer_id = Customer::customerId($req->vc_name);
        $warehouseId = Warehouse::warehouseId($req->warehouse_name);
        $pos        = Pos::create([
          'pos_id'       => $pid,
          'customer_id'  => $customer_id,
          'warehouse_id' => $warehouseId,
          'pos_date'     => now()->toDateString(),
          DatabaseConstants::TABLE_CREATOR   => $creatorId,
        ]);
        Log::info('Pos record created', ['pos_id' => $pos->id]);
        foreach ($cart as $item) {
          $prod = ProductService::where('id', $item['id'])
            ->where(DatabaseConstants::TABLE_CREATOR, $creatorId)
            ->lockForUpdate()
            ->firstOrFail();
          if ($prod->quantity < $item['quantity']) {
            Log::error('Insufficient stock', [
              'product_id' => $prod->id,
              'required'   => $item['quantity'],
              'available'  => $prod->quantity,
            ]);
            throw new \Exception("Insufficient stock for product {$prod->id}");
          }
          $prod->decrement('quantity', $item['quantity']);
          $taxId = ProductService::taxId($item['id']);
          PosProduct::create([
            'pos_id'    => $pos->id,
            'product_id' => $item['id'],
            'price'     => $item['price'],
            'quantity'  => $item['quantity'],
            'tax'       => $taxId,
            'discount'  => $req->discount,
          ]);
          Utility::warehouseQuantity('minus', $item['quantity'], $item['id'], $warehouseId);
          StockReport::where('type', 'pos')->where('type_id', $pos->id)->delete();
          Log::info('PosProduct created and stock updated', ['pos_id' => $pos->id, 'product_id' => $item['id']]);
        }
        $subtotal = array_sum(array_column($cart, 'subtotal'));
        PosPayment::create([
          'pos_id'          => $pos->id,
          'date'            => $req->date,
          'amount'          => $subtotal,
          'discount'        => $req->discount,
          'discount_amount' => $subtotal - $req->discount,
        ]);
        session()->forget('pos');
        Log::info('PosPayment created and cart cleared', ['pos_id' => $pos->id]);
        return response()->json(['code' => 200, 'success' => 'Payment completed successfully!']);
      });
    } catch (Throwable $e) {
      return self::handleException($req, $e);
    }
  }

  public function show(Request $req, string $encId): Response|RedirectResponse|JsonResponse|null
  {
    Log::info('POS show requested', ['encrypted_id' => $encId]);
    if (($r = self::_authorize($req, PermissionsConstants::MNG_POS)) !== null)
      return $r;
    try {
      $id = Crypt::decrypt($encId);
      $pos = Pos::findOrFail($id);
      if ($pos[DatabaseConstants::TABLE_CREATOR] !== $req->user()->creatorId()) {
        Log::warning('POS show: unauthorized access', ['pos_id' => $id]);
        return defaultPermissionDenial(
          $req,
          new \Exception('permission denied'),
          __CLASS__ . '::' . __FUNCTION__
        );
      }
      Log::info('POS show data prepared', ['pos_id' => $id]);
      return response()->view(ViewsConstants::POS . '.view', [
        'pos'        => $pos,
        'customer'   => $pos->customer,
        'items'      => $pos->items,
        'posPayment' => PosPayment::where('pos_id', $pos->id)->first(),
      ]);
    } catch (Throwable $e) {
      return self::handleException($req, $e);
    }
  }

  public function report(Request $req): Response|RedirectResponse|JsonResponse|null
  {
    Log::info('POS report requested', [
      'method'  => __METHOD__,
      UsersConstants::COL_USER_ID => $req->user()?->id,
    ]);
    if ($r = self::_authorize($req, PermissionsConstants::MNG_POS)) {
      Log::warning('POS report: permission denied', [
        'method'  => __METHOD__,
        UsersConstants::COL_USER_ID => $req->user()?->id,
      ]);
      return $r;
    }
    try {
      $pps = Pos::where(DatabaseConstants::TABLE_CREATOR, $req->user()->creatorId())
        ->with(['customer', 'warehouse'])
        ->get();
      Log::info('POS report: records loaded', [
        'method' => __METHOD__,
        'count'  => $pps->count(),
      ]);
      return response()->view(ViewsConstants::POS . '.' . __FUNCTION__, ['posPayments' => $pps]);
    } catch (Throwable $e) {
      return self::handleException($req, $e);
    }
  }

  public function barcode(Request $req): Response|RedirectResponse|JsonResponse|null
  {
    Log::info('POS barcode page requested', [
      'method' => __METHOD__,
      UsersConstants::COL_USER_ID => $req->user()?->id,
    ]);
    if (($u = self::_checkLogin()) instanceof RedirectResponse) {
      Log::warning('POS barcode: unauthenticated');
      return $u;
    }
    if ($r = self::_authorize($req, PermissionsConstants::MNG_POS)) {
      Log::warning('POS barcode: permission denied', [UsersConstants::COL_USER_ID => $u->id]);
      return $r;
    }
    try {
      $prods = ProductService::where(DatabaseConstants::TABLE_CREATOR, $u->creatorId())->get();
      $barcode = [
        'barcodeType'   => $u->barcodeType(),
        'barcodeFormat' => $u->barcodeFormat(),
      ];
      Log::info('POS barcode: data ready', [
        'method'        => __METHOD__,
        'products_count' => $prods->count(),
      ]);
      return response()->view(ViewsConstants::POS . '.' . __FUNCTION__, [
        'productServices' => $prods,
        'barcode'         => $barcode,
      ]);
    } catch (Throwable $e) {
      return self::handleException($req, $e);
    }
  }

  public function setting(Request $req): Response|RedirectResponse|JsonResponse|null
  {
    Log::info('POS setting page requested', [
      'method'  => __METHOD__,
      UsersConstants::COL_USER_ID => $req->user()?->id,
    ]);
    if ($r = self::_authorize($req, PermissionsConstants::MNG_POS)) {
      Log::warning('POS setting: permission denied', [UsersConstants::COL_USER_ID => $req->user()->id]);
      return $r;
    }
    $settings = Utility::settings();
    Log::info('POS setting: settings loaded', [
      'method'   => __METHOD__,
      'settings' => $settings,
    ]);
    return response()->view(ViewsConstants::POS . '.' . __FUNCTION__, ['settings' => $settings]);
  }

  public function barcodeSettingStore(Request $req): RedirectResponse
  {
    Log::info('POS barcodeSettingStore called', ['method' => __METHOD__]);
    if ($r = self::_validate($req->all(), [
      'barcode_type'   => 'required',
      'barcode_format' => 'required',
    ])) {
      Log::warning('POS barcodeSettingStore: validation failed', [
        'errors' => $req->all(),
        'method' => __METHOD__,
      ]);
      return $r;
    }
    Log::info('POS barcodeSettingStore: validation passed', ['method' => __METHOD__]);
    $userId = Auth::id();
    $data  = $req->only(['barcode_type', 'barcode_format']);
    DB::transaction(function () use ($data, $userId) {
      $creatorCol = DatabaseConstants::TABLE_CREATOR;
      foreach ($data as $key => $value)
        DB::insert(
          'INSERT INTO settings (`value`,`name`,`' . $creatorCol . '`) VALUES (?,?,?)
           ON DUPLICATE KEY UPDATE `value`=VALUES(`value`)',
          [$value, $key, $userId]
        );
    });
    Log::info('POS barcodeSettingStore: settings updated', [
      'method'  => __METHOD__,
      UsersConstants::COL_USER_ID => $userId,
      'data'    => $data,
    ]);
    return redirect()->back()
      ->with('success', __('Barcode setting successfully updated.'));
  }

  public function printBarcode(Request $req): Response|RedirectResponse|JsonResponse|null
  {
    Log::info('POS printBarcode requested', [
      'method'  => __METHOD__,
      UsersConstants::COL_USER_ID => $req->user()?->id,
    ]);
    if ($r = self::_authorize($req, PermissionsConstants::MNG_POS)) {
      Log::warning('POS printBarcode: permission denied', [UsersConstants::COL_USER_ID => $req->user()->id]);
      return $r;
    }
    $wh = Warehouse::where(DatabaseConstants::TABLE_CREATOR, $req->user()->creatorId())
      ->pluck('name', 'id');
    Log::info('POS printBarcode: warehouses loaded', [
      'method'      => __METHOD__,
      'warehouses'  => $wh->count(),
    ]);
    return response()->view(ViewsConstants::POS . '.print', ['warehouses' => $wh]);
  }

  public function getProduct(Request $req): JsonResponse|RedirectResponse|null
  {
    Log::info('POS getProduct called', [
      'method'       => __METHOD__,
      'warehouse_id' => $req->warehouse_id,
    ]);
    if (($u = self::_checkLogin()) instanceof RedirectResponse) {
      Log::warning('POS getProduct: unauthenticated');
      return $u;
    }
    $creatorId = $u->creatorId();
    $prodIds  = WarehouseProduct::where(DatabaseConstants::TABLE_CREATOR, $creatorId)
      ->where('warehouse_id', $req->warehouse_id)
      ->pluck('product_id');
    $prods = ProductService::where(DatabaseConstants::TABLE_CREATOR, $creatorId)
      ->whereIn('id', $prodIds)
      ->pluck('name', 'id');
    Log::info('POS getProduct: products fetched', [
      'method'        => __METHOD__,
      'product_count' => $prods->count(),
    ]);
    return response()->json($prods->toArray());
  }

  public function cartDiscount(Request $req): JsonResponse
  {
    Log::info('POS cartDiscount called', [
      'method'   => __METHOD__,
      'discount' => $req->discount,
    ]);
    $cart = session('pos', []);
    $sub = array_sum(array_column($cart, 'subtotal'));
    $tot = User::priceFormats($sub - ($req->discount ?? 0));
    Log::info('POS cartDiscount: total calculated', [
      'method' => __METHOD__,
      'total'  => $tot,
    ]);
    return response()->json(['total' => $tot]);
  }

  public function posView(Request $req, string $encId): Response|RedirectResponse|JsonResponse|null
  {
    Log::info('POS posView requested', [
      'method' => __METHOD__,
      'encId'  => $encId,
      UsersConstants::COL_USER_ID => $req->user()?->id,
    ]);
    if ($r = self::_authorize($req, PermissionsConstants::MNG_POS)) {
      Log::warning('POS posView: permission denied', [UsersConstants::COL_USER_ID => $req->user()->id]);
      return $r;
    }
    try {
      $id = Crypt::decrypt($encId);
      $pos = Pos::findOrFail($id);
      if ($pos[DatabaseConstants::TABLE_CREATOR] !== $req->user()->creatorId()) {
        Log::warning('POS posView: unauthorized access', ['pos_id' => $id]);
        return defaultPermissionDenial($req, new \Exception('permission denied'), __CLASS__ . '::posView');
      }
      Log::info('POS posView: rendering template', ['pos_id' => $id]);
      return response()->view(
        ViewsConstants::POS_TMP . $this->_template($pos[DatabaseConstants::TABLE_CREATOR]),
        $this->_templateData($pos)
      );
    } catch (Throwable $e) {
      return self::handleException($req, $e);
    }
  }

  public const PV_POS = 'previewPos';
  public function previewPos(Request $req, string $tpl, string $col): Response
  {
    Log::info('POS previewPos requested', [
      'method' => __METHOD__,
      'tpl'    => $tpl,
      'col'    => $col,
    ]);
    $view = $this->_previewData($tpl, $col);
    Log::info('POS previewPos: view data composed', ['method' => __METHOD__]);
    return response()->view(ViewsConstants::PRC_TMP . 'settings' . $tpl, $view);
  }

  public const SV_POS_TMP = 'savePosTemplateSettings';
  public function savePosTemplateSettings(Request $req): RedirectResponse
  {
    Log::info('POS ' . __FUNCTION__ . ' called', ['method' => __METHOD__]);
    $userOrRedirect = self::_checkLogin();
    if ($userOrRedirect instanceof RedirectResponse) {
      Log::warning(__FUNCTION__ . ': unauthenticated');
      return $userOrRedirect;
    }
    $userId = $userOrRedirect->creatorId();
    $data  = $req->except('_token');
    $data['pos_color'] ??= 'ffffff';
    if ($req->hasFile('pos_logo')) {
      $path = Utility::uploadFile(
        $req,
        'pos_logo',
        $userId . '_logo.png',
        'pos_logo/',
        ['mimes:png', 'max:' . SettingsConstants::MAX_U_SIZE_DEF]
      );
      if ($path['flag'] === 0) {
        Log::error(__FUNCTION__ . ': logo upload failed', ['msg' => $path['msg']]);
        return redirect()->back()->with('error', __($path['msg']));
      }
      $data['pos_logo'] = $userId . '_logo.png';
      Log::info(__FUNCTION__ . ': logo uploaded', ['filename' => $data['pos_logo']]);
    }
    DB::transaction(function () use ($data, $userId) {
      $creatorCol = DatabaseConstants::TABLE_CREATOR;
      foreach ($data as $k => $v)
        DB::insert(
          'INSERT INTO settings (`value`,`name`,`' . $creatorCol . '`) VALUES (?,?,?)
           ON DUPLICATE KEY UPDATE `value`=VALUES(`value`)',
          [$v, $k, $userId]
        );
    });
    Log::info(__FUNCTION__ . ': settings saved', [
      UsersConstants::COL_USER_ID => $userId,
      'data'    => $data,
    ]);
    return redirect()->back()
      ->with('success', __('POS Setting updated successfully'));
  }

  public const PRT_VW = 'printView';
  public function printView(Request $req): Response|RedirectResponse|JsonResponse|null
  {
    Log::info('POS printView requested', ['method' => __METHOD__, UsersConstants::COL_USER_ID => $req->user()?->id]);
    if (($u = self::_checkLogin()) instanceof RedirectResponse) {
      Log::warning('POS printView: unauthenticated');
      return $u;
    }
    if ($r = self::_authorize($req, PermissionsConstants::MNG_POS)) {
      Log::warning('POS printView: permission denied', [UsersConstants::COL_USER_ID => $u->id]);
      return $r;
    }
    try {
      $cart = session('pos', []);
      if (empty($cart)) {
        Log::warning('POS printView: empty cart');
        return redirect()->back()->with('error', 'Cart is empty.');
      }
      $creatorId = $u->creatorId();
      $customer = Customer::where('name', $req->vc_name)
        ->where(DatabaseConstants::TABLE_CREATOR, $creatorId)
        ->firstOrFail();
      $warehouse = Warehouse::where('id', $req->warehouse_name)
        ->where(DatabaseConstants::TABLE_CREATOR, $creatorId)
        ->firstOrFail();
      $details = [
        'pos_id'    => $u->posNumberFormat(self::invoiceNumber()),
        'customer'  => $customer->toArray(),
        'warehouse' => $warehouse->toArray(),
        'user'      => $u->toArray(),
        'date'      => now()->toDateString(),
        'pay'       => 'show',
      ];
      $settings = Utility::settings();
      $this->_composeDetails($details, $settings);
      [$sales, $subtotal] = $this->_summarizeCart($cart, $req);
      $disc = $req->discount ?? 0;
      $sales['discount'] = $u->priceFormat($disc);
      $sales['sub_total'] = $u->priceFormat($subtotal);
      $sales['total']    = $u->priceFormat($subtotal - $disc);
      $barcode = [
        'barcodeType'   => $u->barcodeType(),
        'barcodeFormat' => $u->barcodeFormat(),
      ];
      $prodList = ProductService::where(DatabaseConstants::TABLE_CREATOR, $creatorId)->get();
      Log::info('POS printView: view data ready', [
        'method'         => __METHOD__,
        'cart_count'     => count($cart),
        'products_count' => $prodList->count(),
      ]);
      return response()->view(ViewsConstants::POS . '.printview', [
        'details'         => $details,
        'sales'           => $sales,
        'customer'        => $customer,
        'productServices' => $prodList,
        'barcode'         => $barcode,
      ]);
    } catch (Throwable $e) {
      return self::handleException($req, $e);
    }
  }

  public function invoicePosNumber(Request $request): int|RedirectResponse|null
  {
    if (($r = self::_checkLogin()) instanceof RedirectResponse) return $r;
    $user = $r;
    if ($deny = self::_authorize($request, PermissionsConstants::MNG_POS)) return $deny;
    Log::info(__METHOD__ . ' calculating next POS number', [UsersConstants::COL_USER_ID => $user?->id]);
    try {
      $latest = Pos::where(DatabaseConstants::TABLE_CREATOR, $user?->creatorId())->latest()->first();
      // ! ALERT THIS WILL CRASH
      $next = $latest ? $latest->pos_id + 1 : 1;
      Log::info(__METHOD__ . ' next POS number', ['next' => $next]);
      return $next;
    } catch (Throwable $e) {
      Log::error(__METHOD__ . ' failed', ['error' => $e->getMessage()]);
      return defaultUndefinedException($request, $e, __CLASS__ . '::' . __FUNCTION__);
    }
  }

  public function receipt(Request $request): View|RedirectResponse|null
  {
    if (($r = self::_checkLogin()) instanceof RedirectResponse) return $r;
    $user = $r;
    if ($deny = self::_authorize($request, PermissionsConstants::MNG_POS)) return $deny;
    Log::info(__METHOD__ . ' start', [UsersConstants::COL_USER_ID => $user?->id]);
    try {
      $ids = (array) $request->input('product_id', []);
      if (empty($ids)) return redirect()->back()->with('error', __('Product is required.'));
      $productServices = ProductService::whereIn('id', $ids)->get();
      $quantity = (int) $request->input('quantity', 1);
      $barcodeType = $user?->barcodeType() ?: 'code128';
      $barcodeFormat = $user?->barcodeFormat() ?: 'css';
      $barcode = ['barcodeType' => $barcodeType, 'barcodeFormat' => $barcodeFormat];
      Log::info(__METHOD__ . ' prepared data', ['count' => $productServices->count()]);
      return view(ViewsConstants::POS . '.receipt', compact('productServices', 'barcode', 'quantity'));
    } catch (Throwable $e) {
      Log::error(__METHOD__ . ' failed', ['error' => $e->getMessage()]);
      return defaultUndefinedException($request, $e, __CLASS__ . '::' . __FUNCTION__);
    }
  }

  public function pos(Request $request, string $encId): View|RedirectResponse|null
  {
    if (($r = self::_checkLogin()) instanceof RedirectResponse) return $r;
    $user = $r;
    if ($deny = self::_authorize($request, PermissionsConstants::MNG_POS)) return $deny;
    Log::info(__METHOD__ . ' start', ['encId' => $encId, UsersConstants::COL_USER_ID => $user?->id]);
    try {
      $id = Crypt::decrypt($encId);
      $pos = Pos::with('items.product', 'customer')->findOrFail($id);
      if ($pos[DatabaseConstants::TABLE_CREATOR] !== $user?->creatorId()) {
        return defaultPermissionDenial($request, new \Exception('permission denied'), __CLASS__ . '::' . __FUNCTION__);
      }
      $posPayment = PosPayment::where('pos_id', $pos->id)->first();
      $settings = Utility::settingsById($pos[DatabaseConstants::TABLE_CREATOR]);
      $customer = $pos->customer;
      $totalTaxPrice = $totalQuantity = $totalRate = $totalDiscount = 0;
      $taxesData = [];
      $items = [];
      foreach ($pos->items as $it) {
        $name = $it->product?->name ?? '';
        $qty = $it->quantity;
        $price = $it->price;
        $discount = $it->discount;
        $taxRate = $it->tax;
        $totalQuantity += $qty;
        $totalRate += $price;
        $totalDiscount += $discount;
        $itemTaxes = [];
        if ($taxRate) {
          foreach (Utility::tax($taxRate) as $tax) {
            $taxPrice = Utility::taxRate($tax->rate, $price, $qty, $discount);
            $totalTaxPrice += $taxPrice;
            $itemTax = [
              'name'  => $tax->name,
              'rate'  => $tax->rate . '%',
              'price' => Utility::priceFormat($settings, $taxPrice),
            ];
            $itemTaxes[] = $itemTax;
            $taxesData[$tax->name] = ($taxesData[$tax->name] ?? 0) + $taxPrice;
          }
        }
        $items[] = (object) [
          'name'        => $name,
          'quantity'    => $qty,
          'tax'         => $taxRate,
          'discount'    => $discount,
          'price'       => $price,
          'description' => $it->description,
          'itemTax'     => $itemTaxes,
        ];
      }
      $pos->itemData = $items;
      $pos->totalTaxPrice = $totalTaxPrice;
      $pos->totalQuantity = $totalQuantity;
      $pos->totalRate = $totalRate;
      $pos->totalDiscount = $totalDiscount;
      $pos->taxesData = $taxesData;
      $logoPath = asset(Storage::url('uploads/logo/'));
      $companyLogo = Utility::getValByName(SettingsConstants::CPN_LG_DK);
      $posLogo = $settings['pos_logo'] ?? '';
      $img = $posLogo
        ? Utility::getFile('pos_logo/') . $posLogo
        : asset($logoPath . '/' . ($companyLogo ?: SettingsConstants::CPN_LG_DK_DEF));
      $color = '#' . ($settings['pos_color'] ?? 'ffffff');
      $fontColor = Utility::getFontColor($color);
      Log::info(__METHOD__ . ' succeeded', ['pos_id' => $pos->id]);
      return view(ViewsConstants::POS_TMP . ($settings[BillsConstants::COL_POS_TMP] ?? 'default'), compact(
        'pos',
        'posPayment',
        'color',
        'settings',
        'customer',
        'img',
        'fontColor'
      ));
    } catch (Throwable $e) {
      Log::error(__METHOD__ . ' failed', ['error' => $e->getMessage()]);
      return defaultUndefinedException($request, $e, __CLASS__ . '::' . __FUNCTION__);
    }
  }

  private function _composeDetails(array &$d, array $s): void
  {
    Log::info('_composeDetails start', [
      'warehouse' => $d['warehouse']['name'] ?? null,
      'customer'  => $d['customer']['name']  ?? null,
      'method'    => __METHOD__,
    ]);
    // warehouse details
    $ws = '<h7 class="text-dark">' . ucfirst($d['warehouse']['name']) . '</h7>';
    $d['warehouse']['details'] = $ws;

    if ($d['customer']) {
      $this->_addrCompose($d['customer'], 'billing');
      $this->_addrCompose($d['customer'], 'shipping');
      $cust = $d['customer'];
      $d['customer']['details'] =
        '<h6 class="text-dark">' . ucfirst($cust['name']) .
        '<p class="m-0 h6 font-weight-normal">' . $cust['billing_phone'] . '</p>' .
        '<p class="m-0 h6 font-weight-normal">' . $cust['billing_address'] . '</p>' .
        '<p class="m-0 h6 font-weight-normal">' . $cust['billing_city'] . $cust['billing_state'] . '</p>' .
        '<p class="m-0 h6 font-weight-normal">' . $cust['billing_country'] . '</p>' .
        '<p class="m-0 h6 font-weight-normal">' . $cust['billing_zip'] . '</p></h6>';
      $d['customer']['shippdetails'] =
        '<h6 class="text-dark"><b>' . ucfirst($cust['name']) .
        '</b><p class="m-0 h6 font-weight-normal">' . $cust['shipping_phone'] . '</p>' .
        '<p class="m-0 h6 font-weight-normal">' . $cust['shipping_address'] . '</p>' .
        '<p class="m-0 h6 font-weight-normal">' . $cust['shipping_city'] . $cust['shipping_state'] . '</p>' .
        '<p class="m-0 h6 font-weight-normal">' . $cust['shipping_country'] . '</p>' .
        '<p class="m-0 h6 font-weight-normal">' . $cust['shipping_zip'] . '</p></h6>';
    } else {
      $d['customer']['details'] = '<h2 class="h6"><b>Walk-in Customer</b><h2>';
      $d['customer']['shippdetails'] = '-';
    }

    // company details fallbacks
    $s['company_telephone'] = $s['company_telephone'] ? ', ' . $s['company_telephone'] : '';
    $s['company_state']    = $s['company_state']     ? ', ' . $s['company_state']     : '';

    // user (company) details
    $usr = $d['user'];
    $d['user']['details'] =
      '<h6 class="text-dark"><b>' . ucfirst($usr['name']) . '</b>' .
      '<h2 class="font-weight-normal">' .
      '<p class="m-0 font-weight-normal">' . $s['company_name'] . $s['company_telephone'] . '</p>' .
      '<p class="m-0 font-weight-normal">' . $s['company_address'] . '</p>' .
      '<p class="m-0 h6 font-weight-normal">' . $s['company_city'] . $s['company_state'] . '</p>' .
      '<p class="m-0 font-weight-normal">' . $s['company_country'] . '</p>' .
      '<p class="m-0 font-weight-normal">' . $s['company_zipcode'] . '</p></h2>';
    Log::info('_composeDetails complete', ['method' => __METHOD__]);
  }

  private function _addrCompose(array &$a, string $type): void
  {
    Log::info('_addrCompose', [
      'type'   => $type,
      'state'  => $a["{$type}_state"] ?? null,
      'method' => __METHOD__,
    ]);
    $k = "{$type}_state";
    $a[$k] = $a[$k] ? ', ' . $a[$k] : '';
  }

  private function _summarizeCart(array $cart): array|RedirectResponse
  {
    Log::info('_summarizeCart start', [
      'count'  => count($cart),
      'method' => __METHOD__,
    ]);
    $userOrRedirect = self::_checkLogin();
    if ($userOrRedirect instanceof RedirectResponse) {
      Log::warning('_summarizeCart unauthenticated', ['method' => __METHOD__]);
      return $userOrRedirect;
    }
    $user = $userOrRedirect;
    $sum = 0;
    $out = ['data' => []];

    foreach ($cart as $k => $v) {
      $sub = $v['price'] * $v['quantity'];
      $tax = ($sub * $v['tax']) / 100;
      $out['data'][$k] = [
        'name'        => $v['name'],
        'quantity'    => $v['quantity'],
        'price'       => $user?->priceFormat($v['price']),
        'tax'         => $v['tax'] . '%',
        'product_tax' => $v['product_tax'],
        'tax_amount'  => $user?->priceFormat($tax),
        'subtotal'    => $user?->priceFormat($v['subtotal']),
      ];
      $sum += $v['subtotal'];
    }

    Log::info('_summarizeCart complete', [
      'sum'    => $sum,
      'method' => __METHOD__,
    ]);
    return [$out, $sum];
  }

  private function _template(int $uid): string
  {
    $template = Utility::settingsById($uid)[BillsConstants::COL_POS_TMP] ?? 'template1';
    Log::info('_template resolved', [
      UsersConstants::COL_USER_ID  => $uid,
      'template' => $template,
      'method'   => __METHOD__,
    ]);
    return $template;
  }

  private function _templateData(Pos $pos): array
  {
    Log::info('_templateData start', [
      'pos_id' => $pos->id,
      'method' => __METHOD__,
    ]);
    $settings = Utility::settingsById($pos[DatabaseConstants::TABLE_CREATOR]);
    $logoPath = Utility::getFile('pos_logo/') . ($settings['pos_logo'] ?? '');

    $data = [
      'pos'        => $pos,
      'posPayment' => PosPayment::where('pos_id', $pos->id)->first(),
      'color'      => '#' . $settings['pos_color'],
      'settings'   => $settings,
      'customer'   => $pos->customer,
      'img'        => $logoPath
        ?: asset(Storage::url('uploads/logo/' . (Utility::getValByName(SettingsConstants::CPN_LG_DK) ?: SettingsConstants::CPN_LG_DK_DEF))),
      'font_color' => Utility::getFontColor('#' . $settings['pos_color']),
    ];

    Log::info('_templateData complete', [
      'pos_id' => $pos->id,
      'method' => __METHOD__,
    ]);
    return $data;
  }

  private function _previewData(string $tpl, string $col): array|RedirectResponse
  {
    Log::info('_previewData start', [
      'template' => $tpl,
      'color'    => $col,
      'method'   => __METHOD__,
    ]);
    $userOrRedirect = self::_checkLogin();
    if ($userOrRedirect instanceof RedirectResponse) {
      Log::warning('_previewData unauthenticated', ['method' => __METHOD__]);
      return $userOrRedirect;
    }

    $settings = Utility::settings();
    $pos = new Pos([
      'pos_id'         => 1,
      'issue_date'     => now(),
      'itemData'       => [],
      'totalTaxPrice'  => 0,
      'totalQuantity'  => 0,
      'totalRate'      => 0,
      'totalDiscount'  => 0,
      'taxesData'      => [],
      DatabaseConstants::TABLE_CREATOR     => $userOrRedirect->creatorId(),
    ]);

    $view = [
      'pos'        => $pos,
      'preview'    => 1,
      'color'      => '#' . $col,
      'img'        => asset(Storage::url('uploads/logo/' . (Utility::getValByName(SettingsConstants::CPN_LG_DK) ?: SettingsConstants::CPN_LG_DK_DEF))),
      'settings'   => $settings,
      'customer'   => new \stdClass(),
      'font_color' => Utility::getFontColor('#' . $col),
      'posPayment' => new PosPayment(['amount' => 360, 'discount' => 100]),
    ];

    Log::info('_previewData complete', [
      'template' => $tpl,
      'method'   => __METHOD__,
    ]);
    return $view;
  }

  private static function _authorize(Request $req, string $perm): RedirectResponse|JsonResponse|null
  {
    if (!$req->user()->can($perm)) {
      Log::warning('Permission denied', [
        UsersConstants::COL_USER_ID    => $req->user()->id,
        'permission' => $perm,
        'method'     => __METHOD__,
      ]);
      return defaultPermissionDenial(
        $req,
        new \Exception('permission denied'),
        __CLASS__ . '::_authorize'
      );
    }
    Log::info('Permission granted', [
      UsersConstants::COL_USER_ID    => $req->user()->id,
      'permission' => $perm,
      'method'     => __METHOD__,
    ]);
    return null;
  }

  private static function _validate(
    array $d,
    array $r
  ): RedirectResponse|JsonResponse|null {
    $v = Validator::make($d, $r);
    return $v->fails()
      ? redirect()->back()->with('error', $v->getMessageBag()->first())
      : null;
  }

  private static function validateData(array $data, array $rules): ?RedirectResponse
  {
    $v = Validator::make($data, $rules);
    if ($v->fails()) {
      $msg = $v->getMessageBag()->first();
      Log::warning('Validation failed', [
        'errors' => $v->errors()->all(),
        'method' => __METHOD__,
      ]);
      return redirect()->back()->with('error', $msg);
    }
    Log::info('Validation passed', ['method' => __METHOD__]);
    return null;
  }

  private static function handleException(Request $req, Throwable $e): RedirectResponse|JsonResponse|null
  {
    Log::error('Unhandled exception', [
      'error'  => $e->getMessage(),
      'method' => __METHOD__,
    ]);
    return defaultUndefinedException($req, $e, __CLASS__ . '::handleException');
  }

  private static function invoiceNumber(): int
  {
    $userOrRedirect = self::_checkLogin();
    if ($userOrRedirect instanceof RedirectResponse) {
      Log::error('invoiceNumber: unauthenticated', ['method' => __METHOD__]);
      throw new \Exception('unauthenticated');
    }
    $uid   = $userOrRedirect->creatorId();
    $latest = Pos::where(DatabaseConstants::TABLE_CREATOR, $uid)->latest()->first();
    $next  = $latest ? $latest->pos_id + 1 : 1;
    Log::info('Next POS invoice number', [
      UsersConstants::COL_USER_ID => $userOrRedirect->id,
      'next'    => $next,
      'method'  => __METHOD__,
    ]);
    return $next;
  }
}

// ! ALERT getProduct joins by product_id == warehouse_id; confirm logic to avoid empty/incorrect datasets.