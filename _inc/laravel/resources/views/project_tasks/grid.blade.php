@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        StacksConstants,
        ViewsConstants,
        ViewClassNamesConstants as VC,
        YieldingConstants
    };
    use App\Models\{ProjectTask,Utility};
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\{Collection,Str};
    $lang = Utility::fetchUserLang();
    $projectIndexBaseName = ViewsConstants::PRJ . '.index';
    $projectIndexKebabName = Str::kebab($projectIndexBaseName);
    $projectIndexResolvedName = Route::has($projectIndexBaseName) ? $projectIndexBaseName : (Route::has($projectIndexKebabName) ? $projectIndexKebabName : null);
    $projectIndexUrl = $projectIndexResolvedName ? route($projectIndexResolvedName) : '#';
    $projectIndexGuardMsg = Utility::fetchLinkMessage($lang, ViewsConstants::PRJ, 'project_index_route_unavailable') ?? 'Project index route is unavailable. Please contact technical support or your domain administrator.';
@endphp
@extends(ExtendingLayoutsConstants::ADM)

@section(YieldingConstants::ADM_PG_TTL)
    {{__('Tasks')}}
@endsection

@section(YieldingConstants::ADM_BDC)
    <li class="breadcrumb-item">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
        {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="breadcrumb-item">
        <a
            id="project-index-link"
            href="{{ $projectIndexUrl }}"
            data-url="{{ $projectIndexUrl }}"
            data-guard-msg="{{ $projectIndexGuardMsg }}"
        >
            {{ __('Project') }}
        </a>
    </li>
    @push(StacksConstants::ADM_SCR_PG)
        <script defer>
            (() => {
                const link = document.getElementById('project-index-link');
                if (!link || link.getAttribute('data-listener-active') === 'true') return;
                link.setAttribute('data-listener-active', 'true');
                link.addEventListener('click', e => {
                    try {
                        const url = link.getAttribute('data-url') || '#';
                        if (url !== '#') return;
                        e.preventDefault();
                        const msg = link.getAttribute('data-guard-msg') || '# ERROR';
                        const hasBootstrap = document.querySelector('link[href*="bootstrap"]') && window.bootstrap;
                        let container = document.getElementById('toast-container');
                        if (!container) {
                            container = document.createElement('div');
                            container.id = 'toast-container';
                            document.body.appendChild(container);
                        }
                        if (hasBootstrap) {
                            const toast = document.createElement('div');
                            toast.className = 'toast';
                            toast.setAttribute('role','alert');
                            toast.setAttribute('aria-live','assertive');
                            toast.setAttribute('aria-atomic','true');
                            const body = document.createElement('div');
                            body.className = 'toast-body';
                            body.textContent = msg;
                            toast.appendChild(body);
                            container.appendChild(toast);
                            bootstrap.Toast.getOrCreateInstance(toast).show();
                        } else {
                            alert(msg);
                        }
                        link.setAttribute('data-failed-route', 'true');
                    } catch (err) {}
                });
            })();
        </script>
    @endpush
    <li class="breadcrumb-item">{{__('Task')}}</li>
@endsection

@php
    $viewSafe = (string)($view ?? 'list');
    $tasksSafe = (isset($tasks) && (is_array($tasks) || $tasks instanceof Collection)) ? $tasks : [];
@endphp

@section(YieldingConstants::ADM_ACT_BTN)
    <div class="{{ VC::FEND }}">
        @if($viewSafe === 'grid')
            @php
                $taskboardListBaseName     = ViewsConstants::TSKB.'.view';
                $taskboardListKebabName    = Str::kebab($taskboardListBaseName);
                $taskboardListResolvedName = Route::has($taskboardListBaseName)
                    ? $taskboardListBaseName
                    : (Route::has($taskboardListKebabName) ? $taskboardListKebabName : null);
                $taskboardListParam        = 'list';
                $taskboardListUrl          = $taskboardListResolvedName ? route($taskboardListResolvedName, $taskboardListParam) : '#';
                $taskboardListGuardMsg     = Utility::fetchLinkMessage($lang, ViewsConstants::TSKB, 'list_taskboard_route_unavailable') ?? 'List taskboard route is unavailable. Please contact technical support or your domain administrator.';
                $taskboardListLinkId       = 'taskboard-list-view-link';
                $taskboardListTitle        = __('List View');
            @endphp
            <a href="{{ $taskboardListUrl }}"
            id="{{ $taskboardListLinkId }}"
            class="{{ VC::BT_SM_PM }}"
            data-url="{{ $taskboardListUrl }}"
            data-guard-msg="{{ $taskboardListGuardMsg }}"
            data-bs-toggle="tooltip"
            title="{{ $taskboardListTitle }}">
                <span class="btn-inner--text"><i class="{{ VC::TI_LT }}"></i></span>
            </a>
            @push(StacksConstants::ADM_SCR_PG)
                <script defer>
                    (() => {
                        try {
                            const l = document.getElementById('{{ $taskboardListLinkId }}');
                            if (!l || l.getAttribute('data-listener-active') === 'true') return;
                            l.setAttribute('data-listener-active', 'true');
                            l.addEventListener('click', e => {
                                try {
                                    const href = l.getAttribute('href') || '#';
                                    const url  = l.getAttribute('data-url') || href || '#';
                                    if (href !== '#' || url !== '#') return;
                                    e.preventDefault();
                                    const msg = l.getAttribute('data-guard-msg') || 'List taskboard route is unavailable. Please contact technical support or your domain administrator.';
                                    const hasBootstrap = document.querySelector('link[href*="bootstrap"]') && window.bootstrap;
                                    let container = document.getElementById('toast-container');
                                    if (!container) {
                                        container = document.createElement('div');
                                        container.id = 'toast-container';
                                        document.body.appendChild(container);
                                    }
                                    if (hasBootstrap) {
                                        const toast = document.createElement('div');
                                        toast.className = 'toast';
                                        toast.setAttribute('role', 'alert');
                                        toast.setAttribute('aria-live', 'assertive');
                                        toast.setAttribute('aria-atomic', 'true');
                                        const body = document.createElement('div');
                                        body.className = 'toast-body';
                                        body.textContent = msg;
                                        toast.appendChild(body);
                                        container.appendChild(toast);
                                        bootstrap.Toast.getOrCreateInstance(toast).show();
                                    } else {
                                        alert(msg);
                                    }
                                    l.setAttribute('data-failed-route', 'true');
                                } catch (err) {}
                            });
                        } catch (error) {}
                    })();
                </script>
            @endpush
        @else
            <a href="{{ route(VW::TSKB.'.view', 'grid') }}" class="{{ VC::BT_SM_PM }}" data-bs-toggle="tooltip" title="{{ __('Card View') }}">
                <span class="btn-inner--text"><i class="ti ti-table"></i></span>
            </a>
        @endif
    </div>
@endsection

@section(YieldingConstants::ADM_CTT)
    <div class="{{ VC::RW }}">
        <div class="{{ VC::C12 }}">
            <div class="{{ VC::CD }}">
                <div class="{{ VC::RW }}">
                    @if(count($tasksSafe) > 0)
                        @foreach($tasksSafe as $task)
                            @php
                                $tid = data_get($task,'id');
                                $priority = (int)(data_get($task,'priority') ?? -1);
                                $priorityColors = is_array(ProjectTask::$priority_color ?? null) ? ProjectTask::$priority_color : [];
                                $priorityLabels = is_array(ProjectTask::$priority ?? null) ? ProjectTask::$priority : [];
                                $priorityColorKey = $priorityColors[$priority] ?? 'secondary';
                                $priorityLabel = $priorityLabels[$priority] ?? __('Unknown');
                                $borderColor = data_get($task,'priority_color');
                                $progressArr = method_exists($task,'taskProgress') ? (array) $task->taskProgress($task) : [];
                                $pctStr = (string)($progressArr['percentage'] ?? '0%');
                                $pctNum = (float)str_replace('%','',$pctStr);
                                $pctColor = (string)($progressArr['color'] ?? 'secondary');
                                $taskName = data_get($task,'name') ?? __('No task name available');
                                $projectId = data_get($task,'project.id') ?? '';
                                $taskFilesRel = data_get($task,'taskFiles');
                                $filesCount = is_countable($taskFilesRel) ? count($taskFilesRel) : ((is_object($taskFilesRel) && method_exists($taskFilesRel,'count')) ? $taskFilesRel->count() : 0);
                                $commentsRel = data_get($task,'comments');
                                $commentsCount = is_countable($commentsRel) ? count($commentsRel) : ((is_object($commentsRel) && method_exists($commentsRel,'count')) ? $commentsRel->count() : 0);
                                $checklistRel = data_get($task,'checklist');
                                $checklistCount = (is_object($checklistRel) && method_exists($checklistRel,'count')) ? $checklistRel->count() : (is_countable($checklistRel) ? count($checklistRel) : 0);
                                $checklistTotal = method_exists($task,'countTaskChecklist') ? (int)$task->countTaskChecklist() : $checklistCount;
                                $endDate = data_get($task,'end_date');
                                $endDateText = (!empty($endDate) && $endDate !== '0000-00-00') ? (Utility::getDateFormated($endDate) ?? '') : '';
                                $isOverdue = $endDateText && (strtotime((string)$endDate) < time());
                                $usersRel = method_exists($task,'users') ? $task->users() : [];
                                $usersArr = (is_array($usersRel) || $usersRel instanceof \Illuminate\Support\Collection) ? $usersRel : [];
                                $usersCount = is_countable($usersArr) ? count($usersArr) : 0;
                            @endphp
                            <div class="{{ VC::CLMS3 }}">
                                <div class="{{ VC::CD_NSD }} m-3 card-progress" id="{{ $tid ?? '' }}" style="{{ !empty($borderColor) ? 'border-left: 2px solid '.$borderColor.' !important' : '' }};">
                                    <div class="card-body">
                                        <div class="{{ VC::R_ALC }} mb-2">
                                            <div class="col-6">
                                                <span class="{{ VC::BDG }} p-2 {{ VC::PX3 }} rounded bg-{{ $priorityColorKey }}">{{ __($priorityLabel) }}</span>
                                            </div>
                                            <div class="col-6 text-end">
                                                @if($pctNum > 0)
                                                    <span class="{{ VC::TXSM }}">{{ $pctStr }}</span>
                                                    <div class="{{ VC::PG }}" style="top:0px">
                                                        <div class="progress-bar bg-{{ $pctColor }}" role="progressbar" style="width: {{ $pctStr }};"></div>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                        <a class="{{ VC::H6 }} task-name-break" href="{{ route(VW::PRJ_TSK_C . '.index', $projectId) }}">{{ $taskName }}</a>
                                        <div class="{{ VC::R_ALC }}">
                                            <div class="col-12">
                                                <div class="actions {{ VC::DFL_JCB }} mt-2 mb-2">
                                                    @if($filesCount > 0)
                                                        <div class="action-item {{ VC::MR2 }}"><i class="ti ti-paperclip {{ VC::MR2 }}"></i>{{ $filesCount }}</div>
                                                    @endif
                                                    @if($commentsCount > 0)
                                                        <div class="action-item {{ VC::MR2 }}"><i class="ti ti-brand-hipchat {{ VC::MR2 }}"></i>{{ $commentsCount }}</div>
                                                    @endif
                                                    @if($checklistCount > 0)
                                                        <div class="action-item {{ VC::MR2 }}"><i class="ti ti-list-check {{ VC::MR2 }}"></i>{{ $checklistTotal }}</div>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                @if($endDateText)
                                                    <small @if($isOverdue) class="text-danger" @endif>{{ $endDateText }}</small>
                                                @endif
                                            </div>
                                            <div class="col-6 text-end">
                                                @if($usersCount > 0)
                                                    <div class="avatar-group">
                                                        @foreach($usersArr as $key => $u)
                                                            @if($key < 3)
                                                                @php
                                                                    $uName = data_get($u,'name') ?? '';
                                                                    $uAvatar = data_get($u,'avatar');
                                                                    $uSrc = !empty($uAvatar) ? asset('/storage/uploads/avatar/'.$uAvatar) : asset('/storage/uploads/avatar/avatar.png');
                                                                @endphp
                                                                <a href="#" class="{{ VC::AV_CC_SM }}">
                                                                    <img class="hweb" data-original-title="{{ $uName }}" src="{{ $uSrc }}">
                                                                </a>
                                                            @else
                                                                @break
                                                            @endif
                                                        @endforeach
                                                        @if($usersCount > 3)
                                                            <span class="{{ VC::BDG }} rounded-circle">{{ '+' . ($usersCount - 3) }}</span>
                                                        @endif
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="{{ VC::CM12 }}">
                            <h6 class="text-center m-3">{{ __('No tasks found') }}</h6>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@push(StacksConstants::ADM_SCR_PG)
    <script async>
        window.translations = {
            ar: { taskboard_unavailable: "لوحة المهام غير متاحة حاليًا.", request_failed: "فشل الطلب. حاول مرة أخرى." },
            da: { taskboard_unavailable: "Tavlevisning er ikke tilgængelig lige nu.", request_failed: "Anmodning mislykkedes. Prøv igen." },
            de: { taskboard_unavailable: "Taskboard ist derzeit nicht verfügbar.", request_failed: "Anfrage fehlgeschlagen. Bitte erneut versuchen." },
            en: { taskboard_unavailable: "Taskboard is currently unavailable.", request_failed: "Request failed. Please try again." },
            es: { taskboard_unavailable: "El panel de tareas no está disponible.", request_failed: "La solicitud falló. Inténtalo de nuevo." },
            fr: { taskboard_unavailable: "Le tableau des tâches est indisponible.", request_failed: "Échec de la requête. Réessayez." },
            he: { taskboard_unavailable: "לוח המשימות אינו זמין כעת.", request_failed: "הבקשה נכשלה. נסה שוב." },
            it: { taskboard_unavailable: "La bacheca non è disponibile.", request_failed: "Richiesta non riuscita. Riprova." },
            ja: { taskboard_unavailable: "タスクボードは現在利用できません。", request_failed: "リクエストに失敗しました。もう一度お試しください。" },
            nl: { taskboard_unavailable: "Taakbord is momenteel niet beschikbaar.", request_failed: "Aanvraag mislukt. Probeer opnieuw." },
            pl: { taskboard_unavailable: "Tablica zadań jest obecnie niedostępna.", request_failed: "Żądanie nie powiodło się. Spróbuj ponownie." },
            pt: { taskboard_unavailable: "Quadro de tarefas indisponível.", request_failed: "Falha na solicitação. Tente novamente." },
            "pt-br": { taskboard_unavailable: "Quadro de tarefas indisponível no momento.", request_failed: "Falha na requisição. Tente novamente." },
            ru: { taskboard_unavailable: "Доска задач недоступна.", request_failed: "Запрос не выполнен. Повторите попытку." },
            tr: { taskboard_unavailable: "Görev panosu şu anda kullanılamıyor.", request_failed: "İstek başarısız oldu. Lütfen tekrar deneyin." },
            zh: { taskboard_unavailable: "任务面板当前不可用。", request_failed: "请求失败。请重试。" }
        };
    </script>
    <script defer>
        (function () {
            if (!window.jQuery || typeof $ !== 'function') { try { console.error('jQuery failed to load'); } catch (_) {} return; }

            const errFb = "# ERROR";
            const dataClientLocalized = "data-client-localized";
            const dataGuardMsg = "data-guard-msg";
            const dataGuardAttached = "data-guard-attached";
            const toastContainerId = "sv-toast-container";
            const bsLinkSel = 'link[rel~="stylesheet"][href*="bootstrap"]';
            const $taskView = $('#taskboard_view');
            const $filters = $('.task-filter-actions');
            const $sort = $('#task_sort');
            const $search = $('#task_keyword');

            const localize = (el, msgKey) => {
            let msg = errFb;
            if (el.getAttribute('data-sv-localized') === 'true' || el.getAttribute(dataClientLocalized) === 'true') {
                msg = el.getAttribute(dataGuardMsg) || errFb;
            } else {
                let lang = (window.sessionStorage.getItem("erp-np-lang") || document.documentElement.lang || "en").toLowerCase().replace(/_/g, "-");
                lang = lang === "pt-br" ? lang : lang.slice(0, 2);
                msg = window.translations?.[lang]?.[msgKey] || el.getAttribute(dataGuardMsg) || window.translations?.["en"]?.[msgKey] || errFb;
                if (msg !== errFb) {
                el.setAttribute(dataGuardMsg, msg);
                el.setAttribute(dataClientLocalized, "true");
                }
            }
            return msg;
            };

            const ensureToastContainer = () => {
            if (!document.getElementById(toastContainerId)) {
                const wrap = document.createElement('div');
                wrap.id = toastContainerId;
                wrap.className = 'position-fixed top-0 end-0 p-3';
                document.body.appendChild(wrap);
            }
            };

            const showErrorUI = (hostEl, msgKey) => {
            const msg = localize(hostEl, msgKey);
            const hasBootstrapCss = !!document.querySelector(bsLinkSel);
            if (!hasBootstrapCss) { alert(msg); return; }
            ensureToastContainer();
            const id = 'toast-' + Date.now();
            const toastHtml =
                '<div id="'+id+'" class="toast align-items-center text-bg-danger border-0" role="alert" aria-live="assertive" aria-atomic="true">' +
                '<div class="d-flex">' +
                    '<div class="toast-body">'+ msg +'</div>' +
                    '<button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>' +
                '</div>' +
                '</div>';
            const container = document.getElementById(toastContainerId);
            if (!container.querySelector('#'+id)) {
                container.insertAdjacentHTML('beforeend', toastHtml);
                try {
                const t = new bootstrap.Toast(document.getElementById(id), { delay: 5000 });
                t.show();
                } catch (_) { alert(msg); }
            }
            };

            let sort = 'created_at-desc';
            let status = [];

            const ajaxFilterTaskView = (task_sort, keyword = '', sts = []) => {
            const routeUrl = '{{ route('project.taskboard.view') }}' ?? '';
            if ((!routeUrl || routeUrl === '#')) { showErrorUI($taskView.get(0) ?? document.body, 'taskboard_unavailable'); return; }
            try {
                $.ajax({
                url: routeUrl,
                data: { view: '{{$view}}', sort: task_sort, keyword: keyword, status: sts },
                success: data => { $taskView.html(data?.html ?? ''); },
                error: () => { showErrorUI($taskView.get(0) ?? document.body, 'request_failed'); }
                });
            } catch (_) { showErrorUI($taskView.get(0) ?? document.body, 'request_failed'); }
            };

            const attachFilterHandlers = () => {
            const el = $filters.get(0);
            if (!el || el.getAttribute(dataGuardAttached) === 'true') return;
            $filters.on('click.taskfilters', '.filter-action', function () {
                if ($(this).hasClass('filter-show-all')) {
                $('.filter-action').removeClass('active'); $(this).addClass('active');
                } else {
                $('.filter-show-all').removeClass('active');
                if ($(this).hasClass('filter-other')) { $('.filter-other').removeClass('active'); }
                if ($(this).hasClass('active')) { $(this).removeClass('active').blur(); } else { $(this).addClass('active'); }
                }
                const filterArray = [];
                $('div.task-filter-actions').find('.active').each(function () { filterArray.push($(this).attr('data-val')); });
                status = filterArray;
                ajaxFilterTaskView(sort, $('#task_keyword').val() ?? '', status);
            });
            el.setAttribute(dataGuardAttached, 'true');
            };

            const attachSortHandlers = () => {
            const el = $sort.get(0);
            if (!el || el.getAttribute(dataGuardAttached) === 'true') return;
            $sort.on('click.tasksort', 'a', function (e) {
                const url = this.getAttribute('data-url');
                const href = this.href;
                if ((!url || url === '#') && (!href || href === '#')) { e.preventDefault(); showErrorUI(this, 'taskboard_unavailable'); return; }
                sort = $(this).attr('data-val') ?? sort;
                ajaxFilterTaskView(sort, $('#task_keyword').val() ?? '', status);
                $('#task_sort a').removeClass('active'); $(this).addClass('active');
            });
            el.setAttribute(dataGuardAttached, 'true');
            };

            const attachSearchHandler = () => {
            const el = $search.get(0);
            if (!el || el.getAttribute(dataGuardAttached) === 'true') return;
            $search.on('pointerup.tasksearch', function () {
                ajaxFilterTaskView(sort, $(this).val() ?? '', status);
            });
            el.setAttribute(dataGuardAttached, 'true');
            };

            const init = () => {
            ajaxFilterTaskView('created_at-desc', '', ['see_my_tasks']);
            attachFilterHandlers();
            attachSortHandlers();
            attachSearchHandler();
            };

            const mo = new MutationObserver(() => {
            if (!$filters.length || $filters.get(0)?.getAttribute(dataGuardAttached) !== 'true') { attachFilterHandlers(); }
            if (!$sort.length || $sort.get(0)?.getAttribute(dataGuardAttached) !== 'true') { attachSortHandlers(); }
            if (!$search.length || $search.get(0)?.getAttribute(dataGuardAttached) !== 'true') { attachSearchHandler(); }
            if ($filters.length === 0) { $('.task-filter-actions').off('.taskfilters'); }
            if ($sort.length === 0) { $('#task_sort').off('.tasksort'); }
            if ($search.length === 0) { $('#task_keyword').off('.tasksearch'); }
            });

            try { mo.observe(document.body, { childList: true, subtree: true }); } catch (_) {}

            $(function () { init(); });
        })();
    </script>
@endpush
