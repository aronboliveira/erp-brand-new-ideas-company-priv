@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants as EL,
        PermissionsConstants as PM,
        StacksConstants as ST,
        ViewsConstants as VW,
        ViewClassNamesConstants as VC,
        YieldingConstants as YD
    };
    use App\Models\Utility;
    use Collective\Html\FormFacade as Form;
    use Illuminate\Support\Facades\{Auth, Route};
    use Illuminate\Support\Collection;

    $profile      = Utility::getFile('uploads/avatar/');
    $user         = Auth::user();
    $canFetchMsg  = is_callable([Utility::class,'fetchLinkMessage']);
    $lang         = is_callable([Utility::class,'fetchUserLang']) ? Utility::fetchUserLang(user:$user) : app()->getLocale();

    $dashUrl      = Route::has('dashboard') ? route('dashboard') : '#';
    $dashGuard    = ($canFetchMsg ? Utility::fetchLinkMessage($lang,'generics','dashboard_unavailable') : 'Dashboard route is unavailable. Please contact technical support or your domain administrator.') ?? __('Dashboard route is unavailable. Please contact technical support or your domain administrator.');

    $indexName    = VW::PRJ_RPT.'.index';
    $indexRoute   = Route::has($indexName) ? [$indexName] : ['#'];
    $indexUrl     = Route::has($indexName) ? route($indexName) : '#';
    $indexGuard   = ($canFetchMsg ? Utility::fetchLinkMessage($lang, VW::PRJ_RPT, 'project_report_index_route_unavailable') : 'Project Reports index route is unavailable. Please contact technical support or your domain administrator.') ?? __('Project Reports index route is unavailable. Please contact technical support or your domain administrator.');
    $resetClass   = 'reset-project-report-link';

    $projectsList = [];
    if (is_array($projects ?? null) && count($projects)) {
        $projectsList = $projects;
    } elseif (($projects ?? null) instanceof Collection && $projects->isNotEmpty()) {
        $projectsList = $projects;
    }

    $usersList = [];
    if (is_array($users ?? null) && count($users)) {
        $usersList = $users;
    } elseif (($users ?? null) instanceof Collection && $users->isNotEmpty()) {
        $usersList = $users;
    }
@endphp

@extends(EL::ADM)

@push(ST::ADM_SCR_PG)
@endpush

@section(YD::ADM_PG_TTL)
    {{ __('Project Reports') }}
@endsection

@section('title')
    <div class="d-inline-block">
        <h5 class="h4 d-inline-block font-weight-400 mb-0">{{ __('Project Reports') }}</h5>
    </div>
@endsection

@section(YD::ADM_BDC)
    <li class="breadcrumb-item">
        <a href="{{ $dashUrl }}"
           data-url="{{ $dashUrl }}"
           data-sv-localized="true"
           data-guard-msg="{{ $dashGuard }}"
           {{ $dashUrl !== '#' ? '' : 'aria-disabled=true' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="breadcrumb-item active" aria-current="page">{{ __('All Project') }}</li>
@endsection

@push(ST::ADM_CSS)
    <link rel="stylesheet" href="{{ asset('css/datatable/buttons.dataTables.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/routes/projects/reports/index.css') }}">
@endpush

@section(YD::ADM_CTT)
    @if(($user?->type ?? null) === PM::CPN)
        <div class="{{ VC::RW }}">
            <div class="{{ VC::C12 }}">
                <div class="{{ VC::MT3 }}">
                    <div class="{{ VC::CD }}">
                        <div class="card-body">
                            {!! Form::open(['route' => $indexRoute, 'method' => 'GET', 'id' => 'project_report_submit']) !!}
                                <div class="{{ VC::R_FLX_ALC_JCE }}">
                                    <div class="{{ VC::CL_POS2 }} mb-0">
                                        <div class="btn-box">
                                            {{ Form::label('users', __('Users'), ['class' => VC::FM_LB]) }}
                                            <select class="select form-select" name="all_users" id="all_users">
                                                <option value="">{{ __('All Users') }}</option>
                                                @if(Utility::isFilled($usersList) ?? [])
                                                    @foreach ($usersList as $usr)
                                                        <option value="{{ $usr->id }}" {{ (string)request('all_users') === (string)$usr->id ? 'selected' : '' }}>
                                                            {{ $usr->name }}
                                                        </option>
                                                    @endforeach
                                                @endif
                                            </select>
                                        </div>
                                    </div>
                                    <div class="{{ VC::CL_POS1 }}">
                                        <div class="btn-box">
                                            {{ Form::label('status', __('Status'), ['class' => VC::FM_LB]) }}
                                            {{ Form::select('status', ['' => __('Select Status')] + ($status ?? []), request('status'), ['class' => VC::FM_CT_SL]) }}
                                        </div>
                                    </div>
                                    <div class="{{ VC::CL_POS3 }}">
                                        <div class="btn-box">
                                            {{ Form::label('start_date', __('Start Date'), ['class' => VC::FM_LB]) }}
                                            {{ Form::date('start_date', request('start_date',''), ['class' => 'form-control month-btn']) }}
                                        </div>
                                    </div>
                                    <div class="{{ VC::CL_POS3 }}">
                                        <div class="btn-box">
                                            {{ Form::label('end_date', __('End Date'), ['class' => VC::FM_LB]) }}
                                            {{ Form::date('end_date', request('end_date',''), ['class' => 'form-control month-btn']) }}
                                        </div>
                                    </div>
                                    <div class="{{ VC::C_AT_FEND }}">
                                        <a href="#" class="{{ VC::BT_SM_PM }}" onclick="document.getElementById('project_report_submit').submit();return false;" data-toggle="tooltip" data-original-title="{{ __('apply') }}">
                                            <span class="btn-inner--icon"><i class="{{ VC::TI_SRC }}"></i></span>
                                        </a>
                                        <a href="{{ $indexUrl }}"
                                           id="{{ $resetClass }}"
                                           class="{{ VC::BT_SM_DG }} {{ $resetClass }}"
                                           data-url="{{ $indexUrl }}"
                                           data-sv-localized="true"
                                           data-guard-msg="{{ $indexGuard }}"
                                           data-toggle="tooltip"
                                           data-original-title="{{ __('Reset') }}">
                                            <span class="btn-inner--icon"><i class="{{ VC::TI_TRS_OFF }}"></i></span>
                                        </a>
                                    </div>
                                </div>
                            {!! Form::close() !!}
                        </div>
                        <script defer src="{{ asset('assets/js/routes/projects/reports/reset.js') }}"></script>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="{{ VC::C12 }} {{ VC::MT3 }}">
        <div class="{{ VC::CD }} table-card">
            <div class="card-header card-body table-border-style">
                <div class="table-responsive">
                    <table class="table datatable">
                        <thead>
                            <tr>
                                <th>{{ __('Projects') }}</th>
                                <th>{{ __('Start Date') }}</th>
                                <th>{{ __('Due Date') }}</th>
                                <th>{{ __('Projects Members') }}</th>
                                <th>{{ __('Completion') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th>{{ __('Action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                        @if(Utility::isFilled($projectsList) ?? [])
                            @foreach ($projectsList as $proj)
                                @php
                                    $projId    = data_get($proj,'id');
                                    $projName  = data_get($proj,'project_name', __('No project name available'));
                                    $startRaw  = data_get($proj,'start_date');
                                    $endRaw    = data_get($proj,'end_date');
                                    $isDateFormatAvailable = is_callable([Utility::class,'getDateFormated']);
                                    $startTxt  = $isDateFormatAvailable && $startRaw ? Utility::getDateFormated($startRaw) : __('No start date');
                                    $endTxt    = $isDateFormatAvailable && $endRaw ? Utility::getDateFormated($endRaw)   : __('No due date');
                                    $projUsers = data_get($proj,'users');

                                    $usersSafe = [];
                                    if (is_array($projUsers ?? null) && count($projUsers)) {
                                        $usersSafe = $projUsers;
                                    } elseif (($projUsers ?? null) instanceof Collection && $projUsers->isNotEmpty()) {
                                        $usersSafe = $projUsers;
                                    }

                                    $showName  = VW::PRJ_RPT.'.show';
                                    $showUrl   = Route::has($showName) ? route($showName, $projId) : '#';
                                    $showGuard = ($canFetchMsg ? Utility::fetchLinkMessage($lang, VW::PRJ_RPT, 'show_project_report_unavailable') : 'View project report route is unavailable. Please contact technical support or your domain administrator.') ?? __('View project report route is unavailable. Please contact technical support or your domain administrator.');
                                    $showClass = 'show-project-report-link';

                                    $editName  = VW::PRJ.'.edit';
                                    $editUrl   = Route::has($editName) ? route($editName, $projId) : '#';
                                    $editGuard = ($canFetchMsg ? Utility::fetchLinkMessage($lang, VW::PRJ, 'project_report_edit_route_unavailable') : 'Edit project route is unavailable. Please contact technical support or your domain administrator.') ?? __('Edit project route is unavailable. Please contact technical support or your domain administrator.');
                                    $editClass = 'edit-project-link';
                                @endphp
                                <tr>
                                    <td>
                                        <div class="{{ VC::DFL_AIC }}">
                                            <p class="mb-0"><a class="{{ VC::NM_HD_SM }}">{{ $projName }}</a></p>
                                        </div>
                                    </td>
                                    <td>{{ $startTxt }}</td>
                                    <td>{{ $endTxt }}</td>
                                    <td>
                                        <div class="avatar-group" id="project_{{ $projId }}">
                                            @if(Utility::isFilled($usersSafe) ?? [])
                                                @foreach($usersSafe as $idx => $usr)
                                                    @if($idx < 3)
                                                        <a href="#" class="{{ VC::AV_CC }}">
                                                            <img src="{{ $usr?->avatar ? asset('/storage/uploads/avatar/'.$usr->avatar) : asset('/storage/uploads/avatar/avatar.png') }}"
                                                                 title="{{ $usr?->name ?? '' }}" style="height:36px;width:36px;">
                                                        </a>
                                                    @else
                                                        @break
                                                    @endif
                                                @endforeach
                                                @php
                                                    $extra = is_array($usersSafe) ? count($usersSafe) : $usersSafe->count();
                                                @endphp
                                                @if($extra > 3)
                                                    <a href="#" class="{{ VC::AV_CC_SM }}">
                                                        <img avatar="+ {{ $extra - 3 }}" style="height:36px;width:36px;">
                                                    </a>
                                                @endif
                                            @else
                                                {{ __('-') }}
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        @php
                                            $pp = method_exists($proj,'projectProgress') && isset($last_task) && isset($last_task->id)
                                                ? $proj->projectProgress($proj,$last_task->id)
                                                : ['percentage'=>'0%','color'=>'secondary'];
                                        @endphp
                                        <h6 class="mb-0 text-success">{{ $pp['percentage'] }}</h6>
                                        <div class="progress mb-0">
                                            <div class="progress-bar bg-{{ $pp['color'] }}" style="width: {{ $pp['percentage'] }};"></div>
                                        </div>
                                    </td>
                                    <td>
                                        @php
                                            $statusKey = data_get($proj,'status');
                                            $stTxt = \App\Models\Project::$project_status[$statusKey] ?? __('Unknown');
                                            $stClr = \App\Models\Project::$status_color[$statusKey] ?? 'secondary';
                                        @endphp
                                        <span class="badge bg-{{ $stClr }} p-2 px-3 rounded status_badge">{{ __($stTxt) }}</span>
                                    </td>
                                    <td>
                                        @can(PM::MNG_PRJ)
                                            <div class="{{ VC::ACT_BTN_WRN }}">
                                                <a href="{{ $showUrl }}"
                                                   id="{{ $showClass }}-{{ $projId }}"
                                                   class="{{ VC::BT_SM_CT }} {{ $showClass }}"
                                                   data-url="{{ $showUrl }}"
                                                   data-sv-localized="true"
                                                   data-guard-msg="{{ $showGuard }}"
                                                   data-bs-toggle="tooltip"
                                                   title="{{ __('View Project Report') }}"
                                                   data-original-title="{{ __('Detail') }}">
                                                    <i class="{{ VC::TI_EYE_WT }}"></i>
                                                </a>
                                            </div>
                                            <script defer src="{{ asset('assets/js/routes/projects/reports/show.js') }}"></script>
                                        @endcan
                                        @can('edit project')
                                            <div class="{{ VC::ACT_BTN_PRIM }}">
                                                <a href="{{ $editUrl }}"
                                                   id="{{ $editClass }}-{{ $projId }}"
                                                   class="{{ VC::BT_SM_FL_CT }} {{ $editClass }}"
                                                   data-url="{{ $editUrl }}"
                                                   data-sv-localized="true"
                                                   data-guard-msg="{{ $editGuard }}"
                                                   data-ajax-popup="true"
                                                   data-size="lg"
                                                   data-bs-toggle="tooltip"
                                                   title="{{ __('Edit') }}"
                                                   data-title="{{ __('Edit Project') }}">
                                                    <i class="{{ VC::TI_PC_WT }}"></i>
                                                </a>
                                            </div>
                                            <script defer src="{{ asset('assets/js/routes/projects/reports/edit.js') }}"></script>
                                        @endcan
                                    </td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="7" class="text-center text-muted">{{ __('No projects found.') }}</td>
                            </tr>
                        @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

