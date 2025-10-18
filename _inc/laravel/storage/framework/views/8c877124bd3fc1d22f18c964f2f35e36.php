<?php
    use Illuminate\Support\Collection;
    $raw = $messengerColor ?? '';
    $mc = (is_string($raw) && preg_match('/^#(?:[0-9a-fA-F]{3}){1,2}$/', $raw)) ? $raw : '#9ca3af';
?>
<style>
    #nprogress .bar{background: <?php echo e($mc); ?>;}
    #nprogress .peg{box-shadow:0 0 10px <?php echo e($mc); ?>,0 0 5px <?php echo e($mc); ?>;}
    #nprogress .spinner-icon{border-top-color: <?php echo e($mc); ?>;border-left-color: <?php echo e($mc); ?>;}
    .m-header svg{color: <?php echo e($mc); ?>;}
    .messenger-list-item td b{background: <?php echo e($mc); ?>;}
    .messenger-infoView nav a{color: <?php echo e($mc); ?>;}
    .messenger-infoView-btns a.default{color: <?php echo e($mc); ?>;}
    .mc-sender p{background: <?php echo e($mc); ?>;}
    .messenger-sendCard button svg{color: <?php echo e($mc); ?>;}
    .messenger-listView-tabs a,.messenger-listView-tabs a:hover,.messenger-listView-tabs a:focus{color: <?php echo e($mc); ?>;}
    .active-tab{border-bottom:2px solid <?php echo e($mc); ?>;}
    .lastMessageIndicator{color: <?php echo e($mc); ?>;}
    .messenger-favorites div.avatar{box-shadow:0 0 0 2px <?php echo e($mc); ?>;}
    .dark-mode-switch{color: <?php echo e($mc); ?>;}
    .m-list-active b{background:#fff !important;color: <?php echo e($mc); ?> !important;}
</style>
<?php /**PATH /home/aronboliveira/Desktop/programming/Prestech/erp/erpgo-fork/erp_prestech/_inc/laravel/resources/views/vendor/Chatify/layouts/messenger_color.blade.php ENDPATH**/ ?>