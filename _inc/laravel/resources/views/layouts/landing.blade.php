@php
	use App\Config\Constants\{ExtendingLayoutsConstants,SettingsConstants,ViewClassNamesConstants};
	use App\Models\Utility;
	use Illuminate\Support\Facades\{Log,Route};
	use Symfony\Component\Console\Output\ConsoleOutput;
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
	try {
		$data=Utility::prepareCommonViewData(null,'uploads/logo')?:[];
		$setting=$data[SettingsConstants::ENTITY]??[];
		$logo=$data[SettingsConstants::LOGO]??'';
		$company_logo=$data[SettingsConstants::CPN_LG_DK]??'';
		$company_logos=$data[SettingsConstants::CPN_LG_LT]??'';
		$colorSettings=$data[SettingsConstants::CLR_STG]??[];
		$color=$data[SettingsConstants::THM_CLR]??'';
		$mode_setting=$data[SettingsConstants::MD_LO]??'';
		$siteRtl=$data[SettingsConstants::RTL]??false;
		$meta_title=$data[SettingsConstants::MT_TTL_K]??'';
		$meta_desc=$data[SettingsConstants::MT_DESC_LONG]??'';
		$meta_image=$data[SettingsConstants::MT_IMG_K]??'';
		$meta_logo=$data[SettingsConstants::MT_LOGO]??'';
		$get_cookie=$data[SettingsConstants::CK_STG]??'';
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
    <html lang="{{ str_replace('_', '-', is_string(app()->getLocale()) ? app()->getLocale() : DatabaseConstants::DEFAULT_LANG) }}" dir="{{$siteRtl == 'on'?'rtl':''}}">
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
            @if ($colorSettings[SettingsConstants::CST_DRK] == 'on')
                <link rel="stylesheet" href="{{ asset('assets/css/style-dark.css') }}">
            @else
                <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}" id="main-style-link">
            @endif
            <link rel="stylesheet" href="{{asset('assets/css/customizer.css')}}">
            <link rel="stylesheet" href="{{asset('assets/css/landing.css')}}" />
        </head>
        <body class="{{$color}}">
            <nav class="{{ ViewClassNamesConstants::NVB_DEF_TOP }} navbar-dark">
                <div class="{{ ViewClassNamesConstants::CT }}">
                    <a class="{{ ViewClassNamesConstants::NVB_BR_TPR }}" href="">
                        <img src="{{ $logo .'/'.SettingsConstants::CPN_LG_LT_DEF }}" alt="logo" width="40%"/>
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
                    <div class="{{ ViewClassNamesConstants::NVB_CLP }}" id="navbarTogglerDemo01">
                        <ul class="{{ ViewClassNamesConstants::NVB_NAV_LG }}">
                            @foreach(['home','features','layouts','testimonial','pricing','faq'] as $key)
                                <li class="{{ ViewClassNamesConstants::NV_IT }}">
                                    <a 
                                        class="{{ $loop->first ? ViewClassNamesConstants::NV_LK.' active' : 
                                        ViewClassNamesConstants::NV_LK }}" 
                                        href="#{{ $key }}"
                                    >
                                        {{ ucfirst($key) }}
                                    </a>
                                </li>
                            @endforeach
                            <li class="nav-item">
                                @php
                                    $loginUrl = Route::has('login') ? route('login') : '#';
                                @endphp
                                <a class="btn btn-light ms-2 me-1" href="{{ $loginUrl }}">
                                    {{ __('Login') }}
                                </a>
                            </li>
                            @if(!empty($setting[SettingsConstants::ENB_SGU]) && $setting[SettingsConstants::ENB_SGU] == 'on')
                                <li class="nav-item">
                                    <a class="btn btn-light ms-2 me-1"
                                    href="{{ Route::has('register') ? route('register') : '#' }}">
                                        {{ __('Register') }}
                                    </a>
                                </li>
                            @endif
                        </ul>
                    </div>
                </div>
            </nav>
            <header id="home" class="bg-primary">
                <div class="{{ ViewClassNamesConstants::CT }}">
                    <div class="row align-items-center justify-content-between">
                        <div class="col-sm-5">
                            <h1
                                class="text-white mb-sm-4 wow animate__fadeInLeft"
                                data-wow-delay="0.2s"
                            >
                                {{__('ERP Nova Prestech')}}
                            </h1>
                            <h2
                                class="text-white mb-sm-4 wow animate__fadeInLeft"
                                data-wow-delay="0.4s"
                            >
                                {{__('All In One Business ERP With Project, Account, HRM, CRM')}}
                            </h2>
                            <p class="mb-sm-4 wow animate__fadeInLeft" data-wow-delay="0.6s">
                                Use these awesome forms to login or create new account in your
                                project for free.
                            </p>
                            <div class="my-4 wow animate__fadeInLeft" data-wow-delay="0.8s">
                                @php
                                    $liveDemoUrl = Route::has('login') ? route('login') : '#';
                                @endphp
                                <a href="{{ $liveDemoUrl }}" class="btn btn-light me-2">
                                    <i class="far fa-eye me-2"></i>Live Demo
                                </a>
                                <a href="https://codecanyon.net/item/erpgo-saas-all-in-one-business-erp-with-project-account-hrm-crm/33263426"
                                class="btn btn-outline-light"
                                target="_blank">
                                    <i class="fas fa-shopping-cart me-2"></i>Buy now
                                </a>
                            </div>
                        </div>
                        <div class="col-sm-5">
                            <img
                                src="{{asset('assets/images/front/header-mokeup.svg')}}"
                                alt="Datta Able Admin Template"
                                class="img-fluid header-img wow animate__fadeInRight"
                                data-wow-delay="0.2s"
                            />
                        </div>
                    </div>
                </div>
            </header>
            <section id="dashboard" class="theme-alt-bg dashboard-block">
                <div class="{{ ViewClassNamesConstants::CT }}">
                    <div class="row justify-content-center">
                        <div class="col-xl-6 col-md-9 title">
                            <h2><span>Happy clients use Dashboard</span> </h2>
                        </div>
                    </div>
                    @php
                        $delays = ['0.2s', '0.4s', '0.6s', '0.8s', '1s'];
                        $isDarkMode = $mode_setting[SettingsConstants::CST_DRK] && $mode_setting[SettingsConstants::CST_DRK] == 'on';
                        $logoSrc = $logo . '/' . ($isDarkMode 
                            ? (isset($company_logos) && !empty($company_logos) ? $company_logos : SettingsConstants::CPN_LG_DK_DEF)
                            : (isset($company_logo) && !empty($company_logo) ? $company_logo : SettingsConstants::CPN_LG_LT_DEF)
                        );
                    @endphp
                    <div class="row align-items-center justify-content-center mobile-screen dashboard_images">
                        @foreach($delays as $delay)
                            <div class="col-lg-2">
                                <div class="wow animate__fadeInRight mobile-widget" data-wow-delay="{{ $delay }}">
                                    <img src="{{ $logoSrc }}" alt="" class="img-fluid">
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <img
                        src="{{asset('landing/images/dashboard.png')}}"
                        alt=""
                        class="img-fluid img-dashboard wow animate__fadeInUp mt-5"  style='border-radius: 15px;'
                        data-wow-delay="0.2s"
                    />
                </div>
            </section>
            <section id="dashboard" class="theme-alt-bg dashboard-block">
                <div class="{{ ViewClassNamesConstants::CT }}">
                    <div class="row align-items-center justify-content-end mb-5">
                        <div class="col-sm-4">
                            <h1
                                class="mb-sm-4 f-w-600 wow animate__fadeInLeft"
                                data-wow-delay="0.2s"
                            >
                                {{__('ERP Nova Prestech')}}
                            </h1>
                            <h2 class="mb-sm-4 wow animate__fadeInLeft" data-wow-delay="0.4s">
                                {{__(' All In One Business ERP With Project, Account, HRM, CRM')}}
                            </h2>
                            <p class="mb-sm-4 wow animate__fadeInLeft" data-wow-delay="0.6s">
                                Use these awesome forms to login or create new account in your
                                project for free.
                            </p>
                            <div class="my-4 wow animate__fadeInLeft" data-wow-delay="0.8s">
                                <a href="#" class="btn btn-primary" target="_blank"
                                ><i class="fas fa-shopping-cart me-2"></i>Buy now</a
                                >
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <img
                                src="{{asset('landing/images/dashboard.png')}}"
                                alt="Datta Able Admin Template"
                                class="img-fluid header-img wow animate__fadeInRight"
                                data-wow-delay="0.2s"
                            />
                        </div>
                    </div>
                    <div class="row align-items-center justify-content-start">
                        <div class="col-sm-6">
                            <img
                                src="{{asset('assets/images/front/img-crm-dash-2.svg')}}"
                                alt="Datta Able Admin Template"
                                class="img-fluid header-img wow animate__fadeInLeft"
                                data-wow-delay="0.2s"
                            />
                        </div>
                        <div class="col-sm-4">
                            <h1
                                class="mb-sm-4 f-w-600 wow animate__fadeInRight"
                                data-wow-delay="0.2s"
                            >
                                {{__('ERP Nova Prestech')}}
                            </h1>
                            <h2 class="mb-sm-4 wow animate__fadeInRight" data-wow-delay="0.4s">
                                {{__('All In One Business ERP With Project, Account, HRM, CRM')}}
                            </h2>
                            <p class="mb-sm-4 wow animate__fadeInRight" data-wow-delay="0.6s">
                                Use these awesome forms to login or create new account in your
                                project for free.
                            </p>
                            <div class="my-4 wow animate__fadeInRight" data-wow-delay="0.8s">
                                <a href="#" class="btn btn-primary" target="_blank"
                                ><i class="fas fa-shopping-cart me-2"></i>Buy now</a
                                >
                            </div>
                        </div>
                    </div>
                </div>
            </section>
            <section id="feature" class="feature">
                <div class="{{ ViewClassNamesConstants::CT }}">
                    <div class="row justify-content-center">
                        <div class="col-xl-6 col-md-9 title">
                            <h2>
                                <span class="d-block mb-3">Features</span> All in one place CRM
                                system
                            </h2>
                            <p class="m-0">
                                Use these awesome forms to login or create new account in your
                                project for free.
                            </p>
                        </div>
                    </div>
                    <div class="row justify-content-center">
                        <div class="col-lg-3 col-md-6">
                            <div
                                class="card wow animate__fadeInUp"
                                data-wow-delay="0.8s"
                                style="
                            visibility: visible;
                            animation-delay: 0.2s;
                            animation-name: fadeInUp;
                        "
                            >
                                <div class="card-body">
                                    <div class="theme-avatar bg-danger">
                                        <i class="ti ti-report-money"></i>
                                    </div>
                                    <h6 class="text-muted mt-4">ABOUT</h6>
                                    <h4 class="my-3 f-w-600">Feature</h4>
                                    <p class="mb-0">
                                        Use these awesome forms to login or create new account in your
                                        project for free.
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <div
                                class="card wow animate__fadeInUp"
                                data-wow-delay="0.4s"
                                style="
                                    visibility: visible;
                                    animation-delay: 0.2s;
                                    animation-name: fadeInUp;
                                "
                            >
                                <div class="card-body">
                                    <div class="theme-avatar bg-success">
                                        <i class="ti ti-user-plus"></i>
                                    </div>
                                    <h6 class="text-muted mt-4">ABOUT</h6>
                                    <h4 class="my-3 f-w-600">Feature</h4>
                                    <p class="mb-0">
                                        Use these awesome forms to login or create new account in your
                                        project for free.
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <div
                                class="card wow animate__fadeInUp"
                                data-wow-delay="0.6s"
                                style="
                            visibility: visible;
                            animation-delay: 0.2s;
                            animation-name: fadeInUp;
                        "
                            >
                                <div class="card-body">
                                    <div class="theme-avatar bg-warning">
                                        <i class="{{ ViewClassNamesConstants::TI_USRS }}"></i>
                                    </div>
                                    <h6 class="text-muted mt-4">ABOUT</h6>
                                    <h4 class="my-3 f-w-600">Feature</h4>
                                    <p class="mb-0">
                                        Use these awesome forms to login or create new account in your
                                        project for free.
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <div
                                class="card wow animate__fadeInUp"
                                data-wow-delay="0.8s"
                                style="
                            visibility: visible;
                            animation-delay: 0.2s;
                            animation-name: fadeInUp;
                        "
                            >
                                <div class="card-body">
                                    <div class="theme-avatar bg-danger">
                                        <i class="ti ti-report-money"></i>
                                    </div>
                                    <h6 class="text-muted mt-4">ABOUT</h6>
                                    <h4 class="my-3 f-w-600">Feature</h4>
                                    <p class="mb-0">
                                        Use these awesome forms to login or create new account in your
                                        project for free.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="text-center pt-sm-5 feature-mobile-screen">
                        <button class="btn px-sm-5 btn-primary me-sm-3 ">Buy Now</button>
                        <button class="btn px-sm-5 btn-outline-primary">
                            View documentation
                        </button>
                    </div>
                </div>
            </section>
            <section class="">
                <div class="{{ ViewClassNamesConstants::CT }}">
                    <div class="row align-items-center justify-content-end mb-5">
                        <div class="col-sm-4">
                            <h1
                                class="mb-sm-4 f-w-600 wow animate__fadeInLeft"
                                data-wow-delay="0.2s"
                            >
                                {{__('ERP Nova Prestech')}}
                            </h1>
                            <h2 class="mb-sm-4 wow animate__fadeInLeft" data-wow-delay="0.4s">
                                {{__('All In One Business ERP With Project, Account, HRM, CRM')}}
                            </h2>
                            <p class="mb-sm-4 wow animate__fadeInLeft" data-wow-delay="0.6s">
                                Use these awesome forms to login or create new account in your
                                project for free.
                            </p>
                            <div class="my-4 wow animate__fadeInLeft" data-wow-delay="0.8s">
                                <a href="#" class="btn btn-primary" target="_blank"
                                ><i class="fas fa-shopping-cart me-2"></i>Buy now</a
                                >
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <img
                                src="{{asset('landing/images/dash-2.svg')}}"
                                alt="Datta Able Admin Template"
                                class="img-fluid header-img wow animate__fadeInRight"
                                data-wow-delay="0.2s"
                            />
                        </div>
                    </div>
                    <div class="row align-items-center justify-content-start">
                        <div class="col-sm-6">
                            <img
                                src="{{asset('assets/images/front/img-crm-dash-4.svg')}}"
                                alt="Datta Able Admin Template"
                                class="img-fluid header-img wow animate__fadeInLeft"
                                data-wow-delay="0.2s"
                            />
                        </div>
                        <div class="col-sm-4">
                            <h1
                                class="mb-sm-4 f-w-600 wow animate__fadeInRight"
                                data-wow-delay="0.2s"
                            >
                                {{__('ERP Nova Prestech')}}
                            </h1>
                            <h2 class="mb-sm-4 wow animate__fadeInRight" data-wow-delay="0.4s">
                                {{__('All In One Business ERP With Project, Account, HRM, CRM')}}
                            </h2>
                            <p class="mb-sm-4 wow animate__fadeInRight" data-wow-delay="0.6s">
                                Use these awesome forms to login or create new account in your
                                project for free.
                            </p>
                            <div class="my-4 wow animate__fadeInRight" data-wow-delay="0.8s">
                                <a href="#" class="btn btn-primary" target="_blank"
                                ><i class="fas fa-shopping-cart me-2"></i>Buy now</a
                                >
                            </div>
                        </div>
                    </div>
                </div>
            </section>
            <section id="price" class="price-section">
                <div class="{{ ViewClassNamesConstants::CT }}">
                    <div class="row justify-content-center">
                        <div class="col-xl-6 col-md-9 title">
                            <h2>
                                <span class="d-block mb-3">Price</span> All in one place CRM
                                system
                            </h2>
                            <p class="m-0">
                                Use these awesome forms to login or create new account in your
                                project for free.
                            </p>
                        </div>
                    </div>
                    @php
                        $pricingPlans = [
                            [
                                'name'           => 'STARTER',
                                'badgeClass'     => ViewClassNamesConstants::BG_P,
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
                                'badgeClass'     => ViewClassNamesConstants::BG_P,
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
                    @endphp
                    <div class="row justify-content-center">
                        @foreach($pricingPlans as $plan)
                            <div class="col-lg-4 col-md-6">
                                <div class="card price-card {{ $plan['priceCardClass'] }} wow animate__fadeInUp" data-wow-delay="{{ $plan['delay'] }}" style="visibility: visible; animation-delay: {{ $plan['delay'] }}; animation-name: fadeInUp;">
                                    <div class="card-body">
                                        <span class="price-badge {{ $plan['badgeClass'] }}">{{ $plan['name'] }}</span>
                                        <span class="mb-4 f-w-600 p-price">${{ $plan['price'] }}
                                            <small class="text-sm">{{ $plan['period'] }}</small>
                                        </span>
                                        <p class="mb-0">{{ $plan['description'] }}</p>
                                        <ul class="list-unstyled my-5">
                                            @foreach($plan['features'] as $feature)
                                                <li>
                                                    <span class="theme-avatar"><i class="{{ ViewClassNamesConstants::TI_CC_PLS }}"></i></span>
                                                    {{ $feature }}
                                                </li>
                                            @endforeach
                                        </ul>
                                        <div class="d-grid text-center">
                                            <button class="btn mb-3 {{ $plan['buttonClass'] }} d-flex justify-content-center align-items-center mx-sm-5">
                                                {{ $plan['buttonLabel'] }}
                                                <i class="{{ ViewClassNamesConstants::TI_CHV_RT_M2 }}"></i>
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
                <div class="{{ ViewClassNamesConstants::CT }}">
                    <div class="row justify-content-center">
                        <div class="col-xl-6 col-md-9 title">
                            <h2><span>Frequently Asked Questions </span></h2>
                            <p class="m-0">
                                Use these awesome forms to login or create new account in your
                                project for free.
                            </p>
                        </div>
                    </div>
                    <div class="row justify-content-center">
                        <div class="col-sm-12 col-md-10 col-xxl-8">
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
                                <span class="d-flex align-items-center">
                                <i class="ti ti-info-circle text-primary"></i> How do I
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
                                <span class="d-flex align-items-center">
                                <i class="ti ti-info-circle text-primary"></i> How do I
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
                                <span class="d-flex align-items-center">
                                <i class="ti ti-info-circle text-primary"></i> How do I
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
                <div class="{{ ViewClassNamesConstants::CT }}">
                    <div class="row align-items-center">
                        <div class="col-sm-3">
                            <h1
                                class="mb-sm-4 f-w-600 wow animate__fadeInLeft"
                                data-wow-delay="0.2s"
                            >
                                {{__('ERP Nova Prestech')}}
                            </h1>
                            <h2 class="mb-sm-4 wow animate__fadeInLeft" data-wow-delay="0.4s">
                                {{__('All In One Business ERP With Project, Account, HRM, CRM')}}
                            </h2>
                            <p class="mb-sm-4 wow animate__fadeInLeft" data-wow-delay="0.6s">
                                Use these awesome forms to login or create new account in your
                                project for free.
                            </p>
                            <div class="my-4 wow animate__fadeInLeft" data-wow-delay="0.8s">
                                <a href="#" class="btn btn-primary" target="_blank"
                                ><i class="fas fa-shopping-cart me-2"></i>Buy now</a
                                >
                            </div>
                        </div>
                        @php
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
                        @endphp
                        <div class="col-sm-9">
                            <div class="row feature-img-row">
                                @foreach($dashboardImages as $img)
                                    <div class="col-3 {{ $img['class'] }}">
                                        <img
                                            src="{{ asset('landing/images/' . $img['image']) }}"
                                            class="img-fluid header-img wow animate__fadeInRight"
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
                <div class="{{ ViewClassNamesConstants::CT }}">
                    <div class="row">
                        <div class="col-lg-6 col-sm-12">
                            @if($colorSettings[SettingsConstants::CST_DRK] && $colorSettings[SettingsConstants::CST_DRK] == 'on' )
                                <img src="{{ $logo . '/' . (isset($company_logos) && !empty($company_logos) ? $company_logos : SettingsConstants::CPN_LG_DK_DEF) }}"
                                    alt="logo" style="width: 150px;" >
                            @else
                                <img src="{{ $logo . '/' . (isset($company_logo) && !empty($company_logo) ? $company_logo : SettingsConstants::CPN_LG_DK_DEF) }}"
                                    alt="logo" style="width: 150px;" >
                            @endif
                        </div>
                        <div class="col-lg-6 col-sm-12 text-end">

                            <p class="text-body">Copyright © 2023 | Design By ERPGo</p>
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
            <script>
                console.log(
                    'Current route:',
                    '{{ Illuminate\Support\Facades\Route::currentRouteName() ?? Illuminate\Support\Facades\Route::currentRouteAction() }}'
                );
            </script>
        </body>
    </html>