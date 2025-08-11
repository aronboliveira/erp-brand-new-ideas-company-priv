<div class="card bg-none card-box">
    {{ Collective\Html\FormFacade::model($leadstages, array('route' => array(App\Config\Constants\ViewsConstants::PRJ_STG.'.update', $leadstages->id), 'method' => 'PUT')) }}
    <div class="row">
        <div class="form-group col-12">
            {{ Collective\Html\FormFacade::label('name', __('Project Stage Name'),['class'=>'form-label']) }}
            {{ Collective\Html\FormFacade::text('name', null, array('class' => 'form-control','required'=>'required')) }}
        </div>
        <div class="form-group col-12">
            {{ Collective\Html\FormFacade::label('color', __('Color'),['class'=>'form-label']) }}
            <input class="jscolor form-control " value="{{ $leadstages->color }}" name="color" id="color" required>
            <small class="small">{{ __('For chart representation') }}</small>
        </div>
        <div class="col-12 text-end">
            <input type="submit" value="{{__('Update')}}" class="btn-create badge-blue">
            <input type="button" value="{{__('Cancel')}}" class="btn-create bg-gray" data-dismiss="modal">
        </div>
    </div>
    {{ Collective\Html\FormFacade::close() }}
</div>
