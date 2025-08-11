@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        StacksConstants,
        YieldingConstants
    };
    use Illuminate\Support\Facades\Route;
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@section(YieldingConstants::ADM_PG_TTL)
    {{__('POS Barcode Print')}}
@endsection
@section(YieldingConstants::ADM_BDC)
    <li class="breadcrumb-item">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
        {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="breadcrumb-item"><a href="{{route('pos.barcode')}}">{{__('POS Product Barcode')}}</a></li>
    <li class="breadcrumb-item">{{__('POS Barcode Print')}}</li>
@endsection
@push(StacksConstants::ADM_CSS)
    <link rel="stylesheet" href="{{ asset('css/datatable/buttons.dataTables.min.css') }}">
@endpush
@push(StacksConstants::ADM_SCR_PG)
    <script type="text/javascript" src="{{ asset('js/html2pdf.bundle.min.js') }}"></script>
    <script>

        $(document).ready(function () {
            var b_id = $('#warehouse_id').val();
            getProduct(b_id);
        });
        $(document).on('change', 'select[name=warehouse_id]', function () {

            var warehouse_id = $(this).val();
            getProduct(warehouse_id);
        });

        function getProduct(bid) {

            $.ajax({
                url: '{{route('pos.getproduct')}}',
                type: 'POST',
                data: {
                    "warehouse_id": bid, "_token": "{{ csrf_token() }}",
                },

                success: function (data) {
                    // console.log(data);
                    $('#product_id').empty();

                    $("#product_div").html('');
                    $('#product_div').append('<label for="product_id" class="form-label">{{__('Product')}}</label>');
                    $('#product_div').append('<select class="form-label" id="product_id" name="product_id[]"  multiple></select>');
                    $('#product_id').append('<option value="">{{__('Select Product')}}</option>');

                    $.each(data, function (key, value) {
                        console.log(key, value);
                        $('#product_id').append('<option value="' + key + '">' + value + '</option>');
                    });
                    var multipleCancelButton = new Choices('#product_id', {
                        removeItemButton: true,
                    });

                }

            });
        }

    </script>
    <script>
        function copyToClipboard(element) {
            var copyText = element.id;
            navigator.clipboard.writeText(copyText);
            // document.addEventListener('copy', function (e) {
            //     e.clipboardData.setData('text/plain', copyText);
            //     e.preventDefault();
            // }, true);
            // document.execCommand('copy');
            show_toastr('success', 'Url copied to clipboard', 'success');
        }
    </script>
    <script>
        var filename = $('#filesname').val();

        function saveAsPDF() {
            var element = document.getElementById('printableArea');
            var opt = {
                margin: 0.3,
                filename: filename,
                image: {type: 'jpeg', quality: 1},
                html2canvas: {scale: 4, dpi: 72, letterRendering: true},
                jsPDF: {unit: 'in', format: 'A2'}
            };
            html2pdf().set(opt).from(element).save();

        }
    </script>
@endpush
@section(YieldingConstants::ADM_ACT_BTN)
    <div class="float-end">
        <a href="{{ route('pos.barcode') }}" class="btn btn-sm btn-primary" data-bs-toggle="tooltip" title="{{__('Back')}}">
            <i class="ti ti-arrow-left text-white"></i>
        </a>
    </div>
@endsection
@section(YieldingConstants::ADM_CTT)
    <div class="row mt-3">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    {{Collective\Html\FormFacade::open(array('route'=>'pos.receipt','method'=>'post'))}}
                        <div class="row" id="printableArea">
                            <div class="col-md-4">
                                <div class="form-group">
                                    {{Collective\Html\FormFacade::label('warehouse_id',__('Warehouse'),['class'=>'form-label'])}}
                                    {{ Collective\Html\FormFacade::select('warehouse_id', $warehouses,'', array('class' => 'form-control select','id'=>'warehouse_id','required'=>'required')) }}
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group" id="product_div">
                                    {{Collective\Html\FormFacade::label('product_id',__('Product'),['class'=>'form-label'])}}
                                    <select class="form-control select" name="product_id[]" id="product_id" required >
                                    </select>
                                </div>
                            </div>
                            <div class="form-group col-md-4">
                                {{ Collective\Html\FormFacade::label('quantity', __('Quantity'),['class'=>'form-label']) }}<span class="text-danger">*</span>
                                {{ Collective\Html\FormFacade::text('quantity',null, array('class' => 'form-control','required'=>'required')) }}
                            </div>
                        </div>
                        <div class="col-md-6 pt-4">
                            <button class="btn btn-sm btn-primary btn-icon" type="submit">{{__('Print')}}</button>
                        </div>
                    {{Collective\Html\FormFacade::close()}}
                </div>
            </div>
        </div>
    </div>
@endsection

