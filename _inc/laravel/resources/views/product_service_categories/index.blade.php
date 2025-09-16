@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        StacksConstants,
        ViewsConstants,
        ViewClassNamesConstants as VC,
        YieldingConstants,
    };
    use App\Models\{ProductServiceCategory, Utility};
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\{Collection, Str};
    $lang = Utility::fetchUserLang();
@endphp

@extends(ExtendingLayoutsConstants::ADM)

@section(YieldingConstants::ADM_PG_TTL)
    {{ __('Manage Product-Service & Income-Expense Category') }}
@endsection

@section(YieldingConstants::ADM_BDC)
    <li class="breadcrumb-item">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
           {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="breadcrumb-item">{{ __('Category') }}</li>
@endsection

@section(YieldingConstants::ADM_ACT_BTN)
    <div class="{{ VC::FEND }}">
        @can('create constant category')
            @php
                $routeKey = ViewsConstants::PRD_SV_CAT . '.create';
                $kebabRouteKey = Str::kebab($routeKey);
                $createRouteName = Route::has($routeKey) ? $routeKey : (Route::has($kebabRouteKey) ? $kebabRouteKey : null);
                $createRouteUrl = $createRouteName ? route($createRouteName) : '#';
                $createGuardMsg = Utility::fetchLinkMessage($lang, ViewsConstants::PRD_SV_CAT, 'product_category_create_route_unavailable') ?? 'Product category create route is unavailable. Please contact technical support or your domain administrator.';
            @endphp
            <a
            id="product-category-create-btn"
            href="{{ $createRouteUrl }}"
            data-url="{{ $createRouteUrl }}"
            data-guard-msg="{{ $createGuardMsg }}"
            data-size="md"
            data-ajax-popup="true"
            data-bs-toggle="tooltip"
            title="{{ __('Create') }}"
            data-title="{{ __('Create New Category') }}"
            class="{{ VC::BT_SM_PM }}"
            >
            <i class="{{ VC::TI_PLS }}"></i>
            </a>
            @push(StacksConstants::ADM_SCR_PG)
                <script defer src="{{ asset('assets/js/routes/product/services/categories/create.js') }}"></script>
            @endpush
        @endcan
    </div>
@endsection

@section(YieldingConstants::ADM_CTT)
    <div class="row">
        <div class="col-3">
            @include('layouts.account_setup')
        </div>
        <div class="col-9">
            <div class="card">
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        <table class="table datatable">
                            <thead>
                                <tr>
                                    <th>{{ __('Category') }}</th>
                                    <th>{{ __('Type') }}</th>
                                    <th>{{ __('Account') }}</th>
                                    <th width="10%">{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if((is_array($categories) && count($categories)) || ($categories instanceof Collection && $categories->isNotEmpty()))
                                    @foreach ($categories as $category)
                                        <tr>
                                            <td class="font-style">{{ !empty($category->name) ? $category->name : __('No name available for category') }}</td>
                                            <td class="font-style">
                                                {{ __(!empty(ProductServiceCategory::$catTypes) && !empty($category->type) ?ProductServiceCategory::$catTypes[$category->type] : __('No type available for category')) }}
                                            </td>
                                            <td>{{ !empty($category->chartAccount?->name) ? $category->chartAccount->name : __('No account chart name available') }}</td>
                                            <td class="Action">
                                                <span>
                                                    @can('edit constant category')
                                                        <div class="{{ VC::ACT_BTN_PRIM }}">
                                                            @php
                                                                $routeKey = ViewsConstants::PRD_SV_CAT . '.edit';
                                                                $kebabRouteKey = Str::kebab($routeKey);
                                                                $editRouteName = Route::has($routeKey) ? $routeKey : (Route::has($kebabRouteKey) ? $kebabRouteKey : null);
                                                                $editRouteUrl = $editRouteName ? route($editRouteName, $category->id) : '#';
                                                                $editGuardMsg = Utility::fetchLinkMessage($lang, ViewsConstants::PRD_SV_CAT, 'product_category_edit_route_unavailable') ?? 'Product category edit route is unavailable. Please contact technical support or your domain administrator.';
                                                            @endphp
                                                            <a
                                                            id="product-category-edit-btn-{{ $category->id }}"
                                                            href="{{ $editRouteUrl }}"
                                                            data-url="{{ $editRouteUrl }}"
                                                            data-guard-msg="{{ $editGuardMsg }}"
                                                            class="{{ VC::BT_SM_CT }}"
                                                            data-ajax-popup="true"
                                                            data-title="{{ __('Edit Product Category') }}"
                                                            data-bs-toggle="tooltip"
                                                            title="{{ __('Edit') }}"
                                                            >
                                                            <i class="{{ VC::TI_PC_WT }}"></i>
                                                            </a>
                                                            @push(StacksConstants::ADM_SCR_PG)
                                                                <script defer>
                                                                    (() => {
                                                                        const btn = document.getElementById('product-category-edit-btn-{{ $category->id }}');
                                                                        if (!btn || btn.getAttribute('data-listener-active') === 'true') return;
                                                                        btn.setAttribute('data-listener-active', 'true');
                                                                        btn.addEventListener('click', e => {
                                                                            try {
                                                                                const url = btn.getAttribute('data-url') || '#';
                                                                                if (url !== '#') return;
                                                                                e.preventDefault();
                                                                                const msg = btn.getAttribute('data-guard-msg') || '# ERROR';
                                                                                const bs = document.querySelector('link[href*="bootstrap"]') && window.bootstrap;
                                                                                let container = document.getElementById('toast-container');
                                                                                if (!container) {
                                                                                    container = document.createElement('div');
                                                                                    container.id = 'toast-container';
                                                                                    document.body.appendChild(container);
                                                                                }
                                                                                if (bs) {
                                                                                    const toast = document.createElement('div');
                                                                                    toast.className = 'toast';
                                                                                    toast.setAttribute('role','alert');
                                                                                    toast.setAttribute('aria-live','assertive');
                                                                                    toast.setAttribute('aria-atomic','true');
                                                                                    const body = document.createElement('div');
                                                                                    body.className = 'toast-body';
                                                                                    body.textContent = msg;
                                                                                    toast.appendChild(body);
                                                                                    container.appendChild(toast);
                                                                                    bootstrap.Toast.getOrCreateInstance(toast).show();
                                                                                } else {
                                                                                    alert(msg);
                                                                                }
                                                                                btn.setAttribute('data-failed-route', 'true');
                                                                            } catch (error) {}
                                                                        });
                                                                    })();
                                                                </script>
                                                            @endpush
                                                        </div>
                                                    @endcan
                                                    @can('delete constant category')
                                                        @php
                                                            $productCategoryDestroyRouteName     = ViewsConstants::PRD_SV_CAT . '.destroy';
                                                            $productCategoryDestroyKebabRoute    = Str::kebab($productCategoryDestroyRouteName);
                                                            $productCategoryDestroyResolvedName  = Route::has($productCategoryDestroyRouteName)
                                                                ? $productCategoryDestroyRouteName
                                                                : (Route::has($productCategoryDestroyKebabRoute) ? $productCategoryDestroyKebabRoute : null);
                                                            $productCategoryDestroyRouteArray    = $productCategoryDestroyResolvedName
                                                                ? [$productCategoryDestroyResolvedName, $category->id]
                                                                : ['#'];
                                                            $productCategoryDestroyUrl           = $productCategoryDestroyResolvedName
                                                                ? route($productCategoryDestroyResolvedName, $category->id)
                                                                : '#';
                                                            $productCategoryDestroyGuardMsg      = Utility::fetchLinkMessage(
                                                                $lang,
                                                                ViewsConstants::PRD_SV_CAT,
                                                                'product_category_destroy_route_unavailable'
                                                            ) ?? 'Product category destroy route is unavailable. Please contact technical support or your domain administrator.';
                                                            $deleteFormId                        = 'delete-form-' . $category->id;
                                                            $deleteBtnId                         = 'delete-product-category-btn-' . $category->id;
                                                        @endphp
                                                        <div class="{{ VC::ACT_BTN_DNG_2 }}">
                                                            {!! Collective\Html\FormFacade::open([
                                                                'method' => 'DELETE',
                                                                'route'  => $productCategoryDestroyRouteArray,
                                                                'id'     => $deleteFormId
                                                            ]) !!}
                                                                <a
                                                                    id="{{ $deleteBtnId }}"
                                                                    href="{{ $productCategoryDestroyUrl }}"
                                                                    data-url="{{ $productCategoryDestroyUrl }}"
                                                                    data-guard-msg="{{ $productCategoryDestroyGuardMsg }}"
                                                                    class="{{ VC::BT_SM_CT_PR }}"
                                                                    data-bs-toggle="tooltip"
                                                                    title="{{ __('Delete') }}"
                                                                    data-confirm="{{ __(Utility::fetchLinkMessage($lang, 'generics', 'are_you_sure') ?? 'Are You Sure?') }}|{{ __(Utility::fetchLinkMessage($lang, 'generics', 'irreversible_action') ?? 'This action can not be undone. Do you want to continue?') }}"
                                                                    data-confirm-yes="document.getElementById('{{ $deleteFormId }}').submit();"
                                                                >
                                                                    <i class="{{ VC::TI_TRS_WT }}"></i>
                                                                </a>
                                                            {!! Collective\Html\FormFacade::close() !!}
                                                        </div>
                                                        @push(StacksConstants::ADM_SCR_PG)
                                                            <script defer>
                                                                (() => {
                                                                    const btn = document.getElementById('{{ $deleteBtnId }}');
                                                                    if (!btn || btn.getAttribute('data-listener-active') === 'true') return;
                                                                    btn.setAttribute('data-listener-active', 'true');
                                                                    btn.addEventListener('click', e => {
                                                                        try {
                                                                            const url = btn.getAttribute('data-url') || '#';
                                                                            if (url !== '#') return;
                                                                            e.preventDefault();
                                                                            const msg = btn.getAttribute('data-guard-msg') || '# ERROR';
                                                                            const hasBootstrap = document.querySelector('link[href*="bootstrap"]') && window.bootstrap;
                                                                            let container = document.getElementById('toast-container');
                                                                            if (!container) {
                                                                                container = document.createElement('div');
                                                                                container.id = 'toast-container';
                                                                                document.body.appendChild(container);
                                                                            }
                                                                            if (hasBootstrap) {
                                                                                const toast = document.createElement('div');
                                                                                toast.className = 'toast';
                                                                                toast.setAttribute('role', 'alert');
                                                                                toast.setAttribute('aria-live', 'assertive');
                                                                                toast.setAttribute('aria-atomic', 'true');
                                                                                const body = document.createElement('div');
                                                                                body.className = 'toast-body';
                                                                                body.textContent = msg;
                                                                                toast.appendChild(body);
                                                                                container.appendChild(toast);
                                                                                bootstrap.Toast.getOrCreateInstance(toast).show();
                                                                            } else {
                                                                                alert(msg);
                                                                            }
                                                                            btn.setAttribute('data-failed-route', 'true');
                                                                        } catch (err) {}
                                                                    });
                                                                })();
                                                            </script>
                                                        @endpush
                                                    @endcan
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="4" class="text-center text-muted">{{ __('No categories found.') }}</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
