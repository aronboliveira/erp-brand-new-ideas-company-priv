@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        LangsConstants,
        StacksConstants,
        ViewsConstants,
        ViewClassNamesConstants,
        YieldingConstants
    };
    use Illuminate\Support\Facades\{Auth, Route};
    use App\Models\Utility;
    use App\Config\Constants\{PermissionsConstants, UsersConstants};
    use Illuminate\Support\Facades\Auth;
    $admin_payment_setting = Utility::getAdminPaymentSetting();
    $user = Auth::user();
    $lang = Utility::fetchUserLang(user: $user);
@endphp
@extends(ExtendingLayoutsConstants::ADM)

@section(YieldingConstants::ADM_PG_TTL)
    {{__('Orders')}}
@endsection

@section(YieldingConstants::ADM_BDC)
    <li class="breadcrumb-item">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
        {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="breadcrumb-item">{{__('Order')}}</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        <table class="table datatable">
                            <thead>
                            <tr>
                                <th>{{__('Order Id')}}</th>
                                <th>{{__('Name')}}</th>
                                <th>{{__('Plan Name')}}</th>
                                <th>{{__('Price')}}</th>
                                <th>{{__('Status')}}</th>
                                <th>{{__('Payment Type')}}</th>
                                <th>{{__('Date')}}</th>
                                <th>{{__('Coupon')}}</th>
                                <th>{{__('Invoice')}}</th>
                                @if(!empty($user[UsersConstants::COL_TP]) && $user[UsersConstants::COL_TP] == PermissionsConstants::SA)
                                    <th>{{__('Action')}}</th>
                                @endif
                            </tr>
                            </thead>
                            <tbody>
                            @php
                                $path =\App\Models\Utility::getFile('uploads/order');
                            @endphp
                            @foreach($orders as $order)
                                <tr>
                                    <td>{{$order->order_id}}</td>
                                    <td>{{$order->user_name}}</td>
                                    <td>{{$order->plan_name}}</td>
                                    <td>{{isset($admin_payment_setting['currency_symbol']) ? $admin_payment_setting['currency_symbol'] : '$'}}{{number_format($order->price)}}</td>
                                    <td>
                                        @if($order->payment_status == 'success' || $order->payment_status == 'Approved')
                                            <span class="status_badge badge bg-primary p-2 px-3 rounded">{{ucfirst($order->payment_status)}}</span>
                                        @elseif($order->payment_status == 'succeeded')
                                            <span class="status_badge badge bg-primary p-2 px-3 rounded">{{__('Success')}}</span>
                                        @elseif($order->payment_status == 'Pending')
                                            <span class="status_badge badge bg-warning p-2 px-3 rounded">{{__('Pending')}}</span>
                                        @else
                                            <span class="status_badge badge bg-danger p-2 px-3 rounded">{{ucfirst($order->payment_status)}}</span>
                                        @endif
                                    </td>
                                    <td>{{$order->payment_type}}</td>
                                    <td>{{$order->created_at->format('d M Y')}}</td>
                                    <td class="text-center">
                                        {{!empty($order->totalCouponUsed)? !empty($order->totalCouponUsed->couponDetail)?$order->totalCouponUsed->couponDetail->code:'-':'-'}}
                                    </td>
                                    <td class="Id">
                                        @if(!empty($order->payment_type) && strolower($order->payment_type) === 'manually')
                                            <p>{{__('Manually plan upgraded by Super Admin')}}</p>
                                        @elseif(!empty($order->receipt) && strtolower($order->receipt) === 'free coupon')
                                            <p>{{__('Used 100 % discount coupon code.')}}</p>
                                        @elseif(!empty($order->payment_type) && strtolower($order->payment_type) === 'stripe')
                                            <a href="{{$order->receipt}}" target="_blank">
                                                <i class="ti ti-file-invoice"></i> {{__('Receipt')}}
                                            </a>
                                        @elseif(!empty($order->receipt) && strtolower($order->payment_type)  ==='bank transfer')
                                            <a href="{{ $path . '/' . $order->receipt }}" target="_blank">
                                                <i class="ti ti-file-invoice"></i> {{__('Receipt')}}
                                            </a>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="Action">
                                        @if($order->payment_type == 'Bank Transfer' && $order->payment_status == 'Pending')
                                            @php
                                                $actionUrl   = Route::has(ViewsConstants::OD.'.action')
                                                    ? route(ViewsConstants::OD.'.action', $order->id)
                                                    : '#';
                                                $actionClass = 'action-order-link-'.$order->id;
                                            @endphp
                                            <span>
                                                <div class="action-btn bg-warning">
                                                    <a
                                                        href="{{ $actionUrl }}"
                                                        id="{{ $actionClass }}"
                                                        class="{{ ViewClassNamesConstants::BT_SM_CT }} {{ $actionClass }}"
                                                        data-url="{{ $actionUrl }}"
                                                        data-sv-localized="true"
                                                        data-guard-msg="{{ __(Utility::fetchLinkMessage($lang, ViewsConstants::OD, 'payment_status_unavailable') ?? '# ERROR') }}"
                                                        data-size="lg"
                                                        data-ajax-popup="true"
                                                        data-title="{{ __('Payment Status') }}"
                                                        data-bs-toggle="tooltip"
                                                        title="{{ __('Payment Status') }}"
                                                        data-original-title="{{ __('Payment Status') }}"
                                                    >
                                                        <i class="{{ ViewClassNamesConstants::TI_CRT_WT }}"></i>
                                                    </a>
                                                </div>
                                            </span>
                                            <script defer src="{{ asset('assets/js/routes/orders/action.js') }}"></script>
                                        @endif
                                    
                                        @php
                                            $deleteUrl   = Route::has(ViewsConstants::OD.'.destroy')
                                                ? route(ViewsConstants::OD.'.destroy', $order->id)
                                                : '#';
                                            $deleteClass = 'delete-order-link-'.$order->id;
                                            $formId      = 'delete-order-form-'.$order->id;
                                        @endphp
                                        <span>
                                            <div class="{{ ViewClassNamesConstants::ACT_BTN_DNG_2 }}">
                                                {!! Collective\Html\FormFacade::open([
                                                    'method' => 'DELETE',
                                                    'url'    => $deleteUrl,
                                                    'id'     => $formId
                                                ]) !!}
                                                    <a
                                                        href="{{ $deleteUrl }}"
                                                        id="{{ $deleteClass }}"
                                                        class="{{ ViewClassNamesConstants::TRS_PARA }} {{ $deleteClass }}"
                                                        data-url="{{ $deleteUrl }}"
                                                        data-sv-localized="true"
                                                        data-guard-msg="{{ __(Utility::fetchLinkMessage($lang, ViewsConstants::OD, 'delete_order_unavailable') ?? '# ERROR') }}"
                                                        data-bs-toggle="tooltip"
                                                        title="{{ __('Delete') }}"
                                                        data-original-title="{{ __('Delete') }}"
                                                        data-confirm="{{ __(Utility::fetchLinkMessage($lang, 'generics', 'are_you_sure') ?? 'Are You Sure?') }}|{{ __(Utility::fetchLinkMessage($lang, 'generics', 'irreversible_action') ?? 'This action can not be undone. Do you want to continue?') }}"
                                                        data-confirm-yes="document.getElementById('{{ $formId }}').submit();"
                                                    >
                                                        <i class="{{ ViewClassNamesConstants::TI_TRS_WT }}"></i>
                                                    </a>
                                                {!! Collective\Html\FormFacade::close() !!}
                                            </div>
                                        </span>
                                        <script defer src="{{ asset('assets/js/routes/orders/delete.js') }}"></script>
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
