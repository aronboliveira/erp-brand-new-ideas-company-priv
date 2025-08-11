<?php

namespace App\Http\Controllers;

use App\Config\Constants\{
    DatabaseConstants,
    PermissionsConstants,
    ViewsConstants
};
use App\Models\{BillProduct, InvoiceProduct, ProposalProduct, Tax};
use App\Traits\{ChecksLogin, ChecksPermissions};
use Illuminate\Http\{RedirectResponse, Request};
use Illuminate\Support\Facades\{Auth, DB, Log, Validator};

class TaxController extends Controller
{
    use ChecksLogin, ChecksPermissions;

    private const INDEX_ROUTE = ViewsConstants::TX . '.index';

    public function index(Request $request)
    {
        $function = __FUNCTION__;
        if (($user = self::_checkLogin()) instanceof RedirectResponse)
            return $user;
        Log::info('Entering ' . __METHOD__, ['user' => $user?->id]);
        if ($denial = $this->guard($request, PermissionsConstants::MNG_CT_TX, self::INDEX_ROUTE))
            return $denial;
        $taxes = Tax::where(DatabaseConstants::TABLE_CREATOR, $user?->creatorId())->get();
        return view(ViewsConstants::TX . '.' . $function, compact('taxes'));
    }

    public function create(Request $request)
    {
        $function = __FUNCTION__;
        if (($user = self::_checkLogin()) instanceof RedirectResponse)
            return $user;
        Log::info('Entering ' . __METHOD__, ['user' => $user?->id]);
        if ($denial = $this->guard($request, 'create constant tax', self::INDEX_ROUTE))
            return $denial;
        return view(ViewsConstants::TX . '.' . $function);
    }

    public function show(Request $request, Tax $tax)
    {
        if (($user = self::_checkLogin()) instanceof RedirectResponse)
            return $user;
        Log::info('Entering ' . __METHOD__, ['user' => $user?->id, 'tax' => $tax->id]);
        if ($denial = $this->guard($request, 'view constant tax', self::INDEX_ROUTE))
            return $denial;
        if ($tax->created_by !== $user?->creatorId())
            return defaultPermissionDenial(
                $request,
                new \Exception('owner'),
                __CLASS__ . '::' . __FUNCTION__,
                route(self::INDEX_ROUTE),
                false
            );
        return view(ViewsConstants::TX . '.show', compact('tax'));
    }

    public function store(Request $request): RedirectResponse
    {
        if (($user = self::_checkLogin()) instanceof RedirectResponse)
            return $user;
        Log::info('Entering ' . __METHOD__, ['user' => $user?->id, 'input' => $request->all()]);
        if ($denial = $this->guard($request, 'create constant tax', self::INDEX_ROUTE))
            return $denial;

        $v = Validator::make($request->all(), [
            'name' => 'required|string|max:20',
            'rate' => 'required|numeric'
        ]);
        if ($v->fails()) {
            Log::warning('Validation failed in store', ['errors' => $v->errors()->all()]);
            return redirect()->back()->with('error', $v->errors()->first());
        }

        try {
            DB::transaction(function () use ($request, $user) {
                $tax = new Tax([
                    'name'       => $request->name,
                    'rate'       => $request->rate,
                    DatabaseConstants::TABLE_CREATOR => $user?->creatorId()
                ]);
                $tax->save();
                Log::info('Tax created', ['id' => $tax->id]);
            });
            return redirect()->route(self::INDEX_ROUTE)
                ->with('success', __('Tax rate successfully created.'));
        } catch (\Throwable $e) {
            Log::error('Tax store failed', ['error' => $e->getMessage()]);
            return defaultUndefinedException($request, $e, __CLASS__ . '::store');
        }
    }

    public function edit(Request $request, Tax $tax)
    {
        if (($user = self::_checkLogin()) instanceof RedirectResponse)
            return $user;
        Log::info('Entering ' . __METHOD__, ['user' => $user?->id, 'tax' => $tax->id]);
        if ($denial = $this->guard($request, 'edit constant tax', self::INDEX_ROUTE))
            return $denial;
        if ($tax->created_by !== $user?->creatorId())
            return defaultPermissionDenial($request, new \Exception('owner'), __CLASS__ . '::edit', route(self::INDEX_ROUTE), false);

        return view(ViewsConstants::TX . '.edit', compact('tax'));
    }

    public function update(Request $request, Tax $tax): RedirectResponse
    {
        if (($user = self::_checkLogin()) instanceof RedirectResponse)
            return $user;
        Log::info('Entering ' . __METHOD__, ['user' => $user?->id, 'tax' => $tax->id, 'input' => $request->all()]);
        if ($denial = $this->guard($request, 'edit constant tax', self::INDEX_ROUTE))
            return $denial;
        if ($tax->created_by !== $user?->creatorId())
            return defaultPermissionDenial($request, new \Exception('owner'), __CLASS__ . '::update', route(self::INDEX_ROUTE), false);

        $v = Validator::make($request->all(), [
            'name' => 'required|string|max:20',
            'rate' => 'required|numeric'
        ]);
        if ($v->fails()) {
            Log::warning('Validation failed in update', ['errors' => $v->errors()->all()]);
            return redirect()->back()->with('error', $v->errors()->first());
        }

        try {
            DB::transaction(function () use ($request, $tax) {
                $tax->update($request->only('name', 'rate'));
                Log::info('Tax updated', ['id' => $tax->id]);
            });
            return redirect()->route(self::INDEX_ROUTE)
                ->with('success', __('Tax rate successfully updated.'));
        } catch (\Throwable $e) {
            Log::error('Tax update failed', ['error' => $e->getMessage()]);
            return defaultUndefinedException($request, $e, __CLASS__ . '::update');
        }
    }

    public function destroy(Request $request, Tax $tax): RedirectResponse
    {
        if (($user = self::_checkLogin()) instanceof RedirectResponse)
            return $user;
        Log::info('Entering ' . __METHOD__, ['user' => $user?->id, 'tax' => $tax->id]);
        if ($denial = $this->guard($request, 'delete constant tax', self::INDEX_ROUTE))
            return $denial;
        if ($tax->created_by !== $user?->creatorId())
            return defaultPermissionDenial($request, new \Exception('owner'), __CLASS__ . '::destroy', route(self::INDEX_ROUTE), false);

        try {
            $inUse = ProposalProduct::whereRaw("find_in_set('{$tax->id}',tax)")->exists()
                || BillProduct::whereRaw("find_in_set('{$tax->id}',tax)")->exists()
                || InvoiceProduct::whereRaw("find_in_set('{$tax->id}',tax)")->exists();
            if ($inUse) {
                Log::warning('Attempt to delete tax in use', ['tax' => $tax->id]);
                return redirect()->back()
                    ->with('error', __('This tax is already assigned; remove associated records first.'));
            }

            DB::transaction(function () use ($tax) {
                $tax->delete();
                Log::info('Tax deleted', ['id' => $tax->id]);
            });
            return redirect()->route(self::INDEX_ROUTE)
                ->with('success', __('Tax rate successfully deleted.'));
        } catch (\Throwable $e) {
            Log::error('Tax destroy failed', ['error' => $e->getMessage()]);
            return defaultUndefinedException($request, $e, __CLASS__ . '::destroy');
        }
    }
}
