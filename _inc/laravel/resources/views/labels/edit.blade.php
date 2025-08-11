{{ Collective\Html\FormFacade::model($label, array('route' => array('labels.update', $label->id), 'method' => 'PUT')) }}
<div class="modal-body">
    <div class="row">
        <div class="form-group col-12">
            {{ Collective\Html\FormFacade::label('name', __('Label Name'),['class'=>'form-label']) }}
            {{ Collective\Html\FormFacade::text('name', null, array('class' => 'form-control','required'=>'required')) }}
        </div>
        <div class="form-group col-12">
            {{ Collective\Html\FormFacade::label('pipeline_id', __('Pipeline'),['class'=>'form-label']) }}
            {{ Collective\Html\FormFacade::select('pipeline_id', $pipelines,null, array('class' => 'form-control select2','required'=>'required')) }}
        </div>
        <div class="form-group col-12">
            {{ Collective\Html\FormFacade::label('color', __('Color'),['class'=>'form-label']) }}
            <div class="row gutters-xs">
                @foreach($colors as $color)
                    <div class="col-auto">
                        <label class="colorinput">
                            <input name="color" type="radio" value="{{$color}}" @if($label->color == $color) checked @endif class="colorinput-input">
                            <span class="colorinput-color bg-{{$color}}"></span>
                        </label>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{__('Cancel')}}" class="btn btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{__('Update')}}" class="btn btn-primary">
</div>
{{Collective\Html\FormFacade::close()}}

