@php
    $deductionOptionStoreBaseRouteName  = ViewsConstants::DDT_OPT;
    $deductionOptionStoreKebabRouteName = Str::kebab($deductionOptionStoreBaseRouteName);
    $deductionOptionStoreResolvedName   = Route::has($deductionOptionStoreBaseRouteName)
        ? $deductionOptionStoreBaseRouteName
        : (Route::has($deductionOptionStoreKebabRouteName) ? $deductionOptionStoreKebabRouteName : null);
    $deductionOptionStoreUrl            = $deductionOptionStoreResolvedName ? route($deductionOptionStoreResolvedName) : '#';

    $deductionOptionCreateFormId        = 'deduction-option-store-form';
    $langValue                          = isset($lang) ? $lang : Utility::fetchUserLang();
    $deductionOptionStoreGuardMessage   = Utility::fetchLinkMessage($langValue, ViewsConstants::DDT_OPT, 'store_deduction_option_route_unavailable')
        ?? 'Store deduction option route is unavailable. Please contact technical support or your domain administrator.';
@endphp

{{ Form::open([
    'method'            => 'POST',
    'url'               => $deductionOptionStoreUrl,
    'id'                => $deductionOptionCreateFormId,
    'data-url'          => $deductionOptionStoreUrl,
    'data-guard-msg'    => $deductionOptionStoreGuardMessage,
    'data-sv-localized' => 'true',
]) }}
    @csrf
    <div class="modal-body">
        <div class="{{ VC::RW }}">
            <div class="{{ VC::CM12 }}">
                <div class="{{ VC::FM_G }}">
                    {{ Form::label('name', __('Name'), ['class' => VC::FM_LB]) }}
                    @php
                        $hasError = $errors->has('name');
                        $attrs = [
                            'id'               => 'name',
                            'class'            => trim(VC::FM_CT . ' ' . ($hasError ? 'is-invalid' : '')),
                            'placeholder'      => __('Enter Deduction Option Name'),
                            'required'         => 'required',
                            'aria-invalid'     => $hasError ? 'true' : 'false',
                            'aria-describedby' => $hasError ? 'name-error' : null,
                            'autocomplete'     => 'off',
                        ];
                    @endphp
                    {{ Form::text('name', null, $attrs) }}
                    @if($hasError)
                        <span id="name-error" class="invalid-feedback d-block" role="alert">
                            <strong class="text-danger">{{ $errors->first('name') }}</strong>
                        </span>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
        <button type="submit" class="{{ VC::BT_PRM }}">{{ __('Create') }}</button>
    </div>
    <script defer src="{{ asset('assets/js/routes/deductionOptions/store.js') }}"></script>
{{ Form::close() }}