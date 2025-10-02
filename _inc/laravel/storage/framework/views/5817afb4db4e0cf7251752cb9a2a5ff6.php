<?php
    use App\Config\Constants\ViewClassNamesConstants;
    use Illuminate\Support\Facades\Route;
    use Modules\LandingPage\Config\Constants\RoutesResourcesConstants as R;
    $menuItems = [
        [R::LP,      __('Top Bar')],
        [R::CT_PG,   __('Custom Page')],
        [R::HM,      __('Home')],
        [R::FT,      __('Features')],
        [R::DV,      __('Discover')],
        [R::SST,     __('Screenshots')],
        [R::PRC_PLN, __('Pricing Plan')],
        [R::FQ,      __('FAQ')],
        [R::TTMN,    __('Testimonials')],
        [R::JU,      __('Join Us')],
    ];
?>
<div class="list-group">
    <?php $__currentLoopData = $menuItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as [$prefix, $label]): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php
            $routeName = "{$prefix}.index";
            $exists    = Route::has($routeName);
            $url       = $exists ? route($routeName) : '#';
            $isActive  = $exists && Route::currentRouteNamed($routeName);
        ?>
        <a href="<?php echo e($url); ?>"
           class="<?php echo e(ViewClassNamesConstants::LGI_ACT_NBD); ?><?php echo e($isActive ? ' active' : ''); ?>"
           <?php echo e($exists ? '' : 'aria-disabled="true"'); ?>>
            <?php echo e($label); ?>

            <div class="float-end">
                <i class="<?php echo e(ViewClassNamesConstants::TI_CHV_RT); ?>"></i>
            </div>
        </a>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>
<script>
    console.log(
        'Current route:',
        '<?php echo e(Route::currentRouteName() ?? Route::currentRouteAction()); ?>'
    );
</script>
<?php /**PATH /home/aronboliveira/Desktop/programming/Prestech/erp/erpgo-fork/erp_prestech/_inc/laravel/Modules/LandingPage/Resources/views/layouts/tab.blade.php ENDPATH**/ ?>