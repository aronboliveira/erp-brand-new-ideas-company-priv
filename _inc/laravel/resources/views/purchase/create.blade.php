
@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        SettingsConstants,
        StacksConstants,
        ViewsConstants,
        YieldingConstants,
    };
    use Illuminate\Support\Facades\Route;
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@section(YieldingConstants::ADM_PG_TTL)
    {{__('Purchase Create')}}
@endsection
@section(YieldingConstants::ADM_BDC)
    <li class="breadcrumb-item">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
        {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="breadcrumb-item"><a href="{{route(ViewsConstants::PRC.'.index')}}">{{__('Purchase')}}</a></li>
    <li class="breadcrumb-item">{{__('Purchase Create')}}</li>
@endsection
@push(StacksConstants::ADM_SCR_PG)
    <script src="{{asset('js/jquery-ui.min.js')}}"></script>
    <script src="{{asset('js/jquery.repeater.min.js')}}"></script>
    <script>
        var selector = "body";
        if ($(selector + " .repeater").length) {
            var $dragAndDrop = $("body .repeater tbody").sortable({
                handle: '.sort-handler'
            });
            var $repeater = $(selector + ' .repeater').repeater({
                initEmpty: false,
                defaultValues: {
                    'status': 1
                },
                show: function () {
                    $(this).slideDown();
                    var file_uploads = $(this).find('input.multi');
                    if (file_uploads.length) {
                        $(this).find('input.multi').MultiFile({
                            max: 3,
                            accept: 'png|jpg|jpeg',
                            max_size: {{ SettingsConstants::MAX_U_SIZE_DEF }}
                        });
                    }
                    $('.select2').select2();
                },
                hide: function (deleteElement) {
                    if (confirm('Are you sure you want to delete this element?')) {
                        $(this).slideUp(deleteElement);
                        $(this).remove();

                        var inputs = $(".amount");
                        var subTotal = 0;
                        for (var i = 0; i < inputs.length; i++) {
                            subTotal = parseFloat(subTotal) + parseFloat($(inputs[i]).html());
                        }
                        $('.subTotal').html(subTotal.toFixed(2));
                        $('.totalAmount').html(subTotal.toFixed(2));
                    }
                },
                ready: function (setIndexes) {
                    $dragAndDrop.on('drop', setIndexes);
                },
                isFirstItemUndeletable: true
            });
            var value = $(selector + " .repeater").attr('data-value');
            if (typeof value != 'undefined' && value.length != 0) {
                value = JSON.parse(value);
                $repeater.setList(value);
            }

        }

        $(document).on('change', '#vendor', function () {
            $('#vendor_detail').removeClass('d-none');
            $('#vendor_detail').addClass('d-block');
            $('#vendor-box').removeClass('d-block');
            $('#vendor-box').addClass('d-none');
            var id = $(this).val();
            var url = $(this).data('url');
            $.ajax({
                url: url,
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': jQuery('#token').val()
                },
                data: {
                    'id': id
                },
                cache: false,
                success: function (data) {
                    if (data != '') {
                        $('#vendor_detail').html(data);
                    } else {
                        $('#vendor-box').removeClass('d-none');
                        $('#vendor-box').addClass('d-block');
                        $('#vendor_detail').removeClass('d-block');
                        $('#vendor_detail').addClass('d-none');
                    }
                },
            });
        });

        $(document).on('click', '#remove', function () {
            $('#vendor-box').removeClass('d-none');
            $('#vendor-box').addClass('d-block');
            $('#vendor_detail').removeClass('d-block');
            $('#vendor_detail').addClass('d-none');
        })


        $(document).on('change', '.item', function () {
            var iteams_id = $(this).val();
            var url = $(this).data('url');
            var el = $(this);
            $.ajax({
                url: url,
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': jQuery('#token').val()
                },
                data: {
                    'product_id': iteams_id
                },
                cache: false,
                success: function (data) {
                    var item = JSON.parse(data);

                    $(el.parent().parent().find('.quantity')).val(1);
                    $(el.parent().parent().find('.price')).val(item.product.purchase_price);
                    $(el.parent().parent().parent().find('.pro_description')).val(item.product.description);
                    console.log(el);
                    var taxes = '';
                    var tax = [];

                    var totalItemTaxRate = 0;
                    if (item.taxes == 0) {
                        taxes += '-';
                    } else {
                        for (var i = 0; i < item.taxes.length; i++) {

                            taxes += '<span class="badge bg-primary mt-1 mr-2">' + item.taxes[i].name + ' ' + '(' + item.taxes[i].rate + '%)' + '</span>';
                            tax.push(item.taxes[i].id);
                            totalItemTaxRate += parseFloat(item.taxes[i].rate);

                        }
                    }
                    var itemTaxPrice = parseFloat((totalItemTaxRate / 100) * (item.product.purchase_price * 1));

                    $(el.parent().parent().find('.itemTaxPrice')).val(itemTaxPrice.toFixed(2));
                    $(el.parent().parent().find('.itemTaxRate')).val(totalItemTaxRate.toFixed(2));
                    $(el.parent().parent().find('.taxes')).html(taxes);
                    $(el.parent().parent().find('.tax')).val(tax);
                    $(el.parent().parent().find('.unit')).html(item.unit);
                    $(el.parent().parent().find('.discount')).val(0);
                    $(el.parent().parent().find('.amount')).html(item.totalAmount);


                    var inputs = $(".amount");
                    var subTotal = 0;
                    for (var i = 0; i < inputs.length; i++) {
                        subTotal = parseFloat(subTotal) + parseFloat($(inputs[i]).html());
                    }
                    $('.subTotal').html(subTotal.toFixed(2));


                    var totalItemPrice = 0;
                    var priceInput = $('.price');
                    for (var j = 0; j < priceInput.length; j++) {
                        totalItemPrice += parseFloat(priceInput[j].value);
                    }

                    var totalItemTaxPrice = 0;
                    var itemTaxPriceInput = $('.itemTaxPrice');
                    for (var j = 0; j < itemTaxPriceInput.length; j++) {
                        totalItemTaxPrice += parseFloat(itemTaxPriceInput[j].value);
                    }

                    $('.totalTax').html(totalItemTaxPrice.toFixed(2));
                    $('.totalAmount').html((parseFloat(subTotal) + parseFloat(totalItemTaxPrice)).toFixed(2));

                },
            });
        });

        $(document).on('keyup', '.quantity', function () {
            var quntityTotalTaxPrice = 0;

            var el = $(this).parent().parent().parent().parent();
            var quantity = $(this).val();
            var price = $(el.find('.price')).val();
            var discount = $(el.find('.discount')).val();
            if(discount.length <= 0)
            {
                discount = 0 ;
            }

            var totalItemPrice = (quantity * price) - discount;
            var amount = (totalItemPrice);


            var amount = (totalItemPrice);


            var totalItemTaxRate = $(el.find('.itemTaxRate')).val();
            var itemTaxPrice = parseFloat((totalItemTaxRate / 100) * (totalItemPrice));
            $(el.find('.itemTaxPrice')).val(itemTaxPrice.toFixed(2));

            $(el.find('.amount')).html(parseFloat(itemTaxPrice)+parseFloat(amount));

            var totalItemTaxPrice = 0;
            var itemTaxPriceInput = $('.itemTaxPrice');
            for (var j = 0; j < itemTaxPriceInput.length; j++) {
                totalItemTaxPrice += parseFloat(itemTaxPriceInput[j].value);
            }


            var totalItemPrice = 0;
            var inputs_quantity = $(".quantity");

            var priceInput = $('.price');
            for (var j = 0; j < priceInput.length; j++) {
                totalItemPrice += (parseFloat(priceInput[j].value) * parseFloat(inputs_quantity[j].value));
            }

            var inputs = $(".amount");

            var subTotal = 0;
            for (var i = 0; i < inputs.length; i++) {
                subTotal = parseFloat(subTotal) + parseFloat($(inputs[i]).html());
            }

            $('.subTotal').html(totalItemPrice.toFixed(2));
            $('.totalTax').html(totalItemTaxPrice.toFixed(2));

            $('.totalAmount').html((parseFloat(subTotal)).toFixed(2));

        })

        $(document).on('keyup change', '.price', function () {
            var el = $(this).parent().parent().parent().parent();
            var price = $(this).val();
            var quantity = $(el.find('.quantity')).val();

            var discount = $(el.find('.discount')).val();
            if(discount.length <= 0)
            {
                discount = 0 ;
            }
            var totalItemPrice = (quantity * price)-discount;

            var amount = (totalItemPrice);


            var totalItemTaxRate = $(el.find('.itemTaxRate')).val();
            var itemTaxPrice = parseFloat((totalItemTaxRate / 100) * (totalItemPrice));
            $(el.find('.itemTaxPrice')).val(itemTaxPrice.toFixed(2));

            $(el.find('.amount')).html(parseFloat(itemTaxPrice)+parseFloat(amount));

            var totalItemTaxPrice = 0;
            var itemTaxPriceInput = $('.itemTaxPrice');
            for (var j = 0; j < itemTaxPriceInput.length; j++) {
                totalItemTaxPrice += parseFloat(itemTaxPriceInput[j].value);
            }


            var totalItemPrice = 0;
            var inputs_quantity = $(".quantity");

            var priceInput = $('.price');
            for (var j = 0; j < priceInput.length; j++) {
                totalItemPrice += (parseFloat(priceInput[j].value) * parseFloat(inputs_quantity[j].value));
            }

            var inputs = $(".amount");

            var subTotal = 0;
            for (var i = 0; i < inputs.length; i++) {
                subTotal = parseFloat(subTotal) + parseFloat($(inputs[i]).html());
            }

            $('.subTotal').html(totalItemPrice.toFixed(2));
            $('.totalTax').html(totalItemTaxPrice.toFixed(2));

            $('.totalAmount').html((parseFloat(subTotal)).toFixed(2));


        })

        $(document).on('keyup change', '.discount', function () {
            var el = $(this).parent().parent().parent();
            var discount = $(this).val();
            if(discount.length <= 0)
            {
                discount = 0 ;
            }

            var price = $(el.find('.price')).val();
            var quantity = $(el.find('.quantity')).val();
            var totalItemPrice = (quantity * price) - discount;


            var amount = (totalItemPrice);


            var totalItemTaxRate = $(el.find('.itemTaxRate')).val();
            var itemTaxPrice = parseFloat((totalItemTaxRate / 100) * (totalItemPrice));
            $(el.find('.itemTaxPrice')).val(itemTaxPrice.toFixed(2));

            $(el.find('.amount')).html(parseFloat(itemTaxPrice)+parseFloat(amount));

            var totalItemTaxPrice = 0;
            var itemTaxPriceInput = $('.itemTaxPrice');
            for (var j = 0; j < itemTaxPriceInput.length; j++) {
                totalItemTaxPrice += parseFloat(itemTaxPriceInput[j].value);
            }


            var totalItemPrice = 0;
            var inputs_quantity = $(".quantity");

            var priceInput = $('.price');
            for (var j = 0; j < priceInput.length; j++) {
                totalItemPrice += (parseFloat(priceInput[j].value) * parseFloat(inputs_quantity[j].value));
            }

            var inputs = $(".amount");

            var subTotal = 0;
            for (var i = 0; i < inputs.length; i++) {
                subTotal = parseFloat(subTotal) + parseFloat($(inputs[i]).html());
            }


            var totalItemDiscountPrice = 0;
            var itemDiscountPriceInput = $('.discount');

            for (var k = 0; k < itemDiscountPriceInput.length; k++) {

                totalItemDiscountPrice += parseFloat(itemDiscountPriceInput[k].value);
            }


            $('.subTotal').html(totalItemPrice.toFixed(2));
            $('.totalTax').html(totalItemTaxPrice.toFixed(2));

            $('.totalAmount').html((parseFloat(subTotal)).toFixed(2));
            $('.totalDiscount').html(totalItemDiscountPrice.toFixed(2));




        })

        var vendorId = '{{$vendorId}}';
        if (vendorId > 0) {
            $('#vendor').val(vendorId).change();
        }

    </script>
    <script>
        $(document).on('click', '[data-repeater-delete]', function () {
            $(".price").change();
            $(".discount").change();
        });
    </script>
@endpush
@section('content')
    @php
        use Collective\Html\FormFacade as Form;
        $mainField = [
            'name'    => 'vendor_id',
            'label'   => __('Vendor'),
            'options' => $vendors,
            'attrs'   => [
                'class'    => 'form-control select',
                'id'       => 'vendor',
                'data-url' => route(ViewsConstants::BIL . '.vendor'),
                'required' => 'required',
            ],
        ];

        // Side-by-side selects
        $sideFields = [
            [
                'name'    => 'warehouse_id',
                'label'   => __('Warehouse'),
                'options' => $warehouse,
                'attrs'   => ['class'=>'form-control select','required'=>'required'],
            ],
            [
                'name'    => 'category_id',
                'label'   => __('Category'),
                'options' => $category,
                'attrs'   => ['class'=>'form-control select','required'=>'required'],
            ],
        ];

        // Date and read-only fields
        $dateFields = [
            [
                'name'  => 'purchase_date',
                'label' => __('Purchase Date'),
                'type'  => 'date',
                'attrs' => ['class'=>'form-control','required'=>'required'],
            ],
            [
                'name'  => 'purchase_number',
                'label' => __('Purchase Number'),
                'type'  => 'text',
                'value' => $purchase_number,
                'attrs' => ['class'=>'form-control','readonly'=>true],
            ],
        ];

        // Table headers
        $tableHeaders = [
            __('Items'),
            __('Quantity'),
            __('Price'),
            __('Discount'),
            __('Tax').' (%)',
            __('Amount').' <br><small class="text-danger font-weight-bold">'.__('after tax & discount').'</small>',
            '',
        ];
    @endphp

    {{ Form::open(['route' => ViewsConstants::PRC . '.store', 'class' => 'w-100']) }}
    <input type="hidden" id="token" value="{{ csrf_token() }}">

    <div class="row mb-4">
        {{-- Vendor selector --}}
        <div class="col-md-6">
            <div id="vendor-box" class="form-group">
                {{ Form::label($mainField['name'], $mainField['label'], ['class'=>'form-label']) }}
                {{ Form::select(
                    $mainField['name'],
                    $mainField['options'],
                    $vendorId,
                    $mainField['attrs']
                ) }}
            </div>
            <div id="vendor_detail" class="d-none"></div>
        </div>

        {{-- Warehouse & Category --}}
        <div class="col-md-6">
            <div class="row">
                @foreach($sideFields as $f)
                    <div class="col-md-6">
                        <div class="form-group">
                            {{ Form::label($f['name'], $f['label'], ['class'=>'form-label']) }}
                            {{ Form::select($f['name'], $f['options'], null, $f['attrs']) }}
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="row">
                @foreach($dateFields as $f)
                    <div class="col-md-6">
                        <div class="form-group">
                            {{ Form::label($f['name'], $f['label'], ['class'=>'form-label']) }}
                            @if($f['type'] === 'date')
                                {{ Form::date($f['name'], null, $f['attrs']) }}
                            @else
                                <input type="text" name="{{ $f['name'] }}" value="{{ $f['value'] }}"
                                       @foreach($f['attrs'] as $k=>$v) {{ $k }}="{{ $v }}" @endforeach>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <h5 class="mb-3">{{ __('Product & Services') }}</h5>
    <div class="card repeater mb-4">
        <div class="item-section py-2">
            <div class="d-flex justify-content-end">
                <button type="button" data-repeater-create class="btn btn-primary">
                    <i class="ti ti-plus"></i> {{ __('Add item') }}
                </button>
            </div>
        </div>
        <div class="card-body table-border-style">
            <div class="table-responsive">
                <table class="table mb-0" data-repeater-list="items" id="sortable-table">
                    <thead>
                        <tr>
                            @foreach($tableHeaders as $th)
                                <th>{!! $th !!}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="ui-sortable" data-repeater-item>
                        <tr>
                            <td width="25%">
                                {{ Form::select(
                                    'item',
                                    $product_services,
                                    '',
                                    ['class'=>'form-control select2 item','data-url'=>route(ViewsConstants::PRC.'.product'),'required'=>'required']
                                ) }}
                            </td>
                            <td>
                                {{ Form::text('quantity','', ['class'=>'form-control quantity','placeholder'=>__('Qty'),'required'=>'required']) }}
                                <span class="unit input-group-text bg-transparent"></span>
                            </td>
                            <td>
                                {{ Form::text('price','', ['class'=>'form-control price','placeholder'=>__('Price'),'required'=>'required']) }}
                                <span class="input-group-text bg-transparent">{{ Auth::user()->currencySymbol() }}</span>
                            </td>
                            <td>
                                {{ Form::text('discount','', ['class'=>'form-control discount','placeholder'=>__('Discount')]) }}
                                <span class="input-group-text bg-transparent">{{ Auth::user()->currencySymbol() }}</span>
                            </td>
                            <td>
                                <div class="taxes"></div>
                                {{ Form::hidden('tax','', ['class'=>'tax']) }}
                                {{ Form::hidden('itemTaxPrice','', ['class'=>'itemTaxPrice']) }}
                                {{ Form::hidden('itemTaxRate','', ['class'=>'itemTaxRate']) }}
                            </td>
                            <td class="text-end amount">0.00</td>
                            <td>
                                <button type="button" class="ti ti-trash text-white bg-danger repeater-action-btn ms-2" data-repeater-delete></button>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2">
                                {{ Form::textarea('description', null, ['class'=>'form-control pro_description','rows'=>2,'placeholder'=>__('Description')]) }}
                            </td>
                            <td colspan="5"></td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="4"></td>
                            <td><strong>{{ __('Sub Total') }}</strong></td>
                            <td class="text-end subTotal">0.00</td>
                            <td></td>
                        </tr>
                        <tr>
                            <td colspan="4"></td>
                            <td><strong>{{ __('Discount') }}</strong></td>
                            <td class="text-end totalDiscount">0.00</td>
                            <td></td>
                        </tr>
                        <tr>
                            <td colspan="4"></td>
                            <td><strong>{{ __('Tax') }}</strong></td>
                            <td class="text-end totalTax">0.00</td>
                            <td></td>
                        </tr>
                        <tr>
                            <td colspan="4"></td>
                            <td class="blue-text"><strong>{{ __('Total Amount') }}</strong></td>
                            <td class="blue-text text-end totalAmount"></td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-end">
        <button type="button" class="btn btn-light me-2" onclick="location.href='{{ route(ViewsConstants::PRC . '.index') }}'">
            {{ __('Cancel') }}
        </button>
        <button type="submit" class="btn btn-primary">{{ __('Create') }}</button>
    </div>

    {{ Form::close() }}
@endsection
