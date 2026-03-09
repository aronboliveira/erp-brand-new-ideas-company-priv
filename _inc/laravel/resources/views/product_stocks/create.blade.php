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
    $products ??= collect();
    $formId ??= 'product-stock-store-form';
    $actionUrl ??= '#';
    $guardMsg ??= '';

    try {
        $lang = Utility::fetchUserLang() ?? 'en';
        $base = VW::PRD_STK;
        $baseKebab = Str::kebab($base);
        $routeRes = Route::has($base . '.store') ? $base . '.store' : (Route::has($baseKebab . '.store') ? $baseKebab . '.store' : null);
        $actionUrl = $routeRes ? route($routeRes) : '#';
        $guardMsg = 'Product stock store route is unavailable. Please contact technical support.';
    } catch (\Throwable $e) {
        Log::error('product_stocks/create — ' . get_class($e) . ': ' . $e->getMessage(), [
            'file' => $e->getFile(),
            'line' => $e->getLine(),
        ]);
    }
@endphp

@extends(ExtendingLayoutsConstants::ADM)

@section(YieldingConstants::ADM_PG_TTL)
    {{ __('Create Product Stock') }}
@endsection

@section(YieldingConstants::ADM_BDC)
    <li class="{{ VC::BCI }}">
        <a href="{{ Route::has(VW::PRD_STK . '.index') ? route(VW::PRD_STK . '.index') : '#' }}">
            {{ __('Product Stocks') }}
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
                    <h5>{{ __('Create Product Stock') }}</h5>
                </div>
                <div class="{{ VC::CD_BD }}">
                    {{ Form::open(['url' => $actionUrl, 'method' => 'POST', 'id' => $formId, 'class' => 'w-100']) }}
                        <div class="row">
                            <div class="{{ VC::CM6 }} {{ VC::FM_G }}">
                                {{ Form::label('product_id', __('Product'), ['class' => 'form-label']) }}
                                {{ Form::select('product_id', $products, null, ['class' => 'form-control', 'required' => true]) }}
                            </div>
                            <div class="{{ VC::CM6 }} {{ VC::FM_G }}">
                                {{ Form::label('quantity', __('Quantity'), ['class' => 'form-label']) }}
                                {{ Form::number('quantity', null, ['class' => 'form-control', 'required' => true, 'min' => 0]) }}
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
