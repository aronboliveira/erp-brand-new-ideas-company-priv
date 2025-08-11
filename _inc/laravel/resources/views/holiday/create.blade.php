{{Collective\Html\FormFacade::open(array('url'=>'holiday','method'=>'post'))}}
<div class="modal-body">
    {{-- start for ai module--}}
    @php
        $plan= \App\Models\Utility::getChatGPTSettings();
    @endphp
    @if($plan->chatgpt == 1)
    <div class="text-end">
        <a href="#" data-size="md" class="btn  btn-primary btn-icon btn-sm" data-ajax-popup-over="true" data-url="{{ route('generate',['holiday']) }}"
           data-bs-placement="top" data-title="{{ __('Generate content with AI') }}">
            <i class="fas fa-robot"></i> <span>{{__('Generate with AI')}}</span>
        </a>
    </div>
    @endif
    {{-- end for ai module--}}
    <div class="row">
        <div class="form-group col-md-12">
            {{Collective\Html\FormFacade::label('occasion',__('Occasion'),['class'=>'form-label'])}}
            {{Collective\Html\FormFacade::text('occasion',null,array('class'=>'form-control'))}}
        </div>
    </div>
    <div class="row">
        <div class="form-group col-md-6">
            {{Collective\Html\FormFacade::label('date',__('Start Date'),['class'=>'form-label'])}}
            {{Collective\Html\FormFacade::date('date',null,array('class'=>'form-control'))}}
        </div>
        <div class="form-group col-md-6">
            {{Collective\Html\FormFacade::label('end_date',__('End Date'),['class'=>'form-label'])}}
            {{Collective\Html\FormFacade::date('end_date',null,array('class'=>'form-control'))}}
        </div>
    </div>
    @if (isset($settings['google_calendar_enable']) && $settings['google_calendar_enable'] == 'on')
        <div class="form-group col-md-6">
            {{Collective\Html\FormFacade::label('synchronize_type',__('Synchronize in Google Calendar ?'),array('class'=>'form-label')) }}
            <div class=" form-switch">
                <input type="checkbox" class="form-check-input mt-2" name="synchronize_type" id="switch-shadow" value="google_calendar">
                <label class="form-check-label" for="switch-shadow"></label>
            </div>
        </div>
    @endif
</div>

<div class="modal-footer">
    <input type="button" value="{{__('Cancel')}}" class="btn btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{__('Create')}}" class="btn btn-primary">
</div>

{{Collective\Html\FormFacade::close()}}

