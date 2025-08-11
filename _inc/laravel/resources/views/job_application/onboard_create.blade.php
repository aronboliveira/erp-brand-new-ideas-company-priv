{{ Collective\Html\FormFacade::open(['route' => ['job.on.board.store', $id], 'method' => 'post']) }}
<div class="modal-body">
    <div class="row">
        @if ($id == 0)
            <div class="form-group col-md-12">
                {{ Collective\Html\FormFacade::label('application', __('Interviewer'), ['class' => 'col-form-label']) }}
                {{ Collective\Html\FormFacade::select('application', $applications, null, ['class' => 'form-control select2', 'required' => 'required']) }}
            </div>
        @endif
        <div class="form-group col-md-12">
            {!! Collective\Html\FormFacade::label('joining_date', __('Joining Date'), ['class' => 'col-form-label']) !!}
            {!! Collective\Html\FormFacade::date('joining_date', null, ['class' => 'form-control','autocomplete'=>'off']) !!}
        </div>

        <div class="form-group col-md-6">
            {!! Collective\Html\FormFacade::label('days_of_week', __('Days Of Week'), ['class' => 'col-form-label']) !!}
            {!! Collective\Html\FormFacade::number('days_of_week', null, ['class' => 'form-control','autocomplete'=>'off','min'=>'0']) !!}
        </div>
        <div class="form-group col-md-6">
            {!! Collective\Html\FormFacade::label('salary', __('Salary'), ['class' => 'col-form-label']) !!}
            {!! Collective\Html\FormFacade::number('salary', null, ['class' => 'form-control','autocomplete'=>'off','min'=>'0']) !!}
        </div>
        <div class="form-group col-md-6">
            {{ Collective\Html\FormFacade::label('salary_type', __('Salary Type'), ['class' => 'col-form-label']) }}
            {{ Collective\Html\FormFacade::select('salary_type', $salary_type, null, ['class' => 'form-control select']) }}
        </div>
        <div class="form-group col-md-6">
            {{ Collective\Html\FormFacade::label('salary_duration', __('Salary Duration'), ['class' => 'col-form-label']) }}
            {{ Collective\Html\FormFacade::select('salary_duration', $salary_duration, null, ['class' => 'form-control select']) }}
        </div>
        <div class="form-group col-md-6">
            {{ Collective\Html\FormFacade::label('job_type', __('Job Type'), ['class' => 'col-form-label']) }}
            {{ Collective\Html\FormFacade::select('job_type', $job_type, null, ['class' => 'form-control select']) }}
        </div>
        <div class="form-group col-md-6">
            {{ Collective\Html\FormFacade::label('status', __('Status'), ['class' => 'col-form-label']) }}
            {{ Collective\Html\FormFacade::select('status', $status, null, ['class' => 'form-control select']) }}
        </div>
    </div>
</div>
<div class="modal-footer">
    <input type="button" value="Cancel" class="btn btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{ __('Create') }}" class="btn btn-primary">
</div>
{{ Collective\Html\FormFacade::close() }}
