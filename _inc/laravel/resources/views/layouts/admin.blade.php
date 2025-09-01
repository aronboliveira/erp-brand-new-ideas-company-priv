@php
	use App\Config\Constants\{DatabaseConstants,SettingsConstants,
        StacksConstants,ViewClassNamesConstants,YieldingConstants};
	use App\Models\Utility;
	use Illuminate\Support\Facades\Log;
    Log::debug('Loading admin blade layout data...');
	$data??=[];
	$setting??=[];
	$colorSettings??=[];
	$logo??='';
	$company_favicon??='';
	$color??='';
	$siteRtl??=false;
	$lang = Utility::fetchUserLang();
	$meta_title??='';
	$meta_desc??='';
	$meta_image??='';
	$meta_logo??='';
	$faviconUrl??='';
	$filePath??='';
	try {
		$data=Utility::prepareCommonViewData()?:[];
		$setting=$data[SettingsConstants::ENTITY]??[];
		$colorSettings=$data[SettingsConstants::CLR_STG]??[];
		$logo=$data[SettingsConstants::LOGO]??'';
		$company_favicon=$data[SettingsConstants::FAV_ICN]??'';
		$color=$data[SettingsConstants::THM_CLR]??'';
		$siteRtl=$data[SettingsConstants::RTL]??false;
		$meta_title=$data[SettingsConstants::MT_TTL_K]??'';
		$meta_desc=$data[SettingsConstants::MT_DESC_LONG]??'';
		$meta_image=$data[SettingsConstants::MT_IMG_K]??'';
		$meta_logo=$data[SettingsConstants::MT_LOGO]??'';
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
    Log::debug('Loading admin blade layout template...');
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
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-body">
                        <button
                            type="button"
                            class="btn-close float-end"
                            data-bs-dismiss="modal"
                            aria-label="Close"
                        ></button>
                        <h6 class="mt-2">
                            <i data-feather="monitor" class="me-2"></i>Desktop settings
                        </h6>
                        <hr/>
                        <div class="form-check form-switch">
                            <input
                                type="checkbox"
                                class="form-check-input"
                                id="pcsetting1"
                                checked
                            />
                            <label class="form-check-label f-w-600 pl-1" for="pcsetting1"
                            >Allow desktop notification</label
                            >
                        </div>
                        <p class="text-muted ms-5">
                            you get lettest content at a time when data will updated
                        </p>
                        <div class="form-check form-switch">
                            <input type="checkbox" class="form-check-input" id="pcsetting2"/>
                            <label class="form-check-label f-w-600 pl-1" for="pcsetting2"
                            >Store Cookie</label
                            >
                        </div>
                        <h6 class="mb-0 mt-5">
                            <i data-feather="save" class="me-2"></i>Application settings
                        </h6>
                        <hr/>
                        <div class="form-check form-switch">
                            <input type="checkbox" class="form-check-input" id="pcsetting3"/>
                            <label class="form-check-label f-w-600 pl-1" for="pcsetting3"
                            >Backup Storage</label
                            >
                        </div>
                        <p class="text-muted mb-4 ms-5">
                            Automaticaly take backup as par schedule
                        </p>
                        <div class="form-check form-switch">
                            <input type="checkbox" class="form-check-input" id="pcsetting4"/>
                            <label class="form-check-label f-w-600 pl-1" for="pcsetting4"
                            >Allow guest to print file</label
                            >
                        </div>
                        <h6 class="mb-0 mt-5">
                            <i data-feather="cpu" class="me-2"></i>System settings
                        </h6>
                        <hr/>
                        <div class="form-check form-switch">
                            <input
                                type="checkbox"
                                class="form-check-input"
                                id="pcsetting5"
                                checked
                            />
                            <label class="form-check-label f-w-600 pl-1" for="pcsetting5"
                            >View other user chat</label
                            >
                        </div>
                        <p class="text-muted ms-5">Allow to show public user message</p>
                    </div>
                    <div class="modal-footer">
                        <button
                            type="button"
                            class="btn btn-light-danger btn-sm"
                            data-bs-dismiss="modal"
                        >
                            Close
                        </button>
                        <button type="button" class="btn btn-light-primary btn-sm">
                            Save changes
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="dash-container">
            <div class="dash-content">
                <div class="page-header">
                    <div class="page-block">
                        <div class="row align-items-center">
                            <div class="col-auto">
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
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel"></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="body">
                    </div>
                </div>
            </div>
        </div>
        <div class="{{ ViewClassNamesConstants::MD_FD }}" id="commonModalOver" tabindex="-1" role="dialog" aria-labelledby="commonModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="commonModalLabel"></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                    </div>
                </div>
            </div>
        </div>
        <div class="position-fixed top-0 end-0 p-3" style="z-index: 99999">
            <div id="liveToast" class="toast text-white fade" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body"></div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"
                            aria-label="Close"></button>
                </div>
            </div>
        </div>
        @include('partials.admin.footer')
        @include('Chatify::layouts.footer_links')
        <script>
            console.log(
                'Current route:',
                '{{ Illuminate\Support\Facades\Route::currentRouteName() ?? Illuminate\Support\Facades\Route::currentRouteAction() }}'
            );
        </script>
    </body>
</html>
