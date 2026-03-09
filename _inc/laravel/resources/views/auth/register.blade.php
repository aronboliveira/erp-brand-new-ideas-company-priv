@php
$filePath??='';
	$commonSettings??=[];
	$setting??=[];
	$colorSettings??=[];
	$logo??='';
	$languages??=[DC::DEFAULT_LANG];
	$data ??= [];
    $lang = Utility::fetchUserLang();
	try {
        $user=auth()->user()?:null;
		$filePath=collect(
			array_column(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS),'file')
		)->first(fn($p)=>str_ends_with($p,'.blade.php'))??'';
		$commonSettings=Utility::prepareCommonViewData()?:[];
		$setting=Utility::settings()?:[];
		$colorSettings=$commonSettings[SC::CLR_STG]??($setting[SC::CLR_STG]??[]);
		$logo=Utility::getFile()?:'';
		$languages=Utility::languages()?:[DC::DEFAULT_LANG];
        $lang=$data[SC::LCL]??DC::DEFAULT_LANG;
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
@extends(ELC::AUTH)
@section(YC::AUTH_PG_TTL)
    {{ __('Register') }}
@endsection
@push(ST::AUTH_CST_SCR)
    <script defer src="{{ asset('assets/js/core/route-guard.js') }}"></script>
    @if (!empty($setting[SC::RCPT_MDL]) && $setting[SC::RCPT_MDL] == 'on')
        {!! Anhskohbo\NoCaptcha\Facades\NoCaptcha::renderJs() !!}
    @endif
@endpush
{{-- @section(YieldingConstants::AUTH_LG_BAR)

    <li class="{{ VC::NV_IT }}">
        <select class="{{ VC::BT_PRM }} {{ VC::MS2 }} me-2 language_option_bg {{ VC::TXCT }}" style="text-align-last: center;" onchange="this.options[this.selectedIndex].value && (window.location = this.options[this.selectedIndex].value);" id="language">
            @foreach (Utility::languages() as $code => $language)
                <option class="{{ VC::TXCT }}" @if ($lang == $code) selected @endif value="{{ route('register',$code) }}">{{ucfirst($language)}}</option>
            @endforeach
        </select>
    </li>
@endsection --}}
@section(YC::AUTH_LG_BAR)
    <div class="{{ VC::LNG_DD_DSK }}">
        <li class="{{ VC::LNG_DD_IT }}">
            <a class="{{ VC::DRP_BTN }}" href="#" data-bs-toggle="dropdown" aria-expanded="false">
                <span class="drp-text"> {{ !empty($languages) && !empty($languages[$lang]) ? $languages[$lang] : __(DC::DEFAULT_LANG) }}
                </span>
            </a>
            <div class="{{ VC::DRP_MN_DSH_END }}">
                @if(!empty($languages) && ((is_array($languages) && count($languages)) || ($languages instanceof Collection && $languages->isNotEmpty())))
                    @foreach($languages as $code => $language)
                        @php
                            try {
                                $registerUrl   = Route::has('register')
                                    ? route('register', $code)
                                    : '#';
                                $registerLinkId = 'register-link-' . $code;
                                $registerMsg   = Utility::fetchLinkMessage(
                                    app()->getLocale(),
                                    VW::AUT,
                                    'localized_register_unavailable'
                                ) ?? 'Translated registration route is unavailable. Please contact technical support or your domain administrator.';
                            } catch (\Throwable $e) {
                                \Log::error('auth/register — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                            }
@endphp
                        <a
                            id="{{ $registerLinkId }}"
                            href="{{ $registerUrl }}"
                            tabindex="0"
                            class="{{ VC::DRP_IT }}"
                            data-url="{{ $registerUrl }}"
                            data-guard-msg="{{ base64_encode($registerMsg) }}"
                            data-event-alias="false"
                        >
                            <span>{{ Str::ucfirst($language) }}</span>
                        </a>
                        @push(ST::AUTH_CST_SCR)
                            <script defer>
                                (() => {
                                    const el = document.getElementById('{{ $registerLinkId }}');
                                    if (!el || el.getAttribute('data-event-alias') === 'true') return;
                                    el.setAttribute('data-event-alias', 'true');
                                    const url = el.getAttribute('data-url');
                                    const msg = el.getAttribute('data-guard-msg');
                                    if (!url || url === '#') {
                                        const alertMsg = msg ?? '# ERROR';
                                        (window.RouteGuard?.showToast || (m => alert(m)))(alertMsg);
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
                    <span class="drp-text"> {{ __(DC::DEFAULT_LANG) }}</span>
                @endif
            </div>
        </li>
    </div>
@endsection

@section(YC::AUTH_CTT)
    <div class="{{ VC::CD_BD }}">
        <div>
            <h2 class="{{ VC::MB3_FW600 }}">{{ __('Register') }}</h2>
        </div>
        @php
            try {
                $registerRoute      = Route::has('register')
                    ? route('register')
                    : '#';
                $registerFormId     = 'register-form';
                $registerGuardMsg   = Utility::fetchLinkMessage(
                    $lang,
                    'auth',
                    'register_route_unavailable'
                ) ?? 'Registration is unavailable. Please contact technical support or your domain administrator.';
            } catch (\Throwable $e) {
                \Log::error('auth/register — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
            }
@endphp
        <form
            method="POST"
            action="{{ $registerRoute }}"
            id="{{ $registerFormId }}"
            data-url="{{ $registerRoute }}"
            data-guard-msg="{{ base64_encode($registerGuardMsg) }}"
            >
            @if (session('status'))
                <div class="{{ VC::MB4 }} font-medium text-lg text-green-600 {{ VC::TX_DNG }}">
                    {{ __('Email SMTP settings does not configured so please contact to your site admin.') }}
                </div>
            @endif
            @csrf
            <div class="">
                <div class="{{ VC::FM_GB3 }}">
                    <label for="name" class="{{ VC::FM_LB }}">{{__('Name')}}</label>
                    <input id="name" type="text" class="{{ VC::FM_CT }} @error('name') is-invalid @enderror" name="name" value="{{ old('name') }}" required autocomplete="name" autofocus>
                    @error('name')
                        <span class="{{ VC::INV_FB }}" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
                <div class="{{ VC::FM_GB3 }}">
                    <label for="email" class="{{ VC::FM_LB }}">{{__('Email')}}</label>
                    <input class="{{ VC::FM_CT }} @error('email') is-invalid @enderror" id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus>
                    @error('email')
                    <span class="{{ VC::INV_FB }}" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                    @enderror
                    <div class="{{ VC::INV_FB }}">
                        {{__('Please fill in your email')}}
                    </div>
                </div>
                <div class="{{ VC::FM_GB3 }}">
                    <label for="password" class="{{ VC::FM_LB }}">{{__('Password')}}</label>
                    <input id="password" type="password" data-indicator="pwindicator" class="{{ VC::FM_CT }} pwstrength @error('password') is-invalid @enderror" name="password" required autocomplete="new-password">
                    @error('password')
                    <span class="{{ VC::INV_FB }}" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                    @enderror
                    <div id="pwindicator" class="pwindicator">
                        <div class="bar"></div>
                        <div class="label"></div>
                    </div>
                </div>
                <div class="{{ VC::FM_GB3 }}">
                    <label for="password_confirmation" class="{{ VC::FM_LB }}">{{__('Password Confirmation')}}</label>
                    <input id="password_confirmation" type="password" data-indicator="password_confirmation" class="{{ VC::FM_CT }} pwstrength @error('password_confirmation') is-invalid @enderror" name="password_confirmation" required autocomplete="new-password">
                    @error('password_confirmation')
                    <span class="{{ VC::INV_FB }}" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                    @enderror
                    <div id="password_confirmation" class="pwindicator">
                        <div class="bar"></div>
                        <div class="label"></div>
                    </div>
                </div>
                @if (!empty($setting[SC::RCPT_MDL]) && $setting[SC::RCPT_MDL] == 'on')
                    <div class="{{ VC::FM_GB3 }}">
                        {!! Anhskohbo\NoCaptcha\Facades\NoCaptcha::display($colorSettings[SC::CST_DRK]=='on' ? ['data-theme' => 'dark'] : []) !!}
                        @error(SC::G_RCPT_RES)
                            <span class="{{ VC::SM_TX_DNG }}" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                @endif
                <div class="{{ VC::D_GR }}">
                    <button type="submit" class="{{ VC::BT_PRM }} btn-block mt-2">{{__('Register')}}</button>
                </div>
            </div>
            <p class="{{ VC::MY4_TXCT }}">{{__("Already have an account?")}}
                @php
                    try {
                        $loginUrl    = Route::has('login') ? route('login', $lang) : '#';
                        $loginFormId = 'loginLink';
                        $loginMsg    = Utility::fetchLinkMessage(
                            $lang,
                            VW::AUT,
                            'login_unavailable'
                        ) ?? 'Login route is unavailable. Please contact technical support or your domain administrator.';
                    } catch (\Throwable $e) {
                        \Log::error('auth/register — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                    }
@endphp
                <a
                    id="{{ $loginFormId }}"
                    href="{{ $loginUrl }}"
                    class="{{ VC::TX_PM }}"
                    data-url="{{ $loginUrl }}"
                    data-guard-msg="{{ base64_encode($loginMsg) }}"
                    data-event-alias="false"
                >
                    {{ __('Login') }}
                </a>
                @push(ST::AUTH_CST_SCR)
                    <script defer src="{{ asset('assets/js/routes/auth/login/link.js') }}"></script>
                @endpush
            </p>
        </form>
        @push(ST::AUTH_CST_SCR)
            <script defer src="{{ asset('assets/js/routes/auth/register/form.js') }}"></script>
        @endpush
    </div>
@endsection

{{-- @section(YC::AUTH_CTT)
    <div class="">
        <h2 class="{{ VC::MB3_FW600 }}">{{__('Register')}}</h2>
    </div>
    <form method="POST" action="{{ route('register') }}">
        @if (session('status'))
            <div class="{{ VC::MB4 }} font-medium text-lg text-green-600 {{ VC::TX_DNG }}">
                {{ __('Email SMTP settings does not configured so please contact to your site admin.') }}
            </div>
        @endif
        @csrf
        <div class="">
            <div class="{{ VC::FM_GB3 }}">
                <label for="name" class="{{ VC::FM_LB }}">{{__('Name')}}</label>
                <input id="name" type="text" class="{{ VC::FM_CT }} @error('name') is-invalid @enderror" name="name" value="{{ old('name') }}" required autocomplete="name" autofocus>
                @error('name')
                <span class="{{ VC::INV_FB }}" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
                @enderror
            </div>
            <div class="{{ VC::FM_GB3 }}">
                <label for="email" class="{{ VC::FM_LB }}">{{__('Email')}}</label>
                <input class="{{ VC::FM_CT }} @error('email') is-invalid @enderror" id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus>
                @error('email')
                <span class="{{ VC::INV_FB }}" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                @enderror
                <div class="{{ VC::INV_FB }}">
                    {{__('Please fill in your email')}}
                </div>
            </div>
            <div class="{{ VC::FM_GB3 }}">
                <label for="password" class="{{ VC::FM_LB }}">{{__('Password')}}</label>
                <input id="password" type="password" data-indicator="pwindicator" class="{{ VC::FM_CT }} pwstrength @error('password') is-invalid @enderror" name="password" required autocomplete="new-password">
                @error('password')
                <span class="{{ VC::INV_FB }}" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                @enderror
                <div id="pwindicator" class="pwindicator">
                    <div class="bar"></div>
                    <div class="label"></div>
                </div>
            </div>
            <div class="{{ VC::FM_GB3 }}">
                <label for="password_confirmation" class="{{ VC::FM_LB }}">{{__('Password Confirmation')}}</label>
                <input id="password_confirmation" type="password" data-indicator="password_confirmation" class="{{ VC::FM_CT }} pwstrength @error('password_confirmation') is-invalid @enderror" name="password_confirmation" required autocomplete="new-password">
                @error('password_confirmation')
                <span class="{{ VC::INV_FB }}" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                @enderror
                <div id="password_confirmation" class="pwindicator">
                    <div class="bar"></div>
                    <div class="label"></div>
                </div>
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

            <div class="{{ VC::D_GR }}">
                <button type="submit" class="{{ VC::BT_PRM_BLK_MT2 }}">{{__('Register')}}</button>
            </div>

        </div>
        <p class="{{ VC::MY4_TXCT }}">{{__("Already' have an account?")}} <a href="{{ route('login',$lang) }}" class="{{ VC::TX_PM }}">{{__('Login')}}</a></p>
    </form>
@endsection --}}
