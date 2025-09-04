@php
	use App\Config\Constants\{
        DatabaseConstants,
        ExtendingLayoutsConstants,SettingsConstants,
        StacksConstants, UsersConstants, ViewsConstants,
        ViewClassNamesConstants as VC,YieldingConstants};
	use App\Models\Utility;
    use Illuminate\Support\Facades\{Log, Route};
	use Illuminate\Support\Str;
	use Symfony\Component\Console\Output\ConsoleOutput;
	$settings??=[];
	$colorSettings??=[];
    $lang = Utility::fetchUserLang();
	$languages??=[$lang];
	$filePath??='';
	try {
        $user=auth()->user()?:null;
		$settings=Utility::settings()?:[];
		$colorSettings=$settings[SettingsConstants::CLR_STG]??[];
		$languages=Utility::languages()?:[DatabaseConstants::DEFAULT_LANG];
        $lang = isset($user[UsersConstants::COL_LG])?$user[UsersConstants::COL_LG]:DatabaseConstants::DEFAULT_LANG;
        if (empty($lang)) $lang = DatabaseConstants::DEFAULT_LANG;
		$filePath=collect(
			array_column(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS),'file')
		)->first(fn($p)=>str_ends_with($p,'.blade.php'))??'';
		Log::debug(
			"Rendering Forgot Password Blade ({$filePath})",
			['route'=>request()?->getRequestUri()??'Undefined URI','user'=>optional(auth()->user())->id??'Unidentified User']
		);
		(new ConsoleOutput)
			->writeln(
				"Rendering Forgot Password Blade ({$filePath}) for "
				.(request()?->getRequestUri()??'Undefined URI')
			);
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
@if (!empty($settings[SettingsConstants::RCPT_MDL]) && $settings[SettingsConstants::RCPT_MDL] == 'on')
    {!! Anhskohbo\NoCaptcha\Facades\NoCaptcha::renderJs() !!}
@endif
@endpush
@section(YieldingConstants::AUTH_LG_BAR)
    <div class="{{ VC::LNG_DD_DSK }}">
        <li class="{{ VC::LNG_DD_IT }}">
            <a class="{{ VC::DRP_BTN }}" href="#" data-bs-toggle="dropdown" aria-expanded="false">
                <span class="drp-text"> {{ $languages[$lang] }}
                </span>
            </a>
            <div class="{{ VC::DRP_MN_DSH_END }}">
                @foreach($languages as $code => $language)
                    @php
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
                    @endphp
                    <a
                        id="{{ $passwordRequestLinkId }}"
                        href="{{ $passwordRequestRoute }}"
                        tabindex="0"
                        class="dropdown-item"
                        data-url="{{ $passwordRequestRoute }}"
                        data-guard-msg="{{ $passwordRequestRouteMsg }}"
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
                                        const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                                        let container       = document.getElementById('toast-container');
                                        if (!container) {
                                            container       = document.createElement('div');
                                            container.id    = 'toast-container';
                                            document.body.appendChild(container);
                                        }
                                        if (bootstrapLink && window.bootstrap) {
                                            const toastEl      = document.createElement('div');
                                            toastEl.className  = 'toast';
                                            toastEl.setAttribute('role', 'alert');
                                            toastEl.setAttribute('aria-live', 'assertive');
                                            toastEl.setAttribute('aria-atomic', 'true');
                                            const body         = document.createElement('div');
                                            body.className     = 'toast-body';
                                            body.textContent   = msg;
                                            toastEl.appendChild(body);
                                            container.appendChild(toastEl);
                                            bootstrap.Toast.getOrCreateInstance(toastEl).show();
                                        } else {
                                            alert(msg);
                                        }
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
    <div class="card-body">
        <div>
            <h2 class="{{ VC::MB3_FW600 }}><span class="text-primary">{{ __('Reset Password') }}"</span></h2>
            {{-- <p>{{ __('Sign in by entering the information below?') }} </p> --}}
        </div>
        @php
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
        @endphp
        <form
            method="POST"
            action="{{ $passwordEmailRoute }}"
            id="{{ $passwordEmailFormId }}"
            data-url="{{ $passwordEmailRoute }}"
            data-guard-msg="{{ $passwordEmailMsg }}"
            >
            @csrf
            <div class="">
                <div class="{{ VC::FM_GB3 }}">
                    <label for="email" class="form-label">{{ __('E-Mail') }}</label>
                    <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus>
                    @error('email')
                    <span class="invalid-feedback" role="alert">
                        <small>{{ $message }}</small>
                    </span>
                    @enderror
                </div>
                @if (!empty($settings[SettingsConstants::RCPT_MDL]) && $settings[SettingsConstants::RCPT_MDL] == 'on')
                    <div class="{{ VC::FM_GB3 }}">
                     {!! Anhskohbo\NoCaptcha\Facades\NoCaptcha::display($colorSettings[SettingsConstants::CST_DRK]=='on' ? ['data-theme' => 'dark'] : []) !!}                        
                        @error(SettingsConstants::G_RCPT_RES)
                        <span class="small text-danger" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                @endif
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary btn-block mt-2">{{ __('Send Password Reset Link') }}</button>
                </div>
                @php
                    $loginRoute        = Route::has('login') ? route('login') : '#';
                    $backLoginLinkId   = 'back-to-login-link';
                    $loginUnavailable  = Utility::fetchLinkMessage(
                        $lang,
                        ViewsConstants::AUT,
                        'login_unavailable'
                    ) ?? 'Login route is unavailable. Please contact technical support or your domain administrator.';
                @endphp
                <p class="my-4 text-center">
                    {{ __('Back to') }}
                    <a
                        id="{{ $backLoginLinkId }}"
                        href="{{ $loginRoute }}"
                        class="text-primary"
                        data-url="{{ $loginRoute }}"
                        data-guard-msg="{{ $loginUnavailable }}"
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
                                const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                                let container = document.getElementById('toast-container');
                                if (!container) {
                                    container = document.createElement('div');
                                    container.id = 'toast-container';
                                    document.body.appendChild(container);
                                }
                                if (bootstrapLink && window.bootstrap) {
                                    const toastEl = document.createElement('div');
                                    toastEl.className = 'toast';
                                    toastEl.setAttribute('role', 'alert');
                                    toastEl.setAttribute('aria-live', 'assertive');
                                    toastEl.setAttribute('aria-atomic', 'true');
                                    const body = document.createElement('div');
                                    body.className = 'toast-body';
                                    body.textContent = msg;
                                    toastEl.appendChild(body);
                                    container.appendChild(toastEl);
                                    bootstrap.Toast.getOrCreateInstance(toastEl).show();
                                } else {
                                    alert(msg);
                                }
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
                <label for="email" class="form-label">{{ __('E-Mail') }}</label>
                <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus>
                @error('email')
                <span class="invalid-feedback" role="alert">
                    <small>{{ $message }}</small>
                </span>
                @enderror
            </div>

            @if(env(SettingsConstants::RCPT_MDL) == 'on')
                <div class="{{ VC::FM_GB3 }}">
                    {!! Anhskohbo\NoCaptcha\Facades\NoCaptcha::display() !!}
                    @error(SettingsConstants::G_RCPT_RES)
                    <span class="small text-danger" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
            @endif

            <div class="d-grid">
                <button type="submit" class="btn btn-primary btn-block mt-2">{{ __('Send Password Reset Link') }}</button>
            </div>
            <p class="my-4 text-center">{{__("Back to")}} <a href="{{ route('login') }}" class="text-primary">{{__('Sign In')}}</a></p>

        </div>
    </form>
@endsection --}}

