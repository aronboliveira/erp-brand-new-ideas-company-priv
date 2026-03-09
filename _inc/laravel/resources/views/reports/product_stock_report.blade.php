@php
    try {
$lang = Utility::fetchUserLang();
    } catch (\Throwable $e) {
        \Log::error('reports/product_stock_report — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    }
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@section(YieldingConstants::ADM_PG_TTL)
    {{__('Product Stock')}}
@endsection

@section(YieldingConstants::ADM_BDC)
    <li class="{{ VC::BCI }}">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
        {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="{{ VC::BCI }}">{{__('Product Stock')}}</li>
@endsection

{{-- <a href="#" class="{{ VC::BT_SM_PM }}" onclick="saveAsPDF()"data-bs-toggle="tooltip" title="{{__('Download')}}" data-original-title="{{__('Download')}}">
    <span class="btn-inner--icon"><i class="{{ VC::TI_DWN }}"></i></span>
</a> --}}
@section(YieldingConstants::ADM_ACT_BTN)
    <div class="{{ VC::FEND }}">
        @php
            try {
                $exportBase = VW::PRD_STK.'.export';
                $exportKebab = Str::kebab($exportBase);
                $exportResolved = Route::has($exportBase) ? $exportBase : (Route::has($exportKebab) ? $exportKebab : null);
                $exportUrl = $exportResolved ? route($exportResolved) : '#';
                $langValue = isset($lang) ? $lang : Utility::fetchUserLang();
                $exportGuardMsg = Utility::fetchLinkMessage($langValue, VW::PRD_STK, 'export_product_stock_route_unavailable') ?? 'Export product stock route is unavailable. Please contact technical support or your domain administrator.';
                $anchorId = 'product-stock-export-'.uniqid();
            } catch (\Throwable $e) {
                \Log::error('reports/product_stock_report — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
            }
@endphp
        <a id="{{ $anchorId }}"
        href="{{ $exportUrl }}"
        class="{{ VC::BT_SM_PM }}"
        data-url="{{ $exportUrl }}"
        data-guard-msg="{{ base64_encode($exportGuardMsg) }}"
        data-sv-localized="true"
        data-bs-toggle="tooltip"
        title="{{ __('Export') }}">
            <i class="{{ VC::TI_EXP }}"></i>
        </a>
        @push(StacksConstants::ADM_SCR_PG)
            <script defer src="{{ asset('assets/js/routes/reports/products/stocks/export.js') }}">
            </script>
        @endpush
    </div>
@endsection

@section(YieldingConstants::ADM_CTT)
    <div class="{{ VC::RW }}">
        <div class="{{ VC::CM12 }}">
            <div class="{{ VC::CD }}">
                <div class="{{ VC::CD_BD_TB_BD }}">
                    <div class="{{ VC::TB_RSP }}">
                        <table class="{{ VC::TB }} datatable">
                            <thead>
                                <tr>
                                    <th>{{ __('Date') }}</th>
                                    <th>{{ __('Product Name') }}</th>
                                    <th>{{ __('Quantity') }}</th>
                                    <th>{{ __('Type') }}</th>
                                    <th>{{ __('Description') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    try {
                                        $stockTypeClasses = [
                                            'manually' => 'bg-secondary',
                                            'invoice'  => 'bg-warning',
                                            'bill'     => 'bg-primary',
                                            'purchase' => 'bg-danger',
                                            'pos'      => 'bg-info',
                                        ];
                                    } catch (\Throwable $e) {
                                        \Log::error('reports/product_stock_report — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                    }
@endphp
                                @forelse ($stocks as $stock)
                                    @php
                                        $typeKey = strtolower((string) $stock->type);
                                        $bgClass = $stockTypeClasses[$typeKey] ?? null;
@endphp
                                    <tr>
                                        <td class="font-style">
                                            {{ !empty($stock->created_at) ? $stock->created_at->format('d M Y') : __('Date not available') }}
                                        </td>
                                        <td>
                                            {{ (!empty($stock->product) && !empty($stock->product->name)) ? $stock->product->name : __('No product name available') }}
                                        </td>
                                        <td class="font-style">
                                            {{ (isset($stock->quantity) && $stock->quantity !== '') ? $stock->quantity : __('Quantity not available') }}
                                        </td>
                                        <td>
                                            @if($bgClass)
                                                <span class="status_badge {{ VC::BDG }} {{ $bgClass }} p-2 {{ VC::PX3 }} rounded">
                                                    {{ !empty($stock->type) ? ucfirst($stock->type) : __('Type not available') }}
                                                </span>
                                            @else
                                                <span class="status_badge {{ VC::BDG }} p-2 {{ VC::PX3 }} rounded">
                                                    {{ __('Type not available') }}
                                                </span>
                                            @endif
                                        </td>
                                        <td class="font-style">
                                            {{ isset($stock->description) && $stock->description !== '' ? $stock->description : __('No description available') }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="{{ VC::TXCT }}">{{ __('No stock records available') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
