@php
    try {
$lang = Utility::fetchUserLang();

        $fromRows = (($from_warehouses ?? null) instanceof Collection || is_array($from_warehouses ?? null)) ? $from_warehouses : [];
        $toRows   = (($to_warehouses ?? null) instanceof Collection || is_array($to_warehouses ?? null)) ? $to_warehouses : [];
        $toOptions = [];
        foreach ($toRows as $wh) {
            $id = (string) (data_get($wh, 'id') ?? '');
            $nm = (string) (data_get($wh, 'name') ?? '');
            if ($id !== '') $toOptions[$id] = $nm !== '' ? $nm : __('No warehouse name available');
        }

        $storeBase  = VW::WRH_TRF;
        $storeKebab = Str::kebab($storeBase);
        $storeName  = Route::has($storeBase) ? $storeBase : (Route::has($storeKebab) ? $storeKebab : null);
        $storeUrl   = $storeName ? route($storeName) : '#';
        $storeGuard = Utility::fetchLinkMessage($lang, VW::WRH_TRF, 'store_warehouse_transfer_route_unavailable')
            ?? 'Store warehouse transfer route is unavailable. Please contact technical support or your domain administrator.';
        $formId = 'warehouse-transfer-store-form';
    } catch (\Throwable $e) {
        \Log::error('warehouse_transfers/create — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    }
@endphp

{!! Form::open([
    'url'                  => $storeUrl,
    'method'               => 'post',
    'id'                   => $formId,
    'data-resolved-action' => $storeUrl,
    'data-guard-msg'       => $storeGuard,
    'data-sv-localized'    => 'true',
]) !!}
    <div class="modal-body">
        <div class="row">
            <div class="{{ VC::FM_GCB6 }}">
                {{ Form::label('from_warehouse', __('From Warehouse'), ['class' => 'form-label']) }}<span class="{{ VC::TX_DNG }}">*</span>
                <select class="{{ VC::FM_CT }} select" name="from_warehouse" id="warehouse_id" placeholder="{{ __('Select Warehouse') }}" required="required">
                    <option value="">{{ __('Select Warehouse') }}</option>
                    @foreach ($fromRows as $warehouse)
                        @php
                            $wid = (string) (data_get($warehouse, 'id') ?? '');
                            $wnm = (string) (data_get($warehouse, 'name') ?? '');
@endphp
                        <option value="{{ $wid }}">{{ $wnm !== '' ? $wnm : __('No warehouse name available') }}</option>
                    @endforeach
                </select>
            </div>

            <div class="{{ VC::FM_GCB6 }}">
                {{ Form::label('to_warehouse', __('To Warehouse'), ['class' => 'form-label']) }}<span class="{{ VC::TX_DNG }}">*</span>
                {{ Form::select('to_warehouse', $toOptions, null, ['class' => VC::FM_CT . ' select', 'required' => 'required', 'placeholder' => __('Select Warehouse')]) }}
            </div>

            <div class="{{ VC::FM_GCB6 }}" id="product_div">
                {{ Form::label('product', __('Product'), ['class' => 'form-label']) }}
                <select class="{{ VC::FM_CT }} select" name="product_id" id="product_id" placeholder="{{ __('Select Product') }}">
                </select>
            </div>

            <div class="{{ VC::FM_GCB6 }}" id="qty_div">
                {{ Form::label('quantity', __('Quantity'), ['class' => 'form-label']) }}<span class="{{ VC::TX_DNG }}">*</span>
                {{ Form::number('quantity', null, ['class' => VC::FM_CT, 'id' => 'quantity', 'required' => 'required', 'min' => '0', 'step' => '1']) }}
            </div>

            <div class="{{ VC::FM_G }} {{ VC::CL6 }}">
                {{ Form::label('date', __('Date')) }}
                {{ Form::date('date', null, ['class' => VC::FM_CT . ' datepicker w-100 mt-2']) }}
            </div>
        </div>
    </div>

    <div class="modal-footer">
        <input type="button" value="{{ __('Cancel') }}" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">
        <input type="submit" value="{{ __('Create') }}" class="{{ VC::BT_PRM }}">
    </div>

    <script defer src="{{ asset('assets/js/routes/warehouses/transfers/store.js') }}"></script>
{!! Form::close() !!}
