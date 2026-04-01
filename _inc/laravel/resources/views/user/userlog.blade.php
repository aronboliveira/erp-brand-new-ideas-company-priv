@extends('layouts.admin')
@php
    // $profile=asset(Storage::url('uploads/avatar/'));
     $profile=\App\Models\Utility::get_file('uploads/avatar');
@endphp
@section('page-title')
    {{__('Manage User Log')}}
@endsection
@push('script-page')
@endpush
@section('breadcrumb')
    <li class="{{ VC::BCI }}"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="{{ VC::BCI }}">{{__('User Log')}}</li>
@endsection

@section('content')
    <div class="row">
        <div class="{{ VC::CS12 }}">
            <div class="{{ VC::MT2 }}" id="multiCollapseExample1">
                <div class="card">
                    <div class="{{ VC::CD_BD }}">
                        {{ Form::open(array('route' => array('user.userlog'),'method'=>'get','id'=>'user_userlog')) }}
                        <div class="{{ VC::R_ALC_JCE }}">
                            <div class="{{ VC::CXL10 }}">
                                <div class="row">
                                    <div class="{{ VC::CL_XL3 }}">
                                    </div>
                                    <div class="{{ VC::CL_XL3 }}">
                                    </div>
                                    <div class="{{ VC::CL_XL3 }}">
                                        <div class="btn-box">
                                            {{Form::label('month',__('Month'),['class'=>'form-label'])}}
                                            {{Form::month('month',isset($_GET['month'])?$_GET['month']:date('Y-m'),array('class'=>'month-btn form-control'))}}
                                        </div>
                                    </div>
                                    <div class="{{ VC::CL_XL3 }}">
                                        <div class="btn-box">
                                            {{ Form::label('users', __('User'),['class'=>'form-label']) }}
                                            {{ Form::select('users', $filteruser,isset($_GET['users'])?$_GET['users']:'', array('class' => 'form-control select')) }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="{{ VC::C_AT }} {{ VC::MT4 }}">
                                <div class="row">
                                    <div class="{{ VC::C_AT }}">
                                        <a href="#" class="{{ VC::BT_SM_PM }}" onclick="document.getElementById('user_userlog').submit(); return false;" data-bs-toggle="tooltip" title="{{__('Apply')}}" data-original-title="{{__('apply')}}">
                                            <span class="btn-inner--icon"><i class="{{ VC::TI_SRC }}"></i></span>
                                        </a>
                                        <a href="{{route('user.userlog')}}" class="{{ VC::BT_SM_DG }}" data-bs-toggle="tooltip"  title="{{ __('Reset') }}" data-original-title="{{__('Reset')}}">
                                            <span class="btn-inner--icon"><i class="ti ti-trash-off text-white-off "></i></span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="{{ VC::CM12 }}">
            <div class="card">
                <div class="{{ VC::CD_BD_TB_BD }}">
                    <div class="{{ VC::TB_RSP }}">
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
                                @foreach ($userdetails as $user)
                                    @php
                                        $userdetail = json_decode($user->Details);
                                    @endphp
                                    <tr>
                                        <td>{{ $user->user_name }}</td>
                                        <td>
                                            <span class="me-5 badge p-2 {{ VC::PX3 }} rounded {{ VC::BG_P }} status_badge">{{$user->user_type}}</span>
                                        </td>
                                        <td>{{ !empty($user->date) ? $user->date : '-' }}</td>
                                        <td>{{ $user->ip }}</td>
                                        <td>{{ !empty($userdetail->country)?$userdetail->country:'-' }}</td>
                                        <td>{{ $userdetail->device_type }}</td>
                                        <td>{{ $userdetail->os_name }}</td>
                                        <td>
                                            <div class="{{ VC::ACT_BTN_WRN }}">
                                                <a href="#" class="{{ VC::BT_SM_CT }}" data-size="lg" data-url="{{ route('user.userlogview', [$user->id]) }}"
                                                   data-ajax-popup="true" data-size="md" data-bs-toggle="tooltip" title="" data-title="{{ __('View User Logs') }}" data-bs-original-title="{{ __('View') }}">
                                                    <i class="{{ VC::TI_EYE_WT }}"></i>
                                                </a>
                                            </div>
                                            @can('delete user')
                                                <div class="{{ VC::ACT_BTN_DNG_2 }}">
                                                    {!! Form::open(['method' => 'DELETE','route' => ['user.userlogdestroy', $user->user_id],'id' => 'delete-form-' . $user->id,]) !!}
                                                    <a href="#" class="{{ VC::BT_SM_MX3 }} {{ VC::ALC }} bs-pass-para" data-bs-toggle="tooltip" title="" data-bs-original-title="Delete" aria-label="Delete">
                                                        <i class="{{ VC::TI_TRS_WT }} {{ VC::TXT_WT }}"></i>
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
