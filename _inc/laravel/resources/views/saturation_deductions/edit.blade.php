@php
    use App\Config\Constants\{ViewsConstants as VW, ViewClassNamesConstants as VC, StacksConstants};
    use App\Models\Utility;
    use Collective\Html\FormFacade as Form;
    use Illuminate\Support\{Facades\Route, Str};
    $lang = Utility::fetchUserLang();
    $satUpdBaseName      = VW::STR_DD.'.update';
    $satUpdKebabName     = Str::kebab($satUpdBaseName);
    $satUpdResolvedName  = Route::has($satUpdBaseName)
        ? $satUpdBaseName
        : (Route::has($satUpdKebabName) ? $satUpdKebabName : null);
    $satUpdObjectIdValue = isset($saturationDeduction) && !empty($saturationDeduction->id) ? $saturationDeduction->id : null;
    $satUpdUrl           = ($satUpdResolvedName && $satUpdObjectIdValue) ? route($satUpdResolvedName, [$satUpdObjectIdValue]) : '#';
    $satUpdGuardMsg      = Utility::fetchLinkMessage($lang, 'payroll', 'update_saturation_deduction_unavailable') ?? 'Update saturation deduction route is unavailable. Please contact technical support or your domain administrator.';
    $satUpdFormId        = 'edit-saturation-deduction-form-'.($satUpdObjectIdValue ?? 'x');
    $formOpenOpts = ($satUpdResolvedName && $satUpdObjectIdValue)
        ? ['route' => [$satUpdResolvedName, $satUpdObjectIdValue], 'method' => 'PUT', 'id' => $satUpdFormId]
        : ['url' => '#', 'method' => 'PUT', 'id' => $satUpdFormId];
    $formOpenOpts['data-action-url']     = $satUpdUrl;
    $formOpenOpts['data-form-guard-msg'] = $satUpdGuardMsg;
    $formOpenOpts['data-sv-localized']   = 'true';
@endphp

{{ Form::model($saturationDeduction, $formOpenOpts) }}
    <div class="modal-body">
        <div class="{{ VC::CD }} {{ VC::SNN }} p-0">
            <div class="{{ VC::RW }}">
                <div class="{{ VC::FM_GCB6 }}">
                    {{ Form::label('deduction_option', __('Deduction Options'), ['class' => VC::FM_LB]) }}<span class="text-danger">*</span>
                    {{ Form::select('deduction_option', $deduction_options, null, ['class' => VC::FM_CT_SL, 'required' => 'required']) }}
                </div>
                <div class="{{ VC::FM_GCB6 }}">
                    {{ Form::label('title', __('Title'), ['class' => VC::FM_LB]) }}
                    {{ Form::text('title', null, ['class' => VC::FM_CT, 'required' => 'required']) }}
                </div>
                <div class="{{ VC::FM_GCB6 }}">
                    {{ Form::label('type', __('Type'), ['class' => VC::FM_LB]) }}
                    {{ Form::select('type', $saturationdeduc, null, ['class' => VC::FM_CT_SL . ' amount_type', 'required' => 'required']) }}
                </div>
                <div class="{{ VC::FM_GCB6 }}">
                    {{ Form::label('amount', __('Amount'), ['class' => VC::FM_LB . ' amount_label']) }}
                    {{ Form::number('amount', null, ['class' => VC::FM_CT, 'required' => 'required', 'step' => '0.01']) }}
                </div>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <input type="button" value="{{ __('Cancel') }}" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">
        <input type="submit" value="{{ __('Update') }}" class="{{ VC::BT_PRM }}">
    </div>
{{ Form::close() }}

@push(StacksConstants::ADM_SCR_PG)
    <script defer src="{{ asset('assets/js/routes/saturationDeductions/update.js') }}"></script>
@endpush
