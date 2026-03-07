@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants as EL,
        PermissionsConstants as PERM,
        StacksConstants as ST,
        UsersConstants as UCN,
        ViewsConstants as VW,
        ViewClassNamesConstants as VC,
        YieldingConstants as YW
    };
    use App\Models\Utility;
    use Collective\Html\FormFacade as Form;
    use Illuminate\Support\Facades\{Auth, Route};

    $user              = Auth::user();
    $hasFetchUserLang  = is_callable([Utility::class, 'fetchUserLang']);
    $hasFetchLinkMsg   = is_callable([Utility::class, 'fetchLinkMessage']);
    $lang              = $hasFetchUserLang ? Utility::fetchUserLang(user: $user) : app()->getLocale();
@endphp

@extends(EL::ADM)

@section(YW::ADM_PG_TTL)
    {{ __('Manage Job Stage') }}
@endsection

@section(YW::ADM_BDC)
    <li class="breadcrumb-item">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}" {{ Route::has('dashboard') ? '' : 'aria-disabled=true' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="breadcrumb-item">{{ __('Job Stage') }}</li>
@endsection

@section(YW::ADM_ACT_BTN)
    <div class="{{ VC::FEND }}">
        @can(PERM::CR_JST)
            @php
                $createBase = VW::JB_STG.'.create';
                $createUrl  = Route::has($createBase) ? route($createBase) : '#';
                $createMsg  = ($hasFetchLinkMsg ? Utility::fetchLinkMessage($lang, VW::JB_STG, 'create_job_stage_route_unavailable') : null)
                              ?? __('Create job stage route is unavailable. Please contact technical support or your domain administrator.');
            @endphp
            <a  href="{{ $createUrl }}"
                data-url="{{ $createUrl }}"
                data-ajax-popup="true"
                data-title="{{ __('Create New Job Stage') }}"
                data-bs-toggle="tooltip"
                title="{{ __('Create') }}"
                class="{{ VC::BT_SM_PM }}"
                data-guard-msg="{{ $createMsg }}"
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
                <div class="card-body">
                    <div class="tab-content tab-bordered">
                        <div class="tab-pane fade show active" role="tabpanel">
                            @php
                                $orderBase   = VW::JB_STG.'.order';
                                $orderUrl    = Route::has($orderBase) ? route($orderBase) : '#';
                                $orderMsg    = ($hasFetchLinkMsg ? Utility::fetchLinkMessage($lang, VW::JB_STG, 'order_job_stage_route_unavailable') : null)
                                               ?? __('Reorder job stages route is unavailable. Please contact technical support or your domain administrator.');
                            @endphp
                            <ul id="job-stages-sortable"
                                class="list-unstyled list-group sortable stage"
                                data-order-url="{{ $orderUrl }}"
                                data-guard-msg="{{ $orderMsg }}"
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
                                                    $editBase = VW::JB_STG.'.edit';
                                                    $editUrl  = (Route::has($editBase) && $sid !== '') ? route($editBase, $sid) : '#';
                                                    $editMsg  = ($hasFetchLinkMsg ? Utility::fetchLinkMessage($lang, VW::JB_STG, 'edit_job_stage_route_unavailable') : null)
                                                                ?? __('Edit job stage route is unavailable. Please contact technical support or your domain administrator.');
                                                @endphp
                                                <div class="{{ VC::ACT_BTN_PRIM }}">
                                                    <a  href="{{ $editUrl }}"
                                                        class="{{ VC::BT_SM_CT }}"
                                                        data-url="{{ $editUrl }}"
                                                        data-ajax-popup="true"
                                                        data-title="{{ __('Edit Job Stage') }}"
                                                        data-bs-toggle="tooltip"
                                                        title="{{ __('Edit') }}"
                                                        data-guard-msg="{{ $editMsg }}"
                                                        data-sv-localized="true">
                                                        <i class="{{ VC::TI_PC_WT }}"></i>
                                                    </a>
                                                </div>
                                            @endcan

                                            @can(PERM::DEL_JST)
                                                @php
                                                    $destroyBase = VW::JB_STG.'.destroy';
                                                    $destroyUrl  = (Route::has($destroyBase) && $sid !== '') ? route($destroyBase, $sid) : '#';
                                                    $destroyMsg  = ($hasFetchLinkMsg ? Utility::fetchLinkMessage($lang, VW::JB_STG, 'destroy_job_stage_route_unavailable') : null)
                                                                    ?? __('Delete job stage route is unavailable. Please contact technical support or your domain administrator.');
                                                    $delFormId   = 'delete-form-'.($sid === '' ? 'x' : $sid);
                                                    $cTitle      = ($hasFetchLinkMsg ? Utility::fetchLinkMessage($lang, 'generics', 'are_you_sure') : null) ?? 'Are You Sure?';
                                                    $cBody       = ($hasFetchLinkMsg ? Utility::fetchLinkMessage($lang, 'generics', 'irreversible_action') : null) ?? 'This action can not be undone. Do you want to continue?';
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
                                    <li class="list-group-item">{{ __('No job stages were available to display.') }}</li>
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
            const dataSvLocalized = "data-sv-localized";
            const dataBound = "data-sort-bound";
            const dataArmed = "data-sort-error-armed";
            const qs = (s, r = document) => r.querySelector(s);
            const $ = window.jQuery;
            const hasBS = () =>
                !!(
                qs('link[rel="stylesheet"][href*="bootstrap"]') ||
                qs('link[href*="bootstrap"]')
                ) && !!(window.bootstrap && window.bootstrap.Toast);
            const ensureToastContainer = () => {
                let c = qs("#np-toast-container");
                if (c) return c;
                c = document.createElement("div");
                c.id = "np-toast-container";
                c.setAttribute("aria-live", "polite");
                c.setAttribute("aria-atomic", "true");
                c.style.position = "fixed";
                c.style.top = "1rem";
                c.style.right = "1rem";
                document.body.appendChild(c);
                return c;
            };
            const showToast = message => {
                const container = ensureToastContainer();
                let t = qs("#np-toast", container);
                if (!t) {
                t = document.createElement("div");
                t.id = "np-toast";
                t.className = "toast";
                t.setAttribute("role", "alert");
                t.setAttribute("aria-live", "assertive");
                t.setAttribute("aria-atomic", "true");
                t.innerHTML =
                    '<div class="toast-header"><strong class="me-auto">{{ __('Notice') }}</strong><button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="{{ __('Close') }}"></button></div><div class="toast-body"></div>';
                container.appendChild(t);
                }
                const body = t.querySelector(".toast-body");
                if (body) body.textContent = message ?? errFb;
                try {
                new window.bootstrap.Toast(t, { autohide: true, delay: 4000 }).show();
                } catch (_) {
                alert(message ?? errFb);
                }
            };
            const schedulePointerupError = (msgHost, msgKey) => {
                const host = msgHost || document.body;
                if (!host || host.getAttribute(dataArmed) === "true") return;
                host.setAttribute(dataArmed, "true");
                const once = () => {
                try {
                    const msg = getMsg(host, msgKey);
                    hasBS() ? showToast(msg) : alert(msg);
                } finally {
                    host.removeAttribute(dataArmed);
                }
                };
                document.addEventListener("pointerup", once, { once: true });
                const mo = new MutationObserver((m, o) => {
                if (!document.body.contains(host)) {
                    document.removeEventListener("pointerup", once);
                    o.disconnect();
                }
                });
                mo.observe(document.documentElement, { childList: true, subtree: true });
            };
            const getMsg = function (el, msgKey) {
                let msg = errFb;
                if (
                el.getAttribute(dataSvLocalized) === "true" ||
                el.getAttribute(dataClientLocalized) === "true"
                ) {
                msg = el.getAttribute(dataGuardMsg) || errFb;
                } else {
                let lang = (
                    window.sessionStorage.getItem("erp-np-lang") ||
                    document.documentElement.lang ||
                    "en"
                )
                    .toLowerCase()
                    .replace(/_/g, "-");
                lang = lang === "pt-br" ? lang : lang.slice(0, 2);
                const k = msgKey;
                msg =
                    window.translations?.[lang]?.[k] ||
                    el.getAttribute(dataGuardMsg) ||
                    window.translations?.["en"]?.[k] ||
                    errFb;
                if (msg !== errFb) {
                    el.setAttribute(dataGuardMsg, msg);
                    el.setAttribute(dataClientLocalized, "true");
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