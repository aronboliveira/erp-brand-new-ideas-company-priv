@php


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
    $lang = DC::DEFAULT_LANG;
	try {
		$user=auth()->user();
        $lang = Utility::fetchUserLang(user: $user);
		$creatorId=$user?->id?:DC::DEFAULT_UUID;
		$data=Utility::prepareCommonViewData($creatorId,'uploads/landing_page_image')?:[];
		$setting=$data[SC::ENTITY]??[];
		$colorSettings=$data[SC::CLR_STG]??[];
		$logo=$data[SC::LOGO]??'';
		$sup_logo=$data[SC::SC_LOGO]??'';
		$adminSettings=$data[SC::CPN_CFG]??[];
		$meta_title=$data[SC::MT_TTL_K]??'';
		$meta_desc=$data[SC::MT_DESC_LONG]??'';
		$meta_image=$data[SC::MT_IMG_K]??'';
		$meta_logo=$data[SC::MT_LOGO]??'';
		$siteRtl=$data[SC::RTL]??false;
		$color=$data[SC::THM_CLR]??'';
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
    <html lang="{{ $lang ? str_replace('_', '-', app()->getLocale() ?? DC::DEFAULT_LANG) : '' }}" dir="{{ $siteRtl == 'on' ? 'rtl' : 'ltr' }}">
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
            <link rel="stylesheet" href="{{ asset('assets/css/auth-enhancements.css') }}">
            {{-- Transparent base64 decode for data-guard-msg attributes --}}
            <script>
                (function(){var o=Element.prototype.getAttribute;Element.prototype.getAttribute=function(n){var v=o.call(this,n);if(n==='data-guard-msg'&&v){try{return decodeURIComponent(atob(v))}catch(e){try{return atob(v)}catch(e2){return v}}}return v}})();
            </script>
            <style>
                #navbarTogglerDemo01 {
                    padding-bottom: 2rem;
                }
                #auth-cta-fs {
                    padding-bottom: 2rem;
                }
                #main-header-container {
                    margin: 0;
                    min-width: 100%;
                }
                #main-header-container .navbar.top-nav-collapse {
                    display: flex;
                    align-items: center;
                    gap: 2rem;
                    padding-inline: 1rem;
                }
                #main-header-container .header-left {
                    margin-left: 2rem;
                    transform: translateY(-0.75rem);
                }
                @media screen and (max-width: 764px) {
                    #main-header-container .header-left {
                        margin-left: 0;
                        transform: translateY(-1rem) !important;
                    }
                    #main-header-container .header-left #headerLogo {
                        margin-left: 2rem;
                        width: 8rem !important;
                        transform: 
                    }
                    #navbarTogglerDemo01 {
                        padding-bottom: 0;
                        padding-top: 1rem;
                        width: 10rem;
                    }
                    .main-header .navbar .btn.rounded,
                    .navbar-toggler {
                        border: 1px solid #eee !important;
                    }
                    svg.feather:not([class*="hei-"]),
                    svg.feather:not([class*="wid-"]) {
                        transform: scale(2);
                    }
                }
            </style>
        </head>
        @if (!empty($colorSettings[SC::CST_DRK]) && $colorSettings[SC::CST_DRK] === 'on')
            <body class="{{$color}} landing-dark">
        @else
            <body class="{{$color}}">
        @endif
            <!-- [ Header ] start -->
            <header class="main-header">
                @if (!empty($lpSettings[LPSC::TB_STT_K])
                && !empty($lpSettings[LPSC::TB_NTF_MSG_K]))
                    @if ($lpSettings[LPSC::TB_STT_K] === 'on')
                        <div class="announcement bg-dark text-center p-2">
                            <p class="mb-0">
                                @if(! empty($lpSettings[LPSC::TB_NTF_MSG_K]))
                                    {!! $lpSettings[LPSC::TB_NTF_MSG_K] !!}
                                @endif
                            </p>
                        </div>
                    @endif
                @endif
                @if (!empty($lpSettings[LPSC::MB_STT_K]) && $lpSettings[LPSC::MB_STT_K] === 'on')
                    <div class="{{ VC::CT }}" id="main-header-container">
                        <nav class="{{ VC::NVB_DEF_TOP }}">
                            <div class="header-left">
                                <a class="{{ VC::NVB_BR_TPR }}" href="https://prestech.com.br/site/" rel="external" target="_blank" hreflang="pt-BR" title="{{ __('Nova Prestech') }}">
                                    <img src="{{ $lpSettings[LPSC::SL_K] ? asset('assets/images/'.$lpSettings[LPSC::SL_K]) : asset('assets/images/logo-light.webp') }}"
                                         alt="logo"
                                         id="headerLogo"
                                         data-fallback-index="0"
                                         data-fallbacks="{{ asset('assets/images/logo-light.webp') }},{{ asset('assets/logo-light.webp') }},{{ asset('logo-light.webp') }}"
                                         style="border-radius: 0.5rem 0.5rem 1rem 1rem; clip-path: inset(-8px 0px 0px 0px); width: 10rem;
                                         transform: scale(1.1) translateY(1%);"
                                         onload="this.style.opacity='1'"
                                         onerror="var f=(this.getAttribute('data-fallbacks')||'').split(',').filter(Boolean),i=parseInt(this.getAttribute('data-fallback-index'),10)||0;if(i<f.length){this.style.opacity='0';this.style.transition='opacity .5s';this.setAttribute('data-fallback-index',++i);this.src=f[i-1]}else{this.onerror=null;this.style.opacity='1'}">
                                </a>
                            </div>
                            <div class="{{ VC::NVB_CLP }}" id="navbarTogglerDemo01">
                                @php
                                    $menuItems??=[];
                                    try {
                                        $menuItems=json_decode(
                                            $lpSettings[LPSC::MB_PG_K]??'[]',true
                                        )?:[];
                                    } catch (\Error $e) {
                                        Log::error(
                                            'Error decoding menubar pages JSON',
                                            [
                                                'exception_class'=>get_class($e),
                                                'message'=>$e->getMessage(),
                                                'file'=>$e->getFile(),
                                                'line'=>$e->getLine(),
                                                'raw'=>$lpSettings[LPSC::MB_PG_K]??'[]'
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
                                                'raw'=>$lpSettings[LPSC::MB_PG_K]??'[]'
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
                                                'raw'=>$lpSettings[LPSC::MB_PG_K]??'[]'
                                            ]
                                        );
                                    }
                                @endphp
                                <ul class="{{ VC::NVB_NAV }}">
                                    <li class="nav-item">
                                        @auth
                                            <a class="nav-link active" href="{{ Route::has('home') ? route('home') : url('/') }}">
                                                {{ __($lpSettings[LPSC::HM_TTL_K] ?? 'Home') }}
                                            </a>
                                        @else
                                            <a class="nav-link active" href="{{ Route::has('login') ? route('login') : url('/login') }}">
                                                {{ __($lpSettings[LPSC::HM_TTL_K] ?? 'Home') }}
                                            </a>
                                        @endauth
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" href="{{ Route::has('about_us') ? route('about_us') . '#features-section' : url('/about_us#features-section') }}">
                                            {{ __($lpSettings[LPSC::FT_TTL_K] ?? 'Features') }}
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" href="{{ Route::has('about_us') ? route('about_us') . '#plan-section' : url('/about_us#plan-section') }}">
                                            {{ __($lpSettings[LPSC::PN_TTL_K] ?? 'Plan') }}
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" href="{{ Route::has('about_us') ? route('about_us') . '#faq-section' : url('/about_us#faq-section') }}">
                                            {{ __($lpSettings[LPSC::FAQ_TTL_K] ?? 'Faq') }}
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" href="{{ Route::has('custom.page') ? route('custom.page', 'privacy_policy') : url('/privacy_policy') }}">
                                            {{ __('Privacy Policy') }}
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" href="{{ Route::has('custom.page') ? route('custom.page', 'terms_and_conditions') : url('/terms_and_conditions') }}">
                                            {{ __('Terms & Conditions') }}
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
                                            $slug=$item[LPSC::PG_SLG]??'';
                                            $name=$item[LPSC::MB_PG_NM]??'';
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
                                                    {{ __($name) }}
                                                </a>
                                            </li>
                                        @endif
                                    @endforeach
                                </ul>
                            </div>
                            <div class="ms-auto d-flex justify-content-end gap-2" id="auth-cta-fs">
                                <a href="{{ Route::has('login') ? route('login') : '#' }}" class="btn btn-outline-dark rounded">
                                    <span class="hide-mob me-2">{{ __('Login') }}</span>
                                    <i data-feather="log-in"></i>
                                </a>
                                <a href="{{ Route::has('register') ? route('register') : '#' }}" class="btn btn-outline-dark rounded">
                                    <span class="hide-mob me-2">{{ __('Register') }}</span>
                                    <i data-feather="user-check"></i>
                                </a>
                                <button class="{{ VC::NVB_TG }}" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#navbarTogglerDemo01" aria-controls="navbarTogglerDemo01" aria-expanded="false"
                                    aria-label="Toggle navigation">
                                    <span class="{{ VC::NVB_TG_IC }}"></span>
                                </button>
                            </div>
                        </nav>
                    </div>
                @endif
            </header>
            <!-- [ Header ] End -->
            <!-- [ common banner ] start -->
            <section class="common-banner bg-primary">
                <div class="{{ VC::CT }}">
                    <div class="row align-items-center">
                        <div class="col-lg-4">
                            <div class="title">
                                <h1 class="text-white">
                                    @if(! empty($page[LPSC::MB_PG_NM]))
                                        {!! $page[LPSC::MB_PG_NM] !!}
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
                    <div class="{{ VC::CT }}">
                        <div class="mb-5">
                            @if (!empty($page[LPSC::MB_PG_CT]) && trim(strip_tags($page[LPSC::MB_PG_CT])) !== '')
                                {!! $page[LPSC::MB_PG_CT] !!}
                            @elseif(!empty($page[LPSC::PG_SLG]) && $page[LPSC::PG_SLG] === 'terms_and_conditions')
                                @include('landingpage::partials.terms_and_conditions')
                            @elseif(!empty($page[LPSC::PG_SLG]) && $page[LPSC::PG_SLG] === 'privacy_policy')
                                @include('landingpage::partials.privacy_policy')
                            @elseif(!empty($page[LPSC::PG_SLG]) && $page[LPSC::PG_SLG] === 'about_us')
                                @include('landingpage::partials.about_us')
                            @else
                                <div class="card shadow-sm border-0">
                                    <div class="card-body p-4 p-md-5">
                                        <p class="text-muted text-center">{{ __('This page has no content yet.') }}</p>
                                    </div>
                                </div>
                            @endif
                        </div>
                        @if (!empty($lpSettings[LPSC::TM_STT_K]) && $lpSettings[LPSC::TM_STT_K] === 'on'
                            && (!empty($page[LPSC::PG_SLG]) && $page[LPSC::PG_SLG] === 'about_us' || request()->is('about_us')))
                            @if (is_array(json_decode($lpSettings[LPSC::TM_TMS_K], true)) || is_object(json_decode($lpSettings[LPSC::TM_TMS_K], true)))
                                @php
                                    $decodedTestimonials??=[];
                                    try {
                                        $decodedTestimonials=json_decode(
                                            $lpSettings[LPSC::TM_TMS_K]??'[]',
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
                                                'raw'=>$lpSettings[LPSC::TM_TMS_K]??'[]'
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
                                                'raw'=>$lpSettings[LPSC::TM_TMS_K]??'[]'
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
                                                'raw'=>$lpSettings[LPSC::TM_TMS_K]??'[]'
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
                                                $lpSettings[LPSC::TM_TMS_K]??'[]',
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
                                                    'raw'=>$lpSettings[LPSC::TM_TMS_K]??'[]'
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
                                                    'raw'=>$lpSettings[LPSC::TM_TMS_K]??'[]'
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
                                                    'raw'=>$lpSettings[LPSC::TM_TMS_K]??'[]'
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
                                                                        @if(! empty($testimonial[LPSC::TM_TTL_K]))
                                                                            {!! $testimonial[LPSC::TM_TTL_K] !!}
                                                                        @endif
                                                                    </h2>
                                                                    <p class="mb-0">
                                                                        @if(! empty($testimonial[LPSC::TM_DESC_K]))
                                                                            {!! $testimonial[LPSC::TM_DESC_K] !!}
                                                                        @endif
                                                                    </p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-xxl-6 col-lg-6">
                                                        <div class="d-flex align-items-center gap-3 justify-content-center justify-content-sm-end">
                                                            <div class="text-end">
                                                                <b class="d-block">{{ $testimonial[LPSC::TM_USR] ?? 'Anonymous' }} </b>
                                                                <span class="d-block">
                                                                    @if(! empty($testimonial[LPSC::TM_USR_DSG]))
                                                                        {!! $testimonial[LPSC::TM_USR_DSG] !!}
                                                                    @else
                                                                        Customer
                                                                    @endif
                                                                </span>
                                                                <span>
                                                                    @for ($i = 1; $i <= (int) $testimonial[LPSC::TM_STR] ?? 5; $i++)
                                                                        <i data-feather="star"></i>
                                                                    @endfor
                                                                </span>
                                                            </div>
                                                            @php
                                                                try {
                                                                    Log::debug('Fetching avatar URL for testimonial: '.json_encode(array_keys($testimonial)));
                                                                    $avatarKey = LPSC::TM_USR_AV;
                                                                    $avatar    = $testimonial[$avatarKey] ?? null;
                                                                    $avatarUrl = $avatar
                                                                        ? asset('assets/images/'.$testimonial[$avatarKey])
                                                                        : asset('uploads/avatar/avatar.png');
                                                                } catch (\Throwable) {
                                                                    Log::debug('Error fetching avatar URL for testimonial: '.$testimonial[LPSC::TM_USR_AV] ?? 'No avatar URL found');
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
            
            {{-- Footer Styles --}}
            <style>
                /* Prestech-inspired Footer Styling */
                .site-footer {
                    background: linear-gradient(180deg, 
                        #0a1628 0%, 
                        #1a365d 50%,
                        #0d503c 100%
                    ) !important;
                    color: #e2e8f0;
                    position: relative;
                    overflow: hidden;
                }

                .site-footer::before {
                    content: '';
                    position: absolute;
                    top: 0;
                    left: 0;
                    right: 0;
                    bottom: 0;
                    background: 
                        radial-gradient(ellipse at 10% 90%, rgba(16, 185, 129, 0.15) 0%, transparent 50%),
                        radial-gradient(ellipse at 90% 10%, rgba(99, 179, 237, 0.1) 0%, transparent 50%);
                    pointer-events: none;
                }

                .footer-main {
                    padding: 3.5rem 0 2.5rem;
                    position: relative;
                    z-index: 1;
                }

                .footer-brand {
                    margin-bottom: 2rem;
                    display: flex;
                    align-items: center;
                    gap: 1rem;
                }

                .footer-logo-img {
                    max-height: 80px;
                    max-width: 200px;
                    width: auto;
                    height: auto;
                    object-fit: contain;
                    border-radius: 0.5rem;
                    transition: transform 0.3s ease, box-shadow 0.3s ease;
                    background: rgba(255, 255, 255, 0.05);
                    padding: 0.5rem;
                }

                .footer-logo-img:hover {
                    transform: scale(1.03);
                    box-shadow: 0 8px 25px rgba(16, 185, 129, 0.3);
                }

                .footer-description {
                    color: rgba(226, 232, 240, 0.9);
                    font-size: 0.95rem;
                    line-height: 1.8;
                    max-width: 100%;
                    margin-bottom: 1.5rem;
                }

                .footer-heading {
                    color: #10b981;
                    font-weight: 600;
                    font-size: 1.2rem;
                    margin-bottom: 2rem;
                    position: relative;
                    padding-bottom: 0.85rem;
                    letter-spacing: 0.3px;
                }

                .footer-heading::after {
                    content: '';
                    position: absolute;
                    bottom: 0;
                    left: 0;
                    width: 50px;
                    height: 3px;
                    background: linear-gradient(90deg, #10b981, #34d399);
                    border-radius: 3px;
                }

                .footer-links {
                    list-style: none;
                    padding: 0;
                    margin: 0;
                }

                .footer-links li {
                    margin-bottom: 1rem;
                }

                .footer-links a {
                    color: rgba(226, 232, 240, 0.85);
                    text-decoration: none;
                    transition: all 0.3s ease;
                    display: inline-flex;
                    align-items: center;
                    gap: 0.5rem;
                    font-size: 0.95rem;
                }

                .footer-links a::before {
                    content: '→';
                    opacity: 0;
                    transform: translateX(-10px);
                    transition: all 0.3s ease;
                    color: #10b981;
                }

                .footer-links a:hover {
                    color: #10b981;
                    padding-left: 0.5rem;
                }

                .footer-links a:hover::before {
                    opacity: 1;
                    transform: translateX(0);
                }

                .footer-contact-item {
                    display: flex;
                    align-items: flex-start;
                    gap: 1.25rem;
                    margin-bottom: 1.75rem;
                    color: rgba(226, 232, 240, 0.88);
                }

                .footer-contact-icon {
                    width: 45px;
                    height: 45px;
                    border-radius: 50%;
                    background: linear-gradient(135deg, rgba(16, 185, 129, 0.2), rgba(49, 130, 206, 0.2));
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    flex-shrink: 0;
                    border: 1px solid rgba(16, 185, 129, 0.3);
                    transition: all 0.3s ease;
                }

                .footer-contact-item:hover .footer-contact-icon {
                    background: linear-gradient(135deg, #10b981, #0d503c);
                    transform: scale(1.08);
                    box-shadow: 0 4px 15px rgba(16, 185, 129, 0.4);
                }

                .footer-contact-icon svg,
                .footer-contact-icon i {
                    width: 20px;
                    height: 20px;
                    color: #10b981;
                }

                .footer-contact-item:hover .footer-contact-icon svg,
                .footer-contact-item:hover .footer-contact-icon i {
                    color: white;
                }

                .footer-contact-text {
                    font-size: 0.95rem;
                    line-height: 1.6;
                }

                .footer-contact-text a {
                    color: rgba(226, 232, 240, 0.9);
                    text-decoration: none;
                    transition: color 0.3s ease;
                }

                .footer-contact-text a:hover {
                    color: #34d399;
                }

                .footer-social {
                    display: flex;
                    gap: 1rem;
                    margin-top: 2rem;
                    flex-wrap: wrap;
                }

                .footer-social-link {
                    width: 48px;
                    height: 48px;
                    border-radius: 50%;
                    background: linear-gradient(135deg, rgba(255,255,255,0.1), rgba(255,255,255,0.05));
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    color: #e2e8f0;
                    text-decoration: none;
                    transition: all 0.3s ease;
                    border: 1px solid rgba(255,255,255,0.1);
                }

                .footer-social-link:hover {
                    transform: translateY(-5px);
                    box-shadow: 0 8px 20px rgba(16, 185, 129, 0.4);
                }

                .footer-social-link.facebook:hover {
                    background: linear-gradient(135deg, #1877f2, #0d47a1);
                    border-color: #1877f2;
                }

                .footer-social-link.linkedin:hover {
                    background: linear-gradient(135deg, #0077b5, #005885);
                    border-color: #0077b5;
                }

                .footer-social-link.instagram:hover {
                    background: linear-gradient(135deg, #e4405f, #c13584, #833ab4);
                    border-color: #e4405f;
                }

                .footer-social-link svg,
                .footer-social-link i {
                    width: 20px;
                    height: 20px;
                }

                .footer-subscribe {
                    background: linear-gradient(145deg, rgba(255,255,255,0.08), rgba(255,255,255,0.03));
                    border-radius: 1rem;
                    padding: 1.5rem;
                    border: 1px solid rgba(16, 185, 129, 0.2);
                }

                .footer-subscribe h2 {
                    color: white;
                    font-size: 1.1rem;
                    margin-bottom: 0.75rem;
                }

                .footer-subscribe p {
                    color: rgba(226, 232, 240, 0.7);
                    font-size: 0.9rem;
                    margin-bottom: 1rem;
                }

                .footer-subscribe .input-wrapper {
                    display: flex;
                    gap: 0.5rem;
                    flex-wrap: wrap;
                }

                .footer-subscribe input[type="email"] {
                    flex: 1;
                    min-width: 200px;
                    padding: 0.75rem 1rem;
                    border-radius: 2rem;
                    border: 1px solid rgba(16, 185, 129, 0.3);
                    background: rgba(255,255,255,0.1);
                    color: white;
                    font-size: 0.9rem;
                    transition: all 0.3s ease;
                }

                .footer-subscribe input[type="email"]::placeholder {
                    color: rgba(226, 232, 240, 0.5);
                }

                .footer-subscribe input[type="email"]:focus {
                    outline: none;
                    border-color: #10b981;
                    background: rgba(255,255,255,0.15);
                    box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.2);
                }

                .footer-subscribe button {
                    background: linear-gradient(135deg, #10b981, #0d503c);
                    border: none;
                    padding: 0.75rem 1.5rem;
                    border-radius: 2rem;
                    color: white;
                    font-weight: 600;
                    transition: all 0.3s ease;
                }

                .footer-subscribe button:hover {
                    transform: scale(1.05);
                    box-shadow: 0 8px 20px rgba(16, 185, 129, 0.4);
                }

                .footer-bottom {
                    background: rgba(0,0,0,0.3);
                    border-top: 1px solid rgba(16, 185, 129, 0.2);
                    padding: 1.25rem 0;
                    position: relative;
                    z-index: 1;
                }

                .footer-bottom-content {
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    flex-wrap: wrap;
                    gap: 1rem;
                }

                .footer-copyright {
                    color: rgba(226, 232, 240, 0.7);
                    font-size: 0.9rem;
                    margin: 0;
                }

                .footer-bottom-links {
                    display: flex;
                    gap: 1.5rem;
                    flex-wrap: wrap;
                }

                .footer-bottom-links a {
                    color: rgba(226, 232, 240, 0.7);
                    text-decoration: none;
                    font-size: 0.85rem;
                    transition: color 0.3s ease;
                }

                .footer-bottom-links a:hover {
                    color: #10b981;
                }

                @media (max-width: 768px) {
                    .footer-main { padding: 2.5rem 0 1.5rem; }
                    .footer-bottom-content {
                        flex-direction: column;
                        text-align: center;
                    }
                    .footer-description { max-width: 100%; }
                }
            </style>

            <!-- [ Footer ] start -->
            <footer class="site-footer">
                <div class="footer-main">
                    <div class="{{ VC::CT }}">
                        <div class="row g-5">
                            {{-- Brand & Description Column --}}
                            <div class="col-lg-3 col-md-6">
                                <div class="footer-brand">
                                    <a rel="external" href="https://prestech.com.br/site/" hreflang="pt-BR" target="_blank">
                                        <img src="{{ asset($lpSettings[LPSC::SL_K] ?? 'assets/images/favicon.ico') }}"
                                             alt="Nova Prestech"
                                             class="footer-logo-img"
                                             data-fallback-index="0"
                                             data-fallbacks="{{ asset('assets/images/favicon.ico') }},{{ asset('assets/favicon.ico') }},{{ asset('favicon.ico') }}"
                                             onload="this.style.opacity='1'"
                                             onerror="var f=(this.getAttribute('data-fallbacks')||'').split(',').filter(Boolean),i=parseInt(this.getAttribute('data-fallback-index'),10)||0;if(i<f.length){this.style.opacity='0';this.style.transition='opacity .5s';this.setAttribute('data-fallback-index',++i);this.src=f[i-1]}else{this.onerror=null;this.style.opacity='1'}">
                                    </a>
                                </div>
                                <p class="footer-description">
                                    @if(!empty($lpSettings[LPSC::SD_K]))
                                        {!! $lpSettings[LPSC::SD_K] !!}
                                    @else
                                        {{ __('Delivering comprehensive technology solutions with over 30 years of experience — from infrastructure and cybersecurity to custom software development.') }}
                                    @endif
                                </p>
                                {{-- Social Links --}}
                                <div class="footer-social">
                                    <a href="https://web.facebook.com/prestech.tecnologia" target="_blank" rel="noopener noreferrer" class="footer-social-link facebook" title="{{ __('Facebook') }}">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                                    </a>
                                    <a href="https://www.linkedin.com/company/novaprestech" target="_blank" rel="noopener noreferrer" class="footer-social-link linkedin" title="{{ __('LinkedIn') }}">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>
                                    </a>
                                    <a href="https://www.instagram.com/novaprestech/" target="_blank" rel="noopener noreferrer" class="footer-social-link instagram" title="{{ __('Instagram') }}">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M12 0C8.74 0 8.333.015 7.053.072 5.775.132 4.905.333 4.14.63c-.789.306-1.459.717-2.126 1.384S.935 3.35.63 4.14C.333 4.905.131 5.775.072 7.053.012 8.333 0 8.74 0 12s.015 3.667.072 4.947c.06 1.277.261 2.148.558 2.913.306.788.717 1.459 1.384 2.126.667.666 1.336 1.079 2.126 1.384.766.296 1.636.499 2.913.558C8.333 23.988 8.74 24 12 24s3.667-.015 4.947-.072c1.277-.06 2.148-.262 2.913-.558.788-.306 1.459-.718 2.126-1.384.666-.667 1.079-1.335 1.384-2.126.296-.765.499-1.636.558-2.913.06-1.28.072-1.687.072-4.947s-.015-3.667-.072-4.947c-.06-1.277-.262-2.149-.558-2.913-.306-.789-.718-1.459-1.384-2.126C21.319 1.347 20.651.935 19.86.63c-.765-.297-1.636-.499-2.913-.558C15.667.012 15.26 0 12 0zm0 2.16c3.203 0 3.585.016 4.85.071 1.17.055 1.805.249 2.227.415.562.217.96.477 1.382.896.419.42.679.819.896 1.381.164.422.36 1.057.413 2.227.057 1.266.07 1.646.07 4.85s-.015 3.585-.074 4.85c-.061 1.17-.256 1.805-.421 2.227-.224.562-.479.96-.899 1.382-.419.419-.824.679-1.38.896-.42.164-1.065.36-2.235.413-1.274.057-1.649.07-4.859.07-3.211 0-3.586-.015-4.859-.074-1.171-.061-1.816-.256-2.236-.421-.569-.224-.96-.479-1.379-.899-.421-.419-.69-.824-.9-1.38-.165-.42-.359-1.065-.42-2.235-.045-1.26-.061-1.649-.061-4.844 0-3.196.016-3.586.061-4.861.061-1.17.255-1.814.42-2.234.21-.57.479-.96.9-1.381.419-.419.81-.689 1.379-.898.42-.166 1.051-.361 2.221-.421 1.275-.045 1.65-.06 4.859-.06l.045.03zm0 3.678c-3.405 0-6.162 2.76-6.162 6.162 0 3.405 2.76 6.162 6.162 6.162 3.405 0 6.162-2.76 6.162-6.162 0-3.405-2.76-6.162-6.162-6.162zM12 16c-2.21 0-4-1.79-4-4s1.79-4 4-4 4 1.79 4 4-1.79 4-4 4zm7.846-10.405c0 .795-.646 1.44-1.44 1.44-.795 0-1.44-.646-1.44-1.44 0-.794.646-1.439 1.44-1.439.793-.001 1.44.645 1.44 1.439z"/></svg>
                                    </a>
                                </div>
                            </div>

                            {{-- Quick Links Column --}}
                            <div class="col-lg-3 col-md-6">
                                <h6 class="footer-heading">{{ __('Quick Links') }}</h6>
                                @php
                                    $menuItems??=[];
                                    try {
                                        $menuItems=json_decode($lpSettings[LPSC::MB_PG_K]??'[]',true)?:[];
                                    } catch (\Throwable $e) {
                                        Log::error('Error decoding menubar pages JSON', ['message'=>$e->getMessage()]);
                                    }
                                @endphp
                                <ul class="footer-links">
                                    <li><a href="{{ route('login') }}">{{ __('Login') }}</a></li>
                                    @foreach ($menuItems as $item)
                                        @php
                                            $footer=($item['footer']??'')==='on';
                                            $template=$item['template_name']??'';
                                            $slug=$item[LPSC::PG_SLG]??'';
                                            $name=$item[LPSC::MB_PG_NM]??'';
                                            $url='#';
                                            if($footer&&$template==='page_content'){
                                                $url=Route::has('custom.page')?route('custom.page',['slug'=>$slug]):'#';
                                            }elseif($footer&&$template==='page_url'){
                                                $url=$item['page_url']??'#';
                                            }
                                        @endphp
                                        @if ($footer && $name)
                                            <li><a href="{{ $url }}">{!! $name !!}</a></li>
                                        @endif
                                    @endforeach
                                </ul>
                            </div>

                            {{-- Contact Info Column --}}
                            {{-- Contact Column --}}
                            <div class="col-lg-3 col-md-6">
                                <h6 class="footer-heading">{{ __('Contact') }}</h6>
                                <div class="footer-contact-item">
                                    <div class="footer-contact-icon">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                                    </div>
                                    <div class="footer-contact-text">
                                        <strong>{{ __('Address') }}</strong><br>
                                        Rua Francisco Manuel, 99A<br>
                                        Benfica - Rio de Janeiro - RJ<br>
                                        Brasil
                                    </div>
                                </div>
                                <div class="footer-contact-item">
                                    <div class="footer-contact-icon">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
                                    </div>
                                    <div class="footer-contact-text">
                                        <a href="mailto:comercial@prestech.com.br">comercial@prestech.com.br</a>
                                    </div>
                                </div>
                                <div class="footer-contact-item">
                                    <div class="footer-contact-icon">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
                                    </div>
                                    <div class="footer-contact-text">
                                        <a href="tel:+552138607510">+55 (21) 3860-7510</a>
                                    </div>
                                </div>
                            </div>

                            {{-- Newsletter Column --}}
                            @if ($lpSettings[LPSC::JU_STT_K] ?? '' == 'on')
                            <div class="col-lg-3 col-md-6">
                                <div class="footer-subscribe">
                                    <h2>
                                        @if(!empty($lpSettings[LPSC::JU_HDG_K]))
                                            {!! $lpSettings[LPSC::JU_HDG_K] !!}
                                        @else
                                            {{ __('Stay Updated') }}
                                        @endif
                                    </h2>
                                    <p>
                                        @if(!empty($lpSettings[LPSC::JU_DESC_K]))
                                            {!! $lpSettings[LPSC::JU_DESC_K] !!}
                                        @else
                                            {{ __('Subscribe to our newsletter for the latest news and updates.') }}
                                        @endif
                                    </p>
                                    @php $juSt = Route::has(RRC::JU.'.store') ? route(RRC::JU.'.store') : '#'; @endphp
                                    <form method="post" action="{{ $juSt }}">
                                        @csrf
                                        <div class="input-wrapper">
                                            <input type="email" name="email" placeholder="{{ __('Your email...') }}" required>
                                            <button type="submit">{{ __('Subscribe') }}</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
                {{-- Footer Bottom --}}
                <div class="footer-bottom">
                    <div class="{{ VC::CT }}">
                        <div class="footer-bottom-content">
                            <p class="footer-copyright">
                                &copy; {{ date('Y') }} {{ Utility::getValByName(SC::FT_TXT) ?: config('app.name', 'Nova Prestech.net Informática') }}. {{ __('All rights reserved.') }}
                            </p>
                            <div class="footer-bottom-links">
                                <a href="{{ Route::has('custom.page') ? route('custom.page', ['slug' => 'privacy_policy']) : '#' }}">{{ __('Privacy Policy') }}</a>
                                <a href="{{ Route::has('custom.page') ? route('custom.page', ['slug' => 'terms_and_conditions']) : '#' }}">{{ __('Terms & Conditions') }}</a>
                                <a href="https://prestech.com.br/site/contato/" target="_blank" rel="noopener">{{ __('Contact') }}</a>
                            </div>
                        </div>
                    </div>
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
                    if (navbar instanceof HTMLElement) {
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
                feather && typeof feather.replace === 'function' && feather.replace();
            </script>
            <script>
                (window.location.hostname === '127.0.0.1' || window.location.hostname === 'localhost') && console.log(
                    'Current route:',
                    '{{ Illuminate\Support\Facades\Route::currentRouteName() ?? Illuminate\Support\Facades\Route::currentRouteAction() }}'
                );
            </script>
        </body>
    </html>
