<?php
	use App\Config\Constants\{ExtendingLayoutsConstants,StacksConstants,ViewClassNamesConstants,YieldingConstants};
	use Illuminate\Support\Facades\Log;
	use Illuminate\Support\Facades\Route;
	use Modules\LandingPage\Config\Constants\{ExtendingLandingPageLayoutConstants as E,RoutesResourcesConstants as R,SettingsConstants as LandingPageSettingsConstants};
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
    <script>
        document.getElementById("home_banner").onchange = function () {
                var src = URL.createObjectURL(this.files[0])
                document.getElementById('image').src = src
            }
            document.getElementById("home_logo").onchange = function () {
                var src = URL.createObjectURL(this.files[0])
                document.getElementById('image1').src = src
            }
    </script>
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
									<?php if($errors->any()): ?>
										<div class="alert alert-danger">
												<h4 class="alert-heading"><?php echo e(__('Whoops! Something went wrong.')); ?></h4>
												<ul class="mb-0">
													<?php if($errors->any()): ?>
														<?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
																<li><?php echo e($error); ?></li>
														<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
													<?php else: ?>
																<li><?php echo e(__('Could not retrieve the specific error. Please check for entry data errors.')); ?></li>
													<?php endif; ?>
												</ul>
										</div>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>



<?php echo $__env->make(ExtendingLayoutsConstants::ADM, \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\Aron\Desktop\programming\Prestech\erp\erpgo-fork\erp_prestech\_inc\laravel\resources\views/partials/validation/admin_error.blade.php ENDPATH**/ ?>