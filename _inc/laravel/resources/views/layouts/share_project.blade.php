@php
$route??='';
	$segment??='';
	$id??='';
	$project??=null;
	$user??=null;
	$data??=[];
	$colorSettings??=[];
	$siteRtl??=false;
	$company_favicon??='';
	$logo??='';
	$color??='';
	$faviconUrl??='';
	try {
        $route=Request::route()->getName()?:'';
		$segment=Request::segment(3)?:'';
		$id=Crypt::decrypt($segment)?:'';
		$project=Project::find($id);
		$user=User::find($project[DC::COL_TABLE_CREATOR]??null);
        $lang = Utility::fetchUserLang(user:$user);
		$data=Utility::prepareCommonViewData($user?->creatorId(),'uploads/logo')?:[];
		$colorSettings=$data[SC::CLR_STG]??[];
		$siteRtl=$data[SC::RTL]??false;
		$company_favicon=$data[SC::FAV_ICN]??'';
		$logo=$data[SC::LOGO]??'';
		$color=$data[SC::THM_CLR]??'';
		$faviconUrl=Utility::getCompanyLogo()?:'';
	} catch (\Error $e) {
		Log::error(
			'Error rendering project view data',
			[
				'exception_class'=>get_class($e),
				'message'=>$e->getMessage(),
				'file'=>$e->getFile(),
				'line'=>$e->getLine(),
				'route'=>$route,
				'segment'=>$segment,
				'id'=>$id
			]
		);
	} catch (\Exception $e) {
		Log::error(
			'Exception rendering project view data',
			[
				'exception_class'=>get_class($e),
				'message'=>$e->getMessage(),
				'file'=>$e->getFile(),
				'line'=>$e->getLine(),
				'route'=>$route,
				'segment'=>$segment,
				'id'=>$id
			]
		);
	} catch (\Throwable $e) {
		Log::error(
			'Throwable rendering project view data',
			[
				'exception_class'=>get_class($e),
				'message'=>$e->getMessage(),
				'file'=>$e->getFile(),
				'line'=>$e->getLine(),
				'route'=>$route,
				'segment'=>$segment,
				'id'=>$id
			]
		);
	}
    $data = Utility::fallbackSettings($data);
@endphp
<!DOCTYPE html>
<html lang="{{ $lang ?? str_replace('_', '-', is_string(app()->getLocale()) ? app()->getLocale() : DC::DEFAULT_LANG) }}" dir="{{$siteRtl === 'on'?'rtl':''}}">
    <head>
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>
            {{ Utility::getValByName('title_text') ? Utility::getValByName('title_text') : config('app.name', 'ERPNovaPrestech') }}
            - @yield(YieldingConstants::SHR_PRJ_PG_TTL)</title>
        @include('fragments.std', [
            'meta_title' => $meta_title,
            'meta_desc' => $meta_desc
        ])
        @include('fragments.favicon', ['faviconUrl' => $faviconUrl])
        @stack(StacksConstants::SHR_PRJ_HD)
        @include('fragments.stylesheets', ['settings' => $colorSettings])
        <link rel="stylesheet" href="{{ asset('assets/css/plugins/datepicker-bs5.min.css') }}">
        <link rel="stylesheet" href="{{ asset('assets/css/plugins/bootstrap-switch-button.min.css') }}">
        @if ($siteRtl == 'on')
            <link rel="stylesheet" href="{{ asset('assets/css/style-rtl.css') }}">
        @endif
        {{-- ERP Guard & Utils Initialization (Blocking) --}}
        <script>
            window.ERPGuard = window.ERPGuard || null;
            window.ERPUtils = window.ERPUtils || null;
        </script>
    </head>
    <body class="{{$color}}">
        <div class="{{ VC::CT }}">
        <div class="{{ VC::DSH_CTT }}">
            <!-- [ breadcrumb ] start -->
            <div class="{{ VC::PG_HDR }}">
                <div class="{{ VC::PG_BLK }}">
                    <div class="{{ VC::R_ALC }}">
                        <div class="{{ VC::CM12 }} mt-5 {{ VC::MB4 }}">
                            <div class="d-block d-sm-flex {{ VC::ALC }} {{ VC::JCB }}">
                                <div>
                                </div>
                                <div>
                                    @yield(YieldingConstants::SHR_PRJ_ACT_BTN)
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- <div class="row"> -->
        @yield(YieldingConstants::SHR_PRJ_CTT)
        <!-- </div> -->
        </div>
        </div>
        <script src="{{ asset('js/jquery.min.js') }}"></script>
        <script src="{{ asset('assets/js/plugins/popper.min.js') }}"></script>
        <script src="{{ asset('assets/js/plugins/bootstrap.min.js') }}"></script>
        <script src="{{ asset('assets/js/dash.js') }}"></script>
        <script src="{{ asset('js/custom.js') }}"></script>
        <script async src="{{ asset('js/jquery.form.js') }}"></script>
        <script async src="{{ asset('assets/js/plugins/perfect-scrollbar.min.js') }}"></script>
        <script async src="{{ asset('assets/js/plugins/simple-datatables.js') }}"></script>
        <script defer src="{{ asset('assets/js/plugins/choices.min.js') }}"></script>
        <script defer src="{{ asset('assets/js/plugins/feather.min.js') }}"></script>
        <script defer src="{{ asset('assets/js/plugins/bootstrap-switch-button.min.js') }}"></script>
        <script defer src="{{ asset('assets/js/plugins/sweetalert2.all.min.js') }}"></script>
        <script defer src="{{ asset('assets/js/plugins/datepicker-full.min.js') }}"></script>
        <script defer src="{{ asset('assets/js/plugins/flatpickr.min.js') }}"></script>
        <script defer src="{{ asset('js/letter.avatar.js') }}"></script>
        <script defer src="{{ asset('js/chatify/autosize.js') }}"></script>
        <script defer src="{{url('js/swiper.min.js')}}"></script>
        <script async src='https://unpkg.com/nprogress@0.2.0/nprogress.js'></script>
        <script>
            console.log(
                'Current route:',
                '{{ Illuminate\Support\Facades\Route::currentRouteName() ?? Illuminate\Support\Facades\Route::currentRouteAction() }}'
            );
        </script>
        {{-- ERP Guard & Utils Core Classes (Deferred) --}}
        <script src="{{ asset('assets/js/core/erp-guard.js') }}" defer></script>
        <script src="{{ asset('assets/js/core/erp-utils.js') }}" defer></script>
        @stack(StacksConstants::SHR_PRJ_SCR_PG)
        @include('partials.global-error-handler')
    </body>
    <div class="{{ VC::MD_FD }}" id="commonModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="{{ VC::MDL_DLG }}" role="document">
            <div class="{{ VC::MDL_CTT }}">
                <div class="{{ VC::MDL_HDR }}">
                    <h5 class="{{ VC::MDL_TTL }}" id="exampleModalLabel"></h5>
                    <button type="button" class="{{ VC::BT_CL }}" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
                </div>
                <div class="body">
                </div>
            </div>
        </div>
    </div>
</html>
