@php
$settings??=[];
	$colorSettings??=[];
    $lang = Utility::fetchUserLang();
	$languages??=[$lang];
	$filePath??='';
	try {
        $user=auth()->user()?:null;
		$settings=Utility::settings()?:[];
		$colorSettings=$settings[SettingsConstants::CLR_STG]??[];
		$langResult=Utility::languages();
		$languages=($langResult instanceof \Illuminate\Support\Collection)?$langResult->all():(is_array($langResult)?$langResult:[DatabaseConstants::DEFAULT_LANG => __(DatabaseConstants::DEFAULT_LANG)]);
		if (!array_key_exists($lang, $languages)) $lang = array_key_first($languages) ?? DatabaseConstants::DEFAULT_LANG;
        $lang = isset($user[UsersConstants::COL_LG])?$user[UsersConstants::COL_LG]:DatabaseConstants::DEFAULT_LANG;
        if (empty($lang)) $lang = DatabaseConstants::DEFAULT_LANG;
		$filePath=collect(
			array_column(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS),'file')
		)->first(fn($p)=>str_ends_with($p,'.blade.php'))??'';
	} catch (\Error $e) {
		Log::error(
			'Error fetching data for Forgot Password Blade',
			[
				'exception_class'=>get_class($e),
				'message'=>$e->getMessage(),
				'file'=>$e->getFile(),
				'line'=>$e->getLine(),
				'route'=>request()?->getRequestUri()??'Undefined URI',
				'user'=>optional(auth()->user())->id??'Unidentified User'
			]
		);
	} catch (\Exception $e) {
		Log::error(
			'Exception fetching data for Forgot Password Blade',
			[
				'exception_class'=>get_class($e),
				'message'=>$e->getMessage(),
				'file'=>$e->getFile(),
				'line'=>$e->getLine(),
				'route'=>request()?->getRequestUri()??'Undefined URI',
				'user'=>optional(auth()->user())->id??'Unidentified User'
			]
		);
	} catch (\Throwable $e) {
		Log::error(
			'Throwable fetching data for Forgot Password Blade',
			[
				'exception_class'=>get_class($e),
				'message'=>$e->getMessage(),
				'file'=>$e->getFile(),
				'line'=>$e->getLine(),
				'route'=>request()?->getRequestUri()??'Undefined URI',
				'user'=>optional(auth()->user())->id??'Unidentified User'
			]
		);
	}
@endphp
@extends(ExtendingLayoutsConstants::AUTH)
@section(YieldingConstants::AUTH_PG_TTL)
    {{ __('Reset Password') }}
@endsection
@push(StacksConstants::AUTH_CST_SCR)
    <script defer src="{{ asset('assets/js/core/route-guard.js') }}"></script>
@if (!empty($settings[SettingsConstants::RCPT_MDL]) && $settings[SettingsConstants::RCPT_MDL] == 'on')
    {!! Anhskohbo\NoCaptcha\Facades\NoCaptcha::renderJs() !!}
@endif
@endpush
@section(YieldingConstants::AUTH_LG_BAR)
    <div class="{{ VC::LNG_DD_DSK }}">
        <li class="{{ VC::LNG_DD_IT }}">
            <a class="{{ VC::DRP_BTN }}" href="#" data-bs-toggle="dropdown" aria-expanded="false">
                <span class="drp-text"> {{ !empty($languages) && !empty($languages[$lang]) ? $languages[$lang] : __(DatabaseConstants::DEFAULT_LANG) }}
                </span>
            </a>
            <div class="{{ VC::DRP_MN_DSH_END }}">
                @foreach($languages as $code => $language)
                    @php
                        try {
                            $passwordRequestRoute         = Route::has('password.request')
                                ? route('password.request', $code)
                                : (Route::has(Str::kebab('password.request'))
                                    ? route(Str::kebab('password.request'), $code)
                                    : '#');
                            $passwordRequestLinkId        = 'password-request-link-' . $code;
                            $passwordRequestRouteMsg      = Utility::fetchLinkMessage(
                                $lang,
                                ViewsConstants::AUT,
                                'password_request_route_unavailable'
                            ) ?? 'Password request route is unavailable. Please contact technical support or your domain administrator.';
                        } catch (\Throwable $e) {
                            \Log::error('auth/forgot_password — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                        }
@endphp
                    <a
                        id="{{ $passwordRequestLinkId }}"
                        href="{{ $passwordRequestRoute }}"
                        tabindex="0"
                        class="{{ VC::DRP_IT }}"
                        data-url="{{ $passwordRequestRoute }}"
                        data-guard-msg="{{ base64_encode($passwordRequestRouteMsg) }}"
                    >
                        <span>{{ Str::ucfirst($language) }}</span>
                    </a>
                    @push(StacksConstants::AUTH_CST_SCR)
                        <script defer>
                            (() => {
                                const el = document.getElementById('{{ $passwordRequestLinkId }}');
                                if (!el || el.getAttribute('data-listener-active') === 'true') return;
                                el.setAttribute('data-listener-active', 'true');
                                el.addEventListener('click', event => {
                                    try {
                                        const href = el.getAttribute('href');
                                        const url  = el.getAttribute('data-url');
                                        if ((href && href !== '#') || (url && url !== '#')) return;
                                        event.preventDefault();
                                        const msg           = el.getAttribute('data-guard-msg') ?? '# ERROR';
                                        (window.RouteGuard?.showToast || (m => alert(m)))(msg);
                                        el.setAttribute('data-failed-route', 'true');
                                    } catch (e) {}
                                });
                            })();
                        </script>
                    @endpush
                @endforeach
            </div>
        </li>
    </div>
@endsection
@section(YieldingConstants::AUTH_CTT)
    <div class="{{ VC::CD_BD }}">
        <div>
            <h2 class="{{ VC::MB3_FW600 }}"><span class="{{ VC::TX_PM }}">{{ __('Reset Password') }}</span></h2>
            {{-- <p>{{ __('Sign in by entering the information below?') }} </p> --}}
        </div>
        @php
            try {
                $lang                   = Utility::fetchUserLang();
                $passwordEmailRoute     = Route::has('password.email')
                    ? route('password.email')
                    : '#';
                $passwordEmailFormId    = 'password-email-form';
                $passwordEmailMsg       = Utility::fetchLinkMessage(
                    $lang,
                    ViewsConstants::AUT,
                    'password_email_route_unavailable'
                ) ?? 'Password reset email route is unavailable. Please contact technical support or your domain administrator.';
            } catch (\Throwable $e) {
                \Log::error('auth/forgot_password — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
            }
@endphp
        <form
            method="POST"
            action="{{ $passwordEmailRoute }}"
            id="{{ $passwordEmailFormId }}"
            data-url="{{ $passwordEmailRoute }}"
            data-guard-msg="{{ base64_encode($passwordEmailMsg) }}"
            >
            @csrf
            <div class="">
                <div class="{{ VC::FM_GB3 }}">
                    <label for="email" class="{{ VC::FM_LB }}">{{ __('E-Mail') }}</label>
                    <input id="email" type="email" class="{{ VC::FM_CT }} @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus>
                    @error('email')
                    <span class="{{ VC::INV_FB }}" role="alert">
                        <small>{{ $message }}</small>
                    </span>
                    @enderror
                </div>
                @if (!empty($settings[SettingsConstants::RCPT_MDL]) && $settings[SettingsConstants::RCPT_MDL] == 'on')
                    <div class="{{ VC::FM_GB3 }}">
                     {!! Anhskohbo\NoCaptcha\Facades\NoCaptcha::display($colorSettings[SettingsConstants::CST_DRK]=='on' ? ['data-theme' => 'dark'] : []) !!}
                        @error(SettingsConstants::G_RCPT_RES)
                        <span class="{{ VC::SM_TX_DNG }}" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                @endif
                <div class="{{ VC::D_GR }}">
                    <button type="submit" class="{{ VC::BT_PRM_BLK_MT2 }}">{{ __('Send Password Reset Link') }}</button>
                </div>
                @php
                    try {
                        $loginRoute        = Route::has('login') ? route('login') : '#';
                        $backLoginLinkId   = 'back-to-login-link';
                        $loginUnavailable  = Utility::fetchLinkMessage(
                            $lang,
                            ViewsConstants::AUT,
                            'login_unavailable'
                        ) ?? 'Login route is unavailable. Please contact technical support or your domain administrator.';
                    } catch (\Throwable $e) {
                        \Log::error('auth/forgot_password — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                    }
@endphp
                <p class="{{ VC::MY4_TXCT }}">
                    {{ __('Back to') }}
                    <a
                        id="{{ $backLoginLinkId }}"
                        href="{{ $loginRoute }}"
                        class="{{ VC::TX_PM }}"
                        data-url="{{ $loginRoute }}"
                        data-guard-msg="{{ base64_encode($loginUnavailable) }}"
                        data-event-alias="false"
                    >
                        {{ __('Login') }}
                    </a>
                </p>
                @push(StacksConstants::AUTH_CST_SCR)
                    <script defer>
                        (() => {
                            const el = document.getElementById('{{ $backLoginLinkId }}');
                            if (!el || el.getAttribute('data-event-alias') === 'true') return;
                            el.setAttribute('data-event-alias', 'true');
                            const url = el.getAttribute('data-url');
                            const msg = el.getAttribute('data-guard-msg');
                            if (!url || url === '#') {
                                (window.RouteGuard?.showToast || (m => alert(m)))(msg);
                                el.setAttribute('data-failed-route', 'true');
                                return;
                            }
                            el.addEventListener('click', event => {
                                try {
                                    event.preventDefault();
                                    window.location.href = url;
                                } catch (e) {}
                            });
                        })();
                    </script>
                @endpush
            </div>
        </form>
        @push(StacksConstants::AUTH_CST_SCR)
            <script defer src="{{ asset('assets/js/routes/auth/passwords/email.js') }}"></script>
        @endpush
    </div>
@endsection

{{-- @section(YieldingConstants::AUTH_CTT)
    <div class="">
        <h2 class="{{ VC::MB3_FW600 }}>{{__('Reset Password')}}"</h2>
        @if(session('status'))
            <p class="{{ VC::MB4_TXMT }}">
                {{ session('status') }}
            </p>
        @endif
    </div>

    <form method="POST" action="{{ route('password.email') }}">
        @csrf
        <div class="">
            <div class="{{ VC::FM_GB3 }}">
                <label for="email" class="{{ VC::FM_LB }}">{{ __('E-Mail') }}</label>
                <input id="email" type="email" class="{{ VC::FM_CT }} @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus>
                @error('email')
                <span class="{{ VC::INV_FB }}" role="alert">
                    <small>{{ $message }}</small>
                </span>
                @enderror
            </div>

            @if(env(SettingsConstants::RCPT_MDL) == 'on')
                <div class="{{ VC::FM_GB3 }}">
                    {!! Anhskohbo\NoCaptcha\Facades\NoCaptcha::display() !!}
                    @error(SettingsConstants::G_RCPT_RES)
                    <span class="{{ VC::SM_TX_DNG }}" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
            @endif

            <div class="{{ VC::D_GR }}">
                <button type="submit" class="{{ VC::BT_PRM_BLK_MT2 }}">{{ __('Send Password Reset Link') }}</button>
            </div>
            <p class="{{ VC::MY4_TXCT }}">{{__("Back to")}} <a href="{{ route('login') }}" class="{{ VC::TX_PM }}">{{__('Sign In')}}</a></p>

        </div>
    </form>
@endsection --}}
