<?php
    use App\Config\Constants\{
        DatabaseConstants as DB,
        ExtendingLayoutsConstants as EL,
        PermissionsConstants as PC,
        PlansConstants as PL,
        StacksConstants as ST,
        UsersConstants as UC,
        ViewsConstants as VW,
        ViewClassNamesConstants as VC,
        YieldingConstants as YD
    };
    use App\Models\{Plan, Utility};
    use Illuminate\Support\Facades\{Auth, Crypt, Route, Storage};
    use Illuminate\Support\Collection;

    $user        = Auth::user();
    $lang        = is_callable([Utility::class,'fetchUserLang']) ? Utility::fetchUserLang(user:$user) : app()->getLocale();
    $canFetchMsg = is_callable([Utility::class,'fetchLinkMessage']);

    $dashUrl   = Route::has('dashboard') ? route('dashboard') : '#';
    $dashGuard = ($canFetchMsg ? Utility::fetchLinkMessage($lang, 'generics', 'dashboard_unavailable') : 'Dashboard route is unavailable. Please contact technical support or your domain administrator.') ?? __('Dashboard route is unavailable. Please contact technical support or your domain administrator.');

    $plansList = [];
    if (is_array($plans ?? null) && count($plans)) {
        $plansList = $plans;
    } elseif (($plans ?? null) instanceof Collection && $plans->isNotEmpty()) {
        $plansList = $plans;
    }

    $dir = asset(Storage::url('uploads/plan'));
    $currency = $admin_payment_setting['currency_symbol'] ?? '$';
?>


<?php $__env->startSection(YD::ADM_PG_TTL); ?>
    <?php echo e(__('Manage Plan')); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startSection(YD::ADM_BDC); ?>
    <li class="breadcrumb-item">
        <a href="<?php echo e($dashUrl); ?>"
           data-url="<?php echo e($dashUrl); ?>"
           data-sv-localized="true"
           data-guard-msg="<?php echo e($dashGuard); ?>"
           <?php echo e($dashUrl !== '#' ? '' : 'aria-disabled=true'); ?>>
            <?php echo e(__('Dashboard')); ?>

        </a>
    </li>
    <li class="breadcrumb-item active" aria-current="page"><?php echo e(__('Plan')); ?></li>
<?php $__env->stopSection(); ?>

<?php $__env->startSection(YD::ADM_ACT_BTN); ?>
    <div class="<?php echo e(VC::FEND); ?>">
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('create plan')): ?>
            <?php
                $canCreate =
                    !empty($admin_payment_setting) && is_array($admin_payment_setting ?? null) && (
                        ($admin_payment_setting['is_manually_payment_enabled'] ?? 'off') == 'on' ||
                        ($admin_payment_setting['is_bank_transfer_enabled'] ?? 'off') == 'on' ||
                        ($admin_payment_setting['is_stripe_enabled'] ?? 'off') == 'on' ||
                        ($admin_payment_setting['is_paypal_enabled'] ?? 'off') == 'on' ||
                        ($admin_payment_setting['is_paystack_enabled'] ?? 'off') == 'on' ||
                        ($admin_payment_setting['is_flutterwave_enabled'] ?? 'off') == 'on' ||
                        ($admin_payment_setting['is_razorpay_enabled'] ?? 'off') == 'on' ||
                        ($admin_payment_setting['is_mercado_enabled'] ?? 'off') == 'on' ||
                        ($admin_payment_setting['is_paytm_enabled'] ?? 'off') == 'on' ||
                        ($admin_payment_setting['is_mollie_enabled'] ?? 'off') == 'on' ||
                        ($admin_payment_setting['is_skrill_enabled'] ?? 'off') == 'on' ||
                        ($admin_payment_setting['is_coingate_enabled'] ?? 'off') == 'on' ||
                        ($admin_payment_setting['is_paymentwall_enabled'] ?? 'off') == 'on' ||
                        ($admin_payment_setting['is_toyyibpay_enabled'] ?? 'off') == 'on' ||
                        ($admin_payment_setting['is_payfast_enabled'] ?? 'off') == 'on' ||
                        ($admin_payment_setting['is_iyzipay_enabled'] ?? 'off') == 'on' ||
                        ($admin_payment_setting['is_sspay_enabled'] ?? 'off') == 'on' ||
                        ($admin_payment_setting['is_paytab_enabled'] ?? 'off') == 'on' ||
                        ($admin_payment_setting['is_benefit_enabled'] ?? 'off') == 'on' ||
                        ($admin_payment_setting['is_cashfree_enabled'] ?? 'off') == 'on' ||
                        ($admin_payment_setting['is_aamarpay_enabled'] ?? 'off') == 'on' ||
                        ($admin_payment_setting['is_paytr_enabled'] ?? 'off') == 'on' ||
                        ($admin_payment_setting['is_yookassa_enabled'] ?? 'off') == 'on'
                    );

                $createUrl   = $canCreate && Route::has(VW::PLN.'.create') ? route(VW::PLN.'.create') : '#';
                $createGuard = ($canFetchMsg ? Utility::fetchLinkMessage($lang, VW::PLN ?? 'plans', 'create_plan_unavailable') : 'Create Plan route is unavailable. Please contact technical support or your domain administrator.') ?? __('Create Plan route is unavailable. Please contact technical support or your domain administrator.');
            ?>
            <?php if($canCreate): ?>
                <a href="<?php echo e($createUrl); ?>"
                   data-size="lg"
                   data-url="<?php echo e($createUrl); ?>"
                   data-ajax-popup="true"
                   data-bs-toggle="tooltip"
                   title="<?php echo e(__('Create')); ?>"
                   data-title="<?php echo e(__('Create New Plan')); ?>"
                   data-sv-localized="true"
                   data-guard-msg="<?php echo e($createGuard); ?>"
                   class="<?php echo e(VC::BT_SM_PM); ?>">
                    <i class="<?php echo e(VC::TI_PLS); ?>"></i>
                </a>
            <?php endif; ?>
        <?php endif; ?>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection(YD::ADM_CTT); ?>
    <div class="<?php echo e(VC::RW); ?>">
        <?php $__empty_1 = true; $__currentLoopData = $plansList; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $plan): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <?php
                $pName      = data_get($plan, PL::COL_NM, __('No name available'));
                $pPrice     = data_get($plan, PL::COL_PC, 0);
                $pDurRaw    = data_get($plan, 'duration', null);
                $durMap     = Plan::$arrDuration ?? [];
                $pDurText   = $durMap[$pDurRaw] ?? __('Unknown duration');
                $isCompany  = data_get($user, UC::COL_TP) === PC::CPN;
                $isSuper    = data_get($user, UC::COL_TP) === PC::SA;
                $isActive   = ($isCompany || $isSuper) && (data_get($user, UC::COL_PL) == data_get($plan, 'id'));
                $features = [
                    ['key'=>PL::COL_MAX_U,  'label'=>__('Users'),     'type'=>'quota',  'unit'=>null],
                    ['key'=>PL::COL_MAX_CR, 'label'=>__('Customers'), 'type'=>'quota',  'unit'=>null],
                    ['key'=>PL::COL_MAX_V,  'label'=>__('Vendors'),   'type'=>'quota',  'unit'=>null],
                    ['key'=>PL::COL_MAX_CL, 'label'=>__('Clients'),   'type'=>'quota',  'unit'=>null],
                    ['key'=>PL::COL_SL,     'label'=>__('Storage'),   'type'=>'quota',  'unit'=>__('MB')],
                    ['key'=>PL::COL_ACC,    'label'=>__('Account'),   'type'=>'toggle'],
                    ['key'=>PL::COL_CRM,    'label'=>__('CRM'),       'type'=>'toggle'],
                    ['key'=>PL::COL_HRM,    'label'=>__('HRM'),       'type'=>'toggle'],
                    ['key'=>PL::COL_PJ,     'label'=>__('Project'),   'type'=>'toggle'],
                    ['key'=>PL::COL_POS,    'label'=>__('POS'),       'type'=>'toggle'],
                    ['key'=>PL::COL_GPT,    'label'=>__('Chat GPT'),  'type'=>'toggle'],
                ];
                $chunks = collect($features)->chunk(ceil(count($features)/2))->all();

                $editUrl   = (Gate::check('create plan') || Gate::check('edit plan')) && Route::has(VW::PLN.'.edit') ? route(VW::PLN.'.edit', $plan->id) : '#';
                $editGuard = ($canFetchMsg ? Utility::fetchLinkMessage($lang, VW::PLN ?? 'plans', 'edit_plan_unavailable') : 'Edit Plan route is unavailable. Please contact technical support or your domain administrator.') ?? __('Edit Plan route is unavailable. Please contact technical support or your domain administrator.');

                $stripeHas = Route::has('stripe');
                $buyUrl    = $stripeHas ? route('stripe', Crypt::encrypt($plan->id)) : '#';
                $buyGuard  = ($canFetchMsg ? Utility::fetchLinkMessage($lang, VW::PLN ?? 'plans', 'buy_plan_unavailable') : 'Buy Plan route is unavailable. Please contact technical support or your domain administrator.') ?? __('Buy Plan route is unavailable. Please contact technical support or your domain administrator.');

                $reqSendHas = Route::has(VW::PLN.'.request.send');
                $reqSendUrl = $reqSendHas ? route(VW::PLN.'.request.send', [Crypt::encrypt($plan->id)]) : '#';
                $reqSendGuard = ($canFetchMsg ? Utility::fetchLinkMessage($lang, VW::PLN_RQ ?? 'plan_request', 'send_plan_request_unavailable') : 'Send Plan Request route is unavailable. Please contact technical support or your domain administrator.') ?? __('Send Plan Request route is unavailable. Please contact technical support or your domain administrator.');

                $reqCancelHas = Route::has(VW::PLN_RQ.'.request.cancel');
                $reqCancelUrl = $reqCancelHas ? route(VW::PLN_RQ.'.request.cancel', $user->id) : '#';
                $reqCancelGuard = ($canFetchMsg ? Utility::fetchLinkMessage($lang, VW::PLN_RQ ?? 'plan_request', 'cancel_plan_request_unavailable') : 'Cancel Plan Request route is unavailable. Please contact technical support or your domain administrator.') ?? __('Cancel Plan Request route is unavailable. Please contact technical support or your domain administrator.');

                $planExpire = data_get($user, 'plan_expire_date');
            ?>

            <div class="plan_card">
                <div class="card price-card price-1 wow animate__fadeInUp" data-wow-delay="0.2s" style="visibility:visible;animation-delay:0.2s;animation-name:fadeInUp;">
                    <div class="card-body">
                        <span class="price-badge bg-primary"><?php echo e($pName); ?></span>

                        <?php if($isActive): ?>
                            <div class="d-flex flex-row-reverse m-0 p-0 active-tag">
                                <span class="align-items-right">
                                    <i class="f-10 lh-1 fas fa-circle text-success"></i>
                                    <span class="ms-2"><?php echo e(__('Active')); ?></span>
                                </span>
                            </div>
                        <?php endif; ?>

                        <h1 class="mb-4 f-w-600">
                            <?php echo e($currency); ?><?php echo e(number_format((float)$pPrice)); ?>

                            <small class="text-sm">/<?php echo e(__($pDurText)); ?></small>
                        </h1>

                        <p class="mb-0">
                            <?php echo e(__('Duration : ') . __($pDurText)); ?><br/>
                        </p>

                        <div class="row">
                            <?php $__currentLoopData = $chunks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $col): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="col-6">
                                    <ul class="list-unstyled my-5">
                                        <?php $__currentLoopData = $col; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <?php
                                                $value = data_get($plan, $item['key']);
                                                if($item['type']==='quota'){
                                                    $text = ($value===-1 ? __('Unlimited') : (is_null($value) ? __('Not specified') : $value));
                                                    if(!empty($item['unit'])) $text .= ' '.$item['unit'];
                                                    $text .= ' '.$item['label'];
                                                } else {
                                                    $text = ((int)$value === 1 ? __('Enable') : __('Disable')).' '.$item['label'];
                                                }
                                            ?>
                                            <li class="white-sapce-nowrap">
                                                <span class="theme-avatar"><i class="text-primary ti ti-circle-plus"></i></span>
                                                <?php echo e($text); ?>

                                            </li>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </ul>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>

                        <?php if($isSuper): ?>
                            <div class="col-4">
                                <a title="<?php echo e(__('Edit Plan')); ?>"
                                   href="<?php echo e($editUrl); ?>"
                                   class="btn btn-primary btn-icon m-1"
                                   data-url="<?php echo e($editUrl); ?>"
                                   data-ajax-popup="true"
                                   data-title="<?php echo e(__('Edit Plan')); ?>"
                                   data-size="lg"
                                   data-bs-toggle="tooltip"
                                   data-sv-localized="true"
                                   data-guard-msg="<?php echo e($editGuard); ?>"
                                   title="<?php echo e(__('Edit')); ?>">
                                    <i class="ti ti-edit"></i>
                                </a>
                            </div>
                        <?php endif; ?>

                        <?php if(!empty($admin_payment_setting) && is_array($admin_payment_setting ?? null)): ?>
                            <?php
                                $anyGateway =
                                    ($admin_payment_setting['is_manually_payment_enabled'] ?? 'off') == 'on' ||
                                    ($admin_payment_setting['is_bank_transfer_enabled'] ?? 'off') == 'on' ||
                                    ($admin_payment_setting['is_stripe_enabled'] ?? 'off') == 'on' ||
                                    ($admin_payment_setting['is_paypal_enabled'] ?? 'off') == 'on' ||
                                    ($admin_payment_setting['is_paystack_enabled'] ?? 'off') == 'on' ||
                                    ($admin_payment_setting['is_flutterwave_enabled'] ?? 'off') == 'on' ||
                                    ($admin_payment_setting['is_razorpay_enabled'] ?? 'off') == 'on' ||
                                    ($admin_payment_setting['is_mercado_enabled'] ?? 'off') == 'on' ||
                                    ($admin_payment_setting['is_paytm_enabled'] ?? 'off') == 'on' ||
                                    ($admin_payment_setting['is_mollie_enabled'] ?? 'off') == 'on' ||
                                    ($admin_payment_setting['is_skrill_enabled'] ?? 'off') == 'on' ||
                                    ($admin_payment_setting['is_coingate_enabled'] ?? 'off') == 'on' ||
                                    ($admin_payment_setting['is_paymentwall_enabled'] ?? 'off') == 'on' ||
                                    ($admin_payment_setting['is_toyyibpay_enabled'] ?? 'off') == 'on' ||
                                    ($admin_payment_setting['is_payfast_enabled'] ?? 'off') == 'on' ||
                                    ($admin_payment_setting['is_iyzipay_enabled'] ?? 'off') == 'on' ||
                                    ($admin_payment_setting['is_sspay_enabled'] ?? 'off') == 'on' ||
                                    ($admin_payment_setting['is_paytab_enabled'] ?? 'off') == 'on' ||
                                    ($admin_payment_setting['is_benefit_enabled'] ?? 'off') == 'on' ||
                                    ($admin_payment_setting['is_cashfree_enabled'] ?? 'off') == 'on' ||
                                    ($admin_payment_setting['is_aamarpay_enabled'] ?? 'off') == 'on' ||
                                    ($admin_payment_setting['is_paytr_enabled'] ?? 'off') == 'on';
                            ?>

                            <?php if(!$isSuper && $anyGateway): ?>
                                <?php if(data_get($plan,'id') != data_get($user, UC::COL_PL)): ?>
                                    <?php if((float)$pPrice > 0): ?>
                                        <a href="<?php echo e($buyUrl); ?>"
                                           class="btn btn-primary btn-icon m-1"
                                           data-url="<?php echo e($buyUrl); ?>"
                                           data-sv-localized="true"
                                           data-guard-msg="<?php echo e($buyGuard); ?>">
                                            <?php echo e(__('Buy Plan')); ?>

                                        </a>
                                    <?php endif; ?>
                                <?php endif; ?>

                                <?php if(data_get($plan,'id') !== DB::DEFAULT_PLAN && data_get($plan,'id') != data_get($user, UC::COL_PL)): ?>
                                    <?php
                                        $hasRequestedSame = (int) data_get($user, 'requested_plan') === (int) data_get($plan, 'id');
                                    ?>
                                    <?php if(!$hasRequestedSame): ?>
                                        <a href="<?php echo e($reqSendUrl); ?>"
                                           class="btn btn-primary btn-icon m-1"
                                           data-url="<?php echo e($reqSendUrl); ?>"
                                           data-sv-localized="true"
                                           data-guard-msg="<?php echo e($reqSendGuard); ?>"
                                           data-bs-toggle="tooltip"
                                           title="<?php echo e(__('Send Request')); ?>">
                                            <span class="btn-inner--icon"><i class="ti ti-corner-up-right"></i></span>
                                        </a>
                                    <?php else: ?>
                                        <a href="<?php echo e($reqCancelUrl); ?>"
                                           class="btn btn-danger btn-icon m-1"
                                           data-url="<?php echo e($reqCancelUrl); ?>"
                                           data-sv-localized="true"
                                           data-guard-msg="<?php echo e($reqCancelGuard); ?>"
                                           data-bs-toggle="tooltip"
                                           title="<?php echo e(__('Cancel Request')); ?>">
                                            <span class="btn-inner--icon"><i class="ti ti-x"></i></span>
                                        </a>
                                    <?php endif; ?>
                                <?php endif; ?>
                            <?php endif; ?>
                        <?php endif; ?>

                        <?php if($isActive): ?>
                            <p class="display-total-time text-dark mb-0">
                                <?php
                                    $expText = $planExpire ? ($user?->dateFormat($planExpire) ?? $planExpire) : __('lifetime');
                                ?>
                                <?php echo e(__('Plan Expired : ')); ?> <?php echo e($expText); ?>

                            </p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <div class="<?php echo e(VC::C12); ?>">
                <div class="text-center text-muted py-4"><?php echo e(__('No plans found.')); ?></div>
            </div>
        <?php endif; ?>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush(ST::ADM_SCR_PG); ?>
    <script defer src="<?php echo e(asset('assets/js/routes/plans/index.js')); ?>"></script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make(EL::ADM, \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/aronboliveira/Desktop/programming/Prestech/erp/erpgo-fork/erp_prestech/_inc/laravel/resources/views/plans/index.blade.php ENDPATH**/ ?>