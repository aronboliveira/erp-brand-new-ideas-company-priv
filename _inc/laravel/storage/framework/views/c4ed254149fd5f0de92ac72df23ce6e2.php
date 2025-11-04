<?php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        StacksConstants,
        ViewClassNamesConstants as VC,
        ViewsConstants as VW,
        YieldingConstants,
    };
    use Collective\Html\FormFacade as Form;
    use Illuminate\Support\Facades\{Auth,Route};
    $user = Auth::user();
    $lang = Utility::fetchUserLang(user:$user);
?>

<?php $__env->startSection(YieldingConstants::ADM_PG_TTL); ?>
    <?php echo e(__('Manage Deals')); ?> <?php if($pipeline): ?> - <?php echo e($pipeline->name); ?> <?php endif; ?>
<?php $__env->stopSection(); ?>
<?php $__env->startPush(StacksConstants::ADM_CSS); ?>
    <link rel="stylesheet" href="<?php echo e(asset('css/summernote/summernote-bs4.css')); ?>">
<?php $__env->stopPush(); ?>
<?php $__env->startPush(StacksConstants::ADM_SCR_PG); ?>
    <script src="<?php echo e(asset('css/summernote/summernote-bs4.js')); ?>"></script>
    <script async src="<?php echo e(asset('assets/js/routes/deals/lang/list.js')); ?>"></script>
    <script defer src="<?php echo e(asset('assets/js/routes/deals/pipelines/index.js')); ?>"></script>
<?php $__env->stopPush(); ?>
<?php $__env->startSection(YieldingConstants::ADM_BDC); ?>
    <li class="breadcrumb-item">
        <a href="<?php echo e(Route::has('dashboard') ? route('dashboard') : '#'); ?>"
        <?php echo e(Route::has('dashboard') ? '' : 'aria-disabled="true"'); ?>>
            <?php echo e(__('Dashboard')); ?>

        </a>
    </li>
    <li class="breadcrumb-item"><?php echo e(__('Lead')); ?></li>
<?php $__env->stopSection(); ?>
<?php $__env->startSection(YieldingConstants::ADM_ACT_BTN); ?>
  <?php
      $indexRoute = Route::has(VW::DL . '.index')
          ? route(VW::DL . '.index')
          : '#';
      $indexGuardMsg = Utility::fetchLinkMessage(
          $lang,
          VW::DL,
          'deals_index_route_unavailable'
      ) ?? 'Deal index route is unavailable. Please contact technical support or your domain administrator.';
      $createRoute = Route::has(VW::DL . '.create')
          ? route(VW::DL . '.create')
          : '#';
      $createGuardMsg = Utility::fetchLinkMessage(
          $lang,
          VW::DL,
          'deals_create_route_unavailable'
      ) ?? 'Deal create route is unavailable. Please contact technical support or your domain administrator.';
  ?>
  <div class="<?php echo e(VC::FEND); ?>">
      <a
          id="deal-kanban-btn"
          href="<?php echo e($indexRoute); ?>"
          data-url="<?php echo e($indexRoute); ?>"
          data-guard-msg="<?php echo e($indexGuardMsg); ?>"
          data-bs-toggle="tooltip"
          title="<?php echo e(__('Kanban View')); ?>"
          class="<?php echo e(VC::BT_SM_PM); ?>"
      >
          <i class="ti ti-layout-grid"></i>
      </a>
      <a
          id="deal-create-btn"
          href="<?php echo e($createRoute); ?>"
          data-url="<?php echo e($createRoute); ?>"
          data-guard-msg="<?php echo e($createGuardMsg); ?>"
          data-size="lg"
          data-ajax-popup="true"
          data-bs-toggle="tooltip"
          title="<?php echo e(__('Create New Deal')); ?>"
          class="<?php echo e(VC::BT_SM_PM); ?>"
      >
          <i class="<?php echo e(VC::TI_PLS); ?>"></i>
      </a>
  </div>
  <?php $__env->startPush(StacksConstants::ADM_SCR_PG); ?>
      <script defer src="<?php echo e(asset('assets/js/routes/deals/kanban.js')); ?>"></script>
      <script defer src="<?php echo e(asset('assets/js/routes/deals/createList.js')); ?>"></script>
  <?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>


<?php echo $__env->make(ExtendingLayoutsConstants::ADM, \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/aronboliveira/Desktop/programming/Prestech/erp/erpgo-fork/erp_prestech/_inc/laravel/resources/views/deals/list.blade.php ENDPATH**/ ?>