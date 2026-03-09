@php
    try {
$user = Auth::user();
        $lang = Utility::fetchUserLang();
        $accountId = data_get($account ?? null,'id');
        $showName = ViewsConstants::COA . '.show';
        $showRoute = ($accountId && Route::has($showName)) ? route($showName,$accountId) : (Route::has(Str::kebab($showName)) ? route(Str::kebab($showName),$accountId) : '#');
        $formId = 'report_drilldown';
        $guardMsg = Utility::fetchLinkMessage($lang,ViewsConstants::COA,'chart_of_account_show_route_unavailable') ?? __('Failed to open account drilldown');
        $startRange = data_get($filter ?? [],'startDateRange');
        $endRange = data_get($filter ?? [],'endDateRange');
        $accountsOptions = ((is_array($accounts ?? null) && count($accounts ?? [])) || (($accounts ?? null) instanceof Collection && ($accounts)->isNotEmpty())) ? $accounts : [];
    } catch (\Throwable $e) {
        \Log::error('chart_of_accounts/show — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    }
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@section(YieldingConstants::ADM_PG_TTL)
    {{ __('Account Drilldown Report') }}
@endsection
@section(YieldingConstants::ADM_BDC)
    <li class="{{ VC::BCI }}">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}" {{ Route::has('dashboard') ? '' : 'aria-disabled=true' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    @php
        try {
            $coaIndexBase = ViewsConstants::COA.'.index';
            $coaIndexKebab = Str::kebab($coaIndexBase);
            $coaIndexResolved = Route::has($coaIndexBase) ? $coaIndexBase : (Route::has($coaIndexKebab) ? $coaIndexKebab : null);
            $coaIndexUrl = $coaIndexResolved ? route($coaIndexResolved) : '#';
            $langValue = isset($lang) ? $lang : Utility::fetchUserLang();
            $coaIndexGuardMsg = Utility::fetchLinkMessage($langValue, ViewsConstants::COA, 'coa_index_route_unavailable') ?? 'Chart of account index route is unavailable. Please contact technical support or your domain administrator.';
            $breadcrumbCoaIndexLinkId = 'breadcrumb-coa-index-link';
        } catch (\Throwable $e) {
            \Log::error('chart_of_accounts/show — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
        }
@endphp
    </li>
    <li class="{{ VC::BCI }}">
        <a id="{{ $breadcrumbCoaIndexLinkId }}"
        href="{{ $coaIndexUrl }}"
        data-url="{{ $coaIndexUrl }}"
        data-guard-msg="{{ base64_encode($coaIndexGuardMsg) }}"
        data-sv-localized="true">
            {{ __('Chart of Account') }}
        </a>
    </li>
    @push(StacksConstants::ADM_SCR_PG)
        <script defer src="{{ asset('assets/js/routes/chartOfAccounts/index.js') }}"></script>
    @endpush
    <li class="{{ VC::BCI }}">{{ __('Account Drilldown Report') }}</li>
    <li class="{{ VC::BCI }}">{{ (data_get($account,'code') || data_get($account,'name')) ? ucwords((string) data_get($account,'code','') . ' - ' . (string) data_get($account,'name','')) : __('No account code/name available') }}</li>
@endsection
@section(YieldingConstants::ADM_CTT)
    <div class="{{ VC::RW }}">
        <div class="{{ VC::CS12 }}">
            <div class="{{ VC::MT2 }}" id="multiCollapseExample1">
                <div class="{{ VC::CD }}">
                    <div class="{{ VC::CD_BD }}">
                        {{ Form::open(['url'=>$showRoute,'method'=>'GET','id'=>$formId,'data-url'=>$showRoute,'data-guard-msg'=>$guardMsg]) }}
                            <div class="{{ VC::R_ALC_JCE }}">
                                <div class="{{ VC::CXL10 }}">
                                    <div class="{{ VC::RW }}">
                                        <div class="{{ VC::CL_XL3 }}"><div class="btn-box"></div></div>
                                        <div class="{{ VC::CL_XL3 }}">
                                            <div class="btn-box">
                                                {{ Form::label('start_date', __('Start Date'), ['class'=>VC::FM_LB]) }}
                                                {{ Form::date('start_date', $startRange ?? null, ['class'=>VC::FM_CT.' month-btn']) }}
                                            </div>
                                        </div>
                                        <div class="{{ VC::CL_XL3 }}">
                                            <div class="btn-box">
                                                {{ Form::label('end_date', __('End Date'), ['class'=>VC::FM_LB]) }}
                                                {{ Form::date('end_date', $endRange ?? null, ['class'=>VC::FM_CT.' month-btn']) }}
                                            </div>
                                        </div>
                                        <div class="{{ VC::CL_XL3 }}">
                                            <div class="btn-box">
                                                {{ Form::label('account', __('Account'), ['class'=>VC::FM_LB]) }}
                                                {{ Form::select('account', $accountsOptions, request()->input('account', ''), ['class'=>VC::FM_CT_SL]) }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="{{ VC::C_AT }}">
                                    <div class="{{ VC::RW }}">
                                        <div class="{{ VC::C_AT }} {{ VC::MT4 }}">
                                            <a href="#" class="{{ VC::BT_SM_PM }}" id="applyDrilldown" data-listener-alias="drilldown-apply" data-bs-toggle="tooltip" title="{{ __('Apply') }}"><span class="btn-inner--icon"><i class="{{ VC::TI_SRC }}"></i></span></a>
                                            <a href="{{ $showRoute }}" class="{{ VC::BT_SM_DG }}" data-bs-toggle="tooltip" title="{{ __('Reset') }}"><span class="btn-inner--icon"><i class="{{ VC::TI_TRS_OFF }}"></i></span></a>
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
    <div id="printableArea">
        <div class="{{ VC::RW }} mt-2">
            <div class="{{ VC::C3 }}">
                <div class="{{ VC::CD_POS }}">
                    <h6 class="{{ VC::MB0 }}">{{ __('Report') }} :</h6>
                    <h7 class="{{ VC::TXSM }} {{ VC::MB0 }}">{{ __('Account Drilldown') }}</h7>
                </div>
            </div>
            <div class="{{ VC::C3 }}">
                <div class="{{ VC::CD_POS }}">
                    <h6 class="{{ VC::MB0 }}">{{ __('Account Name') }} :</h6>
                    <h7 class="{{ VC::TXSM }} {{ VC::MB0 }}">{{ data_get($account,'name') ?: __('No account name available') }}</h7>
                </div>
            </div>
            <div class="{{ VC::C3 }}">
                <div class="{{ VC::CD_POS }}">
                    <h6 class="{{ VC::MB0 }}">{{ __('Account Code') }} :</h6>
                    <h7 class="{{ VC::TXSM }} {{ VC::MB0 }}">{{ data_get($account,'code') ?: __('No account code available') }}</h7>
                </div>
            </div>
            <div class="{{ VC::C3 }}">
                <div class="{{ VC::CD_POS }}">
                    <h6 class="{{ VC::MB0 }}">{{ __('Duration') }} :</h6>
                    <h7 class="{{ VC::TXSM }} {{ VC::MB0 }}">{{ ($startRange && $endRange) ? ($startRange.' '.__('to').' '.$endRange) : __('No date range available') }}</h7>
                </div>
            </div>
        </div>
        @php
            try {
                $isPriceFormatAvailable = ($user ?? null) && method_exists($user,'priceFormat');
                $isInvoiceNumberFormatAvailable = ($user ?? null) && method_exists($user,'invoiceNumberFormat');
                $isBillNumberFormatAvailable = ($user ?? null) && method_exists($user,'billNumberFormat');
                $isJournalNumberFormatAvailable = ($user ?? null) && method_exists($user,'journalNumberFormat');
            } catch (\Throwable $e) {
                \Log::error('chart_of_accounts/show — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
            }
@endphp
        <div class="{{ VC::RW }} {{ VC::MB4 }}">
            <div class="{{ VC::C12 }} {{ VC::MB4 }}">
                <div class="{{ VC::CD }}">
                    <div class="{{ VC::CD_BD_TB_BD }}">
                        <div class="{{ VC::TB_RSP }}">
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
                                        $balance ??= 0.0;
                                        $totalDebit ??= 0.0;
                                        $totalCredit ??= 0.0;
                                        try {
                                            $chartDatasRaw = ($accountId && $startRange && $endRange) ? Utility::getAccountData($accountId,$startRange,$endRange) : [];
                                            $chartDatas = is_array($chartDatasRaw) ? $chartDatasRaw : [];
                                            $accountModel = $accountId ? ChartOfAccount::find($accountId) : null;
                                            $accountLabel = data_get($accountModel,'name') ?: __('No account name available');
                                            $invRows = ((is_array(data_get($chartDatas,'invoice')) && count(data_get($chartDatas,'invoice'))) || (data_get($chartDatas,'invoice') instanceof Collection && data_get($chartDatas,'invoice')->isNotEmpty())) ? data_get($chartDatas,'invoice') : [];
                                            $invPayRows = ((is_array(data_get($chartDatas,'invoicepayment')) && count(data_get($chartDatas,'invoicepayment'))) || (data_get($chartDatas,'invoicepayment') instanceof Collection && data_get($chartDatas,'invoicepayment')->isNotEmpty())) ? data_get($chartDatas,'invoicepayment') : [];
                                            $revRows = ((is_array(data_get($chartDatas,'revenue')) && count(data_get($chartDatas,'revenue'))) || (data_get($chartDatas,'revenue') instanceof Collection && data_get($chartDatas,'revenue')->isNotEmpty())) ? data_get($chartDatas,'revenue') : [];
                                            $billRows = ((is_array(data_get($chartDatas,'bill')) && count(data_get($chartDatas,'bill'))) || (data_get($chartDatas,'bill') instanceof Collection && data_get($chartDatas,'bill')->isNotEmpty())) ? data_get($chartDatas,'bill') : [];
                                            $billDataRows = ((is_array(data_get($chartDatas,'billdata')) && count(data_get($chartDatas,'billdata'))) || (data_get($chartDatas,'billdata') instanceof Collection && data_get($chartDatas,'billdata')->isNotEmpty())) ? data_get($chartDatas,'billdata') : [];
                                            $billPayRows = ((is_array(data_get($chartDatas,'billpayment')) && count(data_get($chartDatas,'billpayment'))) || (data_get($chartDatas,'billpayment') instanceof Collection && data_get($chartDatas,'billpayment')->isNotEmpty())) ? data_get($chartDatas,'billpayment') : [];
                                            $payRows = ((is_array(data_get($chartDatas,'payment')) && count(data_get($chartDatas,'payment'))) || (data_get($chartDatas,'payment') instanceof Collection && data_get($chartDatas,'payment')->isNotEmpty())) ? data_get($chartDatas,'payment') : [];
                                            $jrRows = ((is_array(data_get($chartDatas,'journalItem')) && count(data_get($chartDatas,'journalItem'))) || (data_get($chartDatas,'journalItem') instanceof Collection && data_get($chartDatas,'journalItem')->isNotEmpty())) ? data_get($chartDatas,'journalItem') : [];
                                        } catch (\Throwable $e) {
                                            \Log::error('chart_of_accounts/show — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                        }
@endphp
                                    @if(!empty($invRows))
                                        @foreach($invRows as $invoiceData)
                                            @php
                                                try {
                                                    $invId = data_get($invoiceData,'invoice_id');
                                                    $invoice = $invId ? Invoice::find($invId) : null;
                                                    $price = (float) (data_get($invoiceData,'price',0) ?? 0);
                                                    $qty = (float) (data_get($invoiceData,'quantity',0) ?? 0);
                                                    $total = $price * $qty;
                                                    $balance += $total;
                                                    $totalCredit += $total;
                                                } catch (\Throwable $e) {
                                                    \Log::error('chart_of_accounts/show — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                }
@endphp
                                            <tr>
                                                <td>{{ $accountLabel }}</td>
                                                <td>{{ data_get($invoice,'customer.name') ?: __('No customer name available') }}</td>
                                                <td>{{ ($num = data_get($invoice,'invoice_id')) ? ($isInvoiceNumberFormatAvailable ? $user?->invoiceNumberFormat($num) : __('No invoice number available')) : __('Failed to format invoice number') }}</td>
                                                <td>{{ ($d = data_get($invoiceData,'created_at')) ? $d->format('d-m-Y') : __('No transaction date available') }}</td>
                                                <td>-</td>
                                                <td>{{ $isPriceFormatAvailable ? $user?->priceFormat($total) : __('Failed to format total') }}</td>
                                                <td>{{ $isPriceFormatAvailable ? $user?->priceFormat($balance) : __('Failed to format balance') }}</td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr><td colspan="7"><div class="{{ VC::RW }} {{ VC::JCC }} {{ VC::ALC }}"><div class="{{ VC::C6 }} {{ VC::TXCT }}"><p class="{{ VC::TXSM }} {{ VC::TX_MUTED }}">{{ __('No data available for invoices') }}</p></div></div></td></tr>
                                    @endif
                                    @if(!empty($invPayRows))
                                        @foreach($invPayRows as $invoicePaymentData)
                                            @php
                                                try {
                                                    $invId = data_get($invoicePaymentData,'invoice_id');
                                                    $invoice = $invId ? Invoice::find($invId) : null;
                                                    $amt = (float) (data_get($invoicePaymentData,'amount',0) ?? 0);
                                                    $balance += $amt;
                                                    $totalCredit += $amt;
                                                } catch (\Throwable $e) {
                                                    \Log::error('chart_of_accounts/show — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                }
@endphp
                                            <tr>
                                                <td>{{ $accountLabel }}</td>
                                                <td>{{ data_get($invoice,'customer.name') ?: __('No customer name available') }}</td>
                                                <td>{{ ($num = data_get($invoice,'invoice_id')) ? ($isInvoiceNumberFormatAvailable ? ($user?->invoiceNumberFormat($num).' '.__('Manually Payment')) : __('No invoice number available')) : __('Failed to format invoice number') }}</td>
                                                <td>{{ ($d = data_get($invoicePaymentData,'created_at')) ? $d->format('d-m-Y') : __('No transaction date available') }}</td>
                                                <td>-</td>
                                                <td>{{ $isPriceFormatAvailable ? $user?->priceFormat($amt) : __('Failed to format amount') }}</td>
                                                <td>{{ $isPriceFormatAvailable ? $user?->priceFormat($balance) : __('Failed to format balance') }}</td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr><td colspan="7"><div class="{{ VC::RW }} {{ VC::JCC }} {{ VC::ALC }}"><div class="{{ VC::C6 }} {{ VC::TXCT }}"><p class="{{ VC::TXSM }} {{ VC::TX_MUTED }}">{{ __('No data available for invoice payments') }}</p></div></div></td></tr>
                                    @endif
                                    @if(!empty($revRows))
                                        @foreach($revRows as $revenueData)
                                            @php
                                                try {
                                                    $amt = (float) (data_get($revenueData,'amount',0) ?? 0);
                                                    $balance += $amt;
                                                    $totalCredit += $amt;
                                                } catch (\Throwable $e) {
                                                    \Log::error('chart_of_accounts/show — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                }
@endphp
                                            <tr>
                                                <td>{{ $accountLabel }}</td>
                                                <td>{{ data_get($revenueData,'customer.name') ?: __('No customer name available') }}</td>
                                                <td>{{ __('Revenue') }}</td>
                                                <td>{{ ($d = data_get($revenueData,'created_at')) ? $d->format('d-m-Y') : __('No transaction date available') }}</td>
                                                <td>-</td>
                                                <td>{{ $isPriceFormatAvailable ? $user?->priceFormat($amt) : __('Failed to format amount') }}</td>
                                                <td>{{ $isPriceFormatAvailable ? $user?->priceFormat($balance) : __('Failed to format balance') }}</td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr><td colspan="7"><div class="{{ VC::RW }} {{ VC::JCC }} {{ VC::ALC }}"><div class="{{ VC::C6 }} {{ VC::TXCT }}"><p class="{{ VC::TXSM }} {{ VC::TX_MUTED }}">{{ __('No data available for revenues') }}</p></div></div></td></tr>
                                    @endif
                                    @if(!empty($billRows))
                                        @foreach($billRows as $billProduct)
                                            @php
                                                try {
                                                    $bill = ($bid = data_get($billProduct,'bill_id')) ? Bill::find($bid) : null;
                                                    $vendor = ($vid = data_get($bill,'vendor_id')) ? Vendor::find($vid) : null;
                                                    $price = (float) (data_get($billProduct,'price',0) ?? 0);
                                                    $qty = (float) (data_get($billProduct,'quantity',0) ?? 0);
                                                    $total = $price * $qty;
                                                    $balance -= $total;
                                                    $totalCredit -= $total;
                                                } catch (\Throwable $e) {
                                                    \Log::error('chart_of_accounts/show — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                }
@endphp
                                            <tr>
                                                <td>{{ $accountLabel }}</td>
                                                <td>{{ data_get($vendor,'name') ?: __('No vendor name available') }}</td>
                                                <td>{{ ($num = data_get($bill,'bill_id')) ? ($isBillNumberFormatAvailable ? $user?->billNumberFormat($num) : __('No Bill Identifier available')) : __('Failed to format Bill Identifier') }}</td>
                                                <td>{{ ($d = data_get($billProduct,'created_at')) ? $d->format('d-m-Y') : __('No transaction date available') }}</td>
                                                <td>{{ $isPriceFormatAvailable ? $user?->priceFormat($total) : __('Failed to format total') }}</td>
                                                <td>-</td>
                                                <td>{{ $isPriceFormatAvailable ? $user?->priceFormat($balance) : __('Failed to format balance') }}</td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr><td colspan="7"><div class="{{ VC::RW }} {{ VC::JCC }} {{ VC::ALC }}"><div class="{{ VC::C6 }} {{ VC::TXCT }}"><p class="{{ VC::TXSM }} {{ VC::TX_MUTED }}">{{ __('No data available for bills') }}</p></div></div></td></tr>
                                    @endif
                                    @if(!empty($billDataRows))
                                        @foreach($billDataRows as $billData)
                                            @php
                                                try {
                                                    $bill = ($ref = data_get($billData,'ref_id')) ? Bill::find($ref) : null;
                                                    $vendor = ($vid = data_get($bill,'vendor_id')) ? Vendor::find($vid) : null;
                                                    $price = (float) (data_get($billData,'price',0) ?? 0);
                                                    $balance -= $price;
                                                    $totalDebit -= $price;
                                                } catch (\Throwable $e) {
                                                    \Log::error('chart_of_accounts/show — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                }
@endphp
                                            <tr>
                                                <td>{{ $accountLabel }}</td>
                                                <td>{{ data_get($vendor,'name') ?: __('No vendor name available') }}</td>
                                                <td>{{ ($num = data_get($bill,'bill_id')) ? ($isBillNumberFormatAvailable ? $user?->billNumberFormat($num) : __('No Bill Identifier available')) : __('Failed to format Bill Identifier') }}</td>
                                                <td>{{ ($d = data_get($billData,'created_at')) ? $d->format('d-m-Y') : __('No transaction date available') }}</td>
                                                <td>{{ $isPriceFormatAvailable ? $user?->priceFormat($price) : __('Failed to format amount') }}</td>
                                                <td>-</td>
                                                <td>{{ $isPriceFormatAvailable ? $user?->priceFormat($balance) : __('Failed to format balance') }}</td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr><td colspan="7"><div class="{{ VC::RW }} {{ VC::JCC }} {{ VC::ALC }}"><div class="{{ VC::C6 }} {{ VC::TXCT }}"><p class="{{ VC::TXSM }} {{ VC::TX_MUTED }}">{{ __('No data available for bill data') }}</p></div></div></td></tr>
                                    @endif
                                    @if(!empty($billPayRows))
                                        @foreach($billPayRows as $billPaymentData)
                                            @php
                                                try {
                                                    $bid = data_get($billPaymentData,'bill_id');
                                                    $bill = $bid ? Bill::find($bid) : null;
                                                    $vendor = ($vid = data_get($bill,'vendor_id')) ? Vendor::find($vid) : null;
                                                    $amt = (float) (data_get($billPaymentData,'amount',0) ?? 0);
                                                    $balance += $amt;
                                                    $totalDebit += $amt;
                                                } catch (\Throwable $e) {
                                                    \Log::error('chart_of_accounts/show — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                }
@endphp
                                            <tr>
                                                <td>{{ $accountLabel }}</td>
                                                <td>{{ data_get($vendor,'name') ?: __('No vendor name available') }}</td>
                                                <td>{{ ($num = data_get($bill,'bill_id')) ? ($isBillNumberFormatAvailable ? ($user?->billNumberFormat($num).' '.__('Manually Payment')) : __('No Bill Identifier available')) : __('Failed to format Bill Identifier') }}</td>
                                                <td>{{ ($d = data_get($billPaymentData,'created_at')) ? $d->format('d-m-Y') : __('No transaction date available') }}</td>
                                                <td>{{ $isPriceFormatAvailable ? $user?->priceFormat($amt) : __('Failed to format amount') }}</td>
                                                <td>-</td>
                                                <td>{{ $isPriceFormatAvailable ? $user?->priceFormat($totalCredit - $totalDebit) : __('Failed to format balance') }}</td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr><td colspan="7"><div class="{{ VC::RW }} {{ VC::JCC }} {{ VC::ALC }}"><div class="{{ VC::C6 }} {{ VC::TXCT }}"><p class="{{ VC::TXSM }} {{ VC::TX_MUTED }}">{{ __('No data available for bill payments') }}</p></div></div></td></tr>
                                    @endif
                                    @if(!empty($payRows))
                                        @foreach($payRows as $paymentData)
                                            @php
                                                try {
                                                    $vendor = ($vid = data_get($paymentData,'vendor_id')) ? Vendor::find($vid) : null;
                                                    $amt = (float) (data_get($paymentData,'amount',0) ?? 0);
                                                    $balance += $amt;
                                                    $totalDebit += $amt;
                                                } catch (\Throwable $e) {
                                                    \Log::error('chart_of_accounts/show — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                }
@endphp
                                            <tr>
                                                <td>{{ $accountLabel }}</td>
                                                <td>{{ data_get($vendor,'name') ?: __('No vendor name available') }}</td>
                                                <td>{{ __('Payment') }}</td>
                                                <td>{{ ($d = data_get($paymentData,'created_at')) ? $d->format('d-m-Y') : __('No transaction date available') }}</td>
                                                <td>{{ $isPriceFormatAvailable ? $user?->priceFormat($amt) : __('Failed to format amount') }}</td>
                                                <td>-</td>
                                                <td>{{ $isPriceFormatAvailable ? $user?->priceFormat($totalCredit - $totalDebit) : __('Failed to format balance') }}</td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr><td colspan="7"><div class="{{ VC::RW }} {{ VC::JCC }} {{ VC::ALC }}"><div class="{{ VC::C6 }} {{ VC::TXCT }}"><p class="{{ VC::TXSM }} {{ VC::TX_MUTED }}">{{ __('No data available for payments') }}</p></div></div></td></tr>
                                    @endif
                                    @if(!empty($jrRows))
                                        @foreach($jrRows as $journalItemData)
                                            @php
                                                try {
                                                    $debit = (float) (data_get($journalItemData,'debit',0) ?? 0);
                                                    $credit = (float) (data_get($journalItemData,'credit',0) ?? 0);
                                                    $balance = $debit ? ($balance - $debit) : ($balance + $credit);
                                                } catch (\Throwable $e) {
                                                    \Log::error('chart_of_accounts/show — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                }
@endphp
                                            <tr>
                                                <td>{{ $accountLabel }}</td>
                                                <td>{{ __('No name available') }}</td>
                                                <td>{{ ($num = data_get($journalItemData,'journal_id')) ? ($isJournalNumberFormatAvailable ? $user?->journalNumberFormat($num) : __('No journal number available')) : __('Failed to format journal number') }}</td>
                                                <td>{{ ($d = data_get($journalItemData,'created_at')) ? $d->format('d-m-Y') : __('No transaction date available') }}</td>
                                                <td>{{ $isPriceFormatAvailable ? $user?->priceFormat($debit) : __('Failed to format debit') }}</td>
                                                <td>{{ $isPriceFormatAvailable ? $user?->priceFormat($credit) : __('Failed to format credit') }}</td>
                                                <td>{{ $isPriceFormatAvailable ? $user?->priceFormat($balance) : __('Failed to format balance') }}</td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr><td colspan="7"><div class="{{ VC::RW }} {{ VC::JCC }} {{ VC::ALC }}"><div class="{{ VC::C6 }} {{ VC::TXCT }}"><p class="{{ VC::TXSM }} {{ VC::TX_MUTED }}">{{ __('No data available for journal items') }}</p></div></div></td></tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push(StacksConstants::ADM_SCR_PG)
    <script defer src="{{ asset('assets/js/routes/chartOfAccounts/show.js') }}"></script>
@endpush
