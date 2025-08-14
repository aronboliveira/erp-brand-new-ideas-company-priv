
{{ Collective\Html\FormFacade::open(array('url' => 'performanceType')) }}
<div class="modal-body">

    <div class="form-group">
        {{ Collective\Html\FormFacade::label('name', __('Name'),['class'=>'form-label'])}}
        {{ Collective\Html\FormFacade::text('name', '', array('class' => 'form-control','required'=>'required')) }}
    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{__('Cancel')}}" class="btn btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{__('Create')}}" class="btn btn-primary">
</div>
{{ Collective\Html\FormFacade::close() }}

