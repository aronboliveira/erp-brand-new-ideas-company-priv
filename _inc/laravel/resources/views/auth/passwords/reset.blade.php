@php
	use App\Config\Constants\{
		DatabaseConstants,
		ExtendingLayoutsConstants,
		StacksConstants,SettingsConstants,
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
			"Rendering Reset Password Blade ({$filePath})",
			['route'=>request()?->getRequestUri()??'Undefined URI',
			 'user'=>optional(auth()->user())->id??'Unidentified User']
		);
		(new ConsoleOutput)
			->writeln(
				"Rendering Reset Password Blade ({$filePath}) for "
				.(request()?->getRequestUri()??'Undefined URI')
			);
	} catch (\Error $e) {
		Log::error(
			'Error fetching data for Reset Password Blade',
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
			'Exception fetching data for Reset Password Blade',
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
			'Throwable fetching data for Reset Password Blade',
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
@endsection
@section(YieldingConstants::AUTH_CTT)
    <div class="card-body">
        <div>
            <h2 class="{{ ViewClassNamesConstants::MB3_FW600 }}"><span class="text-primary">{{ __('Reset Password!') }}</span></h2>
            {{-- <p>{{ __('Sign in by entering the information below?') }} </p> --}}
        </div>
    {{Collective\Html\FormFacade::open(array('route'=>'password.update','method'=>'post','id'=>'loginForm'))}}
    <input type="hidden" name="token" value="{{ $request->route('token') }}">
    <div class="">
        <div class="{{ ViewClassNamesConstants::FM_GB3 }}">
            {{Collective\Html\FormFacade::label('email',__('E-Mail Address'),['class'=>'form-label'])}}
            {{Collective\Html\FormFacade::text('email',null,array('class'=>'form-control'))}}
            @error('email')
            <span class="invalid-email text-danger" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
            @enderror
        </div>
        <div class="{{ ViewClassNamesConstants::FM_GB3 }}">
            {{Collective\Html\FormFacade::label('password',__('Password'),['class'=>'form-label'])}}
            {{Collective\Html\FormFacade::password('password',array('class'=>'form-control'))}}
            @error('password')
            <span class="invalid-password text-danger" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
            @enderror
        </div>
        <div class="{{ ViewClassNamesConstants::FM_GB3 }}">
            {{Collective\Html\FormFacade::label('password_confirmation',__('Password Confirmation'),['class'=>'form-label'])}}
            {{Collective\Html\FormFacade::password('password_confirmation',array('class'=>'form-control'))}}
            @error('password_confirmation')
            <span class="invalid-password_confirmation text-danger" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
            @enderror
        </div>
        <div class="d-grid">
            {{Collective\Html\FormFacade::submit(__('Reset'),array('class'=>'btn btn-primary btn-block mt-2','id'=>'resetBtn'))}}
        </div>

    </div>

    {{Collective\Html\FormFacade::close()}}
</div>

@endsection
