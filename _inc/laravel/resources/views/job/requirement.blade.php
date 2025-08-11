@php
	use App\Config\Constants\{
		DatabaseConstants,
		ExtendingLayoutsConstants,
		SettingsConstants,
		ViewClassNamesConstants
	};
	use App\Models\Utility;
	use Illuminate\Support\Facades\Log;
	$data ??= [];
	$logo ??= '';
	$colorSettings ??= [];
	$color ??= '';
	$meta_title ??= '';
	$meta_desc ??= '';
	$meta_image ??= '';
	$meta_logo ??= '';
	$get_cookie ??= '';
	$faviconUrl ??= '';
	try {
		$data = Utility::prepareCommonViewData(null, 'uploads/logo/') ?: [];
		$logo = $data[SettingsConstants::LOGO] ?? '';
		$colorSettings = $data[SettingsConstants::CLR_STG] ?? [];
		$color = $data[SettingsConstants::THM_CLR] ?? '';
		$meta_title = $data[SettingsConstants::MT_TTL_K] ?? '';
		$meta_desc = $data[SettingsConstants::MT_DESC_LONG] ?? '';
		$meta_image = $data[SettingsConstants::MT_IMG_K] ?? '';
		$meta_logo = $data[SettingsConstants::MT_LOGO] ?? '';
		$get_cookie = $data[SettingsConstants::CK_STG] ?? '';
		$faviconUrl = Utility::getCompanyLogo() ?: '';
	} catch (\Error $e) {
		Log::error(
			'Error loading common view data with logo path',
			[
				'exception_class' => get_class($e),
				'message' => $e->getMessage(),
				'file' => $e->getFile(),
				'line' => $e->getLine()
			]
		);
	} catch (\Exception $e) {
		Log::error(
			'Exception loading common view data with logo path',
			[
				'exception_class' => get_class($e),
				'message' => $e->getMessage(),
				'file' => $e->getFile(),
				'line' => $e->getLine()
			]
		);
	} catch (\Throwable $e) {
		Log::error(
			'Throwable loading common view data with logo path',
			[
				'exception_class' => get_class($e),
				'message' => $e->getMessage(),
				'file' => $e->getFile(),
				'line' => $e->getLine()
			]
		);
	}
    $data = Utility::fallbackSettings($data);
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', is_string(app()->getLocale()) ? app()->getLocale() : DatabaseConstants::DEFAULT_LANG) }}" dir="{{ $siteRtl === 'on' ? 'rtl' : 'ltr' }}">
    <head>
        @include('fragments.std', [
            'meta_title' => $meta_title,
            'meta_desc' => $meta_desc,
            'meta_vp' => 'shrink-to-fit=no',
        ])
        <title>
            {{ !empty($companySettings['header_text']) ? $companySettings['header_text']->value : config('app.name', 'ERP Nova Prestech') }}
            - {{ __('Career') }}</title>
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
        <link rel="stylesheet" href="{{ asset('assets/fonts/tabler-icons.min.css') }}">
        <link rel="stylesheet" href="{{ asset('css/site.css') }}" id="stylesheet">
        @if (issetcolor_($colorSettings[SettingsConstants::CST_DRK]) && $colorSettings[SettingsConstants::CST_DRK] == 'on')
            <link rel="stylesheet" href="{{ asset('assets/css/style-dark.css') }}">
        @else
            <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}"id="main-style-link">
        @endif
        <link rel="stylesheet" href="{{ asset('css/custom.css') }}">
        @if (issetcolor_($colorSettings[SettingsConstants::CST_DRK]) && $colorSettings[SettingsConstants::CST_DRK] == 'on')
            <link rel="stylesheet" href="{{ asset('css/custom-dark.css') }}">
        @endif
        <meta name="csrf-token" content="{{ csrf_token() }}">
    </head>
    <body class="{{$color}}">
        <div class="job-wrapper">
            <div class="job-content">
                <nav class="{{ ViewClassNamesConstants::NVB }}">
                    <div class="{{ ViewClassNamesConstants::CT }}">
                        <a class="{{ ViewClassNamesConstants::NVB_BR }}" href="#">
                            <img src="{{ $logo . '/' . (isset($company_logos) && !empty($company_logos) ? $company_logos : 
                            SettingsConstants::CPN_LG_LT_DEF) }}" alt="logo" style="width: 90px">
                        </a>
                    </div>
                </nav>
                <section class="job-banner">
                    <div class="job-banner-bg">
                        <img src="{{asset('/storage/uploads/job/banner.png')}}" alt="">
                    </div>
                    <div class="{{ ViewClassNamesConstants::CT }}">
                        <div class="job-banner-content text-center text-white">
                            <h1 class="text-white mb-3">
                                {{__(' We help')}} <br> {{__('businesses grow')}}
                            </h1>
                            <p>{{ __('Work there. Find the dream job you’ve always wanted..') }}</p>
                            </p>
                        </div>
                    </div>
                </section>
                <section class="apply-job-section">
                    <div class="{{ ViewClassNamesConstants::CT }}">
                        <div class="apply-job-wrapper bg-light">
                            <div class="section-title text-center">
                                <p><b>{{$job->title}}</b></p>
                                <div class="d-flex flex-wrap justify-content-center gap-1 mb-4">
                                    @foreach (explode(',', $job->skill) as $skill)
                                        <span class="badge rounded p-2 bg-primary">{{ $skill }}</span>
                                    @endforeach
                                </div>

                                @if(!empty($job->branches)?$job->branches->name:'')
                                    <p> <i class="ti ti-map-pin ms-1"></i> {{!empty($job->branches)?$job->branches->name:''}}</p>
                                @endif

                                <a href="{{route('job.apply',[$job->code,$currentLang])}}" class="btn btn-primary rounded">{{__('Apply now')}} <i class="ti ti-send ms-2"></i> </a>
                            </div>
                            <h3>{{__('Requirements')}}</h3>
                            <p>{!! $job->requirement !!}</p>

                            <hr>
                            <h3>{{__('Description')}}</h3><br>
                            {!! $job->description !!}
                        </div>
                    </div>
                </section>
            </div>
        </div>
        <script src="{{ asset('assets/js/plugins/popper.min.js') }}"></script>
        <script src="{{ asset('assets/js/plugins/bootstrap.min.js') }}"></script>
        <script src="{{ asset('js/site.core.js') }}"></script>
        <script src="{{ asset('js/site.js') }}"></script>
        <script src="{{ asset('js/demo.js') }} "></script>
        <script async src="{{ asset('assets/js/plugins/perfect-scrollbar.min.js') }}"></script>
        <script async src="{{ asset('assets/js/plugins/feather.min.js') }}"></script>
    </body>
    @if($get_cookie['enable_cookie'] == 'on')
        @includeIf(ExtendingLayoutsConstants::CKC)
    @endif
</html>
