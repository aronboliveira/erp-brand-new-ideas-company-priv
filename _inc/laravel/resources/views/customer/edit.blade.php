@php
    use Collective\Html\FormFacade as Form;
    use App\Config\Constants\ViewsConstants;
    $basicFields = [
        ['name'=>'name','type'=>'text','label'=>__('Name'),'cols'=>4,'attrs'=>['required'=>'required']],
        ['name'=>'contact','type'=>'number','label'=>__('Contact'),'cols'=>4,'attrs'=>['required'=>'required']],
        ['name'=>'email','type'=>'text','label'=>__('Email'),'cols'=>4],
        ['name'=>'tax_number','type'=>'text','label'=>__('Tax Number'),'cols'=>4],
    ];
    $billingFields = [
        ['name'=>'billing_name','type'=>'text','label'=>__('Name'),'cols'=>6],
        ['name'=>'billing_phone','type'=>'text','label'=>__('Phone'),'cols'=>6],
        ['name'=>'billing_address','type'=>'textarea','label'=>__('Address'),'cols'=>12,'attrs'=>['rows'=>3]],
        ['name'=>'billing_city','type'=>'text','label'=>__('City'),'cols'=>6],
        ['name'=>'billing_state','type'=>'text','label'=>__('State'),'cols'=>6],
        ['name'=>'billing_country','type'=>'text','label'=>__('Country'),'cols'=>6],
        ['name'=>'billing_zip','type'=>'text','label'=>__('Zip Code'),'cols'=>6],
    ];
    $shippingFields = [
        ['name'=>'shipping_name','type'=>'text','label'=>__('Name'),'cols'=>6],
        ['name'=>'shipping_phone','type'=>'text','label'=>__('Phone'),'cols'=>6],
        ['name'=>'shipping_address','type'=>'textarea','label'=>__('Address'),'cols'=>12,'attrs'=>['rows'=>3]],
        ['name'=>'shipping_city','type'=>'text','label'=>__('City'),'cols'=>6],
        ['name'=>'shipping_state','type'=>'text','label'=>__('State'),'cols'=>6],
        ['name'=>'shipping_country','type'=>'text','label'=>__('Country'),'cols'=>6],
        ['name'=>'shipping_zip','type'=>'text','label'=>__('Zip Code'),'cols'=>6],
    ];
@endphp
{{ Form::model($customer,['route'=>['customer.update',$customer->id],'method'=>'PUT']) }}
<div class="modal-body">
    <h6 class="sub-title">{{ __('Basic Info') }}</h6>
    <div class="row">
        @foreach($basicFields as $f)
            <div class="col-lg-4 col-md-4 col-sm-6">
                <div class="form-group">
                    {{ Form::label($f['name'],$f['label'],['class'=>'form-label']) }}
                    @php $attrs=array_merge(['class'=>'form-control'],$f['attrs']??[]) @endphp
                    @if($f['type']==='textarea')
                        {{ Form::textarea($f['name'],null,$attrs) }}
                    @else
                        {{ Form::{$f['type']}($f['name'],null,$attrs) }}
                    @endif
                </div>
            </div>
        @endforeach
        @if(!$customFields->isEmpty())
            <div class="col-lg-4 col-md-4 col-sm-6">
                <div class="tab-pane fade show" id="tab-2" role="tabpanel">
                    @include(ViewsConstants::CST_FD . '.formBuilder')
                </div>
            </div>
        @endif
    </div>
    <h6 class="sub-title">{{ __('Billing Address') }}</h6>
    <div class="row">
        @foreach($billingFields as $f)
            <div class="col-lg-6 col-md-6 col-sm-6">
                <div class="form-group">
                    {{ Form::label($f['name'],$f['label'],['class'=>'form-label']) }}
                    @php $attrs=array_merge(['class'=>'form-control'],$f['attrs']??[]) @endphp
                    @if($f['type']==='textarea')
                        {{ Form::textarea($f['name'],null,$attrs) }}
                    @else
                        {{ Form::{$f['type']}($f['name'],null,$attrs) }}
                    @endif
                </div>
            </div>
        @endforeach
    </div>
    @if(\App\Models\Utility::getValByName('shipping_display')==='on')
        <div class="col-md-12 text-end">
            <button type="button" id="billing_data" class="btn btn-primary">{{ __('Shipping Same As Billing') }}</button>
        </div>
        <h6 class="sub-title">{{ __('Shipping Address') }}</h6>
        <div class="row">
            @foreach($shippingFields as $f)
                <div class="col-lg-6 col-md-6 col-sm-6">
                    <div class="form-group">
                        {{ Form::label($f['name'],$f['label'],['class'=>'form-label']) }}
                        @php $attrs=array_merge(['class'=>'form-control'],$f['attrs']??[]) @endphp
                        @if($f['type']==='textarea')
                            {{ Form::textarea($f['name'],null,$attrs) }}
                        @else
                            {{ Form::{$f['type']}($f['name'],null,$attrs) }}
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
    <button type="submit" class="btn btn-primary">{{ __('Update') }}</button>
</div>
{{ Form::close() }}
