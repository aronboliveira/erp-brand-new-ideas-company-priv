@include('partials.helpers.route_helpers')
@php
    try {
$user = Auth::user();
        $lang = Utility::fetchUserLang(user: $user);
        $projId = safeDataGet($task, 'project_id', '');
        $taskId = safeDataGet($task, 'id', '');
        $taskName = safeDataGet($task, 'name') ?: __('No task name available');
        $isComplete = (int)(data_get($task, ProjectsConstants::COL_IS_CP) ?? 0) === 1;
        $isFavorite = (bool)data_get($task, ProjectsConstants::COL_IS_FV);
        $priorityColor = safeDataGet($task, 'priority_color', '');
        $progressVal = (int)(data_get($task, 'progress') ?? 0);

        $changeComplete = resolveRouteWithGuard(VW::PRJ_TSK_C.'.change.complete', $lang, VW::PRJ_TSK_C, 'change_complete_project_task_route_unavailable', [$projId, $taskId], 'Change complete route unavailable.');
        $changeFav = resolveRouteWithGuard(VW::PRJ_TSK_C.'.change.fav', $lang, VW::PRJ_TSK_C, 'change_favorite_project_task_route_unavailable', [$projId, $taskId], 'Change favorite route unavailable.');
        $changeProgress = resolveRouteWithGuard(VW::PRJ_TSK_C.'.change.progress', $lang, VW::PRJ_TSK_C, 'change_progress_project_task_route_unavailable', [$projId, $taskId], 'Change progress route unavailable.');
        $checklistStore = resolveRouteWithGuard(VW::PRJ_TSK_C.'.checklist.store', $lang, VW::PRJ_TSK_C, 'store_project_task_checklist_route_unavailable', [$projId, $taskId], 'Store checklist route unavailable.');
        $cmtStore = resolveRouteWithGuard(VW::PRJ_TSK_C.'.comment.store', $lang, VW::PRJ_TSK_C, 'store_project_task_comment_route_unavailable', [$projId, $taskId], 'Store comment route unavailable.');

        $chkId = buildElementId('complete-task', $projId, $taskId);
        $favId = buildElementId('fav-task', $projId, $taskId);
        $rangeId = buildElementId('progress-task', $projId, $taskId);
        $checklistFormId = buildElementId('form-checklist', $projId, $taskId);
        $commentFormId = buildElementId('form-comment', $projId, $taskId);

        $checklist = ensureIterable(data_get($task, 'checklist', []));
        $files = ensureIterable(data_get($task, 'taskFiles', []));
        $activity = ensureIterable(is_object($task) && method_exists($task, 'activityLog') ? $task->activityLog() : []);
        $comments = ensureIterable(data_get($task, 'comments', []));
    } catch (\Throwable $e) {
        \Log::error('tasks/show — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    }
@endphp
<div class="{{ VC::MDL_DLG }} modal-vertical modal-lg side-modal animate__animated animate__slideInRight" role="document" id="{{ $taskId }}">
    <div class="{{ VC::MDL_CTT }}">
        <div class="{{ VC::MDL_HDR }}">
            <div class="col {{ VC::DFL_AIC }}">
                <div class="{{ VC::CST_CT_CB }} mt-n1">
                    <input type="checkbox" class="custom-control-input" id="{{ $chkId }}" @if($isComplete) checked @endif data-url="{{ $changeComplete['url'] }}" data-guard-msg="{{ base64_encode($changeComplete['guardMsg']) }}" data-route-guard="task-complete" data-sv-localized="true">
                    <label class="{{ VC::CST_LB }}" for="{{ $chkId }}"></label>
                </div>
                <h6 class="{{ VC::MB0 }}">{{ $taskName }}</h6>
            </div>
            <div class="{{ VC::C_AT }}">
                <div class="actions {{ VC::TX_END }}">
                    <div class="float-left">
                        <a href="{{ $changeFav['url'] }}" id="{{ $favId }}" class="action-item {{ $isFavorite ? 'action-favorite' : '' }} active" data-url="{{ $changeFav['url'] }}" data-toggle="tooltip" data-original-title="{{ __('Mark as favorite') }}" data-guard-msg="{{ base64_encode($changeFav['guardMsg']) }}" data-route-guard="task-favorite" data-sv-localized="true">
                            <i class="ti ti-star"></i>
                        </a>
                    </div>
                    <div class="priority-color float-right">
                        <div class="colorPickSelector" style="background-color: {{ $priorityColor }}"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="scrollbar-inner">
            <div class="modal-body">
                <div class="{{ VC::R_ALC }} {{ VC::MB4 }}">
                    <div class="{{ VC::C6 }}">
                        <label class="{{ VC::FM_LB }} {{ VC::MB0 }}">{{ __('See Detail') }}</label>
                    </div>
                    <div class="{{ VC::C6 }} {{ VC::TX_END }}">
                        <a href="#" class="{{ VC::BT_XS }} btn-secondary btn-icon rounded-pill" data-toggle="collapse" data-target="#overview">
                            <span class="btn-inner--icon"><i class="{{ VC::TI_PLS }}"></i></span>
                        </a>
                    </div>
                </div>
                <div id="overview" class="collapse">
                    <b>{{ __('Estimated Hours') }}</b> : <span>{{ !empty(data_get($task, 'estimated_hrs')) ? number_format((float)data_get($task, 'estimated_hrs', 0)) : __('No estimated hours available') }}</span><br>
                    <b>{{ __('Milestone') }}</b> : <span>{{ safeDataGet($task, 'milestone.title') ?: __('No milestone available') }}</span><br>
                    <b>{{ __('Description') }}</b><br><span>{{ safeDataGet($task, 'description') ?: __('No description available') }}</span>
                </div>
                <hr/>
                @if((string)($allow_progress ?? '') === 'false')
                    <div class="{{ VC::R_ALC }}">
                        <div class="{{ VC::C12 }} pb-2">
                            <label class="{{ VC::FM_LB }} {{ VC::MB0 }}">{{ __('Task Progress') }} : <b id="t_percentage">{{ $progressVal }}</b>%</label>
                        </div>
                        <div class="{{ VC::C12 }}">
                            <div id="progress-result" class="tab-pane tab-example-result fade show active" role="tabpanel" aria-labelledby="progress-result-tab">
                                <input type="range" class="task_progress custom-range" value="{{ $progressVal }}" id="{{ $rangeId }}" name="progress" data-url="{{ $changeProgress['url'] }}" data-guard-msg="{{ base64_encode($changeProgress['guardMsg']) }}" data-sv-localized="true" data-start-val="{{ $progressVal }}">
                            </div>
                        </div>
                    </div>
                    <hr/>
                @endif
                <div class="{{ VC::R_ALC }} {{ VC::MB4 }}">
                    <div class="{{ VC::C6 }}">
                        <label class="{{ VC::FM_LB }} {{ VC::MB0 }}">{{ __('Checklist') }}</label>
                    </div>
                    <div class="{{ VC::C6 }} {{ VC::TX_END }}">
                        <a href="#" class="{{ VC::BT_XS }} btn-secondary btn-icon rounded-pill" data-toggle="collapse" data-target="#{{ $checklistFormId }}">
                            <span class="btn-inner--icon"><i class="{{ VC::TI_PLS }}"></i></span>
                            <span class="btn-inner--text">{{ __('Add item') }}</span>
                        </a>
                    </div>
                </div>
                <div class="checklist" id="checklist">
                    <form method="post" id="{{ $checklistFormId }}" class="collapse pb-2" action="{{ $checklistStore['url'] }}" data-url="{{ $checklistStore['url'] }}" data-guard-msg="{{ base64_encode($checklistStore['guardMsg']) }}" data-sv-localized="true">
                        @csrf
                        <div class="{{ VC::CD_NSD }}">
                            <div class="{{ VC::RW }} {{ VC::ALC }} {{ VC::PX3 }} {{ VC::PY2 }}">
                                <div class="col-10">
                                    <input type="text" name="name" required class="{{ VC::FM_CT }}" placeholder="{{ __('Checklist Name') }}"/>
                                </div>
                                <div class="{{ VC::CL_MT_VC }}">
                                    <button class="{{ VC::BT_XS_PM }}" type="submit"><i class="{{ VC::TI_PLS }}"></i></button>
                                </div>
                            </div>
                        </div>
                    </form>
                    @forelse($checklist as $item)
                        @php
                            try {
                                $chkItemId = safeDataGet($item, 'id', '');
                                $chkUpdate = resolveRouteWithGuard(VW::PRJ_TSK_C.'.checklist.update', $lang, VW::PRJ_TSK_C, 'update_project_task_checklist_route_unavailable', [$projId, $chkItemId], 'Update checklist unavailable.');
                                $chkDestroy = resolveRouteWithGuard(VW::PRJ_TSK_C.'.checklist.destroy', $lang, VW::PRJ_TSK_C, 'destroy_project_task_checklist_route_unavailable', [$projId, $chkItemId], 'Delete checklist unavailable.');
                                $inputId = buildElementId('check-item', $chkItemId);
                                $delId = buildElementId('delete-checklist', $projId, $chkItemId);
                            } catch (\Throwable $e) {
                                \Log::error('tasks/show — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                            }
@endphp
                        <div class="{{ VC::CD_NSD }} checklist-member animate__animated">
                            <div class="{{ VC::RW }} {{ VC::ALC }} {{ VC::PX3 }} {{ VC::PY2 }}">
                                <div class="col-10">
                                    <div class="{{ VC::CST_CTL }} {{ VC::CST_CB }}">
                                        <input type="checkbox" class="custom-control-input" id="{{ $inputId }}" @if((bool)data_get($item, 'status')) checked @endif data-url="{{ $chkUpdate['url'] }}" data-guard-msg="{{ base64_encode($chkUpdate['guardMsg']) }}" data-sv-localized="true">
                                        <label class="{{ VC::CST_LB_SM }}" for="{{ $inputId }}">{{ safeDataGet($item, 'name') ?: __('No checklist name available') }}</label>
                                    </div>
                                </div>
                                <div class="{{ VC::C_AT }} {{ VC::CD_MT }} {{ VC::DFL_IL_VC }} {{ VC::ML_SM_AT }}">
                                    <a id="{{ $delId }}" href="{{ $chkDestroy['url'] }}" class="action-item delete-checklist" data-url="{{ $chkDestroy['url'] }}" data-guard-msg="{{ base64_encode($chkDestroy['guardMsg']) }}" data-sv-localized="true" data-bs-toggle="tooltip" title="{{ __('Delete') }}">
                                        <i class="{{ VC::TI_TRS_ALT }}"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    @empty
                        <p class="{{ VC::TXT_MT }}">{{ __('No checklist items available') }}</p>
                    @endforelse
                </div>
                <hr/>
                <div class="{{ VC::R_ALC }} {{ VC::MB4 }}">
                    <div class="{{ VC::C6 }}">
                        <label class="{{ VC::FM_LB }} {{ VC::MB0 }}">{{ __('Attachments') }}</label>
                    </div>
                    <div class="{{ VC::C6 }} {{ VC::TX_END }}">
                        <a href="#" class="{{ VC::BT_XS }} btn-secondary btn-icon rounded-pill" data-toggle="collapse" data-target="#add_file">
                            <span class="btn-inner--icon"><i class="{{ VC::TI_PLS }}"></i></span>
                            <span class="btn-inner--text">{{ __('Add item') }}</span>
                        </a>
                    </div>
                </div>
                <input type="hidden" name="_token" id="token" value="{{ csrf_token() }}">
                <div class="card {{ VC::MB3 }} border {{ VC::SNN }} collapse" id="add_file">
                    <div class="card border-0 shadow-none {{ VC::MB0 }}">
                        <div class="{{ VC::RW }} {{ VC::ALC }} {{ VC::PX3 }} {{ VC::PY2 }}">
                            <div class="col-10">
                                <input type="file" name="task_attachment" id="task_attachment" required class="custom-input-file"/>
                                <label for="task_attachment"><i class="fa fa-upload"></i><span class="attachment_text">{{ __('Choose a file…') }}</span></label>
                            </div>
                            <div class="{{ VC::CL_MT_VC }}">
                                <button class="{{ VC::BT_XS_PM }}" type="submit" id="file_submit"><i class="{{ VC::TI_PLS }}"></i></button>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="comments-file">
                    @forelse($files as $file)
                        @php
                            try {
                                $fileId = safeDataGet($file, 'id', '');
                                $fileDestroy = resolveRouteWithGuard(VW::PRJ_TSK_C.'.comment.destroy.file', $lang, VW::PRJ_TSK_C, 'destroy_project_task_comment_file_route_unavailable', [$projId, $taskId, $fileId], 'Delete file unavailable.');
                                $delFileId = buildElementId('delete-file', $projId, $taskId, $fileId);
                                $ext = safeDataGet($file, 'extension') ?: 'file';
                                $fileName = safeDataGet($file, 'name') ?: __('No file name available');
                                $fileSize = safeDataGet($file, 'file_size') ?: __('No file size available');
                            } catch (\Throwable $e) {
                                \Log::error('tasks/show — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                            }
@endphp
                        <div class="{{ VC::CD_NSD }} {{ VC::MB3 }} task-file animate__animated">
                            <div class="{{ VC::PX3 }} {{ VC::PY2 }}">
                                <div class="{{ VC::R_ALC }}">
                                    <div class="{{ VC::C_AT }}">
                                        <img src="{{ asset("assets/img/icons/files/{$ext}.png") }}" class="{{ VC::IMG_FL }}" style="width: 40px;">
                                    </div>
                                    <div class="col ml-n2">
                                        <h6 class="{{ VC::TXSM }} {{ VC::MB0 }}"><a href="#">{{ $fileName }}</a></h6>
                                        <p class="card-text small {{ VC::TXT_MT }}">{{ $fileSize }}</p>
                                    </div>
                                    <div class="{{ VC::C_AT }} actions">
                                        <a href="{{ asset(Storage::url('tasks/'.safeDataGet($file, 'file', ''))) }}" download class="action-item" role="button"><i class="{{ VC::TI_DWN }}"></i></a>
                                        @auth('web')
                                            <a id="{{ $delFileId }}" href="{{ $fileDestroy['url'] }}" class="action-item delete-comment-file" role="button" data-url="{{ $fileDestroy['url'] }}" data-guard-msg="{{ base64_encode($fileDestroy['guardMsg']) }}" data-route-guard="task-file-delete" data-sv-localized="true">
                                                <i class="{{ VC::TI_TRS }}"></i>
                                            </a>
                                        @endauth
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <p class="{{ VC::TXT_MT }}">{{ __('No attachments available') }}</p>
                    @endforelse
                </div>
                <hr/>
                <label class="{{ VC::FM_LB }} {{ VC::MB4 }}">{{ __('Activity') }}</label>
                <div class="{{ VC::LG_FLSH_MB0 }}">
                    @forelse($activity as $act)
                        <div class="{{ VC::LGI }}">
                            <div class="{{ VC::R_ALC }}">
                                <div class="{{ VC::C_AT }}">
                                    <a href="#" class="{{ VC::AV_CC_SM }}"><img {{ safeDataGet($act, 'user.img_avatar') }} class="{{ VC::AV_CC_SM }}"></a>
                                </div>
                                <div class="col ml-n2">
                                    <span class="text-dark {{ VC::TXSM }}">{{ __(safeDataGet($act, 'log_type', '')) ?: __('No activity type available') }}</span>
                                    <a class="d-block h6 {{ VC::TXSM }} font-weight-light {{ VC::MB0 }}">{!! is_object($act) && method_exists($act, 'getRemark') ? ($act->getRemark() ?? '') : '' !!}</a>
                                    <small class="{{ VC::DBL }}">{{ (data_get($act, 'created_at') && method_exists(data_get($act, 'created_at'), 'diffForHumans')) ? data_get($act, 'created_at')->diffForHumans() : __('No time available') }}</small>
                                </div>
                            </div>
                        </div>
                    @empty
                        <p class="{{ VC::TXT_MT }}">{{ __('No activity available') }}</p>
                    @endforelse
                </div>
                <hr/>
                <label class="{{ VC::FM_LB }} {{ VC::MB4 }}">{{ __('Comments') }}</label>
                <div class="{{ VC::LG_FLSH_MB0 }}" id="comments">
                    @forelse($comments as $cmt)
                        @php
                            try {
                                $cmtId = safeDataGet($cmt, 'id', '');
                                $cmtDestroy = resolveRouteWithGuard(VW::PRJ_TSK_C.'.comment.destroy', $lang, VW::PRJ_TSK_C, 'destroy_project_task_comment_route_unavailable', [$projId, $taskId, $cmtId], 'Delete comment unavailable.');
                                $delCmtId = buildElementId('delete-comment', $projId, $taskId, $cmtId);
                            } catch (\Throwable $e) {
                                \Log::error('tasks/show — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                            }
@endphp
                        <div class="{{ VC::LGI }}">
                            <div class="{{ VC::R_ALC }}">
                                <div class="{{ VC::C_AT }}">
                                    <a href="#" class="{{ VC::AV_CC_SM }}"><img {{ safeDataGet($cmt, 'user.img_avatar') }} title="{{ safeDataGet($cmt, 'user.name') }}"></a>
                                </div>
                                <div class="col ml-n2">
                                    <p class="d-block h6 {{ VC::TXSM }} font-weight-light {{ VC::MB0 }} text-break">{{ safeDataGet($cmt, 'comment') ?: __('No comment text available') }}</p>
                                    <small class="{{ VC::DBL }}">{{ (data_get($cmt, 'created_at') && method_exists(data_get($cmt, 'created_at'), 'diffForHumans')) ? data_get($cmt, 'created_at')->diffForHumans() : __('No time available') }}</small>
                                </div>
                                <div class="{{ VC::C_AT }}">
                                    <a id="{{ $delCmtId }}" href="{{ $cmtDestroy['url'] }}" class="action-item delete-comment" data-url="{{ $cmtDestroy['url'] }}" data-guard-msg="{{ base64_encode($cmtDestroy['guardMsg']) }}" data-route-guard="task-comment-delete" data-sv-localized="true">
                                        <i class="{{ VC::TI_TRS_ALT }}"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    @empty
                        <p class="{{ VC::TXT_MT }}">{{ __('No comments available') }}</p>
                    @endforelse
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <div class="col-12 {{ VC::DFL }}">
                <div class="pr-3"><img {{ $user?->img_avatar ?? __('No image') }} title="{{ $user?->name ?? __('Anonymous user') }}" class="{{ VC::AV_CC_SM }}"></div>
                <form method="post" class="card-comment-box" id="{{ $commentFormId }}" action="{{ $cmtStore['url'] }}" data-url="{{ $cmtStore['url'] }}" data-guard-msg="{{ base64_encode($cmtStore['guardMsg']) }}" data-sv-localized="true">
                    @csrf
                    <textarea rows="1" class="{{ VC::FM_CT }}" name="comment" data-toggle="autosize" placeholder="{{ __('Add a comment...') }}"></textarea>
                </form>
            </div>
            <div class="col-4 {{ VC::CM3 }} {{ VC::TX_END }}">
                <div class="actions"><a href="#" id="comment_submit" class="action-item"><i class="ti ti-paper-plane"></i></a></div>
            </div>
        </div>
    </div>
</div>
<script defer src="{{ asset('assets/js/core/route-guard.js') }}"></script>
<script async src="{{ asset('assets/js/routes/tasks/lang/color.js') }}"></script>
<script defer>
(function() {
    const $ = window.jQuery;
    if (!$ || !$.fn?.colorPick) {
        window.RouteGuard?.scheduleInteractiveError(window.RouteGuard?.getMsg(document.body, 'plugin_unavailable') || 'ColorPick plugin unavailable');
        return;
    }
    const initPickers = () => {
        $('.colorPickSelector').each(function() {
            const el = this;
            if (el.getAttribute('data-colorpick-bound') === 'true') return;
            el.setAttribute('data-colorpick-bound', 'true');
            $(el).colorPick({
                onColorSelected: function() {
                    const side = this.element?.parents?.('.side-modal');
                    const taskId = side?.length ? side.attr('id') : '';
                    const color = this.color ?? '';
                    if (!taskId || !color) {
                        window.RouteGuard?.showToast('Color update unavailable', 'error');
                        return;
                    }
                    this.element?.css?.({ backgroundColor: color });
                    const url = '{{ Route::has("update.task.priority.color") ? route("update.task.priority.color") : "#" }}';
                    if (!url || url === '#') {
                        window.RouteGuard?.showToast('Color update route unavailable', 'error');
                        return;
                    }
                    $.ajax({
                        url,
                        method: 'PATCH',
                        data: { task_id: taskId, color },
                        cache: false,
                        success: () => $('.task-list-items').find('#' + taskId).attr('style', 'border-left:2px solid ' + color + ' !important'),
                        error: () => window.RouteGuard?.showToast('Failed to update color', 'error')
                    });
                }
            });
        });
    };
    document.readyState === 'loading' ? document.addEventListener('DOMContentLoaded', initPickers, { once: true }) : initPickers();
})();
</script>
