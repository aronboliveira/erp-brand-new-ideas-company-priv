{{ Collective\Html\FormFacade::model($pipeline, array('route' => array('pipelines.update', $pipeline->id), 'method' => 'PUT')) }}
<div class="modal-body">
    <div class="row">
        <div class="form-group col-12">
            {{ Collective\Html\FormFacade::label('name', __('Pipeline Name'),['class'=>'form-label']) }}
            {{ Collective\Html\FormFacade::text('name', null, array('class' => 'form-control','required'=>'required')) }}
        </div>
    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{__('Cancel')}}" class="btn btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{__('Update')}}" class="btn btn-primary">
</div>
{{Collective\Html\FormFacade::close()}}
