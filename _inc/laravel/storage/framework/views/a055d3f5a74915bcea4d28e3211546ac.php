<?php
    use App\Config\Constants\ViewClassNamesConstants as VC;
    use App\Config\Constants\ViewsConstants as VW;
    use App\Config\Constants\StacksConstants;
    use App\Models\{Project, Utility};
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\Facades\Auth;
    use Illuminate\Support\Str;
    use Collective\Html\FormFacade as Form;
    $lang = Utility::fetchUserLang();
?>

<?php if(isset($projects) && !empty($projects) && count($projects) > 0): ?>
    <div class="col-12">
        <div class="row">
            <?php $__currentLoopData = $projects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $project): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                    $projectId = isset($project) && !empty(data_get($project, 'id')) ? data_get($project, 'id') : null;
                    $projectName = isset($project) && !empty(data_get($project, 'project_name')) ? data_get($project, 'project_name') : '';
                    $showBase = VW::PRJ . '.show';
                    $showKebab = Str::kebab($showBase);
                    $showResolved = Route::has($showBase) ? $showBase : (Route::has($showKebab) ? $showKebab : null);
                    $showParams = $projectId ? [$projectId] : ['#'];
                    $showUrl = ($showResolved && $projectId) ? route($showResolved, $showParams) : '#';
                    $showLinkId = 'project-show-link-' . ($projectId ?? 'x');
                    $showGuardMsg = Utility::fetchLinkMessage($lang, VW::PRJ, 'show_project_route_unavailable') ?? 'Show project route is unavailable. Please contact technical support or your domain administrator.';
                    $copyCandidates = [
                        'project.copy',
                        Str::kebab('project.copy'),
                        VW::PRJ . '.copy',
                        Str::kebab(VW::PRJ . '.copy'),
                    ];
                    $copyResolved = null;
                    foreach ($copyCandidates as $c) { if (Route::has($c)) { $copyResolved = $c; break; } }
                    $copyParams = $projectId ? [$projectId] : ['#'];
                    $copyUrl = ($copyResolved && $projectId) ? route($copyResolved, $copyParams) : '#';
                    $copyLinkId = 'project-copy-link-' . ($projectId ?? 'x');
                    $copyGuardMsg = Utility::fetchLinkMessage($lang, VW::PRJ, 'copy_project_unavailable') ?? 'Copy project route is unavailable. Please contact technical support or your domain administrator.';
                    $editBase = VW::PRJ . '.edit';
                    $editKebab = Str::kebab($editBase);
                    $editResolved = Route::has($editBase) ? $editBase : (Route::has($editKebab) ? $editKebab : null);
                    $editParams = $projectId ? [$projectId] : ['#'];
                    $editUrl = ($editResolved && $projectId) ? route($editResolved, $editParams) : '#';
                    $editLinkId = 'project-edit-link-' . ($projectId ?? 'x');
                    $editGuardMsg = Utility::fetchLinkMessage($lang, VW::PRJ, 'project_edit_route_unavailable') ?? 'Edit project route is unavailable. Please contact technical support or your domain administrator.';
                    $destroyBase = VW::PRJ . '.destroy';
                    $destroyKebab = Str::kebab($destroyBase);
                    $destroyResolved = Route::has($destroyBase) ? $destroyBase : (Route::has($destroyKebab) ? $destroyKebab : null);
                    $destroyParams = $projectId ? [$projectId] : ['#'];
                    $destroyUrl = ($destroyResolved && $projectId) ? route($destroyResolved, $destroyParams) : '#';
                    $deleteFormId = 'project-delete-form-' . ($projectId ?? 'x');
                    $deleteLinkId = 'project-delete-link-' . ($projectId ?? 'x');
                    $deleteGuardMsg = Utility::fetchLinkMessage($lang, VW::PRJ, 'delete_project_route_unavailable') ?? 'Delete project route is unavailable. Please contact technical support or your domain administrator.';
                    $areYouSureMsg = Utility::fetchLinkMessage($lang, 'generics', 'are_you_sure') ?? 'Are You Sure?';
                    $irreversibleMsg = Utility::fetchLinkMessage($lang, 'generics', 'irreversible_action') ?? 'This action can not be undone. Do you want to continue?';
                    $inviteCandidates = [
                        VW::PRJ . '.invite.member.view',
                        Str::kebab(VW::PRJ . '.invite.member.view'),
                    ];
                    $inviteResolved = null;
                    foreach ($inviteCandidates as $c) { if (Route::has($c)) { $inviteResolved = $c; break; } }
                    $inviteParams = $projectId ? [$projectId] : ['#'];
                    $inviteUrl = ($inviteResolved && $projectId) ? route($inviteResolved, $inviteParams) : '#';
                    $inviteLinkId = 'project-invite-link-' . ($projectId ?? 'x');
                    $inviteGuardMsg = Utility::fetchLinkMessage($lang, VW::PRJ, 'invite_project_member_unavailable') ?? 'Invite project member route is unavailable. Please contact technical support or your domain administrator.';
                ?>
                <?php if(isset($project) && is_object($project)): ?>
                    <div class="col-md-6 col-xxl-3">
                        <div class="card">
                            <div class="card-header border-0 pb-0">
                                <div class="d-flex align-items-center">
                                    <?php if(isset($project->img_image) && !empty($project->img_image)): ?>
                                        <img <?php echo e($project->img_image); ?> class="img-fluid wid-30 me-2" alt="">
                                    <?php else: ?>
                                        <img src="<?php echo e(asset('default-project-image.png')); ?>" class="img-fluid wid-30 me-2" alt="">
                                    <?php endif; ?>
                                    <h5 class="mb-0">
                                        <a class="text-dark" 
                                        id="<?php echo e(!empty($showLinkId) ? $showLinkId : 'show-link-default'); ?>" 
                                        href="<?php echo e(!empty($showUrl) ? $showUrl : '#'); ?>" 
                                        data-url="<?php echo e(!empty($showUrl) ? $showUrl : '#'); ?>" 
                                        data-guard-msg="<?php echo e(!empty($showGuardMsg) ? $showGuardMsg : ''); ?>">
                                            <?php echo e(!empty($projectName) ? $projectName : (data_get($project, 'name') ?: __('Unnamed Project'))); ?>

                                        </a>
                                    </h5>
                                </div>
                                <div class="card-header-right">
                                    <div class="btn-group card-option">
                                        <button type="button" class="btn dropdown-toggle" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            <i class="<?php echo e(defined('VC::TD_DOTV') ? VC::TD_DOTV : 'ti ti-dots-vertical'); ?>"></i>
                                        </button>
                                        <div class="<?php echo e(defined('VC::DRP_MN_EM') ? VC::DRP_MN_EM : 'dropdown-menu dropdown-menu-end'); ?>">
                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('create project')): ?>
                                                <?php if(!empty($copyUrl) && !empty($copyLinkId)): ?>
                                                    <a class="dropdown-item"
                                                    id="<?php echo e($copyLinkId); ?>"
                                                    data-ajax-popup="true"
                                                    data-size="md"
                                                    data-title="<?php echo e(__('Duplicate Project')); ?>"
                                                    href="<?php echo e($copyUrl); ?>"
                                                    data-url="<?php echo e($copyUrl); ?>"
                                                    data-guard-msg="<?php echo e(!empty($copyGuardMsg) ? $copyGuardMsg : ''); ?>">
                                                        <i class="ti ti-copy"></i> <span><?php echo e(__('Duplicate')); ?></span>
                                                    </a>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('edit project')): ?>
                                                <?php if(!empty($editUrl) && !empty($editLinkId)): ?>
                                                    <a href="<?php echo e($editUrl); ?>"
                                                    id="<?php echo e($editLinkId); ?>"
                                                    data-size="lg"
                                                    data-url="<?php echo e($editUrl); ?>"
                                                    data-ajax-popup="true"
                                                    class="dropdown-item"
                                                    data-bs-original-title="<?php echo e(__('Edit Project')); ?>"
                                                    data-guard-msg="<?php echo e(!empty($editGuardMsg) ? $editGuardMsg : ''); ?>">
                                                        <i class="<?php echo e(defined('VC::TI_PC') ? VC::TI_PC : 'ti ti-pencil'); ?>"></i>
                                                        <span><?php echo e(__('Edit')); ?></span>
                                                    </a>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('delete project')): ?>
                                                <?php if(!empty($destroyUrl) && !empty($deleteFormId) && !empty($deleteLinkId)): ?>
                                                    <?php echo Form::open(['method' => 'DELETE', 'url' => $destroyUrl, 'id' => $deleteFormId]); ?>

                                                        <a href="#!"
                                                        id="<?php echo e($deleteLinkId); ?>"
                                                        class="dropdown-item bs-pass-para"
                                                        data-url="<?php echo e($destroyUrl); ?>"
                                                        data-guard-msg="<?php echo e(!empty($deleteGuardMsg) ? $deleteGuardMsg : ''); ?>"
                                                        data-confirm="<?php echo e(!empty($areYouSureMsg) ? __($areYouSureMsg) : __('Are you sure?')); ?>|<?php echo e(!empty($irreversibleMsg) ? __($irreversibleMsg) : __('This action is irreversible.')); ?>"
                                                        data-confirm-yes="document.getElementById('<?php echo e($deleteFormId); ?>').submit();">
                                                            <i class="<?php echo e(defined('VC::TI_ARC') ? VC::TI_ARC : 'ti ti-archive'); ?>"></i>
                                                            <span><?php echo e(__('Delete')); ?></span>
                                                        </a>
                                                    <?php echo Form::close(); ?>

                                                <?php endif; ?>
                                            <?php endif; ?>
                                            
                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('edit project')): ?>
                                                <?php if(!empty($inviteUrl) && !empty($inviteLinkId)): ?>
                                                    <a href="<?php echo e($inviteUrl); ?>"
                                                    id="<?php echo e($inviteLinkId); ?>"
                                                    data-size="lg"
                                                    data-url="<?php echo e($inviteUrl); ?>"
                                                    data-ajax-popup="true"
                                                    class="dropdown-item"
                                                    data-bs-original-title="<?php echo e(__('Invite User')); ?>"
                                                    data-guard-msg="<?php echo e(!empty($inviteGuardMsg) ? $inviteGuardMsg : ''); ?>">
                                                        <i class="ti ti-send"></i>
                                                        <span><?php echo e(__('Invite User')); ?></span>
                                                    </a>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row g-2 justify-content-between">
                                    <div class="col-auto">
                                        <?php if(isset($project->status) && class_exists('Project') && 
                                            method_exists('Project', '__callStatic') && 
                                            isset(Project::$status_color) && 
                                            isset(Project::$project_status) &&
                                            is_array(Project::$status_color) && 
                                            is_array(Project::$project_status)): ?>
                                            <?php
                                                $statusColor = data_get(Project::$status_color, $project->status, 'secondary');
                                                $statusText = data_get(Project::$project_status, $project->status, 'Unknown');
                                            ?>
                                            <span class="badge rounded-pill bg-<?php echo e($statusColor); ?>"><?php echo e(__($statusText)); ?></span>
                                        <?php else: ?>
                                            <span class="badge rounded-pill bg-secondary"><?php echo e(__('Unknown Status')); ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <p class="text-muted text-sm mt-3">
                                    <?php echo e(data_get($project, 'description', __('No description available'))); ?>

                                </p>
                                <small><?php echo e(__('MEMBERS')); ?></small>
                                <div class="user-group">
                                    <?php if(isset($project->users) && 
                                        (is_array($project->users) || is_object($project->users)) && 
                                        !empty($project->users) && 
                                        count($project->users) > 0): ?>
                                        <?php $__currentLoopData = $project->users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ukey => $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <?php if(is_numeric($ukey) && $ukey < 3 && isset($user) && is_object($user)): ?>
                                                <a href="#" class="avatar rounded-circle avatar-sm">
                                                    <?php if(isset($user->avatar) && !empty($user->avatar) && is_string($user->avatar)): ?>
                                                        <img src="<?php echo e(asset('/storage/uploads/avatar/'.$user->avatar)); ?>" 
                                                            alt="image" 
                                                            data-bs-toggle="tooltip" 
                                                            title="<?php echo e(data_get($user, 'name', 'Unknown User')); ?>">
                                                    <?php else: ?>
                                                        <img src="<?php echo e(asset('/storage/uploads/avatar/avatar.png')); ?>" 
                                                            alt="image" 
                                                            data-bs-toggle="tooltip" 
                                                            title="<?php echo e(data_get($user, 'name', 'Unknown User')); ?>">
                                                    <?php endif; ?>
                                                </a>
                                            <?php elseif($ukey >= 3): ?>
                                                <?php break; ?>
                                            <?php endif; ?>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    <?php else: ?>
                                        <span class="text-muted text-sm"><?php echo e(__('No members assigned')); ?></span>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="card mb-0 mt-3">
                                    <div class="card-body p-3">
                                        <div class="row">
                                            <div class="col-6">
                                                <?php if(isset($project->start_date) && !empty($project->start_date)): ?>
                                                    <?php
                                                        $startDate = $project->start_date;
                                                        $isOverdue = false;
                                                        $formattedStartDate = $startDate;
                                                        if (class_exists('Utility') && method_exists('Utility', 'getDateFormated')) {
                                                            $formattedStartDate = Utility::getDateFormated($startDate);
                                                        } elseif (is_string($startDate)) {
                                                            try {
                                                                $formattedStartDate = date('M d, Y', strtotime($startDate));
                                                                $isOverdue = strtotime($startDate) < time();
                                                            } catch (Exception $e) {
                                                                $formattedStartDate = $startDate;
                                                            }
                                                        }
                                                    ?>
                                                    <h6 class="mb-0 <?php echo e($isOverdue ? 'text-danger' : ''); ?>"><?php echo e($formattedStartDate); ?></h6>
                                                <?php else: ?>
                                                    <h6 class="mb-0"><?php echo e(__('Not set')); ?></h6>
                                                <?php endif; ?>
                                                <p class="text-muted text-sm mb-0"><?php echo e(__('Start Date')); ?></p>
                                            </div>
                                            <div class="col-6 text-end">
                                                <?php if(isset($project->end_date) && !empty($project->end_date)): ?>
                                                    <?php
                                                        $endDate = $project->end_date;
                                                        $formattedEndDate = $endDate;
                                                        if (class_exists('Utility') && method_exists('Utility', 'getDateFormated')) {
                                                            $formattedEndDate = Utility::getDateFormated($endDate);
                                                        } elseif (is_string($endDate)) {
                                                            try {
                                                                $formattedEndDate = date('M d, Y', strtotime($endDate));
                                                            } catch (Exception $e) {
                                                                $formattedEndDate = $endDate;
                                                            }
                                                        }
                                                    ?>
                                                    <h6 class="mb-0"><?php echo e($formattedEndDate); ?></h6>
                                                <?php else: ?>
                                                    <h6 class="mb-0"><?php echo e(__('Not set')); ?></h6>
                                                <?php endif; ?>
                                                <p class="text-muted text-sm mb-0"><?php echo e(__('Due Date')); ?></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="col-md-6 col-xxl-3">
                        <div class="card">
                            <div class="card-body text-center">
                                <p class="text-muted"><?php echo e(__('Project data not available')); ?></p>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>
    <script>
        (() => {
            try {
                const guard = (el) => {
                    try {
                        const href = el.getAttribute('href') || '#';
                        const url = el.getAttribute('data-url') || href || '#';
                        if (href !== '#' || url !== '#') return false;
                        const msg = el.getAttribute('data-guard-msg') || 'Requested route is unavailable. Please contact technical support or your domain administrator.';
                        const hasBootstrap = document.querySelector('link[href*="bootstrap"]') && window.bootstrap && window.bootstrap.Toast;
                        let container = document.getElementById('toast-container');
                        if (!container) {
                            container = document.createElement('div');
                            container.id = 'toast-container';
                            container.className = 'position-fixed top-0 end-0 p-3';
                            document.body.appendChild(container);
                        }
                        if (hasBootstrap) {
                            const toast = document.createElement('div');
                            toast.className = 'toast';
                            toast.setAttribute('role', 'alert');
                            toast.setAttribute('aria-live', 'assertive');
                            toast.setAttribute('aria-atomic', 'true');
                            const body = document.createElement('div');
                            body.className = 'toast-body';
                            body.textContent = msg;
                            toast.appendChild(body);
                            container.appendChild(toast);
                            const inst = window.bootstrap.Toast.getOrCreateInstance(toast);
                            toast.addEventListener('hidden.bs.toast', function () { try { toast.remove(); } catch (err) {} });
                            inst.show();
                        } else {
                            alert(msg);
                        }
                        el.setAttribute('data-failed-route', 'true');
                        return true;
                    } catch (err) { return true; }
                };
                const addClick = (el, flagName) => {
                    try {
                        if (!el) return;
                        if (el.hasAttribute(flagName) && el.getAttribute(flagName) === 'true') return;
                        el.setAttribute(flagName, 'true');
                        el.addEventListener('click', function (e) {
                            try {
                                if (guard(el)) { e.preventDefault(); }
                            } catch (err) {}
                        }, { passive: false });
                    } catch (err) {}
                };
                const showLinks = document.querySelectorAll('a[id^="project-show-link-"]');
                for (let i = 0; i < showLinks.length; i++) { addClick(showLinks[i], 'data-show-listener'); }
                const copyLinks = document.querySelectorAll('a[id^="project-copy-link-"]');
                for (let i = 0; i < copyLinks.length; i++) { addClick(copyLinks[i], 'data-copy-listener'); }
                const editLinks = document.querySelectorAll('a[id^="project-edit-link-"]');
                for (let i = 0; i < editLinks.length; i++) { addClick(editLinks[i], 'data-edit-listener'); }
                const deleteLinks = document.querySelectorAll('a[id^="project-delete-link-"]');
                for (let i = 0; i < deleteLinks.length; i++) {
                    try {
                        const el = deleteLinks[i];
                        const flag = 'data-delete-listener';
                        if (el.hasAttribute(flag) && el.getAttribute(flag) === 'true') continue;
                        el.setAttribute(flag, 'true');
                        el.addEventListener('click', function (e) {
                            try {
                                const prevented = guard(el);
                                if (prevented) { e.preventDefault(); return; }
                            } catch (err) {}
                        }, { passive: false });
                    } catch (err) {}
                }
                const inviteLinks = document.querySelectorAll('a[id^="project-invite-link-"]');
                for (let i = 0; i < inviteLinks.length; i++) { addClick(inviteLinks[i], 'data-invite-listener'); }
            } catch (error) {}
        })();
    </script>
<?php else: ?>
    <div class="col-xl-12 col-lg-12 col-sm-12">
        <div class="card">
            <div class="card-body">
                <h6 class="text-center mb-0"><?php echo e(__('No Projects Found.')); ?></h6>
            </div>
        </div>
    </div>
<?php endif; ?><?php /**PATH /home/aronboliveira/Desktop/programming/Prestech/erp/erpgo-fork/erp_prestech/_inc/laravel/resources/views/projects/grid.blade.php ENDPATH**/ ?>