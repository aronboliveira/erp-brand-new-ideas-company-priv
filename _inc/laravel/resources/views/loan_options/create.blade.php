@php
    try {
$lang = Utility::fetchUserLang();

        $formId     = 'ln-opt-store-form';
        $storeBase  = VW::LN_OPT;
        $storeKebab = Str::kebab($storeBase);
        $storeRes   = Route::has($storeBase) ? $storeBase : (Route::has($storeKebab) ? $storeKebab : null);
        $storeUrl   = $storeRes ? route($storeRes) : '#';
        $storeGuard = Utility::fetchLinkMessage($lang, VW::LN_OPT, 'store_route_unavailable') ?? __('Loan option store route is unavailable. Please contact technical support or your domain administrator.');

        $nameErr   = $errors->has('name');
        $nameAttrs = [
            'id'               => 'name',
            'class'            => trim(VC::FM_CT . ' ' . ($nameErr ? 'is-invalid' : '')),
            'placeholder'      => __('Enter Loan Option Name'),
            'required'         => 'required',
            'aria-invalid'     => $nameErr ? 'true' : 'false',
            'aria-describedby' => $nameErr ? 'name-error' : null,
            'autocomplete'     => 'off',
        ];
    } catch (\Throwable $e) {
        \Log::error('loan_options/create — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    }
@endphp

{{ Form::open([
    'url'               => $storeUrl,
    'method'            => 'POST',
    'id'                => $formId,
    'data-url'          => $storeUrl,
    'data-guard-msg'    => $storeGuard,
    'data-sv-localized' => 'true',
]) }}
    @csrf
    <div class="modal-body">
        <div class="{{ VC::RW }}">
            <div class="{{ VC::FM_GCB12 }}">
                {{ Form::label('name', __('Name'), ['class' => VC::FM_LB]) }}
                {{ Form::text('name', null, $nameAttrs) }}
                @error('name')
                    <span id="name-error" class="{{ VC::INV_FB }} {{ VC::DBL }}" role="alert"><strong class="{{ VC::TX_DNG }}">{{ $message }}</strong></span>
                @enderror
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <input type="button" value="{{ __('Cancel') }}" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">
        <input type="submit" value="{{ __('Create') }}" class="{{ VC::BT_PRM }}">
    </div>
    <script defer src="{{ asset('assets/js/routes/loans/options/store.js') }}"></script>
{{ Form::close() }}
