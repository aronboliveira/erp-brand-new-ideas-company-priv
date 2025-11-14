<?php
	use App\Config\Constants\SettingsConstants;
?>
<?php if(!empty($colorSettings[SettingsConstants::CST_DRK]) 
	&& $colorSettings[SettingsConstants::CST_DRK] === 'on' 
	&& is_file(asset('assets/css/style-dark.css'))): ?>
	<link rel="stylesheet" href="<?php echo e(asset('assets/css/style-dark.css')); ?>" id="main-style-link">
<?php else: ?>
	<link rel="stylesheet" href="<?php echo e(asset('assets/css/style.css')); ?>" id="main-style-link">
<?php endif; ?>
<link rel="stylesheet" href="<?php echo e(asset('assets/css/plugins/style.css')); ?>">
<link rel="stylesheet" href="<?php echo e(asset('assets/css/plugins/main.css')); ?>">
<link rel="stylesheet" href="<?php echo e(asset('assets/css/plugins/animate.min.css')); ?>">
<link rel="stylesheet" href="<?php echo e(asset('assets/fonts/tabler-icons.min.css')); ?>">
<link rel="stylesheet" href="<?php echo e(asset('assets/fonts/feather.css')); ?>">
<link rel="stylesheet" href="<?php echo e(asset('assets/fonts/fontawesome.css')); ?>">
<link rel="stylesheet" href="<?php echo e(asset('assets/fonts/material.css')); ?>">
<link rel="stylesheet" href="<?php echo e(asset('assets/css/customizer.css')); ?>">
<link rel="stylesheet" href="<?php echo e(asset('css/custom.css')); ?>" id="custom-style-link">
<link
  href="https://cdn.jsdelivr.net/npm/bootstrap@5.x/dist/css/bootstrap.min.css"
  rel="stylesheet">
<?php if($colorSettings[SettingsConstants::CST_DRK] == 'on' && is_file(asset('css/custom-dark.css'))): ?>
	<link rel="stylesheet" href="<?php echo e(asset('css/custom-dark.css')); ?>" id="custom-dark-style-link">
<?php endif; ?><?php /**PATH /home/aronboliveira/Desktop/programming/Prestech/erp/erpgo-fork/erp_prestech/_inc/laravel/resources/views/fragments/stylesheets.blade.php ENDPATH**/ ?>