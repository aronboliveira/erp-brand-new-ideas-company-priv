<?php
	use App\Config\Constants\{ExtendingLayoutsConstants,StacksConstants,ViewClassNamesConstants,ViewsConstants,YieldingConstants};
	use App\Models\Utility;
	use Illuminate\Support\Facades\{File,Log,Route};
	use Modules\LandingPage\Config\Constants\{ExtendingLandingPageLayoutConstants as E,RoutesResourcesConstants as R,SettingsConstants as LandingPageSettingsConstants};
    use Nwidart\Modules\Facades\Module;
    Log::debug('Loaded settings for Menubar blade...');
	$lpSettings ??= [];
	$logo ??= '';
	try {
		$lpSettings=\Modules\LandingPage\Entities\LandingPageSetting::landingPageSetting()?:[];
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
    Log::debug('Sucessfully loaded settings for Menubar blade');
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
<?php $__env->startPush(StacksConstants::ADM_CSS); ?>
    <link rel="stylesheet" href=" <?php echo e(asset('Modules/landingpage/css/summernote/summernote-bs4.css')); ?>" />
<?php $__env->stopPush(); ?>

<?php $__env->startPush(StacksConstants::ADM_SCR_PG); ?>
    <script>
        document.getElementById('site_logo').onchange = function () {
                var src = URL.createObjectURL(this.files[0])
                document.getElementById('image').src = src
            }
    </script>
    <script src="<?php echo e(asset('Modules/landingpage/js/plugins/summernote-bs4.js')); ?>" referrerpolicy="origin"></script>
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
                                    <h5><?php echo e(__('Custom Page')); ?></h5>
                                </div>
                            </div>
                        </div>
                        <?php echo e(Collective\Html\FormFacade::open(array('route' => 'custom_pages.store', 'method'=>'post', 'enctype' => "multipart/form-data"))); ?>

                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <?php echo e(Collective\Html\FormFacade::label('Site Logo', __('Site Logo'), ['class' => 'form-label'])); ?>

                                            <div class="logo-content mt-4">
                                                <img
                                                    id="image"
                                                    src="<?php echo e(File::exists($logo.'/'.$lpSettings['site_logo']) ? $logo.'/'.$lpSettings['site_logo'] : asset('assets/images/logo-light.webp')); ?>"
                                                    height="60px"
                                                    fetchpriority="auto"
                                                    decoding="async"
                                                    loading="lazy"
                                                />
                                            </div>
                                            <div class="choose-files mt-5">
                                                <label for="site_logo">
                                                    <div class="<?php echo e(ViewClassNamesConstants::BG_P); ?> company_logo_update" style="cursor: pointer;">
                                                        <i class="ti ti-upload px-1"></i><?php echo e(__('Choose file here')); ?>

                                                    </div>
                                                    <input type="file" name="site_logo" id="site_logo" class="form-control file" data-filename="site_logo">
                                                </label>
                                            </div>
                                            <?php $__errorArgs = ['site_logo'];
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
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <?php echo e(Collective\Html\FormFacade::label('Site Description', __('Site Description'), ['class' => 'form-label'])); ?>

                                            <?php echo e(Collective\Html\FormFacade::text(LandingPageSettingsConstants::SD_K, $lpSettings[LandingPageSettingsConstants::SD_K], ['class' => 'form-control', 'placeholder' => __('Enter Description')])); ?>

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
                        <div class="card">
                            <div class="card-header">
                                <div class="row align-items-center">
                                    <div class="<?php echo e(ViewClassNamesConstants::CLMS9); ?>">
                                        <h5><?php echo e(__('Menu Bar')); ?></h5>
                                    </div>
                                    <div class="<?php echo e(ViewClassNamesConstants::CLMS_JCE3); ?>">
                                        <?php
                                            Log::debug('Loading creation route for custom pages...');
                                            $createRoute     = R::CT_PG . '.create';
                                            $canCreate       = Route::has($createRoute);
                                            Log::debug('Successfully loaded creation route for custom pages',
                                                [
                                                    'create_route' => $createRoute,
                                                ]);
                                        ?>
                                        <a
                                            data-size="lg"
                                            data-url="<?php echo e($canCreate ? route($createRoute) : '#'); ?>"
                                            data-ajax-popup="true"
                                            data-bs-toggle="tooltip"
                                            title="<?php echo e(__('Create')); ?>"
                                            class="btn btn-sm btn-primary <?php echo e($canCreate ? '' : 'disabled'); ?>"
                                            <?php echo e($canCreate ? '' : 'aria-disabled="true"'); ?>

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
                                            <?php if(is_array($pages) || is_object($pages)): ?>
                                                <?php
                                                  $no = 1
                                                ?>
                                                <?php $__currentLoopData = $pages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <tr>
                                                        <td><?php echo e($no++); ?></td>
                                                        <td><?php echo e($value[LandingPageSettingsConstants::MB_PG_NM]); ?></td>
                                                        <td>
                                                            <?php
                                                                Log::debug('Loading routes for stateful routes for custom pages...');
                                                                $editRoute     = R::CT_PG . '.edit';
                                                                $destroyRoute  = R::CT_PG . '.destroy';
                                                                $slug          = $value[LandingPageSettingsConstants::PG_SLG] ?? '';
                                                                $canEdit       = Route::has($editRoute);
                                                                $canDestroy    = Route::has($destroyRoute)
                                                                                && ! in_array($slug, ['terms_and_conditions','about_us','privacy_policy']);
                                                                Log::debug('Successfully loaded stateful routes for custom pages',
                                                                    [
                                                                        'edit_route' => $editRoute,
                                                                        'destroy_route' => $destroyRoute,
                                                                    ]);
                                                            ?>
                                                            <span>
                                                                <div class="action-btn <?php echo e(ViewClassNamesConstants::BG_P); ?> ms-2">
                                                                    <?php if($canEdit): ?>
                                                                        <a href="#"
                                                                        class="mx-3 btn btn-sm align-items-center"
                                                                        data-url="<?php echo e(route($editRoute, $key)); ?>"
                                                                        data-ajax-popup="true"
                                                                        data-title="<?php echo e(__('Edit Page')); ?>"
                                                                        data-size="lg"
                                                                        data-bs-toggle="tooltip"
                                                                        title="<?php echo e(__('Edit')); ?>"
                                                                        data-original-title="<?php echo e(__('Edit')); ?>">
                                                                            <i class="<?php echo e(ViewClassNamesConstants::TI_PC_WT); ?>"></i>
                                                                        </a>
                                                                    <?php else: ?>
                                                                        <a href="#"
                                                                        class="<?php echo e(ViewClassNamesConstants::BT_SM_CT_DSB); ?>"
                                                                        aria-disabled="true"
                                                                        data-bs-toggle="tooltip"
                                                                        title="<?php echo e(__('Edit')); ?>">
                                                                            <i class="<?php echo e(ViewClassNamesConstants::TI_PC_WT); ?>"></i>
                                                                        </a>
                                                                    <?php endif; ?>
                                                                </div>
                                                                <div class="<?php echo e(ViewClassNamesConstants::ACT_BTN_DNG_2); ?>">
                                                                    <?php if($canDestroy): ?>
                                                                        <?php echo Collective\Html\FormFacade::open([
                                                                            'method' => 'DELETE',
                                                                            'route'  => [$destroyRoute, $key],
                                                                            'id'     => 'delete-form-' . $key
                                                                        ]); ?>

                                                                            <a href="#"
                                                                            class="<?php echo e(ViewClassNamesConstants::BT_SM_CT_PR); ?>"
                                                                            data-bs-toggle="tooltip"
                                                                            title="<?php echo e(__('Delete')); ?>"
                                                                            data-original-title="<?php echo e(__('Delete')); ?>"
                                                                             data-confirm="<?php echo e(__(Utility::fetchLinkMessage($lang, 'generics', 'are_you_sure') ?? 'Are You Sure?')); ?>|<?php echo e(__(Utility::fetchLinkMessage($lang, 'generics', 'irreversible_action') ?? 'This action can not be undone. Do you want to continue?')); ?>"
                                                                            data-confirm-yes="document.getElementById('delete-form-<?php echo e($key); ?>').submit();">
                                                                                <i class="ti ti-trash text-white"></i>
                                                                            </a>
                                                                        <?php echo Collective\Html\FormFacade::close(); ?>

                                                                    <?php else: ?>
                                                                        <a href="#"
                                                                        class="<?php echo e(ViewClassNamesConstants::BT_SM_CT_DSB); ?>"
                                                                        aria-disabled="true"
                                                                        data-bs-toggle="tooltip"
                                                                        title="<?php echo e(__('Delete')); ?>">
                                                                            <i class="ti ti-trash text-white"></i>
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
                                    <script>
                                        (function(){
                                          const img = document.getElementById('image');
                                          if (!img || img.hasAttribute('data-reloading-active')) return;
                                          img.setAttribute('data-reloading-active', 'true');
                                          const fallbacks = [
                                            'public/assets/logo-light.webp',
                                            'public/assets/logo-light.png',
                                            'public/assets/logo-light.jpg',
                                            'public/assets/logo-light.jpeg',
                                            'public/assets/images/logo-light.webp',
                                            'public/assets/images/logo-light.png',
                                            'public/assets/images/logo-light.jpg',
                                            'public/assets/images/logo-light.jpeg',
                                            'public/logo-light.webp',
                                            'public/logo-light.png',
                                            'public/logo-light.jpg',
                                            'public/logo-light.jpeg'
                                          ];
                                          img.setAttribute('data-reload-attempt', img.getAttribute('data-reload-attempt') || '0');
                                          img.addEventListener('error', function() {
                                            let attempt = parseInt(this.getAttribute('data-reload-attempt'), 10);
                                            if (!Number.isFinite(attempt) || attempt < 0) attempt = 0;
                                            if (attempt === 0) {
                                              this.setAttribute('data-original-opacity', getComputedStyle(this).opacity || '1');
                                              this.style.transition = (this.style.transition || '') + 'opacity 0.25s ease-in-out';
                                              this.style.opacity = '0';
                                            }
                                            if (attempt >= fallbacks.length) {
                                              this.style.opacity = this.getAttribute('data-original-opacity') || '1';
                                              this.removeAttribute('data-reload-attempt');
                                              this.removeAttribute('data-original-opacity');
                                            } else {
                                              this.setAttribute('data-reload-attempt', String(attempt + 1));
                                              this.src = window.location.origin + '/' + fallbacks[attempt];
                                            }
                                          });
                                          img.addEventListener('load', function() {
                                            this.style.opacity = this.getAttribute('data-original-opacity') || '1';
                                            this.removeAttribute('data-reload-attempt');
                                            this.removeAttribute('data-original-opacity');
                                          });
                                        })();
                                    </script>
                                </div>
                            </div>
                        </div>
                    
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>


<?php echo $__env->make(ExtendingLayoutsConstants::ADM, \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/aronboliveira/Desktop/programming/Prestech/erp/erpgo-fork/erp_prestech/_inc/laravel/Modules/LandingPage/Resources/views/landingpage/menubar/index.blade.php ENDPATH**/ ?>