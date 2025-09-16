@php
    use App\Config\Constants\{ViewsConstants as VW, ViewClassNamesConstants as VC};
    use App\Models\Utility;
    use Collective\Html\FormFacade as Form;
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\Str;
    use Illuminate\Support\Collection;

    $lang      = Utility::fetchUserLang();
    $formId    = 'gl-store-form';
    $storeBase = VW::GL;
    $storeKbb  = Str::kebab($storeBase);
    $storeRes  = Route::has($storeBase) ? $storeBase : (Route::has($storeKbb) ? $storeKbb : null);
    $storeUrl  = $storeRes ? route($storeRes) : '#';
    $storeGuard= Utility::fetchLinkMessage($lang, VW::GL, 'store_route_unavailable') ?? __('Goal store route is unavailable. Please contact technical support or your domain administrator.');

    $nameErr   = $errors->has('name');
    $nameAttrs = [
        'id'               => 'name',
        'class'            => trim(VC::FM_CT . ' ' . ($nameErr ? 'is-invalid' : '')),
        'required'         => 'required',
        'placeholder'      => __('Enter name'),
        'aria-invalid'     => $nameErr ? 'true' : 'false',
        'aria-describedby' => $nameErr ? 'name-error' : null,
        'autocomplete'     => 'off',
    ];

    $amountErr   = $errors->has('amount');
    $amountAttrs = [
        'id'               => 'amount',
        'class'            => trim(VC::FM_CT . ' ' . ($amountErr ? 'is-invalid' : '')),
        'required'         => 'required',
        'step'             => '0.01',
        'aria-invalid'     => $amountErr ? 'true' : 'false',
        'aria-describedby' => $amountErr ? 'amount-error' : null,
        'autocomplete'     => 'off',
    ];

    $typesIsList = (is_array($types ?? null) && count($types ?? []) > 0) || (($types ?? null) instanceof Collection && $types->isNotEmpty());
    $typeOptions = $typesIsList ? (is_array($types) ? $types : $types->toArray()) : ['' => __('No types available')];
    $typeErr     = $errors->has('type');
    $typeAttrs   = [
        'id'               => 'type',
        'class'            => trim(VC::FM_CT_SL . ' ' . ($typeErr ? 'is-invalid' : '')),
        'required'         => 'required',
        'aria-invalid'     => $typeErr ? 'true' : 'false',
        'aria-describedby' => $typeErr ? 'type-error' : null,
    ];
    if (!$typesIsList) { $typeAttrs['disabled'] = 'disabled'; }

    $fromErr   = $errors->has('from');
    $fromAttrs = [
        'id'               => 'from',
        'class'            => trim(VC::FM_CT . ' ' . ($fromErr ? 'is-invalid' : '')),
        'required'         => 'required',
        'aria-invalid'     => $fromErr ? 'true' : 'false',
        'aria-describedby' => $fromErr ? 'from-error' : null,
    ];

    $toErr   = $errors->has('to');
    $toAttrs = [
        'id'               => 'to',
        'class'            => trim(VC::FM_CT . ' ' . ($toErr ? 'is-invalid' : '')),
        'required'         => 'required',
        'aria-invalid'     => $toErr ? 'true' : 'false',
        'aria-describedby' => $toErr ? 'to-error' : null,
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
            <div class="{{ VC::FM_GCB6 }}">
                {{ Form::label('name', __('Name'), ['class' => VC::FM_LB]) }}
                {{ Form::text('name', '', $nameAttrs) }}
                @error('name')
                    <span id="name-error" class="invalid-feedback d-block" role="alert"><strong class="text-danger">{{ $message }}</strong></span>
                @enderror
            </div>

            <div class="{{ VC::FM_GCB6 }}">
                {{ Form::label('amount', __('Amount'), ['class' => VC::FM_LB]) }}
                {{ Form::number('amount', '', $amountAttrs) }}
                @error('amount')
                    <span id="amount-error" class="invalid-feedback d-block" role="alert"><strong class="text-danger">{{ $message }}</strong></span>
                @enderror
            </div>

            <div class="{{ VC::FM_GCB12 }}">
                {{ Form::label('type', __('Type'), ['class' => VC::FM_LB]) }}
                {{ Form::select('type', $typeOptions, null, $typeAttrs) }}
                @error('type')
                    <span id="type-error" class="invalid-feedback d-block" role="alert"><strong class="text-danger">{{ $message }}</strong></span>
                @enderror
            </div>

            <div class="{{ VC::FM_GCB6 }}">
                {{ Form::label('from', __('From'), ['class' => VC::FM_LB]) }}
                {{ Form::date('from', null, $fromAttrs) }}
                @error('from')
                    <span id="from-error" class="invalid-feedback d-block" role="alert"><strong class="text-danger">{{ $message }}</strong></span>
                @enderror
            </div>

            <div class="{{ VC::FM_GCB6 }}">
                {{ Form::label('to', __('To'), ['class' => VC::FM_LB]) }}
                {{ Form::date('to', null, $toAttrs) }}
                @error('to')
                    <span id="to-error" class="invalid-feedback d-block" role="alert"><strong class="text-danger">{{ $message }}</strong></span>
                @enderror
            </div>

            <div class="{{ VC::FM_GCB12 }}">
                <div class="{{ VC::FM_CHK }}">
                    <input class="form-check-input" type="checkbox" name="is_display" id="is_display" checked="checked">
                    <label class="{{ VC::FM_LB }}" for="is_display">{{ __('Display On Dashboard') }}</label>
                </div>
            </div>
        </div>
    </div>

    <div class="modal-footer">
        <input type="button" value="{{ __('Cancel') }}" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">
        <input type="submit" value="{{ __('Create') }}" class="{{ VC::BT_PRM }}">
    </div>

    <script defer src="{{ asset('assets/js/routes/goals/store.js') }}"></script>
{{ Form::close() }}
