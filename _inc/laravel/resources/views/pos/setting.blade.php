@php
    try {
$lang = Utility::fetchUserLang();

        $barcodeBase     = VW::POS . '.barcode.setting';
        $barcodeKebab    = Str::kebab($barcodeBase);
        $barcodeResolved = Route::has($barcodeBase) ? $barcodeBase : (Route::has($barcodeKebab) ? $barcodeKebab : null);
        $actionUrl       = $barcodeResolved ? route($barcodeResolved) : '#';

        $guardMsg = Utility::fetchLinkMessage($lang, VW::POS, 'barcode_setting_route_unavailable')
            ?? __('Barcode settings route is unavailable. Please contact technical support or your domain administrator.');

        $settings      = $settings ?? Utility::settings();
        $barcodeType   = $settings['barcode_type']   ?? 'code128';
        $barcodeFormat = $settings['barcode_format'] ?? 'css';
    } catch (\Throwable $e) {
        \Log::error('pos/setting — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    }
@endphp
<form method="POST"
      action="{{ $actionUrl }}"
      id="pos-barcode-setting-form"
      data-url="{{ $actionUrl }}"
      data-guard-msg="{{ base64_encode($guardMsg) }}"
      data-sv-localized="true">
    @csrf

    <div class="modal-body">
        <div class="row">
            <div class="{{ VC::FM_GCB6 }}">
                {{ Form::label('barcode_type', __('Barcode Type'), ['class' => VC::FM_LB]) }}
                {{ Form::select(
                    'barcode_type',
                    ['code128' => 'Code 128', 'code39' => 'Code 39', 'code93' => 'Code 93'],
                    $barcodeType,
                    ['class' => VC::FM_CT_SL, 'data-toggle' => 'select']
                ) }}
            </div>
            <div class="{{ VC::FM_GCB6 }}">
                {{ Form::label('barcode_format', __('Barcode Format'), ['class' => VC::FM_LB]) }}
                {{ Form::select(
                    'barcode_format',
                    ['css' => 'CSS', 'bmp' => 'BMP'],
                    $barcodeFormat,
                    ['class' => VC::FM_CT_SL, 'data-toggle' => 'select']
                ) }}
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <input type="submit" value="{{ __('Save') }}" class="{{ VC::BT_PRM }}">
    </div>
    <script defer src="{{ asset('assets/js/routes/pos/barcodeSetting.js') }}"></script>
</form>
