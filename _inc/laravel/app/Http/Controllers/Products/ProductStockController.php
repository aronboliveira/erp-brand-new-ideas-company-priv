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
use Illuminate\Support\Facades\{
    Auth,
    Validator
};
use Illuminate\View\View;

final class ProductStockController extends Controller
{
    use ChecksLogin, ChecksPermissions;

    private const REDIRECT_INDEX = ViewsConstants::PRD_STK . '.index';

    /** GET /productstock */
    public function index(Request $r): RedirectResponse|View
    {
        if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
        if ($c = $this->guard($r, PermissionsConstants::MNG_PRD_SV, self::REDIRECT_INDEX)) return $c;
        $productServices = ProductService::query()
            ->where(DatabaseConstants::TABLE_CREATOR, $u->creatorId())
            ->where('type', 'product')
            ->get();
        return view(ViewsConstants::PRD_STK . '.' . __FUNCTION__, compact('productServices'));
    }

    /** GET /productstock/create — simple “add stock” form */
    public function create(Request $r): RedirectResponse|View
    {
        if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
        if ($c = $this->guard($r, 'edit product & service', self::REDIRECT_INDEX)) return $c;
        $products = ProductService::where(DatabaseConstants::TABLE_CREATOR, $u->creatorId())
            ->where('type', 'product')
            ->pluck('name', 'id');
        return view(ViewsConstants::PRD_STK . '.' . __FUNCTION__, compact('products'));
    }

    /** POST /productstock — add stock *without* going through edit screen */
    public function store(Request $r): RedirectResponse|JsonResponse
    {
        if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
        if ($c = $this->guard($r, 'edit product & service', self::REDIRECT_INDEX)) return $c;
        if ($c = self::v($r, [
            'product_id' => 'required|exists:product_services,id',
            'quantity'   => 'required|integer|min:1',
        ])) return $c;
        try {
            /** @var ProductService $p */
            $p     = ProductService::whereKey($r->product_id)
                ->where(DatabaseConstants::TABLE_CREATOR, $u->creatorId())
                ->firstOrFail();
            $p->increment('quantity', $r->quantity);
            Utility::addProductStock(
                $p->id,
                $r->quantity,
                'manually',
                "{$r->quantity} quantity added manually",
                0
            );
            return redirect()
                ->route(self::REDIRECT_INDEX)
                ->with('success', __('Product quantity updated manually.'));
        } catch (\Throwable $e) {
            return defaultUndefinedException($r, $e, __CLASS__ . '::' . __FUNCTION__);
        }
    }

    /** GET /productstock/{id}/edit */
    public function edit(Request $r, int $id): RedirectResponse|View
    {
        if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
        if ($c = $this->guard($r, 'edit product & service', self::REDIRECT_INDEX)) return $c;
        $productService = ProductService::whereKey($id)
            ->where(DatabaseConstants::TABLE_CREATOR, $u->creatorId())
            ->firstOrFail();
        return view(ViewsConstants::PRD_STK . '.' . __FUNCTION__, compact('productService'));
    }

    /** PUT /productstock/{id} */
    public function update(Request $r, int $id): RedirectResponse|JsonResponse
    {
        if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
        if ($c = $this->guard($r, 'edit product & service', self::REDIRECT_INDEX)) return $c;
        if ($c = self::v($r, ['quantity' => 'required|integer|min:1'])) return $c;
        try {
            /** @var ProductService $p */
            $p = ProductService::whereKey($id)
                ->where(DatabaseConstants::TABLE_CREATOR, $u->creatorId())
                ->firstOrFail();
            $p->increment('quantity', $r->quantity);
            Utility::addProductStock(
                $p->id,
                $r->quantity,
                'manually',
                "{$r->quantity} quantity added manually",
                0
            );
            return redirect()
                ->route(self::REDIRECT_INDEX)
                ->with('success', __('Product quantity updated manually.'));
        } catch (\Throwable $e) {
            return defaultUndefinedException($r, $e, __CLASS__ . '::' . __FUNCTION__);
        }
    }

    /** DELETE /productstock/{id} — remove entire product */
    public function destroy(Request $r, int $id): RedirectResponse
    {
        if (($u = self::_checkLogin()) instanceof RedirectResponse) return $u;
        if ($c = $this->guard($r, 'delete product & service', self::REDIRECT_INDEX)) return $c;
        try {
            $product = ProductService::whereKey($id)
                ->where(DatabaseConstants::TABLE_CREATOR, $u->creatorId())
                ->firstOrFail();

            $product->delete();
            return back()->with('success', __('Product deleted.'));
        } catch (\Throwable $e) {
            return defaultUndefinedException($r, $e, __CLASS__ . '::' . __FUNCTION__);
        }
    }

    public function show(): RedirectResponse
    {
        return redirect()->route(self::REDIRECT_INDEX);
    }

    private static function v(Request $r, array $rules): ?RedirectResponse
    {
        $v = Validator::make($r->all(), $rules);
        return $v->fails()
            ? back()->with('error', $v->getMessageBag()->first())
            : null;
    }
}
