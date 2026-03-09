@php
    try {
} catch (\Throwable $e) {
        \Log::error('warehouses/show — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    }
@endphp

@extends(ExtendingLayoutsConstants::ADM)

@section(YieldingConstants::ADM_PG_TTL)
    {{ __('Warehouse Stock Details') }}
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
    <li class="{{ VC::BCI }}">{{ __('Warehouse Stock Details') }}</li>
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
                                    <th>{{ __('Product') }}</th>
                                    <th>{{ __('Quantity') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if(Utility::isFilled($warehouse) ?? [])
                                    @foreach ($warehouse as $warehouses)
                                        <tr class="font-style">
                                            @if(!empty($warehouses->product))
                                                <td>{{ !empty($warehouses->product) ? $warehouses->product->name : __('No name available') }}</td>
                                                <td>{{ $warehouses->quantity ?? __('No quantity available') }}</td>
                                            @endif
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="2">{{ __('No data available') }}</td>
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
