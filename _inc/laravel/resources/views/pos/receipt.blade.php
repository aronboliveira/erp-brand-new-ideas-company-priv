@php
    use App\Models\Utility;
    use App\Config\Constants\{DatabaseConstants, SettingsConstants};
    $settings = Utility::settings();
    $colorSettings  = $settings[SettingsConstants::CLR_STG];
@endphp
    <!DOCTYPE html>
<html lang="{{ str_replace('_', '-', is_string(app()->getLocale()) ? app()->getLocale() : DatabaseConstants::DEFAULT_LANG) }}" dir="{{$settings[SettingsConstants::RTL] == 'on'?'rtl':''}}">
    <head>
        <title>{{env('APP_NAME')}} - POS Barcode</title>
        @include('fragments.std', [
            'meta_title' => $meta_title,
            'meta_desc' => $meta_desc,
            'meta_vp' => ''
        ])
        @include('fragments.stylesheets', ['settings' => $colorSettings])
        @if (isset($settings[SettingsConstants::RTL] ) && $settings[SettingsConstants::RTL] == 'on')
            <link rel="stylesheet" href="{{ asset('assets/css/style-rtl.css')}}" id="style-rtl-link">
        @endif
    </head>
    <body>
        <div id="bot" class="mt-5">
            <div class="row">
                @foreach($productServices as $product)
                    @for($i=1;$i<=$quantity;$i++)
                        <div class="col-auto mb-2">
                            <small class="">{{$product->name}}</small>
                            <div data-id="{{$product->id}}" class="product_barcode product_barcode_hight_de product_barcode_{{$product->id}} mt-2" data-skucode="{{ $product->sku }}"></div>
                        </div>
                    @endfor
                @endforeach
            </div>
        </div>
        <script>
            window.print();
            window.onafterprint = back;

            function back() {
                window.close();
                window.history.back();
            }
        </script>
        <script src="{{ asset('js/jquery.min.js') }}"></script>
        <script src="{{ asset('public/js/jquery-barcode.min.js') }}"></script>
        <script src="{{ asset('public/js/jquery-barcode.js') }}"></script>
        <script>
            $(document).ready(function() {
                $(".product_barcode").each(function() {
                    var id = $(this).data("id");
                    var sku = $(this).data('skucode');
                    generateBarcode(sku, id);
                });
            });
            function generateBarcode(val, id) {
                var value = val;
                var btype = '{{ $barcode['barcodeType'] }}';
                var renderer = '{{ $barcode['barcodeFormat'] }}';
                var settings = {
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
                $('.product_barcode_' + id).html("").show().barcode(value, btype, settings);

            }
        </script>
    </body>
</html>
