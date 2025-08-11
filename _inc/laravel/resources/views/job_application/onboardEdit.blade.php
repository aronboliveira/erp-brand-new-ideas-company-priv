{{ Collective\Html\FormFacade::model($jobOnBoard, ['route' => ['job.on.board.update', $jobOnBoard->id], 'method' => 'post']) }}
<div class="modal-body">
    <div class="row">
        <div class="form-group col-md-12">
            {!! Collective\Html\FormFacade::label('joining_date', __('Joining Date'), ['class' => 'col-form-label']) !!}
            {!! Collective\Html\FormFacade::date('joining_date', null, ['class' => 'form-control d_week','autocomplete'=>'off']) !!}
        </div>

        <div class="form-group col-md-6">
            {!! Collective\Html\FormFacade::label('days_of_week', __('Days Of Week'), ['class' => 'col-form-label']) !!}
            {!! Collective\Html\FormFacade::text('days_of_week', null, ['class' => 'form-control','autocomplete'=>'off']) !!}
        </div>
        <div class="form-group col-md-6">
            {!! Collective\Html\FormFacade::label('salary', __('Salary'), ['class' => 'col-form-label']) !!}
            {!! Collective\Html\FormFacade::text('salary', null, ['class' => 'form-control','autocomplete'=>'off']) !!}
        </div>
        <div class="form-group col-md-6">
            {{ Collective\Html\FormFacade::label('salary_type', __('Salary Type'), ['class' => 'col-form-label']) }}
            {{ Collective\Html\FormFacade::select('salary_type', $salary_type, null, ['class' => 'form-control select']) }}
        </div>
        <div class="form-group col-md-6">
            {{ Collective\Html\FormFacade::label('salary_duration', __('Salary Type'), ['class' => 'col-form-label']) }}
            {{ Collective\Html\FormFacade::select('salary_duration', $salary_duration, null, ['class' => 'form-control select']) }}
        </div>
        <div class="form-group col-md-6">
            {{ Collective\Html\FormFacade::label('jop_type', __('Job Type'), ['class' => 'col-form-label']) }}
            {{ Collective\Html\FormFacade::select('job_type', $job_type, null, ['class' => 'form-control select']) }}
        </div>
        <div class="form-group col-md-6">
            {{ Collective\Html\FormFacade::label('status', __('Status'), ['class' => 'col-form-label']) }}
            {{ Collective\Html\FormFacade::select('status', $status, null, ['class' => 'form-control select']) }}
        </div>
    </div>
</div>
{{-- <div class="col-12">
    <input type="submit" value="{{ __('Update') }}" class="btn-create badge-blue">
    <input type="button" value="{{ __('Cancel') }}" class="btn-create bg-gray" data-dismiss="modal">
</div> --}}
<div class="modal-footer">
    <input type="button" value="Cancel" class="btn btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{ __('Update') }}" class="btn btn-primary">
</div>

{{ Collective\Html\FormFacade::close() }}
