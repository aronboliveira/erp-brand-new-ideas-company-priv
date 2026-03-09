@php
    try {
$user = Auth::user();
        $lang = Utility::fetchUserLang(user:$user);
    } catch (\Throwable $e) {
        \Log::error('debit_notes/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    }
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@section(YieldingConstants::ADM_PG_TTL)
    {{__('Manage Debit Notes')}}
@endsection
@section(YieldingConstants::ADM_BDC)
    <li class="{{ VC::BCI }}">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
        {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="{{ VC::BCI }}">{{__('Debit Note')}}</li>
@endsection
@push(StacksConstants::ADM_SCR_PG)
    <script async src="{{ asset('assets/js/routes/debitNotes/lang/index.js') }}"></script>
    <script defer>
        (() => {
            const selBill  = document.getElementById('bill');
            const inpAmt   = document.getElementById('amount');
            const routeURL = "{{ route(ViewsConstants::BIL . '.get') }}";

            if (!selBill || !inpAmt) return;

            if (selBill.dataset.listenerAttached === 'true') return;
            selBill.dataset.listenerAttached = 'true';

            const langKey = () =>
                (sessionStorage.getItem('erp-np-lang') || document.documentElement.lang || 'en')
                .toLowerCase().replace(/_/g, '-')
                .replace(/^([a-z]{2}).*/,'$1');

            const t = k =>
                window.translations?.[langKey()]?.[k] ||
                window.translations?.en?.[k]          ||
                '# ERROR';

            const showErr = () =>
                window.show_toastr ? window.show_toastr('error', t('bill_fetch_failed'), 'error')
                                : alert(t('bill_fetch_failed'));

            const obs = new MutationObserver((ms, o) => {
                ms.forEach(m => m.removedNodes.forEach(n => {
                if (n === selBill) { selBill.removeEventListener('change', handler); o.disconnect(); }
                }));
            });
            obs.observe(document.body, { childList:true, subtree:true });

            selBill.addEventListener('change', handler);

            function handler() {
                const id = this.value || '';
                if (!id) { inpAmt.value = ''; return; }
                fetch(`${routeURL}?bill_id=${encodeURIComponent(id)}`, {
                headers: { 'X-Requested-With':'XMLHttpRequest' },
                cache  : 'no-store'
                })
                .then(r => r.ok ? r.text() : Promise.reject())
                .then(v => { inpAmt.value = v ?? ''; })
                .catch(showErr);
            }
        })();
    </script>
@endpush
@section(YieldingConstants::ADM_ACT_BTN)
    <div class="{{ VC::FEND }}">
        @can('create debit note')
            @php
                try {
                    $billsCustomDebitNoteCreateBaseRouteName  = ViewsConstants::BIL.'.custom.debit.note';
                    $billsCustomDebitNoteCreateKebabRouteName = Str::kebab($billsCustomDebitNoteCreateBaseRouteName);
                    $billsCustomDebitNoteCreateResolvedName   = Route::has($billsCustomDebitNoteCreateBaseRouteName)
                        ? $billsCustomDebitNoteCreateBaseRouteName
                        : (Route::has($billsCustomDebitNoteCreateKebabRouteName) ? $billsCustomDebitNoteCreateKebabRouteName : null);
                    $billsCustomDebitNoteCreateUrl            = $billsCustomDebitNoteCreateResolvedName ? route($billsCustomDebitNoteCreateResolvedName) : '#';
                    $billsLangValue                           = isset($lang) ? $lang : Utility::fetchUserLang();
                    $billsCustomDebitNoteCreateGuardMessage   = Utility::fetchLinkMessage($billsLangValue, ViewsConstants::BIL, 'create_custom_debit_note_route_unavailable')
                        ?? 'Create custom debit note route is unavailable. Please contact technical support or your domain administrator.';
                    $billsCustomDebitNoteCreateLinkId         = 'bills-custom-debit-note-create-link';
                } catch (\Throwable $e) {
                    \Log::error('debit_notes/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                }
@endphp
            <a href="{{ $billsCustomDebitNoteCreateUrl }}"
            id="{{ $billsCustomDebitNoteCreateLinkId }}"
            data-url="{{ $billsCustomDebitNoteCreateUrl }}"
            data-guard-msg="{{ base64_encode($billsCustomDebitNoteCreateGuardMessage) }}"
            data-ajax-popup="true"
            data-title="{{ __('Create New Debit Note') }}"
            data-bs-toggle="tooltip"
            title="{{ __('Create') }}"
            class="{{ VC::BT_SM_PM }}"
            data-sv-localized="true">
                <i class="{{ VC::TI_PLS }}"></i>
            </a>
            @push(StacksConstants::ADM_SCR_PG)
                <script defer src="{{ asset('assets/js/routes/debitNotes/customLink.js') }}"></script>
            @endpush
        @endcan
    </div>
@endsection
@section(YieldingConstants::ADM_CTT)
    <div class="row">
        <div class="{{ VC::CM12 }}">
            <div class="card">
                <div class="{{ VC::CD_BD_TB_BD }}">
                    <div class="{{ VC::TB_RSP }}">
                        <table class="table datatable">
                            <thead>
                            <tr>
                                <th> {{__('Bill')}}</th>
                                <th> {{__('Vendor')}}</th>
                                <th> {{__('Date')}}</th>
                                <th> {{__('Amount')}}</th>
                                <th> {{__('Description')}}</th>
                                <th width="10%"> {{__('Action')}}</th>
                            </tr>
                            </thead>
                            <tbody>
                                @php
                                    try {
                                        $hasBills  = Utility::isFilled($bills ?? []);
                                        $__shown   = false;

                                        $hasBillNumberFormat = is_object($user ?? null) && method_exists($user, 'billNumberFormat');
                                        $hasDateFormat       = is_object($user ?? null) && method_exists($user, 'dateFormat');
                                        $hasPriceFormat      = is_object($user ?? null) && method_exists($user, 'priceFormat');
                                    } catch (\Throwable $e) {
                                        \Log::error('debit_notes/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                    }
@endphp

                                @if($hasBills)
                                    @foreach ($bills as $bill)
                                        @php
                                            try {
                                                $notes    = $bill->debitNotes ?? [];
                                                $hasNotes = Utility::isFilled($notes ?? []);
                                                $vname    = (isset($bill->vendor) && isset($bill->vendor->name)) ? $bill->vendor->name : __('No name available for vendor');
                                            } catch (\Throwable $e) {
                                                \Log::error('debit_notes/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                            }
@endphp
                                        @if($hasNotes)
                                            @foreach ($notes as $debitNote)
                                                @php
                                                    $__shown = true;
                                                    $langValue = '';
                                                    $debitNoteIdValue = '';
                                                    $billIdValue = '';
                                                    $billShowUrl = '#';
                                                    $billShowGuardMsg = '';
                                                    $billShowLinkId = 'bill-show-link-x';
                                                    try {
                                                        $langValue = isset($lang) ? $lang : Utility::fetchUserLang();
                                                        $debitNoteIdValue = (string) ($debitNote->id ?? '');
                                                        $billIdValue = (string) ($debitNote->bill ?? '');

                                                        $billShowBase = ViewsConstants::BIL.'.show';
                                                        $billShowKebab = Str::kebab($billShowBase);
                                                        $billShowResolved = Route::has($billShowBase) ? $billShowBase : (Route::has($billShowKebab) ? $billShowKebab : null);
                                                        $encryptedBillId = $billIdValue !== '' ? Crypt::encrypt($billIdValue) : null;
                                                        $billShowUrl = ($billShowResolved && $encryptedBillId) ? route($billShowResolved, $encryptedBillId) : '#';
                                                        $billShowGuardMsg = Utility::fetchLinkMessage($langValue, ViewsConstants::BIL, 'show_bill_route_unavailable') ?? 'Show bill route is unavailable. Please contact technical support or your domain administrator.';
                                                        $billShowLinkId = 'bill-show-link-'.($debitNoteIdValue === '' ? 'x' : $debitNoteIdValue);
                                                    } catch (\Throwable $e) {
                                                        \Log::error('debit_notes/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                    }
                                                @endphp
                                                <tr class="font-style">
                                                    <td class="Id">
                                                        <a id="{{ $billShowLinkId }}"
                                                        href="{{ $billShowUrl }}"
                                                        class="{{ VC::BT_OUTPM }}"
                                                        data-url="{{ $billShowUrl }}"
                                                        data-guard-msg="{{ base64_encode($billShowGuardMsg) }}"
                                                        data-sv-localized="true">
                                                            {{ $hasBillNumberFormat ? ($user?->billNumberFormat($bill->bill_id) ?? '-') : ($bill->bill_id ?? '-') }}
                                                        </a>
                                                    </td>
                                                    <td>{{ $vname }}</td>
                                                    <td>{{ $hasDateFormat ? ($user?->dateFormat($debitNote->date) ?? __('Failed to format date')) : ($debitNote->date ?? __('Failed to get date')) }}</td>
                                                    <td>{{ $hasPriceFormat ? ($user?->priceFormat($debitNote->amount) ?? __('Failed to format amount')) : ($debitNote->amount ?? __('Failed to get amount')) }}</td>
                                                    <td>{{ isset($debitNote->description) && $debitNote->description !== '' ? $debitNote->description : __('No description available') }}</td>
                                                    <td class="Action">
                                                        <span>
                                                            @can('edit debit note')
                                                                @php
                                                                    $editDebitUrl = '#';
                                                                    $editDebitGuardMsg = '';
                                                                    $editDebitLinkId = 'debit-note-edit-link-x';
                                                                    try {
                                                                        $editDebitBase = ViewsConstants::BIL.'.edit.debit.note';
                                                                        $editDebitKebab = Str::kebab($editDebitBase);
                                                                        $editDebitResolved = Route::has($editDebitBase) ? $editDebitBase : (Route::has($editDebitKebab) ? $editDebitKebab : null);
                                                                        $editDebitUrl = ($editDebitResolved && $billIdValue !== '' && $debitNoteIdValue !== '') ? route($editDebitResolved, [$billIdValue, $debitNoteIdValue]) : '#';
                                                                        $editDebitGuardMsg = Utility::fetchLinkMessage($langValue, ViewsConstants::BIL, 'edit_debit_note_route_unavailable') ?? 'Edit debit note route is unavailable. Please contact technical support or your domain administrator.';
                                                                        $editDebitLinkId = 'debit-note-edit-link-'.($debitNoteIdValue === '' ? 'x' : $debitNoteIdValue);
                                                                    } catch (\Throwable $e) {
                                                                        \Log::error('debit_notes/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                                    }
@endphp
                                                                <div class="{{ VC::ACT_BTN_PRIM }}">
                                                                    <a id="{{ $editDebitLinkId }}"
                                                                    href="{{ $editDebitUrl }}"
                                                                    data-url="{{ $editDebitUrl }}"
                                                                    data-guard-msg="{{ base64_encode($editDebitGuardMsg) }}"
                                                                    data-ajax-popup="true"
                                                                    data-title="{{ __('Edit Debit Note') }}"
                                                                    data-sv-localized="true"
                                                                    class="{{ VC::BT_SM_CT }}"
                                                                    data-bs-toggle="tooltip"
                                                                    title="{{ __('Edit') }}">
                                                                        <i class="{{ ViewClassNamesConstants::TI_PC_WT }}"></i>
                                                                    </a>
                                                                </div>
                                                            @endcan

                                                            @can('delete debit note')
                                                                @php
                                                                    $deleteDebitUrl = '#';
                                                                    $deleteDebitGuardMsg = '';
                                                                    $deleteDebitFormId = 'debit-note-delete-form-x';
                                                                    $deleteDebitLinkId = 'debit-note-delete-link-x';
                                                                    try {
                                                                        $deleteDebitBase = ViewsConstants::BIL.'.delete.debit.note';
                                                                        $deleteDebitKebab = Str::kebab($deleteDebitBase);
                                                                        $deleteDebitResolved = Route::has($deleteDebitBase) ? $deleteDebitBase : (Route::has($deleteDebitKebab) ? $deleteDebitKebab : null);
                                                                        $deleteDebitUrl = ($deleteDebitResolved && $billIdValue !== '' && $debitNoteIdValue !== '') ? route($deleteDebitResolved, [$billIdValue, $debitNoteIdValue]) : '#';
                                                                        $deleteDebitGuardMsg = Utility::fetchLinkMessage($langValue, ViewsConstants::BIL, 'delete_debit_note_route_unavailable') ?? 'Delete debit note route is unavailable. Please contact technical support or your domain administrator.';
                                                                        $deleteDebitFormId = 'debit-note-delete-form-'.($debitNoteIdValue === '' ? 'x' : $debitNoteIdValue);
                                                                        $deleteDebitLinkId = 'debit-note-delete-link-'.($debitNoteIdValue === '' ? 'x' : $debitNoteIdValue);
                                                                    } catch (\Throwable $e) {
                                                                        \Log::error('debit_notes/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                                    }
@endphp
                                                                <div class="{{ ViewClassNamesConstants::ACT_BTN_DNG_2 }}">
                                                                    {!! Collective\Html\FormFacade::open([
                                                                        'method'            => 'DELETE',
                                                                        'url'               => $deleteDebitUrl,
                                                                        'id'                => $deleteDebitFormId,
                                                                        'data-url'          => $deleteDebitUrl,
                                                                        'data-guard-msg'    => $deleteDebitGuardMsg,
                                                                        'data-sv-localized' => 'true',
                                                                    ]) !!}
                                                                        <a id="{{ $deleteDebitLinkId }}"
                                                                        href="#"
                                                                        class="{{ ViewClassNamesConstants::BT_SM_CT_PR }}"
                                                                        data-bs-toggle="tooltip"
                                                                        title="{{ __('Delete') }}"
                                                                        data-confirm="{{ __(Utility::fetchLinkMessage($langValue, 'generics', 'are_you_sure') ?? 'Are You Sure?') }}|{{ __(Utility::fetchLinkMessage($langValue, 'generics', 'irreversible_action') ?? 'This action can not be undone. Do you want to continue?') }}"
                                                                        data-confirm-yes="document.getElementById('{{ $deleteDebitFormId }}').submit();">
                                                                            <i class="{{ VC::TI_TRS_WT }}"></i>
                                                                        </a>
                                                                    {!! Collective\Html\FormFacade::close() !!}
                                                                </div>
                                                            @endcan
                                                        </span>
                                                    </td>
                                                </tr>

                                                @push(StacksConstants::ADM_SCR_PG)
                                                    <script defer>
                                                        window.RouteGuard?.guardById?.('{{ $billShowLinkId }}');
                                                        @can('edit debit note')
                                                            window.RouteGuard?.guardById?.('{{ $editDebitLinkId }}');
                                                        @endcan
                                                        @can('delete debit note')
                                                            window.RouteGuard?.guardById?.('{{ $deleteDebitLinkId }}');
                                                            window.RouteGuard?.guardFormSubmit?.('{{ $deleteDebitFormId }}');
                                                        @endcan
                                                    </script>
                                                @endpush
                                            @endforeach
                                        @else
                                            <tr>
                                                <td colspan="6" class="{{ VC::TXCT }}">{{ __('No debit notes found for this bill.') }}</td>
                                            </tr>
                                        @endif
                                    @endforeach
                                @endif
                                @unless($__shown)
                                    <tr>
                                        <td colspan="6" class="{{ VC::TXCT }}">{{ __('No debit notes found.') }}</td>
                                    </tr>
                                @endunless
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
