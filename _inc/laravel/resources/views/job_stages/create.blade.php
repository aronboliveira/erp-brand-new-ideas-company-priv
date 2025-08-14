    {{Collective\Html\FormFacade::open(array('url'=>'job-stage','method'=>'post'))}}
    <div class="modal-body">

    <div class="row">
        <div class="col-md-12">
            <div class="form-group">
                {{Collective\Html\FormFacade::label('title',__('Title'),['class'=>'form-label'])}}
                {{Collective\Html\FormFacade::text('title',null,array('class'=>'form-control','placeholder'=>__('Enter stage title')))}}
            </div>
        </div>

    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{__('Cancel')}}" class="btn btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{__('Create')}}" class="btn btn-primary">
</div>
    {{Collective\Html\FormFacade::close()}}

