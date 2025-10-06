@php
	use App\Config\Constants\{DatabaseConstants as DC,
        ExtendingLayoutsConstants,SettingsConstants as SC,
        StacksConstants,
        ViewsConstants as VW,
        ViewClassNamesConstants as VC,
        YieldingConstants
    };
	use App\Models\{Utility,WebhookSetting};
    use Collective\Html\FormFacade as Form;
	use Illuminate\Support\Facades\{Auth,Log,Route,URL};
    use Illuminate\Support\{Collection, Str};
	$lang = Utility::fetchUserLang();
	$company_favicon ??= '';
	$color ??= '';
	$colorSettings ??= [];
	$data ??= [];
	$logo ??= '';
	$logo_dark ??= '';
	$logo_light ??= '';
	$currentLang ??= [];
	$siteRtl ??= false;
	$webhookSetting ??= collect([]);
	$faviconUrl ??= '';
    $explang ??= DC::DEFAULT_LANG;
    $joininglang ??= DC::DEFAULT_LANG;
    $noclang ??= DC::DEFAULT_LANG;
    $offerlang ??= DC::DEFAULT_LANG;
    $explangs ??= [DC::DEFAULT_LANG];
    $joininglangs ??= [DC::DEFAULT_LANG];
    $noclangs ??= [DC::DEFAULT_LANG];
    $offerlangs ??= [DC::DEFAULT_LANG];
	try {
		$data=Utility::prepareCommonViewData()?:[];
		$setting=$data[SC::ENTITY]??[];
		$colorSettings=$data[SC::CLR_STG]??[];
		$logo=$data[SC::LOGO]??'';
		$logo_light=$setting[SC::CPN_LG_LT]??'';
		$logo_dark=$setting[SC::CPN_LG_DK]??'';
		$company_favicon=$setting[SC::CPN_FAVICON_K]??'';
		$color=$data[SC::THM_CLR]??'';
		$siteRtl=$data[SC::RTL]??false;
		$currentLang=Utility::languages()?:[];
		$lang=Utility::getValByName(SC::DEF_LNG)?:'';
		$webhookSetting=WebhookSetting::where(
			DC::TABLE_CREATOR,
			Auth::user()?->creatorId()
		)->get()?:collect([]);
		$faviconUrl=Utility::getCompanyLogo()?:'';
	} catch (\Error $e) {
		Log::error(
			'Error fetching settings/webhook data',
			[
				'exception_class'=>get_class($e),
				'message'=>$e->getMessage(),
				'file'=>$e->getFile(),
				'line'=>$e->getLine()
			]
		);
	} catch (\Exception $e) {
		Log::error(
			'Exception fetching settings/webhook data',
			[
				'exception_class'=>get_class($e),
				'message'=>$e->getMessage(),
				'file'=>$e->getFile(),
				'line'=>$e->getLine()
			]
		);
	} catch (\Throwable $e) {
		Log::error(
			'Throwable fetching settings/webhook data',
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
    {{ __('Settings') }}
@endsection
@section(YieldingConstants::ADM_BDC)
    <li class="breadcrumb-item">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
        {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="breadcrumb-item">{{ __('Settings') }}</li>
@endsection
@push(StacksConstants::ADM_CSS)
    <link rel="stylesheet" href="{{ asset('css/summernote/summernote-bs4.css') }}">
@endpush
@section(YieldingConstants::ADM_CTT)
    <div class="row">
        <div class="col-sm-12">
            <div class="row">
                <div class="col-xl-3">
                    <div class="{{ VC::CD_STK }}" style="top:30px">
                        @php
                            $anchors = [
                                'brand-settings',
                                'system-settings',
                                'company-settings',
                                'email-settings',
                                'tracker-settings',
                                'payment-settings',
                                'zoom-settings',
                                'slack-settings',
                                'telegram-settings',
                                'twilio-settings',
                                'email-notification-settings',
                                'offer-letter-settings',
                                'joining-letter-settings',
                                'experience-certificate-settings',
                                'noc-settings',
                                'google-calendar',
                                'webhook-settings',
                                'ip-restriction-settings',
                            ];
                        @endphp
                        <div class="list-group list-group-flush" id="useradd-sidenav">
                            @if (Utility::isFilled($anchors))
                                @foreach($anchors as $anchor)
                                    @php
                                        $label = preg_replace('/-+/', ' ', $anchor);
                                        $label = ucwords($label);
                                    @endphp
                                    <a href="#{{ $anchor }}"
                                        class="list-group-item list-group-item-action border-0">
                                        {{ __($label) }}
                                        <div class="float-end"><i class="{{ VC::TI_CHV_RT }}"></i></div>
                                    </a>
                                @endforeach
                            @else
                                <div class="list-group-item border-0">
                                    {{ __('No setting links available') }}
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="col-xl-9">
                    @if(!empty($setting) && isset($setting))
                        @php
                            $businessSettingBaseName='business.setting';
                            $businessSettingKebabName=Str::kebab($businessSettingBaseName);
                            $businessSettingResolvedName=Route::has($businessSettingBaseName)?$businessSettingBaseName:(Route::has($businessSettingKebabName)?$businessSettingKebabName:null);
                            $businessSettingRouteArray=$businessSettingResolvedName?[$businessSettingResolvedName]:['#'];
                            $businessSettingUrl=$businessSettingResolvedName?route($businessSettingResolvedName):'#';
                            $langValue=isset($lang)&&is_string($lang)?$lang:Utility::fetchUserLang();
                            $businessSettingGuardMsg=Utility::fetchLinkMessage($langValue,'business','business_setting_route_unavailable')??__('Business setting route is unavailable. Please contact technical support or your domain administrator.');
                            $businessSettingFormId='business-setting-form';
                            $logoBase=isset($logo)&&is_string($logo)?rtrim($logo,'/'):asset('storage');
                            $logoDarkFile=!empty($logo_dark)?$logo_dark:SC::CPN_LG_DK_DEF;
                            $logoLightFile=!empty($logo_light)?$logo_light:SC::CPN_LG_LT_DEF;
                            $faviconFile=!empty($favicon??null)?$favicon:(SC::CPN_FAVICON_DEF??'favicon.png');
                            $t=time();
                            $logoDarkUrl=$logoBase.'/'.$logoDarkFile.'?t='.$t;
                            $logoLightUrl=$logoBase.'/'.$logoLightFile.'?t='.$t;
                            $faviconUrl=(isset($faviconUrl)&&is_string($faviconUrl)?$faviconUrl:($logoBase.'/'.$faviconFile)).'?t='.$t;
                            $langs=Utility::languages();
                            $currLang=Utility::isFilled($langs)?($langs[$langValue]??ucfirst((string)$langValue)):(is_object($langs)&&isset($langs->{$langValue})?$langs->{$langValue}:ucfirst((string)$langValue));
                            $color=$color??'theme-1';
                        @endphp
                        <div id="brand-settings" class="card">
                            {!! Form::model($setting??[],['route'=>$businessSettingRouteArray,'method'=>'POST','enctype'=>'multipart/form-data','id'=>$businessSettingFormId,'data-url'=>$businessSettingUrl,'data-guard-msg'=>$businessSettingGuardMsg]) !!}
                                @csrf
                                @push(StacksConstants::ADM_SCR_PG)
                                    <script defer src="{{ asset('assets/js/routes/settings/companies/business.js') }}"></script>
                                @endpush
                                <div class="card-header">
                                    <h5>{{ __('Brand Settings') }}</h5>
                                    <small class="text-muted">{{ __('Edit your brand details') }}</small>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-lg-4 col-sm-6 col-md-6">
                                            <div class="{{ VC::CD }} logo_card">
                                                <div class="card-header"><h5>{{ __('Logo Dark') }}</h5></div>
                                                <div class="card-body pt-0">
                                                    <div class="setting-card">
                                                        <div class="logo-content {{ VC::MT4 }}">
                                                            <img id="image" src="{{ $logoDarkUrl }}" class="big-logo" alt="{{ __('Company dark logo') }}">
                                                        </div>
                                                        <div class="choose-files mt-5">
                                                            <label for="company_logo_dark">
                                                                <div class="{{ VC::BG_P }} company_logo_update"><i class="ti ti-upload px-1"></i>{{ __('Choose file here') }}</div>
                                                                <input type="file" name="company_logo_dark" id="company_logo_dark" class="{{ VC::FM_CT }} file setting_logo" accept="image/*" aria-label="{{ __('Upload dark logo') }}" data-filename="company_logo_update">
                                                            </label>
                                                        </div>
                                                        @error('company_logo_dark')
                                                            <div class="{{ VC::RW }}"><span class="invalid-logo" role="alert"><strong class="text-danger">{{ !empty($message) ? $message : __('No message available') }}</strong></span></div>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-4 col-sm-6 col-md-6">
                                            <div class="{{ VC::CD }} logo_card">
                                                <div class="card-header"><h5>{{ __('Logo Light') }}</h5></div>
                                                <div class="card-body pt-0">
                                                    <div class="setting-card">
                                                        <div class="logo-content {{ VC::MT4 }}">
                                                            <img id="image1" src="{{ $logoLightUrl }}" class="big-logo img_setting" alt="{{ __('Company light logo') }}">
                                                        </div>
                                                        <div class="choose-files mt-5">
                                                            <label for="company_logo_light">
                                                                <div class="{{ VC::BG_P }} dark_logo_update"><i class="ti ti-upload px-1"></i>{{ __('Choose file here') }}</div>
                                                                <input type="file" class="{{ VC::FM_CT }} file setting_logo" name="company_logo_light" id="company_logo_light" accept="image/*" aria-label="{{ __('Upload light logo') }}" data-filename="dark_logo_update">
                                                            </label>
                                                        </div>
                                                        @error('company_logo_light')
                                                            <div class="{{ VC::RW }}"><span class="invalid-logo" role="alert"><strong class="text-danger">{{ !empty($message) ? $message : __('No message available') }}</strong></span></div>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-4 col-sm-6 col-md-6">
                                            <div class="{{ VC::CD }} logo_card">
                                                <div class="card-header"><h5>{{ __('Favicon') }}</h5></div>
                                                <div class="card-body pt-0">
                                                    <div class="setting-card">
                                                        <div class="logo-content {{ VC::MT4 }}">
                                                            <img id="image2" src="{{ $faviconUrl }}" width="50" class="img_setting" alt="{{ __('Favicon') }}">
                                                        </div>
                                                        <div class="choose-files mt-5">
                                                            <label for="company_favicon">
                                                                <div class="{{ VC::BG_P }} company_favicon_update"><i class="ti ti-upload px-1"></i>{{ __('Choose file here') }}</div>
                                                                <input type="file" class="{{ VC::FM_CT }} file setting_logo" id="company_favicon" name="company_favicon" accept="image/*" aria-label="{{ __('Upload favicon') }}" data-filename="company_favicon_update">
                                                            </label>
                                                        </div>
                                                        @error('company_favicon')
                                                            <div class="{{ VC::RW }}"><span class="invalid-logo" role="alert"><strong class="text-danger">{{ !empty($message) ? $message : __('No message available') }}</strong></span></div>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group col-md-3">
                                            {{ Form::label('title_text', __('Title Text'), ['class'=>VC::FM_LB]) }}
                                            {{ Form::text('title_text', $setting['title_text']??null, ['class'=>VC::FM_CT,'placeholder'=>__('Title Text')]) }}
                                            @error('title_text')
                                                <span class="invalid-title_text" role="alert"><strong class="text-danger">{{ !empty($message) ? $message : __('No message available') }}</strong></span>
                                            @enderror
                                        </div>
                                        <div class="col-md-3 {{ VC::FM_G }}">
                                            {{ Form::label(SC::FT_TXT, __('Footer Text'), ['class'=>VC::FM_LB]) }}
                                            {{ Form::text(SC::FT_TXT, Utility::getValByName(SC::FT_TXT)??__('No footer text available'), ['class'=>VC::FM_CT,'placeholder'=>__('Enter Footer Text')]) }}
                                            @error(SC::FT_TXT)
                                                <span class="invalid-footer_text" role="alert"><strong class="text-danger">{{ !empty($message) ? $message : __('No message available') }}</strong></span>
                                            @enderror
                                        </div>
                                        <div class="col-md-3">
                                            <div class="{{ VC::FM_G }}">
                                                {{ Form::label(SC::DEF_LNG, __('Default Language'), ['class'=>VC::FM_LB.' text-dark']) }}
                                                <div class="changeLanguage">
                                                    <select name="default_language" id="default_language" class="{{ VC::FM_CT_SL }}">
                                                        @if(Utility::isFilled($langs))
                                                            @foreach($langs as $code=>$language)
                                                                <option value="{{ $code }}" @selected($langValue===$code)>{{ ucfirst((string)$language) }}</option>
                                                            @endforeach
                                                        @else
                                                            <option value="{{ $langValue }}" selected>{{ ucfirst((string)$currLang) }}</option>
                                                        @endif
                                                    </select>
                                                </div>
                                                @error(SC::DEF_LNG)
                                                    <span class="invalid-default_language" role="alert"><strong class="text-danger">{{ !empty($message) ? $message : __('No message available') }}</strong></span>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="{{ VC::FM_G }} col-md-3">
                                            <div class="{{ VC::CST_CTL }} custom-switch">
                                                <label class="text-dark mb-1 mt-1" for="SITE_RTL">{{ __('Enable RTL') }}</label>
                                                <div>
                                                    <input type="checkbox" name="SITE_RTL" id="SITE_RTL" data-toggle="switchbutton" data-onstyle="primary" {{ (($siteRtl??'')==='on')?'checked':'' }}>
                                                    <label class="{{ VC::CST_LB }}" for="SITE_RTL"></label>
                                                </div>
                                            </div>
                                        </div>
                                        <h5 class="small-title mt-2">{{ __('Theme Customizer') }}</h5>
                                        <div class="setting-card setting-logo-box">
                                            <div class="{{ VC::RW }}">
                                                <div class="{{ VC::CL_XL4 }}">
                                                    <h6 class="{{ VC::MT1 }}"><i data-feather="credit-card" class="me-2"></i>{{ __('Primary color settings') }}</h6>
                                                    <hr class="my-2" />
                                                    <div class="theme-color themes-color">
                                                        @foreach(range(1,10) as $themeNumber)
                                                            @php $themeValue='theme-'.$themeNumber; @endphp
                                                            <a href="#!" class="themes-color-change {{ $color===$themeValue?'active_color':'' }}" data-value="{{ $themeValue }}" aria-label="{{ __('Choose :theme color',['theme'=>$themeValue]) }}"></a>
                                                            <input type="radio" class="theme_color d-none" name="color" value="{{ $themeValue }}" @checked($color===$themeValue)>
                                                            @if($themeNumber===5)<br>@endif
                                                        @endforeach
                                                    </div>
                                                </div>
                                                <div class="{{ VC::CL_XL4 }}">
                                                    <h6 class="{{ VC::MT1 }}"><i data-feather="layout" class="me-2"></i>{{ __('Sidebar settings') }}</h6>
                                                    <hr class="{{ VC::MT1 }}" />
                                                    <div class="form-check form-switch">
                                                        <input type="checkbox" class="form-check-input" id="cust-theme-bg" name="cust_theme_bg" {{ (data_get($setting??[],SC::CST_BG,'')==='on')?'checked':'' }} />
                                                        <label class="form-check-label {{ VC::FW600 }} ps-1" for="cust-theme-bg">{{ __('Transparent layout') }}</label>
                                                    </div>
                                                </div>
                                                <div class="{{ VC::CL_XL4 }}">
                                                    <h6 class="{{ VC::MT1 }}"><i data-feather="sun" class="me-2"></i>{{ __('Layout settings') }}</h6>
                                                    <hr class="{{ VC::MT1 }}" />
                                                    <div class="form-check form-switch mt-2">
                                                        <input type="checkbox" class="form-check-input" id="cust-darklayout" name="cust_darklayout" {{ (data_get($colorSettings??[],SC::CST_DRK,'')==='on')?'checked':'' }} />
                                                        <label class="form-check-label {{ VC::FW600 }} ps-1" for="cust-darklayout">{{ __('Dark Layout') }}</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer text-end">
                                    <div class="form-group">
                                        <input class="{{ VC::BT_PR_PRM10 }}" type="submit" value="{{ __('Save Changes') }}">
                                    </div>
                                </div>
                            {{ Form::close() }}
                        </div>
                        @php
                            $systemSettingsRouteBaseName=VW::SYS.'.settings';
                            $systemSettingsKebabRouteName=Str::kebab($systemSettingsRouteBaseName);
                            $systemSettingsResolvedName=Route::has($systemSettingsRouteBaseName)?$systemSettingsRouteBaseName:(Route::has($systemSettingsKebabRouteName)?$systemSettingsKebabRouteName:null);
                            $systemSettingsRouteArray=$systemSettingsResolvedName?[$systemSettingsResolvedName]:['#'];
                            $systemSettingsUrl=$systemSettingsResolvedName?route($systemSettingsResolvedName):'#';
                            $systemSettingsGuardMsg=Utility::fetchLinkMessage(isset($lang)&&is_string($lang)?$lang:Utility::fetchUserLang(),VW::SYS,'system_settings_route_unavailable')??__('System settings route is unavailable. Please contact technical support or your domain administrator.');
                            $systemSettingsFormId='system-settings-form';
                        @endphp
                        <div id="system-settings" class="card">
                            <div class="card-header">
                                <h5>{{ __('System Settings') }}</h5>
                                <small class="text-muted">{{ __('Edit your system details') }}</small>
                            </div>
                            {!! Form::model($setting??[],['route'=>$systemSettingsRouteArray,'method'=>'POST','id'=>$systemSettingsFormId,'data-url'=>$systemSettingsUrl,'data-guard-msg'=>$systemSettingsGuardMsg]) !!}
                                @push(StacksConstants::ADM_SCR_PG)
                                    <script defer src="{{ asset('assets/js/routes/settings/companies/system.js') }}"></script>
                                @endpush
                                <div class="card-body">
                                    <div class="{{ VC::RW }}">
                                        <div class="{{ VC::FM_G }} {{ VC::CM6 }}">
                                            {{ Form::label('site_currency', __('Currency *'), ['class'=>VC::FM_LB]) }}
                                            {{ Form::text('site_currency', data_get($setting??[], 'site_currency'), ['class'=>VC::FM_CT.' font-style','required','placeholder'=>__('Enter Currency')]) }}
                                            <small>{{ __('Note: Add currency code as per three-letter ISO code.') }}<br><a href="https://stripe.com/docs/currencies" target="_blank">{{ __('You can find out how to do that here.') }}</a></small><br>
                                            @error('site_currency')
                                                <span class="invalid-site_currency" role="alert"><strong class="text-danger">{{ !empty($message) ? $message : __('No message available') }}</strong></span>
                                            @enderror
                                        </div>
                                        <div class="{{ VC::FM_G }} {{ VC::CM6 }}">
                                            {{ Form::label('site_currency_symbol', __('Currency Symbol *'), ['class'=>VC::FM_LB]) }}
                                            {{ Form::text('site_currency_symbol', data_get($setting??[], 'site_currency_symbol'), ['class'=>VC::FM_CT]) }}
                                            @error('site_currency_symbol')
                                                <span class="invalid-site_currency_symbol" role="alert"><strong class="text-danger">{{ !empty($message) ? $message : __('No message available') }}</strong></span>
                                            @enderror
                                        </div>
                                        <div class="{{ VC::FM_G }} {{ VC::CM6 }}">
                                            <label class="{{ VC::FM_LB }}">{{ __('Currency Symbol Position') }}</label>
                                            <div class="{{ VC::RW }} ms-1">
                                                <div class="form-check {{ VC::CM6 }}">
                                                    <input class="form-check-input" type="radio" name="site_currency_symbol_position" value="pre" id="symbol_pos_pre" @checked((data_get($setting??[], 'site_currency_symbol_position',''))==='pre')>
                                                    <label class="form-check-label" for="symbol_pos_pre">{{ __('Pre') }}</label>
                                                </div>
                                                <div class="form-check {{ VC::CM6 }}">
                                                    <input class="form-check-input" type="radio" name="site_currency_symbol_position" value="post" id="symbol_pos_post" @checked((data_get($setting??[], 'site_currency_symbol_position',''))==='post')>
                                                    <label class="form-check-label" for="symbol_pos_post">{{ __('Post') }}</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="{{ VC::FM_G }} {{ VC::CM6 }}">
                                            {{ Form::label('decimal_number', __('Decimal Number Format'), ['class'=>VC::FM_LB]) }}
                                            {{ Form::number('decimal_number', data_get($setting??[], 'decimal_number'), ['class'=>VC::FM_CT]) }}
                                            @error('decimal_number')
                                                <span class="invalid-decimal_number" role="alert"><strong class="text-danger">{{ !empty($message) ? $message : __('No message available') }}</strong></span>
                                            @enderror
                                        </div>
                                        <div class="{{ VC::FM_G }} {{ VC::CM6 }}">
                                            <label for="site_date_format" class="{{ VC::FM_LB }}">{{ __('Date Format') }}</label>
                                            <select name="site_date_format" id="site_date_format" class="{{ VC::FM_CT }} selectric">
                                                <option value="M j, Y" @selected((data_get($setting??[], 'site_date_format',''))==='M j, Y')>Jan 1,2015</option>
                                                <option value="d-m-Y" @selected((data_get($setting??[], 'site_date_format',''))==='d-m-Y')>dd-mm-yyyy</option>
                                                <option value="m-d-Y" @selected((data_get($setting??[], 'site_date_format',''))==='m-d-Y')>mm-dd-yyyy</option>
                                                <option value="Y-m-d" @selected((data_get($setting??[], 'site_date_format',''))==='Y-m-d')>yyyy-mm-dd</option>
                                            </select>
                                        </div>
                                        <div class="{{ VC::FM_G }} {{ VC::CM6 }}">
                                            <label for="site_time_format" class="{{ VC::FM_LB }}">{{ __('Time Format') }}</label>
                                            <select name="site_time_format" id="site_time_format" class="{{ VC::FM_CT }} selectric">
                                                <option value="g:i A" @selected((data_get($setting??[], 'site_time_format',''))==='g:i A')>10:30 PM</option>
                                                <option value="g:i a" @selected((data_get($setting??[], 'site_time_format',''))==='g:i a')>10:30 pm</option>
                                                <option value="H:i" @selected((data_get($setting??[], 'site_time_format',''))==='H:i')>22:30</option>
                                            </select>
                                        </div>
                                        <div class="{{ VC::FM_G }} {{ VC::CM6 }}">
                                            {{ Form::label('customer_prefix', __('Customer Prefix'), ['class'=>VC::FM_LB]) }}
                                            {{ Form::text('customer_prefix', data_get($setting??[], 'customer_prefix'), ['class'=>VC::FM_CT]) }}
                                            @error('customer_prefix')
                                                <span class="invalid-customer_prefix" role="alert"><strong class="text-danger">{{ !empty($message) ? $message : __('No message available') }}</strong></span>
                                            @enderror
                                        </div>
                                        <div class="{{ VC::FM_G }} {{ VC::CM6 }}">
                                            {{ Form::label('vendor_prefix', __('Vendor Prefix'), ['class'=>VC::FM_LB]) }}
                                            {{ Form::text('vendor_prefix', data_get($setting??[], 'vendor_prefix'), ['class'=>VC::FM_CT]) }}
                                            @error('vendor_prefix')
                                                <span class="invalid-vendor_prefix" role="alert"><strong class="text-danger">{{ !empty($message) ? $message : __('No message available') }}</strong></span>
                                            @enderror
                                        </div>
                                        <div class="{{ VC::FM_G }} {{ VC::CM6 }}">
                                            {{ Form::label('proposal_prefix', __('Proposal Prefix'), ['class'=>VC::FM_LB]) }}
                                            {{ Form::text('proposal_prefix', data_get($setting??[], 'proposal_prefix'), ['class'=>VC::FM_CT]) }}
                                            @error('proposal_prefix')
                                                <span class="invalid-proposal_prefix" role="alert"><strong class="text-danger">{{ !empty($message) ? $message : __('No message available') }}</strong></span>
                                            @enderror
                                        </div>
                                        <div class="{{ VC::FM_G }} {{ VC::CM6 }}">
                                            {{ Form::label('invoice_prefix', __('Invoice Prefix'), ['class'=>VC::FM_LB]) }}
                                            {{ Form::text('invoice_prefix', data_get($setting??[], 'invoice_prefix'), ['class'=>VC::FM_CT]) }}
                                            @error('invoice_prefix')
                                                <span class="invalid-invoice_prefix" role="alert"><strong class="text-danger">{{ !empty($message) ? $message : __('No message available') }}</strong></span>
                                            @enderror
                                        </div>
                                        <div class="{{ VC::FM_G }} {{ VC::CM6 }}">
                                            {{ Form::label('bill_prefix', __('Bill Prefix'), ['class'=>VC::FM_LB]) }}
                                            {{ Form::text('bill_prefix', data_get($setting??[], 'bill_prefix'), ['class'=>VC::FM_CT]) }}
                                            @error('bill_prefix')
                                                <span class="invalid-bill_prefix" role="alert"><strong class="text-danger">{{ !empty($message) ? $message : __('No message available') }}</strong></span>
                                            @enderror
                                        </div>
                                        <div class="{{ VC::FM_G }} {{ VC::CM6 }}">
                                            {{ Form::label('purchase_prefix', __('Purchase Prefix'), ['class'=>VC::FM_LB]) }}
                                            {{ Form::text('purchase_prefix', data_get($setting??[], 'purchase_prefix'), ['class'=>VC::FM_CT]) }}
                                            @error('purchase_prefix')
                                                <span class="invalid-purchase_prefix" role="alert"><strong class="text-danger">{{ !empty($message) ? $message : __('No message available') }}</strong></span>
                                            @enderror
                                        </div>
                                        <div class="{{ VC::FM_G }} {{ VC::CM6 }}">
                                            {{ Form::label('pos_prefix', __('Pos Prefix'), ['class'=>VC::FM_LB]) }}
                                            {{ Form::text('pos_prefix', data_get($setting??[], 'pos_prefix'), ['class'=>VC::FM_CT]) }}
                                            @error('pos_prefix')
                                                <span class="invalid-pos_prefix" role="alert"><strong class="text-danger">{{ !empty($message) ? $message : __('No message available') }}</strong></span>
                                            @enderror
                                        </div>
                                        <div class="{{ VC::FM_G }} {{ VC::CM6 }}">
                                            {{ Form::label('journal_prefix', __('Journal Prefix'), ['class'=>VC::FM_LB]) }}
                                            {{ Form::text('journal_prefix', data_get($setting??[], 'journal_prefix'), ['class'=>VC::FM_CT]) }}
                                            @error('journal_prefix')
                                                <span class="invalid-journal_prefix" role="alert"><strong class="text-danger">{{ !empty($message) ? $message : __('No message available') }}</strong></span>
                                            @enderror
                                        </div>
                                        <div class="{{ VC::FM_G }} {{ VC::CM6 }}">
                                            {{ Form::label('expense_prefix', __('Expense Prefix'), ['class'=>VC::FM_LB]) }}
                                            {{ Form::text('expense_prefix', data_get($setting??[], 'expense_prefix'), ['class'=>VC::FM_CT]) }}
                                            @error('expense_prefix')
                                                <span class="invalid-expense_prefix" role="alert"><strong class="text-danger">{{ !empty($message) ? $message : __('No message available') }}</strong></span>
                                            @enderror
                                        </div>
                                        <div class="{{ VC::FM_G }} {{ VC::CM6 }}">
                                            {{ Form::label('shipping_display', __('Display Shipping in Proposal / Invoice / Bill'), ['class'=>VC::FM_LB]) }}
                                            <div class="form-switch form-switch-left">
                                                <input type="checkbox" class="form-check-input {{ VC::MT3 }}" name="shipping_display" id="shipping_display" {{ (data_get($setting??[], 'shipping_display',''))==='on'?'checked':'' }}>
                                                <label class="form-check-label" for="shipping_display"></label>
                                            </div>
                                            @error('shipping_display')
                                                <span class="invalid-shipping_display" role="alert"><strong class="text-danger">{{ !empty($message) ? $message : __('No message available') }}</strong></span>
                                            @enderror
                                        </div>
                                        <div class="{{ VC::FM_G }} {{ VC::C12 }}">
                                            {{ Form::label('footer_title', __('Proposal/Invoice/Bill/Purchase/POS Footer Title'), ['class'=>VC::FM_LB]) }}
                                            {{ Form::text('footer_title', data_get($setting??[], 'footer_title'), ['class'=>VC::FM_CT]) }}
                                            @error('footer_title')
                                                <span class="invalid-footer_title" role="alert"><strong class="text-danger">{{ !empty($message) ? $message : __('No message available') }}</strong></span>
                                            @enderror
                                        </div>
                                        <div class="{{ VC::FM_G }} {{ VC::C12 }}">
                                            {{ Form::label('footer_notes', __('Proposal/Invoice/Bill/Purchase/POS Footer Note'), ['class'=>VC::FM_LB]) }}
                                            {{ Form::textarea('footer_notes', data_get($setting??[], 'footer_notes'), ['class'=>'summernote-simple4 summernote-simple']) }}
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer text-end">
                                    <div class="{{ VC::FM_G }}">
                                        <input class="{{ VC::BT_PR_PRM10 }}" type="submit" value="{{ __('Save Changes') }}">
                                    </div>
                                </div>
                            {{ Form::close() }}
                        </div>
                        @php
                            $cpSettingsRouteBaseName=VW::CP.'.settings';
                            $cpSettingsKebabRouteName=Str::kebab($cpSettingsRouteBaseName);
                            $cpSettingsResolvedName=Route::has($cpSettingsRouteBaseName)?$cpSettingsRouteBaseName:(Route::has($cpSettingsKebabRouteName)?$cpSettingsKebabRouteName:null);
                            $cpSettingsRouteArray=$cpSettingsResolvedName?[$cpSettingsResolvedName]:['#'];
                            $cpSettingsUrl=$cpSettingsResolvedName?route($cpSettingsResolvedName):'#';
                            $cpSettingsGuardMsg=Utility::fetchLinkMessage(isset($lang)&&is_string($lang)?$lang:Utility::fetchUserLang(),VW::CP,'company_settings_route_unavailable')??__('Company settings route is unavailable. Please contact technical support or your domain administrator.');
                            $cpSettingsFormId='cp-settings-form';
                        @endphp
                        <div id="company-settings" class="card">
                            <div class="card-header">
                                <h5>{{ __('Company Settings') }}</h5>
                                <small class="text-muted">{{ __('Edit your company details') }}</small>
                            </div>
                            {!! Form::model($setting??[],['route'=>$cpSettingsRouteArray,'method'=>'POST','id'=>$cpSettingsFormId,'data-url'=>$cpSettingsUrl,'data-guard-msg'=>$cpSettingsGuardMsg]) !!}
                                @push(StacksConstants::ADM_SCR_PG)
                                    <script defer src="{{ asset('assets/js/routes/settings/companies/company.js') }}"></script>
                                @endpush
                                <div class="card-body">
                                    <div class="{{ VC::RW }}">
                                        <div class="{{ VC::FM_G }} col-md-6">
                                            {{ Form::label('company_name', __('Company Name *'), ['class'=>VC::FM_LB]) }}
                                            {{ Form::text('company_name', data_get($setting??[], 'company_name'), ['class'=>VC::FM_CT.' font-style']) }}
                                            @error('company_name')
                                                <span class="invalid-company_name" role="alert"><strong class="text-danger">{{ !empty($message) ? $message : __('No message available') }}</strong></span>
                                            @enderror
                                        </div>
                                        <div class="{{ VC::FM_G }} col-md-6">
                                            {{ Form::label('company_address', __('Address'), ['class'=>VC::FM_LB]) }}
                                            {{ Form::text('company_address', data_get($setting??[], 'company_address'), ['class'=>VC::FM_CT.' font-style']) }}
                                            @error('company_address')
                                                <span class="invalid-company_address" role="alert"><strong class="text-danger">{{ !empty($message) ? $message : __('No message available') }}</strong></span>
                                            @enderror
                                        </div>
                                        <div class="{{ VC::FM_G }} col-md-6">
                                            {{ Form::label('company_city', __('City'), ['class'=>VC::FM_LB]) }}
                                            {{ Form::text('company_city', data_get($setting??[], 'company_city'), ['class'=>VC::FM_CT.' font-style']) }}
                                            @error('company_city')
                                                <span class="invalid-company_city" role="alert"><strong class="text-danger">{{ !empty($message) ? $message : __('No message available') }}</strong></span>
                                            @enderror
                                        </div>
                                        <div class="{{ VC::FM_G }} col-md-6">
                                            {{ Form::label('company_state', __('State'), ['class'=>VC::FM_LB]) }}
                                            {{ Form::text('company_state', data_get($setting??[], 'company_state'), ['class'=>VC::FM_CT.' font-style']) }}
                                            @error('company_state')
                                                <span class="invalid-company_state" role="alert"><strong class="text-danger">{{ !empty($message) ? $message : __('No message available') }}</strong></span>
                                            @enderror
                                        </div>
                                        <div class="{{ VC::FM_G }} col-md-6">
                                            {{ Form::label('company_zipcode', __('Zip/Post Code'), ['class'=>VC::FM_LB]) }}
                                            {{ Form::text('company_zipcode', data_get($setting??[], 'company_zipcode'), ['class'=>VC::FM_CT]) }}
                                            @error('company_zipcode')
                                                <span class="invalid-company_zipcode" role="alert"><strong class="text-danger">{{ !empty($message) ? $message : __('No message available') }}</strong></span>
                                            @enderror
                                        </div>
                                        <div class="{{ VC::FM_G }} col-md-6">
                                            {{ Form::label('company_country', __('Country'), ['class'=>VC::FM_LB]) }}
                                            {{ Form::text('company_country', data_get($setting??[], 'company_country'), ['class'=>VC::FM_CT.' font-style']) }}
                                            @error('company_country')
                                                <span class="invalid-company_country" role="alert"><strong class="text-danger">{{ !empty($message) ? $message : __('No message available') }}</strong></span>
                                            @enderror
                                        </div>
                                        <div class="{{ VC::FM_G }} col-md-6">
                                            {{ Form::label('company_telephone', __('Telephone'), ['class'=>VC::FM_LB]) }}
                                            {{ Form::text('company_telephone', data_get($setting??[], 'company_telephone'), ['class'=>VC::FM_CT]) }}
                                            @error('company_telephone')
                                                <span class="invalid-company_telephone" role="alert"><strong class="text-danger">{{ !empty($message) ? $message : __('No message available') }}</strong></span>
                                            @enderror
                                        </div>
                                        <div class="{{ VC::FM_G }} col-md-6">
                                            {{ Form::label('registration_number', __('Company Registration Number *'), ['class'=>VC::FM_LB]) }}
                                            {{ Form::text('registration_number', data_get($setting??[], 'registration_number'), ['class'=>VC::FM_CT]) }}
                                            @error('registration_number')
                                                <span class="invalid-registration_number" role="alert"><strong class="text-danger">{{ !empty($message) ? $message : __('No message available') }}</strong></span>
                                            @enderror
                                        </div>
                                        <div class="{{ VC::FM_G }} col-md-4">
                                            {{ Form::label('company_start_time', __('Company Start Time *'), ['class'=>VC::FM_LB]) }}
                                            {{ Form::time('company_start_time', data_get($setting??[], 'company_start_time'), ['class'=>VC::FM_CT]) }}
                                            @error('company_start_time')
                                                <span class="invalid-company_start_time" role="alert"><strong class="text-danger">{{ !empty($message) ? $message : __('No message available') }}</strong></span>
                                            @enderror
                                        </div>
                                        <div class="{{ VC::FM_G }} col-md-4">
                                            {{ Form::label('company_end_time', __('Company End Time *'), ['class'=>VC::FM_LB]) }}
                                            {{ Form::time('company_end_time', data_get($setting??[], 'company_end_time'), ['class'=>VC::FM_CT]) }}
                                            @error('company_end_time')
                                                <span class="invalid-company_end_time" role="alert"><strong class="text-danger">{{ !empty($message) ? $message : __('No message available') }}</strong></span>
                                            @enderror
                                        </div>
                                        <div class="{{ VC::FM_G }} col-md-4">
                                            <label class="{{ VC::FM_LB }}" for="ip_restrict">{{ __('Ip Restrict') }}</label>
                                            <div class="custom-control custom-switch mt-2">
                                                <input type="checkbox" class="form-check-input" data-toggle="switchbutton" data-onstyle="primary" name="ip_restrict" id="ip_restrict" {{ (data_get($setting??[], 'ip_restrict',''))==='on'?'checked':'' }}>
                                            </div>
                                        </div>
                                        <div class="{{ VC::FM_G }} col-md-12 mt-2">
                                            {{ Form::label('timezone', __('Timezone'), ['class'=>VC::FM_LB]) }}
                                            <select name="timezone" class="{{ VC::FM_CT }} custom-select" id="timezone">
                                                <option value="">{{ __('Select Timezone') }}</option>
                                                @if(is_iterable($timezones??[]))
                                                    @foreach($timezones as $k=>$timezone)
                                                        <option value="{{ $k }}" {{ (data_get($setting??[], 'timezone')==$k)?'selected':'' }}>{{ $timezone }}</option>
                                                    @endforeach
                                                @else
                                                    <option value="{{ data_get($setting??[], 'timezone') }}" selected>{{ data_get($setting??[], 'timezone', __('No timezone available')) }}</option>
                                                @endif
                                            </select>
                                        </div>
                                        <div class="{{ VC::FM_G }} col-md-6">
                                            <div class="{{ VC::RW }} {{ VC::MT4 }}">
                                                <div class="col-md-6">
                                                    <label for="vat_gst_number_switch">{{ __('Tax Number') }}</label>
                                                    <div class="form-check form-switch custom-switch-v1 float-end">
                                                        <input type="checkbox" name="vat_gst_number_switch" class="form-check-input input-primary pointer" value="on" id="vat_gst_number_switch" {{ (data_get($setting??[], 'vat_gst_number_switch',''))==='on'?' checked ':'' }}>
                                                        <label class="form-check-label" for="vat_gst_number_switch"></label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="{{ VC::FM_G }} col-md-6 tax_type_div {{ (data_get($setting??[], 'vat_gst_number_switch','')!=='on')?' d-none ':'' }}">
                                            <div class="{{ VC::RW }}">
                                                <div class="col-md-6">
                                                    <div class="{{ VC::FM_CHK_IL }} {{ VC::FM_GB3 }}">
                                                        <input type="radio" id="customRadio8" name="tax_type" value="VAT" class="form-check-input" {{ (data_get($setting??[], 'tax_type',''))==='VAT'?'checked':'' }}>
                                                        <label class="form-check-label" for="customRadio8">{{ __('VAT Number') }}</label>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="{{ VC::FM_CHK_IL }} {{ VC::FM_GB3 }}">
                                                        <input type="radio" id="customRadio7" name="tax_type" value="GST" class="form-check-input" {{ (data_get($setting??[], 'tax_type',''))==='GST'?'checked':'' }}>
                                                        <label class="form-check-label" for="customRadio7">{{ __('GST Number') }}</label>
                                                    </div>
                                                </div>
                                            </div>
                                            {{ Form::text('vat_number', data_get($setting??[], 'vat_number'), ['class'=>VC::FM_CT,'placeholder'=>__('Enter VAT / GST Number')]) }}
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer text-end">
                                    <div class="{{ VC::FM_G }}">
                                        <input class="{{ VC::BT_PR_PRM10 }}" type="submit" value="{{ __('Save Changes') }}">
                                    </div>
                                </div>
                            {{ Form::close() }}
                        </div>
                        @php
                            $cpEmailSettingsRouteBaseName=VW::CP.'.email.settings';
                            $cpEmailSettingsKebabRouteName=Str::kebab($cpEmailSettingsRouteBaseName);
                            $cpEmailSettingsResolvedName=Route::has($cpEmailSettingsRouteBaseName)?$cpEmailSettingsRouteBaseName:(Route::has($cpEmailSettingsKebabRouteName)?$cpEmailSettingsKebabRouteName:null);
                            $cpEmailSettingsRouteArray=$cpEmailSettingsResolvedName?[$cpEmailSettingsResolvedName]:['#'];
                            $cpEmailSettingsUrl=$cpEmailSettingsResolvedName?route($cpEmailSettingsResolvedName):'#';
                            $langValue=isset($lang)&&is_string($lang)?$lang:Utility::fetchUserLang();
                            $cpEmailSettingsGuardMsg=Utility::fetchLinkMessage($langValue, VW::CP, 'company_email_settings_route_unavailable')??__('Company email settings route is unavailable. Please contact technical support or your domain administrator.');
                            $cpEmailSettingsFormId='cp-email-settings-form';
                        @endphp
                        <div id="email-settings" class="card">
                            <div class="card-header">
                                <h5>{{ __('Email Settings') }}</h5>
                            </div>
                            {!! Form::model($setting??[],['route'=>$cpEmailSettingsRouteArray,'method'=>'post','id'=>$cpEmailSettingsFormId,'data-url'=>$cpEmailSettingsUrl,'data-guard-msg'=>$cpEmailSettingsGuardMsg]) !!}
                                @push(StacksConstants::ADM_SCR_PG)
                                    <script defer src="{{ asset('assets/js/routes/settings/companies/email.js') }}"></script>
                                @endpush
                                <div class="card-body">
                                    @csrf
                                    <div class="{{ VC::RW }}">
                                        <div class="col-md-4">
                                            <div class="{{ VC::FM_G }}">
                                                {{ Form::label('mail_driver', __('Mail Driver'), ['class'=>VC::FM_LB]) }}
                                                {{ Form::text('mail_driver', old('mail_driver', data_get($emailSetting??[], 'mail_driver','')), ['class'=>VC::FM_CT,'placeholder'=>__('Enter Mail Driver')]) }}
                                                @error('mail_driver')
                                                    <span class="invalid-mail_driver" role="alert"><strong class="text-danger">{{ !empty($message) ? $message : __('No message available') }}</strong></span>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="{{ VC::FM_G }}">
                                                {{ Form::label('mail_host', __('Mail Host'), ['class'=>VC::FM_LB]) }}
                                                {{ Form::text('mail_host', old('mail_host', data_get($emailSetting??[], 'mail_host','')), ['class'=>VC::FM_CT,'placeholder'=>__('Enter Mail Host')]) }}
                                                @error('mail_host')
                                                    <span class="invalid-mail_host" role="alert"><strong class="text-danger">{{ !empty($message) ? $message : __('No message available') }}</strong></span>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="{{ VC::FM_G }}">
                                                {{ Form::label('mail_port', __('Mail Port'), ['class'=>VC::FM_LB]) }}
                                                {{ Form::text('mail_port', old('mail_port', data_get($emailSetting??[], 'mail_port','')), ['class'=>VC::FM_CT,'placeholder'=>__('Enter Mail Port')]) }}
                                                @error('mail_port')
                                                    <span class="invalid-mail_port" role="alert"><strong class="text-danger">{{ !empty($message) ? $message : __('No message available') }}</strong></span>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                    <div class="{{ VC::RW }}">
                                        <div class="col-md-4">
                                            <div class="{{ VC::FM_G }}">
                                                {{ Form::label('mail_username', __('Mail Username'), ['class'=>VC::FM_LB]) }}
                                                {{ Form::text('mail_username', old('mail_username', data_get($emailSetting??[], 'mail_username','')), ['class'=>VC::FM_CT,'placeholder'=>__('Enter Mail Username')]) }}
                                                @error('mail_username')
                                                    <span class="invalid-mail_username" role="alert"><strong class="text-danger">{{ !empty($message) ? $message : __('No message available') }}</strong></span>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="{{ VC::FM_G }}">
                                                {{ Form::label('mail_password', __('Mail Password'), ['class'=>VC::FM_LB]) }}
                                                {{ Form::text('mail_password', old('mail_password', data_get($emailSetting??[], 'mail_password','')), ['class'=>VC::FM_CT,'placeholder'=>__('Enter Mail Password')]) }}
                                                @error('mail_password')
                                                    <span class="invalid-mail_password" role="alert"><strong class="text-danger">{{ !empty($message) ? $message : __('No message available') }}</strong></span>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="{{ VC::FM_G }}">
                                                {{ Form::label('mail_encryption', __('Mail Encryption'), ['class'=>VC::FM_LB]) }}
                                                {{ Form::text('mail_encryption', old('mail_encryption', data_get($emailSetting??[], 'mail_encryption','')), ['class'=>VC::FM_CT,'placeholder'=>__('Enter Mail Encryption')]) }}
                                                @error('mail_encryption')
                                                    <span class="invalid-mail_encryption" role="alert"><strong class="text-danger">{{ !empty($message) ? $message : __('No message available') }}</strong></span>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                    <div class="{{ VC::RW }}">
                                        <div class="col-md-4">
                                            <div class="{{ VC::FM_G }}">
                                                {{ Form::label('mail_from_address', __('Mail From Address'), ['class'=>VC::FM_LB]) }}
                                                {{ Form::email('mail_from_address', old('mail_from_address', data_get($emailSetting??[], 'mail_from_address','')), ['class'=>VC::FM_CT,'placeholder'=>__('Enter Mail From Address')]) }}
                                                @error('mail_from_address')
                                                    <span class="invalid-mail_from_address" role="alert"><strong class="text-danger">{{ !empty($message) ? $message : __('No message available') }}</strong></span>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="{{ VC::FM_G }}">
                                                {{ Form::label('mail_from_name', __('Mail From Name'), ['class'=>VC::FM_LB]) }}
                                                {{ Form::text('mail_from_name', old('mail_from_name', data_get($emailSetting??[], 'mail_from_name','')), ['class'=>VC::FM_CT,'placeholder'=>__('Enter Mail From Name')]) }}
                                                @error('mail_from_name')
                                                    <span class="invalid-mail_from_name" role="alert"><strong class="text-danger">{{ !empty($message) ? $message : __('No message available') }}</strong></span>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="{{ VC::RW }}">
                                    <div class="card-footer {{ VC::DFL }} {{ VC::JCE }}">
                                        <div class="{{ VC::FM_G }} me-2">
                                            @php
                                                $sendTestMailBaseName=VW::TT.'.mail';
                                                $sendTestMailKebabName=Str::kebab($sendTestMailBaseName);
                                                $sendTestMailResolvedName=Route::has($sendTestMailBaseName)?$sendTestMailBaseName:(Route::has($sendTestMailKebabName)?$sendTestMailKebabName:null);
                                                $sendTestMailUrl=$sendTestMailResolvedName?route($sendTestMailResolvedName):'#';
                                                $sendTestMailGuardMsg=Utility::fetchLinkMessage($langValue, VW::TT, 'send_test_mail_route_unavailable')??__('Send test mail route is unavailable. Please contact technical support or your domain administrator.');
                                            @endphp
                                            <a id="send-test-mail-btn" href="{{ $sendTestMailUrl }}" data-url="{{ $sendTestMailUrl }}" data-guard-msg="{{ $sendTestMailGuardMsg }}" data-title="{{ __('Send Test Mail') }}" class="{{ VC::BT_PRM }} send_email">{{ __('Send Test Mail') }}</a>
                                            @push(StacksConstants::ADM_SCR_PG)
                                                <script defer src="{{ asset('assets/js/routes/settings/companies/emailTest.js') }}"></script>
                                            @endpush
                                        </div>
                                        <div class="{{ VC::FM_G }}">
                                            <input class="{{ VC::BT_PRM }}" type="submit" value="{{ __('Save Changes') }}">
                                        </div>
                                    </div>
                                </div>
                            {{ Form::close() }}
                        </div>
                        @php
                            $timeTrackersSettingsBaseName=VW::TMT.'.settings';
                            $timeTrackersSettingsKebabName=Str::kebab($timeTrackersSettingsBaseName);
                            $timeTrackersSettingsResolvedName=Route::has($timeTrackersSettingsBaseName)?$timeTrackersSettingsBaseName:(Route::has($timeTrackersSettingsKebabName)?$timeTrackersSettingsKebabName:null);
                            $timeTrackersSettingsRouteArray=$timeTrackersSettingsResolvedName?[$timeTrackersSettingsResolvedName]:['#'];
                            $timeTrackersSettingsUrl=$timeTrackersSettingsResolvedName?route($timeTrackersSettingsResolvedName):'#';
                            $langValue=isset($lang)&&is_string($lang)?$lang:Utility::fetchUserLang();
                            $timeTrackersSettingsGuardMsg=Utility::fetchLinkMessage($langValue, VW::TMT, 'time_trackers_settings_route_unavailable')??__('Time trackers settings route is unavailable. Please contact technical support or your domain administrator.');
                            $timeTrackersSettingsFormId='time-trackers-settings-form';
                        @endphp
                        <div id="tracker-settings" class="card">
                            <div class="card-header">
                                <h5>{{ __('Time Tracker Settings') }}</h5>
                                <small class="text-muted">{{ __('Edit your Time Tracker settings') }}</small>
                            </div>
                            {!! Form::model($setting??[],['route'=>$timeTrackersSettingsRouteArray,'method'=>'post','id'=>$timeTrackersSettingsFormId,'data-url'=>$timeTrackersSettingsUrl,'data-guard-msg'=>$timeTrackersSettingsGuardMsg]) !!}
                                @push(StacksConstants::ADM_SCR_PG)
                                    <script defer src="{{ asset('assets/js/routes/settings/companies/tracker.js') }}"></script>
                                @endpush
                                <div class="card-body">
                                    <div class="{{ VC::RW }}">
                                        <div class="{{ VC::FM_G }} {{ VC::CM6 }}">
                                            <label class="{{ VC::FM_LB }}">{{ __('Application URL') }}</label>
                                            <small>{{ __('Application URL to log into the app.') }}</small>
                                            {{ Form::text('apps_url', old('apps_url', URL::to('/')), ['class'=>VC::FM_CT,'placeholder'=>__('Application URL'),'readonly'=>true]) }}
                                        </div>
                                        <div class="{{ VC::FM_G }} {{ VC::CM6 }}">
                                            <label class="{{ VC::FM_LB }}">{{ __('Tracking Interval') }}</label>
                                            <small>{{ __('Image Screenshot Take Interval time ( 1 = 1 min)') }}</small>
                                            {{ Form::number('interval_time', old('interval_time', data_get($setting??[], 'interval_time','10')), ['class'=>VC::FM_CT,'placeholder'=>__('Enter Tracking Interval Time'),'min'=>1,'step'=>1]) }}
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer {{ VC::DFL }} {{ VC::JCE }}">
                                    <div class="{{ VC::FM_G }}">
                                        <input class="{{ VC::BT_PR_PRM10 }}" type="submit" value="{{ __('Save Changes') }}">
                                    </div>
                                </div>
                            {{ Form::close() }}
                        </div>
                        @php
                            $cpPaymentSettingsBaseRouteName=VW::CP.'.payment.settings';
                            $cpPaymentSettingsKebabRouteName=Str::kebab($cpPaymentSettingsBaseRouteName);
                            $cpPaymentSettingsResolvedRouteName=Route::has($cpPaymentSettingsBaseRouteName)?$cpPaymentSettingsBaseRouteName:(Route::has($cpPaymentSettingsKebabRouteName)?$cpPaymentSettingsKebabRouteName:null);
                            $cpPaymentSettingsRouteArray=$cpPaymentSettingsResolvedRouteName?[$cpPaymentSettingsResolvedRouteName]:['#'];
                            $cpPaymentSettingsUrl=$cpPaymentSettingsResolvedRouteName?route($cpPaymentSettingsResolvedRouteName):'#';
                            $langValue=isset($lang)&&is_string($lang)?$lang:Utility::fetchUserLang();
                            $cpPaymentSettingsGuardMsg=Utility::fetchLinkMessage($langValue, VW::CP, 'company_payment_settings_route_unavailable')??__('Company payment settings route is unavailable. Please contact technical support or your domain administrator.');
                            $cpPaymentSettingsFormId='cp-payment-settings-form';
                        @endphp
                        <div class="card" id="payment-settings">
                            <div class="card-header">
                                <h5>{{ __('Payment Settings') }}</h5>
                                <small class="text-secondary font-weight-bold">{{ __('These details will be used to collect invoice payments. Each invoice will have a payment button based on the below configuration.') }}</small>
                            </div>
                            {!! Form::model($setting??[],['route'=>$cpPaymentSettingsRouteArray,'method'=>'POST','id'=>$cpPaymentSettingsFormId,'data-url'=>$cpPaymentSettingsUrl,'data-guard-msg'=>$cpPaymentSettingsGuardMsg]) !!}
                                @push(StacksConstants::ADM_SCR_PG)
                                    <script defer src="{{ asset('assets/js/routes/settings/companies/payment.js') }}"></script>
                                @endpush
                                @csrf
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-12">
                                            <div class="faq justify-content-center">
                                                <div class="row">
                                                    <div class="col-12">
                                                        @php
                                                            $gateways=[
                                                                ['key'=>'bank','title'=>__('Bank Transfer'),'enabled'=>'is_bank_transfer_enabled','fields'=>[['name'=>'bank_details','type'=>'textarea','label'=>__('Bank Details'),'rows'=>4,'col'=>'col-lg-12','placeholder'=>__('Enter Your Bank Details'),'hint'=>__('Example : Bank : bank name </br> Account Number : 0000 0000 </br>')]]],
                                                                ['key'=>'stripe','title'=>__('Stripe'),'enabled'=>'is_stripe_enabled','fields'=>[['name'=>'stripe_key','label'=>__('Stripe Key'),'type'=>'text','col'=>'col-lg-6'],['name'=>'stripe_secret','label'=>__('Stripe Secret'),'type'=>'password','col'=>'col-lg-6']]],
                                                                ['key'=>'paypal','title'=>__('Paypal'),'enabled'=>'is_paypal_enabled','radios'=>['name'=>'paypal_mode','options'=>['sandbox'=>__('Sandbox'),'live'=>__('Live')],'default'=>'sandbox'],'fields'=>[['name'=>'paypal_client_id','label'=>__('Client ID'),'type'=>'text','col'=>'col-lg-6'],['name'=>'paypal_secret_key','label'=>__('Secret Key'),'type'=>'password','col'=>'col-lg-6']]],
                                                                ['key'=>'paystack','title'=>__('Paystack'),'enabled'=>'is_paystack_enabled','fields'=>[['name'=>'paystack_public_key','label'=>__('Public Key'),'type'=>'text','col'=>'col-lg-6'],['name'=>'paystack_secret_key','label'=>__('Secret Key'),'type'=>'password','col'=>'col-lg-6']]],
                                                                ['key'=>'flutterwave','title'=>__('Flutterwave'),'enabled'=>'is_flutterwave_enabled','fields'=>[['name'=>'flutterwave_public_key','label'=>__('Public Key'),'type'=>'text','col'=>'col-lg-6'],['name'=>'flutterwave_secret_key','label'=>__('Secret Key'),'type'=>'password','col'=>'col-lg-6']]],
                                                                ['key'=>'razorpay','title'=>__('Razorpay'),'enabled'=>'is_razorpay_enabled','fields'=>[['name'=>'razorpay_public_key','label'=>__('Public Key'),'type'=>'text','col'=>'col-lg-6'],['name'=>'razorpay_secret_key','label'=>__('Secret Key'),'type'=>'password','col'=>'col-lg-6']]],
                                                                ['key'=>'paytm','title'=>__('Paytm'),'enabled'=>'is_paytm_enabled','radios'=>['name'=>'paytm_mode','options'=>['local'=>__('Local'),'production'=>__('Production')],'default'=>'local'],'fields'=>[['name'=>'paytm_merchant_id','label'=>__('Merchant ID'),'type'=>'text','col'=>'col-lg-4'],['name'=>'paytm_merchant_key','label'=>__('Merchant Key'),'type'=>'password','col'=>'col-lg-4'],['name'=>'paytm_industry_type','label'=>__('Industry Type'),'type'=>'text','col'=>'col-lg-4']]],
                                                                ['key'=>'mercado','title'=>__('Mercado Pago'),'enabled'=>'is_mercado_enabled','radios'=>['name'=>'mercado_mode','options'=>['sandbox'=>__('Sandbox'),'live'=>__('Live')],'default'=>'sandbox'],'fields'=>[['name'=>'mercado_access_token','label'=>__('Access Token'),'type'=>'password','col'=>'col-lg-6']]],
                                                                ['key'=>'mollie','title'=>__('Mollie'),'enabled'=>'is_mollie_enabled','fields'=>[['name'=>'mollie_api_key','label'=>__('Mollie API Key'),'type'=>'password','col'=>'col-lg-6'],['name'=>'mollie_profile_id','label'=>__('Mollie Profile ID'),'type'=>'text','col'=>'col-lg-6'],['name'=>'mollie_partner_id','label'=>__('Mollie Partner ID'),'type'=>'text','col'=>'col-lg-6']]],
                                                                ['key'=>'skrill','title'=>__('Skrill'),'enabled'=>'is_skrill_enabled','fields'=>[['name'=>'skrill_email','label'=>__('Skrill Email'),'type'=>'email','col'=>'col-lg-6','attrs'=>['autocomplete'=>'email','inputmode'=>'email']]]],
                                                                ['key'=>'coingate','title'=>__('CoinGate'),'enabled'=>'is_coingate_enabled','radios'=>['name'=>'coingate_mode','options'=>['sandbox'=>__('Sandbox'),'live'=>__('Live')],'default'=>'sandbox'],'fields'=>[['name'=>'coingate_auth_token','label'=>__('CoinGate Auth Token'),'type'=>'password','col'=>'col-lg-6']]],
                                                                ['key'=>'paymentwall','title'=>__('PaymentWall'),'enabled'=>'is_paymentwall_enabled','fields'=>[['name'=>'paymentwall_public_key','label'=>__('Public Key'),'type'=>'text','col'=>'col-lg-6'],['name'=>'paymentwall_secret_key','label'=>__('Private Key'),'type'=>'password','col'=>'col-lg-6']]],
                                                                ['key'=>'toyyibpay','title'=>__('Toyyibpay'),'enabled'=>'is_toyyibpay_enabled','fields'=>[['name'=>'toyyibpay_category_code','label'=>__('Category Key'),'type'=>'text','col'=>'col-lg-6'],['name'=>'toyyibpay_secret_key','label'=>__('Secret Key'),'type'=>'password','col'=>'col-lg-6']]],
                                                                ['key'=>'payfast','title'=>__('PayFast'),'enabled'=>'is_payfast_enabled','radios'=>['name'=>'payfast_mode','options'=>['sandbox'=>__('Sandbox'),'live'=>__('Live')],'default'=>'sandbox'],'fields'=>[['name'=>'payfast_merchant_id','label'=>__('Merchant ID'),'type'=>'text','col'=>'col-lg-4'],['name'=>'payfast_merchant_key','label'=>__('Merchant Key'),'type'=>'password','col'=>'col-lg-4'],['name'=>'payfast_signature','label'=>__('Salt Passphrase'),'type'=>'password','col'=>'col-lg-4']]],
                                                                ['key'=>'iyzipay','title'=>__('Iyzipay'),'enabled'=>'is_iyzipay_enabled','radios'=>['name'=>'iyzipay_mode','options'=>['sandbox'=>__('Sandbox'),'live'=>__('Live')],'default'=>'sandbox'],'fields'=>[['name'=>'iyzipay_public_key','label'=>__('Public Key'),'type'=>'text','col'=>'col-lg-6'],['name'=>'iyzipay_secret_key','label'=>__('Secret Key'),'type'=>'password','col'=>'col-lg-6']]],
                                                                ['key'=>'sspay','title'=>__('SSpay'),'enabled'=>'is_sspay_enabled','fields'=>[['name'=>'sspay_category_code','label'=>__('Category Code'),'type'=>'text','col'=>'col-lg-6'],['name'=>'sspay_secret_key','label'=>__('Secret Key'),'type'=>'password','col'=>'col-lg-6']]],
                                                                ['key'=>'paytab','title'=>__('PayTab'),'enabled'=>'is_paytab_enabled','fields'=>[['name'=>'paytab_profile_id','label'=>__('Profile Id'),'type'=>'text','col'=>'col-lg-6'],['name'=>'paytab_server_key','label'=>__('Server Key'),'type'=>'password','col'=>'col-lg-6'],['name'=>'paytab_region','label'=>__('Region'),'type'=>'text','col'=>'col-lg-6']]],
                                                                ['key'=>'benefit','title'=>__('Benefit'),'enabled'=>'is_benefit_enabled','fields'=>[['name'=>'benefit_api_key','label'=>__('Benefit Key'),'type'=>'text','col'=>'col-lg-6'],['name'=>'benefit_secret_key','label'=>__('Benefit Secret Key'),'type'=>'password','col'=>'col-lg-6']]],
                                                                ['key'=>'cashfree','title'=>__('Cashfree'),'enabled'=>'is_cashfree_enabled','fields'=>[['name'=>'cashfree_api_key','label'=>__('Cashfree Key'),'type'=>'text','col'=>'col-lg-6'],['name'=>'cashfree_secret_key','label'=>__('Cashfree Secret Key'),'type'=>'password','col'=>'col-lg-6']]],
                                                                ['key'=>'aamarpay','title'=>__('Aamarpay'),'enabled'=>'is_aamarpay_enabled','fields'=>[['name'=>'aamarpay_store_id','label'=>__('Store Id'),'type'=>'text','col'=>'col-lg-6'],['name'=>'aamarpay_signature_key','label'=>__('Signature Key'),'type'=>'password','col'=>'col-lg-6'],['name'=>'aamarpay_description','label'=>__('Description'),'type'=>'text','col'=>'col-lg-6']]],
                                                                ['key'=>'paytr','title'=>__('PayTR'),'enabled'=>'is_paytr_enabled','fields'=>[['name'=>'paytr_merchant_id','label'=>__('Merchant Id'),'type'=>'text','col'=>'col-lg-4'],['name'=>'paytr_merchant_key','label'=>__('Merchant Key'),'type'=>'password','col'=>'col-lg-4'],['name'=>'paytr_merchant_salt','label'=>__('Merchant Salt'),'type'=>'password','col'=>'col-lg-4']]],
                                                                ['key'=>'yookassa','title'=>__('Yookassa'),'enabled'=>'is_yookassa_enabled','fields'=>[['name'=>'yookassa_shop_id','label'=>__('Shop ID Key'),'type'=>'text','col'=>'col-lg-6'],['name'=>'yookassa_secret','label'=>__('Secret Key'),'type'=>'password','col'=>'col-lg-6']]],
                                                                ['key'=>'midtrans','title'=>__('Midtrans'),'enabled'=>'is_midtrans_enabled','fields'=>[['name'=>'midtrans_secret','label'=>__('Secret Key'),'type'=>'password','col'=>'col-lg-6']]],
                                                                ['key'=>'xendit','title'=>__('Xendit'),'enabled'=>'is_xendit_enabled','fields'=>[['name'=>'xendit_api','label'=>__('API Key'),'type'=>'password','col'=>'col-lg-6'],['name'=>'xendit_token','label'=>__('Token'),'type'=>'password','col'=>'col-lg-6']]],
                                                            ];
                                                        @endphp
                                                        <div class="accordion accordion-flush setting-accordion" id="accordionExample">
                                                            @if(is_iterable($gateways??[]) && (is_array($gateways)?count($gateways):count($gateways)))
                                                                @foreach($gateways as $gw)
                                                                    @php
                                                                        $enabledKey=data_get($gw,'enabled');
                                                                        $isEnabled=old($enabledKey, data_get($company_payment_setting??[], $enabledKey,'off'))==='on';
                                                                        $key=data_get($gw,'key','gw');
                                                                        $headingId="heading_{$key}";
                                                                        $collapseId="collapse_{$key}";
                                                                        $switchId="switch_{$enabledKey}";
                                                                    @endphp
                                                                    <div class="accordion-item">
                                                                        <h2 class="accordion-header" id="{{ $headingId }}">
                                                                            <button class="accordion-button {{ $isEnabled?'':'collapsed' }}" type="button" data-bs-toggle="collapse" data-bs-target="#{{ $collapseId }}" aria-expanded="{{ $isEnabled?'true':'false' }}" aria-controls="{{ $collapseId }}">
                                                                                <span class="{{ VC::DFL_AIC }}">{{ data_get($gw,'title',__('No gateway title available')) }}</span>
                                                                                <div class="{{ VC::DFL_AIC }}">
                                                                                    <span class="me-2">{{ __('Enable') }}:</span>
                                                                                    <div class="form-check form-switch custom-switch-v1">
                                                                                        <input type="hidden" name="{{ $enabledKey }}" value="off">
                                                                                        <input type="checkbox" class="form-check-input input-primary" id="{{ $switchId }}" name="{{ $enabledKey }}" @checked($isEnabled)>
                                                                                    </div>
                                                                                </div>
                                                                            </button>
                                                                        </h2>
                                                                        <div id="{{ $collapseId }}" class="accordion-collapse collapse {{ $isEnabled?'show':'' }}" aria-labelledby="{{ $headingId }}" data-bs-parent="#accordionExample">
                                                                            <div class="accordion-body">
                                                                                @if(data_get($gw,'radios'))
                                                                                    @php
                                                                                        $rName=data_get($gw,'radios.name');
                                                                                        $rDefault=data_get($gw,'radios.default');
                                                                                        $rValue=old($rName, data_get($company_payment_setting??[], $rName, $rDefault));
                                                                                        $options=data_get($gw,'radios.options',[]);
                                                                                    @endphp
                                                                                    <div class="{{ VC::C12 }} {{ VC::MB4 }}">
                                                                                        <label class="{{ VC::FM_LB }}" for="{{ $rName }}">{{ Str::headline(str_replace('_',' ',$rName)) }}</label>
                                                                                        <div class="{{ VC::DFL }}">
                                                                                            @foreach($options as $val=>$label)
                                                                                                <div class="me-2" style="margin-right: 15px;">
                                                                                                    <div class="{{ VC::BD }} {{ VC::CD }} {{ VC::P4 }}">
                                                                                                        <div class="form-check">
                                                                                                            <label class="form-check-label text-dark me-2">
                                                                                                                <input type="radio" class="form-check-input" name="{{ $rName }}" value="{{ $val }}" {{ $rValue===$val?'checked':'' }}>
                                                                                                                {{ $label }}
                                                                                                            </label>
                                                                                                        </div>
                                                                                                    </div>
                                                                                                </div>
                                                                                            @endforeach
                                                                                        </div>
                                                                                    </div>
                                                                                @endif
                                                                                <div class="{{ VC::RW }} gy-4">
                                                                                    @foreach((array) data_get($gw,'fields',[]) as $f)
                                                                                        @php
                                                                                            $name=data_get($f,'name');
                                                                                            $type=data_get($f,'type','text');
                                                                                            $label=data_get($f,'label',Str::headline(str_replace('_',' ',$name)));
                                                                                            $col=data_get($f,'col','col-lg-6');
                                                                                            $val=old($name, data_get($company_payment_setting??[], $name, data_get($f,'value','')));
                                                                                            $attrs=array_merge(['class'=>VC::FM_CT,'placeholder'=>data_get($f,'placeholder',$label)], (array) data_get($f,'attrs',[]));
                                                                                            if($type==='password'){ $val=null; $attrs['autocomplete']='off'; $attrs['spellcheck']='false'; }
                                                                                        @endphp
                                                                                        <div class="{{ $col }}">
                                                                                            <div class="input-edits">
                                                                                                <div class="{{ VC::FM_G }}">
                                                                                                    {{ Form::label($name, $label, ['class'=>VC::FM_LB]) }}
                                                                                                    @switch($type)
                                                                                                        @case('textarea')
                                                                                                            {{ Form::textarea($name, $val, array_merge($attrs,['rows'=>data_get($f,'rows',4)])) }}
                                                                                                            @break
                                                                                                        @case('number')
                                                                                                            {{ Form::number($name, $val, $attrs) }}
                                                                                                            @break
                                                                                                        @case('password')
                                                                                                            {{ Form::password($name, $attrs) }}
                                                                                                            @break
                                                                                                        @case('email')
                                                                                                            {{ Form::email($name, $val, $attrs) }}
                                                                                                            @break
                                                                                                        @default
                                                                                                            {{ Form::text($name, $val, $attrs) }}
                                                                                                    @endswitch
                                                                                                    @isset($f['hint'])
                                                                                                        <small class="{{ VC::TXS }}">{!! $f['hint'] !!}</small>
                                                                                                    @endisset
                                                                                                    @error($name)
                                                                                                        <span class="invalid-feedback {{ VC::DBL }}">{{ $message }}</span>
                                                                                                    @enderror
                                                                                                </div>
                                                                                            </div>
                                                                                        </div>
                                                                                    @endforeach
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                @endforeach
                                                            @else
                                                                <div class="alert alert-warning">{{ __('No payment gateways found.') }}</div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer text-end">
                                    <div class="form-group">
                                        <input class="{{ VC::BT_PR_PRM10 }}" type="submit" value="{{ __('Save Changes') }}">
                                    </div>
                                </div>
                            {{ Form::close() }}
                        </div>
                        @php
                            $zoomSettingsBaseRouteName='zoom.settings';
                            $zoomSettingsKebabRouteName=Str::kebab($zoomSettingsBaseRouteName);
                            $zoomSettingsResolvedName=Route::has($zoomSettingsBaseRouteName)?$zoomSettingsBaseRouteName:(Route::has($zoomSettingsKebabRouteName)?$zoomSettingsKebabRouteName:null);
                            $zoomSettingsRouteArray=$zoomSettingsResolvedName?[$zoomSettingsResolvedName]:['#'];
                            $zoomSettingsUrl=$zoomSettingsResolvedName?route($zoomSettingsResolvedName):'#';
                            $langValue=isset($lang)&&is_string($lang)?$lang:Utility::fetchUserLang();
                            $zoomSettingsGuardMsg=Utility::fetchLinkMessage($langValue,'zoom','zoom_settings_route_unavailable')??__('Zoom settings route is unavailable. Please contact technical support or your domain administrator.');
                            $zoomSettingsFormId='zoom-settings-form';
                        @endphp
                        <div id="zoom-settings" class="card">
                            <div class="card-header">
                                <h5>{{ __('Zoom Settings') }}</h5>
                                <small class="text-muted">{{ __('Edit your Zoom settings') }}</small>
                            </div>
                            {!! Form::model($setting??[],['route'=>$zoomSettingsRouteArray,'method'=>'post','id'=>$zoomSettingsFormId,'data-url'=>$zoomSettingsUrl,'data-guard-msg'=>$zoomSettingsGuardMsg]) !!}
                                @push(StacksConstants::ADM_SCR_PG)
                                    <script defer src="{{ asset('assets/js/routes/settings/companies/zoom.js') }}"></script>
                                @endpush
                                @csrf
                                <div class="card-body">
                                    <div class="{{ VC::RW }}">
                                        <div class="{{ VC::FM_G }} {{ VC::CM6 }}">
                                            <label class="{{ VC::FM_LB }}">{{ __('Zoom Account ID') }}</label>
                                            {{ Form::text('zoom_account_id', old('zoom_account_id', data_get($setting??[], 'zoom_account_id','')), ['class'=>VC::FM_CT,'placeholder'=>__('Enter Zoom Account ID')]) }}
                                            @error('zoom_account_id')
                                                <span class="invalid-feedback {{ VC::DBL }}">{{ $message ?? __('No message available') }}</span>
                                            @enderror
                                        </div>
                                        <div class="{{ VC::FM_G }} {{ VC::CM6 }}">
                                            <label class="{{ VC::FM_LB }}">{{ __('Zoom Client ID') }}</label>
                                            {{ Form::text('zoom_client_id', old('zoom_client_id', data_get($setting??[], 'zoom_client_id','')), ['class'=>VC::FM_CT,'placeholder'=>__('Enter Zoom Client ID')]) }}
                                            @error('zoom_client_id')
                                                <span class="invalid-feedback {{ VC::DBL }}">{{ $message ?? __('No message available') }}</span>
                                            @enderror
                                        </div>
                                        <div class="{{ VC::FM_G }} {{ VC::CM6 }}">
                                            <label class="{{ VC::FM_LB }}">{{ __('Zoom Client Secret Key') }}</label>
                                            {{ Form::password('zoom_client_secret', ['class'=>VC::FM_CT,'placeholder'=>__('Enter Zoom Client Secret Key'),'autocomplete'=>'off','spellcheck'=>'false']) }}
                                            @error('zoom_client_secret')
                                                <span class="invalid-feedback {{ VC::DBL }}">{{ $message ?? __('No message available') }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer {{ VC::DFL }} {{ VC::JCE }}">
                                    <div class="{{ VC::FM_G }}">
                                        <input class="{{ VC::BT_PR_PRM10 }}" type="submit" value="{{ __('Save Changes') }}">
                                    </div>
                                </div>
                            {{ Form::close() }}
                        </div>
                        @php
                            $slackSettingsBaseRouteName='slack.settings';
                            $slackSettingsKebabRouteName=Str::kebab($slackSettingsBaseRouteName);
                            $slackSettingsResolvedRouteName=Route::has($slackSettingsBaseRouteName)?$slackSettingsBaseRouteName:(Route::has($slackSettingsKebabRouteName)?$slackSettingsKebabRouteName:null);
                            $slackSettingsRouteArray=$slackSettingsResolvedRouteName?[$slackSettingsResolvedRouteName]:['#'];
                            $slackSettingsUrl=$slackSettingsResolvedRouteName?route($slackSettingsResolvedRouteName):'#';
                            $langValue=isset($lang)&&is_string($lang)?$lang:Utility::fetchUserLang();
                            $slackSettingsGuardMsg=Utility::fetchLinkMessage($langValue,'slack','slack_settings_route_unavailable')??__('Slack settings route is unavailable. Please contact technical support or your domain administrator.');
                            $slackSettingsFormId='slack-settings-form';
                            $groups=[
                                [['name'=>'lead_notification','label'=>__('New Lead')],['name'=>'deal_notification','label'=>__('New Deal')]],
                                [['name'=>'leadtodeal_notification','label'=>__('Lead to Deal Conversion')],['name'=>'contract_notification','label'=>__('New Contract')]],
                                [['name'=>'project_notification','label'=>__('New Project')],['name'=>'task_notification','label'=>__('New Task')]],
                                [['name'=>'taskmove_notification','label'=>__('Task Stage Updated')],['name'=>'taskcomment_notification','label'=>__('New Task Comment')]],
                                [['name'=>'payslip_notification','label'=>__('New Monthly Payslip')],['name'=>'award_notification','label'=>__('New Award')]],
                                [['name'=>'announcement_notification','label'=>__('New Announcement')],['name'=>'holiday_notification','label'=>__('New Holiday')]],
                                [['name'=>'support_notification','label'=>__('New Support Ticket')],['name'=>'event_notification','label'=>__('New Event')]],
                                [['name'=>'meeting_notification','label'=>__('New Meeting')],['name'=>'policy_notification','label'=>__('New Company Policy')]],
                                [['name'=>'invoice_notification','label'=>__('New Invoice')],['name'=>'revenue_notification','label'=>__('New Revenue')]],
                                [['name'=>'bill_notification','label'=>__('New Bill')],['name'=>'payment_notification','label'=>__('New Invoice Payment')]],
                                [['name'=>'budget_notification','label'=>__('New Budget')]],
                            ];
                        @endphp
                        <div id="slack-settings" class="card">
                            <div class="card-header">
                                <h5>{{ __('Slack Settings') }}</h5>
                                <small class="text-muted">{{ __('Edit your Slack settings') }}</small>
                            </div>
                            {!! Form::open(['route'=>$slackSettingsRouteArray,'id'=>$slackSettingsFormId,'method'=>'post','class'=>'d-contents','data-url'=>$slackSettingsUrl,'data-guard-msg'=>$slackSettingsGuardMsg]) !!}
                                @push(StacksConstants::ADM_SCR_PG)
                                    <script defer src="{{ asset('assets/js/routes/settings/companies/slack.js') }}"></script>
                                @endpush
                                @csrf
                                <div class="card-body">
                                    <div class="{{ VC::FM_G }} {{ VC::C12 }}">
                                        <label class="{{ VC::FM_LB }}">{{ __('Slack Webhook URL') }}</label>
                                        {{ Form::url('slack_webhook', old('slack_webhook', data_get($setting??[], 'slack_webhook','')), ['class'=>VC::FM_CT.' w-100','placeholder'=>__('Enter Slack Webhook URL'),'required'=>true]) }}
                                        @error('slack_webhook')
                                            <span class="invalid-feedback {{ VC::DBL }}">{{ $message ?? __('No message available') }}</span>
                                        @enderror
                                    </div>
                                    <div class="{{ VC::C12 }} mt-5 mb-2">
                                        <h5 class="small-title">{{ __('Module Settings') }}</h5>
                                    </div>
                                    <div class="{{ VC::RW }}">
                                        @foreach(Utility::isFilled($groups) && is_array($groups)?array_chunk($groups,2):[] as $chunk)
                                            <div class="{{ VC::CM3 }}">
                                                <ul class="{{ VC::LGRP }}">
                                                    @foreach(($chunk[0]??[]) as $item)
                                                        @php
                                                            $n=data_get($item,'name');
                                                            $checked=old($n, data_get($setting??[], $n,'0'))=='1';
                                                        @endphp
                                                        <li class="{{ VC::LGI }}">
                                                            <div class="form-switch form-switch-right">
                                                                <span>{{ data_get($item,'label',__('No label available')) }}</span>
                                                                {{ Form::hidden($n,'0') }}
                                                                {{ Form::checkbox($n,'1',$checked,['class'=>'form-check-input','id'=>$n]) }}
                                                                <label class="form-check-label" for="{{ $n }}"></label>
                                                            </div>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                            <div class="{{ VC::CM3 }}">
                                                <ul class="{{ VC::LGRP }}">
                                                    @foreach(($chunk[1]??[]) as $item)
                                                        @php
                                                            $n=data_get($item,'name');
                                                            $checked=old($n, data_get($setting??[], $n,'0'))=='1';
                                                        @endphp
                                                        <li class="{{ VC::LGI }}">
                                                            <div class="form-switch form-switch-right">
                                                                <span>{{ data_get($item,'label',__('No label available')) }}</span>
                                                                {{ Form::hidden($n,'0') }}
                                                                {{ Form::checkbox($n,'1',$checked,['class'=>'form-check-input','id'=>$n]) }}
                                                                <label class="form-check-label" for="{{ $n }}"></label>
                                                            </div>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                                <div class="card-footer {{ VC::DFL }} {{ VC::JCE }}">
                                    <div class="{{ VC::FM_G }}">
                                        <input class="{{ VC::BT_PR_PRM10 }}" type="submit" value="{{ __('Save Changes') }}">
                                    </div>
                                </div>
                            {{ Form::close() }}
                        </div>
                        @php
                            $telegramSettingsBaseRouteName='telegram.settings';
                            $telegramSettingsKebabRouteName=Str::kebab($telegramSettingsBaseRouteName);
                            $telegramSettingsResolvedRouteName=Route::has($telegramSettingsBaseRouteName)?$telegramSettingsBaseRouteName:(Route::has($telegramSettingsKebabRouteName)?$telegramSettingsKebabRouteName:null);
                            $telegramSettingsRouteArray=$telegramSettingsResolvedRouteName?[$telegramSettingsResolvedRouteName]:['#'];
                            $telegramSettingsUrl=$telegramSettingsResolvedRouteName?route($telegramSettingsResolvedRouteName):'#';
                            $langValue=isset($lang)&&is_string($lang)?$lang:Utility::fetchUserLang();
                            $telegramSettingsGuardMsg=Utility::fetchLinkMessage($langValue,'telegram','telegram_settings_route_unavailable')??__('Telegram settings route is unavailable. Please contact technical support or your domain administrator.');
                            $telegramSettingsFormId='telegram-settings-form';
                            $groups=[
                                [['name'=>'telegram_lead_notification','label'=>__('New Lead')],['name'=>'telegram_deal_notification','label'=>__('New Deal')]],
                                [['name'=>'telegram_leadtodeal_notification','label'=>__('Lead to Deal Conversion')],['name'=>'telegram_contract_notification','label'=>__('New Contract')]],
                                [['name'=>'telegram_project_notification','label'=>__('New Project')],['name'=>'telegram_task_notification','label'=>__('New Task')]],
                                [['name'=>'telegram_taskmove_notification','label'=>__('Task Stage Updated')],['name'=>'telegram_taskcomment_notification','label'=>__('New Task Comment')]],
                                [['name'=>'telegram_payslip_notification','label'=>__('New Monthly Payslip')],['name'=>'telegram_award_notification','label'=>__('New Award')]],
                                [['name'=>'telegram_announcement_notification','label'=>__('New Announcement')],['name'=>'telegram_holiday_notification','label'=>__('New Holiday')]],
                                [['name'=>'telegram_support_notification','label'=>__('New Support Ticket')],['name'=>'telegram_event_notification','label'=>__('New Event')]],
                                [['name'=>'telegram_meeting_notification','label'=>__('New Meeting')],['name'=>'telegram_policy_notification','label'=>__('New Company Policy')]],
                                [['name'=>'telegram_invoice_notification','label'=>__('New Invoice')],['name'=>'telegram_revenue_notification','label'=>__('New Revenue')]],
                                [['name'=>'telegram_bill_notification','label'=>__('New Bill')],['name'=>'telegram_payment_notification','label'=>__('New Invoice Payment')]],
                                [['name'=>'telegram_budget_notification','label'=>__('New Budget')]],
                            ];
                        @endphp
                        <div id="telegram-settings" class="card">
                            <div class="card-header">
                                <h5>{{ __('Telegram Settings') }}</h5>
                                <small class="text-muted">{{ __('Edit your Telegram settings') }}</small>
                            </div>
                            {!! Form::open(['route'=>$telegramSettingsRouteArray,'id'=>$telegramSettingsFormId,'method'=>'post','class'=>'d-contents','data-url'=>$telegramSettingsUrl,'data-guard-msg'=>$telegramSettingsGuardMsg]) !!}
                                @push(StacksConstants::ADM_SCR_PG)
                                    <script defer src="{{ asset('assets/js/routes/settings/companies/telegram.js') }}"></script>
                                @endpush
                                @csrf
                                <div class="card-body">
                                    <div class="{{ VC::RW }}">
                                        <div class="{{ VC::FM_G }} {{ VC::CM6 }}">
                                            <label class="{{ VC::FM_LB }}">{{ __('Telegram AccessToken') }}</label>
                                            {{ Form::text('telegram_accesstoken', old('telegram_accesstoken', data_get($setting??[], 'telegram_accesstoken','')), ['class'=>VC::FM_CT,'placeholder'=>__('Enter Telegram AccessToken'),'autocomplete'=>'off','spellcheck'=>'false']) }}
                                            @error('telegram_accesstoken')
                                                <span class="invalid-feedback {{ VC::DBL }}">{{ $message ?? __('No message available') }}</span>
                                            @enderror
                                        </div>
                                        <div class="{{ VC::FM_G }} {{ VC::CM6 }}">
                                            <label class="{{ VC::FM_LB }}">{{ __('Telegram ChatID') }}</label>
                                            {{ Form::text('telegram_chatid', old('telegram_chatid', data_get($setting??[], 'telegram_chatid','')), ['class'=>VC::FM_CT,'placeholder'=>__('Enter Telegram ChatID')]) }}
                                            @error('telegram_chatid')
                                                <span class="invalid-feedback {{ VC::DBL }}">{{ $message ?? __('No message available') }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="{{ VC::C12 }} mt-5 mb-2">
                                        <h5 class="small-title">{{ __('Module Settings') }}</h5>
                                    </div>
                                    <div class="{{ VC::RW }}">
                                        @foreach(Utility::isFilled($groups) && is_array($groups) ?array_chunk($groups,2):[] as $chunk)
                                            <div class="{{ VC::CM3 }}">
                                                <ul class="{{ VC::LGRP }}">
                                                    @foreach(($chunk[0]??[]) as $item)
                                                        @php
                                                            $n=data_get($item,'name');
                                                            $checked=old($n, data_get($setting??[], $n,'0'))=='1';
                                                        @endphp
                                                        <li class="{{ VC::LGI }}">
                                                            <div class="form-switch form-switch-right">
                                                                <span>{{ data_get($item,'label',__('No label available')) }}</span>
                                                                {{ Form::hidden($n,'0') }}
                                                                {{ Form::checkbox($n,'1',$checked,['class'=>'form-check-input','id'=>$n]) }}
                                                                <label class="form-check-label" for="{{ $n }}"></label>
                                                            </div>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                            <div class="{{ VC::CM3 }}">
                                                <ul class="{{ VC::LGRP }}">
                                                    @foreach(($chunk[1]??[]) as $item)
                                                        @php
                                                            $n=data_get($item,'name');
                                                            $checked=old($n, data_get($setting??[], $n,'0'))=='1';
                                                        @endphp
                                                        <li class="{{ VC::LGI }}">
                                                            <div class="form-switch form-switch-right">
                                                                <span>{{ data_get($item,'label',__('No label available')) }}</span>
                                                                {{ Form::hidden($n,'0') }}
                                                                {{ Form::checkbox($n,'1',$checked,['class'=>'form-check-input','id'=>$n]) }}
                                                                <label class="form-check-label" for="{{ $n }}"></label>
                                                            </div>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                                <div class="card-footer {{ VC::DFL }} {{ VC::JCE }}">
                                    <div class="{{ VC::FM_G }}">
                                        <input class="{{ VC::BT_PR_PRM10 }}" type="submit" value="{{ __('Save Changes') }}">
                                    </div>
                                </div>
                            {{ Form::close() }}
                        </div>
                        @php
                            $twilioSettingBaseRouteName='twilio.setting';
                            $twilioSettingKebabRouteName=Str::kebab($twilioSettingBaseRouteName);
                            $twilioSettingResolvedRouteName=Route::has($twilioSettingBaseRouteName)?$twilioSettingBaseRouteName:(Route::has($twilioSettingKebabRouteName)?$twilioSettingKebabRouteName:null);
                            $twilioSettingRouteArray=$twilioSettingResolvedRouteName?[$twilioSettingResolvedRouteName]:['#'];
                            $twilioSettingUrl=$twilioSettingResolvedRouteName?route($twilioSettingResolvedRouteName):'#';
                            $langValue=isset($lang)&&is_string($lang)?$lang:Utility::fetchUserLang();
                            $twilioSettingGuardMsg=Utility::fetchLinkMessage($langValue,'twilio','twilio_setting_route_unavailable')??__('Twilio setting route is unavailable. Please contact technical support or your domain administrator.');
                            $twilioSettingFormId='twilio-settings-form';
                            $groups=[
                                [['name'=>'twilio_customer_notification','label'=>__('New Customer')],['name'=>'twilio_vendor_notification','label'=>__('New Vendor')]],
                                [['name'=>'twilio_invoice_notification','label'=>__('New Invoice')],['name'=>'twilio_revenue_notification','label'=>__('New Revenue')]],
                                [['name'=>'twilio_bill_notification','label'=>__('New Bill')],['name'=>'twilio_proposal_notification','label'=>__('New Proposal')]],
                                [['name'=>'twilio_payment_notification','label'=>__('New Payment')],['name'=>'twilio_reminder_notification','label'=>__('Invoice Reminder')]],
                            ];
                        @endphp
                        <div id="twilio-settings" class="card">
                            <div class="card-header">
                                <h5>{{ __('Twilio Settings') }}</h5>
                                <small class="text-muted">{{ __('Edit your Twilio settings') }}</small>
                            </div>
                            {!! Form::model($setting??[],['route'=>$twilioSettingRouteArray,'method'=>'post','id'=>$twilioSettingFormId,'class'=>'d-contents','data-url'=>$twilioSettingUrl,'data-guard-msg'=>$twilioSettingGuardMsg]) !!}
                                @push(StacksConstants::ADM_SCR_PG)
                                    <script defer src="{{ asset('assets/js/routes/settings/companies/twilio.js') }}"></script>
                                @endpush
                                @csrf
                                <div class="card-body">
                                    <div class="{{ VC::RW }}">
                                        <div class="{{ VC::FM_G }} {{ VC::CM4 }}">
                                            <label class="{{ VC::FM_LB }}">{{ __('Twilio SID') }}</label>
                                            {{ Form::text('twilio_sid', old('twilio_sid', data_get($setting??[], 'twilio_sid','')), ['class'=>VC::FM_CT.' w-100','placeholder'=>__('Enter Twilio SID'),'required'=>true]) }}
                                            @error('twilio_sid')
                                                <span class="invalid-feedback {{ VC::DBL }}">{{ $message ?? __('No message available') }}</span>
                                            @enderror
                                        </div>
                                        <div class="{{ VC::FM_G }} {{ VC::CM4 }}">
                                            <label class="{{ VC::FM_LB }}">{{ __('Twilio Token') }}</label>
                                            {{ Form::password('twilio_token', ['class'=>VC::FM_CT.' w-100','placeholder'=>__('Enter Twilio Token'),'required'=>true,'autocomplete'=>'off','spellcheck'=>'false']) }}
                                            @error('twilio_token')
                                                <span class="invalid-feedback {{ VC::DBL }}">{{ $message ?? __('No message available') }}</span>
                                            @enderror
                                        </div>
                                        <div class="{{ VC::FM_G }} {{ VC::CM4 }}">
                                            <label class="{{ VC::FM_LB }}">{{ __('Twilio From') }}</label>
                                            {{ Form::text('twilio_from', old('twilio_from', data_get($setting??[], 'twilio_from','')), ['class'=>VC::FM_CT.' w-100','placeholder'=>__('Enter Twilio From'),'required'=>true]) }}
                                            @error('twilio_from')
                                                <span class="invalid-feedback {{ VC::DBL }}">{{ $message ?? __('No message available') }}</span>
                                            @enderror
                                        </div>
                                        <div class="{{ VC::C12 }} mt-4 mb-2">
                                            <h5 class="small-title">{{ __('Module Settings') }}</h5>
                                        </div>
                                        @foreach($groups as $cols)
                                            @foreach($cols as $i=>$item)
                                                @if($i===0)
                                                    <div class="{{ VC::CM4 }} {{ VC::MB1 }}">
                                                        <ul class="{{ VC::LGRP }}">
                                                @endif
                                                            @php
                                                                $n=data_get($item,'name');
                                                                $checked=old($n, data_get($setting??[], $n,'0'))=='1';
                                                            @endphp
                                                            <li class="{{ VC::LGI }}">
                                                                <div class="form-switch form-switch-right">
                                                                    <span>{{ data_get($item,'label',__('No label available')) }}</span>
                                                                    {{ Form::hidden($n,'0') }}
                                                                    {{ Form::checkbox($n,'1',$checked,['class'=>'form-check-input','id'=>$n]) }}
                                                                    <label class="form-check-label" for="{{ $n }}"></label>
                                                                </div>
                                                            </li>
                                                @if($i===1||count($cols)===1)
                                                        </ul>
                                                    </div>
                                                @endif
                                            @endforeach
                                        @endforeach
                                    </div>
                                </div>
                                <div class="card-footer {{ VC::DFL }} {{ VC::JCE }}">
                                    <div class="form-group">
                                        <input class="{{ VC::BT_PR_PRM10 }}" type="submit" value="{{ __('Save Changes') }}">
                                    </div>
                                </div>
                            {{ Form::close() }}
                        </div>
                        <div id="email-notification-settings" class="card">
                            <div class="col-md-12">
                                <div class="card-header">
                                    <h5>{{ __('Email Notification Settings') }}</h5>
                                    <small class="text-muted">{{ __('Edit email notification settings') }}</small>
                                </div>
                                @php
                                    $emailStatusLanguageBaseRoute=VW::EMLS.'.status.language';
                                    $emailStatusLanguageKebabRoute=Str::kebab($emailStatusLanguageBaseRoute);
                                    $emailStatusLanguageResolvedName=Route::has($emailStatusLanguageBaseRoute)?$emailStatusLanguageBaseRoute:(Route::has($emailStatusLanguageKebabRoute)?$emailStatusLanguageKebabRoute:null);
                                    $emailStatusLanguageRouteArray=$emailStatusLanguageResolvedName?[$emailStatusLanguageResolvedName]:['#'];
                                    $emailStatusLanguageUrl=$emailStatusLanguageResolvedName?route($emailStatusLanguageResolvedName):'#';
                                    $langValue=isset($lang)&&is_string($lang)?$lang:Utility::fetchUserLang();
                                    $emailStatusLanguageGuardMsg=Utility::fetchLinkMessage($langValue, VW::EMLS, 'email_status_language_route_unavailable')??__('Email status language route is unavailable. Please contact technical support or your domain administrator.');
                                    $emailStatusLanguageFormId='email-status-language-form';
                                @endphp
                                {!! Form::model($setting??[],['route'=>$emailStatusLanguageRouteArray,'method'=>'post','id'=>$emailStatusLanguageFormId,'data-url'=>$emailStatusLanguageUrl,'data-guard-msg'=>$emailStatusLanguageGuardMsg]) !!}
                                    @push(StacksConstants::ADM_SCR_PG)
                                        <script defer src="{{ asset('assets/js/routes/settings/companies/emailNotification.js') }}"></script>
                                    @endpush
                                    @csrf
                                    <div class="card-body">
                                        <div class="{{ VC::RW }}">
                                            @php
                                                $hasTemplates= Utility::isFilled($emailTemplates);
                                            @endphp
                                            @if($hasTemplates)
                                                @foreach($emailTemplates as $emailTemplate)
                                                    @php
                                                        $tplId=data_get($emailTemplate,'template.id',data_get($emailTemplate,'id'));
                                                        $tplKey=$tplId??('unknown_'.$loop->index);
                                                        $tplName=data_get($emailTemplate,'name')?:__('No template name available');
                                                        $isActive=(bool) data_get($emailTemplate,'template.is_active',0);
                                                        $checkboxId='email_template_'.$tplKey;
                                                        $itemUrl=$emailStatusLanguageResolvedName&&$tplId?route($emailStatusLanguageResolvedName,[$tplId]):'#';
                                                        $itemGuardMsg=Utility::fetchLinkMessage($langValue, VW::EMLS, 'email_template_status_language_route_unavailable')??__('Email template status language route is unavailable. Please contact technical support or your domain administrator.');
                                                    @endphp
                                                    <div class="col-lg-4 col-md-6 col-sm-6 {{ VC::FM_G }}">
                                                        <div class="{{ VC::LGRP }}">
                                                            <div class="{{ VC::LGI }} form-switch form-switch-right">
                                                                <label class="{{ VC::FM_LB }}" style="margin-left:5%;">{{ $tplName }}</label>
                                                                {{ Form::hidden("templates[$tplKey]",0) }}
                                                                <input class="form-check-input email-template-toggle" name="templates[{{ $tplKey }}]" id="{{ $checkboxId }}" type="checkbox" value="1" @checked($isActive) data-url="{{ $itemUrl }}" data-guard-msg="{{ $itemGuardMsg }}" />
                                                                <label class="form-check-label" for="{{ $checkboxId }}"></label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            @else
                                                <div class="alert alert-warning">{{ __('No email templates found.') }}</div>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="card-footer text-end">
                                        <div class="{{ VC::FM_G }}">
                                            <input class="{{ VC::BT_PR_PRM10 }}" type="submit" value="{{ __('Save Changes') }}">
                                        </div>
                                    </div>
                                {{ Form::close() }}
                            </div>
                        </div>
                        @push(StacksConstants::ADM_SCR_PG)
                            <script defer src="{{ asset('assets/js/routes/settings/companies/emailSettings.js') }}"></script>
                        @endpush
                    @else
                        <div class="alert alert-warning">
                            {{ __("No core settings available. Make immediate contact with your system administrator or available technical support.") }}
                        </div>
                    @endif
                    <div id="offer-letter-settings" class="{{ VC::CD }}">
                        <div class="col-md-12">
                            <div class="card-header {{ VC::DFL_JCB }}">
                                <h5>{{ __('Offer Letter Settings') }}</h5>
                                <div class="{{ VC::DFL.' '.VC::JCE }} drp-languages">
                                    <ul class="list-unstyled {{ VC::MB0 }} m-2">
                                        <li class="{{ VC::LNG_DD_IT }}" style="margin-top:-7px;">
                                            <a class="{{ VC::DRP_NO_ARROW }}" data-bs-toggle="dropdown" href="#" role="button" aria-haspopup="false" aria-expanded="false" id="dropdownLanguage">
                                                <span class="drp-text hide-mob text-primary me-2">{{ ucfirst(data_get($offerlangName??null,'full_name',__('No language available'))) }}</span>
                                                <i class="ti ti-chevron-down drp-arrow nocolor"></i>
                                            </a>
                                            <div class="{{ VC::DRP_MN_DSH_END }}" aria-labelledby="dropdownLanguage">
                                                @php
                                                    $offerLetterLangRouteBase=VW::SET.'.offer_letter.language';
                                                    $offerLetterLangRouteKebab=Str::kebab($offerLetterLangRouteBase);
                                                    $offerLetterLangRouteName=Route::has($offerLetterLangRouteBase)?$offerLetterLangRouteBase:(Route::has($offerLetterLangRouteKebab)?$offerLetterLangRouteKebab:null);
                                                    $langValue=isset($lang)&&is_string($lang)?$lang:Utility::fetchUserLang();
                                                    $offerLetterLangGuardMsg=Utility::fetchLinkMessage($langValue, VW::SET, 'offer_letter_language_route_unavailable')??__('Offer letter language route is unavailable. Please contact technical support or your domain administrator.');
                                                @endphp
                                                @if(is_iterable($currentLang??[]))
                                                    @foreach($currentLang as $code=>$offerlang)
                                                        @php
                                                            $offerLetterLangParams=['noclangs'=>$noclang??null,'explangs'=>$explang??null,'offerlang'=>$code,'joininglangs'=>$joininglang??null];
                                                            $offerLetterLangUrl=$offerLetterLangRouteName?route($offerLetterLangRouteName,$offerLetterLangParams):'#';
                                                        @endphp
                                                        <a id="offer-letter-language-link-{{ $code }}" href="{{ $offerLetterLangUrl }}" data-url="{{ $offerLetterLangUrl }}" data-guard-msg="{{ $offerLetterLangGuardMsg }}" class="dropdown-item ms-1 offer-letter-language-link {{ ((isset($offerlang)&&$offerlang===$code)?'text-primary':'') }}">{{ ucfirst($offerlang) }}</a>
                                                    @endforeach
                                                @endif
                                                @push(StacksConstants::ADM_SCR_PG)
                                                    <script defer>
                                                        (function(){var links=document.querySelectorAll('.offer-letter-language-link');if(!links||links.length===0)return;links.forEach(function(l){if(l.getAttribute('data-listener-active')==='true')return;l.setAttribute('data-listener-active','true');l.addEventListener('click',function(e){try{var url=l.getAttribute('data-url')||'#';if(url!=='#')return;e.preventDefault();var msg=l.getAttribute('data-guard-msg')||'';var hasBootstrap=!!(document.querySelector('link[href*="bootstrap"]')&&window.bootstrap);var container=document.getElementById('toast-container');if(!container){container=document.createElement('div');container.id='toast-container';document.body.appendChild(container);}if(hasBootstrap){var toast=document.createElement('div');toast.className='toast';toast.setAttribute('role','alert');toast.setAttribute('aria-live','assertive');toast.setAttribute('aria-atomic','true');var body=document.createElement('div');body.className='toast-body';body.textContent=msg;toast.appendChild(body);container.appendChild(toast);bootstrap.Toast.getOrCreateInstance(toast).show();}else{alert(msg);}l.setAttribute('data-failed-route','true');}catch(err){}});});})();
                                                    </script>
                                                @endpush
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <div class="card-body">
                                <h5 class="font-weight-bold pb-3">{{ __('Placeholders') }}</h5>
                                <div class="col-lg-12 col-md-12 col-sm-12">
                                    <div class="{{ VC::CD }}">
                                        <div class="card-header card-body">
                                            <div class="{{ VC::RW }} {{ VC::TXS }}">
                                                <div class="{{ VC::RW }}">
                                                    <p class="col-4">{{ __('Applicant Name') }} : <span class="pull-end text-primary">{applicant_name}</span></p>
                                                    <p class="col-4">{{ __('Company Name') }} : <span class="pull-right text-primary">{app_name}</span></p>
                                                    <p class="col-4">{{ __('Job title') }} : <span class="pull-right text-primary">{job_title}</span></p>
                                                    <p class="col-4">{{ __('Job type') }} : <span class="pull-right text-primary">{job_type}</span></p>
                                                    <p class="col-4">{{ __('Proposed Start Date') }} : <span class="pull-right text-primary">{start_date}</span></p>
                                                    <p class="col-4">{{ __('Working Location') }} : <span class="pull-right text-primary">{workplace_location}</span></p>
                                                    <p class="col-4">{{ __('Days Of Week') }} : <span class="pull-right text-primary">{days_of_week}</span></p>
                                                    <p class="col-4">{{ __('Salary') }} : <span class="pull-right text-primary">{salary}</span></p>
                                                    <p class="col-4">{{ __('Salary Type') }} : <span class="pull-right text-primary">{salary_type}</span></p>
                                                    <p class="col-4">{{ __('Salary Duration') }} : <span class="pull-end text-primary">{salary_duration}</span></p>
                                                    <p class="col-4">{{ __('Offer Expiration Date') }} : <span class="pull-right text-primary">{offer_expiration_date}</span></p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body table-border-style">
                                @php
                                    $offerLetterUpdateBaseName='offer_letter.update';
                                    $offerLetterUpdateKebabName=Str::kebab($offerLetterUpdateBaseName);
                                    $offerLetterUpdateResolvedName=Route::has($offerLetterUpdateBaseName)?$offerLetterUpdateBaseName:(Route::has($offerLetterUpdateKebabName)?$offerLetterUpdateKebabName:null);
                                    $offerLangKey=(string)($offerlang??'default');
                                    $offerLetterUpdateRouteArray=$offerLetterUpdateResolvedName?[$offerLetterUpdateResolvedName,$offerLangKey]:['#'];
                                    $offerLetterUpdateUrl=$offerLetterUpdateResolvedName?route($offerLetterUpdateResolvedName,$offerLangKey):'#';
                                    $offerLetterUpdateGuardMsg=Utility::fetchLinkMessage($langValue??(isset($lang)&&is_string($lang)?$lang:Utility::fetchUserLang()), VW::SET, 'offer_letter_update_route_unavailable')??__('Offer letter update route is unavailable. Please contact technical support or your domain administrator.');
                                    $offerLetterUpdateFormId='offer-letter-update-form-'.$offerLangKey;
                                @endphp
                                {!! Form::open(['route'=>$offerLetterUpdateRouteArray,'method'=>'post','id'=>$offerLetterUpdateFormId,'data-url'=>$offerLetterUpdateUrl,'data-guard-msg'=>$offerLetterUpdateGuardMsg]) !!}
                                    @csrf
                                    @push(StacksConstants::ADM_SCR_PG)
                                        <script defer>
                                            (function(){var form=document.getElementById('{{ $offerLetterUpdateFormId }}');if(!form||form.getAttribute('data-listener-active')==='true')return;form.setAttribute('data-listener-active','true');form.addEventListener('submit',function(e){try{var url=form.getAttribute('data-url')||'#';var action=form.getAttribute('action')||'#';if(url!=='#'&&action!=='#')return;e.preventDefault();var msg=form.getAttribute('data-guard-msg')||'';var hasBootstrap=!!(document.querySelector('link[href*="bootstrap"]')&&window.bootstrap);var container=document.getElementById('toast-container');if(!container){container=document.createElement('div');container.id='toast-container';document.body.appendChild(container);}if(hasBootstrap){var toast=document.createElement('div');toast.className='toast';toast.setAttribute('role','alert');toast.setAttribute('aria-live','assertive');toast.setAttribute('aria-atomic','true');var body=document.createElement('div');body.className='toast-body';body.textContent=msg;toast.appendChild(body);container.appendChild(toast);bootstrap.Toast.getOrCreateInstance(toast).show();}else{alert(msg);}form.setAttribute('data-failed-route','true');}catch(err){}});})();
                                        </script>
                                    @endpush
                                    <div class="{{ VC::FM_G }} {{ VC::C12 }}">
                                        {{ Form::label('content', __('Format'), ['class'=>VC::FM_LB.' text-dark']) }}
                                        <textarea name="content" class="summernote-simple0 summernote-simple">{!! data_get($currOfferletterLang??null,'content','') !!}</textarea>
                                    </div>
                                {{ Form::close() }}
                            </div>
                        </div>
                    </div>
                    <div id="joining-letter-settings" class="{{ VC::CD }}">
                        <div class="col-md-12">
                            <div class="card-header {{ VC::DFL_JCB }}">
                                <h5>{{ __('Joining Letter Settings') }}</h5>
                                <div class="{{ VC::DFL.' '.VC::JCE }} drp-languages">
                                    <ul class="list-unstyled {{ VC::MB0 }} m-2">
                                        <li class="{{ VC::LNG_DD_IT }}" style="margin-top:-7px;">
                                            <a class="{{ VC::DRP_NO_ARROW }}" data-bs-toggle="dropdown" href="#" role="button" aria-haspopup="false" aria-expanded="false" id="dropdownLanguage1">
                                                <span class="drp-text hide-mob text-primary me-2">{{ ucfirst(data_get($joininglangName??null,'full_name',__('No language available'))) }}</span>
                                                <i class="ti ti-chevron-down drp-arrow nocolor"></i>
                                            </a>
                                            <div class="{{ VC::DRP_MN_DSH_END }}" aria-labelledby="dropdownLanguage1">
                                                @php
                                                    $joiningLetterLangBaseName=VW::SET.'.joining_letter.language';
                                                    $joiningLetterLangKebabName=Str::kebab($joiningLetterLangBaseName);
                                                    $joiningLetterLangRouteName=Route::has($joiningLetterLangBaseName)?$joiningLetterLangBaseName:(Route::has($joiningLetterLangKebabName)?$joiningLetterLangKebabName:null);
                                                    $langValue=isset($lang)&&is_string($lang)?$lang:Utility::fetchUserLang();
                                                    $joiningLetterLangGuardMsg=Utility::fetchLinkMessage($langValue, VW::SET, 'joining_letter_language_route_unavailable')??__('Joining letter language route is unavailable. Please contact technical support or your domain administrator.');
                                                @endphp
                                                @if(is_iterable($currentLang??[]))
                                                    @foreach($currentLang as $code=>$joininglang)
                                                        @php
                                                            $joiningLetterParams=['noclangs'=>$noclang??null,'explangs'=>$explang??null,'offerlangs'=>$joininglang??null,'joininglangs'=>$code];
                                                            $joiningLetterLangUrl=$joiningLetterLangRouteName?route($joiningLetterLangRouteName,$joiningLetterParams):'#';
                                                        @endphp
                                                        <a id="joining-letter-language-link-{{ $code }}" href="{{ $joiningLetterLangUrl }}" data-url="{{ $joiningLetterLangUrl }}" data-guard-msg="{{ $joiningLetterLangGuardMsg }}" class="dropdown-item joining-letter-language-link {{ ($joininglang==$code)?'text-primary':'' }}">{{ (is_string($joininglang)&&$joininglang!=='')?ucfirst($joininglang):__('No language label available') }}</a>
                                                    @endforeach
                                                @else
                                                    <span class="text-muted">{{ __('No languages found for Joining letters') }}</span>
                                                @endif
                                                @push(StacksConstants::ADM_SCR_PG)
                                                    <script defer src="{{ asset('assets/js/routes/settings/companies/joiningLetter.js') }}"></script>
                                                @endpush
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <div class="card-body">
                                <h5 class="font-weight-bold pb-3">{{ __('Placeholders') }}</h5>
                                <div class="col-lg-12 col-md-12 col-sm-12">
                                    <div class="{{ VC::CD }}">
                                        <div class="card-header card-body">
                                            <div class="{{ VC::RW }} {{ VC::TXS }}">
                                                <div class="{{ VC::RW }}">
                                                    <p class="col-4">{{ __('Applicant Name') }} : <span class="pull-end text-primary">{date}</span></p>
                                                    <p class="col-4">{{ __('Company Name') }} : <span class="pull-right text-primary">{app_name}</span></p>
                                                    <p class="col-4">{{ __('Employee Name') }} : <span class="pull-right text-primary">{employee_name}</span></p>
                                                    <p class="col-4">{{ __('Address') }} : <span class="pull-right text-primary">{address}</span></p>
                                                    <p class="col-4">{{ __('Designation') }} : <span class="pull-right text-primary">{designation}</span></p>
                                                    <p class="col-4">{{ __('Start Date') }} : <span class="pull-right text-primary">{start_date}</span></p>
                                                    <p class="col-4">{{ __('Branch') }} : <span class="pull-right text-primary">{branch}</span></p>
                                                    <p class="col-4">{{ __('Start Time') }} : <span class="pull-end text-primary">{start_time}</span></p>
                                                    <p class="col-4">{{ __('End Time') }} : <span class="pull-right text-primary">{end_time}</span></p>
                                                    <p class="col-4">{{ __('Number of Hours') }} : <span class="pull-right text-primary">{total_hours}</span></p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body table-border-style">
                                @php
                                    $joiningLetterUpdateBaseName='joining_letter.update';
                                    $joiningLetterUpdateKebabName=Str::kebab($joiningLetterUpdateBaseName);
                                    $joiningLetterUpdateResolvedName=Route::has($joiningLetterUpdateBaseName)?$joiningLetterUpdateBaseName:(Route::has($joiningLetterUpdateKebabName)?$joiningLetterUpdateKebabName:null);
                                    $joiningLangKey=(string)($joininglang??'default');
                                    $joiningLetterUpdateRouteArray=$joiningLetterUpdateResolvedName?[$joiningLetterUpdateResolvedName,$joiningLangKey]:['#'];
                                    $joiningLetterUpdateUrl=$joiningLetterUpdateResolvedName?route($joiningLetterUpdateResolvedName,$joiningLangKey):'#';
                                    $joiningLetterUpdateGuardMsg=Utility::fetchLinkMessage($langValue, VW::SET, 'joining_letter_update_route_unavailable')??__('Joining letter update route is unavailable. Please contact technical support or your domain administrator.');
                                    $joiningLetterUpdateFormId='joining-letter-update-form-'.$joiningLangKey;
                                @endphp
                                {!! Form::open(['route'=>$joiningLetterUpdateRouteArray,'method'=>'post','id'=>$joiningLetterUpdateFormId,'data-url'=>$joiningLetterUpdateUrl,'data-guard-msg'=>$joiningLetterUpdateGuardMsg]) !!}
                                    @push(StacksConstants::ADM_SCR_PG)
                                        <script defer>
                                            (()=>{const form=document.getElementById('{{ $joiningLetterUpdateFormId }}');if(!form||form.getAttribute('data-listener-active')==='true')return;form.setAttribute('data-listener-active','true');form.addEventListener('submit',e=>{try{const url=form.getAttribute('data-url')||'#';const action=form.getAttribute('action')||'#';if(url!=='#'&&action!=='#')return;e.preventDefault();const msg=form.getAttribute('data-guard-msg')||'# ERROR';const hasBootstrap=document.querySelector('link[href*="bootstrap"]')&&window.bootstrap;let container=document.getElementById('toast-container');if(!container){container=document.createElement('div');container.id='toast-container';document.body.appendChild(container);}if(hasBootstrap){const toast=document.createElement('div');toast.className='toast';toast.setAttribute('role','alert');toast.setAttribute('aria-live','assertive');toast.setAttribute('aria-atomic','true');const body=document.createElement('div');body.className='toast-body';body.textContent=msg;toast.appendChild(body);container.appendChild(toast);bootstrap.Toast.getOrCreateInstance(toast).show();}else{alert(msg);}form.setAttribute('data-failed-route','true');}catch(err){}});})();
                                        </script>
                                    @endpush
                                    <div class="{{ VC::FM_G }} {{ VC::C12 }}">
                                        {{ Form::label('content', __('Format'), ['class'=>VC::FM_LB.' text-dark']) }}
                                        <textarea name="content" class="summernote-simple1 summernote-simple">{!! data_get($currjoiningletterLang??null,'content','') !!}</textarea>
                                    </div>
                                {{ Form::close() }}
                            </div>
                        </div>
                    </div>
                    <div id="experience-certificate-settings" class="{{ VC::CD }}">
                        <div class="col-md-12">
                            <div class="card-header {{ VC::DFL_JCB }}">
                                <h5>{{ __('Experience Certificate Settings') }}</h5>
                                <div class="{{ VC::DFL.' '.VC::JCE }} drp-languages">
                                    <ul class="list-unstyled {{ VC::MB0 }} m-2">
                                        <li class="{{ VC::LNG_DD_IT }}" style="margin-top:-7px;">
                                            <a class="{{ VC::DRP_NO_ARROW }}" data-bs-toggle="dropdown" href="#" role="button" aria-haspopup="false" aria-expanded="false" id="dropdownLanguage1">
                                                <span class="drp-text hide-mob text-primary me-2">{{ ucfirst(data_get($explangName??null,'full_name',__('No language available'))) }}</span>
                                                <i class="ti ti-chevron-down drp-arrow nocolor"></i>
                                            </a>
                                            <div class="{{ VC::DRP_MN_DSH_END }}" aria-labelledby="dropdownLanguage1">
                                                @php
                                                    $experienceCertificateLangBase=VW::SET.'.experience_certificate.language';
                                                    $experienceCertificateLangKebab=Str::kebab($experienceCertificateLangBase);
                                                    $experienceCertificateLangName=Route::has($experienceCertificateLangBase)?$experienceCertificateLangBase:(Route::has($experienceCertificateLangKebab)?$experienceCertificateLangKebab:null);
                                                    $experienceCertificateLangGuard=Utility::fetchLinkMessage($langValue, VW::SET, 'experience_certificate_language_route_unavailable')??__('Experience certificate language route is unavailable. Please contact technical support or your domain administrator.');
                                                @endphp
                                                @if(is_iterable($currentLang??[]))
                                                    @foreach($currentLang as $code=>$explang)
                                                        @php
                                                            $experienceCertificateParams=['noclangs'=>$noclang??null,'explangs'=>$code,'offerlangs'=>$explang??null,'joininglangs'=>$joininglang??null];
                                                            $experienceCertificateLangUrl=$experienceCertificateLangName?route($experienceCertificateLangName,$experienceCertificateParams):'#';
                                                        @endphp
                                                        <a id="experience-certificate-language-link-{{ $code }}" href="{{ $experienceCertificateLangUrl }}" data-url="{{ $experienceCertificateLangUrl }}" data-guard-msg="{{ $experienceCertificateLangGuard }}" class="dropdown-item experience-certificate-language-link {{ ($explang==$code)?'text-primary':'' }}">{{ (is_string($explang)&&$explang!=='')?ucfirst($explang):__('No language label available') }}</a>
                                                    @endforeach
                                                @else
                                                    <span class="text-muted">{{ __('No languages found for Experience Certificates') }}</span>
                                                @endif
                                                @push(StacksConstants::ADM_SCR_PG)
                                                    <script defer src="{{ asset('assets/js/routes/settings/companies/experienceCertificate.js') }}"></script>
                                                @endpush
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <div class="card-body">
                                <h5 class="font-weight-bold pb-3">{{ __('Placeholders') }}</h5>
                                <div class="col-lg-12 col-md-12 col-sm-12">
                                    <div class="{{ VC::CD }}">
                                        <div class="card-header card-body">
                                            <div class="{{ VC::RW }} {{ VC::TXS }}">
                                                <div class="{{ VC::RW }}">
                                                    <p class="col-4">{{ __('Company Name') }} : <span class="pull-right text-primary">{app_name}</span></p>
                                                    <p class="col-4">{{ __('Employee Name') }} : <span class="pull-right text-primary">{employee_name}</span></p>
                                                    <p class="col-4">{{ __('Date of Issuance') }} : <span class="pull-right text-primary">{date}</span></p>
                                                    <p class="col-4">{{ __('Designation') }} : <span class="pull-right text-primary">{designation}</span></p>
                                                    <p class="col-4">{{ __('Start Date') }} : <span class="pull-right text-primary">{start_date}</span></p>
                                                    <p class="col-4">{{ __('Branch') }} : <span class="pull-right text-primary">{branch}</span></p>
                                                    <p class="col-4">{{ __('Start Time') }} : <span class="pull-end text-primary">{start_time}</span></p>
                                                    <p class="col-4">{{ __('End Time') }} : <span class="pull-right text-primary">{end_time}</span></p>
                                                    <p class="col-4">{{ __('Number of Hours') }} : <span class="pull-right text-primary">{total_hours}</span></p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body table-border-style">
                                @php
                                    $expCertUpdateBaseName='experience_certificate.update';
                                    $expCertUpdateKebabName=Str::kebab($expCertUpdateBaseName);
                                    $expCertUpdateResolvedName=Route::has($expCertUpdateBaseName)?$expCertUpdateBaseName:(Route::has($expCertUpdateKebabName)?$expCertUpdateKebabName:null);
                                    $expLangKey=(string)($explang??'default');
                                    $expCertUpdateRouteArray=$expCertUpdateResolvedName?[$expCertUpdateResolvedName,$expLangKey]:['#'];
                                    $expCertUpdateUrl=$expCertUpdateResolvedName?route($expCertUpdateResolvedName,$expLangKey):'#';
                                    $expCertUpdateGuardMsg=Utility::fetchLinkMessage($langValue, VW::SET, 'experience_certificate_update_route_unavailable')??__('Experience certificate update route is unavailable. Please contact technical support or your domain administrator.');
                                    $expCertUpdateFormId='experience-certificate-update-form-'.$expLangKey;
                                @endphp
                                {!! Form::open(['route'=>$expCertUpdateRouteArray,'method'=>'post','id'=>$expCertUpdateFormId,'data-url'=>$expCertUpdateUrl,'data-guard-msg'=>$expCertUpdateGuardMsg]) !!}
                                    @push(StacksConstants::ADM_SCR_PG)
                                        <script defer>
                                            (()=>{const form=document.getElementById('{{ $expCertUpdateFormId }}');if(!form||form.getAttribute('data-listener-active')==='true')return;form.setAttribute('data-listener-active','true');form.addEventListener('submit',e=>{try{const url=form.getAttribute('data-url')||'#';const action=form.getAttribute('action')||'#';if(url!=='#'&&action!=='#')return;e.preventDefault();const msg=form.getAttribute('data-guard-msg')||'# ERROR';const hasBootstrap=document.querySelector('link[href*="bootstrap"]')&&window.bootstrap;let container=document.getElementById('toast-container');if(!container){container=document.createElement('div');container.id='toast-container';document.body.appendChild(container);}if(hasBootstrap){const toast=document.createElement('div');toast.className='toast';toast.setAttribute('role','alert');toast.setAttribute('aria-live','assertive');toast.setAttribute('aria-atomic','true');const body=document.createElement('div');body.className='toast-body';body.textContent=msg;toast.appendChild(body);container.appendChild(toast);bootstrap.Toast.getOrCreateInstance(toast).show();}else{alert(msg);}form.setAttribute('data-failed-route','true');}catch(err){}});})();
                                        </script>
                                    @endpush
                                    <div class="{{ VC::FM_G }} {{ VC::C12 }}">
                                        {{ Form::label('content', __('Format'), ['class'=>VC::FM_LB.' text-dark']) }}
                                        <textarea name="content" class="summernote-simple2 summernote-simple">{!! data_get($curr_exp_cetificate_Lang??null,'content','') !!}</textarea>
                                    </div>
                                {{ Form::close() }}
                            </div>
                        </div>
                    </div>
                    <div id="noc-settings" class="{{ VC::CD }}">
                        <div class="col-md-12">
                            <div class="card-header {{ VC::DFL_JCB }}">
                                <h5>{{ __('NOC Settings') }}</h5>
                                <div class="{{ VC::DFL.' '.VC::JCE }} drp-languages">
                                    <ul class="list-unstyled {{ VC::MB0 }} m-2">
                                        <li class="{{ VC::LNG_DD_IT }}" style="margin-top:-7px;">
                                            <a class="{{ VC::DRP_NO_ARROW }}" data-bs-toggle="dropdown" href="#" role="button" aria-haspopup="false" aria-expanded="false" id="dropdownLanguage1">
                                                <span class="drp-text hide-mob text-primary me-2">{{ ucfirst(data_get($noclangName??null,'full_name',__('No language available'))) }}</span>
                                                <i class="ti ti-chevron-down drp-arrow nocolor"></i>
                                            </a>
                                            <div class="{{ VC::DRP_MN_DSH_END }}" aria-labelledby="dropdownLanguage1">
                                                @php
                                                    $nocLanguageBaseName=VW::SET.'.noc.language';
                                                    $nocLanguageKebabName=Str::kebab($nocLanguageBaseName);
                                                    $nocLanguageRouteName=Route::has($nocLanguageBaseName)?$nocLanguageBaseName:(Route::has($nocLanguageKebabName)?$nocLanguageKebabName:null);
                                                    $nocLanguageGuardMsg=Utility::fetchLinkMessage($langValue, VW::SET, 'noc_language_route_unavailable')??__('NOC language route is unavailable. Please contact technical support or your domain administrator.');
                                                @endphp
                                                @if(is_iterable($currentLang??[]))
                                                    @foreach($currentLang as $code=>$noclangs)
                                                        @php
                                                            $nocLanguageParams=['noclangs'=>$code,'explangs'=>$explang??null,'offerlangs'=>$offerlang??null,'joininglangs'=>$joininglang??null];
                                                            $nocLanguageUrl=$nocLanguageRouteName?route($nocLanguageRouteName,$nocLanguageParams):'#';
                                                        @endphp
                                                        <a id="noc-language-link-{{ $code }}" href="{{ $nocLanguageUrl }}" data-url="{{ $nocLanguageUrl }}" data-guard-msg="{{ $nocLanguageGuardMsg }}" class="dropdown-item noc-language-link {{ ($noclangs==$code)?'text-primary':'' }}">{{ (is_string($noclangs)&&$noclangs!=='')?ucfirst($noclangs):__('No language label available') }}</a>
                                                    @endforeach
                                                @else
                                                    <span class="text-muted">{{ __('No languages found for NOC template') }}</span>
                                                @endif
                                                @push(StacksConstants::ADM_SCR_PG)
                                                    <script defer src="{{ asset('assets/js/routes/settings/companies/noc.js') }}"></script>
                                                @endpush
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <div class="card-body">
                                <h5 class="font-weight-bold pb-3">{{ __('Placeholders') }}</h5>
                                <div class="col-lg-12 col-md-12 col-sm-12">
                                    <div class="{{ VC::CD }}">
                                        <div class="card-header card-body">
                                            <div class="{{ VC::RW }} {{ VC::TXS }}">
                                                <div class="{{ VC::RW }}">
                                                    <p class="col-4">{{ __('Date') }} : <span class="pull-end text-primary">{date}</span></p>
                                                    <p class="col-4">{{ __('Company Name') }} : <span class="pull-right text-primary">{app_name}</span></p>
                                                    <p class="col-4">{{ __('Employee Name') }} : <span class="pull-right text-primary">{employee_name}</span></p>
                                                    <p class="col-4">{{ __('Designation') }} : <span class="pull-right text-primary">{designation}</span></p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body table-border-style">
                                @php
                                    $nocUpdateBaseName='noc.update';
                                    $nocUpdateKebabName=Str::kebab($nocUpdateBaseName);
                                    $nocUpdateResolvedName=Route::has($nocUpdateBaseName)?$nocUpdateBaseName:(Route::has($nocUpdateKebabName)?$nocUpdateKebabName:null);
                                    $nocLangKey=(string)($noclang??'default');
                                    $nocUpdateRouteArray=$nocUpdateResolvedName?[$nocUpdateResolvedName,$nocLangKey]:['#'];
                                    $nocUpdateUrl=$nocUpdateResolvedName?route($nocUpdateResolvedName,$nocLangKey):'#';
                                    $nocUpdateGuardMsg=Utility::fetchLinkMessage($langValue, VW::SET, 'noc_update_route_unavailable')??__('NOC update route is unavailable. Please contact technical support or your domain administrator.');
                                    $nocUpdateFormId='noc-update-form-'.$nocLangKey;
                                @endphp
                                {!! Form::open(['route'=>$nocUpdateRouteArray,'method'=>'post','id'=>$nocUpdateFormId,'data-url'=>$nocUpdateUrl,'data-guard-msg'=>$nocUpdateGuardMsg]) !!}
                                    @push(StacksConstants::ADM_SCR_PG)
                                        <script defer>
                                            (()=>{const form=document.getElementById('{{ $nocUpdateFormId }}');if(!form||form.getAttribute('data-listener-active')==='true')return;form.setAttribute('data-listener-active','true');form.addEventListener('submit',e=>{try{const url=form.getAttribute('data-url')||'#';const action=form.getAttribute('action')||'#';if(url!=='#'&&action!=='#')return;e.preventDefault();const msg=form.getAttribute('data-guard-msg')||'# ERROR';const hasBootstrap=document.querySelector('link[href*="bootstrap"]')&&window.bootstrap;let container=document.getElementById('toast-container');if(!container){container=document.createElement('div');container.id='toast-container';document.body.appendChild(container);}if(hasBootstrap){const toast=document.createElement('div');toast.className='toast';toast.setAttribute('role','alert');toast.setAttribute('aria-live','assertive');toast.setAttribute('aria-atomic','true');const body=document.createElement('div');body.className='toast-body';body.textContent=msg;toast.appendChild(body);container.appendChild(toast);bootstrap.Toast.getOrCreateInstance(toast).show();}else{alert(msg);}form.setAttribute('data-failed-route','true');}catch(err){}});})();
                                        </script>
                                    @endpush
                                    <div class="{{ VC::FM_G }} {{ VC::C12 }}">
                                        {{ Form::label('content', __('Format'), ['class'=>VC::FM_LB.' text-dark']) }}
                                        <textarea name="content" class="summernote-simple3 summernote-simple">{!! data_get($currnocLang??null,'content','') !!}</textarea>
                                    </div>
                                {{ Form::close() }}
                            </div>
                        </div>
                    </div>
                    <div id="google-calendar" class="card">
                        <div class="col-md-12">
                            @php
                                $settingsGoogleCalendarBaseRouteName=VW::SET.'.google.calendar';
                                $settingsGoogleCalendarKebabRouteName=Str::kebab($settingsGoogleCalendarBaseRouteName);
                                $settingsGoogleCalendarResolvedRouteName=Route::has($settingsGoogleCalendarBaseRouteName)?$settingsGoogleCalendarBaseRouteName:(Route::has($settingsGoogleCalendarKebabRouteName)?$settingsGoogleCalendarKebabRouteName:null);
                                $settingsGoogleCalendarUrl=$settingsGoogleCalendarResolvedRouteName?route($settingsGoogleCalendarResolvedRouteName):'#';
                                $langValue=isset($lang)&&is_string($lang)?$lang:Utility::fetchUserLang();
                                $settingsGoogleCalendarGuardMsg=Utility::fetchLinkMessage($langValue, VW::SET, 'settings_google_calendar_route_unavailable')??__('Settings Google Calendar route is unavailable. Please contact technical support or your domain administrator.');
                                $settingsGoogleCalendarFormId='settings-google-calendar-form';
                            @endphp
                            {!! Form::open(['url'=>$settingsGoogleCalendarUrl,'enctype'=>'multipart/form-data','id'=>$settingsGoogleCalendarFormId,'data-url'=>$settingsGoogleCalendarUrl,'data-guard-msg'=>$settingsGoogleCalendarGuardMsg]) !!}
                                @push(StacksConstants::ADM_SCR_PG)
                                    <script defer src="{{ asset('assets/js/routes/settings/companies/calendar.js') }}"></script>
                                @endpush
                                <div class="card-header">
                                    <div class="{{ VC::RW }}">
                                        <div class="col-6">
                                            <h5 class="mb-2">{{ __('Google Calendar Settings') }}</h5>
                                        </div>
                                        <div class="col switch-width text-end">
                                            <div class="{{ VC::FM_G }} {{ VC::MB0 }}">
                                                <div class="{{ VC::CST_CTL }} custom-switch">
                                                    <input type="checkbox" name="google_calendar_enable" id="google_calendar_enable" data-toggle="switchbutton" data-onstyle="primary" {{ (data_get($setting??[], 'google_calendar_enable','')==='on')?'checked':'' }}>
                                                    <label class="{{ VC::CST_LB }}" for="google_calendar_enable"></label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="{{ VC::RW }}">
                                        <div class="col-lg-6 {{ VC::CM6 }} {{ VC::CS12 }} {{ VC::FM_G }}">
                                            {{ Form::label('Google calendar id', __('Google Calendar Id'), ['class'=>'col-form-label']) }}
                                            {{ Form::text('google_clender_id', old('google_clender_id', (string) data_get($setting??[], 'google_clender_id','')), ['class'=>VC::FM_CT,'placeholder'=>__('Google Calendar Id'),'required'=>'required']) }}
                                        </div>
                                        <div class="col-lg-6 {{ VC::CM6 }} {{ VC::CS12 }} {{ VC::FM_G }}">
                                            {{ Form::label('Google calendar json file', __('Google Calendar json File'), ['class'=>'col-form-label']) }}
                                            <input type="file" class="{{ VC::FM_CT }}" name="google_calendar_json_file" id="file" aria-label="{{ __('Google Calendar json File') }}">
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer text-end">
                                    <button class="btn-submit {{ VC::BT_PRM }}" type="submit">{{ __('Save Changes') }}</button>
                                </div>
                            {{ Form::close() }}
                        </div>
                    </div>
                    <div id="webhook-settings" class="card">
                        <div class="col-md-12">
                            <div class="card-header">
                                <div class="row">
                                    <div class="col-6">
                                        <h5 class="mb-2">{{ __('Webhook Settings') }}</h5>
                                    </div>
                                    @can('create webhook')
                                        <div class="col-6 text-end">
                                            @php
                                                $webhookCreateBaseName=VW::WBH.'.create';
                                                $webhookCreateKebabName=Str::kebab($webhookCreateBaseName);
                                                $webhookCreateResolvedName=Route::has($webhookCreateBaseName)?$webhookCreateBaseName:(Route::has($webhookCreateKebabName)?$webhookCreateKebabName:null);
                                                $webhookCreateUrl=$webhookCreateResolvedName?route($webhookCreateResolvedName):'#';
                                                $webhookCreateGuardMsg=Utility::fetchLinkMessage($langValue, VW::WBH, 'webhook_create_route_unavailable')??__('Webhook create route is unavailable. Please contact technical support or your domain administrator.');
                                                $webhookCreateBtnId='webhook-create-btn';
                                            @endphp
                                            <a id="{{ $webhookCreateBtnId }}" href="{{ $webhookCreateUrl }}" data-url="{{ $webhookCreateUrl }}" data-guard-msg="{{ $webhookCreateGuardMsg }}" data-size="lg" data-ajax-popup="true" data-bs-toggle="tooltip" title="{{ __('Create') }}" data-title="{{ __('Create New Webhook') }}" class="{{ VC::BT_SM_PM }}"><i class="{{ VC::TI_PLS }}"></i></a>
                                            @push(StacksConstants::ADM_SCR_PG)
                                                <script defer src="{{ asset('assets/js/routes/settings/companies/webhook.js') }}"></script>
                                            @endpush
                                        </div>
                                    @endcan
                                </div>
                            </div>
                            <div class="card-body table-border-style">
                                <div class="table-responsive">
                                    @php
                                        $webhookRows=is_iterable($webhookSetting??[])?$webhookSetting:[];
                                    @endphp
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>{{ __('Module') }}</th>
                                                <th>{{ __('Url') }}</th>
                                                <th>{{ __('Method') }}</th>
                                                <th>{{ __('Action') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody class="font-style">
                                            @forelse($webhookRows as $webhooksetting)
                                                <tr>
                                                    <td>{{ (is_string($webhooksetting->module??null)&&$webhooksetting->module!=='')?ucwords($webhooksetting->module):__('No module available') }}</td>
                                                    <td>{{ (is_string($webhooksetting->url??null)&&$webhooksetting->url!=='')?$webhooksetting->url:__('No URL available') }}</td>
                                                    <td>{{ (is_string($webhooksetting->method??null)&&$webhooksetting->method!=='')?ucwords($webhooksetting->method):__('No method available') }}</td>
                                                    <td class="Action">
                                                        <span>
                                                            @can(PermissionsConstants::ED_WHK)
                                                                <div class="action-btn bg-primary ms-2">
                                                                    @php
                                                                        $webhookEditBaseName=VW::WBH.'.edit';
                                                                        $webhookEditKebabName=Str::kebab($webhookEditBaseName);
                                                                        $webhookEditResolvedName=Route::has($webhookEditBaseName)?$webhookEditBaseName:(Route::has($webhookEditKebabName)?$webhookEditKebabName:null);
                                                                        $webhookEditUrl=$webhookEditResolvedName?route($webhookEditResolvedName, (int) data_get($webhooksetting,'id',0)):'#';
                                                                        $webhookEditGuardMsg=Utility::fetchLinkMessage($langValue, VW::WBH, 'webhook_edit_route_unavailable')??__('Webhook edit route is unavailable. Please contact technical support or your domain administrator.');
                                                                        $webhookEditBtnId='webhook-edit-btn-'.data_get($webhooksetting,'id','x');
                                                                    @endphp
                                                                    <a id="{{ $webhookEditBtnId }}" href="{{ $webhookEditUrl }}" data-url="{{ $webhookEditUrl }}" data-guard-msg="{{ $webhookEditGuardMsg }}" class="mx-3 btn btn-sm d-inline-flex align-items-center" data-ajax-popup="true" data-bs-toggle="tooltip" data-size="lg" title="{{ __('Edit') }}" data-title="{{ __('Webhook Edit') }}"><i class="{{ VC::TI_PC_WT }}"></i></a>
                                                                    @push(StacksConstants::ADM_SCR_PG)
                                                                        <script defer>
                                                                            (()=>{const btn=document.getElementById('{{ $webhookEditBtnId }}');if(!btn||btn.getAttribute('data-listener-active')==='true')return;btn.setAttribute('data-listener-active','true');btn.addEventListener('click',e=>{try{const url=btn.getAttribute('data-url')||'#';if(url!=='#')return;e.preventDefault();const msg=btn.getAttribute('data-guard-msg')||'# ERROR';const hasBootstrap=document.querySelector('link[href*="bootstrap"]')&&window.bootstrap;let container=document.getElementById('toast-container');if(!container){container=document.createElement('div');container.id='toast-container';document.body.appendChild(container);}if(hasBootstrap){const toast=document.createElement('div');toast.className='toast';toast.setAttribute('role','alert');toast.setAttribute('aria-live','assertive');toast.setAttribute('aria-atomic','true');const body=document.createElement('div');body.className='toast-body';body.textContent=msg;toast.appendChild(body);container.appendChild(toast);bootstrap.Toast.getOrCreateInstance(toast).show();}else{alert(msg);}btn.setAttribute('data-failed-route','true');}catch(err){}});})();
                                                                        </script>
                                                                    @endpush
                                                                </div>
                                                            @endcan
                                                            @can(PermissionsConstants::DEL_WHK)
                                                                @php
                                                                    $webhookDestroyBaseName=VW::WBH.'.destroy';
                                                                    $webhookDestroyKebabName=Str::kebab($webhookDestroyBaseName);
                                                                    $webhookDestroyResolvedName=Route::has($webhookDestroyBaseName)?$webhookDestroyBaseName:(Route::has($webhookDestroyKebabName)?$webhookDestroyKebabName:null);
                                                                    $webhookDestroyRouteArray=$webhookDestroyResolvedName?[$webhookDestroyResolvedName,(int) data_get($webhooksetting,'id',0)]:['#'];
                                                                    $webhookDestroyUrl=$webhookDestroyResolvedName?route($webhookDestroyResolvedName, (int) data_get($webhooksetting,'id',0)):'#';
                                                                    $webhookDestroyGuardMsg=Utility::fetchLinkMessage($langValue, VW::WBH, 'webhook_destroy_route_unavailable')??__('Webhook destroy route is unavailable. Please contact technical support or your domain administrator.');
                                                                    $webhookDeleteFormId='delete-form-'.data_get($webhooksetting,'id','x');
                                                                    $webhookDeleteBtnId='webhook-destroy-btn-'.data_get($webhooksetting,'id','x');
                                                                @endphp
                                                                <div class="action-btn bg-danger ms-2">
                                                                    {!! Form::open(['method'=>'DELETE','route'=>$webhookDestroyRouteArray,'id'=>$webhookDeleteFormId]) !!}
                                                                        <a id="{{ $webhookDeleteBtnId }}" href="{{ $webhookDestroyUrl }}" data-url="{{ $webhookDestroyUrl }}" data-guard-msg="{{ $webhookDestroyGuardMsg }}" class="{{ VC::BT_SM_CT_PR }}" data-bs-toggle="tooltip" title="{{ __('Delete') }}"><i class="{{ VC::TI_TRS_WT }}"></i></a>
                                                                    {!! Form::close() !!}
                                                                </div>
                                                                @push(StacksConstants::ADM_SCR_PG)
                                                                    <script defer>
                                                                        (()=>{const btn=document.getElementById('{{ $webhookDeleteBtnId }}');if(!btn||btn.getAttribute('data-listener-active')==='true')return;btn.setAttribute('data-listener-active','true');btn.addEventListener('click',e=>{try{const url=btn.getAttribute('data-url')||'#';if(url!=='#')return;e.preventDefault();const msg=btn.getAttribute('data-guard-msg')||'# ERROR';const hasBootstrap=document.querySelector('link[href*="bootstrap"]')&&window.bootstrap;let container=document.getElementById('toast-container');if(!container){container=document.createElement('div');container.id='toast-container';document.body.appendChild(container);}if(hasBootstrap){const toast=document.createElement('div');toast.className='toast';toast.setAttribute('role','alert');toast.setAttribute('aria-live','assertive');toast.setAttribute('aria-atomic','true');const body=document.createElement('div');body.className='toast-body';body.textContent=msg;toast.appendChild(body);container.appendChild(toast);bootstrap.Toast.getOrCreateInstance(toast).show();}else{alert(msg);}btn.setAttribute('data-failed-route','true');}catch(err){}});})();
                                                                    </script>
                                                                @endpush
                                                            @endcan
                                                        </span>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr class="text-center">
                                                    <td colspan="4">{{ __('No data found for webhooks.') }}</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="ip-restriction-settings" class="{{ VC::CD }}">
                        <div class="col-md-12">
                            <div class="card-header">
                                <div class="{{ VC::RW }}">
                                    <div class="col-6">
                                        <h5 class="mb-2">{{ __('IP Restriction Settings') }}</h5>
                                    </div>
                                    @can('create webhook')
                                        <div class="col-6 text-end">
                                            @php
                                                $systemIpCreateBaseName=VW::SYS.'.ip.create';
                                                $systemIpCreateKebabName=Str::kebab($systemIpCreateBaseName);
                                                $systemIpCreateResolvedName=Route::has($systemIpCreateBaseName)?$systemIpCreateBaseName:(Route::has($systemIpCreateKebabName)?$systemIpCreateKebabName:null);
                                                $systemIpCreateUrl=$systemIpCreateResolvedName?route($systemIpCreateResolvedName):'#';
                                                $langValue=isset($lang)&&is_string($lang)?$lang:Utility::fetchUserLang();
                                                $systemIpCreateGuardMsg=Utility::fetchLinkMessage($langValue, VW::SYS, 'system_ip_create_route_unavailable')??__('System IP create route is unavailable. Please contact technical support or your domain administrator.');
                                                $systemIpCreateBtnId='system-ip-create-btn';
                                            @endphp
                                            <a id="{{ $systemIpCreateBtnId }}" href="{{ $systemIpCreateUrl }}" data-url="{{ $systemIpCreateUrl }}" data-guard-msg="{{ $systemIpCreateGuardMsg }}" data-size="md" data-ajax-popup="true" data-bs-toggle="tooltip" title="{{ __('Create') }}" data-title="{{ __('Create New IP') }}" class="{{ VC::BT_SM_PM }}"><i class="{{ VC::TI_PLS }} {{ VC::TXT_WT }}"></i></a>
                                            @push(StacksConstants::ADM_SCR_PG)
                                                <script defer src="{{ asset('assets/js/routes/settings/companies/ip.js') }}"></script>
                                            @endpush
                                        </div>
                                    @endcan
                                </div>
                            </div>
                            <div class="card-body table-border-style">
                                <div class="table-responsive">
                                    @php
                                        $ipRows=is_iterable($ips??[])?$ips:[];
                                    @endphp
                                    <table class="{{ VC::TB }}">
                                        <thead>
                                            <tr>
                                                <th class="w-75">{{ __('IP') }}</th>
                                                <th>{{ __('Action') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody class="font-style">
                                            @forelse($ipRows as $ip)
                                                @php
                                                    $rowId=(int) data_get($ip,'id',0);
                                                    $rowIdStr=$rowId?:'x';
                                                @endphp
                                                <tr>
                                                    <td>{{ (is_string(data_get($ip,'ip'))&&data_get($ip,'ip')!=='')?data_get($ip,'ip'):__('No IP available') }}</td>
                                                    <td class="Action">
                                                        <span>
                                                            @can(PermissionsConstants::ED_WHK)
                                                                <div class="{{ VC::ACT_BTN_PRIM }}">
                                                                    @php
                                                                        $systemIpEditBaseName=VW::SYS.'.ip.edit';
                                                                        $systemIpEditKebabName=Str::kebab($systemIpEditBaseName);
                                                                        $systemIpEditResolvedName=Route::has($systemIpEditBaseName)?$systemIpEditBaseName:(Route::has($systemIpEditKebabName)?$systemIpEditKebabName:null);
                                                                        $systemIpEditUrl=$systemIpEditResolvedName?route($systemIpEditResolvedName,$rowId):'#';
                                                                        $systemIpEditGuardMsg=Utility::fetchLinkMessage($langValue, VW::SYS, 'system_ip_edit_route_unavailable')??__('System IP edit route is unavailable. Please contact technical support or your domain administrator.');
                                                                        $systemIpEditBtnId='system-ip-edit-btn-'.$rowIdStr;
                                                                    @endphp
                                                                    <a id="{{ $systemIpEditBtnId }}" href="{{ $systemIpEditUrl }}" data-url="{{ $systemIpEditUrl }}" data-guard-msg="{{ $systemIpEditGuardMsg }}" class="{{ VC::BT_SM_FL_CT }}" data-ajax-popup="true" data-bs-toggle="tooltip" title="{{ __('Edit') }}" data-title="{{ __('IP Edit') }}"><i class="{{ VC::TI_PC_WT }}"></i></a>
                                                                    @push(StacksConstants::ADM_SCR_PG)
                                                                        <script defer>
                                                                            (()=>{const btn=document.getElementById('{{ $systemIpEditBtnId }}');if(!btn||btn.getAttribute('data-listener-active')==='true')return;btn.setAttribute('data-listener-active','true');btn.addEventListener('click',e=>{try{const url=btn.getAttribute('data-url')||'#';if(url!=='#')return;e.preventDefault();const msg=btn.getAttribute('data-guard-msg')||'';const hasBootstrap=document.querySelector('link[href*="bootstrap"]')&&window.bootstrap;let container=document.getElementById('toast-container');if(!container){container=document.createElement('div');container.id='toast-container';document.body.appendChild(container);}if(hasBootstrap){const toast=document.createElement('div');toast.className='toast';toast.setAttribute('role','alert');toast.setAttribute('aria-live','assertive');toast.setAttribute('aria-atomic','true');const body=document.createElement('div');body.className='toast-body';body.textContent=msg;toast.appendChild(body);container.appendChild(toast);bootstrap.Toast.getOrCreateInstance(toast).show();}else{alert(msg);}btn.setAttribute('data-failed-route','true');}catch(err){}});})();
                                                                        </script>
                                                                    @endpush
                                                                </div>
                                                            @endcan
                                                            @can(PermissionsConstants::DEL_WHK)
                                                                <div class="{{ VC::ACT_BTN_DNG_2 }}">
                                                                    @php
                                                                        $systemIpDestroyBaseName=VW::SYS.'.ip.destroy';
                                                                        $systemIpDestroyKebabName=Str::kebab($systemIpDestroyBaseName);
                                                                        $systemIpDestroyResolvedName=Route::has($systemIpDestroyBaseName)?$systemIpDestroyBaseName:(Route::has($systemIpDestroyKebabName)?$systemIpDestroyKebabName:null);
                                                                        $systemIpDestroyRouteArray=$systemIpDestroyResolvedName?[$systemIpDestroyResolvedName,$rowId]:['#'];
                                                                        $systemIpDestroyUrl=$systemIpDestroyResolvedName?route($systemIpDestroyResolvedName,$rowId):'#';
                                                                        $systemIpDestroyGuardMsg=Utility::fetchLinkMessage($langValue, VW::SYS, 'system_ip_destroy_route_unavailable')??__('System IP destroy route is unavailable. Please contact technical support or your domain administrator.');
                                                                        $systemIpDeleteFormId='delete-form-'.$rowIdStr;
                                                                        $systemIpDeleteBtnId='system-ip-destroy-btn-'.$rowIdStr;
                                                                    @endphp
                                                                    {!! Form::open(['method'=>'DELETE','route'=>$systemIpDestroyRouteArray,'id'=>$systemIpDeleteFormId]) !!}
                                                                        <a id="{{ $systemIpDeleteBtnId }}" href="{{ $systemIpDestroyUrl }}" data-url="{{ $systemIpDestroyUrl }}" data-guard-msg="{{ $systemIpDestroyGuardMsg }}" class="{{ VC::BT_SM_CT_PR }}" data-bs-toggle="tooltip" title="{{ __('Delete') }}"><i class="{{ VC::TI_TRS_WT }}"></i></a>
                                                                    {!! Form::close() !!}
                                                                    @push(StacksConstants::ADM_SCR_PG)
                                                                        <script defer>
                                                                            (()=>{const btn=document.getElementById('{{ $systemIpDeleteBtnId }}');if(!btn||btn.getAttribute('data-listener-active')==='true')return;btn.setAttribute('data-listener-active','true');btn.addEventListener('click',e=>{try{const url=btn.getAttribute('data-url')||'#';if(url!=='#')return;e.preventDefault();const msg=btn.getAttribute('data-guard-msg')||'';const hasBootstrap=document.querySelector('link[href*="bootstrap"]')&&window.bootstrap;let container=document.getElementById('toast-container');if(!container){container=document.createElement('div');container.id='toast-container';document.body.appendChild(container);}if(hasBootstrap){const toast=document.createElement('div');toast.className='toast';toast.setAttribute('role','alert');toast.setAttribute('aria-live','assertive');toast.setAttribute('aria-atomic','true');const body=document.createElement('div');body.className='toast-body';body.textContent=msg;toast.appendChild(body);container.appendChild(toast);bootstrap.Toast.getOrCreateInstance(toast).show();}else{alert(msg);}btn.setAttribute('data-failed-route','true');}catch(err){}});})();
                                                                        </script>
                                                                    @endpush
                                                                </div>
                                                            @endcan
                                                        </span>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr class="text-center">
                                                    <td colspan="2">{{ __('No data found for IP addresses.') }}</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push(StacksConstants::ADM_SCR_PG)
    <script src="{{ asset('css/summernote/summernote-bs4.js') }}"></script>
    <script async src="{{ asset('assets/js/routes/settings/companies/lang/notes.js') }}"></script>
    <script defer>
        (() => {
            const ERR = "# ERROR";
            const D_CLIENT = "data-client-localized";
            const D_MSG = "data-guard-msg";
            const D_BOUND = "data-settings-bound";
            const once = (el, ev, fn, opt) => {
                if (!el) return;
                const h = e => fn(e);
                el.addEventListener(ev, h, { once: true, ...(opt || {}) });
                const mo = new MutationObserver((_, o) => {
                if (!document.body.contains(el)) {
                    el.removeEventListener(ev, h);
                    o.disconnect();
                }
                });
                mo.observe(document.body, { childList: true, subtree: true });
            };
            const langKey = () => {
                let l = (
                window.sessionStorage.getItem("erp-np-lang") ||
                document.documentElement.lang ||
                "en"
                )
                .toLowerCase()
                .replace(/_/g, "-");
                return l === "pt-br" ? l : l.slice(0, 2);
            };
            const t = (k, el) => {
                let v = ERR;
                if (
                el?.getAttribute("data-sv-localized") === "true" ||
                el?.getAttribute(D_CLIENT) === "true"
                ) {
                v = el.getAttribute(D_MSG) || ERR;
                } else {
                v =
                    window.translations?.[langKey()]?.[k] ||
                    el?.getAttribute(D_MSG) ||
                    window.translations?.en?.[k] ||
                    ERR;
                if (v !== ERR) {
                    el?.setAttribute(D_MSG, v);
                    el?.setAttribute(D_CLIENT, "true");
                }
                }
                return v;
            };
            const toast = msg => {
                const hasBs =
                document.querySelector('link[href*="bootstrap"]') &&
                window.bootstrap?.Toast;
                if (hasBs) {
                let el = document.querySelector("#err-toast");
                if (!el) {
                    el = document.createElement("div");
                    el.id = "err-toast";
                    el.className = "toast align-items-center text-bg-danger border-0";
                    el.setAttribute("role", "alert");
                    el.setAttribute("aria-live", "assertive");
                    el.setAttribute("aria-atomic", "true");
                    el.innerHTML = `<div class="d-flex"><div class="toast-body">${msg}</div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button></div>`;
                    document.body.appendChild(el);
                }
                new bootstrap.Toast(el).show();
                } else {
                alert(msg);
                }
            };
            const showOn = (origin, key, ev = "pointerup") =>
                once(document, ev, () => toast(t(key, origin)));
            const safeUrl = u =>
                typeof u === "string" && u.trim() !== "" && u.trim() !== "#";
            const $ = (...a) =>
                window.jQuery?.apply?.(window.jQuery, a) ?? window.jQuery(...a);
            if (typeof jQuery === "undefined") {
                if (
                    window.location.hostname === "localhost" ||
                    window.location.hostname === "127.0.0.1"
                ) console.error("jQuery unavailable");
                return;
            }

            // 1) Summernote blur handlers (4 templates + footer notes) with reuse
            const bindSummernoteSave = (selector, urlKey) => {
                const $els = $(selector);
                if (!$els.length) {
                    return;
                }
                if (!$.fn?.summernote) {
                    if (
                        window.location.hostname === "localhost" ||
                        window.location.hostname === "127.0.0.1"
                    ) console.error("Summernote unavailable");
                    showOn(document.body, "summernote_unavailable");
                    return;
                }
                $els
                .off("summernote.blur.__guard")
                .on("summernote.blur.__guard", function () {
                    const el = this;
                    const url = urlKey();
                    if (!safeUrl(url)) {
                    showOn(el, "save_failed");
                    return;
                    }
                    $.ajax({
                    url: url,
                    type: "POST",
                    data: {
                        _token: $('meta[name="csrf-token"]').attr("content") || "",
                        content: $(el).val() ?? "",
                    },
                    success: res => {
                        if (res?.is_success) {
                        if (typeof show_toastr === "function")
                            show_toastr("success", res.success, "success");
                        } else {
                        showOn(el, "save_failed");
                        }
                    },
                    error: xhr => {
                        const r = xhr?.responseJSON;
                        const ok = r?.is_success === true;
                        if (!ok) {
                        showOn(el, "save_failed");
                        }
                    },
                    });
                });
            };

            bindSummernoteSave(
                ".summernote-simple0",
                () => "{{ route('offer_letter.update', $offerlang) }}"
            );
            bindSummernoteSave(
                ".summernote-simple1",
                () => "{{ route('joining_letter.update', $joininglang) }}"
            );
            bindSummernoteSave(
                ".summernote-simple2",
                () => "{{ route('experience_certificate.update', $explang) }}"
            );
            bindSummernoteSave(
                ".summernote-simple3",
                () => "{{ route('noc.update', $noclang) }}"
            );
            bindSummernoteSave(
                ".summernote-simple4",
                () => "{{ route('systems.settings.footernote') }}"
            ); // footer notes

            // 2) Theme switches
            const darkChk = document.querySelector("#cust-darklayout");
            if (darkChk && !darkChk.getAttribute(D_BOUND)) {
                darkChk.setAttribute(D_BOUND, "1");
                darkChk.addEventListener("click", () => {
                try {
                    const styleEl = document.querySelector("#style");
                    const logo = $(".dash-sidebar .main-logo a img");
                    const darkHref =
                    "{{ env('APP_URL') }}" + "/public/assets/css/style-dark.css";
                    const lightHref =
                    "{{ env('APP_URL') }}" + "/public/assets/css/style.css";
                    if (darkChk.checked) {
                    styleEl?.setAttribute("href", darkHref);
                    if (logo.length) {
                        logo.attr("src", "{{ $logo . $logo_light }}");
                    }
                    } else {
                    styleEl?.setAttribute("href", lightHref);
                    if (logo.length) {
                        logo.attr("src", "{{ $logo . $logo_dark }}");
                    }
                    }
                } catch {
                    showOn(darkChk, "theme_switch_failed", "click");
                }
                });
            }
            const bgChk = document.querySelector("#cust-theme-bg");
            if (bgChk && !bgChk.getAttribute(D_BOUND)) {
                bgChk.setAttribute(D_BOUND, "1");
                bgChk.addEventListener("click", () => {
                try {
                    const sb = document.querySelector(".dash-sidebar");
                    const hd = document.querySelector(".dash-header:not(.dash-mob-header)");
                    if (bgChk.checked) {
                    sb?.classList.add("transprent-bg");
                    hd?.classList.add("transprent-bg");
                    } else {
                    sb?.classList.remove("transprent-bg");
                    hd?.classList.remove("transprent-bg");
                    }
                } catch {
                    showOn(bgChk, "theme_switch_failed", "click");
                }
                });
            }

            // 3) Live previews (invoice / proposal / bill)
            $(document).on(
                "change",
                "select[name='invoice_template'], input[name='invoice_color']",
                function () {
                try {
                    const template = $("select[name='invoice_template']").val() ?? "";
                    const color = $("input[name='invoice_color']:checked").val() ?? "";
                    const src = `{{ url('/invoices/preview') }}/${template}/${color}`;
                    if (document.querySelector("#invoice_frame"))
                    $("#invoice_frame").attr("src", src);
                } catch {
                    showOn(this, "preview_update_failed", "click");
                }
                }
            );
            $(document).on(
                "change",
                "select[name='proposal_template'], input[name='proposal_color']",
                function () {
                try {
                    const template = $("select[name='proposal_template']").val() ?? "";
                    const color = $("input[name='proposal_color']:checked").val() ?? "";
                    const src = `{{ url('/'.VW::PPS.'/preview') }}/${template}/${color}`;
                    if (document.querySelector("#proposal_frame"))
                    $("#proposal_frame").attr("src", src);
                } catch {
                    showOn(this, "preview_update_failed", "click");
                }
                }
            );
            $(document).on(
                "change",
                "select[name='bill_template'], input[name='bill_color']",
                function () {
                try {
                    const template = $("select[name='bill_template']").val() ?? "";
                    const color = $("input[name='bill_color']:checked").val() ?? "";
                    const src = `{{ url('/bill/preview') }}/${template}/${color}`;
                    if (document.querySelector("#bill_frame"))
                    $("#bill_frame").attr("src", src);
                } catch {
                    showOn(this, "preview_update_failed", "click");
                }
                }
            );

            // 4) ScrollSpy (Bootstrap)
            try {
                if (window.bootstrap?.ScrollSpy) {
                new bootstrap.ScrollSpy(document.body, {
                    target: "#useradd-sidenav",
                    offset: 300,
                });
                } else {
                /* no bootstrap: silently ignore */
                }
            } catch {
                showOn(document.body, "scrollspy_failed", "click");
            }

            // 5) Theme color radio sync
            $(document).on("click", ".themes-color-change", function () {
                try {
                const color = $(this).data("value");
                $(".theme-color").prop("checked", false);
                $(".themes-color-change").removeClass("active_color");
                $(this).addClass("active_color");
                $(`input[value=${color}]`).prop("checked", true);
                } catch {
                /* non-critical */
                }
            });

            // 6) Image previews on file inputs (IDs may be constants)
            const bindPreview = (inputId, imgId) => {
                const i = document.getElementById(inputId);
                const img = document.getElementById(imgId);
                if (!i || !img) return;
                if (i.getAttribute(D_BOUND) === "1") return;
                i.setAttribute(D_BOUND, "1");
                i.addEventListener("change", () => {
                try {
                    const f = i.files?.[0];
                    if (!f) return;
                    const src = URL.createObjectURL(f);
                    img.src = src;
                } catch {
                    showOn(i, "image_preview_failed", "click");
                }
                });
            };
            bindPreview(String("{{SC::CPN_LG_DK}}"), "image");
            bindPreview("company_logo_light", "image1");
            bindPreview(String("{{SC::CPN_FAVICON_K}}"), "image2");

            // 7) VAT/GST toggle
            $(document).on("change", "#vat_gst_number_switch", function () {
                try {
                $(this).is(":checked")
                    ? $(".tax_type_div").removeClass("d-none")
                    : $(".tax_type_div").addClass("d-none");
                } catch {
                showOn(this, "tax_toggle_failed", "click");
                }
            });

            // 8) Mail dialog + test send
            $(document).on("click", ".send_email", function (e) {
                e.preventDefault();
                const el = this;
                const title = $(el).attr("data-title") || "";
                const size = "md";
                const url = $(el).attr("data-url") || "";
                if (!safeUrl(url)) {
                showOn(el, "send_email_failed");
                return;
                }
                try {
                $("#commonModal .modal-title").html(title);
                $("#commonModal .modal-dialog").addClass("modal-" + size);
                $("#commonModal").modal("show");
                $.post(
                    url,
                    {
                    _token: "{{ csrf_token() }}",
                    mail_driver: $("#mail_driver").val(),
                    mail_host: $("#mail_host").val(),
                    mail_port: $("#mail_port").val(),
                    mail_username: $("#mail_username").val(),
                    mail_password: $("#mail_password").val(),
                    mail_encryption: $("#mail_encryption").val(),
                    mail_from_address: $("#mail_from_address").val(),
                    mail_from_name: $("#mail_from_name").val(),
                    },
                    data => {
                    $("#commonModal .body").html(data);
                    }
                ).fail(() => showOn(el, "send_email_failed"));
                } catch {
                showOn(el, "send_email_failed");
                }
            });

            $(document).on("submit", "#test_email", function (e) {
                e.preventDefault();
                const form = this;
                const url = $(form).attr("action") || "";
                if (!safeUrl(url)) {
                showOn(form, "test_email_failed");
                return;
                }
                const post = $(form).serialize();
                $.ajax({
                type: "post",
                url,
                data: post,
                cache: false,
                beforeSend: () =>
                    $("#test_email .btn-create").attr("disabled", "disabled"),
                success: data => {
                    if (data?.success) {
                    show_toastr?.("success", data.message, "success");
                    } else {
                    showOn(form, "test_email_failed");
                    }
                    $("#commonModal").modal("hide");
                },
                complete: () => $("#test_email .btn-create").removeAttr("disabled"),
                }).fail(() => showOn(form, "test_email_failed"));
            });
        })();
    </script>
@endpush