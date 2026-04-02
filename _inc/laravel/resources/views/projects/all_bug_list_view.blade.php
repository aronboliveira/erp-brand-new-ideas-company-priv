@php
    try {
$user = Auth::user();
        $lang = Utility::fetchUserLang(user:$user);

            if (!function_exists('resolveBugListRoute')) {
            function resolveBugListRoute($baseName) {
                $kebab = Str::kebab($baseName);
                return Route::has($baseName) ? $baseName : (Route::has($kebab) ? $kebab : null);
            }
        }

            if (!function_exists('safeBugListRoute')) {
            function safeBugListRoute($routeName, $params = []) {
                return $routeName ? route($routeName, $params) : '#';
            }
        }
    } catch (\Throwable $e) {
        \Log::error('projects/all_bug_list_view — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    }
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@section(YieldingConstants::ADM_PG_TTL)
    {{ __('Manage Bug Report') }}
@endsection

@section(YieldingConstants::ADM_BDC)
    <li class="{{ VC::BCI }}"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="{{ VC::BCI }}">{{ __('Project') }}</li>
    <li class="{{ VC::BCI }}">{{ __('Bug Report') }}</li>
@endsection

@section(YieldingConstants::ADM_ACT_BTN)
    @php
        try {
            $otherView = $view === 'grid' ? 'list' : 'grid';
            $icon      = $view === 'grid' ? 'ti-list' : 'ti-table';
            $title     = $view === 'grid' ? __('List View') : __('Card View');
        } catch (\Throwable $e) {
            \Log::error('projects/all_bug_list_view — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
        }
@endphp
    <div class="{{ VC::FEND }}">
        <a href="{{ route(ViewsConstants::PRJ_BUG . '.view', $otherView) }}"
        class="{{ VC::BT_SM_PM }}"
        data-bs-toggle="tooltip"
        title="{{ $title }}">
            <span class="btn-inner--text"><i class="ti {{ $icon }}"></i></span>
        </a>
        @can(PermissionsConstants::MNG_PRJ)
          @php
              try {
                  $projectIndexBaseName     = ViewsConstants::PRJ.'.index';
                  $projectIndexKebabName    = Str::kebab($projectIndexBaseName);
                  $projectIndexResolvedName = Route::has($projectIndexBaseName)
                      ? $projectIndexBaseName
                      : (Route::has($projectIndexKebabName) ? $projectIndexKebabName : null);
                  $projectIndexUrl          = $projectIndexResolvedName ? route($projectIndexResolvedName) : '#';
                  $projectIndexGuardMsg     = Utility::fetchLinkMessage($lang, ViewsConstants::PRJ, 'project_index_route_unavailable') ?? 'Project index route is unavailable. Please contact technical support or your domain administrator.';
                  $projectIndexLinkId       = 'project-index-back-link';
                  $projectIndexTitle        = __('Back');
              } catch (\Throwable $e) {
                  \Log::error('projects/all_bug_list_view — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
              }
@endphp
          <a href="{{ $projectIndexUrl }}"
            data-route-guard
            class="{{ VC::BT_SM_PM }}"
            data-url="{{ $projectIndexUrl }}"
            data-guard-msg="{{ base64_encode($projectIndexGuardMsg) }}"
            data-bs-toggle="tooltip"
            title="{{ $projectIndexTitle }}">
              <span class="btn-inner--icon">
                  <i class="{{ VC::TI }} ti-arrow-left"></i>
              </span>
          </a>
        @endcan
    </div>
@endsection

@section(YieldingConstants::ADM_CTT)
    <div class="{{ VC::RW }}">
        <div class="{{ VC::CM10 }} {{ VC::CS12 }}">
            <div class="{{ VC::CD }}">
                <div class="{{ VC::CD_BD_TB_BD }}">
                    <div class="{{ VC::TB_RSP }}">
                        <table class="{{ VC::TB_AL }}">
                            <thead>
                                <tr>
                                    <th scope="col">{{ __('Name') }}</th>
                                    <th scope="col">{{ __('Bug Status') }}</th>
                                    <th scope="col">{{ __('Priority') }}</th>
                                    <th scope="col">{{ __('End Date') }}</th>
                                    <th scope="col">{{ __('created By') }}</th>
                                    <th scope="col">{{ __('Assigned To') }}</th>
                                    <th scope="col"></th>
                                </tr>
                            </thead>
                            <tbody class="list">
                                @if (isset($bugs) && (is_countable($bugs) && count($bugs) > 0 || $bugs instanceof Collection && $bugs->count() > 0))
                                    @foreach ($bugs as $bug)
                                        @if(isset($bug) && is_object($bug))
                                            @php

                                                $checkProject = __('N/A');
                                                $projectId = data_get($bug, 'project_id');

                                                if (isset($user) && is_object($user) && method_exists($user, 'checkProject') && !empty($projectId)) {
                                                    try {
                                                        $checkProject = $user->checkProject($projectId);
                                                        $checkProject = !empty($checkProject) ? e($checkProject) : __('N/A');
                                                    } catch (Exception $e) {
                                                        $checkProject = __('N/A');
                                                    }
                                                }
@endphp
                                            <tr>
                                                <td>
                                                    <span class="{{ VC::H6 }} {{ VC::TXSM }} {{ VC::FW600 }} {{ VC::MB0 }}">
                                                        @if(!empty($projectId))
                                                            @php
                                                                try {
                                                                    $bugBaseName     = ViewsConstants::PRJ_TSK_BUG;
                                                                    $bugKebabName    = Str::kebab($bugBaseName);
                                                                    $bugResolvedName = Route::has($bugBaseName)
                                                                        ? $bugBaseName
                                                                        : (Route::has($bugKebabName) ? $bugKebabName : null);
                                                                    $projectId       = (isset($projectId) && !empty($projectId))
                                                                        ? $projectId
                                                                        : ((isset($bug) && !empty($bug->project_id)) ? $bug->project_id : null);
                                                                    $bugUrl          = ($bugResolvedName && $projectId) ? route($bugResolvedName, $projectId) : '#';
                                                                    $bugGuardMsg     = Utility::fetchLinkMessage($lang, ViewsConstants::PRJ_TSK_BUG, 'access_bug_route_unavailable') ?? 'Access bug route is unavailable. Please contact technical support or your domain administrator.';
                                                                    $bugTitle        = e(data_get($bug, 'title', __('Untitled Bug')));
                                                                } catch (\Throwable $e) {
                                                                    \Log::error('projects/all_bug_list_view — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                                }
@endphp
                                                            <a href="{{ $bugUrl }}"
                                                            data-route-guard
                                                            data-url="{{ $bugUrl }}"
                                                            data-guard-msg="{{ base64_encode($bugGuardMsg) }}">
                                                                {{ $bugTitle }}
                                                            </a>
                                                        @else
                                                            {{ e(data_get($bug, 'title', __('Untitled Bug'))) }}
                                                        @endif
                                                    </span>
                                                    <span class="{{ VC::DFL_JCB }} {{ VC::TXSM }} {{ VC::TXT_MT }}">
                                                        @php
                                                            $projectName ??= '';
                                                            try {
                                                                $project = data_get($bug, 'project');
                                                                if (isset($project) && is_object($project)) {
                                                                    $projectName = data_get($project, 'project_name', '');
                                                                }
                                                            } catch (\Throwable $e) {
                                                                \Log::error('projects/all_bug_list_view — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                            }
@endphp
                                                        @if(!empty($projectName))
                                                            <p class="{{ VC::MB0 }}">{{ e($projectName) }}</p>
                                                        @else
                                                            <p class="{{ VC::MB0 }}">{{ __('No Project') }}</p>
                                                        @endif
                                                        <span class="me-5 {{ VC::BDG }} p-2 px-3 rounded bg-{{ $checkProject === 'Owner' ? 'success' : 'warning' }}">
                                                            {{ __($checkProject) }}
                                                        </span>
                                                    </span>
                                                </td>
                                                <td>
                                                    @php
                                                        try {
                                                            $bugStatus = data_get($bug, 'bug_status');
                                                            $statusTitle = '';
                                                            if (isset($bugStatus) && is_object($bugStatus)) {
                                                                $statusTitle = data_get($bugStatus, 'title', '');
                                                            }
                                                        } catch (\Throwable $e) {
                                                            \Log::error('projects/all_bug_list_view — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                        }
@endphp
                                                    @if(!empty($statusTitle))
                                                        {{ e($statusTitle) }}
                                                    @else
                                                        {{ __('No Status') }}
                                                    @endif
                                                </td>
                                                <td>
                                                    @php
                                                        $priority = data_get($bug, 'priority');
                                                        $priorityColor = 'secondary';
                                                        $priorityText = __('Unknown');
                                                        if (!empty($priority) && class_exists('ProjectTask')) {
                                                            try {
                                                                if (isset(ProjectTask::$priority_color[$priority])) {
                                                                    $priorityColor = ProjectTask::$priority_color[$priority];
                                                                }
                                                                if (isset(ProjectTask::$priority[$priority])) {
                                                                    $priorityText = __(ProjectTask::$priority[$priority]);
                                                                }
                                                            } catch (Exception $e) {
                                                                                                                            }
                                                        }
@endphp
                                                    <span class="status_badge {{ VC::BDG }} p-2 px-3 rounded bg-{{ $priorityColor }}">
                                                        {{ $priorityText }}
                                                    </span>
                                                </td>
                                                <td class="{{ !empty(data_get($bug, 'due_date')) && strtotime(data_get($bug, 'due_date')) < time() ? 'text-danger' : '' }}">
                                                    @php
                                                        $dueDate = data_get($bug, 'due_date');
                                                        $formattedDate = __('No Due Date');
                                                        if (!empty($dueDate) && class_exists('Utility') && method_exists('Utility', 'getDateFormated')) {
                                                            try {
                                                                $formattedDate = Utility::getDateFormated($dueDate);
                                                            } catch (Exception $e) {
                                                                $formattedDate = e($dueDate);
                                                            }
                                                        } elseif (!empty($dueDate))
                                                            $formattedDate = e($dueDate);
@endphp
                                                    {{ $formattedDate }}
                                                </td>
                                                <td>
                                                    <div class="{{ VC::DFL_AIC }}">
                                                        @php
                                                            try {
                                                                $createdBy = data_get($bug, 'createdBy');
                                                                $creatorName = '';
                                                                if (isset($createdBy) && is_object($createdBy))
                                                                    $creatorName = data_get($createdBy, 'name', '');
                                                            } catch (\Throwable $e) {
                                                                \Log::error('projects/all_bug_list_view — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                            }
@endphp
                                                        @if(!empty($creatorName))
                                                            {{ e($creatorName) }}
                                                        @else
                                                            {{ __('Unknown User') }}
                                                        @endif
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="avatar-group">
                                                        @php

                                                            $assignees ??= [];
                                                            if (method_exists($bug, 'users')) {
                                                                try {
                                                                    $assignees = $bug->users();
                                                                    $assignees = is_object($assignees) || is_array($assignees) ? $assignees : [];
                                                                } catch (Exception $e) {
                                                                    $assignees = [];
                                                                }
                                                            }
@endphp
                                                        @if (!empty($assignees) && ((is_countable($assignees) && count($assignees) > 0) || (is_object($assignees) && method_exists($assignees, 'count') && $assignees->count() > 0)))
                                                            @php
                                                                try {
                                                                    $assigneesToShow = is_object($assignees) && method_exists($assignees, 'take')
                                                                        ? $assignees->take(3)
                                                                        : (is_array($assignees) ? array_slice($assignees, 0, 3) : []);

                                                                    $totalAssignees = is_object($assignees) && method_exists($assignees, 'count')
                                                                        ? $assignees->count()
                                                                        : (is_countable($assignees) ? count($assignees) : 0);
                                                                } catch (\Throwable $e) {
                                                                    \Log::error('projects/all_bug_list_view — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                                }
@endphp
                                                            @foreach ($assigneesToShow as $assignee)
                                                                @if(isset($assignee) && is_object($assignee))
                                                                    @php
                                                                        try {
                                                                            $assigneeName = data_get($assignee, 'name', __('User'));
                                                                            $avatar = data_get($assignee, 'avatar');
                                                                            $avatarPath = !empty($avatar)
                                                                                ? asset('/storage/uploads/avatar/' . $avatar)
                                                                                : asset('/storage/uploads/avatar/avatar.png');
                                                                        } catch (\Throwable $e) {
                                                                            \Log::error('projects/all_bug_list_view — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                                        }
@endphp
                                                                    <a href="#" class="{{ VC::AV_CC_SM }}" title="{{ e($assigneeName) }}">
                                                                        <img
                                                                            src="{{ $avatarPath }}"
                                                                            alt="{{ e($assigneeName) }}"
                                                                            class="hweb"
                                                                            data-fallback-src="{{ asset('/storage/uploads/avatar/avatar.png') }}"
                                                                        >
                                                                    </a>
                                                                @endif
                                                            @endforeach
                                                            @if ($totalAssignees > 3)
                                                                <a href="#" class="{{ VC::AV_CC_SM }}" title="+{{ $totalAssignees - 3 }} {{ __('more') }}">
                                                                    <span class="{{ VC::TXT_WT }} {{ VC::FW600 }}">+{{ $totalAssignees - 3 }}</span>
                                                                </a>
                                                            @endif
                                                        @else
                                                            {{ __('No Assignees') }}
                                                        @endif
                                                    </div>
                                                </td>
                                                <td class="{{ VC::TX_END }} w-15">
                                                    <div class="actions">
                                                        @php
                                                            $bugFiles = data_get($bug, 'bugFiles', []);
                                                            $bugFilesCount = is_countable($bugFiles) ? count($bugFiles) : 0;
@endphp
                                                        <a class="action-item px-1" data-bs-toggle="tooltip" title="{{ __('Attachment') }}">
                                                            <i class="{{ VC::TI }} ti-paperclip {{ VC::MR2 }}"></i>{{ $bugFilesCount }}
                                                        </a>
                                                        @php
                                                            $comments = data_get($bug, 'comments', []);
                                                            $commentsCount = is_countable($comments) ? count($comments) : 0;
@endphp
                                                        <a class="action-item px-1" data-bs-toggle="tooltip" title="{{ __('Comment') }}">
                                                            <i class="{{ VC::TI }} ti-brand-hipchat {{ VC::MR2 }}"></i>{{ $commentsCount }}
                                                        </a>
                                                        <a class="action-item px-1" data-bs-toggle="tooltip" title="{{ __('Checklist') }}"></a>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endif
                                    @endforeach
                                @else
                                    <tr>
                                        <th scope="col" colspan="7">
                                            <h6 class="{{ VC::TXCT }}">{{ __('No tasks found') }}</h6>
                                        </th>
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
@push(StacksConstants::ADM_SCR_PG)
    <script>
    if (typeof window.AllBugListHandler === 'undefined') {
        window.AllBugListHandler = {
            debounceMap: new Map(),

            init() {
                document.querySelectorAll('[data-route-guard]').forEach(el => this.attachClickHandler(el));
            },

            attachClickHandler(el) {
                const elId = el.getAttribute('data-url') || Math.random();
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
        document.readyState === 'loading' ? document.addEventListener('DOMContentLoaded', () => window.AllBugListHandler.init()) : window.AllBugListHandler.init();
    }
    </script>
@endpush
