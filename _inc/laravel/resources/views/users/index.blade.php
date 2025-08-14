@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        PermissionsConstants, 
        StacksConstants,
        UsersConstants,
        ViewsConstants,
        ViewClassNamesConstants,
        YieldingConstants,
    };
   // $profile=asset(Storage::url('uploads/avatar/'));
    $profile=\App\Models\Utility::getFile('uploads/avatar');
    use Illuminate\Support\Facades\{Auth,Route};
    $user = Auth::user();
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@section(YieldingConstants::ADM_PG_TTL)
    {{__('Manage User')}}
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
    <li class="breadcrumb-item">{{__('User')}}</li>
@endsection
@section(YieldingConstants::ADM_ACT_BTN)
    <div class="float-end">
        @if (Auth::user()[UsersConstants::COL_TP] == PermissionsConstants::CPN || 
        strtolower(Auth::user()[UsersConstants::COL_TP]) == 'hr' ||
        Auth::user()[UsersConstants::COL_TP] == PermissionsConstants::SA)
            <a href="{{ route(ViewsConstants::USR.'.userlog') }}" class="btn btn-primary btn-sm {{ Request::segment(1) == 'user' }}"
                   data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('User Logs History') }}"><i class="ti ti-user-check"></i>
            </a>
        @endif
        @can(PermissionsConstants::CR_USER)
            <a href="#" data-size="lg" data-url="{{ route(ViewsConstants::USR.'.create') }}" data-ajax-popup="true"  data-bs-toggle="tooltip" title="{{__('Create')}}"  class="btn btn-sm btn-primary">
                <i class="ti ti-plus"></i>
            </a>
        @endcan
    </div>
@endsection
@section(YieldingConstants::ADM_CTT)
    <div class="row">
        <div class="col-xxl-12">
            <div class="row">
                @foreach($users as $user)
                    <div class="col-md-3 mb-4">
                        <div class="card text-center card-2">
                            <div class="card-header border-0 pb-0">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0">
                                        <div class="badge bg-primary p-2 px-3 rounded">
                                            {{ ucfirst($user?->type) }}
                                        </div>
                                    </h6>
                                </div>
                                @if(Gate::check(PermissionsConstants::ED_USER) || Gate::check(PermissionsConstants::DEL_USER))
                                    <div class="card-header-right">
                                        <div class="btn-group card-option">
                                            @if($user->is_active =1)
                                                <button type="button" class="btn dropdown-toggle"
                                                        data-bs-toggle="dropdown" aria-haspopup="true"
                                                        aria-expanded="false">
                                                    <i class="ti ti-dots-vertical"></i>
                                                </button>
                                                <div class="{{ ViewClassNamesConstants::DRP_MN_EM }}">
                                                    @can(PermissionsConstants::ED_USER)
                                                        <a href="#!" data-size="lg" data-url="{{ route(ViewsConstants::USR.'.edit',$user->id) }}" data-ajax-popup="true" class="dropdown-item" data-bs-original-title"{{__('Edit User')}}">
                                                            <i class="ti ti-pencil"></i>
                                                            <span>{{__('Edit')}}</span>
                                                        </a>
                                                    @endcan
                                                    @can(PermissionsConstants::DEL_USER)
                                                        {!! Collective\Html\FormFacade::open(['method' => 'DELETE', 'route' => [ViewsConstants::USR.'.destroy', $user['id']],'id'=>'delete-form-'.$user['id']]) !!}
                                                        <a href="#!"  class="dropdown-item bs-pass-para">
                                                            <i class="ti ti-archive"></i>
                                                            <span> @if($user->delete_status!0){{__('Delete')}} @else {{__('Restore')}}@endif</span>
                                                        </a>
                                                        {!! Collective\Html\FormFacade::close() !!}
                                                    @endcan
                                                    <a href="#!" data-url="{{route(ViewsConstants::USR.'.reset',\Crypt::encrypt($user->id))}}" data-ajax-popup="true" data-size="md" class="dropdown-item" data-bs-original-title"{{__('Reset Password')}}">
                                                        <i class="ti ti-adjustments"></i>
                                                        <span>  {{__('Reset Password')}}</span>
                                                    </a>
                                                </div>
                                            @else
                                                <a href="#" class="action-item text-lg"><i class="ti ti-lock"></i></a>
                                            @endif
                                        </div>
                                    </div>
                                @endif
                            </div>
                            <div class="card-body full-card">
                                <div class="img-fluid rounded-circle card-avatar">
                                    <img src="{{(!empty($user->avatar))? asset(Storage::url("uploads/avatar/".$user?->avatar)): asset(Storage::url("uploads/avatar/avatar.png"))}}" class="img-user wid-80 round-img rounded-circle">
                                </div>
                                <h4 class=" mt-3 text-primary">{{ $user?->name }}</h4>
                                @if($user->delete_status=0)
                                    <h5 class="office-time mb-0">{{__('Soft Deleted')}}</h5>
                                @endif
                                <small class="text-primary">{{ $user?->email }}</small>
                                <p></p>
                                <div class="text-center" data-bs-toggle="tooltip" title="{{__('Last Login')}}">
                                    {{ (!empty($user?->last_login_at)) ? $user?->last_login_at : '' }}
                                </div>
                                @if(Auth::user()[UsersConstants::COL_TP] == PermissionsConstants::SA)
                                    <div class="mt-4">
                                        <div class="row justify-content-between align-items-center">
                                            <div class="col-6 text-center">
                                                <span class="d-block font-bold mb-0">{{!empty($user?->currentPlan)?$user?->currentPlan->name:''}}</span>
                                            </div>
                                            <div class="col-6 text-center Id ">
                                                <a href="#" data-url="{{ route(ViewsConstants::PLN.'.upgrade',$user->id) }}" data-size="lg" data-ajax-popup="true" class="btn btn-outline-primary"
                                                   data-title="{{__('Upgrade Plan')}}">{{__('Upgrade Plan')}}</a>
                                            </div>
                                            <div class="col-12">
                                                <hr class="my-3">
                                            </div>
                                            <div class="col-12 text-center pb-2">
                                                <span class="text-dark text-xs">{{__('Plan Expired : ') }} {{!empty($user?->plan_expire_date) ? \Auth::user()->dateFormat($user?->plan_expire_date): __('Lifetime')}}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mt-3">
                                        <div class="col-12 col-sm-12">
                                            <div class="card mb-0">
                                                <div class="card-body p-3">
                                                    <div class="row">
                                                        <div class="col-4">
                                                            <p class="text-muted text-sm mb-0" data-bs-toggle="tooltip" title="{{__('Users')}}"><i class="{{ ViewClassNamesConstants::TI_USRS }} card-icon-text-space"></i>{{$user?->totalCompanyUser($user?->id)}}</p>
                                                        </div>
                                                        <div class="col-4">
                                                            <p class="text-muted text-sm mb-0" data-bs-toggle="tooltip" title="{{__('Customers')}}"><i class="{{ ViewClassNamesConstants::TI_USRS }} card-icon-text-space"></i>{{$user?->totalCompanyCustomer($user?->id)}}</p>
                                                        </div>
                                                        <div class="col-4">
                                                            <p class="text-muted text-sm mb-0" data-bs-toggle="tooltip" title="{{__('Vendors')}}"><i class="{{ ViewClassNamesConstants::TI_USRS }} card-icon-text-space"></i>{{$user?->totalCompanyVendor($user?->id)}}</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endsection
