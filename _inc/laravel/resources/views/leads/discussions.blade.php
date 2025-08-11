
{{ Collective\Html\FormFacade::model($lead, array('route' => array('leads.discussion.store', $lead->id), 'method' => 'POST')) }}
<div class="modal-body">
    <div class="row">
        <div class="col-12 form-group">
            {{ Collective\Html\FormFacade::label('comment', __('Message'),['class'=>'form-label']) }}
            {{ Collective\Html\FormFacade::textarea('comment', null, array('class' => 'form-control')) }}
        </div>
    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{__('Cancel')}}" class="btn btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{__('Create')}}" class="btn btn-primary">
</div>
{{Collective\Html\FormFacade::close()}}

