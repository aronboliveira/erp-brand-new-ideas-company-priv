@php
    use Collective\Html\FormFacade as Form;
    use App\Config\Constants\{DatabaseConstants,ViewClassNamesConstants, ViewsConstants};
    use App\Config\Constants\DatabaseConstants;

    $plan = \App\Models\Utility::getChatGPTSettings();
    $fields = [
        ['name'=>'name',                   'type'=>'text',     'label'=>__('Name'),             'required'=>true],
        ['name'=>'sku',                    'type'=>'text',     'label'=>__('SKU'),              'required'=>true],
        ['name'=>'sale_price',             'type'=>'number',   'label'=>__('Sale Price'),       'required'=>true,'step'=>'0.01'],
        ['name'=>'sale_chartaccount_id',   'type'=>'select',   'label'=>__('Income Account'),   'options'=>$incomeChartAccounts,'required'=>true],
        ['name'=>'purchase_price',         'type'=>'number',   'label'=>__('Purchase Price'),   'required'=>true,'step'=>'0.01'],
        ['name'=>'expense_chartaccount_id','type'=>'select',   'label'=>__('Expense Account'),  'options'=>$expenseChartAccounts,'required'=>true],
        ['name'=>'tax_id',                 'type'=>'select2',  'label'=>__('Tax'),              'options'=>$tax,'multiple'=>true],
        ['name'=>'category_id',            'type'=>'select',   'label'=>__('Category'),         'options'=>$category,'required'=>true],
        ['name'=>'unit_id',                'type'=>'select',   'label'=>__('Unit'),             'options'=>$unit,'required'=>true],
        ['name'=>'description',            'type'=>'textarea','label'=>__('Description'),      'rows'=>2],
    ];
@endphp

{{ Form::model($productService,[
    'route'     => ['product_services.update',$productService->id],
    'method'    => 'PUT',
    'enctype'   => 'multipart/form-data',
]) }}

<div class="modal-body">
    @if($plan->chatgpt == 1)
        <div class="text-end mb-3">
            <a href="#"
               class="btn btn-primary btn-icon btn-sm"
               data-ajax-popup-over="true"
               data-size="md"
               data-url="{{ route('generate',[DatabaseConstants::TABLE_PROD_SERVS]) }}"
               data-bs-placement="top"
               data-title="{{ __('Generate content with AI') }}">
                <i class="fas fa-robot"></i> {{ __('Generate with AI') }}
            </a>
        </div>
    @endif

    <div class="row">
        @foreach($fields as $f)
            @php
                $attrs = ['class'=>'form-control'];
                if(!empty($f['required'])) $attrs['required']='required';
                if(!empty($f['step']))     $attrs['step']=$f['step'];
            @endphp
            <div class="form-group col-md-6">
                {{ Form::label($f['name'],$f['label'],['class'=>'form-label']) }}
                @if(!empty($f['required']))<span class="text-danger">*</span>@endif

                @if($f['type']=='text' || $f['type']=='number')
                    {{ Form::{ $f['type'] }($f['name'], null, $attrs) }}
                @elseif($f['type']=='select')
                    {{ Form::select($f['name'], $f['options'], null, $attrs + ['class'=>'form-control select']) }}
                @elseif($f['type']=='select2')
                    {{ Form::select($f['name'], $f['options'], null, $attrs + ['class'=>'form-control select2','id'=>'choices-multiple1','multiple'=>'']) }}
                @elseif($f['type']=='textarea')
                    {{ Form::textarea($f['name'], null, $attrs + ['rows'=>$f['rows']]) }}
                @endif
            </div>
        @endforeach

        <div class="col-md-6 form-group">
            {{ Form::label('pro_image',__('Product Image'),['class'=>'form-label']) }}
            <div class="choose-file">
                <label for="pro_image" class="form-label">
                    <input
                        type="file"
                        name="pro_image"
                        id="pro_image"
                        class="form-control"
                        data-filename="pro_image_create"
                    >
                    <img
                        id="image"
                        class="mt-3"
                        width="100"
                        src="{{ $productService->pro_image
                            ? asset(Storage::url('uploads/pro_image/'.$productService->pro_image))
                            : asset(Storage::url('uploads/pro_image/user-2_1654779769.jpg')) }}"
                    />
                </label>
            </div>
        </div>

        <div class="col-md-6">
            <div class="form-group">
                <label class="d-block form-label">{{ __('Type') }}</label>
                <div class="row">
                    @foreach(['product','service'] as $type)
                        <div class="col-md-6">
                            <div class="{{ ViewClassNamesConstants::FM_CHK_IL }}">
                                <input
                                    class="form-check-input type"
                                    type="radio"
                                    name="type"
                                    id="type_{{ $type }}"
                                    value="{{ $type }}"
                                    {{ $productService->type == $type ? 'checked' : '' }}
                                >
                                <label class="form-label" for="type_{{ $type }}">{{ ucfirst($type) }}</label>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="form-group col-md-6 quantity {{ $productService->type=='service' ? 'd-none' : '' }}">
            {{ Form::label('quantity',__('Quantity'),['class'=>'form-label']) }}<span class="text-danger">*</span>
            {{ Form::text('quantity', null, ['class'=>'form-control','required'=>'required']) }}
        </div>

        @if(!$customFields->isEmpty())
            <div class="col-md-6">
                <div class="tab-pane fade show" id="tab-2" role="tabpanel">
                    @include(ViewsConstants::CST_FD . '.formBuilder')
                </div>
            </div>
        @endif
    </div>
</div>

<div class="modal-footer">
    <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
    <button type="submit" class="btn btn-primary">{{ __('Update') }}</button>
</div>

{{ Form::close() }}

<script>
    document.getElementById('pro_image').addEventListener('change', function() {
        document.getElementById('image').src = URL.createObjectURL(this.files[0]);
    });

    document.querySelectorAll('.type').forEach(el =>
        el.addEventListener('click', function() {
            document.querySelector('.quantity')
                .classList.toggle('d-none', this.value !== 'product');
        })
    );
</script>
