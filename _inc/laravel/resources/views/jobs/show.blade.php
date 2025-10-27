@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants as EL,
        StacksConstants as ST,
        YieldingConstants as YW,
        ViewsConstants as VW,
        ViewClassNamesConstants as VC
    };
    use App\Models\{Job, Utility};
    use Illuminate\Support\{Collection, Str};
    use Illuminate\Support\Facades\{Auth, Route};

    $user = Auth::user();
    $lang = Utility::fetchUserLang(user: $user);
    $canFormatDate = is_callable([$user, 'dateFormat']);
    $skills           = $job->skill ?? [__('No skill available')];
    $skillsIsList     = Utility::isFilled($skills ?? []);

    $applicant        = $job->applicant ?? [__('No applicant available')];
    $applicantIsList  = Utility::isFilled($applicant ?? []);

    $visibility       = $job->visibility ?? [__('No visibility available')];
    $visibilityIsList = Utility::isFilled($visibility ?? []);

    $questions        = is_callable([$job, 'questions']) ? $job->questions() : [__('No questions available')];
    $questionsIsList  = Utility::isFilled($questions ?? []);
@endphp

@extends(EL::ADM)

@section(YW::ADM_PG_TTL)
    {{ __('Job Details') }}
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
    <li class="breadcrumb-item">{{ __('Job Details') }}</li>
@endsection

@section(YW::ADM_ACT_BTN)
    <div class="{{ VC::FEND }}">
        @can('edit job')
            @php
                $editBase     = VW::JB . '.edit';
                $editResolved = Route::has($editBase) ? $editBase : null;
                $editUrl      = ($editResolved && ($job->id ?? null)) ? route($editResolved, $job->id) : '#';
                $editGuard    = Utility::fetchLinkMessage($lang, VW::JB, 'edit_route_unavailable') ?? __('Edit Job route is unavailable. Please contact technical support or your domain administrator.');
            @endphp
            <a id="job-edit-link"
               href="{{ $editUrl }}"
               data-url="{{ $editUrl }}"
               data-ajax-popup="true"
               data-title="{{ __('Edit Job') }}"
               data-bs-toggle="tooltip"
               title="{{ __('Edit') }}"
               class="{{ VC::BT_SM_PM }}"
               data-guard-msg="{{ $editGuard }}"
               data-sv-localized="true">
                <i class="{{ VC::TI_PC_WT }}"></i>
            </a>
        @endcan
    </div>
@endsection

@section(YW::ADM_CTT)
    <div id="job-show-page" class="{{ VC::RW }}">
        <div class="{{ VC::CM4 }}">
            <div class="card">
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        <table class="{{ VC::TB }} mt-3">
                            <tbody>
                                <tr>
                                    <td>{{ __('Job Title') }}</td>
                                    <td>{{ !empty($job->title) ? $job->title : __('No job title available') }}</td>
                                </tr>
                                <tr>
                                    <td>{{ __('Branch') }}</td>
                                    <td>{{ !empty($job->branches) ? $job->branches->name : __('All') }}</td>
                                </tr>
                                <tr>
                                    <td>{{ __('Job Category') }}</td>
                                    <td>{{ !empty($job->categories) && !empty($job->categories->title) ? $job->categories->title : __('No job category title available') }}</td>
                                </tr>
                                <tr>
                                    <td>{{ __('Positions') }}</td>
                                    <td>{{ !empty($job->position) ? $job->position : __('No position found') }}</td>
                                </tr>
                                <tr>
                                    <td>{{ __('Status') }}</td>
                                    <td>
                                        @if($job->status == 'active')
                                            <span class="p-2 px-3 rounded badge bg-primary">{{ !empty(Job::$status) && !empty($job->status) && !empty(Job::$status[$job->status]) ? Job::$status[$job->status] : __('Unknown status') }}</span>
                                        @else
                                            <span class="p-2 px-3 rounded badge bg-danger">{{ !empty(Job::$status) && !empty($job->status) && !empty(Job::$status[$job->status]) ? Job::$status[$job->status] : __('Unknown status') }}</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td>{{ __('Created Date') }}</td>
                                    <td>{{ $canFormatDate ? (!empty($job->created_at) ? $user->dateFormat($job->created_at) : __('No created date available')) : __('Failed to format date') }}</td>
                                </tr>
                                <tr>
                                    <td>{{ __('Start Date') }}</td>
                                    <td>{{ $canFormatDate ? (!empty($job->start_date) ? $user->dateFormat($job->start_date) : __('No start date available')) : __('Failed to format date') }}</td>
                                </tr>
                                <tr>
                                    <td>{{ __('End Date') }}</td>
                                    <td>{{ $canFormatDate ? (!empty($job->end_date) ? $user->dateFormat($job->end_date) : __('No end date available')) : __('Failed to format date') }}</td>
                                </tr>
                                <tr>
                                    <td>{{ __('Skill') }}</td>
                                    <td>
                                        @if($skillsIsList)
                                            @foreach($skills as $skill)
                                                <span class="p-2 px-3 rounded badge bg-primary">{{ !empty($skill) ? $skill : __('No skill found') }}</span>
                                            @endforeach
                                        @else
                                            <span class="{{ VC::TXT_MT }}">—</span>
                                        @endif
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="{{ VC::CM8 }}">
            <div class="card card-fluid">
                <div class="card-body">
                    <div class="{{ VC::C12 }}">
                        <div class="{{ VC::RW }}">
                            <div class="{{ VC::CM6 }}">
                                <h6>{{ __('Need to ask ?') }}</h6>
                                @if($applicantIsList)
                                    <ul>
                                        @foreach($applicant as $a)
                                            <li>{{ ucfirst($a) }}</li>
                                        @endforeach
                                    </ul>
                                @else
                                    <div class="{{ VC::TXT_MT }}">{{ ___('No applicants listed') }}</div>
                                @endif
                            </div>
                            <div class="{{ VC::CM6 }}">
                                <h6>{{ __('Need to show option ?') }}</h6>
                                @if($visibilityIsList)
                                    <ul>
                                        @foreach($visibility as $v)
                                            <li>{{ ucfirst($v) }}</li>
                                        @endforeach
                                    </ul>
                                @else
                                    <div class="{{ VC::TXT_MT }}">{{ __('No visibility options listed') }}</div>
                                @endif
                            </div>
                            <div class="{{ VC::C12 }}">
                                <h6>{{ __('Custom Question') }}</h6>
                                @if($questionsIsList)
                                    <ul>
                                        @foreach($questions as $question)
                                            <li>{{ !empty($question->question) ? $question->question : __('Undefined question') }}</li>
                                        @endforeach
                                    </ul>
                                @else
                                    <div class="{{ VC::TXT_MT }}">{{ __('No questions available') }}</div>
                                @endif
                            </div>
                        </div>
                        <div class="{{ VC::RW }}">
                            <div class="{{ VC::C12 }} {{ VC::MT3 }}">
                                <h6>{{ __('Job Description') }}</h6>
                                {!! !empty($job->description) ? $job->description : __('No description found') !!}
                            </div>
                        </div>
                        <div class="{{ VC::RW }}">
                            <div class="{{ VC::C12 }} {{ VC::MT3 }}">
                                <h6>{{ __('Job Requirement') }}</h6>
                                {!! !empty($job->requirement) ? $job->requirement : __('No requirement found') !!}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push(ST::ADM_SCR_PG)
    <script defer src="{{ asset('assets/js/routes/jobs/show.js') }}"></script>
@endpush
