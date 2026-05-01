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
		$data = Utility::prepareCommonViewData(null,'uploads/logo/') ?: [];
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
		Log::error('Error preparing common view data with custom path',['exception_class'=>get_class($e),'message'=>$e->getMessage(),'file'=>$e->getFile(),'line'=>$e->getLine()]);
	} catch (\Exception $e) {
		Log::error('Exception preparing common view data with custom path',['exception_class'=>get_class($e),'message'=>$e->getMessage(),'file'=>$e->getFile(),'line'=>$e->getLine()]);
	} catch (\Throwable $e) {
		Log::error('Throwable preparing common view data with custom path',['exception_class'=>get_class($e),'message'=>$e->getMessage(),'file'=>$e->getFile(),'line'=>$e->getLine()]);
	}
	$data = Utility::fallbackSettings($data);
	$lang = Utility::fetchUserLang();
	$companySettings ??= [];
@endphp
<!DOCTYPE html>
<html lang="{{ $lang ? str_replace('_','-',is_string(app()->getLocale())?app()->getLocale():DatabaseConstants::DEFAULT_LANG) : DatabaseConstants::DEFAULT_LANG }}">
	<head>
		@include('fragments.std',['meta_title'=>$meta_title,'meta_desc'=>$meta_desc,'meta_vp'=>'shrink-to-fit=no'])
		<title>{{ data_get($companySettings,'header_text.value',config('app.name','ERP Brand New Ideas Company')) }} - {{ __('Career') }}</title>
		@include('fragments.og',['meta_title'=>$meta_title,'meta_desc'=>$meta_desc,'meta_image'=>$meta_image,'meta_logo'=>$meta_logo])
		@include('fragments.x',['meta_title'=>$meta_title,'meta_desc'=>$meta_desc,'meta_image'=>$meta_image,'meta_logo'=>$meta_logo])
		@include('fragments.favicon',['faviconUrl'=>$faviconUrl])
		<link rel="stylesheet" href="{{ asset('assets/fonts/tabler-icons.min.css') }}">
		<link rel="stylesheet" href="{{ asset('css/site.css') }}" id="stylesheet">
		@if(isset($colorSettings[ST::CST_DRK]) && $colorSettings[ST::CST_DRK]==='on')
			<link rel="stylesheet" href="{{ asset('assets/css/style-dark.css') }}">
		@else
			<link rel="stylesheet" href="{{ asset('assets/css/style.css') }}" id="main-style-link">
		@endif
		<link rel="stylesheet" href="{{ asset('css/custom.css') }}">
		@if(isset($colorSettings[ST::CST_DRK]) && $colorSettings[ST::CST_DRK]==='on')
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
				<section class="placedjob-section">
					<div class="{{ VC::CT }}">
						<div class="section-title bg-light">
							@php
 $totaljob = Job::where('created_by',$id ?? null)->count();
@endphp
							<h2 class="h1 {{ VC::MB3 }}"><span class="{{ VC::TX_PM }}">+{{ (int) $totaljob }}</span> {{ __('Job openings') }}</h2>
							<p>{{ __('Always looking for better ways to do things, innovate') }} <br> {{ __('and help people achieve their goals') }}.</p>
						</div>
						<div class="row g-4">
							@if((is_array($jobs ?? null) && count($jobs ?? [])) || (($jobs ?? null) instanceof Collection && $jobs->isNotEmpty()))
								@foreach($jobs as $job)
									@php
										try {
										    $branch = data_get($job,'branches.name');
										    $code = data_get($job,'code');
										    $langCode = data_get($job,'createdBy.lang',DatabaseConstants::DEFAULT_LANG);
										    $title = data_get($job,'title',__('No job title available'));
										    $skills = array_filter(array_map('trim',explode(',',(string) data_get($job,'skill',''))));
										    $positions = (int) data_get($job,'position',0);
										} catch (\Throwable $e) {
										    \Log::error('jobs/career — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
										}
@endphp
									<div class="{{ VC::CXL3 }} {{ VC::CL4 }} {{ VC::CM6 }} {{ VC::CS6 }} job-card">
										<div class="job-card-body">
											<div class="{{ VC::DFL }} {{ VC::MB3 }} {{ VC::ALC }} {{ VC::JCB }}">
												<img src="{{ asset('/storage/uploads/job/figma.png') }}" alt="">
												<span>{{ !empty($branch) ? $branch : __('No branch name available') }} <i class="ti ti-map-pin ms-1"></i></span>
											</div>
											<h5 class="{{ VC::MB3 }}">
												<a href="{{ $reqUrl }}" class="{{ VC::TX_DK }} job-requirement-link" data-url="{{ $reqUrl }}">{{ $title }}</a>
											</h5>
											<div class="{{ VC::DFL }} {{ VC::MB3 }} align-items-start flex-column flex-xl-row flex-md-row flex-lg-column">
												<span class="d-inline-block me-2"><i class="{{ VC::TI_CC_PLS }}"></i> {{ $positions }} {{ __('position available') }}</span>
											</div>
											<div class="{{ VC::DFL }} flex-wrap gap-1 {{ VC::ALC }}">
												@if(Utility::isFilled($skills) ?? [])
													@foreach($skills as $sk)
														<span class="badge rounded p-2 {{ VC::BG_P }}">{{ $sk }}</span>
													@endforeach
												@else
													<span class="badge rounded p-2 {{ VC::BG_P }}">{{ __('No skills available') }}</span>
												@endif
											</div>
											@php
													try {
													    $jobCode               = isset($code) ? (string) $code : '';
													    $langCodeStr           = isset($langCode) ? (string) $langCode : app()->getLocale();

													    $reqBase               = VW::JB.'.requirement';
													    $reqKebab              = Str::kebab($reqBase);
													    $reqResolved           = Route::has($reqBase) ? $reqBase : (Route::has($reqKebab) ? $reqKebab : null);
													    $reqUrl                = ($reqResolved && $jobCode !== '') ? route($reqResolved, [$jobCode, $langCodeStr]) : '#';

													    $reqGuardMsg           = Utility::fetchLinkMessage($lang, VW::JB, 'requirement_job_route_unavailable')
													    													?? 'Job requirement route is unavailable. Please contact technical support or your domain administrator.';

													    $reqLinkId             = 'job-requirement-link-'.($jobCode !== '' ? $jobCode : 'x');
													} catch (\Throwable $e) {
													    \Log::error('jobs/career — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
													}
@endphp

											<a
													id="{{ $reqLinkId }}"
													href="{{ $reqUrl }}"
													class="{{ VC::BT_PRM }} {{ VC::W100 }} {{ VC::MT4 }} job-requirement-link"
													data-url="{{ $reqUrl }}"
													data-guard-msg="{{ base64_encode($reqGuardMsg) }}"
													data-sv-localized="true"
													{{ $reqUrl === '#' ? 'aria-disabled=true' : '' }}
											>
													{{ __('Read more') }}
											</a>
											<script defer src="{{ asset('assets/js/routes/jobs/requirementLink.js') }}"></script>
										</div>
									</div>
								@endforeach
							@else
								<div class="{{ VC::C12 }}"><p class="{{ VC::TXCT }} {{ VC::MY4 }}">{{ __('No jobs available') }}</p></div>
							@endif
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
				<script defer src="{{ asset('assets/js/routes/jobs/career.js') }}"></script>
        @if(is_array($get_cookie) && ($get_cookie['enable_cookie'] ?? '') == 'on')
            @includeIf(ExtendingLayoutsConstants::CKC)
        @endif
	</body>
</html>
