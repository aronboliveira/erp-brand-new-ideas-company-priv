{{ Collective\Html\FormFacade::open(array('url' => 'taxes')) }}
<div class="modal-body">
    <div class="row">
        <div class="form-group col-md-6">
            {{ Collective\Html\FormFacade::label('name', __('Tax Rate Name'),['class'=>'form-label']) }}
            {{ Collective\Html\FormFacade::text('name', '', array('class' => 'form-control','required'=>'required')) }}
            @error('name')
            <small class="invalid-name" role="alert">
                <strong class="text-danger">{{ $message }}</strong>
            </small>
            @enderror
        </div>
        <div class="form-group col-md-6">
            {{ Collective\Html\FormFacade::label('rate', __('Tax Rate %'),['class'=>'form-label']) }}
            {{ Collective\Html\FormFacade::number('rate', '', array('class' => 'form-control','required'=>'required','step'=>'0.01')) }}
            @error('rate')
            <small class="invalid-rate" role="alert">
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
