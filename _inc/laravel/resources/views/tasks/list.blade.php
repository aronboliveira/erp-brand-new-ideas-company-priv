@include('partials.helpers.route_helpers')
@php
    try {
$tasks ??= [];
        $user = Auth::user();
        $lang = Utility::fetchUserLang(user: $user);
        $noTasksLabel = __('No tasks found') ?: __('No tasks available');
        $showGuard = resolveRouteWithGuard(VW::PRJ_TSK_C.'.index', $lang, VW::PRJ_TSK_C, 'show_project_task_route_unavailable', [], 'Show task unavailable.');
    } catch (\Throwable $e) {
        \Log::error('tasks/list — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    }
@endphp

<div class="{{ VC::C12 }}">
    <div class="{{ VC::CD }}">
        <div class="{{ VC::TB_RSP }}">
            <table class="{{ VC::TB_AL }}">
                <thead>
                    <tr>
                        <th scope="col">{{ __('Name') }}</th>
                        <th scope="col">{{ __('Stage') }}</th>
                        <th scope="col">{{ __('Priority') }}</th>
                        <th scope="col">{{ __('End Date') }}</th>
                        <th scope="col">{{ __('Assigned To') }}</th>
                        <th scope="col">{{ __('Completion') }}</th>
                        <th scope="col"></th>
                    </tr>
                </thead>
                <tbody class="list">
                    @if(count($tasks) > 0)
                        @foreach($tasks as $task)
                            @php
                                $projectId = safeDataGet($task, 'project.id');
                                $taskName = safeDataGet($task, 'name') ?: __('No name available');
                                $projectName = safeDataGet($task, 'project.name') ?: __('No project name available');
                                $showRoute = resolveRouteWithGuard(VW::PRJ_TSK_C.'.index', $lang, VW::PRJ_TSK_C, 'show_project_task_route_unavailable', [$projectId], 'Show task unavailable.');

                                $priorityKey = safeDataGet($task, 'priority');
                                $priorityColor = data_get(ProjectTask::$priority_color, $priorityKey) ?? 'secondary';
                                $priorityText = data_get(ProjectTask::$priority, $priorityKey) ?? __('No priority available');

                                $endDate = safeDataGet($task, 'end_date');
                                $isOverdue = false;
                                $endDateOut = '';
                                try {
                                    if (!empty($endDate) && $endDate !== '0000-00-00') {
                                        $endDateOut = Utility::getDateFormated($endDate);
                                        $isOverdue = @strtotime($endDate) < @time();
                                    }
                                } catch (\Throwable $e) { Log::error('Blade tasks/list: end date error: '.$e->getMessage()); }

                                $stageName = safeDataGet($task, 'stage.name') ?: __('No stage available');

                                $ownerState = null;
                                try { $ownerState = $user?->checkProject(safeDataGet($task, 'project_id')); }
                                catch (\Throwable $e) { Log::error('Blade tasks/list: checkProject error: '.$e->getMessage()); }
                                $ownerBadge = ($ownerState === 'Owner') ? ProjectsConstants::STT_SCS : ProjectsConstants::STT_WRN;

                                $filesCount = count(ensureIterable(safeDataGet($task, 'taskFiles')));
                                $commentsCount = count(ensureIterable(safeDataGet($task, 'comments')));
                                $checklistCount = 0;
                                try { $checklistCount = method_exists($task, 'countTaskChecklist') ? (int)$task->countTaskChecklist() : 0; }
                                catch (\Throwable $e) { $checklistCount = 0; }

                                $progressPct = safeProgress($task);
                                $progressPctOnly = is_string($progressPct) ? str_replace('%', '', $progressPct) : '0';
                                $progressColor = 'secondary';
                                try { $progressColor = data_get($task->taskProgress($task), 'color', 'secondary'); }
                                catch (\Throwable $e) { Log::error('Blade tasks/list: taskProgress error: '.$e->getMessage()); }

                                $usersList = [];
                                try { $usersList = method_exists($task, 'users') ? $task->users() : []; }
                                catch (\Throwable $e) { $usersList = []; }
                                $uCount = is_countable($usersList) ? count($usersList) : 0;
@endphp
                            <tr>
                                <td>
                                    <span class="{{ VC::H6 }} {{ VC::TXSM }} font-weight-bold {{ VC::MB0 }}">
                                        <a href="{{ $showRoute['url'] }}" class="project-task-index-link" data-url="{{ $showRoute['url'] }}" data-guard-msg="{{ base64_encode($showRoute['guardMsg']) }}" data-sv-localized="true">{{ $taskName }}</a>
                                    </span>
                                    <span class="{{ VC::DBL }} {{ VC::TXSM }} {{ VC::TXT_MT }}">
                                        {{ $projectName }}
                                        <span class="{{ VC::BDG_XS }} badge-{{ $ownerBadge }}">{{ __($ownerState ?? '-') }}</span>
                                    </span>
                                </td>
                                <td>{{ $stageName }}</td>
                                <td><span class="{{ VC::BDG }} badge-pill badge-sm badge-{{ $priorityColor }}">{{ __($priorityText) }}</span></td>
                                <td class="{{ $isOverdue ? 'text-danger' : '' }}">{{ $endDateOut }}</td>
                                <td>
                                    <div class="avatar-group">
                                        @if($uCount > 0)
                                            @foreach($usersList as $k => $u)
                                                @if($k < 3)
                                                    @php
                                                        $img ??= '';
                                                        try { $img = $u->getImgImageAttribute(); } catch (\Throwable $e) { $img = ''; }
@endphp
                                                    <a href="#" class="{{ VC::AV_CC_SM }}"><img src="{{ $img }}" title="{{ safeDataGet($u, 'name') }}"></a>
                                                @else @break
                                                @endif
                                            @endforeach
                                            @if($uCount > 3)
                                                <a href="#" class="{{ VC::AV_CC_SM }}"><img avatar="+ {{ $uCount - 3 }}"></a>
                                            @endif
                                        @else
                                            {{ __('-') }}
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <div class="{{ VC::DFL_AIC }}">
                                        <span class="completion {{ VC::MR2 }}">{{ $progressPct }}</span>
                                        <div>
                                            <div class="{{ VC::PG }}" style="width: 100px;">
                                                <div class="progress-bar bg-{{ $progressColor }}" role="progressbar" aria-valuenow="{{ $progressPctOnly }}" aria-valuemin="0" aria-valuemax="100" style="width: {{ $progressPct }};"></div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="{{ VC::TX_END }} w-15">
                                    <div class="actions">
                                        <a class="action-item px-1" data-toggle="tooltip" data-original-title="{{ __('Attachment') }}"><i class="ti ti-paperclip {{ VC::MR2 }}"></i>{{ $filesCount }}</a>
                                        <a class="action-item px-1" data-toggle="tooltip" data-original-title="{{ __('Comment') }}"><i class="ti ti-brand-hipchart {{ VC::MR2 }}"></i>{{ $commentsCount }}</a>
                                        <a class="action-item px-1" data-toggle="tooltip" data-original-title="{{ __('Checklist') }}"><i class="ti ti-tasks {{ VC::MR2 }}"></i>{{ $checklistCount }}</a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <th scope="col" colspan="7"><h6 class="{{ VC::TXCT }}">{{ $noTasksLabel }}</h6></th>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
    <script defer src="{{ asset('assets/js/core/route-guard.js') }}"></script>
    <script defer src="{{ asset('assets/js/routes/projects/tasks/list.js') }}"></script>
</div>
