<?php
    use App\Config\Constants\{
        PlansConstants,
        ViewsConstants as VW,
        ViewClassNamesConstants as VC
    };
    use App\Models\Utility;
    use Collective\Html\FormFacade as Form;
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\Str;

    $lang       = Utility::fetchUserLang();
    $hasModel   = !empty($plan ?? null) && data_get($plan, 'id');
    $updateBase     = VW::PLN . '.update';
    $updateKebab    = Str::kebab($updateBase);
    $updateResolved = Route::has($updateBase) ? $updateBase : (Route::has($updateKebab) ? $updateKebab : null);
    $updateUrl      = ($updateResolved && $hasModel) ? route($updateResolved, $plan->id) : '#';
    $updateGuard    = Utility::fetchLinkMessage($lang, VW::PLN, 'update_route_unavailable')
                        ?? __('Update Plan route is unavailable. Please contact technical support or your domain administrator.');
    $generateResolved = Route::has('generate') ? 'generate' : null;
    $generateUrl      = $generateResolved ? route('generate', ['plan']) : '#';
    $generateGuard    = Utility::fetchLinkMessage($lang, VW::PLN, 'generate_route_unavailable')
                        ?? __('Generate content route for Plan is unavailable. Please contact technical support or your domain administrator.');
?>

<?php if($hasModel): ?>
    <?php echo e(Form::model($plan, [
        'url'               => $updateUrl,
        'method'            => 'PUT',
        'enctype'           => 'multipart/form-data',
        'id'                => 'plan-update-form',
        'data-url'          => $updateUrl,
        'data-guard-msg'    => $updateGuard,
        'data-sv-localized' => 'true',
    ])); ?>

        <div class="modal-body">
            <?php ($settings = Utility::settings()); ?>
            <?php if(!empty($settings['chat_gpt_key'])): ?>
                <div class="text-end">
                    <a href="#"
                       id="generate-plan-btn"
                       data-size="md"
                       class="<?php echo e(VC::BT_SM_PM); ?> btn-icon"
                       data-ajax-popup-over="true"
                       data-url="<?php echo e($generateUrl); ?>"
                       data-bs-placement="top"
                       data-title="<?php echo e(__('Generate content with AI')); ?>"
                       data-guard-msg="<?php echo e($generateGuard); ?>"
                       data-sv-localized="true">
                        <i class="<?php echo e(VC::FAS_RB); ?>"></i> <span><?php echo e(__('Generate with AI')); ?></span>
                    </a>
                </div>
            <?php endif; ?>

            <div class="row">
                <div class="<?php echo e(VC::FM_GCB6); ?>">
                    <?php echo e(Form::label('name', __('Name'), ['class'=> VC::FM_LB])); ?>

                    <?php echo e(Form::text('name', null, ['class'=> VC::FM_CT . ' font-style','placeholder'=>__('Enter Plan Name'),'required'=>'required'])); ?>

                </div>

                <?php if(($plan->price ?? 0) > 0): ?>
                    <div class="<?php echo e(VC::FM_GCB6); ?>">
                        <?php echo e(Form::label('price', __('Price'), ['class'=> VC::FM_LB])); ?>

                        <?php echo e(Form::number('price', null, ['class'=> VC::FM_CT,'placeholder'=>__('Enter Plan Price'),'required'=>'required'])); ?>

                    </div>
                <?php endif; ?>

                <div class="<?php echo e(VC::FM_GCB6); ?>">
                    <?php echo e(Form::label('duration', __('Duration'), ['class'=> VC::FM_LB])); ?>

                    <?php echo Form::select('duration', $arrDuration ?? [], null, ['class' => VC::FM_CT_SL,'required'=>'required']); ?>

                </div>

                <div class="<?php echo e(VC::FM_GCB6); ?>">
                    <?php echo e(Form::label('max_users', __('Maximum Users'), ['class'=> VC::FM_LB])); ?>

                    <?php echo e(Form::number('max_users', null, ['class'=> VC::FM_CT,'required'=>'required'])); ?>

                    <span class="small"><?php echo e(__('Note: "-1" for Unlimited')); ?></span>
                </div>

                <div class="<?php echo e(VC::FM_GCB6); ?>">
                    <?php echo e(Form::label('max_customers', __('Maximum Customers'), ['class'=> VC::FM_LB])); ?>

                    <?php echo e(Form::number('max_customers', null, ['class'=> VC::FM_CT,'required'=>'required'])); ?>

                    <span class="small"><?php echo e(__('Note: "-1" for Unlimited')); ?></span>
                </div>

                <div class="<?php echo e(VC::FM_GCB6); ?>">
                    <?php echo e(Form::label('max_vendors', __('Maximum Vendors'), ['class'=> VC::FM_LB])); ?>

                    <?php echo e(Form::number('max_vendors', null, ['class'=> VC::FM_CT,'required'=>'required'])); ?>

                    <span class="small"><?php echo e(__('Note: "-1" for Unlimited')); ?></span>
                </div>

                <div class="<?php echo e(VC::FM_GCB6); ?>">
                    <?php echo e(Form::label('max_clients', __('Maximum Clients'), ['class'=> VC::FM_LB])); ?>

                    <?php echo e(Form::number('max_clients', null, ['class'=> VC::FM_CT,'required'=>'required'])); ?>

                    <span class="small"><?php echo e(__('Note: "-1" for Unlimited')); ?></span>
                </div>

                <div class="<?php echo e(VC::FM_GCB6); ?>">
                    <?php echo e(Form::label('storage_limit', __('Storage limit'), ['class'=> VC::FM_LB])); ?>

                    <div class="input-group">
                        <?php echo e(Form::number('storage_limit', null, ['class'=> VC::FM_CT,'required'=>'required'])); ?>

                        <div class="input-group-append">
                            <span class="input-group-text" id="basic-addon2"><?php echo e(__('MB')); ?></span>
                        </div>
                    </div>
                    <span class="small"><?php echo e(__('Note: upload size ( In MB)')); ?></span>
                </div>

                <div class="<?php echo e(VC::FM_GCB12); ?>">
                    <?php echo e(Form::label('description', __('Description'), ['class'=> VC::FM_LB])); ?>

                    <?php echo Form::textarea('description', null, ['class'=> VC::FM_CT,'rows'=>'2']); ?>

                </div>

                <div class="<?php echo e(VC::FM_GCB3); ?>">
                    <div class="form-check form-switch">
                        <input type="checkbox" class="form-check-input" name="enable_crm" id="enable_crm" <?php echo e(($plan['crm'] ?? 0) == 1 ? 'checked' : ''); ?>>
                        <label class="custom-control-label form-label" for="enable_crm"><?php echo e(__('CRM')); ?></label>
                    </div>
                </div>

                <div class="<?php echo e(VC::FM_GCB3); ?>">
                    <div class="form-check form-switch">
                        <input type="checkbox" class="form-check-input" name="enable_project" id="enable_project" <?php echo e(($plan['project'] ?? 0) == 1 ? 'checked' : ''); ?>>
                        <label class="custom-control-label form-label" for="enable_project"><?php echo e(__('Project')); ?></label>
                    </div>
                </div>

                <div class="<?php echo e(VC::FM_GCB3); ?>">
                    <div class="form-check form-switch">
                        <input type="checkbox" class="form-check-input" name="enable_hrm" id="enable_hrm" <?php echo e(($plan['hrm'] ?? 0) == 1 ? 'checked' : ''); ?>>
                        <label class="custom-control-label form-label" for="enable_hrm"><?php echo e(__('HRM')); ?></label>
                    </div>
                </div>

                <div class="<?php echo e(VC::FM_GCB3); ?>">
                    <div class="form-check form-switch">
                        <input type="checkbox" class="form-check-input" name="enable_account" id="enable_account" <?php echo e(($plan['account'] ?? 0) == 1 ? 'checked' : ''); ?>>
                        <label class="custom-control-label form-label" for="enable_account"><?php echo e(__('Account')); ?></label>
                    </div>
                </div>

                <div class="<?php echo e(VC::FM_GCB3); ?>">
                    <div class="form-check form-switch">
                        <input type="checkbox" class="form-check-input" name="enable_pos" id="enable_pos" <?php echo e(($plan['pos'] ?? 0) == 1 ? 'checked' : ''); ?>>
                        <label class="custom-control-label form-label" for="enable_pos"><?php echo e(__('POS')); ?></label>
                    </div>
                </div>

                <div class="<?php echo e(VC::FM_GCB3); ?>">
                    <div class="form-check form-switch">
                        <input type="checkbox" class="form-check-input" name="enable_chatgpt" id="enable_chatgpt" <?php echo e(($plan['chatgpt'] ?? 0) == 1 ? 'checked' : ''); ?>>
                        <label class="custom-control-label form-label" for="enable_chatgpt"><?php echo e(__('Chat GPT')); ?></label>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal-footer">
            <input type="button" value="<?php echo e(__('Cancel')); ?>" class="<?php echo e(VC::BT_LG); ?>" data-bs-dismiss="modal">
            <input type="submit" value="<?php echo e(__('Update')); ?>" class="<?php echo e(VC::BT_PRM); ?>">
        </div>
    <?php echo e(Form::close()); ?>


    <script defer src="<?php echo e(asset('assets/js/routes/plans/update.js')); ?>"></script>
    <script defer src="<?php echo e(asset('assets/js/routes/plans/generateEdit.js')); ?>"></script>
<?php else: ?>
    <div><?php echo e(__('No plan could be found.')); ?></div>
<?php endif; ?>
<?php /**PATH /home/aronboliveira/Desktop/programming/Prestech/erp/erpgo-fork/erp_prestech/_inc/laravel/resources/views/plans/edit.blade.php ENDPATH**/ ?>