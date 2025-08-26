{{-- @extends(ExtendingLayoutsConstants::ADM) --}}
@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        StacksConstants,
        ViewsConstants as VW,
        ViewClassNamesConstants as VC,
        YieldingConstants,
    };
    use App\Models\{User, Utility};
    use Collective\Html\FormFacade as Form;
    use Illuminate\Support\Facades\{Auth, Route};
    use Illuminate\Support\Str;
    $user = Auth::user();
    $lang = Utility::fetchUserLang(user: $user);
    $authUser = $user?->creatorId() ?? null;
    $creatorUser = User::find($authUser);
    $settings = Utility::settings();
    $color = (!empty($settings[SettingsConstants::THML_CLR])) ? $settings[SettingsConstants::THML_CLR] : 'theme-3';
@endphp
<html lang="{{ $lang ? str_replace('_', '-', is_string(app()->getLocale()) ? app()->getLocale() : DatabaseConstants::DEFAULT_LANG) }}" dir="{{ $settings[SettingsConstants::RTL] == 'on' ? 'rtl' : '' }}">
    <head>
        @include('fragments.std', [
            'meta_title' => $meta_title,
            'meta_desc' => $meta_desc,
            'meta_vp' => ''
        ])
        <title>{{ env('APP_NAME') }} - Receivable Report</title>
        @include('fragments.stylesheets', ['settings' => $settings[SettingsConstants::CLR_STG]])
        @if (isset($settings[SettingsConstants::RTL]) && $settings[SettingsConstants::RTL] == 'on')
            <link rel="stylesheet" href="{{ asset('assets/css/style-rtl.css') }}" id="main-style-link">
        @endif
    </head>
    <body class="{{ $color }}">
        <div class="mt-4">
            <div class="row">
                <div class="col-12" id="invoice-container">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-sm-12">
                                    <div class="tab-content" id="myTabContent2">
                                        @if ($reportName == '#customer_balance')
                                            <table class="{{ VC::TB }} table-flush" id="report-dataTable">
                                                <thead>
                                                    <tr>
                                                        <th width="33%">{{ __('Customer Name') }}</th>
                                                        <th width="33%">{{ __('Invoice Balance') }}</th>
                                                        <th width="33%">{{ __('Available Credits') }}</th>
                                                        <th class="text-end">{{ __('Balance') }}</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @php
                                                        $mergedArray = [];

                                                        foreach (($receivableCustomers ?? []) as $item) {
                                                            $name = $item['name'] ?? __('No customer name available');

                                                            if (!isset($mergedArray[$name])) {
                                                                $mergedArray[$name] = [
                                                                    'name'         => $name,
                                                                    'price'        => 0.0,
                                                                    'pay_price'    => 0.0,
                                                                    'total_tax'    => 0.0,
                                                                    'credit_price' => 0.0,
                                                                ];
                                                            }

                                                            $mergedArray[$name]['price']        += floatval($item['price'] ?? 0);
                                                            if (array_key_exists('pay_price', $item) && $item['pay_price'] !== null) {
                                                                $mergedArray[$name]['pay_price'] += floatval($item['pay_price']);
                                                            }
                                                            $mergedArray[$name]['total_tax']    += floatval($item['total_tax'] ?? 0);
                                                            $mergedArray[$name]['credit_price'] += floatval($item['credit_price'] ?? 0);
                                                        }

                                                        $resultArray = array_values($mergedArray);
                                                        $total = 0;
                                                    @endphp

                                                    @forelse ($resultArray as $receivableCustomer)
                                                        @php
                                                            $customerBalance = ($receivableCustomer['price'] ?? 0)
                                                                            + ($receivableCustomer['total_tax'] ?? 0)
                                                                            - ($receivableCustomer['pay_price'] ?? 0);

                                                            $balance = $customerBalance - ($receivableCustomer['credit_price'] ?? 0);
                                                            $total  += $balance;
                                                        @endphp
                                                        <tr>
                                                            <td>{{ $receivableCustomer['name'] ?: __('No customer name available') }}</td>
                                                            <td>{{ $user?->priceFormat($customerBalance) }}</td>
                                                            <td>{{ $user?->priceFormat($receivableCustomer['credit_price'] ?? 0) }}</td>
                                                            <td class="text-end">{{ $user?->priceFormat($balance) }}</td>
                                                        </tr>
                                                    @empty
                                                        <tr>
                                                            <td colspan="4" class="text-center">{{ __('No receivable customers available') }}</td>
                                                        </tr>
                                                    @endforelse

                                                    @if (!empty($resultArray))
                                                        <tr>
                                                            <th>{{ __('Total') }}</th>
                                                            <td></td>
                                                            <td></td>
                                                            <th class="text-end">{{ $user?->priceFormat($total) }}</th>
                                                        </tr>
                                                    @endif
                                                </tbody>
                                            </table>
                                        @elseif ($reportName == '#receivable_summary')
                                            <table class="{{ VC::TB }} table-flush" id="report-dataTable">
                                                <thead>
                                                    <tr>
                                                        <th>{{ __('Customer Name') }}</th>
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
                                                        $receivableSummaries = $receivableSummaries ?? [];

                                                        $total       = 0;
                                                        $totalAmount = 0;

                                                        // Robust sort (handles missing or invalid dates)
                                                        usort($receivableSummaries, function ($a, $b) {
                                                            $ad = strtotime($a['issue_date'] ?? '1970-01-01');
                                                            $bd = strtotime($b['issue_date'] ?? '1970-01-01');
                                                            return $bd <=> $ad;
                                                        });
                                                    @endphp

                                                    @forelse ($receivableSummaries as $receivableSummary)
                                                        @php
                                                            $isInvoice = !empty($receivableSummary['invoice']);

                                                            $price     = floatval($receivableSummary['price']      ?? 0);
                                                            $totalTax  = floatval($receivableSummary['total_tax']  ?? 0);
                                                            $payPrice  = floatval($receivableSummary['pay_price']  ?? 0);

                                                            $receivableBalance = $isInvoice ? ($price + $totalTax) : (-1 * $price);
                                                            $balance           = $receivableBalance - $payPrice;

                                                            $total       += $balance;
                                                            $totalAmount += $receivableBalance;

                                                            $status      = $receivableSummary['status'] ?? null;
                                                            $statusClasses = [
                                                                0 => 'bg-secondary',
                                                                1 => 'bg-warning',
                                                                2 => 'bg-danger',
                                                                3 => 'bg-info',
                                                                4 => 'bg-primary',
                                                            ];
                                                            $bgClass = $status !== null && isset($statusClasses[$status]) ? $statusClasses[$status] : null;
                                                        @endphp

                                                        <tr>
                                                            <td>{{ $receivableSummary['name'] ?? __('No customer name available') }}</td>
                                                            <td>{{ $receivableSummary['issue_date'] ?? __('No issue date available') }}</td>

                                                            <td>
                                                                @if ($isInvoice)
                                                                    {{ $user?->invoiceNumberFormat($receivableSummary['invoice']) }}
                                                                @else
                                                                    {{ __('Credit Note') }}
                                                                @endif
                                                            </td>

                                                            <td>
                                                                @if ($bgClass)
                                                                    <span class="status_badge {{ VC::BDG }} {{ $bgClass }} p-2 px-3 rounded">
                                                                        {{ __(\App\Models\Invoice::$statuses[$status] ?? 'Unknown status') }}
                                                                    </span>
                                                                @else
                                                                    <span class="status_badge {{ VC::BDG }} bg-secondary p-2 px-3 rounded">
                                                                        {{ __('No status available') }}
                                                                    </span>
                                                                @endif
                                                            </td>

                                                            <td>
                                                                @if ($isInvoice)
                                                                    {{ __('Invoice') }}
                                                                @else
                                                                    {{ __('Credit Note') }}
                                                                @endif
                                                            </td>

                                                            <td>{{ $user?->priceFormat($receivableBalance) }}</td>
                                                            <td>{{ $user?->priceFormat($balance) }}</td>
                                                        </tr>
                                                    @empty
                                                        <tr>
                                                            <td colspan="7" class="text-center">
                                                                {{ __('No receivable summaries available') }}
                                                            </td>
                                                        </tr>
                                                    @endforelse

                                                    @if (!empty($receivableSummaries))
                                                        <tr>
                                                            <th>{{ __('Total') }}</th>
                                                            <th></th>
                                                            <th></th>
                                                            <th></th>
                                                            <th></th>
                                                            <th>{{ $user?->priceFormat($totalAmount) }}</th>
                                                            <th>{{ $user?->priceFormat($total) }}</th>
                                                        </tr>
                                                    @endif
                                                </tbody>
                                            </table>
                                        @elseif ($reportName == '#receivable_details')
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
                                                        $receivableDetails = $receivableDetails ?? [];

                                                        $total          = 0;
                                                        $totalQuantity  = 0;

                                                        // Robust sort (handles missing/invalid dates)
                                                        usort($receivableDetails, function ($a, $b) {
                                                            $ad = strtotime($a['issue_date'] ?? '1970-01-01');
                                                            $bd = strtotime($b['issue_date'] ?? '1970-01-01');
                                                            return $bd <=> $ad;
                                                        });
                                                    @endphp

                                                    @forelse ($receivableDetails as $receivableDetail)
                                                        @php
                                                            // Normalize inputs
                                                            $hasInvoiceId       = !empty($receivableDetail['invoice']);
                                                            $isInvoice          = (bool) $hasInvoiceId;
                                                            $price              = floatval($receivableDetail['price']     ?? 0);
                                                            $qty                = intval($receivableDetail['quantity']    ?? 0);

                                                            // Per-row calculations
                                                            $receivableBalance  = $isInvoice ? $price : -$price;
                                                            $quantity           = $isInvoice ? $qty : 0;
                                                            $itemTotal          = $isInvoice ? ($receivableBalance * $quantity) : -$price;

                                                            $total             += $itemTotal;
                                                            $totalQuantity     += $quantity;

                                                            // Status badge mapping
                                                            $status = $receivableDetail['status'] ?? null;
                                                            $statusClasses = [
                                                                0 => 'bg-secondary',
                                                                1 => 'bg-warning',
                                                                2 => 'bg-danger',
                                                                3 => 'bg-info',
                                                                4 => 'bg-primary',
                                                            ];
                                                            $bgClass = ($status !== null && isset($statusClasses[$status])) ? $statusClasses[$status] : null;
                                                        @endphp

                                                        <tr>
                                                            <td>{{ $receivableDetail['name'] ?? __('No customer name available') }}</td>
                                                            <td>{{ $receivableDetail['issue_date'] ?? __('No issue date available') }}</td>

                                                            <td>
                                                                @if ($isInvoice)
                                                                    @if($hasInvoiceId)
                                                                        {{ $user?->invoiceNumberFormat($receivableDetail['invoice']) }}
                                                                    @else
                                                                        {{ __('Could not find invoice number') }}
                                                                    @endif
                                                                @else
                                                                    {{ __('Credit Note') }}
                                                                @endif
                                                            </td>

                                                            <td>
                                                                @if($bgClass)
                                                                    <span class="status_badge {{ VC::BDG }} {{ $bgClass }} p-2 px-3 rounded">
                                                                        {{ __(\App\Models\Invoice::$statuses[$status] ?? __('Unknown status')) }}
                                                                    </span>
                                                                @else
                                                                    <span class="status_badge {{ VC::BDG }} bg-secondary p-2 px-3 rounded">
                                                                        {{ __('No status available') }}
                                                                    </span>
                                                                @endif
                                                            </td>

                                                            <td>{{ $isInvoice ? __('Invoice') : __('Credit Note') }}</td>
                                                            <td>{{ $receivableDetail['product_name'] ?? __('No item name available') }}</td>
                                                            <td>{{ $quantity }}</td>
                                                            <td>{{ $user?->priceFormat($receivableBalance) }}</td>
                                                            <td>{{ $user?->priceFormat($itemTotal) }}</td>
                                                        </tr>
                                                    @empty
                                                        <tr>
                                                            <td colspan="9" class="text-center">
                                                                {{ __('No receivable details available') }}
                                                            </td>
                                                        </tr>
                                                    @endforelse

                                                    @if (!empty($receivableDetails))
                                                        <tr>
                                                            <th>{{ __('Total') }}</th>
                                                            <th></th>
                                                            <th></th>
                                                            <th></th>
                                                            <th></th>
                                                            <th></th>
                                                            <th>{{ $totalQuantity }}</th>
                                                            <th></th>
                                                            <th>{{ $user?->priceFormat($total) }}</th>
                                                        </tr>
                                                    @endif
                                                </tbody>
                                            </table>
                                        @elseif($reportName == '#aging_summary')
                                            <table class="{{ VC::TB }} table-flush" id="report-dataTable">
                                                <thead>
                                                    <tr>
                                                        <th>{{ __('Customer Name') }}</th>
                                                        <th>{{ __('Current') }}</th>
                                                        <th>{{ __('1-15 DAYS') }}</th>
                                                        <th>{{ __('16-30 DAYS') }}</th>
                                                        <th>{{ __('31-45 DAYS') }}</th>
                                                        <th>{{ __('> 45 DAYS') }}</th>
                                                        <th>{{ __('Total') }}</th>
                                                    </tr>
                                                </thead>

                                                <tbody>
                                                    @php
                                                        // Ensure we have a usable collection/array
                                                        $agingSummaries = $agingSummaries ?? [];

                                                        $currentTotal = 0.0;
                                                        $days15       = 0.0;
                                                        $days30       = 0.0;
                                                        $days45       = 0.0;
                                                        $daysMore45   = 0.0;
                                                        $grandTotal   = 0.0;
                                                    @endphp

                                                    @forelse ($agingSummaries as $key => $agingSummary)
                                                        @php
                                                            // Safe value extraction with sensible defaults
                                                            $customerName = $key ?: __('No customer name available');

                                                            $cur      = (float)($agingSummary['current'] ?? 0);
                                                            $d1_15    = (float)($agingSummary['1_15_days'] ?? 0);
                                                            $d16_30   = (float)($agingSummary['16_30_days'] ?? 0);
                                                            $d31_45   = (float)($agingSummary['31_45_days'] ?? 0);
                                                            $gt45     = (float)($agingSummary['greater_than_45_days'] ?? 0);
                                                            $rowTotal = (float)($agingSummary['total_due'] ?? ($cur + $d1_15 + $d16_30 + $d31_45 + $gt45));

                                                            // Accumulate totals
                                                            $currentTotal += $cur;
                                                            $days15       += $d1_15;
                                                            $days30       += $d16_30;
                                                            $days45       += $d31_45;
                                                            $daysMore45   += $gt45;
                                                            $grandTotal   += $rowTotal;
                                                        @endphp

                                                        <tr>
                                                            <td>{{ $customerName }}</td>
                                                            <td>{{ $user?->priceFormat($cur) }}</td>
                                                            <td>{{ $user?->priceFormat($d1_15) }}</td>
                                                            <td>{{ $user?->priceFormat($d16_30) }}</td>
                                                            <td>{{ $user?->priceFormat($d31_45) }}</td>
                                                            <td>{{ $user?->priceFormat($gt45) }}</td>
                                                            <td>{{ $user?->priceFormat($rowTotal) }}</td>
                                                        </tr>
                                                    @empty
                                                        <tr>
                                                            <td colspan="7" class="text-center">
                                                                {{ __('No aging summary data available') }}
                                                            </td>
                                                        </tr>
                                                    @endforelse

                                                    @if (!empty($agingSummaries))
                                                        <tr>
                                                            <th>{{ __('Total') }}</th>
                                                            <th>{{ $user?->priceFormat($currentTotal) }}</th>
                                                            <th>{{ $user?->priceFormat($days15) }}</th>
                                                            <th>{{ $user?->priceFormat($days30) }}</th>
                                                            <th>{{ $user?->priceFormat($days45) }}</th>
                                                            <th>{{ $user?->priceFormat($daysMore45) }}</th>
                                                            <th>{{ $user?->priceFormat($grandTotal) }}</th>
                                                        </tr>
                                                    @endif
                                                </tbody>
                                            </table>
                                        @elseif($reportName == '#aging_details')
                                            @php
                                                // Map invoice statuses to badge colors
                                                $statusClasses = [
                                                    0 => 'bg-secondary',
                                                    1 => 'bg-warning',
                                                    2 => 'bg-danger',
                                                    3 => 'bg-info',
                                                    4 => 'bg-primary',
                                                ];

                                                // Ensure buckets are iterable
                                                $currents    = $currents    ?? [];
                                                $days1to15   = $days1to15   ?? [];
                                                $days16to30  = $days16to30  ?? [];
                                                $days31to45  = $days31to45  ?? [];
                                                $moreThan45  = $moreThan45  ?? [];

                                                // Running totals
                                                $currentTotal = 0;   $currentDue = 0;
                                                $days15Total  = 0;   $days15Due  = 0;
                                                $days30Total  = 0;   $days30Due  = 0;
                                                $days45Total  = 0;   $days45Due  = 0;
                                                $daysMore45Total = 0; $daysMore45Due = 0;

                                                $hasAny = !empty($currents) || !empty($days1to15) || !empty($days16to30) || !empty($days31to45) || !empty($moreThan45);
                                            @endphp
                                            <table class="{{ VC::TB }} table-flush" id="report-dataTable">
                                                <thead>
                                                    <tr>
                                                        <th>{{ __('Date') }}</th>
                                                        <th>{{ __('Transaction') }}</th>
                                                        <th>{{ __('Type') }}</th>
                                                        <th>{{ __('Status') }}</th>
                                                        <th>{{ __('Customer Name') }}</th>
                                                        <th>{{ __('Age') }}</th>
                                                        <th>{{ __('Amount') }}</th>
                                                        <th>{{ __('Balance Due') }}</th>
                                                    </tr>
                                                </thead>

                                                <tbody>
                                                    {{-- > 45 Days --}}
                                                    @if (!empty($moreThan45))
                                                        <tr><th>{{ __('> 45 Days') }}</th></tr>
                                                    @endif
                                                    @foreach ($moreThan45 as $value)
                                                        @php
                                                            $amount   = (float)($value['total_price']  ?? 0);
                                                            $due      = (float)($value['balance_due'] ?? 0);
                                                            $status   = $value['status'] ?? null;
                                                            $bgClass  = $statusClasses[$status] ?? 'bg-secondary';

                                                            $daysMore45Total += $amount;
                                                            $daysMore45Due   += $due;
                                                        @endphp
                                                        <tr>
                                                            <td>{{ $value['due_date'] ?? __('No due date available') }}</td>
                                                            <td>{{ !empty($value['invoice_id']) ? $user?->invoiceNumberFormat($value['invoice_id']) : __('No transaction number available') }}</td>
                                                            <td>{{ __('Invoice') }}</td>
                                                            <td>
                                                                <span class="status_badge {{ VC::BDG }} {{ $bgClass }} p-2 {{ VC::PX3 }} rounded">
                                                                    {{ isset($value['status']) ? __(\App\Models\Invoice::$statuses[$value['status']]) : __('Status not available') }}
                                                                </span>
                                                            </td>
                                                            <td>{{ $value['name'] ?? __('No customer name available') }}</td>
                                                            <td>{{ isset($value['age']) ? ($value['age'].' '.__('Days')) : __('No age available') }}</td>
                                                            <td>{{ $user?->priceFormat($amount) }}</td>
                                                            <td>{{ $user?->priceFormat($due) }}</td>
                                                        </tr>
                                                    @endforeach
                                                    @if (!empty($moreThan45))
                                                        <tr>
                                                            <th colspan="6"></th>
                                                            <th>{{ $user?->priceFormat($daysMore45Total) }}</th>
                                                            <th>{{ $user?->priceFormat($daysMore45Due) }}</th>
                                                        </tr>
                                                    @endif

                                                    {{-- 31 to 45 Days --}}
                                                    @if (!empty($days31to45))
                                                        <tr><th>{{ __('31 to 45 Days') }}</th></tr>
                                                    @endif
                                                    @foreach ($days31to45 as $day31to45)
                                                        @php
                                                            $amount  = (float)($day31to45['total_price']  ?? 0);
                                                            $due     = (float)($day31to45['balance_due'] ?? 0);
                                                            $status  = $day31to45['status'] ?? null;
                                                            $bgClass = $statusClasses[$status] ?? 'bg-secondary';

                                                            $days45Total += $amount;
                                                            $days45Due   += $due;
                                                        @endphp
                                                        <tr>
                                                            <td>{{ $day31to45['due_date'] ?? __('No due date available') }}</td>
                                                            <td>{{ !empty($day31to45['invoice_id']) ? $user?->invoiceNumberFormat($day31to45['invoice_id']) : __('No transaction number available') }}</td>
                                                            <td>{{ __('Invoice') }}</td>
                                                            <td>
                                                                <span class="status_badge {{ VC::BDG }} {{ $bgClass }} p-2 {{ VC::PX3 }} rounded">
                                                                    {{ isset($day31to45['status']) ? __(\App\Models\Invoice::$statuses[$day31to45['status']]) : __('Status not available') }}
                                                                </span>
                                                            </td>
                                                            <td>{{ $day31to45['name'] ?? __('No customer name available') }}</td>
                                                            <td>{{ isset($day31to45['age']) ? ($day31to45['age'].' '.__('Days')) : __('No age available') }}</td>
                                                            <td>{{ $user?->priceFormat($amount) }}</td>
                                                            <td>{{ $user?->priceFormat($due) }}</td>
                                                        </tr>
                                                    @endforeach
                                                    @if (!empty($days31to45))
                                                        <tr>
                                                            <th colspan="6"></th>
                                                            <th>{{ $user?->priceFormat($days45Total) }}</th>
                                                            <th>{{ $user?->priceFormat($days45Due) }}</th>
                                                        </tr>
                                                    @endif

                                                    {{-- 16 to 30 Days --}}
                                                    @if (!empty($days16to30))
                                                        <tr><th>{{ __('16 to 30 Days') }}</th></tr>
                                                    @endif
                                                    @foreach ($days16to30 as $day16to30)
                                                        @php
                                                            $amount  = (float)($day16to30['total_price']  ?? 0);
                                                            $due     = (float)($day16to30['balance_due'] ?? 0);
                                                            $status  = $day16to30['status'] ?? null;
                                                            $bgClass = $statusClasses[$status] ?? 'bg-secondary';

                                                            $days30Total += $amount;
                                                            $days30Due   += $due;
                                                        @endphp
                                                        <tr>
                                                            <td>{{ $day16to30['due_date'] ?? __('No due date available') }}</td>
                                                            <td>{{ !empty($day16to30['invoice_id']) ? $user?->invoiceNumberFormat($day16to30['invoice_id']) : __('No transaction number available') }}</td>
                                                            <td>{{ __('Invoice') }}</td>
                                                            <td>
                                                                <span class="status_badge {{ VC::BDG }} {{ $bgClass }} p-2 {{ VC::PX3 }} rounded">
                                                                    {{ isset($day16to30['status']) ? __(\App\Models\Invoice::$statuses[$day16to30['status']]) : __('Status not available') }}
                                                                </span>
                                                            </td>
                                                            <td>{{ $day16to30['name'] ?? __('No customer name available') }}</td>
                                                            <td>{{ isset($day16to30['age']) ? ($day16to30['age'].' '.__('Days')) : __('No age available') }}</td>
                                                            <td>{{ $user?->priceFormat($amount) }}</td>
                                                            <td>{{ $user?->priceFormat($due) }}</td>
                                                        </tr>
                                                    @endforeach
                                                    @if (!empty($days16to30))
                                                        <tr>
                                                            <th colspan="6"></th>
                                                            <th>{{ $user?->priceFormat($days30Total) }}</th>
                                                            <th>{{ $user?->priceFormat($days30Due) }}</th>
                                                        </tr>
                                                    @endif

                                                    {{-- 1 to 15 Days --}}
                                                    @if (!empty($days1to15))
                                                        <tr><th>{{ __('1 to 15 Days') }}</th></tr>
                                                    @endif
                                                    @foreach ($days1to15 as $day1to15)
                                                        @php
                                                            $amount  = (float)($day1to15['total_price']  ?? 0);
                                                            $due     = (float)($day1to15['balance_due'] ?? 0);
                                                            $status  = $day1to15['status'] ?? null;
                                                            $bgClass = $statusClasses[$status] ?? 'bg-secondary';

                                                            $days15Total += $amount;
                                                            $days15Due   += $due;
                                                        @endphp
                                                        <tr>
                                                            <td>{{ $day1to15['due_date'] ?? __('No due date available') }}</td>
                                                            <td>{{ !empty($day1to15['invoice_id']) ? $user?->invoiceNumberFormat($day1to15['invoice_id']) : __('No transaction number available') }}</td>
                                                            <td>{{ __('Invoice') }}</td>
                                                            <td>
                                                                <span class="status_badge {{ VC::BDG }} {{ $bgClass }} p-2 {{ VC::PX3 }} rounded">
                                                                    {{ isset($day1to15['status']) ? __(\App\Models\Invoice::$statuses[$day1to15['status']]) : __('Status not available') }}
                                                                </span>
                                                            </td>
                                                            <td>{{ $day1to15['name'] ?? __('No customer name available') }}</td>
                                                            <td>{{ isset($day1to15['age']) ? ($day1to15['age'].' '.__('Days')) : __('No age available') }}</td>
                                                            <td>{{ $user?->priceFormat($amount) }}</td>
                                                            <td>{{ $user?->priceFormat($due) }}</td>
                                                        </tr>
                                                    @endforeach
                                                    @if (!empty($days1to15))
                                                        <tr>
                                                            <th colspan="6"></th>
                                                            <th>{{ $user?->priceFormat($days15Total) }}</th>
                                                            <th>{{ $user?->priceFormat($days15Due) }}</th>
                                                        </tr>
                                                    @endif

                                                    {{-- Current --}}
                                                    @if (!empty($currents))
                                                        <tr><th>{{ __('Current') }}</th></tr>
                                                    @endif
                                                    @foreach ($currents as $current)
                                                        @php
                                                            $amount  = (float)($current['total_price']  ?? 0);
                                                            $due     = (float)($current['balance_due'] ?? 0);
                                                            $status  = $current['status'] ?? null;
                                                            $bgClass = $statusClasses[$status] ?? 'bg-secondary';

                                                            $currentTotal += $amount;
                                                            $currentDue   += $due;
                                                        @endphp
                                                        <tr>
                                                            <td>{{ $current['due_date'] ?? __('No due date available') }}</td>
                                                            <td>{{ !empty($current['invoice_id']) ? $user?->invoiceNumberFormat($current['invoice_id']) : __('No transaction number available') }}</td>
                                                            <td>{{ __('Invoice') }}</td>
                                                            <td>
                                                                <span class="status_badge {{ VC::BDG }} {{ $bgClass }} p-2 {{ VC::PX3 }} rounded">
                                                                    {{ isset($current['status']) ? __(\App\Models\Invoice::$statuses[$current['status']]) : __('Status not available') }}
                                                                </span>
                                                            </td>
                                                            <td>{{ $current['name'] ?? __('No customer name available') }}</td>
                                                            <td>{{ __('Current') }}</td>
                                                            <td>{{ $user?->priceFormat($amount) }}</td>
                                                            <td>{{ $user?->priceFormat($due) }}</td>
                                                        </tr>
                                                    @endforeach
                                                    @if (!empty($currents))
                                                        <tr>
                                                            <th colspan="6"></th>
                                                            <th>{{ $user?->priceFormat($currentTotal) }}</th>
                                                            <th>{{ $user?->priceFormat($currentDue) }}</th>
                                                        </tr>
                                                    @endif

                                                    {{-- Empty state --}}
                                                    @unless ($hasAny)
                                                        <tr>
                                                            <td colspan="8" class="text-center">
                                                                {{ __('No aging detail records available') }}
                                                            </td>
                                                        </tr>
                                                    @endunless

                                                    {{-- Grand Total --}}
                                                    @if ($hasAny)
                                                        <tr>
                                                            <th>{{ __('Total') }}</th>
                                                            <th colspan="5"></th>
                                                            <th>{{ $user?->priceFormat($currentTotal + $days15Total + $days30Total + $days45Total + $daysMore45Total) }}</th>
                                                            <th>{{ $user?->priceFormat($currentDue + $days15Due + $days30Due + $days45Due + $daysMore45Due) }}</th>
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
        <script src="{{ asset('js/jquery.min.js') }}"></script>
        <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js"></script>
        <script type="text/javascript" src="{{ asset('js/html2pdf.bundle.min.js') }}"></script>
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script async src="{{ asset('assets/js/routes/reports/receivables/receipts/lang/pdf.js') }}"></script>
        <script defer src="{{ asset('assets/js/routes/reports/receivables/receipts/lang/pdf.js') }}"></script>
    </body>
</html>
