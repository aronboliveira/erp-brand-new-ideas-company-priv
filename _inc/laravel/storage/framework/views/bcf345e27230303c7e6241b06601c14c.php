<?php
    use Collective\Html\FormFacade as Form;
    use App\Config\Constants\{ViewClassNamesConstants as VC, ViewsConstants};
    use Modules\LandingPage\Config\Constants\SettingsConstants as LSC;
    $radioOptions = [
        ['id'=>'page_content', 'value'=>'page_content', 'label'=>__('Page Content')],
        ['id'=>'page_url',     'value'=>'page_url',     'label'=>__('Page URL')],
    ];
    $toggles = [
        ['id'=>'header', 'label'=>__('Header')],
        ['id'=>'footer', 'label'=>__('Footer')],
        ['id'=>'login',  'label'=>__('Login')],
    ];
?>

<?php echo e(Form::model(null, [
    'route'   => [ViewsConstants::CST_PG . '.update', $key],
    'method'  => 'PUT',
    'enctype' => 'multipart/form-data',
])); ?>

    <div class="modal-body">
        <?php echo csrf_field(); ?>
        <div class="row">
            
            <div class="form-group col-md-12">
                <?php echo e(Form::label(LSC::MB_PG_NM, __('Page Name'), ['class'=>'form-label'])); ?>

                <?php echo e(Form::text(
                    LSC::MB_PG_NM,
                    $page[LSC::MB_PG_NM] ?? '',
                    ['class'=>'form-control font-style','placeholder'=>__('Enter Plan Name'),'required'=>'required']
                )); ?>

            </div>
            
            <div class="form-group col-md-12">
                <?php $__currentLoopData = $radioOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $opt): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="<?php echo e(VC::FM_CHK_IL); ?>">
                        <input
                            class="form-check-input"
                            type="radio"
                            name="template_name"
                            id="<?php echo e($opt['id']); ?>"
                            value="<?php echo e($opt['value']); ?>"
                            <?php echo e((isset($page['template_name']) && $page['template_name'] === $opt['value']) ? 'checked' : ''); ?>

                        >
                        <label class="form-check-label" for="<?php echo e($opt['id']); ?>">
                            <?php echo e($opt['label']); ?>

                        </label>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>

            
            <div class="form-group col-md-12 page_content <?php echo e((isset($page['template_name']) && $page['template_name'] !== 'page_content') ? 'd-none' : ''); ?>">
                <?php echo e(Form::label(LSC::MB_PG_CT, __('Page Content'), ['class'=>'form-label'])); ?>

                <?php echo e(Form::textarea(
                    LSC::MB_PG_CT,
                    $page[LSC::MB_PG_CT] ?? '',
                    ['class'=>'form-control summernote-simple','rows'=>5]
                )); ?>

            </div>

            
            <div class="form-group col-md-12 page_url <?php echo e((isset($page['template_name']) && $page['template_name'] !== 'page_url') ? 'd-none' : ''); ?>">
                <?php echo e(Form::label('page_url', __('Page URL'), ['class'=>'form-label'])); ?>

                <?php echo e(Form::text(
                    'page_url',
                    $page['page_url'] ?? '',
                    ['class'=>'form-control font-style','placeholder'=>__('Enter Page URL')]
                )); ?>

            </div>

            
            <?php $__currentLoopData = $toggles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="col-lg-2 col-xl-2 col-md-2">
                    <div class="form-check form-switch ml-1">
                        <input
                            type="checkbox"
                            class="form-check-input"
                            id="<?php echo e($t['id']); ?>"
                            name="<?php echo e($t['id']); ?>"
                            <?php echo e((isset($page[$t['id']]) && $page[$t['id']] === 'on') ? 'checked' : ''); ?>

                        />
                        <label class="form-check-label f-w-600 pl-1" for="<?php echo e($t['id']); ?>">
                            <?php echo e($t['label']); ?>

                        </label>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal"><?php echo e(__('Cancel')); ?></button>
        <button type="submit" class="btn btn-primary"><?php echo e(__('Update')); ?></button>
    </div>
    <script defer src="<?php echo e(asset('assets/js/routes/landingPage/menubar/edit.js')); ?>"></script>
<?php echo e(Form::close()); ?>


<?php /**PATH /home/aronboliveira/Desktop/programming/Prestech/erp/erpgo-fork/erp_prestech/_inc/laravel/Modules/LandingPage/Resources/views/landingpage/menubar/edit.blade.php ENDPATH**/ ?>