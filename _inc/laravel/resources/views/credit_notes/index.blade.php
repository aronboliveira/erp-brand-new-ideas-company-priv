@php
    try {
$user = Auth::user();
        $lang = Utility::fetchUserLang(user:$user);
        $createName     = ViewsConstants::INV . '.custom.credit.note';
        $createRoute    = Route::has($createName)
            ? route($createName)
            : '#';
        $createGuardMsg = Utility::fetchLinkMessage(
            $lang,
            ViewsConstants::INV,
            'custom_credit_note_route_unavailable'
        ) ?? 'Create custom credit note route is unavailable. Please contact technical support or your domain administrator.';
    } catch (\Throwable $e) {
        \Log::error('credit_notes/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    }
    $lang ??= 'en';
    $createRoute ??= '#';
    $createGuardMsg ??= '';
@endphp

@extends(ExtendingLayoutsConstants::ADM)
@section(YieldingConstants::ADM_PG_TTL)
    {{__('Manage Credit Notes')}}
@endsection
@push(StacksConstants::ADM_SCR_PG)
    <script src="{{ asset('assets/js/core/route-guard.js') }}"></script>
    <script async src="{{ asset('asset/js/routes/creditNotes/lang/index.js') }}">
    </script>
    <script defer>
        (() => {
        const ERR_FB = '# ERROR';
        const CLIENT_FLAG = 'data-client-localized';
        const GUARD_MSG = 'data-guard-msg';
        const LANG_KEY = 'erp-np-lang';
        let errorMessage = '';

        const getMsg = (key, el) => {
            let msg = ERR_FB;
            if (el.getAttribute(CLIENT_FLAG) === 'true') {
            msg = el.getAttribute(GUARD_MSG) || msg;
            } else {
            let lang = (sessionStorage.getItem(LANG_KEY) || document.documentElement.lang || 'en')
                .toLowerCase().replace(/_/g, '-');
            lang = lang === 'pt-br' ? lang : lang.slice(0,2);
            msg = window.translations?.[lang]?.[key]
                || el.getAttribute(GUARD_MSG)
                || window.translations?.['en']?.[key]
                || msg;
            if (msg !== ERR_FB) {
                el.setAttribute(GUARD_MSG, msg);
                el.setAttribute(CLIENT_FLAG, 'true');
            }
            }
            return msg;
        };

        const showError = message => {
            (window.RouteGuard?.showToast || (m => alert(m)))(message);
        };

        const onPointerUp = () => {
            if (errorMessage) {
            showError(errorMessage);
            errorMessage = '';
            }
        };
        document.addEventListener('pointerup', onPointerUp);
        new MutationObserver((muts, obs) => {
            muts.forEach(m => Array.from(m.removedNodes).forEach(n => {
            if (n === document.documentElement) {
                document.removeEventListener('pointerup', onPointerUp);
                obs.disconnect();
            }
            }));
        }).observe(document.body, { childList: true, subtree: true });

        document.addEventListener('DOMContentLoaded', () => {
            const invEl = document.getElementById('invoice');
            if (!invEl || invEl.dataset.listenerAttached === 'true') return;
            invEl.dataset.listenerAttached = 'true';

            const onInvoiceChange = () => {
            try {
                const id = invEl.value ?? '';
                const url = '{{ route(ViewsConstants::INV . ".get") }}';
                if (!url || url === '#') throw new Error('invoice_fetch_unavailable');

                $.ajax({
                url,
                type: 'GET',
                dataType: 'json',
                data: { id },
                })
                .done(data => {
                const amt = document.getElementById('amount');
                if (amt) amt.value = data;
                })
                .fail(() => {
                throw new Error('invoice_fetch_unavailable');
                });
            } catch (e) {
                errorMessage = getMsg(e.message, invEl);
            }
            };

            invEl.addEventListener('change', onInvoiceChange);

            new MutationObserver((muts, obs) => {
            muts.forEach(m => Array.from(m.removedNodes).forEach(n => {
                if (n === invEl) {
                invEl.removeEventListener('change', onInvoiceChange);
                obs.disconnect();
                }
            }));
            }).observe(document.body, { childList: true, subtree: true });
        });
        })();
    </script>
@endpush
@section(YieldingConstants::ADM_BDC)
    <li class="{{ VC::BCI }}">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
           {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="{{ VC::BCI }}">{{ __('Credit Note') }}</li>
@endsection

@section(YieldingConstants::ADM_ACT_BTN)
    <div class="{{ VC::FEND }}">
        @can('create credit note')
            <a
                href="#"
                id="create_credit_note_btn"
                data-url="{{ $createRoute }}"
                data-guard-msg="{{ base64_encode($createGuardMsg) }}"
                data-listener-alias="create-credit-note"
                data-ajax-popup="true"
                data-title="{{ __('Create New Credit Note') }}"
                data-size="lg"
                data-bs-toggle="tooltip"
                title="{{ __('Create') }}"
                class="{{ VC::BT_SM_PM }}"
            >
                <i class="{{ VC::TI_PLS }}"></i>
            </a>
        @endcan
    </div>
@endsection

@section(YieldingConstants::ADM_CTT)
    <div class="{{ VC::RW }}">
        <div class="{{ VC::CS12 }}">
            <div class="{{ VC::CD }}">
                <div class="{{ VC::CD_BD_TB_BD }} {{ VC::MT2 }}">
                    <div class="{{ VC::TB_RSP }}">
                        <table class="{{ VC::TB }} datatable">
                            <thead>
                                <tr>
                                    <th>{{ __('Invoice') }}</th>
                                    <th>{{ __('Customer') }}</th>
                                    <th>{{ __('Date') }}</th>
                                    <th>{{ __('Amount') }}</th>
                                    <th>{{ __('Description') }}</th>
                                    <th width="10%">{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if(Utility::isFilled($invoices) ?? [])
                                    @php
                                        try {
                                            $hasPriceFormat = $user && method_exists($user,'priceFormat');
                                            $hasDateFormat = $user && method_exists($user,'dateFormat');
                                            $hasInvoiceNumberFormat = $user && method_exists($user,'invoiceNumberFormat');
                                        } catch (\Throwable $e) {
                                            \Log::error('credit_notes/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                        }
@endphp
                                    @foreach($invoices as $invoice)
                                        @php
                                            $cnRaw = $invoice->creditNote ?? null;
                                            $creditNotes = Utility::isFilled($cnRaw ?? []) ? $cnRaw : [];
@endphp
                                        @if(!empty($creditNotes))
                                            @foreach($creditNotes as $creditNote)
                                                @php
                                                    $showHref = route(ViewsConstants::INV . '.show', Crypt::encrypt($creditNote->invoice));
@endphp
                                                <tr>
                                                    <td>
                                                        <a href="{{ $showHref }}" class="{{ VC::BT_OUTPM }}">
                                                            {{ !empty($invoice->invoice_id) ? ($hasInvoiceNumberFormat ? $user?->invoiceNumberFormat($invoice->invoice_id) : __('Failed to format invoice number')) : __('No invoice number available') }}
                                                        </a>
                                                    </td>
                                                    <td>{{ data_get($invoice,'customer.name') ?: __('No customer name available') }}</td>
                                                    <td>
                                                        @php
                                                            $d = $creditNote->date ?? null;
                                                        @endphp
                                                        {{ $d ? ($hasDateFormat ? $user?->dateFormat($d) : __('Failed to format date')) : __('No credit note date available') }}
                                                    </td>
                                                    <td>
                                                        @php
                                                            $amt = $creditNote->amount ?? null;
                                                        @endphp
                                                        {{ is_numeric($amt) ? ($hasPriceFormat ? $user?->priceFormat($amt) : __('Failed to format amount')) : __('No amount available') }}
                                                    </td>
                                                    <td>{{ isset($creditNote->description) && $creditNote->description !== '' ? $creditNote->description : __('No description available') }}</td>
                                                    <td class="{{ VC::TX_END }}">
                                                        @can('edit credit note')
                                                            @php
                                                                try {
                                                                    $editName  = ViewsConstants::INV . '.edit.credit.note';
                                                                    $editRoute = route($editName, [$creditNote->invoice, $creditNote->id]);
                                                                    $editBtnId = 'edit-cn-' . $creditNote->id;
                                                                    $editGuard = Utility::fetchLinkMessage($lang, ViewsConstants::INV, 'edit_credit_note_route_unavailable') ?? 'Edit credit note route is unavailable. Please contact technical support or your domain administrator.';
                                                                } catch (\Throwable $e) {
                                                                    \Log::error('credit_notes/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                                }
                                                                $editRoute ??= '#';
                                                                $editBtnId ??= 'edit-cn-0';
                                                                $editGuard ??= '';
                                                            @endphp
                                                            <div class="{{ VC::ACT_BTN_PRIM }}">
                                                                <a href="#"
                                                                id="{{ $editBtnId }}"
                                                                data-url="{{ $editRoute }}"
                                                                data-guard-msg="{{ base64_encode($editGuard) }}"
                                                                data-listener-alias="edit-credit-note"
                                                                data-ajax-popup="true"
                                                                data-size="md"
                                                                data-bs-toggle="tooltip"
                                                                title="{{ __('Edit') }}"
                                                                class="{{ VC::BT_SM_FL_CT }}">
                                                                    <i class="{{ VC::TI_PC_WT }}"></i>
                                                                </a>
                                                            </div>
                                                            @push(StacksConstants::ADM_SCR_PG)
                                                                <script defer>
                                                                    (function(){
                                                                        try{
                                                                            var showErr = window.RouteGuard?.showToast || function(msg) { alert(msg); };
                                                                            var btn=document.getElementById('{{ $editBtnId }}');
                                                                            if(btn && btn.getAttribute('data-listener-active')!=='true'){
                                                                                btn.setAttribute('data-listener-active','true');
                                                                                window.RouteGuard?.attachGuard?.(btn) || btn.addEventListener('click',function(e){
                                                                                    var url=(btn.getAttribute('data-url')||'').trim();
                                                                                    if(!url || url==='#'){ e.preventDefault(); showErr(btn.getAttribute('data-guard-msg')||'#'); }
                                                                                });
                                                                            }
                                                                        }catch(_){}
                                                                    })();
                                                                </script>
                                                            @endpush
                                                        @endcan
                                                        @can('delete credit note')
                                                            @php
                                                                try {
                                                                    $delName   = ViewsConstants::INV . '.delete.credit.note';
                                                                    $delRoute  = route($delName, [$creditNote->invoice, $creditNote->id]);
                                                                    $delGuard  = Utility::fetchLinkMessage($lang, ViewsConstants::INV, 'delete_credit_note_route_unavailable') ?? 'Delete credit note route is unavailable. Please contact technical support or your domain administrator.';
                                                                    $delFormId = 'delete-cn-' . $creditNote->id;
                                                                    $delBtnId  = 'del-cn-' . $creditNote->id;
                                                                } catch (\Throwable $e) {
                                                                    \Log::error('credit_notes/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                                }
@endphp
                                                            {!! Collective\Html\FormFacade::open([
                                                                'method'         => 'DELETE',
                                                                'route'          => [$delName, $creditNote->invoice, $creditNote->id],
                                                                'id'             => $delFormId,
                                                                'data-url'       => $delRoute,
                                                                'data-guard-msg' => $delGuard,
                                                            ]) !!}
                                                                <div class="{{ VC::ACT_BTN_DNG_2 }}">
                                                                    <a href="#"
                                                                    id="{{ $delBtnId }}"
                                                                    data-listener-alias="delete-credit-note"
                                                                    data-confirm="{{ __(Utility::fetchLinkMessage($lang, 'generics', 'are_you_sure') ?? 'Are You Sure?') }}|{{ __(Utility::fetchLinkMessage($lang, 'generics', 'irreversible_action') ?? 'This action can not be undone. Do you want to continue?') }}"
                                                                    data-confirm-yes="document.getElementById('{{ $delFormId }}').submit();"
                                                                    data-bs-toggle="tooltip"
                                                                    title="{{ __('Delete') }}"
                                                                    class="{{ VC::BT_SM_CT_PR }}"
                                                                    data-url="{{ $delRoute }}"
                                                                    data-guard-msg="{{ base64_encode($delGuard) }}">
                                                                        <i class="{{ VC::TI_TRS_WT }}"></i>
                                                                    </a>
                                                                </div>
                                                            {!! Collective\Html\FormFacade::close() !!}
                                                            @push(StacksConstants::ADM_SCR_PG)
                                                                <script defer>
                                                                    (function(){
                                                                        try{
                                                                            var showErr = window.RouteGuard?.showToast || function(msg) { alert(msg); };
                                                                            var btn=document.getElementById('{{ $delBtnId }}');
                                                                            if(btn && btn.getAttribute('data-listener-active')!=='true'){
                                                                                btn.setAttribute('data-listener-active','true');
                                                                                window.RouteGuard?.attachGuard?.(btn) || btn.addEventListener('click',function(e){
                                                                                    var url=(btn.getAttribute('data-url')||'').trim();
                                                                                    if(!url || url==='#'){ e.preventDefault(); showErr(btn.getAttribute('data-guard-msg')||'#'); }
                                                                                });
                                                                            }
                                                                        }catch(_){}
                                                                    })();
                                                                </script>
                                                            @endpush
                                                        @endcan
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @endif
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="6" class="{{ VC::TXCT }}">{{ __('No invoices available') }}</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push(StacksConstants::ADM_SCR_PG)
    <script defer src="{{ asset('asset/js/routes/creditNotes/index.js') }}"></script>
@endpush
