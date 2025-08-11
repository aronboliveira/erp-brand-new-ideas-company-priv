<div class="card bg-none card-box">
    {{ Collective\Html\FormFacade::open(array('url' => 'estimations')) }}
    <div class="row">
        <div class="col-6 form-group">
            {{ Collective\Html\FormFacade::label('client_id', __('Client'),['class'=>'form-label']) }}
            {{ Collective\Html\FormFacade::select('client_id', $client,null, array('class' => 'form-control select2','required'=>'required')) }}
        </div>
        <div class="col-6 form-group">
            {{ Collective\Html\FormFacade::label('issue_date', __('Issue Date'),['class'=>'form-label']) }}
            {{ Collective\Html\FormFacade::text('issue_date',null, array('class' => 'form-control datepicker','required'=>'required')) }}
        </div>
        <div class="col-6 form-group">
            {{ Collective\Html\FormFacade::label('tax_id', __('Tax %'),['class'=>'form-label']) }}
            {{ Collective\Html\FormFacade::select('tax_id', $taxes,null, array('class' => 'form-control select2','required'=>'required')) }}
            @if(count($taxes) <= 0)
                <div class="text-muted text-xs">
                    {{__('Please create new Tax')}} <a href="{{route('taxes.index')}}">{{__('here')}}</a>.
                </div>
            @endif
        </div>
        <div class="col-12 form-group">
            {{ Collective\Html\FormFacade::label('terms', __('Terms'),['class'=>'form-label']) }}
            {{ Collective\Html\FormFacade::textarea('terms',null, array('class' => 'form-control')) }}
        </div>
        <div class="col-12 text-end">
            <input type="submit" value="{{__('Create')}}" class="btn-create badge-blue">
            <input type="button" value="{{__('Cancel')}}" class="btn-create bg-gray" data-dismiss="modal">
        </div>
    </div>
    {{ Collective\Html\FormFacade::close() }}
</div>
