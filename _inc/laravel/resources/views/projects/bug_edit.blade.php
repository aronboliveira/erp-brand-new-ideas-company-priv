@php
    try {
$lang = Utility::fetchUserLang();
    } catch (\Throwable $e) {
        \Log::error('projects/bug_edit — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    }
@endphp
@if (isset($bug) && !empty($bug))
    @php
        try {
            $bugUpdateBaseName     = ViewsConstants::PRJ_TSK_BUG.'.update';
            $bugUpdateKebabName    = Str::kebab($bugUpdateBaseName);
            $bugUpdateResolvedName = Route::has($bugUpdateBaseName)
                ? $bugUpdateBaseName
                : (Route::has($bugUpdateKebabName) ? $bugUpdateKebabName : null);
            $projectId             = isset($project_id) && !empty($project_id) ? $project_id : null;
            $bugId                 = isset($bug) && !empty($bug->id) ? $bug->id : null;
            $bugUpdateRouteArray   = ($bugUpdateResolvedName && $projectId && $bugId) ? [$bugUpdateResolvedName, [$projectId, $bugId]] : ['#'];
            $bugUpdateUrl          = ($bugUpdateResolvedName && $projectId && $bugId) ? route($bugUpdateResolvedName, [$projectId, $bugId]) : '#';
            $bugUpdateGuardMsg     = Utility::fetchLinkMessage($lang, ViewsConstants::PRJ_TSK_BUG, 'update_bug_route_unavailable') ?? 'Update bug route is unavailable. Please contact technical support or your domain administrator.';
            $bugUpdateFormId       = 'edit_bug';
        } catch (\Throwable $e) {
            \Log::error('projects/bug_edit — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
        }
@endphp
    {!! Form::model($bug, [
        'route'          => $bugUpdateRouteArray,
        'method'         => 'post',
        'accept-charset' => 'UTF-8',
        'id'             => $bugUpdateFormId,
        'data-url'       => $bugUpdateUrl,
        'data-guard-msg' => $bugUpdateGuardMsg
    ]) !!}
        @csrf
        <div class="modal-body">
            @php
	try {
		($plan = Utility::getChatGPTSettings())
		            @if($plan?->{PlansConstants::COL_GPT} == 1)
		                <div class="{{ VC::TX_END }}">
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
	} catch (\Throwable $e) {
		\Log::error('projects/bug_edit — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
	}
@endphp
                    <a href="{{ $aiGenerateUrl }}"
                    data-size="md"
                    class="{{ VC::BT_PRM }} btn-icon btn-sm"
                    data-ajax-popup-over="true"
                    data-route-guard
                    data-url="{{ $aiGenerateUrl }}"
                    data-guard-msg="{{ base64_encode($aiGenerateGuardMsg) }}"
                    data-bs-placement="top"
                    data-title="{{ $aiGenerateTitle }}">
                        <i class="{{ VC::FAS_RB }}"></i>
                        <span>{{ __('Generate with AI') }}</span>
                    </a>
                </div>
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
            <input type="submit" value="{{ __('Update') }}" class="{{ VC::BT_PM }}">
        </div>
    {!! Form::close() !!}
    <script>
    if (typeof window.BugEditHandler === 'undefined') {
        window.BugEditHandler = {
            debounceMap: new Map(),

            init() {
                document.querySelectorAll('[data-route-guard]').forEach(el => this.attachClickHandler(el));
                const form = document.getElementById('{{ $bugUpdateFormId }}');
                if (form) this.attachFormHandler(form);
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

            attachFormHandler(form) {
                form.addEventListener('submit', (e) => {
                    const url = form.getAttribute('data-url') || '#';
                    const action = form.getAttribute('action') || '#';
                    if (url !== '#' || action !== '#') return;
                    e.preventDefault();
                    this.showToast(form.getAttribute('data-guard-msg') || 'Update bug route is unavailable');
                }, { passive: false });
            },

            showToast(msg) {
                const RG = window.RouteGuard || {};
                (RG.showToast || (m => alert(m)))(msg);
            }
        };
        document.readyState === 'loading' ? document.addEventListener('DOMContentLoaded', () => window.BugEditHandler.init()) : window.BugEditHandler.init();
    }
    </script>
@else
    <div class="{{ VC::ALT_WRN }}">
        {{ __('No bug information available for editing.') }}
    </div>
@endif
