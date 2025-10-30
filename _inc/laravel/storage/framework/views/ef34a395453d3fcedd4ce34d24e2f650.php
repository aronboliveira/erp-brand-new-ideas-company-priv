<?php
	use App\Config\Constants\{ExtendingLayoutsConstants,StacksConstants,ViewClassNamesConstants as VC, ViewsConstants as VW,YieldingConstants};
	use App\Models\Utility;
    use Collective\Html\FormFacade as Form;
	use Illuminate\Support\Facades\{Log,Route};
    use Illuminate\Support\Str;
	use Modules\LandingPage\Config\Constants\{ExtendingLandingPageLayoutConstants as E,RoutesResourcesConstants as R,SettingsConstants as LPC};
    use Nwidart\Modules\Facades\Module;
    
    $lang = Utility::fetchUserLang();
	$lpSettings ??= [];
	$logo ??= '';
	try {
		$lpSettings=\Modules\LandingPage\Entities\LandingPageSetting
			::landingPageSetting()?:[];
		$logo=Utility::getFile('uploads/landing_page_image')?:'';
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

<?php $__env->startSection('content'); ?>
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
                            <div class="card-header">
                                <div class="row">
                                    <div class="<?php echo e(VC::CLMS10); ?>">
                                        <h5><?php echo e(__('Feature')); ?></h5>
                                    </div>
                                </div>
                            </div>
                            <?php echo e(Form::open(array('route' => VW::FT.'.store', 'method'=>'post', 'enctype' => "multipart/form-data"))); ?>

                                <?php echo csrf_field(); ?>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <?php echo e(Form::label('Title', __('Title'), ['class' => 'form-label'])); ?>

                                                <?php echo e(Form::text(LPC::FT_TTL_K,!empty($lpSettings[LPC::FT_TTL_K]) ? $lpSettings[LPC::FT_TTL_K] : null, ['class' => 'form-control', 'placeholder' => __('Enter Title')])); ?>

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

                                                <?php echo e(Form::text('feature_heading',!empty($lpSettings['feature_heading']) ? $lpSettings['feature_heading'] : null, ['class' => 'form-control', 'placeholder' => __('Enter Heading')])); ?>

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

                                                <?php echo e(Form::text('feature_description', !empty($lpSettings['feature_description']) ? $lpSettings['feature_description'] : null, ['class' => 'form-control', 'placeholder' => __('Enter Description')])); ?>

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
                                                <?php echo e(Form::label('Buy Now Link', __('Buy Now Link'), ['class' => 'form-label'])); ?>

                                                <?php echo e(Form::text('feature_buy_now_link', !empty($lpSettings['feature_buy_now_link']) ? $lpSettings['feature_buy_now_link'] : null, ['class' => 'form-control', 'placeholder' => __('Enter Link')])); ?>

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
                                            $createFeatureRoute = VW::FT.'.create';
                                            $canCreateFeature   = Route::has($createFeatureRoute);
                                        ?>
                                        <a
                                            data-size="lg"
                                            data-url="<?php echo e($canCreateFeature ? route($createFeatureRoute) : '#'); ?>"
                                            data-ajax-popup="true"
                                            data-bs-toggle="tooltip"
                                            title="<?php echo e(__('Create')); ?>"
                                            class="<?php echo e(VC::BT_SM_PM); ?> <?php echo e($canCreateFeature ? '' : 'disabled'); ?>"
                                            <?php echo e($canCreateFeature ? '' : 'aria-disabled="true"'); ?>

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
                                           <?php if(Utility::isFilled($feature_of_features ?? [])): ?>
                                                <?php
                                                    $ff_no = 1;
                                                    Log::info($feature_of_features);
                                                ?>
                                                <?php $__currentLoopData = $feature_of_features; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <tr>
                                                        <td><?php echo e($ff_no++); ?></td>
                                                        <td><?php echo e(!empty($value['feature_heading']) ? $value['feature_heading'] : __('No heading available for feature')); ?></td>
                                                        <td>
                                                            <span>
                                                                <div class="action-btn <?php echo e(VC::BG_P); ?> ms-2">
                                                                    <?php if(Route::has(VW::FT.'.edit')): ?>
                                                                        <a href="#"
                                                                           class="<?php echo e(VC::BT_SM_CT); ?>"
                                                                           data-url="<?php echo e(route(VW::FT.'.edit', $key)); ?>"
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
                                                                    <?php if(Route::has(VW::FT.'.destroy')): ?>
                                                                        <?php echo Form::open([
                                                                            'method' => 'GET',
                                                                            'route'  => [VW::FT.'.destroy', $key],
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
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="3">
                                                        <div class="text-center">
                                                            <?php echo e(__('No data available for features')); ?>

                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="card">
                            <div class="card-header">
                                <div class="row">
                                    <div class="<?php echo e(VC::CLMS10); ?>">
                                        <h5><?php echo e(__('Feature')); ?></h5>
                                    </div>
                                </div>
                            </div>
                            <?php
                                $lang = Utility::fetchUserLang();

                                $logo = Utility::getFile('uploads/logo');
                                $lp = is_array($lpSettings ?? null) ? $lpSettings : [];

                                $heading = $lp['highlight_feature_heading'] ?? __('No heading available for feature highlight');
                                $desc    = $lp['highlight_feature_description'] ?? __('No description available for feature highlight');
                                $img     = $lp['highlight_feature_image'] ?? null;

                                $storeBase     = R::FT . '.highlight.store';
                                $storeKebab    = Str::kebab($storeBase);
                                $storeResolved = Route::has($storeBase) ? $storeBase : (Route::has($storeKebab) ? $storeKebab : null);
                                $storeUrl      = $storeResolved ? route($storeResolved) : '#';
                                $storeGuard    = Utility::fetchLinkMessage($lang, R::FT, 'highlight_store_route_unavailable')
                                                ?? __('Store Highlight Feature route is unavailable. Please contact technical support or your domain administrator.');
                            ?>
                            <?php echo e(Form::open([
                                'url'               => $storeUrl,
                                'method'            => 'post',
                                'enctype'           => 'multipart/form-data',
                                'id'                => 'highlight-feature-store-form',
                                'data-url'          => $storeUrl,
                                'data-guard-msg'    => $storeGuard,
                                'data-sv-localized' => 'true'
                            ])); ?>

                                <?php echo csrf_field(); ?>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="<?php echo e(VC::CM6); ?>">
                                            <div class="form-group">
                                                <?php echo e(Form::label('highlight_feature_heading', __('Heading'), ['class' => 'form-label'])); ?>

                                                <?php echo e(Form::text('highlight_feature_heading', $heading, ['class' => 'form-control', 'placeholder' => __('Enter Link')])); ?>

                                                <?php $__errorArgs = ['highlight_feature_heading'];
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
                                        <div class="<?php echo e(VC::CM6); ?>">
                                            <div class="form-group">
                                                <?php echo e(Form::label('highlight_feature_description', __('Description'), ['class' => 'form-label'])); ?>

                                                <?php echo e(Form::text('highlight_feature_description', $desc, ['class' => 'form-control', 'placeholder' => __('Enter Link')])); ?>

                                                <?php $__errorArgs = ['highlight_feature_description'];
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
                                        <div class="<?php echo e(VC::CM6); ?>">
                                            <div class="form-group">
                                                <?php echo e(Form::label('Logo', __('Logo'), ['class' => 'form-label'])); ?>

                                                <div class="logo-content mt-2">
                                                    <img id="image1" src="<?php echo e($img ? asset($logo . '/' . $img) : asset("assets/images/logo-light.webp")); ?>" class="big-logo img_setting" alt="<?php echo e(__('Feature Highlight Image')); ?>" 
                                                    onerror='this.src="<?php echo e(asset("assets/images/logo-light.webp")); ?>"'/>
                                                </div>
                                                <div class="choose-files mt-4">
                                                    <label for="highlight_feature_image">
                                                        <div class="<?php echo e(VC::BG_P); ?> dark_logo_update" style="cursor:pointer;">
                                                            <i class="ti ti-upload px-1"></i><?php echo e(__('Choose file here')); ?>

                                                        </div>
                                                        <input type="file" name="highlight_feature_image" id="highlight_feature_image" class="form-control file" data-filename="highlight_feature_image">
                                                    </label>
                                                </div>
                                                <?php $__errorArgs = ['highlight_feature_image'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                                <div class="row">
                                                    <span class="invalid-logo" role="alert">
                                                        <strong class="text-danger"><?php echo e($message); ?></strong>
                                                    </span>
                                                </div>
                                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer text-end">
                                    <input class="<?php echo e(VC::BT_PR_PRM10); ?>" type="submit" value="<?php echo e(__('Save Changes')); ?>">
                                </div>
                                <script defer src="<?php echo e(asset('assets/js/routes/features/highlight/store.js')); ?>"></script>
                            <?php echo e(Form::close()); ?>

                        </div>
                        <div class="card">
                            <div class="card-header">
                                <div class="row align-items-center">
                                    <div class="<?php echo e(VC::CLMS9); ?>">
                                        
                                    </div>
                                    <div class="<?php echo e(VC::CLMS_JCE3); ?>">
                                        <?php 
                                            $createFeatureRoute = VW::FT.'.create';
                                            $canCreateFeature   = Route::has($createFeatureRoute);
                                            $createFeatureUrl   = $canCreateFeature 
                                                ? route($createFeatureRoute) 
                                                : '#';
                                        ?>
                                        <a
                                            data-size="lg"
                                            data-url="<?php echo e($createFeatureUrl); ?>"
                                            data-ajax-popup="true"
                                            data-bs-toggle="tooltip"
                                            title="<?php echo e(__('Create')); ?>"
                                            class="<?php echo e(VC::BT_SM_PM); ?> <?php echo e($canCreateFeature ? '' : 'disabled'); ?>"
                                            <?php echo e($canCreateFeature ? '' : 'aria-disabled="true"'); ?>

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
                                            <?php if(Utility::isFilled($other_features ?? [])): ?>
                                                <?php
                                                    $of_no = 1;
                                                    Log::info('Other Features data:');
                                                    Log::info($other_features);
                                                ?>
                                                <?php $__currentLoopData = $other_features; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <tr>
                                                        <td><?php echo e($of_no++); ?></td>
                                                        <td><?php echo e($value['other_features_heading']); ?></td>
                                                        <?php
                                                            $editRoute    = VW::FT.'.edit';
                                                            $deleteRoute  = VW::FT.'.delete';
                                                            $canEdit      = Route::has($editRoute);
                                                            $canDelete    = Route::has($deleteRoute);
                                                            $editUrl      = $canEdit   ? route($editRoute,   $key) : '#';
                                                        ?>
                                                        <td>
                                                            <span>
                                                                <div class="action-btn <?php echo e(VC::BG_P); ?> ms-2">
                                                                    <a
                                                                        href="<?php echo e($editUrl); ?>"
                                                                        class="<?php echo e(VC::BT_SM_CT); ?> <?php echo e($canEdit ? '' : 'disabled'); ?>"
                                                                        <?php echo e($canEdit ? "data-url={$editUrl}" : 'aria-disabled="true"'); ?>

                                                                        data-ajax-popup="true"
                                                                        data-title="<?php echo e(__('Edit Page')); ?>"
                                                                        data-size="lg"
                                                                        data-bs-toggle="tooltip"
                                                                        title="<?php echo e(__('Edit')); ?>"
                                                                        data-original-title="<?php echo e(__('Edit')); ?>"
                                                                    >
                                                                        <i class="<?php echo e(VC::TI_PC_WT); ?>"></i>
                                                                    </a>
                                                                </div>
                                                                <div class="<?php echo e(VC::ACT_BTN_DNG_2); ?>">
                                                                    <?php if($canDelete): ?>
                                                                        <?php echo Form::open([
                                                                            'method' => 'GET',
                                                                            'route'  => [$deleteRoute, $key],
                                                                            'id'     => 'delete-form-' . $key
                                                                        ]); ?>

                                                                            <a
                                                                                href="#"
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
                                                                        <a
                                                                            href="#"
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
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="3">
                                                        <div class="text-center">
                                                            <?php echo e(__('No data available for other features')); ?>

                                                        </div>
                                                    </td>
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
<?php $__env->stopSection(); ?>


<?php echo $__env->make(ExtendingLayoutsConstants::ADM, \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/aronboliveira/Desktop/programming/Prestech/erp/erpgo-fork/erp_prestech/_inc/laravel/Modules/LandingPage/Resources/views/landingpage/features/index.blade.php ENDPATH**/ ?>