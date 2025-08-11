<div class="card bg-none card-box">
    {{ Collective\Html\FormFacade::model($estimation, array('route' => array('estimations.update', $estimation->id), 'method' => 'PUT')) }}
    <div class="row">
        <div class="col-6 form-group">
            {{ Collective\Html\FormFacade::label('client_id', __('Client'),['class'=>'form-label']) }}
            {{ Collective\Html\FormFacade::select('client_id', $client,null, array('class' => 'form-control select2','required'=>'required')) }}
        </div>
        <div class="col-6 form-group">
            {{ Collective\Html\FormFacade::label('status', __('Status'),['class'=>'form-label']) }}
            {{ Collective\Html\FormFacade::select('status', \App\Models\Estimation::$statuses,null, array('class' => 'form-control select2','required'=>'required')) }}
        </div>
        <div class="col-6 form-group">
            {{ Collective\Html\FormFacade::label('issue_date', __('Issue Date'),['class'=>'form-label']) }}
            {{ Collective\Html\FormFacade::text('issue_date',null, array('class' => 'form-control datepicker','required'=>'required')) }}
        </div>
        <div class="col-6 form-group">
            {{ Collective\Html\FormFacade::label('discount', __('Discount'),['class'=>'form-label']) }}
            {{ Collective\Html\FormFacade::number('discount',null, array('class' => 'form-control','required'=>'required','min'=>"0")) }}
        </div>
        <div class="col-6 form-group">
            {{ Collective\Html\FormFacade::label('tax_id', __('Tax %'),['class'=>'form-label']) }}
            {{ Collective\Html\FormFacade::select('tax_id', $taxes,null, array('class' => 'form-control select2','required'=>'required')) }}
        </div>
        <div class="col-12 form-group">
            {{ Collective\Html\FormFacade::label('terms', __('Terms'),['class'=>'form-label']) }}
            {{ Collective\Html\FormFacade::textarea('terms',null, array('class' => 'form-control')) }}
        </div>
        <div class="col-12 text-end">
            <input type="submit" value="{{__('Update')}}" class="btn-create badge-blue">
            <input type="button" value="{{__('Cancel')}}" class="btn-create bg-gray" data-dismiss="modal">
        </div>
    </div>
    {{ Collective\Html\FormFacade::close() }}
</div>
