{{Collective\Html\FormFacade::model($complaint,array('route' => array('complaint.update', $complaint->id), 'method' => 'PUT')) }}
<div class="modal-body">
    {{-- start for ai module--}}
    @php
        $plan= \App\Models\Utility::getChatGPTSettings();
    @endphp
    @if($plan->chatgpt == 1)
    <div class="text-end">
        <a href="#" data-size="md" class="btn  btn-primary btn-icon btn-sm" data-ajax-popup-over="true" data-url="{{ route('generate',['complaint']) }}"
           data-bs-placement="top" data-title="{{ __('Generate content with AI') }}">
            <i class="fas fa-robot"></i> <span>{{__('Generate with AI')}}</span>
        </a>
    </div>
    @endif
    {{-- end for ai module--}}
    <div class="row">
        @if(\Auth::user()->type !='employee')
            <div class="form-group col-md-6 col-lg-6">
                {{ Collective\Html\FormFacade::label('complaint_from', __('Complaint From'),['class'=>'form-label'])}}
                {{ Collective\Html\FormFacade::select('complaint_from', $employees,null, array('class' => 'form-control  select','required'=>'required')) }}
            </div>
        @endif
        <div class="form-group col-md-6 col-lg-6">
            {{Collective\Html\FormFacade::label('complaint_against',__('Complaint Against'),['class'=>'form-label'])}}
            {{Collective\Html\FormFacade::select('complaint_against',$employees,null,array('class'=>'form-control select'))}}
        </div>
        <div class="form-group col-md-6 col-lg-6">
            {{Collective\Html\FormFacade::label('title',__('Title'),['class'=>'form-label'])}}
            {{Collective\Html\FormFacade::text('title',null,array('class'=>'form-control'))}}
        </div>
        <div class="form-group col-md-6 col-lg-6">
            {{Collective\Html\FormFacade::label('complaint_date',__('Complaint Date'),['class'=>'form-label'])}}
            {{Collective\Html\FormFacade::date('complaint_date',null,array('class'=>'form-control'))}}
        </div>
        <div class="form-group col-md-12">
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

