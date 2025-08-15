@php
    use App\Config\Constants\{
        ActivitiesConstants,
        PlansConstants,
        ProjectsConstants,
        ViewsConstants,
        DatabaseConstants,
        ViewClassNamesConstants as VC
    };
    use App\Models\{Utility, User, Plan};
    use Collective\Html\FormFacade as Form;
    use Illuminate\Support\Facades\{Auth, Route};
    use Illuminate\Support\Str;
    $user = Auth::user();
    $lang = Utility::fetchUserLang(user:$user);
    $bugStoreBaseName     = ViewsConstants::PRJ_TSK_BUG.'.store';
    $bugStoreKebabName    = Str::kebab($bugStoreBaseName);
    $bugStoreResolvedName = Route::has($bugStoreBaseName)
        ? $bugStoreBaseName
        : (Route::has($bugStoreKebabName) ? $bugStoreKebabName : null);
    $projectId            = isset($project_id) && !empty($project_id) ? $project_id : null;
    $bugStoreRouteArray   = ($bugStoreResolvedName && $projectId) ? [$bugStoreResolvedName, $projectId] : ['#'];
    $bugStoreUrl          = ($bugStoreResolvedName && $projectId) ? route($bugStoreResolvedName, $projectId) : '#';
    $bugStoreGuardMsg     = Utility::fetchLinkMessage($lang, ViewsConstants::PRJ_TSK_BUG, 'create_bug_route_unavailable') ?? 'Create bug route is unavailable. Please contact technical support or your domain administrator.';
    $bugStoreFormId       = 'create_bug';
@endphp
{!! Form::open([
    'route'          => $bugStoreRouteArray,
    'method'         => 'post',
    'accept-charset' => 'UTF-8',
    'id'             => $bugStoreFormId,
    'data-url'       => $bugStoreUrl,
    'data-guard-msg' => $bugStoreGuardMsg
]) !!}
    @csrf
    <div class="modal-body">
        @if($user && method_exists($user, 'creatorId'))
            @php
                $planUser = User::find($user->creatorId());
                $plan     = Plan::getPlan($planUser?->plan ?? DatabaseConstants::DEFAULT_PLAN);
            @endphp
            @if($plan?->{PlansConstants::COL_GPT} == 1)
                <div class="float-end">
                    @php
                        $aiGenerateBaseName        = 'generate';
                        $aiGenerateKebabName       = Str::kebab($aiGenerateBaseName);
                        $aiGenerateResolvedName    = Route::has($aiGenerateBaseName)
                            ? $aiGenerateBaseName
                            : (Route::has($aiGenerateKebabName) ? $aiGenerateKebabName : null);
                        $aiGenerateParam           = ['project bug'];
                        $aiGenerateUrl             = $aiGenerateResolvedName ? route($aiGenerateResolvedName, $aiGenerateParam) : '#';
                        $aiGenerateGuardMsg        = Utility::fetchLinkMessage($lang, ViewsConstants::PRJ_TSK_BUG, 'generate_project_bug_route_unavailable') ?? 'Generate project bug route is unavailable. Please contact technical support or your domain administrator.';
                        $aiGenerateLinkId          = 'ai-generate-project-bug-link';
                        $aiGenerateTitle           = __('Generate content with AI');
                    @endphp
                    <a href="{{ $aiGenerateUrl }}"
                    id="{{ $aiGenerateLinkId }}"
                    data-size="md"
                    class="btn btn-primary btn-icon btn-sm"
                    data-ajax-popup-over="true"
                    data-url="{{ $aiGenerateUrl }}"
                    data-guard-msg="{{ $aiGenerateGuardMsg }}"
                    data-bs-placement="top"
                    data-title="{{ $aiGenerateTitle }}">
                        <i class="{{ VC::FAS_RB }}"></i>
                        <span>{{ __('Generate with AI') }}</span>
                    </a>
                </div>
            @endif
        @endif
        <div class="row">
            <div class="{{ VC::FM_GCB6 }}">
                {{ Form::label(ActivitiesConstants::COL_TT, __('Title'), ['class' => 'form-label']) }}
                {{ Form::text(ActivitiesConstants::COL_TT, null, ['class' => 'form-control', 'required' => 'required']) }}
            </div>

            <div class="{{ VC::FM_GCB6 }}">
                {{ Form::label(ProjectsConstants::COL_PRT, __('Priority'), ['class' => 'form-label']) }}
                {{ Form::select(ProjectsConstants::COL_PRT, !empty($priority) ? $priority : ['null' => __('Could not load priorities')], null, ['class' => "{{ VC::FM_CT_SL }}", 'required' => 'required']) }}
            </div>

            <div class="{{ VC::FM_GCB6 }}">
                {{ Form::label(ProjectsConstants::COL_S_DT, __('Start Date'), ['class' => 'form-label']) }}
                {{ Form::date(ProjectsConstants::COL_S_DT, null, ['class' => 'form-control', 'required' => 'required']) }}
            </div>

            <div class="{{ VC::FM_GCB6 }}">
                {{ Form::label(ProjectsConstants::COL_D_DATE, __('Due Date'), ['class' => 'form-label']) }}
                {{ Form::date(ProjectsConstants::COL_D_DATE, null, ['class' => 'form-control', 'required' => 'required']) }}
            </div>

            <div class="{{ VC::FM_GCB6 }}">
                {{ Form::label(ActivitiesConstants::COL_TSK_STT, __('Bug Status'), ['class' => 'form-label']) }}
                {{ Form::select(ActivitiesConstants::COL_TSK_STT, !empty($status) ? $status : ['null' => __('Could not load statuses')], null, ['class' => "{{ VC::FM_CT_SL }}", 'required' => 'required']) }}
            </div>

            <div class="{{ VC::FM_GCB6 }}">
                {{ Form::label(ProjectsConstants::COL_ASGN, __('Assigned To'), ['class' => 'form-label']) }}
                {{ Form::select(ProjectsConstants::COL_ASGN, !empty($users) ? $users : ['null' => __('Could not load users')], null, ['class' => "{{ VC::FM_CT_SL }}", 'required' => 'required']) }}
            </div>
        </div>

        <div class="row">
            <div class="{{ VC::FM_GCB12 }}">
                {{ Form::label(ActivitiesConstants::COL_DESC, __('Description'), ['class' => 'form-label']) }}
                {{ Form::textarea(ActivitiesConstants::COL_DESC, null, ['class' => 'form-control', 'rows' => 2]) }}
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <input type="button" value="{{ __('Cancel') }}" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">
        <input type="submit" value="{{ __('Create') }}" class="{{ VC::BT_PM }}">
    </div>
{!! Form::close() !!}
<script defer>
    (() => {
        try {
            const f = document.getElementById('{{ $bugStoreFormId }}');
            if (!f || f.getAttribute('data-listener-active') === 'true') return;
            f.setAttribute('data-listener-active', 'true');
            f.addEventListener('submit', e => {
                try {
                    const url = f.getAttribute('data-url') || '#';
                    const action = f.getAttribute('action') || '#';
                    if (url !== '#' || action !== '#') return;
                    e.preventDefault();
                    const msg = f.getAttribute('data-guard-msg') || 'Create bug route is unavailable. Please contact technical support or your domain administrator.';
                    const hasBootstrap = !!(document.querySelector('link[href*="bootstrap"]') && window.bootstrap);
                    let container = document.getElementById('toast-container');
                    if (!container) {
                        container = document.createElement('div');
                        container.id = 'toast-container';
                        document.body.appendChild(container);
                    }
                    if (hasBootstrap) {
                        const toast = document.createElement('div');
                        toast.className = 'toast';
                        toast.setAttribute('role','alert');
                        toast.setAttribute('aria-live','assertive');
                        toast.setAttribute('aria-atomic','true');
                        const body = document.createElement('div');
                        body.className = 'toast-body';
                        body.textContent = msg;
                        toast.appendChild(body);
                        container.appendChild(toast);
                        bootstrap.Toast.getOrCreateInstance(toast).show();
                    } else {
                        alert(msg);
                    }
                    f.setAttribute('data-failed-route', 'true');
                } catch (err) {}
            });
        } catch (error) {}
    })();
</script>
<script defer>
    (() => {
        try {
            const l = document.getElementById('{{ $aiGenerateLinkId }}');
            if (!l || l.getAttribute('data-listener-active') === 'true') return;
            l.setAttribute('data-listener-active', 'true');
            l.addEventListener('click', e => {
                try {
                    const href = l.getAttribute('href') || '#';
                    const url = l.getAttribute('data-url') || href || '#';
                    if (href !== '#' || url !== '#') return;
                    e.preventDefault();
                    const msg = l.getAttribute('data-guard-msg') || 'Generate project bug route is unavailable. Please contact technical support or your domain administrator.';
                    const hasBootstrap = !!(document.querySelector('link[href*="bootstrap"]') && window.bootstrap);
                    let container = document.getElementById('toast-container');
                    if (!container) {
                        container = document.createElement('div');
                        container.id = 'toast-container';
                        document.body.appendChild(container);
                    }
                    if (hasBootstrap) {
                        const toast = document.createElement('div');
                        toast.className = 'toast';
                        toast.setAttribute('role','alert');
                        toast.setAttribute('aria-live','assertive');
                        toast.setAttribute('aria-atomic','true');
                        const body = document.createElement('div');
                        body.className = 'toast-body';
                        body.textContent = msg;
                        toast.appendChild(body);
                        container.appendChild(toast);
                        bootstrap.Toast.getOrCreateInstance(toast).show();
                    } else {
                        alert(msg);
                    }
                    l.setAttribute('data-failed-route', 'true');
                } catch (err) {}
            });
        } catch (error) {}
    })();
</script>