@php
	use App\Config\Constants\{DatabaseConstants,
		ExtendingLayoutsConstants,SettingsConstants,
		ViewClassNamesConstants,
		StacksConstants,YieldingConstants};
	use App\Models\Utility;
	use Illuminate\Support\Facades\Log;
	use Symfony\Component\Console\Output\ConsoleOutput;
	$filePath??='';
	$settings??=[];
	$logo??='';
	$languages??=[DatabaseConstants::DEFAULT_LANG];
	$company_logo??='';
	try {
		$settings=Utility::settings()?:[];
		$logo=Utility::getFile()?:'';
		$languages=Utility::languages()?:[DatabaseConstants::DEFAULT_LANG];
		$company_logo=Utility::getValByName(
			SettingsConstants::CPN_LG
		)?:'';
		$filePath=collect(
			array_column(
				debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS),
				'file'
			)
		)->first(fn($p)=>str_ends_with($p,'.blade.php'))??'';
		Log::debug(
			"Rendering Confirm Password Blade ({$filePath})",
			[
				'route'=>request()?->getRequestUri()??'Undefined URI',
				'user'=>optional(auth()->user())->id??'Unidentified User'
			]
		);
		(new ConsoleOutput)
			->writeln(
				"Rendering Confirm Password Blade ({$filePath}) for "
				.(request()?->getRequestUri()??'Undefined URI')
			);
	} catch (\Error $e) {
		Log::error(
			'Error fetching data for Confirm Password Blade',
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
			'Exception fetching data for Confirm Password Blade',
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
			'Throwable fetching data for Confirm Password Blade',
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
    {{__('Confirm Password')}}
@endsection
@section(YieldingConstants::AUTH_CTT)
<div class="card-body">
    <div class="">
        <h2 class="{{ ViewClassNamesConstants::MB3_FW600 }}">{{__('Confirm Password')}}</h2>
        <p class="{{ ViewClassNamesConstants::MB4_TXMT }}">
            {{__(' Please confirm your password before continuing.')}}
        </p>
    </div>
    <form method="POST" action="{{ route('password.confirm') }}">
        @csrf
        <div class="">
            <div class="{{ ViewClassNamesConstants::FM_GB3 }}">
                <label for="password" class="form-label">{{ __('Password') }}</label>
                <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="current-password">
                @error('password')
                <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                @enderror
            </div>
            <div class="d-grid">
                <button type="submit" class="btn-login btn btn-primary btn-block mt-2">{{ __('Confirm Password') }}</button>
            </div>
            @if (Illuminate\Support\Facades\Route::has('password.request'))
                <p class="my-4 text-center">{{__("OR")}} <a href="{{ route('password.request') }}" class="text-primary">{{__('Forgot Your Password?')}}</a></p>
            @endif
        </div>
    </form>
</div>
@endsection
