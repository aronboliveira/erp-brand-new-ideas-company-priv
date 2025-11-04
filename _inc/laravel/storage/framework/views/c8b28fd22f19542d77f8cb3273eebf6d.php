<?php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        StacksConstants,
        ViewsConstants as VW,
        ViewClassNamesConstants as VC,
        YieldingConstants
    };
    use App\Models\{ProjectTask,Utility};
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\{Collection,Str};
    $lang = Utility::fetchUserLang();
    $projectIndexBaseName = VW::PRJ . '.index';
    $projectIndexKebabName = Str::kebab($projectIndexBaseName);
    $projectIndexResolvedName = Route::has($projectIndexBaseName) ? $projectIndexBaseName : (Route::has($projectIndexKebabName) ? $projectIndexKebabName : null);
    $projectIndexUrl = $projectIndexResolvedName ? route($projectIndexResolvedName) : '#';
    $projectIndexGuardMsg = Utility::fetchLinkMessage($lang, VW::PRJ, 'project_index_route_unavailable') ?? 'Project index route is unavailable. Please contact technical support or your domain administrator.';
?>
@endphp

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
        <script defer src="<?php echo e(asset('assets/js/routes/projects/tasks/index.js')); ?>"></script>
    <?php $__env->stopPush(); ?>
    <li class="breadcrumb-item"><?php echo e(__('Task')); ?></li>
<?php $__env->stopSection(); ?>
<?php $__env->startSection(YieldingConstants::ADM_ACT_BTN); ?>
    <div class="<?php echo e(VC::FEND); ?>">
        <div class="dropdown me-2">
            <a href="#"
            class="<?php echo e(VC::BT_SM_PM); ?> dropdown-toggle"
            role="button"
            data-bs-toggle="dropdown"
            aria-expanded="false">
                <span class="btn-inner--icon"><i class="ti ti-filter"></i></span>
            </a>
            <div class="<?php echo e(VC::DRP_MN_END); ?> dropdown-steady" id="task_sort">
                <a class="dropdown-item active" href="#" data-val="created_at-desc">
                    <i class="ti ti-sort-amount-down"></i><?php echo e(__('Newest')); ?>

                </a>
                <a class="dropdown-item" href="#" data-val="created_at-asc">
                    <i class="ti ti-sort-amount-up"></i><?php echo e(__('Oldest')); ?>

                </a>
                <a class="dropdown-item" href="#" data-val="name-asc">
                    <i class="ti ti-sort-alpha-down"></i><?php echo e(__('From A-Z')); ?>

                </a>
                <a class="dropdown-item" href="#" data-val="name-desc">
                    <i class="ti ti-sort-alpha-up"></i><?php echo e(__('From Z-A')); ?>

                </a>
            </div>
        </div>
        <div class="dropdown me-2">
            <a href="#"
            class="<?php echo e(VC::BT_SM_PM); ?> dropdown-toggle"
            role="button"
            data-bs-toggle="dropdown"
            aria-expanded="false">
                <span class="btn-inner--icon"><?php echo e(__('Status')); ?></span>
            </a>

            <div class="<?php echo e(VC::DRP_MN_END); ?> task-filter-actions dropdown-steady" id="task_status">
                <a class="dropdown-item filter-action filter-show-all ps-4" href="#"><?php echo e(__('Show All')); ?></a>
                <hr class="my-0">
                <a class="dropdown-item filter-action ps-4 active" href="#" data-val="see_my_tasks"><?php echo e(__('See My Tasks')); ?></a>
                <hr class="my-0">
                <?php
                    $priorities = ProjectTask::$priority;
                    $isCountable = Utility::isFilled($priorities ?? []);
                ?>
                <?php if($isCountable): ?>
                    <?php $__currentLoopData = $priorities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $val): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php if(!empty($key) && !empty($val)): ?>
                            <a class="dropdown-item filter-action ps-4" href="#" data-val="<?php echo e($key); ?>"><?php echo e(__($val)); ?></a>
                        <?php endif; ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <?php else: ?>
                    <a class="dropdown-item filter-action ps-4" href="#" data-val="no_priority"><?php echo e(__('No Priority')); ?></a>
                <?php endif; ?>
                <hr class="my-0">
                <a class="dropdown-item filter-action filter-other ps-4" href="#" data-val="due_today"><?php echo e(__('Due Today')); ?></a>
                <a class="dropdown-item filter-action filter-other ps-4" href="#" data-val="over_due"><?php echo e(__('Over Due')); ?></a>
                <a class="dropdown-item filter-action filter-other ps-4" href="#" data-val="starred"><?php echo e(__('Starred')); ?></a>
            </div>
        </div>
        <?php if($view == 'grid'): ?>
            <?php
                $taskboardViewBaseName = VW::TSKB . '.view';
                $taskboardViewKebabName = Str::kebab($taskboardViewBaseName);
                $taskboardViewResolvedName = Route::has($taskboardViewBaseName) ? $taskboardViewBaseName : (Route::has($taskboardViewKebabName) ? $taskboardViewKebabName : null);
                $taskboardViewUrl = $taskboardViewResolvedName ? route($taskboardViewResolvedName, 'list') : '#';
                $taskboardViewGuardMsg = Utility::fetchLinkMessage($lang, VW::TSK, 'taskboard_view_route_unavailable') ?? 'Taskboard view route is unavailable. Please contact technical support or your domain administrator.';
                $taskboardViewBtnId = 'taskboard-view-list-btn';
            ?>
            <a
                id="<?php echo e($taskboardViewBtnId); ?>"
                href="<?php echo e($taskboardViewUrl); ?>"
                data-url="<?php echo e($taskboardViewUrl); ?>"
                data-guard-msg="<?php echo e($taskboardViewGuardMsg); ?>"
                class="<?php echo e(VC::BT_SM_PM); ?>"
                data-bs-toggle="tooltip"
                title="<?php echo e(__('List View')); ?>"
            >
                <span class="btn-inner--text"><i class="ti ti-list"></i><?php echo e(__('List View')); ?></span>
            </a>
            <?php $__env->startPush(StacksConstants::ADM_SCR_PG); ?>
                <script defer src="<?php echo e(asset('assets/js/routes/projects/tasks/boardView.js')); ?>"></script>
            <?php $__env->stopPush(); ?>
        <?php else: ?>
            <?php
                $taskboardViewRouteName        = VW::TSKB . '.view';
                $taskboardViewKebabName        = Str::kebab($taskboardViewRouteName);
                $taskboardViewResolvedName     = Route::has($taskboardViewRouteName)
                    ? $taskboardViewRouteName
                    : (Route::has($taskboardViewKebabName) ? $taskboardViewKebabName : null);
                $taskboardGridViewUrl          = $taskboardViewResolvedName
                    ? route($taskboardViewResolvedName, 'grid')
                    : '#';
                $taskboardGridViewGuardMsg     = Utility::fetchLinkMessage(
                    $lang,
                    VW::TSK,
                    'taskboard_view_grid_route_unavailable'
                ) ?? 'Taskboard grid view route is unavailable. Please contact technical support or your domain administrator.';
                $taskboardGridViewBtnId        = 'taskboard-grid-view-btn';
            ?>
            <a
                id="<?php echo e($taskboardGridViewBtnId); ?>"
                href="<?php echo e($taskboardGridViewUrl); ?>"
                data-url="<?php echo e($taskboardGridViewUrl); ?>"
                data-guard-msg="<?php echo e($taskboardGridViewGuardMsg); ?>"
                class="<?php echo e(VC::BT_SM_PM); ?>"
                data-bs-toggle="tooltip"
                title="<?php echo e(__('Grid View')); ?>"
            >
                <span class="btn-inner--text"><i class="ti ti-table"></i></span>
            </a>
            <?php $__env->startPush(StacksConstants::ADM_SCR_PG); ?>
                <script defer src="<?php echo e(asset('assets/js/routes/projects/tasks/gridView.js')); ?>"></script>
            <?php $__env->stopPush(); ?>
        <?php endif; ?>
    </div>
<?php $__env->stopSection(); ?>
<?php $__env->startSection(YieldingConstants::ADM_CTT); ?>
    <div class="row min-750" id="taskboard_view"></div>
<?php $__env->stopSection(); ?>
<?php $__env->startPush(StacksConstants::ADM_SCR_PG); ?>
    <script async src="<?php echo e(asset('assets/js/routes/projects/tasks/lang/sort.js')); ?>"></script>
    <script defer>
        (()=>{
            const errFb='# ERROR';
            const dataClientLocalized='data-client-localized';
            const dataGuardMsg='data-guard-msg';
            const DATA_LISTENER_ADDED='data-listener-added';

            const getLocalizedMessage=(el,msgKey)=>{
            let msg=errFb;
            if(el?.getAttribute('data-sv-localized')==='true'||el?.getAttribute(dataClientLocalized)==='true'){
                msg=el.getAttribute(dataGuardMsg)||errFb;
            }else{
                let lang=(window.sessionStorage.getItem('erp-np-lang')||document.documentElement.lang||'en').toLowerCase().replace(/_/g,'-');
                lang=lang==='pt-br'?lang:lang.slice(0,2);
                msg=window.translations?.[lang]?.[msgKey]||el?.getAttribute(dataGuardMsg)||window.translations?.en?.[msgKey]||errFb;
                if(msg!==errFb){ el?.setAttribute(dataGuardMsg,msg); el?.setAttribute(dataClientLocalized,'true'); }
            }
            return msg;
            };

            const showError=(message)=>{
            const hasBootstrap=document.querySelector('link[href*="bootstrap"]')&&window.bootstrap?.Toast;
            if(hasBootstrap){
                if(!document.querySelector('#error-toast')){
                const toast=document.createElement('div');
                toast.id='error-toast';
                toast.className='toast align-items-center text-bg-danger border-0';
                toast.setAttribute('role','alert'); toast.setAttribute('aria-live','assertive'); toast.setAttribute('aria-atomic','true');
                toast.innerHTML=`<div class="d-flex"><div class="toast-body">${message}</div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button></div>`;
                document.body.appendChild(toast);
                }
                new bootstrap.Toast(document.querySelector('#error-toast')).show();
            }else{ alert(message); }
            };

            const attachGuardOnce=(el,key,ev='click')=>{
            if(!el||el.getAttribute(DATA_LISTENER_ADDED)==='true') return;
            const handler=()=>showError(getLocalizedMessage(el,key));
            el.addEventListener(ev,handler,{once:true});
            el.setAttribute(DATA_LISTENER_ADDED,'true');
            const obs=new MutationObserver((_,o)=>{ if(!document.body.contains(el)){ el.removeEventListener(ev,handler); o.disconnect(); }});
            obs.observe(document.body,{childList:true,subtree:true});
            };

            const ajaxFilterTaskView=(task_sort,keyword='',status=[])=>{
            const mainEle=$('#taskboard_view');
            if(!mainEle.length) return;
            const container=document.querySelector('.task-filter-actions');
            const urlAttr=container?.getAttribute('data-url')||'';
            const href=container?.getAttribute('action')||'';
            const endpoint = (urlAttr && urlAttr!=='#') ? urlAttr : '<?php echo e(route(VW::PRJ.".taskboard.view")); ?>';
            if((!urlAttr||urlAttr==='#') && (!href||href==='#')){ attachGuardOnce(container||document.body,'taskboard_unavailable'); return; }
            const view='<?php echo e($view); ?>';
            const data={ view, sort: task_sort, keyword, status };
            try{
                $.ajax({
                url:endpoint,
                data,
                success:(res)=>{ try{ mainEle.html(res?.html ?? res); }catch{ attachGuardOnce(mainEle.get(0),'taskboard_unavailable'); } },
                error:()=>attachGuardOnce(container||mainEle.get(0),'taskboard_unavailable')
                });
            }catch{ attachGuardOnce(container||mainEle.get(0),'taskboard_unavailable'); }
            };

            try{
                if(typeof $==="undefined"){ 
                    if (
                        window.location.hostname === "localhost" ||
                        window.location.hostname === "127.0.0.1"
                    ) console.error("jQuery unavailable");     
                    return; 
                }

            $(function(){
                let sort='created_at-desc';
                let status=[];
                ajaxFilterTaskView(sort,'',['see_my_tasks']);

                $('.task-filter-actions').on('click','.filter-action',function(e){
                try{
                    if($(this).hasClass('filter-show-all')){
                    $('.filter-action').removeClass('active'); $(this).addClass('active');
                    }else{
                    $('.filter-show-all').removeClass('active');
                    if($(this).hasClass('filter-other')) $('.filter-other').removeClass('active');
                    $(this).toggleClass('active').blur();
                    }
                    const filterArray=[];
                    $('div.task-filter-actions').find('.active').each(function(){ filterArray.push($(this).attr('data-val')); });
                    status=filterArray;
                    ajaxFilterTaskView(sort,$('#task_keyword').val()??'',status);
                }catch{ attachGuardOnce(this,'task_filter_unavailable','click'); }
                });

                $('#task_sort').on('click','a',function(){
                try{
                    sort=$(this).attr('data-val')||sort;
                    ajaxFilterTaskView(sort,$('#task_keyword').val()??'',status);
                    $('#task_sort a').removeClass('active'); $(this).addClass('active');
                }catch{ attachGuardOnce(this,'task_sort_unavailable','click'); }
                });

                $(document).on('keyup','#task_keyword',function(){
                try{ ajaxFilterTaskView(sort,$(this).val()??'',status); }catch{ /* defer visible error to pointerup */ }
                });
                const searchEl=document.getElementById('task_keyword');
                if(searchEl) attachGuardOnce(searchEl,'task_search_unavailable','pointerup');
            });

            }catch(e){ 
                if (
                    window.location.hostname === "localhost" ||
                    window.location.hostname === "127.0.0.1"
                ) {
                    console.error("Initialization failed", e);
                }
            }
        })();
    </script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make(ExtendingLayoutsConstants::ADM, \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/aronboliveira/Desktop/programming/Prestech/erp/erpgo-fork/erp_prestech/_inc/laravel/resources/views/project_tasks/taskboard.blade.php ENDPATH**/ ?>