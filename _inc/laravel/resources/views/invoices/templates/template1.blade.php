@php
    # Template 1
    use App\Config\Constants\{DatabaseConstants, ViewsConstants, SettingsConstants};
    use App\Models\{Utility, ProductServiceUnit};
    use Illuminate\Support\Facades\{Auth, Log};
    use InvalidArgumentException;
    use RuntimeException;
    use TypeError;

    $usr ??= null;
    $lang ??= (string)'';
    $siteRtl ??= (string)'';
    $color ??= (string)'#ffffff';
    $font_color ??= (string)'#000000';
    $img ??= (string)'';
    $meta_title ??= (string)'';
    $meta_desc ??= (string)'';
    $themeCSS ??= (string)'';
    $settings ??= [];
    $settings_data ??= [];
    $invoice ??= null;
    $customer ??= null;
    $customFields ??= [];

    try {
        $usr = Auth::user();
    } catch (InvalidArgumentException $e) {
        Log::error('auth_user_fetch_failed', ['file'=>$e->getFile(),'line'=>$e->getLine(),'code'=>$e->getCode(),'error_class'=>get_class($e),'message'=>$e->getMessage()]);
    } catch (RuntimeException $e) {
        Log::error('auth_user_runtime_error', ['file'=>$e->getFile(),'line'=>$e->getLine(),'code'=>$e->getCode(),'error_class'=>get_class($e),'message'=>$e->getMessage()]);
    } catch (TypeError $e) {
        Log::error('auth_user_type_error', ['file'=>$e->getFile(),'line'=>$e->getLine(),'code'=>$e->getCode(),'error_class'=>get_class($e),'message'=>$e->getMessage()]);
    } catch (\Error $e) {
        Log::error('auth_user_error', ['file'=>$e->getFile(),'line'=>$e->getLine(),'code'=>$e->getCode(),'error_class'=>get_class($e),'message'=>$e->getMessage()]);
    } catch (\Exception $e) {
        Log::error('auth_user_exception', ['file'=>$e->getFile(),'line'=>$e->getLine(),'code'=>$e->getCode(),'error_class'=>get_class($e),'message'=>$e->getMessage()]);
    } catch (\Throwable $e) {
        Log::error('auth_user_throwable', ['file'=>$e->getFile(),'line'=>$e->getLine(),'code'=>$e->getCode(),'error_class'=>get_class($e),'message'=>$e->getMessage()]);
    }

    try {
        $lang = (string)(Utility::fetchUserLang(user:$usr) ?? '');
    } catch (\Throwable $e) {
        Log::error('fetch_user_lang_failed', ['file'=>$e->getFile(),'line'=>$e->getLine(),'code'=>$e->getCode(),'error_class'=>get_class($e),'message'=>$e->getMessage()]);
        $lang = '';
    }

    try {
        $settings_data = method_exists(Utility::class, 'settingsById') && isset($invoice[DatabaseConstants::COL_TABLE_CREATOR])
            ? (Utility::settingsById($invoice[DatabaseConstants::COL_TABLE_CREATOR]) ?? [])
            : ($settings_data ?? []);
    } catch (\Throwable $e) {
        Log::error('settings_by_id_failed', ['file'=>$e->getFile(),'line'=>$e->getLine(),'code'=>$e->getCode(),'error_class'=>get_class($e),'message'=>$e->getMessage()]);
        $settings_data = $settings_data ?? [];
    }

    $hasUtilPriceFormat = method_exists(Utility::class, 'priceFormat');
    $hasUtilInvoiceNumberFormat = method_exists(Utility::class, 'invoiceNumberFormat');
    $hasUtilDateFormat = method_exists(Utility::class, 'dateFormat');

    $hasInvGetSubTotal = is_object($invoice) && method_exists($invoice, 'getSubTotal');
    $hasInvGetTotalDiscount = is_object($invoice) && method_exists($invoice, 'getTotalDiscount');
    $hasInvGetTotalTax = is_object($invoice) && method_exists($invoice, 'getTotalTax');
    $hasInvGetTotal = is_object($invoice) && method_exists($invoice, 'getTotal');
    $hasInvGetDue = is_object($invoice) && method_exists($invoice, 'getDue');
    $hasInvTotalCreditNote = is_object($invoice) && method_exists($invoice, 'invoiceTotalCreditNote');

    $subTotalVal = (float)($hasInvGetSubTotal ? ($invoice->getSubTotal() ?? 0) : 0);
    $totalDiscountVal = (float)($hasInvGetTotalDiscount ? ($invoice->getTotalDiscount() ?? 0) : 0);
    $totalTaxVal = (float)($hasInvGetTotalTax ? ($invoice->getTotalTax() ?? 0) : 0);
    $grandTotalVal = $subTotalVal - $totalDiscountVal + $totalTaxVal;
    $paidVal = (float)(
        (($hasInvGetTotal ? ($invoice->getTotal() ?? 0) : 0) - ($hasInvGetDue ? ($invoice->getDue() ?? 0) : 0))
        - ($hasInvTotalCreditNote ? ($invoice->invoiceTotalCreditNote() ?? 0) : 0)
    );
    $creditNoteVal = (float)($hasInvTotalCreditNote ? ($invoice->invoiceTotalCreditNote() ?? 0) : 0);
    $dueVal = (float)($hasInvGetDue ? ($invoice->getDue() ?? 0) : 0);

    $imgSrc = ($img !== '') ? $img : asset('assets/img/placeholder.png');

    $qrHtml = '';
    try {
        $canQR = class_exists(\DNS2D::class) && method_exists(\DNS2D::class, 'getBarcodeHTML');
        $encId = class_exists(\Crypt::class) && method_exists(\Crypt::class, 'encrypt') && isset($invoice->invoice_id) ? \Crypt::encrypt($invoice->invoice_id) : null;
        $routeUrl = function_exists('route') && $encId ? route(ViewsConstants::INV.'.link.copy', $encId) : (isset($invoice->invoice_id) ? (string)$invoice->invoice_id : '');
        $qrHtml = ($canQR && $routeUrl !== '') ? \DNS2D::getBarcodeHTML($routeUrl, "QRCODE", 2, 2) : '';
    } catch (\Throwable $e) {
        Log::error('qr_generate_failed', ['file'=>$e->getFile(),'line'=>$e->getLine(),'code'=>$e->getCode(),'error_class'=>get_class($e),'message'=>$e->getMessage()]);
        $qrHtml = '';
    }
@endphp
<!DOCTYPE html>
<html lang="{{ $lang ? (str_replace('_','-',is_string(app()->getLocale()) ? app()->getLocale() : DatabaseConstants::DEFAULT_LANG)) : DatabaseConstants::DEFAULT_LANG }}" dir="{{ ($siteRtl ?? '') === 'on' ? 'rtl' : '' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $meta_title !== '' ? $meta_title : __('Invoice') }}</title>
    <meta name="description" content="{{ $meta_desc !== '' ? $meta_desc : __('Invoice document') }}">
    <link href="https://fonts.googleapis.com/css2?family=Lato:ital,wght@0,100;0,300;0,400;0,700;0,900;1,100;1,300;1,400;1,700;1,900&display=swap" rel="stylesheet">
    <style>
        <?php echo $themeCSS; ?>
    </style>
    <link rel="stylesheet" href="{{ asset('assets/css/routes/invoices/theme1.css') }}">
    @if(($settings_data[SettingsConstants::RTL] ?? '') === 'on')
        <link rel="stylesheet" href="{{ asset('css/bootstrap-rtl.css') }}">
    @endif
</head>
<body class="">
    <div class="invoice-preview-main" id="boxes">
        <div class="invoice-header" style="background: {{ $color }}; color: {{ $font_color }}">
            <table>
                <tbody>
                    <tr>
                        <td>
                            <img class="invoice-logo" src="{{ $imgSrc }}" alt="">
                        </td>
                        <td class="text-right">
                            <h3 style="text-transform: uppercase; font-size: 40px; font-weight: bold;">{{ __('INVOICE') }}</h3>
                        </td>
                    </tr>
                </tbody>
            </table>
            <table class="vertical-align-top">
                <tbody>
                    <tr>
                        <td>
                            <p>
                                {{ ($settings['company_name'] ?? '') !== '' ? $settings['company_name'] : __('No name available for company') }}<br>
                                {{ ($settings['mail_from_address'] ?? '') !== '' ? $settings['mail_from_address'] : __('No email available for company') }}<br><br>
                                {{ ($settings['company_address'] ?? '') !== '' ? $settings['company_address'] : __('No address available for company') }}
                                {!! (($settings['company_city'] ?? '') !== '') ? '<br> '.e($settings['company_city']).', ' : __('No city available for company') !!}
                                {{ ($settings['company_state'] ?? '') !== '' ? $settings['company_state'] : __('No state available for company') }}
                                {!! (($settings['company_zipcode'] ?? '') !== '') ? ' - '.e($settings['company_zipcode']) : '' !!}
                                {!! (($settings['company_country'] ?? '') !== '') ? '<br>'.e($settings['company_country']) : __('No country available for company') !!}
                                {{ ($settings['company_telephone'] ?? '') !== '' ? $settings['company_telephone'] : __('No telephone available for company') }}<br>
                                {!! (!empty($settings['registration_number'])) ? e(__('Registration Number')).' : '.e($settings['registration_number']) : '' !!} <br>
                                @if(($settings['vat_gst_number_switch'] ?? '') === 'on')
                                    {!! (!empty($settings['tax_type']) && !empty($settings['vat_number'])) ? e($settings['tax_type'].' '.__('Number')).' : '.e($settings['vat_number']).' <br>' : '' !!}
                                @endif
                            </p>
                        </td>
                        <td>
                            <table class="no-space" style="width:45%;margin-left:auto;">
                                <tbody>
                                    <tr>
                                        <td>{{ __('Number') }}:</td>
                                        <td class="text-right">
                                            @if(isset($invoice->invoice_id))
                                                {{ $hasUtilInvoiceNumberFormat ? (string)Utility::invoiceNumberFormat($settings, $invoice->invoice_id) : (string)$invoice->invoice_id }}
                                            @else
                                                {{ __('N/A') }}
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>{{ __('Issue Date') }}:</td>
                                        <td class="text-right">
                                            @if(isset($invoice->issue_date) && $invoice->issue_date !== '')
                                                {{ $hasUtilDateFormat ? (string)Utility::dateFormat($settings, $invoice->issue_date) : (string)$invoice->issue_date }}
                                            @else
                                                {{ __('N/A') }}
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><b>{{ __('Due Date:') }}</b></td>
                                        <td class="text-right">
                                            @if(isset($invoice->due_date) && $invoice->due_date !== '')
                                                {{ $hasUtilDateFormat ? (string)Utility::dateFormat($settings, $invoice->due_date) : (string)$invoice->due_date }}
                                            @else
                                                {{ __('N/A') }}
                                            @endif
                                        </td>
                                    </tr>
                                    @if(!empty($customFields) && isset($invoice->customField) && is_countable($invoice->customField) && count($invoice->customField) > 0)
                                        @foreach($customFields as $field)
                                            <tr>
                                                <td>{{ $field->name }} :</td>
                                                <td>{{ !empty($invoice->customField) && isset($invoice->customField[$field->id]) ? $invoice->customField[$field->id] : '-' }}</td>
                                            </tr>
                                        @endforeach
                                    @endif
                                    <tr>
                                        <td colspan="2">
                                            <div class="view-qrcode">
                                                {!! $qrHtml !== '' ? $qrHtml : '' !!}
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
        <div class="invoice-body">
            <table>
                <tbody>
                    <tr>
                        <td>
                            <strong style="margin-bottom:10px;display:block;">{{ __('Bill To') }}:</strong>
                            @if(!empty($customer?->billing_name))
                                <p>
                                    {{ $customer->billing_name ?? __('No billing name available for customer') }}<br>
                                    {{ $customer->billing_address ?? __('No billing address available for customer') }}<br>
                                    {{ $customer->billing_city ?? __('No billing city available for customer') }}{{ ($customer->billing_city ?? '') !== '' ? ',' : '' }}<br>
                                    {{ $customer->billing_state ?? __('No billing state available for customer') }}{{ ($customer->billing_state ?? '') !== '' ? ',' : '' }}
                                    {{ $customer->billing_zip ?? __('No billing zip available for customer') }}<br>
                                    {{ $customer->billing_country ?? __('No billing country available for customer') }}<br>
                                    {{ $customer->billing_phone ?? __('No billing phone available for customer') }}<br>
                                </p>
                            @else
                                -
                            @endif
                        </td>

                        @if(($settings['shipping_display'] ?? '') === 'on')
                            <td class="text-right">
                                <strong style="margin-bottom:10px;display:block;">{{ __('Ship To') }}:</strong>
                                @if(!empty($customer?->shipping_name))
                                    <p>
                                        {{ $customer->shipping_name ?? __('No shipping name available for customer') }}<br>
                                        {{ $customer->shipping_address ?? __('No shipping address available for customer') }}<br>
                                        {{ $customer->shipping_city ?? __('No shipping city available for customer') }}{{ ($customer->shipping_city ?? '') !== '' ? ',' : '' }}<br>
                                        {{ $customer->shipping_state ?? __('No shipping state available for customer') }}{{ ($customer->shipping_state ?? '') !== '' ? ',' : '' }}
                                        {{ $customer->shipping_zip ?? __('No shipping zip available for customer') }}<br>
                                        {{ $customer->shipping_country ?? __('No shipping country available for customer') }}<br>
                                        {{ $customer->shipping_phone ?? __('No shipping phone available for customer') }}<br>
                                    </p>
                                @else
                                    -
                                @endif
                            </td>
                        @endif
                    </tr>
                </tbody>
            </table>

            <table class="add-border invoice-summary" style="margin-top:30px;">
                <thead style="background: {{ $color }}; color: {{ $font_color }}">
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
                    @if(isset($invoice->itemData) && is_countable($invoice->itemData) && count($invoice->itemData) > 0)
                        @foreach($invoice->itemData as $key => $item)
                            @php
                                $unitNameObj = (class_exists(ProductServiceUnit::class) && method_exists(ProductServiceUnit::class, 'find')) ? ProductServiceUnit::find($item->unit ?? null) : null;
                                $unitLabel = isset($unitNameObj->name) ? $unitNameObj->name : '';
                                $qtyText = (isset($item->quantity) ? $item->quantity : 0) . ($unitLabel !== '' ? ' ('.$unitLabel.')' : '');
                                $rateVal = (float)($item->price ?? 0);
                                $discVal = (float)($item->discount ?? 0);
                                $itemtax = 0.0;
                                if (!empty($item->itemTax) && is_iterable($item->itemTax)) {
                                    foreach ($item->itemTax as $taxes) {
                                        $itemtax += (float)($taxes['tax_price'] ?? 0);
                                    }
                                }
                                $lineTotal = ($rateVal * (float)($item->quantity ?? 0)) - $discVal + $itemtax;
                            @endphp
                            <tr>
                                <td>{{ $item->name ?? '-' }}</td>
                                <td>{{ $qtyText }}</td>
                                <td>{{ $hasUtilPriceFormat ? (string)Utility::priceFormat($settings, $rateVal) : number_format($rateVal, 2) }}</td>
                                <td>{{ $discVal != 0 ? ($hasUtilPriceFormat ? (string)Utility::priceFormat($settings, $discVal) : number_format($discVal, 2)) : '-' }}</td>
                                <td>
                                    @if(!empty($item->itemTax) && is_iterable($item->itemTax))
                                        @foreach($item->itemTax as $taxes)
                                            <p>{{ ($taxes['name'] ?? '') }} ({{ ($taxes['rate'] ?? '') }}) {{ ($taxes['price'] ?? '') }}</p>
                                        @endforeach
                                    @else
                                        <span>-</span>
                                    @endif
                                </td>
                                <td>{{ $hasUtilPriceFormat ? (string)Utility::priceFormat($settings, $lineTotal) : number_format($lineTotal, 2) }}</td>
                            </tr>
                            @if(!empty($item->description))
                                <tr class="border-0 itm-description">
                                    <td colspan="6">{{ $item->description }}</td>
                                </tr>
                            @endif
                        @endforeach
                    @endif
                </tbody>
                <tfoot>
                    <tr>
                        <td>{{ __('Total') }}</td>
                        <td>{{ $invoice->totalQuantity ?? '-' }}</td>
                        <td>{{ $hasUtilPriceFormat ? (string)Utility::priceFormat($settings, (float)($invoice->totalRate ?? 0)) : number_format((float)($invoice->totalRate ?? 0), 2) }}</td>
                        <td>{{ $hasUtilPriceFormat ? (string)Utility::priceFormat($settings, (float)($invoice->totalDiscount ?? 0)) : number_format((float)($invoice->totalDiscount ?? 0), 2) }}</td>
                        <td>{{ $hasUtilPriceFormat ? (string)Utility::priceFormat($settings, (float)($invoice->totalTaxPrice ?? 0)) : number_format((float)($invoice->totalTaxPrice ?? 0), 2) }}</td>
                        <td>{{ $hasUtilPriceFormat ? (string)Utility::priceFormat($settings, $subTotalVal) : number_format($subTotalVal, 2) }}</td>
                    </tr>
                    <tr>
                        <td colspan="4"></td>
                        <td colspan="2" class="sub-total">
                            <table class="total-table">
                                <tr>
                                    <td>{{ __('Subtotal') }}:</td>
                                    <td>{{ $hasUtilPriceFormat ? (string)Utility::priceFormat($settings, $subTotalVal) : number_format($subTotalVal, 2) }}</td>
                                </tr>
                                @if($totalDiscountVal > 0)
                                    <tr>
                                        <td>{{ __('Discount') }}:</td>
                                        <td>{{ $hasUtilPriceFormat ? (string)Utility::priceFormat($settings, $totalDiscountVal) : number_format($totalDiscountVal, 2) }}</td>
                                    </tr>
                                @endif
                                @if(!empty($invoice->taxesData) && is_iterable($invoice->taxesData))
                                    @foreach($invoice->taxesData as $taxName => $taxPrice)
                                        <tr>
                                            <td>{{ $taxName }} :</td>
                                            <td>{{ $hasUtilPriceFormat ? (string)Utility::priceFormat($settings, (float)$taxPrice) : number_format((float)$taxPrice, 2) }}</td>
                                        </tr>
                                    @endforeach
                                @endif
                                <tr>
                                    <td>{{ __('Total') }}:</td>
                                    <td>{{ $hasUtilPriceFormat ? (string)Utility::priceFormat($settings, $grandTotalVal) : number_format($grandTotalVal, 2) }}</td>
                                </tr>
                                <tr>
                                    <td>{{ __('Paid') }}:</td>
                                    <td>{{ $hasUtilPriceFormat ? (string)Utility::priceFormat($settings, $paidVal) : number_format($paidVal, 2) }}</td>
                                </tr>
                                <tr>
                                    <td>{{ __('Credit Note') }}:</td>
                                    <td>{{ $hasUtilPriceFormat ? (string)Utility::priceFormat($settings, $creditNoteVal) : number_format($creditNoteVal, 2) }}</td>
                                </tr>
                                <tr>
                                    <td>{{ __('Due Amount') }}:</td>
                                    <td>{{ $hasUtilPriceFormat ? (string)Utility::priceFormat($settings, $dueVal) : number_format($dueVal, 2) }}</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </tfoot>
            </table>

            <div class="invoice-footer">
                <b>{{ ($settings['footer_title'] ?? '') }}</b> <br>
                {!! $settings['footer_notes'] ?? '' !!}
            </div>
        </div>
    </div>
</body>
</html>
