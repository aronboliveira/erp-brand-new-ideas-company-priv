@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        StacksConstants,
        ViewsConstants as VW,
        ViewsClassNamesConstants as VC,
        YieldingConstants
    };
    use App\Models\{Project, ProjectTask, Utility};
    use Illuminate\Support\Facades\Route;
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@section(YieldingConstants::ADM_PG_TTL)
    {{__('Dashboard')}}
@endsection
@push(StacksConstants::ADM_SCR_PG)
    <script async>
        (() => {
            const ERR_FB = '# ERROR';
            const FL_CLIENT = 'data-client-localized';
            const FL_GUARD  = 'data-guard-msg';
            const LANG_KEY  = 'erp-np-lang';
            let errorMessage = '';
            
            const getMsg = (key, el) => {
                let msg = ERR_FB;
                if (el.getAttribute(FL_CLIENT) === 'true') {
                msg = el.getAttribute(FL_GUARD) || msg;
                } else {
                let lang = (sessionStorage.getItem(LANG_KEY) || document.documentElement.lang || 'en')
                    .toLowerCase().replace(/_/g, '-');
                lang = lang === 'pt-br' ? lang : lang.slice(0,2);
                msg = window.translations?.[lang]?.[key]
                    ?? el.getAttribute(FL_GUARD)
                    ?? window.translations?.['en']?.[key]
                    ?? msg;
                if (msg !== ERR_FB) {
                    el.setAttribute(FL_GUARD, msg);
                    el.setAttribute(FL_CLIENT, 'true');
                }
                }
                return msg;
            };
            
            const showError = message => {
                try {
                let c = document.getElementById('toast-container');
                if (!c) {
                    c = document.createElement('div');
                    c.id = 'toast-container';
                    document.body.appendChild(c);
                }
                const bs = !!document.querySelector('link[href*="bootstrap"]') && window.bootstrap?.Toast;
                if (bs) {
                    const t = document.createElement('div');
                    t.className = 'toast';
                    t.setAttribute('role','alert');
                    t.setAttribute('aria-live','assertive');
                    t.setAttribute('aria-atomic','true');
                    const b = document.createElement('div');
                    b.className = 'toast-body';
                    b.textContent = message;
                    t.appendChild(b);
                    c.appendChild(t);
                    bootstrap.Toast.getOrCreateInstance(t).show();
                } else {
                    alert(message);
                }
                } catch {
                alert(message);
                }
            };
            
            const onUp = () => {
                if (errorMessage) {
                showError(errorMessage);
                errorMessage = '';
                }
            };
            document.addEventListener('pointerup', onUp);
            new MutationObserver((m, obs) => {
                m.forEach(mut => Array.from(mut.removedNodes).forEach(n => {
                if (n === document.documentElement) {
                    document.removeEventListener('pointerup', onUp);
                    obs.disconnect();
                }
                }));
            }).observe(document.body,{ childList:true, subtree:true });
            
            document.addEventListener('DOMContentLoaded', () => {
                try {
                const el = document.querySelector('#task_overview');
                if (!el || typeof ApexCharts !== 'function') throw new Error('task_overview_unavailable');
                const opts = {
                    chart: { height:180, type:'area', toolbar:{ show:false } },
                    dataLabels:{ enabled:false },
                    stroke:{ width:2, curve:'smooth' },
                    series:[{
                    name:'Referral',
                    data: {!! json_encode(array_values($home_data['task_overview'])) !!}
                    }],
                    xaxis:{ categories:{!! json_encode(array_keys($home_data['task_overview'])) !!} },
                    colors:['#3ec9d6'],
                    fill:{ type:'solid' },
                    grid:{ strokeDashArray:4 },
                    legend:{ show:true, position:'top', horizontalAlign:'right' }
                };
                new ApexCharts(el, opts).render();
                } catch (e) {
                errorMessage = getMsg(e.message, document.querySelector('#task_overview')||document.body);
                }
                try {
                const el2 = document.querySelector('#timesheet_logged');
                if (!el2 || typeof ApexCharts !== 'function') throw new Error('timesheet_logged_unavailable');
                const opts2 = {
                    chart:{ height:300, type:'bar', toolbar:{ show:false } },
                    plotOptions:{ bar:{ horizontal:true, borderRadius:10, dataLabels:{ position:'top' } } },
                    colors:['#3ec9d6'],
                    dataLabels:{ enabled:true, offsetX:-6, style:{ fontSize:'12px', colors:['#fff'] } },
                    stroke:{ show:true, width:1, colors:['#fff'] },
                    grid:{ strokeDashArray:4 },
                    series:[{ data: {!! json_encode(array_values($home_data['timesheet_logged'])) !!} }],
                    xaxis:{ categories: {!! json_encode(array_keys($home_data['timesheet_logged'])) !!} }
                };
                new ApexCharts(el2, opts2).render();
                } catch (e) {
                errorMessage = getMsg(e.message, document.querySelector('#timesheet_logged')||document.body);
                }
            });
        })();
    </script>
@endpush
@section(YieldingConstants::ADM_BDC)
    <li class="breadcrumb-item">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
        {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="breadcrumb-item">{{__('Project')}}</li>
@endsection
@section('content')
    <div class="{{ VC::RW }}">
        @php
            $totProjTotal  = data_get($home_data ?? [], 'total_project.total');
            $totProjPct    = data_get($home_data ?? [], 'total_project.percentage');
            $totTaskTotal  = data_get($home_data ?? [], 'total_task.total');
            $totTaskPct    = data_get($home_data ?? [], 'total_task.percentage');
            $totExpTotal   = data_get($home_data ?? [], 'total_expense.total');
            $totExpPct     = data_get($home_data ?? [], 'total_expense.percentage');
            $projStatus    = is_iterable(data_get($home_data ?? [], 'project_status')) ? $home_data['project_status'] : [];
            $dueProjects   = data_get($home_data ?? [], 'due_project');
            $dueProjects   = is_iterable($dueProjects) ? $dueProjects : [];
            $dueTasks      = data_get($home_data ?? [], 'due_tasks');
            $dueTasks      = is_iterable($dueTasks) ? $dueTasks : [];
        @endphp

        <div class="col-lg-4 col-md-6">
            <div class="{{ VC::CD }}">
                <div class="card-body">
                    <div class="{{ VC::R_ALC_JCB }}">
                        <div class="col-auto mb-3 mb-sm-0">
                            <div class="d-flex align-items-center">
                                <div class="theme-avatar bg-primary">
                                    <i class="ti ti-cast"></i>
                                </div>
                                <div class="ms-3">
                                    <small class="text-muted">{{ __('Total') }}</small>
                                    <h6 class="m-0">{{ __('Projects') }}</h6>
                                </div>
                            </div>
                        </div>
                        <div class="col-auto text-end">
                            <h4 class="m-0">
                                {{ is_numeric($totProjTotal) ? $totProjTotal : __('Could not find total projects') }}
                            </h4>
                            <small class="text-muted">
                                @if(is_numeric($totProjPct))
                                    <span class="text-success">{{ $totProjPct }}%</span>
                                    {{ __('completed') }}
                                @else
                                    {{ __('Could not find projects completion percentage') }}
                                @endif
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-md-6">
            <div class="{{ VC::CD }}">
                <div class="card-body">
                    <div class="{{ VC::R_ALC_JCB }}">
                        <div class="col-auto mb-3 mb-sm-0">
                            <div class="d-flex align-items-center">
                                <div class="theme-avatar bg-info">
                                    <i class="ti ti-activity"></i>
                                </div>
                                <div class="ms-3">
                                    <small class="text-muted">{{ __('Total') }}</small>
                                    <h6 class="m-0">{{ __('Tasks') }}</h6>
                                </div>
                            </div>
                        </div>
                        <div class="col-auto text-end">
                            <h4 class="m-0">
                                {{ is_numeric($totTaskTotal) ? $totTaskTotal : __('Could not find total tasks') }}
                            </h4>
                            <small class="text-muted">
                                @if(is_numeric($totTaskPct))
                                    <span class="text-success">{{ $totTaskPct }}%</span>
                                    {{ __('completed') }}
                                @else
                                    {{ __('Could not find tasks completion percentage') }}
                                @endif
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-md-12">
            <div class="{{ VC::CD }}">
                <div class="card-body">
                    <div class="{{ VC::R_ALC_JCB }}">
                        <div class="col-auto mb-3 mb-sm-0">
                            <div class="d-flex align-items-center">
                                <div class="theme-avatar bg-danger">
                                    <i class="ti ti-report-money"></i>
                                </div>
                                <div class="ms-3">
                                    <small class="text-muted">{{ __('Total') }}</small>
                                    <h6 class="m-0">{{ __('Expense') }}</h6>
                                </div>
                            </div>
                        </div>
                        <div class="col-auto text-end">
                            <h4 class="m-0">
                                {{ is_numeric($totExpTotal) ? $totExpTotal : __('Could not find total expense') }}
                            </h4>
                            <small class="text-muted">
                                @if(is_numeric($totExpPct))
                                    <span class="text-success">{{ $totExpPct }}%</span>
                                    {{ __('Expense') }}
                                @else
                                    {{ __('Could not find expense percentage') }}
                                @endif
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="{{ VC::CD }}">
                <div class="card-header">
                    <h5>{{ __('Project Status') }}</h5>
                </div>
                <div class="card-body">
                    <div class="{{ VC::RW }}">
                        @if(empty($projStatus))
                            <div class="col-12">
                                <p class="mb-0">{{ __('No project status breakdown available') }}</p>
                            </div>
                        @else
                            @foreach($projStatus as $status => $val)
                                @php
                                    $totalPct = data_get($val,'total');
                                    $barPct   = data_get($val,'percentage',0);
                                    $label    = Project::$project_status[$status] ?? __('Status not available');
                                    $color    = Project::$status_color[$status] ?? 'secondary';
                                @endphp
                                <div class="col-md-6 col-sm-6 mb-5">
                                    <div class="align-items-start">
                                        <div class="ms-2">
                                            <p class="text-muted text-sm mb-0">{{ $label }}</p>
                                            <h3 class="mb-0 text-{{ $color }}">
                                                {{ is_numeric($totalPct) ? $totalPct.'%' : __('Could not find percentage') }}
                                            </h3>
                                            <div class="progress mb-0">
                                                <div class="progress-bar bg-{{ $color }}" style="width: {{ (float)$barPct }}%;"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="{{ VC::CD }}">
                <div class="card-header">
                    <h5>
                        {{ __('Tasks Overview') }}
                        <span class="float-end">
                            <small class="text-muted">{{ __('Total Completed task in last 7 days') }}</small>
                        </span>
                    </h5>
                </div>
                <div class="card-body">
                    <div id="task_overview"></div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="{{ VC::CD }}">
                <div class="card-header">
                    <h5>{{ __('Top Due Projects') }}</h5>
                </div>
                <div class="card-body project_table">
                    <div class="table-responsive">
                        <table class="{{ VC::TB }} table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>{{ __('Name') }}</th>
                                    <th>{{ __('End Date') }}</th>
                                    <th>{{ __('Status') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if(is_iterable($dueProjects) && count($dueProjects) > 0)
                                    @foreach($dueProjects as $due_project)
                                        @php
                                            $img = $due_project->project_image
                                                ? asset(Storage::url('/' . $due_project->project_image))
                                                : asset('assets/img/placeholder.png');
                                            $pName = $due_project->project_name ?: __('Could not find project name');
                                            $budget = isset($due_project->budget) ? $user?->priceFormat($due_project->budget) : __('Could not find project budget');
                                            $endDate = $due_project->end_date ? Utility::getDateFormated($due_project->end_date) : __('Could not find end date');
                                            $badgeColor = Project::$status_color[$due_project->status] ?? 'secondary';
                                            $badgeText  = Project::$project_status[$due_project->status] ?? __('Status not available');
                                        @endphp
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <img src="{{ $img }}" alt="{{ __('Project image') }}" class="wid-40 rounded-circle me-3">
                                                    <div>
                                                        <h6 class="mb-0">{{ $pName }}</h6>
                                                        <p class="mb-0">
                                                            <span class="text-success">{{ $budget }}</span>
                                                        </p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>{{ $endDate }}</td>
                                            <td>
                                                <span class="status_badge p-2 px-3 rounded badge bg-{{ $badgeColor }}">
                                                    {{ $badgeText }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr class="py-5">
                                        <td class="text-center mb-0" colspan="3">{{ __('No due projects found') }}</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="{{ VC::CD }}">
                <div class="card-header">
                    <h5>
                        {{ __('Timesheet Logged Hours') }}
                        <span><small class="float-end text-muted">{{ __('Last 7 days') }}</small></span>
                    </h5>
                </div>
                <div class="card-body project_table">
                    <div id="timesheet_logged"></div>
                </div>
            </div>
        </div>

        <div class="col-md-12">
            <div class="{{ VC::CD }}">
                <div class="card-header">
                    <h5>{{ __('Top Due Tasks') }}</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        @if(empty($dueTasks))
                            <div class="p-2">{{ __('No due tasks found') }}</div>
                        @else
                            @php
                                $tasksRoute = Route::has('projects.tasks.index') ? 'projects.tasks.index' : null;
                            @endphp
                            <table class="{{ VC::TB }}">
                                <tbody>
                                    @foreach($dueTasks as $due_task)
                                        @php
                                            $taskName   = $due_task->name ?: __('Could not find task name');
                                            $projId     = optional($due_task->project)->id;
                                            $projName   = optional($due_task->project)->project_name ?: __('Could not find project name');
                                            $priorityK  = $due_task->priority;
                                            $prioColor  = \App\Models\ProjectTask::$priority_color[$priorityK] ?? 'secondary';
                                            $prioLabel  = \App\Models\ProjectTask::$priority[$priorityK] ?? __('Priority not available');
                                            $progress   = data_get($due_task->taskProgress($due_task) ?? [], 'percentage');
                                            $progressV  = $progress !== null ? $progress : __('Could not find completion percentage');
                                            $href       = $tasksRoute ? route($tasksRoute, $projId) : '#';
                                        @endphp
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div>
                                                        <small class="text-muted">{{ __('Task') }}:</small>
                                                        <h6 class="m-0">
                                                            <a href="{{ $href }}" class="name mb-0 h6 text-sm">
                                                                {{ $taskName }}
                                                            </a>
                                                        </h6>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <small class="text-muted">{{ __('Project') }}:</small>
                                                <h6 class="m-0 h6 text-sm">{{ $projName }}</h6>
                                            </td>
                                            <td>
                                                <small class="text-muted">{{ __('Stage') }}:</small>
                                                <div class="d-flex align-items-center h6 text-sm mt-2">
                                                    <span class="full-circle bg-{{ $prioColor }}"></span>
                                                    <span class="ms-1">{{ $prioLabel }}</span>
                                                </div>
                                            </td>
                                            <td>
                                                <small class="text-muted">{{ __('Completion') }}:</small>
                                                <h6 class="m-0 h6 text-sm">
                                                    {{ is_numeric($progressV) ? $progressV : $progressV }}
                                                </h6>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
