@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        StacksConstants,
        YieldingConstants,
        ViewsConstants,
        ViewClassNamesConstants as VC
    };
    use App\Models\Utility;
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\Str;
    $lang = Utility::fetchUserLang();
@endphp

@extends(ExtendingLayoutsConstants::ADM)

@section(YieldingConstants::ADM_PG_TTL)
    {{ __('Manage Product Stock') }}
@endsection

@push(StacksConstants::ADM_SCR_PG)
@endpush

@section(YieldingConstants::ADM_BDC)
    <li class="breadcrumb-item">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
           {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="breadcrumb-item">{{ __('Product Stock') }}</li>
@endsection

@section(YieldingConstants::ADM_ACT_BTN)
@endsection

@section(YieldingConstants::ADM_CTT)
    <div class="{{ VC::RW }}">
        <div class="col-xl-12">
            <div class="{{ VC::CD }}">
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        <table class="{{ VC::TB }} datatable">
                            <thead>
                                <tr>
                                    <th>{{ __('Name') }}</th>
                                    <th>{{ __('Sku') }}</th>
                                    <th>{{ __('Current Quantity') }}</th>
                                    <th>{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if(is_array($productServices) && count($productServices) || $productServices instanceof \Illuminate\Database\Eloquent\Collection && $productServices->isNotEmpty())
                                    @foreach ($productServices as $productService)
                                        <tr class="font-style">
                                            <td>{{ !empty($productService->name) ? $productService->name : __('No name available') }}</td>
                                            <td>{{ !empty($productService->sku) ? $productService->sku : __('No SKU available') }}</td>
                                            <td>{{ !empty($productService->quantity) ? $productService->quantity : __('No quantity available') }}</td>
                                            <td class="Action">
                                                <div class="{{ VC::ACT_BTN_INF }}">
                                                    @php
                                                        $productStockEditRouteNameBase    = ViewsConstants::PRD_STK . '.edit';
                                                        $productStockEditKebabName        = Str::kebab($productStockEditRouteNameBase);
                                                        $productStockEditResolvedName     = Route::has($productStockEditRouteNameBase)
                                                            ? $productStockEditRouteNameBase
                                                            : (Route::has($productStockEditKebabName) ? $productStockEditKebabName : null);
                                                        $productStockEditUrl              = $productStockEditResolvedName
                                                            ? route($productStockEditResolvedName, $productService->id)
                                                            : '#';
                                                        $productStockEditGuardMsg         = Utility::fetchLinkMessage(
                                                            $lang,
                                                            ViewsConstants::PRD_STK,
                                                            'product_stock_edit_route_unavailable'
                                                        ) ?? 'Product stock edit route is unavailable. Please contact technical support or your domain administrator.';
                                                        $productStockEditBtnId            = 'product-stock-edit-btn-' . $productService->id;
                                                    @endphp
                                                    <a
                                                    id="{{ $productStockEditBtnId }}"
                                                    href="{{ $productStockEditUrl }}"
                                                    data-url="{{ $productStockEditUrl }}"
                                                    data-guard-msg="{{ $productStockEditGuardMsg }}"
                                                    data-size="md"
                                                    class="{{ VC::BT_SM_FL_CT }}"
                                                    data-ajax-popup="true"
                                                    data-size="xl"
                                                    data-bs-toggle="tooltip"
                                                    title="{{ __('Update Quantity') }}"
                                                    >
                                                    <i class="{{ VC::TI_PLS }} {{ VC::TXT_WT }}"></i>
                                                    </a>
                                                    @push(StacksConstants::ADM_SCR_PG)
                                                        <script defer>
                                                            (() => {
                                                                const btn = document.getElementById('{{ $productStockEditBtnId }}');
                                                                if (!btn || btn.getAttribute('data-listener-active') === 'true') return;
                                                                btn.setAttribute('data-listener-active', 'true');
                                                                btn.addEventListener('click', e => {
                                                                    try {
                                                                        const url = btn.getAttribute('data-url') ?? '#';
                                                                        if (url !== '#') return;
                                                                        e.preventDefault();
                                                                        const msg = btn.getAttribute('data-guard-msg') ?? '# ERROR';
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
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="2">{{ __('No Product services found') }}</td>
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
