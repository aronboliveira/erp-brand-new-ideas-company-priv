<?php

namespace App\Http\Controllers;

use App\Config\Constants\{
    DatabaseConstants,
    PermissionsConstants,
    ViewsConstants
};
use App\Models\{ProductService, Utility};
use App\Traits\{ChecksLogin, ChecksPermissions};
use Illuminate\Http\{
    JsonResponse,
    RedirectResponse,
    Request
};
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

use function App\Http\Controllers\Helpers\defaultUndefinedException;
final class ProductStockController extends Controller
{
    use ChecksLogin, ChecksPermissions;

    private const REDIRECT_INDEX = ViewsConstants::PRD_STK . '.index';

    public function index(Request $r): RedirectResponse|View|bool
    {
        $cls = __CLASS__;
        $fn = __FUNCTION__;
        $action = "$cls::$fn";
        return $this->measureProfile($action, function () use ($r) {
            if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
            if (($c = self::guard($r, PermissionsConstants::MNG_PRD_SV, self::REDIRECT_INDEX)) !== true) return $c;
            $productServices = ProductService::query()
                ->where(DatabaseConstants::COL_TABLE_CREATOR, $u->creatorId())
                ->where('type', 'product')
                ->get();
            return view(ViewsConstants::PRD_STK . '.index', compact('productServices'));
        });
    }

    public function create(Request $r): RedirectResponse|View
    {
        $cls = __CLASS__;
        $fn = __FUNCTION__;
        $action = "$cls::$fn";
        return $this->measureProfile($action, function () use ($r) {
            if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
            if (($c = $this->guard($r, 'edit product & service', self::REDIRECT_INDEX)) !== true) return $c;
            $products = ProductService::where(DatabaseConstants::COL_TABLE_CREATOR, $u->creatorId())
                ->where('type', 'product')
                ->pluck('name', 'id');
            return view(ViewsConstants::PRD_STK . '.create', compact('products'));
        });
    }

    public function store(Request $r): RedirectResponse|JsonResponse
    {
        $cls = __CLASS__;
        $fn = __FUNCTION__;
        $action = "$cls::$fn";
        return $this->measureProfile($action, function () use ($r, $action) {
            if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
            if (($c = self::guard($r, 'edit product & service', self::REDIRECT_INDEX)) !== true) return $c;
            if ($c = self::v($r, [
                'product_id' => 'required|exists:product_services,id',
                'quantity'   => 'required|integer|min:1',
            ])) return $c;
            try {
                $p = ProductService::whereKey($r->product_id)
                    ->where(DatabaseConstants::COL_TABLE_CREATOR, $u->creatorId())
                    ->firstOrFail();
                $p->increment('quantity', $r->quantity);
                Utility::addProductStock(
                    $p->id,
                    $r->quantity,
                    'manually',
                    "{$r->quantity} quantity added manually",
                    0
                );
                return redirect()->route(self::REDIRECT_INDEX)->with('success', __('Product quantity updated manually.'));
            } catch (\Throwable $e) {
                return defaultUndefinedException($r, $e, $action);
            }
        });
    }

    public function edit(Request $r, string|int $id): RedirectResponse|View
    {
        $cls = __CLASS__;
        $fn = __FUNCTION__;
        $action = "$cls::$fn";
        return $this->measureProfile(function () use ($r, $id) {
            if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
            if (($c = self::guard($r, 'edit product & service', self::REDIRECT_INDEX)) !== true) return $c;
            $productService = ProductService::whereKey($id)
                ->where(DatabaseConstants::COL_TABLE_CREATOR, $u->creatorId())
                ->firstOrFail();
            return view(ViewsConstants::PRD_STK . '.edit', compact('productService'));
        });
    }

    public function update(Request $r, string|int $id): RedirectResponse|JsonResponse
    {
        $cls = __CLASS__;
        $fn = __FUNCTION__;
        $action = "$cls::$fn";
        return $this->measureProfile($action, function () use ($r, $id, $action) {
            if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
            if (($c = self::guard($r, 'edit product & service', self::REDIRECT_INDEX)) !== true) return $c;
            if ($c = self::v($r, ['quantity' => 'required|integer|min:1'])) return $c;
            try {
                $p = ProductService::whereKey($id)
                    ->where(DatabaseConstants::COL_TABLE_CREATOR, $u->creatorId())
                    ->firstOrFail();
                $p->increment('quantity', $r->quantity);
                Utility::addProductStock(
                    $p->id,
                    $r->quantity,
                    'manually',
                    "{$r->quantity} quantity added manually",
                    0
                );
                return redirect()->route(self::REDIRECT_INDEX)->with('success', __('Product quantity updated manually.'));
            } catch (\Throwable $e) {
                return defaultUndefinedException($r, $e, $action);
            }
        });
    }

    public function destroy(Request $r, string|int $id): RedirectResponse
    {
        $cls = __CLASS__;
        $fn = __FUNCTION__;
        $action = "$cls::$fn";
        return $this->measureProfile($action, function () use ($r, $id, $action) {
            if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
            if (($c = self::guard($r, 'delete product & service', self::REDIRECT_INDEX)) !== true) return $c;
            try {
                $product = ProductService::whereKey($id)
                    ->where(DatabaseConstants::COL_TABLE_CREATOR, $u->creatorId())
                    ->firstOrFail();
                $product->delete();
                return back()->with('success', __('Product deleted.'));
            } catch (\Throwable $e) {
                return defaultUndefinedException($r, $e, $action);
            }
        });
    }

    public function show(): RedirectResponse
    {
        $cls = __CLASS__;
        $fn = __FUNCTION__;
        $action = "$cls::$fn";
        return $this->measureProfile($action, fn() => redirect()->route(self::REDIRECT_INDEX));
    }

    private static function v(Request $r, array $rules): ?RedirectResponse
    {
        $v = Validator::make($r->all(), $rules);
        return $v->fails()
            ? back()->with('error', $v->getMessageBag()->first())
            : null;
    }
}
