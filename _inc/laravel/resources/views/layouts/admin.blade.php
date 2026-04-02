@php
	$data??=[];
	$setting??=[];
	$colorSettings??=[];
	$logo??='';
	$company_favicon??='';
	$color??='';
	$siteRtl??=false;
	$lang = str_replace('_','-',app()->getLocale()) ?: Utility::fetchUserLang();
	$meta_title??='';
	$meta_desc??='';
	$meta_image??='';
	$meta_logo??='';
	$faviconUrl??='';
	$filePath??='';
	try {
		$data=Utility::prepareCommonViewData()?:[];
		$setting=$data[SC::ENTITY]??[];
		$colorSettings=$data[SC::CLR_STG]??[];
		$logo=$data[SC::LOGO]??'';
		$company_favicon=$data[SC::FAV_ICN]??'';
		$color=$data[SC::THM_CLR]??'';
		$siteRtl=$data[SC::RTL]??false;
		$meta_title=$data[SC::MT_TTL_K]??'';
		$meta_desc=$data[SC::MT_DESC_LONG]??'';
		$meta_image=$data[SC::MT_IMG_K]??'';
		$meta_logo=$data[SC::MT_LOGO]??'';
		$faviconUrl=Utility::getCompanyLogo()?:'';
		$filePath=collect(
			array_column(
				debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS),
				'file'
			)
		)->first(fn($p)=>str_ends_with($p,'.blade.php'))??'';
	} catch (\Error $e) {
		Log::error(
			'Error fetching common view data',
			[
				'exception_class'=>get_class($e),
				'message'=>$e->getMessage(),
				'file'=>$e->getFile(),
				'line'=>$e->getLine()
			]
		);
	} catch (\Exception $e) {
		Log::error(
			'Exception fetching common view data',
			[
				'exception_class'=>get_class($e),
				'message'=>$e->getMessage(),
				'file'=>$e->getFile(),
				'line'=>$e->getLine()
			]
		);
	} catch (\Throwable $e) {
		Log::error(
			'Throwable fetching common view data',
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
<html lang="{{ $lang ?? str_replace('_', '-', is_string(app()->getLocale()) ? app()->getLocale() : DatabaseConstants::DEFAULT_LANG) }}" dir="{{$siteRtl == 'on' ? 'rtl' : '' }}">
    <meta name="csrf-token" id="csrf-token" content="{{ csrf_token() }}">
    <head>
        <title>{{($setting['title_text']) ? $setting['title_text'] : config('app.name', 'ERPNovaPrestech')}}
            - @yield(YieldingConstants::ADM_PG_TTL)</title>
        @include('fragments.std', [
            'meta_title' => $meta_title,
            'meta_desc' => $meta_desc,
            'meta_vp' => 'shrink-to-fit=no',
            'meta_url' => url('').'/'.config('chatify.path'),
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
        <script src="{{ asset('js/html5shiv.js') }}"></script>
        {{--    <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>--}}
        <!-- Meta -->
        @include('fragments.favicon', ['faviconUrl' => $faviconUrl])
        {{--    <link rel="icon" href="{{ asset('assets/images/favicon.svg') }}" type="image/x-icon"/>--}}
        @include('fragments.stylesheets', ['settings' => $colorSettings])
        <link rel="stylesheet" href="{{ asset('assets/css/plugins/flatpickr.min.css') }}">
        <link rel="stylesheet" href="{{ asset('assets/css/plugins/bootstrap-switch-button.min.css') }}">
        @if ($siteRtl == 'on')
            <link rel="stylesheet" href="{{ asset('assets/css/style-rtl.css') }}">
        @endif
        @stack(StacksConstants::ADM_CSS)
        {{-- ERP Guard & Utils Initialization (Blocking) --}}
        <script>
            window.ERPGuard = window.ERPGuard || null;
            window.ERPUtils = window.ERPUtils || null;
        </script>
        {{-- Transparent base64 decode for data-guard-msg attributes --}}
        <script>
            (function(){var o=Element.prototype.getAttribute;Element.prototype.getAttribute=function(n){var v=o.call(this,n);if(n==='data-guard-msg'&&v){try{return decodeURIComponent(atob(v))}catch(e){try{return atob(v)}catch(e2){return v}}}return v}})();
        </script>
    </head>
    <body class="{{ $color }}">
        <div class="loader-bg">
            <div class="loader-track">
                <div class="loader-fill"></div>
            </div>
        </div>
        @include('partials.admin.menu')
        @include('partials.admin.header')
        <div class="{{ ViewClassNamesConstants::MD_FD }} notification-modal"
            id="notification-modal"
            tabindex="-1"
            role="dialog"
            aria-hidden="true"
        >
            <div class="{{ VC::MDL_DLG }}" role="document">
                <div class="{{ VC::MDL_CTT }}">
                    <div class="modal-body">
                        <button
                            type="button"
                            class="{{ VC::BT_CL }} {{ VC::FEND }}"
                            data-bs-dismiss="modal"
                            aria-label="Close"
                        ></button>
                        <h6 class="{{ VC::MT2 }}">
                            <i data-feather="monitor" class="me-2"></i>{{ __('Desktop settings') }}
                        </h6>
                        <hr/>
                        <div class="{{ VC::FM_CHK }} form-switch">
                            <input
                                type="checkbox"
                                class="form-check-input"
                                id="pcsetting1"
                                checked
                            />
                            <label class="form-check-label {{ VC::FW600 }} pl-1" for="pcsetting1"
                            >{{ __('Allow desktop notification') }}</label
                            >
                        </div>
                        <p class="{{ VC::TXT_MT }} ms-5">
                            {{ __('You get the latest content when data is updated') }}
                        </p>
                        <div class="{{ VC::FM_CHK }} form-switch">
                            <input type="checkbox" class="form-check-input" id="pcsetting2"/>
                            <label class="form-check-label {{ VC::FW600 }} pl-1" for="pcsetting2"
                            >{{ __('Store Cookie') }}</label
                            >
                        </div>
                        <h6 class="{{ VC::MB0 }} mt-5">
                            <i data-feather="save" class="me-2"></i>{{ __('Application settings') }}
                        </h6>
                        <hr/>
                        <div class="{{ VC::FM_CHK }} form-switch">
                            <input type="checkbox" class="form-check-input" id="pcsetting3"/>
                            <label class="form-check-label {{ VC::FW600 }} pl-1" for="pcsetting3"
                            >{{ __('Backup Storage') }}</label
                            >
                        </div>
                        <p class="{{ VC::TXT_MT }} {{ VC::MB4 }} ms-5">
                            {{ __('Automatically take backup as per schedule') }}
                        </p>
                        <div class="{{ VC::FM_CHK }} form-switch">
                            <input type="checkbox" class="form-check-input" id="pcsetting4"/>
                            <label class="form-check-label {{ VC::FW600 }} pl-1" for="pcsetting4"
                            >{{ __('Allow guest to print file') }}</label
                            >
                        </div>
                        <h6 class="{{ VC::MB0 }} mt-5">
                            <i data-feather="cpu" class="me-2"></i>{{ __('System settings') }}
                        </h6>
                        <hr/>
                        <div class="{{ VC::FM_CHK }} form-switch">
                            <input
                                type="checkbox"
                                class="form-check-input"
                                id="pcsetting5"
                                checked
                            />
                            <label class="form-check-label {{ VC::FW600 }} pl-1" for="pcsetting5"
                            >{{ __('View other user chat') }}</label
                            >
                        </div>
                        <p class="{{ VC::TXT_MT }} ms-5">{{ __('Allow to show public user message') }}</p>
                    </div>
                    <div class="modal-footer">
                        <button
                            type="button"
                            class="btn btn-light-danger btn-sm"
                            data-bs-dismiss="modal"
                        >
                            {{ __('Close') }}
                        </button>
                        <button type="button" class="btn btn-light-primary btn-sm">
                            {{ __('Save changes') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="dash-container">
            <div class="{{ VC::DSH_CTT }}">
                <div class="{{ VC::PG_HDR }}">
                    <div class="{{ VC::PG_BLK }}">
                        <div class="{{ VC::R_ALC }}">
                            <div class="{{ VC::C_AT }}">
                                <div class="page-header-title">
                                    <h4 class="m-b-10">@yield(YieldingConstants::ADM_PG_TTL)</h4>
                                </div>
                                <ul class="breadcrumb">
                                    @yield(YieldingConstants::ADM_BDC)
                                </ul>
                            </div>
                            <div class="col">
                                @yield(YieldingConstants::ADM_ACT_BTN)
                            </div>
                        </div>
                    </div>
                </div>
            @yield(YieldingConstants::ADM_CTT)
            <!-- [ Main Content ] end -->
            </div>
        </div>
        <div class="{{ ViewClassNamesConstants::MD_FD }}" id="commonModal" tabindex="-1" role="dialog"
            aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="{{ VC::MDL_DLG }}" role="document">
                <div class="{{ VC::MDL_CTT }}">
                    <div class="{{ VC::MDL_HDR }}">
                        <h5 class="{{ VC::MDL_TTL }}" id="exampleModalLabel"></h5>
                        <button type="button" class="{{ VC::BT_CL }}" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="body">
                    </div>
                </div>
            </div>
        </div>
        <div class="{{ ViewClassNamesConstants::MD_FD }}" id="commonModalOver" tabindex="-1" role="dialog" aria-labelledby="commonModalLabel" aria-hidden="true">
            <div class="{{ VC::MDL_DLG }}" role="document">
                <div class="{{ VC::MDL_CTT }}">
                    <div class="{{ VC::MDL_HDR }}">
                        <h5 class="{{ VC::MDL_TTL }}" id="commonModalLabel"></h5>
                        <button type="button" class="{{ VC::BT_CL }}" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                    </div>
                </div>
            </div>
        </div>
        <div class="position-fixed top-0 end-0 p-3" style="z-index: 99999">
            <div id="liveToast" class="toast {{ VC::TXT_WT }} fade" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="{{ VC::DFL }}">
                    <div class="toast-body"></div>
                    <button type="button" class="{{ VC::BT_CL }} btn-close-white me-2 m-auto" data-bs-dismiss="toast"
                            aria-label="Close"></button>
                </div>
            </div>
        </div>
        @include('partials.admin.footer')
        @include('Chatify::layouts.footer_links')
        {{-- ERP Guard & Utils Core Classes (Deferred) --}}
        <script src="{{ asset('assets/js/core/erp-guard.js') }}" defer></script>
        <script src="{{ asset('assets/js/core/erp-utils.js') }}" defer></script>
        <script src="{{ asset('assets/js/core/erp-bootstrap.min.js') }}" defer></script>
        <script src="{{ asset('assets/js/core/modal-autoopen.js') }}" defer></script>
        <script src="{{ asset('assets/js/core/form-submit-delegate.js') }}" defer></script>
        <script src="{{ asset('assets/js/core/img-fallback-delegate.js') }}" defer></script>
        <script async src="{{ asset('assets/js/routes/generics/lang/utility.js') }}"></script>
        <script defer src="{{ asset('assets/js/routes/generics/utility.js') }}"></script>
        <script>
            (window.location.hostname === '127.0.0.1' || window.location.hostname === 'localhost') && console.log(
                'Current route:',
                '{{ Illuminate\Support\Facades\Route::currentRouteName() ?? Illuminate\Support\Facades\Route::currentRouteAction() }}'
            );
            window.sessionStorage.setItem('logPusher', 'false');
            window.sessionStorage.setItem('warnPusher', 'true');
            window.sessionStorage.setItem('errorPusher', 'true');
        </script>
        @include('partials.global-error-handler')
    </body>
</html>
