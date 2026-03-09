@php
    try {
$raw = $messengerColor ?? '';
        $mc = (is_string($raw) && preg_match('/^#(?:[0-9a-fA-F]{3}){1,2}$/', $raw)) ? $raw : '#9ca3af';
    } catch (\Throwable $e) {
        \Log::error('vendor/Chatify/layouts/messenger_color — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    }
@endphp
<style>
    #nprogress .bar{background: {{ $mc }};}
    #nprogress .peg{box-shadow:0 0 10px {{ $mc }},0 0 5px {{ $mc }};}
    #nprogress .spinner-icon{border-top-color: {{ $mc }};border-left-color: {{ $mc }};}
    .m-header svg{color: {{ $mc }};}
    .messenger-list-item td b{background: {{ $mc }};}
    .messenger-infoView nav a{color: {{ $mc }};}
    .messenger-infoView-btns a.default{color: {{ $mc }};}
    .mc-sender p{background: {{ $mc }};}
    .messenger-sendCard button svg{color: {{ $mc }};}
    .messenger-listView-tabs a,.messenger-listView-tabs a:hover,.messenger-listView-tabs a:focus{color: {{ $mc }};}
    .active-tab{border-bottom:2px solid {{ $mc }};}
    .lastMessageIndicator{color: {{ $mc }};}
    .messenger-favorites div.avatar{box-shadow:0 0 0 2px {{ $mc }};}
    .dark-mode-switch{color: {{ $mc }};}
    .m-list-active b{background:#fff !important;color: {{ $mc }} !important;}
</style>
