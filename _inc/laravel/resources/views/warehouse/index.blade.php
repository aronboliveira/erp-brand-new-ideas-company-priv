@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        YieldingConstants,
        StacksConstants,
        ViewsConstants as VW
    };
    use App\Models\Utility;
    use Illuminate\Support\Facades\{Log, Route};

    $lang ??= 'en';
    $warehouses ??= collect();

    try {
        $lang = Utility::fetchUserLang() ?? 'en';
    } catch (\Throwable $e) {
        Log::error('warehouse/index — ' . get_class($e) . ': ' . $e->getMessage(), [
            'file' => $e->getFile(),
            'line' => $e->getLine(),
        ]);
    }
@endphp

@extends(ExtendingLayoutsConstants::ADM)

@section(YieldingConstants::ADM_PG_TTL)
    {{ __('Manage Warehouses') }}
@endsection

@push(StacksConstants::ADM_SCR_PG)
@endpush

@section(YieldingConstants::ADM_BDC)
    <li class="{{ VC::BCI }}">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}">{{ __('Dashboard') }}</a>
    </li>
    <li class="{{ VC::BCI_ACT }}" aria-current="page">{{ __('Warehouses') }}</li>
@endsection

@section(YieldingConstants::ADM_CTT)
    <div class="row">
        <div class="{{ VC::CXL12 }}">
            <div class="card">
                <div class="{{ VC::CD_HD }}">
                    <div class="{{ VC::DFL_AIC_JCB }}">
                        <h5>{{ __('Warehouses') }}</h5>
                        @if(Route::has(VW::WRH . '.create'))
                            <a href="{{ route(VW::WRH . '.create') }}" class="{{ VC::BT_SM_PM }}">
                                <i class="{{ VC::TI_PLS }}"></i> {{ __('Create') }}
                            </a>
                        @endif
                    </div>
                </div>
                <div class="{{ VC::CD_BD_TB_BD }}">
                    <div class="{{ VC::TB_RSP }}">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>{{ __('Name') }}</th>
                                    <th class="{{ VC::TX_END }}">{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($warehouses as $warehouse)
                                    <tr>
                                        <td>{{ $warehouse->name ?? '-' }}</td>
                                        <td class="{{ VC::TX_END }}">
                                            {{-- Action buttons --}}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="2" class="{{ VC::TXCT_MT }}">
                                            {{ __('No warehouses found.') }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
