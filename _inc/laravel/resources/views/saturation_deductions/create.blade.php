@php
    use App\Config\Constants\{ViewsConstants as VW, ViewClassNamesConstants as VC, StacksConstants};
    use App\Models\Utility;
    use Collective\Html\FormFacade as Form;
    use Illuminate\Support\{Facades\Route, Str};
    $lang = Utility::fetchUserLang();
    $satDedBaseName     = VW::STR_DD;
    $satDedKebabName    = Str::kebab($satDedBaseName);
    $satDedResolvedName = Route::has($satDedBaseName)
        ? $satDedBaseName
        : (Route::has($satDedKebabName) ? $satDedKebabName : null);
    $satDedUrl          = $satDedResolvedName ? route($satDedResolvedName) : '#';
    $satDedGuardMsg     = Utility::fetchLinkMessage($lang, VW::STR_DD, 'saturation_deduction_store_route_unavailable') ?? 'Store saturation deduction route is unavailable. Please contact technical support or your domain administrator.';
    $satDedFormId       = 'create_saturation_deduction_form';
@endphp

{{ Form::open([
    'url'                 => VW::STR_DD,
    'method'              => 'post',
    'id'                  => $satDedFormId,
    'data-action-url'     => $satDedUrl,
    'data-form-guard-msg' => $satDedGuardMsg,
    'data-sv-localized'   => 'true',
]) }}
<div class="modal-body">
    {{ Form::hidden('employee_id', $employee->id, []) }}

    <div class="{{ VC::RW }}">
        <div class="{{ VC::FM_GCB6 }}">
            {{ Form::label('deduction_option', __('Deduction Options'), [ 'class' => VC::FM_LB ]) }}<span class="text-danger">*</span>
            {{ Form::select('deduction_option', $deduction_options, null, [ 'class' => VC::FM_CT_SL, 'required' => 'required' ]) }}
        </div>

        <div class="{{ VC::FM_GCB6 }}">
            {{ Form::label('title', __('Title'), [ 'class' => VC::FM_LB ]) }}
            {{ Form::text('title', null, [ 'class' => VC::FM_CT, 'required' => 'required' ]) }}
        </div>

        <div class="{{ VC::FM_GCB6 }}">
            {{ Form::label('type', __('Type'), [ 'class' => VC::FM_LB ]) }}
            {{ Form::select('type', $saturationdeduc, null, [ 'class' => VC::FM_CT_SL . ' amount_type', 'required' => 'required' ]) }}
        </div>

        <div class="{{ VC::FM_GCB6 }}">
            {{ Form::label('amount', __('Amount'), [ 'class' => VC::FM_LB . ' amount_label' ]) }}
            {{ Form::number('amount', null, [ 'class' => VC::FM_CT, 'required' => 'required', 'step' => '0.01' ]) }}
        </div>
    </div>
</div>

<div class="modal-footer">
    <input type="button" value="{{ __('Cancel') }}" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">
    <input type="submit" value="{{ __('Create') }}" class="{{ VC::BT_PRM }}">
</div>

{{ Form::close() }}

@push(StacksConstants::ADM_SCR_PG)
    <script defer src="{{ asset('assets/js/routes/saturationDeductions/store.js') }}"></script>
@endpush
