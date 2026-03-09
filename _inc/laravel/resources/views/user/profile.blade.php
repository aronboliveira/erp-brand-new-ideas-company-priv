@extends('layouts.admin')
@php
    $profile=\App\Models\utils\Utility::getFile('uploads/avatar/');
@endphp
@section('page-title')
    {{__('Profile Account')}}
@endsection
@push('script-page')
    <script>
        var scrollSpy = new bootstrap.ScrollSpy(document.body, {
            target: '#useradd-sidenav',
            offset: 300,
        })
        $(".list-group-item").click(function(){
            $('.list-group-item').filter(function(){
                return this.href == id;
            }).parent().removeClass('text-primary');
        });
    </script>
@endpush
@section('breadcrumb')
    <li class="{{ VC::BCI }}"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="{{ VC::BCI }}">{{__('Profile')}}</li>
@endsection

@section('content')
    <div class="row">
        <div class="{{ VC::CXL3 }}">
            <div class="{{ VC::CD_STK }}" style="top:30px">
                <div class="{{ VC::LG_FLSH }}" id="useradd-sidenav">
                    <a href="#personal_info" class="{{ VC::LGI_ACT_NBD }}">{{__('Personal Info')}} <div class="{{ VC::FEND }}"><i class="{{ VC::TI_CHV_RT }}"></i></div></a>

                    <a href="#change_password" class="{{ VC::LGI_ACT_NBD }}">{{__('Change Password')}}<div class="{{ VC::FEND }}"><i class="{{ VC::TI_CHV_RT }}"></i></div></a>
                </div>
            </div>
        </div>
        <div class="col-xl-9">
            <div id="personal_info" class="card">
                <div class="{{ VC::CD_HD }}">
                    <h5>{{__('Personal Info')}}</h5>
                </div>
                <div class="{{ VC::CD_BD }}">
                    <form method="POST" action="{{ route('users.account.update') }}" enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            <div class="{{ VC::CL6 }} {{ VC::CS6 }}">
                                <div class="{{ VC::FM_G }}">
                                    <label class="{{ VC::FM_LB_DK }}">{{__('Name')}}</label>
                                    <input class="{{ VC::FM_CT }}" name="name" type="text" id="name" placeholder="{{ __('Enter Your Name') }}" value="{{ $userDetail->name }}" required autocomplete="name">
                                    {{-- @error('name')
                                    <span class="{{ VC::INV_FB_DNG_XS }}" role="alert">{{ $message }}</span>
                                    @enderror --}}
                                </div>
                            </div>
                            <div class="{{ VC::CL6 }} {{ VC::CS6 }}">
                                <div class="{{ VC::FM_G }}">
                                    <label for="email" class="{{ VC::FM_LB_DK }}">{{__('Email')}}</label>
                                    <input class="{{ VC::FM_CT }}" name="email" type="text" id="email" placeholder="{{ __('Enter Your Email Address') }}" value="{{ $userDetail->email }}" required autocomplete="email">
                                    {{-- @error('email')
                                    <span class="{{ VC::INV_FB_DNG_XS }}" role="alert">{{ $message }}</span>
                                    @enderror --}}
                                </div>
                            </div>
                            <div class="{{ VC::CLM6 }}">
                                <div class="{{ VC::FM_G }}">
                                    <div class="choose-files">
                                        <label for="avatar">
                                            <div class="{{ VC::BG_P }} profile_update"> <i class="ti ti-upload px-1"></i>{{__('Choose file here')}}</div>
                                            <input type="file" class="{{ VC::FM_CT }} file" name="profile" id="avatar" data-filename="profile_update">
                                        </label>
                                    </div>
                                    <span class="{{ VC::TXS }} {{ VC::TXT_MT }}">{{ __('Please upload a valid image file. Size of image should not be more than 2MB.')}}</span>
                                    {{-- @error('avatar')
                                    <span class="{{ VC::INV_FB_DNG_XS }}" role="alert">{{ $message }}</span>
                                    @enderror --}}

                                </div>

                            </div>
                            <div class="{{ VC::CL12 }} {{ VC::TX_END }}">
                                <input type="submit" value="{{__('Save Changes')}}" class="{{ VC::BT_PR_INV }} {{ VC::BT_PM }} {{ VC::MR10 }}">
                            </div>
                        </div>
                    </form>

                </div>

            </div>
            <div id="change_password" class="card">
                <div class="{{ VC::CD_HD }}">
                    <h5>{{__('Change Password')}}</h5>
                </div>
                <div class="{{ VC::CD_BD }}">
                    <form method="post" action="{{route('users.password.update')}}">
                        @csrf
                        <div class="row">
                            <div class="{{ VC::CL6 }} {{ VC::CS6 }} {{ VC::FM_G }}">
                                <label for="old_password" class="{{ VC::FM_LB_DK }}">{{ __('Old Password') }}</label>
                                <input class="{{ VC::FM_CT }}" name="old_password" type="password" id="old_password" required autocomplete="old_password" placeholder="{{ __('Enter Old Password') }}">
                                {{-- @error('old_password')
                                <span class="{{ VC::INV_FB_DNG_XS }}" role="alert">{{ $message }}</span>
                                @enderror --}}
                            </div>

                            <div class="{{ VC::CL6 }} {{ VC::CS6 }} {{ VC::FM_G }}">
                                <label for="password" class="{{ VC::FM_LB_DK }}">{{ __('New Password') }}</label>
                                <input class="{{ VC::FM_CT }}" name="password" type="password" required autocomplete="new-password" id="password" placeholder="{{ __('Enter Your Password') }}">
                                {{-- @error('password')
                                <span class="{{ VC::INV_FB_DNG_XS }}" role="alert">{{ $message }}</span>
                                @enderror --}}
                            </div>
                            <div class="{{ VC::CL6 }} {{ VC::CS6 }} {{ VC::FM_G }}">
                                <label for="password_confirmation" class="{{ VC::FM_LB_DK }}">{{ __('New Confirm Password') }}</label>
                                <input class="{{ VC::FM_CT }}" name="password_confirmation" type="password" required autocomplete="new-password" id="password_confirmation" placeholder="{{ __('Enter Your Password') }}">
                            </div>
                            <div class="{{ VC::CL12 }} {{ VC::TX_END }}">
                                <input type="submit" value="{{__('Change Password')}}" class="{{ VC::BT_PR_INV }} {{ VC::BT_PM }} {{ VC::MR10 }}">
                            </div>
                        </div>
                    </form>
                </div>

            </div>
        </div>
@endsection
