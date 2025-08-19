@php
	use App\Config\Constants\{
		DatabaseConstants,
		ExtendingLayoutsConstants,
		SettingsConstants as SC,
		ViewClassNamesConstants as VC,
		ViewsConstants as VW,
	};
	use App\Models\{Proposal,Utility};
	use Illuminate\Support\Facades\{Crypt,Log};
    use Illuminate\Support\Str;
	$proposal ??= null;
	$data ??= [];
	$logo ??= '';
	$company_favicon ??= '';
	$colorSettings ??= [];
	$color ??= '';
	$company_setting ??= [];
	$meta_title ??= '';
	$meta_desc ??= '';
	$meta_image ??= '';
	$meta_logo ??= '';
	$get_cookie ??= '';
	$faviconUrl ??= '';
    $lang = Utility::fetchUserLang();
	try {
		$data = Utility::prepareCommonViewData(
			$proposal?->{DatabaseConstants::TABLE_CREATOR} ?? '',
			'uploads/logo'
		) ?: [];
		$logo = $data[SC::LOGO] ?? '';
		$company_favicon = $data[SC::FAV_ICN] ?? '';
		$colorSettings = $data[SC::CLR_STG] ?? [];
		$color = $data[SC::THM_CLR] ?? '';
		$company_setting = $data[SC::CPN_CFG] ?? [];
		$meta_title = $data[SC::MT_TTL_K] ?? '';
		$meta_desc = $data[SC::MT_DESC_LONG] ?? '';
		$meta_image = $data[SC::MT_IMG_K] ?? '';
		$meta_logo = $data[SC::MT_LOGO] ?? '';
		$get_cookie = $data[SC::CK_STG] ?? '';
		$faviconUrl = Utility::getCompanyLogo() ?: '';
	} catch (\Error $e) {
		Log::error(
			'Error preparing proposal view data',
			[
				'exception_class' => get_class($e),
				'message' => $e->getMessage(),
				'file' => $e->getFile(),
				'line' => $e->getLine()
			]
		);
	} catch (\Exception $e) {
		Log::error(
			'Exception preparing proposal view data',
			[
				'exception_class' => get_class($e),
				'message' => $e->getMessage(),
				'file' => $e->getFile(),
				'line' => $e->getLine()
			]
		);
	} catch (\Throwable $e) {
		Log::error(
			'Throwable preparing proposal view data',
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
<html lang="{{ str_replace('_', '-', is_string(app()->getLocale()) ? app()->getLocale() : DatabaseConstants::DEFAULT_LANG) }}" dir="{{ $siteRtl === 'on' ? 'rtl' : 'ltr' }}">
        <head>
            @include('fragments.std', [
                'meta_title' => $meta_title,
                'meta_desc' => $meta_desc,
                'meta_vp' => 'shrink-to-fit=no',
                ])
            <title>{{(Utility::companyData($proposal[DatabaseConstants::TABLE_CREATOR],'title_text')) ? Utility::companyData($proposal[DatabaseConstants::TABLE_CREATOR],'title_text') : config('app.name', 'ERPNovaPrestech')}} - {{__('Proposal')}}</title>
            <meta name="title" content="{{$meta_title}}">
            <meta name="description" content="{{$meta_desc}}">
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
            <link rel="stylesheet" href="{{ asset('assets/css/plugins/main.css') }}">
            <link rel="stylesheet" href="{{ asset('assets/css/plugins/style.css') }}">
            <link rel="stylesheet" href="{{ asset('assets/css/plugins/animate.min.css') }}">
            <link rel="stylesheet" href="{{ asset('assets/fonts/tabler-icons.min.css') }}">
            <link rel="stylesheet" href="{{ asset('assets/fonts/feather.css') }}">
            <link rel="stylesheet" href="{{ asset('assets/fonts/fontawesome.css') }}">
            <link rel="stylesheet" href="{{ asset('assets/fonts/material.css') }}">
            <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}" id="main-style-link">
            <link rel="stylesheet" href="{{ asset('assets/css/customizer.css') }}">
            <link rel="stylesheet" href="{{ asset('css/custom.css') }}" id="main-style-link">
            <link rel="stylesheet" href="{{ asset('assets/css/plugins/bootstrap-switch-button.min.css') }}">
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
                <div class="{{ VC::RW }} {{ VC::JCB }} {{ VC::ALC }} {{ VC::MB3 }}">
                    <div class="{{ VC::CM12 }} {{ VC::DFL_AIC_JCB }} justify-content-md-end">
                        <div class="all-button-box mx-2">
                            @php
                                $proposalPdfBaseName     = ViewsConstants::PPS.'.pdf';
                                $proposalPdfKebabName    = Str::kebab($proposalPdfBaseName);
                                $proposalPdfResolvedName = Route::has($proposalPdfBaseName)
                                    ? $proposalPdfBaseName
                                    : (Route::has($proposalPdfKebabName) ? $proposalPdfKebabName : null);
                                $proposalIdValue         = isset($proposal) && !empty($proposal->id) ? $proposal->id : null;
                                $encryptedProposalId     = $proposalIdValue ? Crypt::encrypt($proposalIdValue) : null;
                                $proposalPdfUrl          = ($proposalPdfResolvedName && $encryptedProposalId) ? route($proposalPdfResolvedName, $encryptedProposalId) : '#';
                                $proposalPdfGuardMsg     = Utility::fetchLinkMessage($lang, ViewsConstants::PPS, 'download_proposal_pdf_route_unavailable') ?? 'Download proposal pdf route is unavailable. Please contact technical support or your domain administrator.';
                                $proposalPdfLinkId       = 'proposal-pdf-download-link-'.($proposalIdValue ?? 'x');
                            @endphp
                            <a href="{{ $proposalPdfUrl }}"
                            id="{{ $proposalPdfLinkId }}"
                            target="_blank"
                            class="{{ VC::BT_PRM }} {{ VC::MT3 }}"
                            data-url="{{ $proposalPdfUrl }}"
                            data-guard-msg="{{ $proposalPdfGuardMsg }}">
                                {{ __('Download') }}
                            </a>
                        </div>
                    </div>
                </div>
                <div class="{{ VC::RW }}">
                    <div class="col-12">
                        <div class="{{ VC::CD }}">
                            <div class="card-body">
                                <div class="proposal">
                                    <div class="proposal-print">
                                        <div class="{{ VC::RW }} invoice-title mt-2">
                                            <div class="col-xs-12 col-sm-12 col-nd-6 col-lg-6 col-12">
                                                <h2>{{__('Proposal')}}</h2>
                                            </div>
                                            <div class="col-12">
                                                <hr>
                                            </div>
                                        </div>
                                        <div class="{{ VC::RW }}">
                                            @if(!empty($customer))
                                                <div class="col">
                                                    <small class="font-style">
                                                        <strong>{{__('Billed To')}} :</strong><br>
                                                        {{!empty($customer->billing_name)?$customer->billing_name:__('Name not found.')}}<br>
                                                        {{!empty($customer->billing_phone)?$customer->billing_phone:__('Phone number not found.')}}<br>
                                                        {{!empty($customer->billing_address)?$customer->billing_address:__('Address not found.')}}<br>
                                                        {{!empty($customer->billing_zip)?$customer->billing_zip:__('ZIP Code not found.')}}<br>
                                                        {{!empty($customer->billing_city)?$customer->billing_city:__('City not found.')}} {{!empty($customer->billing_state)?$customer->billing_state: __('State not found.')}} {{!empty($customer->billing_country)?$customer->billing_country:__('Country not found.')}}
                                                    </small>
                                                </div>
                                            @endif
                                            @if(Utility::companyData($proposal[DatabaseConstants::TABLE_CREATOR],'shipping_display')=='on')
                                                <div class="col">
                                                    <small>
                                                        <strong>{{__('Shipped To')}} :</strong><br>
                                                        {{!empty($customer->shipping_name)?$customer->shipping_name:__('Name not found.')}}<br>
                                                        {{!empty($customer->shipping_phone)?$customer->shipping_phone:__('Phone number not found.')}}<br>
                                                        {{!empty($customer->shipping_address)?$customer->shipping_address:__('Address not found.')}}<br>
                                                        {{!empty($customer->shipping_zip)?$customer->shipping_zip:__('ZIP Code not found.')}}<br>
                                                        {{!empty($customer->shipping_city)?$customer->shipping_city:__('City not found.')}} {{!empty($customer->shipping_state)?$customer->shipping_state: __('State not found.')}} {{!empty($customer->shipping_country)?$customer->shipping_country:__('Country not found.')}}
                                                    </small>
                                                </div>
                                            @endif
                                            <div class="col">
                                                <div class="{{ VC::FEND }} {{ VC::MT3 }}">
                                                    @if(!empty($proposal->id))
                                                        @php
                                                            $proposalLinkCopyBaseName     = ViewsConstants::PPS.'.link.copy';
                                                            $proposalLinkCopyKebabName    = Str::kebab($proposalLinkCopyBaseName);
                                                            $proposalLinkCopyResolvedName = Route::has($proposalLinkCopyBaseName)
                                                                ? $proposalLinkCopyBaseName
                                                                : (Route::has($proposalLinkCopyKebabName) ? $proposalLinkCopyKebabName : null);
                                                            $proposalIdValue              = isset($proposal) && !empty($proposal->id) ? $proposal->id : null;
                                                            $encryptedProposalId          = $proposalIdValue ? Crypt::encrypt($proposalIdValue) : null;
                                                            $proposalLinkCopyUrl          = ($proposalLinkCopyResolvedName && $encryptedProposalId) ? route($proposalLinkCopyResolvedName, $encryptedProposalId) : '#';
                                                            $langValue                    = isset($lang) ? $lang : Utility::fetchUserLang();
                                                            $proposalLinkCopyGuardMsg     = Utility::fetchLinkMessage($langValue, ViewsConstants::PPS, 'copy_proposal_link_route_unavailable') ?? 'Copy proposal link route is unavailable. Please contact technical support or your domain administrator.';
                                                            $proposalLinkCopyQrId         = 'proposal-link-copy-qr-'.($proposalIdValue ?? 'x');
                                                        @endphp
                                                        <div id="{{ $proposalLinkCopyQrId }}"
                                                            class="{{ VC::DBL }}"
                                                            data-url="{{ $proposalLinkCopyUrl }}"
                                                            data-guard-msg="{{ $proposalLinkCopyGuardMsg }}">
                                                            {!! DNS2D::getBarcodeHTML($proposalLinkCopyUrl, 'QRCODE', 2, 2) !!}
                                                        </div>
                                                        <script defer>
                                                            (() => {
                                                                try {
                                                                    const c = document.getElementById('{{ $proposalLinkCopyQrId }}');
                                                                    if (!c || c.getAttribute('data-listener-active') === 'true') return;
                                                                    c.setAttribute('data-listener-active', 'true');
                                                                    c.addEventListener('click', e => {
                                                                        try {
                                                                            const url = c.getAttribute('data-url') || '#';
                                                                            if (url !== '#') return;
                                                                            e.preventDefault();
                                                                            const msg = c.getAttribute('data-guard-msg') || 'Copy proposal link route is unavailable. Please contact technical support or your domain administrator.';
                                                                            const hasBootstrap = document.querySelector('link[href*="bootstrap"]') && window.bootstrap;
                                                                            let container = document.getElementById('toast-container');
                                                                            if (!container) {
                                                                                container = document.createElement('div');
                                                                                container.id = 'toast-container';
                                                                                document.body.appendChild(container);
                                                                            }
                                                                            if (hasBootstrap) {
                                                                                const toast = document.createElement('div');
                                                                                toast.className = 'toast';
                                                                                toast.setAttribute('role', 'alert');
                                                                                toast.setAttribute('aria-live', 'assertive');
                                                                                toast.setAttribute('aria-atomic', 'true');
                                                                                const body = document.createElement('div');
                                                                                body.className = 'toast-body';
                                                                                body.textContent = msg;
                                                                                toast.appendChild(body);
                                                                                container.appendChild(toast);
                                                                                bootstrap.Toast.getOrCreateInstance(toast).show();
                                                                            } else {
                                                                                alert(msg);
                                                                            }
                                                                            c.setAttribute('data-failed-route', 'true');
                                                                        } catch (err) {}
                                                                    });
                                                                } catch (error) {}
                                                            })();
                                                        </script>
                                                    @else
                                                        <div>{{__('No QR code available')}}</div>
                                                    @endif
                                                </div>
                                            </div>

                                        </div>
                                        <div class="{{ VC::RW }} {{ VC::MT3 }}">
                                            <div class="col">
                                                <small>
                                                    <strong>{{__('Status')}} :</strong><br>
                                                    @if(!empty($proposal->status) && is_numeric($proposal->status) && $proposal->status >= 0 && $proposal->status <= 4)
                                                        @if($proposal->status == 0)
                                                            <span class="{{ VC::BDG }} badge-pill bg-primary">{{ __(Proposal::$statuses[$proposal->status]) }}</span>
                                                        @elseif($proposal->status == 1)
                                                            <span class="{{ VC::BDG }} badge-pill bg-info">{{ __(Proposal::$statuses[$proposal->status]) }}</span>
                                                        @elseif($proposal->status == 2)
                                                            <span class="{{ VC::BDG }} badge-pill bg-success">{{ __(Proposal::$statuses[$proposal->status]) }}</span>
                                                        @elseif($proposal->status == 3)
                                                            <span class="{{ VC::BDG }} badge-pill bg-warning">{{ __(Proposal::$statuses[$proposal->status]) }}</span>
                                                        @elseif($proposal->status == 4)
                                                            <span class="{{ VC::BDG }} badge-pill bg-danger">{{ __(Proposal::$statuses[$proposal->status]) }}</span>
                                                        @endif
                                                    @else
                                                        <span class="{{ VC::BDG }} badge-pill bg-secondary">{{ __('Unknown status') }}</span>
                                                    @endif
                                                </small>
                                            </div>
                                            <div class="{{ VC::RW }}">
                                                <div class="col text-end">
                                                    <div class="{{ VC::DFL }} {{ VC::ALC }} justify-content-end">
                                                        <div class="me-4">
                                                            <small>
                                                                <strong>{{__('Issue Date')}} :</strong><br>
                                                                @php
                                                                    $issueDate = null;
                                                                    if (isset($proposal) && is_object($proposal) && !empty($proposal->issue_date)) {
                                                                        $issueDate = isset($user) && is_object($user) && method_exists($user, 'dateFormat') 
                                                                            ? $user->dateFormat($proposal->issue_date)
                                                                            : date('M d, Y', strtotime($proposal->issue_date));
                                                                    }
                                                                @endphp
                                                                @if(!empty($issueDate))
                                                                    {{ $issueDate }}<br><br>
                                                                @else
                                                                    <span class="text-muted">{{ __('Issue date not available') }}</span><br><br>
                                                                @endif
                                                            </small>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            @if(isset($customFields) && !empty($customFields) && isset($proposal) && is_object($proposal))
                                                @php
                                                    $proposalCustomFields = is_object($proposal) && property_exists($proposal, 'customField') ? $proposal->customField : [];
                                                    $hasCustomFields = !empty($proposalCustomFields) && (is_array($proposalCustomFields) || is_countable($proposalCustomFields));
                                                @endphp
                                                @if($hasCustomFields && count($proposalCustomFields) > 0)
                                                    @foreach($customFields as $field)
                                                        @if(isset($field) && is_object($field) && property_exists($field, 'name') && property_exists($field, 'id'))
                                                            <div class="col text-md-right">
                                                                <small>
                                                                    <strong>{{ $field->name }} :</strong><br>
                                                                    @php
                                                                        $fieldValue = null;
                                                                        if (is_array($proposalCustomFields)) {
                                                                            $fieldValue = $proposalCustomFields[$field->id] ?? __('Custom field not provided');
                                                                        } elseif (is_object($proposalCustomFields)) {
                                                                            $fieldValue = $proposalCustomFields->{$field->id} ?? __('Custom field not provided');
                                                                        }
                                                                    @endphp
                                                                    {{ !empty($fieldValue) && $fieldValue !== __('Custom field not provided') ? $fieldValue : __('Custom field not provided') }}
                                                                    <br><br>
                                                                </small>
                                                            </div>
                                                        @endif
                                                    @endforeach
                                                @endif
                                            @endif
                                            <div class="{{ VC::RW }} {{ VC::MT4 }}">
                                                <div class="col-md-12">
                                                    <div class="font-weight-bold">{{__('Product Summary')}}</div>
                                                    <small>{{__('All items here cannot be deleted.')}}</small>
                                                    <div class="table-responsive mt-2">
                                                        <table class="{{ VC::TB }} {{ VC::MB0 }} table-striped">
                                                            <tr>
                                                                <th class="text-dark" data-width="40">#</th>
                                                                <th class="text-dark">{{__('Product')}}</th>
                                                                <th class="text-dark">{{__('Quantity')}}</th>
                                                                <th class="text-dark">{{__('Rate')}}</th>
                                                                <th class="text-dark">{{__('Tax')}}</th>
                                                                <th class="text-dark">{{__('Discount')}}</th>
                                                                <th class="text-dark">{{__('Description')}}</th>
                                                                <th class="text-end text-dark" width="12%">{{__('Price')}}<br>
                                                                    <small class="text-danger font-weight-bold">{{__('after tax & discount')}}</small>
                                                                </th>
                                                            </tr>
                                                            @php
                                                                $totalQuantity = 0;
                                                                $totalRate = 0;
                                                                $totalTaxPrice = 0;
                                                                $totalDiscount = 0;
                                                                $taxesData = [];
                                                                $items = isset($iteams) && !empty($iteams) ? $iteams : [];
                                                            @endphp
                                                            @if(!empty($items))
                                                                @foreach($items as $key => $item)
                                                                    @if(isset($item) && is_object($item))
                                                                        @php
                                                                            $itemQuantity = property_exists($item, 'quantity') ? ($item->quantity ?? 0) : 0;
                                                                            $itemPrice = property_exists($item, 'price') ? ($item->price ?? 0) : 0;
                                                                            $itemDiscount = property_exists($item, 'discount') ? ($item->discount ?? 0) : 0;
                                                                            $itemTax = property_exists($item, 'tax') ? $item->tax : null;
                                                                            $itemDescription = property_exists($item, 'description') ? ($item->description ?? __('No description provided')) : __('No description provided');
                                                                            $itemProduct = property_exists($item, 'product') ? $item->product : null;
                                                                            $productName = (isset($itemProduct) && is_object($itemProduct) && property_exists($itemProduct, 'name')) 
                                                                                ? ($itemProduct->name ?? __('Product name not available')) 
                                                                                : __('Product name not available');
                                                                            
                                                                            $taxes = [];
                                                                            if (!empty($itemTax) && class_exists('Utility')) {
                                                                                try {
                                                                                    $taxes = Utility::tax($itemTax);
                                                                                    $totalQuantity += $itemQuantity;
                                                                                    $totalRate += $itemPrice;
                                                                                    $totalDiscount += $itemDiscount;
                                                                                    if (!empty($taxes)) {
                                                                                        foreach($taxes as $taxe) {
                                                                                            if (isset($taxe) && is_object($taxe) && property_exists($taxe, 'rate') && property_exists($taxe, 'name')) {
                                                                                                $taxDataPrice = Utility::taxRate($taxe->rate, $itemPrice, $itemQuantity);
                                                                                                if (array_key_exists($taxe->name, $taxesData)) {
                                                                                                    $taxesData[$taxe->name] += $taxDataPrice;
                                                                                                } else {
                                                                                                    $taxesData[$taxe->name] = $taxDataPrice;
                                                                                                }
                                                                                            }
                                                                                        }
                                                                                    }
                                                                                } catch (Exception $e) {
                                                                                    $taxes = [];
                                                                                }
                                                                            }
                                                                        @endphp
                                                                        <tr>
                                                                            <td>{{ $key + 1 }}</td>
                                                                            <td>{{ $productName }}</td>
                                                                            <td>{{ $itemQuantity }}</td>
                                                                            <td>
                                                                                @if(isset($user) && is_object($user) && method_exists($user, 'priceFormat'))
                                                                                    {{ $user->priceFormat($itemPrice) }}
                                                                                @else
                                                                                    {{ number_format($itemPrice, 2) }}
                                                                                @endif
                                                                            </td>
                                                                            <td>
                                                                                @if(!empty($taxes))
                                                                                    <table>
                                                                                        @php $totalTaxRate = 0; @endphp
                                                                                        @foreach($taxes as $tax)
                                                                                            @if(isset($tax) && is_object($tax) && property_exists($tax, 'name') && property_exists($tax, 'rate'))
                                                                                                @php
                                                                                                    $taxPrice = method_exists('Utility', 'taxRate') ? Utility::taxRate($tax->rate, $itemPrice, $itemQuantity) : 0;
                                                                                                    $totalTaxPrice += $taxPrice;
                                                                                                @endphp
                                                                                                <tr>
                                                                                                    <td>{{ $tax->name . ' (' . $tax->rate . '%)' }}</td>
                                                                                                    <td>
                                                                                                        @if(isset($user) && is_object($user) && method_exists($user, 'priceFormat'))
                                                                                                            {{ $user->priceFormat($taxPrice) }}
                                                                                                        @else
                                                                                                            {{ number_format($taxPrice, 2) }}
                                                                                                        @endif
                                                                                                    </td>
                                                                                                </tr>
                                                                                            @endif
                                                                                        @endforeach
                                                                                    </table>
                                                                                @else
                                                                                    <span class="text-muted">{{ __('No tax applied') }}</span>
                                                                                @endif
                                                                            </td>
                                                                            <td>
                                                                                @if(isset($user) && is_object($user) && method_exists($user, 'priceFormat'))
                                                                                    {{ $user->priceFormat($itemDiscount) }}
                                                                                @else
                                                                                    {{ number_format($itemDiscount, 2) }}
                                                                                @endif
                                                                            </td>
                                                                            <td>{{ $itemDescription }}</td>
                                                                            <td class="text-end">
                                                                                @php $lineTotal = $itemPrice * $itemQuantity; @endphp
                                                                                @if(isset($user) && is_object($user) && method_exists($user, 'priceFormat'))
                                                                                    {{ $user->priceFormat($lineTotal) }}
                                                                                @else
                                                                                    {{ number_format($lineTotal, 2) }}
                                                                                @endif
                                                                            </td>
                                                                        </tr>
                                                                    @endif
                                                                @endforeach
                                                            @else
                                                                <tr>
                                                                    <td colspan="8" class="text-center text-muted">{{ __('No items available') }}</td>
                                                                </tr>
                                                            @endif
                                                            <tfoot>
                                                                <tr>
                                                                    <td></td>
                                                                    <td><b>{{__('Total')}}</b></td>
                                                                    <td><b>{{ $totalQuantity }}</b></td>
                                                                    <td><b>
                                                                        @if(isset($user) && is_object($user) && method_exists($user, 'priceFormat'))
                                                                            {{ $user->priceFormat($totalRate) }}
                                                                        @else
                                                                            {{ number_format($totalRate, 2) }}
                                                                        @endif
                                                                    </b></td>
                                                                    <td><b>
                                                                        @if(isset($user) && is_object($user) && method_exists($user, 'priceFormat'))
                                                                            {{ $user->priceFormat($totalTaxPrice) }}
                                                                        @else
                                                                            {{ number_format($totalTaxPrice, 2) }}
                                                                        @endif
                                                                    </b></td>
                                                                    <td><b>
                                                                        @if(isset($user) && is_object($user) && method_exists($user, 'priceFormat'))
                                                                            {{ $user->priceFormat($totalDiscount) }}
                                                                        @else
                                                                            {{ number_format($totalDiscount, 2) }}
                                                                        @endif
                                                                    </b></td>
                                                                    <td></td>
                                                                    <td></td>
                                                                </tr>
                                                                @if(isset($proposal) && is_object($proposal))
                                                                    <tr>
                                                                        <td colspan="6"></td>
                                                                        <td class="text-end"><b>{{__('Sub Total')}}</b></td>
                                                                        <td class="text-end">
                                                                            @php
                                                                                $subTotal = method_exists($proposal, 'getSubTotal') ? $proposal->getSubTotal() : 0;
                                                                            @endphp
                                                                            @if(isset($user) && is_object($user) && method_exists($user, 'priceFormat'))
                                                                                {{ $user->priceFormat($subTotal) }}
                                                                            @else
                                                                                {{ number_format($subTotal, 2) }}
                                                                            @endif
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td colspan="6"></td>
                                                                        <td class="text-end"><b>{{__('Discount')}}</b></td>
                                                                        <td class="text-end">
                                                                            @php
                                                                                $totalDiscountAmount = method_exists($proposal, 'getTotalDiscount') ? $proposal->getTotalDiscount() : 0;
                                                                            @endphp
                                                                            @if(isset($user) && is_object($user) && method_exists($user, 'priceFormat'))
                                                                                {{ $user->priceFormat($totalDiscountAmount) }}
                                                                            @else
                                                                                {{ number_format($totalDiscountAmount, 2) }}
                                                                            @endif
                                                                        </td>
                                                                    </tr>
                                                                    @if(!empty($taxesData))
                                                                        @foreach($taxesData as $taxName => $taxPrice)
                                                                            <tr>
                                                                                <td colspan="6"></td>
                                                                                <td class="text-end"><b>{{ $taxName }}</b></td>
                                                                                <td class="text-end">
                                                                                    @if(isset($user) && is_object($user) && method_exists($user, 'priceFormat'))
                                                                                        {{ $user->priceFormat($taxPrice) }}
                                                                                    @else
                                                                                        {{ number_format($taxPrice, 2) }}
                                                                                    @endif
                                                                                </td>
                                                                            </tr>
                                                                        @endforeach
                                                                    @endif
                                                                    <tr>
                                                                        <td colspan="6"></td>
                                                                        <td class="blue-text text-end"><b>{{__('Total')}}</b></td>
                                                                        <td class="blue-text text-end">
                                                                            @php
                                                                                $grandTotal = method_exists($proposal, 'getTotal') ? $proposal->getTotal() : 0;
                                                                            @endphp
                                                                            @if(isset($user) && is_object($user) && method_exists($user, 'priceFormat'))
                                                                                {{ $user->priceFormat($grandTotal) }}
                                                                            @else
                                                                                {{ number_format($grandTotal, 2) }}
                                                                            @endif
                                                                        </td>
                                                                    </tr>
                                                                @endif
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
                </div>
            </div>
            <footer id="footer-main">
                <div class="footer-dark">
                    <div class="{{ VC::CT }}">
                        <div class="{{ VC::RW }} {{ VC::ALC }} justify-content-md-between py-4 {{ VC::MT4 }} delimiter-top">
                            <div class="col-md-6">
                                <div class="copyright text-sm font-weight-bold text-center text-md-left">
                                    {{!empty($companySettings[SC::FT_TXT]) ? $companySettings[SC::FT_TXT]->value : __('No footer text available')}}
                                </div>
                            </div>
                            <div class="col-md-6">
                                <ul class="{{ VC::NAV }} justify-content-center justify-content-md-end mt-3 mt-md-0">
                                    <li class="nav-item">
                                        <a class="{{ VC::NV_LK }}" href="#" target="_blank">
                                            <i class="fab fa-dribbble"></i>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="{{ VC::NV_LK }}" href="#" target="_blank">
                                            <i class="fab fa-instagram"></i>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="{{ VC::NV_LK }}" href="#" target="_blank">
                                            <i class="fab fa-github"></i>
                                        </a>
                                    </li>
                                    <li class="nav-item">
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
            <script async src="{{ asset('assets/js/plugins/apexcharts.min.js') }}"></script>
            <script defer src="{{ asset('assets/js/plugins/perfect-scrollbar.min.js') }}"></script>
            <script defer src="{{ asset('assets/js/plugins/feather.min.js') }}"></script>
            <script defer src="{{ asset('assets/js/plugins/bootstrap-switch-button.min.js') }}"></script>
            <script defer src="{{ asset('assets/js/plugins/sweetalert2.all.min.js') }}"></script>
            <script defer src="{{ asset('assets/js/plugins/simple-datatables.js') }}"></script>
            <script defer src="{{ asset('assets/js/plugins/choices.min.js') }}"></script>
            <script defer src="{{ asset('js/jscolor.js') }}"></script>
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
            <script defer>
                (() => {
                    try {
                        const l = document.getElementById('{{ $proposalPdfLinkId }}');
                        if (!l || l.getAttribute('data-listener-active') === 'true') return;
                        l.setAttribute('data-listener-active', 'true');
                        l.addEventListener('click', e => {
                            try {
                                const href = l.getAttribute('href') || '#';
                                const url = l.getAttribute('data-url') || href || '#';
                                if (href !== '#' || url !== '#') return;
                                e.preventDefault();
                                const msg = l.getAttribute('data-guard-msg') || 'Download proposal pdf route is unavailable. Please contact technical support or your domain administrator.';
                                const hasBootstrap = document.querySelector('link[href*="bootstrap"]') && window.bootstrap;
                                let container = document.getElementById('toast-container');
                                if (!container) {
                                    container = document.createElement('div');
                                    container.id = 'toast-container';
                                    document.body.appendChild(container);
                                }
                                if (hasBootstrap) {
                                    const toast = document.createElement('div');
                                    toast.className = 'toast';
                                    toast.setAttribute('role', 'alert');
                                    toast.setAttribute('aria-live', 'assertive');
                                    toast.setAttribute('aria-atomic', 'true');
                                    const body = document.createElement('div');
                                    body.className = 'toast-body';
                                    body.textContent = msg;
                                    toast.appendChild(body);
                                    container.appendChild(toast);
                                    bootstrap.Toast.getOrCreateInstance(toast).show();
                                } else {
                                    alert(msg);
                                }
                                l.setAttribute('data-failed-route', 'true');
                            } catch (err) {}
                        });
                    } catch (error) {}
                })();
            </script>

