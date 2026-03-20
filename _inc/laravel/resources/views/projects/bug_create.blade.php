@php
    try {
$user = Auth::user();
        $lang = Utility::fetchUserLang(user:$user);

            function resolveBugCreateRoute($baseName) {
            $kebab = Str::kebab($baseName);
            return Route::has($baseName) ? $baseName : (Route::has($kebab) ? $kebab : null);
        }

            function safeBugCreateRoute($routeName, $params = []) {
            return $routeName ? route($routeName, $params) : '#';
        }

        $projectId = isset($project_id) && !empty($project_id) ? $project_id : null;
        $bugStoreRouteName = resolveBugCreateRoute(ViewsConstants::PRJ_TSK_BUG.'.store');
        $bugStoreUrl = $bugStoreRouteName && $projectId ? safeBugCreateRoute($bugStoreRouteName, $projectId) : '#';
        $bugStoreRouteArray = $bugStoreRouteName && $projectId ? [$bugStoreRouteName, $projectId] : ['#'];

        $guardMessages = [
            'bug_store' => Utility::fetchLinkMessage($lang, ViewsConstants::PRJ_TSK_BUG, 'create_bug_route_unavailable') ?? 'Create bug route is unavailable. Please contact technical support or your domain administrator.',
            'ai_generate' => Utility::fetchLinkMessage($lang, ViewsConstants::PRJ_TSK_BUG, 'generate_project_bug_route_unavailable') ?? 'Generate project bug route is unavailable. Please contact technical support or your domain administrator.',
        ];
    } catch (\Throwable $e) {
        \Log::error('projects/bug_create — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    }
@endphp
{!! Form::open([
    'route'          => $bugStoreRouteArray,
    'method'         => 'post',
    'accept-charset' => 'UTF-8',
    'id'             => 'create_bug',
    'data-url'       => $bugStoreUrl,
    'data-guard-msg' => $guardMessages['bug_store']
]) !!}
    @csrf
    <div class="modal-body">
        @if($user && method_exists($user, 'creatorId'))
            @php
                $planUser = User::find($user->creatorId());
                $plan     = Plan::getPlan($planUser?->plan ?? DatabaseConstants::DEFAULT_PLAN);
@endphp
            @if($plan?->{PlansConstants::COL_GPT} == 1)
                <div class="{{ VC::FEND }}">
                    @php
                        $aiGenerateRouteName = resolveBugCreateRoute('generate');
                        $aiGenerateUrl = safeBugCreateRoute($aiGenerateRouteName, ['project bug']);
@endphp
                    <a href="{{ $aiGenerateUrl }}"
                    data-size="md"
                    class="{{ VC::BT_PRM }} btn-icon btn-sm"
                    data-ajax-popup-over="true"
                    data-route-guard
                    data-url="{{ $aiGenerateUrl }}"
                    data-guard-msg="{{ base64_encode($guardMessages['ai_generate']) }}"
                    data-bs-placement="top"
                    data-title="{{ __('Generate content with AI') }}">
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
<script>
if (typeof window.BugCreateHandler === 'undefined') {
    window.BugCreateHandler = {
        debounceMap: new Map(),

        init() {
            document.querySelectorAll('[data-route-guard]').forEach(el => this.attachClickHandler(el));
            document.querySelectorAll('form[data-guard-msg]').forEach(form => this.attachFormHandler(form));
        },

        attachClickHandler(el) {
            const elId = el.getAttribute('data-url') || el.href;
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

        attachFormHandler(form) {
            form.addEventListener('submit', (e) => {
                const url = form.getAttribute('data-url') || '#';
                const action = form.getAttribute('action') || '#';
                if (url !== '#' || action !== '#') return;
                e.preventDefault();
                this.showToast(form.getAttribute('data-guard-msg') || 'Form submission unavailable');
            });
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
            toast.innerHTML = `<div class="toast-body">${msg}<button type="button" class="{{ VC::BT_CL }} {{ VC::MS2 }}" data-bs-dismiss="toast" aria-label="{{ __('Close') }}"></button></div>`;
            container.appendChild(toast);
            window.bootstrap?.Toast?.getOrCreateInstance(toast)?.show() || alert(msg);
            toast.addEventListener('hidden.bs.toast', () => toast.remove());
        }
    };
    document.readyState === 'loading' ? document.addEventListener('DOMContentLoaded', () => window.BugCreateHandler.init()) : window.BugCreateHandler.init();
}
</script>
