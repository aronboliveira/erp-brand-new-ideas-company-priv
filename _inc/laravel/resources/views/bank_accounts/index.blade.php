@php
    try {
$user = Auth::user();
        $lang = Utility::fetchUserLang(user:$user);
    } catch (\Throwable $e) {
        \Log::error('bank_accounts/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    }
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@section(YieldingConstants::ADM_PG_TTL)
    {{__('Manage Bank Account')}}
@endsection
@section(YieldingConstants::ADM_BDC)
    <li class="{{ VC::BCI }}">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
        {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="{{ VC::BCI }}">{{__('Bank Account')}}</li>
@endsection

@section(YieldingConstants::ADM_ACT_BTN)
    <div class="{{ VC::FEND }}">
        @can('create bank account')
            @php
                try {
                    $bankAccountCreateRoute = Route::has(ViewsConstants::BNK_ACC.'.create')
                        ? route(ViewsConstants::BNK_ACC.'.create')
                        : (Route::has(Str::kebab(ViewsConstants::BNK_ACC.'.create'))
                            ? route(Str::kebab(ViewsConstants::BNK_ACC.'.create'))
                            : '#');
                    $createLinkId = 'bank-account-create-link';
                    $createMsg = Utility::fetchLinkMessage(
                        $lang,
                        ViewsConstants::BNK_ACC,
                        'bank_account_create_route_unavailable'
                    ) ?? 'Create New Bank Account route is unavailable. Please contact technical support or your domain administrator.';
                    $guardIds = [$createLinkId];
                } catch (\Throwable $e) {
                    \Log::error('bank_accounts/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                }
@endphp
            <a
                id="{{ $createLinkId }}"
                href="#"
                data-url="{{ $bankAccountCreateRoute }}"
                data-sv-localized="true"
                data-guard-msg="{{ base64_encode($createMsg) }}"
                data-ajax-popup="true"
                data-size="lg"
                data-bs-toggle="tooltip"
                title="{{ __('Create') }}"
                data-title="{{ __('Create New Bank Account') }}"
                class="{{ ViewClassNamesConstants::BT_SM_PM }}"
            >
                <i class="{{ ViewClassNamesConstants::TI_PLS }}"></i>
            </a>

            @push(StacksConstants::ADM_SCR_PG)
                <script defer>
                    (() => {
                        const ids = {!! json_encode($guardIds) !!};
                        const flagAttr = 'data-listener-active';
                        ids.forEach(id => {
                            const el = document.getElementById(id);
                            if (!el || el.getAttribute(flagAttr) === 'true') return;
                            el.setAttribute(flagAttr, 'true');
                            el.addEventListener('click', event => {
                                try {
                                    const url = el.getAttribute('data-url');
                                    const href = el.href.replace(window.location.origin, '').replace(window.location.pathname, '');
                                    if ((!url || url === '#') && (!href || href === '#')) {
                                        event.preventDefault();
                                        const msg = el.getAttribute('data-guard-msg') ?? '# ERROR';
                                        (window.RouteGuard?.showToast || (m => alert(m)))(msg);
                                        el.setAttribute('data-failed-route', 'true');
                                    }
                                } catch {}
                            });
                            const observer = new MutationObserver(() => {
                                if (!document.getElementById(id)) observer.disconnect();
                            });
                            observer.observe(document.body, { childList: true, subtree: true });
                        });
                    })();
                </script>
            @endpush
        @endcan
    </div>
@endsection

@section(YieldingConstants::ADM_CTT)
    <div class="{{ ViewClassNamesConstants::RW }}">
        <div class="{{ ViewClassNamesConstants::C12 }}">
            <div class="{{ ViewClassNamesConstants::CD }}">
                <div class="{{ VC::CD_BD_TB_BD }}">
                    <div class="{{ VC::TB_RSP }}">
                        <table class="{{ ViewClassNamesConstants::TB }} datatable">
                            <thead>
                                <tr>
                                    <th>{{ __('Chart Of Account') }}</th>
                                    <th>{{ __('Name') }}</th>
                                    <th>{{ __('Bank') }}</th>
                                    <th>{{ __('Account Number') }}</th>
                                    <th>{{ __('Current Balance') }}</th>
                                    <th>{{ __('Contact Number') }}</th>
                                    <th>{{ __('Bank Branch') }}</th>
                                    <th width="10%">{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($accounts as $account)
                                    <tr class="font-style">
                                        <td>{{ $account->chartAccount->name ?? __('No name available for Chart') }}</td>
                                        <td>{{ $account->holder_name ?? __('No name available for Holder') }}</td>
                                        <td>{{ $account->bank_name ?? __('No name available for Bank') }}</td>
                                        <td>{{ $account->account_number ?? __('No name available for Account Number') }}</td>
                                        <td>{{ $user->priceFormat($account->opening_balance) ?? __('No name available for Current Balance') }}</td>
                                        <td>{{ $account->contact_number ?? __('No name available for Contact Number') }}</td>
                                        <td>{{ $account->bank_address ?? __('No name available for Bank Branch') }}</td>
                                        @if(Gate::check('edit bank account') || Gate::check('delete bank account'))
                                            <td class="Action">
                                                <span>
                                                    @if($account->holder_name!='Cash')
                                                        @can('edit bank account')
                                                            <div class="{{ ViewClassNamesConstants::ACT_BTN }} {{ ViewClassNamesConstants::BG_P }} {{ ViewClassNamesConstants::MS2 }}">
                                                                @php
                                                                    try {
                                                                        $bnkAccEditBase = ViewsConstants::BNK_ACC.'.edit';
                                                                        $bnkAccEditKebab = Str::kebab($bnkAccEditBase);
                                                                        $bnkAccIdValue = data_get($account,'id','');
                                                                        $bnkAccEditResolved = Route::has($bnkAccEditBase) ? $bnkAccEditBase : (Route::has($bnkAccEditKebab) ? $bnkAccEditKebab : null);
                                                                        $bnkAccEditUrl = ($bnkAccEditResolved && $bnkAccIdValue !== '') ? route($bnkAccEditResolved,$bnkAccIdValue) : '#';
                                                                        $langValue = isset($lang) ? $lang : Utility::fetchUserLang();
                                                                        $bnkAccEditGuardMsg = Utility::fetchLinkMessage($langValue, ViewsConstants::BNK_ACC, 'edit_bank_account_route_unavailable') ?? 'Edit bank account route is unavailable. Please contact technical support or your domain administrator.';
                                                                        $bnkAccEditAnchorId = 'bank-account-edit-'.($bnkAccIdValue === '' ? 'x' : $bnkAccIdValue);
                                                                    } catch (\Throwable $e) {
                                                                        \Log::error('bank_accounts/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                                    }
@endphp
                                                                <a href="{{ $bnkAccEditUrl }}"
                                                                id="{{ $bnkAccEditAnchorId }}"
                                                                class="{{ ViewClassNamesConstants::BT_SM_MX3 }} {{ ViewClassNamesConstants::AL_IT_CT }}"
                                                                data-url="{{ $bnkAccEditUrl }}"
                                                                data-ajax-popup="true"
                                                                data-title="{{ __('Edit Bank Account') }}"
                                                                data-bs-toggle="tooltip"
                                                                data-size="lg"
                                                                title="{{ __('Edit') }}"
                                                                data-guard-msg="{{ base64_encode($bnkAccEditGuardMsg) }}"
                                                                data-sv-localized="true">
                                                                    <i class="{{ ViewClassNamesConstants::TI_PC_WT }}"></i>
                                                                </a>
                                                                @push(StacksConstants::ADM_SCR_PG)
                                                                    <script defer>
                                                                        (() => {
                                                                            try {
                                                                                const el = document.getElementById('{{ $bnkAccEditAnchorId }}');
                                                                                if (!el) { return; }
                                                                                if (el.getAttribute('data-listener-active') === 'true') { return; }
                                                                                el.setAttribute('data-listener-active','true');
                                                                                el.addEventListener('click',(e) => {
                                                                                    try {
                                                                                        const href = el.getAttribute('href') ?? '#';
                                                                                        const url = el.getAttribute('data-url') ?? href ?? '#';
                                                                                        if (url !== '#' && href !== '#') { return; }
                                                                                        e.preventDefault();
                                                                                        const msg = el.getAttribute('data-guard-msg') ?? 'Edit bank account route is unavailable. Please contact technical support or your domain administrator.';
                                                                                        (window.RouteGuard?.showToast || (m => alert(m)))(msg);
                                                                                        el.setAttribute('data-failed-route','true');
                                                                                    } catch (err) {}
                                                                                });
                                                                            } catch (err) {}
                                                                        })();
                                                                    </script>
                                                                @endpush
                                                            </div>
                                                        @endcan
                                                        @can('delete bank account')
                                                            <div class="{{ ViewClassNamesConstants::ACT_BTN_DNG_2 }}">
                                                                @php
                                                                    try {
                                                                        $bnkAccDestroyBase = ViewsConstants::BNK_ACC.'.destroy';
                                                                        $bnkAccDestroyKebab = Str::kebab($bnkAccDestroyBase);
                                                                        $accIdValue = data_get($account,'id','');
                                                                        $bnkAccDestroyResolved = Route::has($bnkAccDestroyBase) ? $bnkAccDestroyBase : (Route::has($bnkAccDestroyKebab) ? $bnkAccDestroyKebab : null);
                                                                        $bnkAccDestroyUrl = ($bnkAccDestroyResolved && $accIdValue !== '') ? route($bnkAccDestroyResolved,$accIdValue) : '#';
                                                                        $langValue = isset($lang) ? $lang : Utility::fetchUserLang();
                                                                        $bnkAccDestroyGuardMsg = Utility::fetchLinkMessage($langValue, ViewsConstants::BNK_ACC, 'destroy_bank_account_route_unavailable') ?? 'Destroy bank account route is unavailable. Please contact technical support or your domain administrator.';
                                                                        $delFormId = 'delete-form-'.($accIdValue === '' ? 'x' : $accIdValue);
                                                                        $delAnchorId = 'delete-bank-account-'.($accIdValue === '' ? 'x' : $accIdValue);
                                                                    } catch (\Throwable $e) {
                                                                        \Log::error('bank_accounts/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                                    }
@endphp
                                                                {!! Collective\Html\FormFacade::open([
                                                                    'method' => 'DELETE',
                                                                    'url' => $bnkAccDestroyUrl,
                                                                    'id' => $delFormId,
                                                                    'data-url' => $bnkAccDestroyUrl,
                                                                    'data-guard-msg' => $bnkAccDestroyGuardMsg,
                                                                    'data-sv-localized' => 'true'
                                                                ]) !!}
                                                                    <a href="#"
                                                                    id="{{ $delAnchorId }}"
                                                                    class="{{ ViewClassNamesConstants::BT_SM_CT_PR }}"
                                                                    data-bs-toggle="tooltip"
                                                                    title="{{ __('Delete') }}"
                                                                    data-confirm="{{ __(Utility::fetchLinkMessage($langValue, 'generics', 'are_you_sure') ?? 'Are You Sure?') }}|{{ __(Utility::fetchLinkMessage($langValue, 'generics', 'irreversible_action') ?? 'This action can not be undone. Do you want to continue?') }}"
                                                                    data-confirm-yes="document.getElementById('{{ $delFormId }}').submit();"
                                                                    data-form-id="{{ $delFormId }}"
                                                                    data-guard-msg="{{ base64_encode($bnkAccDestroyGuardMsg) }}"
                                                                    data-sv-localized="true">
                                                                        <i class="{{ ViewClassNamesConstants::TI_TRS_WT }}"></i>
                                                                    </a>
                                                                {!! Collective\Html\FormFacade::close() !!}
                                                                @push(StacksConstants::ADM_SCR_PG)
                                                                    <script defer>
                                                                        (() => {
                                                                            try {
                                                                                const a = document.getElementById('{{ $delAnchorId }}');
                                                                                const f = document.getElementById('{{ $delFormId }}');
                                                                                if (!a || !f) { return; }
                                                                                if (a.getAttribute('data-listener-active') === 'true') { return; }
                                                                                a.setAttribute('data-listener-active','true');
                                                                                a.addEventListener('click',(e) => {
                                                                                    try {
                                                                                        const fid = a.getAttribute('data-form-id') ?? '';
                                                                                        if (!fid) { return; }
                                                                                        const fm = document.getElementById(fid);
                                                                                        if (!fm) { return; }
                                                                                        const action = fm.getAttribute('action') ?? '#';
                                                                                        const url = fm.getAttribute('data-url') ?? action ?? '#';
                                                                                        if (url !== '#' && action !== '#') { return; }
                                                                                        e.preventDefault();
                                                                                        const msg = a.getAttribute('data-guard-msg') ?? 'Destroy bank account route is unavailable. Please contact technical support or your domain administrator.';
                                                                                        (window.RouteGuard?.showToast || (m => alert(m)))(msg);
                                                                                        a.setAttribute('data-failed-route','true');
                                                                                        fm.setAttribute('data-failed-route','true');
                                                                                    } catch (err) {}
                                                                                });
                                                                                if (f.getAttribute('data-submit-guarded') !== 'true') {
                                                                                    f.setAttribute('data-submit-guarded','true');
                                                                                    f.addEventListener('submit',(e) => {
                                                                                        try {
                                                                                            const action = f.getAttribute('action') ?? '#';
                                                                                            const url = f.getAttribute('data-url') ?? action ?? '#';
                                                                                            if (url !== '#' && action !== '#') { return; }
                                                                                            e.preventDefault();
                                                                                            const msg = f.getAttribute('data-guard-msg') ?? 'Destroy bank account route is unavailable. Please contact technical support or your domain administrator.';
                                                                                            (window.RouteGuard?.showToast || (m => alert(m)))(msg);
                                                                                            f.setAttribute('data-failed-route','true');
                                                                                        } catch (err) {}
                                                                                    });
                                                                                }
                                                                            } catch (err) {}
                                                                        })();
                                                                    </script>
                                                                @endpush
                                                            </div>
                                                        @endcan
                                                    @else
                                                        -
                                                    @endif
                                                </span>
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
