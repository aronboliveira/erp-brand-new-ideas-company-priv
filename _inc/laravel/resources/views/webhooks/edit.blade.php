{{Collective\Html\FormFacade::model($webhooksetting,array('route' => array('webhook.update', $webhooksetting->id), 'method' => 'POST')) }}
<div class="modal-body">

    <div class="row">
        <div class="col-12 form-group">
            {{Collective\Html\FormFacade::label('module',__('Module'),['class'=>'form-label'])}}
            {{Collective\Html\FormFacade::select('module',$modules,null,array('class'=>'form-control select','placeholder'=>__('Select Module')))}}
        </div>
        <div class="col-12 form-group">
            {{Collective\Html\FormFacade::label('url',__('Url'),['class'=>'form-label'])}}
            {{Collective\Html\FormFacade::text('url',null,array('class'=>'form-control','placeholder'=>__('Enter Webhook Url')))}}
            @error('url')
            <span class="invalid-name" role="alert">
                <strong class="text-danger">{{ $message }}</strong>
            </span>
            @enderror
        </div>
    </div>
        <div class="col-12 form-group">
            {{Collective\Html\FormFacade::label('method',__('Method'),['class'=>'form-label'])}}
            {{Collective\Html\FormFacade::select('method',$methods,null,array('class'=>'form-control select','placeholder'=>__('Select Method')))}}
        </div>

    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{__('Cancel')}}" class="btn btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{__('Update')}}" class="btn btn-primary">
</div>
{{Collective\Html\FormFacade::close()}}
