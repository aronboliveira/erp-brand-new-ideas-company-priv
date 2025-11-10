<?php
	use App\Config\Constants\{ExtendingLayoutsConstants,StacksConstants,ViewClassNamesConstants as VC,YieldingConstants};
	use App\Models\Utility;
    use Collective\Html\FormFacade as Form;
	use Illuminate\Support\Facades\{Log, Route};
	use Modules\LandingPage\Config\Constants\{
		ExtendingLandingPageLayoutConstants as E,
		RoutesResourcesConstants                as R,
		SettingsConstants                       as LPC
	};
    use Nwidart\Modules\Facades\Module;
    
    $lang = Utility::fetchUserLang();
	$lpSettings ??= [];
	$logo       ??= '';
	try {
		$lpSettings = \Modules\LandingPage\Entities\LandingPageSetting::landingPageSetting() ?: [];
		$logo       = Utility::getFile('uploads/landing_page_image')            ?: '';
	} catch (\Error $e) {
		Log::error(
			'Error fetching landing page settings/logo',
			[
				'exception_class'=>get_class($e),
				'message'        =>$e->getMessage(),
				'file'           =>$e->getFile(),
				'line'           =>$e->getLine()
			]
		);
	} catch (\Exception $e) {
		Log::error(
			'Exception fetching landing page settings/logo',
			[
				'exception_class'=>get_class($e),
				'message'        =>$e->getMessage(),
				'file'           =>$e->getFile(),
				'line'           =>$e->getLine()
			]
		);
	} catch (\Throwable $e) {
		Log::error(
			'Throwable fetching landing page settings/logo',
			[
				'exception_class'=>get_class($e),
				'message'        =>$e->getMessage(),
				'file'           =>$e->getFile(),
				'line'           =>$e->getLine()
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
    <link rel="stylesheet" href=" <?php echo e(asset('Modules/landingpage/css/summernote/summernote-bs4.css')); ?>" />
<?php $__env->stopPush(); ?>

<?php $__env->startPush(StacksConstants::ADM_SCR_PG); ?>
    <script src="<?php echo e(asset('Modules/landingpage/js/plugins/summernote-bs4.js')); ?>" referrerpolicy="origin"></script>
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
                    <div class="<?php echo e(VC::CD_STK); ?>" style="top:30px">
                        <div class="<?php echo e(VC::LG_FLSH); ?>" id="useradd-sidenav">
                            <?php echo $__env->make(R::LP.'::'.E::LOS.'.tab', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                        </div>
                    </div>
                </div>
                <div class="col-xl-9">
                    
                        <div class="card">
                            <?php echo e(Form::open(array('route' => R::FQ.'.store', 'method'=>'post', 'enctype' => "multipart/form-data"))); ?>

                                <?php echo csrf_field(); ?>
                                <div class="card-header">
                                    <div class="row align-items-center">
                                        <div class="col-6">
                                            <h5 class="mb-2"><?php echo e(__('FAQ')); ?></h5>
                                        </div>
                                        <div class="col switch-width text-end">
                                            <div class="form-group mb-0">
                                                <div class="custom-control custom-switch">
                                                    <input type="checkbox" data-toggle="switchbutton" data-onstyle="primary" class="" name="faq_status"
                                                        id="faq_status"  <?php echo e(isset($lpSettings[LPC::FAQ_STT_K]) && $lpSettings[LPC::FAQ_STT_K] == 'on' ? 'checked="checked"' : ''); ?>>
                                                    <label class="custom-control-label" for="faq_status"></label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <?php echo e(Form::label('Title', __('Title'), ['class' => 'form-label'])); ?>

                                                <?php echo e(Form::text(LPC::FAQ_TTL_K, !empty($lpSettings[LPC::FAQ_TTL_K]) ? $lpSettings[LPC::FAQ_TTL_K] : null, ['class' => 'form-control', 'placeholder' => __('Enter Title')])); ?>

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
                                                <?php echo e(Form::label('Heading', __('Heading'), ['class' => 'form-label'])); ?>

                                                <?php echo e(Form::text(LPC::FAQ_HDG_K, !empty($lpSettings[LPC::FAQ_HDG_K]) ? $lpSettings[LPC::FAQ_HDG_K] : null, ['class' => 'form-control', 'placeholder' => __('Enter Heading')])); ?>

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
                                                <?php echo e(Form::label('Description', __('Description'), ['class' => 'form-label'])); ?>

                                                <?php echo e(Form::text(LPC::FAQ_DESC_K, !empty($lpSettings[LPC::FAQ_DESC_K]) ? $lpSettings[LPC::FAQ_DESC_K] : null, ['class' => 'form-control', 'placeholder' => __('Enter Description')])); ?>

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
                                    <button class="<?php echo e(VC::BT_PR_PRM10); ?>" type="submit" ><?php echo e(__('Save Changes')); ?></button>
                                </div>
                            <?php echo e(Form::close()); ?>

                        </div>
                        <div class="card">
                            <div class="card-header">
                                <div class="row align-items-center">
                                    <div class="<?php echo e(VC::CLMS9); ?>">
                                        
                                    </div>
                                    <div class="<?php echo e(VC::CLMS_JCE3); ?>">
                                        <?php
                                            $faqCreateRoute = R::FQ.'.create';
                                            $canCreateFaq   = Route::has($faqCreateRoute);
                                            $faqCreateUrl   = $canCreateFaq ? route($faqCreateRoute) : '#';
                                        ?>
                                        <a
                                            data-size="lg"
                                            data-url="<?php echo e($faqCreateUrl); ?>"
                                            data-ajax-popup="true"
                                            data-bs-toggle="tooltip"
                                            title="<?php echo e(__('Discover Feature Create')); ?>"
                                            class="btn btn-sm btn-primary <?php echo e($canCreateFaq ? '' : 'disabled'); ?>"
                                            <?php echo e($canCreateFaq ? '' : 'aria-disabled="true"'); ?>

                                        >
                                            <i class="<?php echo e(VC::TI_PLS_LG); ?>"></i>
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
                                           <?php if(Utility::isFilled($faqs)): ?>
                                            <?php
                                                $no = 1
                                            ?>
                                                <?php $__currentLoopData = $faqs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <tr>
                                                        <td><?php echo e($no++); ?></td>
                                                        <td><?php echo e(!empty($value['faq_questions']) ? $value['faq_questions'] : __('No heading available')); ?></td>
                                                        <?php
                                                            $editRoute    = R::FQ.'.edit';
                                                            $deleteRoute  = R::FQ.'.delete';
                                                            $canEditFaq   = Route::has($editRoute);
                                                            $canDeleteFaq = Route::has($deleteRoute);
                                                        ?>
                                                        <td>
                                                            <span>
                                                                <div class="action-btn <?php echo e(VC::BG_P); ?> ms-2">
                                                                    <?php if($canEditFaq): ?>
                                                                        <a href="<?php echo e(route($editRoute, $key)); ?>"
                                                                        class="<?php echo e(VC::BT_SM_CT); ?>"
                                                                        data-url="<?php echo e(route($editRoute, $key)); ?>"
                                                                        data-ajax-popup="true"
                                                                        data-title="<?php echo e(__('Edit Page')); ?>"
                                                                        data-size="lg"
                                                                        data-bs-toggle="tooltip"
                                                                        title="<?php echo e(__('Edit')); ?>"
                                                                        data-original-title="<?php echo e(__('Edit')); ?>"
                                                                        >
                                                                            <i class="<?php echo e(VC::TI_PC_WT); ?>"></i>
                                                                        </a>
                                                                    <?php else: ?>
                                                                        <a href="#"
                                                                        class="<?php echo e(VC::BT_SM_CT_DSB); ?>"
                                                                        aria-disabled="true"
                                                                        data-bs-toggle="tooltip"
                                                                        title="<?php echo e(__('Edit')); ?>"
                                                                        >
                                                                            <i class="<?php echo e(VC::TI_PC_WT); ?>"></i>
                                                                        </a>
                                                                    <?php endif; ?>
                                                                </div>
                                                                <div class="<?php echo e(VC::ACT_BTN_DNG_2); ?>">
                                                                    <?php if($canDeleteFaq): ?>
                                                                        <?php echo Form::open([
                                                                            'method' => 'GET',
                                                                            'route'  => [$deleteRoute, $key],
                                                                            'id'     => 'delete-form-' . $key
                                                                        ]); ?>

                                                                            <a href="#"
                                                                            class="<?php echo e(VC::BT_SM_CT_PR); ?>"
                                                                            data-bs-toggle="tooltip"
                                                                            title="<?php echo e(__('Delete')); ?>"
                                                                            data-original-title="<?php echo e(__('Delete')); ?>"
                                                                            data-confirm="<?php echo e(__(Utility::fetchLinkMessage($lang, 'generics', 'are_you_sure') ?? 'Are You Sure?')); ?>|<?php echo e(__(Utility::fetchLinkMessage($lang, 'generics', 'irreversible_action') ?? 'This action can not be undone. Do you want to continue?')); ?>"
                                                                            data-confirm-yes="document.getElementById('delete-form-<?php echo e($key); ?>').submit();"
                                                                            >
                                                                                <i class="<?php echo e(VC::TI_TRS_WT); ?>"></i>
                                                                            </a>
                                                                        <?php echo Form::close(); ?>

                                                                    <?php else: ?>
                                                                        <a href="#"
                                                                        class="<?php echo e(VC::BT_SM_CT_DSB); ?>"
                                                                        aria-disabled="true"
                                                                        data-bs-toggle="tooltip"
                                                                        title="<?php echo e(__('Delete')); ?>"
                                                                        >
                                                                            <i class="<?php echo e(VC::TI_TRS_WT); ?>"></i>
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


<?php echo $__env->make(ExtendingLayoutsConstants::ADM, \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/aronboliveira/Desktop/programming/Prestech/erp/erpgo-fork/erp_prestech/_inc/laravel/Modules/LandingPage/Resources/views/landingpage/faqs/index.blade.php ENDPATH**/ ?>