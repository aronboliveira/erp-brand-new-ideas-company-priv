@php
    use App\Config\Constants\ViewClassNamesConstants;
@endphp
{{ Collective\Html\FormFacade::model($formField, array('route' => array('form.bind.store', $form->id))) }}
<div class="modal-body">
    <div class="row">
        <div class="col-12 pb-3">
            <span class="text-xs"><b>{{__('It will auto convert from response on lead based on below setting. It will not convert old response.')}}</b></span>
        </div>
    </div>
    <div class="row px-2">
        <div class="col-4">
            <div class="form-group">
                {{ Collective\Html\FormFacade::label('active', __('Active'),['class'=>'form-label']) }}
            </div>
        </div>
        <div class="col-8">
            <div class="d-flex radio-check">
                <div class="{{ ViewClassNamesConstants::FM_CHK_IL }}">
                    <input type="radio" id="on" value="1" name="is_lead_active" class="form-check-input lead_radio" {{($form->is_lead_active == 1) ? 'checked' : ''}}>
                    <label class="custom-control-label form-label" for="on">{{__('On')}}</label>
                </div>
                <div class="{{ ViewClassNamesConstants::FM_CHK_IL }}">
                    <input type="radio" id="off" value="0" name="is_lead_active" class="form-check-input lead_radio" {{($form->is_lead_active == 0) ? 'checked' : ''}}>
                    <label class="custom-control-label form-label" for="off">{{__('Off')}}</label>
                </div>
            </div>
        </div>
    </div>
    <div id="lead_activated" class="d-none">
        <div class="row px-2">
            <div class="col-4">
                <div class="form-group">
                     {{Collective\Html\FormFacade::label('subject_id', __('Subject'),['class'=>'form-label']) }}
                </div>
            </div>
            <div class="col-8">
                <div class="form-group">
                    {{ Collective\Html\FormFacade::select('subject_id', $types,null, array('class' => 'form-control','data-toggle'=>'select')) }}
                </div>
            </div>
            <div class="col-4">
                <div class="form-group">
                    {{ Collective\Html\FormFacade::label('name_id', __('Name'),['class'=>'form-label']) }}
                </div>
            </div>
            <div class="col-8">
                <div class="form-group">
                    {{ Collective\Html\FormFacade::select('name_id', $types,null, array('class' => 'form-control','data-toggle'=>'select')) }}
                </div>
            </div>
            <div class="col-4 form-group">
                {{ Collective\Html\FormFacade::label('email_id', __('Email'),['class'=>'form-label']) }}
            </div>
            <div class="col-8">
                <div class="form-group">
                    {{ Collective\Html\FormFacade::select('email_id', $types,null, array('class' => 'form-control','data-toggle'=>'select')) }}
                </div>
                {{ Collective\Html\FormFacade::hidden('form_id',$form->id) }}
                {{ Collective\Html\FormFacade::hidden('form_response_id',(!empty($formField))? $formField->id : '') }}
            </div>
            <div class="col-4 form-group">
                {{ Collective\Html\FormFacade::label('user_id', __('User'),['class'=>'form-label']) }}
            </div>
            <div class="col-8">
                <div class="form-group">
                    {{ Collective\Html\FormFacade::select('user_id', $users,null, array('class' => 'form-control','data-toggle'=>'select')) }}
                    @if(count($users) == 0)
                        <div class="text-muted text-xs">
                            {{__('Please create new employee')}} <a href="{{ route('employee.index') }}" >{{__('here')}}</a>.
                        </div>
                    @endif
                </div>
            </div>
            <div class="col-4 form-group">
                {{ Collective\Html\FormFacade::label('pipeline_id', __('Pipelines'),['class'=>'form-label']) }}
            </div>
            <div class="col-8">
                <div class="form-group">
                    {{ Collective\Html\FormFacade::select('pipeline_id', $pipelines,null, array('class' => 'form-control','data-toggle'=>'select')) }}
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{__('Cancel')}}" class="btn btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{__('Create')}}" class="btn btn-primary">
</div>
{{Collective\Html\FormFacade::close()}}


<script>
    $(document).ready(function () {
        var lead_active = {{$form->is_lead_active}};
        if (lead_active == 1) {
            $('#lead_activated').removeClass('d-none');
        }
    });
    $(document).on('click', function () {
        $('.lead_radio').on('click', function () {
            var inputValue = $(this).attr("value");
            if (inputValue == 1) {
                $('#lead_activated').removeClass('d-none');
            } else {
                $('#lead_activated').addClass('d-none');
            }
            $('.lead_radio').removeAttr('checked');
            $(this).prop("checked", true);
        })
    });
</script>
