@php
    try {
$user = Auth::user();
        $avatarFolder = config('chatify.user_avatar.folder','uploads/avatar');
        $avatarBase = '/'.$avatarFolder.'/';
        $avatarUrl = function($u) use ($avatarBase,$avatarFolder){
            $file = (string) (data_get($u,'avatar') ?: 'avatar.png');
            $url = \App\Models\Utility::getFile($avatarBase.$file);
            return $url ?: asset('/storage/'.$avatarFolder.'/'.$file);
        };
    } catch (\Throwable $e) {
        \Log::error('vendor/Chatify/layouts/list_item — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    }
@endphp
@if(($get ?? '') === 'saved')
    <table class="messenger-list-item m-li-divider @if(('user_'.(string)($user?->id ?? '')) === (string)($id ?? '') && (string)($id ?? '') !== '0') m-list-active @endif">
        <tr data-action="0">
            <td><div class="{{ VC::AV_CC_SM }}" style="background-color:#D9EFFF; text-align:center;"><span class="{{ VC::TI_CC_PLS }}" style="font-size:22px; color:#6FD943;"></span></div></td>
            <td>
                <p data-id="{{ 'user_'.(string)($user?->id ?? '') }}">{{ __('Saved Messages') }} <span>{{ __('You') }}</span></p>
                <span>{{ __('Save messages secretly') }}</span>
            </td>
        </tr>
    </table>
@endif
@if(($get ?? '') === 'users')
    <table class="messenger-list-item @if((string) data_get($user,'id','') === (string)($id ?? '') && (string)($id ?? '') !== '0') m-list-active @endif" data-contact="{{ (string) data_get($user,'id','') }}">
        <tr data-action="0">
            <td style="position:relative">
                @if((bool) data_get($user,'active_status'))<span class="activeStatus"></span>@endif
                <div class="{{ VC::AV_CC_SM }}" style="background-image:url('{{ $avatarUrl($user ?? null) }}');"></div>
            </td>
            @php
 $lm = $lastMessage ?? null;
@endphp
            @if($lm)
                <td>
                    <p data-id="{{ (string)($type ?? 'user') . '_' . (string) data_get($user,'id','') }}">
                        {{ ($n = (string) data_get($user,'name','')) !== '' ? Str::limit($n,12,'..') : __('No name available') }}
                        <span class="chat-time">{{ (data_get($lm,'created_at') && method_exists(data_get($lm,'created_at'),'diffForHumans')) ? data_get($lm,'created_at')->diffForHumans() : '' }}</span>
                    </p>
                    <span class="chat-msg">
                        {!! ((string) data_get($lm,'from_id','') === (string) ($user?->id ?? '')) ? '<span class="lastMessageIndicator">You :</span>' : '' !!}
                        @if(is_null(data_get($lm,'attachment')))
                            {{ ($b = (string) data_get($lm,'body','')) !== '' ? Str::limit($b,30,'..') : __('No message available') }}
                        @else
                            <span class="{{ VC::TI_FL }}"></span> {{ __('Attachment') }}
                        @endif
                    </span>
                    {!! ((int)($unseenCounter ?? 0) > 0) ? '<b>'.(int) $unseenCounter.'</b>' : '' !!}
                </td>
            @else
                <td></td>
            @endif
        </tr>
    </table>
@endif
@if(($get ?? '') === 'search_item')
    <table class="messenger-list-item" data-contact="{{ (string) data_get($user,'id','') }}">
        <tr data-action="0">
            <td style="position:relative">
                @if((bool) data_get($user,'active_status'))<span class="activeStatus"></span>@endif
                <div class="{{ VC::AV_CC_SM }}" style="background-image:url('{{ $avatarUrl($user ?? null) }}');"></div>
            </td>
            <td>
                <p data-id="{{ (string)($type ?? 'user') . '_' . (string) data_get($user,'id','') }}">{{ ($n = (string) data_get($user,'name','')) !== '' ? Str::limit($n,12,'..') : __('No name available') }}</p>
            </td>
        </tr>
    </table>
@endif
@if(($get ?? '') === 'all_members')
    <table class="messenger-list-item" data-contact="{{ (string) data_get($user,'id','') }}">
        <tr data-action="0">
            <td style="position:relative">
                @if((bool) data_get($user,'active_status'))<span class="activeStatus"></span>@endif
                <div class="{{ VC::AV_CC_SM }}" style="background-image:url('{{ $avatarUrl($user ?? null) }}');"></div>
            </td>
            <td>
                <p data-id="{{ (string)($type ?? 'user') . '_' . (string) data_get($user,'id','') }}">{{ ($n = (string) data_get($user,'name','')) !== '' ? Str::limit($n,12,'..') : __('No name available') }}</p>
            </td>
        </tr>
    </table>
@endif
@if(($get ?? '') === 'sharedPhoto')
    <div class="shared-photo chat-image" style="background-image:url('{{ (string)($image ?? '') !== '' ? $image : asset('/images/placeholder.png') }}')"></div>
@endif
