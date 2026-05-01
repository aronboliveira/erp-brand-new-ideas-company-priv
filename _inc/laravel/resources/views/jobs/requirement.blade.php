@php
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
		$logo = $data[ST::LOGO] ?? '';
		$colorSettings = $data[ST::CLR_STG] ?? [];
		$color = $data[ST::THM_CLR] ?? '';
		$meta_title = $data[ST::MT_TTL_K] ?? '';
		$meta_desc = $data[ST::MT_DESC_LONG] ?? '';
		$meta_image = $data[ST::MT_IMG_K] ?? '';
		$meta_logo = $data[ST::MT_LOGO] ?? '';
		$get_cookie = $data[ST::CK_STG] ?? '';
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
    $lang = Utility::fetchUserLang();
    $siteRtl = data_get($data,'site_rtl','off');
    $jobTitle = data_get($job,'title',__('No job title available'));
    $branchName = data_get($job,'branches.name');
    $skills = array_filter(array_map('trim',explode(',',(string) data_get($job,'skill',''))));
    $reqHtml = data_get($job,'requirement');
    $descHtml = data_get($job,'description');
@endphp
<!DOCTYPE html>
<html lang="{{ $lang ?? (str_replace('_', '-', is_string(app()->getLocale()) ? app()->getLocale() : DatabaseConstants::DEFAULT_LANG)) }}" dir="{{ $siteRtl === 'on' ? 'rtl' : 'ltr' }}">
    <head>
        @include('fragments.std', ['meta_title'=>$meta_title,'meta_desc'=>$meta_desc,'meta_vp'=>'shrink-to-fit-no'])
        <title>{{ data_get($companySettings,'header_text.value',config('app.name','ERP Brand New Ideas Company')) }} - {{ __('Career') }}</title>
        @include('fragments.og', ['meta_title'=>$meta_title,'meta_desc'=>$meta_desc,'meta_image'=>$meta_image,'meta_logo'=>$meta_logo])
        @include('fragments.x', ['meta_title'=>$meta_title,'meta_desc'=>$meta_desc,'meta_image'=>$meta_image,'meta_logo'=>$meta_logo])
        @include('fragments.favicon', ['faviconUrl'=>$faviconUrl])
        <link rel="stylesheet" href="{{ asset('assets/fonts/tabler-icons.min.css') }}">
        <link rel="stylesheet" href="{{ asset('css/site.css') }}" id="stylesheet">
        @if (function_exists('issetcolor_') ? (issetcolor_(data_get($colorSettings,ST::CST_DRK)) && data_get($colorSettings,ST::CST_DRK)==='on') : (data_get($colorSettings,ST::CST_DRK)==='on'))
            <link rel="stylesheet" href="{{ asset('assets/css/style-dark.css') }}">
        @else
            <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}" id="main-style-link">
        @endif
        <link rel="stylesheet" href="{{ asset('css/custom.css') }}">
        @if (function_exists('issetcolor_') ? (issetcolor_(data_get($colorSettings,ST::CST_DRK)) && data_get($colorSettings,ST::CST_DRK)==='on') : (data_get($colorSettings,ST::CST_DRK)==='on'))
            <link rel="stylesheet" href="{{ asset('css/custom-dark.css') }}">
        @endif
        <meta name="csrf-token" content="{{ csrf_token() }}">
    </head>
    <body class="{{ $color }}">
        <div class="job-wrapper">
            <div class="job-content">
                <nav class="{{ VC::NVB }}">
                    <div class="{{ VC::CT }}">
                        @php
 $companyLogo = !empty($company_logos) ? $company_logos : SC::CPN_LG_LT_DEF;
@endphp
                        <a class="{{ VC::NVB_BR }}" href="#">
                            <img src="{{ rtrim($logo,'/').'/'.$companyLogo }}" alt="logo" style="width:90px">
                        </a>
                    </div>
                </nav>
                <section class="job-banner">
                    <div class="job-banner-bg"><img src="{{ asset('/storage/uploads/job/banner.png') }}" alt=""></div>
                    <div class="{{ VC::CT }}">
                        <div class="job-banner-content {{ VC::TXCT }} {{ VC::TXT_WT }}">
                            <h1 class="{{ VC::TXT_WT }} {{ VC::MB3 }}">{{ __(' We help') }} <br> {{ __('businesses grow') }}</h1>
                            <p>{{ __('Work there. Find the dream job you’ve always wanted..') }}</p>
                        </div>
                    </div>
                </section>
                <section class="apply-job-section">
                    <div class="{{ VC::CT }}">
                        <div class="apply-job-wrapper bg-light">
                            <div class="section-title {{ VC::TXCT }}">
                                <p><b>{{ $jobTitle }}</b></p>
                                <div class="{{ VC::DFL }} flex-wrap {{ VC::JCC }} gap-1 {{ VC::MB4 }}">
                                    @if(Utility::isFilled($skills) ?? [])
                                        @foreach($skills as $skill)
                                            <span class="badge rounded p-2 {{ VC::BG_P }}">{{ $skill }}</span>
                                        @endforeach
                                    @else
                                        <span class="badge rounded p-2 {{ VC::BG_P }}">{{ __('No skills available') }}</span>
                                    @endif
                                </div>
                                <p><i class="ti ti-map-pin ms-1"></i> {{ !empty($branchName) ? $branchName : __('No branch name available') }}</p>
                                @php
                                    try {
                                        $jobCode       = (string) data_get($job, 'code', '');
                                        $locale        = isset($currentLang) ? $currentLang : app()->getLocale();
                                        $applyBase     = VW::JB.'.apply';
                                        $applyKebab    = Str::kebab($applyBase);
                                        $applyResolved = Route::has($applyBase) ? $applyBase : (Route::has($applyKebab) ? $applyKebab : null);
                                        $applyUrl      = ($applyResolved && $jobCode !== '') ? route($applyResolved, [$jobCode, $locale]) : '#';
                                        $applyGuardMsg = Utility::fetchLinkMessage($lang, VW::JB, 'apply_job_route_unavailable')
                                                        ?? 'Apply job route is unavailable. Please contact technical support or your domain administrator.';
                                        $linkId        = 'job-apply-link-'.($jobCode !== '' ? $jobCode : 'x');
                                    } catch (\Throwable $e) {
                                        \Log::error('jobs/requirement — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                    }
@endphp
                                <a
                                    id="{{ $linkId }}"
                                    href="{{ $applyUrl }}"
                                    class="{{ VC::BT_PRM }} rounded job-apply-link"
                                    data-url="{{ $applyUrl }}"
                                    data-guard-msg="{{ base64_encode($applyGuardMsg) }}"
                                    data-sv-localized="true"
                                    {{ $applyUrl === '#' ? 'aria-disabled=true' : '' }}
                                >
                                    {{ __('Apply now') }} <i class="ti ti-send {{ VC::MS2 }}"></i>
                                </a>
                                <script defer src="{{ asset('assets/js/routes/jobs/applyRequirement.js') }}"></script>
                            </div>
                            <h3>{{ __('Requirements') }}</h3>
                            <p>{!! !empty($reqHtml) ? $reqHtml : e(__('No requirements available')) !!}</p>
                            <hr>
                            <h3>{{ __('Description') }}</h3><br>
                            {!! !empty($descHtml) ? $descHtml : e(__('No description available')) !!}
                        </div>
                    </div>
                </section>
            </div>
        </div>
        <div id="toast-container" style="position:fixed;top:1rem;right:1rem;z-index:1060"></div>
        <script src="{{ asset('assets/js/plugins/popper.min.js') }}"></script>
        <script src="{{ asset('assets/js/plugins/bootstrap.min.js') }}"></script>
        <script src="{{ asset('js/site.core.js') }}"></script>
        <script src="{{ asset('js/site.js') }}"></script>
        <script src="{{ asset('js/demo.js') }}"></script>
        <script async src="{{ asset('assets/js/plugins/perfect-scrollbar.min.js') }}"></script>
        <script async src="{{ asset('assets/js/plugins/feather.min.js') }}"></script>
        <script defer src="{{ asset('assets/js/routes/jobs/requirement.js') }}"></script>
    </body>
    @if(Utility::isFilled($get_cookie) && data_get($get_cookie,'enable_cookie')==='on' ?? [])
        @includeIf(ExtendingLayoutsConstants::CKC)
    @endif
</html>
