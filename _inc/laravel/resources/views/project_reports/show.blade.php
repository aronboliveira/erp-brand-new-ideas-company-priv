@php
    try {
$user        = Auth::user();
        $canFetchMsg = is_callable([Utility::class,'fetchLinkMessage']);
        $lang        = is_callable([Utility::class,'fetchUserLang']) ? Utility::fetchUserLang(user:$user) : app()->getLocale();

        $dashUrl     = Route::has('dashboard') ? route('dashboard') : '#';
        $dashGuard   = ($canFetchMsg ? Utility::fetchLinkMessage($lang,'generics','dashboard_unavailable') : 'Dashboard route is unavailable. Please contact technical support or your domain administrator.') ?? __('Dashboard route is unavailable. Please contact technical support or your domain administrator.');

        $idxBase     = VW::PRJ_RPT . '.index';
        $idxKebab    = Str::kebab($idxBase);
        $canViewIdx  = Gate::check('manage project') || Gate::check('view project report');
        $idxName     = ($canViewIdx && Route::has($idxBase)) ? $idxBase : (($canViewIdx && Route::has($idxKebab)) ? $idxKebab : null);
        $idxUrl      = $idxName ? route($idxName) : '#';
        $idxGuard    = ($canFetchMsg ? Utility::fetchLinkMessage($lang, VW::PRJ_RPT, 'project_report_index_route_unavailable') : 'Project report index route is unavailable. Please contact technical support or your domain administrator.') ?? __('Project report index route is unavailable. Please contact technical support or your domain administrator.');

        $tasksList = [];
        if (is_array($tasks ?? null) && count($tasks)) {
            $tasksList = $tasks;
        } elseif (($tasks ?? null) instanceof Collection && $tasks->isNotEmpty()) {
            $tasksList = $tasks;
        }

        $projUsers = [];
        if (is_array($project->users ?? null) && count($project->users)) {
            $projUsers = $project->users;
        } elseif (($project->users ?? null) instanceof Collection && $project->users->isNotEmpty()) {
            $projUsers = $project->users;
        }

        $projMilestones = [];
        if (is_array($project->milestones ?? null) && count($project->milestones)) {
            $projMilestones = $project->milestones;
        } elseif (($project->milestones ?? null) instanceof Collection && $project->milestones->isNotEmpty()) {
            $projMilestones = $project->milestones;
        }
    } catch (\Throwable $e) {
        \Log::error('project_reports/show — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    }
@endphp

@extends(EL::ADM)

@section(YD::ADM_PG_TTL)
    {{ __('Project Reports') }}
@endsection

@section('title')
    <div class="d-inline-block">
        <h5 class="h4 d-inline-block font-weight-400 {{ VC::MB0 }}">{{ __('Project Reports') }}</h5>
    </div>
@endsection

@section(YD::ADM_BDC)
    <li class="{{ VC::BCI }}">
        <a href="{{ $dashUrl }}"
           data-url="{{ $dashUrl }}"
           data-sv-localized="true"
           data-guard-msg="{{ base64_encode($dashGuard) }}"
           {{ $dashUrl !== '#' ? '' : 'aria-disabled=true' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="{{ VC::BCI }}">
        <a id="project-report-index-link"
           href="{{ $idxUrl }}"
           data-url="{{ $idxUrl }}"
           data-sv-localized="true"
           data-guard-msg="{{ base64_encode($idxGuard) }}"
           {{ $idxUrl !== '#' ? '' : 'aria-disabled=true' }}>
            {{ __('Project Report') }}
        </a>
    </li>
    <li class="{{ VC::BCI }}">{{ ucwords($project->project_name ?? __('(no name)')) }}</li>
@endsection

@push(ST::ADM_CSS)
    <style>
        .table.dataTable.no-footer{border-bottom:none!important}
        .display-none{display:none!important}
    </style>
@endpush

@section(YD::ADM_ACT_BTN)
    <div class="{{ VC::FEND }}">
        <a href="#" onclick="typeof saveAsPDF === 'function' && saveAsPDF();" class="{{ VC::BT_SM_PM }} dwn" data-toggle="tooltip" title="{{ __('Project Report Download') }}">
            <i class="{{ VC::TI_DWN }}"></i>
        </a>
    </div>
@endsection

@section(YD::ADM_CTT)
    <div class="{{ VC::RW }}">
        <div class="{{ VC::CS12 }}">
            @if(!empty($project) && isset($project->id))
                <div class="{{ VC::RW }}" id="printableArea">
                    <div class="{{ VC::CM6 }}">
                        <div class="{{ VC::CD }}">
                            <div class="{{ VC::CD_HD }}">
                                <h5>{{ __('Overview') }}</h5>
                            </div>
                            <div class="{{ VC::CD_BD }}" style="min-height:280px;">
                                <div class="{{ VC::R_ALC }}">
                                    <div class="col-7">
                                        <table class="{{ VC::TB }}">
                                            <tbody>
                                                <tr class="border-0">
                                                    <th class="border-0">{{ __('Project Name') }}:</th>
                                                    <td class="border-0">{{ !empty($project->project_name) ? $project->project_name : __('No name available for project') }}</td>
                                                </tr>
                                                <tr>
                                                    <th class="border-0">{{ __('Project Status') }}:</th>
                                                    <td class="border-0">
                                                        @php
                                                            $pStat = $project[PJ::COL_STAT] ?? null;
@endphp
                                                        @if($pStat === PJ::STT_INP_K)
                                                            <div class="{{ VC::BDG }} {{ VC::BG_P }} p-2 {{ VC::PX3 }} rounded">{{ __('In Progress') }}</div>
                                                        @elseif($pStat === PJ::STT_ONH_K)
                                                            <div class="{{ VC::BDG }} bg-secondary p-2 {{ VC::PX3 }} rounded">{{ __('On Hold') }}</div>
                                                        @elseif($pStat === PJ::STT_CPT_K)
                                                            <div class="{{ VC::BDG }} bg-danger p-2 {{ VC::PX3 }} rounded">{{ __('Canceled') }}</div>
                                                        @else
                                                            <div class="{{ VC::BDG }} bg-warning p-2 {{ VC::PX3 }} rounded">{{ __('Finished') }}</div>
                                                        @endif
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th class="border-0">{{ __('Start Date') }}:</th>
                                                    <td class="border-0">{{ $project->start_date ?? __('No start date') }}</td>
                                                </tr>
                                                <tr>
                                                    <th class="border-0">{{ __('End Date') }}:</th>
                                                    <td class="border-0">{{ $project->end_date ?? __('No end date') }}</td>
                                                </tr>
                                                <tr>
                                                    <th class="border-0">{{ __('Total Members') }}:</th>
                                                    <td class="border-0">{{ (int) (is_countable($projUsers) ? count($projUsers) : ($projUsers->count() ?? 0)) }}</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="col-5">
                                        @php
                                            try {
                                                $ppCalc = (method_exists($project,'projectProgress') && isset($lastTask?->id))
                                                    ? $project->projectProgress($project, $lastTask->id)
                                                    : ['percentage'=>'0%'];
                                                $ppText = (string)($ppCalc['percentage'] ?? '0%');
                                                $ppNum  = (int)trim($ppText, '%');
                                                $status = $ppNum>0 && $ppNum<=25 ? 'red' : ($ppNum<=50 ? 'orange' : ($ppNum<=75 ? 'blue' : ($ppNum<=100 ? 'green' : '')));
                                            } catch (\Throwable $e) {
                                                \Log::error('project_reports/show — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                            }
@endphp
                                        <div class="circular-progressbar p-0">
                                            <div class="flex-wrapper">
                                                <div class="single-chart">
                                                    <svg viewBox="0 0 36 36" class="circular-chart orange {{ $status }}">
                                                        <path class="circle-bg" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                                                        <path class="circle" stroke-dasharray="{{ $ppNum }}, 100" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                                                        <text x="18" y="20.35" class="percentage">{{ $ppNum }}%</text>
                                                    </svg>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    @php
                        $milePctRaw = $project->projectMilestoneProgress()['percentage'] ?? '0%';
                        $milePct = (int)trim($milePctRaw,'%');
@endphp
                    <div class="{{ VC::CM6 }}">
                        <div class="{{ VC::CD }}">
                            <div class="{{ VC::CD_HD }}" style="padding:25px 35px!important;">
                                <div class="{{ VC::DFL_AIC_JCB }}">
                                    <div class="{{ VC::RW }}">
                                        <h5 class="{{ VC::MB0 }}">{{ __('Milestone Progress') }}</h5>
                                    </div>
                                </div>
                            </div>
                            <div class="{{ VC::CD_BD }}">
                                <div class="chart">
                                    <div id="milestone-chart" class="chart-canvas" height="150" data-progress="{{ $milePct }}"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="{{ VC::CM3 }}">
                        <div class="{{ VC::CD }}">
                            <div class="{{ VC::CD_HD }}">
                                <div class="{{ VC::FEND }}"><a href="#" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('Referrals') }}"><i></i></a></div>
                                <h5>{{ __('Task Priority') }}</h5>
                            </div>
                            <div class="{{ VC::CD_BD }}" style="min-height:280px;">
                                <div class="{{ VC::RW }} {{ VC::ALC }}"><div class="{{ VC::C12 }}"><div id="chart_priority"></div></div></div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-5">
                        <div class="{{ VC::CD }}">
                            <div class="{{ VC::CD_HD }}">
                                <div class="{{ VC::FEND }}"><a href="#" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('Referrals') }}"><i></i></a></div>
                                <h5>{{ __('Task Status') }}</h5>
                            </div>
                            <div class="{{ VC::CD_BD }}" style="min-height:280px;">
                                <div class="{{ VC::RW }} {{ VC::ALC }}"><div class="{{ VC::C12 }}"><div id="chart"></div></div></div>
                            </div>
                        </div>
                    </div>

                    <div class="{{ VC::CM4 }}">
                        <div class="{{ VC::CD }}">
                            <div class="{{ VC::CD_HD }}">
                                <div class="{{ VC::FEND }}"><a href="#" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('Referrals') }}"><i></i></a></div>
                                <h5>{{ __('Hours Estimation') }}</h5>
                            </div>
                            <div class="{{ VC::CD_BD }}" style="min-height:280px;">
                                <div class="{{ VC::RW }} {{ VC::ALC }}"><div class="{{ VC::C12 }}"><div id="chart-hours"></div></div></div>
                            </div>
                        </div>
                    </div>

                    @php
                        $lastStage = TaskStage::where(DC::COL_TABLE_CREATOR, $user?->creatorId())->orderBy('id','desc')->first();
@endphp

                    <div class="col-md-5">
                        <div class="{{ VC::CD }}">
                            <div class="{{ VC::CD_HD }}"><h5>{{ __('Users') }}</h5></div>
                            <div class="{{ VC::CD_BD_TB_BD }}">
                                <div class="{{ VC::TB_RSP }} milestone">
                                    <table class="{{ VC::TB }}">
                                        <thead>
                                            <tr>
                                                <th>{{ __('Name') }}</th>
                                                <th>{{ __('Assigned Tasks') }}</th>
                                                <th>{{ __('Done Tasks') }}</th>
                                                <th>{{ __('Logged Hours') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @if(Utility::isFilled($projUsers) ?? [])
                                                @foreach($projUsers as $usr)
                                                    @php
                                                        try {
                                                            $total_user_task = ProjectTask::where(PJ::COL_PJ_ID, $project->id)
                                                                ->whereRaw("FIND_IN_SET(?, ".PJ::COL_ASGN.") > 0", [$usr?->id])
                                                                ->count();

                                                            $total_complete_task = 0;
                                                            if($lastStage){
                                                                $total_complete_task = ProjectTask::where(PJ::COL_PJ_ID, $project->id)
                                                                    ->where('stage_id', $lastStage->id)
                                                                    ->whereRaw("FIND_IN_SET(?, ".PJ::COL_ASGN.") > 0", [$usr?->id])
                                                                    ->count();
                                                            }

                                                            $timesheets = Timesheet::where(PJ::COL_PJ_ID, $project->id)
                                                                ->where(DC::COL_TABLE_CREATOR, $usr?->id)
                                                                ->get();

                                                            $logged_hours = 0;
                                                            foreach($timesheets as $t){
                                                                $h = (int)date('H', strtotime($t->time));
                                                                $m = (int)date('i', strtotime($t->time));
                                                                $logged_hours += $h + ($m/60);
                                                            }
                                                            $hours_format_number = number_format($logged_hours, 2, '.', '');
                                                        } catch (\Throwable $e) {
                                                            \Log::error('project_reports/show — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                        }
@endphp
                                                    <tr>
                                                        <td>{{ $usr?->name ?? __('(no name)') }}</td>
                                                        <td>{{ $total_user_task }}</td>
                                                        <td>{{ $total_complete_task }}</td>
                                                        <td>{{ $hours_format_number }}</td>
                                                    </tr>
                                                @endforeach
                                            @else
                                                <tr><td colspan="4" class="{{ VC::TXCT_MT }}">{{ __('No users found.') }}</td></tr>
                                            @endif
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-7">
                        <div class="{{ VC::CD }}">
                            <div class="{{ VC::CD_HD }}"><h5>{{ __('Milestones') }}</h5></div>
                            <div class="{{ VC::CD_BD_TB_BD }}">
                                <div class="{{ VC::TB_RSP }} milestone">
                                    <table class="{{ VC::TB }}">
                                        <thead>
                                            <tr>
                                                <th>{{ __('Name') }}</th>
                                                <th>{{ __('Progress') }}</th>
                                                <th>{{ __('Cost') }}</th>
                                                <th>{{ __('Status') }}</th>
                                                <th>{{ __('Start Date') }}</th>
                                                <th>{{ __('End Date') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @if(Utility::isFilled($projMilestones) ?? [])
                                                @foreach($projMilestones as $milestone)
                                                    <tr>
                                                        <td>{{ $milestone->title ?? __('(no title)') }}</td>
                                                        <td>
                                                            <div class="progress_wrapper">
                                                                <div class="{{ VC::PG }}">
                                                                    <div class="progress-bar" role="progressbar" style="width: {{ (int)($milestone->progress ?? 0) }}%;" aria-valuemin="0" aria-valuemax="100"></div>
                                                                </div>
                                                                <div class="progress_labels">
                                                                    <div class="total_progress"><strong>{{ (int)($milestone->progress ?? 0) }}%</strong></div>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td>{{ $milestone->cost ?? 0 }}</td>
                                                        <td>
                                                            @if(($milestone[PJ::COL_STAT] ?? null) === PJ::STT_CPT_K)
                                                                <label class="{{ VC::BDG }} bg-success p-2 {{ VC::PX3 }} rounded">{{ __('Complete') }}</label>
                                                            @else
                                                                <label class="{{ VC::BDG }} bg-warning p-2 {{ VC::PX3 }} rounded">{{ __('Incomplete') }}</label>
                                                            @endif
                                                        </td>
                                                        <td>{{ $milestone->start_date ?? '-' }}</td>
                                                        <td>{{ $milestone->due_date ?? '-' }}</td>
                                                    </tr>
                                                @endforeach
                                            @else
                                                <tr><td colspan="6" class="{{ VC::TXCT_MT }}">{{ __('No milestones found.') }}</td></tr>
                                            @endif
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="{{ VC::C12 }}">
                    <div class="{{ VC::RW }} {{ VC::DFL_AIC }} {{ VC::JCE }}">
                        <div class="col-1">
                            @php
                                try {
                                    $expBase   = VW::PRJ_RPT . '.export';
                                    $expKebab  = Str::kebab($expBase);
                                    $expName   = (Route::has($expBase) ? $expBase : (Route::has($expKebab) ? $expKebab : null));
                                    $expUrl    = $expName ? route($expName, $project->id) : '#';
                                    $expGuard  = ($canFetchMsg ? Utility::fetchLinkMessage($lang, VW::PRJ_RPT, 'project_report_export_route_unavailable') : 'Project report export route is unavailable. Please contact technical support or your domain administrator.') ?? __('Project report export route is unavailable. Please contact technical support or your domain administrator.');
                                    $expId     = 'project-report-export-link-'.$project->id;
                                } catch (\Throwable $e) {
                                    \Log::error('project_reports/show — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                }
@endphp
                            <a id="{{ $expId }}" href="{{ $expUrl }}" data-url="{{ $expUrl }}" data-sv-localized="true" data-guard-msg="{{ base64_encode($expGuard) }}" class="{{ VC::BT_PRM }} {{ VC::TXT_WT }}">{{ __('Export') }}</a>
                        </div>
                    </div>
                </div>
            @else
                <div class="{{ VC::ALT_DNG }} {{ VC::MB4 }}" role="alert">
                    {{ __('No project data available to display the report.') }}
                </div>
            @endif
            <div class="{{ VC::CS12 }} {{ VC::MT3 }}">
                <div class="{{ VC::CD }}">
                    <div class="card-body {{ VC::MT3 }} mx-2">
                        <div class="{{ VC::RW }} mt-2">
                            <div class="{{ VC::TB_RSP }}">
                                <table class="{{ VC::TB }} datatable">
                                    <thead>
                                        <tr>
                                            <th>{{ __('Task Name') }}</th>
                                            <th>{{ __('Milestone') }}</th>
                                            <th>{{ __('Start Date') }}</th>
                                            <th>{{ __('End Date') }}</th>
                                            <th>{{ __('Assigned to') }}</th>
                                            <th>{{ __('Total Logged Hours') }}</th>
                                            <th>{{ __('Priority') }}</th>
                                            <th>{{ __('Stage') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    @if(is_array($tasksList) && count($tasksList) || ($tasksList instanceof Collection && $tasksList->isNotEmpty()))
                                        @foreach($tasksList as $task)
                                            @php
                                                try {
                                                    $tTimes = Timesheet::where(PJ::COL_PJ_ID, $project->id)->where('task_id',$task->id)->get();
                                                    $tHours = 0;
                                                    foreach($tTimes as $ts){ $tHours += (int)date('H',strtotime($ts->time)) + ((int)date('i',strtotime($ts->time)) / 60); }
                                                    $hours_format_number = number_format($tHours, 2, '.', '');

                                                    $showBase = VW::PRJ . '.tasks.show';
                                                    $showK    = Str::kebab($showBase);
                                                    $showName = Route::has($showBase) ? $showBase : (Route::has($showK) ? $showK : null);
                                                    $showUrl  = $showName ? route($showName, [$project->id, $task->id]) : '#';
                                                    $showId   = 'project-task-show-link-'.$project->id.'-'.$task->id;
                                                    $showGuard= ($canFetchMsg ? Utility::fetchLinkMessage($lang, VW::PRJ, 'project_tasks_show_route_unavailable') : 'Project tasks show route is unavailable. Please contact technical support or your domain administrator.') ?? __('Project tasks show route is unavailable. Please contact technical support or your domain administrator.');

                                                    $taskUsers = $task->users();
                                                    $taskUsersArr = [];
                                                    if (is_array($taskUsers ?? null) && count($taskUsers)) { $taskUsersArr = $taskUsers; }
                                                    elseif (($taskUsers ?? null) instanceof Collection && $taskUsers->isNotEmpty()) { $taskUsersArr = $taskUsers; }
                                                } catch (\Throwable $e) {
                                                    \Log::error('project_reports/show — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                }
@endphp
                                            <tr>
                                                <td>
                                                    <a id="{{ $showId }}"
                                                       href="{{ $showUrl }}"
                                                       data-url="{{ $showUrl }}"
                                                       data-sv-localized="true"
                                                       data-guard-msg="{{ base64_encode($showGuard) }}"
                                                       data-size="md"
                                                       data-ajax-popup="true"
                                                       class="{{ VC::DRP_IT }}">
                                                        {{ $task->name ?? __('(no name)') }}
                                                    </a>
                                                </td>
                                                <td>{{ !empty($task->milestone) && isset($task->milestone->title) ? $task->milestone->title : __('No milestone title available') }}</td>
                                                <td>{{ $task->start_date ?? __('No start date available') }}</td>
                                                <td>{{ $task->end_date ?? __('No end date available') }}</td>
                                                <td>
                                                    <div class="avatar-group">
                                                        @if(Utility::isFilled($taskUsersArr) ?? [])
                                                            @foreach($taskUsersArr as $k=>$tu)
                                                                @if($k<3)
                                                                    <a href="#" class="{{ VC::AV_CC_SM }}"><img src="{{ $tu->getImgImageAttribute() }}" title="{{ $tu?->name }}"></a>
                                                                @else @break @endif
                                                            @endforeach
                                                            @php
 $extra = is_array($taskUsersArr)?count($taskUsersArr):$taskUsersArr->count();
@endphp
                                                            @if($extra>3)
                                                                <a href="#" class="{{ VC::AV_CC_SM }}"><img avatar="+ {{ $extra-3 }}"></a>
                                                            @endif
                                                        @else
                                                            {{ __('-') }}
                                                        @endif
                                                    </div>
                                                </td>
                                                <td>{{ $hours_format_number }}</td>
                                                <td>
                                                    @php
                                                        try {
                                                            $pKey = $task->priority ?? null;
                                                            $pTxt = ProjectTask::$priority[$pKey] ?? __('Unknown');
                                                            $pClr = ProjectTask::$priority_color[$pKey] ?? 'secondary';
                                                        } catch (\Throwable $e) {
                                                            \Log::error('project_reports/show — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                        }
@endphp
                                                    <span class="{{ VC::BDG }} p-2 {{ VC::PX3 }} status_badge rounded bg-{{ $pClr }}">{{ $pTxt }}</span>
                                                </td>
                                                <td>{{ !empty($task->stage) && isset($task->stage->name) ? $task->stage->name : __('No name available for Task stage') }}</td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr><td colspan="8" class="{{ VC::TXCT_MT }}">{{ __('No tasks found.') }}</td></tr>
                                    @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection

@push(ST::ADM_SCR_PG)
    <script defer src="{{ asset('assets/js/core/route-guard.js') }}"></script>
    <script src="{{ asset('assets/js/datatables.min.js') }}"></script>
    <script src="{{ asset('js/html2pdf.bundle.min.js') }}"></script>
    <script async src="{{ asset('assets/js/plugins/apexcharts.min.js')}}"></script>
    <script defer src="{{ asset('assets/js/routes/projects/reports/detail.js') }}"></script>
    <script async src="{{ asset('assets/js/routes/projects/reports/lang/show.js') }}"></script>
    <script defer src="{{ asset('assets/js/routes/projects/reports/index.js') }}"></script>
    <script defer src="{{ asset('assets/js/routes/projects/reports/print.js') }}"></script>
    <script async>
        (()=>{
            const errFb='# ERROR';
            const dataClientLocalized='data-client-localized';
            const dataGuardMsg='data-guard-msg';
            const dataListenerAdded='data-listener-added';
            const dataRendered='data-chart-rendered';

            const getLocalizedMessage=(el,msgKey)=>{
            let msg=errFb;
            if(el?.getAttribute('data-sv-localized')==='true'||el?.getAttribute(dataClientLocalized)==='true'){
                msg=el.getAttribute(dataGuardMsg)||errFb;
            }else{
                let lang=(window.sessionStorage.getItem('erp-np-lang')||document.documentElement.lang||'en').toLowerCase().replace(/_/g,'-');
                lang = (lang==='pt-br') ? lang : lang.slice(0,2);
                msg = window.translations?.[lang]?.[msgKey] || el?.getAttribute?.(dataGuardMsg) || window.translations?.en?.[msgKey] || errFb;
                if(msg!==errFb){ el?.setAttribute?.(dataGuardMsg,msg); el?.setAttribute?.(dataClientLocalized,'true'); }
            }
            return msg;
            };

            const showToast=(text)=>{
            const hasBootstrap=document.querySelector('link[href*="bootstrap"]') && window.bootstrap?.Toast;
            if(hasBootstrap){
                if(!document.querySelector('#error-toast')){
                const t=document.createElement('div');
                t.id='error-toast';
                t.className='toast align-items-center text-bg-danger border-0';
                t.setAttribute('role','alert'); t.setAttribute('aria-live','assertive'); t.setAttribute('aria-atomic','true');
                t.innerHTML=`<div class="{{ VC::DFL }}"><div class="toast-body">${text}</div><button type="button" class="{{ VC::BT_CL }} btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="{{ __('Close') }}"></button></div>`;
                document.body.appendChild(t);
                }
                new bootstrap.Toast(document.querySelector('#error-toast')).show();
            }else{ alert(text); }
            };

            const attachGuardOnce=(el,key,ev='click')=>{
            if(!el||el.getAttribute(dataListenerAdded)==='true') return;
            const handler=()=>showToast(getLocalizedMessage(el,key));
            el.addEventListener(ev,handler,{once:true});
            el.setAttribute(dataListenerAdded,'true');
            const mo=new MutationObserver((_,o)=>{ if(!document.body.contains(el)){ el.removeEventListener(ev,handler); o.disconnect(); }});
            mo.observe(document.body,{childList:true,subtree:true});
            };

            const renderChart=(selector, options, key)=>{
            try{
                if(typeof ApexCharts==='undefined'){
                if (
                    window.location.hostname === "localhost" ||
                    window.location.hostname === "127.0.0.1"
                ) console.error("ApexCharts unavailable");
                attachGuardOnce(document.querySelector(selector)||document.body,key); return; }
                const el=document.querySelector(selector);
                if(!el){ attachGuardOnce(document.body,key); return; }
                if(el.getAttribute(dataRendered)==='true') return;
                const chart=new ApexCharts(el, options);
                chart.render();
                el.setAttribute(dataRendered,'true');
            }catch{ attachGuardOnce(document.querySelector(selector)||document.body,key); }
            };

            // Expose PDF function
            window.saveAsPDF=()=>{
            const el=document.getElementById('printableArea');
            try{
                if(!window.html2pdf || !el){ throw new Error('html2pdf missing or target not found'); }
                const $nameSrc = $('#chart-hours');
                const fileName = (typeof $nameSrc.val==='function' && $nameSrc.val()) || ($nameSrc.text && $nameSrc.text()) || 'report';
                const opt={ margin:0.3, filename:fileName, image:{type:'jpeg',quality:1}, html2canvas:{scale:4,dpi:72,letterRendering:true}, jsPDF:{unit:'in',format:'A2'} };
                html2pdf().set(opt).from(el).save();
            }catch{ attachGuardOnce(el||document.body,'pdf_unavailable','pointerup'); }
            };

            // Render charts after DOM ready
            $(()=>{
            // Milestone radialBar (semicircle)
            const optionsMilestone={
                series: {!! json_encode($mile_percentage) !!},
                chart:{ height:475, type:'radialBar', offsetY:-20, sparkline:{enabled:true} },
                plotOptions:{ radialBar:{ startAngle:-90, endAngle:90, track:{ background:'#e7e7e7', strokeWidth:'97%', margin:5 }, dataLabels:{ name:{show:true}, value:{offsetY:-50,fontSize:'20px'} } } },
                grid:{ padding:{ top:-10 } },
                colors:['#51459d'],
                labels:['Progress']
            };
            renderChart('#milestone-chart', optionsMilestone, 'chart_milestone_unavailable');

            // Priority bar
            const optionsPriority={
                series:[{ data: {!! json_encode($arrProcessPer_priority) !!} }],
                chart:{ height:210, type:'bar' },
                colors:['#6fd943','#ff3a6e','#3ec9d6'],
                plotOptions:{ bar:{ columnWidth:'50%', distributed:true } },
                dataLabels:{ enabled:false },
                legend:{ show:true },
                xaxis:{ categories:{!! json_encode($arrProcess_Label_priority) !!}, labels:{ style:{ colors:{!! json_encode($chartData['color']) !!} } } }
            };
            renderChart('#chart_priority', optionsPriority, 'chart_priority_unavailable');

            // Status pie
            const optionsPie={
                series: {!! json_encode($arrProcessPer_status_task) !!},
                chart:{ width:380, type:'pie' },
                colors: {!! json_encode($chartData['color']) !!},
                labels: {!! json_encode($arrProcess_Label_status_tasks) !!},
                responsive:[{ breakpoint:480, options:{ chart:{ width:100 }, legend:{ position:'bottom' } } }]
            };
            renderChart('#chart', optionsPie, 'chart_pie_unavailable');

            // Hours bar (horizontal)
            const optionsHours={
                series:[{ data:[{!! json_encode($esti_logged_hour_chart) !!},{!! json_encode($logged_hour_chart) !!}] }],
                chart:{ height:210, type:'bar' },
                colors:['#963aff','#ffa21d'],
                plotOptions:{ bar:{ horizontal:true, columnWidth:'30%', distributed:true } },
                dataLabels:{ enabled:false },
                legend:{ show:true },
                xaxis:{ categories:['Estimated Hours','Logged Hours '] }
            };
            renderChart('#chart-hours', optionsHours, 'chart_hours_unavailable');
            });
        })();
    </script>
@endpush
