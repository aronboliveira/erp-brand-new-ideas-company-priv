@include('partials.helpers.route_helpers')
@php
    try {
$user = Auth::user();
        $lang = Utility::fetchUserLang(user: $user);
        $stages = ensureIterable($task_stages ?? []);
    } catch (\Throwable $e) {
        \Log::error('task_stages/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    }
@endphp

@extends(ExtendingLayoutsConstants::ADM)

@push(StacksConstants::ADM_SCR_PG)
    <script src="{{ asset('js/jquery-ui.min.js') }}"></script>
    @if($user?->{UsersConstants::COL_TP} === PermissionsConstants::CPN)
        <script async src="{{ asset('assets/js/routes/projects/tasks/stages/lang/reorder.js') }}"></script>
        <script defer src="{{ asset('assets/js/routes/projects/tasks/stages/reorder.js') }}"></script>
    @endif
@endpush

@section(YieldingConstants::ADM_PG_TTL)
    {{ __('Manage Project Task Stages') }}
@endsection

@section(YieldingConstants::ADM_BDC)
    @php
 $dashRoute = resolveRouteWithGuard('dashboard', $lang, 'dashboard', 'dashboard_route_unavailable', [], 'Dashboard unavailable.');
@endphp
    <li class="{{ VC::BCI }}">
        <a href="{{ $dashRoute['url'] }}" data-guard-msg="{{ base64_encode($dashRoute['guardMsg']) }}" data-sv-localized="true" {{ $dashRoute['resolved'] ? '' : 'aria-disabled="true"' }}>{{ __('Dashboard') }}</a>
    </li>
    <li class="{{ VC::BCI }}">{{ __('Project Task Stage') }}</li>
@endsection

@section(YieldingConstants::ADM_ACT_BTN)
    <div class="{{ VC::FEND }}">
        @can('create project task stage')
            @php
 $stageCreate = resolveRouteWithGuard(VW::PRJ_TSK_STG.'.create', $lang, VW::PRJ_TSK_STG, 'create_project_task_stage_route_unavailable', [], 'Create stage route unavailable.');
@endphp
            <a id="project-task-stage-create" href="{{ $stageCreate['url'] }}" data-url="{{ $stageCreate['url'] }}" data-guard-msg="{{ base64_encode($stageCreate['guardMsg']) }}" data-sv-localized="true" data-bs-toggle="tooltip" title="{{ __('Create') }}" class="{{ VC::BT_SM_PM }}" data-ajax-popup="true" data-title="{{ __('Create Project Task Stage') }}">
                <i class="{{ VC::TI_PLS }}"></i>
            </a>
            @push(StacksConstants::ADM_SCR_PG)
                <script defer src="{{ asset('assets/js/routes/projects/tasks/stages/create.js') }}"></script>
            @endpush
        @endcan
    </div>
@endsection

@section(YieldingConstants::ADM_CTT)
    <div class="{{ VC::RW }} justify-content-center">
        <div class="{{ VC::CS12 }} {{ VC::CM10 }} col-xxl-8">
            <div class="{{ VC::CD }} mt-5">
                <div class="{{ VC::CD_BD }}">
                    <div class="tab-content" id="pills-tabContent">
                        @php
 $i ??= 0;
@endphp
                        @forelse($stages as $key => $taskStage)
                            <div class="{{ VC::TAB_FD_SH }} {{ $i == 0 ? 'active' : '' }}" role="tabpanel">
                                <ul class="list-unstyled {{ VC::LGRP }} sortable stage">
                                    @forelse($stages as $stg)
                                        @php
                                            try {
                                                $stgId = safeDataGet($stg, 'id', 0);
                                                $stgName = safeDataGet($stg, 'name') ?: __('No stage name available');
                                                $stageEdit = resolveRouteWithGuard(VW::PRJ_TSK_STG.'.edit', $lang, VW::PRJ_TSK_STG, 'edit_project_task_stage_route_unavailable', [$stgId], 'Edit stage unavailable.');
                                                $stageDestroy = resolveRouteWithGuard(VW::PRJ_TSK_STG.'.destroy', $lang, VW::PRJ_TSK_STG, 'destroy_project_task_stage_route_unavailable', [$stgId], 'Delete stage unavailable.');
                                                $delFormId = buildElementId('delete-form', $stgId);
                                                $confirmMsg = __(Utility::fetchLinkMessage($lang, 'generics', 'are_you_sure') ?? 'Are You Sure?');
                                                $confirmSub = __(Utility::fetchLinkMessage($lang, 'generics', 'irreversible_action') ?? 'This action can not be undone. Do you want to continue?');
                                            } catch (\Throwable $e) {
                                                \Log::error('task_stages/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                            }
@endphp
                                        <li class="{{ VC::DFL_AIC_JCB_IT }}" data-id="{{ $stgId }}">
                                            <h6 class="{{ VC::MB0 }}">
                                                <i class="{{ VC::TI_AR_M3 }}" data-feather="move"></i>
                                                <span>{{ $stgName }}</span>
                                            </h6>
                                            <span class="{{ VC::FEND }}">
                                                @can('edit project task stage')
                                                    <div class="{{ VC::ACT_BTN_INF }}">
                                                        <a href="{{ $stageEdit['url'] }}" data-url="{{ $stageEdit['url'] }}" data-guard-msg="{{ base64_encode($stageEdit['guardMsg']) }}" data-sv-localized="true" data-ajax-popup="true" data-route-guard="task-stages-edit" data-bs-toggle="tooltip" title="{{ __('Edit') }}" data-title="{{ __('Edit Bug Status') }}" class="{{ VC::BT_SM_FL_CT }}">
                                                            <i class="{{ VC::TI_PC_WT }}"></i>
                                                        </a>
                                                    </div>
                                                @endcan
                                                @can('delete project task stage')
                                                    <div class="{{ VC::ACT_BTN_DNG_2 }}">
                                                        {!! Form::open(['method' => 'DELETE', 'url' => $stageDestroy['url'], 'id' => $delFormId, 'data-url' => $stageDestroy['url']]) !!}
                                                            <a href="#!" class="{{ VC::BT_SM_CT_PR }}" data-bs-toggle="tooltip" title="{{ __('Delete') }}" data-original-title="{{ __('Delete') }}" data-confirm="{{ $confirmMsg }}|{{ $confirmSub }}" data-confirm-yes="document.getElementById('{{ $delFormId }}').submit();" data-form-id="{{ $delFormId }}" data-guard-msg="{{ base64_encode($stageDestroy['guardMsg']) }}" data-route-guard="task-stages-delete" data-sv-localized="true">
                                                                <i class="{{ VC::TI_TRS_WT }}"></i>
                                                            </a>
                                                        {!! Form::close() !!}
                                                    </div>
                                                @endcan
                                            </span>
                                        </li>
                                    @empty
                                        <li><span class="{{ VC::TXT_MT }}">{{ __('No stages available') }}</span></li>
                                    @endforelse
                                </ul>
                            </div>
                            @php
 $i++;
@endphp
                        @empty
                            <div class="{{ VC::TAB_FD_SH }} active" role="tabpanel">
                                <ul class="list-unstyled {{ VC::LGRP }} sortable stage">
                                    <li><span class="{{ VC::TXT_MT }}">{{ __('No stages available') }}</span></li>
                                </ul>
                            </div>
                        @endforelse
                    </div>
                    <p class="{{ VC::MT4 }}"><strong>{{ __('Note') }} : </strong><b>{{ __('You can easily change order of project task stage using drag & drop.') }}</b></p>
                </div>
            </div>
        </div>
    </div>
@endsection

@push(StacksConstants::ADM_SCR_PG)
    <script defer src="{{ asset('assets/js/core/route-guard.js') }}"></script>
@endpush
