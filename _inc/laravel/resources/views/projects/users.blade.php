@php
    use App\Config\Constants\{ViewsConstants as VW, ViewClassNamesConstants as VC, StacksConstants};
    use App\Models\{Utility, Project};
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\Str;
    use Collective\Html\FormFacade as Form;
    $lang = Utility::fetchUserLang();
@endphp

@if(isset($project) && is_object($project) && isset($project->users) && (is_array($project->users) || is_countable($project->users)) && count($project->users) > 0)
    @foreach ($project->users as $user)
        @if(isset($user) && is_object($user))
            @php
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
            @endphp
            <li class="{{ VC::LGI }} px-0">
                <div class="{{ VC::R_ALC }} {{ VC::JCB }}">
                    <div class="col-sm-auto {{ VC::MB3 }} mb-sm-0">
                        <div class="{{ VC::DFL_AIC }}">
                            <div class="{{ VC::AV_CC_SM }} {{ VC::ME3 }}">
                                @php
                                    $userAvatar = data_get($user, 'avatar');
                                    $avatarSrc = !empty($userAvatar) && is_string($userAvatar) 
                                        ? asset('/storage/uploads/avatar/' . $userAvatar)
                                        : asset('/storage/uploads/avatar/avatar.png');
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
                                {!! Form::open(['method' => 'DELETE', 'url' => $destroyUrl, 'id' => $deleteFormId]) !!}
                                    <a href="#"
                                       id="{{ $deleteLinkId }}"
                                       class="{{ VC::BT_SM_CT_PR }}"
                                       data-url="{{ $destroyUrl }}"
                                       data-form-id="{{ $deleteFormId }}"
                                       data-bs-toggle="tooltip"
                                       title="{{ __('Delete') }}"
                                       data-guard-msg="{{ $deleteGuardMsg }}">
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
        <div class="text-center py-4">
            <i class="ti ti-users text-muted mb-3" style="font-size: 3rem;"></i>
            <h5 class="text-muted">{{ __('No Users Found') }}</h5>
            <p class="text-muted">{{ __('This project has no assigned users.') }}</p>
        </div>
    </li>
@endif

@push(StacksConstants::ADM_SCR_PG)
    <script>
        (() => {
            try {
                const nodes = document.querySelectorAll('a[id^="project-user-delete-link-"]');
                if (!nodes || !nodes.length) return;
                for (let i = 0; i < nodes.length; i++) {
                    try {
                        const el = nodes[i];
                        const flag = 'data-delete-listener';
                        if (el.hasAttribute(flag) && el.getAttribute(flag) === 'true') continue;
                        el.setAttribute(flag, 'true');
                        el.addEventListener('click', function (e) {
                            try {
                                const href = el.getAttribute('href') || '#';
                                const url = el.getAttribute('data-url') || href || '#';
                                if (href !== '#' || url !== '#') return;
                                e.preventDefault();
                                const msg = el.getAttribute('data-guard-msg') || 'Delete project user route is unavailable. Please contact technical support or your domain administrator.';
                                const hasBootstrap = document.querySelector('link[href*="bootstrap"]') && window.bootstrap && window.bootstrap.Toast;
                                let container = document.getElementById('toast-container');
                                if (!container) {
                                    container = document.createElement('div');
                                    container.id = 'toast-container';
                                    container.className = 'position-fixed top-0 end-0 p-3';
                                    document.body.appendChild(container);
                                }
                                if (hasBootstrap) {
                                    const toast = document.createElement('div');
                                    toast.className = 'toast';
                                    toast.setAttribute('role', 'alert');
                                    toast.setAttribute('aria-live', 'assertive');
                                    toast.setAttribute('aria-atomic', 'true');
                                    const body = document.createElement('div');
                                    body.className = 'toast-body';
                                    body.textContent = msg;
                                    toast.appendChild(body);
                                    container.appendChild(toast);
                                    const inst = window.bootstrap.Toast.getOrCreateInstance(toast);
                                    toast.addEventListener('hidden.bs.toast', function () { try { toast.remove(); } catch (err) {} });
                                    inst.show();
                                } else {
                                    alert(msg);
                                }
                                el.setAttribute('data-failed-route', 'true');
                            } catch (err) {}
                        }, { passive: false });
                    } catch (innerErr) {}
                }
            } catch (error) {}
        })();
    </script>
@endpush


    
    {{--                        <img src="@if($user->avatar) src="{{asset('/storage/uploads/avatar/'.$user->avatar)}}" @else src="{{asset('/storage/uploads/avatar/avatar.png')}}" @endif " alt = "kal" class="img-user">--}}