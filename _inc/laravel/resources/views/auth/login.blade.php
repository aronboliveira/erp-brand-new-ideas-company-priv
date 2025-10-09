@php
	use App\Config\Constants\{DatabaseConstants,ExtendingLayoutsConstants,
        SettingsConstants as SC,StacksConstants,ViewClassNamesConstants as VC,
        YieldingConstants};
	use App\Models\Utility;
    use Collective\Html\FormFacade as Form;
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
		$colorSettings=$data[SC::CLR_STG]??[];
		$languages=Utility::languages()?:[DatabaseConstants::DEFAULT_LANG];
		$lang= Utility::fetchUserLang() ?? $data[SC::LCL] ?? DatabaseConstants::DEFAULT_LANG;
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
@if (!empty($setting[SC::RCPT_MDL]) && $setting[SC::RCPT_MDL] == 'on')
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
    <div class="{{ VC::LNG_DD_DSK }}">
        <li class="{{ VC::LNG_DD_IT }}">
            <a class="{{ VC::DRP_BTN }}" href="#" data-bs-toggle="dropdown" aria-expanded="false">
                <span class="drp-text"> {{ !empty($languages) && !empty($languages[$lang]) ? $languages[$lang] : __(DatabaseConstants::DEFAULT_LANG) }}
                </span>
            </a>
            <div class="{{ VC::DRP_MN_DSH_END }}">
                @if(!empty($languages) && ((is_array($languages) && count($languages)) || ($languages instanceof Collection && $languages->isNotEmpty())))
                    @foreach($languages as $code => $language)
                        @php
                            $loginBase = 'login';
                            $loginKebab = Str::kebab($loginBase);
                            $loginResolved = Route::has($loginBase) ? $loginBase : (Route::has($loginKebab) ? $loginKebab : null);
                            $loginUrl = $loginResolved ? route($loginResolved, $code) : '#';
                            $langValue = isset($lang) ? $lang : Utility::fetchUserLang();
                            $loginGuardMsg = Utility::fetchLinkMessage($langValue, 'auth', 'login_lang_route_unavailable') ?? 'Login language route is unavailable. Please contact technical support or your domain administrator.';
                            $anchorId = 'login-lang-'.Str::slug((string)$code,'-');
                            $label = Str::upper($language);
                        @endphp
                        <a id="{{ $anchorId }}"
                        href="{{ $loginUrl }}"
                        tabindex="0"
                        class="dropdown-item"
                        data-url="{{ $loginUrl }}"
                        data-guard-msg="{{ $loginGuardMsg }}"
                        data-sv-localized="true">
                            <span>{{ $label }}</span>
                        </a>
                        @push(StacksConstants::AUTH_CST_SCR)
                            <script defer>
                                (() => {
                                    try {
                                        const el = document.getElementById('{{ $anchorId }}');
                                        if (!el) { return; }
                                        if (el.getAttribute('data-listener-active') === 'true') { return; }
                                        el.setAttribute('data-listener-active','true');
                                        el.addEventListener('click',(e) => {
                                            try {
                                                const href = el.getAttribute('href') ?? '#';
                                                const url = el.getAttribute('data-url') ?? href ?? '#';
                                                if (url !== '#' && href !== '#') { return; }
                                                e.preventDefault();
                                                const msg = el.getAttribute('data-guard-msg') ?? 'Login route is unavailable. Please contact technical support or your domain administrator.';
                                                const hasBootstrap = !!(document.querySelector('link[href*="bootstrap"]') && window.bootstrap);
                                                let container = document.getElementById('toast-container');
                                                if (!container) {
                                                    container = document.createElement('div');
                                                    container.id = 'toast-container';
                                                    document.body.appendChild(container);
                                                }
                                                if (hasBootstrap) {
                                                    const toast = document.createElement('div');
                                                    toast.className = 'toast';
                                                    toast.setAttribute('role','alert');
                                                    toast.setAttribute('aria-live','assertive');
                                                    toast.setAttribute('aria-atomic','true');
                                                    const body = document.createElement('div');
                                                    body.className = 'toast-body';
                                                    body.textContent = msg;
                                                    toast.appendChild(body);
                                                    container.appendChild(toast);
                                                    bootstrap.Toast.getOrCreateInstance(toast).show();
                                                } else {
                                                    alert(msg);
                                                }
                                                el.setAttribute('data-failed-route','true');
                                            } catch (err) {}
                                        });
                                    } catch (err) {}
                                })();
                            </script>
                        @endpush
                    @endforeach
                @else
                    <span class="drp-text"> {{ __(DatabaseConstants::DEFAULT_LANG) }}</span>
                @endif
            </div>
        </li>
    </div>
@endsection
@section(YieldingConstants::AUTH_CTT)
    <div class="card-body">
        <div>
            <h2 class="{{ VC::MB3_FW600 }}">{{ __('Login') }}</h2>
        </div>
        @php
            $loginStoreBase = 'login.store';
            $loginStoreKebab = Str::kebab($loginStoreBase);
            $loginStoreResolved = Route::has($loginStoreBase) ? $loginStoreBase : (Route::has($loginStoreKebab) ? $loginStoreKebab : null);
            $loginStoreUrl = $loginStoreResolved ? route($loginStoreResolved) : '#';
            $langValue = isset($lang) ? $lang : Utility::fetchUserLang();
            $loginStoreGuardMsg = Utility::fetchLinkMessage($langValue, 'auth', 'store_login_auth_route_unavailable') ?? 'Login submit route is unavailable. Please contact technical support or your domain administrator.';
            $pwdReqBase = 'password.request';
            $pwdReqKebab = Str::kebab($pwdReqBase);
            $pwdReqResolved = Route::has($pwdReqBase) ? $pwdReqBase : (Route::has($pwdReqKebab) ? $pwdReqKebab : null);
            $pwdReqUrl = $pwdReqResolved ? route($pwdReqResolved) : '#';
            $pwdReqGuardMsg = Utility::fetchLinkMessage($langValue, 'auth', 'password_request_auth_route_unavailable') ?? 'Password request route is unavailable. Please contact technical support or your domain administrator.';
            $pwdReqAnchorId = 'password-request-link';
            $registerBase = 'register';
            $registerKebab = Str::kebab($registerBase);
            $registerResolved = Route::has($registerBase) ? $registerBase : (Route::has($registerKebab) ? $registerKebab : null);
            $registerUrl = $registerResolved ? route($registerResolved) : (url('register') ?: '#');
            $registerGuardMsg = Utility::fetchLinkMessage($langValue, 'auth', 'register_auth_route_unavailable') ?? 'Register route is unavailable. Please contact technical support or your domain administrator.';
            $registerAnchorId = 'register-link';
        @endphp
        {{ Form::open([
            'method' => 'post',
            'url' => $loginStoreUrl,
            'id' => 'loginForm',
            'class' => 'login-form',
            'data-url' => $loginStoreUrl,
            'data-guard-msg' => $loginStoreGuardMsg,
            'data-sv-localized' => 'true',
        ]) }}
            {{ csrf_field() }}
            <div class="custom-login-form">
                <div class="{{ VC::FM_GB3 }}">
                    <label class="form-label" for="email-input">{{ __('Email') }}</label>
                    {{ Form::text('email', null, [
                        'class' => 'form-control',
                        'placeholder' => __('Enter Your Email'),
                        'id' => 'email-input',
                        'autocomplete' => 'email',
                        'required' => 'required',
                        'autofocus' => 'autofocus',
                        'title' => __('Please enter a valid email address.'),
                        'aria-label' => __('Email Address'),
                    ]) }}
                    @error('email')
                        <span class="error invalid-email text-danger" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
                <div class="{{ VC::FM_GB3 }}">
                    <label class="form-label" for="pw-input">{{ __('Password') }}</label>
                    <div class="input-group">
                        {{ Form::password('password', [
                            'class' => 'form-control',
                            'placeholder' => __('Enter Your Password'),
                            'id' => 'pw-input',
                            'autocomplete' => 'current-password',
                            'required' => 'required',
                            'autofocus' => 'autofocus',
                            'title' => __('Password must be at least 8 characters long and contain a mix of letters, numbers, and special characters.'),
                        ]) }}
                        <button class="btn btn-outline-secondary" type="button" id="togglePassword" aria-label="{{ __('Toggle password visibility') }}">
                            <svg id="toggleIcon" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                <path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8zM1.173 8a13.133 13.133 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5c2.12 0 3.879 1.168 5.168 2.457A13.133 13.133 0 0 1 14.828 8c-.058.087-.122.183-.195.288-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5c-2.12 0-3.879-1.168-5.168-2.457A13.134 13.134 0 0 1 1.172 8z"></path>
                                <path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5zM4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0z"></path>
                            </svg>
                        </button>
                    </div>
                    @error('password')
                        <span class="error invalid-password text-danger" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
                <script defer src="{{ asset('assets/js/routes/auth/login/toggle.js') }}"></script>
                <link rel="stylesheet" href="{{ asset('assets/css/routes/auth/login/toggle.css') }}"></link>
                <div class="form-group mb-4">
                    <div class="d-flex flex-wrap align-items-center justify-content-between">
                        <span>
                            <a id="{{ $pwdReqAnchorId }}"
                            href="{{ $pwdReqUrl }}"
                            tabindex="0"
                            data-url="{{ $pwdReqUrl }}"
                            data-guard-msg="{{ $pwdReqGuardMsg }}"
                            data-sv-localized="true">
                                {{ __('Forgot your password?') }}
                            </a>
                        </span>
                    </div>
                </div>
                <div class="d-grid">
                    {{ Form::submit(__('Login'), [
                        'class' => 'btn btn-primary mt-2',
                        'id' => 'saveBtn',
                    ]) }}
                </div>
                @if (!empty($setting[SC::ENB_SGU]) && $setting[SC::ENB_SGU] == 'on')
                    <p class="my-4 text-center">
                        {{ __("Don't have an account?") }}
                        <a id="{{ $registerAnchorId }}"
                        href="{{ $registerUrl }}"
                        tabindex="0"
                        data-url="{{ $registerUrl }}"
                        data-guard-msg="{{ $registerGuardMsg }}"
                        data-sv-localized="true">{{ __('Register') }}</a>
                    </p>
                @endif
                @if (!empty($setting[SC::RCPT_MDL]) && $setting[SC::RCPT_MDL] == 'on')
                    <div class="form-group col-lg-12 col-md-12 mt-3">
                        {!! class_exists(Anhskohbo\NoCaptcha\Facades\NoCaptcha::class) && Anhskohbo\NoCaptcha\Facades\NoCaptcha::display(
                            $colorSettings[SC::CST_DRK] == 'on'
                                ? ['data-theme' => 'dark']
                                : []
                        ) !!}
                        @error(SC::G_RCPT_RES)
                            <span class="small text-danger" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                @endif
            </div>
        {{ Form::close() }}
        @push(StacksConstants::AUTH_CST_SCR)
            <script defer src={{ asset('assets/js/routes/auth/login/store.js') }}></script>
        @endpush
    </div>
@endsection
{{-- @section(YieldingConstants::AUTH_CTT)

    <div class="">
        <h2 class="{{ VC::MB3_FW600 }}>{{__('Login')}}"</h2>
    </div>
    {{Form::open(array('route'=>'login','method'=>'post','id'=>'loginForm' ))}}
    @csrf
    <div class="">
        <div class="{{ VC::FM_GB3 }}">
            <label for="email" class="form-label">{{__('Email')}}</label>
            <input class="form-control @error('email') is-invalid @enderror" id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus>
            @error('email')
            <div class="invalid-feedback" role="alert">{{ $message }}</div>
            @enderror
        </div>
        <div class="{{ VC::FM_GB3 }}">
            <label for="password" class="form-label">{{__('Password')}}</label>
            <input class="form-control @error('password') is-invalid @enderror" id="password" type="password" name="password" required autocomplete="current-password">
            @error('password')
            <div class="invalid-feedback" role="alert">{{ $message }}</div>
            @enderror

        </div>

        @if (env(SC::RCPT_MDL) == 'on')
            <div class="{{ VC::FM_GB3 }}">
                {!! Anhskohbo\NoCaptcha\Facades\NoCaptcha::display() !!}
                @error(SC::G_RCPT_RES)
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
        @if (!empty(SC::ENB_SGU) && $data[SC::ENB_SGU] == 'on')

        <p class="my-4 text-center">{{__("Don't have an account?")}} <a href="{{ route('register',$lang) }}" class="text-primary">{{__('Register')}}</a></p>
        @endif

    </div>
    {{Form::close()}}
@endsection --}}
@push(StacksConstants::AUTH_CST_SCR)
    <script src="{{ asset('js/jquery.min.js') }}"></script>
    <script async src="{{ asset('assets/js/routes/auth/login/lang/submit.js') }}"></script>
    <script async src="{{ asset('assets/js/routes/auth/login/submit.js') }}"></script>
@endpush