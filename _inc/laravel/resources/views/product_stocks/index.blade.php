@php
    try {
$lang = Utility::fetchUserLang();
    } catch (\Throwable $e) {
        \Log::error('product_stocks/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    }
@endphp

@extends(ExtendingLayoutsConstants::ADM)

@section(YieldingConstants::ADM_PG_TTL)
    {{ __('Manage Product Stock') }}
@endsection

@push(StacksConstants::ADM_SCR_PG)
@endpush

@section(YieldingConstants::ADM_BDC)
    <li class="{{ VC::BCI }}">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
           {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="{{ VC::BCI }}">{{ __('Product Stock') }}</li>
@endsection

@section(YieldingConstants::ADM_ACT_BTN)
@endsection

@section(YieldingConstants::ADM_CTT)
    <div class="{{ VC::RW }}">
        <div class="{{ VC::CXL12 }}">
            <div class="{{ VC::CD }}">
                <div class="{{ VC::CD_BD_TB_BD }}">
                    <div class="{{ VC::TB_RSP }}">
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
                                @if(Utility::isFilled($productServices) ?? [])
                                    @foreach ($productServices as $productService)
                                        <tr class="font-style">
                                            <td>{{ !empty($productService->name) ? $productService->name : __('No name available') }}</td>
                                            <td>{{ !empty($productService->sku) ? $productService->sku : __('No SKU available') }}</td>
                                            <td>{{ !empty($productService->quantity) ? $productService->quantity : __('No quantity available') }}</td>
                                            <td class="Action">
                                                <div class="{{ VC::ACT_BTN_INF }}">
                                                    @php
                                                        try {
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
                                                        } catch (\Throwable $e) {
                                                            \Log::error('product_stocks/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                        }
@endphp
                                                    <a
                                                    id="{{ $productStockEditBtnId }}"
                                                    href="{{ $productStockEditUrl }}"
                                                    data-url="{{ $productStockEditUrl }}"
                                                    data-guard-msg="{{ base64_encode($productStockEditGuardMsg) }}"
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
                                                                        (window.RouteGuard?.showToast || (m => alert(m)))(msg);
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
