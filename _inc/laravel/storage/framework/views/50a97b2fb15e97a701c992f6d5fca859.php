<?php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        StacksConstants,
        ViewsConstants,
        ViewClassNamesConstants as VC,
        YieldingConstants,
    };
    use App\Models\Utility;
    use Illuminate\Support\Facades\Route;
    $lang = Utility::fetchUserLang();
    $couponCreateRoute  = Route::has(ViewsConstants::CPN . '.create')
        ? route(ViewsConstants::CPN . '.create')
        : '#';
    $couponCreateBtnId  = 'coupon-create-btn';
    $couponCreateMsg    = Utility::fetchLinkMessage(
        $lang,
        ViewsConstants::CPN,
        'coupon_create_route_unavailable'
    ) ?? 'Coupon create route is unavailable. Please contact technical support or your domain administrator.';
?>

<?php $__env->startPush(StacksConstants::ADM_SCR_PG); ?>
    <script defer>
        (() => {
            const ERR_FB = '# ERROR';
            const CLIENT_FLAG = 'data-client-localized';
            const GUARD_MSG = 'data-guard-msg';
            const LANG_KEY = 'erp-np-lang';
            let errorMessage = '';
            
            const getLocalizedMessage = (key, el) => {
                let msg = ERR_FB;
                if (el.getAttribute(CLIENT_FLAG) === 'true') {
                msg = el.getAttribute(GUARD_MSG) || msg;
                } else {
                let lang = (sessionStorage.getItem(LANG_KEY) || document.documentElement.lang || 'en')
                    .toLowerCase().replace(/_/g, '-');
                lang = lang === 'pt-br' ? lang : lang.slice(0, 2);
                msg = translations?.[lang]?.[key] ||
                        el.getAttribute(GUARD_MSG) ||
                        translations?.['en']?.[key] ||
                        msg;
                if (msg !== ERR_FB) {
                    el.setAttribute(GUARD_MSG, msg);
                    el.setAttribute(CLIENT_FLAG, 'true');
                }
                }
                return msg;
            };
            
            const showError = message => {
                try {
                let container = document.getElementById('toast-container');
                if (!container) {
                    container = document.createElement('div');
                    container.id = 'toast-container';
                    document.body.appendChild(container);
                }
                const bs = document.querySelector('link[href*="bootstrap"]') && window.bootstrap?.Toast;
                if (bs) {
                    const toast = document.createElement('div');
                    toast.className = 'toast';
                    toast.setAttribute('role', 'alert');
                    toast.setAttribute('aria-live', 'assertive');
                    toast.setAttribute('aria-atomic', 'true');
                    const body = document.createElement('div');
                    body.className = 'toast-body';
                    toast.appendChild(body);
                    container.appendChild(toast);
                    if (toast.getAttribute('data-click-listener') !== 'true') {
                    toast.addEventListener('click', () => body.textContent = message);
                    toast.setAttribute('data-click-listener', 'true');
                    }
                    body.textContent = message;
                    new bootstrap.Toast(toast).show();
                } else {
                    alert(message);
                }
                } catch {
                alert(message);
                }
            };
            
            const onErrorPointerUp = () => {
                if (errorMessage) {
                showError(errorMessage);
                errorMessage = '';
                }
            };
            document.addEventListener('pointerup', onErrorPointerUp);
            new MutationObserver((muts, obs) => {
                muts.forEach(m => Array.from(m.removedNodes).forEach(n => {
                if (n === document.documentElement) {
                    document.removeEventListener('pointerup', onErrorPointerUp);
                    obs.disconnect();
                }
                }));
            }).observe(document.body, { childList: true, subtree: true });
            
            document.addEventListener('DOMContentLoaded', () => {
                // Toggle manual/auto sections
                document.querySelectorAll('.code').forEach(el => {
                if (el.dataset.listenerAttached === 'true') return;
                el.dataset.listenerAttached = 'true';
                const onClick = () => {
                    try {
                    const val = el.value;
                    const manual = document.getElementById('manual');
                    const auto = document.getElementById('auto');
                    if (!manual || !auto) throw new Error('code_toggle_failed');
                    if (val === 'manual') {
                        manual.classList.replace('d-none', 'd-block');
                        auto.classList.replace('d-block', 'd-none');
                    } else {
                        auto.classList.replace('d-none', 'd-block');
                        manual.classList.replace('d-block', 'd-none');
                    }
                    } catch (e) {
                    errorMessage = getLocalizedMessage('code_toggle_failed', el);
                    }
                };
                el.addEventListener('click', onClick);
                new MutationObserver((m, obs) => {
                    m.forEach(mut => Array.from(mut.removedNodes).forEach(node => {
                    if (node === el) {
                        el.removeEventListener('click', onClick);
                        obs.disconnect();
                    }
                    }));
                }).observe(document.body, { childList: true, subtree: true });
                });
            
                const genBtn = document.getElementById('code-generate');
                if (genBtn && genBtn.dataset.listenerAttached !== 'true') {
                genBtn.dataset.listenerAttached = 'true';
                const onGenerate = () => {
                    try {
                    const length = 10;
                    const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
                    let result = Array.from({ length }, () =>
                        chars.charAt(Math.floor(Math.random() * chars.length))
                    ).join('');
                    const autoInput = document.getElementById('auto-code');
                    if (!autoInput) throw new Error('code_generation_failed');
                    autoInput.value = result;
                    } catch (e) {
                    errorMessage = getLocalizedMessage('code_generation_failed', genBtn);
                    }
                };
                genBtn.addEventListener('click', onGenerate);
                new MutationObserver((m, obs) => {
                    m.forEach(mut => Array.from(mut.removedNodes).forEach(node => {
                    if (node === genBtn) {
                        genBtn.removeEventListener('click', onGenerate);
                        obs.disconnect();
                    }
                    }));
                }).observe(document.body, { childList: true, subtree: true });
                }
            });
        })();
    </script>
<?php $__env->stopPush(); ?>
<?php $__env->startSection(YieldingConstants::ADM_PG_TTL); ?>
    <?php echo e(__('Manage Coupon')); ?>

<?php $__env->stopSection(); ?>
<?php $__env->startSection(YieldingConstants::ADM_BDC); ?>
    <li class="breadcrumb-item">
        <a href="<?php echo e(Route::has('dashboard') ? route('dashboard') : '#'); ?>"
        <?php echo e(Route::has('dashboard') ? '' : 'aria-disabled="true"'); ?>>
            <?php echo e(__('Dashboard')); ?>

        </a>
    </li>
    <li class="breadcrumb-item"><?php echo e(__('Coupon')); ?></li>
<?php $__env->stopSection(); ?>
<?php $__env->startSection(YieldingConstants::ADM_ACT_BTN); ?>
    <div class="<?php echo e(VC::FEND); ?>">
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('create coupon')): ?>
            <a
                id="<?php echo e($couponCreateBtnId); ?>"
                href="#"
                data-url="<?php echo e($couponCreateRoute); ?>"
                data-guard-msg="<?php echo e($couponCreateMsg); ?>"
                data-size="lg"
                data-ajax-popup="true"
                data-title="<?php echo e(__('Create New Coupon')); ?>"
                class="<?php echo e(VC::BT_SM_PM); ?>"
                data-bs-toggle="tooltip"
                title="<?php echo e(__('Create')); ?>"
            >
                <i class="<?php echo e(VC::TI_PLS); ?>"></i>
            </a>
            <?php $__env->startPush(StacksConstants::ADM_SCR_PG); ?>
                <script defer src="<?php echo e(asset('assets/js/routes/coupons/create.js')); ?>"></script>
            <?php $__env->stopPush(); ?>
        <?php endif; ?>
    </div>
<?php $__env->stopSection(); ?>
<?php $__env->startSection(YieldingConstants::ADM_CTT); ?>
    <div class="<?php echo e(VC::RW); ?>">
        <div class="<?php echo e(VC::CS12); ?>">
            <div class="<?php echo e(VC::CD); ?>">
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        <table class="<?php echo e(VC::TB); ?> datatable">
                            <thead>
                                <tr>
                                    <th><?php echo e(__('Name')); ?></th>
                                    <th><?php echo e(__('Code')); ?></th>
                                    <th><?php echo e(__('Discount (%)')); ?></th>
                                    <th><?php echo e(__('Limit')); ?></th>
                                    <th><?php echo e(__('Used')); ?></th>
                                    <th><?php echo e(__('Action')); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(Utility::isFilled($coupons) ?? []): ?>
                                    <?php $__currentLoopData = $coupons; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $coupon): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <?php
                                            $name     = !empty($coupon->name) ? $coupon->name : __('No coupon name available');
                                            $code     = !empty($coupon->code) ? $coupon->code : __('No coupon code available');
                                            $discount = is_numeric($coupon->discount) ? $coupon->discount : __('No discount available');
                                            $limit    = is_numeric($coupon->limit) ? $coupon->limit : __('No limit available');
                                            $used     = method_exists($coupon,'used_coupon') ? $coupon->used_coupon() : __('No used count available');
                                            $listLang  = $lang ?? Utility::fetchUserLang();
                                            $rowId         = (string) $coupon->id;
                                            $showName = VW::CPN . '.show';
                                            $showHref = Route::has($showName) ? route($showName, $coupon->id) : '#';
                                            $showBtnId     = 'coupon-show-btn-'    . $rowId;
                                            $guardShow = Utility::fetchLinkMessage($listLang, VW::CPN, 'coupon_show_route_unavailable')    ?? 'Coupon show route is unavailable. Please contact technical support or your domain administrator.';
                                        ?>
                                        <tr class="font-style">
                                            <td><?php echo e($name); ?></td>
                                            <td><?php echo e($code); ?></td>
                                            <td><?php echo e($discount); ?></td>
                                            <td><?php echo e($limit); ?></td>
                                            <td><?php echo e($used); ?></td>
                                            <td class="action text-end">
                                                <span>
                                                    <div class="<?php echo e(VC::ACT_BTN_WRN); ?>">
                                                        <a id="<?php echo e($showBtnId); ?>"
                                                        href="<?php echo e($showHref); ?>"
                                                        class="<?php echo e(VC::BT_SM_FL_CT); ?>"
                                                        data-bs-toggle="tooltip"
                                                        title="<?php echo e(__('View')); ?>"
                                                        data-sv-localized="true"
                                                        data-guard-msg="<?php echo e($guardShow); ?>">
                                                            <i class="<?php echo e(VC::TI_EYE_WT); ?>"></i>
                                                        </a>
                                                    </div>

                                                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('edit coupon')): ?>
                                                        <?php
                                                            $editName = VW::CPN . '.edit';
                                                            $editUrl  = Route::has($editName) ? route($editName, $coupon->id) : '#';
                                                            $editBtnId     = 'coupon-edit-btn-'    . $rowId;
                                                            $guardEdit = Utility::fetchLinkMessage($listLang, VW::CPN, 'coupon_edit_route_unavailable')    ?? 'Coupon edit route is unavailable. Please contact technical support or your domain administrator.';
                                                        ?>
                                                        <div class="<?php echo e(VC::ACT_BTN_PRIM); ?>">
                                                            <a id="<?php echo e($editBtnId); ?>"
                                                            href="#"
                                                            class="<?php echo e(VC::BT_SM_FL_CT); ?>"
                                                            data-url="<?php echo e($editUrl); ?>"
                                                            data-ajax-popup="true"
                                                            data-size="md"
                                                            data-bs-toggle="tooltip"
                                                            title="<?php echo e(__('Edit')); ?>"
                                                            data-title="<?php echo e(__('Edit Coupon')); ?>"
                                                            data-sv-localized="true"
                                                            data-guard-msg="<?php echo e($guardEdit); ?>">
                                                                <i class="<?php echo e(VC::TI_PC_WT); ?>"></i>
                                                            </a>
                                                        </div>
                                                        <?php $__env->startPush(StacksConstants::ADM_SCR_PG); ?>
                                                            <script defer>
                                                                (function(){
                                                                    try{
                                                                        function toastOrAlert(msg){
                                                                            try{
                                                                                if(window.bootstrap && window.bootstrap.Toast){
                                                                                    var t=document.getElementById('route-guard-toast');
                                                                                    if(!t){
                                                                                        t=document.createElement('div');
                                                                                        t.id='route-guard-toast';
                                                                                        t.className='toast align-items-center text-bg-danger border-0 position-fixed bottom-0 end-0 m-3';
                                                                                        t.setAttribute('role','alert');t.setAttribute('aria-live','assertive');t.setAttribute('aria-atomic','true');
                                                                                        t.innerHTML='<div class="d-flex"><div class="toast-body"></div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button></div>';
                                                                                        document.body.appendChild(t);
                                                                                    }
                                                                                    t.querySelector('.toast-body').textContent=msg;
                                                                                    new bootstrap.Toast(t,{delay:4000}).show();
                                                                                }else{ alert(msg); }
                                                                            }catch(e){ alert(msg); }
                                                                        }
                                                                        var editBtn=document.getElementById('<?php echo e($editBtnId); ?>');
                                                                        if(editBtn && editBtn.getAttribute('data-listener-active')!=='true'){
                                                                            editBtn.setAttribute('data-listener-active','true');
                                                                            editBtn.addEventListener('click',function(e){
                                                                                var url=(editBtn.getAttribute('data-url')||'').trim();
                                                                                if(!url || url==='#'){ e.preventDefault(); toastOrAlert(editBtn.getAttribute('data-guard-msg')||'#'); }
                                                                            });
                                                                        }
                                                                        var showBtn=document.getElementById('<?php echo e($showBtnId); ?>');
                                                                        if(showBtn && showBtn.getAttribute('data-listener-active')!=='true'){
                                                                            showBtn.setAttribute('data-listener-active','true');
                                                                            showBtn.addEventListener('click',function(e){
                                                                                var href=(showBtn.getAttribute('href')||'').trim();
                                                                                if(!href || href==='#'){ e.preventDefault(); toastOrAlert(showBtn.getAttribute('data-guard-msg')||'#'); }
                                                                            });
                                                                        }
                                                                    }catch(_){}
                                                                })();
                                                            </script>
                                                        <?php $__env->stopPush(); ?>
                                                    <?php endif; ?>

                                                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('delete coupon')): ?>
                                                        <?php
                                                            $delName  = VW::CPN . '.destroy';
                                                            $delHas   = Route::has($delName);
                                                            $destroyBtnId  = 'coupon-destroy-btn-' . $rowId;
                                                            $destroyFormId = 'delete-form-'        . $rowId;
                                                            $guardDel  = Utility::fetchLinkMessage($listLang, VW::CPN, 'coupon_destroy_route_unavailable') ?? 'Coupon delete route is unavailable. Please contact technical support or your domain administrator.';
                                                        ?>
                                                        <div class="<?php echo e(VC::ACT_BTN_DNG_2); ?>">
                                                            <?php
                                                                $delFormAttrs = ['method' => 'DELETE', 'id' => $destroyFormId, 'data-sv-localized' => 'true', 'data-guard-msg' => $guardDel];
                                                                if($delHas){ $delFormAttrs['route'] = [$delName, $coupon->id]; }
                                                                else{ $delFormAttrs['url'] = '#'; }
                                                            ?>
                                                            <?php echo Form::open($delFormAttrs); ?>

                                                                <a id="<?php echo e($destroyBtnId); ?>"
                                                                href="#"
                                                                class="<?php echo e(VC::BT_SM_CT_PR); ?>"
                                                                data-bs-toggle="tooltip"
                                                                title="<?php echo e(__('Delete')); ?>"
                                                                data-url="<?php echo e($delHas ? route($delName, $coupon->id) : '#'); ?>"
                                                                data-guard-msg="<?php echo e($guardDel); ?>"
                                                                data-confirm="<?php echo e(__(Utility::fetchLinkMessage($listLang, 'generics', 'are_you_sure') ?? 'Are You Sure?')); ?>|<?php echo e(__(Utility::fetchLinkMessage($listLang, 'generics', 'irreversible_action') ?? 'This action can not be undone. Do you want to continue?')); ?>"
                                                                data-confirm-yes="document.getElementById('<?php echo e($destroyFormId); ?>').submit();">
                                                                    <i class="<?php echo e(VC::TI_TRS_WT); ?>"></i>
                                                                </a>
                                                            <?php echo Form::close(); ?>

                                                        </div>
                                                        <?php $__env->startPush(StacksConstants::ADM_SCR_PG); ?>
                                                            <script defer>
                                                                (function(){
                                                                    try{
                                                                        function toastOrAlert(msg){
                                                                            try{
                                                                                if(window.bootstrap && window.bootstrap.Toast){
                                                                                    var t=document.getElementById('route-guard-toast');
                                                                                    if(!t){
                                                                                        t=document.createElement('div');
                                                                                        t.id='route-guard-toast';
                                                                                        t.className='toast align-items-center text-bg-danger border-0 position-fixed bottom-0 end-0 m-3';
                                                                                        t.setAttribute('role','alert');t.setAttribute('aria-live','assertive');t.setAttribute('aria-atomic','true');
                                                                                        t.innerHTML='<div class="d-flex"><div class="toast-body"></div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button></div>';
                                                                                        document.body.appendChild(t);
                                                                                    }
                                                                                    t.querySelector('.toast-body').textContent=msg;
                                                                                    new bootstrap.Toast(t,{delay:4000}).show();
                                                                                }else{ alert(msg); }
                                                                            }catch(e){ alert(msg); }
                                                                        }
                                                                        var delBtn=document.getElementById('<?php echo e($destroyBtnId); ?>');
                                                                        if(delBtn && delBtn.getAttribute('data-listener-active')!=='true'){
                                                                            delBtn.setAttribute('data-listener-active','true');
                                                                            delBtn.addEventListener('click',function(e){
                                                                                var url=(delBtn.getAttribute('data-url')||'').trim();
                                                                                if(!url || url==='#'){ e.preventDefault(); toastOrAlert(delBtn.getAttribute('data-guard-msg')||'#'); }
                                                                            });
                                                                        }
                                                                    }catch(_){}
                                                                })();
                                                            </script>
                                                        <?php $__env->stopPush(); ?>
                                                    <?php endif; ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6">
                                            <div class="text-center font-style"><?php echo e(__('No coupons available')); ?></div>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make(ExtendingLayoutsConstants::ADM, \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/aronboliveira/Desktop/programming/Prestech/erp/erpgo-fork/erp_prestech/_inc/laravel/resources/views/coupons/index.blade.php ENDPATH**/ ?>