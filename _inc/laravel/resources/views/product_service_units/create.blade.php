@php
    use App\Config\Constants\ViewsConstants;
    use App\Config\Constants\ViewClassNamesConstants as VC;
    use Collective\Html\FormFacade as Form;
@endphp
{{ Form::open(['url' => ViewsConstants::PRD_SV_UNT]) }}
    <div class="modal-body">
        <div class="row">
            <div class="{{ VC::FM_G }} {{ VC::C12 }}">
                {{ Form::label('name', __('Unit Name'), ['class' => VC::FM_LB]) }}
                {{ Form::text('name', '', ['class' => VC::FM_CT, 'required' => 'required']) }}
                @error('name')
                    <small class="invalid-name" role="alert">
                        <strong class="text-danger">{{ $message }}</strong>
                    </small>
                @enderror
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <input type="button" value="{{ __('Cancel') }}" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">
        <input type="submit" value="{{ __('Create') }}" class="{{ VC::BT_PRM }}">
    </div>
{{ Form::close() }}
