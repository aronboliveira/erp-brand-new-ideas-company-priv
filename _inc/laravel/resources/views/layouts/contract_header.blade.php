@php
	use App\Config\Constants\{SettingsConstants,ViewClassNamesConstants,YieldingConstants};
	use App\Models\Utility;
	use Illuminate\Support\Facades\Log;
	$data??=[];
	$logo??='';
	$company_favicon??='';
	$company_logo??='';
	$siteRtl??=false;
	$colorSettings??=[];
	$color??='';
	$faviconUrl??='';
    $lang = Utility::fetchUserLang();
	try {
		$data=Utility::prepareCommonViewData(null,'uploads/logo/')?:[];
		$logo=$data[SettingsConstants::LOGO]??'';
		$company_favicon=$data[SettingsConstants::FAV_ICN]??'';
		$company_logo=$data[SettingsConstants::LOGO]??'';
		$siteRtl=$data[SettingsConstants::RTL]??false;
		$colorSettings=$data[SettingsConstants::CLR_STG]??[];
		$color=$data[SettingsConstants::THM_CLR]??'';
		$faviconUrl=Utility::getCompanyLogo()?:'';
	} catch (\Error $e) {
		Log::error(
			"Error fetching logo data",
			[
				'exception_class'=>get_class($e),
				'message'=>$e->getMessage(),
				'file'=>$e->getFile(),
				'line'=>$e->getLine()
			]
		);
	} catch (\Exception $e) {
		Log::error(
			"Exception fetching logo data",
			[
				'exception_class'=>get_class($e),
				'message'=>$e->getMessage(),
				'file'=>$e->getFile(),
				'line'=>$e->getLine()
			]
		);
	} catch (\Throwable $e) {
		Log::error(
			"Throwable fetching logo data",
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
<html lang="{{ $lang ?? str_replace('_', '-', is_string(app()->getLocale()) ? app()->getLocale() : DatabaseConstants::DEFAULT_LANG) }}" dir="{{$siteRtl == 'on'?'rtl':''}}">
    <head>
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>
            {{ Utility::getValByName('title_text') ? Utility::getValByName('title_text') : config('app.name', 'ERPNovaPrestech') }}
            - @yield(YieldingConstants::CTC_PG_TTL)</title>
        @include('fragments.std', [
            'meta_title' => $meta_title,
            'meta_desc' => $meta_desc
        ])
        @include('fragments.stylesheets', ['settings' => $colorSettings])
        @include('fragments.favicon', ['faviconUrl' => $faviconUrl])
        @stack(StacksConstants::CTC_HD)
        <link rel="stylesheet" href="{{ asset('assets/css/plugins/bootstrap-switch-button.min.css') }}">
        <link rel="stylesheet" href="{{ asset('assets/css/plugins/datepicker-bs5.min.css') }}">
        @if ($siteRtl == 'on')
            <link rel="stylesheet" href="{{ asset('assets/css/style-rtl.css') }}">
        @endif
    </head>
    <body class="{{$color}}">
        <!-- [ Pre-loader ] start -->
        <!-- [ Mobile header ] End -->
        <!-- [ Main Content ] start -->
        <div class="{{ ViewClassNamesConstants::CT }}">
            <div class="dash-content">
                <!-- [ breadcrumb ] start -->
                <div class="page-header">
                    <div class="page-block">
                        <div class="row align-items-center">
                            <div class="col-md-12 mt-5 mb-4">
                                <div class="d-block d-sm-flex align-items-center justify-content-between">
                                    <div>
                                    </div>
                                    <div>
                                        @yield(YieldingConstants::CTC_ACT_BTN)
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- <div class="row"> -->
                @yield(YieldingConstants::CTC_CTT)
                <!-- </div> -->
            </div>
        </div>
        <script src="{{ asset('js/jquery.min.js') }}"></script>
        <script src="{{ asset('assets/js/dash.js') }}"></script>
        <script src="{{ asset('js/custom.js') }}"></script>
        <script async src='https://unpkg.com/nprogress@0.2.0/nprogress.js'></script>
        <script defer src="{{ asset('assets/js/plugins/choices.min.js') }}"></script>
        <script defer src="{{ asset('js/jquery.form.js') }}"></script>
        <script defer src="{{ asset('js/letter.avatar.js') }}"></script>
        <script defer src="{{ asset('assets/js/plugins/datepicker-full.min.js') }}"></script>
        <script defer src="{{ asset('assets/js/plugins/popper.min.js') }}"></script>
        <script defer src="{{ asset('assets/js/plugins/perfect-scrollbar.min.js') }}"></script>
        <script defer src="{{ asset('assets/js/plugins/bootstrap.min.js') }}"></script>
        <script defer src="{{ asset('assets/js/plugins/feather.min.js') }}"></script>
        <script defer src="{{ asset('assets/js/plugins/bootstrap-switch-button.min.js') }}"></script>
        <script defer src="{{ asset('assets/js/plugins/sweetalert2.all.min.js') }}"></script>
        <script defer src="{{ asset('assets/js/plugins/simple-datatables.js') }}"></script>
        <script defer src="{{ asset('assets/js/plugins/flatpickr.min.js') }}"></script>
        <script defer src="{{ asset('js/chatify/autosize.js') }}"></script>
        <script defer src="{{url('js/swiper.min.js')}}"></script>
        <!-- <script>
        if ($(".pc-dt-simple").length) {
            const dataTable = new simpleDatatables.DataTable(".pc-dt-simple");
        }
        </script> -->
        @stack(StacksConstants::CTC_SCR_PG)
        <script>
            console.log(
                'Current route:',
                '{{ Illuminate\Support\Facades\Route::currentRouteName() ?? Illuminate\Support\Facades\Route::currentRouteAction() }}'
            );
        </script>
    </body>
    <div class="{{ ViewClassNamesConstants::MD_FD }}" id="commonModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
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
</html>

