<!DOCTYPE html>
@php
$uri??='';
	$backtrace??=[];
	$compiledPath??='';
	$filePath??='';
	$data??=[];
	$setting??=[];
	$colorSettings??=[];
	$company_logo_dk??='';
	$company_logo_lt??='';
	$company_favicon??='';
	$logo??='';
	$color??='';
	$siteRtl??='';
	$lang = Utility::fetchUserLang();
	$meta_title??='';
	$meta_desc??='';
	$meta_image??='';
	$meta_logo??='';
	$get_cookie??='';
	$faviconUrl??='';
	try {
		$backtrace=debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
		$compiledPath=collect(
			array_column($backtrace,'file')
		)->first(fn($f)=>is_string($f)
			&&str_contains($f,storage_path('framework/views'))
		)??'';
		if($compiledPath&&file_exists($compiledPath)){
			$contents=(string)(file_get_contents($compiledPath)?:'');
			$filePath=($contents && preg_match(
				'/\*\*PATH\s+(.+\.blade\.php)\s+ENDPATH\*\*/',
				$contents,$m
			))?$m[1]:'unknown.blade.php';
		}else$filePath='unknown.blade.php';
	} catch (\Error $e) {
		Log::error(
			'Error resolving blade file path',
			[
				'exception_class'=>get_class($e),
				'message'=>$e->getMessage(),
				'file'=>$e->getFile(),
				'line'=>$e->getLine()
			]
		);
		$filePath='unknown.blade.php';
	} catch (\Exception $e) {
		Log::error(
			'Exception resolving blade file path',
			[
				'exception_class'=>get_class($e),
				'message'=>$e->getMessage(),
				'file'=>$e->getFile(),
				'line'=>$e->getLine()
			]
		);
		$filePath='unknown.blade.php';
	} catch (\Throwable $e) {
		Log::error(
			'Throwable resolving blade file path',
			[
				'exception_class'=>get_class($e),
				'message'=>$e->getMessage(),
				'file'=>$e->getFile(),
				'line'=>$e->getLine()
			]
		);
		$filePath='unknown.blade.php';
	}
	try {
		$data=Utility::prepareCommonViewData()?:[];
		$setting=$data[SC::ENTITY]??
			SC::DFT_SETTINGS;
		$colorSettings=$data[SC::CLR_STG]??[];
		$company_logo_dk=$setting[SC::CPN_LG_DK]??
			$setting[SC::CPN_LG_LT]??'';
		$company_logo_lt=$setting[SC::CPN_LG_LT]??
			$setting[SC::CPN_LG_DK]??'';
		$company_favicon=$data[SC::FAV_ICN]? asset($data[SC::FAV_ICN]) :
			asset('favicon.ico');
		$logo=$data[SC::LOGO]? asset($data[SC::LOGO]) : asset('favicon.ico');
		$color=$data[SC::THM_CLR]??
			SC::THM_CLR_DEF;
		$siteRtl=$data[SC::RTL]??'off';
		$routeLang=request()->route('lang');
		$lang=($routeLang && in_array($routeLang, array_keys(Utility::langList()), true) ? $routeLang : null)
			?? str_replace('_','-',app()->getLocale())
			?? $data[SC::LCL]
			?? Utility::fetchUserLang()
			?? DatabaseConstants::DEFAULT_LANG;
		if(in_array($lang,['ar','he'],true))
			$siteRtl='on';
		elseif($routeLang && !in_array($routeLang,['ar','he'],true))
			$siteRtl=$data[SC::RTL]??'off';
		$meta_title=$data[SC::MT_TTL_K]??
			config('app.name','ERPNovaPrestech');
		$meta_desc=$data[SC::MT_DESC_LONG]??
			config('app.desc','A brand new ERP!');
		$meta_image=$data[SC::MT_IMG_K]??
			$setting[SC::CPN_LG_LT]??
			$setting[SC::CPN_LG_DK]??'';
		$meta_logo=$data[SC::MT_LOGO]??
			$data[SC::MT_IMG_K]??
			$setting[SC::CPN_LG_LT]??
			$setting[SC::CPN_LG_DK]??'';
		$get_cookie=$data[SC::CK_STG]??'off';
		$faviconUrl=Utility::getCompanyLogo()?:'';
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
<html lang="{{ $lang }}" dir="{{ $siteRtl === 'on' ? 'rtl' : 'ltr' }}">
    <head>
        <title>
            {{ Utility::getValByName('title_text') ? Utility::getValByName('title_text') : config('app.name', 'ERPNovaPrestech') }}
            - @yield(YieldingConstants::AUTH_PG_TTL)</title>
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
        @include('fragments.favicon', ['faviconUrl' => $faviconUrl])
        @include('fragments.stylesheets', ['settings' => $colorSettings])
        @if ($colorSettings[SC::CST_DRK] ==='on' && is_file(asset('assets/css/custom-auth-dark.css')))
            <link rel="stylesheet" href="{{ asset('assets/css/custom-auth-dark.css') }}" id="custom-auth-style-link">
        @else
            <link rel="stylesheet" href="{{ asset('assets/css/custom-auth.css') }}" id="custom-auth-style-link">
        @endif
        @if ($siteRtl === 'on')
            <link rel="stylesheet" href="{{ asset('assets/css/style-rtl.css') }}" id="style-rtl-link">
            <link rel="stylesheet" href="{{ asset('assets/css/custom-auth-rtl.css') }}" id="custom-auth-rtl-link">
        @endif
        <link rel="stylesheet" href="{{ asset('assets/css/auth-enhancements.css') }}">
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
        <div class="custom-login">
            <div class="login-bg-img">
                <img src="{{ asset('assets/images/auth/' . ($color ?: 'default') . '.svg') }}" class="login-bg-1">
                <img src="{{ asset('assets/images/auth/common.svg') }}" class="login-bg-2">
            </div>
            <div class="bg-login {{ VC::BG_P }}"></div>
            <div class="custom-login-inner">
                <header class="{{ VC::DSH }}">
                    <nav class="{{ VC::NVB_DEF }}">
                        <div class="{{ VC::CT }}">
                            <div class="{{ VC::NVB_BR }}">
                            <a class="{{ VC::NVB_BR }}" href="https://prestech.com.br/site/" rel="external" target="_blank" hreflang="pt-BR" title="Nova Prestech">
                                @php
                                    $srcDark='';
                                    $srcLight='';
                                    $bgDark='';
                                    $bgLight='';
                                    try {
                                        $srcDark=!empty($company_logo_dk)?$company_logo_dk:'logo-dark.webp';
                                        $srcLight=!empty($company_logo_lt)?$company_logo_lt:'logo-light.webp';
                                        $bgDark=($company_logo_dk===$company_logo_lt)?'background-color: #181818':'';
                                        $bgLight=($company_logo_lt===$company_logo_dk)?'background-color: #fff':'';
                                    } catch (\Error $e) {
                                        Log::error(
                                            'Error computing logo sources/backgrounds',
                                            [
                                                'exception_class'=>get_class($e),
                                                'message'=>$e->getMessage(),
                                                'file'=>$e->getFile(),
                                                'line'=>$e->getLine()
                                            ]
                                        );
                                    } catch (\Exception $e) {
                                        Log::error(
                                            'Exception computing logo sources/backgrounds',
                                            [
                                                'exception_class'=>get_class($e),
                                                'message'=>$e->getMessage(),
                                                'file'=>$e->getFile(),
                                                'line'=>$e->getLine()
                                            ]
                                        );
                                    } catch (\Throwable $e) {
                                        Log::error(
                                            'Throwable computing logo sources/backgrounds',
                                            [
                                                'exception_class'=>get_class($e),
                                                'message'=>$e->getMessage(),
                                                'file'=>$e->getFile(),
                                                'line'=>$e->getLine()
                                            ]
                                        );
                                    }
@endphp
                                @if ($colorSettings[SC::CST_DRK] === 'on')
                                    <img
                                        class="{{ VC::LOGO }}"
                                        src="{{ asset($srcDark) }}"
                                        alt="Company Logo"
                                        loading="lazy"
                                        @if($bgDark)
                                            style="{{ $bgDark }}"
                                        @endif
                                    />
                                @else
                                    <img
                                        class="{{ VC::LOGO }}"
                                        src="{{ asset($srcLight) }}"
                                        alt="Company Logo"
                                        loading="lazy"
                                        @if($bgLight)
                                            style="{{ $bgLight }}"
                                        @endif
                                    />
                                @endif
                            </a>
                            </div>
                            <button class="{{ VC::NVB_TG }}" type="button" data-bs-toggle="collapse"
                                data-bs-target="#navbarlogin">
                                <span class="{{ VC::NVB_TG_IC }}"></span>
                            </button>
                            <div class="{{ VC::NVB_CLP }}" id="navbarlogin">
                                <ul class="{{ VC::NVB_NAV_LG }}">
                                    @includeIf(R::LP.'::'.E::LOS.'.buttons')
                                    @yield(YieldingConstants::AUTH_LG_BAR)
                                </ul>
                            </div>
                        </div>
                    </nav>
                </header>
                <main class="custom-wrapper">
                    <div class="custom-row">
                        <div class="{{ VC::CD }}">
                            @yield(YieldingConstants::AUTH_CTT)
                        </div>
                    </div>
                </main>
                <footer>
                    <div class="{{ VC::AUT_FT }}">
                        <div class="{{ VC::CT }}">
                            <div class="{{ VC::RW }}">
                                <div class="{{ VC::C12 }}">
                                    <span>&copy; {{ date('Y') }}
                                        {{ Utility::getValByName(SC::FT_TXT) ?: config('app.name', 'Storego Saas') }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </footer>
            </div>
        </div>
        @if($get_cookie === 'on')
            @includeIf(ExtendingLayoutsConstants::CKC)
        @endif
        <div
        id="loginToast"
        class="toast position-fixed top-0 end-0 m-3"
        role="alert"
        aria-live="assertive"
        aria-atomic="true"
        data-bs-delay="5000"
        style="display: none;"
        >
            <div class="toast-header">
                <strong class="me-auto {{ VC::TX_DNG }}">Oops!</strong>
                <button
                type="button"
                class="{{ VC::BT_CL }}"
                data-bs-dismiss="toast"
                aria-label="Close"
                ></button>
            </div>
            <div class="toast-body">
                @stack('toasts')
            </div>
        </div>
        @if(session('toast_error'))
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                var toast = document.getElementById('loginToast');
                if (!toast) return;
                var body = toast.querySelector('.toast-body');
                if (body) body.textContent = @json(session('toast_error'));
                toast.style.display = 'block';
                toast.style.zIndex = '1100';
                var bs = new bootstrap.Toast(toast, { delay: 5000 });
                bs.show();
                toast.addEventListener('hidden.bs.toast', function h() {
                    if (body) body.textContent = '';
                    toast.style.display = 'none';
                    toast.removeEventListener('hidden.bs.toast', h);
                });
                // Briefly outline the email and password inputs
                var inputs = document.querySelectorAll('input[name="email"], input[name="password"]');
                inputs.forEach(function (inp) {
                    inp.style.outline = '2px solid #dc3545';
                    inp.style.outlineOffset = '1px';
                    inp.style.transition = 'outline-color 2s ease';
                    setTimeout(function () {
                        inp.style.outlineColor = 'transparent';
                        setTimeout(function () { inp.style.outline = ''; inp.style.outlineOffset = ''; }, 2100);
                    }, 2500);
                });
            });
        </script>
        @endif
        <div class="bg-auth-side {{ VC::BG_P }}"></div>
            <div class="auth-content">
                <nav class="{{ VC::NVB_DEF }} navbar-light" style="border-block: 1px solid #eeeeee;">
                    <div class="container-fluid pe-2">
                        <a class="{{ VC::NVB_BR }}" href="#">
                            @if ($colorSettings[SC::CST_DRK] && $colorSettings[SC::CST_DRK] ==='on')
                                <img src="{{ $logo . '/' . (isset($company_logo_lt) && !empty($company_logo_lt) ? $company_logo_lt : SC::CPN_LG_DK_DEF) }}"
                                    alt="{{ config('app.name', 'ERPNovaPrestech') }}" class="{{ VC::LOGO }}" style="max-width: 10rem;">
                            @else
                                <img src="{{ $logo . '/' . (isset($company_logo_dk) && !empty($company_logo_dk) ? $company_logo_dk : SC::CPN_LG_DK_DEF) }}"
                                    alt="{{ config('app.name', 'ERPNovaPrestech') }}" class="{{ VC::LOGO }}" style="max-width: 10rem;">
                            @endif
                        </a>
                        <button
                            class="{{ VC::NVB_TG }}"
                            type="button"
                            data-bs-toggle="collapse"
                            data-bs-target="#navbarTogglerDemo01"
                            aria-controls="navbarTogglerDemo01"
                            aria-expanded="false"
                            aria-label="Toggle navigation"
                        >
                            <span class="{{ VC::NVB_TG_IC }}"></span>
                        </button>
                        <div class="{{ VC::NVB_CLP }}" id="navbarTogglerDemo01" style="flex-grow: 0;">
                            <ul class="{{ VC::NVB_NAV_LG }}">
                                <li class="{{ VC::NV_IT }}">
                                    @include(R::LP.'::'.E::LOS.'.buttons')
                                </li>

                            </ul>

                            <ul class="{{ VC::NVB_NAV_LG }}">
                                @yield(YieldingConstants::AUTH_TB)
                            </ul>
                        </div>
                    </div>
                </nav>
                <div class="card" style="scrollbar-width: none;">
                    <div class="{{ VC::R_ALC }} text-start">
                        <div class="col-xl-6">
                            <div class="{{ VC::CD_BD }}">
                                @yield(YieldingConstants::AUTH_CTT)
                            </div>
                        </div>
                        <div class="col-xl-6 img-card-side">
                            <div class="auth-img-content">
                                <img
                                    src="{{ asset('assets/images/auth/img-auth-3.svg') }}"
                                    alt=""
                                    class="{{ VC::IMG_FL }}"
                                />
                                <h3 class="{{ VC::TXT_WT }} {{ VC::MB4 }} mt-5">
                                    “Attention is the new currency”
                                </h3>
                                <p class="{{ VC::TXT_WT }}">
                                    The more effortless the writing looks, the more effort the
                                    writer actually put into the process.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="{{ VC::AUT_FT }}">
                    <div class="container-fluid">
                        <div class="{{ VC::RW }}">
                            <div class="{{ VC::C6 }}">
                                <p class="{{ VC::MB0 }}"> &copy;
                                    {{ date('Y') }} {{ Utility::getValByName(SC::FT_TXT) ? Utility::getValByName(SC::FT_TXT) : config('app.name', 'ERPNovaPrestech') }}
                                </p>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
        <script src="{{ asset('assets/js/vendor-all.js') }}"></script>
        <script defer src="{{ asset('assets/js/plugins/bootstrap.min.js') }}"></script>
        <script defer src="{{ asset('assets/js/plugins/feather.min.js') }}"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                feather && typeof feather.replace === 'function' && feather.replace();
            });
        </script>
        @if ($colorSettings[SC::CST_DRK] === 'on')
            <style>
                .g-recaptcha {
                    filter: invert(1) hue-rotate(180deg) !important;
                }
            </style>
        @endif
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                feather && typeof feather.replace === 'function' && feather.replace();
                document.querySelector("#pct-toggler")?.addEventListener("click", () => {
                    const cust = document.querySelector(".pct-customizer");
                    cust && cust.classList.toggle("active");
                });
                document.querySelectorAll(".themes-color > a").forEach(c => {
                    c.addEventListener("click", event => {
                        let target = event.target.tagName === "SPAN" ? event.target.parentNode : event.target;
                        const val = target.getAttribute("data-value");
                        if (val) {
                            document.body.classList.forEach(cls => {
                                if (cls.startsWith("theme-")) document.body.classList.remove(cls);
                            });
                            document.body.classList.add(val);
                        }
                    });
                });
            });
        </script>
        {{-- ERP Guard & Utils Core Classes — MUST load before @stack so dependent scripts have access --}}
        <script src="{{ asset('assets/js/core/erp-guard.js') }}"></script>
        <script src="{{ asset('assets/js/core/erp-utils.js') }}"></script>
        <script src="{{ asset('assets/js/core/erp-bootstrap.min.js') }}"></script>
        @stack(StacksConstants::AUTH_CST_SCR)
        <script>
           (window.location.hostname === '127.0.0.1' || window.location.hostname === 'localhost') && console.log(
                'Current route:',
                '{{ Illuminate\Support\Facades\Route::currentRouteName() ?? Illuminate\Support\Facades\Route::currentRouteAction() }}'
            );
        </script>
        @include('partials.global-error-handler')
    </body>
</html>
