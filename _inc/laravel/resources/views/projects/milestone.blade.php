{{ Collective\Html\FormFacade::open(array('route' => array('project.milestone.store',$project->id))) }}
<div class="modal-body">
    {{-- start for ai module--}}
    @php
        $plan= \App\Models\Utility::getChatGPTSettings();
    @endphp
    @if($plan->chatgpt == 1)
    <div class="text-end">
        <a href="#" data-size="md" class="btn  btn-primary btn-icon btn-sm" data-ajax-popup-over="true" data-url="{{ route('generate',['project milestone']) }}"
           data-bs-placement="top" data-title="{{ __('Generate content with AI') }}">
            <i class="fas fa-robot"></i> <span>{{__('Generate with AI')}}</span>
        </a>
    </div>
    @endif
    {{-- end for ai module--}}
    <div class="row">
    <div class="form-group col-md-6">
        {{ Collective\Html\FormFacade::label('title', __('Title'),['class' => 'form-label']) }}
        {{ Collective\Html\FormFacade::text('title', null, array('class' => 'form-control','required'=>'required')) }}
        @error('title')
        <span class="invalid-title" role="alert">
            <strong class="text-danger">{{ $message }}</strong
        </span>
        @enderror
    </div>
    <div class="form-group  col-md-6">
        {{ Collective\Html\FormFacade::label('status', __('Status'),['class' => 'form-label']) }}
        {!! Collective\Html\FormFacade::select('status',\App\Models\Project::$project_status, null,array('class' => 'form-control select','required'=>'required')) !!}
        @error('client')
        <span class="invalid-client" role="alert">
            <strong class="text-danger">{{ $message }}</strong>
        </span>
        @enderror
    </div>
        <div class="form-group  col-md-6">
            {{ Collective\Html\FormFacade::label('start_date', __('Start Date'),['class' => 'col-form-label']) }}
            {{ Collective\Html\FormFacade::date('start_date', '', array('class' => 'form-control','required'=>'required')) }}
        </div>
        <div class="form-group  col-md-6">
            {{ Collective\Html\FormFacade::label('due_date', __('Due Date'),['class' => 'col-form-label']) }}
            {{ Collective\Html\FormFacade::date('due_date', '', array('class' => 'form-control','required'=>'required')) }}
        </div>
        <div class="form-group  col-md-6">
            {{ Collective\Html\FormFacade::label('cost', __('Cost'),['class' => 'col-form-label']) }}
            {{ Collective\Html\FormFacade::number('cost', '', array('class' => 'form-control','required'=>'required','stage'=>'0.01')) }}
        </div>
</div>
    <div class="row">
        <div class="form-group  col-md-12">
        {{ Collective\Html\FormFacade::label('description', __('Description'),['class' => 'form-label']) }}
        {!! Collective\Html\FormFacade::textarea('description', null, ['class'=>'form-control','rows'=>'2']) !!}
        @error('description')
        <span class="invalid-description" role="alert">
            <strong class="text-danger">{{ $message }}</strong>
        </span>
        @enderror
    </div>
    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{__('Cancel')}}" class="btn btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{__('Create')}}" class="btn btn-primary">
</div>
{{ Collective\Html\FormFacade::close() }}
