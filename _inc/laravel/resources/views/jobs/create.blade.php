@php
    use App\Config\Constants\{
        PlansConstants,
        ExtendingLayoutsConstants as EL,
        StacksConstants as ST,
        ViewsConstants as VW,
        ViewClassNamesConstants as VC,
        YieldingConstants as YW
    };
    use App\Models\Utility;
    use Collective\Html\FormFacade as Form;
    use Illuminate\Support\Collection;

    $lang = Utility::fetchUserLang();
    $plan = Utility::getChatGPTSettings();

    $aiGuard    = Utility::fetchLinkMessage($lang, VW::JB, 'generate_route_unavailable') ?? __('Generate with AI route is unavailable. Please contact technical support or your domain administrator.');
    $storeGuard = Utility::fetchLinkMessage($lang, VW::JB, 'store_route_unavailable') ?? __('Create Job route is unavailable. Please contact technical support or your domain administrator.');

    $branchesIsList   = (is_array($branches ?? null) && count($branches ?? []) > 0) || (($branches ?? null) instanceof Collection && $branches->isNotEmpty());
    $categoriesIsList = (is_array($categories ?? null) && count($categories ?? []) > 0) || (($categories ?? null) instanceof Collection && $categories->isNotEmpty());
    $statusIsList     = (is_array($status ?? null) && count($status ?? []) > 0) || (($status ?? null) instanceof Collection && $status->isNotEmpty());
    $customQIsList    = (is_array($customQuestion ?? null) && count($customQuestion ?? []) > 0) || (($customQuestion ?? null) instanceof Collection && $customQuestion->isNotEmpty());

    $branchOptions    = $branchesIsList   ? $branches   : ['' => __('— No branches found —')];
    $categoryOptions  = $categoriesIsList ? $categories : ['' => __('— No categories found —')];
    $statusOptions    = $statusIsList     ? $status     : ['' => __('— No statuses found —')];

    $branchAttrs   = ['class' => VC::FM_CT_SL, 'required' => 'required'] + ($branchesIsList ? [] : ['disabled' => 'disabled']);
    $categoryAttrs = ['class' => VC::FM_CT_SL, 'required' => 'required'] + ($categoriesIsList ? [] : ['disabled' => 'disabled']);
    $statusAttrs   = ['class' => VC::FM_CT_SL, 'required' => 'required'] + ($statusIsList ? [] : ['disabled' => 'disabled']);
@endphp

@extends(EL::ADM)

@section(YW::ADM_PG_TTL)
    {{ __('Create Job') }}
@endsection

@section(YW::ADM_BDC)
    <li class="breadcrumb-item">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}" {{ Route::has('dashboard') ? '' : 'aria-disabled=true' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    @php
        $jobIndexBase    = VW::JB.'.index';
        $jobIndexKebab   = Str::kebab($jobIndexBase);
        $jobIndexName    = Route::has($jobIndexBase) ? $jobIndexBase : (Route::has($jobIndexKebab) ? $jobIndexKebab : null);
        $jobIndexUrl     = $jobIndexName ? route($jobIndexName) : '#';
        $jobIndexGuard   = Utility::fetchLinkMessage($lang, VW::JB, 'job_index_route_unavailable') ?? 'Job index route is unavailable. Please contact technical support or your domain administrator.';
    @endphp
    <li class="breadcrumb-item">
        <a
            id="bc-job-index-link"
            href="{{ $jobIndexUrl }}"
            data-url="{{ $jobIndexUrl }}"
            data-guard-msg="{{ $jobIndexGuard }}"
            data-sv-localized="true"
        >
            {{ __('Job') }}
        </a>
    </li>
    @push(ST::ADM_SCR_PG)
        <script defer src="{{ asset('assets/js/routes/jobs/indexBc.js') }}"></script>
    @endpush
    <li class="breadcrumb-item">{{ __('Job Create') }}</li>
@endsection

@push(ST::ADM_CSS)
    <link rel="stylesheet" href="{{ asset('css/summernote/summernote-bs4.css') }}">
    <link rel="stylesheet" href="{{ asset('css/bootstrap-tagsinput.css') }}">
@endpush

@push(ST::ADM_SCR_PG)
    <script src="{{ asset('css/summernote/summernote-bs4.js') }}"></script>
    <script async src="{{ asset('js/bootstrap-tagsinput.min.js') }}"></script>
    <script async src="{{ asset('assets/js/routes/jobs/lang/create.js') }}"></script>
    <script defer src="{{ asset('assets/js/routes/jobs/store.js') }}"></script>
@endpush

@section(YW::ADM_ACT_BTN)
    <div class="{{ VC::FEND }}">
        @if($plan?->{PlansConstants::COL_GPT} == 1)
            <a href="#"
               data-size="lg"
               class="{{ VC::BT_SM_PM }} btn-icon"
               data-ajax-popup-over="true"
               data-url="{{ route('generate',['job']) }}"
               data-bs-placement="top"
               data-title="{{ __('Generate content with AI') }}"
               data-guard-msg="{{ $aiGuard }}">
                <i class="{{ VC::FAS_RB }}"></i> <span>{{ __('Generate with AI') }}</span>
            </a>
        @endif
    </div>
@endsection

@section(YW::ADM_CTT)
    {{ Form::open([
        'url'               => VW::JB,
        'method'            => 'post',
        'id'                => 'job-create-form',
        'data-url'          => VW::JB,
        'data-guard-msg'    => $storeGuard
    ]) }}
        <div class="{{ VC::RW }} {{ VC::MT3 }}">
            <div class="{{ VC::CM6 }}">
                <div class="{{ VC::CD_FL }}">
                    <div class="card-body job-create">
                        <div class="{{ VC::RW }}">
                            <div class="{{ VC::FM_GCB12 }}">
                                {!! Form::label('title', __('Job Title'), ['class'=>VC::FM_LB]) !!}
                                {!! Form::text('title', old('title'), ['class'=>VC::FM_CT,'required'=>'required']) !!}
                            </div>
                            <div class="{{ VC::FM_GCB6 }}">
                                {!! Form::label('branch', __('Branch'), ['class'=>VC::FM_LB]) !!}
                                {{ Form::select('branch', $branchOptions, null, $branchAttrs) }}
                            </div>
                            <div class="{{ VC::FM_GCB6 }}">
                                {!! Form::label('category', __('Job Category'), ['class'=>VC::FM_LB]) !!}
                                {{ Form::select('category', $categoryOptions, null, $categoryAttrs) }}
                            </div>
                            <div class="{{ VC::FM_GCB6 }}">
                                {!! Form::label('position', __('Positions'), ['class'=>VC::FM_LB]) !!}
                                {!! Form::number('position', old('positions'), ['class'=>VC::FM_CT,'required'=>'required']) !!}
                            </div>
                            <div class="{{ VC::FM_GCB6 }}">
                                {!! Form::label('status', __('Status'), ['class'=>VC::FM_LB]) !!}
                                {{ Form::select('status', $statusOptions, null, $statusAttrs) }}
                            </div>
                            <div class="{{ VC::FM_GCB6 }}">
                                {!! Form::label('start_date', __('Start Date'), ['class'=>VC::FM_LB]) !!}
                                {!! Form::date('start_date', old('start_date'), ['class'=>VC::FM_CT]) !!}
                            </div>
                            <div class="{{ VC::FM_GCB6 }}">
                                {!! Form::label('end_date', __('End Date'), ['class'=>VC::FM_LB]) !!}
                                {!! Form::date('end_date', old('end_date'), ['class'=>VC::FM_CT]) !!}
                            </div>
                            <div class="{{ VC::FM_GCB12 }}">
                                <input type="text" class="{{ VC::FM_CT }}" value="" data-toggle="tags" name="skill" placeholder="Skill"/>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="{{ VC::CM6 }}">
                <div class="{{ VC::CD_FL }}">
                    <div class="card-body job-create">
                        <div class="{{ VC::RW }}">
                            <div class="{{ VC::CM6 }}">
                                <div class="{{ VC::FM_G }}">
                                    <h6 class="{{ VC::H6 }}">{{ __('Need to ask ?') }}</h6>
                                    <div class="{{ VC::MY3 }}">
                                        <div class="form-check custom-checkbox">
                                            <input type="checkbox" class="form-check-input" name="applicant[]" value="gender" id="check-gender">
                                            <label class="{{ VC::CST_LB }}" for="check-gender">{{ __('Gender') }}</label>
                                        </div>
                                        <div class="form-check custom-checkbox">
                                            <input type="checkbox" class="form-check-input" name="applicant[]" value="dob" id="check-dob">
                                            <label class="{{ VC::CST_LB }}" for="check-dob">{{ __('Date Of Birth') }}</label>
                                        </div>
                                        <div class="form-check custom-checkbox">
                                            <input type="checkbox" class="form-check-input" name="applicant[]" value="country" id="check-country">
                                            <label class="{{ VC::CST_LB }}" for="check-country">{{ __('Country') }}</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="{{ VC::CM6 }}">
                                <div class="{{ VC::FM_G }}">
                                    <h6 class="{{ VC::H6 }}">{{ __('Need to show option ?') }}</h6>
                                    <div class="{{ VC::MY3 }}">
                                        <div class="form-check custom-checkbox">
                                            <input type="checkbox" class="form-check-input" name="visibility[]" value="profile" id="check-profile">
                                            <label class="{{ VC::CST_LB }}" for="check-profile">{{ __('Profile Image') }}</label>
                                        </div>
                                        <div class="form-check custom-checkbox">
                                            <input type="checkbox" class="form-check-input" name="visibility[]" value="resume" id="check-resume">
                                            <label class="{{ VC::CST_LB }}" for="check-resume">{{ __('Resume') }}</label>
                                        </div>
                                        <div class="form-check custom-checkbox">
                                            <input type="checkbox" class="form-check-input" name="visibility[]" value="letter" id="check-letter">
                                            <label class="{{ VC::CST_LB }}" for="check-letter">{{ __('Cover Letter') }}</label>
                                        </div>
                                        <div class="form-check custom-checkbox">
                                            <input type="checkbox" class="form-check-input" name="visibility[]" value="terms" id="check-terms">
                                            <label class="{{ VC::CST_LB }}" for="check-terms">{{ __('Terms And Conditions') }}</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="{{ VC::FM_GCB12 }}">
                                <h6 class="{{ VC::H6 }}">{{ __('Custom Question') }}</h6>
                                <div class="{{ VC::MY3 }}">
                                    @if($customQIsList)
                                        @foreach($customQuestion as $question)
                                            <div class="form-check custom-checkbox">
                                                <input type="checkbox" class="form-check-input" name="custom_question[]" value="{{ $question->id }}" id="custom_question_{{ $question->id }}">
                                                <label class="{{ VC::CST_LB }}" for="custom_question_{{ $question->id }}">{{ $question->question }}</label>
                                            </div>
                                        @endforeach
                                    @else
                                        <div class="{{ VC::TXT_MT }}">{{ __('No custom questions found.') }}</div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="{{ VC::CM6 }}">
                <div class="{{ VC::CD_FL }}">
                    <div class="card-body">
                        <div class="{{ VC::RW }}">
                            <div class="{{ VC::FM_GCB12 }}">
                                {!! Form::label('description', __('Job Description'), ['class'=>VC::FM_LB]) !!}
                                <textarea class="{{ VC::FM_CT }} summernote-simple-2" name="description" rows="15"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="{{ VC::CM6 }}">
                <div class="{{ VC::CD_FL }}">
                    <div class="card-body">
                        <div class="{{ VC::RW }}">
                            <div class="{{ VC::CLMS4_12 }}">
                                {!! Form::label('requirement', __('Job Requirement'), ['class'=>VC::FM_LB]) !!}
                            </div>
                            <div class="{{ VC::CLMS4_12 }} text-end">
                                @if($plan?->{PlansConstants::COL_GPT} == 1)
                                    @php
                                        $gramGuard            = Utility::fetchLinkMessage($lang, 'generics', 'grammar_check_route_unavailable')
                                                                ?? 'Grammar check route is unavailable. Please contact technical support or your domain administrator.';

                                        $grammarBase          = 'grammar';
                                        $grammarKebab         = Str::kebab($grammarBase);
                                        $grammarResolved      = Route::has($grammarBase) ? $grammarBase : (Route::has($grammarKebab) ? $grammarKebab : null);
                                        $grammarParam         = 'grammar';
                                        $grammarUrl           = $grammarResolved ? route($grammarResolved, $grammarParam) : '#';

                                        $grammarLinkId        = 'grammar-check-link';
                                    @endphp

                                    <a
                                        href="{{ $grammarUrl }}"
                                        id="{{ $grammarLinkId }}"
                                        data-size="md"
                                        class="{{ VC::BT_SM_PM }} btn-icon"
                                        data-ajax-popup-over="true"
                                        data-url="{{ $grammarUrl }}"
                                        data-bs-placement="top"
                                        data-title="{{ __('Grammar check with AI') }}"
                                        data-guard-msg="{{ $gramGuard }}"
                                        data-sv-localized="true"
                                        {{ $grammarUrl === '#' ? 'aria-disabled=true' : '' }}
                                    >
                                        <i class="ti ti-rotate"></i> <span>{{ __('Grammar check with AI') }}</span>
                                    </a>
                                    <script defer src="{{ asset('assets/js/routes/grammar/check.js') }}"></script>
                                @endif
                            </div>
                            <div class="{{ VC::FM_GCB12 }}">
                                <textarea class="{{ VC::FM_CT }} summernote-simple" name="requirement" rows="8"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="{{ VC::CM12 }} text-end">
                <div class="{{ VC::FM_G }}">
                    <input type="submit" value="{{ __('Create') }}" class="{{ VC::BT_PRM }}">
                </div>
            </div>
        </div>
    {{ Form::close() }}
@endsection