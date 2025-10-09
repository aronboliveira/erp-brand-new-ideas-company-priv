<script src="https://js.pusher.com/7.0.3/pusher.min.js"></script>
<script>
  if (window?.console) {
    const originalConsoleLog = window.console.log;
    window.console.log = function(...args) {
      if (args[0]?.includes?.('Pusher')) return;
      originalConsoleLog.apply(console, args);
    };
  }
</script>
<script defer>
  document.addEventListener("DOMContentLoaded", function() {
    const originalConsoleLog = console.log;
    console.log = function(...args) {
      if (args[0]?.includes?.('Pusher')) return;
      originalConsoleLog.apply(console, args);
    };
  });
</script>
<script>
  messenger = "<?php echo e(@$id ?? '0'); ?>";
</script>
<script src="<?php echo e(asset('js/chatify/code.js')); ?>"></script>
<?php if (! (app()->environment('production'))): ?>
  <script>
      if (!window.pusherConfig || !Object.entries(window.pusherConfig || []).length)
        window.pusherConfig = {
            key: "<?php echo e(config('chatify.pusher.key')); ?>",
            cluster: "<?php echo e(config('chatify.pusher.options.cluster')); ?>",
            wsHost: "ws-<?php echo e(config('chatify.pusher.options.cluster')); ?>.pusher.com"
        };
  </script>
  <script src="<?php echo e(asset('assets/js/routes/vendors/chatify/lang/pusher.js')); ?>"></script>
  <script src="<?php echo e(asset('assets/js/routes/vendors/chatify/pusher.js')); ?>"></script>
<?php endif; ?>
<?php /**PATH /home/aronboliveira/Desktop/programming/Prestech/erp/erpgo-fork/erp_prestech/_inc/laravel/resources/views/vendor/Chatify/layouts/footer_links.blade.php ENDPATH**/ ?>