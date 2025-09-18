@extends(ExtendingLayoutsConstants::ADM)
@php
    use App\Config\Constants\{
        DatabaseConstants,
        ExtendingLayoutsConstants,
        PermissionsConstants,
        StacksConstants,
        ViewClassNamesConstants,
        YieldingConstants,
    };
    use App\Models\Utility;
    use Illuminate\Support\Facades\{Auth, Route};
    $user = Auth::user();
    $lang = Utility::fetchUserLang(user: $user);
@endphp
@section(YieldingConstants::ADM_PG_TTL)
    {{__('Manage Job')}}
@endsection
@section(YieldingConstants::ADM_BDC)
    <li class="breadcrumb-item">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
        {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="breadcrumb-item">{{__('Job')}}</li>
@endsection
@push(StacksConstants::ADM_SCR_PG)
    <script async src="{{ asset('assets/js/routes/jobs/lang/index.js') }}"></script>
    <script defer src="{{ asset('assets/js/routes/jobs/index.js') }}"></script>
@endpush
@section(YieldingConstants::ADM_ACT_BTN)
    <div class="float-end">
        @can(PermissionsConstants::CR_JB)
            <a href="{{ route(ViewsConstants::JB.'.create') }}" class="btn btn-sm btn-primary"  data-bs-toggle="tooltip" title="{{__('Create')}}" data-title="{{__('Create New Job')}}">
                <i class="ti ti-plus"></i>
            </a>
        @endcan
    </div>
@endsection
@section(YieldingConstants::ADM_CTT)
    <div class="row">
            <div class="col-lg-4 col-md-6">
                <div class="card">
                    <div class="card-body">
                        <div class="row align-items-center justify-content-between">
                            <div class="col-auto mb-3 mb-sm-0">
                                <div class="d-flex align-items-center">
                                    <div class="theme-avatar bg-primary">
                                        <i class="ti ti-cast"></i>
                                    </div>
                                    <div class="ms-3">
                                        <small class="text-muted">{{__('Total')}}</small>
                                        <h6 class="m-0">{{__('Jobs')}}</h6>
                                    </div>
                                </div>
                            </div>
                            <div class="col-auto text-end">
                                <h4 class="m-0">{{$data['total']}}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <div class="card">
                    <div class="card-body">
                        <div class="row align-items-center justify-content-between">
                            <div class="col-auto mb-3 mb-sm-0">
                                <div class="d-flex align-items-center">
                                    <div class="theme-avatar bg-info">
                                        <i class="ti ti-cast"></i>
                                    </div>
                                    <div class="ms-3">
                                        <small class="text-muted">{{__('Active')}}</small>
                                        <h6 class="m-0">{{__('Jobs')}}</h6>
                                    </div>
                                </div>
                            </div>
                            <div class="col-auto text-end">
                                <h4 class="m-0">{{$data['active']}}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <div class="card">
                    <div class="card-body">
                        <div class="row align-items-center justify-content-between">
                            <div class="col-auto mb-3 mb-sm-0">
                                <div class="d-flex align-items-center">
                                    <div class="theme-avatar bg-warning">
                                        <i class="ti ti-cast"></i>
                                    </div>
                                    <div class="ms-3">
                                        <small class="text-muted">{{__('Inactive')}}</small>
                                        <h6 class="m-0">{{__('Jobs')}}</h6>
                                    </div>
                                </div>
                            </div>
                            <div class="col-auto text-end">
                                <h4 class="m-0">{{$data['in_active']}}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
    </div>

        <div class="row">
            <div class="col-md-12">
                <div class="card">
                <div class="card-body table-border-style">
                        <div class="table-responsive">
                        <table class="table datatable">
                                <thead>
                                <tr>
                                    <th>{{__('Branch')}}</th>
                                    <th>{{__('Title')}}</th>
                                    <th>{{__('Start Date')}}</th>
                                    <th>{{__('End Date')}}</th>
                                    <th>{{__('Status')}}</th>
                                    <th>{{__('Created At')}}</th>
                                    @if( Gate::check('edit job') ||Gate::check('delete job') ||Gate::check('show job'))
                                        <th width="200px">{{__('Action')}}</th>
                                    @endif
                                </tr>
                                </thead>
                                <tbody class="font-style">
                                @foreach ($jobs as $job)
                                    <tr>
                                        <td>{{ !empty($job->branches)?$job->branches->name:__('All') }}</td>
                                        <td>{{$job->title}}</td>
                                        <td>{{ $user?->dateFormat($job->start_date) }}</td>
                                        <td>{{ $user?->dateFormat($job->end_date) }}</td>
                                        <td>
                                            @if($job->status=='active')
                                                <span class="status_badge badge bg-primary p-2 px-3 rounded">{{App\Models\Job::$status[$job->status]}}</span>
                                            @else
                                                <span class="status_badge badge bg-danger p-2 px-3 rounded">{{App\Models\Job::$status[$job->status]}}</span>
                                            @endif
                                        </td>
                                        <td>{{ $user?->dateFormat($job->created_at) }}</td>
                                        @if( Gate::check('edit job') ||Gate::check('delete job') || Gate::check('show job'))
                                            <td>
                                            @if($job->status!='in_active')
                                                    <div class="action-btn bg-warning ms-2">
                                                        <a href="#" id="{{ route(ViewsConstants::JB.'.requirement',[$job->code,!empty($job)?$job->createdBy->lang:DatabaseConstants::DEFAULT_LANG]) }}" class="mx-3 btn btn-sm align-items-center"  onclick="copyToClipboard(this)" data-bs-toggle="tooltip" title="{{__('Copy')}}" data-original-title="{{__('Click to copy')}}"><i class="ti ti-link text-white"></i></a>
                                                    </div>
                                                @endif
                                                @can('show job')
                                                <div class="action-btn bg-info ms-2">
                                                    <a href="{{ route(ViewsConstants::JB.'.show',$job->id) }}" data-title="{{__('Job Detail')}}" title="{{__('View')}}"  class="mx-3 btn btn-sm align-items-center" data-bs-toggle="tooltip" data-original-title="{{__('View Detail')}}">
                                                        <i class="ti ti-eye text-white"></i></a>
                                                </div>
                                                    @endcan
                                                @can('edit job')
                                                <div class="action-btn bg-primary ms-2">
                                                    <a href="{{ route(ViewsConstants::JB.'.edit',$job->id) }}" data-title="{{__('Edit Job')}}" class="mx-3 btn btn-sm align-items-center" data-bs-toggle="tooltip" title="{{__('Edit')}}" data-original-title="{{__('Edit')}}">
                                                        <i class="{{ ViewClassNamesConstants::TI_PC_WT }}"></i></a>
                                                </div>
                                                    @endcan
                                                @can('delete job')
                                                <div class="action-btn bg-danger ms-2">
                                                {!! Collective\Html\FormFacade::open(['method' => 'DELETE', 'route' => [ViewsConstants::JB.'.destroy', $job->id],'id'=>'delete-form-'.$job->id]) !!}
                                                    <a href="#" class="{{ ViewClassNamesConstants::BT_SM_CT_PR }}" data-bs-toggle="tooltip" title="{{__('Delete')}}" data-original-title="{{__('Delete')}}" data-confirm="{{ __(Utility::fetchLinkMessage($lang, 'generics', 'are_you_sure') ?? 'Are You Sure?') }}|{{ __(Utility::fetchLinkMessage($lang, 'generics', 'irreversible_action') ?? 'This action can not be undone. Do you want to continue?') }}" data-confirm-yes="document.getElementById('delete-form-{{$job->id}}').submit();">
                                                        <i class="ti ti-trash text-white"></i></a>
                                                    {!! Collective\Html\FormFacade::close() !!}
                                                    </div>
                                                @endcan
                                            </td>
                                        @endif
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
@endsection
                                                    {{--                                            <div class="action-btn bg-warning ms-2">--}}
                                                    {{--                                                <a href="{{ route(ViewsConstants::JB.'.requirement',[$job->code,!empty($job)?$job->createdBy->lang:DatabaseConstants::DEFAULT_LANG]) }}" class="mx-3 btn btn-sm align-items-center " onclick="copyToClipboard(this)" data-bs-toggle="tooltip" data-original-title="{{__('Click to copy')}}">--}}
                                                    {{--                                                    <i class="ti ti-link text-white"></i></a>--}}

                                                    {{--                                                <a href="#" id="{{ route(ViewsConstants::INV.'.link.copy',[$invoiceID]) }}" class="mx-3 btn btn-sm align-items-center"   onclick="copyToClipboard(this)" data-bs-toggle="tooltip" data-original-title="{{__('Click to copy')}}"><i class="ti ti-link text-white"></i></a>--}}

                                                    {{--                                            </div>--}}