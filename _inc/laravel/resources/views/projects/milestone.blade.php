@php
    try {
$lang = isset($lang) && $lang ? $lang : Utility::fetchUserLang();

            function resolveMilestoneRoute($baseName) {
            $kebab = Str::kebab($baseName);
            return Route::has($baseName) ? $baseName : (Route::has($kebab) ? $kebab : null);
        }

            function safeMilestoneRoute($routeName, $params = []) {
            return $routeName ? route($routeName, $params) : '#';
        }

            $guardMessages = [
            'milestone_store' => Utility::fetchLinkMessage($lang, ViewsConstants::ML, 'project_milestone_store_route_unavailable') ?? 'Milestone store route is unavailable. Please contact technical support or your domain administrator.',
            'ai_generate' => Utility::fetchLinkMessage($lang, ViewsConstants::PRJ, 'generate_project_milestone_route_unavailable') ?? 'Generate project milestone route is unavailable. Please contact technical support or your domain administrator.',
        ];
    } catch (\Throwable $e) {
        \Log::error('projects/milestone — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    }
@endphp

@if (!empty($project) && !empty($project->id))
    @php
        try {
            $milestoneStoreRouteName = resolveMilestoneRoute(ViewsConstants::ML . '.store');
            $milestoneStoreUrl = safeMilestoneRoute($milestoneStoreRouteName, $project->id);
            $milestoneStoreRouteArray = $milestoneStoreRouteName ? [$milestoneStoreRouteName, $project->id] : ['#'];
        } catch (\Throwable $e) {
            \Log::error('projects/milestone — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
        }
@endphp
    {!! Form::open([
        'route'          => $milestoneStoreRouteArray,
        'method'         => 'post',
        'id'             => 'milestone-store-form-' . $project->id,
        'data-url'       => $milestoneStoreUrl,
        'data-guard-msg' => $guardMessages['milestone_store']
    ]) !!}
        <div class="modal-body">
            {{-- start for ai module --}}
            @php
 $plan = Utility::getChatGPTSettings();
@endphp
            @if($plan?->{PlansConstants::COL_GPT} == 1)
                <div class="{{ VC::TX_END }}">
                    @php
                        $genRouteName = resolveMilestoneRoute('generate');
                        $genUrl = safeMilestoneRoute($genRouteName, ['project milestone']);
@endphp
                    <a href="{{ $genUrl }}"
                        data-size="md"
                        class="{{ VC::BT_PRM }} btn-icon btn-sm"
                        data-ajax-popup-over="true"
                        data-route-guard
                        data-url="{{ $genUrl }}"
                        data-guard-msg="{{ base64_encode($guardMessages['ai_generate']) }}"
                        data-bs-placement="top"
                        data-title="{{ __('Generate content with AI') }}">
                        <i class="{{ VC::FAS_RB }}"></i> <span>{{ __('Generate with AI') }}</span>
                    </a>
                </div>
            @endif
            {{-- end for ai module --}}

            <div class="{{ VC::RW }}">
                <div class="{{ VC::FM_G }} {{ VC::CM6 }}">
                    {{ Form::label('title', __('Title'), ['class' => VC::FM_LB]) }}
                    {{ Form::text('title', null, ['class' => VC::FM_CT, 'required' => 'required']) }}
                    @error('title')
                        <span class="invalid-title" role="alert">
                            <strong class="{{ VC::TX_DNG }}">{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="{{ VC::FM_G }} {{ VC::CM6 }}">
                    {{ Form::label('status', __('Status'), ['class' => VC::FM_LB]) }}
                    {!! Form::select('status', \App\Models\Project::$project_status, null, ['class' => VC::FM_CT_SL, 'required' => 'required']) !!}
                    @error('status')
                        <span class="invalid-status" role="alert">
                            <strong class="{{ VC::TX_DNG }}">{{ $message }}</strong>
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
                            <strong class="{{ VC::TX_DNG }}">{{ $message }}</strong>
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
