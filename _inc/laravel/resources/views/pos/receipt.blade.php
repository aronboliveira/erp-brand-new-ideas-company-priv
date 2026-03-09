@php
    try {
$settings = Utility::settings();
        $colorSettings = $settings[SettingsConstants::CLR_STG] ?? [];
        $lang = Utility::fetchUserLang();
        $rtl = (($settings[SettingsConstants::RTL] ?? '') === 'on') ? 'rtl' : '';
        $qty = (is_numeric($quantity ?? null) && (int)$quantity > 0) ? (int)$quantity : 1;
    } catch (\Throwable $e) {
        \Log::error('pos/receipt — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    }
@endphp
<!DOCTYPE html>
<html lang="{{ $lang ? (str_replace('_', '-', is_string(app()->getLocale()) ? app()->getLocale() : DatabaseConstants::DEFAULT_LANG)) : DatabaseConstants::DEFAULT_LANG }}" dir="{{ $rtl }}">
    <head>
        <title>{{ env('APP_NAME') }} - POS Barcode</title>
        @include('fragments.std', [
            'meta_title' => $meta_title ?? '',
            'meta_desc' => $meta_desc ?? '',
            'meta_vp' => ''
        ])
        @include('fragments.stylesheets', ['settings' => $colorSettings])
        @if(($settings[SettingsConstants::RTL] ?? '') === 'on')
            <link rel="stylesheet" href="{{ asset('assets/css/style-rtl.css') }}" id="style-rtl-link">
        @endif
    </head>
    <body>
        <div id="bot" class="mt-5">
            <div class="row">
                @if( (is_array($productServices ?? null) && count($productServices)) || (($productServices ?? null) instanceof Collection && $productServices->isNotEmpty()) )
                    @foreach($productServices as $product)
                        @for($i = 1; $i <= $qty; $i++)
                            <div class="{{ VC::C_AT }} {{ VC::MB2 }}">
                                <small>{{ data_get($product, 'name', 'Unavailable') }}</small>
                                <div
                                    data-id="{{ data_get($product, 'id', '0') }}"
                                    class="product_barcode product_barcode_hight_de product_barcode_{{ data_get($product, 'id', '0') }} {{ VC::MT2 }}"
                                    data-skucode="{{ data_get($product, 'sku', 'Unavailable') }}">
                                </div>
                            </div>
                        @endfor
                    @endforeach
                @else
                    <div class="{{ VC::C12 }} {{ VC::TXCT_DK }}">
                        <p>No product barcodes available.</p>
                    </div>
                @endif
            </div>
        </div>
        <script src="{{ asset('js/jquery.min.js') }}"></script>
        <script src="{{ asset('public/js/jquery-barcode.min.js') }}"></script>
        <script src="{{ asset('public/js/jquery-barcode.js') }}"></script>
        <script async src="{{ asset('assets/js/routes/pos/lang/receipt.js') }}"></script>
        <script defer>
            const generateBarcode = (value, id) => {
                const btype = '{{ $barcode['barcodeType'] }}';
                const renderer = '{{ $barcode['barcodeFormat'] }}';
                const settings = {
                output: renderer,
                bgColor: '#FFFFFF',
                color: '#000000',
                barWidth: '1',
                barHeight: '50',
                moduleSize: '5',
                posX: '10',
                posY: '20',
                addQuietZone: '1'
                };
                $(`.product_barcode_${id}`).html("").show().barcode(value, btype, settings);
            };
            document.addEventListener("DOMContentLoaded", () => {
                if (typeof $ !== 'function') {
                    alert(`Failed to load necessary functions.`);
                    return;
                }
                $(".product_barcode").each((_, el) => {
                const { id, skucode } = el.dataset;
                if (id && skucode) generateBarcode(skucode, id);
                });
                const back = () => {
                    window.close();
                    window.history.back();
                };
                window.onafterprint = back;
                window.print();
            });
        </script>
    </body>
</html>
