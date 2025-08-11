{{ Collective\Html\FormFacade::open(array('route' => ['leads.emails.store',$lead->id])) }}
<div class="modal-body">
    <div class="row">
        <div class="col-6 form-group">
            {{ Collective\Html\FormFacade::label('to', __('Mail To'),['class'=>'form-label']) }}
            {{ Collective\Html\FormFacade::email('to', null, array('class' => 'form-control','required'=>'required')) }}
        </div>
        <div class="col-6 form-group">
            {{ Collective\Html\FormFacade::label('subject', __('Subject'),['class'=>'form-label']) }}
            {{ Collective\Html\FormFacade::text('subject', null, array('class' => 'form-control','required'=>'required')) }}
        </div>
        <div class="col-12 form-group">
            {{ Collective\Html\FormFacade::label('description', __('Description'),['class'=>'form-label']) }}
        {{ Collective\Html\FormFacade::textarea('description', null, array('class' => 'summernote-simple')) }}
        </div>
        <script>
            $('#emails-summernote').summernote();
        </script>

    </div>
</div>

<div class="modal-footer">
    <input type="button" value="{{__('Cancel')}}" class="btn btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{__('Create')}}" class="btn btn-primary">
</div>

{{Collective\Html\FormFacade::close()}}
