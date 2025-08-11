@php
    use App\Config\Constants\{
        DatabaseConstants,
        ExtendingLayoutsConstants,
        SettingsConstants,
        StacksConstants,
        ViewClassNamesConstants,
        YieldingConstants,
    };
    use App\Models\Utility;
    use Modules\LandingPage\Config\Constants\{
        ExtendingLandingPageLayoutConstants as E, 
        RoutesResourcesConstants as R
    };
    use Illuminate\Support\Facades\{Log, Route};
	$data??=[];
	$lang??='';
	$logo??='';
	$logo_dark??='';
	$logo_light??='';
	$company_favicon??='';
	$color??='';
	$colorSettings??=[];
	$mode_setting??='';
	$meta_image??='';
	$siteRtl??=false;
	try {
		$data=Utility::prepareCommonViewData()?:[];
		$lang=$data[SettingsConstants::LCL]??DatabaseConstants::DEFAULT_LANG;
		$logo=$data[SettingsConstants::LOGO]??'';
		$logo_light=Utility::getValByName('logo_light')?:'';
		$logo_dark=Utility::getValByName('logo_dark')?:'';
		$company_favicon=$data[SettingsConstants::FAV_ICN]??'';
		$colorSettings=$data[SettingsConstants::CLR_STG]??[];
		$mode_setting=$data[SettingsConstants::MD_LO]??'';
		$color=$data[SettingsConstants::THM_CLR]??'';
		$siteRtl=$data[SettingsConstants::RTL]??false;
		$meta_image=$data[SettingsConstants::MT_IMG_K]??'';
	} catch (\Error $e) {
		Log::error(
			'Error fetching common view data',
			[
				'exception_class'=>get_class($e),
				'message'=>$e->getMessage(),
				'file'=>$e->getFile(),
				'line'=>$e->getLine()
			]
		);
	} catch (\Exception $e) {
		Log::error(
			'Exception fetching common view data',
			[
				'exception_class'=>get_class($e),
				'message'=>$e->getMessage(),
				'file'=>$e->getFile(),
				'line'=>$e->getLine()
			]
		);
	} catch (\Throwable $e) {
		Log::error(
			'Throwable fetching common view data',
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
    <script>
        function summernote() {
            if ($(".summernote-simple").length) {
                $('.summernote-simple').summernote({
                    dialogsInBody: !0,
                    minHeight: 200,
                    maxHeight: 300,
                    toolbar: [
                        ['style', ['style']],
                        ["font", ["bold", "italic", "underline", "clear", "strikethrough"]],
                        ['fontname', ['fontname']],
                        ['color', ['color']],
                        ["para", ["ul", "ol", "paragraph"]],
                    ],

                });
            }
        }
    </script>
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
                        <div class="{{ ViewClassNamesConstants::CD }}">
                            <div class="card-header">
                                <div class="row">
                                    <div class="{{ ViewClassNamesConstants::CLMS10 }}">
                                        <h5>{{ __('Footer') }}</h5>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                </div>
                            </div>
                            <div class="card-footer text-end">
                                <input class="{{ ViewClassNamesConstants::BT_PR_PRM10 }}" type="submit" value="{{ __('Save Changes') }}">
                            </div>
                        </div>
                    {{--  End for all settings tab --}}
                </div>
            </div>
        </div>
    </div>
@endsection

