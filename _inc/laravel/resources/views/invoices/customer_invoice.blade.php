@php
$invoice ??= null;
	$creatorId ??= '';
	$data ??= [];
	$logo ??= '';
	$company_favicon ??= '';
	$colorSettings ??= [];
	$settings_data ??= [];
	$company_setting ??= [];
	$color ??= '';
	$meta_title ??= '';
	$meta_desc ??= '';
	$meta_image ??= '';
	$meta_logo ??= '';
	$get_cookie ??= '';
	$faviconUrl ??= '';
    $user = Auth::user();
    $lang = Utility::fetchUserLang(user: $user);
	try {
		$creatorId = $invoice?->{DatabaseConstants::COL_TABLE_CREATOR} ?? '';
		$data = Utility::prepareCommonViewData($creatorId) ?: [];
		$logo = $data[SettingsConstants::LOGO] ?? '';
		$company_favicon = $data[SettingsConstants::FAV_ICN] ?? '';
		$colorSettings = $data[SettingsConstants::ENTITY] ?? [];
		$settings_data = $data[SettingsConstants::ENTITY] ?? [];
		$company_setting = $data[SettingsConstants::CPN_CFG] ?? [];
		$color = $data[SettingsConstants::THM_CLR] ?? '';
		$meta_title = $data[SettingsConstants::MT_TTL_K] ?? '';
		$meta_desc = $data[SettingsConstants::MT_DESC_LONG] ?? '';
		$meta_image = $data[SettingsConstants::MT_IMG_K] ?? '';
		$meta_logo = $data[SettingsConstants::MT_LOGO] ?? '';
		$get_cookie = $data[SettingsConstants::CK_STG] ?? '';
		$faviconUrl = Utility::getCompanyLogo() ?: '';
	} catch (\Error $e) {
		Log::error(
			'Error fetching invoice view data',
			[
				'exception_class' => get_class($e),
				'message' => $e->getMessage(),
				'file' => $e->getFile(),
				'line' => $e->getLine()
			]
		);
	} catch (\Exception $e) {
		Log::error(
			'Exception fetching invoice view data',
			[
				'exception_class' => get_class($e),
				'message' => $e->getMessage(),
				'file' => $e->getFile(),
				'line' => $e->getLine()
			]
		);
	} catch (\Throwable $e) {
		Log::error(
			'Throwable fetching invoice view data',
			[
				'exception_class' => get_class($e),
				'message' => $e->getMessage(),
				'file' => $e->getFile(),
				'line' => $e->getLine()
			]
		);
	}
    $data = Utility::fallbackSettings($data);
    $companyDataAvailable = is_callable([Utility::class, 'companyData']);
@endphp
<!DOCTYPE html>
<html lang="{{ !empty($lang) ? $lang : (str_replace('_', '-', is_string(app()->getLocale()) ? app()->getLocale() : DatabaseConstants::DEFAULT_LANG)) }}" dir="{{ $settings_data[SettingsConstants::RTL] == 'on' ? 'rtl' : '' }}">
    <head>
        @php
            $title = $creatorId && $companyDataAvailable && Utility::companyData($creatorId, 'title_text');
@endphp
        <title>
            {{ $title ?: config('app.name', 'ERPNovaPrestech') }}
            - {{ __('Invoice') }}</title>
        @include('fragments.std', [
            'meta_title' => $meta_title,
            'meta_desc' => $meta_desc,
            'meta_vp' => "maximum-scale=1, shrink-to-fit=no"
        ])
        @include('fragments.og', [
            'meta_title' => $meta_title,
            'meta_desc' => $meta_desc,
            'meta_image' => $meta_image,
            'meta_logo' => $meta_logo
        ])
        @include('fragments.x', [
            'meta_title' => $meta_title,
            'meta_desc' => $meta_desc,
            'meta_image' => $meta_image,
            'meta_logo' => $meta_logo
        ])
        @include('fragments.favicon', ['faviconUrl' => $faviconUrl])
        @include('fragments.stylesheets', ['settings' => $colorSettings]);
        <link rel="stylesheet" href="{{ asset('assets/css/plugins/bootstrap-switch-button.min.css') }}">
        @if (!empty($settings_data) && isset($settings_data[SettingsConstants::RTL]) && $settings_data[SettingsConstants::RTL] == 'on')
            <link rel="stylesheet" href="{{ asset('assets/css/style-rtl.css') }}">
        @endif
        @stack('css-page')
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <link rel="stylesheet" src="{{ asset('assets/css/routes/invoices/customer-invoice.css') }}" />
    </head>
    <body class="{{ $color }}">
        @if(!empty($invoice && isset($invoice->id)))
            @php
                try {
                    $due = is_callable([$invoice, 'getDue']) ? $invoice->getDue() : 999999999999999.9999999999;
                    $canFormatDate = is_callable([Utility::class, 'dateFormat']);
                    $canFormatPrice = is_callable([Utility::class, 'priceFormat']);
                    $canTaxRate = is_callable([Utility::class, 'taxRate']);
                    $companyDataAvailable = is_callable([Utility::class, 'companyData']);
                    $canGetTotal = is_callable([$invoice, 'getTotal']);
                    $total = $canGetTotal ? $invoice->getTotal() : 9999999999999999999999999999999.9999;
                    $canGetCredit = is_callable([$invoice, 'invoiceTotalCreditNote']);
                    $totalCredit = $canGetCredit ? $invoice->invoiceTotalCreditNote() : -9999999999999999999999999999999.9999;
                    $user_plan ??= $user?->{UsersConstants::COL_PL};
                    $userPlanAv = !empty($user_plan) && isset($user_plan->id);
                    $invoice_user ??= $invoice?->customer_id;
                    $invoiceUserAv = !empty($invoice_user) && isset($invoice_user->id) ? $invoice_user : null;
                    $siteCurrency = !empty($company_setting['site_currency']) ?: __('Failed to retrieve Site Currency');
                } catch (\Throwable $e) {
                    \Log::error('invoices/customer_invoice — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                }
@endphp
            <header class="header header-transparent" id="header-main"></header>
            <div class="{{ VC::MCT_CT }}">
                <div class="{{ VC::RW }} justify-content-between align-items-center mb-3">
                    <div class="col-md-12 {{ VC::DFL }} {{ VC::ALC }} justify-content-between justify-content-md-end">
                        <div class="all-button-box mx-2">
                            @php
                                try {
                                    $routeName         = ViewsConstants::INV . '.pdf';
                                    $encryptedInvoice  = Crypt::encrypt($invoice->id);
                                    $pdfRoute          = Route::has($routeName)
                                        ? route($routeName, $encryptedInvoice)
                                        : '#';
                                    $downloadGuardMsg  = Utility::fetchLinkMessage(
                                        $lang,
                                        ViewsConstants::INV,
                                        'invoice_pdf_route_unavailable'
                                    ) ?? 'Download invoice PDF route is unavailable. Please contact technical support or your domain administrator.';
                                } catch (\Throwable $e) {
                                    \Log::error('invoices/customer_invoice — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                }
@endphp
                            <a
                                href="{{ $pdfRoute }}"
                                target="_blank"
                                class="{{ VC::BT_PRM }} mt-3"
                                data-url="{{ $pdfRoute }}"
                                data-guard-msg="{{ base64_encode($downloadGuardMsg) }}"
                                data-listener-alias="download-invoice-pdf"
                            >
                                {{ __('Download') }}
                            </a>
                            @push(StacksConstants::ADM_SCR_PG)
                                <script defer src="{{ asset('assets/js/routes/invoices/customers/pdf.js') }}"></script>
                            @endpush
                        </div>
                        @if (
                            isset($invoice->status) && $invoice->status != 0 &&
                                $due > 0 &&
                                (!empty($company_payment_setting) &&
                                    ($company_payment_setting['is_bank_transfer_enabled'] == 'on' ||
                                        $company_payment_setting['is_stripe_enabled'] == 'on' ||
                                        $company_payment_setting['is_paypal_enabled'] == 'on' ||
                                        $company_payment_setting['is_paystack_enabled'] == 'on' ||
                                        $company_payment_setting['is_flutterwave_enabled'] == 'on' ||
                                        $company_payment_setting['is_razorpay_enabled'] == 'on' ||
                                        $company_payment_setting['is_mercado_enabled'] == 'on' ||
                                        $company_payment_setting['is_paytm_enabled'] == 'on' ||
                                        $company_payment_setting['is_mollie_enabled'] == 'on' ||
                                        $company_payment_setting['is_paypal_enabled'] == 'on' ||
                                        $company_payment_setting['is_skrill_enabled'] == 'on' ||
                                        $company_payment_setting['is_coingate_enabled'] == 'on' ||
                                        $company_payment_setting['is_paymentwall_enabled'] == 'on' ||
                                        $company_payment_setting['is_toyyibpay_enabled'] == 'on' ||
                                        $company_payment_setting['is_payfast_enabled'] == 'on' ||
                                        $company_payment_setting['is_iyzipay_enabled'] == 'on' ||
                                        $company_payment_setting['is_sspay_enabled'] == 'on' ||
                                        $company_payment_setting['is_paytab_enabled'] == 'on' ||
                                        $company_payment_setting['is_benefit_enabled'] == 'on' ||
                                        $company_payment_setting['is_cashfree_enabled'] == 'on' ||
                                        $company_payment_setting['is_aamarpay_enabled'] == 'on' ||
                                        $company_payment_setting['is_yookassa_enabled'] == 'on' ||
                                        $company_payment_setting['is_midtrans_enabled'] == 'on' ||
                                        $company_payment_setting['is_xendit_enabled'] == 'on')))
                            <div class="all-button-box">
                                <a
                                    href="#"
                                    class="{{ VC::BT_PRM }} mt-3"
                                    data-bs-toggle="modal"
                                    data-bs-target="#paymentModal"
                                >
                                    {{ __('Pay Now') }}
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
                <div class="{{ VC::RW }}">
                    <div class="{{ VC::C12 }}">
                        <div class="card">
                            <div class="{{ VC::CD_BD }}">
                                <div class="invoice">
                                    <div class="invoice-print">
                                        <div class="{{ VC::RW }} invoice-title {{ VC::MT2 }}">
                                            <div class="{{ VC::C12 }} {{ VC::CM6 }} col-lg-6">
                                                <h2>{{ __('Invoice') }}</h2>
                                            </div>
                                            <div class="{{ VC::C12 }} {{ VC::CM6 }} col-lg-6 {{ VC::JCE }}">
                                                <h3 class="invoice-number {{ VC::FEND }}">
                                                    {{ is_callable([$user, 'invoiceNumberFormat']) ? $user->invoiceNumberFormat($invoice->invoice_id) : __('Failed to format invoice number') }}
                                                </h3>
                                            </div>
                                            <div class="{{ VC::C12 }}"><hr></div>
                                        </div>
                                        <div class="{{ VC::RW }}">
                                            <div class="col {{ VC::JCE }}">
                                                <div class="{{ VC::DFL }} {{ VC::ALC }} {{ VC::JCE }}">
                                                    <div class="{{ VC::ME4 }}">
                                                        <small>
                                                            <strong>{{ __('Issue Date') }} :</strong><br>
                                                            {{ isset($invoice->issue_date) ? ($canFormatDate ? Utility::dateFormat($settings, $invoice->issue_date) : __('Failed to format issue date')) : __('Failed to get issue date')}}<br><br>
                                                        </small>
                                                    </div>
                                                    <small>
                                                        <strong>{{ __('Due Date') }} :</strong><br>
                                                        {{ isset($invoice->due_date) ? ($canFormatDate ? Utility::dateFormat($settings, $invoice->due_date) : __('Failed to format due date')) : __('Failed to get due date') }}<br><br>
                                                    </small>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="{{ VC::RW }}">
                                            @if(!empty($customer))
                                                @if (!empty($customer->billing_name))
                                                    <div class="{{ VC::C12 }} {{ VC::CM4 }} col-lg-4">
                                                        <small class="font-style">
                                                            <strong>{{ __('Billed To') }} :</strong><br>
                                                            {{ !empty($customer->billing_name) ? $customer->billing_name : __('Name for billing unavailable') }}<br>
                                                            {{ !empty($customer->billing_phone) ? $customer->billing_phone : __('Phone for billing unavailable') }}<br>
                                                            {{ !empty($customer->billing_address) ? $customer->billing_address : __('Address for billing unavailable') }}<br>
                                                            {{ !empty($customer->billing_zip) ? $customer->billing_zip : __('ZIP for billing unavailable') }}<br>
                                                            {{ !empty($customer->billing_city) ? $customer->billing_city : __('City for billing unavailable') }}, {{ !empty($customer->billing_state) ? $customer->billing_state : __('State for billing unavailable') }}, {{ !empty($customer->billing_country) ? $customer->billing_country : __('Country for billing unavailable') }}
                                                        </small>
                                                    </div>
                                                @endif
                                                @if (Utility::companyData($creatorId, 'shipping_display') == 'on')
                                                    <div class="{{ VC::C12 }} {{ VC::CM4 }} col-lg-4">
                                                        <small>
                                                            <strong>{{ __('Shipped To') }} :</strong><br>
                                                            {{ !empty($customer->shipping_name) ? $customer->shipping_name : __('Name for shipping unavailable') }}<br>
                                                            {{ !empty($customer->shipping_phone) ? $customer->shipping_phone : __('Phone for shipping unavailable')}}<br>
                                                            {{ !empty($customer->shipping_address) ? $customer->shipping_address : __('Address for shipping unavailable') }}<br>
                                                            {{ !empty($customer->shipping_zip) ? $customer->shipping_zip : __('ZIP for shipping unavailable') }}<br>
                                                            {{ !empty($customer->shipping_city) ? $customer->shipping_city : __('City for shipping unavailable') }}, {{ !empty($customer->shipping_state) ? $customer->shipping_state : __('State for shipping unavailable') }}, {{ !empty($customer->shipping_country) ? $customer->shipping_country : __('Country for shipping unavailable') }}
                                                        </small>
                                                    </div>
                                                @endif
                                            @else
                                                <div class="col"><small>{{ __('No customer data available') }}</small></div>
                                            @endif
                                            <div class="{{ VC::C12 }} {{ VC::CM4 }} col-lg-4 {{ VC::DFL }} {{ VC::JCE }}">
                                                @php
                                                    try {
                                                        $routeName      = ViewsConstants::INV . '.link.copy';
                                                        $copyLinkRoute  = Route::has($routeName)
                                                            ? route($routeName, Crypt::encrypt($invoice->id))
                                                            : '#';
                                                        $guardMsg       = Utility::fetchLinkMessage(
                                                            $lang,
                                                            ViewsConstants::INV,
                                                            'link_copy_route_unavailable'
                                                        ) ?? 'Invoice link copy route is unavailable. Please contact technical support or your domain administrator.';
                                                    } catch (\Throwable $e) {
                                                        \Log::error('invoices/customer_invoice — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                    }
@endphp
                                                <div class="{{ VC::FEND }} {{ VC::MT3 }}">
                                                    <div
                                                        class="qr-code-wrapper"
                                                        data-url="{{ $copyLinkRoute }}"
                                                        data-guard-msg="{{ base64_encode($guardMsg) }}"
                                                        data-listener-alias="qrcode-copy-link"
                                                    >
                                                        {!! class_exists(\Milon\Barcode\DNS2D::class) && is_callable([\Milon\Barcode\DNS2D, 'getBarcodeHTML']) ? \Milon\Barcode\DNS2D::getBarcodeHTML($copyLinkRoute, 'QRCODE', 2, 2) : __('Failed to generate QRCode') !!}
                                                    </div>
                                                </div>
                                                @push(StacksConstants::ADM_SCR_PG)
                                                    <script defer src="{{ asset('assets/js/routes/invoices/customers/qrcode.js') }}"></script>
                                                @endpush
                                            </div>
                                        </div>
                                        <div class="{{ VC::RW }} mt-3">
                                            <div class="{{ VC::C12 }} {{ VC::CM4 }} col-lg-4">
                                                <small>
                                                    <strong>{{ __('Status') }} :</strong><br>
                                                    @php
 try {
     $badge = match($invoice->status) {
                                                            0 => 'bg-primary',
                                                            1 => 'bg-warning',
                                                            2 => 'bg-danger',
                                                            3 => 'bg-info',
                                                            4 => 'bg-primary',
                                                            default => 'bg-secondary',
                                                        };
 } catch (\Throwable $e) {
     \Log::error('invoices/customer_invoice — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
 }
@endphp
                                                    <span class="badge {{ $badge }}">
                                                        {{ __(!empty(Invoice::$statuses) ? Invoice::$statuses[$invoice->status] : __('Failed to retrieve status')) }}
                                                    </span>
                                                </small>
                                            </div>
                                            @if (Utility::isFilled($customFields) && Utility::isFilled($invoice->customField) ?? [])
                                                @foreach ($customFields as $field)
                                                    <div class="{{ VC::C12 }} {{ VC::CM4 }} col-lg-4 {{ VC::JCE }}">
                                                        <small>
                                                            <strong>{{ !empty($field->name) ? $field->name : __('Unnamed field')  }} :</strong><br>
                                                            {{ $invoice->customField[$field->id] ?? __('Failed to get field') }}<br><br>
                                                        </small>
                                                    </div>
                                                @endforeach
                                            @endif
                                        </div>
                                        <div class="{{ VC::RW }} {{ VC::MT4 }}">
                                            <div class="{{ VC::C12 }}">
                                                <div class="font-weight-bold">{{ __('Product Summary') }}</div>
                                                <small>{{ __('All items here cannot be deleted.') }}</small>
                                                <div class="table-responsive {{ VC::MT2 }}">
                                                    <table class="{{ VC::TB }} {{ VC::MB0 }} table-striped">
                                                        <tr>
                                                            <th data-width="40" class="{{ VC::TX_DK }}">#</th>
                                                            <th class="{{ VC::TX_DK }}">{{ __('Product') }}</th>
                                                            <th class="{{ VC::TX_DK }}">{{ __('Quantity') }}</th>
                                                            <th class="{{ VC::TX_DK }}">{{ __('Rate') }}</th>
                                                            <th class="{{ VC::TX_DK }}">{{ __('Discount') }}</th>
                                                            <th class="{{ VC::TX_DK }}">{{ __('Tax') }}</th>
                                                            <th class="{{ VC::TX_DK }}">{{ __('Description') }}</th>
                                                            <th class="{{ VC::TX_END }} {{ VC::TX_DK }}" width="12%">
                                                                {{ __('Price') }}<br>
                                                                <small class="{{ VC::TX_DNG }} font-weight-bold">
                                                                    {{ __('after tax & discount') }}
                                                                </small>
                                                            </th>
                                                        </tr>
                                                        @php
                                                            $totalQuantity ??= 0;
                                                            $totalRate     ??= 0;
                                                            $totalTaxPrice ??= 0;
                                                            $totalDiscount ??= 0;
                                                            $itemsWithCalculations ??= [];
@endphp
                                                        @if(Utility::isFilled($items) ?? [])
                                                            @foreach ($items as $key => $item)
                                                                @php
                                                                    $itemTaxes ??= [];
                                                                    $itemTotalTaxPrice ??= 0;
                                                                    try {
                                                                        $itemQuantity = !empty($item->quantity) ? $item->quantity : 0;
                                                                        $itemPrice = !empty($item->price) ? $item->price : 99999999999.99999;
                                                                        $itemDiscount = !empty($item->discount) ? $item->discount : 0;

                                                                        if (!empty($item->tax)) {
                                                                            $taxes = is_callable([Utility::class, 'tax']) ? Utility::tax($item->tax) : [];

                                                                            if(Utility::isFilled($taxes) ?? []) {
                                                                                if (!$canTaxRate) {
                                                                                    Log::warning("Cannot calculate tax rate for invoice {$invoice->id} item ID {$item->id} because Utility::taxRate is not callable.");
                                                                                }

                                                                                foreach ($taxes as $tax) {
                                                                                    $taxPrice = $canTaxRate ? Utility::taxRate(
                                                                                        !empty($tax->rate) ? $tax->rate : 0,
                                                                                        $itemPrice,
                                                                                        $itemQuantity,
                                                                                        $itemDiscount
                                                                                    ) : 0;

                                                                                    $itemTaxes[] = [
                                                                                        'tax' => $tax,
                                                                                        'price' => $taxPrice
                                                                                    ];
                                                                                    $itemTotalTaxPrice += $taxPrice;
                                                                                }
                                                                            } else {
                                                                                Log::warning("No taxes found for invoice {$invoice->id} item ID {$item->id}");
                                                                            }
                                                                        }

                                                                        $itemsWithCalculations[$key] = [
                                                                            'item' => $item,
                                                                            'quantity' => $itemQuantity,
                                                                            'price' => $itemPrice,
                                                                            'discount' => $itemDiscount,
                                                                            'taxes' => $itemTaxes,
                                                                            'totalTaxPrice' => $itemTotalTaxPrice
                                                                        ];

                                                                        $totalQuantity += $itemQuantity;
                                                                        $totalRate += $itemPrice;
                                                                        $totalDiscount += $itemDiscount;
                                                                        $totalTaxPrice += $itemTotalTaxPrice;
                                                                    } catch (\Throwable $e) {
                                                                        \Log::error('invoices/customer_invoice — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                                    }
@endphp
                                                            @endforeach

                                                            @foreach ($itemsWithCalculations as $key => $itemData)
                                                                @php
                                                                    try {
                                                                        $item = $itemData['item'];
                                                                        $itemTaxes = $itemData['taxes'];
                                                                        $itemTotalTaxPrice = $itemData['totalTaxPrice'];
                                                                    } catch (\Throwable $e) {
                                                                        \Log::error('invoices/customer_invoice — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                                    }
@endphp
                                                                <tr>
                                                                    <td>{{ $key + 1 }}</td>
                                                                    <td>{{ isset($item->product) && isset($item->product->name) && !empty($item->product->name) ? $item->product->name : __('No name available for product') }}</td>
                                                                    <td>{{ $itemData['quantity'] }}</td>
                                                                    <td>{{ $canFormatPrice ? Utility::priceFormat($settings, $itemData['price']) : __('Failed to format price') }}</td>
                                                                    <td>{{ $itemData['discount'] > 0 ? ($canFormatPrice ? Utility::priceFormat($settings, $itemData['discount']) : __('Failed to format price')) : __('No discount available')}}</td>
                                                                    <td>
                                                                        @if (!empty($item->tax) && !empty($itemTaxes))
                                                                            <table>
                                                                                @foreach ($itemTaxes as $taxData)
                                                                                    <tr>
                                                                                        <td>{{ "{$taxData['tax']->name} ({$taxData['tax']->rate}% )" }}</td>
                                                                                        <td>{{ $canFormatPrice ? Utility::priceFormat($settings, $taxData['price']) : __('Failed to format price') }}</td>
                                                                                    </tr>
                                                                                @endforeach
                                                                            </table>
                                                                        @else
                                                                            <table><tr><td>{{ __('No registered tax for item') }}</td></tr></table>
                                                                        @endif
                                                                    </td>
                                                                    <td>{{ $item->description ?: __('No description for item') }}</td>
                                                                    <td class="{{ VC::TX_END }}">
                                                                        {{ $canFormatPrice ? Utility::priceFormat(
                                                                            $settings,
                                                                            $itemData['price'] * $itemData['quantity']
                                                                            - $itemData['discount']
                                                                            + $itemTotalTaxPrice
                                                                        ) : __('Failed to format final price') }}
                                                                    </td>
                                                                </tr>
                                                            @endforeach
                                                        @else
                                                            <tr>
                                                                <td colspan="8" class="{{ VC::TXCT }}">
                                                                    {{ __('No data available in table') }}
                                                                </td>
                                                            </tr>
                                                        @endif
                                                        <tfoot>
                                                            <tr>
                                                                <td></td>
                                                                <td><b>{{ __('Total') }}</b></td>
                                                                <td><b>{{ $totalQuantity }}</b></td>
                                                                <td>{{ $canFormatPrice ? Utility::priceFormat($settings, $totalRate) : __('Failed to format price') }}</td>
                                                                <td><b>{{ $canFormatPrice ? Utility::priceFormat($settings, $totalDiscount) : __('Failed to format price')}}</b></td>
                                                                <td><b>{{ $canFormatPrice ? Utility::priceFormat($settings, $totalTaxPrice) : __('Failed to format price')}}</b></td>
                                                                <td></td>
                                                            </tr>
                                                            <tr>
                                                                <td colspan="6"></td>
                                                                <td class="{{ VC::TX_END }}"><b>{{ __('Sub Total') }}</b></td>
                                                                <td class="{{ VC::TX_END }}">
                                                                    {{ $canFormatPrice && is_callable([$invoice, 'getSubTotal']) ?Utility::priceFormat($settings, $invoice->getSubTotal()) : __('Failed to format subtotal') }}
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td colspan="6"></td>
                                                                <td class="{{ VC::TX_END }}"><b>{{ __('Discount') }}</b></td>
                                                                <td class="{{ VC::TX_END }}">
                                                                    {{ $canFormatPrice && is_callable([$invoice, 'getTotalDiscount']) ?Utility::priceFormat($settings, $invoice->getTotalDiscount()) : __('Failed to format total discount')}}
                                                                </td>
                                                            </tr>
                                                            @if (Utility::isFilled($taxesData) ?? [])
                                                                @foreach ($taxesData as $taxName => $taxPrice)
                                                                    <tr>
                                                                        <td colspan="6"></td>
                                                                        <td class="{{ VC::TX_END }}"><b>{{ $taxName }}</b></td>
                                                                        <td class="{{ VC::TX_END }}">
                                                                            {{ $canFormatPrice ? Utility::priceFormat($settings, $taxPrice) : __('Failed to format tax price') }}
                                                                        </td>
                                                                    </tr>
                                                                @endforeach
                                                            @else
                                                                <tr>
                                                                    <td colspan="6"></td>
                                                                    <td class="{{ VC::TX_END }}"><b>{{ __('Tax') }}</b></td>
                                                                    <td class="{{ VC::TX_END }}">
                                                                        {{__('No data for taxes')}}
                                                                    </td>
                                                                </tr>
                                                            @endif
                                                            <tr>
                                                                <td colspan="6"></td>
                                                                <td class="blue-text {{ VC::TX_END }}"><b>{{ __('Total') }}</b></td>
                                                                <td class="blue-text {{ VC::TX_END }}">
                                                                    {{ $canFormatPrice && $canGetTotal ? Utility::priceFormat($settings, $total) : __('Failed to format total') }}
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td colspan="6"></td>
                                                                <td class="{{ VC::TX_END }}"><b>{{ __('Paid') }}</b></td>
                                                                <td class="{{ VC::TX_END }}">
                                                                    {{ $canFormatPrice && $canGetTotal && $canGetCredit ? Utility::priceFormat(
                                                                        $settings,
                                                                        $total
                                                                        - $due
                                                                        - $totalCredit
                                                                    ) : __('Failed to format paid value') }}
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td colspan="6"></td>
                                                                <td class="{{ VC::TX_END }}"><b>{{ __('Credit Note') }}</b></td>
                                                                <td class="{{ VC::TX_END }}">
                                                                    {{ $canFormatPrice && $canGetCredit ? Utility::priceFormat($settings, $totalCredit) : __('Failed to format credit note') }}
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td colspan="6"></td>
                                                                <td class="{{ VC::TX_END }}"><b>{{ __('Due') }}</b></td>
                                                                <td class="{{ VC::TX_END }}">
                                                                    {{ $canFormatPrice ? Utility::priceFormat($settings, $due) : __('Failed to format due') }}
                                                                </td>
                                                            </tr>
                                                        </tfoot>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="{{ VC::C12 }}">
                        <h5 class="h4 d-inline-block font-weight-400 {{ VC::MB2 }}">{{ __('Receipt Summary') }}</h5><br>
                        @if(!$invoiceUserAv || !$userPlanAv)
                            <small>{{ __('Failed to access plans data') }}</small><br />
                        @else
                            @if ($user_plan->storage_limit <= $invoice_user->storage_limit)
                                <small class="{{ VC::TXT_MT }} {{ VC::FW600 }} text-danger">
                                    {{ __('Your plan storage limit is over , so you can not see customer uploaded payment receipt') }}
                                </small><br>
                            @else
                                <small>{{ __('You can see customer uploaded payment receipt until your plan storage limit is over') }}</small><br />
                            @endif
                        @endif
                        <div class="{{ VC::CD }} {{ VC::MT1 }}">
                            <div class="{{ VC::CD_MT }} table-border-style">
                                <div class="{{ VC::tableResponsive ?? 'table-responsive' }}">
                                    <table class="{{ VC::TB }}">
                                        <tr>
                                            <th class="{{ VC::TX_DK }}">{{ __('Date') }}</th>
                                            <th class="{{ VC::TX_DK }}">{{ __('Amount') }}</th>
                                            <th class="{{ VC::TX_DK }}">{{ __('Payment Type') }}</th>
                                            <th class="{{ VC::TX_DK }}">{{ __('Account') }}</th>
                                            <th class="{{ VC::TX_DK }}">{{ __('Reference') }}</th>
                                            <th class="{{ VC::TX_DK }}">{{ __('Description') }}</th>
                                            <th class="{{ VC::TX_DK }}">{{ __('Receipt') }}</th>
                                            <th class="{{ VC::TX_DK }}">{{ __('OrderId') }}</th>
                                            @can('delete invoice product')
                                                <th class="{{ VC::TX_DK }}">{{ __('Action') }}</th>
                                            @endcan
                                        </tr>
                                        @php
 $path = Utility::getFile('uploads/order');
@endphp
                                        @forelse ($invoice->payments as $key => $payment)
                                            <tr>
                                                <td>{{ $canFormatDate ? (!empty($payment->date) ? Utility::dateFormat($settings, $payment->date) : __('No date available for payment')) : __('Failed to format Payment date') }}</td>
                                                <td>{{$canFormatPrice ? (!empty($payment->amount) ? Utility::priceFormat($payment->amount) : __('No price amount for payment available')) : __('Failed to format payment amount price')}}</td>
                                                <td>{{!empty($payment->payment_type) ? $payment->payment_type : __('No payment type available')}}</td>
                                                <td>{{!empty($payment->bankAccount) && !empty($payment->bankAccount->bank_name) && !empty($payment->bankAccount->holder_name) ? $payment->bankAccount->bank_name.' '.$payment->bankAccount->holder_name: __('No information about bank and holder name')}}</td>
                                                <td>{{!empty($payment->reference) ? $payment->reference: __('No reference available for payment')}}</td>
                                                <td>{{!empty($payment->description)?$payment->description: __('No description available for payment')}}</td>
                                                @if(!$invoiceUserAv || !$userPlanAv)
                                                    <td>
                                                        {{ __('Failed to access plans data') }}
                                                    </td>
                                                @else
                                                    @if ($user_plan->storage_limit <= $invoice_user->storage_limit)
                                                        <td><small class="{{ VC::TX_DNG }} font-bold">{{__('Your plan storage limit is over')}}</small></td>
                                                    @else
                                                        <td>
                                                            @if (!empty($payment->receipt))
                                                                @php
                                                                    try {
                                                                        $receiptUrl  = $path . '/' . $payment->receipt;
                                                                        $guardMsg    = Utility::fetchLinkMessage(
                                                                            $lang,
                                                                            ViewsConstants::INV,
                                                                            'payment_receipt_route_unavailable'
                                                                        ) ?? 'Payment receipt route is unavailable. Please contact technical support or your domain administrator.';
                                                                    } catch (\Throwable $e) {
                                                                        \Log::error('invoices/customer_invoice — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                                    }
@endphp
                                                                <a
                                                                    href="{{ $receiptUrl }}"
                                                                    target="_blank"
                                                                    class="{{ VC::BT_SM_CT_PR }}"
                                                                    data-url="{{ $receiptUrl }}"
                                                                    data-guard-msg="{{ base64_encode($guardMsg) }}"
                                                                    data-listener-alias="payment-receipt"
                                                                >
                                                                    <i class="{{ VC::TI_FL }}"></i> {{ __('Receipt') }}
                                                                </a>
                                                                @push(StacksConstants::ADM_SCR_PG)
                                                                    <script defer src="{{ asset('assets/js/routes/invoices/customers/paymentReceipts.js') }}"></script>
                                                                @endpush
                                                            @elseif(!empty($payment->add_receipt))
                                                                @php
                                                                    try {
                                                                        $receiptUrl = asset(Storage::url('uploads/payment') . '/' . $payment->add_receipt);
                                                                        $guardMsg = Utility::fetchLinkMessage(
                                                                            $lang,
                                                                            ViewsConstants::INV,
                                                                            'payment_add_receipt_route_unavailable'
                                                                        ) ?? 'Payment receipt URL is unavailable. Please contact technical support or your domain administrator.';
                                                                    } catch (\Throwable $e) {
                                                                        \Log::error('invoices/customer_invoice — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                                    }
@endphp
                                                                <a
                                                                    href="{{ $receiptUrl }}"
                                                                    target="_blank"
                                                                    class="{{ VC::BT_SM_CT_PR }}"
                                                                    data-url="{{ $receiptUrl }}"
                                                                    data-guard-msg="{{ base64_encode($guardMsg) }}"
                                                                    data-listener-alias="payment-add-receipt"
                                                                >
                                                                    <i class="{{ VC::TI_FL }}"></i> {{ __('Receipt') }}
                                                                </a>
                                                                @push(StacksConstants::ADM_SCR_PG)
                                                                    <script defer src="{{ asset('assets/js/routes/invoices/customers/addPaymentReceipt.js') }}"></script>
                                                                @endpush
                                                            @else
                                                                <div><small class="{{ VC::TXT_MT }}">{{ __('No receipt available') }}</small></div>
                                                            @endif
                                                        </td>
                                                    @endif
                                                @endif
                                                <td>{{ $payment->order_id ?: __('Failed to retrieve order ID') }}</td>
                                                @can('delete invoice product')
                                                    @php
                                                        try {
                                                            $deleteName  = ViewsConstants::INV . '.payment.destroy';
                                                            $deleteRoute = Route::has($deleteName)
                                                                ? route($deleteName, [$invoice->id, $payment->id])
                                                                : '#';
                                                            $delPayFormId      = 'delete-form-' . $payment->id;
                                                            $lang        = Utility::fetchUserLang();
                                                            $guardMsg    = Utility::fetchLinkMessage(
                                                                $lang,
                                                                ViewsConstants::INV,
                                                                'payment_delete_route_unavailable'
                                                            ) ?? 'Payment delete route is unavailable. Please contact technical support or your domain administrator.';
                                                        } catch (\Throwable $e) {
                                                            \Log::error('invoices/customer_invoice — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                        }
@endphp
                                                    <td>
                                                        <div class="{{ VC::ACT_BTN_DNG }} {{ VC::MS2 }}">
                                                            {!! Form::open([
                                                                'method'         => 'DELETE',
                                                                'id'             => $delPayFormId,
                                                                'data-url'       => $deleteRoute,
                                                                'data-guard-msg' => $guardMsg,
                                                            ]) !!}
                                                                <a href="#"
                                                                class="{{ VC::BT_SM_CT_PR }}"
                                                                data-listener-alias="delete-payment-{{ $payment->id }}"
                                                                data-bs-toggle="tooltip"
                                                                title="{{ __('Delete') }}"
                                                                data-confirm="{{ __(Utility::fetchLinkMessage($lang, 'generics', 'are_you_sure') ?? 'Are You Sure?') }}|{{ __(Utility::fetchLinkMessage($lang, 'generics', 'irreversible_action') ?? 'This action can not be undone. Do you want to continue?') }}"
                                                                data-confirm-yes="document.getElementById('delete-form-{{ $payment->id }}').submit();">
                                                                <i class="{{ VC::TI_TRS_WT }}"></i>
                                                                </a>
                                                            {!! Form::close() !!}
                                                        </div>
                                                    </td>
                                                    @push(StacksConstants::ADM_SCR_PG)
                                                        <script defer>
                                                            (() => {
                                                                const selector = '[data-listener-alias="delete-payment-{{ $payment->id }}"]';
                                                                const link = document.querySelector(selector);
                                                                if (!link || link.getAttribute('data-listener-active') === 'true') return;
                                                                link.setAttribute('data-listener-active', 'true');
                                                                link.addEventListener('click', event => {
                                                                    try {
                                                                        const url = link.getAttribute('data-url') ?? '#';
                                                                        if (url !== '#') return;
                                                                        event.preventDefault();
                                                                        const msg = link.getAttribute('data-guard-msg') ?? '# ERROR';
                                                                        (window.RouteGuard?.showToast || (m => alert(m)))(msg);
                                                                        link.setAttribute('data-failed-route', 'true');
                                                                    } catch (e) {}
                                                                });
                                                            })();
                                                        </script>
                                                    @endpush
                                                @endcan
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="{{ Gate::check('delete invoice product') ? 9 : 8 }}"
                                                    class="{{ VC::TXCT_DK }}">
                                                    {{ __('No Data Found') }}
                                                </td>
                                            </tr>
                                        @endforelse
                                        @if(Utility::isFilled($invoice->bankPayments) ?? [])
                                            @foreach ($invoice->bankPayments as $bankPayment)
                                                <tr>
                                                    <td>{{$canFormatDate ? (!empty($bankPayment->date) ? Utility::dateFormat($bankPayment->date) : __('No date for bank payment available')) : __('Failed to format bank payment date')}}</td>
                                                    <td>{{$canFormatPrice ? (!empty($bankPayment->amount) ? Utility::priceFormat($bankPayment->amount) : __('No price amount for bank payment available')) : __('Failed to format bank payment amount price')}}</td>
                                                    <td>{{ __('Bank Transfer') }}</td>
                                                    <td>—</td><td>—</td><td>—</td>
                                                    @if(!$invoiceUserAv || !$userPlanAv)
                                                        <td>
                                                            {{ __('Failed to access plans data') }}
                                                        </td>
                                                    @else
                                                        @if ($user_plan->storage_limit <= $invoice_user->storage_limit)
                                                            <td><small class="{{ VC::TX_DNG }} font-bold">{{__('Your plan storage limit is over')}}</small></td>
                                                        @else
                                                            <td>
                                                                @if ($bankPayment->receipt)
                                                                    @php
                                                                        try {
                                                                            $receiptUrl         = !empty($bankPayment->receipt)
                                                                                ? ($path . '/' . $bankPayment->receipt)
                                                                                : '#';
                                                                            $guardMsg           = Utility::fetchLinkMessage(
                                                                                $lang,
                                                                                ViewsConstants::INV,
                                                                                'bank_payment_receipt_route_unavailable'
                                                                            ) ?? 'Bank payment receipt route is unavailable. Please contact technical support or your domain administrator.';
                                                                            $listenerAlias      = 'bankpayment-receipt-' . $bankPayment->id;
                                                                        } catch (\Throwable $e) {
                                                                            \Log::error('invoices/customer_invoice — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                                        }
@endphp
                                                                    <a
                                                                        href="{{ $receiptUrl }}"
                                                                        target="_blank"
                                                                        class="{{ VC::BT_SM_CT_PR }}"
                                                                        data-url="{{ $receiptUrl }}"
                                                                        data-guard-msg="{{ base64_encode($guardMsg) }}"
                                                                        data-listener-alias="{{ $listenerAlias }}"
                                                                    >
                                                                        <i class="{{ VC::TI_FL }}"></i> {{ __('Receipt') }}
                                                                    </a>
                                                                    @push(StacksConstants::ADM_SCR_PG)
                                                                        <script defer src="{{ asset('assets/js/routes/invoices/customers/bankPaymentReceipt.js') }}"></script>
                                                                    @endpush
                                                                @else
                                                                    <div><small class="{{ VC::TXT_MT }}">{{ __('No bank payment receipt available') }}</small></div>
                                                                @endif
                                                            </td>
                                                        @endif
                                                    @endif
                                                    <td>{{ $bankPayment->order_id ?: __('Failed to get order identifier') }}</td>
                                                    @can('delete invoice product')
                                                        <td>
                                                            @if (!empty($bankPayment) && $bankPayment->status == 'Pending')
                                                                <div class="{{ VC::ACT_BTN_INF }}">
                                                                    @php
                                                                        try {
                                                                            $actionName       = ViewsConstants::INV . '.action';
                                                                            $actionRoute      = Route::has($actionName)
                                                                                ? route($actionName, $bankPayment->id)
                                                                                : '#';
                                                                            $guardMsg         = Utility::fetchLinkMessage(
                                                                                $lang,
                                                                                ViewsConstants::INV,
                                                                                'payment_status_route_unavailable'
                                                                            ) ?? 'Payment status route is unavailable. Please contact technical support or your domain administrator.';
                                                                        } catch (\Throwable $e) {
                                                                            \Log::error('invoices/customer_invoice — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                                        }
@endphp
                                                                    <a
                                                                        href="#"
                                                                        id="paymentStatusBtn_{{ $bankPayment->id }}"
                                                                        data-url="{{ $actionRoute }}"
                                                                        data-guard-msg="{{ base64_encode($guardMsg) }}"
                                                                        data-listener-alias="payment-status-{{ $bankPayment->id }}"
                                                                        data-ajax-popup="true"
                                                                        data-size="lg"
                                                                        data-title="{{ __('Payment Status') }}"
                                                                        class="{{ VC::BT_SM }}"
                                                                        data-bs-toggle="tooltip"
                                                                        title="{{ __('Payment Status') }}"
                                                                    >
                                                                        <i class="{{ VC::TI_CRT_WT }}"></i>
                                                                    </a>
                                                                    @push(StacksConstants::ADM_SCR_PG)
                                                                        <script defer>
                                                                            (() => {
                                                                                const selector = '[data-listener-alias="payment-status-{{ $bankPayment->id }}"]';
                                                                                const btn = document.querySelector(selector);
                                                                                if (!btn || btn.getAttribute('data-listener-active') === 'true') return;
                                                                                btn.setAttribute('data-listener-active', 'true');
                                                                                btn.addEventListener('click', e => {
                                                                                    try {
                                                                                        const url = btn.getAttribute('data-url') ?? '#';
                                                                                        if (url !== '#') return;
                                                                                        e.preventDefault();
                                                                                        const msg = btn.getAttribute('data-guard-msg') ?? '# ERROR';
                                                                                        (window.RouteGuard?.showToast || (m => alert(m)))(msg);
                                                                                        btn.setAttribute('data-failed-route', 'true');
                                                                                    } catch {}
                                                                                });
                                                                            })();
                                                                        </script>
                                                                    @endpush
                                                                </div>
                                                            @endif
                                                            @php
                                                                try {
                                                                    $destroyName     = ViewsConstants::INV . '.payment.destroy';
                                                                    $destroyRoute    = Route::has($destroyName)
                                                                        ? route($destroyName, [$invoice->id, $bankPayment->id])
                                                                        : '#';
                                                                    $guardMsg        = Utility::fetchLinkMessage(
                                                                        $lang,
                                                                        ViewsConstants::INV,
                                                                        'payment_destroy_route_unavailable'
                                                                    ) ?? 'Payment delete route is unavailable. Please contact technical support or your domain administrator.';
                                                                    $bankPayFormId          = 'delete-form-' . $bankPayment->id;
                                                                    $listenerAlias   = 'delete-bankpayment-' . $bankPayment->id;
                                                                } catch (\Throwable $e) {
                                                                    \Log::error('invoices/customer_invoice — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                                }
@endphp
                                                            <div class="{{ VC::ACT_BTN_DNG }} {{ VC::MS2 }}">
                                                                {!! Form::open([
                                                                    'method'         => 'DELETE',
                                                                    'route'          => [ViewsConstants::INV . '.payment.destroy', $invoice->id, $bankPayment->id],
                                                                    'id'             => $bankPayFormId,
                                                                    'data-url'       => $destroyRoute,
                                                                    'data-guard-msg' => $guardMsg,
                                                                ]) !!}
                                                                    <a href="#"
                                                                    class="{{ VC::BT_SM_CT_PR }}"
                                                                    data-listener-alias="{{ $listenerAlias }}"
                                                                    data-bs-toggle="tooltip"
                                                                    title="{{ __('Delete') }}"
                                                                    data-confirm="{{ __(Utility::fetchLinkMessage($lang, 'generics', 'are_you_sure') ?? 'Are You Sure?') }}|{{ __(Utility::fetchLinkMessage($lang, 'generics', 'irreversible_action') ?? 'This action can not be undone. Do you want to continue?') }}"
                                                                    data-confirm-yes="document.getElementById('{{ $bankPayFormId }}').submit();"
                                                                    >
                                                                        <i class="{{ VC::TI_TRS_WT }}"></i>
                                                                    </a>
                                                                {!! Form::close() !!}
                                                            </div>
                                                            @push(StacksConstants::ADM_SCR_PG)
                                                                <script defer src="{{ asset('assets/js/routes/invoices/customers/deleteBankPayment.js') }}"></script>
                                                            @endpush
                                                        </td>
                                                    @endcan
                                                </tr>
                                            @endforeach
                                        @else
                                            <tr>
                                                <td colspan="{{ Gate::check('delete invoice product') ? 9 : 8 }}"
                                                    class="{{ VC::TXCT_DK }}">
                                                    {{ __('No Data Found for Bank Payments') }}
                                                </td>
                                            </tr>
                                        @endif
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @if (!empty($due) && $due > 0)
                    <div class="{{ VC::MD_FD }}" id="paymentModal" tabindex="-1" role="dialog"
                        aria-labelledby="paymentModalLabel" aria-hidden="true">
                        <div class="{{ VC::MDL_DLG }} modal-lg" role="document">
                            <div class="{{ VC::MDL_CTT }}">
                                <div class="{{ VC::MDL_HDR }}">
                                    <h5 class="{{ VC::MDL_TTL }}" id="paymentModalLabel">{{ __('Add Payment') }}</h5>
                                    <button type="button" class="{{ VC::BT_CL }}" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="{{ VC::CD_BGN_BX }}">
                                        <section class="nav-tabs p-2">
                                            @if (!empty($company_payment_setting) &&
                                                ((isset($company_payment_setting['is_stripe_enabled']) && $company_payment_setting['is_stripe_enabled'] == 'on') ||
                                                    (isset($company_payment_setting['is_bank_transfer_enabled']) &&
                                                        $company_payment_setting['is_bank_transfer_enabled'] == 'on') ||
                                                    (isset($company_payment_setting['is_paypal_enabled']) &&
                                                        $company_payment_setting['is_paypal_enabled'] == 'on') ||
                                                    (isset($company_payment_setting['is_paystack_enabled']) &&
                                                        $company_payment_setting['is_paystack_enabled'] == 'on') ||
                                                    (isset($company_payment_setting['is_flutterwave_enabled']) &&
                                                        $company_payment_setting['is_flutterwave_enabled'] == 'on') ||
                                                    (isset($company_payment_setting['is_razorpay_enabled']) &&
                                                        $company_payment_setting['is_razorpay_enabled'] == 'on') ||
                                                    (isset($company_payment_setting['is_mercado_enabled']) &&
                                                        $company_payment_setting['is_mercado_enabled'] == 'on') ||
                                                    (isset($company_payment_setting['is_paytm_enabled']) && $company_payment_setting['is_paytm_enabled'] == 'on') ||
                                                    (isset($company_payment_setting['is_mollie_enabled']) &&
                                                        $company_payment_setting['is_mollie_enabled'] == 'on') ||
                                                    (isset($company_payment_setting['is_skrill_enabled']) &&
                                                        $company_payment_setting['is_skrill_enabled'] == 'on') ||
                                                    (isset($company_payment_setting['is_coingate_enabled']) &&
                                                        $company_payment_setting['is_coingate_enabled'] == 'on') ||
                                                    (isset($company_payment_setting['is_paymentwall_enabled']) &&
                                                        $company_payment_setting['is_paymentwall_enabled'] == 'on') ||
                                                    (isset($company_payment_setting['is_toyyibpay_enabled']) &&
                                                        $company_payment_setting['is_toyyibpay_enabled'] == 'on') ||
                                                    (isset($company_payment_setting['is_payfast_enabled']) &&
                                                        $company_payment_setting['is_payfast_enabled'] == 'on') ||
                                                    (isset($company_payment_setting['is_iyzipay_enabled']) &&
                                                        $company_payment_setting['is_iyzipay_enabled'] == 'on') ||
                                                    (isset($company_payment_setting['is_sspay_enabled']) && $company_payment_setting['is_sspay_enabled'] == 'on') ||
                                                    (isset($company_payment_setting['is_paytab_enabled']) &&
                                                        $company_payment_setting['is_paytab_enabled'] == 'on') ||
                                                    (isset($company_payment_setting['is_benefit_enabled']) &&
                                                        $company_payment_setting['is_benefit_enabled'] == 'on') ||
                                                    (isset($company_payment_setting['is_cashfree_enabled']) &&
                                                        $company_payment_setting['is_cashfree_enabled'] == 'on') ||
                                                    (isset($company_payment_setting['is_aamarpay_enabled']) &&
                                                        $company_payment_setting['is_aamarpay_enabled'] == 'on') ||
                                                    (isset($company_payment_setting['is_paytr_enabled']) && $company_payment_setting['is_paytr_enabled'] == 'on') ||
                                                    (isset($company_payment_setting['is_yookassa_enabled']) &&
                                                        $company_payment_setting['is_yookassa_enabled'] == 'on') ||
                                                    (isset($company_payment_setting['is_midtrans_enabled']) &&
                                                        $company_payment_setting['is_midtrans_enabled'] == 'on') ||
                                                    (isset($company_payment_setting['is_xendit_enabled']) &&
                                                        $company_payment_setting['is_xendit_enabled'] == 'on')))
                                                <ul class="{{ VC::NAV_PL }} {{ VC::MB3 }}" role="tablist">
                                                    @if ($company_payment_setting['is_bank_transfer_enabled'] == 'on' && !empty($company_payment_setting['bank_details']))
                                                        <li class="{{ VC::NV_IT }} {{ VC::MB2 }}">
                                                            <a class="{{ VC::BT_OUTPM_SM }} me-1 active"
                                                                data-bs-toggle="tab" href="#bank-transfer-payment"
                                                                role="tab" aria-controls="bank"
                                                                aria-selected="true">{{ __('Bank Transfer') }}</a>
                                                        </li>
                                                    @endif
                                                    @if (
                                                        $company_payment_setting['is_stripe_enabled'] == 'on' &&
                                                            !empty($company_payment_setting['stripe_key']) &&
                                                            !empty($company_payment_setting['stripe_secret']))
                                                        <li class="{{ VC::NV_IT }} {{ VC::MB2 }}">
                                                            <a class="{{ VC::BT_OUTPM_SM }} me-1"
                                                                data-bs-toggle="tab" href="#stripe-payment" role="tab"
                                                                aria-controls="stripe"
                                                                aria-selected="true">{{ __('Stripe') }}</a>
                                                        </li>
                                                    @endif
                                                    @if (
                                                        $company_payment_setting['is_paypal_enabled'] == 'on' &&
                                                            !empty($company_payment_setting['paypal_client_id']) &&
                                                            !empty($company_payment_setting['paypal_secret_key']))
                                                        <li class="{{ VC::NV_IT }} {{ VC::MB2 }}">
                                                            <a class="{{ VC::BT_OUTPM_SM }} me-1 ml-1"
                                                                data-bs-toggle="tab" href="#paypal-payment" role="tab"
                                                                aria-controls="paypal"
                                                                aria-selected="false">{{ __('Paypal') }}</a>
                                                        </li>
                                                    @endif
                                                    @if (
                                                        $company_payment_setting['is_paystack_enabled'] == 'on' &&
                                                            !empty($company_payment_setting['paystack_public_key']) &&
                                                            !empty($company_payment_setting['paystack_secret_key']))
                                                        <li class="{{ VC::NV_IT }} {{ VC::MB2 }}">
                                                            <a class="{{ VC::BT_OUTPM_SM }} me-1 ml-1"
                                                                data-bs-toggle="tab" href="#paystack-payment" role="tab"
                                                                aria-controls="paystack"
                                                                aria-selected="false">{{ __('Paystack') }}</a>
                                                        </li>
                                                    @endif
                                                    @if (isset($company_payment_setting['is_flutterwave_enabled']) &&
                                                            $company_payment_setting['is_flutterwave_enabled'] == 'on')
                                                        <li class="{{ VC::NV_IT }} {{ VC::MB2 }}">
                                                            <a class="{{ VC::BT_OUTPM_SM }} me-1 ml-1"
                                                                data-bs-toggle="tab" href="#flutterwave-payment"
                                                                role="tab" aria-controls="flutterwave"
                                                                aria-selected="false">{{ __('Flutterwave') }}</a>
                                                        </li>
                                                    @endif
                                                    @if (isset($company_payment_setting['is_razorpay_enabled']) && $company_payment_setting['is_razorpay_enabled'] == 'on')
                                                        <li class="{{ VC::NV_IT }} {{ VC::MB2 }}">
                                                            <a class="{{ VC::BT_OUTPM_SM }} me-1 ml-1"
                                                                data-bs-toggle="tab" href="#razorpay-payment" role="tab"
                                                                aria-controls="razorpay"
                                                                aria-selected="false">{{ __('Razorpay') }}</a>
                                                        </li>
                                                    @endif
                                                    @if (isset($company_payment_setting['is_mercado_enabled']) && $company_payment_setting['is_mercado_enabled'] == 'on')
                                                        <li class="{{ VC::NV_IT }} {{ VC::MB2 }}">
                                                            <a class="{{ VC::BT_OUTPM_SM }} me-1 ml-1"
                                                                data-bs-toggle="tab" href="#mercado-payment" role="tab"
                                                                aria-controls="mercado"
                                                                aria-selected="false">{{ __('Mercado') }}</a>
                                                        </li>
                                                    @endif
                                                    @if (isset($company_payment_setting['is_paytm_enabled']) && $company_payment_setting['is_paytm_enabled'] == 'on')
                                                        <li class="{{ VC::NV_IT }} {{ VC::MB2 }}">
                                                            <a class="{{ VC::BT_OUTPM_SM }} me-1 ml-1"
                                                                data-bs-toggle="tab" href="#paytm-payment" role="tab"
                                                                aria-controls="paytm"
                                                                aria-selected="false">{{ __('Paytm') }}</a>
                                                        </li>
                                                    @endif
                                                    @if (isset($company_payment_setting['is_mollie_enabled']) && $company_payment_setting['is_mollie_enabled'] == 'on')
                                                        <li class="{{ VC::NV_IT }} {{ VC::MB2 }}">
                                                            <a class="{{ VC::BT_OUTPM_SM }} me-1 ml-1"
                                                                data-bs-toggle="tab" href="#mollie-payment" role="tab"
                                                                aria-controls="mollie"
                                                                aria-selected="false">{{ __('Mollie') }}</a>
                                                        </li>
                                                    @endif
                                                    @if (isset($company_payment_setting['is_skrill_enabled']) && $company_payment_setting['is_skrill_enabled'] == 'on')
                                                        <li class="{{ VC::NV_IT }} {{ VC::MB2 }}">
                                                            <a class="{{ VC::BT_OUTPM_SM }} me-1 ml-1"
                                                                data-bs-toggle="tab" href="#skrill-payment" role="tab"
                                                                aria-controls="skrill"
                                                                aria-selected="false">{{ __('Skrill') }}</a>
                                                        </li>
                                                    @endif
                                                    @if (isset($company_payment_setting['is_coingate_enabled']) && $company_payment_setting['is_coingate_enabled'] == 'on')
                                                        <li class="{{ VC::NV_IT }} {{ VC::MB2 }}">
                                                            <a class="{{ VC::BT_OUTPM_SM }} me-1 ml-1"
                                                                data-bs-toggle="tab" href="#coingate-payment" role="tab"
                                                                aria-controls="coingate"
                                                                aria-selected="false">{{ __('Coingate') }}</a>
                                                        </li>
                                                    @endif
                                                    @if (
                                                        $company_payment_setting['is_paymentwall_enabled'] == 'on' &&
                                                            !empty($company_payment_setting['paymentwall_public_key']) &&
                                                            !empty($company_payment_setting['paymentwall_private_key']))
                                                        <li class="{{ VC::NV_IT }} {{ VC::MB2 }}">
                                                            <a class="{{ VC::BT_OUTPM_SM }} me-1 ml-1"
                                                                data-bs-toggle="tab" href="#paymentwall-payment"
                                                                role="tab" aria-controls="paymentwall"
                                                                aria-selected="false">{{ __('PaymentWall') }}</a>
                                                        </li>
                                                    @endif
                                                    @if (isset($company_payment_setting['is_toyyibpay_enabled']) && $company_payment_setting['is_toyyibpay_enabled'] == 'on')
                                                        <li class="{{ VC::NV_IT }} {{ VC::MB2 }}">
                                                            <a class="{{ VC::BT_OUTPM_SM }} me-1 ml-1"
                                                                data-bs-toggle="tab" href="#toyyibpay-payment" role="tab"
                                                                aria-controls="toyyibpay"
                                                                aria-selected="false">{{ __('Toyyibpay') }}</a>
                                                        </li>
                                                    @endif
                                                    @if (isset($company_payment_setting['is_payfast_enabled']) && $company_payment_setting['is_payfast_enabled'] == 'on')
                                                        <li class="{{ VC::NV_IT }} {{ VC::MB2 }}">
                                                            <a class="{{ VC::BT_OUTPM_SM }} me-1 ml-1"
                                                                id="payfast-tab-trigger" data-bs-toggle="tab"
                                                                href="#payfast-payment" role="tab"
                                                                aria-controls="payfast"
                                                                aria-selected="false">{{ __('PayFast') }}</a>
                                                        </li>
                                                    @endif
                                                    @if (isset($company_payment_setting['is_iyzipay_enabled']) && $company_payment_setting['is_iyzipay_enabled'] == 'on')
                                                        <li class="{{ VC::NV_IT }} {{ VC::MB2 }}">
                                                            <a class="{{ VC::BT_OUTPM_SM }} me-1 ml-1"
                                                                data-bs-toggle="tab" href="#iyzipay-payment" role="tab"
                                                                aria-controls="iyzipay"
                                                                aria-selected="false">{{ __('Iyzipay') }}</a>
                                                        </li>
                                                    @endif
                                                    @if (isset($company_payment_setting['is_sspay_enabled']) && $company_payment_setting['is_sspay_enabled'] == 'on')
                                                        <li class="{{ VC::NV_IT }} {{ VC::MB2 }}">
                                                            <a class="{{ VC::BT_OUTPM_SM }} me-1 ml-1"
                                                                data-bs-toggle="tab" href="#sspay-payment" role="tab"
                                                                aria-controls="sspay"
                                                                aria-selected="false">{{ __('SSPay') }}</a>
                                                        </li>
                                                    @endif
                                                    @if (isset($company_payment_setting['is_paytab_enabled']) && $company_payment_setting['is_paytab_enabled'] == 'on')
                                                        <li class="{{ VC::NV_IT }} {{ VC::MB2 }}">
                                                            <a class="{{ VC::BT_OUTPM_SM }} me-1 ml-1"
                                                                data-bs-toggle="tab" href="#paytab-payment" role="tab"
                                                                aria-controls="paytab"
                                                                aria-selected="false">{{ __('PayTab') }}</a>
                                                        </li>
                                                    @endif
                                                    @if (isset($company_payment_setting['is_benefit_enabled']) && $company_payment_setting['is_benefit_enabled'] == 'on')
                                                        <li class="{{ VC::NV_IT }} {{ VC::MB2 }}">
                                                            <a class="{{ VC::BT_OUTPM_SM }} me-1 ml-1"
                                                                data-bs-toggle="tab" href="#benefit-payment" role="tab"
                                                                aria-controls="benefit"
                                                                aria-selected="false">{{ __('Benefit') }}</a>
                                                        </li>
                                                    @endif
                                                    @if (isset($company_payment_setting['is_cashfree_enabled']) && $company_payment_setting['is_cashfree_enabled'] == 'on')
                                                        <li class="{{ VC::NV_IT }} {{ VC::MB2 }}">
                                                            <a class="{{ VC::BT_OUTPM_SM }} me-1 ml-1"
                                                                data-bs-toggle="tab" href="#cashfree-payment" role="tab"
                                                                aria-controls="cashfree"
                                                                aria-selected="false">{{ __('Cashfree') }}</a>
                                                        </li>
                                                    @endif
                                                    @if (isset($company_payment_setting['is_aamarpay_enabled']) && $company_payment_setting['is_aamarpay_enabled'] == 'on')
                                                        <li class="{{ VC::NV_IT }} {{ VC::MB2 }}">
                                                            <a class="{{ VC::BT_OUTPM_SM }} me-1 ml-1"
                                                                data-bs-toggle="tab" href="#aamarpay-payment" role="tab"
                                                                aria-controls="aamarpay"
                                                                aria-selected="false">{{ __('AamarPay') }}</a>
                                                        </li>
                                                    @endif
                                                    @if (isset($company_payment_setting['is_paytr_enabled']) && $company_payment_setting['is_paytr_enabled'] == 'on')
                                                        <li class="{{ VC::NV_IT }} {{ VC::MB2 }}">
                                                            <a class="{{ VC::BT_OUTPM_SM }} me-1 ml-1"
                                                                data-bs-toggle="tab" href="#paytr-payment" role="tab"
                                                                aria-controls="paytr"
                                                                aria-selected="false">{{ __('PayTR') }}</a>
                                                        </li>
                                                    @endif
                                                    @if (isset($company_payment_setting['is_yookassa_enabled']) && $company_payment_setting['is_yookassa_enabled'] == 'on')
                                                        <li class="{{ VC::NV_IT }} {{ VC::MB2 }}">
                                                            <a class="{{ VC::BT_OUTPM_SM }} me-1 ml-1"
                                                                data-bs-toggle="tab" href="#yookassa-payment" role="tab"
                                                                aria-controls="yookassa"
                                                                aria-selected="false">{{ __('Yookassa') }}</a>
                                                        </li>
                                                    @endif
                                                    @if (isset($company_payment_setting['is_midtrans_enabled']) && $company_payment_setting['is_midtrans_enabled'] == 'on')
                                                        <li class="{{ VC::NV_IT }} {{ VC::MB2 }}">
                                                            <a class="{{ VC::BT_OUTPM_SM }} me-1 ml-1"
                                                                data-bs-toggle="tab" href="#midtrans-payment" role="tab"
                                                                aria-controls="midtrans"
                                                                aria-selected="false">{{ __('Midtrans') }}</a>
                                                        </li>
                                                    @endif
                                                    @if (isset($company_payment_setting['is_xendit_enabled']) && $company_payment_setting['is_xendit_enabled'] == 'on')
                                                        <li class="{{ VC::NV_IT }} {{ VC::MB2 }}">
                                                            <a class="{{ VC::BT_OUTPM_SM }} me-1 ml-1"
                                                                data-bs-toggle="tab" href="#xendit-payment" role="tab"
                                                                aria-controls="xendit"
                                                                aria-selected="false">{{ __('Xendit') }}</a>
                                                        </li>
                                                    @endif
                                                </ul>
                                            @endif
                                            <div class="tab-content">
                                                @if (
                                                    !empty($company_payment_setting) &&
                                                        ($company_payment_setting['is_bank_transfer_enabled'] == 'on' &&
                                                            !empty($company_payment_setting['bank_details'])))
                                                    <div class="tab-pane fade active show" id="bank-transfer-payment" role="tabpanel" aria-labelledby="bank-transfer-payment">
                                                        @php
                                                            try {
                                                                $bankRouteName = ViewsConstants::CST.'.pay.with.bank';
                                                                $bankPayRoute  = Route::has($bankRouteName)
                                                                    ? route($bankRouteName)
                                                                    : '#';
                                                                $bankPayFormId    = 'bankPaymentForm';
                                                                $bankGuardMsg  = Utility::fetchLinkMessage(
                                                                    $lang,
                                                                    ViewsConstants::CST,
                                                                    'pay_with_bank_route_unavailable'
                                                                ) ?? 'Payment route is unavailable. Please contact technical support or your domain administrator.';
                                                            } catch (\Throwable $e) {
                                                                \Log::error('invoices/customer_invoice — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                            }
@endphp
                                                        <form
                                                            id="{{ $bankPayFormId }}"
                                                            class="w3-container w3-display-middle w3-card-4"
                                                            method="POST"
                                                            enctype="multipart/form-data"
                                                            action="{{ $bankPayRoute }}"
                                                            data-url="{{ $bankPayRoute }}"
                                                            data-guard-msg="{{ base64_encode($bankGuardMsg) }}"
                                                        >
                                                            @csrf
                                                            <input type="hidden" name="invoice_id" value="{{ Crypt::encrypt($invoice->id) }}">
                                                            <div class="row">
                                                                <div class="{{ VC::C6 }}">
                                                                    <div class="custom-radio">
                                                                        <label class="font-16 font-bold">{{ __('Bank Details') }} :</label>
                                                                    </div>
                                                                    <p class="{{ VC::MB0 }} pt-1 {{ VC::TXSM }}">
                                                                        {!! !empty($company_payment_setting['bank_details']) ?: __('Failed to retrieve data for Bank Details') !!}
                                                                    </p>
                                                                </div>
                                                                <div class="{{ VC::C6 }}">
                                                                    {{ Form::label('payment_receipt', __('Payment Receipt'), ['class' => "{{ VC::FM_LB }}"]) }}
                                                                    <div class="choose-file {{ VC::FM_G }}">
                                                                        <input type="file" name="payment_receipt" id="image" class="{{ VC::FM_CT }}">
                                                                        <p class="upload_file"></p>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="row {{ VC::MT2 }}">
                                                                <div class="{{ VC::FM_GCB12 }}">
                                                                    <label for="amount">{{ __('Amount') }}</label>
                                                                    <div class="input-group">
                                                                        <span class="input-group-prepend">
                                                                            <span class="{{ VC::INP_GP_TXT }}">{{ $siteCurrency }}</span>
                                                                        </span>
                                                                        <input
                                                                            class="{{ VC::FM_CT }}"
                                                                            required="required"
                                                                            min="0"
                                                                            name="amount"
                                                                            type="number"
                                                                            value="{{ $due }}"
                                                                            step="0.01"
                                                                            max="{{ $due }}"
                                                                            id="amount"
                                                                        >
                                                                        @error('amount')
                                                                            <span class="invalid-amount" role="alert">
                                                                                <strong>{{ $message }}</strong>
                                                                            </span>
                                                                        @enderror
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="{{ VC::FM_GT3 }}">
                                                                <button class="{{ VC::BT_PRM }}" type="submit">{{ __('Make Payment') }}</button>
                                                            </div>
                                                        </form>
                                                        @push(StacksConstants::ADM_SCR_PG)
                                                            <script defer src="{{ asset('assets/js/routes/invoices/customers/bankPayment.js') }}"></script>
                                                        @endpush
                                                    </div>
                                                @endif
                                                @if (
                                                    !empty($company_payment_setting) &&
                                                        ($company_payment_setting['is_stripe_enabled'] == 'on' &&
                                                            !empty($company_payment_setting['stripe_key']) &&
                                                            !empty($company_payment_setting['stripe_secret'])))
                                                    <div class="tab-pane fade" id="stripe-payment" role="tabpanel" aria-labelledby="stripe-payment">
                                                        @php
                                                            try {
                                                                $stripeRouteName   = ViewsConstants::CST.'.payment';
                                                                $stripePayRoute    = Route::has($stripeRouteName)
                                                                    ? route($stripeRouteName, $invoice->id)
                                                                    : '#';
                                                                $stripeGuardMsg    = Utility::fetchLinkMessage(
                                                                    $lang,
                                                                    ViewsConstants::CST,
                                                                    'payment_with_stripe_route_unavailable'
                                                                ) ?? 'Payment route for Stripe is unavailable. Please contact technical support or your domain administrator.';
                                                            } catch (\Throwable $e) {
                                                                \Log::error('invoices/customer_invoice — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                            }
@endphp
                                                        <form
                                                            id="stripe-payment-form"
                                                            class="require-validation"
                                                            method="POST"
                                                            action="{{ route($stripePayRoute) }}"
                                                            data-url="{{ $stripePayRoute }}"
                                                            data-guard-msg="{{ base64_encode($stripeGuardMsg) }}"
                                                        >
                                                            @csrf
                                                            <div class="row">
                                                                <div class="col-sm-8">
                                                                    <div class="custom-radio">
                                                                        <label class="font-16 font-weight-bold">{{ __('Credit / Debit Card') }}</label>
                                                                    </div>
                                                                    <p class="{{ VC::MB0 }} pt-1 {{ VC::TXSM }}">
                                                                        {{ __('Safe money transfer using your bank account. We support Mastercard, Visa, Discover and American express.') }}
                                                                    </p>
                                                                </div>
                                                            </div>

                                                            <div class="row">
                                                                <div class="{{ VC::CM12 }}">
                                                                    <div class="{{ VC::FM_G }}">
                                                                        <label for="card-name-on">{{ __('Name on card') }}</label>
                                                                        <input
                                                                            type="text"
                                                                            name="name"
                                                                            id="card-name-on"
                                                                            class="{{ VC::FM_CT }} required"
                                                                        >
                                                                    </div>
                                                                </div>
                                                                <div class="{{ VC::CM12 }}">
                                                                    <div id="card-element">
                                                                        <div id="card-errors" role="alert"></div>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <div class="row">
                                                                <div class="{{ VC::FM_GCB12 }}">
                                                                    <br>
                                                                    <label for="amount">{{ __('Amount') }}</label>
                                                                    <div class="input-group">
                                                                        <span class="input-group-prepend">
                                                                            <span class="{{ VC::INP_GP_TXT }}">{{ $siteCurrency }}</span>
                                                                        </span>
                                                                        <input
                                                                            id="amount"
                                                                            class="{{ VC::FM_CT }}"
                                                                            required="required"
                                                                            min="0"
                                                                            name="amount"
                                                                            type="number"
                                                                            value="{{ $due }}"
                                                                            step="0.01"
                                                                            max="{{ $due }}"
                                                                        >
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <div class="row">
                                                                <div class="{{ VC::C12 }}">
                                                                    <div class="error" style="display: none;">
                                                                        <div class="alert-danger alert">
                                                                            {{ __('Please correct the errors and try again.') }}
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <div class="{{ VC::FM_GT3 }}">
                                                                <button class="{{ VC::BT_PRM }}" type="submit">{{ __('Make Payment') }}</button>
                                                            </div>
                                                        </form>
                                                        @push(StacksConstants::ADM_SCR_PG)
                                                            <script defer src="{{ asset('assets/js/routes/invoices/customers/stripePayment.js') }}">
                                                            </script>
                                                        @endpush
                                                    </div>
                                                @endif

                                                @if (
                                                    !empty($company_payment_setting) &&
                                                        ($company_payment_setting['is_paypal_enabled'] == 'on' &&
                                                            !empty($company_payment_setting['paypal_client_id']) &&
                                                            !empty($company_payment_setting['paypal_secret_key'])))
                                                    <div class="tab-pane fade" id="paypal-payment" role="tabpanel" aria-labelledby="paypal-payment">
                                                        @php
                                                            try {
                                                                $paypalRouteName       = ViewsConstants::CST . '.pay.with.paypal';
                                                                $paypalActionUrl       = Route::has($paypalRouteName)
                                                                    ? route($paypalRouteName, $invoice->id)
                                                                    : '#';
                                                                $payPalFormId                = 'paypalPaymentForm_' . $invoice->id;
                                                                $paypalGuardMsg        = Utility::fetchLinkMessage(
                                                                    $lang,
                                                                    ViewsConstants::CST,
                                                                    'payment_with_paypal_route_unavailable'
                                                                ) ?? 'Payment with PayPal route is unavailable. Please contact technical support or your domain administrator.';
                                                            } catch (\Throwable $e) {
                                                                \Log::error('invoices/customer_invoice — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                            }
@endphp
                                                        <form
                                                            class="w3-container w3-display-middle w3-card-4"
                                                            method="POST"
                                                            id="{{ $payPalFormId }}"
                                                            action="{{ $paypalActionUrl }}"
                                                            data-url="{{ $paypalActionUrl }}"
                                                            data-guard-msg="{{ base64_encode($paypalGuardMsg) }}"
                                                        >
                                                            @csrf
                                                            <div class="row">
                                                                <div class="{{ VC::FM_GCB12 }}">
                                                                    <label for="amount">{{ __('Amount') }}</label>
                                                                    <div class="input-group">
                                                                        <span class="input-group-prepend">
                                                                            <span class="{{ VC::INP_GP_TXT }}">{{ $siteCurrency }}</span>
                                                                        </span>
                                                                        <input
                                                                            id="amount"
                                                                            class="{{ VC::FM_CT }}"
                                                                            required="required"
                                                                            name="amount"
                                                                            type="number"
                                                                            min="0"
                                                                            step="0.01"
                                                                            max="{{ $due }}"
                                                                            value="{{ $due }}"
                                                                        >
                                                                        @error('amount')
                                                                            <span class="invalid-amount" role="alert">
                                                                                <strong>{{ $message }}</strong>
                                                                            </span>
                                                                        @enderror
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="{{ VC::FM_GT3 }}">
                                                                <button class="{{ VC::BT_PRM }}" name="submit" type="submit">
                                                                    {{ __('Make Payment') }}
                                                                </button>
                                                            </div>
                                                        </form>
                                                        @push(StacksConstants::ADM_SCR_PG)
                                                            <script defer>
                                                                (() => {
                                                                    const form = document.getElementById('{{ $payPalFormId }}');
                                                                    if (!form || form.getAttribute('data-listener-active') === 'true') return;
                                                                    form.setAttribute('data-listener-active', 'true');
                                                                    form.addEventListener('submit', event => {
                                                                        try {
                                                                            const url = form.getAttribute('data-url') ?? '#';
                                                                            if (url !== '#') return;
                                                                            event.preventDefault();
                                                                            const msg = form.getAttribute('data-guard-msg') ?? '# ERROR';
                                                                            (window.RouteGuard?.showToast || (m => alert(m)))(msg);
                                                                            form.setAttribute('data-failed-route', 'true');
                                                                        } catch (e) {}
                                                                    });
                                                                })();
                                                            </script>
                                                        @endpush
                                                    </div>
                                                @endif
                                                @if (isset($company_payment_setting['is_paystack_enabled']) &&
                                                        $company_payment_setting['is_paystack_enabled'] == 'on' &&
                                                        !empty($company_payment_setting['paystack_public_key']) &&
                                                        !empty($company_payment_setting['paystack_secret_key']))
                                                    <div class="tab-pane fade" id="paystack-payment" role="tabpanel" aria-labelledby="paypal-payment">
                                                        @php
                                                            try {
                                                                $routeName          = ViewsConstants::CST . '.pay.with.paystack';
                                                                $paystackActionUrl  = Route::has($routeName)
                                                                    ? route($routeName, $invoice->id)
                                                                    : '#';
                                                                $payStackFormId             = 'paystack-payment-form';
                                                                $guardMsgKey        = 'payment_with_paystack_route_unavailable';
                                                                $paystackGuardMsg   = Utility::fetchLinkMessage(
                                                                    $lang,
                                                                    ViewsConstants::CST,
                                                                    $guardMsgKey
                                                                ) ?? 'Payment with Paystack route is unavailable. Please contact technical support or your domain administrator.';
                                                            } catch (\Throwable $e) {
                                                                \Log::error('invoices/customer_invoice — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                            }
@endphp
                                                        <form
                                                            id="{{ $payStackFormId }}"
                                                            class="w3-container w3-display-middle w3-card-4"
                                                            method="POST"
                                                            action="{{ $paystackActionUrl }}"
                                                            data-url="{{ $paystackActionUrl }}"
                                                            data-guard-msg="{{ base64_encode($paystackGuardMsg) }}"
                                                        >
                                                            @csrf
                                                            <input type="hidden" name="invoice_id" value="{{ Crypt::encrypt($invoice->id) }}">

                                                            <div class="{{ VC::FM_GCB12 }}">
                                                                <label for="amount">{{ __('Amount') }}</label>
                                                                <div class="input-group">
                                                                    <span class="input-group-prepend">
                                                                        <span class="{{ VC::INP_GP_TXT }}">{{ $siteCurrency }}</span>
                                                                    </span>
                                                                    <input
                                                                        id="amount"
                                                                        class="{{ VC::FM_CT }}"
                                                                        required="required"
                                                                        name="amount"
                                                                        type="number"
                                                                        value="{{ $due }}"
                                                                        min="0"
                                                                        step="0.01"
                                                                        max="{{ $due }}"
                                                                    >
                                                                </div>
                                                            </div>

                                                            <div class="{{ VC::FM_GT3 }}">
                                                                <button
                                                                    id="pay_with_paystack"
                                                                    class="{{ VC::BT_PRM }}"
                                                                    type="button"
                                                                >{{ __('Make Payment') }}</button>
                                                            </div>
                                                        </form>
                                                        @push(StacksConstants::ADM_SCR_PG)
                                                            <script defer>
                                                                (() => {
                                                                    const form = document.getElementById('{{ $payStackFormId }}');
                                                                    const btn  = document.getElementById('pay_with_paystack');
                                                                    if (!form || !btn
                                                                        || form.getAttribute('data-listener-active') === 'true'
                                                                        || btn.getAttribute('data-listener-active') === 'true'
                                                                    ) return;
                                                                    form.setAttribute('data-listener-active', 'true');
                                                                    btn.setAttribute('data-listener-active', 'true');

                                                                    btn.addEventListener('click', event => {
                                                                        try {
                                                                            const url = form.getAttribute('data-url') ?? '#';
                                                                            if (url !== '#') {
                                                                                form.submit();
                                                                                return;
                                                                            }
                                                                            event.preventDefault();
                                                                            const msg = form.getAttribute('data-guard-msg') ?? '# ERROR';
                                                                            (window.RouteGuard?.showToast || (m => alert(m)))(msg);
                                                                            form.setAttribute('data-failed-route', 'true');
                                                                        } catch (e) {}
                                                                    });
                                                                })();
                                                            </script>
                                                        @endpush
                                                    </div>
                                                @endif
                                                @if (isset($company_payment_setting['is_flutterwave_enabled']) &&
                                                        $company_payment_setting['is_flutterwave_enabled'] == 'on' &&
                                                        !empty($company_payment_setting['paystack_public_key']) &&
                                                        !empty($company_payment_setting['paystack_secret_key']))
                                                    <div class="tab-pane fade" id="flutterwave-payment" role="tabpanel" aria-labelledby="paypal-payment">
                                                        @php
                                                            try {
                                                                $flutterwaveName       = ViewsConstants::CST . '.pay.with.flutterwave';
                                                                $flutterwaveRoute      = Route::has($flutterwaveName)
                                                                    ? route($flutterwaveName)
                                                                    : '#';
                                                                $fwFormId               = 'flutterwavePaymentForm_' . $invoice->id;
                                                                $flutterwaveGuardMsg   = Utility::fetchLinkMessage(
                                                                    $lang,
                                                                    ViewsConstants::CST,
                                                                    'payment_with_flutterwave_route_unavailable'
                                                                ) ?? 'Payment with flutterwave route is unavailable. Please contact technical support or your domain administrator.';
                                                            } catch (\Throwable $e) {
                                                                \Log::error('invoices/customer_invoice — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                            }
@endphp
                                                        <form
                                                            role="form"
                                                            action="{{ $flutterwaveRoute }}"
                                                            method="post"
                                                            class="require-validation"
                                                            id="{{ $fwFormId }}"
                                                            data-url="{{ $flutterwaveRoute }}"
                                                            data-guard-msg="{{ base64_encode($flutterwaveGuardMsg) }}"
                                                        >
                                                            @csrf
                                                            <input type="hidden" name="invoice_id" value="{{ Crypt::encrypt($invoice->id) }}">

                                                            <div class="{{ VC::FM_GCB12 }}">
                                                                <label for="amount">{{ __('Amount') }}</label>
                                                                <div class="input-group">
                                                                    <span class="input-group-prepend">
                                                                        <span class="{{ VC::INP_GP_TXT }}">{{ $siteCurrency }}</span>
                                                                    </span>
                                                                    <input
                                                                        id="amount"
                                                                        class="{{ VC::FM_CT }}"
                                                                        required="required"
                                                                        name="amount"
                                                                        type="number"
                                                                        value="{{ $due }}"
                                                                        min="0"
                                                                        step="0.01"
                                                                        max="{{ $due }}"
                                                                    >
                                                                </div>
                                                            </div>

                                                            <div class="{{ VC::FM_GT3 }}">
                                                                <button
                                                                    id="pay_with_flutterwave"
                                                                    class="{{ VC::BT_PRM }}"
                                                                    type="button"
                                                                >{{ __('Make Payment') }}</button>
                                                            </div>
                                                        </form>
                                                        @push(StacksConstants::ADM_SCR_PG)
                                                            <script defer>
                                                                (() => {
                                                                    const form = document.getElementById('{{ $fwFormId }}');
                                                                    const btn  = document.getElementById('pay_with_flutterwave');
                                                                    if (!form || !btn) return;
                                                                    if (form.getAttribute('data-listener-active') === 'true'
                                                                        || btn.getAttribute('data-listener-active') === 'true'
                                                                    ) return;

                                                                    form.setAttribute('data-listener-active', 'true');
                                                                    btn.setAttribute('data-listener-active', 'true');

                                                                    btn.addEventListener('click', event => {
                                                                        try {
                                                                            const url = form.getAttribute('data-url') ?? '#';
                                                                            if (url !== '#') {
                                                                                form.submit();
                                                                                return;
                                                                            }
                                                                            event.preventDefault();
                                                                            const msg = form.getAttribute('data-guard-msg') ?? '# ERROR';
                                                                            (window.RouteGuard?.showToast || (m => alert(m)))(msg);
                                                                            form.setAttribute('data-failed-route', 'true');
                                                                        } catch {}
                                                                    });
                                                                })();
                                                            </script>
                                                        @endpush
                                                    </div>
                                                @endif
                                                @if (isset($company_payment_setting['is_razorpay_enabled']) && $company_payment_setting['is_razorpay_enabled'] == 'on')
                                                    <div class="tab-pane fade" id="razorpay-payment" role="tabpanel" aria-labelledby="paypal-payment">
                                                        @php
                                                            try {
                                                                $routeName            = ViewsConstants::CST . '.pay.with.razorpay';
                                                                $razorpayActionUrl    = Route::has($routeName)
                                                                    ? route($routeName)
                                                                    : '#';
                                                                $rzFormId               = 'razorpay-payment-form';
                                                                $razorpayGuardMsg     = Utility::fetchLinkMessage(
                                                                    $lang,
                                                                    ViewsConstants::CST,
                                                                    'payment_with_razorpay_route_unavailable'
                                                                ) ?? 'Payment with Razorpay route is unavailable. Please contact technical support or your domain administrator.';
                                                            } catch (\Throwable $e) {
                                                                \Log::error('invoices/customer_invoice — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                            }
@endphp
                                                        <form
                                                            role="form"
                                                            id="{{ $rzFormId }}"
                                                            class="require-validation"
                                                            method="POST"
                                                            action="{{ $razorpayActionUrl }}"
                                                            data-url="{{ $razorpayActionUrl }}"
                                                            data-guard-msg="{{ base64_encode($razorpayGuardMsg) }}"
                                                        >
                                                            @csrf
                                                            <input type="hidden" name="invoice_id" value="{{ Crypt::encrypt($invoice->id) }}">

                                                            <div class="{{ VC::FM_GCB12 }}">
                                                                <label for="amount">{{ __('Amount') }}</label>
                                                                <div class="input-group">
                                                                    <span class="input-group-prepend">
                                                                        <span class="{{ VC::INP_GP_TXT }}">{{ $siteCurrency }}</span>
                                                                    </span>
                                                                    <input
                                                                        id="amount"
                                                                        class="{{ VC::FM_CT }}"
                                                                        required="required"
                                                                        name="amount"
                                                                        type="number"
                                                                        value="{{ $due }}"
                                                                        min="0"
                                                                        step="0.01"
                                                                        max="{{ $due }}"
                                                                    >
                                                                </div>
                                                            </div>

                                                            <div class="{{ VC::FM_GT3 }}">
                                                                <button
                                                                    id="pay_with_razorpay"
                                                                    class="{{ VC::BT_PRM }}"
                                                                    type="button"
                                                                >{{ __('Make Payment') }}</button>
                                                            </div>
                                                        </form>
                                                        @push(StacksConstants::ADM_SCR_PG)
                                                            <script defer>
                                                                (() => {
                                                                    const form = document.getElementById('{{ $rzFormId }}');
                                                                    const btn  = document.getElementById('pay_with_razorpay');
                                                                    if (!form || !btn) return;
                                                                    if (
                                                                        form.getAttribute('data-listener-active') === 'true' ||
                                                                        btn.getAttribute('data-listener-active') === 'true'
                                                                    ) return;

                                                                    form.setAttribute('data-listener-active', 'true');
                                                                    btn.setAttribute('data-listener-active', 'true');

                                                                    btn.addEventListener('click', event => {
                                                                        try {
                                                                            const url = form.getAttribute('data-url') ?? '#';
                                                                            if (url !== '#') {
                                                                                form.submit();
                                                                                return;
                                                                            }
                                                                            event.preventDefault();
                                                                            const msg = form.getAttribute('data-guard-msg') ?? '# ERROR';
                                                                            (window.RouteGuard?.showToast || (m => alert(m)))(msg);
                                                                            form.setAttribute('data-failed-route', 'true');
                                                                        } catch {}
                                                                    });
                                                                })();
                                                            </script>
                                                        @endpush
                                                    </div>
                                                @endif

                                                @if (isset($company_payment_setting['is_mercado_enabled']) && $company_payment_setting['is_mercado_enabled'] == 'on')
                                                    <div class="tab-pane fade" id="mercado-payment" role="tabpanel" aria-labelledby="mercado-payment">
                                                        @php
                                                            try {
                                                                $mercadoRouteName         = ViewsConstants::CST . '.pay.with.mercado';
                                                                $mercadoActionUrl         = Route::has($mercadoRouteName)
                                                                    ? route($mercadoRouteName, $invoice->id)
                                                                    : '#';
                                                                $mercadoFormId            = 'mercadoPaymentForm_' . $invoice->id;
                                                                $mercadoGuardMsg          = Utility::fetchLinkMessage(
                                                                    $lang,
                                                                    ViewsConstants::CST,
                                                                    'payment_with_mercado_route_unavailable'
                                                                ) ?? 'Payment with Mercado route is unavailable. Please contact technical support or your domain administrator.';
                                                            } catch (\Throwable $e) {
                                                                \Log::error('invoices/customer_invoice — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                            }
@endphp
                                                        <form
                                                            role="form"
                                                            id="{{ $mercadoFormId }}"
                                                            class="require-validation"
                                                            method="post"
                                                            action="{{ $mercadoActionUrl }}"
                                                            data-url="{{ $mercadoActionUrl }}"
                                                            data-guard-msg="{{ base64_encode($mercadoGuardMsg) }}"
                                                        >
                                                            @csrf
                                                            <input type="hidden" name="invoice_id" value="{{ Crypt::encrypt($invoice->id) }}">

                                                            <div class="{{ VC::FM_GCB12 }}">
                                                                <label for="amount">{{ __('Amount') }}</label>
                                                                <div class="input-group">
                                                                    <span class="input-group-prepend">
                                                                        <span class="{{ VC::INP_GP_TXT }}">{{ $siteCurrency }}</span>
                                                                    </span>
                                                                    <input
                                                                        id="amount"
                                                                        class="{{ VC::FM_CT }}"
                                                                        required="required"
                                                                        name="amount"
                                                                        type="number"
                                                                        value="{{ $due }}"
                                                                        min="0"
                                                                        step="0.01"
                                                                        max="{{ $due }}"
                                                                    >
                                                                </div>
                                                            </div>

                                                            <div class="{{ VC::FM_GT3 }}">
                                                                <button
                                                                    id="pay_with_mercado"
                                                                    class="{{ VC::BT_PRM }}"
                                                                    type="submit"
                                                                >{{ __('Make Payment') }}</button>
                                                            </div>
                                                        </form>
                                                        @push(StacksConstants::ADM_SCR_PG)
                                                            <script defer>
                                                                (() => {
                                                                    const form = document.getElementById('{{ $mercadoFormId }}');
                                                                    if (!form || form.getAttribute('data-listener-active') === 'true') return;
                                                                    form.setAttribute('data-listener-active', 'true');
                                                                    form.addEventListener('submit', event => {
                                                                        try {
                                                                            const url = form.getAttribute('data-url') ?? '#';
                                                                            if (url !== '#') return;
                                                                            event.preventDefault();
                                                                            const msg = form.getAttribute('data-guard-msg') ?? '# ERROR';
                                                                            (window.RouteGuard?.showToast || (m => alert(m)))(msg);
                                                                            form.setAttribute('data-failed-route', 'true');
                                                                        } catch (e) {}
                                                                    });
                                                                })();
                                                            </script>
                                                        @endpush
                                                    </div>
                                                @endif
                                                @if (isset($company_payment_setting['is_paytm_enabled']) && $company_payment_setting['is_paytm_enabled'] == 'on')
                                                    <div class="tab-pane fade" id="paytm-payment" role="tabpanel" aria-labelledby="paytm-payment">
                                                        @php
                                                            try {
                                                                $paytmRouteName         = ViewsConstants::CST . '.pay.with.paytm';
                                                                $paytmActionUrl         = Route::has($paytmRouteName)
                                                                    ? route($paytmRouteName, $invoice->id)
                                                                    : '#';
                                                                $paytmFormId            = 'paytmPaymentForm_' . $invoice->id;
                                                                $paytmGuardMsg          = Utility::fetchLinkMessage(
                                                                    $lang,
                                                                    ViewsConstants::CST,
                                                                    'payment_with_paytm_route_unavailable'
                                                                ) ?? 'Payment with Paytm route is unavailable. Please contact technical support or your domain administrator.';
                                                            } catch (\Throwable $e) {
                                                                \Log::error('invoices/customer_invoice — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                            }
@endphp
                                                        <form
                                                            role="form"
                                                            id="{{ $paytmFormId }}"
                                                            class="require-validation"
                                                            method="post"
                                                            action="{{ $paytmActionUrl }}"
                                                            data-url="{{ $paytmActionUrl }}"
                                                            data-guard-msg="{{ base64_encode($paytmGuardMsg) }}"
                                                        >
                                                            @csrf
                                                            <input type="hidden" name="invoice_id" value="{{ Crypt::encrypt($invoice->id) }}">

                                                            <div class="{{ VC::FM_GCB12 }}">
                                                                <label for="amount">{{ __('Amount') }}</label>
                                                                <div class="input-group">
                                                                    <span class="input-group-prepend">
                                                                        <span class="{{ VC::INP_GP_TXT }}">{{ $siteCurrency }}</span>
                                                                    </span>
                                                                    <input
                                                                        id="amount"
                                                                        class="{{ VC::FM_CT }}"
                                                                        required="required"
                                                                        name="amount"
                                                                        type="number"
                                                                        value="{{ $due }}"
                                                                        min="0"
                                                                        step="0.01"
                                                                        max="{{ $due }}"
                                                                    >
                                                                </div>
                                                            </div>

                                                            <div class="{{ VC::CM12 }}">
                                                                <div class="{{ VC::FM_G }}">
                                                                    <label for="mobile" class="{{ VC::TX_DK }}">{{ __('Mobile Number') }}</label>
                                                                    <input
                                                                        type="text"
                                                                        id="mobile"
                                                                        name="mobile"
                                                                        class="{{ VC::FM_CT }} mobile"
                                                                        placeholder="{{ __('Enter Mobile Number') }}"
                                                                        required
                                                                    >
                                                                </div>
                                                            </div>

                                                            <div class="{{ VC::FM_GT3 }}">
                                                                <button
                                                                    id="pay_with_paytm_btn_{{ $invoice->id }}"
                                                                    class="{{ VC::BT_PRM }}"
                                                                    name="submit"
                                                                    type="submit"
                                                                >{{ __('Make Payment') }}</button>
                                                            </div>
                                                        </form>
                                                        @push(StacksConstants::ADM_SCR_PG)
                                                            <script defer>
                                                                (() => {
                                                                    const formEl = document.getElementById('{{ $paytmFormId }}');
                                                                    if (!formEl || formEl.getAttribute('data-listener-active') === 'true') return;
                                                                    formEl.setAttribute('data-listener-active', 'true');

                                                                    formEl.addEventListener('submit', event => {
                                                                        try {
                                                                            const url = formEl.getAttribute('data-url') ?? '#';
                                                                            if (url !== '#') return;
                                                                            event.preventDefault();
                                                                            const msg = formEl.getAttribute('data-guard-msg') ?? '# ERROR';
                                                                            (window.RouteGuard?.showToast || (m => alert(m)))(msg);
                                                                            formEl.setAttribute('data-failed-route', 'true');
                                                                        } catch {}
                                                                    });
                                                                })();
                                                            </script>
                                                        @endpush
                                                    </div>
                                                @endif

                                                @if (isset($company_payment_setting['is_mollie_enabled']) && $company_payment_setting['is_mollie_enabled'] == 'on')
                                                    <div class="tab-pane fade" id="mollie-payment" role="tabpanel" aria-labelledby="mollie-payment">
                                                        @php
                                                            try {
                                                                $mollieRouteName        = ViewsConstants::CST . '.pay.with.mollie';
                                                                $mollieRouteUrl         = Route::has($mollieRouteName)
                                                                    ? route($mollieRouteName, $invoice->id)
                                                                    : '#';
                                                                $mollieFormId           = 'molliePaymentForm_' . $invoice->id;
                                                                $mollieGuardMsg         = Utility::fetchLinkMessage(
                                                                    $lang,
                                                                    ViewsConstants::CST,
                                                                    'payment_with_mollie_route_unavailable'
                                                                ) ?? 'Payment with Mollie route is unavailable. Please contact technical support or your domain administrator.';
                                                            } catch (\Throwable $e) {
                                                                \Log::error('invoices/customer_invoice — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                            }
@endphp
                                                        <form
                                                            role="form"
                                                            id="{{ $mollieFormId }}"
                                                            class="require-validation"
                                                            method="post"
                                                            action="{{ $mollieRouteUrl }}"
                                                            data-url="{{ $mollieRouteUrl }}"
                                                            data-guard-msg="{{ base64_encode($mollieGuardMsg) }}"
                                                        >
                                                            @csrf
                                                            <input type="hidden" name="invoice_id" value="{{ Crypt::encrypt($invoice->id) }}">

                                                            <div class="{{ VC::FM_GCB12 }}">
                                                                <label for="amount">{{ __('Amount') }}</label>
                                                                <div class="input-group">
                                                                    <span class="input-group-prepend">
                                                                        <span class="{{ VC::INP_GP_TXT }}">{{ $siteCurrency }}</span>
                                                                    </span>
                                                                    <input
                                                                        id="amount"
                                                                        class="{{ VC::FM_CT }}"
                                                                        required
                                                                        name="amount"
                                                                        type="number"
                                                                        value="{{ $due }}"
                                                                        min="0"
                                                                        step="0.01"
                                                                        max="{{ $due }}"
                                                                    >
                                                                </div>
                                                            </div>

                                                            <div class="{{ VC::FM_GT3 }}">
                                                                <button
                                                                    id="pay_with_mollie"
                                                                    class="{{ VC::BT_PRM }}"
                                                                    type="submit"
                                                                >{{ __('Make Payment') }}</button>
                                                            </div>
                                                        </form>
                                                        @push(StacksConstants::ADM_SCR_PG)
                                                            <script defer>
                                                                (() => {
                                                                    const mollieForm = document.getElementById('{{ $mollieFormId }}');
                                                                    if (!mollieForm || mollieForm.getAttribute('data-listener-active') === 'true') return;
                                                                    mollieForm.setAttribute('data-listener-active', 'true');
                                                                    mollieForm.addEventListener('submit', event => {
                                                                        try {
                                                                            const url = mollieForm.getAttribute('data-url') ?? '#';
                                                                            if (url !== '#') return;
                                                                            event.preventDefault();
                                                                            const msg = mollieForm.getAttribute('data-guard-msg') ?? '# ERROR';
                                                                            (window.RouteGuard?.showToast || (m => alert(m)))(msg);
                                                                            mollieForm.setAttribute('data-failed-route', 'true');
                                                                        } catch {}
                                                                    });
                                                                })();
                                                            </script>
                                                        @endpush
                                                    </div>
                                                @endif

                                                @if (isset($company_payment_setting['is_skrill_enabled']) && $company_payment_setting['is_skrill_enabled'] == 'on')
                                                    <div class="tab-pane fade" id="skrill-payment" role="tabpanel" aria-labelledby="skrill-payment">
                                                        @php
                                                            try {
                                                                $skrillRouteName       = ViewsConstants::CST . '.pay.with.skrill';
                                                                $skrillActionUrl       = Route::has($skrillRouteName)
                                                                    ? route($skrillRouteName, $invoice->id)
                                                                    : '#';
                                                                $skrillFormId          = 'skrillPaymentForm_' . $invoice->id;
                                                                $skrillGuardMsg        = Utility::fetchLinkMessage(
                                                                    $lang,
                                                                    ViewsConstants::CST,
                                                                    'payment_with_skrill_route_unavailable'
                                                                ) ?? 'Payment with Skrill route is unavailable. Please contact technical support or your domain administrator.';
                                                            } catch (\Throwable $e) {
                                                                \Log::error('invoices/customer_invoice — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                            }
@endphp
                                                        <form
                                                            role="form"
                                                            id="{{ $skrillFormId }}"
                                                            class="require-validation"
                                                            method="post"
                                                            action="{{ $skrillActionUrl }}"
                                                            data-url="{{ $skrillActionUrl }}"
                                                            data-guard-msg="{{ base64_encode($skrillGuardMsg) }}"
                                                        >
                                                            @csrf
                                                            <input type="hidden" name="invoice_id" value="{{ Crypt::encrypt($invoice->id) }}">

                                                            <div class="{{ VC::FM_GCB12 }}">
                                                                <label for="amount">{{ __('Amount') }}</label>
                                                                <div class="input-group">
                                                                    <span class="input-group-prepend">
                                                                        <span class="{{ VC::INP_GP_TXT }}">{{ $siteCurrency }}</span>
                                                                    </span>
                                                                    <input
                                                                        id="amount"
                                                                        class="{{ VC::FM_CT }}"
                                                                        required="required"
                                                                        name="amount"
                                                                        type="number"
                                                                        value="{{ $due }}"
                                                                        min="0"
                                                                        step="0.01"
                                                                        max="{{ $due }}"
                                                                    >
                                                                </div>
                                                            </div>

                                                            @php
                                                                try {
                                                                    $skrill_data = [
                                                                        'transaction_id' => md5(date('Y-m-d') . strtotime('Y-m-d H:i:s') . 'user_id'),
                                                                        'user_id'        => 'user_id',
                                                                        'amount'         => 'amount',
                                                                        'currency'       => 'currency',
                                                                    ];
                                                                    session()->put('skrill_data', $skrill_data);
                                                                } catch (\Throwable $e) {
                                                                    \Log::error('invoices/customer_invoice — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                                }
@endphp

                                                            <div class="{{ VC::FM_GT3 }}">
                                                                <button
                                                                    id="pay_with_skrill_btn_{{ $invoice->id }}"
                                                                    class="{{ VC::BT_PRM }}"
                                                                    name="submit"
                                                                    type="submit"
                                                                >{{ __('Make Payment') }}</button>
                                                            </div>
                                                        </form>
                                                        @push(StacksConstants::ADM_SCR_PG)
                                                            <script defer>
                                                                (() => {
                                                                    const formEl = document.getElementById('{{ $skrillFormId }}');
                                                                    if (!formEl || formEl.getAttribute('data-listener-active') === 'true') return;
                                                                    formEl.setAttribute('data-listener-active', 'true');
                                                                    formEl.addEventListener('submit', event => {
                                                                        try {
                                                                            const url = formEl.getAttribute('data-url') ?? '#';
                                                                            if (url !== '#') return;
                                                                            event.preventDefault();
                                                                            const msg = formEl.getAttribute('data-guard-msg') ?? '# ERROR';
                                                                            (window.RouteGuard?.showToast || (m => alert(m)))(msg);
                                                                            formEl.setAttribute('data-failed-route', 'true');
                                                                        } catch {}
                                                                    });
                                                                })();
                                                            </script>
                                                        @endpush
                                                    </div>
                                                @endif
                                                @if (isset($company_payment_setting['is_coingate_enabled']) && $company_payment_setting['is_coingate_enabled'] == 'on')
                                                    <div class="tab-pane fade" id="coingate-payment" role="tabpanel" aria-labelledby="coingate-payment">
                                                        @php
                                                            try {
                                                                $coingateRouteName        = ViewsConstants::CST . '.pay.with.coingate';
                                                                $coingateActionUrl        = Route::has($coingateRouteName)
                                                                    ? route($coingateRouteName, $invoice->id)
                                                                    : '#';
                                                                $coingateFormId           = 'coingatePaymentForm_' . $invoice->id;
                                                                $coingateGuardMsg         = Utility::fetchLinkMessage(
                                                                    $lang,
                                                                    ViewsConstants::CST,
                                                                    'payment_with_coingate_route_unavailable'
                                                                ) ?? 'Payment with Coingate route is unavailable. Please contact technical support or your domain administrator.';
                                                            } catch (\Throwable $e) {
                                                                \Log::error('invoices/customer_invoice — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                            }
@endphp
                                                        <form
                                                            role="form"
                                                            id="{{ $coingateFormId }}"
                                                            class="require-validation"
                                                            method="post"
                                                            action="{{ $coingateActionUrl }}"
                                                            data-url="{{ $coingateActionUrl }}"
                                                            data-guard-msg="{{ base64_encode($coingateGuardMsg) }}"
                                                        >
                                                            @csrf
                                                            <input type="hidden" name="invoice_id" value="{{ Crypt::encrypt($invoice->id) }}">

                                                            <div class="{{ VC::FM_GCB12 }}">
                                                                <label for="amount">{{ __('Amount') }}</label>
                                                                <div class="input-group">
                                                                    <span class="input-group-prepend">
                                                                        <span class="{{ VC::INP_GP_TXT }}">{{ $siteCurrency }}</span>
                                                                    </span>
                                                                    <input
                                                                        id="amount"
                                                                        class="{{ VC::FM_CT }}"
                                                                        required="required"
                                                                        name="amount"
                                                                        type="number"
                                                                        value="{{ $due }}"
                                                                        min="0"
                                                                        step="0.01"
                                                                        max="{{ $due }}"
                                                                    >
                                                                </div>
                                                            </div>

                                                            <div class="{{ VC::FM_GT3 }}">
                                                                <button
                                                                    id="pay_with_coingate"
                                                                    class="{{ VC::BT_PRM }}"
                                                                    name="submit"
                                                                    type="submit"
                                                                >{{ __('Make Payment') }}</button>
                                                            </div>
                                                        </form>
                                                        @push(StacksConstants::ADM_SCR_PG)
                                                            <script defer>
                                                                (() => {
                                                                    const coingateForm = document.getElementById('{{ $coingateFormId }}');
                                                                    if (!coingateForm || coingateForm.getAttribute('data-listener-active') === 'true') return;
                                                                    coingateForm.setAttribute('data-listener-active', 'true');

                                                                    coingateForm.addEventListener('submit', event => {
                                                                        try {
                                                                            const url = coingateForm.getAttribute('data-url') ?? '#';
                                                                            if (url !== '#') return;
                                                                            event.preventDefault();
                                                                            const msg = coingateForm.getAttribute('data-guard-msg') ?? '# ERROR';
                                                                            (window.RouteGuard?.showToast || (m => alert(m)))(msg);
                                                                            coingateForm.setAttribute('data-failed-route', 'true');
                                                                        } catch (e) {}
                                                                    });
                                                                })();
                                                            </script>
                                                        @endpush
                                                    </div>
                                                @endif
                                                @if (
                                                    !empty($company_payment_setting) &&
                                                        isset($company_payment_setting['is_paymentwall_enabled']) &&
                                                        $company_payment_setting['is_paymentwall_enabled'] == 'on' &&
                                                        !empty($company_payment_setting['paymentwall_public_key']) &&
                                                        !empty($company_payment_setting['paymentwall_private_key']))
                                                    <div class="tab-pane fade" id="paymentwall-payment" role="tabpanel" aria-labelledby="paypal-payment">
                                                        @php
                                                            try {
                                                                $paymentwallRouteName          = ViewsConstants::INV . '.paymentwallpayment';
                                                                $paymentwallActionUrl          = Route::has($paymentwallRouteName)
                                                                    ? route($paymentwallRouteName, $invoice->id)
                                                                    : '#';
                                                                $paymentwallFormId             = 'paymentwallPaymentForm_' . $invoice->id;
                                                                $paymentwallGuardMsg           = Utility::fetchLinkMessage(
                                                                    $lang,
                                                                    ViewsConstants::INV,
                                                                    'payment_with_paymentwall_route_unavailable'
                                                                ) ?? 'Payment with Paymentwall route is unavailable. Please contact technical support or your domain administrator.';
                                                            } catch (\Throwable $e) {
                                                                \Log::error('invoices/customer_invoice — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                            }
@endphp
                                                        <form
                                                            id="{{ $paymentwallFormId }}"
                                                            class="w3-container w3-display-middle w3-card-4"
                                                            method="POST"
                                                            action="{{ $paymentwallActionUrl }}"
                                                            data-url="{{ $paymentwallActionUrl }}"
                                                            data-guard-msg="{{ base64_encode($paymentwallGuardMsg) }}"
                                                        >
                                                            @csrf
                                                            <input type="hidden" name="invoice_id" value="{{ Crypt::encrypt($invoice->id) }}">

                                                            <div class="{{ VC::FM_GCB12 }}">
                                                                <label for="amount">{{ __('Amount') }}</label>
                                                                <div class="input-group">
                                                                    <span class="input-group-prepend">
                                                                        <span class="{{ VC::INP_GP_TXT }}">{{ $siteCurrency }}</span>
                                                                    </span>
                                                                    <input
                                                                        id="amount"
                                                                        class="{{ VC::FM_CT }}"
                                                                        required="required"
                                                                        name="amount"
                                                                        type="number"
                                                                        value="{{ $due }}"
                                                                        min="0"
                                                                        step="0.01"
                                                                        max="{{ $due }}"
                                                                    >
                                                                </div>
                                                            </div>

                                                            <div class="{{ VC::FM_GT3 }}">
                                                                <button
                                                                    id="pay_with_paymentwall_btn_{{ $invoice->id }}"
                                                                    class="{{ VC::BT_PRM }}"
                                                                    name="submit"
                                                                    type="submit"
                                                                >{{ __('Make Payment') }}</button>
                                                            </div>
                                                        </form>
                                                        @push(StacksConstants::ADM_SCR_PG)
                                                            <script defer>
                                                                (() => {
                                                                    const paymentwallForm = document.getElementById('{{ $paymentwallFormId }}');
                                                                    if (!paymentwallForm || paymentwallForm.getAttribute('data-listener-active') === 'true') return;
                                                                    paymentwallForm.setAttribute('data-listener-active', 'true');

                                                                    paymentwallForm.addEventListener('submit', event => {
                                                                        try {
                                                                            const url = paymentwallForm.getAttribute('data-url') ?? '#';
                                                                            if (url !== '#') return;
                                                                            event.preventDefault();
                                                                            const msg = paymentwallForm.getAttribute('data-guard-msg') ?? '# ERROR';
                                                                            (window.RouteGuard?.showToast || (m => alert(m)))(msg);
                                                                            paymentwallForm.setAttribute('data-failed-route', 'true');
                                                                        } catch {}
                                                                    });
                                                                })();
                                                            </script>
                                                        @endpush
                                                    </div>
                                                @endif
                                                @if (isset($company_payment_setting['is_toyyibpay_enabled']) && $company_payment_setting['is_toyyibpay_enabled'] == 'on')
                                                    <div class="tab-pane fade" id="toyyibpay-payment" role="tabpanel" aria-labelledby="toyyibpay-payment">
                                                        @php
                                                            try {
                                                                $toyyibRouteName             = ViewsConstants::CST . '.pay.with.toyyibpay';
                                                                $toyyibActionUrl             = Route::has($toyyibRouteName)
                                                                    ? route($toyyibRouteName, $invoice->id)
                                                                    : '#';
                                                                $toyyibFormId                = 'toyyibpayPaymentForm_' . $invoice->id;
                                                                $toyyibGuardMsg              = Utility::fetchLinkMessage(
                                                                    $lang,
                                                                    ViewsConstants::CST,
                                                                    'payment_with_toyyibpay_route_unavailable'
                                                                ) ?? 'Payment with ToyyibPay route is unavailable. Please contact technical support or your domain administrator.';
                                                            } catch (\Throwable $e) {
                                                                \Log::error('invoices/customer_invoice — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                            }
@endphp
                                                        <form
                                                            role="form"
                                                            id="{{ $toyyibFormId }}"
                                                            class="require-validation"
                                                            method="post"
                                                            action="{{ $toyyibActionUrl }}"
                                                            data-url="{{ $toyyibActionUrl }}"
                                                            data-guard-msg="{{ base64_encode($toyyibGuardMsg) }}"
                                                        >
                                                            @csrf
                                                            <input type="hidden" name="invoice_id" value="{{ Crypt::encrypt($invoice->id) }}">

                                                            <div class="{{ VC::FM_GCB12 }}">
                                                                <label for="amount">{{ __('Amount') }}</label>
                                                                <div class="input-group">
                                                                    <span class="input-group-prepend">
                                                                        <span class="{{ VC::INP_GP_TXT }}">{{ $siteCurrency }}</span>
                                                                    </span>
                                                                    <input
                                                                        id="amount"
                                                                        class="{{ VC::FM_CT }}"
                                                                        required
                                                                        name="amount"
                                                                        type="number"
                                                                        value="{{ $due }}"
                                                                        min="0"
                                                                        step="0.01"
                                                                        max="{{ $due }}"
                                                                    >
                                                                </div>
                                                            </div>

                                                            <div class="{{ VC::FM_GT3 }}">
                                                                <button
                                                                    id="pay_with_toyyibpay_btn_{{ $invoice->id }}"
                                                                    class="{{ VC::BT_PRM }}"
                                                                    name="submit"
                                                                    type="submit"
                                                                >{{ __('Make Payment') }}</button>
                                                            </div>
                                                        </form>
                                                        @push(StacksConstants::ADM_SCR_PG)
                                                            <script defer>
                                                                (() => {
                                                                    const toyyibForm = document.getElementById('{{ $toyyibFormId }}');
                                                                    if (!toyyibForm || toyyibForm.getAttribute('data-listener-active') === 'true') return;
                                                                    toyyibForm.setAttribute('data-listener-active', 'true');

                                                                    toyyibForm.addEventListener('submit', event => {
                                                                        try {
                                                                            const toyyibUrl = toyyibForm.getAttribute('data-url') ?? '#';
                                                                            if (toyyibUrl !== '#') return;
                                                                            event.preventDefault();
                                                                            const toyyibMsg = toyyibForm.getAttribute('data-guard-msg') ?? '# ERROR';
                                                                            (window.RouteGuard?.showToast || (m => alert(m)))(toyyibMsg);
                                                                            toyyibForm.setAttribute('data-failed-route', 'true');
                                                                        } catch {}
                                                                    });
                                                                })();
                                                            </script>
                                                        @endpush
                                                    </div>
                                                @endif
                                                @if (
                                                    !empty($company_payment_setting) &&
                                                        isset($company_payment_setting['is_payfast_enabled']) &&
                                                        $company_payment_setting['is_payfast_enabled'] == 'on' &&
                                                        !empty($company_payment_setting['is_payfast_enabled']) &&
                                                        !empty($company_payment_setting['is_payfast_enabled']))
                                                    <div class="tab-pane fade" id="payfast-payment" role="tabpanel" aria-labelledby="payfast-payment">
                                                        @php
                                                            try {
                                                                $pfHost = $company_payment_setting['payfast_mode'] == 'sandbox' ? 'sandbox.payfast.co.za' : 'www.payfast.co.za';
                                                                $payfastUrl                 = 'https://' . $pfHost . '/eng/process';
                                                                $payfastFormId              = 'payfastPaymentForm_' . $invoice->id;
                                                                $payfastGuardMsg            = Utility::fetchLinkMessage(
                                                                    $lang,
                                                                    ViewsConstants::CST,
                                                                    'payment_with_payfast_route_unavailable'
                                                                ) ?? 'Payment with Payfast route is unavailable. Please contact technical support or your domain administrator.';
                                                            } catch (\Throwable $e) {
                                                                \Log::error('invoices/customer_invoice — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                            }
@endphp
                                                        <form
                                                            role="form"
                                                            id="{{ $payfastFormId }}"
                                                            action="{{ $payfastUrl }}"
                                                            method="post"
                                                            data-url="{{ $payfastUrl }}"
                                                            data-guard-msg="{{ base64_encode($payfastGuardMsg) }}"
                                                            >
                                                            @csrf
                                                            <div class="{{ VC::FM_GCB12 }}">
                                                                <label for="pay_fast_amount">{{ __('Amount') }}</label>
                                                                <div class="input-group">
                                                                    <span class="input-group-prepend">
                                                                        <span class="{{ VC::INP_GP_TXT }}">{{ $siteCurrency }}</span>
                                                                    </span>
                                                                    <input
                                                                        id="pay_fast_amount"
                                                                        class="{{ VC::FM_CT }}"
                                                                        required
                                                                        name="amount"
                                                                        type="number"
                                                                        value="{{ $due }}"
                                                                        min="0"
                                                                        step="0.01"
                                                                        max="{{ $due }}"
                                                                    >
                                                                </div>
                                                            </div>
                                                            <div id="get-payfast-inputs"></div>
                                                            <div class="{{ VC::FM_GT3 }}">
                                                                <input
                                                                    type="hidden"
                                                                    name="invoice_id"
                                                                    value="{{ Crypt::encrypt($invoice->id) }}"
                                                                >
                                                                <button
                                                                    id="pay_with_payfast_btn_{{ $invoice->id }}"
                                                                    class="{{ VC::BT_PRM }}"
                                                                    name="submit"
                                                                    type="submit"
                                                                >{{ __('Make Payment') }}</button>
                                                            </div>
                                                        </form>
                                                        @push(StacksConstants::ADM_SCR_PG)
                                                            <script defer>
                                                                (() => {
                                                                    const pfForm = document.getElementById('{{ $payfastFormId }}');
                                                                    if (!pfForm || pfForm.getAttribute('data-listener-active') === 'true') return;
                                                                    pfForm.setAttribute('data-listener-active', 'true');
                                                                    pfForm.addEventListener('submit', event => {
                                                                        try {
                                                                            const url = pfForm.getAttribute('data-url') ?? '#';
                                                                            if (url !== '#') return;
                                                                            event.preventDefault();
                                                                            const msg = pfForm.getAttribute('data-guard-msg') ?? '# ERROR';
                                                                            (window.RouteGuard?.showToast || (m => alert(m)))(msg);
                                                                            pfForm.setAttribute('data-failed-route', 'true');
                                                                        } catch {}
                                                                    });
                                                                })();
                                                            </script>
                                                        @endpush
                                                    </div>
                                                @endif
                                                @if (isset($company_payment_setting['is_iyzipay_enabled']) && $company_payment_setting['is_iyzipay_enabled'] == 'on')
                                                    <div class="tab-pane fade" id="iyzipay-payment" role="tabpanel" aria-labelledby="iyzipay-payment">
                                                        @php
                                                            try {
                                                                $iyzipayRouteName         = ViewsConstants::CST . '.pay.with.iyzipay';
                                                                $iyzipayActionUrl         = Route::has($iyzipayRouteName)
                                                                    ? route($iyzipayRouteName, $invoice->id)
                                                                    : '#';
                                                                $iyzipayFormId            = 'iyzipayPaymentForm_' . $invoice->id;
                                                                $iyzipayGuardMsg          = Utility::fetchLinkMessage(
                                                                    $lang,
                                                                    ViewsConstants::CST,
                                                                    'payment_with_iyzipay_route_unavailable'
                                                                ) ?? 'Payment with Iyzipay route is unavailable. Please contact technical support or your domain administrator.';
                                                            } catch (\Throwable $e) {
                                                                \Log::error('invoices/customer_invoice — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                            }
@endphp
                                                        <form
                                                            role="form"
                                                            id="{{ $iyzipayFormId }}"
                                                            class="require-validation"
                                                            method="post"
                                                            action="{{ $iyzipayActionUrl }}"
                                                            data-url="{{ $iyzipayActionUrl }}"
                                                            data-guard-msg="{{ base64_encode($iyzipayGuardMsg) }}"
                                                        >
                                                            @csrf
                                                            <input type="hidden" name="invoice_id" value="{{ Crypt::encrypt($invoice->id) }}">

                                                            <div class="{{ VC::FM_GCB12 }}">
                                                                <label for="amount">{{ __('Amount') }}</label>
                                                                <div class="input-group">
                                                                    <span class="input-group-prepend">
                                                                        <span class="{{ VC::INP_GP_TXT }}">{{ $siteCurrency }}</span>
                                                                    </span>
                                                                    <input
                                                                        id="amount"
                                                                        class="{{ VC::FM_CT }}"
                                                                        required="required"
                                                                        name="amount"
                                                                        type="number"
                                                                        value="{{ $due }}"
                                                                        min="0"
                                                                        step="0.01"
                                                                        max="{{ $due }}"
                                                                    >
                                                                </div>
                                                            </div>

                                                            <div class="{{ VC::FM_GT3 }}">
                                                                <button
                                                                    id="pay_with_iyzipay_btn_{{ $invoice->id }}"
                                                                    class="{{ VC::BT_PRM }}"
                                                                    name="submit"
                                                                    type="submit"
                                                                >{{ __('Make Payment') }}</button>
                                                            </div>
                                                        </form>
                                                        @push(StacksConstants::ADM_SCR_PG)
                                                            <script defer>
                                                                (() => {
                                                                    const form = document.getElementById('{{ $iyzipayFormId }}');
                                                                    if (!form || form.getAttribute('data-listener-active') === 'true') return;
                                                                    form.setAttribute('data-listener-active', 'true');
                                                                    form.addEventListener('submit', event => {
                                                                        try {
                                                                            const url = form.getAttribute('data-url') ?? '#';
                                                                            if (url !== '#') return;
                                                                            event.preventDefault();
                                                                            const msg = form.getAttribute('data-guard-msg') ?? '# ERROR';
                                                                            (window.RouteGuard?.showToast || (m => alert(m)))(msg);
                                                                            form.setAttribute('data-failed-route', 'true');
                                                                        } catch (e) {}
                                                                    });
                                                                })();
                                                            </script>
                                                        @endpush
                                                    </div>
                                                @endif
                                                @if (isset($company_payment_setting['is_sspay_enabled']) && $company_payment_setting['is_sspay_enabled'] == 'on')
                                                    <div class="tab-pane fade" id="sspay-payment" role="tabpanel" aria-labelledby="sspay-payment">
                                                        @php
                                                            try {
                                                                $sspayRouteName         = ViewsConstants::CST . '.pay.with.sspay';
                                                                $sspayActionUrl         = Route::has($sspayRouteName)
                                                                    ? route($sspayRouteName, $invoice->id)
                                                                    : '#';
                                                                $sspayFormId            = 'sspayPaymentForm_' . $invoice->id;
                                                                $sspayGuardMsg          = Utility::fetchLinkMessage(
                                                                    $lang,
                                                                    ViewsConstants::CST,
                                                                    'payment_with_sspay_route_unavailable'
                                                                ) ?? 'Payment with SSPay route is unavailable. Please contact technical support or your domain administrator.';
                                                            } catch (\Throwable $e) {
                                                                \Log::error('invoices/customer_invoice — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                            }
@endphp
                                                        <form
                                                            role="form"
                                                            id="{{ $sspayFormId }}"
                                                            class="require-validation"
                                                            method="post"
                                                            action="{{ $sspayActionUrl }}"
                                                            data-url="{{ $sspayActionUrl }}"
                                                            data-guard-msg="{{ base64_encode($sspayGuardMsg) }}"
                                                        >
                                                            @csrf
                                                            <input type="hidden" name="invoice_id" value="{{ Crypt::encrypt($invoice->id) }}">

                                                            <div class="{{ VC::FM_GCB12 }}">
                                                                <label for="amount">{{ __('Amount') }}</label>
                                                                <div class="input-group">
                                                                    <span class="input-group-prepend">
                                                                        <span class="{{ VC::INP_GP_TXT }}">{{ $siteCurrency }}</span>
                                                                    </span>
                                                                    <input
                                                                        id="amount"
                                                                        class="{{ VC::FM_CT }}"
                                                                        required
                                                                        name="amount"
                                                                        type="number"
                                                                        value="{{ $due }}"
                                                                        min="0"
                                                                        step="0.01"
                                                                        max="{{ $due }}"
                                                                    >
                                                                </div>
                                                            </div>

                                                            <div class="{{ VC::FM_GT3 }}">
                                                                <button
                                                                    id="pay_with_sspay_btn_{{ $invoice->id }}"
                                                                    class="{{ VC::BT_PRM }}"
                                                                    name="submit"
                                                                    type="submit"
                                                                >{{ __('Make Payment') }}</button>
                                                            </div>
                                                        </form>
                                                        @push(StacksConstants::ADM_SCR_PG)
                                                            <script defer>
                                                                (() => {
                                                                    const sspayForm = document.getElementById('{{ $sspayFormId }}');
                                                                    if (!sspayForm || sspayForm.getAttribute('data-listener-active') === 'true') return;
                                                                    sspayForm.setAttribute('data-listener-active', 'true');

                                                                    sspayForm.addEventListener('submit', event => {
                                                                        try {
                                                                            const url = sspayForm.getAttribute('data-url') ?? '#';
                                                                            if (url !== '#') return;
                                                                            event.preventDefault();
                                                                            const msg = sspayForm.getAttribute('data-guard-msg') ?? '# ERROR';
                                                                            (window.RouteGuard?.showToast || (m => alert(m)))(msg);
                                                                            sspayForm.setAttribute('data-failed-route', 'true');
                                                                        } catch {}
                                                                    });
                                                                })();
                                                            </script>
                                                        @endpush
                                                    </div>
                                                @endif
                                                @if (isset($company_payment_setting['is_paytab_enabled']) && $company_payment_setting['is_paytab_enabled'] == 'on')
                                                    <div class="tab-pane fade" id="paytab-payment" role="tabpanel" aria-labelledby="paytab-payment">
                                                        @php
                                                            try {
                                                                $paytabRouteName        = ViewsConstants::CST . '.pay.with.paytab';
                                                                $paytabActionUrl        = Route::has($paytabRouteName)
                                                                    ? route($paytabRouteName, $invoice->id)
                                                                    : '#';
                                                                $paytabFormId           = 'paytabPaymentForm_' . $invoice->id;
                                                                $paytabGuardMsg         = Utility::fetchLinkMessage(
                                                                    $lang,
                                                                    ViewsConstants::CST,
                                                                    'payment_with_paytab_route_unavailable'
                                                                ) ?? 'Payment with PayTab route is unavailable. Please contact technical support or your domain administrator.';
                                                            } catch (\Throwable $e) {
                                                                \Log::error('invoices/customer_invoice — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                            }
@endphp
                                                        <form
                                                            role="form"
                                                            id="{{ $paytabFormId }}"
                                                            class="require-validation"
                                                            method="post"
                                                            action="{{ $paytabActionUrl }}"
                                                            data-url="{{ $paytabActionUrl }}"
                                                            data-guard-msg="{{ base64_encode($paytabGuardMsg) }}"
                                                        >
                                                            @csrf
                                                            <input type="hidden" name="invoice_id" value="{{ Crypt::encrypt($invoice->id) }}">

                                                            <div class="{{ VC::FM_GCB12 }}">
                                                                <label for="amount">{{ __('Amount') }}</label>
                                                                <div class="input-group">
                                                                    <span class="input-group-prepend">
                                                                        <span class="{{ VC::INP_GP_TXT }}">{{ $siteCurrency }}</span>
                                                                    </span>
                                                                    <input
                                                                        id="amount"
                                                                        class="{{ VC::FM_CT }}"
                                                                        required
                                                                        name="amount"
                                                                        type="number"
                                                                        value="{{ $due }}"
                                                                        min="0"
                                                                        step="0.01"
                                                                        max="{{ $due }}"
                                                                    >
                                                                </div>
                                                            </div>

                                                            <div class="{{ VC::FM_GT3 }}">
                                                                <button
                                                                    id="pay_with_paytab_btn_{{ $invoice->id }}"
                                                                    class="{{ VC::BT_PRM }}"
                                                                    name="submit"
                                                                    type="submit"
                                                                >{{ __('Make Payment') }}</button>
                                                            </div>
                                                        </form>
                                                        @push(StacksConstants::ADM_SCR_PG)
                                                            <script defer>
                                                                (() => {
                                                                    const paytabForm = document.getElementById('{{ $paytabFormId }}');
                                                                    if (!paytabForm || paytabForm.getAttribute('data-listener-active') === 'true') return;
                                                                    paytabForm.setAttribute('data-listener-active', 'true');

                                                                    paytabForm.addEventListener('submit', e => {
                                                                        try {
                                                                            const url = paytabForm.getAttribute('data-url') ?? '#';
                                                                            if (url !== '#') return;
                                                                            e.preventDefault();
                                                                            const msg = paytabForm.getAttribute('data-guard-msg') ?? '# ERROR';
                                                                            (window.RouteGuard?.showToast || (m => alert(m)))(msg);
                                                                            paytabForm.setAttribute('data-failed-route', 'true');
                                                                        } catch {}
                                                                    });
                                                                })();
                                                            </script>
                                                        @endpush
                                                    </div>
                                                @endif
                                                @if (isset($company_payment_setting['is_benefit_enabled']) && $company_payment_setting['is_benefit_enabled'] == 'on')
                                                    <div class="tab-pane fade" id="benefit-payment" role="tabpanel" aria-labelledby="benefit-payment">
                                                        @php
                                                            try {
                                                                $benefitRouteName             = ViewsConstants::INV . '.benefit.initiate';
                                                                $benefitInitiateRoute         = Route::has($benefitRouteName)
                                                                    ? route($benefitRouteName)
                                                                    : '#';
                                                                $benefitFormId                = 'benefit-payment-form';
                                                                $benefitGuardMsg              = Utility::fetchLinkMessage(
                                                                    $lang,
                                                                    ViewsConstants::INV,
                                                                    'payment_with_benefit_route_unavailable'
                                                                ) ?? 'Payment with Benefit route is unavailable. Please contact technical support or your domain administrator.';
                                                            } catch (\Throwable $e) {
                                                                \Log::error('invoices/customer_invoice — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                            }
@endphp
                                                        <form
                                                            role="form"
                                                            id="{{ $benefitFormId }}"
                                                            class="require-validation"
                                                            method="post"
                                                            action="{{ $benefitInitiateRoute }}"
                                                            data-url="{{ $benefitInitiateRoute }}"
                                                            data-guard-msg="{{ base64_encode($benefitGuardMsg) }}"
                                                        >
                                                            @csrf

                                                            <input type="hidden"
                                                                name="invoice_id"
                                                                value="{{ Crypt::encrypt($invoice->id) }}"
                                                            >

                                                            <div class="{{ VC::FM_GCB12 }}">
                                                                <label for="amount">{{ __('Amount') }}</label>
                                                                <div class="input-group">
                                                                    <span class="input-group-prepend">
                                                                        <span class="{{ VC::INP_GP_TXT }}">{{ $siteCurrency }}</span>
                                                                    </span>
                                                                    <input
                                                                        id="amount"
                                                                        class="{{ VC::FM_CT }}"
                                                                        required="required"
                                                                        name="amount"
                                                                        type="number"
                                                                        value="{{ $due }}"
                                                                        min="0"
                                                                        step="0.01"
                                                                        max="{{ $due }}"
                                                                    >
                                                                </div>
                                                            </div>

                                                            <div class="{{ VC::FM_GT3 }}">
                                                                <button
                                                                    class="{{ VC::BT_PRM }}"
                                                                    name="submit"
                                                                    id="pay_with_benefit_btn_{{ $invoice->id }}"
                                                                    type="submit"
                                                                >{{ __('Make Payment') }}</button>
                                                            </div>
                                                        </form>
                                                        @push(StacksConstants::ADM_SCR_PG)
                                                            <script defer>
                                                                (() => {
                                                                    const form = document.getElementById('{{ $benefitFormId }}');
                                                                    if (!form || form.getAttribute('data-listener-active') === 'true') return;
                                                                    form.setAttribute('data-listener-active', 'true');
                                                                    form.addEventListener('submit', event => {
                                                                        try {
                                                                            const url = form.getAttribute('data-url') ?? '#';
                                                                            if (url !== '#') return;
                                                                            event.preventDefault();
                                                                            const msg = form.getAttribute('data-guard-msg') ?? '# ERROR';
                                                                            (window.RouteGuard?.showToast || (m => alert(m)))(msg);
                                                                            form.setAttribute('data-failed-route','true');
                                                                        } catch (e) {}
                                                                    });
                                                                })();
                                                            </script>
                                                        @endpush
                                                    </div>
                                                @endif
                                                @if (isset($company_payment_setting['is_cashfree_enabled']) && $company_payment_setting['is_cashfree_enabled'] == 'on')
                                                    <div class="tab-pane fade" id="cashfree-payment" role="tabpanel" aria-labelledby="cashfree-payment">
                                                        @php
                                                            try {
                                                                $routeName              = ViewsConstants::CST . '.pay.with.cashfree';
                                                                $cashfreeAction         = Route::has($routeName)
                                                                    ? route($routeName)
                                                                    : '#';
                                                                $cfFormId                 = 'cashfree-payment-form';
                                                                $cashfreeGuardKey       = 'payment_with_cashfree_route_unavailable';
                                                                $cashfreeGuardMsg       = Utility::fetchLinkMessage(
                                                                    $lang,
                                                                    ViewsConstants::CST,
                                                                    $cashfreeGuardKey
                                                                ) ?? 'Payment with Cashfree route is unavailable. Please contact technical support or your domain administrator.';
                                                            } catch (\Throwable $e) {
                                                                \Log::error('invoices/customer_invoice — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                            }
@endphp
                                                        <form
                                                            role="form"
                                                            id="{{ $cfFormId }}"
                                                            class="require-validation"
                                                            method="post"
                                                            action="{{ $cashfreeAction }}"
                                                            data-url="{{ $cashfreeAction }}"
                                                            data-guard-msg="{{ base64_encode($cashfreeGuardMsg) }}"
                                                            data-listener-alias="cashfree-payment"
                                                        >
                                                            @csrf
                                                            <input
                                                                type="hidden"
                                                                name="invoice_id"
                                                                value="{{ Crypt::encrypt($invoice->id) }}"
                                                            >

                                                            <div class="{{ VC::FM_GCB12 }}">
                                                                <label for="amount">{{ __('Amount') }}</label>
                                                                <div class="input-group">
                                                                    <span class="input-group-prepend">
                                                                        <span class="{{ VC::INP_GP_TXT }}">{{ $siteCurrency }}</span>
                                                                    </span>
                                                                    <input
                                                                        id="amount"
                                                                        class="{{ VC::FM_CT }}"
                                                                        required
                                                                        name="amount"
                                                                        type="number"
                                                                        value="{{ $due }}"
                                                                        min="0"
                                                                        step="0.01"
                                                                        max="{{ $due }}"
                                                                    >
                                                                </div>
                                                            </div>

                                                            <div class="{{ VC::FM_GT3 }}">
                                                                <button
                                                                    class="{{ VC::BT_PRM }}"
                                                                    id="pay_with_cashfree"
                                                                    name="submit"
                                                                    type="submit"
                                                                >{{ __('Make Payment') }}</button>
                                                            </div>
                                                        </form>
                                                        @push(StacksConstants::ADM_SCR_PG)
                                                            <script defer>
                                                                (() => {
                                                                    const formCashfree = document.getElementById('{{ $cfFormId }}');
                                                                    if (!formCashfree || formCashfree.getAttribute('data-listener-active') === 'true') return;
                                                                    formCashfree.setAttribute('data-listener-active', 'true');

                                                                    formCashfree.addEventListener('submit', event => {
                                                                        try {
                                                                            const urlCashfree = formCashfree.getAttribute('data-url') ?? '#';
                                                                            if (urlCashfree !== '#') return;
                                                                            event.preventDefault();
                                                                            const msgCashfree = formCashfree.getAttribute('data-guard-msg') ?? '# ERROR';
                                                                            (window.RouteGuard?.showToast || (m => alert(m)))(msgCashfree);
                                                                            formCashfree.setAttribute('data-failed-route', 'true');
                                                                        } catch (e) {}
                                                                    });
                                                                })();
                                                            </script>
                                                        @endpush
                                                    </div>
                                                @endif
                                                @if (isset($company_payment_setting['is_aamarpay_enabled']) && $company_payment_setting['is_aamarpay_enabled'] == 'on')
                                                    <div class="tab-pane fade" id="aamarpay-payment" role="tabpanel" aria-labelledby="aamarpay-payment">
                                                        <form role="form"
                                                            action="{{ route(ViewsConstants::CST.'.pay.with.aamarpay') }}"
                                                            method="post" class="require-validation"
                                                            id="aamarpay-payment-form">
                                                            @csrf
                                                            <input type="hidden" name="invoice_id"
                                                                value="{{ Crypt::encrypt($invoice->id) }}">
                                                            <div class="{{ VC::FM_GCB12 }}">
                                                                <label for="amount">{{ __('Amount') }}</label>
                                                                <div class="input-group">
                                                                    <span class="input-group-prepend"><span
                                                                            class="{{ VC::INP_GP_TXT }}">{{ $siteCurrency }}</span></span>
                                                                    <input class="{{ VC::FM_CT }}" required="required"
                                                                        min="0" name="amount" type="number"
                                                                        value="{{ $due }}" min="0"
                                                                        step="0.01" max="{{ $due }}"
                                                                        id="amount">
                                                                </div>
                                                            </div>
                                                            <div class="{{ VC::FM_GT3 }}">
                                                                <button class="{{ VC::BT_PRM }}" name="submit"
                                                                    id="pay_with_aamarpay"
                                                                    type="submit">{{ __('Make Payment') }}</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                @endif

                                                @if (isset($company_payment_setting['is_paytr_enabled']) && $company_payment_setting['is_paytr_enabled'] == 'on')
                                                    <div class="tab-pane fade" id="paytr-payment" role="tabpanel" aria-labelledby="paytr-payment">
                                                        @php
                                                            try {
                                                                $aamarpayRouteName           = ViewsConstants::CST . '.pay.with.aamarpay';
                                                                $aamarpayActionUrl           = Route::has($aamarpayRouteName)
                                                                    ? route($aamarpayRouteName, $invoice->id)
                                                                    : (Route::has(Str::kebab($aamarpayRouteName))
                                                                        ? route(Str::kebab($aamarpayRouteName), $invoice->id)
                                                                        : '#');
                                                                $aamarpayFormId              = 'aamarpay-payment-form';
                                                                $aamarpayGuardKey            = 'payment_with_aamarpay_route_unavailable';
                                                                $aamarpayGuardMsg            = Utility::fetchLinkMessage(
                                                                    $lang,
                                                                    ViewsConstants::CST,
                                                                    $aamarpayGuardKey
                                                                ) ?? 'Payment with Aamarpay route is unavailable. Please contact technical support or your domain administrator.';
                                                            } catch (\Throwable $e) {
                                                                \Log::error('invoices/customer_invoice — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                            }
@endphp
                                                        <form
                                                            role="form"
                                                            id="{{ $aamarpayFormId }}"
                                                            class="require-validation"
                                                            method="post"
                                                            action="{{ $aamarpayActionUrl }}"
                                                            data-url="{{ $aamarpayActionUrl }}"
                                                            data-guard-msg="{{ base64_encode($aamarpayGuardMsg) }}"
                                                            data-listener-alias="{{ $aamarpayFormId }}"
                                                        >
                                                            @csrf
                                                            <input type="hidden"
                                                                name="invoice_id"
                                                                value="{{ Crypt::encrypt($invoice->id) }}"
                                                            >

                                                            <div class="{{ VC::FM_GCB12 }}">
                                                                <label for="amount">{{ __('Amount') }}</label>
                                                                <div class="input-group">
                                                                    <span class="input-group-prepend">
                                                                        <span class="{{ VC::INP_GP_TXT }}">{{ $siteCurrency }}</span>
                                                                    </span>
                                                                    <input
                                                                        id="amount"
                                                                        class="{{ VC::FM_CT }}"
                                                                        required
                                                                        name="amount"
                                                                        type="number"
                                                                        value="{{ $due }}"
                                                                        min="0"
                                                                        step="0.01"
                                                                        max="{{ $due }}"
                                                                    >
                                                                </div>
                                                            </div>

                                                            <div class="{{ VC::FM_GT3 }}">
                                                                <button
                                                                    id="pay_with_aamarpay_btn_{{ $invoice->id }}"
                                                                    class="{{ VC::BT_PRM }}"
                                                                    name="submit"
                                                                    type="submit"
                                                                >{{ __('Make Payment') }}</button>
                                                            </div>
                                                        </form>
                                                        @push(StacksConstants::ADM_SCR_PG)
                                                            <script defer>
                                                                (() => {
                                                                    const form = document.getElementById('{{ $aamarpayFormId }}');
                                                                    if (!form || form.getAttribute('data-listener-active') === 'true') return;
                                                                    form.setAttribute('data-listener-active', 'true');
                                                                    form.addEventListener('submit', event => {
                                                                        try {
                                                                            const url = form.getAttribute('data-url') ?? '#';
                                                                            if (url !== '#') return;
                                                                            event.preventDefault();
                                                                            const msg = form.getAttribute('data-guard-msg') ?? '# ERROR';
                                                                            (window.RouteGuard?.showToast || (m => alert(m)))(msg);
                                                                            form.setAttribute('data-failed-route', 'true');
                                                                        } catch {}
                                                                    });
                                                                })();
                                                            </script>
                                                        @endpush
                                                    </div>
                                                @endif
                                                @if (isset($company_payment_setting['is_yookassa_enabled']) && $company_payment_setting['is_yookassa_enabled'] == 'on')
                                                    <div class="tab-pane fade" id="yookassa-payment" role="tabpanel" aria-labelledby="yookassa-payment">
                                                        @php
                                                            try {
                                                                $yooRouteName             = ViewsConstants::CST . '.with.yookassa';
                                                                $yooRouteUrl              = Route::has($yooRouteName)
                                                                    ? route($yooRouteName, $invoice->id)
                                                                    : (Route::has(Str::kebab($yooRouteName))
                                                                        ? route(Str::kebab($yooRouteName), $invoice->id)
                                                                        : '#');
                                                                $yooGuardMsg              = Utility::fetchLinkMessage(
                                                                    $lang,
                                                                    ViewsConstants::CST,
                                                                    'payment_with_yookassa_route_unavailable'
                                                                ) ?? 'Payment with YooKassa route is unavailable. Please contact technical support or your domain administrator.';
                                                                $yooListenerAlias         = 'yookassa-payment-' . $invoice->id;
                                                            } catch (\Throwable $e) {
                                                                \Log::error('invoices/customer_invoice — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                            }
@endphp
                                                        <form
                                                            role="form"
                                                            id="yookassa-payment-form"
                                                            class="require-validation"
                                                            method="post"
                                                            action="{{ $yooRouteUrl }}"
                                                            data-url="{{ $yooRouteUrl }}"
                                                            data-guard-msg="{{ base64_encode($yooGuardMsg) }}"
                                                            data-listener-alias="{{ $yooListenerAlias }}"
                                                        >
                                                            @csrf
                                                            <input type="hidden" name="invoice_id" value="{{ Crypt::encrypt($invoice->id) }}">

                                                            <div class="{{ VC::FM_GCB12 }}">
                                                                <label for="amount">{{ __('Amount') }}</label>
                                                                <div class="input-group">
                                                                    <span class="input-group-prepend">
                                                                        <span class="{{ VC::INP_GP_TXT }}">{{ $siteCurrency }}</span>
                                                                    </span>
                                                                    <input
                                                                        id="amount"
                                                                        class="{{ VC::FM_CT }}"
                                                                        required="required"
                                                                        name="amount"
                                                                        type="number"
                                                                        value="{{ $due }}"
                                                                        min="0"
                                                                        step="0.01"
                                                                        max="{{ $due }}"
                                                                    >
                                                                </div>
                                                            </div>

                                                            <div class="{{ VC::FM_GT3 }}">
                                                                <button
                                                                    id="pay_with_yookassa"
                                                                    class="{{ VC::BT_PRM }}"
                                                                    name="submit"
                                                                    type="submit"
                                                                >{{ __('Make Payment') }}</button>
                                                            </div>
                                                        </form>
                                                        @push(StacksConstants::ADM_SCR_PG)
                                                            <script defer src="{{ asset('assets/js/routes/invoices/customers/yookasaPayment.js') }}"></script>
                                                        @endpush
                                                    </div>
                                                @endif
                                                @if (isset($company_payment_setting['is_midtrans_enabled']) && $company_payment_setting['is_midtrans_enabled'] == 'on')
                                                    <div class="tab-pane fade" id="midtrans-payment" role="tabpanel" aria-labelledby="midtrans-payment">
                                                        @php
                                                            try {
                                                                $midtransRouteName         = ViewsConstants::CST . '.with.midtrans';
                                                                $midtransActionUrl         = Route::has($midtransRouteName)
                                                                    ? route($midtransRouteName, $invoice->id)
                                                                    : '#';
                                                                $midtransFormId            = 'midtrans-payment-form';
                                                                $midtransGuardKey          = 'payment_with_midtrans_route_unavailable';
                                                                $midtransGuardMsg          = Utility::fetchLinkMessage(
                                                                    $lang,
                                                                    ViewsConstants::CST,
                                                                    $midtransGuardKey
                                                                ) ?? 'Payment with Midtrans route is unavailable. Please contact technical support or your domain administrator.';
                                                            } catch (\Throwable $e) {
                                                                \Log::error('invoices/customer_invoice — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                            }
@endphp
                                                        <form
                                                            role="form"
                                                            id="{{ $midtransFormId }}"
                                                            class="require-validation"
                                                            method="post"
                                                            action="{{ $midtransActionUrl }}"
                                                            data-url="{{ $midtransActionUrl }}"
                                                            data-guard-msg="{{ base64_encode($midtransGuardMsg) }}"
                                                        >
                                                            @csrf
                                                            <input type="hidden" name="invoice_id" value="{{ Crypt::encrypt($invoice->id) }}">

                                                            <div class="{{ VC::FM_GCB12 }}">
                                                                <label for="amount">{{ __('Amount') }}</label>
                                                                <div class="input-group">
                                                                    <span class="input-group-prepend">
                                                                        <span class="{{ VC::INP_GP_TXT }}">{{ $siteCurrency }}</span>
                                                                    </span>
                                                                    <input
                                                                        id="amount"
                                                                        class="{{ VC::FM_CT }}"
                                                                        required="required"
                                                                        name="amount"
                                                                        type="number"
                                                                        value="{{ $due }}"
                                                                        min="0"
                                                                        step="0.01"
                                                                        max="{{ $due }}"
                                                                    >
                                                                </div>
                                                            </div>

                                                            <div class="{{ VC::FM_GT3 }}">
                                                                <button
                                                                    id="pay_with_midtrans"
                                                                    class="{{ VC::BT_PRM }}"
                                                                    name="submit"
                                                                    type="submit"
                                                                >{{ __('Make Payment') }}</button>
                                                            </div>
                                                        </form>
                                                        @push(StacksConstants::ADM_SCR_PG)
                                                            <script defer>
                                                                (() => {
                                                                    const form = document.getElementById('{{ $midtransFormId }}');
                                                                    if (!form || form.getAttribute('data-listener-active') === 'true') return;
                                                                    form.setAttribute('data-listener-active', 'true');

                                                                    form.addEventListener('submit', event => {
                                                                        try {
                                                                            const url = form.getAttribute('data-url') ?? '#';
                                                                            if (url !== '#') return;
                                                                            event.preventDefault();
                                                                            const msg = form.getAttribute('data-guard-msg') ?? '# ERROR';
                                                                            (window.RouteGuard?.showToast || (m => alert(m)))(msg);
                                                                            form.setAttribute('data-failed-route', 'true');
                                                                        } catch (e) {}
                                                                    });
                                                                })();
                                                            </script>
                                                        @endpush
                                                    </div>
                                                @endif
                                                @if (isset($company_payment_setting['is_xendit_enabled']) && $company_payment_setting['is_xendit_enabled'] == 'on')
                                                    <div class="tab-pane fade" id="xendit-payment" role="tabpanel" aria-labelledby="xendit-payment">
                                                        @php
                                                            try {
                                                                $xenditRouteName          = ViewsConstants::CST . '.with.xendit';
                                                                $xenditRouteUrl           = Route::has($xenditRouteName)
                                                                    ? route($xenditRouteName, $invoice->id)
                                                                    : '#';
                                                                $xenditFormId             = 'xendit-payment-form';
                                                                $xenditGuardMsg           = Utility::fetchLinkMessage(
                                                                    $lang,
                                                                    ViewsConstants::CST,
                                                                    'payment_with_xendit_route_unavailable'
                                                                ) ?? 'Payment with Xendit route is unavailable. Please contact technical support or your domain administrator.';
                                                            } catch (\Throwable $e) {
                                                                \Log::error('invoices/customer_invoice — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                            }
@endphp
                                                        <form
                                                            role="form"
                                                            id="{{ $xenditFormId }}"
                                                            class="require-validation"
                                                            method="post"
                                                            action="{{ $xenditRouteUrl }}"
                                                            data-url="{{ $xenditRouteUrl }}"
                                                            data-guard-msg="{{ base64_encode($xenditGuardMsg) }}"
                                                        >
                                                            @csrf
                                                            <input type="hidden" name="invoice_id" value="{{ Crypt::encrypt($invoice->id) }}">

                                                            <div class="{{ VC::FM_GCB12 }}">
                                                                <label for="amount">{{ __('Amount') }}</label>
                                                                <div class="input-group">
                                                                    <span class="input-group-prepend">
                                                                        <span class="{{ VC::INP_GP_TXT }}">{{ $siteCurrency }}</span>
                                                                    </span>
                                                                    <input
                                                                        id="amount"
                                                                        class="{{ VC::FM_CT }}"
                                                                        required="required"
                                                                        name="amount"
                                                                        type="number"
                                                                        value="{{ $due }}"
                                                                        min="0"
                                                                        step="0.01"
                                                                        max="{{ $due }}"
                                                                    >
                                                                </div>
                                                            </div>

                                                            <div class="{{ VC::FM_GT3 }}">
                                                                <button
                                                                    id="pay_with_xendit"
                                                                    class="{{ VC::BT_PRM }}"
                                                                    name="submit"
                                                                    type="submit"
                                                                >{{ __('Make Payment') }}</button>
                                                            </div>
                                                        </form>
                                                        @push(StacksConstants::ADM_SCR_PG)
                                                            <script defer src="{{ asset('assets/js/routes/invoices/customers/xenditPayment.js') }}"></script>
                                                        @endpush
                                                    </div>
                                                @endif
                                            </div>
                                        </section>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
            <div class="position-fixed top-0 end-0 p-3" style="z-index: 99999">
                <div id="liveToast"
                    class="toast {{ VC::TXT_WT }} fade"
                    role="alert"
                    aria-live="assertive"
                    aria-atomic="true">
                    <div class="{{ VC::DFL }}">
                        <div class="toast-body"></div>
                        <button type="button"
                                class="{{ VC::BT_CL }} btn-close-white me-2 m-auto"
                                data-bs-dismiss="toast"
                                aria-label="Close">
                        </button>
                    </div>
                </div>
            </div>
            <footer id="footer-main">
                <div class="footer-dark">
                    <div class="{{ VC::CT }}">
                        <div class="{{ VC::RW }} {{ VC::ALC }} {{ VC::JCB }} {{ VC::PY2 }} {{ VC::MT4 }} delimiter-top">
                            <div class="{{ VC::C12 }} {{ VC::CM6 }}">
                                <div class="{{ VC::TXSM }} font-weight-bold {{ VC::TXCT }} text-md-left">
                                    {{ !empty($companySettings[SettingsConstants::FT_TXT]) && !empty($companySettings[SettingsConstants::FT_TXT]->value) ? $companySettings[SettingsConstants::FT_TXT]->value : '' }}
                                </div>
                            </div>
                            <div class="{{ VC::C12 }} {{ VC::CM6 }}">
                                <ul class="{{ VC::NAV }} justify-content-center justify-content-md-end {{ VC::MT1 }}">
                                    <li class="{{ VC::NV_IT }}">
                                        <a class="{{ VC::NV_LK }}" href="#" target="_blank">
                                            <i class="fab fa-dribbble"></i>
                                        </a>
                                    </li>
                                    <li class="{{ VC::NV_IT }}">
                                        <a class="{{ VC::NV_LK }}" href="#" target="_blank">
                                            <i class="fab fa-instagram"></i>
                                        </a>
                                    </li>
                                    <li class="{{ VC::NV_IT }}">
                                        <a class="{{ VC::NV_LK }}" href="#" target="_blank">
                                            <i class="fab fa-github"></i>
                                        </a>
                                    </li>
                                    <li class="{{ VC::NV_IT }}">
                                        <a class="{{ VC::NV_LK }}" href="#" target="_blank">
                                            <i class="fab fa-facebook"></i>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </footer>
            <script src="{{ asset('js/jquery.min.js') }}"></script>
            <script src="{{ asset('assets/js/plugins/popper.min.js') }}"></script>
            <script src="{{ asset('assets/js/plugins/bootstrap.min.js') }}"></script>
            <script src="{{ asset('assets/js/dash.js') }}"></script>
            <script src="{{ asset('assets/js/plugins/main.min.js') }}"></script>
            <script src="{{ asset('js/custom.js') }}"></script>
            <script async src="{{ asset('assets/js/plugins/simple-datatables.js') }}"></script>
            <script async src="{{ asset('assets/js/plugins/apexcharts.min.js') }}"></script>
            <script defer src="{{ asset('assets/js/plugins/perfect-scrollbar.min.js') }}"></script>
            <script defer src="{{ asset('assets/js/plugins/feather.min.js') }}"></script>
            <script defer src="{{ asset('assets/js/plugins/bootstrap-switch-button.min.js') }}"></script>
            <script defer src="{{ asset('assets/js/plugins/sweetalert2.all.min.js') }}"></script>
            <script defer src="{{ asset('assets/js/plugins/choices.min.js') }}"></script>
            <script defer src="{{ asset('js/jscolor.js') }}"></script>
            @if ($message = Session::get('success'))
                <script>
                    (() => {typeof show_toastr === 'function' &&  show_toastr('success', '{!! $message !!}');})()
                </script>
            @endif
            @if ($message = Session::get('error'))
                <script>
                    (() => {typeof show_toastr === 'function' && show_toastr('error', '{!! $message !!}');})()
                </script>
            @endif
            <script async src="https://js.stripe.com/v3/"></script>
            <script async src="https://js.paystack.co/v1/inline.js"></script>
            <script async src="https://api.ravepay.co/flwv3-pug/getpaidx/api/flwpbf-inline.js"></script>
            <script async src="https://checkout.razorpay.com/v1/checkout.js"></script>
            <script async src="https://code.jquery.com/jquery-3.5.1.min.js"
                integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0=" crossorigin="anonymous"></script>
            <script async src="https://cdnjs.cloudflare.com/ajax/libs/jquery.form/4.3.0/jquery.form.min.js"
                integrity="sha384-qlmct0AOBiA2VPZkMY3+2WqkHtIQ9lSdAsAn5RUJD/3vA5MKDgSGcdmIv4ycVxyn" crossorigin="anonymous">
            </script>
            <script async src="{{ asset('assets/js/routes/invoices/customers/lang/invoice.js') }}"></script>
            <script defer>
                (() => {
                    const dataListenerAdded   = 'data-listener-added';
                    const errFb               = '# ERROR';
                    const dataClientLocalized = 'data-client-localized';
                    const dataGuardMsg        = 'data-guard-msg';

                    const getLocalizedMessage = (el, msgKey) => {
                        let msg = errFb;
                        if (
                            el.getAttribute('data-sv-localized') === 'true' ||
                            el.getAttribute(dataClientLocalized) === 'true'
                        ) {
                            msg = el.getAttribute(dataGuardMsg) || errFb;
                        } else {
                            let lang = (
                                sessionStorage.getItem('erp-np-lang') ||
                                document.documentElement.lang ||
                                'en'
                            )
                                .toLowerCase()
                                .replace(/_/g, '-');
                            lang = lang === 'pt-br' ? lang : lang.slice(0, 2);
                            msg =
                                translations?.[lang]?.[msgKey] ||
                                el.getAttribute(dataGuardMsg) ||
                                translations?.['en']?.[msgKey] ||
                                errFb;
                            if (msg !== errFb) {
                                el.setAttribute(dataGuardMsg, msg);
                                el.setAttribute(dataClientLocalized, 'true');
                            }
                        }
                        return msg;
                    };

                    const handleErrorDisplay = (el, msgKey) => {
                        const message = el
                            ? getLocalizedMessage(el, msgKey)
                            : errFb;
                        const hasBootstrap =
                            document.querySelector('link[href*="bootstrap"]') &&
                            bootstrap?.Toast;
                        if (hasBootstrap) {
                            if (!document.querySelector('#error-toast')) {
                                const toast = document.createElement('div');
                                toast.id        = 'error-toast';
                                toast.className = 'toast align-items-center text-bg-danger border-0';
                                toast.setAttribute('role', 'alert');
                                toast.setAttribute('aria-live', 'assertive');
                                toast.setAttribute('aria-atomic', 'true');
                                toast.innerHTML = `
                                    <div class="{{ VC::DFL }}">
                                        <div class="toast-body">${message}</div>
                                        <button type="button"
                                                class="{{ VC::BT_CL }} btn-close-white me-2 m-auto"
                                                data-bs-dismiss="toast"
                                                aria-label="Close"></button>
                                    </div>`;
                                document.body.appendChild(toast);
                            }
                            new bootstrap.Toast(
                                document.querySelector('#error-toast')
                            ).show();
                        } else {
                            alert(message);
                        }
                    };

                    try {
                        if (typeof $ === 'undefined') {
                            if (
                                window.location.hostname === "localhost" ||
                                window.location.hostname === "127.0.0.1"
                            ) console.error("jQuery unavailable");
                            return;
                        }

                        // Stripe
                        @if (
                            $invoice->status != 0 &&
                            $invoice->getDue() > 0 &&
                            !empty($company_payment_setting) &&
                            $company_payment_setting['is_stripe_enabled'] === 'on' &&
                            $company_payment_setting['stripe_key'] &&
                            $company_payment_setting['stripe_secret']
                        )
                            try {
                                const stripe = Stripe('{{ !empty($company_payment_setting['stripe_key']) ? $company_payment_setting['stripe_key'] : '' }}');
                                const elements = stripe.elements();
                                const style = { base: { fontSize: '14px', color: '#32325d' } };
                                const card = elements.create('card', { style });
                                card.mount('#card-element');
                                const form = document.getElementById('payment-form');
                                form.addEventListener('submit', async event => {
                                    event.preventDefault();
                                    const result = await stripe.createToken(card);
                                    if (result.error) {
                                        $('#card-errors').html(result.error.message);
                                        show_toastr('error', result.error.message, 'error');
                                    } else {
                                        const hiddenInput = document.createElement('input');
                                        hiddenInput.type = 'hidden';
                                        hiddenInput.name = 'stripeToken';
                                        hiddenInput.value = result.token.id;
                                        form.appendChild(hiddenInput);
                                        form.submit();
                                    }
                                });
                            } catch {
                                const el = document.getElementById('payment-form');
                                if (el && !el.hasAttribute(dataListenerAdded)) {
                                    el.addEventListener('click', () =>
                                        handleErrorDisplay(el, 'stripe_unavailable')
                                    );
                                    el.setAttribute(dataListenerAdded, 'true');
                                    const obs = new MutationObserver((_, o) => {
                                        if (!document.body.contains(el)) {
                                            el.removeEventListener('click', () =>
                                                handleErrorDisplay(el, 'stripe_unavailable')
                                            );
                                            o.disconnect();
                                        }
                                    });
                                    obs.observe(document.body, { childList: true, subtree: true });
                                }
                            }
                        @endif

                        // Paystack
                        @if (!empty($company_payment_setting) && isset($company_payment_setting['paystack_public_key']))
                            $(document).on('click', '#pay_with_paystack', function () {
                                const el = this;
                                if (el.hasAttribute(dataListenerAdded)) return;
                                $('#paystack-payment-form')
                                    .ajaxForm(res => {
                                        try {
                                            if (res.flag === 1) {
                                                const handler = PaystackPop.setup({
                                                    key: '{{ $company_payment_setting['paystack_public_key'] }}',
                                                    email: res.email,
                                                    amount: res.total_price * 100,
                                                    currency: res.currency,
                                                    ref: 'ps_ref_' + Math.floor(Math.random() * 1e9 + 1),
                                                    metadata: { custom_fields: [{ display_name: 'Email', variable_name: 'email', value: res.email }] },
                                                    callback: r => window.location.href =
                                                        `{{ url('/customers/paystack') }}/${r.reference}/{{ encrypt($invoice->id) }}?amount=${res.total_price}`,
                                                    onClose: () => alert('window closed')
                                                });
                                                handler.openIframe();
                                            } else {
                                                toastrs('Error', res.msg || res.message, 'msg');
                                            }
                                        } catch {
                                            handleErrorDisplay(el, 'paystack_unavailable');
                                        }
                                    })
                                    .submit();
                                el.setAttribute(dataListenerAdded, 'true');
                                const obs = new MutationObserver((_, o) => {
                                    if (!document.body.contains(el)) {
                                        $(el).off('click');
                                        o.disconnect();
                                    }
                                });
                                obs.observe(document.body, { childList: true, subtree: true });
                            });
                        @endif

                        // Flutterwave
                        @if (!empty($company_payment_setting) && isset($company_payment_setting['flutterwave_public_key']))
                            $(document).on('click', '#pay_with_flutterwave', function () {
                                const el = this;
                                if (el.hasAttribute(dataListenerAdded)) return;
                                $('#flutterwave-payment-form')
                                    .ajaxForm(res => {
                                        try {
                                            if (res.flag === 1) {
                                                const txref = `${Date.now()}_${Math.floor(Math.random() * 1e9)}`;
                                                const x = getpaidSetup({
                                                    PBFPubKey: '{{ $company_payment_setting['flutterwave_public_key'] }}',
                                                    customer_email: '{{ $user->email }}',
                                                    amount: res.total_price,
                                                    currency: '{{ $siteCurrency }}',
                                                    txref,
                                                    meta: [{ metaname: 'payment_id', metavalue: 'id' }],
                                                    callback: rsp => {
                                                        if (['00','0'].includes(rsp.tx.chargeResponseCode)) {
                                                            window.location.href =
                                                                `{{ url('/customers/flutterwave') }}/${rsp.tx.txRef}/{{ encrypt($invoice->id) }}?amount=${res.total_price}`;
                                                        }
                                                        x.close();
                                                    },
                                                    onclose: () => {}
                                                });
                                            } else {
                                                toastrs('Error', res.msg || res.message, 'msg');
                                            }
                                        } catch {
                                            handleErrorDisplay(el, 'flutterwave_unavailable');
                                        }
                                    })
                                    .submit();
                                el.setAttribute(dataListenerAdded, 'true');
                                const obs = new MutationObserver((_, o) => {
                                    if (!document.body.contains(el)) {
                                        $(el).off('click');
                                        o.disconnect();
                                    }
                                });
                                obs.observe(document.body, { childList: true, subtree: true });
                            });
                        @endif

                        // Razorpay
                        @if (!empty($company_payment_setting) && isset($company_payment_setting['razorpay_public_key']))
                            $(document).on('click', '#pay_with_razorpay', function () {
                                const el = this;
                                if (el.hasAttribute(dataListenerAdded)) return;
                                $('#razorpay-payment-form')
                                    .ajaxForm(res => {
                                        try {
                                            if (res.flag === 1) {
                                                const options = {
                                                    key: '{{ $company_payment_setting['razorpay_public_key'] }}',
                                                    amount: res.total_price * 100,
                                                    currency: '{{ $siteCurrency }}',
                                                    name: 'Invoice',
                                                    handler: r => window.location.href =
                                                        `{{ url('/customers/razorpay') }}/${r.razorpay_payment_id}/{{ encrypt($invoice->id) }}?amount=${res.total_price}`,
                                                    theme: { color: '#528FF0' }
                                                };
                                                new Razorpay(options).open();
                                            } else {
                                                toastrs('Error', res.msg || res.message, 'msg');
                                            }
                                        } catch {
                                            handleErrorDisplay(el, 'razorpay_unavailable');
                                        }
                                    })
                                    .submit();
                                el.setAttribute(dataListenerAdded, 'true');
                                const obs = new MutationObserver((_, o) => {
                                    if (!document.body.contains(el)) {
                                        $(el).off('click');
                                        o.disconnect();
                                    }
                                });
                                obs.observe(document.body, { childList: true, subtree: true });
                            });
                        @endif

                        // PayFast
                        @if (
                            !empty($company_payment_setting) &&
                            isset($company_payment_setting['is_payfast_enabled']) &&
                            $company_payment_setting['is_payfast_enabled'] === 'on'
                        )
                            const getPayFastStatus = () => {
                                const el = document.getElementById('invoice_id') || document.querySelector('#pay_fast_amount');
                                if (!el) return;
                                const invoiceId = $('#invoice_id').val() ?? '';
                                const amount = $('#pay_fast_amount').val() ?? '';
                                $.ajax({
                                    url: '{{ route(ViewsConstants::INV . ".with.payfast") }}',
                                    method: 'POST',
                                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                                    data: { invoice_id: invoiceId, amount },
                                    success: data => {
                                        try {
                                            if (data.success) {
                                                $('#get-payfast-inputs').append(data.inputs);
                                            } else {
                                                show_toastr('Error', data.inputs || data.msg, 'error');
                                            }
                                        } catch {
                                            handleErrorDisplay(el, 'payfast_unavailable');
                                        }
                                    }
                                });
                            };

                            // CSP-safe replacements for inline onclick/onchange
                            const pfTabTrigger = document.getElementById('payfast-tab-trigger');
                            if (pfTabTrigger) {
                                pfTabTrigger.addEventListener('click', getPayFastStatus);
                            }
                            const pfAmountInput = document.getElementById('pay_fast_amount');
                            if (pfAmountInput) {
                                pfAmountInput.addEventListener('change', getPayFastStatus);
                            }
                        @endif

                        // Shipping toggle
                        $(document).on('click', '#shipping', function () {
                            try {
                                const el = this;
                                const url = $(el).data('url') ?? '';
                                const isDisplay = $(el).is(':checked');
                                if (!url) return;
                                $.ajax({ url, type: 'get', data: { is_display: isDisplay } });
                            } catch {
                                handleErrorDisplay(this, 'shipping_toggle_unavailable');
                            }
                        });

                    } catch (e) {
                        if (
                            window.location.hostname === "localhost" ||
                            window.location.hostname === "127.0.0.1"
                        ) console.error('Initialization failed', e);
                    }
                })();
            </script>
            @if (!empty($get_cookie) && isset($get_cookie['enable_cookie']) && $get_cookie['enable_cookie'] == 'on')
                @includeIf(ExtendingLayoutsConstants::CKC)
            @endif
        @else
            <div class="{{ VC::ALT_WRN }}">{{ __('Invoice not found') }}</div>
        @endif
    </body>
</html>
