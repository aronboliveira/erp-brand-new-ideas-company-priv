@php
    use App\Config\Constants\{StacksConstants, ViewClassNamesConstants as VC};
    use App\Models\{Project, ProjectTask, Utility};
    use Illuminate\Support\Facades\Auth;
    $lang = Utility::fetchUserLang();
    $milestoneStatus = data_get($milestone, 'status');
    $milestoneDesc = data_get($milestone, 'description');
    $statusColor = Project::$status_color[$milestoneStatus] ?? 'secondary';
    $statusLabel = Project::$project_status[$milestoneStatus] ?? __('Could not retrieve milestone status.');
    $tasks = data_get($milestone, 'tasks', []);
@endphp
<div class="modal-body">
    <div class="p-2">
        <div class="row mb-4">
            <div class="col-md-6">
                <span class="font-bold lab-title">{{ __('Status') }} : </span>
                <span class="badge-xs badge p-2 px-3 rounded bg-{{ $statusColor }} text-white">{{ __($statusLabel) }}</span>
            </div>
            <div class="col-md-12 pt-4">
                <div class="font-weight-bold lab-title">{{ __('Description') }} :</div>
                <p class="mt-1 lab-val">{{ !empty($milestoneDesc) ? $milestoneDesc : '-' }}</p>
            </div>
            <div class="col-12">
                <div class="table-border-style">
                    <div class="table-responsive">
                        @php $hasTasks = isset($tasks) && !empty($tasks) && is_countable($tasks) && count($tasks) > 0; @endphp
                        <table class="{{ VC::TB }}">
                            <thead>
                                <tr>
                                    <th scope="col">{{ __('Name') }}</th>
                                    <th scope="col">{{ __('Stage') }}</th>
                                    <th scope="col">{{ __('Priority') }}</th>
                                    <th scope="col">{{ __('End Date') }}</th>
                                    <th scope="col">{{ __('Completion') }}</th>
                                    <th scope="col"></th>
                                </tr>
                            </thead>
                            <tbody class="list">
                                @if($hasTasks)
                                    @foreach($tasks as $task)
                                        @php
                                            $taskName = data_get($task, 'name', '-');
                                            $taskStage = data_get($task, 'stage.name', '-');
                                            $taskPriority = data_get($task, 'priority');
                                            $taskPriorityColor = ProjectTask::$priority_color[$taskPriority] ?? 'secondary';
                                            $taskPriorityLabel = ProjectTask::$priority[$taskPriority] ?? '-';
                                            $taskEnd = data_get($task, 'end_date');
                                            $endIsPast = !empty($taskEnd) ? (strtotime($taskEnd) < time()) : false;
                                            $tp = method_exists($task, 'taskProgress') ? $task->taskProgress($task) : ['percentage' => '0%', 'color' => 'primary'];
                                            $tpPct = (string) data_get($tp, 'percentage', '0%');
                                            $tpPctNum = (int) str_replace('%', '', $tpPct);
                                            $tpColor = data_get($tp, 'color', 'primary');
                                            $taskFilesCnt = is_countable(data_get($task, 'taskFiles', [])) ? count(data_get($task, 'taskFiles', [])) : 0;
                                            $taskCommentsCnt = is_countable(data_get($task, 'comments', [])) ? count(data_get($task, 'comments', [])) : 0;
                                            $taskChecklistCnt = method_exists($task, 'countTaskChecklist') ? $task->countTaskChecklist() : 0;
                                        @endphp
                                        <tr>
                                            <td><span class="h6 text-sm">{{ $taskName }}</span></td>
                                            <td>{{ $taskStage }}</td>
                                            <td><span class="badge p-2 px-3 rounded badge-sm bg-{{ $taskPriorityColor }}">{{ __($taskPriorityLabel) }}</span></td>
                                            <td class="{{ $endIsPast ? 'text-danger' : '' }}">{{ Utility::getDateFormated($taskEnd) }}</td>
                                            <td>
                                                @if($tpPctNum > 0)
                                                    <span class="text-sm">{{ $tpPct }}</span>
                                                    <div class="progress" style="top:0px;">
                                                        <div class="progress-bar bg-{{ $tpColor }}" role="progressbar" style="width: {{ $tpPct }};"></div>
                                                    </div>
                                                @endif
                                            </td>
                                            <td class="text-end w-15">
                                                <div class="actions">
                                                    @php
                                                        $attachId = 'task-attach-' . (data_get($task, 'id') ?? 'x');
                                                        $commentId = 'task-comment-' . (data_get($task, 'id') ?? 'x');
                                                        $checklistId = 'task-checklist-' . (data_get($task, 'id') ?? 'x');
                                                    @endphp
                                                    <a id="{{ $attachId }}" class="action-item px-2" data-bs-toggle="tooltip" data-original-title="{{ __('Attachment') }}">
                                                        <i class="ti ti-paperclip mr-2"></i>{{ $taskFilesCnt }}
                                                    </a>
                                                    <a id="{{ $commentId }}" class="action-item px-2" data-bs-toggle="tooltip" data-original-title="{{ __('Comment') }}">
                                                        <i class="ti ti-brand-hipchat mr-2"></i>{{ $taskCommentsCnt }}
                                                    </a>
                                                    <a id="{{ $checklistId }}" class="action-item px-2" data-bs-toggle="tooltip" data-original-title="{{ __('Checklist') }}">
                                                        <i class="ti ti-list-check mr-2"></i>{{ $taskChecklistCnt }}
                                                    </a>
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
    </div>
</div>
@push(StacksConstants::ADM_SCR_PG)
    <script>
        (() => {
            try {
                const nodes = document.querySelectorAll('[data-bs-toggle="tooltip"]');
                if (!nodes || !nodes.length) return;
                for (let i = 0; i < nodes.length; i++) {
                    try {
                        const el = nodes[i];
                        const flag = 'data-tooltip-init';
                        if (el.hasAttribute(flag) && el.getAttribute(flag) === 'true') continue;
                        el.setAttribute(flag, 'true');
                        const hasBootstrap = document.querySelector('link[href*="bootstrap"]') && window.bootstrap && window.bootstrap.Tooltip);
                        if (!hasBootstrap) continue;
                        window.bootstrap.Tooltip.getOrCreateInstance(el);
                    } catch (innerErr) {}
                }
            } catch (error) {}
        })();
    </script>
@endpush
