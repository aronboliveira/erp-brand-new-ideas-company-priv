{{-- @extends(ExtendingLayoutsConstants::ADM) --}}
@php
    try {
$user = Auth::user();
        $lang = Utility::fetchUserLang(user: $user);
        $authUser = $user?->creatorId() ?? null;
        $creatorUser = $authUser ? User::find($authUser) : null;
        $settings = Utility::settings();
        $color = !empty($settings[SettingsConstants::THML_CLR]) ? $settings[SettingsConstants::THML_CLR] : 'theme-3';
    } catch (\Throwable $e) {
        \Log::error('reports/payable_report_receipt — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    }
@endphp
<html lang="{{ $lang ? str_replace('_', '-', is_string(app()->getLocale()) ? app()->getLocale() : DatabaseConstants::DEFAULT_LANG) }}" dir="{{ $settings[SettingsConstants::RTL] == 'on' ? 'rtl' : '' }}">
    <head>
        <title>{{ env('APP_NAME') }} - Payable Report</title>
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
        <script async src="{{ asset('assets/js/routes/reports/payables/receipts/lang/pdf.js') }}"></script>
        <script defer src="{{ asset('assets/js/routes/reports/payables/receipts/pdf.js') }}"></script>
    </head>
    <body class="{{ $color }}">
        <div class="{{ VC::MT4 }}">
            <div class="{{ VC::RW }}">
                <div class="{{ VC::C12 }}" id="invoice-container">
                    <div class="{{ VC::CD }}">
                        <div class="{{ VC::CD_BD }}">
                            <div class="{{ VC::RW }}">
                                <div class="{{ VC::CS12 }}">
                                    <div class="tab-content" id="myTabContent2">
                                        @if($reportName  == '#vendor_balance')
                                            <table class="{{ VC::TB }} table-flush" id="report-dataTable">
                                                <thead>
                                                    <tr>
                                                        <th width="33%">{{ __('Vendor Name') }}</th>
                                                        <th width="33%">{{ __('Billed Amount') }}</th>
                                                        <th width="33%">{{ __('Available Debit') }}</th>
                                                        <th class="{{ VC::TX_END }}">{{ __('Closing Balance') }}</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @php
                                                        $mergedArray ??= [];
                                                        try {
                                                            foreach (($payableVendors ?? []) as $item) {
                                                                $name = $item['name'] ?? __('No vendor name available');
                                                                if (!isset($mergedArray[$name])) {
                                                                    $mergedArray[$name] = [
                                                                        'name'        => $name,
                                                                        'price'       => 0.0,
                                                                        'pay_price'   => 0.0,
                                                                        'total_tax'   => 0.0,
                                                                        'debit_price' => 0.0,
                                                                    ];
                                                                }
                                                                $mergedArray[$name]['price']       += floatval($item['price'] ?? 0);
                                                                if (!is_null($item['pay_price'] ?? null)) {
                                                                    $mergedArray[$name]['pay_price'] += floatval($item['pay_price']);
                                                                }
                                                                $mergedArray[$name]['total_tax']   += floatval($item['total_tax'] ?? 0);
                                                                $mergedArray[$name]['debit_price'] += floatval($item['debit_price'] ?? 0);
                                                            }
                                                            $resultArray = array_values($mergedArray);
                                                            $total = 0.0;
                                                        } catch (\Throwable $e) {
                                                            \Log::error('reports/payable_report_receipt — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                        }
@endphp

                                                    @forelse ($resultArray as $receivableCustomer)
                                                        @php
                                                            try {
                                                                $customerBalance = ($receivableCustomer['price'] ?? 0) + ($receivableCustomer['total_tax'] ?? 0) - ($receivableCustomer['pay_price'] ?? 0);
                                                                $balance = $customerBalance - ($receivableCustomer['debit_price'] ?? 0);
                                                                $total += $balance;
                                                            } catch (\Throwable $e) {
                                                                \Log::error('reports/payable_report_receipt — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                            }
@endphp
                                                        <tr>
                                                            <td>{{ $receivableCustomer['name'] ?? __('No vendor name available') }}</td>
                                                            <td>{{ $user?->priceFormat($customerBalance) ?? number_format((float)$customerBalance,2) }}</td>
                                                            <td>{{ $user?->priceFormat($receivableCustomer['debit_price'] ?? 0) ?? number_format((float)($receivableCustomer['debit_price'] ?? 0),2) }}</td>
                                                            <td class="{{ VC::TX_END }}">{{ $user?->priceFormat($balance) ?? number_format((float)$balance,2) }}</td>
                                                        </tr>
                                                    @empty
                                                        <tr>
                                                            <td colspan="4">{{ __('No vendor balances available') }}</td>
                                                        </tr>
                                                    @endforelse
                                                    @if (!empty($resultArray))
                                                        <tr>
                                                            <th>{{ __('Total') }}</th>
                                                            <td></td>
                                                            <td></td>
                                                            <th class="{{ VC::TX_END }}">{{ $user?->priceFormat($total) ?? number_format((float)$total,2) }}</th>
                                                        </tr>
                                                    @endif
                                                </tbody>
                                            </table>

                                        @elseif($reportName == '#payable_summary')
                                            <table class="{{ VC::TB }} table-flush" id="report-dataTable">
                                                <thead>
                                                    <tr>
                                                        <th>{{ __('Vendor Name') }}</th>
                                                        <th>{{ __('Date') }}</th>
                                                        <th>{{ __('Transaction') }}</th>
                                                        <th>{{ __('Status') }}</th>
                                                        <th>{{ __('Transaction Type') }}</th>
                                                        <th>{{ __('Total') }}</th>
                                                        <th>{{ __('Balance') }}</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @php
                                                        $total ??= 0.0;
                                                        $totalAmount ??= 0.0;
                                                        try {
                                                            $list = $payableSummaries ?? [];
                                                            usort($list, function($a,$b){
                                                                return strtotime(($b['bill_date'] ?? '1970-01-01')) <=> strtotime(($a['bill_date'] ?? '1970-01-01'));
                                                            });
                                                        } catch (\Throwable $e) {
                                                            \Log::error('reports/payable_report_receipt — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                        }
@endphp
                                                    @forelse ($list as $payableSummary)
                                                        @php
                                                            try {
                                                                $isBill = (bool)($payableSummary['bill'] ?? false);
                                                                $payableBalance = $isBill
                                                                    ? (float)($payableSummary['price'] ?? 0) + (float)($payableSummary['total_tax'] ?? 0)
                                                                    : -(float)($payableSummary['price'] ?? 0);
                                                                $pay_price = (float)($payableSummary['pay_price'] ?? 0);
                                                                $balance = $payableBalance - $pay_price;
                                                                $total += $balance;
                                                                $totalAmount += $payableBalance;

                                                                $status = $payableSummary['status'] ?? null;
                                                                $statusClasses = [
                                                                    0 => 'bg-secondary',
                                                                    1 => 'bg-warning',
                                                                    2 => 'bg-danger',
                                                                    3 => 'bg-info',
                                                                    4 => VC::BG_P,
                                                                ];
                                                                $bgClass = $status !== null ? ($statusClasses[$status] ?? null) : null;
                                                            } catch (\Throwable $e) {
                                                                \Log::error('reports/payable_report_receipt — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                            }
@endphp
                                                        <tr>
                                                            <td>{{ $payableSummary['name'] ?? __('No vendor name available') }}</td>
                                                            <td>{{ $payableSummary['bill_date'] ?? __('No date available') }}</td>
                                                            <td>
                                                                @if ($isBill)
                                                                    @if (($payableSummary['type'] ?? '') === 'Bill')
                                                                        {{ $user?->billNumberFormat($payableSummary['bill']) ?? __('Could not format Bill Identifier') }}
                                                                    @elseif(($payableSummary['type'] ?? '') === 'Expense')
                                                                        {{ $user?->expenseNumberFormat($payableSummary['bill']) ?? __('Could not format expense number') }}
                                                                    @else
                                                                        {{ __('Unknown transaction') }}
                                                                    @endif
                                                                @else
                                                                    {{ __('Debit Note') }}
                                                                @endif
                                                            </td>
                                                            <td>
                                                                @if($bgClass)
                                                                    <span class="status_badge {{ VC::BDG }} {{ $bgClass }} p-2 px-3 rounded">
                                                                        {{ __(Invoice::$statuses[$status] ?? __('No status available')) }}
                                                                    </span>
                                                                @else
                                                                    <span class="p-2 {{ VC::PX3 }}">{{ __('No status available') }}</span>
                                                                @endif
                                                            </td>
                                                            <td>{{ $isBill ? ($payableSummary['type'] ?? __('No type available')) : __('Debit Note') }}</td>
                                                            <td>{{ $user?->priceFormat($payableBalance) ?? number_format((float)$payableBalance,2) }}</td>
                                                            <td>{{ $user?->priceFormat($balance) ?? number_format((float)$balance,2) }}</td>
                                                        </tr>
                                                    @empty
                                                        <tr>
                                                            <td colspan="7">{{ __('No payable summaries available') }}</td>
                                                        </tr>
                                                    @endforelse
                                                    @if (!empty($list))
                                                        <tr>
                                                            <th>{{ __('Total') }}</th>
                                                            <th></th>
                                                            <th></th>
                                                            <th></th>
                                                            <th></th>
                                                            <th>{{ $user?->priceFormat($totalAmount) ?? number_format((float)$totalAmount,2) }}</th>
                                                            <th>{{ $user?->priceFormat($total) ?? number_format((float)$total,2) }}</th>
                                                        </tr>
                                                    @endif
                                                </tbody>
                                            </table>

                                        @else
                                            <table class="{{ VC::TB }} table-flush" id="report-dataTable">
                                                <thead>
                                                    <tr>
                                                        <th>{{ __('Customer Name') }}</th>
                                                        <th>{{ __('Date') }}</th>
                                                        <th>{{ __('Transaction') }}</th>
                                                        <th>{{ __('Status') }}</th>
                                                        <th>{{ __('Transaction Type') }}</th>
                                                        <th>{{ __('Item Name') }}</th>
                                                        <th>{{ __('Quantity Ordered') }}</th>
                                                        <th>{{ __('Item Price') }}</th>
                                                        <th>{{ __('Total') }}</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @php
                                                        $total ??= 0.0;
                                                        $totalQuantity ??= 0.0;
                                                        try {
                                                            $rows = $payableDetails ?? [];
                                                            usort($rows, function($a,$b){
                                                                return strtotime(($b['bill_date'] ?? '1970-01-01')) <=> strtotime(($a['bill_date'] ?? '1970-01-01'));
                                                            });
                                                        } catch (\Throwable $e) {
                                                            \Log::error('reports/payable_report_receipt — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                        }
@endphp

                                                    @forelse ($rows as $payableDetail)
                                                        @php
                                                            try {
                                                                $isBill = (bool)($payableDetail['bill'] ?? false);
                                                                $unitPrice = $isBill ? (float)($payableDetail['price'] ?? 0) : -(float)($payableDetail['price'] ?? 0);
                                                                $quantity = $isBill ? (int)($payableDetail['quantity'] ?? 0) : 0;
                                                                $itemTotal = $isBill ? $unitPrice * $quantity : -(float)($payableDetail['price'] ?? 0);
                                                                $total += $itemTotal;
                                                                $totalQuantity += $quantity;

                                                                $status = $payableDetail['status'] ?? null;
                                                                $statusClasses = [
                                                                    0 => 'bg-secondary',
                                                                    1 => 'bg-warning',
                                                                    2 => 'bg-danger',
                                                                    3 => 'bg-info',
                                                                    4 => VC::BG_P,
                                                                ];
                                                                $bgClass = $status !== null ? ($statusClasses[$status] ?? null) : null;
                                                            } catch (\Throwable $e) {
                                                                \Log::error('reports/payable_report_receipt — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                            }
@endphp
                                                        <tr>
                                                            <td>{{ $payableDetail['name'] ?? __('No customer name available') }}</td>
                                                            <td>{{ $payableDetail['bill_date'] ?? __('No date available') }}</td>
                                                            <td>
                                                                @if ($isBill)
                                                                    @if (($payableDetail['type'] ?? '') === 'Bill')
                                                                        {{ $user?->billNumberFormat($payableDetail['bill']) ?? __('Could not format Bill Identifier') }}
                                                                    @elseif(($payableDetail['type'] ?? '') === 'Expense')
                                                                        {{ $user?->expenseNumberFormat($payableDetail['bill']) ?? __('Could not format expense number') }}
                                                                    @else
                                                                        {{ __('Unknown transaction') }}
                                                                    @endif
                                                                @else
                                                                    {{ __('Debit Note') }}
                                                                @endif
                                                            </td>
                                                            <td>
                                                                @if($bgClass)
                                                                    <span class="status_badge {{ VC::BDG }} {{ $bgClass }} p-2 px-3 rounded">
                                                                        {{ __(Invoice::$statuses[$status] ?? __('No status available')) }}
                                                                    </span>
                                                                @else
                                                                    <span class="p-2 {{ VC::PX3 }}">{{ __('No status available') }}</span>
                                                                @endif
                                                            </td>
                                                            <td>{{ $isBill ? ($payableDetail['type'] ?? __('No type available')) : __('Debit Note') }}</td>
                                                            <td>{{ $payableDetail['product_name'] ?? __('No item name available') }}</td>
                                                            <td>{{ $quantity }}</td>
                                                            <td>{{ $user?->priceFormat($unitPrice) ?? number_format((float)$unitPrice,2) }}</td>
                                                            <td>{{ $user?->priceFormat($itemTotal) ?? number_format((float)$itemTotal,2) }}</td>
                                                        </tr>
                                                    @empty
                                                        <tr>
                                                            <td colspan="9">{{ __('No payable details available') }}</td>
                                                        </tr>
                                                    @endforelse
                                                    @if (!empty($rows))
                                                        <tr>
                                                            <th>{{ __('Total') }}</th>
                                                            <th></th>
                                                            <th></th>
                                                            <th></th>
                                                            <th></th>
                                                            <th></th>
                                                            <th>{{ $totalQuantity }}</th>
                                                            <th></th>
                                                            <th>{{ $user?->priceFormat($total) ?? number_format((float)$total,2) }}</th>
                                                        </tr>
                                                    @endif
                                                </tbody>
                                            </table>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>
