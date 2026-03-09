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
		Log::error('Error fetching data for Confirm Password Blade', [
			'exception_class' => get_class($e),
			'message'         => $e->getMessage(),
			'file'            => $e->getFile(),
			'line'            => $e->getLine(),
			'blade'           => $filePath,
		]);
	} catch (\Exception $e) {
		Log::error('Exception fetching data for Confirm Password Blade', [
			'exception_class' => get_class($e),
			'message'         => $e->getMessage(),
			'file'            => $e->getFile(),
			'line'            => $e->getLine(),
			'blade'           => $filePath,
		]);
	} catch (\Throwable $e) {
		Log::error('Throwable fetching data for Confirm Password Blade', [
			'exception_class' => get_class($e),
			'message'         => $e->getMessage(),
			'file'            => $e->getFile(),
			'line'            => $e->getLine(),
			'blade'           => $filePath,
		]);
	}

	$lang = Utility::fetchUserLang();

	$formId = 'confirm-password-form';
	$confirmBase = 'password.confirm';
	$confirmResolved = null;
	$confirmUrl = '#';
	$confirmGuardMsg = Utility::fetchLinkMessage($lang, 'auth', 'confirm_password_route_unavailable') ?? 'Confirm password route is unavailable. Please contact technical support or your domain administrator.';

	$forgotBase = 'password.request';
	$forgotResolved = null;
	$forgotUrl = '#';
	$forgotGuardMsg = Utility::fetchLinkMessage($lang, 'auth', 'forgot_password_route_unavailable') ?? 'Forgot password route is unavailable. Please contact technical support or your domain administrator.';

	try {
		$confirmResolved = Route::has($confirmBase)
			? $confirmBase
			: (Route::has(Str::kebab($confirmBase)) ? Str::kebab($confirmBase) : null);
	} catch (\Throwable $e) {
		Log::error('Confirm Password Blade: resolving password.confirm failed: ' . $e->getMessage());
	}
	try {
		$confirmUrl = $confirmResolved ? route($confirmResolved) : '#';
	} catch (\Throwable $e) {
		Log::error('Confirm Password Blade: generating URL for password.confirm failed: ' . $e->getMessage());
		$confirmUrl = '#';
	}

	try {
		$forgotResolved = Route::has($forgotBase)
			? $forgotBase
			: (Route::has(Str::kebab($forgotBase)) ? Str::kebab($forgotBase) : null);
	} catch (\Throwable $e) {
		Log::error('Confirm Password Blade: resolving password.request failed: ' . $e->getMessage());
	}
	try {
		$forgotUrl = $forgotResolved ? route($forgotResolved) : '#';
	} catch (\Throwable $e) {
		Log::error('Confirm Password Blade: generating URL for password.request failed: ' . $e->getMessage());
		$forgotUrl = '#';
	}
@endphp

@extends(ExtendingLayoutsConstants::AUTH)

@section(YieldingConstants::AUTH_PG_TTL)
	{{ __('Confirm Password') }}
@endsection

@section(YieldingConstants::AUTH_CTT)
	<div class="{{ VC::CD_BD }}">
		<div>
			<h2 class="{{ VC::MB3_FW600 }}">{{ __('Confirm Password') }}</h2>
			<p class="{{ VC::MB4_TXMT }}">{{ __(' Please confirm your password before continuing.') }}</p>
		</div>

		{{ Collective\Html\FormFacade::open([
			'url'                  => $confirmUrl,
			'method'               => 'post',
			'id'                   => $formId,
			'data-resolved-action' => $confirmUrl,
			'data-guard-msg'       => $confirmGuardMsg,
			'data-sv-localized'    => 'true',
		]) }}
			@csrf
			<div>
				<div class="{{ VC::FM_GB3 }}">
					<label for="password" class="{{ VC::FM_LB }}">{{ __('Password') }}</label>
					<input id="password" type="password" class="{{ VC::FM_CT }} @error('password') is-invalid @enderror" name="password" required autocomplete="current-password">
					@error('password')
						<span class="{{ VC::INV_FB }}" role="alert">
							<strong>{{ $message }}</strong>
						</span>
					@enderror
				</div>

				<div class="{{ VC::D_GR }}">
					<button type="submit" class="btn-login {{ VC::BT_PRM }} btn-block mt-2">{{ __('Confirm Password') }}</button>
				</div>

				@if ($forgotResolved)
					<p class="{{ VC::MY4_TXCT }}">
						{{ __('OR') }}
						<a href="{{ $forgotUrl }}"
						   class="{{ VC::TX_PM }} auth-forgot-link"
						   data-url="{{ $forgotUrl }}"
						   data-guard-msg="{{ base64_encode($forgotGuardMsg) }}"
						   data-sv-localized="true">{{ __('Forgot Your Password?') }}</a>
					</p>
				@else
					<p class="{{ VC::MY4_TXCT }}">
						{{ __('OR') }}
						<a href="#"
						   class="{{ VC::TX_PM }} auth-forgot-link"
						   data-url="#"
						   data-guard-msg="{{ base64_encode($forgotGuardMsg) }}"
						   data-sv-localized="true">{{ __('Forgot Your Password?') }}</a>
					</p>
				@endif
			</div>
		{{ Collective\Html\FormFacade::close() }}
	</div>
@endsection

@push(StacksConstants::AUTH_CST_SCR)
	<script defer src="{{ asset('assets/js/routes/auth/passwords/confirm.js') }}"></script>
	<script defer src="{{ asset('assets/js/routes/auth/passwords/forgot.js') }}"></script>
@endpush
