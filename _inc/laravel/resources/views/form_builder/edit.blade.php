@php
    use App\Config\Constants\ViewClassNamesConstants;
@endphp
{{ Collective\Html\FormFacade::model($formBuilder, array('route' => array('form_builder.update', $formBuilder->id), 'method' => 'PUT')) }}
<div class="modal-body">
    <div class="row">
        <div class="col-12 form-group">
            {{ Collective\Html\FormFacade::label('name', __('Name'),['class'=>'form-label']) }}
            {{ Collective\Html\FormFacade::text('name', null, array('class' => 'form-control','required' => 'required')) }}
        </div>
        <div class="col-12 form-group">
            <label for="exampleColorInput" class="form-label">{{__('Active')}}</label>
            <div class="d-flex radio-check">
                <div class="{{ ViewClassNamesConstants::FM_CHK_IL }}">
                    <input type="radio" id="on" value="1" name="is_active" class="form-check-input" {{($formBuilder->is_active == 1) ? 'checked' : ''}}>
                    <label class="custom-control-label form-label" for="on">{{__('On')}}</label>
                </div>
                <div class="{{ ViewClassNamesConstants::FM_CHK_IL }}">
                    <input type="radio" id="off" value="0" name="is_active" class="form-check-input" {{($formBuilder->is_active == 0) ? 'checked' : ''}}>
                    <label class="custom-control-label form-label" for="off">{{__('Off')}}</label>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{__('Cancel')}}" class="btn btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{__('Create')}}" class="btn btn-primary">
</div>
{{Collective\Html\FormFacade::close()}}

