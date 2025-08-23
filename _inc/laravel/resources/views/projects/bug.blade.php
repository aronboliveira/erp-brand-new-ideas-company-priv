@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        StacksConstants,
        YieldingConstants,
        ViewClassNamesConstants as VC
    };
    use Illuminate\Support\Facades\{Auth, Route};
    $user = Auth::user();
    $lang = Utility::fetchUserLang(user:$user);
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@if(isset($project) && !empty($project))
    @section(YieldingConstants::ADM_PG_TTL)
        {{__('Manage Bug Report')}}
    @endsection
    @push(StacksConstants::ADM_SCR_PG)
    @endpush
    @php
        $projectIndexBaseName = ViewsConstants::PRJ . '.index';
        $projectIndexKebabName = Str::kebab($projectIndexBaseName);
        $projectIndexResolvedName = Route::has($projectIndexBaseName) ? $projectIndexBaseName : (Route::has($projectIndexKebabName) ? $projectIndexKebabName : null);
        $projectIndexUrl = $projectIndexResolvedName ? route($projectIndexResolvedName) : '#';
        $projectIndexGuardMsg = Utility::fetchLinkMessage($lang, ViewsConstants::PRJ, 'project_index_route_unavailable') ?? 'Project index route is unavailable. Please contact technical support or your domain administrator.';
    @endphp
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
        <li class="breadcrumb-item">
            @php
                $projectShowBaseName     = ViewsConstants::PRJ.'.show';
                $projectShowKebabName    = Str::kebab($projectShowBaseName);
                $projectShowResolvedName = Route::has($projectShowBaseName)
                    ? $projectShowBaseName
                    : (Route::has($projectShowKebabName) ? $projectShowKebabName : null);
                $projectId               = isset($project) && !empty($project->id) ? $project->id : null;
                $projectShowUrl          = ($projectShowResolvedName && $projectId) ? route($projectShowResolvedName, $projectId) : '#';
                $projectShowGuardMsg     = Utility::fetchLinkMessage($lang, ViewsConstants::PRJ, 'show_project_route_unavailable') ?? 'Show project route is unavailable. Please contact technical support or your domain administrator.';
                $projectShowLinkId       = 'project-show-link';
                $projectNameText         = ($project->project_name ?? null) ? ucwords($project->project_name) : __('No name found for project');
            @endphp
            <a href="{{ $projectShowUrl }}"
            id="{{ $projectShowLinkId }}"
            data-url="{{ $projectShowUrl }}"
            data-guard-msg="{{ $projectShowGuardMsg }}">
                {{ $projectNameText }}
            </a>
            @push(StacksConstants::ADM_SCR_PG)
                <script defer>
                    (() => {
                        try {
                            const l = document.getElementById('{{ $projectShowLinkId }}');
                            if (!l || l.getAttribute('data-listener-active') === 'true') return;
                            l.setAttribute('data-listener-active', 'true');
                            l.addEventListener('click', e => {
                                try {
                                    const href = l.getAttribute('href') || '#';
                                    const url = l.getAttribute('data-url') || href || '#';
                                    if (href !== '#' || url !== '#') return;
                                    e.preventDefault();
                                    const msg = l.getAttribute('data-guard-msg') || 'Show project route is unavailable. Please contact technical support or your domain administrator.';
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
        </li>
        <li class="breadcrumb-item">{{__('Bug Report')}}</li>
    @endsection
    @section(YieldingConstants::ADM_ACT_BTN)
        <div class="float-end">
            @can('manage bug report')
                @php
                    $bugListBaseName     = ViewsConstants::PRJ_TSK_BUG;
                    $bugListKebabName    = Str::kebab($bugListBaseName);
                    $bugListResolvedName = Route::has($bugListBaseName)
                        ? $bugListBaseName
                        : (Route::has($bugListKebabName) ? $bugListKebabName : null);
                    $projectId           = isset($project) && !empty($project->id) ? $project->id : null;
                    $bugListUrl          = ($bugListResolvedName && $projectId) ? route($bugListResolvedName, $projectId) : '#';
                    $bugListGuardMsg     = Utility::fetchLinkMessage($lang, ViewsConstants::PRJ_TSK_BUG, 'list_bug_route_unavailable') ?? 'List bug route is unavailable. Please contact technical support or your domain administrator.';
                    $bugListLinkId       = 'bug-list-link-'.($projectId ?? 'x');
                    $bugListTitle        = __('List');
                @endphp
                <a href="{{ $bugListUrl }}"
                id="{{ $bugListLinkId }}"
                class="{{ VC::BT_SM_PM }}"
                data-url="{{ $bugListUrl }}"
                data-guard-msg="{{ $bugListGuardMsg }}"
                data-bs-toggle="tooltip"
                title="{{ $bugListTitle }}">
                    <i class="{{ VC::TI_LT }}"></i>
                </a>
                @push(StacksConstants::ADM_SCR_PG)
                    <script defer>
                        (() => {
                            try {
                                const l = document.getElementById('{{ $bugListLinkId }}');
                                if (!l || l.getAttribute('data-listener-active') === 'true') return;
                                l.setAttribute('data-listener-active', 'true');
                                l.addEventListener('click', e => {
                                    try {
                                        const href = l.getAttribute('href') || '#';
                                        const url = l.getAttribute('data-url') || href || '#';
                                        if (href !== '#' || url !== '#') return;
                                        e.preventDefault();
                                        const msg = l.getAttribute('data-guard-msg') || 'List bug route is unavailable. Please contact technical support or your domain administrator.';
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
            @endcan
            @can('create bug report')
                @php
                    $bugCreateBaseName     = ViewsConstants::PRJ_TSK_BUG.'.create';
                    $bugCreateKebabName    = Str::kebab($bugCreateBaseName);
                    $bugCreateResolvedName = Route::has($bugCreateBaseName)
                        ? $bugCreateBaseName
                        : (Route::has($bugCreateKebabName) ? $bugCreateKebabName : null);
                    $projectId             = isset($project) && !empty($project->id) ? $project->id : null;
                    $bugCreateUrl          = ($bugCreateResolvedName && $projectId) ? route($bugCreateResolvedName, $projectId) : '#';
                    $bugCreateGuardMsg     = Utility::fetchLinkMessage($lang, ViewsConstants::PRJ_TSK_BUG, 'create_bug_route_unavailable') ?? 'Create bug route is unavailable. Please contact technical support or your domain administrator.';
                    $bugCreateLinkId       = 'bug-create-link-'.($projectId ?? 'x');
                    $bugCreateTitle        = __('Create New Bug');
                @endphp
                <a href="{{ $bugCreateUrl }}"
                id="{{ $bugCreateLinkId }}"
                data-size="lg"
                data-url="{{ $bugCreateUrl }}"
                data-ajax-popup="true"
                data-guard-msg="{{ $bugCreateGuardMsg }}"
                data-bs-toggle="tooltip"
                data-title="{{ $bugCreateTitle }}"
                title="{{ $bugCreateTitle }}"
                class="{{ VC::BT_SM_PM }}">
                    <i class="{{ VC::TI_PLS }}"></i>
                </a>
                @push(StacksConstants::ADM_SCR_PG)
                    <script defer>
                        (() => {
                            try {
                                const l = document.getElementById('{{ $bugCreateLinkId }}');
                                if (!l || l.getAttribute('data-listener-active') === 'true') return;
                                l.setAttribute('data-listener-active', 'true');
                                l.addEventListener('click', e => {
                                    try {
                                        const href = l.getAttribute('href') || '#';
                                        const url = l.getAttribute('data-url') || href || '#';
                                        if (href !== '#' || url !== '#') return;
                                        e.preventDefault();
                                        const msg = l.getAttribute('data-guard-msg') || 'Create bug route is unavailable. Please contact technical support or your domain administrator.';
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
            @endcan
        </div>
    @endsection
    @section(YieldingConstants::ADM_CTT)
        <div class="row">
            <div class="col-xl-12">
                <div class="card">
                    <div class="card-body table-border-style">
                        <div class="table-responsive">
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
                                                                @endphp
                                                                <div class="{{ VC::ACT_BTN_INF }}">
                                                                    <a href="{{ $bugEditUrl }}"
                                                                    id="{{ $bugEditLinkId }}"
                                                                    class="{{ VC::BT_SM_CT }}"
                                                                    data-url="{{ $bugEditUrl }}"
                                                                    data-ajax-popup="true"
                                                                    data-size="xl"
                                                                    data-bs-toggle="tooltip"
                                                                    title="{{ $bugEditTooltip }}"
                                                                    data-title="{{ $bugEditTitle }}"
                                                                    data-guard-msg="{{ $bugEditGuardMsg }}">
                                                                        <i class="{{ VC::TI_PC_WT }}"></i>
                                                                    </a>
                                                                </div>
                                                                @push(StacksConstants::ADM_SCR_PG)
                                                                    <script defer>
                                                                        (() => {
                                                                            try {
                                                                                const l = document.getElementById('{{ $bugEditLinkId }}');
                                                                                if (!l || l.getAttribute('data-listener-active') === 'true') return;
                                                                                l.setAttribute('data-listener-active', 'true');
                                                                                l.addEventListener('click', e => {
                                                                                    try {
                                                                                        const href = l.getAttribute('href') || '#';
                                                                                        const url = l.getAttribute('data-url') || href || '#';
                                                                                        if (href !== '#' || url !== '#') return;
                                                                                        e.preventDefault();
                                                                                        const msg = l.getAttribute('data-guard-msg') || 'Edit bug route is unavailable. Please contact technical support or your domain administrator.';
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
                                                            @endcan
                                                            @can('delete bug report')
                                                                @php
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
                                                                        id="{{ $bugDestroyLinkId }}"
                                                                        class="{{ VC::BT_SM_CT_PR }}"
                                                                        data-form-id="{{ $bugDestroyFormId }}"
                                                                        data-url="{{ $bugDestroyUrl }}"
                                                                        data-guard-msg="{{ $bugDestroyGuardMsg }}"
                                                                        data-bs-toggle="tooltip"
                                                                        title="{{ $bugDestroyTitle }}">
                                                                            <i class="{{ VC::TI_TRS_WT }}"></i>
                                                                        </a>
                                                                    {!! Collective\Html\FormFacade::close() !!}
                                                                </div>
                                                                @push(StacksConstants::ADM_SCR_PG)
                                                                    <script defer>
                                                                        (() => {
                                                                            try {
                                                                                const l = document.getElementById('{{ $bugDestroyLinkId }}');
                                                                                if (!l || l.getAttribute('data-listener-active') === 'true') return;
                                                                                l.setAttribute('data-listener-active', 'true');
                                                                                l.addEventListener('click', e => {
                                                                                    try {
                                                                                        e.preventDefault();
                                                                                        const formId = l.getAttribute('data-form-id') || '';
                                                                                        const f = formId ? document.getElementById(formId) : null;
                                                                                        if (!f) return;
                                                                                        const url = f.getAttribute('data-url') || '#';
                                                                                        const action = f.getAttribute('action') || '#';
                                                                                        if (url === '#' && action === '#') {
                                                                                            const msg = l.getAttribute('data-guard-msg') || f.getAttribute('data-guard-msg') || 'Delete bug route is unavailable. Please contact technical support or your domain administrator.';
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
                                                                                            f.setAttribute('data-failed-route', 'true');
                                                                                            return;
                                                                                        }
                                                                                        f.submit();
                                                                                    } catch (err) {}
                                                                                });
                                                                            } catch (error) {}
                                                                        })();
                                                                    </script>
                                                                @endpush
                                                            @endcan
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endif
                                        @endforeach
                                    @else
                                        <tr>
                                            <td colspan="9" class="text-center">
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
    @endsection
@else
    <div class="alert alert-warning">{{ __('No Project Found') }}</div>
@endif
