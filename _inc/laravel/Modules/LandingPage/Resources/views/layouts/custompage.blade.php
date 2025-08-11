@php
	use App\Config\Constants\{DatabaseConstants,SettingsConstants,ViewClassNamesConstants};
	use App\Models\Utility;
	use Illuminate\Support\Facades\{Log,Route};
	use Modules\LandingPage\Config\Constants\{RoutesResourcesConstants,SettingsConstants as LandingPageSettingsConstants};
    use Nwidart\Modules\Facades\Module;
	use Symfony\Component\Console\Output\ConsoleOutput;

	if (!$page) {
        Log::error('Page variable is not set, defaulting to about_us');
        $page = 'about_us';
    }
	$user??=null;
	$creatorId??='';
	$data??=[];
	$setting??=[];
	$colorSettings??=[];
	$logo??='';
	$sup_logo??='';
	$adminSettings??=[];
	$meta_title??='';
	$meta_desc??='';
	$meta_image??='';
	$meta_logo??='';
	$siteRtl??=false;
	$color??='';
	$faviconUrl??='';
	$lpSettings??=[];
	try {
		$user=auth()->user();
		$creatorId=$user?->id?:DatabaseConstants::DEFAULT_UUID;
		$data=Utility::prepareCommonViewData($creatorId,'uploads/landing_page_image')?:[];
		$setting=$data[SettingsConstants::ENTITY]??[];
		$colorSettings=$data[SettingsConstants::CLR_STG]??[];
		$logo=$data[SettingsConstants::LOGO]??'';
		$sup_logo=$data[SettingsConstants::SC_LOGO]??'';
		$adminSettings=$data[SettingsConstants::CPN_CFG]??[];
		$meta_title=$data[SettingsConstants::MT_TTL_K]??'';
		$meta_desc=$data[SettingsConstants::MT_DESC_LONG]??'';
		$meta_image=$data[SettingsConstants::MT_IMG_K]??'';
		$meta_logo=$data[SettingsConstants::MT_LOGO]??'';
		$siteRtl=$data[SettingsConstants::RTL]??false;
		$color=$data[SettingsConstants::THM_CLR]??'';
		$faviconUrl=Utility::getCompanyLogo()?:'';
		$lpSettings=\Modules\LandingPage\Entities\LandingPageSetting::settings()?:[];
	} catch (\Error $e) {
		Log::error(
			'Error fetching landing page data',
			[
				'exception_class'=>get_class($e),
				'message'=>$e->getMessage(),
				'file'=>$e->getFile(),
				'line'=>$e->getLine()
			]
		);
	} catch (\Exception $e) {
		Log::error(
			'Exception fetching landing page data',
			[
				'exception_class'=>get_class($e),
				'message'=>$e->getMessage(),
				'file'=>$e->getFile(),
				'line'=>$e->getLine()
			]
		);
	} catch (\Throwable $e) {
		Log::error(
			'Throwable fetching landing page data',
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
    <html lang="{{ str_replace('_', '-', is_string(app()->getLocale()) ? app()->getLocale() : DatabaseConstants::DEFAULT_LANG) }}"  dir="{{$siteRtl == 'on'?'rtl':''}}">
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
            <link rel="stylesheet" href="{{ Module::asset('LandingPage:fonts/tabler-icons.min.css')}}" />
            <link rel="stylesheet" href="{{ Module::asset('LandingPage:fonts/feather.css')}}" />
            <link rel="stylesheet" href="{{ Module::asset('LandingPage:fonts/fontawesome.css')}}" />
            <link rel="stylesheet" href="{{ Module::asset('LandingPage:fonts/material.css')}}" />
            @if ($siteRtl == 'on')
                <link rel="stylesheet" href="{{ asset('assets/css/style-rtl.css') }}">
            @endif
            @if ($setting['cust_darklayout'] == 'on')
                <link rel="stylesheet" href="{{ asset('assets/css/style-dark.css') }}">
            @else
                <link rel="stylesheet" href="{{ Module::asset('LandingPage:css/style.css')}}" id="main-style-link">
            @endif
            <link rel="stylesheet" href=" {{ Module::asset('LandingPage:css/customizer.css')}}" />
            <link rel="stylesheet" href=" {{ Module::asset('LandingPage:css/landing-page.css')}}" />
            <link rel="stylesheet" href=" {{ Module::asset('LandingPage:css/custom.css')}}" />
            <link rel="stylesheet" href=" {{ Module::asset('LandingPage:css/landing-page.css')}}" />
            @include('fragments.stylesheets', ['settings' => $colorSettings])
        </head>
        @if (!empty($colorSettings[SettingsConstants::CST_DRK]) && $colorSettings[SettingsConstants::CST_DRK] === 'on')
            <body class="{{$color}} landing-dark">
        @else
            <body class="{{$color}}">
        @endif
            <!-- [ Header ] start -->
            <header class="main-header">
                @if (!empty($lpSettings[LandingPageSettingsConstants::TB_STT_K])
                && !empty($lpSettings[LandingPageSettingsConstants::TB_NTF_MSG_K]))
                    @if ($lpSettings[LandingPageSettingsConstants::TB_STT_K] === 'on')
                        <div class="announcement bg-dark text-center p-2">
                            <p class="mb-0">
                                @if(! empty($lpSettings[LandingPageSettingsConstants::TB_NTF_MSG_K]))
                                    {!! $lpSettings[LandingPageSettingsConstants::TB_NTF_MSG_K] !!}
                                @endif
                            </p>
                        </div>
                    @endif
                @endif
                @if (!empty($lpSettings[LandingPageSettingsConstants::MB_STT_K]) && $lpSettings[LandingPageSettingsConstants::MB_STT_K] === 'on')
                    <div class="{{ ViewClassNamesConstants::CT }}">
                        <nav class="{{ ViewClassNamesConstants::NVB_DEF_TOP }}">
                            <div class="header-left">
                                <a class="{{ ViewClassNamesConstants::NVB_BR_TPR }}" href="#">
                                    <img src="{{ $lpSettings[LandingPageSettingsConstants::SL_K] ? asset('assets/images/'.$lpSettings[LandingPageSettingsConstants::SL_K]) : asset('assets/images/logo-light.webp') }}" 
                                         alt="logo" 
                                         id="headerLogo"
                                         data-fallback-index="0"
                                         style="border-radius: 0.5rem 0.5rem 1rem 1rem; clip-path: inset(-8px 0px 0px 0px);
                                         transform: scale(1.1) translateY(1%);"
                                         onload="this.style.opacity = '1'"
                                         onerror="
                                            const fallbacks = [
                                                '{{ asset('assets/images/logo-light.webp') }}',
                                                '{{ asset('assets/logo-light.webp') }}',
                                                '{{ asset('logo-light.webp') }}'
                                            ];
                                            let currentIndex = parseInt(this.getAttribute('data-fallback-index')) || 0;
                                            if (currentIndex < fallbacks.length - 1) {
                                                if (currentIndex === 0) {
                                                    this.style.opacity = '0';
                                                    this.style.transition = 'opacity 0.5s ease-in-out';
                                                }
                                                currentIndex++;
                                                this.setAttribute('data-fallback-index', currentIndex);
                                                this.src = fallbacks[currentIndex];
                                            } else {
                                                this.onerror = null;
                                                this.style.opacity = '1';
                                            }
                                         ">
                                </a>
                            </div>
                            <div class="{{ ViewClassNamesConstants::NVB_CLP }}" id="navbarTogglerDemo01">
                                @php
                                    $menuItems??=[];
                                    try {
                                        $menuItems=json_decode(
                                            $lpSettings[LandingPageSettingsConstants::MB_PG_K]??'[]',true
                                        )?:[];
                                    } catch (\Error $e) {
                                        Log::error(
                                            'Error decoding menubar pages JSON',
                                            [
                                                'exception_class'=>get_class($e),
                                                'message'=>$e->getMessage(),
                                                'file'=>$e->getFile(),
                                                'line'=>$e->getLine(),
                                                'raw'=>$lpSettings[LandingPageSettingsConstants::MB_PG_K]??'[]'
                                            ]
                                        );
                                    } catch (\Exception $e) {
                                        Log::error(
                                            'Exception decoding menubar pages JSON',
                                            [
                                                'exception_class'=>get_class($e),
                                                'message'=>$e->getMessage(),
                                                'file'=>$e->getFile(),
                                                'line'=>$e->getLine(),
                                                'raw'=>$lpSettings[LandingPageSettingsConstants::MB_PG_K]??'[]'
                                            ]
                                        );
                                    } catch (\Throwable $e) {
                                        Log::error(
                                            'Throwable decoding menubar pages JSON',
                                            [
                                                'exception_class'=>get_class($e),
                                                'message'=>$e->getMessage(),
                                                'file'=>$e->getFile(),
                                                'line'=>$e->getLine(),
                                                'raw'=>$lpSettings[LandingPageSettingsConstants::MB_PG_K]??'[]'
                                            ]
                                        );
                                    }
                                @endphp
                                <ul class="{{ ViewClassNamesConstants::NVB_NAV }}">
                                    <li class="nav-item">
                                        <a class="nav-link active" href="{{ url('/#home') }}">
                                            {{ $lpSettings[LandingPageSettingsConstants::HM_TTL_K] ?? '' }}
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" href="{{ url('/#features') }}">
                                            {{ $lpSettings[LandingPageSettingsConstants::FT_TTL_K] ?? '' }}
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" href="{{ url('/#plan') }}">
                                            {{ $lpSettings[LandingPageSettingsConstants::PN_TTL_K] ?? '' }}
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" href="{{ url('/#faq') }}">
                                            {{ $lpSettings[LandingPageSettingsConstants::FAQ_TTL_K] ?? '' }}
                                        </a>
                                    </li>
                                    @foreach($menuItems as $item)
                                    @php
                                        $header??='';
                                        $template??='';
                                        $slug??='';
                                        $name??='';
                                        $cstNm??='';
                                        $cstRt??='';
                                        $url??='';
                                        try {
                                            $header=$item['header']??'';
                                            $template=$item['template_name']??'';
                                            $slug=$item[LandingPageSettingsConstants::PG_SLG]??'';
                                            $name=$item[LandingPageSettingsConstants::MB_PG_NM]??'';
                                            if($header==='on'&&$template==='page_content'){
                                                $cstNm='custom.page';
                                                $cstRt=Route::has($cstNm)?$cstNm:'#';
                                                $url=route('custom.page',$slug);
                                            }elseif($header==='on'&&$template==='page_url'){
                                                $url=$item['page_url']??'#';
                                            }else{
                                                $url='#';
                                            }
                                        } catch(Error $e) {
                                            Log::error(
                                                'Error processing menubar item data',
                                                [
                                                    'exception_class'=>get_class($e),
                                                    'message'=>$e->getMessage(),
                                                    'file'=>$e->getFile(),
                                                    'line'=>$e->getLine()
                                                ]
                                            );
                                        } catch(Exception $e) {
                                            Log::error(
                                                'Exception processing menubar item data',
                                                [
                                                    'exception_class'=>get_class($e),
                                                    'message'=>$e->getMessage(),
                                                    'file'=>$e->getFile(),
                                                    'line'=>$e->getLine()
                                                ]
                                            );
                                        } catch(Throwable $e) {
                                            Log::error(
                                                'Throwable processing menubar item data',
                                                [
                                                    'exception_class'=>get_class($e),
                                                    'message'=>$e->getMessage(),
                                                    'file'=>$e->getFile(),
                                                    'line'=>$e->getLine()
                                                ]
                                            );
                                        }
                                    @endphp
                                        @if($header === 'on' && $name)
                                            <li class="nav-item">
                                                <a class="nav-link" href="{{ $url }}">
                                                    {!! $name !!}
                                                </a>
                                            </li>
                                        @endif
                                    @endforeach
                                </ul>
                                <button class="{{ ViewClassNamesConstants::NVB_TG_P }}" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#navbarTogglerDemo01" aria-controls="navbarTogglerDemo01" aria-expanded="false"
                                    aria-label="Toggle navigation">
                                    <span class="{{ ViewClassNamesConstants::NVB_TG_IC }}"></span>
                                </button>
                            </div>
                            <div class="ms-auto d-flex justify-content-end gap-2">
                                <a href="{{ Route::has('login') ? route('login') : '#' }}" class="btn btn-outline-dark rounded">
                                    <span class="hide-mob me-2">{{ __('Login') }}</span>
                                    <i data-feather="log-in"></i>
                                </a>
                                <a href="{{ Route::has('register') ? route('register') : '#' }}" class="btn btn-outline-dark rounded">
                                    <span class="hide-mob me-2">{{ __('Register') }}</span>
                                    <i data-feather="user-check"></i>
                                </a>
                                <button class="{{ ViewClassNamesConstants::NVB_TG }}" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#navbarTogglerDemo01" aria-controls="navbarTogglerDemo01" aria-expanded="false"
                                    aria-label="Toggle navigation">
                                    <span class="{{ ViewClassNamesConstants::NVB_TG_IC }}"></span>
                                </button>
                            </div>
                        </nav>
                    </div>
                @endif
            </header>
            <!-- [ Header ] End -->
            <!-- [ common banner ] start -->
            <section class="common-banner bg-primary">
                <div class="{{ ViewClassNamesConstants::CT }}">
                    <div class="row align-items-center">
                        <div class="col-lg-4">
                            <div class="title">
                                <h1 class="text-white">
                                    @if(! empty($page[LandingPageSettingsConstants::MB_PG_NM]))
                                        {!! $page[LandingPageSettingsConstants::MB_PG_NM] !!}
                                    @endif
                                </h1>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
            <!-- [ common banner ] end -->
            <!-- [ Static content ] start -->
                <section class="static-content section-gap">
                    <div class="{{ ViewClassNamesConstants::CT }}">
                        <div class="mb-5">
                            @if (!empty($page[LandingPageSettingsConstants::MB_PG_CT]))
                                {!! $page[LandingPageSettingsConstants::MB_PG_CT] !!}
                            @endif
                        </div>
                        @if (!empty($lpSettings[LandingPageSettingsConstants::TM_STT_K]) && $lpSettings[LandingPageSettingsConstants::TM_STT_K] === 'on')
                            @if (is_array(json_decode($lpSettings[LandingPageSettingsConstants::TM_TMS_K], true)) || is_object(json_decode($lpSettings[LandingPageSettingsConstants::TM_TMS_K], true)))
                                @php
                                    $decodedTestimonials??=[];
                                    try {
                                        $decodedTestimonials=json_decode(
                                            $lpSettings[LandingPageSettingsConstants::TM_TMS_K]??'[]',
                                            true
                                        )?:[];
                                    } catch (\Error $e) {
                                        Log::error(
                                            'Error decoding testimonials JSON',
                                            [
                                                'exception_class'=>get_class($e),
                                                'message'=>$e->getMessage(),
                                                'file'=>$e->getFile(),
                                                'line'=>$e->getLine(),
                                                'raw'=>$lpSettings[LandingPageSettingsConstants::TM_TMS_K]??'[]'
                                            ]
                                        );
                                    } catch (\Exception $e) {
                                        Log::error(
                                            'Exception decoding testimonials JSON',
                                            [
                                                'exception_class'=>get_class($e),
                                                'message'=>$e->getMessage(),
                                                'file'=>$e->getFile(),
                                                'line'=>$e->getLine(),
                                                'raw'=>$lpSettings[LandingPageSettingsConstants::TM_TMS_K]??'[]'
                                            ]
                                        );
                                    } catch (\Throwable $e) {
                                        Log::error(
                                            'Throwable decoding testimonials JSON',
                                            [
                                                'exception_class'=>get_class($e),
                                                'message'=>$e->getMessage(),
                                                'file'=>$e->getFile(),
                                                'line'=>$e->getLine(),
                                                'raw'=>$lpSettings[LandingPageSettingsConstants::TM_TMS_K]??'[]'
                                            ]
                                        );
                                    }
                                @endphp
                                @if ($decodedTestimonials && count($decodedTestimonials) > 0)
                                    @php
                                        $decodedTestimonials??=[];
                                        $testimonial??=[];
                                        try {
                                            $decodedTestimonials=json_decode(
                                                $lpSettings[LandingPageSettingsConstants::TM_TMS_K]??'[]',
                                                true
                                            )?:[];
                                            $testimonial=!empty($decodedTestimonials)
                                                ?$decodedTestimonials[array_rand($decodedTestimonials,1)]
                                                :[];
                                        } catch (\Error $e) {
                                            Log::error(
                                                'Error selecting testimonial',
                                                [
                                                    'exception_class'=>get_class($e),
                                                    'message'=>$e->getMessage(),
                                                    'file'=>$e->getFile(),
                                                    'line'=>$e->getLine(),
                                                    'raw'=>$lpSettings[LandingPageSettingsConstants::TM_TMS_K]??'[]'
                                                ]
                                            );
                                        } catch (\Exception $e) {
                                            Log::error(
                                                'Exception selecting testimonial',
                                                [
                                                    'exception_class'=>get_class($e),
                                                    'message'=>$e->getMessage(),
                                                    'file'=>$e->getFile(),
                                                    'line'=>$e->getLine(),
                                                    'raw'=>$lpSettings[LandingPageSettingsConstants::TM_TMS_K]??'[]'
                                                ]
                                            );
                                        } catch (\Throwable $e) {
                                            Log::error(
                                                'Throwable selecting testimonial',
                                                [
                                                    'exception_class'=>get_class($e),
                                                    'message'=>$e->getMessage(),
                                                    'file'=>$e->getFile(),
                                                    'line'=>$e->getLine(),
                                                    'raw'=>$lpSettings[LandingPageSettingsConstants::TM_TMS_K]??'[]'
                                                ]
                                            );
                                        }
                                    @endphp
                                    <div>
                                        <div class="row gy-4">
                                            <div class="col-12">
                                                <div class="bg-primary p-4 rounded">
                                                    <div class="row gy-3 align-items-center">
                                                        <div class="col-xxl-6 col-lg-6">
                                                            <div class="d-flex flex-column flex-sm-row gap-3">
                                                                <span class="theme-avatar avatar avatar-xl bg-light-dark rounded-1">
                                                                    <svg xmlns="http://www.w3.org/2000/svg" width="36" height="23" viewBox="0 0 36 23" fill="none">
                                                                        <path d="M12.4728 22.6171H0.770508L10.6797 0.15625H18.2296L12.4728 22.6171ZM29.46 22.6171H17.7577L27.6669 0.15625H35.2168L29.46 22.6171Z" fill="black"></path>
                                                                        </svg>
                                                                </span>
                                                                <div>
                                                                    <h2>
                                                                        @if(! empty($testimonial[LandingPageSettingsConstants::TM_TTL_K]))
                                                                            {!! $testimonial[LandingPageSettingsConstants::TM_TTL_K] !!}
                                                                        @endif
                                                                    </h2>
                                                                    <p class="mb-0">
                                                                        @if(! empty($testimonial[LandingPageSettingsConstants::TM_DESC_K]))
                                                                            {!! $testimonial[LandingPageSettingsConstants::TM_DESC_K] !!}
                                                                        @endif
                                                                    </p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-xxl-6 col-lg-6">
                                                        <div class="d-flex align-items-center gap-3 justify-content-center justify-content-sm-end">
                                                            <div class="text-end">
                                                                <b class="d-block">{{ $testimonial[LandingPageSettingsConstants::TM_USR] ?? 'Anonymous' }} </b>
                                                                <span class="d-block">
                                                                    @if(! empty($testimonial[LandingPageSettingsConstants::TM_USR_DSG]))
                                                                        {!! $testimonial[LandingPageSettingsConstants::TM_USR_DSG] !!}
                                                                    @else
                                                                        Customer
                                                                    @endif
                                                                </span>
                                                                <span>
                                                                    @for ($i = 1; $i <= (int) $testimonial[LandingPageSettingsConstants::TM_STR] ?? 5; $i++)
                                                                        <i data-feather="star"></i>
                                                                    @endfor
                                                                </span>
                                                            </div>
                                                            @php
                                                                try {
                                                                    Log::debug('Fetching avatar URL for testimonial: '.json_encode(array_keys($testimonial)));
                                                                    $avatarKey = LandingPageSettingsConstants::TM_USR_AV;
                                                                    $avatar    = $testimonial[$avatarKey] ?? null;
                                                                    $avatarUrl = $avatar
                                                                        ? asset('assets/images/'.$testimonial[$avatarKey])
                                                                        : asset('uploads/avatar/avatar.png');
                                                                } catch (\Throwable) {
                                                                    Log::debug('Error fetching avatar URL for testimonial: '.$testimonial[LandingPageSettingsConstants::TM_USR_AV] ?? 'No avatar URL found');
                                                                }
                                                            @endphp
                                                            <span class="theme-avatar avatar avatar-l rounded-circle">
                                                                <img
                                                                    src="{{ $avatarUrl }}"
                                                                    class="img-fluid rounded-circle"
                                                                    alt="User avatar"
                                                                >
                                                            </span>
                                                        </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            @endif
                        @endif
                    </div>
                </section>
            <!-- [ Static content ] end -->
            <!-- [ Footer ] start -->
            <footer class="site-footer bg-gray-100">
                <div class="{{ ViewClassNamesConstants::CT }}">
                    <div class="footer-row">
                        <div class="ftr-col cmp-detail">
                            <div class="footer-logo mb-3">
                                <a rel="external" href="https://prestech.com.br/site/" hreflang="pt-BR" target="_blank">
                                    <img src="{{ asset($lpSettings[LandingPageSettingsConstants::SL_K] ?? 'assets/images/favicon.ico') }}" 
                                         alt="logo" 
                                         id="footerLogo"
                                         data-fallback-index="0"
                                         style="border-radius: 1rem;
                                         transform: scale(0.8);"
                                         onload="this.style.opacity = '1'"
                                         onerror="
                                            const fallbacks = [
                                                '{{ asset('assets/images/favicon.ico') }}',
                                                '{{ asset('assets/favicon.ico') }}',
                                                '{{ asset('favicon.ico') }}'
                                            ];
                                            let currentIndex = parseInt(this.getAttribute('data-fallback-index')) || 0;
                                            if (currentIndex < fallbacks.length - 1) {
                                                if (currentIndex === 0) {
                                                    this.style.opacity = '0';
                                                    this.style.transition = 'opacity 0.5s ease-in-out';
                                                }
                                                currentIndex++;
                                                this.setAttribute('data-fallback-index', currentIndex);
                                                this.src = fallbacks[currentIndex];
                                            } else {
                                                this.style.opacity = '1';
                                                this.onerror = null;
                                            }
                                         ">
                                </a>
                            </div>
                            <p>
                                @if(!empty($lpSettings[LandingPageSettingsConstants::SD_K]))
                                    {!! $lpSettings[LandingPageSettingsConstants::SD_K] !!}
                                @endif
                            </p>
                        </div>
                        <div class="ftr-col">
                            @php
                                $menuItems??=[];
                                try {
                                    $menuItems=json_decode(
                                        $lpSettings[LandingPageSettingsConstants::MB_PG_K]??'[]',
                                        true
                                    )?:[];
                                } catch (\Error $e) {
                                    Log::error(
                                        'Error decoding menubar pages JSON',
                                        [
                                            'exception_class'=>get_class($e),
                                            'message'=>$e->getMessage(),
                                            'file'=>$e->getFile(),
                                            'line'=>$e->getLine(),
                                            'raw'=>$lpSettings[LandingPageSettingsConstants::MB_PG_K]??'[]'
                                        ]
                                    );
                                } catch (\Exception $e) {
                                    Log::error(
                                        'Exception decoding menubar pages JSON',
                                        [
                                            'exception_class'=>get_class($e),
                                            'message'=>$e->getMessage(),
                                            'file'=>$e->getFile(),
                                            'line'=>$e->getLine(),
                                            'raw'=>$lpSettings[LandingPageSettingsConstants::MB_PG_K]??'[]'
                                        ]
                                    );
                                } catch (\Throwable $e) {
                                    Log::error(
                                        'Throwable decoding menubar pages JSON',
                                        [
                                            'exception_class'=>get_class($e),
                                            'message'=>$e->getMessage(),
                                            'file'=>$e->getFile(),
                                            'line'=>$e->getLine(),
                                            'raw'=>$lpSettings[LandingPageSettingsConstants::MB_PG_K]??'[]'
                                        ]
                                    );
                                }
                            @endphp
                            <ul class="list-unstyled">
                                @foreach ($menuItems as $item)
                                    @php
                                        $footer??=false;
                                        $template??='';
                                        $slug??='';
                                        $name??='';
                                        $url??='';
                                        try {
                                            $footer=($item['footer']??'')==='on';
                                            $template=$item['template_name']??'';
                                            $slug=$item[LandingPageSettingsConstants::PG_SLG]??'';
                                            $name=$item[LandingPageSettingsConstants::MB_PG_NM]??'';
                                            if($footer&&$template==='page_content'){
                                                $cstNm='custom.page';
                                                $url=Route::has($cstNm)
                                                    ?route($cstNm,['slug'=>$slug])
                                                    :'#';
                                            }elseif($footer&&$template==='page_url'){
                                                $url=$item['page_url']??'#';
                                            }else{
                                                $url='#';
                                            }
                                        } catch (\Error $e) {
                                            Log::error(
                                                'Error processing footer item',
                                                [
                                                    'exception_class'=>get_class($e),
                                                    'message'=>$e->getMessage(),
                                                    'file'=>$e->getFile(),
                                                    'line'=>$e->getLine(),
                                                    'footer'=>$footer,
                                                    'template'=>$template,
                                                    'slug'=>$slug,
                                                    'name'=>$name
                                                ]
                                            );
                                        } catch (\Exception $e) {
                                            Log::error(
                                                'Exception processing footer item',
                                                [
                                                    'exception_class'=>get_class($e),
                                                    'message'=>$e->getMessage(),
                                                    'file'=>$e->getFile(),
                                                    'line'=>$e->getLine(),
                                                    'footer'=>$footer,
                                                    'template'=>$template,
                                                    'slug'=>$slug,
                                                    'name'=>$name
                                                ]
                                            );
                                        } catch (\Throwable $e) {
                                            Log::error(
                                                'Throwable processing footer item',
                                                [
                                                    'exception_class'=>get_class($e),
                                                    'message'=>$e->getMessage(),
                                                    'file'=>$e->getFile(),
                                                    'line'=>$e->getLine(),
                                                    'footer'=>$footer,
                                                    'template'=>$template,
                                                    'slug'=>$slug,
                                                    'name'=>$name
                                                ]
                                            );
                                        }
                                    @endphp
                                    @if ($footer && $name)
                                        <li>
                                            <a href="{{ $url }}">
                                                {!! $name !!}
                                            </a>
                                        </li>
                                    @endif
                                @endforeach
                            </ul>
                        </div>
                        @if ( $lpSettings[LandingPageSettingsConstants::JU_STT_K] == 'on')
                        <div class="ftr-col ftr-subscribe">
                            <h2>
                                @if(! empty($lpSettings[LandingPageSettingsConstants::JU_HDG_K]))
                                    {!! $lpSettings[LandingPageSettingsConstants::JU_HDG_K] !!}
                                @endif
                            </h2>
                            <p>
                                @if(! empty($lpSettings[LandingPageSettingsConstants::JU_DESC_K]))
                                    {!! $lpSettings[LandingPageSettingsConstants::JU_DESC_K] !!}
                                @endif
                            </p>
                            @php
                                $juRt = RoutesResourcesConstants::JU.'.store';
                                $juSt = Route::has($juRt) ? $juRt : '#';
                            @endphp
                            <form method="post" action="{{ $juSt }}">
                                @csrf
                                <div class="input-wrapper border border-dark">
                                    <input type="text" name="email" placeholder="Type your email address...">
                                    <button type="submit" class="btn btn-dark rounded-pill">{{__('Join Us')}}!</button>
                                </div>
                            </form>
                        </div>
                        @endif
                    </div>
                </div>
                <div class="border-top border-dark text-center p-2">
                    <p class="mb-0">  &copy;
                        {{ date('Y') }} {{ Utility::getValByName(SettingsConstants::FT_TXT) ? Utility::getValByName(SettingsConstants::FT_TXT) : config('app.name', 'ERPNovaPrestech') }}
                    </p>
                </div>
            </footer>
            <!-- [ Footer ] end -->
            <!-- Required Js -->
            <script src="{{ Module::asset('LandingPage:js/plugins/popper.min.js')}}"></script>
            <script src="{{ Module::asset('LandingPage:js/plugins/bootstrap.min.js')}}"></script>
            <script src="{{ Module::asset('LandingPage:js/plugins/feather.min.js')}}"></script>
            <script>
                // Start [ Menu hide/show on scroll ]
                let ost = 0;
                document.addEventListener("scroll", function () {
                    let cOst = document.documentElement.scrollTop;
                    const navbar = document.querySelector(".navbar");
                    if (nabvbar instanceof HTMLElement) {
                        if (cOst == 0)
                            navbar.classList.add("top-nav-collapse");
                        else if (cOst > ost) {
                            navbar.classList.add("top-nav-collapse");
                            navbar.classList.remove("default");
                        } else {
                            navbar.classList.add("default");
                            navbar.classList.remove("top-nav-collapse");
                        }
                        ost = cOst;
                    }
                });
                if (document.getElementById('#navbar-example')) {
                    const scrollSpy = new bootstrap.ScrollSpy(document.body, {
                        target: "#navbar-example",
                    });
                }
                feather.replace();
            </script>
            <script>
                console.log(
                    'Current route:',
                    '{{ Illuminate\Support\Facades\Route::currentRouteName() ?? Illuminate\Support\Facades\Route::currentRouteAction() }}'
                );
            </script>
        </body>
    </html>
