@php
    use App\Config\Constants\{
        ActivitiesConstants,
        PlansConstants,
        ProjectsConstants,
        ViewsConstants,
        DatabaseConstants,
        ViewClassNamesConstants as VC
    };
    use App\Models\{Utility, User, Plan};
    use Collective\Html\FormFacade as Form;
    use Illuminate\Support\Facades\{Auth, Route};
    $lang = Utility::fetchUserLang();
    $user = $user ?? Auth::user();
@endphp
{!! Form::open([
    'route'  => [ViewsConstants::PRJ_TSK_BUG . '.store', $project_id],
    'method' => 'POST',
    'id'     => 'create_bug',
]) !!}
    <div class="modal-body">
        @if($user && method_exists($user, 'creatorId'))
            @php
                $planUser = User::find($user->creatorId());
                $plan     = Plan::getPlan($planUser?->plan ?? DatabaseConstants::DEFAULT_PLAN);
            @endphp
            @if($plan?->{PlansConstants::COL_GPT} == 1)
                <div class="float-end">
                    <a href="#"
                       data-size="md"
                       class="btn btn-primary btn-icon btn-sm"
                       data-ajax-popup-over="true"
                       data-url="{{ route('generate', ['project bug']) }}"
                       data-bs-placement="top"
                       data-title="{{ __('Generate content with AI') }}">
                        <i class="{{ VC::FAS_RB }}"></i>
                        <span>{{ __('Generate with AI') }}</span>
                    </a>
                </div>
            @endif
        @endif
        <div class="row">
            <div class="{{ VC::FM_GCB6 }}">
                {{ Form::label(ActivitiesConstants::COL_TT, __('Title'), ['class' => 'form-label']) }}
                {{ Form::text(ActivitiesConstants::COL_TT, null, ['class' => 'form-control', 'required' => 'required']) }}
            </div>

            <div class="{{ VC::FM_GCB6 }}">
                {{ Form::label(ProjectsConstants::COL_PRT, __('Priority'), ['class' => 'form-label']) }}
                {{ Form::select(ProjectsConstants::COL_PRT, $priority, null, ['class' => 'form-control select', 'required' => 'required']) }}
            </div>

            <div class="{{ VC::FM_GCB6 }}">
                {{ Form::label(ProjectsConstants::COL_S_DT, __('Start Date'), ['class' => 'form-label']) }}
                {{ Form::date(ProjectsConstants::COL_S_DT, null, ['class' => 'form-control', 'required' => 'required']) }}
            </div>

            <div class="{{ VC::FM_GCB6 }}">
                {{ Form::label(ProjectsConstants::COL_D_DATE, __('Due Date'), ['class' => 'form-label']) }}
                {{ Form::date(ProjectsConstants::COL_D_DATE, null, ['class' => 'form-control', 'required' => 'required']) }}
            </div>

            <div class="{{ VC::FM_GCB6 }}">
                {{ Form::label(ActivitiesConstants::COL_TSK_STT, __('Bug Status'), ['class' => 'form-label']) }}
                {{ Form::select(ActivitiesConstants::COL_TSK_STT, $status, null, ['class' => 'form-control select', 'required' => 'required']) }}
            </div>

            <div class="{{ VC::FM_GCB6 }}">
                {{ Form::label(ProjectsConstants::COL_ASGN, __('Assigned To'), ['class' => 'form-label']) }}
                {{ Form::select(ProjectsConstants::COL_ASGN, $users, null, ['class' => 'form-control select', 'required' => 'required']) }}
            </div>
        </div>

        <div class="row">
            <div class="{{ VC::FM_GCB12 }}">
                {{ Form::label(ActivitiesConstants::COL_DESC, __('Description'), ['class' => 'form-label']) }}
                {{ Form::textarea(ActivitiesConstants::COL_DESC, null, ['class' => 'form-control', 'rows' => 2]) }}
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <input type="button" value="{{ __('Cancel') }}" class="btn btn-light" data-bs-dismiss="modal">
        <input type="submit" value="{{ __('Create') }}" class="btn btn-primary">
    </div>
{!! Form::close() !!}
