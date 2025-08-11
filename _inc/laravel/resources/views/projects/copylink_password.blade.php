@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        YieldingConstants,
        SettingsConstants,
        StacksConstants
    };
    use App\Models\Utility;
    $settings = Utility::settings();
    $logo = Utility::getFile();
    $languages = Utility::languages();
    $company_logo = Utility::getValByName(SettingsConstants::CPN_LG);
@endphp
@extends(ExtendingLayoutsConstants::AUTH)
@section(YieldingConstants::AUTH_PG_TTL)
    {{__('Copylink')}}
@endsection
@section(YieldingConstants::AUTH_TB)
@endsection
@section(YieldingConstants::AUTH_CTT)
    <div class="">
        <h2 class="h3">{{__('Password required')}}</h2>
        <h6>{{ __('This document is password-protected. Please enter a password.') }}</h6>
    </div>
    <form method="POST" action="{{ route('projects.link', \Illuminate\Support\Facades\Crypt::encrypt($id)) }}">
        @csrf
        <div class="">
            <div class="form-group ">
                <label class="form-control-label mt-2 mb-2">{{__('Password')}}</label>
                <div class="input-group input-group-merge">
                    <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="new-password">
                    @error('password')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
            </div>
            <div class="d-grid">
                <button type="submit" class="btn-login btn btn-primary btn-block mt-2" >{{__('Save')}}</button>
            </div>
        </div>
    {{Collective\Html\FormFacade::close()}}
@endsection



