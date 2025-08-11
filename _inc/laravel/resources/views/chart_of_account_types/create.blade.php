<div class="{{ ViewClassNamesConstants::CD }} bg-none card-box">
    {{ Collective\Html\FormFacade::open(['url'=>'chart-of-account-types']) }}
    <div class="{{ ViewClassNamesConstants::RW }}">
        <div class="{{ ViewClassNamesConstants::FM_G }} {{ ViewClassNamesConstants::C12 }}">
            {{ Collective\Html\FormFacade::label('name', __('Name'), ['class'=>ViewClassNamesConstants::FM_LB]) }}
            {{ Collective\Html\FormFacade::text('name', '', ['class'=>ViewClassNamesConstants::FM_CT,'required'=>'required']) }}
            @error('name')<small class="invalid-name" role="alert"><strong class="text-danger">{{ $message }}</strong></small>@enderror
        </div>
    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{ __('Cancel') }}" class="{{ ViewClassNamesConstants::BT_LG }}" data-bs-dismiss="modal">
    <input type="submit" value="{{ __('Create') }}" class="{{ ViewClassNamesConstants::BT_PRM }}">
</div>
{{ Collective\Html\FormFacade::close() }}
