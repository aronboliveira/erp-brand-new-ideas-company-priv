@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        PermissionsConstants,
        StacksConstants,
        ViewClassNamesConstants,
        YieldingConstants,
    };
    use Illuminate\Support\Facades\Route;
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@section(YieldingConstants::ADM_PG_TTL)
    {{__('Job Application Details')}}
@endsection
@section(YieldingConstants::ADM_BDC)
    <li class="breadcrumb-item">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
        {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="breadcrumb-item"><a href="{{route('job-application.index')}}">{{__('Job Application')}}</a></li>
    <li class="breadcrumb-item">{{__('Job Application Details')}}</li>
@endsection
@push(StacksConstants::ADM_CSS)
    <style>
        @import url({{ asset('css/font-awesome.css') }});
    </style>
@endpush
@push(StacksConstants::ADM_SCR_PG)
    <script async src="{{ asset('js/bootstrap-toggle.js') }}"></script>
    <script async>
        window.translations = {
            ar:  { tags_unavailable: 'لا يمكن تهيئة الوسوم', rating_unavailable: 'فشل إرسال التقييم', stage_change_unavailable: 'فشل تغيير مرحلة المرشح' },
            da:  { tags_unavailable: 'Kan ikke initialisere tags', rating_unavailable: 'Kunne ikke sende vurdering', stage_change_unavailable: 'Ændring af fase mislykkedes' },
            de:  { tags_unavailable: 'Tags konnten nicht initialisiert werden', rating_unavailable: 'Bewertung konnte nicht gesendet werden', stage_change_unavailable: 'Phasenänderung fehlgeschlagen' },
            en:  { tags_unavailable: 'Cannot initialize tags', rating_unavailable: 'Cannot submit rating', stage_change_unavailable: 'Cannot change candidate stage' },
            es:  { tags_unavailable: 'No se pueden inicializar etiquetas', rating_unavailable: 'No se pudo enviar la valoración', stage_change_unavailable: 'No se pudo cambiar la etapa del candidato' },
            fr:  { tags_unavailable: 'Impossible d’initialiser les tags', rating_unavailable: 'Impossible d’envoyer la note', stage_change_unavailable: 'Impossible de changer l’étape du candidat' },
            he:  { tags_unavailable: 'לא ניתן לאתחל תגים', rating_unavailable: 'שליחת הדירוג נכשלה', stage_change_unavailable: 'שינוי שלב המועמד נכשל' },
            it:  { tags_unavailable: 'Impossibile inizializzare i tag', rating_unavailable: 'Impossibile inviare la valutazione', stage_change_unavailable: 'Impossibile cambiare fase candidato' },
            ja:  { tags_unavailable: 'タグを初期化できません', rating_unavailable: '評価を送信できません', stage_change_unavailable: '候補者フェーズの変更に失敗しました' },
            nl:  { tags_unavailable: 'Kan tags niet initialiseren', rating_unavailable: 'Kan beoordeling niet verzenden', stage_change_unavailable: 'Fase wijzigen mislukt' },
            pl:  { tags_unavailable: 'Nie można zainicjalizować tagów', rating_unavailable: 'Nie można wysłać oceny', stage_change_unavailable: 'Nie udało się zmienić etapu kandydata' },
            pt:  { tags_unavailable: 'Não foi possível inicializar as tags', rating_unavailable: 'Não foi possível enviar avaliação', stage_change_unavailable: 'Não foi possível alterar etapa do candidato' },
            'pt-br': { tags_unavailable: 'Não foi possível inicializar as tags', rating_unavailable: 'Não foi possível enviar avaliação', stage_change_unavailable: 'Não foi possível alterar etapa do candidato' },
            ru:  { tags_unavailable: 'Не удалось инициализировать теги', rating_unavailable: 'Не удалось отправить оценку', stage_change_unavailable: 'Не удалось изменить этап кандидата' },
            tr:  { tags_unavailable: 'Etiketler başlatılamıyor', rating_unavailable: 'Değerlendirme gönderilemedi', stage_change_unavailable: 'Aday aşaması değiştirilemedi' },
            zh:  { tags_unavailable: '无法初始化标签', rating_unavailable: '无法提交评分', stage_change_unavailable: '无法更改候选人阶段' }
        };
    </script>
    <script defer>
        (() => {
            const DATA_LISTENER_ADDED   = 'data-listener-added';
            const DATA_CLIENT_LOCALIZED = 'data-client-localized';
            const DATA_GUARD_MSG        = 'data-guard-msg';
            const ERR_FB                = '# ERROR';

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
                                .replace(/_/g, '-');
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
                        toast.setAttribute('role', 'alert');
                        toast.setAttribute('aria-live', 'assertive');
                        toast.setAttribute('aria-atomic', 'true');
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
                    new bootstrap.Toast(document.querySelector('#error-toast')).show();
                } else {
                    alert(message);
                }
            };

            try {
                if (typeof $ === 'undefined') throw new Error();
                
                // TagsInput initialization
                const $tags = $('[data-bs-toggle="tags"]');
                $tags.each(function() {
                    const el = this;
                    try {
                        $(el).tagsinput({ tagClass: 'badge badge-primary' });
                    } catch {
                        if (!el.hasAttribute(DATA_LISTENER_ADDED)) {
                            el.addEventListener('click', () =>
                                handleErrorDisplay(el, 'tags_unavailable')
                            );
                            el.setAttribute(DATA_LISTENER_ADDED, 'true');
                            const obs = new MutationObserver((_, o) => {
                                if (!document.body.contains(el)) {
                                    el.removeEventListener('click',
                                        () => handleErrorDisplay(el, 'tags_unavailable')
                                    );
                                    o.disconnect();
                                }
                            });
                            obs.observe(document.body, { childList: true, subtree: true });
                        }
                    }
                });

                // Star rating hover
                $('#stars li')
                    .on('mouseover', function() {
                        const onStar = parseInt($(this).data('value'), 10);
                        $(this).siblings('li.star').each(function(i) {
                            $(this).toggleClass('hover', i < onStar);
                        });
                    })
                    .on('mouseout', function() {
                        $(this).siblings('li.star').removeClass('hover');
                    });

                // Star rating click
                $('#stars li').on('click', function() {
                    const el = this;
                    try {
                        const onStar = parseInt($(el).data('value'), 10);
                        const $stars = $(el).siblings('li.star').addBack();
                        $stars.removeClass('selected');
                        $stars.slice(0, onStar).addClass('selected');
                        const ratingValue = parseInt($('#stars li.selected').last().data('value'), 10);
                        $.ajax({
                            url: '{{ route("job.application.rating", $jobApplication->id) }}',
                            type: 'POST',
                            data: {
                                rating: ratingValue,
                                _token: $('meta[name="csrf-token"]').attr('content')
                            },
                            cache: false,
                            success: () => {},
                            error: () => handleErrorDisplay(el, 'rating_unavailable')
                        });
                    } catch {
                        handleErrorDisplay(el, 'rating_unavailable');
                    }
                });

                // Stage change
                $(document).on('change', '.stages', function() {
                    const el = this;
                    try {
                        const id = $(el).val();
                        const scheduleId = el.getAttribute('data-scheduleid');
                        $.ajax({
                            url: "{{ route('job.application.stage.change') }}",
                            type: 'POST',
                            data: { stage: id, schedule_id: scheduleId, _token: "{{ csrf_token() }}" },
                            cache: false,
                            success: () => {
                                show_toastr('success', 'The candidate stage successfully changed', 'success');
                                setTimeout(() => window.location.reload(), 1000);
                            },
                            error: () => handleErrorDisplay(el, 'stage_change_unavailable')
                        });
                    } catch {
                        handleErrorDisplay(el, 'stage_change_unavailable');
                    }
                });

            } catch {
                handleErrorDisplay(document.body, 'tags_unavailable');
            }
        })();
    </script>
@endpush
@section('content')

    <div class="row">
        <div class="col-md-6">
            <div class="card job-create">
                <div class="card-header">
                    <div class="row">
                        <div class="col-auto">
                            <h6 class="text-muted">{{__('Basic Details')}}</h6>
                        </div>
                        <div class="col float-end">
                            <ul class="list-inline mb-0">
                                @can('delete job application')
                                    <li class="list-inline-item float-end">
                                        {!! Collective\Html\FormFacade::open(['method' => 'DELETE', 'route' => ['job.application.archive', $jobApplication->id],'id'=>'archive-form-'.$jobApplication->id]) !!}


                                        <a href="#" data-confirm="Are You Sure?|This action can not be undone. Do you want to continue?" class="bs-pass-para" data-bs-toggle="tooltip" data-confirm-yes="document.getElementById('archive-form-{{$jobApplication->id}}').submit();">
                                            @if($jobApplication->is_archive==0)
                                                <span class="badge bg-info p-2 px-3 rounded">{{__('Archive')}}</span>
                                            @else
                                                <span class="badge bg-warning p-2 px-3 rounded">{{__('UnArchive')}}</span>
                                            @endif
                                        </a>
                                        {!! Collective\Html\FormFacade::close() !!}

                                    </li>
                                    @if($jobApplication->is_archive==0)
                                        <li class="list-inline-item">
                                            {!! Collective\Html\FormFacade::open(['method' => 'DELETE', 'route' => ['job-application.destroy', $jobApplication->id],'id'=>'delete-form-'.$jobApplication->id]) !!}

                                            <a href="#" data-confirm="Are You Sure?|This action can not be undone. Do you want to continue?" class="bs-pass-para" data-bs-toggle="tooltip" data-confirm-yes="document.getElementById('delete-form-{{$jobApplication->id}}').submit();">
                                                <span class="badge badge-pill badge-soft-danger">{{__('Delete')}}</span></a>
                                            {!! Collective\Html\FormFacade::close() !!}
                                        </li>
                                    @endif
                                @endcan
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="card-body ">
                    <h5 class="h4">
                        <div class="d-flex align-items-center" data-toggle="tooltip" data-placement="right" data-title="2 hrs ago" data-original-title="" title="">
                            <div>
                                @php
                                    $logo=\App\Models\Utility::getFile('uploads/avatar/');
                                    $profiles=\App\Models\Utility::getFile('uploads/job/profile/');
                                @endphp
                                {{--                                <a href="#" class="avatar rounded-circle avatar-sm">--}}
                                {{--                                    <img src="{{!empty($jobApplication->profile)? asset('/storage/uploads/job/profile/'.$jobApplication->profile):asset('/storage/uploads/avatar/avatar.png')}}"--}}
                                {{--                                         class="hweb h-100">--}}
                                {{--                                </a>--}}
                                <a href="{{ !empty($jobApplication->profile) ?($profiles . $jobApplication->profile) : $logo."avatar.png" }}" class="avatar rounded-circle avatar-sm">
                                    <img src="{{ !empty($jobApplication->profile) ? ($profiles . $jobApplication->profile) : $logo."avatar.png" }}"
                                         class="hweb h-100" >
                                </a>

                            </div>
                            <div class="flex-fill ms-3">
                                <div class="h6 text-sm mb-0"> {{$jobApplication->name}}</div>
                                <p class="text-sm lh-140 mb-0">
                                    {{ $jobApplication->email}}
                                </p>
                            </div>
                        </div>
                    </h5>
                    <div class="py-2 mt-3 border-top ">
                        <div class="row align-items-center ms-2">
                            @foreach($stages as $stage)
                                <div class="{{ ViewClassNamesConstants::FM_CHK_IL_GP }}">
                                    <input type="radio" id="stage_{{$stage->id}}" name="stage" data-scheduleid="{{$jobApplication->id}}" value="{{$stage->id}}" class="form-check-input stages" {{($jobApplication->stage==$stage->id)?'checked':''}}>
                                    <label class="form check-label" for="stage_{{$stage->id}}">{{$stage->title}}</label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 ">
            <div class="card ">
                <div class="card-header">
                    <div class="row">
                        <div class="col-auto">
                            <h6 class="text-muted">{{__('Basic Information')}}</h6>
                        </div>

                        <div class="col text-end">
                            <div class="col-12 text-end">
                                <a href="#" data-url="{{route('job.on.board.create', $jobApplication->id)}}" data-title="{{__('Add to Job OnBoard')}}" data-ajax-popup="true" class="btn-sm btn btn-primary">
                                    <i class="ti ti-plus"></i>{{__('Add to Job OnBoard')}}</a>
                            </div>

                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <dl class="row">
                        <dt class="col-sm-3"><span class="h6 text-sm mb-0">{{__('Phone')}}</span></dt>
                        <dd class="col-sm-9"><span class="text-sm">{{$jobApplication->phone}}</span></dd>
                        @if(!empty($jobApplication->dob))
                            <dt class="col-sm-3"><span class="h6 text-sm mb-0">{{__('DOB')}}</span></dt>
                            <dd class="col-sm-9"><span class="text-sm">{{\Auth::user()->dateFormat($jobApplication->dob)}}</span></dd>
                        @endif
                        @if(!empty($jobApplication->gender))
                            <dt class="col-sm-3"><span class="h6 text-sm mb-0">{{__('Gender')}}</span></dt>
                            <dd class="col-sm-9"><span class="text-sm">{{$jobApplication->gender}}</span></dd>
                        @endif
                        @if(!empty($jobApplication->country))
                            <dt class="col-sm-3"><span class="h6 text-sm mb-0">{{__('Country')}}</span></dt>
                            <dd class="col-sm-9"><span class="text-sm">{{$jobApplication->country}}</span></dd>
                        @endif
                        @if(!empty($jobApplication->state))
                            <dt class="col-sm-3"><span class="h6 text-sm mb-0">{{__('State')}}</span></dt>
                            <dd class="col-sm-9"><span class="text-sm">{{$jobApplication->state}}</span></dd>
                        @endif
                        @if(!empty($jobApplication->city))
                            <dt class="col-sm-3"><span class="h6 text-sm mb-0">{{__('City')}}</span></dt>
                            <dd class="col-sm-9"><span class="text-sm">{{$jobApplication->city}}</span></dd>
                        @endif

                        <dt class="col-sm-3"><span class="h6 text-sm mb-0">{{__('Applied For')}}</span></dt>
                        <dd class="col-sm-9"><span class="text-sm">{{ !empty($jobApplication->jobs)?$jobApplication->jobs->title:'-' }}</span></dd>

                        <dt class="col-sm-3"><span class="h6 text-sm mb-0">{{__('Applied at')}}</span></dt>
                        <dd class="col-sm-9"><span class="text-sm">{{\Auth::user()->dateFormat($jobApplication->created_at)}}</span></dd>
                        <dt class="col-sm-3"><span class="h6 text-sm mb-0">{{__('CV / Resume')}}</span></dt>
                        <dd class="col-sm-9">
                            @if(!empty($jobApplication->resume))
                                <span class="text-sm action-btn bg-primary ms-2 ">
                                <a href="{{asset(Storage::url('uploads/job/resume')).'/'.$jobApplication->resume}}" download=""  target="_blank"><i class="ti ti-download text-white"></i></a>
                            </span>
                            @else
                                -
                            @endif
                        </dd>
                        <dt class="col-sm-3"><span class="h6 text-sm mb-0">{{__('Cover Letter')}}</span></dt>
                        <dd class="col-sm-9"><span class="text-sm">{{$jobApplication->cover_letter}}</span></dd>


                    </dl>
                    <div class='rating-stars text-right'>
                        <ul id='stars'>
                            <li class='star {{(in_array($jobApplication->rating,[1,2,3,4,5])==true)?'selected':''}}' data-bs-toggle="tooltip"   data-bs-title="Poor" data-value='1'>
                                <i class='fas fa-star fa-fw'></i>
                            </li>
                            <li class='star {{(in_array($jobApplication->rating,[2,3,4,5])==true)?'selected':''}}' data-bs-toggle="tooltip"   data-bs-title='Fair' data-value='2'>
                                <i class='fas fa-star fa-fw'></i>
                            </li>
                            <li class='star {{(in_array($jobApplication->rating,[3,4,5])==true)?'selected':''}}' data-bs-toggle="tooltip"   data-bs-title='Good' data-value='3'>
                                <i class='fas fa-star fa-fw'></i>
                            </li>
                            <li class='star {{(in_array($jobApplication->rating,[4,5])==true)?'selected':''}}' data-bs-toggle="tooltip"   data-bs-title='Excellent' data-value='4'>
                                <i class='fas fa-star fa-fw'></i>
                            </li>
                            <li class='star {{(in_array($jobApplication->rating,[5])==true)?'selected':''}}' data-bs-toggle="tooltip"   data-bs-title='WOW!!!' data-value='5'>
                                <i class='fas fa-star fa-fw'></i>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <div class="card">
        <div class="card-header">
            <div class="row">
                <div class="col">
                    <h6 class="text-muted">{{__('Additional Details')}}</h6>
                </div>
                <div class="col text-end">
                    @can(PermissionsConstants::CR_ITV_SCHD)
                        <a href="#" data-url="{{ route('interview-schedule.create',$jobApplication->id) }}" data-size="lg" class="btn-sm btn btn-primary" data-ajax-popup="true" data-title="{{__('Create New Interview Schedule')}}">
                            <i class="ti ti-plus"></i> {{__('Create Interview Schedule')}}
                        </a>
                    @endcan
                </div>
            </div>
        </div>
        <div class="card-body">
            @if(!empty(json_decode($jobApplication->custom_question)))
                <div class="{{ ViewClassNamesConstants::LG_FLSH_MB4 }}">
                    @foreach(json_decode($jobApplication->custom_question) as $que => $ans)
                        @if(!empty($ans))
                            <div class="list-group-item px-0">
                                <div class="row align-items-center">
                                    <div class="col">
                                        <a href="#!" class="d-block h6 text-sm mb-0">{{$que}}</a>
                                        <p class="card-text text-sm text-muted mb-0">
                                            {{$ans}}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
            @endif
            {{Collective\Html\FormFacade::open(array('route'=>array('job.application.skill.store',$jobApplication->id),'method'=>'post'))}}
            <div class="form-group">
                <label class="form-label">{{__('Skills')}}</label>
                <input type="text" class="form-control" value="{{$jobApplication->skill}}" data-toggle="tags" name="skill" placeholder="{{__('Type here....')}}"/>
            </div>
            @can('add job application skill')
                <div class="form-group">
                    <input type="submit" value="{{__('Add Skills')}}" class="btn-sm btn btn-primary">
                </div>
            @endcan
            {{Collective\Html\FormFacade::close()}}


            {{Collective\Html\FormFacade::open(array('route'=>array('job.application.note.store',$jobApplication->id),'method'=>'post'))}}
            <div class="form-group">
                <label class="form-label">{{__('Applicant Notes')}}</label>
                <textarea name="note" class="form-control" id="" rows="3"></textarea>
            </div>
            @can('add job application note')
                <div class="form-group">
                    <input type="submit" value="{{__('Add Notes')}}" class="btn-sm btn btn-primary">
                </div>
            @endcan
            {{Collective\Html\FormFacade::close()}}

            <div class="{{ ViewClassNamesConstants::LG_FLSH_MB4 }}">
                @foreach($notes as $note)
                    <div class="list-group-item px-0">
                        <div class="row align-items-center">
                            <div class="col">
                                <a href="#!" class="d-block h6 text-sm mb-0">{{!empty($note->noteCreated)?$note->noteCreated->name:'-'}}</a>
                                <p class="card-text text-sm text-muted mb-0">
                                    {{$note->note}}
                                </p>
                            </div>
                            <div class="col-auto">
                                <a href="#" class=""> {{\Auth::user()->dateFormat($note->created_at)}}</a>
                            </div>
                            @can('delete job application note')
                                @if($note->note_created==\Auth::user()->id)
                                    <div class="action-btn bg-danger ms-2">
                                        {!! Collective\Html\FormFacade::open(['method' => 'DELETE', 'route' => ['job.application.note.destroy', $note->id],'id'=>'delete-form-'.$note->id]) !!}
                                        <a class="{{ ViewClassNamesConstants::TRS_PARA }}" href="#" data-confirm="Are You Sure?|This action can not be undone. Do you want to continue?" data-bs-toggle="tooltip" title="{{__('Delete')}}" data-confirm-yes="document.getElementById('delete-form-{{$note->id}}').submit();">
                                            <i class="ti ti-trash text-white"></i></a>
                                        {!! Collective\Html\FormFacade::close() !!}
                                    </div>
                                @endif
                            @endcan
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

@endsection
