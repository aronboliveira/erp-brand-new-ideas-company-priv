{{ Collective\Html\FormFacade::model($revenue, array('route' => array('revenue.update', $revenue->id), 'method' => 'PUT','enctype' => 'multipart/form-data')) }}
<div class="modal-body">
    <div class="row">
        <div class="form-group  col-md-6">
            {{ Collective\Html\FormFacade::label('date', __('Date'),['class'=>'form-label']) }}
            {{Collective\Html\FormFacade::date('date',null,array('class'=>'form-control','required'=>'required'))}}
        </div>
        <div class="form-group  col-md-6">
            {{ Collective\Html\FormFacade::label('amount', __('Amount'),['class'=>'form-label']) }}
            {{ Collective\Html\FormFacade::number('amount', null, array('class' => 'form-control','required'=>'required','step'=>'0.01')) }}
        </div>
        <div class="form-group  col-md-6">
            {{ Collective\Html\FormFacade::label('account_id', __('Account'),['class'=>'form-label']) }}
            {{ Collective\Html\FormFacade::select('account_id',$accounts,null, array('class' => 'form-control select','required'=>'required')) }}
        </div>
        <div class="form-group  col-md-6">
            {{ Collective\Html\FormFacade::label('customer_id', __('Customer'),['class'=>'form-label']) }}
            {{ Collective\Html\FormFacade::select('customer_id', $customers,null, array('class' => 'form-control select','required'=>'required')) }}
        </div>
        <div class="form-group  col-md-12">
            {{ Collective\Html\FormFacade::label('description', __('Description'),['class'=>'form-label']) }}
            {{ Collective\Html\FormFacade::textarea('description', null, array('class' => 'form-control','rows'=>3)) }}
        </div>
        <div class="form-group  col-md-6">
            {{ Collective\Html\FormFacade::label('category_id', __('Category'),['class'=>'form-label']) }}
            {{ Collective\Html\FormFacade::select('category_id', $categories,null, array('class' => 'form-control select','required'=>'required')) }}
        </div>

        <div class="form-group  col-md-6">
            {{ Collective\Html\FormFacade::label('reference', __('Reference'),['class'=>'form-label']) }}
            {{ Collective\Html\FormFacade::text('reference', null, array('class' => 'form-control')) }}

        </div>

        <div class="form-group col-md-6">
            {{Collective\Html\FormFacade::label('add_receipt',__('Payment Receipt'),['class' => 'col-form-label'])}}
            {{Collective\Html\FormFacade::file('add_receipt',array('class'=>'form-control', 'id'=>'files'))}}
            <img id="image" src="{{asset(Storage::url('uploads/revenue')).'/'.$revenue->add_receipt}}" class="mt-2" style="width:25%;"/>
        </div>

    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{__('Cancel')}}" class="btn btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{__('Update')}}" class="btn btn-primary">
</div>
{{ Collective\Html\FormFacade::close() }}



<script>
    document.getElementById('files').onchange = function () {
        var src = URL.createObjectURL(this.files[0])
        document.getElementById('image').src = src
    }
</script>
