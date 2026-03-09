@php
    try {
} catch (\Throwable $e) {
        \Log::error('vendor/Chatify/layouts/favorite — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    }
@endphp
<div class="favorite-list-item">
    <div data-id="{{ (string) data_get($user,'id','') }}"
         data-action="0"
         class="{{ VC::AV }} {{ VC::AV_CC_SM }}"
         style="background-image: url('{{ asset('/storage/'.config('chatify.user_avatar.folder','uploads/avatar').'/'.(data_get($user,'avatar') ?: 'avatar.png')) }}');">
    </div>
    <p class="{{ VC::TXSM }}">
        {{ ($__n = (string) (data_get($user,'name') ?? '')) !== '' ? Str::limit($__n, 6, '..') : __('No name available') }}
    </p>
</div>
