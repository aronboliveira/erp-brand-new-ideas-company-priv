@php
    use App\Config\Constants\{
        DatabaseConstants
        ExtendingLayoutsConstants,
        PlansConstants,
        StacksConstants,
        YieldingConstants,
    };
    use Illuminate\Support\Facades\{Auth,Route};
    $user = Auth::user();
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@php
    use App\Config\Constants\{PermissionsConstants, UsersConstants};
    use Illuminate\Support\Facades\Storage;
    $dir = asset(Storage::url('uploads/plan'));
@endphp
@section(YieldingConstants::ADM_PG_TTL)
    {{__('Manage Plan')}}
@endsection
@section(YieldingConstants::ADM_BDC)
    <li class="breadcrumb-item">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
        {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="breadcrumb-item active" aria-current="page">{{__('Plan')}}</li>
@endsection
@section(YieldingConstants::ADM_ACT_BTN)
    <div class="float-end">
        @can('create plan')
            @if(isset($admin_payment_setting) && !empty($admin_payment_setting))
                @if($admin_payment_setting['is_manually_payment_enabled'] == 'on' || $admin_payment_setting['is_bank_transfer_enabled'] == 'on'
                || $admin_payment_setting['is_stripe_enabled'] == 'on' || $admin_payment_setting['is_paypal_enabled'] == 'on'
                || $admin_payment_setting['is_paystack_enabled'] == 'on' || $admin_payment_setting['is_flutterwave_enabled'] == 'on'
                || $admin_payment_setting['is_razorpay_enabled'] == 'on' || $admin_payment_setting['is_mercado_enabled'] == 'on'
                || $admin_payment_setting['is_paytm_enabled'] == 'on'  || $admin_payment_setting['is_mollie_enabled'] == 'on'
                || $admin_payment_setting['is_skrill_enabled'] == 'on' || $admin_payment_setting['is_coingate_enabled'] == 'on'
                || $admin_payment_setting['is_paymentwall_enabled'] == 'on' || $admin_payment_setting['is_toyyibpay_enabled'] == 'on'
                || $admin_payment_setting['is_payfast_enabled'] == 'on' || $admin_payment_setting['is_iyzipay_enabled'] == 'on'
                || $admin_payment_setting['is_sspay_enabled'] == 'on' || $admin_payment_setting['is_paytab_enabled'] == 'on'
                || $admin_payment_setting['is_benefit_enabled'] == 'on' || $admin_payment_setting['is_cashfree_enabled'] == 'on'
                || $admin_payment_setting['is_aamarpay_enabled'] == 'on' || $admin_payment_setting['is_paytr_enabled'] == 'on'
                || $admin_payment_setting['is_yookassa_enabled'] == 'on')
                    <a href="#" data-size="lg" data-url="{{ route('plans.create') }}" data-ajax-popup="true" data-bs-toggle="tooltip" title="{{__('Create')}}" data-title="{{__('Create New Plan')}}" class="btn btn-sm btn-primary">
                        <i class="ti ti-plus"></i>
                    </a>
                @endif
            @endif
        @endcan
    </div>
@endsection
@section(YieldingConstants::ADM_CTT)
    <div class="row">
        @foreach($plans as $plan)
            <div class="plan_card">
                <div class="card price-card price-1 wow animate__fadeInUp" data-wow-delay="0.2s" style="
                   visibility: visible;
                   animation-delay: 0.2s;
                   animation-name: fadeInUp;
                   ">
                    <div class="card-body">
                        <span class="price-badge bg-primary">{{ $plan[PlansConstants::COL_NM] }}</span>
                        @if (($user[UsersConstants::COL_TP] == PermissionsConstants::CPN || 
                                $user[UsersConstants::COL_TP] == PermissionsConstants::SA) 
                                && $user[UsersConstants::COL_PL] == $plan->id)
                            <div class="d-flex flex-row-reverse m-0 p-0 active-tag">
                                 <span class=" align-items-right">
                                    <i class="f-10 lh-1 fas fa-circle text-success"></i>
                                    <span class="ms-2">{{ __('Active') }}</span>
                                </span>
                            </div>

                        @endif
                        <h1 class="mb-4 f-w-600 ">{{(isset($admin_payment_setting['currency_symbol']) ? 
                        $admin_payment_setting['currency_symbol'] : '$')}}{{ number_format($plan[PlansConstants::COL_PC]) }}
                            <small class="text-sm">/{{__(\App\Models\Plan::$arrDuration[$plan->duration])}}</small></h1>
                        <p class="mb-0">
                            {{__('Duration : ').__(\App\Models\Plan::$arrDuration[$plan->duration])}}<br/>
                        </p>
                        @php
                            $features = [
                                ['key'=>PlansConstants::COL_MAX_U,  'label'=>__('Users'),       'type'=>'quota',  'unit'=>null],
                                ['key'=>PlansConstants::COL_MAX_CR, 'label'=>__('Customers'),   'type'=>'quota',  'unit'=>null],
                                ['key'=>PlansConstants::COL_MAX_V,  'label'=>__('Vendors'),     'type'=>'quota',  'unit'=>null],
                                ['key'=>PlansConstants::COL_MAX_CL, 'label'=>__('Clients'),     'type'=>'quota',  'unit'=>null],
                                ['key'=>PlansConstants::COL_SL,     'label'=>__('Storage'),     'type'=>'quota',  'unit'=>__('MB')],
                                ['key'=>PlansConstants::COL_ACC,    'label'=>__('Account'),     'type'=>'toggle'],
                                ['key'=>PlansConstants::COL_CRM,    'label'=>__('CRM'),         'type'=>'toggle'],
                                ['key'=>PlansConstants::COL_HRM,    'label'=>__('HRM'),         'type'=>'toggle'],
                                ['key'=>PlansConstants::COL_PJ,     'label'=>__('Project'),     'type'=>'toggle'],
                                ['key'=>PlansConstants::COL_POS,    'label'=>__('POS'),         'type'=>'toggle'],
                                ['key'=>PlansConstants::COL_GPT,    'label'=>__('Chat GPT'),    'type'=>'toggle'],
                            ];
                        
                            $chunks = collect($features)
                                ->chunk(ceil(count($features)/2))
                                ->all();
                        @endphp
                        <div class="row">
                            @foreach($chunks as $col)
                                <div class="col-6">
                                    <ul class="list-unstyled my-5">
                                        @foreach($col as $item)
                                            @php
                                                $value = $plan[$item['key']];
                                                if($item['type']=='quota'){
                                                    $text = $value===-1?__('Unlimited'):$value;
                                                    if($item['unit']) $text .= ' '.$item['unit'];
                                                    $text .= ' '.$item['label'];
                                                } else {
                                                    $text = ($value===1?__('Enable'):__('Disable')).' '.$item['label'];
                                                }
                                            @endphp
                                            <li class="white-sapce-nowrap">
                                                <span class="theme-avatar"><i class="text-primary ti ti-circle-plus"></i></span>
                                                {{ $text }}
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endforeach
                        </div>
                        @if($user[UsersConstants::COL_TP] == PermissionsConstants::SA)
                            <div class="col-4">
                                <a title="{{__('Edit Plan')}}" href="#" class="btn btn-primary btn-icon m-1" data-url="{{ route('plans.edit',$plan->id) }}" data-ajax-popup="true" data-title="{{__('Edit Plan')}}" data-size="lg" data-toggle="tooltip" data-original-title="{{__('Edit')}}">
                                    <i class="ti ti-edit"></i>
                                </a>
                            </div>
                        @endif
                        @if(isset($admin_payment_setting) && !empty($admin_payment_setting))
                            @if($admin_payment_setting['is_manually_payment_enabled'] == 'on'
                                || $admin_payment_setting['is_bank_transfer_enabled'] == 'on' || $admin_payment_setting['is_stripe_enabled'] == 'on'
                                || $admin_payment_setting['is_paypal_enabled'] == 'on' || $admin_payment_setting['is_paystack_enabled'] == 'on'
                                || $admin_payment_setting['is_flutterwave_enabled'] == 'on'|| $admin_payment_setting['is_razorpay_enabled'] == 'on'
                                || $admin_payment_setting['is_mercado_enabled'] == 'on'|| $admin_payment_setting['is_paytm_enabled'] == 'on'
                                || $admin_payment_setting['is_mollie_enabled'] == 'on'|| $admin_payment_setting['is_skrill_enabled'] == 'on'
                                || $admin_payment_setting['is_coingate_enabled'] == 'on' || $admin_payment_setting['is_paymentwall_enabled'] == 'on'
                                || $admin_payment_setting['is_paymentwall_enabled'] == 'on' || $admin_payment_setting['is_toyyibpay_enabled'] == 'on'
                                || $admin_payment_setting['is_payfast_enabled'] == 'on' || $admin_payment_setting['is_iyzipay_enabled'] == 'on'
                                || $admin_payment_setting['is_sspay_enabled'] == 'on' || $admin_payment_setting['is_paytab_enabled'] == 'on'
                                || $admin_payment_setting['is_benefit_enabled'] == 'on' || $admin_payment_setting['is_cashfree_enabled'] == 'on'
                                || $admin_payment_setting['is_aamarpay_enabled'] == 'on' || $admin_payment_setting['is_paytr_enabled'] == 'on')
                                @if($user[UsersConstants::COL_TP] != PermissionsConstants::SA)
                                    @if($plan->id != $user[UsersConstants::COL_PL])
                                        @if($plan[PlansConstants::COL_PC] > 0)
                                            <a href="{{route('stripe',\Illuminate\Support\Facades\Crypt::encrypt($plan->id))}}" class="btn btn-primary btn-icon m-1">{{__('Buy Plan')}}</a>
                                        @endif
                                    @endif
                                    @if($plan->id !== DatabaseConstants::DEFAULT_PLAN && $plan->id != $user[UsersConstants::COL_PL])
                                        @if($user->requested_plan !$plan->id)
                                            <a href="{{ route(ViewsConstants::PLN.'.request.send',[\Illuminate\Support\Facades\Crypt::encrypt($plan->id)])}}" class="btn btn-primary btn-icon m-1" data-title="{{__('Send Request')}}" data-bs-toggle="tooltip" title="{{__('Send Request')}}">
                                                <span class="btn-inner--icon"><i class="ti ti-corner-up-right"></i></span>
                                            </a>
                                        @else
                                            <a href="{{ route(ViewsConstants::PLN_RQ.'.request.cancel',$user->id) }}" class="btn btn-danger btn-icon m-1" data-title="{{__('`Cancle Request')}}" data-bs-toggle="tooltip" title"{{__('Cancle Request')}}">
                                                <span class="btn-inner--icon"><i class="ti ti-x"></i></span>
                                            </a>
                                        @endif
                                    @endif
                                @endif
                            @endif
                        @endif

                        @if(($user[UsersConstants::COL_TP] == PermissionsConstants::CPN || 
                             $user[UsersConstants::COL_TP] == PermissionsConstants::SA)
                             && $user[UsersConstants::COL_PL] == $plan->id)
                            <p class="display-total-time text-dark mb-0">
                                {{__('Plan Expired : ') }} {{!empty($user[UsersConstants::COL_PL]_expire_date) 
                                ? $user?->dateFormat($user[UsersConstants::COL_PL]_expire_date)
                                :'lifetime'}}
                            </p>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endsection
