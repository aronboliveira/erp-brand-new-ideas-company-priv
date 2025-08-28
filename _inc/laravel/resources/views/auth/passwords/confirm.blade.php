@php
	use App\Config\Constants\{
		DatabaseConstants,
		ExtendingLayoutsConstants,
		SettingsConstants,
		StacksConstants,
		ViewClassNamesConstants as VC,
		YieldingConstants
	};
	use App\Models\Utility;
	use Illuminate\Support\{Facades\Log, Facades\Route, Str};
	use Symfony\Component\Console\Output\ConsoleOutput;

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

		Log::debug('Rendering Confirm Password Blade (' . $filePath . ')', [
			'route' => request()?->getRequestUri() ?? 'Undefined URI',
			'user'  => optional(auth()->user())->id ?? 'Unidentified User',
		]);

		(new ConsoleOutput)->writeln('Rendering Confirm Password Blade (' . $filePath . ') for ' . (request()?->getRequestUri() ?? 'Undefined URI'));
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
	<div class="card-body">
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
						<span class="invalid-feedback" role="alert">
							<strong>{{ $message }}</strong>
						</span>
					@enderror
				</div>

				<div class="d-grid">
					<button type="submit" class="btn-login {{ VC::BT_PRM }} btn-block mt-2">{{ __('Confirm Password') }}</button>
				</div>

				@if ($forgotResolved)
					<p class="my-4 text-center">
						{{ __('OR') }}
						<a href="{{ $forgotUrl }}"
						   class="text-primary auth-forgot-link"
						   data-url="{{ $forgotUrl }}"
						   data-guard-msg="{{ $forgotGuardMsg }}"
						   data-sv-localized="true">{{ __('Forgot Your Password?') }}</a>
					</p>
				@else
					<p class="my-4 text-center">
						{{ __('OR') }}
						<a href="#"
						   class="text-primary auth-forgot-link"
						   data-url="#"
						   data-guard-msg="{{ $forgotGuardMsg }}"
						   data-sv-localized="true">{{ __('Forgot Your Password?') }}</a>
					</p>
				@endif
			</div>
		{{ Collective\Html\FormFacade::close() }}
	</div>
@endsection

@push(StacksConstants::ADM_SCR_PG)
	<script defer src="{{ asset('assets/js/routes/auth/passwords/confirm.js') }}"></script>
	<script defer src="{{ asset('assets/js/routes/auth/passwords/forgot.js') }}"></script>
@endpush
