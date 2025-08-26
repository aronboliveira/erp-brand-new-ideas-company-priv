{{-- @extends(ExtendingLayoutsConstants::ADM) --}}
@php
    use App\Config\Constants\{
        DatabaseConstants,
        ExtendingLayoutsConstants,
        SettingsConstants,
        StacksConstants,
        ViewsConstants as VW,
        ViewClassNamesConstants as VC,
        YieldingConstants,
    };
    use App\Models\Utility;
    use Collective\Html\FormFacade as Form;
    use Illuminate\Support\Facades\{Auth, Route};
    use Illuminate\Support\Str;
    $user = Auth::user();
    $lang = Utility::fetchUserLang(user: $user);
    $authUser = $creatorUser?->creatorId() ?? null;
    $creatorUser = User::find($authUser);
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
                <div class="col-md-8">
                    <div class="{{ VC::CD }}">
                        <div class="card-body">
                            @if ($reportName === '#item')
                                <div class="account-main-title mb-5">
                                    <h5>
                                        {{ __('Sales By Item of :name as of :start to :end', [
                                            'name'  => $creatorUser?->name,
                                            'start' => $filter['startDateRange'],
                                            'end'   => $filter['endDateRange'],
                                        ]) }}
                                    </h5>

                                    @php $totQty = 0; $totAmt = 0.0; @endphp
                                    <table class="{{ VC::TB }} table-flush {{ VC::MT3 }}" id="report-items-table">
                                        <thead>
                                            <tr>
                                                <th width="33%">{{ __('Invoice Item') }}</th>
                                                <th width="33%" class="text-end">{{ __('Quantity Sold') }}</th>
                                                <th width="33%" class="text-end">{{ __('Amount') }}</th>
                                                <th class="text-end">{{ __('Average Price') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($invoiceItems as $row)
                                                @php
                                                    $qty   = (float) ($row['quantity'] ?? 0);
                                                    $amt   = (float) ($row['price'] ?? 0);
                                                    $avg   = (float) ($row['avg_price'] ?? ($qty > 0 ? $amt / $qty : 0));
                                                    $totQty += $qty;
                                                    $totAmt += $amt;
                                                @endphp
                                                <tr>
                                                    <td>{{ $row['name'] }}</td>
                                                    <td class="text-end">{{ $qty }}</td>
                                                    <td class="text-end">{{ $user?->priceFormat($amt) }}</td>
                                                    <td class="text-end">{{ $user?->priceFormat($avg) }}</td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="4" class="text-center text-muted">{{ __('No data found') }}</td>
                                                </tr>
                                            @endforelse
                                        </tbody>

                                        @if (!empty($invoiceItems))
                                            <tfoot>
                                                <tr>
                                                    <th>{{ __('Total') }}</th>
                                                    <th class="text-end">{{ $totQty }}</th>
                                                    <th class="text-end">{{ $user?->priceFormat($totAmt) }}</th>
                                                    <th class="text-end">
                                                        {{ $user?->priceFormat($totQty > 0 ? ($totAmt / $totQty) : 0) }}
                                                    </th>
                                                </tr>
                                            </tfoot>
                                        @endif
                                    </table>
                                </div>
                            @else
                                <div class="account-main-title mb-5">
                                    <h5>
                                        {{ __('Sales By Customer of :name as of :start to :end', [
                                            'name'  => $creatorUser?->name,
                                            'start' => $filter['startDateRange'],
                                            'end'   => $filter['endDateRange'],
                                        ]) }}
                                    </h5>

                                    @php $totCount = 0; $totSales = 0.0; $totSalesWithTax = 0.0; @endphp
                                    <table class="{{ VC::TB }} table-flush {{ VC::MT3 }}" id="report-customers-table">
                                        <thead>
                                            <tr>
                                                <th width="33%">{{ __('Customer Name') }}</th>
                                                <th width="33%" class="text-end">{{ __('Invoice Count') }}</th>
                                                <th width="33%" class="text-end">{{ __('Sales') }}</th>
                                                <th class="text-end">{{ __('Sales With Tax') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($invoiceCustomers as $row)
                                                @php
                                                    $count = (int) ($row['invoice_count'] ?? 0);
                                                    $amt   = (float) ($row['price'] ?? 0);
                                                    $tax   = (float) ($row['total_tax'] ?? 0);
                                                    $totCount        += $count;
                                                    $totSales        += $amt;
                                                    $totSalesWithTax += ($amt + $tax);
                                                @endphp
                                                <tr>
                                                    <td>{{ $row['name'] }}</td>
                                                    <td class="text-end">{{ $count }}</td>
                                                    <td class="text-end">{{ $user?->priceFormat($amt) }}</td>
                                                    <td class="text-end">{{ $user?->priceFormat($amt + $tax) }}</td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="4" class="text-center text-muted">{{ __('No data found') }}</td>
                                                </tr>
                                            @endforelse
                                        </tbody>

                                        @if (!empty($invoiceCustomers))
                                            <tfoot>
                                                <tr>
                                                    <th>{{ __('Total') }}</th>
                                                    <th class="text-end">{{ $totCount }}</th>
                                                    <th class="text-end">{{ $user?->priceFormat($totSales) }}</th>
                                                    <th class="text-end">{{ $user?->priceFormat($totSalesWithTax) }}</th>
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
