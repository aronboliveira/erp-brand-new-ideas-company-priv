@extends('layouts.admin')
@php
   // $profile=asset(Storage::url('uploads/avatar/'));
    $profile=\App\Models\Utility::get_file('uploads/avatar');
@endphp
@section('page-title')
    {{__('Manage User')}}
@endsection
@push('script-page')
@endpush
@section('breadcrumb')
    <li class="{{ VC::BCI }}"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="{{ VC::BCI }}">{{__('User')}}</li>
@endsection
@section('action-btn')
    <div class="{{ VC::FEND }}">
        @if (\Auth::user()->type == 'company' || \Auth::user()->type == 'HR')
            <a href="{{ route('user.userlog') }}" class="{{ VC::BT_PRM }} btn-sm {{ Request::segment(1) == 'user' }}"
                   data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('User Logs History') }}"><i class="ti ti-user-check"></i>
            </a>
        @endif
        @can('create user')
            <a href="#" data-size="lg" data-url="{{ route('users.create') }}" data-ajax-popup="true"  data-bs-toggle="tooltip" title="{{__('Create')}}"  class="{{ VC::BT_SM_PM }}">
                <i class="{{ VC::TI_PLS }}"></i>
            </a>
        @endcan
    </div>
@endsection
@section('content')
    <div class="row">
        <div class="col-xxl-12">
            <div class="row">
                @foreach($users as $user)
                    <div class="{{ VC::CM3 }} {{ VC::MB4 }}">
                        <div class="card {{ VC::TXCT }} card-2">
                            <div class="{{ VC::CD_HD }} border-0 pb-0">
                                <div class="{{ VC::DFL_JCB }} {{ VC::ALC }}">
                                    <h6 class="{{ VC::MB0 }}">
                                        <div class="badge {{ VC::BG_P }} p-2 {{ VC::PX3 }} rounded">
                                            {{ ucfirst($user->type) }}
                                        </div>
                                    </h6>
                                </div>
                                @if(Gate::check('edit user') || Gate::check('delete user'))
                                    <div class="card-header-right">
                                        <div class="btn-group card-option">
                                            @if($user->is_active == 1)
                                                <button type="button" class="btn dropdown-toggle"
                                                        data-bs-toggle="dropdown" aria-haspopup="true"
                                                        aria-expanded="false">
                                                    <i class="{{ VC::TD_DOTV }}"></i>
                                                </button>

                                                <div class="{{ VC::DRP_MN_END }}">

                                                    @can('edit user')
                                                        <a href="#!" data-size="lg" data-url="{{ route('users.edit',$user->id) }}" data-ajax-popup="true" class="{{ VC::DRP_IT }}" data-bs-original-title="{{__('Edit User')}}">
                                                            <i class="{{ VC::TI_PC }}"></i>
                                                            <span>{{__('Edit')}}</span>
                                                        </a>
                                                    @endcan

                                                    @can('delete user')
                                                        {!! Form::open(['method' => 'DELETE', 'route' => ['users.destroy', $user['id']],'id'=>'delete-form-'.$user['id']]) !!}
                                                        <a href="#!"  class="{{ VC::DRP_IT }} bs-pass-para">
                                                            <i class="{{ VC::TI_ARC }}"></i>
                                                            <span> @if($user->delete_status!=0){{__('Delete')}} @else {{__('Restore')}}@endif</span>
                                                        </a>
                                                        {!! Form::close() !!}
                                                    @endcan

                                                    <a href="#!" data-url="{{route('users.reset',\Crypt::encrypt($user->id))}}" data-ajax-popup="true" data-size="md" class="{{ VC::DRP_IT }}" data-bs-original-title="{{__('Reset Password')}}">
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

                            <div class="{{ VC::CD_BD }} full-card">
                                <div class="{{ VC::IMG_FL }} rounded-circle card-avatar">
                                    <img src="{{(!empty($user->avatar))? asset(Storage::url("uploads/avatar/".$user->avatar)): asset(Storage::url("uploads/avatar/avatar.png"))}}" class="img-user wid-80 round-img rounded-circle">
                                </div>
                                <h4 class="{{ VC::MT3 }} {{ VC::TX_PM }}">{{ $user->name }}</h4>
                                @if($user->delete_status==0)
                                    <h5 class="office-time {{ VC::MB0 }}">{{__('Soft Deleted')}}</h5>
                                @endif
                                <small class="{{ VC::TX_PM }}">{{ $user->email }}</small>
                                <p></p>
                                <div class="{{ VC::TXCT }}" data-bs-toggle="tooltip" title="{{__('Last Login')}}">
                                    {{ (!empty($user->last_login_at)) ? $user->last_login_at : '' }}
                                </div>
                                @if(\Auth::user()->type == 'super admin')
                                    <div class="{{ VC::MT4 }}">
                                        <div class="row {{ VC::JCB }} {{ VC::ALC }}">
                                            <div class="{{ VC::C6 }} {{ VC::TXCT }}">
                                                <span class="{{ VC::DBL }} font-bold {{ VC::MB0 }}">{{!empty($user->currentPlan)?$user->currentPlan->name:''}}</span>
                                            </div>
                                            <div class="{{ VC::C6 }} {{ VC::TXCT }} Id">
                                                <a href="#" data-url="{{ route('plan.upgrade',$user->id) }}" data-size="lg" data-ajax-popup="true" class="{{ VC::BT_OUTPM }}"
                                                   data-title="{{__('Upgrade Plan')}}">{{__('Upgrade Plan')}}</a>
                                            </div>
                                            <div class="{{ VC::C12 }}">
                                                <hr class="{{ VC::MY3 }}">
                                            </div>
                                            <div class="{{ VC::C12 }} {{ VC::TXCT }} pb-2">
                                                <span class="{{ VC::TX_DK }} {{ VC::TXS }}">{{__('Plan Expired : ') }} {{!empty($user->plan_expire_date) ? \Auth::user()->dateFormat($user->plan_expire_date): __('Lifetime')}}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row {{ VC::MT3 }}">
                                        <div class="{{ VC::C12 }} {{ VC::CS12 }}">
                                            <div class="card {{ VC::MB0 }}">
                                                <div class="{{ VC::CD_BD }} p-3">
                                                    <div class="row">
                                                        <div class="col-4">
                                                            <p class="{{ VC::TXT_MT_TXSM_MB0 }}" data-bs-toggle="tooltip" title="{{__('Users')}}"><i class="{{ VC::TI_USRS }} card-icon-text-space"></i>{{ $userCounts[$user->id] ?? 0 }}</p>
                                                        </div>
                                                        <div class="col-4">
                                                            <p class="{{ VC::TXT_MT_TXSM_MB0 }}" data-bs-toggle="tooltip" title="{{__('Customers')}}"><i class="{{ VC::TI_USRS }} card-icon-text-space"></i>{{ $customerCounts[$user->id] ?? 0 }}</p>
                                                        </div>
                                                        <div class="col-4">
                                                            <p class="{{ VC::TXT_MT_TXSM_MB0 }}" data-bs-toggle="tooltip" title="{{__('Vendors')}}"><i class="{{ VC::TI_USRS }} card-icon-text-space"></i>{{ $vendorCounts[$user->id] ?? 0 }}</p>
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
