<?php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        StacksConstants,
        ViewClassNamesConstants as VC,
        ViewsConstants as VW,
        YieldingConstants
    };
    use App\Models\Utility;
    use Collective\Html\FormFacade as Form;
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\{Collection, Str};

    $profile = Utility::getFile('uploads/avatar');
    $user   = auth()->user();
    $lang = Utility::fetchUserLang();

    $dashBase  = 'dashboard';
    $dashKebab = Str::kebab($dashBase);
    $dashName  = Route::has($dashBase) ? $dashBase : (Route::has($dashKebab) ? $dashKebab : null);
    $dashUrl   = $dashName ? route($dashName) : '#';

    $accBase   = VW::USR . '.account.update';
    $accKebab  = Str::kebab($accBase);
    $accName   = Route::has($accBase) ? $accBase : (Route::has($accKebab) ? $accKebab : null);
    $accUrl    = $accName ? route($accName) : '#';
    $accGuard  = Utility::fetchLinkMessage($lang, VW::USR, 'update_account_route_unavailable')
                 ?? 'Update account route is unavailable. Please contact technical support or your domain administrator.';
    $accFormId = 'profile-account-update-form';

    $pwdBase   = VW::USR . '.password.update';
    $pwdKebab  = Str::kebab($pwdBase);
    $pwdName   = Route::has($pwdBase) ? $pwdBase : (Route::has($pwdKebab) ? $pwdKebab : null);
    $pwdUrl    = $pwdName ? route($pwdName, [$user?->id]) : '#';
    $pwdGuard  = Utility::fetchLinkMessage($lang, VW::USR, 'update_password_route_unavailable')
                 ?? 'Update password route is unavailable. Please contact technical support or your domain administrator.';
    $pwdFormId = 'profile-password-update-form';
?>



<?php $__env->startSection(YieldingConstants::ADM_PG_TTL); ?>
    <?php echo e(__('Profile Account')); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startPush(StacksConstants::ADM_SCR_PG); ?>
    <script async src="<?php echo e(asset('assets/js/routes/users/profiles/lang/scroll.js')); ?>"></script>
    <script async src="<?php echo e(asset('assets/js/routes/users/profiles/scroll.js')); ?>"></script>
    <script defer src="<?php echo e(asset('assets/js/routes/users/profiles/updateAccount.js')); ?>" id="profile-account-update-script"></script>
    <script defer src="<?php echo e(asset('assets/js/routes/users/profiles/updatePassword.js')); ?>" id="profile-password-update-script"></script>
<?php $__env->stopPush(); ?>

<?php $__env->startSection(YieldingConstants::ADM_BDC); ?>
    <li class="breadcrumb-item">
        <a href="<?php echo e($dashUrl); ?>" <?php echo e($dashUrl === '#' ? 'aria-disabled=true' : ''); ?>>
            <?php echo e(__('Dashboard')); ?>

        </a>
    </li>
    <li class="breadcrumb-item"><?php echo e(__('Profile')); ?></li>
<?php $__env->stopSection(); ?>

<?php $__env->startSection(YieldingConstants::ADM_CTT); ?>
    <div class="<?php echo e(VC::RW); ?>">
        <div class="<?php echo e(VC::CXL3); ?>">
            <?php
                $sections = [
                    ['id' => 'personal_info',   'label' => __('Personal Info')],
                    ['id' => 'change_password', 'label' => __('Change Password')],
                ];
            ?>
            <div class="<?php echo e(VC::CD_STK); ?>" style="top:30px">
                <div class="<?php echo e(VC::LG_FLSH); ?>" id="useradd-sidenav">
                    <?php $__currentLoopData = ($sections ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $section): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <a href="#<?php echo e(data_get($section, 'id', '')); ?>" class="<?php echo e(VC::LGI_ACT_NBD); ?>">
                            <?php echo e(data_get($section, 'label') ?: __('No section label available')); ?>

                            <div class="<?php echo e(VC::FEND); ?>">
                                <i class="<?php echo e(VC::TI_CHV_RT); ?>"></i>
                            </div>
                        </a>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
        </div>

        <div class="col-xl-9 <?php echo e(VC::CLMS9); ?>">
            <div id="personal_info" class="<?php echo e(VC::CD); ?>">
                <div class="card-header">
                    <h5><?php echo e(__('Personal Info')); ?></h5>
                </div>
                <div class="card-body">
                    <?php echo Form::model(
                        $userDetail ?? null,
                        [
                            'url'                  => $accUrl,
                            'method'               => 'POST',
                            'enctype'              => 'multipart/form-data',
                            'id'                   => $accFormId,
                            'data-resolved-action' => $accUrl,
                            'data-guard-msg'       => $accGuard,
                            'data-sv-localized'    => 'true',
                        ]
                    ); ?>

                        <?php echo csrf_field(); ?>
                        <div class="<?php echo e(VC::RW); ?>">
                            <div class="<?php echo e(VC::CLM6); ?>">
                                <div class="form-group">
                                    <label class="col-form-label text-dark"><?php echo e(__('Name')); ?></label>
                                    <input class="form-control"
                                           name="name"
                                           type="text"
                                           id="name"
                                           placeholder="<?php echo e(__('Enter Your Name')); ?>"
                                           value="<?php echo e(old('name', (string) data_get($userDetail, 'name', ''))); ?>"
                                           required
                                           autocomplete="name">
                                </div>
                            </div>
                            <div class="<?php echo e(VC::CLM6); ?>">
                                <div class="form-group">
                                    <label for="email" class="col-form-label text-dark"><?php echo e(__('Email')); ?></label>
                                    <input class="form-control"
                                           name="email"
                                           type="text"
                                           id="email"
                                           placeholder="<?php echo e(__('Enter Your Email Address')); ?>"
                                           value="<?php echo e(old('email', (string) data_get($userDetail, 'email', ''))); ?>"
                                           required
                                           autocomplete="email">
                                </div>
                            </div>
                            <div class="<?php echo e(VC::CLM6); ?>">
                                <div class="form-group">
                                    <div class="choose-files">
                                        <label for="avatar">
                                            <div class="<?php echo e(VC::BG_P); ?> profile_update">
                                                <i class="ti ti-upload <?php echo e(VC::PX3); ?>" style="padding-left: 0 !important;"></i><?php echo e(__('Choose file here')); ?>

                                            </div>
                                            <input type="file"
                                                   class="form-control file"
                                                   name="profile"
                                                   id="avatar"
                                                   data-filename="profile_update">
                                        </label>
                                    </div>
                                    <span class="<?php echo e(VC::TXS); ?> <?php echo e(VC::TXT_MT); ?>">
                                        <?php echo e(__('Please upload a valid image file. Size of image should not be more than 2MB.')); ?>

                                    </span>
                                </div>
                            </div>
                            <div class="col-lg-12 text-end">
                                <input type="submit" value="<?php echo e(__('Save Changes')); ?>" class="<?php echo e(VC::BT_PR_PRM10); ?>">
                            </div>
                        </div>
                    <?php echo Form::close(); ?>

                </div>
            </div>

            <div id="change_password" class="<?php echo e(VC::CD); ?>">
                <div class="card-header">
                    <h5><?php echo e(__('Change Password')); ?></h5>
                </div>
                <div class="card-body">
                    <?php echo Form::open([
                        'url'                  => $pwdUrl,
                        'method'               => 'POST',
                        'id'                   => $pwdFormId,
                        'data-resolved-action' => $pwdUrl,
                        'data-guard-msg'       => $pwdGuard,
                        'data-sv-localized'    => 'true',
                    ]); ?>

                        <?php echo csrf_field(); ?>
                        <div class="<?php echo e(VC::RW); ?>">
                            <div class="<?php echo e(VC::CLM6); ?> form-group">
                                <label for="old_password" class="col-form-label text-dark"><?php echo e(__('Old Password')); ?></label>
                                <input class="form-control"
                                       name="old_password"
                                       type="password"
                                       id="old_password"
                                       required
                                       autocomplete="current-password"
                                       placeholder="<?php echo e(__('Enter Old Password')); ?>">
                            </div>
                            <div class="<?php echo e(VC::CLM6); ?> form-group">
                                <label for="password" class="col-form-label text-dark"><?php echo e(__('New Password')); ?></label>
                                <input class="form-control"
                                       name="password"
                                       type="password"
                                       id="password"
                                       required
                                       autocomplete="new-password"
                                       placeholder="<?php echo e(__('Enter Your Password')); ?>">
                            </div>
                            <div class="<?php echo e(VC::CLM6); ?> form-group">
                                <label for="password_confirmation" class="col-form-label text-dark"><?php echo e(__('New Confirm Password')); ?></label>
                                <input class="form-control"
                                       name="password_confirmation"
                                       type="password"
                                       id="password_confirmation"
                                       required
                                       autocomplete="new-password"
                                       placeholder="<?php echo e(__('Enter Your Password')); ?>">
                            </div>
                            <div class="col-lg-12 text-end">
                                <input type="submit" value="<?php echo e(__('Change Password')); ?>" class="<?php echo e(VC::BT_PR_PRM10); ?>">
                            </div>
                        </div>
                    <?php echo Form::close(); ?>

                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>



                                    
                                    
                                    
        
                                
        
<?php echo $__env->make(ExtendingLayoutsConstants::ADM, \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/aronboliveira/Desktop/programming/Prestech/erp/erpgo-fork/erp_prestech/_inc/laravel/resources/views/users/profile.blade.php ENDPATH**/ ?>