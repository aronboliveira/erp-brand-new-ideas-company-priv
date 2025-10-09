@php
	use App\Config\Constants\{DatabaseConstants,ExtendingLayoutsConstants,SettingsConstants,ViewClassNamesConstants};
	use App\Models\Utility;
	use Illuminate\Support\Facades\{Log, Route};
	use Modules\LandingPage\Config\Constants\{RoutesResourcesConstants as R,SettingsConstants as LandingPageSettingsConstants};
    use Nwidart\Modules\Facades\Module;
	use Symfony\Component\Console\Output\ConsoleOutput;
    
	$lpSettings??=[];
	$data??=[];
	$logo??='';
	$sup_logo??='';
	$meta_title??='';
	$meta_desc??='';
	$meta_image??='';
	$meta_logo??='';
	$get_cookie??='';
	$colorSettings??=[];
	$siteRtl??=false;
	$color??='';
	try {
		$lpSettings=\Modules\LandingPage\Entities\LandingPageSetting::settings()?:[];
		$data=Utility::prepareCommonViewData(null,'uploads/landing_page_image')?:[];
		$logo=$data[SettingsConstants::LOGO]??'';
		$sup_logo=$data[SettingsConstants::SC_LOGO]??'';
		$meta_title=$data[SettingsConstants::MT_TTL_K]??'';
		$meta_desc=$data[SettingsConstants::MT_DESC_LONG]??'';
		$meta_image=$data[SettingsConstants::MT_IMG_K]??'';
		$meta_logo=$data[SettingsConstants::MT_LOGO]??'';
		$get_cookie=$data[SettingsConstants::CK_STG]??'';
		$colorSettings=$data[SettingsConstants::CLR_STG]??[];
		$siteRtl=$data[SettingsConstants::RTL]??false;
		$color=$data[SettingsConstants::THM_CLR]??'';
	} catch (\Error $e) {
		Log::error(
			'Error fetching landing page and view data',
			[
				'exception_class'=>get_class($e),
				'message'=>$e->getMessage(),
				'file'=>$e->getFile(),
				'line'=>$e->getLine()
			]
		);
	} catch (\Exception $e) {
		Log::error(
			'Exception fetching landing page and view data',
			[
				'exception_class'=>get_class($e),
				'message'=>$e->getMessage(),
				'file'=>$e->getFile(),
				'line'=>$e->getLine()
			]
		);
	} catch (\Throwable $e) {
		Log::error(
			'Throwable fetching landing page and view data',
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
<html lang="{{ str_replace('_', '-', is_string(app()->getLocale()) ? app()->getLocale() : DatabaseConstants::DEFAULT_LANG) }}" dir="{{ $siteRtl == 'on' ? 'rtl' : '' }}">
    <head>
        <title>{{ env('APP_NAME') }}</title>
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
        <link rel="stylesheet" href=" {{ Module::asset('LandingPage:fonts/tabler-icons.min.css') }}" />
        <link rel="stylesheet" href=" {{ Module::asset('LandingPage:fonts/feather.css') }}" />
        <link rel="stylesheet" href="  {{ Module::asset('LandingPage:fonts/fontawesome.css') }}" />
        <link rel="stylesheet" href="{{ Module::asset('LandingPage:fonts/material.css') }}" />
        @if ($siteRtl == 'on')
            <link rel="stylesheet" href="{{ asset('assets/css/style-rtl.css') }}">
        @endif
        @if ($colorSettings[SettingsConstants::CST_DRK] == 'on')
            <link rel="stylesheet" href="{{ asset('assets/css/style-dark.css') }}">
        @else
            <link rel="stylesheet" href="{{ Module::asset('LandingPage:css/style.css') }}"
                id="main-style-link">
        @endif
        <link rel="stylesheet" href=" {{ Module::asset('LandingPage:css/customizer.css') }}" />
        <link rel="stylesheet" href=" {{ Module::asset('LandingPage:css/landing-page.css') }}" />
        <link rel="stylesheet" href=" {{ Module::asset('LandingPage:css/custom.css') }}" />
    </head>
    @if ($colorSettings[SettingsConstants::CST_DRK] == 'on')
        <body class="{{ $color }} landing-dark">
    @else
        <body class="{{ $color }}">
    @endif
                <header class="main-header">
                    @if (!empty($lpSettings[LandingPageSettingsConstants::TB_STT_K]) && $lpSettings[LandingPageSettingsConstants::TB_STT_K] === 'on')
                        <div class="announcement bg-dark text-center p-2">
                            <p class="mb-0">{!! $lpSettings[LandingPageSettingsConstants::TB_NTF_MSG_K] !!}</p>
                        </div>
                    @endif
                    @if (!empty($lpSettings[LandingPageSettingsConstants::MB_STT_K]) && $lpSettings[LandingPageSettingsConstants::MB_STT_K] === 'on')
                        <div class="{{ ViewClassNamesConstants::CT }}">
                            <nav class="{{ ViewClassNamesConstants::NVB_DEF_TOP }}">
                                <div class="header-left">
                                    <a class="{{ ViewClassNamesConstants::NVB_BR_TPR }}" href="#">
                                        <img src="{{ $logo . '/' . $lpSettings['site_logo'] }}" alt="logo">
                                    </a>
                                </div>
                                <div class="{{ ViewClassNamesConstants::NVB_CLP }}" id="navbarTogglerDemo01">
                                    <ul class="{{ ViewClassNamesConstants::NVB_NAV }}">
                                        <li class="nav-item">
                                            <a class="nav-link active" href="#home">{{ $lpSettings[LandingPageSettingsConstants::HM_TTL_K] }}</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" href="#features">{{ $lpSettings[LandingPageSettingsConstants::FT_TTL_K] }}</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" href="#plan">{{ $lpSettings[LandingPageSettingsConstants::PN_TTL_K] }}</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" href="#faq">{{ $lpSettings[LandingPageSettingsConstants::FAQ_TTL_K] }}</a>
                                        </li>

                                        @if (is_array(json_decode($lpSettings[LandingPageSettingsConstants::MB_PG_K])) || is_object(json_decode($lpSettings[LandingPageSettingsConstants::MB_PG_K])))
                                            @foreach (json_decode($lpSettings[LandingPageSettingsConstants::MB_PG_K]) as $key => $value)
                                                @if ($value->header == 'on' && $value->template_name == 'page_content')
                                                    <li class="nav-item">
                                                        @php
                                                            $slug??='';
                                                            $hasRoute??=false;
                                                            $pageName??='';
                                                            $pageUrl??='';
                                                            try {
                                                                $slug=data_get($value,LandingPageSettingsConstants::PG_SLG,'');
                                                                $hasRoute=Route::has('custom.page')&&$slug!=='';
                                                                $pageName=data_get($value,LandingPageSettingsConstants::MB_PG_NM,'');
                                                                $pageUrl=$hasRoute?route('custom.page',$slug):'#';
                                                            } catch (\Error $e) {
                                                                Log::error(
                                                                    'Error processing menubar page link',
                                                                    [
                                                                        'exception_class'=>get_class($e),
                                                                        'message'=>$e->getMessage(),
                                                                        'file'=>$e->getFile(),
                                                                        'line'=>$e->getLine(),
                                                                        'value'=>$value
                                                                    ]
                                                                );
                                                            } catch (\Exception $e) {
                                                                Log::error(
                                                                    'Exception processing menubar page link',
                                                                    [
                                                                        'exception_class'=>get_class($e),
                                                                        'message'=>$e->getMessage(),
                                                                        'file'=>$e->getFile(),
                                                                        'line'=>$e->getLine(),
                                                                        'value'=>$value
                                                                    ]
                                                                );
                                                            } catch (\Throwable $e) {
                                                                Log::error(
                                                                    'Throwable processing menubar page link',
                                                                    [
                                                                        'exception_class'=>get_class($e),
                                                                        'message'=>$e->getMessage(),
                                                                        'file'=>$e->getFile(),
                                                                        'line'=>$e->getLine(),
                                                                        'value'=>$value
                                                                    ]
                                                                );
                                                            }
                                                        @endphp
                                                        <a class="nav-link"
                                                        href="{{ $pageUrl }}">
                                                            {!! $pageName !!}
                                                        </a>
                                                    </li>
                                                @elseif($value->header == 'on')
                                                    <li class="nav-item">
                                                        <a class="nav-link"
                                                            href="{{ $value->page_url }}">{{ $value[LandingPageSettingsConstants::MB_PG_NM] }}</a>
                                                    </li>
                                                @endif
                                            @endforeach
                                        @endif

                                    </ul>
                                    <button class="{{ ViewClassNamesConstants::NVB_TG_P }}" type="button" data-bs-toggle="collapse"
                                        data-bs-target="#navbarTogglerDemo01" aria-controls="navbarTogglerDemo01" aria-expanded="false"
                                        aria-label="Toggle navigation">
                                        <span class="{{ ViewClassNamesConstants::NVB_TG_IC }}"></span>
                                    </button>
                                </div>
                                <div class="ms-auto d-flex justify-content-end gap-2">
                                    <a href="{{ Route::has('login') ? route('login') : '#' }}"
                                        class="btn btn-outline-dark rounded"
                                        {{ Route::has('login') ? '' : 'aria-disabled="true"' }}>
                                        <span class="hide-mob me-2">{{ __('Login') }}</span>
                                        <i data-feather="log-in"></i>
                                    </a>
                                    <a href="{{ Route::has('register') ? route('register') : '#' }}"
                                        class="btn btn-outline-dark rounded"
                                        {{ Route::has('register') ? '' : 'aria-disabled="true"' }}>
                                        <span class="hide-mob me-2">{{ __('Register') }}</span>
                                        <i data-feather="user-check"></i>
                                    </a>
                                    <button class="{{ ViewClassNamesConstants::NVB_TG }}" type="button" data-bs-toggle="collapse"
                                        data-bs-target="#navbarTogglerDemo01" aria-controls="navbarTogglerDemo01"
                                        aria-expanded="false" aria-label="Toggle navigation">
                                        <span class="{{ ViewClassNamesConstants::NVB_TG_IC }}"></span>
                                    </button>
                                </div>
                            </nav>
                        </div>
                    @endif

                </header>
                <!-- [ Header ] End -->
                <!-- [ Banner ] start -->
                @if ($lpSettings[LandingPageSettingsConstants::HM_STT_K] == 'on')
                    <section class="main-banner bg-primary" id="home">
                        <div class="container-offset">
                            <div class="row gy-3 g-0 align-items-center">
                                <div class="col-xxl-4 col-md-6">
                                    <span class="badge py-2 px-3 bg-white text-dark rounded-pill fw-bold mb-3">
                                        {{ $lpSettings[LandingPageSettingsConstants::HM_OFF_TXT_K] }}
                                    </span>
                                    <h1 class="mb-3">
                                        {{ $lpSettings[LandingPageSettingsConstants::HM_HDG_K] }}
                                    </h1>
                                    <h6 class="mb-0">{{ $lpSettings[LandingPageSettingsConstants::HM_DESC_K] }}</h6>
                                    <div class="d-flex gap-3 mt-4 banner-btn">
                                        @if ($lpSettings[LandingPageSettingsConstants::HM_DEMO_LNK_K])
                                            <a href="{{ $lpSettings[LandingPageSettingsConstants::HM_DEMO_LNK_K] }}" class="btn btn-outline-dark">
                                                {{ __('Live Demo') }}
                                                <i data-feather="play-circle" class="ms-2"></i>
                                            </a>
                                        @endif
                                        @if ($lpSettings[LandingPageSettingsConstants::HM_BUY_LNK_K])
                                            <a href="{{ $lpSettings[LandingPageSettingsConstants::HM_BUY_LNK_K] }}"
                                                class="btn btn-outline-dark">{{ __('Buy Now') }} <i data-feather="lock"
                                                    class="ms-2"></i></a>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-xxl-8 col-md-6">
                                    <div class="dash-preview">
                                        <img class="img-fluid preview-img" src="{{ $logo . '/' . $lpSettings[LandingPageSettingsConstants::HM_BNR_K] }}"
                                            alt="">
                                    </div>
                                </div>
                            </div>
                        </div>
                        {{-- <div class="{{ ViewClassNamesConstants::CT }}">
                                <div class="row g-0 gy-2 mt-4 align-items-center">
                                    <div class="col-xxl-3">
                                        <p class="mb-0">{{__('Trusted by')}} <b class="fw-bold">{{ $lpSettings[LandingPageSettingsConstants::HM_TRST_BY_K] }}</b></p>
                                    </div>
                                    <div class="col-xxl-9">
                                        <div class="row gy-3 row-cols-9">
                                            <div class="col-auto">
                                                <img src="{{ $logo.'/'. $lpSettings[LandingPageSettingsConstants::HM_LGO_K] }}" alt="" class="img-fluid"
                                                    style="width: 130px;">
                                            </div>
                                            <div class="col-auto">
                                                <img src="{{ $logo.'/'. $lpSettings[LandingPageSettingsConstants::HM_LGO_K] }}" alt="" class="img-fluid"
                                                    style="width: 130px;">
                                            </div>
                                            <div class="col-auto">
                                                <img src="{{ $logo.'/'. $lpSettings[LandingPageSettingsConstants::HM_LGO_K] }}" alt="" class="img-fluid"
                                                    style="width: 130px;">
                                            </div>
                                            <div class="col-auto">
                                                <img src="{{ $logo.'/'. $lpSettings[LandingPageSettingsConstants::HM_LGO_K] }}" alt="" class="img-fluid"
                                                    style="width: 130px;">
                                            </div>
                                            <div class="col-auto">
                                                <img src="{{ $logo.'/'. $lpSettings[LandingPageSettingsConstants::HM_LGO_K] }}" alt="" class="img-fluid"
                                                    style="width: 130px;">
                                            </div>
                                            <div class="col-auto">
                                                <img src="{{ $logo.'/'. $lpSettings[LandingPageSettingsConstants::HM_LGO_K] }}" alt="" class="img-fluid"
                                                    style="width: 130px;">
                                            </div>
                                            <div class="col-auto">
                                                <img src="{{ $logo.'/'. $lpSettings[LandingPageSettingsConstants::HM_LGO_K] }}" alt="" class="img-fluid"
                                                    style="width: 130px;">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div> --}}

                    </section>
                @endif
                <!-- [ Banner ] start -->
                <!-- [ features ] start -->
                @if ($lpSettings['feature_status'] == 'on')
                    <section class="features-section section-gap bg-dark" id="features">
                        <div class="{{ ViewClassNamesConstants::CT }}">
                            <div class="row gy-3">
                                <div class="col-xxl-4">
                                    <span class="d-block mb-2 text-uppercase">{{ $lpSettings[LandingPageSettingsConstants::FT_TTL_K] }}</span>
                                    <div class="title mb-4">
                                        <h2><b class="fw-bold">{!! $lpSettings['feature_heading'] !!}</b></h2>
                                    </div>
                                    <p class="mb-3">{!! $lpSettings['feature_description'] !!}</p>
                                    @if ($lpSettings['feature_buy_now_link'])
                                        <a href="{{ $lpSettings['feature_buy_now_link'] }}"
                                            class="btn btn-primary rounded-pill d-inline-flex align-items-center">{{ __('Buy Now') }}
                                            <i data-feather="lock" class="ms-2"></i></a>
                                    @endif
                                </div>
                                <div class="col-xxl-8">
                                    <div class="row">
                                        @if (is_array(json_decode($lpSettings['feature_of_features'], true)) ||
                                                is_object(json_decode($lpSettings['feature_of_features'], true)))
                                            @foreach (json_decode($lpSettings['feature_of_features'], true) as $key => $value)
                                                <div class="col-lg-4 col-sm-6 d-flex">
                                                    <div class="card {{ $key == 0 ? 'bg-primary' : '' }}">
                                                        <div class="card-body">
                                                            <span class="theme-avatar avatar avatar-xl mb-4">
                                                                <img src="{{ $logo . '/' . $value['feature_logo'] }}" alt="">
                                                            </span>
                                                            <h3 class="mb-3 {{ $key == 0 ? '' : 'text-white' }}">
                                                                {!! $value['feature_heading'] !!}</h3>
                                                            <p class=" f-w-600 mb-0 {{ $key == 0 ? 'text-body' : '' }}">
                                                                {!! $value['feature_description'] !!}</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        @endif
                                    </div>
                                </div>
                                <div class="mt-5">
                                    <div class="title text-center mb-4">
                                        <span class="d-block mb-2 text-uppercase">{{ $lpSettings[LandingPageSettingsConstants::FT_TTL_K] }}</span>
                                        <h2 class="mb-4">{!! $lpSettings['highlight_feature_heading'] !!}</h2>
                                        <p>{!! $lpSettings['highlight_feature_description'] !!}</p>
                                    </div>
                                    <div class="features-preview">
                                        <img class="img-fluid m-auto d-block"
                                            src="{{ $logo . '/' . $lpSettings['highlight_feature_image'] }}" alt="">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>
                @endif
                <!-- [ features ] start -->
                <!-- [ element ] start -->
                @if ($lpSettings['feature_status'] == 'on')
                    <section class="element-section  section-gap ">
                        <div class="{{ ViewClassNamesConstants::CT }}">
                            @if (is_array(json_decode($lpSettings['other_features'], true)) ||
                                    is_object(json_decode($lpSettings['other_features'], true)))
                                @foreach (json_decode($lpSettings['other_features'], true) as $key => $value)
                                    @if ($key % 2 == 0)
                                        <div class="row align-items-center justify-content-center mb-4">
                                            <div class="col-lg-4 col-md-6">
                                                <div class="title mb-4">
                                                    <span class="d-block fw-bold mb-2 text-uppercase">{{ __('Features') }}</span>
                                                    <h2>
                                                        {!! $value['other_features_heading'] !!}
                                                    </h2>
                                                </div>
                                                <p class="mb-3">{!! $value['other_featured_description'] !!}</p>
                                                <a href="{{ $value['other_feature_buy_now_link'] }}"
                                                    class="btn btn-primary rounded-pill d-inline-flex align-items-center">{{ __('Buy Now ') }}
                                                    <i data-feather="lock" class="ms-2"></i></a>
                                            </div>
                                            <div class="col-lg-7 col-md-6 res-img">
                                                <div class="img-wrapper">
                                                    <img src="{{ $logo . '/' . $value['other_features_image'] }}" alt=""
                                                        class="img-fluid header-img">
                                                </div>
                                            </div>
                                        </div>
                                    @else
                                        <div class="row align-items-center justify-content-center mb-4">
                                            <div class="col-lg-7 col-md-6">
                                                <div class="img-wrapper">
                                                    <img src="{{ $logo . '/' . $value['other_features_image'] }}" alt=""
                                                        class="img-fluid header-img">
                                                </div>
                                            </div>
                                            <div class="col-lg-4  col-md-6">
                                                <div class="title mb-4">
                                                    <span class="d-block fw-bold mb-2 text-uppercase">{{ __('Features') }}</span>
                                                    <h2>
                                                        {!! $value['other_features_heading'] !!}
                                                    </h2>
                                                </div>
                                                <p class="mb-3">{!! $value['other_featured_description'] !!}</p>
                                                <a href="{{ $value['other_feature_buy_now_link'] }}"
                                                    class="btn btn-primary rounded-pill d-inline-flex align-items-center">{{ __('Buy Now ') }}
                                                    <i data-feather="lock" class="ms-2"></i></a>
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            @endif

                        </div>
                    </section>
                @endif
                <!-- [ element ] end -->
                <!-- [ element ] start -->
                @if ($lpSettings['discover_status'] == 'on')
                    <section class="bg-dark section-gap">
                        <div class="{{ ViewClassNamesConstants::CT }}">
                            <div class="row mb-2 justify-content-center">
                                <div class="col-xxl-6">
                                    <div class="title text-center mb-4">
                                        <span class="d-block mb-2 text-uppercase">{{ __('DISCOVER') }}</span>
                                        <h2 class="mb-4">{!! $lpSettings['discover_heading'] !!}</h2>
                                        <p>{!! $lpSettings['discover_description'] !!}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                @if (is_array(json_decode($lpSettings['discover_of_features'], true)) ||
                                        is_object(json_decode($lpSettings['discover_of_features'], true)))
                                    @foreach (json_decode($lpSettings['discover_of_features'], true) as $key => $value)
                                        <div class="col-xxl-3 col-sm-6 col-lg-4 ">
                                            <div class="card   border {{ $key == 1 ? 'bg-primary' : 'bg-transparent' }}">
                                                <div class="card-body text-center">
                                                    <span class="theme-avatar avatar avatar-xl mx-auto mb-4">
                                                        <img src="{{ $logo . '/' . $value['discover_logo'] }}" alt="">
                                                    </span>
                                                    <h3 class="mb-3 {{ $key == 1 ? '' : 'text-white' }} ">{!! $value['discover_heading'] !!}
                                                    </h3>
                                                    <p class="{{ $key == 1 ? 'text-body' : '' }}">
                                                        {!! $value['discover_description'] !!}
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                @endif

                            </div>
                            <div class="d-flex flex-column justify-content-center flex-sm-row gap-3 mt-3">
                                @if ($lpSettings['discover_live_demo_link'])
                                    <a href="{{ $lpSettings['discover_live_demo_link'] }}"
                                        class="btn btn-outline-light rounded-pill">{{ __('Live Demo') }}
                                        <i data-feather="play-circle" class="ms-2"></i>
                                    </a>
                                @endif

                                @if ($lpSettings['discover_buy_now_link'])
                                    <a href="{{ $lpSettings['discover_buy_now_link'] }}"
                                        class="btn btn-primary rounded-pill">{{ __('Buy Now ') }} <i data-feather="lock"
                                            class="ms-2"></i> </a>
                                @endif
                            </div>
                        </div>
                    </section>
                @endif
                <!-- [ element ] end -->
                <!-- [ Screenshots ] start -->
                @if ($lpSettings['screenshots_status'] == 'on')
                    <section class="screenshots section-gap">
                        <div class="{{ ViewClassNamesConstants::CT }}">
                            <div class="row mb-2 justify-content-center">
                                <div class="col-xxl-6">
                                    <div class="title text-center mb-4">
                                        <span class="d-block mb-2 fw-bold text-uppercase">{{ __('SCREENSHOTS') }}</span>
                                        <h2 class="mb-4">{!! $lpSettings['screenshots_heading'] !!}</h2>
                                        <p>{!! $lpSettings['screenshots_description'] !!}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="row gy-4 gx-4">
                                @if (is_array(json_decode($lpSettings['screenshots'], true)) || is_object(json_decode($lpSettings['screenshots'], true)))
                                    @foreach (json_decode($lpSettings['screenshots'], true) as $value)
                                        <div class="col-md-4 col-sm-6">
                                            <div class="screenshot-card">
                                                <div class="img-wrapper">
                                                    <img src="{{ $logo . '/' . $value['screenshots'] }}"
                                                        class="img-fluid header-img mb-4 shadow-sm" alt="">
                                                </div>
                                                <h5 class="mb-0">{!! $value['screenshots_heading'] !!}</h5>
                                            </div>
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                        </div>
                    </section>
                @endif
                <!-- [ Screenshots ] start -->
                <!-- [ subscription ] start -->
                @if ($lpSettings['plan_status'])
                    <section class="subscription bg-primary section-gap" id="plan">
                        <div class="{{ ViewClassNamesConstants::CT }}">
                            <div class="row mb-2 justify-content-center">
                                <div class="col-xxl-6">
                                    <div class="title text-center mb-4">
                                        <span class="d-block mb-2 fw-bold text-uppercase">{{ __('PLAN') }}</span>
                                        <h2 class="mb-4">{!! $lpSettings['plan_heading'] !!}</h2>
                                        <p>{!! $lpSettings['plan_description'] !!}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="row justify-content-center">
                                @php
                                    use App\Models\{Plan,Utility};
                                    $collection??=collect([]);
                                    $admin_payment_setting??=[];
                                    try {
                                        $collection=Plan::orderBy('price','ASC')->get();
                                        $admin_payment_setting=Utility::getAdminPaymentSetting()?:[];
                                    } catch (\Error $e) {
                                        Log::error(
                                            'Error fetching plans or payment settings',
                                            [
                                                'exception_class'=>get_class($e),
                                                'message'=>$e->getMessage(),
                                                'file'=>$e->getFile(),
                                                'line'=>$e->getLine()
                                            ]
                                        );
                                    } catch (\Exception $e) {
                                        Log::error(
                                            'Exception fetching plans or payment settings',
                                            [
                                                'exception_class'=>get_class($e),
                                                'message'=>$e->getMessage(),
                                                'file'=>$e->getFile(),
                                                'line'=>$e->getLine()
                                            ]
                                        );
                                    } catch (\Throwable $e) {
                                        Log::error(
                                            'Throwable fetching plans or payment settings',
                                            [
                                                'exception_class'=>get_class($e),
                                                'message'=>$e->getMessage(),
                                                'file'=>$e->getFile(),
                                                'line'=>$e->getLine()
                                            ]
                                        );
                                    }
                                @endphp
                                @foreach ($collection as $key => $value)
                                    <div class="col-xxl-3 col-lg-4 col-md-6">
                                        <div class="card price-card shadow-none {{ $key == 2 ? 'bg-dark' : '' }}">
                                            @php
                                                use App\Config\Constants\PlansConstants;
                                                $features??=[];
                                                $name??='';
                                                $price??=0;
                                                $duration??='';
                                                $description??='';
                                                $icon??='';
                                                $currency??='';
                                                try {
                                                    $features=[
                                                        [
                                                            'key'=>PlansConstants::COL_MAX_U,
                                                            'label'=>__('User'),
                                                            'type'=>'quota'
                                                        ],
                                                        [
                                                            'key'=>PlansConstants::COL_MAX_CR,
                                                            'label'=>__('Customer'),
                                                            'type'=>'quota'
                                                        ],
                                                        [
                                                            'key'=>PlansConstants::COL_MAX_V,
                                                            'label'=>__('Vendor'),
                                                            'type'=>'quota'
                                                        ],
                                                        [
                                                            'key'=>PlansConstants::COL_MAX_CL,
                                                            'label'=>__('Clients'),
                                                            'type'=>'quota'
                                                        ],
                                                        [
                                                            'key'=>PlansConstants::COL_ACC,
                                                            'label'=>__('Account'),
                                                            'type'=>'toggle'
                                                        ],
                                                        [
                                                            'key'=>PlansConstants::COL_CRM,
                                                            'label'=>__('CRM'),
                                                            'type'=>'toggle'
                                                        ],
                                                        [
                                                            'key'=>PlansConstants::COL_HRM,
                                                            'label'=>__('HRM'),
                                                            'type'=>'toggle'
                                                        ],
                                                        [
                                                            'key'=>PlansConstants::COL_PJ,
                                                            'label'=>__('Project'),
                                                            'type'=>'toggle'
                                                        ],
                                                        [
                                                            'key'=>PlansConstants::COL_POS,
                                                            'label'=>__('POS'),
                                                            'type'=>'toggle'
                                                        ],
                                                        [
                                                            'key'=>PlansConstants::COL_GPT,
                                                            'label'=>__('ChatGPT'),
                                                            'type'=>'toggle'
                                                        ]
                                                    ];
                                                    $name=$value->{PlansConstants::COL_NM}??'';
                                                    $price=intval($value->{PlansConstants::COL_PC}??0);
                                                    $duration=$value->{PlansConstants::COL_DUR}??'';
                                                    $description=$value->{PlansConstants::COL_DESC}??'';
                                                    $icon='<span class="theme-avatar"><i class="'
                                                        .ViewClassNamesConstants::TI_CC_PLS
                                                        .'"></i></span>';
                                                    $currency=$admin_payment_setting['currency_symbol']??'$';
                                                } catch (\Error $e) {
                                                    Log::error(
                                                        'Error processing plan data',
                                                        [
                                                            'exception_class'=>get_class($e),
                                                            'message'=>$e->getMessage(),
                                                            'file'=>$e->getFile(),
                                                            'line'=>$e->getLine()
                                                        ]
                                                    );
                                                } catch (\Exception $e) {
                                                    Log::error(
                                                        'Exception processing plan data',
                                                        [
                                                            'exception_class'=>get_class($e),
                                                            'message'=>$e->getMessage(),
                                                            'file'=>$e->getFile(),
                                                            'line'=>$e->getLine()
                                                        ]
                                                    );
                                                } catch (\Throwable $e) {
                                                    Log::error(
                                                        'Throwable processing plan data',
                                                        [
                                                            'exception_class'=>get_class($e),
                                                            'message'=>$e->getMessage(),
                                                            'file'=>$e->getFile(),
                                                            'line'=>$e->getLine()
                                                        ]
                                                    );
                                                }
                                            @endphp
                                        <div class="card-body">
                                            <span class="price-badge bg-dark">{{ $name }}</span>
                                            <span class="mb-4 f-w-00 p-price">
                                                {{ $currency }}{{ $price }}
                                                <small class="text-sm">/{{ $duration }}</small>
                                            </span>
                                            <p>{!! $description !!}</p>
                                            <ul class="list-unstyled my-3">
                                                @foreach($features as $feature)
                                                    @php
                                                        $val??=null;
                                                        $text??='';
                                                        try {
                                                            $val=$value->{$feature['key']}??null;
                                                            if($feature['type']==='quota'){
                                                                $text=$val===-1?__('Unlimited'):$val;
                                                                $text.=' '.$feature['label'];
                                                            }else{
                                                                $text=($val===1?__('Enable'):__('Disable')).' '.$feature['label'];
                                                            }
                                                        } catch(Error $e) {
                                                            Log::error(
                                                                'Error processing feature display',
                                                                [
                                                                    'exception_class'=>get_class($e),
                                                                    'message'=>$e->getMessage(),
                                                                    'file'=>$e->getFile(),
                                                                    'line'=>$e->getLine(),
                                                                    'feature_key'=>$feature['key'],
                                                                    'feature_type'=>$feature['type']
                                                                ]
                                                            );
                                                        } catch(Exception $e) {
                                                            Log::error(
                                                                'Exception processing feature display',
                                                                [
                                                                    'exception_class'=>get_class($e),
                                                                    'message'=>$e->getMessage(),
                                                                    'file'=>$e->getFile(),
                                                                    'line'=>$e->getLine(),
                                                                    'feature_key'=>$feature['key'],
                                                                    'feature_type'=>$feature['type']
                                                                ]
                                                            );
                                                        } catch(Throwable $e) {
                                                            Log::error(
                                                                'Throwable processing feature display',
                                                                [
                                                                    'exception_class'=>get_class($e),
                                                                    'message'=>$e->getMessage(),
                                                                    'file'=>$e->getFile(),
                                                                    'line'=>$e->getLine(),
                                                                    'feature_key'=>$feature['key'],
                                                                    'feature_type'=>$feature['type']
                                                                ]
                                                            );
                                                        }
                                                    @endphp
                                                    <li>
                                                        <div class="form-check text-start">
                                                            <label class="form-check-label" for="feature-{{ $loop->index }}">
                                                                {!! $icon !!}
                                                                {{ $text }}
                                                            </label>
                                                        </div>
                                                    </li>
                                                @endforeach
                                            </ul>
                                            <div class="d-grid">
                                                <a href="{{ Route::has('register') ? route('register') : '#' }}"
                                                    class="btn btn-primary rounded-pill"
                                                    {{ Route::has('register') ? '' : 'aria-disabled="true"' }}>
                                                    {{ __('Start with Starter') }}
                                                    <i data-feather="log-in" class="ms-2"></i>
                                                </a>
                                            </div>
                                        </div>
                                        </div>
                                    </div>
                                @endforeach

                            </div>
                        </div>
                    </section>
                @endif
                <!-- [ subscription ] end -->
                <!-- [ FAqs ] start -->
                @if ($lpSettings[LandingPageSettingsConstants::FAQ_STT_K] == 'on')
                    <section class="faqs section-gap bg-gray-100" id="faq">
                        <div class="{{ ViewClassNamesConstants::CT }}">
                            <div class="row mb-2">
                                <div class="col-xxl-6">
                                    <div class="title mb-4">
                                        <span class="d-block mb-2 fw-bold text-uppercase">{{ $lpSettings[LandingPageSettingsConstants::FAQ_TTL_K] }}</span>
                                        <h2 class="mb-4">{!! $lpSettings[LandingPageSettingsConstants::FAQ_HDG_K] !!}</h2>
                                        <p>{!! $lpSettings[LandingPageSettingsConstants::FAQ_DESC_K] !!}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="accordion accordion-flush" id="accordionFlushExample">
                                        @if (is_array(json_decode($lpSettings[LandingPageSettingsConstants::FAQ_FQS_K], true)) || is_object(json_decode($lpSettings[LandingPageSettingsConstants::FAQ_FQS_K], true)))
                                            @foreach (json_decode($lpSettings[LandingPageSettingsConstants::FAQ_FQS_K], true) as $key => $value)
                                                @if ($key % 2 == 0)
                                                    <div class="accordion-item">
                                                        <h2 class="accordion-header" id="{{ 'flush-heading' . $key }}">
                                                            <button class="accordion-button collapsed fw-bold" type="button"
                                                                data-bs-toggle="collapse" data-bs-target="{{ '#flush-' . $key }}"
                                                                aria-expanded="false" aria-controls="{{ 'flush-collapse' . $key }}">
                                                                {!! $value['faq_questions'] !!}
                                                            </button>
                                                        </h2>
                                                        <div id="{{ 'flush-' . $key }}" class="accordion-collapse collapse"
                                                            aria-labelledby="{{ 'flush-heading' . $key }}"
                                                            data-bs-parent="#accordionFlushExample">
                                                            <div class="accordion-body">
                                                                {!! $value['faq_answer'] !!}
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif
                                            @endforeach
                                        @endif

                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="accordion accordion-flush" id="accordionFlushExample2">
                                        @if (is_array(json_decode($lpSettings[LandingPageSettingsConstants::FAQ_FQS_K], true)) || is_object(json_decode($lpSettings[LandingPageSettingsConstants::FAQ_FQS_K], true)))
                                            @foreach (json_decode($lpSettings[LandingPageSettingsConstants::FAQ_FQS_K], true) as $key => $value)
                                                @if ($key % 2 != 0)
                                                    <div class="accordion-item">
                                                        <h2 class="accordion-header" id="{{ 'flush-heading' . $key }}">
                                                            <button class="accordion-button collapsed fw-bold" type="button"
                                                                data-bs-toggle="collapse" data-bs-target="{{ '#flush-' . $key }}"
                                                                aria-expanded="false" aria-controls="{{ 'flush-collapse' . $key }}">
                                                                {!! $value['faq_questions'] !!}
                                                            </button>
                                                        </h2>
                                                        <div id="{{ 'flush-' . $key }}" class="accordion-collapse collapse"
                                                            aria-labelledby="{{ 'flush-heading' . $key }}"
                                                            data-bs-parent="#accordionFlushExample2">
                                                            <div class="accordion-body">
                                                                {!! $value['faq_answer'] !!}
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif
                                            @endforeach
                                        @endif

                                    </div>
                                </div>

                            </div>
                        </div>
                    </section>
                @endif
                <!-- [ FAqs ] end -->
                <!-- [ testimonial ] start -->
                @if (!empty($lpSettings[LandingPageSettingsConstants::TM_STT_K]) && $lpSettings[LandingPageSettingsConstants::TM_STT_K] === 'on')
                    <section class="testimonial section-gap">
                        <div class="{{ ViewClassNamesConstants::CT }}">
                            <div class="row gy-4">
                                <div class="col-lg-4">
                                    <div class="title mb-4">
                                        <span class="d-block mb-2 fw-bold text-uppercase">{{ __('TESTIMONIALS') }}</span>
                                        <h2 class="mb-2">{!! $lpSettings[LandingPageSettingsConstants::TM_HDG_K] !!}</h2>
                                        <p>{!! $lpSettings[LandingPageSettingsConstants::TM_DESC_K] !!}</p>
                                    </div>
                                </div>
                                <div class="col-lg-8">
                                    <div class="row justify-content-center gy-3">
                                        @if (is_array(json_decode($lpSettings[LandingPageSettingsConstants::TM_TMS_K])) || is_object(json_decode($lpSettings[LandingPageSettingsConstants::TM_TMS_K])))
                                            @foreach (json_decode($lpSettings[LandingPageSettingsConstants::TM_TMS_K]) as $key => $value)
                                                <div class="col-xxl-4 col-sm-6 col-lg-6 col-md-4">
                                                    <div class="card bg-dark shadow-none mb-0">
                                                        <div class="card-body p-3">
                                                            <div class="d-flex mb-3 align-items-center justify-content-between">
                                                                <span class="theme-avatar avatar avatar-sm bg-light-dark rounded-1">
                                                                    <svg xmlns="http://www.w3.org/2000/svg" width="36"
                                                                        height="23" viewBox="0 0 36 23" fill="none">
                                                                        <path
                                                                            d="M12.4728 22.6171H0.770508L10.6797 0.15625H18.2296L12.4728 22.6171ZM29.46 22.6171H17.7577L27.6669 0.15625H35.2168L29.46 22.6171Z"
                                                                            fill="white" />
                                                                    </svg>
                                                                </span>
                                                                <span>
                                                                    @for ($i = 1; $i <= (int) $value[LandingPageSettingsConstants::TM_STR] ?? 5; $i++)
                                                                        <i data-feather="star"></i>
                                                                    @endfor
                                                                </span>
                                                            </div>
                                                            <h3 class="text-white">{{ $value[LandingPageSettingsConstants::TM_TTL_K] }}</h3>
                                                            <p class="hljs-comment">
                                                                {{ $value[LandingPageSettingsConstants::TM_DESC_K]}}
                                                            </p>
                                                            <div class="d-flex  align-items-center ">
                                                                <img src="{{ $logo . '/' . $value[LandingPageSettingsConstants::TM_USR_AV] }}"
                                                                    class="wid-40 rounded-circle me-3" alt="User avatar">
                                                                <span>
                                                                    <b class="fw-bold d-block">{{ $value[LandingPageSettingsConstants::TM_USR] ?? 'Anonymous' }}</b>
                                                                    {{ $value[LandingPageSettingsConstants::TM_USR_DSG] ?? 'Customer' }}
                                                                </span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        @endif

                                    </div>
                                </div>
                                <div class="col-12">
                                    <p class="mb-0 f-w-600">
                                        {!! $lpSettings[LandingPageSettingsConstants::TM_LONG_DESC_K] !!}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </section>
                @endif
                <!-- [ testimonial ] end -->
                <!-- [ Footer ] start -->
                <footer class="site-footer bg-gray-100">
                    <div class="{{ ViewClassNamesConstants::CT }}">
                        <div class="footer-row">
                            <div class="ftr-col cmp-detail">
                                <div class="footer-logo mb-3">
                                    <a href="#">
                                        <img src="{{ $logo . '/' . $lpSettings['site_logo'] }}" alt="logo">
                                    </a>
                                </div>
                                <p>
                                    {!! $lpSettings[LandingPageSettingsConstants::SD_K] !!}
                                </p>
                            </div>
                            <div class="ftr-col">
                                <ul class="list-unstyled">
                                    @if (is_array(json_decode($lpSettings[LandingPageSettingsConstants::MB_PG_K])) || is_object(json_decode($lpSettings[LandingPageSettingsConstants::MB_PG_K])))
                                        @php
                                            $lpSettings??=[];
                                            $data??=[];
                                            $logo??='';
                                            $sup_logo??='';
                                            $meta_title??='';
                                            $meta_desc??='';
                                            $meta_image??='';
                                            $meta_logo??='';
                                            $get_cookie??='';
                                            $colorSettings??=[];
                                            $siteRtl??=false;
                                            $color??='';
                                            try {
                                                $lpSettings=\Modules\LandingPage\Entities\LandingPageSetting::settings()?:[];
                                                $data=Utility::prepareCommonViewData(null,'uploads/landing_page_image')?:[];
                                                $logo=$data[SettingsConstants::LOGO]??'';
                                                $sup_logo=$data[SettingsConstants::SC_LOGO]??'';
                                                $meta_title=$data[SettingsConstants::MT_TTL_K]??'';
                                                $meta_desc=$data[SettingsConstants::MT_DESC_LONG]??'';
                                                $meta_image=$data[SettingsConstants::MT_IMG_K]??'';
                                                $meta_logo=$data[SettingsConstants::MT_LOGO]??'';
                                                $get_cookie=$data[SettingsConstants::CK_STG]??'';
                                                $colorSettings=$data[SettingsConstants::CLR_STG]??[];
                                                $siteRtl=$data[SettingsConstants::RTL]??false;
                                                $color=$data[SettingsConstants::THM_CLR]??'';
                                            } catch (\Error $e) {
                                                Log::error(
                                                    'Error loading landing page settings and view data',
                                                    [
                                                        'exception_class'=>get_class($e),
                                                        'message'=>$e->getMessage(),
                                                        'file'=>$e->getFile(),
                                                        'line'=>$e->getLine()
                                                    ]
                                                );
                                            } catch (\Exception $e) {
                                                Log::error(
                                                    'Exception loading landing page settings and view data',
                                                    [
                                                        'exception_class'=>get_class($e),
                                                        'message'=>$e->getMessage(),
                                                        'file'=>$e->getFile(),
                                                        'line'=>$e->getLine()
                                                    ]
                                                );
                                            } catch (\Throwable $e) {
                                                Log::error(
                                                    'Throwable loading landing page settings and view data',
                                                    [
                                                        'exception_class'=>get_class($e),
                                                        'message'=>$e->getMessage(),
                                                        'file'=>$e->getFile(),
                                                        'line'=>$e->getLine()
                                                    ]
                                                );
                                            }
                                        @endphp
                                        <ul class="list-unstyled">
                                            @foreach($menuItems as $item)
                                                @php
                                                    $footer??=false;
                                                    $header??=false;
                                                    $template??='';
                                                    $slug??='';
                                                    $name??='';
                                                    $url??='';
                                                    try {
                                                        $footer=($item['footer']??'')==='on';
                                                        $header=($item['header']??'')==='on';
                                                        $template=$item['template_name']??'';
                                                        $slug=$item[LandingPageSettingsConstants::PG_SLG]??'';
                                                        $name=$item[LandingPageSettingsConstants::MB_PG_NM]??'';
                                                        if($footer&&$template==='page_content'){
                                                            $url=Route::has('custom.page')
                                                                ?route('custom.page',$slug)
                                                                :'#';
                                                        }elseif($footer&&$template==='page_url'){
                                                            $url=$item['page_url']??'#';
                                                        }else{
                                                            $url='#';
                                                        }
                                                    } catch(Error $e) {
                                                        Log::error(
                                                            'Error processing footer item',
                                                            [
                                                                'exception_class'=>get_class($e),
                                                                'message'=>$e->getMessage(),
                                                                'file'=>$e->getFile(),
                                                                'line'=>$e->getLine(),
                                                                'item'=>$item
                                                            ]
                                                        );
                                                    } catch(Exception $e) {
                                                        Log::error(
                                                            'Exception processing footer item',
                                                            [
                                                                'exception_class'=>get_class($e),
                                                                'message'=>$e->getMessage(),
                                                                'file'=>$e->getFile(),
                                                                'line'=>$e->getLine(),
                                                                'item'=>$item
                                                            ]
                                                        );
                                                    } catch(Throwable $e) {
                                                        Log::error(
                                                            'Throwable processing footer item',
                                                            [
                                                                'exception_class'=>get_class($e),
                                                                'message'=>$e->getMessage(),
                                                                'file'=>$e->getFile(),
                                                                'line'=>$e->getLine(),
                                                                'item'=>$item
                                                            ]
                                                        );
                                                    }
                                                @endphp
                                                @if($footer && $name)
                                                    <li>
                                                        <a href="{{ $url }}">
                                                            {!! $name !!}
                                                        </a>
                                                    </li>
                                                @endif
                                            @endforeach
                                        </ul>
                                    @endif
                                </ul>
                            </div>
                            @if ($lpSettings[LandingPageSettingsConstants::JU_STT_K] == 'on')
                                <div class="ftr-col ftr-subscribe">
                                    <h2>{!! $lpSettings[LandingPageSettingsConstants::JU_HDG_K] !!}</h2>
                                    <p>{!! $lpSettings[LandingPageSettingsConstants::JU_DESC_K] !!}</p>
                                    @php
                                        $juSt = RoutesResourcesConstants::JU.'.store';
                                        $joinUsRouteExists = Route::has($juSt);
                                        $joinUsUrl         = $joinUsRouteExists ? route($juSt) : '#';
                                    @endphp
                                    <form method="post" action="{{ $joinUsUrl }}">
                                        @csrf
                                        <div class="input-wrapper border border-dark">
                                            <input type="email"
                                                name="email"
                                                placeholder="Type your email address…"
                                                {{ $joinUsRouteExists ? '' : 'disabled' }}>
                                            <button type="submit"
                                                    class="btn btn-dark rounded-pill"
                                                    {{ $joinUsRouteExists ? '' : 'disabled' }}>
                                                {{ __('Join Us') }}!
                                            </button>
                                        </div>
                                    </form>
                                    @if (!$joinUsRouteExists)
                                        <p class="text-muted mt-2">
                                            Sorry, sign-up is currently unavailable.
                                        </p>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="border-top border-dark text-center p-2">
                        <p class="mb-0"> &copy;
                            {{ date('Y') }}
                            {{ Utility::getValByName(SettingsConstants::FT_TXT) ? Utility::getValByName(SettingsConstants::FT_TXT) : config('app.name', 'ERPNovaPrestech') }}
                        </p>

                    </div>
                </footer>
                <!-- [ Footer ] end -->
                <!-- Required Js -->
                <script src="{{ Module::asset('LandingPage:js/plugins/popper.min.js') }}"></script>
                <script src="{{ Module::asset('LandingPage:js/plugins/bootstrap.min.js') }}"></script>
                <script src="{{ Module::asset('LandingPage:js/plugins/feather.min.js') }}"></script>
                <script>
                    // Start [ Menu hide/show on scroll ]
                    let ost = 0;
                    document.addEventListener("scroll", function() {
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

                    var scrollSpy = new bootstrap.ScrollSpy(document.body, {
                        target: "#navbar-example",
                    });
                    feather.replace();
                </script>
                <script>
                    (window.location.hostname === '127.0.0.1' || window.location.hostname === 'localhost') && console.log(
                        'Current route:',
                        '{{ Illuminate\Support\Facades\Route::currentRouteName() ?? Illuminate\Support\Facades\Route::currentRouteAction() }}'
                    );
                </script>
                @if ($get_cookie['enable_cookie'] == 'on')
                    @includeIf(ExtendingLayoutsConstants::CKC)
                @endif
        </body>
</html>
