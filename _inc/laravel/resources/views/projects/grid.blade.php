@php
    use App\Config\Constants\ViewClassNamesConstants as VC;
    use App\Config\Constants\ViewsConstants as VW;
    use App\Config\Constants\StacksConstants;
    use App\Models\{Project, Utility};
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\Facades\Auth;
    use Illuminate\Support\Str;
    use Collective\Html\FormFacade as Form;
    $lang = Utility::fetchUserLang();
@endphp

@if(isset($projects) && !empty($projects) && count($projects) > 0)
    <div class="col-12">
        <div class="row">
            @foreach ($projects as $key => $project)
                @php
                    $projectId = isset($project) && !empty(data_get($project, 'id')) ? data_get($project, 'id') : null;
                    $projectName = isset($project) && !empty(data_get($project, 'project_name')) ? data_get($project, 'project_name') : '';
                    $showBase = VW::PRJ . '.show';
                    $showKebab = Str::kebab($showBase);
                    $showResolved = Route::has($showBase) ? $showBase : (Route::has($showKebab) ? $showKebab : null);
                    $showParams = $projectId ? [$projectId] : ['#'];
                    $showUrl = ($showResolved && $projectId) ? route($showResolved, $showParams) : '#';
                    $showLinkId = 'project-show-link-' . ($projectId ?? 'x');
                    $showGuardMsg = Utility::fetchLinkMessage($lang, VW::PRJ, 'show_project_route_unavailable') ?? 'Show project route is unavailable. Please contact technical support or your domain administrator.';
                    $copyCandidates = [
                        'project.copy',
                        Str::kebab('project.copy'),
                        VW::PRJ . '.copy',
                        Str::kebab(VW::PRJ . '.copy'),
                    ];
                    $copyResolved = null;
                    foreach ($copyCandidates as $c) { if (Route::has($c)) { $copyResolved = $c; break; } }
                    $copyParams = $projectId ? [$projectId] : ['#'];
                    $copyUrl = ($copyResolved && $projectId) ? route($copyResolved, $copyParams) : '#';
                    $copyLinkId = 'project-copy-link-' . ($projectId ?? 'x');
                    $copyGuardMsg = Utility::fetchLinkMessage($lang, VW::PRJ, 'copy_project_unavailable') ?? 'Copy project route is unavailable. Please contact technical support or your domain administrator.';
                    $editBase = VW::PRJ . '.edit';
                    $editKebab = Str::kebab($editBase);
                    $editResolved = Route::has($editBase) ? $editBase : (Route::has($editKebab) ? $editKebab : null);
                    $editParams = $projectId ? [$projectId] : ['#'];
                    $editUrl = ($editResolved && $projectId) ? route($editResolved, $editParams) : '#';
                    $editLinkId = 'project-edit-link-' . ($projectId ?? 'x');
                    $editGuardMsg = Utility::fetchLinkMessage($lang, VW::PRJ, 'project_edit_route_unavailable') ?? 'Edit project route is unavailable. Please contact technical support or your domain administrator.';
                    $destroyBase = VW::PRJ . '.destroy';
                    $destroyKebab = Str::kebab($destroyBase);
                    $destroyResolved = Route::has($destroyBase) ? $destroyBase : (Route::has($destroyKebab) ? $destroyKebab : null);
                    $destroyParams = $projectId ? [$projectId] : ['#'];
                    $destroyUrl = ($destroyResolved && $projectId) ? route($destroyResolved, $destroyParams) : '#';
                    $deleteFormId = 'project-delete-form-' . ($projectId ?? 'x');
                    $deleteLinkId = 'project-delete-link-' . ($projectId ?? 'x');
                    $deleteGuardMsg = Utility::fetchLinkMessage($lang, VW::PRJ, 'delete_project_route_unavailable') ?? 'Delete project route is unavailable. Please contact technical support or your domain administrator.';
                    $areYouSureMsg = Utility::fetchLinkMessage($lang, 'generics', 'are_you_sure') ?? 'Are You Sure?';
                    $irreversibleMsg = Utility::fetchLinkMessage($lang, 'generics', 'irreversible_action') ?? 'This action can not be undone. Do you want to continue?';
                    $inviteCandidates = [
                        VW::PRJ . '.invite.member.view',
                        Str::kebab(VW::PRJ . '.invite.member.view'),
                    ];
                    $inviteResolved = null;
                    foreach ($inviteCandidates as $c) { if (Route::has($c)) { $inviteResolved = $c; break; } }
                    $inviteParams = $projectId ? [$projectId] : ['#'];
                    $inviteUrl = ($inviteResolved && $projectId) ? route($inviteResolved, $inviteParams) : '#';
                    $inviteLinkId = 'project-invite-link-' . ($projectId ?? 'x');
                    $inviteGuardMsg = Utility::fetchLinkMessage($lang, VW::PRJ, 'invite_project_member_unavailable') ?? 'Invite project member route is unavailable. Please contact technical support or your domain administrator.';
                @endphp
                @if(isset($project) && is_object($project))
                    <div class="col-md-6 col-xxl-3">
                        <div class="card">
                            <div class="card-header border-0 pb-0">
                                <div class="d-flex align-items-center">
                                    @if(isset($project->img_image) && !empty($project->img_image))
                                        <img {{ $project->img_image }} class="img-fluid wid-30 me-2" alt="">
                                    @else
                                        <img src="{{ asset('default-project-image.png') }}" class="img-fluid wid-30 me-2" alt="">
                                    @endif
                                    <h5 class="mb-0">
                                        <a class="text-dark" 
                                        id="{{ !empty($showLinkId) ? $showLinkId : 'show-link-default' }}" 
                                        href="{{ !empty($showUrl) ? $showUrl : '#' }}" 
                                        data-url="{{ !empty($showUrl) ? $showUrl : '#' }}" 
                                        data-guard-msg="{{ !empty($showGuardMsg) ? $showGuardMsg : '' }}">
                                            {{ !empty($projectName) ? $projectName : (data_get($project, 'name') ?: __('Unnamed Project')) }}
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
                                                @if(!empty($copyUrl) && !empty($copyLinkId))
                                                    <a class="dropdown-item"
                                                    id="{{ $copyLinkId }}"
                                                    data-ajax-popup="true"
                                                    data-size="md"
                                                    data-title="{{ __('Duplicate Project') }}"
                                                    href="{{ $copyUrl }}"
                                                    data-url="{{ $copyUrl }}"
                                                    data-guard-msg="{{ !empty($copyGuardMsg) ? $copyGuardMsg : '' }}">
                                                        <i class="ti ti-copy"></i> <span>{{ __('Duplicate') }}</span>
                                                    </a>
                                                @endif
                                            @endcan
                                            @can('edit project')
                                                @if(!empty($editUrl) && !empty($editLinkId))
                                                    <a href="{{ $editUrl }}"
                                                    id="{{ $editLinkId }}"
                                                    data-size="lg"
                                                    data-url="{{ $editUrl }}"
                                                    data-ajax-popup="true"
                                                    class="dropdown-item"
                                                    data-bs-original-title="{{ __('Edit Project') }}"
                                                    data-guard-msg="{{ !empty($editGuardMsg) ? $editGuardMsg : '' }}">
                                                        <i class="{{ defined('VC::TI_PC') ? VC::TI_PC : 'ti ti-pencil' }}"></i>
                                                        <span>{{ __('Edit') }}</span>
                                                    </a>
                                                @endif
                                            @endcan
                                            @can('delete project')
                                                @if(!empty($destroyUrl) && !empty($deleteFormId) && !empty($deleteLinkId))
                                                    {!! Form::open(['method' => 'DELETE', 'url' => $destroyUrl, 'id' => $deleteFormId]) !!}
                                                        <a href="#!"
                                                        id="{{ $deleteLinkId }}"
                                                        class="dropdown-item bs-pass-para"
                                                        data-url="{{ $destroyUrl }}"
                                                        data-guard-msg="{{ !empty($deleteGuardMsg) ? $deleteGuardMsg : '' }}"
                                                        data-confirm="{{ !empty($areYouSureMsg) ? __($areYouSureMsg) : __('Are you sure?') }}|{{ !empty($irreversibleMsg) ? __($irreversibleMsg) : __('This action is irreversible.') }}"
                                                        data-confirm-yes="document.getElementById('{{ $deleteFormId }}').submit();">
                                                            <i class="{{ defined('VC::TI_ARC') ? VC::TI_ARC : 'ti ti-archive' }}"></i>
                                                            <span>{{ __('Delete') }}</span>
                                                        </a>
                                                    {!! Form::close() !!}
                                                @endif
                                            @endcan
                                            
                                            @can('edit project')
                                                @if(!empty($inviteUrl) && !empty($inviteLinkId))
                                                    <a href="{{ $inviteUrl }}"
                                                    id="{{ $inviteLinkId }}"
                                                    data-size="lg"
                                                    data-url="{{ $inviteUrl }}"
                                                    data-ajax-popup="true"
                                                    class="dropdown-item"
                                                    data-bs-original-title="{{ __('Invite User') }}"
                                                    data-guard-msg="{{ !empty($inviteGuardMsg) ? $inviteGuardMsg : '' }}">
                                                        <i class="ti ti-send"></i>
                                                        <span>{{ __('Invite User') }}</span>
                                                    </a>
                                                @endif
                                            @endcan
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row g-2 justify-content-between">
                                    <div class="col-auto">
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
                                <p class="text-muted text-sm mt-3">
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
                                                <a href="#" class="avatar rounded-circle avatar-sm">
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
                                        <span class="text-muted text-sm">{{ __('No members assigned') }}</span>
                                    @endif
                                </div>
                                
                                <div class="card mb-0 mt-3">
                                    <div class="card-body p-3">
                                        <div class="row">
                                            <div class="col-6">
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
                                                    <h6 class="mb-0 {{ $isOverdue ? 'text-danger' : '' }}">{{ $formattedStartDate }}</h6>
                                                @else
                                                    <h6 class="mb-0">{{ __('Not set') }}</h6>
                                                @endif
                                                <p class="text-muted text-sm mb-0">{{ __('Start Date') }}</p>
                                            </div>
                                            <div class="col-6 text-end">
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
                                                    <h6 class="mb-0">{{ $formattedEndDate }}</h6>
                                                @else
                                                    <h6 class="mb-0">{{ __('Not set') }}</h6>
                                                @endif
                                                <p class="text-muted text-sm mb-0">{{ __('Due Date') }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="col-md-6 col-xxl-3">
                        <div class="card">
                            <div class="card-body text-center">
                                <p class="text-muted">{{ __('Project data not available') }}</p>
                            </div>
                        </div>
                    </div>
                @endif
            @endforeach
        </div>
    </div>
    <script>
        (() => {
            try {
                const guard = (el) => {
                    try {
                        const href = el.getAttribute('href') || '#';
                        const url = el.getAttribute('data-url') || href || '#';
                        if (href !== '#' || url !== '#') return false;
                        const msg = el.getAttribute('data-guard-msg') || 'Requested route is unavailable. Please contact technical support or your domain administrator.';
                        const hasBootstrap = document.querySelector('link[href*="bootstrap"]') && window.bootstrap && window.bootstrap.Toast);
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
                        return true;
                    } catch (err) { return true; }
                };
                const addClick = (el, flagName) => {
                    try {
                        if (!el) return;
                        if (el.hasAttribute(flagName) && el.getAttribute(flagName) === 'true') return;
                        el.setAttribute(flagName, 'true');
                        el.addEventListener('click', function (e) {
                            try {
                                if (guard(el)) { e.preventDefault(); }
                            } catch (err) {}
                        }, { passive: false });
                    } catch (err) {}
                };
                const showLinks = document.querySelectorAll('a[id^="project-show-link-"]');
                for (let i = 0; i < showLinks.length; i++) { addClick(showLinks[i], 'data-show-listener'); }
                const copyLinks = document.querySelectorAll('a[id^="project-copy-link-"]');
                for (let i = 0; i < copyLinks.length; i++) { addClick(copyLinks[i], 'data-copy-listener'); }
                const editLinks = document.querySelectorAll('a[id^="project-edit-link-"]');
                for (let i = 0; i < editLinks.length; i++) { addClick(editLinks[i], 'data-edit-listener'); }
                const deleteLinks = document.querySelectorAll('a[id^="project-delete-link-"]');
                for (let i = 0; i < deleteLinks.length; i++) {
                    try {
                        const el = deleteLinks[i];
                        const flag = 'data-delete-listener';
                        if (el.hasAttribute(flag) && el.getAttribute(flag) === 'true') continue;
                        el.setAttribute(flag, 'true');
                        el.addEventListener('click', function (e) {
                            try {
                                const prevented = guard(el);
                                if (prevented) { e.preventDefault(); return; }
                            } catch (err) {}
                        }, { passive: false });
                    } catch (err) {}
                }
                const inviteLinks = document.querySelectorAll('a[id^="project-invite-link-"]');
                for (let i = 0; i < inviteLinks.length; i++) { addClick(inviteLinks[i], 'data-invite-listener'); }
            } catch (error) {}
        })();
    </script>
@else
    <div class="col-xl-12 col-lg-12 col-sm-12">
        <div class="card">
            <div class="card-body">
                <h6 class="text-center mb-0">{{ __('No Projects Found.') }}</h6>
            </div>
        </div>
    </div>
@endif