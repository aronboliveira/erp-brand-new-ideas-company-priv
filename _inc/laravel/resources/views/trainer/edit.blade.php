{{Collective\Html\FormFacade::model($trainer,array('route' => array('trainer.update', $trainer->id), 'method' => 'PUT')) }}
<div class="modal-body">
    <div class="row">
        <div class="col-md-12">
            <div class="form-group">
                {{Collective\Html\FormFacade::label('branch',__('Branch'),['class'=>'form-label'])}}
                {{Collective\Html\FormFacade::select('branch',$branches,null,array('class'=>'form-control select','required'=>'required'))}}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{Collective\Html\FormFacade::label('firstname',__('First Name'),['class'=>'form-label'])}}
                {{Collective\Html\FormFacade::text('firstname',null,array('class'=>'form-control','required'=>'required'))}}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{Collective\Html\FormFacade::label('lastname',__('Last Name'),['class'=>'form-label'])}}
                {{Collective\Html\FormFacade::text('lastname',null,array('class'=>'form-control','required'=>'required'))}}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{Collective\Html\FormFacade::label('contact',__('Contact'),['class'=>'form-label'])}}
                {{Collective\Html\FormFacade::text('contact',null,array('class'=>'form-control','required'=>'required'))}}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{Collective\Html\FormFacade::label('email',__('Email'),['class'=>'form-label'])}}
                {{Collective\Html\FormFacade::text('email',null,array('class'=>'form-control','required'=>'required'))}}
            </div>
        </div>
        <div class="form-group col-lg-12">
            {{Collective\Html\FormFacade::label('expertise',__('Expertise'),['class'=>'form-label'])}}
            {{Collective\Html\FormFacade::textarea('expertise',null,array('class'=>'form-control','placeholder'=>__('Expertise')))}}
        </div>
        <div class="form-group col-lg-12">
            {{Collective\Html\FormFacade::label('address',__('Address'),['class'=>'form-label'])}}
            {{Collective\Html\FormFacade::textarea('address',null,array('class'=>'form-control','placeholder'=>__('Address')))}}
        </div>
    
    </div>
</div>

    <div class="modal-footer">
        <input type="button" value="{{__('Cancel')}}" class="btn btn-light" data-bs-dismiss="modal">
        <input type="submit" value="{{__('Update')}}" class="btn btn-primary">
    </div>
{{Collective\Html\FormFacade::close()}}
