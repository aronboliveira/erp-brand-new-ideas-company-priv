@php
    use App\Config\Constants\{ActivitiesConstants, ProjectsConstants};
@endphp
{{ Collective\Html\FormFacade::open(array('route' => array(ViewsConstants::PRJ_TSK_BUG . '.store',$project_id))) }}
<div class="modal-body">
    {{-- start for ai module--}}
    @php
                            $user = \App\Models\User::find(\Auth::user()->creatorId());
                    $plan= \App\Models\Plan::getPlan($user?->plan);
    @endphp
    @if($plan->chatgpt == 1)
    <div class="text-end">
        <a href="#" data-size="md" class="btn btn-primary btn-icon btn-sm" data-ajax-popup-over="true" data-url="{{ route('generate',['project bug']) }}"
           data-bs-placement="top" data-title="{{ __('Generate content with AI') }}">
            <i class="fas fa-robot"></i> <span>{{__('Generate with AI')}}</span>
        </a>
    </div>
    @endif
    {{-- end for ai module--}}
    <div class="row">
        <div class="form-group col-md-6">
            {{ Collective\Html\FormFacade::label(ActivitiesConstants::COL_TT, __('Title'),['class'=>'form-label']) }}
            {{ Collective\Html\FormFacade::text(ActivitiesConstants::COL_TT, '', array('class' => 'form-control','required'=>'required')) }}
        </div>
        <div class="form-group col-md-6">
            {{ Collective\Html\FormFacade::label(ProjectsConstants::COL_PRT, __('Priority'),['class'=>'form-label']) }}
            {!! Collective\Html\FormFacade::select(ProjectsConstants::COL_PRT, $priority, null,array('class' => 'form-control select','required'=>'required')) !!}
        </div>
        <div class="form-group  col-md-6">
            {{ Collective\Html\FormFacade::label(ProjectsConstants::COL_S_DT, __('Start Date'),['class'=>'form-label']) }}
            {{ Collective\Html\FormFacade::date(ProjectsConstants::COL_S_DT, '', array('class' => 'form-control','required'=>'required')) }}
        </div>
        <div class="form-group  col-md-6">
            {{ Collective\Html\FormFacade::label(ProjectsConstants::COL_D_DATE, __('Due Date'),['class'=>'form-label']) }}
            {{ Collective\Html\FormFacade::date(ProjectsConstants::COL_D_DATE, '', array('class' => 'form-control','required'=>'required')) }}
        </div>
        <div class="form-group col-md-6">
            {{ Collective\Html\FormFacade::label(ActivitiesConstants::COL_TSK_STT, __('Bug Status'),['class'=>'form-label']) }}
            {!! Collective\Html\FormFacade::select(ActivitiesConstants::COL_TSK_STT, $status, null,array('class' => 'form-control select','required'=>'required')) !!}
        </div>
        <div class="form-group col-md-6">
            {{ Collective\Html\FormFacade::label(ProjectsConstants::COL_ASGN, __('Assigned To'),['class'=>'form-label']) }}
            {{ Collective\Html\FormFacade::select(ProjectsConstants::COL_ASGN, $users, null,array('class' => 'form-control select','required'=>'required')) }}
        </div>
    </div>
    <div class="row">
        <div class="form-group  col-md-12">
            {{ Collective\Html\FormFacade::label(ActivitiesConstants::COL_DESC, __('Description'),['class'=>'form-label']) }}
            {!! Collective\Html\FormFacade::textarea(ActivitiesConstants::COL_DESC, null, ['class'=>'form-control','rows'=>'2']) !!}
        </div>
    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{__('Cancel')}}" class="btn btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{__('Create')}}" class="btn btn-primary">
</div>
{{Collective\Html\FormFacade::close()}}
