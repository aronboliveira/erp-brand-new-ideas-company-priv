@php
	use App\Config\Constants\{
		DatabaseConstants,
		ExtendingLayoutsConstants,
		SettingsConstants,
		ViewClassNamesConstants as VC,
		ViewsConstants
	};
	use App\Models\{Bill,Utility};
	use Illuminate\Support\Facades\{Auth,Cookie,Crypt,Log,Route};
	$data ??= [];
	$colorSettings ??= [];
	$setting ??= [];
	$company_logo ??= '';
	$company_logos ??= '';
	$company_favicon ??= '';
	$logo ??= '';
	$color ??= '';
	$siteRtl ??= false;
	$currentLang ??= '';
	$cust_theme_bg ??= '';
	$cust_darklayout ??= '';
	$meta_title ??= '';
	$meta_desc ??= '';
	$meta_image ??= '';
	$meta_logo ??= '';
	$get_cookie ??= '';
	$faviconUrl ??= '';
    $user = Auth::user();
    $lang = Utility::fetchUserLang(user:$user);
	try {
		$data = Utility::prepareCommonViewData() ?: [];
		$colorSettings = $data[SettingsConstants::CLR_STG] ?? [];
		$setting = $data[SettingsConstants::ENTITY] ?? [];
		$company_logo = $setting[SettingsConstants::CPN_LG_DK] ?? '';
		$company_logos = $setting[SettingsConstants::CPN_LG_LT] ?? '';
		$company_favicon = $data[SettingsConstants::FAV_ICN] ?? '';
		$logo = $data[SettingsConstants::LOGO] ?? '';
		$color = $data[SettingsConstants::THM_CLR] ?? '';
		$siteRtl = $data[SettingsConstants::RTL] ?? false;
		$currentLang = Cookie::get('LANGUAGE') ?: DatabaseConstants::DEFAULT_LANG;
		$cust_theme_bg = Cookie::get(SettingsConstants::CST_BG) ?: 'on';
		$cust_darklayout = Cookie::get(SettingsConstants::CST_DRK) ?: 'off';
		$meta_title = $data[SettingsConstants::MT_TTL_K] ?? '';
		$meta_desc = $data[SettingsConstants::MT_DESC_LONG] ?? '';
		$meta_image = $data[SettingsConstants::MT_IMG_K] ?? '';
		$meta_logo = $data[SettingsConstants::MT_LOGO] ?? '';
		$get_cookie = $data[SettingsConstants::CK_STG] ?? '';
		$faviconUrl = Utility::getCompanyLogo() ?: '';
	} catch (\Error $e) {
		Log::error(
			'Error loading layout configuration data',
			[
				'exception_class' => get_class($e),
				'message' => $e->getMessage(),
				'file' => $e->getFile(),
				'line' => $e->getLine()
			]
		);
	} catch (\Exception $e) {
		Log::error(
			'Exception loading layout configuration data',
			[
				'exception_class' => get_class($e),
				'message' => $e->getMessage(),
				'file' => $e->getFile(),
				'line' => $e->getLine()
			]
		);
	} catch (\Throwable $e) {
		Log::error(
			'Throwable loading layout configuration data',
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
<html lang="{{ $lang ?? str_replace('_', '-', is_string(app()->getLocale()) ? app()->getLocale() : DatabaseConstants::DEFAULT_LANG) }}" dir="{{ $siteRtl === 'on' ? 'rtl' : 'ltr' }}">
    <head>
        <title>{{__('ERPNovaPrestech')}}</title>
        @include('fragments.std', [
            'meta_title' => $meta_title,
            'meta_desc' => $meta_desc,
            'meta_vp' => ''
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
        <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
        <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
        {{--    <meta name="url" content="{{ url('').'/'.config('chatify.path') }}" data-user="{{ Auth::user()->id }}">--}}
        @include('fragments.favicon', ['faviconUrl' => $faviconUrl])
        {{--    <link rel="icon" href="{{ asset('assets/images/favicon.svg') }}" type="image/x-icon"/>--}}
        @stack('css-page')
        @include('fragments.stylesheets', ['settings' => $colorSettings])
        <link rel="stylesheet" href="{{ asset('assets/css/plugins/flatpickr.min.css') }}">
        <link rel="stylesheet" href="{{ asset('assets/css/plugins/bootstrap-switch-button.min.css') }}">
        @if ($siteRtl == 'on')
            <link rel="stylesheet" href="{{ asset('assets/css/style-rtl.css') }}">
        @endif
        @stack('css-page')
    </head>
    <body class="{{ $color }}">
        <header class="header header-transparent" id="header-main">
        </header>
        @php
            $pid = data_get($purchase,'id');
            $pstatus = (int)(data_get($purchase,'status') ?? -1);
            $fmtDate = function($v,$fb) use($user){ return ($v && $user && method_exists($user,'dateFormat')) ? ($user->dateFormat($v) ?? $fb) : $fb; };
            $priceFmt = function($v) use($user){ return $user?->priceFormat($v) ?? number_format((float)$v,2); };
            $pnum = $user?->purchaseNumberFormat(data_get($purchase,'purchase_id')) ?? __('Could not find purchase number');
            $vatOn = (data_get($settings,'vat_gst_number_switch') === 'on');
            $bName = data_get($vendor,'billing_name'); $bPhone = data_get($vendor,'billing_phone'); $bAddr = data_get($vendor,'billing_address'); $bZip = data_get($vendor,'billing_zip'); $bCity = data_get($vendor,'billing_city'); $bState = data_get($vendor,'billing_state'); $bCountry = data_get($vendor,'billing_country'); $bTax = data_get($vendor,'tax_number');
            $sName = data_get($vendor,'shipping_name'); $sPhone = data_get($vendor,'shipping_phone'); $sAddr = data_get($vendor,'shipping_address'); $sZip = data_get($vendor,'shipping_zip'); $sCity = data_get($vendor,'shipping_city'); $sState = data_get($vendor,'shipping_state'); $sCountry = data_get($vendor,'shipping_country');
            $items = is_iterable($iteams ?? null) ? $iteams : [];
        @endphp
        <div class="{{ VC::MCT_CT }}">
            <div class="{{ VC::RW }} {{ VC::JCB }} {{ VC::ALC }} {{ VC::MB3 }}">
                <div class="{{ VC::CM12 }} {{ VC::DFL }} {{ VC::ALC }} {{ VC::JCB }} justify-content-md-end">
                    @php
                        $pidVal             = isset($pid) ? $pid : null;
                        $encId              = $pidVal ? Crypt::encrypt($pidVal) : null;
                        $pdfBase            = ViewsConstants::PRC.'.pdf';
                        $pdfKebab           = Str::kebab($pdfBase);
                        $pdfResolved        = Route::has($pdfBase) ? $pdfBase : (Route::has($pdfKebab) ? $pdfKebab : null);
                        $pdfParams          = $encId ? [$encId] : ['#'];
                        $purchasePdfUrl     = ($pdfResolved && $encId) ? route($pdfResolved, $pdfParams) : '#';
                        $purchasePdfGuardMsg= Utility::fetchLinkMessage($lang, ViewsConstants::PRC, 'pdf_purchase_route_unavailable') ?? 'PDF purchase route is unavailable. Please contact technical support or your domain administrator.';
                    @endphp
                    <div class="all-button-box mx-2">
                        <a href="{{ $purchasePdfUrl }}"
                        target="_blank"
                        class="{{ VC::BT_PRM }} {{ VC::MT3 }} purchase-pdf"
                        data-url="{{ $purchasePdfUrl }}"
                        data-guard-msg="{{ $purchasePdfGuardMsg }}"
                        data-sv-localized="true">{{ __('Download') }}</a>
                    </div>
                    @push(StacksConstants::ADM_SCRP_PG)
                        <script src="{{ asset('assets/js/routes/purchases/pdf.js') }}" defer></script>
                    @endpush
                </div>
            </div>
            <div class="{{ VC::RW }}">
                <div class="{{ VC::C12 }}">
                    <div class="{{ VC::CD }}">
                        <div class="card-body">
                            <div class="invoice">
                                <div class="invoice-print">
                                    <div class="{{ VC::RW }} invoice-title mt-2">
                                        <div class="col-xs-12 {{ VC::CS12 }} col-nd-6 {{ VC::CL6 }} {{ VC::C12 }}"><h2>{{ __('Purchase') }}</h2></div>
                                        <div class="col-xs-12 {{ VC::CS12 }} col-nd-6 {{ VC::CL6 }} {{ VC::C12 }} text-end"><h3 class="invoice-number float-right">{{ $pnum }}</h3></div>
                                        <div class="{{ VC::C12 }}"><hr></div>
                                    </div>
                                    <div class="{{ VC::RW }}">
                                        <div class="col text-end">
                                            <div class="{{ VC::DFL }} {{ VC::ALC }} {{ VC::JCE }}">
                                                <div class="me-4">
                                                    <small><strong>{{ __('Purchase Date') }} :</strong><br>{{ $fmtDate(data_get($purchase,'purchase_date'), __('No purchase date available')) }}<br><br></small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="{{ VC::RW }}">
                                        @if(!empty($bName))
                                            <div class="col">
                                                <small class="font-style">
                                                    <strong>{{ __('Billed To') }} :</strong><br>
                                                    {{ $bName }}<br>
                                                    {{ $bPhone ?? '' }}<br>
                                                    {{ $bAddr ?? __('No billing address available') }}<br>
                                                    {{ $bZip ?? __('No billing zip available') }}<br>
                                                    {{ $bCity ?? __('No billing city available') }}, {{ $bState ?? __('No billing state available') }}, {{ $bCountry ?? __('No billing country available') }}
                                                </small>
                                            </div>
                                        @endif
                                        @if(\Utility::getValByName('shipping_display')=='on')
                                            <div class="col">
                                                <small>
                                                    <strong>{{ __('Shipped To') }} :</strong><br>
                                                    {{ $sName ?? __('No shipping name available') }}<br>
                                                    {{ $sPhone ?? '' }}<br>
                                                    {{ $sAddr ?? __('No shipping address available') }}<br>
                                                    {{ $sZip ?? __('No shipping zip available') }}<br>
                                                    {{ $sCity ?? __('No shipping city available') }}, {{ $sState ?? __('No shipping state available') }}, {{ $sCountry ?? __('No shipping country available') }}
                                                </small>
                                            </div>
                                        @endif
                                        <div class="col">
                                            <div class="{{ VC::FEND }} {{ VC::MT3 }}">
                                                @php
                                                    $pidVal              = isset($pid) ? $pid : null;
                                                    $encId               = $pidVal ? Crypt::encrypt($pidVal) : null;
                                                    $copyLinkBase        = ViewsConstants::BIL.'.link.copy';
                                                    $copyLinkKebab       = Str::kebab($copyLinkBase);
                                                    $copyLinkResolved    = Route::has($copyLinkBase) ? $copyLinkBase : (Route::has($copyLinkKebab) ? $copyLinkKebab : null);
                                                    $copyLinkParams      = $encId ? [$encId] : ['#'];
                                                    $billCopyLinkUrl     = ($copyLinkResolved && $encId) ? route($copyLinkResolved, $copyLinkParams) : '#';
                                                    $billCopyLinkMsg     = Utility::fetchLinkMessage($lang, ViewsConstants::BIL, 'copy_link_route_unavailable') ?? 'Copy link for bill route is unavailable. Please contact technical support or your domain administrator.';
                                                    $qrElId              = 'bill-copy-link-qrcode';
                                                @endphp
                                                <div id="{{ $qrElId }}" data-url="{{ $billCopyLinkUrl }}" data-guard-msg="{{ $billCopyLinkMsg }}" data-sv-localized="true">
                                                    {!! DNS2D::getBarcodeHTML($billCopyLinkUrl, 'QRCODE', 2, 2) !!}
                                                </div>
                                                @push(StacksConstants::ADM_SCRP_PG)
                                                    <script>
                                                        (() => {
                                                            try {
                                                                const el = document.getElementById('{{ $qrElId }}');
                                                                if (!el) return;
                                                                const flag = 'data-route-check-listener';
                                                                if (el.hasAttribute(flag) && el.getAttribute(flag) === 'true') return;
                                                                el.setAttribute(flag, 'true');
                                                                const url = el.getAttribute('data-url') || '#';
                                                                if (url !== '#') return;
                                                                const msg = el.getAttribute('data-guard-msg') || 'Copy link for bill route is unavailable. Please contact technical support or your domain administrator.';
                                                                const linkEl = document.querySelector('link[href*="bootstrap"]');
                                                                const hasBootstrapToast = (typeof window !== 'undefined' && window.bootstrap && typeof window.bootstrap.Toast === 'function');
                                                                let container = document.getElementById('toast-container');
                                                                if (!container) {
                                                                    container = document.createElement('div');
                                                                    container.id = 'toast-container';
                                                                    container.className = 'position-fixed top-0 end-0 p-3';
                                                                    document.body.appendChild(container);
                                                                }
                                                                if (linkEl && hasBootstrapToast) {
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
                                                                    const inst = window.bootstrap.Toast.getOrCreateInstance(toast);
                                                                    toast.addEventListener('hidden.bs.toast', function() { try { toast.remove(); } catch (_) {} });
                                                                    inst.show();
                                                                } else {
                                                                    alert(msg);
                                                                }
                                                                el.setAttribute('data-failed-route', 'true');
                                                            } catch (_) {}
                                                        })();
                                                    </script>
                                                @endpush
                                            </div>
                                        </div>
                                    </div>
                                    <div class="{{ VC::RW }} {{ VC::MT3 }}">
                                        <div class="col">
                                            @php $badgeMap=[0=>'bg-primary',1=>'bg-warning',2=>'bg-danger',3=>'bg-info',4=>'bg-success']; $badge=$badgeMap[$pstatus] ?? 'bg-secondary'; $stText=__(Bill::$statuses[$pstatus] ?? __('Unknown status')); @endphp
                                            <small><strong>{{ __('Status') }} :</strong><br><span class="{{ VC::BDG }} {{ $badge }}">{{ $stText }}</span></small>
                                        </div>
                                    </div>
                                    <div class="{{ VC::RW }} {{ VC::MT4 }}">
                                        <div class="{{ VC::CM12 }}">
                                            <div class="font-weight-bold">{{ __('Product Summary') }}</div>
                                            <small>{{ __('All items here cannot be deleted.') }}</small>
                                            <div class="table-responsive mt-2">
                                                <table class="{{ VC::TB }} table-striped">
                                                    @php $totalQuantity=0; $totalRate=0; $totalTaxPrice=0; $totalDiscount=0; $taxesData=[]; @endphp
                                                    <thead>
                                                        <tr>
                                                            <th class="text-dark" data-width="40">#</th>
                                                            <th class="text-dark">{{ __('Product') }}</th>
                                                            <th class="text-dark">{{ __('Quantity') }}</th>
                                                            <th class="text-dark">{{ __('Rate') }}</th>
                                                            <th class="text-dark">{{ __('Tax') }}</th>
                                                            <th class="text-dark">{{ __('Discount') }}</th>
                                                            <th class="text-dark">{{ __('Description') }}</th>
                                                            <th class="text-end text-dark" width="12%">{{ __('Price') }}<br><small class="text-danger">{{ __('after tax & discount') }}</small></th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($items as $key => $it)
                                                            @php
                                                                $qty=(float)(data_get($it,'quantity') ?? 0); $rate=(float)(data_get($it,'price') ?? 0); $disc=(float)(data_get($it,'discount') ?? 0);
                                                                $totalQuantity+=$qty; $totalRate+=$rate; $totalDiscount+=$disc;
                                                                $taxes=!empty(data_get($it,'tax')) ? \Utility::tax(data_get($it,'tax')) : [];
                                                            @endphp
                                                            <tr>
                                                                <td>{{ $key+1 }}</td>
                                                                <td>{{ data_get($it,'product.name') ?? __('No product name available') }}</td>
                                                                <td>{{ $qty }}</td>
                                                                <td>{{ $priceFmt($rate) }}</td>
                                                                <td>
                                                                    @if(!empty($taxes))
                                                                        <table>
                                                                            @php $rowTax=0; @endphp
                                                                            @foreach($taxes as $tax)
                                                                                @php $tRate=(float)($tax->rate ?? 0); $tName=$tax->name ?? __('Tax'); $tPrice=\Utility::taxRate($tRate,$rate,$qty,$disc); $rowTax+=$tPrice; $totalTaxPrice+=$tPrice; $taxesData[$tName]=($taxesData[$tName] ?? 0)+$tPrice; @endphp
                                                                                <tr><td>{{ $tName.' ('.$tRate.'%)' }}</td><td>{{ $priceFmt($tPrice) }}</td></tr>
                                                                            @endforeach
                                                                        </table>
                                                                    @else
                                                                        -
                                                                    @endif
                                                                </td>
                                                                <td>{{ $priceFmt($disc) }}</td>
                                                                <td>{{ data_get($it,'description') ?? __('No description available.') }}</td>
                                                                @php $lineTotal = ($rate*$qty - $disc) + ($rowTax ?? 0); @endphp
                                                                <td class="text-end">{{ $priceFmt($lineTotal) }}</td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                    <tfoot>
                                                        <tr>
                                                            <td></td>
                                                            <td><b>{{ __('Total') }}</b></td>
                                                            <td><b>{{ $totalQuantity }}</b></td>
                                                            <td><b>{{ $priceFmt($totalRate) }}</b></td>
                                                            <td><b>{{ $priceFmt($totalTaxPrice) }}</b></td>
                                                            <td><b>{{ $priceFmt($totalDiscount) }}</b></td>
                                                            <td></td>
                                                            <td></td>
                                                        </tr>
                                                        @php
                                                            $subTotal = method_exists($purchase,'getSubTotal') ? $purchase->getSubTotal() : 0;
                                                            $totalDiscountVal = method_exists($purchase,'getTotalDiscount') ? $purchase->getTotalDiscount() : 0;
                                                            $grandTotal = method_exists($purchase,'getTotal') ? $purchase->getTotal() : 0;
                                                            $due = method_exists($purchase,'getDue') ? $purchase->getDue() : 0;
                                                            $paid = $grandTotal - $due;
                                                        @endphp
                                                        <tr>
                                                            <td colspan="6"></td>
                                                            <td class="text-end"><b>{{ __('Sub Total') }}</b></td>
                                                            <td class="text-end">{{ $priceFmt($subTotal) }}</td>
                                                        </tr>
                                                        <tr>
                                                            <td colspan="6"></td>
                                                            <td class="text-end"><b>{{ __('Discount') }}</b></td>
                                                            <td class="text-end">{{ $priceFmt($totalDiscountVal) }}</td>
                                                        </tr>
                                                        @if(!empty($taxesData))
                                                            @foreach($taxesData as $tName => $tVal)
                                                                <tr>
                                                                    <td colspan="6"></td>
                                                                    <td class="text-end"><b>{{ $tName }}</b></td>
                                                                    <td class="text-end">{{ $priceFmt($tVal) }}</td>
                                                                </tr>
                                                            @endforeach
                                                        @endif
                                                        <tr>
                                                            <td colspan="6"></td>
                                                            <td class="blue-text text-end"><b>{{ __('Total') }}</b></td>
                                                            <td class="blue-text text-end">{{ $priceFmt($grandTotal) }}</td>
                                                        </tr>
                                                        <tr>
                                                            <td colspan="6"></td>
                                                            <td class="text-end"><b>{{ __('Paid') }}</b></td>
                                                            <td class="text-end">{{ $priceFmt($paid) }}</td>
                                                        </tr>
                                                        <tr>
                                                            <td colspan="6"></td>
                                                            <td class="text-end"><b>{{ __('Due') }}</b></td>
                                                            <td class="text-end">{{ $priceFmt($due) }}</td>
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
            </div>
        </div>
        <footer id="footer-main">
            <div class="footer-dark">
                <div class="{{ VC::CT }}">
                    <div class="{{ VC::RW }} {{ VC::ALC }} justify-content-md-between py-4 {{ VC::MT4 }} delimiter-top">
                        <div class="col-md-6">
                            <div class="copyright text-sm font-weight-bold text-center text-md-left">{{ data_get($companySettings, SettingsConstants::FT_TXT.'.value') ?? __('No footer text available') }}</div>
                        </div>
                        <div class="col-md-6">
                            <ul class="{{ VC::NAV }} justify-content-center justify-content-md-end mt-3 mt-md-0">
                                <li class="{{ VC::NV_IT }}"><a class="{{ VC::NV_LK }}" href="#" target="_blank"><i class="fab fa-dribbble"></i></a></li>
                                <li class="{{ VC::NV_IT }}"><a class="{{ VC::NV_LK }}" href="#" target="_blank"><i class="fab fa-instagram"></i></a></li>
                                <li class="{{ VC::NV_IT }}"><a class="{{ VC::NV_LK }}" href="#" target="_blank"><i class="fab fa-github"></i></a></li>
                                <li class="{{ VC::NV_IT }}"><a class="{{ VC::NV_LK }}" href="#" target="_blank"><i class="fab fa-facebook"></i></a></li>
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
