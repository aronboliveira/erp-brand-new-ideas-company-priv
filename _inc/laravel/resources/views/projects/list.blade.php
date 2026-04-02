@php
$user = Auth::user();
    $lang = Utility::fetchUserLang(user:$user);

        if (!function_exists('resolveProjectRoute')) {
        function resolveProjectRoute($base, $kebabFallback = true) {
            if (Route::has($base)) return $base;
            if ($kebabFallback) {
                $kebab = Str::kebab($base);
                if (Route::has($kebab)) return $kebab;
            }
            return null;
        }
    }

        if (!function_exists('safeProjectRoute')) {
        function safeProjectRoute($routeName, $params = []) {
            $resolved = resolveProjectRoute($routeName);
            if (!$resolved || (is_array($params) && empty($params[0]))) return '#';
            try {
                return route($resolved, $params);
            } catch (\Exception $e) {
                return '#';
            }
        }
    }
@endphp

<div class="{{ VC::CXL12 }}">
    <div class="card">
        <div class="{{ VC::CD_BD_TB_BD }}">
            <div class="{{ VC::TB_RSP }}">
                <table class="{{ VC::TB }} datatable">
                    <thead>
                        <tr>
                            <th>{{ __('Project') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th>{{ __('Users') }}</th>
                            <th>{{ __('Completion') }}</th>
                            <th class="{{ VC::TX_END }}">{{ __('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if(isset($projects) && !empty($projects) && count($projects) > 0)
                            @foreach ($projects as $key => $project)
                                @if(isset($project) && is_object($project))
                                    @php
                                                                                try {
                                                                                    $projectId = data_get($project, 'id');
                                                                                                                            $projectName = data_get($project, 'project_name') ?: data_get($project, 'name') ?: __('Unnamed Project');
                                                                                                                            $projectSlug = $projectId ? 'prj-' . $projectId : 'prj-x-' . $key;

                                                                                    $showUrl = safeProjectRoute(VW::PRJ . '.show', [$projectId]);
                                                                                                                            $copyUrl = safeProjectRoute(VW::PRJ . '.copy', [$projectId]);
                                                                                                                            $editUrl = safeProjectRoute(VW::PRJ . '.edit', [$projectId]);
                                                                                                                            $inviteUrl = safeProjectRoute(VW::PRJ . '.invite.member.view', [$projectId]);
                                                                                                                            $destroyUrl = safeProjectRoute(VW::PRJ . '.user.destroy', [$projectId, $user?->id]);

                                                                                    $showLinkId = 'project-show-link-' . $projectSlug;
                                                                                                                            $copyLinkId = 'project-copy-link-' . $projectSlug;
                                                                                                                            $editLinkId = 'project-edit-link-' . $projectSlug;
                                                                                                                            $deleteLinkId = 'project-delete-link-' . $projectSlug;
                                                                                                                            $deleteFormId = 'project-delete-form-' . $projectSlug;
                                                                                                                            $inviteLinkId = 'project-invite-link-' . $projectSlug;

                                                                                    $messages = [
                                                                                                                                'show' => Utility::fetchLinkMessage($lang, VW::PRJ, 'show_project_route_unavailable') ?? __('Show project route unavailable. Please contact support.'),
                                                                                                                                'copy' => Utility::fetchLinkMessage($lang, VW::PRJ, 'copy_project_unavailable') ?? __('Copy project route unavailable. Please contact support.'),
                                                                                                                                'edit' => Utility::fetchLinkMessage($lang, VW::PRJ, 'project_edit_route_unavailable') ?? __('Edit project route unavailable. Please contact support.'),
                                                                                                                                'delete' => Utility::fetchLinkMessage($lang, VW::PRJ, 'delete_user_project_unavailable') ?? __('Delete project route unavailable. Please contact support.'),
                                                                                                                                'invite' => Utility::fetchLinkMessage($lang, VW::PRJ, 'invite_project_member_unavailable') ?? __('Invite member route unavailable. Please contact support.'),
                                                                                                                                'confirm' => Utility::fetchLinkMessage($lang, 'generics', 'are_you_sure') ?? __('Are You Sure?'),
                                                                                                                                'irreversible' => Utility::fetchLinkMessage($lang, 'generics', 'irreversible_action') ?? __('This action cannot be undone. Do you want to continue?'),
                                                                                                                            ];

                                                                                    $lastTaskId = data_get($last_task ?? null, 'id');
                                                                                                                            $progress = method_exists($project, 'projectProgress') ? $project->projectProgress($project, $lastTaskId) : [];
                                                                                                                            $progressPct = data_get($progress, 'percentage', '0%');
                                                                                                                            $progressColor = data_get($progress, 'color', 'primary');
                                                                                } catch (\Throwable $e) {
                                                                                    \Log::error('projects/list — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                                                }
@endphp
                                    <tr>
                                        <td>
                                            <div class="{{ VC::DFL_AIC }}">
                                                @if(isset($project->img_image) && !empty($project->img_image))
                                                    <img {{ $project->img_image }} class="wid-40 rounded {{ VC::ME3 }}">
                                                @else
                                                    <img src="{{ asset('default-project-image.png') }}" class="wid-40 rounded {{ VC::ME3 }}" alt="">
                                                @endif
                                                <p class="{{ VC::MB0 }}">
                                                    <a href="{{ $showUrl }}"
                                                    id="{{ $showLinkId }}"
                                                    data-url="{{ $showUrl }}"
                                                    data-guard-msg="{{ base64_encode($messages['show']) }}"
                                                    data-project-id="{{ $projectId }}"
                                                    class="{{ VC::NM_HD_SM }} project-show-link"
                                                    data-route-guard>
                                                        {{ $projectName }}
                                                    </a>
                                                </p>
                                            </div>
                                        </td>
                                        <td>
                                            @if(isset($project->status) &&
                                                isset(Project::$status_color) &&
                                                isset(Project::$project_status) &&
                                                is_array(Project::$status_color) &&
                                                is_array(Project::$project_status))
                                                @php
                                                    $statusColor = data_get(Project::$status_color, $project->status, 'secondary');
                                                    $statusText = data_get(Project::$project_status, $project->status, 'Unknown');
@endphp
                                                <span class="badge bg-{{ $statusColor }} p-2 {{ VC::PX3 }} rounded">{{ __($statusText) }}</span>
                                            @else
                                                <span class="badge bg-secondary p-2 {{ VC::PX3 }} rounded">{{ __('Unknown Status') }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="avatar-group" id="project_{{ !empty($projectId) ? $projectId : 'unknown' }}">
                                                @if(isset($project->users) &&
                                                    (is_array($project->users) || is_object($project->users)) &&
                                                    !empty($project->users) &&
                                                    count($project->users) > 0)
                                                    @foreach($project->users as $uKey => $u)
                                                        @if(is_numeric($uKey) && $uKey < 3 && isset($u) && is_object($u))
                                                            <a href="#" class="{{ VC::AV_CC }}">
                                                                <img src="{{ isset($u->avatar) && !empty($u->avatar) && is_string($u->avatar)
                                                                    ? asset('/storage/uploads/avatar/' . $u->avatar)
                                                                    : asset('/storage/uploads/avatar/avatar.png') }}"
                                                                    title="{{ data_get($u, 'name', 'Unknown User') }}"
                                                                    style="height:36px;width:36px;"
                                                                    alt="user avatar"
                                                                    data-fallback-src="{{ asset('/storage/uploads/avatar/avatar.png') }}">
                                                            </a>
                                                        @elseif($uKey >= 3)
                                                            @break
                                                        @endif
                                                    @endforeach
                                                    @if(count($project->users) > 3)
                                                        <a href="#" class="{{ VC::AV_CC_SM }}">
                                                            <div class="avatar-placeholder {{ VC::DFL_AIC }} {{ VC::JCC }} {{ VC::BG_P }} {{ VC::TXT_WT }} rounded-circle"
                                                                style="height:36px;width:36px;font-size:12px;">
                                                                + {{ count($project->users) - 3 }}
                                                            </div>
                                                        </a>
                                                    @endif
                                                @else
                                                    <span class="{{ VC::TXT_MT }}">{{ __('No users could be found for the project.') }}</span>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="{{ VC::TX_END }}">
                                            @if(!empty($progressPct) && is_string($progressPct))
                                                <h5 class="{{ VC::MB0 }} text-success">{{ $progressPct }}</h5>
                                                <div class="progress {{ VC::MB0 }}">
                                                    <div class="progress-bar bg-{{ !empty($progressColor) ? $progressColor : 'primary' }}"
                                                        style="width: {{ $progressPct }};"></div>
                                                </div>
                                            @else
                                                <h5 class="{{ VC::MB0 }} {{ VC::TXT_MT }}">{{ __('N/A') }}</h5>
                                                <div class="progress {{ VC::MB0 }}">
                                                    <div class="progress-bar bg-secondary" style="width: 0%;"></div>
                                                </div>
                                            @endif
                                        </td>
                                        <td class="{{ VC::TX_END }}">
                                            <span>
                                                @can('edit project')
                                                    @if($inviteUrl !== '#')
                                                        <div class="{{ VC::ACT_BTN_WRN }} ms-2">
                                                            <a href="{{ $inviteUrl }}"
                                                            id="{{ $inviteLinkId }}"
                                                            class="{{ VC::BT_SM_FL_CT }} project-action-link"
                                                            data-url="{{ $inviteUrl }}"
                                                            data-ajax-popup="true"
                                                            data-size="lg"
                                                            data-action="invite"
                                                            data-project-id="{{ $projectId }}"
                                                            data-bs-toggle="tooltip"
                                                            title="{{ __('Invite User') }}"
                                                            data-title="{{ __('Invite User') }}"
                                                            data-guard-msg="{{ base64_encode($messages['invite']) }}"
                                                            data-route-guard>
                                                                <i class="ti ti-send {{ VC::TXT_WT }}"></i>
                                                            </a>
                                                        </div>
                                                    @endif
                                                @endcan
                                                @can('edit project')
                                                    @if($editUrl !== '#')
                                                        <div class="{{ VC::ACT_BTN_INF }} ms-2">
                                                            <a href="{{ $editUrl }}"
                                                            id="{{ $editLinkId }}"
                                                            class="{{ VC::BT_SM_FL_CT }} project-action-link"
                                                            data-url="{{ $editUrl }}"
                                                            data-ajax-popup="true"
                                                            data-size="lg"
                                                            data-action="edit"
                                                            data-project-id="{{ $projectId }}"
                                                            data-bs-toggle="tooltip"
                                                            title="{{ __('Edit') }}"
                                                            data-title="{{ __('Edit Project') }}"
                                                            data-guard-msg="{{ base64_encode($messages['edit']) }}"
                                                            data-route-guard>
                                                                <i class="{{ VC::TI_PC_WT }}"></i>
                                                            </a>
                                                        </div>
                                                    @endif
                                                @endcan
                                                @can('delete project')
                                                    @if($destroyUrl !== '#')
                                                        <div class="{{ VC::ACT_BTN_DNG_2 }}">
                                                            {!! Form::open(['method' => 'DELETE', 'url' => $destroyUrl, 'id' => $deleteFormId]) !!}
                                                                <a href="#!"
                                                                id="{{ $deleteLinkId }}"
                                                                class="{{ VC::BT_SM_CT_PR }} project-action-link"
                                                                data-url="{{ $destroyUrl }}"
                                                                data-action="delete"
                                                                data-project-id="{{ $projectId }}"
                                                                data-form-id="{{ $deleteFormId }}"
                                                                data-guard-msg="{{ base64_encode($messages['delete']) }}"
                                                                data-bs-toggle="tooltip"
                                                                title="{{ __('Delete') }}"
                                                                data-confirm="{{ $messages['confirm'] }}|{{ $messages['irreversible'] }}"
                                                                data-confirm-yes="document.getElementById('{{ $deleteFormId }}').submit();"
                                                                data-route-guard>
                                                                    <i class="ti ti-trash {{ VC::TXT_WT }}"></i>
                                                                </a>
                                                            {!! Form::close() !!}
                                                        </div>
                                                    @endif
                                                @endcan
                                                @can('create project')
                                                    @if($copyUrl !== '#')
                                                        <div class="{{ VC::ACT_BTN_PRIM }}">
                                                            <a href="{{ $copyUrl }}"
                                                            id="{{ $copyLinkId }}"
                                                            class="{{ VC::BT_SM_CT }} project-action-link"
                                                            data-url="{{ $copyUrl }}"
                                                            data-ajax-popup="true"
                                                            data-size="md"
                                                            data-action="copy"
                                                            data-project-id="{{ $projectId }}"
                                                            data-title="{{ __('Duplicate Project') }}"
                                                            data-bs-toggle="tooltip"
                                                            title="{{ __('Duplicate') }}"
                                                            data-guard-msg="{{ base64_encode($messages['copy']) }}"
                                                            data-route-guard>
                                                                <i class="ti ti-copy {{ VC::TXT_WT }}"></i>
                                                            </a>
                                                        </div>
                                                    @endif
                                                @endcan
                                            </span>
                                        </td>
                                    </tr>
                                @endif
                            @endforeach
                        @else
                            <tr>
                                <th scope="col" colspan="7"><h6 class="{{ VC::TXCT }}">{{ __('No Projects Found.') }}</h6></th>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@push(StacksConstants::ADM_SCR_PG)
    <script defer>
        (() => {
            'use strict';

            const ProjectListGuardHandler = {
                initialized: false,
                debounceMap: new Map(),

                // DRY: Centralized toast notification using RouteGuard
                showToast(message) {
                    (window.RouteGuard?.showToast || (m => alert(m)))(message);
                },

                // Route guard with debouncing
                guardRoute(el) {
                    try {
                        if (!el) return true;
                        const href = el.getAttribute('href') || '#';
                        const url = el.getAttribute('data-url') || href || '#';

                        if (href !== '#' && url !== '#') return false;

                        const msg = el.getAttribute('data-guard-msg') ||
                                  '{{ __('Route is unavailable. Please contact support.') }}';
                        this.showToast(msg);
                        el.setAttribute('data-failed-route', 'true');
                        return true;
                    } catch (err) {
                        console.error('[ProjectListGuard] Guard error:', err);
                        return true;
                    }
                },

                // Attach click handler with debouncing
                attachClickHandler(el) {
                    try {
                        if (!el) return;
                        const flagName = 'data-route-listener';
                        if (el.getAttribute(flagName) === 'true') return;

                        el.setAttribute(flagName, 'true');

                        el.addEventListener('click', (e) => {
                            try {
                                const elId = el.id || el.textContent.trim();

                                // Debounce multiple clicks
                                if (this.debounceMap.has(elId)) {
                                    e.preventDefault();
                                    return;
                                }

                                if (this.guardRoute(el)) {
                                    e.preventDefault();
                                    return;
                                }

                                // Set debounce timeout
                                this.debounceMap.set(elId, true);
                                setTimeout(() => this.debounceMap.delete(elId), 800);

                                console.log('[ProjectListGuard] Allowed:', el.dataset.action || 'action', 'for project:', el.dataset.projectId);
                            } catch (err) {
                                console.error('[ProjectListGuard] Click handler error:', err);
                            }
                        }, { passive: false });
                    } catch (err) {
                        console.error('[ProjectListGuard] Attach handler error:', err);
                    }
                },

                // Initialize all route guard links
                init() {
                    if (this.initialized) return;

                    try {
                        const guardLinks = document.querySelectorAll('[data-route-guard]');
                        guardLinks.forEach(el => this.attachClickHandler(el));

                        this.initialized = true;
                        console.log('[ProjectListGuard] Initialized:', guardLinks.length + ' links');
                    } catch (err) {
                        console.error('[ProjectListGuard] Initialization error:', err);
                    }
                }
            };

            // Initialize on DOM ready
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', () => ProjectListGuardHandler.init());
            } else {
                ProjectListGuardHandler.init();
            }

            // Expose for potential dynamic content
            window.ProjectListGuardHandler = ProjectListGuardHandler;
        })();
    </script>
@endpush
