<?php

namespace App\Http\Controllers\Products;

use App\Config\Constants\{
    DatabaseConstants as DC,
    PermissionsConstants as PMC,
    ViewsConstants as VW,
    ViewClassNamesConstants as VC
};
use App\Http\Controllers\Abstracts\Controller;
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
    DB,
    Validator
};
use Illuminate\View\View;
use function App\Http\Controllers\Helpers\{defaultPermissionDenial, defaultUndefinedException};

final class ProductServiceCategoryController extends Controller
{
    use ChecksLogin, ChecksPermissions;

    private const REDIRECT_INDEX = VW::PRD_SV_CAT . '.index';

    public function index(Request $request): View|RedirectResponse
    {
        $cls = __CLASS__;
        $fn = __FUNCTION__;
        $action = "$cls::$fn";
        $view = VW::PRD_SV_CAT . '.' . $fn;

        return $this->measureProfile($action, function () use ($request, $view) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;
            if (($c = self::guard($request, PMC::MNG_CT_CAT, self::REDIRECT_INDEX)) !== true) return $c;
            $categories = ProductServiceCategory::where(DC::COL_TABLE_CREATOR, $user?->creatorId())->get();
            return view($view, compact('categories'));
        });
    }

    public function create(Request $request): View|RedirectResponse
    {
        $cls = __CLASS__;
        $fn = __FUNCTION__;
        $action = "$cls::$fn";
        $view = VW::PRD_SV_CAT . '.' . $fn;

        return $this->measureProfile($action, function () use ($request, $view) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;
            if (($c = self::guard($request, 'create constant category', self::REDIRECT_INDEX)) !== true) return $c;
            $types = ['' => __('Select Category Type')] + ProductServiceCategory::$catTypes;
            $chartAccounts = ChartOfAccount::select(DB::raw('CONCAT(code," - ",name) AS code_name'), 'id')
                ->where(DC::COL_TABLE_CREATOR, $user?->creatorId())
                ->pluck('code_name', 'id')
                ->prepend(__('Select Account'), '');
            return view($view, compact('types', 'chartAccounts'));
        });
    }

    public function store(Request $request): RedirectResponse
    {
        $cls = __CLASS__;
        $fn = __FUNCTION__;
        $action = "$cls::$fn";

        return $this->measureProfile($action, function () use ($request) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;
            if (($c = self::guard($request, 'create constant category', self::REDIRECT_INDEX)) !== true) return $c;
            $validator = Validator::make($request->all(), [
                'name'  => 'required|max:200',
                'type'  => 'required',
                'color' => 'required',
            ]);
            if ($validator->fails()) {
                return redirect()->back()->with('error', $validator->errors()->first());
            }
            $category = new ProductServiceCategory();
            $category->name = $request->input('name');
            $category->type = $request->input('type');
            $category->color = $request->input('color');
            $category->chart_account_id = $request->input('chart_account', 0);
            $category->created_by = $user?->creatorId();
            $category->save();
            return redirect()->route(self::REDIRECT_INDEX)->with('success', __('Category successfully created.'));
        });
    }

    public function edit(Request $request, int|string $id): View|RedirectResponse
    {
        $cls = __CLASS__;
        $fn = __FUNCTION__;
        $action = "$cls::$fn";
        $view = VW::PRD_SV_CAT . '.' . $fn;

        return $this->measureProfile($action, function () use ($request, $id, $view) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;
            if (($c = self::guard($request, 'edit constant category', self::REDIRECT_INDEX)) !== true) return $c;
            $category = ProductServiceCategory::findOrFail($id);
            if ($category->created_by !== $user?->creatorId()) return redirect()->back()->with('error', __('Permission denied.'));
            $types = ProductServiceCategory::$catTypes;
            return view($view, compact('category', 'types'));
        });
    }

    public function update(Request $request, int|string $id): RedirectResponse
    {
        $cls = __CLASS__;
        $fn = __FUNCTION__;
        $action = "$cls::$fn";

        return $this->measureProfile($action, function () use ($request, $id) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;
            if (($c = self::guard($request, 'edit constant category', self::REDIRECT_INDEX)) !== true) return $c;
            $category = ProductServiceCategory::findOrFail($id);
            if ($category->created_by !== $user?->creatorId()) return redirect()->back()->with('error', __('Permission denied.'));
            $validator = Validator::make($request->all(), [
                'name'  => 'required|max:200',
                'type'  => 'required',
                'color' => 'required',
            ]);
            if ($validator->fails()) {
                return redirect()->back()->with('error', $validator->errors()->first());
            }
            $category->fill([
                'name'             => $request->input('name'),
                'type'             => $request->input('type'),
                'color'            => $request->input('color'),
                'chart_account_id' => $request->input('chart_account', 0),
            ])->save();
            return redirect()->route(self::REDIRECT_INDEX)->with('success', __('Category successfully updated.'));
        });
    }

    public function destroy(Request $request, int|string $id): RedirectResponse
    {
        $cls = __CLASS__;
        $fn = __FUNCTION__;
        $action = "$cls::$fn";

        return $this->measureProfile($action, function () use ($request, $id) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;
            if (($c = self::guard($request, 'delete constant category', self::REDIRECT_INDEX)) !== true) return $c;
            $category = ProductServiceCategory::findOrFail($id);
            if ($category->created_by !== $user?->creatorId()) return redirect()->back()->with('error', __('Permission denied.'));
            $existsCheck = match ($category->type) {
                0 => ProductService::where('category_id', $id)->exists(),
                1 => Invoice::where('category_id', $id)->exists(),
                default => Bill::where('category_id', $id)->exists(),
            };
            if ($existsCheck) return redirect()->back()->with('error', __('This category is in use.'));
            $category->delete();
            return redirect()->route(self::REDIRECT_INDEX)->with('success', __('Category successfully deleted.'));
        });
    }

    public const GET_PRD_CAT = 'getProductCategories';
    public function getProductCategories(Request $request): Response|RedirectResponse
    {
        $cls = __CLASS__;
        $fn = __FUNCTION__;
        $action = "$cls::$fn";

        return $this->measureProfile($action, function () use ($request) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;
            if (($c = self::guard($request, PMC::MNG_CT_CAT, self::REDIRECT_INDEX)) !== true) return $c;

            $html = '<div class="mb-3 mr-2 zoom-in ">
        <div class="' . VC::CD . ' rounded-10 card-stats mb-0 cat-active overflow-hidden" data-id="0">
            <div class="category-select" data-cat-id="0">
                    <button type="button" class="btn tab-btns btn-primary">'
                . __("All Categories") .
                '</button>
                </div>
            </div>
        </div>';

            foreach (ProductServiceCategory::where(DC::COL_TABLE_CREATOR, $user?->creatorId())->get() as $c) {
                $html .= '<div class="mb-3 mr-2 zoom-in cat-list-btn">
            <div class="' . VC::CD . ' rounded-10 card-stats mb-0 overflow-hidden" data-id="'
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
        });
    }

    public const GET_ACC = 'getAccount';
    public const IDX = 'index';
    public const CRT = 'create';
    public const STR = 'store';
    public const EDT = 'edit';
    public const UPD = 'update';
    public const DEL = 'destroy';

    public function getAccount(Request $request): JsonResponse|RedirectResponse
    {
        $cls = __CLASS__;
        $fn = __FUNCTION__;
        $action = "$cls::$fn";

        return $this->measureProfile($action, function () use ($request) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;
            if (($c = self::guard($request, PMC::MNG_CT_CAT, self::REDIRECT_INDEX)) !== true) return $c;

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
                ? ChartOfAccount::select(DB::raw('CONCAT(code," - ",name) AS code_name'), 'id')
                ->leftJoin('chart_of_account_types', 'chart_of_account_types.id', 'chart_of_accounts.type')
                ->where('chart_of_account_types.name', $typeName)
                ->where('chart_of_accounts.created_by', $user?->creatorId())
                ->pluck('code_name', 'id')
                : [];

            return response()->json($chartAccounts);
        });
    }

    /**
     * Show a single product/service category.
     */
    public function show(Request $request, int|string $id): View|RedirectResponse|JsonResponse
    {
        $cls = __CLASS__;
        $fn = __FUNCTION__;
        $action = "$cls::$fn";
        $view = VW::PRD_SV_CAT . '.' . $fn;
        return $this->measureProfile($action, function () use ($request, $id, $view, $action) {
            if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
            $user = $userOrRedirect;
            if (($c = self::guard($request, PMC::MNG_CT_CAT, self::REDIRECT_INDEX)) !== true) return $c;
            try {
                $category = ProductServiceCategory::where(DC::COL_TABLE_CREATOR, $user?->creatorId())->findOrFail($id);
                if ($request->wantsJson()) return response()->json($category);
                if (!\Illuminate\Support\Facades\View::exists($view)) {
                    return redirect()->route(self::REDIRECT_INDEX)->with('info', __('Category detail view not available.'));
                }
                return view($view, compact('category'));
            } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
                return redirect()->route(self::REDIRECT_INDEX)->with('error', __('Category not found.'));
            } catch (\Throwable $e) {
                return defaultUndefinedException($request, $e, "$action");
            }
        });
    }
}
