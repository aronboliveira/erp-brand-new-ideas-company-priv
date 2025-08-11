@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        StacksConstants,
        YieldingConstants
    };
    use Illuminate\Support\Facades\Route;
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@section(YieldingConstants::ADM_PG_TTL)
    {{__('Create Job')}}
@endsection
@section(YieldingConstants::ADM_BDC)
    <li class="breadcrumb-item">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
        {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="breadcrumb-item"><a href="{{route('job.index')}}">{{__('Job')}}</a></li>
    <li class="breadcrumb-item">{{__('Job Create')}}</li>
@endsection
@push(StacksConstants::ADM_CSS)
    <link rel="stylesheet" href="{{asset('css/summernote/summernote-bs4.css')}}">
    <link href="{{asset('css/bootstrap-tagsinput.css')}}" rel="stylesheet"/>
@endpush
@push(StacksConstants::ADM_SCR_PG)
    <script src="{{asset('css/summernote/summernote-bs4.js')}}"></script>
    <script async src="{{asset('js/bootstrap-tagsinput.min.js')}}"></script>
    <script async>
        window.translations = {
            ar:  { tags_unavailable: 'لا يمكن تهيئة الوسوم' },
            da:  { tags_unavailable: 'Kan ikke initialisere tags' },
            de:  { tags_unavailable: 'Tags konnten nicht initialisiert werden' },
            en:  { tags_unavailable: 'Cannot initialize tags' },
            es:  { tags_unavailable: 'No se pueden inicializar etiquetas' },
            fr:  { tags_unavailable: 'Impossible d’initialiser les tags' },
            he:  { tags_unavailable: 'לא ניתן לאתחל תגים' },
            it:  { tags_unavailable: 'Impossibile inizializzare i tag' },
            ja:  { tags_unavailable: 'タグを初期化できません' },
            nl:  { tags_unavailable: 'Kan tags niet initialiseren' },
            pl:  { tags_unavailable: 'Nie można zainicjalizować tagów' },
            pt:  { tags_unavailable: 'Não foi possível inicializar as tags' },
            'pt-br': { tags_unavailable: 'Não foi possível inicializar as tags' },
            ru:  { tags_unavailable: 'Не удалось инициализировать теги' },
            tr:  { tags_unavailable: 'Etiketler başlatılamıyor' },
            zh:  { tags_unavailable: '无法初始化标签' }
        };
    </script>
    <script defer>
        (() => {
            const DATA_LISTENER_ADDED   = 'data-listener-added';
            const ERR_FB                = '# ERROR';
            const DATA_CLIENT_LOCALIZED = 'data-client-localized';
            const DATA_GUARD_MSG        = 'data-guard-msg';

            const getLocalizedMessage = (el, key) => {
                let msg = ERR_FB;
                if (el?.getAttribute('data-sv-localized') === 'true' ||
                    el?.getAttribute(DATA_CLIENT_LOCALIZED) === 'true') {
                    msg = el.getAttribute(DATA_GUARD_MSG) || ERR_FB;
                } else {
                    let lang = (sessionStorage.getItem('erp-np-lang') ||
                                document.documentElement.lang ||
                                'en')
                                .toLowerCase()
                                .replace(/_/g, '-');
                    lang = lang === 'pt-br' ? lang : lang.slice(0,2);
                    msg = window.translations?.[lang]?.[key] ||
                        el.getAttribute(DATA_GUARD_MSG) ||
                        window.translations?.['en']?.[key] ||
                        ERR_FB;
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
                const hasBootstrap = document.querySelector('link[href*="bootstrap"]') &&
                                    window.bootstrap?.Toast;
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
                    new bootstrap.Toast(
                        document.querySelector('#error-toast')
                    ).show();
                } else {
                    alert(message);
                }
            };

            try {
                if (typeof $ === 'undefined' || !$.fn.tagsinput) {
                    throw new Error();
                }
                const els = $('[data-toggle="tags"]');
                if (!els.length) return;
                els.each(function() {
                    try {
                        $(this).tagsinput({ tagClass: 'badge badge-primary' });
                    } catch {
                        const el = this;
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
            } catch {
                const el = document.body;
                handleErrorDisplay(el, 'tags_unavailable');
            }
        })();
    </script>
@endpush
@php
    $plan= \App\Models\Utility::getChatGPTSettings();
@endphp
@section(YieldingConstants::ADM_ACT_BTN)
    <div class="float-end">
        {{-- start for ai module--}}

        @if($plan->chatgpt == 1)
            <a href="#" data-size="lg" class="btn btn-primary btn-icon btn-sm" data-ajax-popup-over="true" data-url="{{ route('generate',['job']) }}"
               data-bs-placement="top" data-title="{{ __('Generate content with AI') }}">
                <i class="fas fa-robot"> </i> <span>{{__('Generate with AI')}}</span>
            </a>
        @endif
        {{-- end for ai module--}}
    </div>
@endsection
@section(YieldingConstants::ADM_CTT)
    {{Collective\Html\FormFacade::open(array('url'=>'job','method'=>'post'))}}
    <div class="row mt-3">
        <div class="col-md-6 ">
            <div class="card card-fluid">
                <div class="card-body job-create ">
                    <div class="row">
                        <div class="form-group col-md-12">
                            {!! Collective\Html\FormFacade::label('title', __('Job Title'),['class'=>'form-label']) !!}
                            {!! Collective\Html\FormFacade::text('title', old('title'), ['class' => 'form-control','required' => 'required']) !!}
                        </div>
                        <div class="form-group col-md-6">
                            {!! Collective\Html\FormFacade::label('branch', __('Branch'),['class'=>'form-label']) !!}
                            {{ Collective\Html\FormFacade::select('branch', $branches,null, array('class' => 'form-control select','required'=>'required')) }}
                        </div>
                        <div class="form-group col-md-6">
                            {!! Collective\Html\FormFacade::label('category', __('Job Category'),['class'=>'form-label']) !!}
                            {{ Collective\Html\FormFacade::select('category', $categories,null, array('class' => 'form-control select','required'=>'required')) }}
                        </div>

                        <div class="form-group col-md-6">
                            {!! Collective\Html\FormFacade::label('position', __('Positions'),['class'=>'form-label']) !!}
                            {!! Collective\Html\FormFacade::number('position', old('positions'), ['class' => 'form-control','required' => 'required']) !!}
                        </div>
                        <div class="form-group col-md-6">
                            {!! Collective\Html\FormFacade::label('status', __('Status'),['class'=>'form-label']) !!}
                            {{ Collective\Html\FormFacade::select('status', $status,null, array('class' => 'form-control select','required'=>'required')) }}
                        </div>
                        <div class="form-group col-md-6">
                            {!! Collective\Html\FormFacade::label('start_date', __('Start Date'),['class'=>'form-label']) !!}
                            {!! Collective\Html\FormFacade::date('start_date', old('start_date'), ['class' => 'form-control']) !!}
                        </div>
                        <div class="form-group col-md-6">
                            {!! Collective\Html\FormFacade::label('end_date', __('End Date'),['class'=>'form-label']) !!}
                            {!! Collective\Html\FormFacade::date('end_date', old('end_date'), ['class' => 'form-control']) !!}
                        </div>
                        <div class="form-group col-md-12">
                            <input type="text" class="form-control" value="" data-toggle="tags" name="skill" placeholder="Skill"/>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 ">
            <div class="card card-fluid">
                <div class="card-body job-create">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <h6>{{__('Need to ask ?')}}</h6>
                                <div class="my-4">
                                    <div class="form-check custom-checkbox">
                                        <input type="checkbox" class="form-check-input" name="applicant[]" value="gender" id="check-gender">
                                        <label class="form-check-label" for="check-gender">{{__('Gender')}} </label>
                                    </div>
                                    <div class="form-check custom-checkbox">
                                        <input type="checkbox" class="form-check-input" name="applicant[]" value="dob" id="check-dob">
                                        <label class="form-check-label" for="check-dob">{{__('Date Of Birth')}}</label>
                                    </div>
                                    <div class="form-check custom-checkbox">
                                        <input type="checkbox" class="form-check-input" name="applicant[]" value="country" id="check-country">
                                        <label class="form-check-label" for="check-country">{{__('Country')}}</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                       <div class="col-md-6">
                           <div class="form-group">
                               <h6>{{__('Need to show option ?')}}</h6>
                               <div class="my-4">
                                   <div class="form-check custom-checkbox">
                                       <input type="checkbox" class="form-check-input" name="visibility[]" value="profile" id="check-profile">
                                       <label class="form-check-label" for="check-profile">{{__('Profile Image')}} </label>
                                   </div>
                                   <div class="form-check custom-checkbox">
                                       <input type="checkbox" class="form-check-input" name="visibility[]" value="resume" id="check-resume">
                                       <label class="form-check-label" for="check-resume">{{__('Resume')}}</label>
                                   </div>
                                   <div class="form-check custom-checkbox">
                                       <input type="checkbox" class="form-check-input" name="visibility[]" value="letter" id="check-letter">
                                       <label class="form-check-label" for="check-letter">{{__('Cover Letter')}}</label>
                                   </div>
                                   <div class="form-check custom-checkbox">
                                       <input type="checkbox" class="form-check-input" name="visibility[]" value="terms" id="check-terms">
                                       <label class="form-check-label" for="check-terms">{{__('Terms And Conditions')}}</label>
                                   </div>
                               </div>
                           </div>
                       </div>
                        <div class="form-group col-md-12">
                            <h6>{{__('Custom Question')}}</h6>
                            <div class="my-4">
                                @foreach($customQuestion as $question)
                                    <div class="form-check custom-checkbox">
                                        <input type="checkbox" class="form-check-input" name="custom_question[]" value="{{$question->id}}" id="custom_question_{{$question->id}}">
                                        <label class="form-check-label" for="custom_question_{{$question->id}}">{{$question->question}} </label>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card card-fluid">
                <div class="card-body ">
                    <div class="row">
                        <div class="form-group col-md-12">
                            {!! Collective\Html\FormFacade::label('description', __('Job Description'),['class'=>'form-label']) !!}
                            <textarea class="form-control summernote-simple-2" name="description" id="exampleFormControlTextarea1" rows="15"></textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card card-fluid">
                <div class="card-body">
                    <div class="row">
                        <div class="form-group col-6 mb-2">
                            {!! Collective\Html\FormFacade::label('requirement', __('Job Requirement'),['class'=>'form-label']) !!}
                        </div>
                        <div class="col-6 text-end">
                            @if($plan->chatgpt == 1)
                                <a href="#" data-size="md" class="btn btn-primary btn-icon btn-sm" data-ajax-popup-over="true" id="grammarCheck" data-url="{{ route('grammar',['grammar']) }}"
                                   data-bs-placement="top" data-title="{{ __('Grammar check with AI') }}">
                                    <i class="ti ti-rotate"></i> <span>{{__('Grammar check with AI')}}</span>
                                </a>
                            @endif
                        </div>
                        <div class="form-group col-md-12">
                            <textarea class="form-control summernote-simple" name="requirement" id="exampleFormControlTextarea2" rows="8"></textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-12 text-end">
            <div class="form-group">
                <input type="submit" value="{{__('Create')}}" class="btn btn-primary">
            </div>
        </div>
        {{Collective\Html\FormFacade::close()}}
    </div>
@endsection

