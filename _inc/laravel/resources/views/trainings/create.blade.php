{{Collective\Html\FormFacade::open(array('url'=>'training','method'=>'post'))}}
<div class="modal-body">

    {{-- start for ai module--}}
    @php
        $plan= \App\Models\Utility::getChatGPTSettings();
    @endphp
    @if($plan->chatgpt == 1)
    <div class="text-end">
        <a href="#" data-size="md" class="btn btn-primary btn-icon btn-sm" data-ajax-popup-over="true" data-url="{{ route('generate',['training']) }}"
          data-bs-placement="top"  data-title="{{ __('Generate content with AI') }}">
            <i class="fas fa-robot"></i> <span>{{__('Generate with AI')}}</span>
        </a>
    </div>
    @endif
    {{-- end for ai module--}}

    <div class="row">
        <div class="col-md-12">
            <div class="form-group">
                {{Collective\Html\FormFacade::label('branch',__('Branch'),['class'=>'form-label'])}}
                {{Collective\Html\FormFacade::select('branch',$branches,null,array('class'=>'form-control select','required'=>'required'))}}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{Collective\Html\FormFacade::label('trainer_option',__('Trainer Option'),['class'=>'form-label'])}}
                {{Collective\Html\FormFacade::select('trainer_option',$options,null,array('class'=>'form-control select','required'=>'required'))}}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{Collective\Html\FormFacade::label('training_type',__('Training Type'),['class'=>'form-label'])}}
                {{Collective\Html\FormFacade::select('training_type',$trainingTypes,null,array('class'=>'form-control select','required'=>'required'))}}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{Collective\Html\FormFacade::label('trainer',__('Trainer'),['class'=>'form-label'])}}
                {{Collective\Html\FormFacade::select('trainer',$trainers,null,array('class'=>'form-control select','required'=>'required'))}}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{Collective\Html\FormFacade::label('training_cost',__('Training Cost'),['class'=>'form-label'])}}
                {{Collective\Html\FormFacade::number('training_cost',null,array('class'=>'form-control','step'=>'0.01','required'=>'required'))}}
            </div>
        </div>
        <div class="col-md-12">
            <div class="form-group">
                {{Collective\Html\FormFacade::label('employee',__('Employee'),['class'=>'form-label'])}}
                {{Collective\Html\FormFacade::select('employee',$employees,null,array('class'=>'form-control select','required'=>'required'))}}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{Collective\Html\FormFacade::label('start_date',__('Start Date'),['class'=>'form-label'])}}
                {{Collective\Html\FormFacade::date('start_date',null,array('class'=>'form-control'))}}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{Collective\Html\FormFacade::label('end_date',__('End Date'),['class'=>'form-label'])}}
                {{Collective\Html\FormFacade::date('end_date',null,array('class'=>'form-control'))}}
            </div>
        </div>
        <div class="form-group col-lg-12">
            {{Collective\Html\FormFacade::label('description',__('Description'),['class'=>'form-label'])}}
            {{Collective\Html\FormFacade::textarea('description',null,array('class'=>'form-control','placeholder'=>__('Description')))}}
        </div>


    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{__('Cancel')}}" class="btn btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{__('Create')}}" class="btn btn-primary">
</div>

{{Collective\Html\FormFacade::close()}}
