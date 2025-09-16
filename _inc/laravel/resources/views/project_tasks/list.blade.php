@php
    use App\Config\Constants\{ProjectsConstants, ViewsConstants as VW, ViewClassNamesConstants as VC, StacksConstants};
    use App\Models\{ProjectTask, Utility};
    use Illuminate\Support\Facades\{Auth, Route};
    use Illuminate\Support\{Collection,Str};
    $user = Auth::user();
    $lang = Utility::fetchUserLang(user:$user);
@endphp
<div class="col-md-12">
    <div class="card">
        <div class="col-12">
            <div class="card-body table-border-style">
                <div class="table-responsive">
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
                                @endphp
                                <tr>
                                    <td>
                                        <span class="h6 text-sm font-weight-bold mb-0">
                                            <a href="{{ $idxUrl }}" id="{{ $idxLinkId }}" class="text-decoration-none" data-url="{{ $idxUrl }}" data-guard-msg="{{ $idxGuardMsg }}">{{ $taskName }}</a>
                                        </span>
                                        <span class="d-flex text-sm text-muted justify-content-between">
                                            <p class="m-0">{{ $projectName }}</p>
                                            <span class="me-5 badge p-2 px-3 rounded bg-{{ $ownerBg }}">{{ $ownerBadge }}</span>
                                        </span>
                                    </td>
                                    <td>{{ $stageName }}</td>
                                    <td><span class="status_badge badge p-2 px-3 rounded bg-{{ $priorityBg }}">{{ $priorityLabel }}</span></td>
                                    <td class="{{ $isOverdue ? 'text-danger' : '' }}">{{ $endDateText ?: '-' }}</td>
                                    <td>
                                        <div class="avatar-group">
                                            @if($assignees && $assignees->count() > 0)
                                                @foreach($assignees as $k => $assignee)
                                                    @if($k < 3)
                                                        @php
                                                            $aName = data_get($assignee,'name') ?? '';
                                                            $aAvatar = data_get($assignee,'avatar');
                                                            $aSrc = !empty($aAvatar) ? asset('/storage/uploads/avatar/'.$aAvatar) : asset('/storage/uploads/avatar/avatar.png');
                                                        @endphp
                                                        <a href="#" class="avatar rounded-circle avatar-sm">
                                                            <img title="{{ $aName }}" src="{{ $aSrc }}" class="hweb">
                                                        </a>
                                                    @else
                                                        @break
                                                    @endif
                                                @endforeach
                                                @if($assignees->count() > 3)
                                                    <span class="avatar rounded-circle avatar-sm d-inline-flex align-items-center justify-content-center">{{ '+' . ($assignees->count() - 3) }}</span>
                                                @endif
                                            @else
                                                {{ __('-') }}
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        @php $pctNum = (float)str_replace('%','',$pctStr); @endphp
                                        <div class="align-items-center">
                                            <span class="completion">{{ $pctStr }}</span>
                                            <div class="progress">
                                                <div class="progress-bar bg-{{ $pctColor }}" role="progressbar" style="width: {{ $pctStr }};"></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-end w-15">
                                        <div class="actions">
                                            <a class="action-item px-1" data-bs-toggle="tooltip" title="{{ __('Attachment') }}"><i class="ti ti-paperclip mr-2"></i>{{ $filesCount }}</a>
                                            <a class="action-item px-1" data-bs-toggle="tooltip" title="{{ __('Comment') }}"><i class="ti ti-brand-hipchat mr-2"></i>{{ $commentsCount }}</a>
                                            <a class="action-item px-1" data-bs-toggle="tooltip" title="{{ __('Checklist') }}"><i class="ti ti-list-check mr-2"></i>{{ $checklistTotal }}</a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <th scope="col" colspan="7"><h6 class="text-center">{{ __('No tasks found') }}</h6></th>
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

