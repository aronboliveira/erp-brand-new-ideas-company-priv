@php
    $account ??= [];
    try {
$user = Auth::user();
        $lang = Utility::fetchUserLang(user:$user);
    } catch (\Throwable $e) {
        \Log::error('bank_transfers/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    }
@endphp

@extends(ExtendingLayoutsConstants::ADM)

@section(YieldingConstants::ADM_PG_TTL)
    {{ __('Bank Balance Transfer') }}
@endsection

@section(YieldingConstants::ADM_BDC)
    <li class="{{ VC::BCI }}">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
           {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="{{ VC::BCI }}">{{ __('Bank Balance Transfer') }}</li>
@endsection

@section(YieldingConstants::ADM_ACT_BTN)
    @can('create bank transfer')
        @php
            try {
                $createUrl = Route::has(ViewsConstants::BNK_TRF.'.create')
                    ? route(ViewsConstants::BNK_TRF.'.create')
                    : '#';
                $unavail = Utility::fetchLinkMessage(
                    $lang,
                    ViewsConstants::BNK_TRF,
                    'create_bank_transfer_unavailable'
                ) ?? __('Create bank transfer route is unavailable. Please contact support.');
            } catch (\Throwable $e) {
                \Log::error('bank_transfers/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
            }
@endphp
        <a href="{{ $createUrl }}"
           data-url="{{ $createUrl }}"
           data-ajax-popup="true"
           data-title="{{ __('Create Bank-Transfer') }}"
           data-unavailable-msg="{{ $unavail }}"
           class="{{ VC::BT_SM_PM }}"
           data-create-listener-added="false"
           data-bs-toggle="tooltip"
           title="{{ __('Create') }}">
            <i class="{{ VC::TI_PLS }}"></i>
        </a>
    @endcan
@endsection

@push(StacksConstants::ADM_SCR_PG)
    <script defer>
        (() => {
            const btn = document.querySelector('a.btn-sm.btn-primary[data-ajax-popup]');
            if (btn && btn.getAttribute('data-create-listener-added') !== 'true') {
                btn.setAttribute('data-create-listener-added', 'true');
                btn.addEventListener('click', e => {
                    e.preventDefault();
                    const url = btn.getAttribute('data-url');
                    if (!url || url === '#') {
                        const msg = btn.getAttribute('data-unavailable-msg');
                        if (window.bootstrap?.Toast) {
                            const toastEl = document.querySelector('.toast'),
                                t = new bootstrap.Toast(toastEl),
                                body = toastEl.querySelector('.toast-body');
                            body && (body.textContent = msg);
                            t.show();
                        } else alert(msg);
                        return;
                    }
                    window.location.href = url;
                });
            }
        })();
    </script>
@endpush

@section(YieldingConstants::ADM_CTT)
    <div class="{{ VC::RW }} mb-3">
        @php
            try {
                $bnkTrfIndexBase = ViewsConstants::BNK_TRF.'.index';
                $bnkTrfIndexKebab = Str::kebab($bnkTrfIndexBase);
                $bnkTrfIndexResolved = Route::has($bnkTrfIndexBase) ? $bnkTrfIndexBase : (Route::has($bnkTrfIndexKebab) ? $bnkTrfIndexKebab : null);
                $bnkTrfIndexUrl = $bnkTrfIndexResolved ? route($bnkTrfIndexResolved) : '#';
                $langValue = isset($lang) ? $lang : Utility::fetchUserLang();
                $applyGuardMsg = Utility::fetchLinkMessage($langValue, ViewsConstants::BNK_TRF, 'apply_bank_transfer_route_unavailable') ?? 'Apply bank transfer route is unavailable. Please contact technical support or your domain administrator.';
                $resetGuardMsg = Utility::fetchLinkMessage($langValue, ViewsConstants::BNK_TRF, 'reset_bank_transfer_route_unavailable') ?? 'Reset bank transfer route is unavailable. Please contact technical support or your domain administrator.';
                $formId = 'transfer_form';
                $applyId = 'transfer-apply';
                $resetId = 'transfer-reset';
            } catch (\Throwable $e) {
                \Log::error('bank_transfers/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
            }
@endphp
        {{ Form::open([
            'method' => 'GET',
            'url' => $bnkTrfIndexUrl,
            'id' => $formId,
            'data-url' => $bnkTrfIndexUrl,
            'data-guard-msg' => $applyGuardMsg,
            'data-sv-localized' => 'true',
        ]) }}
            <div class="{{ VC::R_ALC_JCE }}">
                <div class="{{ VC::CLMS10 }}">
                    <div class="{{ VC::RW }}">
                        <div class="{{ VC::CM3 }} month">
                            {{ Form::label('date', __('Date'), ['class'=>VC::FM_LB]) }}
                            {{ Form::text('date', request('date'), ['class'=>VC::FM_CT.' month-btn','id'=>'pc-daterangepicker-1','readonly']) }}
                        </div>
                        <div class="{{ VC::CM3 }} date">
                            {{ Form::label('f_account', __('From Account'), ['class'=>VC::FM_LB]) }}
                            {{ Form::select('f_account', $account, request('f_account'), ['class'=>VC::FM_CT_SL]) }}
                        </div>
                        <div class="{{ VC::CM3 }}">
                            {{ Form::label('t_account', __('To Account'), ['class'=>VC::FM_LB]) }}
                            {{ Form::select('t_account', $account, request('t_account'), ['class'=>VC::FM_CT_SL]) }}
                        </div>
                    </div>
                </div>
                <div class="{{ VC::C_AT_FEND }}">
                    <a href="#"
                    id="{{ $applyId }}"
                    class="{{ VC::BT_SM_PM }}"
                    data-form-id="{{ $formId }}"
                    data-guard-msg="{{ base64_encode($applyGuardMsg) }}"
                    data-sv-localized="true"
                    data-bs-toggle="tooltip"
                    title="{{ __('Apply') }}">
                        <i class="{{ VC::TI_SRC }}"></i>
                    </a>
                    <a href="{{ $bnkTrfIndexUrl }}"
                    id="{{ $resetId }}"
                    class="{{ VC::BT_SM_DG }} ms-2"
                    data-url="{{ $bnkTrfIndexUrl }}"
                    data-guard-msg="{{ base64_encode($resetGuardMsg) }}"
                    data-sv-localized="true"
                    data-bs-toggle="tooltip"
                    title="{{ __('Reset') }}">
                        <i class="{{ VC::TI_TRS_OFF }}"></i>
                    </a>
                </div>
            </div>
        {{ Form::close() }}
        @push(StacksConstants::ADM_SCR_PG)
            <script defer src="{{ asset('assets/js/routes/bank/transfers/index.js') }}"></script>
        @endpush
    </div>
    <div class="{{ VC::RW }}">
        <div class="{{ VC::C12 }}">
            <div class="{{ VC::CD }}">
                <div class="{{ VC::CD_MT }} table-border-style">
                    <div class="{{ VC::TABLE_RESPONSIVE }}">
                        <table class="{{ VC::TB }} datatable">
                            <thead>
                                <tr>
                                    <th>{{ __('Date') }}</th>
                                    <th>{{ __('From Account') }}</th>
                                    <th>{{ __('To Account') }}</th>
                                    <th>{{ __('Amount') }}</th>
                                    <th>{{ __('Reference') }}</th>
                                    <th>{{ __('Description') }}</th>
                                    @if(Gate::check('edit transfer') || Gate::check('delete transfer'))
                                        <th width="10%">{{ __('Action') }}</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @php
 $transfersList=(!empty($transfers) ? (is_array($transfers) && count($transfers) ? $transfers : (($transfers instanceof \Illuminate\Support\Collection && $transfers->isNotEmpty()) ? $transfers : [])) : []);
@endphp
                                @forelse($transfersList as $t)
                                    @php
                                        try {
                                            $from=is_callable([$t,'fromBankAccount'])?$t->fromBankAccount():null;
                                            $to=is_callable([$t,'toBankAccount'])?$t->toBankAccount():null;
                                            $tid=data_get($t,'id');
                                            $transferEditRoute=Route::has(ViewsConstants::BNK_TRF.'.edit')?route(ViewsConstants::BNK_TRF.'.edit',$tid):(Route::has(Str::kebab(ViewsConstants::BNK_TRF.'.edit'))?route(Str::kebab(ViewsConstants::BNK_TRF.'.edit'),$tid):'#');
                                            $transferEditBtnId='transfer-edit-'.$tid;
                                            $transferEditMsg=Utility::fetchLinkMessage($lang,ViewsConstants::BNK_TRF,'transfer_edit_route_unavailable')??__('Failed to get transfer edit route');
                                            $transferDestroyRoute=Route::has(ViewsConstants::BNK_TRF.'.destroy')?route(ViewsConstants::BNK_TRF.'.destroy',$tid):(Route::has(Str::kebab(ViewsConstants::BNK_TRF.'.destroy'))?route(Str::kebab(ViewsConstants::BNK_TRF.'.destroy'),$tid):'#');
                                            $transferDeleteBtnId='transfer-delete-'.$tid;
                                            $transferDeleteFormId='transfer-delete-form-'.$tid;
                                            $transferDestroyMsg=Utility::fetchLinkMessage($lang,ViewsConstants::BNK_TRF,'transfer_destroy_route_unavailable')??__('Failed to get transfer destroy route');
                                        } catch (\Throwable $e) {
                                            \Log::error('bank_transfers/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                        }
@endphp
                                    <tr>
                                        <td>{{ $user?->dateFormat(data_get($t,'date')) ?? __('Failed to format transfer date') }}</td>
                                        <td>{{ data_get($from,'bank_name') ?? __('No bank name available') }} {{ data_get($from,'holder_name') ?? __('No account holder name available') }}</td>
                                        <td>{{ data_get($to,'bank_name') ?? __('No bank name available') }} {{ data_get($to,'holder_name') ?? __('No account holder name available') }}</td>
                                        <td>{{ $user?->priceFormat(data_get($t,'amount')) ?? __('Failed to format amount') }}</td>
                                        <td>{{ data_get($t,'reference') ?: __('No reference available') }}</td>
                                        <td>{{ data_get($t,'description') ?: __('No description available') }}</td>
                                        @if(Gate::check('edit transfer') || Gate::check('delete transfer'))
                                            <td class="Action">
                                                @can('edit transfer')
                                                    <div class="{{ VC::ACT_BTN_PRIM }}">
                                                        <a id="{{ $transferEditBtnId }}" href="{{ $transferEditRoute }}" data-url="{{ $transferEditRoute }}" data-guard-msg="{{ base64_encode($transferEditMsg) }}" data-ajax-popup="true" data-title="{{ __('Edit Transfer') }}" class="{{ VC::BT_SM_CT }} ms-2" data-bs-toggle="tooltip" title="{{ __('Edit') }}"><i class="{{ VC::TI_PC_WT }}"></i></a>
                                                    </div>
                                                @endcan
                                                @can('delete transfer')
                                                    <div class="{{ VC::ACT_BTN_DNG_2 }}">
                                                        {!! Form::open(['url'=>$transferDestroyRoute,'method'=>'DELETE','id'=>$transferDeleteFormId,'data-url'=>$transferDestroyRoute,'data-guard-msg'=>$transferDestroyMsg]) !!}
                                                            <a id="{{ $transferDeleteBtnId }}" href="#" class="{{ VC::BT_SM_CT_PR }}" data-bs-toggle="tooltip" title="{{ __('Delete') }}" data-confirm="{{ __(Utility::fetchLinkMessage($lang,'generics','are_you_sure') ?? 'Are You Sure?') }}|{{ __(Utility::fetchLinkMessage($lang,'generics','irreversible_action') ?? 'This action can not be undone. Do you want to continue?') }}" data-confirm-yes="document.getElementById('{{ $transferDeleteFormId }}').submit();"><i class="{{ VC::TI_TRS_WT }}"></i></a>
                                                        {!! Form::close() !!}
                                                    </div>
                                                @endcan
                                            </td>
                                            @push(StacksConstants::ADM_SCR_PG)
                                                <script defer>
                                                    (()=>{const g=id=>{const b=document.getElementById(id);if(!b||b.getAttribute('data-listener-active')==='true')return;b.setAttribute('data-listener-active','true');b.addEventListener('click',e=>{try{const h=b.getAttribute('href');const u=b.getAttribute('data-url');if((h&&h!=='#')||(u&&u!=='#'))return;e.preventDefault();const m=b.getAttribute('data-guard-msg')??'{{ __('Failed action') }}';const hasB=document.querySelector('link[href*="bootstrap"]')&&window.bootstrap;let c=document.getElementById('toast-container');if(!c){c=document.createElement('div');c.id='toast-container';document.body.appendChild(c);}if(hasB){const t=document.createElement('div');t.className='toast';t.setAttribute('role','alert');t.setAttribute('aria-live','assertive');t.setAttribute('aria-atomic','true');const body=document.createElement('div');body.className='toast-body';body.textContent=m;t.appendChild(body);c.appendChild(t);bootstrap.Toast.getOrCreateInstance(t).show();}else{alert(m);}b.setAttribute('data-failed-route','true');}catch(_){}});};g('{{ $transferEditBtnId }}');g('{{ $transferDeleteBtnId }}');})();
                                                </script>
                                            @endpush
                                        @endif
                                    </tr>
                                @empty
                                    <tr><td colspan="7" class="{{ VC::TXCT_MT }}">{{ __('No transfers available') }}</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
