@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        StacksConstants,
        ViewClassNamesConstants as VC,
        YieldingConstants,
    };
    use Illuminate\Support\Facades\Route;
@endphp
@extends(ExtendingLayoutsConstants::ADM)

@section(YieldingConstants::ADM_PG_TTL)
    {{ __('Dashboard')}}
@endsection
@section(YieldingConstants::ADM_BDC)
    <li class="breadcrumb-item">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
        {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="breadcrumb-item">{{__('Client')}}</li>
@endsection

@push('theme-script')
    <script async src="{{ asset('assets/js/plugins/apexcharts.min.js') }}"></script>
@endpush

@push(StacksConstants::ADM_SCR_PG)
        <script async>
          (() => { 
              if (!window.translations) {
  window.translations = {};
}
const t = {
        ar: {
            calendar_init_failed: 'فشل تهيئة التقويم.',
            event_modal_unavailable: 'فشل تحميل تفاصيل الحدث.',
            chart_tasks_unavailable: 'فشل عرض مخطط المهام.',
            chart_projects_unavailable: 'فشل عرض مخطط حالة المشروع.'
        },
        da: {
            calendar_init_failed: 'Kunne ikke initialisere kalender.',
            event_modal_unavailable: 'Kunne ikke indlæse begivenhedsdetaljer.',
            chart_tasks_unavailable: 'Kunne ikke vise opgavegraf.',
            chart_projects_unavailable: 'Kunne ikke vise projektstatusgraf.'
        },
        de: {
            calendar_init_failed: 'Kalender konnte nicht initialisiert werden.',
            event_modal_unavailable: 'Ereignisdetails konnten nicht geladen werden.',
            chart_tasks_unavailable: 'Task-Diagramm konnte nicht dargestellt werden.',
            chart_projects_unavailable: 'Projektstatus-Diagramm konnte nicht dargestellt werden.'
        },
        en: {
            calendar_init_failed: 'Failed to initialize calendar.',
            event_modal_unavailable: 'Failed to load event details.',
            chart_tasks_unavailable: 'Failed to render tasks chart.',
            chart_projects_unavailable: 'Failed to render project status chart.'
        },
        es: {
            calendar_init_failed: 'Error al inicializar el calendario.',
            event_modal_unavailable: 'Error al cargar los detalles del evento.',
            chart_tasks_unavailable: 'Error al mostrar el gráfico de tareas.',
            chart_projects_unavailable: 'Error al mostrar el gráfico de estado del proyecto.'
        },
        fr: {
            calendar_init_failed: 'Échec de l’initialisation du calendrier.',
            event_modal_unavailable: 'Échec du chargement des détails de l’événement.',
            chart_tasks_unavailable: 'Échec de l’affichage du graphique des tâches.',
            chart_projects_unavailable: 'Échec de l’affichage du graphique de statut du projet.'
        },
        he: {
            calendar_init_failed: 'האתחול של היומן נכשל.',
            event_modal_unavailable: 'הטענת פרטי האירוע נכשלה.',
            chart_tasks_unavailable: 'הצגת תרשים המשימות נכשלה.',
            chart_projects_unavailable: 'הצגת תרשים מצב הפרויקט נכשלה.'
        },
        it: {
            calendar_init_failed: 'Impossibile inizializzare il calendario.',
            event_modal_unavailable: 'Impossibile caricare i dettagli dell’evento.',
            chart_tasks_unavailable: 'Impossibile visualizzare il grafico delle attività.',
            chart_projects_unavailable: 'Impossibile visualizzare il grafico di stato del progetto.'
        },
        ja: {
            calendar_init_failed: 'カレンダーの初期化に失敗しました。',
            event_modal_unavailable: 'イベントの詳細の読み込みに失敗しました。',
            chart_tasks_unavailable: 'タスクチャートの表示に失敗しました。',
            chart_projects_unavailable: 'プロジェクトステータスチャートの表示に失敗しました。'
        },
        nl: {
            calendar_init_failed: 'Initialisatie van kalender mislukt.',
            event_modal_unavailable: 'Kon gebeurtenisdetails niet laden.',
            chart_tasks_unavailable: 'Kon takengrafiek niet weergeven.',
            chart_projects_unavailable: 'Kon projectstatusgrafiek niet weergeven.'
        },
        pl: {
            calendar_init_failed: 'Nie udało się zainicjalizować kalendarza.',
            event_modal_unavailable: 'Nie można załadować szczegółów wydarzenia.',
            chart_tasks_unavailable: 'Nie udało się wyświetlić wykresu zadań.',
            chart_projects_unavailable: 'Nie udało się wyświetlić wykresu statusu projektu.'
        },
        pt: {
            calendar_init_failed: 'Falha ao inicializar o calendário.',
            event_modal_unavailable: 'Falha ao carregar detalhes do evento.',
            chart_tasks_unavailable: 'Falha ao exibir gráfico de tarefas.',
            chart_projects_unavailable: 'Falha ao exibir gráfico de status do projeto.'
        },
        'pt-br': {
            calendar_init_failed: 'Falha ao inicializar o calendário.',
            event_modal_unavailable: 'Falha ao carregar detalhes do evento.',
            chart_tasks_unavailable: 'Falha ao exibir gráfico de tarefas.',
            chart_projects_unavailable: 'Falha ao exibir gráfico de status do projeto.'
        },
        ru: {
            calendar_init_failed: 'Не удалось инициализировать календарь.',
            event_modal_unavailable: 'Не удалось загрузить детали события.',
            chart_tasks_unavailable: 'Не удалось отобразить график задач.',
            chart_projects_unavailable: 'Не удалось отобразить график статуса проекта.'
        },
        tr: {
            calendar_init_failed: 'Takvim başlatılamadı.',
            event_modal_unavailable: 'Etkinlik ayrıntıları yüklenemedi.',
            chart_tasks_unavailable: 'Görev grafiği oluşturulamadı.',
            chart_projects_unavailable: 'Proje durum grafiği oluşturulamadı.'
        },
        zh: {
            calendar_init_failed: '初始化日历失败。',
            event_modal_unavailable: '加载事件详情失败。',
            chart_tasks_unavailable: '呈现任务图表失败。',
            chart_projects_unavailable: '呈现项目状态图表失败。'
        }
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
    <script async>
        (() => {
        const ERR_FB = '# ERROR';
        const DS_CLIENT = 'data-client-localized';
        const DS_GUARD   = 'data-guard-msg';
        const LANG_KEY   = 'erp-np-lang';
        let errorMessage = '';
        const getLocalizedMessage = (key, el) => {
            let msg = ERR_FB;
            if (el.getAttribute(DS_CLIENT) === 'true') {
            msg = el.getAttribute(DS_GUARD) || msg;
            } else {
            let lang = (sessionStorage.getItem(LANG_KEY) || document.documentElement.lang || 'en')
                .toLowerCase().replace(/_/g,'-');
            lang = lang === 'pt-br' ? lang : lang.slice(0,2);
            msg = translations?.[lang]?.[key]
                ?? el.getAttribute(DS_GUARD)
                ?? translations?.['en']?.[key]
                ?? msg;
            if (msg !== ERR_FB) {
                el.setAttribute(DS_GUARD, msg);
                el.setAttribute(DS_CLIENT, 'true');
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
            const hasBs = !!document.querySelector('link[href*="bootstrap"]') && window.bootstrap?.Toast;
            if (hasBs) {
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
        new MutationObserver((m, obs) => {
            m.forEach(mut => 
            Array.from(mut.removedNodes).forEach(node => {
                if (node === document.documentElement) {
                document.removeEventListener('pointerup', onPointerUp);
                obs.disconnect();
                }
            })
            );
        }).observe(document.body, { childList:true, subtree:true });
        document.addEventListener('DOMContentLoaded', () => {
            const tasks = Array.isArray(window.calendarTasks) ? window.calendarTasks : {!! json_encode($calendarTasks) !!};
            if (tasks?.length > 0) {
            try {
                const calEl = document.getElementById('calendar');
                if (!calEl) throw new Error('calendar_init_failed');
                const calendar = new FullCalendar.Calendar(calEl, {
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay'
                },
                themeSystem: 'bootstrap',
                initialDate: '{{ $transdate }}',
                slotDuration: '00:10:00',
                navLinks: true,
                droppable: true,
                selectable: true,
                selectMirror: true,
                editable: true,
                dayMaxEvents: true,
                handleWindowResize: true,
                events: tasks
                });
                calendar.render();
            } catch (e) {
                errorMessage = getLocalizedMessage(e.message, document.getElementById('calendar')||document.body);
            }
            }
            if (document.body.getAttribute('data-event-listener') !== 'true') {
            document.body.setAttribute('data-event-listener','true');
            $(document).on('click', '.fc-day-grid-event', function(e) {
                try {
                if ($(this).hasClass('deal')) return;
                e.preventDefault();
                const title = $(this).find('.fc-content .fc-title').html() || '';
                const size = 'md';
                const url  = $(this).attr('href') ?? '';
                $("#commonModal .modal-title").html(title);
                $("#commonModal .modal-dialog").addClass('modal-' + size);
                $.ajax({
                    url,
                    success: data => {
                    $('#commonModal .modal-body').html(data);
                    $("#commonModal").modal('show');
                    },
                    error: xhr => {
                    const msg = xhr.responseJSON?.error || '';
                    showError(msg);
                    }
                });
                } catch {
                errorMessage = getLocalizedMessage('event_modal_unavailable', this);
                }
            });
            }
            try {
            const chartEl = document.querySelector('#chart-sales');
            if (!chartEl) throw new Error('chart_tasks_unavailable');
            const opts = {
                series: {!! json_encode($taskData['dataset']) !!},
                chart: {
                height: 250,
                type: 'area',
                dropShadow: { enabled: true, color: '#000', top:18, left:7, blur:10, opacity:0.2 },
                toolbar: { show: false }
                },
                dataLabels: { enabled: false },
                stroke: { width:2, curve:'smooth' },
                xaxis: {
                categories: {!! json_encode($taskData['label']) !!},
                title: { text: "{{ __('Days') }}" }
                },
                colors: ['#6fd944','#883617','#4e37b9','#8f841b'],
                grid: { strokeDashArray:4 },
                legend: { show:false },
                yaxis: { title:{ text: "{{ __('Amount') }}" } }
            };
            new ApexCharts(chartEl, opts).render();
            } catch {
            errorMessage = getLocalizedMessage('chart_tasks_unavailable', document.querySelector('#chart-sales')||document.body);
            }
            try {
            const donutEl = document.querySelector('#chart-doughnut');
            if (!donutEl) throw new Error('chart_projects_unavailable');
            const opts2 = {
                chart: { height: 140, type:'donut' },
                dataLabels: { enabled:false },
                plotOptions: { pie:{ donut:{ size:'70%' } } },
                series: {!! json_encode(array_values($projectData)) !!},
                labels: {!! json_encode($project_status) !!},
                colors: ["#bd9925","#2f71bd","#720d3a","#ef4917"],
                legend: { show:true }
            };
            new ApexCharts(donutEl, opts2).render();
            } catch {
            errorMessage = getLocalizedMessage('chart_projects_unavailable', document.querySelector('#chart-doughnut')||document.body);
            }
        });
        })();
    </script>
@endpush

@section(YieldingConstants::ADM_CTT)
    @php
        $project_task_percentage = $project['project_task_percentage'];
        $label='';
                if($project_task_percentage<=15){
                    $label='bg-danger';
                }else if ($project_task_percentage > 15 && $project_task_percentage <= 33) {
                    $label='bg-warning';
                } else if ($project_task_percentage > 33 && $project_task_percentage <= 70) {
                    $label='bg-primary';
                } else {
                    $label='bg-primary';
                }

        $project_percentage = $project['project_percentage'];
        $label1='';
                if($project_percentage<=15){
                    $label1='bg-danger';
                }else if ($project_percentage > 15 && $project_percentage <= 33) {
                    $label1='bg-warning';
                } else if ($project_percentage > 33 && $project_percentage <= 70) {
                    $label1='bg-primary';
                } else {
                    $label1='bg-primary';
                }

        $project_bug_percentage = $project['project_bug_percentage'];
        $label2='';
            if($project_bug_percentage<=15){
                $label2='bg-danger';
            }else if ($project_bug_percentage > 15 && $project_bug_percentage <= 33) {
                $label2='bg-warning';
            } else if ($project_bug_percentage > 33 && $project_bug_percentage <= 70) {
                $label2='bg-primary';
            } else {
                $label2='bg-primary';
            }
    @endphp
    <div class="row">
        @if(!empty($arrErr))
            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                @if(!empty($arrErr['system']))
                    <div class="alert alert-danger text-xs">
                         {{ __('are required in') }} <a href="{{ route('settings') }}" class=""><u> {{ __('System Setting') }}</u></a>
                    </div>
                @endif
                @if(!empty($arrErr['user']))
                    <div class="alert alert-danger text-xs">
                         <a href="{{ route('users') }}" class=""><u>{{ __('here') }}</u></a>
                    </div>
                @endif
                @if(!empty($arrErr['role']))
                    <div class="alert alert-danger text-xs">
                         <a href="{{ route('roles.index') }}" class=""><u>{{ __('here') }}</u></a>
                    </div>
                @endif
            </div>
        @endif
    </div>
    <div class="col-sm-12">
        <div class="row">
            <div class="col-xxl-6">
                <div class="row">
                    @if(isset($arrCount['deal']))
                        <div class="{{ VC::CLM6 }}">
                            <div class="card">
                                <div class="card-body">
                                    <div class="row align-items-center justify-content-between">
                                        <div class="col-auto mb-3 mb-sm-0">
                                            <div class="d-flex align-items-center">
                                                <div class="theme-avatar bg-primary">
                                                    <i class="ti ti-cast"></i>
                                                </div>
                                                <div class="ms-3">
                                                    <small class="text-muted">{{__('Total')}}</small>
                                                    <h6 class="m-0">{{__('Deal')}}</h6>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-auto text-end">
                                            <h5 class="m-0">{{ $arrCount['deal'] }}</h5>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                    @if(isset($arrCount['task']))
                            <div class="{{ VC::CLM6 }}">
                                            <div class="card">
                                                <div class="card-body">
                                                    <div class="row align-items-center justify-content-between">
                                                        <div class="col-auto mb-3 mb-sm-0">
                                                            <div class="d-flex align-items-center">
                                                                <div class="theme-avatar bg-primary">
                                                                    <i class="ti ti-list"></i>
                                                                </div>
                                                                <div class="ms-3">
                                                                    <small class="text-muted">{{__('Total')}}</small>
                                                                    <h6 class="m-0">{{__('Deal Task')}}</h6>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-auto text-end">
                                                            <h5 class="m-0">{{ $arrCount['task'] }}</h5>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                        @endif

                    <div class="col-xxl-12">
                        <div class="card">
                            <div class="card-header">
                                <h5>{{ __('Calendar') }}</h5>
                            </div>
                            <div class="card-body">
                                <div id='calendar' class='calendar'></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xxl-6">
                <div class="row">
                    <div class="col--xxl-12">
                        <div class="card">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4 col-sm-6">
                                            <div class="align-items-start">
                                                <div class="ms-2">
                                                    <p class="text-muted text-sm mb-0">{{__('Total Project')}}</p>
                                                    <h3 class="mb-0 text-warning">{{$project['project_percentage']}}%</h3>
                                                    <div class="progress mb-0">
                                                        <div class="progress-bar bg-{{$label1}}" style="width: {{$project['project_percentage']}}%;"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4 col-sm-6">
                                            <div class="align-items-start">
                                                <div class="ms-2">
                                                    <p class="text-muted text-sm mb-0">{{__('Total Project Tasks')}}</p>
                                                    <h3 class="mb-0 text-info">{{$project['projects_tasks_count']}}%</h3>
                                                    <div class="progress mb-0">
                                                        <div class="progress-bar bg-{{$label1}}" style="width: {{$project['project_task_percentage']}}%;"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4 col-sm-6">
                                            <div class="align-items-start">

                                                <div class="ms-2">

                                                    <p class="text-muted text-sm mb-0">{{__('Total Bugs')}}</p>
                                                    <h3 class="mb-0 text-danger">{{$project['projects_bugs_count']}}%</h3>
                                                    <div class="progress mb-0">
                                                        <div class="progress-bar bg-{{$label1}}" style="width: {{$project['project_bug_percentage']}}%;"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                    </div>
                    <div class="col-xxl-12">
                        <div class="card">
                            <div class="card-header">
                                <h5>{{__('Tasks Overview')}}</h5>
                                <h6 class="last-day-text">{{__('Last 7 Days')}}</h6>
                            </div>
                            <div class="card-body">
                                <div id="chart-sales" height="200" class="p-3"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xxl-12">
                        <div class="card">
                            <div class="card-header">
                                <h5>{{__('Project Status')}}
                                    <span class="float-end text-muted">{{__('Year').' - '.$currentYear}}</span>
                                </h5>

                            </div>
                            <div class="card-body">
                                <div id="chart-doughnut"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="{{ (Auth::user()->type =='company' || Auth::user()->type =='client') ? 'col-xl-6 col-lg-6 col-md-6' : 'col-xl-8 col-lg-8 col-md-8' }} col-sm-12">
                <div class="card bg-none min-410 mx-410">
                    <div class="card-header">
                        <h5>{{ __('Top Due Project') }}</h5>
                    </div>
                    <div class="card-body table-border-style">
                        <div class="table-responsive">
                        <table class="table align-items-center mb-0">
                            <thead>
                            <tr>
                                <th>{{__('Task Name')}}</th>
                                <th>{{__('Remain Task')}}</th>
                                <th>{{__('Due Date')}}</th>
                                <th>{{__('Action')}}</th>
                            </tr>
                            </thead>
                            <tbody class="list">
                            @forelse($project['projects'] as $project)
                                @php
                                    $datetime1 = new DateTime($project->due_date);
                                    $datetime2 = new DateTime(date('Y-m-d'));
                                    $interval = $datetime1->diff($datetime2);
                                    $days = $interval->format('%a');

                                    $project_last_stage = ($project->projectLastStage($project->id))?$project->projectLastStage($project->id)->id:'';
                                    $total_task = $project->projectTotalTask($project->id);
                                    $completed_task=$project->projectCompleteTask($project->id,$project_last_stage);
                                    $remain_task=$total_task-$completed_task;
                                @endphp
                                <tr>
                                    <td class="id-web">
                                        {{$project->project_name}}
                                    </td>
                                    <td>{{$remain_task }}</td>
                                    <td>{{ Auth::user()->dateFormat($project->end_date) }}</td>
                                    <td>
                                        <div class="action-btn bg-primary ms-2">
                                            <a href="{{ route('projects.show',$project->id) }}" class="mx-3 btn btn-sm align-items-center"><i class="ti ti-eye text-white"></i></a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr class="text-center">
                                    <td colspan="4">{{__('No Data Found.!')}}</td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                    </div>
                </div>
            </div>
            <div class="col-xxl-6">
                <div class="card bg-none min-410 mx-410">
                    <div class="card-header">
                        <h5>{{ __('Top Due Task') }}</h5>
                    </div>
                    <div class="card-body table-border-style">
                        <div class="table-responsive">
                        <table class="table align-items-center mb-0">
                            <thead>
                            <tr>
                                <th>{{__('Task Name')}}</th>
                                <th>{{__('Assign To')}}</th>
                                <th>{{__('Task Stage')}}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($top_tasks as $top_task)
                                <tr>
                                    <td class="id-web">
                                        {{$top_task->name}}
                                    </td>
                                    <td>
                                        <div class="avatar-group">
                                            @if($top_task->users()->count() > 0)
                                                @if($users = $top_task->users())
                                                    @foreach($users as $key => $user)
                                                        @if($key<3)
                                                            <a href="#" class="avatar rounded-circle avatar-sm">
                                                                <img data-original-title="{{(!empty($user)?$user->name:'')}}" @if($user->avatar) src="{{asset('/storage/uploads/avatar/'.$user->avatar)}}" @else src="{{asset('assets/img/avatar/avatar-1.png')}}" @endif title="{{ $user->name }}" class="hweb">
                                                            </a>
                                                        @else
                                                            @break
                                                        @endif
                                                    @endforeach
                                                @endif
                                                @if(count($users) > 3)
                                                    <a href="#" class="avatar rounded-circle avatar-sm">
                                                        <img  data-original-title="{{(!empty($user)?$user->name:'')}}" @if($user->avatar) src="{{asset('/storage/uploads/avatar/'.$user->avatar)}}" @else src="{{asset('assets/img/avatar/avatar-1.png')}}" @endif class="hweb">
                                                    </a>
                                                @endif
                                            @else
                                                {{ __('-') }}
                                            @endif
                                        </div>
                                    </td>
                                    <td><span class="p-2 px-3 rounded badge bg-">{{ $top_task->stage->name }}</span></td>
                                </tr>
                            @empty
                                <tr class="text-center">
                                    <td colspan="4">{{__('No Data Found.!')}}</td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
@endsection
