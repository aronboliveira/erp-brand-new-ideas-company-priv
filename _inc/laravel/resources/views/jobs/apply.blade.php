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
		$data = Utility::prepareCommonViewData() ?: [];
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
			'Error preparing common view data',
			[
				'exception_class' => get_class($e),
				'message' => $e->getMessage(),
				'file' => $e->getFile(),
				'line' => $e->getLine()
			]
		);
	} catch (\Exception $e) {
		Log::error(
			'Exception preparing common view data',
			[
				'exception_class' => get_class($e),
				'message' => $e->getMessage(),
				'file' => $e->getFile(),
				'line' => $e->getLine()
			]
		);
	} catch (\Throwable $e) {
		Log::error(
			'Throwable preparing common view data',
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
    $siteRtl = data_get($data,SC::SITE_RTL,'off');
@endphp
<!DOCTYPE html>
<html lang="{{ $lang ? str_replace('_','-',is_string($lang)?$lang:DatabaseConstants::DEFAULT_LANG) : DatabaseConstants::DEFAULT_LANG }}" dir="{{ $siteRtl === 'on' ? 'rtl' : 'ltr' }}">
    <head>
        <title>{{ data_get($companySettings,'header_text.value',config('app.name','ERP Nova Prestech')) }} - {{ __('Career') }}</title>
        @include('fragments.std',['meta_title'=>$meta_title,'meta_desc'=>$meta_desc,'meta_vp'=>'shrink-to-fit=no'])
        @include('fragments.og',['meta_title'=>$meta_title,'meta_desc'=>$meta_desc,'meta_image'=>$meta_image,'meta_logo'=>$meta_logo])
        @include('fragments.x',['meta_title'=>$meta_title,'meta_desc'=>$meta_desc,'meta_image'=>$meta_image,'meta_logo'=>$meta_logo])
        @include('fragments.favicon',['faviconUrl'=>$faviconUrl])
        <link rel="stylesheet" href="{{ asset('assets/fonts/tabler-icons.min.css') }}">
        <link rel="stylesheet" href="{{ asset('css/site.css') }}" id="stylesheet">
        @php
 $darkOn = function_exists('issetcolor_') ? (issetcolor_(data_get($colorSettings,SC::CST_DRK)) && data_get($colorSettings,SC::CST_DRK)==='on') : (data_get($colorSettings,SC::CST_DRK)==='on');
@endphp
        @if($darkOn)
            <link rel="stylesheet" href="{{ asset('assets/css/style-dark.css') }}">
        @else
            <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}" id="main-style-link">
        @endif
        <link rel="stylesheet" href="{{ asset('css/custom.css') }}">
        @if($darkOn)
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
                            <p>{{ __('Work there. Find the dream job you’ve always wanted.') }}</p>
                        </div>
                    </div>
                </section>
                <section class="apply-job-section">
                    <div class="{{ VC::CT }}">
                        <div class="apply-job-wrapper bg-light">
                            <div class="section-title {{ VC::TXCT }}">
                                <h2 class="h1 {{ VC::MB3 }}">{{ data_get($job,'title',__('No job title available')) }}</h2>
                                @php
 $skills = array_filter(array_map('trim',explode(',',(string) data_get($job,'skill',''))));
@endphp
                                <div class="{{ VC::DFL }} flex-wrap {{ VC::JCC }} gap-1 {{ VC::MB4 }}">
                                    @if(Utility::isFilled($skills) ?? [])
                                        @foreach($skills as $skill)
                                            <span class="badge rounded p-2 {{ VC::BG_P }}">{{ !empty($skill) ? $skill : __('Undefined skill') }}</span>
                                        @endforeach
                                    @else
                                        <span class="badge rounded p-2 {{ VC::BG_P }}">{{ __('No skills available') }}</span>
                                    @endif
                                </div>
                                @php
 $branchName = data_get($job,'branches.name');
@endphp
                                <p><i class="ti ti-map-pin ms-1"></i> {{ !empty($branchName) ? $branchName : __('No branch name available') }}</p>
                            </div>
                            <div class="apply-job-form">
                                <h2 class="{{ VC::MB4 }}">{{ __('Apply for this job') }}</h2>
                                @php
                                    try {
                                        $jobCode              = (string) data_get($job ?? null, 'code', '');
                                        $applyStoreBase       = VW::JB.'.apply.data';
                                        $applyStoreKebab      = Str::kebab($applyStoreBase);
                                        $applyStoreResolved   = Route::has($applyStoreBase) ? $applyStoreBase : (Route::has($applyStoreKebab) ? $applyStoreKebab : null);
                                        $applyStoreUrl        = ($applyStoreResolved && $jobCode !== '') ? route($applyStoreResolved, $jobCode) : '#';
                                        $applyFormId          = 'job-apply-store-form-'.($jobCode !== '' ? $jobCode : 'x');
                                        $applyGuardMsg        = Utility::fetchLinkMessage($lang, VW::JB, 'apply_data_route_unavailable') ?? 'Apply data route is unavailable. Please contact technical support or your domain administrator.';

                                        $applicantFieldsRaw   = (string) data_get($job, 'applicant', '');
                                        $applicantFields      = array_filter(array_map('trim', explode(',', $applicantFieldsRaw)));

                                        $visibilityRaw        = (string) data_get($job, 'visibility', '');
                                        $visibilityFields     = array_filter(array_map('trim', explode(',', $visibilityRaw)));

                                        $hasQuestions         = (is_array($questions ?? null) && count($questions ?? []) > 0) || (($questions ?? null) instanceof Collection && $questions->isNotEmpty());
                                    } catch (\Throwable $e) {
                                        \Log::error('jobs/apply — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                    }
@endphp
                                {{ Form::open([
                                    'url'               => $applyStoreUrl,
                                    'method'            => 'POST',
                                    'enctype'           => 'multipart/form-data',
                                    'id'                => $applyFormId,
                                    'data-url'          => $applyStoreUrl,
                                    'data-guard-msg'    => $applyGuardMsg,
                                    'data-sv-localized' => 'true',
                                    'class'             => 'w-100',
                                ]) }}
                                    @csrf
                                    <div class="row">
                                        <div class="{{ VC::CM6 }}">
                                            <div class="{{ VC::FM_G }}">
                                                {!! Form::label('name', __('Name'), ['class' => 'form-label']) !!}
                                                {!! Form::text('name', null, ['class' => 'form-control name', 'required' => 'required', 'autocomplete' => 'off']) !!}
                                            </div>
                                        </div>
                                        <div class="{{ VC::CM6 }}">
                                            <div class="{{ VC::FM_G }}">
                                                {!! Form::label('email', __('Email'), ['class' => 'form-label']) !!}
                                                {!! Form::text('email', null, ['class' => 'form-control', 'required' => 'required', 'autocomplete' => 'off']) !!}
                                            </div>
                                        </div>
                                        <div class="{{ VC::CM6 }}">
                                            <div class="{{ VC::FM_G }}">
                                                {!! Form::label('phone', __('Phone'), ['class' => 'form-label']) !!}
                                                {!! Form::text('phone', null, ['class' => 'form-control', 'required' => 'required', 'autocomplete' => 'off']) !!}
                                            </div>
                                        </div>

                                        @if(!empty($applicantFields) && is_array($applicantFields) && in_array('dob', $applicantFields, true))
                                            <div class="{{ VC::CM6 }}">
                                                <div class="{{ VC::FM_G }}">
                                                    {!! Form::label('dob', __('Date of Birth'), ['class' => 'form-label']) !!}
                                                    {!! Form::date('dob', old('dob'), ['class' => 'form-control datepicker w-100', 'required' => 'required']) !!}
                                                </div>
                                            </div>
                                        @endif

                                        @if(!empty($applicantFields) && is_array($applicantFields) && in_array('gender', $applicantFields, true))
                                            <div class="{{ VC::FM_GCB6 }}">
                                                {!! Form::label('gender', __('Gender'), ['class' => 'form-label']) !!}
                                                <div class="{{ VC::DFL }} radio-check">
                                                    <div class="{{ VC::CST_CTL }} custom-radio custom-control-inline">
                                                        <input type="radio" id="g_male" value="Male" name="gender" class="custom-control-input">
                                                        <label class="{{ VC::CST_LB }}" for="g_male">{{ __('Male') }}</label>
                                                    </div>
                                                    <div class="{{ VC::CST_CTL }} custom-radio custom-control-inline">
                                                        <input type="radio" id="g_female" value="Female" name="gender" class="custom-control-input">
                                                        <label class="{{ VC::CST_LB }}" for="g_female">{{ __('Female') }}</label>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif

                                        @if(!empty($applicantFields) && is_array($applicantFields) && in_array('country', $applicantFields, true))
                                            <div class="{{ VC::FM_GCB6 }}">
                                                {!! Form::label('country', __('Country'), ['class' => 'form-label']) !!}
                                                {!! Form::text('country', null, ['class' => 'form-control', 'required' => 'required', 'autocomplete' => 'off']) !!}
                                            </div>
                                            <div class="{{ VC::FM_GCB6 }} country">
                                                {!! Form::label('state', __('State'), ['class' => 'form-label']) !!}
                                                {!! Form::text('state', null, ['class' => 'form-control', 'required' => 'required', 'autocomplete' => 'off']) !!}
                                            </div>
                                            <div class="{{ VC::FM_GCB6 }} country">
                                                {!! Form::label('city', __('City'), ['class' => 'form-label']) !!}
                                                {!! Form::text('city', null, ['class' => 'form-control', 'required' => 'required', 'autocomplete' => 'off']) !!}
                                            </div>
                                        @endif

                                        @if(!empty($visibilityFields) && is_array($visibilityFields) && in_array('profile', $visibilityFields, true))
                                            <div class="{{ VC::FM_GCB6 }}">
                                                {!! Form::label('profile', __('Profile'), ['class' => 'col-form-label']) !!}
                                                <input type="file" class="{{ VC::FM_CT }}" name="profile" id="profile" data-filename="profile_create">
                                                <img id="profile_preview" src="" class="{{ VC::MT3 }}" style="width:25%; display:none;">
                                                <p class="profile_create"></p>
                                            </div>
                                        @endif

                                        @if(!empty($visibilityFields) && is_array($visibilityFields) && in_array('resume', $visibilityFields, true))
                                            <div class="{{ VC::FM_GCB6 }}">
                                                {!! Form::label('resume', __('CV / Resume'), ['class' => 'col-form-label']) !!}
                                                <input type="file" class="{{ VC::FM_CT }}" name="resume" id="resume" data-filename="resume_create" required>
                                                <img id="resume_preview" class="{{ VC::MT3 }}" src="" style="width:25%; display:none;">
                                                <p class="resume_create"></p>
                                            </div>
                                        @endif

                                        @if(!empty($visibilityFields) && is_array($visibilityFields) && in_array('letter', $visibilityFields, true))
                                            <div class="{{ VC::FM_GCB12 }}">
                                                {!! Form::label('cover_letter', __('Cover Letter'), ['class' => 'form-label']) !!}
                                                {!! Form::textarea('cover_letter', null, ['class' => 'form-control', 'rows' => 3, 'autocomplete' => 'off']) !!}
                                            </div>
                                        @endif

                                        @if($hasQuestions)
                                            @foreach($questions as $question)
                                                @php
                                                    try {
                                                        $qId     = (string) data_get($question, 'id', '');
                                                        $qText   = (string) data_get($question, 'question', __('No question available'));
                                                        $qReq    = (string) data_get($question, 'is_required', '') === 'yes';
                                                        $nameKey = (string) data_get($question, 'question', '');
                                                    } catch (\Throwable $e) {
                                                        \Log::error('jobs/apply — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                    }
@endphp
                                                <div class="{{ VC::FM_GCB12 }} question question_{{ $qId }}">
                                                    {!! Form::label($qText, $qText, ['class' => 'form-label']) !!}
                                                    <input type="text" class="{{ VC::FM_CT }}" name="question[{{ $nameKey }}]" {{ $qReq ? 'required' : '' }} autocomplete="off">
                                                </div>
                                            @endforeach
                                        @else
                                            <div class="{{ VC::FM_GCB12 }}">
                                                <p>{{ __('No questions available') }}</p>
                                            </div>
                                        @endif

                                        <div class="{{ VC::C12 }}">
                                            <div class="{{ VC::TXCT }} {{ VC::MT4 }}">
                                                <button type="submit" class="{{ VC::BT_PRM }}">{{ __('Submit your application') }}</button>
                                            </div>
                                        </div>
                                    </div>
                                    <script defer src="{{ asset('assets/js/routes/jobs/applyStore.js') }}"></script>
                                    <script defer src="{{ asset('assets/js/routes/jobs/applyPreviews.js') }}"></script>
                                {{ Form::close() }}
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
        <div class="position-fixed top-0 end-0 p-3" style="z-index:99999">
            <div id="liveToast" class="toast {{ VC::TXT_WT }} fade" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="{{ VC::DFL }}">
                    <div class="toast-body"></div>
                    <button type="button" class="{{ VC::BT_CL }} btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="{{ __('Close') }}"></button>
                </div>
            </div>
        </div>
        <script src="{{ asset('assets/js/plugins/popper.min.js') }}"></script>
        <script src="{{ asset('assets/js/plugins/bootstrap.min.js') }}"></script>
        <script src="{{ asset('js/site.core.js') }}"></script>
        <script src="{{ asset('js/site.js') }}"></script>
        <script src="{{ asset('js/demo.js') }} "></script>
        <script src="{{ asset('js/custom.js') }}"></script>
        <script async src="{{ asset('assets/js/plugins/perfect-scrollbar.min.js') }}"></script>
        <script async src="{{ asset('assets/js/plugins/feather.min.js') }}"></script>
        <script defer src="{{ asset('assets/js/routes/jobs/apply.js') }}"></script>
        <script defer src="{{ asset('assets/js/plugins/sweetalert2.all.min.js') }}"></script>
        @if($message = Session::get('success'))
            <script>
                (() => {typeof show_toastr === 'function' && show_toastr('success', '{!! $message !!}');})()
            </script>
        @endif
        @if($message = Session::get('error'))
            <script>
                (() => {typeof show_toastr === 'function' && show_toastr('error', '{!! $message !!}');})()
            </script>
        @endif
        @if($get_cookie['enable_cookie'] == 'on')
            @includeIf(ExtendingLayoutsConstants::CKC)
        @endif
    </body>
</html>
