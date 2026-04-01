@extends('email.common')

@section('content')
    {{-- purify_html: email body may originate from user-editable templates --}}
    {!! purify_html($content ?? '') !!}
@endsection
