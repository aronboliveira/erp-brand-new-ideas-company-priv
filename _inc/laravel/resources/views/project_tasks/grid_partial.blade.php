{{-- Grid partial — AJAX-only fragment for taskBoardView() --}}
@php
    try {
        $lang = Utility::fetchUserLang();
        $tasksSafe = (isset($tasks) && (is_array($tasks) || $tasks instanceof \Illuminate\Support\Collection)) ? $tasks : [];
    } catch (\Throwable $e) {
        \Log::error('project_tasks/grid_partial — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
        $tasksSafe = [];
    }
@endphp
<div class="{{ VC::RW }}">
    <div class="{{ VC::C12 }}">
        <div class="{{ VC::CD }}">
            <div class="{{ VC::RW }}">
                @if(count($tasksSafe) > 0)
                    @foreach($tasksSafe as $task)
                        @php
                            try {
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
                                $usersArr = Utility::isFilled($usersRel ?? []) ? $usersRel : [];
                                $usersCount = is_countable($usersArr) ? count($usersArr) : 0;
                                $taskIndexBase = VW::PRJ_TSK_C.'.index';
                                $taskIndexKebab = Str::kebab($taskIndexBase);
                                $taskIndexResolved = Route::has($taskIndexBase) ? $taskIndexBase : (Route::has($taskIndexKebab) ? $taskIndexKebab : null);
                                $projIdValue = isset($projectId) ? $projectId : null;
                                $taskIndexUrl = ($taskIndexResolved && $projIdValue) ? route($taskIndexResolved, $projIdValue) : '#';
                                $taskIndexGuardMsg = Utility::fetchLinkMessage($lang, VW::PRJ_TSK_C, 'index_project_task_route_unavailable') ?? 'Index project task route is unavailable. Please contact technical support or your domain administrator.';
                                $taskIndexAnchorId = 'project-task-index-'.($projIdValue ?? 'x').'-'.Str::slug($taskName ?? 'task','-');
                            } catch (\Throwable $e) {
                                \Log::error('project_tasks/grid_partial — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                            }
                        @endphp
                        <div class="{{ VC::CLMS3 }}">
                            <div class="{{ VC::CD_NSD }} m-3 card-progress" id="{{ $tid ?? '' }}" style="{{ !empty($borderColor) ? 'border-left: 2px solid '.$borderColor.' !important' : '' }};">
                                <div class="{{ VC::CD_BD }}">
                                    <div class="{{ VC::R_ALC }} mb-2">
                                        <div class="{{ VC::C6 }}">
                                            <span class="{{ VC::BDG }} p-2 {{ VC::PX3 }} rounded bg-{{ $priorityColorKey }}">{{ __($priorityLabel) }}</span>
                                        </div>
                                        <div class="{{ VC::C6 }} {{ VC::TX_END }}">
                                            @if($pctNum > 0)
                                                <span class="{{ VC::TXSM }}">{{ $pctStr }}</span>
                                                <div class="{{ VC::PG }}" style="top:0px">
                                                    <div class="progress-bar bg-{{ $pctColor }}" role="progressbar" style="width: {{ $pctStr }};"></div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                    <a id="{{ $taskIndexAnchorId }}"
                                       class="{{ VC::H6 }} task-name-break"
                                       href="{{ $taskIndexUrl }}"
                                       data-url="{{ $taskIndexUrl }}"
                                       data-guard-msg="{{ base64_encode($taskIndexGuardMsg) }}"
                                       data-sv-localized="true">
                                        {{ $taskName }}
                                    </a>
                                    <div class="{{ VC::R_ALC }}">
                                        <div class="{{ VC::C12 }}">
                                            <div class="actions {{ VC::DFL_JCB }} mt-2 mb-2">
                                                @if($filesCount > 0)
                                                    <div class="action-item {{ VC::MR2 }}"><i class="ti ti-paperclip {{ VC::MR2 }}"></i>{{ $filesCount }}</div>
                                                @else
                                                    <div class="action-item {{ VC::MR2 }}"><i class="ti ti-paperclip {{ VC::MR2 }}"></i>{{ __('No files found') }}</div>
                                                @endif
                                                @if($commentsCount > 0)
                                                    <div class="action-item {{ VC::MR2 }}"><i class="ti ti-brand-hipchat {{ VC::MR2 }}"></i>{{ $commentsCount }}</div>
                                                @else
                                                    <div class="action-item {{ VC::MR2 }}"><i class="ti ti-brand-hipchat {{ VC::MR2 }}"></i>{{ __('No comments found') }}</div>
                                                @endif
                                                @if($checklistCount > 0)
                                                    <div class="action-item {{ VC::MR2 }}"><i class="ti ti-list-check {{ VC::MR2 }}"></i>{{ $checklistTotal }}</div>
                                                @else
                                                    <div class="action-item {{ VC::MR2 }}"><i class="ti ti-list-check {{ VC::MR2 }}"></i>{{ __('No checklist items found') }}</div>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="{{ VC::C6 }}">
                                            @if($endDateText)
                                                <small @if($isOverdue) class="{{ VC::TX_DNG }}" @endif>{{ $endDateText }}</small>
                                            @endif
                                        </div>
                                        <div class="{{ VC::C6 }} {{ VC::TX_END }}">
                                            @if($usersCount > 0)
                                                <div class="avatar-group">
                                                    @foreach($usersArr as $key => $u)
                                                        @if($key < 3)
                                                            @php
                                                                try {
                                                                    $uName = data_get($u,'name') ?? '';
                                                                    $uAvatar = data_get($u,'avatar');
                                                                    $uSrc = !empty($uAvatar) ? asset('/storage/uploads/avatar/'.$uAvatar) : asset('/storage/uploads/avatar/avatar.png');
                                                                } catch (\Throwable $e) {
                                                                    \Log::error('project_tasks/grid_partial — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                                }
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
                        <h6 class="{{ VC::TXCT }} m-3">{{ __('No tasks found') }}</h6>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
