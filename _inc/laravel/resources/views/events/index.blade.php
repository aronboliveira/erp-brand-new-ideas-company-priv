@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        StacksConstants,
        ViewClassNamesConstants,
        YieldingConstants,
    };
    use Illuminate\Support\Facades\{Auth, Route};
    $user = Auth::user();
@endphp
@extends(ExtendingLayoutsConstants::ADM)

@section(YieldingConstants::ADM_PG_TTL)
    {{__('Event')}}
@endsection
@section(YieldingConstants::ADM_BDC)
    <li class="breadcrumb-item">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
        {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="breadcrumb-item">{{__('Event')}}</li>
@endsection
@php
    $settings = \App\Models\Utility::settings();
@endphp
@section(YieldingConstants::ADM_ACT_BTN)
    <div class="float-end">
        @can('create event')
            <a href="#" data-size="lg" data-url="{{ route('event.create') }}" data-ajax-popup="true" data-bs-toggle="tooltip" title="{{__('Create')}}" data-title="{{__('Create New Event')}}" class="btn btn-sm btn-primary">
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
                            <input type="hidden" id="path_admin" value="{{url('/')}}">
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
                    <h6 class="mb-4">{{__('Upcoming Events')}}</h6>
                    <ul class="{{ ViewClassNamesConstants::LG_FLSH_W }}">
                        <li class="list-group-item card mb-3">
                            <div class="row align-items-center justify-content-between">
                                <div class="align-items-center">
                                    @if(!$events->isEmpty())
                                        @forelse ($current_month_event as $event)
                                            <div class="card mb-3 border shadow-none">
                                                <div class="px-3">
                                                    <div class="row align-items-center">
                                                        <div class="col ml-n2">
                                                            <h5 class="text-sm mb-0 fc-event-title-container">
                                                                <a href="#" data-size="lg" data-url="{{ route('event.edit',$event->id) }}" data-ajax-popup="true" data-title="{{__('Edit Event')}}" class="fc-event-title text-primary">
                                                                    {{$event->title}}
                                                                </a>
                                                            </h5><br>
                                                            <p class="card-text small text-dark mt-0">
                                                                {{__('Start Date : ')}}
                                                                {{ $user?->dateFormat($event->start_date)}}<br>
                                                                {{__('End Date : ')}}
                                                                {{ $user?->dateFormat($event->end_date) }}
                                                            </p>
                                                        </div>
                                                        <div class="col-auto text-right">
                                                            <div class="action-btn bg-primary ms-2">
                                                                <a href="#" data-url="{{ route('event.edit',$event->id) }}" data-title="{{__('Edit Event')}}" data-ajax-popup="true" class="mx-3 btn btn-sm  align-items-center" data-bs-toggle="tooltip" title="{{__('Edit')}}" data-original-title="{{__('Edit')}}"><i class="{{ ViewClassNamesConstants::TI_PC_WT }}"></i></a>
                                                            </div>

                                                            <div class="action-btn bg-danger ms-2">
                                                                {!! Collective\Html\FormFacade::open(['method' => 'DELETE', 'route' => ['event.destroy', $event->id],'id'=>'delete-form-'.$event->id]) !!}
                                                                <a href="#" class="mx-3 btn btn-sm align-items-center bs-pass-para" data-bs-toggle="tooltip" title="{{__('Delete')}}" data-original-title="{{__('Delete')}}" data-confirm="{{__('Are You Sure?').'|'.__('This action can not be undone. Do you want to continue?')}}" data-confirm-yes="document.getElementById('delete-form-{{$event->id}}').submit();"><i class="ti ti-trash text-white"></i></a>
                                                                {!! Collective\Html\FormFacade::close() !!}
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @empty
                                            <tr>
                                                <td colspan="4">
                                                    <div class="text-center">
                                                        <h6>{{__('There is no event in this month')}}</h6>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforelse
                                    @else
                                        <div class="text-center">
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
        window.translations = {
          ar:    { calendar_data_unavailable:'فشل تحميل بيانات التقويم.', department_fetch_unavailable:'فشل جلب الأقسام.', employee_fetch_unavailable:'فشل جلب الموظفين.' },
          da:    { calendar_data_unavailable:'Kunne ikke hente kalenderdata.', department_fetch_unavailable:'Kunne ikke hente afdelinger.', employee_fetch_unavailable:'Kunne ikke hente medarbejdere.' },
          de:    { calendar_data_unavailable:'Abrufen der Kalenderdaten fehlgeschlagen.', department_fetch_unavailable:'Abrufen der Abteilungen fehlgeschlagen.', employee_fetch_unavailable:'Abrufen der Mitarbeiter fehlgeschlagen.' },
          en:    { calendar_data_unavailable:'Failed to load calendar data.',             department_fetch_unavailable:'Failed to fetch departments.',           employee_fetch_unavailable:'Failed to fetch employees.' },
          es:    { calendar_data_unavailable:'Error al cargar datos del calendario.',      department_fetch_unavailable:'Error al obtener departamentos.',        employee_fetch_unavailable:'Error al obtener empleados.' },
          fr:    { calendar_data_unavailable:'Échec du chargement du calendrier.',        department_fetch_unavailable:'Échec de la récupération des départements.', employee_fetch_unavailable:'Échec de la récupération des employés.' },
          it:    { calendar_data_unavailable:'Impossibile caricare il calendario.',         department_fetch_unavailable:'Impossibile recuperare i dipartimenti.', employee_fetch_unavailable:'Impossibile recuperare i dipendenti.' },
          ja:    { calendar_data_unavailable:'カレンダーデータの読み込みに失敗しました。', department_fetch_unavailable:'部署を取得できませんでした。',            employee_fetch_unavailable:'従業員を取得できませんでした。' },
          nl:    { calendar_data_unavailable:'Kan kalendergegevens niet laden.',         department_fetch_unavailable:'Kan afdelingen niet ophalen.',         employee_fetch_unavailable:'Kan medewerkers niet ophalen.' },
          pl:    { calendar_data_unavailable:'Nie udało się załadować kalendarza.',     department_fetch_unavailable:'Nie udało się pobrać działów.',         employee_fetch_unavailable:'Nie udało się pobrać pracowników.' },
          pt:    { calendar_data_unavailable:'Falha ao carregar calendário.',            department_fetch_unavailable:'Falha ao obter departamentos.',        employee_fetch_unavailable:'Falha ao obter funcionários.' },
          'pt-br':{ calendar_data_unavailable:'Falha ao carregar dados do calendário.', department_fetch_unavailable:'Falha ao buscar departamentos.',       employee_fetch_unavailable:'Falha ao buscar funcionários.' },
          ru:    { calendar_data_unavailable:'Не удалось загрузить календарь.',          department_fetch_unavailable:'Не удалось получить отделы.',          employee_fetch_unavailable:'Не удалось получить сотрудников.' },
          tr:    { calendar_data_unavailable:'Takvim verileri yüklenemedi.',             department_fetch_unavailable:'Birimler alınamadı.',                  employee_fetch_unavailable:'Çalışanlar alınamadı.' },
          zh:    { calendar_data_unavailable:'无法加载日历数据。',                         department_fetch_unavailable:'获取部门失败。',                         employee_fetch_unavailable:'获取员工失败。' }
        };
    </script>
    <script defer>
        (() => {
          const langKey      = 'erp-np-lang';
          const toastBoxId   = 'toast-box';
          const csrfToken    = '{{ csrf_token() }}';
          let   queuedError  = '';
        
          const getTr = key => {
            let lang = (sessionStorage.getItem(langKey) || document.documentElement.lang || 'en')
              .toLowerCase()
              .replace(/_/g, '-');
            lang = lang === 'pt-br' ? lang : lang.slice(0, 2);
            return window.translations?.[lang]?.[key]
              || window.translations.en[key]
              || '# ERROR';
          };
        
          const showToast = message => {
            const hasBs = Array.from(document.querySelectorAll('link[rel="stylesheet"]'))
              .some(l => /bootstrap/i.test(l.href)) && window.bootstrap?.Toast;
            if (hasBs) {
              let box = document.getElementById(toastBoxId);
              if (!box) {
                box = document.createElement('div');
                box.id = toastBoxId;
                box.setAttribute('aria-live', 'polite');
                box.setAttribute('aria-atomic', 'true');
                document.body.appendChild(box);
              }
              const t = document.createElement('div');
              t.className = 'toast';
              t.innerHTML = `<div class="toast-body">${message}</div>`;
              box.appendChild(t);
              bootstrap.Toast.getOrCreateInstance(t).show();
            } else {
              alert(message);
            }
          };
        
          document.addEventListener('pointerup', () => {
            if (queuedError) {
              showToast(queuedError);
              queuedError = '';
            }
          });
        
          new MutationObserver((records, obs) => {
            for (const r of records) {
              for (const n of r.removedNodes) {
                if (n === document.documentElement) {
                  obs.disconnect();
                }
              }
            }
          }).observe(document.body, { childList: true, subtree: true });
        
          const getData = () => {
            try {
              const base = $('#path_admin').val();
              if (!base) throw 0;
              let ct = $('#calendar_type').find(':selected').val();
              const cal = $('#calendar');
              cal.removeClass('local_calendar goggle_calendar');
              if (!ct) cal.addClass('local_calendar');
              cal.addClass(ct);
              $.ajax({
                url: `${base}/event/get_event_data`,
                type: 'POST',
                data: { _token: csrfToken, calendar_type: ct },
                success: data => {
                  try {
                    if (!window.FullCalendar?.Calendar) throw 0;
                    const calendar = new FullCalendar.Calendar(
                      document.getElementById('calendar'), {
                        headerToolbar: {
                          left: 'prev,next today',
                          center: 'title',
                          right: 'dayGridMonth,timeGridWeek,timeGridDay'
                        },
                        buttonText: {
                          timeGridDay: '{{ __("Day") }}',
                          timeGridWeek: '{{ __("Week") }}',
                          dayGridMonth: '{{ __("Month") }}'
                        },
                        themeSystem: 'bootstrap',
                        slotDuration: '00:10:00',
                        navLinks: true,
                        droppable: true,
                        selectable: true,
                        selectMirror: true,
                        editable: true,
                        dayMaxEvents: true,
                        handleWindowResize: true,
                        events: data
                      }
                    );
                    calendar.render();
                  } catch {
                    queuedError = getTr('calendar_data_unavailable');
                  }
                },
                error: () => {
                  queuedError = getTr('calendar_data_unavailable');
                }
              });
            } catch {
              queuedError = getTr('calendar_data_unavailable');
            }
          };
        
          const getDepartment = bid => {
            try {
              const url = '{{ route("event.getdepartment") }}';
              if (!url) throw 0;
              $.ajax({
                url,
                type: 'POST',
                data: { branch_id: bid ?? '', _token: csrfToken },
                success: data => {
                  try {
                    $('.department_id').remove();
                    $('.department_div').html(`
                      <select class="form-control department_id" id="choices-dept" placeholder="{{__("Select Department")}}" multiple></select>
                    `);
                    const sel = $('#choices-dept');
                    sel.append('<option value="0">{{ __("All") }}</option>');
                    for (const [k, v] of Object.entries(data || {})) {
                      sel.append(`<option value="${k}">${v}</option>`);
                    }
                    new Choices('#choices-dept', { removeItemButton: true });
                  } catch {
                    queuedError = getTr('department_fetch_unavailable');
                  }
                },
                error: () => {
                  queuedError = getTr('department_fetch_unavailable');
                }
              });
            } catch {
              queuedError = getTr('department_fetch_unavailable');
            }
          };
        
          const getEmployee = did => {
            try {
              const url = '{{ route("event.getemployee") }}';
              if (!url) throw 0;
              $.ajax({
                url,
                type: 'POST',
                data: { department_id: did ?? '', _token: csrfToken },
                success: data => {
                  try {
                    $('.employee_id').remove();
                    $('.employee_div').html(`
                      <select class="form-control employee_id" id="choices-emp" placeholder="{{__("Select Employee")}}" multiple></select>
                    `);
                    const sel = $('#choices-emp');
                    sel.append('<option value="0">{{ __("All") }}</option>');
                    for (const [k, v] of Object.entries(data || {})) {
                      sel.append(`<option value="${k}">${v}</option>`);
                    }
                    new Choices('#choices-emp', { removeItemButton: true });
                  } catch {
                    queuedError = getTr('employee_fetch_unavailable');
                  }
                },
                error: () => {
                  queuedError = getTr('employee_fetch_unavailable');
                }
              });
            } catch {
              queuedError = getTr('employee_fetch_unavailable');
            }
          };
        
          $(document).ready(() => {
            getData();
            const b = $('#branch_id').val();
            if (b != null) getDepartment(b);
          });
        
          $(document).on('change', 'select[name=branch_id]', e => {
            getDepartment(e.target.value);
          });
        
          $(document).on('change', '.department_id', e => {
            getEmployee($(e.target).val());
          });
        })();
    </script>
@endpush
