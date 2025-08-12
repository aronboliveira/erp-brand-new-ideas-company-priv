@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        StacksConstants,
        ViewsConstants,
        ViewClassNamesConstants as VC,
        YieldingConstants,
    };
    use App\Models\Utility;
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\Str;
    $lang = Utility::fetchUserLang();
    $projectIndexBaseName = ViewsConstants::PRJ . '.index';
    $projectIndexKebabName = Str::kebab($projectIndexBaseName);
    $projectIndexResolvedName = Route::has($projectIndexBaseName) ? $projectIndexBaseName : (Route::has($projectIndexKebabName) ? $projectIndexKebabName : null);
    $projectIndexUrl = $projectIndexResolvedName ? route($projectIndexResolvedName) : '#';
    $projectIndexGuardMsg = Utility::fetchLinkMessage($lang, ViewsConstants::PRJ, 'project_index_route_unavailable') ?? 'Project index route is unavailable. Please contact technical support or your domain administrator.';
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@section(YieldingConstants::ADM_PG_TTL)
    {{ucwords($project->project_name)}}
@endsection
@push(StacksConstants::ADM_SCR_PG)
    <script async>
    window.translations={
        ar:{timesheet_chart_unavailable:'تعذّر عرض مخطط الجداول الزمنية',task_chart_unavailable:'تعذّر عرض مخطط المهام',users_load_unavailable:'تعذّر تحميل أعضاء المشروع',invite_unavailable:'تعذّر دعوة المستخدم',copy_unavailable:'تعذّر نسخ الرابط'},
        da:{timesheet_chart_unavailable:'Kunne ikke vise timesheet-diagram',task_chart_unavailable:'Kunne ikke vise opgavediagram',users_load_unavailable:'Kunne ikke indlæse projektbrugere',invite_unavailable:'Kunne ikke invitere bruger',copy_unavailable:'Kunne ikke kopiere linket'},
        de:{timesheet_chart_unavailable:'Zeiterfassungsdiagramm konnte nicht angezeigt werden',task_chart_unavailable:'Aufgabendagramm konnte nicht angezeigt werden',users_load_unavailable:'Projektmitglieder konnten nicht geladen werden',invite_unavailable:'Benutzer konnte nicht eingeladen werden',copy_unavailable:'Link konnte nicht kopiert werden'},
        en:{timesheet_chart_unavailable:'Cannot render timesheet chart',task_chart_unavailable:'Cannot render task chart',users_load_unavailable:'Cannot load project members',invite_unavailable:'Cannot invite user',copy_unavailable:'Cannot copy link'},
        es:{timesheet_chart_unavailable:'No se puede mostrar el gráfico de partes',task_chart_unavailable:'No se puede mostrar el gráfico de tareas',users_load_unavailable:'No se pueden cargar los miembros del proyecto',invite_unavailable:'No se puede invitar al usuario',copy_unavailable:'No se puede copiar el enlace'},
        fr:{timesheet_chart_unavailable:'Impossible d’afficher le graphique des feuilles de temps',task_chart_unavailable:'Impossible d’afficher le graphique des tâches',users_load_unavailable:'Impossible de charger les membres du projet',invite_unavailable:'Impossible d’inviter l’utilisateur',copy_unavailable:'Impossible de copier le lien'},
        he:{timesheet_chart_unavailable:'לא ניתן להציג תרשים גיליונות זמנים',task_chart_unavailable:'לא ניתן להציג תרשים משימות',users_load_unavailable:'לא ניתן לטעון חברי פרויקט',invite_unavailable:'לא ניתן להזמין משתמש',copy_unavailable:'לא ניתן להעתיק את הקישור'},
        it:{timesheet_chart_unavailable:'Impossibile mostrare il grafico dei timesheet',task_chart_unavailable:'Impossibile mostrare il grafico delle attività',users_load_unavailable:'Impossibile caricare i membri del progetto',invite_unavailable:'Impossibile invitare l’utente',copy_unavailable:'Impossibile copiare il link'},
        ja:{timesheet_chart_unavailable:'工数チャートを表示できません',task_chart_unavailable:'タスクチャートを表示できません',users_load_unavailable:'プロジェクトメンバーを読み込めません',invite_unavailable:'ユーザーを招待できません',copy_unavailable:'リンクをコピーできません'},
        nl:{timesheet_chart_unavailable:'Kan timesheetgrafiek niet weergeven',task_chart_unavailable:'Kan taakgrafiek niet weergeven',users_load_unavailable:'Kan projectleden niet laden',invite_unavailable:'Kan gebruiker niet uitnodigen',copy_unavailable:'Kan link niet kopiëren'},
        pl:{timesheet_chart_unavailable:'Nie można wyświetlić wykresu timesheet',task_chart_unavailable:'Nie można wyświetlić wykresu zadań',users_load_unavailable:'Nie można wczytać członków projektu',invite_unavailable:'Nie można zaprosić użytkownika',copy_unavailable:'Nie można skopiować linku'},
        pt:{timesheet_chart_unavailable:'Não foi possível exibir o gráfico de horas',task_chart_unavailable:'Não foi possível exibir o gráfico de tarefas',users_load_unavailable:'Não foi possível carregar os membros do projeto',invite_unavailable:'Não foi possível convidar o usuário',copy_unavailable:'Não foi possível copiar o link'},
        'pt-br':{timesheet_chart_unavailable:'Não foi possível exibir o gráfico de horas',task_chart_unavailable:'Não foi possível exibir o gráfico de tarefas',users_load_unavailable:'Não foi possível carregar os membros do projeto',invite_unavailable:'Não foi possível convidar o usuário',copy_unavailable:'Não foi possível copiar o link'},
        ru:{timesheet_chart_unavailable:'Не удалось отобразить график табеля',task_chart_unavailable:'Не удалось отобразить график задач',users_load_unavailable:'Не удалось загрузить участников проекта',invite_unavailable:'Не удалось пригласить пользователя',copy_unavailable:'Не удалось скопировать ссылку'},
        tr:{timesheet_chart_unavailable:'Zaman çizelgesi grafiği oluşturulamadı',task_chart_unavailable:'Görev grafiği oluşturulamadı',users_load_unavailable:'Proje üyeleri yüklenemedi',invite_unavailable:'Kullanıcı davet edilemedi',copy_unavailable:'Bağlantı kopyalanamadı'},
        zh:{timesheet_chart_unavailable:'无法渲染工时图表',task_chart_unavailable:'无法渲染任务图表',users_load_unavailable:'无法加载项目成员',invite_unavailable:'无法邀请用户',copy_unavailable:'无法复制链接'}
    };
    </script>
    <script defer>
    (()=>{
        const errFb="# ERROR";
        const dataClientLocalized="data-client-localized";
        const dataGuardMsg="data-guard-msg";
        const DATA_LISTENER_ADDED="data-listener-added";
        const DATA_RENDERED="data-chart-rendered";

        const getMsg=(el,msgKey)=>{let msg=errFb; if(el?.getAttribute("data-sv-localized")==="true"||el?.getAttribute(dataClientLocalized)==="true") msg=el.getAttribute(dataGuardMsg)||errFb; else {let lang=(window.sessionStorage.getItem("erp-np-lang")||document.documentElement.lang||"en").toLowerCase().replace(/_/g,"-"); lang=lang==="pt-br"?lang:lang.slice(0,2); msg=window.translations?.[lang]?.[msgKey]||el?.getAttribute(dataGuardMsg)||window.translations?.en?.[msgKey]||errFb; if(msg!==errFb){el?.setAttribute(dataGuardMsg,msg); el?.setAttribute(dataClientLocalized,"true");}} return msg;};

        const showError=(el,key,ev="click")=>{const message=getMsg(el||document.body,key); const hasBs=document.querySelector('link[href*="bootstrap"]')&&window.bootstrap?.Toast; const id="error-toast"; if(hasBs){if(!document.querySelector("#"+id)){const t=document.createElement("div"); t.id=id; t.className="toast align-items-center text-bg-danger border-0"; t.setAttribute("role","alert"); t.setAttribute("aria-live","assertive"); t.setAttribute("aria-atomic","true"); t.innerHTML=`<div class="d-flex"><div class="toast-body">${message}</div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button></div>`; document.body.appendChild(t);} const toastEl=document.querySelector("#"+id); const onceHandler=()=>new bootstrap.Toast(toastEl).show(); if(!toastEl.getAttribute(DATA_LISTENER_ADDED)){ toastEl.setAttribute(DATA_LISTENER_ADDED,"true"); const mo=new MutationObserver((_,o)=>{ if(!document.body.contains(toastEl)){ toastEl.removeEventListener(ev,onceHandler); o.disconnect(); }}); mo.observe(document.body,{childList:true,subtree:true}); } document.addEventListener(ev,onceHandler,{once:true}); } else { const onceHandler=()=>alert(message); document.addEventListener(ev,onceHandler,{once:true}); }};

        const guardOnce=(el,key,ev="pointerup")=>{ if(!el||el.getAttribute(DATA_LISTENER_ADDED)==="true") return; const handler=()=>showError(el,key,ev); el.addEventListener(ev,handler,{once:true}); el.setAttribute(DATA_LISTENER_ADDED,"true"); const mo=new MutationObserver((_,o)=>{ if(!document.body.contains(el)){ el.removeEventListener(ev,handler); o.disconnect(); }}); mo.observe(document.body,{childList:true,subtree:true}); };

        const renderChart=(selector,options,key)=>{try{ if(typeof ApexCharts==="undefined"){ console.error("ApexCharts failed to load"); guardOnce(document.querySelector(selector)||document.body,key,"click"); return; } const el=document.querySelector(selector); if(!el){ guardOnce(document.body,key,"click"); return; } if(el.getAttribute(DATA_RENDERED)==="true") return; const chart=new ApexCharts(el,options); chart.render(); el.setAttribute(DATA_RENDERED,"true"); }catch{ guardOnce(document.querySelector(selector)||document.body,key,"click"); }};

        const routeGuard=(element)=>{ const url=element?.getAttribute?.("data-url"); const href=element?.getAttribute?.("action")||element?.getAttribute?.("href"); return (!url||url==="#") && (!href||href==="#"); };

        const loadProjectUser=()=>{const $main=$("#project_users"); const el=$main.get(0); try{ if(routeGuard(el)){ guardOnce(el,"users_load_unavailable"); return; } $.ajax({ url:'{{ route('project.user') }}', data:{ project_id:'{{$project->id}}' }, beforeSend:()=>{ $('#project_users').html('<tr><th colspan="2" class="h6 text-center pt-5">{{__("Loading...")}}</th></tr>'); }, success:(data)=>{ $main.html(data?.html ?? ""); $('[id^=fire-modal]').remove(); }, error:()=>guardOnce(el,"users_load_unavailable") }); }catch{ guardOnce(el,"users_load_unavailable"); }};

        try{
        if(typeof $==="undefined"){ console.error("jQuery failed to load"); return; }

        // Charts (safe to defer; render after DOM parsed)
        (function(){const options={chart:{type:"area",height:60,sparkline:{enabled:true}},colors:["#ffa21d"],dataLabels:{enabled:false},stroke:{curve:"smooth",width:2},series:[{name:"Bandwidth",data:{{ json_encode(array_map('intval',$project_data['timesheet_chart']['chart'])) }} }],tooltip:{followCursor:false,fixed:{enabled:false},x:{show:false},y:{title:{formatter:()=>""}},marker:{show:false}}}; renderChart("#timesheet_chart",options,"timesheet_chart_unavailable");})();
        (function(){const options={chart:{type:"area",height:60,sparkline:{enabled:true}},colors:["#ffa21d"],dataLabels:{enabled:false},stroke:{curve:"smooth",width:2},series:[{name:"Bandwidth",data:{{ json_encode($project_data['task_chart']['chart']) }} }],tooltip:{followCursor:false,fixed:{enabled:false},x:{show:false},y:{title:{formatter:()=>""}},marker:{show:false}}}; renderChart("#task_chart",options,"task_chart_unavailable");})();

        // DOM ready for AJAX + bindings
        $(function(){
            try{ loadProjectUser(); }catch{ guardOnce(document.querySelector("#project_users")||document.body,"users_load_unavailable"); }

            $(document).on("click",".invite_usr",function(){
            const el=this;
            try{
                if(routeGuard(el)){ guardOnce(el,"invite_unavailable","pointerup"); return; }
                const project_id=$("#project_id").val() ?? "{{$project->id}}";
                const user_id=$(el).attr("data-id") ?? "";
                const endpoint=$(el).get(0).getAttribute("data-url") && $(el).get(0).getAttribute("data-url")!=="#" ? $(el).get(0).getAttribute("data-url") : '{{ route('invite.project.user.member') }}';
                $.ajax({
                url:endpoint,
                method:"POST",
                dataType:"json",
                data:{ project_id, user_id, _token:"{{ csrf_token() }}" },
                success:(data)=>{ if(String(data?.code)==="200"){ show_toastr(data.status,data.success,"success"); setTimeout(()=>location.reload(),5000); loadProjectUser(); } else if(String(data?.code)==="404"){ show_toastr(data.status,data.errors,"error"); } },
                error:()=>guardOnce(el,"invite_unavailable","pointerup")
                });
            }catch{ guardOnce(el,"invite_unavailable","pointerup"); }
            });
        });

        // Clipboard helper (click-triggered)
        window.copyToClipboard=(element)=>{try{ const text=element?.id ?? ""; if(!navigator?.clipboard){ throw new Error("Clipboard API unavailable"); } navigator.clipboard.writeText(text).then(()=>{ if(typeof show_toastr==="function") show_toastr("success","Url copied to clipboard","success"); }).catch(()=>{ guardOnce(element||document.body,"copy_unavailable","click"); }); }catch{ guardOnce(element||document.body,"copy_unavailable","click"); }};
        }catch(e){ console.error("Initialization failed",e); }
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
    <li class="breadcrumb-item">
        <a
            id="project-index-link"
            href="{{ $projectIndexUrl }}"
            data-url="{{ $projectIndexUrl }}"
            data-guard-msg="{{ $projectIndexGuardMsg }}"
        >
            {{ __('Project') }}
        </a>
    </li>
    @push(StacksConstants::ADM_SCR_PG)
        <script defer>
            (() => {
                const link = document.getElementById('project-index-link');
                if (!link || link.getAttribute('data-listener-active') === 'true') return;
                link.setAttribute('data-listener-active', 'true');
                link.addEventListener('click', e => {
                    try {
                        const url = link.getAttribute('data-url') || '#';
                        if (url !== '#') return;
                        e.preventDefault();
                        const msg = link.getAttribute('data-guard-msg') || '# ERROR';
                        const hasBootstrap = !!(document.querySelector('link[href*="bootstrap"]') && window.bootstrap);
                        let container = document.getElementById('toast-container');
                        if (!container) {
                            container = document.createElement('div');
                            container.id = 'toast-container';
                            document.body.appendChild(container);
                        }
                        if (hasBootstrap) {
                            const toast = document.createElement('div');
                            toast.className = 'toast';
                            toast.setAttribute('role','alert');
                            toast.setAttribute('aria-live','assertive');
                            toast.setAttribute('aria-atomic','true');
                            const body = document.createElement('div');
                            body.className = 'toast-body';
                            body.textContent = msg;
                            toast.appendChild(body);
                            container.appendChild(toast);
                            bootstrap.Toast.getOrCreateInstance(toast).show();
                        } else {
                            alert(msg);
                        }
                        link.setAttribute('data-failed-route', 'true');
                    } catch (err) {}
                });
            })();
        </script>
    @endpush
    <li class="breadcrumb-item">{{ucwords($project->project_name)}}</li>
@endsection
@section(YieldingConstants::ADM_ACT_BTN)
    <div class="float-end">
        @can('share project')
            <a href="#" class="btn btn-sm btn-primary" data-ajax-popup="true" data-size="md" data-title="{{ __('Shared Project Settings') }}"
               data-url="{{route('projects.copylink.setting.create',[ $project->id])}}"
               data-toggle="tooltip" title="{{ __('Shared project settings') }}">
                <i class="ti ti-share text-white"></i>
            </a>
            {{--            @php $projectID= Crypt::encrypt($project->id); @endphp--}}
            {{--            <a href="#" id="{{ route('projects.link', \Illuminate\Support\Facades\Crypt::encrypt($project->id)) }}" class="btn btn-sm btn-primary btn-icon m-1"--}}
            {{--               onclick="copyToClipboard(this)" data-bs-toggle="tooltip" title="{{__('Click to copy link')}}">--}}
            {{--                <i class="ti ti-link text-white"></i>--}}
            {{--            </a>--}}
        @endcan
        @can('view grant chart')
            <a href="{{ route('projects.gantt',$project->id) }}" class="btn btn-sm btn-primary">
                {{__('Gantt Chart')}}
            </a>
        @endcan
        @if($user?->type!='client' || ($user?->type=='client' ))
            <a href="{{ route('projecttime.tracker',$project->id) }}" class="btn btn-sm btn-primary">
                {{__('Tracker')}}
            </a>
        @endif
        @can('view expense')
            <a href="{{ route('projects.expenses.index',$project->id) }}" class="btn btn-sm btn-primary">
                {{__('Expense')}}
            </a>
        @endcan
        @if($user?->type != 'client')
            @can('view timesheet')
                <a href="{{ route('timesheet.index',$project->id) }}" class="btn btn-sm btn-primary">
                    {{__('Timesheet')}}
                </a>
            @endcan
        @endif
        @can('manage bug report')
            <a href="{{ route('task.bug',$project->id) }}" class="btn btn-sm btn-primary">
                {{__('Bug Report')}}
            </a>
        @endcan
        @can('create project task')
            <a href="{{ route('projects.tasks.index',$project->id) }}" class="btn btn-sm btn-primary">
                {{__('Task')}}
            </a>
        @endcan
        @can('edit project')
            <a href="#" data-size="lg" data-url="{{ route('projects.edit', $project->id) }}" data-ajax-popup="true" data-bs-toggle="tooltip" title="{{__('Edit Project')}}" class="btn btn-sm btn-primary">
                <i class="ti ti-pencil"></i>
            </a>
        @endcan


    </div>
@endsection
@section(YieldingConstants::ADM_CTT)
    <div class="row">
        <div class="col-lg-4 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="row align-items-center justify-content-between">
                        <div class="col-auto mb-3 mb-sm-0">
                            <div class="d-flex align-items-center">
                                <div class="theme-avatar bg-warning">
                                    <i class="ti ti-list"></i>
                                </div>
                                <div class="ms-3">
                                    <small class="text-muted h6">{{__('Total Task')}}</small>
                                    <h6 class="m-0">{{$project_data['task']['total'] }}</h6>
                                </div>
                            </div>
                        </div>
                        <div class="col-auto text-end">
                            <h4 class="m-0">{{ $project_data['task']['done'] }}</h4>
                            <small class="text-muted h6">{{__('Done Task')}}</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-12">
            <div class="card">
                <div class="card-body">
                    <div class="row align-items-center justify-content-between">
                        <div class="col-auto mb-3 mb-sm-0">
                            <div class="d-flex align-items-center">
                                <div class="theme-avatar bg-danger">
                                    <i class="ti ti-report-money"></i>
                                </div>
                                <div class="ms-3">
                                    <small class="text-muted">{{__('Total')}}</small>
                                    <h6 class="m-0">{{__('Budget')}}</h6>
                                </div>
                            </div>
                        </div>
                        <div class="col-auto text-end">
                            <h4 class="m-0">{{ $user?->priceFormat($project->budget)}}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @if($user?->type !='client')
            <div class="col-lg-4 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="row align-items-center justify-content-between">
                        <div class="col-auto mb-3 mb-sm-0">
                            <div class="d-flex align-items-center">
                                <div class="theme-avatar bg-primary">
                                    <i class="ti ti-report-money"></i>
                                </div>
                                <div class="ms-3">
                                    <small class="text-muted">{{__('Total')}}</small>
                                    <h6 class="m-0">{{__('Expense')}}</h6>
                                </div>
                            </div>
                        </div>
                        <div class="col-auto text-end">
                            <h4 class="m-0">{{ $user?->priceFormat($project_data['expense']['total']) }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @else
            <div class="col-lg-4 col-md-6"></div>
        @endif
        <div class="col-lg-4 col-md-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar me-3">
                            <img {{ $project->img_image }} alt="" class="img-user wid-45 rounded-circle">
                        </div>
                        <div class="d-block  align-items-center justify-content-between w-100">
                            <div class="mb-3 mb-sm-0">
                                <h5 class="mb-1"> {{$project->project_name}}</h5>
                                <p class="mb-0 text-sm">
                                    @php
                                        $projectProgress = $project->projectProgress($project,$last_task->id)['percentage'];
                                    @endphp
                                    <div class="progress-wrapper">
                                        <span class="progress-percentage"><small class="font-weight-bold">{{__('Completed:')}} : </small>{{ $projectProgress }}</span>
                                        <div class="progress progress-xs mt-2">
                                            <div class="progress-bar bg-info" role="progressbar" aria-valuenow="{{ $projectProgress }}" aria-valuemin="0" aria-valuemax="100" style="width: {{ $projectProgress }};"></div>
                                        </div>
                                    </div>
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-10">
                            <h4 class="mt-3 mb-1"></h4>
                            <p> {{$project->description }}</p>
                        </div>
                    </div>
                    <div class="card bg-primary mb-0">
                        <div class="card-body">
                            <div class="d-block d-sm-flex align-items-center justify-content-between">
                                <div class="row align-items-center">
                                    <span class="text-white text-sm">{{__('Start Date')}}</span>
                                    <h5 class="text-white text-nowrap">{{ Utility::getDateFormated($project->start_date) }}</h5>
                                </div>
                                <div class="row align-items-center">
                                    <span class="text-white text-sm">{{__('End Date')}}</span>
                                    <h5 class="text-white text-nowrap">{{ Utility::getDateFormated($project->end_date) }}</h5>
                                </div>

                            </div>
                            <div class="row">
                                <span class="text-white text-sm">{{__('Client')}}</span>
                                <h5 class="text-white text-nowrap">{{ (!empty($project->client)?$project->client->name: '-') }}</h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-start">
                        <div class="theme-avatar bg-primary">
                            <i class="ti ti-clipboard-list"></i>
                        </div>
                        <div class="ms-3">
                            <p class="text-muted mb-0">{{__('Last 7 days task done')}}</p>
                            <h4 class="mb-0">{{ $project_data['task_chart']['total'] }}</h4>

                        </div>
                    </div>
                    <div id="task_chart"></div>
                </div>

                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div class="d-flex align-items-center">
                            <span class="text-muted">{{__('Day Left')}}</span>
                        </div>
                        <span>{{ $project_data['day_left']['day'] }}</span>
                    </div>
                    <div class="progress mb-3">
                        <div class="progress-bar bg-primary" style="width: {{ $project_data['day_left']['percentage'] }}%"></div>
                    </div>
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div class="d-flex align-items-center">

                            <span class="text-muted">{{__('Open Task')}}</span>
                        </div>
                        <span>{{ $project_data['open_task']['tasks'] }}</span>
                    </div>
                    <div class="progress mb-3">
                        <div class="progress-bar bg-primary" style="width: {{ $project_data['open_task']['percentage'] }}%"></div>
                    </div>
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div class="d-flex align-items-center">
                            <span class="text-muted">{{__('Completed Milestone')}}</span>
                        </div>
                        <span>{{ $project_data['milestone']['total'] }}</span>
                    </div>
                    <div class="progress mb-3">
                        <div class="progress-bar bg-primary" style="width: {{ $project_data['milestone']['percentage'] }}%"></div>
                    </div>
                </div>
            </div>

        </div>
        <div class="col-lg-4 col-md-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-start">
                        <div class="theme-avatar bg-primary">
                            <i class="ti ti-clipboard-list"></i>
                        </div>
                        <div class="ms-3">
                            <p class="text-muted mb-0">{{__('Last 7 days hours spent')}}</p>
                            <h4 class="mb-0">{{ $project_data['timesheet_chart']['total'] }}</h4>

                        </div>
                    </div>
                    <div id="timesheet_chart"></div>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div class="d-flex align-items-center">
                            <span class="text-muted">{{__('Total project time spent')}}</span>
                        </div>
                        <span>{{ $project_data['time_spent']['total'] }}</span>
                    </div>
                    <div class="progress mb-3">
                        <div class="progress-bar bg-primary" style="width: {{ $project_data['time_spent']['percentage'] }}%"></div>
                    </div>
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div class="d-flex align-items-center">

                            <span class="text-muted">{{__('Allocated hours on task')}}</span>
                        </div>
                        <span>{{ $project_data['task_allocated_hrs']['hrs'] }}</span>
                    </div>
                    <div class="progress mb-3">
                        <div class="progress-bar bg-primary" style="width: {{ $project_data['task_allocated_hrs']['percentage'] }}%"></div>
                    </div>
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div class="d-flex align-items-center">
                            <span class="text-muted">{{__('User Assigned')}}</span>
                        </div>
                        <span>{{ $project_data['user_assigned']['total'] }}</span>
                    </div>
                    <div class="progress mb-3">
                        <div class="progress-bar bg-primary" style="width: {{ $project_data['user_assigned']['percentage'] }}%"></div>
                    </div>
                </div>
            </div>

        </div>
        <div class="col-lg-6 col-md-6">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-center justify-content-between">
                        <h5>{{__('Members')}}</h5>
                        @can('edit project')
                            <div class="float-end">
                                <a href="#" data-size="lg" data-url="{{ route('invite.project.member.view', $project->id) }}" data-ajax-popup="true" data-bs-toggle="tooltip" title="" class="btn btn-sm btn-primary" data-bs-original-title="{{__('Add Member')}}">
                                    <i class="ti ti-plus"></i>
                                </a>
                            </div>
                        @endcan
                    </div>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush list" id="project_users">
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-lg-6 col-md-6">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-center justify-content-between">
                        <h5>{{__('Milestones')}} ({{count($project->milestones)}})</h5>
                        @can('create milestone')
                            <div class="float-end">
                                <a href="#" data-size="md" data-url="{{ route('project.milestone', $project->id) }}" data-ajax-popup="true" data-bs-toggle="tooltip" title="" class="btn btn-sm btn-primary" data-bs-original-title="{{__('Create Milestone')}}">
                                    <i class="ti ti-plus"></i>
                                </a>
                            </div>
                        @endcan
                    </div>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        @if($project->milestones->count() > 0)
                            @foreach($project->milestones as $milestone)
                                <li class="list-group-item px-0">
                                    <div class="row align-items-center justify-content-between">
                                        <div class="col-sm-auto mb-3 mb-sm-0">
                                            <div class="d-flex align-items-center">
                                                <div class="div">
                                                    <h6 class="m-0">{{ $milestone->title }}
                                                        <span class="badge-xs badge bg-{{\App\Models\Project::$status_color[$milestone->status]}} p-2 px-3 rounded">{{ __(\App\Models\Project::$project_status[$milestone->status]) }}</span>
                                                    </h6>
                                                    <small class="text-muted">{{ $milestone->tasks->count().' '. __('Tasks') }}</small>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-sm-auto text-sm-end d-flex align-items-center">
                                            @can('view milestone')
                                                <div class="action-btn bg-warning ms-2">
                                                    <a href="#" data-size="lg" data-url="{{ route('project.milestone.show',$milestone->id) }}" data-ajax-popup="true" data-bs-toggle="tooltip" title="{{__('View')}}" class="btn btn-sm">
                                                        <i class="ti ti-eye text-white"></i>
                                                    </a>
                                                </div>
                                            @endcan
                                            @can('edit milestone')
                                                <div class="action-btn bg-info ms-2">
                                                    <a href="#" data-size="md" data-url="{{ route('project.milestone.edit',$milestone->id) }}" data-ajax-popup="true" data-bs-toggle="tooltip" title="{{__('Edit')}}" data-title="{{__('Edit Milestone')}}"class="btn btn-sm">
                                                        <i class="{{ VC::TI_PC_WT }}"></i>
                                                    </a>
                                                </div>
                                            @endcan
                                            @can('delete milestone')
                                                <div class="action-btn bg-danger ms-2">
                                                    {!! Collective\Html\FormFacade::open(['method' => 'DELETE', 'route' => ['project.milestone.destroy', $milestone->id]]) !!}
                                                    <a href="#" class="mx-3 btn btn-sm align-items-center bs-pass-para" data-bs-toggle="tooltip" title="{{__('Delete')}}"><i class="ti ti-trash text-white"></i></a>

                                                    {!! Collective\Html\FormFacade::close() !!}
                                                </div>
                                            @endcan
                                        </div>
                                    </div>
                                </li>
                            @endforeach
                        @else
                            <div class="py-5">
                                <h6 class="h6 text-center">{{__('No Milestone Found.')}}</h6>
                            </div>
                        @endif
                    </ul>

                </div>
            </div>
        </div>
        @can('view activity')
            <div class="col-xl-6">
                <div class="card activity-scroll">
                    <div class="card-header">
                        <h5>{{__('Activity Log')}}</h5>
                        <small>{{__('Activity Log of this project')}}</small>
                    </div>
                    <div class="card-body vertical-scroll-cards">
                        @foreach($project->activities as $activity)
                            <div class="card p-2 mb-2">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center">
                                        <div class="theme-avatar bg-primary">
                                            <i class="ti {{$activity->logIcon($activity->log_type)}}"></i>
                                        </div>
                                        <div class="ms-3">
                                            <h6 class="mb-0">{{ __($activity->log_type) }}</h6>
                                            <p class="text-muted text-sm mb-0">{!! $activity->getRemark() !!}</p>
                                        </div>
                                    </div>
                                    <p class="text-muted text-sm mb-0">{{$activity->created_at->diffForHumans()}}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endcan
        <div class="col-lg-6 col-md-6">
            <div class="card activity-scroll">
                <div class="card-header">
                    <h5>{{__('Attachments')}}</h5>
                    <small>{{__('Attachment that uploaded in this project')}}</small>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        @if($project->projectAttachments()->count() > 0)
                            @foreach($project->projectAttachments() as $attachment)
                                <li class="list-group-item px-0">
                                    <div class="row align-items-center justify-content-between">
                                        <div class="col mb-3 mb-sm-0">
                                            <div class="d-flex align-items-center">
                                                <div class="div">
                                                    <h6 class="m-0">{{ $attachment->name }}</h6>
                                                    <small class="text-muted">{{ $attachment->file_size }}</small>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-auto text-sm-end d-flex align-items-center">
                                            <div class="action-btn bg-info ms-2">
                                                <a href="{{asset(Storage::url('tasks/'.$attachment->file))}}"  data-bs-toggle="tooltip" title="{{__('Download')}}" class="btn btn-sm" download>
                                                    <i class="ti ti-download text-white"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                            @endforeach
                        @else
                            <div class="py-5">
                                <h6 class="h6 text-center">{{__('No Attachments Found.')}}</h6>
                            </div>
                        @endif
                    </ul>

                </div>
            </div>
        </div>
    </div>
@endsection
