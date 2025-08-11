<?php
    $twTitle = ! empty($meta_title) ? $meta_title : 'ERP';
    $twDesc = ! empty($meta_desc)  ? $meta_desc  : 'Your personalized ERP, made for businesses success!';
    $twImg  = ! empty($meta_image) ? $meta_image : $meta_logo;
?>
<meta property="twitter:card"        content="summary_large_image">
<meta property="twitter:url"         content="<?php echo e(env('APP_URL')); ?>">
<meta property="twitter:title"       content="<?php echo e($twTitle); ?>">
<meta property="twitter:description" content="<?php echo e($twDesc); ?>">
<meta property="twitter:image"       content="<?php echo e($twImg); ?>"><?php /**PATH C:\Users\Aron\Desktop\programming\Prestech\erp\erpgo-fork\erp_prestech\_inc\laravel\resources\views/fragments/x.blade.php ENDPATH**/ ?>