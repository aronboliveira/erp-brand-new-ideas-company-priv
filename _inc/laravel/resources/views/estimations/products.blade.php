@php
    try {
$user = Auth::user();
        $lang = Utility::fetchUserLang(user: $user);

        $productsIsList = (is_array($products ?? null) && count($products ?? []) > 0) || (($products ?? null) instanceof Collection && $products->isNotEmpty());
        $productOptions = $productsIsList ? (is_array($products) ? $products : $products->toArray()) : [__('No products available')];
    } catch (\Throwable $e) {
        \Log::error('estimations/products — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    }
@endphp

<div class="{{ VC::CD }} bg-none card-box">
    @if(!empty($estimation) && isset($estimation->id))
        @if(!empty($product) && isset($product->id))
            @php
                $formId     ??= 'est-prod-update-form';
                try {
                    $base       = VW::EST . '.products.update';
                    $kebab      = Str::kebab($base);
                    $resolved   = Route::has($base) ? $base : (Route::has($kebab) ? $kebab : null);
                    $actionUrl  = ($resolved && isset($estimation?->id, $product?->id)) ? route($resolved, [$estimation->id, $product->id]) : '#';
                    $guardMsg   = Utility::fetchLinkMessage($lang, VW::EST, 'update_product_route_unavailable') ?? 'Update product route is unavailable. Please contact technical support or your domain administrator.';
                } catch (\Throwable $e) {
                    \Log::error('estimations/products — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                }
@endphp
            {{ Form::model($product, [
                'url'                => $actionUrl,
                'method'             => 'PUT',
                'id'                 => $formId,
                'data-url'           => $actionUrl,
                'data-guard-msg'     => $guardMsg,
                'data-sv-localized'  => 'true',
            ]) }}
        @else
            @php
                $formId     ??= 'est-prod-store-form';
                try {
                    $base       = VW::EST . '.products.store';
                    $kebab      = Str::kebab($base);
                    $resolved   = Route::has($base) ? $base : (Route::has($kebab) ? $kebab : null);
                    $actionUrl  = ($resolved && isset($estimation?->id)) ? route($resolved, $estimation->id) : '#';
                    $guardMsg   = Utility::fetchLinkMessage($lang, VW::EST, 'store_product_route_unavailable') ?? 'Store product route is unavailable. Please contact technical support or your domain administrator.';
                } catch (\Throwable $e) {
                    \Log::error('estimations/products — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                }
@endphp
            {{ Form::model($estimation, [
                'url'                => $actionUrl,
                'method'             => 'POST',
                'id'                 => $formId,
                'data-url'           => $actionUrl,
                'data-guard-msg'     => $guardMsg,
                'data-sv-localized'  => 'true',
            ]) }}
        @endif
            @csrf
            <div class="{{ VC::RW }}">
                <div class="{{ VC::FM_G }} {{ VC::CLMS6 }}">
                    {{ Form::label('product_id', __('Product'), ['class' => VC::FM_LB]) }}
                    {{ Form::select(
                        'product_id',
                        $productOptions,
                        null,
                        array_merge(['class' => VC::FM_CT.' select2', 'required' => 'required', 'placeholder' => __('Select Product')], $productsIsList ? [] : ['disabled' => 'disabled'])
                    ) }}
                    @error('product_id')
                        <div class="{{ VC::TXT_MT }} {{ VC::TXS }}">{{ $message }}</div>
                    @enderror
                    @unless($productsIsList)
                        <div class="{{ VC::TXT_MT }} {{ VC::TXS }}">{{ __('No products available.') }}</div>
                    @endunless
                </div>

                <div class="{{ VC::FM_G }} {{ VC::CLMS6 }}">
                    {{ Form::label('quantity', __('Quantity'), ['class' => VC::FM_LB]) }}
                    {{ Form::number('quantity', isset($product) ? null : 1, ['class' => VC::FM_CT, 'required' => 'required', 'min' => '1', 'placeholder' => __('Enter quantity')]) }}
                    @error('quantity')
                        <div class="{{ VC::TXT_MT }} {{ VC::TXS }}">{{ $message }}</div>
                    @enderror
                </div>

                <div class="{{ VC::FM_G }} {{ VC::C12 }}">
                    {{ Form::label('description', __('Description'), ['class' => VC::FM_LB]) }}
                    {{ Form::textarea('description', null, ['class' => VC::FM_CT, 'rows' => 3, 'placeholder' => __('Enter description...')]) }}
                    @error('description')
                        <div class="{{ VC::TXT_MT }} {{ VC::TXS }}">{{ $message }}</div>
                    @enderror
                </div>

                <div class="{{ VC::C12 }} text-end">
                    @if(isset($product))
                        <input type="submit" value="{{ __('Update') }}" class="{{ VC::BT_PRM }}">
                    @else
                        <input type="submit" value="{{ __('Add') }}" class="{{ VC::BT_PRM }}">
                    @endif
                    <input type="button" value="{{ __('Cancel') }}" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">
                </div>
            </div>
            <script defer src="{{ asset('assets/js/routes/estimations/products.js') }}"></script>
        {{ Form::close() }}
    @else
        <div class="{{ VC::ALERT }} {{ VC::ALERT_DANGER }} mb-4" role="alert">
            {{ __('Estimation information could not be found.') }}
        </div>
    @endif
</div>
