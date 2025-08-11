<?php
	use App\Config\Constants\ViewClassNamesConstants;
	use Illuminate\Support\Facades\{Log, Route};
	use Modules\LandingPage\Config\Constants\RoutesResourcesConstants as R;
	$landingItems??=[];
	$landingArea??=[];
	try {
		$landingItems=[
			R::LP      =>__('Top Bar'),
			R::CT_PG   =>__('Custom Page'),
			R::HM      =>__('Home'),
			R::FT      =>__('Features'),
			R::DV      =>__('Discover'),
			R::SST     =>__('Screenshots'),
			R::PRC_PLN =>__('Pricing Plan'),
			R::FQ      =>__('FAQ'),
			R::TTMN    =>__('Testimonials'),
			R::JU      =>__('Join Us'),
		];
		$landingArea=array_map(
			fn($key)=>$key.'.index',
			array_keys($landingItems)
		);
	} catch (\Error $e) {
		Log::error(
			'Error computing landing items',
			[
				'exception_class'=>get_class($e),
				'message'=>$e->getMessage(),
				'file'=>$e->getFile(),
				'line'=>$e->getLine(),
				'landingItems'=>$landingItems,
				'landingArea'=>$landingArea
			]
		);
	} catch (\Exception $e) {
		Log::error(
			'Exception computing landing items',
			[
				'exception_class'=>get_class($e),
				'message'=>$e->getMessage(),
				'file'=>$e->getFile(),
				'line'=>$e->getLine(),
				'landingItems'=>$landingItems,
				'landingArea'=>$landingArea
			]
		);
	} catch (\Throwable $e) {
		Log::error(
			'Throwable computing landing items',
			[
				'exception_class'=>get_class($e),
				'message'=>$e->getMessage(),
				'file'=>$e->getFile(),
				'line'=>$e->getLine(),
				'landingItems'=>$landingItems,
				'landingArea'=>$landingArea
			]
		);
	}
?>
<li class="dash-item dash-hasmenu<?php echo e(request()->routeIs(...$landingArea) ? ' active' : ''); ?>">
    <a href="javascript:void(0)" class="dash-link">
        <span class="dash-micon"><i class="ti ti-license"></i></span>
        <span class="dash-mtext"><?php echo e(__('Landing Page')); ?></span>
        <span class="dash-arrow"><i class="<?php echo e(ViewClassNamesConstants::TI_CHV_RT); ?>"></i></span>
    </a>
    <div class="dash-submenu">
        <?php $__currentLoopData = $landingItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $prefix => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php $routeName = $prefix . '.index'; ?>
            <a href="<?php echo e(Route::has($routeName) ? route($routeName) : '#'); ?>"
               class="dash-link<?php echo e(Route::has($routeName) && request()->routeIs($routeName) ? ' active' : ''); ?>"
               <?php echo e(Route::has($routeName) ? '' : 'aria-disabled="true"'); ?>>
                <?php echo e($label); ?>

            </a>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
</li>
<?php /**PATH C:\Users\Aron\Desktop\programming\Prestech\erp\erpgo-fork\erp_prestech\_inc\laravel\Modules/LandingPage\Resources/views/menu/landingpage.blade.php ENDPATH**/ ?>