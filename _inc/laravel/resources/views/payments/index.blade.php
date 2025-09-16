@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants as EL,
        StacksConstants as ST,
        ViewsConstants as VW,
        ViewClassNamesConstants as VC,
        YieldingConstants as YD
    };
    use App\Models\Utility;
    use Collective\Html\FormFacade as Form;
    use Illuminate\Support\Facades\{Auth, Route};
    use Illuminate\Support\Collection;

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
@endphp

@extends(EL::ADM)

@section(YD::ADM_PG_TTL)
    {{ __('Manage Payments') }}
@endsection

@section(YD::ADM_BDC)
    <li class="breadcrumb-item">
        <a href="{{ $dashUrl }}"
           data-url="{{ $dashUrl }}"
           data-sv-localized="true"
           data-guard-msg="{{ $dashGuard }}"
           {{ $dashUrl !== '#' ? '' : 'aria-disabled=true' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="breadcrumb-item">{{ __('Payment') }}</li>
@endsection

@section(YD::ADM_ACT_BTN)
    <div class="float-end">
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
               data-guard-msg="{{ $createGuard }}"
               data-bs-toggle="tooltip"
               title="{{ __('Create') }}"
               class="btn btn-sm btn-primary">
                <i class="ti ti-plus"></i>
            </a>
        @endcan
    </div>
@endsection

@section(YD::ADM_CTT)
    <div class="row">
        <div class="col-sm-12">
            <div class="mt-2" id="multiCollapseExample1">
                <div class="card">
                    <div class="card-body">
                        {{ Form::open(['route' => [VW::PAY.'.index'],'method' => 'GET','id'=>'payment_form']) }}
                        <div class="row align-items-center justify-content-end">
                            <div class="col-xl-10">
                                <div class="row">
                                    <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                                        <div class="btn-box">
                                            {{ Form::label('date', __('Date'),['class'=>'form-label']) }}
                                            {{ Form::date('date', request('date',''), ['class' => 'form-control month-btn','id'=>'pc-daterangepicker-1']) }}
                                        </div>
                                    </div>
                                    <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                                        <div class="btn-box">
                                            {{ Form::label('account', __('Account'),['class'=>'form-label']) }}
                                            {{ Form::select('account',$account ?? [], request('account',''), ['class' => 'form-control select','id'=>'choices-multiple']) }}
                                        </div>
                                    </div>
                                    <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                                        <div class="btn-box">
                                            {{ Form::label('vendor', __('Vendor'),['class'=>'form-label']) }}
                                            {{ Form::select('vendor',$vendor ?? [], request('vendor',''), ['class' => 'form-control select','id'=>'choices-multiple1']) }}
                                        </div>
                                    </div>
                                    <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                                        <div class="btn-box">
                                            {{ Form::label('category', __('Category'),['class'=>'form-label']) }}
                                            {{ Form::select('category',$category ?? [], request('category',''), ['class' => 'form-control select','id'=>'choices-multiple2']) }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-auto mt-4">
                                <div class="row">
                                    <div class="col-auto">
                                        <a href="#" class="btn btn-sm btn-primary"
                                           onclick="document.getElementById('payment_form').submit(); return false;"
                                           data-bs-toggle="tooltip" title="{{__('Apply')}}">
                                            <span class="btn-inner--icon"><i class="ti ti-search"></i></span>
                                        </a>
                                        @php
                                            $resetUrl = Route::has(VW::PAY.'.index') ? route(VW::PAY.'.index') : '#';
                                            $resetGuard = (is_callable([Utility::class,'fetchLinkMessage']) ? Utility::fetchLinkMessage($lang, VW::PAY, 'index_route_unavailable') : null) ?? __('Index payment route is unavailable. Please contact technical support or your domain administrator.');
                                        @endphp
                                        <a href="{{ $resetUrl }}" class="btn btn-sm btn-danger"
                                           data-url="{{ $resetUrl }}"
                                           data-sv-localized="true"
                                           data-guard-msg="{{ $resetGuard }}"
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
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
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
                                            @php $fileUrl = rtrim($filesBase,'/').'/'.$receipt; @endphp
                                            <a class="action-btn bg-primary ms-2 btn btn-sm align-items-center" href="{{ $fileUrl }}" download>
                                                <i class="ti ti-download text-white"></i>
                                            </a>
                                            <a href="{{ $fileUrl }}" target="_blank" class="action-btn bg-secondary ms-2 mx-3 btn btn-sm align-items-center" data-bs-toggle="tooltip" title="{{ __('View') }}">
                                                <span class="btn-inner--icon"><i class="ti ti-crosshair text-white"></i></span>
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
                                                <div class="action-btn bg-primary ms-2">
                                                    <a href="{{ $editUrl }}"
                                                       class="mx-3 btn btn-sm align-items-center"
                                                       data-url="{{ $editUrl }}"
                                                       data-ajax-popup="true"
                                                       data-size="lg"
                                                       data-title="{{ __('Edit Payment') }}"
                                                       data-sv-localized="true"
                                                       data-guard-msg="{{ $editGuard }}"
                                                       data-bs-toggle="tooltip"
                                                       title="{{ __('Edit') }}">
                                                        <i class="{{ VC::TI_PC_WT }}"></i>
                                                    </a>
                                                </div>
                                            @endcan
                                            @can('delete payment')
                                                @php
                                                    $delUrl   = Route::has(VW::PAY.'.destroy') ? route(VW::PAY.'.destroy', $payment->id) : '#';
                                                    $delGuard = (is_callable([Utility::class,'fetchLinkMessage']) ? Utility::fetchLinkMessage($lang, VW::PAY, 'delete_payment_unavailable') : 'Delete payment route is unavailable. Please contact technical support or your domain administrator.') ?? __('Delete payment route is unavailable. Please contact technical support or your domain administrator.');
                                                    $formId   = 'delete-form-'.$payment->id;
                                                @endphp
                                                <div class="action-btn bg-danger ms-2">
                                                    {!! Form::open([
                                                        'method' => 'DELETE',
                                                        'url'    => $delUrl,
                                                        'id'     => $formId,
                                                        'data-url' => $delUrl,
                                                        'data-sv-localized' => 'true',
                                                        'data-guard-msg' => $delGuard
                                                    ]) !!}
                                                        <a href="{{ $delUrl }}"
                                                           class="mx-3 btn btn-sm align-items-center bs-pass-para"
                                                           data-url="{{ $delUrl }}"
                                                           data-sv-localized="true"
                                                           data-guard-msg="{{ $delGuard }}"
                                                           data-bs-toggle="tooltip"
                                                           title="{{ __('Delete') }}"
                                                           data-confirm="{{ __(Utility::fetchLinkMessage($lang, 'generics', 'are_you_sure') ?? 'Are You Sure?') }}|{{ __(Utility::fetchLinkMessage($lang, 'generics', 'irreversible_action') ?? 'This action can not be undone. Do you want to continue?') }}"
                                                           data-confirm-yes="document.getElementById('{{ $formId }}').submit();">
                                                            <i class="ti ti-trash text-white"></i>
                                                        </a>
                                                    {!! Form::close() !!}
                                                </div>
                                            @endcan
                                        </td>
                                    @endcanany
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center text-muted">{{ __('No payments found.') }}</td>
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
