<?php

namespace App\Http\Controllers;

use App\Config\Constants\{
    DatabaseConstants as DC,
    PermissionsConstants,
    UsersConstants as UC,
    ViewsConstants
};
use App\Models\{BillProduct, InvoiceProduct, ProposalProduct, Tax};
use App\Traits\{ChecksLogin, ChecksPermissions};
use Illuminate\Http\{RedirectResponse, Request};
use Illuminate\Support\Facades\{Auth, DB, Log, Validator, View as ViewFacade};
use Illuminate\View\View;

use function App\Http\Controllers\Helpers\{defaultUndefinedException, defaultPermissionDenial};
use App\Traits\HasCrudConstants;
use App\Traits\DefinesResourceActions;
class TaxController extends Controller
{
	use DefinesResourceActions;

    use HasCrudConstants;

    use ChecksLogin, ChecksPermissions;

    private const INDEX_ROUTE = ViewsConstants::TX . '.index';

    public function index(Request $request): View|RedirectResponse
    {
        $cls = __CLASS__;
        $meth = __METHOD__;
        $func = __FUNCTION__;
        $action = $meth;

        return $this->measureProfile($action, function () use ($request, $func, $action) {
            if (($user = self::_checkLogin()) instanceof RedirectResponse) return $user;
            Log::debug($action . ' start', [UC::COL_USER_ID => $user?->id]);
            if (($denial = self::guard($request, PermissionsConstants::MNG_CT_TX, self::INDEX_ROUTE)) !== true) return $denial;

            $taxes = Tax::where(DC::COL_TABLE_CREATOR, $user?->creatorId())->get();

            $view = ViewsConstants::TX . '.' . $func;
            if (!ViewFacade::exists($view)) return defaultUndefinedException($request, new \Exception('view'), $action, route(self::INDEX_ROUTE));

            return ViewFacade::make($view, compact('taxes'));
        }, [UC::COL_USER_ID => $request->user()?->id ?? null]);
    }

    public function create(Request $request): View|RedirectResponse
    {
        $cls = __CLASS__;
        $meth = __METHOD__;
        $func = __FUNCTION__;
        $action = $meth;

        return $this->measureProfile($action, function () use ($request, $func, $action) {
            if (($user = self::_checkLogin()) instanceof RedirectResponse) return $user;
            Log::debug($action . ' start', [UC::COL_USER_ID => $user?->id]);
            if (($denial = self::guard($request, 'create constant tax', self::INDEX_ROUTE)) !== true) return $denial;

            $view = ViewsConstants::TX . '.' . $func;
            if (!ViewFacade::exists($view)) return defaultUndefinedException($request, new \Exception('view'), $action, route(self::INDEX_ROUTE));

            return ViewFacade::make($view);
        }, [UC::COL_USER_ID => $request->user()?->id ?? null]);
    }

    public function show(Request $request, Tax $tax): View|RedirectResponse
    {
        $cls = __CLASS__;
        $meth = __METHOD__;
        $func = __FUNCTION__;
        $action = $meth;

        return $this->measureProfile($action, function () use ($request, $tax, $action) {
            if (($user = self::_checkLogin()) instanceof RedirectResponse) return $user;
            Log::debug($action . ' start', [UC::COL_USER_ID => $user?->id, 'tax_id' => $tax->id]);
            if (($denial = self::guard($request, 'view constant tax', self::INDEX_ROUTE)) !== true) return $denial;
            if ($tax->created_by !== $user?->creatorId())
                return defaultPermissionDenial($request, new \Exception('owner'), $action, route(self::INDEX_ROUTE), false);
            $view = ViewsConstants::TX . '.show';
            if (!ViewFacade::exists($view)) return defaultUndefinedException($request, new \Exception('view'), $action, route(self::INDEX_ROUTE));

            return ViewFacade::make($view, compact('tax'));
        }, ['tax_id' => $tax->id]);
    }

    public function store(Request $request): RedirectResponse
    {
        $cls = __CLASS__;
        $meth = __METHOD__;
        $func = __FUNCTION__;
        $action = $meth;

        return $this->measureProfile($action, function () use ($request, $action) {
            if (($user = self::_checkLogin()) instanceof RedirectResponse) return $user;
            Log::debug($action . ' start', [UC::COL_USER_ID => $user?->id, 'input' => $request->only('name', 'rate')]);
            if (($denial = self::guard($request, 'create constant tax', self::INDEX_ROUTE)) !== true) return $denial;

            $v = Validator::make($request->all(), ['name' => 'required|string|max:20', 'rate' => 'required|numeric']);
            if ($v->fails()) {
                Log::debug($action . ' validation failed', ['errors' => $v->errors()->all()]);
                return redirect()->back()->with('error', $v->errors()->first());
            }

            try {
                DB::transaction(function () use ($request, $user, $action) {
                    $rate = is_float($request->rate) ? $request->rate : (is_int($request->rate) ? (float)$request->rate : (is_numeric($request->rate) ? (float)$request->rate : 0));
                    if (!is_float($rate)) {
                        throw new \InvalidArgumentException('Invalid rate value');
                    }
                    if (Tax::where('name', $request->name)->where(DC::COL_TABLE_CREATOR, $user?->creatorId())->exists()) {
                        throw new \InvalidArgumentException('Tax name already exists');
                    }
                    $tax = Tax::create([
                        'name' => $request->name,
                        'rate' => $rate,
                        DC::COL_TABLE_CREATOR => $user?->creatorId()
                    ]);
                    Log::info($action . ' created', ['tax_id' => $tax->id]);
                });

                return redirect()->route(self::INDEX_ROUTE)->with('success', __('Tax rate successfully created.'));
            } catch (\Throwable $e) {
                Log::error($action . ' failed', ['error' => $e->getMessage()]);
                return defaultUndefinedException($request, $e, $action, route(self::INDEX_ROUTE));
            }
        }, [UC::COL_USER_ID => $request->user()?->id ?? null, 'input' => $request->only('name', 'rate')]);
    }

    public function edit(Request $request, Tax $tax): View|RedirectResponse
    {
        $cls = __CLASS__;
        $meth = __METHOD__;
        $func = __FUNCTION__;
        $action = $meth;

        return $this->measureProfile($action, function () use ($request, $tax, $action) {
            if (($user = self::_checkLogin()) instanceof RedirectResponse) return $user;
            Log::debug($action . ' start', [UC::COL_USER_ID => $user?->id, 'tax_id' => $tax->id]);
            if (($denial = self::guard($request, 'edit constant tax', self::INDEX_ROUTE)) !== true) return $denial;
            if ($tax->created_by !== $user?->creatorId()) {
                return defaultPermissionDenial($request, new \Exception('owner'), $action, route(self::INDEX_ROUTE), false);
            }

            $view = ViewsConstants::TX . '.edit';
            if (!ViewFacade::exists($view)) return defaultUndefinedException($request, new \Exception('view'), $action, route(self::INDEX_ROUTE));

            return ViewFacade::make($view, compact('tax'));
        }, ['tax_id' => $tax->id]);
    }

    public function update(Request $request, Tax $tax): RedirectResponse
    {
        $cls = __CLASS__;
        $meth = __METHOD__;
        $func = __FUNCTION__;
        $action = $meth;

        return $this->measureProfile($action, function () use ($request, $tax, $action) {
            if (($user = self::_checkLogin()) instanceof RedirectResponse) return $user;
            Log::debug($action . ' start', [UC::COL_USER_ID => $user?->id, 'tax_id' => $tax->id, 'input' => $request->only('name', 'rate')]);
            if (($denial = self::guard($request, 'edit constant tax', self::INDEX_ROUTE)) !== true) return $denial;
            if ($tax->created_by !== $user?->creatorId()) {
                return defaultPermissionDenial($request, new \Exception('owner'), $action, route(self::INDEX_ROUTE), false);
            }

            $v = Validator::make($request->all(), ['name' => 'required|string|max:20', 'rate' => 'required|numeric']);
            if ($v->fails()) {
                Log::debug($action . ' validation failed', ['errors' => $v->errors()->all()]);
                return redirect()->back()->with('error', $v->errors()->first());
            }

            try {
                DB::transaction(function () use ($request, $tax, $action) {
                    $tax->update($request->only('name', 'rate'));
                    Log::info($action . ' updated', ['tax_id' => $tax->id]);
                });

                return redirect()->route(self::INDEX_ROUTE)->with('success', __('Tax rate successfully updated.'));
            } catch (\Throwable $e) {
                Log::error($action . ' failed', ['error' => $e->getMessage()]);
                return defaultUndefinedException($request, $e, $action, route(self::INDEX_ROUTE));
            }
        }, ['tax_id' => $tax->id, 'input' => $request->only('name', 'rate')]);
    }

    public function destroy(Request $request, Tax $tax): RedirectResponse
    {
        $cls = __CLASS__;
        $meth = __METHOD__;
        $func = __FUNCTION__;
        $action = $meth;

        return $this->measureProfile($action, function () use ($request, $tax, $action) {
            if (($user = self::_checkLogin()) instanceof RedirectResponse) return $user;
            Log::debug($action . ' start', [UC::COL_USER_ID => $user?->id, 'tax_id' => $tax->id]);
            if (($denial = self::guard($request, 'delete constant tax', self::INDEX_ROUTE)) !== true) return $denial;
            if ($tax->created_by !== $user?->creatorId()) {
                return defaultPermissionDenial($request, new \Exception('owner'), $action, route(self::INDEX_ROUTE), false);
            }

            try {
                $inUse = ProposalProduct::whereRaw('find_in_set(?,tax)', [$tax->id])->exists()
                    || BillProduct::whereRaw('find_in_set(?,tax)', [$tax->id])->exists()
                    || InvoiceProduct::whereRaw('find_in_set(?,tax)', [$tax->id])->exists();

                if ($inUse) {
                    Log::debug($action . ' tax in use', ['tax_id' => $tax->id]);
                    return redirect()->back()->with('error', __('This tax is already assigned; remove associated records first.'));
                }

                DB::transaction(function () use ($tax, $action) {
                    $id = $tax->id;
                    $tax->delete();
                    Log::info($action . ' deleted', ['tax_id' => $id]);
                });

                return redirect()->route(self::INDEX_ROUTE)->with('success', __('Tax rate successfully deleted.'));
            } catch (\Throwable $e) {
                Log::error($action . ' failed', ['error' => $e->getMessage()]);
                return defaultUndefinedException($request, $e, $action, route(self::INDEX_ROUTE));
            }
        }, ['tax_id' => $tax->id]);
    }
}
