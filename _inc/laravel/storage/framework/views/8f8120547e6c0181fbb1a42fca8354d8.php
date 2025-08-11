<?php
    use Illuminate\Support\Facades\Auth;
    $meta_vp   = ! empty($meta_vp)
                  ? $meta_vp
                  : 'user-scalable=0, minimal-ui';
    $meta_kw   = ! empty($meta_kw)
                  ? $meta_kw
                  : 'ERP,PaaS,Prestech,B2B,AI,IA,Helpdesk,Suporte';
    $meta_url  = ! empty($meta_url)
                  ? $meta_url
                  : url('');
    $meta_title = ! empty($meta_title)
                  ? $meta_title
                  : config('app.name', '');
    $meta_desc = ! empty($meta_desc)
                  ? $meta_desc
                  : '';
?>
<meta charset="utf-8" />
<meta 
    name="viewport" 
    content="width=device-width, initial-scale=1.0, <?php echo e($meta_vp); ?>" 
/>
<meta http-equiv="X-UA-Compatible" content="IE=edge" />
<meta 
    name="keywords" 
    content="<?php echo e($meta_kw); ?>" 
/>
<meta name="author" content="Desenvolvimento Nova Prestech" />
<meta name="title" content="<?php echo e($meta_title); ?>" />
<meta name="description" content="<?php echo e($meta_desc); ?>" />
<meta 
    name="url" 
    content="<?php echo e($meta_url); ?>" 
    data-user="<?php echo e(Auth::user()?->id ?? 'anonymous'); ?>" 
/>
<?php /**PATH C:\Users\Aron\Desktop\programming\Prestech\erp\erpgo-fork\erp_prestech\_inc\laravel\resources\views/fragments/std.blade.php ENDPATH**/ ?>