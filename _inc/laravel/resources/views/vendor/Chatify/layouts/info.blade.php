@php
    use App\Config\Constants\ViewClassNamesConstants as VC;
    use App\Models\Utility;
    use Illuminate\Support\Facades\Auth;
    $profile = Utility::get_file('uploads/avatar/');
    $avatarUrl = !empty($user?->avatar)
        ? ($profile . '/' . $user->avatar)
        : asset('/storage/' . config('chatify.user_avatar.folder') . '/avatar.png');
@endphp

<div class="{{ VC::AV_CC }} av-l"
     style="background-image: url('{{ $avatarUrl }}');"
     role="img" aria-label="{{ __('User avatar') }}">
</div>

<p class="info-name">{{ config('chatify.name') ?? __('Failed to get app name') }}</p>

<div class="messenger-infoView-btns">
    <a href="#" class="danger delete-conversation">
        <i class="{{ VC::TI_TRS }}"></i> {{ __('Delete Conversation') }}
    </a>
</div>

<div class="messenger-infoView-shared">
    <p class="messenger-title">{{ __('Shared photos') }}</p>
    <div class="shared-photos-list"></div>
</div>


{{-- <a href="#" class="default"><i class="ti ti-camera"></i> default</a> --}}
{{--    <div class="avatar av-l" style="background-image: url('{{ asset('/storage/'.config('chatify.user_avatar.folder').'/'.Auth::user()->avatar) }}');">--}}
{{--        --}}
{{--    </div>--}}