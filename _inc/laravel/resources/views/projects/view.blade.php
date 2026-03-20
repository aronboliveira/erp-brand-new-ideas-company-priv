@php
$lang = Utility::fetchUserLang();

        if (!function_exists('resolveRoute')) {
        function resolveRoute($base, $kebabFallback = true) {
            if (Route::has($base)) return $base;
            if ($kebabFallback) {
                $kebab = Str::kebab($base);
                if (Route::has($kebab)) return $kebab;
            }
            return null;
        }
    }

        if (!function_exists('safeRoute')) {
        function safeRoute($routeName, $params = []) {
            $resolved = resolveRoute($routeName);
            if (!$resolved || (is_array($params) && empty($params[0]))) return '#';
            try {
                return route($resolved, $params);
            } catch (\Exception $e) {
                return '#';
            }
        }
    }
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@if(!empty($project))
    @php
        $projectName = data_get($project, 'project_name') ?: data_get($project, 'name') ?: __('No project name found.');
@endphp
    @section(YieldingConstants::ADM_PG_TTL)
        {{$projectName}}
    @endsection
    @push(StacksConstants::ADM_SCR_PG)
            <script async>
          (() => {
              if (!window.translations) {
  window.translations = {};
}
const t = {
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
            (()=>{
                const errFb="# ERROR";
                const dataClientLocalized="data-client-localized";
                const dataGuardMsg="data-guard-msg";
                const DATA_LISTENER_ADDED="data-listener-added";
                const DATA_RENDERED="data-chart-rendered";

                const getMsg=(el,msgKey)=>{let msg=errFb; if(el?.getAttribute("data-sv-localized")==="true"||el?.getAttribute(dataClientLocalized)==="true") msg=el.getAttribute(dataGuardMsg)||errFb; else {let lang=(window.sessionStorage.getItem("erp-np-lang")||document.documentElement.lang||"en").toLowerCase().replace(/_/g,"-"); lang=lang==="pt-br"?lang:lang.slice(0,2); msg=window.translations?.[lang]?.[msgKey]||el?.getAttribute(dataGuardMsg)||window.translations?.en?.[msgKey]||errFb; if(msg!==errFb){el?.setAttribute(dataGuardMsg,msg); el?.setAttribute(dataClientLocalized,"true");}} return msg;};

                const showError=(el,key,ev="click")=>{const message=getMsg(el||document.body,key); const hasBs=document.querySelector('link[href*="bootstrap"]')&&window.bootstrap?.Toast; const id="error-toast"; if(hasBs){if(!document.querySelector("#"+id)){const t=document.createElement("div"); t.id=id; t.className="toast align-items-center text-bg-danger border-0"; t.setAttribute("role","alert"); t.setAttribute("aria-live","assertive"); t.setAttribute("aria-atomic","true"); t.innerHTML=`<div class="{{ VC::DFL }}"><div class="toast-body">${message}</div><button type="button" class="{{ VC::BT_CL }} btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="{{ __('Close') }}"></button></div>`; document.body.appendChild(t);} const toastEl=document.querySelector("#"+id); const onceHandler=()=>new bootstrap.Toast(toastEl).show(); if(!toastEl.getAttribute(DATA_LISTENER_ADDED)){ toastEl.setAttribute(DATA_LISTENER_ADDED,"true"); const mo=new MutationObserver((_,o)=>{ if(!document.body.contains(toastEl)){ toastEl.removeEventListener(ev,onceHandler); o.disconnect(); }}); mo.observe(document.body,{childList:true,subtree:true}); } document.addEventListener(ev,onceHandler,{once:true}); } else { const onceHandler=()=>alert(message); document.addEventListener(ev,onceHandler,{once:true}); }};

                const guardOnce=(el,key,ev="pointerup")=>{ if(!el||el.getAttribute(DATA_LISTENER_ADDED)==="true") return; const handler=()=>showError(el,key,ev); el.addEventListener(ev,handler,{once:true}); el.setAttribute(DATA_LISTENER_ADDED,"true"); const mo=new MutationObserver((_,o)=>{ if(!document.body.contains(el)){ el.removeEventListener(ev,handler); o.disconnect(); }}); mo.observe(document.body,{childList:true,subtree:true}); };

                const renderChart=(selector,options,key)=>{try{ if(typeof ApexCharts==="undefined"){
                        if (
                            window.location.hostname === "localhost" ||
                            window.location.hostname === "127.0.0.1"
                        ) console.error("ApexCharts unavailable"); guardOnce(document.querySelector(selector)||document.body,key,"click"); return; } const el=document.querySelector(selector); if(!el){ guardOnce(document.body,key,"click"); return; } if(el.getAttribute(DATA_RENDERED)==="true") return; const chart=new ApexCharts(el,options); chart.render(); el.setAttribute(DATA_RENDERED,"true"); }catch{ guardOnce(document.querySelector(selector)||document.body,key,"click"); }};

                const routeGuard=(element)=>{ const url=element?.getAttribute?.("data-url"); const href=element?.getAttribute?.("action")||element?.getAttribute?.("href"); return (!url||url==="#") && (!href||href==="#"); };

                const loadProjectUser=()=>{const $main=$("#project_users"); const el=$main.get(0); try{ if(routeGuard(el)){ guardOnce(el,"users_load_unavailable"); return; } $.ajax({ url:'{{ route(ViewsConstants::PRJ.'.user') }}', data:{ project_id:'{{$project->id}}' }, beforeSend:()=>{ $('#project_users').html('<tr><th colspan="2" class="h6 {{ VC::TXCT }} pt-5">{{__("Loading...")}}</th></tr>'); }, success:(data)=>{ $main.html(data?.html ?? ""); $('[id^=fire-modal]').remove(); }, error:()=>guardOnce(el,"users_load_unavailable") }); }catch{ guardOnce(el,"users_load_unavailable"); }};

                try{
                if(typeof $==="undefined"){
                    if (
                        window.location.hostname === "localhost" ||
                        window.location.hostname === "127.0.0.1"
                    ) console.error("jQuery unavailable");
                    return;
                }

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
                        const endpoint=$(el).get(0).getAttribute("data-url") && $(el).get(0).getAttribute("data-url")!=="#" ? $(el).get(0).getAttribute("data-url") : '{{ route(ViewsConstants::PRJ . ".invite.user.member") }}';
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
                window.copyToClipboard=(element)=>{try{ const text=element?.id ?? ""; if(!navigator?.clipboard){ throw new Error("Clipboard API unavailable"); } navigator.clipboard.writeText(text).then(()=>{ if(typeof show_toastr==="function") show_toastr("success","{{ __('URL copied to clipboard') }}","success"); }).catch(()=>{ guardOnce(element||document.body,"copy_unavailable","click"); }); }catch{ guardOnce(element||document.body,"copy_unavailable","click"); }};
                }catch(e){
                    if (
                        window.location.hostname === "localhost" ||
                        window.location.hostname === "127.0.0.1"
                    ) {
                        console.error("Initialization failed", e);
                    }
                 }
            })();
        </script>
    @endpush
    @section(YieldingConstants::ADM_BDC)
        <li class="{{ VC::BCI }}">
            <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
            {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}
            id="breadcrumb-dashboard-link"
            data-route-guard>
                {{ __('Dashboard') }}
            </a>
        </li>
        <li class="{{ VC::BCI }}">
            <a
                id="project-index-link"
                href="{{ safeRoute(ViewsConstants::PRJ . '.index') }}"
                data-url="{{ safeRoute(ViewsConstants::PRJ . '.index') }}"
                data-guard-msg="{{ base64_encode(Utility::fetchLinkMessage($lang, ViewsConstants::PRJ, 'project_index_route_unavailable') ?? __('Project index route is unavailable. Please contact support.')) }}"
                data-route-guard
            >
                {{ __('Project') }}
            </a>
        </li>
        <li class="{{ VC::BCI }}">{{$projectName}}</li>
    @endsection
    @section(YieldingConstants::ADM_ACT_BTN)
        <div class="{{ VC::FEND }}">
            @can('share project')
                @php
                    try {
                        $projectId = data_get($project, 'id');
                        $sharedProjUrl = safeRoute(ViewsConstants::PRJ.'.copy_link.setting.create', [$projectId]);
                        $sharedProjMsg = Utility::fetchLinkMessage($lang, ViewsConstants::PRJ, 'create_shared_project_setting_unavailable')
                                      ?? __('Shared project settings route unavailable. Please contact support.');
                    } catch (\Throwable $e) {
                        \Log::error('projects/view — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                    }
@endphp
                <a href="{{ $sharedProjUrl }}"
                id="shared-project-settings-create-link"
                class="{{ VC::BT_SM_PM }}"
                data-ajax-popup="true"
                data-size="md"
                data-title="{{ __('Shared Project Settings') }}"
                data-url="{{ $sharedProjUrl }}"
                data-guard-msg="{{ base64_encode($sharedProjMsg) }}"
                data-bs-toggle="tooltip"
                title="{{ __('Shared project settings') }}"
                data-route-guard>
                    <i class="{{ VC::TI }} {{ VC::TI }}-share {{ VC::TXT_WT }}"></i>
                </a>
            @endcan
            @can('view grant chart')
                @php
                    try {
                        $projectId = data_get($project, 'id');
                        $ganttUrl = safeRoute(ViewsConstants::PRJ.'.gantt', [$projectId]);
                        $ganttMsg = Utility::fetchLinkMessage($lang, ViewsConstants::PRJ, 'view_project_gantt_unavailable')
                                 ?? __('Gantt chart route unavailable. Please contact support.');
                    } catch (\Throwable $e) {
                        \Log::error('projects/view — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                    }
@endphp
                <a href="{{ $ganttUrl }}"
                id="project-gantt-link"
                class="{{ VC::BT_SM_PM }}"
                data-url="{{ $ganttUrl }}"
                data-guard-msg="{{ base64_encode($ganttMsg) }}"
                data-route-guard>
                    {{ __('Gantt Chart') }}
                </a>
            @endcan
            @php
                try {
                    $projectId = data_get($project, 'id');
                    $trackerUrl = safeRoute(ViewsConstants::PRJ.'.time.tracker', [$projectId]);
                    $trackerMsg = Utility::fetchLinkMessage($lang, ViewsConstants::PRJ, 'view_project_time_tracker_unavailable')
                               ?? __('Time tracker route unavailable. Please contact support.');
                } catch (\Throwable $e) {
                    \Log::error('projects/view — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                }
@endphp
            <a href="{{ $trackerUrl }}"
            id="project-time-tracker-link"
            class="{{ VC::BT_SM_PM }}"
            data-url="{{ $trackerUrl }}"
            data-guard-msg="{{ base64_encode($trackerMsg) }}"
            data-route-guard>
                {{ __('Tracker') }}
            </a>
            @can('view expense')
                @php
                    try {
                        $projectId = data_get($project, 'id');
                        $expenseUrl = safeRoute(ViewsConstants::PRJ_EXP.'.index', [$projectId]);
                        $expenseMsg = Utility::fetchLinkMessage($lang, ViewsConstants::PRJ_EXP, 'view_project_expense_unavailable')
                                   ?? __('Expense route unavailable. Please contact support.');
                    } catch (\Throwable $e) {
                        \Log::error('projects/view — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                    }
@endphp
                <a href="{{ $expenseUrl }}"
                id="project-expense-index-link"
                class="{{ VC::BT_SM_PM }}"
                data-url="{{ $expenseUrl }}"
                data-guard-msg="{{ base64_encode($expenseMsg) }}"
                data-route-guard>
                    {{ __('Expense') }}
                </a>
            @endcan
            @if ($user?->{UsersConstants::COL_TP} !== PermissionsConstants::CL)
                @can('view timesheet')
                    @php
                        try {
                            $projectId = data_get($project, 'id');
                            $timesheetUrl = safeRoute(ViewsConstants::TMS.'.index', [$projectId]);
                            $timesheetMsg = Utility::fetchLinkMessage($lang, ViewsConstants::TMS, 'open_timesheet_route_unavailable')
                                         ?? __('Timesheet route unavailable. Please contact support.');
                        } catch (\Throwable $e) {
                            \Log::error('projects/view — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                        }
@endphp
                    <a href="{{ $timesheetUrl }}"
                    id="timesheet-index-link"
                    class="{{ VC::BT_SM_PM }}"
                    data-url="{{ $timesheetUrl }}"
                    data-guard-msg="{{ base64_encode($timesheetMsg) }}"
                    data-route-guard>
                        {{ __('Timesheet') }}
                    </a>
                @endcan
            @endif
            @can('manage bug report')
                @php
                    try {
                        $projectId = data_get($project, 'id');
                        $bugReportUrl = safeRoute(ViewsConstants::PRJ_TSK_BUG.'.index', [$projectId]);
                        $bugReportMsg = Utility::fetchLinkMessage($lang, ViewsConstants::PRJ_TSK_BUG, 'open_bug_report_route_unavailable')
                                     ?? __('Bug report route unavailable. Please contact support.');
                    } catch (\Throwable $e) {
                        \Log::error('projects/view — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                    }
@endphp
                <a href="{{ $bugReportUrl }}"
                id="project-bug-report-index-link"
                class="{{ VC::BT_SM_PM }}"
                data-url="{{ $bugReportUrl }}"
                data-guard-msg="{{ base64_encode($bugReportMsg) }}"
                data-route-guard>
                    {{ __('Bug Report') }}
                </a>
            @endcan
            @can('create project task')
                @php
                    try {
                        $projectId = data_get($project, 'id');
                        $taskUrl = safeRoute(ViewsConstants::PRJ_TSK_C.'.index', [$projectId]);
                        $taskMsg = Utility::fetchLinkMessage($lang, ViewsConstants::PRJ_TSK_C, 'open_task_route_unavailable')
                                ?? __('Task route unavailable. Please contact support.');
                    } catch (\Throwable $e) {
                        \Log::error('projects/view — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                    }
@endphp
                <a href="{{ $taskUrl }}"
                id="project-task-index-link"
                class="{{ VC::BT_SM_PM }}"
                data-url="{{ $taskUrl }}"
                data-guard-msg="{{ base64_encode($taskMsg) }}"
                data-route-guard>
                    {{ __('Task') }}
                </a>
            @endcan
        </div>
    @endsection
                    $projectTaskIndexResolvedName = Route::has($projectTaskIndexBaseName)
                        ? $projectTaskIndexBaseName
                        : (Route::has($projectTaskIndexKebabName) ? $projectTaskIndexKebabName : null);
                    $projectId                    = isset($project) && !empty($project->id) ? $project->id : null;
                    $projectTaskIndexUrl          = ($projectTaskIndexResolvedName && $projectId) ? route($projectTaskIndexResolvedName, $projectId) : '#';
                    $projectTaskIndexGuardMsg     = Utility::fetchLinkMessage($lang, ViewsConstants::PRJ_TSK_C, 'open_task_route_unavailable') ?? 'Open task route is unavailable. Please contact technical support or your domain administrator.';
                    $projectTaskIndexLinkId       = 'project-task-index-link';
                @endphp
                <a href="{{ $projectTaskIndexUrl }}"
                id="{{ $projectTaskIndexLinkId }}"
                class="{{ VC::BT_SM_PM }}"
                data-url="{{ $projectTaskIndexUrl }}"
                data-guard-msg="{{ base64_encode($projectTaskIndexGuardMsg) }}">
                    {{ __('Task') }}
                </a>
                @push(StacksConstants::ADM_SCR_PG)
                    <script defer>
                        (() => {
                            try {
                                const l = document.getElementById('{{ $projectTaskIndexLinkId }}');
                                if (!l || l.getAttribute('data-listener-active') === 'true') return;
                                l.setAttribute('data-listener-active', 'true');
                                l.addEventListener('click', e => {
                                    try {
                                        const href = l.getAttribute('href') || '#';
                                        const url = l.getAttribute('data-url') || href || '#';
                                        if (href !== '#' || url !== '#') return;
                                        e.preventDefault();
                                        const msg = l.getAttribute('data-guard-msg') || 'Open task route is unavailable. Please contact technical support or your domain administrator.';
                                        (window.RouteGuard?.showToast || (m => alert(m)))(msg);
                                        l.setAttribute('data-failed-route', 'true');
                                    } catch (err) {}
                                });
                            } catch (error) {}
                        })();
                    </script>
                @endpush
            @endcan
            @can('edit project')
                @php
                    try {
                        $projectEditBaseName     = ViewsConstants::PRJ.'.edit';
                        $projectEditKebabName    = Str::kebab($projectEditBaseName);
                        $projectEditResolvedName = Route::has($projectEditBaseName)
                            ? $projectEditBaseName
                            : (Route::has($projectEditKebabName) ? $projectEditKebabName : null);
                        $projectId               = isset($project) && !empty($project->id) ? $project->id : null;
                        $projectEditUrl          = ($projectEditResolvedName && $projectId) ? route($projectEditResolvedName, $projectId) : '#';
                        $projectEditGuardMsg     = Utility::fetchLinkMessage($lang, ViewsConstants::PRJ, 'project_edit_route_unavailable') ?? 'Edit project route is unavailable. Please contact technical support or your domain administrator.';
                        $projectEditLinkId       = 'project-edit-link';
                        $projectEditTitle        = __('Edit Project');
                    } catch (\Throwable $e) {
                        \Log::error('projects/view — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                    }
@endphp
                <a href="{{ $projectEditUrl }}"
                id="{{ $projectEditLinkId }}"
                class="{{ VC::BT_SM_PM }}"
                data-ajax-popup="true"
                data-size="lg"
                data-title="{{ $projectEditTitle }}"
                data-url="{{ $projectEditUrl }}"
                data-guard-msg="{{ base64_encode($projectEditGuardMsg) }}"
                data-bs-toggle="tooltip"
                title="{{ $projectEditTitle }}">
                    <i class="{{ VC::TI_PC }}"></i>
                </a>
                @push(StacksConstants::ADM_SCR_PG)
                    <script defer>
                        (() => {
                            try {
                                const l = document.getElementById('{{ $projectEditLinkId }}');
                                if (!l || l.getAttribute('data-listener-active') === 'true') return;
                                l.setAttribute('data-listener-active', 'true');
                                l.addEventListener('click', e => {
                                    try {
                                        const href = l.getAttribute('href') || '#';
                                        const url = l.getAttribute('data-url') || href || '#';
                                        if (href !== '#' || url !== '#') return;
                                        e.preventDefault();
                                        const msg = l.getAttribute('data-guard-msg') || 'Edit project route is unavailable. Please contact technical support or your domain administrator.';\n                                        (window.RouteGuard?.showToast || (m => alert(m)))(msg);
                                        l.setAttribute('data-failed-route', 'true');
                                    } catch (err) {}
                                });
                            } catch (error) {}
                        })();
                    </script>
                @endpush
            @endcan
        </div>
    @endsection
    @section(YieldingConstants::ADM_CTT)
        <div class="row">
            <div class="col-lg-4 {{ VC::CM6 }}">
                <div class="{{ VC::CD }}">
                    <div class="{{ VC::CD_BD }}">
                        <div class="{{ VC::R_ALC }} {{ VC::JCB }}">
                            <div class="{{ VC::C_AT }} {{ VC::MB3 }} mb-sm-0">
                                <div class="{{ VC::DFL_AIC }}">
                                    <div class="theme-avatar bg-warning">
                                        <i class="{{ VC::TI_LT }}"></i>
                                    </div>
                                    <div class="ms-3">
                                        <small class="{{ VC::TXT_MT }} {{ VC::H6 }}">{{ __('Total Task') }}</small>
                                        <h6 class="m-0">{{ $project_data['task']['total'] }}</h6>
                                    </div>
                                </div>
                            </div>
                            <div class="{{ VC::C_AT }} text-end">
                                <h4 class="m-0">{{ $project_data['task']['done'] }}</h4>
                                <small class="{{ VC::TXT_MT }} {{ VC::H6 }}">{{ __('Done Task') }}</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="{{ VC::CL4 }} {{ VC::CM12 }}">
                <div class="{{ VC::CD }}">
                    <div class="{{ VC::CD_BD }}">
                        <div class="{{ VC::R_ALC }} {{ VC::JCB }}">
                            <div class="{{ VC::C_AT }} {{ VC::MB3 }} mb-sm-0">
                                <div class="{{ VC::DFL_AIC }}">
                                    <div class="theme-avatar bg-danger">
                                        <i class="{{ VC::TI_RPT_MN }}"></i>
                                    </div>
                                    <div class="ms-3">
                                        <small class="{{ VC::TXT_MT }}">{{ __('Total') }}</small>
                                        <h6 class="m-0">{{ __('Budget') }}</h6>
                                    </div>
                                </div>
                            </div>
                            <div class="{{ VC::C_AT }} text-end">
                                <h4 class="m-0">{{ !empty($project->budget) ? $user?->priceFormat($project->budget) : __('No budget found')}}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @if ($user?->{UsersConstants::COL_TP} !== PermissionsConstants::CL)
                <div class="col-lg-4 {{ VC::CM6 }}">
                    <div class="{{ VC::CD }}">
                        <div class="{{ VC::CD_BD }}">
                            <div class="{{ VC::RW }} {{ VC::ALC }} {{ VC::JCB }}">
                                <div class="{{ VC::C_AT }} {{ VC::MB3 }} mb-sm-0">
                                    <div class="{{ VC::DFL_AIC }}">
                                        <div class="theme-avatar {{ VC::BG_P }}">
                                            <i class="{{ VC::TI }} {{ VC::TI }}-report-money"></i>
                                        </div>
                                        <div class="ms-3">
                                            <small class="{{ VC::TXT_MT }}">{{ __('Total') }}</small>
                                            <h6 class="m-0">{{ __('Expense') }}</h6>
                                        </div>
                                    </div>
                                </div>
                                <div class="{{ VC::C_AT }} text-end">
                                    <h4 class="m-0">{{ $user?->priceFormat($project_data['expense']['total']) }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <div class="col-lg-4 {{ VC::CM6 }}"></div>
            @endif
            <div class="{{ VC::CLM4 }}">
                <div class="{{ VC::CD }}">
                    <div class="{{ VC::CD_BD }}">
                        <div class="{{ VC::DFL_AIC }}">
                            <div class="{{ VC::AV }} {{ VC::ME3 }}">
                                <img {!! !empty($project->img_image) ? $project->img_image : '' !!} alt="" class="img-user wid-45 rounded-circle">
                            </div>
                            <div class="{{ VC::DBL }} d-sm-flex {{ VC::ALC }} {{ VC::JCB }} w-100">
                                <div class="{{ VC::MB3 }} mb-sm-0">
                                    <h5 class="{{ VC::MB1 }}">{{ $project->project_name }}</h5>
                                    @php
                                        $projectProgress ??= 0;
                                        try {
                                            if (isset($project, $last_task) && method_exists($project, 'projectProgress')) {
                                                $progressData = $project->projectProgress($project, $last_task->id);
                                                $projectProgress = $progressData['percentage'] ?? 0;
                                            }
                                        } catch (Exception $e) {
                                            Log::error("Progress calculation failed: " . $e->getMessage());
                                        }
@endphp
                                    <div class="progress-wrapper">
                                        <span class="{{ VC::PG_SM_BL }}">
                                            <small class="font-weight-bold">{{ __('Completed:') }} :</small>
                                            {{ $projectProgress }}
                                        </span>
                                        <div class="{{ VC::PG }} progress-xs {{ VC::MT1 }}">
                                            <div
                                                class="progress-bar bg-info"
                                                role="progressbar"
                                                aria-valuenow="{{ $projectProgress }}"
                                                aria-valuemin="0"
                                                aria-valuemax="100"
                                                style="width: {{ $projectProgress }};">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="{{ VC::RW }}">
                            <div class="{{ VC::CS10 }}">
                                <h4 class="{{ VC::MT3 }} {{ VC::MB1 }}"></h4>
                                <p>{{ !empty($project->description) ?$project->description : __('No description found.') }}</p>
                            </div>
                        </div>
                        <div class="{{ VC::CD }} {{ VC::BG_P }} {{ VC::MB0 }}">
                            <div class="{{ VC::CD_BD }}">
                                <div class="d-block d-sm-flex {{ VC::ALC }} {{ VC::JCB }}">
                                    <div class="{{ VC::RW }} {{ VC::ALC }}">
                                        <span class="{{ VC::TXT_WT }} {{ VC::TXSM }}">{{ __('Start Date') }}</span>
                                        <h5 class="{{ VC::TXT_WT }} text-nowrap">{{ method_exists(Utility::class, 'getDateFormated') && !empty($project->start_date) ? Utility::getDateFormated($project->start_date) : __('No start date found.') }}</h5>
                                    </div>
                                    <div class="{{ VC::RW }} {{ VC::ALC }}">
                                        <span class="{{ VC::TXT_WT }} {{ VC::TXSM }}">{{ __('End Date') }}</span>
                                        <h5 class="{{ VC::TXT_WT }} text-nowrap">{{ method_exists(Utility::class, 'getDateFormated') && !empty($project->end_date) ? Utility::getDateFormated($project->end_date) : __('No end date found.') }}</h5>
                                    </div>
                                </div>
                                <div class="{{ VC::RW }}">
                                    <span class="{{ VC::TXT_WT }} {{ VC::TXSM }}">{{ __('Client') }}</span>
                                    <h5 class="{{ VC::TXT_WT }} text-nowrap">{{ !empty($project->client) ? $project->client->name : __('No client found.') }}</h5>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="{{ VC::CLM4 }}">
                <div class="{{ VC::CD }}">
                    <div class="{{ VC::CD_BD }}">
                        <div class="{{ VC::DFL }} align-items-start">
                            <div class="theme-avatar {{ VC::BG_P }}">
                                <i class="{{ VC::TI }} {{ VC::TI }}-clipboard-list"></i>
                            </div>
                            <div class="{{ VC::MS2 }}">
                                <p class="{{ VC::TXT_MT }} {{ VC::MB0 }}">{{ __('Last 7 days task done') }}</p>
                                <h4 class="{{ VC::MB0 }}">{{ data_get($project_data, 'task_chart.total', __('No total for task chart found.')) }}</h4>
                            </div>
                        </div>
                        <div id="task_chart"></div>
                    </div>
                    <div class="{{ VC::CD_BD }}">
                        <div class="{{ VC::DFL_AIC_JCB }} {{ VC::MB1 }}">
                            <div class="{{ VC::DFL_AIC }}">
                                <span class="{{ VC::TXT_MT }}">{{ __('Day Left') }}</span>
                            </div>
                            <span>{{ data_get($project_data, 'day_left.day', __('No date for days left found.')) }}</span>
                        </div>
                        <div class="{{ VC::PG }} {{ VC::MB3 }}">
                            <div class="progress-bar {{ VC::BG_P }}" style="width: {{ data_get($project_data, 'day_left.percentage', __('No percentage for days left found.')) }}%"></div>
                        </div>
                        <div class="{{ VC::DFL_AIC_JCB }} {{ VC::MB1 }}">
                            <div class="{{ VC::DFL_AIC }}">
                                <span class="{{ VC::TXT_MT }}">{{ __('Open Task') }}</span>
                            </div>
                            <span>{{ data_get($project_data, 'open_task.tasks', __('No open task found.')) }}</span>
                        </div>
                        <div class="{{ VC::PG }} {{ VC::MB3 }}">
                            <div class="progress-bar {{ VC::BG_P }}" style="width: {{ data_get($project_data, 'open_task.percentage', __('No percentage for open task found.')) }}%"></div>
                        </div>
                        <div class="{{ VC::DFL_AIC_JCB }} {{ VC::MB1 }}">
                            <div class="{{ VC::DFL_AIC }}">
                                <span class="{{ VC::TXT_MT }}">{{ __('Completed Milestone') }}</span>
                            </div>
                            <span>{{ data_get($project_data, 'milestone.total', __('No completed milestone found.')) }}</span>
                        </div>
                        <div class="{{ VC::PG }} {{ VC::MB3 }}">
                            <div class="progress-bar {{ VC::BG_P }}" style="width: {{ data_get($project_data, 'milestone.percentage', __('No percentage for completed milestone found.')) }}%"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="{{ VC::CLM4 }}">
                <div class="{{ VC::CD }}">
                    <div class="{{ VC::CD_BD }}">
                        <div class="{{ VC::DFL }} align-items-start">
                            <div class="theme-avatar {{ VC::BG_P }}">
                                <i class="{{ VC::TI }} {{ VC::TI }}-clipboard-list"></i>
                            </div>
                            <div class="{{ VC::MS2 }}">
                                <p class="{{ VC::TXT_MT }} {{ VC::MB0 }}">{{ __('Last 7 days hours spent') }}</p>
                                <h4 class="{{ VC::MB0 }}">{{ data_get($project_data, 'timesheet_chart.total', __('No timesheet chart total found.')) }}</h4>
                            </div>
                        </div>
                        <div id="timesheet_chart"></div>
                    </div>
                    <div class="{{ VC::CD_BD }}">
                        <div class="{{ VC::DFL_AIC_JCB }} {{ VC::MB1 }}">
                            <div class="{{ VC::DFL_AIC }}">
                                <span class="{{ VC::TXT_MT }}">{{ __('Total project time spent') }}</span>
                            </div>
                            <span>{{ data_get($project_data, 'time_spent.total', __('No total project time spent found.')) }}</span>
                        </div>
                        <div class="{{ VC::PG }} {{ VC::MB3 }}">
                            <div class="progress-bar {{ VC::BG_P }}" style="width: {{ data_get($project_data, 'time_spent.percentage', __('No percentage for total project time spent found.')) }}%"></div>
                        </div>
                        <div class="{{ VC::DFL_AIC_JCB }} {{ VC::MB1 }}">
                            <div class="{{ VC::DFL_AIC }}">
                                <span class="{{ VC::TXT_MT }}">{{ __('Allocated hours on task') }}</span>
                            </div>
                            <span>{{ data_get($project_data, 'task_allocated_hrs.hrs', __('No allocated hours on task found.')) }}</span>
                        </div>
                        <div class="{{ VC::PG }} {{ VC::MB3 }}">
                            <div class="progress-bar {{ VC::BG_P }}" style="width: {{ data_get($project_data, 'task_allocated_hrs.percentage', __('No percentage for allocated hours on task found.')) }}%"></div>
                        </div>
                        <div class="{{ VC::DFL_AIC_JCB }} {{ VC::MB1 }}">
                            <div class="{{ VC::DFL_AIC }}">
                                <span class="{{ VC::TXT_MT }}">{{ __('User Assigned') }}</span>
                            </div>
                            <span>{{ data_get($project_data, 'user_assigned.total', __('No user assigned found.')) }}</span>
                        </div>
                        <div class="{{ VC::PG }} {{ VC::MB3 }}">
                            <div class="progress-bar {{ VC::BG_P }}" style="width: {{ data_get($project_data, 'user_assigned.percentage', __('No percentage for user assigned found.')) }}%"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="{{ VC::CLM6 }}">
                <div class="{{ VC::CD }}">
                    <div class="{{ VC::CD_HD }}">
                        <div class="{{ VC::DFL_AIC_JCB }}">
                            <h5>{{ __('Members') }}</h5>
                            @can('edit project')
                                <div class="{{ VC::FEND }}">
                                    <a href="#"
                                        data-size="lg"
                                        data-url="{{ route(ViewsConstants::PRJ . '.invite.member.view', $project->id) }}"
                                        data-ajax-popup="true"
                                        data-bs-toggle="tooltip"
                                        title="{{ __('Add Member') }}"
                                        class="{{ VC::BT_SM_PM }}">
                                        <i class="{{ VC::TI_PLS }}"></i>
                                    </a>
                                </div>
                            @endcan
                        </div>
                    </div>
                    <div class="{{ VC::CD_BD }}">
                        <ul class="{{ VC::LG_FLSH }} list" id="project_users"></ul>
                    </div>
                </div>
            </div>
            <div class="{{ VC::CLM6 }}">
                <div class="{{ VC::CD }}">
                    <div class="{{ VC::CD_HD }}">
                        <div class="{{ VC::DFL_AIC_JCB }}">
                            <h5>{{ __('Milestones') }} ({{ count($project->milestones) }})</h5>
                            @can('create milestone')
                                <div class="{{ VC::FEND }}">
                                    @php
                                        try {
                                            $milestoneCreateBaseName     = ViewsConstants::ML.'.create';
                                            $milestoneCreateKebabName    = Str::kebab($milestoneCreateBaseName);
                                            $milestoneCreateResolvedName = Route::has($milestoneCreateBaseName)
                                                ? $milestoneCreateBaseName
                                                : (Route::has($milestoneCreateKebabName) ? $milestoneCreateKebabName : null);
                                            $projectId                   = isset($project) && !empty($project->id) ? $project->id : null;
                                            $milestoneCreateUrl          = ($milestoneCreateResolvedName && $projectId) ? route($milestoneCreateResolvedName, $projectId) : '#';
                                            $milestoneCreateGuardMsg     = Utility::fetchLinkMessage($lang, ViewsConstants::ML, 'project_milestone_create_route_unavailable') ?? 'Create milestone route is unavailable. Please contact technical support or your domain administrator.';
                                            $milestoneCreateLinkId       = 'milestone-create-link';
                                            $milestoneCreateTitle        = __('Create Milestone');
                                        } catch (\Throwable $e) {
                                            \Log::error('projects/view — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                        }
@endphp
                                    <a href="{{ $milestoneCreateUrl }}"
                                    id="{{ $milestoneCreateLinkId }}"
                                    class="{{ VC::BT_SM_PM }}"
                                    data-ajax-popup="true"
                                    data-size="md"
                                    data-title="{{ $milestoneCreateTitle }}"
                                    data-url="{{ $milestoneCreateUrl }}"
                                    data-guard-msg="{{ base64_encode($milestoneCreateGuardMsg) }}"
                                    data-bs-toggle="tooltip"
                                    title="{{ $milestoneCreateTitle }}">
                                        <i class="{{ VC::TI_PLS }}"></i>
                                    </a>
                                    @push(StacksConstants::ADM_SCR_PG)
                                        <script defer>
                                            (() => {
                                                try {
                                                    const l = document.getElementById('{{ $milestoneCreateLinkId }}');
                                                    if (!l || l.getAttribute('data-listener-active') === 'true') return;
                                                    l.setAttribute('data-listener-active', 'true');
                                                    l.addEventListener('click', e => {
                                                        try {
                                                            const href = l.getAttribute('href') || '#';
                                                            const url = l.getAttribute('data-url') || href || '#';
                                                            if (href !== '#' || url !== '#') return;
                                                            e.preventDefault();
                                                            const msg = l.getAttribute('data-guard-msg') || 'Create milestone route is unavailable. Please contact technical support or your domain administrator.';\n                                                            (window.RouteGuard?.showToast || (m => alert(m)))(msg);
                                                            l.setAttribute('data-failed-route', 'true');
                                                        } catch (err) {}
                                                    });
                                                } catch (error) {}
                                            })();
                                        </script>
                                    @endpush
                                </div>
                            @endcan
                        </div>
                    </div>
                    <div class="{{ VC::CD_BD }}">
                        <ul class="{{ VC::LG_FLSH }}">
                            @if ($project->milestones->count() > 0)
                                @foreach ($project->milestones as $milestone)
                                    <li class="{{ VC::LGI }} px-0">
                                        <div class="{{ VC::R_ALC }} {{ VC::JCB }}">
                                        <div class="col-sm-auto {{ VC::MB3 }} mb-sm-0">
                                            <div class="{{ VC::DFL_AIC }}">
                                                <div>
                                                    <h6 class="{{ VC::MB0 }}">
                                                        {{ !empty($milestone->title) ? $milestone->title : __('No Title') }}
                                                        @if(isset($milestone->status) &&
                                                        array_key_exists($milestone->status, Project::status_color) &&
                                                        array_key_exists($milestone->status, Project::$project_status))
                                                            <span class="{{ VC::BDG_XS }} p-2 px-3 rounded bg-{{ Project::status_color[$milestone->status] }}">
                                                                {{ __(data_get(Project::$project_status, $milestone->status, 'Unknown')) }}
                                                            </span>
                                                        @endif
                                                    </h6>
                                                    <small class="{{ VC::TXT_MT }}">
                                                        {{ (isset($milestone->tasks) ? $milestone->tasks->count() : __('No listed tasks.')) }}
                                                        {{ __('Tasks') }}
                                                    </small>
                                                </div>
                                            </div>
                                        </div>
                                            <div class="col-sm-auto text-sm-end {{ VC::DFL_AIC }}">
                                                @can('view milestone')
                                                    <div class="{{ VC::ACT_BTN_WRN }}">
                                                        @php
                                                            try {
                                                                $milestoneShowBaseName     = ViewsConstants::ML.'.show';
                                                                $milestoneShowKebabName    = Str::kebab($milestoneShowBaseName);
                                                                $milestoneShowResolvedName = Route::has($milestoneShowBaseName)
                                                                    ? $milestoneShowBaseName
                                                                    : (Route::has($milestoneShowKebabName) ? $milestoneShowKebabName : null);
                                                                $milestoneId               = isset($milestone) && !empty($milestone->id) ? $milestone->id : null;
                                                                $milestoneShowUrl          = ($milestoneShowResolvedName && $milestoneId) ? route($milestoneShowResolvedName, $milestoneId) : '#';
                                                                $milestoneShowGuardMsg     = Utility::fetchLinkMessage($lang, ViewsConstants::ML, 'project_milestone_show_route_unavailable') ?? 'Open milestone route is unavailable. Please contact technical support or your domain administrator.';
                                                                $milestoneShowLinkId       = 'milestone-show-link-'.($milestoneId ?? 'x');
                                                                $milestoneShowTitle        = __('View');
                                                            } catch (\Throwable $e) {
                                                                \Log::error('projects/view — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                            }
@endphp
                                                        <a href="{{ $milestoneShowUrl }}"
                                                        id="{{ $milestoneShowLinkId }}"
                                                        class="{{ VC::BT_SM }}"
                                                        data-ajax-popup="true"
                                                        data-size="lg"
                                                        data-title="{{ $milestoneShowTitle }}"
                                                        data-url="{{ $milestoneShowUrl }}"
                                                        data-guard-msg="{{ base64_encode($milestoneShowGuardMsg) }}"
                                                        data-bs-toggle="tooltip"
                                                        title="{{ $milestoneShowTitle }}">
                                                            <i class="{{ VC::TI_EYE_WT }}"></i>
                                                        </a>
                                                        @push(StacksConstants::ADM_SCR_PG)
                                                            <script defer>
                                                                (() => {
                                                                    try {
                                                                        const l = document.getElementById('{{ $milestoneShowLinkId }}');
                                                                        if (!l || l.getAttribute('data-listener-active') === 'true') return;
                                                                        l.setAttribute('data-listener-active', 'true');
                                                                        l.addEventListener('click', e => {
                                                                            try {
                                                                                const href = l.getAttribute('href') || '#';
                                                                                const url = l.getAttribute('data-url') || href || '#';
                                                                                if (href !== '#' || url !== '#') return;
                                                                                e.preventDefault();
                                                                                const msg = l.getAttribute('data-guard-msg') || 'Open milestone route is unavailable. Please contact technical support or your domain administrator.';
                                                                                (window.RouteGuard?.showToast || (m => alert(m)))(msg);
                                                                                l.setAttribute('data-failed-route', 'true');
                                                                            } catch (err) {}
                                                                        });
                                                                    } catch (error) {}
                                                                })();
                                                            </script>
                                                        @endpush
                                                    </div>
                                                @endcan
                                                @can('edit milestone')
                                                    <div class="{{ VC::ACT_BTN_INF }}">
                                                        @php
                                                            try {
                                                                $milestoneEditBaseName     = ViewsConstants::ML.'.edit';
                                                                $milestoneEditKebabName    = Str::kebab($milestoneEditBaseName);
                                                                $milestoneEditResolvedName = Route::has($milestoneEditBaseName)
                                                                    ? $milestoneEditBaseName
                                                                    : (Route::has($milestoneEditKebabName) ? $milestoneEditKebabName : null);
                                                                $milestoneId               = isset($milestone) && !empty($milestone->id) ? $milestone->id : null;
                                                                $milestoneEditUrl          = ($milestoneEditResolvedName && $milestoneId) ? route($milestoneEditResolvedName, $milestoneId) : '#';
                                                                $milestoneEditGuardMsg     = Utility::fetchLinkMessage($lang, ViewsConstants::ML, 'project_milestone_edit_route_unavailable') ?? 'Edit milestone route is unavailable. Please contact technical support or your domain administrator.';
                                                                $milestoneEditLinkId       = 'milestone-edit-link-'.($milestoneId ?? 'x');
                                                                $milestoneEditTitle        = __('Edit');
                                                                $milestoneEditDataTitle    = __('Edit Milestone');
                                                            } catch (\Throwable $e) {
                                                                \Log::error('projects/view — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                            }
@endphp
                                                        <a href="{{ $milestoneEditUrl }}"
                                                        id="{{ $milestoneEditLinkId }}"
                                                        class="{{ VC::BT_SM }}"
                                                        data-ajax-popup="true"
                                                        data-size="md"
                                                        data-title="{{ $milestoneEditDataTitle }}"
                                                        data-url="{{ $milestoneEditUrl }}"
                                                        data-guard-msg="{{ base64_encode($milestoneEditGuardMsg) }}"
                                                        data-bs-toggle="tooltip"
                                                        title="{{ $milestoneEditTitle }}">
                                                            <i class="{{ VC::TI_PC_WT }}"></i>
                                                        </a>
                                                        @push(StacksConstants::ADM_SCR_PG)
                                                            <script defer>
                                                                (() => {
                                                                    try {
                                                                        const l = document.getElementById('{{ $milestoneEditLinkId }}');
                                                                        if (!l || l.getAttribute('data-listener-active') === 'true') return;
                                                                        l.setAttribute('data-listener-active', 'true');
                                                                        l.addEventListener('click', e => {
                                                                            try {
                                                                                const href = l.getAttribute('href') || '#';
                                                                                const url = l.getAttribute('data-url') || href || '#';
                                                                                if (href !== '#' || url !== '#') return;
                                                                                e.preventDefault();
                                                                                const msg = l.getAttribute('data-guard-msg') || 'Edit milestone route is unavailable. Please contact technical support or your domain administrator.';
                                                                                (window.RouteGuard?.showToast || (m => alert(m)))(msg);
                                                                                l.setAttribute('data-failed-route', 'true');
                                                                            } catch (err) {}
                                                                        });
                                                                    } catch (error) {}
                                                                })();
                                                            </script>
                                                        @endpush
                                                    </div>
                                                @endcan
                                                @can('delete milestone')
                                                    <div class="{{ VC::ACT_BTN_DNG_2 }}">
                                                        @php
                                                            try {
                                                                $milestoneDestroyBaseName     = ViewsConstants::ML.'.destroy';
                                                                $milestoneDestroyKebabName    = Str::kebab($milestoneDestroyBaseName);
                                                                $milestoneDestroyResolvedName = Route::has($milestoneDestroyBaseName)
                                                                    ? $milestoneDestroyBaseName
                                                                    : (Route::has($milestoneDestroyKebabName) ? $milestoneDestroyKebabName : null);
                                                                $milestoneId                  = isset($milestone) && !empty($milestone->id) ? $milestone->id : null;
                                                                $milestoneDestroyRouteArray   = ($milestoneDestroyResolvedName && $milestoneId) ? [$milestoneDestroyResolvedName, $milestoneId] : ['#'];
                                                                $milestoneDestroyUrl          = ($milestoneDestroyResolvedName && $milestoneId) ? route($milestoneDestroyResolvedName, $milestoneId) : '#';
                                                                $milestoneDestroyGuardMsg     = Utility::fetchLinkMessage($lang, ViewsConstants::ML, 'project_milestone_delete_route_unavailable') ?? 'Delete milestone route is unavailable. Please contact technical support or your domain administrator.';
                                                                $milestoneDestroyFormId       = 'milestone-destroy-form-'.($milestoneId ?? 'x');
                                                                $milestoneDestroyLinkId       = 'milestone-destroy-link-'.($milestoneId ?? 'x');
                                                                $milestoneDestroyTitle        = __('Delete');
                                                            } catch (\Throwable $e) {
                                                                \Log::error('projects/view — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                            }
@endphp
                                                        {!! Form::open([
                                                            'method'         => 'DELETE',
                                                            'route'          => $milestoneDestroyRouteArray,
                                                            'id'             => $milestoneDestroyFormId,
                                                            'data-url'       => $milestoneDestroyUrl,
                                                            'data-guard-msg' => $milestoneDestroyGuardMsg
                                                        ]) !!}
                                                            @csrf
                                                            <a href="#"
                                                            id="{{ $milestoneDestroyLinkId }}"
                                                            class="{{ VC::BT_SM_CT_PR }}"
                                                            data-form-id="{{ $milestoneDestroyFormId }}"
                                                            data-url="{{ $milestoneDestroyUrl }}"
                                                            data-guard-msg="{{ base64_encode($milestoneDestroyGuardMsg) }}"
                                                            data-bs-toggle="tooltip"
                                                            title="{{ $milestoneDestroyTitle }}">
                                                                <i class="{{ VC::TI_TRS_WT }}"></i>
                                                            </a>
                                                        {!! Form::close() !!}
                                                        @push(StacksConstants::ADM_SCR_PG)
                                                            <script defer>
                                                                (() => {
                                                                    try {
                                                                        const l = document.getElementById('{{ $milestoneDestroyLinkId }}');
                                                                        if (!l || l.getAttribute('data-listener-active') === 'true') return;
                                                                        l.setAttribute('data-listener-active', 'true');
                                                                        l.addEventListener('click', e => {
                                                                            try {
                                                                                e.preventDefault();
                                                                                const formId = l.getAttribute('data-form-id') || '';
                                                                                const f = formId ? document.getElementById(formId) : null;
                                                                                if (!f) return;
                                                                                const url = f.getAttribute('data-url') || '#';
                                                                                const action = f.getAttribute('action') || '#';
                                                                                if (url === '#' && action === '#') {
                                                                                    const msg = l.getAttribute('data-guard-msg') || f.getAttribute('data-guard-msg') || 'Delete milestone route is unavailable. Please contact technical support or your domain administrator.';
                                                                                    (window.RouteGuard?.showToast || (m => alert(m)))(msg);
                                                                                    l.setAttribute('data-failed-route', 'true');
                                                                                    f.setAttribute('data-failed-route', 'true');
                                                                                    return;
                                                                                }
                                                                                f.submit();
                                                                            } catch (err) {}
                                                                        });
                                                                    } catch (error) {}
                                                                })();
                                                            </script>
                                                        @endpush
                                                    </div>
                                                @endcan
                                            </div>
                                        </div>
                                    </li>
                                @endforeach
                            @else
                                <div class="py-5">
                                    <h6 class="{{ VC::H6 }} text-center">{{ __('No Milestone Found.') }}</h6>
                                </div>
                            @endif
                        </ul>
                    </div>
                </div>
            </div>
            @can('view activity')
                <div class="col-xl-6">
                    <div class="{{ VC::CD }} activity-scroll">
                        <div class="{{ VC::CD_HD }}">
                            <h5>{{ __('Activity Log') }}</h5>
                            <small>{{ __('Activity Log of this project') }}</small>
                        </div>
                        <div class="{{ VC::CD_BD }} vertical-scroll-cards">
                            @if (!empty($project->activities) and $project->activities->count() > 0)
                                @foreach ($project->activities as $activity)
                                    <div class="{{ VC::CD }} {{ VC::P4 }} {{ VC::MB3 }}">
                                        <div class="{{ VC::DFL_AIC_JCB }}">
                                            <div class="{{ VC::DFL_AIC }}">
                                                <div class="theme-avatar {{ VC::BG_P }}">
                                                    @php
                                                        try {
                                                            $iconClasses = VC::TI;
                                                            if (isset($activity->log_type)) {
                                                                $iconClasses .= ' ' . (method_exists($activity, 'logIcon')
                                                                    ? $activity->logIcon($activity->log_type)
                                                                    : '');
                                                            }
                                                        } catch (\Throwable $e) {
                                                            \Log::error('projects/view — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                        }
@endphp
                                                    <i class="{{ $iconClasses }}"></i>
                                                </div>
                                                <div class="ms-3">
                                                    <h6 class="{{ VC::MB0 }}">
                                                        {{ __(data_get($activity, 'log_type', 'unknown_activity')) }}
                                                    </h6>

                                                    @php
                                                        try {
                                                            $remark = method_exists($activity, 'getRemark')
                                                                ? $activity->getRemark()
                                                                : null;
                                                        } catch (\Throwable $e) {
                                                            \Log::error('projects/view — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                        }
@endphp

                                                    <p class="{{ VC::TXT_MT }} {{ VC::TXSM }} {{ VC::MB0 }}">
                                                        {!! !empty(trim(strip_tags($remark ?? '')))
                                                            ? $remark
                                                            : __('No activity details available') !!}
                                                    </p>
                                                </div>
                                            </div>
                                            @php
                                                $timestamp ??= '';
                                                if (isset($activity) && isset($activity->created_at) && method_exists($activity->created_at, 'diffForHumans')) {
                                                    if ($activity->created_at instanceof Carbon)
                                                        $timestamp = $activity->created_at->diffForHumans();
                                                    else {
                                                        try {
                                                            $timestamp = Carbon::parse($activity->created_at)->diffForHumans();
                                                        } catch (\Exception $e) {
                                                            $timestamp = $activity->created_at ?? __('No activity timestamp available');
                                                        }
                                                    }
                                                }
@endphp
                                            <p class="{{ VC::TXT_MT }} {{ VC::TXSM }} {{ VC::MB0 }}">
                                                {{ !empty($timestamp) ? $timestamp : __('No activity timestamp available') }}
                                            </p>
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                <div class="py-5">
                                    <h6 class="{{ VC::H6 }} text-center">{{ __('No Activity Found.') }}</h6>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endcan
            <div class="{{ VC::CLM6 }}">
                <div class="{{ VC::CD }} activity-scroll">
                    <div class="{{ VC::CD_HD }}">
                        <h5>{{ __('Attachments') }}</h5>
                        <small>{{ __('Attachment that uploaded in this project') }}</small>
                    </div>
                    <div class="{{ VC::CD_BD }}">
                        <ul class="{{ VC::LG_FLSH }}">
                            @php
                                $attachments ??= [];
                                $attachmentsCount ??= 0;
                                try {
                                    if (method_exists($project, 'projectAttachments')) {
                                        $attachments = $project->projectAttachments();
                                        $attachmentsCount = is_countable($attachments) ? count($attachments) : 0;
                                    }
                                } catch (\Throwable $e) {
                                    \Log::error('projects/view — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                }
@endphp
                            @if ($attachmentsCount > 0)
                                @foreach ($attachments as $attachment)
                                    <li class="{{ VC::LGI }} px-0">
                                        <div class="{{ VC::R_ALC }} {{ VC::JCB }}">
                                            <div class="col {{ VC::MB3 }} mb-sm-0">
                                                <div class="{{ VC::DFL_AIC }}">
                                                    <div>
                                                        <h6 class="{{ VC::MB0 }}">{{ $attachment->name }}</h6>
                                                        <small class="{{ VC::TXT_MT }}">{{ $attachment->file_size }}</small>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-auto text-sm-end {{ VC::DFL_AIC }}">
                                                <div class="{{ VC::ACT_BTN_INF }}">
                                                    <a href="{{ asset(Storage::url('tasks/'.$attachment->file)) }}"
                                                        data-bs-toggle="tooltip"
                                                        title="{{ __('Download') }}"
                                                        class="{{ VC::BT_SM }}"
                                                        download>
                                                        <i class="{{ VC::TI_DWN }} {{ VC::TXT_WT }}"></i>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                @endforeach
                            @else
                                <div class="py-5">
                                    <h6 class="{{ VC::H6 }} text-center">{{ __('No Attachments Found.') }}</h6>
                                </div>
                            @endif
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    @endsection
    @push(StacksConstants::ADM_SCR_PG)
    <script defer>
        (() => {
            'use strict';

            const RouteGuardHandler = {
                initialized: false,
                debounceMap: new Map(),

                // DRY: Centralized toast notification
                showToast(message) {
                    try {
                        const container = this.getOrCreateToastContainer();
                        if (!container) return alert(message);

                        const hasBootstrap = window.bootstrap?.Toast;
                        if (hasBootstrap) {
                            const toast = this.createBootstrapToast(message);
                            container.appendChild(toast);
                            const instance = window.bootstrap.Toast.getOrCreateInstance(toast);
                            toast.addEventListener('hidden.bs.toast', () => {
                                try { toast.remove(); } catch (err) {}
                            });
                            instance.show();
                        } else {
                            alert(message);
                        }
                    } catch (err) {
                        console.error('[RouteGuard] Toast error:', err);
                        alert(message);
                    }
                },

                getOrCreateToastContainer() {
                    return document.getElementById('toast-container') ||
                           (() => { const c = document.createElement('div'); c.id = 'toast-container'; c.className = 'position-fixed top-0 end-0 p-3'; c.style.zIndex = '9999'; document.body.appendChild(c); return c; })();
                },

                createBootstrapToast(message) {
                    const toast = document.createElement('div');
                    toast.className = 'toast align-items-center text-white bg-warning border-0';
                    toast.setAttribute('role', 'alert');
                    toast.setAttribute('aria-live', 'assertive');
                    toast.setAttribute('aria-atomic', 'true');
                    toast.innerHTML = `
                        <div class="{{ VC::DFL }}">
                            <div class="toast-body">${message}</div>
                            <button type="button" class="{{ VC::BT_CL }} btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="{{ __('Close') }}"></button>
                        </div>
                    `;
                    return toast;
                },

                // Route guard with debouncing
                guardRoute(el) {
                    try {
                        if (!el) return true;
                        const href = el.getAttribute('href') || '#';
                        const url = el.getAttribute('data-url') || href || '#';

                        if (href !== '#' && url !== '#') return false;

                        const msg = el.getAttribute('data-guard-msg') ||
                                  '{{ __('Route is unavailable. Please contact support.') }}';
                        this.showToast(msg);
                        el.setAttribute('data-failed-route', 'true');
                        return true;
                    } catch (err) {
                        console.error('[RouteGuard] Guard error:', err);
                        return true;
                    }
                },

                // Attach click handler with debouncing
                attachClickHandler(el) {
                    try {
                        if (!el) return;
                        const flagName = 'data-route-listener';
                        if (el.getAttribute(flagName) === 'true') return;

                        el.setAttribute(flagName, 'true');

                        el.addEventListener('click', (e) => {
                            try {
                                const elId = el.id || el.textContent.trim();

                                // Debounce multiple clicks
                                if (this.debounceMap.has(elId)) {
                                    e.preventDefault();
                                    return;
                                }

                                if (this.guardRoute(el)) {
                                    e.preventDefault();
                                    return;
                                }

                                // Set debounce timeout
                                this.debounceMap.set(elId, true);
                                setTimeout(() => this.debounceMap.delete(elId), 800);

                                console.log('[RouteGuard] Allowed action for:', elId);
                            } catch (err) {
                                console.error('[RouteGuard] Click handler error:', err);
                            }
                        }, { passive: false });
                    } catch (err) {
                        console.error('[RouteGuard] Attach handler error:', err);
                    }
                },

                // Initialize all route guard links
                init() {
                    if (this.initialized) return;

                    try {
                        const guardLinks = document.querySelectorAll('[data-route-guard]');
                        guardLinks.forEach(el => this.attachClickHandler(el));

                        this.initialized = true;
                        console.log('[RouteGuard] Initialized:', guardLinks.length + ' links');
                    } catch (err) {
                        console.error('[RouteGuard] Initialization error:', err);
                    }
                }
            };

            // Initialize on DOM ready
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', () => RouteGuardHandler.init());
            } else {
                RouteGuardHandler.init();
            }

            // Expose for potential dynamic content
            window.RouteGuardHandler = RouteGuardHandler;
        })();
    </script>
    @endpush
@else
    <div>
        {{ __('No project selected.') }}
    </div>
@endif
