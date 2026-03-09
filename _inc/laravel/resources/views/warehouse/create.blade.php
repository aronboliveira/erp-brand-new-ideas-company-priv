@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        YieldingConstants,
        StacksConstants,
        ViewsConstants as VW
    };
    use App\Models\Utility;
    use Illuminate\Support\Str;
    use Illuminate\Support\Facades\{Log, Route};

    $lang ??= 'en';
    $formId ??= 'warehouse-store-form';
    $actionUrl ??= '#';
    $guardMsg ??= '';

    try {
        $lang = Utility::fetchUserLang() ?? 'en';
        $base = VW::WRH;
        $baseKebab = Str::kebab($base);
        $routeRes = Route::has($base . '.store') ? $base . '.store' : (Route::has($baseKebab . '.store') ? $baseKebab . '.store' : null);
        $actionUrl = $routeRes ? route($routeRes) : '#';
        $guardMsg = 'Warehouse store route is unavailable. Please contact technical support.';
    } catch (\Throwable $e) {
        Log::error('warehouse/create — ' . get_class($e) . ': ' . $e->getMessage(), [
            'file' => $e->getFile(),
            'line' => $e->getLine(),
        ]);
    }
@endphp

@extends(ExtendingLayoutsConstants::ADM)

@section(YieldingConstants::ADM_PG_TTL)
    {{ __('Create Warehouse') }}
@endsection

@section(YieldingConstants::ADM_BDC)
    <li class="{{ VC::BCI }}">
        <a href="{{ Route::has(VW::WRH . '.index') ? route(VW::WRH . '.index') : '#' }}">
            {{ __('Warehouses') }}
        </a>
    </li>
    <li class="{{ VC::BCI_ACT }}" aria-current="page">{{ __('Create') }}</li>
@endsection

@push(StacksConstants::ADM_SCR_PG)
@endpush

@section(YieldingConstants::ADM_CTT)
    <div class="row">
        <div class="{{ VC::CM8 }}">
            <div class="card">
                <div class="{{ VC::CD_HD }}">
                    <h5>{{ __('Create Warehouse') }}</h5>
                </div>
                <div class="{{ VC::CD_BD }}">
                    {{ Form::open(['url' => $actionUrl, 'method' => 'POST', 'id' => $formId, 'class' => 'w-100']) }}
                        <div class="row">
                            <div class="{{ VC::CM12 }} {{ VC::FM_G }}">
                                {{ Form::label('name', __('Name'), ['class' => 'form-label']) }}
                                {{ Form::text('name', null, ['class' => 'form-control', 'required' => true, 'maxlength' => 100]) }}
                            </div>
                        </div>
                        <div class="{{ VC::MT3 }}">
                            {{ Form::submit(__('Save'), ['class' => 'btn btn-primary']) }}
                        </div>
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>
@endsection
