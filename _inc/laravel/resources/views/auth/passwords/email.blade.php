@php
$filePath ??= '';
	$settings ??= [];
	$logo ??= '';
	$languages ??= [DatabaseConstants::DEFAULT_LANG];
	$company_logo ??= '';

	try {
		$settings = Utility::settings() ?: [];
		$logo = Utility::getFile() ?: '';
		$languages = Utility::languages() ?: [DatabaseConstants::DEFAULT_LANG];
		$company_logo = Utility::getValByName(SettingsConstants::CPN_LG) ?: '';
		$filePath = collect(array_column(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS), 'file'))
			->first(fn ($p) => str_ends_with($p, '.blade.php')) ?? '';
	} catch (\Error $e) {
		Log::error('Error fetching data for Email Password Blade', [
			'exception_class' => get_class($e),
			'message'         => $e->getMessage(),
			'file'            => $e->getFile(),
			'line'            => $e->getLine(),
			'blade'           => $filePath,
		]);
	} catch (\Exception $e) {
		Log::error('Exception fetching data for Email Password Blade', [
			'exception_class' => get_class($e),
			'message'         => $e->getMessage(),
			'file'            => $e->getFile(),
			'line'            => $e->getLine(),
			'blade'           => $filePath,
		]);
	} catch (\Throwable $e) {
		Log::error('Throwable fetching data for Email Password Blade', [
			'exception_class' => get_class($e),
			'message'         => $e->getMessage(),
			'file'            => $e->getFile(),
			'line'            => $e->getLine(),
			'blade'           => $filePath,
		]);
	}

	$lang = Utility::fetchUserLang();

	$formId = 'password-email-form';
	$passwordEmailBase = 'password.email';
	$passwordEmailResolved = null;
	$passwordEmailUrl = '#';
	$passwordEmailGuardMsg = Utility::fetchLinkMessage($lang, 'auth', 'email_password_route_unavailable') ?? 'Send password reset link route is unavailable. Please contact technical support or your domain administrator.';

	$loginBase = 'login';
	$loginResolved = null;
	$loginUrl = '#';
	$loginGuardMsg = Utility::fetchLinkMessage($lang, 'auth', 'login_route_unavailable') ?? 'Login route is unavailable. Please contact technical support or your domain administrator.';

	try {
		$passwordEmailResolved = Route::has($passwordEmailBase)
			? $passwordEmailBase
			: (Route::has(Str::kebab($passwordEmailBase)) ? Str::kebab($passwordEmailBase) : null);
	} catch (\Throwable $e) {
		Log::error('Email Password Blade: resolving password.email failed: ' . $e->getMessage());
	}
	try {
		$passwordEmailUrl = $passwordEmailResolved ? route($passwordEmailResolved) : '#';
	} catch (\Throwable $e) {
		Log::error('Email Password Blade: generating URL for password.email failed: ' . $e->getMessage());
		$passwordEmailUrl = '#';
	}

	try {
		$loginResolved = Route::has($loginBase)
			? $loginBase
			: (Route::has(Str::kebab($loginBase)) ? Str::kebab($loginBase) : null);
	} catch (\Throwable $e) {
		Log::error('Email Password Blade: resolving login failed: ' . $e->getMessage());
	}
	try {
		$loginUrl = $loginResolved ? route($loginResolved) : '#';
	} catch (\Throwable $e) {
		Log::error('Email Password Blade: generating URL for login failed: ' . $e->getMessage());
		$loginUrl = '#';
	}

	$languageSelectId = 'language';
	$langGuardMsg = Utility::fetchLinkMessage($lang, 'auth', 'language_switch_unavailable') ?? 'Language switch route is unavailable. Please contact technical support or your domain administrator.';
@endphp

@extends(ExtendingLayoutsConstants::AUTH)

@section(YieldingConstants::AUTH_PG_TTL)
	{{ __('Forgot Password') }}
@endsection

@section(YieldingConstants::AUTH_TB)
	<li class="{{ VC::NV_IT }}">
		<select class="{{ VC::BT_PM }} my-1 me-2"
				onchange="this.options[this.selectedIndex].value && (window.location = this.options[this.selectedIndex].value);"
				id="{{ $languageSelectId }}"
				data-guard-msg="{{ base64_encode($langGuardMsg) }}"
				data-sv-localized="true">
			@foreach($languages as $language)
				@php
					$optUrl ??= '#';
					try {
						$optUrl = $loginResolved ? route($loginResolved, $language) : '#';
					} catch (\Throwable $e) {
						Log::error('Email Password Blade: generating URL for login with language failed: ' . $e->getMessage());
						$optUrl = '#';
					}
@endphp
				<option value="{{ $optUrl }}" {{ $lang === $language ? 'selected' : '' }}>{{ ucfirst($language) }}</option>
			@endforeach
		</select>
	</li>
@endsection

@section(YieldingConstants::AUTH_CTT)
	<div>
		<h2 class="{{ VC::MB3_FW600 }}">{{ __('Forgot Password') }}</h2>
		<p class="{{ VC::MB4_TXMT }}">{{ __('We will send a link to reset your password.') }}</p>
		@if (session('status'))
			<p class="{{ VC::MB4_TXMT }}">{{ session('status') }}</p>
		@endif
	</div>

	{{ Form::open([
		'url'                  => $passwordEmailUrl,
		'method'               => 'post',
		'id'                   => $formId,
		'data-resolved-action' => $passwordEmailUrl,
		'data-guard-msg'       => $passwordEmailGuardMsg,
		'data-sv-localized'    => 'true',
	]) }}
		@csrf
		<div>
			<div class="{{ VC::FM_GB3 }}">
				<label class="{{ VC::FM_LB }}" for="email">{{ __('E-Mail Address') }}</label>
				<input id="email" type="email" class="{{ VC::FM_CT }} @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus>
				@error('email')
					<span class="{{ VC::INV_FB }}" role="alert"><strong>{{ $message }}</strong></span>
				@enderror
			</div>
			<div class="{{ VC::D_GR }}">
				<button type="submit" class="{{ VC::BT_PRM }} btn-block mt-2">{{ __('Send Password Reset Link') }}</button>
			</div>
		</div>
		<p class="{{ VC::MY4 }}">
			{{ __('OR') }}
			<a href="{{ $loginUrl }}"
			   class="f-w-400 {{ VC::TX_PM }} auth-login-link"
			   data-url="{{ $loginUrl }}"
			   data-guard-msg="{{ base64_encode($loginGuardMsg) }}"
			   data-sv-localized="true">{{ __('Signin') }}</a>
		</p>
	{{ Form::close() }}
@endsection

@push(StacksConstants::AUTH_CST_SCR)
	<script defer src="{{ asset('assets/js/routes/auth/passwords/email.js') }}"></script>
	<script defer src="{{ asset('assets/js/routes/auth/passwords/link.js') }}"></script>
	<script defer src="{{ asset('assets/js/routes/auth/languageSwitcher.js') }}"></script>
@endpush
