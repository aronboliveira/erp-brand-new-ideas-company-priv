@include('partials.helpers.route_helpers')
@php
    try {
$tasks ??= [];
        $user = Auth::user();
        $lang = Utility::fetchUserLang(user: $user);
        $noTasksLabel = __('No tasks found') ?: __('No tasks available');
        $showGuardBase = resolveRouteWithGuard(VW::PRJ_TSK_C.'.index', $lang, VW::PRJ_TSK_C, 'show_project_task_route_unavailable', [], 'Show task route unavailable.');
    } catch (\Throwable $e) {
        \Log::error('tasks/grid — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    }
@endphp

<div class="{{ VC::C12 }}">
    <div class="{{ VC::CD }}">
        <div class="{{ VC::RW }}">
            @if(count($tasks) > 0)
                @foreach($tasks as $task)
                    @php
                        $permissions ??= null;
                        try { $permissions = method_exists($user, 'getPermission') ? $user?->getPermission(safeDataGet($task, 'project_id')) : null; }
                        catch (\Throwable $e) { Log::error('Blade tasks/grid: error getting permissions: '.$e->getMessage()); }

                        $projectId = safeDataGet($task, 'project.id');
                        $showRoute = resolveRouteWithGuard(VW::PRJ_TSK_C.'.index', $lang, VW::PRJ_TSK_C, 'show_project_task_route_unavailable', [$projectId], 'Show task unavailable.');

                        $progressPct = safeProgress($task);
                        $progressVal = is_string($progressPct) ? str_replace('%', '', $progressPct) : null;

                        $priorityKey = safeDataGet($task, 'priority');
                        $priorityColor = data_get(ProjectTask::$priority_color, $priorityKey) ?? 'secondary';
                        $priorityText = data_get(ProjectTask::$priority, $priorityKey) ?? __('No priority available');

                        $endDateOut = null;
                        $isOverdue = false;
                        try {
                            $ed = safeDataGet($task, 'end_date');
                            if (!empty($ed) && $ed !== '0000-00-00') {
                                $endDateOut = is_callable([Utility::class, 'getDateFormated']) ? Utility::getDateFormated($ed) : null;
                                $isOverdue = @strtotime($ed) < @time();
                            }
                        } catch (\Throwable $e) { Log::error('Blade tasks/grid: error formatting end date: '.$e->getMessage()); }

                        $usersList = [];
                        try { $usersList = method_exists($task, 'users') ? $task->users() : []; }
                        catch (\Throwable $e) { Log::error('Blade tasks/grid: error fetching task users: '.$e->getMessage()); }

                        $taskId = safeDataGet($task, 'id');
                        $taskName = safeDataGet($task, 'name') ?: __('No name available');
                        $taskPriorityColor = safeDataGet($task, 'priority_color');
                        $cardStyle = !empty($taskPriorityColor) ? 'border-left: 2px solid '.$taskPriorityColor.' !important' : '';
                        $taskFiles = ensureIterable(safeDataGet($task, 'taskFiles'));
                        $comments = ensureIterable(safeDataGet($task, 'comments'));
@endphp
                    <div class="{{ VC::CM4 }}">
                        <div class="{{ VC::CD }} m-3 card-progress {{ VC::BD }} {{ VC::SNN }}" id="{{ $taskId }}" style="{{ $cardStyle }};">
                            <div class="{{ VC::CD_BD }}">
                                <div class="{{ VC::RW }} align-items-center mb-2">
                                    <div class="{{ VC::C6 }}">
                                        <span class="{{ VC::BDG_XS }} badge-pill badge-{{ $priorityColor }}">{{ $priorityText }}</span>
                                    </div>
                                    <div class="{{ VC::C6 }} text-end">
                                        @if(is_numeric($progressVal) && floatval($progressVal) > 0)
                                            <span class="{{ VC::TXSM }}">{{ $progressPct }}</span>
                                        @endif
                                    </div>
                                </div>

                                @if(isset($permissions) && is_array($permissions) && in_array('show task', $permissions, true))
                                    <a class="{{ VC::H6 }} task-name-break project-task-index-link" href="{{ $showRoute['url'] }}" data-url="{{ $showRoute['url'] }}" data-guard-msg="{{ base64_encode($showRoute['guardMsg']) }}" data-sv-localized="true">{{ $taskName }}</a>
                                @else
                                    <a class="{{ VC::H6 }} task-name-break project-task-index-link" href="#" data-url="#" data-guard-msg="{{ base64_encode($showRoute['guardMsg']) }}" data-sv-localized="true">{{ $taskName }}</a>
                                @endif

                                <div class="{{ VC::RW }} {{ VC::ALC }}">
                                    <div class="{{ VC::C12 }}">
                                        <div class="actions d-inline-block">
                                            @if(count($taskFiles) > 0)
                                                <div class="action-item {{ VC::MR2 }}"><i class="ti ti-paperclip {{ VC::MR2 }}"></i>{{ count($taskFiles) }}</div>
                                            @endif
                                            @if(count($comments) > 0)
                                                <div class="action-item {{ VC::MR2 }}"><i class="ti ti-brand-hipchart {{ VC::MR2 }}"></i>{{ count($comments) }}</div>
                                            @endif
                                            @if(method_exists($task, 'checklist') && $task->checklist->count() > 0)
                                                <div class="action-item {{ VC::MR2 }}"><i class="ti ti-tasks {{ VC::MR2 }}"></i>{{ $task->countTaskChecklist() }}</div>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="{{ VC::C5 }}">
                                        @if(!empty($endDateOut))
                                            <small @if($isOverdue) class="{{ VC::TX_DNG }}" @endif>{{ $endDateOut }}</small>
                                        @endif
                                    </div>
                                    <div class="{{ VC::C7 }} text-end">
                                        @if(is_iterable($usersList) && count($usersList) > 0)
                                            <div class="avatar-group">
                                                @foreach($usersList as $key => $u)
                                                    @if($key < 3)
                                                        <a href="#" class="{{ VC::AV_CC_SM }}"><img {!! safeDataGet($u, 'img_avatar') !!} title="{{ safeDataGet($u, 'name') }}"></a>
                                                    @else @break
                                                    @endif
                                                @endforeach
                                                @if(count($usersList) > 3)
                                                    <a href="#" class="{{ VC::AV_CC_SM }}"><img avatar="+ {{ count($usersList) - 3 }}"></a>
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
                    <h6 class="{{ VC::TXCT }} m-3">{{ $noTasksLabel }}</h6>
                </div>
            @endif
        </div>
    </div>
    <script defer src="{{ asset('assets/js/core/route-guard.js') }}"></script>
    <script defer src="{{ asset('assets/js/routes/projects/tasks/gridShow.js') }}"></script>
</div>
