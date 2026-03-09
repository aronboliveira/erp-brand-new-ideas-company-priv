@php
    try {
$lang = Utility::fetchUserLang();
        $milestoneStatus = data_get($milestone, 'status');
        $milestoneDesc = data_get($milestone, 'description');
        $statusColor = Project::$status_color[$milestoneStatus] ?? 'secondary';
        $statusLabel = Project::$project_status[$milestoneStatus] ?? __('Could not retrieve milestone status.');
        $tasks = data_get($milestone, 'tasks', []);
    } catch (\Throwable $e) {
        \Log::error('projects/milestone_show — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    }
@endphp
<div class="modal-body">
    <div class="p-2">
        <div class="row {{ VC::MB4 }}">
            <div class="{{ VC::CM6 }}">
                <span class="font-bold lab-title">{{ __('Status') }} : </span>
                <span class="badge-xs badge p-2 {{ VC::PX3 }} rounded bg-{{ $statusColor }} {{ VC::TXT_WT }}">{{ __($statusLabel) }}</span>
            </div>
            <div class="{{ VC::CM12 }} pt-4">
                <div class="font-weight-bold lab-title">{{ __('Description') }} :</div>
                <p class="{{ VC::MT1 }} lab-val">{{ !empty($milestoneDesc) ? $milestoneDesc : '-' }}</p>
            </div>
            <div class="{{ VC::C12 }}">
                <div class="table-border-style">
                    <div class="{{ VC::TB_RSP }}">
                        @php
 $hasTasks = isset($tasks) && !empty($tasks) && is_countable($tasks) && count($tasks) > 0;
@endphp
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
                                            try {
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
                                            } catch (\Throwable $e) {
                                                \Log::error('projects/milestone_show — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                            }
@endphp
                                        <tr>
                                            <td><span class="h6 {{ VC::TXSM }}">{{ $taskName }}</span></td>
                                            <td>{{ $taskStage }}</td>
                                            <td><span class="badge p-2 {{ VC::PX3 }} rounded badge-sm bg-{{ $taskPriorityColor }}">{{ __($taskPriorityLabel) }}</span></td>
                                            <td class="{{ $endIsPast ? 'text-danger' : '' }}">{{ Utility::getDateFormated($taskEnd) }}</td>
                                            <td>
                                                @if($tpPctNum > 0)
                                                    <span class="{{ VC::TXSM }}">{{ $tpPct }}</span>
                                                    <div class="progress" style="top:0px;">
                                                        <div class="progress-bar bg-{{ $tpColor }}" role="progressbar" style="width: {{ $tpPct }};"></div>
                                                    </div>
                                                @endif
                                            </td>
                                            <td class="{{ VC::TX_END }} w-15">
                                                <div class="actions">
                                                    @php
                                                        try {
                                                            $attachId = 'task-attach-' . (data_get($task, 'id') ?? 'x');
                                                            $commentId = 'task-comment-' . (data_get($task, 'id') ?? 'x');
                                                            $checklistId = 'task-checklist-' . (data_get($task, 'id') ?? 'x');
                                                        } catch (\Throwable $e) {
                                                            \Log::error('projects/milestone_show — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                        }
@endphp
                                                    <a id="{{ $attachId }}" class="action-item px-2" data-bs-toggle="tooltip" data-original-title="{{ __('Attachment') }}">
                                                        <i class="ti ti-paperclip {{ VC::MR2 }}"></i>{{ $taskFilesCnt }}
                                                    </a>
                                                    <a id="{{ $commentId }}" class="action-item px-2" data-bs-toggle="tooltip" data-original-title="{{ __('Comment') }}">
                                                        <i class="ti ti-brand-hipchat {{ VC::MR2 }}"></i>{{ $taskCommentsCnt }}
                                                    </a>
                                                    <a id="{{ $checklistId }}" class="action-item px-2" data-bs-toggle="tooltip" data-original-title="{{ __('Checklist') }}">
                                                        <i class="ti ti-list-check {{ VC::MR2 }}"></i>{{ $taskChecklistCnt }}
                                                    </a>
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
