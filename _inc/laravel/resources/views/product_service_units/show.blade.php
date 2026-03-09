@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        YieldingConstants,
        StacksConstants,
        ViewsConstants as VW
    };
    $unit ??= null;
@endphp

@extends(ExtendingLayoutsConstants::ADM)

@section(YieldingConstants::ADM_PG_TTL)
    {{ __('Unit Detail') }}
@endsection

@section(YieldingConstants::ADM_BDC)
    <li class="{{ VC::BCI }}">
        <a href="{{ Route::has(VW::PRD_SV_UNT . '.index') ? route(VW::PRD_SV_UNT . '.index') : '#' }}">
            {{ __('Units') }}
        </a>
    </li>
    <li class="{{ VC::BCI_ACT }}" aria-current="page">{{ __('Show') }}</li>
@endsection

@section(YieldingConstants::ADM_CTT)
    <div class="row">
        <div class="{{ VC::CM6 }}">
            <div class="card">
                <div class="{{ VC::CD_HD }}">
                    <h5>{{ __('Unit Details') }}</h5>
                </div>
                <div class="{{ VC::CD_BD }}">
                    @if($unit)
                        <div class="{{ VC::MB3 }}">
                            <strong>{{ __('Name') }}:</strong>
                            <span>{{ $unit->name ?? '-' }}</span>
                        </div>
                    @else
                        <p class="{{ VC::TXT_MT }}">{{ __('No unit data available.') }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
