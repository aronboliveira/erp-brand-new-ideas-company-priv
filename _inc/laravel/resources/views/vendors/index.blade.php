@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        StacksConstants,
        ViewClassNamesConstants,
        YieldingConstants,
    };
    use Illuminate\Support\Facades\{Auth, Crypt, Route};
    $user = Auth::user();
    $profile = asset(Storage::url('uploads/avatar/'));
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@push(StacksConstants::ADM_SCR_PG)
    <script>
        $(document).on('click', '#billing_data', function() {
            $("[name='shipping_name']").val($("[name='billing_name']").val());
            $("[name='shipping_country']").val($("[name='billing_country']").val());
            $("[name='shipping_state']").val($("[name='billing_state']").val());
            $("[name='shipping_city']").val($("[name='billing_city']").val());
            $("[name='shipping_phone']").val($("[name='billing_phone']").val());
            $("[name='shipping_zip']").val($("[name='billing_zip']").val());
            $("[name='shipping_address']").val($("[name='billing_address']").val());
        })
    </script>
@endpush
@section(YieldingConstants::ADM_PG_TTL)
    {{ __('Manage Vendors') }}
@endsection
@section(YieldingConstants::ADM_BDC)
    <li class="breadcrumb-item">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
        {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="breadcrumb-item">{{__('Vendor')}}</li>
@endsection
@section(YieldingConstants::ADM_ACT_BTN)
    <div class="float-end">
        <a href="#" class="btn btn-sm btn-primary" data-url="{{ route('vendor.file.import') }}" data-ajax-popup="true" data-bs-toggle="tooltip"
           title="{{ __('Import') }}">
            <i class="ti ti-file-import"></i>
        </a>
        <a href="{{ route('vendor.export') }}" class="btn btn-sm btn-primary" data-bs-toggle="tooltip" title="{{ __('Export') }}">
            <i class="ti ti-file-export"></i>
        </a>
        @can('create vendor')
            <a href="#" data-size="lg" data-url="{{ route('vendor.create') }}" data-ajax-popup="true" data-title="{{__('Create New Vendor')}}" data-bs-toggle="tooltip" title="{{ __('Create') }}" class="btn btn-sm btn-primary">
                <i class="ti ti-plus"></i>
            </a>
        @endcan
    </div>
@endsection
@section(YieldingConstants::ADM_CTT)
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        <table class="table datatable">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>{{ __('Name') }}</th>
                                    <th>{{ __('Contact') }}</th>
                                    <th>{{ __('Email') }}</th>
                                    <th>{{ __('Balance') }}</th>
                                    <th>{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($vendors as $k => $Vendor)
                                    <tr class="cust_tr" id="vend_detail">
                                        <td class="Id">
                                            @can('show vendor')
                                                <a href="{{ route('vendor.show', Crypt::encrypt($Vendor['id'])) }}" class="btn btn-outline-primary">
                                                    {{ $user?->vendorNumberFormat($Vendor['vendor_id']) }}
                                                </a>
                                            @else
                                                <a href="#" class="btn btn-outline-primary"> {{ $user?->vendorNumberFormat($Vendor['vendor_id']) }}
                                                </a>
                                            @endcan
                                        </td>
                                        <td>{{ $Vendor['name'] }}</td>
                                        <td>{{ $Vendor['contact'] }}</td>
                                        <td>{{ $Vendor['email'] }}</td>
                                        <td>{{ $user?->priceFormat($Vendor['balance']) }}</td>
                                        <td class="Action">
                                            <span>
                                                    @if ($Vendor['is_active'] == 0)
                                                        <i class="fa fa-lock" title="Inactive"></i>
                                                    @else
                                                        @can('show vendor')
                                                            <div class="action-btn bg-info ms-2">
                                                                <a href="{{ route('vendor.show', Crypt::encrypt($Vendor['id'])) }}"
                                                                    class="mx-3 btn btn-sm align-items-center" data-bs-toggle="tooltip"
                                                                    title="{{ __('View') }}">
                                                                    <i class="ti ti-eye text-white"></i>
                                                                </a>
                                                            </div>
                                                        @endcan
                                                        @can('edit vendor')
                                                            <div class="action-btn bg-primary ms-2">
                                                                <a href="#" class="mx-3 btn btn-sm align-items-center" data-size="lg"
                                                                data-title="{{__('Edit Vendor')}}"
                                                                    data-url="{{ route('vendor.edit', $Vendor['id']) }}"
                                                                    data-ajax-popup="true" title="{{ __('Edit') }}"
                                                                    data-bs-toggle="tooltip" data-original-title="{{ __('Edit') }}">
                                                                    <i class="{{ ViewClassNamesConstants::TI_PC_WT }}"></i>
                                                                </a>
                                                            </div>
                                                        @endcan
                                                        @can('delete vendor')
                                                            <div class="action-btn bg-danger ms-2">
                                                                {!! Collective\Html\FormFacade::open(['method' => 'DELETE', 'route' => ['vendor.destroy', $Vendor['id']], 'id' => 'delete-form-' . $Vendor['id']]) !!}
                                                                    <a href="#" class="mx-3 btn btn-sm align-items-center bs-pass-para" data-bs-toggle="tooltip"
                                                                           data-original-title="{{ __('Delete') }}" title="{{ __('Delete') }}"
                                                                            data-confirm="{{ __(Utility::fetchLinkMessage($lang, 'generics', 'are_you_sure') ?? 'Are You Sure?') }}|{{ __(Utility::fetchLinkMessage($lang, 'generics', 'irreversible_action') ?? 'This action can not be undone. Do you want to continue?') }}"
                                                                           data-confirm-yes="document.getElementById('delete-form-{{ $Vendor['id'] }}').submit();">
                                                                        <i class="ti ti-trash text-white"></i>
                                                                    </a>
                                                                {!! Collective\Html\FormFacade::close() !!}
                                                            </div>
                                                        @endcan
                                                @endif
                                            </span>
                                        </td>
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
