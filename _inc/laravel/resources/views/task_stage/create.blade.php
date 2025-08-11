{{ Collective\Html\FormFacade::open(array('url' => 'project-task-new-stage')) }}
<div class="modal-body">

    <div class="row">
        <div class="form-group col-12">
            {{ Collective\Html\FormFacade::label('name', __('Project Task Stage Name'),['class'=>'form-label']) }}
            {{ Collective\Html\FormFacade::text('name', '', array('class' => 'form-control','required'=>'required')) }}
        </div>
        <div class="form-group col-12">
            {{ Collective\Html\FormFacade::label('color', __('Color'),['class'=>'form-label']) }}
            <input class="jscolor form-control" value="FFFFFF" name="color" id="color" required>
            <small class="small">{{ __('For chart representation') }}</small>
        </div>

    </div>
</div>
<div class="modal-footer">
        <input type="button" value="{{__('Cancel')}}" class="btn btn-light" data-bs-dismiss="modal">
        <input type="submit" value="{{__('Create')}}" class="btn btn-primary">
</div>
{{ Collective\Html\FormFacade::close() }}
