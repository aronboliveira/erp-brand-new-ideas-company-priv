{{Collective\Html\FormFacade::model($termination,array('route' => array('termination.update', $termination->id), 'method' => 'PUT')) }}
<div class="modal-body">
    {{-- start for ai module--}}
    @php
        $plan= \App\Models\Utility::getChatGPTSettings();
    @endphp
    @if($plan->chatgpt == 1)
    <div class="text-end">
        <a href="#" data-size="md" class="btn  btn-primary btn-icon btn-sm" data-ajax-popup-over="true" data-url="{{ route('generate',['termination']) }}"
           data-bs-placement="top" data-title="{{ __('Generate content with AI') }}">
            <i class="fas fa-robot"></i> <span>{{__('Generate with AI')}}</span>
        </a>
    </div>
    @endif
    {{-- end for ai module--}}
    <div class="row">
        <div class="form-group  col-lg-6 col-md-6">
            {{ Collective\Html\FormFacade::label('employee_id', __('Employee'),['class'=>'form-label'])}}
            {{ Collective\Html\FormFacade::select('employee_id', $employees,null, array('class' => 'form-control select','required'=>'required')) }}
        </div>
        <div class="form-group  col-lg-6 col-md-6">
            {{ Collective\Html\FormFacade::label('termination_type', __('Termination Type'),['class'=>'form-label']) }}
            {{ Collective\Html\FormFacade::select('termination_type', $terminationtypes,null, array('class' => 'form-control select','required'=>'required')) }}
        </div>
        <div class="form-group  col-lg-6 col-md-6">
            {{Collective\Html\FormFacade::label('notice_date',__('Notice Date'),['class'=>'form-label'])}}
            {{Collective\Html\FormFacade::date('notice_date',null,array('class'=>'form-control'))}}
        </div>
        <div class="form-group  col-lg-6 col-md-6">
            {{Collective\Html\FormFacade::label('termination_date',__('Termination Date'),['class'=>'form-label'])}}
            {{Collective\Html\FormFacade::date('termination_date',null,array('class'=>'form-control'))}}
        </div>
        <div class="form-group  col-lg-12">
            {{Collective\Html\FormFacade::label('description',__('Description'),['class'=>'form-label'])}}
            {{Collective\Html\FormFacade::textarea('description',null,array('class'=>'form-control','placeholder'=>__('Enter Description')))}}
        </div>

    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{__('Cancel')}}" class="btn btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{__('Update')}}" class="btn btn-primary">
</div>

    {{Collective\Html\FormFacade::close()}}
