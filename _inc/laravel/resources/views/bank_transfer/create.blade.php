{{ Collective\Html\FormFacade::open(array('url' => 'bank-transfer')) }}
<div class="modal-body">

    <div class="row">
        <div class="form-group  col-md-6">
            {{ Collective\Html\FormFacade::label('from_account', __('From Account'),['class'=>'form-label']) }}
            {{ Collective\Html\FormFacade::select('from_account', $bankAccount,null, array('class' => 'form-control select','required'=>'required')) }}
        </div>
        <div class="form-group  col-md-6">
            {{ Collective\Html\FormFacade::label('to_account', __('To Account'),['class'=>'form-label']) }}
            {{ Collective\Html\FormFacade::select('to_account', $bankAccount,null, array('class' => 'form-control select','required'=>'required')) }}
        </div>
        <div class="form-group  col-md-6">
            {{ Collective\Html\FormFacade::label('amount', __('Amount'),['class'=>'form-label']) }}
            {{ Collective\Html\FormFacade::number('amount', '', array('class' => 'form-control','required'=>'required','step'=>'0.01')) }}
        </div>
        <div class="form-group  col-md-6">
            {{ Collective\Html\FormFacade::label('date', __('Date'),['class'=>'form-label']) }}
            {{Collective\Html\FormFacade::date('date',null,array('class'=>'form-control','required'=>'required'))}}
        </div>
        <div class="form-group  col-md-6">
            {{ Collective\Html\FormFacade::label('reference', __('Reference'),['class'=>'form-label']) }}
            {{ Collective\Html\FormFacade::text('reference', '', array('class' => 'form-control')) }}
        </div>
        <div class="form-group  col-md-12">
            {{ Collective\Html\FormFacade::label('description', __('Description'),['class'=>'form-label']) }}
            {{ Collective\Html\FormFacade::textarea('description', '', array('class' => 'form-control','rows'=>3)) }}
        </div>

    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{__('Cancel')}}" class="btn btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{__('Create')}}" class="btn btn-primary">
</div>
{{ Collective\Html\FormFacade::close() }}
