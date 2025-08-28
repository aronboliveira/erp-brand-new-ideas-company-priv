@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        PermissionsConstants,
        ProjectsConstants,
        StacksConstants,
        ViewsConstants,
        ViewClassNamesConstants as VC,
        YieldingConstants,
    };
    use App\Models\Utility;
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\Str;
    $lang = Utility::fetchUserLang();
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@section(YieldingConstants::ADM_PG_TTL) {{__('Gantt Chart')}} @endsection
@section(YieldingConstants::ADM_BDC)
    @php
        $dashboardBaseName = 'dashboard';
        $dashboardKebabName = Str::kebab($dashboardBaseName);
        $dashboardResolvedName = Route::has($dashboardBaseName) ? $dashboardBaseName : (Route::has($dashboardKebabName) ? $dashboardKebabName : null);
        $dashboardUrl = $dashboardResolvedName ? route($dashboardResolvedName) : '#';
        $dashboardLinkId = 'dashboard-breadcrumb-link';
        $dashboardGuardMsg = Utility::fetchLinkMessage($lang, 'generics', 'dashboard_unavailable') ?? 'Dashboard route is unavailable. Please contact technical support or your domain administrator.';
        $projectIdVal = isset($project) && !empty(data_get($project, 'id')) ? data_get($project, 'id') : null;
        $projectNameVal = isset($project) && !empty(data_get($project, 'project_name')) ? data_get($project, 'project_name') : '';
        $prjIndexBaseName = VW::PRJ . '.index';
        $prjIndexKebabName = Str::kebab($prjIndexBaseName);
        $prjIndexResolvedName = Route::has($prjIndexBaseName) ? $prjIndexBaseName : (Route::has($prjIndexKebabName) ? $prjIndexKebabName : null);
        $prjIndexUrl = $prjIndexResolvedName ? route($prjIndexResolvedName) : '#';
        $prjIndexLinkId = 'projects-index-breadcrumb-link';
        $prjIndexGuardMsg = Utility::fetchLinkMessage($lang, VW::PRJ, 'project_index_route_unavailable') ?? 'Index project route is unavailable. Please contact technical support or your domain administrator.';
        $prjShowBaseName = VW::PRJ . '.show';
        $prjShowKebabName = Str::kebab($prjShowBaseName);
        $prjShowResolvedName = Route::has($prjShowBaseName) ? $prjShowBaseName : (Route::has($prjShowKebabName) ? $prjShowKebabName : null);
        $prjShowParams = $projectIdVal ? [$projectIdVal] : ['#'];
        $prjShowUrl = ($prjShowResolvedName && $projectIdVal) ? route($prjShowResolvedName, $prjShowParams) : '#';
        $prjShowLinkId = 'projects-show-breadcrumb-link-' . ($projectIdVal ?? 'x');
        $prjShowGuardMsg = Utility::fetchLinkMessage($lang, VW::PRJ, 'show_project_route_unavailable') ?? 'Show project route is unavailable. Please contact technical support or your domain administrator.';
    @endphp
    <li class="breadcrumb-item">
        <a href="{{ $dashboardUrl }}" id="{{ $dashboardLinkId }}" data-url="{{ $dashboardUrl }}" data-guard-msg="{{ $dashboardGuardMsg }}" {{ $dashboardUrl === '#' ? 'aria-disabled=true' : '' }}>{{ __('Dashboard') }}</a>
    </li>
    <li class="breadcrumb-item">
        <a href="{{ $prjIndexUrl }}" id="{{ $prjIndexLinkId }}" data-url="{{ $prjIndexUrl }}" data-guard-msg="{{ $prjIndexGuardMsg }}" {{ $prjIndexUrl === '#' ? 'aria-disabled=true' : '' }}>{{ __('Project') }}</a>
    </li>
    <li class="breadcrumb-item">
        <a href="{{ $prjShowUrl }}" id="{{ $prjShowLinkId }}" data-url="{{ $prjShowUrl }}" data-guard-msg="{{ $prjShowGuardMsg }}" {{ $prjShowUrl === '#' ? 'aria-disabled=true' : '' }}>{{ ucwords($projectNameVal) }}</a>
    </li>
    <li class="breadcrumb-item">{{ __('Gantt Chart') }}</li>
    @push(StacksConstants::ADM_SCR_PG)
        <script>
            (() => {
                try {
                    const ids = ['{{ $dashboardLinkId }}','{{ $prjIndexLinkId }}','{{ $prjShowLinkId }}'];
                    for (let i = 0; i < ids.length; i++) {
                        try {
                            const el = document.getElementById(ids[i]);
                            if (!el) continue;
                            const flag = 'data-breadcrumb-listener';
                            if (el.hasAttribute(flag) && el.getAttribute(flag) === 'true') continue;
                            el.setAttribute(flag, 'true');
                            el.addEventListener('click', function (e) {
                                try {
                                    const href = el.getAttribute('href') || '#';
                                    const url = el.getAttribute('data-url') || href || '#';
                                    if (href !== '#' || url !== '#') return;
                                    e.preventDefault();
                                    const msg = el.getAttribute('data-guard-msg') || 'Requested route is unavailable. Please contact technical support or your domain administrator.';
                                    const hasBootstrap = document.querySelector('link[href*="bootstrap"]') && window.bootstrap && window.bootstrap.Toast);
                                    let container = document.getElementById('toast-container');
                                    if (!container) {
                                        container = document.createElement('div');
                                        container.id = 'toast-container';
                                        container.className = 'position-fixed top-0 end-0 p-3';
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
                                        const inst = window.bootstrap.Toast.getOrCreateInstance(toast);
                                        toast.addEventListener('hidden.bs.toast', function () { try { toast.remove(); } catch (err) {} });
                                        inst.show();
                                    } else {
                                        alert(msg);
                                    }
                                    el.setAttribute('data-failed-route', 'true');
                                } catch (err) {}
                            }, { passive: false });
                        } catch (innerErr) {}
                    }
                } catch (error) {}
            })();
        </script>
    @endpush
@endsection

@section(YieldingConstants::ADM_ACT_BTN)
    @php
        $durations = ['Quarter Day','Half Day','Day','Week','Month'];
    @endphp
    <div class="{{ VC::FEND }}">
        <div class="btn-group {{ VC::MR2 }}" id="change_view" role="group">
            @php
                $projectIdVal = isset($project) && !empty(data_get($project, 'id')) ? data_get($project, 'id') : null;
                $ganttBaseName = VW::PRJ . '.gantt';
                $ganttKebabName = Str::kebab($ganttBaseName);
                $ganttResolvedName = Route::has($ganttBaseName) ? $ganttBaseName : (Route::has($ganttKebabName) ? $ganttKebabName : null);
                $ganttGuardMsg = Utility::fetchLinkMessage($lang, VW::PRJ, 'gantt_project_unavailable') ?? 'Gantt project route is unavailable. Please contact technical support or your domain administrator.';
            @endphp
            @foreach($durations as $d)
                @php
                    $dSlug = Str::slug($d, '-');
                    $ganttLinkId = 'projects-gantt-link-' . $dSlug . '-' . ($projectIdVal ?? 'x');
                    $ganttParams = ($projectIdVal && !empty($d)) ? [$projectIdVal, $d] : ['#'];
                    $ganttUrl = ($ganttResolvedName && $projectIdVal && !empty($d)) ? route($ganttResolvedName, $ganttParams) : '#';
                    $isActive = isset($duration) && $duration === $d;
                @endphp
                <a href="{{ $ganttUrl }}" id="{{ $ganttLinkId }}" class="{{ VC::BT_SM_PM }} {{ $isActive ? 'active' : '' }}" data-url="{{ $ganttUrl }}" data-value="{{ $d }}" data-guard-msg="{{ $ganttGuardMsg }}">{{ __($d) }}</a>
            @endforeach
        </div>
        @can(PermissionsConstants::MNG_PRJ)
            @php
                $backBaseName = VW::PRJ . '.show';
                $backKebabName = Str::kebab($backBaseName);
                $backResolvedName = Route::has($backBaseName) ? $backBaseName : (Route::has($backKebabName) ? $backKebabName : null);
                $backParams = $projectIdVal ? [$projectIdVal] : ['#'];
                $backUrl = ($backResolvedName && $projectIdVal) ? route($backResolvedName, $backParams) : '#';
                $backLinkId = 'projects-show-back-link-' . ($projectIdVal ?? 'x');
                $backGuardMsg = Utility::fetchLinkMessage($lang, VW::PRJ, 'show_project_route_unavailable') ?? 'Show project route is unavailable. Please contact technical support or your domain administrator.';
            @endphp
            <a href="{{ $backUrl }}" id="{{ $backLinkId }}" class="{{ VC::BT_SM_PM }}" data-url="{{ $backUrl }}" data-bs-toggle="tooltip" title="{{ __('Back') }}" data-guard-msg="{{ $backGuardMsg }}"><span class="btn-inner--icon"><i class="{{ VC::TI }} ti-arrow-left"></i></span></a>
            @push(StacksConstants::ADM_SCR_PG)
                <script>
                    (() => {
                        try {
                            const ganttEls = document.querySelectorAll('a[id^="projects-gantt-link-"]');
                            if (ganttEls && ganttEls.length) {
                                for (let i = 0; i < ganttEls.length; i++) {
                                    try {
                                        const el = ganttEls[i];
                                        const flag = 'data-gantt-listener';
                                        if (el.hasAttribute(flag) && el.getAttribute(flag) === 'true') continue;
                                        el.setAttribute(flag, 'true');
                                        el.addEventListener('click', function (e) {
                                            try {
                                                const href = el.getAttribute('href') || '#';
                                                const url = el.getAttribute('data-url') || href || '#';
                                                if (href !== '#' || url !== '#') return;
                                                e.preventDefault();
                                                const msg = el.getAttribute('data-guard-msg') || 'Gantt project route is unavailable. Please contact technical support or your domain administrator.';
                                                const hasBootstrap = document.querySelector('link[href*="bootstrap"]') && window.bootstrap && window.bootstrap.Toast);
                                                let container = document.getElementById('toast-container');
                                                if (!container) {
                                                    container = document.createElement('div');
                                                    container.id = 'toast-container';
                                                    container.className = 'position-fixed top-0 end-0 p-3';
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
                                                    const inst = window.bootstrap.Toast.getOrCreateInstance(toast);
                                                    toast.addEventListener('hidden.bs.toast', function () { try { toast.remove(); } catch (err) {} });
                                                    inst.show();
                                                } else {
                                                    alert(msg);
                                                }
                                                el.setAttribute('data-failed-route', 'true');
                                            } catch (err) {}
                                        }, { passive: false });
                                    } catch (loopErr) {}
                                }
                            }
                            const backEl = document.getElementById('{{ $backLinkId }}');
                            if (backEl) {
                                const flag = 'data-back-listener';
                                if (!(backEl.hasAttribute(flag) && backEl.getAttribute(flag) === 'true')) {
                                    backEl.setAttribute(flag, 'true');
                                    backEl.addEventListener('click', function (e) {
                                        try {
                                            const href = backEl.getAttribute('href') || '#';
                                            const url = backEl.getAttribute('data-url') || href || '#';
                                            if (href !== '#' || url !== '#') return;
                                            e.preventDefault();
                                            const msg = backEl.getAttribute('data-guard-msg') || 'Show project route is unavailable. Please contact technical support or your domain administrator.';
                                            const hasBootstrap = document.querySelector('link[href*="bootstrap"]') && window.bootstrap && window.bootstrap.Toast);
                                            let container = document.getElementById('toast-container');
                                            if (!container) {
                                                container = document.createElement('div');
                                                container.id = 'toast-container';
                                                container.className = 'position-fixed top-0 end-0 p-3';
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
                                                const inst = window.bootstrap.Toast.getOrCreateInstance(toast);
                                                toast.addEventListener('hidden.bs.toast', function () { try { toast.remove(); } catch (err) {} });
                                                inst.show();
                                            } else {
                                                alert(msg);
                                            }
                                            backEl.setAttribute('data-failed-route', 'true');
                                        } catch (err) {}
                                    }, { passive: false });
                                }
                            }
                        } catch (error) {}
                    })();
                </script>
            @endpush
        @endcan
    </div>
@endsection


@section(YieldingConstants::ADM_CTT)
    <div class="row">
        <div class="col-12">
            <div class="card card-stats border-0">
                <div class="card-body"></div>
                @if($project)
                    <div class="gantt-target"></div>
                @else
                    <h1>404</h1>
                    <div class="page-description">
                        {{ __('Page Not Found') }}
                    </div>
                    <div class="page-search">
                        <p class="text-muted mt-3">{{ __("It's looking like you may have taken a wrong turn. Don't worry... it happens to the best of us. Here's a little tip that might help you get back on track.")}}</p>
                        <div class="mt-3">
                            <a class="btn-return-home badge-blue" href="{{route('home')}}"><i class="ti ti-reply"></i> {{ __('Return Home')}}</a>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
@if($project)
    @push(StacksConstants::ADM_CSS)
        <link rel="stylesheet" href="{{asset('css/frappe-gantt.css')}}" />
    @endpush
    @push(StacksConstants::ADM_SCR_PG)
        @php
            $currentLang = basename(App::getLocale());
        @endphp
        <script async>
            const month_names = {
                "{{$currentLang}}": [
                    '{{__('January')}}',
                    '{{__('February')}}',
                    '{{__('March')}}',
                    '{{__('April')}}',
                    '{{__('May')}}',
                    '{{__('June')}}',
                    '{{__('July')}}',
                    '{{__('August')}}',
                    '{{__('September')}}',
                    '{{__('October')}}',
                    '{{__('November')}}',
                    '{{__('December')}}'
                ],
                "en": [
                    'January',
                    'February',
                    'March',
                    'April',
                    'May',
                    'June',
                    'July',
                    'August',
                    'September',
                    'October',
                    'November',
                    'December'
                ],
            };
        </script>
        <script src="{{asset('js/frappe-gantt.js')}}"></script>
        <script defer>
            (() => {
              const errFb = '# ERROR';
              const dataClientLocalized = 'data-client-localized';
              const dataGuardMsg = 'data-guard-msg';
              const langSessionKey = 'erp-np-lang';
              const getLocalizedMessage = (msgKey, el) => {
                let msg = errFb;
                if (el.getAttribute('data-sv-localized') === 'true' || el.getAttribute(dataClientLocalized) === 'true') {
                  msg = el.getAttribute(dataGuardMsg) ?? errFb;
                } else {
                  let lang = (window.sessionStorage.getItem(langSessionKey) ?? document.documentElement.lang ?? 'en')
                    .toLowerCase().replace(/_/g, '-');
                  lang = lang === 'pt-br' ? lang : lang.slice(0, 2);
                  msg = window.translations?.[lang]?.[msgKey] ??
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
                    const hasBs = Array.from(document.querySelectorAll('link[rel="stylesheet"]'))
                      .some(l => /bootstrap/i.test(l.href)) && window.bootstrap?.Toast;
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
                        toast.addEventListener('click', () => body.textContent = message);
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
              const container = document.querySelector('.gantt-target');
              if (!container || container.dataset.ganttInitiated === 'true') return;
              container.dataset.ganttInitiated = 'true';
              let errorMessage = '';
              const onErrorPointerUp = () => {
                if (errorMessage) {
                  showError(errorMessage);
                  errorMessage = '';
                }
              };
              container.addEventListener('pointerup', onErrorPointerUp);
              new MutationObserver((ms, o) => {
                ms.forEach(m => m.removedNodes.forEach(n => {
                  if (n === container) {
                    container.removeEventListener('pointerup', onErrorPointerUp);
                    o.disconnect();
                  }
                }));
              }).observe(document.body, { childList: true, subtree: true });
              try {
                const tasks = JSON.parse('{!! addslashes(json_encode($tasks)) !!}') ?? [];
                new Gantt('.gantt-target', tasks, {
                  custom_popup_html: task => {
                    let statusClass = '{{ ProjectsConstants::STT_SCS }}';
                    if (task.custom_class === 'medium') statusClass = '{{ ProjectsConstants::STT_INF }}';
                    else if (task.custom_class === 'high') statusClass = '{{ ProjectsConstants::STT_DGR }}';
                    return `<div class="details-container">
                                <div class="title">${task.name} <span class="badge badge-${statusClass} float-right">${task.extra.priority}</span></div>
                                <div class="subtitle">
                                    <b>${task.progress}%</b> {{ __('Progress') }} <br>
                                    <b>${task.extra.comments}</b> {{ __('Comments') }} <br>
                                    <b>{{ __('Duration') }}</b> ${task.extra.duration}
                                </div>
                            </div>`;
                  },
                  on_click: task => {},
                  on_date_change: (task, start, end) => {
                    try {
                      const { id: taskId } = task;
                      const s = moment(start);
                      const e = moment(end);
                      $.ajax({
                        url: '{{ route("projects.gantt.post",[$project->id]) }}',
                        type: 'POST',
                        data: {
                          start: s.format('YYYY-MM-DD HH:mm:ss'),
                          end: e.format('YYYY-MM-DD HH:mm:ss'),
                          task_id: taskId,
                          _token: document.querySelector('meta[name="csrf-token"]').getAttribute('content') ?? ''
                        }
                      }).fail(() => {
                        errorMessage = getLocalizedMessage('gantt_date_change_failed', container);
                      });
                    } catch {
                      errorMessage = getLocalizedMessage('gantt_date_change_failed', container);
                    }
                  },
                  view_mode: '{{$duration}}',
                  language: '{{$currentLang}}'
                });
              } catch {
                showError(getLocalizedMessage('gantt_init_failed', container));
              }
            })();
        </script>
    @endpush
@endif
