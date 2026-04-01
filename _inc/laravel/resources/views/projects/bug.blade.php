@php
    try {
$user = Auth::user();
        $lang = Utility::fetchUserLang(user:$user);

            function resolveBugRoute($baseName) {
            $kebab = Str::kebab($baseName);
            return Route::has($baseName) ? $baseName : (Route::has($kebab) ? $kebab : null);
        }

            function safeBugRoute($routeName, $params = []) {
            return $routeName ? route($routeName, $params) : '#';
        }

            $guardMessages = [
            'project_index' => Utility::fetchLinkMessage($lang, ViewsConstants::PRJ, 'project_index_route_unavailable') ?? 'Project index route is unavailable. Please contact technical support or your domain administrator.',
            'project_show' => Utility::fetchLinkMessage($lang, ViewsConstants::PRJ, 'show_project_route_unavailable') ?? 'Show project route is unavailable. Please contact technical support or your domain administrator.',
            'bug_list' => Utility::fetchLinkMessage($lang, ViewsConstants::PRJ_TSK_BUG, 'list_bug_route_unavailable') ?? 'List bug route is unavailable. Please contact technical support or your domain administrator.',
            'bug_create' => Utility::fetchLinkMessage($lang, ViewsConstants::PRJ_TSK_BUG, 'create_bug_route_unavailable') ?? 'Create bug route is unavailable. Please contact technical support or your domain administrator.',
        ];
    } catch (\Throwable $e) {
        \Log::error('projects/bug — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    }
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@if(isset($project) && !empty($project))
    @section(YieldingConstants::ADM_PG_TTL)
        {{__('Manage Bug Report')}}
    @endsection
    @push(StacksConstants::ADM_SCR_PG)
    @endpush
    @php
        $projectIndexRouteName = resolveBugRoute(ViewsConstants::PRJ . '.index');
        $projectIndexUrl = safeBugRoute($projectIndexRouteName);
@endphp
    @section(YieldingConstants::ADM_BDC)
        <li class="{{ VC::BCI }}">
            <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
            {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
                {{ __('Dashboard') }}
            </a>
        </li>
        <li class="{{ VC::BCI }}">
            <a href="{{ $projectIndexUrl }}" data-route-guard data-url="{{ $projectIndexUrl }}" data-guard-msg="{{ base64_encode($guardMessages['project_index']) }}">
                {{ __('Project') }}
            </a>
        </li>
        <li class="{{ VC::BCI }}">
            @php
                try {
                    $projectId = isset($project) && !empty($project->id) ? $project->id : null;
                    $projectShowRouteName = resolveBugRoute(ViewsConstants::PRJ.'.show');
                    $projectShowUrl = $projectShowRouteName && $projectId ? safeBugRoute($projectShowRouteName, $projectId) : '#';
                    $projectNameText = ($project->project_name ?? null) ? ucwords($project->project_name) : __('No name found for project');
                } catch (\Throwable $e) {
                    \Log::error('projects/bug — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                }
@endphp
            <a href="{{ $projectShowUrl }}" data-route-guard data-url="{{ $projectShowUrl }}" data-guard-msg="{{ base64_encode($guardMessages['project_show']) }}">
                {{ $projectNameText }}
            </a>
        </li>
        <li class="{{ VC::BCI }}">{{__('Bug Report')}}</li>
        <script>
        if (typeof window.BugBreadcrumbHandler === 'undefined') {
            window.BugBreadcrumbHandler = {
                debounceMap: new Map(),

                init() {
                    document.querySelectorAll('[data-route-guard]').forEach(el => this.attachClickHandler(el));
                },

                attachClickHandler(el) {
                    const elId = el.getAttribute('data-url') || el.href;
                    el.addEventListener('click', (e) => {
                        if (this.debounceMap.has(elId)) { e.preventDefault(); return; }
                        const href = el.getAttribute('href') || '#';
                        const url = el.getAttribute('data-url') || href || '#';
                        if (href !== '#' && url !== '#') return;
                        e.preventDefault();
                        this.showToast(el.getAttribute('data-guard-msg') || 'Route unavailable');
                        this.debounceMap.set(elId, true);
                        setTimeout(() => this.debounceMap.delete(elId), 800);
                    }, { passive: false });
                },

                showToast(msg) {
                    const container = document.getElementById('toast-container') || (() => {
                        const c = document.createElement('div');
                        c.id = 'toast-container';
                        c.className = 'position-fixed top-0 end-0 p-3';
                        document.body.appendChild(c);
                        return c;
                    })();
                    const toast = document.createElement('div');
                    toast.className = 'toast';
                    toast.setAttribute('role', 'alert');
                    toast.setAttribute('aria-live', 'assertive');
                    toast.setAttribute('aria-atomic', 'true');
                    toast.innerHTML = `<div class="toast-body">${msg}<button type="button" class="{{ VC::BT_CL }} {{ VC::MS2 }}" data-bs-dismiss="toast" aria-label="Close"></button></div>`;
                    container.appendChild(toast);
                    window.bootstrap?.Toast?.getOrCreateInstance(toast)?.show() || alert(msg);
                    toast.addEventListener('hidden.bs.toast', () => toast.remove());
                }
            };
            document.readyState === 'loading' ? document.addEventListener('DOMContentLoaded', () => window.BugBreadcrumbHandler.init()) : window.BugBreadcrumbHandler.init();
        }
        </script>
    @endsection
    @section(YieldingConstants::ADM_ACT_BTN)
        <div class="{{ VC::FEND }}">
            @can('manage bug report')
                @php
                    try {
                        $projectId = isset($project) && !empty($project->id) ? $project->id : null;
                        $bugListRouteName = resolveBugRoute(ViewsConstants::PRJ_TSK_BUG);
                        $bugListUrl = $bugListRouteName && $projectId ? safeBugRoute($bugListRouteName, $projectId) : '#';
                    } catch (\Throwable $e) {
                        \Log::error('projects/bug — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                    }
@endphp
                <a href="{{ $bugListUrl }}" class="{{ VC::BT_SM_PM }}" data-route-guard data-url="{{ $bugListUrl }}" data-guard-msg="{{ base64_encode($guardMessages['bug_list']) }}" data-bs-toggle="tooltip" title="{{ __('List') }}">
                    <i class="{{ VC::TI_LT }}"></i>
                </a>
            @endcan
            @can('create bug report')
                @php
                    $bugCreateRouteName = resolveBugRoute(ViewsConstants::PRJ_TSK_BUG.'.create');
                    $bugCreateUrl = $bugCreateRouteName && $projectId ? safeBugRoute($bugCreateRouteName, $projectId) : '#';
@endphp
                <a href="{{ $bugCreateUrl }}" data-size="lg" data-url="{{ $bugCreateUrl }}" data-ajax-popup="true" data-route-guard data-guard-msg="{{ base64_encode($guardMessages['bug_create']) }}" data-bs-toggle="tooltip" data-title="{{ __('Create New Bug') }}" title="{{ __('Create New Bug') }}" class="{{ VC::BT_SM_PM }}">
                    <i class="{{ VC::TI_PLS }}"></i>
                </a>
            @endcan
        </div>
        <script>
        if (typeof window.BugActionHandler === 'undefined') {
            window.BugActionHandler = {
                debounceMap: new Map(),

                init() {
                    document.querySelectorAll('[data-route-guard]').forEach(el => this.attachClickHandler(el));
                },

                attachClickHandler(el) {
                    const elId = el.getAttribute('data-url') || el.href;
                    el.addEventListener('click', (e) => {
                        if (this.debounceMap.has(elId)) { e.preventDefault(); return; }
                        const href = el.getAttribute('href') || '#';
                        const url = el.getAttribute('data-url') || href || '#';
                        if (href !== '#' && url !== '#') return;
                        e.preventDefault();
                        this.showToast(el.getAttribute('data-guard-msg') || 'Route unavailable');
                        this.debounceMap.set(elId, true);
                        setTimeout(() => this.debounceMap.delete(elId), 800);
                    }, { passive: false });
                },

                showToast(msg) {
                    const container = document.getElementById('toast-container') || (() => {
                        const c = document.createElement('div');
                        c.id = 'toast-container';
                        c.className = 'position-fixed top-0 end-0 p-3';
                        document.body.appendChild(c);
                        return c;
                    })();
                    const toast = document.createElement('div');
                    toast.className = 'toast';
                    toast.setAttribute('role', 'alert');
                    toast.setAttribute('aria-live', 'assertive');
                    toast.setAttribute('aria-atomic', 'true');
                    toast.innerHTML = `<div class="toast-body">${msg}<button type="button" class="{{ VC::BT_CL }} {{ VC::MS2 }}" data-bs-dismiss="toast" aria-label="Close"></button></div>`;
                    container.appendChild(toast);
                    window.bootstrap?.Toast?.getOrCreateInstance(toast)?.show() || alert(msg);
                    toast.addEventListener('hidden.bs.toast', () => toast.remove());
                }
            };
            document.readyState === 'loading' ? document.addEventListener('DOMContentLoaded', () => window.BugActionHandler.init()) : window.BugActionHandler.init();
        }
        </script>
    @endsection
    @section(YieldingConstants::ADM_CTT)
        <div class="row">
            <div class="{{ VC::CXL12 }}">
                <div class="card">
                    <div class="{{ VC::CD_BD_TB_BD }}">
                        <div class="{{ VC::TB_RSP }}">
                            <table class="table datatable">
                                <thead>
                                <tr>
                                    <th> {{__('Bug Id')}}</th>
                                    <th> {{__('Assign To')}}</th>
                                    <th> {{__('Bug Title')}}</th>
                                    <th> {{__('Start Date')}}</th>
                                    <th> {{__('Due Date')}}</th>
                                    <th> {{__('Status')}}</th>
                                    <th> {{__('Priority')}}</th>
                                    <th> {{__('Created By')}}</th>
                                    <th width="10%"> {{__('Action')}}</th>
                                </tr>
                                </thead>
                                <tbody>
                                    @if(isset($bugs) && is_countable($bugs) && count($bugs) > 0)
                                        @foreach ($bugs as $bug)
                                            @if(isset($bug) && is_object($bug))
                                                @php
                                                    $bugId = data_get($bug, 'bug_id', '');
                                                    $bugTitle = data_get($bug, 'title', __('Untitled Bug'));
                                                    $bugPriority = data_get($bug, 'priority', __('No Priority'));
                                                    $bugStartDate = data_get($bug, 'start_date', '');
                                                    $bugDueDate = data_get($bug, 'due_date', '');
                                                    $bugObjectId = data_get($bug, 'id');
                                                    $projectId = data_get($project, 'id');
                                                    $formattedBugId = '';
                                                    if (isset($user) && is_object($user) && method_exists($user, 'bugNumberFormat') && !empty($bugId)) {
                                                        try {
                                                            $formattedBugId = $user->bugNumberFormat($bugId);
                                                        } catch (Exception $e) {
                                                            $formattedBugId = e($bugId);
                                                        }
                                                    } elseif (!empty($bugId)) {
                                                        $formattedBugId = e($bugId);
                                                    } else {
                                                        $formattedBugId = __('No ID');
                                                    }
                                                    $assignTo = data_get($bug, 'assignTo');
                                                    $assigneeName = '';
                                                    if (isset($assignTo) && is_object($assignTo)) {
                                                        $assigneeName = data_get($assignTo, 'name', '');
                                                    }
                                                    $displayAssignee = !empty($assigneeName) ? e($assigneeName) : __('Not Assigned');
                                                    $formattedStartDate = __('No Start Date');
                                                    $formattedDueDate = __('No Due Date');
                                                    if (isset($user) && is_object($user) && method_exists($user, 'dateFormat')) {
                                                        if (!empty($bugStartDate)) {
                                                            try {
                                                                $formattedStartDate = $user->dateFormat($bugStartDate);
                                                            } catch (Exception $e) {
                                                                $formattedStartDate = e($bugStartDate);
                                                            }
                                                        }
                                                        if (!empty($bugDueDate)) {
                                                            try {
                                                                $formattedDueDate = $user->dateFormat($bugDueDate);
                                                            } catch (Exception $e) {
                                                                $formattedDueDate = e($bugDueDate);
                                                            }
                                                        }
                                                    } else {
                                                        $formattedStartDate = !empty($bugStartDate) ? e($bugStartDate) : __('No Start Date');
                                                        $formattedDueDate = !empty($bugDueDate) ? e($bugDueDate) : __('No Due Date');
                                                    }
                                                    $bugStatus = data_get($bug, 'bug_status');
                                                    $statusTitle = '';
                                                    if (isset($bugStatus) && is_object($bugStatus)) {
                                                        $statusTitle = data_get($bugStatus, 'title', '');
                                                    }
                                                    $displayStatus = !empty($statusTitle) ? e($statusTitle) : __('No Status');
                                                    $createdBy = data_get($bug, 'createdBy');
                                                    $creatorName = '';
                                                    if (isset($createdBy) && is_object($createdBy)) {
                                                        $creatorName = data_get($createdBy, 'name', '');
                                                    }
                                                    $displayCreator = !empty($creatorName) ? e($creatorName) : __('Unknown User');
@endphp
                                                <tr>
                                                    <td>{{ $formattedBugId }}</td>
                                                    <td>{{ $displayAssignee }}</td>
                                                    <td>{{ e($bugTitle) }}</td>
                                                    <td>{{ $formattedStartDate }}</td>
                                                    <td>{{ $formattedDueDate }}</td>
                                                    <td>{{ $displayStatus }}</td>
                                                    <td>{{ e($bugPriority) }}</td>
                                                    <td>{{ $displayCreator }}</td>
                                                    <td class="Action" width="10%">
                                                        @if(!empty($projectId) && !empty($bugObjectId))
                                                            @can('edit bug report')
                                                                @php
                                                                    try {
                                                                        $bugEditBaseName     = ViewsConstants::PRJ_TSK_BUG.'.edit';
                                                                        $bugEditKebabName    = Str::kebab($bugEditBaseName);
                                                                        $bugEditResolvedName = Route::has($bugEditBaseName)
                                                                            ? $bugEditBaseName
                                                                            : (Route::has($bugEditKebabName) ? $bugEditKebabName : null);
                                                                        $projectIdValue      = isset($projectId) && !empty($projectId) ? $projectId : (isset($bug) && !empty($bug->project_id) ? $bug->project_id : null);
                                                                        $bugObjectIdValue    = isset($bugObjectId) && !empty($bugObjectId) ? $bugObjectId : (isset($bug) && !empty($bug->id) ? $bug->id : null);
                                                                        $bugEditUrl          = ($bugEditResolvedName && $projectIdValue && $bugObjectIdValue) ? route($bugEditResolvedName, [$projectIdValue, $bugObjectIdValue]) : '#';
                                                                        $bugEditGuardMsg     = Utility::fetchLinkMessage($lang, ViewsConstants::PRJ_TSK_BUG, 'edit_bug_route_unavailable') ?? 'Edit bug route is unavailable. Please contact technical support or your domain administrator.';
                                                                        $bugEditLinkId       = 'bug-edit-link-'.($bugObjectIdValue ?? 'x');
                                                                        $bugEditTitle        = __('Edit Bug');
                                                                        $bugEditTooltip      = __('Edit');
                                                                    } catch (\Throwable $e) {
                                                                        \Log::error('projects/bug — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                                    }
@endphp
                                                                <div class="{{ VC::ACT_BTN_INF }}">
                                                                    <a href="{{ $bugEditUrl }}"
                                                                    data-route-guard
                                                                    class="{{ VC::BT_SM_CT }}"
                                                                    data-url="{{ $bugEditUrl }}"
                                                                    data-ajax-popup="true"
                                                                    data-size="xl"
                                                                    data-bs-toggle="tooltip"
                                                                    title="{{ $bugEditTooltip }}"
                                                                    data-title="{{ $bugEditTitle }}"
                                                                    data-guard-msg="{{ base64_encode($bugEditGuardMsg) }}">
                                                                        <i class="{{ VC::TI_PC_WT }}"></i>
                                                                    </a>
                                                                </div>
                                                            @endcan
                                                            @can('delete bug report')
                                                                @php
                                                                    try {
                                                                        $bugDestroyBaseName     = ViewsConstants::PRJ_TSK_BUG.'.destroy';
                                                                        $bugDestroyKebabName    = Str::kebab($bugDestroyBaseName);
                                                                        $bugDestroyResolvedName = Route::has($bugDestroyBaseName)
                                                                            ? $bugDestroyBaseName
                                                                            : (Route::has($bugDestroyKebabName) ? $bugDestroyKebabName : null);
                                                                        $projectIdValue         = isset($projectId) && !empty($projectId) ? $projectId : (isset($bug) && !empty($bug->project_id) ? $bug->project_id : null);
                                                                        $bugObjectIdValue       = isset($bugObjectId) && !empty($bugObjectId) ? $bugObjectId : (isset($bug) && !empty($bug->id) ? $bug->id : null);
                                                                        $bugDestroyRouteArray   = ($bugDestroyResolvedName && $projectIdValue && $bugObjectIdValue) ? [$bugDestroyResolvedName, [$projectIdValue, $bugObjectIdValue]] : ['#'];
                                                                        $bugDestroyUrl          = ($bugDestroyResolvedName && $projectIdValue && $bugObjectIdValue) ? route($bugDestroyResolvedName, [$projectIdValue, $bugObjectIdValue]) : '#';
                                                                        $bugDestroyGuardMsg     = Utility::fetchLinkMessage($lang, ViewsConstants::PRJ_TSK_BUG, 'delete_bug_route_unavailable') ?? 'Delete bug route is unavailable. Please contact technical support or your domain administrator.';
                                                                        $bugDestroyFormId       = 'bug-destroy-form-'.($bugObjectIdValue ?? 'x');
                                                                        $bugDestroyLinkId       = 'bug-destroy-link-'.($bugObjectIdValue ?? 'x');
                                                                        $bugDestroyTitle        = __('Delete');
                                                                    } catch (\Throwable $e) {
                                                                        \Log::error('projects/bug — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                                    }
@endphp
                                                                <div class="{{ VC::ACT_BTN_DNG_2 }}">
                                                                    {!! Collective\Html\FormFacade::open([
                                                                        'method'         => 'DELETE',
                                                                        'route'          => $bugDestroyRouteArray,
                                                                        'id'             => $bugDestroyFormId,
                                                                        'data-url'       => $bugDestroyUrl,
                                                                        'data-guard-msg' => $bugDestroyGuardMsg
                                                                    ]) !!}
                                                                        @csrf
                                                                        <a href="#"
                                                                        data-route-guard
                                                                        class="{{ VC::BT_SM_CT_PR }}"
                                                                        data-form-id="{{ $bugDestroyFormId }}"
                                                                        data-url="{{ $bugDestroyUrl }}"
                                                                        data-guard-msg="{{ base64_encode($bugDestroyGuardMsg) }}"
                                                                        data-bs-toggle="tooltip"
                                                                        title="{{ $bugDestroyTitle }}">
                                                                            <i class="{{ VC::TI_TRS_WT }}"></i>
                                                                        </a>
                                                                    {!! Collective\Html\FormFacade::close() !!}
                                                                </div>
                                                            @endcan
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endif
                                        @endforeach
                                    @else
                                        <tr>
                                            <td colspan="9" class="{{ VC::TXCT }}">
                                                {{ __('No bugs found') }}
                                            </td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @push(StacksConstants::ADM_SCR_PG)
            <script>
            if (typeof window.BugTableHandler === 'undefined') {
                window.BugTableHandler = {
                    debounceMap: new Map(),

                    init() {
                        document.querySelectorAll('[data-route-guard]').forEach(el => this.attachClickHandler(el));
                    },

                    attachClickHandler(el) {
                        const elId = el.getAttribute('data-url') || el.getAttribute('data-form-id') || Math.random();
                        el.addEventListener('click', (e) => {
                            if (this.debounceMap.has(elId)) { e.preventDefault(); return; }

                            const formId = el.getAttribute('data-form-id');
                            if (formId) {
                                e.preventDefault();
                                const form = document.getElementById(formId);
                                if (!form) return;
                                const url = form.getAttribute('data-url') || '#';
                                const action = form.getAttribute('action') || '#';
                                if (url === '#' && action === '#') {
                                    this.showToast(el.getAttribute('data-guard-msg') || form.getAttribute('data-guard-msg') || 'Route unavailable');
                                    return;
                                }
                                form.submit();
                                return;
                            }

                            const href = el.getAttribute('href') || '#';
                            const url = el.getAttribute('data-url') || href || '#';
                            if (href !== '#' && url !== '#') return;
                            e.preventDefault();
                            this.showToast(el.getAttribute('data-guard-msg') || 'Route unavailable');
                            this.debounceMap.set(elId, true);
                            setTimeout(() => this.debounceMap.delete(elId), 800);
                        }, { passive: false });
                    },

                    showToast(msg) {
                        const container = document.getElementById('toast-container') || (() => {
                            const c = document.createElement('div');
                            c.id = 'toast-container';
                            c.className = 'position-fixed top-0 end-0 p-3';
                            document.body.appendChild(c);
                            return c;
                        })();
                        const toast = document.createElement('div');
                        toast.className = 'toast';
                        toast.setAttribute('role', 'alert');
                        toast.setAttribute('aria-live', 'assertive');
                        toast.setAttribute('aria-atomic', 'true');
                        toast.innerHTML = `<div class="toast-body">${msg}<button type="button" class="{{ VC::BT_CL }} {{ VC::MS2 }}" data-bs-dismiss="toast" aria-label="Close"></button></div>`;
                        container.appendChild(toast);
                        window.bootstrap?.Toast?.getOrCreateInstance(toast)?.show() || alert(msg);
                        toast.addEventListener('hidden.bs.toast', () => toast.remove());
                    }
                };
                document.readyState === 'loading' ? document.addEventListener('DOMContentLoaded', () => window.BugTableHandler.init()) : window.BugTableHandler.init();
            }
            </script>
        @endpush
    @endsection
@else
    <div class="{{ VC::ALT_WRN }}">{{ __('No Project Found') }}</div>
@endif
