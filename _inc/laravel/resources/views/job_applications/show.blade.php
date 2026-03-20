@php
    try {
$user = Auth::user() ?? null;
        $canFetchLang = is_callable([Utility::class,'fetchUserLang']);
        $lang = $canFetchLang ? Utility::fetchUserLang(user:$user) : app()->getLocale();
        $canFetchMsg = is_callable([Utility::class,'fetchLinkMessage']);
        $canGetFile = is_callable([Utility::class,'getFile']);
        $canUserDate = $user && is_callable([$user,'dateFormat']);
        $logo = $canGetFile ? Utility::getFile('uploads/avatar/') : '';
        $profiles = $canGetFile ? Utility::getFile('uploads/job/profile/') : '';
        $areYouSure = ($canFetchMsg ? Utility::fetchLinkMessage($lang,'generics','are_you_sure') : 'Are You Sure?') ?? __('Are You Sure?');
        $irreversible = ($canFetchMsg ? Utility::fetchLinkMessage($lang,'generics','irreversible_action') : 'This action can not be undone. Do you want to continue?') ?? __('This action can not be undone. Do you want to continue?');
    } catch (\Throwable $e) {
        \Log::error('job_applications/show — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    }
@endphp
@extends(EL::ADM)
@section(YD::ADM_PG_TTL)
    {{ __('Job Application Details') }}
@endsection
@section(YD::ADM_BDC)
    <li class="{{ VC::BCI }}"><a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}">{{ __('Dashboard') }}</a></li>
    @php
        try {
            $jbIndexBase        = VW::JB_APL.'.index';
            $jbIndexKebab       = Str::kebab($jbIndexBase);
            $jbIndexResolved    = Route::has($jbIndexBase) ? $jbIndexBase : (Route::has($jbIndexKebab) ? $jbIndexKebab : null);
            $jbIndexUrl         = $jbIndexResolved ? route($jbIndexResolved) : '#';
            $jbIndexGuardMsg    = Utility::fetchLinkMessage($lang, VW::JB_APL, 'index_job_application_route_unavailable')
                                    ?? 'Job application index route is unavailable. Please contact technical support or your domain administrator.';
        } catch (\Throwable $e) {
            \Log::error('job_applications/show — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
        }
@endphp
    <li class="{{ VC::BCI }}">
        <a
            id="bc-job-application-index-link"
            href="{{ $jbIndexUrl }}"
            data-url="{{ $jbIndexUrl }}"
            data-guard-msg="{{ base64_encode($jbIndexGuardMsg) }}"
            data-sv-localized="true"
        >
            {{ __('Job Application') }}
        </a>
    </li>
    @push(ST::ADM_SCR_PG)
        <script defer src="{{ asset('assets/js/routes/jobs/applications/indexShow.js') }}"></script>
    @endpush
    <li class="{{ VC::BCI }}">{{ __('Job Application Details') }}</li>
@endsection
@push(ST::ADM_CSS)
    <style>@import url({{ asset('css/font-awesome.css') }});</style>
@endpush
@section(YD::ADM_CTT)
    <div class="row">
        <div class="{{ VC::CM6 }}">
            <div class="card job-create">
                <div class="{{ VC::CD_HD }}">
                    <div class="row">
                        <div class="{{ VC::C_AT }}"><h6 class="{{ VC::TXT_MT }}">{{ __('Basic Details') }}</h6></div>
                        <div class="col {{ VC::FEND }}">
                            <ul class="list-inline {{ VC::MB0 }}">
                                @can('delete job application')
                                    <li class="list-inline-item {{ VC::FEND }}">
                                        @php
                                            try {
                                                $applicationIdStr          = (string) data_get($jobApplication ?? null, 'id', '');
                                                $archiveBase               = VW::JB.'.application.archive';
                                                $archiveKebab              = Str::kebab($archiveBase);
                                                $archiveResolved           = Route::has($archiveBase) ? $archiveBase : (Route::has($archiveKebab) ? $archiveKebab : null);
                                                $archiveUrl                = ($archiveResolved && $applicationIdStr !== '') ? route($archiveResolved, $applicationIdStr) : '#';
                                                $archiveFormId             = 'archive-form-'.($applicationIdStr !== '' ? $applicationIdStr : 'x');
                                                $archiveLinkId             = 'archive-link-'.($applicationIdStr !== '' ? $applicationIdStr : 'x');
                                                $archiveGuard              = Utility::fetchLinkMessage($lang, VW::JB, 'application_archive_route_unavailable') ?? 'Archive job application route is unavailable. Please contact technical support or your domain administrator.';
                                            } catch (\Throwable $e) {
                                                \Log::error('job_applications/show — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                            }
@endphp
                                        {!! Form::open([
                                            'method'            => 'DELETE',
                                            'url'               => $archiveUrl,
                                            'id'                => $archiveFormId,
                                            'data-url'          => $archiveUrl,
                                            'data-guard-msg'    => $archiveGuard,
                                            'data-sv-localized' => 'true',
                                        ]) !!}
                                            <a
                                                id="{{ $archiveLinkId }}"
                                                href="{{ $archiveUrl }}"
                                                class="bs-pass-para"
                                                data-bs-toggle="tooltip"
                                                data-confirm="{{ __($areYouSure) }}|{{ __($irreversible) }}"
                                                data-confirm-yes="document.getElementById('{{ $archiveFormId }}').submit();"
                                                data-url="{{ $archiveUrl }}"
                                                data-guard-msg="{{ base64_encode($archiveGuard) }}"
                                                data-sv-localized="true"
                                                {{ $archiveUrl === '#' ? 'aria-disabled=true' : '' }}
                                            >
                                                @if((int)($jobApplication->is_archive ?? 0)===0)
                                                    <span class="badge bg-info p-2 {{ VC::PX3 }} rounded">{{ __('Archive') }}</span>
                                                @else
                                                    <span class="badge bg-warning p-2 {{ VC::PX3 }} rounded">{{ __('UnArchive') }}</span>
                                                @endif
                                            </a>
                                            <script defer src="{{ asset('assets/js/routes/jobs/applications/archive.js') }}"></script>
                                        {!! Form::close() !!}
                                    </li>
                                    @if((int)($jobApplication->is_archive ?? 0)===0)
                                        @php
                                            try {
                                                $appIdStr             = (string) data_get($jobApplication ?? null, 'id', '');
                                                $destroyBase          = VW::JB_APL.'.destroy';
                                                $destroyKebab         = Str::kebab($destroyBase);
                                                $destroyResolved      = Route::has($destroyBase) ? $destroyBase : (Route::has($destroyKebab) ? $destroyKebab : null);
                                                $destroyUrl           = ($destroyResolved && $appIdStr !== '') ? route($destroyResolved, $appIdStr) : '#';
                                                $formId               = 'delete-form-'.($appIdStr !== '' ? $appIdStr : 'x');
                                                $linkId               = 'delete-link-'.($appIdStr !== '' ? $appIdStr : 'x');
                                                $guardDestroy         = Utility::fetchLinkMessage($lang, VW::JB_APL, 'delete_job_application_unavailable') ?? 'Delete job application route is unavailable. Please contact technical support or your domain administrator.';
                                            } catch (\Throwable $e) {
                                                \Log::error('job_applications/show — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                            }
@endphp
                                        <li class="list-inline-item">
                                            {{ Form::open([
                                                'method'            => 'DELETE',
                                                'url'               => $destroyUrl,
                                                'id'                => $formId,
                                                'data-url'          => $destroyUrl,
                                                'data-guard-msg'    => $guardDestroy,
                                                'data-sv-localized' => 'true',
                                            ]) }}
                                                <a
                                                    id="{{ $linkId }}"
                                                    href="{{ $destroyUrl }}"
                                                    class="bs-pass-para"
                                                    data-bs-toggle="tooltip"
                                                    data-confirm="{{ __($areYouSure) }}|{{ __($irreversible) }}"
                                                    data-confirm-yes="document.getElementById('{{ $formId }}').submit();"
                                                    data-url="{{ $destroyUrl }}"
                                                    data-guard-msg="{{ base64_encode($guardDestroy) }}"
                                                    data-sv-localized="true"
                                                    {{ $destroyUrl === '#' ? 'aria-disabled=true' : '' }}
                                                >
                                                    <span class="badge badge-pill badge-soft-danger">{{ __('Delete') }}</span>
                                                </a>
                                            {{ Form::close() }}
                                        </li>
                                        @push(ST::ADM_SCR_PG)
                                            <script defer src="{{ asset('assets/js/routes/jobs/applications/destroy.js') }}"></script>
                                        @endpush
                                    @endif
                                @endcan
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="{{ VC::CD_BD }}">
                    <h5 class="h4">
                        <div class="{{ VC::DFL_AIC }}">
                            <div>
                                @php
                                    $profileSrc = !empty($jobApplication->profile) ? ($profiles.$jobApplication->profile) : ($logo.'avatar.png');
@endphp
                                <a href="{{ $profileSrc }}" class="{{ VC::AV_CC_SM }}">
                                    <img src="{{ $profileSrc }}" class="hweb h-100">
                                </a>
                            </div>
                            <div class="flex-fill ms-3">
                                <div class="h6 {{ VC::TXSM }} {{ VC::MB0 }}">{{ $jobApplication->name ?? __('No applicant name available') }}</div>
                                <p class="{{ VC::TXSM }} lh-140 {{ VC::MB0 }}">{{ $jobApplication->email ?? __('No email available') }}</p>
                            </div>
                        </div>
                    </h5>
                    <div class="{{ VC::PY2 }} {{ VC::MT3 }} border-top">
                        <div class="{{ VC::R_ALC }} {{ VC::MS2 }}">
                            @if(Utility::isFilled($stages) ?? [])
                                @foreach($stages as $stage)
                                    <div class="{{ ViewClassNamesConstants::FM_CHK_IL_GP }}">
                                        <input type="radio"
                                            id="stage_{{ data_get($stage,'id') }}"
                                            name="stage"
                                            class="form-check-input stages"
                                            data-scheduleid="{{ $jobApplication->id }}"
                                            value="{{ data_get($stage,'id') }}"
                                            {{ ((int)($jobApplication->stage ?? 0)=== (int) data_get($stage,'id')) ? 'checked' : '' }}>
                                        <label class="form check-label" for="stage_{{ data_get($stage,'id') }}">{{ data_get($stage,'title',__('No stage title available')) }}</label>
                                    </div>
                                @endforeach
                            @else
                                <div class="{{ VC::C_AT }}">
                                    <span class="{{ VC::TXT_MT }}">{{ __('No stages available') }}</span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="{{ VC::CM6 }}">
            <div class="card">
                <div class="{{ VC::CD_HD }}">
                    <div class="row">
                        <div class="{{ VC::C_AT }}"><h6 class="{{ VC::TXT_MT }}">{{ __('Basic Information') }}</h6></div>
                        <div class="col {{ VC::TX_END }}">
                            @php
                                try {
                                    $appIdStr               = (string) data_get($jobApplication ?? null, 'id', '');
                                    $onBoardCreateBase      = VW::JB.'.on.board.create';
                                    $onBoardCreateKebab     = Str::kebab($onBoardCreateBase);
                                    $onBoardCreateResolved  = Route::has($onBoardCreateBase) ? $onBoardCreateBase : (Route::has($onBoardCreateKebab) ? $onBoardCreateKebab : null);
                                    $onBoardCreateUrl       = ($onBoardCreateResolved && $appIdStr !== '') ? route($onBoardCreateResolved, $appIdStr) : '#';
                                    $onBoardCreateGuardMsg  = Utility::fetchLinkMessage($lang, VW::JB, 'on_board_create_route_unavailable') ?? 'Add to Job OnBoard route is unavailable. Please contact technical support or your domain administrator.';
                                    $onBoardCreateLinkId    = 'job-onboard-create-btn-'.($appIdStr !== '' ? $appIdStr : 'x');
                                } catch (\Throwable $e) {
                                    \Log::error('job_applications/show — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                }
@endphp
                            <div class="{{ VC::C12 }} {{ VC::TX_END }}">
                                <a
                                    id="{{ $onBoardCreateLinkId }}"
                                    href="{{ $onBoardCreateUrl }}"
                                    data-url="{{ $onBoardCreateUrl }}"
                                    data-title="{{ __('Add to Job OnBoard') }}"
                                    data-ajax-popup="true"
                                    data-guard-msg="{{ base64_encode($onBoardCreateGuardMsg) }}"
                                    data-sv-localized="true"
                                    class="{{ VC::BT_SM_PM }}"
                                    {{ $onBoardCreateUrl === '#' ? 'aria-disabled=true' : '' }}
                                >
                                    <i class="{{ VC::TI_PLS }}"></i>{{ __('Add to Job OnBoard') }}
                                </a>
                            </div>
                            @push(ST::ADM_SCR_PG)
                                <script defer src="{{ asset('assets/js/routes/jobs/boards/create.js') }}"></script>
                            @endpush
                        </div>
                    </div>
                </div>
                <div class="{{ VC::CD_BD }}">
                    <dl class="row">
                        <dt class="{{ VC::CS3 }}"><span class="h6 {{ VC::TXSM }} {{ VC::MB0 }}">{{ __('Phone') }}</span></dt>
                        <dd class="{{ VC::CS9 }}"><span class="{{ VC::TXSM }}">{{ $jobApplication->phone ?? __('No phone available') }}</span></dd>
                        @if(!empty($jobApplication->dob))
                            <dt class="{{ VC::CS3 }}"><span class="h6 {{ VC::TXSM }} {{ VC::MB0 }}">{{ __('DOB') }}</span></dt>
                            <dd class="{{ VC::CS9 }}"><span class="{{ VC::TXSM }}">{{ $canUserDate ? $user->dateFormat($jobApplication->dob) : ($jobApplication->dob ?? __('No date available')) }}</span></dd>
                        @endif
                        @if(!empty($jobApplication->gender))
                            <dt class="{{ VC::CS3 }}"><span class="h6 {{ VC::TXSM }} {{ VC::MB0 }}">{{ __('Gender') }}</span></dt>
                            <dd class="{{ VC::CS9 }}"><span class="{{ VC::TXSM }}">{{ $jobApplication->gender ?? __('No gender available') }}</span></dd>
                        @endif
                        @if(!empty($jobApplication->country))
                            <dt class="{{ VC::CS3 }}"><span class="h6 {{ VC::TXSM }} {{ VC::MB0 }}">{{ __('Country') }}</span></dt>
                            <dd class="{{ VC::CS9 }}"><span class="{{ VC::TXSM }}">{{ $jobApplication->country ?? __('No country available') }}</span></dd>
                        @endif
                        @if(!empty($jobApplication->state))
                            <dt class="{{ VC::CS3 }}"><span class="h6 {{ VC::TXSM }} {{ VC::MB0 }}">{{ __('State') }}</span></dt>
                            <dd class="{{ VC::CS9 }}"><span class="{{ VC::TXSM }}">{{ $jobApplication->state ?? __('No state available') }}</span></dd>
                        @endif
                        @if(!empty($jobApplication->city))
                            <dt class="{{ VC::CS3 }}"><span class="h6 {{ VC::TXSM }} {{ VC::MB0 }}">{{ __('City') }}</span></dt>
                            <dd class="{{ VC::CS9 }}"><span class="{{ VC::TXSM }}">{{ $jobApplication->city ?? __('No city available') }}</span></dd>
                        @endif
                        <dt class="{{ VC::CS3 }}"><span class="h6 {{ VC::TXSM }} {{ VC::MB0 }}">{{ __('Applied For') }}</span></dt>
                        <dd class="{{ VC::CS9 }}"><span class="{{ VC::TXSM }}">{{ data_get($jobApplication,'jobs.title',__('No job title available')) }}</span></dd>
                        <dt class="{{ VC::CS3 }}"><span class="h6 {{ VC::TXSM }} {{ VC::MB0 }}">{{ __('Applied at') }}</span></dt>
                        <dd class="{{ VC::CS9 }}"><span class="{{ VC::TXSM }}">{{ $canUserDate ? $user->dateFormat($jobApplication->created_at) : ((string)($jobApplication->created_at ?? __('No date available'))) }}</span></dd>
                        <dt class="{{ VC::CS3 }}"><span class="h6 {{ VC::TXSM }} {{ VC::MB0 }}">{{ __('CV / Resume') }}</span></dt>
                        <dd class="{{ VC::CS9 }}">
                            @if(!empty($jobApplication->resume))
                                <span class="{{ VC::TXSM }} {{ VC::ACT_BTN_PRIM }}">
                                    <a href="{{ asset(Storage::url('uploads/job/resume')).'/'.$jobApplication->resume }}" download target="_blank"><i class="{{ VC::TI_DWN }} {{ VC::TXT_WT }}"></i></a>
                                </span>
                            @else
                                -
                            @endif
                        </dd>
                        <dt class="{{ VC::CS3 }}"><span class="h6 {{ VC::TXSM }} {{ VC::MB0 }}">{{ __('Cover Letter') }}</span></dt>
                        <dd class="{{ VC::CS9 }}"><span class="{{ VC::TXSM }}">{{ $jobApplication->cover_letter ?? __('No cover letter available') }}</span></dd>
                    </dl>
                    <div class="rating-stars {{ VC::TX_RT }}">
                        @php
 $r = (int)($jobApplication->rating ?? 0);
@endphp
                        <ul id="stars">
                            <li class="star {{ in_array($r,[1,2,3,4,5]) ? 'selected' : '' }}" data-bs-toggle="tooltip" data-bs-title="{{ __('Poor') }}" data-value="1"><i class="fas fa-star fa-fw"></i></li>
                            <li class="star {{ in_array($r,[2,3,4,5]) ? 'selected' : '' }}" data-bs-toggle="tooltip" data-bs-title="{{ __('Fair') }}" data-value="2"><i class="fas fa-star fa-fw"></i></li>
                            <li class="star {{ in_array($r,[3,4,5]) ? 'selected' : '' }}" data-bs-toggle="tooltip" data-bs-title="{{ __('Good') }}" data-value="3"><i class="fas fa-star fa-fw"></i></li>
                            <li class="star {{ in_array($r,[4,5]) ? 'selected' : '' }}" data-bs-toggle="tooltip" data-bs-title="{{ __('Excellent') }}" data-value="4"><i class="fas fa-star fa-fw"></i></li>
                            <li class="star {{ in_array($r,[5]) ? 'selected' : '' }}" data-bs-toggle="tooltip" data-bs-title="{{ __('WOW!!!') }}" data-value="5"><i class="fas fa-star fa-fw"></i></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="{{ VC::CD_HD }}">
            <div class="row">
                <div class="col"><h6 class="{{ VC::TXT_MT }}">{{ __('Additional Details') }}</h6></div>
                <div class="col {{ VC::TX_END }}">
                    @can(PM::CR_ITV_SCHD)
                        @php
                            try {
                                $jobAppIdStr             = (string) data_get($jobApplication ?? null, 'id', '');

                                $itvCreateBase           = VW::ITV_SCD.'.create';
                                $itvCreateKebab          = Str::kebab($itvCreateBase);
                                $itvCreateResolved       = Route::has($itvCreateBase) ? $itvCreateBase : (Route::has($itvCreateKebab) ? $itvCreateKebab : null);
                                $itvCreateUrl            = ($itvCreateResolved && $jobAppIdStr !== '') ? route($itvCreateResolved, $jobAppIdStr) : '#';

                                $itvCreateGuardMsg       = Utility::fetchLinkMessage($lang, VW::ITV_SCHD, 'create_interview_schedule_route_unavailable')
                                                            ?? 'Create interview schedule route is unavailable. Please contact technical support or your domain administrator.';

                                $itvCreateLinkId         = 'interview-schedule-create-btn-'.($jobAppIdStr !== '' ? $jobAppIdStr : 'x');
                            } catch (\Throwable $e) {
                                \Log::error('job_applications/show — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                            }
@endphp
                        <a
                            id="{{ $itvCreateLinkId }}"
                            href="{{ $itvCreateUrl }}"
                            data-url="{{ $itvCreateUrl }}"
                            data-size="lg"
                            class="{{ VC::BT_SM_PM }}"
                            data-ajax-popup="true"
                            data-title="{{ __('Create New Interview Schedule') }}"
                            data-guard-msg="{{ base64_encode($itvCreateGuardMsg) }}"
                            data-sv-localized="true"
                            {{ $itvCreateUrl === '#' ? 'aria-disabled=true' : '' }}
                        >
                            <i class="{{ VC::TI_PLS }}"></i> {{ __('Create Interview Schedule') }}
                        </a>
                        @push(ST::ADM_SCR_PG)
                            <script defer src="{{ asset('assets/js/routes/jobs/applications/interviewScheduleCreate.js') }}"></script>
                        @endpush
                    @endcan
                </div>
            </div>
        </div>
        <div class="{{ VC::CD_BD }}">
            @php
 $cq = json_decode($jobApplication->custom_question ?? '[]', true) ?: [];
@endphp
            @if(!empty($cq))
                <div class="{{ ViewClassNamesConstants::LG_FLSH_MB4 }}">
                    @foreach($cq as $que => $ans)
                        @if(!empty($ans))
                            <div class="{{ VC::LG_IT }} px-0">
                                <div class="{{ VC::R_ALC }}">
                                    <div class="col">
                                        <a href="#!" class="{{ VC::DBL }} h6 {{ VC::TXSM }} {{ VC::MB0 }}">{{ $que }}</a>
                                        <p class="card-text {{ VC::TXSM }} {{ VC::TXT_MT }} {{ VC::MB0 }}">{{ $ans }}</p>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
            @endif
            @php
                try {
                    $jobAppIdStr         = (string) data_get($jobApplication ?? null, 'id', '');
                    $skillStoreBase      = VW::JB.'.application.skill.store';
                    $skillStoreKebab     = Str::kebab($skillStoreBase);
                    $skillStoreResolved  = Route::has($skillStoreBase) ? $skillStoreBase : (Route::has($skillStoreKebab) ? $skillStoreKebab : null);
                    $skillStoreUrl       = ($skillStoreResolved && $jobAppIdStr !== '') ? route($skillStoreResolved, $jobAppIdStr) : '#';
                    $skillFormId         = 'job-application-skill-store-form-'.($jobAppIdStr !== '' ? $jobAppIdStr : 'x');
                    $skillGuardMsg       = Utility::fetchLinkMessage($lang, VW::JB, 'application_skill_store_route_unavailable') ?? 'Store job application skill route is unavailable. Please contact technical support or your domain administrator.';
                    $noteStoreBase       = VW::JB.'.application.note.store';
                    $noteStoreKebab      = Str::kebab($noteStoreBase);
                    $noteStoreResolved   = Route::has($noteStoreBase) ? $noteStoreBase : (Route::has($noteStoreKebab) ? $noteStoreKebab : null);
                    $noteStoreUrl        = ($noteStoreResolved && $jobAppIdStr !== '') ? route($noteStoreResolved, $jobAppIdStr) : '#';
                    $noteFormId          = 'job-application-note-store-form-'.($jobAppIdStr !== '' ? $jobAppIdStr : 'x');
                    $noteGuardMsg        = Utility::fetchLinkMessage($lang, VW::JB, 'application_note_store_route_unavailable') ?? 'Store job application note route is unavailable. Please contact technical support or your domain administrator.';
                } catch (\Throwable $e) {
                    \Log::error('job_applications/show — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                }
@endphp
            {{ Form::open([
                'url'               => $skillStoreUrl,
                'method'            => 'POST',
                'id'                => $skillFormId,
                'data-url'          => $skillStoreUrl,
                'data-guard-msg'    => $skillGuardMsg,
                'data-sv-localized' => 'true',
            ]) }}
                @csrf
                <div class="{{ VC::FM_G }}">
                    <label class="{{ VC::FM_LB }}">{{ __('Skills') }}</label>
                    <input
                        type="text"
                        class="{{ VC::FM_CT }}"
                        value="{{ $jobApplication->skill ?? '' }}"
                        data-toggle="tags"
                        name="skill"
                        placeholder="{{ __('Type here....') }}"
                        autocomplete="off"
                    />
                </div>
                @can('add job application skill')
                    <div class="{{ VC::FM_G }}">
                        <input type="submit" value="{{ __('Add Skills') }}" class="btn-sm {{ VC::BT_PRM }}">
                    </div>
                @endcan
                <script defer src="{{ asset('assets/js/routes/jobs/applications/skillStore.js') }}"></script>
            {{ Form::close() }}
            {{ Form::open([
                'url'               => $noteStoreUrl,
                'method'            => 'POST',
                'id'                => $noteFormId,
                'data-url'          => $noteStoreUrl,
                'data-guard-msg'    => $noteGuardMsg,
                'data-sv-localized' => 'true',
            ]) }}
                @csrf
                <div class="{{ VC::FM_G }}">
                    <label class="{{ VC::FM_LB }}">{{ __('Applicant Notes') }}</label>
                    <textarea name="note" class="{{ VC::FM_CT }}" rows="3" placeholder="{{ __('Type here....') }}"></textarea>
                </div>
                @can('add job application note')
                    <div class="{{ VC::FM_G }}">
                        <input type="submit" value="{{ __('Add Notes') }}" class="btn-sm {{ VC::BT_PRM }}">
                    </div>
                @endcan
                <script defer src="{{ asset('assets/js/routes/jobs/applications/noteStore.js') }}"></script>
            {{ Form::close() }}
            <div class="{{ ViewClassNamesConstants::LG_FLSH_MB4 }}">
                @if(Utility::isFilled($notes) ?? [])
                    @foreach($notes as $note)
                        <div class="{{ VC::LG_IT }} px-0">
                            <div class="{{ VC::R_ALC }}">
                                <div class="col">
                                    <a href="#!" class="{{ VC::DBL }} h6 {{ VC::TXSM }} {{ VC::MB0 }}">{{ data_get($note,'noteCreated.name',__('No author available')) }}</a>
                                    <p class="card-text {{ VC::TXSM }} {{ VC::TXT_MT }} {{ VC::MB0 }}">{{ $note->note ?? __('No note content available') }}</p>
                                </div>
                                <div class="{{ VC::C_AT }}">
                                    <a href="#" class="">{{ $canUserDate ? $user->dateFormat($note->created_at) : ((string)($note->created_at ?? __('No date available'))) }}</a>
                                </div>
                                @can('delete job application note')
                                    @if((int) data_get($note,'note_created') === (int) data_get($user,'id'))
                                        <div class="{{ VC::ACT_BTN_DNG_2 }}">
                                            @php
                                                try {
                                                    $noteIdStr       = (string) data_get($note ?? null, 'id', '');
                                                    $destroyBase     = VW::JB.'.application.note.destroy';
                                                    $destroyKebab    = Str::kebab($destroyBase);
                                                    $destroyResolved = Route::has($destroyBase) ? $destroyBase : (Route::has($destroyKebab) ? $destroyKebab : null);
                                                    $destroyUrl      = ($destroyResolved && $noteIdStr !== '') ? route($destroyResolved, $noteIdStr) : '#';

                                                    $formId          = 'delete-form-'.($noteIdStr !== '' ? $noteIdStr : 'x');
                                                    $linkId          = 'delete-note-link-'.($noteIdStr !== '' ? $noteIdStr : 'x');

                                                    $guardDestroy    = isset($guardDestroy) ? $guardDestroy : (Utility::fetchLinkMessage($lang, VW::JB, 'application_note_destroy_route_unavailable') ?? 'Delete job application note route is unavailable. Please contact technical support or your domain administrator.');
                                                } catch (\Throwable $e) {
                                                    \Log::error('job_applications/show — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                }
@endphp
                                            {{ Form::open([
                                                    'method'            => 'DELETE',
                                                    'url'               => $destroyUrl,
                                                    'id'                => $formId,
                                                    'data-url'          => $destroyUrl,
                                                    'data-guard-msg'    => $guardDestroy,
                                                    'data-sv-localized' => 'true',
                                            ]) }}
                                                    <a
                                                        id="{{ $linkId }}"
                                                        class="{{ VC::TRS_PARA }}"
                                                        href="{{ $destroyUrl }}"
                                                        data-bs-toggle="tooltip"
                                                        title="{{ __('Delete') }}"
                                                        data-confirm="{{ __($areYouSure) }}|{{ __($irreversible) }}"
                                                        data-confirm-yes="document.getElementById('{{ $formId }}').submit();"
                                                        data-url="{{ $destroyUrl }}"
                                                        data-guard-msg="{{ base64_encode($guardDestroy) }}"
                                                        data-sv-localized="true"
                                                        {{ $destroyUrl === '#' ? 'aria-disabled=true' : '' }}
                                                    >
                                                        <i class="{{ VC::TI_TRS_WT }}"></i>
                                                    </a>
                                                @push(ST::ADM_SCR_PG)
                                                    <script defer src="{{ asset('assets/js/routes/jobs/applications/noteDestroy.js') }}"></script>
                                                @endpush
                                            {{ Form::close() }}
                                        </div>
                                    @endif
                                @endcan
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="{{ VC::TXCT_MT }}">{{ __('No notes available') }}</div>
                @endif
            </div>
        </div>
    </div>
@endsection

@push(ST::ADM_SCR_PG)
    <script async src="{{ asset('js/bootstrap-toggle.js') }}"></script>
    <script defer src="{{ asset('assets/js/routes/jobs/applications/show.js') }}"></script>
    <script async src="{{ asset('assets/js/routes/jobs/applications/lang/show.js') }}"></script>
    <script defer>
        (() => {
            const DATA_LISTENER_ADDED   = 'data-listener-added';
            const DATA_CLIENT_LOCALIZED = 'data-client-localized';
            const DATA_GUARD_MSG        = 'data-guard-msg';
            const ERR_FB                = '# ERROR';

            const getLocalizedMessage = (el, key) => {
                let msg = ERR_FB;
                if (el?.getAttribute('data-sv-localized') === 'true'
                    || el?.getAttribute(DATA_CLIENT_LOCALIZED) === 'true') {
                    msg = el.getAttribute(DATA_GUARD_MSG) || ERR_FB;
                } else {
                    let lang = (sessionStorage.getItem('erp-np-lang')
                                || document.documentElement.lang
                                || 'en')
                                .toLowerCase()
                                .replace(/_/g, '-');
                    lang = lang === 'pt-br' ? lang : lang.slice(0,2);
                    msg = window.translations?.[lang]?.[key]
                        || window.translations?.['en']?.[key]
                        || ERR_FB;
                    if (msg !== ERR_FB) {
                        el.setAttribute(DATA_GUARD_MSG, msg);
                        el.setAttribute(DATA_CLIENT_LOCALIZED, 'true');
                    }
                }
                return msg;
            };

            const handleErrorDisplay = (el, key) => {
                const message = el
                    ? getLocalizedMessage(el, key)
                    : ERR_FB;
                const hasBootstrap = document.querySelector('link[href*="bootstrap"]')
                                    && window.bootstrap?.Toast;
                if (hasBootstrap) {
                    if (!document.querySelector('#error-toast')) {
                        const toast = document.createElement('div');
                        toast.id        = 'error-toast';
                        toast.className = 'toast align-items-center text-bg-danger border-0';
                        toast.setAttribute('role', 'alert');
                        toast.setAttribute('aria-live', 'assertive');
                        toast.setAttribute('aria-atomic', 'true');
                        toast.innerHTML = `
                            <div class="{{ VC::DFL }}">
                                <div class="toast-body">${message}</div>
                                <button type="button"
                                        class="{{ VC::BT_CL }} btn-close-white me-2 m-auto"
                                        data-bs-dismiss="toast"
                                        aria-label="{{ __('Close') }}"></button>
                            </div>`;
                        document.body.appendChild(toast);
                    }
                    new bootstrap.Toast(document.querySelector('#error-toast')).show();
                } else {
                    alert(message);
                }
            };

            try {
                if (typeof $ === 'undefined') throw new Error();

                // TagsInput initialization
                const $tags = $('[data-bs-toggle="tags"]');
                $tags.each(function() {
                    const el = this;
                    try {
                        $(el).tagsinput({ tagClass: 'badge badge-primary' });
                    } catch {
                        if (!el.hasAttribute(DATA_LISTENER_ADDED)) {
                            el.addEventListener('click', () =>
                                handleErrorDisplay(el, 'tags_unavailable')
                            );
                            el.setAttribute(DATA_LISTENER_ADDED, 'true');
                            const obs = new MutationObserver((_, o) => {
                                if (!document.body.contains(el)) {
                                    el.removeEventListener('click',
                                        () => handleErrorDisplay(el, 'tags_unavailable')
                                    );
                                    o.disconnect();
                                }
                            });
                            obs.observe(document.body, { childList: true, subtree: true });
                        }
                    }
                });

                // Star rating hover
                $('#stars li')
                    .on('mouseover', function() {
                        const onStar = parseInt($(this).data('value'), 10);
                        $(this).siblings('li.star').each(function(i) {
                            $(this).toggleClass('hover', i < onStar);
                        });
                    })
                    .on('mouseout', function() {
                        $(this).siblings('li.star').removeClass('hover');
                    });

                // Star rating click
                $('#stars li').on('click', function() {
                    const el = this;
                    try {
                        const onStar = parseInt($(el).data('value'), 10);
                        const $stars = $(el).siblings('li.star').addBack();
                        $stars.removeClass('selected');
                        $stars.slice(0, onStar).addClass('selected');
                        const ratingValue = parseInt($('#stars li.selected').last().data('value'), 10);
                        $.ajax({
                            url: '{{ route(VW::JB.".application.rating", $jobApplication->id) }}',
                            type: 'POST',
                            data: {
                                rating: ratingValue,
                                _token: $('meta[name="csrf-token"]').attr('content')
                            },
                            cache: false,
                            success: () => {},
                            error: () => handleErrorDisplay(el, 'rating_unavailable')
                        });
                    } catch {
                        handleErrorDisplay(el, 'rating_unavailable');
                    }
                });

                // Stage change
                $(document).on('change', '.stages', function() {
                    const el = this;
                    try {
                        const id = $(el).val();
                        const scheduleId = el.getAttribute('data-scheduleid');
                        $.ajax({
                            url: "{{ route(VW::JB.'.application.stage.change') }}",
                            type: 'POST',
                            data: { stage: id, schedule_id: scheduleId, _token: "{{ csrf_token() }}" },
                            cache: false,
                            success: () => {
                                show_toastr('success', '{{ __("The candidate stage successfully changed") }}', 'success');
                                setTimeout(() => window.location.reload(), 1000);
                            },
                            error: () => handleErrorDisplay(el, 'stage_change_unavailable')
                        });
                    } catch {
                        handleErrorDisplay(el, 'stage_change_unavailable');
                    }
                });

            } catch {
                handleErrorDisplay(document.body, 'tags_unavailable');
            }
        })();
    </script>
@endpush
