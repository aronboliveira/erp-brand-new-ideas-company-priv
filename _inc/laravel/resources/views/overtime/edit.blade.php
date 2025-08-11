{{Collective\Html\FormFacade::model($overtime,array('route' => array('overtime.update', $overtime->id), 'method' => 'PUT')) }}
<div class="modal-body">

    <div class="card-body p-0">
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    {{ Collective\Html\FormFacade::label('title', __('Title'),['class'=>'form-label']) }}
                    {{ Collective\Html\FormFacade::text('title',null, array('class' => 'form-control','required'=>'required')) }}
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    {{ Collective\Html\FormFacade::label('number_of_days', __('Number Of Days'),['class'=>'form-label']) }}
                    {{ Collective\Html\FormFacade::text('number_of_days',null, array('class' => 'form-control','required'=>'required')) }}
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    {{ Collective\Html\FormFacade::label('hours', __('Hours'),['class'=>'form-label']) }}
                    {{ Collective\Html\FormFacade::text('hours',null, array('class' => 'form-control','required'=>'required')) }}
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    {{ Collective\Html\FormFacade::label('rate', __('Rate'),['class'=>'form-label']) }}
                    {{ Collective\Html\FormFacade::number('rate',null, array('class' => 'form-control','required'=>'required')) }}
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


