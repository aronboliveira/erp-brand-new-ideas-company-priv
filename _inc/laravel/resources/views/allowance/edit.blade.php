@php
    use App\Config\Constants\{ViewsConstants, ViewClassNamesConstants as VC};
    use App\Models\Utility;
    use Collective\Html\FormFacade as Form;
    use Illuminate\Support\Facades\Route;
    $lang = Utility::fetchUserLang();
    $updateRoute = Route::has(ViewsConstants::ALW.'.update')
        ? route(ViewsConstants::ALW.'.update', $allowance->id)
        : '#';
    $formId = 'allowance-update-form';
    $updateMsg = Utility::fetchLinkMessage(
        $lang,
        ViewsConstants::ALW,
        'allowance_update_route_unavailable'
    ) ?? 'Allowance update route is unavailable. Please contact technical support or your domain administrator.';
@endphp

{{ Form::model($allowance, [
    'route'             => [$updateRoute],
    'method'            => 'PUT',
    'id'                => $formId,
    'data-url'          => $updateRoute,
    'data-sv-localized' => 'true',
    'data-guard-msg'    => $updateMsg,
]) }}
    <div class="modal-body">
        <div class="card-body p-0">
            <div class="{{ VC::RW }}">
                <div class="{{ VC::FM_G }} {{ VC::CM6 }}">
                    {{ Form::label('allowance_option', __('Allowance Options'), ['class' => VC::FM_LB]) }}<span class="text-danger">*</span>
                    {{ Form::select('allowance_option', $allowance_options, null, ['class' => VC::FM_CT_SL, 'required']) }}
                </div>
                <div class="{{ VC::FM_G }} {{ VC::CM6 }}">
                    {{ Form::label('title', __('Title'), ['class' => VC::FM_LB]) }}
                    {{ Form::text('title', null, ['class' => VC::FM_CT, 'required']) }}
                </div>
            </div>
            <div class="{{ VC::RW }}">
                <div class="{{ VC::FM_G }} {{ VC::CM6 }}">
                    {{ Form::label('type', __('Type'), ['class' => VC::FM_LB]) }}
                    {{ Form::select('type', $Allowancetypes, null, ['class' => VC::FM_CT_SL . ' amount_type', 'required']) }}
                </div>
                <div class="{{ VC::FM_G }} {{ VC::CM6 }}">
                    {{ Form::label('amount', __('Amount'), ['class' => VC::FM_LB]) }}
                    {{ Form::number('amount', null, ['class' => VC::FM_CT, 'required', 'step' => '0.01']) }}
                </div>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <input type="button" value="{{ __('Cancel') }}" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">
        <input type="submit" value="{{ __('Update') }}" class="{{ VC::BT_PRM }}">
    </div>
{{ Form::close() }}
<script async src="{{ asset('assets/js/routes/allowances/lang/update.js') }}"></script>
<script defer src="{{ asset('assets/js/routes/allowances/update.js') }}"></script>
