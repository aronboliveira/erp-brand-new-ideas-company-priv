@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        PermissionsConstants,
        StacksConstants,
        ViewsConstants,
        ViewClassNamesConstants as VC,
        YieldingConstants,
    };
    use App\Models\Utility;
    use Illuminate\Support\Facades\{Auth, Route};
    $profile = Utility::getFile('uploads/avatar/');
    $user ??= Auth::user();
    $lang = Utility::fetchUserLang(user: $user);
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@push(StacksConstants::ADM_SCR_PG)
@endpush
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
    <li class="breadcrumb-item active" aria-current="page">{{__('All Project')}}</li>
@endsection
@push(StacksConstants::ADM_CSS)
    <link rel="stylesheet" href="{{ asset('css/datatable/buttons.dataTables.min.css') }}">
    <style>
        .table.dataTable.no-footer {
            border-bottom: none !important;
        }
        .display-none {
            display: none !important;
        }
    </style>
@endpush
@section(YieldingConstants::ADM_CTT)
    @if($user?->type === PermissionsConstants::CPN)
        <div class="row">
            <div class="col-sm-12">
                <div class="mt-2" >
                    <div class="card">
                        @php
                            $indexRouteName = ViewsConstants::PRJ_RPT.'.index';
                            $indexRoute = Route::has($indexRouteName) ? [$indexRouteName] : ['#'];
                            $indexUrl = Route::has($indexRouteName) ? route($indexRouteName) : '#';
                            $resetClass = 'reset-project-report-link';
                        @endphp
                        <div class="card-body">
                            {!! Collective\Html\FormFacade::open(['route'=>$indexRoute,'method'=>'GET','id'=>'project_report_submit']) !!}
                                <div class="{{ VC::R_FLX_ALC_JCE }}">
                                    <div class="{{ VC::CL_POS2 }} mb-0">
                                        <div class="btn-box">
                                            {{ Collective\Html\FormFacade::label('users',__('Users'),['class'=>'form-label']) }}
                                            <select class="select form-select" name="all_users" id="all_users">
                                                <option value="">{{ __('All Users') }}</option>
                                                @foreach ($users as $user)
                                                    <option value="{{ $user->id }}" {{ isset($_GET['all_users']) && $_GET['all_users']==$user->id ? 'selected' : '' }}>
                                                        {{ $user->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="{{ VC::CL_POS1 }}">
                                        <div class="btn-box">
                                            {{ Collective\Html\FormFacade::label('status',__('Status'),['class'=>'form-label']) }}
                                            {{ Collective\Html\FormFacade::select('status',[''=>'Select Status']+$status,isset($_GET['status'])?$_GET['status']:'',['class'=>'form-control select']) }}
                                        </div>
                                    </div>
                                    <div class="{{ VC::CL_POS3 }}">
                                        <div class="btn-box">
                                            {{ Collective\Html\FormFacade::label('start_date',__('Start Date'),['class'=>'form-label']) }}
                                            {{ Collective\Html\FormFacade::date('start_date',isset($_GET['start_date'])?$_GET['start_date']:'',['class'=>'form-control month-btn']) }}
                                        </div>
                                    </div>
                                    <div class="{{ VC::CL_POS3 }}">
                                        <div class="btn-box">
                                            {{ Collective\Html\FormFacade::label('end_date',__('End Date'),['class'=>'form-label']) }}
                                            {{ Collective\Html\FormFacade::date('end_date',isset($_GET['end_date'])?$_GET['end_date']:'',['class'=>'form-control month-btn']) }}
                                        </div>
                                    </div>
                                    <div class="{{ VC::C_AT_FEND }}">
                                        <a href="#" class="{{ VC::BT_SM_PM }}" onclick="document.getElementById('project_report_submit').submit();return false;" data-toggle="tooltip" data-original-title="{{ __('apply') }}">
                                            <span class="btn-inner--icon"><i class="{{ VC::TI_SRC }}"></i></span>
                                        </a>
                                        <a href="{{ $indexUrl }}" id="{{ $resetClass }}" class="{{ VC::BT_SM_DG }} {{ $resetClass }}" data-url="{{ $indexUrl }}"
                                        data-sv-localized="true" data-guard-msg="{{ Utility::fetchLinkMessage($lang,ViewsConstants::PRJ_RPT,'project_report_index_route_unavailable') ?? 'Reset route is unavailable. Please contact technical support or your domain administrator.' }}" data-toggle="tooltip" data-original-title="{{ __('Reset') }}">
                                            <span class="btn-inner--icon"><i class="{{ VC::TI_TRS_OFF }}"></i></span>
                                        </a>
                                    </div>
                                </div>
                            {!! Collective\Html\FormFacade::close() !!}
                        </div>
                        <script defer src="{{ asset('assets/js/routes/projectReports/reset.js') }}"></script>
                    </div>
                </div>
            </div>
        </div>
    @endif
    <div class="col-xl-12 mt-3">
        <div class="card table-card">
            <div class="card-header card-body table-border-style">
                <div class="table-responsive">
                    <table class="table datatable">
                        <thead class="">
                            <tr>
                                <th>{{__('Projects')}}</th>
                                <th>{{__('Start Date')}}</th>
                                <th>{{__('Due Date')}}</th>
                                <th>{{__('Projects Members')}}</th>
                                <th>{{__('Completion')}}</th>
                                <th>{{__('Status')}}</th>
                                <th>{{__('Action')}}</th>
                            </tr>
                        </thead>
                        <tbody>
                        @if(isset($projects) && !empty($projects) && count($projects) > 0)
                            @foreach ($projects as $key => $project)
                                <tr>
                                    <td>
                                        <div class="{{ VC::DFL_AIC }}">
                                            <p class="mb-0"><a  class="name mb-0 h6 text-sm">{{ $project->project_name }}</a></p>
                                        </div>
                                    </td>
                                    <td>{{ Utility::getDateFormated($project->start_date) }}</td>
                                    <td>{{ Utility::getDateFormated($project->end_date) }}</td>
                                    <td class="">
                                        <div class="avatar-group" id="project_{{ $project->id }}">
                                            @if(isset($project->users) && !empty($project->users) && count($project->users) > 0)
                                                @foreach($project->users as $key => $user)
                                                    @if($key < 3)
                                                        <a href="#" class="{{ VC::AV_CC }}">
                                                            <img @if($user->avatar) src="{{asset('/storage/uploads/avatar/'.$user?->avatar)}}" @else src="{{asset('/storage/uploads/avatar/avatar.png')}}" @endif
                                                            title="{{ $user->name }}" style="height:36px;width:36px;">
                                                        </a>
                                                    @else
                                                        @break
                                                    @endif
                                                @endforeach
                                                @if(count($project->users) > 3)
                                                    <a href="#" class="{{ VC::AV_CC_SM }}">
                                                        <img avatar="+ {{ count($project->users)-3 }}" style="height:36px;width:36px;">
                                                    </a>
                                                @endif
                                            @else
                                                {{ __('-') }}
                                            @endif
                                        </div>
                                    </td>
                                    <td class=""> 
                                        <h6 class="mb-0 text-success">{{ $project->projectProgress($project,$last_task->id)['percentage'] }}</h6>
                                        <div class="progress mb-0"><div class="progress-bar bg-{{ $project->projectProgress($project,$last_task->id)['color'] }}" style="width: {{ $project->projectProgress($project,$last_task->id)['percentage'] }};"></div>
                                        </div>
                                    </td>
                                    <td class="">/
                                        <span class="badge bg-{{\App\Models\Project::$status_color[$project->status]}} p-2 px-3 rounded status_badge">{{ __(\App\Models\Project::$project_status[$project->status]) }}</span>
                                    </td>
                                    <td class="">
                                        @can(PermissionsConstants::MNG_PRJ)
                                            @php
                                                $showRouteName = ViewsConstants::PRJ_RPT.'.show';
                                                $showUrl = Route::has($showRouteName) ? route($showRouteName,$project->id) : '#';
                                                $showClass = 'show-project-report-link';
                                            @endphp
                                            <div class="{{ VC::ACT_BTN_WRN }}">
                                                <a href="{{ $showUrl }}"
                                                   id="{{ $showClass }}-{{ $project->id }}"
                                                   class="{{ VC::BT_SM_CT }} {{ $showClass }}"
                                                   data-url="{{ $showUrl }}"
                                                   data-sv-localized="true"
                                                   data-guard-msg="{{ Utility::fetchLinkMessage($lang,ViewsConstants::PRJ_RPT,'show_project_report_unavailable') ?? 'View project report route is unavailable. Please contact technical support or your domain administrator.' }}"
                                                   data-bs-toggle="tooltip"
                                                   title="{{ __('View Project Report') }}"
                                                   data-original-title="{{ __('Detail') }}"
                                                >
                                                    <i class="{{ VC::TI_EYE_WT }}"></i>
                                                </a>
                                            </div>
                                            <script defer src="{{ asset('assets/js/routes/projectReports/show.js') }}"></script>
                                        @endcan
                                        @can('edit project')
                                            @php
                                                $editRouteName = ViewsConstants::PRJ.'.edit';
                                                $editUrl = Route::has($editRouteName) ? route($editRouteName,$project->id) : '#';
                                                $editClass = 'edit-project-link';
                                            @endphp
                                            <div class="{{ VC::ACT_BTN_PRIM }}">
                                                <a href="{{ $editUrl }}"
                                                   id="{{ $editClass }}-{{ $project->id }}"
                                                   class="{{ VC::BT_SM_FL_CT }} {{ $editClass }}"
                                                   data-url="{{ $editUrl }}"
                                                   data-sv-localized="true"
                                                   data-guard-msg="{{ Utility::fetchLinkMessage($lang,ViewsConstants::PRJ,'project_report_edit_route_unavailable') ?? 'Edit project route is unavailable. Please contact technical support or your domain administrator.' }}"
                                                   data-ajax-popup="true"
                                                   data-size="lg"
                                                   data-bs-toggle="tooltip"
                                                   title="{{ __('Edit') }}"
                                                   data-title="{{ __('Edit Project') }}"
                                                >
                                                    <i class="{{ VC::TI_PC_WT }}"></i>
                                                </a>
                                            </div>
                                            <script defer src="{{ asset('assets/js/routes/projects/edit.js') }}"></script>
                                        @endcan
                                    </td>                                    
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <th scope="col" colspan="7"><h6 class="text-center">{{__('No Projects Found.')}}</h6></th>
                            </tr>
                        @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

