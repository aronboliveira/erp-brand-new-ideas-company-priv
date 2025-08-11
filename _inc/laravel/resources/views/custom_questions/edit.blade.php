@php
    use App\Config\Constants\ViewClassNamesConstants as VC;
    use Collective\Html\FormFacade as Form;
@endphp

{{ Form::model($customQuestion, ['route' => ['custom-questions.update', $customQuestion->id], 'method' => 'PUT']) }}
    <div class="modal-body">
        <div class="{{ VC::RW }}">
            <div class="{{ VC::CM12 }}">
                <div class="{{ VC::FM_G }}">
                    {{ Form::label('question', __('Question'), ['class' => VC::FM_LB]) }}
                    {{ Form::text('question', null, ['class' => VC::FM_CT, 'placeholder' => __('Enter question')]) }}
                </div>
            </div>
            <div class="{{ VC::CM12 }}">
                <div class="{{ VC::FM_G }}">
                    {{ Form::label('is_required', __('Is Required'), ['class' => VC::FM_LB]) }}
                    {{ Form::select('is_required', $is_required, null, ['class' => VC::FM_CT_SL, 'required' => 'required']) }}
                </div>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <input type="button" value="{{ __('Cancel') }}" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">
        <input type="submit" value="{{ __('Update') }}" class="{{ VC::BT_PRM }}">
    </div>
{{ Form::close() }}
