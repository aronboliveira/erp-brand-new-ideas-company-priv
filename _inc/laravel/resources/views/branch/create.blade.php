{{Collective\Html\FormFacade::open(array('url'=>'branch','method'=>'post'))}}
<div class="modal-body">

    <div class="row">
        <div class="col-12">
            <div class="form-group">
                {{Collective\Html\FormFacade::label('name',__('Name'),['class'=>'form-label'])}}
                {{Collective\Html\FormFacade::text('name',null,array('class'=>'form-control','placeholder'=>__('Enter Branch Name')))}}
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
    <input type="submit" value="{{__('Create')}}" class="btn btn-primary">
</div>
    {{Collective\Html\FormFacade::close()}}

