@php

@endphp
{{ Collective\Html\FormFacade::open(array('route' => RoutesResourcesConstants::FQ.'.store', 'method'=>'post', 'enctype' => "multipart/form-data")) }}
    <div class="modal-body">
        @csrf
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    {{ Collective\Html\FormFacade::label('question', __('Question'), ['class' => 'form-label']) }}
                    {{ Collective\Html\FormFacade::text('faq_questions',null, ['class' => 'form-control', 'placeholder' => __('Enter Question')]) }}
                </div>
            </div>

            <div class="col-md-12">
                <div class="form-group">
                    {{ Collective\Html\FormFacade::label('answer', __('Answer'), ['class' => 'form-label']) }}
                    {{ Collective\Html\FormFacade::textarea('faq_answer', null, ['class' => 'form-control summernote-simple', 'placeholder' => __('Enter Answer')]) }}
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
