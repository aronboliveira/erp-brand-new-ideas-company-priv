@php
    try {
$lang = Utility::fetchUserLang();

            function resolveUserRoute($baseName) {
            $kebab = Str::kebab($baseName);
            return Route::has($baseName) ? $baseName : (Route::has($kebab) ? $kebab : null);
        }

            function safeUserRoute($routeName, $params = []) {
            return $routeName ? route($routeName, $params) : '#';
        }
    } catch (\Throwable $e) {
        \Log::error('projects/users — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    }
@endphp

@if(isset($project) && is_object($project) && isset($project->users) && (is_array($project->users) || is_countable($project->users)) && count($project->users) > 0)
    @foreach ($project->users as $user)
        @if(isset($user) && is_object($user))
            @php
                try {
                    $projectId = data_get($project, 'id');
                    $userId = data_get($user, 'id');
                    $destroyBase = defined('VW::PRJ') ? VW::PRJ . '.user.destroy' : 'project.user.destroy';
                    $destroyKebab = Str::kebab($destroyBase);
                    $destroyResolved = Route::has($destroyBase) ? $destroyBase : (Route::has($destroyKebab) ? $destroyKebab : null);
                    $destroyParams = ($projectId && $userId) ? [$projectId, $userId] : ['#'];
                    $destroyUrl = ($destroyResolved && $projectId && $userId) ? route($destroyResolved, $destroyParams) : '#';
                    $deleteFormId = 'project-user-delete-form-' . ($projectId ?? 'x') . '-' . ($userId ?? 'x');
                    $deleteLinkId = 'project-user-delete-link-' . ($projectId ?? 'x') . '-' . ($userId ?? 'x');

                    $deleteGuardMsg = 'Delete project user route is unavailable. Please contact technical support or your domain administrator.';
                    if (method_exists('Utility', 'fetchLinkMessage') && defined('VW::PRJ')) {
                        $fetchedMsg = Utility::fetchLinkMessage($lang, VW::PRJ, 'delete_project_user_unavailable');
                        $deleteGuardMsg = $fetchedMsg ?? $deleteGuardMsg;
                    }
                } catch (\Throwable $e) {
                    \Log::error('projects/users — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                }
@endphp
            <li class="{{ VC::LGI }} px-0">
                <div class="{{ VC::R_ALC }} {{ VC::JCB }}">
                    <div class="col-sm-auto {{ VC::MB3 }} mb-sm-0">
                        <div class="{{ VC::DFL_AIC }}">
                            <div class="{{ VC::AV_CC_SM }} {{ VC::ME3 }}">
                                @php
                                    try {
                                        $userAvatar = data_get($user, 'avatar');
                                        $avatarSrc = !empty($userAvatar) && is_string($userAvatar)
                                            ? asset('/storage/uploads/avatar/' . $userAvatar)
                                            : asset('/storage/uploads/avatar/avatar.png');
                                    } catch (\Throwable $e) {
                                        \Log::error('projects/users — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                    }
@endphp
                                <img src="{{ $avatarSrc }}"
                                     alt="avatar"
                                     class="img-user"
                                     onerror="this.src='{{ asset('/storage/uploads/avatar/avatar.png') }}'">
                            </div>
                            <div>
                                <h5 class="m-0">{{ data_get($user, 'name', __('Unknown User')) }}</h5>
                                <small class="{{ VC::TXT_MT }}">
                                    @if(data_get($user, 'email') && filter_var(data_get($user, 'email'), FILTER_VALIDATE_EMAIL))
                                        {{ data_get($user, 'email') }}
                                    @else
                                        {{ __('No email available') }}
                                    @endif
                                </small>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-auto text-sm-end {{ VC::DFL_AIC }}">
                        @if($projectId && $userId && $destroyUrl !== '#')
                            <div class="{{ VC::ACT_BTN_DNG_2 }}">
                                {!! Form::open(['method' => 'DELETE', 'url' => $destroyUrl, 'id' => $deleteFormId, 'data-url' => $destroyUrl, 'data-guard-msg' => $deleteGuardMsg]) !!}
                                    <a href="#"
                                       data-route-guard
                                       class="{{ VC::BT_SM_CT_PR }}"
                                       data-url="{{ $destroyUrl }}"
                                       data-form-id="{{ $deleteFormId }}"
                                       data-bs-toggle="tooltip"
                                       title="{{ __('Delete') }}"
                                       data-guard-msg="{{ base64_encode($deleteGuardMsg) }}">
                                        <i class="{{ VC::TI_TRS_WT }}"></i>
                                    </a>
                                {!! Form::close() !!}
                            </div>
                        @else
                            <div class="{{ VC::ACT_BTN_DNG_2 }}">
                                <button type="button"
                                        class="{{ VC::BT_SM_CT_PR }}"
                                        disabled
                                        data-bs-toggle="tooltip"
                                        title="{{ __('Action unavailable') }}">
                                    <i class="{{ VC::TI_TRS_WT }}"></i>
                                </button>
                            </div>
                        @endif
                    </div>
                </div>
            </li>
        @endif
    @endforeach
@else
    <li class="{{ VC::LGI }} px-0">
        <div class="{{ VC::TXCT_PY4 }}">
            <i class="{{ VC::TI_USRS }} {{ VC::TXT_MT }} {{ VC::MB3 }}" style="font-size: 3rem;"></i>
            <h5 class="{{ VC::TXT_MT }}">{{ __('No Users Found') }}</h5>
            <p class="{{ VC::TXT_MT }}">{{ __('This project has no assigned users.') }}</p>
        </div>
    </li>
@endif

@push(StacksConstants::ADM_SCR_PG)
    <script>
    if (typeof window.ProjectUsersHandler === 'undefined') {
        window.ProjectUsersHandler = {
            debounceMap: new Map(),

            init() {
                document.querySelectorAll('[data-route-guard]').forEach(el => this.attachClickHandler(el));
            },

            attachClickHandler(el) {
                const elId = el.getAttribute('data-form-id') || el.getAttribute('data-url') || Math.random();
                el.addEventListener('click', (e) => {
                    if (this.debounceMap.has(elId)) { e.preventDefault(); return; }
                    const formId = el.getAttribute('data-form-id');
                    if (formId) {
                        e.preventDefault();
                        const form = document.getElementById(formId);
                        if (!form) return;
                        const url = form.getAttribute('data-url') || '#';
                        const action = form.getAttribute('action') || '#';
                        if (url === '#' && action === '#') {
                            this.showToast(el.getAttribute('data-guard-msg') || form.getAttribute('data-guard-msg') || 'Route unavailable');
                            return;
                        }
                        form.submit();
                        return;
                    }
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
        document.readyState === 'loading' ? document.addEventListener('DOMContentLoaded', () => window.ProjectUsersHandler.init()) : window.ProjectUsersHandler.init();
    }
    </script>
@endpush


    {{--                        <img src="@if($user->avatar) src="{{asset('/storage/uploads/avatar/'.$user->avatar)}}" @else src="{{asset('/storage/uploads/avatar/avatar.png')}}" @endif " alt = "kal" class="img-user">--}}
