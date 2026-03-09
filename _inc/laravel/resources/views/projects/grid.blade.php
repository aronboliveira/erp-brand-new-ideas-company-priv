@php
$lang = Utility::fetchUserLang();

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
            if (!$resolved || empty($params[0])) return '#';
            try {
                return route($resolved, $params);
            } catch (\Exception $e) {
                return '#';
            }
        }
    }
@endphp

@if(isset($projects) && !empty($projects) && count($projects) > 0)
    <div class="{{ VC::C12 }}">
        <div class="row">
            @foreach ($projects as $key => $project)
                @php
                                        try {
                                            $projectId = data_get($project, 'id');
                                                                $projectName = data_get($project, 'project_name') ?: data_get($project, 'name') ?: __('Unnamed Project');
                                                                $projectSlug = $projectId ? 'prj-' . $projectId : 'prj-x-' . $key;

                                            $showUrl = safeProjectRoute(VW::PRJ . '.show', [$projectId]);
                                                                $copyUrl = safeProjectRoute(VW::PRJ . '.copy', [$projectId]) ?: safeProjectRoute('project.copy', [$projectId]);
                                                                $editUrl = safeProjectRoute(VW::PRJ . '.edit', [$projectId]);
                                                                $destroyUrl = safeProjectRoute(VW::PRJ . '.destroy', [$projectId]);
                                                                $inviteUrl = safeProjectRoute(VW::PRJ . '.invite.member.view', [$projectId]);

                                            $showLinkId = 'project-show-link-' . $projectSlug;
                                                                $copyLinkId = 'project-copy-link-' . $projectSlug;
                                                                $editLinkId = 'project-edit-link-' . $projectSlug;
                                                                $deleteLinkId = 'project-delete-link-' . $projectSlug;
                                                                $deleteFormId = 'project-delete-form-' . $projectSlug;
                                                                $inviteLinkId = 'project-invite-link-' . $projectSlug;

                                            $messages = [
                                                                    'show' => Utility::fetchLinkMessage($lang, VW::PRJ, 'show_project_route_unavailable')
                                                                            ?? __('Show project route is unavailable. Please contact support.'),
                                                                    'copy' => Utility::fetchLinkMessage($lang, VW::PRJ, 'copy_project_unavailable')
                                                                            ?? __('Copy project route is unavailable. Please contact support.'),
                                                                    'edit' => Utility::fetchLinkMessage($lang, VW::PRJ, 'project_edit_route_unavailable')
                                                                            ?? __('Edit project route is unavailable. Please contact support.'),
                                                                    'delete' => Utility::fetchLinkMessage($lang, VW::PRJ, 'delete_project_route_unavailable')
                                                                            ?? __('Delete project route is unavailable. Please contact support.'),
                                                                    'invite' => Utility::fetchLinkMessage($lang, VW::PRJ, 'invite_project_member_unavailable')
                                                                            ?? __('Invite member route is unavailable. Please contact support.'),
                                                                    'confirm' => Utility::fetchLinkMessage($lang, 'generics', 'are_you_sure') ?? __('Are You Sure?'),
                                                                    'irreversible' => Utility::fetchLinkMessage($lang, 'generics', 'irreversible_action')
                                                                            ?? __('This action cannot be undone. Do you want to continue?'),
                                                                ];
                                        } catch (\Throwable $e) {
                                            \Log::error('projects/grid — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                        }
@endphp
                @if(isset($project) && is_object($project))
                    <div class="{{ VC::CM6 }} col-xxl-3" data-project-id="{{ $projectId }}" data-project-card>
                        <div class="card" style="transition: transform 0.2s ease, box-shadow 0.2s ease;" onmouseenter="this.style.transform='translateY(-4px)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.15)'" onmouseleave="this.style.transform=''; this.style.boxShadow=''">
                            <div class="{{ VC::CD_HD }} border-0 pb-0">
                                <div class="{{ VC::DFL_AIC }}">
                                    @if(isset($project->img_image) && !empty($project->img_image))
                                        <img {{ $project->img_image }} class="{{ VC::IMG_FL }} wid-30 me-2" alt="">
                                    @else
                                        <img src="{{ asset('default-project-image.png') }}" class="{{ VC::IMG_FL }} wid-30 me-2" alt="">
                                    @endif
                                    <h5 class="{{ VC::MB0 }}">
                                        <a class="{{ VC::TX_DK }} project-show-link"
                                        id="{{ $showLinkId }}"
                                        href="{{ $showUrl }}"
                                        data-url="{{ $showUrl }}"
                                        data-guard-msg="{{ base64_encode($messages['show']) }}"
                                        data-project-id="{{ $projectId }}"
                                        aria-label="{{ __('View project') }}: {{ $projectName }}"
                                        style="transition: color 0.2s ease;"
                                        onmouseenter="this.style.color='#0d6efd'"
                                        onmouseleave="this.style.color=''">
                                            {{ $projectName }}
                                        </a>
                                    </h5>
                                </div>
                                <div class="card-header-right">
                                    <div class="btn-group card-option">
                                        <button type="button" class="btn dropdown-toggle" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            <i class="{{ defined('VC::TD_DOTV') ? VC::TD_DOTV : 'ti ti-dots-vertical' }}"></i>
                                        </button>
                                        <div class="{{ defined('VC::DRP_MN_EM') ? VC::DRP_MN_EM : 'dropdown-menu dropdown-menu-end' }}">
                                            @can('create project')
                                                @if($copyUrl !== '#')
                                                    <a class="{{ VC::DRP_IT }} project-action-link"
                                                    id="{{ $copyLinkId }}"
                                                    data-ajax-popup="true"
                                                    data-size="md"
                                                    data-title="{{ __('Duplicate Project') }}"
                                                    href="{{ $copyUrl }}"
                                                    data-url="{{ $copyUrl }}"
                                                    data-action="copy"
                                                    data-project-id="{{ $projectId }}"
                                                    data-guard-msg="{{ base64_encode($messages['copy']) }}"
                                                    aria-label="{{ __('Duplicate project') }}: {{ $projectName }}">
                                                        <i class="{{ VC::TI_COPY }}"></i> <span>{{ __('Duplicate') }}</span>
                                                    </a>
                                                @endif
                                            @endcan
                                            @can('edit project')
                                                @if($editUrl !== '#')
                                                    <a href="{{ $editUrl }}"
                                                    id="{{ $editLinkId }}"
                                                    data-size="lg"
                                                    data-url="{{ $editUrl }}"
                                                    data-ajax-popup="true"
                                                    class="{{ VC::DRP_IT }} project-action-link"
                                                    data-action="edit"
                                                    data-project-id="{{ $projectId }}"
                                                    data-bs-original-title="{{ __('Edit Project') }}"
                                                    data-guard-msg="{{ base64_encode($messages['edit']) }}"
                                                    aria-label="{{ __('Edit project') }}: {{ $projectName }}">
                                                        <i class="{{ VC::TI_PC }}"></i>
                                                        <span>{{ __('Edit') }}</span>
                                                    </a>
                                                @endif
                                            @endcan
                                            @can('delete project')
                                                @if($destroyUrl !== '#')
                                                    {!! Form::open(['method' => 'DELETE', 'url' => $destroyUrl, 'id' => $deleteFormId]) !!}
                                                        <a href="#!"
                                                        id="{{ $deleteLinkId }}"
                                                        class="{{ VC::DRP_IT }} bs-pass-para project-action-link"
                                                        data-url="{{ $destroyUrl }}"
                                                        data-action="delete"
                                                        data-project-id="{{ $projectId }}"
                                                        data-form-id="{{ $deleteFormId }}"
                                                        data-guard-msg="{{ base64_encode($messages['delete']) }}"
                                                        data-confirm="{{ $messages['confirm'] }}|{{ $messages['irreversible'] }}"
                                                        data-confirm-yes="document.getElementById('{{ $deleteFormId }}').submit();"
                                                        aria-label="{{ __('Delete project') }}: {{ $projectName }}">
                                                            <i class="{{ VC::TI_ARC }}"></i>
                                                            <span>{{ __('Delete') }}</span>
                                                        </a>
                                                    {!! Form::close() !!}
                                                @endif
                                            @endcan

                                            @can('edit project')
                                                @if($inviteUrl !== '#')
                                                    <a href="{{ $inviteUrl }}"
                                                    id="{{ $inviteLinkId }}"
                                                    data-size="lg"
                                                    data-url="{{ $inviteUrl }}"
                                                    data-ajax-popup="true"
                                                    class="{{ VC::DRP_IT }} project-action-link"
                                                    data-action="invite"
                                                    data-project-id="{{ $projectId }}"
                                                    data-bs-original-title="{{ __('Invite User') }}"
                                                    data-guard-msg="{{ base64_encode($messages['invite']) }}"
                                                    aria-label="{{ __('Invite user to project') }}: {{ $projectName }}">
                                                        <i class="ti ti-send"></i>
                                                        <span>{{ __('Invite User') }}</span>
                                                    </a>
                                                @endif
                                            @endcan
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="{{ VC::CD_BD }}">
                                <div class="row g-2 {{ VC::JCB }}">
                                    <div class="{{ VC::C_AT }}">
                                        @if(isset($project->status) && class_exists('Project') &&
                                            method_exists('Project', '__callStatic') &&
                                            isset(Project::$status_color) &&
                                            isset(Project::$project_status) &&
                                            is_array(Project::$status_color) &&
                                            is_array(Project::$project_status))
                                            @php
                                                $statusColor = data_get(Project::$status_color, $project->status, 'secondary');
                                                $statusText = data_get(Project::$project_status, $project->status, 'Unknown');
@endphp
                                            <span class="badge rounded-pill bg-{{ $statusColor }}">{{ __($statusText) }}</span>
                                        @else
                                            <span class="badge rounded-pill bg-secondary">{{ __('Unknown Status') }}</span>
                                        @endif
                                    </div>
                                </div>
                                <p class="{{ VC::TXT_MT }} {{ VC::TXSM }} {{ VC::MT3 }}">
                                    {{ data_get($project, 'description', __('No description available')) }}
                                </p>
                                <small>{{ __('MEMBERS') }}</small>
                                <div class="user-group">
                                    @if(isset($project->users) &&
                                        (is_array($project->users) || is_object($project->users)) &&
                                        !empty($project->users) &&
                                        count($project->users) > 0)
                                        @foreach($project->users as $ukey => $user)
                                            @if(is_numeric($ukey) && $ukey < 3 && isset($user) && is_object($user))
                                                <a href="#" class="{{ VC::AV_CC_SM }}">
                                                    @if(isset($user->avatar) && !empty($user->avatar) && is_string($user->avatar))
                                                        <img src="{{ asset('/storage/uploads/avatar/'.$user->avatar) }}"
                                                            alt="image"
                                                            data-bs-toggle="tooltip"
                                                            title="{{ data_get($user, 'name', 'Unknown User') }}">
                                                    @else
                                                        <img src="{{ asset('/storage/uploads/avatar/avatar.png') }}"
                                                            alt="image"
                                                            data-bs-toggle="tooltip"
                                                            title="{{ data_get($user, 'name', 'Unknown User') }}">
                                                    @endif
                                                </a>
                                            @elseif($ukey >= 3)
                                                @break
                                            @endif
                                        @endforeach
                                    @else
                                        <span class="{{ VC::TXT_MT }} {{ VC::TXSM }}">{{ __('No members assigned') }}</span>
                                    @endif
                                </div>

                                <div class="card {{ VC::MB0 }} {{ VC::MT3 }}">
                                    <div class="{{ VC::CD_BD }} p-3">
                                        <div class="row">
                                            <div class="{{ VC::C6 }}">
                                                @if(isset($project->start_date) && !empty($project->start_date))
                                                    @php
                                                        $startDate = $project->start_date;
                                                        $isOverdue = false;
                                                        $formattedStartDate = $startDate;
                                                        if (class_exists('Utility') && method_exists('Utility', 'getDateFormated')) {
                                                            $formattedStartDate = Utility::getDateFormated($startDate);
                                                        } elseif (is_string($startDate)) {
                                                            try {
                                                                $formattedStartDate = date('M d, Y', strtotime($startDate));
                                                                $isOverdue = strtotime($startDate) < time();
                                                            } catch (Exception $e) {
                                                                $formattedStartDate = $startDate;
                                                            }
                                                        }
@endphp
                                                    <h6 class="{{ VC::MB0 }} {{ $isOverdue ? 'text-danger' : '' }}">{{ $formattedStartDate }}</h6>
                                                @else
                                                    <h6 class="{{ VC::MB0 }}">{{ __('Not set') }}</h6>
                                                @endif
                                                <p class="{{ VC::TXT_MT_TXSM_MB0 }}">{{ __('Start Date') }}</p>
                                            </div>
                                            <div class="{{ VC::C6 }} {{ VC::TX_END }}">
                                                @if(isset($project->end_date) && !empty($project->end_date))
                                                    @php
                                                        $endDate = $project->end_date;
                                                        $formattedEndDate = $endDate;
                                                        if (class_exists('Utility') && method_exists('Utility', 'getDateFormated')) {
                                                            $formattedEndDate = Utility::getDateFormated($endDate);
                                                        } elseif (is_string($endDate)) {
                                                            try {
                                                                $formattedEndDate = date('M d, Y', strtotime($endDate));
                                                            } catch (Exception $e) {
                                                                $formattedEndDate = $endDate;
                                                            }
                                                        }
@endphp
                                                    <h6 class="{{ VC::MB0 }}">{{ $formattedEndDate }}</h6>
                                                @else
                                                    <h6 class="{{ VC::MB0 }}">{{ __('Not set') }}</h6>
                                                @endif
                                                <p class="{{ VC::TXT_MT_TXSM_MB0 }}">{{ __('Due Date') }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="{{ VC::CM6 }} col-xxl-3">
                        <div class="card">
                            <div class="{{ VC::CD_BD }} {{ VC::TXCT }}">
                                <p class="{{ VC::TXT_MT }}">{{ __('Project data not available') }}</p>
                            </div>
                        </div>
                    </div>
                @endif
            @endforeach
        </div>
    </div>
    @push(StacksConstants::ADM_SCR_PG)
    <script>
        (() => {
            'use strict';

            const ProjectGridHandler = {
                initialized: false,

                // DRY: Centralized toast notification using RouteGuard
                showToast(message, type = 'warning') {
                    (window.RouteGuard?.showToast || (m => alert(m)))(message);
                },

                // Route guard with defensive checks
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
                        console.error('[ProjectGrid] Guard error:', err);
                        return true;
                    }
                },

                // DRY: Attach click handler with debouncing
                attachClickHandler(el, action = 'default') {
                    try {
                        if (!el) return;
                        const flagName = `data-${action}-listener`;
                        if (el.getAttribute(flagName) === 'true') return;

                        el.setAttribute(flagName, 'true');

                        let debounceTimer = null;
                        el.addEventListener('click', (e) => {
                            try {
                                if (el.dataset.processing === 'true') {
                                    e.preventDefault();
                                    return;
                                }

                                if (this.guardRoute(el)) {
                                    e.preventDefault();
                                    return;
                                }

                                // Debounce to prevent double-clicks
                                if (debounceTimer) {
                                    e.preventDefault();
                                    return;
                                }

                                el.dataset.processing = 'true';
                                debounceTimer = setTimeout(() => {
                                    el.dataset.processing = 'false';
                                    debounceTimer = null;
                                }, 1000);

                                console.log(`[ProjectGrid] ${action} action triggered for project:`, el.dataset.projectId);
                            } catch (err) {
                                console.error(`[ProjectGrid] ${action} handler error:`, err);
                            }
                        }, { passive: false });
                    } catch (err) {
                        console.error('[ProjectGrid] Attach handler error:', err);
                    }
                },

                // Initialize all handlers
                init() {
                    if (this.initialized) return;

                    try {
                        // Use class-based selectors for better performance
                        const showLinks = document.querySelectorAll('.project-show-link');
                        const actionLinks = document.querySelectorAll('.project-action-link');

                        showLinks.forEach(el => this.attachClickHandler(el, 'show'));

                        actionLinks.forEach(el => {
                            const action = el.dataset.action || 'action';
                            this.attachClickHandler(el, action);
                        });

                        this.initialized = true;
                        console.log('[ProjectGrid] Initialized successfully:', {
                            showLinks: showLinks.length,
                            actionLinks: actionLinks.length
                        });
                    } catch (err) {
                        console.error('[ProjectGrid] Initialization error:', err);
                    }
                }
            };

            // Initialize on DOM ready
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', () => ProjectGridHandler.init());
            } else {
                ProjectGridHandler.init();
            }

            // Expose for potential dynamic content loading
            window.ProjectGridHandler = ProjectGridHandler;
        })();
    </script>
    @endpush
@else
    <div class="{{ VC::CXL12 }} {{ VC::CL12 }} {{ VC::CS12 }}">
        <div class="card">
            <div class="{{ VC::CD_BD }}">
                <h6 class="{{ VC::TXCT }} {{ VC::MB0 }}">{{ __('No Projects Found.') }}</h6>
            </div>
        </div>
    </div>
@endif
