@php
	use App\Config\Constants\{
		DatabaseConstants,
		ExtendingLayoutsConstants,
		SettingsConstants,
		ViewClassNamesConstants as VC,
		ViewsConstants,
		YieldingConstants
	};
	use App\Models\{Bill,Utility};
	use Illuminate\Support\Facades\{Crypt,Log,Route};
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
		$creatorId = $bill?->[DatabaseConstants::TABLE_CREATOR] ?? '';
		$data = Utility::prepareCommonViewData($creatorId) ?: [];
		$logo = $data[SettingsConstants::LOGO] ?? '';
		$company_favicon = $data[SettingsConstants::FAV_ICN] ?? '';
		$colorSettings = $data[SettingsConstants::CLR_STG] ?? [];
		$color = $data[SettingsConstants::THM_CLR] ?? '';
		$company_setting = $data[SettingsConstants::CPN_CFG] ?? [];
		$mode_setting = $data[SettingsConstants::MD_LO] ?? '';
		$siteRtl = $data[SettingsConstants::RTL] ?? false;
		$meta_title = $data[SettingsConstants::MT_TTL_K] ?? '';
		$meta_desc = $data[SettingsConstants::MT_DESC_LONG] ?? '';
		$meta_image = $data[SettingsConstants::MT_IMG_K] ?? '';
		$meta_logo = $data[SettingsConstants::MT_LOGO] ?? '';
		$get_cookie = $data[SettingsConstants::CK_STG] ?? '';
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
<html lang="{{ $lang ? str_replace('_', '-', is_string(app()->getLocale()) ? (app()->getLocale() : DatabaseConstants::DEFAULT_LANG) : '') : '' }}" dir="{{ $siteRtl === 'on' ? 'rtl' : 'ltr' }}">
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
            @if ($colorSettings[SettingsConstants::CST_DRK] == 'on')
                <link rel="stylesheet" href="{{ asset('assets/css/style-dark.css') }}">
            @else
                <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}" id="main-style-link">
            @endif
            <link rel="stylesheet" href="{{ asset('assets/css/customizer.css') }}">
            <link rel="stylesheet" href="{{ asset('css/custom.css') }}" id="main-style-link">
        @stack('css-page')
    </head>
    <body class="{{ $color }}">
        <header class="header header-transparent" id="header-main">
        </header>
        <div class="{{ VC::MCT_CT }}">
            <div class="row justify-content-between align-items-center mb-3">
                <div class="col-md-12 d-flex align-items-center justify-content-between justify-content-md-end">
                    <div class="all-button-box mx-2">
                        @php
                            $billPdfRoute   = Route::has(ViewsConstants::BIL . '.pdf')
                                ? route(ViewsConstants::BIL . '.pdf', Crypt::encrypt($bill->id))
                                : '#';
                            $billPdfLinkId  = 'bill-pdf-link-' . $bill->id;
                            $billPdfMsg     = Utility::fetchLinkMessage(
                                $lang,
                                ViewsConstants::BIL,
                                'bill_pdf_route_unavailable'
                            ) ?? 'Bill PDF route is unavailable. Please contact technical support or your domain administrator.';
                        @endphp
                        <a
                            id="{{ $billPdfLinkId }}"
                            href="{{ $billPdfRoute }}"
                            target="_blank"
                            class="{{ VC::BT_PRM }} mt-3"
                            data-url="{{ $billPdfRoute }}"
                            data-guard-msg="{{ $billPdfMsg }}"
                        >
                            {{ __('Download') }}
                        </a>
                        @push(StacksConstants::ADM_SCR_PG)
                            <script defer>
                                (() => {
                                    const link = document.getElementById('{{ $billPdfLinkId }}');
                                    if (!link || link.getAttribute('data-listener-active') === 'true') return;
                                    link.setAttribute('data-listener-active', 'true');
                                    link.addEventListener('click', event => {
                                        try {
                                            const href = link.getAttribute('href');
                                            const url  = link.getAttribute('data-url');
                                            if ((href && href !== '#') || (url && url !== '#')) return;
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
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="invoice">
                                <div class="invoice-print">
                                    <div class="row invoice-title mt-2">
                                        <div class="col-xs-12 col-sm-12 col-nd-6 col-lg-6 col-12">
                                            <h2>{{__('Bill')}}</h2>
                                        </div>
                                        <div class="col-xs-12 col-sm-12 col-nd-6 col-lg-6 col-12 text-end">
                                            <h3 class="invoice-number float-right"></h3>
                                        </div>
                                        <div class="col-12">
                                            <hr>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col text-end">
                                            <div class="d-flex align-items-center justify-content-end">
                                                <div class="me-4">
                                                    <small>
                                                        <strong>{{__('Issue Date')}} :</strong><br>
                                                        {{$user?->dateFormat($bill->issue_date)}}<br><br>
                                                    </small>
                                                </div>
                                                <small>
                                                    <strong>{{__('Due Date')}} :</strong><br>
                                                    {{$user?->dateFormat($bill->due_date)}}<br><br>
                                                </small>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        @if(!empty($vendor->billing_name))
                                            <div class="col">
                                                <small class="font-style">
                                                    <strong>{{__('Billed To')}} :</strong><br>
                                                    {{!empty($vendor->billing_name)?$vendor->billing_name:''}}<br>
                                                    {{!empty($vendor->billing_phone)?$vendor->billing_phone:''}}<br>
                                                    {{!empty($vendor->billing_address)?$vendor->billing_address:''}}<br>
                                                    {{!empty($vendor->billing_zip)?$vendor->billing_zip:''}}<br>
                                                    {{!empty($vendor->billing_city)?$vendor->billing_city:'' .', '}} {{!empty($vendor->billing_state)?$vendor->billing_state:'',', '}} {{!empty($vendor->billing_country)?$vendor->billing_country:''}}
                                                </small>
                                            </div>
                                        @endif
                                        @if(\Utility::getValByName('shipping_display')=='on')
                                            <div class="col">
                                                <small>
                                                    <strong>{{__('Shipped To')}} :</strong><br>
                                                    {{!empty($vendor->shipping_name)?$vendor->shipping_name:''}}<br>
                                                    {{!empty($vendor->shipping_phone)?$vendor->shipping_phone:''}}<br>
                                                    {{!empty($vendor->shipping_address)?$vendor->shipping_address:''}}<br>
                                                    {{!empty($vendor->shipping_zip)?$vendor->shipping_zip:''}}<br>
                                                    {{!empty($vendor->shipping_city)?$vendor->shipping_city:'' .', '}} {{!empty($vendor->shipping_state)?$vendor->shipping_state:'',', '}} {{!empty($vendor->shipping_country)?$vendor->shipping_country:''}}
                                                </small>
                                            </div>
                                        @endif
                                        <div class="col">
                                            @php
                                                $qrRoute        = Route::has(ViewsConstants::BIL . '.link.copy')
                                                    ? route(ViewsConstants::BIL . '.link.copy', Crypt::encrypt($bill->id))
                                                    : '#';
                                                $qrId           = 'bill-qr-copy-' . $bill->id;
                                                $qrGuardMsg     = Utility::fetchLinkMessage(
                                                    $lang,
                                                    ViewsConstants::BIL,
                                                    'bill_link_copy_route_unavailable'
                                                ) ?? 'Bill link copy route is unavailable. Please contact technical support or your domain administrator.';
                                            @endphp
                                            <div
                                                id="{{ $qrId }}"
                                                class="float-end mt-3"
                                                data-url="{{ $qrRoute }}"
                                                data-guard-msg="{{ $qrGuardMsg }}"
                                                style="cursor: pointer;"
                                            >
                                                {!! DNS2D::getBarcodeHTML($qrRoute, 'QRCODE', 2, 2) !!}
                                            </div>
                                            @push(StacksConstants::ADM_SCR_PG)
                                                <script defer>
                                                    (() => {
                                                        const el = document.getElementById('{{ $qrId }}');
                                                        if (!el || el.getAttribute('data-listener-active') === 'true') return;
                                                        el.setAttribute('data-listener-active', 'true');
                                                        el.addEventListener('click', event => {
                                                            try {
                                                                const url = el.getAttribute('data-url');
                                                                if (!url || url === '#') {
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
                                                                    return;
                                                                }
                                                                navigator.clipboard.writeText(url);
                                                            } catch (e) {}
                                                        });
                                                    })();
                                                </script>
                                            @endpush
                                        </div>
                                    </div>
                                    <div class="row mt-3">
                                        <div class="col">
                                            @php
                                                $statusClasses = [
                                                    0 => 'bg-primary',
                                                    1 => 'bg-warning', 
                                                    2 => 'bg-danger',
                                                    3 => 'bg-info',
                                                    4 => 'bg-primary'
                                                ];
                                            @endphp
                                            <small>
                                                <strong>{{__('Status')}} :</strong><br>
                                                <span class="badge {{ $statusClasses[$bill->status] ?? 'bg-secondary' }}">
                                                    {{ __(Bill::$statuses[$bill->status]) }}
                                                </span>
                                            </small>
                                        </div>
                                        @if(!empty($customFields) && count($bill->customField)>0)
                                            @foreach($customFields as $field)
                                                <div class="col text-md-right">
                                                    <small>
                                                        <strong>{{$field->name}} :</strong><br>
                                                        {{!empty($bill->customField)?$bill->customField[$field->id]:'-'}}
                                                        <br><br>
                                                    </small>
                                                </div>
                                            @endforeach
                                        @endif
                                    </div>
                                    <div class="row mt-4">
                                        <div class="col-md-12">
                                            <div class="font-weight-bold">{{__('Product Summary')}}</div>
                                            <small>{{__('All items here cannot be deleted.')}}</small>
                                            <div class="table-responsive mt-2">
                                                <table class="table table-striped">
                                                    @php
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
                                                    @endphp
                                                    <tr>
                                                        @foreach($headers as $header)
                                                            <th class="{{ $header['class'] }}" 
                                                                @if(isset($header['width'])) width="{{ $header['width'] }}" @endif
                                                                @if(isset($header['data-width'])) data-width="{{ $header['data-width'] }}" @endif>
                                                                {{ $header['label'] }}
                                                                @if(isset($header['subtitle']))
                                                                    <br><small class="text-danger">{{ $header['subtitle'] }}</small>
                                                                @endif
                                                            </th>
                                                        @endforeach
                                                    </tr>
                                                    @php
                                                        $totalQuantity=0;
                                                        $totalRate=0;
                                                        $totalTaxPrice=0;
                                                        $totalDiscount=0;
                                                        $taxesData=[];
                                                    @endphp
                                                    @foreach($iteams as $key =>$iteam)
                                                        @if(!empty($iteam->tax))
                                                            @php
                                                                $taxes = Utility::tax($iteam->tax);
                                                                $totalQuantity+=$iteam->quantity;
                                                                $totalRate+=$iteam->price;
                                                                $totalDiscount+=$iteam->discount;
                                                                foreach($taxes as $taxe){
                                                                    $taxDataPrice = Utility::taxRate($taxe->rate,$iteam->price,$iteam->quantity);
                                                                    if (array_key_exists($taxe->name,$taxesData))
                                                                    {
                                                                        $taxesData[$taxe->name] = $taxesData[$taxe->name]+$taxDataPrice;
                                                                    }
                                                                    else
                                                                    {
                                                                        $taxesData[$taxe->name] = $taxDataPrice;
                                                                    }
                                                                }
                                                            @endphp
                                                        @endif
                                                        <tr>
                                                            <td>{{$key+1}}</td>
                                                            <td>{{!empty($iteam->product())?$iteam->product()->name:''}}</td>
                                                            <td>{{$iteam->quantity}}</td>
                                                            <td>{{$user?->priceFormat($iteam->price)}}</td>
                                                            <td>
                                                                @if(!empty($iteam->tax))
                                                                    <table>
                                                                        @php $totalTaxRate = 0;@endphp
                                                                        @foreach($taxes as $tax)
                                                                            @php
                                                                                $taxPrice = Utility::taxRate($tax->rate,$iteam->price,$iteam->quantity);
                                                                                $totalTaxPrice+=$taxPrice;
                                                                            @endphp
                                                                            <tr>
                                                                                <td>{{$tax->name .' ('.$tax->rate .'%)'}}</td>
                                                                                <td>{{$user?->priceFormat($taxPrice)}}</td>
                                                                            </tr>
                                                                        @endforeach
                                                                    </table>
                                                                @else
                                                                    -
                                                                @endif
                                                            </td>
                                                            <td>
                                                                {{$user?->priceFormat($iteam->discount)}}
                                                            </td>
                                                            <td>{{!empty($iteam->description)?$iteam->description:'-'}}</td>
                                                            <td class="text-end">{{$user?->priceFormat(($iteam->price*$iteam->quantity))}}</td>
                                                        </tr>
                                                    @endforeach
                                                    @php
                                                        $columnTotals = [
                                                            '',
                                                            __('Total'),
                                                            $totalQuantity,
                                                            $user?->priceFormat($totalRate),
                                                            $user?->priceFormat($totalTaxPrice),
                                                            $user?->priceFormat($totalDiscount)
                                                        ];
                                                        $summaryRows = [
                                                            ['label' => __('Sub Total'), 'value' => $user?->priceFormat($bill->getSubTotal())],
                                                            ['label' => __('Discount'), 'value' => $user?->priceFormat($bill->getTotalDiscount())],
                                                        ];
                                                        if (!empty($taxesData)) {
                                                            foreach ($taxesData as $taxName => $taxPrice) {
                                                                $summaryRows[] = ['label' => $taxName, 'value' => $user?->priceFormat($taxPrice)];
                                                            }
                                                        }
                                                        $summaryRows = array_merge($summaryRows, [
                                                            ['label' => __('Total'), 'value' => $user?->priceFormat($bill->getTotal()), 'class' => 'blue-text'],
                                                            ['label' => __('Paid'), 'value' => $user?->priceFormat(($bill->getTotal()-$bill->getDue())-($bill->billTotalDebitNote()))],
                                                            ['label' => __('Debit Note'), 'value' => $user?->priceFormat($bill->billTotalDebitNote())],
                                                            ['label' => __('Due'), 'value' => $user?->priceFormat($bill->getDue())]
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
                                                                <td class="text-end {{ $row['class'] ?? '' }}">
                                                                    <b>{{ $row['label'] }}</b>
                                                                </td>
                                                                <td class="text-end {{ $row['class'] ?? '' }}">
                                                                    {{ $row['value'] }}
                                                                </td>
                                                            </tr>
                                                        @endforeach
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
            </div>
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body table-border-style">
                            <h5 class="d-inline-block mb-5">{{__('Payment Summary')}}</h5>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            @foreach([__('Date'), __('Amount'), __('Account'), __('Reference'), __('Description')] as $header)
                                                <th class="text-dark">{{ $header }}</th>
                                            @endforeach
                                        </tr>
                                    </thead>
                                    @forelse($bill->payments as $key =>$payment)
                                        <tr>
                                            <td>{{$user?->dateFormat($payment->date)}}</td>
                                            <td>{{$user?->priceFormat($payment->amount)}}</td>
                                            <td>{{!empty($payment->bankAccount)?$payment->bankAccount->bank_name.' '.$payment->bankAccount->holder_name:''}}</td>
                                            <td>{{$payment->reference}}</td>
                                            <td>{{$payment->description}}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center text-dark"><p>{{__('No Data Found')}}</p></td>
                                        </tr>
                                    @endforelse
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
                    <div class="row align-items-center justify-content-md-between py-4 mt-4 delimiter-top">
                        <div class="col-md-6">
                            <div class="copyright text-sm font-weight-bold text-center text-md-left">
                                {{!empty($companySettings[SettingsConstants::FT_TXT]) ? $companySettings[SettingsConstants::FT_TXT]->value : ''}}
                            </div>
                        </div>
                        <div class="col-md-6">
                            @php
                                $socialLinks = [
                                    ['icon' => 'fab fa-dribbble', 'url' => '#'],
                                    ['icon' => 'fab fa-instagram', 'url' => '#'],
                                    ['icon' => 'fab fa-github', 'url' => '#'],
                                    ['icon' => 'fab fa-facebook', 'url' => '#']
                                ];
                            @endphp
                            <ul class="nav justify-content-center justify-content-md-end mt-3 mt-md-0">
                                @foreach($socialLinks as $link)
                                    <li class="nav-item">
                                        <a class="nav-link" href="{{ $link['url'] }}" target="_blank">
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
                show_toastr('success', '{!! $message !!}');
            </script>
        @endif
        @if($message = Session::get('error'))
            <script>
                show_toastr('error', '{!! $message !!}');
            </script>
        @endif
        @if($get_cookie['enable_cookie'] == 'on')
            @includeIf(ExtendingLayoutsConstants::CKC)
        @endif





