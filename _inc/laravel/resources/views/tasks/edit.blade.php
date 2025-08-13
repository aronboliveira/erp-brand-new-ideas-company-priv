@php
    use App\Config\Constants\{
        ActivitiesConstants, 
        ProjectsConstants,
        ViewsConstants,
        ViewClassNamesConstants,
    };
@endphp
{{ Collective\Html\FormFacade::model($task, ['route' => [ViewsConstants::PRJ_TSK_C . '.update',[$project->id, $task->id]], 'id' => 'edit_task', 'method' => 'POST']) }}
<div class="row">
    <div class="col-8">
        <div class="form-group">
            {{ Collective\Html\FormFacade::label(ProjectsConstants::COL_NM, __('Task name'),['class' => 'form-label']) }}
            {{ Collective\Html\FormFacade::text(ProjectsConstants::COL_NM, null, ['class' => 'form-control','required'=>'required']) }}
        </div>
    </div>
    <div class="col-4">
        <div class="form-group">
            {{ Collective\Html\FormFacade::label(ProjectsConstants::COL_ML_ID, __('Milestone'),['class' => 'form-label']) }}
            <select class="form-control" name="milestone_id" id="milestone_id">
                <option value="0"></option>
                @foreach($project->milestones as $m_val)
                    <option value="{{ $m_val->id }}" {{ ($task->milestone_id == $m_val->id) ? 'selected':'' }}>{{ $m_val->title }}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="col-12">
        <div class="form-group">
            {{ Collective\Html\FormFacade::label(ActivitiesConstants::COL_DESC, __('Description'),['class' => 'form-label']) }}
            <small class="form-text text-muted mb-2 mt-0">{{__('This textarea will autosize while you type')}}</small>
            {{ Collective\Html\FormFacade::textarea(ActivitiesConstants::COL_DESC, null, ['class' => 'form-control','rows'=>'1','data-toggle' => 'autosize']) }}
        </div>
    </div>
    <div class="col-6">
        <div class="form-group">
            {{ Collective\Html\FormFacade::label(ProjectsConstants::COL_E_HRS, __('Estimated Hours'),['class' => 'form-label']) }}
            <small class="form-text text-muted mb-2 mt-0">{{__('Total hrs of project ').$hrs['total'].__(' & allocated total ').$hrs['allocated'].__(' hrs in other tasks')}}</small>
            {{ Collective\Html\FormFacade::number(ProjectsConstants::COL_E_HRS, null, ['class' => 'form-control','required' => 'required','min'=>'0','maxlength' => '8']) }}
        </div>
    </div>
    <div class="col-6">
        <div class="form-group">
            {{ Collective\Html\FormFacade::label(ProjectsConstants::COL_PRT, __('Priority'),['class' => 'form-label']) }}
            <small class="form-text text-muted mb-2 mt-0">{{__('Set Priority of your task')}}</small>
            <select class="form-control" name="priority" id="priority" required>
                @foreach(\App\Models\ProjectTask::$priority as $key => $val)
                    <option value="{{ $key }}" {{ ($key == $task->priority) ? 'selected' : '' }} >{{ __($val) }}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="col-6">
        <div class="form-group">
            {{ Collective\Html\FormFacade::label(ProjectsConstants::COL_S_DT, __('Start Date'),['class' => 'form-label']) }}
            {{ Collective\Html\FormFacade::date(ProjectsConstants::COL_S_DT, null, ['class' => 'form-control']) }}
        </div>
    </div>
    <div class="col-6">
        <div class="form-group">
            {{ Collective\Html\FormFacade::label(ProjectsConstants::COL_E_DT, __('End Date'),['class' => 'form-label']) }}
            {{ Collective\Html\FormFacade::date(ProjectsConstants::COL_E_DT, null, ['class' => 'form-control']) }}
        </div>
    </div>
</div>

<div class="form-group">
    <label class="form-label">{{__('Task members')}}</label>
    <small class="form-text text-muted mb-2 mt-0">{{__('Below users are assigned in your project.')}}</small>
</div>
<div class="{{ ViewClassNamesConstants::LG_FLSH_MB4 }}">
    <div class="row">
        @foreach($project->users as $user)
            <div class="col-6">
                <div class="list-group-item px-0">
                    <div class="row align-items-center">
                        <div class="col-auto ml-3">
                            <a href="#" class="avatar avatar-sm rounded-circle">
                                <img {{$user?->img_avatar}} />
                            </a>
                        </div>
                        <div class="col ml-n2">
                            <p class="d-block h6 text-sm mb-0">{{ $user?->name }}</p>
                            <p class="card-text text-sm text-muted mb-0">{{ $user?->email }}</p>
                        </div>
                        @php
                            $usrs = explode(',',$task->assign_to);
                        @endphp
                        <div class="col-auto text-end add_usr {{ (in_array($user?->id,$usrs)) ? 'selected':'' }}" data-id="{{ $user?->id }}">
                            <button type="button" class="btn btn-xs btn-animated btn-primary rounded-pill btn-animated-y mr-3">
                                <span class="btn-inner--visible">
                                  <i class="ti ti-{{ (in_array($user?->id,$usrs)) ? 'check' : 'plus' }} " id="usr_icon_{{$user?->id}}"></i>
                                </span>
                                <span class="btn-inner--hidden" id="usr_txt_{{$user?->id}}">{{ (in_array($user?->id,$usrs)) ? __('Added') : __('Add')}}</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
    {{ Collective\Html\FormFacade::hidden(ProjectsConstants::COL_ASGN, null) }}
</div>
<div class="text-end">
    {{ Collective\Html\FormFacade::button(__('Update'), ['type' => 'submit','class' => 'btn btn-sm btn-primary rounded-pill']) }}
</div>
{{ Collective\Html\FormFacade::close() }}
