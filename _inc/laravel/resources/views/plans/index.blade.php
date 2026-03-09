@php
    try {
$user        = Auth::user();
        $lang        = is_callable([Utility::class,'fetchUserLang']) ? Utility::fetchUserLang(user:$user) : app()->getLocale();
        $canFetchMsg = is_callable([Utility::class,'fetchLinkMessage']);

        $dashUrl   = Route::has('dashboard') ? route('dashboard') : '#';
        $dashGuard = ($canFetchMsg ? Utility::fetchLinkMessage($lang, 'generics', 'dashboard_unavailable') : 'Dashboard route is unavailable. Please contact technical support or your domain administrator.') ?? __('Dashboard route is unavailable. Please contact technical support or your domain administrator.');

        $plansList = [];
        if (is_array($plans ?? null) && count($plans)) {
            $plansList = $plans;
        } elseif (($plans ?? null) instanceof Collection && $plans->isNotEmpty()) {
            $plansList = $plans;
        }

        $dir = asset(Storage::url('uploads/plan'));
        $currency = $admin_payment_setting['currency_symbol'] ?? '$';
    } catch (\Throwable $e) {
        \Log::error('plans/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    }
@endphp
@extends(EL::ADM)

@section(YD::ADM_PG_TTL)
    {{ __('Manage Plan') }}
@endsection

@section(YD::ADM_BDC)
    <li class="{{ VC::BCI }}">
        <a href="{{ $dashUrl }}"
           data-url="{{ $dashUrl }}"
           data-sv-localized="true"
           data-guard-msg="{{ base64_encode($dashGuard) }}"
           {{ $dashUrl !== '#' ? '' : 'aria-disabled=true' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="{{ VC::BCI_ACT }}" aria-current="page">{{ __('Plan') }}</li>
@endsection

@section(YD::ADM_ACT_BTN)
    <div class="{{ VC::FEND }}">
        @can('create plan')
            @php
                try {
                    $canCreate =
                        !empty($admin_payment_setting) && is_array($admin_payment_setting ?? null) && (
                            ($admin_payment_setting['is_manually_payment_enabled'] ?? 'off') == 'on' ||
                            ($admin_payment_setting['is_bank_transfer_enabled'] ?? 'off') == 'on' ||
                            ($admin_payment_setting['is_stripe_enabled'] ?? 'off') == 'on' ||
                            ($admin_payment_setting['is_paypal_enabled'] ?? 'off') == 'on' ||
                            ($admin_payment_setting['is_paystack_enabled'] ?? 'off') == 'on' ||
                            ($admin_payment_setting['is_flutterwave_enabled'] ?? 'off') == 'on' ||
                            ($admin_payment_setting['is_razorpay_enabled'] ?? 'off') == 'on' ||
                            ($admin_payment_setting['is_mercado_enabled'] ?? 'off') == 'on' ||
                            ($admin_payment_setting['is_paytm_enabled'] ?? 'off') == 'on' ||
                            ($admin_payment_setting['is_mollie_enabled'] ?? 'off') == 'on' ||
                            ($admin_payment_setting['is_skrill_enabled'] ?? 'off') == 'on' ||
                            ($admin_payment_setting['is_coingate_enabled'] ?? 'off') == 'on' ||
                            ($admin_payment_setting['is_paymentwall_enabled'] ?? 'off') == 'on' ||
                            ($admin_payment_setting['is_toyyibpay_enabled'] ?? 'off') == 'on' ||
                            ($admin_payment_setting['is_payfast_enabled'] ?? 'off') == 'on' ||
                            ($admin_payment_setting['is_iyzipay_enabled'] ?? 'off') == 'on' ||
                            ($admin_payment_setting['is_sspay_enabled'] ?? 'off') == 'on' ||
                            ($admin_payment_setting['is_paytab_enabled'] ?? 'off') == 'on' ||
                            ($admin_payment_setting['is_benefit_enabled'] ?? 'off') == 'on' ||
                            ($admin_payment_setting['is_cashfree_enabled'] ?? 'off') == 'on' ||
                            ($admin_payment_setting['is_aamarpay_enabled'] ?? 'off') == 'on' ||
                            ($admin_payment_setting['is_paytr_enabled'] ?? 'off') == 'on' ||
                            ($admin_payment_setting['is_yookassa_enabled'] ?? 'off') == 'on'
                        );

                    $createUrl   = $canCreate && Route::has(VW::PLN.'.create') ? route(VW::PLN.'.create') : '#';
                    $createGuard = ($canFetchMsg ? Utility::fetchLinkMessage($lang, VW::PLN ?? 'plans', 'create_plan_unavailable') : 'Create Plan route is unavailable. Please contact technical support or your domain administrator.') ?? __('Create Plan route is unavailable. Please contact technical support or your domain administrator.');
                } catch (\Throwable $e) {
                    \Log::error('plans/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                }
@endphp
            @if($canCreate)
                <a href="{{ $createUrl }}"
                   data-size="lg"
                   data-url="{{ $createUrl }}"
                   data-ajax-popup="true"
                   data-bs-toggle="tooltip"
                   title="{{ __('Create') }}"
                   data-title="{{ __('Create New Plan') }}"
                   data-sv-localized="true"
                   data-guard-msg="{{ base64_encode($createGuard) }}"
                   class="{{ VC::BT_SM_PM }}">
                    <i class="{{ VC::TI_PLS }}"></i>
                </a>
            @endif
        @endcan
    </div>
@endsection

@section(YD::ADM_CTT)
    <div class="{{ VC::RW }}">
        @forelse($plansList as $plan)
            @php
                try {
                    $pName      = data_get($plan, PL::COL_NM, __('No name available'));
                    $pPrice     = data_get($plan, PL::COL_PC, 0);
                    $pDurRaw    = data_get($plan, 'duration', null);
                    $durMap     = Plan::$arrDuration ?? [];
                    $pDurText   = $durMap[$pDurRaw] ?? __('Unknown duration');
                    $isCompany  = data_get($user, UC::COL_TP) === PC::CPN;
                    $isSuper    = data_get($user, UC::COL_TP) === PC::SA;
                    $isActive   = ($isCompany || $isSuper) && (data_get($user, UC::COL_PL) == data_get($plan, 'id'));
                    $features = [
                        ['key'=>PL::COL_MAX_U,  'label'=>__('Users'),     'type'=>'quota',  'unit'=>null],
                        ['key'=>PL::COL_MAX_CR, 'label'=>__('Customers'), 'type'=>'quota',  'unit'=>null],
                        ['key'=>PL::COL_MAX_V,  'label'=>__('Vendors'),   'type'=>'quota',  'unit'=>null],
                        ['key'=>PL::COL_MAX_CL, 'label'=>__('Clients'),   'type'=>'quota',  'unit'=>null],
                        ['key'=>PL::COL_SL,     'label'=>__('Storage'),   'type'=>'quota',  'unit'=>__('MB')],
                        ['key'=>PL::COL_ACC,    'label'=>__('Account'),   'type'=>'toggle'],
                        ['key'=>PL::COL_CRM,    'label'=>__('CRM'),       'type'=>'toggle'],
                        ['key'=>PL::COL_HRM,    'label'=>__('HRM'),       'type'=>'toggle'],
                        ['key'=>PL::COL_PJ,     'label'=>__('Project'),   'type'=>'toggle'],
                        ['key'=>PL::COL_POS,    'label'=>__('POS'),       'type'=>'toggle'],
                        ['key'=>PL::COL_GPT,    'label'=>__('Chat GPT'),  'type'=>'toggle'],
                    ];
                    $chunks = collect($features)->chunk(ceil(count($features)/2))->all();

                    $editUrl   = (Gate::check('create plan') || Gate::check('edit plan')) && Route::has(VW::PLN.'.edit') ? route(VW::PLN.'.edit', $plan->id) : '#';
                    $editGuard = ($canFetchMsg ? Utility::fetchLinkMessage($lang, VW::PLN ?? 'plans', 'edit_plan_unavailable') : 'Edit Plan route is unavailable. Please contact technical support or your domain administrator.') ?? __('Edit Plan route is unavailable. Please contact technical support or your domain administrator.');

                    $stripeHas = Route::has('stripe');
                    $buyUrl    = $stripeHas ? route('stripe', Crypt::encrypt($plan->id)) : '#';
                    $buyGuard  = ($canFetchMsg ? Utility::fetchLinkMessage($lang, VW::PLN ?? 'plans', 'buy_plan_unavailable') : 'Buy Plan route is unavailable. Please contact technical support or your domain administrator.') ?? __('Buy Plan route is unavailable. Please contact technical support or your domain administrator.');

                    $reqSendHas = Route::has(VW::PLN.'.request.send');
                    $reqSendUrl = $reqSendHas ? route(VW::PLN.'.request.send', [Crypt::encrypt($plan->id)]) : '#';
                    $reqSendGuard = ($canFetchMsg ? Utility::fetchLinkMessage($lang, VW::PLN_RQ ?? 'plan_request', 'send_plan_request_unavailable') : 'Send Plan Request route is unavailable. Please contact technical support or your domain administrator.') ?? __('Send Plan Request route is unavailable. Please contact technical support or your domain administrator.');

                    $reqCancelHas = Route::has(VW::PLN_RQ.'.request.cancel');
                    $reqCancelUrl = $reqCancelHas ? route(VW::PLN_RQ.'.request.cancel', $user->id) : '#';
                    $reqCancelGuard = ($canFetchMsg ? Utility::fetchLinkMessage($lang, VW::PLN_RQ ?? 'plan_request', 'cancel_plan_request_unavailable') : 'Cancel Plan Request route is unavailable. Please contact technical support or your domain administrator.') ?? __('Cancel Plan Request route is unavailable. Please contact technical support or your domain administrator.');

                    $planExpire = data_get($user, 'plan_expire_date');
                } catch (\Throwable $e) {
                    \Log::error('plans/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                }
@endphp

            <div class="plan_card">
                <div class="card price-card price-1 wow animate__fadeInUp" data-wow-delay="0.2s" style="visibility:visible;animation-delay:0.2s;animation-name:fadeInUp;">
                    <div class="{{ VC::CD_BD }}">
                        <span class="price-badge {{ VC::BG_P }}">{{ $pName }}</span>

                        @if($isActive)
                            <div class="{{ VC::DFL }} flex-row-reverse m-0 p-0 active-tag">
                                <span class="align-items-right">
                                    <i class="f-10 lh-1 fas fa-circle text-success"></i>
                                    <span class="{{ VC::MS2 }}">{{ __('Active') }}</span>
                                </span>
                            </div>
                        @endif

                        <h1 class="{{ VC::MB4 }} {{ VC::FW600 }}">
                            {{ $currency }}{{ number_format((float)$pPrice) }}
                            <small class="{{ VC::TXSM }}">/{{ __($pDurText) }}</small>
                        </h1>

                        <p class="{{ VC::MB0 }}">
                            {{ __('Duration : ') . __($pDurText) }}<br/>
                        </p>

                        <div class="row">
                            @foreach($chunks as $col)
                                <div class="{{ VC::C6 }}">
                                    <ul class="{{ VC::LST_UNSTL_MY5 }}">
                                        @foreach($col as $item)
                                            @php
                                                try {
                                                    $value = data_get($plan, $item['key']);
                                                    if($item['type']==='quota'){
                                                        $text = ($value===-1 ? __('Unlimited') : (is_null($value) ? __('Not specified') : $value));
                                                        if(!empty($item['unit'])) $text .= ' '.$item['unit'];
                                                        $text .= ' '.$item['label'];
                                                    } else {
                                                        $text = ((int)$value === 1 ? __('Enable') : __('Disable')).' '.$item['label'];
                                                    }
                                                } catch (\Throwable $e) {
                                                    \Log::error('plans/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                }
@endphp
                                            <li class="white-sapce-nowrap">
                                                <span class="theme-avatar"><i class="{{ VC::TX_PM }} {{ VC::TI_CC_PLS }}"></i></span>
                                                {{ $text }}
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endforeach
                        </div>

                        @if($isSuper)
                            <div class="col-4">
                                <a title="{{ __('Edit Plan') }}"
                                   href="{{ $editUrl }}"
                                   class="{{ VC::BT_PRM }} btn-icon m-1"
                                   data-url="{{ $editUrl }}"
                                   data-ajax-popup="true"
                                   data-title="{{ __('Edit Plan') }}"
                                   data-size="lg"
                                   data-bs-toggle="tooltip"
                                   data-sv-localized="true"
                                   data-guard-msg="{{ base64_encode($editGuard) }}"
                                   title="{{ __('Edit') }}">
                                    <i class="ti ti-edit"></i>
                                </a>
                            </div>
                        @endif

                        @if(!empty($admin_payment_setting) && is_array($admin_payment_setting ?? null))
                            @php
                                try {
                                    $anyGateway =
                                        ($admin_payment_setting['is_manually_payment_enabled'] ?? 'off') == 'on' ||
                                        ($admin_payment_setting['is_bank_transfer_enabled'] ?? 'off') == 'on' ||
                                        ($admin_payment_setting['is_stripe_enabled'] ?? 'off') == 'on' ||
                                        ($admin_payment_setting['is_paypal_enabled'] ?? 'off') == 'on' ||
                                        ($admin_payment_setting['is_paystack_enabled'] ?? 'off') == 'on' ||
                                        ($admin_payment_setting['is_flutterwave_enabled'] ?? 'off') == 'on' ||
                                        ($admin_payment_setting['is_razorpay_enabled'] ?? 'off') == 'on' ||
                                        ($admin_payment_setting['is_mercado_enabled'] ?? 'off') == 'on' ||
                                        ($admin_payment_setting['is_paytm_enabled'] ?? 'off') == 'on' ||
                                        ($admin_payment_setting['is_mollie_enabled'] ?? 'off') == 'on' ||
                                        ($admin_payment_setting['is_skrill_enabled'] ?? 'off') == 'on' ||
                                        ($admin_payment_setting['is_coingate_enabled'] ?? 'off') == 'on' ||
                                        ($admin_payment_setting['is_paymentwall_enabled'] ?? 'off') == 'on' ||
                                        ($admin_payment_setting['is_toyyibpay_enabled'] ?? 'off') == 'on' ||
                                        ($admin_payment_setting['is_payfast_enabled'] ?? 'off') == 'on' ||
                                        ($admin_payment_setting['is_iyzipay_enabled'] ?? 'off') == 'on' ||
                                        ($admin_payment_setting['is_sspay_enabled'] ?? 'off') == 'on' ||
                                        ($admin_payment_setting['is_paytab_enabled'] ?? 'off') == 'on' ||
                                        ($admin_payment_setting['is_benefit_enabled'] ?? 'off') == 'on' ||
                                        ($admin_payment_setting['is_cashfree_enabled'] ?? 'off') == 'on' ||
                                        ($admin_payment_setting['is_aamarpay_enabled'] ?? 'off') == 'on' ||
                                        ($admin_payment_setting['is_paytr_enabled'] ?? 'off') == 'on';
                                } catch (\Throwable $e) {
                                    \Log::error('plans/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                }
@endphp

                            @if(!$isSuper && $anyGateway)
                                @if(data_get($plan,'id') != data_get($user, UC::COL_PL))
                                    @if((float)$pPrice > 0)
                                        <a href="{{ $buyUrl }}"
                                           class="{{ VC::BT_PRM }} btn-icon m-1"
                                           data-url="{{ $buyUrl }}"
                                           data-sv-localized="true"
                                           data-guard-msg="{{ base64_encode($buyGuard) }}">
                                            {{ __('Buy Plan') }}
                                        </a>
                                    @endif
                                @endif

                                @if(data_get($plan,'id') !== DB::DEFAULT_PLAN && data_get($plan,'id') != data_get($user, UC::COL_PL))
                                    @php
                                        $hasRequestedSame = (int) data_get($user, 'requested_plan') === (int) data_get($plan, 'id');
@endphp
                                    @if(!$hasRequestedSame)
                                        <a href="{{ $reqSendUrl }}"
                                           class="{{ VC::BT_PRM }} btn-icon m-1"
                                           data-url="{{ $reqSendUrl }}"
                                           data-sv-localized="true"
                                           data-guard-msg="{{ base64_encode($reqSendGuard) }}"
                                           data-bs-toggle="tooltip"
                                           title="{{ __('Send Request') }}">
                                            <span class="btn-inner--icon"><i class="ti ti-corner-up-right"></i></span>
                                        </a>
                                    @else
                                        <a href="{{ $reqCancelUrl }}"
                                           class="btn btn-danger btn-icon m-1"
                                           data-url="{{ $reqCancelUrl }}"
                                           data-sv-localized="true"
                                           data-guard-msg="{{ base64_encode($reqCancelGuard) }}"
                                           data-bs-toggle="tooltip"
                                           title="{{ __('Cancel Request') }}">
                                            <span class="btn-inner--icon"><i class="ti ti-x"></i></span>
                                        </a>
                                    @endif
                                @endif
                            @endif
                        @endif

                        @if($isActive)
                            <p class="display-total-time {{ VC::TX_DK }} {{ VC::MB0 }}">
                                @php
                                    $expText = $planExpire ? ($user?->dateFormat($planExpire) ?? $planExpire) : __('lifetime');
@endphp
                                {{ __('Plan Expired : ') }} {{ $expText }}
                            </p>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="{{ VC::C12 }}">
                <div class="{{ VC::TXCT_MT }} {{ VC::PY4 }}">{{ __('No plans found.') }}</div>
            </div>
        @endforelse
    </div>
@endsection

@push(ST::ADM_SCR_PG)
    <script defer src="{{ asset('assets/js/routes/plans/index.js') }}"></script>
@endpush
