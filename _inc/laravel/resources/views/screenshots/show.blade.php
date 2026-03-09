@php
    use App\Config\Constants\ExtendingLayoutsConstants as EL;
@endphp

@extends(EL::ADM)

@section('page-title')
    {{ __('Screenshot Details') }}
@endsection

@section('content')
<div class="row">
    <div class="{{ VC::C12 }}">
        <div class="card">
            <div class="{{ VC::CD_HD }}">
                <h5>{{ __('Screenshot Details') }}</h5>
            </div>
            <div class="{{ VC::CD_BD }}">
                <p class="{{ VC::TXT_MT }}">{{ __('Screenshot details page — stub view. Content will be added as needed.') }}</p>
            </div>
        </div>
    </div>
</div>
@endsection
