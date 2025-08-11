<?php

namespace App\Http\Controllers;

use App\Config\Constants\{
    BillsConstants,
    DatabaseConstants,
    SettingsConstants,
    UsersConstants,
    ViewsConstants,
};
use App\Models\{
    BankAccount,
    Bill,
    CustomField,
    ProductService,
    ProductServiceCategory,
    Purchase,
    PurchasePayment,
    PurchaseProduct,
    StockReport,
    Transaction,
    User,
    Utility,
    Vendor,
    Warehouse,
    WarehouseProduct,
    WarehouseTransfer
};
use App\Traits\{
    ChecksLogin,
    ChecksPermissions
};
use Google\Service\FirebaseAppHosting\Redirect;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\{
    RedirectResponse,
    Request
};
use Illuminate\Support\Facades\{
    Crypt,
    DB,
    Log,
    Storage,
    Validator
};
use Illuminate\View\View;

class PurchaseController extends Controller
{
    private const ROUTE_INDEX = ViewsConstants::PRC . '.index';
    private const ROUTE_SHOW  = ViewsConstants::PRC . '.show';

    use ChecksLogin, ChecksPermissions;

    public function index(Request $request): View|RedirectResponse
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        ) return $userOrRedirect;
        $user = $userOrRedirect;
        if (
            $r = self::guard($request, 'view purchase', self::ROUTE_INDEX)
        ) return $r;
        Log::info(__METHOD__ . ' invoked', [UsersConstants::COL_USER_ID => $user?->id]);
        try {
            Log::debug('Fetching vendors and purchases list', [
                'creator_id' => $user?->creatorId()
            ]);
            $vendors = Vendor::where(
                DatabaseConstants::TABLE_CREATOR,
                $user?->creatorId()
            )->pluck(UsersConstants::COL_NM, 'id')->prepend(
                'Select Vendor',
                ''
            );
            $status   = Purchase::$statuses;
            $purchases = Purchase::where(
                DatabaseConstants::TABLE_CREATOR,
                $user?->creatorId()
            )->with(['vendor', 'category'])->get();
            Log::info('Displaying purchase index', [
                'count'      => $purchases->count(),
                'vendor_opt' => $vendors->count()
            ]);
            return view(
                ViewsConstants::PRC . '.' . __FUNCTION__,
                compact('purchases', 'status', 'vendors')
            );
        } catch (\Throwable $e) {
            Log::error(__METHOD__ . ' error', ['exception' => $e]);
            return defaultUndefinedException(
                $request,
                $e,
                __CLASS__ . '::' . __FUNCTION__,
                route(self::ROUTE_INDEX)
            );
        }
    }

    public function create(
        Request $request,
        string|int $vendorId
    ): View|RedirectResponse {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        ) return $userOrRedirect;
        $user = $userOrRedirect;
        if (
            $r = self::guard(
                $request,
                'create purchase',
                self::ROUTE_INDEX
            )
        ) return $r;
        Log::info(__METHOD__ . ' invoked', [
            UsersConstants::COL_USER_ID   => $user?->id,
            'vendor_id' => $vendorId
        ]);
        try {
            Log::debug('Loading form data for create', [
                'creator_id' => $user?->creatorId()
            ]);
            $customFields = CustomField::where(
                DatabaseConstants::TABLE_CREATOR,
                $user?->creatorId()
            )->where('module', 'purchase')->get();
            $category = ProductServiceCategory::where(
                DatabaseConstants::TABLE_CREATOR,
                $user?->creatorId()
            )->where('type', 'expense')->pluck(
                'name',
                'id'
            )->prepend('Select Category', '');
            $purchaseNumber = $user?->purchaseNumberFormat(
                $this->purchaseNumber()
            );
            $vendors = Vendor::where(
                DatabaseConstants::TABLE_CREATOR,
                $user?->creatorId()
            )->pluck(UsersConstants::COL_NM, 'id')->prepend('Select Vendor', '');
            $warehouse = Warehouse::where(
                DatabaseConstants::TABLE_CREATOR,
                $user?->creatorId()
            )->pluck('name', 'id')->prepend(
                'Select Warehouse',
                ''
            );
            $productServices = ProductService::where(
                DatabaseConstants::TABLE_CREATOR,
                $user?->creatorId()
            )->where('type', '!=', 'service')->pluck(
                'name',
                'id'
            )->prepend('--', '');
            Log::info('Form data loaded for create', [
                'custom_fields'   => $customFields->count(),
                'product_services' => $productServices->count()
            ]);
            return view(
                ViewsConstants::PRC . '.' . __FUNCTION__,
                compact(
                    'vendors',
                    'purchaseNumber',
                    'productServices',
                    'category',
                    'customFields',
                    'vendorId',
                    'warehouse'
                )
            );
        } catch (\Throwable $e) {
            Log::error(__METHOD__ . ' error', ['exception' => $e]);
            return defaultUndefinedException(
                $request,
                $e,
                __CLASS__ . '::' . __FUNCTION__,
                route(self::ROUTE_INDEX)
            );
        }
    }

    public function store(Request $request): RedirectResponse
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        ) return $userOrRedirect;
        $user = $userOrRedirect;
        if (
            $r = self::guard(
                $request,
                'create purchase',
                self::ROUTE_INDEX
            )
        ) return $r;
        Log::info(__METHOD__ . ' invoked', [UsersConstants::COL_USER_ID => $user?->id]);
        try {
            Log::info('Starting DB transaction for store', [
                UsersConstants::COL_USER_ID => $user?->id
            ]);
            $purchase = DB::transaction(function () use ($request, $user) {
                $validator = Validator::make($request->all(), [
                    'vendor_id'     => 'required',
                    'warehouse_id'  => 'required',
                    'purchase_date' => 'required',
                    'category_id'   => 'required',
                    'items'         => 'required',
                ]);
                if ($validator->fails()) {
                    Log::warning(__METHOD__ . ' validation failed', [
                        'errors' => $validator->errors()->all()
                    ]);
                    throw new \InvalidArgumentException(
                        $validator->errors()->first()
                    );
                }
                $purchase = new Purchase();
                $purchase->purchase_id    = $this->purchaseNumber();
                $purchase->vendor_id      = $request->vendor_id;
                $purchase->warehouse_id   = $request->warehouse_id;
                $purchase->purchase_date  = $request->purchase_date;
                $purchase->purchase_number = $request->purchase_number ?? 0;
                $purchase->status         = 0;
                $purchase->category_id    = $request->category_id;
                $purchase[DatabaseConstants::TABLE_CREATOR]     = $user?->creatorId();
                $purchase->save();
                Log::info('Purchase record created', [
                    'purchase_id' => $purchase->id
                ]);
                foreach ($request->items as $item) {
                    $pp = new PurchaseProduct();
                    $pp->purchase_id = $purchase->id;
                    $pp->product_id = $item['item'];
                    $pp->quantity   = $item['quantity'];
                    $pp->tax        = $item['tax'];
                    $pp->discount   = $item['discount'];
                    $pp->price      = $item['price'];
                    $pp->description = $item['description'];
                    $pp->save();
                    Log::debug('PurchaseProduct saved', [
                        'pp_id'      => $pp->id,
                        'product_id' => $pp->product_id,
                        'quantity'   => $pp->quantity
                    ]);
                    Utility::totalQuantity(
                        'plus',
                        $pp->quantity,
                        $pp->product_id
                    );
                    $desc = $item['quantity']
                        . '  quantity add in purchase '
                        . $user?->purchaseNumberFormat(
                            $purchase->purchase_id
                        );
                    Utility::addProductStock(
                        $item['item'],
                        $item['quantity'],
                        'purchase',
                        $desc,
                        $purchase->id
                    );
                    Utility::addWarehouseStock(
                        $item['item'],
                        $item['quantity'],
                        $request->warehouse_id
                    );
                }
                return $purchase;
            });
            Log::info('DB transaction committed for store', [
                'purchase_id' => $purchase->id,
                UsersConstants::COL_USER_ID     => $user?->id
            ]);
            return redirect()->route(
                self::ROUTE_SHOW,
                ['purchase' => $purchase->id]
            )->with(
                'success',
                __('Purchase successfully created.')
            );
        } catch (\Throwable $e) {
            Log::error(__METHOD__ . ' failed', ['exception' => $e]);
            return defaultUndefinedException(
                $request,
                $e,
                __CLASS__ . '::' . __FUNCTION__,
                route(self::ROUTE_INDEX)
            );
        }
    }

    public function show(
        Request $request,
        string $ids
    ): View|RedirectResponse {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        ) return $userOrRedirect;
        $user = $userOrRedirect;
        if (
            $r = self::guard(
                $request,
                'show purchase',
                self::ROUTE_SHOW
            )
        ) return $r;
        Log::info(__METHOD__ . ' invoked', [UsersConstants::COL_USER_ID => $user?->id]);
        try {
            $id = Crypt::decrypt($ids);
            Log::debug('Decrypted purchase id', ['id' => $id]);
            $purchase = Purchase::findOrFail($id);
            if ($purchase[DatabaseConstants::TABLE_CREATOR] !== $user?->creatorId()) {
                throw new AuthorizationException();
            }
            $purchasePayment = PurchasePayment::where(
                'purchase_id',
                $id
            )->first();
            $vendor = $purchase->vendor;
            $items = $purchase->items;
            Log::info('Purchase loaded for viewing', [
                'purchase_id' => $id,
                'items_count' => count($items)
            ]);
            return view(
                ViewsConstants::PRC . '.' . __FUNCTION__,
                compact(
                    'purchase',
                    'vendor',
                    'items',
                    'purchasePayment'
                )
            );
        } catch (AuthorizationException $e) {
            Log::warning(__METHOD__ . ' authorization failed', [
                UsersConstants::COL_USER_ID => $user?->id
            ]);
            return defaultPermissionDenial(
                $request,
                $e,
                __CLASS__ . '::' . __FUNCTION__,
                route(self::ROUTE_INDEX)
            );
        } catch (\Throwable $e) {
            Log::error(__METHOD__ . ' error', ['exception' => $e]);
            return defaultUndefinedException(
                $request,
                $e,
                __CLASS__ . '::' . __FUNCTION__,
                route(self::ROUTE_INDEX)
            );
        }
    }

    public function edit(
        Request $request,
        string $ids
    ): View|RedirectResponse {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        ) return $userOrRedirect;
        $user = $userOrRedirect;
        if (
            $r = self::guard(
                $request,
                'edit purchase',
                ViewsConstants::PRC . '.' . __FUNCTION__
            )
        ) return $r;
        Log::info(__METHOD__ . ' invoked', [UsersConstants::COL_USER_ID => $user?->id]);
        try {
            $id = Crypt::decrypt($ids);
            $purchase = Purchase::findOrFail($id);
            if ($purchase[DatabaseConstants::TABLE_CREATOR] !== $user?->creatorId()) {
                throw new AuthorizationException();
            }
            $category = ProductServiceCategory::where(
                DatabaseConstants::TABLE_CREATOR,
                $user?->creatorId()
            )->where('type', 'expense')->pluck(
                'name',
                'id'
            )->prepend('Select Category', '');
            $vendors = Vendor::where(
                DatabaseConstants::TABLE_CREATOR,
                $user?->creatorId()
            )->pluck('name', 'id')->prepend('Select Vendor', '');
            $warehouse = Warehouse::where(
                DatabaseConstants::TABLE_CREATOR,
                $user?->creatorId()
            )->pluck('name', 'id')->prepend(
                'Select Warehouse',
                ''
            );
            $productServices = ProductService::where(
                DatabaseConstants::TABLE_CREATOR,
                $user?->creatorId()
            )->where('type', '!=', 'service')->pluck(
                'name',
                'id'
            )->prepend('--', '');
            $purchaseNumber = $user?->purchaseNumberFormat(
                $purchase->purchase_id
            );
            Log::info('Loaded data for edit', [
                'purchase_id' => $id
            ]);
            return view(
                ViewsConstants::PRC . '.' . __FUNCTION__,
                compact(
                    'purchase',
                    'vendors',
                    'productServices',
                    'warehouse',
                    'category',
                    'purchaseNumber'
                )
            );
        } catch (AuthorizationException $e) {
            Log::warning(__METHOD__ . ' authorization failed', [
                UsersConstants::COL_USER_ID => $user?->id
            ]);
            return defaultPermissionDenial(
                $request,
                $e,
                __CLASS__ . '::' . __FUNCTION__,
                route(self::ROUTE_INDEX)
            );
        } catch (\Throwable $e) {
            Log::error(__METHOD__ . ' error', ['exception' => $e]);
            return defaultUndefinedException(
                $request,
                $e,
                __CLASS__ . '::' . __FUNCTION__,
                route(self::ROUTE_INDEX)
            );
        }
    }

    public function update(
        Request $request,
        Purchase $purchase
    ): RedirectResponse {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        ) return $userOrRedirect;
        $user = $userOrRedirect;
        if (
            $r = self::guard(
                $request,
                'edit purchase',
                self::ROUTE_INDEX
            )
        ) return $r;
        Log::info(__METHOD__ . ' invoked', [UsersConstants::COL_USER_ID => $user?->id]);
        try {
            if ($purchase[DatabaseConstants::TABLE_CREATOR] !== $user?->creatorId()) {
                throw new AuthorizationException();
            }
            $validator = Validator::make($request->all(), [
                'vendor_id'     => 'required',
                'purchase_date' => 'required',
                'items'         => 'required',
            ]);
            if ($validator->fails()) {
                Log::warning(__METHOD__ . ' validation failed', [
                    'errors' => $validator->errors()->all()
                ]);
                return redirect()->route(self::ROUTE_INDEX)->with(
                    'error',
                    $validator->errors()->first()
                );
            }
            Log::info('Starting DB transaction for update', [
                'purchase_id' => $purchase->id
            ]);
            DB::transaction(function () use ($request, $purchase, $user) {
                $purchase->update([
                    'vendor_id'     => $request->vendor_id,
                    'purchase_date' => $request->purchase_date,
                    'category_id'   => $request->category_id,
                ]);
                Log::debug('Purchase record updated', [
                    'purchase_id' => $purchase->id
                ]);
                StockReport::where(
                    'type',
                    'purchase'
                )->where(
                    'type_id',
                    $purchase->id
                )->delete();
                foreach ($request->items as $item) {
                    $pp = PurchaseProduct::find($item['id']) ??
                        new PurchaseProduct(['purchase_id' => $purchase->id]);
                    $oldQty = $pp->exists ? $pp->quantity : 0;
                    if ($pp->exists) {
                        Utility::totalQuantity(
                            'minus',
                            $oldQty,
                            $pp->product_id
                        );
                    }
                    $pp->product_id = $item['item'] ?? $pp->product_id;
                    $pp->quantity   = $item['quantity'];
                    $pp->tax        = $item['tax'];
                    $pp->discount   = $item['discount'];
                    $pp->price      = $item['price'];
                    $pp->description = $item['description'];
                    $pp->save();
                    Log::debug('PurchaseProduct updated', [
                        'pp_id'    => $pp->id,
                        'quantity' => $pp->quantity
                    ]);
                    Utility::totalQuantity(
                        'plus',
                        $pp->quantity,
                        $pp->product_id
                    );
                    $desc = $pp->quantity
                        . '  quantity add in purchase '
                        . $user?->purchaseNumberFormat(
                            $purchase->purchase_id
                        );
                    Utility::addProductStock(
                        $pp->product_id,
                        $pp->quantity,
                        'purchase',
                        $desc,
                        $purchase->id
                    );
                    $diff = $pp->quantity - $oldQty;
                    Utility::addWarehouseStock(
                        $pp->product_id,
                        $diff,
                        $request->warehouse_id
                    );
                }
            });
            Log::info('DB transaction committed for update', [
                'purchase_id' => $purchase->id
            ]);
            return redirect()->route(self::ROUTE_INDEX)->with(
                'success',
                __('Purchase successfully updated.')
            );
        } catch (AuthorizationException $e) {
            Log::warning(__METHOD__ . ' authorization failed', [
                UsersConstants::COL_USER_ID => $user?->id
            ]);
            return defaultPermissionDenial(
                $request,
                $e,
                __CLASS__ . '::' . __FUNCTION__,
                route(self::ROUTE_INDEX)
            );
        } catch (\Throwable $e) {
            Log::error(__METHOD__ . ' failed', ['exception' => $e]);
            return defaultUndefinedException(
                $request,
                $e,
                __CLASS__ . '::' . __FUNCTION__,
                route(self::ROUTE_INDEX)
            );
        }
    }

    public function destroy(
        Request $request,
        Purchase $purchase
    ): RedirectResponse {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        ) return $userOrRedirect;
        $user = $userOrRedirect;
        if (
            $r = self::guard(
                $request,
                'delete purchase',
                self::ROUTE_INDEX
            )
        ) return $r;
        Log::info(__METHOD__ . ' invoked', [
            UsersConstants::COL_USER_ID     => $user?->id,
            'purchase_id' => $purchase->id
        ]);
        try {
            Log::info('Starting DB transaction for destroy', [
                'purchase_id' => $purchase->id
            ]);
            DB::transaction(function () use ($purchase) {
                foreach ($purchase->payments as $pay) {
                    Log::debug('Deleting payment', ['pay_id' => $pay->id]);
                    $pay->delete();
                }
                foreach ($purchase->items as $item) {
                    foreach (
                        WarehouseTransfer::where(
                            'product_id',
                            $item->product_id
                        )->where(
                            'from_warehouse',
                            $purchase->warehouse_id
                        )->get() as $t
                    ) {
                        Log::debug('Reversing warehouse transfer', [
                            'transfer_id' => $t->id
                        ]);
                        $to = WarehouseProduct::where(
                            'warehouse_id',
                            $t->to_warehouse
                        )->first();
                        if ($to) {
                            $to->decrement('quantity', $t->quantity);
                            if ($to->quantity <= 0) {
                                $to->delete();
                            }
                        }
                    }
                    WarehouseProduct::where(
                        'warehouse_id',
                        $purchase->warehouse_id
                    )->where(
                        'product_id',
                        $item->product_id
                    )->decrement('quantity', $item->quantity);
                    WarehouseProduct::where(
                        'warehouse_id',
                        $purchase->warehouse_id
                    )->where(
                        'product_id',
                        $item->product_id
                    )->where('quantity', '<=', 0)->delete();
                    ProductService::where(
                        'id',
                        $item->product_id
                    )->decrement('quantity', $item->quantity);
                    Log::debug('Deleting purchase item', [
                        'item_id'     => $item->id,
                        'product_id'  => $item->product_id
                    ]);
                    $item->delete();
                }
                $purchase->delete();
                Log::info('Purchase record deleted', [
                    'purchase_id' => $purchase->id
                ]);
            });
            Log::info('DB transaction committed for destroy', [
                'purchase_id' => $purchase->id
            ]);
            return redirect()->route(self::ROUTE_INDEX)->with(
                'success',
                __('Purchase successfully deleted.')
            );
        } catch (\Throwable $e) {
            Log::error(__METHOD__ . ' failed', ['exception' => $e]);
            return defaultUndefinedException(
                $request,
                $e,
                __CLASS__ . '::' . __FUNCTION__,
                route(self::ROUTE_INDEX)
            );
        }
    }

    public function sent(Request $request, int $id): RedirectResponse
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        ) return $userOrRedirect;
        $user = $userOrRedirect;
        if (
            $r = self::guard(
                $request,
                'send purchase',
                self::ROUTE_INDEX
            )
        ) return $r;
        Log::info(__METHOD__ . ' invoked', [
            UsersConstants::COL_USER_ID     => $user?->id,
            'purchase_id' => $id
        ]);
        try {
            $purchase = Purchase::findOrFail($id);
            $purchase->send_date = now()->toDateString();
            $purchase->status   = 1;
            $purchase->save();
            Log::debug('Purchase marked as sent', [
                'purchase_id' => $purchase->id,
                'status'      => $purchase->status
            ]);
            $vendor = Vendor::find($purchase->vendor_id);
            $name  = $vendor->name ?? '';
            $purchase->name    = $name;
            $purchase->purchase = $user?->purchaseNumberFormat(
                $purchase->purchase_id
            );
            $purchaseId        = Crypt::encrypt($purchase->id);
            $purchase->url     = route(ViewsConstants::PRC . '.pdf', $purchaseId);
            Utility::userBalance(
                'vendor',
                $vendor->id,
                $purchase->getTotal(),
                'credit'
            );
            Log::info('Vendor balance credited', [
                'vendor_id' => $vendor->id,
                'amount'    => $purchase->getTotal()
            ]);
            $vendorArr = [
                'vendor_bill_name'   => $name,
                'vendor_bill_number' => $purchase->purchase,
                'vendor_bill_url'    => $purchase->url
            ];
            $resp = Utility::sendEmailTemplate(
                'vendor_bill_sent',
                [$vendor->id => $vendor->email],
                $vendorArr
            );
            Log::info('Email template sent', [
                'template'   => 'vendor_bill_sent',
                'is_success' => $resp['is_success'] ?? false
            ]);
            return redirect()->back()->with(
                'success',
                __('Purchase successfully sent.')
                    . (
                        ($resp['is_success'] === false
                            && !empty($resp['error']))
                        ? '<br><span class="text-danger">'
                        . $resp['error'] .
                        '</span>'
                        : ''
                    )
            );
        } catch (AuthorizationException $e) {
            return defaultPermissionDenial(
                $request,
                $e,
                __METHOD__,
                route(self::ROUTE_INDEX)
            );
        } catch (\Throwable $e) {
            Log::error(__METHOD__ . ' failed', ['exception' => $e]);
            return defaultUndefinedException(
                $request,
                $e,
                __METHOD__,
                route(self::ROUTE_INDEX)
            );
        }
    }

    public function resent(Request $request, int $id): RedirectResponse
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        ) return $userOrRedirect;
        $user = $userOrRedirect;
        if (
            $r = self::guard(
                $request,
                'send purchase',
                self::ROUTE_INDEX
            )
        ) return $r;
        Log::info(__METHOD__ . ' invoked', [
            UsersConstants::COL_USER_ID     => $user?->id,
            'purchase_id' => $id
        ]);
        try {
            $purchase = Purchase::findOrFail($id);
            $vendor  = Vendor::find($purchase->vendor_id);
            $name    = $vendor->name ?? '';
            $purchase->name    = $name;
            $purchase->purchase = $user?->purchaseNumberFormat(
                $purchase->purchase_id
            );
            $purchaseId        = Crypt::encrypt($purchase->id);
            $purchase->url     = route(ViewsConstants::PRC . '.pdf', $purchaseId);
            Log::debug('Purchase data prepared for resend', [
                'purchase_id' => $purchase->id
            ]);
            $vendorArr = [
                'vendor_bill_name'   => $name,
                'vendor_bill_number' => $purchase->purchase,
                'vendor_bill_url'    => $purchase->url
            ];
            $resp = Utility::sendEmailTemplate(
                'vendor_bill_sent',
                [$vendor->id => $vendor->email],
                $vendorArr
            );
            Log::info('Email template resent', [
                'template'   => 'vendor_bill_sent',
                'is_success' => $resp['is_success'] ?? false
            ]);
            return redirect()->back()->with(
                'success',
                __('Purchase successfully sent.')
                    . (
                        ($resp['is_success'] === false
                            && !empty($resp['error']))
                        ? '<br><span class="text-danger">'
                        . $resp['error'] .
                        '</span>'
                        : ''
                    )
            );
        } catch (AuthorizationException $e) {
            return defaultPermissionDenial(
                $request,
                $e,
                __METHOD__,
                route(self::ROUTE_INDEX)
            );
        } catch (\Throwable $e) {
            Log::error(__METHOD__ . ' failed', ['exception' => $e]);
            return defaultUndefinedException(
                $request,
                $e,
                __METHOD__,
                route(self::ROUTE_INDEX)
            );
        }
    }

    public function purchase(Request $request, string $purchaseId): View|RedirectResponse
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        ) return $userOrRedirect;
        $user = $userOrRedirect;
        if (
            $r = self::guard(
                $request,
                'show purchase',
                self::ROUTE_INDEX
            )
        ) return $r;
        Log::info(__METHOD__ . ' invoked', [
            UsersConstants::COL_USER_ID     => $user?->id,
            'purchase_id' => $purchaseId
        ]);
        try {
            $id = Crypt::decrypt($purchaseId);
            Log::debug('Decrypted purchase id', ['id' => $id]);
            $purchase = Purchase::findOrFail($id);
            $settings = Utility::settings();
            $rows    = DB::table('settings')
                ->where(DatabaseConstants::TABLE_CREATOR, $purchase[DatabaseConstants::TABLE_CREATOR])
                ->get();
            foreach ($rows as $row) {
                $settings[$row->name] = $row->value;
            }
            Log::debug('Settings loaded', [
                'count'    => count($settings),
                'template' => $settings[BillsConstants::COL_PRC_TMP] ?? null
            ]);
            $vendor       = $purchase->vendor;
            $totalTaxPrice = 0;
            $totalQuantity = 0;
            $totalRate    = 0;
            $totalDiscount = 0;
            $taxesData    = [];
            $items        = [];
            foreach ($purchase->items as $product) {
                $item             = new \stdClass();
                $item->name       = $product->product()?->name ?? '';
                $item->quantity   = $product->quantity;
                $item->tax        = $product->tax;
                $item->discount   = $product->discount;
                $item->price      = $product->price;
                $item->description = $product->description;
                $totalQuantity += $item->quantity;
                $totalRate     += $item->price;
                $totalDiscount += $item->discount;
                $taxes    = Utility::tax($product->tax);
                $itemTaxes = [];
                foreach ($taxes as $tax) {
                    $taxPrice     = Utility::taxRate(
                        $tax->rate,
                        $item->price,
                        $item->quantity,
                        $item->discount
                    );
                    $totalTaxPrice += $taxPrice;
                    $itemTax = [
                        'name'      => $tax->name,
                        'rate'      => $tax->rate . '%',
                        'price'     => Utility::priceFormat($settings, $taxPrice),
                        'tax_price' => $taxPrice
                    ];
                    $itemTaxes[]                       = $itemTax;
                    $taxesData[$tax->name] = ($taxesData[$tax->name] ?? 0)
                        + $taxPrice;
                }
                $item->itemTax = $itemTaxes;
                $items[]      = $item;
            }
            $color     = '#' . $settings['purchase_color'];
            $font_color = Utility::getFontColor($color);
            $logo      = asset(Storage::url('uploads/logo/'));
            $purchase_logo = Utility::getValByName('purchase_logo');
            $img = $purchase_logo
                ? Utility::getFile('purchase_logo/') . $purchase_logo
                : asset($logo . '/' . (
                    $settings[SettingsConstants::CPN_LG_DK]
                    ?? SettingsConstants::CPN_LG_DK_DEF
                ));
            Log::info('Rendering purchase template', [
                'template' => $settings[BillsConstants::COL_PRC_TMP]
            ]);
            return view(
                ViewsConstants::PRC_TMP .
                    $settings[BillsConstants::COL_PRC_TMP],
                compact(
                    'purchase',
                    'color',
                    'settings',
                    'vendor',
                    'img',
                    'font_color'
                )
            );
        } catch (AuthorizationException $e) {
            return defaultPermissionDenial(
                $request,
                $e,
                __METHOD__,
                route(self::ROUTE_INDEX)
            );
        } catch (\Throwable $e) {
            Log::error(__METHOD__ . ' failed', ['exception' => $e]);
            return defaultUndefinedException(
                $request,
                $e,
                __METHOD__,
                route(self::ROUTE_INDEX)
            );
        }
    }

    public const PV_PRC = 'previewPurchase';
    public function previewPurchase(Request $request, string $template, string $color): View|RedirectResponse
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        ) return $userOrRedirect;
        $user = $userOrRedirect;
        Log::info(__METHOD__ . ' invoked for preview', [
            UsersConstants::COL_USER_ID   => $user?->id,
            'template'  => $template,
            'color_hex' => '#' . $color
        ]);
        $settings     = Utility::settings();
        $purchase     = new Purchase();
        $vendor       = (object)[
            'email'            => '<Email>',
            'shipping_name'    => '<Vendor Name>',
            'shipping_country' => '<Country>',
            'shipping_state'   => '<State>',
            'shipping_city'    => '<City>',
            'shipping_phone'   => '<Vendor Phone Number>',
            'shipping_zip'     => '<Zip>',
            'shipping_address' => '<Address>',
            'billing_name'     => '<Vendor Name>',
            'billing_country'  => '<Country>',
            'billing_state'    => '<State>',
            'billing_city'     => '<City>',
            'billing_phone'    => '<Vendor Phone Number>',
            'billing_zip'      => '<Zip>',
            'billing_address'  => '<Address>'
        ];
        $totalTaxPrice = 0;
        $taxesData    = [];
        $items        = [];
        for ($i = 1; $i <= 3; $i++) {
            $item          = new \stdClass();
            $item->name    = 'Item ' . $i;
            $item->quantity = 1;
            $item->tax     = 5;
            $item->discount = 50;
            $item->price   = 100;
            $itemTaxes     = [];
            $taxes         = ['Tax 1', 'Tax 2'];
            foreach ($taxes as $tax) {
                $taxPrice         = 10;
                $totalTaxPrice    += $taxPrice;
                $itemTax = [
                    'name'      => $tax,
                    'rate'      => '10%',
                    'price'     => '$10',
                    'tax_price' => 10
                ];
                $itemTaxes[]     = $itemTax;
                $taxesData[$tax] = ($taxesData[$tax] ?? 0) + $taxPrice;
            }
            $item->itemTax = $itemTaxes;
            $items[]      = $item;
        }
        $purchase->purchase_id   = 1;
        $purchase->issue_date    = now()->toDateTimeString();
        $purchase->itemData      = $items;
        $purchase->totalTaxPrice = $totalTaxPrice;
        $purchase->totalQuantity = 3;
        $purchase->totalRate     = 300;
        $purchase->totalDiscount = 10;
        $purchase->taxesData     = $taxesData;
        $purchase[DatabaseConstants::TABLE_CREATOR]    = $user?->creatorId();
        $logo        = asset(Storage::url('uploads/logo/'));
        $company_logo = Utility::getValByName(SettingsConstants::CPN_LG_DK);
        $settingsData = Utility::settingsById(
            $purchase[DatabaseConstants::TABLE_CREATOR]
        );
        $purchase_logo = $settingsData['purchase_logo'] ?? null;
        $img = $purchase_logo
            ? Utility::getFile('purchase_logo/') . $purchase_logo
            : asset($logo . '/' . (
                $company_logo ?? SettingsConstants::CPN_LG_DK_DEF
            ));
        Log::info('Rendering preview template', [
            'template' => $template
        ]);
        return view(
            ViewsConstants::PRC_TMP . $template,
            compact(
                'purchase',
                'preview',
                'color',
                'img',
                'settings',
                'vendor',
                'font_color'
            )
        );
    }

    public const SV_PCR_TMP_STG = 'savePurchaseTemplateSettings';
    public function savePurchaseTemplateSettings(Request $request): RedirectResponse
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        ) return $userOrRedirect;
        $user = $userOrRedirect;
        Log::info(__METHOD__ . ' invoked', [UsersConstants::COL_USER_ID => $user?->id]);
        try {
            $post = $request->except('_token');
            if (
                isset($post[BillsConstants::COL_PRC_TMP])
                && empty($post['purchase_color'])
            ) {
                $post['purchase_color'] = 'ffffff';
            }
            if ($request->hasFile('purchase_logo')) {
                $dir      = 'purchase_logo/';
                $filename = $user?->id . '_purchase_logo.png';
                $validation = ['mimes:png', 'max:' . SettingsConstants::MAX_U_SIZE_DEF];
                $path     = Utility::uploadFile(
                    $request,
                    'purchase_logo',
                    $filename,
                    $dir,
                    $validation
                );
                if ($path['flag'] == 0) {
                    throw new \InvalidArgumentException(
                        $path['msg']
                    );
                }
                $post['purchase_logo'] = $filename;
            }
            $creatorCol = DatabaseConstants::TABLE_CREATOR;
            foreach ($post as $key => $value)
                DB::insert(
                    'insert into settings (`value`,`name`,`' . $creatorCol . '`) '
                        . 'values(?,?,?) ON DUPLICATE KEY UPDATE `value`=VALUES(`value`)',
                    [$value, $key, $user?->creatorId()]
                );
            Log::info('Purchase template settings saved', [
                'settings' => array_keys($post)
            ]);
            return redirect()->back()->with(
                'success',
                __('Purchase Setting updated successfully')
            );
        } catch (\Throwable $e) {
            Log::error(__METHOD__ . ' failed', ['exception' => $e]);
            return defaultUndefinedException(
                $request,
                $e,
                __METHOD__,
                url()->previous()
            );
        }
    }

    public function items(Request $request): string
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        ) return $userOrRedirect;
        Log::info(__METHOD__ . ' invoked', [
            'purchase_id' => $request->purchase_id,
            'product_id'  => $request->product_id
        ]);
        $item = PurchaseProduct::where([
            'purchase_id' => $request->purchase_id,
            'product_id'  => $request->product_id
        ])->first();
        return $item?->toJson() ?? '{}';
    }

    public const PRC_LK = 'purchaseLink';
    public function purchaseLink(string $encryptedId): View|RedirectResponse
    {
        Log::info(__METHOD__ . ' invoked', [
            'encrypted_id' => $encryptedId
        ]);
        try {
            $id      = Crypt::decrypt($encryptedId);
            $purchase = Purchase::findOrFail($id);
            $user    = User::findOrFail($purchase[DatabaseConstants::TABLE_CREATOR]);
            $purchasePayment = PurchasePayment::where(
                'purchase_id',
                $purchase->id
            )->first();
            $vendor = $purchase->vendor;
            $items = $purchase->items;
            Log::info('Rendering customer purchase link', [
                'purchase_id' => $id
            ]);
            return view(
                ViewsConstants::PRC . '.customer_bill',
                compact(
                    'purchase',
                    'vendor',
                    'items',
                    'purchasePayment',
                    'user'
                )
            );
        } catch (\Throwable $e) {
            Log::error(__METHOD__ . ' failed', ['exception' => $e]);
            return redirect()->back()->with(
                'error',
                __('Permission Denied.')
            );
        }
    }

    public function payment(Request $request, int $purchaseId): View|RedirectResponse
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        ) return $userOrRedirect;
        $user = $userOrRedirect;
        if (
            $r = self::guard(
                $request,
                'create payment purchase',
                self::ROUTE_INDEX
            )
        ) return $r;
        Log::info(__METHOD__ . ' invoked', [
            UsersConstants::COL_USER_ID     => $user?->id,
            'purchase_id' => $purchaseId
        ]);
        try {
            $purchase = Purchase::findOrFail($purchaseId);
            Log::debug('Loaded purchase for payment', [
                'purchase_id' => $purchaseId
            ]);
            $vendors = Vendor::where(
                DatabaseConstants::TABLE_CREATOR,
                $user?->creatorId()
            )->pluck('name', 'id');
            $categories = ProductServiceCategory::where(
                DatabaseConstants::TABLE_CREATOR,
                $user?->creatorId()
            )->pluck('name', 'id');
            $accounts = BankAccount::select('*', DB::raw(
                "CONCAT(bank_name,' ',holder_name) AS name"
            ))->where(
                DatabaseConstants::TABLE_CREATOR,
                $user?->creatorId()
            )->pluck('name', 'id');
            Log::info('Rendering payment form', [
                'vendors_count'    => $vendors->count(),
                'categories_count' => $categories->count(),
                'accounts_count'   => $accounts->count()
            ]);
            return view(
                ViewsConstants::PRC . '.payment',
                compact('vendors', 'categories', 'accounts', 'purchase')
            );
        } catch (\Throwable $e) {
            Log::error(__METHOD__ . ' failed', ['exception' => $e]);
            return defaultUndefinedException(
                $request,
                $e,
                __CLASS__ . '::' . __FUNCTION__,
                route(self::ROUTE_INDEX)
            );
        }
    }

    public function createPayment(Request $request, int $purchaseId): RedirectResponse
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        ) return $userOrRedirect;
        $user = $userOrRedirect;
        if (
            $r = self::guard(
                $request,
                'create payment purchase',
                self::ROUTE_INDEX
            )
        ) return $r;
        Log::info(__METHOD__ . ' invoked', [
            UsersConstants::COL_USER_ID     => $user?->id,
            'purchase_id' => $purchaseId
        ]);
        try {
            Log::info('Starting DB transaction for createPayment', [
                'purchase_id' => $purchaseId
            ]);
            DB::transaction(function () use ($request, $purchaseId, $user) {
                $validator = Validator::make($request->all(), [
                    'date'       => 'required',
                    'amount'     => 'required',
                    'account_id' => 'required'
                ]);
                if ($validator->fails()) {
                    Log::warning(__METHOD__ . ' validation failed', [
                        'errors' => $validator->errors()->all()
                    ]);
                    throw new \InvalidArgumentException(
                        $validator->errors()->first()
                    );
                }
                $purchase = Purchase::findOrFail($purchaseId);
                Log::debug('Found purchase for payment', [
                    'purchase_id' => $purchaseId
                ]);
                $pp = new PurchasePayment();
                $pp->purchase_id  = $purchaseId;
                $pp->date         = $request->date;
                $pp->amount       = $request->amount;
                $pp->account_id   = $request->account_id;
                $pp->payment_method = 0;
                $pp->reference    = $request->reference;
                $pp->description  = $request->description;
                if ($request->hasFile('add_receipt')) {
                    $fileName = time() . '_' . $request
                        ->add_receipt
                        ->getClientOriginalName();
                    $request->add_receipt
                        ->storeAs('uploads/payment', $fileName);
                    $pp->add_receipt = $fileName;
                    Log::debug('Stored payment receipt', [
                        'file' => $fileName
                    ]);
                }
                $pp->save();
                Log::info('Payment record created', [
                    'payment_id' => $pp->id
                ]);
                $due  = $purchase->getDue();
                $total = $purchase->getTotal();
                if ($purchase->status === 0) {
                    $purchase->send_date = now()->toDateString();
                }
                $purchase->status = $due <= 0 ? 4 : 3;
                $purchase->save();
                Log::info('Updated purchase status', [
                    'purchase_id' => $purchaseId,
                    'status'     => $purchase->status
                ]);
                $pp->user_id   = $purchase->vendor_id;
                $pp->user_type = 'Vendor';
                $pp->type      = 'Partial';
                $pp[DatabaseConstants::TABLE_CREATOR] = $user?->id;
                $pp->payment_id = $pp->id;
                $pp->category  = 'Bill';
                $pp->account   = $request->account_id;
                Transaction::addTransaction($pp);
                Log::info('Transaction added', [
                    'payment_id' => $pp->id
                ]);
                Utility::userBalance(
                    'vendor',
                    $purchase->vendor_id,
                    $request->amount,
                    'debit'
                );
                Log::info('Vendor balance debited', [
                    'vendor_id' => $purchase->vendor_id,
                    'amount'   => $request->amount
                ]);
                Utility::bankAccountBalance(
                    $request->account_id,
                    $request->amount,
                    'debit'
                );
                Log::info('Bank account balance debited', [
                    'account_id' => $request->account_id,
                    'amount'    => $request->amount
                ]);
                $settings = Utility::settings();
                if (!empty($settings['new_bill_payment'])) {
                    $vendor = Vendor::findOrFail(
                        $purchase->vendor_id
                    );
                    $billPaymentArr = [
                        'vendor_name'    => $vendor->name,
                        'vendor_email'   => $vendor->email,
                        'payment_name'   => $vendor->name,
                        'payment_amount' => $user?->priceFormat(
                            $request->amount
                        ),
                        'payment_bill'   => 'bill ' .
                            $user?->purchaseNumberFormat(
                                $pp->purchase_id
                            ),
                        'payment_date'   => $user?->dateFormat(
                            $request->date
                        ),
                        'payment_method' => '-',
                        'company_name'   => '-'
                    ];
                    $resp = Utility::sendEmailTemplate(
                        'new_bill_payment',
                        [$vendor->id => $vendor->email],
                        $billPaymentArr
                    );
                    Log::info('Email template new_bill_payment sent', [
                        'is_success' => $resp['is_success'] ?? false
                    ]);
                }
            });
            Log::info('DB transaction committed for createPayment', [
                'purchase_id' => $purchaseId
            ]);
            return redirect()->back()->with(
                'success',
                __('Payment successfully added.')
            );
        } catch (AuthorizationException $e) {
            return defaultPermissionDenial(
                $request,
                $e,
                __CLASS__ . '::' . __FUNCTION__,
                route(self::ROUTE_INDEX)
            );
        } catch (\Throwable $e) {
            Log::error(__METHOD__ . ' failed', ['exception' => $e]);
            return defaultUndefinedException(
                $request,
                $e,
                __CLASS__ . '::' . __FUNCTION__,
                route(self::ROUTE_INDEX)
            );
        }
    }

    public function paymentDestroy(Request $request, int $purchaseId, int $paymentId): RedirectResponse
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        ) return $userOrRedirect;
        $user = $userOrRedirect;
        if (
            $r = self::guard(
                $request,
                'delete payment purchase',
                self::ROUTE_INDEX
            )
        ) return $r;
        Log::info(__METHOD__ . ' invoked', [
            UsersConstants::COL_USER_ID     => $user?->id,
            'purchase_id' => $purchaseId,
            'payment_id'  => $paymentId
        ]);
        try {
            DB::transaction(function () use (
                $purchaseId,
                $paymentId
            ) {
                $pp = PurchasePayment::findOrFail($paymentId);
                $amount   = $pp->amount;
                $accountId = $pp->account_id;
                $pp->delete();
                Log::info('Deleted payment record', [
                    'payment_id' => $paymentId
                ]);
                $purchase = Purchase::findOrFail($purchaseId);
                $due  = $purchase->getDue();
                $total = $purchase->getTotal();
                $purchase->status = $due > 0 && $total !== $due
                    ? 3
                    : 2;
                $purchase->save();
                Log::info('Updated purchase status after payment deletion', [
                    'purchase_id' => $purchaseId,
                    'status'     => $purchase->status
                ]);
                Utility::userBalance(
                    'vendor',
                    $purchase->vendor_id,
                    $amount,
                    'credit'
                );
                Log::info('Vendor balance credited', [
                    'vendor_id' => $purchase->vendor_id,
                    'amount'   => $amount
                ]);
                Utility::bankAccountBalance(
                    $accountId,
                    $amount,
                    'credit'
                );
                Log::info('Bank account balance credited', [
                    'account_id' => $accountId,
                    'amount'    => $amount
                ]);
                Transaction::destroyTransaction(
                    $paymentId,
                    'Partial',
                    'Vendor'
                );
                Log::info('Transaction destroyed', [
                    'payment_id' => $paymentId
                ]);
            });
            return redirect()->back()->with(
                'success',
                __('Payment successfully deleted.')
            );
        } catch (AuthorizationException $e) {
            return defaultPermissionDenial(
                $request,
                $e,
                __CLASS__ . '::' . __FUNCTION__,
                route(self::ROUTE_INDEX)
            );
        } catch (\Throwable $e) {
            Log::error(__METHOD__ . ' failed', ['exception' => $e]);
            return defaultUndefinedException(
                $request,
                $e,
                __CLASS__ . '::' . __FUNCTION__,
                route(self::ROUTE_INDEX)
            );
        }
    }

    public function vendor(Request $request): View|RedirectResponse
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        ) return $userOrRedirect;
        $user = $userOrRedirect;
        if (
            $r = self::guard(
                $request,
                'view purchase',
                self::ROUTE_INDEX
            )
        ) return $r;
        Log::info(__METHOD__ . ' invoked', [
            UsersConstants::COL_USER_ID   => $user?->id,
            'vendor_id' => $request->id
        ]);
        try {
            $vendor = Vendor::findOrFail($request->id);
            Log::debug('Loaded vendor detail', [
                'vendor_id' => $vendor->id
            ]);
            return view(
                ViewsConstants::PRC . '.vendor_detail',
                compact('vendor')
            );
        } catch (\Throwable $e) {
            Log::error(__METHOD__ . ' failed', ['exception' => $e]);
            return defaultUndefinedException(
                $request,
                $e,
                __CLASS__ . '::' . __FUNCTION__,
                route(self::ROUTE_INDEX)
            );
        }
    }

    public function product(Request $request): string
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        ) return $userOrRedirect;
        $user = $userOrRedirect;
        if (
            $r = self::guard(
                $request,
                'view purchase',
                self::ROUTE_INDEX
            )
        ) return $r;
        Log::info(__METHOD__ . ' invoked', [
            UsersConstants::COL_USER_ID     => $user?->id,
            'product_id'  => $request->product_id
        ]);
        try {
            $product = ProductService::findOrFail(
                $request->product_id
            );
            Log::debug('Loaded product', [
                'product_id' => $product->id
            ]);
            $unit       = $product->unit?->name ?? '';
            $taxRate    = $product->tax_id
                ? $product->taxRate($product->tax_id)
                : 0;
            $taxes      = $product->tax_id
                ? Utility::tax($product->tax_id)
                : [];
            $salePrice  = $product->purchase_price;
            $quantity   = 1;
            $taxPrice   = ($taxRate / 100) * ($salePrice * $quantity);
            $totalAmount = $salePrice * $quantity;
            $data = [
                'product'     => $product,
                'unit'        => $unit,
                'taxRate'     => $taxRate,
                'taxes'       => $taxes,
                'totalAmount' => $totalAmount
            ];
            Log::info('Returning product JSON', [
                'product_id' => $product->id
            ]);
            return json_encode($data);
        } catch (\Throwable $e) {
            Log::error(__METHOD__ . ' failed', ['exception' => $e]);
            return '{}';
        }
    }

    public function productDestroy(Request $request): RedirectResponse
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        ) return $userOrRedirect;
        $user = $userOrRedirect;
        if (
            $r = self::guard(
                $request,
                'delete purchase',
                self::ROUTE_INDEX
            )
        ) return $r;
        Log::info(__METHOD__ . ' invoked', [
            UsersConstants::COL_USER_ID => $user?->id,
            'item_id' => $request->id
        ]);
        try {
            DB::transaction(function () use ($request, $user) {
                $res = PurchaseProduct::findOrFail(
                    $request->id
                );
                Log::debug('Found PurchaseProduct', [
                    'id' => $res->id
                ]);
                $purchase = Purchase::where(
                    DatabaseConstants::TABLE_CREATOR,
                    $user?->creatorId()
                )->firstOrFail();
                $warehouseId = $purchase->warehouse_id;
                $warePro = WarehouseProduct::where([
                    'warehouse_id' => $warehouseId,
                    'product_id'  => $res->product_id
                ])->firstOrFail();
                if ($res->quantity >= $warePro->quantity) {
                    $warePro->delete();
                    Log::info('Deleted WarehouseProduct', [
                        'warehouse_id' => $warehouseId,
                        'product_id'  => $res->product_id
                    ]);
                } else {
                    $warePro->decrement(
                        'quantity',
                        $res->quantity
                    );
                    Log::info('Decremented WarehouseProduct quantity', [
                        'warehouse_id' => $warehouseId,
                        'product_id'  => $res->product_id,
                        'new_qty'     => $warePro->quantity
                    ]);
                }
                $res->delete();
                Log::info('Deleted PurchaseProduct', [
                    'id' => $res->id
                ]);
            });
            return redirect()->back()->with(
                'success',
                __('Purchase product successfully deleted.')
            );
        } catch (AuthorizationException $e) {
            return defaultPermissionDenial(
                $request,
                $e,
                __CLASS__ . '::' . __FUNCTION__,
                route(self::ROUTE_INDEX)
            );
        } catch (\Throwable $e) {
            Log::error(__METHOD__ . ' failed', ['exception' => $e]);
            return defaultUndefinedException(
                $request,
                $e,
                __CLASS__ . '::' . __FUNCTION__,
                route(self::ROUTE_INDEX)
            );
        }
    }

    private function purchaseNumber(): int
    {
        try {
            if (
                ($userOrRedirect = self::_checkLogin())
                instanceof \Illuminate\Http\RedirectResponse
            )
                return -1;
            $user = $userOrRedirect;
            Log::debug(__METHOD__ . ' generating purchase number', [
                'creator_id' => $user?->creatorId()
            ]);
            $latest = Purchase::where(
                DatabaseConstants::TABLE_CREATOR,
                $user?->creatorId()
            )->latest()->first();
            return $latest
                ? $latest->purchase_id + 1
                : 1;
        } catch (\Throwable $e) {
            Log::error(__METHOD__ . ' failed', ['exception' => $e]);
            return 1;
        }
    }
}
