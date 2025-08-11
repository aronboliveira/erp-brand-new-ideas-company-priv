{{ Collective\Html\FormFacade::open(array('url' => 'email_template','method' =>'post')) }}
<div class="row">
    <div class="form-group col-md-12">
        {{Collective\Html\FormFacade::label('name',__('Name'))}}
        {{Collective\Html\FormFacade::text('name',null,array('class'=>'form-control font-style','required'=>'required'))}}
    </div>
    <div class="form-group col-md-12 text-end">
        {{--        <button type="button" class="btn btn-secondary" data-dismiss="modal">{{__('Cancel')}}</button>--}}
        {{Collective\Html\FormFacade::submit(__('Create'),array('class'=>'btn btn-primary'))}}
    </div>
</div>
{{ Collective\Html\FormFacade::close() }}
