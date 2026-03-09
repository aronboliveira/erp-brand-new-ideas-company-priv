@php
    try {
$user = Auth::user();
        $lang = is_callable([Utility::class, 'fetchUserLang']) ? Utility::fetchUserLang(user:$user) : app()->getLocale();
        $hasUserDate  = $user && method_exists($user, 'dateFormat');
        $hasUserPrice = $user && method_exists($user, 'priceFormat');

        $dashUrl   = Route::has('dashboard') ? route('dashboard') : '#';
        $dashGuard = (is_callable([Utility::class,'fetchLinkMessage']) ? Utility::fetchLinkMessage($lang,'generics','dashboard_unavailable') : null) ?? __('Dashboard route is unavailable. Please contact technical support or your domain administrator.');

        $paymentsList = [];
        if (is_array($payments ?? null) && count($payments))               $paymentsList = $payments;
        elseif (($payments ?? null) instanceof Collection && $payments->isNotEmpty()) $paymentsList = $payments;

        $filesBase = is_callable([Utility::class,'getFile']) ? Utility::getFile('uploads/payment') : 'uploads/payment';

        $fmtDate = function($v) use($hasUserDate,$user){
            if(!$v) return __('Date not available.');
            if($hasUserDate){ try{ return $user->dateFormat($v) ?? __('Failed to format date.'); }catch(\Throwable){ return __('Failed to format date.'); } }
            if($v instanceof \Carbon\Carbon){ try{ return $v->format('Y-m-d'); }catch(\Throwable){ return __('Failed to format date.'); } }
            return __('Date not available.');
        };
        $fmtPrice = function($v) use($hasUserPrice,$user){
            if($hasUserPrice){ try{ return $user->priceFormat($v) ?? __('Failed to format amount.'); }catch(\Throwable){ return __('Failed to format amount.'); } }
            return is_numeric($v) ? number_format((float)$v,2) : __('Amount not available.');
        };
    } catch (\Throwable $e) {
        \Log::error('payments/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    }
@endphp

@extends(EL::ADM)

@section(YD::ADM_PG_TTL)
    {{ __('Manage Payments') }}
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
    <li class="{{ VC::BCI }}">{{ __('Payment') }}</li>
@endsection

@section(YD::ADM_ACT_BTN)
    <div class="{{ VC::FEND }}">
        @can('create payment')
            @php
                $createUrl   = Route::has(VW::PAY.'.create') ? route(VW::PAY.'.create') : '#';
                $createGuard = (is_callable([Utility::class,'fetchLinkMessage']) ? Utility::fetchLinkMessage($lang, VW::PAY, 'create_payment_unavailable') : 'Create payment route is unavailable. Please contact technical support or your domain administrator.') ?? __('Create payment route is unavailable. Please contact technical support or your domain administrator.');
@endphp
            <a href="{{ $createUrl }}"
               data-url="{{ $createUrl }}"
               data-ajax-popup="true"
               data-size="lg"
               data-title="{{ __('Create New Payment') }}"
               data-sv-localized="true"
               data-guard-msg="{{ base64_encode($createGuard) }}"
               data-bs-toggle="tooltip"
               title="{{ __('Create') }}"
               class="{{ VC::BT_SM_PM }}">
                <i class="{{ VC::TI_PLS }}"></i>
            </a>
        @endcan
    </div>
@endsection

@section(YD::ADM_CTT)
    <div class="row">
        <div class="{{ VC::CS12 }}">
            <div class="{{ VC::MT2 }}" id="multiCollapseExample1">
                <div class="card">
                    <div class="{{ VC::CD_BD }}">
                        {{ Form::open(['route' => [VW::PAY.'.index'],'method' => 'GET','id'=>'payment_form']) }}
                        <div class="{{ VC::R_ALC_JCE }}">
                            <div class="{{ VC::CXL10 }}">
                                <div class="row">
                                    <div class="{{ VC::CL_XL3 }}">
                                        <div class="btn-box">
                                            {{ Form::label('date', __('Date'),['class'=>'form-label']) }}
                                            {{ Form::date('date', request('date',''), ['class' => 'form-control month-btn','id'=>'pc-daterangepicker-1']) }}
                                        </div>
                                    </div>
                                    <div class="{{ VC::CL_XL3 }}">
                                        <div class="btn-box">
                                            {{ Form::label('account', __('Account'),['class'=>'form-label']) }}
                                            {{ Form::select('account',$account ?? [], request('account',''), ['class' => 'form-control select','id'=>'choices-multiple']) }}
                                        </div>
                                    </div>
                                    <div class="{{ VC::CL_XL3 }}">
                                        <div class="btn-box">
                                            {{ Form::label('vendor', __('Vendor'),['class'=>'form-label']) }}
                                            {{ Form::select('vendor',$vendor ?? [], request('vendor',''), ['class' => 'form-control select','id'=>'choices-multiple1']) }}
                                        </div>
                                    </div>
                                    <div class="{{ VC::CL_XL3 }}">
                                        <div class="btn-box">
                                            {{ Form::label('category', __('Category'),['class'=>'form-label']) }}
                                            {{ Form::select('category',$category ?? [], request('category',''), ['class' => 'form-control select','id'=>'choices-multiple2']) }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="{{ VC::C_AT }} {{ VC::MT4 }}">
                                <div class="row">
                                    <div class="{{ VC::C_AT }}">
                                        <a href="#" class="{{ VC::BT_SM_PM }}"
                                           onclick="document.getElementById('payment_form').submit(); return false;"
                                           data-bs-toggle="tooltip" title="{{__('Apply')}}">
                                            <span class="btn-inner--icon"><i class="{{ VC::TI_SRC }}"></i></span>
                                        </a>
                                        @php
                                            $resetUrl = Route::has(VW::PAY.'.index') ? route(VW::PAY.'.index') : '#';
                                            $resetGuard = (is_callable([Utility::class,'fetchLinkMessage']) ? Utility::fetchLinkMessage($lang, VW::PAY, 'index_route_unavailable') : null) ?? __('Index payment route is unavailable. Please contact technical support or your domain administrator.');
@endphp
                                        <a href="{{ $resetUrl }}" class="{{ VC::BT_SM_DG }}"
                                           data-url="{{ $resetUrl }}"
                                           data-sv-localized="true"
                                           data-guard-msg="{{ base64_encode($resetGuard) }}"
                                           data-bs-toggle="tooltip" title="{{ __('Reset') }}">
                                            <span class="btn-inner--icon"><i class="ti ti-trash-off text-white-off"></i></span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        {{ Form::close() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="{{ VC::C12 }}">
            <div class="card">
                <div class="{{ VC::CD_BD }}">
                    <div class="{{ VC::TB_RSP }}">
                        <table class="table datatable">
                            <thead>
                            <tr>
                                <th>{{ __('Date') }}</th>
                                <th>{{ __('Amount') }}</th>
                                <th>{{ __('Account') }}</th>
                                <th>{{ __('Vendor') }}</th>
                                <th>{{ __('Category') }}</th>
                                <th>{{ __('Reference') }}</th>
                                <th>{{ __('Description') }}</th>
                                <th>{{ __('Payment Receipt') }}</th>
                                @canany(['edit payment','delete payment'])
                                    <th>{{ __('Action') }}</th>
                                @endcanany
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($paymentsList as $payment)
                                @php
                                    try {
                                        $dateTxt   = $fmtDate($payment->date ?? null);
                                        $amtTxt    = $fmtPrice($payment->amount ?? null);
                                        $acctTxt   = isset($payment->bankAccount) && $payment->bankAccount
                                                     ? trim(($payment->bankAccount->bank_name ?? '').' '.($payment->bankAccount->holder_name ?? ''))
                                                     : __('No account text available.');
                                        $vendorTxt = isset($payment->vendor) && $payment->vendor ? ($payment->vendor->name ?? '-') : __('No vendor text available.');
                                        $catTxt    = isset($payment->category) && $payment->category ? ($payment->category->name ?? '-') : __('No category text available.');
                                        $refTxt    = isset($payment->reference) && $payment->reference !== '' ? $payment->reference : __('No reference text available.');
                                        $descTxt   = isset($payment->description) && $payment->description !== '' ? $payment->description : __('No description text available.');
                                        $receipt   = $payment->add_receipt ?? '';
                                    } catch (\Throwable $e) {
                                        \Log::error('payments/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                    }
@endphp
                                <tr class="font-style">
                                    <td>{{ $dateTxt }}</td>
                                    <td>{{ $amtTxt }}</td>
                                    <td>{{ $acctTxt !== '' ? $acctTxt : '-' }}</td>
                                    <td>{{ $vendorTxt }}</td>
                                    <td>{{ $catTxt }}</td>
                                    <td>{{ $refTxt }}</td>
                                    <td>{{ $descTxt }}</td>
                                    <td>
                                        @if(!empty($receipt))
                                            @php
 $fileUrl = rtrim($filesBase,'/').'/'.$receipt;
@endphp
                                            <a class="{{ VC::ACT_BTN_PRIM }} {{ VC::BT_SM }} {{ VC::ALC }}" href="{{ $fileUrl }}" download>
                                                <i class="{{ VC::TI_DWN }} {{ VC::TXT_WT }}"></i>
                                            </a>
                                            <a href="{{ $fileUrl }}" target="_blank" class="{{ VC::ACT_BTN }} bg-secondary {{ VC::MS2 }} {{ VC::BT_SM_CT }}" data-bs-toggle="tooltip" title="{{ __('View') }}">
                                                <span class="btn-inner--icon"><i class="ti ti-crosshair {{ VC::TXT_WT }}"></i></span>
                                            </a>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    @canany(['edit payment','delete payment'])
                                        <td class="action">
                                            @can('edit payment')
                                                @php
                                                    $editUrl   = Route::has(VW::PAY.'.edit') ? route(VW::PAY.'.edit', $payment->id) : '#';
                                                    $editGuard = (is_callable([Utility::class,'fetchLinkMessage']) ? Utility::fetchLinkMessage($lang, VW::PAY, 'edit_payment_unavailable') : 'Edit payment route is unavailable. Please contact technical support or your domain administrator.') ?? __('Edit payment route is unavailable. Please contact technical support or your domain administrator.');
@endphp
                                                <div class="{{ VC::ACT_BTN_PRIM }}">
                                                    <a href="{{ $editUrl }}"
                                                       class="{{ VC::BT_SM_CT }}"
                                                       data-url="{{ $editUrl }}"
                                                       data-ajax-popup="true"
                                                       data-size="lg"
                                                       data-title="{{ __('Edit Payment') }}"
                                                       data-sv-localized="true"
                                                       data-guard-msg="{{ base64_encode($editGuard) }}"
                                                       data-bs-toggle="tooltip"
                                                       title="{{ __('Edit') }}">
                                                        <i class="{{ VC::TI_PC_WT }}"></i>
                                                    </a>
                                                </div>
                                            @endcan
                                            @can('delete payment')
                                                @php
                                                    try {
                                                        $delUrl   = Route::has(VW::PAY.'.destroy') ? route(VW::PAY.'.destroy', $payment->id) : '#';
                                                        $delGuard = (is_callable([Utility::class,'fetchLinkMessage']) ? Utility::fetchLinkMessage($lang, VW::PAY, 'delete_payment_unavailable') : 'Delete payment route is unavailable. Please contact technical support or your domain administrator.') ?? __('Delete payment route is unavailable. Please contact technical support or your domain administrator.');
                                                        $formId   = 'delete-form-'.$payment->id;
                                                    } catch (\Throwable $e) {
                                                        \Log::error('payments/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                    }
@endphp
                                                <div class="{{ VC::ACT_BTN_DNG_2 }}">
                                                    {!! Form::open([
                                                        'method' => 'DELETE',
                                                        'url'    => $delUrl,
                                                        'id'     => $formId,
                                                        'data-url' => $delUrl,
                                                        'data-sv-localized' => 'true',
                                                        'data-guard-msg' => $delGuard
                                                    ]) !!}
                                                        <a href="{{ $delUrl }}"
                                                           class="{{ VC::BT_SM_CT_PR }}"
                                                           data-url="{{ $delUrl }}"
                                                           data-sv-localized="true"
                                                           data-guard-msg="{{ base64_encode($delGuard) }}"
                                                           data-bs-toggle="tooltip"
                                                           title="{{ __('Delete') }}"
                                                           data-confirm="{{ __(Utility::fetchLinkMessage($lang, 'generics', 'are_you_sure') ?? 'Are You Sure?') }}|{{ __(Utility::fetchLinkMessage($lang, 'generics', 'irreversible_action') ?? 'This action can not be undone. Do you want to continue?') }}"
                                                           data-confirm-yes="document.getElementById('{{ $formId }}').submit();">
                                                            <i class="{{ VC::TI_TRS_WT }}"></i>
                                                        </a>
                                                    {!! Form::close() !!}
                                                </div>
                                            @endcan
                                        </td>
                                    @endcanany
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="{{ VC::TXCT_MT }}">{{ __('No payments found.') }}</td>
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

@push(ST::ADM_SCR_PG)
    <script defer src="{{ asset('assets/js/routes/payments/index.js') }}"></script>
@endpush
