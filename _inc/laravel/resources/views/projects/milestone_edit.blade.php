@php
    use App\Config\Constants\{
        PlansConstants,
        ViewsConstants,
        ViewClassNamesConstants as VC
    };
    use App\Models\Utility;
    use Collective\Html\FormFacade as Form;
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\Str;
    $lang                                          = isset($lang) && $lang ? $lang : Utility::fetchUserLang();
@endphp
@if(!empty($milestone) && !empty($milestone->id))
    @php
        $projectMilestoneUpdateBaseName                = ViewsConstants::ML.'.update';
        $projectMilestoneUpdateKebabName               = Str::kebab($projectMilestoneUpdateBaseName);
        $projectMilestoneUpdateResolvedName            = Route::has($projectMilestoneUpdateBaseName)
            ? $projectMilestoneUpdateBaseName
            : (Route::has($projectMilestoneUpdateKebabName) ? $projectMilestoneUpdateKebabName : null);
        $projectMilestoneUpdateRouteArray              = $projectMilestoneUpdateResolvedName ? [$projectMilestoneUpdateResolvedName, $milestone->id] : ['#'];
        $projectMilestoneUpdateUrl                     = $projectMilestoneUpdateResolvedName ? route($projectMilestoneUpdateResolvedName, $milestone->id) : '#';
        $projectMilestoneUpdateGuardMsg                = Utility::fetchLinkMessage($lang, ViewsConstants::ML, 'project_milestone_update_route_unavailable') ?? 'Project milestone update route is unavailable. Please contact technical support or your domain administrator.';
        $projectMilestoneUpdateFormId                  = 'project-milestone-update-form-' . $milestone->id;
    @endphp
    {!! Form::model($milestone, [
        'route'          => $projectMilestoneUpdateRouteArray,
        'method'         => 'POST',
        'id'             => $projectMilestoneUpdateFormId,
        'data-url'       => $projectMilestoneUpdateUrl,
        'data-guard-msg' => $projectMilestoneUpdateGuardMsg
    ]) !!}
        <div class="modal-body">
            {{-- start for ai module --}}
            @php
                $plan = Utility::getChatGPTSettings();
            @endphp
            @if($plan?->{PlansConstants::COL_GPT} == 1)
                <div class="text-end">
                    @php
                        $genProjMilestoneBaseName                          = 'generate';
                        $genProjMilestoneKebabName                         = Str::kebab($genProjMilestoneBaseName);
                        $genProjMilestoneResolvedName                      = Route::has($genProjMilestoneBaseName)
                            ? $genProjMilestoneBaseName
                            : (Route::has($genProjMilestoneKebabName) ? $genProjMilestoneKebabName : null);
                        $genProjMilestoneUrl                               = $genProjMilestoneResolvedName ? route($genProjMilestoneResolvedName, ['project milestone']) : '#';
                        $genProjMilestoneGuardMsg                          = Utility::fetchLinkMessage($lang, ViewsConstants::ML, 'generate_project_milestone_route_unavailable') ?? 'Generate project milestone route is unavailable. Please contact technical support or your domain administrator.';
                        $genProjMilestoneLinkId                            = 'generate-project-milestone-link';
                    @endphp
                    <a
                        id="{{ $genProjMilestoneLinkId }}"
                        href="{{ $genProjMilestoneUrl }}"
                        data-size="md"
                        class="{{ VC::BT_PRM }} btn-icon btn-sm"
                        data-ajax-popup-over="true"
                        data-url="{{ $genProjMilestoneUrl }}"
                        data-guard-msg="{{ $genProjMilestoneGuardMsg }}"
                        data-bs-placement="top"
                        data-title="{{ __('Generate content with AI') }}"
                    >
                        <i class="{{ VC::FAS_RB }}"></i> <span>{{ __('Generate with AI') }}</span>
                    </a>
                    @push(StacksConstants::ADM_SCR_PG)
                        <script defer>
                            (() => {
                                const link = document.getElementById('{{ $genProjMilestoneLinkId }}');
                                if (!link || link.getAttribute('data-listener-active') === 'true') return;
                                link.setAttribute('data-listener-active', 'true');
                                link.addEventListener('click', e => {
                                    try {
                                        const url = link.getAttribute('data-url') || '#';
                                        if (url !== '#') return;
                                        e.preventDefault();
                                        const msg = link.getAttribute('data-guard-msg') || '# ERROR';
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
                                        link.setAttribute('data-failed-route', 'true');
                                    } catch (err) {}
                                });
                            })();
                        </script>
                    @endpush
                </div>
            @endif
            {{-- end for ai module --}}
            <div class="{{ VC::RW }}">
                <div class="{{ VC::FM_G }} {{ VC::CM6 }}">
                    {{ Form::label('title', __('Title'), ['class' => VC::FM_LB]) }}
                    {{ Form::text('title', null, ['class' => VC::FM_CT, 'required' => 'required']) }}
                    @error('title')
                        <span class="invalid-title" role="alert">
                            <strong class="text-danger">{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="{{ VC::FM_G }} {{ VC::CM6 }}">
                    {{ Form::label('status', __('Status'), ['class' => VC::FM_LB]) }}
                    {!! Form::select('status', \App\Models\Project::$project_status, null, ['class' => VC::FM_CT_SL . ' selectric', 'required' => 'required']) !!}
                    @error('client')
                        <span class="invalid-client" role="alert">
                            <strong class="text-danger">{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="{{ VC::FM_G }} {{ VC::CM6 }}">
                    {{ Form::label('start_date', __('Start Date'), ['class' => 'col-form-label']) }}
                    {{ Form::date('start_date', null, ['class' => VC::FM_CT, 'required' => 'required']) }}
                </div>

                <div class="{{ VC::FM_G }} {{ VC::CM6 }}">
                    {{ Form::label('due_date', __('Due Date'), ['class' => 'col-form-label']) }}
                    {{ Form::date('due_date', null, ['class' => VC::FM_CT, 'required' => 'required']) }}
                </div>

                <div class="{{ VC::FM_G }} col-md-12">
                    {{ Form::label('cost', __('Cost'), ['class' => 'col-form-label']) }}
                    {{ Form::number('cost', null, ['class' => VC::FM_CT, 'required' => 'required', 'step' => '0.01']) }}
                </div>
            </div>
            <div class="{{ VC::RW }}">
                <div class="{{ VC::FM_G }} col-md-12">
                    {{ Form::label('description', __('Description'), ['class' => VC::FM_LB]) }}
                    {!! Form::textarea('description', null, ['class' => VC::FM_CT, 'rows' => '2']) !!}
                </div>
            </div>
            <div class="col-md-12">
                <div class="{{ VC::FM_G }}">
                    <label for="task-summary" class="col-form-label">{{ __('Progress') }}</label>
                    <input type="range"
                           class="slider w-100 {{ VC::MB0 }}"
                           name="progress"
                           id="myRange"
                           value="{{ ($milestone->progress) ? $milestone->progress : '0' }}"
                           min="0" max="100"
                           oninput="ageOutputId.value = myRange.value">
                    <output name="ageOutputName" id="ageOutputId">{{ ($milestone->progress) ? $milestone->progress : '0' }}</output> %
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <input type="button" value="{{ __('Cancel') }}" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">
            <input type="submit" value="{{ __('Edit') }}" class="{{ VC::BT_PRM }}">
        </div>
    {{ Form::close() }}
    <script defer>
        (() => {
            const form = document.getElementById('{{ $projectMilestoneUpdateFormId }}');
            if (!form || form.getAttribute('data-listener-active') === 'true') return;
            form.setAttribute('data-listener-active', 'true');
            form.addEventListener('submit', e => {
                try {
                    const url = form.getAttribute('data-url') || '#';
                    const action = form.getAttribute('action') || '#';
                    if (url !== '#' || action !== '#') return;
                    e.preventDefault();
                    const msg = form.getAttribute('data-guard-msg') || '# ERROR';
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
                    form.setAttribute('data-failed-route', 'true');
                } catch (err) {}
            });
        })();
    </script>
@else
    <div class="alert alert-danger">
        {{ __('Milestone not found.') }}
    </div>
@endif