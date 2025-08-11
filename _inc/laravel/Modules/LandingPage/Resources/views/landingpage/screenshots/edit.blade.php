@php
    use Modules\LandingPage\Constants\RoutesResourcesConstants;
@endphp
{{Collective\Html\FormFacade::model(null, array('route' => array(RoutesResourcesConstants::SST.'.update', $key), 'method' => 'POST','enctype' => "multipart/form-data")) }}
<div class="modal-body">
    @csrf
    <div class="row">
        <div class="col-md-12">
            <div class="form-group">
                {{ Collective\Html\FormFacade::label('Heading', __('Heading'), ['class' => 'form-label']) }}
                {{ Collective\Html\FormFacade::text('screenshots_heading',$screenshot['screenshots_heading'], ['class' => 'form-control', 'placeholder' => __('Enter Heading')]) }}
            </div>
        </div>

        <div class="col-md-12">
            <div class="form-group">
                {{ Collective\Html\FormFacade::label('screenshot', __('Screenshot'), ['class' => 'form-label']) }}
                <input type="file" name="screenshots" class="form-control">
            </div>
        </div>

    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{__('Cancel')}}" class="btn btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{__('Update')}}" class="btn btn-primary">
</div>
{{ Collective\Html\FormFacade::close() }}
