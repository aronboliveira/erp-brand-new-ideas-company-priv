@php
$lang ??= 'en';
	$jobId ??= '';
	$formId ??= 'job-update-form-x';
	$updBase ??= '';
	$updKebab ??= '';
	$updResolved ??= null;
	$updUrl ??= '#';
	$updGuardMsg ??= '';
	$genResolved ??= null;
	$genUrl ??= '#';
	$genGuardMsg ??= '';
	$gramResolved ??= null;
	$gramUrl ??= '#';
	$gramGuardMsg ??= '';
	$branchesIsList ??= false;
	$branchesOptions ??= [];
	$branchAttrs ??= [];
	$categoriesIsList ??= false;
	$categoriesOptions ??= [];
	$categoryAttrs ??= [];
	$statusIsList ??= false;
	$statusOptions ??= [];
	$statusAttrs ??= [];
	$customQIsList ??= false;
	$applicantArr ??= [];
	$visibilityArr ??= [];
	$plan ??= null;
	try {
		$lang = Utility::fetchUserLang() ?? 'en';
		$jobId = (string) data_get($job ?? null, 'id', '');
		$formId = 'job-update-form-' . ($jobId === '' ? 'x' : $jobId);
		$updBase = VW::JB . '.update';
		$updKebab = Str::kebab($updBase);
		$updResolved = Route::has($updBase) ? $updBase : (Route::has($updKebab) ? $updKebab : null);
		$updUrl = ($updResolved && $jobId !== '') ? (route($updResolved, [$jobId]) ?? '#') : '#';
		$updGuardMsg = Utility::fetchLinkMessage($lang, VW::JB, 'update_route_unavailable') ?? __('Job update route is unavailable. Please contact technical support or your domain administrator.');
		$genResolved = Route::has('generate') ? 'generate' : (Route::has(Str::kebab('generate')) ? Str::kebab('generate') : null);
		$genUrl = ($genResolved && $jobId !== '') ? (route($genResolved, [$jobId]) ?? '#') : '#';
		$genGuardMsg = Utility::fetchLinkMessage($lang, VW::JB, 'generate_route_unavailable') ?? __('AI generate route for jobs is unavailable. Please contact technical support or your domain administrator.');
		$gramResolved = Route::has('grammar') ? 'grammar' : (Route::has(Str::kebab('grammar')) ? Str::kebab('grammar') : null);
		$gramUrl = ($gramResolved && $jobId !== '') ? (route($gramResolved, [$jobId]) ?? '#') : '#';
		$gramGuardMsg = Utility::fetchLinkMessage($lang, 'generics', 'grammar_check_route_unavailable') ?? __('Grammar check route is unavailable. Please contact technical support or your domain administrator.');
		$branchesIsList = (is_array($branches ?? null) && count($branches ?? [])) || (($branches ?? null) instanceof Collection && $branches->isNotEmpty());
		$branchesOptions = $branchesIsList ? (is_array($branches) ? $branches : $branches->toArray()) : ['' => __('No branches available')];
		$branchAttrs = ['class' => VC::FM_CT_SL . ' select', 'required' => 'required'];
		if (!$branchesIsList) { $branchAttrs['disabled'] = 'disabled'; }
		$categoriesIsList = (is_array($categories ?? null) && count($categories ?? [])) || (($categories ?? null) instanceof Collection && $categories->isNotEmpty());
		$categoriesOptions = $categoriesIsList ? (is_array($categories) ? $categories : $categories->toArray()) : ['' => __('No job categories available')];
		$categoryAttrs = ['class' => VC::FM_CT_SL . ' select', 'required' => 'required'];
		if (!$categoriesIsList) { $categoryAttrs['disabled'] = 'disabled'; }
		$statusIsList = (is_array($status ?? null) && count($status ?? [])) || (($status ?? null) instanceof Collection && $status->isNotEmpty());
		$statusOptions = $statusIsList ? (is_array($status) ? $status : $status->toArray()) : ['' => __('No statuses available')];
		$statusAttrs = ['class' => VC::FM_CT_SL . ' select', 'required' => 'required'];
		if (!$statusIsList) { $statusAttrs['disabled'] = 'disabled'; }
		$customQIsList = (is_array($customQuestion ?? null) && count($customQuestion ?? [])) || (($customQuestion ?? null) instanceof Collection && $customQuestion->isNotEmpty());
		$applicantArr = is_array(data_get($job ?? null, 'applicant')) ? $job->applicant : [];
		$visibilityArr = is_array(data_get($job ?? null, 'visibility')) ? $job->visibility : [];
		$plan = Utility::getChatGPTSettings();
	} catch (\Error $e) {
		Log::error('Error in jobs/edit.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\Exception $e) {
		Log::error('Exception in jobs/edit.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\Throwable $e) {
		Log::error('Throwable in jobs/edit.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	}
@endphp

@extends(EL::ADM)

@section(YW::ADM_PG_TTL)
    {{ __('Edit Job') }}
@endsection

@section(YW::ADM_BDC)
    <li class="{{ VC::BCI }}">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}" {{ Route::has('dashboard') ? '' : 'aria-disabled=true' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    @php
        $jobIndexBase ??= '';
        $jobIndexKebab ??= '';
        $jobIndexName ??= null;
        $jobIndexUrl ??= '#';
        $jobIndexGuard ??= '';
        try {
            $jobIndexBase = VW::JB.'.index';
            $jobIndexKebab = Str::kebab($jobIndexBase);
            $jobIndexName = Route::has($jobIndexBase) ? $jobIndexBase : (Route::has($jobIndexKebab) ? $jobIndexKebab : null);
            $jobIndexUrl = $jobIndexName ? (route($jobIndexName) ?? '#') : '#';
            $jobIndexGuard = Utility::fetchLinkMessage($lang, VW::JB, 'job_index_route_unavailable') ?? 'Job index route is unavailable. Please contact technical support or your domain administrator.';
        } catch (\Error $e) {
            Log::error('Error in jobs/edit.blade.php breadcrumb @php block', [
                'exception_class' => get_class($e),
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
        } catch (\Exception $e) {
            Log::error('Exception in jobs/edit.blade.php breadcrumb @php block', [
                'exception_class' => get_class($e),
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
        } catch (\Throwable $e) {
            Log::error('Throwable in jobs/edit.blade.php breadcrumb @php block', [
                'exception_class' => get_class($e),
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
        }
@endphp
    <li class="{{ VC::BCI }}">
        <a
            id="bc-job-index-link"
            href="{{ $jobIndexUrl }}"
            data-url="{{ $jobIndexUrl }}"
            data-guard-msg="{{ base64_encode($jobIndexGuard) }}"
            data-sv-localized="true"
        >
            {{ __('Job') }}
        </a>
    </li>
    @push(ST::ADM_SCR_PG)
        <script defer src="{{ asset('assets/js/routes/jobs/indexBc.js') }}"></script>
    @endpush
    <li class="{{ VC::BCI }}">{{ __('Job Edit') }}</li>
@endsection

@push(ST::ADM_CSS)
    <link rel="stylesheet" href="{{ asset('css/summernote/summernote-bs4.css') }}">
    <link href="{{ asset('css/bootstrap-tagsinput.css') }}" rel="stylesheet"/>
@endpush

@push(ST::ADM_SCR_PG)
    <script src="{{ asset('css/summernote/summernote-bs4.js') }}"></script>
    <script async src="{{ asset('js/bootstrap-tagsinput.min.js') }}"></script>
    <script async src="{{ asset('assets/js/routes/jobs/lang/edit.js') }}"></script>
    <script defer src="{{ asset('assets/js/routes/jobs/edit.js') }}"></script>
@endpush

@section(YW::ADM_ACT_BTN)
    @if($plan?->{PL::COL_GPT} == 1)
        <div class="{{ VC::FEND }}">
            <a href="{{ $genUrl }}" data-size="lg" class="{{ VC::BT_PRM }} btn-icon btn-sm ai-btn"
               data-ajax-popup-over="true"
               data-url="{{ $genUrl }}"
               data-guard-msg="{{ base64_encode($genGuardMsg) }}"
               data-bs-placement="top"
               data-title="{{ __('Generate content with AI') }}">
                <i class="{{ VC::FAS_RB }}"></i> <span>{{ __('Generate with AI') }}</span>
            </a>
        </div>
    @endif
@endsection

@section(YW::ADM_CTT)
    @if(!empty($job) && isset($job->id))
        {{ Form::model($job, [
            'url'               => $updUrl,
            'method'            => 'PUT',
            'id'                => $formId,
            'data-url'          => $updUrl,
            'data-guard-msg'    => $updGuardMsg,
            'data-sv-localized' => 'true',
        ]) }}
            <div class="{{ VC::RW }} mt-3">
                <div class="{{ VC::CLM6 }}">
                    <div class="{{ VC::CD_FL }}">
                        <div class="{{ VC::CD_BD }} job-create">
                            <div class="{{ VC::RW }}">
                                <div class="form-group {{ VC::CM12 }}">
                                    {!! Form::label('title', __('Job Title'), ['class'=>VC::FM_LB]) !!}
                                    {!! Form::text('title', null, ['class'=>VC::FM_CT,'required'=>'required']) !!}
                                </div>
                                <div class="form-group {{ VC::CM6 }}">
                                    {!! Form::label('branch', __('Branch'), ['class'=>VC::FM_LB]) !!}
                                    {{ Form::select('branch', $branchesOptions, null, $branchAttrs) }}
                                </div>
                                <div class="form-group {{ VC::CM6 }}">
                                    {!! Form::label('category', __('Job Category'), ['class'=>VC::FM_LB]) !!}
                                    {{ Form::select('category', $categoriesOptions, null, $categoryAttrs) }}
                                </div>
                                <div class="form-group {{ VC::CM6 }}">
                                    {!! Form::label('position', __('Positions'), ['class'=>VC::FM_LB]) !!}
                                    {!! Form::text('position', null, ['class'=>VC::FM_CT,'required'=>'required']) !!}
                                </div>
                                <div class="form-group {{ VC::CM6 }}">
                                    {!! Form::label('status', __('Status'), ['class'=>VC::FM_LB]) !!}
                                    {{ Form::select('status', $statusOptions, null, $statusAttrs) }}
                                </div>
                                <div class="form-group {{ VC::CM6 }}">
                                    {!! Form::label('start_date', __('Start Date'), ['class'=>VC::FM_LB]) !!}
                                    {!! Form::date('start_date', null, ['class'=>VC::FM_CT]) !!}
                                </div>
                                <div class="form-group {{ VC::CM6 }}">
                                    {!! Form::label('end_date', __('End Date'), ['class'=>VC::FM_LB]) !!}
                                    {!! Form::date('end_date', null, ['class'=>VC::FM_CT]) !!}
                                </div>
                                <div class="form-group {{ VC::CM12 }}">
                                    <input type="text" class="{{ VC::FM_CT }}" value="{{ !empty($job->skill) ? $job->skill : __('No name fetched for skill') }}" data-toggle="tags" name="skill" placeholder="Skill"/>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="{{ VC::CLM6 }}">
                    <div class="{{ VC::CD_FL }}">
                        <div class="{{ VC::CD_BD }} job-create">
                            <div class="{{ VC::RW }}">
                                <div class="{{ VC::CM6 }}">
                                    <div class="{{ VC::FM_G }}">
                                        <h6>{{ __('Need to ask ?') }}</h6>
                                        <div class="{{ VC::MY4 }}">
                                            <div class="{{ VC::FM_CHK }} {{ VC::CST_CB }}">
                                                <input type="checkbox" class="form-check-input" name="applicant[]" value="gender" id="check-gender" {{ in_array('gender', $applicantArr) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="check-gender">{{ __('Gender') }}</label>
                                            </div>
                                            <div class="{{ VC::FM_CHK }} {{ VC::CST_CB }}">
                                                <input type="checkbox" class="form-check-input" name="applicant[]" value="dob" id="check-dob" {{ in_array('dob', $applicantArr) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="check-dob">{{ __('Date Of Birth') }}</label>
                                            </div>
                                            <div class="{{ VC::FM_CHK }} {{ VC::CST_CB }}">
                                                <input type="checkbox" class="form-check-input" name="applicant[]" value="country" id="check-country" {{ in_array('country', $applicantArr) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="check-country">{{ __('Country') }}</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="{{ VC::CM6 }}">
                                    <div class="{{ VC::FM_G }}">
                                        <h6>{{ __('Need to show option ?') }}</h6>
                                        <div class="{{ VC::MY4 }}">
                                            <div class="{{ VC::FM_CHK }} {{ VC::CST_CB }}">
                                                <input type="checkbox" class="form-check-input" name="visibility[]" value="profile" id="check-profile" {{ in_array('profile', $visibilityArr) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="check-profile">{{ __('Profile Image') }}</label>
                                            </div>
                                            <div class="{{ VC::FM_CHK }} {{ VC::CST_CB }}">
                                                <input type="checkbox" class="form-check-input" name="visibility[]" value="resume" id="check-resume" {{ in_array('resume', $visibilityArr) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="check-resume">{{ __('Resume') }}</label>
                                            </div>
                                            <div class="{{ VC::FM_CHK }} {{ VC::CST_CB }}">
                                                <input type="checkbox" class="form-check-input" name="visibility[]" value="letter" id="check-letter" {{ in_array('letter', $visibilityArr) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="check-letter">{{ __('Cover Letter') }}</label>
                                            </div>
                                            <div class="{{ VC::FM_CHK }} {{ VC::CST_CB }}">
                                                <input type="checkbox" class="form-check-input" name="visibility[]" value="terms" id="check-terms" {{ in_array('terms', $visibilityArr) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="check-terms">{{ __('Terms And Conditions') }}</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group {{ VC::CM12 }}">
                                    <h6>{{ __('Custom Question') }}</h6>
                                    <div class="{{ VC::MY4 }}">
                                        @if($customQIsList)
                                            @foreach($customQuestion as $question)
                                                <div class="{{ VC::FM_CHK }} {{ VC::CST_CB }}">
                                                    <input type="checkbox" class="form-check-input" name="custom_question[]" value="{{ data_get($question,'id','') }}" id="custom_question_{{ data_get($question,'id','x') }}" {{ in_array(data_get($question,'id',''), $job->custom_question ?? []) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="custom_question_{{ data_get($question,'id','x') }}">{{ data_get($question,'question',__('Question data is unavailable')) }}</label>
                                                </div>
                                            @endforeach
                                        @else
                                            <div class="{{ VC::TXT_MT }}">{{ __('No custom questions available.') }}</div>
                                        @endif
                                    </div>
                                </div>

                                <div class="{{ VC::CM12 }} {{ VC::DFL_JCB }}">
                                    <div></div>
                                    @if($plan?->{PL::COL_GPT} == 1)
                                        <a href="{{ $gramUrl }}" data-size="md" class="{{ VC::BT_PRM }} btn-icon btn-sm grammar-btn"
                                        data-ajax-popup-over="true"
                                        id="grammarCheck"
                                        data-url="{{ $gramUrl }}"
                                        data-guard-msg="{{ base64_encode($gramGuardMsg) }}"
                                        data-bs-placement="top"
                                        data-title="{{ __('Grammar check with AI') }}">
                                            <i class="ti ti-rotate"></i> <span>{{ __('Grammar check with AI') }}</span>
                                        </a>
                                    @endif
                                </div>

                            </div>
                        </div>
                    </div>
                </div>

                <div class="{{ VC::CLM6 }}">
                    <div class="{{ VC::CD_FL }}">
                        <div class="{{ VC::CD_BD }}">
                            <div class="{{ VC::RW }}">
                                <div class="form-group {{ VC::CM12 }}">
                                    {!! Form::label('description', __('Job Description'), ['class'=>VC::FM_LB]) !!}
                                    <textarea class="{{ VC::FM_CT }} summernote-simple-2" name="description" rows="15">{{ $job->description }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="{{ VC::CLM6 }}">
                    <div class="{{ VC::CD_FL }}">
                        <div class="{{ VC::CD_BD }}">
                            <div class="{{ VC::RW }}">
                                <div class="form-group {{ VC::CM12 }}">
                                    {!! Form::label('requirement', __('Job Requirement'), ['class'=>VC::FM_LB]) !!}
                                    <textarea class="{{ VC::FM_CT }} summernote-simple" name="requirement" rows="8">{{ !empty($job->requirement) ? $job->requirement : __('No requirements explicited')  }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="{{ VC::CM12 }} text-end">
                    <div class="{{ VC::FM_G }}">
                        <input type="submit" value="{{ __('Update') }}" class="{{ VC::BT_PRM }}">
                    </div>
                </div>
            </div>
        {{ Form::close() }}
    @else
        <div class="{{ VC::TXCT }} {{ VC::MT4 }}">
            <h3>{{ __('Job data is unavailable') }}</h3>
        </div>
    @endif
@endsection
