{{ Collective\Html\FormFacade::model($goal, array('route' => array('goal.update', $goal->id), 'method' => 'PUT')) }}
<div class="modal-body">
     <div class="row">
        <div class="form-group col-md-6">
            {{ Collective\Html\FormFacade::label('name', __('Name'),['class'=>'form-label']) }}
            {{ Collective\Html\FormFacade::text('name', null, array('class' => 'form-control','required'=>'required')) }}
        </div>
        <div class="form-group col-md-6">
            {{ Collective\Html\FormFacade::label('amount', __('Amount'),['class'=>'form-label']) }}
            {{ Collective\Html\FormFacade::number('amount', null, array('class' => 'form-control','required'=>'required','step'=>'0.01')) }}
        </div>
        <div class="form-group  col-md-12">
            {{ Collective\Html\FormFacade::label('type', __('Type'),['class'=>'form-label']) }}
            {{ Collective\Html\FormFacade::select('type',$types,null, array('class' => 'form-control select','required'=>'required')) }}
        </div>
        <div class="form-group  col-md-6">
            {{ Collective\Html\FormFacade::label('from', __('From'),['class'=>'form-label']) }}
            {{Collective\Html\FormFacade::date('from',null,array('class'=>'form-control','required'=>'required'))}}
        </div>
        <div class="form-group  col-md-6">
            {{ Collective\Html\FormFacade::label('to', __('To'),['class'=>'form-label']) }}
            {{Collective\Html\FormFacade::date('to',null,array('class'=>'form-control','required'=>'required'))}}
        </div>
        <div class="form-group col-md-12">
            <input class="form-check-input" type="checkbox" name="is_display" id="is_display" {{$goal->is_display==1?'checked':''}}>
            <label class="custom-control-label form-label" for="is_display">{{__('Display On Dashboard')}}</label>
        </div>

    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{__('Cancel')}}" class="btn btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{__('Update')}}" class="btn btn-primary">
</div>
{{ Collective\Html\FormFacade::close() }}
