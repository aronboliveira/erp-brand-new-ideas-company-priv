@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        StacksConstants,
        ViewClassNamesConstants,
        YieldingConstants,
    };
    use Illuminate\Support\Facades\Route;
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@section(YieldingConstants::ADM_PG_TTL)
    {{__('Manage Job Application')}}
@endsection
@php
    $logo=\App\Models\Utility::getFile('uploads/avatar/');
    $profile=\App\Models\Utility::getFile('uploads/job/profile/');
@endphp
@push(StacksConstants::ADM_CSS)
    <link rel="stylesheet" href="{{ asset('assets/css/plugins/dragula.min.css') }}" id="main-style-link">
@endpush
@push(StacksConstants::ADM_SCR_PG)
    {{-- <script src="{{ asset('libs/dragula/dist/dragula.min.js') }}"></script>
    <script src="{{ asset('libs/autosize/dist/autosize.min.js') }}"></script> --}}
    <script defer src="{{ asset('assets/js/plugins/dragula.min.js') }}"></script>
        <script async>
          (() => { 
              if (!window.translations) {
  window.translations = {};
}
const t = {
            ar:  { application_unavailable: 'لا يمكن جلب طلبات الوظيفة', application_order_unavailable: 'فشل تحديث ترتيب الطلب', dragula_unavailable: 'لا يمكن تهيئة قائمة السحب والإفلات' },
            da:  { application_unavailable: 'Kan ikke hente jobansøgning', application_order_unavailable: 'Opdatering af rækkefølge mislykkedes', dragula_unavailable: 'Kan ikke initialisere dragula' },
            de:  { application_unavailable: 'Kann Bewerbungen nicht abrufen', application_order_unavailable: 'Reihenfolgeaktualisierung fehlgeschlagen', dragula_unavailable: 'Kann Dragula nicht initialisieren' },
            en:  { application_unavailable: 'Cannot fetch job application', application_order_unavailable: 'Failed to update application order', dragula_unavailable: 'Cannot initialize dragula list' },
            es:  { application_unavailable: 'No se puede obtener solicitud de trabajo', application_order_unavailable: 'Fallo al actualizar el orden de la solicitud', dragula_unavailable: 'No se puede inicializar dragula' },
            fr:  { application_unavailable: 'Impossible de récupérer la candidature', application_order_unavailable: 'Échec de la mise à jour de l’ordre', dragula_unavailable: 'Impossible d’initialiser dragula' },
            he:  { application_unavailable: 'אין אפשרות להביא את בקשת המשרה', application_order_unavailable: 'עדכון סדר הבקשה נכשל', dragula_unavailable: 'לא ניתן לאתחל dragula' },
            it:  { application_unavailable: 'Impossibile recuperare candidatura', application_order_unavailable: 'Aggiornamento ordine non riuscito', dragula_unavailable: 'Impossibile inizializzare dragula' },
            ja:  { application_unavailable: '求人応募を取得できません', application_order_unavailable: '申請順序の更新に失敗しました', dragula_unavailable: 'dragulaを初期化できません' },
            nl:  { application_unavailable: 'Kan sollicitatie niet ophalen', application_order_unavailable: 'Bijwerken volgorde mislukt', dragula_unavailable: 'Kan dragula niet initialiseren' },
            pl:  { application_unavailable: 'Nie można pobrać zgłoszenia', application_order_unavailable: 'Aktualizacja kolejności nieudana', dragula_unavailable: 'Nie można zainicjalizować dragula' },
            pt:  { application_unavailable: 'Não foi possível buscar candidatura', application_order_unavailable: 'Falha ao atualizar ordem', dragula_unavailable: 'Não foi possível inicializar dragula' },
            'pt-br': { application_unavailable: 'Não foi possível buscar candidatura', application_order_unavailable: 'Falha ao atualizar ordem', dragula_unavailable: 'Não foi possível inicializar dragula' },
            ru:  { application_unavailable: 'Не удалось получить заявку', application_order_unavailable: 'Не удалось обновить порядок', dragula_unavailable: 'Не удалось инициализировать dragula' },
            tr:  { application_unavailable: 'İş başvurusu alınamadı', application_order_unavailable: 'Başvuru sırası güncellenemedi', dragula_unavailable: 'dragula başlatılamıyor' },
            zh:  { application_unavailable: '无法获取求职申请', application_order_unavailable: '更新申请顺序失败', dragula_unavailable: '无法初始化 dragula' }
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
            const DATA_LISTENER_ADDED   = 'data-listener-added';
            const ERR_FB                = '# ERROR';
            const DATA_CLIENT_LOCALIZED = 'data-client-localized';
            const DATA_GUARD_MSG        = 'data-guard-msg';

            const getLocalizedMessage = (el, key) => {
                let msg = ERR_FB;
                if (el?.getAttribute('data-sv-localized') === 'true'
                    || el?.getAttribute(DATA_CLIENT_LOCALIZED) === 'true') {
                    msg = el.getAttribute(DATA_GUARD_MSG) || ERR_FB;
                } else {
                    let lang = (sessionStorage.getItem('erp-np-lang')
                                || document.documentElement.lang
                                || 'en')
                                .toLowerCase()
                                .replace(/_/g,'-');
                    lang = lang === 'pt-br' ? lang : lang.slice(0,2);
                    msg = window.translations?.[lang]?.[key]
                        || window.translations?.['en']?.[key]
                        || ERR_FB;
                    if (msg !== ERR_FB) {
                        el.setAttribute(DATA_GUARD_MSG, msg);
                        el.setAttribute(DATA_CLIENT_LOCALIZED, 'true');
                    }
                }
                return msg;
            };

            const handleErrorDisplay = (el, key) => {
                const message = el
                    ? getLocalizedMessage(el, key)
                    : ERR_FB;
                const hasBootstrap = document.querySelector('link[href*="bootstrap"]')
                                    && window.bootstrap?.Toast;
                if (hasBootstrap) {
                    if (!document.querySelector('#error-toast')) {
                        const toast = document.createElement('div');
                        toast.id        = 'error-toast';
                        toast.className = 'toast align-items-center text-bg-danger border-0';
                        toast.setAttribute('role','alert');
                        toast.setAttribute('aria-live','assertive');
                        toast.setAttribute('aria-atomic','true');
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

            try {
                if (typeof $ === 'undefined') {
                    console.error('jQuery is required');
                    return;
                }

                const loadApplication = (id) => {
                    if (!id) return;
                    $.ajax({
                        url: "{{ route('get.job.application') }}",
                        type: 'POST',
                        data: { id, _token: "{{ csrf_token() }}" },
                        success: (res) => {
                            try {
                                const job = JSON.parse(res);
                                const { applicant = [], visibility = [], custom_question = [] } = job;
                                $('.gender').toggleClass('d-none', !applicant.includes('gender'));
                                $('.dob').toggleClass('d-none', !applicant.includes('dob'));
                                $('.country').toggleClass('d-none', !applicant.includes('country'));
                                $('.profile').toggleClass('d-none', !visibility.includes('profile'));
                                $('.resume').toggleClass('d-none', !visibility.includes('resume'));
                                $('.letter').toggleClass('d-none', !visibility.includes('letter'));
                                $('.question').addClass('d-none');
                                custom_question.forEach(q => {
                                    $(`.question_${q}`).removeClass('d-none');
                                });
                            } catch {
                                const el = document.getElementById('jobs');
                                handleErrorDisplay(el, 'application_unavailable');
                            }
                        },
                        error: () => {
                            const el = document.getElementById('jobs');
                            handleErrorDisplay(el, 'application_unavailable');
                        }
                    });
                };

                // initial load
                $( () => {
                    loadApplication($('#jobs').val());
                });
                $(document).on('change','#jobs', function() {
                    loadApplication($(this).val());
                });

                @can('move job application')
                try {
                    $('[data-plugin="dragula"]').each(function () {
                        const containers = $(this).data('containers') ?? [];
                        const elems = containers.map(id => document.getElementById(id))
                                                .filter(el => el);
                        const handleClass = $(this).data('handleclass');
                        const drake = dragula(elems, handleClass ? {
                            moves: (_el,_source,handle) => handle.classList.contains(handleClass)
                        } : {});
                        drake.on('drop', (el, target, source) => {
                            try {
                                const order = Array.from(target.children)
                                    .map((child, i) => child.getAttribute('data-id'));
                                const id = el.getAttribute('data-id');
                                const old_status = source.getAttribute('data-status');
                                const new_status = target.getAttribute('data-status');
                                const stage_id = target.getAttribute('data-id');
                                $("#" + source.id).siblings('.count')
                                    .text(source.children.length);
                                $("#" + target.id).siblings('.count')
                                    .text(target.children.length);
                                $.ajax({
                                    url: '{{ route('job.application.order') }}',
                                    type: 'POST',
                                    data: {
                                        application_id: id,
                                        stage_id,
                                        order,
                                        new_status,
                                        old_status,
                                        _token: $('meta[name="csrf-token"]').attr('content')
                                    },
                                    success: () => show_toastr('success','Job-application successfully updated','success'),
                                    error: (xhr) => {
                                        const err = xhr.responseJSON?.error || '';
                                        show_toastr('error', err, 'error');
                                    }
                                });
                            } catch {
                                handleErrorDisplay(el, 'application_order_unavailable');
                            }
                        });
                    });
                } catch {
                    const el = document.body;
                    handleErrorDisplay(el, 'dragula_unavailable');
                }
                @endcan

            } catch (e) {
                console.error('Initialization failed', e);
            }
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
    <li class="breadcrumb-item">{{__('Job Application')}}</li>
@endsection
@section(YieldingConstants::ADM_ACT_BTN)
    <div class="float-end">
        {{--        <a class="btn btn-sm btn-primary" data-bs-toggle="collapse" href="#multiCollapseExample1" role="button" aria-expanded="false" aria-controls="multiCollapseExample1" data-bs-toggle="tooltip" title="{{__('Filter')}}">--}}
        {{--            <i class="ti ti-filter"></i>--}}
        {{--        </a>--}}

        @can('create job application')
            <a href="#" data-size="lg" data-url="{{ route('job-application.create')}}" data-ajax-popup="true" data-bs-toggle="tooltip" title="{{__('Create')}}" data-title="{{__('Create New Job Application')}}" class="btn btn-sm btn-primary">
                <i class="ti ti-plus"></i>
            </a>
        @endcan

    </div>
@endsection
@section(YieldingConstants::ADM_CTT)
    <div class="row">
        <div class="col-sm-12">
            <div class="mt-2" id="multiCollapseExample1">
                <div class="card">
                    <div class="card-body">
                        {{ Collective\Html\FormFacade::open(array('route' => array('job-application.index'),'method'=>'get','id'=>'applicarion_filter')) }}

                        <div class="row d-flex align-items-center justify-content-end">

                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                <div class="btn-box">
                                    {{Collective\Html\FormFacade::label('start_date',__('Start Date'),['class'=>'form-label'])}}
                                    {{Collective\Html\FormFacade::date('start_date',$filter['start_date'],array('class'=>'month-btn form-control '))}}
                                </div>
                            </div>

                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                <div class="btn-box">
                                    {{Collective\Html\FormFacade::label('end_date',__('End Date'),['class'=>'form-label'])}}
                                    {{Collective\Html\FormFacade::date('end_date',$filter['end_date'],array('class'=>'month-btn form-control '))}}
                                </div>
                            </div>
                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                                <div class="btn-box">
                                    {{ Collective\Html\FormFacade::label('job', __('Job'),['class'=>'form-label']) }}
                                    {{ Collective\Html\FormFacade::select('job', $jobs,$filter['job'], array('class' => 'form-control select')) }}
                                </div>
                            </div>
                            <div class="col-auto float-end ms-2 mt-4">

                                <a href="#" class="btn btn-sm btn-primary" onclick="document.getElementById('applicarion_filter').submit(); return false;" data-bs-toggle="tooltip" data-original-title="{{__('apply')}}">
                                    <span class="btn-inner--icon"><i class="ti ti-search"></i></span>
                                </a>
                                <a href="{{ route('job-application.index') }}" class="btn btn-sm btn-danger" data-bs-toggle="tooltip"
                                   title="{{ __('Reset') }}">
                                    <span class="btn-inner--icon"><i class="ti ti-trash-off text-white-off"></i></span>
                                </a>
                            </div>

                        </div>
                        {{ Collective\Html\FormFacade::close() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="card overflow-hidden mt-0">
        <div class="container-kanban">
            @php

                $json = [];
                foreach ($stages as $stage){
                    $json[] = 'task-list-'.$stage->id;
                }
            @endphp
            <div class="row kanban-wrapper horizontal-scroll-cards" data-plugin="dragula" data-containers='{!! json_encode($json) !!}'>
                @foreach($stages as $stage)
                    @php $applications = $stage->applications($filter) @endphp

                    <div class="col">
                        <div class="card">

                            <div class="card-header">
                                <div class="float-end">
                                    <span class="btn btn-sm btn-primary btn-icon count">
                                        {{count($applications)}}
                                    </span>
                                </div>
                                <h4 class="mb-0">{{$stage->title}}</h4>
                            </div>

                            <div class="card-body kanban-box" id="task-list-{{$stage->id}}" data-id="{{$stage->id}}">
                                @foreach($applications as $application)
                                    <div class="card" data-id="{{$application->id}}">
                                        <div class="pt-3 ps-3">
                                        </div>
                                        <div class="card-header border-0 pb-0 position-relative">
                                            <h5><a href="{{ route('job-application.show',\Crypt::encrypt($application->id)) }}">{{$application->name}}</a></h5>
                                            <div class="card-header-right">
                                                @if(Auth::user()->type != 'client')
                                                    <div class="btn-group card-option">
                                                        <button type="button" class="btn dropdown-toggle"
                                                                data-bs-toggle="dropdown" aria-haspopup="true"
                                                                aria-expanded="false">
                                                            <i class="ti ti-dots-vertical"></i>
                                                        </button>
                                                        <div class="{{ ViewClassNamesConstants::DRP_MN_EM }}">
                                                            @can('show job application')
                                                                <a class="dropdown-item" href="{{ route('job-application.show',\Crypt::encrypt($application->id)) }}" class="dropdown-item"> <i class="ti ti-bookmark"></i>{{__('View')}}</a>
                                                            @endcan
                                                            @can('delete job application')
                                                                {!! Collective\Html\FormFacade::open(['method' => 'DELETE', 'route' => ['job-application.destroy', $application->id],'id'=>'delete-form-'.$application->id]) !!}
                                                                <a href="#!" class="dropdown-item bs-pass-para"><i class="ti ti-archive"></i>
                                                                    <span> {{__('Delete')}} </span>
                                                                </a>
                                                                {!! Collective\Html\FormFacade::close() !!}
                                                            @endcan
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <ul class="list-inline mb-0 mt-0">
                                                    <div class="row align-items-center">
                                                        <div class="col-md-12">
                                                            <span class="static-rating static-rating-sm d-block">
                                                            @for($i=1; $i<=5; $i++)
                                                                    @if($i <= $application->rating)
                                                                        <i class="star fas fa-star voted"></i>
                                                                    @else
                                                                        <i class="star fas fa-star"></i>
                                                                    @endif
                                                                @endfor
                                                            </span>
                                                        </div>
                                                    </div>
                                                    <small class="text-md">{{ !empty($application->jobs)?$application->jobs->title:'' }}</small><br>

                                                    <li class="list-inline-item d-inline-flex align-items-center" data-bs-toggle="tooltip" title="{{__('Product')}}">
                                                        <i class="ti ti-clock me-1" data-ajax-popup="true" data-title="{{__('Applied at')}}"></i>{{\Auth::user()->dateFormat($application->created_at)}}

                                                    </li>

                                                    <li class="list-inline-item d-inline-flex align-items-center" data-bs-toggle="tooltip" title="{{__('Source')}}">
                                                    </li>
                                                </ul>
                                                <div class="user-group">

{{--                                                    <img @if($application->profile) src="{{asset('/storage/uploads/job/profile/'.$application->profile)}}" --}}
{{--                                                         @else src="{{asset('/storage/uploads/avatar/avatar.png')}}" @endif class="hweb">--}}

                                                    <img src="{{ !empty($application->profile) ?$profile . ($application->profile) : $logo."avatar.png" }}"
                                                         class="hweb" >

                                                </div>

                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <span class="empty-container" data-placeholder="Empty"></span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endsection
