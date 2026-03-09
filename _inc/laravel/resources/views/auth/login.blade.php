@php
$filePath??='';
	$data??=[];
	$setting??=[];
	$colorSettings??=[];
	$languages??=[DatabaseConstants::DEFAULT_LANG];
	// Preserve controller-passed $lang if it exists and is valid
	$controllerLang = $lang ?? null;
	$lang??=DatabaseConstants::DEFAULT_LANG;
	try {
		$filePath=collect(
			array_column(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS),'file')
		)->first(fn($p)=>str_ends_with($p,'.blade.php'))??'';
		$data=Utility::prepareCommonViewData()?:[];
		$setting=$data['settings']??[];
		$colorSettings=$data[SC::CLR_STG]??[];
		$languages=Utility::languages()?:[DatabaseConstants::DEFAULT_LANG];
		// Prioritize controller-passed lang, then route param, then session/locale defaults
		$lang = $controllerLang ?? request()->route('lang') ?? $data[SC::LCL] ?? Utility::fetchUserLang() ?? app()->getLocale() ?? DatabaseConstants::DEFAULT_LANG;
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
    <script defer src="{{ asset('assets/js/core/route-guard.js') }}"></script>
@if (!empty($setting[SC::RCPT_MDL]) && $setting[SC::RCPT_MDL] == 'on')
        {!! Anhskohbo\NoCaptcha\Facades\NoCaptcha::renderJs() !!}
@endif
@endpush
@section(YieldingConstants::AUTH_PG_TTL)
    {{ __('Login') }}
@endsection
{{-- @section(YieldingConstants::AUTH_TB)
    <li class="{{ VC::NV_IT }}">
        <select class="{{ VC::BT_PRM }} {{ VC::MS2 }} me-2 language_option_bg {{ VC::TXCT }}" style="text-align-last: center;" onchange="this.options[this.selectedIndex].value && (window.location = this.options[this.selectedIndex].value);" id="language">
            @foreach (Utility::languages() as $code => $language)
                <option class="{{ VC::TXCT }}" @if ($lang == $code) selected @endif value="{{ route('login',$code) }}">{{ucfirst($language)}}</option>
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
                            $loginBase ??= 'login';
                            try {
                                $loginKebab = Str::kebab($loginBase);
                                $loginResolved = Route::has($loginBase) ? $loginBase : (Route::has($loginKebab) ? $loginKebab : null);
                                $loginUrl = $loginResolved ? route($loginResolved, $code) : '#';
                                $langValue = isset($lang) ? $lang : Utility::fetchUserLang();
                                $loginGuardMsg = Utility::fetchLinkMessage($langValue, 'auth', 'login_lang_route_unavailable') ?? 'Login language route is unavailable. Please contact technical support or your domain administrator.';
                                $anchorId = 'login-lang-'.Str::slug((string)$code,'-');
                                $label = Str::upper($language);
                            } catch (\Throwable $e) {
                                \Log::error('auth/login — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                            }
@endphp
                        <a id="{{ $anchorId }}"
                        href="{{ $loginUrl }}"
                        tabindex="0"
                        class="{{ VC::DRP_IT }}"
                        data-url="{{ $loginUrl }}"
                        data-guard-msg="{{ base64_encode($loginGuardMsg) }}"
                        data-sv-localized="true"
                        data-lang-code="{{ $code }}">
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
                                                (window.RouteGuard?.showToast || (m => alert(m)))(msg);
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
    @push(StacksConstants::AUTH_CST_SCR)
        <script>
            (() => {
                // Store the current language in localStorage + cookie for client-side state tracking
                const currentLang = '{{ $lang }}';
                if (currentLang) {
                    try {
                        localStorage.setItem('locale', currentLang);
                        localStorage.setItem('erp-np-lang', currentLang);
                        document.cookie = 'erp_locale=' + encodeURIComponent(currentLang) + ';path=/;max-age=31536000;SameSite=Lax';
                    } catch (e) {}
                }
                // Update drp-text with current language name
                document.addEventListener('DOMContentLoaded', () => {
                    const drpText = document.querySelector('.drp-text');
                    const currentLangName = '{{ !empty($languages[$lang]) ? $languages[$lang] : __(DatabaseConstants::DEFAULT_LANG) }}';
                    if (drpText && currentLangName) {
                        drpText.textContent = ' ' + currentLangName + ' ';
                    }
                    // When clicking a language link, save the lang code to localStorage + cookie
                    document.querySelectorAll('[data-lang-code]').forEach(link => {
                        link.addEventListener('click', () => {
                            try {
                                const code = link.getAttribute('data-lang-code');
                                if (code) {
                                    localStorage.setItem('locale', code);
                                    localStorage.setItem('erp-np-lang', code);
                                    document.cookie = 'erp_locale=' + encodeURIComponent(code) + ';path=/;max-age=31536000;SameSite=Lax';
                                }
                            } catch (e) {}
                        });
                    });
                });
            })();
        </script>
    @endpush
@endsection
@section(YieldingConstants::AUTH_CTT)
    <div class="{{ VC::CD_BD }}">
        <div>
            <h2 class="{{ VC::MB3_FW600 }}">{{ __('Login') }}</h2>
        </div>
        @php
            $loginStoreBase ??= 'login.store';
            try {
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
            } catch (\Throwable $e) {
                \Log::error('auth/login — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
            }
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
                    <label class="{{ VC::FM_LB }}" for="email-input">{{ __('Email') }}</label>
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
                        <span class="error invalid-email {{ VC::TX_DNG }}" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
                <div class="{{ VC::FM_GB3 }}">
                    <label class="{{ VC::FM_LB }}" for="pw-input">{{ __('Password') }}</label>
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
                        <button class="{{ VC::BT_OUT_SEC }}" type="button" id="togglePassword" aria-label="{{ __('Toggle password visibility') }}">
                            <svg id="toggleIcon" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                <path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8zM1.173 8a13.133 13.133 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5c2.12 0 3.879 1.168 5.168 2.457A13.133 13.133 0 0 1 14.828 8c-.058.087-.122.183-.195.288-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5c-2.12 0-3.879-1.168-5.168-2.457A13.134 13.134 0 0 1 1.172 8z"></path>
                                <path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5zM4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0z"></path>
                            </svg>
                        </button>
                    </div>
                    @error('password')
                        <span class="error invalid-password {{ VC::TX_DNG }}" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
                @push(StacksConstants::AUTH_CST_SCR)
                    <script src="{{ asset('assets/js/routes/auth/login/toggle.js') }}"></script>
                @endpush
                <link rel="stylesheet" href="{{ asset('assets/css/routes/auth/login/toggle.css') }}">
                <div class="{{ VC::FM_GB4 }}">
                    <div class="{{ VC::DFL }} flex-wrap {{ VC::ALC }} {{ VC::JCB }}">
                        <span>
                            <a id="{{ $pwdReqAnchorId }}"
                            href="{{ $pwdReqUrl }}"
                            tabindex="0"
                            data-url="{{ $pwdReqUrl }}"
                            data-guard-msg="{{ base64_encode($pwdReqGuardMsg) }}"
                            data-sv-localized="true">
                                {{ __('Forgot your password?') }}
                            </a>
                        </span>
                    </div>
                </div>
                <div class="{{ VC::D_GR }}">
                    {{ Form::submit(__('Login'), [
                        'class' => 'btn btn-primary mt-2',
                        'id' => 'saveBtn',
                    ]) }}
                </div>
                @if (!empty($setting[SC::ENB_SGU]) && $setting[SC::ENB_SGU] == 'on')
                    <p class="{{ VC::MY4_TXCT }}">
                        {{ __("Don't have an account?") }}
                        <a id="{{ $registerAnchorId }}"
                        href="{{ $registerUrl }}"
                        tabindex="0"
                        data-url="{{ $registerUrl }}"
                        data-guard-msg="{{ base64_encode($registerGuardMsg) }}"
                        data-sv-localized="true">{{ __('Register') }}</a>
                    </p>
                @endif
                @if (!empty($setting[SC::RCPT_MDL]) && $setting[SC::RCPT_MDL] == 'on')
                    <div class="{{ VC::FM_G }} {{ VC::CL12 }} {{ VC::CM12 }} {{ VC::MT3 }}">
                        {!! class_exists(Anhskohbo\NoCaptcha\Facades\NoCaptcha::class) && Anhskohbo\NoCaptcha\Facades\NoCaptcha::display(
                            $colorSettings[SC::CST_DRK] == 'on'
                                ? ['data-theme' => 'dark']
                                : []
                        ) !!}
                        @error(SC::G_RCPT_RES)
                            <span class="{{ VC::SM_TX_DNG }}" role="alert">
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
            <label for="email" class="{{ VC::FM_LB }}">{{__('Email')}}</label>
            <input class="{{ VC::FM_CT }} @error('email') is-invalid @enderror" id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus>
            @error('email')
            <div class="{{ VC::INV_FB }}" role="alert">{{ $message }}</div>
            @enderror
        </div>
        <div class="{{ VC::FM_GB3 }}">
            <label for="password" class="{{ VC::FM_LB }}">{{__('Password')}}</label>
            <input class="{{ VC::FM_CT }} @error('password') is-invalid @enderror" id="password" type="password" name="password" required autocomplete="current-password">
            @error('password')
            <div class="{{ VC::INV_FB }}" role="alert">{{ $message }}</div>
            @enderror

        </div>

        @if (env(SC::RCPT_MDL) == 'on')
            <div class="{{ VC::FM_GB3 }}">
                {!! Anhskohbo\NoCaptcha\Facades\NoCaptcha::display() !!}
                @error(SC::G_RCPT_RES)
                <span class="{{ VC::SM_TX_DNG }}" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                @enderror
            </div>
        @endif
        <div class="{{ VC::FM_GB4 }}">
            @if (Illuminate\Support\Facades\Route::has('password.request'))
                <a href="{{ route('password.request') }}" class="{{ VC::TXS }}">{{ __('Forgot Your Password?') }}</a>
            @endif

        </div>
        <div class="{{ VC::D_GR }}">
            <button type="submit" class="btn-login {{ VC::BT_PRM_BLK_MT2 }}" id="login_button">{{__('Login')}}</button>
        </div>
        @if (!empty(SC::ENB_SGU) && $data[SC::ENB_SGU] == 'on')

        <p class="{{ VC::MY4_TXCT }}">{{__("Don't have an account?")}} <a href="{{ route('register',$lang) }}" class="{{ VC::TX_PM }}">{{__('Register')}}</a></p>
        @endif

    </div>
    {{Form::close()}}
@endsection --}}
@push(StacksConstants::AUTH_CST_SCR)
    <script src="{{ asset('js/jquery.min.js') }}"></script>
    <script async src="{{ asset('assets/js/routes/auth/login/lang/submit.js') }}"></script>
    <script async src="{{ asset('assets/js/routes/auth/login/submit.js') }}"></script>
@endpush
