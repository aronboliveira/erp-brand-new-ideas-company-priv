@php
    try {
$lang = Utility::fetchUserLang();
        $projectIndexBaseName = VW::PRJ . '.index';
        $projectIndexKebabName = Str::kebab($projectIndexBaseName);
        $projectIndexResolvedName = Route::has($projectIndexBaseName) ? $projectIndexBaseName : (Route::has($projectIndexKebabName) ? $projectIndexKebabName : null);
        $projectIndexUrl = $projectIndexResolvedName ? route($projectIndexResolvedName) : '#';
        $projectIndexGuardMsg = Utility::fetchLinkMessage($lang, VW::PRJ, 'project_index_route_unavailable') ?? 'Project index route is unavailable. Please contact technical support or your domain administrator.';
    } catch (\Throwable $e) {
        \Log::error('projects/timesheets/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    }
@endphp
@extends(ExtendingLayoutsConstants::ADM)

@if(!empty($project))
    @section(YieldingConstants::ADM_PG_TTL)
        {{!empty($project->project_name) ? $project->project_name.__("'s Timesheet") : __('No project name available.')}}
    @endsection

    @section(YieldingConstants::ADM_BDC)
        <li class="{{ VC::BCI }}">
            <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
            {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
                {{ __('Dashboard') }}
            </a>
        </li>
        <li class="{{ VC::BCI }}">
            <a
                href="{{ $projectIndexUrl }}"
                data-url="{{ $projectIndexUrl }}"
                data-guard-msg="{{ base64_encode($projectIndexGuardMsg) }}"
                data-route-guard
            >
                {{ __('Project') }}
            </a>
        </li>
        <li class="{{ VC::BCI }}">
            @php
                try {
                    $projectShowBaseName     = VW::PRJ.'.show';
                    $projectShowKebabName    = Str::kebab($projectShowBaseName);
                    $projectShowResolvedName = Route::has($projectShowBaseName)
                        ? $projectShowBaseName
                        : (Route::has($projectShowKebabName) ? $projectShowKebabName : null);
                    $projectId               = isset($project) && !empty($project->id) ? $project->id : null;
                    $projectShowUrl          = ($projectShowResolvedName && $projectId) ? route($projectShowResolvedName, $projectId) : '#';
                    $projectShowGuardMsg     = Utility::fetchLinkMessage($lang, VW::PRJ, 'open_project_route_unavailable') ?? 'Open project route is unavailable. Please contact technical support or your domain administrator.';
                    $projectShowLinkId       = 'project-show-link';
                    $projectNameText         = ucwords($project->project_name ?? '');
                } catch (\Throwable $e) {
                    \Log::error('projects/timesheets/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                }
@endphp
            <a href="{{ $projectShowUrl }}"
            data-url="{{ $projectShowUrl }}"
            data-guard-msg="{{ base64_encode($projectShowGuardMsg) }}"
            data-route-guard>
                {{ $projectNameText }}
            </a>
        </li>
        <li class="{{ VC::BCI }}">{{__('Timesheet')}}</li>
    @endsection

    @section(YieldingConstants::ADM_ACT_BTN)
        <div class="{{ VC::RW }} gy-3 {{ VC::JCE }} {{ VC::ALC }}">
            <div class="{{ VC::C_AT }} weekly-dates-div text-end me-2">
                <a href="#" class="action-item previous">
                    <i class="{{ VC::TI }} {{ VC::TI }}-arrow-left"></i>
                </a>
                <span class="weekly-dates"></span>
                <input type="hidden" id="weeknumber" value="0">
                <input type="hidden" id="selected_dates">
                <a href="#" class="action-item next">
                    <i class="{{ VC::TI }} {{ VC::TI }}-arrow-right"></i>
                </a>
            </div>

            @can('create timesheet')
                <div class="{{ VC::C_AT }} project_tasks_select text-end">
                    <div class="dropdown {{ VC::BT_SM }} p-0">
                        <a class="{{ VC::BT_PRM }} add-small"
                            role="button"
                            data-bs-toggle="dropdown"
                            aria-haspopup="true"
                            aria-expanded="true">
                            <i class="{{ VC::TI_PLS }} me-2"></i>{{ __('Add Task on Timesheet') }}
                        </a>
                        <div class="{{ VC::DRP_MN_END }} tasks-box">
                            <div class="scrollbar-inner">
                                <div class="mh-280">
                                    <div class="tasks-list"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endcan
        </div>
    @endsection

    @section(YieldingConstants::ADM_CTT)
        <div class="{{ VC::RW }}">
            <div class="{{ VC::CS12 }}">
                <div class="{{ VC::RW }}">
                    <div class="{{ VC::C12 }}">
                        <div class="{{ VC::CD_NSD }}">
                            <div class="{{ VC::CD_BD_TB_BD }}">
                                <div class="{{ VC::TB_RSP }} project-timesheet overflow-auto"></div>
                                <div class="{{ VC::TXCT }} notfound-timesheet">
                                    <div class="empty-project-text {{ VC::TXCT }} p-3 min-h-300">
                                        <h5 class="pt-5">{{ __("We couldn't find any data") }}</h5>
                                        <p class="m-0">{{ __("Sorry we can't find any timesheet records on this week.") }}</p>
                                        <p class="m-0">{{ __("To add timesheet record go to Add Task on Timesheet") }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endsection

    @push(StacksConstants::ADM_SCR_PG)
            <script async>
          (() => {
              if (!window.translations) {
  window.translations = {};
}
const t = {
                ar: { timesheet_unavailable: "تعذّر تحميل الجدول الزمني", timesheet_nav_unavailable: "تعذّر التنقل بين الأسابيع", timesheet_popup_unavailable: "تعذّر فتح نافذة الجدول الزمني", timesheet_task_append_unavailable: "تعذّر إضافة المهمة إلى الجدول الزمني", timesheet_timecalc_unavailable: "تعذّر حساب إجمالي الوقت" },
                da: { timesheet_unavailable: "Kunne ikke indlæse timeseddel", timesheet_nav_unavailable: "Kunne ikke skifte uge", timesheet_popup_unavailable: "Kunne ikke åbne timeseddel-popup", timesheet_task_append_unavailable: "Kunne ikke tilføje opgave til timeseddel", timesheet_timecalc_unavailable: "Kunne ikke beregne samlet tid" },
                de: { timesheet_unavailable: "Zeiterfassung konnte nicht geladen werden", timesheet_nav_unavailable: "Woche konnte nicht gewechselt werden", timesheet_popup_unavailable: "Zeiterfassungs-Popup konnte nicht geöffnet werden", timesheet_task_append_unavailable: "Aufgabe konnte nicht zur Zeiterfassung hinzugefügt werden", timesheet_timecalc_unavailable: "Gesamtzeit konnte nicht berechnet werden" },
                en: { timesheet_unavailable: "Cannot load timesheet", timesheet_nav_unavailable: "Cannot change week", timesheet_popup_unavailable: "Cannot open timesheet dialog", timesheet_task_append_unavailable: "Cannot append task to timesheet", timesheet_timecalc_unavailable: "Cannot calculate total time" },
                es: { timesheet_unavailable: "No se puede cargar la hoja de tiempo", timesheet_nav_unavailable: "No se puede cambiar de semana", timesheet_popup_unavailable: "No se puede abrir el diálogo de hoja de tiempo", timesheet_task_append_unavailable: "No se puede añadir la tarea a la hoja de tiempo", timesheet_timecalc_unavailable: "No se puede calcular el tiempo total" },
                fr: { timesheet_unavailable: "Impossible de charger la feuille de temps", timesheet_nav_unavailable: "Impossible de changer de semaine", timesheet_popup_unavailable: "Impossible d’ouvrir la boîte de dialogue de feuille de temps", timesheet_task_append_unavailable: "Impossible d’ajouter la tâche à la feuille de temps", timesheet_timecalc_unavailable: "Impossible de calculer le temps total" },
                he: { timesheet_unavailable: "לא ניתן לטעון גיליון שעות", timesheet_nav_unavailable: "לא ניתן להחליף שבוע", timesheet_popup_unavailable: "לא ניתן לפתוח חלון גיליון שעות", timesheet_task_append_unavailable: "לא ניתן להוסיף משימה לגיליון שעות", timesheet_timecalc_unavailable: "לא ניתן לחשב זמן כולל" },
                it: { timesheet_unavailable: "Impossibile caricare il timesheet", timesheet_nav_unavailable: "Impossibile cambiare settimana", timesheet_popup_unavailable: "Impossibile aprire la finestra del timesheet", timesheet_task_append_unavailable: "Impossibile aggiungere l’attività al timesheet", timesheet_timecalc_unavailable: "Impossibile calcolare il tempo totale" },
                ja: { timesheet_unavailable: "タイムシートを読み込めません", timesheet_nav_unavailable: "週を変更できません", timesheet_popup_unavailable: "タイムシートのダイアログを開けません", timesheet_task_append_unavailable: "タスクをタイムシートに追加できません", timesheet_timecalc_unavailable: "合計時間を計算できません" },
                nl: { timesheet_unavailable: "Timesheet kan niet worden geladen", timesheet_nav_unavailable: "Kan week niet wijzigen", timesheet_popup_unavailable: "Kan timesheetdialoog niet openen", timesheet_task_append_unavailable: "Kan taak niet aan timesheet toevoegen", timesheet_timecalc_unavailable: "Kan totale tijd niet berekenen" },
                pl: { timesheet_unavailable: "Nie można wczytać karty czasu", timesheet_nav_unavailable: "Nie można zmienić tygodnia", timesheet_popup_unavailable: "Nie można otworzyć okna karty czasu", timesheet_task_append_unavailable: "Nie można dodać zadania do karty czasu", timesheet_timecalc_unavailable: "Nie można obliczyć łącznego czasu" },
                pt: { timesheet_unavailable: "Não foi possível carregar a folha de horas", timesheet_nav_unavailable: "Não foi possível mudar a semana", timesheet_popup_unavailable: "Não foi possível abrir a janela da folha de horas", timesheet_task_append_unavailable: "Não foi possível adicionar a tarefa à folha de horas", timesheet_timecalc_unavailable: "Não foi possível calcular o tempo total" },
                "pt-br": { timesheet_unavailable: "Não foi possível carregar o timesheet", timesheet_nav_unavailable: "Não foi possível mudar a semana", timesheet_popup_unavailable: "Não foi possível abrir o timesheet", timesheet_task_append_unavailable: "Não foi possível adicionar a tarefa ao timesheet", timesheet_timecalc_unavailable: "Não foi possível calcular o tempo total" },
                ru: { timesheet_unavailable: "Не удалось загрузить табель", timesheet_nav_unavailable: "Не удалось сменить неделю", timesheet_popup_unavailable: "Не удалось открыть окно табеля", timesheet_task_append_unavailable: "Не удалось добавить задачу в табель", timesheet_timecalc_unavailable: "Не удалось вычислить общее время" },
                tr: { timesheet_unavailable: "Zaman çizelgesi yüklenemiyor", timesheet_nav_unavailable: "Hafta değiştirilemiyor", timesheet_popup_unavailable: "Zaman çizelgesi penceresi açılamıyor", timesheet_task_append_unavailable: "Görev zaman çizelgesine eklenemiyor", timesheet_timecalc_unavailable: "Toplam süre hesaplanamıyor" },
                zh: { timesheet_unavailable: "无法加载工时表", timesheet_nav_unavailable: "无法切换周", timesheet_popup_unavailable: "无法打开工时表弹窗", timesheet_task_append_unavailable: "无法将任务添加到工时表", timesheet_timecalc_unavailable: "无法计算总时间" }
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
                const errFb = "# ERROR";
                const dataClientLocalized = "data-client-localized";
                const dataGuardMsg = "data-guard-msg";
                const DATA_LISTENER_ADDED = "data-listener-added";

                const getMsg = (el, msgKey) => {
                let msg = errFb;
                if (el?.getAttribute("data-sv-localized") === "true" || el?.getAttribute(dataClientLocalized) === "true") {
                    msg = el.getAttribute(dataGuardMsg) || errFb;
                } else {
                    let lang = (window.sessionStorage.getItem("erp-np-lang") || document.documentElement.lang || "en")
                    .toLowerCase()
                    .replace(/_/g, "-");
                    lang = lang === "pt-br" ? lang : lang.slice(0, 2);
                    msg = window.translations?.[lang]?.[msgKey] || el?.getAttribute(dataGuardMsg) || window.translations?.en?.[msgKey] || errFb;
                    if (msg !== errFb) {
                    el?.setAttribute(dataGuardMsg, msg);
                    el?.setAttribute(dataClientLocalized, "true");
                    }
                }
                return msg;
                };

                const showFeedback = (el, key, ev = "click") => {
                const text = getMsg(el || document.body, key);
                const hasBs = document.querySelector('link[href*="bootstrap"]') && window.bootstrap?.Toast;
                if (hasBs) {
                    let toast = document.querySelector("#np-error-toast");
                    if (!toast) {
                    toast = document.createElement("div");
                    toast.id = "np-error-toast";
                    toast.className = "toast align-items-center text-bg-danger border-0";
                    toast.setAttribute("role", "alert");
                    toast.setAttribute("aria-live", "assertive");
                    toast.setAttribute("aria-atomic", "true");
                    toast.innerHTML = `
                        <div class="{{ VC::DFL }}">
                        <div class="toast-body">${text}</div>
                        <button type="button" class="{{ VC::BT_CL }} btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                        </div>`;
                    document.body.appendChild(toast);
                    }
                    const handler = () => new bootstrap.Toast(toast).show();
                    if (!toast.getAttribute(DATA_LISTENER_ADDED)) {
                    toast.setAttribute(DATA_LISTENER_ADDED, "true");
                    const mo = new MutationObserver((_, o) => {
                        if (!document.body.contains(toast)) {
                        document.removeEventListener(ev, handler);
                        o.disconnect();
                        }
                    });
                    mo.observe(document.body, { childList: true, subtree: true });
                    }
                    document.addEventListener(ev, handler, { once: true });
                } else {
                    const handler = () => alert(text);
                    document.addEventListener(ev, handler, { once: true });
                }
                };

                const guardOnce = (el, key, ev = "click") => {
                if (!el || el.getAttribute(DATA_LISTENER_ADDED) === "true") return;
                const handler = () => showFeedback(el, key, ev);
                el.addEventListener(ev, handler, { once: true });
                el.setAttribute(DATA_LISTENER_ADDED, "true");
                const mo = new MutationObserver((_, o) => {
                    if (!document.body.contains(el)) {
                    el.removeEventListener(ev, handler);
                    o.disconnect();
                    }
                });
                mo.observe(document.body, { childList: true, subtree: true });
                };

                const routeGuard = (element, alt) => {
                const url = element?.getAttribute?.("data-url");
                const href = element?.action ?? element?.href;
                return (!url || url === "#") && (!href || href === "#") && (!alt || alt === "#");
                };

                try {
                if(typeof $==="undefined"){
                    if (
                        window.location.hostname === "localhost" ||
                        window.location.hostname === "127.0.0.1"
                    ) console.error("jQuery unavailable");
                    return;
                }

                const $doc = $(document);
                const initFlag = "data-timesheet-init";
                if (document.documentElement.getAttribute(initFlag) === "true") return;
                document.documentElement.setAttribute(initFlag, "true");

                const weeklyDatesDiv = document.querySelector(".weekly-dates-div");
                const timesheetUrl = "{{ route('timesheets.filters.table.view') }}";
                const appendTaskUrl = "{{route(VW::PRJ . '.' . VW::TMS . '.append.task')}}";

                const ajaxFilterTimesheetTableView = () => {
                    try {
                    const $main = $(".project-timesheet");
                    const $notfound = $(".notfound-timesheet");
                    const week = parseInt($("#weeknumber").val() ?? "0", 10) || 0;
                    const project_id = '{{ $project->id }}' ?? "";

                    if (routeGuard(null, timesheetUrl)) {
                        guardOnce(document.body, "timesheet_unavailable", "click");
                        return;
                    }

                    $.ajax({
                        url: timesheetUrl,
                        data: { week, project_id },
                        success: (res) => {
                        try {
                            $(".weekly-dates-div .weekly-dates").text(res?.onewWeekDate ?? "");
                            $(".weekly-dates-div #selected_dates").val(res?.selectedDate ?? "");

                            const $list = $(".project_tasks_select .tasks-list");
                            $list.find(".dropdown-item").remove();

                            (res?.sectiontasks ?? []).forEach((sec) => {
                            let html = "";
                            if (sec?.section_id !== 0 && sec?.section_name && Array.isArray(sec?.tasks) && sec.tasks.length > 0) {
                                html += `<a href="#" class="{{ VC::DRP_IT }} select-sub-heading" data-tasks-count="${sec.tasks.length}">${sec.section_name}</a>`;
                            }
                            (sec?.tasks ?? []).forEach((t) => {
                                html += `<a href="#" class="{{ VC::DRP_IT }} select-task" data-task-id="${t.task_id}">${t.task_name}</a>`;
                            });
                            $list.append(html);
                            });

                            if ((res?.totalrecords ?? 0) === 0) {
                            $main.hide();
                            $notfound.css("display", "block");
                            } else {
                            $notfound.hide();
                            $main.show();
                            }

                            $main.html(res?.html ?? "");
                        } catch {
                            guardOnce(document.body, "timesheet_unavailable", "click");
                        }
                        },
                        error: () => guardOnce(document.body, "timesheet_unavailable", "click")
                    });
                    } catch {
                    guardOnce(document.body, "timesheet_unavailable", "click");
                    }
                };

                // initial load
                ajaxFilterTimesheetTableView();

                // week navigation
                $doc.on("click", ".weekly-dates-div .action-item", function () {
                    try {
                    const $input = $("#weeknumber");
                    let val = parseInt($input.val() ?? "0", 10) || 0;
                    if ($(this).hasClass("previous")) val--;
                    else if ($(this).hasClass("next")) val++;
                    $input.val(val);
                    ajaxFilterTimesheetTableView();
                    } catch {
                    guardOnce(weeklyDatesDiv, "timesheet_nav_unavailable", "click");
                    }
                });

                // open create/edit modal
                $doc.on("click", "[data-ajax-timesheet-popup='true']", function (e) {
                    e.preventDefault();
                    try {
                    const el = this;
                    const url = $(el).data("url");
                    const type = $(el).data("type");
                    const date = $(el).data("date");
                    const task_id = $(el).data("task-id");
                    const user_id = $(el).data("user-id");

                    const data = { date, task_id };
                    if (user_id != null) data.user_id = user_id;
                    if (type === "create") data.project_id = '{{ $project->id }}';

                    const urlAttr = el.getAttribute("data-url");
                    const href = el.action ?? el.href;
                    if ((!urlAttr || urlAttr === "#") && (!href || href === "#")) {
                        guardOnce(el, "timesheet_popup_unavailable", "click");
                        return;
                    }

                    $.ajax({
                        url,
                        data,
                        dataType: "html",
                        success: (html) => {
                        try {
                            $("#commonModal .body").html(html);
                            const title = (type === "create" ? '{{ __("Create Timesheet") }}' : '{{ __("Edit Timesheet") }}');
                            const dateText = window.moment ? moment(date).format("ddd, Do MMM YYYY") : date;
                            $("#commonModal .modal-title").html(`${title} <small>(${dateText})</small>`);
                            $("#commonModal").modal("show");

                            if (window.$ && $("#date").length > 0 && $.fn.daterangepicker) {
                            $("#date").daterangepicker({ singleDatePicker: true, locale: { format: "YYYY-MM-DD" } });
                            }
                            $("#commonModal").modal({ backdrop: "static", keyboard: false });
                        } catch {
                            guardOnce(el, "timesheet_popup_unavailable", "click");
                        }
                        },
                        error: () => guardOnce(el, "timesheet_popup_unavailable", "click")
                    });
                    } catch {
                    guardOnce(this, "timesheet_popup_unavailable", "click");
                    }
                });

                // append task row
                $doc.on("click", ".project_tasks_select .tasks-box .select-task", function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    try {
                    const task_id = $(this).attr("data-task-id") ?? "";
                    const selected_dates = $("#selected_dates").val() ?? "";
                    const project_id = '{{ $project->id }}' ?? "";

                    if (routeGuard(null, appendTaskUrl)) {
                        guardOnce(this, "timesheet_task_append_unavailable", "click");
                        return;
                    }

                    $.ajax({
                        url: appendTaskUrl,
                        data: { project_id, task_id, selected_dates },
                        success: (res) => {
                        try {
                            $(".notfound-timesheet").hide();
                            $(".project-timesheet").show();
                            $(".project-timesheet .tbody").append(res?.html ?? "");
                            $(`.project_tasks_select .tasks-list .select-task[data-task-id="${task_id}"]`).remove();
                        } catch {
                            guardOnce(document.body, "timesheet_task_append_unavailable", "click");
                        }
                        },
                        error: () => guardOnce(document.body, "timesheet_task_append_unavailable", "click")
                    });
                    } catch {
                    guardOnce(this, "timesheet_task_append_unavailable", "click");
                    }
                });

                // time calculation
                $doc.on("change", "#time_hour, #time_minute", function () {
                    try {
                    let hour = $("#time_hour").children("option:selected").val() ?? "";
                    let minute = $("#time_minute").children("option:selected").val() ?? "";
                    const total = ($("#totaltasktime").val() ?? "0:0").split(":");

                    if ((hour === "00" || hour === "") && (minute === "00" || minute === "")) {
                        $(this).val("");
                        return;
                    }

                    hour = parseInt(hour || "0", 10) + parseInt(total[0] || "0", 10);
                    minute = parseInt(minute || "0", 10) + parseInt(total[1] || "0", 10);

                    if (minute > 50) {
                        minute -= 60;
                        hour++;
                    }

                    const hh = hour < 10 ? `0${hour}` : `${hour}`;
                    const mm = minute < 10 ? `0${minute}` : `${minute}`;

                    $(".display-total-time small").text(
                        `{{ __("Total Time worked on this task") }} : ${hh} {{ __("Hours") }} ${mm} {{ __("Minutes") }}`
                    );
                    } catch {
                    guardOnce(document.body, "timesheet_timecalc_unavailable", "click");
                    }
                });

                } catch (e) {
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
    @push(StacksConstants::ADM_SCR_PG)
        <script>
            if (typeof window.TimesheetsIndexHandler === 'undefined') {
                window.TimesheetsIndexHandler = {
                    init() {
                        document.querySelectorAll('[data-route-guard]').forEach(el => this.attachHandler(el));
                    },
                    attachHandler(el) {
                        el.addEventListener('click', (e) => {
                            try {
                                const href = el.getAttribute('href') || '#';
                                const url = el.getAttribute('data-url') || href || '#';
                                if (href !== '#' && url !== '#') return;
                                e.preventDefault();
                                const msg = el.getAttribute('data-guard-msg') || 'Route is unavailable. Please contact technical support or your domain administrator.';
                                this.showToast(msg);
                            } catch (err) {}
                        }, { passive: false });
                    },
                    showToast(msg) {
                        const hasBootstrap = document.querySelector('link[href*="bootstrap"]') && window.bootstrap;
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
                    }
                };
                document.readyState === 'loading' ? document.addEventListener('DOMContentLoaded', () => window.TimesheetsIndexHandler.init()) : window.TimesheetsIndexHandler.init();
            }
        </script>
    @endpush
@else
    <div class="{{ VC::ALT_INF }}">{{ __('No timesheet data available.') }}</div>
@endif
