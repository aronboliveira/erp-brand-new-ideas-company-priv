<?php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        StacksConstants,
        ViewsConstants,
        ViewClassNamesConstants as VC,
        YieldingConstants
    };
    use App\Models\{ProjectTask,Utility};
    use App\Models\{ProjectTask,Utility};
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\{Collection,Str};
    use Illuminate\Support\{Collection,Str};
    $lang = Utility::fetchUserLang();
    $projectIndexBaseName = ViewsConstants::PRJ . '.index';
    $projectIndexKebabName = Str::kebab($projectIndexBaseName);
    $projectIndexResolvedName = Route::has($projectIndexBaseName) ? $projectIndexBaseName : (Route::has($projectIndexKebabName) ? $projectIndexKebabName : null);
    $projectIndexUrl = $projectIndexResolvedName ? route($projectIndexResolvedName) : '#';
    $projectIndexGuardMsg = Utility::fetchLinkMessage($lang, ViewsConstants::PRJ, 'project_index_route_unavailable') ?? 'Project index route is unavailable. Please contact technical support or your domain administrator.';
?>


<?php $__env->startSection(YieldingConstants::ADM_PG_TTL); ?>
    <?php echo e(__('Tasks')); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startSection(YieldingConstants::ADM_BDC); ?>
    <li class="breadcrumb-item">
        <a href="<?php echo e(Route::has('dashboard') ? route('dashboard') : '#'); ?>"
        <?php echo e(Route::has('dashboard') ? '' : 'aria-disabled="true"'); ?>>
            <?php echo e(__('Dashboard')); ?>

        </a>
    </li>
    <li class="breadcrumb-item">
        <a
            id="project-index-link"
            href="<?php echo e($projectIndexUrl); ?>"
            data-url="<?php echo e($projectIndexUrl); ?>"
            data-guard-msg="<?php echo e($projectIndexGuardMsg); ?>"
        >
            <?php echo e(__('Project')); ?>

        </a>
    </li>
    <?php $__env->startPush(StacksConstants::ADM_SCR_PG); ?>
        <script defer src="<?php echo e(asset('assets/js/routes/projects/tasks/gridIndex.js')); ?>"></script>
    <?php $__env->stopPush(); ?>
    <li class="breadcrumb-item"><?php echo e(__('Task')); ?></li>
<?php $__env->stopSection(); ?>

<?php
    $viewSafe = (string)($view ?? 'list');
    $tasksSafe = (isset($tasks) && (is_array($tasks) || $tasks instanceof Collection)) ? $tasks : [];
?>

<?php $__env->startSection(YieldingConstants::ADM_ACT_BTN); ?>
    <div class="<?php echo e(VC::FEND); ?>">
        <?php if($viewSafe === 'grid'): ?>
            <?php
                $taskboardListBaseName     = ViewsConstants::TSKB.'.view';
                $taskboardListKebabName    = Str::kebab($taskboardListBaseName);
                $taskboardListResolvedName = Route::has($taskboardListBaseName)
                    ? $taskboardListBaseName
                    : (Route::has($taskboardListKebabName) ? $taskboardListKebabName : null);
                $taskboardListParam        = 'list';
                $taskboardListUrl          = $taskboardListResolvedName ? route($taskboardListResolvedName, $taskboardListParam) : '#';
                $taskboardListGuardMsg     = Utility::fetchLinkMessage($lang, ViewsConstants::TSK, 'list_taskboard_route_unavailable') ?? 'List taskboard route is unavailable. Please contact technical support or your domain administrator.';
                $taskboardListLinkId       = 'taskboard-list-view-link';
                $taskboardListTitle        = __('List View');
            ?>
            <a href="<?php echo e($taskboardListUrl); ?>"
            id="<?php echo e($taskboardListLinkId); ?>"
            class="<?php echo e(VC::BT_SM_PM); ?>"
            data-url="<?php echo e($taskboardListUrl); ?>"
            data-guard-msg="<?php echo e($taskboardListGuardMsg); ?>"
            data-bs-toggle="tooltip"
            title="<?php echo e($taskboardListTitle); ?>">
                <span class="btn-inner--text"><i class="<?php echo e(VC::TI_LT); ?>"></i></span>
            </a>
            <?php $__env->startPush(StacksConstants::ADM_SCR_PG); ?>
                <script defer src="<?php echo e(asset('assets/js/routes/projects/tasks/boardList.js')); ?>"></script>
            <?php $__env->stopPush(); ?>
        <?php else: ?>
            <?php
                $taskboardViewBase = VW::TSKB.'.view';
                $taskboardViewKebab = Str::kebab($taskboardViewBase);
                $taskboardViewResolved = Route::has($taskboardViewBase) ? $taskboardViewBase : (Route::has($taskboardViewKebab) ? $taskboardViewKebab : null);
                $viewMode = 'grid';
                $taskboardViewUrl = $taskboardViewResolved ? route($taskboardViewResolved, $viewMode) : '#';
                $langValue = isset($lang) ? $lang : Utility::fetchUserLang();
                $taskboardViewGuardMsg = Utility::fetchLinkMessage($langValue, VW::TSK, 'view_taskboard_grid_route_unavailable') ?? 'View taskboard as grid route is unavailable. Please contact technical support or your domain administrator.';
                $taskboardViewAnchorId = 'taskboard-view-'.$viewMode;
            ?>
            <a id="<?php echo e($taskboardViewAnchorId); ?>"
            href="<?php echo e($taskboardViewUrl); ?>"
            class="<?php echo e(VC::BT_SM_PM); ?>"
            data-bs-toggle="tooltip"
            title="<?php echo e(__('Card View')); ?>"
            data-url="<?php echo e($taskboardViewUrl); ?>"
            data-guard-msg="<?php echo e($taskboardViewGuardMsg); ?>"
            data-sv-localized="true">
                <span class="btn-inner--text"><i class="ti ti-table"></i></span>
            </a>
            <?php $__env->startPush(StacksConstants::ADM_SCR_PG); ?>
                <script defer src="<?php echo e(asset('assets/js/routes/projects/tasks/grid.js')); ?>"></script>
            <?php $__env->stopPush(); ?>
        <?php endif; ?>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection(YieldingConstants::ADM_CTT); ?>
    <div class="<?php echo e(VC::RW); ?>">
        <div class="<?php echo e(VC::C12); ?>">
            <div class="<?php echo e(VC::CD); ?>">
                <div class="<?php echo e(VC::RW); ?>">
                    <?php if(count($tasksSafe) > 0): ?>
                        <?php $__currentLoopData = $tasksSafe; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $task): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php
                                $tid = data_get($task,'id');
                                $priority = (int)(data_get($task,'priority') ?? -1);
                                $priorityColors = is_array(ProjectTask::$priority_color ?? null) ? ProjectTask::$priority_color : [];
                                $priorityLabels = is_array(ProjectTask::$priority ?? null) ? ProjectTask::$priority : [];
                                $priorityColorKey = $priorityColors[$priority] ?? 'secondary';
                                $priorityLabel = $priorityLabels[$priority] ?? __('Unknown');
                                $borderColor = data_get($task,'priority_color');
                                $progressArr = method_exists($task,'taskProgress') ? (array) $task->taskProgress($task) : [];
                                $pctStr = (string)($progressArr['percentage'] ?? '0%');
                                $pctNum = (float)str_replace('%','',$pctStr);
                                $pctColor = (string)($progressArr['color'] ?? 'secondary');
                                $taskName = data_get($task,'name') ?? __('No task name available');
                                $projectId = data_get($task,'project.id') ?? '';
                                $taskFilesRel = data_get($task,'taskFiles');
                                $filesCount = is_countable($taskFilesRel) ? count($taskFilesRel) : ((is_object($taskFilesRel) && method_exists($taskFilesRel,'count')) ? $taskFilesRel->count() : 0);
                                $commentsRel = data_get($task,'comments');
                                $commentsCount = is_countable($commentsRel) ? count($commentsRel) : ((is_object($commentsRel) && method_exists($commentsRel,'count')) ? $commentsRel->count() : 0);
                                $checklistRel = data_get($task,'checklist');
                                $checklistCount = (is_object($checklistRel) && method_exists($checklistRel,'count')) ? $checklistRel->count() : (is_countable($checklistRel) ? count($checklistRel) : 0);
                                $checklistTotal = method_exists($task,'countTaskChecklist') ? (int)$task->countTaskChecklist() : $checklistCount;
                                $endDate = data_get($task,'end_date');
                                $endDateText = (!empty($endDate) && $endDate !== '0000-00-00') ? (Utility::getDateFormated($endDate) ?? '') : '';
                                $isOverdue = $endDateText && (strtotime((string)$endDate) < time());
                                $usersRel = method_exists($task,'users') ? $task->users() : [];
                                $usersArr = Utility::isFilled($usersRel ?? []) ? $usersRel : [];
                                $usersCount = is_countable($usersArr) ? count($usersArr) : 0;
                            ?>
                            <div class="<?php echo e(VC::CLMS3); ?>">
                                <div class="<?php echo e(VC::CD_NSD); ?> m-3 card-progress" id="<?php echo e($tid ?? ''); ?>" style="<?php echo e(!empty($borderColor) ? 'border-left: 2px solid '.$borderColor.' !important' : ''); ?>;">
                                    <div class="card-body">
                                        <div class="<?php echo e(VC::R_ALC); ?> mb-2">
                                            <div class="col-6">
                                                <span class="<?php echo e(VC::BDG); ?> p-2 <?php echo e(VC::PX3); ?> rounded bg-<?php echo e($priorityColorKey); ?>"><?php echo e(__($priorityLabel)); ?></span>
                                            </div>
                                            <div class="col-6 text-end">
                                                <?php if($pctNum > 0): ?>
                                                    <span class="<?php echo e(VC::TXSM); ?>"><?php echo e($pctStr); ?></span>
                                                    <div class="<?php echo e(VC::PG); ?>" style="top:0px">
                                                        <div class="progress-bar bg-<?php echo e($pctColor); ?>" role="progressbar" style="width: <?php echo e($pctStr); ?>;"></div>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <?php
                                            $taskIndexBase = VW::PRJ_TSK_C.'.index';
                                            $taskIndexKebab = Str::kebab($taskIndexBase);
                                            $taskIndexResolved = Route::has($taskIndexBase) ? $taskIndexBase : (Route::has($taskIndexKebab) ? $taskIndexKebab : null);
                                            $projIdValue = isset($projectId) ? $projectId : null;
                                            $taskIndexUrl = ($taskIndexResolved && $projIdValue) ? route($taskIndexResolved, $projIdValue) : '#';
                                            $langValue = isset($lang) ? $lang : Utility::fetchUserLang();
                                            $taskIndexGuardMsg = Utility::fetchLinkMessage($langValue, VW::PRJ_TSK_C, 'index_project_task_route_unavailable') ?? 'Index project task route is unavailable. Please contact technical support or your domain administrator.';
                                            $taskIndexAnchorId = 'project-task-index-'.($projIdValue ?? 'x').'-'.Str::slug($taskName ?? 'task','-');
                                        ?>
                                        <a id="<?php echo e($taskIndexAnchorId); ?>"
                                        class="<?php echo e(VC::H6); ?> task-name-break"
                                        href="<?php echo e($taskIndexUrl); ?>"
                                        data-url="<?php echo e($taskIndexUrl); ?>"
                                        data-guard-msg="<?php echo e($taskIndexGuardMsg); ?>"
                                        data-sv-localized="true">
                                            <?php echo e($taskName); ?>

                                        </a>
                                        <?php $__env->startPush(StacksConstants::ADM_SCR_PG); ?>
                                            <script defer>
                                                (() => {
                                                    try {
                                                        const el = document.getElementById('<?php echo e($taskIndexAnchorId); ?>');
                                                        if (!el) { return; }
                                                        if (el.getAttribute('data-listener-active') === 'true') { return; }
                                                        el.setAttribute('data-listener-active','true');
                                                        el.addEventListener('click',(e) => {
                                                            try {
                                                                const href = el.getAttribute('href') ?? '#';
                                                                const url = el.getAttribute('data-url') ?? href ?? '#';
                                                                if (url !== '#' && href !== '#') { return; }
                                                                e.preventDefault();
                                                                const msg = el.getAttribute('data-guard-msg') ?? 'Index project task route is unavailable. Please contact technical support or your domain administrator.';
                                                                const hasBootstrap = !!(document.querySelector('link[href*="bootstrap"]') && window.bootstrap);
                                                                let container = document.getElementById('toast-container');
                                                                if (!container) {
                                                                    container = document.createElement('div');
                                                                    container.id = 'toast-container';
                                                                    document.body.appendChild(container);
                                                                }
                                                                if (hasBootstrap) {
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
                                                                el.setAttribute('data-failed-route','true');
                                                            } catch (err) {}
                                                        });
                                                    } catch (err) {}
                                                })();
                                            </script>
                                        <?php $__env->stopPush(); ?>
                                        <div class="<?php echo e(VC::R_ALC); ?>">
                                            <div class="col-12">
                                                <div class="actions <?php echo e(VC::DFL_JCB); ?> mt-2 mb-2">
                                                    <?php if($filesCount > 0): ?>
                                                        <div class="action-item <?php echo e(VC::MR2); ?>"><i class="ti ti-paperclip <?php echo e(VC::MR2); ?>"></i><?php echo e($filesCount); ?></div>
                                                    <?php else: ?>
                                                        <div class="action-item <?php echo e(VC::MR2); ?>"><i class="ti ti-paperclip <?php echo e(VC::MR2); ?>"></i><?php echo e(__('No files found')); ?></div>
                                                    <?php endif; ?>
                                                    <?php if($commentsCount > 0): ?>
                                                        <div class="action-item <?php echo e(VC::MR2); ?>"><i class="ti ti-brand-hipchat <?php echo e(VC::MR2); ?>"></i><?php echo e($commentsCount); ?></div>
                                                    <?php else: ?>
                                                        <div class="action-item <?php echo e(VC::MR2); ?>"><i class="ti ti-brand-hipchat <?php echo e(VC::MR2); ?>"></i><?php echo e(__('No comments found')); ?></div>
                                                    <?php endif; ?>
                                                    <?php if($checklistCount > 0): ?>
                                                        <div class="action-item <?php echo e(VC::MR2); ?>"><i class="ti ti-list-check <?php echo e(VC::MR2); ?>"></i><?php echo e($checklistTotal); ?></div>
                                                    <?php else: ?>
                                                        <div class="action-item <?php echo e(VC::MR2); ?>"><i class="ti ti-list-check <?php echo e(VC::MR2); ?>"></i><?php echo e(__('No checklist items found')); ?></div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <?php if($endDateText): ?>
                                                    <small <?php if($isOverdue): ?> class="text-danger" <?php endif; ?>><?php echo e($endDateText); ?></small>
                                                <?php endif; ?>
                                            </div>
                                            <div class="col-6 text-end">
                                                <?php if($usersCount > 0): ?>
                                                    <div class="avatar-group">
                                                        <?php $__currentLoopData = $usersArr; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $u): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                            <?php if($key < 3): ?>
                                                                <?php
                                                                    $uName = data_get($u,'name') ?? '';
                                                                    $uAvatar = data_get($u,'avatar');
                                                                    $uSrc = !empty($uAvatar) ? asset('/storage/uploads/avatar/'.$uAvatar) : asset('/storage/uploads/avatar/avatar.png');
                                                                ?>
                                                                <a href="#" class="<?php echo e(VC::AV_CC_SM); ?>">
                                                                    <img class="hweb" data-original-title="<?php echo e($uName); ?>" src="<?php echo e($uSrc); ?>">
                                                                </a>
                                                            <?php else: ?>
                                                                <?php break; ?>
                                                            <?php endif; ?>
                                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                        <?php if($usersCount > 3): ?>
                                                            <span class="<?php echo e(VC::BDG); ?> rounded-circle"><?php echo e('+' . ($usersCount - 3)); ?></span>
                                                        <?php endif; ?>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <?php else: ?>
                        <div class="<?php echo e(VC::CM12); ?>">
                            <h6 class="text-center m-3"><?php echo e(__('No tasks found')); ?></h6>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush(StacksConstants::ADM_SCR_PG); ?>
    <script async src="<?php echo e(asset('assets/js/routes/projects/tasks/lang/sortGrid.js')); ?>"></script>
    <script defer>
        (function () {
            const $ = window.jQuery;
            const errFb = "# ERROR";
            const dataClientLocalized = "data-client-localized";
            const dataGuardMsg = "data-guard-msg";
            const dataSvLocalized = "data-sv-localized";
            const dataErrGuard = "data-error-guard";
            const boundFilter = "data-bound-filter";
            const boundSort = "data-bound-sort";
            const boundSearch = "data-bound-search";
            const qs = (s, r = document) => r.querySelector(s);
            const ensureToastContainer = () => {
                const id = "np-toast-container";
                let c = qs("#" + id);
                if (c) {
                return c;
                }
                c = document.createElement("div");
                c.id = id;
                c.setAttribute("aria-live", "polite");
                c.setAttribute("aria-atomic", "true");
                c.style.position = "fixed";
                c.style.top = "1rem";
                c.style.right = "1rem";
                document.body.appendChild(c);
                return c;
            };
            const showErrorNow = message => {
                const hasBootstrap =
                (qs('link[rel="stylesheet"][href*="bootstrap"]') ||
                    qs('link[href*="bootstrap"]')) &&
                window.bootstrap &&
                window.bootstrap.Toast;
                if (hasBootstrap) {
                const container = ensureToastContainer();
                let t = qs("#np-toast", container);
                if (!t) {
                    t = document.createElement("div");
                    t.id = "np-toast";
                    t.className = "toast";
                    t.setAttribute("role", "alert");
                    t.setAttribute("aria-live", "assertive");
                    t.setAttribute("aria-atomic", "true");
                    t.innerHTML =
                    '<div class="toast-header"><strong class="me-auto">Notice</strong><button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button></div><div class="toast-body"></div>';
                    container.appendChild(t);
                }
                const body = qs(".toast-body", t);
                if (body) {
                    body.textContent = message ?? errFb;
                }
                try {
                    new window.bootstrap.Toast(t, { autohide: true, delay: 4000 }).show();
                } catch (_) {
                    alert(message ?? errFb);
                }
                } else {
                alert(message ?? errFb);
                }
            };
            const scheduleInteractiveError = message => {
                const host = document.body;
                if (!host || host.getAttribute(dataErrGuard) === "true") {
                return;
                }
                host.setAttribute(dataErrGuard, "true");
                const once = () => {
                try {
                    showErrorNow(message);
                } finally {
                    host.removeAttribute(dataErrGuard);
                }
                };
                document.addEventListener("pointerup", once, { once: true });
                const mo = new MutationObserver((m, o) => {
                if (!document.body.contains(host)) {
                    document.removeEventListener("pointerup", once);
                    o.disconnect();
                }
                });
                mo.observe(document.documentElement, { childList: true, subtree: true });
            };
            const getMsg = (el, key) => {
                let msg = errFb;
                if (
                el?.getAttribute(dataSvLocalized) === "true" ||
                el?.getAttribute(dataClientLocalized) === "true"
                ) {
                msg = el.getAttribute(dataGuardMsg) || errFb;
                } else {
                let lang = (
                    window.sessionStorage.getItem("erp-np-lang") ||
                    document.documentElement.lang ||
                    "en"
                )
                    .toLowerCase()
                    .replace(/_/g, "-");
                lang = lang === "pt-br" ? lang : lang.slice(0, 2);
                const msgKey = key;
                msg =
                    window.translations?.[lang]?.[msgKey] ||
                    el?.getAttribute(dataGuardMsg) ||
                    window.translations?.en?.[msgKey] ||
                    errFb;
                if (el && msg !== errFb) {
                    el.setAttribute(dataGuardMsg, msg);
                    el.setAttribute(dataClientLocalized, "true");
                }
                }
                return msg;
            };
            const ajaxFilterTaskView = (task_sort, keyword = "", status = []) => {
                const mainEle = $("#taskboard_view");
                const containerEl = mainEle.get(0) ?? document.body;
                const explicit = '<?php echo e(route(VW::PRJ.".taskboard.view")); ?>';
                const urlAttr = mainEle.attr("data-url") || "";
                const hrefAttr = mainEle.is("form")
                ? mainEle.attr("action") || ""
                : mainEle.attr("href") || "";
                const url =
                explicit && explicit !== "#"
                    ? explicit
                    : urlAttr && urlAttr !== "#"
                    ? urlAttr
                    : hrefAttr;
                if (!url || url === "#") {
                scheduleInteractiveError(getMsg(containerEl, "task_view_unavailable"));
                return;
                }
                const view = '<?php echo e($view ?? ""); ?>';
                const data = {
                view: view,
                sort: task_sort ?? "created_at-desc",
                keyword: keyword ?? "",
                status: status ?? [],
                };
                $.ajax({
                url: url,
                data: data,
                cache: false,
                success: function (resp) {
                    try {
                    const html = (resp && resp.html) ?? resp;
                    if (mainEle.length) {
                        mainEle.html(html ?? "");
                    }
                    } catch (_) {
                    scheduleInteractiveError(
                        getMsg(containerEl, "task_view_unavailable")
                    );
                    }
                },
                error: function () {
                    scheduleInteractiveError(getMsg(containerEl, "ajax_unavailable"));
                },
                });
            };
            const bindFilterClicks = () => {
                const box = qs(".task-filter-actions");
                if (!box) {
                return;
                }
                if (box.getAttribute(boundFilter) === "true") {
                return;
                }
                box.setAttribute(boundFilter, "true");
                $(box).on("click.filterAction", ".filter-action", function (e) {
                const $btn = $(this);
                if ($btn.hasClass("filter-show-all")) {
                    $(".filter-action").removeClass("active");
                    $btn.addClass("active");
                } else {
                    $(".filter-show-all").removeClass("active");
                    if ($btn.hasClass("filter-other")) {
                    $(".filter-other").removeClass("active");
                    }
                    if ($btn.hasClass("active")) {
                    $btn.removeClass("active");
                    $btn.blur();
                    } else {
                    $btn.addClass("active");
                    }
                }
                const filterArray = [];
                $(box)
                    .find(".active")
                    .each(function () {
                    filterArray.push($(this).attr("data-val"));
                    });
                state.status = filterArray;
                ajaxFilterTaskView(
                    state.sort,
                    $("#task_keyword").val() ?? "",
                    state.status
                );
                });
                const mo = new MutationObserver((m, o) => {
                if (!document.body.contains(box)) {
                    $(box).off(".filterAction");
                    o.disconnect();
                }
                });
                mo.observe(document.body, { childList: true, subtree: true });
            };
            const bindSortClicks = () => {
                const wrap = qs("#task_sort");
                if (!wrap) {
                return;
                }
                if (wrap.getAttribute(boundSort) === "true") {
                return;
                }
                wrap.setAttribute(boundSort, "true");
                $(wrap).on("click.sort", "a", function () {
                state.sort = $(this).attr("data-val") ?? state.sort;
                ajaxFilterTaskView(
                    state.sort,
                    $("#task_keyword").val() ?? "",
                    state.status
                );
                $("#task_sort a").removeClass("active");
                $(this).addClass("active");
                });
                const mo = new MutationObserver((m, o) => {
                if (!document.body.contains(wrap)) {
                    $(wrap).off(".sort");
                    o.disconnect();
                }
                });
                mo.observe(document.body, { childList: true, subtree: true });
            };
            const bindSearchKeyup = () => {
                const input = qs("#task_keyword");
                if (!input) {
                return;
                }
                if (input.getAttribute(boundSearch) === "true") {
                return;
                }
                input.setAttribute(boundSearch, "true");
                $(document).on("keyup.searchTasks", "#task_keyword", function () {
                ajaxFilterTaskView(state.sort, $(this).val() ?? "", state.status);
                });
                const mo = new MutationObserver((m, o) => {
                if (!document.body.contains(input)) {
                    $(document).off("keyup.searchTasks");
                    o.disconnect();
                }
                });
                mo.observe(document.body, { childList: true, subtree: true });
            };
            const state = { sort: "created_at-desc", status: [] };
            const init = () => {
                if (!$ || !$.fn) {
                try {
                    if (
                        window.location.hostname === "localhost" ||
                        window.location.hostname === "127.0.0.1"
                    ) console.error("jQuery unavailable");     
                    return; 
                } catch (_) {}
                scheduleInteractiveError(getMsg(document.body, "plugin_unavailable"));
                return;
                }
                state.status = ["see_my_tasks"];
                ajaxFilterTaskView(
                state.sort,
                $("#task_keyword").val() ?? "",
                state.status
                );
                bindFilterClicks();
                bindSortClicks();
                bindSearchKeyup();
            };
            if (document.readyState === "loading") {
                document.addEventListener("DOMContentLoaded", init, { once: true });
            } else {
                init();
            }
        })();
    </script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make(ExtendingLayoutsConstants::ADM, \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/aronboliveira/Desktop/programming/Prestech/erp/erpgo-fork/erp_prestech/_inc/laravel/resources/views/project_tasks/grid.blade.php ENDPATH**/ ?>