@php
	use App\Config\Constants\{ExtendingLayoutsConstants,SettingsConstants,StacksConstants,ViewClassNamesConstants,YieldingConstants};
	use App\Models\Utility;
	use Illuminate\Support\Facades\{Log,Route};
	use Modules\LandingPage\Config\Constants\{ExtendingLandingPageLayoutConstants as E,SettingsConstants as LandingPageSettingsConstants,RoutesResourcesConstants as R};
    use Nwidart\Modules\Facades\Module;
    
	$logo ??= '';
	$lpSettings ??= [];
	try {
		$logo = Utility::getFile('uploads/logo') ?: '';
		$lpSettings = \Modules\LandingPage\Entities\LandingPageSetting::landingPageSetting() ?: [];
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
    <li class="breadcrumb-item">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
        {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="breadcrumb-item">
        {{ __('Landing Page') }}
    </li>
@endsection
@push(StacksConstants::ADM_CSS)
    <link rel="stylesheet" href=" {{ asset('Modules/landingpage/css/summernote/summernote-bs4.css')}}" />
@endpush

@push(StacksConstants::ADM_SCR_PG)
    <script src="{{ asset('Modules/landingpage/js/plugins/summernote-bs4.js')}}" referrerpolicy="origin"></script>
@endpush

@section(YieldingConstants::ADM_BDC)
    <li class="breadcrumb-item">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
        {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="breadcrumb-item">
        {{ __('Landing Page') }}
    </li>
@endsection

@section(YieldingConstants::ADM_CTT)
    <div class="row">
        <div class="col-sm-12">
            <div class="row">
                <div class="col-xl-3">
                    <div class="{{ ViewClassNamesConstants::CD_STK }}" style="top:30px">
                        <div class="{{ ViewClassNamesConstants::LG_FLSH }}" id="useradd-sidenav">
                            @include(R::LP.'::'.E::LOS.'.tab')
                        </div>
                    </div>
                </div>
                <div class="col-xl-9">
                {{--  Start for all settings tab --}}
                    {{Collective\Html\FormFacade::model(null, array('route' => array('landingpage.store'), 'method' => 'POST')) }}
                    @csrf
                        <div class="card">
                            <div class="card-header">
                                <div class="row align-items-center">
                                    <div class="col-6">
                                        <h5 class="mb-2">{{ __('Top Bar') }}</h5>
                                    </div>
                                    <div class="col switch-width text-end">
                                        <div class="form-group mb-0">
                                            <div class="custom-control custom-switch">
                                                <input type="checkbox" data-toggle="switchbutton" data-onstyle="primary" class="" name="topbar_status"
                                                    id="topbar_status" {{ !empty($lpSettings[LandingPageSettingsConstants::TB_STT_K]) && $lpSettings[LandingPageSettingsConstants::TB_STT_K] === 'on' ? 'checked="checked"' : '' }}>
                                                <label class="custom-control-label" for="topbar_status"></label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="form-group col-12">
                                        {{ Collective\Html\FormFacade::label('content', __('Message'), ['class' => 'col-form-label text-dark']) }}
                                        {{ Collective\Html\FormFacade::textarea(LandingPageSettingsConstants::TB_NTF_MSG_K,$lpSettings[LandingPageSettingsConstants::TB_NTF_MSG_K], ['class' => 'summernote-simple form-control', 'required' => 'required']) }}
                                    </div>

                                </div>
                            </div>
                            <div class="card-footer text-end">
                                <input class="{{ ViewClassNamesConstants::BT_PR_PRM10 }}" type="submit" value="{{ __('Save Changes') }}">
                            </div>
                        </div>
                    {{ Collective\Html\FormFacade::close() }}
                {{--  End for all settings tab --}}
                </div>
            </div>
        </div>
    </div>
@endsection



