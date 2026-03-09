@php
    try {
        $twTitle = ! empty($meta_title) ? $meta_title : 'ERP';
        $twDesc = ! empty($meta_desc)  ? $meta_desc  : 'Your personalized ERP, made for businesses success!';
        $twImg  = ! empty($meta_image) ? $meta_image : $meta_logo;
    } catch (\Throwable $e) {
        \Log::error('fragments/x — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    }
@endphp
<meta property="twitter:card"        content="summary_large_image">
<meta property="twitter:url"         content="{{ env('APP_URL') }}">
<meta property="twitter:title"       content="{{ $twTitle }}">
<meta property="twitter:description" content="{{ $twDesc }}">
<meta property="twitter:image"       content="{{ $twImg }}">
