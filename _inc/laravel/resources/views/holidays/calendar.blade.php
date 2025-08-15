@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        StacksConstants,
        ViewClassNamesConstants,
        YieldingConstants,
    };
    use App\Models\Utility;
    use Illuminate\Support\Facades\{Auth, Route};
    $user = Auth::user();
    $lang = Utility::fetchUserLang(user:$user);
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@section(YieldingConstants::ADM_PG_TTL)
    {{__('Manage Holiday')}}
@endsection
@php
    $settings = Utility::settings();
@endphp
@push(StacksConstants::ADM_CSS)
    <link rel="stylesheet" href="{{ asset('assets/libs/fullcalendar/dist/fullcalendar.min.css') }}">
@endpush
@section(YieldingConstants::ADM_BDC)
    <li class="breadcrumb-item">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
        {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="breadcrumb-item">{{__('Holiday')}}</li>
@endsection
@section(YieldingConstants::ADM_ACT_BTN)
    @can('create holiday')
        <div class="float-end">
            <a href="{{ route(ViewsConstants::HLD.'.index') }}" class="btn btn-sm btn-primary" data-bs-toggle="tooltip" title="{{__('List View')}}" data-original-title="{{__('List View')}}">
                <i class="ti ti-list"></i>
            </a>
            <a href="#" data-size="lg" data-url="{{ route(ViewsConstants::HLD.'.create') }}" data-ajax-popup="true" data-bs-toggle="tooltip" title="{{__('Create')}}" data-title="{{__('Create New Holiday')}}" class="btn btn-sm btn-primary">
                <i class="ti ti-plus"></i>
            </a>
        </div>
    @endcan
@endsection
@section(YieldingConstants::ADM_CTT)
    <div class="row">
        <div class="col-sm-12">
            <div class="mt-2" id="multiCollapseExample1">
                <div class="card">
                    <div class="card-body">
                        {{ Collective\Html\FormFacade::open(array('route' => array(ViewsConstants::HLD.'.calendar'),'method'=>'get','id'=>'holiday_filter')) }}
                            <div class="row align-items-center justify-content-end">
                                <div class="col-xl-10">
                                    <div class="row">
                                        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                                            <div class="btn-box"></div>
                                        </div>
                                        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                                            <div class="btn-box"></div>
                                        </div>
                                        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                                            <div class="btn-box">
                                                {{Collective\Html\FormFacade::label('start_date',__('Start Date'),['class'=>'form-label'])}}
                                                {{Collective\Html\FormFacade::date('start_date',isset($_GET['start_date'])?$_GET['start_date']:'',array('class'=>'month-btn form-control'))}}
                                            </div>
                                        </div>
                                        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                                            <div class="btn-box">
                                                {{Collective\Html\FormFacade::label('end_date',__('End Date'),['class'=>'form-label'])}}
                                                {{Collective\Html\FormFacade::date('end_date',isset($_GET['end_date'])?$_GET['end_date']:'',array('class'=>'month-btn form-control '))}}                                        </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <div class="row">
                                            <div class="col-auto mt-4">
                                                <a href="#" class="btn btn-sm btn-primary" onclick="document.getElementById('holiday_filter').submit(); return false;" data-bs-toggle="tooltip" title="{{__('Apply')}}" data-original-title="{{__('apply')}}">
                                                    <span class="btn-inner--icon"><i class="ti ti-search"></i></span>
                                                </a>
                                                <a href="{{route(ViewsConstants::HLD.'.calendar')}}" class="btn btn-sm btn-danger" data-bs-toggle="tooltip"  title="{{ __('Reset') }}" data-original-title="{{__('Reset')}}">
                                                    <span class="btn-inner--icon"><i class="ti ti-trash-off text-white-off"></i></span>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        {{ Collective\Html\FormFacade::close() }}
                    </div>
                </div>
            </div>
        </div>

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
                            <input type="hidden" id="holiday_calendar" value="{{url('/')}}">
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
                    <h4 class="mb-4">{{__('Holiday List')}}</h4>
                    <ul class="{{ ViewClassNamesConstants::LG_FLSH_W }}">
                        <li class="list-group-item card mb-3">
                            <div class="row align-items-center justify-content-between">
                                <div class=" align-items-center">
                                    @if(!$holidays->isEmpty())
                                        @foreach ($holidays as $holiday)
                                            <div class="card mb-3 border shadow-none">
                                                <div class="px-3">
                                                    <div class="row align-items-center">
                                                        <div class="col ml-n2">
                                                            <h5 class="text-sm mb-0">
                                                            </h5>
                                                            <p class="card-text small text-primary">
                                                                {{($holiday->occasion)}}
                                                            </p>
                                                            <p class="card-text small text-dark">
                                                                {{__('Start Date :')}}
                                                                {{ $user?->dateFormat($holiday->date) }}<br>
                                                                {{__('End Date :')}}
                                                                {{ $user?->dateFormat($holiday->end_date) }}
                                                            </p>
                                                        </div>
                                                        <div class="col-auto text-right">
                                                            @can('edit interview schedule')
                                                                <div class="action-btn bg-primary ms-2">
                                                                    <a href="#" data-url="{{ route(ViewsConstants::HLD.'.edit',$holiday->id) }}" data-title="{{__('Edit Interview Schedule')}}" data-ajax-popup="true" class="mx-3 btn btn-sm  align-items-center" data-bs-toggle="tooltip" title="{{__('Edit')}}" data-original-title="{{__('Edit')}}"><i class="{{ ViewClassNamesConstants::TI_PC_WT }}"></i></a>
                                                                </div>
                                                            @endcan
                                                            @can('delete interview schedule')
                                                                    <div class="action-btn bg-danger ms-2">
                                                                        {!! Collective\Html\FormFacade::open(['method' => 'DELETE', 'route' => [ViewsConstants::HLD.'.destroy', $holiday->id],'id'=>'delete-form-'.$holiday->id]) !!}
                                                                        <a href="#" class="mx-3 btn btn-sm align-items-center bs-pass-para" data-bs-toggle="tooltip" title="{{__('Delete')}}" data-original-title="{{__('Delete')}}" data-confirm="{{ __(Utility::fetchLinkMessage($lang, 'generics', 'are_you_sure') ?? 'Are You Sure?') }}|{{ __(Utility::fetchLinkMessage($lang, 'generics', 'irreversible_action') ?? 'This action can not be undone. Do you want to continue?') }}" data-confirm-yes="document.getElementById('delete-form-{{$holiday->id}}').submit();"><i class="ti ti-trash text-white"></i></a>
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
        window.translations = {
          ar: {
            calendar_type_element_unavailable:   'عنصر نوع التقويم غير متوفر.',
            calendar_element_unavailable:        'عنصر التقويم غير متوفر.',
            holiday_fetch_unavailable:           'فشل جلب بيانات العطل.',
            calendar_initialization_failed:      'فشل تهيئة التقويم.'
          },
          da: {
            calendar_type_element_unavailable:   'Elementet til kalender type ikke fundet.',
            calendar_element_unavailable:        'Kalenderelement ikke fundet.',
            holiday_fetch_unavailable:           'Kunne ikke hente helligdagsdata.',
            calendar_initialization_failed:      'Kalenderinitialisering mislykkedes.'
          },
          de: {
            calendar_type_element_unavailable:   'Kalendertyp-Element nicht gefunden.',
            calendar_element_unavailable:        'Kalender-Element nicht gefunden.',
            holiday_fetch_unavailable:           'Abruf der Feiertagsdaten fehlgeschlagen.',
            calendar_initialization_failed:      'Initialisierung des Kalenders fehlgeschlagen.'
          },
          en: {
            calendar_type_element_unavailable:   'Calendar type element unavailable.',
            calendar_element_unavailable:        'Calendar element unavailable.',
            holiday_fetch_unavailable:           'Failed to fetch holiday data.',
            calendar_initialization_failed:      'Failed to initialize calendar.'
          },
          es: {
            calendar_type_element_unavailable:   'Elemento de tipo de calendario no disponible.',
            calendar_element_unavailable:        'Elemento de calendario no disponible.',
            holiday_fetch_unavailable:           'Error al obtener los datos de días festivos.',
            calendar_initialization_failed:      'Error al inicializar el calendario.'
          },
          fr: {
            calendar_type_element_unavailable:   'Élément de type de calendrier indisponible.',
            calendar_element_unavailable:        'Élément de calendrier indisponible.',
            holiday_fetch_unavailable:           'Échec de la récupération des données des jours fériés.',
            calendar_initialization_failed:      'Échec de l’initialisation du calendrier.'
          },
          he: {
            calendar_type_element_unavailable:   'אלמנט סוג לוח שנה לא זמין.',
            calendar_element_unavailable:        'אלמנט לוח שנה לא זמין.',
            holiday_fetch_unavailable:           'לא ניתנו נתוני חגים.',
            calendar_initialization_failed:      'התחוללה שגיאה באתחול לוח השנה.'
          },
          it: {
            calendar_type_element_unavailable:   'Elemento tipo calendario non disponibile.',
            calendar_element_unavailable:        'Elemento calendario non disponibile.',
            holiday_fetch_unavailable:           'Impossibile recuperare i dati delle festività.',
            calendar_initialization_failed:      'Impossibile inizializzare il calendario.'
          },
          ja: {
            calendar_type_element_unavailable:   'カレンダータイプ要素が利用できません。',
            calendar_element_unavailable:        'カレンダー要素が利用できません。',
            holiday_fetch_unavailable:           '祝日データの取得に失敗しました。',
            calendar_initialization_failed:      'カレンダーの初期化に失敗しました。'
          },
          nl: {
            calendar_type_element_unavailable:   'Kalendertype-element niet beschikbaar.',
            calendar_element_unavailable:        'Kalenderelement niet beschikbaar.',
            holiday_fetch_unavailable:           'Kon feestdaggegevens niet ophalen.',
            calendar_initialization_failed:      'Initialiseren van de kalender mislukt.'
          },
          pl: {
            calendar_type_element_unavailable:   'Element typu kalendarza niedostępny.',
            calendar_element_unavailable:        'Element kalendarza niedostępny.',
            holiday_fetch_unavailable:           'Nie udało się pobrać danych o świętach.',
            calendar_initialization_failed:      'Nie udało się zainicjować kalendarza.'
          },
          pt: {
            calendar_type_element_unavailable:   'Elemento de tipo de calendário indisponível.',
            calendar_element_unavailable:        'Elemento de calendário indisponível.',
            holiday_fetch_unavailable:           'Falha ao obter dados de feriados.',
            calendar_initialization_failed:      'Falha ao inicializar o calendário.'
          },
          'pt-br': {
            calendar_type_element_unavailable:   'Elemento de tipo de calendário indisponível.',
            calendar_element_unavailable:        'Elemento de calendário indisponível.',
            holiday_fetch_unavailable:           'Falha ao obter dados de feriados.',
            calendar_initialization_failed:      'Falha ao inicializar o calendário.'
          },
          ru: {
            calendar_type_element_unavailable:   'Элемент типа календаря недоступен.',
            calendar_element_unavailable:        'Элемент календаря недоступен.',
            holiday_fetch_unavailable:           'Не удалось получить данные о праздниках.',
            calendar_initialization_failed:      'Не удалось инициализировать календарь.'
          },
          tr: {
            calendar_type_element_unavailable:   'Takvim türü öğesi bulunamadı.',
            calendar_element_unavailable:        'Takvim öğesi bulunamadı.',
            holiday_fetch_unavailable:           'Tatil verileri alınamadı.',
            calendar_initialization_failed:      'Takvim başlatılamadı.'
          },
          zh: {
            calendar_type_element_unavailable:   '日历类型元素不可用。',
            calendar_element_unavailable:        '日历元素不可用。',
            holiday_fetch_unavailable:           '获取节假日数据失败。',
            calendar_initialization_failed:      '初始化日历失败。'
          }
        };
    </script>
    <script defer>
        (() => {
            const ERR_FB      = '# ERROR';
            const CLIENT_FLAG = 'data-client-localized';
            const GUARD_MSG   = 'data-guard-msg';
            const LANG_KEY    = 'erp-np-lang';
            let   errorMessage = '';
        
            const getMsg = (key, el) => {
            let msg = ERR_FB;
            if (el.getAttribute(CLIENT_FLAG) === 'true') {
                msg = el.getAttribute(GUARD_MSG) || msg;
            } else {
                let lang = (sessionStorage.getItem(LANG_KEY) || document.documentElement.lang || 'en')
                .toLowerCase().replace(/_/g,'-');
                lang = (lang === 'pt-br' ? lang : lang.slice(0,2));
                msg = window.translations?.[lang]?.[key]
                || el.getAttribute(GUARD_MSG)
                || window.translations?.['en']?.[key]
                || msg;
                if (msg !== ERR_FB) {
                el.setAttribute(GUARD_MSG, msg);
                el.setAttribute(CLIENT_FLAG, 'true');
                }
            }
            return msg;
            };
        
            const showError = message => {
            try {
                let container = document.getElementById('toast-container');
                if (!container) {
                container = document.createElement('div');
                container.id = 'toast-container';
                document.body.appendChild(container);
                }
                const hasBS = document.querySelector('link[href*="bootstrap"]') && window.bootstrap?.Toast;
                if (hasBS) {
                const toast = document.createElement('div');
                toast.className = 'toast';
                toast.setAttribute('role','alert');
                toast.setAttribute('aria-live','assertive');
                toast.setAttribute('aria-atomic','true');
                const body = document.createElement('div');
                body.className = 'toast-body';
                body.textContent = message;
                toast.appendChild(body);
                container.appendChild(toast);
                bootstrap.Toast.getOrCreateInstance(toast).show();
                } else {
                alert(message);
                }
            } catch {
                alert(message);
            }
            };
        
            const onPointerUp = () => {
            if (errorMessage) {
                showError(errorMessage);
                errorMessage = '';
            }
            };
            document.addEventListener('pointerup', onPointerUp);
        
            new MutationObserver((muts, obs) => {
            muts.forEach(m => Array.from(m.removedNodes).forEach(n => {
                if (n === document.documentElement) {
                document.removeEventListener('pointerup', onPointerUp);
                obs.disconnect();
                }
            }));
            }).observe(document.body, { childList:true, subtree:true });
        
            document.addEventListener('DOMContentLoaded', () => {
            try {
                const typeEl = document.querySelector('#calendar_type');
                if (!typeEl) throw new Error('calendar_type_element_unavailable');
                const calEl  = document.getElementById('calendar');
                if (!calEl) throw new Error('calendar_element_unavailable');
                const type   = typeEl.value ?? '';
                calEl.classList.remove('local_calendar','goggle_calendar');
                calEl.classList.add(type || 'local_calendar');
        
                const base = document.getElementById('holiday_calendar')?.value ?? '';
                if (!base) throw new Error('holiday_fetch_unavailable');
                const url = `${base}/holiday/get_holiday_data`;
        
                $.ajax({
                url,
                type: 'POST',
                data: { _token: '{{ csrf_token() }}', calendar_type: type }
                })
                .done(data => {
                try {
                    const calendar = new FullCalendar.Calendar(calEl, {
                    headerToolbar: {
                        left:  'prev,next today',
                        center:'title',
                        right: 'timeGridDay,timeGridWeek,dayGridMonth'
                    },
                    buttonText: {
                        timeGridDay:   "{{__('Day')}}",
                        timeGridWeek:  "{{__('Week')}}",
                        dayGridMonth:  "{{__('Month')}}"
                    },
                    themeSystem:      'bootstrap',
                    initialDate:      '{{ $transdate }}',
                    slotDuration:     '00:10:00',
                    navLinks:         true,
                    droppable:        true,
                    selectable:       true,
                    selectMirror:     true,
                    editable:         true,
                    dayMaxEvents:     true,
                    handleWindowResize:true,
                    events:           data
                    });
                    calendar.render();
                } catch {
                    throw new Error('calendar_initialization_failed');
                }
                })
                .fail(() => { throw new Error('holiday_fetch_unavailable'); });
            } catch (e) {
                errorMessage = getMsg(e.message, document.documentElement);
            }
            });
        })();
    </script>
@endpush
