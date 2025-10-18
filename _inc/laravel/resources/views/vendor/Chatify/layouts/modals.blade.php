@php
    use Illuminate\Support\Collection;
    use App\Config\Constants\ViewClassNamesConstants as VC;
    use App\Models\Utility;
    use Illuminate\Support\Str;
    use Illuminate\Support\Facades\{Auth, Route};
    $auth = Auth::user();
    $avatarFolder = config('chatify.user_avatar.folder','uploads/avatar');
    $avatarFile = data_get($auth,'avatar') ?: 'avatar.png';
    $avatarPathTmp = Utility::getFile('/'.$avatarFolder.'/'.$avatarFile);
    $avatarPath = $avatarPathTmp ?: asset('/storage/'.$avatarFolder.'/avatar.png');
    $darkMode = (int) (data_get($auth,'dark_mode',0)) > 0 ? 1 : 0;
    $appName = config('chatify.name') ?: __('Messenger');
@endphp
<div id="imageModalBox" class="imageModal"><span class="imageModal-close">&times;</span><img class="imageModal-content" id="imageModalBoxSrc"></div>
<div class="app-modal" data-name="delete">
    <div class="app-modal-container">
        <div class="app-modal-card" data-name="delete" data-modal="0">
            <div class="app-modal-header">{{ __('Are you sure you want to delete this?') }}</div>
            <div class="app-modal-body">{{ __('You can not undo this action') }}</div>
            <div class="app-modal-footer"><a href="javascript:void(0)" class="app-btn cancel">{{ __('Cancel') }}</a><a href="javascript:void(0)" class="app-btn a-btn-danger delete">{{ __('Delete') }}</a></div>
        </div>
    </div>
</div>
<div class="app-modal" data-name="alert">
    <div class="app-modal-container">
        <div class="app-modal-card" data-name="alert" data-modal="0">
            <div class="app-modal-header"></div>
            <div class="app-modal-body"></div>
            <div class="app-modal-footer"><a href="javascript:void(0)" class="app-btn cancel">{{ __('Cancel') }}</a></div>
        </div>
    </div>
</div>
<div class="app-modal" data-name="settings">
    <div class="app-modal-container">
        <div class="app-modal-card" data-name="settings" data-modal="0">
            @php
                $avatarUpdateBase = 'avatar.update';
                $avatarUpdateKebab = Str::kebab($avatarUpdateBase);
                $avatarUpdateResolved = Route::has($avatarUpdateBase) ? $avatarUpdateBase : (Route::has($avatarUpdateKebab) ? $avatarUpdateKebab : null);
                $avatarUpdateUrl = $avatarUpdateResolved ? route($avatarUpdateResolved) : '#';
                $langValue = isset($lang) ? $lang : Utility::fetchUserLang();
                $avatarUpdateGuardMsg = Utility::fetchLinkMessage($langValue, 'messenger', 'update_avatar_route_unavailable') ?? 'Update avatar route is unavailable. Please contact technical support or your domain administrator.';
                $formId = 'update-settings';
            @endphp
            <form id="{{ $formId }}"
                action="{{ $avatarUpdateUrl }}"
                enctype="multipart/form-data"
                method="POST"
                data-url="{{ $avatarUpdateUrl }}"
                data-guard-msg="{{ $avatarUpdateGuardMsg }}"
                data-sv-localized="true">
                @csrf
                <div class="app-modal-header">{{ __('Update your profile settings') }}</div>
                <div class="app-modal-body">
                    <div class="{{ VC::AV }} av-l upload-avatar-preview" style="background-image:url('{{ $avatarPath }}');" role="img" aria-label="{{ __('User avatar') }}"></div>
                    <p class="upload-avatar-details"></p>
                    <label class="app-btn a-btn-primary update">{{ __('Upload profile photo') }}
                        <input class="upload-avatar" accept="image/*" name="avatar" type="file" style="display:none">
                    </label>
                    <p class="divider"></p>
                    <p class="app-modal-header">{{ __('Dark Mode') }}<span class="{{ $darkMode ? VC::FAS : 'far' }} fa-moon dark-mode-switch" data-mode="{{ $darkMode }}"></span></p>
                    <p class="divider"></p>
                    <p class="app-modal-header">{{ __('Change :name Color', ['name' => $appName]) }}</p>
                    <div class="update-messengerColor">
                        <span class="messengerColor-1 color-btn"></span><span class="messengerColor-2 color-btn"></span><span class="messengerColor-3 color-btn"></span><span class="messengerColor-4 color-btn"></span><span class="messengerColor-5 color-btn"></span><br><span class="messengerColor-6 color-btn"></span><span class="messengerColor-7 color-btn"></span><span class="messengerColor-8 color-btn"></span><span class="messengerColor-9 color-btn"></span><span class="messengerColor-10 color-btn"></span>
                    </div>
                </div>
                <div class="app-modal-footer">
                    <a href="javascript:void(0)" class="app-btn cancel">{{ __('Cancel') }}</a>
                    <input type="submit" class="app-btn a-btn-success update" value="{{ __('Update') }}">
                </div>
            </form>
            <script defer src="{{ asset('js/routes/vendors/chatify/settings/update.js') }}"></script>
        </div>
    </div>
</div>
