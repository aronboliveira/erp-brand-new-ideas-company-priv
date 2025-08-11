{{Collective\Html\FormFacade::model($department,array('route' => array('department.update', $department->id), 'method' => 'PUT')) }}
<div class="modal-body">

    <div class="row">
        <div class="col-12">
            <div class="form-group">
                {{Collective\Html\FormFacade::label('branch_id',__('Branch'))}}
                {{Collective\Html\FormFacade::select('branch_id',$branch,null,array('class'=>'form-control select','placeholder'=>__('select Branch')))}}
            </div>
        </div>
        <div class="col-12">
            <div class="form-group">
                {{Collective\Html\FormFacade::label('name',__('Name'))}}
                {{Collective\Html\FormFacade::text('name',null,array('class'=>'form-control','placeholder'=>__('Enter Department Name')))}}
                @error('name')
                <span class="invalid-name" role="alert">
                    <strong class="text-danger">{{ $message }}</strong>
                </span>
                @enderror
            </div>
        </div>

    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{__('Cancel')}}" class="btn btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{__('Update')}}" class="btn btn-primary">
</div>
{{Collective\Html\FormFacade::close()}}
