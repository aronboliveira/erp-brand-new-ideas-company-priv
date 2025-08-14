@php
    use App\Config\Constants\{
        DatabaseConstants,
        ExtendingLayoutsConstants,
        ProjectsConstants,
        StacksConstants,
        ViewsConstants,
        YieldingConstants,
    };
    use App\Models\{ProjectTask,Utility};
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\Str;
    $lang = Utility::fetchUserLang();
    $projectReportIndexBaseName = ViewsConstants::PRJ_RPT . '.index';
    $projectReportIndexKebabName = Str::kebab($projectReportIndexBaseName);
    $projectReportIndexResolvedName = Route::has($projectReportIndexBaseName) ? $projectReportIndexBaseName : (Route::has($projectReportIndexKebabName) ? $projectReportIndexKebabName : null);
    $projectReportIndexUrl = $projectReportIndexResolvedName ? route($projectReportIndexResolvedName) : '#';
    $projectReportIndexGuardMsg = Utility::fetchLinkMessage($lang, ViewsConstants::PRJ_RPT, 'project_report_index_route_unavailable') ?? 'Project report index route is unavailable. Please contact technical support or your domain administrator.';
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@section(YieldingConstants::ADM_PG_TTL)
    {{__('Project Reports')}}
@endsection
@section('title')
    <div class="d-inline-block">
        <h5 class="h4 d-inline-block font-weight-400 mb-0">{{__('Project Reports')}}</h5>
    </div>
@endsection
@section(YieldingConstants::ADM_BDC)
    <li class="breadcrumb-item">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
        {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="breadcrumb-item">
        <a
            id="project-report-index-link"
            href="{{ $projectReportIndexUrl }}"
            data-url="{{ $projectReportIndexUrl }}"
            data-guard-msg="{{ $projectReportIndexGuardMsg }}"
        >
            {{ __('Project Report') }}
        </a>
    </li>
    {{--    <li class="breadcrumb-item active" aria-current="page">{{__('Project Details')}}</li>--}}
    <li class="breadcrumb-item">{{ucwords($project->project_name)}}</li>
@endsection
@push(StacksConstants::ADM_CSS)
    <style>
        .table.dataTable.no-footer {
            border-bottom: none !important;
        }
        .display-none {
            display: none !important;
        }
    </style>
@endpush
@push(StacksConstants::ADM_SCR_PG)
    <script type="text/javascript" src="{{ asset('js/html2pdf.bundle.min.js') }}"></script>
    <script async>
        window.translations = {
            ar:{report_pdf_unavailable:'تعذّر إنشاء ملف PDF',datatable_unavailable:'تعذّر تهيئة الجدول'},
            da:{report_pdf_unavailable:'Kunne ikke generere PDF',datatable_unavailable:'Kunne ikke initialisere tabel'},
            de:{report_pdf_unavailable:'PDF konnte nicht erstellt werden',datatable_unavailable:'Tabelle konnte nicht initialisiert werden'},
            en:{report_pdf_unavailable:'Cannot generate PDF',datatable_unavailable:'Cannot initialize table'},
            es:{report_pdf_unavailable:'No se puede generar el PDF',datatable_unavailable:'No se puede inicializar la tabla'},
            fr:{report_pdf_unavailable:'Impossible de générer le PDF',datatable_unavailable:'Impossible d’initialiser le tableau'},
            he:{report_pdf_unavailable:'לא ניתן ליצור PDF',datatable_unavailable:'לא ניתן לאתחל טבלה'},
            it:{report_pdf_unavailable:'Impossibile generare il PDF',datatable_unavailable:'Impossibile inizializzare la tabella'},
            ja:{report_pdf_unavailable:'PDF を生成できません',datatable_unavailable:'テーブルを初期化できません'},
            nl:{report_pdf_unavailable:'Kan geen PDF genereren',datatable_unavailable:'Kan tabel niet initialiseren'},
            pl:{report_pdf_unavailable:'Nie można wygenerować PDF',datatable_unavailable:'Nie można zainicjować tabeli'},
            pt:{report_pdf_unavailable:'Não foi possível gerar o PDF',datatable_unavailable:'Não foi possível inicializar a tabela'},
            'pt-br':{report_pdf_unavailable:'Não foi possível gerar o PDF',datatable_unavailable:'Não foi possível inicializar a tabela'},
            ru:{report_pdf_unavailable:'Не удалось создать PDF',datatable_unavailable:'Не удалось инициализировать таблицу'},
            tr:{report_pdf_unavailable:'PDF oluşturulamadı',datatable_unavailable:'Tablo başlatılamıyor'},
            zh:{report_pdf_unavailable:'无法生成 PDF',datatable_unavailable:'无法初始化表格'}
        };
    </script>
    <script defer>
        (() => {
            const DATA_LISTENER_ADDED = 'data-listener-added';
            const ERR_FB = '# ERROR';
            const DATA_CLIENT_LOCALIZED = 'data-client-localized';
            const DATA_GUARD_MSG = 'data-guard-msg';

            const getLocalizedMessage = (el, key) => {
            let msg = ERR_FB;
            if (el?.getAttribute('data-sv-localized') === 'true' || el?.getAttribute(DATA_CLIENT_LOCALIZED) === 'true') {
                msg = el.getAttribute(DATA_GUARD_MSG) || ERR_FB;
            } else {
                let lang = (sessionStorage.getItem('erp-np-lang') || document.documentElement.lang || 'en').toLowerCase().replace(/_/g,'-');
                lang = lang === 'pt-br' ? lang : lang.slice(0,2);
                msg = window.translations?.[lang]?.[key] || el?.getAttribute(DATA_GUARD_MSG) || window.translations?.en?.[key] || ERR_FB;
                if (msg !== ERR_FB) { el?.setAttribute(DATA_GUARD_MSG, msg); el?.setAttribute(DATA_CLIENT_LOCALIZED, 'true'); }
            }
            return msg;
            };

            const handleErrorDisplay = (el, key) => {
            const message = getLocalizedMessage(el || document.body, key);
            const hasBootstrap = document.querySelector('link[href*="bootstrap"]') && window.bootstrap?.Toast;
            if (hasBootstrap) {
                if (!document.querySelector('#error-toast')) {
                const t = document.createElement('div');
                t.id = 'error-toast';
                t.className = 'toast align-items-center text-bg-danger border-0';
                t.setAttribute('role','alert'); t.setAttribute('aria-live','assertive'); t.setAttribute('aria-atomic','true');
                t.innerHTML = `<div class="d-flex"><div class="toast-body">${message}</div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button></div>`;
                document.body.appendChild(t);
                }
                new bootstrap.Toast(document.querySelector('#error-toast')).show();
            } else { alert(message); }
            };

            const attachPointerGuard = (el, key) => {
            if (!el || el.getAttribute(DATA_LISTENER_ADDED) === 'true') return;
            const onceHandler = () => handleErrorDisplay(el, key);
            el.addEventListener('pointerup', onceHandler, { once: true });
            el.setAttribute(DATA_LISTENER_ADDED, 'true');
            const mo = new MutationObserver((_, o) => { if (!document.body.contains(el)) { el.removeEventListener('pointerup', onceHandler); o.disconnect(); } });
            mo.observe(document.body, { childList: true, subtree: true });
            };

            try {
            if (typeof $ === 'undefined') { console.error('jQuery is required'); return; }

            const initialFilename = $('#filename').val() || 'report';
            window.saveAsPDF = () => {
                const el = document.getElementById('printableArea');
                try {
                if (!el || typeof html2pdf === 'undefined') throw new Error('html2pdf missing or target not found');
                const currentName = $('#filename').val() || initialFilename;
                const opt = { margin: 0.3, filename: currentName, image: { type: 'jpeg', quality: 1 }, html2canvas: { scale: 4, dpi: 72, letterRendering: true }, jsPDF: { unit: 'in', format: 'A2' } };
                html2pdf().set(opt).from(el).save();
                } catch (e) {
                console.error('PDF generation failed: library or target missing', e);
                attachPointerGuard(el || document.body, 'report_pdf_unavailable');
                }
            };

            $(() => {
                const $table = $('#reportTable');
                if (!$table.length) return;
                try {
                if (!$.fn.DataTable) { console.error('DataTables library is required'); attachPointerGuard($table.get(0), 'datatable_unavailable'); return; }
                if ($.fn.DataTable.isDataTable($table)) return;
                const currentName = $('#filename').val() || initialFilename;
                $table.DataTable({
                    dom: 'Bfrtip',
                    buttons: [
                    { extend: 'excelHtml5', title: currentName },
                    { extend: 'csvHtml5',   title: currentName },
                    { extend: 'pdfHtml5',   title: currentName }
                    ],
                    language: (typeof window.dataTabelLang !== 'undefined' && window.dataTabelLang) ? window.dataTabelLang : {}
                });
                } catch {
                attachPointerGuard($table.get(0), 'datatable_unavailable');
                }
            });

            } catch (e) {
            console.error('Initialization failed', e);
            }
        })();
    </script>
@endpush
@section(YieldingConstants::ADM_ACT_BTN)
    <div class="float-end">
        <a href="#" onclick="saveAsPDF();" class="btn btn-sm btn-primary dwn" data-toggle="tooltip" title="{{__('Project Report Download')}}">
            <i class="ti ti-download"></i>
        </a>
    </div>
@endsection
@section(YieldingConstants::ADM_CTT)
    <div class="row">
        <div class="col-sm-12">
            <div class="row" id="printableArea">
                <div class="{{ VC::CM6 }}">
                    <div class="{{ VC::CD }}">
                        <div class="card-header">
                            <h5>{{ __('Overview') }}</h5>
                        </div>
                        <div class="card-body" style="min-height: 280px;">
                            <div class="{{ VC::R_ALC }}">
                                <div class="col-7">
                                    <table class="{{ VC::TB }}">
                                        <tbody>
                                            <tr class="border-0">
                                                <th class="border-0">{{ __('Project Name') }}:</th>
                                                <td class="border-0">{{ $project->project_name }}</td>
                                            </tr>
                                            <tr>
                                                <th class="border-0">{{ __('Project Status') }}:</th>
                                                <td class="border-0">
                                                    @if($project[ProjectsConstants::COL_STAT] == ProjectsConstants::STT_INP_K)
                                                        <div class="{{ VC::BDG }} {{ VC::BG_P }} p-2 {{ VC::PX3 }} rounded">{{ __('In Progress') }}</div>
                                                    @elseif($project[ProjectsConstants::COL_STAT] == ProjectsConstants::STT_ONH_K)
                                                        <div class="{{ VC::BDG }} bg-secondary p-2 px-3 rounded">{{ __('On Hold') }}</div>
                                                    @elseif($project[ProjectsConstants::COL_STAT] == ProjectsConstants::STT_CPT_K)
                                                        <div class="{{ VC::BDG }} bg-danger p-2 px-3 rounded">{{ __('Canceled') }}</div>
                                                    @else
                                                        <div class="{{ VC::BDG }} bg-warning p-2 px-3 rounded">{{ __('Finished') }}</div>
                                                    @endif
                                                </td>
                                            </tr>
                                            <tr role="row">
                                                <th class="border-0">{{ __('Start Date') }}:</th>
                                                <td class="border-0">{{ $project->start_date }}</td>
                                            </tr>
                                            <tr>
                                                <th class="border-0">{{ __('End Date') }}:</th>
                                                <td class="border-0">{{ $project->end_date }}</td>
                                            </tr>
                                            <tr>
                                                <th class="border-0">{{ __('Total Members') }}:</th>
                                                <td class="border-0">{{ (int) $project->users->count() }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="col-5">
                                    @php
                                        $task_percentage = $project->projectProgress($project, $last_task->id)['percentage'];
                                        $data = trim($task_percentage, '%');
                                        $status = $data > 0 && $data <= 25 ? 'red'
                                                : ($data > 25 && $data <= 50 ? 'orange'
                                                : ($data > 50 && $data <= 75 ? 'blue'
                                                : ($data > 75 && $data <= 100 ? 'green' : '')));
                                    @endphp
                                    <div class="circular-progressbar p-0">
                                        <div class="flex-wrapper">
                                            <div class="single-chart">
                                                <svg viewBox="0 0 36 36" class="circular-chart orange {{ $status }}">
                                                    <path class="circle-bg" d="M18 2.0845
                                                                a 15.9155 15.9155 0 0 1 0 31.831
                                                                a 15.9155 15.9155 0 0 1 0 -31.831" />
                                                    <path class="circle"
                                                        stroke-dasharray="{{ $data }}, 100"
                                                        d="M18 2.0845
                                                                a 15.9155 15.9155 0 0 1 0 31.831
                                                                a 15.9155 15.9155 0 0 1 0 -31.831" />
                                                    <text x="18" y="20.35" class="percentage">{{ $data }}%</text>
                                                </svg>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @php
                    $mile_percentage = $project->projectMilestoneProgress()['percentage'];
                    $mile_percentage =trim($mile_percentage,'%');
                @endphp
                <div class="{{ VC::CM6 }}">
                    <div class="{{ VC::CD }}">
                        <div class="card-header" style="padding: 25px 35px !important;">
                            <div class="{{ VC::DFL_AIC_JCB }}">
                                <div class="{{ VC::RW }}">
                                    <h5 class="{{ VC::MB0 }}">{{ __('Milestone Progress') }}</h5>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="chart">
                                <div class="chartjs-size-monitor">
                                    <div class="chartjs-size-monitor-expand"><div class=""></div></div>
                                    <div class="chartjs-size-monitor-shrink"><div class=""></div></div>
                                </div>
                                <div id="milestone-chart" class="chart-canvas chartjs-render-monitor" height="150"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="{{ VC::CM3 }}">
                    <div class="{{ VC::CD }}">
                        <div class="card-header">
                            <div class="{{ VC::FEND }}">
                                <a href="#" data-bs-toggle="tooltip" data-bs-placement="top" title="Refferals"><i class=""></i></a>
                            </div>
                            <h5>{{ __('Task Priority') }}</h5>
                        </div>
                        <div class="card-body" style="min-height: 280px;">
                            <div class="{{ VC::RW }} {{ VC::ALC }}">
                                <div class="col-12">
                                    <div id="chart_priority"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="{{ VC::CD }}">
                        <div class="card-header">
                            <div class="{{ VC::FEND }}">
                                <a href="#" data-bs-toggle="tooltip" data-bs-placement="top" title="Refferals"><i class=""></i></a>
                            </div>
                            <h5>{{ __('Task Status') }}</h5>
                        </div>
                        <div class="card-body" style="min-height: 280px;">
                            <div class="{{ VC::RW }} {{ VC::ALC }}">
                                <div class="col-12">
                                    <div id="chart"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="{{ VC::CD }}">
                        <div class="card-header">
                            <div class="{{ VC::FEND }}">
                                <a href="#" data-bs-toggle="tooltip" data-bs-placement="top" title="Refferals"><i class=""></i></a>
                            </div>
                            <h5>{{ __('Hours Estimation') }}</h5>
                        </div>
                        <div class="card-body" style="min-height: 280px;">
                            <div class="{{ VC::RW }} {{ VC::ALC }}">
                                <div class="col-12">
                                    <div id="chart-hours"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @php
                    $lastStage=\App\Models\TaskStage::where(DatabaseConstants::TABLE_CREATOR,\Auth::user()->creatorId())->orderby('id','desc')->first();
                @endphp
                <div class="col-md-5">
                    <div class="{{ VC::CD }}">
                        <div class="card-header">
                            <h5>{{ __('Users') }}</h5>
                        </div>
                        <div class="card-body table-border-style">
                            <div class="table-responsive milestone">
                                <table class="{{ VC::TB }}">
                                    <thead>
                                        <tr>
                                            <th>{{ __('Name') }}</th>
                                            <th>{{ __('Assigned Tasks') }}</th>
                                            <th>{{ __('Done Tasks') }}</th>
                                            <th>{{ __('Logged Hours') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($project->users as $user)
                                            @php
                                                $hours_format_number = 0;
                                                $total_hours = 0;
                                                $hourdiff_late = 0;
                                                $esti_late_hour = 0;
                                                $esti_late_hour_chart = 0;

                                                $total_user_task = ProjectTask::where(ProjectsConstants::COL_PJ_ID, $project->id)
                                                    ->whereRaw("FIND_IN_SET(?,  ".ProjectsConstants::COL_ASGN.") > 0", [$user?->id])
                                                    ->count();

                                                $all_task = ProjectTask::where(ProjectsConstants::COL_PJ_ID, $project->id)
                                                    ->whereRaw("FIND_IN_SET(?,  ".ProjectsConstants::COL_ASGN.") > 0", [$user?->id])
                                                    ->get();

                                                $total_complete_task = ProjectTask::where(ProjectsConstants::COL_PJ_ID, $project->id)
                                                    ->where('stage_id', $lastStage->id)
                                                    ->where(ProjectsConstants::COL_ASGN, $user?->id)
                                                    ->count();

                                                $logged_hours = 0;
                                                $timesheets = App\Models\Timesheet::where(ProjectsConstants::COL_PJ_ID, $project->id)
                                                    ->where(DatabaseConstants::TABLE_CREATOR, $user?->id)
                                                    ->get();
                                            @endphp

                                            @foreach($timesheets as $timesheet)
                                                @php
                                                    $hours = date('H', strtotime($timesheet->time));
                                                    $minutes = date('i', strtotime($timesheet->time));
                                                    $total_hours = $hours + ($minutes/60);
                                                    $logged_hours += $total_hours;
                                                    $hours_format_number = number_format($logged_hours, 2, '.', '');
                                                @endphp
                                            @endforeach

                                            <tr>
                                                <td>{{ $user?->name }}</td>
                                                <td>{{ $total_user_task }}</td>
                                                <td>{{ $total_complete_task }}</td>
                                                <td>{{ $hours_format_number }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-7">
                    <div class="{{ VC::CD }}">
                        <div class="card-header">
                            <h5>{{ __('Milestones') }}</h5>
                        </div>
                        <div class="card-body table-border-style">
                            <div class="table-responsive milestone">
                                <table class="{{ VC::TB }}">
                                    <thead>
                                        <tr>
                                            <th>{{ __('Name') }}</th>
                                            <th>{{ __('Progress') }}</th>
                                            <th>{{ __('Cost') }}</th>
                                            <th>{{ __('Status') }}</th>
                                            <th>{{ __('Start Date') }}</th>
                                            <th>{{ __('End Date') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($project->milestones as $milestone)
                                            <tr>
                                                <td>{{ $milestone->title }}</td>
                                                <td>
                                                    <div class="progress_wrapper">
                                                        <div class="{{ VC::PG }}">
                                                            <div class="progress-bar" role="progressbar"
                                                                style="width: {{ $milestone->progress }}px;"
                                                                aria-valuenow="55" aria-valuemin="0" aria-valuemax="100">
                                                            </div>
                                                        </div>
                                                        <div class="progress_labels">
                                                            <div class="total_progress">
                                                                <strong>{{ $milestone->progress }}%</strong>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>{{ $milestone->cost }}</td>
                                                <td>
                                                    @if($milestone[ProjectsConstants::COL_STAT] == ProjectsConstants::STT_CPT_K)
                                                        <label class="{{ VC::BDG }} bg-success p-2 {{ VC::PX3 }} rounded">{{ __('Complete') }}</label>
                                                    @else
                                                        <label class="{{ VC::BDG }} bg-warning p-2 {{ VC::PX3 }} rounded">{{ __('Incomplete') }}</label>
                                                    @endif
                                                </td>
                                                <td>{{ $milestone->start_date }}</td>
                                                <td>{{ $milestone->due_date }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-12">
                <div class="col-md-12 {{ VC::RW }} {{ VC::DFL_AIC }} {{ VC::JCE }}">
                    <div class="col-1">
                        <button class="{{ VC::BT_PRM }} mx-2 btn-filter apply">
                            @php
                                $projectReportExportBaseName = ViewsConstants::PRJ_RPT . '.export';
                                $projectReportExportKebabName = Str::kebab($projectReportExportBaseName);
                                $projectReportExportResolvedName = Route::has($projectReportExportBaseName) ? $projectReportExportBaseName : (Route::has($projectReportExportKebabName) ? $projectReportExportKebabName : null);
                                $projectReportExportUrl = $projectReportExportResolvedName ? route($projectReportExportResolvedName, $project->id) : '#';
                                $projectReportExportGuardMsg = Utility::fetchLinkMessage($lang, ViewsConstants::PRJ_RPT, 'project_report_export_route_unavailable') ?? 'Project report export route is unavailable. Please contact technical support or your domain administrator.';
                                $projectReportExportLinkId = 'project-report-export-link-' . $project->id;
                            @endphp
                            <a
                                id="{{ $projectReportExportLinkId }}"
                                href="{{ $projectReportExportUrl }}"
                                data-url="{{ $projectReportExportUrl }}"
                                data-guard-msg="{{ $projectReportExportGuardMsg }}"
                                class="{{ VC::TXT_WT }}"
                            >
                                {{ __('Export') }}
                            </a>
                            @push(StacksConstants::ADM_SCR_PG)
                                <script defer>
                                    (() => {
                                        const link = document.getElementById('{{ $projectReportExportLinkId }}');
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
                        </button>
                    </div>
                </div>
            </div>
            <div class="{{ VC::CS12 }} {{ VC::MT3 }}">
                <div class="{{ VC::CD }}">
                    <div class="card-body {{ VC::MT3 }} mx-2">
                        <div class="{{ VC::RW }} mt-2">
                            <div class="table-responsive">
                                <table class="{{ VC::TB }} datatable">
                                    <thead>
                                        <tr>
                                            <th>{{ __('Task Name') }}</th>
                                            <th>{{ __('Milestone') }}</th>
                                            <th>{{ __('Start Date') }}</th>
                                            <th>{{ __('End Date') }}</th>
                                            <th>{{ __('Assigned to') }}</th>
                                            <th>{{ __('Total Logged Hours') }}</th>
                                            <th>{{ __('Priority') }}</th>
                                            <th>{{ __('Stage') }}</th>
                                        </tr>
                                    </thead>
                                    @foreach($tasks as $task)
                                        @php
                                            $hours_format_number = 0;
                                            $total_hours = 0;
                                            $hourdiff_late = 0;
                                            $esti_late_hour = 0;
                                            $esti_late_hour_chart = 0;

                                            $total_user_task = ProjectTask::where(ProjectsConstants::COL_PJ_ID, $project->id)
                                                ->whereRaw("FIND_IN_SET(?, ".ProjectsConstants::COL_ASGN.") > 0", [$user?->id])
                                                ->count();

                                            $all_task = ProjectTask::where(ProjectsConstants::COL_PJ_ID, $project->id)
                                                ->whereRaw("FIND_IN_SET(?, ".ProjectsConstants::COL_ASGN.") > 0", [$user?->id])
                                                ->get();

                                            $total_complete_task = ProjectTask::join('task_stages','task_stages.id','=','project_tasks.stage_id')
                                                ->where('task_stages.project_id', $project->id)
                                                ->where('stage_id', 4)
                                                ->where(ProjectsConstants::COL_ASGN, $user?->id)
                                                ->count();

                                            $logged_hours = 0;
                                            $timesheets = App\Models\Timesheet::where(ProjectsConstants::COL_PJ_ID, $project->id)
                                                ->where('task_id', $task->id)
                                                ->get();
                                        @endphp

                                        @foreach($timesheets as $timesheet)
                                            @php
                                                $hours = date('H', strtotime($timesheet->time));
                                                $minutes = date('i', strtotime($timesheet->time));
                                                $total_hours = $hours + ($minutes / 60);
                                                $logged_hours += $total_hours;
                                                $hours_format_number = number_format($logged_hours, 2, '.', '');
                                            @endphp
                                        @endforeach

                                        <tbody>
                                            <tr>
                                                <td>
                                                    @php
                                                        use App\Config\Constants\{ViewsConstants, ViewClassNamesConstants as VC, StacksConstants};
                                                        use App\Models\Utility;
                                                        use Illuminate\Support\Facades\Route;
                                                        use Illuminate\Support\Str;
                                                        $lang = isset($lang) && $lang ? $lang : Utility::fetchUserLang();
                                                        $projectTaskShowBaseName = ViewsConstants::PRJ . '.tasks.show';
                                                        $projectTaskShowKebabName = Str::kebab($projectTaskShowBaseName);
                                                        $projectTaskShowResolvedName = Route::has($projectTaskShowBaseName) ? $projectTaskShowBaseName : (Route::has($projectTaskShowKebabName) ? $projectTaskShowKebabName : null);
                                                        $projectTaskShowUrl = $projectTaskShowResolvedName ? route($projectTaskShowResolvedName, [$project->id, $task->id]) : '#';
                                                        $projectTaskShowGuardMsg = Utility::fetchLinkMessage($lang, ViewsConstants::PRJ, 'project_tasks_show_route_unavailable') ?? 'Project tasks show route is unavailable. Please contact technical support or your domain administrator.';
                                                        $projectTaskShowLinkId = 'project-task-show-link-' . $project->id . '-' . $task->id;
                                                    @endphp
                                                    <a
                                                        id="{{ $projectTaskShowLinkId }}"
                                                        href="{{ $projectTaskShowUrl }}"
                                                        data-url="{{ $projectTaskShowUrl }}"
                                                        data-guard-msg="{{ $projectTaskShowGuardMsg }}"
                                                        data-size="md"
                                                        data-ajax-popup="true"
                                                        class="dropdown-item"
                                                        data-bs-original-title="{{ __('View') }}"
                                                    >
                                                        {{ $task->name }}
                                                    </a>
                                                    @push(StacksConstants::ADM_SCR_PG)
                                                        <script defer>
                                                            (() => {
                                                                const link = document.getElementById('{{ $projectTaskShowLinkId }}');
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
                                                </td>
                                                <td>{{ !empty($task->milestone) ? $task->milestone->title : '-' }}</td>
                                                <td>{{ $task->start_date }}</td>
                                                <td>{{ $task->end_date }}</td>
                                                <td>
                                                    <div class="avatar-group">
                                                        @if($task->users()->count() > 0)
                                                            @if($users = $task->users())
                                                                @foreach($users as $key => $user)
                                                                    @if($key < 3)
                                                                        <a href="#" class="{{ VC::AV_CC_SM }}">
                                                                            <img src="{{ $user->getImgImageAttribute() }}" title="{{ $user?->name }}">
                                                                        </a>
                                                                    @else
                                                                        @break
                                                                    @endif
                                                                @endforeach
                                                            @endif
                                                            @if(count($users) > 3)
                                                                <a href="#" class="{{ VC::AV_CC_SM }}">
                                                                    <img src="{{ $user?->getImgImageAttribute() }}">
                                                                </a>
                                                            @endif
                                                        @else
                                                            {{ __('-') }}
                                                        @endif
                                                    </div>
                                                </td>
                                                <td>{{ $hours_format_number }}</td>
                                                <td>
                                                    <span class="{{ VC::BDG }} p-2 {{ VC::PX3 }} status_badge rounded bg-{{ ProjectTask::$priority_color[$task->priority] }}">
                                                        {{ ProjectTask::$priority[$task->priority] }}
                                                    </span>
                                                </td>
                                                <td>{{ $task->stage->name }}</td>
                                            </tr>
                                        </tbody>
                                    @endforeach
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push(StacksConstants::ADM_SCR_PG)
    <script src="{{ asset('assets/js/datatables.min.js') }}"></script>
    <script async src="{{ asset('assets/js/plugins/apexcharts.min.js')}}"></script>
    <script>
        window.translations = {
            ar:{pdf_unavailable:'تعذّر إنشاء ملف PDF',chart_milestone_unavailable:'تعذّر عرض مخطط الإنجاز',chart_priority_unavailable:'تعذّر عرض مخطط الأولوية',chart_pie_unavailable:'تعذّر عرض المخطط الدائري',chart_hours_unavailable:'تعذّر عرض مخطط الساعات'},
            da:{pdf_unavailable:'Kunne ikke generere PDF',chart_milestone_unavailable:'Kunne ikke vise milepælsdiagram',chart_priority_unavailable:'Kunne ikke vise prioritetsdiagram',chart_pie_unavailable:'Kunne ikke vise cirkeldiagram',chart_hours_unavailable:'Kunne ikke vise time-diagram'},
            de:{pdf_unavailable:'PDF konnte nicht erstellt werden',chart_milestone_unavailable:'Meilenstein-Diagramm konnte nicht angezeigt werden',chart_priority_unavailable:'Prioritätsdiagramm konnte nicht angezeigt werden',chart_pie_unavailable:'Kreisdiagramm konnte nicht angezeigt werden',chart_hours_unavailable:'Stundendiagramm konnte nicht angezeigt werden'},
            en:{pdf_unavailable:'Cannot generate PDF',chart_milestone_unavailable:'Cannot render milestone chart',chart_priority_unavailable:'Cannot render priority chart',chart_pie_unavailable:'Cannot render pie chart',chart_hours_unavailable:'Cannot render hours chart'},
            es:{pdf_unavailable:'No se puede generar el PDF',chart_milestone_unavailable:'No se puede renderizar el gráfico de hitos',chart_priority_unavailable:'No se puede renderizar el gráfico de prioridad',chart_pie_unavailable:'No se puede renderizar el gráfico circular',chart_hours_unavailable:'No se puede renderizar el gráfico de horas'},
            fr:{pdf_unavailable:'Impossible de générer le PDF',chart_milestone_unavailable:'Impossible d’afficher le graphique des jalons',chart_priority_unavailable:'Impossible d’afficher le graphique des priorités',chart_pie_unavailable:'Impossible d’afficher le camembert',chart_hours_unavailable:'Impossible d’afficher le graphique des heures'},
            he:{pdf_unavailable:'לא ניתן ליצור PDF',chart_milestone_unavailable:'לא ניתן להציג תרשים אבני דרך',chart_priority_unavailable:'לא ניתן להציג תרשים עדיפויות',chart_pie_unavailable:'לא ניתן להציג תרשים עוגה',chart_hours_unavailable:'לא ניתן להציג תרשים שעות'},
            it:{pdf_unavailable:'Impossibile generare il PDF',chart_milestone_unavailable:'Impossibile renderizzare il grafico delle milestone',chart_priority_unavailable:'Impossibile renderizzare il grafico priorità',chart_pie_unavailable:'Impossibile renderizzare il grafico a torta',chart_hours_unavailable:'Impossibile renderizzare il grafico ore'},
            ja:{pdf_unavailable:'PDF を生成できません',chart_milestone_unavailable:'マイルストーンチャートを表示できません',chart_priority_unavailable:'優先度チャートを表示できません',chart_pie_unavailable:'円グラフを表示できません',chart_hours_unavailable:'時間チャートを表示できません'},
            nl:{pdf_unavailable:'Kan geen PDF genereren',chart_milestone_unavailable:'Kan mijlpaaldiagram niet weergeven',chart_priority_unavailable:'Kan prioriteitsdiagram niet weergeven',chart_pie_unavailable:'Kan cirkeldiagram niet weergeven',chart_hours_unavailable:'Kan uren­diagram niet weergeven'},
            pl:{pdf_unavailable:'Nie można wygenerować PDF',chart_milestone_unavailable:'Nie można wyświetlić wykresu kamieni milowych',chart_priority_unavailable:'Nie można wyświetlić wykresu priorytetów',chart_pie_unavailable:'Nie można wyświetlić wykresu kołowego',chart_hours_unavailable:'Nie można wyświetlić wykresu godzin'},
            pt:{pdf_unavailable:'Não foi possível gerar o PDF',chart_milestone_unavailable:'Não foi possível renderizar o gráfico de marcos',chart_priority_unavailable:'Não foi possível renderizar o gráfico de prioridade',chart_pie_unavailable:'Não foi possível renderizar o gráfico de pizza',chart_hours_unavailable:'Não foi possível renderizar o gráfico de horas'},
            'pt-br':{pdf_unavailable:'Não foi possível gerar o PDF',chart_milestone_unavailable:'Não foi possível renderizar o gráfico de marcos',chart_priority_unavailable:'Não foi possível renderizar o gráfico de prioridade',chart_pie_unavailable:'Não foi possível renderizar o gráfico de pizza',chart_hours_unavailable:'Não foi possível renderizar o gráfico de horas'},
            ru:{pdf_unavailable:'Не удалось создать PDF',chart_milestone_unavailable:'Не удалось отобразить диаграмму этапов',chart_priority_unavailable:'Не удалось отобразить диаграмму приоритетов',chart_pie_unavailable:'Не удалось отобразить круговую диаграмму',chart_hours_unavailable:'Не удалось отобразить диаграмму часов'},
            tr:{pdf_unavailable:'PDF oluşturulamadı',chart_milestone_unavailable:'Kilometre taşı grafiği oluşturulamadı',chart_priority_unavailable:'Öncelik grafiği oluşturulamadı',chart_pie_unavailable:'Pasta grafiği oluşturulamadı',chart_hours_unavailable:'Saat grafiği oluşturulamadı'},
            zh:{pdf_unavailable:'无法生成 PDF',chart_milestone_unavailable:'无法渲染里程碑图',chart_priority_unavailable:'无法渲染优先级图',chart_pie_unavailable:'无法渲染饼图',chart_hours_unavailable:'无法渲染工时图'}
        };
    </script>
    <script async>
        (()=>{
            const errFb='# ERROR';
            const dataClientLocalized='data-client-localized';
            const dataGuardMsg='data-guard-msg';
            const dataListenerAdded='data-listener-added';
            const dataRendered='data-chart-rendered';

            const getLocalizedMessage=(el,msgKey)=>{
            let msg=errFb;
            if(el?.getAttribute('data-sv-localized')==='true'||el?.getAttribute(dataClientLocalized)==='true'){
                msg=el.getAttribute(dataGuardMsg)||errFb;
            }else{
                let lang=(window.sessionStorage.getItem('erp-np-lang')||document.documentElement.lang||'en').toLowerCase().replace(/_/g,'-');
                lang = (lang==='pt-br') ? lang : lang.slice(0,2);
                msg = window.translations?.[lang]?.[msgKey] || el?.getAttribute?.(dataGuardMsg) || window.translations?.en?.[msgKey] || errFb;
                if(msg!==errFb){ el?.setAttribute?.(dataGuardMsg,msg); el?.setAttribute?.(dataClientLocalized,'true'); }
            }
            return msg;
            };

            const showToast=(text)=>{
            const hasBootstrap=document.querySelector('link[href*="bootstrap"]') && window.bootstrap?.Toast;
            if(hasBootstrap){
                if(!document.querySelector('#error-toast')){
                const t=document.createElement('div');
                t.id='error-toast';
                t.className='toast align-items-center text-bg-danger border-0';
                t.setAttribute('role','alert'); t.setAttribute('aria-live','assertive'); t.setAttribute('aria-atomic','true');
                t.innerHTML=`<div class="d-flex"><div class="toast-body">${text}</div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button></div>`;
                document.body.appendChild(t);
                }
                new bootstrap.Toast(document.querySelector('#error-toast')).show();
            }else{ alert(text); }
            };

            const attachGuardOnce=(el,key,ev='click')=>{
            if(!el||el.getAttribute(dataListenerAdded)==='true') return;
            const handler=()=>showToast(getLocalizedMessage(el,key));
            el.addEventListener(ev,handler,{once:true});
            el.setAttribute(dataListenerAdded,'true');
            const mo=new MutationObserver((_,o)=>{ if(!document.body.contains(el)){ el.removeEventListener(ev,handler); o.disconnect(); }});
            mo.observe(document.body,{childList:true,subtree:true});
            };

            const renderChart=(selector, options, key)=>{
            try{
                if(typeof ApexCharts==='undefined'){ console.error('ApexCharts not loaded'); attachGuardOnce(document.querySelector(selector)||document.body,key); return; }
                const el=document.querySelector(selector);
                if(!el){ attachGuardOnce(document.body,key); return; }
                if(el.getAttribute(dataRendered)==='true') return;
                const chart=new ApexCharts(el, options);
                chart.render();
                el.setAttribute(dataRendered,'true');
            }catch{ attachGuardOnce(document.querySelector(selector)||document.body,key); }
            };

            // Expose PDF function
            window.saveAsPDF=()=>{
            const el=document.getElementById('printableArea');
            try{
                if(!window.html2pdf || !el){ throw new Error('html2pdf missing or target not found'); }
                const $nameSrc = $('#chart-hours');
                const fileName = (typeof $nameSrc.val==='function' && $nameSrc.val()) || ($nameSrc.text && $nameSrc.text()) || 'report';
                const opt={ margin:0.3, filename:fileName, image:{type:'jpeg',quality:1}, html2canvas:{scale:4,dpi:72,letterRendering:true}, jsPDF:{unit:'in',format:'A2'} };
                html2pdf().set(opt).from(el).save();
            }catch{ attachGuardOnce(el||document.body,'pdf_unavailable','pointerup'); }
            };

            // Render charts after DOM ready
            $(()=>{
            // Milestone radialBar (semicircle)
            const optionsMilestone={
                series: {!! json_encode($mile_percentage) !!},
                chart:{ height:475, type:'radialBar', offsetY:-20, sparkline:{enabled:true} },
                plotOptions:{ radialBar:{ startAngle:-90, endAngle:90, track:{ background:'#e7e7e7', strokeWidth:'97%', margin:5 }, dataLabels:{ name:{show:true}, value:{offsetY:-50,fontSize:'20px'} } } },
                grid:{ padding:{ top:-10 } },
                colors:['#51459d'],
                labels:['Progress']
            };
            renderChart('#milestone-chart', optionsMilestone, 'chart_milestone_unavailable');

            // Priority bar
            const optionsPriority={
                series:[{ data: {!! json_encode($arrProcessPer_priority) !!} }],
                chart:{ height:210, type:'bar' },
                colors:['#6fd943','#ff3a6e','#3ec9d6'],
                plotOptions:{ bar:{ columnWidth:'50%', distributed:true } },
                dataLabels:{ enabled:false },
                legend:{ show:true },
                xaxis:{ categories:{!! json_encode($arrProcess_Label_priority) !!}, labels:{ style:{ colors:{!! json_encode($chartData['color']) !!} } } }
            };
            renderChart('#chart_priority', optionsPriority, 'chart_priority_unavailable');

            // Status pie
            const optionsPie={
                series: {!! json_encode($arrProcessPer_status_task) !!},
                chart:{ width:380, type:'pie' },
                colors: {!! json_encode($chartData['color']) !!},
                labels: {!! json_encode($arrProcess_Label_status_tasks) !!},
                responsive:[{ breakpoint:480, options:{ chart:{ width:100 }, legend:{ position:'bottom' } } }]
            };
            renderChart('#chart', optionsPie, 'chart_pie_unavailable');

            // Hours bar (horizontal)
            const optionsHours={
                series:[{ data:[{!! json_encode($esti_logged_hour_chart) !!},{!! json_encode($logged_hour_chart) !!}] }],
                chart:{ height:210, type:'bar' },
                colors:['#963aff','#ffa21d'],
                plotOptions:{ bar:{ horizontal:true, columnWidth:'30%', distributed:true } },
                dataLabels:{ enabled:false },
                legend:{ show:true },
                xaxis:{ categories:['Estimated Hours','Logged Hours '] }
            };
            renderChart('#chart-hours', optionsHours, 'chart_hours_unavailable');
            });
        })();
    </script>
    <script defer>
        (() => {
            const link = document.getElementById('project-report-index-link');
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
                        toast.setAttribute('role', 'alert');
                        toast.setAttribute('aria-live', 'assertive');
                        toast.setAttribute('aria-atomic', 'true');
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
