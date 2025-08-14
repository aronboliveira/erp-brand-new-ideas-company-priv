{{Collective\Html\FormFacade::model($loan,array('route' => array('loan.update', $loan->id), 'method' => 'PUT')) }}
<div class="modal-body">
    <div class="card-body p-0">
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    {{ Collective\Html\FormFacade::label('title', __('Title')) }}
                    {{ Collective\Html\FormFacade::text('title',null, array('class' => 'form-control','required'=>'required')) }}
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    {{ Collective\Html\FormFacade::label('loan_option', __('Loan Options')) }}<span class="text-danger">*</span>
                    {{ Collective\Html\FormFacade::select('loan_option',$loan_options,null, array('class' => 'form-control select','required'=>'required')) }}
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    {{ Collective\Html\FormFacade::label('type', __('Type'), ['class' => 'form-label']) }}
                    {{ Collective\Html\FormFacade::select('type', $loans, null, ['class' => 'form-control select amount_type', 'required' => 'required']) }}
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    {{ Collective\Html\FormFacade::label('amount', __('Loan Amount'),['class'=>'form-label amount_label']) }}
                    {{ Collective\Html\FormFacade::number('amount',null, array('class' => 'form-control','required'=>'required')) }}
                </div>
            </div>
{{--            <div class="col-md-6">--}}
{{--                <div class="form-group">--}}
{{--                    {{ Collective\Html\FormFacade::label('start_date', __('Start Date')) }}--}}
{{--                    {{ Collective\Html\FormFacade::date('start_date',null, array('class' => 'form-control','required'=>'required')) }}--}}
{{--                </div>--}}
{{--            </div>--}}
{{--            <div class="col-md-6">--}}
{{--                <div class="form-group">--}}
{{--                    {{ Collective\Html\FormFacade::label('end_date', __('End Date')) }}--}}
{{--                    {{ Collective\Html\FormFacade::date('end_date',null, array('class' => 'form-control','required'=>'required')) }}--}}
{{--                </div>--}}
{{--            </div>--}}
            <div class="col-md-12">
                <div class="form-group">
                    {{ Collective\Html\FormFacade::label('reason', __('Reason')) }}
                    {{ Collective\Html\FormFacade::textarea('reason',null, array('class' => 'form-control','required'=>'required','rows' => 3)) }}
                </div>
            </div>
        </div>

    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{__('Cancel')}}" class="btn btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{__('Update')}}" class="btn btn-primary">
</div>
{{Collective\Html\FormFacade::close()}}
