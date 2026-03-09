@php
    try {
$user              = Auth::user();
        $hasFetchUserLang  = is_callable([Utility::class, 'fetchUserLang']);
        $hasFetchLinkMsg   = is_callable([Utility::class, 'fetchLinkMessage']);
        $lang              = $hasFetchUserLang ? Utility::fetchUserLang(user: $user) : app()->getLocale();
    } catch (\Throwable $e) {
        \Log::error('job_stages/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    }
@endphp

@extends(EL::ADM)

@section(YW::ADM_PG_TTL)
    {{ __('Manage Job Stage') }}
@endsection

@section(YW::ADM_BDC)
    <li class="{{ VC::BCI }}">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}" {{ Route::has('dashboard') ? '' : 'aria-disabled=true' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="{{ VC::BCI }}">{{ __('Job Stage') }}</li>
@endsection

@section(YW::ADM_ACT_BTN)
    <div class="{{ VC::FEND }}">
        @can(PERM::CR_JST)
            @php
                try {
                    $createBase = VW::JB_STG.'.create';
                    $createUrl  = Route::has($createBase) ? route($createBase) : '#';
                    $createMsg  = ($hasFetchLinkMsg ? Utility::fetchLinkMessage($lang, VW::JB_STG, 'create_job_stage_route_unavailable') : null)
                                  ?? __('Create job stage route is unavailable. Please contact technical support or your domain administrator.');
                } catch (\Throwable $e) {
                    \Log::error('job_stages/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                }
@endphp
            <a  href="{{ $createUrl }}"
                data-url="{{ $createUrl }}"
                data-ajax-popup="true"
                data-title="{{ __('Create New Job Stage') }}"
                data-bs-toggle="tooltip"
                title="{{ __('Create') }}"
                class="{{ VC::BT_SM_PM }}"
                data-guard-msg="{{ base64_encode($createMsg) }}"
                data-sv-localized="true">
                <i class="{{ VC::TI_PLS }}"></i>
            </a>
        @endcan
    </div>
@endsection

@section(YW::ADM_CTT)
    <div class="{{ VC::RW }}">
        <div class="{{ VC::CL_XL3 }}">
            @include('layouts.hrm_setup')
        </div>
        <div class="{{ VC::CLMS9 }}">
            <div class="{{ VC::CD }}">
                <div class="{{ VC::CD_BD }}">
                    <div class="tab-content tab-bordered">
                        <div class="{{ VC::TAB_FD_SH }} active" role="tabpanel">
                            @php
                                try {
                                    $orderBase   = VW::JB_STG.'.order';
                                    $orderUrl    = Route::has($orderBase) ? route($orderBase) : '#';
                                    $orderMsg    = ($hasFetchLinkMsg ? Utility::fetchLinkMessage($lang, VW::JB_STG, 'order_job_stage_route_unavailable') : null)
                                                   ?? __('Reorder job stages route is unavailable. Please contact technical support or your domain administrator.');
                                } catch (\Throwable $e) {
                                    \Log::error('job_stages/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                }
@endphp
                            <ul id="job-stages-sortable"
                                class="{{ VC::LST_UNSTL }} {{ VC::LGRP }} sortable stage"
                                data-order-url="{{ $orderUrl }}"
                                data-guard-msg="{{ base64_encode($orderMsg) }}"
                                data-sv-localized="true">
                                @forelse ($stages as $stage)
                                    @php
                                        $sid   = isset($stage->id) ? (string)$stage->id : '';
                                        $title = isset($stage->title) && $stage->title !== '' ? $stage->title : __('Stage title was not available.');
@endphp
                                    <li class="{{ VC::DFL_AIC_JCB_IT }}" data-id="{{ $sid }}">
                                        <h6 class="{{ VC::MB0 }}">
                                            <i class="{{ VC::TI_AR_M3 }}" data-feather="move"></i>
                                            <span>{{ $title }}</span>
                                        </h6>
                                        <span class="{{ VC::FEND }}">
                                            @can(PERM::ED_JST)
                                                @php
                                                    try {
                                                        $editBase = VW::JB_STG.'.edit';
                                                        $editUrl  = (Route::has($editBase) && $sid !== '') ? route($editBase, $sid) : '#';
                                                        $editMsg  = ($hasFetchLinkMsg ? Utility::fetchLinkMessage($lang, VW::JB_STG, 'edit_job_stage_route_unavailable') : null)
                                                                    ?? __('Edit job stage route is unavailable. Please contact technical support or your domain administrator.');
                                                    } catch (\Throwable $e) {
                                                        \Log::error('job_stages/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                    }
@endphp
                                                <div class="{{ VC::ACT_BTN_PRIM }}">
                                                    <a  href="{{ $editUrl }}"
                                                        class="{{ VC::BT_SM_CT }}"
                                                        data-url="{{ $editUrl }}"
                                                        data-ajax-popup="true"
                                                        data-title="{{ __('Edit Job Stage') }}"
                                                        data-bs-toggle="tooltip"
                                                        title="{{ __('Edit') }}"
                                                        data-guard-msg="{{ base64_encode($editMsg) }}"
                                                        data-sv-localized="true">
                                                        <i class="{{ VC::TI_PC_WT }}"></i>
                                                    </a>
                                                </div>
                                            @endcan

                                            @can(PERM::DEL_JST)
                                                @php
                                                    try {
                                                        $destroyBase = VW::JB_STG.'.destroy';
                                                        $destroyUrl  = (Route::has($destroyBase) && $sid !== '') ? route($destroyBase, $sid) : '#';
                                                        $destroyMsg  = ($hasFetchLinkMsg ? Utility::fetchLinkMessage($lang, VW::JB_STG, 'destroy_job_stage_route_unavailable') : null)
                                                                        ?? __('Delete job stage route is unavailable. Please contact technical support or your domain administrator.');
                                                        $delFormId   = 'delete-form-'.($sid === '' ? 'x' : $sid);
                                                        $cTitle      = ($hasFetchLinkMsg ? Utility::fetchLinkMessage($lang, 'generics', 'are_you_sure') : null) ?? 'Are You Sure?';
                                                        $cBody       = ($hasFetchLinkMsg ? Utility::fetchLinkMessage($lang, 'generics', 'irreversible_action') : null) ?? 'This action can not be undone. Do you want to continue?';
                                                    } catch (\Throwable $e) {
                                                        \Log::error('job_stages/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                    }
@endphp
                                                <div class="{{ VC::ACT_BTN_DNG_2 }}">
                                                    {!! Form::open([
                                                        'method'            => 'DELETE',
                                                        'url'               => $destroyUrl,
                                                        'id'                => $delFormId,
                                                        'data-url'          => $destroyUrl,
                                                        'data-guard-msg'    => $destroyMsg,
                                                        'data-sv-localized' => 'true',
                                                    ]) !!}
                                                        <a  href="#"
                                                            class="{{ VC::BT_SM_CT_PR }}"
                                                            data-bs-toggle="tooltip"
                                                            title="{{ __('Delete') }}"
                                                            data-confirm="{{ __($cTitle) }}|{{ __($cBody) }}"
                                                            data-confirm-yes="document.getElementById('{{ $delFormId }}').submit();">
                                                            <i class="{{ VC::TI_TRS_WT }}"></i>
                                                        </a>
                                                    {!! Form::close() !!}
                                                </div>
                                            @endcan
                                        </span>
                                    </li>
                                @empty
                                    <li class="{{ VC::LG_IT }}">{{ __('No job stages were available to display.') }}</li>
                                @endforelse
                            </ul>
                        </div>
                    </div>
                    <p class="{{ VC::MT4 }}"><strong>{{ __('Note') }} : </strong><b>{{ __('You can easily change order of job stage using drag & drop.') }}</b></p>
                </div>
            </div>
        </div>
    </div>
@endsection

@push(ST::ADM_SCR_PG)
    <script src="{{ asset('js/jquery-ui.min.js') }}"></script>
    <script async src="{{ asset('assets/js/routes/jobs/stages/lang/index.js') }}"></script>
    <script defer src="{{ asset('assets/js/routes/jobs/stages/index.js') }}"></script>
    @if($user?->{UsersConstants::COL_TP} === PermissionsConstants::CPN)
        <script async src="{{ asset('assets/js/routes/jobs/stages/lang/sort.js') }}"></script>
        <script defer>
            (function () {
            const errFb = "# ERROR";
            const dataClientLocalized = "data-client-localized";
            const dataGuardMsg = "data-guard-msg";
            const RG = window.RouteGuard || {};
            const getMsg = RG.getMsg || ((k, el) => el?.getAttribute?.('data-guard-msg') || '# ERROR');
            const showToast = RG.showToast || (m => alert(m));
            const dataSvLocalized = "data-sv-localized";
            const dataBound = "data-sort-bound";
            const dataArmed = "data-sort-error-armed";
            const qs = (s, r = document) => r.querySelector(s);
            const $ = window.jQuery;
            const schedulePointerupError = (msgHost, msgKey) => {
                const host = msgHost || document.body;
                if (!host || host.getAttribute(dataArmed) === "true") return;
                host.setAttribute(dataArmed, "true");
                const once = () => {
                try {
                    const msg = getMsg(msgKey, host);
                    showToast(msg);
                } finally {
                    host.removeAttribute(dataArmed);
                }
                };
                document.addEventListener("pointerup", once, { once: true });
                }
                }
                return msg;
            };
            const initSortable = () => {
                if (!$ || !$.fn || typeof $.fn.sortable !== "function") {
                try {
                    if (
                        window.location.hostname === "localhost" ||
                        window.location.hostname === "127.0.0.1"
                    ) console.error("jQuery UI Sortable unavailable");
                } catch (_) {}
                schedulePointerupError(document.body, "sort_unavailable");
                return;
                }
                const $lists = $(".sortable");
                if (!$lists || $lists.length === 0) return;
                const url = "{{route(ViewsConstants::JB_STG.'.order')}}";
                const form = null;
                const href = form?.getAttribute?.("action") || null;
                if ((!url || url === "#") && (!href || href === "#")) {
                schedulePointerupError(document.body, "sort_unavailable");
                return;
                }
                $lists.each(function () {
                const el = this;
                if (el.getAttribute(dataBound) === "true") return;
                el.setAttribute(dataBound, "true");
                try {
                    $(el).sortable();
                    $(el).disableSelection();
                } catch (_) {
                    schedulePointerupError(el, "sort_unavailable");
                    return;
                }
                const onStop = function () {
                    try {
                    const order = [];
                    $(el)
                        .find("li")
                        .each(function (index, node) {
                        order[index] = $(node).attr("data-id") ?? "";
                        });
                    const target = url || href;
                    if (!target) {
                        schedulePointerupError(el, "sort_unavailable");
                        return;
                    }
                    $.ajax({
                        url: target,
                        data: {
                        order: order,
                        _token: $('meta[name="csrf-token"]').attr("content"),
                        },
                        type: "POST",
                    }).fail(function () {
                        schedulePointerupError(el, "sort_unavailable");
                    });
                    } catch (_) {
                    schedulePointerupError(el, "sort_unavailable");
                    }
                };
                $(el).sortable({ stop: onStop });
                const mo = new MutationObserver(function () {
                    if (!document.body.contains(el)) {
                    try {
                        $(el).off("sortstop", onStop);
                    } catch (_) {}
                    el.removeAttribute(dataBound);
                    mo.disconnect();
                    }
                });
                mo.observe(document.documentElement, { childList: true, subtree: true });
                });
            };
            const start = () => {
                try {
                initSortable();
                } catch (_) {
                schedulePointerupError(document.body, "sort_unavailable");
                }
            };
            if (document.readyState === "loading") {
                document.addEventListener("DOMContentLoaded", start, { once: true });
            } else {
                start();
            }
            })();
        </script>
    @endif
@endpush
