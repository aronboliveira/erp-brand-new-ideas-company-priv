@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        PermissionsConstants,
        ProjectsConstants,
        StacksConstants
        YieldingConstants,
    };
    use Illuminate\Support\Facades\Route;
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@section(YieldingConstants::ADM_PG_TTL) {{__('Gantt Chart')}} @endsection

@section(YieldingConstants::ADM_BDC)
    <li class="breadcrumb-item">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
        {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="breadcrumb-item"><a href="{{route('projects.index')}}">{{__('Project')}}</a></li>
    <li class="breadcrumb-item"><a href="{{route('projects.show',$project->id)}}">    {{ucwords($project->project_name)}}</a></li>
    <li class="breadcrumb-item">{{__('Gantt Chart')}}</li>
@endsection

@section(YieldingConstants::ADM_ACT_BTN)
    @php
        $durations=['Quarter Day','Half Day','Day','Week','Month'];
    @endphp
    <div class="{{ ViewClassNamesConstants::FEND }}">
        <div class="btn-group {{ ViewClassNamesConstants::MR2 }}" id="change_view" role="group">
            @foreach($durations as $d)
                <a href="{{ route(ViewsConstants::PRJ.'.gantt',[$project->id,$d]) }}"
                class="{{ ViewClassNamesConstants::BT_SM_PM }} {{ $duration===$d?'active':'' }}"
                data-value="{{ $d }}">{{ __($d) }}</a>
            @endforeach
        </div>
        @can(PermissionsConstants::MNG_PRJ)
            <a href="{{ route(ViewsConstants::PRJ.'.show',$project->id) }}"
            class="{{ ViewClassNamesConstants::BT_SM_PM }}"
            data-bs-toggle="tooltip"
            title="{{ __('Back') }}">
                <span class="btn-inner--icon"><i class="{{ ViewClassNamesConstants::TI }} ti-arrow-left"></i></span>
            </a>
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
