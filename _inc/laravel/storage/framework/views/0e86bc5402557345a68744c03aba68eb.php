<?php
    use App\Config\Constants\{
        DatabaseConstants,
        ExtendingLayoutsConstants,
        YieldingConstants,
        SettingsConstants,
        StacksConstants,
        ViewClassNamesConstants
    };
    use Modules\LandingPage\Config\Constants\{
        ExtendingLandingPageLayoutConstants as E, 
        RoutesResourcesConstants as R
    };
    use Modules\LandingPage\Config\Constants\SettingsConstants as LandingPageSettingsConstants;
    use Illuminate\Support\Facades\{Log, Route};
	use App\Models\Utility;
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
?>

<?php $__env->startSection(YieldingConstants::ADM_PG_TTL); ?>
    <?php echo e(__('Landing Page')); ?>

<?php $__env->stopSection(); ?>
<?php $__env->startSection(YieldingConstants::ADM_BDC); ?>
    <li class="breadcrumb-item">
        <a href="<?php echo e(Route::has('dashboard') ? route('dashboard') : '#'); ?>"
        <?php echo e(Route::has('dashboard') ? '' : 'aria-disabled="true"'); ?>>
            <?php echo e(__('Dashboard')); ?>

        </a>
    </li>
    <li class="breadcrumb-item">
        <?php echo e(__('Landing Page')); ?>

    </li>
<?php $__env->stopSection(); ?>

<?php $__env->startPush(StacksConstants::ADM_SCR_PG); ?>
<?php $__env->stopPush(); ?>

<?php $__env->startSection(YieldingConstants::ADM_BDC); ?>
    <li class="breadcrumb-item">
        <a href="<?php echo e(Route::has('dashboard') ? route('dashboard') : '#'); ?>"
        <?php echo e(Route::has('dashboard') ? '' : 'aria-disabled="true"'); ?>>
            <?php echo e(__('Dashboard')); ?>

        </a>
    </li>
    <li class="breadcrumb-item">
        <?php echo e(__('Landing Page')); ?>

    </li>
<?php $__env->stopSection(); ?>

<?php $__env->startSection(YieldingConstants::ADM_CTT); ?>
    <div class="row">
        <div class="col-sm-12">
            <div class="row">
                <div class="col-xl-3">
                    <div class="<?php echo e(ViewClassNamesConstants::CD_STK); ?>" style="top:30px">
                        <div class="<?php echo e(ViewClassNamesConstants::LG_FLSH); ?>" id="useradd-sidenav">
                            <?php echo $__env->make(R::LP.'::'.E::LOS.'.tab', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                        </div>
                    </div>
                </div>
                <div class="col-xl-9">
                    
                    <div class="card">
                        <div class="card-header">
                            <div class="row">
                                <div class="<?php echo e(ViewClassNamesConstants::CLMS10); ?>">
                                    <h5><?php echo e(__('Plan Section')); ?></h5>
                                </div>
                            </div>
                        </div>
                        <?php echo e(Collective\Html\FormFacade::open(array('route' => R::PRC_PLN.'.store', 'method'=>'post', 'enctype' => "multipart/form-data"))); ?>

                            <div class="card-body">
                                <div class="row">

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <?php echo e(Collective\Html\FormFacade::label('Title', __('Title'), ['class' => 'form-label'])); ?>

                                            <?php echo e(Collective\Html\FormFacade::text(LandingPageSettingsConstants::PN_TTL_K,!empty($data[LandingPageSettingsConstants::PN_TTL_K]) ? $data[LandingPageSettingsConstants::PN_TTL_K] : '# ERROR: COULD NOT FIND PLAN TITLE', ['class' => 'form-control', 'placeholder' => __('Enter Title')])); ?>

                                            <?php $__errorArgs = ['mail_host'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                            <span class="invalid-mail_driver" role="alert">
                                                    <strong class="text-danger"><?php echo e($message); ?></strong>
                                                </span>
                                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <?php echo e(Collective\Html\FormFacade::label('Heading', __('Heading'), ['class' => 'form-label'])); ?>

                                            <?php echo e(Collective\Html\FormFacade::text(LandingPageSettingsConstants::PN_HDG_K,!empty($data[LandingPageSettingsConstants::PN_HDG_K]) ? $data[LandingPageSettingsConstants::PN_HDG_K] : '# ERROR: COULD NOT FIND PLAN HEADING', ['class' => 'form-control', 'placeholder' => __('Enter Heading')])); ?>

                                            <?php $__errorArgs = ['mail_host'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                            <span class="invalid-mail_driver" role="alert">
                                                    <strong class="text-danger"><?php echo e($message); ?></strong>
                                                </span>
                                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <?php echo e(Collective\Html\FormFacade::label('Description', __('Description'), ['class' => 'form-label'])); ?>

                                            <?php echo e(Collective\Html\FormFacade::text(LandingPageSettingsConstants::PN_DESC_K, !empty($data[LandingPageSettingsConstants::PN_DESC_K]) ? $data[LandingPageSettingsConstants::PN_DESC_K] : '# ERROR: COULD NOT FIND PLAN DESCRIPTION', ['class' => 'form-control', 'placeholder' => __('Enter Description')])); ?>

                                            <?php $__errorArgs = ['mail_port'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                            <span class="invalid-mail_port" role="alert">
                                                    <strong class="text-danger"><?php echo e($message); ?></strong>
                                                </span>
                                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                        </div>
                                    </div>

                                </div>
                            </div>
                            <div class="card-footer text-end">
                                <input class="<?php echo e(ViewClassNamesConstants::BT_PR_PRM10); ?>" type="submit" value="<?php echo e(__('Save Changes')); ?>">
                            </div>
                        <?php echo e(Collective\Html\FormFacade::close()); ?>

                    </div>
                    
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>


<?php echo $__env->make(ExtendingLayoutsConstants::ADM, \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/aronboliveira/Desktop/programming/Prestech/erp/erpgo-fork/erp_prestech/_inc/laravel/Modules/LandingPage/Resources/views/landingpage/pricing_plan.blade.php ENDPATH**/ ?>