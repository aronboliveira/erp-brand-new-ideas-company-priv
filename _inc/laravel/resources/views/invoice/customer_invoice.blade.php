@php
	use App\Config\Constants\{
		DatabaseConstants,
		ExtendingLayoutsConstants,
		SettingsConstants,
		ViewClassNamesConstants as VC,
		ViewsConstants
	};
	use App\Models\{Invoice,Utility};
	use Illuminate\Support\Facades\{Crypt,Log};
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
    $lang = Utility::fetchUserLang();
	try {
		$creatorId = $invoice?->{DatabaseConstants::TABLE_CREATOR} ?? '';
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
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', is_string(app()->getLocale()) ? app()->getLocale() : DatabaseConstants::DEFAULT_LANG) }}" dir="{{ $settings_data[SettingsConstants::RTL] == 'on' ? 'rtl' : '' }}">
    <head>
        <title>
            {{ Utility::companyData($invoice[DatabaseConstants::TABLE_CREATOR], 'title_text') ? 
            Utility::companyData($invoice[DatabaseConstants::TABLE_CREATOR], 'title_text') : 
            config('app.name', 'ERPNovaPrestech') }}
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
        @if ($settings_data[SettingsConstants::RTL] == 'on')
            <link rel="stylesheet" href="{{ asset('assets/css/style-rtl.css') }}">
        @endif
        @stack('css-page')
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <style>
            #card-element {
                border: 1px solid #a3afbb !important;
                border-radius: 10px !important;
                padding: 10px !important;
            }
        </style>
    </head>
    <body class="{{ $color }}">
        <header class="header header-transparent" id="header-main">
        </header>
        <div class="{{ VC::MCT_CT }}">
            <div class="{{ VC::RW }} justify-content-between align-items-center mb-3">
                <div class="col-md-12 {{ VC::DFL }} {{ VC::ALC }} justify-content-between justify-content-md-end">
                    <div class="all-button-box mx-2">
                        @php
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
                        @endphp
                        <a
                            href="{{ $pdfRoute }}"
                            target="_blank"
                            class="{{ VC::BT_PRM }} mt-3"
                            data-url="{{ $pdfRoute }}"
                            data-guard-msg="{{ $downloadGuardMsg }}"
                            data-listener-alias="download-invoice-pdf"
                        >
                            {{ __('Download') }}
                        </a>
                        @push(StacksConstants::ADM_SCR_PG)
                            <script defer>
                                (() => {
                                    const link = document.querySelector('[data-listener-alias="download-invoice-pdf"]');
                                    if (!link || link.getAttribute('data-listener-active') === 'true') return;
                                    link.setAttribute('data-listener-active', 'true');
                                    link.addEventListener('click', event => {
                                        try {
                                            const url = link.getAttribute('data-url') ?? '#';
                                            if (url !== '#') return;
                                            event.preventDefault();
                                            const msg           = link.getAttribute('data-guard-msg') ?? '# ERROR';
                                            const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                                            let container       = document.getElementById('toast-container');
                                            if (!container) {
                                                container       = document.createElement('div');
                                                container.id    = 'toast-container';
                                                document.body.appendChild(container);
                                            }
                                            if (bootstrapLink && window.bootstrap) {
                                                const toastEl      = document.createElement('div');
                                                toastEl.className  = 'toast';
                                                toastEl.setAttribute('role', 'alert');
                                                toastEl.setAttribute('aria-live', 'assertive');
                                                toastEl.setAttribute('aria-atomic', 'true');
                                                const body         = document.createElement('div');
                                                body.className     = 'toast-body';
                                                body.textContent   = msg;
                                                toastEl.appendChild(body);
                                                container.appendChild(toastEl);
                                                bootstrap.Toast.getOrCreateInstance(toastEl).show();
                                            } else {
                                                alert(msg);
                                            }
                                            link.setAttribute('data-failed-route', 'true');
                                        } catch (e) {}
                                    });
                                })();
                            </script>
                        @endpush
                    </div>
                    @if (
                        $invoice->status != 0 &&
                            $invoice->getDue() > 0 &&
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
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="invoice">
                                <div class="invoice-print">
                                    <div class="{{ VC::RW }} invoice-title {{ VC::MT2 }}">
                                        <div class="{{ VC::C12 }} {{ VC::CM6 }} col-lg-6">
                                            <h2>{{ __('Invoice') }}</h2>
                                        </div>
                                        <div class="{{ VC::C12 }} {{ VC::CM6 }} col-lg-6 {{ VC::JCE }}">
                                            <h3 class="invoice-number {{ VC::FEND }}">
                                                {{ $user->invoiceNumberFormat($invoice->invoice_id) }}
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
                                                        {{ Utility::dateFormat($settings, $invoice->issue_date) }}<br><br>
                                                    </small>
                                                </div>
                                                <small>
                                                    <strong>{{ __('Due Date') }} :</strong><br>
                                                    {{ Utility::dateFormat($settings, $invoice->due_date) }}<br><br>
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="{{ VC::RW }}">
                                        @if (!empty($customer->billing_name))
                                            <div class="{{ VC::C12 }} {{ VC::CM4 }} col-lg-4">
                                                <small class="font-style">
                                                    <strong>{{ __('Billed To') }} :</strong><br>
                                                    {{ $customer->billing_name }}<br>
                                                    {{ $customer->billing_phone }}<br>
                                                    {{ $customer->billing_address }}<br>
                                                    {{ $customer->billing_zip }}<br>
                                                    {{ $customer->billing_city }}, {{ $customer->billing_state }}, {{ $customer->billing_country }}
                                                </small>
                                            </div>
                                        @endif
                                        @if (Utility::companyData($invoice[DatabaseConstants::TABLE_CREATOR], 'shipping_display') == 'on')
                                            <div class="{{ VC::C12 }} {{ VC::CM4 }} col-lg-4">
                                                <small>
                                                    <strong>{{ __('Shipped To') }} :</strong><br>
                                                    {{ $customer->shipping_name }}<br>
                                                    {{ $customer->shipping_phone }}<br>
                                                    {{ $customer->shipping_address }}<br>
                                                    {{ $customer->shipping_zip }}<br>
                                                    {{ $customer->shipping_city }}, {{ $customer->shipping_state }}, {{ $customer->shipping_country }}
                                                </small>
                                            </div>
                                        @endif
                                        <div class="{{ VC::C12 }} {{ VC::CM4 }} col-lg-4 {{ VC::DFL }} {{ VC::JCE }}">
                                            @php
                                                $routeName      = ViewsConstants::INV . '.link.copy';
                                                $copyLinkRoute  = Route::has($routeName)
                                                    ? route($routeName, Crypt::encrypt($invoice->id))
                                                    : '#';
                                                $guardMsg       = Utility::fetchLinkMessage(
                                                    $lang,
                                                    ViewsConstants::INV,
                                                    'link_copy_route_unavailable'
                                                ) ?? 'Invoice link copy route is unavailable. Please contact technical support or your domain administrator.';
                                            @endphp
                                            <div class="{{ VC::FEND }} {{ VC::MT3 }}">
                                                <div
                                                    class="qr-code-wrapper"
                                                    data-url="{{ $copyLinkRoute }}"
                                                    data-guard-msg="{{ $guardMsg }}"
                                                    data-listener-alias="qrcode-copy-link"
                                                >
                                                    {!! DNS2D::getBarcodeHTML($copyLinkRoute, 'QRCODE', 2, 2) !!}
                                                </div>
                                            </div>
                                            @push(StacksConstants::ADM_SCR_PG)
                                                <script defer>
                                                    (() => {
                                                        const wrapper = document.querySelector('[data-listener-alias="qrcode-copy-link"]');
                                                        if (!wrapper || wrapper.getAttribute('data-listener-active') === 'true') return;
                                                        wrapper.setAttribute('data-listener-active', 'true');
                                                        wrapper.addEventListener('click', event => {
                                                            try {
                                                                const url = wrapper.getAttribute('data-url') ?? '#';
                                                                if (url !== '#') return;
                                                                event.preventDefault();
                                                                const msg           = wrapper.getAttribute('data-guard-msg') ?? '# ERROR';
                                                                const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                                                                let container       = document.getElementById('toast-container');
                                                                if (!container) {
                                                                    container       = document.createElement('div');
                                                                    container.id    = 'toast-container';
                                                                    document.body.appendChild(container);
                                                                }
                                                                if (bootstrapLink && window.bootstrap) {
                                                                    const toastEl      = document.createElement('div');
                                                                    toastEl.className  = 'toast';
                                                                    toastEl.setAttribute('role', 'alert');
                                                                    toastEl.setAttribute('aria-live', 'assertive');
                                                                    toastEl.setAttribute('aria-atomic', 'true');
                                                                    const body         = document.createElement('div');
                                                                    body.className     = 'toast-body';
                                                                    body.textContent   = msg;
                                                                    toastEl.appendChild(body);
                                                                    container.appendChild(toastEl);
                                                                    bootstrap.Toast.getOrCreateInstance(toastEl).show();
                                                                } else {
                                                                    alert(msg);
                                                                }
                                                                wrapper.setAttribute('data-failed-route', 'true');
                                                            } catch (e) {}
                                                        });
                                                    })();
                                                </script>
                                            @endpush
                                        </div>
                                    </div>
                                    <div class="{{ VC::RW }} mt-3">
                                        <div class="{{ VC::C12 }} {{ VC::CM4 }} col-lg-4">
                                            <small>
                                                <strong>{{ __('Status') }} :</strong><br>
                                                @php $badge = match($invoice->status) {
                                                    0 => 'bg-primary',
                                                    1 => 'bg-warning',
                                                    2 => 'bg-danger',
                                                    3 => 'bg-info',
                                                    4 => 'bg-primary',
                                                    default => 'bg-secondary',
                                                }; @endphp
                                                <span class="badge {{ $badge }}">
                                                    {{ __(Invoice::$statuses[$invoice->status]) }}
                                                </span>
                                            </small>
                                        </div>
                                    
                                        @if (!empty($customFields) && count($invoice->customField) > 0)
                                            @foreach ($customFields as $field)
                                                <div class="{{ VC::C12 }} {{ VC::CM4 }} col-lg-4 {{ VC::JCE }}">
                                                    <small>
                                                        <strong>{{ $field->name }} :</strong><br>
                                                        {{ $invoice->customField[$field->id] ?? '-' }}<br><br>
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
                                                        <th data-width="40" class="text-dark">#</th>
                                                        <th class="text-dark">{{ __('Product') }}</th>
                                                        <th class="text-dark">{{ __('Quantity') }}</th>
                                                        <th class="text-dark">{{ __('Rate') }}</th>
                                                        <th class="text-dark">{{ __('Discount') }}</th>
                                                        <th class="text-dark">{{ __('Tax') }}</th>
                                                        <th class="text-dark">{{ __('Description') }}</th>
                                                        <th class="text-end text-dark" width="12%">
                                                            {{ __('Price') }}<br>
                                                            <small class="text-danger font-weight-bold">
                                                                {{ __('after tax & discount') }}
                                                            </small>
                                                        </th>
                                                    </tr>
                                                    @php
                                                        $totalQuantity = 0;
                                                        $totalRate     = 0;
                                                        $totalTaxPrice = 0;
                                                        $totalDiscount = 0;
                                                        $taxesData     = [];
                                                    @endphp
                                                    @foreach ($iteams as $key => $iteam)
                                                        @if (!empty($iteam->tax))
                                                            @php
                                                                $taxes          = Utility::tax($iteam->tax);
                                                                $totalQuantity += $iteam->quantity;
                                                                $totalRate     += $iteam->price;
                                                                $totalDiscount += $iteam->discount;
                                                                foreach ($taxes as $taxe) {
                                                                    $taxDataPrice = Utility::taxRate(
                                                                        $taxe->rate,
                                                                        $iteam->price,
                                                                        $iteam->quantity,
                                                                        $iteam->discount
                                                                    );
                                                                    $taxesData[$taxe->name] = ($taxesData[$taxe->name] ?? 0) + $taxDataPrice;
                                                                }
                                                            @endphp
                                                        @endif
                                                        <tr>
                                                            <td>{{ $key + 1 }}</td>
                                                            <td>{{ $iteam->product->name ?? '' }}</td>
                                                            <td>{{ $iteam->quantity }}</td>
                                                            <td>{{ Utility::priceFormat($settings, $iteam->price) }}</td>
                                                            <td>{{ Utility::priceFormat($settings, $iteam->discount) }}</td>
                                                            <td>
                                                                @if (!empty($iteam->tax))
                                                                    <table>
                                                                        @php
                                                                            $totalTaxPrice = 0;
                                                                        @endphp
                                                                        @foreach ($taxes as $tax)
                                                                            @php
                                                                                $taxPrice       = Utility::taxRate(
                                                                                    $tax->rate,
                                                                                    $iteam->price,
                                                                                    $iteam->quantity,
                                                                                    $iteam->discount
                                                                                );
                                                                                $totalTaxPrice += $taxPrice;
                                                                            @endphp
                                                                            <tr>
                                                                                <td>{{ "{$tax->name} ({$tax->rate}% )" }}</td>
                                                                                <td>{{ Utility::priceFormat($settings, $taxPrice) }}</td>
                                                                            </tr>
                                                                        @endforeach
                                                                    </table>
                                                                @else
                                                                    -
                                                                @endif
                                                            </td>
                                                            <td>{{ $iteam->description ?: '-' }}</td>
                                                            <td class="text-end">
                                                                {{ Utility::priceFormat(
                                                                    $settings,
                                                                    $iteam->price * $iteam->quantity
                                                                      - $iteam->discount
                                                                      + $totalTaxPrice
                                                                ) }}
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                    <tfoot>
                                                        <tr>
                                                            <td></td>
                                                            <td><b>{{ __('Total') }}</b></td>
                                                            <td><b>{{ $totalQuantity }}</b></td>
                                                            <td>{{ Utility::priceFormat($settings, $totalRate) }}</td>
                                                            <td><b>{{ Utility::priceFormat($settings, $totalDiscount) }}</b></td>
                                                            <td><b>{{ Utility::priceFormat($settings, $totalTaxPrice) }}</b></td>
                                                            <td></td>
                                                        </tr>
                                                        <tr>
                                                            <td colspan="6"></td>
                                                            <td class="text-end"><b>{{ __('Sub Total') }}</b></td>
                                                            <td class="text-end">
                                                                {{ Utility::priceFormat($settings, $invoice->getSubTotal()) }}
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td colspan="6"></td>
                                                            <td class="text-end"><b>{{ __('Discount') }}</b></td>
                                                            <td class="text-end">
                                                                {{ Utility::priceFormat($settings, $invoice->getTotalDiscount()) }}
                                                            </td>
                                                        </tr>
                                                        @if (!empty($taxesData))
                                                            @foreach ($taxesData as $taxName => $taxPrice)
                                                                <tr>
                                                                    <td colspan="6"></td>
                                                                    <td class="text-end"><b>{{ $taxName }}</b></td>
                                                                    <td class="text-end">
                                                                        {{ Utility::priceFormat($settings, $taxPrice) }}
                                                                    </td>
                                                                </tr>
                                                            @endforeach
                                                        @endif
                                                        <tr>
                                                            <td colspan="6"></td>
                                                            <td class="blue-text text-end"><b>{{ __('Total') }}</b></td>
                                                            <td class="blue-text text-end">
                                                                {{ Utility::priceFormat($settings, $invoice->getTotal()) }}
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td colspan="6"></td>
                                                            <td class="text-end"><b>{{ __('Paid') }}</b></td>
                                                            <td class="text-end">
                                                                {{ Utility::priceFormat(
                                                                    $settings,
                                                                    $invoice->getTotal()
                                                                      - $invoice->getDue()
                                                                      - $invoice->invoiceTotalCreditNote()
                                                                ) }}
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td colspan="6"></td>
                                                            <td class="text-end"><b>{{ __('Credit Note') }}</b></td>
                                                            <td class="text-end">
                                                                {{ Utility::priceFormat($settings, $invoice->invoiceTotalCreditNote()) }}
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td colspan="6"></td>
                                                            <td class="text-end"><b>{{ __('Due') }}</b></td>
                                                            <td class="text-end">
                                                                {{ Utility::priceFormat($settings, $invoice->getDue()) }}
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
                    <h5 class="h4 d-inline-block font-weight-400 mb-2">{{ __('Receipt Summary') }}</h5><br>
                    @if ($user_plan->storage_limit <= $invoice_user->storage_limit)
                        <small class="{{ VC::TXT_MT }} {{ VC::FW600 }} text-danger">
                            {{ __('Your plan storage limit is over , so you can not see customer uploaded payment receipt') }}
                        </small><br>
                    @endif
            
                    <div class="{{ VC::CD }} {{ VC::MT1 }}">
                        <div class="{{ VC::CD_MT }} table-border-style">
                            <div class="{{ VC::tableResponsive ?? 'table-responsive' }}">
                                <table class="{{ VC::TB }}">
                                    <tr>
                                        <th class="text-dark">{{ __('Date') }}</th>
                                        <th class="text-dark">{{ __('Amount') }}</th>
                                        <th class="text-dark">{{ __('Payment Type') }}</th>
                                        <th class="text-dark">{{ __('Account') }}</th>
                                        <th class="text-dark">{{ __('Reference') }}</th>
                                        <th class="text-dark">{{ __('Description') }}</th>
                                        <th class="text-dark">{{ __('Receipt') }}</th>
                                        <th class="text-dark">{{ __('OrderId') }}</th>
                                        @can('delete invoice product')
                                            <th class="text-dark">{{ __('Action') }}</th>
                                        @endcan
                                    </tr>
                                    @php $path = Utility::getFile('uploads/order'); @endphp
            
                                    @forelse ($invoice->payments as $key => $payment)
                                        <tr>
                                            <td>{{ Utility::dateFormat($settings, $payment->date) }}</td>
                                            <td>{{ Utility::priceFormat($settings, $payment->amount) }}</td>
                                            <td>{{ $payment->payment_type }}</td>
                                            <td>
                                                {{ $payment->bankAccount
                                                    ? $payment->bankAccount->bank_name.' '.$payment->bankAccount->holder_name
                                                    : '--' }}
                                            </td>
                                            <td>{{ $payment->reference ?: '--' }}</td>
                                            <td>{{ $payment->description ?: '--' }}</td>
            
                                            @if ($user_plan->storage_limit <= $invoice_user->storage_limit)
                                                <td>--</td>
                                            @else
                                                <td>
                                                    @if ($payment->receipt)
                                                        @php
                                                            $receiptUrl  = !empty($payment->receipt) ? $path . '/' . $payment->receipt : '#';
                                                            $guardMsg    = Utility::fetchLinkMessage(
                                                                $lang,
                                                                ViewsConstants::INV,
                                                                'payment_receipt_route_unavailable'
                                                            ) ?? 'Payment receipt route is unavailable. Please contact technical support or your domain administrator.';
                                                        @endphp
                                                        <a
                                                            href="{{ $receiptUrl }}"
                                                            target="_blank"
                                                            class="{{ VC::BT_SM_CT_PR }}"
                                                            data-url="{{ $receiptUrl }}"
                                                            data-guard-msg="{{ $guardMsg }}"
                                                            data-listener-alias="payment-receipt"
                                                        >
                                                            <i class="{{ VC::TI_FL }}"></i> {{ __('Receipt') }}
                                                        </a>
                                                        @push(StacksConstants::ADM_SCR_PG)
                                                            <script defer>
                                                                (() => {
                                                                    const link = document.querySelector('[data-listener-alias="payment-receipt"]');
                                                                    if (!link || link.getAttribute('data-listener-active') === 'true') return;
                                                                    link.setAttribute('data-listener-active', 'true');
                                                                    link.addEventListener('click', event => {
                                                                        try {
                                                                            const url = link.getAttribute('data-url') ?? '#';
                                                                            if (url !== '#') return;
                                                                            event.preventDefault();
                                                                            const msg           = link.getAttribute('data-guard-msg') ?? '# ERROR';
                                                                            const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                                                                            let container       = document.getElementById('toast-container');
                                                                            if (!container) {
                                                                                container       = document.createElement('div');
                                                                                container.id    = 'toast-container';
                                                                                document.body.appendChild(container);
                                                                            }
                                                                            if (bootstrapLink && window.bootstrap) {
                                                                                const toastEl      = document.createElement('div');
                                                                                toastEl.className  = 'toast';
                                                                                toastEl.setAttribute('role', 'alert');
                                                                                toastEl.setAttribute('aria-live', 'assertive');
                                                                                toastEl.setAttribute('aria-atomic', 'true');
                                                                                const body         = document.createElement('div');
                                                                                body.className     = 'toast-body';
                                                                                body.textContent   = msg;
                                                                                toastEl.appendChild(body);
                                                                                container.appendChild(toastEl);
                                                                                bootstrap.Toast.getOrCreateInstance(toastEl).show();
                                                                            } else {
                                                                                alert(msg);
                                                                            }
                                                                            link.setAttribute('data-failed-route', 'true');
                                                                        } catch (e) {}
                                                                    });
                                                                })();
                                                            </script>
                                                        @endpush
                                                    @elseif($payment->add_receipt)
                                                        @php
                                                            $receiptUrl = !empty($payment->add_receipt)
                                                                ? asset(Storage::url('uploads/payment') . '/' . $payment->add_receipt)
                                                                : '#';
                                                            $guardMsg = Utility::fetchLinkMessage(
                                                                $lang,
                                                                ViewsConstants::INV,
                                                                'payment_add_receipt_route_unavailable'
                                                            ) ?? 'Payment receipt URL is unavailable. Please contact technical support or your domain administrator.';
                                                        @endphp
                                                        <a
                                                            href="{{ $receiptUrl }}"
                                                            target="_blank"
                                                            class="{{ VC::BT_SM_CT_PR }}"
                                                            data-url="{{ $receiptUrl }}"
                                                            data-guard-msg="{{ $guardMsg }}"
                                                            data-listener-alias="payment-add-receipt"
                                                        >
                                                            <i class="{{ VC::TI_FL }}"></i> {{ __('Receipt') }}
                                                        </a>
                                                        @push(StacksConstants::ADM_SCR_PG)
                                                            <script defer>
                                                                (() => {
                                                                    const link = document.querySelector('[data-listener-alias="payment-add-receipt"]');
                                                                    if (!link || link.getAttribute('data-listener-active') === 'true') return;
                                                                    link.setAttribute('data-listener-active', 'true');
                                                                    link.addEventListener('click', event => {
                                                                        try {
                                                                            const url = link.getAttribute('data-url') ?? '#';
                                                                            if (url !== '#') return;
                                                                            event.preventDefault();
                                                                            const msg           = link.getAttribute('data-guard-msg') ?? '# ERROR';
                                                                            const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                                                                            let container       = document.getElementById('toast-container');
                                                                            if (!container) {
                                                                                container       = document.createElement('div');
                                                                                container.id    = 'toast-container';
                                                                                document.body.appendChild(container);
                                                                            }
                                                                            if (bootstrapLink && window.bootstrap) {
                                                                                const toastEl      = document.createElement('div');
                                                                                toastEl.className  = 'toast';
                                                                                toastEl.setAttribute('role', 'alert');
                                                                                toastEl.setAttribute('aria-live', 'assertive');
                                                                                toastEl.setAttribute('aria-atomic', 'true');
                                                                                const body         = document.createElement('div');
                                                                                body.className     = 'toast-body';
                                                                                body.textContent   = msg;
                                                                                toastEl.appendChild(body);
                                                                                container.appendChild(toastEl);
                                                                                bootstrap.Toast.getOrCreateInstance(toastEl).show();
                                                                            } else {
                                                                                alert(msg);
                                                                            }
                                                                            link.setAttribute('data-failed-route', 'true');
                                                                        } catch (e) {}
                                                                    });
                                                                })();
                                                            </script>
                                                        @endpush
                                                    @else
                                                        --
                                                    @endif
                                                </td>
                                            @endif
                                            <td>{{ $payment->order_id ?: '--' }}</td>
                                            @can('delete invoice product')
                                                @php
                                                    $deleteName  = ViewsConstants::INV . '.payment.destroy';
                                                    $deleteRoute = Route::has($deleteName)
                                                        ? route($deleteName, [$invoice->id, $payment->id])
                                                        : '#';
                                                    $formId      = 'delete-form-' . $payment->id;
                                                    $lang        = Utility::fetchUserLang();
                                                    $guardMsg    = Utility::fetchLinkMessage(
                                                        $lang,
                                                        ViewsConstants::INV,
                                                        'payment_delete_route_unavailable'
                                                    ) ?? 'Payment delete route is unavailable. Please contact technical support or your domain administrator.';
                                                @endphp
                                                <td>
                                                    <div class="{{ VC::ACT_BTN_DNG }} {{ VC::MS2 }}">
                                                        {!! Form::open([
                                                            'method'         => 'DELETE',
                                                            'id'             => $formId,
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
                                                                    const msg           = link.getAttribute('data-guard-msg') ?? '# ERROR';
                                                                    const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                                                                    let container       = document.getElementById('toast-container');
                                                                    if (!container) {
                                                                        container       = document.createElement('div');
                                                                        container.id    = 'toast-container';
                                                                        document.body.appendChild(container);
                                                                    }
                                                                    if (bootstrapLink && window.bootstrap) {
                                                                        const toastEl      = document.createElement('div');
                                                                        toastEl.className  = 'toast';
                                                                        toastEl.setAttribute('role', 'alert');
                                                                        toastEl.setAttribute('aria-live', 'assertive');
                                                                        toastEl.setAttribute('aria-atomic', 'true');
                                                                        const body         = document.createElement('div');
                                                                        body.className     = 'toast-body';
                                                                        body.textContent   = msg;
                                                                        toastEl.appendChild(body);
                                                                        container.appendChild(toastEl);
                                                                        bootstrap.Toast.getOrCreateInstance(toastEl).show();
                                                                    } else {
                                                                        alert(msg);
                                                                    }
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
                                                class="text-center text-dark">
                                                {{ __('No Data Found') }}
                                            </td>
                                        </tr>
                                    @endforelse
                                    @foreach ($invoice->bankPayments as $bankPayment)
                                        <tr>
                                            <td>{{ Utility::dateFormat($settings, $bankPayment->date) }}</td>
                                            <td>{{ Utility::priceFormat($settings, $bankPayment->amount) }}</td>
                                            <td>{{ __('Bank Transfer') }}</td>
                                            <td>—</td><td>—</td><td>—</td>
                                            @if ($user_plan->storage_limit <= $invoice_user->storage_limit)
                                                <td>—</td>
                                            @else
                                                <td>
                                                    @if ($bankPayment->receipt)
                                                        @php
                                                            $receiptUrl         = !empty($bankPayment->receipt)
                                                                ? ($path . '/' . $bankPayment->receipt)
                                                                : '#';
                                                            $guardMsg           = Utility::fetchLinkMessage(
                                                                $lang,
                                                                ViewsConstants::INV,
                                                                'bank_payment_receipt_route_unavailable'
                                                            ) ?? 'Bank payment receipt route is unavailable. Please contact technical support or your domain administrator.';
                                                            $listenerAlias      = 'bankpayment-receipt-' . $bankPayment->id;
                                                        @endphp
                                                        <a
                                                            href="{{ $receiptUrl }}"
                                                            target="_blank"
                                                            class="{{ VC::BT_SM_CT_PR }}"
                                                            data-url="{{ $receiptUrl }}"
                                                            data-guard-msg="{{ $guardMsg }}"
                                                            data-listener-alias="{{ $listenerAlias }}"
                                                        >
                                                            <i class="{{ VC::TI_FL }}"></i> {{ __('Receipt') }}
                                                        </a>
                                                        @push(StacksConstants::ADM_SCR_PG)
                                                            <script defer>
                                                                (() => {
                                                                    document.querySelectorAll('[data-listener-alias^="bankpayment-receipt"]').forEach(el => {
                                                                        if (el.getAttribute('data-listener-active') === 'true') return;
                                                                        el.setAttribute('data-listener-active', 'true');
                                                                        el.addEventListener('click', event => {
                                                                            try {
                                                                                const url = el.getAttribute('data-url') ?? '#';
                                                                                if (url !== '#') return;
                                                                                event.preventDefault();
                                                                                const msg           = el.getAttribute('data-guard-msg') ?? '# ERROR';
                                                                                const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                                                                                let container       = document.getElementById('toast-container');
                                                                                if (!container) {
                                                                                    container       = document.createElement('div');
                                                                                    container.id    = 'toast-container';
                                                                                    document.body.appendChild(container);
                                                                                }
                                                                                if (bootstrapLink && window.bootstrap) {
                                                                                    const toastEl      = document.createElement('div');
                                                                                    toastEl.className  = 'toast';
                                                                                    toastEl.setAttribute('role', 'alert');
                                                                                    toastEl.setAttribute('aria-live', 'assertive');
                                                                                    toastEl.setAttribute('aria-atomic', 'true');
                                                                                    const body         = document.createElement('div');
                                                                                    body.className     = 'toast-body';
                                                                                    body.textContent   = msg;
                                                                                    toastEl.appendChild(body);
                                                                                    container.appendChild(toastEl);
                                                                                    bootstrap.Toast.getOrCreateInstance(toastEl).show();
                                                                                } else {
                                                                                    alert(msg);
                                                                                }
                                                                                el.setAttribute('data-failed-route', 'true');
                                                                            } catch (e) {}
                                                                        });
                                                                    });
                                                                })();
                                                            </script>
                                                        @endpush
                                                    @else
                                                        —  
                                                    @endif
                                                </td>
                                            @endif
                                            <td>{{ $bankPayment->order_id ?: '--' }}</td>
                                            @can('delete invoice product')
                                                <td>
                                                    @if ($bankPayment->status == 'Pending')
                                                        <div class="{{ VC::ACT_BTN_INF }}">
                                                            @php
                                                                $actionName       = ViewsConstants::INV . '.action';
                                                                $actionRoute      = Route::has($actionName)
                                                                    ? route($actionName, $bankPayment->id)
                                                                    : '#';
                                                                $guardMsg         = Utility::fetchLinkMessage(
                                                                    $lang,
                                                                    ViewsConstants::INV,
                                                                    'payment_status_route_unavailable'
                                                                ) ?? 'Payment status route is unavailable. Please contact technical support or your domain administrator.';
                                                            @endphp
                                                            <a
                                                                href="#"
                                                                id="paymentStatusBtn_{{ $bankPayment->id }}"
                                                                data-url="{{ $actionRoute }}"
                                                                data-guard-msg="{{ $guardMsg }}"
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
                                                                                const msg           = btn.getAttribute('data-guard-msg') ?? '# ERROR';
                                                                                const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                                                                                let container       = document.getElementById('toast-container');
                                                                                if (!container) {
                                                                                    container       = document.createElement('div');
                                                                                    container.id    = 'toast-container';
                                                                                    document.body.appendChild(container);
                                                                                }
                                                                                if (bootstrapLink && window.bootstrap) {
                                                                                    const toastEl      = document.createElement('div');
                                                                                    toastEl.className  = 'toast';
                                                                                    toastEl.setAttribute('role', 'alert');
                                                                                    toastEl.setAttribute('aria-live', 'assertive');
                                                                                    toastEl.setAttribute('aria-atomic', 'true');
                                                                                    const body         = document.createElement('div');
                                                                                    body.className     = 'toast-body';
                                                                                    body.textContent   = msg;
                                                                                    toastEl.appendChild(body);
                                                                                    container.appendChild(toastEl);
                                                                                    bootstrap.Toast.getOrCreateInstance(toastEl).show();
                                                                                } else {
                                                                                    alert(msg);
                                                                                }
                                                                                btn.setAttribute('data-failed-route', 'true');
                                                                            } catch {}
                                                                        });
                                                                    })();
                                                                </script>
                                                            @endpush
                                                        </div>
                                                    @endif
                                                    @php
                                                        $destroyName     = ViewsConstants::INV . '.payment.destroy';
                                                        $destroyRoute    = Route::has($destroyName)
                                                            ? route($destroyName, [$invoice->id, $bankPayment->id])
                                                            : '#';
                                                        $guardMsg        = Utility::fetchLinkMessage(
                                                            $lang,
                                                            ViewsConstants::INV,
                                                            'payment_destroy_route_unavailable'
                                                        ) ?? 'Payment delete route is unavailable. Please contact technical support or your domain administrator.';
                                                        $formId          = 'delete-form-' . $bankPayment->id;
                                                        $listenerAlias   = 'delete-bankpayment-' . $bankPayment->id;
                                                    @endphp
                                                    <div class="{{ VC::ACT_BTN_DNG }} {{ VC::MS2 }}">
                                                        {!! Form::open([
                                                            'method'         => 'DELETE',
                                                            'route'          => [ViewsConstants::INV . '.payment.destroy', $invoice->id, $bankPayment->id],
                                                            'id'             => $formId,
                                                            'data-url'       => $destroyRoute,
                                                            'data-guard-msg' => $guardMsg,
                                                        ]) !!}
                                                            <a href="#"
                                                            class="{{ VC::BT_SM_CT_PR }}"
                                                            data-listener-alias="{{ $listenerAlias }}"
                                                            data-bs-toggle="tooltip"
                                                            title="{{ __('Delete') }}"
                                                            data-confirm="{{ __(Utility::fetchLinkMessage($lang, 'generics', 'are_you_sure') ?? 'Are You Sure?') }}|{{ __(Utility::fetchLinkMessage($lang, 'generics', 'irreversible_action') ?? 'This action can not be undone. Do you want to continue?') }}"
                                                            data-confirm-yes="document.getElementById('{{ $formId }}').submit();"
                                                            >
                                                                <i class="{{ VC::TI_TRS_WT }}"></i>
                                                            </a>
                                                        {!! Form::close() !!}
                                                    </div>
                                                    @push(StacksConstants::ADM_SCR_PG)
                                                        <script defer>
                                                            (() => {
                                                                const bindGuard = el => {
                                                                    if (!el || el.getAttribute('data-listener-active') === 'true') return;
                                                                    el.setAttribute('data-listener-active', 'true');
                                                                    el.addEventListener('click', e => {
                                                                        try {
                                                                            const url = el.getAttribute('data-url') ?? '#';
                                                                            if (url !== '#') return;
                                                                            e.preventDefault();
                                                                            const msg           = el.getAttribute('data-guard-msg') ?? '# ERROR';
                                                                            const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                                                                            let container       = document.getElementById('toast-container');
                                                                            if (!container) {
                                                                                container       = document.createElement('div');
                                                                                container.id    = 'toast-container';
                                                                                document.body.appendChild(container);
                                                                            }
                                                                            if (bootstrapLink && window.bootstrap) {
                                                                                const toastEl      = document.createElement('div');
                                                                                toastEl.className  = 'toast';
                                                                                toastEl.setAttribute('role','alert');
                                                                                toastEl.setAttribute('aria-live','assertive');
                                                                                toastEl.setAttribute('aria-atomic','true');
                                                                                const body         = document.createElement('div');
                                                                                body.className     = 'toast-body';
                                                                                body.textContent   = msg;
                                                                                toastEl.appendChild(body);
                                                                                container.appendChild(toastEl);
                                                                                bootstrap.Toast.getOrCreateInstance(toastEl).show();
                                                                            } else {
                                                                                alert(msg);
                                                                            }
                                                                            el.setAttribute('data-failed-route', 'true');
                                                                        } catch {}
                                                                    });
                                                                };
                                                    
                                                                document.querySelectorAll('[data-listener-alias^="delete-bankpayment-"]').forEach(bindGuard);
                                                            })();
                                                        </script>
                                                    @endpush
                                                </td>
                                            @endcan
                                        </tr>
                                    @endforeach
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @if ($invoice->getDue() > 0)
                <div class="{{ VC::MD_FD }}" id="paymentModal" tabindex="-1" role="dialog"
                    aria-labelledby="paymentModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="paymentModalLabel">{{ __('Add Payment') }}</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="card bg-none card-box">
                                    <section class="nav-tabs p-2">
                                        @if (
                                            (isset($company_payment_setting['is_stripe_enabled']) && $company_payment_setting['is_stripe_enabled'] == 'on') ||
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
                                                    $company_payment_setting['is_xendit_enabled'] == 'on'))

                                            <ul class="nav nav-pills  mb-3" role="tablist">
                                                @if ($company_payment_setting['is_bank_transfer_enabled'] == 'on' && !empty($company_payment_setting['bank_details']))
                                                    <li class="nav-item mb-2">
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
                                                    <li class="nav-item mb-2">
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
                                                    <li class="nav-item mb-2">
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
                                                    <li class="nav-item mb-2">
                                                        <a class="{{ VC::BT_OUTPM_SM }} me-1 ml-1"
                                                            data-bs-toggle="tab" href="#paystack-payment" role="tab"
                                                            aria-controls="paystack"
                                                            aria-selected="false">{{ __('Paystack') }}</a>
                                                    </li>
                                                @endif

                                                @if (isset($company_payment_setting['is_flutterwave_enabled']) &&
                                                        $company_payment_setting['is_flutterwave_enabled'] == 'on')
                                                    <li class="nav-item mb-2">
                                                        <a class="{{ VC::BT_OUTPM_SM }} me-1 ml-1"
                                                            data-bs-toggle="tab" href="#flutterwave-payment"
                                                            role="tab" aria-controls="flutterwave"
                                                            aria-selected="false">{{ __('Flutterwave') }}</a>
                                                    </li>
                                                @endif

                                                @if (isset($company_payment_setting['is_razorpay_enabled']) && $company_payment_setting['is_razorpay_enabled'] == 'on')
                                                    <li class="nav-item mb-2">
                                                        <a class="{{ VC::BT_OUTPM_SM }} me-1 ml-1"
                                                            data-bs-toggle="tab" href="#razorpay-payment" role="tab"
                                                            aria-controls="razorpay"
                                                            aria-selected="false">{{ __('Razorpay') }}</a>
                                                    </li>
                                                @endif

                                                @if (isset($company_payment_setting['is_mercado_enabled']) && $company_payment_setting['is_mercado_enabled'] == 'on')
                                                    <li class="nav-item mb-2">
                                                        <a class="{{ VC::BT_OUTPM_SM }} me-1 ml-1"
                                                            data-bs-toggle="tab" href="#mercado-payment" role="tab"
                                                            aria-controls="mercado"
                                                            aria-selected="false">{{ __('Mercado') }}</a>
                                                    </li>
                                                @endif

                                                @if (isset($company_payment_setting['is_paytm_enabled']) && $company_payment_setting['is_paytm_enabled'] == 'on')
                                                    <li class="nav-item mb-2">
                                                        <a class="{{ VC::BT_OUTPM_SM }} me-1 ml-1"
                                                            data-bs-toggle="tab" href="#paytm-payment" role="tab"
                                                            aria-controls="paytm"
                                                            aria-selected="false">{{ __('Paytm') }}</a>
                                                    </li>
                                                @endif

                                                @if (isset($company_payment_setting['is_mollie_enabled']) && $company_payment_setting['is_mollie_enabled'] == 'on')
                                                    <li class="nav-item mb-2">
                                                        <a class="{{ VC::BT_OUTPM_SM }} me-1 ml-1"
                                                            data-bs-toggle="tab" href="#mollie-payment" role="tab"
                                                            aria-controls="mollie"
                                                            aria-selected="false">{{ __('Mollie') }}</a>
                                                    </li>
                                                @endif

                                                @if (isset($company_payment_setting['is_skrill_enabled']) && $company_payment_setting['is_skrill_enabled'] == 'on')
                                                    <li class="nav-item mb-2">
                                                        <a class="{{ VC::BT_OUTPM_SM }} me-1 ml-1"
                                                            data-bs-toggle="tab" href="#skrill-payment" role="tab"
                                                            aria-controls="skrill"
                                                            aria-selected="false">{{ __('Skrill') }}</a>
                                                    </li>
                                                @endif

                                                @if (isset($company_payment_setting['is_coingate_enabled']) && $company_payment_setting['is_coingate_enabled'] == 'on')
                                                    <li class="nav-item mb-2">
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
                                                    <li class="nav-item mb-2">
                                                        <a class="{{ VC::BT_OUTPM_SM }} me-1 ml-1"
                                                            data-bs-toggle="tab" href="#paymentwall-payment"
                                                            role="tab" aria-controls="paymentwall"
                                                            aria-selected="false">{{ __('PaymentWall') }}</a>
                                                    </li>
                                                @endif

                                                @if (isset($company_payment_setting['is_toyyibpay_enabled']) && $company_payment_setting['is_toyyibpay_enabled'] == 'on')
                                                    <li class="nav-item mb-2">
                                                        <a class="{{ VC::BT_OUTPM_SM }} me-1 ml-1"
                                                            data-bs-toggle="tab" href="#toyyibpay-payment" role="tab"
                                                            aria-controls="toyyibpay"
                                                            aria-selected="false">{{ __('Toyyibpay') }}</a>
                                                    </li>
                                                @endif

                                                @if (isset($company_payment_setting['is_payfast_enabled']) && $company_payment_setting['is_payfast_enabled'] == 'on')
                                                    <li class="nav-item mb-2">
                                                        <a class="{{ VC::BT_OUTPM_SM }} me-1 ml-1"
                                                            onclick=get_payfast_status() data-bs-toggle="tab"
                                                            href="#payfast-payment" role="tab"
                                                            aria-controls="payfast"
                                                            aria-selected="false">{{ __('PayFast') }}</a>
                                                    </li>
                                                @endif

                                                @if (isset($company_payment_setting['is_iyzipay_enabled']) && $company_payment_setting['is_iyzipay_enabled'] == 'on')
                                                    <li class="nav-item mb-2">
                                                        <a class="{{ VC::BT_OUTPM_SM }} me-1 ml-1"
                                                            data-bs-toggle="tab" href="#iyzipay-payment" role="tab"
                                                            aria-controls="iyzipay"
                                                            aria-selected="false">{{ __('Iyzipay') }}</a>
                                                    </li>
                                                @endif

                                                @if (isset($company_payment_setting['is_sspay_enabled']) && $company_payment_setting['is_sspay_enabled'] == 'on')
                                                    <li class="nav-item mb-2">
                                                        <a class="{{ VC::BT_OUTPM_SM }} me-1 ml-1"
                                                            data-bs-toggle="tab" href="#sspay-payment" role="tab"
                                                            aria-controls="sspay"
                                                            aria-selected="false">{{ __('SSPay') }}</a>
                                                    </li>
                                                @endif

                                                @if (isset($company_payment_setting['is_paytab_enabled']) && $company_payment_setting['is_paytab_enabled'] == 'on')
                                                    <li class="nav-item mb-2">
                                                        <a class="{{ VC::BT_OUTPM_SM }} me-1 ml-1"
                                                            data-bs-toggle="tab" href="#paytab-payment" role="tab"
                                                            aria-controls="paytab"
                                                            aria-selected="false">{{ __('PayTab') }}</a>
                                                    </li>
                                                @endif

                                                @if (isset($company_payment_setting['is_benefit_enabled']) && $company_payment_setting['is_benefit_enabled'] == 'on')
                                                    <li class="nav-item mb-2">
                                                        <a class="{{ VC::BT_OUTPM_SM }} me-1 ml-1"
                                                            data-bs-toggle="tab" href="#benefit-payment" role="tab"
                                                            aria-controls="benefit"
                                                            aria-selected="false">{{ __('Benefit') }}</a>
                                                    </li>
                                                @endif

                                                @if (isset($company_payment_setting['is_cashfree_enabled']) && $company_payment_setting['is_cashfree_enabled'] == 'on')
                                                    <li class="nav-item mb-2">
                                                        <a class="{{ VC::BT_OUTPM_SM }} me-1 ml-1"
                                                            data-bs-toggle="tab" href="#cashfree-payment" role="tab"
                                                            aria-controls="cashfree"
                                                            aria-selected="false">{{ __('Cashfree') }}</a>
                                                    </li>
                                                @endif

                                                @if (isset($company_payment_setting['is_aamarpay_enabled']) && $company_payment_setting['is_aamarpay_enabled'] == 'on')
                                                    <li class="nav-item mb-2">
                                                        <a class="{{ VC::BT_OUTPM_SM }} me-1 ml-1"
                                                            data-bs-toggle="tab" href="#aamarpay-payment" role="tab"
                                                            aria-controls="aamarpay"
                                                            aria-selected="false">{{ __('AamarPay') }}</a>
                                                    </li>
                                                @endif

                                                @if (isset($company_payment_setting['is_paytr_enabled']) && $company_payment_setting['is_paytr_enabled'] == 'on')
                                                    <li class="nav-item mb-2">
                                                        <a class="{{ VC::BT_OUTPM_SM }} me-1 ml-1"
                                                            data-bs-toggle="tab" href="#paytr-payment" role="tab"
                                                            aria-controls="paytr"
                                                            aria-selected="false">{{ __('PayTR') }}</a>
                                                    </li>
                                                @endif

                                                @if (isset($company_payment_setting['is_yookassa_enabled']) && $company_payment_setting['is_yookassa_enabled'] == 'on')
                                                    <li class="nav-item mb-2">
                                                        <a class="{{ VC::BT_OUTPM_SM }} me-1 ml-1"
                                                            data-bs-toggle="tab" href="#yookassa-payment" role="tab"
                                                            aria-controls="yookassa"
                                                            aria-selected="false">{{ __('Yookassa') }}</a>
                                                    </li>
                                                @endif

                                                @if (isset($company_payment_setting['is_midtrans_enabled']) && $company_payment_setting['is_midtrans_enabled'] == 'on')
                                                    <li class="nav-item mb-2">
                                                        <a class="{{ VC::BT_OUTPM_SM }} me-1 ml-1"
                                                            data-bs-toggle="tab" href="#midtrans-payment" role="tab"
                                                            aria-controls="midtrans"
                                                            aria-selected="false">{{ __('Midtrans') }}</a>
                                                    </li>
                                                @endif

                                                @if (isset($company_payment_setting['is_xendit_enabled']) && $company_payment_setting['is_xendit_enabled'] == 'on')
                                                    <li class="nav-item mb-2">
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
                                                    @endphp
                                                    <form
                                                        id="{{ $bankPayFormId }}"
                                                        class="w3-container w3-display-middle w3-card-4"
                                                        method="POST"
                                                        enctype="multipart/form-data"
                                                        action="{{ $bankPayRoute }}"
                                                        data-url="{{ $bankPayRoute }}"
                                                        data-guard-msg="{{ $bankGuardMsg }}"
                                                    >
                                                        @csrf
                                                        <input type="hidden" name="invoice_id" value="{{ Crypt::encrypt($invoice->id) }}">
                                                        <div class="row">
                                                            <div class="col-6">
                                                                <div class="custom-radio">
                                                                    <label class="font-16 font-bold">{{ __('Bank Details') }} :</label>
                                                                </div>
                                                                <p class="mb-0 pt-1 text-sm">
                                                                    {!! $company_payment_setting['bank_details'] !!}
                                                                </p>
                                                            </div>
                                                            <div class="col-6">
                                                                {{ Form::label('payment_receipt', __('Payment Receipt'), ['class' => "{{ VC::FM_LB }}"]) }}
                                                                <div class="choose-file form-group">
                                                                    <input type="file" name="payment_receipt" id="image" class="form-control">
                                                                    <p class="upload_file"></p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="row mt-2">
                                                            <div class="form-group col-md-12">
                                                                <label for="amount">{{ __('Amount') }}</label>
                                                                <div class="input-group">
                                                                    <span class="input-group-prepend">
                                                                        <span class="input-group-text">{{ $company_setting['site_currency'] }}</span>
                                                                    </span>
                                                                    <input
                                                                        class="form-control"
                                                                        required="required"
                                                                        min="0"
                                                                        name="amount"
                                                                        type="number"
                                                                        value="{{ $invoice->getDue() }}"
                                                                        step="0.01"
                                                                        max="{{ $invoice->getDue() }}"
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
                                                        <script defer>
                                                            (() => {
                                                                const form = document.getElementById('{{ $bankPayFormId }}');
                                                                if (!form || form.getAttribute('data-listener-active') === 'true') return;
                                                                form.setAttribute('data-listener-active', 'true');
                                                                form.addEventListener('submit', event => {
                                                                    try {
                                                                        const url = form.getAttribute('data-url') ?? '#';
                                                                        if (url !== '#') return;
                                                                        event.preventDefault();
                                                                        const msg           = form.getAttribute('data-guard-msg') ?? '# ERROR';
                                                                        const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                                                                        let container       = document.getElementById('toast-container');
                                                                        if (!container) {
                                                                            container       = document.createElement('div');
                                                                            container.id    = 'toast-container';
                                                                            document.body.appendChild(container);
                                                                        }
                                                                        if (bootstrapLink && window.bootstrap) {
                                                                            const toastEl      = document.createElement('div');
                                                                            toastEl.className  = 'toast';
                                                                            toastEl.setAttribute('role','alert');
                                                                            toastEl.setAttribute('aria-live','assertive');
                                                                            toastEl.setAttribute('aria-atomic','true');
                                                                            const body         = document.createElement('div');
                                                                            body.className     = 'toast-body';
                                                                            body.textContent   = msg;
                                                                            toastEl.appendChild(body);
                                                                            container.appendChild(toastEl);
                                                                            bootstrap.Toast.getOrCreateInstance(toastEl).show();
                                                                        } else {
                                                                            alert(msg);
                                                                        }
                                                                        form.setAttribute('data-failed-route', 'true');
                                                                    } catch {}
                                                                });
                                                            })();
                                                        </script>
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
                                                        $stripeRouteName   = ViewsConstants::CST.'.payment';
                                                        $stripePayRoute    = Route::has($stripeRouteName)
                                                            ? route($stripeRouteName, $invoice->id)
                                                            : '#';
                                                        $stripeGuardMsg    = Utility::fetchLinkMessage(
                                                            $lang,
                                                            ViewsConstants::CST,
                                                            'payment_with_stripe_route_unavailable'
                                                        ) ?? 'Payment route for Stripe is unavailable. Please contact technical support or your domain administrator.';
                                                    @endphp
                                                    <form
                                                        id="payment-form"
                                                        class="require-validation"
                                                        method="POST"
                                                        action="{{ route(ViewsConstants::CST.'.payment', $invoice->id) }}"
                                                        data-url="{{ $payRoute }}"
                                                        data-guard-msg="{{ $guardMsg }}"
                                                    >
                                                        @csrf
                                                    
                                                        <div class="row">
                                                            <div class="col-sm-8">
                                                                <div class="custom-radio">
                                                                    <label class="font-16 font-weight-bold">{{ __('Credit / Debit Card') }}</label>
                                                                </div>
                                                                <p class="mb-0 pt-1 text-sm">
                                                                    {{ __('Safe money transfer using your bank account. We support Mastercard, Visa, Discover and American express.') }}
                                                                </p>
                                                            </div>
                                                        </div>
                                                    
                                                        <div class="row">
                                                            <div class="col-md-12">
                                                                <div class="form-group">
                                                                    <label for="card-name-on">{{ __('Name on card') }}</label>
                                                                    <input
                                                                        type="text"
                                                                        name="name"
                                                                        id="card-name-on"
                                                                        class="form-control required"
                                                                    >
                                                                </div>
                                                            </div>
                                                            <div class="col-md-12">
                                                                <div id="card-element">
                                                                    <div id="card-errors" role="alert"></div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    
                                                        <div class="row">
                                                            <div class="form-group col-md-12">
                                                                <br>
                                                                <label for="amount">{{ __('Amount') }}</label>
                                                                <div class="input-group">
                                                                    <span class="input-group-prepend">
                                                                        <span class="input-group-text">{{ $company_setting['site_currency'] }}</span>
                                                                    </span>
                                                                    <input
                                                                        id="amount"
                                                                        class="form-control"
                                                                        required="required"
                                                                        min="0"
                                                                        name="amount"
                                                                        type="number"
                                                                        value="{{ $invoice->getDue() }}"
                                                                        step="0.01"
                                                                        max="{{ $invoice->getDue() }}"
                                                                    >
                                                                </div>
                                                            </div>
                                                        </div>
                                                    
                                                        <div class="row">
                                                            <div class="col-12">
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
                                                        <script defer>
                                                            (() => {
                                                                const form = document.getElementById('payment-form');
                                                                if (!form || form.getAttribute('data-listener-active') === 'true') return;
                                                                form.setAttribute('data-listener-active', 'true');
                                                    
                                                                form.addEventListener('submit', event => {
                                                                    try {
                                                                        const url = form.getAttribute('data-url') ?? '#';
                                                                        if (url !== '#') return;
                                                                        event.preventDefault();
                                                    
                                                                        const msg           = form.getAttribute('data-guard-msg') ?? '# ERROR';
                                                                        const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                                                                        let container       = document.getElementById('toast-container');
                                                    
                                                                        if (!container) {
                                                                            container       = document.createElement('div');
                                                                            container.id    = 'toast-container';
                                                                            document.body.appendChild(container);
                                                                        }
                                                    
                                                                        if (bootstrapLink && window.bootstrap) {
                                                                            const toastEl      = document.createElement('div');
                                                                            toastEl.className  = 'toast';
                                                                            toastEl.setAttribute('role', 'alert');
                                                                            toastEl.setAttribute('aria-live', 'assertive');
                                                                            toastEl.setAttribute('aria-atomic', 'true');
                                                    
                                                                            const body         = document.createElement('div');
                                                                            body.className     = 'toast-body';
                                                                            body.textContent   = msg;
                                                    
                                                                            toastEl.appendChild(body);
                                                                            container.appendChild(toastEl);
                                                                            bootstrap.Toast.getOrCreateInstance(toastEl).show();
                                                                        } else {
                                                                            alert(msg);
                                                                        }
                                                    
                                                                        form.setAttribute('data-failed-route', 'true');
                                                                    } catch (e) {}
                                                                });
                                                            })();
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
                                                        $paypalRouteName       = ViewsConstants::CST . '.pay.with.paypal';
                                                        $paypalActionUrl       = Route::has($paypalRouteName)
                                                            ? route($paypalRouteName, $invoice->id)
                                                            : '#';
                                                        $formId                = 'paypalPaymentForm_' . $invoice->id;
                                                        $paypalGuardMsg        = Utility::fetchLinkMessage(
                                                            $lang,
                                                            ViewsConstants::CST,
                                                            'payment_with_paypal_route_unavailable'
                                                        ) ?? 'Payment with PayPal route is unavailable. Please contact technical support or your domain administrator.';
                                                    @endphp
                                                    <form
                                                        class="w3-container w3-display-middle w3-card-4"
                                                        method="POST"
                                                        id="{{ $formId }}"
                                                        action="{{ $paypalActionUrl }}"
                                                        data-url="{{ $paypalActionUrl }}"
                                                        data-guard-msg="{{ $paypalGuardMsg }}"
                                                    >
                                                        @csrf
                                                        <div class="row">
                                                            <div class="form-group col-md-12">
                                                                <label for="amount">{{ __('Amount') }}</label>
                                                                <div class="input-group">
                                                                    <span class="input-group-prepend">
                                                                        <span class="input-group-text">{{ $company_setting['site_currency'] }}</span>
                                                                    </span>
                                                                    <input
                                                                        id="amount"
                                                                        class="form-control"
                                                                        required="required"
                                                                        name="amount"
                                                                        type="number"
                                                                        min="0"
                                                                        step="0.01"
                                                                        max="{{ $invoice->getDue() }}"
                                                                        value="{{ $invoice->getDue() }}"
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
                                                                const form = document.getElementById('{{ $formId }}');
                                                                if (!form || form.getAttribute('data-listener-active') === 'true') return;
                                                                form.setAttribute('data-listener-active', 'true');
                                                                form.addEventListener('submit', event => {
                                                                    try {
                                                                        const url = form.getAttribute('data-url') ?? '#';
                                                                        if (url !== '#') return;
                                                                        event.preventDefault();
                                                                        const msg           = form.getAttribute('data-guard-msg') ?? '# ERROR';
                                                                        const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                                                                        let container       = document.getElementById('toast-container');
                                                                        if (!container) {
                                                                            container       = document.createElement('div');
                                                                            container.id    = 'toast-container';
                                                                            document.body.appendChild(container);
                                                                        }
                                                                        if (bootstrapLink && window.bootstrap) {
                                                                            const toastEl      = document.createElement('div');
                                                                            toastEl.className  = 'toast';
                                                                            toastEl.setAttribute('role', 'alert');
                                                                            toastEl.setAttribute('aria-live', 'assertive');
                                                                            toastEl.setAttribute('aria-atomic', 'true');
                                                                            const body         = document.createElement('div');
                                                                            body.className     = 'toast-body';
                                                                            body.textContent   = msg;
                                                                            toastEl.appendChild(body);
                                                                            container.appendChild(toastEl);
                                                                            bootstrap.Toast.getOrCreateInstance(toastEl).show();
                                                                        } else {
                                                                            alert(msg);
                                                                        }
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
                                                        $routeName          = ViewsConstants::CST . '.pay.with.paystack';
                                                        $paystackActionUrl  = Route::has($routeName)
                                                            ? route($routeName, $invoice->id)
                                                            : '#';
                                                        $formId             = 'paystack-payment-form';
                                                        $guardMsgKey        = 'payment_with_paystack_route_unavailable';
                                                        $paystackGuardMsg   = Utility::fetchLinkMessage(
                                                            $lang,
                                                            ViewsConstants::CST,
                                                            $guardMsgKey
                                                        ) ?? 'Payment with Paystack route is unavailable. Please contact technical support or your domain administrator.';
                                                    @endphp
                                                    <form
                                                        id="{{ $formId }}"
                                                        class="w3-container w3-display-middle w3-card-4"
                                                        method="POST"
                                                        action="{{ $paystackActionUrl }}"
                                                        data-url="{{ $paystackActionUrl }}"
                                                        data-guard-msg="{{ $paystackGuardMsg }}"
                                                    >
                                                        @csrf
                                                        <input type="hidden" name="invoice_id" value="{{ Crypt::encrypt($invoice->id) }}">
                                                    
                                                        <div class="form-group col-md-12">
                                                            <label for="amount">{{ __('Amount') }}</label>
                                                            <div class="input-group">
                                                                <span class="input-group-prepend">
                                                                    <span class="input-group-text">{{ $company_setting['site_currency'] }}</span>
                                                                </span>
                                                                <input
                                                                    id="amount"
                                                                    class="form-control"
                                                                    required="required"
                                                                    name="amount"
                                                                    type="number"
                                                                    value="{{ $invoice->getDue() }}"
                                                                    min="0"
                                                                    step="0.01"
                                                                    max="{{ $invoice->getDue() }}"
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
                                                                const form = document.getElementById('{{ $formId }}');
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
                                                                        const msg           = form.getAttribute('data-guard-msg') ?? '# ERROR';
                                                                        const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                                                                        let container       = document.getElementById('toast-container');
                                                                        if (!container) {
                                                                            container       = document.createElement('div');
                                                                            container.id    = 'toast-container';
                                                                            document.body.appendChild(container);
                                                                        }
                                                                        if (bootstrapLink && window.bootstrap) {
                                                                            const toastEl      = document.createElement('div');
                                                                            toastEl.className  = 'toast';
                                                                            toastEl.setAttribute('role','alert');
                                                                            toastEl.setAttribute('aria-live','assertive');
                                                                            toastEl.setAttribute('aria-atomic','true');
                                                                            const body         = document.createElement('div');
                                                                            body.className     = 'toast-body';
                                                                            body.textContent   = msg;
                                                                            toastEl.appendChild(body);
                                                                            container.appendChild(toastEl);
                                                                            bootstrap.Toast.getOrCreateInstance(toastEl).show();
                                                                        } else {
                                                                            alert(msg);
                                                                        }
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
                                                        $flutterwaveName       = ViewsConstants::CST . '.pay.with.flutterwave';
                                                        $flutterwaveRoute      = Route::has($flutterwaveName)
                                                            ? route($flutterwaveName)
                                                            : '#';
                                                        $formId               = 'flutterwavePaymentForm_' . $invoice->id;
                                                        $flutterwaveGuardMsg   = Utility::fetchLinkMessage(
                                                            $lang,
                                                            ViewsConstants::CST,
                                                            'payment_with_flutterwave_route_unavailable'
                                                        ) ?? 'Payment with flutterwave route is unavailable. Please contact technical support or your domain administrator.';
                                                    @endphp
                                                    <form
                                                        role="form"
                                                        action="{{ $flutterwaveRoute }}"
                                                        method="post"
                                                        class="require-validation"
                                                        id="{{ $formId }}"
                                                        data-url="{{ $flutterwaveRoute }}"
                                                        data-guard-msg="{{ $flutterwaveGuardMsg }}"
                                                    >
                                                        @csrf
                                                        <input type="hidden" name="invoice_id" value="{{ Crypt::encrypt($invoice->id) }}">
                                                    
                                                        <div class="form-group col-md-12">
                                                            <label for="amount">{{ __('Amount') }}</label>
                                                            <div class="input-group">
                                                                <span class="input-group-prepend">
                                                                    <span class="input-group-text">{{ $company_setting['site_currency'] }}</span>
                                                                </span>
                                                                <input
                                                                    id="amount"
                                                                    class="form-control"
                                                                    required="required"
                                                                    name="amount"
                                                                    type="number"
                                                                    value="{{ $invoice->getDue() }}"
                                                                    min="0"
                                                                    step="0.01"
                                                                    max="{{ $invoice->getDue() }}"
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
                                                                const form = document.getElementById('{{ $formId }}');
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
                                                    
                                                                        const msg           = form.getAttribute('data-guard-msg') ?? '# ERROR';
                                                                        const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                                                                        let container       = document.getElementById('toast-container');
                                                                        if (!container) {
                                                                            container       = document.createElement('div');
                                                                            container.id    = 'toast-container';
                                                                            document.body.appendChild(container);
                                                                        }
                                                    
                                                                        if (bootstrapLink && window.bootstrap) {
                                                                            const toastEl      = document.createElement('div');
                                                                            toastEl.className  = 'toast';
                                                                            toastEl.setAttribute('role', 'alert');
                                                                            toastEl.setAttribute('aria-live', 'assertive');
                                                                            toastEl.setAttribute('aria-atomic', 'true');
                                                    
                                                                            const body         = document.createElement('div');
                                                                            body.className     = 'toast-body';
                                                                            body.textContent   = msg;
                                                    
                                                                            toastEl.appendChild(body);
                                                                            container.appendChild(toastEl);
                                                                            bootstrap.Toast.getOrCreateInstance(toastEl).show();
                                                                        } else {
                                                                            alert(msg);
                                                                        }
                                                    
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
                                                        $routeName            = ViewsConstants::CST . '.pay.with.razorpay';
                                                        $razorpayActionUrl    = Route::has($routeName)
                                                            ? route($routeName)
                                                            : '#';
                                                        $formId               = 'razorpay-payment-form';
                                                        $razorpayGuardMsg     = Utility::fetchLinkMessage(
                                                            $lang,
                                                            ViewsConstants::CST,
                                                            'payment_with_razorpay_route_unavailable'
                                                        ) ?? 'Payment with Razorpay route is unavailable. Please contact technical support or your domain administrator.';
                                                    @endphp
                                                    <form
                                                        role="form"
                                                        id="{{ $formId }}"
                                                        class="require-validation"
                                                        method="POST"
                                                        action="{{ $razorpayActionUrl }}"
                                                        data-url="{{ $razorpayActionUrl }}"
                                                        data-guard-msg="{{ $razorpayGuardMsg }}"
                                                    >
                                                        @csrf
                                                        <input type="hidden" name="invoice_id" value="{{ Crypt::encrypt($invoice->id) }}">
                                                    
                                                        <div class="form-group col-md-12">
                                                            <label for="amount">{{ __('Amount') }}</label>
                                                            <div class="input-group">
                                                                <span class="input-group-prepend">
                                                                    <span class="input-group-text">{{ $company_setting['site_currency'] }}</span>
                                                                </span>
                                                                <input
                                                                    id="amount"
                                                                    class="form-control"
                                                                    required="required"
                                                                    name="amount"
                                                                    type="number"
                                                                    value="{{ $invoice->getDue() }}"
                                                                    min="0"
                                                                    step="0.01"
                                                                    max="{{ $invoice->getDue() }}"
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
                                                                const form = document.getElementById('{{ $formId }}');
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
                                                                        const msg           = form.getAttribute('data-guard-msg') ?? '# ERROR';
                                                                        const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                                                                        let container       = document.getElementById('toast-container');
                                                                        if (!container) {
                                                                            container       = document.createElement('div');
                                                                            container.id    = 'toast-container';
                                                                            document.body.appendChild(container);
                                                                        }
                                                                        if (bootstrapLink && window.bootstrap) {
                                                                            const toastEl      = document.createElement('div');
                                                                            toastEl.className  = 'toast';
                                                                            toastEl.setAttribute('role','alert');
                                                                            toastEl.setAttribute('aria-live','assertive');
                                                                            toastEl.setAttribute('aria-atomic','true');
                                                                            const body         = document.createElement('div');
                                                                            body.className     = 'toast-body';
                                                                            body.textContent   = msg;
                                                                            toastEl.appendChild(body);
                                                                            container.appendChild(toastEl);
                                                                            bootstrap.Toast.getOrCreateInstance(toastEl).show();
                                                                        } else {
                                                                            alert(msg);
                                                                        }
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
                                                    @endphp
                                                    <form
                                                        role="form"
                                                        id="{{ $mercadoFormId }}"
                                                        class="require-validation"
                                                        method="post"
                                                        action="{{ $mercadoActionUrl }}"
                                                        data-url="{{ $mercadoActionUrl }}"
                                                        data-guard-msg="{{ $mercadoGuardMsg }}"
                                                    >
                                                        @csrf
                                                        <input type="hidden" name="invoice_id" value="{{ Crypt::encrypt($invoice->id) }}">
                                                    
                                                        <div class="form-group col-md-12">
                                                            <label for="amount">{{ __('Amount') }}</label>
                                                            <div class="input-group">
                                                                <span class="input-group-prepend">
                                                                    <span class="input-group-text">{{ $company_setting['site_currency'] }}</span>
                                                                </span>
                                                                <input
                                                                    id="amount"
                                                                    class="form-control"
                                                                    required="required"
                                                                    name="amount"
                                                                    type="number"
                                                                    value="{{ $invoice->getDue() }}"
                                                                    min="0"
                                                                    step="0.01"
                                                                    max="{{ $invoice->getDue() }}"
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
                                                                        const msg           = form.getAttribute('data-guard-msg') ?? '# ERROR';
                                                                        const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                                                                        let container       = document.getElementById('toast-container');
                                                                        if (!container) {
                                                                            container       = document.createElement('div');
                                                                            container.id    = 'toast-container';
                                                                            document.body.appendChild(container);
                                                                        }
                                                                        if (bootstrapLink && window.bootstrap) {
                                                                            const toastEl      = document.createElement('div');
                                                                            toastEl.className  = 'toast';
                                                                            toastEl.setAttribute('role','alert');
                                                                            toastEl.setAttribute('aria-live','assertive');
                                                                            toastEl.setAttribute('aria-atomic','true');
                                                                            const body         = document.createElement('div');
                                                                            body.className     = 'toast-body';
                                                                            body.textContent   = msg;
                                                                            toastEl.appendChild(body);
                                                                            container.appendChild(toastEl);
                                                                            bootstrap.Toast.getOrCreateInstance(toastEl).show();
                                                                        } else {
                                                                            alert(msg);
                                                                        }
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
                                                    @endphp
                                                    <form
                                                        role="form"
                                                        id="{{ $paytmFormId }}"
                                                        class="require-validation"
                                                        method="post"
                                                        action="{{ $paytmActionUrl }}"
                                                        data-url="{{ $paytmActionUrl }}"
                                                        data-guard-msg="{{ $paytmGuardMsg }}"
                                                    >
                                                        @csrf
                                                        <input type="hidden" name="invoice_id" value="{{ Crypt::encrypt($invoice->id) }}">
                                                    
                                                        <div class="form-group col-md-12">
                                                            <label for="amount">{{ __('Amount') }}</label>
                                                            <div class="input-group">
                                                                <span class="input-group-prepend">
                                                                    <span class="input-group-text">{{ $company_setting['site_currency'] }}</span>
                                                                </span>
                                                                <input
                                                                    id="amount"
                                                                    class="form-control"
                                                                    required="required"
                                                                    name="amount"
                                                                    type="number"
                                                                    value="{{ $invoice->getDue() }}"
                                                                    min="0"
                                                                    step="0.01"
                                                                    max="{{ $invoice->getDue() }}"
                                                                >
                                                            </div>
                                                        </div>
                                                    
                                                        <div class="col-md-12">
                                                            <div class="form-group">
                                                                <label for="mobile" class="text-dark">{{ __('Mobile Number') }}</label>
                                                                <input
                                                                    type="text"
                                                                    id="mobile"
                                                                    name="mobile"
                                                                    class="form-control mobile"
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
                                                    
                                                                        const msg           = formEl.getAttribute('data-guard-msg') ?? '# ERROR';
                                                                        const bsLink        = document.querySelector('link[href*="bootstrap"]');
                                                                        let container       = document.getElementById('toast-container');
                                                                        if (!container) {
                                                                            container       = document.createElement('div');
                                                                            container.id    = 'toast-container';
                                                                            document.body.appendChild(container);
                                                                        }
                                                    
                                                                        if (bsLink && window.bootstrap) {
                                                                            const toastEl      = document.createElement('div');
                                                                            toastEl.className  = 'toast';
                                                                            toastEl.setAttribute('role', 'alert');
                                                                            toastEl.setAttribute('aria-live', 'assertive');
                                                                            toastEl.setAttribute('aria-atomic', 'true');
                                                                            const body         = document.createElement('div');
                                                                            body.className     = 'toast-body';
                                                                            body.textContent   = msg;
                                                                            toastEl.appendChild(body);
                                                                            container.appendChild(toastEl);
                                                                            bootstrap.Toast.getOrCreateInstance(toastEl).show();
                                                                        } else {
                                                                            alert(msg);
                                                                        }
                                                    
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
                                                    @endphp
                                                    <form
                                                        role="form"
                                                        id="{{ $mollieFormId }}"
                                                        class="require-validation"
                                                        method="post"
                                                        action="{{ $mollieRouteUrl }}"
                                                        data-url="{{ $mollieRouteUrl }}"
                                                        data-guard-msg="{{ $mollieGuardMsg }}"
                                                    >
                                                        @csrf
                                                        <input type="hidden" name="invoice_id" value="{{ Crypt::encrypt($invoice->id) }}">
                                                    
                                                        <div class="form-group col-md-12">
                                                            <label for="amount">{{ __('Amount') }}</label>
                                                            <div class="input-group">
                                                                <span class="input-group-prepend">
                                                                    <span class="input-group-text">{{ $company_setting['site_currency'] }}</span>
                                                                </span>
                                                                <input
                                                                    id="amount"
                                                                    class="form-control"
                                                                    required
                                                                    name="amount"
                                                                    type="number"
                                                                    value="{{ $invoice->getDue() }}"
                                                                    min="0"
                                                                    step="0.01"
                                                                    max="{{ $invoice->getDue() }}"
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
                                                                        const msg           = mollieForm.getAttribute('data-guard-msg') ?? '# ERROR';
                                                                        const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                                                                        let container       = document.getElementById('toast-container');
                                                                        if (!container) {
                                                                            container       = document.createElement('div');
                                                                            container.id    = 'toast-container';
                                                                            document.body.appendChild(container);
                                                                        }
                                                                        if (bootstrapLink && window.bootstrap) {
                                                                            const toastEl      = document.createElement('div');
                                                                            toastEl.className  = 'toast';
                                                                            toastEl.setAttribute('role', 'alert');
                                                                            toastEl.setAttribute('aria-live', 'assertive');
                                                                            toastEl.setAttribute('aria-atomic', 'true');
                                                                            const bodyEl       = document.createElement('div');
                                                                            bodyEl.className   = 'toast-body';
                                                                            bodyEl.textContent = msg;
                                                                            toastEl.appendChild(bodyEl);
                                                                            container.appendChild(toastEl);
                                                                            bootstrap.Toast.getOrCreateInstance(toastEl).show();
                                                                        } else {
                                                                            alert(msg);
                                                                        }
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
                                                    @endphp
                                                    <form
                                                        role="form"
                                                        id="{{ $skrillFormId }}"
                                                        class="require-validation"
                                                        method="post"
                                                        action="{{ $skrillActionUrl }}"
                                                        data-url="{{ $skrillActionUrl }}"
                                                        data-guard-msg="{{ $skrillGuardMsg }}"
                                                    >
                                                        @csrf
                                                        <input type="hidden" name="invoice_id" value="{{ Crypt::encrypt($invoice->id) }}">
                                                    
                                                        <div class="form-group col-md-12">
                                                            <label for="amount">{{ __('Amount') }}</label>
                                                            <div class="input-group">
                                                                <span class="input-group-prepend">
                                                                    <span class="input-group-text">{{ $company_setting['site_currency'] }}</span>
                                                                </span>
                                                                <input
                                                                    id="amount"
                                                                    class="form-control"
                                                                    required="required"
                                                                    name="amount"
                                                                    type="number"
                                                                    value="{{ $invoice->getDue() }}"
                                                                    min="0"
                                                                    step="0.01"
                                                                    max="{{ $invoice->getDue() }}"
                                                                >
                                                            </div>
                                                        </div>
                                                    
                                                        @php
                                                            $skrill_data = [
                                                                'transaction_id' => md5(date('Y-m-d') . strtotime('Y-m-d H:i:s') . 'user_id'),
                                                                'user_id'        => 'user_id',
                                                                'amount'         => 'amount',
                                                                'currency'       => 'currency',
                                                            ];
                                                            session()->put('skrill_data', $skrill_data);
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
                                                                        const msg           = formEl.getAttribute('data-guard-msg') ?? '# ERROR';
                                                                        const bsLink        = document.querySelector('link[href*="bootstrap"]');
                                                                        let container       = document.getElementById('toast-container');
                                                                        if (!container) {
                                                                            container       = document.createElement('div');
                                                                            container.id    = 'toast-container';
                                                                            document.body.appendChild(container);
                                                                        }
                                                                        if (bsLink && window.bootstrap) {
                                                                            const toastEl      = document.createElement('div');
                                                                            toastEl.className  = 'toast';
                                                                            toastEl.setAttribute('role','alert');
                                                                            toastEl.setAttribute('aria-live','assertive');
                                                                            toastEl.setAttribute('aria-atomic','true');
                                                                            const body         = document.createElement('div');
                                                                            body.className     = 'toast-body';
                                                                            body.textContent   = msg;
                                                                            toastEl.appendChild(body);
                                                                            container.appendChild(toastEl);
                                                                            bootstrap.Toast.getOrCreateInstance(toastEl).show();
                                                                        } else {
                                                                            alert(msg);
                                                                        }
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
                                                    @endphp
                                                    <form
                                                        role="form"
                                                        id="{{ $coingateFormId }}"
                                                        class="require-validation"
                                                        method="post"
                                                        action="{{ $coingateActionUrl }}"
                                                        data-url="{{ $coingateActionUrl }}"
                                                        data-guard-msg="{{ $coingateGuardMsg }}"
                                                    >
                                                        @csrf
                                                        <input type="hidden" name="invoice_id" value="{{ Crypt::encrypt($invoice->id) }}">
                                                    
                                                        <div class="form-group col-md-12">
                                                            <label for="amount">{{ __('Amount') }}</label>
                                                            <div class="input-group">
                                                                <span class="input-group-prepend">
                                                                    <span class="input-group-text">{{ $company_setting['site_currency'] }}</span>
                                                                </span>
                                                                <input
                                                                    id="amount"
                                                                    class="form-control"
                                                                    required="required"
                                                                    name="amount"
                                                                    type="number"
                                                                    value="{{ $invoice->getDue() }}"
                                                                    min="0"
                                                                    step="0.01"
                                                                    max="{{ $invoice->getDue() }}"
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
                                                                        const msg           = coingateForm.getAttribute('data-guard-msg') ?? '# ERROR';
                                                                        const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                                                                        let container       = document.getElementById('toast-container');
                                                                        if (!container) {
                                                                            container       = document.createElement('div');
                                                                            container.id    = 'toast-container';
                                                                            document.body.appendChild(container);
                                                                        }
                                                                        if (bootstrapLink && window.bootstrap) {
                                                                            const toastEl      = document.createElement('div');
                                                                            toastEl.className  = 'toast';
                                                                            toastEl.setAttribute('role', 'alert');
                                                                            toastEl.setAttribute('aria-live', 'assertive');
                                                                            toastEl.setAttribute('aria-atomic', 'true');
                                                                            const body         = document.createElement('div');
                                                                            body.className     = 'toast-body';
                                                                            body.textContent   = msg;
                                                                            toastEl.appendChild(body);
                                                                            container.appendChild(toastEl);
                                                                            bootstrap.Toast.getOrCreateInstance(toastEl).show();
                                                                        } else {
                                                                            alert(msg);
                                                                        }
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
                                                    @endphp
                                                    <form
                                                        id="{{ $paymentwallFormId }}"
                                                        class="w3-container w3-display-middle w3-card-4"
                                                        method="POST"
                                                        action="{{ $paymentwallActionUrl }}"
                                                        data-url="{{ $paymentwallActionUrl }}"
                                                        data-guard-msg="{{ $paymentwallGuardMsg }}"
                                                    >
                                                        @csrf
                                                        <input type="hidden" name="invoice_id" value="{{ Crypt::encrypt($invoice->id) }}">
                                                    
                                                        <div class="form-group col-md-12">
                                                            <label for="amount">{{ __('Amount') }}</label>
                                                            <div class="input-group">
                                                                <span class="input-group-prepend">
                                                                    <span class="input-group-text">{{ $company_setting['site_currency'] }}</span>
                                                                </span>
                                                                <input
                                                                    id="amount"
                                                                    class="form-control"
                                                                    required="required"
                                                                    name="amount"
                                                                    type="number"
                                                                    value="{{ $invoice->getDue() }}"
                                                                    min="0"
                                                                    step="0.01"
                                                                    max="{{ $invoice->getDue() }}"
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
                                                                        const msg           = paymentwallForm.getAttribute('data-guard-msg') ?? '# ERROR';
                                                                        const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                                                                        let container       = document.getElementById('toast-container');
                                                                        if (!container) {
                                                                            container       = document.createElement('div');
                                                                            container.id    = 'toast-container';
                                                                            document.body.appendChild(container);
                                                                        }
                                                                        if (bootstrapLink && window.bootstrap) {
                                                                            const toastEl      = document.createElement('div');
                                                                            toastEl.className  = 'toast';
                                                                            toastEl.setAttribute('role','alert');
                                                                            toastEl.setAttribute('aria-live','assertive');
                                                                            toastEl.setAttribute('aria-atomic','true');
                                                                            const body         = document.createElement('div');
                                                                            body.className     = 'toast-body';
                                                                            body.textContent   = msg;
                                                                            toastEl.appendChild(body);
                                                                            container.appendChild(toastEl);
                                                                            bootstrap.Toast.getOrCreateInstance(toastEl).show();
                                                                        } else {
                                                                            alert(msg);
                                                                        }
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
                                                    @endphp
                                                    <form
                                                        role="form"
                                                        id="{{ $toyyibFormId }}"
                                                        class="require-validation"
                                                        method="post"
                                                        action="{{ $toyyibActionUrl }}"
                                                        data-url="{{ $toyyibActionUrl }}"
                                                        data-guard-msg="{{ $toyyibGuardMsg }}"
                                                    >
                                                        @csrf
                                                        <input type="hidden" name="invoice_id" value="{{ Crypt::encrypt($invoice->id) }}">
                                                    
                                                        <div class="form-group col-md-12">
                                                            <label for="amount">{{ __('Amount') }}</label>
                                                            <div class="input-group">
                                                                <span class="input-group-prepend">
                                                                    <span class="input-group-text">{{ $company_setting['site_currency'] }}</span>
                                                                </span>
                                                                <input
                                                                    id="amount"
                                                                    class="form-control"
                                                                    required
                                                                    name="amount"
                                                                    type="number"
                                                                    value="{{ $invoice->getDue() }}"
                                                                    min="0"
                                                                    step="0.01"
                                                                    max="{{ $invoice->getDue() }}"
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
                                                                        const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                                                                        let toastContainer = document.getElementById('toast-container');
                                                                        if (!toastContainer) {
                                                                            toastContainer = document.createElement('div');
                                                                            toastContainer.id = 'toast-container';
                                                                            document.body.appendChild(toastContainer);
                                                                        }
                                                    
                                                                        if (bootstrapLink && window.bootstrap) {
                                                                            const toastEl = document.createElement('div');
                                                                            toastEl.className = 'toast';
                                                                            toastEl.setAttribute('role', 'alert');
                                                                            toastEl.setAttribute('aria-live', 'assertive');
                                                                            toastEl.setAttribute('aria-atomic', 'true');
                                                                            const body = document.createElement('div');
                                                                            body.className = 'toast-body';
                                                                            body.textContent = toyyibMsg;
                                                                            toastEl.appendChild(body);
                                                                            toastContainer.appendChild(toastEl);
                                                                            bootstrap.Toast.getOrCreateInstance(toastEl).show();
                                                                        } else {
                                                                            alert(toyyibMsg);
                                                                        }
                                                    
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
                                                        $pfHost = $company_payment_setting['payfast_mode'] == 'sandbox' ? 'sandbox.payfast.co.za' : 'www.payfast.co.za';
                                                        $payfastUrl                 = 'https://' . $pfHost . '/eng/process';
                                                        $payfastFormId              = 'payfastPaymentForm_' . $invoice->id;
                                                        $payfastGuardMsg            = Utility::fetchLinkMessage(
                                                            $lang,
                                                            ViewsConstants::CST,
                                                            'payment_with_payfast_route_unavailable'
                                                        ) ?? 'Payment with Payfast route is unavailable. Please contact technical support or your domain administrator.';
                                                    @endphp
                                                    <form
                                                        role="form"
                                                        id="{{ $payfastFormId }}"
                                                        action="{{ $payfastUrl }}"
                                                        method="post"
                                                        data-url="{{ $payfastUrl }}"
                                                        data-guard-msg="{{ $payfastGuardMsg }}"
                                                        >
                                                        @csrf
                                                        <div class="form-group col-md-12">
                                                            <label for="pay_fast_amount">{{ __('Amount') }}</label>
                                                            <div class="input-group">
                                                                <span class="input-group-prepend">
                                                                    <span class="input-group-text">{{ $company_setting['site_currency'] }}</span>
                                                                </span>
                                                                <input
                                                                    id="pay_fast_amount"
                                                                    class="form-control"
                                                                    required
                                                                    name="amount"
                                                                    type="number"
                                                                    value="{{ $invoice->getDue() }}"
                                                                    min="0"
                                                                    step="0.01"
                                                                    max="{{ $invoice->getDue() }}"
                                                                    onchange="get_payfast_status()"
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
                                                                        const msg           = pfForm.getAttribute('data-guard-msg') ?? '# ERROR';
                                                                        const bsLink        = document.querySelector('link[href*="bootstrap"]');
                                                                        let toastContainer  = document.getElementById('toast-container');
                                                                        if (!toastContainer) {
                                                                            toastContainer  = document.createElement('div');
                                                                            toastContainer.id = 'toast-container';
                                                                            document.body.appendChild(toastContainer);
                                                                        }
                                                                        if (bsLink && window.bootstrap) {
                                                                            const toastEl    = document.createElement('div');
                                                                            toastEl.className= 'toast';
                                                                            toastEl.setAttribute('role','alert');
                                                                            toastEl.setAttribute('aria-live','assertive');
                                                                            toastEl.setAttribute('aria-atomic','true');
                                                                            const body       = document.createElement('div');
                                                                            body.className   = 'toast-body';
                                                                            body.textContent = msg;
                                                                            toastEl.appendChild(body);
                                                                            toastContainer.appendChild(toastEl);
                                                                            bootstrap.Toast.getOrCreateInstance(toastEl).show();
                                                                        } else {
                                                                            alert(msg);
                                                                        }
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
                                                    @endphp
                                                    <form
                                                        role="form"
                                                        id="{{ $iyzipayFormId }}"
                                                        class="require-validation"
                                                        method="post"
                                                        action="{{ $iyzipayActionUrl }}"
                                                        data-url="{{ $iyzipayActionUrl }}"
                                                        data-guard-msg="{{ $iyzipayGuardMsg }}"
                                                    >
                                                        @csrf
                                                        <input type="hidden" name="invoice_id" value="{{ Crypt::encrypt($invoice->id) }}">
                                                    
                                                        <div class="form-group col-md-12">
                                                            <label for="amount">{{ __('Amount') }}</label>
                                                            <div class="input-group">
                                                                <span class="input-group-prepend">
                                                                    <span class="input-group-text">{{ $company_setting['site_currency'] }}</span>
                                                                </span>
                                                                <input
                                                                    id="amount"
                                                                    class="form-control"
                                                                    required="required"
                                                                    name="amount"
                                                                    type="number"
                                                                    value="{{ $invoice->getDue() }}"
                                                                    min="0"
                                                                    step="0.01"
                                                                    max="{{ $invoice->getDue() }}"
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
                                                    
                                                                        const msg           = form.getAttribute('data-guard-msg') ?? '# ERROR';
                                                                        const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                                                                        let container       = document.getElementById('toast-container');
                                                                        if (!container) {
                                                                            container       = document.createElement('div');
                                                                            container.id    = 'toast-container';
                                                                            document.body.appendChild(container);
                                                                        }
                                                                        if (bootstrapLink && window.bootstrap) {
                                                                            const toastEl      = document.createElement('div');
                                                                            toastEl.className  = 'toast';
                                                                            toastEl.setAttribute('role','alert');
                                                                            toastEl.setAttribute('aria-live','assertive');
                                                                            toastEl.setAttribute('aria-atomic','true');
                                                                            const body         = document.createElement('div');
                                                                            body.className     = 'toast-body';
                                                                            body.textContent   = msg;
                                                                            toastEl.appendChild(body);
                                                                            container.appendChild(toastEl);
                                                                            bootstrap.Toast.getOrCreateInstance(toastEl).show();
                                                                        } else {
                                                                            alert(msg);
                                                                        }
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
                                                    @endphp
                                                    <form
                                                        role="form"
                                                        id="{{ $sspayFormId }}"
                                                        class="require-validation"
                                                        method="post"
                                                        action="{{ $sspayActionUrl }}"
                                                        data-url="{{ $sspayActionUrl }}"
                                                        data-guard-msg="{{ $sspayGuardMsg }}"
                                                    >
                                                        @csrf
                                                        <input type="hidden" name="invoice_id" value="{{ Crypt::encrypt($invoice->id) }}">
                                                    
                                                        <div class="form-group col-md-12">
                                                            <label for="amount">{{ __('Amount') }}</label>
                                                            <div class="input-group">
                                                                <span class="input-group-prepend">
                                                                    <span class="input-group-text">{{ $company_setting['site_currency'] }}</span>
                                                                </span>
                                                                <input
                                                                    id="amount"
                                                                    class="form-control"
                                                                    required
                                                                    name="amount"
                                                                    type="number"
                                                                    value="{{ $invoice->getDue() }}"
                                                                    min="0"
                                                                    step="0.01"
                                                                    max="{{ $invoice->getDue() }}"
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
                                                                        const msg           = sspayForm.getAttribute('data-guard-msg') ?? '# ERROR';
                                                                        const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                                                                        let container       = document.getElementById('toast-container');
                                                                        if (!container) {
                                                                            container       = document.createElement('div');
                                                                            container.id    = 'toast-container';
                                                                            document.body.appendChild(container);
                                                                        }
                                                                        if (bootstrapLink && window.bootstrap) {
                                                                            const toastEl      = document.createElement('div');
                                                                            toastEl.className  = 'toast';
                                                                            toastEl.setAttribute('role','alert');
                                                                            toastEl.setAttribute('aria-live','assertive');
                                                                            toastEl.setAttribute('aria-atomic','true');
                                                                            const body         = document.createElement('div');
                                                                            body.className     = 'toast-body';
                                                                            body.textContent   = msg;
                                                                            toastEl.appendChild(body);
                                                                            container.appendChild(toastEl);
                                                                            bootstrap.Toast.getOrCreateInstance(toastEl).show();
                                                                        } else {
                                                                            alert(msg);
                                                                        }
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
                                                    @endphp
                                                    <form
                                                        role="form"
                                                        id="{{ $paytabFormId }}"
                                                        class="require-validation"
                                                        method="post"
                                                        action="{{ $paytabActionUrl }}"
                                                        data-url="{{ $paytabActionUrl }}"
                                                        data-guard-msg="{{ $paytabGuardMsg }}"
                                                    >
                                                        @csrf
                                                        <input type="hidden" name="invoice_id" value="{{ Crypt::encrypt($invoice->id) }}">
                                                    
                                                        <div class="form-group col-md-12">
                                                            <label for="amount">{{ __('Amount') }}</label>
                                                            <div class="input-group">
                                                                <span class="input-group-prepend">
                                                                    <span class="input-group-text">{{ $company_setting['site_currency'] }}</span>
                                                                </span>
                                                                <input
                                                                    id="amount"
                                                                    class="form-control"
                                                                    required
                                                                    name="amount"
                                                                    type="number"
                                                                    value="{{ $invoice->getDue() }}"
                                                                    min="0"
                                                                    step="0.01"
                                                                    max="{{ $invoice->getDue() }}"
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
                                                                        const msg           = paytabForm.getAttribute('data-guard-msg') ?? '# ERROR';
                                                                        const bsLink        = document.querySelector('link[href*="bootstrap"]');
                                                                        let container       = document.getElementById('toast-container');
                                                                        if (!container) {
                                                                            container       = document.createElement('div');
                                                                            container.id    = 'toast-container';
                                                                            document.body.appendChild(container);
                                                                        }
                                                                        if (bsLink && window.bootstrap) {
                                                                            const toastEl      = document.createElement('div');
                                                                            toastEl.className  = 'toast';
                                                                            toastEl.setAttribute('role','alert');
                                                                            toastEl.setAttribute('aria-live','assertive');
                                                                            toastEl.setAttribute('aria-atomic','true');
                                                                            const body         = document.createElement('div');
                                                                            body.className     = 'toast-body';
                                                                            body.textContent   = msg;
                                                                            toastEl.appendChild(body);
                                                                            container.appendChild(toastEl);
                                                                            bootstrap.Toast.getOrCreateInstance(toastEl).show();
                                                                        } else {
                                                                            alert(msg);
                                                                        }
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
                                                    @endphp
                                                    <form
                                                        role="form"
                                                        id="{{ $benefitFormId }}"
                                                        class="require-validation"
                                                        method="post"
                                                        action="{{ $benefitInitiateRoute }}"
                                                        data-url="{{ $benefitInitiateRoute }}"
                                                        data-guard-msg="{{ $benefitGuardMsg }}"
                                                    >
                                                        @csrf
                                                    
                                                        <input type="hidden"
                                                            name="invoice_id"
                                                            value="{{ Crypt::encrypt($invoice->id) }}"
                                                        >
                                                    
                                                        <div class="form-group col-md-12">
                                                            <label for="amount">{{ __('Amount') }}</label>
                                                            <div class="input-group">
                                                                <span class="input-group-prepend">
                                                                    <span class="input-group-text">{{ $company_setting['site_currency'] }}</span>
                                                                </span>
                                                                <input
                                                                    id="amount"
                                                                    class="form-control"
                                                                    required="required"
                                                                    name="amount"
                                                                    type="number"
                                                                    value="{{ $invoice->getDue() }}"
                                                                    min="0"
                                                                    step="0.01"
                                                                    max="{{ $invoice->getDue() }}"
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
                                                                        const msg           = form.getAttribute('data-guard-msg') ?? '# ERROR';
                                                                        const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                                                                        let container       = document.getElementById('toast-container');
                                                                        if (!container) {
                                                                            container       = document.createElement('div');
                                                                            container.id    = 'toast-container';
                                                                            document.body.appendChild(container);
                                                                        }
                                                                        if (bootstrapLink && window.bootstrap) {
                                                                            const toastEl      = document.createElement('div');
                                                                            toastEl.className  = 'toast';
                                                                            toastEl.setAttribute('role','alert');
                                                                            toastEl.setAttribute('aria-live','assertive');
                                                                            toastEl.setAttribute('aria-atomic','true');
                                                                            const body         = document.createElement('div');
                                                                            body.className     = 'toast-body';
                                                                            body.textContent   = msg;
                                                                            toastEl.appendChild(body);
                                                                            container.appendChild(toastEl);
                                                                            bootstrap.Toast.getOrCreateInstance(toastEl).show();
                                                                        } else {
                                                                            alert(msg);
                                                                        }
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
                                                        $lang                   = Utility::fetchUserLang();
                                                        $routeName              = ViewsConstants::CST . '.pay.with.cashfree';
                                                        $cashfreeAction         = Route::has($routeName)
                                                            ? route($routeName)
                                                            : '#';
                                                        $formId                 = 'cashfree-payment-form';
                                                        $cashfreeGuardKey       = 'payment_with_cashfree_route_unavailable';
                                                        $cashfreeGuardMsg       = Utility::fetchLinkMessage(
                                                            $lang,
                                                            ViewsConstants::CST,
                                                            $cashfreeGuardKey
                                                        ) ?? 'Payment with Cashfree route is unavailable. Please contact technical support or your domain administrator.';
                                                    @endphp
                                                    <form
                                                        role="form"
                                                        id="{{ $formId }}"
                                                        class="require-validation"
                                                        method="post"
                                                        action="{{ $cashfreeAction }}"
                                                        data-url="{{ $cashfreeAction }}"
                                                        data-guard-msg="{{ $cashfreeGuardMsg }}"
                                                        data-listener-alias="cashfree-payment"
                                                    >
                                                        @csrf
                                                        <input
                                                            type="hidden"
                                                            name="invoice_id"
                                                            value="{{ Crypt::encrypt($invoice->id) }}"
                                                        >
                                                    
                                                        <div class="form-group col-md-12">
                                                            <label for="amount">{{ __('Amount') }}</label>
                                                            <div class="input-group">
                                                                <span class="input-group-prepend">
                                                                    <span class="input-group-text">{{ $company_setting['site_currency'] }}</span>
                                                                </span>
                                                                <input
                                                                    id="amount"
                                                                    class="form-control"
                                                                    required
                                                                    name="amount"
                                                                    type="number"
                                                                    value="{{ $invoice->getDue() }}"
                                                                    min="0"
                                                                    step="0.01"
                                                                    max="{{ $invoice->getDue() }}"
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
                                                                const formCashfree = document.getElementById('{{ $formId }}');
                                                                if (!formCashfree || formCashfree.getAttribute('data-listener-active') === 'true') return;
                                                                formCashfree.setAttribute('data-listener-active', 'true');
                                                    
                                                                formCashfree.addEventListener('submit', event => {
                                                                    try {
                                                                        const urlCashfree = formCashfree.getAttribute('data-url') ?? '#';
                                                                        if (urlCashfree !== '#') return;
                                                                        event.preventDefault();
                                                    
                                                                        const msgCashfree    = formCashfree.getAttribute('data-guard-msg') ?? '# ERROR';
                                                                        const bootstrapLink  = document.querySelector('link[href*="bootstrap"]');
                                                                        let containerToast   = document.getElementById('toast-container');
                                                                        if (!containerToast) {
                                                                            containerToast   = document.createElement('div');
                                                                            containerToast.id = 'toast-container';
                                                                            document.body.appendChild(containerToast);
                                                                        }
                                                    
                                                                        if (bootstrapLink && window.bootstrap) {
                                                                            const toastEl      = document.createElement('div');
                                                                            toastEl.className  = 'toast';
                                                                            toastEl.setAttribute('role', 'alert');
                                                                            toastEl.setAttribute('aria-live', 'assertive');
                                                                            toastEl.setAttribute('aria-atomic', 'true');
                                                                            const bodyEl       = document.createElement('div');
                                                                            bodyEl.className   = 'toast-body';
                                                                            bodyEl.textContent = msgCashfree;
                                                                            toastEl.appendChild(bodyEl);
                                                                            containerToast.appendChild(toastEl);
                                                                            bootstrap.Toast.getOrCreateInstance(toastEl).show();
                                                                        } else {
                                                                            alert(msgCashfree);
                                                                        }
                                                    
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
                                                        <div class="form-group col-md-12">
                                                            <label for="amount">{{ __('Amount') }}</label>
                                                            <div class="input-group">
                                                                <span class="input-group-prepend"><span
                                                                        class="input-group-text">{{ $company_setting['site_currency'] }}</span></span>
                                                                <input class="form-control" required="required"
                                                                    min="0" name="amount" type="number"
                                                                    value="{{ $invoice->getDue() }}" min="0"
                                                                    step="0.01" max="{{ $invoice->getDue() }}"
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
                                                    @endphp
                                                    <form
                                                        role="form"
                                                        id="{{ $aamarpayFormId }}"
                                                        class="require-validation"
                                                        method="post"
                                                        action="{{ $aamarpayActionUrl }}"
                                                        data-url="{{ $aamarpayActionUrl }}"
                                                        data-guard-msg="{{ $aamarpayGuardMsg }}"
                                                        data-listener-alias="{{ $aamarpayFormId }}"
                                                    >
                                                        @csrf
                                                        <input type="hidden"
                                                            name="invoice_id"
                                                            value="{{ Crypt::encrypt($invoice->id) }}"
                                                        >
                                                    
                                                        <div class="form-group col-md-12">
                                                            <label for="amount">{{ __('Amount') }}</label>
                                                            <div class="input-group">
                                                                <span class="input-group-prepend">
                                                                    <span class="input-group-text">{{ $company_setting['site_currency'] }}</span>
                                                                </span>
                                                                <input
                                                                    id="amount"
                                                                    class="form-control"
                                                                    required
                                                                    name="amount"
                                                                    type="number"
                                                                    value="{{ $invoice->getDue() }}"
                                                                    min="0"
                                                                    step="0.01"
                                                                    max="{{ $invoice->getDue() }}"
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
                                                                        const url    = form.getAttribute('data-url') ?? '#';
                                                                        if (url !== '#') return;
                                                                        event.preventDefault();
                                                                        const msg         = form.getAttribute('data-guard-msg') ?? '# ERROR';
                                                                        const bsLink      = document.querySelector('link[href*="bootstrap"]');
                                                                        let container     = document.getElementById('toast-container');
                                                                        if (!container) {
                                                                            container     = document.createElement('div');
                                                                            container.id  = 'toast-container';
                                                                            document.body.appendChild(container);
                                                                        }
                                                                        if (bsLink && window.bootstrap) {
                                                                            const toastEl    = document.createElement('div');
                                                                            toastEl.className= 'toast';
                                                                            toastEl.setAttribute('role','alert');
                                                                            toastEl.setAttribute('aria-live','assertive');
                                                                            toastEl.setAttribute('aria-atomic','true');
                                                                            const body       = document.createElement('div');
                                                                            body.className   = 'toast-body';
                                                                            body.textContent = msg;
                                                                            toastEl.appendChild(body);
                                                                            container.appendChild(toastEl);
                                                                            bootstrap.Toast.getOrCreateInstance(toastEl).show();
                                                                        } else {
                                                                            alert(msg);
                                                                        }
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
                                                    @endphp
                                                    <form
                                                        role="form"
                                                        id="yookassa-payment-form"
                                                        class="require-validation"
                                                        method="post"
                                                        action="{{ $yooRouteUrl }}"
                                                        data-url="{{ $yooRouteUrl }}"
                                                        data-guard-msg="{{ $yooGuardMsg }}"
                                                        data-listener-alias="{{ $yooListenerAlias }}"
                                                    >
                                                        @csrf
                                                        <input type="hidden" name="invoice_id" value="{{ Crypt::encrypt($invoice->id) }}">
                                                    
                                                        <div class="form-group col-md-12">
                                                            <label for="amount">{{ __('Amount') }}</label>
                                                            <div class="input-group">
                                                                <span class="input-group-prepend">
                                                                    <span class="input-group-text">{{ $company_setting['site_currency'] }}</span>
                                                                </span>
                                                                <input
                                                                    id="amount"
                                                                    class="form-control"
                                                                    required="required"
                                                                    name="amount"
                                                                    type="number"
                                                                    value="{{ $invoice->getDue() }}"
                                                                    min="0"
                                                                    step="0.01"
                                                                    max="{{ $invoice->getDue() }}"
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
                                                        <script defer>
                                                            (() => {
                                                                const form = document.getElementById('yookassa-payment-form');
                                                                if (!form || form.getAttribute('data-listener-active') === 'true') return;
                                                                form.setAttribute('data-listener-active', 'true');
                                                                form.addEventListener('submit', event => {
                                                                    try {
                                                                        const url   = form.getAttribute('data-url') ?? '#';
                                                                        if (url !== '#') return;
                                                                        event.preventDefault();
                                                                        const msg           = form.getAttribute('data-guard-msg') ?? '# ERROR';
                                                                        const bsLink        = document.querySelector('link[href*="bootstrap"]');
                                                                        let container       = document.getElementById('toast-container');
                                                                        if (!container) {
                                                                            container       = document.createElement('div');
                                                                            container.id    = 'toast-container';
                                                                            document.body.appendChild(container);
                                                                        }
                                                                        if (bsLink && window.bootstrap) {
                                                                            const toastEl      = document.createElement('div');
                                                                            toastEl.className  = 'toast';
                                                                            toastEl.setAttribute('role', 'alert');
                                                                            toastEl.setAttribute('aria-live', 'assertive');
                                                                            toastEl.setAttribute('aria-atomic', 'true');
                                                                            const body         = document.createElement('div');
                                                                            body.className     = 'toast-body';
                                                                            body.textContent   = msg;
                                                                            toastEl.appendChild(body);
                                                                            container.appendChild(toastEl);
                                                                            bootstrap.Toast.getOrCreateInstance(toastEl).show();
                                                                        } else {
                                                                            alert(msg);
                                                                        }
                                                                        form.setAttribute('data-failed-route', 'true');
                                                                    } catch {}
                                                                });
                                                            })();
                                                        </script>
                                                    @endpush
                                                </div>
                                            @endif

                                            @if (isset($company_payment_setting['is_midtrans_enabled']) && $company_payment_setting['is_midtrans_enabled'] == 'on')
                                                <div class="tab-pane fade" id="midtrans-payment" role="tabpanel" aria-labelledby="midtrans-payment">
                                                    @php
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
                                                    @endphp
                                                    <form
                                                        role="form"
                                                        id="{{ $midtransFormId }}"
                                                        class="require-validation"
                                                        method="post"
                                                        action="{{ $midtransActionUrl }}"
                                                        data-url="{{ $midtransActionUrl }}"
                                                        data-guard-msg="{{ $midtransGuardMsg }}"
                                                    >
                                                        @csrf
                                                        <input type="hidden" name="invoice_id" value="{{ Crypt::encrypt($invoice->id) }}">
                                                    
                                                        <div class="form-group col-md-12">
                                                            <label for="amount">{{ __('Amount') }}</label>
                                                            <div class="input-group">
                                                                <span class="input-group-prepend">
                                                                    <span class="input-group-text">{{ $company_setting['site_currency'] }}</span>
                                                                </span>
                                                                <input
                                                                    id="amount"
                                                                    class="form-control"
                                                                    required="required"
                                                                    name="amount"
                                                                    type="number"
                                                                    value="{{ $invoice->getDue() }}"
                                                                    min="0"
                                                                    step="0.01"
                                                                    max="{{ $invoice->getDue() }}"
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
                                                    
                                                                        const msg           = form.getAttribute('data-guard-msg') ?? '# ERROR';
                                                                        const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                                                                        let container       = document.getElementById('toast-container');
                                                                        if (!container) {
                                                                            container       = document.createElement('div');
                                                                            container.id    = 'toast-container';
                                                                            document.body.appendChild(container);
                                                                        }
                                                                        if (bootstrapLink && window.bootstrap) {
                                                                            const toastEl      = document.createElement('div');
                                                                            toastEl.className  = 'toast';
                                                                            toastEl.setAttribute('role', 'alert');
                                                                            toastEl.setAttribute('aria-live', 'assertive');
                                                                            toastEl.setAttribute('aria-atomic', 'true');
                                                                            const body         = document.createElement('div');
                                                                            body.className     = 'toast-body';
                                                                            body.textContent   = msg;
                                                                            toastEl.appendChild(body);
                                                                            container.appendChild(toastEl);
                                                                            bootstrap.Toast.getOrCreateInstance(toastEl).show();
                                                                        } else {
                                                                            alert(msg);
                                                                        }
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
                                                    @endphp
                                                    <form
                                                        role="form"
                                                        id="{{ $xenditFormId }}"
                                                        class="require-validation"
                                                        method="post"
                                                        action="{{ $xenditRouteUrl }}"
                                                        data-url="{{ $xenditRouteUrl }}"
                                                        data-guard-msg="{{ $xenditGuardMsg }}"
                                                    >
                                                        @csrf
                                                        <input type="hidden" name="invoice_id" value="{{ Crypt::encrypt($invoice->id) }}">
                                                    
                                                        <div class="form-group col-md-12">
                                                            <label for="amount">{{ __('Amount') }}</label>
                                                            <div class="input-group">
                                                                <span class="input-group-prepend">
                                                                    <span class="input-group-text">{{ $company_setting['site_currency'] }}</span>
                                                                </span>
                                                                <input
                                                                    id="amount"
                                                                    class="form-control"
                                                                    required="required"
                                                                    name="amount"
                                                                    type="number"
                                                                    value="{{ $invoice->getDue() }}"
                                                                    min="0"
                                                                    step="0.01"
                                                                    max="{{ $invoice->getDue() }}"
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
                                                        <script defer>
                                                            (() => {
                                                                const form = document.getElementById('{{ $xenditFormId }}');
                                                                if (!form || form.getAttribute('data-listener-active') === 'true') return;
                                                                form.setAttribute('data-listener-active', 'true');
                                                    
                                                                form.addEventListener('submit', event => {
                                                                    try {
                                                                        const url = form.getAttribute('data-url') ?? '#';
                                                                        if (url !== '#') return;
                                                                        event.preventDefault();
                                                                        const msg           = form.getAttribute('data-guard-msg') ?? '# ERROR';
                                                                        const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                                                                        let container       = document.getElementById('toast-container');
                                                                        if (!container) {
                                                                            container       = document.createElement('div');
                                                                            container.id    = 'toast-container';
                                                                            document.body.appendChild(container);
                                                                        }
                                                                        if (bootstrapLink && window.bootstrap) {
                                                                            const toastEl      = document.createElement('div');
                                                                            toastEl.className  = 'toast';
                                                                            toastEl.setAttribute('role','alert');
                                                                            toastEl.setAttribute('aria-live','assertive');
                                                                            toastEl.setAttribute('aria-atomic','true');
                                                                            const body         = document.createElement('div');
                                                                            body.className     = 'toast-body';
                                                                            body.textContent   = msg;
                                                                            toastEl.appendChild(body);
                                                                            container.appendChild(toastEl);
                                                                            bootstrap.Toast.getOrCreateInstance(toastEl).show();
                                                                        } else {
                                                                            alert(msg);
                                                                        }
                                                                        form.setAttribute('data-failed-route', 'true');
                                                                    } catch {}
                                                                });
                                                            })();
                                                        </script>
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
                            class="btn-close btn-close-white me-2 m-auto"
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
                            <div class="text-sm font-weight-bold text-center text-md-left">
                                {{ $companySettings[SettingsConstants::FT_TXT]->value ?? '' }}
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
                show_toastr('success', '{!! $message !!}');
            </script>
        @endif
        @if ($message = Session::get('error'))
            <script>
                show_toastr('error', '{!! $message !!}');
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
        <script async>
            window.translations = {
                ar:       { stripe_unavailable: 'لا يمكن تحميل Stripe', paystack_unavailable: 'لا يمكن تحميل Paystack', flutterwave_unavailable: 'لا يمكن تحميل Flutterwave', razorpay_unavailable: 'لا يمكن تحميل Razorpay', payfast_unavailable: 'لا يمكن تحميل PayFast', shipping_toggle_unavailable: 'فشل تبديل الشحن' },
                da:       { stripe_unavailable: 'Kan ikke indlæse Stripe', paystack_unavailable: 'Kan ikke indlæse Paystack', flutterwave_unavailable: 'Kan ikke indlæse Flutterwave', razorpay_unavailable: 'Kan ikke indlæse Razorpay', payfast_unavailable: 'Kan ikke indlæse PayFast', shipping_toggle_unavailable: 'Skift af forsendelse mislykkedes' },
                de:       { stripe_unavailable: 'Stripe konnte nicht geladen werden', paystack_unavailable: 'Paystack konnte nicht geladen werden', flutterwave_unavailable: 'Flutterwave konnte nicht geladen werden', razorpay_unavailable: 'Razorpay konnte nicht geladen werden', payfast_unavailable: 'PayFast konnte nicht geladen werden', shipping_toggle_unavailable: 'Versandumschaltung fehlgeschlagen' },
                en:       { stripe_unavailable: 'Cannot load Stripe', paystack_unavailable: 'Cannot load Paystack', flutterwave_unavailable: 'Cannot load Flutterwave', razorpay_unavailable: 'Cannot load Razorpay', payfast_unavailable: 'Cannot load PayFast', shipping_toggle_unavailable: 'Shipping toggle failed' },
                es:       { stripe_unavailable: 'No se puede cargar Stripe', paystack_unavailable: 'No se puede cargar Paystack', flutterwave_unavailable: 'No se puede cargar Flutterwave', razorpay_unavailable: 'No se puede cargar Razorpay', payfast_unavailable: 'No se puede cargar PayFast', shipping_toggle_unavailable: 'Error al alternar envío' },
                fr:       { stripe_unavailable: 'Impossible de charger Stripe', paystack_unavailable: 'Impossible de charger Paystack', flutterwave_unavailable: 'Impossible de charger Flutterwave', razorpay_unavailable: 'Impossible de charger Razorpay', payfast_unavailable: 'Impossible de charger PayFast', shipping_toggle_unavailable: 'Échec du basculement de livraison' },
                he:       { stripe_unavailable: 'לא ניתן לטעון Stripe', paystack_unavailable: 'לא ניתן לטעון Paystack', flutterwave_unavailable: 'לא ניתן לטעון Flutterwave', razorpay_unavailable: 'לא ניתן לטעון Razorpay', payfast_unavailable: 'לא ניתן לטעון PayFast', shipping_toggle_unavailable: 'החלפת שילוח נכשלה' },
                it:       { stripe_unavailable: 'Impossibile caricare Stripe', paystack_unavailable: 'Impossibile caricare Paystack', flutterwave_unavailable: 'Impossibile caricare Flutterwave', razorpay_unavailable: 'Impossibile caricare Razorpay', payfast_unavailable: 'Impossibile caricare PayFast', shipping_toggle_unavailable: 'Errore commutazione spedizione' },
                ja:       { stripe_unavailable: 'Stripeを読み込めません', paystack_unavailable: 'Paystackを読み込めません', flutterwave_unavailable: 'Flutterwaveを読み込めません', razorpay_unavailable: 'Razorpayを読み込めません', payfast_unavailable: 'PayFastを読み込めません', shipping_toggle_unavailable: '配送切り替えに失敗しました' },
                nl:       { stripe_unavailable: 'Kan Stripe niet laden', paystack_unavailable: 'Kan Paystack niet laden', flutterwave_unavailable: 'Kan Flutterwave niet laden', razorpay_unavailable: 'Kan Razorpay niet laden', payfast_unavailable: 'Kan PayFast niet laden', shipping_toggle_unavailable: 'Verzending wisselen mislukt' },
                pl:       { stripe_unavailable: 'Nie można załadować Stripe', paystack_unavailable: 'Nie można załadować Paystack', flutterwave_unavailable: 'Nie można załadować Flutterwave', razorpay_unavailable: 'Nie można załadować Razorpay', payfast_unavailable: 'Nie można załadować PayFast', shipping_toggle_unavailable: 'Nie udało się zmienić wysyłki' },
                pt:       { stripe_unavailable: 'Não foi possível carregar Stripe', paystack_unavailable: 'Não foi possível carregar Paystack', flutterwave_unavailable: 'Não foi possível carregar Flutterwave', razorpay_unavailable: 'Não foi possível carregar Razorpay', payfast_unavailable: 'Não foi possível carregar PayFast', shipping_toggle_unavailable: 'Falha ao alternar frete' },
                'pt-br': { stripe_unavailable: 'Não foi possível carregar Stripe', paystack_unavailable: 'Não foi possível carregar Paystack', flutterwave_unavailable: 'Não foi possível carregar Flutterwave', razorpay_unavailable: 'Não foi possível carregar Razorpay', payfast_unavailable: 'Não foi possível carregar PayFast', shipping_toggle_unavailable: 'Falha ao alternar frete' },
                ru:       { stripe_unavailable: 'Не удалось загрузить Stripe', paystack_unavailable: 'Не удалось загрузить Paystack', flutterwave_unavailable: 'Не удалось загрузить Flutterwave', razorpay_unavailable: 'Не удалось загрузить Razorpay', payfast_unavailable: 'Не удалось загрузить PayFast', shipping_toggle_unavailable: 'Не удалось переключить доставку' },
                tr:       { stripe_unavailable: 'Stripe yüklenemiyor', paystack_unavailable: 'Paystack yüklenemiyor', flutterwave_unavailable: 'Flutterwave yüklenemiyor', razorpay_unavailable: 'Razorpay yüklenemiyor', payfast_unavailable: 'PayFast yüklenemiyor', shipping_toggle_unavailable: 'Gönderim geçişi başarısız' },
                zh:       { stripe_unavailable: '无法加载 Stripe', paystack_unavailable: '无法加载 Paystack', flutterwave_unavailable: '无法加载 Flutterwave', razorpay_unavailable: '无法加载 Razorpay', payfast_unavailable: '无法加载 PayFast', shipping_toggle_unavailable: '运送切换失败' }
            };
        </script>
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
                                <div class="d-flex">
                                    <div class="toast-body">${message}</div>
                                    <button type="button"
                                            class="btn-close btn-close-white me-2 m-auto"
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
                        console.error('jQuery is required');
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
                            const stripe = Stripe('{{ $company_payment_setting['stripe_key'] }}');
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
                    @if (isset($company_payment_setting['paystack_public_key']))
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
                    @if (isset($company_payment_setting['flutterwave_public_key']))
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
                                                currency: '{{ $company_setting['site_currency'] }}',
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
                    @if (isset($company_payment_setting['razorpay_public_key']))
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
                                                currency: '{{ $company_setting['site_currency'] }}',
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
                    console.error('Initialization failed', e);
                }
            })();
        </script>
        @if ($get_cookie['enable_cookie'] == 'on')
            @includeIf(ExtendingLayoutsConstants::CKC)
        @endif
    </body>
</html>
