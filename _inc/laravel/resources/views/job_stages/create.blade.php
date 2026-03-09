@php
    try {
$lang = Utility::fetchUserLang();

        $formId     = 'jb-stg-store-form';
        $storeBase  = VW::JB_STG;
        $storeKebab = Str::kebab($storeBase);
        $storeRes   = Route::has($storeBase) ? $storeBase : (Route::has($storeKebab) ? $storeKebab : null);
        $storeUrl   = $storeRes ? route($storeRes) : '#';
        $storeGuard = Utility::fetchLinkMessage($lang, VW::JB_STG, 'store_route_unavailable') ?? __('Job stage store route is unavailable. Please contact technical support or your domain administrator.');

        $titleErr   = $errors->has('title');
        $titleAttrs = [
            'id'               => 'title',
            'class'            => trim(VC::FM_CT . ' ' . ($titleErr ? 'is-invalid' : '')),
            'placeholder'      => __('Enter stage title'),
            'required'         => 'required',
            'aria-invalid'     => $titleErr ? 'true' : 'false',
            'aria-describedby' => $titleErr ? 'title-error' : null,
            'autocomplete'     => 'off',
        ];
    } catch (\Throwable $e) {
        \Log::error('job_stages/create — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
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
    <div class="modal-body">
        <div class="{{ VC::RW }}">
            <div class="{{ VC::FM_GCB12 }}">
                {{ Form::label('title', __('Title'), ['class' => VC::FM_LB]) }}
                {{ Form::text('title', null, $titleAttrs) }}
                @error('title')
                    <span id="title-error" class="{{ VC::INV_FB }} {{ VC::DBL }}" role="alert"><strong class="{{ VC::TX_DNG }}">{{ $message }}</strong></span>
                @enderror
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <input type="button" value="{{ __('Cancel') }}" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">
        <input type="submit" value="{{ __('Create') }}" class="{{ VC::BT_PRM }}">
    </div>
    <script defer src="{{ asset('assets/js/routes/jobs/stages/store.js') }}"></script>
{{ Form::close() }}
