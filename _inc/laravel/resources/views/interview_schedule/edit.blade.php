    {{Collective\Html\FormFacade::model($interviewSchedule,array('route' => array('interview-schedule.update', $interviewSchedule->id), 'method' => 'PUT')) }}
    <div class="modal-body">

    <div class="row">
        <div class="form-group col-md-6">
            {{Collective\Html\FormFacade::label('candidate',__('Interview To'),['class'=>'form-label'])}}
            {{ Collective\Html\FormFacade::select('candidate', $candidates,null, array('class' => 'form-control select','required'=>'required')) }}
        </div>
        <div class="form-group col-md-6">
            {{Collective\Html\FormFacade::label('employee',__('Interviewer'),['class'=>'form-label'])}}
            {{ Collective\Html\FormFacade::select('employee', $employees,null, array('class' => 'form-control select','required'=>'required')) }}
        </div>
        <div class="form-group col-md-6">
            {{Collective\Html\FormFacade::label('date',__('Interview Date'),['class'=>'form-label'])}}
            {{Collective\Html\FormFacade::date('date',null,array('class'=>'form-control'))}}
        </div>
        <div class="form-group col-md-">
            {{Collective\Html\FormFacade::label('time',__('Interview Time'),['class'=>'form-label'])}}
            {{Collective\Html\FormFacade::time('time',null,array('class'=>'form-control timepicker'))}}
        </div>
        <div class="form-group col-md-12">
            {{Collective\Html\FormFacade::label('comment',__('Comment'),['class'=>'form-label'])}}
            {{Collective\Html\FormFacade::textarea('comment',null,array('class'=>'form-control'))}}
        </div>

    </div>
    </div>
    <div class="modal-footer">
        <input type="button" value="{{__('Cancel')}}" class="btn btn-light" data-bs-dismiss="modal">
        <input type="submit" value="{{__('Update')}}" class="btn btn-primary">
    </div>
    {{Collective\Html\FormFacade::close()}}

