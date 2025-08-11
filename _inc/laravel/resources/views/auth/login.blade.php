@php
	use App\Config\Constants\{DatabaseConstants,ExtendingLayoutsConstants,
        SettingsConstants,StacksConstants,ViewClassNamesConstants,
        YieldingConstants};
	use App\Models\Utility;
	use Illuminate\Support\{Facades\Log,Facades\Route,Str};
	use Symfony\Component\Console\Output\ConsoleOutput;
	$filePath??='';
	$data??=[];
	$setting??=[];
	$colorSettings??=[];
	$languages??=[DatabaseConstants::DEFAULT_LANG];
	$lang??=DatabaseConstants::DEFAULT_LANG;
	try {
		$filePath=collect(
			array_column(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS),'file')
		)->first(fn($p)=>str_ends_with($p,'.blade.php'))??'';
		Log::debug(
			"Rendering Login Blade ({$filePath})",
			[
				'route'=>request()?->getRequestUri()??'Undefined URI',
				'user'=>optional(auth()->user())->id??'Unidentified User'
			]
		);
		(new ConsoleOutput)
			->writeln(
				"Rendering Login Blade ({$filePath}) for "
				.(request()?->getRequestUri()??'Undefined URI')
			);
		$data=Utility::prepareCommonViewData()?:[];
		$setting=$data['settings']??[];
		$colorSettings=$data[SettingsConstants::CLR_STG]??[];
		$languages=Utility::languages()?:[DatabaseConstants::DEFAULT_LANG];
		$lang=$data[SettingsConstants::LCL]??DatabaseConstants::DEFAULT_LANG;
	} catch (\Error $e) {
		Log::error(
			'Error in Login Blade rendering',
			[
				'exception_class'=>get_class($e),
				'message'=>$e->getMessage(),
				'file'=>$e->getFile(),
				'line'=>$e->getLine(),
				'blade'=>$filePath,
				'route'=>request()?->getRequestUri()??'Undefined URI',
				'user'=>optional(auth()->user())->id??'Unidentified User'
			]
		);
	} catch (\Exception $e) {
		Log::error(
			'Exception in Login Blade rendering',
			[
				'exception_class'=>get_class($e),
				'message'=>$e->getMessage(),
				'file'=>$e->getFile(),
				'line'=>$e->getLine(),
				'blade'=>$filePath,
				'route'=>request()?->getRequestUri()??'Undefined URI',
				'user'=>optional(auth()->user())->id??'Unidentified User'
			]
		);
	} catch (\Throwable $e) {
		Log::error(
			'Throwable in Login Blade rendering',
			[
				'exception_class'=>get_class($e),
				'message'=>$e->getMessage(),
				'file'=>$e->getFile(),
				'line'=>$e->getLine(),
				'blade'=>$filePath,
				'route'=>request()?->getRequestUri()??'Undefined URI',
				'user'=>optional(auth()->user())->id??'Unidentified User'
			]
		);
	}
    $data = Utility::fallbackSettings($data);
@endphp
@extends(ExtendingLayoutsConstants::AUTH)
@push(StacksConstants::AUTH_CST_SCR)
@if (!empty($setting[SettingsConstants::RCPT_MDL]) && $setting[SettingsConstants::RCPT_MDL] == 'on')
        {!! Anhskohbo\NoCaptcha\Facades\NoCaptcha::renderJs() !!}
@endif
@endpush
@section(YieldingConstants::AUTH_PG_TTL)
    {{ __('Login') }}
@endsection
{{-- @section(YieldingConstants::AUTH_TB)
    <li class="nav-item">
        <select class="btn btn-primary ms-2 me-2 language_option_bg text-center" style="text-align-last: center;" onchange="this.options[this.selectedIndex].value && (window.location = this.options[this.selectedIndex].value);" id="language">
            @foreach (Utility::languages() as $code => $language)
                <option class="text-center" @if ($lang == $code) selected @endif value="{{ route('login',$code) }}">{{ucfirst($language)}}</option>
            @endforeach
        </select>
    </li>///
@endsection --}}
@section(YieldingConstants::AUTH_LG_BAR)
    <div class="{{ ViewClassNamesConstants::LNG_DD_DSK }}">
        <li class="{{ ViewClassNamesConstants::LNG_DD_IT }}">
            <a class="{{ ViewClassNamesConstants::DRP_BTN }}" href="#" data-bs-toggle="dropdown" aria-expanded="false">
                <span class="drp-text"> {{ $languages[$lang] }}
                </span>
            </a>
            <div class="{{ ViewClassNamesConstants::DRP_MN_DSH_END }}">
                @foreach($languages as $code => $language)
                    <a href="{{ route('login',$code) }}"tabindex="0" class="dropdown-item">
                        <span>{{ Str::upper($language) }}</span>
                    </a>
                @endforeach
            </div>
        </li>
    </div>
@endsection
@section(YieldingConstants::AUTH_CTT)
    <div class="card-body">
        <div>
            <h2 class="{{ ViewClassNamesConstants::MB3_FW600 }}">{{ __('Login') }}</h2>
        </div>
        {{ Collective\Html\FormFacade::open([
            'route'  => 'login.store',
            'method' => 'post',
            'id'     => 'loginForm',
            'class'  => 'login-form'
        ]) }}
            {{ csrf_field() }}
            <div class="custom-login-form">
                <div class="{{ ViewClassNamesConstants::FM_GB3 }}">
                    <label class="form-label" for="email-input">{{ __('Email') }}</label>
                    {{ Collective\Html\FormFacade::text('email', null, [
                        'class'       => 'form-control',
                        'placeholder' => __('Enter Your Email'),
                        'id'          => 'email-input',
                        'autocomplete' => 'email',
                        'required'    => 'required',
                        'autofocus'   => 'autofocus',
                        'title'      => __('Please enter a valid email address.'),
                        'aria-label' => __('Email Address'),
                    ]) }}
                    @error('email')
                        <span class="error invalid-email text-danger" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
                <div class="{{ ViewClassNamesConstants::FM_GB3 }}">
                    <label class="form-label" for="pw-input">{{ __('Password') }}</label>
                    <div class="input-group">
                        {{ Collective\Html\FormFacade::password('password', [
                            'class'       => 'form-control',
                            'placeholder' => __('Enter Your Password'),
                            'id'          => 'pw-input',
                            'autocomplete' => 'current-password',
                            'required'    => 'required',
                            'autofocus'   => 'autofocus',
                            'title'      => __('Password must be at least 8 characters long and contain a mix of letters, numbers, and special characters.')
                        ]) }}
                        <button class="btn btn-outline-secondary" type="button" id="togglePassword" aria-label="{{ __('Toggle password visibility') }}">
                            <svg id="toggleIcon" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                <path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8zM1.173 8a13.133 13.133 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5c2.12 0 3.879 1.168 5.168 2.457A13.133 13.133 0 0 1 14.828 8c-.058.087-.122.183-.195.288-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5c-2.12 0-3.879-1.168-5.168-2.457A13.134 13.134 0 0 1 1.172 8z"/>
                                <path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5zM4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0z"/>
                            </svg>
                        </button>
                    </div>
                    @error('password')
                        <span class="error invalid-password text-danger" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
                <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const togglePassword = document.getElementById('togglePassword'), 
                    passwordInput = document.getElementById('pw-input'), 
                    toggleIcon = document.getElementById('toggleIcon');
                    togglePassword.addEventListener('click', function() {
                        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                        passwordInput.setAttribute('type', type);
                        if (type === 'password') {
                            toggleIcon.innerHTML = '<path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8zM1.173 8a13.133 13.133 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5c2.12 0 3.879 1.168 5.168 2.457A13.133 13.133 0 0 1 14.828 8c-.058.087-.122.183-.195.288-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5c-2.12 0-3.879-1.168-5.168-2.457A13.134 13.134 0 0 1 1.172 8z"/><path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5zM4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0z"/>';
                        } else {
                            toggleIcon.innerHTML = '<path d="M13.359 11.238C15.06 9.72 16 8 16 8s-3-5.5-8-5.5a7.028 7.028 0 0 0-2.79.588l.77.771A5.944 5.944 0 0 1 8 3.5c2.12 0 3.879 1.168 5.168 2.457A13.134 13.134 0 0 1 14.828 8c-.058.087-.122.183-.195.288-.335.48-.83 1.12-1.465 1.755-.165.165-.337.328-.517.486l.708.709z"/><path d="M11.297 9.176a3.5 3.5 0 0 0-4.474-4.474l.823.823a2.5 2.5 0 0 1 2.829 2.829l.822.822zm-2.943 1.299.822.822a3.5 3.5 0 0 1-4.474-4.474l.823.823a2.5 2.5 0 0 0 2.829 2.829z"/><path d="M3.35 5.47c-.18.16-.353.322-.518.487A13.134 13.134 0 0 0 1.172 8l.195.288c.335.48.83 1.12 1.465 1.755C4.121 11.332 5.881 12.5 8 12.5c.716 0 1.39-.133 2.02-.36l.77.772A7.029 7.029 0 0 1 8 13.5C3 13.5 0 8 0 8s.939-1.721 2.641-3.238l.708.709zm10.296 8.884-12-12 .708-.708 12 12-.708.708z"/>';
                        }
                    });
                });
                </script>
                <style>
                    .input-group .btn-outline-secondary {
                        border-left: 0;
                    }
                    
                    .input-group .form-control:focus {
                        border-color: #86b7fe;
                        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
                    }

                    #togglePassword {
                        cursor: pointer;
                        border: 1px solid #ced4da;
                    }
                </style>

                <div class="form-group mb-4">
                    <div class="d-flex flex-wrap align-items-center justify-content-between">
                        @if (Route::has('password.request'))
                            <span>
                                <a href="{{ route('password.request') }}" tabindex="0">
                                    {{ __('Forgot your password?') }}
                                </a>
                            </span>
                        @endif
                    </div>
                </div>
        
                <div class="d-grid">
                    {{ Collective\Html\FormFacade::submit(__('Login'), [
                        'class' => 'btn btn-primary mt-2',
                        'id'    => 'saveBtn'
                    ]) }}
                </div>
        
                @if (!empty($setting[SettingsConstants::ENB_SGU]) && $setting[SettingsConstants::ENB_SGU] == 'on')
                    <p class="my-4 text-center">
                        {{ __("Don't have an account?") }}
                        <a href="{{ url('register') }}" tabindex="0">{{ __('Register') }}</a>
                    </p>
                @endif
        
                @if (!empty($setting[SettingsConstants::RCPT_MDL]) && $setting[SettingsConstants::RCPT_MDL] == 'on')
                    <div class="form-group col-lg-12 col-md-12 mt-3">
                        {!! Anhskohbo\NoCaptcha\Facades\NoCaptcha::display(
                            $colorSettings[SettingsConstants::CST_DRK] == 'on'
                                ? ['data-theme' => 'dark']
                                : []
                        ) !!}
                        @error(SettingsConstants::G_RCPT_RES)
                            <span class="small text-danger" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                @endif
            </div>
        {{ Collective\Html\FormFacade::close() }}
    </div>
@endsection
{{-- @section(YieldingConstants::AUTH_CTT)

    <div class="">
        <h2 class="{{ ViewClassNamesConstants::MB3_FW600 }}>{{__('Login')}}"</h2>
    </div>
    {{Collective\Html\FormFacade::open(array('route'=>'login','method'=>'post','id'=>'loginForm' ))}}
    @csrf
    <div class="">
        <div class="{{ ViewClassNamesConstants::FM_GB3 }}">
            <label for="email" class="form-label">{{__('Email')}}</label>
            <input class="form-control @error('email') is-invalid @enderror" id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus>
            @error('email')
            <div class="invalid-feedback" role="alert">{{ $message }}</div>
            @enderror
        </div>
        <div class="{{ ViewClassNamesConstants::FM_GB3 }}">
            <label for="password" class="form-label">{{__('Password')}}</label>
            <input class="form-control @error('password') is-invalid @enderror" id="password" type="password" name="password" required autocomplete="current-password">
            @error('password')
            <div class="invalid-feedback" role="alert">{{ $message }}</div>
            @enderror

        </div>

        @if (env(SettingsConstants::RCPT_MDL) == 'on')
            <div class="{{ ViewClassNamesConstants::FM_GB3 }}">
                {!! Anhskohbo\NoCaptcha\Facades\NoCaptcha::display() !!}
                @error(SettingsConstants::G_RCPT_RES)
                <span class="small text-danger" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                @enderror
            </div>
        @endif
        <div class="form-group mb-4">
            @if (Illuminate\Support\Facades\Route::has('password.request'))
                <a href="{{ route('password.request') }}" class="text-xs">{{ __('Forgot Your Password?') }}</a>
            @endif

        </div>
        <div class="d-grid">
            <button type="submit" class="btn-login btn btn-primary btn-block mt-2" id="login_button">{{__('Login')}}</button>
        </div>
        @if (!empty(SettingsConstants::ENB_SGU) && $data[SettingsConstants::ENB_SGU] == 'on')

        <p class="my-4 text-center">{{__("Don't have an account?")}} <a href="{{ route('register',$lang) }}" class="text-primary">{{__('Register')}}</a></p>
        @endif

    </div>
    {{Collective\Html\FormFacade::close()}}
@endsection --}}
<script src="{{ asset('js/jquery.min.js') }}"></script>
<script>
    $(document).ready(function() {
        $("#form_data").submit(function(e) {
            $("#login_button").attr("disabled", true);
            return true;
        });
    });
</script>
