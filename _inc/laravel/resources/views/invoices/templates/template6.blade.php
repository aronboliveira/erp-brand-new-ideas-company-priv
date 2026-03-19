@php
        try {
$settings_data = \App\Models\Utility::settingsById($invoice[DatabaseConstants::COL_TABLE_CREATOR]);

            $hasInvoiceNumberFormat = is_callable([UtilModel::class, 'invoiceNumberFormat']);
                $hasDateFormat          = is_callable([UtilModel::class, 'dateFormat']);
                $hasPriceFormat         = is_callable([UtilModel::class, 'priceFormat']);

                $hasGetSubTotal              = method_exists($invoice, 'getSubTotal');
                $hasGetTotalDiscount         = method_exists($invoice, 'getTotalDiscount');
                $hasGetTotalTax              = method_exists($invoice, 'getTotalTax');
                $hasInvoiceTotalCreditNote   = method_exists($invoice, 'invoiceTotalCreditNote');
                $hasGetTotal                 = method_exists($invoice, 'getTotal');
                $hasGetDue                   = method_exists($invoice, 'getDue');

            $fmtInvoiceNo = function ($settings, $id) use ($hasInvoiceNumberFormat) {
            return $hasInvoiceNumberFormat ? \App\Models\Utility::invoiceNumberFormat($settings, $id) : (string) $id;
                };
                $fmtDate = function ($settings, $date) use ($hasDateFormat) {
            if ($hasDateFormat) return \App\Models\Utility::dateFormat($settings, $date);
            if ($date instanceof \DateTimeInterface) return $date->format('Y-m-d');
            return is_string($date) ? $date : (string) $date;
                };
                $fmtPrice = function ($settings, $amount) use ($hasPriceFormat) {
            return $hasPriceFormat ? \App\Models\Utility::priceFormat($settings, $amount) : number_format((float) $amount, 2);
                };

            $subTotal   = $hasGetSubTotal            ? $invoice->getSubTotal()            : ($invoice->totalRate ?? 0);
                $totalDisc  = $hasGetTotalDiscount       ? $invoice->getTotalDiscount()       : ($invoice->totalDiscount ?? 0);
                $totalTax   = $hasGetTotalTax            ? $invoice->getTotalTax()            : ($invoice->totalTaxPrice ?? 0);
                $creditNote = $hasInvoiceTotalCreditNote ? $invoice->invoiceTotalCreditNote() : 0;
                $total      = $hasGetTotal               ? $invoice->getTotal()               : ($subTotal - $totalDisc + $totalTax);
                $due        = $hasGetDue                 ? $invoice->getDue()                 : max(0, $total - (($invoice->payments_total ?? 0) - $creditNote));
        } catch (\Throwable $e) {
            \Log::error('invoices/templates/template6 — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
        }
@endphp
<!DOCTYPE html>
<html lang="{{ $lang ? (str_replace('_','-',is_string(app()->getLocale()) ? app()->getLocale() : DatabaseConstants::DEFAULT_LANG)) : DatabaseConstants::DEFAULT_LANG }}" dir="{{ ($siteRtl ?? '') === 'on' ? 'rtl' : '' }}">

<head>
    @include('fragments.std', [
        'meta_title' => $meta_title,
        'meta_desc'  => $meta_desc,
    ])
    <title>{{ __('New York') }} - {{ __('Invoice') }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Lato:ital,wght@0,100;0,300;0,400;0,700;0,900;1,100;1,300;1,400;1,700;1,900&display=swap" rel="stylesheet">

    <style>
        <?php echo $themeCSS; ?>
    </style>
    <link rel="stylesheet" href="{{ asset('assets/css/routes/invoices/theme2.css') }}" />
    @if(($settings_data[SettingsConstants::RTL] ?? '') == 'on')
        <link rel="stylesheet" href="{{ asset('css/bootstrap-rtl.css') }}">
    @endif
</head>

<body>
    <div class="invoice-preview-main" id="boxes">
        <div class="invoice-header" style="border-top: 15px solid <?= $color ?>">
            <table>
                <tbody>
                    <tr>
                        <td>
                            <h3 style="text-transform: uppercase; font-size: 40px; font-weight: bold;">{{ __('INVOICE') }}</h3>
                        </td>
                        <td class="{{ VC::TX_RT }}">
                            <img class="invoice-logo" src="{{ $img }}" alt="">
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="invoice-body">
            <table class="{{ VC::VA_TOP }}">
                <tbody>
                    <tr>
                        @php
                            try {
                                $company_city   = $settings['company_city']   ?? '';
                                $company_state  = $settings['company_state']  ?? '';
                                $company_zip    = $settings['company_zipcode']?? '';
                            } catch (\Throwable $e) {
                                \Log::error('invoices/templates/template6 — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                            }
@endphp

                        @if (!empty($settings['company_name']) || !empty($settings['mail_from_address']) || !empty($settings['company_address']))
                        <td style="font-size: 13px;">
                            <strong style="margin-bottom: 10px; display:block;">{{ __('From:') }}</strong>
                            <p>
                                {{ $settings['company_name']      ?? __('No company name available') }}<br>
                                {{ $settings['mail_from_address'] ?? __('No company email available') }}<br><br>
                                {{ $settings['company_address']   ?? __('No company address available') }}<br>
                                {{ $company_city !== '' ? $company_city : __('No city') }}
                                {{ $company_city !== '' ? ',' : '' }}
                                {{ $company_state !== '' ? $company_state : '' }}
                                {{ $company_zip !== '' ? ' - ' . $company_zip : '' }}<br>
                                {{ $settings['company_country']   ?? __('No country') }}<br>
                                {{ $settings['company_telephone'] ?? __('No company phone available') }}<br>
                            </p>
                        </td>
                        @endif

                        <td style="font-size: 13px;">
                            <strong style="margin-bottom: 10px; display:block;">{{ __('Bill To:') }}</strong>
                            <p>
                                {{ $customer->billing_name    ?? __('No billing name available for customer') }}<br>
                                {{ $customer->billing_address ?? __('No billing address available for customer') }}<br>
                                {{ $customer->billing_city    ?? __('No billing city available for customer') }}{{ ($customer->billing_city ?? '') !== '' ? ',' : '' }}<br>
                                {{ $customer->billing_state   ?? __('No billing state available for customer') }}{{ ($customer->billing_state ?? '') !== '' ? ',' : '' }}
                                {{ $customer->billing_zip     ?? __('No billing zip available for customer') }}<br>
                                {{ $customer->billing_country ?? __('No billing country available for customer') }}<br>
                                {{ $customer->billing_phone   ?? __('No billing phone available for customer') }}<br>
                            </p>
                        </td>

                        @if(($settings['shipping_display'] ?? '') == 'on')
                        <td style="font-size: 13px;" class="{{ VC::TX_RT }}">
                            <strong style="margin-bottom: 10px; display:block;">{{ __('Ship To:') }}</strong>
                            <p>
                                {{ $customer->shipping_name    ?? __('No shipping name available for customer') }}<br>
                                {{ $customer->shipping_address ?? __('No shipping address available for customer') }}<br>
                                {{ $customer->shipping_city    ?? __('No shipping city available for customer') }}{{ ($customer->shipping_city ?? '') !== '' ? ',' : '' }}<br>
                                {{ $customer->shipping_state   ?? __('No shipping state available for customer') }}{{ ($customer->shipping_state ?? '') !== '' ? ',' : '' }}
                                {{ $customer->shipping_zip     ?? __('No shipping zip available for customer') }}<br>
                                {{ $customer->shipping_country ?? __('No shipping country available for customer') }}<br>
                                {{ $customer->shipping_phone   ?? __('No shipping phone available for customer') }}<br>
                            </p>
                        </td>
                        @endif
                    </tr>

                    <tr style="border-bottom:1px solid <?= $color ?>">
                        <td>
                            <p>
                                {{ !empty($settings['registration_number']) ? __('Registration Number') . ' : ' . $settings['registration_number'] : __('No registration number provided') }}<br>
                                @if (!empty($settings['tax_type']) && !empty($settings['vat_number']))
                                    {{ $settings['tax_type'] . ' ' . __('Number') }} : {{ $settings['vat_number'] }} <br>
                                @else
                                    {{ __('No tax information provided') }}<br>
                                @endif
                            </p>
                        </td>
                        <td colspan="2">
                            <div class="{{ VC::VW_QR }}" style="margin-top: 0;">
                                {!! DNS2D::getBarcodeHTML(route(ViewsConstants::INV.'.link.copy', \Crypt::encrypt($invoice->invoice_id)), 'QRCODE', 2, 2) !!}
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>

            <table>
                <tbody>
                    <tr>
                        <td>
                            <table class="{{ VC::NO_SPC }}">
                                <tbody>
                                    <tr>
                                        <td>{{ __('Number') }}:</td>
                                        <td class="{{ VC::TX_RT }}">{{ $fmtInvoiceNo($settings, $invoice->invoice_id) }}</td>
                                    </tr>
                                    <tr>
                                        <td>{{ __('Issue Date') }}:</td>
                                        <td class="{{ VC::TX_RT }}">{{ $fmtDate($settings, $invoice->issue_date) }}</td>
                                    </tr>
                                    <tr>
                                        <td><b>{{ __('Due Date:') }}</b></td>
                                        <td class="{{ VC::TX_RT }}">{{ $fmtDate($settings, $invoice->due_date) }}</td>
                                    </tr>

                                    @if(!empty($customFields) && !empty($invoice->customField) && count($invoice->customField) > 0)
                                        @foreach($customFields as $field)
                                            <tr>
                                                <td>{{ $field->name }} :</td>
                                                <td>{{ !empty($invoice->customField) && isset($invoice->customField[$field->id]) ? $invoice->customField[$field->id] : '-' }}</td>
                                            </tr>
                                        @endforeach
                                    @endif
                                </tbody>
                            </table>
                        </td>
                    </tr>
                </tbody>
            </table>

            <table class="{{ VC::BDR_INV_SM }}" style="margin-top: 30px;">
                <thead style="background: <?= $color ?>; color: {{ $font_color }}">
                    <tr style="border-bottom:1px solid <?= $color ?>">
                        <th>{{ __('Item') }}</th>
                        <th>{{ __('Quantity') }}</th>
                        <th>{{ __('Rate') }}</th>
                        <th>{{ __('Discount') }}</th>
                        <th>{{ __('Tax') }} (%)</th>
                        <th>{{ __('Price') }} <small>{{ __('after tax & discount') }}</small></th>
                    </tr>
                </thead>
                <tbody style="border-bottom:1px solid <?= $color ?>">
                    @if(isset($invoice->itemData) && count($invoice->itemData) > 0)
                        @foreach($invoice->itemData as $key => $item)
                            @php
                                try {
                                    $unitName = class_exists(\App\Models\ProductServiceUnit::class)
                                        ? \App\Models\ProductServiceUnit::find($item->unit)
                                        : null;
                                    $unitLabel = $unitName->name ?? __('unit');
                                    $itemtax   = 0;
                                } catch (\Throwable $e) {
                                    \Log::error('invoices/templates/template6 — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                }
@endphp
                            <tr>
                                <td>{{ $item->name ?? __('Unnamed item') }}</td>
                                <td>{{ ($item->quantity ?? 0) . ' (' . $unitLabel . ')' }}</td>
                                <td>{{ $fmtPrice($settings, $item->price ?? 0) }}</td>
                                <td>{{ (!empty($item->discount) && $item->discount != 0) ? $fmtPrice($settings, $item->discount) : '-' }}</td>
                                <td>
                                    @if(!empty($item->itemTax))
                                        @foreach($item->itemTax as $taxes)
                                            @php
 $itemtax += $taxes['tax_price'] ?? 0;
@endphp
                                            <p>
                                                {{ ($taxes['name'] ?? __('Tax')) }}
                                                ({{ $taxes['rate'] ?? 0 }})
                                                {{ $fmtPrice($settings, $taxes['price'] ?? 0) }}
                                            </p>
                                        @endforeach
                                    @else
                                        <span>-</span>
                                    @endif
                                </td>
                                <td>{{ $fmtPrice($settings, (($item->price ?? 0) * ($item->quantity ?? 0)) - ($item->discount ?? 0) + $itemtax) }}</td>
                            </tr>
                            @if(!empty($item->description))
                                <tr class="{{ VC::BD0_ITM_DSC }}">
                                    <td colspan="6" style="border-bottom:1px solid <?= $color ?>">{{ $item->description }}</td>
                                </tr>
                            @endif
                        @endforeach
                    @endif
                </tbody>

                <tfoot>
                    <tr style="border-bottom:1px solid <?= $color ?>">
                        <td>{{ __('Total') }}</td>
                        <td>{{ $invoice->totalQuantity ?? 0 }}</td>
                        <td>{{ $fmtPrice($settings, $invoice->totalRate ?? 0) }}</td>
                        <td>{{ $fmtPrice($settings, $invoice->totalDiscount ?? 0) }}</td>
                        <td>{{ $fmtPrice($settings, $invoice->totalTaxPrice ?? 0) }}</td>
                        <td>{{ $fmtPrice($settings, $subTotal) }}</td>
                    </tr>
                    <tr>
                        <td colspan="4"></td>
                        <td colspan="2" class="{{ VC::SUB_TTL }}">
                            <table class="{{ VC::TTL_TB }}">
                                <tr style="border-bottom:1px solid <?= $color ?>">
                                    <td>{{ __('Subtotal') }}:</td>
                                    <td>{{ $fmtPrice($settings, $subTotal) }}</td>
                                </tr>

                                @if(($totalDisc ?? 0) > 0)
                                    <tr style="border-bottom:1px solid <?= $color ?>">
                                        <td>{{ __('Discount') }}:</td>
                                        <td>{{ $fmtPrice($settings, $totalDisc) }}</td>
                                    </tr>
                                @endif

                                @if(!empty($invoice->taxesData))
                                    @foreach($invoice->taxesData as $taxName => $taxPrice)
                                        <tr style="border-bottom:1px solid <?= $color ?>">
                                            <td>{{ $taxName }} :</td>
                                            <td>{{ $fmtPrice($settings, $taxPrice) }}</td>
                                        </tr>
                                    @endforeach
                                @endif

                                <tr style="border-bottom:1px solid <?= $color ?>">
                                    <td>{{ __('Total') }}:</td>
                                    <td>{{ $fmtPrice($settings, $subTotal - $totalDisc + $totalTax) }}</td>
                                </tr>
                                <tr style="border-bottom:1px solid <?= $color ?>">
                                    <td>{{ __('Paid') }}:</td>
                                    <td>{{ $fmtPrice($settings, ($total - $due) - $creditNote) }}</td>
                                </tr>
                                <tr style="border-bottom:1px solid <?= $color ?>">
                                    <td>{{ __('Credit Note') }}:</td>
                                    <td>{{ $fmtPrice($settings, $creditNote) }}</td>
                                </tr>
                                <tr style="border-bottom:1px solid <?= $color ?>">
                                    <td>{{ __('Due Amount') }}:</td>
                                    <td>{{ $fmtPrice($settings, $due) }}</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </tfoot>
            </table>

            <div class="invoice-footer">
                <b>{{ $settings['footer_title'] ?? '' }}</b> <br>
                {!! $settings['footer_notes'] ?? '' !!}
            </div>
        </div>
    </div>

    @if(!isset($preview))
        @include(ViewsConstants::INV.'.script')
    @endif
</body>
</html>
