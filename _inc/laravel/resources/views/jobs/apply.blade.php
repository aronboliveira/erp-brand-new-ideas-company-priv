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
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', is_string(app()->getLocale()) ? app()->getLocale() : DatabaseConstants::DEFAULT_LANG) }}" dir="{{ $siteRtl === 'on' ? 'rtl' : 'ltr' }}">
    <head>
        <title>
            {{ !empty($companySettings['header_text']) ? $companySettings['header_text']->value : config('app.name', 'ERP Nova Prestech') }}
            - {{ __('Career') }}</title>
        @include('fragments.std', [
            'meta_title' => $meta_title,
            'meta_desc' => $meta_desc,
            'meta_vp' => 'shrink-to-fit=no',
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
                                <h2 class="h1 mb-3"> {{$job->title}}</h2>
                                <div class="d-flex flex-wrap justify-content-center gap-1 mb-4">
                                    @foreach (explode(',', $job->skill) as $skill)
                                        <span class="badge rounded p-2 bg-primary">{{ $skill }}</span>
                                    @endforeach
                                </div>
                                @if(!empty($job->branches)?$job->branches->name:'')
                                    <p> <i class="ti ti-map-pin ms-1"></i> {{!empty($job->branches)?$job->branches->name:''}}</p>
                                @endif
                            </div>
                            <div class="apply-job-form">
                                <h2 class="mb-4">{{__('Apply for this job')}}</h2>
                                {{Collective\Html\FormFacade::open(array('route'=>array('job.apply.data',$job->code),'method'=>'post', 'enctype' => "multipart/form-data"))}}
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            {{Collective\Html\FormFacade::label('name',__('Name'),['class'=>'form-label'])}}
                                            {{Collective\Html\FormFacade::text('name',null,array('class'=>'form-control name','required'=>'required'))}}
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            {{Collective\Html\FormFacade::label('email',__('Email'),['class'=>'form-label'])}}
                                            {{Collective\Html\FormFacade::text('email',null,array('class'=>'form-control','required'=>'required'))}}
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            {{Collective\Html\FormFacade::label('phone',__('Phone'),['class'=>'form-label'])}}
                                            {{Collective\Html\FormFacade::text('phone',null,array('class'=>'form-control','required'=>'required'))}}
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        @if(!empty($job->applicant) && in_array('dob',explode(',',$job->applicant)))
                                            <div class="form-group">
                                                {!! Collective\Html\FormFacade::label('dob', __('Date of Birth'),['class'=>'form-label']) !!}
                                                {!! Collective\Html\FormFacade::date('dob', old('dob'), ['class' => 'form-control datepicker w-100','required'=>'required']) !!}
                                            </div>
                                        @endif
                                    </div>
                                    @if(!empty($job->applicant) && in_array('gender',explode(',',$job->applicant)))
                                        <div class="form-group col-md-6 ">
                                            {!! Collective\Html\FormFacade::label('gender', __('Gender'),['class'=>'form-label']) !!}
                                            <div class="d-flex radio-check">
                                                <div class="custom-control custom-radio custom-control-inline">
                                                    <input type="radio" id="g_male" value="Male" name="gender" class="custom-control-input" >
                                                    <label class="custom-control-label" for="g_male">{{__('Male')}}</label>
                                                </div>
                                                <div class="custom-control custom-radio custom-control-inline">
                                                    <input type="radio" id="g_female" value="Female" name="gender" class="custom-control-input">
                                                    <label class="custom-control-label" for="g_female">{{__('Female')}}</label>
                                                </div>
                                            </div>
                                        </div>
                                    @endif

                                    @if(!empty($job->applicant) && in_array('country',explode(',',$job->applicant)))
                                        <div class="form-group col-md-6 ">
                                            {{Collective\Html\FormFacade::label('country',__('Country'),['class'=>'form-label'])}}
                                            {{Collective\Html\FormFacade::text('country',null,array('class'=>'form-control','required'=>'required'))}}
                                        </div>
                                        <div class="form-group col-md-6 country">
                                            {{Collective\Html\FormFacade::label('state',__('State'),['class'=>'form-label'])}}
                                            {{Collective\Html\FormFacade::text('state',null,array('class'=>'form-control','required'=>'required'))}}
                                        </div>
                                        <div class="form-group col-md-6 country">
                                            {{Collective\Html\FormFacade::label('city',__('City'),['class'=>'form-label'])}}
                                            {{Collective\Html\FormFacade::text('city',null,array('class'=>'form-control','required'=>'required'))}}
                                        </div>
                                    @endif

                                    @if(!empty($job->visibility) && in_array('profile',explode(',',$job->visibility)))
                                        <div class="form-group col-md-6 ">
                                            {{Collective\Html\FormFacade::label('profile',__('Profile'),['class'=>'col-form-label'])}}
                                            <input type="file" class="form-control" name="profile" id="profile" data-filename="profile_create" onchange="document.getElementById('blah').src = window.URL.createObjectURL(this.files[0])">
                                            <img id="blah" src="" class="mt-3" width="25%"/>
                                            <p class="profile_create"></p>
                                        </div>
                                    @endif

                                    @if(!empty($job->visibility) && in_array('resume',explode(',',$job->visibility)))
                                        <div class="form-group col-md-6 ">
                                            {{Collective\Html\FormFacade::label('resume',__('CV / Resume'),['class'=>'col-form-label'])}}
                                            <input type="file" class="form-control" name="resume" id="resume" data-filename="resume_create" onchange="document.getElementById('blah1').src = window.URL.createObjectURL(this.files[0])" required>
                                            <img id="blah1" class="mt-3" src="" width="25%"/>
                                            <p class="resume_create"></p>
                                        </div>
                                    @endif

                                    @if(!empty($job->visibility) && in_array('letter',explode(',',$job->visibility)))
                                        <div class="form-group col-md-12 ">
                                            {{Collective\Html\FormFacade::label('cover_letter',__('Cover Letter'),['class'=>'form-label'])}}
                                            {{Collective\Html\FormFacade::textarea('cover_letter',null,array('class'=>'form-control','rows'=>'3'))}}
                                        </div>
                                    @endif

                                    @foreach($questions as $question)
                                        <div class="form-group col-md-12  question question_{{$question->id}}">
                                            {{Collective\Html\FormFacade::label($question->question,$question->question,['class'=>'form-label'])}}
                                            <input type="text" class="form-control" name="question[{{$question->question}}]" {{($question->is_required=='yes')?'required':''}}>
                                        </div>
                                    @endforeach
                                    <div class="col-12">
                                        <div class="text-center mt-4">
                                            <button type="submit" class="btn btn-primary">{{__('Submit your application')}}</button>
                                        </div>
                                    </div>
                                </div>
                                {{Collective\Html\FormFacade::close()}}
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
        <div class="position-fixed top-0 end-0 p-3" style="z-index: 99999">
            <div id="liveToast" class="toast text-white  fade" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body"> </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
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
        <script defer src="{{ asset('assets/js/plugins/sweetalert2.all.min.js') }}"></script>
        @if($message = Session::get('success'))
            <script>
               typeof show_toastr === 'function' &&  show_toastr('success', '{!! $message !!}');
            </script>
        @endif
        @if($message = Session::get('error'))
            <script>
                typeof show_toastr === 'function' && show_toastr('error', '{!! $message !!}');
            </script>
        @endif
        @if($get_cookie['enable_cookie'] == 'on')
            @includeIf(ExtendingLayoutsConstants::CKC)
        @endif
    </body>
</html>
