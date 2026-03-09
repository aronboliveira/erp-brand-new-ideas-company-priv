@php
    try {
$lang    = Utility::fetchUserLang();
        $hasLead = !empty($lead ?? null) && data_get($lead, 'id');

        $updateBase     = VW::LD.'.products.update';
        $updateKebab    = Str::kebab($updateBase);
        $updateResolved = Route::has($updateBase) ? $updateBase : (Route::has($updateKebab) ? $updateKebab : null);
        $updateUrl      = ($updateResolved && $hasLead) ? route($updateResolved, $lead->id) : '#';
        $updateGuard    = Utility::fetchLinkMessage($lang, VW::LD, 'update_route_unavailable') ?? __('Update route is unavailable. Please contact technical support or your domain administrator.');

        $productsOptions = Utility::isFilled($products) ? $products : ['' => __('No product available' ?? [])];
    } catch (\Throwable $e) {
        \Log::error('leads/products — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    }
@endphp

@if(!$hasLead)
    <div class="{{ VC::ALT_WRN_MB0 }}" role="alert">{{ __('The requested lead was not found or is unavailable.') }}</div>
@else
    {{ Form::model($lead, [
        'url'               => $updateUrl,
        'method'            => 'PUT',
        'id'                => 'lead-products-update-form',
        'data-url'          => $updateUrl,
        'data-guard-msg'    => $updateGuard,
        'data-sv-localized' => 'true'
    ]) }}
        <div class="modal-body">
            <div class="{{ VC::RW }}">
                <div class="{{ VC::FM_GCB12 }}">
                    {{ Form::label('products', __('Products'), ['class' => VC::FM_LB]) }}
                    {{ Form::select('products[]', $productsOptions, null, [
                        'class'     => VC::FM_CT_SL.' select2',
                        'id'        => 'choices-multiple3',
                        'multiple'  => 'multiple',
                        'required'  => 'required'
                    ]) }}
                </div>
            </div>
        </div>

        <div class="modal-footer">
            <input type="button" value="{{ __('Cancel') }}" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">
            <input type="submit" value="{{ __('Save') }}" class="{{ VC::BT_PRM }}" id="lead-products-update-submit">
        </div>

        <script async src="{{ asset('assets/js/routes/leads/lang/products.js') }}"></script>
        <script defer src="{{ asset('assets/js/routes/leads/products.js') }}"></script>
    {{ Form::close() }}
@endif
