<?php
	use App\Config\Constants\{ExtendingLayoutsConstants,StacksConstants,ViewClassNamesConstants,YieldingConstants};
	use App\Models\Utility;
	use Illuminate\Support\Facades\{Log,Route};
	use Modules\LandingPage\Config\Constants\{
		ExtendingLandingPageLayoutConstants as E,
		RoutesResourcesConstants              as R,
		SettingsConstants                     as LandingPageSettingsConstants
	};
    use Nwidart\Modules\Facades\Module;
    
	$lpSettings ??= [];
	$logo       ??= '';
	try {
		$lpSettings = \Modules\LandingPage\Entities\LandingPageSetting
			::landingPageSetting() ?: [];
		$logo       = Utility::getFile('uploads/landing_page_image') ?: '';
	} catch (\Error $e) {
		Log::error(
			'Error fetching landing page settings and logo',
			[
				'exception_class'=>get_class($e),
				'message'=>$e->getMessage(),
				'file'=>$e->getFile(),
				'line'=>$e->getLine()
			]
		);
	} catch (\Exception $e) {
		Log::error(
			'Exception fetching landing page settings and logo',
			[
				'exception_class'=>get_class($e),
				'message'=>$e->getMessage(),
				'file'=>$e->getFile(),
				'line'=>$e->getLine()
			]
		);
	} catch (\Throwable $e) {
		Log::error(
			'Throwable fetching landing page settings and logo',
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
<?php $__env->startSection('breadcrumb'); ?>
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
<script src="<?php echo e(Module::asset('LandingPage:js/plugins/tinymce.min.js')); ?>" referrerpolicy="origin"></script>

<?php $__env->stopPush(); ?>

<?php $__env->startSection('breadcrumb'); ?>
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

<?php $__env->startSection('content'); ?>
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
                                        <h5><?php echo e(__('Testimonials')); ?></h5>
                                    </div>
                                </div>
                            </div>
                            <?php echo e(Collective\Html\FormFacade::open(array('route' => R::TTMN.'.store', 'method'=>'post', 'enctype' => "multipart/form-data"))); ?>

                                <?php echo csrf_field(); ?>
                                <div class="card-body">
                                    <div class="row">

                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <?php echo e(Collective\Html\FormFacade::label('Heading', __('Heading'), ['class' => 'form-label'])); ?>

                                                <?php echo e(Collective\Html\FormFacade::text($lpSettings[LandingPageSettingsConstants::TM_HDG_K],$lpSettings[LandingPageSettingsConstants::TM_HDG_K], ['class' => 'form-control', 'placeholder' => __('Enter Heading')])); ?>

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
                                                <?php echo e(Collective\Html\FormFacade::label('Description', __('Description'), ['class' => 'form-label'])); ?>

                                                <?php echo e(Collective\Html\FormFacade::text($lpSettings[LandingPageSettingsConstants::TM_DESC_K], $lpSettings[LandingPageSettingsConstants::TM_DESC_K], ['class' => 'form-control', 'placeholder' => __('Enter Description')])); ?>

                                                <?php $__errorArgs = [$lpSettings[LandingPageSettingsConstants::TM_DESC_K]];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                                <span class="invalid-testimonials_description" role="alert">
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
                                                <?php echo e(Collective\Html\FormFacade::label('Long Description', __('Long Description'), ['class' => 'form-label'])); ?>

                                                <?php echo e(Collective\Html\FormFacade::textarea($lpSettings[LandingPageSettingsConstants::TM_LONG_DESC_K], $lpSettings[LandingPageSettingsConstants::TM_LONG_DESC_K], ['class' => 'form-control', 'placeholder' => __('Enter Long Description')])); ?>

                                                <?php $__errorArgs = [$lpSettings[LandingPageSettingsConstants::TM_LONG_DESC_K]];
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
                                    <button class="<?php echo e(ViewClassNamesConstants::BT_PR_PRM10); ?>" type="submit" ><?php echo e(__('Save Changes')); ?></button>
                                </div>
                            <?php echo e(Collective\Html\FormFacade::close()); ?>

                        </div>
                        <div class="card">
                            <div class="card-header">
                                <div class="row align-items-center">
                                    <div class="<?php echo e(ViewClassNamesConstants::CLMS9); ?>">
                                        
                                    </div>
                                    <div class="<?php echo e(ViewClassNamesConstants::CLMS_JCE3); ?>">
                                        <?php $createRoute = R::TTMN.'.create'; ?>
                                        <a
                                            data-size="lg"
                                            data-url="<?php echo e(Route::has($createRoute) ? route($createRoute) : '#'); ?>"
                                            data-ajax-popup="true"
                                            data-bs-toggle="tooltip"
                                            title="<?php echo e(__('Discover Feature Create')); ?>"
                                            class="btn btn-sm btn-primary <?php echo e(Route::has($createRoute) ? '' : 'disabled'); ?>"
                                            <?php echo e(Route::has($createRoute) ? '' : 'aria-disabled="true"'); ?>

                                        >
                                            <i class="<?php echo e(ViewClassNamesConstants::TI_PLS_LG); ?>"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">

                                

                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th><?php echo e(__('No')); ?></th>
                                                <th><?php echo e(__('Name')); ?></th>
                                                <th><?php echo e(__('Action')); ?></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                           <?php if(is_array($testimonials) || is_object($testimonials)): ?>
                                            <?php
                                                $no = 1
                                            ?>
                                                <?php $__currentLoopData = $testimonials; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <tr>
                                                        <td><?php echo e($no++); ?></td>
                                                        <td><?php echo e($value[LandingPageSettingsConstants::TM_TTL_K]); ?></td>
                                                        <td>
                                                            <span>
                                                                <div class="<?php echo e(ViewClassNamesConstants::ACT_BTN_PRIM); ?>">
                                                                    <?php if(Route::has(R::TTMN.'.edit')): ?>
                                                                        <a href="#"
                                                                           class="mx-3 btn btn-sm align-items-center"
                                                                           data-url="<?php echo e(route(R::TTMN.'.edit', $key)); ?>"
                                                                           data-ajax-popup="true"
                                                                           data-title="<?php echo e(__('Edit Page')); ?>"
                                                                           data-size="lg"
                                                                           data-bs-toggle="tooltip"
                                                                           title="<?php echo e(__('Edit')); ?>"
                                                                           data-original-title="<?php echo e(__('Edit')); ?>"
                                                                        >
                                                                            <i class="<?php echo e(ViewClassNamesConstants::TI_PC_WT); ?>"></i>
                                                                        </a>
                                                                    <?php else: ?>
                                                                        <a href="#"
                                                                           class="<?php echo e(ViewClassNamesConstants::BT_SM_CT_DSB); ?>"
                                                                           aria-disabled="true"
                                                                           data-bs-toggle="tooltip"
                                                                           title="<?php echo e(__('Edit')); ?>"
                                                                        >
                                                                            <i class="<?php echo e(ViewClassNamesConstants::TI_PC_WT); ?>"></i>
                                                                        </a>
                                                                    <?php endif; ?>
                                                                </div>
                                                                <div class="<?php echo e(ViewClassNamesConstants::ACT_BTN_DNG_2); ?>">
                                                                    <?php if(Route::has(R::TTMN.'.delete')): ?>
                                                                        <?php echo Collective\Html\FormFacade::open([
                                                                            'method' => 'GET',
                                                                            'route'  => [R::TTMN.'.delete', $key],
                                                                            'id'     => 'delete-form-' . $key
                                                                        ]); ?>

                                                                            <a href="#"
                                                                               class="<?php echo e(ViewClassNamesConstants::BT_SM_CT_PR); ?>"
                                                                               data-bs-toggle="tooltip"
                                                                               title="<?php echo e(__('Delete')); ?>"
                                                                               data-original-title="<?php echo e(__('Delete')); ?>"
                                                                               data-confirm="<?php echo e(__('Are You Sure?') . '|' . __('This action cannot be undone. Do you want to continue?')); ?>"
                                                                               data-confirm-yes="document.getElementById('delete-form-<?php echo e($key); ?>').submit();"
                                                                            >
                                                                                <i class="ti ti-trash text-white"></i>
                                                                            </a>
                                                                        <?php echo Collective\Html\FormFacade::close(); ?>

                                                                    <?php else: ?>
                                                                        <a href="#"
                                                                           class="<?php echo e(ViewClassNamesConstants::BT_SM_CT_DSB); ?>"
                                                                           aria-disabled="true"
                                                                           data-bs-toggle="tooltip"
                                                                           title="<?php echo e(__('Delete')); ?>"
                                                                        >
                                                                            <i class="<?php echo e(ViewClassNamesConstants::TI_TRS_WT); ?>"></i>
                                                                        </a>
                                                                    <?php endif; ?>
                                                                </div>
                                                            </span>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
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
<?php $__env->stopSection(); ?>




<?php echo $__env->make(ExtendingLayoutsConstants::ADM, \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\Aron\Desktop\programming\Prestech\erp\erpgo-fork\erp_prestech\_inc\laravel\Modules/LandingPage\Resources/views/landingpage/testimonials/index.blade.php ENDPATH**/ ?>