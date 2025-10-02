@php
    use App\Config\Constants\{
        DatabaseConstants,
        ExtendingLayoutsConstants,
        PermissionsConstants,
        StacksConstants,
        ViewClassNamesConstants as VC,
        ViewsConstants as VW,
        YieldingConstants
    };
    use App\Models\Utility;
    use Illuminate\Support\Facades\{Auth,Route,Gate};
    use Illuminate\Support\Collection;

    $user = Auth::user();
    $canDateFormat = is_object($user) && is_callable([$user,'dateFormat']);
    $lang = is_callable([Utility::class,'fetchUserLang']) ? Utility::fetchUserLang(user:$user) : app()->getLocale();
    $canFetchMsg = is_callable([Utility::class,'fetchLinkMessage']);

    $dashBase = 'dashboard';
    $dashUrl = Route::has($dashBase) ? route($dashBase) : '#';
    $dashGuard = ($canFetchMsg ? Utility::fetchLinkMessage($lang,'generics','dashboard_unavailable') : 'Dashboard route is unavailable. Please contact technical support or your domain administrator.') ?? __('Dashboard route is unavailable. Please contact technical support or your domain administrator.');

    $canCreate = Gate::check('create job');
    if($canCreate){
        $createBase = VW::JB.'.create';
        $createUrl = Route::has($createBase) ? route($createBase) : '#';
        $createGuard = ($canFetchMsg ? Utility::fetchLinkMessage($lang,VW::JB,'job_create_route_unavailable') : 'Job create route is unavailable. Please contact technical support or your domain administrator.') ?? __('Job create route is unavailable. Please contact technical support or your domain administrator.');
    }

    $canShow = Gate::check('show job');
    if($canShow){
        $showBase = VW::JB.'.show';
        $showGuard = ($canFetchMsg ? Utility::fetchLinkMessage($lang,VW::JB,'job_show_route_unavailable') : 'Job details route is unavailable. Please contact technical support or your domain administrator.') ?? __('Job details route is unavailable. Please contact technical support or your domain administrator.');
    }

    $canEdit = Gate::check('edit job');
    if($canEdit){
        $editBase = VW::JB.'.edit';
        $editGuard = ($canFetchMsg ? Utility::fetchLinkMessage($lang,VW::JB,'edit_route_unavailable') : 'Edit job route is unavailable. Please contact technical support or your domain administrator.') ?? __('Edit job route is unavailable. Please contact technical support or your domain administrator.');
    }

    $canDelete = Gate::check('delete job');
    if($canDelete){
        $destroyBase = VW::JB.'.destroy';
        $destroyGuard = ($canFetchMsg ? Utility::fetchLinkMessage($lang,VW::JB,'job_destroy_route_unavailable') : 'Delete job route is unavailable. Please contact technical support or your domain administrator.') ?? __('Delete job route is unavailable. Please contact technical support or your domain administrator.');
        $confirmSure = ($canFetchMsg ? Utility::fetchLinkMessage($lang,'generics','are_you_sure') : 'Are You Sure?') ?? __('Are You Sure?');
        $confirmIrrev = ($canFetchMsg ? Utility::fetchLinkMessage($lang,'generics','irreversible_action') : 'This action can not be undone. Do you want to continue?') ?? __('This action can not be undone. Do you want to continue?');
    }

    $hasAnyAction = $canShow || $canEdit || $canDelete;

    $requirementBase = VW::JB.'.requirement';
    $requirementGuard = ($canFetchMsg ? Utility::fetchLinkMessage($lang,VW::JB,'requirement_job_route_unavailable') : 'Job requirement route is unavailable. Please contact technical support or your domain administrator.') ?? __('Job requirement route is unavailable. Please contact technical support or your domain administrator.');

    $jobs = (is_array($jobs ?? null) && count($jobs ?? [])) ? $jobs : (($jobs ?? null) instanceof Collection && $jobs->isNotEmpty() ? $jobs : []);
    $tot = (int) data_get($data ?? [],'total',0);
    $act = (int) data_get($data ?? [],'active',0);
    $inact = (int) data_get($data ?? [],'in_active',0);
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@section(YieldingConstants::ADM_PG_TTL){{ __('Manage Job') }}@endsection

@section(YieldingConstants::ADM_BDC)
    <li class="breadcrumb-item">
        <a href="{{ $dashUrl }}" data-url="{{ $dashUrl }}" data-guard-msg="{{ $dashGuard }}" data-sv-localized="true" {{ $dashUrl !== '#' ? '' : 'aria-disabled=true' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="breadcrumb-item">{{ __('Job') }}</li>
@endsection

@push(StacksConstants::ADM_SCR_PG)
    <script>window.JOBS_I18N={routeUnavailable:"{{ __('Requested route is unavailable. Please contact technical support or your domain administrator.') }}",copySuccess:"{{ __('Link copied to clipboard') }}",copyFail:"{{ __('Failed to copy link') }}"};</script>
    <script async src="{{ asset('assets/js/routes/jobs/lang/index.js') }}"></script>
    <script defer src="{{ asset('assets/js/routes/jobs/index.js') }}"></script>
@endpush

@section(YieldingConstants::ADM_ACT_BTN)
    <div class="{{ VC::FEND }}">
        @if($canCreate)
            <a href="{{ $createUrl }}" class="{{ VC::BT_SM_PM }} route-guard" data-url="{{ $createUrl }}" data-guard-msg="{{ $createGuard }}" data-bs-toggle="tooltip" title="{{ __('Create') }}" data-title="{{ __('Create New Job') }}">
                <i class="{{ VC::TI_PLS }}"></i>
            </a>
        @endif
    </div>
@endsection

@section(YieldingConstants::ADM_CTT)
    <div class="{{ VC::RW }}">
        <div class="{{ VC::CL4 }} {{ VC::CM6 }}">
            <div class="{{ VC::CD }}"><div class="card-body">
                <div class="{{ VC::RW }} {{ VC::JCB }} {{ VC::ALC }}">
                    <div class="{{ VC::C_AT }} {{ VC::MB3 }}">
                        <div class="{{ VC::DFL_AIC }}">
                            <div class="theme-avatar {{ VC::BG_P }}"><i class="{{ VC::TI }} {{ VC::TI }}-cast"></i></div>
                            <div class="{{ VC::MS2 }}"><small class="{{ VC::TXT_MT }}">{{ __('Total') }}</small><h6 class="{{ VC::MB0 }}">{{ __('Jobs') }}</h6></div>
                        </div>
                    </div>
                    <div class="{{ VC::C_AT }} text-end"><h4 class="{{ VC::MB0 }}">{{ $tot }}</h4></div>
                </div>
            </div></div>
        </div>
        <div class="{{ VC::CL4 }} {{ VC::CM6 }}">
            <div class="{{ VC::CD }}"><div class="card-body">
                <div class="{{ VC::RW }} {{ VC::JCB }} {{ VC::ALC }}">
                    <div class="{{ VC::C_AT }} {{ VC::MB3 }}">
                        <div class="{{ VC::DFL_AIC }}">
                            <div class="theme-avatar bg-info"><i class="{{ VC::TI }} {{ VC::TI }}-cast"></i></div>
                            <div class="{{ VC::MS2 }}"><small class="{{ VC::TXT_MT }}">{{ __('Active') }}</small><h6 class="{{ VC::MB0 }}">{{ __('Jobs') }}</h6></div>
                        </div>
                    </div>
                    <div class="{{ VC::C_AT }} text-end"><h4 class="{{ VC::MB0 }}">{{ $act }}</h4></div>
                </div>
            </div></div>
        </div>
        <div class="{{ VC::CL4 }} {{ VC::CM6 }}">
            <div class="{{ VC::CD }}"><div class="card-body">
                <div class="{{ VC::RW }} {{ VC::JCB }} {{ VC::ALC }}">
                    <div class="{{ VC::C_AT }} {{ VC::MB3 }}">
                        <div class="{{ VC::DFL_AIC }}">
                            <div class="theme-avatar bg-warning"><i class="{{ VC::TI }} {{ VC::TI }}-cast"></i></div>
                            <div class="{{ VC::MS2 }}"><small class="{{ VC::TXT_MT }}">{{ __('Inactive') }}</small><h6 class="{{ VC::MB0 }}">{{ __('Jobs') }}</h6></div>
                        </div>
                    </div>
                    <div class="{{ VC::C_AT }} text-end"><h4 class="{{ VC::MB0 }}">{{ $inact }}</h4></div>
                </div>
            </div></div>
        </div>
    </div>

    <div class="{{ VC::RW }}">
        <div class="{{ VC::CM12 }}">
            <div class="{{ VC::CD }}"><div class="card-body table-border-style">
                <div class="table-responsive">
                    <table class="{{ VC::TB }} datatable">
                        <thead>
                            <tr>
                                <th>{{ __('Branch') }}</th>
                                <th>{{ __('Title') }}</th>
                                <th>{{ __('Start Date') }}</th>
                                <th>{{ __('End Date') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th>{{ __('Created At') }}</th>
                                @if($hasAnyAction)<th width="200px">{{ __('Action') }}</th>@endif
                            </tr>
                        </thead>
                        <tbody class="font-style">
                            @if(Utility::isFilled($jobs))
                                @foreach($jobs as $job)
                                    @php
                                        $jid = data_get($job,'id');
                                        $branchName = data_get($job,'branches.name',__('All'));
                                        $title = data_get($job,'title',__('No title available'));
                                        $code = data_get($job,'code','');
                                        $creatorLang = data_get($job,'createdBy.lang',DatabaseConstants::DEFAULT_LANG);
                                        $statusKey = data_get($job,'status','in_active');
                                        $statusTxt = data_get(\App\Models\Job::$status,$statusKey,$statusKey);
                                        $isActive = $statusKey === 'active';
                                        $reqUrl = Route::has($requirementBase) ? route($requirementBase,[$code,$creatorLang]) : '#';

                                        if($canShow){ $showUrl = Route::has($showBase) ? route($showBase,$jid) : '#'; }
                                        if($canEdit){ $editUrl = Route::has($editBase) ? route($editBase,$jid) : '#'; }
                                        if($canDelete){ $destroyUrl = Route::has($destroyBase) ? route($destroyBase,$jid) : '#'; }

                                        $startOut = $canDateFormat ? ($job?->start_date ? $user?->dateFormat($job->start_date) : __('No start date available')) : __('Failed to format date');
                                        $endOut = $canDateFormat ? ($job?->end_date ? $user?->dateFormat($job->end_date) : __('No end date available')) : __('Failed to format date');
                                        $createdOut = $canDateFormat ? ($job?->created_at ? $user?->dateFormat($job->created_at) : __('No created date available')) : __('Failed to format date');
                                    @endphp
                                    <tr>
                                        <td>{{ $branchName }}</td>
                                        <td>{{ $title }}</td>
                                        <td>{{ $startOut }}</td>
                                        <td>{{ $endOut }}</td>
                                        <td>
                                            @if($isActive)
                                                <span class="status_badge badge bg-primary p-2 px-3 rounded">{{ $statusTxt }}</span>
                                            @else
                                                <span class="status_badge badge bg-danger p-2 px-3 rounded">{{ $statusTxt }}</span>
                                            @endif
                                        </td>
                                        <td>{{ $createdOut }}</td>
                                        @if($hasAnyAction)
                                            <td>
                                                @if($isActive)
                                                    <div class="{{ VC::ACT_BTN_WRN }}">
                                                        <a href="{{ $reqUrl }}" class="{{ VC::BT_SM_CT }} route-guard copy-link"
                                                           data-url="{{ $reqUrl }}" data-guard-msg="{{ $requirementGuard }}"
                                                           data-bs-toggle="tooltip" title="{{ __('Copy') }}" data-original-title="{{ __('Click to copy') }}">
                                                            <i class="{{ VC::TI }} {{ VC::TI }}-link {{ VC::TXT_WT }}"></i>
                                                        </a>
                                                    </div>
                                                @endif

                                                @if($canShow)
                                                    <div class="{{ VC::ACT_BTN_INF }}">
                                                        <a href="{{ $showUrl }}" class="{{ VC::BT_SM_CT }} route-guard"
                                                           data-url="{{ $showUrl }}" data-guard-msg="{{ $showGuard }}"
                                                           data-title="{{ __('Job Detail') }}" title="{{ __('View') }}"
                                                           data-bs-toggle="tooltip" data-original-title="{{ __('View Detail') }}">
                                                            <i class="{{ VC::TI_EYE_WT }}"></i>
                                                        </a>
                                                    </div>
                                                @endif

                                                @if($canEdit)
                                                    <div class="{{ VC::ACT_BTN_PRIM }}">
                                                        <a href="{{ $editUrl }}" class="{{ VC::BT_SM_CT }} route-guard"
                                                           data-url="{{ $editUrl }}" data-guard-msg="{{ $editGuard }}"
                                                           data-title="{{ __('Edit Job') }}" data-bs-toggle="tooltip"
                                                           title="{{ __('Edit') }}" data-original-title="{{ __('Edit') }}">
                                                            <i class="{{ VC::TI_PC_WT }}"></i>
                                                        </a>
                                                    </div>
                                                @endif

                                                @if($canDelete)
                                                    <div class="{{ VC::ACT_BTN_DNG_2 }}">
                                                        {!! Collective\Html\FormFacade::open(['method'=>'DELETE','route'=>[$destroyBase,$jid],'id'=>'delete-form-'.$jid,'data-url'=>$destroyUrl,'data-guard-msg'=>$destroyGuard]) !!}
                                                            <a href="#"
                                                               class="{{ VC::BT_SM_CT_PR }} route-guard"
                                                               data-url="{{ $destroyUrl }}" data-guard-msg="{{ $destroyGuard }}"
                                                               data-bs-toggle="tooltip" title="{{ __('Delete') }}" data-original-title="{{ __('Delete') }}"
                                                               data-confirm="{{ $confirmSure }}|{{ $confirmIrrev }}"
                                                               data-confirm-yes="document.getElementById('delete-form-{{ $jid }}').submit();">
                                                                <i class="{{ VC::TI_TRS_WT }}"></i>
                                                            </a>
                                                        {!! Collective\Html\FormFacade::close() !!}
                                                    </div>
                                                @endif
                                            </td>
                                        @endif
                                    </tr>
                                @endforeach
                            @else
                                <tr><td colspan="{{ $hasAnyAction ? 7 : 6 }}" class="text-center">{{ __('No jobs available') }}</td></tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div></div>
        </div>
    </div>
@endsection
