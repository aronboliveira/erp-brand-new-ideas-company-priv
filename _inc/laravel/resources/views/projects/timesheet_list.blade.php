@php
    use App\Config\Constants\{ExtendingLayoutsConstants, StacksConstants, YieldingConstants, ViewsConstants as VW, ViewClassNamesConstants as VC, PermissionsConstants};
    use App\Models\{Project,Utility};
    use Illuminate\Support\Facades\{Auth,Route};
    use Illuminate\Support\Str;
    $user = Auth::user();
    $lang = Utility::fetchUserLang(user:$user);
@endphp
<div class="col-xl-12">
    <div class="card">
        <div class="card-body table-border-style">
            <div class="table-responsive">
                <table class="{{ VC::TB }} datatable">
                    <thead>
                        <tr>
                            <th>{{ __('Project') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th>{{ __('Users') }}</th>
                            <th>{{ __('Completion') }}</th>
                            <th class="text-end">{{ __('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if(isset($projects) && !empty($projects) && count($projects) > 0)
                            @foreach ($projects as $key => $project)
                                @if(isset($project) && is_object($project))
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
                                        $copyCandidates = [ViewsConstans::PRJ.'.copy', Str::kebab(ViewsConstans::PRJ.'.copy'), VW::PRJ . '.copy', Str::kebab(VW::PRJ . '.copy')];
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
                                        $authUserId = $user?->id;
                                        $destroyBase = ViewsConstans::PRJ.'.user.destroy';
                                        $destroyKebab = Str::kebab($destroyBase);
                                        $destroyResolved = Route::has($destroyBase) ? $destroyBase : (Route::has($destroyKebab) ? $destroyKebab : null);
                                        $destroyParams = ($projectId && $authUserId) ? [$projectId, $authUserId] : ['#'];
                                        $destroyUrl = ($destroyResolved && $projectId && $authUserId) ? route($destroyResolved, $destroyParams) : '#';
                                        $deleteFormId = 'project-delete-form-' . ($projectId ?? 'x');
                                        $deleteLinkId = 'project-delete-link-' . ($projectId ?? 'x');
                                        $deleteGuardMsg = Utility::fetchLinkMessage($lang, VW::PRJ, 'delete_user_project_unavailable') ?? 'Delete user project route is unavailable. Please contact technical support or your domain administrator.';
                                        $areYouSureMsg = Utility::fetchLinkMessage($lang, 'generics', 'are_you_sure') ?? 'Are You Sure?';
                                        $irreversibleMsg = Utility::fetchLinkMessage($lang, 'generics', 'irreversible_action') ?? 'This action can not be undone. Do you want to continue?';
                                        $inviteBase = VW::PRJ . '.invite.member.view';
                                        $inviteKebab = Str::kebab($inviteBase);
                                        $inviteResolved = Route::has($inviteBase) ? $inviteBase : (Route::has($inviteKebab) ? $inviteKebab : null);
                                        $inviteParams = $projectId ? [$projectId] : ['#'];
                                        $inviteUrl = ($inviteResolved && $projectId) ? route($inviteResolved, $inviteParams) : '#';
                                        $inviteLinkId = 'project-invite-link-' . ($projectId ?? 'x');
                                        $inviteGuardMsg = Utility::fetchLinkMessage($lang, VW::PRJ, 'invite_project_member_unavailable') ?? 'Invite project member route is unavailable. Please contact technical support or your domain administrator.';
                                        $lastTaskId = isset($last_task) && !empty(data_get($last_task, 'id')) ? data_get($last_task, 'id') : null;
                                        $progress = method_exists($project, 'projectProgress') ? $project->projectProgress($project, $lastTaskId) : [];
                                        $progressPct = data_get($progress, 'percentage', '0%');
                                        $progressColor = data_get($progress, 'color', 'primary');
                                    @endphp
                                    <tr>
                                        <td>
                                            <div class="{{ VC::DFL_AIC }}">
                                                @if(isset($project->img_image) && !empty($project->img_image))
                                                    <img {{ $project->img_image }} class="wid-40 rounded me-3">
                                                @else
                                                    <img src="{{ asset('default-project-image.png') }}" class="wid-40 rounded me-3" alt="">
                                                @endif
                                                <p class="mb-0">
                                                    <a href="{{ !empty($showUrl) ? $showUrl : '#' }}" 
                                                    id="{{ !empty($showLinkId) ? $showLinkId : 'show-link-default' }}" 
                                                    data-url="{{ !empty($showUrl) ? $showUrl : '#' }}" 
                                                    data-guard-msg="{{ !empty($showGuardMsg) ? $showGuardMsg : '' }}" 
                                                    class="name mb-0 h6 text-sm">
                                                        {{ !empty($projectName) ? $projectName : (data_get($project, 'name') ?: __('Unnamed Project')) }}
                                                    </a>
                                                </p>
                                            </div>
                                        </td>
                                        <td>
                                            @if(isset($project->status) && 
                                                class_exists('\App\Models\Project') && 
                                                isset(Project::$status_color) && 
                                                isset(Project::$project_status) &&
                                                is_array(Project::$status_color) && 
                                                is_array(Project::$project_status))
                                                @php
                                                    $statusColor = data_get(Project::$status_color, $project->status, 'secondary');
                                                    $statusText = data_get(Project::$project_status, $project->status, 'Unknown');
                                                @endphp
                                                <span class="badge bg-{{ $statusColor }} p-2 px-3 rounded">{{ __($statusText) }}</span>
                                            @else
                                                <span class="badge bg-secondary p-2 px-3 rounded">{{ __('Unknown Status') }}</span>
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
                                                            <a href="#" class="avatar rounded-circle">
                                                                <img src="{{ isset($u->avatar) && !empty($u->avatar) && is_string($u->avatar) 
                                                                    ? asset('/storage/uploads/avatar/' . $u->avatar) 
                                                                    : asset('/storage/uploads/avatar/avatar.png') }}" 
                                                                    title="{{ data_get($u, 'name', 'Unknown User') }}" 
                                                                    style="height:36px;width:36px;"
                                                                    alt="user avatar"
                                                                    onerror="this.src='{{ asset('/storage/uploads/avatar/avatar.png') }}'">
                                                            </a>
                                                        @elseif($uKey >= 3)
                                                            @break
                                                        @endif
                                                    @endforeach
                                                    @if(count($project->users) > 3)
                                                        <a href="#" class="avatar rounded-circle avatar-sm">
                                                            <div class="avatar-placeholder d-flex align-items-center justify-content-center bg-primary text-white rounded-circle" 
                                                                style="height:36px;width:36px;font-size:12px;">
                                                                + {{ count($project->users) - 3 }}
                                                            </div>
                                                        </a>
                                                    @endif
                                                @else
                                                    <span class="text-muted">{{ __('-') }}</span>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="text-end">
                                            @if(!empty($progressPct) && is_string($progressPct))
                                                <h5 class="mb-0 text-success">{{ $progressPct }}</h5>
                                                <div class="progress mb-0">
                                                    <div class="progress-bar bg-{{ !empty($progressColor) ? $progressColor : 'primary' }}" 
                                                        style="width: {{ $progressPct }};"></div>
                                                </div>
                                            @else
                                                <h5 class="mb-0 text-muted">{{ __('N/A') }}</h5>
                                                <div class="progress mb-0">
                                                    <div class="progress-bar bg-secondary" style="width: 0%;"></div>
                                                </div>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            <span>
                                                @can('edit project')
                                                    @if(!empty($inviteUrl) && !empty($inviteLinkId))
                                                        <div class="{{ VC::ACT_BTN_WRN }} ms-2">
                                                            <a href="{{ $inviteUrl }}" 
                                                            id="{{ $inviteLinkId }}" 
                                                            class="mx-3 btn btn-sm d-inline-flex align-items-center" 
                                                            data-url="{{ $inviteUrl }}" 
                                                            data-ajax-popup="true" 
                                                            data-size="lg" 
                                                            data-bs-toggle="tooltip" 
                                                            title="{{ __('Invite User') }}" 
                                                            data-title="{{ __('Invite User') }}" 
                                                            data-guard-msg="{{ !empty($inviteGuardMsg) ? $inviteGuardMsg : '' }}">
                                                                <i class="ti ti-send {{ VC::TXT_WT }}"></i>
                                                            </a>
                                                        </div>
                                                    @endif
                                                @endcan
                                                @can('edit project')
                                                    @if(!empty($editUrl) && !empty($editLinkId))
                                                        <div class="{{ VC::ACT_BTN_INF }} ms-2">
                                                            <a href="{{ $editUrl }}" 
                                                            id="{{ $editLinkId }}" 
                                                            class="mx-3 btn btn-sm d-inline-flex align-items-center" 
                                                            data-url="{{ $editUrl }}" 
                                                            data-ajax-popup="true" 
                                                            data-size="lg" 
                                                            data-bs-toggle="tooltip" 
                                                            title="{{ __('Edit') }}" 
                                                            data-title="{{ __('Edit Project') }}" 
                                                            data-guard-msg="{{ !empty($editGuardMsg) ? $editGuardMsg : '' }}">
                                                                <i class="{{ VC::TI_PC_WT }}"></i>
                                                            </a>
                                                        </div>
                                                    @endif
                                                @endcan
                                                @can('delete project')
                                                    @if(!empty($destroyUrl) && !empty($deleteFormId) && !empty($deleteLinkId))
                                                        <div class="{{ VC::ACT_BTN_DNG_2 }}">
                                                            {!! Collective\Html\FormFacade::open(['method' => 'DELETE', 'url' => $destroyUrl, 'id' => $deleteFormId]) !!}
                                                                <a href="#!" 
                                                                id="{{ $deleteLinkId }}" 
                                                                class="{{ VC::BT_SM_CT_PR }}" 
                                                                data-url="{{ $destroyUrl }}" 
                                                                data-guard-msg="{{ !empty($deleteGuardMsg) ? $deleteGuardMsg : '' }}" 
                                                                data-bs-toggle="tooltip" 
                                                                title="{{ __('Delete') }}" 
                                                                data-confirm="{{ !empty($areYouSureMsg) ? __($areYouSureMsg) : __('Are you sure?') }}|{{ !empty($irreversibleMsg) ? __($irreversibleMsg) : __('This action is irreversible.') }}" 
                                                                data-confirm-yes="document.getElementById('{{ $deleteFormId }}').submit();">
                                                                    <i class="ti ti-trash {{ VC::TXT_WT }}"></i>
                                                                </a>
                                                            {!! Collective\Html\FormFacade::close() !!}
                                                        </div>
                                                    @endif
                                                @endcan
                                                @can('create project')
                                                    @if(!empty($copyUrl) && !empty($copyLinkId))
                                                        <div class="{{ VC::ACT_BTN_PRIM }}">
                                                            <a href="{{ $copyUrl }}" 
                                                            id="{{ $copyLinkId }}" 
                                                            class="{{ VC::BT_SM_CT }}" 
                                                            data-url="{{ $copyUrl }}" 
                                                            data-ajax-popup="true" 
                                                            data-size="md" 
                                                            data-title="{{ __('Duplicate Project') }}" 
                                                            data-bs-toggle="tooltip" 
                                                            title="{{ __('Duplicate') }}" 
                                                            data-guard-msg="{{ !empty($copyGuardMsg) ? $copyGuardMsg : '' }}">
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
                                <th scope="col" colspan="7"><h6 class="text-center">{{ __('No Projects Found.') }}</h6></th>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
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
                    return true;
                } catch (err) { return true; }
            };
            const wire = (selector, flag) => {
                try {
                    const nodes = document.querySelectorAll(selector);
                    if (!nodes || !nodes.length) return;
                    for (let i = 0; i < nodes.length; i++) {
                        try {
                            const el = nodes[i];
                            if (el.hasAttribute(flag) && el.getAttribute(flag) === 'true') continue;
                            el.setAttribute(flag, 'true');
                            el.addEventListener('click', function (e) {
                                try {
                                    if (guard(el)) { e.preventDefault(); }
                                } catch (err) {}
                            }, { passive: false });
                        } catch (inner) {}
                    }
                } catch (err) {}
            };
            wire('a[id^="project-show-link-"]', 'data-show-listener');
            wire('a[id^="project-copy-link-"]', 'data-copy-listener');
            wire('a[id^="project-edit-link-"]', 'data-edit-listener');
            wire('a[id^="project-delete-link-"]', 'data-delete-listener');
            wire('a[id^="project-invite-link-"]', 'data-invite-listener');
        } catch (error) {}
    })();
</script>
