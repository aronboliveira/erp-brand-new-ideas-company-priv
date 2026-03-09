@php
    try {
        $ogTitle = ! empty($meta_title) ? $meta_title : 'ERP';
        $ogDesc = ! empty($meta_desc)  ? $meta_desc  : 'Your personalized ERP, made for businesses success!';
        $ogImg  = ! empty($meta_image) ? $meta_image : $meta_logo;
    } catch (\Throwable $e) {
        \Log::error('fragments/og — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    }
@endphp
<meta property="og:type"        content="website">
<meta property="og:url"         content="{{ env('APP_URL') }}">
<meta property="og:title"       content="{{ $ogTitle }}">
<meta property="og:description" content="{{ $ogDesc }}">
<meta property="og:image"       content="{{ $ogImg }}">
