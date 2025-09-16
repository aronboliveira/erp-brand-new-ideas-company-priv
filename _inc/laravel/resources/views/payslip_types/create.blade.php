@php
    use App\Config\Constants\{ViewsConstants as VW, ViewClassNamesConstants as VC};
    use App\Models\Utility;
    use Collective\Html\FormFacade as Form;
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\{Collection, Str};

    $lang = Utility::fetchUserLang();

    $formId     = 'py-slp-tp-store-form';
    $storeBase  = VW::PY_SLP_TP;
    $storeKebab = Str::kebab($storeBase);
    $storeRes   = Route::has($storeBase) ? $storeBase : (Route::has($storeKebab) ? $storeKebab : null);
    $storeUrl   = $storeRes ? route($storeRes) : '#';
    $storeGuard = Utility::fetchLinkMessage($lang, VW::PY_SLP_TP, 'store_route_unavailable') ?? __('Payslip type store route is unavailable. Please contact technical support or your domain administrator.');

    $nameErr = $errors->has('name');
    $nameAttrs = [
        'id'               => 'name',
        'class'            => trim(VC::FM_CT . ' ' . ($nameErr ? 'is-invalid' : '')),
        'placeholder'      => __('Enter Payslip Type Name'),
        'required'         => 'required',
        'aria-invalid'     => $nameErr ? 'true' : 'false',
        'aria-describedby' => $nameErr ? 'name-error' : null,
        'autocomplete'     => 'off',
    ];
@endphp

{{ Form::open([
    'url'               => $storeUrl,
    'method'            => 'POST',
    'id'                => $formId,
    'data-url'          => $storeUrl,
    'data-guard-msg'    => $storeGuard,
    'data-sv-localized' => 'true',
]) }}
<div class="modal-body">
    <div class="{{ VC::RW }}">
        <div class="{{ VC::FM_GCB12 }}">
            {{ Form::label('name', __('Name'), ['class' => VC::FM_LB]) }}
            {{ Form::text('name', null, $nameAttrs) }}
            @error('name')
                <span id="name-error" class="invalid-feedback d-block" role="alert"><strong class="text-danger">{{ $message }}</strong></span>
            @enderror
        </div>
    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{ __('Cancel') }}" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">
    <input type="submit" value="{{ __('Create') }}" class="{{ VC::BT_PRM }}">
</div>
<script defer src="{{ asset('assets/js/routes/payslips/types/store.js') }}"></script>
{{ Form::close() }}
