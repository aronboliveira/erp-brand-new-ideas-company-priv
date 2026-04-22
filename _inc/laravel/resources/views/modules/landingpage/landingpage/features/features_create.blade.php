{{ Collective\Html\FormFacade::open(array('route' => 'features.store', 'method'=>'post', 'enctype' => "multipart/form-data")) }}
    <div class="modal-body">
        @csrf
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    {{ Collective\Html\FormFacade::label('Heading', __('Heading'), ['class' => 'form-label']) }}
                    {{ Collective\Html\FormFacade::text('other_features_heading',null, ['class' => 'form-control', 'placeholder' => __('Enter Heading')]) }}
                </div>
            </div>

            <div class="col-md-12">
                <div class="form-group">
                    {{ Collective\Html\FormFacade::label('Description', __('Description'), ['class' => 'form-label']) }}
                    {{ Collective\Html\FormFacade::textarea('other_featured_description', null, ['class' => 'form-control summernote-simple', 'placeholder' => __('Enter Description')]) }}
                </div>
            </div>

            <div class="col-md-12">
                <div class="form-group">
                    {{ Collective\Html\FormFacade::label('Buy Now Link', __('Buy Now Link'), ['class' => 'form-label']) }}
                    {{ Collective\Html\FormFacade::text('other_feature_buy_now_link', null, ['class' => 'form-control', 'placeholder' => __('Enter Link')]) }}
                </div>
            </div>

            <div class="col-md-12">
                <div class="form-group">
                    {{ Collective\Html\FormFacade::label('Image', __('Image'), ['class' => 'form-label']) }}
                    <input type="file" name="other_features_image" class="form-control" required="required">
                </div>
            </div>

        </div>
    </div>
    <div class="modal-footer">
        <input type="button" value="{{__('Cancel')}}" class="btn btn-light" data-bs-dismiss="modal">
        <input type="submit" value="{{__('Create')}}" class="btn btn-primary">
    </div>
{{ Collective\Html\FormFacade::close() }}

{{--<script>--}}
{{--    tinymce.init({--}}
{{--      selector: '#mytextarea',--}}
{{--      menubar: '',--}}
{{--    });--}}
{{--  </script>--}}
