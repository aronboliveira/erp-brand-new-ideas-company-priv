@php

	$lang??='';
	$logo??='';
	$logo_light??='';
	$logo_dark??='';
	$company_favicon??='';
	$data??=[];
	$colorSettings??=[];
	$color??='';
	$siteRtl??='off';
	$meta_image??='';
	try {
		$lang=Utility::getValByName(SettingsConstants::DEF_LNG)?:DatabaseConstants::DEFAULT_LANG;
		$logo=Utility::getFile('uploads/logo')?:'';
		$logo_light=Utility::getValByName('logo_light')?:'';
		$logo_dark=Utility::getValByName('logo_dark')?:'';
		$company_favicon=Utility::getValByName(SettingsConstants::CPN_FAVICON_K)?:'';
		$data=Utility::prepareCommonViewData()?:[];
		$colorSettings=Utility::colorset()?:[];
		$color=!empty($colorSettings[SettingsConstants::CLR])?$colorSettings[SettingsConstants::CLR]:'theme-3';
		$siteRtl=isset($colorSettings[SettingsConstants::RTL])?$colorSettings[SettingsConstants::RTL]:'off';
		$meta_image=Utility::getFile('uploads/meta/')?:'';
	} catch (\Error $e) {
		Log::error(
			'Error fetching theme settings',
			[
				'exception_class'=>get_class($e),
				'message'=>$e->getMessage(),
				'file'=>$e->getFile(),
				'line'=>$e->getLine()
			]
		);
	} catch (\Exception $e) {
		Log::error(
			'Exception fetching theme settings',
			[
				'exception_class'=>get_class($e),
				'message'=>$e->getMessage(),
				'file'=>$e->getFile(),
				'line'=>$e->getLine()
			]
		);
	} catch (\Throwable $e) {
		Log::error(
			'Throwable fetching theme settings',
			[
				'exception_class'=>get_class($e),
				'message'=>$e->getMessage(),
				'file'=>$e->getFile(),
				'line'=>$e->getLine()
			]
		);
	}
    $data = Utility::fallbackSettings($data);
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

@push(StacksConstants::ADM_SCR_PG)
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
                    <div class="card">
                        <div class="card-header">
                            <div class="row">
                                <div class="{{ ViewClassNamesConstants::CLMS10 }}">
                                    <h5>{{ __('Plan Section') }}</h5>
                                </div>
                            </div>
                        </div>
                        {{ Collective\Html\FormFacade::open(array('route' => R::PRC_PLN.'.store', 'method'=>'post', 'enctype' => "multipart/form-data")) }}
                            <div class="card-body">
                                <div class="row">

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            {{ Collective\Html\FormFacade::label('Title', __('Title'), ['class' => 'form-label']) }}
                                            {{ Collective\Html\FormFacade::text(LPSC::PN_TTL_K,!empty($data[LPSC::PN_TTL_K]) ? $data[LPSC::PN_TTL_K] : '# ERROR: COULD NOT FIND PLAN TITLE', ['class' => 'form-control', 'placeholder' => __('Enter Title')]) }}
                                            @error('mail_host')
                                            <span class="invalid-mail_driver" role="alert">
                                                    <strong class="text-danger">{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            {{ Collective\Html\FormFacade::label('Heading', __('Heading'), ['class' => 'form-label']) }}
                                            {{ Collective\Html\FormFacade::text(LPSC::PN_HDG_K,!empty($data[LPSC::PN_HDG_K]) ? $data[LPSC::PN_HDG_K] : '# ERROR: COULD NOT FIND PLAN HEADING', ['class' => 'form-control', 'placeholder' => __('Enter Heading')]) }}
                                            @error('mail_host')
                                            <span class="invalid-mail_driver" role="alert">
                                                    <strong class="text-danger">{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <div class="form-group">
                                            {{ Collective\Html\FormFacade::label('Description', __('Description'), ['class' => 'form-label']) }}
                                            {{ Collective\Html\FormFacade::text(LPSC::PN_DESC_K, !empty($data[LPSC::PN_DESC_K]) ? $data[LPSC::PN_DESC_K] : '# ERROR: COULD NOT FIND PLAN DESCRIPTION', ['class' => 'form-control', 'placeholder' => __('Enter Description')]) }}
                                            @error('mail_port')
                                            <span class="invalid-mail_port" role="alert">
                                                    <strong class="text-danger">{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>

                                </div>
                            </div>
                            <div class="card-footer text-end">
                                <input class="{{ ViewClassNamesConstants::BT_PR_PRM10 }}" type="submit" value="{{ __('Save Changes') }}">
                            </div>
                        {{ Collective\Html\FormFacade::close() }}
                    </div>
                    {{--  End for all settings tab --}}
                </div>
            </div>
        </div>
    </div>
@endsection
