@php
    try {
$user = Auth::user();
        $lang = Utility::fetchUserLang(user:$user);
    } catch (\Throwable $e) {
        \Log::error('project_tasks/list — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    }
@endphp
<div class="{{ VC::CM12 }}">
    <div class="card">
        <div class="{{ VC::C12 }}">
            <div class="{{ VC::CD_BD_TB_BD }}">
                <div class="{{ VC::TB_RSP }}">
                    <table class="table datatable">
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
                        @if(isset($tasks) && (is_array($tasks) || $tasks instanceof Collection) && count($tasks) > 0)
                            @foreach($tasks as $task)
                                @php
                                    try {
                                        $taskValid = isset($task) && (is_array($task) || is_object($task));
                                        $projectId = $taskValid ? (data_get($task,'project_id') ?? data_get($task,'project.id')) : null;
                                        $taskId = $taskValid ? data_get($task,'id') : null;
                                        $idxUrl = $projectId ? route(VW::PRJ_TSK_C.'.index', [$projectId]) : '#';
                                        $idxLinkId = 'task-index-link-'.($taskId ?? 'x');
                                        $idxGuardMsg = __(Utility::fetchLinkMessage($lang ?? app()->getLocale(), VW::PRJ_TSK_C, 'index_project_task_unavailable') ?? 'View project tasks route is unavailable. Please contact technical support or your domain administrator.');
                                        $taskName = $taskValid ? (data_get($task,'name') ?? __('No task name available')) : __('No task available');
                                        $projectName = $taskValid ? (data_get($task,'project.project_name') ?? '-') : '-';
                                        $stageName = $taskValid ? (data_get($task,'stage.name') ?? '-') : '-';
                                        $priorityIdx = (int)($taskValid ? (data_get($task,'priority') ?? -1) : -1);
                                        $priorityColors = is_array(ProjectTask::$priority_color ?? null) ? ProjectTask::$priority_color : [];
                                        $priorityLabels = is_array(ProjectTask::$priority ?? null) ? ProjectTask::$priority : [];
                                        $priorityBg = $priorityColors[$priorityIdx] ?? 'secondary';
                                        $priorityLabel = __($priorityLabels[$priorityIdx] ?? '-');
                                        $endDate = $taskValid ? data_get($task,'end_date') : null;
                                        $endDateText = $endDate ? (Utility::getDateFormated($endDate) ?? '') : '';
                                        $isOverdue = $endDate ? (strtotime((string)$endDate) < time()) : false;
                                        $progress = $taskValid && method_exists($task,'taskProgress') ? (array)$task->taskProgress($task) : [];
                                        $pctStr = (string)($progress['percentage'] ?? '0%');
                                        $pctColor = (string)($progress['color'] ?? 'secondary');
                                        $ownerCheck = (isset($user) && method_exists($user,'checkProject')) ? $user->checkProject(data_get($task,'project_id')) : null;
                                        $ownerBadge = $ownerCheck ? __($ownerCheck) : __('Unknown');
                                        $ownerBg = ($ownerCheck === 'Owner') ? ProjectsConstants::STT_SCS : ProjectsConstants::STT_WRN;
                                        $taskFilesRel = $taskValid ? data_get($task,'taskFiles') : [];
                                        $filesCount = is_countable($taskFilesRel) ? count($taskFilesRel) : ((is_object($taskFilesRel) && method_exists($taskFilesRel,'count')) ? $taskFilesRel->count() : 0);
                                        $commentsRel = $taskValid ? data_get($task,'comments') : [];
                                        $commentsCount = is_countable($commentsRel) ? count($commentsRel) : ((is_object($commentsRel) && method_exists($commentsRel,'count')) ? $commentsRel->count() : 0);
                                        $checklistRel = $taskValid ? data_get($task,'checklist') : [];
                                        $checklistCount = is_countable($checklistRel) ? count($checklistRel) : ((is_object($checklistRel) && method_exists($checklistRel,'count')) ? $checklistRel->count() : 0);
                                        $checklistTotal = $taskValid && method_exists($task,'countTaskChecklist') ? (int)$task->countTaskChecklist() : $checklistCount;
                                        $assignees = $taskValid && method_exists($task,'users') ? $task->users() : collect();
                                    } catch (\Throwable $e) {
                                        \Log::error('project_tasks/list — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                    }
@endphp
                                <tr>
                                    <td>
                                        <span class="h6 {{ VC::TXSM }} font-weight-bold {{ VC::MB0 }}">
                                            <a href="{{ $idxUrl }}" id="{{ $idxLinkId }}" class="text-decoration-none" data-url="{{ $idxUrl }}" data-guard-msg="{{ base64_encode($idxGuardMsg) }}">{{ $taskName }}</a>
                                        </span>
                                        <span class="{{ VC::DFL }} {{ VC::TXSM }} {{ VC::TXT_MT }} {{ VC::JCB }}">
                                            <p class="m-0">{{ $projectName }}</p>
                                            <span class="me-5 badge p-2 {{ VC::PX3 }} rounded bg-{{ $ownerBg }}">{{ $ownerBadge }}</span>
                                        </span>
                                    </td>
                                    <td>{{ $stageName }}</td>
                                    <td><span class="status_badge badge p-2 {{ VC::PX3 }} rounded bg-{{ $priorityBg }}">{{ $priorityLabel }}</span></td>
                                    <td class="{{ $isOverdue ? 'text-danger' : '' }}">{{ $endDateText ?: '-' }}</td>
                                    <td>
                                        <div class="avatar-group">
                                            @if($assignees && $assignees->count() > 0)
                                                @foreach($assignees as $k => $assignee)
                                                    @if($k < 3)
                                                        @php
                                                            try {
                                                                $aName = data_get($assignee,'name') ?? '';
                                                                $aAvatar = data_get($assignee,'avatar');
                                                                $aSrc = !empty($aAvatar) ? asset('/storage/uploads/avatar/'.$aAvatar) : asset('/storage/uploads/avatar/avatar.png');
                                                            } catch (\Throwable $e) {
                                                                \Log::error('project_tasks/list — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                            }
@endphp
                                                        <a href="#" class="{{ VC::AV_CC_SM }}">
                                                            <img title="{{ $aName }}" src="{{ $aSrc }}" class="hweb">
                                                        </a>
                                                    @else
                                                        @break
                                                    @endif
                                                @endforeach
                                                @if($assignees->count() > 3)
                                                    <span class="{{ VC::AV_CC_SM }} {{ VC::DFL_IL_VC }} {{ VC::JCC }}">{{ '+' . ($assignees->count() - 3) }}</span>
                                                @endif
                                            @else
                                                {{ __('-') }}
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        @php
 $pctNum = (float)str_replace('%','',$pctStr);
@endphp
                                        <div class="{{ VC::ALC }}">
                                            <span class="completion">{{ $pctStr }}</span>
                                            <div class="progress">
                                                <div class="progress-bar bg-{{ $pctColor }}" role="progressbar" style="width: {{ $pctStr }};"></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="{{ VC::TX_END }} w-15">
                                        <div class="actions">
                                            <a class="action-item px-1" data-bs-toggle="tooltip" title="{{ __('Attachment') }}"><i class="ti ti-paperclip {{ VC::MR2 }}"></i>{{ $filesCount }}</a>
                                            <a class="action-item px-1" data-bs-toggle="tooltip" title="{{ __('Comment') }}"><i class="ti ti-brand-hipchat {{ VC::MR2 }}"></i>{{ $commentsCount }}</a>
                                            <a class="action-item px-1" data-bs-toggle="tooltip" title="{{ __('Checklist') }}"><i class="ti ti-list-check {{ VC::MR2 }}"></i>{{ $checklistTotal }}</a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <th scope="col" colspan="7"><h6 class="{{ VC::TXCT }}">{{ __('No tasks found') }}</h6></th>
                            </tr>
                        @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <script defer src="{{ asset('assets/js/routes/projects/tasks/projectList.js') }}"></script>
</div>
