{{ Collective\Html\FormFacade::open(array('route' => ['form.field.store',$formbuilder->id])) }}
<div class="modal-body">
    <div class="row" id="frm_field_data">
        <div class="col-12 form-group">
            {{ Collective\Html\FormFacade::label('name', __('Question Name'),['class'=>'form-label']) }}
            {{ Collective\Html\FormFacade::text('name[]', '', array('class' => 'form-control','required'=>'required')) }}
        </div>
        <div class="col-12 form-group">
            {{ Collective\Html\FormFacade::label('type', __('Type'),['class'=>'form-label']) }}
            {{ Collective\Html\FormFacade::select('type[]', $types,null, array('class' => 'form-control select2','id'=>'choices-multiple1','required'=>'required')) }}
        </div>

    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{__('Cancel')}}" class="btn btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{__('Create')}}" class="btn btn-primary">
</div>
{{Collective\Html\FormFacade::close()}}
