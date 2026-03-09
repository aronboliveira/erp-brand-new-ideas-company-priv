@php
$bill ??= null;
	$creatorId ??= '';
	$data ??= [];
	$logo ??= '';
	$company_favicon ??= '';
	$colorSettings ??= [];
	$color ??= '';
	$company_setting ??= [];
	$mode_setting ??= '';
	$siteRtl ??= false;
	$meta_title ??= '';
	$meta_desc ??= '';
	$meta_image ??= '';
	$meta_logo ??= '';
	$get_cookie ??= '';
	$faviconUrl ??= '';
    $lang = Utility::fetchUserLang();
	try {
		$creatorId = $bill?->[DC::COL_TABLE_CREATOR] ?? '';
		$data = Utility::prepareCommonViewData($creatorId) ?: [];
		$logo = $data[SC::LOGO] ?? '';
		$company_favicon = $data[SC::FAV_ICN] ?? '';
		$colorSettings = $data[SC::CLR_STG] ?? [];
		$color = $data[SC::THM_CLR] ?? '';
		$company_setting = $data[SC::CPN_CFG] ?? [];
		$mode_setting = $data[SC::MD_LO] ?? '';
		$siteRtl = $data[SC::RTL] ?? false;
		$meta_title = $data[SC::MT_TTL_K] ?? '';
		$meta_desc = $data[SC::MT_DESC_LONG] ?? '';
		$meta_image = $data[SC::MT_IMG_K] ?? '';
		$meta_logo = $data[SC::MT_LOGO] ?? '';
		$get_cookie = $data[SC::CK_STG] ?? '';
		$faviconUrl = Utility::getCompanyLogo() ?: '';
	} catch (\Error $e) {
		Log::error(
			'Error fetching bill view data',
			[
				'exception_class'=>get_class($e),
				'message'=>$e->getMessage(),
				'file'=>$e->getFile(),
				'line'=>$e->getLine()
			]
		);
	} catch (\Exception $e) {
		Log::error(
			'Exception fetching bill view data',
			[
				'exception_class'=>get_class($e),
				'message'=>$e->getMessage(),
				'file'=>$e->getFile(),
				'line'=>$e->getLine()
			]
		);
	} catch (\Throwable $e) {
		Log::error(
			'Throwable fetching bill view data',
			[
				'exception_class'=>get_class($e),
				'message'=>$e->getMessage(),
				'file'=>$e->getFile(),
				'line'=>$e->getLine()
			]
		);
	}
    $data = Utility::fallbackSettings($data);
@endphp
<!DOCTYPE html>
<html lang="{{ $lang ? str_replace('_', '-', is_string(app()->getLocale()) ? (app()->getLocale() : DC::DEFAULT_LANG) : '') : '' }}" dir="{{ $siteRtl === 'on' ? 'rtl' : 'ltr' }}">
    <head>
        <title>{{(Utility::getValByName('title_text')) ? Utility::getValByName('title_text') :
        config('app.name', 'ERPNovaPrestech')}} - @yield('page-title')</title>
        @include('fragments.std', [
            'meta_title' => $meta_title,
            'meta_desc' => $meta_desc
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
        <script>
            if (!document.createElement('nav').style) {
                document.write('<script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"><\/script>');
                document.write('<script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"><\/script>');
            }
        </script>
        {{--    <meta name="url" content="{{ url('').'/'.config('chatify.path') }}" data-user="{{ Auth::user()->id }}">--}}
        @include('fragments.favicon', ['faviconUrl' => $faviconUrl])
        {{--    <link rel="icon" href="{{ asset('assets/images/favicon.svg') }}" type="image/x-icon"/>--}}
        <!-- Calendar-->
        @stack('css-page')
            <link rel="stylesheet" href="{{ asset('assets/css/plugins/main.css') }}">
            <link rel="stylesheet" href="{{ asset('assets/css/plugins/style.css') }}">
            <link rel="stylesheet" href="{{ asset('assets/css/plugins/flatpickr.min.css') }}">
            <link rel="stylesheet" href="{{ asset('assets/css/plugins/animate.min.css') }}">
            <link rel="stylesheet" href="{{ asset('assets/fonts/tabler-icons.min.css') }}">
            <link rel="stylesheet" href="{{ asset('assets/fonts/feather.css') }}">
            <link rel="stylesheet" href="{{ asset('assets/fonts/fontawesome.css') }}">
            <link rel="stylesheet" href="{{ asset('assets/fonts/material.css') }}">
            <link rel="stylesheet" href="{{ asset('assets/css/plugins/bootstrap-switch-button.min.css') }}">
            @if ($siteRtl == 'on')
                <link rel="stylesheet" href="{{ asset('assets/css/style-rtl.css') }}">
            @endif
            @if ($colorSettings[SC::CST_DRK] == 'on')
                <link rel="stylesheet" href="{{ asset('assets/css/style-dark.css') }}">
            @else
                <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}" id="main-style-link">
            @endif
            <link rel="stylesheet" href="{{ asset('assets/css/customizer.css') }}">
            <link rel="stylesheet" href="{{ asset('css/custom.css') }}" id="main-style-link">
        @stack('css-page')
    </head>
    <body class="{{ $color }}">
        <header class="header header-transparent" id="header-main"></header>
    @if(!empty($bill) && isset($bill))
        <div class="{{ VC::MCT_CT }}">
            <div class="{{ VC::R_ALC }} justify-content-between {{ VC::MB3 }}">
                <div class="{{ VC::CM12 }} {{ VC::DFL_AIC }} justify-content-between justify-content-md-end">
                    <div class="all-button-box mx-2">
                        @php
                            $billPdfRoute = Route::has(VW::BIL . '.pdf') ? route(VW::BIL . '.pdf', Crypt::encrypt($bill->id)) : '#';
                            $billPdfLinkId = 'bill-pdf-link-' . $bill->id;
@endphp
                        <a id="{{ $billPdfLinkId }}" href="{{ $billPdfRoute }}" target="_blank" class="{{ VC::BT_PRM }} {{ VC::MT3 }}" data-url="{{ $billPdfRoute }}">
                            {{ __('Download') }}
                        </a>
                        @push(StacksConstants::ADM_SCR_PG)
                            <script src="{{ asset('assets/js/core/route-guard.js') }}" defer></script>
                            <script src="{{ asset('assets/js/routes/bills/shared/customer-bill-helpers.js') }}" defer></script>
                        @endpush
                    </div>
                </div>
            </div>
            @php
                $canPriceFormat = method_exists($user, 'priceFormat');
@endphp
            <div class="{{ VC::RW }}">
                <div class="{{ VC::C12 }}">
                    <div class="{{ VC::CD }}">
                        <div class="{{ VC::CD_BD }}">
                            <div class="invoice">
                                <div class="invoice-print">
                                    <div class="{{ VC::RW }} invoice-title mt-2">
                                        <div class="{{ VC::CXS12 }} {{ VC::CS12 }} {{ VC::CM6 }} {{ VC::CL6 }} {{ VC::C12 }}">
                                            <h2>{{ __('Bill') }}</h2>
                                        </div>
                                        <div class="{{ VC::CXS12 }} {{ VC::CS12 }} {{ VC::CM6 }} {{ VC::CL6 }} {{ VC::C12 }} text-end">
                                            <h3 class="invoice-number float-right"></h3>
                                        </div>
                                        <div class="{{ VC::C12 }}">
                                            <hr>
                                        </div>
                                    </div>

                                    <div class="{{ VC::RW }}">
                                        <div class="col {{ VC::TX_END }}">
                                            <div class="{{ VC::DFL_AIC }} justify-content-end">
                                                <div class="me-4">
                                                    <small>
                                                        <strong>{{ __('Issue Date') }} :</strong><br>
                                                        {{ $user?->dateFormat($bill->issue_date) ?? __('No issue date available') }}<br><br>
                                                    </small>
                                                </div>
                                                <small>
                                                    <strong>{{ __('Due Date') }} :</strong><br>
                                                    {{ $user?->dateFormat($bill->due_date) ?? __('No due date available') }}<br><br>
                                                </small>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="{{ VC::RW }}">
                                        <div class="col">
                                            <small class="font-style">
                                                <strong>{{ __('Billed To') }} :</strong><br>
                                                {{ !empty($vendor->billing_name) ? $vendor->billing_name : __('No billing name available for vendor') }}<br>
                                                {{ !empty($vendor->billing_phone) ? $vendor->billing_phone : __('No billing phone available for vendor') }}<br>
                                                {{ !empty($vendor->billing_address) ? $vendor->billing_address : __('No billing address available for vendor') }}<br>
                                                {{ !empty($vendor->billing_zip) ? $vendor->billing_zip : __('No billing zip available for vendor') }}<br>
                                                {{ !empty($vendor->billing_city) ? $vendor->billing_city : __('No billing city available for vendor') }}, {{ !empty($vendor->billing_state) ? $vendor->billing_state : __('No billing state available for vendor') }}, {{ !empty($vendor->billing_country) ? $vendor->billing_country : __('No billing country available for vendor') }}
                                            </small>
                                        </div>

                                        @if(Utility::getValByName('shipping_display')=='on')
                                            <div class="col">
                                                <small>
                                                    <strong>{{ __('Shipped To') }} :</strong><br>
                                                    {{ !empty($vendor->shipping_name) ? $vendor->shipping_name : __('No shipping name available for vendor') }}<br>
                                                    {{ !empty($vendor->shipping_phone) ? $vendor->shipping_phone : __('No shipping phone available for vendor') }}<br>
                                                    {{ !empty($vendor->shipping_address) ? $vendor->shipping_address : __('No shipping address available for vendor') }}<br>
                                                    {{ !empty($vendor->shipping_zip) ? $vendor->shipping_zip : __('No shipping zip available for vendor') }}<br>
                                                    {{ !empty($vendor->shipping_city) ? $vendor->shipping_city : __('No shipping city available for vendor') }}, {{ !empty($vendor->shipping_state) ? $vendor->shipping_state : __('No shipping state available for vendor') }}, {{ !empty($vendor->shipping_country) ? $vendor->shipping_country : __('No shipping country available for vendor') }}
                                                </small>
                                            </div>
                                        @endif

                                        <div class="col">
                                            @php
                                                $qrRoute = Route::has(VW::BIL . '.link.copy') ? route(VW::BIL . '.link.copy', Crypt::encrypt($bill->id)) : '#';
                                                $qrId = 'bill-qr-copy-' . $bill->id;
@endphp
                                            <div id="{{ $qrId }}" class="{{ VC::FEND }} {{ VC::MT3 }}" data-url="{{ $qrRoute }}" style="cursor: pointer;">
                                                {!! DNS2D::getBarcodeHTML($qrRoute, 'QRCODE', 2, 2) !!}
                                            </div>
                                            {{-- QR copy handler auto-initialized via customer-bill-helpers.js --}}
                                        </div>
                                    </div>

                                    <div class="{{ VC::RW }} {{ VC::MT3 }}">
                                        <div class="col">
                                            @php
                                                $statusClasses = [0=>'bg-primary',1=>'bg-warning',2=>'bg-danger',3=>'bg-info',4=>'bg-primary'];
@endphp
                                            <small>
                                                <strong>{{ __('Status') }} :</strong><br>
                                                <span class="badge {{ $statusClasses[$bill->status] ?? 'bg-secondary' }}">{{ __(Bill::$statuses[$bill->status]) }}</span>
                                            </small>
                                        </div>

                                        @if(!empty($customFields) && count($bill->customField)>0)
                                            @foreach($customFields as $field)
                                                <div class="col text-md-right">
                                                    <small>
                                                        <strong>{{ !empty($field->name) ? $field->name : __('No name available for field') }} :</strong><br>
                                                        {{ !empty($bill->customField) ? ($bill->customField[$field->id] ?? '-') : '-' }}
                                                        <br><br>
                                                    </small>
                                                </div>
                                            @endforeach
                                        @else
                                            <div class="col">{{ __('No custom fields available') }}</div>
                                        @endif
                                    </div>

                                    <div class="{{ VC::RW }} {{ VC::MT4 }}">
                                        <div class="{{ VC::CM12 }}">
                                            <div class="font-weight-bold">{{ __('Product Summary') }}</div>
                                            <small>{{ __('All items here cannot be deleted.') }}</small>
                                            <div class="table-responsive {{ VC::MT2 ?? '' }}">
                                                <table class="{{ VC::TB }} table-striped">
                                                    @php
                                                        try {
                                                            $headers = [
                                                                ['label' => '#', 'class' => 'text-dark', 'width' => '40', 'data-width' => '40'],
                                                                ['label' => __('Product'), 'class' => 'text-dark'],
                                                                ['label' => __('Quantity'), 'class' => 'text-dark'],
                                                                ['label' => __('Rate'), 'class' => 'text-dark'],
                                                                ['label' => __('Tax'), 'class' => 'text-dark'],
                                                                ['label' => __('Discount'), 'class' => 'text-dark'],
                                                                ['label' => __('Description'), 'class' => 'text-dark'],
                                                                ['label' => __('Price'), 'class' => 'text-end text-dark', 'width' => '12%', 'subtitle' => __('after tax & discount')]
                                                            ];
                                                        } catch (\Throwable $e) {
                                                            \Log::error('bills/customer_bill — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                        }
@endphp
                                                    <tr>
                                                        @foreach($headers as $header)
                                                            <th class="{{ $header['class'] }}" @if(isset($header['width'])) width="{{ $header['width'] }}" @endif @if(isset($header['data-width'])) data-width="{{ $header['data-width'] }}" @endif>
                                                                {{ $header['label'] }}
                                                                @if(isset($header['subtitle']))
                                                                    <br><small class="{{ VC::TX_DNG }}">{{ $header['subtitle'] }}</small>
                                                                @endif
                                                            </th>
                                                        @endforeach
                                                    </tr>
                                                    @php
                                                        try {
                                                            $hasItems = isset($items) && ((is_array($items) && count($items)) || ($items instanceof Collection && $items->isNotEmpty()));
                                                            $totalQuantity = isset($totalQuantity) && is_numeric($totalQuantity) ? $totalQuantity : 0;
                                                            $totalRate = isset($totalRate) && is_numeric($totalRate) ? $totalRate : 0;
                                                            $totalDiscount = isset($totalDiscount) && is_numeric($totalDiscount) ? $totalDiscount : 0;
                                                            $totalTaxPrice = isset($totalTaxPrice) && is_numeric($totalTaxPrice) ? $totalTaxPrice : 0;
                                                            $taxesData = isset($taxesData) && is_array($taxesData) ? $taxesData : [];
                                                        } catch (\Throwable $e) {
                                                            \Log::error('bills/customer_bill — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                        }
@endphp
                                                    @if($hasItems)
                                                        @foreach($items as $key => $item)
                                                            @php
 $hasTaxes = false; $taxList = [];
@endphp
                                                            @if(!empty($item->tax))
                                                                @php
                                                                    try {
                                                                        $qty = is_numeric($item->quantity ?? null) ? (float) $item->quantity : 0;
                                                                        $price = is_numeric($item->price ?? null) ? (float) $item->price : 0;
                                                                        $disc = is_numeric($item->discount ?? null) ? (float) $item->discount : 0;
                                                                        $totalQuantity += $qty;
                                                                        $totalRate += $price;
                                                                        $totalDiscount += $disc;
                                                                        $maybeTaxes = Utility::tax($item->tax);
                                                                        $hasTaxes = (!empty($maybeTaxes) && (is_array($maybeTaxes) && count($maybeTaxes) || $maybeTaxes instanceof Collection && $maybeTaxes->isNotEmpty()));
                                                                        if ($hasTaxes) {
                                                                            foreach ($maybeTaxes as $t) { $taxList[] = $t; }
                                                                        }
                                                                        foreach ($taxList as $t) {
                                                                            $taxName = isset($t->name) && $t->name !== '' ? (string) $t->name : __('Tax');
                                                                            $taxRate = is_numeric($t->rate ?? null) ? (float) $t->rate : 0;
                                                                            $taxAmountForTotals = Utility::taxRate($taxRate, $price, $qty);
                                                                            $taxesData[$taxName] = ($taxesData[$taxName] ?? 0) + $taxAmountForTotals;
                                                                        }
                                                                    } catch (\Throwable $e) {
                                                                        \Log::error('bills/customer_bill — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                                    }
@endphp
                                                            @endif
                                                            <tr>
                                                                <td>{{ $key + 1 }}</td>
                                                                <td>
                                                                    @php
 $product = !empty($item->product()) ? $item->product() : null;
@endphp
                                                                    {{ !empty($product) && !empty($product->name) ? $product->name : __('No product name available') }}
                                                                </td>
                                                                <td>
                                                                    @if(is_numeric($item->quantity ?? null))
                                                                        {{ $item->quantity }}
                                                                    @else
                                                                        {{ __('No quantity available') }}
                                                                    @endif
                                                                </td>
                                                                <td>
                                                                    @if(is_numeric($item->price ?? null) && $canPriceFormat)
                                                                        {{ $user?->priceFormat($item->price) }}
                                                                    @else
                                                                        {{ __('No rate available') }}
                                                                    @endif
                                                                </td>
                                                                <td>
                                                                    @if(!empty($item->tax) && $hasTaxes)
                                                                        <table>
                                                                            @foreach($taxList as $t)
                                                                                @php
                                                                                    try {
                                                                                        $nameToShow = isset($t->name) && $t->name !== '' ? (string) $t->name : __('Tax');
                                                                                        $rateToUse = is_numeric($t->rate ?? null) ? (float) $t->rate : 0;
                                                                                        $rowTaxPrice = Utility::taxRate($rateToUse, is_numeric($item->price ?? null) ? (float)$item->price : 0, is_numeric($item->quantity ?? null) ? (float)$item->quantity : 0);
                                                                                        $totalTaxPrice += $rowTaxPrice;
                                                                                    } catch (\Throwable $e) {
                                                                                        \Log::error('bills/customer_bill — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                                                    }
@endphp
                                                                                <tr>
                                                                                    <td>{{ $nameToShow . ' (' . $rateToUse . '%)' }}</td>
                                                                                    <td>{{ $canPriceFormat ? $user?->priceFormat($rowTaxPrice) : __('Failed to get price format') }}</td>
                                                                                </tr>
                                                                            @endforeach
                                                                        </table>
                                                                    @else
                                                                        {{ __('No tax data available') }}
                                                                    @endif
                                                                </td>
                                                                <td>
                                                                    @if(is_numeric($item->discount ?? null))
                                                                        {{ $canPriceFormat ? $user?->priceFormat($item->discount) : __('Failed to get price format') }}
                                                                    @else
                                                                        {{ __('No discount available') }}
                                                                    @endif
                                                                </td>
                                                                <td>{{ !empty($item->description) ? $item->description : __('No description available') }}</td>
                                                                <td class="{{ VC::TX_END }}">
                                                                    @if(is_numeric($item->price ?? null) && is_numeric($item->quantity ?? null))
                                                                        {{ $canPriceFormat ? $user?->priceFormat(((float)$item->price * (float)$item->quantity)) : __('Failed to get price format') }}
                                                                    @else
                                                                        {{ __('No total price available') }}
                                                                    @endif
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    @else
                                                        <tr>
                                                            <td colspan="8" class="{{ VC::TXCT_DK }}">{{ __('No items found') }}</td>
                                                        </tr>
                                                    @endif
                                                    @if($canPriceFormat)
                                                        @php
                                                            $toNumber = function ($value, $default = null) {
                                                                return is_numeric($value) ? (float) $value : $default;
                                                            };

                                                            $safeCall = function ($object, $method, $default = null, array $args = []) {
                                                                if (is_object($object) && is_string($method) && method_exists($object, $method) && is_callable([$object, $method])) {
                                                                    try {
                                                                        $result = call_user_func_array([$object, $method], $args);
                                                                        return $result !== null ? $result : $default;
                                                                    } catch (\Throwable $e) {
                                                                        return $default;
                                                                    }
                                                                }
                                                                return $default;
                                                            };

                                                            $formatAmount = function ($amount) use ($user, $safeCall, $toNumber) {
                                                                $num = $toNumber($amount);
                                                                if ($num === null) {
                                                                    return 'No amount available';
                                                                }
                                                                $formatted = $safeCall($user ?? null, 'priceFormat', null, [$num]);
                                                                if (is_string($formatted) && $formatted !== '') {
                                                                    return $formatted;
                                                                }
                                                                return 'Could not format amount (raw: ' . number_format((float) $num, 2, '.', ',') . ')';
                                                            };

                                                            $totalQuantity    = $toNumber($totalQuantity ?? null, 0);
                                                            $totalRate        = $toNumber($totalRate ?? null, 0);
                                                            $totalTaxPrice    = $toNumber($totalTaxPrice ?? null, 0);
                                                            $totalDiscount    = $toNumber($totalDiscount ?? null, 0);
                                                            $taxesData        = is_array($taxesData ?? null) ? $taxesData : [];

                                                            $billSubTotal     = $toNumber($safeCall($bill ?? null, 'getSubTotal', 0), 0);
                                                            $billTotalDiscount= $toNumber($safeCall($bill ?? null, 'getTotalDiscount', 0), 0);
                                                            $billTotal        = $toNumber($safeCall($bill ?? null, 'getTotal', 0), 0);
                                                            $billDue          = $toNumber($safeCall($bill ?? null, 'getDue', 0), 0);
                                                            $billDebitNote    = $toNumber($safeCall($bill ?? null, 'billTotalDebitNote', 0), 0);
                                                            $billPaidRaw      = $toNumber(($billTotal - $billDue) - $billDebitNote, 0);

                                                            $columnTotals = [
                                                                '',
                                                                __('Total'),
                                                                $totalQuantity,
                                                                $formatAmount($totalRate),
                                                                $formatAmount($totalTaxPrice),
                                                                $formatAmount($totalDiscount),
                                                            ];

                                                            $summaryRows = [
                                                                ['label' => __('Sub Total'),  'value' => $formatAmount($billSubTotal)],
                                                                ['label' => __('Discount'),   'value' => $formatAmount($billTotalDiscount)],
                                                            ];

                                                            if (!empty($taxesData)) {
                                                                foreach ($taxesData as $taxName => $taxPrice) {
                                                                    $name = is_string($taxName) && $taxName !== '' ? $taxName : 'Tax';
                                                                    $summaryRows[] = ['label' => $name, 'value' => $formatAmount($toNumber($taxPrice, 0))];
                                                                }
                                                            }

                                                            $summaryRows = array_merge($summaryRows, [
                                                                ['label' => __('Total'),      'value' => $formatAmount($billTotal),   'class' => 'blue-text'],
                                                                ['label' => __('Paid'),       'value' => $formatAmount($billPaidRaw)],
                                                                ['label' => __('Debit Note'), 'value' => $formatAmount($billDebitNote)],
                                                                ['label' => __('Due'),        'value' => $formatAmount($billDue)],
                                                            ]);
@endphp
                                                        <tfoot>
                                                            <tr>
                                                                @foreach($columnTotals as $total)
                                                                    <td><b>{{ $total }}</b></td>
                                                                @endforeach
                                                            </tr>
                                                            @foreach($summaryRows as $row)
                                                                <tr>
                                                                    <td colspan="6"></td>
                                                                    <td class="{{ VC::TX_END }} {{ $row['class'] ?? '' }}"><b>{{ $row['label'] }}</b></td>
                                                                    <td class="{{ VC::TX_END }} {{ $row['class'] ?? '' }}">{{ $row['value'] }}</td>
                                                                </tr>
                                                            @endforeach
                                                        </tfoot>
                                                    @else
                                                        <tfoot>
                                                            <tr>
                                                                <td colspan="8" class="{{ VC::TXCT_DK }}">{{ __('Unable to show totals, user price format method not found') }}</td>
                                                            </tr>
                                                        </tfoot>
                                                    @endif
                                                </table>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="{{ VC::RW }}">
                <div class="{{ VC::C12 }}">
                    <div class="{{ VC::CD }}">
                        <div class="{{ VC::CD_BD_TB_BD }}">
                            <h5 class="d-inline-block {{ VC::MB5 }}">{{ __('Payment Summary') }}</h5>
                            <div class="{{ VC::TB_RSP }}">
                                <table class="{{ VC::TB }} table-striped">
                                    <thead>
                                        <tr>
                                            @foreach([__('Date'), __('Amount'), __('Account'), __('Reference'), __('Description')] as $header)
                                                <th class="{{ VC::TX_DK }}">{{ $header }}</th>
                                            @endforeach
                                        </tr>
                                    </thead>
                                    @php
                                        $safeCall = function ($object, $method, $default = null, array $args = []) {
                                            if (is_object($object) && is_string($method) && method_exists($object, $method) && is_callable([$object, $method])) {
                                                try {
                                                    $result = call_user_func_array([$object, $method], $args);
                                                    return $result !== null ? $result : $default;
                                                } catch (\Throwable $e) {
                                                    return $default;
                                                }
                                            }
                                            return $default;
                                        };
                                        $formatDate = function ($value) use ($user, $safeCall) {
                                            if (empty($value)) {
                                                return 'No payment date available';
                                            }
                                            $formatted = $safeCall($user ?? null, 'dateFormat', null, [$value]);
                                            return is_string($formatted) && $formatted !== '' ? $formatted : 'Could not format payment date';
                                        };
                                        $formatAmount = function ($value) use ($user, $safeCall) {
                                            if (!is_numeric($value)) {
                                                return 'No amount available';
                                            }
                                            $formatted = $safeCall($user ?? null, 'priceFormat', null, [(float) $value]);
                                            return is_string($formatted) && $formatted !== '' ? $formatted : ('Could not format amount (raw: ' . number_format((float) $value, 2, '.', ',') . ')');
                                        };
                                        $formatAccount = function ($bankAccount) {
                                            $bank = is_object($bankAccount) ? trim((string) ($bankAccount->bank_name ?? '')) : '';
                                            $holder = is_object($bankAccount) ? trim((string) ($bankAccount->holder_name ?? '')) : '';
                                            $both = trim($bank . ' ' . $holder);
                                            return $both !== '' ? $both : 'No bank account available';
                                        };
                                        $paymentsSource = is_object($bill ?? null) ? ($bill->payments ?? null) : null;
                                        $hasPayments = (!empty($paymentsSource) && ((is_array($paymentsSource) && count($paymentsSource)) || ($paymentsSource instanceof \Illuminate\Support\Collection && $paymentsSource->isNotEmpty())));
@endphp
                                    @if($hasPayments)
                                        @foreach($paymentsSource as $index => $payment)
                                            <tr>
                                                <td>{{ $formatDate($payment->date ?? null) }}</td>
                                                <td>{{ $formatAmount($payment->amount ?? null) }}</td>
                                                <td>{{ $formatAccount($payment->bankAccount ?? null) }}</td>
                                                <td>{{ isset($payment->reference) && $payment->reference !== '' ? $payment->reference : 'No reference available' }}</td>
                                                <td>{{ isset($payment->description) && $payment->description !== '' ? $payment->description : 'No description available' }}</td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr>
                                            <td colspan="6" class="{{ VC::TXCT_DK }}"><p>{{ __('No Data Found') }}</p></td>
                                        </tr>
                                    @endif
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <footer id="footer-main">
            <div class="footer-dark">
                <div class="{{ VC::CT }}">
                    <div class="{{ VC::R_ALC }} justify-content-md-between py-4 {{ VC::MT4 }} delimiter-top">
                        <div class="{{ VC::CM6 }}">
                            <div class="copyright {{ VC::TXSM }} font-weight-bold text-center text-md-left">
                                {{ !empty($companySettings[SC::FT_TXT]) ? $companySettings[SC::FT_TXT]->value : '' }}
                            </div>
                        </div>
                        <div class="{{ VC::CM6 }}">
                            @php
                                try {
                                    $socialLinks = [
                                        ['icon' => 'fab fa-dribbble', 'url' => '#'],
                                        ['icon' => 'fab fa-instagram', 'url' => '#'],
                                        ['icon' => 'fab fa-github', 'url' => '#'],
                                        ['icon' => 'fab fa-facebook', 'url' => '#']
                                    ];
                                } catch (\Throwable $e) {
                                    \Log::error('bills/customer_bill — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                }
@endphp
                            <ul class="{{ VC::NAV }} justify-content-center justify-content-md-end mt-3 mt-md-0">
                                @foreach($socialLinks as $link)
                                    <li class="{{ VC::NV_IT }}">
                                        <a class="{{ VC::NV_LK }}" href="{{ $link['url'] }}" target="_blank">
                                            <i class="{{ $link['icon'] }}"></i>
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </footer>
        @if($message = Session::get('success'))
            <script>
                (() => {typeof show_toastr === 'function' && show_toastr('success', '{!! $message !!}');})()
            </script>
        @endif
        @if($message = Session::get('error'))
            <script>
                (() => {typeof show_toastr === 'function' && show_toastr('error', '{!! $message !!}');})()
            </script>
        @endif
        @if($get_cookie['enable_cookie'] == 'on')
            @includeIf(ELC::CKC)
        @endif
    @else
        <div class="{{ VC::R_ALC }} {{ VC::MT5 }} justify-content-center">
            <div class="{{ VC::CM6 }}">
                <div class="{{ VC::ALT_DNG }} {{ VC::TXCT }}">
                    {{ __('Bill not found') }}
                </div>
            </div>
        </div>
    @endif
