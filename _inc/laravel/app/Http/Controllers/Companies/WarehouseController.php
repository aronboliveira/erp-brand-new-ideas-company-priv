<?php

namespace App\Http\Controllers;

use App\Config\Constants\{DatabaseConstants, PermissionsConstants, ViewsConstants};
use App\Models\{Warehouse, WarehouseProduct};
use App\Traits\{ChecksLogin, ChecksPermissions};
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\{RedirectResponse, Request};
use Illuminate\Support\Facades\{DB, Log};
use function App\Http\Controllers\{defaultPermissionDenial, defaultUndefinedException};

class WarehouseController extends Controller
{
    use ChecksLogin, ChecksPermissions;

    private const ROUTE_INDEX = ViewsConstants::WRH . '.index';

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Renderable|RedirectResponse
    {
        try {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $this->guard($request, PermissionsConstants::MNG_WRH, self::ROUTE_INDEX);
            $warehouses = Warehouse::where(DatabaseConstants::TABLE_CREATOR, $request->user()->creatorId())->get();
            Log::info(__METHOD__ . ' fetched warehouses', ['count' => $warehouses->count()]);
            return view(ViewsConstants::WRH . '.index', compact('warehouses'));
        } catch (\Throwable $e) {
            Log::error(__METHOD__ . ' failed', ['exception' => $e]);
            return defaultUndefinedException($request, $e, __CLASS__ . '::' . __FUNCTION__, route(self::ROUTE_INDEX));
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request): Renderable|RedirectResponse
    {
        if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
        $this->guard($request, 'create warehouse', self::ROUTE_INDEX);

        return view(ViewsConstants::WRH . '.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        try {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $this->guard($request, 'create warehouse', self::ROUTE_INDEX);

            $data = $request->validate([
                'name'    => 'required|string|max:255',
                'address' => 'nullable|string',
                'city'    => 'nullable|string',
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
        } catch (\Illuminate\Validation\ValidationException $ve) {
            Log::warning(__METHOD__ . ' validation failed', ['errors' => $ve->errors()]);
            return redirect()->back()->with('error', array_values($ve->errors())[0][0]);
        } catch (\Throwable $e) {
            Log::error(__METHOD__ . ' failed', ['exception' => $e]);
            return defaultUndefinedException($request, $e, __CLASS__ . '::' . __FUNCTION__, route(self::ROUTE_INDEX));
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Warehouse $warehouse): Renderable|RedirectResponse
    {
        try {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $this->guard($request, 'show warehouse', self::ROUTE_INDEX);

            $products = WarehouseProduct::where('warehouse_id', $warehouse->id)
                ->where(DatabaseConstants::TABLE_CREATOR, $request->user()->creatorId())
                ->with('product')
                ->get();

            Log::info(__METHOD__ . ' fetched products for warehouse', ['warehouse_id' => $warehouse->id, 'count' => $products->count()]);

            return view(ViewsConstants::WRH . '.show', ['warehouse' => $products]);
        } catch (\Throwable $e) {
            Log::error(__METHOD__ . ' failed', ['exception' => $e]);
            return defaultUndefinedException($request, $e, __CLASS__ . '::' . __FUNCTION__, route(self::ROUTE_INDEX));
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, Warehouse $warehouse): Renderable|RedirectResponse
    {
        try {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $this->guard($request, 'edit warehouse', self::ROUTE_INDEX);
            if ($warehouse->created_by !== $request->user()->creatorId()) return defaultPermissionDenial($request, null, __METHOD__, route(self::ROUTE_INDEX));

            return view(ViewsConstants::WRH . '.edit', compact('warehouse'));
        } catch (\Throwable $e) {
            Log::error(__METHOD__ . ' failed', ['exception' => $e]);
            return defaultUndefinedException($request, $e, __CLASS__ . '::' . __FUNCTION__, route(self::ROUTE_INDEX));
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Warehouse $warehouse): RedirectResponse
    {
        try {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $this->guard($request, 'edit warehouse', self::ROUTE_INDEX);
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
        } catch (\Illuminate\Validation\ValidationException $ve) {
            Log::warning(__METHOD__ . ' validation failed', ['errors' => $ve->errors()]);
            return redirect()->back()->with('error', array_values($ve->errors())[0][0]);
        } catch (\Throwable $e) {
            Log::error(__METHOD__ . ' failed', ['exception' => $e]);
            return defaultUndefinedException($request, $e, __CLASS__ . '::' . __FUNCTION__, route(self::ROUTE_INDEX));
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Warehouse $warehouse): RedirectResponse
    {
        try {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $this->guard($request, 'delete warehouse', self::ROUTE_INDEX);
            if ($warehouse->created_by !== $request->user()->creatorId()) return defaultPermissionDenial($request, null, __METHOD__, route(self::ROUTE_INDEX));

            DB::transaction(function () use ($warehouse) {
                $warehouse->delete();
                Log::info(__METHOD__ . ' deleted warehouse', ['id' => $warehouse->id]);
            });

            return redirect()->route(self::ROUTE_INDEX)->with('success', __('Warehouse successfully deleted.'));
        } catch (\Throwable $e) {
            Log::error(__METHOD__ . ' failed', ['exception' => $e]);
            return defaultUndefinedException($request, $e, __CLASS__ . '::' . __FUNCTION__, route(self::ROUTE_INDEX));
        }
    }
}
