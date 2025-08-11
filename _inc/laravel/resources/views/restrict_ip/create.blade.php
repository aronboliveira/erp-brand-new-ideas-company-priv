{{ Collective\Html\FormFacade::open(['route' => ['create.ip'], 'method' => 'post']) }}
<div class="modal-body">
    <div class="row">
        <div class="form-group">
            {{ Collective\Html\FormFacade::label('ip', __('IP'), ['class' => 'col-form-label']) }}
            {{ Collective\Html\FormFacade::text('ip', null, ['class' => 'form-control']) }}
        </div>
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ __('Close') }}</button>
    <input type="submit" value="{{ __('Create') }}" class="btn btn-primary">

</div>
{{ Collective\Html\FormFacade::close() }}
