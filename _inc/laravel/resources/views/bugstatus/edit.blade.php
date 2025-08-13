    {{ Collective\Html\FormFacade::model($bug_status, array('route' => array(ViewsConstants::BUG_STT.'.update', $bug_status->id), 'method' => 'PUT')) }}
    <div class="modal-body">

    <div class="row">
        <div class="form-group col-12">
            {{ Collective\Html\FormFacade::label('title', __('Bug Status Title'),['class'=>'form-label']) }}
            {{ Collective\Html\FormFacade::text('title',null, array('class' => 'form-control','required'=>'required')) }}
        </div>

    </div>
</div>

<div class="modal-footer">
    <input type="button" value="{{__('Cancel')}}" class="btn btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{__('Update')}}" class="btn btn-primary">
</div>
    {{ Collective\Html\FormFacade::close() }}

