<div class="card bg-none card-box">
    {{ Collective\Html\FormFacade::open(array('url' => 'clients')) }}
    <div class="row">
        <div class="col-6 form-group">
            {{ Collective\Html\FormFacade::label('name', __('Name'),['class'=>'form-label']) }}
            {{ Collective\Html\FormFacade::text('name', null, array('class' => 'form-control','required'=>'required')) }}
        </div>
        <div class="col-6 form-group">
            {{ Collective\Html\FormFacade::label('email', __('E-Mail Address'),['class'=>'form-label']) }}
            {{ Collective\Html\FormFacade::email('email', null, array('class' => 'form-control','required'=>'required')) }}
        </div>
        <div class="col-6 form-group">
            {{ Collective\Html\FormFacade::label('password', __('Password'),['class'=>'form-label']) }}
            {{ Collective\Html\FormFacade::password('password', null, array('class' => 'form-control','required'=>'required')) }}
        </div>

        <div class="form-group mt-4 mb-0">
            {{ Collective\Html\FormFacade::hidden('ajax',true) }}
            <input type="submit" value="{{__('Create')}}" class="btn-create badge-blue">
        </div>
    </div>
    {{ Collective\Html\FormFacade::close() }}
</div>
