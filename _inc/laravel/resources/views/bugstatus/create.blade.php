{{ Collective\Html\FormFacade::open(array('url' => 'bugstatus')) }}
<div class="modal-body">

    <div class="row">
        <div class="form-group col-12">
            {{ Collective\Html\FormFacade::label('title', __('Bug Status Title'),['class'=>'form-label']) }}
            {{ Collective\Html\FormFacade::text('title', '', array('class' => 'form-control','required'=>'required')) }}
        </div>

    </div>
</div>
    <div class="modal-footer">
        <input type="button" value="{{__('Cancel')}}" class="btn btn-light" data-bs-dismiss="modal">
        <input type="submit" value="{{__('Create')}}" class="btn btn-primary">
    </div>
    {{ Collective\Html\FormFacade::close() }}

