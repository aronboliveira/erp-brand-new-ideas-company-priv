{{Collective\Html\FormFacade::open(array('url'=>'loan','method'=>'post'))}}
{{ Collective\Html\FormFacade::hidden('employee_id',$employee->id, array()) }}
<div class="modal-body">
    <div class="row">
        <div class="form-group col-md-6">
            {{ Collective\Html\FormFacade::label('title', __('Title'),['class'=>'form-label']) }}
            {{ Collective\Html\FormFacade::text('title',null, array('class' => 'form-control','required'=>'required')) }}
        </div>
        <div class="form-group col-md-6">
            {{ Collective\Html\FormFacade::label('loan_option', __('Loan Options'),['class'=>'form-label']) }}<span class="text-danger">*</span>
            {{ Collective\Html\FormFacade::select('loan_option',$loan_options,null, array('class' => 'form-control select','required'=>'required')) }}
        </div>
        <div class="form-group col-md-6">
            {{ Collective\Html\FormFacade::label('type', __('Type'), ['class' => 'form-label']) }}
            {{ Collective\Html\FormFacade::select('type', $loan, null, ['class' => 'form-control select amount_type', 'required' => 'required']) }}
        </div>

        <div class="form-group col-md-6">
            {{ Collective\Html\FormFacade::label('amount', __('Loan Amount'),['class'=>'form-label amount_label']) }}
            {{ Collective\Html\FormFacade::number('amount',null, array('class' => 'form-control','required'=>'required','step'=>'0.01')) }}
        </div>
{{--        <div class="form-group col-md-6">--}}
{{--            {{ Collective\Html\FormFacade::label('start_date', __('Start Date'),['class'=>'form-label']) }}--}}
{{--            {{ Collective\Html\FormFacade::date('start_date',null, array('class' => 'form-control','required'=>'required')) }}--}}
{{--        </div>--}}
{{--        <div class="form-group col-md-6">--}}
{{--            {{ Collective\Html\FormFacade::label('end_date', __('End Date'),['class'=>'form-label']) }}--}}
{{--            {{ Collective\Html\FormFacade::date('end_date',null, array('class' => 'form-control','required'=>'required')) }}--}}
{{--        </div>--}}

        <div class="col-md-12">
            <div class="form-group">
                {{ Collective\Html\FormFacade::label('reason', __('Reason')) }}
                {{ Collective\Html\FormFacade::textarea('reason',null, array('class' => 'form-control','required'=>'required','rows' => 3)) }}
            </div>
        </div>

    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{__('Cancel')}}" class="btn btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{__('Create')}}" class="btn btn-primary">
</div>
{{ Collective\Html\FormFacade::close() }}
