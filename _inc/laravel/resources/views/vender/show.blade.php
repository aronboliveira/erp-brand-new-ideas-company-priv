@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        YieldingConstants,
        StacksConstants,
        ViewsConstants
    };
    use Illuminate\Support\Facades\{Auth, Route};
    $user = Auth::user();
@endphp

@extends(ExtendingLayoutsConstants::ADM)

@push(StacksConstants::ADM_SCR_PG)
@endpush

@section(YieldingConstants::ADM_PG_TTL)
    {{ __('Manage Vendor-Detail') }}
@endsection

@section(YieldingConstants::ADM_BDC)
    <li class="breadcrumb-item">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
        {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="breadcrumb-item"><a href="{{ route(ViewsConstants::VND . '.index') }}">{{ __('Vendor') }}</a></li>
    <li class="breadcrumb-item">{{ $vendor->name }}</li>
@endsection

@section(YieldingConstants::ADM_ACT_BTN)
    <div class="float-end">
        @can('create bill')
            <a href="{{ route(ViewsConstants::BIL . '.create', $vendor->id) }}" class="btn btn-sm btn-primary">
                {{ __('Create Bill') }}
            </a>
        @endcan

        @can('edit vendor')
            <a href="#"
               class="btn btn-sm btn-primary"
               data-size="xl"
               data-url="{{ route(ViewsConstants::VND . '.edit', $vendor->id) }}"
               data-ajax-popup="true"
               title="{{ __('Edit') }}">
                <i class="ti ti-pencil"></i>
            </a>
        @endcan

        @can('delete vendor')
            {!! Collective\Html\FormFacade::open([
                    'method' => 'DELETE',
                    'route'  => [ViewsConstants::VND . '.destroy', $vendor->id],
                    'class'  => 'delete-form-btn',
                    'id'     => 'delete-form-' . $vendor->id
                ]) !!}
            <a href="#"
               class="btn btn-sm btn-danger bs-pass-para"
               data-confirm="{{ __('Are You Sure?') . '|' . __('This action can not be undone. Do you want to continue?') }}"
               data-confirm-yes="document.getElementById('delete-form-{{ $vendor->id }}').submit();">
                <i class="ti ti-trash text-white"></i>
            </a>
            {!! Collective\Html\FormFacade::close() !!}
        @endcan
    </div>
@endsection

@section(YieldingConstants::ADM_CTT)
    <div class="row">
        {{-- Vendor Info --}}
        <div class="col-md-4">
            <div class="card pb-0 customer-detail-box vendor_card">
                <div class="card-body">
                    <h5 class="card-title">{{ __('Vendor Info') }}</h5>
                    <p class="card-text">{{ $vendor->name }}</p>
                    <p class="card-text">{{ $vendor->email }}</p>
                    <p class="card-text">{{ $vendor->contact }}</p>
                </div>
            </div>
        </div>
        {{-- Billing Info --}}
        <div class="col-md-4">
            <div class="card pb-0 customer-detail-box vendor_card">
                <div class="card-body">
                    <h5 class="card-title">{{ __('Billing Info') }}</h5>
                    <p class="card-text">{{ $vendor->billing_name }}</p>
                    <p class="card-text">{{ $vendor->billing_address }}</p>
                    <p class="card-text">
                        {{ $vendor->billing_city.', '.$vendor->billing_state.', '.$vendor->billing_zip }}
                    </p>
                    <p class="card-text">{{ $vendor->billing_country }}</p>
                    <p class="card-text">{{ $vendor->billing_phone }}</p>
                </div>
            </div>
        </div>
        {{-- Shipping Info --}}
        <div class="col-md-4">
            <div class="card pb-0 customer-detail-box vendor_card">
                <div class="card-body">
                    <h5 class="card-title">{{ __('Shipping Info') }}</h5>
                    <p class="card-text">{{ $vendor->shipping_name }}</p>
                    <p class="card-text">{{ $vendor->shipping_address }}</p>
                    <p class="card-text">
                        {{ $vendor->shipping_city.', '.$vendor->shipping_state.', '.$vendor->shipping_zip }}
                    </p>
                    <p class="card-text">{{ $vendor->shipping_country }}</p>
                    <p class="card-text">{{ $vendor->shipping_phone }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Bills Table --}}
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body table-border-style">
                    <h5 class="mb-4">{{ __('Bills') }}</h5>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>{{ __('Bill') }}</th>
                                    <th>{{ __('Bill Date') }}</th>
                                    <th>{{ __('Due Date') }}</th>
                                    <th>{{ __('Due Amount') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    @if(Gate::check('edit bill') || Gate::check('delete bill') || Gate::check('show bill'))
                                        <th>{{ __('Action') }}</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($vendor->vendorBill($vendor->id) as $bill)
                                    <tr>
                                        <td>
                                            <a href="{{ route(ViewsConstants::BIL . '.show', Crypt::encrypt($bill->id)) }}"
                                               class="btn btn-outline-primary">
                                                {{ Auth::user()->billNumberFormat($bill->bill_id) }}
                                            </a>
                                        </td>
                                        <td>{{ Auth::user()->dateFormat($bill->bill_date) }}</td>
                                        <td>
                                            @if($bill->due_date < now()->toDateString())
                                                <span class="text-danger">
                                                    {{ Auth::user()->dateFormat($bill->due_date) }}
                                                </span>
                                            @else
                                                {{ Auth::user()->dateFormat($bill->due_date) }}
                                            @endif
                                        </td>
                                        <td>{{ $user?->priceFormat($bill->getDue()) }}</td>
                                        <td>
                                            @php $status = \App\Models\Invoice::$statuses[$bill->status]; @endphp
                                            @php
                                                $colors = ['primary','warning','danger','info','success'];
                                            @endphp
                                            <span class="badge bg-{{ $colors[$bill->status] }} p-2 px-3 rounded">
                                                {{ __($status) }}
                                            </span>
                                        </td>
                                        @if(Gate::check('edit bill') || Gate::check('delete bill') || Gate::check('show bill'))
                                            <td>
                                                @can('duplicate bill')
                                                    {!! Collective\Html\FormFacade::open([
                                                            'method' => 'GET',
                                                            'route'  => [ViewsConstants::BIL . '.duplicate', $bill->id],
                                                            'id'     => 'duplicate-form-'.$bill->id
                                                        ]) !!}
                                                    {!! Collective\Html\FormFacade::close() !!}
                                                    <a href="#"
                                                       class="me-2"
                                                       data-confirm-yes="document.getElementById('duplicate-form-{{ $bill->id }}').submit();">
                                                        <i class="ti ti-copy text-success"></i>
                                                    </a>
                                                @endcan

                                                @can('show bill')
                                                    <a href="{{ route(ViewsConstants::BIL . '.show', Crypt::encrypt($bill->id)) }}"
                                                       class="me-2">
                                                        <i class="ti ti-eye text-info"></i>
                                                    </a>
                                                @endcan

                                                @can('edit bill')
                                                    <a href="{{ route(ViewsConstants::BIL . '.edit', Crypt::encrypt($bill->id)) }}"
                                                       class="me-2">
                                                        <i class="ti ti-pencil text-primary"></i>
                                                    </a>
                                                @endcan

                                                @can('delete bill')
                                                    {!! Collective\Html\FormFacade::open([
                                                            'method' => 'DELETE',
                                                            'route'  => [ViewsConstants::BIL . '.destroy', $bill->id],
                                                            'id'     => 'delete-form-'.$bill->id
                                                        ]) !!}
                                                    {!! Collective\Html\FormFacade::close() !!}
                                                    <a href="#"
                                                       data-confirm-yes="document.getElementById('delete-form-{{ $bill->id }}').submit();">
                                                        <i class="ti ti-trash text-danger"></i>
                                                    </a>
                                                @endcan
                                            </td>
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
