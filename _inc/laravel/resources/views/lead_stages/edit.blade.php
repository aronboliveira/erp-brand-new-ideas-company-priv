{{ Collective\Html\FormFacade::model($leadStage, array('route' => array('lead_stages.update', $leadStage->id), 'method' => 'PUT')) }}
<div class="modal-body">
    <div class="row">
        <div class="form-group col-12">
            {{ Collective\Html\FormFacade::label('name', __('Stage Name'),['class'=>'form-label']) }}
            {{ Collective\Html\FormFacade::text('name', null, array('class' => 'form-control','required'=>'required')) }}
        </div>
        <div class="form-group col-12">
            {{ Collective\Html\FormFacade::label('pipeline_id', __('Pipeline'),['class'=>'form-label']) }}
            {{ Collective\Html\FormFacade::select('pipeline_id', $pipelines,null, array('class' => 'form-control select2','required'=>'required')) }}
        </div>
    </div>
</div>

<div class="modal-footer">
    <input type="button" value="{{__('Cancel')}}" class="btn btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{__('Update')}}" class="btn btn-primary">
</div>

{{Collective\Html\FormFacade::close()}}
