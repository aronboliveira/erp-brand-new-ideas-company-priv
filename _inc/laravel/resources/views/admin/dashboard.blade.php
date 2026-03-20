@php
    try {
$user = Auth::user();
        $lang = Utility::fetchUserLang(user:$user);
    } catch (\Throwable $e) {
        \Log::error('admin/dashboard — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    }
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@section(YieldingConstants::ADM_PG_TTL)
    {{__('Project Dashboard')}}
@endsection

@section(YieldingConstants::ADM_ACT_BTN)
    @if($user[UsersConstants::COL_TP] == PermissionsConstants::ADM ||
        $user[UsersConstants::COL_TP] == PermissionsConstants::SA)
        <div class="bg-neutral rounded-pill d-inline-block">
            <div class="input-group input-group-sm input-group-merge input-group-flush">
                <div class="input-group-prepend">
                    <span class="{{ ViewClassNamesConstants::TXTS_TRP }}"><i class="{{ ViewClassNamesConstants::TI_SRC }}"></i></span>
                </div>
                <input type="text" id="keyword" class="{{ VC::FM_CT }} form-control-flush" placeholder="{{__('Search by Name or skill')}}">
            </div>
        </div>
    @endif
@endsection

@push('theme-script')
    @if($user[UsersConstants::COL_TP] != PermissionsConstants::ADM ||
        $user[UsersConstants::COL_TP] != PermissionsConstants::SA)
        <script src="{{ asset('assets/libs/dragula/dist/dragula.min.js') }}"></script>
        <script src="{{ asset('assets/libs/apexcharts/dist/apexcharts.min.js') }}"></script>
    @endif
@endpush

@section(YieldingConstants::ADM_CTT)
    @if($user[UsersConstants::COL_TP] == PermissionsConstants::ADM ||
        $user[UsersConstants::COL_TP] == PermissionsConstants::SA)
        <div class="row" id="dashboard_view"></div>
    @else
        <div class="row">
            @php
                try {
                    $stats = [
                        [
                            'label'      => __('Total Projects'),
                            'total'      => $home_data['total_project']['total'],
                            'percentage' => $home_data['total_project']['percentage'],
                            'color'      => 'primary',
                        ],
                        [
                            'label'      => __('Total Tasks'),
                            'total'      => $home_data['total_task']['total'],
                            'percentage' => $home_data['total_task']['percentage'],
                            'color'      => 'info',
                        ],
                        [
                            'label'      => __('Total Expense'),
                            'total'      => $home_data['total_expense']['total'],
                            'percentage' => $home_data['total_expense']['percentage'],
                            'color'      => 'warning',
                        ],
                        [
                            'label'      => __('Total Users'),
                            'total'      => $home_data['total_user'],
                            'percentage' => null,  /* no circle */
                            'color'      => null,
                        ],
                    ];
                } catch (\Throwable $e) {
                    \Log::error('admin/dashboard — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                }
@endphp
            @foreach($stats as $stat)
                <div class="{{ VC::CXL3 }} {{ VC::CM6 }}">
                    <div class="card card-stats">
                        <div class="{{ VC::CD_BD }}">
                            <div class="{{ VC::R_ALC }}">
                                <div class="col">
                                    <h6 class="{{ VC::TXT_MT }} {{ VC::MB1 }}">{{ $stat['label'] }}</h6>
                                    <span class="h3 font-weight-bold {{ VC::MB0 }}">{{ $stat['total'] }}</span>
                                </div>
                                @if($stat['percentage'] !== null)
                                    <div class="{{ VC::C_AT }}">
                                        <div
                                            class="progress-circle progress-sm"
                                            data-progress="{{ $stat['percentage'] }}"
                                            data-text="{{ $stat['percentage'] }}%"
                                            data-color="{{ $stat['color'] }}"
                                        ></div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        <div class="row">
            <div class="{{ VC::CXL12 }} {{ VC::CM12 }}">
                <div class="{{ ViewClassNamesConstants::CD_FL }}">
                    <div class="{{ VC::CD_HD }}">
                        <h6 class="{{ VC::MB0 }}">{{__('Tasks Overview')}}</h6>
                        <small class="{{ VC::TXT_MT }}">{{__('Total Completed task in last 7 days')}}</small>
                    </div>
                    <div class="{{ VC::CD_BD }}">
                        <div id="task_overview" data-color="primary" data-height="280"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="{{ VC::CXS12 }} {{ VC::CS12 }} {{ VC::CM4 }} {{ VC::CL4 }}">
                <div class="{{ ViewClassNamesConstants::CD_FL }}">
                    <div class="{{ VC::CD_HD }}">
                        <h6 class="{{ VC::MB0 }}">{{__('Project Status')}}</h6>
                    </div>
                    <div class="{{ VC::CD_BD }}">
                        @foreach($home_data['project_status'] as $status => $val)
                            <div class="{{ ViewClassNamesConstants::R_ALC_M4 }}">
                                <div class="{{ VC::C_AT }}">
                                    <div class="progress-circle progress-sm" data-progress="{{$val['percentage']}}" data-color="{{ Project::$status_color[$status] }}"></div>
                                </div>
                                <div class="col">
                                    <span class="{{ VC::DBL }} h6 {{ VC::MB0 }}">{{__(Project::$project_status[$status])}}</span>
                                </div>
                            </div>
                        @endforeach
                        <div class="{{ VC::DFL }} my-1 {{ VC::TXCT }}">
                            @foreach($home_data['project_status'] as $status => $val)
                                <div class="col">
                            <span class="badge badge-dot badge-lg h6">
                                <i class="bg-{{ Project::$status_color[$status] }}"></i>{{ $val['total'] }}
                            </span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
            <div class="{{ VC::CXS12 }} {{ VC::CS12 }} {{ VC::CM6 }} {{ VC::CL8 }}">
                <div class="card">
                    <div class="{{ VC::CD_HD }}">
                        <div class="{{ ViewClassNamesConstants::DFL_AIC_JCB }}">
                            <div>
                                <h6 class="{{ VC::MB0 }}">{{__('Top Due Projects')}}</h6>
                            </div>
                        </div>
                    </div>
                    <div class="scrollbar-inner">
                        <div class="min-h-430 mh-430">
                            <div class="{{ ViewClassNamesConstants::LG_FLSH }}">
                                @if($home_data['due_project']->count() > 0)
                                @foreach($home_data['due_project'] as $due_project)
                                    @php
                                        try {
                                            $showRoute = Route::has(ViewsConstants::PRJ.'.show')
                                                ? route(ViewsConstants::PRJ.'.show', $due_project)
                                                : '#';
                                            $linkId = 'due-project-link-'.$due_project->id;
                                            $message = Utility::fetchLinkMessage(
                                                $lang,
                                                ViewsConstants::PRJ,
                                                'show_project_route_unavailable'
                                            ) ?? 'Project view route is unavailable. Please contact technical support or your domain administrator.';
                                        } catch (\Throwable $e) {
                                            \Log::error('admin/dashboard — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                        }
@endphp
                                    <a
                                        id="{{ $linkId }}"
                                        href="#"
                                        data-sv-localized="true"
                                        data-url="{{ $showRoute }}"
                                        class="{{ ViewClassNamesConstants::LG_IT_ACT }}"
                                    >
                                        <div class="{{ ViewClassNamesConstants::DFL_AIC_JCB }}">
                                            <div>
                                                <img {{ $due_project->img_image }} class="{{ ViewClassNamesConstants::AV_CC }}" />
                                            </div>
                                            <div class="flex-fill pl-3 {{ VC::TX_LM }}">
                                                <div class="row">
                                                    <div class="{{ VC::C9 }}">
                                                        <h6 class="{{ ViewClassNamesConstants::PG_SM_BL }}">
                                                            {{ $due_project->name }}
                                                        </h6>
                                                    </div>
                                                    <div class="{{ VC::C3 }} {{ VC::TX_END }}">
                                                        <span class="{{ ViewClassNamesConstants::BDG_XS }} {{ ViewClassNamesConstants::BDG }}-{{ ($user->checkProject($due_project->id) === 'Owner') ? ProjectsConstants::STT_SCS : ProjectsConstants::STT_WRN }}">
                                                            {{ $user?->checkProject($due_project->id) }}
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="{{ ViewClassNamesConstants::PG_XS }}">
                                                    <div
                                                        class="progress-bar bg-{{ $due_project->projectProgress()['color'] }}"
                                                        role="progressbar"
                                                        style="width: {{ $due_project->projectProgress()['percentage'] }}%;"
                                                        aria-valuenow="{{ $due_project->projectProgress()['percentage'] }}"
                                                        aria-valuemin="0"
                                                        aria-valuemax="100"
                                                    ></div>
                                                </div>
                                                <div class="{{ ViewClassNamesConstants::DFL_SPC_TXT }}">
                                                    <div>
                                                        <span class="font-weight-bold text-{{ \App\Models\Project::$status_color[$due_project->status] }}">
                                                            {{ \App\Models\Project::$project_status[$due_project->status] }}
                                                        </span>
                                                    </div>
                                                    <div>
                                                        {{ $due_project->countTask($user?->id) }}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </a>
                                    @push(StacksConstants::ADM_SCR_PG)
                                        <script defer>
                                            (() => {
                                                const dataSVLocalized = 'data-sv-localized';
                                                const dataClientLocalized = 'data-client-localized';
                                                const dataGuardMsg = 'data-guard-msg';
                                                const listenerAttr = 'data-due-project-listener-active';
                                                const el = document.getElementById('{{ $linkId }}');
                                                if (!el || el.getAttribute(listenerAttr) === 'true') return;
                                                el.setAttribute(listenerAttr, 'true');
                                                el.addEventListener('click', event => {
                                                    try {
                                                        const url = el.getAttribute('data-url');
                                                        const href = el.href.replace(window.location.origin, '').replace(window.location.pathname, '');
                                                        if ((!url || url === '#') && (!href || href === '#')) {
                                                            event.preventDefault();
                                                            let msg = el.getAttribute(dataGuardMsg) ?? "{{ $message }}";
                                                            if (el.getAttribute(dataSVLocalized) !== 'true' && el.getAttribute(dataClientLocalized) !== 'true') {
                                                                let langCode = (window.sessionStorage.getItem('erp-np-lang') || document.documentElement.lang || 'en')
                                                                    .toLowerCase()
                                                                    .replace(/_/g, '-');
                                                                langCode = langCode === 'pt-br' ? langCode : langCode.slice(0, 2);
                                                                msg = window.translations?.[langCode]?.['show_project_unavailable'] || msg;
                                                                if (msg !== '') {
                                                                    el.setAttribute(dataGuardMsg, msg);
                                                                    el.setAttribute(dataClientLocalized, 'true');
                                                                }
                                                            }
                                                            const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                                                            const containerId = 'toast-container';
                                                            let container = document.getElementById(containerId);
                                                            if (!container) {
                                                                container = document.createElement('div');
                                                                container.id = containerId;
                                                                document.body.appendChild(container);
                                                            }
                                                            if (bootstrapLink && window.bootstrap) {
                                                                const toastEl = document.createElement('div');
                                                                toastEl.className = 'toast';
                                                                toastEl.setAttribute('role', 'alert');
                                                                toastEl.setAttribute('aria-live', 'assertive');
                                                                toastEl.setAttribute('aria-atomic', 'true');
                                                                const body = document.createElement('div');
                                                                body.className = 'toast-body';
                                                                body.textContent = msg;
                                                                toastEl.appendChild(body);
                                                                container.appendChild(toastEl);
                                                                bootstrap.Toast.getOrCreateInstance(toastEl).show();
                                                            } else {
                                                                if (msg) console.warn("[Dashboard]", msg);
                                                            }
                                                            el.setAttribute('data-failed-route', 'true');
                                                        }
                                                    } catch (error) {}
                                                });
                                                const observer = new MutationObserver(() => {
                                                    if (!document.body.contains(el)) {
                                                        observer.disconnect();
                                                        el.removeEventListener('click', () => {});
                                                    }
                                                });
                                                observer.observe(document.body, { childList: true, subtree: true });
                                            })();
                                        </script>
                                    @endpush
                                @endforeach
                                @else
                                    <div class="py-5">
                                        <h6 class="{{ VC::TXCT }} {{ VC::MB0 }}">{{__('No Due Projects Found.')}}</h6>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="{{ VC::C12 }}">
                <div class="{{ ViewClassNamesConstants::CD_FL }}">
                    <div class="{{ VC::CD_HD }}">
                        <h6 class="{{ VC::MB0 }}">{{__('Timesheet Logged Hours')}}</h6>
                        <small class="{{ VC::TXT_MT }}">{{__('Last 7 days')}}</small>
                    </div>
                    <div class="{{ VC::CD_BD }}">
                        <div id="timesheet_logged" data-color="primary" data-height="410"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-xl-8 {{ VC::CM8 }}">
                <div class="{{ ViewClassNamesConstants::CD_FL }}">
                    <div class="{{ VC::CD_HD }} border-0">
                        <h6 class="{{ VC::MB0 }}">{{__('Top Due Tasks')}}</h6>
                    </div>
                    <div class="{{ VC::TB_RSP }}">
                        <table class="{{ ViewClassNamesConstants::TB_AL }}">
                            <thead>
                            <tr>
                                @php
                                    try {
                                        $columns = [
                                            ['key' => 'name',       'label' => __('Tasks')],
                                            ['key' => 'budget',     'label' => __('Project')],
                                            ['key' => 'status',     'label' => __('Stage')],
                                            ['key' => 'completion', 'label' => __('Completion')],
                                        ];
                                    } catch (\Throwable $e) {
                                        \Log::error('admin/dashboard — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                    }
@endphp
                                @foreach($columns as $col)
                                    <th scope="col" class="sort" data-sort="{{ $col['key'] }}">
                                        {{ $col['label'] }}
                                    </th>
                                @endforeach
                            </tr>
                            </thead>
                            <tbody class="list">
                                @foreach($home_data['due_tasks'] as $due_task)
                                    @php
                                        try {
                                            $chainRoute = Route::has(ViewsConstants::PRJ_TSK_C.'.index')
                                                ? route(ViewsConstants::PRJ_TSK_C.'.index', $due_task->project->id)
                                                : '#';
                                            $linkId = 'due-task-link-'.$due_task->id;
                                            $message = Utility::fetchLinkMessage(
                                                $lang,
                                                ViewsConstants::PRJ,
                                                'project_task_route_unavailable'
                                            ) ?? 'Project task route is unavailable. Please contact technical support or your domain administrator.';
                                        } catch (\Throwable $e) {
                                            \Log::error('admin/dashboard — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                        }
@endphp
                                    <tr>
                                        <th scope="row">
                                            <div class="{{ ViewClassNamesConstants::MD_AIC }}">
                                                <div class="media-body ml-4">
                                                    <a
                                                        id="{{ $linkId }}"
                                                        href="{{ $chainRoute }}"
                                                        data-sv-localized="true"
                                                        data-url="{{ $chainRoute }}"
                                                        class="{{ ViewClassNamesConstants::NM_HD_SM }}"
                                                    >
                                                        {{ $due_task->name }}
                                                    </a>
                                                </div>
                                            </div>
                                        </th>
                                        <td class="budget">{{ $due_task->project->name }}</td>
                                        <td>
                                            <span class="badge badge-dot mr-4">
                                                <i class="bg-{{ ProjectTask::$priority_color[$due_task->priority] }}"></i>
                                                <span class="status">{{ ProjectTask::$priority[$due_task->priority] }}</span>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="{{ ViewClassNamesConstants::DFL_AIC }}">
                                                <span class="completion {{ VC::MR2 }}">{{ $due_task->taskProgress($due_task)['percentage'] }}</span>
                                            </div>
                                        </td>
                                    </tr>
                                    @push(StacksConstants::ADM_SCR_PG)
                                        <script defer>
                                            (() => {
                                                const dataSVLocalized = 'data-sv-localized';
                                                const dataClientLocalized = 'data-client-localized';
                                                const dataGuardMsg = 'data-guard-msg';
                                                const listenerAttr = 'data-due-task-listener-active';
                                                const el = document.getElementById('{{ $linkId }}');
                                                if (!el || el.getAttribute(listenerAttr) === 'true') return;
                                                el.setAttribute(listenerAttr, 'true');
                                                el.addEventListener('click', event => {
                                                    try {
                                                        const url = el.getAttribute('data-url');
                                                        const href = el.href.replace(window.location.origin, '').replace(window.location.pathname, '');
                                                        if ((!url || url === '#') && (!href || href === '#')) {
                                                            event.preventDefault();
                                                            let msg = el.getAttribute(dataGuardMsg) ?? '{{ $message }}';
                                                            if (
                                                                el.getAttribute(dataSVLocalized) !== 'true' &&
                                                                el.getAttribute(dataClientLocalized) !== 'true'
                                                            ) {
                                                                let langCode = (
                                                                    window.sessionStorage.getItem('erp-np-lang') ||
                                                                    document.documentElement.lang ||
                                                                    'en'
                                                                )
                                                                    .toLowerCase()
                                                                    .replace(/_/g, '-');
                                                                langCode = langCode === 'pt-br' ? langCode : langCode.slice(0, 2);
                                                                msg =
                                                                    window.translations?.[langCode]?.['index_project_task_chain_unavailable'] ||
                                                                    el.getAttribute(dataGuardMsg) ||
                                                                    window.translations?.['en']?.['index_project_task_chain_unavailable'] ||
                                                                    '';
                                                                if (msg !== '') {
                                                                    el.setAttribute(dataGuardMsg, msg);
                                                                    el.setAttribute(dataClientLocalized, 'true');
                                                                }
                                                            }
                                                            const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                                                            const containerId = 'toast-container';
                                                            let container = document.getElementById(containerId);
                                                            if (!container) {
                                                                container = document.createElement('div');
                                                                container.id = containerId;
                                                                document.body.appendChild(container);
                                                            }
                                                            if (bootstrapLink && window.bootstrap) {
                                                                const toastEl = document.createElement('div');
                                                                toastEl.className = 'toast';
                                                                toastEl.setAttribute('role', 'alert');
                                                                toastEl.setAttribute('aria-live', 'assertive');
                                                                toastEl.setAttribute('aria-atomic', 'true');
                                                                const body = document.createElement('div');
                                                                body.className = 'toast-body';
                                                                body.textContent = msg;
                                                                toastEl.appendChild(body);
                                                                container.appendChild(toastEl);
                                                                bootstrap.Toast.getOrCreateInstance(toastEl).show();
                                                            } else {
                                                                if (msg) console.warn("[Dashboard]", msg);
                                                            }
                                                            el.setAttribute('data-failed-route', 'true');
                                                        }
                                                    } catch (error) {}
                                                });
                                                const observer = new MutationObserver(() => {
                                                    if (!document.body.contains(el)) {
                                                        observer.disconnect();
                                                        el.removeEventListener('click', () => {});
                                                    }
                                                });
                                                observer.observe(document.body, { childList: true, subtree: true });
                                            })();
                                        </script>
                                    @endpush
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="{{ VC::CXL4 }} {{ VC::CM4 }}">
                <div class="card">
                    <div class="{{ VC::CD_HD }}">
                        <div class="{{ ViewClassNamesConstants::DFL_AIC_JCB }}">
                            <div>
                                <h6 class="{{ VC::MB0 }}">{{__('To do list')}}</h6>
                            </div>
                            <div class="{{ VC::TX_END }}">
                                <div class="actions">
                                    <div data-toggle="collapse" data-target="#form-todo">
                                        <a class="action-item">
                                            <i class="{{ ViewClassNamesConstants::TI_PLS }}"></i>
                                            <span class="d-none d-sm-inline-block">{{__('Add')}}</span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="scrollbar-inner">
                        <div class="mh-350 min-h-350">
                            <div class="card-wrapper p-3">
                                @php
                                    try {
                                        $storeRoute = Route::has(ViewsConstants::TD.'.store')
                                            ? route(ViewsConstants::TD.'.store')
                                            : '#';
                                        $message = Utility::fetchLinkMessage(
                                            $lang,
                                            ViewsConstants::PRJ,
                                            'store_todo_route_unavailable'
                                        ) ?? 'Todo creation route is unavailable. Please contact technical support or your domain administrator.';
                                    } catch (\Throwable $e) {
                                        \Log::error('admin/dashboard — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                    }
@endphp
                                <form
                                    method="post"
                                    id="form-todo"
                                    class="collapse pb-2"
                                    data-action="{{ $storeRoute }}"
                                    data-url="{{ $storeRoute }}"
                                    action="{{ $storeRoute }}"
                                    data-sv-localized="true"
                                    data-guard-msg="{{ base64_encode($message) }}"
                                >
                                    <div class="{{ ViewClassNamesConstants::CD_NSD }}">
                                        <div class="{{ ViewClassNamesConstants::R_ALC_SMPD }}">
                                            <div class="{{ VC::C9 }}">
                                                <input
                                                    type="text"
                                                    name="title"
                                                    required
                                                    class="{{ VC::FM_CT }}"
                                                    placeholder="{{ __('Todo Title') }}"
                                                />
                                            </div>
                                            <div class="{{ ViewClassNamesConstants::CL_MT_VC }}">
                                                <button
                                                    class="{{ ViewClassNamesConstants::BT_XS_PM }}"
                                                    type="submit"
                                                    id="todo_submit"
                                                >
                                                    <i class="{{ ViewClassNamesConstants::TI_PLS }}"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                                @push(StacksConstants::ADM_SCR_PG)
                                    <script defer>
                                        (() => {
                                            const errFb = "";
                                            const dataSVLocalized = "data-sv-localized";
                                            const dataClientLocalized = "data-client-localized";
                                            const dataGuardMsg = "data-guard-msg";
                                            const listenerAttr = "data-submit-listener-active";
                                            const msgKey = "store_todo_unavailable";
                                            const getMessage = (el, key) => {
                                                let msg = errFb;
                                                if (
                                                    el.getAttribute(dataSVLocalized) === "true" ||
                                                    el.getAttribute(dataClientLocalized) === "true"
                                                ) {
                                                    msg = el.getAttribute(dataGuardMsg) ?? errFb;
                                                } else {
                                                    let langCode = (
                                                        window.sessionStorage.getItem("erp-np-lang") ||
                                                        document.documentElement.lang ||
                                                        "en"
                                                    )
                                                        .toLowerCase()
                                                        .replace(/_/g, "-");
                                                    langCode = langCode === "pt-br" ? langCode : langCode.slice(0, 2);
                                                    msg =
                                                        window.translations?.[langCode]?.[key] ||
                                                        el.getAttribute(dataGuardMsg) ||
                                                        window.translations?.["en"]?.[key] ||
                                                        errFb;
                                                    if (msg !== errFb) {
                                                        el.setAttribute(dataGuardMsg, msg);
                                                        el.setAttribute(dataClientLocalized, "true");
                                                    }
                                                }
                                                return msg;
                                            };
                                            const form = document.getElementById("form-todo");
                                            if (!form || form.getAttribute(listenerAttr) === "true") return;
                                            form.setAttribute(listenerAttr, "true");
                                            const onSubmit = event => {
                                                try {
                                                    const url = form.getAttribute("data-action");
                                                    const href = form.action;
                                                    if ((!url || url === "#") && (!href || href === "#")) {
                                                        event.preventDefault();
                                                        const message = getMessage(form, msgKey);
                                                        const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                                                        const containerId = "toast-container";
                                                        let container = document.getElementById(containerId);
                                                        if (!container) {
                                                            container = document.createElement("div");
                                                            container.id = containerId;
                                                            document.body.appendChild(container);
                                                        }
                                                        if (bootstrapLink && window.bootstrap) {
                                                            const toastEl = document.createElement("div");
                                                            toastEl.className = "toast";
                                                            toastEl.setAttribute("role", "alert");
                                                            toastEl.setAttribute("aria-live", "assertive");
                                                            toastEl.setAttribute("aria-atomic", "true");
                                                            const body = document.createElement("div");
                                                            body.className = "toast-body";
                                                            body.textContent = message;
                                                            toastEl.appendChild(body);
                                                            container.appendChild(toastEl);
                                                            bootstrap.Toast.getOrCreateInstance(toastEl).show();
                                                        } else {
                                                            if (message) console.warn("[Dashboard]", message);
                                                        }
                                                    }
                                                } catch (error) {}
                                            };
                                            form.addEventListener("submit", onSubmit);
                                            const observer = new MutationObserver(() => {
                                                if (!document.body.contains(form)) {
                                                    observer.disconnect();
                                                    form.removeEventListener("submit", onSubmit);
                                                }
                                            });
                                            observer.observe(document.body, { childList: true, subtree: true });
                                        })();
                                    </script>
                                @endpush
                                <div id="todolist">
                                    @if($user?->todo->count() > 0)
                                        @foreach($user?->todo as $todo)
                                            @php
                                                try {
                                                    $updateRoute = Route::has(ViewsConstants::TD.'.update')
                                                        ? route(ViewsConstants::TD.'.update', $todo->id)
                                                        : '#';
                                                    $destroyRoute = Route::has(ViewsConstants::TD.'.destroy')
                                                        ? route(ViewsConstants::TD.'.destroy', $todo->id)
                                                        : '#';
                                                    $updateMessage = Utility::fetchLinkMessage(
                                                        $lang,
                                                        ViewsConstants::PRJ,
                                                        'update_todo_route_unavailable'
                                                    ) ?? 'Todo update route is unavailable. Please contact technical support or your domain administrator.';
                                                    $destroyMessage = Utility::fetchLinkMessage(
                                                        $lang,
                                                        ViewsConstants::PRJ,
                                                        'destroy_todo_route_unavailable'
                                                    ) ?? 'Todo delete route is unavailable. Please contact technical support or your domain administrator.';
                                                    $inputId = 'todo-item-' . $todo->id;
                                                    $btnId = 'destroy-todo-' . $todo->id;
                                                } catch (\Throwable $e) {
                                                    \Log::error('admin/dashboard — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                }
@endphp
                                            <div class="{{ ViewClassNamesConstants::CD_NSD }} todo-member mb-2">
                                                <div class="{{ ViewClassNamesConstants::R_ALC_SMPD }}">
                                                    <div class="col-10">
                                                        <div class="{{ ViewClassNamesConstants::CST_CT_CB }}">
                                                            <input
                                                                type="checkbox"
                                                                class="custom-control-input"
                                                                id="{{ $inputId }}"
                                                                data-url="{{ $updateRoute }}"
                                                                data-sv-localized="true"
                                                                data-guard-msg="{{ base64_encode($updateMessage) }}"
                                                                {{ ($todo[ProjectsConstants::COL_IS_CP] == 1) ? 'checked' : '' }}
                                                            />
                                                            <label
                                                                class="{{ ViewClassNamesConstants::CST_LB_SM }}"
                                                                for="{{ $inputId }}"
                                                            >
                                                                {{ $todo->title }}
                                                            </label>
                                                        </div>
                                                    </div>
                                                    <div class="{{ ViewClassNamesConstants::C_AT_CD_SM }}">
                                                        <a
                                                            id="{{ $btnId }}"
                                                            class="action-item d-todo"
                                                            role="button"
                                                            data-url="{{ $destroyRoute }}"
                                                            data-sv-localized="true"
                                                            data-guard-msg="{{ base64_encode($destroyMessage) }}"
                                                        >
                                                            <i class="{{ ViewClassNamesConstants::TI_TRS_ALT }}"></i>
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                            @push(StacksConstants::ADM_SCR_PG)
                                                <script defer>
                                                    (() => {
                                                        const errFb = "";
                                                        const dataSVLocalized = "data-sv-localized";
                                                        const dataClientLocalized = "data-client-localized";
                                                        const dataGuardMsg = "data-guard-msg";
                                                        const listenerUpdate = "data-update-listener-active";
                                                        const listenerDelete = "data-delete-listener-active";
                                                        const msgKeyUpdate = "update_todo_unavailable";
                                                        const msgKeyDelete = "destroy_todo_unavailable";
                                                        const getMessage = (el, key) => {
                                                            let msg = errFb;
                                                            if (
                                                                el.getAttribute(dataSVLocalized) === "true" ||
                                                                el.getAttribute(dataClientLocalized) === "true"
                                                            ) {
                                                                msg = el.getAttribute(dataGuardMsg) ?? errFb;
                                                            } else {
                                                                let lang = (
                                                                    window.sessionStorage.getItem("erp-np-lang") ||
                                                                    document.documentElement.lang ||
                                                                    "en"
                                                                )
                                                                    .toLowerCase()
                                                                    .replace(/_/g, "-");
                                                                lang = lang === "pt-br" ? lang : lang.slice(0, 2);
                                                                msg =
                                                                    window.translations?.[lang]?.[key] ||
                                                                    el.getAttribute(dataGuardMsg) ||
                                                                    window.translations?.["en"]?.[key] ||
                                                                    errFb;
                                                                if (msg !== errFb) {
                                                                    el.setAttribute(dataGuardMsg, msg);
                                                                    el.setAttribute(dataClientLocalized, "true");
                                                                }
                                                            }
                                                            return msg;
                                                        };
                                                        const input = document.getElementById("{{ $inputId }}");
                                                        if (input && input.getAttribute(listenerUpdate) !== "true") {
                                                            input.setAttribute(listenerUpdate, "true");
                                                            input.addEventListener("change", event => {
                                                                try {
                                                                    const url = input.getAttribute("data-url");
                                                                    const href = input.form?.action;
                                                                    if ((!url || url === "#") && (!href || href === "#")) {
                                                                        event.preventDefault();
                                                                        const message = getMessage(input, msgKeyUpdate);
                                                                        const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                                                                        const containerId = "toast-container";
                                                                        let container = document.getElementById(containerId);
                                                                        if (!container) {
                                                                            container = document.createElement("div");
                                                                            container.id = containerId;
                                                                            document.body.appendChild(container);
                                                                        }
                                                                        if (bootstrapLink && window.bootstrap) {
                                                                            const toastEl = document.createElement("div");
                                                                            toastEl.className = "toast";
                                                                            toastEl.setAttribute("role","alert");
                                                                            toastEl.setAttribute("aria-live","assertive");
                                                                            toastEl.setAttribute("aria-atomic","true");
                                                                            const body = document.createElement("div");
                                                                            body.className = "toast-body";
                                                                            body.textContent = message;
                                                                            toastEl.appendChild(body);
                                                                            container.appendChild(toastEl);
                                                                            bootstrap.Toast.getOrCreateInstance(toastEl).show();
                                                                        } else {
                                                                            if (message) console.warn("[Dashboard]", message);
                                                                        }
                                                                    }
                                                                } catch (error) {}
                                                            });
                                                        }
                                                        const btn = document.getElementById("{{ $btnId }}");
                                                        if (btn && btn.getAttribute(listenerDelete) !== "true") {
                                                            btn.setAttribute(listenerDelete, "true");
                                                            btn.addEventListener("click", event => {
                                                                try {
                                                                    const url = btn.getAttribute("data-url");
                                                                    const href = btn.href;
                                                                    if ((!url || url === "#") && (!href || href === "#")) {
                                                                        event.preventDefault();
                                                                        const message = getMessage(btn, msgKeyDelete);
                                                                        const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                                                                        const containerId = "toast-container";
                                                                        let container = document.getElementById(containerId);
                                                                        if (!container) {
                                                                            container = document.createElement("div");
                                                                            container.id = containerId;
                                                                            document.body.appendChild(container);
                                                                        }
                                                                        if (bootstrapLink && window.bootstrap) {
                                                                            const toastEl = document.createElement("div");
                                                                            toastEl.className = "toast";
                                                                            toastEl.setAttribute("role","alert");
                                                                            toastEl.setAttribute("aria-live","assertive");
                                                                            toastEl.setAttribute("aria-atomic","true");
                                                                            const body = document.createElement("div");
                                                                            body.className = "toast-body";
                                                                            body.textContent = message;
                                                                            toastEl.appendChild(body);
                                                                            container.appendChild(toastEl);
                                                                            bootstrap.Toast.getOrCreateInstance(toastEl).show();
                                                                        } else {
                                                                            if (message) console.warn("[Dashboard]", message);
                                                                        }
                                                                    } else {
                                                                        const form = document.createElement("form");
                                                                        form.method = "post";
                                                                        form.action = btn.getAttribute("data-url");
                                                                        const token = document.querySelector('meta[name="csrf-token"]').getAttribute("content");
                                                                        const input = document.createElement("input");
                                                                        input.type = "hidden";
                                                                        input.name = "_token";
                                                                        input.value = token;
                                                                        form.appendChild(input);
                                                                        const method = document.createElement("input");
                                                                        method.type = "hidden";
                                                                        method.name = "_method";
                                                                        method.value = "DELETE";
                                                                        form.appendChild(method);
                                                                        document.body.appendChild(form);
                                                                        form.submit();
                                                                    }
                                                                } catch (error) {}
                                                            });
                                                        }
                                                        const observer = new MutationObserver(() => {
                                                            if (
                                                                (!input || !document.body.contains(input)) &&
                                                                (!btn || !document.body.contains(btn))
                                                            ) {
                                                                observer.disconnect();
                                                            }
                                                        });
                                                        observer.observe(document.body, { childList: true, subtree: true });
                                                    })();
                                                </script>
                                            @endpush
                                        @endforeach
                                    @else
                                        <p class="h6 {{ VC::TXCT }}">{{__('No Todo List Found !')}}</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection

@push('script')
    @if($user[UsersConstants::COL_TP] == PermissionsConstants::ADM ||
        $user[UsersConstants::COL_TP] == PermissionsConstants::SA)
        <script defer>
            (() => {
            const errFb = "";
            const dataClientLocalized = "data-client-localized";
            const dataGuardMsg = "data-guard-msg";

            const getLocalizedMessage = (el, msgKey) => {
                let msg = errFb;
                if (
                el.getAttribute("data-sv-localized") === "true" ||
                el.getAttribute(dataClientLocalized) === "true"
                ) {
                msg = el.getAttribute(dataGuardMsg) || errFb;
                } else {
                let lang = (
                    window.sessionStorage.getItem("erp-np-lang") ||
                    document.documentElement.lang ||
                    "en"
                )
                    .toLowerCase()
                    .replace(/_/g, "-");
                lang = lang === "pt-br" ? lang : lang.slice(0, 2);
                msg =
                    window.translations?.[lang]?.[msgKey] ||
                    el.getAttribute(dataGuardMsg) ||
                    window.translations?.["en"]?.[msgKey] ||
                    errFb;
                if (msg !== errFb) {
                    el.setAttribute(dataGuardMsg, msg);
                    el.setAttribute(dataClientLocalized, "true");
                }
                }
                return msg;
            };

            const showErrorUI = (msg) => {
                const bsLink = document.querySelector('link[href*="bootstrap"]');
                if (bsLink && window.bootstrap?.Toast) {
                if (!document.querySelector("#fail-toast")) {
                    const toast = document.createElement("div");
                    toast.id = "fail-toast";
                    toast.className =
                    "toast align-items-center text-white bg-danger border-0 position-fixed bottom-0 end-0 m-3";
                    toast.setAttribute("role", "alert");
                    toast.setAttribute("aria-live", "assertive");
                    toast.setAttribute("aria-atomic", "true");
                    toast.innerHTML = `
                    <div class="{{ VC::DFL }}">
                        <div class="toast-body">${msg}</div>
                        <button type="button" class="{{ VC::BT_CL }} btn-close-white me-2 m-auto"
                                data-bs-dismiss="toast" aria-label="{{ __('Close') }}"></button>
                    </div>`;
                    document.body.appendChild(toast);
                    new bootstrap.Toast(toast).show();
                }
                } else {
                if (msg) console.warn("[Dashboard]", msg);
                }
            };

            document.addEventListener("DOMContentLoaded", () => {
                const mainEle = document.querySelector("#dashboard_view");
                if (!mainEle) return;
                const url = "{{ route('dashboard.view') }}";

                const filterView = (keyword = "") => {
                try {
                    if (!$?.ajax) throw new Error("jQuery AJAX unavailable");
                    $.ajax({ url, data: { keyword }, type: "GET" })
                    .done((data) => {
                        mainEle.innerHTML = data.html || "";
                    })
                    .fail((jq) => {
                        const base = getLocalizedMessage(
                        mainEle,
                        "user_view_filter_unavailable"
                        );
                        const serverMsg = jq.responseJSON?.error;
                        const msg = serverMsg ? `${base}: ${serverMsg}` : base;
                        showErrorUI(msg);
                    });
                } catch (e) {
                    if (
                        window.location.hostname === "localhost" ||
                        window.location.hostname === "127.0.0.1"
                    ) console.error("jQuery or AJAX unavailable", e);
                }
                };

                filterView();

                const bindInput = (input) => {
                if (input.dataset.filterBound) return;
                input.addEventListener("keyup", () => filterView(input.value));
                input.dataset.filterBound = "true";
                };

                document.querySelectorAll("#keyword").forEach(bindInput);

                new MutationObserver((muts) => {
                muts.forEach((m) =>
                    m.addedNodes.forEach(
                    (n) => n instanceof HTMLElement && n.id === "keyword" && bindInput(n)
                    )
                );
                }).observe(document.body, { childList: true, subtree: true });
            });
            })();
        </script>
    @else
        <script defer>
            (() => {
            const errFb = "";
            const dataClientLocalized = "data-client-localized";
            const dataGuardMsg = "data-guard-msg";

            const getLocalizedMessage = (el, msgKey) => {
                let msg = errFb;
                if (
                el.getAttribute("data-sv-localized") === "true" ||
                el.getAttribute(dataClientLocalized) === "true"
                ) {
                msg = el.getAttribute(dataGuardMsg) || errFb;
                } else {
                let lang = (
                    window.sessionStorage.getItem("erp-np-lang") ||
                    document.documentElement.lang ||
                    "en"
                )
                    .toLowerCase()
                    .replace(/_/g, "-");
                lang = lang === "pt-br" ? lang : lang.slice(0, 2);
                msg =
                    window.translations?.[lang]?.[msgKey] ||
                    el.getAttribute(dataGuardMsg) ||
                    window.translations?.["en"]?.[msgKey] ||
                    errFb;
                if (msg !== errFb) {
                    el.setAttribute(dataGuardMsg, msg);
                    el.setAttribute(dataClientLocalized, "true");
                }
                }
                return msg;
            };

            const showErrorUI = (msg) => {
                const bsLink = document.querySelector('link[href*="bootstrap"]');
                if (bsLink && window.bootstrap?.Toast) {
                if (!document.querySelector("#fail-toast")) {
                    const toast = document.createElement("div");
                    toast.id = "fail-toast";
                    toast.className =
                    "toast align-items-center text-white bg-danger border-0 position-fixed bottom-0 end-0 m-3";
                    toast.setAttribute("role", "alert");
                    toast.setAttribute("aria-live", "assertive");
                    toast.setAttribute("aria-atomic", "true");
                    toast.innerHTML = `
                    <div class="{{ VC::DFL }}">
                        <div class="toast-body">${msg}</div>
                        <button type="button" class="{{ VC::BT_CL }} btn-close-white me-2 m-auto"
                            data-bs-dismiss="toast" aria-label="{{ __('Close') }}"></button>
                    </div>`;
                    document.body.appendChild(toast);
                    new bootstrap.Toast(toast).show();
                }
                } else {
                if (msg) console.warn("[Dashboard]", msg);
                }
            };

            const handleAdd = (e) => {
                e.preventDefault();
                const form = document.querySelector("#form-todo");
                if (!form) return;
                const titleInput = form.querySelector("input[name=title]");
                const title = titleInput?.value.trim() || "";
                if (!title) {
                showErrorUI(getLocalizedMessage(form, "todo_title_required"));
                return;
                }
                try {
                const action = form.dataset.action;
                if (!action) throw new Error("Missing action URL");
                $.ajax({
                    url: action,
                    type: "POST",
                    data: { title },
                })
                    .done((resp) => {
                    let data;
                    try {
                        data = typeof resp === "string" ? JSON.parse(resp) : resp;
                    } catch {
                        throw new Error("Invalid JSON");
                    }
                    show_toastr(
                        "{{ __('Success') }}",
                        "{{ __('Todo Added Successfully!') }}",
                        "success"
                    );
                    const html = `
                        <div class="{{ VC::CD_NSD }} todo-member {{ VC::MB2 }}">
                        <div class="{{ ViewClassNamesConstants::R_ALC_SMPD }}">
                            <div class="col-10">
                            <div class="{{ ViewClassNamesConstants::CST_CT_CB }}">
                                <input
                                type="checkbox"
                                class="custom-control-input"
                                id="check-item-${data.id}"
                                data-url="${data.updateUrl}">
                                <label
                                class="{{ ViewClassNamesConstants::CST_LB_SM }}"
                                for="check-item-${data.id}">
                                ${data.title}
                                </label>
                            </div>
                            </div>
                            <div class="{{ ViewClassNamesConstants::C_AT_CD_SM }}">
                            <a
                                class="action-item d-todo"
                                role="button"
                                data-url="${data.deleteUrl}">
                                <i class="{{ VC::TI_TRS_ALT }}"></i>
                            </a>
                            </div>
                        </div>
                        </div>`;
                    document
                        .querySelector("#todolist")
                        ?.insertAdjacentHTML("beforeend", html);
                    titleInput.value = "";
                    $(form).collapse("toggle");
                    })
                    .fail((jq) => {
                    const server = jq.responseJSON?.message;
                    const base = getLocalizedMessage(form, "todo_add_unavailable");
                    showErrorUI(server ? `${base}: ${server}` : base);
                    });
                } catch {
                showErrorUI(getLocalizedMessage(form, "todo_add_unavailable"));
                }
            };

            const handleToggle = (e) => {
                const cb = e.target;
                if (!(cb instanceof HTMLInputElement)) return;
                const url = cb.dataset.url;
                if (!url) return;
                $.ajax({
                url,
                type: "POST",
                dataType: "json",
                })
                .done(() => {
                    show_toastr(
                    "{{ __('Success') }}",
                    "{{ __('Todo Updated Successfully!') }}",
                    "success"
                    );
                })
                .fail((jq) => {
                    const server = jq.responseJSON?.message;
                    const base = getLocalizedMessage(cb, "todo_update_unavailable");
                    showErrorUI(server ? `${base}: ${server}` : base);
                });
            };

            const handleDelete = (e) => {
                e.preventDefault();
                const btn = (e.target.closest(".d-todo") as HTMLElement) || e.target;
                if (!(btn instanceof HTMLElement)) return;
                const url = btn.getAttribute("data-url");
                if (!url) return;
                $.ajax({
                url,
                type: "DELETE",
                dataType: "json",
                })
                .done(() => {
                    show_toastr(
                    "{{ __('Success') }}",
                    "{{ __('Todo Deleted Successfully!') }}",
                    "success"
                    );
                    btn.closest(".todo-member")?.remove();
                })
                .fail((jq) => {
                    const server = jq.responseJSON?.message;
                    const base = getLocalizedMessage(btn, "todo_delete_unavailable");
                    showErrorUI(server ? `${base}: ${server}` : base);
                });
            };

            const initCharts = () => {
                if (!window.ApexCharts) throw new Error("ApexCharts missing");
                const setup = (selector, series, categories, key) => {
                const el = document.querySelector(selector);
                if (!el) return;
                try {
                    const ds = el.dataset;
                    const height = ds.height || 350;
                    const type = ds.type || (key === "overview" ? "line" : "bar");
                    const colKey = ds.color;
                    const themeCol = SiteStyle.colors.theme[colKey] || [];
                    const cfg = {
                    chart: {
                        width: "100%",
                        height,
                        type,
                        zoom: { enabled: false },
                        toolbar: { show: false },
                        shadow: { enabled: false },
                    },
                    stroke: { width: key === "overview" ? 7 : 2, curve: key === "overview" ? "smooth" : undefined },
                    series: [{ name: key === "overview" ? "Tasks" : "Timesheet hours", data: series }],
                    xaxis: {
                        type: "category",
                        categories,
                        labels: {
                        style: {
                            colors: SiteStyle.colors.gray[600],
                            fontSize: "14px",
                            fontFamily: SiteStyle.fonts.base,
                            cssClass: "apexcharts-xaxis-label",
                        },
                        },
                        axisBorder: { show: false },
                        axisTicks: { show: true, color: SiteStyle.colors.gray[300], height: 6 },
                    },
                    yaxis: {
                        labels: {
                        style: { color: SiteStyle.colors.gray[600], fontSize: "12px", fontFamily: SiteStyle.fonts.base },
                        },
                        axisBorder: { show: false },
                        axisTicks: { show: true, color: SiteStyle.colors.gray[300], height: 6 },
                    },
                    fill: { type: "solid" },
                    markers: { size: 4, opacity: 0.7, strokeColor: "#fff", strokeWidth: 3, hover: { size: 7 }, colors: themeCol },
                    grid: { borderColor: SiteStyle.colors.gray[300], strokeDashArray: 5 },
                    dataLabels: { enabled: false },
                    colors: themeCol,
                    };
                    new ApexCharts(el, cfg).render();
                } catch {
                    showErrorUI(
                    getLocalizedMessage(document.body, key === "overview" ? "chart_overview_unavailable" : "chart_timesheet_unavailable")
                    );
                }
                };

                setup(
                "#task_overview",
                {!! json_encode(array_values($home_data['task_overview'])) !!},
                {!! json_encode(array_keys($home_data['task_overview'])) !!},
                "overview"
                );
                setup(
                "#timesheet_logged",
                {!! json_encode(array_values($home_data['timesheet_logged'])) !!},
                {!! json_encode(array_keys($home_data['timesheet_logged'])) !!},
                "timesheet"
                );
            };

            document.addEventListener("DOMContentLoaded", () => {
                if (!document.body.dataset.todoBound) {
                document.addEventListener("click", (e) => {
                    if ((e.target as HTMLElement).id === "todo_submit") handleAdd(e);
                    else if ((e.target as HTMLElement).closest(".d-todo")) handleDelete(e);
                });
                document.addEventListener("change", (e) => {
                    if ((e.target as HTMLElement).matches("#todolist input[type=checkbox]")) handleToggle(e);
                });
                document.body.dataset.todoBound = "true";
                }
                try {
                initCharts();
                } catch {
                /* chart errors already handled */
                }
            });
            })();
        </script>
    @endif
@endpush
