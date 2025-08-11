{{ Collective\Html\FormFacade::model($lead, array('route' => array('leads.products.update', $lead->id), 'method' => 'PUT')) }}
<div class="modal-body">
    <div class="row">
        <div class="col-12 form-group">
            {{ Collective\Html\FormFacade::label('products', __('Products'),['class'=>'form-label']) }}
            {{ Collective\Html\FormFacade::select('products[]', $products,false, array('class' => 'form-control select2','id'=>'choices-multiple3','multiple'=>'','required'=>'required')) }}
        </div>
    </div>
</div>

<div class="modal-footer">
    <input type="button" value="{{__('Cancel')}}" class="btn btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{__('Save')}}" class="btn btn-primary">
</div>

{{Collective\Html\FormFacade::close()}}


