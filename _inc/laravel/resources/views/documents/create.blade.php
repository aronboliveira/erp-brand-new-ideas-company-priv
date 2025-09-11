@php
    use App\Config\Constants\{ViewClassNamesConstants as VC, ViewsConstants as VW};
    use App\Models\Utility;
    use Collective\Html\FormFacade as Form;
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\Str;
    $lang                 = Utility::fetchUserLang();
    $docStoreBase         = VW::DOC;
    $docStoreKebab        = Str::kebab($docStoreBase);
    $docStoreResolved     = Route::has($docStoreBase) ? $docStoreBase : (Route::has($docStoreKebab) ? $docStoreKebab : null);
    $docStoreUrl          = $docStoreResolved ? route($docStoreResolved) : '#';
    $docStoreFormId       = 'document-store-form';
    $docStoreGuardMsg     = Utility::fetchLinkMessage($lang, VW::DOC, 'document_store_route_unavailable') ?? 'Store document route is unavailable. Please contact technical support or your domain administrator.';
    $nameHasError         = $errors->has('name');
    $nameAttrs            = [
        'id'               => 'doc_name',
        'class'            => trim(VC::FM_CT.' '.($nameHasError ? 'is-invalid' : '')),
        'placeholder'      => __('Enter Document Name'),
        'required'         => 'required',
        'autocomplete'     => 'off',
        'aria-invalid'     => $nameHasError ? 'true' : 'false',
        'aria-describedby' => $nameHasError ? 'name-error' : null,
    ];
    $reqHasError          = $errors->has('is_required');
    $isRequiredOptions    = ['0' => __('Not Required'), '1' => __('Is Required')];
    $reqAttrs             = [
        'id'               => 'doc_required',
        'class'            => trim(VC::FM_CT.' select2'.($reqHasError ? ' is-invalid' : '')),
        'required'         => 'required',
        'aria-invalid'     => $reqHasError ? 'true' : 'false',
        'aria-describedby' => $reqHasError ? 'is_required-error' : null,
    ];
@endphp
{{ Form::open([
    'url'               => $docStoreUrl,
    'method'            => 'POST',
    'id'                => $docStoreFormId,
    'data-url'          => $docStoreUrl,
    'data-guard-msg'    => $docStoreGuardMsg,
    'data-sv-localized' => 'true',
]) }}
    @csrf
    <div class="modal-body">
        <div class="{{ VC::RW }}">
            <div class="{{ VC::FM_G }} {{ VC::C12 }}">
                {{ Form::label('name', __('Name'), ['class' => VC::FM_LB, 'for' => 'doc_name']) }}
                {{ Form::text('name', null, $nameAttrs) }}
                @error('name')
                    <span id="name-error" class="invalid-feedback d-block" role="alert"><strong class="text-danger">{{ $message }}</strong></span>
                @enderror
            </div>
            <div class="{{ VC::FM_G }} {{ VC::C12 }}">
                {{ Form::label('is_required', __('Required Field'), ['class' => VC::FM_LB, 'for' => 'doc_required']) }}
                {{ Form::select('is_required', $isRequiredOptions, null, $reqAttrs) }}
                @error('is_required')
                    <span id="is_required-error" class="invalid-feedback d-block" role="alert"><strong class="text-danger">{{ $message }}</strong></span>
                @enderror
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <input type="button" value="{{ __('Cancel') }}" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">
        <input type="submit" value="{{ __('Create') }}" class="{{ VC::BT_PRM }}">
    </div>
    <script defer src="{{ asset('assets/js/routes/documents/store.js') }}"></script>
{{ Form::close() }}
