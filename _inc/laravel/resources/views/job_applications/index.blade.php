@php
    try {
$user = Auth::user() ?? null;
        $lang = is_callable([Utility::class,'fetchUserLang']) ? Utility::fetchUserLang(user:$user) : app()->getLocale();
        $canFetchMsg = is_callable([Utility::class,'fetchLinkMessage']);
        $logo = Utility::getFile('uploads/avatar/');
        $profile = Utility::getFile('uploads/job/profile/');
        $filters = is_array($filter ?? null) ? $filter : [];
        $jobsList = (is_array($jobs ?? null) && count($jobs ?? [])) ? $jobs : ((($jobs ?? null) instanceof Collection && $jobs->isNotEmpty()) ? $jobs : []);
        $stagesList = (is_array($stages ?? null) && count($stages ?? [])) ? $stages : ((($stages ?? null) instanceof Collection && $stages->isNotEmpty()) ? $stages : []);
        $createGuard = ($canFetchMsg ? Utility::fetchLinkMessage($lang, VW::JB_APL, 'create_job_application_unavailable') : 'Create Job Application route is unavailable. Please contact technical support or your domain administrator.') ?? __('Create Job Application route is unavailable. Please contact technical support or your domain administrator.');
        $showGuard = ($canFetchMsg ? Utility::fetchLinkMessage($lang, VW::JB_APL, 'show_job_application_unavailable') : 'Job Application details route is unavailable. Please contact technical support or your domain administrator.') ?? __('Job Application details route is unavailable. Please contact technical support or your domain administrator.');
        $deleteGuard = ($canFetchMsg ? Utility::fetchLinkMessage($lang, VW::JB_APL, 'delete_job_application_unavailable') : 'Delete Job Application route is unavailable. Please contact technical support or your domain administrator.') ?? __('Delete Job Application route is unavailable. Please contact technical support or your domain administrator.');
        // Resource route is registered as 'job-application' (singular, dash); VW::JB_APL is the view-path constant ('job_applications') and is not interchangeable.
        $jbAplRoute = 'job-application';
    } catch (\Throwable $e) {
        \Log::error('job_applications/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    }
@endphp

@extends(EL::ADM)

@section(YD::ADM_PG_TTL)
    {{ __('Manage Job Application') }}
@endsection

@push(ST::ADM_CSS)
    <link rel="stylesheet" href="{{ asset('assets/css/plugins/dragula.min.css') }}" id="main-style-link">
@endpush

@section(YD::ADM_BDC)
    <li class="{{ VC::BCI }}"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="{{ VC::BCI }}">{{ __('Job Application') }}</li>
@endsection

@section(YD::ADM_ACT_BTN)
    <div class="{{ VC::FEND }}">
        @can('create job application')
            <a href="{{ route($jbAplRoute.'.create') }}" data-url="{{ route($jbAplRoute.'.create') }}" data-ajax-popup="true" data-bs-toggle="tooltip" title="{{ __('Create') }}" data-title="{{ __('Create New Job Application') }}" data-guard-msg="{{ base64_encode($createGuard) }}" class="{{ VC::BT_SM_PM }}"><i class="{{ VC::TI_PLS }}"></i></a>
        @endcan
    </div>
@endsection

@section(YD::ADM_CTT)
    <div class="row">
        <div class="{{ VC::CS12 }}">
            <div class="{{ VC::MT2 }}" id="multiCollapseExample1">
                <div class="card">
                    <div class="{{ VC::CD_BD }}">
                        {!! Collective\Html\FormFacade::open(['route'=>[$jbAplRoute.'.index'],'method'=>'get','id'=>'application_filter']) !!}
                        <div class="{{ VC::R_FLX_ALC_JCE }}">
                            <div class="{{ VC::CL_POS3 }}">
                                <div class="btn-box">
                                    {!! Collective\Html\FormFacade::label('start_date',__('Start Date'),['class'=>'form-label']) !!}
                                    {!! Collective\Html\FormFacade::date('start_date', data_get($filters,'start_date'), ['class'=>'month-btn form-control']) !!}
                                </div>
                            </div>
                            <div class="{{ VC::CL_POS3 }}">
                                <div class="btn-box">
                                    {!! Collective\Html\FormFacade::label('end_date',__('End Date'),['class'=>'form-label']) !!}
                                    {!! Collective\Html\FormFacade::date('end_date', data_get($filters,'end_date'), ['class'=>'month-btn form-control']) !!}
                                </div>
                            </div>
                            <div class="{{ VC::CL_XL3 }}">
                                <div class="btn-box">
                                    {!! Collective\Html\FormFacade::label('job', __('Job'),['class'=>'form-label']) !!}
                                    {!! Collective\Html\FormFacade::select('job', $jobsList, data_get($filters,'job'), ['class'=>'form-control select']) !!}
                                </div>
                            </div>
                            <div class="{{ VC::C_AT_FEND }}">
                                <a href="#" class="{{ VC::BT_SM_PM }}" data-submit-form="application_filter" data-bs-toggle="tooltip" title="{{ __('apply') }}"><span class="btn-inner--icon"><i class="{{ VC::TI_SRC }}"></i></span></a>
                                <a href="{{ route($jbAplRoute.'.index') }}" class="{{ VC::BT_SM_DG }}" data-bs-toggle="tooltip" title="{{ __('Reset') }}"><span class="btn-inner--icon"><i class="ti ti-trash-off text-white-off"></i></span></a>
                            </div>
                        </div>
                        {!! Collective\Html\FormFacade::close() !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="card overflow-hidden mt-0">
        <div class="container-kanban" data-plugin="dragula">
            @php
 $containerIds ??= [];
@endphp
            <div class="row kanban-wrapper horizontal-scroll-cards" data-containers='@json($containerIds)'>
                @if(!empty($stagesList))
                    @foreach($stagesList as $stage)
                        @php
                            try {
                                $stageId = data_get($stage,'id');
                                $stageTitle = data_get($stage,'title',__('No stage title available'));
                                $apps = method_exists($stage,'applications') ? $stage->applications($filters) : [];
                                $appsList = Utility::isFilled($apps ?? []) ? $apps : [];
                                $containerIds[] = 'task-list-'.$stageId;
                            } catch (\Throwable $e) {
                                \Log::error('job_applications/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                            }
@endphp
                        <div class="col">
                            <div class="card">
                                <div class="{{ VC::CD_HD }}">
                                    <div class="{{ VC::FEND }}">
                                        <span class="{{ VC::BT_SM_PM }} btn-icon count">{{ is_iterable($appsList) ? count($appsList) : 0 }}</span>
                                    </div>
                                    <h4 class="{{ VC::MB0 }}">{{ $stageTitle }}</h4>
                                </div>
                                <div class="{{ VC::CD_BD }} kanban-box" id="task-list-{{ $stageId }}" data-id="{{ $stageId }}">
                                    @if(is_iterable($appsList) && count($appsList))
                                        @foreach($appsList as $application)
                                            @php
                                                try {
                                                    $appId = data_get($application,'id');
                                                    $appName = data_get($application,'name',__('No applicant name available'));
                                                    $rating = (int) (data_get($application,'rating',0));
                                                    $jobTitle = data_get($application,'jobs.title',__('No job title available'));
                                                    $appliedAt = $user && is_callable([$user,'dateFormat']) ? $user->dateFormat(data_get($application,'created_at')) : (string) data_get($application,'created_at',__('No date available'));
                                                    $profileImg = data_get($application,'profile');
                                                    $imgSrc = !empty($profileImg) ? ($profile . $profileImg) : ($logo.'avatar.png');
                                                    $showUrl = route($jbAplRoute.'.show', \Crypt::encrypt($appId));
                                                } catch (\Throwable $e) {
                                                    \Log::error('job_applications/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                }
@endphp
                                            <div class="card" data-id="{{ $appId }}">
                                                <div class="{{ VC::PT3 }} {{ VC::PS3 }}"></div>
                                                <div class="{{ VC::CD_HD }} border-0 pb-0 position-relative">
                                                    <h5><a href="{{ $showUrl }}" data-url="{{ $showUrl }}" data-guard-msg="{{ base64_encode($showGuard) }}">{{ $appName }}</a></h5>
                                                    <div class="card-header-right">
                                                        @if(data_get($user,'type') !== 'client')
                                                            <div class="btn-group card-option">
                                                                <button type="button" class="btn dropdown-toggle" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="{{ VC::TD_DOTV }}"></i></button>
                                                                <div class="{{ ViewClassNamesConstants::DRP_MN_EM }}">
                                                                    @can('show job application')
                                                                        <a class="{{ VC::DRP_IT }}" href="{{ $showUrl }}" data-url="{{ $showUrl }}" data-guard-msg="{{ base64_encode($showGuard) }}"><i class="ti ti-bookmark"></i>{{ __('View') }}</a>
                                                                    @endcan
                                                                    @can('delete job application')
                                                                        {!! Collective\Html\FormFacade::open(['method'=>'DELETE','route'=>[$jbAplRoute.'.destroy', $appId],'id'=>'delete-form-'.$appId,'data-url'=>route($jbAplRoute.'.destroy',$appId),'data-guard-msg'=>$deleteGuard]) !!}
                                                                        <a href="#!" class="{{ VC::DRP_IT }} bs-pass-para" data-confirm="{{ __('Are You Sure?') }}|{{ __('This action can not be undone. Do you want to continue?') }}" data-confirm-yes="document.getElementById('delete-form-{{ $appId }}').submit();"><i class="{{ VC::TI_ARC }}"></i><span>{{ __('Delete') }}</span></a>
                                                                        {!! Collective\Html\FormFacade::close() !!}
                                                                    @endcan
                                                                </div>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                                <div class="{{ VC::CD_BD }}">
                                                    <div class="{{ VC::DFL_AIC_JCB }}">
                                                        <ul class="list-inline {{ VC::MB0 }} mt-0">
                                                            <div class="{{ VC::R_ALC }}">
                                                                <div class="{{ VC::CM12 }}">
                                                                    <span class="static-rating static-rating-sm {{ VC::DBL }}">
                                                                        @for($i=1;$i<=5;$i++)
                                                                            @if($i <= $rating)
                                                                                <i class="star fas fa-star voted"></i>
                                                                            @else
                                                                                <i class="star fas fa-star"></i>
                                                                            @endif
                                                                        @endfor
                                                                    </span>
                                                                </div>
                                                            </div>
                                                            <small class="text-md">{{ $jobTitle }}</small><br>
                                                            <li class="list-inline-item {{ VC::DFL_IL_VC }}" data-bs-toggle="tooltip" title="{{ __('Applied at') }}"><i class="ti ti-clock me-1" data-ajax-popup="true" data-title="{{ __('Applied at') }}"></i>{{ $appliedAt }}</li>
                                                            <li class="list-inline-item {{ VC::DFL_IL_VC }}" data-bs-toggle="tooltip" title="{{ __('Source') }}"></li>
                                                        </ul>
                                                        <div class="user-group"><img src="{{ $imgSrc }}" class="hweb"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    @else
                                        <div class="{{ VC::TXCT_MT }}">{{ __('No applications available') }}</div>
                                    @endif
                                </div>
                                <span class="empty-container" data-placeholder="Empty"></span>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="{{ VC::C12 }}"><h6 class="{{ VC::TXCT }}">{{ __('No stages available') }}</h6></div>
                @endif
            </div>
        </div>
    </div>
@endsection

@push(ST::ADM_SCR_PG)
    {{-- <script src="{{ asset('libs/dragula/dist/dragula.min.js') }}"></script>
    <script src="{{ asset('libs/autosize/dist/autosize.min.js') }}"></script> --}}
    <script defer src="{{ asset('assets/js/core/form-submit-delegate.js') }}"></script>
    <script defer src="{{ asset('assets/js/plugins/dragula.min.js') }}"></script>
    <script defer src="{{ asset('assets/js/routes/jobs/applications/index.js') }}"></script>
    <script async src="{{ asset('assets/js/routes/jobs/applications/lang/index.js') }}"></script>
    <script defer>
        (() => {
            const RG = window.RouteGuard || {};
            const getMsg = RG.getMsg || ((k, el) => el?.getAttribute?.('data-guard-msg') || '# ERROR');
            const handleErrorDisplay = (el, key) => {
                const message = el ? getMsg(key, el) : '# ERROR';
                (RG.showToast || (m => alert(m)))(message);
            };
            const DATA_LISTENER_ADDED = 'data-listener-added';

            try {
                if (typeof $ === 'undefined') {
                    if (
                        window.location.hostname === "localhost" ||
                        window.location.hostname === "127.0.0.1"
                    ) console.error("jQuery unavailable");
                    return;
                }

                const loadApplication = (id) => {
                    if (!id) return;
                    $.ajax({
                        url: "{{ route(VW::JB_APL . '.get') }}",
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
                                    url: '{{ route('jobs.application.order') }}',
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
                if (
                    window.location.hostname === "localhost" ||
                    window.location.hostname === "127.0.0.1"
                ) console.error('Initialization failed', e);
            }
        })();
    </script>
@endpush

        {{--        <a class="{{ VC::BT_SM_PM }}" data-bs-toggle="collapse" href="#multiCollapseExample1" role="button" aria-expanded="false" aria-controls="multiCollapseExample1" data-bs-toggle="tooltip" title="{{__('Filter')}}">--}}
        {{--            <i class="ti ti-filter"></i>--}}
        {{--        </a>--}}

        {{--                                                    <img @if($application->profile) src="{{asset('/storage/uploads/job/profile/'.$application->profile)}}" --}}
        {{--                                                         @else src="{{asset('/storage/uploads/avatar/avatar.png')}}" @endif class="hweb">--}}
