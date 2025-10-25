<?php

namespace App\Http\Controllers;

use App\Config\Constants\{DatabaseConstants, PermissionsConstants, ViewsConstants};
use App\Models\{Warehouse, WarehouseProduct};
use App\Traits\{ChecksLogin, ChecksPermissions};
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\{RedirectResponse, Request};
use Illuminate\Support\Facades\{DB, Log, View as ViewFacade};
use Illuminate\Validation\ValidationException;
use function App\Http\Controllers\{defaultPermissionDenial, defaultUndefinedException};

class WarehouseController extends Controller
{
    use ChecksLogin, ChecksPermissions;

    private const ROUTE_INDEX = ViewsConstants::WRH . '.index';

    public function index(Request $request): Renderable|RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        return $this->measureProfile($action, function () use ($request) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            if (($c = self::guard($request, PermissionsConstants::MNG_WRH, self::ROUTE_INDEX)) !== true) return $c;
            $warehouses = Warehouse::where(DatabaseConstants::TABLE_CREATOR, $request->user()->creatorId())->get();
            Log::debug(__METHOD__ . ' fetched warehouses', ['count' => $warehouses->count()]);
            return ViewFacade::make(ViewsConstants::WRH . '.index', compact('warehouses'));
        }, ['method' => $method, 'class' => class_basename(static::class)]);
    }

    public function create(Request $request): Renderable|RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        return $this->measureProfile($action, function () use ($request) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            if (($c = self::guard($request, 'create warehouse', self::ROUTE_INDEX)) !== true) return $c;
            return ViewFacade::make(ViewsConstants::WRH . '.create');
        }, ['method' => $method, 'class' => class_basename(static::class)]);
    }

    public function store(Request $request): RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        return $this->measureProfile($action, function () use ($request, $action) {
            try {
                if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
                if (($c = self::guard($request, 'create warehouse', self::ROUTE_INDEX)) !== true) return $c;

                $data = $request->validate([
                    'name'     => 'required|string|max:255',
                    'address'  => 'nullable|string',
                    'city'     => 'nullable|string',
                    'city_zip' => 'nullable|string',
                ]);

                DB::transaction(function () use ($data, $request) {
                    $warehouse = new Warehouse();
                    $warehouse->fill($data);
                    $warehouse->created_by = $request->user()->creatorId();
                    $warehouse->save();
                    Log::info(__METHOD__ . ' created warehouse', ['id' => $warehouse->id]);
                });

                return redirect()->route(self::ROUTE_INDEX)->with('success', __('Warehouse successfully created.'));
            } catch (ValidationException $ve) {
                Log::debug(__METHOD__ . ' validation failed', ['errors' => $ve->errors()]);
                return redirect()->back()->with('error', array_values($ve->errors())[0][0]);
            } catch (\Throwable $e) {
                Log::error(__METHOD__ . ' failed', ['exception' => $e]);
                return defaultUndefinedException($request, $e, $action, route(self::ROUTE_INDEX));
            }
        }, ['method' => $method, 'class' => class_basename(static::class)]);
    }

    public function show(Request $request, Warehouse $warehouse): Renderable|RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        return $this->measureProfile($action, function () use ($request, $warehouse, $action) {
            try {
                if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
                if (($c = self::guard($request, 'show warehouse', self::ROUTE_INDEX)) !== true) return $c;
                if ($warehouse->created_by !== $request->user()->creatorId()) return defaultPermissionDenial($request, null, __METHOD__, route(self::ROUTE_INDEX));

                $products = WarehouseProduct::where('warehouse_id', $warehouse->id)
                    ->where(DatabaseConstants::TABLE_CREATOR, $request->user()->creatorId())
                    ->with('product')
                    ->get();

                Log::debug(__METHOD__ . ' fetched products for warehouse', ['warehouse_id' => $warehouse->id, 'count' => $products->count()]);

                return ViewFacade::make(ViewsConstants::WRH . '.show', ['warehouse' => $warehouse, 'products' => $products]);
            } catch (\Throwable $e) {
                Log::error(__METHOD__ . ' failed', ['exception' => $e]);
                return defaultUndefinedException($request, $e, $action, route(self::ROUTE_INDEX));
            }
        }, ['method' => $method, 'class' => class_basename(static::class)]);
    }

    public function edit(Request $request, Warehouse $warehouse): Renderable|RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        return $this->measureProfile($action, function () use ($request, $warehouse, $action) {
            try {
                if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
                if (($c = self::guard($request, 'edit warehouse', self::ROUTE_INDEX)) !== true) return $c;
                if ($warehouse->created_by !== $request->user()->creatorId()) return defaultPermissionDenial($request, null, __METHOD__, route(self::ROUTE_INDEX));

                return ViewFacade::make(ViewsConstants::WRH . '.edit', compact('warehouse'));
            } catch (\Throwable $e) {
                Log::error(__METHOD__ . ' failed', ['exception' => $e]);
                return defaultUndefinedException($request, $e, $action, route(self::ROUTE_INDEX));
            }
        }, ['method' => $method, 'class' => class_basename(static::class)]);
    }

    public function update(Request $request, Warehouse $warehouse): RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        return $this->measureProfile($action, function () use ($request, $warehouse, $action) {
            try {
                if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
                if (($c = self::guard($request, 'edit warehouse', self::ROUTE_INDEX)) !== true) return $c;
                if ($warehouse->created_by !== $request->user()->creatorId()) return defaultPermissionDenial($request, null, __METHOD__, route(self::ROUTE_INDEX));

                $data = $request->validate([
                    'name'     => 'required|string|max:255',
                    'address'  => 'nullable|string',
                    'city'     => 'nullable|string',
                    'city_zip' => 'nullable|string',
                ]);

                DB::transaction(function () use ($data, $warehouse) {
                    $warehouse->fill($data)->save();
                    Log::info(__METHOD__ . ' updated warehouse', ['id' => $warehouse->id]);
                });

                return redirect()->route(self::ROUTE_INDEX)->with('success', __('Warehouse successfully updated.'));
            } catch (ValidationException $ve) {
                Log::debug(__METHOD__ . ' validation failed', ['errors' => $ve->errors()]);
                return redirect()->back()->with('error', array_values($ve->errors())[0][0]);
            } catch (\Throwable $e) {
                Log::error(__METHOD__ . ' failed', ['exception' => $e]);
                return defaultUndefinedException($request, $e, $action, route(self::ROUTE_INDEX));
            }
        }, ['method' => $method, 'class' => class_basename(static::class)]);
    }

    public function destroy(Request $request, Warehouse $warehouse): RedirectResponse
    {
        $action = __FUNCTION__;
        $method = __METHOD__;
        return $this->measureProfile($action, function () use ($request, $warehouse, $action) {
            try {
                if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
                if (($c = self::guard($request, 'delete warehouse', self::ROUTE_INDEX)) !== true) return $c;
                if ($warehouse->created_by !== $request->user()->creatorId()) return defaultPermissionDenial($request, null, __METHOD__, route(self::ROUTE_INDEX));

                DB::transaction(function () use ($warehouse) {
                    $warehouse->delete();
                    Log::info(__METHOD__ . ' deleted warehouse', ['id' => $warehouse->id]);
                });

                return redirect()->route(self::ROUTE_INDEX)->with('success', __('Warehouse successfully deleted.'));
            } catch (\Throwable $e) {
                Log::error(__METHOD__ . ' failed', ['exception' => $e]);
                return defaultUndefinedException($request, $e, $action, route(self::ROUTE_INDEX));
            }
        }, ['method' => $method, 'class' => class_basename(static::class)]);
    }
}
