{{-- @extends(ExtendingLayoutsConstants::ADM) --}}
@php
    try {
$user = Auth::user();
        $lang = Utility::fetchUserLang(user: $user);
        $authUser = $creatorUser?->creatorId() ?? null;
        $creatorUser = User::find($authUser);
    } catch (\Throwable $e) {
        \Log::error('reports/sales_report_receipt — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    }
@endphp
<html lang="{{ $lang ? str_replace('_', '-', is_string(app()->getLocale()) ? app()->getLocale() : DatabaseConstants::DEFAULT_LANG) }}" dir="{{ $settings[SettingsConstants::RTL] == 'on' ? 'rtl' : '' }}">
    <head>
        <title>{{ env('APP_NAME') }} - Sales Report</title>
        @include('fragments.std', [
            'meta_title' => $meta_title,
            'meta_desc' => $meta_desc,
            'meta_vp' => ''
        ])
        @include('fragments.stylesheets', ['settings' => $settings[SettingsConstants::CLR_STG]])
        @if (isset($settings[SettingsConstants::RTL]) && $settings[SettingsConstants::RTL] == 'on')
            <link rel="stylesheet" href="{{ asset('assets/css/style-rtl.css') }}" id="main-style-link">
        @endif
        <script src="{{ asset('js/jquery.min.js') }}"></script>
        <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js"></script>
        <script type="text/javascript" src="{{ asset('js/html2pdf.bundle.min.js') }}"></script>
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script async src="{{ asset('assets/js/routes/reports/sales/receipts/lang/pdf.js') }}"></script>
        <script defer src="{{ asset('assets/js/routes/reports/sales/receipts/pdf.js') }}"></script>
    </head>
    <body class="{{ $color }}">
        <div class="{{ VC::MT4 }}">
            <div class="{{ VC::RW }} justify-content-center" id="printableArea">
                <div class="{{ VC::CM8 }}">
                    <div class="{{ VC::CD }}">
                        <div class="{{ VC::CD_BD }}">
                            @if ($reportName === '#item')
                                <div class="account-main-title {{ VC::MB5 }}">
                                    <h5>
                                        {{ __('Sales By Item of :name as of :start to :end', [
                                            'name'  => $creatorUser?->name,
                                            'start' => $filter['startDateRange'],
                                            'end'   => $filter['endDateRange'],
                                        ]) }}
                                    </h5>

                                    @php
 $totQty = 0; $totAmt = 0.0;
@endphp
                                    <table class="{{ VC::TB }} table-flush {{ VC::MT3 }}" id="report-items-table">
                                        <thead>
                                            <tr>
                                                <th width="33%">{{ __('Invoice Item') }}</th>
                                                <th width="33%" class="{{ VC::TX_END }}">{{ __('Quantity Sold') }}</th>
                                                <th width="33%" class="{{ VC::TX_END }}">{{ __('Amount') }}</th>
                                                <th class="{{ VC::TX_END }}">{{ __('Average Price') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($invoiceItems as $row)
                                                @php
                                                    try {
                                                        $qty   = (float) ($row['quantity'] ?? 0);
                                                        $amt   = (float) ($row['price'] ?? 0);
                                                        $avg   = (float) ($row['avg_price'] ?? ($qty > 0 ? $amt / $qty : 0));
                                                        $totQty += $qty;
                                                        $totAmt += $amt;
                                                    } catch (\Throwable $e) {
                                                        \Log::error('reports/sales_report_receipt — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                    }
@endphp
                                                <tr>
                                                    <td>{{ $row['name'] }}</td>
                                                    <td class="{{ VC::TX_END }}">{{ $qty }}</td>
                                                    <td class="{{ VC::TX_END }}">{{ $user?->priceFormat($amt) }}</td>
                                                    <td class="{{ VC::TX_END }}">{{ $user?->priceFormat($avg) }}</td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="4" class="{{ VC::TXCT_MT }}">{{ __('No data found') }}</td>
                                                </tr>
                                            @endforelse
                                        </tbody>

                                        @if (!empty($invoiceItems))
                                            <tfoot>
                                                <tr>
                                                    <th>{{ __('Total') }}</th>
                                                    <th class="{{ VC::TX_END }}">{{ $totQty }}</th>
                                                    <th class="{{ VC::TX_END }}">{{ $user?->priceFormat($totAmt) }}</th>
                                                    <th class="{{ VC::TX_END }}">
                                                        {{ $user?->priceFormat($totQty > 0 ? ($totAmt / $totQty) : 0) }}
                                                    </th>
                                                </tr>
                                            </tfoot>
                                        @endif
                                    </table>
                                </div>
                            @else
                                <div class="account-main-title {{ VC::MB5 }}">
                                    <h5>
                                        {{ __('Sales By Customer of :name as of :start to :end', [
                                            'name'  => $creatorUser?->name,
                                            'start' => $filter['startDateRange'],
                                            'end'   => $filter['endDateRange'],
                                        ]) }}
                                    </h5>

                                    @php
 $totCount = 0; $totSales = 0.0; $totSalesWithTax = 0.0;
@endphp
                                    <table class="{{ VC::TB }} table-flush {{ VC::MT3 }}" id="report-customers-table">
                                        <thead>
                                            <tr>
                                                <th width="33%">{{ __('Customer Name') }}</th>
                                                <th width="33%" class="{{ VC::TX_END }}">{{ __('Invoice Count') }}</th>
                                                <th width="33%" class="{{ VC::TX_END }}">{{ __('Sales') }}</th>
                                                <th class="{{ VC::TX_END }}">{{ __('Sales With Tax') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($invoiceCustomers as $row)
                                                @php
                                                    try {
                                                        $count = (int) ($row['invoice_count'] ?? 0);
                                                        $amt   = (float) ($row['price'] ?? 0);
                                                        $tax   = (float) ($row['total_tax'] ?? 0);
                                                        $totCount        += $count;
                                                        $totSales        += $amt;
                                                        $totSalesWithTax += ($amt + $tax);
                                                    } catch (\Throwable $e) {
                                                        \Log::error('reports/sales_report_receipt — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                    }
@endphp
                                                <tr>
                                                    <td>{{ $row['name'] }}</td>
                                                    <td class="{{ VC::TX_END }}">{{ $count }}</td>
                                                    <td class="{{ VC::TX_END }}">{{ $user?->priceFormat($amt) }}</td>
                                                    <td class="{{ VC::TX_END }}">{{ $user?->priceFormat($amt + $tax) }}</td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="4" class="{{ VC::TXCT_MT }}">{{ __('No data found') }}</td>
                                                </tr>
                                            @endforelse
                                        </tbody>

                                        @if (!empty($invoiceCustomers))
                                            <tfoot>
                                                <tr>
                                                    <th>{{ __('Total') }}</th>
                                                    <th class="{{ VC::TX_END }}">{{ $totCount }}</th>
                                                    <th class="{{ VC::TX_END }}">{{ $user?->priceFormat($totSales) }}</th>
                                                    <th class="{{ VC::TX_END }}">{{ $user?->priceFormat($totSalesWithTax) }}</th>
                                                </tr>
                                            </tfoot>
                                        @endif
                                    </table>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>
