<title><?php echo e(config('chatify.name') ?: __('No application name available')); ?></title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="route" content="<?php echo e((string) ($route ?? __('No route available'))); ?>">
<meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
<link href="<?php echo e(asset('css/chatify/style.css')); ?>" rel="stylesheet"/>
<link href="<?php echo e(asset('css/chatify/'.(($dark_mode ?? 'light')).'.mode.css')); ?>" rel="stylesheet"/>
<?php echo $__env->make('Chatify::layouts.messenger_color', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php /**PATH /home/aronboliveira/Desktop/programming/Prestech/erp/erpgo-fork/erp_prestech/_inc/laravel/resources/views/vendor/Chatify/layouts/head_links.blade.php ENDPATH**/ ?>