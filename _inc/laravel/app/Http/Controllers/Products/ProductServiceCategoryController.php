<?php

namespace App\Http\Controllers;

use App\Config\Constants\{
    DatabaseConstants,
    PermissionsConstants,
    ViewsConstants,
    ViewClassNamesConstants
};
use App\Models\{
    Bill,
    ChartOfAccount,
    Invoice,
    ProductService,
    ProductServiceCategory
};
use App\Traits\{ChecksLogin, ChecksPermissions};
use Illuminate\Http\{
    JsonResponse,
    RedirectResponse,
    Request,
    Response
};
use Illuminate\Support\Facades\{
    Auth,
    DB,
    Validator
};
use Illuminate\View\View;

final class ProductServiceCategoryController extends Controller
{
    use ChecksLogin, ChecksPermissions;

    public function index(Request $request): View|RedirectResponse
    {
        $function = __FUNCTION__;
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        ) return $userOrRedirect;
        $user = $userOrRedirect;
        if (
            $c = self::guard(
                $request,
                PermissionsConstants::MNG_CT_CAT,
                ViewsConstants::PRD_SV_CAT . '.' . $function
            )
        ) return $c;
        $categories = ProductServiceCategory::where(
            DatabaseConstants::TABLE_CREATOR,
            $user?->creatorId()
        )->get();
        return view(
            ViewsConstants::PRD_SV_CAT . '.' . $function,
            compact('categories')
        );
    }

    public function create(Request $request): View|RedirectResponse
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        ) return $userOrRedirect;
        $user = $userOrRedirect;
        if (
            $c = self::guard(
                $request,
                'create constant category',
                ViewsConstants::PRD_SV_CAT . '.index'
            )
        ) return $c;
        $types = ['' => __('Select Category Type')]
            + ProductServiceCategory::$catTypes;
        $chartAccounts = ChartOfAccount::select(
            DB::raw('CONCAT(code," - ",name) AS code_name'),
            'id'
        )->where(
            DatabaseConstants::TABLE_CREATOR,
            $user?->creatorId()
        )->pluck('code_name', 'id');
        $chartAccounts->prepend(__('Select Account'), '');
        return view(
            ViewsConstants::PRD_SV_CAT . '.' . __FUNCTION__,
            compact('types', 'chartAccounts')
        );
    }

    public function store(Request $request): RedirectResponse
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        ) return $userOrRedirect;
        $user = $userOrRedirect;
        if (
            $c = self::guard(
                $request,
                'create constant category',
                ViewsConstants::PRD_SV_CAT . '.index'
            )
        ) return $c;
        $validator = Validator::make($request->all(), [
            'name'  => 'required|max:200',
            'type'  => 'required',
            'color' => 'required',
        ]);
        if ($validator->fails()) {
            return redirect()->back()
                ->with('error', $validator->errors()->first());
        }
        $category = new ProductServiceCategory();
        $category->name           = $request->input('name');
        $category->type           = $request->input('type');
        $category->color          = $request->input('color');
        $category->chart_account_id = $request->input('chart_account', 0);
        $category->created_by     = $user?->creatorId();
        $category->save();
        return redirect()
            ->route(ViewsConstants::PRD_SV_CAT . '.index')
            ->with('success', __('Category successfully created.'));
    }

    public function edit(Request $request, int|string $id): View|RedirectResponse
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        ) return $userOrRedirect;
        $user = $userOrRedirect;
        if (
            $c = self::guard(
                $request,
                'edit constant category',
                ViewsConstants::PRD_SV_CAT . '.index'
            )
        ) return $c;
        $category = ProductServiceCategory::findOrFail($id);
        $types   = ProductServiceCategory::$catTypes;
        return view(
            ViewsConstants::PRD_SV_CAT . '.' . __FUNCTION__,
            compact('category', 'types')
        );
    }

    public function update(Request $request, int|string $id): RedirectResponse
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        ) return $userOrRedirect;
        $user = $userOrRedirect;
        if (
            $c = self::guard(
                $request,
                'edit constant category',
                ViewsConstants::PRD_SV_CAT . '.index'
            )
        ) return $c;
        $category = ProductServiceCategory::findOrFail($id);
        if ($category->created_by !== $user?->creatorId())
            return redirect()->back()
                ->with('error', __('Permission denied.'));
        $validator = Validator::make($request->all(), [
            'name'  => 'required|max:200',
            'type'  => 'required',
            'color' => 'required',
        ]);
        if ($validator->fails()) {
            return redirect()->back()
                ->with('error', $validator->errors()->first());
        }
        $category->fill([
            'name'             => $request->input('name'),
            'type'             => $request->input('type'),
            'color'            => $request->input('color'),
            'chart_account_id' => $request->input('chart_account', 0),
        ])->save();
        return redirect()
            ->route(ViewsConstants::PRD_SV_CAT . '.index')
            ->with('success', __('Category successfully updated.'));
    }

    public function destroy(Request $request, int|string $id): RedirectResponse
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        ) return $userOrRedirect;
        $user = $userOrRedirect;
        if (
            $c = self::guard(
                $request,
                'delete constant category',
                ViewsConstants::PRD_SV_CAT . '.index'
            )
        ) return $c;
        $category = ProductServiceCategory::findOrFail($id);
        if ($category->created_by !== $user?->creatorId())
            return redirect()->back()
                ->with('error', __('Permission denied.'));
        $existsCheck = match ($category->type) {
            0 => ProductService::where('category_id', $id)->exists(),
            1 => Invoice::where('category_id', $id)->exists(),
            default => Bill::where('category_id', $id)->exists(),
        };
        if ($existsCheck)
            return redirect()->back()
                ->with('error', __('This category is in use.'));
        $category->delete();
        return redirect()
            ->route(ViewsConstants::PRD_SV_CAT . '.index')
            ->with('success', __('Category successfully deleted.'));
    }

    public const GET_PRD_CAT = 'getProductCategories';
    public function getProductCategories(Request $request): Response|RedirectResponse
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        ) return $userOrRedirect;
        $user = $userOrRedirect;
        if (
            $c = self::guard(
                $request,
                PermissionsConstants::MNG_CT_CAT,
                ViewsConstants::PRD_SV_CAT . '.index'
            )
        ) return $c;
        $html = '<div class="mb-3 mr-2 zoom-in ">
        <div class="' . ViewClassNamesConstants::CD . ' rounded-10 card-stats mb-0 cat-active overflow-hidden" data-id="0">
            <div class="category-select" data-cat-id="0">
                    <button type="button" class="btn tab-btns btn-primary">'
            . __("All Categories") .
            '</button>
                </div>
            </div>
        </div>';
        foreach (
            ProductServiceCategory::where(
                DatabaseConstants::TABLE_CREATOR,
                $user?->creatorId()
            )->get() as $c
        ) {
            $html .= '<div class="mb-3 mr-2 zoom-in cat-list-btn">
            <div class="' . ViewClassNamesConstants::CD . ' rounded-10 card-stats mb-0 overflow-hidden" data-id="'
                . $c->id . '">
            <div class="category-select" data-cat-id="'
                . $c->id . '">
                <button type="button" class="btn tab-btns btn-primary">'
                . $c->name .
                '</button>
            </div>
            </div>
        </div>';
        }
        return response($html);
    }

    public const GET_ACC = 'getAccount';
    public function getAccount(Request $request): JsonResponse|RedirectResponse
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        ) return $userOrRedirect;
        $user = $userOrRedirect;
        if (
            $c = self::guard(
                $request,
                PermissionsConstants::MNG_CT_CAT,
                ViewsConstants::PRD_SV_CAT . '.index'
            )
        ) return $c;
        $map = [
            'income'             => 'Income',
            'expense'            => 'Expenses',
            'asset'              => 'Assets',
            'liability'          => 'Liabilities',
            'equity'             => 'Equity',
            'costs of good sold' => 'Costs of Goods Sold',
        ];
        $typeName = $map[$request->type] ?? null;
        $chartAccounts = $typeName
            ? ChartOfAccount::select(
                DB::raw('CONCAT(code," - ",name) AS code_name'),
                'id'
            )->leftJoin(
                'chart_of_account_types',
                'chart_of_account_types.id',
                'chart_of_accounts.type'
            )->where(
                'chart_of_account_types.name',
                $typeName
            )->where(
                'chart_of_accounts.created_by',
                $user?->creatorId()
            )->pluck('code_name', 'id')
            : [];
        return response()->json($chartAccounts);
    }
}
