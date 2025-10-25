<!DOCTYPE html>
@php
	use App\Config\Constants\{
		DatabaseConstants,
		ExtendingLayoutsConstants,
		SettingsConstants,
		StacksConstants,
		ViewClassNamesConstants,
		YieldingConstants
	};
	use App\Models\Utility;
	use Illuminate\Support\Facades\Log;
	use Modules\LandingPage\Config\Constants\{
		ExtendingLandingPageLayoutConstants as E,
		RoutesResourcesConstants       as R
	};
	use Symfony\Component\Console\Output\ConsoleOutput;
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
			$contents=file_get_contents($compiledPath);
			$filePath=preg_match(
				'/\*\*PATH\s+(.+\.blade\.php)\s+ENDPATH\*\*/',
				$contents,$m
			)?$m[1]:'unknown.blade.php';
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
		Log::debug(
			"Rendering Authentication Layout Blade ({$filePath})",
			['route'=>$uri,'user'=>optional(auth()->user())->id??'Unidentified User']
		);
	} catch (Error|Exception|Throwable $e) {}
	try {
		(new ConsoleOutput)
			->writeln(
				"Rendering Authentication Layout Blade ({$filePath}) for {$uri}"
			);
	} catch (Error|Exception|Throwable $e) {}
	try {
		$data=Utility::prepareCommonViewData()?:[];
		$setting=$data[SettingsConstants::ENTITY]??
			SettingsConstants::DFT_SETTINGS;
		$colorSettings=$data[SettingsConstants::CLR_STG]??[];
		$company_logo_dk=$setting[SettingsConstants::CPN_LG_DK]??
			$setting[SettingsConstants::CPN_LG_LT]??'';
		$company_logo_lt=$setting[SettingsConstants::CPN_LG_LT]??
			$setting[SettingsConstants::CPN_LG_DK]??'';
		$company_favicon=$data[SettingsConstants::FAV_ICN]? asset($data[SettingsConstants::FAV_ICN]) :
			asset('favicon.ico');
		$logo=$data[SettingsConstants::LOGO]? asset($data[SettingsConstants::LOGO]) : asset('favicon.ico');
		$color=$data[SettingsConstants::THM_CLR]??
			SettingsConstants::THM_CLR_DEF;
		$siteRtl=$data[SettingsConstants::RTL]??'off';
		$lang=$data[SettingsConstants::LCL]?? Utility::fetchUserLang() ??
			str_replace('_','-',app()->getLocale())??
			DatabaseConstants::DEFAULT_LANG;
		$meta_title=$data[SettingsConstants::MT_TTL_K]??
			config('app.name','ERPNovaPrestech');
		$meta_desc=$data[SettingsConstants::MT_DESC_LONG]??
			config('app.desc','A brand new ERP!');
		$meta_image=$data[SettingsConstants::MT_IMG_K]??
			$setting[SettingsConstants::CPN_LG_LT]??
			$setting[SettingsConstants::CPN_LG_DK]??'';
		$meta_logo=$data[SettingsConstants::MT_LOGO]??
			$data[SettingsConstants::MT_IMG_K]??
			$setting[SettingsConstants::CPN_LG_LT]??
			$setting[SettingsConstants::CPN_LG_DK]??'';
		$get_cookie=$data[SettingsConstants::CK_STG]??'off';
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
        @if ($colorSettings[SettingsConstants::CST_DRK] ==='on' && is_file(asset('assets/css/custom-auth-dark.css')))
            <link rel="stylesheet" href="{{ asset('assets/css/custom-auth-dark.css') }}" id="custom-auth-style-link">
        @else
            <link rel="stylesheet" href="{{ asset('assets/css/custom-auth.css') }}" id="custom-auth-style-link">
        @endif
        @if ($siteRtl === 'on')
            <link rel="stylesheet" href="{{ asset('assets/css/style-rtl.css') }}" id="style-rtl-link">
            <link rel="stylesheet" href="{{ asset('assets/css/custom-auth-rtl.css') }}" id="custom-auth-rtl-link">
        @endif
    </head>
    <body class="{{ $color }}">
        <div class="custom-login">
            <div class="login-bg-img">
                <img src="{{ asset('assets/images/auth/' . ($color ?: 'default') . '.svg') }}" class="login-bg-1">
                <img src="{{ asset('assets/images/auth/common.svg') }}" class="login-bg-2">
            </div>
            <div class="bg-login {{ ViewClassNamesConstants::BG_P }}"></div>
            <div class="custom-login-inner">
                <header class="{{ ViewClassNamesConstants::DSH }}">
                    <nav class="{{ ViewClassNamesConstants::NVB_DEF }}">
                        <div class="{{ ViewClassNamesConstants::CT }}">
                            <div class="{{ ViewClassNamesConstants::NVB_BR }}">
                            <a class="{{ ViewClassNamesConstants::NVB_BR }}" href="#">
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
                                @if ($colorSettings[SettingsConstants::CST_DRK] === 'on')
                                    <img
                                        class="{{ ViewClassNamesConstants::LOGO }}"
                                        src="{{ asset($srcDark) }}"
                                        alt="Company Logo"
                                        loading="lazy"
                                        @if($bgDark)
                                            style="{{ $bgDark }}"
                                        @endif
                                    />
                                @else
                                    <img
                                        class="{{ ViewClassNamesConstants::LOGO }}"
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
                            <button class="{{ ViewClassNamesConstants::NVB_TG }}" type="button" data-bs-toggle="collapse"
                                data-bs-target="#navbarlogin">
                                <span class="{{ ViewClassNamesConstants::NVB_TG_IC }}"></span>
                            </button>
                            <div class="{{ ViewClassNamesConstants::NVB_CLP }}" id="navbarlogin">
                                <ul class="{{ ViewClassNamesConstants::NVB_NAV_LG }}">
                                    @includeIf(R::LP.'::'.E::LOS.'.buttons')
                                    @yield(YieldingConstants::AUTH_LG_BAR)
                                </ul>
                            </div>
                        </div>
                    </nav>
                </header>
                <main class="custom-wrapper">
                    <div class="custom-row">
                        <div class="{{ ViewClassNamesConstants::CD }}">
                            @yield(YieldingConstants::AUTH_CTT)
                        </div>
                    </div>
                </main>
                <footer>
                    <div class="{{ ViewClassNamesConstants::AUT_FT }}">
                        <div class="{{ ViewClassNamesConstants::CT }}">
                            <div class="{{ ViewClassNamesConstants::RW }}">
                                <div class="col-12">
                                    <span>&copy; {{ date('Y') }}
                                        {{ Utility::getValByName(SettingsConstants::FT_TXT) ?: config('app.name', 'Storego Saas') }}
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
                <strong class="me-auto text-danger">Oops!</strong>
                <button
                type="button"
                class="btn-close"
                data-bs-dismiss="toast"
                aria-label="Close"
                ></button>
            </div>
            <div class="toast-body">
                @stack('toasts')
            </div>
        </div>
        {{-- <div class="auth-wrapper auth-v3">
        <div class="bg-auth-side bg-primary"></div>
            <div class="auth-content">
                <nav class="{{ ViewClassNamesConstants::NVB_DEF }} navbar-light">
                    <div class="container-fluid pe-2">
                        <a class="{{ ViewClassNamesConstants::NVB_BR }}" href="#">
                            @if ($colorSettings[SettingsConstants::CST_DRK] && $colorSettings[SettingsConstants::CST_DRK] ==='on')
                                <img src="{{ $logo . '/' . (isset($company_logo_lt) && !empty($company_logo_lt) ? $company_logo_lt : SettingsConstants::CPN_LG_DK_DEF) }}"
                                    alt="{{ config('app.name', 'ERPNovaPrestech') }}" class="{{ ViewClassNamesConstants::LOGO }}">
                            @else
                                <img src="{{ $logo . '/' . (isset($company_logo_dk) && !empty($company_logo_dk) ? $company_logo_dk : SettingsConstants::CPN_LG_DK_DEF) }}"
                                    alt="{{ config('app.name', 'ERPNovaPrestech') }}" class="{{ ViewClassNamesConstants::LOGO }}">
                            @endif
                        </a>
                        <button
                            class="{{ ViewClassNamesConstants::NVB_TG }}"
                            type="button"
                            data-bs-toggle="collapse"
                            data-bs-target="#navbarTogglerDemo01"
                            aria-controls="navbarTogglerDemo01"
                            aria-expanded="false"
                            aria-label="Toggle navigation"
                        >
                            <span class="{{ ViewClassNamesConstants::NVB_TG_IC }}"></span>
                        </button>
                        <div class="{{ ViewClassNamesConstants::NVB_CLP }}" id="navbarTogglerDemo01" style="flex-grow: 0;">
                            <ul class="{{ ViewClassNamesConstants::NVB_NAV_LG }}">
                                <li class="nav-item">
                                    @include(R::LP.'::'.E::LOS.'.buttons')
                                </li>

                            </ul>

                            <ul class="{{ ViewClassNamesConstants::NVB_NAV_LG }}">
                                @yield(YieldingConstants::AUTH_TB)
                            </ul>
                        </div>
                    </div>
                </nav>
                <div class="{{ ViewClassNamesConstants::CD }}">
                    <div class="row align-items-center text-start">
                        <div class="col-xl-6">
                            <div class="card-body">
                                @yield(YieldingConstants::AUTH_CTT)
                            </div>
                        </div>
                        <div class="col-xl-6 img-card-side">
                            <div class="auth-img-content">
                                <img
                                    src="{{ asset('assets/images/auth/img-auth-3.svg') }}"
                                    alt=""
                                    class="img-fluid"
                                />
                                <h3 class="text-white mb-4 mt-5">
                                    “Attention is the new currency”
                                </h3>
                                <p class="text-white">
                                    The more effortless the writing looks, the more effort the
                                    writer actually put into the process.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="{{ ViewClassNamesConstants::AUT_FT }}">
                    <div class="container-fluid">
                        <div class="{{ ViewClassNamesConstants::RW }}">
                            <div class="col-6">
                                <p class="mb-0"> &copy;
                                    {{ date('Y') }} {{ Utility::getValByName(SettingsConstants::FT_TXT) ? Utility::getValByName(SettingsConstants::FT_TXT) : config('app.name', 'ERPNovaPrestech') }}
                                </p>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div> --}}
        <script src="{{ asset('assets/js/vendor-all.js') }}"></script>
        <script defer src="{{ asset('assets/js/plugins/bootstrap.min.js') }}"></script>
        <script defer src="{{ asset('assets/js/plugins/feather.min.js') }}"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                feather.replace();
            });
        </script>
        @if ($colorSettings[SettingsConstants::CST_DRK] === 'on')
            <style>
                .g-recaptcha {
                    filter: invert(1) hue-rotate(180deg) !important;
                }
            </style>
        @endif
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                feather.replace();
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
        @stack(StacksConstants::AUTH_CST_SCR)
        <script>
           (window.location.hostname === '127.0.0.1' || window.location.hostname === 'localhost') && console.log(
                'Current route:',
                '{{ Illuminate\Support\Facades\Route::currentRouteName() ?? Illuminate\Support\Facades\Route::currentRouteAction() }}'
            );
        </script>
    </body>
</html>
