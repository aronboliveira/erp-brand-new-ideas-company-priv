<?php
    use App\Config\Constants\ViewClassNamesConstants;
    use Illuminate\Support\Facades\Route;
?>


<?php $__env->startSection(YieldingConstants::ADM_PG_TTL); ?>
    <?php echo e(__('Manage Client')); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startSection(YieldingConstants::ADM_BDC); ?>
    <li class="breadcrumb-item">
        <a href="<?php echo e(Route::has('dashboard') ? route('dashboard') : '#'); ?>"
           <?php echo e(Route::has('dashboard') ? '' : 'aria-disabled="true"'); ?>>
            <?php echo e(__('Dashboard')); ?>

        </a>
    </li>
    <li class="breadcrumb-item"><?php echo e(__('Client')); ?></li>
<?php $__env->stopSection(); ?>

<?php $__env->startSection(YieldingConstants::ADM_ACT_BTN); ?>
    <div class="<?php echo e(ViewClassNamesConstants::FEND); ?>">
        <a href="#"
           data-size="md"
           data-url="<?php echo e(route('clients.create')); ?>"
           data-ajax-popup="true"
           data-bs-toggle="tooltip"
           title="<?php echo e(__('Create')); ?>"
           class="<?php echo e(ViewClassNamesConstants::BT_SM_PM); ?>">
            <i class="<?php echo e(ViewClassNamesConstants::TI_PLS); ?>"></i>
        </a>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection(YieldingConstants::ADM_CTT); ?>
    <div class="<?php echo e(ViewClassNamesConstants::RW); ?>">
        <div class="col-xxl-12">
            <div class="<?php echo e(ViewClassNamesConstants::RW); ?>">
                <?php $__currentLoopData = $clients; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $client): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="<?php echo e(ViewClassNamesConstants::CM3); ?>">
                        <div class="<?php echo e(ViewClassNamesConstants::CD); ?> text-center">
                            <div class="card-header border-0 pb-0">
                                <div class="card-header-right">
                                    <div class="btn-group card-option">
                                        <button type="button" class="btn dropdown-toggle"
                                                data-bs-toggle="dropdown" aria-haspopup="true"
                                                aria-expanded="false">
                                            <i class="<?php echo e(ViewClassNamesConstants::TI_DRP); ?>"></i>
                                        </button>
                                        <div class="<?php echo e(ViewClassNamesConstants::DRP_MN_EM); ?>">
                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('edit client')): ?>
                                                <a href="#!"
                                                   data-size="md"
                                                   data-url="<?php echo e(route('clients.edit',$client->id)); ?>"
                                                   data-ajax-popup="true"
                                                   class="dropdown-item">
                                                    <i class="<?php echo e(ViewClassNamesConstants::TI_PC); ?>"></i>
                                                    <span><?php echo e(__('Edit')); ?></span>
                                                </a>
                                            <?php endif; ?>
                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('delete client')): ?>
                                                <?php echo Collective\Html\FormFacade::open([
                                                    'method' => 'DELETE',
                                                    'route'  => ['clients.destroy', $client->id],
                                                    'id'     => 'delete-form-'.$client->id
                                                ]); ?>

                                                <a href="#!"
                                                   class="dropdown-item bs-pass-para">
                                                    <i class="<?php echo e(ViewClassNamesConstants::TI_TRS); ?>"></i>
                                                    <span>
                                                        <?php if($client->delete_status!=0): ?>
                                                            <?php echo e(__('Delete')); ?>

                                                        <?php else: ?>
                                                            <?php echo e(__('Restore')); ?>

                                                        <?php endif; ?>
                                                    </span>
                                                </a>
                                                <?php echo Collective\Html\FormFacade::close(); ?>

                                            <?php endif; ?>
                                            <a href="#!"
                                               data-url="<?php echo e(route('clients.reset', Crypt::encrypt($client->id))); ?>"
                                               data-ajax-popup="true"
                                               class="dropdown-item">
                                                <i class="<?php echo e(ViewClassNamesConstants::TI_ADJ); ?>"></i>
                                                <span><?php echo e(__('Reset Password')); ?></span>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="<?php echo e(ViewClassNamesConstants::CD_MT); ?> full-card">
                                <div class="card-avatar">
                                    <img src="<?php echo e($client->avatar
                                        ? asset(Storage::url('uploads/avatar/'.$client->avatar))
                                        : asset(Storage::url('uploads/avatar/avatar.png'))); ?>"
                                         class="img-user wid-80 rounded-circle">
                                </div>
                                <h4 class="mt-2 text-primary"><?php echo e($client->name); ?></h4>
                                <div class="<?php echo e(ViewClassNamesConstants::DFL_AIC_JCB); ?>">
                                    <div class="me-4 text-primary">
                                        <?php echo e($client->email); ?>

                                    </div>
                                </div>
                                <div class="mt-2 h6" data-bs-toggle="tooltip" title="<?php echo e(__('Last Login')); ?>">
                                    <?php echo e($client->last_login_at ?? ''); ?>

                                </div>
                            </div>
                            <div class="card-footer p-3">
                                <div class="<?php echo e(ViewClassNamesConstants::DFL_JCB); ?>">
                                    <div>
                                        <h6 class="mb-0"><?php echo e($client->clientDeals->count() ?? 0); ?></h6>
                                        <p class="text-muted text-sm mb-0"><?php echo e(__('Deals')); ?></p>
                                    </div>
                                    <div>
                                        <h6 class="mb-0"><?php echo e($client->clientProjects->count() ?? 0); ?></h6>
                                        <p class="text-muted text-sm mb-0"><?php echo e(__('Projects')); ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make(ExtendingLayoutsConstants::ADM, \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\Aron\Desktop\programming\Prestech\erp\erpgo-fork\erp_prestech\_inc\laravel\resources\views/clients/index.blade.php ENDPATH**/ ?>