@php
    try {
$user = Auth::user();
        $lang = Utility::fetchUserLang(user: $user);
    } catch (\Throwable $e) {
        \Log::error('reports/dashboard — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    }
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@section(YieldingConstants::ADM_PG_TTL)
    {{__('Dashboard')}}
@endsection

@push(StacksConstants::ADM_CSS)
    <link rel="stylesheet" href="{{ asset('assets/libs/fullcalendar/dist/fullcalendar.min.css') }}">
@endpush

@section('content')
    @if (session('status'))
        <div class="{{ VC::ALT_SUC }}" role="alert">
            {{ session('status') }}
        </div>
    @endif
    @if($user?->{UsersConstants::COL_TP} != PermissionsConstants::CL && $user?->{UsersConstants::COL_TP} != PermissionsConstants::CPN)
        <div class="{{ VC::RW }} mt-5">
            <div class="{{ VC::CL6 }}">
                <div class="{{ VC::CD }}">
                    <div class="{{ VC::CD_HD }}">
                        <h4>{{ __('Event View') }}</h4>
                    </div>
                    <div class="{{ VC::CD_BD }} dash-card-body">
                        <div class="page-title">
                            <div class="{{ VC::RW }} {{ VC::JCB }} {{ VC::ALC }} full-calendar">
                                <div class="col {{ VC::DFL_AIC }}">
                                    <div class="btn-group" role="group" aria-label="Calendar navigation">
                                        <a href="#" class="fullcalendar-btn-prev {{ VC::BT_SM }} btn-neutral" title="{{ __('Previous') }}">
                                            <i class="ti ti-angle-left"></i>
                                        </a>
                                        <a href="#" class="fullcalendar-btn-next {{ VC::BT_SM }} btn-neutral" title="{{ __('Next') }}">
                                            <i class="ti ti-angle-right"></i>
                                        </a>
                                    </div>
                                    <h5 class="fullcalendar-title h4 d-inline-block font-weight-400 {{ VC::MB0 }}"></h5>
                                </div>
                                <div class="{{ VC::CL6 }} {{ VC::MT3 }} mt-lg-0 text-lg-right">
                                    <div class="btn-group" role="group" aria-label="Calendar view">
                                        <a href="#" class="{{ VC::BT_SM }} btn-neutral" data-calendar-view="month">{{ __('Month') }}</a>
                                        <a href="#" class="{{ VC::BT_SM }} btn-neutral" data-calendar-view="basicWeek">{{ __('Week') }}</a>
                                        <a href="#" class="{{ VC::BT_SM }} btn-neutral" data-calendar-view="basicDay">{{ __('Day') }}</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="{{ VC::RW }}">
                            <div class="col">
                                <div class="overflow-hidden widget-calendar">
                                    <div class="calendar e-height" data-toggle="event_calendar" id="event_calendar"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="{{ VC::CL6 }}">
                <div class="{{ VC::CD }}">
                    <div class="{{ VC::CD_HD }}">
                        <h4>{{ __('Mark Attendance') }}</h4>
                    </div>
                    <div class="{{ VC::CD_BD }} dash-card-body">
                        @php
                            $startTime = data_get($officeTime ?? [], 'startTime');
                            $endTime   = data_get($officeTime ?? [], 'endTime');
@endphp
                        <p class="{{ VC::TXT_MT }} pb-0-5">
                            {{ __('My Office Time: :start to :end', ['start' => $startTime ?: __('N/A'), 'end' => $endTime ?: __('N/A')]) }}
                        </p>
                        <center>
                            <div class="{{ VC::RW }}">
                                <div class="{{ VC::CM6 }} float-right border-right">
                                    @php
                                        try {
                                            $clockInBase          = VW::EMP_ATD.'.attendance';
                                            $clockInKebab         = Str::kebab($clockInBase);
                                            $clockInResolved      = Route::has($clockInBase) ? $clockInBase : (Route::has($clockInKebab) ? $clockInKebab : null);
                                            $clockInUrl           = $clockInResolved ? route($clockInResolved) : '#';
                                            $clockInFormId        = 'clock-in-form';
                                            $clockInGuardMsg      = Utility::fetchLinkMessage($lang, VW::EMP_ATD, 'clock_in_employee_attendance_unavailable') ?? 'Clock in route is unavailable. Please contact technical support or your domain administrator.';
                                        } catch (\Throwable $e) {
                                            \Log::error('reports/dashboard — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                        }
@endphp
                                    {{ Form::open([
                                        'method'            => 'POST',
                                        'url'               => $clockInUrl,
                                        'id'                => $clockInFormId,
                                        'data-url'          => $clockInUrl,
                                        'data-guard-msg'    => $clockInGuardMsg,
                                        'data-sv-localized' => 'true',
                                    ]) }}
                                        @if(empty($employeeAttendance) || $employeeAttendance->clock_out != '00:00:00')
                                            <button type="submit" value="0" name="in" id="clock_in" class="btn-create badge-success">{{ __('CLOCK IN') }}</button>
                                        @else
                                            <button type="submit" value="0" name="in" id="clock_in" class="btn-create badge-success disabled" disabled>{{ __('CLOCK IN') }}</button>
                                        @endif
                                    {{ Form::close() }}
                                    @push(StacksConstants::ADM_SCR_PG)
                                        <script src="{{ asset('assets/js/routes/employeeAttendances/clockIn.js') }}" defer></script>
                                    @endpush
                                </div>
                                <div class="{{ VC::CM6 }} float-left">
                                    @if(!empty($employeeAttendance) && $employeeAttendance->clock_out == '00:00:00')
                                        @php
                                            try {
                                                $empAttId            = data_get($employeeAttendance, 'id');
                                                $clockOutBase        = VW::EMP_ATD.'.update';
                                                $clockOutKebab       = Str::kebab($clockOutBase);
                                                $clockOutResolved    = Route::has($clockOutBase) ? $clockOutBase : (Route::has($clockOutKebab) ? $clockOutKebab : null);
                                                $clockOutParams      = $empAttId ? [$empAttId] : ['#'];
                                                $clockOutUrl         = ($clockOutResolved && $empAttId) ? route($clockOutResolved, $clockOutParams) : '#';
                                                $clockOutFormId      = 'clock-out-form';
                                                $clockOutGuardMsg    = Utility::fetchLinkMessage($lang, VW::EMP_ATD, 'clock_out_employee_attendance_unavailable') ?? 'Clock out route is unavailable. Please contact technical support or your domain administrator.';
                                            } catch (\Throwable $e) {
                                                \Log::error('reports/dashboard — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                            }
@endphp
                                        {{ Form::model($employeeAttendance, [
                                            'method'            => 'PUT',
                                            'url'               => $clockOutUrl,
                                            'id'                => $clockOutFormId,
                                            'data-url'          => $clockOutUrl,
                                            'data-guard-msg'    => $clockOutGuardMsg,
                                            'data-sv-localized' => 'true',
                                        ]) }}
                                            <button type="submit" value="1" name="out" id="clock_out" class="btn-create badge-danger">{{ __('CLOCK OUT') }}</button>
                                        {{ Form::close() }}
                                        @push(StacksConstants::ADM_SCR_PG)
                                            <script src="{{ asset('assets/js/routes/employeeAttendances/clockOut.js') }}" defer></script>
                                        @endpush
                                    @else
                                        <button type="button" value="1" name="out" id="clock_out" class="btn-create badge-danger disabled" disabled>{{ __('CLOCK OUT') }}</button>
                                    @endif
                                </div>
                            </div>
                        </center>
                    </div>
                </div>
            </div>
        </div>
        <div class="{{ VC::RW }}">
            <div class="{{ VC::CL6 }}">
                <div class="{{ VC::CD }}">
                    <div class="{{ VC::CD_HD }}">
                        <h4>{{ __('Announcement List') }}</h4>
                    </div>
                    <div class="{{ VC::CD_BD }} dash-card-body">
                        <div class="{{ VC::TB_RSP }}">
                            <table class="{{ VC::TB }} table-striped {{ VC::MB0 }}">
                                <thead>
                                    <tr>
                                        <th>{{ __('Title') }}</th>
                                        <th>{{ __('Start Date') }}</th>
                                        <th>{{ __('End Date') }}</th>
                                        <th>{{ __('Description') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($announcements as $announcement)
                                        <tr>
                                            <td>{{ $announcement->title ?: __('-') }}</td>
                                            <td>{{ !empty($announcement->start_date) ? ($user?->dateFormat($announcement->start_date) ?: $announcement->start_date) : __('-') }}</td>
                                            <td>{{ !empty($announcement->end_date) ? ($user?->dateFormat($announcement->end_date) ?: $announcement->end_date) : __('-') }}</td>
                                            <td>{{ $announcement->description ?: __('-') }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="{{ VC::TXCT }}">{{ __('No announcements found') }}</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="{{ VC::CL6 }}">
                <div class="{{ VC::CD }}">
                    <div class="{{ VC::CD_HD }}">
                        <h4>{{ __('Meeting List') }}</h4>
                    </div>
                    <div class="{{ VC::CD_BD }} dash-card-body">
                        @if(count($meetings ?? []) > 0)
                            <div class="{{ VC::TB_RSP }}">
                                <table class="{{ VC::TB }} table-striped {{ VC::MB0 }}">
                                    <thead>
                                        <tr>
                                            <th>{{ __('Meeting title') }}</th>
                                            <th>{{ __('Meeting Date') }}</th>
                                            <th>{{ __('Meeting Time') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($meetings as $meeting)
                                            <tr>
                                                <td>{{ $meeting->title ?: __('-') }}</td>
                                                <td>{{ !empty($meeting->date) ? ($user?->dateFormat($meeting->date) ?: $meeting->date) : __('-') }}</td>
                                                <td>{{ !empty($meeting->time) ? ($user?->timeFormat($meeting->time) ?: $meeting->time) : __('-') }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="p-2">{{ __('No meetings scheduled yet') }}</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="{{ VW::RW }}">
            <div class="{{ VC::CXL4 }} {{ VW::CL4 }} {{ VW::CM6 }} {{ VW::CS12 }}">
                <div class="{{ VW::CD }} card-box">
                    <div class="left-card">
                        <div class="icon-box"><i class="{{ VW::TI_USRS }}"></i></div>
                        <h4>{{ __('Total Staff') }}</h4>
                    </div>
                    <div class="number-icon">{{ (int)($countUser ?? 0) + (int)($countClient ?? 0) }}</div>
                    <div class="user-text">
                        <h5>{{ __('Employee ') }}: {{ (int)($countUser ?? 0) }}</h5>
                        <h5>{{ __('Client ') }}: {{ (int)($countClient ?? 0) }}</h5>
                    </div>
                </div>
                <img src="{{ asset('assets/img/dot-icon.png') }}" alt="{{ __('Decorative dots') }}" class="dotted-icon"/>
            </div>

            <div class="{{ VC::CXL4 }} {{ VW::CL4 }} {{ VW::CM6 }} {{ VW::CS12 }}">
                <div class="{{ VW::CD }} card-box">
                    <div class="left-card">
                        <div class="icon-box yellow-bg"><i class="ti ti-graduation-cap"></i></div>
                        <h4>{{ __('Total Training') }}</h4>
                    </div>
                    <div class="number-icon">{{ (int)($onGoingTraining ?? 0) + (int)($doneTraining ?? 0) }}</div>
                    <div class="user-text">
                        <h5>{{ __('Trainer ') }}: {{ (int)($countTrainer ?? 0) }}</h5>
                        <h5>{{ __('Active Training ') }}: {{ (int)($onGoingTraining ?? 0) }}</h5>
                        <h5>{{ __('Done Training ') }}: {{ (int)($doneTraining ?? 0) }}</h5>
                    </div>
                    <img src="{{ asset('assets/img/dot-icon.png') }}" alt="{{ __('Decorative dots') }}" class="dotted-icon"/>
                </div>
            </div>

            @if($user?->{UsersConstants::COL_TP} == 'company')
                <div class="{{ VC::CXL4 }} {{ VW::CL4 }} {{ VW::CM6 }} {{ VW::CS12 }}">
                    <div class="{{ VW::CD }} card-box">
                        <div class="left-card">
                            <div class="icon-box green-bg"><i class="ti ti-user-md"></i></div>
                            <h4>{{ __('Total Jobs') }}</h4>
                        </div>
                        <div class="number-icon">{{ (int)($activeJob ?? 0) + (int)($inActiveJOb ?? 0) }}</div>
                        <div class="user-text">
                            <h5>{{ __('Active Job ') }}: {{ (int)($activeJob ?? 0) }}</h5>
                            <h5>{{ __('Inactive Job ') }}: {{ (int)($inActiveJOb ?? 0) }}</h5>
                        </div>
                    </div>
                    <img src="{{ asset('assets/img/dot-icon.png') }}" alt="{{ __('Decorative dots') }}" class="dotted-icon"/>
                </div>
            @endif
        </div>
        <div class="{{ VW::RW }}">
            <div class="{{ VC::CXL3 }} {{ VW::CL4 }} col-md-5">
                <h4 class="h4 font-weight-400">{{ __("Today's Not Clock In") }}</h4>
                <div class="{{ VW::CD_FL }} bg-none min-height-443">
                    <div class="{{ VC::TB_RSP }}">
                        <table class="{{ VW::TB_AL }}">
                            <thead>
                                <tr>
                                    <th>{{ __('Name') }}</th>
                                    <th>{{ __('Status') }}</th>
                                </tr>
                            </thead>
                            <tbody class="list">
                                @forelse($notClockIns ?? [] as $notClockIn)
                                    <tr>
                                        <td>{{ $notClockIn->name ?? __('-') }}</td>
                                        <td><span class="absent-btn">{{ __('Absent') }}</span></td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="2" class="{{ VC::TXCT }}">{{ __('No employees to display') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-xl-9 {{ VW::CL8 }} col-md-7">
                <div><h4 class="h4 font-weight-400 float-left">{{ __('Announcement List') }}</h4></div>
                <div class="{{ VW::CD_FL }} bg-none min-height-443">
                    <div class="{{ VC::TB_RSP }}">
                        @if(count($announcements ?? []) > 0)
                            <table class="{{ VW::TB_AL }}">
                                <thead>
                                    <tr>
                                        <th>{{ __('Title') }}</th>
                                        <th>{{ __('Start Date') }}</th>
                                        <th>{{ __('End Date') }}</th>
                                        <th>{{ __('Description') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="list">
                                    @foreach($announcements as $announcement)
                                        <tr>
                                            <td>{{ $announcement->title ?? __('-') }}</td>
                                            <td>{{ !empty($announcement->start_date) ? ($user?->dateFormat($announcement->start_date) ?: $announcement->start_date) : __('-') }}</td>
                                            <td>{{ !empty($announcement->end_date) ? ($user?->dateFormat($announcement->end_date) ?: $announcement->end_date) : __('-') }}</td>
                                            <td>{{ $announcement->description ?? __('-') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @else
                            <div class="p-2">{{ __('No announcement present yet.') }}</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        <div class="{{ VW::RW }} mt-5">
            <div class="{{ VW::CM6 }}">
                <h4 class="h4 font-weight-400 float-left">{{ __('Event View') }}</h4>
                <div class="{{ VW::CD_FL }} widget-calendar min-height-940">
                    <div class="{{ VC::CD_HD }}">
                        <div class="{{ VW::RW }}">
                            <div class="{{ VC::CXL2 }} {{ VW::CL3 }} {{ VC::CM2 }} col-sm-2">
                                <div class="btn-group" role="group" aria-label="Calendar navigation">
                                    <a href="#" class="fullcalendar-btn-prev {{ VW::BT_SM }} btn-neutral" title="{{ __('Previous') }}">
                                        <i class="ti ti-angle-left"></i>
                                    </a>
                                    <a href="#" class="fullcalendar-btn-next {{ VW::BT_SM }} btn-neutral" title="{{ __('Next') }}">
                                        <i class="ti ti-angle-right"></i>
                                    </a>
                                </div>
                            </div>
                            <div class="col-xl-5 {{ VW::CL4 }} col-md-5 {{ VC::CS6 }} {{ VC::TXCT }}">
                                <h5 class="fullcalendar-title h4 d-inline-block font-weight-600 {{ VC::MB0 }}">{{ __('Calendar') }}</h5>
                            </div>
                            <div class="col-xl-5 {{ VW::CL5 }} col-md-5 {{ VC::CS4 }} text-lg-right">
                                <div class="btn-group" role="group" aria-label="Calendar view">
                                    <a href="#" class="{{ VW::BT_SM }} btn-neutral" data-calendar-view="month">{{ __('Month') }}</a>
                                    <a href="#" class="{{ VW::BT_SM }} btn-neutral" data-calendar-view="basicWeek">{{ __('Week') }}</a>
                                    <a href="#" class="{{ VW::BT_SM }} btn-neutral" data-calendar-view="basicDay">{{ __('Day') }}</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="calendar" data-toggle="event_calendar"></div>
                </div>
            </div>

            <div class="{{ VW::CM6 }}">
                <div><h4 class="h4 font-weight-400 float-left">{{ __('Meeting schedule') }}</h4></div>
                <div class="{{ VW::CD_FL }} bg-none min-height-940">
                    <div class="{{ VC::TB_RSP }}">
                        @if(count($meetings ?? []) > 0)
                            <table class="{{ VW::TB_AL }}">
                                <thead>
                                    <tr>
                                        <th>{{ __('Title') }}</th>
                                        <th>{{ __('Date') }}</th>
                                        <th>{{ __('Time') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="list">
                                    @foreach($meetings as $meeting)
                                        <tr>
                                            <td>{{ $meeting->title ?? __('-') }}</td>
                                            <td>{{ !empty($meeting->date) ? ($user?->dateFormat($meeting->date) ?: $meeting->date) : __('-') }}</td>
                                            <td>{{ !empty($meeting->time) ? ($user?->timeFormat($meeting->time) ?: $meeting->time) : __('-') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @else
                            <div class="p-2">{{ __('No meeting scheduled yet.') }}</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection

@push('theme-script')
    <script src="{{ asset('assets/libs/fullcalendar/dist/fullcalendar.min.js') }}"></script>
@endpush
@push(StacksConstants::ADM_SCR_PG)
    <script async src="{{ asset('assets/routes/reports/dashboard/calendar.js') }}"></script>
    <script defer>
        (function () {
            const $ = window.jQuery;
            const qs = (s, r = document) => r.querySelector(s);
            const errFb = "# ERROR";
            const dataClientLocalized = "data-client-localized";
            const dataGuardMsg = "data-guard-msg";
            const dataSvLocalized = "data-sv-localized";
            const dataErrGuard = "data-error-guard";
            const dataListenerGuard = "data-cal-listener";
            if (!$) { try {
                if (
                    window.location.hostname === "localhost" ||
                    window.location.hostname === "127.0.0.1"
                ) console.error("jQuery unavailable");
             } catch (_) {} scheduleInteractiveError(getMsg(document.body, "plugin_unavailable")); return; }
            const ensureToastContainer = () => {
            const id = "np-toast-container";
            let c = qs("#" + id);
            if (c) { return c; }
            c = document.createElement("div");
            c.id = id;
            c.setAttribute("aria-live", "polite");
            c.setAttribute("aria-atomic", "true");
            c.style.position = "fixed";
            c.style.top = "1rem";
            c.style.right = "1rem";
            document.body.appendChild(c);
            return c;
            };
            const showErrorNow = (message) => {
            const hasBootstrap = (qs('link[rel="stylesheet"][href*="bootstrap"]') || qs('link[href*="bootstrap"]')) && window.bootstrap && window.bootstrap.Toast;
            if (hasBootstrap) {
                const container = ensureToastContainer();
                const tid = "np-toast";
                let t = qs("#" + tid, container);
                if (!t) {
                t = document.createElement("div");
                t.id = tid;
                t.className = "toast";
                t.setAttribute("role", "alert");
                t.setAttribute("aria-live", "assertive");
                t.setAttribute("aria-atomic", "true");
                t.innerHTML = '<div class="toast-header"><strong class="me-auto">{{ __('Notice') }}</strong><button type="button" class="{{ VC::BT_CL }}" data-bs-dismiss="toast" aria-label="{{ __('Close') }}"></button></div><div class="toast-body"></div>';
                container.appendChild(t);
                }
                const body = qs(".toast-body", t);
                if (body) { body.textContent = message ?? errFb; }
                try { new window.bootstrap.Toast(t, { autohide: true, delay: 4000 }).show(); } catch (_) { alert(message ?? errFb); }
            } else { alert(message ?? errFb); }
            };
            function scheduleInteractiveError(message) {
            const host = document.body;
            if (!host || host.getAttribute(dataErrGuard) === "true") { return; }
            host.setAttribute(dataErrGuard, "true");
            const once = () => { try { showErrorNow(message); } finally { host.removeAttribute(dataErrGuard); } };
            document.addEventListener("click", once, { once: true });
            const mo = new MutationObserver((m, o) => { if (!document.body.contains(host)) { document.removeEventListener("click", once); o.disconnect(); } });
            mo.observe(document.documentElement, { childList: true, subtree: true });
            }
            const getMsg = (el, key) => {
            let msg = errFb;
            if (el?.getAttribute(dataSvLocalized) === "true" || el?.getAttribute(dataClientLocalized) === "true") { msg = el.getAttribute(dataGuardMsg) || errFb; }
            else {
                let lang = (window.sessionStorage.getItem("erp-np-lang") || document.documentElement.lang || "en").toLowerCase().replace(/_/g, "-");
                lang = lang === "pt-br" ? lang : lang.slice(0, 2);
                const msgKey = key;
                msg = window.translations?.[lang]?.[msgKey] || el?.getAttribute(dataGuardMsg) || window.translations?.["en"]?.[msgKey] || errFb;
                if (el && msg !== errFb) { el.setAttribute(dataGuardMsg, msg); el.setAttribute(dataClientLocalized, "true"); }
            }
            return msg;
            };
            const routeOrFail = (url, el) => {
            if (!url || url === "#") { showErrorNow(getMsg(el || document.body, "endpoint_unavailable")); return null; }
            return url;
            };
            const bindWithObserver = (el, evt, handler, flag) => {
            if (!el || el.getAttribute(flag) === "true") { return; }
            el.setAttribute(flag, "true");
            $(el).on(evt, handler);
            const mo = new MutationObserver((m, o) => { if (!document.body.contains(el)) { $(el).off(evt, handler); o.disconnect(); } });
            mo.observe(document.body, { childList: true, subtree: true });
            };
            const initCalendar = () => {
            const $cal = $('[data-toggle="event_calendar"]');
            if (!$cal.length) { return; }
            if (typeof $.fn.fullCalendar !== "function" || typeof window.moment !== "function") { try {
                if (
                    window.location.hostname === "localhost" ||
                    window.location.hostname === "127.0.0.1"
                ) console.error("FullCalendar or Moment unavailable");
             } catch (_) {} scheduleInteractiveError(getMsg($cal.get(0), "plugin_unavailable")); return; }
            try {
                const opts = {
                header: { right: "", center: "", left: "" },
                buttonIcons: { prev: "calendar--prev", next: "calendar--next" },
                theme: false,
                selectable: true,
                selectHelper: true,
                editable: true,
                events: {!! json_encode($arrEvents) !!},
                eventStartEditable: false,
                locale: '{{basename(App::getLocale())}}',
                dayClick: function (date) {
                    const t = window.moment(date).toISOString();
                    const $modal = $("#new-event");
                    if (!$modal.length || typeof $modal.modal !== "function") { showErrorNow(getMsg(document.body, "modal_unavailable")); return; }
                    $modal.modal("show");
                    $(".new-event--title").val("");
                    $(".new-event--start").val(t);
                    $(".new-event--end").val(t);
                },
                eventResize: function (event) {
                    const eventObj = { start: event.start && event.start.format ? event.start.format() : "", end: event.end && event.end.format ? event.end.format() : "" };
                },
                viewRender: function (view) {
                    try {
                    const titleEl = document.querySelector(".fullcalendar-title");
                    if (titleEl) { titleEl.innerHTML = view.title || ""; }
                    } catch (_) {}
                },
                eventClick: function (ev, js, view) {
                    const title = ev && ev.title || "";
                    const url = ev && ev.url || "";
                    const ok = routeOrFail(url, $cal.get(0));
                    if (!ok) { return false; }
                    const $modal = $("#commonModal");
                    if (!$modal.length || typeof $modal.modal !== "function") { showErrorNow(getMsg(document.body, "modal_unavailable")); return false; }
                    $modal.find(".modal-title").html(title);
                    $modal.find(".modal-dialog").addClass("modal-md");
                    $modal.modal("show");
                    $.get(ok, {}, function (data) { $modal.find(".modal-body").html(data); });
                    return false;
                }
                };
                $cal.fullCalendar(opts);
            } catch (_) { scheduleInteractiveError(getMsg($cal.get(0), "calendar_unavailable")); }
            const changeViewHandler = function (evt) {
                evt.preventDefault();
                $('[data-calendar-view]').removeClass("active");
                $(this).addClass("active");
                const v = $(this).attr("data-calendar-view");
                $cal.fullCalendar("changeView", v);
            };
            const nextHandler = function (evt) { evt.preventDefault(); $cal.fullCalendar("next"); };
            const prevHandler = function (evt) { evt.preventDefault(); $cal.fullCalendar("prev"); };
            document.querySelectorAll("[data-calendar-view]").forEach(function (el) { bindWithObserver(el, "click", changeViewHandler, dataListenerGuard + "-view"); });
            document.querySelectorAll(".fullcalendar-btn-next").forEach(function (el) { bindWithObserver(el, "click", nextHandler, dataListenerGuard + "-next"); });
            document.querySelectorAll(".fullcalendar-btn-prev").forEach(function (el) { bindWithObserver(el, "click", prevHandler, dataListenerGuard + "-prev"); });
            };
            const init = () => { initCalendar(); };
            if (document.readyState === "loading") { document.addEventListener("DOMContentLoaded", init, { once: true }); }
            else { init(); }
        })();
    </script>
@endpush
