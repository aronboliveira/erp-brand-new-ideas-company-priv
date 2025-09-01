@php
    use Illuminate\Support\{Facades\Route, Str};
    use App\Models\Utility;

    $lang = Utility::fetchUserLang();
@endphp

@if((string)($viewType ?? '') === 'default')
    @if((string)($from_id ?? '') !== (string)($to_id ?? ''))
        @php
            $attName  = data_get($attachment ?? [], 0);
            $attLabel = data_get($attachment ?? [], 1);
            $attType  = data_get($attachment ?? [], 2);
            $isFile   = ($attType === 'file');
            $isImage  = ($attType === 'image');
            $imgUrl   = $attName ? Utility::get_file((string) config('chatify.attachments.folder').'/'.$attName) : null;
            $msgBody  = (is_null($message ?? null) && !empty($attachment) && !$isFile)
                        ? (e($attLabel) ?: __('No attachment label available'))
                        : (nl2br(e(($message ?? '') !== '' ? $message : __('No message content available'))));
            $dlBase   = (string) config('chatify.attachments.download_route_name');
            $dlName   = Route::has($dlBase) ? $dlBase : (Route::has(Str::kebab($dlBase)) ? Str::kebab($dlBase) : null);
            $dlUrl    = ($dlName && $attName) ? route($dlName, ['fileName' => $attName]) : '#';
            $dlGuard  = Utility::fetchLinkMessage($lang, 'messenger', 'download_attachment_route_unavailable') ?? 'Download attachment route is unavailable. Please contact technical support or your domain administrator.';
            $dlId     = 'attachment-download-link-' . (string) ($id ?? Str::uuid());
        @endphp
        <div class="message-card" data-id="{{ (string)($id ?? '') }}">
            <p>
                {!! $msgBody !!}
                <sub title="{{ (string)($fullTime ?? __('No timestamp available')) }}">{{ (string)($time ?? __('No time available')) }}</sub>
                @if($isFile && $attName)
                    <a id="{{ $dlId }}"
                       href="{{ $dlUrl }}"
                       data-url="{{ $dlUrl }}"
                       data-guard-msg="{{ $dlGuard }}"
                       class="file-download"
                       style="color:#595959;">
                        <span class="ti ti-file"></span> {{ $attLabel ?: __('No file name available') }}
                    </a>
                @endif
            </p>
        </div>
        @if($isImage && $imgUrl)
            <div>
                <div class="message-card">
                    <div class="image-file chat-image" style="width:250px;height:150px;background-image:url('{{ $imgUrl }}')"></div>
                </div>
            </div>
        @endif
    @endif
@endif

@if((string)($viewType ?? '') === 'sender')
    @php
        $attName  = data_get($attachment ?? [], 0);
        $attLabel = data_get($attachment ?? [], 1);
        $attType  = data_get($attachment ?? [], 2);
        $isFile   = ($attType === 'file');
        $isImage  = ($attType === 'image');
        $imgUrl   = $attName ? Utility::get_file((string) config('chatify.attachments.folder').'/'.$attName) : null;
        $msgBody  = (is_null($message ?? null) && !empty($attachment) && !$isFile)
                    ? (e($attLabel) ?: __('No attachment label available'))
                    : (nl2br(e(($message ?? '') !== '' ? $message : __('No message content available'))));
        $dlBase   = (string) config('chatify.attachments.download_route_name');
        $dlName   = Route::has($dlBase) ? $dlBase : (Route::has(Str::kebab($dlBase)) ? Str::kebab($dlBase) : null);
        $dlUrl    = ($dlName && $attName) ? route($dlName, ['fileName' => $attName]) : '#';
        $dlGuard  = Utility::fetchLinkMessage($lang, 'messenger', 'download_attachment_route_unavailable') ?? 'Download attachment route is unavailable. Please contact technical support or your domain administrator.';
        $dlId     = 'attachment-download-link-' . (string) ($id ?? Str::uuid());
    @endphp
    <div class="message-card mc-sender" data-id="{{ (string)($id ?? '') }}">
        <p>
            {!! $msgBody !!}
            <sub title="{{ (string)($fullTime ?? __('No timestamp available')) }}" class="message-time">
                <span class="ti ti-{{ ((int)($seen ?? 0) > 0) ? 'check-double' : 'check' }} seen"></span>
                {{ (string)($time ?? __('No time available')) }}
            </sub>
            @if($isFile && $attName)
                <a id="{{ $dlId }}"
                   href="{{ $dlUrl }}"
                   data-url="{{ $dlUrl }}"
                   data-guard-msg="{{ $dlGuard }}"
                   class="file-download">
                    <span class="ti ti-file"></span> {{ $attLabel ?: __('No file name available') }}
                </a>
            @endif
        </p>
    </div>
    @if($isImage && $imgUrl)
        <div>
            <div class="message-card mc-sender">
                <div class="image-file chat-image" style="width:250px;height:150px;background-image:url('{{ $imgUrl }}')"></div>
            </div>
        </div>
    @endif
@endif

@once
    <script defer src="{{ asset('assets/js/routes/vendors/chatify/card.js') }}"></script>
@endonce
