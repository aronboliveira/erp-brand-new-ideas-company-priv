<?php
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
?>

<?php $__env->startSection(YieldingConstants::ADM_PG_TTL); ?>
    <?php echo e(__('Settings')); ?>

<?php $__env->stopSection(); ?>
<?php $__env->startSection(YieldingConstants::ADM_BDC); ?>
    <li class="breadcrumb-item">
        <a href="<?php echo e(Route::has('dashboard') ? route('dashboard') : '#'); ?>"
        <?php echo e(Route::has('dashboard') ? '' : 'aria-disabled="true"'); ?>>
            <?php echo e(__('Dashboard')); ?>

        </a>
    </li>
    <li class="breadcrumb-item"><?php echo e(__('Settings')); ?></li>
<?php $__env->stopSection(); ?>
<?php $__env->startPush(StacksConstants::ADM_CSS); ?>
    <link rel="stylesheet" href="<?php echo e(asset('css/summernote/summernote-bs4.css')); ?>">
<?php $__env->stopPush(); ?>
<?php $__env->startSection(YieldingConstants::ADM_CTT); ?>
    <div class="row">
        <div class="col-sm-12">
            <div class="row">
                <div class="col-xl-3">
                    <div class="<?php echo e(VC::CD_STK); ?>" style="top:30px">
                        <?php
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
                        ?>
                        <div class="list-group list-group-flush" id="useradd-sidenav">
                            <?php if(Utility::isFilled($anchors)): ?>
                                <?php $__currentLoopData = $anchors; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $anchor): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php
                                        $label = preg_replace('/-+/', ' ', $anchor);
                                        $label = ucwords($label);
                                    ?>
                                    <a href="#<?php echo e($anchor); ?>"
                                        class="list-group-item list-group-item-action border-0">
                                        <?php echo e(__($label)); ?>

                                        <div class="float-end"><i class="<?php echo e(VC::TI_CHV_RT); ?>"></i></div>
                                    </a>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            <?php else: ?>
                                <div class="list-group-item border-0">
                                    <?php echo e(__('No setting links available')); ?>

                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="col-xl-9">
                    <?php if(!empty($setting) && isset($setting)): ?>
                        <?php
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
                        ?>
                        <div id="brand-settings" class="card">
                            <?php echo Form::model($setting??[],['route'=>$businessSettingRouteArray,'method'=>'POST','enctype'=>'multipart/form-data','id'=>$businessSettingFormId,'data-url'=>$businessSettingUrl,'data-guard-msg'=>$businessSettingGuardMsg]); ?>

                                <?php echo csrf_field(); ?>
                                <?php $__env->startPush(StacksConstants::ADM_SCR_PG); ?>
                                    <script defer src="<?php echo e(asset('assets/js/routes/settings/companies/business.js')); ?>"></script>
                                <?php $__env->stopPush(); ?>
                                <div class="card-header">
                                    <h5><?php echo e(__('Brand Settings')); ?></h5>
                                    <small class="text-muted"><?php echo e(__('Edit your brand details')); ?></small>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-lg-4 col-sm-6 col-md-6">
                                            <div class="<?php echo e(VC::CD); ?> logo_card">
                                                <div class="card-header"><h5><?php echo e(__('Logo Dark')); ?></h5></div>
                                                <div class="card-body pt-0">
                                                    <div class="setting-card">
                                                        <div class="logo-content <?php echo e(VC::MT4); ?>">
                                                            <img id="image" src="<?php echo e($logoDarkUrl); ?>" class="big-logo" alt="<?php echo e(__('Company dark logo')); ?>">
                                                        </div>
                                                        <div class="choose-files mt-5">
                                                            <label for="company_logo_dark">
                                                                <div class="<?php echo e(VC::BG_P); ?> company_logo_update"><i class="ti ti-upload px-1"></i><?php echo e(__('Choose file here')); ?></div>
                                                                <input type="file" name="company_logo_dark" id="company_logo_dark" class="<?php echo e(VC::FM_CT); ?> file setting_logo" accept="image/*" aria-label="<?php echo e(__('Upload dark logo')); ?>" data-filename="company_logo_update">
                                                            </label>
                                                        </div>
                                                        <?php $__errorArgs = ['company_logo_dark'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                                            <div class="<?php echo e(VC::RW); ?>"><span class="invalid-logo" role="alert"><strong class="text-danger"><?php echo e(!empty($message) ? $message : __('No message available')); ?></strong></span></div>
                                                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-4 col-sm-6 col-md-6">
                                            <div class="<?php echo e(VC::CD); ?> logo_card">
                                                <div class="card-header"><h5><?php echo e(__('Logo Light')); ?></h5></div>
                                                <div class="card-body pt-0">
                                                    <div class="setting-card">
                                                        <div class="logo-content <?php echo e(VC::MT4); ?>">
                                                            <img id="image1" src="<?php echo e($logoLightUrl); ?>" class="big-logo img_setting" alt="<?php echo e(__('Company light logo')); ?>">
                                                        </div>
                                                        <div class="choose-files mt-5">
                                                            <label for="company_logo_light">
                                                                <div class="<?php echo e(VC::BG_P); ?> dark_logo_update"><i class="ti ti-upload px-1"></i><?php echo e(__('Choose file here')); ?></div>
                                                                <input type="file" class="<?php echo e(VC::FM_CT); ?> file setting_logo" name="company_logo_light" id="company_logo_light" accept="image/*" aria-label="<?php echo e(__('Upload light logo')); ?>" data-filename="dark_logo_update">
                                                            </label>
                                                        </div>
                                                        <?php $__errorArgs = ['company_logo_light'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                                            <div class="<?php echo e(VC::RW); ?>"><span class="invalid-logo" role="alert"><strong class="text-danger"><?php echo e(!empty($message) ? $message : __('No message available')); ?></strong></span></div>
                                                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-4 col-sm-6 col-md-6">
                                            <div class="<?php echo e(VC::CD); ?> logo_card">
                                                <div class="card-header"><h5><?php echo e(__('Favicon')); ?></h5></div>
                                                <div class="card-body pt-0">
                                                    <div class="setting-card">
                                                        <div class="logo-content <?php echo e(VC::MT4); ?>">
                                                            <img id="image2" src="<?php echo e($faviconUrl); ?>" width="50" class="img_setting" alt="<?php echo e(__('Favicon')); ?>">
                                                        </div>
                                                        <div class="choose-files mt-5">
                                                            <label for="company_favicon">
                                                                <div class="<?php echo e(VC::BG_P); ?> company_favicon_update"><i class="ti ti-upload px-1"></i><?php echo e(__('Choose file here')); ?></div>
                                                                <input type="file" class="<?php echo e(VC::FM_CT); ?> file setting_logo" id="company_favicon" name="company_favicon" accept="image/*" aria-label="<?php echo e(__('Upload favicon')); ?>" data-filename="company_favicon_update">
                                                            </label>
                                                        </div>
                                                        <?php $__errorArgs = ['company_favicon'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                                            <div class="<?php echo e(VC::RW); ?>"><span class="invalid-logo" role="alert"><strong class="text-danger"><?php echo e(!empty($message) ? $message : __('No message available')); ?></strong></span></div>
                                                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group col-md-3">
                                            <?php echo e(Form::label('title_text', __('Title Text'), ['class'=>VC::FM_LB])); ?>

                                            <?php echo e(Form::text('title_text', $setting['title_text']??null, ['class'=>VC::FM_CT,'placeholder'=>__('Title Text')])); ?>

                                            <?php $__errorArgs = ['title_text'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                                <span class="invalid-title_text" role="alert"><strong class="text-danger"><?php echo e(!empty($message) ? $message : __('No message available')); ?></strong></span>
                                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                        </div>
                                        <div class="col-md-3 <?php echo e(VC::FM_G); ?>">
                                            <?php echo e(Form::label(SC::FT_TXT, __('Footer Text'), ['class'=>VC::FM_LB])); ?>

                                            <?php echo e(Form::text(SC::FT_TXT, Utility::getValByName(SC::FT_TXT)??__('No footer text available'), ['class'=>VC::FM_CT,'placeholder'=>__('Enter Footer Text')])); ?>

                                            <?php $__errorArgs = [SC::FT_TXT];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                                <span class="invalid-footer_text" role="alert"><strong class="text-danger"><?php echo e(!empty($message) ? $message : __('No message available')); ?></strong></span>
                                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="<?php echo e(VC::FM_G); ?>">
                                                <?php echo e(Form::label(SC::DEF_LNG, __('Default Language'), ['class'=>VC::FM_LB.' text-dark'])); ?>

                                                <div class="changeLanguage">
                                                    <select name="default_language" id="default_language" class="<?php echo e(VC::FM_CT_SL); ?>">
                                                        <?php if(Utility::isFilled($langs)): ?>
                                                            <?php $__currentLoopData = $langs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $code=>$language): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                                <option value="<?php echo e($code); ?>" <?php if($langValue===$code): echo 'selected'; endif; ?>><?php echo e(ucfirst((string)$language)); ?></option>
                                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                        <?php else: ?>
                                                            <option value="<?php echo e($langValue); ?>" selected><?php echo e(ucfirst((string)$currLang)); ?></option>
                                                        <?php endif; ?>
                                                    </select>
                                                </div>
                                                <?php $__errorArgs = [SC::DEF_LNG];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                                    <span class="invalid-default_language" role="alert"><strong class="text-danger"><?php echo e(!empty($message) ? $message : __('No message available')); ?></strong></span>
                                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                            </div>
                                        </div>
                                        <div class="<?php echo e(VC::FM_G); ?> col-md-3">
                                            <div class="<?php echo e(VC::CST_CTL); ?> custom-switch">
                                                <label class="text-dark mb-1 mt-1" for="SITE_RTL"><?php echo e(__('Enable RTL')); ?></label>
                                                <div>
                                                    <input type="checkbox" name="SITE_RTL" id="SITE_RTL" data-toggle="switchbutton" data-onstyle="primary" <?php echo e((($siteRtl??'')==='on')?'checked':''); ?>>
                                                    <label class="<?php echo e(VC::CST_LB); ?>" for="SITE_RTL"></label>
                                                </div>
                                            </div>
                                        </div>
                                        <h5 class="small-title mt-2"><?php echo e(__('Theme Customizer')); ?></h5>
                                        <div class="setting-card setting-logo-box">
                                            <div class="<?php echo e(VC::RW); ?>">
                                                <div class="<?php echo e(VC::CL_XL4); ?>">
                                                    <h6 class="<?php echo e(VC::MT1); ?>"><i data-feather="credit-card" class="me-2"></i><?php echo e(__('Primary color settings')); ?></h6>
                                                    <hr class="my-2" />
                                                    <div class="theme-color themes-color">
                                                        <?php $__currentLoopData = range(1,10); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $themeNumber): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                            <?php $themeValue='theme-'.$themeNumber; ?>
                                                            <a href="#!" class="themes-color-change <?php echo e($color===$themeValue?'active_color':''); ?>" data-value="<?php echo e($themeValue); ?>" aria-label="<?php echo e(__('Choose :theme color',['theme'=>$themeValue])); ?>"></a>
                                                            <input type="radio" class="theme_color d-none" name="color" value="<?php echo e($themeValue); ?>" <?php if($color===$themeValue): echo 'checked'; endif; ?>>
                                                            <?php if($themeNumber===5): ?><br><?php endif; ?>
                                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                    </div>
                                                </div>
                                                <div class="<?php echo e(VC::CL_XL4); ?>">
                                                    <h6 class="<?php echo e(VC::MT1); ?>"><i data-feather="layout" class="me-2"></i><?php echo e(__('Sidebar settings')); ?></h6>
                                                    <hr class="<?php echo e(VC::MT1); ?>" />
                                                    <div class="form-check form-switch">
                                                        <input type="checkbox" class="form-check-input" id="cust-theme-bg" name="cust_theme_bg" <?php echo e((data_get($setting??[],SC::CST_BG,'')==='on')?'checked':''); ?> />
                                                        <label class="form-check-label <?php echo e(VC::FW600); ?> ps-1" for="cust-theme-bg"><?php echo e(__('Transparent layout')); ?></label>
                                                    </div>
                                                </div>
                                                <div class="<?php echo e(VC::CL_XL4); ?>">
                                                    <h6 class="<?php echo e(VC::MT1); ?>"><i data-feather="sun" class="me-2"></i><?php echo e(__('Layout settings')); ?></h6>
                                                    <hr class="<?php echo e(VC::MT1); ?>" />
                                                    <div class="form-check form-switch mt-2">
                                                        <input type="checkbox" class="form-check-input" id="cust-darklayout" name="cust_darklayout" <?php echo e((data_get($colorSettings??[],SC::CST_DRK,'')==='on')?'checked':''); ?> />
                                                        <label class="form-check-label <?php echo e(VC::FW600); ?> ps-1" for="cust-darklayout"><?php echo e(__('Dark Layout')); ?></label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer text-end">
                                    <div class="form-group">
                                        <input class="<?php echo e(VC::BT_PR_PRM10); ?>" type="submit" value="<?php echo e(__('Save Changes')); ?>">
                                    </div>
                                </div>
                            <?php echo e(Form::close()); ?>

                        </div>
                        <?php
                            $systemSettingsRouteBaseName=VW::SYS.'.settings';
                            $systemSettingsKebabRouteName=Str::kebab($systemSettingsRouteBaseName);
                            $systemSettingsResolvedName=Route::has($systemSettingsRouteBaseName)?$systemSettingsRouteBaseName:(Route::has($systemSettingsKebabRouteName)?$systemSettingsKebabRouteName:null);
                            $systemSettingsRouteArray=$systemSettingsResolvedName?[$systemSettingsResolvedName]:['#'];
                            $systemSettingsUrl=$systemSettingsResolvedName?route($systemSettingsResolvedName):'#';
                            $systemSettingsGuardMsg=Utility::fetchLinkMessage(isset($lang)&&is_string($lang)?$lang:Utility::fetchUserLang(),VW::SYS,'system_settings_route_unavailable')??__('System settings route is unavailable. Please contact technical support or your domain administrator.');
                            $systemSettingsFormId='system-settings-form';
                        ?>
                        <div id="system-settings" class="card">
                            <div class="card-header">
                                <h5><?php echo e(__('System Settings')); ?></h5>
                                <small class="text-muted"><?php echo e(__('Edit your system details')); ?></small>
                            </div>
                            <?php echo Form::model($setting??[],['route'=>$systemSettingsRouteArray,'method'=>'POST','id'=>$systemSettingsFormId,'data-url'=>$systemSettingsUrl,'data-guard-msg'=>$systemSettingsGuardMsg]); ?>

                                <?php $__env->startPush(StacksConstants::ADM_SCR_PG); ?>
                                    <script defer src="<?php echo e(asset('assets/js/routes/settings/companies/system.js')); ?>"></script>
                                <?php $__env->stopPush(); ?>
                                <div class="card-body">
                                    <div class="<?php echo e(VC::RW); ?>">
                                        <div class="<?php echo e(VC::FM_G); ?> <?php echo e(VC::CM6); ?>">
                                            <?php echo e(Form::label('site_currency', __('Currency *'), ['class'=>VC::FM_LB])); ?>

                                            <?php echo e(Form::text('site_currency', data_get($setting??[], 'site_currency'), ['class'=>VC::FM_CT.' font-style','required','placeholder'=>__('Enter Currency')])); ?>

                                            <small><?php echo e(__('Note: Add currency code as per three-letter ISO code.')); ?><br><a href="https://stripe.com/docs/currencies" target="_blank"><?php echo e(__('You can find out how to do that here.')); ?></a></small><br>
                                            <?php $__errorArgs = ['site_currency'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                                <span class="invalid-site_currency" role="alert"><strong class="text-danger"><?php echo e(!empty($message) ? $message : __('No message available')); ?></strong></span>
                                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                        </div>
                                        <div class="<?php echo e(VC::FM_G); ?> <?php echo e(VC::CM6); ?>">
                                            <?php echo e(Form::label('site_currency_symbol', __('Currency Symbol *'), ['class'=>VC::FM_LB])); ?>

                                            <?php echo e(Form::text('site_currency_symbol', data_get($setting??[], 'site_currency_symbol'), ['class'=>VC::FM_CT])); ?>

                                            <?php $__errorArgs = ['site_currency_symbol'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                                <span class="invalid-site_currency_symbol" role="alert"><strong class="text-danger"><?php echo e(!empty($message) ? $message : __('No message available')); ?></strong></span>
                                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                        </div>
                                        <div class="<?php echo e(VC::FM_G); ?> <?php echo e(VC::CM6); ?>">
                                            <label class="<?php echo e(VC::FM_LB); ?>"><?php echo e(__('Currency Symbol Position')); ?></label>
                                            <div class="<?php echo e(VC::RW); ?> ms-1">
                                                <div class="form-check <?php echo e(VC::CM6); ?>">
                                                    <input class="form-check-input" type="radio" name="site_currency_symbol_position" value="pre" id="symbol_pos_pre" <?php if((data_get($setting??[], 'site_currency_symbol_position',''))==='pre'): echo 'checked'; endif; ?>>
                                                    <label class="form-check-label" for="symbol_pos_pre"><?php echo e(__('Pre')); ?></label>
                                                </div>
                                                <div class="form-check <?php echo e(VC::CM6); ?>">
                                                    <input class="form-check-input" type="radio" name="site_currency_symbol_position" value="post" id="symbol_pos_post" <?php if((data_get($setting??[], 'site_currency_symbol_position',''))==='post'): echo 'checked'; endif; ?>>
                                                    <label class="form-check-label" for="symbol_pos_post"><?php echo e(__('Post')); ?></label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="<?php echo e(VC::FM_G); ?> <?php echo e(VC::CM6); ?>">
                                            <?php echo e(Form::label('decimal_number', __('Decimal Number Format'), ['class'=>VC::FM_LB])); ?>

                                            <?php echo e(Form::number('decimal_number', data_get($setting??[], 'decimal_number'), ['class'=>VC::FM_CT])); ?>

                                            <?php $__errorArgs = ['decimal_number'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                                <span class="invalid-decimal_number" role="alert"><strong class="text-danger"><?php echo e(!empty($message) ? $message : __('No message available')); ?></strong></span>
                                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                        </div>
                                        <div class="<?php echo e(VC::FM_G); ?> <?php echo e(VC::CM6); ?>">
                                            <label for="site_date_format" class="<?php echo e(VC::FM_LB); ?>"><?php echo e(__('Date Format')); ?></label>
                                            <select name="site_date_format" id="site_date_format" class="<?php echo e(VC::FM_CT); ?> selectric">
                                                <option value="M j, Y" <?php if((data_get($setting??[], 'site_date_format',''))==='M j, Y'): echo 'selected'; endif; ?>>Jan 1,2015</option>
                                                <option value="d-m-Y" <?php if((data_get($setting??[], 'site_date_format',''))==='d-m-Y'): echo 'selected'; endif; ?>>dd-mm-yyyy</option>
                                                <option value="m-d-Y" <?php if((data_get($setting??[], 'site_date_format',''))==='m-d-Y'): echo 'selected'; endif; ?>>mm-dd-yyyy</option>
                                                <option value="Y-m-d" <?php if((data_get($setting??[], 'site_date_format',''))==='Y-m-d'): echo 'selected'; endif; ?>>yyyy-mm-dd</option>
                                            </select>
                                        </div>
                                        <div class="<?php echo e(VC::FM_G); ?> <?php echo e(VC::CM6); ?>">
                                            <label for="site_time_format" class="<?php echo e(VC::FM_LB); ?>"><?php echo e(__('Time Format')); ?></label>
                                            <select name="site_time_format" id="site_time_format" class="<?php echo e(VC::FM_CT); ?> selectric">
                                                <option value="g:i A" <?php if((data_get($setting??[], 'site_time_format',''))==='g:i A'): echo 'selected'; endif; ?>>10:30 PM</option>
                                                <option value="g:i a" <?php if((data_get($setting??[], 'site_time_format',''))==='g:i a'): echo 'selected'; endif; ?>>10:30 pm</option>
                                                <option value="H:i" <?php if((data_get($setting??[], 'site_time_format',''))==='H:i'): echo 'selected'; endif; ?>>22:30</option>
                                            </select>
                                        </div>
                                        <div class="<?php echo e(VC::FM_G); ?> <?php echo e(VC::CM6); ?>">
                                            <?php echo e(Form::label('customer_prefix', __('Customer Prefix'), ['class'=>VC::FM_LB])); ?>

                                            <?php echo e(Form::text('customer_prefix', data_get($setting??[], 'customer_prefix'), ['class'=>VC::FM_CT])); ?>

                                            <?php $__errorArgs = ['customer_prefix'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                                <span class="invalid-customer_prefix" role="alert"><strong class="text-danger"><?php echo e(!empty($message) ? $message : __('No message available')); ?></strong></span>
                                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                        </div>
                                        <div class="<?php echo e(VC::FM_G); ?> <?php echo e(VC::CM6); ?>">
                                            <?php echo e(Form::label('vendor_prefix', __('Vendor Prefix'), ['class'=>VC::FM_LB])); ?>

                                            <?php echo e(Form::text('vendor_prefix', data_get($setting??[], 'vendor_prefix'), ['class'=>VC::FM_CT])); ?>

                                            <?php $__errorArgs = ['vendor_prefix'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                                <span class="invalid-vendor_prefix" role="alert"><strong class="text-danger"><?php echo e(!empty($message) ? $message : __('No message available')); ?></strong></span>
                                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                        </div>
                                        <div class="<?php echo e(VC::FM_G); ?> <?php echo e(VC::CM6); ?>">
                                            <?php echo e(Form::label('proposal_prefix', __('Proposal Prefix'), ['class'=>VC::FM_LB])); ?>

                                            <?php echo e(Form::text('proposal_prefix', data_get($setting??[], 'proposal_prefix'), ['class'=>VC::FM_CT])); ?>

                                            <?php $__errorArgs = ['proposal_prefix'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                                <span class="invalid-proposal_prefix" role="alert"><strong class="text-danger"><?php echo e(!empty($message) ? $message : __('No message available')); ?></strong></span>
                                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                        </div>
                                        <div class="<?php echo e(VC::FM_G); ?> <?php echo e(VC::CM6); ?>">
                                            <?php echo e(Form::label('invoice_prefix', __('Invoice Prefix'), ['class'=>VC::FM_LB])); ?>

                                            <?php echo e(Form::text('invoice_prefix', data_get($setting??[], 'invoice_prefix'), ['class'=>VC::FM_CT])); ?>

                                            <?php $__errorArgs = ['invoice_prefix'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                                <span class="invalid-invoice_prefix" role="alert"><strong class="text-danger"><?php echo e(!empty($message) ? $message : __('No message available')); ?></strong></span>
                                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                        </div>
                                        <div class="<?php echo e(VC::FM_G); ?> <?php echo e(VC::CM6); ?>">
                                            <?php echo e(Form::label('bill_prefix', __('Bill Prefix'), ['class'=>VC::FM_LB])); ?>

                                            <?php echo e(Form::text('bill_prefix', data_get($setting??[], 'bill_prefix'), ['class'=>VC::FM_CT])); ?>

                                            <?php $__errorArgs = ['bill_prefix'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                                <span class="invalid-bill_prefix" role="alert"><strong class="text-danger"><?php echo e(!empty($message) ? $message : __('No message available')); ?></strong></span>
                                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                        </div>
                                        <div class="<?php echo e(VC::FM_G); ?> <?php echo e(VC::CM6); ?>">
                                            <?php echo e(Form::label('purchase_prefix', __('Purchase Prefix'), ['class'=>VC::FM_LB])); ?>

                                            <?php echo e(Form::text('purchase_prefix', data_get($setting??[], 'purchase_prefix'), ['class'=>VC::FM_CT])); ?>

                                            <?php $__errorArgs = ['purchase_prefix'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                                <span class="invalid-purchase_prefix" role="alert"><strong class="text-danger"><?php echo e(!empty($message) ? $message : __('No message available')); ?></strong></span>
                                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                        </div>
                                        <div class="<?php echo e(VC::FM_G); ?> <?php echo e(VC::CM6); ?>">
                                            <?php echo e(Form::label('pos_prefix', __('Pos Prefix'), ['class'=>VC::FM_LB])); ?>

                                            <?php echo e(Form::text('pos_prefix', data_get($setting??[], 'pos_prefix'), ['class'=>VC::FM_CT])); ?>

                                            <?php $__errorArgs = ['pos_prefix'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                                <span class="invalid-pos_prefix" role="alert"><strong class="text-danger"><?php echo e(!empty($message) ? $message : __('No message available')); ?></strong></span>
                                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                        </div>
                                        <div class="<?php echo e(VC::FM_G); ?> <?php echo e(VC::CM6); ?>">
                                            <?php echo e(Form::label('journal_prefix', __('Journal Prefix'), ['class'=>VC::FM_LB])); ?>

                                            <?php echo e(Form::text('journal_prefix', data_get($setting??[], 'journal_prefix'), ['class'=>VC::FM_CT])); ?>

                                            <?php $__errorArgs = ['journal_prefix'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                                <span class="invalid-journal_prefix" role="alert"><strong class="text-danger"><?php echo e(!empty($message) ? $message : __('No message available')); ?></strong></span>
                                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                        </div>
                                        <div class="<?php echo e(VC::FM_G); ?> <?php echo e(VC::CM6); ?>">
                                            <?php echo e(Form::label('expense_prefix', __('Expense Prefix'), ['class'=>VC::FM_LB])); ?>

                                            <?php echo e(Form::text('expense_prefix', data_get($setting??[], 'expense_prefix'), ['class'=>VC::FM_CT])); ?>

                                            <?php $__errorArgs = ['expense_prefix'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                                <span class="invalid-expense_prefix" role="alert"><strong class="text-danger"><?php echo e(!empty($message) ? $message : __('No message available')); ?></strong></span>
                                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                        </div>
                                        <div class="<?php echo e(VC::FM_G); ?> <?php echo e(VC::CM6); ?>">
                                            <?php echo e(Form::label('shipping_display', __('Display Shipping in Proposal / Invoice / Bill'), ['class'=>VC::FM_LB])); ?>

                                            <div class="form-switch form-switch-left">
                                                <input type="checkbox" class="form-check-input <?php echo e(VC::MT3); ?>" name="shipping_display" id="shipping_display" <?php echo e((data_get($setting??[], 'shipping_display',''))==='on'?'checked':''); ?>>
                                                <label class="form-check-label" for="shipping_display"></label>
                                            </div>
                                            <?php $__errorArgs = ['shipping_display'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                                <span class="invalid-shipping_display" role="alert"><strong class="text-danger"><?php echo e(!empty($message) ? $message : __('No message available')); ?></strong></span>
                                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                        </div>
                                        <div class="<?php echo e(VC::FM_G); ?> <?php echo e(VC::C12); ?>">
                                            <?php echo e(Form::label('footer_title', __('Proposal/Invoice/Bill/Purchase/POS Footer Title'), ['class'=>VC::FM_LB])); ?>

                                            <?php echo e(Form::text('footer_title', data_get($setting??[], 'footer_title'), ['class'=>VC::FM_CT])); ?>

                                            <?php $__errorArgs = ['footer_title'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                                <span class="invalid-footer_title" role="alert"><strong class="text-danger"><?php echo e(!empty($message) ? $message : __('No message available')); ?></strong></span>
                                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                        </div>
                                        <div class="<?php echo e(VC::FM_G); ?> <?php echo e(VC::C12); ?>">
                                            <?php echo e(Form::label('footer_notes', __('Proposal/Invoice/Bill/Purchase/POS Footer Note'), ['class'=>VC::FM_LB])); ?>

                                            <?php echo e(Form::textarea('footer_notes', data_get($setting??[], 'footer_notes'), ['class'=>'summernote-simple4 summernote-simple'])); ?>

                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer text-end">
                                    <div class="<?php echo e(VC::FM_G); ?>">
                                        <input class="<?php echo e(VC::BT_PR_PRM10); ?>" type="submit" value="<?php echo e(__('Save Changes')); ?>">
                                    </div>
                                </div>
                            <?php echo e(Form::close()); ?>

                        </div>
                        <?php
                            $cpSettingsRouteBaseName=VW::CP.'.settings';
                            $cpSettingsKebabRouteName=Str::kebab($cpSettingsRouteBaseName);
                            $cpSettingsResolvedName=Route::has($cpSettingsRouteBaseName)?$cpSettingsRouteBaseName:(Route::has($cpSettingsKebabRouteName)?$cpSettingsKebabRouteName:null);
                            $cpSettingsRouteArray=$cpSettingsResolvedName?[$cpSettingsResolvedName]:['#'];
                            $cpSettingsUrl=$cpSettingsResolvedName?route($cpSettingsResolvedName):'#';
                            $cpSettingsGuardMsg=Utility::fetchLinkMessage(isset($lang)&&is_string($lang)?$lang:Utility::fetchUserLang(),VW::CP,'company_settings_route_unavailable')??__('Company settings route is unavailable. Please contact technical support or your domain administrator.');
                            $cpSettingsFormId='cp-settings-form';
                        ?>
                        <div id="company-settings" class="card">
                            <div class="card-header">
                                <h5><?php echo e(__('Company Settings')); ?></h5>
                                <small class="text-muted"><?php echo e(__('Edit your company details')); ?></small>
                            </div>
                            <?php echo Form::model($setting??[],['route'=>$cpSettingsRouteArray,'method'=>'POST','id'=>$cpSettingsFormId,'data-url'=>$cpSettingsUrl,'data-guard-msg'=>$cpSettingsGuardMsg]); ?>

                                <?php $__env->startPush(StacksConstants::ADM_SCR_PG); ?>
                                    <script defer src="<?php echo e(asset('assets/js/routes/settings/companies/company.js')); ?>"></script>
                                <?php $__env->stopPush(); ?>
                                <div class="card-body">
                                    <div class="<?php echo e(VC::RW); ?>">
                                        <div class="<?php echo e(VC::FM_G); ?> col-md-6">
                                            <?php echo e(Form::label('company_name', __('Company Name *'), ['class'=>VC::FM_LB])); ?>

                                            <?php echo e(Form::text('company_name', data_get($setting??[], 'company_name'), ['class'=>VC::FM_CT.' font-style'])); ?>

                                            <?php $__errorArgs = ['company_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                                <span class="invalid-company_name" role="alert"><strong class="text-danger"><?php echo e(!empty($message) ? $message : __('No message available')); ?></strong></span>
                                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                        </div>
                                        <div class="<?php echo e(VC::FM_G); ?> col-md-6">
                                            <?php echo e(Form::label('company_address', __('Address'), ['class'=>VC::FM_LB])); ?>

                                            <?php echo e(Form::text('company_address', data_get($setting??[], 'company_address'), ['class'=>VC::FM_CT.' font-style'])); ?>

                                            <?php $__errorArgs = ['company_address'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                                <span class="invalid-company_address" role="alert"><strong class="text-danger"><?php echo e(!empty($message) ? $message : __('No message available')); ?></strong></span>
                                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                        </div>
                                        <div class="<?php echo e(VC::FM_G); ?> col-md-6">
                                            <?php echo e(Form::label('company_city', __('City'), ['class'=>VC::FM_LB])); ?>

                                            <?php echo e(Form::text('company_city', data_get($setting??[], 'company_city'), ['class'=>VC::FM_CT.' font-style'])); ?>

                                            <?php $__errorArgs = ['company_city'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                                <span class="invalid-company_city" role="alert"><strong class="text-danger"><?php echo e(!empty($message) ? $message : __('No message available')); ?></strong></span>
                                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                        </div>
                                        <div class="<?php echo e(VC::FM_G); ?> col-md-6">
                                            <?php echo e(Form::label('company_state', __('State'), ['class'=>VC::FM_LB])); ?>

                                            <?php echo e(Form::text('company_state', data_get($setting??[], 'company_state'), ['class'=>VC::FM_CT.' font-style'])); ?>

                                            <?php $__errorArgs = ['company_state'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                                <span class="invalid-company_state" role="alert"><strong class="text-danger"><?php echo e(!empty($message) ? $message : __('No message available')); ?></strong></span>
                                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                        </div>
                                        <div class="<?php echo e(VC::FM_G); ?> col-md-6">
                                            <?php echo e(Form::label('company_zipcode', __('Zip/Post Code'), ['class'=>VC::FM_LB])); ?>

                                            <?php echo e(Form::text('company_zipcode', data_get($setting??[], 'company_zipcode'), ['class'=>VC::FM_CT])); ?>

                                            <?php $__errorArgs = ['company_zipcode'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                                <span class="invalid-company_zipcode" role="alert"><strong class="text-danger"><?php echo e(!empty($message) ? $message : __('No message available')); ?></strong></span>
                                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                        </div>
                                        <div class="<?php echo e(VC::FM_G); ?> col-md-6">
                                            <?php echo e(Form::label('company_country', __('Country'), ['class'=>VC::FM_LB])); ?>

                                            <?php echo e(Form::text('company_country', data_get($setting??[], 'company_country'), ['class'=>VC::FM_CT.' font-style'])); ?>

                                            <?php $__errorArgs = ['company_country'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                                <span class="invalid-company_country" role="alert"><strong class="text-danger"><?php echo e(!empty($message) ? $message : __('No message available')); ?></strong></span>
                                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                        </div>
                                        <div class="<?php echo e(VC::FM_G); ?> col-md-6">
                                            <?php echo e(Form::label('company_telephone', __('Telephone'), ['class'=>VC::FM_LB])); ?>

                                            <?php echo e(Form::text('company_telephone', data_get($setting??[], 'company_telephone'), ['class'=>VC::FM_CT])); ?>

                                            <?php $__errorArgs = ['company_telephone'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                                <span class="invalid-company_telephone" role="alert"><strong class="text-danger"><?php echo e(!empty($message) ? $message : __('No message available')); ?></strong></span>
                                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                        </div>
                                        <div class="<?php echo e(VC::FM_G); ?> col-md-6">
                                            <?php echo e(Form::label('registration_number', __('Company Registration Number *'), ['class'=>VC::FM_LB])); ?>

                                            <?php echo e(Form::text('registration_number', data_get($setting??[], 'registration_number'), ['class'=>VC::FM_CT])); ?>

                                            <?php $__errorArgs = ['registration_number'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                                <span class="invalid-registration_number" role="alert"><strong class="text-danger"><?php echo e(!empty($message) ? $message : __('No message available')); ?></strong></span>
                                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                        </div>
                                        <div class="<?php echo e(VC::FM_G); ?> col-md-4">
                                            <?php echo e(Form::label('company_start_time', __('Company Start Time *'), ['class'=>VC::FM_LB])); ?>

                                            <?php echo e(Form::time('company_start_time', data_get($setting??[], 'company_start_time'), ['class'=>VC::FM_CT])); ?>

                                            <?php $__errorArgs = ['company_start_time'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                                <span class="invalid-company_start_time" role="alert"><strong class="text-danger"><?php echo e(!empty($message) ? $message : __('No message available')); ?></strong></span>
                                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                        </div>
                                        <div class="<?php echo e(VC::FM_G); ?> col-md-4">
                                            <?php echo e(Form::label('company_end_time', __('Company End Time *'), ['class'=>VC::FM_LB])); ?>

                                            <?php echo e(Form::time('company_end_time', data_get($setting??[], 'company_end_time'), ['class'=>VC::FM_CT])); ?>

                                            <?php $__errorArgs = ['company_end_time'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                                <span class="invalid-company_end_time" role="alert"><strong class="text-danger"><?php echo e(!empty($message) ? $message : __('No message available')); ?></strong></span>
                                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                        </div>
                                        <div class="<?php echo e(VC::FM_G); ?> col-md-4">
                                            <label class="<?php echo e(VC::FM_LB); ?>" for="ip_restrict"><?php echo e(__('Ip Restrict')); ?></label>
                                            <div class="custom-control custom-switch mt-2">
                                                <input type="checkbox" class="form-check-input" data-toggle="switchbutton" data-onstyle="primary" name="ip_restrict" id="ip_restrict" <?php echo e((data_get($setting??[], 'ip_restrict',''))==='on'?'checked':''); ?>>
                                            </div>
                                        </div>
                                        <div class="<?php echo e(VC::FM_G); ?> col-md-12 mt-2">
                                            <?php echo e(Form::label('timezone', __('Timezone'), ['class'=>VC::FM_LB])); ?>

                                            <select name="timezone" class="<?php echo e(VC::FM_CT); ?> custom-select" id="timezone">
                                                <option value=""><?php echo e(__('Select Timezone')); ?></option>
                                                <?php if(is_iterable($timezones??[])): ?>
                                                    <?php $__currentLoopData = $timezones; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k=>$timezone): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <option value="<?php echo e($k); ?>" <?php echo e((data_get($setting??[], 'timezone')==$k)?'selected':''); ?>><?php echo e($timezone); ?></option>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                <?php else: ?>
                                                    <option value="<?php echo e(data_get($setting??[], 'timezone')); ?>" selected><?php echo e(data_get($setting??[], 'timezone', __('No timezone available'))); ?></option>
                                                <?php endif; ?>
                                            </select>
                                        </div>
                                        <div class="<?php echo e(VC::FM_G); ?> col-md-6">
                                            <div class="<?php echo e(VC::RW); ?> <?php echo e(VC::MT4); ?>">
                                                <div class="col-md-6">
                                                    <label for="vat_gst_number_switch"><?php echo e(__('Tax Number')); ?></label>
                                                    <div class="form-check form-switch custom-switch-v1 float-end">
                                                        <input type="checkbox" name="vat_gst_number_switch" class="form-check-input input-primary pointer" value="on" id="vat_gst_number_switch" <?php echo e((data_get($setting??[], 'vat_gst_number_switch',''))==='on'?' checked ':''); ?>>
                                                        <label class="form-check-label" for="vat_gst_number_switch"></label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="<?php echo e(VC::FM_G); ?> col-md-6 tax_type_div <?php echo e((data_get($setting??[], 'vat_gst_number_switch','')!=='on')?' d-none ':''); ?>">
                                            <div class="<?php echo e(VC::RW); ?>">
                                                <div class="col-md-6">
                                                    <div class="<?php echo e(VC::FM_CHK_IL); ?> <?php echo e(VC::FM_GB3); ?>">
                                                        <input type="radio" id="customRadio8" name="tax_type" value="VAT" class="form-check-input" <?php echo e((data_get($setting??[], 'tax_type',''))==='VAT'?'checked':''); ?>>
                                                        <label class="form-check-label" for="customRadio8"><?php echo e(__('VAT Number')); ?></label>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="<?php echo e(VC::FM_CHK_IL); ?> <?php echo e(VC::FM_GB3); ?>">
                                                        <input type="radio" id="customRadio7" name="tax_type" value="GST" class="form-check-input" <?php echo e((data_get($setting??[], 'tax_type',''))==='GST'?'checked':''); ?>>
                                                        <label class="form-check-label" for="customRadio7"><?php echo e(__('GST Number')); ?></label>
                                                    </div>
                                                </div>
                                            </div>
                                            <?php echo e(Form::text('vat_number', data_get($setting??[], 'vat_number'), ['class'=>VC::FM_CT,'placeholder'=>__('Enter VAT / GST Number')])); ?>

                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer text-end">
                                    <div class="<?php echo e(VC::FM_G); ?>">
                                        <input class="<?php echo e(VC::BT_PR_PRM10); ?>" type="submit" value="<?php echo e(__('Save Changes')); ?>">
                                    </div>
                                </div>
                            <?php echo e(Form::close()); ?>

                        </div>
                        <?php
                            $cpEmailSettingsRouteBaseName=VW::CP.'.email.settings';
                            $cpEmailSettingsKebabRouteName=Str::kebab($cpEmailSettingsRouteBaseName);
                            $cpEmailSettingsResolvedName=Route::has($cpEmailSettingsRouteBaseName)?$cpEmailSettingsRouteBaseName:(Route::has($cpEmailSettingsKebabRouteName)?$cpEmailSettingsKebabRouteName:null);
                            $cpEmailSettingsRouteArray=$cpEmailSettingsResolvedName?[$cpEmailSettingsResolvedName]:['#'];
                            $cpEmailSettingsUrl=$cpEmailSettingsResolvedName?route($cpEmailSettingsResolvedName):'#';
                            $langValue=isset($lang)&&is_string($lang)?$lang:Utility::fetchUserLang();
                            $cpEmailSettingsGuardMsg=Utility::fetchLinkMessage($langValue, VW::CP, 'company_email_settings_route_unavailable')??__('Company email settings route is unavailable. Please contact technical support or your domain administrator.');
                            $cpEmailSettingsFormId='cp-email-settings-form';
                        ?>
                        <div id="email-settings" class="card">
                            <div class="card-header">
                                <h5><?php echo e(__('Email Settings')); ?></h5>
                            </div>
                            <?php echo Form::model($setting??[],['route'=>$cpEmailSettingsRouteArray,'method'=>'post','id'=>$cpEmailSettingsFormId,'data-url'=>$cpEmailSettingsUrl,'data-guard-msg'=>$cpEmailSettingsGuardMsg]); ?>

                                <?php $__env->startPush(StacksConstants::ADM_SCR_PG); ?>
                                    <script defer src="<?php echo e(asset('assets/js/routes/settings/companies/email.js')); ?>"></script>
                                <?php $__env->stopPush(); ?>
                                <div class="card-body">
                                    <?php echo csrf_field(); ?>
                                    <div class="<?php echo e(VC::RW); ?>">
                                        <div class="col-md-4">
                                            <div class="<?php echo e(VC::FM_G); ?>">
                                                <?php echo e(Form::label('mail_driver', __('Mail Driver'), ['class'=>VC::FM_LB])); ?>

                                                <?php echo e(Form::text('mail_driver', old('mail_driver', data_get($emailSetting??[], 'mail_driver','')), ['class'=>VC::FM_CT,'placeholder'=>__('Enter Mail Driver')])); ?>

                                                <?php $__errorArgs = ['mail_driver'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                                    <span class="invalid-mail_driver" role="alert"><strong class="text-danger"><?php echo e(!empty($message) ? $message : __('No message available')); ?></strong></span>
                                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="<?php echo e(VC::FM_G); ?>">
                                                <?php echo e(Form::label('mail_host', __('Mail Host'), ['class'=>VC::FM_LB])); ?>

                                                <?php echo e(Form::text('mail_host', old('mail_host', data_get($emailSetting??[], 'mail_host','')), ['class'=>VC::FM_CT,'placeholder'=>__('Enter Mail Host')])); ?>

                                                <?php $__errorArgs = ['mail_host'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                                    <span class="invalid-mail_host" role="alert"><strong class="text-danger"><?php echo e(!empty($message) ? $message : __('No message available')); ?></strong></span>
                                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="<?php echo e(VC::FM_G); ?>">
                                                <?php echo e(Form::label('mail_port', __('Mail Port'), ['class'=>VC::FM_LB])); ?>

                                                <?php echo e(Form::text('mail_port', old('mail_port', data_get($emailSetting??[], 'mail_port','')), ['class'=>VC::FM_CT,'placeholder'=>__('Enter Mail Port')])); ?>

                                                <?php $__errorArgs = ['mail_port'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                                    <span class="invalid-mail_port" role="alert"><strong class="text-danger"><?php echo e(!empty($message) ? $message : __('No message available')); ?></strong></span>
                                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="<?php echo e(VC::RW); ?>">
                                        <div class="col-md-4">
                                            <div class="<?php echo e(VC::FM_G); ?>">
                                                <?php echo e(Form::label('mail_username', __('Mail Username'), ['class'=>VC::FM_LB])); ?>

                                                <?php echo e(Form::text('mail_username', old('mail_username', data_get($emailSetting??[], 'mail_username','')), ['class'=>VC::FM_CT,'placeholder'=>__('Enter Mail Username')])); ?>

                                                <?php $__errorArgs = ['mail_username'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                                    <span class="invalid-mail_username" role="alert"><strong class="text-danger"><?php echo e(!empty($message) ? $message : __('No message available')); ?></strong></span>
                                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="<?php echo e(VC::FM_G); ?>">
                                                <?php echo e(Form::label('mail_password', __('Mail Password'), ['class'=>VC::FM_LB])); ?>

                                                <?php echo e(Form::text('mail_password', old('mail_password', data_get($emailSetting??[], 'mail_password','')), ['class'=>VC::FM_CT,'placeholder'=>__('Enter Mail Password')])); ?>

                                                <?php $__errorArgs = ['mail_password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                                    <span class="invalid-mail_password" role="alert"><strong class="text-danger"><?php echo e(!empty($message) ? $message : __('No message available')); ?></strong></span>
                                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="<?php echo e(VC::FM_G); ?>">
                                                <?php echo e(Form::label('mail_encryption', __('Mail Encryption'), ['class'=>VC::FM_LB])); ?>

                                                <?php echo e(Form::text('mail_encryption', old('mail_encryption', data_get($emailSetting??[], 'mail_encryption','')), ['class'=>VC::FM_CT,'placeholder'=>__('Enter Mail Encryption')])); ?>

                                                <?php $__errorArgs = ['mail_encryption'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                                    <span class="invalid-mail_encryption" role="alert"><strong class="text-danger"><?php echo e(!empty($message) ? $message : __('No message available')); ?></strong></span>
                                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="<?php echo e(VC::RW); ?>">
                                        <div class="col-md-4">
                                            <div class="<?php echo e(VC::FM_G); ?>">
                                                <?php echo e(Form::label('mail_from_address', __('Mail From Address'), ['class'=>VC::FM_LB])); ?>

                                                <?php echo e(Form::email('mail_from_address', old('mail_from_address', data_get($emailSetting??[], 'mail_from_address','')), ['class'=>VC::FM_CT,'placeholder'=>__('Enter Mail From Address')])); ?>

                                                <?php $__errorArgs = ['mail_from_address'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                                    <span class="invalid-mail_from_address" role="alert"><strong class="text-danger"><?php echo e(!empty($message) ? $message : __('No message available')); ?></strong></span>
                                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="<?php echo e(VC::FM_G); ?>">
                                                <?php echo e(Form::label('mail_from_name', __('Mail From Name'), ['class'=>VC::FM_LB])); ?>

                                                <?php echo e(Form::text('mail_from_name', old('mail_from_name', data_get($emailSetting??[], 'mail_from_name','')), ['class'=>VC::FM_CT,'placeholder'=>__('Enter Mail From Name')])); ?>

                                                <?php $__errorArgs = ['mail_from_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                                    <span class="invalid-mail_from_name" role="alert"><strong class="text-danger"><?php echo e(!empty($message) ? $message : __('No message available')); ?></strong></span>
                                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="<?php echo e(VC::RW); ?>">
                                    <div class="card-footer <?php echo e(VC::DFL); ?> <?php echo e(VC::JCE); ?>">
                                        <div class="<?php echo e(VC::FM_G); ?> me-2">
                                            <?php
                                                $sendTestMailBaseName=VW::TT.'.mail';
                                                $sendTestMailKebabName=Str::kebab($sendTestMailBaseName);
                                                $sendTestMailResolvedName=Route::has($sendTestMailBaseName)?$sendTestMailBaseName:(Route::has($sendTestMailKebabName)?$sendTestMailKebabName:null);
                                                $sendTestMailUrl=$sendTestMailResolvedName?route($sendTestMailResolvedName):'#';
                                                $sendTestMailGuardMsg=Utility::fetchLinkMessage($langValue, VW::TT, 'send_test_mail_route_unavailable')??__('Send test mail route is unavailable. Please contact technical support or your domain administrator.');
                                            ?>
                                            <a id="send-test-mail-btn" href="<?php echo e($sendTestMailUrl); ?>" data-url="<?php echo e($sendTestMailUrl); ?>" data-guard-msg="<?php echo e($sendTestMailGuardMsg); ?>" data-title="<?php echo e(__('Send Test Mail')); ?>" class="<?php echo e(VC::BT_PRM); ?> send_email"><?php echo e(__('Send Test Mail')); ?></a>
                                            <?php $__env->startPush(StacksConstants::ADM_SCR_PG); ?>
                                                <script defer src="<?php echo e(asset('assets/js/routes/settings/companies/emailTest.js')); ?>"></script>
                                            <?php $__env->stopPush(); ?>
                                        </div>
                                        <div class="<?php echo e(VC::FM_G); ?>">
                                            <input class="<?php echo e(VC::BT_PRM); ?>" type="submit" value="<?php echo e(__('Save Changes')); ?>">
                                        </div>
                                    </div>
                                </div>
                            <?php echo e(Form::close()); ?>

                        </div>
                        <?php
                            $timeTrackersSettingsBaseName=VW::TMT.'.settings';
                            $timeTrackersSettingsKebabName=Str::kebab($timeTrackersSettingsBaseName);
                            $timeTrackersSettingsResolvedName=Route::has($timeTrackersSettingsBaseName)?$timeTrackersSettingsBaseName:(Route::has($timeTrackersSettingsKebabName)?$timeTrackersSettingsKebabName:null);
                            $timeTrackersSettingsRouteArray=$timeTrackersSettingsResolvedName?[$timeTrackersSettingsResolvedName]:['#'];
                            $timeTrackersSettingsUrl=$timeTrackersSettingsResolvedName?route($timeTrackersSettingsResolvedName):'#';
                            $langValue=isset($lang)&&is_string($lang)?$lang:Utility::fetchUserLang();
                            $timeTrackersSettingsGuardMsg=Utility::fetchLinkMessage($langValue, VW::TMT, 'time_trackers_settings_route_unavailable')??__('Time trackers settings route is unavailable. Please contact technical support or your domain administrator.');
                            $timeTrackersSettingsFormId='time-trackers-settings-form';
                        ?>
                        <div id="tracker-settings" class="card">
                            <div class="card-header">
                                <h5><?php echo e(__('Time Tracker Settings')); ?></h5>
                                <small class="text-muted"><?php echo e(__('Edit your Time Tracker settings')); ?></small>
                            </div>
                            <?php echo Form::model($setting??[],['route'=>$timeTrackersSettingsRouteArray,'method'=>'post','id'=>$timeTrackersSettingsFormId,'data-url'=>$timeTrackersSettingsUrl,'data-guard-msg'=>$timeTrackersSettingsGuardMsg]); ?>

                                <?php $__env->startPush(StacksConstants::ADM_SCR_PG); ?>
                                    <script defer src="<?php echo e(asset('assets/js/routes/settings/companies/tracker.js')); ?>"></script>
                                <?php $__env->stopPush(); ?>
                                <div class="card-body">
                                    <div class="<?php echo e(VC::RW); ?>">
                                        <div class="<?php echo e(VC::FM_G); ?> <?php echo e(VC::CM6); ?>">
                                            <label class="<?php echo e(VC::FM_LB); ?>"><?php echo e(__('Application URL')); ?></label>
                                            <small><?php echo e(__('Application URL to log into the app.')); ?></small>
                                            <?php echo e(Form::text('apps_url', old('apps_url', URL::to('/')), ['class'=>VC::FM_CT,'placeholder'=>__('Application URL'),'readonly'=>true])); ?>

                                        </div>
                                        <div class="<?php echo e(VC::FM_G); ?> <?php echo e(VC::CM6); ?>">
                                            <label class="<?php echo e(VC::FM_LB); ?>"><?php echo e(__('Tracking Interval')); ?></label>
                                            <small><?php echo e(__('Image Screenshot Take Interval time ( 1 = 1 min)')); ?></small>
                                            <?php echo e(Form::number('interval_time', old('interval_time', data_get($setting??[], 'interval_time','10')), ['class'=>VC::FM_CT,'placeholder'=>__('Enter Tracking Interval Time'),'min'=>1,'step'=>1])); ?>

                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer <?php echo e(VC::DFL); ?> <?php echo e(VC::JCE); ?>">
                                    <div class="<?php echo e(VC::FM_G); ?>">
                                        <input class="<?php echo e(VC::BT_PR_PRM10); ?>" type="submit" value="<?php echo e(__('Save Changes')); ?>">
                                    </div>
                                </div>
                            <?php echo e(Form::close()); ?>

                        </div>
                        <?php
                            $cpPaymentSettingsBaseRouteName=VW::CP.'.payment.settings';
                            $cpPaymentSettingsKebabRouteName=Str::kebab($cpPaymentSettingsBaseRouteName);
                            $cpPaymentSettingsResolvedRouteName=Route::has($cpPaymentSettingsBaseRouteName)?$cpPaymentSettingsBaseRouteName:(Route::has($cpPaymentSettingsKebabRouteName)?$cpPaymentSettingsKebabRouteName:null);
                            $cpPaymentSettingsRouteArray=$cpPaymentSettingsResolvedRouteName?[$cpPaymentSettingsResolvedRouteName]:['#'];
                            $cpPaymentSettingsUrl=$cpPaymentSettingsResolvedRouteName?route($cpPaymentSettingsResolvedRouteName):'#';
                            $langValue=isset($lang)&&is_string($lang)?$lang:Utility::fetchUserLang();
                            $cpPaymentSettingsGuardMsg=Utility::fetchLinkMessage($langValue, VW::CP, 'company_payment_settings_route_unavailable')??__('Company payment settings route is unavailable. Please contact technical support or your domain administrator.');
                            $cpPaymentSettingsFormId='cp-payment-settings-form';
                        ?>
                        <div class="card" id="payment-settings">
                            <div class="card-header">
                                <h5><?php echo e(__('Payment Settings')); ?></h5>
                                <small class="text-secondary font-weight-bold"><?php echo e(__('These details will be used to collect invoice payments. Each invoice will have a payment button based on the below configuration.')); ?></small>
                            </div>
                            <?php echo Form::model($setting??[],['route'=>$cpPaymentSettingsRouteArray,'method'=>'POST','id'=>$cpPaymentSettingsFormId,'data-url'=>$cpPaymentSettingsUrl,'data-guard-msg'=>$cpPaymentSettingsGuardMsg]); ?>

                                <?php $__env->startPush(StacksConstants::ADM_SCR_PG); ?>
                                    <script defer src="<?php echo e(asset('assets/js/routes/settings/companies/payment.js')); ?>"></script>
                                <?php $__env->stopPush(); ?>
                                <?php echo csrf_field(); ?>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-12">
                                            <div class="faq justify-content-center">
                                                <div class="row">
                                                    <div class="col-12">
                                                        <?php
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
                                                        ?>
                                                        <div class="accordion accordion-flush setting-accordion" id="accordionExample">
                                                            <?php if(is_iterable($gateways??[]) && (is_array($gateways)?count($gateways):count($gateways))): ?>
                                                                <?php $__currentLoopData = $gateways; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $gw): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                                    <?php
                                                                        $enabledKey=data_get($gw,'enabled');
                                                                        $isEnabled=old($enabledKey, data_get($company_payment_setting??[], $enabledKey,'off'))==='on';
                                                                        $key=data_get($gw,'key','gw');
                                                                        $headingId="heading_{$key}";
                                                                        $collapseId="collapse_{$key}";
                                                                        $switchId="switch_{$enabledKey}";
                                                                    ?>
                                                                    <div class="accordion-item">
                                                                        <h2 class="accordion-header" id="<?php echo e($headingId); ?>">
                                                                            <button class="accordion-button <?php echo e($isEnabled?'':'collapsed'); ?>" type="button" data-bs-toggle="collapse" data-bs-target="#<?php echo e($collapseId); ?>" aria-expanded="<?php echo e($isEnabled?'true':'false'); ?>" aria-controls="<?php echo e($collapseId); ?>">
                                                                                <span class="<?php echo e(VC::DFL_AIC); ?>"><?php echo e(data_get($gw,'title',__('No gateway title available'))); ?></span>
                                                                                <div class="<?php echo e(VC::DFL_AIC); ?>">
                                                                                    <span class="me-2"><?php echo e(__('Enable')); ?>:</span>
                                                                                    <div class="form-check form-switch custom-switch-v1">
                                                                                        <input type="hidden" name="<?php echo e($enabledKey); ?>" value="off">
                                                                                        <input type="checkbox" class="form-check-input input-primary" id="<?php echo e($switchId); ?>" name="<?php echo e($enabledKey); ?>" <?php if($isEnabled): echo 'checked'; endif; ?>>
                                                                                    </div>
                                                                                </div>
                                                                            </button>
                                                                        </h2>
                                                                        <div id="<?php echo e($collapseId); ?>" class="accordion-collapse collapse <?php echo e($isEnabled?'show':''); ?>" aria-labelledby="<?php echo e($headingId); ?>" data-bs-parent="#accordionExample">
                                                                            <div class="accordion-body">
                                                                                <?php if(data_get($gw,'radios')): ?>
                                                                                    <?php
                                                                                        $rName=data_get($gw,'radios.name');
                                                                                        $rDefault=data_get($gw,'radios.default');
                                                                                        $rValue=old($rName, data_get($company_payment_setting??[], $rName, $rDefault));
                                                                                        $options=data_get($gw,'radios.options',[]);
                                                                                    ?>
                                                                                    <div class="<?php echo e(VC::C12); ?> <?php echo e(VC::MB4); ?>">
                                                                                        <label class="<?php echo e(VC::FM_LB); ?>" for="<?php echo e($rName); ?>"><?php echo e(Str::headline(str_replace('_',' ',$rName))); ?></label>
                                                                                        <div class="<?php echo e(VC::DFL); ?>">
                                                                                            <?php $__currentLoopData = $options; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $val=>$label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                                                                <div class="me-2" style="margin-right: 15px;">
                                                                                                    <div class="<?php echo e(VC::BD); ?> <?php echo e(VC::CD); ?> <?php echo e(VC::P4); ?>">
                                                                                                        <div class="form-check">
                                                                                                            <label class="form-check-label text-dark me-2">
                                                                                                                <input type="radio" class="form-check-input" name="<?php echo e($rName); ?>" value="<?php echo e($val); ?>" <?php echo e($rValue===$val?'checked':''); ?>>
                                                                                                                <?php echo e($label); ?>

                                                                                                            </label>
                                                                                                        </div>
                                                                                                    </div>
                                                                                                </div>
                                                                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                                                        </div>
                                                                                    </div>
                                                                                <?php endif; ?>
                                                                                <div class="<?php echo e(VC::RW); ?> gy-4">
                                                                                    <?php $__currentLoopData = (array) data_get($gw,'fields',[]); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $f): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                                                        <?php
                                                                                            $name=data_get($f,'name');
                                                                                            $type=data_get($f,'type','text');
                                                                                            $label=data_get($f,'label',Str::headline(str_replace('_',' ',$name)));
                                                                                            $col=data_get($f,'col','col-lg-6');
                                                                                            $val=old($name, data_get($company_payment_setting??[], $name, data_get($f,'value','')));
                                                                                            $attrs=array_merge(['class'=>VC::FM_CT,'placeholder'=>data_get($f,'placeholder',$label)], (array) data_get($f,'attrs',[]));
                                                                                            if($type==='password'){ $val=null; $attrs['autocomplete']='off'; $attrs['spellcheck']='false'; }
                                                                                        ?>
                                                                                        <div class="<?php echo e($col); ?>">
                                                                                            <div class="input-edits">
                                                                                                <div class="<?php echo e(VC::FM_G); ?>">
                                                                                                    <?php echo e(Form::label($name, $label, ['class'=>VC::FM_LB])); ?>

                                                                                                    <?php switch($type):
                                                                                                        case ('textarea'): ?>
                                                                                                            <?php echo e(Form::textarea($name, $val, array_merge($attrs,['rows'=>data_get($f,'rows',4)]))); ?>

                                                                                                            <?php break; ?>
                                                                                                        <?php case ('number'): ?>
                                                                                                            <?php echo e(Form::number($name, $val, $attrs)); ?>

                                                                                                            <?php break; ?>
                                                                                                        <?php case ('password'): ?>
                                                                                                            <?php echo e(Form::password($name, $attrs)); ?>

                                                                                                            <?php break; ?>
                                                                                                        <?php case ('email'): ?>
                                                                                                            <?php echo e(Form::email($name, $val, $attrs)); ?>

                                                                                                            <?php break; ?>
                                                                                                        <?php default: ?>
                                                                                                            <?php echo e(Form::text($name, $val, $attrs)); ?>

                                                                                                    <?php endswitch; ?>
                                                                                                    <?php if(isset($f['hint'])): ?>
                                                                                                        <small class="<?php echo e(VC::TXS); ?>"><?php echo $f['hint']; ?></small>
                                                                                                    <?php endif; ?>
                                                                                                    <?php $__errorArgs = [$name];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                                                                                        <span class="invalid-feedback <?php echo e(VC::DBL); ?>"><?php echo e($message); ?></span>
                                                                                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                                                                                </div>
                                                                                            </div>
                                                                                        </div>
                                                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                            <?php else: ?>
                                                                <div class="alert alert-warning"><?php echo e(__('No payment gateways found.')); ?></div>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer text-end">
                                    <div class="form-group">
                                        <input class="<?php echo e(VC::BT_PR_PRM10); ?>" type="submit" value="<?php echo e(__('Save Changes')); ?>">
                                    </div>
                                </div>
                            <?php echo e(Form::close()); ?>

                        </div>
                        <?php
                            $zoomSettingsBaseRouteName='zoom.settings';
                            $zoomSettingsKebabRouteName=Str::kebab($zoomSettingsBaseRouteName);
                            $zoomSettingsResolvedName=Route::has($zoomSettingsBaseRouteName)?$zoomSettingsBaseRouteName:(Route::has($zoomSettingsKebabRouteName)?$zoomSettingsKebabRouteName:null);
                            $zoomSettingsRouteArray=$zoomSettingsResolvedName?[$zoomSettingsResolvedName]:['#'];
                            $zoomSettingsUrl=$zoomSettingsResolvedName?route($zoomSettingsResolvedName):'#';
                            $langValue=isset($lang)&&is_string($lang)?$lang:Utility::fetchUserLang();
                            $zoomSettingsGuardMsg=Utility::fetchLinkMessage($langValue,'zoom','zoom_settings_route_unavailable')??__('Zoom settings route is unavailable. Please contact technical support or your domain administrator.');
                            $zoomSettingsFormId='zoom-settings-form';
                        ?>
                        <div id="zoom-settings" class="card">
                            <div class="card-header">
                                <h5><?php echo e(__('Zoom Settings')); ?></h5>
                                <small class="text-muted"><?php echo e(__('Edit your Zoom settings')); ?></small>
                            </div>
                            <?php echo Form::model($setting??[],['route'=>$zoomSettingsRouteArray,'method'=>'post','id'=>$zoomSettingsFormId,'data-url'=>$zoomSettingsUrl,'data-guard-msg'=>$zoomSettingsGuardMsg]); ?>

                                <?php $__env->startPush(StacksConstants::ADM_SCR_PG); ?>
                                    <script defer src="<?php echo e(asset('assets/js/routes/settings/companies/zoom.js')); ?>"></script>
                                <?php $__env->stopPush(); ?>
                                <?php echo csrf_field(); ?>
                                <div class="card-body">
                                    <div class="<?php echo e(VC::RW); ?>">
                                        <div class="<?php echo e(VC::FM_G); ?> <?php echo e(VC::CM6); ?>">
                                            <label class="<?php echo e(VC::FM_LB); ?>"><?php echo e(__('Zoom Account ID')); ?></label>
                                            <?php echo e(Form::text('zoom_account_id', old('zoom_account_id', data_get($setting??[], 'zoom_account_id','')), ['class'=>VC::FM_CT,'placeholder'=>__('Enter Zoom Account ID')])); ?>

                                            <?php $__errorArgs = ['zoom_account_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                                <span class="invalid-feedback <?php echo e(VC::DBL); ?>"><?php echo e($message ?? __('No message available')); ?></span>
                                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                        </div>
                                        <div class="<?php echo e(VC::FM_G); ?> <?php echo e(VC::CM6); ?>">
                                            <label class="<?php echo e(VC::FM_LB); ?>"><?php echo e(__('Zoom Client ID')); ?></label>
                                            <?php echo e(Form::text('zoom_client_id', old('zoom_client_id', data_get($setting??[], 'zoom_client_id','')), ['class'=>VC::FM_CT,'placeholder'=>__('Enter Zoom Client ID')])); ?>

                                            <?php $__errorArgs = ['zoom_client_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                                <span class="invalid-feedback <?php echo e(VC::DBL); ?>"><?php echo e($message ?? __('No message available')); ?></span>
                                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                        </div>
                                        <div class="<?php echo e(VC::FM_G); ?> <?php echo e(VC::CM6); ?>">
                                            <label class="<?php echo e(VC::FM_LB); ?>"><?php echo e(__('Zoom Client Secret Key')); ?></label>
                                            <?php echo e(Form::password('zoom_client_secret', ['class'=>VC::FM_CT,'placeholder'=>__('Enter Zoom Client Secret Key'),'autocomplete'=>'off','spellcheck'=>'false'])); ?>

                                            <?php $__errorArgs = ['zoom_client_secret'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                                <span class="invalid-feedback <?php echo e(VC::DBL); ?>"><?php echo e($message ?? __('No message available')); ?></span>
                                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer <?php echo e(VC::DFL); ?> <?php echo e(VC::JCE); ?>">
                                    <div class="<?php echo e(VC::FM_G); ?>">
                                        <input class="<?php echo e(VC::BT_PR_PRM10); ?>" type="submit" value="<?php echo e(__('Save Changes')); ?>">
                                    </div>
                                </div>
                            <?php echo e(Form::close()); ?>

                        </div>
                        <?php
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
                        ?>
                        <div id="slack-settings" class="card">
                            <div class="card-header">
                                <h5><?php echo e(__('Slack Settings')); ?></h5>
                                <small class="text-muted"><?php echo e(__('Edit your Slack settings')); ?></small>
                            </div>
                            <?php echo Form::open(['route'=>$slackSettingsRouteArray,'id'=>$slackSettingsFormId,'method'=>'post','class'=>'d-contents','data-url'=>$slackSettingsUrl,'data-guard-msg'=>$slackSettingsGuardMsg]); ?>

                                <?php $__env->startPush(StacksConstants::ADM_SCR_PG); ?>
                                    <script defer src="<?php echo e(asset('assets/js/routes/settings/companies/slack.js')); ?>"></script>
                                <?php $__env->stopPush(); ?>
                                <?php echo csrf_field(); ?>
                                <div class="card-body">
                                    <div class="<?php echo e(VC::FM_G); ?> <?php echo e(VC::C12); ?>">
                                        <label class="<?php echo e(VC::FM_LB); ?>"><?php echo e(__('Slack Webhook URL')); ?></label>
                                        <?php echo e(Form::url('slack_webhook', old('slack_webhook', data_get($setting??[], 'slack_webhook','')), ['class'=>VC::FM_CT.' w-100','placeholder'=>__('Enter Slack Webhook URL'),'required'=>true])); ?>

                                        <?php $__errorArgs = ['slack_webhook'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                            <span class="invalid-feedback <?php echo e(VC::DBL); ?>"><?php echo e($message ?? __('No message available')); ?></span>
                                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                    </div>
                                    <div class="<?php echo e(VC::C12); ?> mt-5 mb-2">
                                        <h5 class="small-title"><?php echo e(__('Module Settings')); ?></h5>
                                    </div>
                                    <div class="<?php echo e(VC::RW); ?>">
                                        <?php $__currentLoopData = Utility::isFilled($groups) && is_array($groups)?array_chunk($groups,2):[]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $chunk): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <div class="<?php echo e(VC::CM3); ?>">
                                                <ul class="<?php echo e(VC::LGRP); ?>">
                                                    <?php $__currentLoopData = ($chunk[0]??[]); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <?php
                                                            $n=data_get($item,'name');
                                                            $checked=old($n, data_get($setting??[], $n,'0'))=='1';
                                                        ?>
                                                        <li class="<?php echo e(VC::LGI); ?>">
                                                            <div class="form-switch form-switch-right">
                                                                <span><?php echo e(data_get($item,'label',__('No label available'))); ?></span>
                                                                <?php echo e(Form::hidden($n,'0')); ?>

                                                                <?php echo e(Form::checkbox($n,'1',$checked,['class'=>'form-check-input','id'=>$n])); ?>

                                                                <label class="form-check-label" for="<?php echo e($n); ?>"></label>
                                                            </div>
                                                        </li>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                </ul>
                                            </div>
                                            <div class="<?php echo e(VC::CM3); ?>">
                                                <ul class="<?php echo e(VC::LGRP); ?>">
                                                    <?php $__currentLoopData = ($chunk[1]??[]); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <?php
                                                            $n=data_get($item,'name');
                                                            $checked=old($n, data_get($setting??[], $n,'0'))=='1';
                                                        ?>
                                                        <li class="<?php echo e(VC::LGI); ?>">
                                                            <div class="form-switch form-switch-right">
                                                                <span><?php echo e(data_get($item,'label',__('No label available'))); ?></span>
                                                                <?php echo e(Form::hidden($n,'0')); ?>

                                                                <?php echo e(Form::checkbox($n,'1',$checked,['class'=>'form-check-input','id'=>$n])); ?>

                                                                <label class="form-check-label" for="<?php echo e($n); ?>"></label>
                                                            </div>
                                                        </li>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                </ul>
                                            </div>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </div>
                                </div>
                                <div class="card-footer <?php echo e(VC::DFL); ?> <?php echo e(VC::JCE); ?>">
                                    <div class="<?php echo e(VC::FM_G); ?>">
                                        <input class="<?php echo e(VC::BT_PR_PRM10); ?>" type="submit" value="<?php echo e(__('Save Changes')); ?>">
                                    </div>
                                </div>
                            <?php echo e(Form::close()); ?>

                        </div>
                        <?php
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
                        ?>
                        <div id="telegram-settings" class="card">
                            <div class="card-header">
                                <h5><?php echo e(__('Telegram Settings')); ?></h5>
                                <small class="text-muted"><?php echo e(__('Edit your Telegram settings')); ?></small>
                            </div>
                            <?php echo Form::open(['route'=>$telegramSettingsRouteArray,'id'=>$telegramSettingsFormId,'method'=>'post','class'=>'d-contents','data-url'=>$telegramSettingsUrl,'data-guard-msg'=>$telegramSettingsGuardMsg]); ?>

                                <?php $__env->startPush(StacksConstants::ADM_SCR_PG); ?>
                                    <script defer src="<?php echo e(asset('assets/js/routes/settings/companies/telegram.js')); ?>"></script>
                                <?php $__env->stopPush(); ?>
                                <?php echo csrf_field(); ?>
                                <div class="card-body">
                                    <div class="<?php echo e(VC::RW); ?>">
                                        <div class="<?php echo e(VC::FM_G); ?> <?php echo e(VC::CM6); ?>">
                                            <label class="<?php echo e(VC::FM_LB); ?>"><?php echo e(__('Telegram AccessToken')); ?></label>
                                            <?php echo e(Form::text('telegram_accesstoken', old('telegram_accesstoken', data_get($setting??[], 'telegram_accesstoken','')), ['class'=>VC::FM_CT,'placeholder'=>__('Enter Telegram AccessToken'),'autocomplete'=>'off','spellcheck'=>'false'])); ?>

                                            <?php $__errorArgs = ['telegram_accesstoken'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                                <span class="invalid-feedback <?php echo e(VC::DBL); ?>"><?php echo e($message ?? __('No message available')); ?></span>
                                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                        </div>
                                        <div class="<?php echo e(VC::FM_G); ?> <?php echo e(VC::CM6); ?>">
                                            <label class="<?php echo e(VC::FM_LB); ?>"><?php echo e(__('Telegram ChatID')); ?></label>
                                            <?php echo e(Form::text('telegram_chatid', old('telegram_chatid', data_get($setting??[], 'telegram_chatid','')), ['class'=>VC::FM_CT,'placeholder'=>__('Enter Telegram ChatID')])); ?>

                                            <?php $__errorArgs = ['telegram_chatid'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                                <span class="invalid-feedback <?php echo e(VC::DBL); ?>"><?php echo e($message ?? __('No message available')); ?></span>
                                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                        </div>
                                    </div>
                                    <div class="<?php echo e(VC::C12); ?> mt-5 mb-2">
                                        <h5 class="small-title"><?php echo e(__('Module Settings')); ?></h5>
                                    </div>
                                    <div class="<?php echo e(VC::RW); ?>">
                                        <?php $__currentLoopData = Utility::isFilled($groups) && is_array($groups) ?array_chunk($groups,2):[]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $chunk): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <div class="<?php echo e(VC::CM3); ?>">
                                                <ul class="<?php echo e(VC::LGRP); ?>">
                                                    <?php $__currentLoopData = ($chunk[0]??[]); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <?php
                                                            $n=data_get($item,'name');
                                                            $checked=old($n, data_get($setting??[], $n,'0'))=='1';
                                                        ?>
                                                        <li class="<?php echo e(VC::LGI); ?>">
                                                            <div class="form-switch form-switch-right">
                                                                <span><?php echo e(data_get($item,'label',__('No label available'))); ?></span>
                                                                <?php echo e(Form::hidden($n,'0')); ?>

                                                                <?php echo e(Form::checkbox($n,'1',$checked,['class'=>'form-check-input','id'=>$n])); ?>

                                                                <label class="form-check-label" for="<?php echo e($n); ?>"></label>
                                                            </div>
                                                        </li>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                </ul>
                                            </div>
                                            <div class="<?php echo e(VC::CM3); ?>">
                                                <ul class="<?php echo e(VC::LGRP); ?>">
                                                    <?php $__currentLoopData = ($chunk[1]??[]); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <?php
                                                            $n=data_get($item,'name');
                                                            $checked=old($n, data_get($setting??[], $n,'0'))=='1';
                                                        ?>
                                                        <li class="<?php echo e(VC::LGI); ?>">
                                                            <div class="form-switch form-switch-right">
                                                                <span><?php echo e(data_get($item,'label',__('No label available'))); ?></span>
                                                                <?php echo e(Form::hidden($n,'0')); ?>

                                                                <?php echo e(Form::checkbox($n,'1',$checked,['class'=>'form-check-input','id'=>$n])); ?>

                                                                <label class="form-check-label" for="<?php echo e($n); ?>"></label>
                                                            </div>
                                                        </li>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                </ul>
                                            </div>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </div>
                                </div>
                                <div class="card-footer <?php echo e(VC::DFL); ?> <?php echo e(VC::JCE); ?>">
                                    <div class="<?php echo e(VC::FM_G); ?>">
                                        <input class="<?php echo e(VC::BT_PR_PRM10); ?>" type="submit" value="<?php echo e(__('Save Changes')); ?>">
                                    </div>
                                </div>
                            <?php echo e(Form::close()); ?>

                        </div>
                        <?php
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
                        ?>
                        <div id="twilio-settings" class="card">
                            <div class="card-header">
                                <h5><?php echo e(__('Twilio Settings')); ?></h5>
                                <small class="text-muted"><?php echo e(__('Edit your Twilio settings')); ?></small>
                            </div>
                            <?php echo Form::model($setting??[],['route'=>$twilioSettingRouteArray,'method'=>'post','id'=>$twilioSettingFormId,'class'=>'d-contents','data-url'=>$twilioSettingUrl,'data-guard-msg'=>$twilioSettingGuardMsg]); ?>

                                <?php $__env->startPush(StacksConstants::ADM_SCR_PG); ?>
                                    <script defer src="<?php echo e(asset('assets/js/routes/settings/companies/twilio.js')); ?>"></script>
                                <?php $__env->stopPush(); ?>
                                <?php echo csrf_field(); ?>
                                <div class="card-body">
                                    <div class="<?php echo e(VC::RW); ?>">
                                        <div class="<?php echo e(VC::FM_G); ?> <?php echo e(VC::CM4); ?>">
                                            <label class="<?php echo e(VC::FM_LB); ?>"><?php echo e(__('Twilio SID')); ?></label>
                                            <?php echo e(Form::text('twilio_sid', old('twilio_sid', data_get($setting??[], 'twilio_sid','')), ['class'=>VC::FM_CT.' w-100','placeholder'=>__('Enter Twilio SID'),'required'=>true])); ?>

                                            <?php $__errorArgs = ['twilio_sid'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                                <span class="invalid-feedback <?php echo e(VC::DBL); ?>"><?php echo e($message ?? __('No message available')); ?></span>
                                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                        </div>
                                        <div class="<?php echo e(VC::FM_G); ?> <?php echo e(VC::CM4); ?>">
                                            <label class="<?php echo e(VC::FM_LB); ?>"><?php echo e(__('Twilio Token')); ?></label>
                                            <?php echo e(Form::password('twilio_token', ['class'=>VC::FM_CT.' w-100','placeholder'=>__('Enter Twilio Token'),'required'=>true,'autocomplete'=>'off','spellcheck'=>'false'])); ?>

                                            <?php $__errorArgs = ['twilio_token'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                                <span class="invalid-feedback <?php echo e(VC::DBL); ?>"><?php echo e($message ?? __('No message available')); ?></span>
                                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                        </div>
                                        <div class="<?php echo e(VC::FM_G); ?> <?php echo e(VC::CM4); ?>">
                                            <label class="<?php echo e(VC::FM_LB); ?>"><?php echo e(__('Twilio From')); ?></label>
                                            <?php echo e(Form::text('twilio_from', old('twilio_from', data_get($setting??[], 'twilio_from','')), ['class'=>VC::FM_CT.' w-100','placeholder'=>__('Enter Twilio From'),'required'=>true])); ?>

                                            <?php $__errorArgs = ['twilio_from'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                                <span class="invalid-feedback <?php echo e(VC::DBL); ?>"><?php echo e($message ?? __('No message available')); ?></span>
                                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                        </div>
                                        <div class="<?php echo e(VC::C12); ?> mt-4 mb-2">
                                            <h5 class="small-title"><?php echo e(__('Module Settings')); ?></h5>
                                        </div>
                                        <?php $__currentLoopData = $groups; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cols): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <?php $__currentLoopData = $cols; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i=>$item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <?php if($i===0): ?>
                                                    <div class="<?php echo e(VC::CM4); ?> <?php echo e(VC::MB1); ?>">
                                                        <ul class="<?php echo e(VC::LGRP); ?>">
                                                <?php endif; ?>
                                                            <?php
                                                                $n=data_get($item,'name');
                                                                $checked=old($n, data_get($setting??[], $n,'0'))=='1';
                                                            ?>
                                                            <li class="<?php echo e(VC::LGI); ?>">
                                                                <div class="form-switch form-switch-right">
                                                                    <span><?php echo e(data_get($item,'label',__('No label available'))); ?></span>
                                                                    <?php echo e(Form::hidden($n,'0')); ?>

                                                                    <?php echo e(Form::checkbox($n,'1',$checked,['class'=>'form-check-input','id'=>$n])); ?>

                                                                    <label class="form-check-label" for="<?php echo e($n); ?>"></label>
                                                                </div>
                                                            </li>
                                                <?php if($i===1||count($cols)===1): ?>
                                                        </ul>
                                                    </div>
                                                <?php endif; ?>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </div>
                                </div>
                                <div class="card-footer <?php echo e(VC::DFL); ?> <?php echo e(VC::JCE); ?>">
                                    <div class="form-group">
                                        <input class="<?php echo e(VC::BT_PR_PRM10); ?>" type="submit" value="<?php echo e(__('Save Changes')); ?>">
                                    </div>
                                </div>
                            <?php echo e(Form::close()); ?>

                        </div>
                        <div id="email-notification-settings" class="card">
                            <div class="col-md-12">
                                <div class="card-header">
                                    <h5><?php echo e(__('Email Notification Settings')); ?></h5>
                                    <small class="text-muted"><?php echo e(__('Edit email notification settings')); ?></small>
                                </div>
                                <?php
                                    $emailStatusLanguageBaseRoute=VW::EMLS.'.status.language';
                                    $emailStatusLanguageKebabRoute=Str::kebab($emailStatusLanguageBaseRoute);
                                    $emailStatusLanguageResolvedName=Route::has($emailStatusLanguageBaseRoute)?$emailStatusLanguageBaseRoute:(Route::has($emailStatusLanguageKebabRoute)?$emailStatusLanguageKebabRoute:null);
                                    $emailStatusLanguageRouteArray=$emailStatusLanguageResolvedName?[$emailStatusLanguageResolvedName]:['#'];
                                    $emailStatusLanguageUrl=$emailStatusLanguageResolvedName?route($emailStatusLanguageResolvedName):'#';
                                    $langValue=isset($lang)&&is_string($lang)?$lang:Utility::fetchUserLang();
                                    $emailStatusLanguageGuardMsg=Utility::fetchLinkMessage($langValue, VW::EMLS, 'email_status_language_route_unavailable')??__('Email status language route is unavailable. Please contact technical support or your domain administrator.');
                                    $emailStatusLanguageFormId='email-status-language-form';
                                ?>
                                <?php echo Form::model($setting??[],['route'=>$emailStatusLanguageRouteArray,'method'=>'post','id'=>$emailStatusLanguageFormId,'data-url'=>$emailStatusLanguageUrl,'data-guard-msg'=>$emailStatusLanguageGuardMsg]); ?>

                                    <?php $__env->startPush(StacksConstants::ADM_SCR_PG); ?>
                                        <script defer src="<?php echo e(asset('assets/js/routes/settings/companies/emailNotification.js')); ?>"></script>
                                    <?php $__env->stopPush(); ?>
                                    <?php echo csrf_field(); ?>
                                    <div class="card-body">
                                        <div class="<?php echo e(VC::RW); ?>">
                                            <?php
                                                $hasTemplates= Utility::isFilled($emailTemplates);
                                            ?>
                                            <?php if($hasTemplates): ?>
                                                <?php $__currentLoopData = $emailTemplates; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $emailTemplate): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <?php
                                                        $tplId=data_get($emailTemplate,'template.id',data_get($emailTemplate,'id'));
                                                        $tplKey=$tplId??('unknown_'.$loop->index);
                                                        $tplName=data_get($emailTemplate,'name')?:__('No template name available');
                                                        $isActive=(bool) data_get($emailTemplate,'template.is_active',0);
                                                        $checkboxId='email_template_'.$tplKey;
                                                        $itemUrl=$emailStatusLanguageResolvedName&&$tplId?route($emailStatusLanguageResolvedName,[$tplId]):'#';
                                                        $itemGuardMsg=Utility::fetchLinkMessage($langValue, VW::EMLS, 'email_template_status_language_route_unavailable')??__('Email template status language route is unavailable. Please contact technical support or your domain administrator.');
                                                    ?>
                                                    <div class="col-lg-4 col-md-6 col-sm-6 <?php echo e(VC::FM_G); ?>">
                                                        <div class="<?php echo e(VC::LGRP); ?>">
                                                            <div class="<?php echo e(VC::LGI); ?> form-switch form-switch-right">
                                                                <label class="<?php echo e(VC::FM_LB); ?>" style="margin-left:5%;"><?php echo e($tplName); ?></label>
                                                                <?php echo e(Form::hidden("templates[$tplKey]",0)); ?>

                                                                <input class="form-check-input email-template-toggle" name="templates[<?php echo e($tplKey); ?>]" id="<?php echo e($checkboxId); ?>" type="checkbox" value="1" <?php if($isActive): echo 'checked'; endif; ?> data-url="<?php echo e($itemUrl); ?>" data-guard-msg="<?php echo e($itemGuardMsg); ?>" />
                                                                <label class="form-check-label" for="<?php echo e($checkboxId); ?>"></label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            <?php else: ?>
                                                <div class="alert alert-warning"><?php echo e(__('No email templates found.')); ?></div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="card-footer text-end">
                                        <div class="<?php echo e(VC::FM_G); ?>">
                                            <input class="<?php echo e(VC::BT_PR_PRM10); ?>" type="submit" value="<?php echo e(__('Save Changes')); ?>">
                                        </div>
                                    </div>
                                <?php echo e(Form::close()); ?>

                            </div>
                        </div>
                        <?php $__env->startPush(StacksConstants::ADM_SCR_PG); ?>
                            <script defer src="<?php echo e(asset('assets/js/routes/settings/companies/emailSettings.js')); ?>"></script>
                        <?php $__env->stopPush(); ?>
                    <?php else: ?>
                        <div class="alert alert-warning">
                            <?php echo e(__("No core settings available. Make immediate contact with your system administrator or available technical support.")); ?>

                        </div>
                    <?php endif; ?>
                    <div id="offer-letter-settings" class="<?php echo e(VC::CD); ?>">
                        <div class="col-md-12">
                            <div class="card-header <?php echo e(VC::DFL_JCB); ?>">
                                <h5><?php echo e(__('Offer Letter Settings')); ?></h5>
                                <div class="<?php echo e(VC::DFL.' '.VC::JCE); ?> drp-languages">
                                    <ul class="list-unstyled <?php echo e(VC::MB0); ?> m-2">
                                        <li class="<?php echo e(VC::LNG_DD_IT); ?>" style="margin-top:-7px;">
                                            <a class="<?php echo e(VC::DRP_NO_ARROW); ?>" data-bs-toggle="dropdown" href="#" role="button" aria-haspopup="false" aria-expanded="false" id="dropdownLanguage">
                                                <span class="drp-text hide-mob text-primary me-2"><?php echo e(ucfirst(data_get($offerlangName??null,'full_name',__('No language available')))); ?></span>
                                                <i class="ti ti-chevron-down drp-arrow nocolor"></i>
                                            </a>
                                            <div class="<?php echo e(VC::DRP_MN_DSH_END); ?>" aria-labelledby="dropdownLanguage">
                                                <?php
                                                    $offerLetterLangRouteBase=VW::SET.'.offer_letter.language';
                                                    $offerLetterLangRouteKebab=Str::kebab($offerLetterLangRouteBase);
                                                    $offerLetterLangRouteName=Route::has($offerLetterLangRouteBase)?$offerLetterLangRouteBase:(Route::has($offerLetterLangRouteKebab)?$offerLetterLangRouteKebab:null);
                                                    $langValue=isset($lang)&&is_string($lang)?$lang:Utility::fetchUserLang();
                                                    $offerLetterLangGuardMsg=Utility::fetchLinkMessage($langValue, VW::SET, 'offer_letter_language_route_unavailable')??__('Offer letter language route is unavailable. Please contact technical support or your domain administrator.');
                                                ?>
                                                <?php if(is_iterable($currentLang??[])): ?>
                                                    <?php $__currentLoopData = $currentLang; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $code=>$offerlang): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <?php
                                                            $offerLetterLangParams=['noclangs'=>$noclang??null,'explangs'=>$explang??null,'offerlang'=>$code,'joininglangs'=>$joininglang??null];
                                                            $offerLetterLangUrl=$offerLetterLangRouteName?route($offerLetterLangRouteName,$offerLetterLangParams):'#';
                                                        ?>
                                                        <a id="offer-letter-language-link-<?php echo e($code); ?>" href="<?php echo e($offerLetterLangUrl); ?>" data-url="<?php echo e($offerLetterLangUrl); ?>" data-guard-msg="<?php echo e($offerLetterLangGuardMsg); ?>" class="dropdown-item ms-1 offer-letter-language-link <?php echo e(((isset($offerlang)&&$offerlang===$code)?'text-primary':'')); ?>"><?php echo e(ucfirst($offerlang)); ?></a>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                <?php endif; ?>
                                                <?php $__env->startPush(StacksConstants::ADM_SCR_PG); ?>
                                                    <script defer>
                                                        (function(){var links=document.querySelectorAll('.offer-letter-language-link');if(!links||links.length===0)return;links.forEach(function(l){if(l.getAttribute('data-listener-active')==='true')return;l.setAttribute('data-listener-active','true');l.addEventListener('click',function(e){try{var url=l.getAttribute('data-url')||'#';if(url!=='#')return;e.preventDefault();var msg=l.getAttribute('data-guard-msg')||'';var hasBootstrap=!!(document.querySelector('link[href*="bootstrap"]')&&window.bootstrap);var container=document.getElementById('toast-container');if(!container){container=document.createElement('div');container.id='toast-container';document.body.appendChild(container);}if(hasBootstrap){var toast=document.createElement('div');toast.className='toast';toast.setAttribute('role','alert');toast.setAttribute('aria-live','assertive');toast.setAttribute('aria-atomic','true');var body=document.createElement('div');body.className='toast-body';body.textContent=msg;toast.appendChild(body);container.appendChild(toast);bootstrap.Toast.getOrCreateInstance(toast).show();}else{alert(msg);}l.setAttribute('data-failed-route','true');}catch(err){}});});})();
                                                    </script>
                                                <?php $__env->stopPush(); ?>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <div class="card-body">
                                <h5 class="font-weight-bold pb-3"><?php echo e(__('Placeholders')); ?></h5>
                                <div class="col-lg-12 col-md-12 col-sm-12">
                                    <div class="<?php echo e(VC::CD); ?>">
                                        <div class="card-header card-body">
                                            <div class="<?php echo e(VC::RW); ?> <?php echo e(VC::TXS); ?>">
                                                <div class="<?php echo e(VC::RW); ?>">
                                                    <p class="col-4"><?php echo e(__('Applicant Name')); ?> : <span class="pull-end text-primary">{applicant_name}</span></p>
                                                    <p class="col-4"><?php echo e(__('Company Name')); ?> : <span class="pull-right text-primary">{app_name}</span></p>
                                                    <p class="col-4"><?php echo e(__('Job title')); ?> : <span class="pull-right text-primary">{job_title}</span></p>
                                                    <p class="col-4"><?php echo e(__('Job type')); ?> : <span class="pull-right text-primary">{job_type}</span></p>
                                                    <p class="col-4"><?php echo e(__('Proposed Start Date')); ?> : <span class="pull-right text-primary">{start_date}</span></p>
                                                    <p class="col-4"><?php echo e(__('Working Location')); ?> : <span class="pull-right text-primary">{workplace_location}</span></p>
                                                    <p class="col-4"><?php echo e(__('Days Of Week')); ?> : <span class="pull-right text-primary">{days_of_week}</span></p>
                                                    <p class="col-4"><?php echo e(__('Salary')); ?> : <span class="pull-right text-primary">{salary}</span></p>
                                                    <p class="col-4"><?php echo e(__('Salary Type')); ?> : <span class="pull-right text-primary">{salary_type}</span></p>
                                                    <p class="col-4"><?php echo e(__('Salary Duration')); ?> : <span class="pull-end text-primary">{salary_duration}</span></p>
                                                    <p class="col-4"><?php echo e(__('Offer Expiration Date')); ?> : <span class="pull-right text-primary">{offer_expiration_date}</span></p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body table-border-style">
                                <?php
                                    $offerLetterUpdateBaseName='offer_letter.update';
                                    $offerLetterUpdateKebabName=Str::kebab($offerLetterUpdateBaseName);
                                    $offerLetterUpdateResolvedName=Route::has($offerLetterUpdateBaseName)?$offerLetterUpdateBaseName:(Route::has($offerLetterUpdateKebabName)?$offerLetterUpdateKebabName:null);
                                    $offerLangKey=(string)($offerlang??'default');
                                    $offerLetterUpdateRouteArray=$offerLetterUpdateResolvedName?[$offerLetterUpdateResolvedName,$offerLangKey]:['#'];
                                    $offerLetterUpdateUrl=$offerLetterUpdateResolvedName?route($offerLetterUpdateResolvedName,$offerLangKey):'#';
                                    $offerLetterUpdateGuardMsg=Utility::fetchLinkMessage($langValue??(isset($lang)&&is_string($lang)?$lang:Utility::fetchUserLang()), VW::SET, 'offer_letter_update_route_unavailable')??__('Offer letter update route is unavailable. Please contact technical support or your domain administrator.');
                                    $offerLetterUpdateFormId='offer-letter-update-form-'.$offerLangKey;
                                ?>
                                <?php echo Form::open(['route'=>$offerLetterUpdateRouteArray,'method'=>'post','id'=>$offerLetterUpdateFormId,'data-url'=>$offerLetterUpdateUrl,'data-guard-msg'=>$offerLetterUpdateGuardMsg]); ?>

                                    <?php echo csrf_field(); ?>
                                    <?php $__env->startPush(StacksConstants::ADM_SCR_PG); ?>
                                        <script defer>
                                            (function(){var form=document.getElementById('<?php echo e($offerLetterUpdateFormId); ?>');if(!form||form.getAttribute('data-listener-active')==='true')return;form.setAttribute('data-listener-active','true');form.addEventListener('submit',function(e){try{var url=form.getAttribute('data-url')||'#';var action=form.getAttribute('action')||'#';if(url!=='#'&&action!=='#')return;e.preventDefault();var msg=form.getAttribute('data-guard-msg')||'';var hasBootstrap=!!(document.querySelector('link[href*="bootstrap"]')&&window.bootstrap);var container=document.getElementById('toast-container');if(!container){container=document.createElement('div');container.id='toast-container';document.body.appendChild(container);}if(hasBootstrap){var toast=document.createElement('div');toast.className='toast';toast.setAttribute('role','alert');toast.setAttribute('aria-live','assertive');toast.setAttribute('aria-atomic','true');var body=document.createElement('div');body.className='toast-body';body.textContent=msg;toast.appendChild(body);container.appendChild(toast);bootstrap.Toast.getOrCreateInstance(toast).show();}else{alert(msg);}form.setAttribute('data-failed-route','true');}catch(err){}});})();
                                        </script>
                                    <?php $__env->stopPush(); ?>
                                    <div class="<?php echo e(VC::FM_G); ?> <?php echo e(VC::C12); ?>">
                                        <?php echo e(Form::label('content', __('Format'), ['class'=>VC::FM_LB.' text-dark'])); ?>

                                        <textarea name="content" class="summernote-simple0 summernote-simple"><?php echo data_get($currOfferletterLang??null,'content',''); ?></textarea>
                                    </div>
                                <?php echo e(Form::close()); ?>

                            </div>
                        </div>
                    </div>
                    <div id="joining-letter-settings" class="<?php echo e(VC::CD); ?>">
                        <div class="col-md-12">
                            <div class="card-header <?php echo e(VC::DFL_JCB); ?>">
                                <h5><?php echo e(__('Joining Letter Settings')); ?></h5>
                                <div class="<?php echo e(VC::DFL.' '.VC::JCE); ?> drp-languages">
                                    <ul class="list-unstyled <?php echo e(VC::MB0); ?> m-2">
                                        <li class="<?php echo e(VC::LNG_DD_IT); ?>" style="margin-top:-7px;">
                                            <a class="<?php echo e(VC::DRP_NO_ARROW); ?>" data-bs-toggle="dropdown" href="#" role="button" aria-haspopup="false" aria-expanded="false" id="dropdownLanguage1">
                                                <span class="drp-text hide-mob text-primary me-2"><?php echo e(ucfirst(data_get($joininglangName??null,'full_name',__('No language available')))); ?></span>
                                                <i class="ti ti-chevron-down drp-arrow nocolor"></i>
                                            </a>
                                            <div class="<?php echo e(VC::DRP_MN_DSH_END); ?>" aria-labelledby="dropdownLanguage1">
                                                <?php
                                                    $joiningLetterLangBaseName=VW::SET.'.joining_letter.language';
                                                    $joiningLetterLangKebabName=Str::kebab($joiningLetterLangBaseName);
                                                    $joiningLetterLangRouteName=Route::has($joiningLetterLangBaseName)?$joiningLetterLangBaseName:(Route::has($joiningLetterLangKebabName)?$joiningLetterLangKebabName:null);
                                                    $langValue=isset($lang)&&is_string($lang)?$lang:Utility::fetchUserLang();
                                                    $joiningLetterLangGuardMsg=Utility::fetchLinkMessage($langValue, VW::SET, 'joining_letter_language_route_unavailable')??__('Joining letter language route is unavailable. Please contact technical support or your domain administrator.');
                                                ?>
                                                <?php if(is_iterable($currentLang??[])): ?>
                                                    <?php $__currentLoopData = $currentLang; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $code=>$joininglang): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <?php
                                                            $joiningLetterParams=['noclangs'=>$noclang??null,'explangs'=>$explang??null,'offerlangs'=>$joininglang??null,'joininglangs'=>$code];
                                                            $joiningLetterLangUrl=$joiningLetterLangRouteName?route($joiningLetterLangRouteName,$joiningLetterParams):'#';
                                                        ?>
                                                        <a id="joining-letter-language-link-<?php echo e($code); ?>" href="<?php echo e($joiningLetterLangUrl); ?>" data-url="<?php echo e($joiningLetterLangUrl); ?>" data-guard-msg="<?php echo e($joiningLetterLangGuardMsg); ?>" class="dropdown-item joining-letter-language-link <?php echo e(($joininglang==$code)?'text-primary':''); ?>"><?php echo e((is_string($joininglang)&&$joininglang!=='')?ucfirst($joininglang):__('No language label available')); ?></a>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                <?php else: ?>
                                                    <span class="text-muted"><?php echo e(__('No languages found for Joining letters')); ?></span>
                                                <?php endif; ?>
                                                <?php $__env->startPush(StacksConstants::ADM_SCR_PG); ?>
                                                    <script defer src="<?php echo e(asset('assets/js/routes/settings/companies/joiningLetter.js')); ?>"></script>
                                                <?php $__env->stopPush(); ?>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <div class="card-body">
                                <h5 class="font-weight-bold pb-3"><?php echo e(__('Placeholders')); ?></h5>
                                <div class="col-lg-12 col-md-12 col-sm-12">
                                    <div class="<?php echo e(VC::CD); ?>">
                                        <div class="card-header card-body">
                                            <div class="<?php echo e(VC::RW); ?> <?php echo e(VC::TXS); ?>">
                                                <div class="<?php echo e(VC::RW); ?>">
                                                    <p class="col-4"><?php echo e(__('Applicant Name')); ?> : <span class="pull-end text-primary">{date}</span></p>
                                                    <p class="col-4"><?php echo e(__('Company Name')); ?> : <span class="pull-right text-primary">{app_name}</span></p>
                                                    <p class="col-4"><?php echo e(__('Employee Name')); ?> : <span class="pull-right text-primary">{employee_name}</span></p>
                                                    <p class="col-4"><?php echo e(__('Address')); ?> : <span class="pull-right text-primary">{address}</span></p>
                                                    <p class="col-4"><?php echo e(__('Designation')); ?> : <span class="pull-right text-primary">{designation}</span></p>
                                                    <p class="col-4"><?php echo e(__('Start Date')); ?> : <span class="pull-right text-primary">{start_date}</span></p>
                                                    <p class="col-4"><?php echo e(__('Branch')); ?> : <span class="pull-right text-primary">{branch}</span></p>
                                                    <p class="col-4"><?php echo e(__('Start Time')); ?> : <span class="pull-end text-primary">{start_time}</span></p>
                                                    <p class="col-4"><?php echo e(__('End Time')); ?> : <span class="pull-right text-primary">{end_time}</span></p>
                                                    <p class="col-4"><?php echo e(__('Number of Hours')); ?> : <span class="pull-right text-primary">{total_hours}</span></p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body table-border-style">
                                <?php
                                    $joiningLetterUpdateBaseName='joining_letter.update';
                                    $joiningLetterUpdateKebabName=Str::kebab($joiningLetterUpdateBaseName);
                                    $joiningLetterUpdateResolvedName=Route::has($joiningLetterUpdateBaseName)?$joiningLetterUpdateBaseName:(Route::has($joiningLetterUpdateKebabName)?$joiningLetterUpdateKebabName:null);
                                    $joiningLangKey=(string)($joininglang??'default');
                                    $joiningLetterUpdateRouteArray=$joiningLetterUpdateResolvedName?[$joiningLetterUpdateResolvedName,$joiningLangKey]:['#'];
                                    $joiningLetterUpdateUrl=$joiningLetterUpdateResolvedName?route($joiningLetterUpdateResolvedName,$joiningLangKey):'#';
                                    $joiningLetterUpdateGuardMsg=Utility::fetchLinkMessage($langValue, VW::SET, 'joining_letter_update_route_unavailable')??__('Joining letter update route is unavailable. Please contact technical support or your domain administrator.');
                                    $joiningLetterUpdateFormId='joining-letter-update-form-'.$joiningLangKey;
                                ?>
                                <?php echo Form::open(['route'=>$joiningLetterUpdateRouteArray,'method'=>'post','id'=>$joiningLetterUpdateFormId,'data-url'=>$joiningLetterUpdateUrl,'data-guard-msg'=>$joiningLetterUpdateGuardMsg]); ?>

                                    <?php $__env->startPush(StacksConstants::ADM_SCR_PG); ?>
                                        <script defer>
                                            (()=>{const form=document.getElementById('<?php echo e($joiningLetterUpdateFormId); ?>');if(!form||form.getAttribute('data-listener-active')==='true')return;form.setAttribute('data-listener-active','true');form.addEventListener('submit',e=>{try{const url=form.getAttribute('data-url')||'#';const action=form.getAttribute('action')||'#';if(url!=='#'&&action!=='#')return;e.preventDefault();const msg=form.getAttribute('data-guard-msg')||'# ERROR';const hasBootstrap=document.querySelector('link[href*="bootstrap"]')&&window.bootstrap;let container=document.getElementById('toast-container');if(!container){container=document.createElement('div');container.id='toast-container';document.body.appendChild(container);}if(hasBootstrap){const toast=document.createElement('div');toast.className='toast';toast.setAttribute('role','alert');toast.setAttribute('aria-live','assertive');toast.setAttribute('aria-atomic','true');const body=document.createElement('div');body.className='toast-body';body.textContent=msg;toast.appendChild(body);container.appendChild(toast);bootstrap.Toast.getOrCreateInstance(toast).show();}else{alert(msg);}form.setAttribute('data-failed-route','true');}catch(err){}});})();
                                        </script>
                                    <?php $__env->stopPush(); ?>
                                    <div class="<?php echo e(VC::FM_G); ?> <?php echo e(VC::C12); ?>">
                                        <?php echo e(Form::label('content', __('Format'), ['class'=>VC::FM_LB.' text-dark'])); ?>

                                        <textarea name="content" class="summernote-simple1 summernote-simple"><?php echo data_get($currjoiningletterLang??null,'content',''); ?></textarea>
                                    </div>
                                <?php echo e(Form::close()); ?>

                            </div>
                        </div>
                    </div>
                    <div id="experience-certificate-settings" class="<?php echo e(VC::CD); ?>">
                        <div class="col-md-12">
                            <div class="card-header <?php echo e(VC::DFL_JCB); ?>">
                                <h5><?php echo e(__('Experience Certificate Settings')); ?></h5>
                                <div class="<?php echo e(VC::DFL.' '.VC::JCE); ?> drp-languages">
                                    <ul class="list-unstyled <?php echo e(VC::MB0); ?> m-2">
                                        <li class="<?php echo e(VC::LNG_DD_IT); ?>" style="margin-top:-7px;">
                                            <a class="<?php echo e(VC::DRP_NO_ARROW); ?>" data-bs-toggle="dropdown" href="#" role="button" aria-haspopup="false" aria-expanded="false" id="dropdownLanguage1">
                                                <span class="drp-text hide-mob text-primary me-2"><?php echo e(ucfirst(data_get($explangName??null,'full_name',__('No language available')))); ?></span>
                                                <i class="ti ti-chevron-down drp-arrow nocolor"></i>
                                            </a>
                                            <div class="<?php echo e(VC::DRP_MN_DSH_END); ?>" aria-labelledby="dropdownLanguage1">
                                                <?php
                                                    $experienceCertificateLangBase=VW::SET.'.experience_certificate.language';
                                                    $experienceCertificateLangKebab=Str::kebab($experienceCertificateLangBase);
                                                    $experienceCertificateLangName=Route::has($experienceCertificateLangBase)?$experienceCertificateLangBase:(Route::has($experienceCertificateLangKebab)?$experienceCertificateLangKebab:null);
                                                    $experienceCertificateLangGuard=Utility::fetchLinkMessage($langValue, VW::SET, 'experience_certificate_language_route_unavailable')??__('Experience certificate language route is unavailable. Please contact technical support or your domain administrator.');
                                                ?>
                                                <?php if(is_iterable($currentLang??[])): ?>
                                                    <?php $__currentLoopData = $currentLang; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $code=>$explang): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <?php
                                                            $experienceCertificateParams=['noclangs'=>$noclang??null,'explangs'=>$code,'offerlangs'=>$explang??null,'joininglangs'=>$joininglang??null];
                                                            $experienceCertificateLangUrl=$experienceCertificateLangName?route($experienceCertificateLangName,$experienceCertificateParams):'#';
                                                        ?>
                                                        <a id="experience-certificate-language-link-<?php echo e($code); ?>" href="<?php echo e($experienceCertificateLangUrl); ?>" data-url="<?php echo e($experienceCertificateLangUrl); ?>" data-guard-msg="<?php echo e($experienceCertificateLangGuard); ?>" class="dropdown-item experience-certificate-language-link <?php echo e(($explang==$code)?'text-primary':''); ?>"><?php echo e((is_string($explang)&&$explang!=='')?ucfirst($explang):__('No language label available')); ?></a>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                <?php else: ?>
                                                    <span class="text-muted"><?php echo e(__('No languages found for Experience Certificates')); ?></span>
                                                <?php endif; ?>
                                                <?php $__env->startPush(StacksConstants::ADM_SCR_PG); ?>
                                                    <script defer src="<?php echo e(asset('assets/js/routes/settings/companies/experienceCertificate.js')); ?>"></script>
                                                <?php $__env->stopPush(); ?>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <div class="card-body">
                                <h5 class="font-weight-bold pb-3"><?php echo e(__('Placeholders')); ?></h5>
                                <div class="col-lg-12 col-md-12 col-sm-12">
                                    <div class="<?php echo e(VC::CD); ?>">
                                        <div class="card-header card-body">
                                            <div class="<?php echo e(VC::RW); ?> <?php echo e(VC::TXS); ?>">
                                                <div class="<?php echo e(VC::RW); ?>">
                                                    <p class="col-4"><?php echo e(__('Company Name')); ?> : <span class="pull-right text-primary">{app_name}</span></p>
                                                    <p class="col-4"><?php echo e(__('Employee Name')); ?> : <span class="pull-right text-primary">{employee_name}</span></p>
                                                    <p class="col-4"><?php echo e(__('Date of Issuance')); ?> : <span class="pull-right text-primary">{date}</span></p>
                                                    <p class="col-4"><?php echo e(__('Designation')); ?> : <span class="pull-right text-primary">{designation}</span></p>
                                                    <p class="col-4"><?php echo e(__('Start Date')); ?> : <span class="pull-right text-primary">{start_date}</span></p>
                                                    <p class="col-4"><?php echo e(__('Branch')); ?> : <span class="pull-right text-primary">{branch}</span></p>
                                                    <p class="col-4"><?php echo e(__('Start Time')); ?> : <span class="pull-end text-primary">{start_time}</span></p>
                                                    <p class="col-4"><?php echo e(__('End Time')); ?> : <span class="pull-right text-primary">{end_time}</span></p>
                                                    <p class="col-4"><?php echo e(__('Number of Hours')); ?> : <span class="pull-right text-primary">{total_hours}</span></p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body table-border-style">
                                <?php
                                    $expCertUpdateBaseName='experience_certificate.update';
                                    $expCertUpdateKebabName=Str::kebab($expCertUpdateBaseName);
                                    $expCertUpdateResolvedName=Route::has($expCertUpdateBaseName)?$expCertUpdateBaseName:(Route::has($expCertUpdateKebabName)?$expCertUpdateKebabName:null);
                                    $expLangKey=(string)($explang??'default');
                                    $expCertUpdateRouteArray=$expCertUpdateResolvedName?[$expCertUpdateResolvedName,$expLangKey]:['#'];
                                    $expCertUpdateUrl=$expCertUpdateResolvedName?route($expCertUpdateResolvedName,$expLangKey):'#';
                                    $expCertUpdateGuardMsg=Utility::fetchLinkMessage($langValue, VW::SET, 'experience_certificate_update_route_unavailable')??__('Experience certificate update route is unavailable. Please contact technical support or your domain administrator.');
                                    $expCertUpdateFormId='experience-certificate-update-form-'.$expLangKey;
                                ?>
                                <?php echo Form::open(['route'=>$expCertUpdateRouteArray,'method'=>'post','id'=>$expCertUpdateFormId,'data-url'=>$expCertUpdateUrl,'data-guard-msg'=>$expCertUpdateGuardMsg]); ?>

                                    <?php $__env->startPush(StacksConstants::ADM_SCR_PG); ?>
                                        <script defer>
                                            (()=>{const form=document.getElementById('<?php echo e($expCertUpdateFormId); ?>');if(!form||form.getAttribute('data-listener-active')==='true')return;form.setAttribute('data-listener-active','true');form.addEventListener('submit',e=>{try{const url=form.getAttribute('data-url')||'#';const action=form.getAttribute('action')||'#';if(url!=='#'&&action!=='#')return;e.preventDefault();const msg=form.getAttribute('data-guard-msg')||'# ERROR';const hasBootstrap=document.querySelector('link[href*="bootstrap"]')&&window.bootstrap;let container=document.getElementById('toast-container');if(!container){container=document.createElement('div');container.id='toast-container';document.body.appendChild(container);}if(hasBootstrap){const toast=document.createElement('div');toast.className='toast';toast.setAttribute('role','alert');toast.setAttribute('aria-live','assertive');toast.setAttribute('aria-atomic','true');const body=document.createElement('div');body.className='toast-body';body.textContent=msg;toast.appendChild(body);container.appendChild(toast);bootstrap.Toast.getOrCreateInstance(toast).show();}else{alert(msg);}form.setAttribute('data-failed-route','true');}catch(err){}});})();
                                        </script>
                                    <?php $__env->stopPush(); ?>
                                    <div class="<?php echo e(VC::FM_G); ?> <?php echo e(VC::C12); ?>">
                                        <?php echo e(Form::label('content', __('Format'), ['class'=>VC::FM_LB.' text-dark'])); ?>

                                        <textarea name="content" class="summernote-simple2 summernote-simple"><?php echo data_get($curr_exp_cetificate_Lang??null,'content',''); ?></textarea>
                                    </div>
                                <?php echo e(Form::close()); ?>

                            </div>
                        </div>
                    </div>
                    <div id="noc-settings" class="<?php echo e(VC::CD); ?>">
                        <div class="col-md-12">
                            <div class="card-header <?php echo e(VC::DFL_JCB); ?>">
                                <h5><?php echo e(__('NOC Settings')); ?></h5>
                                <div class="<?php echo e(VC::DFL.' '.VC::JCE); ?> drp-languages">
                                    <ul class="list-unstyled <?php echo e(VC::MB0); ?> m-2">
                                        <li class="<?php echo e(VC::LNG_DD_IT); ?>" style="margin-top:-7px;">
                                            <a class="<?php echo e(VC::DRP_NO_ARROW); ?>" data-bs-toggle="dropdown" href="#" role="button" aria-haspopup="false" aria-expanded="false" id="dropdownLanguage1">
                                                <span class="drp-text hide-mob text-primary me-2"><?php echo e(ucfirst(data_get($noclangName??null,'full_name',__('No language available')))); ?></span>
                                                <i class="ti ti-chevron-down drp-arrow nocolor"></i>
                                            </a>
                                            <div class="<?php echo e(VC::DRP_MN_DSH_END); ?>" aria-labelledby="dropdownLanguage1">
                                                <?php
                                                    $nocLanguageBaseName=VW::SET.'.noc.language';
                                                    $nocLanguageKebabName=Str::kebab($nocLanguageBaseName);
                                                    $nocLanguageRouteName=Route::has($nocLanguageBaseName)?$nocLanguageBaseName:(Route::has($nocLanguageKebabName)?$nocLanguageKebabName:null);
                                                    $nocLanguageGuardMsg=Utility::fetchLinkMessage($langValue, VW::SET, 'noc_language_route_unavailable')??__('NOC language route is unavailable. Please contact technical support or your domain administrator.');
                                                ?>
                                                <?php if(is_iterable($currentLang??[])): ?>
                                                    <?php $__currentLoopData = $currentLang; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $code=>$noclangs): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <?php
                                                            $nocLanguageParams=['noclangs'=>$code,'explangs'=>$explang??null,'offerlangs'=>$offerlang??null,'joininglangs'=>$joininglang??null];
                                                            $nocLanguageUrl=$nocLanguageRouteName?route($nocLanguageRouteName,$nocLanguageParams):'#';
                                                        ?>
                                                        <a id="noc-language-link-<?php echo e($code); ?>" href="<?php echo e($nocLanguageUrl); ?>" data-url="<?php echo e($nocLanguageUrl); ?>" data-guard-msg="<?php echo e($nocLanguageGuardMsg); ?>" class="dropdown-item noc-language-link <?php echo e(($noclangs==$code)?'text-primary':''); ?>"><?php echo e((is_string($noclangs)&&$noclangs!=='')?ucfirst($noclangs):__('No language label available')); ?></a>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                <?php else: ?>
                                                    <span class="text-muted"><?php echo e(__('No languages found for NOC template')); ?></span>
                                                <?php endif; ?>
                                                <?php $__env->startPush(StacksConstants::ADM_SCR_PG); ?>
                                                    <script defer src="<?php echo e(asset('assets/js/routes/settings/companies/noc.js')); ?>"></script>
                                                <?php $__env->stopPush(); ?>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <div class="card-body">
                                <h5 class="font-weight-bold pb-3"><?php echo e(__('Placeholders')); ?></h5>
                                <div class="col-lg-12 col-md-12 col-sm-12">
                                    <div class="<?php echo e(VC::CD); ?>">
                                        <div class="card-header card-body">
                                            <div class="<?php echo e(VC::RW); ?> <?php echo e(VC::TXS); ?>">
                                                <div class="<?php echo e(VC::RW); ?>">
                                                    <p class="col-4"><?php echo e(__('Date')); ?> : <span class="pull-end text-primary">{date}</span></p>
                                                    <p class="col-4"><?php echo e(__('Company Name')); ?> : <span class="pull-right text-primary">{app_name}</span></p>
                                                    <p class="col-4"><?php echo e(__('Employee Name')); ?> : <span class="pull-right text-primary">{employee_name}</span></p>
                                                    <p class="col-4"><?php echo e(__('Designation')); ?> : <span class="pull-right text-primary">{designation}</span></p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body table-border-style">
                                <?php
                                    $nocUpdateBaseName='noc.update';
                                    $nocUpdateKebabName=Str::kebab($nocUpdateBaseName);
                                    $nocUpdateResolvedName=Route::has($nocUpdateBaseName)?$nocUpdateBaseName:(Route::has($nocUpdateKebabName)?$nocUpdateKebabName:null);
                                    $nocLangKey=(string)($noclang??'default');
                                    $nocUpdateRouteArray=$nocUpdateResolvedName?[$nocUpdateResolvedName,$nocLangKey]:['#'];
                                    $nocUpdateUrl=$nocUpdateResolvedName?route($nocUpdateResolvedName,$nocLangKey):'#';
                                    $nocUpdateGuardMsg=Utility::fetchLinkMessage($langValue, VW::SET, 'noc_update_route_unavailable')??__('NOC update route is unavailable. Please contact technical support or your domain administrator.');
                                    $nocUpdateFormId='noc-update-form-'.$nocLangKey;
                                ?>
                                <?php echo Form::open(['route'=>$nocUpdateRouteArray,'method'=>'post','id'=>$nocUpdateFormId,'data-url'=>$nocUpdateUrl,'data-guard-msg'=>$nocUpdateGuardMsg]); ?>

                                    <?php $__env->startPush(StacksConstants::ADM_SCR_PG); ?>
                                        <script defer>
                                            (()=>{const form=document.getElementById('<?php echo e($nocUpdateFormId); ?>');if(!form||form.getAttribute('data-listener-active')==='true')return;form.setAttribute('data-listener-active','true');form.addEventListener('submit',e=>{try{const url=form.getAttribute('data-url')||'#';const action=form.getAttribute('action')||'#';if(url!=='#'&&action!=='#')return;e.preventDefault();const msg=form.getAttribute('data-guard-msg')||'# ERROR';const hasBootstrap=document.querySelector('link[href*="bootstrap"]')&&window.bootstrap;let container=document.getElementById('toast-container');if(!container){container=document.createElement('div');container.id='toast-container';document.body.appendChild(container);}if(hasBootstrap){const toast=document.createElement('div');toast.className='toast';toast.setAttribute('role','alert');toast.setAttribute('aria-live','assertive');toast.setAttribute('aria-atomic','true');const body=document.createElement('div');body.className='toast-body';body.textContent=msg;toast.appendChild(body);container.appendChild(toast);bootstrap.Toast.getOrCreateInstance(toast).show();}else{alert(msg);}form.setAttribute('data-failed-route','true');}catch(err){}});})();
                                        </script>
                                    <?php $__env->stopPush(); ?>
                                    <div class="<?php echo e(VC::FM_G); ?> <?php echo e(VC::C12); ?>">
                                        <?php echo e(Form::label('content', __('Format'), ['class'=>VC::FM_LB.' text-dark'])); ?>

                                        <textarea name="content" class="summernote-simple3 summernote-simple"><?php echo data_get($currnocLang??null,'content',''); ?></textarea>
                                    </div>
                                <?php echo e(Form::close()); ?>

                            </div>
                        </div>
                    </div>
                    <div id="google-calendar" class="card">
                        <div class="col-md-12">
                            <?php
                                $settingsGoogleCalendarBaseRouteName=VW::SET.'.google.calendar';
                                $settingsGoogleCalendarKebabRouteName=Str::kebab($settingsGoogleCalendarBaseRouteName);
                                $settingsGoogleCalendarResolvedRouteName=Route::has($settingsGoogleCalendarBaseRouteName)?$settingsGoogleCalendarBaseRouteName:(Route::has($settingsGoogleCalendarKebabRouteName)?$settingsGoogleCalendarKebabRouteName:null);
                                $settingsGoogleCalendarUrl=$settingsGoogleCalendarResolvedRouteName?route($settingsGoogleCalendarResolvedRouteName):'#';
                                $langValue=isset($lang)&&is_string($lang)?$lang:Utility::fetchUserLang();
                                $settingsGoogleCalendarGuardMsg=Utility::fetchLinkMessage($langValue, VW::SET, 'settings_google_calendar_route_unavailable')??__('Settings Google Calendar route is unavailable. Please contact technical support or your domain administrator.');
                                $settingsGoogleCalendarFormId='settings-google-calendar-form';
                            ?>
                            <?php echo Form::open(['url'=>$settingsGoogleCalendarUrl,'enctype'=>'multipart/form-data','id'=>$settingsGoogleCalendarFormId,'data-url'=>$settingsGoogleCalendarUrl,'data-guard-msg'=>$settingsGoogleCalendarGuardMsg]); ?>

                                <?php $__env->startPush(StacksConstants::ADM_SCR_PG); ?>
                                    <script defer src="<?php echo e(asset('assets/js/routes/settings/companies/calendar.js')); ?>"></script>
                                <?php $__env->stopPush(); ?>
                                <div class="card-header">
                                    <div class="<?php echo e(VC::RW); ?>">
                                        <div class="col-6">
                                            <h5 class="mb-2"><?php echo e(__('Google Calendar Settings')); ?></h5>
                                        </div>
                                        <div class="col switch-width text-end">
                                            <div class="<?php echo e(VC::FM_G); ?> <?php echo e(VC::MB0); ?>">
                                                <div class="<?php echo e(VC::CST_CTL); ?> custom-switch">
                                                    <input type="checkbox" name="google_calendar_enable" id="google_calendar_enable" data-toggle="switchbutton" data-onstyle="primary" <?php echo e((data_get($setting??[], 'google_calendar_enable','')==='on')?'checked':''); ?>>
                                                    <label class="<?php echo e(VC::CST_LB); ?>" for="google_calendar_enable"></label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="<?php echo e(VC::RW); ?>">
                                        <div class="col-lg-6 <?php echo e(VC::CM6); ?> <?php echo e(VC::CS12); ?> <?php echo e(VC::FM_G); ?>">
                                            <?php echo e(Form::label('Google calendar id', __('Google Calendar Id'), ['class'=>'col-form-label'])); ?>

                                            <?php echo e(Form::text('google_clender_id', old('google_clender_id', (string) data_get($setting??[], 'google_clender_id','')), ['class'=>VC::FM_CT,'placeholder'=>__('Google Calendar Id'),'required'=>'required'])); ?>

                                        </div>
                                        <div class="col-lg-6 <?php echo e(VC::CM6); ?> <?php echo e(VC::CS12); ?> <?php echo e(VC::FM_G); ?>">
                                            <?php echo e(Form::label('Google calendar json file', __('Google Calendar json File'), ['class'=>'col-form-label'])); ?>

                                            <input type="file" class="<?php echo e(VC::FM_CT); ?>" name="google_calendar_json_file" id="file" aria-label="<?php echo e(__('Google Calendar json File')); ?>">
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer text-end">
                                    <button class="btn-submit <?php echo e(VC::BT_PRM); ?>" type="submit"><?php echo e(__('Save Changes')); ?></button>
                                </div>
                            <?php echo e(Form::close()); ?>

                        </div>
                    </div>
                    <div id="webhook-settings" class="card">
                        <div class="col-md-12">
                            <div class="card-header">
                                <div class="row">
                                    <div class="col-6">
                                        <h5 class="mb-2"><?php echo e(__('Webhook Settings')); ?></h5>
                                    </div>
                                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('create webhook')): ?>
                                        <div class="col-6 text-end">
                                            <?php
                                                $webhookCreateBaseName=VW::WBH.'.create';
                                                $webhookCreateKebabName=Str::kebab($webhookCreateBaseName);
                                                $webhookCreateResolvedName=Route::has($webhookCreateBaseName)?$webhookCreateBaseName:(Route::has($webhookCreateKebabName)?$webhookCreateKebabName:null);
                                                $webhookCreateUrl=$webhookCreateResolvedName?route($webhookCreateResolvedName):'#';
                                                $webhookCreateGuardMsg=Utility::fetchLinkMessage($langValue, VW::WBH, 'webhook_create_route_unavailable')??__('Webhook create route is unavailable. Please contact technical support or your domain administrator.');
                                                $webhookCreateBtnId='webhook-create-btn';
                                            ?>
                                            <a id="<?php echo e($webhookCreateBtnId); ?>" href="<?php echo e($webhookCreateUrl); ?>" data-url="<?php echo e($webhookCreateUrl); ?>" data-guard-msg="<?php echo e($webhookCreateGuardMsg); ?>" data-size="lg" data-ajax-popup="true" data-bs-toggle="tooltip" title="<?php echo e(__('Create')); ?>" data-title="<?php echo e(__('Create New Webhook')); ?>" class="<?php echo e(VC::BT_SM_PM); ?>"><i class="<?php echo e(VC::TI_PLS); ?>"></i></a>
                                            <?php $__env->startPush(StacksConstants::ADM_SCR_PG); ?>
                                                <script defer src="<?php echo e(asset('assets/js/routes/settings/companies/webhook.js')); ?>"></script>
                                            <?php $__env->stopPush(); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="card-body table-border-style">
                                <div class="table-responsive">
                                    <?php
                                        $webhookRows=is_iterable($webhookSetting??[])?$webhookSetting:[];
                                    ?>
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th><?php echo e(__('Module')); ?></th>
                                                <th><?php echo e(__('Url')); ?></th>
                                                <th><?php echo e(__('Method')); ?></th>
                                                <th><?php echo e(__('Action')); ?></th>
                                            </tr>
                                        </thead>
                                        <tbody class="font-style">
                                            <?php $__empty_1 = true; $__currentLoopData = $webhookRows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $webhooksetting): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                                <tr>
                                                    <td><?php echo e((is_string($webhooksetting->module??null)&&$webhooksetting->module!=='')?ucwords($webhooksetting->module):__('No module available')); ?></td>
                                                    <td><?php echo e((is_string($webhooksetting->url??null)&&$webhooksetting->url!=='')?$webhooksetting->url:__('No URL available')); ?></td>
                                                    <td><?php echo e((is_string($webhooksetting->method??null)&&$webhooksetting->method!=='')?ucwords($webhooksetting->method):__('No method available')); ?></td>
                                                    <td class="Action">
                                                        <span>
                                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check(PermissionsConstants::ED_WHK)): ?>
                                                                <div class="action-btn bg-primary ms-2">
                                                                    <?php
                                                                        $webhookEditBaseName=VW::WBH.'.edit';
                                                                        $webhookEditKebabName=Str::kebab($webhookEditBaseName);
                                                                        $webhookEditResolvedName=Route::has($webhookEditBaseName)?$webhookEditBaseName:(Route::has($webhookEditKebabName)?$webhookEditKebabName:null);
                                                                        $webhookEditUrl=$webhookEditResolvedName?route($webhookEditResolvedName, (int) data_get($webhooksetting,'id',0)):'#';
                                                                        $webhookEditGuardMsg=Utility::fetchLinkMessage($langValue, VW::WBH, 'webhook_edit_route_unavailable')??__('Webhook edit route is unavailable. Please contact technical support or your domain administrator.');
                                                                        $webhookEditBtnId='webhook-edit-btn-'.data_get($webhooksetting,'id','x');
                                                                    ?>
                                                                    <a id="<?php echo e($webhookEditBtnId); ?>" href="<?php echo e($webhookEditUrl); ?>" data-url="<?php echo e($webhookEditUrl); ?>" data-guard-msg="<?php echo e($webhookEditGuardMsg); ?>" class="mx-3 btn btn-sm d-inline-flex align-items-center" data-ajax-popup="true" data-bs-toggle="tooltip" data-size="lg" title="<?php echo e(__('Edit')); ?>" data-title="<?php echo e(__('Webhook Edit')); ?>"><i class="<?php echo e(VC::TI_PC_WT); ?>"></i></a>
                                                                    <?php $__env->startPush(StacksConstants::ADM_SCR_PG); ?>
                                                                        <script defer>
                                                                            (()=>{const btn=document.getElementById('<?php echo e($webhookEditBtnId); ?>');if(!btn||btn.getAttribute('data-listener-active')==='true')return;btn.setAttribute('data-listener-active','true');btn.addEventListener('click',e=>{try{const url=btn.getAttribute('data-url')||'#';if(url!=='#')return;e.preventDefault();const msg=btn.getAttribute('data-guard-msg')||'# ERROR';const hasBootstrap=document.querySelector('link[href*="bootstrap"]')&&window.bootstrap;let container=document.getElementById('toast-container');if(!container){container=document.createElement('div');container.id='toast-container';document.body.appendChild(container);}if(hasBootstrap){const toast=document.createElement('div');toast.className='toast';toast.setAttribute('role','alert');toast.setAttribute('aria-live','assertive');toast.setAttribute('aria-atomic','true');const body=document.createElement('div');body.className='toast-body';body.textContent=msg;toast.appendChild(body);container.appendChild(toast);bootstrap.Toast.getOrCreateInstance(toast).show();}else{alert(msg);}btn.setAttribute('data-failed-route','true');}catch(err){}});})();
                                                                        </script>
                                                                    <?php $__env->stopPush(); ?>
                                                                </div>
                                                            <?php endif; ?>
                                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check(PermissionsConstants::DEL_WHK)): ?>
                                                                <?php
                                                                    $webhookDestroyBaseName=VW::WBH.'.destroy';
                                                                    $webhookDestroyKebabName=Str::kebab($webhookDestroyBaseName);
                                                                    $webhookDestroyResolvedName=Route::has($webhookDestroyBaseName)?$webhookDestroyBaseName:(Route::has($webhookDestroyKebabName)?$webhookDestroyKebabName:null);
                                                                    $webhookDestroyRouteArray=$webhookDestroyResolvedName?[$webhookDestroyResolvedName,(int) data_get($webhooksetting,'id',0)]:['#'];
                                                                    $webhookDestroyUrl=$webhookDestroyResolvedName?route($webhookDestroyResolvedName, (int) data_get($webhooksetting,'id',0)):'#';
                                                                    $webhookDestroyGuardMsg=Utility::fetchLinkMessage($langValue, VW::WBH, 'webhook_destroy_route_unavailable')??__('Webhook destroy route is unavailable. Please contact technical support or your domain administrator.');
                                                                    $webhookDeleteFormId='delete-form-'.data_get($webhooksetting,'id','x');
                                                                    $webhookDeleteBtnId='webhook-destroy-btn-'.data_get($webhooksetting,'id','x');
                                                                ?>
                                                                <div class="action-btn bg-danger ms-2">
                                                                    <?php echo Form::open(['method'=>'DELETE','route'=>$webhookDestroyRouteArray,'id'=>$webhookDeleteFormId]); ?>

                                                                        <a id="<?php echo e($webhookDeleteBtnId); ?>" href="<?php echo e($webhookDestroyUrl); ?>" data-url="<?php echo e($webhookDestroyUrl); ?>" data-guard-msg="<?php echo e($webhookDestroyGuardMsg); ?>" class="<?php echo e(VC::BT_SM_CT_PR); ?>" data-bs-toggle="tooltip" title="<?php echo e(__('Delete')); ?>"><i class="<?php echo e(VC::TI_TRS_WT); ?>"></i></a>
                                                                    <?php echo Form::close(); ?>

                                                                </div>
                                                                <?php $__env->startPush(StacksConstants::ADM_SCR_PG); ?>
                                                                    <script defer>
                                                                        (()=>{const btn=document.getElementById('<?php echo e($webhookDeleteBtnId); ?>');if(!btn||btn.getAttribute('data-listener-active')==='true')return;btn.setAttribute('data-listener-active','true');btn.addEventListener('click',e=>{try{const url=btn.getAttribute('data-url')||'#';if(url!=='#')return;e.preventDefault();const msg=btn.getAttribute('data-guard-msg')||'# ERROR';const hasBootstrap=document.querySelector('link[href*="bootstrap"]')&&window.bootstrap;let container=document.getElementById('toast-container');if(!container){container=document.createElement('div');container.id='toast-container';document.body.appendChild(container);}if(hasBootstrap){const toast=document.createElement('div');toast.className='toast';toast.setAttribute('role','alert');toast.setAttribute('aria-live','assertive');toast.setAttribute('aria-atomic','true');const body=document.createElement('div');body.className='toast-body';body.textContent=msg;toast.appendChild(body);container.appendChild(toast);bootstrap.Toast.getOrCreateInstance(toast).show();}else{alert(msg);}btn.setAttribute('data-failed-route','true');}catch(err){}});})();
                                                                    </script>
                                                                <?php $__env->stopPush(); ?>
                                                            <?php endif; ?>
                                                        </span>
                                                    </td>
                                                </tr>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                                <tr class="text-center">
                                                    <td colspan="4"><?php echo e(__('No data found for webhooks.')); ?></td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="ip-restriction-settings" class="<?php echo e(VC::CD); ?>">
                        <div class="col-md-12">
                            <div class="card-header">
                                <div class="<?php echo e(VC::RW); ?>">
                                    <div class="col-6">
                                        <h5 class="mb-2"><?php echo e(__('IP Restriction Settings')); ?></h5>
                                    </div>
                                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('create webhook')): ?>
                                        <div class="col-6 text-end">
                                            <?php
                                                $systemIpCreateBaseName=VW::SYS.'.ip.create';
                                                $systemIpCreateKebabName=Str::kebab($systemIpCreateBaseName);
                                                $systemIpCreateResolvedName=Route::has($systemIpCreateBaseName)?$systemIpCreateBaseName:(Route::has($systemIpCreateKebabName)?$systemIpCreateKebabName:null);
                                                $systemIpCreateUrl=$systemIpCreateResolvedName?route($systemIpCreateResolvedName):'#';
                                                $langValue=isset($lang)&&is_string($lang)?$lang:Utility::fetchUserLang();
                                                $systemIpCreateGuardMsg=Utility::fetchLinkMessage($langValue, VW::SYS, 'system_ip_create_route_unavailable')??__('System IP create route is unavailable. Please contact technical support or your domain administrator.');
                                                $systemIpCreateBtnId='system-ip-create-btn';
                                            ?>
                                            <a id="<?php echo e($systemIpCreateBtnId); ?>" href="<?php echo e($systemIpCreateUrl); ?>" data-url="<?php echo e($systemIpCreateUrl); ?>" data-guard-msg="<?php echo e($systemIpCreateGuardMsg); ?>" data-size="md" data-ajax-popup="true" data-bs-toggle="tooltip" title="<?php echo e(__('Create')); ?>" data-title="<?php echo e(__('Create New IP')); ?>" class="<?php echo e(VC::BT_SM_PM); ?>"><i class="<?php echo e(VC::TI_PLS); ?> <?php echo e(VC::TXT_WT); ?>"></i></a>
                                            <?php $__env->startPush(StacksConstants::ADM_SCR_PG); ?>
                                                <script defer src="<?php echo e(asset('assets/js/routes/settings/companies/ip.js')); ?>"></script>
                                            <?php $__env->stopPush(); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="card-body table-border-style">
                                <div class="table-responsive">
                                    <?php
                                        $ipRows=is_iterable($ips??[])?$ips:[];
                                    ?>
                                    <table class="<?php echo e(VC::TB); ?>">
                                        <thead>
                                            <tr>
                                                <th class="w-75"><?php echo e(__('IP')); ?></th>
                                                <th><?php echo e(__('Action')); ?></th>
                                            </tr>
                                        </thead>
                                        <tbody class="font-style">
                                            <?php $__empty_1 = true; $__currentLoopData = $ipRows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ip): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                                <?php
                                                    $rowId=(int) data_get($ip,'id',0);
                                                    $rowIdStr=$rowId?:'x';
                                                ?>
                                                <tr>
                                                    <td><?php echo e((is_string(data_get($ip,'ip'))&&data_get($ip,'ip')!=='')?data_get($ip,'ip'):__('No IP available')); ?></td>
                                                    <td class="Action">
                                                        <span>
                                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check(PermissionsConstants::ED_WHK)): ?>
                                                                <div class="<?php echo e(VC::ACT_BTN_PRIM); ?>">
                                                                    <?php
                                                                        $systemIpEditBaseName=VW::SYS.'.ip.edit';
                                                                        $systemIpEditKebabName=Str::kebab($systemIpEditBaseName);
                                                                        $systemIpEditResolvedName=Route::has($systemIpEditBaseName)?$systemIpEditBaseName:(Route::has($systemIpEditKebabName)?$systemIpEditKebabName:null);
                                                                        $systemIpEditUrl=$systemIpEditResolvedName?route($systemIpEditResolvedName,$rowId):'#';
                                                                        $systemIpEditGuardMsg=Utility::fetchLinkMessage($langValue, VW::SYS, 'system_ip_edit_route_unavailable')??__('System IP edit route is unavailable. Please contact technical support or your domain administrator.');
                                                                        $systemIpEditBtnId='system-ip-edit-btn-'.$rowIdStr;
                                                                    ?>
                                                                    <a id="<?php echo e($systemIpEditBtnId); ?>" href="<?php echo e($systemIpEditUrl); ?>" data-url="<?php echo e($systemIpEditUrl); ?>" data-guard-msg="<?php echo e($systemIpEditGuardMsg); ?>" class="<?php echo e(VC::BT_SM_FL_CT); ?>" data-ajax-popup="true" data-bs-toggle="tooltip" title="<?php echo e(__('Edit')); ?>" data-title="<?php echo e(__('IP Edit')); ?>"><i class="<?php echo e(VC::TI_PC_WT); ?>"></i></a>
                                                                    <?php $__env->startPush(StacksConstants::ADM_SCR_PG); ?>
                                                                        <script defer>
                                                                            (()=>{const btn=document.getElementById('<?php echo e($systemIpEditBtnId); ?>');if(!btn||btn.getAttribute('data-listener-active')==='true')return;btn.setAttribute('data-listener-active','true');btn.addEventListener('click',e=>{try{const url=btn.getAttribute('data-url')||'#';if(url!=='#')return;e.preventDefault();const msg=btn.getAttribute('data-guard-msg')||'';const hasBootstrap=document.querySelector('link[href*="bootstrap"]')&&window.bootstrap;let container=document.getElementById('toast-container');if(!container){container=document.createElement('div');container.id='toast-container';document.body.appendChild(container);}if(hasBootstrap){const toast=document.createElement('div');toast.className='toast';toast.setAttribute('role','alert');toast.setAttribute('aria-live','assertive');toast.setAttribute('aria-atomic','true');const body=document.createElement('div');body.className='toast-body';body.textContent=msg;toast.appendChild(body);container.appendChild(toast);bootstrap.Toast.getOrCreateInstance(toast).show();}else{alert(msg);}btn.setAttribute('data-failed-route','true');}catch(err){}});})();
                                                                        </script>
                                                                    <?php $__env->stopPush(); ?>
                                                                </div>
                                                            <?php endif; ?>
                                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check(PermissionsConstants::DEL_WHK)): ?>
                                                                <div class="<?php echo e(VC::ACT_BTN_DNG_2); ?>">
                                                                    <?php
                                                                        $systemIpDestroyBaseName=VW::SYS.'.ip.destroy';
                                                                        $systemIpDestroyKebabName=Str::kebab($systemIpDestroyBaseName);
                                                                        $systemIpDestroyResolvedName=Route::has($systemIpDestroyBaseName)?$systemIpDestroyBaseName:(Route::has($systemIpDestroyKebabName)?$systemIpDestroyKebabName:null);
                                                                        $systemIpDestroyRouteArray=$systemIpDestroyResolvedName?[$systemIpDestroyResolvedName,$rowId]:['#'];
                                                                        $systemIpDestroyUrl=$systemIpDestroyResolvedName?route($systemIpDestroyResolvedName,$rowId):'#';
                                                                        $systemIpDestroyGuardMsg=Utility::fetchLinkMessage($langValue, VW::SYS, 'system_ip_destroy_route_unavailable')??__('System IP destroy route is unavailable. Please contact technical support or your domain administrator.');
                                                                        $systemIpDeleteFormId='delete-form-'.$rowIdStr;
                                                                        $systemIpDeleteBtnId='system-ip-destroy-btn-'.$rowIdStr;
                                                                    ?>
                                                                    <?php echo Form::open(['method'=>'DELETE','route'=>$systemIpDestroyRouteArray,'id'=>$systemIpDeleteFormId]); ?>

                                                                        <a id="<?php echo e($systemIpDeleteBtnId); ?>" href="<?php echo e($systemIpDestroyUrl); ?>" data-url="<?php echo e($systemIpDestroyUrl); ?>" data-guard-msg="<?php echo e($systemIpDestroyGuardMsg); ?>" class="<?php echo e(VC::BT_SM_CT_PR); ?>" data-bs-toggle="tooltip" title="<?php echo e(__('Delete')); ?>"><i class="<?php echo e(VC::TI_TRS_WT); ?>"></i></a>
                                                                    <?php echo Form::close(); ?>

                                                                    <?php $__env->startPush(StacksConstants::ADM_SCR_PG); ?>
                                                                        <script defer>
                                                                            (()=>{const btn=document.getElementById('<?php echo e($systemIpDeleteBtnId); ?>');if(!btn||btn.getAttribute('data-listener-active')==='true')return;btn.setAttribute('data-listener-active','true');btn.addEventListener('click',e=>{try{const url=btn.getAttribute('data-url')||'#';if(url!=='#')return;e.preventDefault();const msg=btn.getAttribute('data-guard-msg')||'';const hasBootstrap=document.querySelector('link[href*="bootstrap"]')&&window.bootstrap;let container=document.getElementById('toast-container');if(!container){container=document.createElement('div');container.id='toast-container';document.body.appendChild(container);}if(hasBootstrap){const toast=document.createElement('div');toast.className='toast';toast.setAttribute('role','alert');toast.setAttribute('aria-live','assertive');toast.setAttribute('aria-atomic','true');const body=document.createElement('div');body.className='toast-body';body.textContent=msg;toast.appendChild(body);container.appendChild(toast);bootstrap.Toast.getOrCreateInstance(toast).show();}else{alert(msg);}btn.setAttribute('data-failed-route','true');}catch(err){}});})();
                                                                        </script>
                                                                    <?php $__env->stopPush(); ?>
                                                                </div>
                                                            <?php endif; ?>
                                                        </span>
                                                    </td>
                                                </tr>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                                <tr class="text-center">
                                                    <td colspan="2"><?php echo e(__('No data found for IP addresses.')); ?></td>
                                                </tr>
                                            <?php endif; ?>
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
<?php $__env->stopSection(); ?>
<?php $__env->startPush(StacksConstants::ADM_SCR_PG); ?>
    <script src="<?php echo e(asset('css/summernote/summernote-bs4.js')); ?>"></script>
    <script async src="<?php echo e(asset('assets/js/routes/settings/companies/lang/notes.js')); ?>"></script>
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
                console.error("jQuery failed to load");
                return;
            }

            // 1) Summernote blur handlers (4 templates + footer notes) with reuse
            const bindSummernoteSave = (selector, urlKey) => {
                const $els = $(selector);
                if (!$els.length) {
                return;
                }
                if (!$.fn?.summernote) {
                console.error("Summernote not available");
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
                () => "<?php echo e(route('offer_letter.update', $offerlang)); ?>"
            );
            bindSummernoteSave(
                ".summernote-simple1",
                () => "<?php echo e(route('joining_letter.update', $joininglang)); ?>"
            );
            bindSummernoteSave(
                ".summernote-simple2",
                () => "<?php echo e(route('experience_certificate.update', $explang)); ?>"
            );
            bindSummernoteSave(
                ".summernote-simple3",
                () => "<?php echo e(route('noc.update', $noclang)); ?>"
            );
            bindSummernoteSave(
                ".summernote-simple4",
                () => "<?php echo e(route('systems.settings.footernote')); ?>"
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
                    "<?php echo e(env('APP_URL')); ?>" + "/public/assets/css/style-dark.css";
                    const lightHref =
                    "<?php echo e(env('APP_URL')); ?>" + "/public/assets/css/style.css";
                    if (darkChk.checked) {
                    styleEl?.setAttribute("href", darkHref);
                    if (logo.length) {
                        logo.attr("src", "<?php echo e($logo . $logo_light); ?>");
                    }
                    } else {
                    styleEl?.setAttribute("href", lightHref);
                    if (logo.length) {
                        logo.attr("src", "<?php echo e($logo . $logo_dark); ?>");
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
                    const src = `<?php echo e(url('/invoices/preview')); ?>/${template}/${color}`;
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
                    const src = `<?php echo e(url('/'.VW::PPS.'/preview')); ?>/${template}/${color}`;
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
                    const src = `<?php echo e(url('/bill/preview')); ?>/${template}/${color}`;
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
            bindPreview(String("<?php echo e(SC::CPN_LG_DK); ?>"), "image");
            bindPreview("company_logo_light", "image1");
            bindPreview(String("<?php echo e(SC::CPN_FAVICON_K); ?>"), "image2");

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
                    _token: "<?php echo e(csrf_token()); ?>",
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
<?php $__env->stopPush(); ?>
<?php echo $__env->make(ExtendingLayoutsConstants::ADM, \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/aronboliveira/Desktop/programming/Prestech/erp/erpgo-fork/erp_prestech/_inc/laravel/resources/views/settings/company.blade.php ENDPATH**/ ?>