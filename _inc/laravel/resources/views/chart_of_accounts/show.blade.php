@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        StacksConstants,
        ViewsConstants,
        ViewClassNamesConstants as VC
        YieldingConstants,
    };
    use App\Models\{Bill,Invoice,Utility,Vendor};
    use Illuminate\Support\Facades\{Auth,Route};
    use Illuminate\Support\Str;
    $user = Auth::user();
    $lang = Utility::fetchUserLang();
    $showName    = ViewsConstants::COA . '.show';
    $showRoute   = Route::has($showName)
        ? route($showName, $account->id)
        : (Route::has(Str::kebab($showName))
            ? route(Str::kebab($showName), $account->id)
            : '#');
    $formId      = 'report_drilldown';
    $guardMsg    = Utility::fetchLinkMessage(
        $lang,
        ViewsConstants::COA,
        'chart_of_account_show_route_unavailable'
    ) ?? 'Chart of Account show route is unavailable. Please contact technical support or your domain administrator.';
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@section(YieldingConstants::ADM_PG_TTL)
    {{ __('Account Drilldown Report') }}
@endsection
@section(YieldingConstants::ADM_BDC)
    <li class="breadcrumb-item">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
        {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="breadcrumb-item"><a href="{{ route(ViewsConstants::COA.'.index') }}">{{ __('Chart of Account') }}</a></li>
    <li class="breadcrumb-item">{{ __('Account Drilldown Report') }}</li>
    <li class="breadcrumb-item">{{ ucwords($account->code . ' - ' . $account->name) }}</li>
@endsection
@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="mt-2" id="multiCollapseExample1">
                <div class="card">
                    <div class="card-body">
                        {{ Collective\Html\FormFacade::open([
                            'route'          => $showRoute,
                            'method'         => 'GET',
                            'id'             => $formId,
                            'data-url'       => $showRoute,
                            'data-guard-msg' => $guardMsg,
                        ]) }}
                        <div class="row align-items-center justify-content-end">
                            <div class="col-xl-10">
                                <div class="row">
                                    <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                                        <div class="btn-box"></div>
                                    </div>
                                    <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                                        <div class="btn-box">
                                            {{ Collective\Html\FormFacade::label('start_date', __('Start Date'), ['class' => 'form-label']) }}
                                            {{ Collective\Html\FormFacade::date('start_date', $filter['startDateRange'], ['class' => 'month-btn form-control']) }}
                                        </div>
                                    </div>
                                    <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                                        <div class="btn-box">
                                            {{ Collective\Html\FormFacade::label('end_date', __('End Date'), ['class' => 'form-label']) }}
                                            {{ Collective\Html\FormFacade::date('end_date', $filter['endDateRange'], ['class' => 'month-btn form-control']) }}
                                        </div>
                                    </div>
                                    <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                                        <div class="btn-box">
                                            {{ Collective\Html\FormFacade::label('account', __('Account'), ['class' => 'form-label']) }}
                                            {{ Collective\Html\FormFacade::select('account', $accounts, $_GET['account'] ?? '', ['class' => 'form-control select']) }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-auto">
                                <div class="row">
                                    <div class="col-auto mt-4">
                                        <a
                                            href="#"
                                            class="btn btn-sm btn-primary"
                                            id="applyDrilldown"
                                            data-listener-alias="drilldown-apply"
                                            data-bs-toggle="tooltip"
                                            title="{{ __('Apply') }}"
                                        >
                                            <span class="btn-inner--icon"><i class="ti ti-search"></i></span>
                                        </a>
                                        <a
                                            href="{{ $showRoute }}"
                                            class="btn btn-sm btn-danger"
                                            data-bs-toggle="tooltip"
                                            title="{{ __('Reset') }}"
                                        >
                                            <span class="btn-inner--icon"><i class="ti ti-trash-off text-white-off"></i></span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        {{ Collective\Html\FormFacade::close() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
    @push(StacksConstants::ADM_SCR_PG)
        <script defer>
            (() => {
                const bindGuard = (el, event, urlAttr='data-url', msgAttr='data-guard-msg') => {
                    if (!el || el.getAttribute('data-listener-active') === 'true') return;
                    el.setAttribute('data-listener-active', 'true');
                    el.addEventListener(event, e => {
                        try {
                            const url = el.getAttribute(urlAttr) ?? '#';
                            if (url !== '#') return;
                            e.preventDefault();
                            const msg = el.getAttribute(msgAttr) ?? '# ERROR';
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
                            el.setAttribute('data-failed-route', 'true');
                        } catch (err) {}
                    });
                };

                const form = document.getElementById('report_drilldown');
                bindGuard(form, 'submit');

                const applyBtn = document.getElementById('applyDrilldown');
                bindGuard(applyBtn, 'click');
            })();
        </script>
    @endpush
    <div id="printableArea">
        <div class="row mt-2">
            <div class="col-3">
                {{--                <input type="hidden" value="{{__('Ledger').' '.'Report of'.' '.$filter['startDateRange'].' to '.$filter['endDateRange']}}" id="filename"> --}}
                <div class="card p-4 mb-4">
                    <h6 class="mb-0">{{ __('Report') }} :</h6>
                    <h7 class="text-sm mb-0">{{ __('Account Drilldown') }}</h7>
                </div>
            </div>

            @if (!empty($account))
                <div class="col-3">
                    <div class="card p-4 mb-4">
                        <h6 class="mb-0">{{ __('Account Name') }} :</h6>
                        <h7 class="text-sm mb-0">{{ $account->name }}</h7>
                    </div>
                </div>
                <div class="col-3">
                    <div class="card p-4 mb-4">
                        <h6 class="mb-0">{{ __('Account Code') }} :</h6>
                        <h7 class="text-sm mb-0">{{ $account->code }}</h7>
                    </div>
                </div>
            @endif

            <div class="col-3">
                <div class="card p-4 mb-4">
                    <h6 class="mb-0">{{ __('Duration') }} :</h6>
                    <h7 class="text-sm mb-0">{{ $filter['startDateRange'] . ' to ' . $filter['endDateRange'] }}</h7>
                </div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-12 mb-4">
                <div class="card">
                    <div class="card-body table-border-style">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th> {{ __('Account Name') }}</th>
                                        <th> {{ __('Name') }}</th>
                                        <th> {{ __('Transaction Type') }}</th>
                                        <th> {{ __('Transaction Date') }}</th>
                                        <th> {{ __('Debit') }}</th>
                                        <th> {{ __('Credit') }}</th>
                                        <th> {{ __('Balance') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $balance = 0;
                                        $totalDebit = 0;
                                        $totalCredit = 0;
                                        $chartDatas = Utility::getAccountData($account->id, $filter['startDateRange'], $filter['endDateRange']);
                                        
                                        $accountName = \App\Models\ChartOfAccount::find($account->id);
                                    @endphp

                                    @foreach ($chartDatas['invoice'] as $invoiceData)
                                        <tr>
                                            <td>{{ $accountName->name }}</td>
                                            @php
                                                $invoice = Invoice::where('id', $invoiceData->invoice_id)->first();
                                            @endphp
                                            <td>{{ !empty($invoice->customer) ? $invoice->customer->name : '-' }}</td>
                                            <td>{{ $user?->invoiceNumberFormat($invoice->invoice_id) }}</td>
                                            <td>{{ $invoiceData->created_at->format('d-m-Y') }}</td>
                                            <td>-</td>

                                            @php
                                                $total = $invoiceData->price * $invoiceData->quantity;
                                                $balance += $total;
                                                $totalCredit += $total;
                                            @endphp
                                            <td>{{ $user?->priceFormat($total) }}</td>
                                            <td>{{ $user?->priceFormat($balance) }}</td>
                                        </tr>
                                    @endforeach

                                    @foreach ($chartDatas['invoicepayment'] as $invoicePaymentData)
                                        <tr>
                                            <td>{{ $accountName->name }}</td>
                                            @php
                                                $invoice = Invoice::where('id', $invoicePaymentData->invoice_id)->first();
                                            @endphp
                                            <td>{{ !empty($invoice->customer) ? $invoice->customer->name : '-' }}</td>
                                            <td>{{ $user?->invoiceNumberFormat($invoice->invoice_id) }}
                                                {{ __(' Manually Payment') }}</td>
                                            <td>{{ $invoicePaymentData->created_at->format('d-m-Y') }}</td>
                                            <td>-</td>
                                            <td>{{ $user?->priceFormat($invoicePaymentData->amount) }}</td>
                                            @php
                                                $balance += $invoicePaymentData->amount;
                                                $totalCredit += $invoicePaymentData->amount;
                                            @endphp
                                            <td>{{ $user?->priceFormat($balance) }}</td>
                                        </tr>
                                    @endforeach

                                    @foreach ($chartDatas['revenue'] as $revenueData)
                                        <tr>
                                            <td>{{ $accountName->name }}</td>
                                            <td>{{ !empty($revenueData->customer) ? $revenueData->customer->name : '-' }}
                                            </td>
                                            <td>{{ __('Revenue') }}</td>
                                            <td>{{ $revenueData->created_at->format('d-m-Y') }}</td>
                                            <td>-</td>
                                            <td>{{ $user?->priceFormat($revenueData->amount) }}</td>
                                            @php
                                                $balance += $revenueData->amount;
                                                $totalCredit += $revenueData->amount;
                                            @endphp
                                            <td>{{ $user?->priceFormat($balance) }}</td>
                                        </tr>
                                    @endforeach


                                    @foreach ($chartDatas['bill'] as $billProduct)
                                        <tr>
                                            <td>{{ $accountName->name }}</td>
                                            @php
                                                
                                                $bill = Bill::find($billProduct->bill_id);
                                                $vendor = Vendor::find(!empty($bill) ? $bill->vendor_id : '');
                                            @endphp
                                            <td>{{ !empty($vendor) ? $vendor->name : '-' }}</td>
                                            <td>{{ $user?->billNumberFormat($bill->bill_id) }}</td>
                                            <td>{{ $billProduct->created_at->format('d-m-Y') }}</td>

                                            @php
                                                $total = $billProduct->price * $billProduct->quantity;
                                                $balance -= $total;
                                                $totalCredit -= $total;
                                            @endphp
                                            <td>{{ $user?->priceFormat($total) }}</td>
                                            <td>-</td>
                                            <td>{{ $user?->priceFormat($balance) }}</td>
                                        </tr>
                                    @endforeach

                                    @foreach ($chartDatas['billdata'] as $billData)
                                        @php
                                            $bill = Bill::find($billData->ref_id);
                                            $vendor = Vendor::find(!empty($bill) ? $bill->vendor_id : '');
                                        @endphp
                                        <tr>
                                            <td>{{ $accountName->name }}</td>
                                            <td>{{ !empty($vendor) ? $vendor->name : '-' }}</td>
                                            @if (!empty($bill->bill_id))
                                                <td>{{ $user?->billNumberFormat($bill->bill_id) }}</td>
                                            @else
                                                <td>-</td>
                                            @endif

                                            <td>{{ $billData->created_at->format('d-m-Y') }}</td>
                                            <td>{{ $user?->priceFormat($billData->price) }}</td>
                                            <td>-</td>
                                            @php
                                                $balance -= $billData->price;
                                                $totalDebit -= $billData->price;
                                            @endphp
                                            <td>{{ $user?->priceFormat($balance) }}</td>
                                        </tr>
                                    @endforeach

                                    @foreach ($chartDatas['billpayment'] as $billPaymentData)
                                        @php
                                            $bill = BillPayment::where('bill_id', $billPaymentData->bill_id)->first();
                                            $billId = Bill::find($billPaymentData->bill_id);
                                            $vendor = Vendor::find($billId->vendor_id);
                                        @endphp
                                        <tr>
                                            <td>{{ $accountName->name }}</td>
                                            <td>{{ !empty($vendor) ? $vendor->name : '-' }}</td>
                                            <td>{{ $user?->billNumberFormat($billId->bill_id) }}{{ __(' Manually Payment') }}
                                            </td>
                                            <td>{{ $billPaymentData->created_at->format('d-m-Y') }}</td>
                                            <td>{{ $user?->priceFormat($billPaymentData->amount) }}</td>
                                            <td>-</td>
                                            @php
                                                $balance += $billPaymentData->amount;
                                                $totalDebit += $billPaymentData->amount;
                                            @endphp
                                            <td>{{ $user?->priceFormat($totalCredit - $totalDebit) }}</td>
                                        </tr>
                                    @endforeach

                                    @foreach ($chartDatas['payment'] as $paymentData)
                                        @php
                                            $vendor = Vendor::find($paymentData->vendor_id);
                                        @endphp
                                        <tr>
                                            <td>{{ $accountName->name }}</td>
                                            <td>{{ !empty($vendor) ? $vendor->name : '-' }}</td>
                                            <td>{{ __('Payment') }}</td>
                                            <td>{{ $paymentData->created_at->format('d-m-Y') }}</td>

                                            <td>{{ $user?->priceFormat($paymentData->amount) }}</td>
                                            <td>-</td>
                                            @php
                                                $balance += $paymentData->amount;
                                                $totalDebit += $paymentData->amount;
                                            @endphp
                                            <td>{{ $user?->priceFormat($totalCredit - $totalDebit) }}</td>
                                        </tr>
                                    @endforeach

                                    @php
                                    $debit = 0;
                                    $credit = 0;
                                @endphp

                                @foreach ($chartDatas['journalItem'] as $journalItemData)
                                    <tr>
                                        <td>{{ $accountName->name }}</td>
                                        <td>{{ '-' }}</td>
                                        <td>{{ $user?->journalNumberFormat($journalItemData->journal_id) }}
                                        </td>
                                        <td>{{ $journalItemData->created_at->format('d-m-Y') }}</td>
                                        <td>{{ $user?->priceFormat($journalItemData->debit) }}</td>
                                        <td>{{ $user?->priceFormat($journalItemData->credit) }}</td>
                                        <td>
                                            @if ($journalItemData->debit)
                                                @php $balance-= $journalItemData->debit @endphp
                                            @else
                                                @php $balance+= $journalItemData->credit @endphp
                                            @endif
                                            {{ $user?->priceFormat($balance) }}
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
