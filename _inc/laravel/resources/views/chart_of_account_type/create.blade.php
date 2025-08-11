<div class="card bg-none card-box">
    {{ Collective\Html\FormFacade::open(array('url' => 'chart-of-account-type')) }}
    <div class="row">
        <div class="form-group col-md-12">
            {{ Collective\Html\FormFacade::label('name', __('Name'),['class'=>'form-label']) }}
            {{ Collective\Html\FormFacade::text('name', '', array('class' => 'form-control','required'=>'required')) }}
            @error('name')
            <small class="invalid-name" role="alert">
                <strong class="text-danger">{{ $message }}</strong>
            </small>
            @enderror
        </div>
    </div>
    </div>
<div class="modal-footer">
    <input type="button" value="{{__('Cancel')}}" class="btn btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{__('Create')}}" class="btn btn-primary">
</div>
    {{ Collective\Html\FormFacade::close() }}
