@php
    use App\Config\Constants\{
        ViewsConstants,
        ViewClassNamesConstants as VC,
    };
    use App\Models\Utility;
    use Illuminate\Support\Facades\{Route,Storage};
    use Illuminate\Support\Str;
    $lang = Utility::fetchUserLang();
@endphp
@if(isset($bug) && is_object($bug))
    <div class="modal-body">
        <div class="row">
            <div class="col-6">
                <div class="form-group">
                    <b class="{{ VC::TXSM }}">{{ __('Title') }} :</b>
                    <p class="{{ VC::MB0 }} p-0 {{ VC::TXSM }}">{{ e(data_get($bug, 'title', __('No Title'))) }}</p>
                </div>
            </div>
            <div class="col-6">
                <div class="form-group">
                    <b class="{{ VC::TXSM }}">{{ __('Priority') }} :</b>
                    @php
                        $priority = data_get($bug, 'priority', '');
                        $displayPriority = !empty($priority) ? ucfirst(e($priority)) : __('No Priority');
                    @endphp
                    <p class="{{ VC::MB0 }} p-0 {{ VC::TXSM }}">{{ $displayPriority }}</p>
                </div>
            </div>
            <div class="col-6">
                <div class="form-group">
                    <b class="{{ VC::TXSM }}">{{ __('Created Date') }} :</b>
                    @php
                        $createdAt = data_get($bug, 'created_at', '');
                        $displayCreatedAt = !empty($createdAt) ? e($createdAt) : __('No Date');
                    @endphp
                    <p class="{{ VC::MB0 }} p-0 {{ VC::TXSM }}">{{ $displayCreatedAt }}</p>
                </div>
            </div>
            <div class="col-6">
                <div class="form-group">
                    <b class="{{ VC::TXSM }}">{{ __('Assign to') }} :</b>
                    @php
                        $assignTo = data_get($bug, 'assignTo');
                        $assigneeName = '';
                        if (isset($assignTo) && is_object($assignTo)) {
                            $assigneeName = data_get($assignTo, 'name', '');
                        }
                        $displayAssignee = !empty($assigneeName) ? e($assigneeName) : __('Not Assigned');
                    @endphp
                    <p class="{{ VC::MB0 }} p-0 {{ VC::TXSM }}">{{ $displayAssignee }}</p>
                </div>
            </div>
            <div class="col-12">
                <div class="form-group">
                    <b class="{{ VC::TXSM }}">{{ __('Description') }} :</b>
                    @php
                        $description = data_get($bug, 'description', '');
                        $displayDescription = !empty($description) ? e($description) : __('No Description');
                    @endphp
                    <p class="{{ VC::MB0 }} p-0 {{ VC::TXSM }}">{{ $displayDescription }}</p>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <ul class="nav nav-tabs" id="myTab" role="tablist">
                    <li class="nav-item {{ VC::MB3 }}">
                        <a class="{{ VC::BT_OUTPM_SM }} ms-2 active show" data-bs-toggle="tab"
                           href="#profile" role="tab" aria-selected="false">{{ __('Comments') }}</a>
                    </li>
                    <li class="nav-item {{ VC::MB3 }}">
                        <a class="{{ VC::BT_OUTPM_SM }} ms-2" id="contact-tab" data-bs-toggle="tab" 
                           href="#contact" role="tab" aria-controls="contact" aria-selected="false">{{ __('Files') }}</a>
                    </li>
                </ul>
                <div class="tab-content pt-4" id="myTabContent">
                    <div class="tab-pane fade active show" id="profile" role="tabpanel" aria-labelledby="profile-tab">
                        <div class="form-group m-0">
                            @php
                                $commentStoreBaseName     = ViewsConstants::PRJ_BUG_CM.'.store';
                                $commentStoreKebabName    = Str::kebab($commentStoreBaseName);
                                $commentStoreResolvedName = Route::has($commentStoreBaseName)
                                    ? $commentStoreBaseName
                                    : (Route::has($commentStoreKebabName) ? $commentStoreKebabName : null);
                                $projectIdValue           = isset($projectId) && !empty($projectId) ? $projectId : data_get($bug ?? null, 'project_id');
                                $bugIdValue               = isset($bugId) && !empty($bugId) ? $bugId : data_get($bug ?? null, 'id');
                                $commentStoreUrl          = ($commentStoreResolvedName && $projectIdValue && $bugIdValue) ? route($commentStoreResolvedName, [$projectIdValue, $bugIdValue]) : '#';
                                $commentStoreGuardMsg     = Utility::fetchLinkMessage($lang, ViewsConstants::PRJ_BUG_CM, 'store_bug_comment_route_unavailable') ?? 'Store bug comment route is unavailable. Please contact technical support or your domain administrator.';
                                $commentStoreFormId       = 'form-comment';
                                $commentStoreBtnId        = 'form-comment-submit-btn';
                            @endphp
                            <form method="post"
                                id="{{ $commentStoreFormId }}"
                                action="{{ $commentStoreUrl }}"
                                data-url="{{ $commentStoreUrl }}"
                                data-guard-msg="{{ $commentStoreGuardMsg }}">
                                @csrf
                                <textarea class="{{ VC::FM_CT }}"
                                        name="comment"
                                        placeholder="{{ __('Write message') }}"
                                        id="example-textarea"
                                        rows="3"
                                        required></textarea>
                                <div class="text-end mt-1">
                                    <div class="btn-group mb-2 ms-2 d-none d-sm-inline-block">
                                        <button type="button" id="{{ $commentStoreBtnId }}" class="{{ VC::BT_SM_PM }} ms-2 {{ VC::TXT_WT }}">
                                            {{ __('Submit') }}
                                        </button>
                                    </div>
                                </div>
                            </form>
                            <div class="comment-holder" id="comments">
                                @php
                                    $comments = data_get($bug, 'comments', []);
                                    $comments = is_countable($comments) ? $comments : [];
                                @endphp
                                @if(!empty($comments) && count($comments) > 0)
                                    @foreach($comments as $comment)
                                        @if(isset($comment) && is_object($comment))
                                            @php
                                                $commentId                 = data_get($comment ?? null, 'id');
                                                $commentUser               = data_get($comment ?? null, 'user');
                                                $userNameText              = isset($commentUser) && is_object($commentUser) ? e(data_get($commentUser, 'name', __('Anonymous User'))) : __('Anonymous User');
                                                $commentText               = e(data_get($comment ?? null, 'comment', __('Could not find comment content.')));
                                                $commentDestroyBaseName    = ViewsConstants::PRJ_BUG_CM.'.destroy';
                                                $commentDestroyKebabName   = Str::kebab($commentDestroyBaseName);
                                                $commentDestroyResolved    = Route::has($commentDestroyBaseName) ? $commentDestroyBaseName : (Route::has($commentDestroyKebabName) ? $commentDestroyKebabName : null);
                                                $commentDestroyUrl         = ($commentDestroyResolved && !empty($commentId)) ? route($commentDestroyResolved, $commentId) : '#';
                                                $commentDestroyGuardMsg    = Utility::fetchLinkMessage($lang, ViewsConstants::PRJ_BUG_CM, 'delete_bug_comment_route_unavailable') ?? 'Delete bug comment route is unavailable. Please contact technical support or your domain administrator.';
                                                $commentDestroyLinkId      = 'comment-destroy-link-'.($commentId ?? 'x');
                                            @endphp
                                            <div class="media">
                                                <div class="media-body">
                                                    <div class="{{ VC::DFL_AIC_JCB }} align-items-end">
                                                        <div>
                                                            <h5 class="{{ VC::MT3 }} {{ VC::MB0 }}">
                                                                {{ !empty($userNameText) ? $userNameText : __('Anonymous User') }}
                                                            </h5>
                                                            <p class="{{ VC::MB0 }} {{ VC::TXS }}">
                                                                {{ !empty($commentText) ? $commentText : __('No Comment') }}
                                                            </p>
                                                        </div>
                                                        <a href="{{ $commentDestroyUrl }}"
                                                        id="{{ $commentDestroyLinkId }}"
                                                        class="{{ VC::BT_SM_DG }} delete-comment"
                                                        data-url="{{ $commentDestroyUrl }}"
                                                        data-guard-msg="{{ $commentDestroyGuardMsg }}">
                                                            <i class="{{ VC::TI_TRS }}"></i>
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                            <script defer>
                                                (() => {
                                                    try {
                                                        const l = document.getElementById('{{ $commentDestroyLinkId }}');
                                                        if (!l || l.getAttribute('data-listener-active') === 'true') return;
                                                        l.setAttribute('data-listener-active', 'true');
                                                        l.addEventListener('click', e => {
                                                            try {
                                                                const href = l.getAttribute('href') || '#';
                                                                const url = l.getAttribute('data-url') || href || '#';
                                                                if (href !== '#' || url !== '#') return;
                                                                e.preventDefault();
                                                                const msg = l.getAttribute('data-guard-msg') || 'Delete bug comment route is unavailable. Please contact technical support or your domain administrator.';
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
                                                                l.setAttribute('data-failed-route', 'true');
                                                            } catch (err) {}
                                                        });
                                                    } catch (error) {}
                                                })();
                                            </script>
                                        @endif
                                    @endforeach
                                @else
                                    <div class="text-center {{ VC::TXT_MT }}">
                                        {{ __('No comments yet') }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="contact" role="tabpanel" aria-labelledby="contact-tab">
                        <div class="form-group m-0">
                            @php
                                $commentFileStoreBaseName     = ViewsConstants::PRJ_BUG_CM.'.file.store';
                                $commentFileStoreKebabName    = Str::kebab($commentFileStoreBaseName);
                                $commentFileStoreResolvedName = Route::has($commentFileStoreBaseName)
                                    ? $commentFileStoreBaseName
                                    : (Route::has($commentFileStoreKebabName) ? $commentFileStoreKebabName : null);
                                $bugIdValue                   = isset($bugId) && !empty($bugId) ? $bugId : data_get($bug ?? null, 'id');
                                $commentFileStoreUrl          = ($commentFileStoreResolvedName && $bugIdValue) ? route($commentFileStoreResolvedName, $bugIdValue) : '#';
                                $commentFileStoreGuardMsg     = Utility::fetchLinkMessage($lang, ViewsConstants::PRJ_BUG_CM, 'store_bug_comment_file_route_unavailable') ?? 'Store bug comment file route is unavailable. Please contact technical support or your domain administrator.';
                                $commentFileStoreFormId       = 'form-file';
                            @endphp
                            <form method="post"
                                id="{{ $commentFileStoreFormId }}"
                                enctype="multipart/form-data"
                                action="{{ $commentFileStoreUrl }}"
                                data-url="{{ $commentFileStoreUrl }}"
                                data-guard-msg="{{ $commentFileStoreGuardMsg }}">
                                @csrf
                                <div class="row">
                                    <div class="col-6">
                                        <div class="choose-file form-group">
                                            <label for="file" class="{{ VC::FM_LB }}">
                                                <div>{{ __('file here') }}</div>
                                                <input type="file" class="{{ VC::FM_CT }}" name="file" id="file" data-filename="file_update">
                                            </label>
                                            <p class="file_update"></p>
                                        </div>
                                        <span class="invalid-feedback" id="file-error" role="alert"></span>
                                    </div>
                                    <div class="col-4">
                                        <div class="btn-group ms-2 mt-4 d-none d-sm-inline-block">
                                            <button type="submit" class="{{ VC::BT_SM_PM }} ms-2 {{ VC::TXT_WT }}">
                                                {{ __('Upload') }}
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                            <div class="row mt-3" id="comments-file">
                                @php
                                    $bugFiles = data_get($bug, 'bugFiles', []);
                                    $bugFiles = is_countable($bugFiles) ? $bugFiles : [];
                                @endphp
                                @if(!empty($bugFiles) && count($bugFiles) > 0)
                                    @foreach($bugFiles as $file)
                                        @if(isset($file) && is_object($file))
                                            @php
                                                $fileId                          = data_get($file ?? null, 'id');
                                                $fileNameText                    = e(data_get($file ?? null, 'name', __('Unknown File')));
                                                $fileSizeText                    = e(data_get($file ?? null, 'file_size', __('Unknown Size')));
                                                $filePathValue                   = data_get($file ?? null, 'file', __('Unknown Path'));
                                                $downloadUrl                     = (!empty($filePathValue) && Storage::exists('bugs/'.$filePathValue)) ? asset(Storage::url('bugs/'.$filePathValue)) : '';
                                                $commentFileDestroyBaseName      = ViewsConstants::PRJ_BUG_CM.'.file.destroy';
                                                $commentFileDestroyKebabName     = Str::kebab($commentFileDestroyBaseName);
                                                $commentFileDestroyResolvedName  = Route::has($commentFileDestroyBaseName) ? $commentFileDestroyBaseName : (Route::has($commentFileDestroyKebabName) ? $commentFileDestroyKebabName : null);
                                                $commentFileDestroyUrl           = ($commentFileDestroyResolvedName && !empty($fileId)) ? route($commentFileDestroyResolvedName, [$fileId]) : '#';
                                                $commentFileDestroyGuardMsg      = Utility::fetchLinkMessage($lang, ViewsConstants::PRJ_BUG_CM, 'delete_bug_comment_file_route_unavailable') ?? 'Delete bug comment file route is unavailable. Please contact technical support or your domain administrator.';
                                                $commentFileDestroyLinkId        = 'comment-file-destroy-link-'.($fileId ?? 'x');
                                            @endphp
                                            <div class="col-8 mb-2 file-{{ $fileId }}">
                                                <h5 class="{{ VC::MT3 }} {{ VC::MB1 }} font-weight-bold {{ VC::TXSM }}">{{ $fileNameText }}</h5>
                                                <p class="{{ VC::MB0 }} {{ VC::TXS }}">{{ $fileSizeText }}</p>
                                            </div>
                                            <div class="col-4 mb-2 file-{{ $fileId }}">
                                                <div class="comment-trash" style="float: right">
                                                    @if(!empty($downloadUrl))
                                                        <a download href="{{ $downloadUrl }}" class="{{ VC::BT_SM_PM }}">
                                                            <i class="{{ VC::TI_DWN }}"></i>
                                                        </a>
                                                    @endif
                                                    <a href="{{ $commentFileDestroyUrl }}"
                                                    id="{{ $commentFileDestroyLinkId }}"
                                                    class="{{ VC::BT_SM_DG }} m-0 px-2 delete-comment-file"
                                                    data-id="{{ $fileId }}"
                                                    data-url="{{ $commentFileDestroyUrl }}"
                                                    data-guard-msg="{{ $commentFileDestroyGuardMsg }}">
                                                        <i class="{{ VC::TI_TRS }}"></i>
                                                    </a>
                                                </div>
                                            </div>
                                            <script defer>
                                                (() => {
                                                    try {
                                                        const l = document.getElementById('{{ $commentFileDestroyLinkId }}');
                                                        if (!l || l.getAttribute('data-listener-active') === 'true') return;
                                                        l.setAttribute('data-listener-active', 'true');
                                                        l.addEventListener('click', e => {
                                                            try {
                                                                const href = l.getAttribute('href') || '#';
                                                                const url = l.getAttribute('data-url') || href || '#';
                                                                if (href !== '#' || url !== '#') return;
                                                                e.preventDefault();
                                                                const msg = l.getAttribute('data-guard-msg') || 'Delete bug comment file route is unavailable. Please contact technical support or your domain administrator.';
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
                                                                l.setAttribute('data-failed-route', 'true');
                                                            } catch (err) {}
                                                        });
                                                    } catch (error) {}
                                                })();
                                            </script>
                                        @endif
                                    @endforeach
                                @else
                                    <div class="col-12 text-center {{ VC::TXT_MT }}">
                                        {{ __('No files uploaded yet') }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script defer>
        (() => {
            try {
                const f = document.getElementById('{{ $commentStoreFormId }}');
                if (f && f.getAttribute('data-listener-active') !== 'true') {
                    f.setAttribute('data-listener-active', 'true');
                    f.addEventListener('submit', e => {
                        try {
                            const url = f.getAttribute('data-url') || '#';
                            const action = f.getAttribute('action') || '#';
                            if (url !== '#' || action !== '#') return;
                            e.preventDefault();
                            const msg = f.getAttribute('data-guard-msg') || 'Store bug comment route is unavailable. Please contact technical support or your domain administrator.';
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
                            f.setAttribute('data-failed-route', 'true');
                        } catch (err) {}
                    });
                }
                const b = document.getElementById('{{ $commentStoreBtnId }}');
                if (b && b.getAttribute('data-listener-active') !== 'true') {
                    b.setAttribute('data-listener-active', 'true');
                    b.addEventListener('click', () => {
                        try {
                            if (!f) return;
                            if (typeof f.requestSubmit === 'function') {
                                f.requestSubmit();
                            } else {
                                f.submit();
                            }
                        } catch (err) {}
                    });
                }
            } catch (error) {}
        })();
    </script>
    <script defer>
        (() => {
            try {
                const f = document.getElementById('{{ $commentFileStoreFormId }}');
                if (!f || f.getAttribute('data-listener-active') === 'true') return;
                f.setAttribute('data-listener-active', 'true');
                f.addEventListener('submit', e => {
                    try {
                        const url = f.getAttribute('data-url') || '#';
                        const action = f.getAttribute('action') || '#';
                        if (url !== '#' || action !== '#') return;
                        e.preventDefault();
                        const msg = f.getAttribute('data-guard-msg') || 'Store bug comment file route is unavailable. Please contact technical support or your domain administrator.';
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
                        f.setAttribute('data-failed-route', 'true');
                    } catch (err) {}
                });
            } catch (error) {}
        })();
    </script>
@else
    <div class="modal-body">
        <div class="alert alert-danger text-center">
            {{ __('Bug data is not available') }}
        </div>
    </div>
@endif

{{--<div class="modal-footer">--}}
{{--    <input type="button" value="{{__('Cancel')}}" class="btn btn-light" data-bs-dismiss="modal">--}}
{{--</div>--}}
