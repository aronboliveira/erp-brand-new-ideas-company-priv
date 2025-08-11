@php
    use App\Config\Constants\ViewClassNamesConstants;
@endphp
{{ Collective\Html\FormFacade::model($productService, array('route' => array('productstock.update', $productService->id), 'method' => 'PUT')) }}
<div class="modal-body">
    <div class="row">

        <div class="form-group col-md-6">
            {{ Collective\Html\FormFacade::label('Product', __('Product'),['class'=>'form-label']) }}<br>
            {{$productService->name}}

        </div>
        <div class="form-group col-md-6">
            {{ Collective\Html\FormFacade::label('Product', __('SKU'),['class'=>'form-label']) }}<br>
            {{$productService->sku}}

        </div>

        {{--        <div class="form-group quantity">--}}
        {{--            <div class="d-flex radio-check ">--}}
        {{--                <div class="{{ ViewClassNamesConstants::FM_CHK_IL_GP_COLM6 }}">--}}
        {{--                    <input type="radio" id="plus_quantity" value="Add" name="quantity_type" class="form-check-input" checked="checked">--}}
        {{--                    <label class="form-check-label" for="plus_quantity">{{__('Add Quantity')}}</label>--}}
        {{--                </div>--}}
        {{--                <div class="{{ ViewClassNamesConstants::FM_CHK_IL_GP_COLM6 }}">--}}
        {{--                    <input type="radio" id="minus_quantity" value="Less" name="quantity_type" class="form-check-input">--}}
        {{--                    <label class="form-check-label" for="minus_quantity">{{__('Less Quantity')}}</label>--}}
        {{--                </div>--}}
        {{--            </div>--}}
        {{--        </div>--}}

        <div class="form-group col-md-12">
            {{ Collective\Html\FormFacade::label('quantity', __('Quantity'),['class'=>'form-label']) }}<span class="text-danger">*</span>
            {{ Collective\Html\FormFacade::number('quantity',"", array('class' => 'form-control','required'=>'required')) }}
        </div>
    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{__('Cancel')}}" class="btn btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{__('Save')}}" class="btn btn-primary">
</div>
{{Collective\Html\FormFacade::close()}}
