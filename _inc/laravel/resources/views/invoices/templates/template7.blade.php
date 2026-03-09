@php
        try {
$settings_data = \App\Models\Utility::settingsById($invoice[DatabaseConstants::COL_TABLE_CREATOR] ?? null);
                $hasInvoiceNumberFormat = is_callable([UtilModel::class, 'invoiceNumberFormat']);
                $hasDateFormat          = is_callable([UtilModel::class, 'dateFormat']);
                $hasPriceFormat         = is_callable([UtilModel::class, 'priceFormat']);
                $hasGetSubTotal            = is_object($invoice ?? null) && method_exists($invoice, 'getSubTotal');
                $hasGetTotalDiscount       = is_object($invoice ?? null) && method_exists($invoice, 'getTotalDiscount');
                $hasGetTotalTax            = is_object($invoice ?? null) && method_exists($invoice, 'getTotalTax');
                $hasInvoiceTotalCreditNote = is_object($invoice ?? null) && method_exists($invoice, 'invoiceTotalCreditNote');
                $hasGetTotal               = is_object($invoice ?? null) && method_exists($invoice, 'getTotal');
                $hasGetDue                 = is_object($invoice ?? null) && method_exists($invoice, 'getDue');
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
                $calcTotalQty = 0; $calcTotalRate = 0; $calcTotalDiscount = 0; $calcTotalTax = 0;
                if (!empty($invoice->itemData) && is_iterable($invoice->itemData)) {
            foreach ($invoice->itemData as $it) {
                $calcTotalQty      += (float)($it->quantity ?? 0);
                $calcTotalRate     += (float)($it->price ?? 0);
                $calcTotalDiscount += (float)($it->discount ?? 0);
                if (!empty($it->itemTax) && is_iterable($it->itemTax)) {
                    foreach ($it->itemTax as $tx) { $calcTotalTax += (float)($tx['tax_price'] ?? 0); }
                }
            }
                }
                $displayTotalQuantity  = $invoice->totalQuantity  ?? $calcTotalQty;
                $displayTotalRate      = $invoice->totalRate      ?? $calcTotalRate;
                $displayTotalDiscount  = $invoice->totalDiscount  ?? $calcTotalDiscount;
                $displayTotalTaxPrice  = $invoice->totalTaxPrice  ?? $calcTotalTax;
                $subTotal   = $hasGetSubTotal            ? $invoice->getSubTotal()            : ($invoice->totalRate ?? ($calcTotalRate));
                $totalDisc  = $hasGetTotalDiscount       ? $invoice->getTotalDiscount()       : ($invoice->totalDiscount ?? ($calcTotalDiscount));
                $totalTax   = $hasGetTotalTax            ? $invoice->getTotalTax()            : ($invoice->totalTaxPrice ?? ($calcTotalTax));
                $creditNote = $hasInvoiceTotalCreditNote ? $invoice->invoiceTotalCreditNote() : 0;
                $total      = $hasGetTotal               ? $invoice->getTotal()               : ($subTotal - $totalDisc + $totalTax);
                $due        = $hasGetDue                 ? $invoice->getDue()                 : max(0, $total - (($invoice->payments_total ?? 0) - $creditNote));
                $paidAmount = max(0, ($total - $due) - $creditNote);
                $company_city  = $settings['company_city']    ?? '';
                $company_state = $settings['company_state']   ?? '';
                $company_zip   = $settings['company_zipcode'] ?? '';
        } catch (\Throwable $e) {
            \Log::error('invoices/templates/template7 — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
        }
@endphp
<!DOCTYPE html>
<html lang="{{ $lang ? (str_replace('_','-',is_string(app()->getLocale()) ? app()->getLocale() : DatabaseConstants::DEFAULT_LANG)) : DatabaseConstants::DEFAULT_LANG }}" dir="{{ ($siteRtl ?? '') === 'on' ? 'rtl' : '' }}">

<head>
    @include('fragments.std', [
    'meta_title' => $meta_title,
    'meta_desc'  => $meta_desc,
    ])
    <link href="https://fonts.googleapis.com/css2?family=Lato:ital,wght@0,100;0,300;0,400;0,700;0,900;1,100;1,300;1,400;1,700;1,900&display=swap" rel="stylesheet">

    <style>
        <?php echo $themeCSS; ?>
    </style>
    <link rel="stylesheet" href="{{ asset('assets/css/routes/invoices/theme6.css') }}" />

    @if(($settings_data[SettingsConstants::RTL] ?? '')=='on')
    <link rel="stylesheet" href="{{ asset('css/bootstrap-rtl.css') }}">
    @endif
</head>

<body>
    <div class="invoice-preview-main" id="boxes">
        <div class="invoice-header" style="border-top: 15px solid var(--theme-color); background: #f8f8f8;">
            <table>
                <tbody>
                    <tr>
                        <td>
                            <img class="invoice-logo" src="{{ $img }}" alt="">
                        </td>
                        <td class="{{ VC::TX_RT }}">
                            <h3 style="text-transform: uppercase; font-size: 40px; font-weight: bold;">{{ __('INVOICE') }}</h3>
                        </td>
                    </tr>
                </tbody>
            </table>
            <table class="{{ VC::VA_TOP }}">
                <tbody>
                    <tr>
                        <td>
                            <p>
                                {{ $settings['company_name']      ?? __('No company name available') }}<br>
                                {{ $settings['mail_from_address'] ?? __('No company email available') }}<br><br>
                                {{ $settings['company_address']   ?? __('No company address available') }}<br>
                                {{ $company_city !== '' ? $company_city : __('No city') }}
                                {{ $company_city !== '' ? ',' : '' }}
                                {{ $company_state }}
                                {{ $company_zip !== '' ? ' - '.$company_zip : '' }}<br>
                                {{ $settings['company_country']   ?? __('No country') }}<br>
                                {{ $settings['company_telephone'] ?? __('No company phone available') }}<br>
                                @if(!empty($settings['registration_number']))
                                    {{ __('Registration Number') }} : {{ $settings['registration_number'] }}
                                @endif
                                @if(($settings['vat_gst_number_switch'] ?? '') === 'on')
                                    @if(!empty($settings['tax_type']) && !empty($settings['vat_number']))
                                        <br>{{ $settings['tax_type'].' '. __('Number') }} : {{ $settings['vat_number'] }}
                                    @endif
                                @endif
                            </p>
                        </td>
                        <td>
                            <table class="{{ VC::NO_SPC }}" style="width: 45%;margin-left: auto;">
                                <tbody>
                                    <tr>
                                        <td>{{ __('Number') }}:</td>
                                        <td class="{{ VC::TX_RT }}">{{ $fmtInvoiceNo($settings, $invoice->invoice_id ?? '') }}</td>
                                    </tr>
                                    <tr>
                                        <td>{{ __('Issue Date') }}:</td>
                                        <td class="{{ VC::TX_RT }}">{{ $fmtDate($settings, $invoice->issue_date ?? '') }}</td>
                                    </tr>
                                    <tr>
                                        <td><b>{{ __('Due Date:') }}</b></td>
                                        <td class="{{ VC::TX_RT }}">{{ $fmtDate($settings, $invoice->due_date ?? '') }}</td>
                                    </tr>
                                    @if(!empty($customFields) && !empty($invoice->customField) && count($invoice->customField)>0)
                                        @foreach($customFields as $field)
                                            <tr>
                                                <td>{{ $field->name }} :</td>
                                                <td>{{ $invoice->customField[$field->id] ?? '-' }}</td>
                                            </tr>
                                        @endforeach
                                    @endif
                                    <tr>
                                        <td colspan="2">
                                            <div class="{{ VC::VW_QR }}">
                                                {!! DNS2D::getBarcodeHTML(route(ViewsConstants::INV.'.link.copy', \Crypt::encrypt($invoice->invoice_id ?? '')), "QRCODE",2,2) !!}
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </td>
                    </tr>
                </tbody>
            </table>

        </div>
        <div class="invoice-body" style="border-bottom: 15px solid var(--theme-color);">
            <table class="{{ VC::VA_TOP }}">
                <tbody>
                    <tr>
                        <td>
                            <strong style="margin-bottom: 10px; display:block;">{{ __('Bill To') }}:</strong>
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
                        @if(($settings['shipping_display'] ?? '')=='on')
                        <td class="{{ VC::TX_RT }}">
                            <strong style="margin-bottom: 10px; display:block;">{{ __('Ship To') }}:</strong>
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

                </tbody>
            </table>

            <table class="{{ VC::BDR_INV_SM }}" style="margin-top: 30px;">
                <thead style="background: <?= $color ?>; color: {{ $font_color ?? '#000' }}">
                    <tr>
                        <th>{{ __('Item') }}</th>
                        <th>{{ __('Quantity') }}</th>
                        <th>{{ __('Rate') }}</th>
                        <th>{{ __('Discount') }}</th>
                        <th>{{ __('Tax') }} (%)</th>
                        <th>{{ __('Price') }} <small>{{ __('after tax & discount') }}</small></th>
                    </tr>
                </thead>
                <tbody>
                    @if(!empty($invoice->itemData) && count($invoice->itemData) > 0)
                        @foreach($invoice->itemData as $key => $item)
                            @php
                                try {
                                    $unitModel = \App\Models\ProductServiceUnit::find($item->unit ?? null);
                                    $unitName  = $unitModel->name ?? __('unit');
                                    $qty       = (float)($item->quantity ?? 0);
                                    $rate      = (float)($item->price ?? 0);
                                    $disc      = (float)($item->discount ?? 0);
                                    $itemtax   = 0.0;
                                    $taxLines  = [];
                                    if (!empty($item->itemTax) && is_iterable($item->itemTax)) {
                                        foreach ($item->itemTax as $taxes) {
                                            $itemtax += (float)($taxes['tax_price'] ?? 0);
                                            $taxLines[] = [
                                                'name' => $taxes['name'] ?? __('Tax'),
                                                'rate' => $taxes['rate'] ?? 0,
                                                'disp' => $taxes['price'] ?? $fmtPrice($settings, $taxes['tax_price'] ?? 0),
                                            ];
                                        }
                                    }
                                    $lineTotal = ($rate * $qty) - $disc + $itemtax;
                                } catch (\Throwable $e) {
                                    \Log::error('invoices/templates/template7 — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                }
@endphp
                            <tr>
                                <td>{{ $item->name ?? __('Item') }}</td>
                                <td>{{ $qty.' ('.$unitName.')' }}</td>
                                <td>{{ $fmtPrice($settings, $rate) }}</td>
                                <td>{{ $disc != 0 ? $fmtPrice($settings, $disc) : '-' }}</td>
                                <td>
                                    @if(count($taxLines))
                                        @foreach($taxLines as $tx)
                                            <p>{{ $tx['name'] }} ({{ $tx['rate'] }}) {{ $tx['disp'] }}</p>
                                        @endforeach
                                    @else
                                        <span>-</span>
                                    @endif
                                </td>
                                <td>{{ $fmtPrice($settings, $lineTotal) }}</td>
                            </tr>
                            @if(!empty($item->description))
                                <tr class="{{ VC::BD0_ITM_DSC }}">
                                    <td colspan="6" style="border-bottom:1px solid <?= $color ?>"> {{ $item->description }}</td>
                                </tr>
                            @endif
                        @endforeach
                    @endif
                </tbody>
                <tfoot>
                    <tr>
                        <td>{{ __('Total') }}</td>
                        <td>{{ $displayTotalQuantity }}</td>
                        <td>{{ $fmtPrice($settings, $displayTotalRate) }}</td>
                        <td>{{ $fmtPrice($settings, $displayTotalDiscount) }}</td>
                        <td>{{ $fmtPrice($settings, $displayTotalTaxPrice) }}</td>
                        <td>{{ $fmtPrice($settings, $subTotal) }}</td>
                    </tr>
                    <tr>
                        <td colspan="4"></td>
                        <td colspan="2" class="{{ VC::SUB_TTL }}">
                            <table class="{{ VC::TTL_TB }}">
                                <tr>
                                    <td>{{ __('Subtotal') }}:</td>
                                    <td>{{ $fmtPrice($settings, $subTotal) }}</td>
                                </tr>
                                <tr>
                                    <td>{{ __('Discount') }}:</td>
                                    <td>{{ $fmtPrice($settings, $totalDisc) }}</td>
                                </tr>
                                @if(!empty($invoice->taxesData) && is_iterable($invoice->taxesData))
                                    @foreach($invoice->taxesData as $taxName => $taxPrice)
                                        <tr>
                                            <td>{{ $taxName }} :</td>
                                            <td>{{ $fmtPrice($settings, $taxPrice) }}</td>
                                        </tr>
                                    @endforeach
                                @endif
                                <tr>
                                    <td>{{ __('Total') }}:</td>
                                    <td>{{ $fmtPrice($settings, $subTotal - $totalDisc + $totalTax) }}</td>
                                </tr>
                                <tr>
                                    <td>{{ __('Paid') }}:</td>
                                    <td>{{ $fmtPrice($settings, $paidAmount) }}</td>
                                </tr>
                                <tr>
                                    <td>{{ __('Credit Note') }}:</td>
                                    <td>{{ $fmtPrice($settings, $creditNote) }}</td>
                                </tr>
                                <tr>
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
