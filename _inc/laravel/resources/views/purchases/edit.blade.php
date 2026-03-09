@php
	try {
try {
				} catch (\\Error $e) {
			Log::error('Error in purchases/edit.blade.php main @php block', [
				'exception_class' => get_class($e),
				'message' => $e->getMessage(),
				'file' => $e->getFile(),
				'line' => $e->getLine(),
			]);
		} catch (\\Exception $e) {
			Log::error('Exception in purchases/edit.blade.php main @php block', [
				'exception_class' => get_class($e),
				'message' => $e->getMessage(),
				'file' => $e->getFile(),
				'line' => $e->getLine(),
			]);
		} catch (\\Throwable $e) {
			Log::error('Throwable in purchases/edit.blade.php main @php block', [
				'exception_class' => get_class($e),
				'message' => $e->getMessage(),
				'file' => $e->getFile(),
				'line' => $e->getLine(),
			]);
		}
	} catch (\Throwable $e) {
		\Log::error('purchases/edit — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
	}
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@section(YieldingConstants::ADM_PG_TTL)
    {{__('Purchase Edit')}}
@endsection
@section(YieldingConstants::ADM_BDC)
    <li class="{{ VC::BCI }}">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
        {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="{{ VC::BCI }}"><a href="{{route(ViewsConstants::PRC.'.index')}}">{{__('Purchase')}}</a></li>
    <li class="{{ VC::BCI }}">{{__('Purchase Edit')}}</li>
@endsection
@push(StacksConstants::ADM_SCR_PG)

    <script src="{{asset('js/jquery-ui.min.js')}}"></script>
    <script defer src="{{asset('js/jquery.repeater.min.js')}}"></script>
    <script>
        var selector = "body";
        if ($(selector + " .repeater").length) {
            var $dragAndDrop = $("body .repeater tbody").sortable({
                handle: '.sort-handler'
            });
            var $repeater = $(selector + ' .repeater').repeater({
                initEmpty: true,
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
                    if($('.select2').length) {
                        $('.select2').select2();
                    }
                },
                hide: function (deleteElement) {

                    $(this).slideUp(deleteElement);
                    $(this).remove();
                    var inputs = $(".amount");
                    var subTotal = 0;
                    for (var i = 0; i < inputs.length; i++) {
                        subTotal = parseFloat(subTotal) + parseFloat($(inputs[i]).html());
                    }
                    $('.subTotal').html(subTotal.toFixed(2));
                    $('.totalAmount').html(subTotal.toFixed(2));

                },
                ready: function (setIndexes) {
                    $dragAndDrop.on('drop', setIndexes);
                },
                isFirstItemUndeletable: true
            });
            var value = $(selector + " .repeater").attr('data-value');
            console.log(value);

            if (typeof value != 'undefined' && value.length != 0) {
                value = JSON.parse(value);
                $repeater.setList(value);
                for (var i = 0; i < value.length; i++) {
                    var tr = $('#sortable-table .id[value="' + value[i].id + '"]').parent();
                    tr.find('.item').val(value[i].product_id);
                    changeItem(tr.find('.item'));
                }
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
        });

        var purchase_id = '{{$purchase->id}}';

        function changeItem(element) {
            var items_id = element.val();

            var url = element.data('url');
            var el = element;
            $.ajax({
                url: url,
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': jQuery('#token').val()
                },
                data: {
                    'product_id': items_id
                },
                cache: false,
                success: function (data) {
                    var item = JSON.parse(data);

                    $.ajax({
                        url: '{{route(ViewsConstants::PRC.'.items')}}',
                        type: 'GET',
                        headers: {
                            'X-CSRF-TOKEN': jQuery('#token').val()
                        },
                        data: {
                            'purchase_id': purchase_id,
                            'product_id': items_id,
                        },
                        cache: false,
                        success: function (data) {
                            var purchaseItems = JSON.parse(data);

                            if (purchaseItems != null) {
                                var amount = (purchaseItems.price * purchaseItems.quantity);
                                $(el.parent().parent().parent().find('.quantity')).val(purchaseItems.quantity);
                                $(el.parent().parent().parent().find('.price')).val(purchaseItems.price);
                                $(el.parent().parent().parent().find('.discount')).val(purchaseItems.discount);
                                $(el.parent().parent().parent().parent().find('.pro_description')).val(purchaseItems.description);

                                // $('.pro_description').text(purchaseItems.description);
                            } else {

                                $(el.parent().parent().parent().find('.quantity')).val(1);
                                $(el.parent().parent().parent().find('.price')).val(item.product.purchase_price);
                                $(el.parent().parent().parent().find('.discount')).val(0);
                                $(el.parent().parent().parent().find('.pro_description')).val(item.product.purchase_price);
                                // $('.pro_description').text(item.product.purchase_price);
                                $(el.parent().parent().parent().parent().find('.pro_description')).val(item.product.description);

                            }

                            var taxes = '';
                            var tax = [];

                            var totalItemTaxRate = 0;
                            for (var i = 0; i < item.taxes.length; i++) {

                                taxes += '<span class="badge {{ VC::BG_P }} p-2 {{ VC::PX3 }} rounded {{ VC::MT1 }} mr-1">' + item.taxes[i].name + ' ' + '(' + item.taxes[i].rate + '%)' + '</span>';
                                tax.push(item.taxes[i].id);
                                totalItemTaxRate += parseFloat(item.taxes[i].rate);

                            }

                            var discount=$(el.parent().parent().parent().find('.discount')).val();

                            if (purchaseItems != null) {
                                var itemTaxPrice = parseFloat((totalItemTaxRate / 100)) * parseFloat((purchaseItems.price * purchaseItems.quantity)- discount);
                            } else {
                                var itemTaxPrice = parseFloat((totalItemTaxRate / 100)) * parseFloat((item.product.purchase_price * 1)- discount);
                            }

                            $(el.parent().parent().parent().find('.itemTaxPrice')).val(itemTaxPrice.toFixed(2));
                            $(el.parent().parent().parent().find('.itemTaxRate')).val(totalItemTaxRate.toFixed(2));
                            $(el.parent().parent().parent().find('.taxes')).html(taxes);
                            $(el.parent().parent().parent().find('.tax')).val(tax);
                            $(el.parent().parent().parent().find('.unit')).html(item.unit);

                            var inputs = $(".amount");
                            var subTotal = 0;
                            for (var i = 0; i < inputs.length; i++) {
                                subTotal = parseFloat(subTotal) + parseFloat($(inputs[i]).html());
                            }

                            var totalItemPrice = 0;
                            var inputs_quantity = $(".quantity");
                            var priceInput = $('.price');
                            for (var j = 0; j < priceInput.length; j++) {
                                totalItemPrice += (parseFloat(priceInput[j].value) * parseFloat(inputs_quantity[j].value));
                            }

                            var totalItemTaxPrice = 0;
                            var itemTaxPriceInput = $('.itemTaxPrice');
                            for (var j = 0; j < itemTaxPriceInput.length; j++) {
                                totalItemTaxPrice += parseFloat(itemTaxPriceInput[j].value);
                                if (purchaseItems != null) {
                                    $(el.parent().parent().parent().find('.amount')).html(parseFloat(amount)+parseFloat(itemTaxPrice)-parseFloat(discount));
                                } else {
                                    $(el.parent().parent().parent().find('.amount')).html(parseFloat(item.totalAmount)+parseFloat(itemTaxPrice));
                                }

                            }

                            var totalItemDiscountPrice = 0;
                            var itemDiscountPriceInput = $('.discount');

                            for (var k = 0; k < itemDiscountPriceInput.length; k++) {
                                totalItemDiscountPrice += parseFloat(itemDiscountPriceInput[k].value);
                            }

                            $('.subTotal').html(totalItemPrice.toFixed(2));
                            $('.totalTax').html(totalItemTaxPrice.toFixed(2));
                            $('.totalAmount').html((parseFloat(totalItemPrice) - parseFloat(totalItemDiscountPrice) + parseFloat(totalItemTaxPrice)).toFixed(2));
                            $('.totalDiscount').html(totalItemDiscountPrice.toFixed(2));

                        }
                    });

                },
            });
        }
        $(document).on('change', '.item', function () {
            changeItem($(this));
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

        $(document).on('click', '[data-repeater-create]', function () {
            $('.item :selected').each(function () {
                var id = $(this).val();
                $(".item option[value=" + id + "]").prop("disabled", true);
            });
        })

        $(document).on('click', '[data-repeater-delete]', function () {
            // $('.delete_item').click(function () {
            if (confirm('Are you sure you want to delete this element?')) {
                var el = $(this).parent().parent();
                var id = $(el.find('.id')).val();

                $.ajax({
                    url: '{{route(ViewsConstants::PRC.'.product.destroy')}}',
                    type: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': jQuery('#token').val()
                    },
                    data: {
                        'id': id
                    },
                    cache: false,
                    success: function (data) {

                    },
                });

            }
        });
    </script>
    <script>
        $(document).on('click', '[data-repeater-delete]', function () {
            $(".price").change();
            $(".discount").change();
        });
    </script>
@endpush

@section('content')
    <div class="row">

        {{ Collective\Html\FormFacade::model($purchase, array('route' => array(ViewsConstants::PRC.'.update', $purchase->id), 'method' => 'PUT','class'=>'w-100')) }}
        <div class="{{ VC::C12 }}">
            <input type="hidden" name="_token" id="token" value="{{ csrf_token() }}">
            <div class="card">
                <div class="{{ VC::CD_BD }}">
                    <div class="row">
                        <div class="{{ VC::CM6 }}">
                            <div class="{{ VC::FM_G }}" id="vendor-box">
                                {{ Collective\Html\FormFacade::label('vendor_id', __('Vendor'),['class'=>'form-label']) }}
                                {{ Collective\Html\FormFacade::select('vendor_id', $vendors,null, array('class' => 'form-control select','id'=>'vendor','data-url'=>route(ViewsConstants::PRC.'.vendor'),'required'=>'required')) }}
                            </div>
                            <div id="vendor_detail" class="d-none">
                            </div>
                        </div>
                        <div class="{{ VC::CM6 }}">
                            <div class="row">
                                <div class="{{ VC::CM6 }}">
                                <div class="{{ VC::FM_G }}">
                                    {{ Collective\Html\FormFacade::label('warehouse_id', __('Warehouse'),['class'=>'form-label']) }}
                                    {{ Collective\Html\FormFacade::select('warehouse_id', $warehouse,null, array('class' => 'form-control select','required'=>'required')) }}
                                </div>
                            </div>
                                <div class="{{ VC::CM6 }}">
                                    <div class="{{ VC::FM_G }}">
                                        {{ Collective\Html\FormFacade::label('category_id', __('Category'),['class'=>'form-label']) }}
                                        {{ Collective\Html\FormFacade::select('category_id', $category,null, array('class' => 'form-control select','required'=>'required')) }}
                                    </div>
                                </div>

                            </div>
                            <div class="row">

                                <div class="{{ VC::CM6 }}">
                                    <div class="{{ VC::FM_G }}">
                                        {{ Collective\Html\FormFacade::label('purchase_date', __('Purchase Date'),['class'=>'form-label']) }}
                                        {{Collective\Html\FormFacade::date('purchase_date',null,array('class'=>'form-control','required'=>'required'))}}
                                    </div>
                                </div>
                                <div class="{{ VC::CM6 }}">
                                    <div class="{{ VC::FM_G }}">
                                        {{ Collective\Html\FormFacade::label('purchase_number', __('Purchase Number'),['class'=>'form-label']) }}
                                        <input type="text" class="{{ VC::FM_CT }}" value="{{$purchase_number}}" readonly>
                                    </div>
                                </div>

                            </div>
{{--                            <div class="{{ VC::FM_CHK }} {{ VC::CST_CB }} {{ VC::MT4 }}">--}}
{{--                                        <input class="form-check-input" type="checkbox" name="discount_apply" id="discount_apply" {{$purchase->discount_apply==1?'checked':''}}>--}}
{{--                                        <label class="form-check-label" for="discount_apply">{{__('Discount Apply')}}</label>--}}
{{--                                    </div>--}}
                        </div>

                    </div>
                </div>
            </div>
        </div>
        <div class="{{ VC::C12 }}">
            <h5 class="d-inline-block {{ VC::MB4 }}">{{__('Product & Services')}}</h5>
            <div class="card repeater" data-value='{!! json_encode($purchase->items) !!}'>
                <div class="item-section {{ VC::PY2 }}">
                    <div class="row {{ VC::JCB }} {{ VC::ALC }}">
                        <div class="{{ VC::CM12 }} {{ VC::DFL_AIC_JCB }} justify-content-md-end">
                            <div class="all-button-box me-2">
                                <a href="#" data-repeater-create="" class="{{ VC::BT_PRM }}" data-bs-toggle="modal" data-target="#add-bank">
                                    <i class="{{ VC::TI_PLS }}"></i> {{__('Add item')}}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="{{ VC::CD_BD_TB_BD }}">
                    <div class="{{ VC::TB_RSP }}">
                        <table class="table {{ VC::MB0 }}" data-repeater-list="items" id="sortable-table">
                            <thead>
                            <tr>
                                <th>{{__('Items')}}</th>
                                <th>{{__('Quantity')}}</th>
                                <th>{{__('Price')}} </th>
                                <th>{{__('Discount')}}</th>
                                <th>{{__('Tax')}} (%)</th>

                                <th class="{{ VC::TX_END }}">{{__('Amount')}} <br><small class="{{ VC::TX_DNG }} font-weight-bold">{{__('after tax & discount')}}</small></th>
                                <th></th>
                            </tr>
                            </thead>
                            <tbody class="ui-sortable" data-repeater-item>
                            <tr>
                                {{ Collective\Html\FormFacade::hidden('id',null, array('class' => 'form-control id')) }}
                                <td width="25%">
                                    <div class="{{ VC::FM_G }}">
                                        {{ Collective\Html\FormFacade::select('item', $product_services,null, array('class' => 'form-control select item','data-url'=>route(ViewsConstants::PRC.'.product'))) }}
                                    </div>
                                </td>
                                <td>
                                    <div class="{{ VC::FM_G }} price-input input-group search-form">
                                        {{ Collective\Html\FormFacade::text('quantity',null, array('class' => 'form-control quantity','required'=>'required','placeholder'=>__('Qty'),'required'=>'required')) }}
                                        <span class="unit {{ VC::TXTS_TRP }}"></span>
                                    </div>
                                </td>
                                <td>
                                    <div class="{{ VC::FM_G }} price-input input-group search-form">
                                        {{ Collective\Html\FormFacade::text('price',null, array('class' => 'form-control price','required'=>'required','placeholder'=>__('Price'),'required'=>'required')) }}
                                        <span class="{{ VC::TXTS_TRP }}">{{\Auth::user()->currencySymbol()}}</span>
                                    </div>
                                </td>
                                <td>
                                    <div class="{{ VC::FM_G }} price-input input-group search-form">
                                        {{ Collective\Html\FormFacade::text('discount',null, array('class' => 'form-control discount','required'=>'required','placeholder'=>__('Discount'))) }}
                                        <span class="{{ VC::TXTS_TRP }}">{{\Auth::user()->currencySymbol()}}</span>
                                    </div>
                                </td>
                                <td>
                                    <div class="{{ VC::FM_G }}">
                                        <div class="input-group">
                                            <div class="taxes"></div>
                                            {{ Collective\Html\FormFacade::hidden('tax','', array('class' => 'form-control tax')) }}
                                            {{ Collective\Html\FormFacade::hidden('itemTaxPrice','', array('class' => 'form-control itemTaxPrice')) }}
                                            {{ Collective\Html\FormFacade::hidden('itemTaxRate','', array('class' => 'form-control itemTaxRate')) }}
                                        </div>
                                    </div>
                                </td>

                                <td class="{{ VC::TX_END }} amount">
                                    0.00
                                </td>

                                <td>
                                    @can('delete proposal product')
                                        <a href="#" class="{{ ViewClassNamesConstants::TRS_PARA }}" data-repeater-delete></a>
                                    @endcan
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2">
                                    <div class="{{ VC::FM_G }}">
                                        {{ Collective\Html\FormFacade::textarea('description', null, ['class'=>'form-control pro_description','rows'=>'2','placeholder'=>__('Description')]) }}
                                    </div>
                                </td>
                                <td colspan="5"></td>
                            </tr>
                            </tbody>
                            <tfoot>
                            <tr>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td></td>
                                <td><strong>{{__('Sub Total')}} ({{\Auth::user()->currencySymbol()}})</strong></td>
                                <td class="{{ VC::TX_END }} subTotal">0.00</td>
                                <td></td>
                            </tr>
                            <tr>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td></td>
                                <td><strong>{{__('Discount')}} ({{\Auth::user()->currencySymbol()}})</strong></td>
                                <td class="{{ VC::TX_END }} totalDiscount">0.00</td>
                                <td></td>
                            </tr>
                            <tr>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td></td>
                                <td><strong>{{__('Tax')}} ({{\Auth::user()->currencySymbol()}})</strong></td>
                                <td class="{{ VC::TX_END }} totalTax">0.00</td>
                                <td></td>
                            </tr>
                            <tr>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td class="blue-text"><strong>{{__('Total Amount')}} ({{\Auth::user()->currencySymbol()}})</strong></td>
                                <td class="blue-text {{ VC::TX_END }} totalAmount">0.00</td>
                                <td></td>
                            </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal-footer">
            <input type="button" value="{{__('Cancel')}}" onclick="location.href = ' {{ route('purchase.index') }}';" class="{{ VC::BT_LG }}">
            <input type="submit" value="{{__('Update')}}" class="{{ VC::BT_PRM }}">
        </div>
        {{ Collective\Html\FormFacade::close() }}
    </div>
@endsection
