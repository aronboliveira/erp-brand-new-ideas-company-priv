@php
	use App\Config\Constants\{
		DatabaseConstants,
		ExtendingLayoutsConstants,SettingsConstants,
		StacksConstants,
		ViewClassNamesConstants, YieldingConstants};
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
		$company_logo=Utility::getValByName(SettingsConstants::CPN_LG)?:'';
		$filePath=collect(
			array_column(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS),'file')
		)->first(fn($p)=>str_ends_with($p,'.blade.php'))??'';
		Log::debug(
			"Rendering Email Password Blade ({$filePath})",
			[
				'route'=>request()?->getRequestUri()??'Undefined URI',
				'user'=>optional(auth()->user())->id??'Unidentified User'
			]
		);
		(new ConsoleOutput)
			->writeln(
				"Rendering Email Password Blade ({$filePath}) for "
				.(request()?->getRequestUri()??'Undefined URI')
			);
	} catch (\Error $e) {
		Log::error(
			'Error fetching data for Email Password Blade',
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
			'Exception fetching data for Email Password Blade',
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
			'Throwable fetching data for Email Password Blade',
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
    {{__('Forgot Password')}}
@endsection
@section(YieldingConstants::AUTH_TB)
    <li class="nav-item">
        <select class="btn btn-primary my-1 me-2" onchange="this.options[this.selectedIndex].value && (window.location = this.options[this.selectedIndex].value);" id="language">
            @foreach(Utility::languages() as $language)
                <option class="" @if($lang == $language) selected @endif value="{{ route('login',$language) }}">{{ucfirst($language)}}</option>
            @endforeach
        </select>
    </li>
@endsection
@section(YieldingConstants::AUTH_CTT)
    <div class="">
        <h2 class="{{ ViewClassNamesConstants::MB3_FW600 }}">{{__('Forgot Password')}}</h2>
        <p class="{{ ViewClassNamesConstants::MB4_TXMT }}">
            {{__('We will send a link to reset your password.')}}
        </p>
        @if (session('status'))
            <p class="{{ ViewClassNamesConstants::MB4_TXMT }}">
                {{ session('status') }}
            </p>
        @endif
    </div>
    <form method="POST" action="{{ route('password.email') }}">
        @csrf
        <div class="">
            <div class="{{ ViewClassNamesConstants::FM_GB3 }}">
                <label class="form-label" for="email">{{ __('E-Mail Address') }}</label>
                <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus>
                @error('email')
                <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                @enderror
            </div>
            <div class="d-grid">
                <button type="submit" class="btn btn-primary btn-block mt-2">{{__('Send Password Reset Link')}}</button>
            </div>
        </div>
        <p class="my-4">
            {{__('OR')}}
            <a href="{{ route('login') }}" class="f-w-400 text-primary">{{__('Signin')}}</a>
        </p>
    </form>
@endsection
