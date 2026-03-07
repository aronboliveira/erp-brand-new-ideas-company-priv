@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants as EL,
        StacksConstants as ST,
        ViewsConstants as VW,
        ViewClassNamesConstants as VC,
        YieldingConstants as YD
    };
    use App\Models\Utility;
    use Collective\Html\FormFacade as Form;
    use Illuminate\Support\Facades\{Auth, Route};
    use Illuminate\Support\Collection;

    $user = Auth::user();

    $hasFetchLinkMessage = is_callable([Utility::class, 'fetchLinkMessage']);
    $hasSettingsFn       = is_callable([Utility::class, 'settings']);
    $lang                = is_callable([Utility::class, 'fetchUserLang']) ? Utility::fetchUserLang(user:$user) : app()->getLocale();

    $settings = $hasSettingsFn ? Utility::settings() : [];

    $dashRoute   = Route::has('dashboard') ? route('dashboard') : '#';
    $dashGuard   = ($hasFetchLinkMessage ? Utility::fetchLinkMessage($lang, 'generics', 'dashboard_unavailable') : null)
                   ?? __('Dashboard route is unavailable. Please contact technical support or your domain administrator.');

    $calendarRoute = Route::has(VW::MT.'.calendar') ? route(VW::MT.'.calendar') : '#';
    $calGuard      = ($hasFetchLinkMessage ? Utility::fetchLinkMessage($lang, VW::MT, 'calendar_route_unavailable') : null)
                     ?? __('Calendar view route is unavailable. Please contact technical support or your domain administrator.');

    $eventsRoute = Route::has(VW::MT.'.get_event_data') ? route(VW::MT.'.get_event_data') : '#';
    $eventsGuard = ($hasFetchLinkMessage ? Utility::fetchLinkMessage($lang, VW::MT, 'calendar_data_unavailable') : null)
                   ?? __('Meeting calendar data was not available.');

@endphp

@extends(EL::ADM)

@section(YD::ADM_PG_TTL)
    {{ __('Manage Meeting') }}
@endsection

@push(ST::ADM_CSS)
    <link rel="stylesheet" href="{{ asset('assets/libs/fullcalendar/dist/fullcalendar.min.css') }}">
@endpush

@section(YD::ADM_BDC)
    <li class="breadcrumb-item">
        <a href="{{ $dashRoute }}"
           data-url="{{ $dashRoute }}"
           data-sv-localized="true"
           data-guard-msg="{{ $dashGuard }}"
           {{ $dashRoute !== '#' ? '' : 'aria-disabled=true' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="breadcrumb-item">{{ __('Meeting') }}</li>
@endsection

@section(YD::ADM_ACT_BTN)
    @can('create meeting')
        @php
            $listUrl   = Route::has(VW::MT.'.index') ? route(VW::MT.'.index') : '#';
            $listGuard = ($hasFetchLinkMessage ? Utility::fetchLinkMessage($lang, VW::MT, 'list_route_unavailable') : null)
                         ?? __('List view route is unavailable. Please contact technical support or your domain administrator.');

            $createUrl   = Route::has(VW::MT.'.create') ? route(VW::MT.'.create') : '#';
            $createGuard = ($hasFetchLinkMessage ? Utility::fetchLinkMessage($lang, VW::MT, 'create_meeting_unavailable') : null)
                           ?? __('Create meeting route is unavailable. Please contact technical support or your domain administrator.');
        @endphp
        <div class="{{ VC::FEND }}">
            <a href="{{ $listUrl }}"
               class="{{ VC::BT_SM_PM }}"
               data-url="{{ $listUrl }}"
               data-sv-localized="true"
               data-guard-msg="{{ $listGuard }}"
               data-bs-toggle="tooltip"
               title="{{ __('List View') }}">
                <i class="{{ VC::TI_LT }}"></i>
            </a>
            <a href="{{ $createUrl }}"
               class="{{ VC::BT_SM_PM }}"
               data-url="{{ $createUrl }}"
               data-ajax-popup="true"
               data-size="lg"
               data-title="{{ __('Create New Meeting') }}"
               data-sv-localized="true"
               data-guard-msg="{{ $createGuard }}"
               data-bs-toggle="tooltip"
               title="{{ __('Create') }}">
                <i class="{{ VC::TI_PLS }}"></i>
            </a>
        </div>
    @endcan
@endsection

@section(YD::ADM_CTT)
    <div class="{{ VC::RW }}">
        <div class="{{ VC::CS12 }}">
            <div class="{{ VC::MT1 }}" id="multiCollapseExample1">
                <div class="{{ VC::CD }}">
                    <div class="card-body">
                        {!! Form::open([
                            'route'              => $calendarRoute !== '#' ? [VW::MT.'.calendar'] : ['#'],
                            'method'             => 'get',
                            'id'                 => 'meeting_filter',
                            'data-url'           => $calendarRoute,
                            'data-sv-localized'  => 'true',
                            'data-guard-msg'     => $calGuard
                        ]) !!}
                        <div class="{{ VC::R_ALC_JCE }}">
                            <div class="{{ VC::CLMS10 }}">
                                <div class="{{ VC::RW }}">
                                    <div class="{{ VC::CL_POS3 }}"><div class="btn-box"></div></div>
                                    <div class="{{ VC::CL_POS3 }}"><div class="btn-box"></div></div>

                                    <div class="{{ VC::CL_POS3 }}">
                                        <div class="btn-box">
                                            {!! Form::label('start_date', __('Start Date'), ['class' => VC::FM_LB]) !!}
                                            {!! Form::date('start_date', request('start_date'), ['class' => 'month-btn '.VC::FM_CT]) !!}
                                        </div>
                                    </div>

                                    <div class="{{ VC::CL_POS3 }}">
                                        <div class="btn-box">
                                            {!! Form::label('end_date', __('End Date'), ['class' => VC::FM_LB]) !!}
                                            {!! Form::date('end_date', request('end_date'), ['class' => 'month-btn '.VC::FM_CT]) !!}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="{{ VC::C_AT }}">
                                <div class="{{ VC::RW }}">
                                    @php
                                        $applyTitle = __('Apply');
                                        $resetTitle = __('Reset');
                                    @endphp
                                    <div class="{{ VC::C_AT }} {{ VC::MT4 }}">
                                        <a href="#"
                                           class="{{ VC::BT_SM_PM }}"
                                           onclick="document.getElementById('meeting_filter').submit(); return false;"
                                           data-bs-toggle="tooltip"
                                           title="{{ $applyTitle }}">
                                            <span class="btn-inner--icon"><i class="{{ VC::TI_SRC }}"></i></span>
                                        </a>
                                        <a href="{{ $calendarRoute }}"
                                           class="{{ VC::BT_SM_DG }}"
                                           data-url="{{ $calendarRoute }}"
                                           data-sv-localized="true"
                                           data-guard-msg="{{ $calGuard }}"
                                           data-bs-toggle="tooltip"
                                           title="{{ $resetTitle }}">
                                            <span class="btn-inner--icon"><i class="{{ VC::TI_TRS_OFF }}"></i></span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="{{ VC::RW }}">
        <div class="{{ VC::CLMS6 }}">
            <div class="{{ VC::CD }}">
                <div class="card-header">
                    <div class="{{ VC::RW }}">
                        <div class="{{ VC::CLMS6 }}"><h5>{{ __('Calendar') }}</h5></div>
                        <div class="{{ VC::CLMS6 }}">
                            @if (!empty($settings) && ($settings['google_calendar_enable'] ?? null) === 'on')
                                <select class="{{ VC::FM_CT }}" name="calendar_type" id="calendar_type" style="float: right;width: 150px;">
                                    <option value="google_calendar">{{ __('Google calendar') }}</option>
                                    <option value="local_calendar" selected="true">{{ __('Local calendar') }}</option>
                                </select>
                            @endif
                            <input
                                type="hidden"
                                id="meeting_calendar"
                                value="{{ url('/') }}"
                                data-events-url="{{ $eventsRoute }}"
                                data-guard-msg="{{ $eventsGuard }}"
                                data-sv-localized="true"
                            >
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div id="calendar" class="calendar"></div>
                </div>
            </div>
        </div>

        <div class="{{ VC::CLMS6 }}">
            <div class="{{ VC::CD }}">
                <div class="card-body">
                    <h4 class="{{ VC::MB4 }}">{{ __('Meeting List') }}</h4>
                    <ul class="{{ VC::LG_FLSH_W }}">
                        <li class="{{ VC::LGI }} {{ VC::CD }} {{ VC::MB3 }}">
                            <div class="{{ VC::RW }} {{ VC::JCB }} {{ VC::ALC }}">
                                <div class="{{ VC::AL_IT_CT }}">
                                    @php
                                        $items = [];
                                        if (is_array($meetings ?? null) && count($meetings ?? []) > 0) {
                                            $items = $meetings;
                                        } elseif (($meetings ?? null) instanceof Collection && $meetings->isNotEmpty()) {
                                            $items = $meetings;
                                        }
                                        $hasUserDate = $user && method_exists($user, 'dateFormat');
                                    @endphp

                                    @forelse($items as $meeting)
                                        @php
                                            $title = isset($meeting->title) && $meeting->title !== ''
                                                ? $meeting->title
                                                : __('Meeting title was not available.');
                                            $dateRaw = $meeting->date ?? null;
                                            $dateTxt = $dateRaw
                                                ? ($hasUserDate ? ($user->dateFormat($dateRaw) ?? __('Failed to format meeting date.')) : __('Failed to format meeting date.'))
                                                : __('Meeting date was not available.');
                                            $mid = $meeting->id ?? null;
                                        @endphp
                                        <div class="{{ VC::CD }} {{ VC::MB3 }} {{ VC::CD_NSD }}">
                                            <div class="{{ VC::PX3 ?? 'px-3' }}">
                                                <div class="{{ VC::RW }} {{ VC::ALC }}">
                                                    <div class="col ml-n2">
                                                        <p class="card-text small text-primary mb-0">{{ $title }}</p>
                                                        <p class="card-text small text-dark mb-0">
                                                            {{ __('Meeting Date :') }} {{ $dateTxt }}
                                                        </p>
                                                    </div>
                                                    <div class="col-auto text-right">
                                                        @can('edit meeting')
                                                            @php
                                                                $editUrl   = ($mid !== null && Route::has(VW::MT.'.edit')) ? route(VW::MT.'.edit', $mid) : '#';
                                                                $editGuard = ($hasFetchLinkMessage ? Utility::fetchLinkMessage($lang, VW::MT, 'edit_meeting_unavailable') : null)
                                                                            ?? __('Edit meeting route is unavailable. Please contact technical support or your domain administrator.');
                                                            @endphp
                                                            <div class="{{ VC::ACT_BTN_PRIM }}">
                                                                <a href="#"
                                                                   class="{{ VC::BT_SM_CT }}"
                                                                   data-url="{{ $editUrl }}"
                                                                   data-ajax-popup="true"
                                                                   data-title="{{ __('Edit Meeting') }}"
                                                                   data-sv-localized="true"
                                                                   data-guard-msg="{{ $editGuard }}"
                                                                   data-bs-toggle="tooltip"
                                                                   title="{{ __('Edit') }}">
                                                                    <i class="{{ VC::TI_PC_WT }}"></i>
                                                                </a>
                                                            </div>
                                                        @endcan

                                                        @can('delete meeting')
                                                            @php
                                                                $delUrl   = ($mid !== null && Route::has(VW::MT.'.destroy')) ? route(VW::MT.'.destroy', $mid) : '#';
                                                                $delGuard = ($hasFetchLinkMessage ? Utility::fetchLinkMessage($lang, VW::MT, 'delete_meeting_unavailable') : null)
                                                                            ?? __('Delete meeting route is unavailable. Please contact technical support or your domain administrator.');
                                                                $formId   = 'delete-form-'.$mid;
                                                                $confirmA = ($hasFetchLinkMessage ? Utility::fetchLinkMessage($lang, 'generics', 'are_you_sure') : null) ?? __('Are You Sure?');
                                                                $confirmB = ($hasFetchLinkMessage ? Utility::fetchLinkMessage($lang, 'generics', 'irreversible_action') : null) ?? __('This action can not be undone. Do you want to continue?');
                                                            @endphp
                                                            <div class="{{ VC::ACT_BTN_DNG_2 }}">
                                                                {!! Form::open([
                                                                    'method'            => 'DELETE',
                                                                    'url'               => $delUrl,
                                                                    'id'                => $formId,
                                                                    'data-url'          => $delUrl,
                                                                    'data-sv-localized' => 'true',
                                                                    'data-guard-msg'    => $delGuard
                                                                ]) !!}
                                                                    <a href="#"
                                                                       class="{{ VC::BT_SM_CT_PR }}"
                                                                       data-bs-toggle="tooltip"
                                                                       title="{{ __('Delete') }}"
                                                                       data-confirm="{{ __($confirmA) }}|{{ __($confirmB) }}"
                                                                       data-confirm-yes="document.getElementById('{{ $formId }}').submit();">
                                                                        <i class="{{ VC::TI_TRS_WT }}"></i>
                                                                    </a>
                                                                {!! Form::close() !!}
                                                            </div>
                                                        @endcan
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="text-center">
                                            {{ __('No meetings found.') }}
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
@endsection

@push(ST::ADM_SCR_PG)
<script src="{{ asset('assets/js/plugins/main.min.js') }}"></script>
<script defer src="{{ asset('assets/js/routes/meetings/calendar/index.js') }}"></script>
    <script async src="{{ asset('assets/js/routes/meetings/lang/calendar.js') }}"></script>
    <script async>
        (function () {
        const errFb = "# ERROR";
        const dataClientLocalized = "data-client-localized";
        const dataGuardMsg = "data-guard-msg";
        const dataSvLocalized = "data-sv-localized";
        const dataErrArmed = "data-err-armed";
        const dataChoicesBound = "data-choices-bound";
        const qs = (s, r = document) => r.querySelector(s);
        const $ = window.jQuery;
        const getMsg = function (el, key) {
            let msg = errFb;
            const dataClientLocalizedAttr = "data-client-localized";
            const dataGuardMsgAttr = "data-guard-msg";
            if (
            el.getAttribute("data-sv-localized") === "true" ||
            el.getAttribute(dataClientLocalizedAttr) === "true"
            ) {
            msg = el.getAttribute(dataGuardMsgAttr) || errFb;
            } else {
            let lang = (
                window.sessionStorage.getItem("erp-np-lang") ||
                document.documentElement.lang ||
                "en"
            )
                .toLowerCase()
                .replace(/_/g, "-");
            lang = lang === "pt-br" ? lang : lang.slice(0, 2);
            const msgKey = key;
            msg =
                window.translations?.[lang]?.[msgKey] ||
                el.getAttribute(dataGuardMsgAttr) ||
                window.translations?.["en"]?.[msgKey] ||
                errFb;
            if (msg !== errFb) {
                el.setAttribute(dataGuardMsgAttr, msg);
                el.setAttribute(dataClientLocalizedAttr, "true");
            }
            }
            return msg;
        };
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
        const schedulePointerupError = (key, host) => {
            const el = host || document.body;
            if (!el || el.getAttribute(dataErrArmed) === "true") return;
            el.setAttribute(dataErrArmed, "true");
            const once = () => {
            try {
                const m = getMsg(el, key);
                hasBS() ? showToast(m) : alert(m);
            } finally {
                el.removeAttribute(dataErrArmed);
            }
            };
            document.addEventListener("pointerup", once, { once: true });
            const mo = new MutationObserver((m, o) => {
            if (!document.body.contains(el)) {
                document.removeEventListener("pointerup", once);
                o.disconnect();
            }
            });
            mo.observe(document.documentElement, { childList: true, subtree: true });
        };
        const safeAjax = (opts, failKey) => {
            try {
            $.ajax(opts).fail(function () {
                schedulePointerupError(failKey);
            });
            } catch (_) {
            schedulePointerupError(failKey);
            }
        };
        const initCalendar = () => {
            const calEl = qs("#calendar");
            if (!calEl) {
            schedulePointerupError("element_unavailable");
            return;
            }
            const base = $("#meeting_calendar").val?.();
            const url = (base ?? "").toString().trim()
            ? base + "/meeting/get_meeting_data"
            : "";
            if (!url || url === "#") {
            schedulePointerupError("calendar_unavailable");
            return;
            }
            if (!window.FullCalendar || !window.FullCalendar.Calendar) {
            try {
                if (
                    window.location.hostname === "localhost" ||
                    window.location.hostname === "127.0.0.1"
                ) console.error("FullCalendar unavailable");
            } catch (_) {}
            schedulePointerupError("calendar_unavailable");
            return;
            }
            let calendar_type = $("#calendar_type :selected").val?.();
            $("#calendar").removeClass("local_calendar").removeClass("google_calendar");
            if (calendar_type == null) {
            $("#calendar").addClass("local_calendar");
            }
            $("#calendar").addClass(calendar_type ?? "");
            safeAjax(
            {
                url: url,
                method: "POST",
                data: { _token: "{{ csrf_token() }}", calendar_type: calendar_type },
                success: function (data) {
                try {
                    const calendar = new window.FullCalendar.Calendar(calEl, {
                    headerToolbar: {
                        left: "prev,next today",
                        center: "title",
                        right: "timeGridDay,timeGridWeek,dayGridMonth",
                    },
                    buttonText: {
                        timeGridDay: "{{__('Day')}}",
                        timeGridWeek: "{{__('Week')}}",
                        dayGridMonth: "{{__('Month')}}",
                    },
                    slotLabelFormat: {
                        hour: "2-digit",
                        minute: "2-digit",
                        hour12: false,
                    },
                    themeSystem: "bootstrap",
                    allDaySlot: false,
                    navLinks: true,
                    droppable: true,
                    selectable: true,
                    selectMirror: true,
                    editable: true,
                    dayMaxEvents: true,
                    handleWindowResize: true,
                    height: "auto",
                    timeFormat: "H(:mm)",
                    events: data,
                    });
                    calendar.render();
                } catch (_) {
                    schedulePointerupError("calendar_unavailable");
                }
                },
            },
            "calendar_unavailable"
            );
        };
        const fillSelect = (wrapSel, selId, labelHtml) => {
            const wrap = $(wrapSel);
            let sel = $("#" + selId);
            if (!wrap.length) return sel;
            if (!sel.length) {
            wrap.html("");
            wrap.append(labelHtml);
            sel = $("#" + selId);
            } else {
            sel.empty();
            }
            return sel;
        };
        const initDepartment = () => {
            const bId = $("#branch_id").val?.();
            if (bId != null) {
            getDepartment(bId);
            }
            $(document).on("change", "select[name=branch_id]", function () {
            const val = $(this).val?.();
            getDepartment(val);
            });
        };
        const getDepartment = bid => {
            const route = "{{route(ViewsConstans::MT.'.getdepartment')}}";
            if (!route || route === "#") {
            schedulePointerupError("department_unavailable");
            return;
            }
            safeAjax(
            {
                url: route,
                type: "POST",
                data: { branch_id: bid, _token: "{{ csrf_token() }}" },
                success: function (data) {
                try {
                    const sel = fillSelect(
                    "#department_div",
                    "department_id",
                    '<select class="form-control" id="department_id" name="department_id[]" multiple></select>'
                    );
                    if (!sel || !sel.length) return;
                    sel.append('<option value="">{{__("Select Department")}}</option>');
                    sel.append('<option value="0">{{__("All Department")}}</option>');
                    $.each(data || {}, function (k, v) {
                    sel.append('<option value="' + k + '">' + v + "</option>");
                    });
                    if (!sel.attr(dataChoicesBound)) {
                    new Choices("#department_id", {
                        removeItemButton: true,
                    });
                    sel.attr(dataChoicesBound, "true");
                    }
                } catch (_) {
                    schedulePointerupError("department_unavailable");
                }
                },
            },
            "department_unavailable"
            );
        };
        const bindDeptChange = () => {
            $(document).on("change", "#department_id", function () {
            const did = $(this).val?.();
            getEmployee(did);
            });
        };
        const getEmployee = did => {
            const route = '{{route(ViewsConstans::MT.".getemployee")}}';
            if (!route || route === "#") {
            schedulePointerupError("employee_unavailable");
            return;
            }
            safeAjax(
            {
                url: route,
                type: "POST",
                data: { department_id: did, _token: "{{ csrf_token() }}" },
                success: function (data) {
                try {
                    const sel = fillSelect(
                    "#employee_div",
                    "employee_id",
                    '<select class="form-control" id="employee_id" name="employee_id[]" multiple></select>'
                    );
                    if (!sel || !sel.length) return;
                    sel.append('<option value="">{{__("Select Employee")}}</option>');
                    sel.append('<option value="0">{{__("All Employee")}}</option>');
                    $.each(data || {}, function (k, v) {
                    sel.append('<option value="' + k + '">' + v + "</option>");
                    });
                    if (!sel.attr(dataChoicesBound)) {
                    new Choices("#employee_id", {
                        removeItemButton: true,
                    });
                    sel.attr(dataChoicesBound, "true");
                    }
                } catch (_) {
                    schedulePointerupError("employee_unavailable");
                }
                },
            },
            "employee_unavailable"
            );
        };
        const start = () => {
            if (!$ || !$.ajax) {
            try {
                if (
                    window.location.hostname === "localhost" ||
                    window.location.hostname === "127.0.0.1"
                ) console.error("jQuery unavailable");
            } catch (_) {}
            schedulePointerupError("request_failed");
            return;
            }
            initCalendar();
            initDepartment();
            bindDeptChange();
        };
        if (document.readyState === "loading") {
            document.addEventListener("DOMContentLoaded", start, { once: true });
        } else {
            start();
        }
        })();
    </script>
@endpush
