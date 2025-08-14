@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        StacksConstants,
        ViewsConstants,
        ViewClassNamesConstants,
        YieldingConstants,
    };
    use Illuminate\Support\Facades\{Auth, Crypt, Route};
    $user = Auth::user();
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@section(YieldingConstants::ADM_PG_TTL)
    {{__('Manage Debit Notes')}}
@endsection
@section(YieldingConstants::ADM_BDC)
    <li class="breadcrumb-item">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
        {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="breadcrumb-item">{{__('Debit Note')}}</li>
@endsection
@push(StacksConstants::ADM_SCR_PG)
    <script async>
        const billPatch = {
            ar: { bill_fetch_failed: 'فشل جلب قيمة الفاتورة.' },
            da: { bill_fetch_failed: 'Kunne ikke hente beløbet for fakturaen.' },
            de: { bill_fetch_failed: 'Abrufen des Rechnungsbetrags fehlgeschlagen.' },
            en: { bill_fetch_failed: 'Failed to fetch bill amount.' },
            es: { bill_fetch_failed: 'Error al obtener el importe de la factura.' },
            fr: { bill_fetch_failed: 'Échec de la récupération du montant de la facture.' },
            he: { bill_fetch_failed: 'נכשל בקבלת סכום החשבונית.' },
            it: { bill_fetch_failed: 'Impossibile recuperare l’importo della fattura.' },
            ja: { bill_fetch_failed: '請求書の金額を取得できませんでした。' },
            nl: { bill_fetch_failed: 'Kon factuurbedrag niet ophalen.' },
            pl: { bill_fetch_failed: 'Nie udało się pobrać kwoty faktury.' },
            pt: { bill_fetch_failed: 'Falha ao obter o valor da fatura.' },
            'pt-br': { bill_fetch_failed: 'Falha ao obter o valor da fatura.' },
            ru: { bill_fetch_failed: 'Не удалось получить сумму счёта.' },
            tr: { bill_fetch_failed: 'Fatura tutarı alınamadı.' },
            zh: { bill_fetch_failed: '获取账单金额失败。' }
        };
        window.translations = Object.keys(window.translations || {}).length
        ? Object.keys(billPatch).reduce((acc, l) => {
            acc[l] = { ...(acc[l] || {}), ...billPatch[l] };
            return acc;
            }, window.translations)
        : billPatch;
    </script>
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
    <div class="float-end">
        @can('create debit note')
            <a href="#" data-url="{{ route(ViewsConstants::BIL.'.custom.debit.note') }}" data-ajax-popup="true" data-title="{{__('Create New Debit Note')}}" data-bs-toggle="tooltip" title="{{__('Create')}}"  class="btn btn-sm btn-primary">
                <i class="ti ti-plus"></i>
            </a>
        @endcan
    </div>
@endsection
@section(YieldingConstants::ADM_CTT)
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body table-border-style">
                    <div class="table-responsive">
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
                            @foreach ($bills as $bill)
                                @if(!empty($bill->debitNote))
                                    @foreach ($bill->debitNote as $debitNote)
                                        <tr class="font-style">
                                            <td class="Id">
                                                <a href="{{ route(ViewsConstants::BIL.'.show', Crypt::encrypt($debitNote->bill)) }}" class="btn btn-outline-primary">{{ $user?->billNumberFormat($bill->bill_id) }}
                                                </a>
                                            </td>
                                            <td>{{ (!empty($bill->vendor)?$bill->vendor->name:'-') }}</td>
                                            <td>{{ $user?->dateFormat($debitNote->date) }}</td>
                                            <td>{{ $user?->priceFormat($debitNote->amount) }}</td>
                                            <td>{{!empty($debitNote->description)?$debitNote->description:'-'}}</td>
                                            <td class="Action">
                                                <span>
                                                @can('edit debit note')
                                                        <div class="action-btn bg-primary ms-2">
                                                            <a data-url="{{ route(ViewsConstants::BIL.'.edit.debit.note',[$debitNote->bill,$debitNote->id]) }}" data-ajax-popup="true" data-title="{{__('Edit Debit Note')}}" href="#" class="mx-3 btn btn-sm align-items-center" data-bs-toggle="tooltip" title="{{__('Edit')}}" data-original-title="{{__('Edit')}}">
                                                                <i class="{{ ViewClassNamesConstants::TI_PC_WT }}"></i>
                                                            </a>
                                                        </div>
                                                    @endcan
                                                    @can('edit debit note')
                                                        <div class="{{ ViewClassNamesConstants::ACT_BTN_DNG_2 }}">
                                                            {!! Collective\Html\FormFacade::open(['method' => 'DELETE', 'route' => array(ViewsConstants::BIL.'.delete.debit.note', $debitNote->bill,$debitNote->id),'id'=>'delete-form-'.$debitNote->id]) !!}
                                                            <a href="#" class="{{ ViewClassNamesConstants::BT_SM_CT_PR }}" data-bs-toggle="tooltip" title="{{__('Delete')}}" data-original-title="{{__('Delete')}}" data-confirm="{{__('Are You Sure?').'|'.__('This action can not be undone. Do you want to continue?')}}" data-confirm-yes="document.getElementById('delete-form-{{$debitNote->id}}').submit();">
                                                                <i class="ti ti-trash text-white"></i>
                                                            </a>
                                                            {!! Collective\Html\FormFacade::close() !!}
                                                        </div>
                                                    @endcan
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                @endif
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
