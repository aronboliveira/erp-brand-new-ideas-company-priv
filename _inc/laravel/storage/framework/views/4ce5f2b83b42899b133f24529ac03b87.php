<?php
    $ogTitle = ! empty($meta_title) ? $meta_title : 'ERP';
    $ogDesc = ! empty($meta_desc)  ? $meta_desc  : 'Your personalized ERP, made for businesses success!';
    $ogImg  = ! empty($meta_image) ? $meta_image : $meta_logo;
?>
<meta property="og:type"        content="website">
<meta property="og:url"         content="<?php echo e(env('APP_URL')); ?>">
<meta property="og:title"       content="<?php echo e($ogTitle); ?>">
<meta property="og:description" content="<?php echo e($ogDesc); ?>">
<meta property="og:image"       content="<?php echo e($ogImg); ?>">
<?php /**PATH C:\Users\Aron\Desktop\programming\Prestech\erp\erpgo-fork\erp_prestech\_inc\laravel\resources\views/fragments/og.blade.php ENDPATH**/ ?>