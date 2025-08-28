@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        StacksConstants,
        ViewsConstants,
        ViewClassNamesConstants as VC,
        YieldingConstants,
    };
    use App\Models\Utility;
    use Collective\Html\FormFacade as Form;
    use Illuminate\Support\Facades\{Auth, Route};
    $user = Auth::user();
    $lang = Utility::fetchUserLang(user:$user);
@endphp

@extends(ExtendingLayoutsConstants::ADM)

@section(YieldingConstants::ADM_PG_TTL)
    {{ __('Bank Balance Transfer') }}
@endsection

@section(YieldingConstants::ADM_BDC)
    <li class="breadcrumb-item">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
           {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="breadcrumb-item">{{ __('Bank Balance Transfer') }}</li>
@endsection

@section(YieldingConstants::ADM_ACT_BTN)
    @can('create bank transfer')
        @php
            $createUrl = Route::has(ViewsConstants::BNK_TRF.'.create')
                ? route(ViewsConstants::BNK_TRF.'.create')
                : '#';
            $unavail = Utility::fetchLinkMessage(
                $lang,
                ViewsConstants::BNK_TRF, 
                'create_bank_transfer_unavailable'
            ) ?? __('Create bank transfer route is unavailable. Please contact support.');
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
            const btn = document.querySelector('a.{{ VC::BT_SM_PM }}[data-ajax-popup]');
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
                    data-guard-msg="{{ $applyGuardMsg }}"
                    data-sv-localized="true"
                    data-bs-toggle="tooltip"
                    title="{{ __('Apply') }}">
                        <i class="{{ VC::TI_SRC }}"></i>
                    </a>
                    <a href="{{ $bnkTrfIndexUrl }}"
                    id="{{ $resetId }}"
                    class="{{ VC::BT_SM_DG }} ms-2"
                    data-url="{{ $bnkTrfIndexUrl }}"
                    data-guard-msg="{{ $resetGuardMsg }}"
                    data-sv-localized="true"
                    data-bs-toggle="tooltip"
                    title="{{ __('Reset') }}">
                        <i class="{{ VC::TI_TRS_OFF }}"></i>
                    </a>
                </div>
            </div>
        {{ Form::close() }}
        @push(StacksConstants::ADM_SCR_PG)
            <script defer>
                (() => {
                    try {
                        const fm = document.getElementById('{{ $formId }}');
                        if (fm && fm.getAttribute('data-submit-guarded') !== 'true') {
                            fm.setAttribute('data-submit-guarded','true');
                            fm.addEventListener('submit',(e) => {
                                try {
                                    const action = fm.getAttribute('action') ?? '#';
                                    const url = fm.getAttribute('data-url') ?? action ?? '#';
                                    if (url !== '#' && action !== '#') { return; }
                                    e.preventDefault();
                                    const msg = fm.getAttribute('data-guard-msg') ?? 'Apply bank transfer route is unavailable. Please contact technical support or your domain administrator.';
                                    const hasBootstrap = !!(document.querySelector('link[href*="bootstrap"]') && window.bootstrap);
                                    let container = document.getElementById('toast-container');
                                    if (!container) {
                                        container = document.createElement('div');
                                        container.id = 'toast-container';
                                        document.body.appendChild(container);
                                    }
                                    if (hasBootstrap) {
                                        const toast = document.createElement('div');
                                        toast.className = 'toast';
                                        toast.setAttribute('role','alert');
                                        toast.setAttribute('aria-live','assertive');
                                        toast.setAttribute('aria-atomic','true');
                                        const body = document.createElement('div');
                                        body.className = 'toast-body';
                                        body.textContent = msg;
                                        toast.appendChild(body);
                                        container.appendChild(toast);
                                        bootstrap.Toast.getOrCreateInstance(toast).show();
                                    } else {
                                        alert(msg);
                                    }
                                    fm.setAttribute('data-failed-route','true');
                                } catch (err) {}
                            });
                        }
                        const apply = document.getElementById('{{ $applyId }}');
                        if (apply && apply.getAttribute('data-listener-active') !== 'true') {
                            apply.setAttribute('data-listener-active','true');
                            apply.addEventListener('click',(e) => {
                                try {
                                    e.preventDefault();
                                    const fid = apply.getAttribute('data-form-id') ?? '';
                                    if (!fid) { return; }
                                    const form = document.getElementById(fid);
                                    if (!form) { return; }
                                    const action = form.getAttribute('action') ?? '#';
                                    const url = form.getAttribute('data-url') ?? action ?? '#';
                                    if (url === '#' || action === '#') {
                                        const msg = apply.getAttribute('data-guard-msg') ?? 'Apply bank transfer route is unavailable. Please contact technical support or your domain administrator.';
                                        const hasBootstrap = !!(document.querySelector('link[href*="bootstrap"]') && window.bootstrap);
                                        let container = document.getElementById('toast-container');
                                        if (!container) {
                                            container = document.createElement('div');
                                            container.id = 'toast-container';
                                            document.body.appendChild(container);
                                        }
                                        if (hasBootstrap) {
                                            const toast = document.createElement('div');
                                            toast.className = 'toast';
                                            toast.setAttribute('role','alert');
                                            toast.setAttribute('aria-live','assertive');
                                            toast.setAttribute('aria-atomic','true');
                                            const body = document.createElement('div');
                                            body.className = 'toast-body';
                                            body.textContent = msg;
                                            toast.appendChild(body);
                                            container.appendChild(toast);
                                            bootstrap.Toast.getOrCreateInstance(toast).show();
                                        } else {
                                            alert(msg);
                                        }
                                        apply.setAttribute('data-failed-route','true');
                                        form.setAttribute('data-failed-route','true');
                                        return;
                                    }
                                    form.submit();
                                } catch (err) {}
                            });
                        }
                        const reset = document.getElementById('{{ $resetId }}');
                        if (reset && reset.getAttribute('data-listener-active') !== 'true') {
                            reset.setAttribute('data-listener-active','true');
                            reset.addEventListener('click',(e) => {
                                try {
                                    const href = reset.getAttribute('href') ?? '#';
                                    const url = reset.getAttribute('data-url') ?? href ?? '#';
                                    if (url !== '#' && href !== '#') { return; }
                                    e.preventDefault();
                                    const msg = reset.getAttribute('data-guard-msg') ?? 'Reset bank transfer route is unavailable. Please contact technical support or your domain administrator.';
                                    const hasBootstrap = !!(document.querySelector('link[href*="bootstrap"]') && window.bootstrap);
                                    let container = document.getElementById('toast-container');
                                    if (!container) {
                                        container = document.createElement('div');
                                        container.id = 'toast-container';
                                        document.body.appendChild(container);
                                    }
                                    if (hasBootstrap) {
                                        const toast = document.createElement('div');
                                        toast.className = 'toast';
                                        toast.setAttribute('role','alert');
                                        toast.setAttribute('aria-live','assertive');
                                        toast.setAttribute('aria-atomic','true');
                                        const body = document.createElement('div');
                                        body.className = 'toast-body';
                                        body.textContent = msg;
                                        toast.appendChild(body);
                                        container.appendChild(toast);
                                        bootstrap.Toast.getOrCreateInstance(toast).show();
                                    } else {
                                        alert(msg);
                                    }
                                    reset.setAttribute('data-failed-route','true');
                                } catch (err) {}
                            });
                        }
                    } catch (err) {}
                })();
            </script>
        @endpush
    </div>
    <div class="{{ VC::RW }}">
        <div class="col-12">
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
                            @foreach($transfers as $t)
                                <tr>
                                    <td>{{ $user?->dateFormat($t->date) }}</td>
                                    <td>
                                        {{ optional($t->fromBankAccount())->bank_name }}
                                        {{ optional($t->fromBankAccount())->holder_name }}
                                    </td>
                                    <td>
                                        {{ optional($t->toBankAccount())->bank_name }}
                                        {{ optional($t->toBankAccount())->holder_name }}
                                    </td>
                                    <td>{{ $user?->priceFormat($t->amount) }}</td>
                                    <td>{{ $t->reference }}</td>
                                    <td>{{ $t->description }}</td>
                                    @if(Gate::check('edit transfer') || Gate::check('delete transfer'))
                                        @php
                                            $transferEditRoute    = Route::has(ViewsConstants::BNK_TRF . '.edit')
                                                ? route(ViewsConstants::BNK_TRF . '.edit', $t->id)
                                                : (Route::has(Str::kebab(ViewsConstants::BNK_TRF . '.edit'))
                                                    ? route(Str::kebab(ViewsConstants::BNK_TRF . '.edit'), $t->id)
                                                    : '#');
                                            $transferEditBtnId    = 'transfer-edit-' . $t->id;
                                            $transferEditMsg      = Utility::fetchLinkMessage(
                                                $lang,
                                                ViewsConstants::BNK_TRF,
                                                'transfer_edit_route_unavailable'
                                            ) ?? 'Transfer edit route is unavailable. Please contact technical support or your domain administrator.';
                                            $transferDestroyRoute = Route::has(ViewsConstants::BNK_TRF . '.destroy')
                                                ? route(ViewsConstants::BNK_TRF . '.destroy', $t->id)
                                                : (Route::has(Str::kebab(ViewsConstants::BNK_TRF . '.destroy'))
                                                    ? route(Str::kebab(ViewsConstants::BNK_TRF . '.destroy'), $t->id)
                                                    : '#');
                                            $transferDeleteBtnId  = 'transfer-delete-' . $t->id;
                                            $transferDeleteFormId = 'transfer-delete-form-' . $t->id;
                                            $transferDestroyMsg   = Utility::fetchLinkMessage(
                                                $lang,
                                                ViewsConstants::BNK_TRF,
                                                'transfer_destroy_route_unavailable'
                                            ) ?? 'Transfer destroy route is unavailable. Please contact technical support or your domain administrator.';
                                        @endphp
                                        <td class="Action">
                                            @can('edit transfer')
                                                <div class="{{ VC::ACT_BTN_PRIM }}">
                                                    <a
                                                        id="{{ $transferEditBtnId }}"
                                                        href="{{ $transferEditRoute }}"
                                                        data-url="{{ $transferEditRoute }}"
                                                        data-guard-msg="{{ $transferEditMsg }}"
                                                        data-ajax-popup="true"
                                                        data-title="{{ __('Edit Transfer') }}"
                                                        class="{{ VC::BT_SM_CT }} ms-2"
                                                        data-bs-toggle="tooltip"
                                                        title="{{ __('Edit') }}"
                                                    >
                                                        <i class="{{ VC::TI_PC_WT }}"></i>
                                                    </a>
                                                </div>
                                            @endcan
                                            @can('delete transfer')
                                                <div class="{{ VC::ACT_BTN_DNG_2 }}">
                                                    {!! Form::open([
                                                        'route'            => $transferDestroyRoute,
                                                        'method'         => 'DELETE',
                                                        'id'             => $transferDeleteFormId,
                                                        'data-url'       => $transferDestroyRoute,
                                                        'data-guard-msg' => $transferDestroyMsg,
                                                    ]) !!}
                                                        <a
                                                            id="{{ $transferDeleteBtnId }}"
                                                            href="#"
                                                            class="{{ VC::BT_SM_CT_PR }}"
                                                            data-bs-toggle="tooltip"
                                                            title="{{ __('Delete') }}"
                                                            data-confirm="{{ __(Utility::fetchLinkMessage($lang, 'generics', 'are_you_sure') ?? 'Are You Sure?') }}|{{ __(Utility::fetchLinkMessage($lang, 'generics', 'irreversible_action') ?? 'This action can not be undone. Do you want to continue?') }}"
                                                            data-confirm-yes="document.getElementById('{{ $transferDeleteFormId }}').submit();"
                                                        >
                                                            <i class="{{ VC::TI_TRS_WT }}"></i>
                                                        </a>
                                                    {!! Form::close() !!}
                                                </div>
                                            @endcan
                                        </td>
                                        @push(StacksConstants::ADM_SCR_PG)
                                            <script defer>
                                                (() => {
                                                    const guard = id => {
                                                        const btn = document.getElementById(id);
                                                        if (!btn || btn.getAttribute('data-listener-active') === 'true') return;
                                                        btn.setAttribute('data-listener-active', 'true');
                                                        btn.addEventListener('click', event => {
                                                            try {
                                                                const href = btn.getAttribute('href');
                                                                const url  = btn.getAttribute('data-url');
                                                                if ((href && href !== '#') || (url && url !== '#')) return;
                                                                event.preventDefault();
                                                                const msg = btn.getAttribute('data-guard-msg') ?? '# ERROR';
                                                                const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                                                                let container = document.getElementById('toast-container');
                                                                if (!container) {
                                                                    container = document.createElement('div');
                                                                    container.id = 'toast-container';
                                                                    document.body.appendChild(container);
                                                                }
                                                                if (bootstrapLink && window.bootstrap) {
                                                                    const toastEl = document.createElement('div');
                                                                    toastEl.className = 'toast';
                                                                    toastEl.setAttribute('role', 'alert');
                                                                    toastEl.setAttribute('aria-live', 'assertive');
                                                                    toastEl.setAttribute('aria-atomic', 'true');
                                                                    const body = document.createElement('div');
                                                                    body.className = 'toast-body';
                                                                    body.textContent = msg;
                                                                    toastEl.appendChild(body);
                                                                    container.appendChild(toastEl);
                                                                    bootstrap.Toast.getOrCreateInstance(toastEl).show();
                                                                } else {
                                                                    alert(msg);
                                                                }
                                                                btn.setAttribute('data-failed-route', 'true');
                                                            } catch (e) {}
                                                        });
                                                    };
                                        
                                                    guard('{{ $transferEditBtnId }}');
                                                    guard('{{ $transferDeleteBtnId }}');
                                                })();
                                            </script>
                                        @endpush
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
