
{{Collective\Html\FormFacade::model($permission, array('route' => array('permissions.update', $permission->id), 'method' => 'PUT')) }}
<div class="card-body">
    <div class="form-group">
        {{Collective\Html\FormFacade::label('name',__('Name'))}}
        {{Collective\Html\FormFacade::text('name',null,array('class'=>'form-control','placeholder'=>__('Enter Permission Name')))}}
        @error('name')
        <span class="invalid-name" role="alert">
                    <strong class="text-danger">{{ $message }}</strong>
                </span>
        @enderror
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn dark btn-outline" data-dismiss="modal">{{__('Cancel')}}</button>
    {{Collective\Html\FormFacade::submit(__('Update'),array('class'=>'btn green'))}}
</div>
{{Collective\Html\FormFacade::close()}}
