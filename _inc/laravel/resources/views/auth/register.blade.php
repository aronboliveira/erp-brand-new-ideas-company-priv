@php
	use App\Config\Constants\{
        DatabaseConstants,
        ExtendingLayoutsConstants,StacksConstants, ViewsConstants,
        SettingsConstants,ViewClassNamesConstants,YieldingConstants};
	use App\Models\Utility;
    use Illuminate\Support\Facades\{Log, Route};
	use Illuminate\Support\Str;
	use Symfony\Component\Console\Output\ConsoleOutput;
	$filePath??='';
	$commonSettings??=[];
	$setting??=[];
	$colorSettings??=[];
	$logo??='';
	$languages??=[DatabaseConstants::DEFAULT_LANG];
    $lang = Utility::fetchUserLang();
	try {
        $user=auth()->user()?:null;
		$filePath=collect(
			array_column(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS),'file')
		)->first(fn($p)=>str_ends_with($p,'.blade.php'))??'';
		Log::debug(
			"Rendering Register User Blade ({$filePath})",
			[
				'route'=>request()?->getRequestUri()??'Undefined URI',
				'user'=>optional(auth()->user())->id??'Unidentified User'
			]
		);
		(new ConsoleOutput)
			->writeln(
				"Rendering Register User Blade ({$filePath}) for "
				.(request()?->getRequestUri()??'Undefined URI')
			);
		$commonSettings=Utility::prepareCommonViewData()?:[];
		$setting=Utility::settings()?:[];
		$colorSettings=$commonSettings[SettingsConstants::CLR_STG]??($setting[SettingsConstants::CLR_STG]??[]);
		$logo=Utility::getFile()?:'';
		$languages=Utility::languages()?:[DatabaseConstants::DEFAULT_LANG];
        $lang=$data[SettingsConstants::LCL]??DatabaseConstants::DEFAULT_LANG;
	} catch (\Error $e) {
		Log::error(
			'Error fetching data for Register User Blade',
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
			'Exception fetching data for Register User Blade',
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
			'Throwable fetching data for Register User Blade',
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
@section(YieldingConstants::AUTH_PG_TTL)
    {{ __('Register') }}
@endsection
@push(StacksConstants::AUTH_CST_SCR)
    @if (!empty($setting[SettingsConstants::RCPT_MDL]) && $setting[SettingsConstants::RCPT_MDL] == 'on')
        {!! Anhskohbo\NoCaptcha\Facades\NoCaptcha::renderJs() !!}
    @endif
@endpush
{{-- @section(YieldingConstants::AUTH_LG_BAR)

    <li class="nav-item">
        <select class="btn btn-primary ms-2 me-2 language_option_bg text-center" style="text-align-last: center;" onchange="this.options[this.selectedIndex].value && (window.location = this.options[this.selectedIndex].value);" id="language">
            @foreach (Utility::languages() as $code => $language)
                <option class="text-center" @if ($lang == $code) selected @endif value="{{ route('register',$code) }}">{{ucfirst($language)}}</option>
            @endforeach
        </select>
    </li>
@endsection --}}
@section(YieldingConstants::AUTH_LG_BAR)
    <div class="{{ ViewClassNamesConstants::LNG_DD_DSK }}">
        <li class="{{ ViewClassNamesConstants::LNG_DD_IT }}">
            <a class="{{ ViewClassNamesConstants::DRP_BTN }}" href="#" data-bs-toggle="dropdown" aria-expanded="false">
                <span class="drp-text"> {{ !empty($languages) && !empty($languages[$lang]) ? $languages[$lang] : __(DatabaseConstants::DEFAULT_LANG) }}
                </span>
            </a>
            <div class="{{ ViewClassNamesConstants::DRP_MN_DSH_END }}">
                @if(is_array($languages) && count($languages) || $languages instanceof Collection && $languages->isNotEmpty())
                    @foreach($languages as $code => $language)
                        @php
                            $registerUrl   = Route::has('register')
                                ? route('register', $code)
                                : '#';
                            $registerLinkId = 'register-link-' . $code;
                            $registerMsg   = Utility::fetchLinkMessage(
                                app()->getLocale(),
                                ViewsConstants::AUT,
                                'localized_register_unavailable'
                            ) ?? 'Translated registration route is unavailable. Please contact technical support or your domain administrator.';
                        @endphp
                        <a
                            id="{{ $registerLinkId }}"
                            href="{{ $registerUrl }}"
                            tabindex="0"
                            class="dropdown-item"
                            data-url="{{ $registerUrl }}"
                            data-guard-msg="{{ $registerMsg }}"
                            data-event-alias="false"
                        >
                            <span>{{ Str::ucfirst($language) }}</span>
                        </a>
                        @push(StacksConstants::AUTH_CST_SCR)
                            <script defer>
                                (() => {
                                    const el = document.getElementById('{{ $registerLinkId }}');
                                    if (!el || el.getAttribute('data-event-alias') === 'true') return;
                                    el.setAttribute('data-event-alias', 'true');
                                    const url = el.getAttribute('data-url');
                                    const msg = el.getAttribute('data-guard-msg');
                                    if (!url || url === '#') {
                                        const alertMsg = msg ?? '# ERROR';
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
                                            body.textContent = alertMsg;
                                            toastEl.appendChild(body);
                                            container.appendChild(toastEl);
                                            bootstrap.Toast.getOrCreateInstance(toastEl).show();
                                        } else {
                                            alert(alertMsg);
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
            <h2 class="{{ ViewClassNamesConstants::MB3_FW600 }}">{{ __('Register') }}</h2>
        </div>
        @php
            $registerRoute      = Route::has('register')
                ? route('register')
                : '#';
            $registerFormId     = 'register-form';
            $registerGuardMsg   = Utility::fetchLinkMessage(
                $lang,
                'auth',
                'register_route_unavailable'
            ) ?? 'Registration is unavailable. Please contact technical support or your domain administrator.';
        @endphp
        <form
            method="POST"
            action="{{ $registerRoute }}"
            id="{{ $registerFormId }}"
            data-url="{{ $registerRoute }}"
            data-guard-msg="{{ $registerGuardMsg }}"
            >
            @if (session('status'))
                <div class="mb-4 font-medium text-lg text-green-600 text-danger">
                    {{ __('Email SMTP settings does not configured so please contact to your site admin.') }}
                </div>
            @endif
            @csrf
            <div class="">
                <div class="{{ ViewClassNamesConstants::FM_GB3 }}">
                    <label for="name" class="form-label">{{__('Name')}}</label>
                    <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name') }}" required autocomplete="name" autofocus>
                    @error('name')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
                <div class="{{ ViewClassNamesConstants::FM_GB3 }}">
                    <label for="email" class="form-label">{{__('Email')}}</label>
                    <input class="form-control @error('email') is-invalid @enderror" id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus>
                    @error('email')
                    <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                    @enderror
                    <div class="invalid-feedback">
                        {{__('Please fill in your email')}}
                    </div>
                </div>
                <div class="{{ ViewClassNamesConstants::FM_GB3 }}">
                    <label for="password" class="form-label">{{__('Password')}}</label>
                    <input id="password" type="password" data-indicator="pwindicator" class="form-control pwstrength @error('password') is-invalid @enderror" name="password" required autocomplete="new-password">
                    @error('password')
                    <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                    @enderror
                    <div id="pwindicator" class="pwindicator">
                        <div class="bar"></div>
                        <div class="label"></div>
                    </div>
                </div>
                <div class="{{ ViewClassNamesConstants::FM_GB3 }}">
                    <label for="password_confirmation" class="form-label">{{__('Password Confirmation')}}</label>
                    <input id="password_confirmation" type="password" data-indicator="password_confirmation" class="form-control pwstrength @error('password_confirmation') is-invalid @enderror" name="password_confirmation" required autocomplete="new-password">
                    @error('password_confirmation')
                    <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                    @enderror
                    <div id="password_confirmation" class="pwindicator">
                        <div class="bar"></div>
                        <div class="label"></div>
                    </div>
                </div>
                @if (!empty($setting[SettingsConstants::RCPT_MDL]) && $setting[SettingsConstants::RCPT_MDL] == 'on')
                    <div class="{{ ViewClassNamesConstants::FM_GB3 }}">
                        {!! Anhskohbo\NoCaptcha\Facades\NoCaptcha::display($colorSettings[SettingsConstants::CST_DRK]=='on' ? ['data-theme' => 'dark'] : []) !!}
                        @error(SettingsConstants::G_RCPT_RES)
                            <span class="small text-danger" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                @endif
                <div class="d-grid">
                    <button type="submit" class="{{ ViewClassNamesConstants::BT_PRM }} btn-block mt-2">{{__('Register')}}</button>
                </div>
            </div>
            <p class="my-4 text-center">{{__("Already have an account?")}} 
                @php
                    $loginUrl    = Route::has('login') ? route('login', $lang) : '#';
                    $loginFormId = 'loginLink';
                    $loginMsg    = Utility::fetchLinkMessage(
                        $lang,
                        ViewsConstants::AUT,
                        'login_unavailable'
                    ) ?? 'Login route is unavailable. Please contact technical support or your domain administrator.';
                @endphp
                <a
                    id="{{ $loginFormId }}"
                    href="{{ $loginUrl }}"
                    class="text-primary"
                    data-url="{{ $loginUrl }}"
                    data-guard-msg="{{ $loginMsg }}"
                    data-event-alias="false"
                >
                    {{ __('Login') }}
                </a>
                @push(StacksConstants::AUTH_CST_SCR)
                    <script defer src="{{ asset('assets/js/routes/auth/login/link.js') }}"></script>
                @endpush
            </p>
        </form>
        @push(StacksConstants::AUTH_CST_SCR)
            <script defer src="{{ asset('assets/js/routes/auth/register/form.js') }}"></script>
        @endpush
    </div>
@endsection

{{-- @section(YieldingConstants::AUTH_CTT)
    <div class="">
        <h2 class="{{ ViewClassNamesConstants::MB3_FW600 }}">{{__('Register')}}</h2>
    </div>
    <form method="POST" action="{{ route('register') }}">
        @if (session('status'))
            <div class="mb-4 font-medium text-lg text-green-600 text-danger">
                {{ __('Email SMTP settings does not configured so please contact to your site admin.') }}
            </div>
        @endif
        @csrf
        <div class="">
            <div class="{{ ViewClassNamesConstants::FM_GB3 }}">
                <label for="name" class="form-label">{{__('Name')}}</label>
                <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name') }}" required autocomplete="name" autofocus>
                @error('name')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
                @enderror
            </div>
            <div class="{{ ViewClassNamesConstants::FM_GB3 }}">
                <label for="email" class="form-label">{{__('Email')}}</label>
                <input class="form-control @error('email') is-invalid @enderror" id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus>
                @error('email')
                <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                @enderror
                <div class="invalid-feedback">
                    {{__('Please fill in your email')}}
                </div>
            </div>
            <div class="{{ ViewClassNamesConstants::FM_GB3 }}">
                <label for="password" class="form-label">{{__('Password')}}</label>
                <input id="password" type="password" data-indicator="pwindicator" class="form-control pwstrength @error('password') is-invalid @enderror" name="password" required autocomplete="new-password">
                @error('password')
                <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                @enderror
                <div id="pwindicator" class="pwindicator">
                    <div class="bar"></div>
                    <div class="label"></div>
                </div>
            </div>
            <div class="{{ ViewClassNamesConstants::FM_GB3 }}">
                <label for="password_confirmation" class="form-label">{{__('Password Confirmation')}}</label>
                <input id="password_confirmation" type="password" data-indicator="password_confirmation" class="form-control pwstrength @error('password_confirmation') is-invalid @enderror" name="password_confirmation" required autocomplete="new-password">
                @error('password_confirmation')
                <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                @enderror
                <div id="password_confirmation" class="pwindicator">
                    <div class="bar"></div>
                    <div class="label"></div>
                </div>
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

            <div class="d-grid">
                <button type="submit" class="btn btn-primary btn-block mt-2">{{__('Register')}}</button>
            </div>

        </div>
        <p class="my-4 text-center">{{__("Already' have an account?")}} <a href="{{ route('login',$lang) }}" class="text-primary">{{__('Login')}}</a></p>
    </form>
@endsection --}}
