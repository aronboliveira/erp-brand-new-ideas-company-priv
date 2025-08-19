@php
    use App\Config\Constants\{PlansConstants, ViewsConstants, ViewClassNamesConstants as VC};
    use App\Models\{User, Utility};
    use Illuminate\Support\Facades\{Auth, Route, Storage};
    use Illuminate\Support\Str;
    $user = Auth::user();
    $lang = Utility::fetchUserLang(user:$user);
    $taskId = data_get($task,'id');
    $projectId = data_get($task,'project_id');
    $estHrsText = (data_get($task,'estimated_hrs') !== null && data_get($task,'estimated_hrs') !== '') ? number_format((float)data_get($task,'estimated_hrs')) : '-';
    $milestoneTitle = data_get($task,'milestone.title') ?? '-';
    $allowProgress = (string)($allow_progress ?? '');
    $progressVal = (int)(data_get($task,'progress') ?? 0);
    $descText = (data_get($task,'description') !== null && data_get($task,'description') !== '') ? data_get($task,'description') : '-';
    $checklistIter = is_iterable(data_get($task,'checklist')) ? data_get($task,'checklist') : [];
    $filesIter = is_iterable(data_get($task,'taskFiles')) ? data_get($task,'taskFiles') : [];
    $activityIter = (is_object($task) && method_exists($task,'activityLog')) ? $task->activityLog() : [];
    $commentsIter = is_iterable(data_get($task,'comments')) ? data_get($task,'comments') : [];
    $plan = Utility::getChatGPTSettings();
    $hasGpt = (int)(data_get($plan, PlansConstants::COL_GPT) ?? 0) === 1;
    $userAvatar = data_get($user,'avatar');
    $userAvatarSrc = !empty($userAvatar) ? asset('/storage/uploads/avatar/'.$userAvatar) : asset('/storage/uploads/avatar/avatar.png');
    $userName = data_get($user,'name') ?? '';
@endphp
<div class="modal-body task-id" id="{{ $taskId ?? 'x' }}">
    <div class="{{ VC::CD }}">
        <div class="card-body">
            <h5>{{ __('Task Detail') }}</h5>
            <div class="{{ VC::RW }} {{ VC::MT4 }}">
                <div class="col-md-4 col-sm-6">
                    <div class="d-flex align-items-start">
                        <div class="{{ VC::MS2 }}">
                            <p class="{{ VC::TXT_MT }} {{ VC::TXSM }} {{ VC::MB0 }}">{{ __('Estimated Hours') }}</p>
                            <h3 class="{{ VC::MB0 }} text-success">{{ $estHrsText }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 col-sm-6 my-3 my-sm-0">
                    <div class="d-flex align-items-start">
                        <div class="{{ VC::MS2 }}">
                            <p class="{{ VC::TXT_MT }} {{ VC::TXSM }} {{ VC::MB0 }}">{{ __('Milestone') }}</p>
                            <h3 class="{{ VC::MB0 }} text-primary">{{ $milestoneTitle }}</h3>
                        </div>
                    </div>
                </div>
                @if($allowProgress === 'false')
                    <div class="col-md-4 col-sm-6">
                        <div class="d-flex align-items-start">
                            <div class="{{ VC::MS2 }}">
                                <p class="{{ VC::TXT_MT }} {{ VC::TXSM }} {{ VC::MB0 }}">{{ __('Task Progress') }}</p>
                                <h3 class="{{ VC::MB0 }} text-danger"><b id="t_percentage">{{ $progressVal }}</b>%</h3>
                                <div class="{{ VC::PG }} {{ VC::MB0 }}">
                                    <div id="progress-result" class="tab-pane tab-example-result fade show active" role="tabpanel" aria-labelledby="progress-result-tab">
                                        @php
                                            $taskProgressChangeBaseName     = ViewsConstants::PRJ_TSK_C.'.change.progress';
                                            $taskProgressChangeKebabName    = Str::kebab($taskProgressChangeBaseName);
                                            $taskProgressChangeResolvedName = Route::has($taskProgressChangeBaseName)
                                                ? $taskProgressChangeBaseName
                                                : (Route::has($taskProgressChangeKebabName) ? $taskProgressChangeKebabName : null);
                                            $projectIdVal                   = isset($projectId) && !empty($projectId) ? $projectId : (isset($projectIdSafe) && !empty($projectIdSafe) ? $projectIdSafe : null);
                                            $taskIdVal                      = isset($taskId) && !empty($taskId) ? $taskId : null;
                                            $taskProgressChangeUrl          = ($taskProgressChangeResolvedName && $projectIdVal && $taskIdVal) ? route($taskProgressChangeResolvedName, [$projectIdVal, $taskIdVal]) : '#';
                                            $taskProgressGuardMsg           = Utility::fetchLinkMessage($lang, ViewsConstants::PRJ_TSK_C, 'change_task_progress_route_unavailable') ?? 'Change task progress route is unavailable. Please contact technical support or your domain administrator.';
                                            $taskProgressInputId            = 'task-progress-input-'.($taskIdVal ?? 'x');
                                            $taskProgressValue              = isset($progressVal) ? $progressVal : 0;
                                        @endphp
                                        <input type="range"
                                            class="task_progress custom-range"
                                            value="{{ $taskProgressValue }}"
                                            id="{{ $taskProgressInputId }}"
                                            name="progress"
                                            data-url="{{ $taskProgressChangeUrl }}"
                                            data-guard-msg="{{ $taskProgressGuardMsg }}">
                                        <script defer>
                                            (() => {
                                                try {
                                                    const r = document.getElementById('{{ $taskProgressInputId }}');
                                                    if (!r || r.getAttribute('data-listener-active') === 'true') return;
                                                    r.setAttribute('data-listener-active', 'true');
                                                    r.addEventListener('change', e => {
                                                        try {
                                                            const url = r.getAttribute('data-url') || '#';
                                                            if (url !== '#') return;
                                                            e.preventDefault();
                                                            const msg = r.getAttribute('data-guard-msg') || 'Change task progress route is unavailable. Please contact technical support or your domain administrator.';
                                                            const hasBootstrap = document.querySelector('link[href*="bootstrap"]') && window.bootstrap;
                                                            let container = document.getElementById('toast-container');
                                                            if (!container) {
                                                                container = document.createElement('div');
                                                                container.id = 'toast-container';
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
                                                                bootstrap.Toast.getOrCreateInstance(toast).show();
                                                            } else {
                                                                alert(msg);
                                                            }
                                                            r.setAttribute('data-failed-route', 'true');
                                                        } catch (err) {}
                                                    });
                                                } catch (error) {}
                                            })();
                                        </script>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
            <div class="{{ VC::RW }} {{ VC::MT4 }}">
                <div class="col">
                    <p class="{{ VC::TXSM }} {{ VC::TXT_MT }} mb-2">{{ $descText }}</p>
                </div>
            </div>
        </div>
    </div>
    <div class="{{ VC::CD }}">
        <div class="card-body">
            <div class="{{ VC::R_ALC_M4 }}">
                <div class="col-6">
                    <h5>{{ __('Checklist') }}</h5>
                </div>
                <div class="col-6">
                    <div class="{{ VC::FEND }}">
                        <a data-bs-toggle="collapse" href="#form-checklist" role="button" aria-expanded="false" aria-controls="form-checklist" data-bs-toggle="tooltip" title="{{ __('Add item') }}" class="{{ VC::BT_SM_PM }}">
                            <i class="{{ VC::TI_PLS }}"></i>
                        </a>
                    </div>
                </div>
            </div>
            <div class="checklist" id="checklist">
                @php
                    $taskChecklistStoreBaseName     = ViewsConstants::PRJ_TSK_C.'.checklist.store';
                    $taskChecklistStoreKebabName    = Str::kebab($taskChecklistStoreBaseName);
                    $taskChecklistStoreResolvedName = Route::has($taskChecklistStoreBaseName)
                        ? $taskChecklistStoreBaseName
                        : (Route::has($taskChecklistStoreKebabName) ? $taskChecklistStoreKebabName : null);
                    $projectIdVal                   = isset($projectId) && !empty($projectId) ? $projectId : (isset($projectIdSafe) && !empty($projectIdSafe) ? $projectIdSafe : null);
                    $taskIdVal                      = isset($taskId) && !empty($taskId) ? $taskId : null;
                    $taskChecklistStoreUrl          = ($taskChecklistStoreResolvedName && $projectIdVal && $taskIdVal) ? route($taskChecklistStoreResolvedName, [$projectIdVal, $taskIdVal]) : '#';
                    $taskChecklistGuardMsg          = Utility::fetchLinkMessage($lang, ViewsConstants::PRJ_TSK_C, 'store_task_checklist_route_unavailable') ?? 'Store task checklist route is unavailable. Please contact technical support or your domain administrator.';
                    $taskChecklistFormId            = 'form-checklist';
                @endphp
                <form id="{{ $taskChecklistFormId }}"
                    class="{{ VC::CLP }} pb-2"
                    method="post"
                    action="{{ $taskChecklistStoreUrl }}"
                    data-url="{{ $taskChecklistStoreUrl }}"
                    data-guard-msg="{{ $taskChecklistGuardMsg }}">
                    @csrf
                    <div class="{{ VC::CD_NSD }}">
                        <div class="{{ VC::R_ALC_SMPD }}">
                            @csrf
                            <div class="col-10">
                                <input type="text" name="name" required class="{{ VC::FM_CT }}" placeholder="{{ __('Checklist Name') }}"/>
                            </div>
                            <div class="{{ VC::CL_MT_VC }}">
                                <button class="{{ VC::BT_SM_PM }}" type="button" id="checklist_submit" data-bs-toggle="tooltip" title="{{ __('Create') }}">
                                    <i class="ti ti-check"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
                <script defer src="{{ asset('assets/js/routes/projectTasks/checklists/store.js') }}">
                </script>
                @forelse($checklistIter as $checklist)
                    @php
                        $chkIdVal                    = data_get($checklist, 'id');
                        $chkNameText                 = data_get($checklist, 'name') ?? __('No checklist name available');
                        $chkStatusBool               = (bool)(data_get($checklist, 'status') ?? false);
                        $projectIdVal                = isset($projectId) && !empty($projectId) ? $projectId : (isset($projectIdSafe) && !empty($projectIdSafe) ? $projectIdSafe : null);
                        $taskIdVal                   = isset($taskId) && !empty($taskId) ? $taskId : null;
                        $taskChecklistUpdateBase     = ViewsConstants::PRJ_TSK_C.'.checklist.update';
                        $taskChecklistUpdateKebab    = Str::kebab($taskChecklistUpdateBase);
                        $taskChecklistUpdateResolved = Route::has($taskChecklistUpdateBase)
                            ? $taskChecklistUpdateBase
                            : (Route::has($taskChecklistUpdateKebab) ? $taskChecklistUpdateKebab : null);
                        $taskChecklistDestroyBase     = ViewsConstants::PRJ_TSK_C.'.checklist.destroy';
                        $taskChecklistDestroyKebab    = Str::kebab($taskChecklistDestroyBase);
                        $taskChecklistDestroyResolved = Route::has($taskChecklistDestroyBase)
                            ? $taskChecklistDestroyBase
                            : (Route::has($taskChecklistDestroyKebab) ? $taskChecklistDestroyKebab : null);
                        $chkUpdateUrl                = ($taskChecklistUpdateResolved && $projectIdVal && $chkIdVal) ? route($taskChecklistUpdateResolved, [$projectIdVal, $chkIdVal]) : '#';
                        $chkDestroyUrl               = ($taskChecklistDestroyResolved && $projectIdVal && $chkIdVal) ? route($taskChecklistDestroyResolved, [$projectIdVal, $chkIdVal]) : '#';
                        $chkUpdateGuardMsg           = Utility::fetchLinkMessage($lang, ViewsConstants::PRJ_TSK_C, 'update_task_checklist_route_unavailable') ?? 'Change checklist status route is unavailable. Please contact technical support or your domain administrator.';
                        $chkDestroyGuardMsg          = Utility::fetchLinkMessage($lang, ViewsConstants::PRJ_TSK_C, 'delete_task_checklist_route_unavailable') ?? 'Delete task checklist route is unavailable. Please contact technical support or your domain administrator.';
                        $chkInputId                  = 'check-item-'.($chkIdVal ?? 'x');
                        $chkDeleteLinkId             = 'delete-checklist-link-'.($chkIdVal ?? 'x');
                    @endphp
                    <div class="{{ VC::CD_NSD }} checklist-member">
                        <div class="{{ VC::R_ALC_SMPD }}">
                            <div class="col">
                                <div class="{{ VC::FM_CHK_IL }}">
                                    <input type="checkbox"
                                        class="form-check-input"
                                        id="{{ $chkInputId }}"
                                        @if($chkStatusBool) checked @endif
                                        data-url="{{ $chkUpdateUrl }}"
                                        data-guard-msg="{{ $chkUpdateGuardMsg }}">
                                    <label class="form-check-label {{ VC::H6 }} {{ VC::TXSM }}" for="{{ $chkInputId }}">{{ $chkNameText }}</label>
                                </div>
                            </div>
                            <div class="col-auto">
                                <div class="{{ VC::ACT_BTN_DNG_2 }}">
                                    <a href="#"
                                    id="{{ $chkDeleteLinkId }}"
                                    class="{{ VC::BT_SM_CT }} delete-checklist"
                                    data-url="{{ $chkDestroyUrl }}"
                                    data-guard-msg="{{ $chkDestroyGuardMsg }}">
                                        <i data-bs-toggle="tooltip" title="{{ __('Delete') }}" class="{{ VC::TI_TRS_WT }}"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <script defer>
                        (() => {
                            try {
                                const cb = document.getElementById('{{ $chkInputId }}');
                                if (cb && cb.getAttribute('data-listener-active') !== 'true') {
                                    cb.setAttribute('data-listener-active', 'true');
                                    cb.addEventListener('change', e => {
                                        try {
                                            const url = cb.getAttribute('data-url') || '#';
                                            if (url !== '#') return;
                                            e.preventDefault();
                                            const msg = cb.getAttribute('data-guard-msg') || 'Change checklist status route is unavailable. Please contact technical support or your domain administrator.';
                                            const hasBootstrap = document.querySelector('link[href*="bootstrap"]') && window.bootstrap;
                                            let container = document.getElementById('toast-container');
                                            if (!container) {
                                                container = document.createElement('div');
                                                container.id = 'toast-container';
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
                                                bootstrap.Toast.getOrCreateInstance(toast).show();
                                            } else {
                                                alert(msg);
                                            }
                                            cb.setAttribute('data-failed-route', 'true');
                                        } catch (err) {}
                                    });
                                }
                            } catch (error) {}
                            try {
                                const dl = document.getElementById('{{ $chkDeleteLinkId }}');
                                if (dl && dl.getAttribute('data-listener-active') !== 'true') {
                                    dl.setAttribute('data-listener-active', 'true');
                                    dl.addEventListener('click', e => {
                                        try {
                                            const href = dl.getAttribute('href') || '#';
                                            const url = dl.getAttribute('data-url') || href || '#';
                                            if (href !== '#' || url !== '#') return;
                                            e.preventDefault();
                                            const msg = dl.getAttribute('data-guard-msg') || 'Delete task checklist route is unavailable. Please contact technical support or your domain administrator.';
                                            const hasBootstrap = document.querySelector('link[href*="bootstrap"]') && window.bootstrap;
                                            let container = document.getElementById('toast-container');
                                            if (!container) {
                                                container = document.createElement('div');
                                                container.id = 'toast-container';
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
                                                bootstrap.Toast.getOrCreateInstance(toast).show();
                                            } else {
                                                alert(msg);
                                            }
                                            dl.setAttribute('data-failed-route', 'true');
                                        } catch (err) {}
                                    });
                                }
                            } catch (error) {}
                        })();
                    </script>
                @empty
                    <small class="{{ VC::TXT_MT }}">{{ __('No checklist items available') }}</small>
                @endforelse
            </div>
        </div>
    </div>
    <div class="{{ VC::CD }}">
        <div class="card-body">
            <div class="{{ VC::R_ALC_M4 }}">
                <div class="col-6">
                    <h5>{{ __('Attachments') }}</h5>
                </div>
                <div class="col-6">
                    <div class="{{ VC::FEND }}">
                        <a data-bs-toggle="collapse" href="#add_file" role="button" aria-expanded="false" aria-controls="add_file" data-bs-toggle="tooltip" title="{{ __('Add attachment') }}" class="{{ VC::BT_SM_PM }}">
                            <i class="{{ VC::TI_PLS }}"></i>
                        </a>
                    </div>
                </div>
            </div>
            <div class="attachments" id="attachments">
                <form id="add_file" class="{{ VC::CLP }} pb-2">
                    <div class="{{ VC::CD_NSD }}">
                        <div class="{{ VC::R_ALC_SMPD }}">
                            @csrf
                            <div class="col-10">
                                <input type="file" name="task_attachment" id="task_attachment" onchange="document.getElementById('blah').src = window.URL.createObjectURL(this.files[0])" required class="{{ VC::FM_CT }}"/>
                            </div>
                            <div class="{{ VC::CL_MT_VC }}">
                                @php
                                    $fileAttachRouteBase        = ViewsConstants::PRJ_TSK_C.'.comment.store.file';
                                    $fileAttachRouteKebab       = Str::kebab($fileAttachRouteBase);
                                    $fileAttachResolvedRoute    = Route::has($fileAttachRouteBase) ? $fileAttachRouteBase : (Route::has($fileAttachRouteKebab) ? $fileAttachRouteKebab : null);
                                    $fileAttachParams           = (isset($projectId, $taskId) && $projectId && $taskId) ? [$projectId, $taskId] : ['#'];
                                    $fileAttachUrl              = ($fileAttachResolvedRoute && isset($projectId, $taskId) && $projectId && $taskId) ? route($fileAttachResolvedRoute, $fileAttachParams) : '#';
                                    $fileAttachGuardMsg         = Utility::fetchLinkMessage($lang, ViewsConstants::PRJ_TSK_C, 'store_file_project_task_comment_unavailable') ?? 'Store file for project task comment route is unavailable. Please contact technical support or your domain administrator.';
                                @endphp
                                <button
                                    class="{{ VC::BT_SM_PM }}"
                                    type="button"
                                    id="file_attachment_submit"
                                    data-url="{{ $fileAttachUrl }}"
                                    data-action="{{ $fileAttachUrl }}"
                                    data-guard-msg="{{ $fileAttachGuardMsg }}"
                                    data-bs-toggle="tooltip"
                                    title="{{ __('Create') }}"
                                >
                                    <i class="ti ti-check"></i>
                                </button>
                                <script src="{{ asset('assets/js/routes/projectTasks/comments/storeFile.js') }}" defer></script>
                            </div>
                            <img id="blah" src="" class="img_preview"/>
                        </div>
                    </div>
                </form>
                <div id="comments-file">
                    @forelse($filesIter as $file)
                        @php
                            $fileId               = data_get($file, 'id');
                            $fileName             = data_get($file, 'name') ?? __('No file name available');
                            $fileSize             = data_get($file, 'file_size') ?? __('Unknown size');
                            $filePath             = data_get($file, 'file');
                            $fileUrl              = $filePath ? asset(Storage::url('tasks/'.$filePath)) : '#';
                            $destroyBase          = ViewsConstants::PRJ_TSK_C.'.comment.destroy.file';
                            $destroyKebab         = Str::kebab($destroyBase);
                            $destroyResolved      = Route::has($destroyBase) ? $destroyBase : (Route::has($destroyKebab) ? $destroyKebab : null);
                            $destroyParams        = (isset($projectId, $taskId, $fileId) && $projectId && $taskId && $fileId) ? [$projectId, $taskId, $fileId] : ['#'];
                            $fileDestroyUrl       = ($destroyResolved && isset($projectId, $taskId, $fileId) && $projectId && $taskId && $fileId) ? route($destroyResolved, $destroyParams) : '#';
                            $fileDestroyGuardMsg  = Utility::fetchLinkMessage($lang, ViewsConstants::PRJ_TSK_C, 'destroy_file_project_task_comment_unavailable') ?? 'Destroy file for project task comment route is unavailable. Please contact technical support or your domain administrator.';
                        @endphp
                        <div class="{{ VC::CD }} {{ VC::MB3 }} {{ VC::BD }} {{ VC::SNN }} task-file">
                            <div class="px-3 py-3">
                                <div class="{{ VC::R_ALC }}">
                                    <div class="col ml-n2">
                                        <h6 class="{{ VC::TXSM }} {{ VC::MB0 }}"><a href="#">{{ $fileName }}</a></h6>
                                        <p class="card-text small {{ VC::TXT_MT }}">{{ $fileSize }}</p>
                                    </div>
                                    <div class="col-auto actions">
                                        <div class="action-btn bg-secondary {{ VC::MS2 }}">
                                            <a href="{{ $fileUrl }}" download class="{{ VC::BT_SM_CT }}" role="button">
                                                <i class="{{ VC::TI_DWN }} {{ VC::TXT_WT }}"></i>
                                            </a>
                                        </div>
                                        @auth('web')
                                            <div class="{{ VC::ACT_BTN_DNG_2 }}">
                                                <a href="{{ $fileDestroyUrl }}" class="{{ VC::BT_SM_CT }} delete-comment-file" data-url="{{ $fileDestroyUrl }}" data-guard-msg="{{ $fileDestroyGuardMsg }}">
                                                    <i data-bs-toggle="tooltip" title="{{ __('Delete') }}" class="{{ VC::TI_TRS_WT }}"></i>
                                                </a>
                                            </div>
                                        @endauth
                                    </div>
                                </div>
                            </div>
                        </div>
                        <script src="{{ asset('assets/js/routes/projectTasks/comments/deleteFile.js') }}" defer></script>
                    @empty
                        <small class="{{ VC::TXT_MT }}">{{ __('No attachments available') }}</small>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
    <div class="{{ VC::CD }}">
        <div class="card-body">
            <div class="{{ VC::R_ALC_M4 }}">
                <div class="col-6">
                    <h5>{{ __('Activity') }}</h5>
                </div>
            </div>
            <div class="activity" id="activity">
                @forelse($activityIter as $activity)
                    @php
                        $actUser = User::find(data_get($activity,'user_id'));
                        $actName = data_get($actUser,'name') ?? '';
                        $actAvatar = data_get($actUser,'avatar');
                        $actSrc = !empty($actAvatar) ? asset('/storage/uploads/avatar/'.$actAvatar) : asset('/storage/uploads/avatar/avatar.png');
                        $logType = __((string)data_get($activity,'log_type',''));
                        $remark = method_exists($activity,'getRemark') ? $activity->getRemark() : '';
                        $when = data_get($activity,'created_at') ? data_get($activity,'created_at')->diffForHumans() : '';
                    @endphp
                    <div class="{{ VC::LGI }} px-0">
                        <div class="{{ VC::R_ALC }}">
                            <div class="col-auto">
                                <a href="#" class="avatar avatar-sm {{ VC::MS2 }}">
                                    <img data-toggle="tooltip" data-original-title="{{ $actName }}" src="{{ $actSrc }}" title="{{ $actName }}" class="wid-40 rounded-circle ml-3">
                                </a>
                            </div>
                            <div class="col ml-n2">
                                <span class="text-dark {{ VC::TXSM }}">{{ $logType }}</span>
                                <a class="{{ VC::DBL }} {{ VC::H6 }} {{ VC::TXSM }} font-weight-light {{ VC::MB0 }}">{!! $remark !!}</a>
                                <small class="{{ VC::DBL }}">{{ $when }}</small>
                            </div>
                        </div>
                    </div>
                @empty
                    <small class="{{ VC::TXT_MT }}">{{ __('No activity available') }}</small>
                @endforelse
            </div>
        </div>
    </div>
    <div class="{{ VC::CD }}">
        <div class="card-body">
            <div class="{{ VC::R_ALC_M4 }}">
                <div class="col-6">
                    <h5>{{ __('Comments') }}</h5>
                </div>
                @if($hasGpt)
                    <div class="col-6 text-end">
                        @php
                            $grammarBase               = 'grammar';
                            $grammarKebab              = Str::kebab($grammarBase);
                            $grammarResolved           = Route::has($grammarBase) ? $grammarBase : (Route::has($grammarKebab) ? $grammarKebab : null);
                            $grammarParam              = 'grammar';
                            $grammarParams             = [$grammarParam ?: '#'];
                            $grammarUrl                = $grammarResolved ? route($grammarResolved, $grammarParams) : '#';
                            $grammarGuardMsg           = Utility::fetchLinkMessage($lang, 'generics', 'grammar_check_route_unavailable') ?? 'Grammar check with AI route is unavailable. Please contact technical support or your domain administrator.';
                        @endphp
                        <a href="{{ $grammarUrl }}" data-size="md" class="btn btn-primary btn-icon btn-sm {{ VC::MB3 }} me-2" data-ajax-popup-over="true" id="grammarCheck" data-url="{{ $grammarUrl }}" data-guard-msg="{{ $grammarGuardMsg }}" data-bs-placement="top" data-title="{{ __('Grammar check with AI') }}">
                            <i class="ti ti-rotate"></i> <span>{{ __('Grammar check with AI') }}</span>
                        </a>
                        <script src="{{ asset('assets/js/routes/generics/grammar.js') }}" defer></script>
                    </div>
                @endif
            </div>
            @if(empty($commentsIter))
                <hr />
            @endif
            <div class="activity" id="comments">
                @forelse($commentsIter as $comment)
                    @php
                        $cUser                   = User::find(data_get($comment, 'user_id'));
                        $cName                   = data_get($cUser, 'name') ?? __('Anonymous user');
                        $cAvatar                 = data_get($cUser, 'avatar');
                        $cSrc                    = !empty($cAvatar) ? asset('/storage/uploads/avatar/'.$cAvatar) : asset('/storage/uploads/avatar/avatar.png');
                        $cText                   = data_get($comment, 'comment') ?? __('No comment.');
                        $cWhen                   = data_get($comment, 'created_at') ? data_get($comment, 'created_at')->diffForHumans() : __('No creation date available.');
                        $destroyBase             = ViewsConstants::PRJ_TSK_C.'.comment.destroy';
                        $destroyKebab            = Str::kebab($destroyBase);
                        $destroyResolved         = Route::has($destroyBase) ? $destroyBase : (Route::has($destroyKebab) ? $destroyKebab : null);
                        $commentId               = data_get($comment, 'id');
                        $destroyParams           = (isset($projectId, $taskId, $commentId) && $projectId && $taskId && $commentId) ? [$projectId, $taskId, $commentId] : ['#'];
                        $commentDestroyUrl       = ($destroyResolved && isset($projectId, $taskId, $commentId) && $projectId && $taskId && $commentId) ? route($destroyResolved, $destroyParams) : '#';
                        $commentDestroyGuardMsg  = Utility::fetchLinkMessage($lang, ViewsConstants::PRJ_TSK_C, 'destroy_project_task_comment_unavailable') ?? 'Destroy project task comment route is unavailable. Please contact technical support or your domain administrator.';
                    @endphp
                    <div class="{{ VC::LGI }} px-0 {{ VC::MB1 }}">
                        <div class="{{ VC::R_ALC }}">
                            <div class="col-auto">
                                <a href="#" class="{{ VC::AV_CC_SM }} {{ VC::MS2 }}">
                                    <img data-original-title="{{ $cName }}" src="{{ $cSrc }}" title="{{ $cName }}" class="wid-40 rounded-circle ml-3">
                                </a>
                            </div>
                            <div class="col ml-n2">
                                <p class="{{ VC::DBL }} {{ VC::H6 }} {{ VC::TXSM }} font-weight-light {{ VC::MB0 }} text-break">{{ $cText }}</p>
                                <small class="{{ VC::DBL }}">{{ $cWhen }}</small>
                            </div>
                            <div class="col-auto">
                                <div class="action-btn bg-danger me-2">
                                    <a href="{{ $commentDestroyUrl }}" class="{{ VC::BT_SM_CT }} delete-comment" data-url="{{ $commentDestroyUrl }}" data-guard-msg="{{ $commentDestroyGuardMsg }}">
                                        <i data-bs-toggle="tooltip" title="{{ __('Delete') }}" class="{{ VC::TI_TRS_WT }}"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <script src="{{ asset('assets/js/routes/projectTasks/comments/delete.js') }}" defer></script>
                @empty
                    <small class="{{ VC::TXT_MT }}">{{ __('No comments yet') }}</small>
                @endforelse
            </div>
        </div>
        <div class="card-footer">
            <div class="{{ VC::C12 }} {{ VC::DFL }}">
                <div class="{{ VC::AV }} me-3">
                    <img data-original-title="{{ $userName }}" src="{{ $userAvatarSrc }}" title="{{ $userName }}" class="wid-40 rounded-circle ml-3">
                </div>
                <div class="{{ VC::FM_G }} {{ VC::MB0 }} form-send w-100">
                    @php
                        $commentStoreBase       = ViewsConstants::PRJ_TSK_C.'.comment.store';
                        $commentStoreKebab      = Str::kebab($commentStoreBase);
                        $commentStoreResolved   = Route::has($commentStoreBase) ? $commentStoreBase : (Route::has($commentStoreKebab) ? $commentStoreKebab : null);
                        $commentStoreParams     = (isset($projectId, $taskId) && $projectId && $taskId) ? [$projectId, $taskId] : ['#'];
                        $commentStoreUrl        = ($commentStoreResolved && isset($projectId, $taskId) && $projectId && $taskId) ? route($commentStoreResolved, $commentStoreParams) : '#';
                        $commentStoreGuardMsg   = Utility::fetchLinkMessage($lang, ViewsConstants::PRJ_TSK_C, 'store_project_task_comment_unavailable') ?? 'Store project task comment route is unavailable. Please contact technical support or your domain administrator.';
                    @endphp
                    <form method="post" class="card-comment-box" id="form-comment" action="{{ $commentStoreUrl }}" data-url="{{ $commentStoreUrl }}" data-action="{{ $commentStoreUrl }}" data-guard-msg="{{ $commentStoreGuardMsg }}">
                        <textarea rows="1" class="{{ VC::FM_CT }} grammar_textarea" name="comment" data-toggle="autosize" placeholder="{{ __('Add a comment...') }}"></textarea>
                    </form>
                    <script src="{{ asset('assets/js/routes/projectTasks/comments/store.js') }}" defer></script>
                </div>
                <button id="comment_submit" class="btn btn-send"><i class="text-primary ti ti-brand-telegram"></i></button>
            </div>
        </div>
    </div>
</div>

@push('script-page')
    <script async>
        window.translations = {
            ar:{color_picker_unavailable:'تعذّر تهيئة منتقي الألوان',color_change_unavailable:'تعذّر تحديث لون أولوية المهمة'},
            da:{color_picker_unavailable:'Kunne ikke initialisere farvevælger',color_change_unavailable:'Kunne ikke opdatere opgavens prioritesfarve'},
            de:{color_picker_unavailable:'Farbauswahl konnte nicht initialisiert werden',color_change_unavailable:'Aktualisierung der Aufgabenprioritätsfarbe fehlgeschlagen'},
            en:{color_picker_unavailable:'Cannot initialize color picker',color_change_unavailable:'Cannot update task priority color'},
            es:{color_picker_unavailable:'No se puede inicializar el selector de color',color_change_unavailable:'No se puede actualizar el color de prioridad de la tarea'},
            fr:{color_picker_unavailable:'Impossible d’initialiser le sélecteur de couleur',color_change_unavailable:'Impossible de mettre à jour la couleur de priorité de la tâche'},
            he:{color_picker_unavailable:'לא ניתן לאתחל בוחר צבע',color_change_unavailable:'לא ניתן לעדכן את צבע עדיפות המשימה'},
            it:{color_picker_unavailable:'Impossibile inizializzare il selettore colore',color_change_unavailable:'Impossibile aggiornare il colore di priorità del task'},
            ja:{color_picker_unavailable:'カラーピッカーを初期化できません',color_change_unavailable:'タスクの優先色を更新できません'},
            nl:{color_picker_unavailable:'Kan kleurkiezer niet initialiseren',color_change_unavailable:'Kan prioriteitskleur van taak niet bijwerken'},
            pl:{color_picker_unavailable:'Nie można zainicjować selektora kolorów',color_change_unavailable:'Nie można zaktualizować koloru priorytetu zadania'},
            pt:{color_picker_unavailable:'Não foi possível iniciar o seletor de cores',color_change_unavailable:'Não foi possível atualizar a cor de prioridade da tarefa'},
            'pt-br':{color_picker_unavailable:'Não foi possível iniciar o seletor de cores',color_change_unavailable:'Não foi possível atualizar a cor de prioridade da tarefa'},
            ru:{color_picker_unavailable:'Не удалось инициализировать выбор цвета',color_change_unavailable:'Не удалось обновить цвет приоритета задачи'},
            tr:{color_picker_unavailable:'Renk seçici başlatılamıyor',color_change_unavailable:'Görev öncelik rengi güncellenemedi'},
            zh:{color_picker_unavailable:'无法初始化取色器',color_change_unavailable:'无法更新任务优先级颜色'}
        };
    </script>
    <script defer>
        (()=>{
            const ERR_FB='# ERROR';
            const DATA_CLIENT_LOCALIZED='data-client-localized';
            const DATA_GUARD_MSG='data-guard-msg';
            const DATA_LISTENER_ADDED='data-listener-added';

            const getLocalizedMessage=(el,key)=>{
            let msg=ERR_FB;
            if(el?.getAttribute('data-sv-localized')==='true' || el?.getAttribute(DATA_CLIENT_LOCALIZED)==='true'){
                msg=el.getAttribute(DATA_GUARD_MSG) || ERR_FB;
            }else{
                let lang=(sessionStorage.getItem('erp-np-lang') || document.documentElement.lang || 'en')
                .toLowerCase().replace(/_/g,'-');
                lang = (lang === 'pt-br') ? lang : lang.slice(0,2);
                msg = window.translations?.[lang]?.[key]
                || el?.getAttribute?.(DATA_GUARD_MSG)
                || window.translations?.en?.[key]
                || ERR_FB;
                if(msg !== ERR_FB){ el?.setAttribute?.(DATA_GUARD_MSG,msg); el?.setAttribute?.(DATA_CLIENT_LOCALIZED,'true'); }
            }
            return msg;
            };

            const showError=(el,key)=>{
            const text=getLocalizedMessage(el||document.body,key);
            const hasBootstrap = document.querySelector('link[href*="bootstrap"]') && window.bootstrap?.Toast;
            if(hasBootstrap){
                if(!document.querySelector('#error-toast')){
                const n=document.createElement('div');
                n.id='error-toast';
                n.className='toast align-items-center text-bg-danger border-0';
                n.setAttribute('role','alert'); n.setAttribute('aria-live','assertive'); n.setAttribute('aria-atomic','true');
                n.innerHTML=`<div class="d-flex"><div class="toast-body">${text}</div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button></div>`;
                document.body.appendChild(n);
                }
                new bootstrap.Toast(document.querySelector('#error-toast')).show();
            }else{
                alert(text);
            }
            };

            const attachPointerGuard=(el,key)=>{
            if(!el || el.getAttribute(DATA_LISTENER_ADDED)==='true') return;
            const handler=()=>showError(el,key);
            el.addEventListener('pointerup',handler,{once:true});
            el.setAttribute(DATA_LISTENER_ADDED,'true');
            const mo=new MutationObserver((_,o)=>{ if(!document.body.contains(el)){ el.removeEventListener('pointerup',handler); o.disconnect(); }});
            mo.observe(document.body,{childList:true,subtree:true});
            };

            try{
            if(typeof $==='undefined' || !$.fn?.colorPick){
                console.error('Color picker dependency failed to load');
                return;
            }

            $(()=>{
                try{
                $('.colorPickSelector').colorPick({
                    onColorSelected: function(){
                    try{
                        const jqEl = this.element;
                        const rawEl = jqEl?.get?.(0) || jqEl?.[0] || document.body;
                        const taskId = jqEl?.closest('.side-modal')?.attr('id') ?? '';
                        const color = this.color ?? '';
                        if(!taskId || !color) return;
                        jqEl.css({ backgroundColor: color });
                        const endpoint='{{ route('update.task.priority.color') }}' ?? '#';
                        const urlAttr = rawEl?.getAttribute?.('data-url') || '';
                        const hrefAttr = endpoint;
                        if((!urlAttr || urlAttr==='#') && (!hrefAttr || hrefAttr==='#')){
                        attachPointerGuard(rawEl,'color_change_unavailable');
                        return;
                        }
                        $.ajax({
                        url: hrefAttr,
                        method: 'PATCH',
                        data: { task_id: taskId, color },
                        success: (data)=>{
                            try{
                            $('.task-list-items')?.find('#'+taskId)?.attr('style', 'border-left:2px solid '+color+' !important');
                            }catch{ /* noop */ }
                        },
                        error: ()=>attachPointerGuard(rawEl,'color_change_unavailable')
                        });
                    }catch{
                        const el = this?.element?.get?.(0) || this?.element?.[0] || document.body;
                        attachPointerGuard(el,'color_change_unavailable');
                    }
                    }
                });
                }catch{
                const el=document.querySelector('.colorPickSelector') || document.body;
                attachPointerGuard(el,'color_picker_unavailable');
                }
            });

            }catch(e){
            console.error('Initialization failed', e);
            }
        })();
    </script>
@endpush
