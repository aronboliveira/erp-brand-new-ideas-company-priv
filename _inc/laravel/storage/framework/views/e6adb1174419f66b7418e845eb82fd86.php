<?php
    use App\Config\Constants\{
        PlansConstants,
        ViewsConstants,
        ViewClassNamesConstants as VC,
    };
    use App\Models\Utility;
    use Collective\Html\FormFacade as Form;
    use Illuminate\Support\Facades\{Auth, Route};
    use Illuminate\Support\Str;
    $user = Auth::user();
    $lang = Utility::fetchUserLang(user: $user);
    $dlStoreBaseRouteName   = ViewsConstants::DL;
    $dlStoreKebabRouteName  = Str::kebab($dlStoreBaseRouteName);
    $dlStoreResolvedName    = Route::has($dlStoreBaseRouteName)
        ? $dlStoreBaseRouteName
        : (Route::has($dlStoreKebabRouteName) ? $dlStoreKebabRouteName : null);
    $dlStoreUrl             = $dlStoreResolvedName ? route($dlStoreResolvedName) : '#';
    $dlStoreFormId          = 'deal-store-form';
    $userLang               = isset($lang) ? $lang : Utility::fetchUserLang();
    $dlStoreGuardMessage    = Utility::fetchLinkMessage($userLang, ViewsConstants::DL, 'store_deal_route_unavailable')
        ?? 'Store Deal route is unavailable. Please contact technical support or your domain administrator.';
?>
<?php echo e(Form::open([
    'method'            => 'POST',
    'url'               => $dlStoreUrl,
    'id'                => $dlStoreFormId,
    'data-url'          => $dlStoreUrl,
    'data-guard-msg'    => $dlStoreGuardMessage,
    'data-sv-localized' => 'true',
])); ?>

    <?php echo csrf_field(); ?>
    <div class="modal-body">
        <?php $plan = Utility::getChatGPTSettings(); ?>
        <?php if($plan?->{PlansConstants::COL_GPT} == 1): ?>
            <div class="text-end">
                <?php
                    $generateRoute = Route::has('generate')
                        ? route('generate', ['deal' => $deal->id])
                        : '#';
                    $generateGuardMsg = Utility::fetchLinkMessage(
                        $lang,
                        ViewsConstants::DL,
                        'generate_route_unavailable'
                    ) ?? 'Generate content for deals with AI route is unavailable. Please contact technical support or your domain administrator.';
                ?>
                <a
                    id="generate-ai-btn-<?php echo e($deal->id); ?>"
                    href="<?php echo e($generateRoute); ?>"
                    data-url="<?php echo e($generateRoute); ?>"
                    data-guard-msg="<?php echo e($generateGuardMsg); ?>"
                    data-size="md"
                    class="<?php echo e(VC::BT_PRM); ?> btn-icon btn-sm"
                    data-ajax-popup-over="true"
                    data-bs-placement="top"
                    data-title="<?php echo e(__('Generate content with AI')); ?>"
                >
                    <i class="<?php echo e(VC::FAS_RB); ?>"></i> <span><?php echo e(__('Generate with AI')); ?></span>
                </a>
                <script defer>
                    (() => {
                        const btn = document.getElementById('generate-ai-btn-<?php echo e($deal->id); ?>');
                        if (!btn || btn.getAttribute('data-listener-active') === 'true') return;
                        btn.setAttribute('data-listener-active','true');
                        btn.addEventListener('click', e => {
                            try {
                                const url = btn.getAttribute('data-url') ?? '#';
                                if (url !== '#') return;
                                e.preventDefault();
                                const msg = btn.getAttribute('data-guard-msg') ?? '# ERROR';
                                const bs = document.querySelector('link[href*="bootstrap"]') && window.bootstrap;
                                let container = document.getElementById('toast-container');
                                if (!container) {
                                    container = document.createElement('div');
                                    container.id = 'toast-container';
                                    document.body.appendChild(container);
                                }
                                if (bs) {
                                    const toast = document.createElement('div');
                                    toast.className = 'toast';
                                    toast.setAttribute('role','alert');
                                    toast.setAttribute('aria-live','assertive');
                                    toast.setAttribute('aria-atomic','true');
                                    const body = document.createElement('div');
                                    body.className = 'toast-body';
                                    body.textContent = msg;
                                    toast.appendChild(body);
                                    container.appendChild(toast);
                                    bootstrap.Toast.getOrCreateInstance(toast).show();
                                } else {
                                    alert(msg);
                                }
                                btn.setAttribute('data-failed-route','true');
                            } catch {}
                        });
                    })();
                </script>
            </div>
        <?php endif; ?>
        <div class="<?php echo e(VC::RW); ?>">
            <div class="col-6 <?php echo e(VC::FM_G); ?>">
                <?php echo e(Form::label('name', __('Deal Name'), ['class' => VC::FM_LB])); ?>

                <?php echo e(Form::text('name', null, ['class' => VC::FM_CT, 'required' => 'required'])); ?>

            </div>
            <div class="col-6 <?php echo e(VC::FM_G); ?>">
                <?php echo e(Form::label('phone', __('Phone'), ['class' => VC::FM_LB])); ?>

                <?php echo e(Form::text('phone', null, ['class' => VC::FM_CT, 'required' => 'required'])); ?>

            </div>
            <div class="col-6 <?php echo e(VC::FM_G); ?>">
                <?php echo e(Form::label('price', __('Price'), ['class' => VC::FM_LB])); ?>

                <?php echo e(Form::number('price', 0, ['class' => VC::FM_CT, 'min' => 0])); ?>

            </div>
            <div class="col-6 <?php echo e(VC::FM_G); ?>">
                <?php echo e(Form::label('clients', __('Clients'), ['class' => VC::FM_LB])); ?>

                <?php echo e(Form::select('clients', Utility::isFilled($clients) ? $clients : [__('No clients available.' ?? [])], null, [
                    'class'    => VC::FM_CT . ' select2',
                    'multiple' => '',
                    'id'       => 'choices-multiple1',
                    'required' => 'required'
                ])); ?>

                <?php if(Utility::isFilled($clients) && strtolower($user?->{UsersConstants::COL_TP}) == 'owner' ?? []): ?>
                    <?php
                        $clientsIndexRoute = Route::has(VW::CLT.'.index')
                            ? route(VW::CLT.'.index')
                            : '#';
                        $clientsIndexGuardMsg = Utility::fetchLinkMessage(
                            $lang,
                            ViewsConstants::DL,
                            'clients_index_route_unavailable'
                        ) ?? 'Clients index route is unavailable. Please contact technical support or your domain administrator.';
                    ?>
                    <div class="<?php echo e(VC::TXT_MT); ?> <?php echo e(VC::TXSM); ?>">
                        <?php echo e(__('Please create new clients')); ?> <a
                            id="clients-index-link"
                            href="<?php echo e($clientsIndexRoute); ?>"
                            data-url="<?php echo e($clientsIndexRoute); ?>"
                            data-guard-msg="<?php echo e($clientsIndexGuardMsg); ?>"
                        ><?php echo e(__('here')); ?></a>.
                    </div>
                    <script defer src="<?php echo e(asset('assets/js/routes/deals/storeIndex.js')); ?>"></script>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="<?php echo e(VC::DFL); ?> modal-footer">
        <button type="button" class="<?php echo e(VC::BT_LG); ?>" data-bs-dismiss="modal"><?php echo e(__('Cancel')); ?></button>
        <button type="submit" class="<?php echo e(VC::BT_PRM); ?>"><?php echo e(__('Create')); ?></button>
    </div>
    <script defer src="<?php echo e(asset('assets/js/routes/deals/storeCreate.js')); ?>"></script>
<?php echo e(Form::close()); ?>

<?php /**PATH /home/aronboliveira/Desktop/programming/Prestech/erp/erpgo-fork/erp_prestech/_inc/laravel/resources/views/deals/create.blade.php ENDPATH**/ ?>