<?php
    use App\Config\Constants\ViewClassNamesConstants as VC;
    use App\Models\Utility;
    use Collective\Html\FormFacade as Form;
    use Illuminate\Support\Facades\{Auth, Route};
    use Illuminate\Support\Str;

    $user = Auth::user();
    $lang = Utility::fetchUserLang(user: $user);

    $hasKey         = isset($key) && !empty($key);
    $updateBase     = 'feature_update';
    $updateKebab    = Str::kebab($updateBase);
    $updateResolved = Route::has($updateBase) ? $updateBase : (Route::has($updateKebab) ? $updateKebab : null);
    $updateUrl      = ($updateResolved && $hasKey) ? route($updateResolved, $key) : '#';
    $updateGuard    = Utility::fetchLinkMessage($lang, 'features', 'update_route_unavailable')
                        ?? __('Update Feature route is unavailable. Please contact technical support or your domain administrator.');
    $f        = is_array($feature ?? null) ? $feature : [];
    $heading  = !empty($f['feature_heading']) ? $f['feature_heading'] : '';
    $desc     = !empty($f['feature_description']) ? $f['feature_description'] : '';
?>

<?php echo e(Form::model(null, [
    'url'               => $updateUrl,
    'method'            => 'POST',
    'enctype'           => 'multipart/form-data',
    'id'                => 'feature-update-form',
    'data-url'          => $updateUrl,
    'data-guard-msg'    => $updateGuard,
    'data-sv-localized' => 'true'
])); ?>

    <div class="modal-body">
        <?php echo csrf_field(); ?>
        <div class="row">
            <div class="<?php echo e(VC::FM_GCB12); ?>">
                <?php echo e(Form::label('Heading', __('Heading'), ['class' => VC::FM_LB])); ?>

                <?php echo e(Form::text('feature_heading', $heading, ['class' => VC::FM_CT, 'placeholder' => __('Enter Heading')])); ?>

            </div>

            <div class="<?php echo e(VC::FM_GCB12); ?>">
                <?php echo e(Form::label('Description', __('Description'), ['class' => VC::FM_LB])); ?>

                <?php echo e(Form::textarea('feature_description', $desc, ['class' => VC::FM_CT . ' summernote-simple', 'placeholder' => __('Enter Description')])); ?>

            </div>

            <div class="<?php echo e(VC::FM_GCB12); ?>">
                <?php echo e(Form::label('Logo', __('Logo'), ['class' => VC::FM_LB])); ?>

                <input type="file" name="feature_logo" class="<?php echo e(VC::FM_CT); ?>">
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <input type="button" value="<?php echo e(__('Cancel')); ?>" class="<?php echo e(VC::BT_LG); ?>" data-bs-dismiss="modal">
        <input type="submit" value="<?php echo e(__('Update')); ?>" class="<?php echo e(VC::BT_PRM); ?>">
    </div>
    <script defer src="<?php echo e(asset('assets/js/routes/features/edit.js')); ?>"></script>
<?php echo e(Form::close()); ?>









<?php /**PATH /home/aronboliveira/Desktop/programming/Prestech/erp/erpgo-fork/erp_prestech/_inc/laravel/Modules/LandingPage/Resources/views/landingpage/features/edit.blade.php ENDPATH**/ ?>