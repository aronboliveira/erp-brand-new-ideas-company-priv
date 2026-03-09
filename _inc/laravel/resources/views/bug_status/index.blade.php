@php
    $bugStatus ??= [];
    try {
$user = Auth::user();
        $lang                     = Utility::fetchUserLang(user:$user);
        $createRoute              = Route::has(ViewsConstants::BUG_STT . '.create')
            ? route(ViewsConstants::BUG_STT . '.create')
            : (Route::has(Str::kebab(ViewsConstants::BUG_STT . '.create'))
                ? route(Str::kebab(ViewsConstants::BUG_STT . '.create'))
                : '#');
        $createBtnId              = 'bugstatus-create-btn';
        $createGuardMsg           = Utility::fetchLinkMessage(
            $lang,
            ViewsConstants::BUG_STT,
            'bug_status_create_route_unavailable'
        ) ?? 'Create Bug Status route is unavailable. Please contact technical support or your domain administrator.';
    } catch (\Throwable $e) {
        \Log::error('bug_status/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    }
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@section(YieldingConstants::ADM_PG_TTL)
    {{__('Manage Project Bug Status')}}
@endsection
@section(YieldingConstants::ADM_BDC)
    <li class="{{ VC::BCI }}"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="{{ VC::BCI }}">{{__('Project Bug Status')}}</li>
@endsection
@push(StacksConstants::ADM_SCR_PG)
    <script src="{{asset('js/jquery-ui.min.js')}}"></script>
    @if($user?->{UsersConstants::COL_TP} === PermissionsConstants::CPN)
        <script async src="{{ asset('assets/js/routes/bugStatus/lang/order.js') }}"></script>
        <script defer src="{{ asset('assets/js/routes/bugStatus/order.js') }}"></script>
    @endif
@endpush

@section(YieldingConstants::ADM_ACT_BTN)
    @can('create bug status')
        <div class="{{ VC::FEND }}">
            <a
                id="{{ $createBtnId }}"
                href="#"
                data-url="{{ $createRoute }}"
                data-guard-msg="{{ base64_encode($createGuardMsg) }}"
                data-ajax-popup="true"
                data-title="{{ __('Create Bug Stage') }}"
                class="{{ VC::BT_SM_PM }}"
                data-bs-toggle="tooltip"
                title="{{ __('Create') }}"
            >
                <i class="{{ VC::TI_PLS }}"></i>
            </a>
        </div>
    @endcan
@endsection

@section(YieldingConstants::ADM_CTT)
    <div class="row {{ VC::JCC }}">
        <div class="CS12 CM10 col-xxl-8">
            <div class="{{ VC::CD }} mt-5">
                <div class="{{ VC::CD_BD }}">
                    <div class="tab-content" id="pills-tabContent">
                        @php
                            $i ??= 0;
                            try {
                                $bugStatusIterable =
                                    (is_array($bugStatus ?? null) && count($bugStatus ?? []) > 0) ||
                                    (($bugStatus ?? null) instanceof Collection && ($bugStatus)->isNotEmpty());
                            } catch (\Throwable $e) {
                                \Log::error('bug_status/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                            }
@endphp

                        @if($bugStatusIterable)
                            @foreach($bugStatus as $stage)
                                <div class="{{ VC::TAB_FD_SH }} @if($i === 0) active @endif" role="tabpanel">
                                    <ul class="list-unstyled {{ VC::LGRP }} sortable stage">
                                        @foreach($bugStatus as $bug)
                                            @php
                                                $bugId    = data_get($bug, 'id', 0);
                                                $bugTitle = (string) (data_get($bug, 'title') ?: __('No bug status title available'));
@endphp
                                            <li class="{{ VC::DFL_AIC_JCB_IT }}" data-id="{{ $bugId }}">
                                                <h6 class="{{ VC::MB0 }}">
                                                    <i class="{{ VC::ME3 }} {{ VC::TI_AR }}" data-feather="move"></i>
                                                    <span>{{ $bugTitle }}</span>
                                                </h6>
                                                <span class="{{ VC::FEND }}">
                                                    @can('edit bug status')
                                                        @php
                                                            try {
                                                                $langLocal   = Utility::fetchUserLang();
                                                                $editName    = ViewsConstants::BUG_STT . '.edit';
                                                                $editRoute   = Route::has($editName)
                                                                    ? route($editName, $bugId)
                                                                    : (Route::has(Str::kebab($editName))
                                                                        ? route(Str::kebab($editName), $bugId)
                                                                        : '#');
                                                                $editBtnId   = 'bugstatus-edit-btn-' . $bugId;
                                                                $editMsg     = Utility::fetchLinkMessage($langLocal, ViewsConstants::BUG_STT, 'bug_status_edit_route_unavailable')
                                                                                ?? __('Edit Bug Status route is unavailable. Please contact technical support or your domain administrator.');
                                                            } catch (\Throwable $e) {
                                                                \Log::error('bug_status/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                            }
@endphp
                                                        <a
                                                            id="{{ $editBtnId }}"
                                                            href="#"
                                                            class="{{ VC::BT_SM_FL_CT }}"
                                                            data-url="{{ $editRoute }}"
                                                            data-guard-msg="{{ base64_encode($editMsg) }}"
                                                            data-ajax-popup="true"
                                                            data-bs-toggle="tooltip"
                                                            title="{{ __('Edit') }}"
                                                            data-title="{{ __('Edit Bug Status') }}"
                                                        >
                                                            <i class="{{ VC::TI_PC_WT }}"></i>
                                                        </a>
                                                    @endcan

                                                    @can('delete bug status')
                                                        @php
                                                            try {
                                                                $langLocal       = $langLocal ?? Utility::fetchUserLang();
                                                                $destroyName     = ViewsConstants::BUG_STT . '.destroy';
                                                                $destroyRoute    = Route::has($destroyName)
                                                                    ? route($destroyName, $bugId)
                                                                    : (Route::has(Str::kebab($destroyName))
                                                                        ? route(Str::kebab($destroyName), $bugId)
                                                                        : '#');
                                                                $destroyFormId   = 'bugstatus-delete-form-' . $bugId;
                                                                $destroyBtnId    = 'bugstatus-delete-btn-' . $bugId;
                                                                $destroyMsg      = Utility::fetchLinkMessage($langLocal, ViewsConstants::BUG_STT, 'bug_status_destroy_route_unavailable')
                                                                                    ?? __('Delete Bug Status route is unavailable. Please contact technical support or your domain administrator.');
                                                            } catch (\Throwable $e) {
                                                                \Log::error('bug_status/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                            }
@endphp
                                                        {!! Collective\Html\FormFacade::open([
                                                            'url'            => $destroyRoute,
                                                            'method'         => 'DELETE',
                                                            'id'             => $destroyFormId,
                                                            'data-url'       => $destroyRoute,
                                                            'data-guard-msg' => $destroyMsg,
                                                        ]) !!}
                                                            <a
                                                                id="{{ $destroyBtnId }}"
                                                                href="#"
                                                                class="{{ VC::BT_SM_CT_PR }}"
                                                                data-bs-toggle="tooltip"
                                                                title="{{ __('Delete') }}"
                                                            >
                                                                <i class="{{ VC::TI_TRS_WT }}"></i>
                                                            </a>
                                                        {!! Collective\Html\FormFacade::close() !!}
                                                    @endcan
                                                </span>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                                @php
 $i++;
@endphp
                            @endforeach
                        @else
                            <div class="{{ VC::TAB_FD_SH }} active" role="tabpanel">
                                <div class="{{ VC::ALT_INF_MB0 }}">
                                    {{ __('No bug statuses available') }}
                                </div>
                            </div>
                        @endif
                    </div>

                    <p class="{{ VC::MT4 }}">
                        <strong>{{ __('Note') }}:</strong>
                        <b>{{ __('You can easily change order of project Bug status using drag & drop.') }}</b>
                    </p>
                </div>
            </div>
        </div>
    </div>
@endsection

@push(StacksConstants::ADM_SCR_PG)
    <script defer src="{{ asset('assets/js/core/route-guard.js') }}"></script>
    <script defer>
        window.RouteGuard?.guardMultiple?.(
            '{{ $createBtnId }}',
            @foreach($bugStatus as $bug)
                'bugstatus-edit-btn-{{ $bug->id ?? '' }}',
                'bugstatus-delete-btn-{{ $bug->id ?? '' }}',
            @endforeach
        );
    </script>
@endpush
