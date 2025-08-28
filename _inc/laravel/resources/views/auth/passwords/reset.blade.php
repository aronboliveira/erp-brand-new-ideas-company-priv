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
	use Collective\Html\FormFacade as Form;
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
			->first(fn($p) => str_ends_with($p, '.blade.php')) ?? '';
		Log::debug("Rendering Reset Password Blade ({$filePath})", [
			'route' => request()?->getRequestUri() ?? 'Undefined URI',
			'user'  => optional(auth()->user())->id ?? 'Unidentified User',
		]);
		(new ConsoleOutput)->writeln("Rendering Reset Password Blade ({$filePath}) for " . (request()?->getRequestUri() ?? 'Undefined URI'));
	} catch (\Error $e) {
		Log::error('Error fetching data for Reset Password Blade', [
			'exception_class' => get_class($e),
			'message'         => $e->getMessage(),
			'file'            => $e->getFile(),
			'line'            => $e->getLine(),
			'blade'           => $filePath,
		]);
	} catch (\Exception $e) {
		Log::error('Exception fetching data for Reset Password Blade', [
			'exception_class' => get_class($e),
			'message'         => $e->getMessage(),
			'file'            => $e->getFile(),
			'line'            => $e->getLine(),
			'blade'           => $filePath,
		]);
	} catch (\Throwable $e) {
		Log::error('Throwable fetching data for Reset Password Blade', [
			'exception_class' => get_class($e),
			'message'         => $e->getMessage(),
			'file'            => $e->getFile(),
			'line'            => $e->getLine(),
			'blade'           => $filePath,
		]);
	}

	$lang = Utility::fetchUserLang();

	$formId = 'reset-password-form';
	$resetBase = 'password.update';
	$resetResolved = null;
	$resetUrl = '#';
	$resetGuardMsg = Utility::fetchLinkMessage($lang, 'auth', 'reset_password_route_unavailable') ?? 'Reset password route is unavailable. Please contact technical support or your domain administrator.';

	try {
		$resetResolved = Route::has($resetBase)
			? $resetBase
			: (Route::has(Str::kebab($resetBase)) ? Str::kebab($resetBase) : null);
	} catch (\Throwable $e) {
		Log::error('Reset Password Blade: resolving password.update failed: ' . $e->getMessage());
	}
	try {
		$resetUrl = $resetResolved ? route($resetResolved) : '#';
	} catch (\Throwable $e) {
		Log::error('Reset Password Blade: generating URL for password.update failed: ' . $e->getMessage());
		$resetUrl = '#';
	}

	$tokenValue = request()?->route('token') ?? '';
@endphp

@extends(ExtendingLayoutsConstants::AUTH)

@section(YieldingConstants::AUTH_PG_TTL)
	{{ __('Forgot Password') }}
@endsection

@section(YieldingConstants::AUTH_TB)
@endsection

@section(YieldingConstants::AUTH_CTT)
	<div class="card-body">
		<div>
			<h2 class="{{ VC::MB3_FW600 }}"><span class="text-primary">{{ __('Reset Password!') }}</span></h2>
		</div>

		{{ Form::open([
			'url'                  => $resetUrl,
			'method'               => 'post',
			'id'                   => $formId,
			'data-resolved-action' => $resetUrl,
			'data-guard-msg'       => $resetGuardMsg,
			'data-sv-localized'    => 'true',
		]) }}
			@csrf
			<input type="hidden" name="token" value="{{ $tokenValue }}">
			<div>
				<div class="{{ VC::FM_GB3 }}">
					{{ Form::label('email', __('E-Mail Address'), ['class' => VC::FM_LB]) }}
					{{ Form::text('email', old('email'), ['class' => VC::FM_CT]) }}
					@error('email')
						<span class="invalid-email text-danger" role="alert"><strong>{{ $message }}</strong></span>
					@enderror
				</div>

				<div class="{{ VC::FM_GB3 }}">
					{{ Form::label('password', __('Password'), ['class' => VC::FM_LB]) }}
					{{ Form::password('password', ['class' => VC::FM_CT]) }}
					@error('password')
						<span class="invalid-password text-danger" role="alert"><strong>{{ $message }}</strong></span>
					@enderror
				</div>

				<div class="{{ VC::FM_GB3 }}">
					{{ Form::label('password_confirmation', __('Password Confirmation'), ['class' => VC::FM_LB]) }}
					{{ Form::password('password_confirmation', ['class' => VC::FM_CT]) }}
					@error('password_confirmation')
						<span class="invalid-password_confirmation text-danger" role="alert"><strong>{{ $message }}</strong></span>
					@enderror
				</div>

				<div class="d-grid">
					{{ Form::submit(__('Reset'), ['class' => VC::BT_PRM . ' btn-block mt-2', 'id' => 'resetBtn']) }}
				</div>
			</div>
		{{ Form::close() }}
	</div>
@endsection

@push(StacksConstants::AUTH_CST_SCR)
	<script defer src="{{ asset('assets/js/routes/auth/passwords/reset.js') }}"></script>
@endpush

		
		{{-- <p>{{ __('Sign in by entering the information below?') }} </p> --}}