@php
	use App\Config\Constants\{DatabaseConstants, ExtendingLayoutsConstants,
        SettingsConstants,StacksConstants, ViewsConstants,
        ViewClassNamesConstants,YieldingConstants};
	use App\Models\Utility;
	use Illuminate\Support\Facades\{App,Log,Route};
	use Illuminate\Support\Str;
	use Symfony\Component\Console\Output\ConsoleOutput;
	$filePath??='';
	$settings??=[];
	$logo??='';
	$languages??=[DatabaseConstants::DEFAULT_LANG];
	$company_logo??='';
	$lang = Utility::fetchUserLang();
	try {
		$filePath=collect(array_column(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS),'file'))
			->first(fn($p)=>str_ends_with($p,'.blade.php'))??'';
		Log::debug(
			"Rendering Verify User Blade ({$filePath})",
			[
				'route'=>request()?->getRequestUri()??'Undefined URI',
				'user'=>optional(auth()->user())->id??'Unidentified User'
			]
		);
		(new ConsoleOutput)
			->writeln(
				"Rendering Verify User Blade ({$filePath}) for "
				.(request()?->getRequestUri()??'Undefined URI')
			);
		$settings=Utility::settings()?:[];
		$logo=Utility::getFile()?:'';
		$languages=Utility::languages()?:[DatabaseConstants::DEFAULT_LANG];
		$company_logo=Utility::getValByName(SettingsConstants::CPN_LG)?:'';
		$lang=App::getLocale('lang')?:DatabaseConstants::DEFAULT_LANG;
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
@extends(ExtendingLayoutsConstants::AUTH)
@section(YieldingConstants::AUTH_PG_TTL)
    {{ __('Verify Email') }}
@endsection
@section(YieldingConstants::AUTH_LG_BAR)
    <div class="{{ ViewClassNamesConstants::LNG_DD_DSK }}">
        <li class="{{ ViewClassNamesConstants::LNG_DD_IT }}">
            <a class="{{ ViewClassNamesConstants::DRP_BTN }}" href="#" data-bs-toggle="dropdown" aria-expanded="false">
                <span class="drp-text"> {{ !empty($languages) && !empty($languages[$lang]) ? $languages[$lang] : __(DatabaseConstants::DEFAULT_LANG) }}
                </span>
            </a>
            <div class="{{ ViewClassNamesConstants::DRP_MN_DSH_END }}">
                @php
                    $verificationNoticeRoute = Route::has('verification.notice')
                        ? route('verification.notice', $code)
                        : (Route::has(Str::kebab('verification.notice'))
                            ? route(Str::kebab('verification.notice'), $code)
                            : '#');
                    $verificationNoticeLinkId = 'verification-notice-link-' . $code;
                    $verificationNoticeMsg    = Utility::fetchLinkMessage(
                        $lang,
                        ViewsConstants::AUT,
                        'verification_notice_route_unavailable'
                    ) ?? 'Verification notice route is unavailable. Please contact technical support or your domain administrator.';
                @endphp
                <a
                    id="{{ $verificationNoticeLinkId }}"
                    href="{{ $verificationNoticeRoute }}"
                    tabindex="0"
                    class="dropdown-item"
                    data-url="{{ $verificationNoticeRoute }}"
                    data-guard-msg="{{ $verificationNoticeMsg }}"
                >
                    <span>{{ Str::upper($language) }}</span>
                </a>
                @push(StacksConstants::AUTH_CST_SCR)
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

@section(YieldingConstants::AUTH_CTT)
    <div class="card-body">
        @if (session('status') == 'verification-link-sent')
            <div class="mb-4 font-medium text-sm text-green-600 text-primary">
                {{ __('A new verification link has been sent to the email address you provided during registration.') }}
            </div>
        @endif
        <div class="mb-4 text-sm text-gray-600">
            {{ __('Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you? If you didn\'t receive the email, we will gladly send you another.') }}
        </div>
        <div class="mt-4 flex items-center justify-between">
            @php
                $resendRoute    = Route::has('verification.send')
                    ? route('verification.send')
                    : (Route::has(Str::kebab('verification.send'))
                        ? route(Str::kebab('verification.send'))
                        : '#');
                $resendFormId   = 'resend-verification-form';
                $resendMsg      = Utility::fetchLinkMessage(
                    $lang,
                    ViewsConstants::AUT,
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
                    ViewsConstants::AUT,
                    'logout_route_unavailable'
                ) ?? 'Logout route is unavailable. Please contact technical support or your domain administrator.';
            @endphp
            <div class="row">
                <div class="col-auto">
                    <form
                        method="POST"
                        action="{{ $resendRoute }}"
                        id="{{ $resendFormId }}"
                        data-url="{{ $resendRoute }}"
                        data-guard-msg="{{ $resendMsg }}"
                    >
                        @csrf
                        <button type="submit" class="btn btn-primary btn-sm">{{ __('Resend Verification Email') }}</button>
                    </form>
                </div>
                <div class="col-auto">
                    <form
                        method="POST"
                        action="{{ $logoutRoute }}"
                        id="{{ $logoutFormId }}"
                        data-url="{{ $logoutRoute }}"
                        data-guard-msg="{{ $logoutMsg }}"
                    >
                        @csrf
                        <button type="submit" class="btn btn-danger btn-sm">{{ __('Logout') }}</button>
                    </form>
                </div>
            </div>
            @push(StacksConstants::AUTH_CST_SCR)
                <script defer src="{{ asset('assets/js/routes/auth/login/verify.js') }}"></script>
            @endpush
        </div>
    </div>
@endsection


{{-- @section(YieldingConstants::AUTH_CTT)
    <div class="col-xl-12">
        <div class="">
            @if (session('status') == 'verification-link-sent')
                <div class="mb-4 font-medium text-sm text-green-600 text-primary">
                    {{ __('A new verification link has been sent to the email address you provided during registration.') }}
                </div>
            @endif
            <div class="mb-4 text-sm text-gray-600">
                {{ __('Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you? If you didn\'t receive the email, we will gladly send you another.') }}
            </div>
            <div class="mt-4 flex items-center justify-between">
                <div class="row">
                    <div class="col-auto">
                        <form method="POST" action="{{ route('verification.send') }}">
                            @csrf
                            <button type="submit" class="btn btn-primary btn-sm"> {{ __('Resend Verification Email') }}
                            </button>
                        </form>
                    </div>
                    <div class="col-auto">
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
