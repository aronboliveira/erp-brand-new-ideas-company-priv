<?php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        SettingsConstants as SC
    };
    use App\Models\Utility;
    use Illuminate\Support\Facades\{Log, Session};
    Log::debug('Loading admin footer data...');
    $settings = Utility::settings();
    Log::debug('Loading admin footer template...');
?>
<footer class="dash-footer">
    <div class="footer-wrapper">
        <div class="py-1">
            <p class="mb-0 text-muted"> &copy;
                <?php echo e(date('Y')); ?> <?php echo e($settings[SC::FT_TXT] ? $settings[SC::FT_TXT] : config('app.name', 'ERPNovaPrestech')); ?>

            </p>
        </div>
    </div>
</footer>
<!-- Required Js -->
<script src="<?php echo e(asset('js/jquery.min.js')); ?>"></script>
<script src="<?php echo e(asset('assets/js/dash.js')); ?>"></script>
<script defer src="<?php echo e(asset('js/jquery.form.js')); ?>"></script>
<script defer src="<?php echo e(asset('assets/js/plugins/popper.min.js')); ?>"></script>
<script defer src="<?php echo e(asset('assets/js/plugins/perfect-scrollbar.min.js')); ?>"></script>
<script defer src="<?php echo e(asset('assets/js/plugins/simplebar.min.js')); ?>"></script>
<script defer src="<?php echo e(asset('assets/js/plugins/bootstrap.min.js')); ?>"></script>
<script defer src="<?php echo e(asset('assets/js/plugins/feather.min.js')); ?>"></script>
<script defer src="<?php echo e(asset('js/moment.min.js')); ?>"></script>
<script defer src="<?php echo e(asset('assets/js/plugins/bootstrap-switch-button.min.js')); ?>"></script>
<script defer src="<?php echo e(asset('assets/js/plugins/sweetalert2.all.min.js')); ?>"></script>
<script defer src="<?php echo e(asset('assets/js/plugins/simple-datatables.js')); ?>"></script>
<!-- Apex Chart -->
<script defer src="<?php echo e(asset('assets/js/plugins/apexcharts.min.js')); ?>"></script>
<script defer src="<?php echo e(asset('assets/js/plugins/main.min.js')); ?>"></script>
<script defer src="<?php echo e(asset('assets/js/plugins/choices.min.js')); ?>"></script>
<script defer src="<?php echo e(asset('assets/js/plugins/flatpickr.min.js')); ?>"></script>
<script defer src="<?php echo e(asset('js/jscolor.js')); ?>"></script>
<script defer src="<?php echo e(asset('js/popper.min.js')); ?>"></script>

<script>
    var site_currency_symbol_position = '<?php echo e($settings[SC::CR_SB_P]); ?>';
    var site_currency_symbol = '<?php echo e($settings[SC::CR_SB]); ?>';
</script>
<script src="<?php echo e(asset('js/custom.js')); ?>"></script>
<?php if($message = Session::get('success')): ?>
    <script>
        show_toastr('success', '<?php echo $message; ?>');
    </script>
<?php endif; ?>
<?php if($message = Session::get('error')): ?>
    <script>
        show_toastr('error', '<?php echo $message; ?>');
    </script>
<?php endif; ?>
<?php if($settings['enable_cookie'] == 'on'): ?>
    <?php if ($__env->exists(ExtendingLayoutsConstants::CKC)) echo $__env->make(ExtendingLayoutsConstants::CKC, \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php endif; ?>
<?php echo $__env->yieldPushContent('script-page'); ?>
<?php echo $__env->yieldPushContent('old-datatable-js'); ?>
<script defer src="<?php echo e(asset('assets/js/routes/partials/admin/footer.js')); ?>"></script>
<?php /**PATH /home/aronboliveira/Desktop/programming/Prestech/erp/erpgo-fork/erp_prestech/_inc/laravel/resources/views/partials/admin/footer.blade.php ENDPATH**/ ?>