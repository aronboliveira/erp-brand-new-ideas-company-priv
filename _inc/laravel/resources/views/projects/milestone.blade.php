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

@if (!empty($project) && !empty($project->id))
    @php
        $milestoneStoreBaseRouteName      = ViewsConstants::ML . '.store';
        $milestoneStoreKebabRouteName     = Str::kebab($milestoneStoreBaseRouteName);
        $milestoneStoreResolvedRouteName  = Route::has($milestoneStoreBaseRouteName)
            ? $milestoneStoreBaseRouteName
            : (Route::has($milestoneStoreKebabRouteName) ? $milestoneStoreKebabRouteName : null);
        $milestoneStoreRouteArray         = $milestoneStoreResolvedRouteName ? [$milestoneStoreResolvedRouteName, $project->id] : ['#'];
        $milestoneStoreUrl                = $milestoneStoreResolvedRouteName ? route($milestoneStoreResolvedRouteName, $project->id) : '#';
        $milestoneStoreGuardMsg           = Utility::fetchLinkMessage($lang, ViewsConstants::ML, 'project_milestone_store_route_unavailable') ?? 'Milestone store route is unavailable. Please contact technical support or your domain administrator.';
        $milestoneStoreFormId             = 'milestone-store-form-' . $project->id;
    @endphp
    {!! Form::open([
        'route'          => $milestoneStoreRouteArray,
        'method'         => 'post',
        'id'             => $milestoneStoreFormId,
        'data-url'       => $milestoneStoreUrl,
        'data-guard-msg' => $milestoneStoreGuardMsg
    ]) !!}
        @push(StacksConstants::ADM_SCR_PG)
            <script defer>
                (() => {
                    const form = document.getElementById('{{ $milestoneStoreFormId }}');
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
        @endpush
        <div class="modal-body">
            {{-- start for ai module --}}
            @php $plan = Utility::getChatGPTSettings(); @endphp
            @if($plan?->{PlansConstants::COL_GPT} == 1)
                <div class="text-end">
                    @php
                        $genProjMilestoneBaseName                          = 'generate';
                        $genProjMilestoneKebabName                         = Str::kebab($genProjMilestoneBaseName);
                        $genProjMilestoneResolvedName                      = Route::has($genProjMilestoneBaseName)
                            ? $genProjMilestoneBaseName
                            : (Route::has($genProjMilestoneKebabName) ? $genProjMilestoneKebabName : null);
                        $genProjMilestoneUrl                               = $genProjMilestoneResolvedName ? route($genProjMilestoneResolvedName, ['project milestone']) : '#';
                        $genProjMilestoneGuardMsg                          = Utility::fetchLinkMessage($lang, ViewsConstants::PRJ, 'generate_project_milestone_route_unavailable') ?? 'Generate project milestone route is unavailable. Please contact technical support or your domain administrator.';
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
                        <i class="fas fa-robot"></i> <span>{{ __('Generate with AI') }}</span>
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
                    {!! Form::select('status', \App\Models\Project::$project_status, null, ['class' => VC::FM_CT_SL, 'required' => 'required']) !!}
                    @error('status')
                        <span class="invalid-status" role="alert">
                            <strong class="text-danger">{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="{{ VC::FM_G }} {{ VC::CM6 }}">
                    {{ Form::label('start_date', __('Start Date'), ['class' => VC::FM_LB]) }}
                    {{ Form::date('start_date', '', ['class' => VC::FM_CT, 'required' => 'required']) }}
                </div>

                <div class="{{ VC::FM_G }} {{ VC::CM6 }}">
                    {{ Form::label('due_date', __('Due Date'), ['class' => VC::FM_LB]) }}
                    {{ Form::date('due_date', '', ['class' => VC::FM_CT, 'required' => 'required']) }}
                </div>

                <div class="{{ VC::FM_G }} {{ VC::CM6 }}">
                    {{ Form::label('cost', __('Cost'), ['class' => VC::FM_LB]) }}
                    {{ Form::number('cost', '', ['class' => VC::FM_CT, 'required' => 'required', 'step' => '0.01']) }}
                </div>
            </div>

            <div class="{{ VC::RW }}">
                <div class="{{ VC::FM_G }} {{ VC::C12 }}">
                    {{ Form::label('description', __('Description'), ['class' => VC::FM_LB]) }}
                    {!! Form::textarea('description', null, ['class' => VC::FM_CT, 'rows' => 2]) !!}
                    @error('description')
                        <span class="invalid-description" role="alert">
                            <strong class="text-danger">{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <input type="button" value="{{ __('Cancel') }}" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">
            <input type="submit" value="{{ __('Create') }}" class="{{ VC::BT_PRM }}">
        </div>
    {{ Form::close() }}
@else
    <div class="modal-body">
        <p>{{ __('No project found') }}</p>
    </div>
@endif
