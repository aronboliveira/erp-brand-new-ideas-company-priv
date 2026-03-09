@php
use Modules\LandingPage\Config\Constants\RoutesResourcesConstants;
@endphp
{{Collective\Html\FormFacade::model(null, array('route' => array(RoutesResourcesConstants::DV.'.update', $key), 'method' => 'POST','enctype' => "multipart/form-data")) }}
<div class="modal-body">
    @csrf
    <div class="row">
        <div class="col-md-12">
            <div class="form-group">
                {{ Collective\Html\FormFacade::label('Heading', __('Heading'), ['class' => 'form-label']) }}
                {{ Collective\Html\FormFacade::text('discover_heading',$discover['discover_heading'], ['class' => 'form-control', 'placeholder' => __('Enter Heading')]) }}
            </div>
        </div>

        <div class="col-md-12">
            <div class="form-group">
                {{ Collective\Html\FormFacade::label('Description', __('Description'), ['class' => 'form-label']) }}
                {{ Collective\Html\FormFacade::textarea('discover_description', $discover['discover_description'], ['class' => 'form-control summernote-simple', 'placeholder' => __('Enter Description')]) }}
            </div>
        </div>

        <div class="col-md-12">
            <div class="form-group">
                {{ Collective\Html\FormFacade::label('Logo', __('Logo'), ['class' => 'form-label']) }}
                <input type="file" name="discover_logo" class="form-control">
            </div>
        </div>

    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{__('Cancel')}}" class="btn btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{__('Update')}}" class="btn btn-primary">
</div>
{{ Collective\Html\FormFacade::close() }}
{{--<script>--}}
{{--    tinymce.init({--}}
{{--      selector: '#mytextarea',--}}
{{--      menubar: '',--}}
{{--    });--}}
{{--  </script>--}}
