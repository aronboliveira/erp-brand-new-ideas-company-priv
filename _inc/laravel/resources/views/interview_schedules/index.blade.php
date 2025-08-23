@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        PermissionsConstants,
        StacksConstants,
        ViewClassNamesConstants,
        YieldingConstants,
    };
    use App\Models\Utility;
    use Illuminate\Support\Facades\{Auth, Route};
    $user = Auth::user();
    $lang = Utility::fetchUserLang(user:$user);
    $settings = Utility::settings();
@endphp
@extends(ExtendingLayoutsConstants::ADM)

@section(YieldingConstants::ADM_PG_TTL)
    {{__('Manage Interview Schedule')}}
@endsection

@push(StacksConstants::ADM_CSS)
{{--    <link rel="stylesheet" href="{{ asset('assets/libs/fullcalendar/dist/fullcalendar.min.css') }}">--}}
@endpush

@section(YieldingConstants::ADM_BDC)
    <li class="breadcrumb-item">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
        {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="breadcrumb-item">{{__('Interview Schedule')}}</li>
@endsection

@section(YieldingConstants::ADM_ACT_BTN)
    <div class="float-end">
        @can(PermissionsConstants::CR_ITV_SCHD)
            <a href="#" data-url="{{ route(ViewsConstants::ITV_SCD.'.create') }}" data-bs-toggle="tooltip" title="{{__('Create')}}" data-ajax-popup="true" data-title="{{__('Create New Interview Schedule')}}" class="btn btn-sm btn-primary">
                <i class="ti ti-plus"></i>
            </a>
        @endcan
    </div>
@endsection

@section(YieldingConstants::ADM_CTT)
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="col-lg-6">
                            <h5>{{ __('Calendar') }}</h5>
                        </div>
                        <div class="col-lg-6">
                            @if (isset($settings['google_calendar_enable']) && $settings['google_calendar_enable'] == 'on')
                                <select class="form-control" name="calendar_type" id="calendar_type" style="float: right;width: 150px;" onchange="get_data()">
                                    <option value="goggle_calendar">{{__('Google calendar')}}</option>
                                    <option value="local_calendar" selected="true">{{__('Local calendar')}}</option>
                                </select>
                            @endif
                            <input type="hidden" id="interview_calendar" value="{{url('/')}}">
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div id='calendar' class='calendar'></div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card">
                <div class="card-body">
                    <h4 class="mb-4">{{__('Schedule List')}}</h4>
                    <ul class="{{ ViewClassNamesConstants::LG_FLSH_W }}">
                        <li class="list-group-item card mb-3">
                            <div class="row align-items-center justify-content-between">
                                <div class=" align-items-center">
                                    @if(!$schedules->isEmpty())
                                        @foreach ($schedules as $schedule)
                                            <div class="card mb-3 border shadow-none">
                                                <div class="px-3">
                                                    <div class="row align-items-center">
                                                        <div class="col ml-n2">
                                                            <h5 class="text-sm mb-0">
                                                                <a href="#!">{{!empty($schedule->applications) ? !empty($schedule->applications->jobs) ? $schedule->applications->jobs->title : '' : ''}}</a>
                                                            </h5>
                                                            <p class="card-text small text-muted">
                                                                {{ !empty($schedule->applications)?$schedule->applications->name:'' }}
                                                            </p>
                                                            <p class="card-text small text-muted">
                                                                {{ $user?->dateFormat($schedule->date).' '.$user?->timeFormat($schedule->time) }}
                                                            </p>
                                                        </div>
                                                        <div class="col-auto text-right">
                                                            @can('edit interview schedule')
                                                                <div class="action-btn bg-primary ms-2">
                                                                    <a href="#" data-url="{{ route(ViewsConstants::ITV_SCD.'.edit',$schedule->id) }}" data-title="{{__('Edit Interview Schedule')}}" data-ajax-popup="true" class="mx-3 btn btn-sm  align-items-center" data-bs-toggle="tooltip" title="{{__('Edit')}}" data-original-title="{{__('Edit')}}"><i class="{{ ViewClassNamesConstants::TI_PC_WT }}"></i></a>
                                                                </div>
                                                            @endcan
                                                            @can('delete interview schedule')
                                                                    <div class="action-btn bg-danger ms-2">
                                                                        {!! Collective\Html\FormFacade::open(['method' => 'DELETE', 'route' => [ViewsConstants::ITV_SCD.'.destroy', $schedule->id],'id'=>'delete-form-'.$schedule->id]) !!}
                                                                            <a href="#" class="{{ ViewClassNamesConstants::BT_SM_CT_PR }}" data-bs-toggle="tooltip" title="{{__('Delete')}}" data-original-title="{{__('Delete')}}" data-confirm="{{ __(Utility::fetchLinkMessage($lang, 'generics', 'are_you_sure') ?? 'Are You Sure?') }}|{{ __(Utility::fetchLinkMessage($lang, 'generics', 'irreversible_action') ?? 'This action can not be undone. Do you want to continue?') }}" data-confirm-yes="document.getElementById('delete-form-{{$schedule->id}}').submit();"><i class="ti ti-trash text-white"></i></a>
                                                                        {!! Collective\Html\FormFacade::close() !!}
                                                                    </div>
                                                            @endcan
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    @else
                                        <div class="text-center">
                                            {{__('No Interview Scheduled!')}}
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
@endsection

@push(StacksConstants::ADM_SCR_PG)
    <script src="{{ asset('assets/js/plugins/main.min.js') }}"></script>
        <script async>
          (() => { 
              if (!window.translations) {
  window.translations = {};
}
const t = {
            ar:       { calendar_data_unavailable: 'لا يمكن تحميل بيانات التقويم' },
            da:       { calendar_data_unavailable: 'Kan ikke indlæse kalenderdata' },
            de:       { calendar_data_unavailable: 'Kalenderdaten konnten nicht geladen werden' },
            en:       { calendar_data_unavailable: 'Cannot load calendar data' },
            es:       { calendar_data_unavailable: 'No se pueden cargar los datos del calendario' },
            fr:       { calendar_data_unavailable: 'Impossible de charger les données du calendrier' },
            he:       { calendar_data_unavailable: 'לא ניתן לטעון נתוני לוח השנה' },
            it:       { calendar_data_unavailable: 'Impossibile caricare i dati del calendario' },
            ja:       { calendar_data_unavailable: 'カレンダーデータを読み込めません' },
            nl:       { calendar_data_unavailable: 'Kan kalendergegevens niet laden' },
            pl:       { calendar_data_unavailable: 'Nie można załadować danych kalendarza' },
            pt:       { calendar_data_unavailable: 'Não é possível carregar dados do calendário' },
            'pt-br':  { calendar_data_unavailable: 'Não é possível carregar dados do calendário' },
            ru:       { calendar_data_unavailable: 'Не удалось загрузить данные календаря' },
            tr:       { calendar_data_unavailable: 'Takvim verileri yüklenemiyor' },
            zh:       { calendar_data_unavailable: '无法加载日历数据' }
        };
Object.keys(t).forEach(
  k =>
    (window.translations[k] = {
      ...(window.translations[k] || {}),
      ...t[k],
    })
);
     
          })();
    </script>
    <script defer>
        (() => {
            const dataListenerAdded   = 'data-listener-added';
            const errFb               = '# ERROR';
            const dataClientLocalized = 'data-client-localized';
            const dataGuardMsg        = 'data-guard-msg';

            const getLocalizedMessage = (el, msgKey) => {
                let msg = errFb;
                if (
                    el.getAttribute('data-sv-localized') === 'true' ||
                    el.getAttribute(dataClientLocalized) === 'true'
                ) {
                    msg = el.getAttribute(dataGuardMsg) || errFb;
                } else {
                    let lang = (
                        window.sessionStorage.getItem('erp-np-lang') ||
                        document.documentElement.lang ||
                        'en'
                    )
                        .toLowerCase()
                        .replace(/_/g, '-');
                    lang = lang === 'pt-br' ? lang : lang.slice(0, 2);
                    msg = 
                        window.translations?.[lang]?.[msgKey] ||
                        el.getAttribute(dataGuardMsg) ||
                        window.translations?.['en']?.[msgKey] ||
                        errFb;
                    if (msg !== errFb) {
                        el.setAttribute(dataGuardMsg, msg);
                        el.setAttribute(dataClientLocalized, 'true');
                    }
                }
                return msg;
            };

            const handleErrorDisplay = el => {
                const message = el
                    ? getLocalizedMessage(el, 'calendar_data_unavailable')
                    : errFb;
                const hasBootstrap =
                    document.querySelector('link[href*="bootstrap"]') &&
                    window.bootstrap?.Toast;
                if (hasBootstrap) {
                    if (!document.querySelector('#error-toast')) {
                        const toast = document.createElement('div');
                        toast.id        = 'error-toast';
                        toast.className = 'toast align-items-center text-bg-danger border-0';
                        toast.setAttribute('role', 'alert');
                        toast.setAttribute('aria-live', 'assertive');
                        toast.setAttribute('aria-atomic', 'true');
                        toast.innerHTML = `
                            <div class="d-flex">
                                <div class="toast-body">${message}</div>
                                <button type="button"
                                        class="btn-close btn-close-white me-2 m-auto"
                                        data-bs-dismiss="toast"
                                        aria-label="Close"></button>
                            </div>`;
                        document.body.appendChild(toast);
                    }
                    new bootstrap.Toast(
                        document.querySelector('#error-toast')
                    ).show();
                } else {
                    alert(message);
                }
            };

            const getData = async () => {
                try {
                    if (typeof $ === 'undefined') {
                        console.error('jQuery is required');
                        return;
                    }
                    const calendarType = $('#calendar_type :selected').val() ?? '';
                    const $calendar    = $('#calendar');
                    $calendar.removeClass('local_calendar goggle_calendar');
                    if (!calendarType) {
                        $calendar.addClass('local_calendar');
                    }
                    calendarType && $calendar.addClass(calendarType);

                    const urlBase = $('#interview_calendar').val() ?? '';
                    if (!urlBase) return;

                    const events = await $.ajax({
                        url: `${urlBase}/interview-schedule/get_interview_data`,
                        method: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            calendar_type: calendarType
                        }
                    });

                    ((data) => {
                        try {
                            const calendarEl = document.getElementById('calendar');
                            if (!calendarEl) return;
                            const calendar = new FullCalendar.Calendar(calendarEl, {
                                headerToolbar: {
                                    left: 'prev,next today',
                                    center: 'title',
                                    right: 'timeGridDay,timeGridWeek,dayGridMonth'
                                },
                                buttonText: {
                                    timeGridDay:   '{{__("Day")}}',
                                    timeGridWeek:  '{{__("Week")}}',
                                    dayGridMonth:  '{{__("Month")}}'
                                },
                                slotLabelFormat: {
                                    hour:   '2-digit',
                                    minute: '2-digit',
                                    hour12: false
                                },
                                themeSystem:        'bootstrap',
                                allDaySlot:         false,
                                navLinks:           true,
                                droppable:          true,
                                selectable:         true,
                                selectMirror:       true,
                                editable:           true,
                                dayMaxEvents:       true,
                                handleWindowResize: true,
                                height:             'auto',
                                timeFormat:         'H(:mm)',
                                events:             data
                            });
                            calendar.render();
                        } catch {
                            const calendarEl = document.getElementById('calendar');
                            if (
                                calendarEl &&
                                calendarEl.getAttribute(dataListenerAdded) !== 'true'
                            ) {
                                calendarEl.addEventListener('click', () =>
                                    handleErrorDisplay(calendarEl)
                                );
                                calendarEl.setAttribute(dataListenerAdded, 'true');
                                const obs = new MutationObserver((_, o) => {
                                    if (!document.body.contains(calendarEl)) {
                                        calendarEl.removeEventListener(
                                            'click',
                                            () => handleErrorDisplay(calendarEl)
                                        );
                                        o.disconnect();
                                    }
                                });
                                obs.observe(document.body, {
                                    childList: true,
                                    subtree:   true
                                });
                            }
                        }
                    })(events);
                } catch {
                    const calendarEl = document.getElementById('calendar');
                    if (
                        calendarEl &&
                        calendarEl.getAttribute(dataListenerAdded) !== 'true'
                    ) {
                        calendarEl.addEventListener('click', () =>
                            handleErrorDisplay(calendarEl)
                        );
                        calendarEl.setAttribute(dataListenerAdded, 'true');
                        const obs = new MutationObserver((_, o) => {
                            if (!document.body.contains(calendarEl)) {
                                calendarEl.removeEventListener(
                                    'click',
                                    () => handleErrorDisplay(calendarEl)
                                );
                                o.disconnect();
                            }
                        });
                        obs.observe(document.body, {
                            childList: true,
                            subtree:   true
                        });
                    }
                }
            };

            $(getData);
        })();
    </script>
@endpush
