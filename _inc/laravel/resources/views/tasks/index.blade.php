@php
    try {
$user = Auth::user();
        $lang = Utility::fetchUserLang(user: $user);
    } catch (\Throwable $e) {
        \Log::error('tasks/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    }
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@if(!empty($project) && isset($project))
    @section(YieldingConstants::ADM_PG_TTL)
        {{!empty($project->name) ? $project->name.__("'s Tasks") : __('No project name available')}}
    @endsection

    @push('theme-script')
        <script src="{{ asset('assets/libs/dragula/dist/dragula.min.js') }}"></script>
    @endpush

    @section(YieldingConstants::ADM_ACT_BTN)
        @php
            try {
                $projectIndexBase = VW::PRJ.'.index';
                $projectIndexKebab = Str::kebab($projectIndexBase);
                $projectIndexResolved = Route::has($projectIndexBase) ? $projectIndexBase : (Route::has($projectIndexKebab) ? $projectIndexKebab : null);
                $projectIndexUrl = $projectIndexResolved ? route($projectIndexResolved) : '#';
                $langValue = isset($lang) ? $lang : Utility::fetchUserLang();
                $projectIndexGuardMsg = Utility::fetchLinkMessage($langValue, VW::PRJ, 'project_index_route_unavailable') ?? 'Project index route is unavailable. Please contact technical support or your domain administrator.';
                $projectIndexAnchorId = 'project-index-back-btn';
            } catch (\Throwable $e) {
                \Log::error('tasks/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
            }
@endphp
        <a id="{{ $projectIndexAnchorId }}"
        href="{{ $projectIndexUrl }}"
        class="{{ VC::BT_XS }} btn-white btn-icon-only width-auto"
        data-url="{{ $projectIndexUrl }}"
        data-guard-msg="{{ base64_encode($projectIndexGuardMsg) }}"
        data-sv-localized="true"
        data-bs-toggle="tooltip"
        title="{{ __('Back') }}">
            <span class="btn-inner--icon"><i class="ti ti-arrow-left"></i>{{ __('Back') }}</span>
        </a>
        @push(StacksConstants::ADM_SCR_PG)
            <script defer src="{{ asset('assets/js/routes/tasks/project.js') }}"></script>
        @endpush
    @endsection

    @php
        $permissions = !empty($project->id) ? $user?->getPermission($project->id) : [];
@endphp

    @section(YieldingConstants::ADM_CTT)
        <div class="{{ VC::CD }} overflow-hidden">
            <div class="container-kanban">
                <div class="kanban-board min-750" @if(is_array($permissions ?? null) && in_array('move task',$permissions)) data-plugin="dragula" @endif data-containers='{{ json_encode($stageClass ?? []) }}'>
                    @forelse((($stages ?? null) instanceof Collection || is_array($stages ?? null)) ? $stages : [] as $stage)
                        <div class="kanban-col px-0">
                            <div class="card-list card-list-flush">
                                <div class="card-list-title {{ VC::R_ALC }} {{ VC::MB3 }}">
                                    <div class="col">
                                        <h6 class="{{ VC::MB0 }}">{{ data_get($stage,'name') ?: __('No stage name available') }}</h6>
                                    </div>
                                    @if(is_array($permissions ?? null) && in_array('create task',$permissions))
                                        <div class="col {{ VC::TX_END }}">
                                            <div class="actions">
                                                @php
                                                    try {
                                                        $taskCreateBase = VW::PRJ_TSK_C.'.create';
                                                        $taskCreateKebab = Str::kebab($taskCreateBase);
                                                        $taskCreateResolved = Route::has($taskCreateBase) ? $taskCreateBase : (Route::has($taskCreateKebab) ? $taskCreateKebab : null);
                                                        $projIdValue = data_get($stage,'project_id','');
                                                        $stageIdValue = data_get($stage,'id','');
                                                        $taskCreateUrl = ($taskCreateResolved && $projIdValue !== '' && $stageIdValue !== '') ? route($taskCreateResolved, [$projIdValue, $stageIdValue]) : '#';
                                                        $langValue = isset($lang) ? $lang : Utility::fetchUserLang();
                                                        $taskCreateGuardMsg = Utility::fetchLinkMessage($langValue, VW::PRJ_TSK_C, 'create_project_task_route_unavailable') ?? 'Create project task route is unavailable. Please contact technical support or your domain administrator.';
                                                        $taskCreateAnchorId = 'project-task-create-'.($projIdValue === '' ? 'x' : $projIdValue).'-'.($stageIdValue === '' ? 'y' : $stageIdValue);
                                                        $taskCreateTitle = __('Add Task in ').(data_get($stage,'name') ?: __('this stage'));
                                                    } catch (\Throwable $e) {
                                                        \Log::error('tasks/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                    }
@endphp
                                                <a id="{{ $taskCreateAnchorId }}"
                                                class="action-item {{ VC::MR2 }}"
                                                href="{{ $taskCreateUrl }}"
                                                data-url="{{ $taskCreateUrl }}"
                                                data-ajax-popup="true"
                                                data-size="lg"
                                                data-title="{{ $taskCreateTitle }}"
                                                data-guard-msg="{{ base64_encode($taskCreateGuardMsg) }}"
                                                data-sv-localized="true"
                                                data-bs-toggle="tooltip"
                                                title="{{ __('Add Task') }}">
                                                    <i class="{{ VC::TI_PLS }}"></i>
                                                </a>
                                                @push(StacksConstants::ADM_SCR_PG)
                                                    <script defer>
                                                        (() => {
                                                            try {
                                                                const el = document.getElementById('{{ $taskCreateAnchorId }}');
                                                                if (!el) { return; }
                                                                if (el.getAttribute('data-listener-active') === 'true') { return; }
                                                                el.setAttribute('data-listener-active','true');
                                                                el.addEventListener('click',(e) => {
                                                                    try {
                                                                        const href = el.getAttribute('href') ?? '#';
                                                                        const url = el.getAttribute('data-url') ?? href ?? '#';
                                                                        if (url !== '#' && href !== '#') { return; }
                                                                        e.preventDefault();
                                                                        const msg = el.getAttribute('data-guard-msg') ?? 'Create project task route is unavailable. Please contact technical support or your domain administrator.';
                                                                        (window.RouteGuard?.showToast || (m => alert(m)))(msg);
                                                                        el.setAttribute('data-failed-route','true');
                                                                    } catch (err) {}
                                                                });
                                                            } catch (err) {}
                                                        })();
                                                    </script>
                                                @endpush
                                            </div>
                                        </div>
                                    @endif
                                </div>
                                <div class="card-list-body task-list-items" id="task-list-{{ data_get($stage,'id','') }}" data-status="{{ data_get($stage,'id','') }}">
                                    @php
                                        $__tasks = (is_object($stage) || is_array($stage)) ? (data_get($stage,'tasks',[]) ?: []) : [];
@endphp
                                    @forelse((($__tasks instanceof Collection) || is_array($__tasks)) ? $__tasks : [] as $taskDetail)
                                        @php
                                            try {
                                                $__tid = (string) data_get($taskDetail,'id','');
                                                $__pIdx = data_get($taskDetail,'priority');
                                                $__pColors = ProjectTask::$priority_color ?? [];
                                                $__pColor = $__pColors[$__pIdx] ?? 'secondary';
                                                $__pLabels = ProjectTask::$priority ?? [];
                                                $__pLabel = isset($__pLabels[$__pIdx]) ? __($__pLabels[$__pIdx]) : __('No priority available');
                                                $__prog = (is_object($taskDetail) && method_exists($taskDetail,'taskProgress')) ? ($taskDetail->taskProgress($taskDetail) ?? []) : [];
                                                $__percent = (string) ($__prog['percentage'] ?? '0%');
                                                $__percentInt = (int) str_replace('%','',$__percent);
                                                $__pColorBar = (string) ($__prog['color'] ?? 'secondary');
                                                $__files = data_get($taskDetail,'taskFiles');
                                                $__filesCount = is_countable($__files) ? count($__files) : 0;
                                                $__comments = data_get($taskDetail,'comments');
                                                $__commentsCount = is_countable($__comments) ? count($__comments) : 0;
                                                $__checkCount = (is_object($taskDetail) && method_exists($taskDetail,'countTaskChecklist')) ? (int) $taskDetail->countTaskChecklist() : ((is_object(data_get($taskDetail,'checklist')) && method_exists(data_get($taskDetail,'checklist'),'count')) ? (int) data_get($taskDetail,'checklist')->count() : 0);
                                                $__end = (string) (data_get($taskDetail,'end_date') ?? '');
                                                $__overdue = !empty($__end) && $__end !== '0000-00-00' && (strtotime($__end) < time());
                                                $__canMove = is_array($permissions ?? null) && in_array('move task',$permissions);
                                            } catch (\Throwable $e) {
                                                \Log::error('tasks/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                            }
@endphp
                                        <div class="{{ VC::CD }} card-progress @if($__canMove) draggable-item @endif {{ VC::BD }} {{ VC::SNN }}" id="{{ $__tid }}" style="{{ data_get($taskDetail,'priority_color') ? 'border-left: 2px solid '.data_get($taskDetail,'priority_color').' !important' : '' }};">
                                            <div class="{{ VC::CD_BD }}">
                                                <div class="{{ VC::R_ALC }} mb-2">
                                                    <div class="{{ VC::C6 }}">
                                                        <span class="{{ VC::BDG_XS }} badge-pill badge-{{ $__pColor }}">{{ $__pLabel }}</span>
                                                    </div>
                                                    <div class="{{ VC::C6 }} {{ VC::TX_END }}">
                                                        @if($__percentInt > 0)
                                                            <span class="{{ VC::TXSM }}">{{ $__percent }}</span>
                                                        @endif
                                                        @if(is_array($permissions ?? null) && (in_array('show task',$permissions) || in_array('edit task',$permissions) || in_array('delete task',$permissions)))
                                                            <div class="dropdown action-item">
                                                                <a href="#" class="action-item" role="button" data-toggle="dropdown"><i class="ti ti-ellipsis-h"></i></a>
                                                                <div class="{{ VC::DRP_MN_END }}">
                                                                    @if(in_array('show task',$permissions ?? []))
                                                                        @php
                                                                            try {
                                                                                $taskShowBase = VW::PRJ_TSK_C.'.show';
                                                                                $taskShowKebab = Str::kebab($taskShowBase);
                                                                                $taskShowResolved = Route::has($taskShowBase) ? $taskShowBase : (Route::has($taskShowKebab) ? $taskShowKebab : null);
                                                                                $projIdValue = data_get($project,'id','');
                                                                                $taskIdValue = isset($__tid) ? $__tid : '';
                                                                                $taskShowUrl = ($taskShowResolved && $projIdValue !== '' && $taskIdValue !== '') ? route($taskShowResolved, [$projIdValue, $taskIdValue]) : '#';
                                                                                $langValue = isset($lang) ? $lang : Utility::fetchUserLang();
                                                                                $taskShowGuardMsg = Utility::fetchLinkMessage($langValue, VW::PRJ_TSK_C, 'show_project_task_route_unavailable') ?? 'Show project task route is unavailable. Please contact technical support or your domain administrator.';
                                                                                $taskShowAnchorId = 'project-task-show-'.($projIdValue === '' ? 'x' : $projIdValue).'-'.($taskIdValue === '' ? 'y' : $taskIdValue);
                                                                            } catch (\Throwable $e) {
                                                                                \Log::error('tasks/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                                            }
@endphp
                                                                        <a id="{{ $taskShowAnchorId }}"
                                                                        href="{{ $taskShowUrl }}"
                                                                        class="{{ VC::DRP_IT }}"
                                                                        data-url="{{ $taskShowUrl }}"
                                                                        data-ajax-popup-right="true"
                                                                        data-guard-msg="{{ base64_encode($taskShowGuardMsg) }}"
                                                                        data-sv-localized="true">
                                                                            {{ __('View') }}
                                                                        </a>
                                                                        @push(StacksConstants::ADM_SCR_PG)
                                                                            <script defer>
                                                                                (() => {
                                                                                    try {
                                                                                        const el = document.getElementById('{{ $taskShowAnchorId }}');
                                                                                        if (!el) { return; }
                                                                                        if (el.getAttribute('data-listener-active') === 'true') { return; }
                                                                                        el.setAttribute('data-listener-active', 'true');
                                                                                        el.addEventListener('click', (e) => {
                                                                                            try {
                                                                                                const href = el.getAttribute('href') ?? '#';
                                                                                                const url = el.getAttribute('data-url') ?? href ?? '#';
                                                                                                if (url !== '#' && href !== '#') { return; }
                                                                                                e.preventDefault();
                                                                                                const msg = el.getAttribute('data-guard-msg') ?? 'Show project task route is unavailable. Please contact technical support or your domain administrator.';
                                                                                                (window.RouteGuard?.showToast || (m => alert(m)))(msg);
                                                                                                el.setAttribute('data-failed-route', 'true');
                                                                                            } catch (err) {}
                                                                                        });
                                                                                    } catch (err) {}
                                                                                })();
                                                                            </script>
                                                                        @endpush
                                                                    @endif
                                                                    @if(in_array('edit task',$permissions ?? []))
                                                                        @php
                                                                            try {
                                                                                $taskEditBase = VW::PRJ_TSK_C.'.edit';
                                                                                $taskEditKebab = Str::kebab($taskEditBase);
                                                                                $taskEditResolved = Route::has($taskEditBase) ? $taskEditBase : (Route::has($taskEditKebab) ? $taskEditKebab : null);
                                                                                $projIdValue = data_get($project,'id','');
                                                                                $taskIdValue = isset($__tid) ? $__tid : '';
                                                                                $taskEditUrl = ($taskEditResolved && $projIdValue !== '' && $taskIdValue !== '') ? route($taskEditResolved, [$projIdValue, $taskIdValue]) : '#';
                                                                                $langValue = isset($lang) ? $lang : Utility::fetchUserLang();
                                                                                $taskEditGuardMsg = Utility::fetchLinkMessage($langValue, VW::PRJ_TSK_C, 'edit_project_task_route_unavailable') ?? 'Edit project task route is unavailable. Please contact technical support or your domain administrator.';
                                                                                $taskEditAnchorId = 'project-task-edit-'.($projIdValue === '' ? 'x' : $projIdValue).'-'.($taskIdValue === '' ? 'y' : $taskIdValue);
                                                                                $taskEditTitle = __('Edit ').(data_get($taskDetail,'name') ?: __('task'));
                                                                            } catch (\Throwable $e) {
                                                                                \Log::error('tasks/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                                            }
@endphp
                                                                        <a id="{{ $taskEditAnchorId }}"
                                                                        href="{{ $taskEditUrl }}"
                                                                        class="{{ VC::DRP_IT }}"
                                                                        data-url="{{ $taskEditUrl }}"
                                                                        data-ajax-popup="true"
                                                                        data-size="lg"
                                                                        data-title="{{ $taskEditTitle }}"
                                                                        data-guard-msg="{{ base64_encode($taskEditGuardMsg) }}"
                                                                        data-sv-localized="true">
                                                                            {{ __('Edit') }}
                                                                        </a>
                                                                        @push(StacksConstants::ADM_SCR_PG)
                                                                            <script defer>
                                                                                (() => {
                                                                                    try {
                                                                                        const el = document.getElementById('{{ $taskEditAnchorId }}');
                                                                                        if (!el) { return; }
                                                                                        if (el.getAttribute('data-listener-active') === 'true') { return; }
                                                                                        el.setAttribute('data-listener-active','true');
                                                                                        el.addEventListener('click',(e) => {
                                                                                            try {
                                                                                                const href = el.getAttribute('href') ?? '#';
                                                                                                const url = el.getAttribute('data-url') ?? href ?? '#';
                                                                                                if (url !== '#' && href !== '#') { return; }
                                                                                                e.preventDefault();
                                                                                                const msg = el.getAttribute('data-guard-msg') ?? 'Edit project task route is unavailable. Please contact technical support or your domain administrator.';
                                                                                                (window.RouteGuard?.showToast || (m => alert(m)))(msg);
                                                                                                el.setAttribute('data-failed-route','true');
                                                                                            } catch (err) {}
                                                                                        });
                                                                                    } catch (err) {}
                                                                                })();
                                                                            </script>
                                                                        @endpush
                                                                    @endif
                                                                    @if(in_array('delete task',$permissions ?? []))
                                                                        @php
                                                                            try {
                                                                                $taskDestroyBase = VW::PRJ_TSK_C.'.destroy';
                                                                                $taskDestroyKebab = Str::kebab($taskDestroyBase);
                                                                                $taskDestroyResolved = Route::has($taskDestroyBase) ? $taskDestroyBase : (Route::has($taskDestroyKebab) ? $taskDestroyKebab : null);
                                                                                $projIdValue = data_get($project,'id','');
                                                                                $taskIdValue = isset($__tid) ? $__tid : '';
                                                                                $taskDestroyUrl = ($taskDestroyResolved && $projIdValue !== '' && $taskIdValue !== '') ? route($taskDestroyResolved, [$projIdValue, $taskIdValue]) : '#';
                                                                                $langValue = isset($lang) ? $lang : Utility::fetchUserLang();
                                                                                $taskDestroyGuardMsg = Utility::fetchLinkMessage($langValue, VW::PRJ_TSK_C, 'destroy_project_task_route_unavailable') ?? 'Destroy project task route is unavailable. Please contact technical support or your domain administrator.';
                                                                                $taskDestroyAnchorId = 'project-task-destroy-'.($projIdValue === '' ? 'x' : $projIdValue).'-'.($taskIdValue === '' ? 'y' : $taskIdValue);
                                                                            } catch (\Throwable $e) {
                                                                                \Log::error('tasks/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                                            }
@endphp
                                                                        <a id="{{ $taskDestroyAnchorId }}"
                                                                        href="{{ $taskDestroyUrl }}"
                                                                        class="{{ VC::DRP_IT }} del_task"
                                                                        data-url="{{ $taskDestroyUrl }}"
                                                                        data-guard-msg="{{ base64_encode($taskDestroyGuardMsg) }}"
                                                                        data-sv-localized="true">
                                                                            {{ __('Delete') }}
                                                                        </a>
                                                                        @push(StacksConstants::ADM_SCR_PG)
                                                                            <script defer>
                                                                                (() => {
                                                                                    try {
                                                                                        const el = document.getElementById('{{ $taskDestroyAnchorId }}');
                                                                                        if (!el) { return; }
                                                                                        if (el.getAttribute('data-listener-active') === 'true') { return; }
                                                                                        el.setAttribute('data-listener-active','true');
                                                                                        el.addEventListener('click',(e) => {
                                                                                            try {
                                                                                                const href = el.getAttribute('href') ?? '#';
                                                                                                const url = el.getAttribute('data-url') ?? href ?? '#';
                                                                                                if (url !== '#' && href !== '#') { return; }
                                                                                                e.preventDefault();
                                                                                                const msg = el.getAttribute('data-guard-msg') ?? 'Destroy project task route is unavailable. Please contact technical support or your domain administrator.';
                                                                                                (window.RouteGuard?.showToast || (m => alert(m)))(msg);
                                                                                                el.setAttribute('data-failed-route','true');
                                                                                            } catch (err) {}
                                                                                        });
                                                                                    } catch (err) {}
                                                                                })();
                                                                            </script>
                                                                        @endpush
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                                @if(is_array($permissions ?? null) && in_array('show task',$permissions))
                                                    @php
                                                        try {
                                                            $taskShowBase = VW::PRJ_TSK_C.'.show';
                                                            $taskShowKebab = Str::kebab($taskShowBase);
                                                            $taskShowResolved = Route::has($taskShowBase) ? $taskShowBase : (Route::has($taskShowKebab) ? $taskShowKebab : null);
                                                            $projIdValue = data_get($project,'id','');
                                                            $taskIdValue = isset($__tid) ? $__tid : '';
                                                            $taskShowUrl = ($taskShowResolved && $projIdValue !== '' && $taskIdValue !== '') ? route($taskShowResolved, [$projIdValue, $taskIdValue]) : '#';
                                                            $langValue = isset($lang) ? $lang : Utility::fetchUserLang();
                                                            $taskShowGuardMsg = Utility::fetchLinkMessage($langValue, VW::PRJ_TSK_C, 'show_project_task_route_unavailable') ?? 'Show project task route is unavailable. Please contact technical support or your domain administrator.';
                                                            $taskShowAnchorId = 'project-task-show-'.($projIdValue === '' ? 'x' : $projIdValue).'-'.($taskIdValue === '' ? 'y' : $taskIdValue);
                                                            $taskNameLabel = data_get($taskDetail,'name') ?: __('No task name available');
                                                        } catch (\Throwable $e) {
                                                            \Log::error('tasks/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                        }
@endphp
                                                    <a id="{{ $taskShowAnchorId }}"
                                                    class="h6 task-name-break"
                                                    href="{{ $taskShowUrl }}"
                                                    data-url="{{ $taskShowUrl }}"
                                                    data-ajax-popup-right="true"
                                                    data-guard-msg="{{ base64_encode($taskShowGuardMsg) }}"
                                                    data-sv-localized="true">
                                                        {{ $taskNameLabel }}
                                                    </a>
                                                    @push(StacksConstants::ADM_SCR_PG)
                                                        <script defer>
                                                            (() => {
                                                                try {
                                                                    const el = document.getElementById('{{ $taskShowAnchorId }}');
                                                                    if (!el) { return; }
                                                                    if (el.getAttribute('data-listener-active') === 'true') { return; }
                                                                    el.setAttribute('data-listener-active','true');
                                                                    el.addEventListener('click',(e) => {
                                                                        try {
                                                                            const href = el.getAttribute('href') ?? '#';
                                                                            const url = el.getAttribute('data-url') ?? href ?? '#';
                                                                            if (url !== '#' && href !== '#') { return; }
                                                                            e.preventDefault();
                                                                            const msg = el.getAttribute('data-guard-msg') ?? 'Show project task route is unavailable. Please contact technical support or your domain administrator.';
                                                                            (window.RouteGuard?.showToast || (m => alert(m)))(msg);
                                                                            el.setAttribute('data-failed-route','true');
                                                                        } catch (err) {}
                                                                    });
                                                                } catch (err) {}
                                                            })();
                                                        </script>
                                                    @endpush
                                                @else
                                                    <a class="h6 task-name-break" href="#">{{ data_get($taskDetail,'name') ?: __('No task name available') }}</a>
                                                @endif
                                                <div class="{{ VC::R_ALC }}">
                                                    <div class="{{ VC::C12 }}">
                                                        <div class="actions d-inline-block">
                                                            @if($__filesCount > 0)
                                                                <div class="action-item {{ VC::MR2 }}"><i class="ti ti-paperclip {{ VC::MR2 }}"></i>{{ $__filesCount }}</div>
                                                            @endif
                                                            @if($__commentsCount > 0)
                                                                <div class="action-item {{ VC::MR2 }}"><i class="ti ti-brand-hipchart {{ VC::MR2 }}"></i>{{ $__commentsCount }}</div>
                                                            @endif
                                                            @if($__checkCount > 0)
                                                                <div class="action-item {{ VC::MR2 }}"><i class="ti ti-tasks {{ VC::MR2 }}"></i>{{ $__checkCount }}</div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                    <div class="col-5">
                                                        @if(!empty($__end) && $__end !== '0000-00-00')
                                                            <small @if($__overdue) class="{{ VC::TX_DNG }}" @endif>{{ Utility::getDateFormated($__end) }}</small>
                                                        @endif
                                                    </div>
                                                    <div class="col-7 {{ VC::TX_END }}">
                                                        @php
                                                            $__assignees = (is_object($taskDetail) && method_exists($taskDetail,'users')) ? ($taskDetail->users() ?? []) : [];
                                                            $__assignees = ($__assignees instanceof Collection || is_array($__assignees)) ? $__assignees : [];
@endphp
                                                        @if(!empty($__assignees))
                                                            <div class="avatar-group">
                                                                @foreach($__assignees as $idx => $u)
                                                                    @if($idx < 3)
                                                                        <a href="#" class="{{ VC::AV_CC_SM }}">
                                                                            <img data-original-title="{{ data_get($u,'name') ?: '' }}" src="{{ data_get($u,'avatar') ? asset('/storage/uploads/avatar/'.data_get($u,'avatar')) : asset('/storage/uploads/avatar/avatar.png') }}" style="height:36px;width:36px;">
                                                                        </a>
                                                                    @else
                                                                        @break
                                                                    @endif
                                                                @endforeach
                                                                @if(count($__assignees) > 3)
                                                                    @php
 $__last = $__assignees[2] ?? null;
@endphp
                                                                    <a href="#" class="{{ VC::AV_CC_SM }}">
                                                                        <img data-original-title="{{ data_get($__last,'name') ?: '' }}" src="{{ data_get($__last,'avatar') ? asset('/storage/uploads/avatar/'.data_get($__last,'avatar')) : asset('/storage/uploads/avatar/avatar.png') }}">
                                                                    </a>
                                                                @endif
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @empty
                                        <span class="empty-container" data-placeholder="{{ __('Empty') }}"></span>
                                    @endforelse
                                    <span class="empty-container" data-placeholder="{{ __('Empty') }}"></span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="kanban-col px-0"><div class="card-list card-list-flush"><div class="card-list-title"><h6 class="{{ VC::MB0 }}">{{ __('No stages available') }}</h6></div></div></div>
                    @endforelse
                </div>
            </div>
        </div>
    @endsection

    @push(StacksConstants::ADM_SCR_PG)
        <script src="{{ asset('assets/libs/autosize/dist/autosize.min.js') }}"></script>
        <script src="{{ asset('assets/js/colorPick.js') }}"></script>
        <script async src="{{ asset('assets/js/routes/tasks/lang/drag.js') }}"></script>
        <script defer>
            (function () {
                const $ = window.jQuery;
                const errFb = "# ERROR";
                const dataClientLocalized = "data-client-localized";
                const dataGuardMsg = "data-guard-msg";
                const dataSvLocalized = "data-sv-localized";
                const dataErrGuard = "data-error-guard";
                const dataBound = "data-bound-";
                const now = "{{__('Now')}}";
                const ensureToastContainer = () => {
                    let c = document.getElementById("np-toast-container");
                    if (c) {
                    return c;
                    }
                    c = document.createElement("div");
                    c.id = "np-toast-container";
                    c.setAttribute("aria-live", "polite");
                    c.setAttribute("aria-atomic", "true");
                    c.style.position = "fixed";
                    c.style.top = "1rem";
                    c.style.right = "1rem";
                    document.body.appendChild(c);
                    return c;
                };
                const showErrorNow = message => {
                    const hasBootstrap =
                    (document.querySelector('link[rel="stylesheet"][href*="bootstrap"]') ||
                        document.querySelector('link[href*="bootstrap"]')) &&
                    window.bootstrap &&
                    window.bootstrap.Toast;
                    if (hasBootstrap) {
                    const container = ensureToastContainer();
                    let t = document.getElementById("np-toast");
                    if (!t) {
                        t = document.createElement("div");
                        t.id = "np-toast";
                        t.className = "toast";
                        t.setAttribute("role", "alert");
                        t.setAttribute("aria-live", "assertive");
                        t.setAttribute("aria-atomic", "true");
                        t.innerHTML =
                        '<div class="toast-header"><strong class="me-auto">{{ __('Notice') }}</strong><button type="button" class="{{ VC::BT_CL }}" data-bs-dismiss="toast" aria-label="{{ __('Close') }}"></button></div><div class="toast-body"></div>';
                        container.appendChild(t);
                    }
                    const body = t.querySelector(".toast-body");
                    if (body) {
                        body.textContent = message ?? errFb;
                    }
                    try {
                        new window.bootstrap.Toast(t, { autohide: true, delay: 4000 }).show();
                    } catch (_) {
                        alert(message ?? errFb);
                    }
                    } else {
                    alert(message ?? errFb);
                    }
                };
                const scheduleInteractiveError = message => {
                    const host = document.body;
                    if (!host || host.getAttribute(dataErrGuard) === "true") {
                    return;
                    }
                    host.setAttribute(dataErrGuard, "true");
                    const once = () => {
                    try {
                        showErrorNow(message);
                    } finally {
                        host.removeAttribute(dataErrGuard);
                    }
                    };
                    document.addEventListener("pointerup", once, { once: true });
                    const mo = new MutationObserver((m, o) => {
                    if (!document.body.contains(host)) {
                        document.removeEventListener("pointerup", once);
                        o.disconnect();
                    }
                    });
                    mo.observe(document.documentElement, { childList: true, subtree: true });
                };
                const getMsg = (el, key) => {
                    let msg = errFb;
                    if (
                    el?.getAttribute(dataSvLocalized) === "true" ||
                    el?.getAttribute(dataClientLocalized) === "true"
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
                    const msgKey = key;
                    msg =
                        window.translations?.[lang]?.[msgKey] ||
                        el?.getAttribute(dataGuardMsg) ||
                        window.translations?.en?.[msgKey] ||
                        errFb;
                    if (el && msg !== errFb) {
                        el.setAttribute(dataGuardMsg, msg);
                        el.setAttribute(dataClientLocalized, "true");
                    }
                    }
                    return msg;
                };
                const resolveUrl = (el, explicit) => {
                    const url = el?.getAttribute?.("data-url") || "";
                    const href = el
                    ? el.tagName === "FORM"
                        ? el.getAttribute("action") || ""
                        : el.getAttribute("href") || ""
                    : "";
                    if (
                    (!explicit || explicit === "#") &&
                    (!url || url === "#") &&
                    (!href || href === "#")
                    ) {
                    scheduleInteractiveError(getMsg(el || document.body, "ajax_unavailable"));
                    return null;
                    }
                    return explicit && explicit !== "#"
                    ? explicit
                    : url && url !== "#"
                    ? url
                    : href;
                };
                const ajaxPost = (endpoint, data, onSuccess, elForMsg, msgKey) => {
                    const url = endpoint || "";
                    if (!url) {
                    scheduleInteractiveError(
                        getMsg(elForMsg || document.body, msgKey || "ajax_unavailable")
                    );
                    return;
                    }
                    $.ajax({
                    url: url,
                    type: "POST",
                    data: data || {},
                    cache: false,
                    success: function (d) {
                        if (typeof onSuccess === "function") {
                        onSuccess(d);
                        }
                    },
                    error: function () {
                        scheduleInteractiveError(
                        getMsg(elForMsg || document.body, msgKey || "ajax_unavailable")
                        );
                    },
                    });
                };
                const ajaxDelete = (endpoint, onSuccess, elForMsg, msgKey) => {
                    const url = endpoint || "";
                    if (!url) {
                    scheduleInteractiveError(
                        getMsg(elForMsg || document.body, msgKey || "ajax_unavailable")
                    );
                    return;
                    }
                    $.ajax({
                    url: url,
                    type: "DELETE",
                    dataType: "JSON",
                    cache: false,
                    success: function (d) {
                        if (typeof onSuccess === "function") {
                        onSuccess(d);
                        }
                    },
                    error: function () {
                        scheduleInteractiveError(
                        getMsg(elForMsg || document.body, msgKey || "ajax_unavailable")
                        );
                    },
                    });
                };
                const initDragula = () => {
                    if (!window.dragula) {
                    try {
                        if (
                            window.location.hostname === "localhost" ||
                            window.location.hostname === "127.0.0.1"
                        ) {
                            console.error("Dragula unavailable");
                        }
                    } catch (_) {}
                    scheduleInteractiveError(getMsg(document.body, "plugin_unavailable"));
                    return;
                    }
                    $('[data-plugin="dragula"]').each(function () {
                    const $host = $(this);
                    const containers = $host.data("containers");
                    const nodes = [];
                    if (containers && containers.length) {
                        for (let i = 0; i < containers.length; i++) {
                        const n = document.getElementById(containers[i]);
                        if (n) {
                            nodes.push(n);
                        }
                        }
                    } else {
                        nodes.push(this);
                    }
                    const handleClass = $host.data("handleclass");
                    const drake = handleClass
                        ? window.dragula(nodes, {
                            moves: function (el, c, handle) {
                            return (
                                handle &&
                                handle.classList &&
                                handle.classList.contains(handleClass)
                            );
                            },
                        })
                        : window.dragula(nodes);
                    drake.on("drop", function (el, target, source) {
                        try {
                        if (!target || !source || !el) {
                            scheduleInteractiveError(getMsg(document.body, "drag_unavailable"));
                            return;
                        }
                        const sort = [];
                        $("#" + target.id + " > div").each(function () {
                            sort[$(this).index()] = $(this).attr("id");
                        });
                        const id = el.id;
                        const old_stage = $("#" + source.id).data("status");
                        const new_stage = $("#" + target.id).data("status");
                        const project_id = "{{$project->id}}";
                        $("#" + source.id)
                            .parent()
                            .find(".count")
                            .text($("#" + source.id + " > div").length);
                        $("#" + target.id)
                            .parent()
                            .find(".count")
                            .text($("#" + target.id + " > div").length);
                        const explicit = "{{route(VW::PRJ . '.tasks.update.order',[$project->id])}}";
                        const endpoint = resolveUrl(target, explicit);
                        if (!endpoint) {
                            scheduleInteractiveError(
                            getMsg(target, "update_order_unavailable")
                            );
                            return;
                        }
                        $.ajax({
                            url: endpoint,
                            type: "PATCH",
                            data: {
                            id: id,
                            sort: sort,
                            new_stage: new_stage,
                            old_stage: old_stage,
                            project_id: project_id,
                            },
                            cache: false,
                            success: function () {},
                            error: function () {
                            scheduleInteractiveError(
                                getMsg(target, "update_order_unavailable")
                            );
                            },
                        });
                        } catch (_) {
                        scheduleInteractiveError(
                            getMsg(document.body, "update_order_unavailable")
                        );
                        }
                    });
                    const mo = new MutationObserver((m, o) => {
                        if (!document.body.contains($host.get(0))) {
                        try {
                            drake.destroy();
                        } catch (_) {}
                        o.disconnect();
                        }
                    });
                    mo.observe(document.body, { childList: true, subtree: true });
                    });
                };
                const bindOnce = (key, binder) => {
                    const root = document.documentElement;
                    const attr = dataBound + key;
                    if (root.getAttribute(attr) === "true") {
                    return;
                    }
                    root.setAttribute(attr, "true");
                    binder();
                    const mo = new MutationObserver((m, o) => {
                    if (!document.body.contains(root)) {
                        o.disconnect();
                    }
                    });
                    mo.observe(document.body, { childList: true, subtree: true });
                };
                const toggleSelectionUser = () => {
                    bindOnce("add-usr", function () {
                    $(document).on("click.addUsr", ".add_usr", function () {
                        try {
                        const ids = [];
                        const $btn = $(this);
                        $btn.toggleClass("selected");
                        const crr_id = $btn.attr("data-id");
                        const t = $("#usr_txt_" + crr_id);
                        t.html(t.html() === "Add" ? "{{__('Added')}}" : "{{__('Add')}}");
                        const ic = $("#usr_icon_" + crr_id);
                        if (ic.hasClass("fa-plus")) {
                            ic.removeClass("fa-plus").addClass("fa-check");
                        } else {
                            ic.removeClass("fa-check").addClass("fa-plus");
                        }
                        $(".selected").each(function () {
                            ids.push($(this).attr("data-id"));
                        });
                        $('input[name="assign_to"]').val(ids);
                        } catch (_) {}
                    });
                    });
                };
                const deleteTask = () => {
                    bindOnce("del-task", function () {
                    $(document).on("click.delTask", ".del_task", function () {
                        const $btn = $(this);
                        const url = resolveUrl(this, $btn.attr("data-url"));
                        if (!url) {
                        scheduleInteractiveError(getMsg(this, "delete_task_unavailable"));
                        return;
                        }
                        ajaxDelete(
                        url,
                        function (data) {
                            if (data && data.task_id) {
                            $("#" + data.task_id).remove();
                            }
                            if (window.show_toastr) {
                            window.show_toastr(
                                "{{__('Success')}}",
                                "{{ __('Task Deleted Successfully!')}}",
                                "success"
                            );
                            }
                        },
                        this,
                        "delete_task_unavailable"
                        );
                    });
                    });
                };
                const addComment = () => {
                    bindOnce("comment-submit", function () {
                    $(document).on("click.commentSubmit", "#comment_submit", function () {
                        const curr = $(this);
                        const v = $.trim(
                        $("#form-comment textarea[name='comment']").val() || ""
                        );
                        if (!v) {
                        if (window.show_toastr) {
                            window.show_toastr(
                            "{{__('Error')}}",
                            "{{ __('Please write comment!')}}",
                            "error"
                            );
                        }
                        return;
                        }
                        const form = document.getElementById("form-comment");
                        const url = resolveUrl(form, $("#form-comment").data("action"));
                        if (!url) {
                        scheduleInteractiveError(getMsg(form, "comment_add_unavailable"));
                        return;
                        }
                        ajaxPost(
                        url,
                        { comment: v },
                        function (data) {
                            try {
                            data = typeof data === "string" ? JSON.parse(data) : data;
                            const html =
                                "<div class='{{ VC::LG_IT }} px-0'><div class='{{ VC::R_ALC }}'><div class='{{ VC::C_AT }}'><a href='#' class='avatar avatar-sm rounded-circle'><img " +
                                (data.user && data.user.img_avatar
                                ? data.user.img_avatar
                                : "") +
                                " alt='" +
                                (data.user && data.user.name ? data.user.name : "") +
                                "'></a></div><div class='col ml-n2'><p class='{{ VC::DBL }} h6 {{ VC::TXSM }} font-weight-light {{ VC::MB0 }} text-break'>" +
                                (data.comment ?? "") +
                                "</p><small class='{{ VC::DBL }}'>" +
                                now +
                                "</small></div><div class='{{ VC::C_AT }}'><a href='#' class='delete-comment' data-url='" +
                                (data.deleteUrl ?? "") +
                                "'><i class='{{ VC::TI_TRS_ALT }}'></i></a></div></div></div>";
                            $("#comments").prepend(html);
                            $("#form-comment textarea[name='comment']").val("");
                            const sid = curr.closest(".side-modal").attr("id");
                            if (sid) {
                                load_task(sid);
                            }
                            if (window.show_toastr) {
                                window.show_toastr(
                                "{{__('Success')}}",
                                "{{ __('Comment Added Successfully!')}}",
                                "success"
                                );
                            }
                            } catch (_) {
                            scheduleInteractiveError(getMsg(form, "comment_add_unavailable"));
                            }
                        },
                        form,
                        "comment_add_unavailable"
                        );
                    });
                    });
                };
                const deleteComment = () => {
                    bindOnce("comment-delete", function () {
                    $(document).on("click.commentDel", ".delete-comment", function () {
                        const btn = $(this);
                        const url = resolveUrl(this, btn.attr("data-url"));
                        if (!url) {
                        scheduleInteractiveError(getMsg(this, "comment_delete_unavailable"));
                        return;
                        }
                        ajaxDelete(
                        url,
                        function () {
                            const sid = btn.closest(".side-modal").attr("id");
                            if (sid) {
                            load_task(sid);
                            }
                            if (window.show_toastr) {
                            window.show_toastr(
                                "{{__('Success')}}",
                                "{{ __('Comment Deleted Successfully!')}}",
                                "success"
                            );
                            }
                            btn.closest(".list-group-item").remove();
                        },
                        this,
                        "comment_delete_unavailable"
                        );
                    });
                    });
                };
                const addChecklist = () => {
                    bindOnce("checklist-add", function () {
                    $(document).on("click.checklistAdd", "#checklist_submit", function () {
                        const name = $("#form-checklist input[name=name]").val() || "";
                        if (!name) {
                        if (window.show_toastr) {
                            window.show_toastr(
                            "{{__('Error')}}",
                            "{{ __('Please write checklist name!')}}",
                            "error"
                            );
                        }
                        return;
                        }
                        const form = document.getElementById("form-checklist");
                        const url = resolveUrl(form, $("#form-checklist").data("action"));
                        if (!url) {
                        scheduleInteractiveError(getMsg(form, "checklist_add_unavailable"));
                        return;
                        }
                        ajaxPost(
                        url,
                        { name: name },
                        function (data) {
                            try {
                            data = typeof data === "string" ? JSON.parse(data) : data;
                            const html =
                                '<div class="{{ VC::CD_NSD }} checklist-member"><div class="{{ VC::PX3 }} {{ VC::PY2 }} {{ VC::R_ALC }}"><div class="col-10"><div class="{{ VC::CST_CT_CB }}"><input type="checkbox" class="custom-control-input" id="check-item-' +
                                (data.id ?? "") +
                                '" value="' +
                                (data.id ?? "") +
                                '" data-url="' +
                                (data.updateUrl ?? "") +
                                '"><label class="{{ VC::CST_LB_SM }}" for="check-item-' +
                                (data.id ?? "") +
                                '">' +
                                (data.name ?? "") +
                                "</label></div></div><div class='{{ VC::C_AT }} {{ VC::CD_MT }} {{ VC::DFL_IL_VC }} {{ VC::ML_SM_AT }}'><a href='#' class='action-item delete-checklist' role='button' data-url='" +
                                (data.deleteUrl ?? "") +
                                "'><i class='{{ VC::TI_TRS_ALT }}'></i></a></div></div></div>";
                            $("#checklist").append(html);
                            $("#form-checklist input[name=name]").val("");
                            $("#form-checklist").collapse("toggle");
                            const sid = $(".side-modal").attr("id");
                            if (sid) {
                                load_task(sid);
                            }
                            if (window.show_toastr) {
                                window.show_toastr(
                                "{{__('Success')}}",
                                "{{ __('Checklist Added Successfully!')}}",
                                "success"
                                );
                            }
                            } catch (_) {
                            scheduleInteractiveError(
                                getMsg(form, "checklist_add_unavailable")
                            );
                            }
                        },
                        form,
                        "checklist_add_unavailable"
                        );
                    });
                    });
                };
                const updateChecklist = () => {
                    bindOnce("checklist-update", function () {
                    $(document).on(
                        "change.checklistToggle",
                        "#checklist input[type=checkbox]",
                        function () {
                        const url = resolveUrl(this, $(this).attr("data-url"));
                        if (!url) {
                            scheduleInteractiveError(
                            getMsg(this, "checklist_update_unavailable")
                            );
                            return;
                        }
                        ajaxPost(
                            url,
                            {},
                            function () {
                            const sid = $(".side-modal").attr("id");
                            if (sid) {
                                load_task(sid);
                            }
                            if (window.show_toastr) {
                                window.show_toastr(
                                "{{__('Success')}}",
                                "{{ __('Checklist Updated Successfully!')}}",
                                "success"
                                );
                            }
                            },
                            this,
                            "checklist_update_unavailable"
                        );
                        }
                    );
                    });
                };
                const deleteChecklist = () => {
                    bindOnce("checklist-delete", function () {
                    $(document).on("click.checklistDel", ".delete-checklist", function () {
                        const btn = $(this);
                        const url = resolveUrl(this, btn.attr("data-url"));
                        if (!url) {
                        scheduleInteractiveError(
                            getMsg(this, "checklist_delete_unavailable")
                        );
                        return;
                        }
                        ajaxDelete(
                        url,
                        function () {
                            const sid = $(".side-modal").attr("id");
                            if (sid) {
                            load_task(sid);
                            }
                            if (window.show_toastr) {
                            window.show_toastr(
                                "{{__('Success')}}",
                                "{{ __('Checklist Deleted Successfully!')}}",
                                "success"
                            );
                            }
                            btn.closest(".checklist-member").remove();
                        },
                        this,
                        "checklist_delete_unavailable"
                        );
                    });
                    });
                };
                const favToggle = () => {
                    bindOnce("favorite", function () {
                    $(document).on("click.favorite", "#add_favourite", function () {
                        const btn = $(this);
                        const url = resolveUrl(this, btn.attr("data-url"));
                        if (!url) {
                        scheduleInteractiveError(getMsg(this, "favorite_unavailable"));
                        return;
                        }
                        ajaxPost(
                        url,
                        {},
                        function (data) {
                            if (data && data.fav === 1) {
                            $("#add_favourite").addClass("action-favorite");
                            } else if (data && data.fav === 0) {
                            $("#add_favourite").removeClass("action-favorite");
                            }
                        },
                        this,
                        "favorite_unavailable"
                        );
                    });
                    });
                };
                const completeToggle = () => {
                    bindOnce("complete", function () {
                    $(document).on("change.complete", "#complete_task", function () {
                        const cb = $(this);
                        const url = resolveUrl(this, cb.attr("data-url"));
                        if (!url) {
                        scheduleInteractiveError(getMsg(this, "complete_unavailable"));
                        return;
                        }
                        ajaxPost(
                        url,
                        {},
                        function (data) {
                            if (data && typeof data.com !== "undefined") {
                            $("#complete_task").prop("checked", !!data.com);
                            }
                            if (data && data.task && data.stage) {
                            $("#" + data.task).insertBefore(
                                $("#task-list-" + data.stage + " .empty-container")
                            );
                            load_task(data.task);
                            }
                        },
                        this,
                        "complete_unavailable"
                        );
                    });
                    });
                };
                const progressMove = () => {
                    bindOnce("progress", function () {
                    $(document).on("change.progress", "#task_progress", function () {
                        const sel = $(this);
                        const url = resolveUrl(this, sel.attr("data-url"));
                        if (!url) {
                        scheduleInteractiveError(getMsg(this, "progress_unavailable"));
                        return;
                        }
                        const progress = sel.val();
                        $("#t_percentage").html(progress);
                        ajaxPost(
                        url,
                        { progress: progress },
                        function (data) {
                            if (data && data.task_id) {
                            load_task(data.task_id);
                            }
                        },
                        this,
                        "progress_unavailable"
                        );
                    });
                    });
                };
                const ajaxCsrfHeader = () => {
                    if (!$.ajaxSetup) {
                    return;
                    }
                    $.ajaxSetup({
                    headers: {
                        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content") ?? "",
                    },
                    });
                };
                const load_task = id => {
                    const base = "{{route(VW::PRJ_TSK_C.'.get','_task_id')}}".replace(
                    "_task_id",
                    id || ""
                    );
                    const url = resolveUrl(null, base);
                    if (!url) {
                    scheduleInteractiveError(getMsg(document.body, "load_task_unavailable"));
                    return;
                    }
                    $.ajax({
                    url: url,
                    dataType: "html",
                    cache: false,
                    success: function (data) {
                        if (id) {
                        const c = document.getElementById(id);
                        if (c) {
                            $("#" + id).html("");
                            $("#" + id).html(data);
                        }
                        }
                    },
                    error: function () {
                        scheduleInteractiveError(
                        getMsg(document.body, "load_task_unavailable")
                        );
                    },
                    });
                };
                const init = () => {
                    if (!$ || !$.fn) {
                    try {
                        if (
                            window.location.hostname === "localhost" ||
                            window.location.hostname === "127.0.0.1"
                        ) console.error("jQuery unavailable");
                    } catch (_) {}
                    scheduleInteractiveError(getMsg(document.body, "plugin_unavailable"));
                    return;
                    }
                    ajaxCsrfHeader();
                    initDragula();
                    toggleSelectionUser();
                    deleteTask();
                    addComment();
                    deleteComment();
                    addChecklist();
                    updateChecklist();
                    deleteChecklist();
                    favToggle();
                    completeToggle();
                    progressMove();
                };
                if (document.readyState === "loading") {
                    document.addEventListener("DOMContentLoaded", init, { once: true });
                } else {
                    init();
                }
            })();
        </script>
    @endpush
@else
    <div class="kanban-col px-0"><div class="card-list card-list-flush"><div class="card-list-title"><h6 class="{{ VC::MB0 }}">{{ __('No project for tasks could be found') }}</h6></div></div></div>
@endif
