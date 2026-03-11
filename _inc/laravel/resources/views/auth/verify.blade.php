@php
$filePath??='';
	$settings??=[];
	$logo??='';
	$languages??=[DC::DEFAULT_LANG];
	$company_logo??='';
	$lang = 'en';
	try {
		$filePath=collect(array_column(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS),'file'))
			->first(fn($p)=>str_ends_with($p,'.blade.php'))??'';
		$settings=Utility::settings()?:[];
		$logo=Utility::getFile()?:'';
		$languages=Utility::languages()?:[DC::DEFAULT_LANG];
		$company_logo=Utility::getValByName(SC::CPN_LG)?:'';
		$lang=App::getLocale('lang')?:DC::DEFAULT_LANG;
	} catch (\Error $e) {
		Log::error(
			'Error fetching data for Verify User Blade',
			[
				'exception_class'=>get_class($e),
				'message'=>$e->getMessage(),
				'file'=>$e->getFile(),
				'line'=>$e->getLine(),
				'blade'=>$filePath
			]
		);
	} catch (\Exception $e) {
		Log::error(
			'Exception fetching data for Verify User Blade',
			[
				'exception_class'=>get_class($e),
				'message'=>$e->getMessage(),
				'file'=>$e->getFile(),
				'line'=>$e->getLine(),
				'blade'=>$filePath
			]
		);
	} catch (\Throwable $e) {
		Log::error(
			'Throwable fetching data for Verify User Blade',
			[
				'exception_class'=>get_class($e),
				'message'=>$e->getMessage(),
				'file'=>$e->getFile(),
				'line'=>$e->getLine(),
				'blade'=>$filePath
			]
		);
	}
@endphp
@extends(ELC::AUTH)
@section(YC::AUTH_PG_TTL)
    {{ __('Verify Email') }}
@endsection
@section(YC::AUTH_LG_BAR)
    <div class="{{ VC::LNG_DD_DSK }}">
        <li class="{{ VC::LNG_DD_IT }}">
            <a class="{{ VC::DRP_BTN }}" href="#" data-bs-toggle="dropdown" aria-expanded="false">
                <span class="drp-text"> {{ !empty($languages) && !empty($languages[$lang]) ? $languages[$lang] : __(DC::DEFAULT_LANG) }}
                </span>
            </a>
            <div class="{{ VC::DRP_MN_DSH_END }}">
                @foreach ($languages as $code => $language)
                @php
                    try {
                        $verificationNoticeRoute = Route::has('verification.notice')
                            ? route('verification.notice', $code)
                            : (Route::has(Str::kebab('verification.notice'))
                                ? route(Str::kebab('verification.notice'), $code)
                                : '#');
                        $verificationNoticeLinkId = 'verification-notice-link-' . $code;
                        $verificationNoticeMsg    = Utility::fetchLinkMessage(
                            $lang,
                            VW::AUT,
                            'verification_notice_route_unavailable'
                        ) ?? 'Verification notice route is unavailable. Please contact technical support or your domain administrator.';
                    } catch (\Throwable $e) {
                        \Log::error('auth/verify — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                    }
@endphp
                <a
                    id="{{ $verificationNoticeLinkId }}"
                    href="{{ $verificationNoticeRoute }}"
                    tabindex="0"
                    class="{{ VC::DRP_IT }}"
                    data-url="{{ $verificationNoticeRoute }}"
                    data-guard-msg="{{ base64_encode($verificationNoticeMsg) }}"
                >
                    <span>{{ Str::upper($language) }}</span>
                </a>
                @push(ST::AUTH_CST_SCR)
                    <script defer>
                        (() => {
                            const el = document.getElementById('{{ $verificationNoticeLinkId }}');
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

@section(YC::AUTH_CTT)
    <div class="{{ VC::CD_BD }}">
        @if (session('status') == 'verification-link-sent')
            <div class="{{ VC::MB4 }} font-medium {{ VC::TXSM }} text-green-600 {{ VC::TX_PM }}">
                {{ __('A new verification link has been sent to the email address you provided during registration.') }}
            </div>
        @endif
        <div class="{{ VC::MB4 }} {{ VC::TXSM }} text-gray-600">
            {{ __('Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you? If you didn\'t receive the email, we will gladly send you another.') }}
        </div>
        <div class="{{ VC::MT4 }} flex items-center justify-between">
            @php
                try {
                    $resendRoute    = Route::has('verification.send')
                        ? route('verification.send')
                        : (Route::has(Str::kebab('verification.send'))
                            ? route(Str::kebab('verification.send'))
                            : '#');
                    $resendFormId   = 'resend-verification-form';
                    $resendMsg      = Utility::fetchLinkMessage(
                        $lang,
                        VW::AUT,
                        'verification_send_route_unavailable'
                    ) ?? 'Resend verification route is unavailable. Please contact technical support or your domain administrator.';
                    $logoutRoute    = Route::has('logout')
                        ? route('logout')
                        : (Route::has(Str::kebab('logout'))
                            ? route(Str::kebab('logout'))
                            : '#');
                    $logoutFormId   = 'logout-form';
                    $logoutMsg      = Utility::fetchLinkMessage(
                        $lang,
                        VW::AUT,
                        'logout_route_unavailable'
                    ) ?? 'Logout route is unavailable. Please contact technical support or your domain administrator.';
                } catch (\Throwable $e) {
                    \Log::error('auth/verify — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                }
@endphp
            <div class="{{ VC::RW }}">
                <div class="{{ VC::C_AT }}">
                    <form
                        method="POST"
                        action="{{ $resendRoute }}"
                        id="{{ $resendFormId }}"
                        data-url="{{ $resendRoute }}"
                        data-guard-msg="{{ base64_encode($resendMsg) }}"
                    >
                        @csrf
                        <button type="submit" class="{{ VC::BT_SM_PM }}">{{ __('Resend Verification Email') }}</button>
                    </form>
                </div>
                <div class="{{ VC::C_AT }}">
                    <form
                        method="POST"
                        action="{{ $logoutRoute }}"
                        id="{{ $logoutFormId }}"
                        data-url="{{ $logoutRoute }}"
                        data-guard-msg="{{ base64_encode($logoutMsg) }}"
                    >
                        @csrf
                        <button type="submit" class="{{ VC::BT_SM_DG }}">{{ __('Logout') }}</button>
                    </form>
                </div>
            </div>
            @push(ST::AUTH_CST_SCR)
                <script defer src="{{ asset('assets/js/routes/auth/login/verify.js') }}"></script>
            @endpush
        </div>
    </div>
@endsection

{{-- @section(YieldingConstants::AUTH_CTT)
    <div class="{{ VC::CXL12 }}">
        <div class="">
            @if (session('status') == 'verification-link-sent')
                <div class="{{ VC::MB4 }} font-medium {{ VC::TXSM }} text-green-600 {{ VC::TX_PM }}">
                    {{ __('A new verification link has been sent to the email address you provided during registration.') }}
                </div>
            @endif
            <div class="{{ VC::MB4 }} {{ VC::TXSM }} text-gray-600">
                {{ __('Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you? If you didn\'t receive the email, we will gladly send you another.') }}
            </div>
            <div class="{{ VC::MT4 }} flex items-center justify-between">
                <div class="row">
                    <div class="{{ VC::C_AT }}">
                        <form method="POST" action="{{ route('verification.send') }}">
                            @csrf
                            <button type="submit" class="{{ VC::BT_PRM }} btn-sm"> {{ __('Resend Verification Email') }}
                            </button>
                        </form>
                    </div>
                    <div class="{{ VC::C_AT }}">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="btn btn-danger btn-sm">{{ __('Logout') }}</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection() --}}
