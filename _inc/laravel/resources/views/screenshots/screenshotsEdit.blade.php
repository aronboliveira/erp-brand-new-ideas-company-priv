@php
    use App\Config\Constants\ExtendingLayoutsConstants as EL;
@endphp

@extends(EL::ADM)

@section('page-title')
    {{ __('Edit Screenshot') }}
@endsection

@section('content')
<div class="row">
    <div class="{{ VC::C12 }}">
        <div class="card">
            <div class="{{ VC::CD_HD }}">
                <h5>{{ __('Edit Screenshot') }}</h5>
            </div>
            <div class="{{ VC::CD_BD }}">
                <p class="{{ VC::TXT_MT }}">{{ __('Screenshot editing page — stub view. Content will be added as needed.') }}</p>
            </div>
        </div>
    </div>
</div>
@endsection
