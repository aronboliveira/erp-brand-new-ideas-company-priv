@php
$lpSettings ??= [];
	$logo ??= '';
	try {
		$lpSettings=\Modules\LandingPage\Entities\LandingPageSetting::settings()?:[];
		$logo=\App\Models\Utility::getFile('uploads/landing_page_image')?:'';
	} catch (\Error $e) {
		Log::error(
			'Error fetching landing page settings/logo',
			[
				'exception_class'=>get_class($e),
				'message'=>$e->getMessage(),
				'file'=>$e->getFile(),
				'line'=>$e->getLine()
			]
		);
	} catch (\Exception $e) {
		Log::error(
			'Exception fetching landing page settings/logo',
			[
				'exception_class'=>get_class($e),
				'message'=>$e->getMessage(),
				'file'=>$e->getFile(),
				'line'=>$e->getLine()
			]
		);
	} catch (\Throwable $e) {
		Log::error(
			'Throwable fetching landing page settings/logo',
			[
				'exception_class'=>get_class($e),
				'message'=>$e->getMessage(),
				'file'=>$e->getFile(),
				'line'=>$e->getLine()
			]
		);
	}
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@section(YieldingConstants::ADM_PG_TTL)
    {{ __('Landing Page') }}
@endsection
@section(YieldingConstants::ADM_BDC)
    <li class="{{ VC::BCI }}">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
        {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="{{ VC::BCI }}">
        {{ __('Landing Page') }}
    </li>
@endsection
@push(StacksConstants::ADM_SCR_PG)
		<script async src="{{ asset('assets/js/admin/error/lang/change.js') }}"></script>
    <script defer src="{{ asset('assets/js/admin/error/change.js') }}"></script>
@endpush

@section(YieldingConstants::ADM_BDC)
    <li class="{{ VC::BCI }}">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
        {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="{{ VC::BCI }}">
        {{ __('Landing Page') }}
    </li>
@endsection

@section(YieldingConstants::ADM_CTT)
    <div class="row">
        <div class="{{ VC::CS12 }}">
            <div class="row">
                <div class="{{ VC::CXL3 }}">
                    <div class="{{ ViewClassNamesConstants::CD_STK }}" style="top:30px">
                        <div class="{{ ViewClassNamesConstants::LG_FLSH }}" id="useradd-sidenav">
                            @include(R::LP.'::'.E::LOS.'.tab')
                        </div>
                    </div>
                </div>
                <div class="col-xl-9">
									@if ($errors->any())
										<div class="{{ VC::ALT_DNG }}">
												<h4 class="alert-heading">{{ __('Whoops! Something went wrong.') }}</h4>
												<ul class="{{ VC::MB0 }}">
													@if ($errors->any())
														@foreach ($errors->all() as $error)
																<li>{{ $error }}</li>
														@endforeach
													@else
																<li>{{ __('Could not retrieve the specific error. Please check for entry data errors.') }}</li>
													@endif
												</ul>
										</div>
									@endif
                </div>
            </div>
        </div>
    </div>
@endsection
