@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        StacksConstants,
        PermissionsConstants,
        ViewsConstants,
        YieldingConstants,
    };
    use Illuminate\Support\Facades\Route;
    $profile=\App\Models\Utility::getFile('uploads/avatar');
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@section(YieldingConstants::ADM_PG_TTL)
    {{__('Manage User Log')}}
@endsection
@push(StacksConstants::ADM_SCR_PG)
@endpush
@section(YieldingConstants::ADM_BDC)
    <li class="breadcrumb-item">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
        {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="breadcrumb-item">{{__('User Log')}}</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="mt-2" id="multiCollapseExample1">
                <div class="card">
                    <div class="card-body">
                        {{ Collective\Html\FormFacade::open(array('route' => array(ViewsConstants::USR.'.userlog'),'method'=>'get','id'=>'user_userlog')) }}
                        <div class="row align-items-center justify-content-end">
                            <div class="col-xl-10">
                                <div class="row">
                                    <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                                    </div>
                                    <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                                    </div>
                                    <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                                        <div class="btn-box">
                                            {{Collective\Html\FormFacade::label('month',__('Month'),['class'=>'form-label'])}}
                                            {{Collective\Html\FormFacade::month('month',isset($_GET['month'])?$_GET['month']:date('Y-m'),array('class'=>'month-btn form-control'))}}
                                        </div>
                                    </div>
                                    <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                                        <div class="btn-box">
                                            {{ Collective\Html\FormFacade::label('users', __('User'),['class'=>'form-label']) }}
                                            {{ Collective\Html\FormFacade::select('users', $filteruser,isset($_GET['users'])?$_GET['users']:'', array('class' => 'form-control select')) }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-auto mt-4">
                                <div class="row">
                                    <div class="col-auto">
                                        <a href="#" class="btn btn-sm btn-primary" onclick="document.getElementById('user_userlog').submit(); return false;" data-bs-toggle="tooltip" title="{{__('Apply')}}" data-original-title="{{__('apply')}}">
                                            <span class="btn-inner--icon"><i class="ti ti-search"></i></span>
                                        </a>
                                        <a href="{{route(ViewsConstants::USR.'.userlog')}}" class="btn btn-sm btn-danger" data-bs-toggle="tooltip"  title="{{ __('Reset') }}" data-original-title="{{__('Reset')}}">
                                            <span class="btn-inner--icon"><i class="ti ti-trash-off text-white-off"></i></span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    {{ Collective\Html\FormFacade::close() }}
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
                                    <th>{{ __('User Name') }}</th>
                                    <th>{{ __('Role') }}</th>
                                    <th>{{ __('Last Login') }}</th>
                                    <th>{{ __('Ip') }}</th>
                                    <th>{{ __('Country') }}</th>
                                    <th>{{ __('Device') }}</th>
                                    <th>{{ __('OS') }}</th>
                                    <th>{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($userDetails as $user)
                                    @php
                                        $userDetail = json_decode($user?->Details);
                                    @endphp
                                    <tr>
                                        <td>{{ $user?->user_name }}</td>
                                        <td>
                                            <span class="me-5 badge p-2 px-3 rounded bg-primary status_badge">{{$user?->user_type}}</span>
                                        </td>
                                        <td>{{ !empty($user?->date) ? $user?->date : '-' }}</td>
                                        <td>{{ $user?->ip }}</td>
                                        <td>{{ !empty($userDetail->country)?$userDetail->country:'-' }}</td>
                                        <td>{{ $userDetail->device_type }}</td>
                                        <td>{{ $userDetail->os_name }}</td>
                                        <td>
                                            <div class="action-btn bg-warning ms-2">
                                                <a href="#" class="mx-3 btn btn-sm align-items-center" data-size="lg" data-url="{{ route(ViewsConstants::USR.'.userlogview', [$user?->id]) }}"
                                                   data-ajax-popup="true" data-size="md" data-bs-toggle="tooltip" title="" data-title="{{ __('View User Logs') }}" data-bs-original-title="{{ __('View') }}">
                                                    <i class="ti ti-eye text-white"></i>
                                                </a>
                                            </div>
                                            @can(PermissionsConstants::DEL_USER)
                                                <div class="action-btn bg-danger ms-2">
                                                    {!! Collective\Html\FormFacade::open(['method' => 'DELETE','route' => [ViewsConstants::USR.'.userlogdestroy', $user?->user_id],'id' => 'delete-form-' . $user?->id,]) !!}
                                                    <a href="#" class="mx-3 btn btn-sm align-items-center bs-pass-para" data-bs-toggle="tooltip" title="" data-bs-original-title="Delete" aria-label="Delete">
                                                        <i class="ti ti-trash text-white"></i>
                                                    </a>
                                                    </form>
                                                </div>
                                            @endcan
                                        </td>
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
