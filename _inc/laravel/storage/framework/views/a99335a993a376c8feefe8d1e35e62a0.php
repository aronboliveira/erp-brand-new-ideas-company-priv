<?php

use App\Config\Constants\{
    ExtendingLayoutsConstants as EL,
    PermissionsConstants as PM,
    StacksConstants as ST,
    ViewsConstants as VW,
    ViewClassNamesConstants as VC,
    YieldingConstants as YD
};
use App\Models\Utility;
use Collective\Html\FormFacade as Form;
use Illuminate\Support\Facades\{Auth, Route};
use Illuminate\Support\Collection;

$profile      = Utility::getFile('uploads/avatar/');
$user         = Auth::user();
$canFetchMsg  = is_callable([Utility::class, 'fetchLinkMessage']);
$lang         = is_callable([Utility::class, 'fetchUserLang']) ? Utility::fetchUserLang(user: $user) : app()->getLocale();

$dashUrl      = Route::has('dashboard') ? route('dashboard') : '#';
$dashGuard    = ($canFetchMsg ? Utility::fetchLinkMessage($lang, 'generics', 'dashboard_unavailable') : 'Dashboard route is unavailable. Please contact technical support or your domain administrator.') ?? __('Dashboard route is unavailable. Please contact technical support or your domain administrator.');

$indexName    = VW::PRJ_RPT . '.index';
$indexRoute   = Route::has($indexName) ? [$indexName] : ['#'];
$indexUrl     = Route::has($indexName) ? route($indexName) : '#';
$indexGuard   = ($canFetchMsg ? Utility::fetchLinkMessage($lang, VW::PRJ_RPT, 'project_report_index_route_unavailable') : 'Project Reports index route is unavailable. Please contact technical support or your domain administrator.') ?? __('Project Reports index route is unavailable. Please contact technical support or your domain administrator.');
$resetClass   = 'reset-project-report-link';

$projectsList = [];
if (is_array($projects ?? null) && count($projects)) {
    $projectsList = $projects;
} elseif (($projects ?? null) instanceof Collection && $projects->isNotEmpty()) {
    $projectsList = $projects;
}

$usersList = [];
if (is_array($users ?? null) && count($users)) {
    $usersList = $users;
} elseif (($users ?? null) instanceof Collection && $users->isNotEmpty()) {
    $usersList = $users;
}
?>



<?php $__env->startPush(ST::ADM_SCR_PG); ?>
<?php $__env->stopPush(); ?>

<?php $__env->startSection(YD::ADM_PG_TTL); ?>
<?php echo e(__('Project Reports')); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('title'); ?>
<div class="d-inline-block">
    <h5 class="h4 d-inline-block font-weight-400 mb-0"><?php echo e(__('Project Reports')); ?></h5>
</div>
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
<li class="breadcrumb-item active" aria-current="page"><?php echo e(__('All Project')); ?></li>
<?php $__env->stopSection(); ?>

<?php $__env->startPush(ST::ADM_CSS); ?>
<link rel="stylesheet" href="<?php echo e(asset('css/datatable/buttons.dataTables.min.css')); ?>">
<link rel="stylesheet" href="<?php echo e(asset('assets/css/routes/projects/reports/index.css')); ?>">
<?php $__env->stopPush(); ?>

<?php $__env->startSection(YD::ADM_CTT); ?>
<?php if (($user?->type ?? null) === PM::CPN): ?>
    <div class="<?php echo e(VC::RW); ?>">
        <div class="<?php echo e(VC::C12); ?>">
            <div class="<?php echo e(VC::MT3); ?>">
                <div class="<?php echo e(VC::CD); ?>">
                    <div class="card-body">
                        <?php echo Form::open(['route' => $indexRoute, 'method' => 'GET', 'id' => 'project_report_submit']); ?>

                        <div class="<?php echo e(VC::R_FLX_ALC_JCE); ?>">
                            <div class="<?php echo e(VC::CL_POS2); ?> mb-0">
                                <div class="btn-box">
                                    <?php echo e(Form::label('users', __('Users'), ['class' => VC::FM_LB])); ?>

                                    <select class="select form-select" name="all_users" id="all_users">
                                        <option value=""><?php echo e(__('All Users')); ?></option>
                                        <?php if (Utility::isFilled($usersList) ?? []): ?>
                                            <?php $__currentLoopData = $usersList;
                                            $__env->addLoop($__currentLoopData);
                                            foreach ($__currentLoopData as $usr): $__env->incrementLoopIndices();
                                                $loop = $__env->getLastLoop(); ?>
                                                <option value="<?php echo e($usr->id); ?>" <?php echo e((string)request('all_users') === (string)$usr->id ? 'selected' : ''); ?>>
                                                    <?php echo e($usr->name); ?>

                                                </option>
                                            <?php endforeach;
                                            $__env->popLoop();
                                            $loop = $__env->getLastLoop(); ?>
                                        <?php endif; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="<?php echo e(VC::CL_POS1); ?>">
                                <div class="btn-box">
                                    <?php echo e(Form::label('status', __('Status'), ['class' => VC::FM_LB])); ?>

                                    <?php echo e(Form::select('status', ['' => __('Select Status')] + ($status ?? []), request('status'), ['class' => VC::FM_CT_SL])); ?>

                                </div>
                            </div>
                            <div class="<?php echo e(VC::CL_POS3); ?>">
                                <div class="btn-box">
                                    <?php echo e(Form::label('start_date', __('Start Date'), ['class' => VC::FM_LB])); ?>

                                    <?php echo e(Form::date('start_date', request('start_date', ''), ['class' => 'form-control month-btn'])); ?>

                                </div>
                            </div>
                            <div class="<?php echo e(VC::CL_POS3); ?>">
                                <div class="btn-box">
                                    <?php echo e(Form::label('end_date', __('End Date'), ['class' => VC::FM_LB])); ?>

                                    <?php echo e(Form::date('end_date', request('end_date', ''), ['class' => 'form-control month-btn'])); ?>

                                </div>
                            </div>
                            <div class="<?php echo e(VC::C_AT_FEND); ?>">
                                <a href="#" class="<?php echo e(VC::BT_SM_PM); ?>" onclick="document.getElementById('project_report_submit').submit();return false;" data-toggle="tooltip" data-original-title="<?php echo e(__('apply')); ?>">
                                    <span class="btn-inner--icon"><i class="<?php echo e(VC::TI_SRC); ?>"></i></span>
                                </a>
                                <a href="<?php echo e($indexUrl); ?>"
                                    id="<?php echo e($resetClass); ?>"
                                    class="<?php echo e(VC::BT_SM_DG); ?> <?php echo e($resetClass); ?>"
                                    data-url="<?php echo e($indexUrl); ?>"
                                    data-sv-localized="true"
                                    data-guard-msg="<?php echo e($indexGuard); ?>"
                                    data-toggle="tooltip"
                                    data-original-title="<?php echo e(__('Reset')); ?>">
                                    <span class="btn-inner--icon"><i class="<?php echo e(VC::TI_TRS_OFF); ?>"></i></span>
                                </a>
                            </div>
                        </div>
                        <?php echo Form::close(); ?>

                    </div>
                    <script defer src="<?php echo e(asset('assets/js/routes/projects/reports/reset.js')); ?>"></script>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<div class="<?php echo e(VC::C12); ?> <?php echo e(VC::MT3); ?>">
    <div class="<?php echo e(VC::CD); ?> table-card">
        <div class="card-header card-body table-border-style">
            <div class="table-responsive">
                <table class="table datatable">
                    <thead>
                        <tr>
                            <th><?php echo e(__('Projects')); ?></th>
                            <th><?php echo e(__('Start Date')); ?></th>
                            <th><?php echo e(__('Due Date')); ?></th>
                            <th><?php echo e(__('Projects Members')); ?></th>
                            <th><?php echo e(__('Completion')); ?></th>
                            <th><?php echo e(__('Status')); ?></th>
                            <th><?php echo e(__('Action')); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (Utility::isFilled($projectsList) ?? []): ?>
                            <?php $__currentLoopData = $projectsList;
                            $__env->addLoop($__currentLoopData);
                            foreach ($__currentLoopData as $proj): $__env->incrementLoopIndices();
                                $loop = $__env->getLastLoop(); ?>
                                <?php
                                $projId    = data_get($proj, 'id');
                                $projName  = data_get($proj, 'project_name', __('No project name available'));
                                $startRaw  = data_get($proj, 'start_date');
                                $endRaw    = data_get($proj, 'end_date');
                                $isDateFormatAvailable = is_callable([Utility::class, 'getDateFormated']);
                                $startTxt  = $isDateFormatAvailable && $startRaw ? Utility::getDateFormated($startRaw) : __('No start date');
                                $endTxt    = $isDateFormatAvailable && $endRaw ? Utility::getDateFormated($endRaw)   : __('No due date');
                                $projUsers = data_get($proj, 'users');

                                $usersSafe = [];
                                if (is_array($projUsers ?? null) && count($projUsers)) {
                                    $usersSafe = $projUsers;
                                } elseif (($projUsers ?? null) instanceof Collection && $projUsers->isNotEmpty()) {
                                    $usersSafe = $projUsers;
                                }

                                $showName  = VW::PRJ_RPT . '.show';
                                $showUrl   = Route::has($showName) ? route($showName, $projId) : '#';
                                $showGuard = ($canFetchMsg ? Utility::fetchLinkMessage($lang, VW::PRJ_RPT, 'show_project_report_unavailable') : 'View project report route is unavailable. Please contact technical support or your domain administrator.') ?? __('View project report route is unavailable. Please contact technical support or your domain administrator.');
                                $showClass = 'show-project-report-link';

                                $editName  = VW::PRJ . '.edit';
                                $editUrl   = Route::has($editName) ? route($editName, $projId) : '#';
                                $editGuard = ($canFetchMsg ? Utility::fetchLinkMessage($lang, VW::PRJ, 'project_report_edit_route_unavailable') : 'Edit project route is unavailable. Please contact technical support or your domain administrator.') ?? __('Edit project route is unavailable. Please contact technical support or your domain administrator.');
                                $editClass = 'edit-project-link';
                                ?>
                                <tr>
                                    <td>
                                        <div class="<?php echo e(VC::DFL_AIC); ?>">
                                            <p class="mb-0"><a class="<?php echo e(VC::NM_HD_SM); ?>"><?php echo e($projName); ?></a></p>
                                        </div>
                                    </td>
                                    <td><?php echo e($startTxt); ?></td>
                                    <td><?php echo e($endTxt); ?></td>
                                    <td>
                                        <div class="avatar-group" id="project_<?php echo e($projId); ?>">
                                            <?php if (Utility::isFilled($usersSafe) ?? []): ?>
                                                <?php $__currentLoopData = $usersSafe;
                                                $__env->addLoop($__currentLoopData);
                                                foreach ($__currentLoopData as $idx => $usr): $__env->incrementLoopIndices();
                                                    $loop = $__env->getLastLoop(); ?>
                                                    <?php if ($idx < 3): ?>
                                                        <a href="#" class="<?php echo e(VC::AV_CC); ?>">
                                                            <img src="<?php echo e($usr?->avatar ? asset('/storage/uploads/avatar/' . $usr->avatar) : asset('/storage/uploads/avatar/avatar.png')); ?>"
                                                                title="<?php echo e($usr?->name ?? ''); ?>" style="height:36px;width:36px;">
                                                        </a>
                                                    <?php else: ?>
                                                        <?php break; ?>
                                                    <?php endif; ?>
                                                <?php endforeach;
                                                $__env->popLoop();
                                                $loop = $__env->getLastLoop(); ?>
                                                <?php
                                                $extra = is_array($usersSafe) ? count($usersSafe) : $usersSafe->count();
                                                ?>
                                                <?php if ($extra > 3): ?>
                                                    <a href="#" class="<?php echo e(VC::AV_CC_SM); ?>">
                                                        <img avatar="+ <?php echo e($extra - 3); ?>" style="height:36px;width:36px;">
                                                    </a>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <?php echo e(__('-')); ?>

                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <?php
                                        $pp = method_exists($proj, 'projectProgress') && isset($last_task) && isset($last_task->id)
                                            ? $proj->projectProgress($proj, $last_task->id)
                                            : ['percentage' => '0%', 'color' => 'secondary'];
                                        ?>
                                        <h6 class="mb-0 text-success"><?php echo e($pp['percentage']); ?></h6>
                                        <div class="progress mb-0">
                                            <div class="progress-bar bg-<?php echo e($pp['color']); ?>" style="width: <?php echo e($pp['percentage']); ?>;"></div>
                                        </div>
                                    </td>
                                    <td>
                                        <?php
                                        $statusKey = data_get($proj, 'status');
                                        $stTxt = \App\Models\Project::$project_status[$statusKey] ?? __('Unknown');
                                        $stClr = \App\Models\Project::$status_color[$statusKey] ?? 'secondary';
                                        ?>
                                        <span class="badge bg-<?php echo e($stClr); ?> p-2 px-3 rounded status_badge"><?php echo e(__($stTxt)); ?></span>
                                    </td>
                                    <td>
                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check(PM::MNG_PRJ)): ?>
                                            <div class="<?php echo e(VC::ACT_BTN_WRN); ?>">
                                                <a href="<?php echo e($showUrl); ?>"
                                                    id="<?php echo e($showClass); ?>-<?php echo e($projId); ?>"
                                                    class="<?php echo e(VC::BT_SM_CT); ?> <?php echo e($showClass); ?>"
                                                    data-url="<?php echo e($showUrl); ?>"
                                                    data-sv-localized="true"
                                                    data-guard-msg="<?php echo e($showGuard); ?>"
                                                    data-bs-toggle="tooltip"
                                                    title="<?php echo e(__('View Project Report')); ?>"
                                                    data-original-title="<?php echo e(__('Detail')); ?>">
                                                    <i class="<?php echo e(VC::TI_EYE_WT); ?>"></i>
                                                </a>
                                            </div>
                                            <script defer src="<?php echo e(asset('assets/js/routes/projects/reports/show.js')); ?>"></script>
                                        <?php endif; ?>
                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('edit project')): ?>
                                            <div class="<?php echo e(VC::ACT_BTN_PRIM); ?>">
                                                <a href="<?php echo e($editUrl); ?>"
                                                    id="<?php echo e($editClass); ?>-<?php echo e($projId); ?>"
                                                    class="<?php echo e(VC::BT_SM_FL_CT); ?> <?php echo e($editClass); ?>"
                                                    data-url="<?php echo e($editUrl); ?>"
                                                    data-sv-localized="true"
                                                    data-guard-msg="<?php echo e($editGuard); ?>"
                                                    data-ajax-popup="true"
                                                    data-size="lg"
                                                    data-bs-toggle="tooltip"
                                                    title="<?php echo e(__('Edit')); ?>"
                                                    data-title="<?php echo e(__('Edit Project')); ?>">
                                                    <i class="<?php echo e(VC::TI_PC_WT); ?>"></i>
                                                </a>
                                            </div>
                                            <script defer src="<?php echo e(asset('assets/js/routes/projects/reports/edit.js')); ?>"></script>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach;
                            $__env->popLoop();
                            $loop = $__env->getLastLoop(); ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center text-muted"><?php echo e(__('No projects found.')); ?></td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>


<?php echo $__env->make(EL::ADM, \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/aronboliveira/Desktop/programming/Prestech/erp/erpgo-fork/erp_prestech/_inc/laravel/resources/views/project_reports/index.blade.php ENDPATH**/ ?>