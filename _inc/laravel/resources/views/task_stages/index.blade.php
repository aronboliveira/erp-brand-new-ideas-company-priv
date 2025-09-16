@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        PermissionsConstants,
        StacksConstants,
        UsersConstants,
        ViewsConstants as VW,
        ViewClassNamesConstants as VC,
        YieldingConstants,
    };
    use App\Models\Utility;
    use Illuminate\Support\Facades\{Auth, Route};
    use Illuminate\Support\{Collection, Str};
    $user = Auth::user();
    $lang = Utility::fetchUserLang(user: $user);
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@push(StacksConstants::ADM_SCR_PG)
    <script src="{{asset('js/jquery-ui.min.js')}}"></script>
    @if($user?->{UsersConstants::COL_TP} === PermissionsConstants::CPN)
        <script async src="{{ asset('assets/js/routes/projects/tasks/stages/lang/reorder.js') }}"></script>
        <script defer src="{{ asset('assets/js/routes/projects/tasks/stages/reorder.js') }}"></script>
    @endif
@endpush
@section(YieldingConstants::ADM_PG_TTL)
    {{__('Manage Project Task Stages')}}
@endsection
@section(YieldingConstants::ADM_BDC)
    <li class="breadcrumb-item">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
        {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="breadcrumb-item">{{__('Project Task Stage')}}</li>
@endsection
@section(YieldingConstants::ADM_ACT_BTN)
    <div class="float-end">
        @can('create project task stage')
                @php
                    $taskStageCreateBase = VW::PRJ_TSK_STG.'.create';
                    $taskStageCreateKebab = Str::kebab($taskStageCreateBase);
                    $taskStageCreateResolved = Route::has($taskStageCreateBase) ? $taskStageCreateBase : (Route::has($taskStageCreateKebab) ? $taskStageCreateKebab : null);
                    $taskStageCreateUrl = $taskStageCreateResolved ? route($taskStageCreateResolved) : '#';
                    $langValue = isset($lang) ? $lang : Utility::fetchUserLang();
                    $taskStageCreateGuardMsg = Utility::fetchLinkMessage($langValue, VW::PRJ_TSK_STG, 'create_project_task_stage_route_unavailable') ?? 'Create project task stage route is unavailable. Please contact technical support or your domain administrator.';
                    $taskStageCreateAnchorId = 'project-task-stage-create';
                @endphp
                <a id="{{ $taskStageCreateAnchorId }}"
                href="{{ $taskStageCreateUrl }}"
                data-url="{{ $taskStageCreateUrl }}"
                data-guard-msg="{{ $taskStageCreateGuardMsg }}"
                data-sv-localized="true"
                data-bs-toggle="tooltip"
                title="{{ __('Create') }}"
                class="{{ VC::BT_SM_PM }}"
                data-ajax-popup="true"
                data-title="{{ __('Create Project Task Stage') }}">
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
        <div class="col-sm-12 col-md-10 col-xxl-8">
            <div class="{{ VC::CD }} mt-5">
                <div class="card-body">
                    <div class="tab-content" id="pills-tabContent">
                        @php($i=0)
                        @forelse((($task_stages ?? null) instanceof Collection || is_array($task_stages ?? null)) ? $task_stages : [] as $key => $task_stage)
                            <div class="tab-pane fade show @if($i==0) active @endif" role="tabpanel">
                                <ul class="list-unstyled {{ VC::LGRP }} sortable stage">
                                    @forelse((($task_stages ?? null) instanceof Collection || is_array($task_stages ?? null)) ? $task_stages : [] as $stg)
                                        <li class="{{ VC::DFL_AIC_JCB_IT }}" data-id="{{ data_get($stg,'id',0) }}">
                                            <h6 class="{{ VC::MB0 }}">
                                                <i class="{{ VC::TI_AR_M3 }}" data-feather="move"></i>
                                                <span>{{ data_get($stg,'name') ?: __('No stage name available') }}</span>
                                            </h6>
                                            <span class="{{ VC::FEND }}">
                                                @can('edit project task stage')
                                                    <div class="{{ VC::ACT_BTN_INF }}">
                                                        @php
                                                            $taskStageEditBase = VW::PRJ_TSK_STG.'.edit';
                                                            $taskStageEditKebab = Str::kebab($taskStageEditBase);
                                                            $taskStageEditResolved = Route::has($taskStageEditBase) ? $taskStageEditBase : (Route::has($taskStageEditKebab) ? $taskStageEditKebab : null);
                                                            $stgIdValue = data_get($stg,'id');
                                                            $taskStageEditUrl = ($taskStageEditResolved && $stgIdValue) ? route($taskStageEditResolved,$stgIdValue) : '#';
                                                            $langValue = isset($lang) ? $lang : Utility::fetchUserLang();
                                                            $taskStageEditGuardMsg = Utility::fetchLinkMessage($langValue, VW::PRJ_TSK_STG, 'edit_project_task_stage_route_unavailable') ?? 'Edit project task stage route is unavailable. Please contact technical support or your domain administrator.';
                                                            $taskStageEditAnchorId = 'project-task-stage-edit-'.($stgIdValue ?? 'x');
                                                        @endphp
                                                        <a id="{{ $taskStageEditAnchorId }}"
                                                        href="{{ $taskStageEditUrl }}"
                                                        data-url="{{ $taskStageEditUrl }}"
                                                        data-guard-msg="{{ $taskStageEditGuardMsg }}"
                                                        data-sv-localized="true"
                                                        data-ajax-popup="true"
                                                        data-bs-toggle="tooltip"
                                                        title="{{ __('Edit') }}"
                                                        data-title="{{ __('Edit Bug Status') }}"
                                                        class="{{ VC::BT_SM_FL_CT }}">
                                                            <i class="{{ VC::TI_PC_WT }}"></i>
                                                        </a>
                                                        @push(StacksConstants::ADM_SCR_PG)
                                                            <script defer>
                                                                (() => {
                                                                    try {
                                                                        const el = document.getElementById('{{ $taskStageEditAnchorId }}');
                                                                        if (!el) { return; }
                                                                        if (el.getAttribute('data-listener-active') === 'true') { return; }
                                                                        el.setAttribute('data-listener-active','true');
                                                                        el.addEventListener('click',(e) => {
                                                                            try {
                                                                                const href = el.getAttribute('href') ?? '#';
                                                                                const url = el.getAttribute('data-url') ?? href ?? '#';
                                                                                if (url !== '#' && href !== '#') { return; }
                                                                                e.preventDefault();
                                                                                const msg = el.getAttribute('data-guard-msg') ?? 'Edit project task stage route is unavailable. Please contact technical support or your domain administrator.';
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
                                                                                el.setAttribute('data-failed-route','true');
                                                                            } catch (err) {}
                                                                        });
                                                                    } catch (err) {}
                                                                })();
                                                            </script>
                                                        @endpush
                                                    </div>
                                                @endcan
                                                @can('delete project task stage')
                                                    <div class="{{ VC::ACT_BTN_DNG_2 }}">
                                                        @php
                                                            $taskStageDestroyBase = VW::PRJ_TSK_STG.'.destroy';
                                                            $taskStageDestroyKebab = Str::kebab($taskStageDestroyBase);
                                                            $taskStageDestroyResolved = Route::has($taskStageDestroyBase) ? $taskStageDestroyBase : (Route::has($taskStageDestroyKebab) ? $taskStageDestroyKebab : null);
                                                            $stgIdValue = data_get($stg,'id',0);
                                                            $taskStageDestroyUrl = ($taskStageDestroyResolved && $stgIdValue) ? route($taskStageDestroyResolved,$stgIdValue) : '#';
                                                            $langValue = isset($lang) ? $lang : Utility::fetchUserLang();
                                                            $taskStageDestroyGuardMsg = Utility::fetchLinkMessage($langValue, VW::PRJ_TSK_STG, 'destroy_project_task_stage_route_unavailable') ?? 'Destroy project task stage route is unavailable. Please contact technical support or your domain administrator.';
                                                            $delFormId = 'delete-form-'.($stgIdValue ?? 'x');
                                                            $delAnchorId = 'delete-anchor-'.($stgIdValue ?? 'x');
                                                            $confirmMsg = __(Utility::fetchLinkMessage($langValue, 'generics', 'are_you_sure') ?? 'Are You Sure?');
                                                            $confirmSub = __(Utility::fetchLinkMessage($langValue, 'generics', 'irreversible_action') ?? 'This action can not be undone. Do you want to continue?');
                                                        @endphp
                                                        {!! Form::open([
                                                            'method' => 'DELETE',
                                                            'url' => $taskStageDestroyUrl,
                                                            'id' => $delFormId,
                                                            'data-url' => $taskStageDestroyUrl
                                                        ]) !!}
                                                            <a id="{{ $delAnchorId }}"
                                                            href="#!"
                                                            class="{{ VC::BT_SM_CT_PR }}"
                                                            data-bs-toggle="tooltip"
                                                            title="{{ __('Delete') }}"
                                                            data-original-title="{{ __('Delete') }}"
                                                            data-confirm="{{ $confirmMsg }}|{{ $confirmSub }}"
                                                            data-confirm-yes="document.getElementById('{{ $delFormId }}').submit();"
                                                            data-form-id="{{ $delFormId }}"
                                                            data-guard-msg="{{ $taskStageDestroyGuardMsg }}"
                                                            data-sv-localized="true">
                                                                <i class="{{ VC::TI_TRS_WT }}"></i>
                                                            </a>
                                                        {!! Form::close() !!}
                                                        @push(StacksConstants::ADM_SCR_PG)
                                                            <script defer>
                                                                (() => {
                                                                    try {
                                                                        const aId = '{{ $delAnchorId }}';
                                                                        const el = document.getElementById(aId);
                                                                        if (!el) { return; }
                                                                        if (el.getAttribute('data-listener-active') === 'true') { return; }
                                                                        el.setAttribute('data-listener-active','true');
                                                                        el.addEventListener('click',(e) => {
                                                                            try {
                                                                                const fid = el.getAttribute('data-form-id') ?? '';
                                                                                if (!fid) { return; }
                                                                                const fm = document.getElementById(fid);
                                                                                if (!fm) { return; }
                                                                                const action = fm.getAttribute('action') ?? '#';
                                                                                const url = fm.getAttribute('data-url') ?? action ?? '#';
                                                                                if (url !== '#' && action !== '#') { return; }
                                                                                e.preventDefault();
                                                                                const msg = el.getAttribute('data-guard-msg') ?? 'Destroy project task stage route is unavailable. Please contact technical support or your domain administrator.';
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
                                                                                el.setAttribute('data-failed-route','true');
                                                                            } catch (err) {}
                                                                        });
                                                                    } catch (err) {}
                                                                })();
                                                            </script>
                                                        @endpush
                                                    </div>
                                                @endcan
                                            </span>
                                        </li>
                                    @empty
                                        <li><span class="text-muted">{{ __('No stages available') }}</span></li>
                                    @endforelse
                                </ul>
                            </div>
                            @php($i++)
                        @empty
                            <div class="tab-pane fade show active" role="tabpanel">
                                <ul class="list-unstyled {{ VC::LGRP }} sortable stage">
                                    <li><span class="text-muted">{{ __('No stages available') }}</span></li>
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
