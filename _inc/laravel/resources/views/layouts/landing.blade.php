@php
$data??=[];
	$setting??=[];
	$logo??='';
	$company_logo??='';
	$company_logos??='';
	$colorSettings??=[];
	$color??='';
	$mode_setting??='';
	$siteRtl??=false;
	$meta_title??='';
	$meta_desc??='';
	$meta_image??='';
	$meta_logo??='';
	$get_cookie??='';
	$faviconUrl??='';
    $lang = Utility::fetchUserLang();
	try {
		$data=Utility::prepareCommonViewData(null,'uploads/logo')?:[];
		$setting=$data[SC::ENTITY]??[];
		$logo=$data[SC::LOGO]??'';
		$company_logo=$data[SC::CPN_LG_DK]??'';
		$company_logos=$data[SC::CPN_LG_LT]??'';
		$colorSettings=$data[SC::CLR_STG]??[];
		$color=$data[SC::THM_CLR]??'';
		$mode_setting=$data[SC::MD_LO]??'';
		$siteRtl=$data[SC::RTL]??false;
		$meta_title=$data[SC::MT_TTL_K]??'';
		$meta_desc=$data[SC::MT_DESC_LONG]??'';
		$meta_image=$data[SC::MT_IMG_K]??'';
		$meta_logo=$data[SC::MT_LOGO]??'';
		$get_cookie=$data[SC::CK_STG]??'';
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
<!DOCTYPE html>
    <html lang="{{ $lang ?? str_replace('_', '-', is_string(app()->getLocale()) ? app()->getLocale() : DC::DEFAULT_LANG) }}" dir="{{$siteRtl == 'on'?'rtl':''}}">
        <head>
            <title>{{__('ERP Nova Prestech')}}</title>
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
            <!-- HTML5 Shim and Respond.js IE11 support of HTML5 elements and media queries -->
            <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
            <!--[if lt IE 11]>
            <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
            <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
            <![endif]-->
            <!-- Meta -->
            @include('fragments.favicon', ['faviconUrl' => $faviconUrl])
            <link rel="stylesheet" href="{{asset('assets/css/plugins/animate.min.css')}}" />
            <link rel="stylesheet" href="{{asset('assets/fonts/tabler-icons.min.css')}}">
            <link rel="stylesheet" href="{{asset('assets/fonts/feather.css')}}">
            <link rel="stylesheet" href="{{asset('assets/fonts/fontawesome.css')}}">
            <link rel="stylesheet" href="{{asset('assets/fonts/material.css')}}">
            @if ($siteRtl == 'on')
                <link rel="stylesheet" href="{{ asset('assets/css/style-rtl.css') }}">
            @endif
            @if ($colorSettings[SC::CST_DRK] == 'on')
                <link rel="stylesheet" href="{{ asset('assets/css/style-dark.css') }}">
            @else
                <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}" id="main-style-link">
            @endif
            <link rel="stylesheet" href="{{asset('assets/css/customizer.css')}}">
            <link rel="stylesheet" href="{{asset('assets/css/landing.css')}}" />
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
        <body class="{{$color}}">
            <nav class="{{ VC::NVB_DEF_TOP }} navbar-dark">
                <div class="{{ VC::CT }}">
                    <a class="{{ VC::NVB_BR_TPR }}" href="">
                        <img src="{{ $logo .'/'.SC::CPN_LG_LT_DEF }}" alt="logo" width="40%"/>
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
                    <div class="{{ VC::NVB_CLP }}" id="navbarTogglerDemo01">
                        <ul class="{{ VC::NVB_NAV_LG }}">
                            @foreach(['home','features','layouts','testimonial','pricing','faq'] as $key)
                                <li class="{{ VC::NV_IT }}">
                                    <a
                                        class="{{ $loop->first ? VC::NV_LK.' active' :
                                        VC::NV_LK }}"
                                        href="#{{ $key }}"
                                    >
                                        {{ ucfirst($key) }}
                                    </a>
                                </li>
                            @endforeach
                            <li class="{{ VC::NV_IT }}">
                                @php
                                    $loginUrl = Route::has('login') ? route('login') : '#';
@endphp
                                <a class="{{ VC::BT_LG }} {{ VC::MS2 }} me-1" href="{{ $loginUrl }}">
                                    {{ __('Login') }}
                                </a>
                            </li>
                            @if(!empty($setting[SC::ENB_SGU]) && $setting[SC::ENB_SGU] == 'on')
                                <li class="{{ VC::NV_IT }}">
                                    <a class="{{ VC::BT_LG }} {{ VC::MS2 }} me-1"
                                    href="{{ Route::has('register') ? route('register') : '#' }}">
                                        {{ __('Register') }}
                                    </a>
                                </li>
                            @endif
                        </ul>
                    </div>
                </div>
            </nav>
            <header id="home" class="{{ VC::BG_P }}">
                <div class="{{ VC::CT }}">
                    <div class="{{ VC::R_ALC }} {{ VC::JCB }}">
                        <div class="col-sm-5">
                            <h1
                                class="{{ VC::TXT_WT }} mb-sm-4 wow animate__fadeInLeft"
                                data-wow-delay="0.2s"
                            >
                                {{__('ERP Nova Prestech')}}
                            </h1>
                            <h2
                                class="{{ VC::TXT_WT }} mb-sm-4 wow animate__fadeInLeft"
                                data-wow-delay="0.4s"
                            >
                                {{__('All In One Business ERP With Project, Account, HRM, CRM')}}
                            </h2>
                            <p class="mb-sm-4 wow animate__fadeInLeft" data-wow-delay="0.6s">
                                {{ __('Use these awesome forms to login or create new account in your
                                project for free.')}}
                            </p>
                            <div class="{{ VC::MY4 }} wow animate__fadeInLeft" data-wow-delay="0.8s">
                                @php
                                    $liveDemoUrl = Route::has('login') ? route('login') : '#';
@endphp
                                <a href="{{ $liveDemoUrl }}" class="{{ VC::BT_LG }} me-2">
                                    <i class="{{ VC::FAR_EYE }} me-2"></i>Live Demo
                                </a>
                                <a href="https://codecanyon.net/item/erpgo-saas-all-in-one-business-erp-with-project-account-hrm-crm/33263426"
                                class="{{ VC::BT_OUT_LG }}"
                                target="_blank">
                                    <i class="{{ VC::FAS_CART }} me-2"></i>Buy now
                                </a>
                            </div>
                        </div>
                        <div class="col-sm-5">
                            <img
                                src="{{asset('assets/images/front/header-mokeup.svg')}}"
                                alt="Datta Able Admin Template"
                                class="{{ VC::IMG_FL }} header-img wow animate__fadeInRight"
                                data-wow-delay="0.2s"
                            />
                        </div>
                    </div>
                </div>
            </header>
            <section id="dashboard" class="theme-alt-bg dashboard-block">
                <div class="{{ VC::CT }}">
                    <div class="{{ VC::RW }} justify-content-center">
                        <div class="col-xl-6 {{ VC::CM9 }} title">
                            <h2><span>Happy clients use Dashboard</span> </h2>
                        </div>
                    </div>
                    @php
                        try {
                            $delays = ['0.2s', '0.4s', '0.6s', '0.8s', '1s'];
                            $isDarkMode = $mode_setting[SC::CST_DRK] && $mode_setting[SC::CST_DRK] == 'on';
                            $logoSrc = $logo . '/' . ($isDarkMode
                                ? (isset($company_logos) && !empty($company_logos) ? $company_logos : SC::CPN_LG_DK_DEF)
                                : (isset($company_logo) && !empty($company_logo) ? $company_logo : SC::CPN_LG_LT_DEF)
                            );
                        } catch (\Throwable $e) {
                            \Log::error('layouts/landing — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                        }
@endphp
                    <div class="{{ VC::R_ALC }} justify-content-center mobile-screen dashboard_images">
                        @foreach($delays as $delay)
                            <div class="{{ VC::CL2 }}">
                                <div class="wow animate__fadeInRight mobile-widget" data-wow-delay="{{ $delay }}">
                                    <img src="{{ $logoSrc }}" alt="" class="{{ VC::IMG_FL }}">
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <img
                        src="{{asset('landing/images/dashboard.png')}}"
                        alt=""
                        class="{{ VC::IMG_FL }} img-dashboard wow animate__fadeInUp mt-5"  style='border-radius: 15px;'
                        data-wow-delay="0.2s"
                    />
                </div>
            </section>
            <section id="dashboard" class="theme-alt-bg dashboard-block">
                <div class="{{ VC::CT }}">
                    <div class="{{ VC::R_ALC }} {{ VC::JCE }} mb-5">
                        <div class="{{ VC::CS4 }}">
                            <h1
                                class="mb-sm-4 {{ VC::FW600 }} wow animate__fadeInLeft"
                                data-wow-delay="0.2s"
                            >
                                {{__('ERP Nova Prestech')}}
                            </h1>
                            <h2 class="mb-sm-4 wow animate__fadeInLeft" data-wow-delay="0.4s">
                                {{__(' All In One Business ERP With Project, Account, HRM, CRM')}}
                            </h2>
                            <p class="mb-sm-4 wow animate__fadeInLeft" data-wow-delay="0.6s">
                                {{ __('Use these awesome forms to login or create new account in your
                                project for free.')}}
                            </p>
                            <div class="{{ VC::MY4 }} wow animate__fadeInLeft" data-wow-delay="0.8s">
                                <a href="#" class="{{ VC::BT_PRM }}" target="_blank"
                                ><i class="{{ VC::FAS_CART }} me-2"></i>Buy now</a
                                >
                            </div>
                        </div>
                        <div class="{{ VC::CS6 }}">
                            <img
                                src="{{asset('landing/images/dashboard.png')}}"
                                alt="Datta Able Admin Template"
                                class="{{ VC::IMG_FL }} header-img wow animate__fadeInRight"
                                data-wow-delay="0.2s"
                            />
                        </div>
                    </div>
                    <div class="{{ VC::R_ALC }} justify-content-start">
                        <div class="{{ VC::CS6 }}">
                            <img
                                src="{{asset('assets/images/front/img-crm-dash-2.svg')}}"
                                alt="Datta Able Admin Template"
                                class="{{ VC::IMG_FL }} header-img wow animate__fadeInLeft"
                                data-wow-delay="0.2s"
                            />
                        </div>
                        <div class="{{ VC::CS4 }}">
                            <h1
                                class="mb-sm-4 {{ VC::FW600 }} wow animate__fadeInRight"
                                data-wow-delay="0.2s"
                            >
                                {{__('ERP Nova Prestech')}}
                            </h1>
                            <h2 class="mb-sm-4 wow animate__fadeInRight" data-wow-delay="0.4s">
                                {{__('All In One Business ERP With Project, Account, HRM, CRM')}}
                            </h2>
                            <p class="mb-sm-4 wow animate__fadeInRight" data-wow-delay="0.6s">
                                {{ __('Use these awesome forms to login or create new account in your
                                project for free.')}}
                            </p>
                            <div class="{{ VC::MY4 }} wow animate__fadeInRight" data-wow-delay="0.8s">
                                <a href="#" class="{{ VC::BT_PRM }}" target="_blank"
                                ><i class="{{ VC::FAS_CART }} me-2"></i>Buy now</a
                                >
                            </div>
                        </div>
                    </div>
                </div>
            </section>
            <section id="feature" class="feature">
                <div class="{{ VC::CT }}">
                    <div class="{{ VC::RW }} justify-content-center">
                        <div class="col-xl-6 {{ VC::CM9 }} title">
                            <h2>
                                <span class="{{ VC::DBL }} {{ VC::MB3 }}">Features</span> All in one place CRM
                                system
                            </h2>
                            <p class="m-0">
                                {{ __('Use these awesome forms to login or create new account in your
                                project for free.')}}
                            </p>
                        </div>
                    </div>
                    <div class="{{ VC::RW }} justify-content-center">
                        <div class="{{ VC::CL3 }} {{ VC::CM6 }}">
                            <div
                                class="card wow animate__fadeInUp"
                                data-wow-delay="0.8s"
                                style="
                            visibility: visible;
                            animation-delay: 0.2s;
                            animation-name: fadeInUp;
                        "
                            >
                                <div class="{{ VC::CD_BD }}">
                                    <div class="theme-avatar bg-danger">
                                        <i class="{{ VC::TI_RPT_MN }}"></i>
                                    </div>
                                    <h6 class="{{ VC::TXT_MT_MT4 }}">ABOUT</h6>
                                    <h4 class="{{ VC::MY3_FW600 }}">Feature</h4>
                                    <p class="{{ VC::MB0 }}">
                                        {{ __('Use these awesome forms to login or create new account in your
                                        project for free.')}}
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="{{ VC::CL3 }} {{ VC::CM6 }}">
                            <div
                                class="card wow animate__fadeInUp"
                                data-wow-delay="0.4s"
                                style="
                                    visibility: visible;
                                    animation-delay: 0.2s;
                                    animation-name: fadeInUp;
                                "
                            >
                                <div class="{{ VC::CD_BD }}">
                                    <div class="theme-avatar bg-success">
                                        <i class="{{ VC::TI_USR_PLS }}"></i>
                                    </div>
                                    <h6 class="{{ VC::TXT_MT_MT4 }}">ABOUT</h6>
                                    <h4 class="{{ VC::MY3_FW600 }}">Feature</h4>
                                    <p class="{{ VC::MB0 }}">
                                        {{ __('Use these awesome forms to login or create new account in your
                                project for free.')}}
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="{{ VC::CL3 }} {{ VC::CM6 }}">
                            <div
                                class="card wow animate__fadeInUp"
                                data-wow-delay="0.6s"
                                style="
                            visibility: visible;
                            animation-delay: 0.2s;
                            animation-name: fadeInUp;
                        "
                            >
                                <div class="{{ VC::CD_BD }}">
                                    <div class="theme-avatar bg-warning">
                                        <i class="{{ VC::TI_USRS }}"></i>
                                    </div>
                                    <h6 class="{{ VC::TXT_MT_MT4 }}">ABOUT</h6>
                                    <h4 class="{{ VC::MY3_FW600 }}">Feature</h4>
                                    <p class="{{ VC::MB0 }}">
                                        {{ __('Use these awesome forms to login or create new account in your
                                project for free.')}}
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="{{ VC::CL3 }} {{ VC::CM6 }}">
                            <div
                                class="card wow animate__fadeInUp"
                                data-wow-delay="0.8s"
                                style="
                            visibility: visible;
                            animation-delay: 0.2s;
                            animation-name: fadeInUp;
                        "
                            >
                                <div class="{{ VC::CD_BD }}">
                                    <div class="theme-avatar bg-danger">
                                        <i class="{{ VC::TI_RPT_MN }}"></i>
                                    </div>
                                    <h6 class="{{ VC::TXT_MT_MT4 }}">ABOUT</h6>
                                    <h4 class="{{ VC::MY3_FW600 }}">Feature</h4>
                                    <p class="{{ VC::MB0 }}">
                                        {{ __('Use these awesome forms to login or create new account in your
                                project for free.')}}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="{{ VC::TXCT }} pt-sm-5 feature-mobile-screen">
                        <button class="btn px-sm-5 {{ VC::BT_PM }} me-sm-3">Buy Now</button>
                        <button class="btn px-sm-5 btn-outline-primary">
                            View documentation
                        </button>
                    </div>
                </div>
            </section>
            <section class="">
                <div class="{{ VC::CT }}">
                    <div class="{{ VC::R_ALC }} {{ VC::JCE }} mb-5">
                        <div class="{{ VC::CS4 }}">
                            <h1
                                class="mb-sm-4 {{ VC::FW600 }} wow animate__fadeInLeft"
                                data-wow-delay="0.2s"
                            >
                                {{__('ERP Nova Prestech')}}
                            </h1>
                            <h2 class="mb-sm-4 wow animate__fadeInLeft" data-wow-delay="0.4s">
                                {{__('All In One Business ERP With Project, Account, HRM, CRM')}}
                            </h2>
                            <p class="mb-sm-4 wow animate__fadeInLeft" data-wow-delay="0.6s">
                                {{ __('Use these awesome forms to login or create new account in your
                                project for free.')}}
                            </p>
                            <div class="{{ VC::MY4 }} wow animate__fadeInLeft" data-wow-delay="0.8s">
                                <a href="#" class="{{ VC::BT_PRM }}" target="_blank"
                                ><i class="{{ VC::FAS_CART }} me-2"></i>Buy now</a
                                >
                            </div>
                        </div>
                        <div class="{{ VC::CS6 }}">
                            <img
                                src="{{asset('landing/images/dash-2.svg')}}"
                                alt="Datta Able Admin Template"
                                class="{{ VC::IMG_FL }} header-img wow animate__fadeInRight"
                                data-wow-delay="0.2s"
                            />
                        </div>
                    </div>
                    <div class="{{ VC::R_ALC }} justify-content-start">
                        <div class="{{ VC::CS6 }}">
                            <img
                                src="{{asset('assets/images/front/img-crm-dash-4.svg')}}"
                                alt="Datta Able Admin Template"
                                class="{{ VC::IMG_FL }} header-img wow animate__fadeInLeft"
                                data-wow-delay="0.2s"
                            />
                        </div>
                        <div class="{{ VC::CS4 }}">
                            <h1
                                class="mb-sm-4 {{ VC::FW600 }} wow animate__fadeInRight"
                                data-wow-delay="0.2s"
                            >
                                {{__('ERP Nova Prestech')}}
                            </h1>
                            <h2 class="mb-sm-4 wow animate__fadeInRight" data-wow-delay="0.4s">
                                {{__('All In One Business ERP With Project, Account, HRM, CRM')}}
                            </h2>
                            <p class="mb-sm-4 wow animate__fadeInRight" data-wow-delay="0.6s">
                                {{ __('Use these awesome forms to login or create new account in your
                                project for free.')}}
                            </p>
                            <div class="{{ VC::MY4 }} wow animate__fadeInRight" data-wow-delay="0.8s">
                                <a href="#" class="{{ VC::BT_PRM }}" target="_blank"
                                ><i class="{{ VC::FAS_CART }} me-2"></i>Buy now</a
                                >
                            </div>
                        </div>
                    </div>
                </div>
            </section>
            <section id="price" class="price-section">
                <div class="{{ VC::CT }}">
                    <div class="{{ VC::RW }} justify-content-center">
                        <div class="col-xl-6 {{ VC::CM9 }} title">
                            <h2>
                                <span class="{{ VC::DBL }} {{ VC::MB3 }}">Price</span> All in one place CRM
                                system
                            </h2>
                            <p class="m-0">
                                {{ __('Use these awesome forms to login or create new account in your
                                project for free.')}}
                            </p>
                        </div>
                    </div>
                    @php
                        try {
                            $pricingPlans = [
                                [
                                    'name'           => 'STARTER',
                                    'badgeClass'     => VC::BG_P,
                                    'price'          => 59,
                                    'period'         => '/month',
                                    'description'    => __('You have Free Unlimited Updates and Premium Support on each package.'),
                                    'features'       => [
                                        __('2 team members'),
                                        __('20GB Cloud storage'),
                                        __('Integration help'),
                                    ],
                                    'priceCardClass' => 'price-1',
                                    'delay'          => '0.2s',
                                    'buttonClass'    => 'btn-primary',
                                    'buttonLabel'    => __('Start with Standard plan'),
                                ],
                                [
                                    'name'           => 'STARTER',
                                    'badgeClass'     => '',
                                    'price'          => 59,
                                    'period'         => '/month',
                                    'description'    => __('You have Free Unlimited Updates and Premium Support on each package.'),
                                    'features'       => [
                                        __('2 team members'),
                                        __('20GB Cloud storage'),
                                        __('Integration help'),
                                        __('Sketch Files'),
                                    ],
                                    'priceCardClass' => 'price-2 bg-primary',
                                    'delay'          => '0.4s',
                                    'buttonClass'    => 'btn-light',
                                    'buttonLabel'    => __('Start with Standard plan'),
                                ],
                                [
                                    'name'           => 'STARTER',
                                    'badgeClass'     => VC::BG_P,
                                    'price'          => 119,
                                    'period'         => '/month',
                                    'description'    => __('You have Free Unlimited Updates and Premium Support on each package.'),
                                    'features'       => [
                                        __('2 team members'),
                                        __('20GB Cloud storage'),
                                        __('Integration help'),
                                        __('2 team members'),
                                        __('20GB Cloud storage'),
                                        __('Integration help'),
                                    ],
                                    'priceCardClass' => 'price-3',
                                    'delay'          => '0.6s',
                                    'buttonClass'    => 'btn-primary',
                                    'buttonLabel'    => __('Start with Standard plan'),
                                ],
                            ];
                        } catch (\Throwable $e) {
                            \Log::error('layouts/landing — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                        }
@endphp
                    <div class="{{ VC::RW }} justify-content-center">
                        @foreach($pricingPlans as $plan)
                            <div class="{{ VC::CL4 }} {{ VC::CM6 }}">
                                <div class="card price-card {{ $plan['priceCardClass'] }} wow animate__fadeInUp" data-wow-delay="{{ $plan['delay'] }}" style="visibility: visible; animation-delay: {{ $plan['delay'] }}; animation-name: fadeInUp;">
                                    <div class="{{ VC::CD_BD }}">
                                        <span class="price-badge {{ $plan['badgeClass'] }}">{{ $plan['name'] }}</span>
                                        <span class="{{ VC::MB4 }} {{ VC::FW600 }} p-price">${{ $plan['price'] }}
                                            <small class="{{ VC::TXSM }}">{{ $plan['period'] }}</small>
                                        </span>
                                        <p class="{{ VC::MB0 }}">{{ $plan['description'] }}</p>
                                        <ul class="{{ VC::LST_UNSTL_MY5 }}">
                                            @foreach($plan['features'] as $feature)
                                                <li>
                                                    <span class="theme-avatar"><i class="{{ VC::TI_CC_PLS }}"></i></span>
                                                    {{ $feature }}
                                                </li>
                                            @endforeach
                                        </ul>
                                        <div class="{{ VC::D_GR_TXCT }}">
                                            <button class="btn {{ VC::MB3 }} {{ $plan['buttonClass'] }} {{ VC::DFL }} {{ VC::JCC }} {{ VC::ALC }} mx-sm-5">
                                                {{ $plan['buttonLabel'] }}
                                                <i class="{{ VC::TI_CHV_RT_M2 }}"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>
            <section class="faq">
                <div class="{{ VC::CT }}">
                    <div class="{{ VC::RW }} justify-content-center">
                        <div class="col-xl-6 {{ VC::CM9 }} title">
                            <h2><span>Frequently Asked Questions </span></h2>
                            <p class="m-0">
                                {{ __('Use these awesome forms to login or create new account in your
                                project for free.')}}
                            </p>
                        </div>
                    </div>
                    <div class="{{ VC::RW }} justify-content-center">
                        <div class="{{ VC::CS12 }} {{ VC::CM10 }} col-xxl-8">
                            <div class="accordion accordion-flush" id="accordionExample">
                                <div class="accordion-item card">
                                    <h2 class="accordion-header" id="headingOne">
                                        <button
                                            class="accordion-button"
                                            type="button"
                                            data-bs-toggle="collapse"
                                            data-bs-target="#collapseOne"
                                            aria-expanded="true"
                                            aria-controls="collapseOne"
                                        >
                                <span class="{{ VC::DFL_AIC }}">
                                <i class="{{ VC::TI_INF_CC_PM }}"></i> How do I
                                order?
                                </span>
                                        </button>
                                    </h2>
                                    <div
                                        id="collapseOne"
                                        class="accordion-collapse collapse show"
                                        aria-labelledby="headingOne"
                                        data-bs-parent="#accordionExample"
                                    >
                                        <div class="accordion-body">
                                            <strong>This is the first item's accordion body.</strong> It
                                            is shown by default, until the collapse plugin adds the
                                            appropriate classes that we use to style each element. These
                                            classes control the overall appearance, as well as the
                                            showing and hiding via CSS transitions. You can modify any
                                            of this with custom CSS or overriding our default variables.
                                            It's also worth noting that just about any HTML can go
                                            within the <code>.accordion-body</code>, though the
                                            transition does limit overflow.
                                        </div>
                                    </div>
                                </div>
                                <div class="accordion-item card">
                                    <h2 class="accordion-header" id="headingTwo">
                                        <button
                                            class="accordion-button collapsed"
                                            type="button"
                                            data-bs-toggle="collapse"
                                            data-bs-target="#collapseTwo"
                                            aria-expanded="false"
                                            aria-controls="collapseTwo"
                                        >
                                <span class="{{ VC::DFL_AIC }}">
                                <i class="{{ VC::TI_INF_CC_PM }}"></i> How do I
                                order?
                                </span>
                                        </button>
                                    </h2>
                                    <div
                                        id="collapseTwo"
                                        class="accordion-collapse collapse"
                                        aria-labelledby="headingTwo"
                                        data-bs-parent="#accordionExample"
                                    >
                                        <div class="accordion-body">
                                            <strong>This is the second item's accordion body.</strong>
                                            It is hidden by default, until the collapse plugin adds the
                                            appropriate classes that we use to style each element. These
                                            classes control the overall appearance, as well as the
                                            showing and hiding via CSS transitions. You can modify any
                                            of this with custom CSS or overriding our default variables.
                                            It's also worth noting that just about any HTML can go
                                            within the <code>.accordion-body</code>, though the
                                            transition does limit overflow.
                                        </div>
                                    </div>
                                </div>
                                <div class="accordion-item card">
                                    <h2 class="accordion-header" id="headingThree">
                                        <button
                                            class="accordion-button collapsed"
                                            type="button"
                                            data-bs-toggle="collapse"
                                            data-bs-target="#collapseThree"
                                            aria-expanded="false"
                                            aria-controls="collapseThree"
                                        >
                                <span class="{{ VC::DFL_AIC }}">
                                <i class="{{ VC::TI_INF_CC_PM }}"></i> How do I
                                order?
                                </span>
                                        </button>
                                    </h2>
                                    <div
                                        id="collapseThree"
                                        class="accordion-collapse collapse"
                                        aria-labelledby="headingThree"
                                        data-bs-parent="#accordionExample"
                                    >
                                        <div class="accordion-body">
                                            <strong>This is the third item's accordion body.</strong> It
                                            is hidden by default, until the collapse plugin adds the
                                            appropriate classes that we use to style each element. These
                                            classes control the overall appearance, as well as the
                                            showing and hiding via CSS transitions. You can modify any
                                            of this with custom CSS or overriding our default variables.
                                            It's also worth noting that just about any HTML can go
                                            within the <code>.accordion-body</code>, though the
                                            transition does limit overflow.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
            <section class="side-feature">
                <div class="{{ VC::CT }}">
                    <div class="{{ VC::R_ALC }}">
                        <div class="{{ VC::CS3 }}">
                            <h1
                                class="mb-sm-4 {{ VC::FW600 }} wow animate__fadeInLeft"
                                data-wow-delay="0.2s"
                            >
                                {{__('ERP Nova Prestech')}}
                            </h1>
                            <h2 class="mb-sm-4 wow animate__fadeInLeft" data-wow-delay="0.4s">
                                {{__('All In One Business ERP With Project, Account, HRM, CRM')}}
                            </h2>
                            <p class="mb-sm-4 wow animate__fadeInLeft" data-wow-delay="0.6s">
                                {{ __('Use these awesome forms to login or create new account in your
                                project for free.')}}
                            </p>
                            <div class="{{ VC::MY4 }} wow animate__fadeInLeft" data-wow-delay="0.8s">
                                <a href="#" class="{{ VC::BT_PRM }}" target="_blank"
                                ><i class="{{ VC::FAS_CART }} me-2"></i>Buy now</a
                                >
                            </div>
                        </div>
                        @php
                            try {
                                $dashboardImages = [
                                    ['image' => 'dashboard.png', 'delay' => '0.2s', 'class' => ''],
                                    ['image' => 'dash-3.png', 'delay' => '0.4s', 'class' => ''],
                                    ['image' => 'dash-4.png', 'delay' => '0.6s', 'class' => ''],
                                    ['image' => 'dash-5.png', 'delay' => '0.8s', 'class' => ''],
                                    ['image' => 'dash-6.png', 'delay' => '0.3s', 'class' => 'mt-5'],
                                    ['image' => 'dash-7.png', 'delay' => '0.5s', 'class' => 'mt-5'],
                                    ['image' => 'dash-8.png', 'delay' => '0.7s', 'class' => 'mt-5'],
                                    ['image' => 'dash-9.png', 'delay' => '0.9s', 'class' => 'mt-5'],
                                ];
                            } catch (\Throwable $e) {
                                \Log::error('layouts/landing — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                            }
@endphp
                        <div class="{{ VC::CS9 }}">
                            <div class="{{ VC::RW }} feature-img-row">
                                @foreach($dashboardImages as $img)
                                    <div class="{{ VC::C3 }} {{ $img['class'] }}">
                                        <img
                                            src="{{ asset('landing/images/' . $img['image']) }}"
                                            class="{{ VC::IMG_FL }} header-img wow animate__fadeInRight"
                                            data-wow-delay="{{ $img['delay'] }}"
                                            alt="Admin"
                                        />
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </section>
            <section class="footer">
                <div class="{{ VC::CT }}">
                    <div class="row">
                        <div class="{{ VC::CL6 }} {{ VC::CS12 }}">
                            @if($colorSettings[SC::CST_DRK] && $colorSettings[SC::CST_DRK] == 'on' )
                                <img src="{{ $logo . '/' . (isset($company_logos) && !empty($company_logos) ? $company_logos : SC::CPN_LG_DK_DEF) }}"
                                    alt="logo" style="width: 150px;" >
                            @else
                                <img src="{{ $logo . '/' . (isset($company_logo) && !empty($company_logo) ? $company_logo : SC::CPN_LG_DK_DEF) }}"
                                    alt="logo" style="width: 150px;" >
                            @endif
                        </div>
                        <div class="{{ VC::CL6 }} {{ VC::CS12 }} {{ VC::TX_END }}">

                            <p class="text-body">Copyright © 2025 | Design by Prestech</p>
                        </div>
                    </div>
                </div>
            </section>
            <script defer src="{{asset('assets/js/plugins/popper.min.js')}}"></script>
            <script defer src="{{asset('assets/js/plugins/bootstrap.min.js')}}"></script>
            <script defer src="{{asset('assets/js/pages/wow.min.js')}}"></script>
            <script defer>
                let ost = 0;
                document.addEventListener("scroll", function () {
                    let cOst = document.documentElement.scrollTop;
                    if (cOst == 0) {
                        document.querySelector(".navbar").classList.add("top-nav-collapse");
                    } else if (cOst > ost) {
                        document.querySelector(".navbar").classList.add("top-nav-collapse");
                        document.querySelector(".navbar").classList.remove("default");
                    } else {
                        document.querySelector(".navbar").classList.add("default");
                        document
                            .querySelector(".navbar")
                            .classList.remove("top-nav-collapse");
                    }
                    ost = cOst;
                });
                // End [ Menu hide/show on scroll ]
                var wow = new WOW({
                    animateClass: "animate__animated", // animation css class (default is animated)
                });
                wow.init();
                var scrollSpy = new bootstrap.ScrollSpy(document.body, {
                    target: "#navbar-example",
                });
            </script>
            @if($get_cookie['enable_cookie'] == 'on')
                @includeIf(ExtendingLayoutsConstants::CKC)
            @endif
            {{-- ERP Guard & Utils Core Classes (Deferred) --}}
            <script src="{{ asset('assets/js/core/erp-guard.js') }}" defer></script>
            <script src="{{ asset('assets/js/core/erp-utils.js') }}" defer></script>
            <script src="{{ asset('assets/js/core/erp-bootstrap.min.js') }}" defer></script>
            <script>
                (window.location.hostname === '127.0.0.1' || window.location.hostname === 'localhost') && console.log(
                    'Current route:',
                    '{{ Illuminate\Support\Facades\Route::currentRouteName() ?? Illuminate\Support\Facades\Route::currentRouteAction() }}'
                );
            </script>
            @include('partials.global-error-handler')
        </body>
    </html>
