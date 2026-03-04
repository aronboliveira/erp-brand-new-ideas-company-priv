@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        StacksConstants,
        ViewsConstants as VW,
        ViewClassNamesConstants as VC,
        YieldingConstants,
    };
    use App\Models\Utility;
    use Illuminate\Support\Facades\{Auth, Route};
    $settings = Utility::settings();
    $user = Auth::user();
    $lang = Utility::fetchUserLang(user:$user);
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@section(YieldingConstants::ADM_PG_TTL)
    {{__('Dashboard')}}
@endsection
@push(StacksConstants::ADM_SCR_PG)
    <script async>
        (() => {
        const errFb = '# ERROR';
        const dataClientLocalized = 'data-client-localized';
        const dataGuardMsg = 'data-guard-msg';
        const langSessionKey = 'erp-np-lang';
        
        const getLocalizedMessage = (msgKey, el) => {
            let msg = errFb;
            if (
            el.getAttribute('data-sv-localized') === 'true' ||
            el.getAttribute(dataClientLocalized) === 'true'
            ) {
            msg = el.getAttribute(dataGuardMsg) ?? errFb;
            } else {
            let lang = (
                window.sessionStorage.getItem(langSessionKey) ??
                document.documentElement.lang ??
                'en'
            )
                .toLowerCase()
                .replace(/_/g, '-');
            lang = lang === 'pt-br' ? lang : lang.slice(0, 2);
            msg =
                window.translations?.[lang]?.[msgKey] ??
                el.getAttribute(dataGuardMsg) ??
                window.translations?.['en']?.[msgKey] ??
                errFb;
            if (msg !== errFb) {
                el.setAttribute(dataGuardMsg, msg);
                el.setAttribute(dataClientLocalized, 'true');
            }
            }
            return msg;
        };
        
        const showError = message => {
            try {
            let container = document.querySelector('#bootstrap-toast-container');
            if (!container) {
                const hasBs =
                Array.from(document.querySelectorAll('link[rel="stylesheet"]'))
                    .some(l => /bootstrap/i.test(l.href)) &&
                window.bootstrap?.Toast;
                if (hasBs) {
                container = document.createElement('div');
                container.id = 'bootstrap-toast-container';
                container.setAttribute('aria-live', 'polite');
                container.setAttribute('aria-atomic', 'true');
                document.body.appendChild(container);
                }
            }
            if (container && window.bootstrap.Toast) {
                let toast = container.querySelector('.toast');
                if (!toast) {
                toast = document.createElement('div');
                toast.className = 'toast';
                toast.setAttribute('role', 'alert');
                toast.setAttribute('aria-live', 'assertive');
                toast.setAttribute('aria-atomic', 'true');
                const body = document.createElement('div');
                body.className = 'toast-body';
                toast.appendChild(body);
                container.appendChild(toast);
                if (toast.getAttribute('data-click-listener') !== 'true') {
                    toast.addEventListener('click', () =>
                    body.textContent = message
                    );
                    toast.setAttribute('data-click-listener', 'true');
                }
                }
                toast.querySelector('.toast-body').textContent = message;
                new bootstrap.Toast(toast).show();
            } else {
                alert(message);
            }
            } catch {
            alert(message);
            }
        };
        
        let errorMessage = '';
        const onErrorPointerUp = () => {
            if (errorMessage) {
            showError(errorMessage);
            errorMessage = '';
            }
        };
        document.addEventListener('pointerup', onErrorPointerUp);
        new MutationObserver((mutations, obs) => {
            for (const m of mutations) {
            for (const n of m.removedNodes) {
                if (n === document.documentElement) {
                document.removeEventListener('pointerup', onErrorPointerUp);
                obs.disconnect();
                }
            }
            }
        }).observe(document.body, { childList: true, subtree: true });
        
        document.addEventListener('DOMContentLoaded', () => {
            try {
            getData();
            const sel = document.getElementById('calendar_type');
            if (sel && sel.getAttribute('data-listener-attached') !== 'true') {
                sel.setAttribute('data-listener-attached', 'true');
                sel.addEventListener('change', getData);
                new MutationObserver((ms, o) => {
                for (const m of ms) {
                    for (const n of m.removedNodes) {
                    if (n === sel) {
                        sel.removeEventListener('change', getData);
                        o.disconnect();
                    }
                    }
                }
                }).observe(document.body, { childList: true, subtree: true });
            }
            } catch {
            errorMessage = getLocalizedMessage('calendar_init_failed', document.body);
            }
        });
        
        function getData() {
            try {
            const calEl = document.getElementById('calendar');
            if (!calEl) throw new Error();
            const type = document.getElementById('calendar_type')?.value;
            calEl.classList.remove('local_calendar', 'google_calendar');
            calEl.classList.add(type ?? 'local_calendar');
            $.ajax({
                url:
                (document.getElementById('event_dashboard')?.value ?? '') +
                '/event/get_event_data',
                type: 'POST',
                dataType: 'json',
                data: {
                _token:
                    document
                    .querySelector('meta[name="csrf-token"]')
                    ?.getAttribute('content') ?? '',
                calendar_type: type
                }
            })
                .done(data => initCalendar(calEl, data))
                .fail(() => {
                errorMessage = getLocalizedMessage('calendar_fetch_failed', calEl);
                });
            } catch {
            errorMessage = getLocalizedMessage('calendar_fetch_failed', document.body);
            }
        }
        
        function initCalendar(el, events) {
            try {
            el.innerHTML = '';
            const calendar = new FullCalendar.Calendar(el, {
                headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'timeGridDay,timeGridWeek,dayGridMonth'
                },
                buttonText: {
                timeGridDay: "{{__('Day')}}",
                timeGridWeek: "{{__('Week')}}",
                dayGridMonth: "{{__('Month')}}"
                },
                slotLabelFormat: {
                hour: '2-digit',
                minute: '2-digit',
                hour12: false
                },
                themeSystem: 'bootstrap',
                navLinks: true,
                droppable: true,
                selectable: true,
                selectMirror: true,
                editable: true,
                dayMaxEvents: true,
                handleWindowResize: true,
                height: 'auto',
                timeFormat: 'H(:mm)',
                events,
                locale: '{{ basename(App::getLocale()) }}',
                dayClick: info => {
                try {
                    const t = moment(info).toISOString();
                    $("#new-event").modal("show");
                    $(".new-event--title").val("");
                    $(".new-event--start").val(t);
                    $(".new-event--end").val(t);
                } catch {
                    errorMessage = getLocalizedMessage(
                    'calendar_date_click_failed',
                    el
                    );
                }
                },
                datesSet: arg => {
                try {
                    document
                    .querySelector('.fullcalendar-title')
                    ?.textContent = arg.view.title;
                } catch {
                    /* silent */
                }
                },
                eventClick: info => {
                try {
                    const url = info.event.url;
                    if (url) {
                    $("#commonModal .modal-title").html(info.event.title);
                    $("#commonModal .modal-dialog").addClass('modal-md');
                    $("#commonModal").modal('show');
                    $.get(url, data =>
                        $('#commonModal .modal-body').html(data)
                    ).fail(() => {
                        errorMessage = getLocalizedMessage(
                        'calendar_event_click_failed',
                        el
                        );
                    });
                    info.jsEvent.preventDefault();
                    }
                } catch {
                    errorMessage = getLocalizedMessage(
                    'calendar_event_click_failed',
                    el
                    );
                }
                }
            });
            calendar.render();
            } catch {
            errorMessage = getLocalizedMessage('calendar_render_failed', el);
            }
        }
        })();
    </script>
@endpush
@section(YieldingConstants::ADM_BDC)
    <li class="breadcrumb-item">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
        {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="breadcrumb-item">{{__('HRM')}}</li>
@endsection
@section('content')
    @if( $user?->type != 'client' && $user?->type != 'company')
        <div class="row">
            <div class="col-sm-12">
                <div class="row">
                    <div class="col-xxl-6">
                        <div class="card">
                            <div class="card-header">
                                <h4>{{__('Mark Attandance')}}</h4>
                            </div>
                            @php
                                $canIn  = empty($employeeAttendance) || $employeeAttendance->clock_out != '00:00:00';
                                $canOut = !empty($employeeAttendance) && $employeeAttendance->clock_out == '00:00:00';
                            @endphp
                            <div class="{{ VC::CD }}-body dash-card-body">
                                <p class="{{ VC::TXT_MT }} pb-0-5">{{ __('My Office Time: '.$officeTime['startTime'].' to '.$officeTime['endTime']) }}</p>
                                <center>
                                    @php
                                        $inUrl = url('employee-attendances/attendance');
                                        $inMsg = Utility::fetchLinkMessage(
                                            $lang,
                                            VW::EMP_ATD,
                                            'employee_attendance_in_route_unavailable'
                                        ) ?? 'Clock in route is unavailable. Please contact technical support or your domain administrator.';
                                        $outUrl = Route::has(VW::EMP_ATD.'.update')
                                            ? route(VW::EMP_ATD.'.update', $employeeAttendance->id)
                                            : '#';
                                        $outMsg = Utility::fetchLinkMessage(
                                            $lang,
                                            VW::EMP_ATD,
                                            'employee_attendance_out_route_unavailable'
                                        ) ?? 'Clock out route is unavailable. Please contact technical support or your domain administrator.';
                                    @endphp
                                    <div class="row">
                                        <div class="col-md-6">
                                            {!! Collective\Html\FormFacade::open(['url' => $inUrl, 'method' => 'post']) !!}
                                                @php
                                                    $inClass = VC::BT . ' btn-success' . ($canIn ? '' : ' disabled');
                                                @endphp
                                                <button
                                                    type="submit"
                                                    value="0"
                                                    name="in"
                                                    id="clock_in"
                                                    class="{{ $inClass }}"
                                                    data-url="{{ $inUrl }}"
                                                    data-sv-localized="true"
                                                    data-guard-msg="{{ $inMsg }}"
                                                    @if(!$canIn) disabled @endif
                                                >
                                                    {{ __('CLOCK IN') }}
                                                </button>
                                            {!! Collective\Html\FormFacade::close() !!}
                                        </div>
                                        <div class="col-md-6">
                                            {!! Collective\Html\FormFacade::model($employeeAttendance, [
                                                'route'  => [VW::EMP_ATD.'.update', $employeeAttendance->id],
                                                'method' => 'PUT'
                                            ]) !!}
                                                @php
                                                    $outClass = VC::BT . ' btn-danger' . ($canOut ? '' : ' disabled');
                                                @endphp
                                                <button
                                                    type="submit"
                                                    value="1"
                                                    name="out"
                                                    id="clock_out"
                                                    class="{{ $outClass }}"
                                                    data-url="{{ $outUrl }}"
                                                    data-sv-localized="true"
                                                    data-guard-msg="{{ $outMsg }}"
                                                    @if(!$canOut) disabled @endif
                                                >
                                                    {{ __('CLOCK OUT') }}
                                                </button>
                                            {!! Collective\Html\FormFacade::close() !!}
                                        </div>
                                    </div>
                                    @push(StacksConstants::ADM_SCR_PG)
                                        <script defer>
                                            (() => {
                                                const ids = ['clock_in', 'clock_out'];
                                                const flagAttr = 'data-listener-active';
                                                ids.forEach(id => {
                                                    const el = document.getElementById(id);
                                                    if (!el || el.getAttribute(flagAttr) === 'true') return;
                                                    el.setAttribute(flagAttr, 'true');
                                                    el.addEventListener('click', event => {
                                                        try {
                                                            const url = el.getAttribute('data-url');
                                                            const href = el.tagName === 'BUTTON' ? el.getAttribute('formaction') || url : el.href;
                                                            if ((!url || url === '#') && (!href || href === '#')) {
                                                                event.preventDefault();
                                                                const msg = el.getAttribute('data-guard-msg') ?? '# ERROR';
                                                                const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                                                                let container = document.getElementById('toast-container');
                                                                if (!container) {
                                                                    container = document.createElement('div');
                                                                    container.id = 'toast-container';
                                                                    document.body.appendChild(container);
                                                                }
                                                                if (bootstrapLink && window.bootstrap) {
                                                                    const toastEl = document.createElement('div');
                                                                    toastEl.className = 'toast';
                                                                    toastEl.setAttribute('role', 'alert');
                                                                    toastEl.setAttribute('aria-live', 'assertive');
                                                                    toastEl.setAttribute('aria-atomic', 'true');
                                                                    const body = document.createElement('div');
                                                                    body.className = 'toast-body';
                                                                    body.textContent = msg;
                                                                    toastEl.appendChild(body);
                                                                    container.appendChild(toastEl);
                                                                    bootstrap.Toast.getOrCreateInstance(toastEl).show();
                                                                } else {
                                                                    alert(msg);
                                                                }
                                                                btn.setAttribute('data-failed-route', 'true');
                                                            }
                                                        } catch {}
                                                    });
                                                    const observer = new MutationObserver(() => {
                                                        if (!document.getElementById(id)) observer.disconnect();
                                                    });
                                                    observer.observe(document.body, { childList: true, subtree: true });
                                                });
                                            })();
                                        </script>
                                    @endpush                                
                                </center>
                            </div>
                        </div>
                        <div class="card">
                            <div class="card-header">
                                <div class="row">
                                    <div class="col-lg-6">
                                        <h5>{{ __('Event') }}</h5>
                                    </div>
                                    <div class="col-lg-6">
                                        @if (!empty($settings) && isset($settings['google_calendar_enable']) && $settings['google_calendar_enable'] == 'on')
                                        <select class="form-control" name="calendar_type" id="calendar_type" style="float: right;width: 150px;">
                                            <option value="google_calendar">{{__('Google calendar')}}</option>
                                            <option value="local_calendar" selected="true">{{__('Local calendar')}}</option>
                                        </select>
                                        @endif
                                        <input type="hidden" id="event_dashboard" value="{{url('/')}}">
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div id='calendar' class='calendar e-height'></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xxl-6">
                        <div class="card list_card">
                            <div class="card-header">
                                <h4>{{__('Announcement List')}}</h4>
                            </div>
                            <div class="card-body dash-card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped mb-0">
                                        <thead>
                                        <tr>
                                            <th>{{__('Title')}}</th>
                                            <th>{{__('Start Date')}}</th>
                                            <th>{{__('End Date')}}</th>
                                            <th>{{__('description')}}</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @forelse($announcements as $announcement)
                                            <tr>
                                                <td>{{ $announcement->title }}</td>
                                                <td>{{ $user?->dateFormat($announcement->start_date)  }}</td>
                                                <td>{{ $user?->dateFormat($announcement->end_date) }}</td>
                                                <td>{{ $announcement->description }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4">
                                                    <div class="text-center">
                                                        <h6>{{__('There is no Announcement List')}}</h6>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="card list_card">
                            <div class="card-header">
                                <h4>{{__('Meeting List')}}</h4>
                            </div>
                            <div class="card-body dash-card-body">
                                @if(count($meetings) > 0)
                                    <div class="table-responsive">
                                        <table class="table align-items-center">
                                            <thead>
                                            <tr>
                                                <th>{{__('Meeting title')}}</th>
                                                <th>{{__('Meeting Date')}}</th>
                                                <th>{{__('Meeting Time')}}</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @forelse($meetings as $meeting)
                                                <tr>
                                                    <td>{{ $meeting->title }}</td>
                                                    <td>{{ $user?->dateFormat($meeting->date) }}</td>
                                                    <td>{{ $user?->timeFormat($meeting->time) }}</td>
                                                </tr>
                                            @empty
                                                <tr><td colspan="3" class="text-center">{{ __('No meetings found') }}</td></tr>
                                            @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <div class="p-2">
                                        {{__('No meeting scheduled yet.')}}
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="row">
            <div class="col-xxl-12">
                <div class="card">
                    <div class="card-header">
                        <h5>{{__("Today's Not Clock In")}}</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="row g-3 flex-nowrap team-lists horizontal-scroll-cards">
                                    @foreach($notClockIns as $notClockIn)
                                        <div class="col-auto">
                                            <img src="{{(!empty($notClockIn->user))? $notClockIn->user->profile : asset(Storage::url('uploads/avatar/avatar.png'))}}" alt="">
                                            <p class="mt-2">{{ $notClockIn->name }}</p>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-12">
                <div class="row">
                    <div class="col-md-9">
                        <div class="card">
                            <div class="card-header">
                                <div class="row">
                                    <div class="col-lg-6">
                                        <h5>{{ __('Event') }}</h5>
                                    </div>
                                    <div class="col-lg-6">
                                        @if(!empty($settings) && isset($settings['google_calendar_enable']) && $settings['google_calendar_enable'] == 'on')
                                            <select class="form-control" name="calendar_type" id="calendar_type" style="float: right;width: 150px;">
                                                <option value="google_calendar">{{__('Google calendar')}}</option>
                                                <option value="local_calendar" selected="true">{{__('Local calendar')}}</option>
                                            </select>
                                        @endif
                                        <input type="hidden" id="event_dashboard" value="{{url('/')}}">
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div id='calendar' class='calendar'></div>
                            </div>
                        </div>
                    </div>
                    @php
                        $sections=[
                            ['title'=>__('Staff'),'metrics'=>[
                                ['bg'=>'bg-primary','icon'=>VC::TI_USRS,'label'=>__('Total Staff'),'value'=>$countUser+$countClient,'textClass'=>'text-success'],
                                ['bg'=>'bg-info','icon'=>'ti ti-user','label'=>__('Total Employee'),'value'=>$countUser,'textClass'=>'text-primary'],
                                ['bg'=>'bg-danger','icon'=>'ti ti-user','label'=>__('Total Client'),'value'=>$countClient,'textClass'=>'text-danger']
                            ]],
                            ['title'=>__('Job'),'metrics'=>[
                                ['bg'=>'bg-primary','icon'=>'ti ti-award','label'=>__('Total Jobs'),'value'=>$activeJob+$inActiveJob,'textClass'=>'text-success'],
                                ['bg'=>'bg-info','icon'=>'ti ti-check','label'=>__('Active Jobs'),'value'=>$activeJob,'textClass'=>'text-primary'],
                                ['bg'=>'bg-danger','icon'=>'ti ti-x','label'=>__('Inactive Jobs'),'value'=>$inActiveJob,'textClass'=>'text-danger']
                            ]],
                            ['title'=>__('Training'),'metrics'=>[
                                ['bg'=>'bg-primary','icon'=>VC::TI_USRS,'label'=>__('Total Training'),'value'=>$onGoingTraining+$doneTraining,'textClass'=>'text-success'],
                                ['bg'=>'bg-info','icon'=>'ti ti-user','label'=>__('Trainer'),'value'=>$countTrainer,'textClass'=>'text-primary'],
                                ['bg'=>'bg-danger','icon'=>'ti ti-user-check','label'=>__('Active Training'),'value'=>$onGoingTraining,'textClass'=>'text-danger'],
                                ['bg'=>'bg-secondary','icon'=>'ti ti-user-minus','label'=>__('Done Training'),'value'=>$doneTraining,'textClass'=>'text-secondary']
                            ]]
                        ];
                    @endphp
                    <div class="col-md-3">
                        @foreach($sections as $section)
                            <div class="col-xxl-12">
                                <div class="{{ VC::CD }}">
                                    <div class="card-body">
                                        <h5>{{ $section['title'] }}</h5>
                                        <div class="{{ VC::RW }} {{ VC::MT4 }}">
                                            @foreach($section['metrics'] as $m)
                                                <div class="col-md-6 col-sm-6{{ $loop->index>0?' my-3 my-sm-0':'' }}">
                                                    <div class="{{ VC::DFL }} {{ VC::ALC }} {{ VC::MB3 }}">
                                                        <div class="theme-avatar {{ $m['bg'] }}">
                                                            <i class="{{ $m['icon'] }}"></i>
                                                        </div>
                                                        <div class="{{ VC::MS2 }}">
                                                            <p class="{{ VC::TXT_MT }} {{ VC::TXSM }} {{ VC::MB0 }}">{{ $m['label'] }}</p>
                                                            <h4 class="{{ VC::MB0 }} {{ $m['textClass'] }}">{{ $m['value'] }}</h4>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div class="col-lg-6">
                        <div class="{{ VC::CD }}">
                            <div class="card-header">
                                <h5>{{ __('Announcement List') }}</h5>
                            </div>
                            <div class="card-body" style="min-height: 295px;">
                                <div class="table-responsive">
                                    @if(count($announcements)>0)
                                        <table class="{{ VC::TB_AL }} {{ VC::MB0 }}">
                                            <thead>
                                                <tr>
                                                    <th>{{ __('Title') }}</th>
                                                    <th>{{ __('Start Date') }}</th>
                                                    <th>{{ __('End Date') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody class="list">
                                                @foreach($announcements as $announcement)
                                                    <tr>
                                                        <td>{{ $announcement->title }}</td>
                                                        <td>{{ $user?->dateFormat($announcement->start_date) }}</td>
                                                        <td>{{ $user?->dateFormat($announcement->end_date) }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    @else
                                        <div class="p-2">{{ __('No accouncement present yet.') }}</div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="{{ VC::CD }}">
                            <div class="card-header">
                                <h5>{{ __('Meeting schedule') }}</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    @if(count($meetings)>0)
                                        <table class="{{ VC::TB_AL }} {{ VC::MB0 }}">
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
                                                        <td>{{ $meeting->title }}</td>
                                                        <td>{{ $user?->dateFormat($meeting->date) }}</td>
                                                        <td>{{ $user?->timeFormat($meeting->time) }}</td>
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
                </div>
            </div>
        </div>
    @endif
@endsection


