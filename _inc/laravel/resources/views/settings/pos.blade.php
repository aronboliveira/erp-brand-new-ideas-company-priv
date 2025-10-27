@php
	use App\Config\Constants\{
        SettingsConstants,
        ViewClassNamesConstants as VC
    };
	use App\Models\Utility;
	use Illuminate\Support\Facades\Log;
	$data ??= [];
	$logo ??= '';
	$company_logo ??= '';
	$company_favicon ??= '';
	$lang ??= '';
	try {
		$data = Utility::prepareCommonViewData() ?: [];
		$logo = $data[SettingsConstants::LOGO] ?? '';
		$company_logo = Utility::getValByName(SettingsConstants::CPN_LG) ?: '';
		$company_favicon = $data[SettingsConstants::FAV_ICN] ?? '';
		$lang = Utility::getValByName(SettingsConstants::DEF_LNG) ?: '';
	} catch (\Error $e) {
		Log::error(
			'Error loading header view data',
			[
				'exception_class' => get_class($e),
				'message' => $e->getMessage(),
				'file' => $e->getFile(),
				'line' => $e->getLine()
			]
		);
	} catch (\Exception $e) {
		Log::error(
			'Exception loading header view data',
			[
				'exception_class' => get_class($e),
				'message' => $e->getMessage(),
				'file' => $e->getFile(),
				'line' => $e->getLine()
			]
		);
	} catch (\Throwable $e) {
		Log::error(
			'Throwable loading header view data',
			[
				'exception_class' => get_class($e),
				'message' => $e->getMessage(),
				'file' => $e->getFile(),
				'line' => $e->getLine()
			]
		);
	}
    $data = Utility::fallbackSettings($data);
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@section(YieldingConstants::ADM_PG_TTL)
    {{__('Settings')}}
@endsection
@section(YieldingConstants::ADM_BDC)
    <li class="breadcrumb-item">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
        {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="breadcrumb-item">{{__('Print-Settings')}}</li>
@endsection
@push(StacksConstants::ADM_SCR_PG)
    <script async src="{{ asset('assets/js/routes/settings/pos/lang/purchase.js') }}"></script>
    <script defer src="{{ asset('assets/js/routes/settings/pos/purchase.js') }}"></script>
@endpush
@section(YieldingConstants::ADM_CTT)
    <div class="{{ VC::CS12 }} {{ VC::MT4 }}">
        <div class="{{ VC::CD }}">
            <div class="card-body">
                <ul class="{{ VC::NAV_PL }} {{ VC::MB3 }}" id="pills-tab" role="tablist">
                    <li class="{{ VC::NV_IT }}">
                        <a class="{{ VC::NV_LK }} active"
                        id="pills-purchase-tab"
                        data-bs-toggle="pill"
                        href="#pills-purchase"
                        role="tab"
                        aria-controls="pills-purchase"
                        aria-selected="false">
                            {{ __('Purchase Print Setting') }}
                        </a>
                    </li>
                    <li class="{{ VC::NV_IT }}">
                        <a class="{{ VC::NV_LK }}"
                        id="pills-pos-tab"
                        data-bs-toggle="pill"
                        href="#pills-pos"
                        role="tab"
                        aria-controls="pills-pos"
                        aria-selected="false">
                            {{ __('POS Print Setting') }}
                        </a>
                    </li>
                </ul>
                @php 
                    $templateData = Utility::templateData(); 
                    $templates = $templateData['templates'];
                    $colors = $templateData['colors'];
                @endphp
                <div class="tab-content" id="pills-tabContent">
                    <div class="tab-pane fade show active" id="pills-purchase" role="tabpanel" aria-labelledby="pills-purchase-tab">
                        <div class="bg-none">
                            <div class="{{ VC::RW }} company-setting">
                                <div class="{{ VC::CM3 }}">
                                    <div class="card-body">
                                        <h5></h5>
                                        @php
                                            $prcSettingsBase = ViewsConstants::PRC_TMP.'.settings';
                                            $prcSettingsKebab = Str::kebab($prcSettingsBase);
                                            $prcSettingsResolved = Route::has($prcSettingsBase) ? $prcSettingsBase : (Route::has($prcSettingsKebab) ? $prcSettingsKebab : null);
                                            $prcSettingsUrl = $prcSettingsResolved ? route($prcSettingsResolved) : '#';
                                            $langValue = isset($lang) ? $lang : Utility::fetchUserLang();
                                            $prcSettingsGuardMsg = Utility::fetchLinkMessage($langValue, ViewsConstants::PRC_TMP, 'settings_purchase_template_route_unavailable') ?? 'Purchase template settings route is unavailable. Please contact technical support or your domain administrator.';
                                            $prcTmpFormId = 'prc-settings-form';
                                        @endphp
                                        <form id="{{ $prcTmpFormId }}" method="post" action="{{ $prcSettingsUrl }}" enctype="multipart/form-data" data-url="{{ $prcSettingsUrl }}" data-guard-msg="{{ $prcSettingsGuardMsg }}" data-sv-localized="true">
                                            @csrf
                                            <div class="{{ VC::FM_G }}">
                                                <label for="address" class="{{ VC::FM_LB }}">{{ __('Purchase Template') }}</label>
                                                <select class="{{ VC::FM_CT }}" name="purchase_template">
                                                    @if (Utility::isFilled($templates) ?? [])
                                                        @foreach($templates as $key => $template)
                                                            <option value="{{ $key }}" {{ (isset($settings[BillsConstants::COL_PRC_TMP]) && $settings[BillsConstants::COL_PRC_TMP] == $key) ? 'selected' : '' }}>
                                                                {{ $template }}
                                                            </option>
                                                        @endforeach
                                                    @else
                                                        <option value="" disabled>{{ __('No templates available') }}</option>
                                                    @endif
                                                </select>
                                            </div>
                                            <div class="{{ VC::FM_G }}">
                                                <label class="{{ VC::FM_LB }}">{{ __('Color Input') }}</label>
                                                <div class="{{ VC::RW }} gutters-xs">
                                                    @if (Utility::isFilled($colors) ?? [])
                                                        @foreach($colors as $key => $color)
                                                            <div class="{{ VC::C_AT }}">
                                                                <label class="colorinput">
                                                                    <input name="purchase_color" type="radio" value="{{ $color }}" class="colorinput-input" {{ (isset($settings['purchase_color']) && $settings['purchase_color'] == $color) ? 'checked' : '' }}>
                                                                    <span class="colorinput-color" style="background: #{{ $color }}"></span>
                                                                </label>
                                                            </div>
                                                        @endforeach
                                                    @else
                                                        <div class="{{ VC::C_AT }}">
                                                            <label class="colorinput">
                                                                <input name="purchase_color" type="radio" value="" class="colorinput-input" {{ (isset($settings['purchase_color']) && $settings['purchase_color'] == '') ? 'checked' : '' }}>
                                                                <span class="colorinput-color" style="background: #ffffff"></span>
                                                            </label>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="{{ VC::FM_G }}">
                                                <label class="{{ VC::FM_LB }}">{{ __('Purchase Logo') }}</label>
                                                <div class="choose-files mt-2">
                                                    <label for="purchase_logo">
                                                        <div class="{{ VC::BG_P }} purchase_logo_update">
                                                            <i class="{{ VC::TI }} ti-upload px-1"></i>{{ __('Choose file here') }}
                                                        </div>
                                                        <input type="file" class="{{ VC::FM_CT }} file" name="purchase_logo" id="purchase_logo" data-filename="purchase_logo_update">
                                                        <img id="purchase_image" class="mt-2" style="width:25%;"/>
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="{{ VC::FM_G }} mt-2 text-end">
                                                <input type="submit" value="{{ __('Save') }}" class="{{ VC::BT_PR_PRM10 }}">
                                            </div>
                                        </form>
                                        @push(StacksConstants::ADM_SCR_PG)
                                            <script defer src="{{ asset('assets/js/routes/settings/pos/purchaseSettings.js') }}">
                                            </script>
                                        @endpush
                                    </div>
                                </div>
                                @php
                                    $prcPreviewBase = ViewsConstants::PRC.'.preview';
                                    $prcPreviewKebab = Str::kebab($prcPreviewBase);
                                    $prcPreviewResolved = Route::has($prcPreviewBase) ? $prcPreviewBase : (Route::has($prcPreviewKebab) ? $prcPreviewKebab : null);
                                    $tplValue = (isset($settings[BillsConstants::COL_PRC_TMP]) && isset($settings['purchase_color'])) ? $settings[BillsConstants::COL_PRC_TMP] : 'template1';
                                    $colorValue = (isset($settings[BillsConstants::COL_PRC_TMP]) && isset($settings['purchase_color'])) ? $settings['purchase_color'] : 'ffffff';
                                    $prcPreviewUrl = $prcPreviewResolved ? route($prcPreviewResolved, [$tplValue, $colorValue]) : '#';
                                    $langValue = isset($lang) ? $lang : Utility::fetchUserLang();
                                    $prcPreviewGuardMsg = Utility::fetchLinkMessage($langValue, ViewsConstants::PRC, 'preview_purchase_route_unavailable') ?? 'Purchase preview route is unavailable. Please contact technical support or your domain administrator.';
                                    $iframeId = 'purchase_frame';
                                @endphp
                                <div class="{{ VC::CM9 }}">
                                    <iframe id="{{ $iframeId }}"
                                            class="w-100 h-100"
                                            frameborder="0"
                                            src="{{ $prcPreviewUrl }}"
                                            data-url="{{ $prcPreviewUrl }}"
                                            data-guard-msg="{{ $prcPreviewGuardMsg }}"
                                            data-sv-localized="true"></iframe>
                                </div>
                                @push(StacksConstants::ADM_SCR_PG)
                                    <script defer src="{{ asset('assets/js/routes/settings/pos/purchasePreview.js') }}"></script>
                                @endpush
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="pills-pos" role="tabpanel" aria-labelledby="pills-pos-tab">
                        <div class="bg-none">
                            <div class="{{ VC::RW }} company-setting">
                                <div class="{{ VC::CM3 }}">
                                    <div class="card-body">
                                        <h5></h5>
                                        @php
                                            $posSettingsBase = ViewsConstants::POS_TMP.'.settings';
                                            $posSettingsKebab = Str::kebab($posSettingsBase);
                                            $posSettingsResolved = Route::has($posSettingsBase) ? $posSettingsBase : (Route::has($posSettingsKebab) ? $posSettingsKebab : null);
                                            $posSettingsUrl = $posSettingsResolved ? route($posSettingsResolved) : '#';
                                            $langValue = isset($lang) ? $lang : Utility::fetchUserLang();
                                            $posSettingsGuardMsg = Utility::fetchLinkMessage($langValue, ViewsConstants::POS_TMP, 'settings_pos_template_route_unavailable') ?? 'POS template settings route is unavailable. Please contact technical support or your domain administrator.';
                                            $posTmpSettings = 'pos-settings-form';
                                        @endphp
                                        <form id="{{ $posTmpSettings }}" method="post" action="{{ $posSettingsUrl }}" enctype="multipart/form-data" data-url="{{ $posSettingsUrl }}" data-guard-msg="{{ $posSettingsGuardMsg }}" data-sv-localized="true">
                                            @csrf
                                            <div class="{{ VC::FM_G }}">
                                                <label for="address" class="{{ VC::FM_LB }}">{{ __('POS Template') }}</label>
                                                <select class="{{ VC::FM_CT }}" name="pos_template">
                                                    @if (Utility::isFilled($templates) ?? [])
                                                        @foreach($templates as $key => $template)
                                                            <option value="{{ $key }}" {{ (isset($settings[BillsConstants::COL_BL_]) && $settings[BillsConstants::COL_BL_] == $key) ? 'selected' : '' }}>
                                                                {{ $template }}
                                                            </option>
                                                        @endforeach
                                                    @else
                                                        <option value="">{{ __('No templates found for POS') }}</option>
                                                    @endif
                                                </select>
                                            </div>
                                            <div class="{{ VC::FM_G }}">
                                                <label class="{{ VC::FM_LB }}">{{ __('Color Input') }}</label>
                                                <div class="{{ VC::RW }} gutters-xs">
                                                    @if (Utility::isFilled($colors) ?? [])
                                                        @foreach($colors as $key => $color)
                                                            <div class="{{ VC::C_AT }}">
                                                                <label class="colorinput">
                                                                    <input name="pos_color" type="radio" value="{{ $color }}" class="colorinput-input" {{ (isset($settings['pos_color']) && $settings['pos_color'] == $color) ? 'checked' : '' }}>
                                                                    <span class="colorinput-color" style="background: #{{ $color }}"></span>
                                                                </label>
                                                            </div>
                                                        @endforeach
                                                    @else
                                                        <div class="{{ VC::C_AT }}">
                                                            <label class="colorinput">
                                                                <input name="pos_color" type="radio" value="" class="colorinput-input">
                                                                <span class="colorinput-color" style="background: #ffffff"></span>
                                                            </label>
                                                        </div>
                                                    @endif   
                                                </div>
                                            </div>
                                            <div class="{{ VC::FM_G }}">
                                                <label class="{{ VC::FM_LB }}">{{ __('POS Logo') }}</label>
                                                <div class="choose-files mt-2">
                                                    <label for="pos_logo">
                                                        <div class="{{ VC::BG_P }} pos_logo_update">
                                                            <i class="{{ VC::TI }} ti-upload px-1"></i>{{ __('Choose file here') }}
                                                        </div>
                                                        <input type="file" class="{{ VC::FM_CT }} file" name="pos_logo" id="pos_logo" data-filename="pos_logo_update">
                                                        <img id="pos_image" class="mt-2" style="width:25%;"/>
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="{{ VC::FM_G }} mt-2 text-end">
                                                <input type="submit" value="{{ __('Save') }}" class="{{ VC::BT_PR_PRM10 }}">
                                            </div>
                                        </form>
                                        @push(StacksConstants::ADM_SCR_PG)
                                            <script defer src="{{ asset('assets/js/routes/settings/pos/posSettings.js') }}">
                                            </script>
                                        @endpush
                                    </div>
                                </div>
                                @php
                                    $posPreviewBase = ViewsConstants::POS.'.preview';
                                    $posPreviewKebab = Str::kebab($posPreviewBase);
                                    $posPreviewResolved = Route::has($posPreviewBase) ? $posPreviewBase : (Route::has($posPreviewKebab) ? $posPreviewKebab : null);
                                    $tplValue = (isset($settings[BillsConstants::COL_POS_TMP]) && isset($settings['pos_color'])) ? $settings[BillsConstants::COL_POS_TMP] : 'template1';
                                    $colorValue = (isset($settings[BillsConstants::COL_POS_TMP]) && isset($settings['pos_color'])) ? $settings['pos_color'] : 'ffffff';
                                    $posPreviewUrl = $posPreviewResolved ? route($posPreviewResolved, [$tplValue, $colorValue]) : '#';
                                    $langValue = isset($lang) ? $lang : Utility::fetchUserLang();
                                    $posPreviewGuardMsg = Utility::fetchLinkMessage($langValue, ViewsConstants::POS, 'preview_pos_route_unavailable') ?? 'POS preview route is unavailable. Please contact technical support or your domain administrator.';
                                    $iframeId = 'pos_frame';
                                @endphp
                                <div class="{{ VC::CM9 }}">
                                    <iframe id="{{ $iframeId }}"
                                            class="w-100 h-100"
                                            frameborder="0"
                                            src="{{ $posPreviewUrl }}"
                                            data-url="{{ $posPreviewUrl }}"
                                            data-guard-msg="{{ $posPreviewGuardMsg }}"
                                            data-sv-localized="true"></iframe>
                                </div>
                                @push(StacksConstants::ADM_SCR_PG)
                                    <script defer src="{{ asset('assets/js/routes/settings/pos/posPreview.js') }}"></script>
                                @endpush
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
