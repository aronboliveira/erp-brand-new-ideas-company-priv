{{Collective\Html\FormFacade::open(array('url'=>'saturationdeduction','method'=>'post'))}}
<div class="modal-body">

    {{ Collective\Html\FormFacade::hidden('employee_id',$employee->id, array()) }}
    <div class="row">
        <div class="form-group col-md-6">
            {{ Collective\Html\FormFacade::label('deduction_option', __('Deduction Options'),['class'=>'form-label']) }}<span class="text-danger">*</span>
            {{ Collective\Html\FormFacade::select('deduction_option',$deduction_options,null, array('class' => 'form-control select','required'=>'required')) }}
        </div>
        <div class="form-group col-md-6">
            {{ Collective\Html\FormFacade::label('title', __('Title'),['class'=>'form-label']) }}
            {{ Collective\Html\FormFacade::text('title',null, array('class' => 'form-control','required'=>'required')) }}
        </div>
        <div class="form-group col-md-6">
            {{ Collective\Html\FormFacade::label('type', __('Type'), ['class' => 'form-label']) }}
            {{ Collective\Html\FormFacade::select('type', $saturationdeduc, null, ['class' => 'form-control select amount_type', 'required' => 'required']) }}
        </div>

        <div class="form-group col-md-6">
            {{ Collective\Html\FormFacade::label('amount', __('Amount'),['class'=>'form-label amount_label']) }}
            {{ Collective\Html\FormFacade::number('amount',null, array('class' => 'form-control','required'=>'required','step'=>'0.01')) }}
        </div>

    </div>
</div>

<div class="modal-footer">
    <input type="button" value="{{__('Cancel')}}" class="btn btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{__('Create')}}" class="btn btn-primary">
</div>

    {{ Collective\Html\FormFacade::close() }}
