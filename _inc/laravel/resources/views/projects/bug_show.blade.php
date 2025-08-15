@php
    use App\Config\Constants\{
        ViewsConstants,
        ViewClassNamesConstants as VC,
    };
    use App\Models\Utility;
    use Illuminate\Support\{Facades\Route, Str};
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
                                $projectId = data_get($bug, 'project_id');
                                $bugId = data_get($bug, 'id');
                                $commentStoreRoute = '';
                                
                                if (!empty($projectId) && !empty($bugId)) {
                                    try {
                                        if (Route::has(ViewsConstants::PRJ_BUG_CM . '.store')) {
                                            $commentStoreRoute = route(ViewsConstants::PRJ_BUG_CM . '.store', [$projectId, $bugId]);
                                        }
                                    } catch (Exception $e) {
                                        $commentStoreRoute = '#';
                                    }
                                }
                            @endphp
                            
                            @if(!empty($commentStoreRoute) && $commentStoreRoute !== '#')
                                <form method="post" id="form-comment" data-action="{{ $commentStoreRoute }}">
                                    @csrf
                                    <textarea class="{{ VC::FM_CT }}" name="comment" 
                                              placeholder="{{ __('Write message') }}" 
                                              id="example-textarea" rows="3" required></textarea>
                                    <div class="text-end mt-1">
                                        <div class="btn-group mb-2 ms-2 d-none d-sm-inline-block">
                                            <button type="button" class="{{ VC::BT_SM_PM }} ms-2 {{ VC::TXT_WT }}">
                                                {{ __('Submit') }}
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            @else
                                <div class="alert alert-warning">
                                    {{ __('Comment functionality is not available') }}
                                </div>
                            @endif
                            
                            <div class="comment-holder" id="comments">
                                @php
                                    $comments = data_get($bug, 'comments', []);
                                    $comments = is_countable($comments) ? $comments : [];
                                @endphp
                                
                                @if(!empty($comments) && count($comments) > 0)
                                    @foreach($comments as $comment)
                                        @if(isset($comment) && is_object($comment))
                                            @php
                                                $commentId = data_get($comment, 'id');
                                                $commentUser = data_get($comment, 'user');
                                                $userName = '';
                                                if (isset($commentUser) && is_object($commentUser)) {
                                                    $userName = data_get($commentUser, 'name', '');
                                                }
                                                $commentText = data_get($comment, 'comment', '');
                                                
                                                $deleteRoute = '';
                                                if (!empty($commentId)) {
                                                    try {
                                                        if (Route::has(ViewsConstants::PRJ_BUG_CM . '.destroy')) {
                                                            $deleteRoute = route(ViewsConstants::PRJ_BUG_CM . '.destroy', $commentId);
                                                        }
                                                    } catch (Exception $e) {
                                                        $deleteRoute = '#';
                                                    }
                                                }
                                            @endphp
                                            
                                            <div class="media">
                                                <div class="media-body">
                                                    <div class="{{ VC::DFL_AIC_JCB }} align-items-end">
                                                        <div>
                                                            <h5 class="{{ VC::MT3 }} {{ VC::MB0 }}">
                                                                {{ !empty($userName) ? e($userName) : __('Anonymous User') }}
                                                            </h5>
                                                            <p class="{{ VC::MB0 }} {{ VC::TXS }}">
                                                                {{ !empty($commentText) ? e($commentText) : __('No Comment') }}
                                                            </p>
                                                        </div>
                                                        @if(!empty($deleteRoute) && $deleteRoute !== '#')
                                                            <a href="#" class="{{ VC::BT_SM_DG }} delete-comment" 
                                                               data-url="{{ $deleteRoute }}">
                                                                <i class="{{ VC::TI_TRS }}"></i>
                                                            </a>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
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
                                $fileStoreRoute = '';
                                if (!empty($bugId)) {
                                    try {
                                        if (Route::has(ViewsConstants::PRJ_BUG_CM . '.file.store')) {
                                            $fileStoreRoute = route(ViewsConstants::PRJ_BUG_CM . '.file.store', $bugId);
                                        }
                                    } catch (Exception $e) {
                                        $fileStoreRoute = '';
                                    }
                                }
                            @endphp
                            
                            @if(!empty($fileStoreRoute))
                                <form method="post" id="form-file" enctype="multipart/form-data" data-url="{{ $fileStoreRoute }}">
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
                            @else
                                <div class="alert alert-warning">
                                    {{ __('File upload functionality is not available') }}
                                </div>
                            @endif
                            
                            <div class="row mt-3" id="comments-file">
                                @php
                                    $bugFiles = data_get($bug, 'bugFiles', []);
                                    $bugFiles = is_countable($bugFiles) ? $bugFiles : [];
                                @endphp
                                
                                @if(!empty($bugFiles) && count($bugFiles) > 0)
                                    @foreach($bugFiles as $file)
                                        @if(isset($file) && is_object($file))
                                            @php
                                                $fileId = data_get($file, 'id');
                                                $fileName = data_get($file, 'name', __('Unknown File'));
                                                $fileSize = data_get($file, 'file_size', __('Unknown Size'));
                                                $filePath = data_get($file, 'file', '');
                                                
                                                $downloadUrl = '';
                                                $deleteFileRoute = '';
                                                
                                                if (!empty($filePath) && Storage::exists('bugs/' . $filePath)) {
                                                    $downloadUrl = asset(Storage::url('bugs/' . $filePath));
                                                }
                                                
                                                if (!empty($fileId)) {
                                                    try {
                                                        if (Route::has(ViewsConstants::PRJ_BUG_CM . '.file.destroy')) {
                                                            $deleteFileRoute = route(ViewsConstants::PRJ_BUG_CM . '.file.destroy', [$fileId]);
                                                        }
                                                    } catch (Exception $e) {
                                                        $deleteFileRoute = '#';
                                                    }
                                                }
                                            @endphp
                                            
                                            <div class="col-8 mb-2 file-{{ $fileId }}">
                                                <h5 class="{{ VC::MT3 }} {{ VC::MB1 }} font-weight-bold {{ VC::TXSM }}">{{ e($fileName) }}</h5>
                                                <p class="{{ VC::MB0 }} {{ VC::TXS }}">{{ e($fileSize) }}</p>
                                            </div>
                                            <div class="col-4 mb-2 file-{{ $fileId }}">
                                                <div class="comment-trash" style="float: right">
                                                    @if(!empty($downloadUrl))
                                                        <a download href="{{ $downloadUrl }}" class="{{ VC::BT_SM_PM }}">
                                                            <i class="{{ VC::TI_DWN }}"></i>
                                                        </a>
                                                    @endif
                                                    @if(!empty($deleteFileRoute) && $deleteFileRoute !== '#')
                                                        <a href="#" class="{{ VC::BT_SM_DG }} m-0 px-2 delete-comment-file" 
                                                           data-id="{{ $fileId }}" data-url="{{ $deleteFileRoute }}">
                                                            <i class="{{ VC::TI_TRS }}"></i>
                                                        </a>
                                                    @endif
                                                </div>
                                            </div>
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
