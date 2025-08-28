@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        PermissionsConstants,
        StacksConstants,
        UsersConstants,
        ViewsConstants as VW,
        ViewClassNamesConstants as VC,
        YieldingConstants,
    };
    use App\Models\Utility;
    use Illuminate\Support\Facades\{Auth,Route};
    $user = Auth::user();
    $lang = Utility::fetchUserLang(user: $user);
@endphp
@extends(ExtendingLayoutsConstants::ADM)

@section(YieldingConstants::ADM_PG_TTL)
    {{ __('Dashboard')}}
@endsection
@section(YieldingConstants::ADM_BDC)
    <li class="breadcrumb-item">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
        {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="breadcrumb-item">{{__('Client')}}</li>
@endsection

@push('theme-script')
    <script async src="{{ asset('assets/js/plugins/apexcharts.min.js') }}"></script>
@endpush

@push(StacksConstants::ADM_SCR_PG)
    <script async src="{{ asset('assets/js/routes/dashboards/clients/lang/chart.js') }}"></script>
    <script async>
        (() => {
            const ERR_FB = '# ERROR';
            const DS_CLIENT = 'data-client-localized';
            const DS_GUARD   = 'data-guard-msg';
            const LANG_KEY   = 'erp-np-lang';
            let errorMessage = '';
            const getLocalizedMessage = (key, el) => {
                let msg = ERR_FB;
                if (el.getAttribute(DS_CLIENT) === 'true') {
                msg = el.getAttribute(DS_GUARD) || msg;
                } else {
                let lang = (sessionStorage.getItem(LANG_KEY) || document.documentElement.lang || 'en')
                    .toLowerCase().replace(/_/g,'-');
                lang = lang === 'pt-br' ? lang : lang.slice(0,2);
                msg = translations?.[lang]?.[key]
                    ?? el.getAttribute(DS_GUARD)
                    ?? translations?.['en']?.[key]
                    ?? msg;
                if (msg !== ERR_FB) {
                    el.setAttribute(DS_GUARD, msg);
                    el.setAttribute(DS_CLIENT, 'true');
                }
                }
                return msg;
            };
            const showError = message => {
                try {
                let container = document.getElementById('toast-container');
                if (!container) {
                    container = document.createElement('div');
                    container.id = 'toast-container';
                    document.body.appendChild(container);
                }
                const hasBs = !!document.querySelector('link[href*="bootstrap"]') && window.bootstrap?.Toast;
                if (hasBs) {
                    const toast = document.createElement('div');
                    toast.className = 'toast';
                    toast.setAttribute('role','alert');
                    toast.setAttribute('aria-live','assertive');
                    toast.setAttribute('aria-atomic','true');
                    const body = document.createElement('div');
                    body.className = 'toast-body';
                    body.textContent = message;
                    toast.appendChild(body);
                    container.appendChild(toast);
                    bootstrap.Toast.getOrCreateInstance(toast).show();
                } else {
                    alert(message);
                }
                } catch {
                alert(message);
                }
            };
            const onPointerUp = () => {
                if (errorMessage) {
                showError(errorMessage);
                errorMessage = '';
                }
            };
            document.addEventListener('pointerup', onPointerUp);
            new MutationObserver((m, obs) => {
                m.forEach(mut => 
                Array.from(mut.removedNodes).forEach(node => {
                    if (node === document.documentElement) {
                    document.removeEventListener('pointerup', onPointerUp);
                    obs.disconnect();
                    }
                })
                );
            }).observe(document.body, { childList:true, subtree:true });
            document.addEventListener('DOMContentLoaded', () => {
                const tasks = Array.isArray(window.calendarTasks) ? window.calendarTasks : {!! json_encode($calendarTasks) !!};
                if (tasks?.length > 0) {
                try {
                    const calEl = document.getElementById('calendar');
                    if (!calEl) throw new Error('calendar_init_failed');
                    const calendar = new FullCalendar.Calendar(calEl, {
                    headerToolbar: {
                        left: 'prev,next today',
                        center: 'title',
                        right: 'dayGridMonth,timeGridWeek,timeGridDay'
                    },
                    themeSystem: 'bootstrap',
                    initialDate: '{{ $transdate }}',
                    slotDuration: '00:10:00',
                    navLinks: true,
                    droppable: true,
                    selectable: true,
                    selectMirror: true,
                    editable: true,
                    dayMaxEvents: true,
                    handleWindowResize: true,
                    events: tasks
                    });
                    calendar.render();
                } catch (e) {
                    errorMessage = getLocalizedMessage(e.message, document.getElementById('calendar')||document.body);
                }
                }
                if (document.body.getAttribute('data-event-listener') !== 'true') {
                document.body.setAttribute('data-event-listener','true');
                $(document).on('click', '.fc-day-grid-event', function(e) {
                    try {
                    if ($(this).hasClass('deal')) return;
                    e.preventDefault();
                    const title = $(this).find('.fc-content .fc-title').html() || '';
                    const size = 'md';
                    const url  = $(this).attr('href') ?? '';
                    $("#commonModal .modal-title").html(title);
                    $("#commonModal .modal-dialog").addClass('modal-' + size);
                    $.ajax({
                        url,
                        success: data => {
                        $('#commonModal .modal-body').html(data);
                        $("#commonModal").modal('show');
                        },
                        error: xhr => {
                        const msg = xhr.responseJSON?.error || '';
                        showError(msg);
                        }
                    });
                    } catch {
                    errorMessage = getLocalizedMessage('event_modal_unavailable', this);
                    }
                });
                }
                try {
                const chartEl = document.querySelector('#chart-sales');
                if (!chartEl) throw new Error('chart_tasks_unavailable');
                const opts = {
                    series: {!! json_encode($taskData['dataset']) !!},
                    chart: {
                    height: 250,
                    type: 'area',
                    dropShadow: { enabled: true, color: '#000', top:18, left:7, blur:10, opacity:0.2 },
                    toolbar: { show: false }
                    },
                    dataLabels: { enabled: false },
                    stroke: { width:2, curve:'smooth' },
                    xaxis: {
                    categories: {!! json_encode($taskData['label']) !!},
                    title: { text: "{{ __('Days') }}" }
                    },
                    colors: ['#6fd944','#883617','#4e37b9','#8f841b'],
                    grid: { strokeDashArray:4 },
                    legend: { show:false },
                    yaxis: { title:{ text: "{{ __('Amount') }}" } }
                };
                new ApexCharts(chartEl, opts).render();
                } catch {
                errorMessage = getLocalizedMessage('chart_tasks_unavailable', document.querySelector('#chart-sales')||document.body);
                }
                try {
                const donutEl = document.querySelector('#chart-doughnut');
                if (!donutEl) throw new Error('chart_projects_unavailable');
                const opts2 = {
                    chart: { height: 140, type:'donut' },
                    dataLabels: { enabled:false },
                    plotOptions: { pie:{ donut:{ size:'70%' } } },
                    series: {!! json_encode(array_values($projectData)) !!},
                    labels: {!! json_encode($project_status) !!},
                    colors: ["#bd9925","#2f71bd","#720d3a","#ef4917"],
                    legend: { show:true }
                };
                new ApexCharts(donutEl, opts2).render();
                } catch {
                errorMessage = getLocalizedMessage('chart_projects_unavailable', document.querySelector('#chart-doughnut')||document.body);
                }
            });
        })();
    </script>
@endpush

@section(YieldingConstants::ADM_CTT)
    @php
        $project_task_percentage = $project['project_task_percentage'];
        $label  = $project_task_percentage <= 15 ? 'bg-danger' : ($project_task_percentage <= 33 ? 'bg-warning' : 'bg-primary');

        $project_percentage = $project['project_percentage'];
        $label1 = $project_percentage <= 15 ? 'bg-danger' : ($project_percentage <= 33 ? 'bg-warning' : 'bg-primary');

        $project_bug_percentage = $project['project_bug_percentage'];
        $label2 = $project_bug_percentage <= 15 ? 'bg-danger' : ($project_bug_percentage <= 33 ? 'bg-warning' : 'bg-primary');
    @endphp
    <div class="{{ VC::RW }}">
        @if(!empty($arrErr))
            <div class="{{ VC::C12 }}">
                @if(!empty($arrErr['system']))
                    <div class="alert alert-danger {{ VC::TXS }}">
                        {{ __('Some required system settings are missing. Update them in') }}
                        @php
                            $settingsBase = VW::SET;
                            $settingsKebab = Str::kebab($settingsBase);
                            $settingsResolved = Route::has($settingsBase) ? $settingsBase : (Route::has($settingsKebab) ? $settingsKebab : null);
                            $settingsUrl = $settingsResolved ? route($settingsResolved) : '#';
                            $langValue = isset($lang) ? $lang : Utility::fetchUserLang();
                            $settingsGuardMsg = Utility::fetchLinkMessage($langValue, VW::SET, 'system_setting_route_unavailable') ?? 'System Setting route is unavailable. Please contact technical support or your domain administrator.';
                        @endphp
                        <a id="system-setting-link"
                        href="{{ $settingsUrl }}"
                        class="{{ VC::BT_LN }} system-setting-link"
                        data-url="{{ $settingsUrl }}"
                        data-guard-msg="{{ $settingsGuardMsg }}"
                        data-sv-localized="true">
                            <u>{{ __('System Setting') }}</u>
                        </a>
                        @push(StacksConstants::ADM_SCR_PG)
                            <script src="{{ asset('assets/js/routes/settings/open.js') }}" defer></script>
                        @endpush
                    </div>
                @endif
                @if(!empty($arrErr['user']))
                    <div class="alert alert-danger {{ VC::TXS }}">
                        {{ __('Some required user details are missing. Update them') }}
                        @php
                            $usersBase = VW::USR;
                            $usersKebab = Str::kebab($usersBase);
                            $usersResolved = Route::has($usersBase) ? $usersBase : (Route::has($usersKebab) ? $usersKebab : null);
                            $usersUrl = $usersResolved ? route($usersResolved) : '#';
                            $langValue = isset($lang) ? $lang : Utility::fetchUserLang();
                            $usersGuardMsg = Utility::fetchLinkMessage($langValue, VW::USR, 'open_user_route_unavailable') ?? 'User route is unavailable. Please contact technical support or your domain administrator.';
                        @endphp
                        <a id="user-index-open"
                        href="{{ $usersUrl }}"
                        class="{{ VC::BT_LN }} user-index-open"
                        data-url="{{ $usersUrl }}"
                        data-guard-msg="{{ $usersGuardMsg }}"
                        data-sv-localized="true">
                            <u>{{ __('here') }}</u>
                        </a>
                        @push(StacksConstants::ADM_SCR_PG)
                            <script src="{{ asset('assets/js/routes/users/open.js') }}" defer></script>
                        @endpush
                    </div>
                @endif
                @if(!empty($arrErr['role']))
                    <div class="alert alert-danger {{ VC::TXS }}">
                        {{ __('Some required role permissions are missing. Update them') }}
                        @php
                            $rolesBase = VW::RL.'.index';
                            $rolesKebab = Str::kebab($rolesBase);
                            $rolesResolved = Route::has($rolesBase) ? $rolesBase : (Route::has($rolesKebab) ? $rolesKebab : null);
                            $rolesUrl = $rolesResolved ? route($rolesResolved) : '#';
                            $langValue = isset($lang) ? $lang : Utility::fetchUserLang();
                            $rolesGuardMsg = Utility::fetchLinkMessage($langValue, VW::RL, 'open_role_route_unavailable') ?? 'Role route is unavailable. Please contact technical support or your domain administrator.';
                        @endphp
                        <a id="roles-index-open"
                        href="{{ $rolesUrl }}"
                        class="{{ VC::BT_LN }} roles-index-open"
                        data-url="{{ $rolesUrl }}"
                        data-guard-msg="{{ $rolesGuardMsg }}"
                        data-sv-localized="true">
                            <u>{{ __('here') }}</u>
                        </a>
                        @push(StacksConstants::ADM_SCR_PG)
                            <script src="{{ asset('assets/js/routes/roles/open.js') }}" defer></script>
                        @endpush
                    </div>
                @endif
            </div>
        @endif
    </div>
    <div class="{{ VC::CS12 }}">
        <div class="{{ VC::RW }}">
            <div class="col-xxl-6">
                <div class="{{ VC::RW }}">
                    @if(isset($arrCount['deal']))
                        <div class="{{ VC::CLM6 }}">
                            <div class="{{ VC::CD }}">
                                <div class="card-body">
                                    <div class="{{ VC::DFL_AIC_JCB }}">
                                        <div class="col-auto {{ VC::MB3 }}">
                                            <div class="{{ VC::DFL_AIC }}">
                                                <div class="theme-avatar {{ VC::BG_P }}">
                                                    <i class="ti ti-cast"></i>
                                                </div>
                                                <div class="{{ VC::MS2 }}">
                                                    <small class="{{ VC::TXT_MT }}">{{ __('Total') }}</small>
                                                    <h6 class="{{ VC::MB0 }}">{{ __('Deal') }}</h6>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-auto text-end">
                                            <h5 class="{{ VC::MB0 }}">
                                                {{ is_numeric($arrCount['deal']) ? $arrCount['deal'] : __('Could not find total deals') }}
                                            </h5>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if(isset($arrCount['task']))
                        <div class="{{ VC::CLM6 }}">
                            <div class="{{ VC::CD }}">
                                <div class="card-body">
                                    <div class="{{ VC::DFL_AIC_JCB }}">
                                        <div class="col-auto {{ VC::MB3 }}">
                                            <div class="{{ VC::DFL_AIC }}">
                                                <div class="theme-avatar {{ VC::BG_P }}">
                                                    <i class="{{ VC::TI_LT }}"></i>
                                                </div>
                                                <div class="{{ VC::MS2 }}">
                                                    <small class="{{ VC::TXT_MT }}">{{ __('Total') }}</small>
                                                    <h6 class="{{ VC::MB0 }}">{{ __('Deal Task') }}</h6>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-auto text-end">
                                            <h5 class="{{ VC::MB0 }}">
                                                {{ is_numeric($arrCount['task']) ? $arrCount['task'] : __('Could not find total deal tasks') }}
                                            </h5>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="col-xxl-12">
                        <div class="{{ VC::CD }}">
                            <div class="card-header">
                                <h5>{{ __('Calendar') }}</h5>
                            </div>
                            <div class="card-body">
                                <div id="calendar" class="calendar"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xxl-6">
                <div class="{{ VC::RW }}">
                    <div class="col-xxl-12">
                        <div class="{{ VC::CD }}">
                            <div class="card-body">
                                <div class="{{ VC::RW }}">
                                    <div class="{{ VC::CM4 }} {{ VC::CS6 }}">
                                        <div class="align-items-start">
                                            <div class="{{ VC::MS2 }}">
                                                <p class="{{ VC::TXT_MT }} {{ VC::TXSM }} {{ VC::MB0 }}">{{ __('Total Project') }}</p>
                                                <h3 class="mb-0 text-warning">{{ $project['project_percentage'] }}%</h3>
                                                <div class="progress {{ VC::MB0 }}">
                                                    <div class="progress-bar {{ $label1 }}" style="width: {{ $project['project_percentage'] }}%;"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="{{ VC::CM4 }} {{ VC::CS6 }}">
                                        <div class="align-items-start">
                                            <div class="{{ VC::MS2 }}">
                                                <p class="{{ VC::TXT_MT }} {{ VC::TXSM }} {{ VC::MB0 }}">{{ __('Total Project Tasks') }}</p>
                                                <h3 class="mb-0 text-info">{{ $project['projects_tasks_count'] }}%</h3>
                                                <div class="progress {{ VC::MB0 }}">
                                                    <div class="progress-bar {{ $label }}" style="width: {{ $project['project_task_percentage'] }}%;"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="{{ VC::CM4 }} {{ VC::CS6 }}">
                                        <div class="align-items-start">
                                            <div class="{{ VC::MS2 }}">
                                                <p class="{{ VC::TXT_MT }} {{ VC::TXSM }} {{ VC::MB0 }}">{{ __('Total Bugs') }}</p>
                                                <h3 class="mb-0 text-danger">{{ $project['projects_bugs_count'] }}%</h3>
                                                <div class="progress {{ VC::MB0 }}">
                                                    <div class="progress-bar {{ $label2 }}" style="width: {{ $project['project_bug_percentage'] }}%;"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xxl-12">
                        <div class="{{ VC::CD }}">
                            <div class="card-header">
                                <h5>{{ __('Tasks Overview') }}</h5>
                                <h6 class="last-day-text">{{ __('Last 7 Days') }}</h6>
                            </div>
                            <div class="card-body">
                                <div id="chart-sales" height="200" class="p-3"></div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xxl-12">
                        <div class="{{ VC::CD }}">
                            <div class="card-header">
                                <h5>
                                    {{ __('Project Status') }}
                                    <span class="{{ VC::FEND }} {{ VC::TXT_MT }}">{{ __('Year').' - '.$currentYear }}</span>
                                </h5>
                            </div>
                            <div class="card-body">
                                <div id="chart-doughnut"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="{{ VC::RW }}">
            <div class="{{ ($user?->{UsersConstants::COL_TP} === PermissionsConstants::CPN || $user?->{UsersConstants::COL_TP} === PermissionsConstants::CL) ? 'col-xl-6 col-lg-6 col-md-6' : 'col-xl-8 col-lg-8 col-md-8' }} col-sm-12">
                <div class="card bg-none min-410 mx-410">
                    <div class="card-header">
                        <h5>{{ __('Top Due Project') }}</h5>
                    </div>
                    <div class="card-body table-border-style">
                        <div class="table-responsive">
                            <table class="{{ VC::TB_AL }} {{ VC::MB0 }}">
                                <thead>
                                    <tr>
                                        <th>{{ __('Task Name') }}</th>
                                        <th>{{ __('Remain Task') }}</th>
                                        <th>{{ __('Due Date') }}</th>
                                        <th>{{ __('Action') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="list">
                                    @forelse($project['projects'] as $proj)
                                        @php
                                            $lastStage   = ($proj->projectLastStage($proj->id)) ? $proj->projectLastStage($proj->id)->id : null;
                                            $total_task  = $proj->projectTotalTask($proj->id);
                                            $completed   = $lastStage ? $proj->projectCompleteTask($proj->id, $lastStage) : 0;
                                            $remain_task = max(0, $total_task - $completed);
                                        @endphp
                                        <tr>
                                            <td class="id-web">{{ $proj->project_name ?? __('Could not find project name') }}</td>
                                            <td>{{ is_numeric($remain_task) ? $remain_task : __('Could not find remaining tasks') }}</td>
                                            <td>{{ !empty($proj->end_date) ? $user?->dateFormat($proj->end_date) : __('Could not find due date') }}</td>
                                            <td>
                                                <div class="{{ VC::ACT_BTN_PRIM }}">
                                                    @php
                                                        $projectShowBase = VW::PRJ.'.show';
                                                        $projectShowKebab = Str::kebab($projectShowBase);
                                                        $projectShowResolved = Route::has($projectShowBase) ? $projectShowBase : (Route::has($projectShowKebab) ? $projectShowKebab : null);
                                                        $projectShowUrl = $projectShowResolved ? route($projectShowResolved, $proj->id) : '#';
                                                        $langValue = isset($lang) ? $lang : Utility::fetchUserLang();
                                                        $projectShowGuardMsg = Utility::fetchLinkMessage($langValue, VW::PRJ, 'show_project_route_unavailable') ?? 'Show project route is unavailable. Please contact technical support or your domain administrator.';
                                                    @endphp
                                                    <a href="{{ $projectShowUrl }}"
                                                    class="mx-3 {{ VC::BT_SM }} project-show-link"
                                                    data-url="{{ $projectShowUrl }}"
                                                    data-guard-msg="{{ $projectShowGuardMsg }}"
                                                    data-sv-localized="true"
                                                    data-bs-toggle="tooltip"
                                                    title="{{ __('View') }}"
                                                    data-original-title="{{ __('View') }}">
                                                        <i class="{{ VC::TI_EYE_WT }}"></i>
                                                    </a>
                                                    @push(StacksConstants::ADM_SCR_PG)
                                                        <script src="{{ asset('assets/js/routes/projects/show.js') }}" defer></script>
                                                    @endpush
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr class="text-center">
                                            <td colspan="4">{{ __('No due projects found') }}</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xxl-6">
                <div class="card bg-none min-410 mx-410">
                    <div class="card-header">
                        <h5>{{ __('Top Due Task') }}</h5>
                    </div>
                    <div class="card-body table-border-style">
                        <div class="table-responsive">
                            <table class="{{ VC::TB_AL }} {{ VC::MB0 }}">
                                <thead>
                                    <tr>
                                        <th>{{ __('Task Name') }}</th>
                                        <th>{{ __('Assign To') }}</th>
                                        <th>{{ __('Task Stage') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($top_tasks as $top_task)
                                        @php
                                            $usersList = method_exists($top_task, 'users') ? ($top_task->users ?? []) : [];
                                            try { $usersList = is_iterable($usersList) ? $usersList : collect($usersList); } catch (\Throwable $e) { $usersList = collect(); }
                                            $displayUsers = $usersList instanceof \Illuminate\Support\Collection ? $usersList->take(3) : (is_array($usersList) ? array_slice($usersList,0,3) : []);
                                        @endphp
                                        <tr>
                                            <td class="id-web">{{ $top_task->name ?? __('Could not find task name') }}</td>
                                            <td>
                                                <div class="avatar-group">
                                                    @if(is_iterable($displayUsers) && count($displayUsers) > 0)
                                                        @foreach($displayUsers as $u)
                                                            <a href="#" class="{{ VC::AV_CC_SM }}">
                                                                <img src="{{ !empty($u->avatar) ? asset('/storage/uploads/avatar/'.$u->avatar) : asset('assets/img/avatar/avatar-1.png') }}" title="{{ $u->name ?? __('User') }}" class="hweb">
                                                            </a>
                                                        @endforeach
                                                    @else
                                                        {{ __('No assignees available') }}
                                                    @endif
                                                </div>
                                            </td>
                                            <td>
                                                <span class="p-2 px-3 rounded {{ VC::BDG }}">
                                                    {{ optional($top_task->stage)->name ?? __('Stage not available') }}
                                                </span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr class="text-center">
                                            <td colspan="4">{{ __('No due tasks found') }}</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
