@php
    use App\Config\Constants\{ActivitiesConstants, PlansConstants, ProjectsConstants, ViewsConstants as VW, ViewClassNamesConstants as VC, StacksConstants};
    use App\Models\{Utility, ProjectTask};
    use Collective\Html\FormFacade as Form;
    use Illuminate\Support\{Facades\Route, Str};
    $lang = Utility::fetchUserLang();
    $projectIdVal = isset($project_id) && !empty($project_id) ? $project_id : (isset($project) && !empty(data_get($project, 'id')) ? data_get($project, 'id') : null);
    $stageIdVal   = isset($stage_id) && !empty($stage_id) ? $stage_id : null;
    $storeBase    = VW::PRJ_TSK_C . '.store';
    $storeKebab   = Str::kebab($storeBase);
    $storeResolved= Route::has($storeBase) ? $storeBase : (Route::has($storeKebab) ? $storeKebab : null);
    $storeParams  = ($projectIdVal !== null && $stageIdVal !== null) ? [$projectIdVal, $stageIdVal] : ['#'];
    $storeUrl     = ($storeResolved && $projectIdVal !== null && $stageIdVal !== null) ? route($storeResolved, $storeParams) : '#';
    $formId       = 'store_task';
    $formGuardMsg = Utility::fetchLinkMessage($lang, VW::PRJ_TSK_C, 'store_project_task_unavailable') ?? 'Store project task route is unavailable. Please contact technical support or your domain administrator.';
    $plan         = Utility::getChatGPTSettings();
@endphp
{!! Form::open(['url' => $storeUrl, 'id' => $formId, 'data-guard-msg' => $formGuardMsg]) !!}
    <div class="modal-body">
        @php
            $planHasGpt = (int)(data_get($plan ?? null, PlansConstants::COL_GPT) ?? 0) === 1;
        @endphp
        @if($planHasGpt)
            @php
                $aiRouteName = VW::PRJ_TSK_C . '.generate';
                $aiParams = ['project task'];
                $aiUrl = $planHasGpt ? route($aiRouteName, $aiParams) : '#';
                $aiLinkId = 'project-task-ai-generate-link';
                $aiGuardMsg = __(Utility::fetchLinkMessage($lang ?? app()->getLocale(), VW::PRJ_TSK_C, 'generate_project_task_unavailable') ?? 'Generate project task content route is unavailable. Please contact technical support or your domain administrator.');
                $milestones = (is_object($project ?? null) && is_iterable(data_get($project, 'milestones'))) ? data_get($project, 'milestones') : [];
                $priorityOptions = (isset(ProjectTask::$priority) && is_array(ProjectTask::$priority)) ? ProjectTask::$priority : [];
                $allocatedHrs = (is_array($hrs ?? null) || $hrs instanceof \ArrayAccess) ? (data_get($hrs, 'allocated') ?? 0) : 0;
                $projUsers = (is_object($project ?? null) && is_iterable(data_get($project, 'users'))) ? data_get($project, 'users') : [];
            @endphp
            <div class="text-end">
                <a href="{{ $aiUrl }}" id="{{ $aiLinkId }}" class="{{ VC::BT_SM_PM }} btn-icon" data-ajax-popup-over="true" data-size="md" data-url="{{ $aiUrl }}" data-bs-placement="top" data-title="{{ __('Generate content with AI') }}" data-guard-msg="{{ $aiGuardMsg }}">
                    <i class="{{ VC::FAS_RB }}"></i> <span>{{ __('Generate with AI') }}</span>
                </a>
            </div>
        @endif
        <div class="{{ VC::RW }}">
            <div class="{{ VC::CLMS6 }}">
                <div class="{{ VC::FM_G }}">
                    {{ Form::label(ProjectsConstants::COL_NM, __('Task name'), ['class' => VC::FM_LB]) }}
                    <span class="text-danger">*</span>
                    {{ Form::text(ProjectsConstants::COL_NM, null, ['class' => VC::FM_CT, 'required' => 'required']) }}
                </div>
            </div>
            <div class="{{ VC::CLMS6 }}">
                <div class="{{ VC::FM_G }}">
                    {{ Form::label(ProjectsConstants::COL_ML_ID, __('Milestone'), ['class' => VC::FM_LB]) }}
                    <select class="{{ VC::FM_CT_SL }}" name="milestone_id" id="milestone_id">
                        <option value="0" class="text-muted">{{ __('Select Milestone') }}</option>
                        @foreach($milestones as $m_val)
                            <option value="{{ data_get($m_val,'id') }}">{{ data_get($m_val,'title') ?? __('No milestone title available') }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="{{ VC::C12 }}">
                <div class="{{ VC::FM_G }}">
                    {{ Form::label(ActivitiesConstants::COL_DESC, __('Description'), ['class' => VC::FM_LB]) }}
                    <small class="form-text text-muted mb-2 mt-0">{{ __('This textarea will autosize while you type') }}</small>
                    {{ Form::textarea(ActivitiesConstants::COL_DESC, null, ['class' => VC::FM_CT, 'rows' => '1', 'data-toggle' => 'autosize']) }}
                </div>
            </div>
            <div class="{{ VC::CLMS6 }}">
                <div class="{{ VC::FM_G }}">
                    {{ Form::label(ProjectsConstants::COL_E_HRS, __('Estimated Hours'), ['class' => VC::FM_LB]) }}
                    <span class="text-danger">*</span>
                    <small class="form-text text-muted mb-2 mt-0">{{ __('allocated total ') . $allocatedHrs . __(' hrs in other tasks') }}</small>
                    {{ Form::number(ProjectsConstants::COL_E_HRS, null, ['class' => VC::FM_CT, 'required' => 'required', 'min' => '0', 'maxlength' => '8']) }}
                </div>
            </div>
            <div class="{{ VC::CLMS6 }}">
                <div class="{{ VC::FM_G }}">
                    {{ Form::label(ProjectsConstants::COL_PRT, __('Priority'), ['class' => VC::FM_LB]) }}
                    <small class="form-text text-muted mb-2 mt-0">{{ __('Set Priority of your task') }}</small>
                    <select class="{{ VC::FM_CT_SL }}" name="priority" id="priority" required>
                        @forelse($priorityOptions as $key => $val)
                            <option value="{{ $key }}">{{ __($val) }}</option>
                        @empty
                            <option value="" disabled>{{ __('No priorities available') }}</option>
                        @endforelse
                    </select>
                </div>
            </div>
            <div class="{{ VC::CLMS6 }}">
                <div class="{{ VC::FM_G }}">
                    {{ Form::label(ProjectsConstants::COL_S_DT, __('Start Date'), ['class' => VC::FM_LB]) }}
                    {{ Form::date(ProjectsConstants::COL_S_DT, null, ['class' => VC::FM_CT]) }}
                </div>
            </div>
            <div class="{{ VC::CLMS6 }}">
                <div class="{{ VC::FM_G }}">
                    {{ Form::label(ProjectsConstants::COL_E_DT, __('End Date'), ['class' => VC::FM_LB]) }}
                    {{ Form::date(ProjectsConstants::COL_E_DT, null, ['class' => VC::FM_CT]) }}
                </div>
            </div>
        </div>
        <div class="{{ VC::FM_G }}">
            <label class="{{ VC::FM_LB }}">{{ __('Task members') }}</label>
            <small class="form-text text-muted mb-2 mt-0">{{ __('Below users are assigned in your project.') }}</small>
        </div>
        <div class="{{ VC::LG_FLSH }} mb-4">
            <div class="{{ VC::RW }}">
                @forelse($projUsers as $projUser)
                    <div class="{{ VC::CS6 }}">
                        <div class="{{ VC::LGI }} px-0">
                            <div class="{{ VC::R_ALC }}">
                                <div class="{{ VC::C_AT }}">
                                    <a href="#" class="{{ VC::AV_CC_SM }}">
                                        <img class="wid-40 rounded-circle ml-3"
                                            data-original-title="{{ data_get($projUser,'name') ?? '' }}"
                                            @if(!empty(data_get($projUser,'avatar')))
                                                src="{{ asset('/storage/uploads/avatar/'.data_get($projUser,'avatar')) }}"
                                            @else
                                                src="{{ asset('/storage/uploads/avatar/avatar.png') }}"
                                            @endif />
                                    </a>
                                </div>
                                <div class="{{ VC::C12 }}">
                                    <p class="d-block h6 {{ VC::TXSM }} {{ VC::MB0 }}">{{ data_get($projUser,'name') ?? __('No user name available') }}</p>
                                    <p class="card-text {{ VC::TXSM }} {{ VC::TXT_MT }} {{ VC::MB0 }}">{{ data_get($projUser,'email') ?? __('No email available') }}</p>
                                </div>
                                <div class="{{ VC::C_AT }} text-end add_usr" data-id="{{ data_get($projUser,'id') ?? '' }}">
                                    <button type="button" class="{{ VC::BT_XS }} btn-animated btn-blue rounded-pill btn-animated-y mr-3">
                                        <span class="btn-inner--visible">
                                            <i class="{{ VC::TI_PLS }}" id="usr_icon_{{ data_get($projUser,'id') ?? 'x' }}"></i>
                                        </span>
                                        <span class="btn-inner--hidden {{ VC::TXT_WT }}" id="usr_txt_{{ data_get($projUser,'id') ?? 'x' }}">{{ __('Add') }}</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="{{ VC::CS12 }}">
                        <small class="{{ VC::TXT_MT }}">{{ __('No project users available') }}</small>
                    </div>
                @endforelse
            </div>
            {{ Form::hidden(ProjectsConstants::COL_ASGN, null) }}
        </div>
        @if((is_array($settings ?? null) || $settings instanceof \ArrayAccess) && (data_get($settings,'google_calendar_enable') === 'on'))
            <div class="{{ VC::FM_GCB6 }}">
                {{ Form::label('synchronize_type', __('Synchronize in Google Calendar ?'), ['class' => VC::FM_LB]) }}
                <div class="form-switch">
                    <input type="checkbox" class="form-check-input mt-2" name="synchronize_type" id="switch-shadow" value="google_calendar">
                    <label class="form-check-label" for="switch-shadow"></label>
                </div>
            </div>
        @endif
    </div>
    <div class="modal-footer">
        <input type="button" value="{{ __('Cancel') }}" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">
        <input type="submit" value="{{ __('Create') }}" class="{{ VC::BT_PRM }}">
    </div>
{!! Form::close() !!}
@push(StacksConstants::ADM_SCR_PG)
    <script>
        (() => {
            try {
                const guardToast = (msg) => {
                    try {
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
                            body.textContent = msg || 'Requested route is unavailable. Please contact technical support or your domain administrator.';
                            toast.appendChild(body);
                            container.appendChild(toast);
                            const inst = window.bootstrap.Toast.getOrCreateInstance(toast);
                            toast.addEventListener('hidden.bs.toast', function() { try { toast.remove(); } catch (_) {} });
                            inst.show();
                        } else {
                            alert(msg || 'Requested route is unavailable. Please contact technical support or your domain administrator.');
                        }
                    } catch (_) {}
                };

                const f = document.getElementById('{{ $formId }}');
                if (f && !(f.hasAttribute('data-submit-listener') && f.getAttribute('data-submit-listener') === 'true')) {
                    f.setAttribute('data-submit-listener', 'true');
                    f.addEventListener('submit', function(e) {
                        try {
                            const action = f.getAttribute('action') || '#';
                            if (action !== '#') return;
                            e.preventDefault();
                            const msg = f.getAttribute('data-guard-msg') || 'Create project task route is unavailable. Please contact technical support or your domain administrator.';
                            guardToast(msg);
                            f.setAttribute('data-failed-route', 'true');
                        } catch (_) {}
                    }, { passive: false });
                }

                const ai = document.getElementById('{{ $aiLinkId }}');
                if (ai && !(ai.hasAttribute('data-ai-listener') && ai.getAttribute('data-ai-listener') === 'true')) {
                    ai.setAttribute('data-ai-listener', 'true');
                    ai.addEventListener('click', function(e) {
                        try {
                            const href = ai.getAttribute('href') || '#';
                            const url = ai.getAttribute('data-url') || href || '#';
                            if (href !== '#' || url !== '#') return;
                            e.preventDefault();
                            const msg = ai.getAttribute('data-guard-msg') || 'Generate project task content route is unavailable. Please contact technical support or your domain administrator.';
                            guardToast(msg);
                            ai.setAttribute('data-failed-route', 'true');
                        } catch (_) {}
                    }, { passive: false });
                }
            } catch (_) {}
        })();
    </script>
@endpush
