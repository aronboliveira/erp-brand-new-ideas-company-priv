@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        PermissionsConstants,
        StacksConstants,
        YieldingConstants
    };
    use Illuminate\Support\Facades\Route;
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@section(YieldingConstants::ADM_PG_TTL)
    {{__('POS Product Barcode')}}
@endsection

@section(YieldingConstants::ADM_BDC)
    <li class="breadcrumb-item">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
        {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="breadcrumb-item">{{__('POS Product Barcode')}}</li>
@endsection

@push(StacksConstants::ADM_CSS)
    <link rel="stylesheet" href="{{ asset('css/datatable/buttons.dataTables.min.css') }}">
@endpush

@section(YieldingConstants::ADM_ACT_BTN)
    <div class="float-end">
        @can(PermissionsConstants::CR_BC)
            <a href="{{ route('pos.print') }}" class="btn btn-sm btn-primary" data-bs-toggle="tooltip" title="{{__('Print Barcode')}}">
                <i class="ti ti-scan text-white"></i>
            </a>
            <a data-url="{{ route('pos.setting') }}" data-ajax-popup="true" data-bs-toggle="tooltip" data-title="{{__('Barcode Setting')}}" title="{{__('Barcode Setting')}}" class="btn btn-sm btn-primary">
                <i class="ti ti-settings text-white"></i>
            </a>
        @endcan

    </div>
@endsection

@section(YieldingConstants::ADM_CTT)
    <div class="row mt-3">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        <table class="table datatable-barcode" >
                            <thead>
                                <tr>
                                    <th>{{__('Product')}}</th>
                                    <th>{{ __('SKU') }}</th>
                                    <th>{{ __('Barcode') }}</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse ($productServices as $productService)
                                    <tr>
                                        <td>{{$productService->name}}</td>
                                        <td>{{$productService->sku}}</td>
                                        <td>
                                            <div id="{{ $productService->id }}" class="product_barcode product_barcode_hight_de" data-skucode="{{ $productService->sku }}"></div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-dark"><p>{{__('No Data Found')}}</p></td>
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

@push(StacksConstants::ADM_SCR_PG)
{{--    <script src="{{ asset('public/js/jquery-barcode.min.js') }}"></script>--}}
    <script src="{{ asset('public/js/jquery-barcode.js') }}"></script>
    <script>
        $(document).ready(function() {
            $(".product_barcode").each(function() {
                var id = $(this).attr("id");
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
            $('#' + id).html("").show().barcode(value, btype, settings);

        }

        setTimeout(myGreeting, 1000);
        function myGreeting() {
            if ($(".datatable-barcode").length > 0) {
                const dataTable =  new simpleDatatables.DataTable(".datatable-barcode");
            }
        }
        // });
    </script>

@endpush
