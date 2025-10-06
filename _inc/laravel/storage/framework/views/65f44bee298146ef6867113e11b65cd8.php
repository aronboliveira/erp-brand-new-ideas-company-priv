<?php
	use App\Config\Constants\{ExtendingLayoutsConstants,StacksConstants,
        ViewClassNamesConstants,YieldingConstants};
    use App\Models\Utility;
	use Illuminate\Support\Facades\{Log,Route};
	use Modules\LandingPage\Config\Constants\{ExtendingLandingPageLayoutConstants as E,
        RoutesResourcesConstants as R,
        SettingsConstants as LandingPageSettingsConstants};
    use Nwidart\Modules\Facades\Module;
    $discover_of_features ??= [];
	$lpSettings ??= [];
	$logo ??= '';
    $lang = Utility::fetchUserLang();
	try {
        $lpSettings=\Modules\LandingPage\Entities\LandingPageSetting::landingPageSetting()?:[];
        $discover_of_features = !empty($lpSettings[LandingPageSettingsConstants::DC_OF_FTS_K]) 
            ? $lpSettings[LandingPageSettingsConstants::DC_OF_FTS_K]
            : [];
		$logo= Utility::getFile('uploads/landing_page_image')?:'';
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
<?php $__env->startPush(StacksConstants::ADM_CSS); ?>
    <link rel="stylesheet" href=" <?php echo e(Module::asset('LandingPage:css/summernote/summernote-bs4.css')); ?>" />
<?php $__env->stopPush(); ?>

<?php $__env->startPush(StacksConstants::ADM_SCR_PG); ?>
    <script src="<?php echo e(Module::asset('LandingPage:js/plugins/summernote-bs4.js')); ?>" referrerpolicy="origin"></script>
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
                                        <h5><?php echo e(__('Discover')); ?></h5>
                                    </div>
                                </div>
                            </div>
                            <?php echo e(Collective\Html\FormFacade::open(array('route' => R::DV.'.store', 'method'=>'post', 'enctype' => "multipart/form-data"))); ?>

                                <?php echo csrf_field(); ?>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <?php echo e(Collective\Html\FormFacade::label('Heading', __('Heading'), ['class' => 'form-label'])); ?>

                                                <?php echo e(Collective\Html\FormFacade::text('discover_heading',$lpSettings['discover_heading'], ['class' => 'form-control', 'placeholder' => __('Enter Heading')])); ?>

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

                                                <?php echo e(Collective\Html\FormFacade::text('discover_description', $lpSettings['discover_description'], ['class' => 'form-control', 'placeholder' => __('Enter Description')])); ?>

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
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <?php echo e(Collective\Html\FormFacade::label('Live Demo Link', __('Live Demo Link'), ['class' => 'form-label'])); ?>

                                                <?php echo e(Collective\Html\FormFacade::text('discover_live_demo_link', $lpSettings['discover_live_demo_link'], ['class' => 'form-control', 'placeholder' => __('Enter Link')])); ?>

                                                <?php $__errorArgs = ['discover_live_demo_link'];
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
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <?php echo e(Collective\Html\FormFacade::label('Buy Now Link', __('Buy Now Link'), ['class' => 'form-label'])); ?>

                                                <?php echo e(Collective\Html\FormFacade::text('discover_buy_now_link', $lpSettings['discover_buy_now_link'], ['class' => 'form-control', 'placeholder' => __('Enter Link')])); ?>

                                                <?php $__errorArgs = ['discover_buy_now_link'];
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
                                    <?php 
                                        $discoverCreateRoute = R::DV.'.create';
                                        $canCreateDiscover   = Route::has($discoverCreateRoute);
                                        $discoverCreateUrl   = $canCreateDiscover 
                                            ? route($discoverCreateRoute) 
                                            : '#';
                                    ?>
                                    <div class="<?php echo e(ViewClassNamesConstants::CLMS_JCE3); ?>">
                                        <a 
                                            data-size="lg"
                                            data-url="<?php echo e($discoverCreateUrl); ?>"
                                            data-ajax-popup="true"
                                            data-bs-toggle="tooltip"
                                            title="<?php echo e(__('Discover Feature Create')); ?>"
                                            class="btn btn-sm btn-primary <?php echo e($canCreateDiscover ? '' : 'disabled'); ?>"
                                            <?php echo e($canCreateDiscover ? '' : 'aria-disabled="true"'); ?>

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
                                           <?php if(is_array($discover_of_features) || is_object($discover_of_features)): ?>
                                           <?php
                                                $no = 1
                                            ?>
                                                <?php $__currentLoopData = $discover_of_features; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <tr>
                                                        <td><?php echo e($no++); ?></td>
                                                        <td><?php echo e($value['discover_heading']); ?></td>
                                                        <?php
                                                            $editRoute    = R::DV.'.edit';
                                                            $deleteRoute  = R::DV.'.delete';
                                                            $canEdit      = Route::has($editRoute);
                                                            $canDelete    = Route::has($deleteRoute);
                                                            $editUrl      = $canEdit   ? route($editRoute, $key)    : '#';
                                                        ?>
                                                        <td>
                                                            <span>
                                                                <div class="action-btn <?php echo e(ViewClassNamesConstants::BG_P); ?> ms-2">
                                                                    <?php if($canEdit): ?>
                                                                        <a href="<?php echo e($editUrl); ?>"
                                                                        class="mx-3 btn btn-sm align-items-center"
                                                                        data-url="<?php echo e($editUrl); ?>"
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
                                                                    <?php if($canDelete): ?>
                                                                        <?php echo Collective\Html\FormFacade::open([
                                                                            'method' => 'GET',
                                                                            'route'  => [$deleteRoute, $key],
                                                                            'id'     => 'delete-form-' . $key
                                                                        ]); ?>

                                                                            <a href="#"
                                                                            class="<?php echo e(ViewClassNamesConstants::BT_SM_CT_PR); ?>"
                                                                            data-bs-toggle="tooltip"
                                                                            title="<?php echo e(__('Delete')); ?>"
                                                                            data-original-title="<?php echo e(__('Delete')); ?>"
                                                                            data-confirm="<?php echo e(__(Utility::fetchLinkMessage($lang, 'generics', 'are_you_sure') ?? 'Are You Sure?')); ?>|<?php echo e(__(Utility::fetchLinkMessage($lang, 'generics', 'irreversible_action') ?? 'This action can not be undone. Do you want to continue?')); ?>"
                                                                            data-confirm-yes="document.getElementById('delete-form-<?php echo e($key); ?>').submit();"
                                                                            >
                                                                                <i class="<?php echo e(ViewClassNamesConstants::TI_TRS_WT); ?>"></i>
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



<?php echo $__env->make(ExtendingLayoutsConstants::ADM, \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/aronboliveira/Desktop/programming/Prestech/erp/erpgo-fork/erp_prestech/_inc/laravel/Modules/LandingPage/Resources/views/landingpage/discover/index.blade.php ENDPATH**/ ?>