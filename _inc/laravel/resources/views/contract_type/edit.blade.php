@php
    use App\Config\Constants\ViewsConstants;
@endphp
{{ Collective\Html\FormFacade::model($contractType, array('route' => array(ViewsConstants::CTC_TP.'.update', $contractType->id), 'method' => 'PUT')) }}
<div class="modal-body">
    <div class="row">
        <div class="form-group">
            {{ Collective\Html\FormFacade::label('name', __('Name')) }}
            {{ Collective\Html\FormFacade::text('name', null, array('class' => 'form-control','required'=>'required')) }}
        </div>
    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{__('Cancel')}}" class="btn btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{__('Update')}}" class="btn btn-primary">
</div>
{{Collective\Html\FormFacade::close()}}

