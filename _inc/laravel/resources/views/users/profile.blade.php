@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        StacksConstants,
        ViewClassNamesConstants as VC,
        ViewsConstants as VW,
        YieldingConstants
    };
    use App\Models\Utility;
    use Collective\Html\FormFacade as Form;
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\{Collection, Str};

    $profile = Utility::getFile('uploads/avatar');

    $lang = Utility::fetchUserLang();

    $dashBase  = 'dashboard';
    $dashKebab = Str::kebab($dashBase);
    $dashName  = Route::has($dashBase) ? $dashBase : (Route::has($dashKebab) ? $dashKebab : null);
    $dashUrl   = $dashName ? route($dashName) : '#';

    $accBase   = VW::USR . '.account.update';
    $accKebab  = Str::kebab($accBase);
    $accName   = Route::has($accBase) ? $accBase : (Route::has($accKebab) ? $accKebab : null);
    $accUrl    = $accName ? route($accName) : '#';
    $accGuard  = Utility::fetchLinkMessage($lang, VW::USR, 'update_account_route_unavailable')
                 ?? 'Update account route is unavailable. Please contact technical support or your domain administrator.';
    $accFormId = 'profile-account-update-form';

    $pwdBase   = VW::USR . '.password.update';
    $pwdKebab  = Str::kebab($pwdBase);
    $pwdName   = Route::has($pwdBase) ? $pwdBase : (Route::has($pwdKebab) ? $pwdKebab : null);
    $pwdUrl    = $pwdName ? route($pwdName) : '#';
    $pwdGuard  = Utility::fetchLinkMessage($lang, VW::USR, 'update_password_route_unavailable')
                 ?? 'Update password route is unavailable. Please contact technical support or your domain administrator.';
    $pwdFormId = 'profile-password-update-form';
@endphp

@extends(ExtendingLayoutsConstants::ADM)

@section(YieldingConstants::ADM_PG_TTL)
    {{ __('Profile Account') }}
@endsection

@push(StacksConstants::ADM_SCR_PG)
    <script async src="{{ asset('assets/js/routes/users/profiles/lang/scroll.js') }}"></script>
    <script async src="{{ asset('assets/js/routes/users/profiles/scroll.js') }}"></script>
    <script defer src="{{ asset('assets/js/routes/users/profiles/updateAccount.js') }}" id="profile-account-update-script"></script>
    <script defer src="{{ asset('assets/js/routes/users/profiles/updatePassword.js') }}" id="profile-password-update-script"></script>
@endpush

@section(YieldingConstants::ADM_BDC)
    <li class="breadcrumb-item">
        <a href="{{ $dashUrl }}" {{ $dashUrl === '#' ? 'aria-disabled=true' : '' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="breadcrumb-item">{{ __('Profile') }}</li>
@endsection

@section(YieldingConstants::ADM_CTT)
    <div class="{{ VC::RW }}">
        <div class="{{ VC::CXL3 }}">
            @php
                $sections = [
                    ['id' => 'personal_info',   'label' => __('Personal Info')],
                    ['id' => 'change_password', 'label' => __('Change Password')],
                ];
            @endphp
            <div class="{{ VC::CD_STK }}" style="top:30px">
                <div class="{{ VC::LG_FLSH }}" id="useradd-sidenav">
                    @foreach (($sections ?? []) as $section)
                        <a href="#{{ data_get($section, 'id', '') }}" class="{{ VC::LGI_ACT_NBD }}">
                            {{ data_get($section, 'label') ?: __('No section label available') }}
                            <div class="{{ VC::FEND }}">
                                <i class="{{ VC::TI_CHV_RT }}"></i>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="col-xl-9 {{ VC::CLMS9 }}">
            <div id="personal_info" class="{{ VC::CD }}">
                <div class="card-header">
                    <h5>{{ __('Personal Info') }}</h5>
                </div>
                <div class="card-body">
                    {!! Form::model(
                        $userDetail ?? null,
                        [
                            'url'                  => $accUrl,
                            'method'               => 'POST',
                            'enctype'              => 'multipart/form-data',
                            'id'                   => $accFormId,
                            'data-resolved-action' => $accUrl,
                            'data-guard-msg'       => $accGuard,
                            'data-sv-localized'    => 'true',
                        ]
                    ) !!}
                        @csrf
                        <div class="{{ VC::RW }}">
                            <div class="{{ VC::CLM6 }}">
                                <div class="form-group">
                                    <label class="col-form-label text-dark">{{ __('Name') }}</label>
                                    <input class="form-control"
                                           name="name"
                                           type="text"
                                           id="name"
                                           placeholder="{{ __('Enter Your Name') }}"
                                           value="{{ old('name', (string) data_get($userDetail, 'name', '')) }}"
                                           required
                                           autocomplete="name">
                                </div>
                            </div>
                            <div class="{{ VC::CLM6 }}">
                                <div class="form-group">
                                    <label for="email" class="col-form-label text-dark">{{ __('Email') }}</label>
                                    <input class="form-control"
                                           name="email"
                                           type="text"
                                           id="email"
                                           placeholder="{{ __('Enter Your Email Address') }}"
                                           value="{{ old('email', (string) data_get($userDetail, 'email', '')) }}"
                                           required
                                           autocomplete="email">
                                </div>
                            </div>
                            <div class="{{ VC::CLM6 }}">
                                <div class="form-group">
                                    <div class="choose-files">
                                        <label for="avatar">
                                            <div class="{{ VC::BG_P }} profile_update">
                                                <i class="ti ti-upload {{ VC::PX3 }}"></i>{{ __('Choose file here') }}
                                            </div>
                                            <input type="file"
                                                   class="form-control file"
                                                   name="profile"
                                                   id="avatar"
                                                   data-filename="profile_update">
                                        </label>
                                    </div>
                                    <span class="{{ VC::TXS }} {{ VC::TXT_MT }}">
                                        {{ __('Please upload a valid image file. Size of image should not be more than 2MB.') }}
                                    </span>
                                </div>
                            </div>
                            <div class="col-lg-12 text-end">
                                <input type="submit" value="{{ __('Save Changes') }}" class="{{ VC::BT_PR_PRM10 }}">
                            </div>
                        </div>
                    {!! Form::close() !!}
                </div>
            </div>

            <div id="change_password" class="{{ VC::CD }}">
                <div class="card-header">
                    <h5>{{ __('Change Password') }}</h5>
                </div>
                <div class="card-body">
                    {!! Form::open([
                        'url'                  => $pwdUrl,
                        'method'               => 'POST',
                        'id'                   => $pwdFormId,
                        'data-resolved-action' => $pwdUrl,
                        'data-guard-msg'       => $pwdGuard,
                        'data-sv-localized'    => 'true',
                    ]) !!}
                        @csrf
                        <div class="{{ VC::RW }}">
                            <div class="{{ VC::CLM6 }} form-group">
                                <label for="old_password" class="col-form-label text-dark">{{ __('Old Password') }}</label>
                                <input class="form-control"
                                       name="old_password"
                                       type="password"
                                       id="old_password"
                                       required
                                       autocomplete="current-password"
                                       placeholder="{{ __('Enter Old Password') }}">
                            </div>
                            <div class="{{ VC::CLM6 }} form-group">
                                <label for="password" class="col-form-label text-dark">{{ __('New Password') }}</label>
                                <input class="form-control"
                                       name="password"
                                       type="password"
                                       id="password"
                                       required
                                       autocomplete="new-password"
                                       placeholder="{{ __('Enter Your Password') }}">
                            </div>
                            <div class="{{ VC::CLM6 }} form-group">
                                <label for="password_confirmation" class="col-form-label text-dark">{{ __('New Confirm Password') }}</label>
                                <input class="form-control"
                                       name="password_confirmation"
                                       type="password"
                                       id="password_confirmation"
                                       required
                                       autocomplete="new-password"
                                       placeholder="{{ __('Enter Your Password') }}">
                            </div>
                            <div class="col-lg-12 text-end">
                                <input type="submit" value="{{ __('Change Password') }}" class="{{ VC::BT_PR_PRM10 }}">
                            </div>
                        </div>
                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    </div>
@endsection



                                    {{-- @error('name')
                                    <span class="invalid-feedback text-danger text-xs" role="alert">{{ $message }}</span>
                                    @enderror --}}
                                    {{-- @error('email')
                                    <span class="invalid-feedback text-danger text-xs" role="alert">{{ $message }}</span>
                                    @enderror --}}
                                    {{-- @error('avatar')
                                    <span class="invalid-feedback text-danger text-xs" role="alert">{{ $message }}</span>
                                    @enderror --}}
        
                                {{-- @error('old_password')
                                <span class="invalid-feedback text-danger text-xs" role="alert">{{ $message }}</span>
                                @enderror --}}
        {{-- @error('password')
        <span class="invalid-feedback text-danger text-xs" role="alert">{{ $message }}</span>
        @enderror --}}