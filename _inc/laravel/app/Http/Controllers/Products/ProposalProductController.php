<?php

namespace App\Http\Controllers;

use App\Config\Constants\{DatabaseConstants, ViewsConstants};
use App\Http\Controllers\Controller;
use App\Models\ProposalProduct;
use App\Traits\{ChecksLogin, ChecksPermissions};
use Illuminate\Http\{RedirectResponse, Request};
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProposalProductController extends Controller
{
    use ChecksLogin;
    use ChecksPermissions;

    private const REDIRECT_INDEX = ViewsConstants::PPS_PRD . '.index';

    /**
     * @return \Illuminate\View\View|RedirectResponse|null
     */
    public function index(Request $request): \Illuminate\View\View|RedirectResponse|null
    {
        $action = __METHOD__;
        if (($user = self::_checkLogin()) instanceof RedirectResponse) return $user;
        if (($redirect = self::guard($request, 'manage proposal product', self::REDIRECT_INDEX)) !== true) return $redirect;
        try {
            $products = ProposalProduct::where(DatabaseConstants::TABLE_CREATOR, $user?->creatorId())->get();
            return view(ViewsConstants::PPS_PRD . '.index', compact('products'));
        } catch (\Throwable $e) {
            Log::error("$action error", ['error' => $e->getMessage()]);
            return defaultUndefinedException($request, $e, $action, route(self::REDIRECT_INDEX));
        }
    }

    /**
     * @return \Illuminate\View\View|RedirectResponse|null
     */
    public function create(Request $request): \Illuminate\View\View|RedirectResponse|null
    {
        $action = __METHOD__;
        if (($user = self::_checkLogin()) instanceof RedirectResponse) return $user;
        if (($redirect = self::guard($request, 'create proposal product', self::REDIRECT_INDEX)) !== true) return $redirect;

        try {
            return view(ViewsConstants::PPS_PRD . '.create');
        } catch (\Throwable $e) {
            Log::error("$action error", ['error' => $e->getMessage()]);
            return defaultUndefinedException($request, $e, $action, route(self::REDIRECT_INDEX));
        }
    }

    public function show(Request $request, ProposalProduct $proposalProduct): \Illuminate\View\View|RedirectResponse|null
    {
        if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
        $user = $userOrRedirect;
        if (($redirect = self::guard($request, 'view proposal product', self::REDIRECT_INDEX)) !== true) return $redirect;
        Log::info(__METHOD__ . ' started', ['user_id' => $user?->id, 'productId' => $proposalProduct->id]);
        try {
            if ($proposalProduct->created_by !== $user?->creatorId())
                return defaultPermissionDenial($request, new \Exception('owner'), __METHOD__, route(self::REDIRECT_INDEX));
            Log::info(__METHOD__ . ' authorized', ['user_id' => $user?->id, 'productId' => $proposalProduct->id]);
            return view(ViewsConstants::PPS_PRD . '.show', ['product' => $proposalProduct]);
        } catch (\Throwable $e) {
            Log::error(__METHOD__ . ' failed', ['user_id' => $user?->id, 'error' => $e->getMessage()]);
            return defaultUndefinedException($request, $e, __METHOD__, route(self::REDIRECT_INDEX));
        }
    }

    /**
     * @return RedirectResponse|null
     */
    public function store(Request $request): RedirectResponse|null
    {
        $action = __METHOD__;
        if (($user = self::_checkLogin()) instanceof RedirectResponse) return $user;
        if (($redirect = self::guard($request, 'create proposal product', self::REDIRECT_INDEX)) !== true) return $redirect;

        $request->validate([
            'name'        => 'required|string',
            'description' => 'nullable|string',
            'price'       => 'required|numeric',
        ]);

        DB::beginTransaction();
        try {
            ProposalProduct::create([
                'name'        => $request->input('name'),
                'description' => $request->input('description'),
                'price'       => $request->input('price'),
                DatabaseConstants::TABLE_CREATOR  => $user?->creatorId(),
            ]);
            DB::commit();
            return redirect()->route(self::REDIRECT_INDEX)
                ->with('success', __('Proposal Product created.'));
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error("$action error", ['error' => $e->getMessage()]);
            return defaultUndefinedException($request, $e, $action, route(self::REDIRECT_INDEX));
        }
    }

    /**
     * @return \Illuminate\View\View|RedirectResponse|null
     */
    public function edit(Request $request, string|int $id): \Illuminate\View\View|RedirectResponse|null
    {
        $action = __METHOD__;
        if (($user = self::_checkLogin()) instanceof RedirectResponse) return $user;
        if (($redirect = self::guard($request, 'edit proposal product', self::REDIRECT_INDEX)) !== true) return $redirect;

        try {
            $product = ProposalProduct::findOrFail($id);
            if ($product->created_by !== $user?->creatorId())
                return defaultPermissionDenial($request, new \Exception('owner'), $action, route(self::REDIRECT_INDEX));
            return view(ViewsConstants::PPS_PRD . '.edit', compact('product'));
        } catch (\Throwable $e) {
            Log::error("$action error", ['error' => $e->getMessage()]);
            return defaultUndefinedException($request, $e, $action, route(self::REDIRECT_INDEX));
        }
    }

    /**
     * @return RedirectResponse|null
     */
    public function update(Request $request, string|int $id): RedirectResponse|null
    {
        $action = __METHOD__;
        if (($user = self::_checkLogin()) instanceof RedirectResponse) return $user;
        if (($redirect = self::guard($request, 'edit proposal product', self::REDIRECT_INDEX)) !== true) return $redirect;

        $request->validate([
            'name'        => 'required|string',
            'description' => 'nullable|string',
            'price'       => 'required|numeric',
        ]);

        DB::beginTransaction();
        try {
            $product = ProposalProduct::findOrFail($id);
            if ($product->created_by !== $user?->creatorId())
                return defaultPermissionDenial($request, new \Exception('owner'), $action, route(self::REDIRECT_INDEX));

            $product->update($request->only(['name', 'description', 'price']));
            DB::commit();
            return redirect()->route(self::REDIRECT_INDEX)
                ->with('success', __('Proposal Product updated.'));
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error("$action error", ['error' => $e->getMessage()]);
            return defaultUndefinedException($request, $e, $action, route(self::REDIRECT_INDEX));
        }
    }

    /**
     * @return RedirectResponse|null
     */
    public function destroy(Request $request, string|int $id): RedirectResponse|null
    {
        $action = __METHOD__;
        if (($user = self::_checkLogin()) instanceof RedirectResponse) return $user;
        if (($redirect = self::guard($request, 'delete proposal product', self::REDIRECT_INDEX)) !== true) return $redirect;

        DB::beginTransaction();
        try {
            $product = ProposalProduct::findOrFail($id);
            if ($product->created_by !== $user?->creatorId())
                return defaultPermissionDenial($request, new \Exception('owner'), $action, route(self::REDIRECT_INDEX));

            $product->delete();
            DB::commit();
            return redirect()->route(self::REDIRECT_INDEX)
                ->with('success', __('Proposal Product deleted.'));
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error("$action error", ['error' => $e->getMessage()]);
            return defaultUndefinedException($request, $e, $action, route(self::REDIRECT_INDEX));
        }
    }
}
