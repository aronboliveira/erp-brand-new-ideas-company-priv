@php
    $testimonial ??= [];
    $key ??= '';
@endphp
{{Collective\Html\FormFacade::model(null, array('route' => array('testimonials_update', $key), 'method' => 'POST','enctype' => "multipart/form-data")) }}
    <div class="modal-body">
        @csrf
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    {{ Collective\Html\FormFacade::label('Title', __('Title'), ['class' => 'form-label']) }}
                    {{ Collective\Html\FormFacade::text(LPSC::TM_TTL_K,$testimonial[LPSC::TM_TTL_K], ['class' => 'form-control', 'placeholder' => __('Enter Title')]) }}
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group">
                    {{ Collective\Html\FormFacade::label('Star', __('Star'), ['class' => 'form-label']) }}
                    {{ Collective\Html\FormFacade::number(LPSC::TM_STR ?? 5,$testimonial[LPSC::TM_STR ?? 5], ['class' => 'form-control', 'min'=>'1', 'max'=>'5','required'=>'required', 'placeholder' => __('Enter Star')]) }}
                </div>
            </div>

            <div class="col-md-12">
                <div class="form-group">
                    {{ Collective\Html\FormFacade::label('Description', __('Description'), ['class' => 'form-label']) }}
                    {{ Collective\Html\FormFacade::textarea(LPSC::TM_DESC_K, $testimonial[LPSC::TM_DESC_K], ['class' => 'form-control', 'placeholder' => __('Enter Description')]) }}
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    {{ Collective\Html\FormFacade::label('User', __('User'), ['class' => 'form-label']) }}
                    {{ Collective\Html\FormFacade::text(LPSC::TM_USR ?? 'testimonials_user', data_get($testimonial, LPSC::TM_USR, 'Anonymous'), ['class' => 'form-control', 'placeholder' => __('Enter User Name')]) }}
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    {{ Collective\Html\FormFacade::label('Designation', __('Designation'), ['class' => 'form-label']) }}
                    {{ Collective\Html\FormFacade::text(LPSC::TM_USR_DSG ?? 'Customer',$testimonial[LPSC::TM_USR_DSG] ?? 'Customer', ['class' => 'form-control', 'placeholder' => __('Enter Designation')]) }}
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group">
                    {{ Collective\Html\FormFacade::label('User avatar', __('User avatar'), ['class' => 'form-label']) }}
                    <input type="file" name="testimonials_user_avatar" class="form-control">
                </div>
            </div>

        </div>
    </div>
    <div class="modal-footer">
        <input type="button" value="{{__('Cancel')}}" class="btn btn-light" data-bs-dismiss="modal">
        <input type="submit" value="{{__('Update')}}" class="btn btn-primary">
    </div>

{{ Collective\Html\FormFacade::close() }}
