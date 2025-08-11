@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        StacksConstants,
        YieldingConstants
    };
    use Illuminate\Support\Facades\Route;
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@section(YieldingConstants::ADM_PG_TTL)
    {{__('Warehouse Stock Details')}}
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
    <li class="breadcrumb-item">{{__('Warehouse Stock Details')}}</li>
@endsection
@section(YieldingConstants::ADM_ACT_BTN)
@endsection
@section(YieldingConstants::ADM_CTT)
    <div class="row">
        <div class="col-xl-12">
            <div class="card">
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        <table class="table datatable">
                            <thead>
                            <tr>
                                <th>{{ __('Product') }}</th>
                                <th>{{ __('Quantity') }}</th>
                            </tr>
                            </thead>
                            <tbody>

                            @foreach ($warehouse as $warehouses)
                                <tr class="font-style">
                                    @if(!empty($warehouses->product))
                                        <td>{{ !empty($warehouses->product)? $warehouses->product->name:'' }}</td>
                                        <td>{{ $warehouses->quantity }}</td>
                                    @endif
                                </tr>
                            @endforeach

                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

