@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        StacksConstants,
        ViewsConstants as VW,
        ViewClassNamesConstants as VC,
        YieldingConstants,
    };
    use App\Models\{Bill, BillPayment, ChartOfAccount, Invoice, Utility, Vendor};
    use Collective\Html\FormFacade as Form;
    use Illuminate\Support\Facades\{Auth, Route};
    use Illuminate\Support\Str;
    $user = Auth::user();
    $lang = Utility::fetchUserLang(auth: $user);
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@section(YieldingConstants::ADM_PG_TTL)
    {{ __('Ledger Summary') }}
@endsection

@section(YieldingConstants::ADM_BDC)
    <li class="breadcrumb-item">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
        {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="breadcrumb-item">{{ __('Ledger Summary') }}</li>
@endsection

@push(StacksConstants::ADM_SCR_PG)
    <script type="text/javascript" src="{{ asset('js/html2pdf.bundle.min.js') }}"></script>
    <script async src="{{ asset('assets/js/routes/reports/ledgers/lang/pdf.js') }}"></script>
    <script defer src="{{ asset('assets/js/routes/reports/ledgers/pdf.js') }}"></script>
@endpush

{{--        <a class="btn btn-sm btn-primary" data-bs-toggle="collapse" href="#multiCollapseExample1" role="button" aria-expanded="false" aria-controls="multiCollapseExample1" data-bs-toggle="tooltip" title="{{__('Filter')}}"> --}}
{{--            <i class="ti ti-filter"></i> --}}
{{--        </a> --}}
@section(YieldingConstants::ADM_ACT_BTN)
    <div class="float-end">
        @php
            $downloadGuardMsg = Utility::fetchLinkMessage($lang, VW::RPT, 'download_ledger_summary_unavailable') ?? 'Download function for ledger summaries is unavailable. Please contact technical support or your domain administrator.';
        @endphp
        <a href="#"
        id="download-ledger-summary-link"
        class="{{ VC::BT_SM_PM }} download-ledger-summary"
        data-func-name="saveAsPDF"
        data-guard-msg="{{ $downloadGuardMsg }}"
        data-sv-localized="true"
        data-bs-toggle="tooltip"
        title="{{ __('Download') }}"
        data-original-title="{{ __('Download') }}">
            <span class="btn-inner--icon"><i class="{{ VC::TI_DWN }}"></i></span>
        </a>
        @push(StacksConstants::ADM_SCRP_PG)
            <script src="{{ asset('assets/js/routes/reports/ledgers/summaries/download.js') }}" defer></script>
        @endpush
    </div>
@endsection

@section(YieldingConstants::ADM_CTT)
    <div class="{{ VC::RW }}">
        <div class="{{ VC::CS12 }}">
            <div class="mt-2" id="multiCollapseExample1">
                <div class="{{ VC::CD }}">
                    <div class="card-body">
                        @php
                            $ledgerBase          = ViewsConstants::RPT.'.ledger';
                            $ledgerKebab         = Str::kebab($ledgerBase);
                            $ledgerResolved      = Route::has($ledgerBase) ? $ledgerBase : (Route::has($ledgerKebab) ? $ledgerKebab : null);
                            $ledgerUrl           = $ledgerResolved ? route($ledgerResolved) : '#';
                            $ledgerGuardMsg      = Utility::fetchLinkMessage($lang, ViewsConstants::RPT, 'ledger_report_route_unavailable') ?? 'Ledger report route is unavailable. Please contact technical support or your domain administrator.';
                        @endphp
                        {{ Form::open([
                            'method'            => 'GET',
                            'url'               => $ledgerUrl,
                            'id'                => 'report_ledger',
                            'data-url'          => $ledgerUrl,
                            'data-guard-msg'    => $ledgerGuardMsg,
                            'data-sv-localized' => 'true',
                        ]) }}
                            <div class="{{ VC::R_ALC_JCE }}">
                                <div class="col-xl-10">
                                    <div class="{{ VC::RW }}">
                                        <div class="{{ VC::CL_XLG4 }}">
                                            <div class="btn-box"></div>
                                        </div>
                                        <div class="{{ VC::CL_XLG4 }}">
                                            <div class="btn-box">
                                                {{ Form::label('start_date', __('Start Date'), ['class' => VC::FM_LB]) }}
                                                {{ Form::date('start_date', $filter['startDateRange'], ['class' => 'month-btn ' . VC::FM_CT]) }}
                                            </div>
                                        </div>
                                        <div class="{{ VC::CL_XLG4 }}">
                                            <div class="btn-box">
                                                {{ Form::label('end_date', __('End Date'), ['class' => VC::FM_LB]) }}
                                                {{ Form::date('end_date', $filter['endDateRange'], ['class' => 'month-btn ' . VC::FM_CT]) }}
                                            </div>
                                        </div>
                                        <div class="{{ VC::CL_XLG4 }}">
                                            <div class="btn-box">
                                                {{ Form::label('account', __('Account'), ['class' => VC::FM_LB]) }}
                                                {{ Form::select('account', $accounts, isset($_GET['account']) ? $_GET['account'] : '', ['class' => VC::FM_CT_SL]) }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="{{ VC::C_AT }}">
                                    <div class="{{ VC::RW }}">
                                        <div class="{{ VC::C_AT }} {{ VC::MT4 }}">
                                            <a href="#"
                                            class="{{ VC::BT_SM_PM }} apply-ledger"
                                            data-form-id="report_ledger"
                                            data-guard-msg="{{ $ledgerGuardMsg }}"
                                            data-sv-localized="true"
                                            data-bs-toggle="tooltip"
                                            title="{{ __('Apply') }}"
                                            data-original-title="{{ __('apply') }}">
                                                <span class="btn-inner--icon"><i class="{{ VC::TI_SRC }}"></i></span>
                                            </a>
                                            <a href="{{ $ledgerUrl }}"
                                            class="{{ VC::BT_SM_DG }} reset-ledger"
                                            data-url="{{ $ledgerUrl }}"
                                            data-guard-msg="{{ $ledgerGuardMsg }}"
                                            data-sv-localized="true"
                                            data-bs-toggle="tooltip"
                                            title="{{ __('Reset') }}"
                                            data-original-title="{{ __('Reset') }}">
                                                <span class="btn-inner--icon"><i class="{{ VC::TI_TRS_OFF }}"></i></span>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        {{ Form::close() }}
                        @push(StacksConstants::ADM_SCRP_PG)
                            <script src="{{ asset('assets/js/routes/reports/ledgers/apply.js') }}" defer></script>
                            <script src="{{ asset('assets/js/routes/reports/ledgers/reset.js') }}" defer></script>
                        @endpush
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div id="printableArea">
        <div class="{{ VC::RW }} {{ VC::MB4 }}">
            <div class="{{ VC::C12 }} {{ VC::MB4 }}">
                <div class="{{ VC::CD }}">
                    <div class="card-body table-border-style">
                        <div class="table-responsive">
                            <table class="{{ VC::TB }}">
                                <thead>
                                    <tr>
                                        <th>{{ __('Account Name') }}</th>
                                        <th>{{ __('Name') }}</th>
                                        <th>{{ __('Transaction Type') }}</th>
                                        <th>{{ __('Transaction Date') }}</th>
                                        <th>{{ __('Debit') }}</th>
                                        <th>{{ __('Credit') }}</th>
                                        <th>{{ __('Balance') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $balance = 0;
                                        $totalDebit = 0;
                                        $totalCredit = 0;
                                        $accountArrays = [];
                                        foreach ($accountss as $key => $account) {
                                            $chartDatas = Utility::getAccountData($account->id, $filter['startDateRange'], $filter['endDateRange']);
                                            $a = [0 => ['account' => $account->id]];
                                            $chartDatas = array_merge($chartDatas, $a);
                                            $accountArrays[] = $chartDatas;
                                        }
                                    @endphp
                                    @foreach ($accountArrays as $account)

                                        @foreach ($account[0] as $a)
                                            @php $accountName = ChartOfAccount::find($a); @endphp
                                            @foreach ($account['invoice'] as $invoiceData)
                                                @if ($account['invoice'] != [])
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
                                                @endif
                                            @endforeach

                                            @foreach ($account['invoicepayment'] as $invoicePaymentData)
                                                <tr>
                                                    <td>{{ $accountName->name }}</td>
                                                    @php
                                                        $invoice = Invoice::where('id', $invoicePaymentData->invoice_id)->first();
                                                    @endphp
                                                    <td>{{ !empty($invoice->customer) ? $invoice->customer->name : '-' }}</td>
                                                    <td>{{ $user?->invoiceNumberFormat($invoice->invoice_id) }} {{ __(' Manually Payment') }}</td>
                                                    <td>{{ $invoicePaymentData->created_at->format('d-m-Y') }}</td>
                                                    <td>{{ $user?->priceFormat($invoicePaymentData->amount) }}</td>
                                                    <td>-</td>
                                                    @php
                                                        $balance += $invoicePaymentData->amount;
                                                        $totalCredit += $invoicePaymentData->amount;
                                                    @endphp
                                                    <td>{{ $user?->priceFormat($balance) }}</td>
                                                </tr>
                                            @endforeach

                                            @foreach ($account['revenue'] as $revenueData)
                                                <tr>
                                                    <td>{{ $accountName->name }}</td>
                                                    <td>{{ !empty($revenueData->customer) ? $revenueData->customer->name : '-' }}</td>
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

                                            @foreach ($account['bill'] as $billProduct)
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

                                            @foreach ($account['billdata'] as $billData)
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

                                            @foreach ($account['billpayment'] as $billPaymentData)
                                                @if($account['billpayment'] != [])
                                                    @php
                                                        $bill = BillPayment::where('bill_id', $billPaymentData->bill_id)->first();
                                                        $billId = Bill::find($billPaymentData->bill_id);
                                                        $vendor = Vendor::find($billId->vendor_id);
                                                    @endphp
                                                    <tr>
                                                        <td>{{ $accountName->name }}</td>
                                                        <td>{{ !empty($vendor) ? $vendor->name : '-' }}</td>
                                                        @if($billId->type == 'Expense')
                                                            <td>{{ $user?->expenseNumberFormat($billId->bill_id) }}{{ __(' Manually Payment') }}</td>
                                                        @else
                                                            <td>{{ $user?->billNumberFormat($billId->bill_id) }}{{ __(' Manually Payment') }}
                                                        @endif
                                                        <td>{{ $billPaymentData->created_at->format('d-m-Y') }}</td>
                                                        <td>{{ $user?->priceFormat($billPaymentData->amount) }}</td>
                                                        <td>-</td>
                                                        @php
                                                            $balance -= $billPaymentData->amount;
                                                            $totalDebit += $billPaymentData->amount;
                                                        @endphp
                                                        <td>{{ $user?->priceFormat($balance) }}</td>
                                                    </tr>
                                                @endif
                                            @endforeach

                                            @foreach ($account['payment'] as $paymentData)
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
                                                        $balance -= $paymentData->amount;
                                                        $totalDebit += $paymentData->amount;
                                                    @endphp
                                                    <td>{{ $user?->priceFormat($balance) }}</td>
                                                </tr>
                                            @endforeach

                                            @php
                                                $debit = 0;
                                                $credit = 0;
                                            @endphp
                                            @foreach ($account['journalItem'] as $journalItemData)
                                                <tr>
                                                    <td>{{ $accountName->name }}</td>
                                                    <td>{{ '-' }}</td>
                                                    <td>{{ $user?->journalNumberFormat($journalItemData->journal_id) }}</td>
                                                    <td>{{ $journalItemData->created_at->format('d-m-Y') }}</td>
                                                    @if($journalItemData->debit == 0)
                                                        <td>-</td>
                                                    @else
                                                        <td>{{ $user?->priceFormat($journalItemData->debit) }}</td>
                                                    @endif
                                                    @if($journalItemData->credit == 0)
                                                        <td>-</td>
                                                    @else
                                                        <td>{{ $user?->priceFormat($journalItemData->credit) }}</td>
                                                    @endif
                                                    <td>
                                                        @if ($journalItemData->debit)
                                                            @php $balance -= $journalItemData->debit @endphp
                                                        @else
                                                            @php $balance += $journalItemData->credit @endphp
                                                        @endif
                                                        {{ $user?->priceFormat($balance) }}
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @endforeach
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
        {{-- <div class="row mt-2">
            <div class="col">
                <input type="hidden"
                    value="{{ __('Ledger') . ' ' . 'Report of' . ' ' . $filter['startDateRange'] . ' to ' . $filter['endDateRange'] }}"
                    id="filename">
                <div class="card p-4 mb-4">
                    <h6 class="mb-0">{{ __('Report') }} :</h6>
                    <h7 class="text-sm mb-0">{{ __('Ledger Summary') }}</h7>
                </div>
            </div>

            <div class="col">
                <div class="card p-4 mb-4">
                    <h6 class="mb-0">{{ __('Duration') }} :</h6>
                    <h7 class="text-sm mb-0">{{ $filter['startDateRange'] . ' to ' . $filter['endDateRange'] }}</h7>
                </div>
            </div>
        </div> --}}
        {{-- @if (!empty($account))
            <div class="row mt-2">
                <div class="col">
                    <div class="card p-4 mb-4">
                        <h6 class="mb-0">{{ __('Account Name') }} :</h6>
                        <h7 class="text-sm mb-0">{{ $account->name }}</h7>
                    </div>
                </div>

                <div class="col">
                    <div class="card p-4 mb-4">
                        <h6 class="mb-0">{{ __('Account Code') }} :</h6>
                        <h7 class="text-sm mb-0">{{ $account->code }}</h7>
                    </div>
                </div>
                <div class="col">
                    <div class="card p-4 mb-4">
                        <h6 class="mb-0">{{ __('Total Debit') }} :</h6>
                        <h7 class="text-sm mb-0">{{ $user?->priceFormat($filter['debit']) }}</h7>
                    </div>
                </div>
                <div class="col">
                    <div class="card p-4 mb-4">
                        <h6 class="mb-0">{{ __('Total Credit') }} :</h6>
                        <h7 class="text-sm mb-0">{{ $user?->priceFormat($filter['credit']) }}</h7>
                    </div>
                </div>

                <div class="col">
                    <div class="card p-4 mb-4">
                        <h6 class="mb-0">{{ __('Balance') }} :</h6>
                        <h7 class="text-sm mb-0">
                            {{ $filter['balance'] > 0 ? __('Cr') . '. ' . $user?->priceFormat(abs($filter['balance'])) : __('Dr') . '. ' . $user?->priceFormat(abs($filter['balance'])) }}
                        </h7>
                    </div>
                </div>
            </div>
        @endif --}}